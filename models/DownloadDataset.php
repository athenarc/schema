<?php

namespace app\models;

use Yii;
use yii\httpclient\Client;
use webvimark\modules\UserManagement\models\User as Userw;



/**
 * This is the model class for table "dataset".
 *
 * @property int $id
 * @property string $folder_path
 * @property string $download_url
 * @property string $provider
 */
class DownloadDataset extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'download_dataset';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['folder_path', 'dataset_id', 'provider', 'name', 'version'], 'string'],
            ['dataset_id','required'],
            ['user_id', 'integer'],
            ['date','safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'folder_path' => 'Folder Path',
            'dataset_id' => 'Dataset id',
            'provider' => 'Provider',
        ];
    }

    public function downloadHelixDataset($folder,$dataset_id,$provider)
    {
        $error='';
        $warning='';
        $success='';

        $client = new Client(['baseUrl' => 'https://data.hellenicdataservice.gr/api']);
        $response = $client->createRequest()
        ->setUrl("action/package_show?id=$dataset_id")
        ->send();
        
        $content=json_decode($response->content);
        $title='';
        $version='';
       
        if (empty($folder))
        {
            $warning="You must choose a folder to store the dataset";
            return ['warning'=>$warning];
        }
        elseif (!$content->success==1)
        {
            $error='The dataset id you provided is not valid';
            return ['error'=>$error];
        }
        else
        {
            $title=$content->result->title;
            $version=$content->result->version;
            $resources=$content->result->resources;
            
            $finalFolder=Yii::$app->params['userDataPath'] . '/' . explode('@',Userw::getCurrentUser()['username'])[0] . '/' . $folder . '/'. "Helix_Dataset_" . $dataset_id . '/';
            

            exec("mkdir $finalFolder");

            foreach ($resources as $res)
            {
                if (empty($res->mimetype))
                {
                    $command="wget  -r -np -R 'index.html*' -P $finalFolder $res->url";
                }
                else
                {
                    $command="wget -nc -P $finalFolder $res->url";
                }

                exec($command,$out,$ret);
                
                if ($ret!=0)
                {
                    $warning="The dataset contains resources that can not be downloaded. Please visit https://data.hellenicdataservice.gr/dataset/$dataset_id/ to get access to the files";

                   
                }
                else
                {
                    $success='The dataset has been successfully downloaded';
                   
                }
            }
        }
        
        return ['error'=>$error,'warning'=>$warning,'success'=>$success,'title'=>$title,'version'=>$version];
    }

    public function downloadZenodoDataset($folder,$dataset_id,$provider)
    {   
        $error='';
        $warning='';
        $success='';


        $client = new Client(['baseUrl' => "https://zenodo.org/api/records/$dataset_id"]);
        $response = $client->createRequest()
        ->setMethod('GET')
        ->send();

        $status=$response->headers['http-code'];

        if (empty($folder))
        {
            $warning="You must choose a folder to store the dataset";
            return ['warning'=>$warning];
        }
        elseif ($status!='200')
        {
            $error=json_decode($response->content,true)['message'];
            return ['error'=>$error];
        }
        else
        {

            $content=json_decode($response->content,true);
            $title=$content['metadata']['title'];
            $version=$content['metadata']['version'];
            $resources=$content['files'];
            $finalFolder=Yii::$app->params['userDataPath'] . '/' . explode('@',Userw::getCurrentUser()['username'])[0] . '/' . $folder . '/'. "Zenodo_Dataset_" . $dataset_id . '/';
            
            if(!is_dir($finalFolder))
            {
                exec("mkdir $finalFolder");
            }
            
            foreach ($resources as $res)
            {
                
                $command="wget -nc -P $finalFolder ". $res['links']['self'];
                exec($command,$out,$ret);

                if ($ret!=0)
                {
                    $warning="The dataset contains resources that can not be downloaded. 
                    Please visit https://zenodo.org/ to get access to the files";
                    
                    return ['warning'=>$warning];
                }
                else
                {
                    $success='The dataset has been successfully downloaded.';
                   
                }
            
            }
                    
        }
        
        return ['error'=>$error,'warning'=>$warning,'success'=>$success, 'title'=>$title,'version'=>$version];
     }


}
