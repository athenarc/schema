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

            $model->folder_path=$folder;
            $model->provider=$_POST['DownloadDataset']['provider'];
            $model->dataset_id=$dataset_id;
            $model->user_id=$user_id;

            //$model->save();

            $client = new Client(['baseUrl' => 'https://data.hellenicdataservice.gr/api']);
            $response = $client->createRequest()
                ->setUrl("action/package_show?id=$dataset_id")
                //->addHeaders(['content-type' => 'application/json'])
               // ->addHeaders(['Authorization'=>"$api_key"])
               // ->setContent('{query_string: "Yii"}')
                ->send();


            $content=json_decode($response->content);


            
            if(!$content->success==1)
            {
                    Yii::$app->session->setFlash('danger', 'The dataset id you provided is not valid');
                    return $this->redirect(['filebrowser/index']);
            }
            else
            {
                $error=0;

                

                
                $resources=$content->result->resources;
                

                foreach($resources as $res)
                {
                    
                    //$file=file_get_contents($res->url);
                    $parts=explode('/',$res->url);
                    $compare_url = $parts[0].'/'.$parts[1].'/'.$parts[2].'/'.$parts[3].'/';
                    // print_r($compare_url);
                    // exit(0);
                    if(!strcmp($compare_url,"https://data.hellenicdataservice.gr/dataset/")==0)
                    {
                        $error=1;
                    }

                }

                if($error!=1)
                {
                    $file_name = preg_replace('/\s+/', '-', strval($content->result->title)); 
                    $finalFolder=Yii::$app->params['userDataPath'] . '/' . explode('@',Userw::getCurrentUser()['username'])[0] . '/' . $folder . '/'. "Dataset_" . $dataset_id . '/';

                    exec("mkdir $finalFolder");

                    foreach($resources as $res)
                    {
                        $finalFile=$finalFolder . $res->name;
                        $file=file_get_contents($res->url);
                        file_put_contents($finalFile, $file);  
                    }
                   
                    
                    
                    Yii::$app->session->setFlash('success', 'The dataset has been successfully downloaded');
                    return $this->redirect(['filebrowser/index']);
                }
                else
                {
               
                    Yii::$app->session->setFlash('warning', "The dataset contains resources that can not be downloaded. Please visit https://data.hellenicdataservice.gr/dataset/$dataset_id/ to get access to the files");
                    return $this->redirect(['filebrowser/index']);
                }
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
            


            //a8b0653c-94b1-41d7-97aa-abcda0902ab5
           
            $client = new Client(['baseUrl' => 'https://data.hellenicdataservice.gr/api']);
            $response = $client->createRequest()
                ->setUrl('action/package_create')
                //->addHeaders(['content-type' => 'application/json'])
                ->addHeaders(['Authorization'=>"$api_key"])
               // ->setContent('{query_string: "Yii"}')
                ->send();
            echo 'Results:<br>';
            echo $response->content;


            // $client = new Client([
            //     'transport' => 'yii\httpclient\CurlTransport',     
            // ]);
            // $response = $client->createRequest()
            //     ->setFormat(Client::FORMAT_CURL)
            //     ->setMethod('POST')
            //     ->setUrl('http://example.com')
            //     ->setData([
            //     'name' => 'John Doe',
            //     'email' => 'johndoe@example.com',
            //     'file1' => new \CURLFile('/path/to/file1'), 'text/plain', 'file1'),
            //     'file2' => new \CURLFile('/path/to/file2'), 'text/plain', 'file2'),
            // ])
            // ->send();
            

            return $this->redirect(['filebrowser/index']);
        }
    
    }


}