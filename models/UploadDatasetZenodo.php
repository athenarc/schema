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


class UploadDatasetZenodo extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */

    public $creators_name=[];
    public $creators_affiliation=[];
    public $creators_orcid=[];

    public static function tableName()
    {
        return 'upload_dataset_zenodo';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['dataset_id', 'provider', 'api_key', 'upload_type', 'publication_type', 'image_type','title',
            'creators','description', 'access_rights', 'license', 'access_conditions', 
            'doi',], 'string'],
            [['publication_type', 'image_type','title','description', 'access_rights',], 'required'],
            [['provider', 'api_key'], 'required'],
            [['title','description'],'string', 'min'=>3],
            [['creators_name', 'creators_affiliation'], 'required'],
            
            ['embargo_date', 'required', 'when'=> function($model_zenodo)
            { 
            	return $model_zenodo->access_rights=='embargoed';
            },
            	'whenClient' => "function (attribute, value)
            	{ 
              		return $('#rights').val() == 'embargoed';
         		}"
     		],
            ['access_conditions', 'required', 'when'=> function($model_zenodo)
            { 
            	return $model_zenodo->access_rights=='restricted';

            },	
            	'whenClient' => "function (attribute, value)
            	{ 
              		return $('#rights').val() == 'restricted';
         		}" 
         	]
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

    public function getZenodoDefaults()
    {
    	$zenodo_upload_type=['poster'=>'Poster', 'publication'=>'Publication', 'presentation'=>'Presentation', 
    				'dataset'=>'Dataset', 'image'=>'Image', 'video'=>'Video/Audio', 'software'=>'Software',
				     'lesson'=>'Lesson', 'physicalobject'=>'Physical object', 'other'=>'Other'];

		$zenodo_publication_type=[
				'annotationcollection'=>'Annotation collection',
				'book'=>'Book',
				'section'=>'Book section',
				'conferencepaper'=>'Conference paper',
				'datamanagementplan'=>'Data management plan',
				'article'=>'Journal article',
				'patent'=>'Patent',
				'preprint'=>'Preprint',
				'deliverable'=>'Project deliverable',
				'milestone'=>'Project milestone',
				'proposal'=> 'Proposal',
				'report'=>'Report',
				'softwaredocumentation'=>'Software documentation',
				'taxonomictreatment'=>'Taxonomic treatment',
				'technicalnote'=>'Technical note',
				'thesis'=>'Thesis',
				'workingpaper'=>'Working paper',
				'other'=> 'Other',];

		$zenodo_image_type=['figure'=>'Figure', 'plot'=>'Plot', 'drawing'=>'Drawing', 'diagram'=>'Diagram', 'photo'=>'Photo', 'other'=>'Other'];

		$zenodo_access_rights=['closed'=>'Closed Access', 'open'=>'Open Access', 'embargoed'=>'Embargoed Access',
					'restricted'=>'Restricted Access'];


		$client = new Client(['baseUrl' => "https://zenodo.org/api/licenses"]);
        $response = $client->createRequest()
        ->setMethod('GET')
        ->setFormat(Client::FORMAT_JSON)
       	->addHeaders(['content-type' => 'application/json'])
        ->send();

        $results=json_decode($response->content, true);
        $results=$results['hits']['hits'];
        $zenodo_licenses=[];
        foreach($results as $result)
        {
        	
        	$zenodo_licenses[$result['metadata']['suggest']['input'][0]]=$result['metadata']['suggest']['input'][1];
        }
		


		return ['zenodo_upload_type'=>$zenodo_upload_type, 'zenodo_publication_type'=>$zenodo_publication_type,
				'zenodo_access_rights'=>$zenodo_access_rights, 'zenodo_image_type'=>$zenodo_image_type, 'zenodo_licenses'=>$zenodo_licenses];
    }



    public function uploadZenodoDataset($dataset_path,$provider,$api_key, $title, $description, $upload_type,$publication_type, $image_type, $access_rights,$access_conditions,$license, $doi,$embargo_date,$creators_array)
    {
        
        $error='';
        $success='';

       	$data=["metadata"=>
        	[
        	"upload_type"=>$upload_type, 
        	"title"=>$title, 
        	"description"=>$description,
        	"publication_type"=>$publication_type,
        	"image_type"=>$image_type,
        	'access_right'=>$access_rights,
        	'access_conditions'=>$access_conditions,
        	'license'=>$license,
        	'doi'=>$doi,
        	'embargo_date'=>$embargo_date,
        	'creators'=>$creators_array,
        	]
        ];
        
        $client = new Client(['baseUrl' => "https://zenodo.org/api/deposit/depositions"]);
        $response = $client->createRequest()
        ->setMethod('POST')
        ->setFormat(Client::FORMAT_JSON)
        ->setUrl(["access_token" => $api_key])
        ->addHeaders(['content-type' => 'application/json'])
        ->setContent(json_encode($data))
        ->send();

        
        

        $status=$response->headers['http-code'];

        if ($status!='201')
        {
            $error=$response->content;
            return ['error'=>$error];
        }
        else
        {
            $dataset_path_absolute=Yii::$app->params['userDataPath'] . explode('@',Userw::getCurrentUser()['username'])[0]
            . '/'.$dataset_path;

            $files=Filehelper::findFiles($dataset_path_absolute);

            
            $content=json_decode($response->content,true);
            $files_link=$content['links']['files'];
            $publish_link=$content['links']['publish'];
            $deposit_id=$content['id'];
            
            $error_file='';
            session_write_close();
            foreach($files as $file)
            {
              $client = new Client(['baseUrl' => $files_link]);
              $response = $client->createRequest()
              ->setMethod('POST')
              ->setUrl(["access_token" => $api_key])
              ->addHeaders(['content-type' => 'multipart/form-data'])
              ->setData(['name'=>basename($file)])
              ->addFile('file', $file)
              ->send();
            }

            $content=json_decode($response, true);
            if(empty($content))
            {
               
                $client = new Client(['baseUrl' => $publish_link]);
                $response= $client->createRequest()
                ->setMethod('POST')
                ->setFormat(Client::FORMAT_JSON)
                ->setUrl(["access_token" => $api_key])
                ->send();

                $content=json_decode($response, true);
                if (!empty($content))
                {
                  
                  return ['error'=>$content];
                }
            }
            session_start();
           
            $success='The dataset has been successfully uploaded to Zenodo.';
            return ['success'=>$success];
        }



    }

   


}
