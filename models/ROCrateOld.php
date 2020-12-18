<?php


namespace app\models;

use Yii;
use yii\base\Model;
use app\models\Software;
use app\models\Workflow;

class ROCrate extends \yii\db\ActiveRecord
{
    
    public $software_name;
    public $software_version;
    public $software_url;
    public $input_data=[];
    public $output_data;
    public $publication;  
    public $local_download;
    public $date;
    public $jobid;
    public $username;

    public static function tableName()
    {
        return 'ro_crate';
    }

    public function rules()
    {
        return [
          
        
            [['software_name','software_version','software_url','output_data','publication','username'],'string'],
            [['software_url','output_data', 'input_data'],'url'],
            ['jobid','integer'],
            [['software_url','input_data'],'required'],
            ['local_download', 'boolean'],
            ['date', 'safe']
        ];
    }




    public function CreateROObject($jobid, $software_name,$software_version,$software_url,$input_data,$output_data,$publication,$number_of_inputs)
    {
        
        $software=Software::find()->where(['name'=>$software_name])->andWhere(['version'=>$software_version])->one();
        if(empty($software))
        {
            $software=Workflow::find()->where(['name'=>$software_name])->andWhere(['version'=>$software_version])->one();
        
        }

        $software_description=$software->description;
        $location=empty($software->location)?"$software->cwl_path":"$software->location";
        $creator=explode('@',$software->uploaded_by)[0];

        $ROCratesFolder=Yii::$app->params['ROCratesFolder'];

        if (!is_dir($ROCratesFolder))
        {
            exec("mkdir $ROCratesFolder");
        }
        exec("chmod 777 $ROCratesFolder -R 2>&1",$out,$ret);
        
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
                    'number_of_inputs'=>$number_of_inputs,
                    
        ];
        

        $filepath=Yii::$app->params['ROCratesFolder']. 'arguments.json';
        $arguments_file = fopen($filepath, "w");
        fwrite($arguments_file, json_encode($arguments));
        fclose($arguments_file);

        $command="sudo -u ". Yii::$app->params['systemUser'] . " " . Yii::$app->params['scriptsFolder'] . 
        "ro-crate.py ";
        $command.= "2>&1";       

        $success='ROCrate object has been created';

        exec($command,$out,$ret);

        // print_r($out);
        // exit(0);
        
        return [$software, $success];

        
    }

    public function quotes($string)
    {
        return "'" . trim($string) . "'";
    }


}