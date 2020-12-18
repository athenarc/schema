<?php

namespace app\models;

use Yii;
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

    public function CreateROObject($jobid, $software_name,$software_version,$software_url,$input_data,$output_data,$publication)
    {
        
        $software=Software::find()->where(['name'=>$software_name])->andWhere(['version'=>$software_version])->one();
        $software_id=$software->id;
        $docker=$software->docker_or_local;
        if(empty($software))
        {
            $software=Workflow::find()->where(['name'=>$software_name])->andWhere(['version'=>$software_version])->one();
        
        }

        $software_description=$software->description;
        $location=empty($software->location)?"$software->cwl_path":"$software->location";
		$creator=explode('@',$software->uploaded_by)[0];
        // $creator_name=ucfirst(explode('_',$creator)[0]);
        // $creator_surname=ucfirst(explode('_',$creator)[1]);
        // $creator_fullname=$creator_name.' '.$creator_surname;
        

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
        ]
        )->execute();
        

        $filepath=Yii::$app->params['ROCratesFolder']. 'arguments.json';
        $arguments_file = fopen($filepath, "w");
        fwrite($arguments_file, json_encode($arguments));
        fclose($arguments_file);

        $command="sudo -u ". Yii::$app->params['systemUser'] . " " . Yii::$app->params['scriptsFolder'] . 
        "ro-crate.py ";
        $command.= "2>&1";       

        $success='ROCrate object has been created';

        exec($command,$out,$ret);
		
		return [$software, $success];

        
    }
}
