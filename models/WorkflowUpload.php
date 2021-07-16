<?php
/************************************************************************************
 *
 *  Copyright (c) 2018 Thanasis Vergoulis & Konstantinos Zagganas &  Loukas Kavouras
 *  for the Information Management Systems Institute, "Athena" Research Center.
 *  
 *  This file is part of SCHeMa.
 *  
 *  SCHeMa is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *  
 *  SCHeMa is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  
 *  You should have received a copy of the GNU General Public License
 *  along with Foobar.  If not, see <https://www.gnu.org/licenses/>.
 *
 ************************************************************************************/
/**
 * This model is used to upload a new docker software image (form and actions)
 * 
 * @author Kostis Zagganas
 * First version: December 2018
 */
namespace app\models;

use Yii;
use yii\db\Query;
use yii\web\UploadedFile;
use yii\helpers\Url;
use webvimark\modules\UserManagement\models\User;
use app\models\Workflow;
use yii\helpers\BaseFilehelper;
use app\models\Software;

/**
 * This is the model class for table "software_upload".
 *
 * @property int $id
 * @property string $name
 * @property string $version
 * @property string $description
 * @property string $image
 * @property double $execution_time
 * @property double $cpu_time
 * @property double $memory_amount
 * @property bool $gpu
 * @property bool $io
 * @property string $default_command
 * @property file $imageFile
 */
class WorkflowUpload extends \yii\db\ActiveRecord
{
    public $workflowFile;
    public $dois='';
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'workflow_upload';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [    
            [['name'], 'string', 'max' => 100],
            [['description', 'instructions'], 'string'],
            [['version'], 'string', 'max' => 80],
            [['location'], 'string'],
            [['name','version',],'required'],
            [['name',],'allowed_name_chars'],
            [['version',],'allowed_version_chars'],
            [['workflowFile'], 'file','skipOnEmpty' => false, 'checkExtensionByMimeType' => false, 'extensions' => ['yaml','cwl','zip','gz','tar']],
            [['visibility','description'],'required'],
            [['biotools'],'string','max'=>255],
            [['version'], 'uniqueSoftware'],
            [['covid19'],'required'],
            
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        
        return [
            'id' => 'ID',
            'name' => 'Workflow name * ',
            'version' => 'Workflow version * ',
            'workflowFile' => 'Upload your workflow files (either single file or compressed) * ',
            'biotools'=>'Link in bio.tools (optional)',
            'visibility' => 'Visible to',
            'description'=> 'Workflow description * ',
            'covid19' => 'Workflow is related to COVID-19 research',
            'instructions'=>'Instructions',
        ];
    }


    public function upload()
    {
        $errors="";

        $username=User::getCurrentUser()['username'];
        $this->description=$this->quotes($this->description);
        $this->instructions=$this->quotes($this->instructions);
        $workflowFilePath=$this->quotes('');
        $workFlowFileExt=$this->quotes('');
        $this->covid19=($this->covid19=='1') ? "'t'" : "'f'";
        $this->biotools=$this->quotes($this->biotools);
        $this->github_link=$this->quotes('');

        $dataFolder=Yii::$app->params['tmpWorkflowPath'] . '/' . str_replace(' ','-',$this->name) . '/' . str_replace(' ','-',$this->version) . '/';
        if (!is_dir($dataFolder))
        {
            $command="mkdir -p $dataFolder";
            Software::exec_log($command,$ret,$outdir);
        }
        //add dois string in a file and pass it on to python

        if (!empty($this->dois))
        {
            $doiFile=$dataFolder . 'dois.txt';

            file_put_contents($doiFile, $this->dois . "\n");

            $doiFile=$this->quotes($doiFile);
        }
        else
        {
            $doiFile=$this->quotes('');;
        }
            
        if (!empty($this->workflowFile))
        {

            $workflowFilePath=$dataFolder . $this->workflowFile->baseName . '.' . $this->workflowFile->extension;
            $workflowFileExt=$this->workflowFile->extension;

            $this->workflowFile->saveAs($workflowFilePath);
           
        }

        $command="chmod 777 $dataFolder -R";
        Software::exec_log($command,$out,$ret);

        $workflowFilePath=$this->quotes($workflowFilePath);
        $this->name=$this->quotes($this->name);
        $this->version=$this->quotes($this->version);
        $username=$this->quotes($username);

        $arguments=[$this->name, $this->version, $workflowFilePath, $workflowFileExt, 
                    $username, $this->visibility, $this->description, $this->biotools, $doiFile, $this->covid19,$this->github_link,$this->instructions];

        // $command="sudo -u user /data/www/schema_test/scheduler_files/imageUploader.py ";
        $command=Software::sudoWrap(Yii::$app->params['scriptsFolder'] . "workflowUploader.py ");
        $command.= implode(" ", $arguments) . " ";
        $command.= "2>&1";

        Software::exec_log($command,$out,$ret);
        if ($ret != 0) {
          error_log("ERROR while running: ".$command);
          error_log("ERROR (".$ret."): ".implode($out));
        }

        $workflow=Workflow::find()->orderBy(['id' => SORT_DESC])->one();;
        $workflow_id=$workflow->id;
        $name="workflow" . $workflow_id;
        $dir=$workflow->location;
        $working_dir=getcwd();
        $command2="cwltool --print-dot ". $dir. " | dot -Tsvg > ". $working_dir . "/img/workflows/$name.svg";
        Software::exec_log($command2, $out2, $ret2);
        if ($ret2 != 0) {
          error_log("ERROR while running: ".$command2);
          error_log($ret." ".implode($out2));
        }

        $workflow->visualize="$name.svg";
        $workflow->update();


        $errors='';
        $warning='';
        $success='';
        $prog_output="";
        switch($ret)
        {
            case 0:
                $success="Workflow successfully uploaded!";
                Software::exec_log("rm $doiFile");
                break;
            case 2:
                $errors.="Error: code $ret. ";
                $errors.="Missing \"inputs\" specification in your workflow.";
                $errors.="<br />Please correct the file syntax and try again or contact an administrator.";
                break;
            case 11:
                $errors.="Error: code $ret. ";
                $errors.="There was an problem decoding your CWL file:<br />";
                foreach ($out as $line)
                {
                    $errors.=$line . "<br />";
                }
                $errors.="<br />Please correct the file syntax and try again or contact an administrator.";
                break;
            case 12:
                $errors.="Error: code $ret. ";
                $errors.="One of your CWL files has does not specify a class.";
                $errors.="<br />Please correct the file syntax and try again or contact an administrator.";
                break;
            case 13:
                $errors.="Error: code $ret. ";
                $errors.="Your uploaded file contains more than one workflow files.";
                $errors.="<br />Please contact an administrator.";
                break;
            case 14:
                $errors.="Error: code $ret. ";
                $errors.="None of the uploaded files contain a \"class: Workflow\" specification";
                $errors.="<br />Please correct the file syntax and try again or contact an administrator.";
                break;
            case 30:
                $success.="Workflow successfully uploaded!<br />";
                $warning.="You did not specify any inputs for your workflow.";
                break;
            case 34:
                $errors.="Error: code $ret. ";
                $errors.="One of your workflow inputs does not have a type specification";
                $errors.="<br />Please correct the error and try again or contact an administrator.";
                break;
            case 35:
                $errors.="Error: code $ret. ";
                $errors.="One of your workflow inputs has an urecognized type specification.";
                // foreach ($out as $line)
                // {
                //     $errors.=$line . "<br />";
                // }
                $errors.="<br />Please correct the error and try again or contact an administrator.";
                break;
            case 36:
                $errors.="Error: code $ret. ";
                $errors.="One of your enum workflow inputs has an urecognized type specification.";
                $errors.="<br />Please correct the error and try again or contact an administrator.";
                break;
            case 37:
                $errors.="Error: code $ret. ";
                $errors.="One of your enum workflow inputs has an urecognized specification.";
                $errors.="<br />Please correct the error and try again or contact an administrator.";
                break;
            case 38:
                $errors.="Error: code $ret. ";
                $errors.="One of your enum workflow inputs does not contain any symbols";
                $errors.="<br />Please correct the error and try again or contact an administrator.";
                break;
                break;
            case 50:
                $errors.="Error: code $ret. ";
                $errors.="Input declaration structure not recognized";
                $errors.="<br />Please contact an administrator to assist you.";
                break;
            default:
                $errors.="Error: code $ret. ";
                $errors.="<br />An unexpected error occurred.";
                foreach ($out as $line)
                {
                    $errors.=$line . "<br />";
                }
                $errors.="<br />Please contact an administrator.";
                break;
        }        

        return [$errors,$success,$warning];
    }


    /*
     * This functions are used for validation
     * (doubtful if it works).
     */
    public function uniqueSoftware($attribute, $params, $validator)
    {
        // print_r($this->name);
        // exit(0);
        $workflows=Workflow::find()->where(['name'=>$this->name, 'version'=>$this->version])->all();
        if (!empty($workflows))
        {
                $this->addError($attribute, "Software $this->name v.$this->version already exists. Please specify another name or version.");
                return false;
        }
        return true;
    }


    public function quotes($string)
    {
        return "'" . $string . "'";
    }

    public function allowed_name_chars($attribute, $params, $validator)
    {
        if(preg_match('/[^A-Za-z_\-0-9]/', $this->$attribute))
        {
                $this->addError($attribute, "Software name can only contain letters, numbers, hyphens ( - ) and underscores ( _ )");
                return false;
        }
        return true;
    }

    public function allowed_version_chars($attribute, $params, $validator)
    {
        if(preg_match('/[^A-Va-z_\-0-9\.]/', $this->$attribute))
        {
                $this->addError($attribute, "Software version can only contain letters, numbers, hyphens ( - ) and underscores ( _ ) and full stops (.)");
                return false;
        }
        return true;
    }
}
