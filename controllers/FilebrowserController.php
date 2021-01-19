<?php

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
use \app\models\Notification;
use yii\helpers\Url;
use yii\data\Pagination;
use yii\web\UploadedFile;
use yii\httpclient\Client;
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
        return [
            'connector' => [
                'class' => ConnectorAction::className(),
                'options' => [
                    'roots' => [
                        [
                            'driver' => 'LocalFileSystem',
                            // 'path' => Yii::getAlias('@webroot') . DIRECTORY_SEPARATOR . 'uploads',
                            'path' => Yii::$app->params['userDataPath'] . '/' . explode('@',Userw::getCurrentUser()['username'])[0],
                            'URL' => Yii::getAlias('@web') . '/uploads/',
                            'mimeDetect' => 'internal',
                            'imgLib' => 'gd',
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
            exec("mkdir $userFolder");
        }

        exec("chmod 777 $userFolder -R 2>&1",$out,$ret);


        return $this->render('index',['connectorRoute' => 'connector','messages'=>[]]);
    }

    public function actionGetCovidData()
    {
        $userFolder=Yii::$app->params['userDataPath'] . '/' . explode('@',Userw::getCurrentUser()['username'])[0];

        // $user=User::getCurrentUser()['username'];
        $origin=Yii::$app->params['userDataPath'] . '/covid19_data/';
        // $destination=$userFolder . '/covid19_data/';
        exec("cp -a $origin $userFolder");
        // exec("chmod 777 $userFolder -R 2>&1",$out,$ret);
        // print_r($out);
        // exit(0);

        return $this->render('index',['connectorRoute' => 'connector','messages'=>[]]);
    }

    public function actionRequestDataset()
    {
        $model = new CovidDatasetApplication();

        if (($model->load(Yii::$app->request->post())) && ($model->validate()) ) 
        {
            $model->username=Userw::getCurrentUser()['username'];
            $model->status=0;
            $model->submission_date='NOW()';
            $model->save();

            $musername=explode('@',$model->username)[0];
            $message="User <strong>$musername</strong> applied for a new COVID-19 dataset.";
            $url=Url::to(['/filebrowser/covid-application-details','id'=>$model->id]);

            foreach (User::getAdminIds() as $admin)
            {
                Notification::notify($admin, $message, '0' ,$url);
            }

            $messages=[['message'=>'Applicaton submitted successfully!', 'class'=>'success']];

            return $this->render('index',['connectorRoute' => 'connector','messages'=>$messages]);
        }

        return $this->render('dataset_application', [
            'model' => $model,
        ]);
    }

    public function actionCovidListApplications()
    {
        $query=CovidDatasetApplication::find();

        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count()]);
        $applications = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->all();

        return $this->render('covid_application_list', 
            [
                'applications' => $applications,
                'pages' => $pages,
        ]);

    }

    public function actionCovidApplicationDetails($id)
    {
        $application=CovidDatasetApplication::find()->where(['id'=>$id])->one();

        return $this->render('covid_application_details',['application'=>$application]);
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

        if (($model->load(Yii::$app->request->post())))
        {
            $user_id=Userw::getCurrentUser()['id'];
            $folder=$_POST['osystemmount'];
            $dataset_id=$model->dataset_id;
            $provider=$model->provider;

            if ($provider=='Helix')
            {    
                $result=DownloadDataset::downloadHelixDataset($folder,$dataset_id,$provider);
            }
            else
            {
                $result=DownloadDataset::downloadZenodoDataset($folder,$dataset_id,$provider);
            }

            if(!empty($result['success']))
            {
                $model->version=$result['version'];
                $model->name=$result['title'];
                $model->folder_path=$folder;
                $model->provider=$provider;
                $model->dataset_id=$dataset_id;
                $model->user_id=$user_id;
                $model->date=date("Y-m-d");
                $model->save();
            }

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
        $model_zenodo=new UploadDatasetZenodo;
        $model_helix=new UploadDatasetHelix;
        $datasets=['Zenodo'=>'Zenodo','Helix'=>'Helix'];
        $username=Userw::getCurrentUser()['username'];
        $helix_licenses=  [
            'notspecified'=>'License not specified',
            'CC-BY'=>'CC-BY 4.0 - Creative Commons Attribution 4.0 International',
            'CC-BY-SA'=>'CC-BY-SA 4.0 - Creative Commons Attribution-ShareAlike 4.0 International',
            'CC-BY-NC'=>'CC-BY-NC 4.0 - Creative Commons Attribution-NonCommercial 4.0 International',
            'CC-BY-NC-SA'=>'CC-BY-NC-SA 4.0 - Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International',
            'CC-ZERO'=>'CC-ZERO - Creative Commons CCZero',
            
            'other-restricted'=>'Other (restricted resource)',
        ];
        return $this->render('upload_dataset', ['model_zenodo'=>$model_zenodo, 'model_helix'=>$model_helix, 'datasets'=>$datasets, 'username'=>$username, 'helix_licenses'=>$helix_licenses]);
    }    
    

    public function actionUploadDatasetHelix()
    {
        $model=new UploadDatasetHelix;

        if (($model->load(Yii::$app->request->post()))) 
        {
            print_r($_POST);
            exit(0);

            $dataset_path=$_POST['dataset_helix'];
            $user_id=Userw::getCurrentUser()['id'];
            $provider=$model->provider;
            $api_key=$model->api_key;
            $title=$model->title;
            $description=$model->description;
            $dataset_id=rand(1,10000);
            $publication_doi=$model->publication_doi;
            $private=$model->private;
            $license=$model->license;
            $subjects=$_POST['subjects'];
            $creator=$model->creator;
            $contact_email=$model->contact_email;
            $affiliation=$model->affiliation;
            $result=UploadDatasetHelix::uploadHelixDataset($dataset_path,$provider,$api_key, $title, $description, $dataset_id,$publication_doi,$private,$license,$subjects,$creator,$contact_email,$affiliation);
            

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
                $model->save();
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
            $dataset_path=$_POST['dataset_zenodo'];
            $user_id=Userw::getCurrentUser()['id'];
            $provider=$model->provider;
            $api_key=$model->api_key;
            $result=UploadDatasetZenodo::uploadZenodoDataset($dataset_path,$provider,$api_key);
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
                $model_zenodo->save();
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