<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\RunHistory;
use yii\db\Query;
use webvimark\modules\UserManagement\models\User as Userw;


class ApiController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public $freeAccess = true;
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

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    
    public function beforeAction($action) 
    { 
        $this->enableCsrfValidation = false; 
        return parent::beforeAction($action); 
    }

    public function actionTasks()
    {
        $request=Yii::$app->request;

        if (Yii::$app->request->getIsPost())
        {
            $resources=$request->post('resources');
            $volumes=$request->post('volumes');
            $executors=$request->post('executors');
            $api_id=$request->post('user_id');
            $jobid=uniqid();
            $ret_value=SoftwareApi::createAndRunJob($executors,$resources,$volumes,$jobid,$api_id);

            if ($ret_value==1)
            {
                $response = Yii::$app->response;
                $response->setStatusCode(401);
                $response->format = \yii\web\Response::FORMAT_JSON;
                $response->data = ['message' => "No executors in your data."];
                $response->send();

            }
            else if ($ret_value==2)
            {
                $response = Yii::$app->response;
                $response->setStatusCode(401);
                $response->format = \yii\web\Response::FORMAT_JSON;
                $response->data = ['message' => "One or more of your executors do not contain image specifications."];
                $response->send();

            }
            else if ($ret_value==3)
            {
                $response = Yii::$app->response;
                $response->setStatusCode(401);
                $response->format = \yii\web\Response::FORMAT_JSON;
                $response->data = ['message' => "One or more of your images are not specified correctly."];
                $response->send();

            }
            else if ($ret_value==4)
            {
                $response = Yii::$app->response;
                $response->setStatusCode(402);
                $response->format = \yii\web\Response::FORMAT_JSON;
                $response->data = ['message' => "One or more of your images were not found in the system."];
                $response->send();

            }
            else if ($ret_value==5)
            {
                $response = Yii::$app->response;
                $response->setStatusCode(401);
                $response->format = \yii\web\Response::FORMAT_JSON;
                $response->data = ['message' => "One or more of your executors do not contain a command."];
                $response->send();

            }
            else if ($ret_value==6)
            {
                $response = Yii::$app->response;
                $response->setStatusCode(405);
                $response->format = \yii\web\Response::FORMAT_JSON;
                $response->data = ['message' => "You did not specify a user_id or user_id was not found in the database."];
                $response->send();

            }
            else if ($ret_value==7)
            {
                $response = Yii::$app->response;
                $response->setStatusCode(406);
                $response->format = \yii\web\Response::FORMAT_JSON;
                $response->data = ['message' => "Volumes must be strings in the form SOURCE:DEST."];
                $response->send();

            }
        }
        else if (Yii::$app->request->getIsGet())
        {
            print_r('hello');
        }
        else
        {
            $method=$request->method;
            $response = Yii::$app->response;
            $response->setStatusCode(404);
            $response->format = \yii\web\Response::FORMAT_JSON;
            $response->data = ['message' => "Code 404. No method $method for the /tasks endpoint<br />"];

            $response->send();
        }
    }
    

    public function actionProjectUsage($project)
    {
        // Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        // $jobNum=RunHistory::find()->where(['project'=>$project,])
        //     ->andFilterWhere(
        //     [
        //         'or',
        //         ['status'=>'Complete'],
        //         [
        //             'and',
        //             ['status'=>'Cancelled'],
        //             "stop-start>='00:00:60'"
        //         ]
        //     ])
        //     ->count();
        
        $usage=RunHistory::getProjectUsage($project);
        $this->asJson($usage);
    }

    public function actionPeriodStatistics()
    {
        $totalJobs=RunHistory::find()
            ->filterWhere(
            [
                'or',
                ['status'=>'Complete'],
                [
                    'and',
                    ['status'=>'Cancelled'],
                    "stop-start>='00:00:60'"
                ]
            ])
            ->count();
        
        $query=new Query;
        $result=$query->select([
            "date_part('hour',sum(stop-start)) as hours",
            "date_part('minute',sum(stop-start)) as mins",
            "date_part('second',sum(stop-start)) as secs"
        ])->from('run_history')->one();

        $total_time=$result['hours'] . ':' . $result['mins'] . ':' . $result['secs'];

        $usage=['total_time'=>$total_time, 'total_jobs'=>$totalJobs];
        $this->asJson($usage);
    }



}


