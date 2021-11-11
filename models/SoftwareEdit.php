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

namespace app\models;

use Yii;
use yii\db\Query;
use app\models\Software;
use webvimark\modules\UserManagement\models\User;


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
        if (User::hasRole("Admin", $superAdminAllowed = true))
        {
            $sharedRules=[
                [['shared'],'boolean'],
                [['shared'],'required']
            ];
        }
        else
        {
            $sharedRules=[];
        }
        
        $rules=[
            [['description', 'instructions'], 'string',],
            [['description'], 'required',],
            [['visibility'], 'required',],
            [['imountpoint','omountpoint'], 'string',],
            [['imountpoint','omountpoint'], 'noSlash'],
            [['workingdir'], 'string',],
            [['biotools'],'string'],
            [['iomount'],'boolean'],
            [['gpu'],'boolean'],
            [['gpu'],'required'],
            [['cwlFile'], 'file', 'extensions' => ['yaml', 'cwl']],

        ];

        $rules=array_merge($rules,$sharedRules);

        return $rules;
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
            'shared' => 'Software needs reference data from the shared folder',
            'instructions'=>'User instructions',
            'gpu' => 'Software requires GPUs'
        ];
    }

    public function softwareEdit()
    {

        
        $this->shared=($this->shared=="1") ? true : false;
        $this->gpu=($this->gpu=="1") ? true : false;

        $error='';
        $success='Software details successfully updated!';
        $warning='';
        if (!empty($this->cwlFile))
        {
            $dataFolder=Yii::$app->params['tmpImagePath'] . $this->uploaded_by . '/' . str_replace(' ','-',$this->name) . '/' . str_replace(' ','-',$this->version) . '/';
            
            $fileName=$dataFolder . $this->cwlFile->baseName . '.' . $this->cwlFile->extension;
            $this->cwlFile->saveAs($fileName);

            $encfileName=$this->enclose($fileName);
            $encName=$this->enclose($this->name);
            $encVersion=$this->enclose($this->version);

            $command=Software::sudoWrap(Yii::$app->params['scriptsFolder'] . "inputReplacer.py $encName $encVersion $encfileName 2>&1");

            Software::exec_log($command,$outcwl,$ret);

            $this->has_example=false;


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
                    $warning.="<br />Please correct the file syntax and try again or contact an administrator.";
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
