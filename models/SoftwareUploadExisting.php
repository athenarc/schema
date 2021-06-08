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
class SoftwareUploadExisting extends \yii\db\ActiveRecord
{
    public $cwlFile='';
    public $dois='';
    public $imageInDockerHub=true;
    public $commandRetrieval;
    public $iomount=true;
    public $image;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'software_upload';
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
            [['image'], 'string', 'max' => 200],
            [['imountpoint','omountpoint'], 'string', 'max' => 200],
            [['imountpoint','omountpoint'], 'noSlash'],
            [['workingdir'], 'string', 'max' => 200],
            [['name','version',],'required'],
            [['name',],'allowed_name_chars'],
            [['version',],'allowed_version_chars'],
            [['cwlFile'], 'file', 'extensions' => ['yaml', 'cwl']],
            [['visibility','description'],'required'],
            [['biotools'],'string','max'=>255],
            [['iomount'],'boolean'],
            [['mpi'],'boolean'],
            [['image'],'required'],
            [['version'], 'uniqueSoftware'],
            //[['covid19'],'required'],
            
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        
        return [
            'id' => 'ID',
            'name' => 'Software name * ',
            'version' => 'Software version * ',
            'image' => 'Select image to use',
            'imageFile' => "Upload your own .tar.gz file with the image",
            'cwlFile' => 'Upload your CWL input definition file * ',
            'biotools'=>'Link in bio.tools (optional)',
            'visibility' => 'Visible to',
            'imountpoint'=>'Input folder mount point (where users can provide input data inside the container). Leave empty if no mount is required',
            'omountpoint'=>'Output folder mount point (folder inside the container where users can find the output). Leave empty if no mount is required',
            'workingdir'=>'Working directory (inside the container). If left empty, /data will be used.',
            'description'=> 'Software description * ',
            'imageInDockerHub'=>'Image exists in DockerHub and is specified in the CWL file',
            'iomount' => 'Image requires disk I/O',
            'mpi' => 'Software uses OpenMPI',
            'covid19' => 'Software is related to COVID-19 research',
            'instructions'=>'User instructions'
        ];
    }


    public function upload()
    {
        $errors="";

        $username=User::getCurrentUser()['username'];
        $this->description=$this->quotes($this->description);
        $this->instructions=$this->quotes($this->instructions);
        $this->imountpoint=$this->quotes($this->imountpoint);
        $this->omountpoint=$this->quotes($this->omountpoint);
        $cwlFileName=$this->quotes('');
        $previous=Software::find()->where(['image'=>$this->image])->one();
        $workingdir=$previous->workingdir;
        $original=$previous->original_image;
        $dockerhub=($previous->docker_or_local) ? "'t'" : "'f'";
        $workingdir=$this->quotes($workingdir);
        $original=$this->quotes($original);
        $this->covid19=($this->covid19=='1') ? "'t'" : "'f'";
        $this->biotools=$this->quotes($this->biotools);
        $this->instructions=$this->quotes($this->instructions);

        //add dois string in a file and pass it on to python
        $dataFolder=Yii::$app->params['tmpImagePath'] . $username . '/' . str_replace(' ','-',$this->name) . '/' . str_replace(' ','-',$this->version) . '/';
        if (!is_dir($dataFolder))
        {
            $command="mkdir -p $dataFolder";
            exec($command,$ret,$outdir); 
        }


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
        if (!empty($this->cwlFile))
        {
            $cwlFileName=$dataFolder . $this->cwlFile->baseName . '.' . $this->cwlFile->extension;
            $this->cwlFile->saveAs($cwlFileName);

        }
        $cwlFileName=$this->quotes($cwlFileName);

        $this->name=$this->quotes($this->name);
        $this->version=$this->quotes($this->version);
        $mpi=($this->mpi=='1') ? $this->quotes('t') : $this->quotes('f');
        $username=$this->quotes($username);

        $arguments=[
            $this->name, $this->version, $this->image, $cwlFileName, 
            $username, $this->visibility, $this->imountpoint, $this->omountpoint,
            $this->description, $this->biotools, $doiFile, $mpi, $workingdir,
            $original,$dockerhub,$this->covid19, $this->instructions];

        // $command="sudo -u user /data/www/schema_test/scheduler_files/imageUploader.py ";
        $command=Software::sudoWrap(Yii::$app->params['scriptsFolder'] . "existingImageUploader.py ");
        $command.= implode(" ", $arguments) . " ";
        $command.= "2>&1";

        // print_r($command);
        // print_r("<br />");
        // exit(0);



        exec($command,$out,$ret);


        // print_r($out);
        // print_r("<br /><br />");
        // print_r($ret);
        // exit(0);


        $errors='';
        $warning='';
        $success='';
        $prog_output="";
        switch($ret)
        {
            case 0:
                $success="Image successfully uploaded!";
                exec("rm $doiFile");
                break;
            case 2:
                $errors.="Error: code $ret. ";
                $errors.="Τhere was a problem loading the uploaded image. Please check that the image is a valid Docker image.";
                $errors.="<br />Please contact an administrator.";
                break;
            case 3:
                $errors.="Error: code $ret. ";
                $errors.="No image was uploaded and no Docker image repository location was provided in the CWL definition.";
                $errors.="<br />Please contact an administrator.";
                break;
            case 4:
                $errors.="Error: code $ret. ";
                $errors.="The image uploaded and the image defined in the CWL file are different.";
                $errors.="<br />Please contact an administrator.";
                break;
            case 5:
                $errors.="Error: code $ret. ";
                $errors.="There was a problem pulling the image from the location specified in the CWL file.";
                $errors.="<br />Please contact an administrator.";
                break;
            case 6:
                $errors.="Error: code $ret. ";
                $errors.="The image already exists in the local repository.";
                $errors.="<br />Please contact an administrator.";
                break;
            case 7:
                $errors.="Error: code $ret. ";
                $errors.="There was a problem with Docker.";
                $errors.="<br />Please contact an administrator.";
                break;
            case 8:
                $errors.="Error: code $ret. ";
                $errors.="There was a problem with Docker.";
                $errors.="<br />Please contact an administrator.";
                break;
            case 9:
                $errors.="Error: code $ret. ";
                $errors.="There was a problem with Docker.";
                $errors.="<br />Please contact an administrator.";
                break;
            case 11:
                $errors.="Error: code $ret. ";
                $errors.="You did not provide a CWL file definition file.";
                break;
            case 12:
                $errors.="Error: code $ret. ";
                $errors.="No 'baseCommand' definition was found in the CWL file.";
                break;
            case 13:
                $errors.="Error: code $ret. ";
                $errors.="You are not allowed to use '/' as a mountpoint in the container";
                break;
            case 14:
                $errors.="Error: code $ret. ";
                $errors.="The system cannot find a working directory in the image configuration.";
                break;
            case 15:
                $errors.="Error: code $ret. ";
                $errors.="The system cannot find a command in the image configuration.";
                break;
            case 22:
                $errors.="Error: code $ret. ";
                $errors.="There is more than one Docker image declared in the CWL file";
                $errors.="<br />Please contact an administrator.";
                break;
            case 23:
                $errors.="Error: code $ret. ";
                $errors.="There is different Docker images declared in the 'hints' and 'requirements' section of the CWL file";
                $errors.="<br />Please contact an administrator.";
                break;
            case 24:
                $errors.="Error: code $ret. ";
                $errors.="There was an error during software classification.";
                $errors.="<br />Please contact an administrator.";
                break;
            case 26:
                $errors.="Error: code $ret. ";
                $errors.="There was an problem decoding your CWL file:<br />";
                foreach ($out as $line)
                {
                    $errors.=$line . "<br />";
                }
                $errors.="<br />Please correct the file syntax and try again or contact an administrator.";
                break;
            case 30:
                $success.="Image successfully uploaded!<br />";
                $warning.="Warning: code $ret. ";
                $warning.="You did not provide inputs for the image in your CWL file.";
                break;
            case 31:
                $success.="Image successfully uploaded!<br />";
                $warning.="Warning: code $ret. ";
                $errors.="";
                break;
            case 32:
                $success.="Image successfully uploaded!<br />";
                $warning.="One of the input clauses in the CWL file has no \"inputBinding\" clause and it was ignored.";
                $warninig.="<br />Please correct the file syntax and try again or contact an administrator.";
                break;
            case 33:
                $success.="Image successfully uploaded!<br />";
                $warning.="One of the input clauses in the CWL file has no \"position\" in \"inputBinding\" and it was ignored.";
                $warninig.="<br />Please correct the file syntax and try again or contact an administrator.";
                break;
            case 34:
                $errors.="Error: code $ret. ";
                $errors.="One of the inputs does not have a type.";
                $errors.="<br />Please correct the file syntax and try again or contact an administrator.";
                break;
            case 35:
                $errors.="Error: code $ret. ";
                $errors.="One of the inputs does has an unknown type.";
                $errors.="<br />Please correct the file syntax and try again or contact an administrator.";
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
        $images=Software::find()->where(['name'=>$this->name, 'version'=>$this->version])->all();
        if (!empty($images))
        {
                $this->addError($attribute, "Software $this->name v.$this->version already exists. Please specify another name or version.");
                return false;
        }
        return true;
    }

    public function noSlash($attribute, $params, $validator)
    {
        if ($this->$attribute=='/')
        {
                $this->addError($attribute, "You cannot use '/' as a mountpoint.");
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
