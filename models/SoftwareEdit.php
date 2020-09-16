<?php

namespace app\models;

use Yii;
use yii\db\Query;

/**
 * This is the model class for table "software".
 *
 * @property int $id
 * @property string $name
 * @property string $image
 * @property string $script
 * @property string $version
 * @property string $uploaded_by
 * @property string $visibility
 * @property string $workingdir
 * @property string $imountpoint
 * @property string $description
 * @property string $cwl_path
 * @property bool $has_example
 * @property string $biotools
 * @property string $dois
 * @property string $omountpoint
 */
class SoftwareEdit extends \yii\db\ActiveRecord
{
    public $cwlFile='';
    public $iomount=true;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'software';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description'], 'string',],
            [['description'], 'required',],
            [['visibility'], 'required',],
            [['imountpoint','omountpoint'], 'string',],
            [['imountpoint','omountpoint'], 'noSlash'],
            [['workingdir'], 'string',],
            [['biotools'],'string'],
            [['iomount'],'boolean'],
            [['cwlFile'], 'file', 'extensions' => ['yaml', 'cwl']],
            [['covid19'],'boolean'],
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
            'name' => 'Name',
            'image' => 'Image',
            'script' => 'Script',
            'version' => 'Version',
            'uploaded_by' => 'Uploaded By',
            'visibility' => 'Visibility',
            'workingdir' => 'Workingdir',
            'imountpoint' => 'Input folder mount point (where users can provide input data inside the container). Leave empty if no mount is required',
            'description' => 'Description',
            'cwl_path' => 'Cwl Path',
            'has_example' => 'Has Example',
            'biotools' => 'Link in bio.tools (optional)',
            'dois' => 'Edit relevant DOIs (optional)',
            'omountpoint' => 'Output folder mount point (folder inside the container where users can find the output). Leave empty if no mount is required',
            'iomount' => 'Image requires disk I/O',
            'cwlFile' => 'Upload a new CWL definition file * ',
            'covid19' => 'Software is related to COVID-19 research',
        ];
    }

    public function softwareEdit()
    {

        /*
         * If the user modified the name of the software,
         * update the DB table
         */
        // if (empty($this->workingdir))
        // {
        //     $this->workingdir='/data';
        // }
        $this->covid19=($this->covid19=="1") ? true : false;

        // Yii::$app->db->createCommand()->update('software',['description'=>$this->description,'visibility'=>$this->visibility,'imountpoint'=>$this->mountpoint,'workingdir'=>$this->workingdir,'biotools'=>$this->biotools,'dois'=>$this->dois], "name='$this->name' AND version='$this->version'")->execute();
        // $this->save();
        
        $query=new Query;
        $result=$query->select(['uploaded_by'])
                      ->from('software')
                      ->where(['name'=>$this->name, 'version'=>$this->version])
                      ->one();
        // print_r($this->uploaded_by);
        // exit(0);

        $username=$result['uploaded_by'];


        $error='';
        $success='Software details successfully updated!';
        $warning='';
        if (!empty($this->cwlFile))
        {
            // $username=User::getCurrentUser()['username'];
            $dataFolder=Yii::$app->params['tmpImagePath'] . $username . '/' . str_replace(' ','-',$this->name) . '/' . str_replace(' ','-',$this->version) . '/';
            // print_r($dataFolder);
            // exit(0);
            $fileName=$dataFolder . $this->cwlFile->baseName . '.' . $this->cwlFile->extension;
            $this->cwlFile->saveAs($fileName);

            $encfileName=$this->enclose($fileName);
            $encName=$this->enclose($this->name);
            $encVersion=$this->enclose($this->version);

            $command="sudo -u ". Yii::$app->params['systemUser'] . " " . Yii::$app->params['scriptsFolder'] . "inputReplacer.py $encName $encVersion $encfileName 2>&1";

            
            // print_r($command);
            // exit(0);

            exec($command,$outcwl,$ret);


            // Yii::$app->db->createCommand()->update('software',['has_example'=>false], "name='$this->name' AND version='$this->version'")->execute();
            $this->has_example=false;

            // print_r($outcwl);
            // exit(1);

            switch($ret)
            {
                case 0:
                    break;
                case 2:
                    $error.="Error: code $ret. ";
                    $error.="Τhere was an error in the YAML syntax in your CWL file.";
                    $error.="<br />Please correct the file syntax and try again or contact an administrator.";
                    break;
                case 4:
                    $warning.="Warning: code $ret. ";
                    $warning.="You did not provide inputs for the image in your CWL file.";
                    break;
                case 7:
                    $error.="Error: code $ret. ";
                    $error.="You specified an empty baseCommand field.";
                    $error.="<br />Please specify a command and try again or contact an administrator.";
                    break;
                case 9:
                    $warning.="One of the input clauses in the CWL file has no \"inputBinding\" clause and it was ignored.";
                    $warninig.="<br />Please correct the file syntax and try again or contact an administrator.";
                    break;
                case 33:
                    $warning.="One of the input clauses in the CWL file has no \"position\" in \"inputBinding\" and it was ignored.";
                    $warninig.="<br />Please correct the file syntax and try again or contact an administrator.";
                    break;
                case 34:
                    $error.="Error: code $ret. ";
                    $error.="One of the inputs does not have a type.";
                    $error.="<br />Please correct the file syntax and try again or contact an administrator.";
                    break;
                case 35:
                    $error.="Error: code $ret. ";
                    $error.="One of the inputs does has an unknown type.";
                    $error.="<br />Please correct the file syntax and try again or contact an administrator.";
                    break;
                default:
                    $error.="Error: code $ret. ";
                    $error.="<br />An unexpected error occurred.";
                    foreach ($outcwl as $line)
                    {
                        $error.=$line . "<br />";
                    }
                    $error.="<br />Please contact an administrator.";
                    break;
            }
            
            if (empty($error))
            {
                $this->cwl_path=$fileName;
                // Yii::$app->db->createCommand()->update('software',['cwl_path'=>$fileName], "name='$this->name' AND version='$this->version'")->execute();
            }
        }
        $this->save(false);
        
        return [$error,$success,$warning];
    }

    public function enclose($string)
    {
        return "'" . $string . "'";
    }
        
    public function noSlash($attribute, $params, $validator)
    {
        if ($this->$attribute=='/')
        {
                $this->addError($attribute, "You cannot use '/' as a mountpoint");
                return false;
        }
        return true;
    }

    
}
