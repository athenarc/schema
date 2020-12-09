<?php


namespace app\models;

use Yii;
use yii\base\Model;
use app\models\Software;
use app\models\Workflow;

class ROCrate extends Model
{
    
    public $software_name;
    public $software_version;
    public $software_url;
    public $input_data;
    public $output_data;
    public $publication;  
    public $schema;
    public $local_download;

    public function rules()
    {
        return [
            // Application Name
        
            [['software_name','software_version','software_url','input_data','output_data','publication'],'string'],
            [['software_url','input_data','output_data'],'url'],
            [['schema','local_download'], 'boolean']
        ];
    }




    public function CreateROObject($jobid, $software_name,$software_version,$software_url,$input_data,$output_data,$publication)
    {
        $software=Software::find()->where(['name'=>$software_name])->andWhere(['version'=>$software_version])->one();
        if(empty($software))
        {
            $software=Workflow::find()->where(['name'=>$software_name])->andWhere(['version'=>$software_version])->one();
        
        }

        // print_r($software);
        // exit(0);
        
        $software_description=$software->description;
        $location=empty($software->location)?"$software->cwl_path":"$software->location";
        $creator=explode('@',$software->uploaded_by)[0];

        $ROCratesFolder=Yii::$app->params['ROCratesFolder'];
        if (!is_dir($ROCratesFolder))
        {
            exec("mkdir $ROCratesFolder");
        }
        exec("chmod 777 $ROCratesFolder -R 2>&1",$out,$ret);
        

        $arguments=[self::quotes($software_name), self::quotes($software_version),self::quotes($software_description), self::quotes($software_url), 
            self::quotes($input_data), self::quotes($output_data), 
            self::quotes($publication), self::quotes($ROCratesFolder), self::quotes($location),self::quotes($creator), self::quotes($jobid.uniqid())];

        $command="sudo -u ". Yii::$app->params['systemUser'] . " " . Yii::$app->params['scriptsFolder'] . 
        "ro-crate.py ";

        $command.= implode(" ", $arguments) . " ";
        $command.= "2>&1";

        $success='ROCrate object has been created';

        exec($command,$out,$ret);
        
        return [$software, $success];

        
    }

    public function quotes($string)
    {
        return "'" . trim($string) . "'";
    }
}