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
use yii\httpclient\Client;
use yii\authclient\OAuth2;
use webvimark\modules\UserManagement\models\User as Userw;
use yii\helpers\FileHelper;
use yii\db\Query;
use app\models\UploadDatasetDefaults;

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
        return 'upload_dataset_helix';
    }



    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        
        return [
            [['dataset_id', 'provider', 'api_key','description', 'publication_doi','contact_email','creator',
        	'affiliation','license', 'subject', 'title'], 'string'],
            [['title'],'string', 'min'=>6],
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

    public function getHelixDefaults()
    {

        $helix_licenses=  [
            
            'CC-BY'=>'CC-BY 4.0 - Creative Commons Attribution 4.0 International',
            'CC-BY-SA'=>'CC-BY-SA 4.0 - Creative Commons Attribution-ShareAlike 4.0 International',
            'CC-BY-NC'=>'CC-BY-NC 4.0 - Creative Commons Attribution-NonCommercial 4.0 International',
            'CC-BY-NC-SA'=>'CC-BY-NC-SA 4.0 - Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International',
            'ODC-By'=>'Open Data Commons Attribution License',
            'ODbL'=>'Open Data Commons Open Database License',
            'PDDL'=>'Open Data Commons Public Domain Dedication and License',
        ];

        $helix_defaults=UploadDatasetDefaults::find()->where(['provider'=>'Helix'])->one();
        $helix_provider_id=$helix_defaults->provider_id;
        $helix_community_id=$helix_defaults->default_community_id;

        return ['helix_licenses'=>$helix_licenses, 'helix_provider_id'=>$helix_provider_id, 'helix_community_id'=>$helix_community_id ] ;
    }


    public static function uploadHelixDataset($dataset_path,$provider,$api_key, $title, $description,$publication_doi,$private,$license,$subjects,$creator,$contact_email,$affiliation)
    {
        $error='';
        $success='';
        $dataset_path_absolute=Yii::$app->params['userDataPath'] . explode('@',Userw::getCurrentUser()['username'])[0]
        . '/'.$dataset_path;

        $files=Filehelper::findFiles($dataset_path_absolute);

        $helix_defaults=UploadDatasetHelix::getHelixDefaults();
        $helix_provider_id=$helix_defaults['helix_provider_id'];
        $helix_community_id=$helix_defaults['helix_community_id'];

        $client = new Client(['baseUrl' => 'https://data.hellenicdataservice.gr/api/action/package_create']);
        $response = $client->createRequest()
            ->setMethod('POST')
            ->setFormat(Client::FORMAT_JSON)
            ->setData(['title' => $title, 
                'notes'=>$description,
                'closed_tag'=>$subjects,
                'datacite.creator.creator_name'=>$creator,
                'datacite.creator.creator_affiliation'=>$affiliation, 
                'datacite.contact_email'=>$contact_email,
                'private'=>$private,
                'license_id'=>$license,
                'groups'=>[['id'=>$helix_community_id]],
                'owner_org'=>$helix_provider_id, 
                'dataset_type'=>'datacite'
            ])
            ->addHeaders(['Authorization'=>$api_key])
            ->send();

        $content=json_decode($response->content, true);
        
        sleep(2);

        if($content['success']==1)
        {
            
            $id=$content['result']['id'];

            foreach($files as $file)
            {
                $id=$content['result']['id'];
                $client = new Client(['baseUrl' => 'https://data.hellenicdataservice.gr/api/action/resource_create']);
                $response = $client->createRequest()
                ->setMethod('POST')
                ->setFormat(Client::FORMAT_JSON)
                ->setData(['package_id' => $id, 'url'=>$file, 'name'=>basename($file)])
                ->addHeaders(['Authorization'=>$api_key])
                ->send();
                
            }

            $content=json_decode($response->content, true);

            if($content['success']!=1)
            {
                $error=$content['error'];
                return ['error'=>$error];
            }
            else
            {
                $success='The dataset has been successfully uploaded to Helix';
                return ['success'=>$success];
            }
        }
        else
        {
            $error=$content['error'];
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
