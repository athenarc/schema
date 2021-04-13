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
use yii\helpers\Html;
use yii\helpers\Url;
use webvimark\modules\UserManagement\models\User;
use app\models\SoftwareInput;
use app\models\Software;
use app\models\Workflow;


/**
 * This is the model class for table "ro_crate".
 *
 * @property int $id
 * @property string $username
 * @property string $jobid
 * @property string $date
 * @property string $software_url
 * @property string $input
 * @property string $publication
 * @property string $output
 */
class RoCrate extends \yii\db\ActiveRecord
{
    public $local_download;

    
    public static function tableName()
    {
        return 'ro_crate';
    }

   
    public function rules()
    {
        return [
            [['username', 'jobid', 'software_url', 'input', 'publication', 'output'], 'string'],
            [['date'], 'safe'],
            [['software_url','output', 'input'],'url'],
            [['software_url','input'],'required'],
            ['local_download', 'boolean'],
            ['public', 'boolean'],
            ['experiment_description', 'string']
            
        ];
    }

   
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'jobid' => 'Jobid',
            'date' => 'Date',
            'software_url' => 'Software Url',
            'input' => 'Input',
            'publication' => 'Publication',
            'output' => 'Output',
        ];
    }

    public function CreateROObjectSoftware($jobid, $software_name,$software_version,$software_url,$input_data,$output_data,$publication,$experiment_description,$public_value)
    {
        
        $software=Software::find()->where(['name'=>$software_name])->andWhere(['version'=>$software_version])->one();
        $software_id=$software->id;
        $docker=$software->docker_or_local;
        $software_description=$software->description;
        $location=$software->cwl_path;
        $creator=explode('@',$software->uploaded_by)[0];
        
        

        $ROCratesFolder=Yii::$app->params['ROCratesFolder'];

        if (!is_dir($ROCratesFolder))
        {
            exec("mkdir $ROCratesFolder");
        }
        exec("chmod 777 $ROCratesFolder -R 2>&1",$out,$ret);

        $fields=SoftwareInput::find()->where(['softwareid'=>$software_id])->orderBy(['position'=> SORT_ASC])->all();
        $fields=Software::getRerunFieldValues($jobid,$fields);
        $field_names=[];
        foreach ($fields as $field) 
        {
           $field_names[]=$field->name;
        }
        
        $arguments=['software_name'=>$software_name, 
                    'software_version'=>$software_version,
                    'software_description'=>$software_description, 
                    'software_url'=>$software_url,
                    'output_data'=>['id'=>uniqid(), 'data'=>$output_data], 
                    'publication'=>$publication, 
                    'ROCratesFolder'=>$ROCratesFolder, 
                    'location'=>$location,
                    'creator'=>$creator, 
                    'jobid'=>$jobid,
                    'input_data'=>$input_data, 
                    'number_of_inputs'=>count($input_data),
                    'field_names'=>$field_names,
                    'version'=>$software_version,
                    'image'=>null,
                    'experiment_description'=>$experiment_description,
                    
        ];


        $username=User::getCurrentUser()['username'];

        $query=Yii::$app->db->createCommand()->delete('ro_crate',["jobid" => $jobid])->execute();
     
        $query=Yii::$app->db->createCommand()->insert('ro_crate',
        [
            "username"=>$username,
            "jobid" => $jobid,
            'date'=>'NOW()',
            'output'=>$output_data,
            'input'=>json_encode($input_data),
            'publication'=>$publication,
            'software_url'=>$software_url,
            'experiment_description'=>$experiment_description,
            'public'=>$public_value,
        ]
        )->execute();
        

        $filepath=Yii::$app->params['ROCratesFolder'] . $jobid . '-arguments.json';
        $arguments_file = fopen($filepath, "w");
        fwrite($arguments_file, json_encode($arguments));
        fclose($arguments_file);

        $command=Software::sudoWrap(Yii::$app->params['scriptsFolder'] . "ro-crate.py $filepath 2>&1");   

        exec($command,$out,$ret);    

        // print_r($out);
        // exit(0);

        $success="ROCrate object has been created. You can download the ROCrate object by clicking " . 
        Html::a('here', ['software/download-rocrate', 'jobid'=>$jobid]). ".";

        // exec($command,$out,$ret);

        
        
        return [$software, $success];

    }

    public function CreateROObjectWorkflow($jobid, $software_name,$software_version,$software_url,$input_data,$output_data,$publication,$experiment_description,$public_value)
    {
        
        $workflow=Workflow::find()->where(['name'=>$software_name])->andWhere(['version'=>$software_version])->one();
        $workflow_id=$workflow->id;
       

        $workflow_description=$workflow->description;
        $location=$workflow->original_file;
        $creator=explode('@',$workflow->uploaded_by)[0];
        $url = Url::base('https');
        $image=$url. "/img/workflows/$workflow->visualize";
        
        

        $ROCratesFolder=Yii::$app->params['ROCratesFolder'];

        if (!is_dir($ROCratesFolder))
        {
            exec("mkdir $ROCratesFolder");
        }
        exec("chmod 777 $ROCratesFolder -R 2>&1",$out,$ret);

        $fields=WorkflowInput::find()->where(['workflow_id'=>$workflow_id])->orderBy(['position'=> SORT_ASC])->all();
        $fields=Workflow::getRerunFieldValues($jobid,$fields);
        $field_names=[];
        foreach ($fields as $field) 
        {
           $field_names[]=$field->name;
        }
        
        $arguments=['software_name'=>$software_name, 
                    'software_version'=>$software_version,
                    'software_description'=>$workflow_description, 
                    'software_url'=>$software_url,
                    'output_data'=>['id'=>uniqid(), 'data'=>$output_data], 
                    'publication'=>$publication, 
                    'ROCratesFolder'=>$ROCratesFolder, 
                    'location'=>$location,
                    'creator'=>$creator, 
                    'jobid'=>$jobid,
                    'input_data'=>$input_data, 
                    'number_of_inputs'=>count($input_data),
                    'field_names'=>$field_names,
                    'image'=>$image,
                    'experiment_description'=>$experiment_description,
                    
                    
        ];


        $username=User::getCurrentUser()['username'];

        $query=Yii::$app->db->createCommand()->delete('ro_crate',["jobid" => $jobid])->execute();
     
        $query=Yii::$app->db->createCommand()->insert('ro_crate',
        [
            "username"=>$username,
            "jobid" => $jobid,
            'date'=>'NOW()',
            'output'=>$output_data,
            'input'=>json_encode($input_data),
            'publication'=>$publication,
            'software_url'=>$software_url,
            'experiment_description'=>$experiment_description,
            'public'=>$public_value,
        ]
        )->execute();
        

        $filepath=Yii::$app->params['ROCratesFolder'] . $jobid . '-arguments.json';
        $arguments_file = fopen($filepath, "w");
        fwrite($arguments_file, json_encode($arguments));
        fclose($arguments_file);

        $command=Software::sudoWrap(Yii::$app->params['scriptsFolder'] . "ro-crate.py $filepath 2>&1");       

        $success="ROCrate object has been created. You can download the ROCrate object by clicking ". 
        Html::a('here', ['software/download-rocrate', 'jobid'=>$jobid]). ".";

        exec($command,$out,$ret);

        
        return [$workflow, $success];

    }
}
