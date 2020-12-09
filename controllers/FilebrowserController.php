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
use app\models\UploadDataset;
use app\models\DownloadDataset;
use \app\models\Notification;
use yii\helpers\Url;
use yii\data\Pagination;
use yii\web\UploadedFile;
use yii\httpclient\Client;


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

        if (Yii::$app->request->post()) 
        {
            $user_id=Userw::getCurrentUser()['id'];
            $folder=$_POST['osystemmount'];
            $dataset_id=$_POST['DownloadDataset']['dataset_id'];
            $client = new Client(['baseUrl' => 'https://data.hellenicdataservice.gr/api']);
            $response = $client->createRequest()
                ->setUrl("action/package_show?id=$dataset_id")
                ->send();

            


            $content=json_decode($response->content);

            $title=$content->result->title;
            $version=$content->result->version;
           
            
            if(!$content->success==1)
            {
                    Yii::$app->session->setFlash('danger', 'The dataset id you provided is not valid');
                    return $this->redirect(['filebrowser/index']);

            }
            else
            {
                $resources=$content->result->resources;
                if(empty($folder))
                {
                    Yii::$app->session->setFlash('warning', "You must choose a folder to store the dataset");
                    return $this->redirect(['filebrowser/index']);
                }

                $finalFolder=Yii::$app->params['userDataPath'] . '/' . explode('@',Userw::getCurrentUser()['username'])[0] . '/' . $folder . '/'. "Dataset_" . $dataset_id . '/';

                exec("mkdir $finalFolder");

                foreach ($resources as $res)
                {
                    if (empty($res->mimetype))
                    {
                        $command="wget  -r -np -R 'index.html*' -P $finalFolder $res->url";
                    }
                    else
                    {
                        $command="wget -P $finalFolder $res->url";
                    }

                    exec($command,$out,$ret);
                    
                    if ($ret!=0)
                    {
                        Yii::$app->session->setFlash('warning', "The dataset contains resources that can not be downloaded. Please visit https://data.hellenicdataservice.gr/dataset/$dataset_id/ to get access to the files");
                    }
                    else
                    {
                        Yii::$app->session->setFlash('success', 'The dataset has been successfully downloaded');
                        $model->folder_path=$folder;
                        $model->provider=$_POST['DownloadDataset']['provider'];
                        $model->dataset_id=$dataset_id;
                        $model->user_id=$user_id;
                        $model->date=date("Y-m-d");
                        $model->version=$version;
                        $model->name=$title;
                        $model->save();
                    }
                
                }
                return $this->redirect(['filebrowser/index']);
            }
        }
    }

    
    

    public function actionUploadDataset()
    {
        if(Yii::$app->request->post()) 
        {

            $dataset=UploadedFile::getInstanceByName('dataset');
            $metadata=UploadedFile::getInstanceByName('metadata');
            $api_key=$_POST['api_key'];
            
           
            $client = new Client(['baseUrl' => 'https://data.hellenicdataservice.gr/api']);
            $response = $client->createRequest()
                ->setUrl('action/package_create')
                ->addHeaders(['Authorization'=>"$api_key"])
                ->send();
            echo 'Results:<br>';
            echo $response->content;
            return $this->redirect(['filebrowser/index']);
        }
    
    }

    public function actionDatasetHistory()
    {
        $user_id=Userw::getCurrentUser()['id'];
        $datasets=DownloadDataset::find()->where(['user_id'=>$user_id])->all();
        // print_r($datasets);
        // exit(0);
        return $this->render('dataset_history', ['results'=>$datasets]);
    }


}