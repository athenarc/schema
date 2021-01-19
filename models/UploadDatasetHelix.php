<?php

namespace app\models;

use Yii;
use yii\httpclient\Client;
use yii\authclient\OAuth2;
use webvimark\modules\UserManagement\models\User as Userw;
use yii\helpers\FileHelper;
use yii\db\Query;

/**
 * This is the model class for table "upload_dataset".
 *
 * @property int $id
 * @property string $dataset_id
 * @property string $provider
 * @property int $user_id
 * @property string $api_key
 */
class UploadDatasetHelix extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'upload_dataset';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['dataset_id', 'provider', 'api_key','description', 'publication_doi','contact_email','creator',
        	'affiliation','license', 'subject', 'title'], 'string'],
        	[['provider', 'api_key','description','contact_email','creator',
        	'affiliation','license', 'subject', 'title'], 'required'],
            ['contact_email','email'],
            [['user_id'], 'default', 'value' => null],
            [['user_id'], 'integer'],
            [['private'], 'boolean'],
            ['date','safe']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'dataset_id' => 'Dataset ID',
            'provider' => 'Provider',
            'user_id' => 'User ID',
            'api_key' => 'Api Key',
        ];
    }


    public function uploadHelixDataset($dataset_path,$provider,$api_key, $title, $description, $dataset_id,$publication_doi,$private,$license,$subjects,$creator,$contact_email,$affiliation)
    {
        $error='';
        $success='';
        // print_r(json_encode($subjects));
        // exit(0);
        
        $dataset_path_absolute=Yii::$app->params['userDataPath'] . explode('@',Userw::getCurrentUser()['username'])[0]
        . '/'.$dataset_path;

        $files=Filehelper::findFiles($dataset_path_absolute);
        // print_r(json_encode(['Pure Mathematics','Pure Mathematics not elsewhere classified']));
        // exit(0);
        
        $client = new Client(['baseUrl' => 'https://hardmin-dev.heal-link.gr/api/3/action/package_create']);
        $response = $client->createRequest()
            ->setMethod('POST')
            ->setData(['title' => $title, 
                'notes'=>$description,
                'closed_tag'=>[],
                'name'=>$dataset_id, 
                'datacite.creator.creator_name'=>$creator,
                'datacite.creator.creator_affiliation'=>$affiliation, 
                'datacite.contact_email'=>$contact_email,
                'private'=>$private,
                'license_id'=>$license,
                'owner_org'=>'af33beea-1b64-4052-ac78-e2bf174cc8bd', 
                'dataset_type'=>'datacite',])
            ->addHeaders(['Authorization'=>$api_key])
            ->send();

        $content=json_decode($response->content, true);
        
        if($content['success']==1)
        {
            $id=$content['result']['id'];
            // print_r($id);
            // exit(0);
            // $i=0;
            foreach($files as $file)
            {
                $id=$content['result']['id'];
                $client = new Client(['baseUrl' => 'https://hardmin-dev.heal-link.gr/api/3/action/resource_create']);
                $response = $client->createRequest()
                ->setMethod('POST')
                ->setData(['package_id' => $id, 'url'=>$file, 'name'=>basename($file)])
                ->addHeaders(['Authorization'=>$api_key])
                ->send();
                // $i++;
                // if($i==3)
                // {
                //     break;
                // }
            }

            //print_r($response);

            
            $success='The dataset has been successfully uploaded to Helix';

            // $client = new Client(['baseUrl' => 'https://hardmin-dev.heal-link.gr/api/3/action/package_delete']);
            // $response = $client->createRequest()
            // ->setMethod('POST')
            // ->setData(['id' => $id])
            // ->addHeaders(['Authorization'=>$api_key])
            // ->send();

           // exit(0);

            

            return ['success'=>$success];
        }
        else
        {
            $error='There was an error uploading the dataset to Helix';
            return ['error'=>$error];
        }
    }

    public static function getSubjectsAutoComplete($expansion, $max_num = 5, $term)
    {
        $query=new Query;

        $expansion = strtolower($expansion);
        if($expansion == "left")
        {
            $search_type = $term;
        }
        else if($expansion == 'right')
        {
            $search_type = $term;
        }
        else
        {
            $search_type = $term;
        }
        $rows=$query->select(["levenshtein(name, '$term') as dist","name"])
              ->from('helix_subjects')
              ->where(['ilike',"name",$search_type])
              ->limit($max_num)
              ->orderBy(['dist' => SORT_ASC])
              ->all();
        
        $results=[];
        foreach ($rows as $row)
        {
             $results[]=$row['name'];
        }
        return json_encode($results);
    }


}
