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
use \app\models\Notification;
use yii\helpers\Url;
use yii\data\Pagination;

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
         //print_r($out);
        // exit(0);

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

}