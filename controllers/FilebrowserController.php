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

namespace app\controllers;

use alexantr\elfinder\CKEditorAction;
use alexantr\elfinder\ConnectorAction;
use alexantr\elfinder\InputFileAction;
use alexantr\elfinder\TinyMCEAction;
use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use webvimark\modules\UserManagement\models\User as Userw;
use \app\models\CovidDatasetApplication;
use \app\models\User;
use app\models\UploadDatasetZenodo;
use app\models\UploadDatasetHelix;
use app\models\DownloadDataset;
use app\models\Software;
use \app\models\Notification;
use yii\helpers\Url;
use yii\data\Pagination;
use yii\web\UploadedFile;
use yii\httpclient\Client;
use app\models\UploadDatasetDefaults;
use yii\db\Query;


class FilebrowserController extends Controller
{
    public $freeAccess = false;
    public function behaviors()
    {
        return [
            'ghost-access'=> [
                'class' => 'webvimark\modules\UserManagement\components\GhostAccessControl',
            ],
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actions()
    {
       
        if(Yii::$app->user->getIsGuest())
        {
            return [];
            
        }

        return [
            'connector' => [
                'class' => ConnectorAction::className(),
                'options' => [
                    'roots' => [
                        [
                            'driver' => 'LocalFileSystem',
                            // 'path' => Yii::getAlias('@webroot') . DIRECTORY_SEPARATOR . 'uploads',
                            'path' => Yii::$app->params['userDataPath'] . '/' . explode('@',Userw::getCurrentUser()['username'])[0],
                            //'URL' => Yii::getAlias('@web') . '/uploads/',
                            'mimeDetect' => 'internal',
                            'imgLib' => 'gd',
                            'accessControl' => function ($attr, $path) {
                                // hide files/folders which begins with dot
                                return (strpos(basename($path), '.') === 0) ?
                                    !($attr == 'read' || $attr == 'write') :
                                    null;
                            },
                        ],
                        [
                            'driver' => 'FTP',
                            'alias' => 'FTP',
                            'host'   => Yii::$app->params['ftpIp'],
                            'user'   => Yii::$app->params['ftpUser'],
                            'pass'   => Yii::$app->params['ftpPass'],
                            'path'   => Yii::$app->params['userDataPath'] . '/' . explode('@',Userw::getCurrentUser()['username'])[0],
                            'accessControl' => function ($attr, $path) {
                                // hide files/folders which begins with dot
                                return (strpos(basename($path), '.') === 0) ?
                                    !($attr == 'read' || $attr == 'write') :
                                    null;
                            },
                        ],
                    ],
                ],
            ],
            'input' => [
                'class' => InputFileAction::className(),
                'connectorRoute' => 'connector',
            ],
            'ckeditor' => [
                'class' => CKEditorAction::className(),
                'connectorRoute' => 'connector',
            ],
            'tinymce' => [
                'class' => TinyMCEAction::className(),
                'connectorRoute' => 'connector',
            ],
        ];
    }

    public function actionIndex()
    {
        $userFolder=Yii::$app->params['userDataPath'] . '/' . explode('@',Userw::getCurrentUser()['username'])[0];
        // $user=User::getCurrentUser()['username'];
       
        if (!is_dir($userFolder))
        {
            Software::exec_log("mkdir $userFolder");
        }

        Software::exec_log("chmod 777 $userFolder -R 2>&1",$out,$ret);


        return $this->render('index',['connectorRoute' => 'connector','messages'=>[]]);
    }

    

    

    public function actionSelectMountpoint($username)
    {
        $model=new Software;
        $directory=Yii::$app->params['userDataPath'] . explode('@',User::getCurrentUser()['username'])[0];

        $folders=Software::listDirectories($directory);
        
        return $this->renderAjax('select_mountpoint',['folders'=>$folders]);
    }

    public function actionDownloadDataset()
    {
        $model=new DownloadDataset();
        $datasets=['Helix'=>'Helix', 'Zenodo'=>'Zenodo', 'Url'=>'Any Url'];
        $username=Userw::getCurrentUser()['username'];

        if (($model->load(Yii::$app->request->post())))
        {
            $user_id=Userw::getCurrentUser()['id'];
            
            $dataset_id=$model->dataset_id;
            $provider=$_POST['provider_name'];

            if ($provider=='Helix')
            {    
                $folder=$_POST['dataset_helix'];
                $result=DownloadDataset::downloadHelixDataset($folder,$dataset_id,$provider);
            }
            elseif($provider=='Zenodo')
            {
                $folder=$_POST['dataset_zenodo'];
                $result=DownloadDataset::downloadZenodoDataset($folder,$dataset_id,$provider);
            }
            else
            {
                $folder=$_POST['dataset_url'];
                $result=DownloadDataset::downloadFromUrl($folder,$dataset_id,$provider);
                
            }

            if(!empty($result['success']))
            {
                if($result['version'])
                {
                    $model->version=$result['version'];
                }
                if($result['title'])
                {
                    $model->name=$result['title'];
                }
                
                $model->folder_path=$folder;
                $model->provider=$provider;
                $model->dataset_id=$dataset_id;
                $model->user_id=$user_id;
                $model->date=date("Y-m-d");
                $model->save();
            }

            if (!empty($result['error']))
            {
                Yii::$app->session->setFlash('danger', $result['error']);
            }
            elseif (!empty($result['warning']))
            {
                Yii::$app->session->setFlash('warning', $result['warning']);
            }
            else
            {
                Yii::$app->session->setFlash('success', $result['success']);
            }
            return $this->redirect(['filebrowser/index']);
        }
        
        return $this->render('download_dataset', ['model'=>$model, 'datasets'=>$datasets, 'username'=>$username]);
    }

    public function actionAutoCompleteSubjects($expansion, $max_num, $term)
    {
        //Create mature version model
        $model = new UploadDatasetHelix;
        //Get names based on query parameters. NOTE: results should be in json
        $subjects = $model::getSubjectsAutoComplete($expansion, $max_num, $term);
        // print_r($names);
        // exit(0);
        $subjectsDecoded=json_decode($subjects);
        //Check if results are empty
        if(empty($subjectsDecoded)) 
        {
            $subjects = json_encode(["No suggestions found"]);
        }     
        //Return results - these are already encoded in json
        return $subjects;       
    }

    public function actionUploadDataset()
    {
        $datasets=[];
        $defaults=UploadDatasetDefaults::find()->orderBy(['name'=>SORT_DESC])->all();
        foreach ($defaults as $default)
        {
            if ($default->enabled==1)
            {
                $datasets += ["$default->name"=>"$default->name"];
            }
        }

        $model_zenodo=new UploadDatasetZenodo;
        $model_helix=new UploadDatasetHelix;
        $username=Userw::getCurrentUser()['username'];
        $helix_defaults= UploadDatasetHelix::getHelixDefaults();
        $zenodo_defaults=UploadDatasetZenodo::getZenodoDefaults();


        return $this->render('upload_dataset', ['model_zenodo'=>$model_zenodo, 'model_helix'=>$model_helix, 'datasets'=>$datasets, 'username'=>$username, 'helix_defaults'=>$helix_defaults, 'zenodo_defaults'=>$zenodo_defaults]);
    }    
    

    public function actionUploadDatasetHelix()
    {
        $model=new UploadDatasetHelix;

        if (($model->load(Yii::$app->request->post()))) 
        {
            $dataset_path=$_POST['dataset_helix'];
            $user_id=Userw::getCurrentUser()['id'];
            $provider=$model->provider;
            $api_key=$model->api_key;
            $title=$model->title;
            $description=$model->description;
            $publication_doi=$model->publication_doi;
            $private=$model->private;
            $license=$model->license;
            $subjects=$_POST['subjects'];
            $creator=$model->creator;
            $contact_email=$model->contact_email;
            $affiliation=$model->affiliation;
            $result=UploadDatasetHelix::uploadHelixDataset($dataset_path,$provider,$api_key, $title, $description,$publication_doi,$private,$license,$subjects,$creator,$contact_email,$affiliation);
            
            if (!empty($result['error']))
            {
                Yii::$app->session->setFlash('danger', json_encode($result['error']));
            }
            else
            {
                $model->save(false);
                Yii::$app->session->setFlash('success', $result['success']);
                
            }

            return $this->redirect(['filebrowser/index']);
             
        }
       
        return $this->redirect(['filebrowser/upload-dataset']);
    
    }

    public function actionUploadDatasetZenodo()
    {
        $model=new UploadDatasetZenodo;
        
        if(($model->load(Yii::$app->request->post())))
        {
            
            $creators_array=[];
            for ($i=0; $i < sizeof($_POST['UploadDatasetZenodo']['creators_name']); $i++)
            {
                if(empty($_POST['UploadDatasetZenodo']['creators_orcid'][$i]))
                {
                    $creators_array[$i]=[
                        'name'=>$_POST['UploadDatasetZenodo']['creators_name'][$i],
                        'affiliation'=>$_POST['UploadDatasetZenodo']['creators_affiliation'][$i],
                        
                    ];
                }
                else
                {
                    $creators_array[$i]=[
                        'name'=>$_POST['UploadDatasetZenodo']['creators_name'][$i],
                        'affiliation'=>$_POST['UploadDatasetZenodo']['creators_affiliation'][$i],
                        'orcid'=>$_POST['UploadDatasetZenodo']['creators_orcid'][$i]
                    ];
                }
            }
            $creators=json_encode($creators_array);
            $model->creators=$creators;
            $dataset_path=$_POST['dataset_zenodo'];
            $user_id=Userw::getCurrentUser()['id'];
            $provider=$model->provider;
            $api_key=$model->api_key;
            $title=$model->title;
            $description=$model->description;
            $upload_type=$model->upload_type;
            $publication_type=$model->publication_type;
            $image_type=$model->image_type;
            $access_rights=$model->access_rights;
            $access_conditions=$model->access_conditions;
            $license=$model->license;
            $doi=$model->doi;
            $embargo_date=$model->embargo_date;

            $result=UploadDatasetZenodo::uploadZenodoDataset($dataset_path,$provider,$api_key, $title, $description, $upload_type,$publication_type, $image_type, $access_rights,$access_conditions,$license, 
                $doi,$embargo_date,$creators_array);
            if (!empty($result['error']))
            {
                Yii::$app->session->setFlash('danger', $result['error']);
            }
            else
            {
                 $model->save(false);
                Yii::$app->session->setFlash('success', $result['success']);
               
            }

            return $this->redirect(['filebrowser/index']);
        }
        
        return $this->redirect(['filebrowser/upload-dataset']);
    }

    public function actionDatasetHistory()
    {
        $user_id=Userw::getCurrentUser()['id'];
        $datasets=DownloadDataset::find()->where(['user_id'=>$user_id])->all();
        return $this->render('dataset_history', ['results'=>$datasets]);
    }


}