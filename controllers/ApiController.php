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
use app\models\User;
use app\models\Software;
use yii\httpclient\Client;
use app\models\ApiFunctions;

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
        $response=Yii::$app->response;

        if (Yii::$app->request->getIsPost())
        {
            /*
             * Get the user from the data and see if it exists in the system.
             * If user does not exist, return an error code.
             */
            $data=$request->post();
            if (!isset($data['username']))
            {
                $response->format = \yii\web\Response::FORMAT_JSON;
                $response->setStatusCode(401);
                $response->data = ['message' => "Data does not contain a username specification."];
                $response->send();
                return;

            }
            $username=trim($request->post('username'));
            $user=User::find()->where(['username'=>$username])->one();
            if (empty($user))
            {
                $response->format = \yii\web\Response::FORMAT_JSON;
                $response->setStatusCode(401);
                $response->data = ['message' => "User does not exist in the system."];
                $response->send();
                return;

            }

            /*
             * Get the project from the data and see if it exists in the project management system.
             * If project does not exist, return an error code.
             */

            if (!isset($data['project']))
            {
                
                $response->format = \yii\web\Response::FORMAT_JSON;
                $response->setStatusCode(402);
                $response->data = ['message' => "Data does not contain a project specification."];

                $response->send();
                return;

            }
            
            $project=trim($request->post('project'));
            $quotas=Software::getOndemandProjectQuotas($username,$project);
            
            if (empty($quotas))
            {
                
                $response->format = \yii\web\Response::FORMAT_JSON;
                $response->setStatusCode(402);
                $response->data = ['message' => "Project does not exist in the system."];
                $response->send();
                return;

            }

            $teskData=$data;


            unset($teskData['project']);
            unset($teskData['username']);

            /*
             * Job must contain only one executor.
             * If there are more than one or none, send error code
             */
            $executors=$teskData['executors'];

            if (empty($executors))
            {
                $response->format = \yii\web\Response::FORMAT_JSON;
                $response->setStatusCode(407);
                $response->data = ['message' => "Data does not contain an executor"];
                $response->send();
                return;
            }
            if (count($executors)>1)
            {
                $response->format = \yii\web\Response::FORMAT_JSON;
                $response->setStatusCode(407);
                $response->data = ['message' => "Data must contain only one executor"];
                $response->send();
                return;
            }
            if (!isset($teskData['reference_data']))
            {
                $executor=$executors[0];
                /*
                 * Send job to TESK
                 */
                $teskResponse=ApiFunctions::runTesk($teskData,$username,$project,$executor);
                $tesCode=$teskResponse[0];
                $tesResponseData=$teskResponse[1];
                /*
                 * Return jobid in response
                 */
                
            }
            else
            {

                foreach ($teskData['reference_data'] as $index => $ref)
                {
                    $teskData['reference_data'][$index]['path']= Yii::$app->params['userDataPath'] . explode('@',$username)[0] . '/' . ltrim($ref['path'],'/');
                }

                $tesResponse=ApiFunctions::runSchemaTes($teskData,$project,$username,$quotas);
                $tesCode=$tesResponse[0];
                $tesResponseData=$tesResponse[1];

            }

            $response->format = \yii\web\Response::FORMAT_JSON;
            $response->setStatusCode($tesCode);
            $response->data = $tesResponseData;
            $response->send();
            return;

        }
        else if (Yii::$app->request->getIsget())
        {
            /*
             * Get task id. If not provided or empty, throw an error
             */
            $data=$request->get();

            if (!isset($data['task']))
            {
                $response->format = \yii\web\Response::FORMAT_JSON;
                $response->setStatusCode(408);
                $response->data = ['message' => "Task not provided", 'status'=>408];
                $response->send();
                return;
            }
            $task=$data['task'];
            if (empty($task))
            {
                $response->format = \yii\web\Response::FORMAT_JSON;
                $response->setStatusCode(408);
                $response->data = ['status' => 408, 'message' => "Task is empty"];
                $response->send();
                return;
            }

            /*
             * Uncomment the following line if you want to support other types of views
             */
            // $view=isset($data['view']) ? $data['view'] : '';
            $view='';

            /*
             * View is optional so see if it defined and use it
             */
            if (!empty($view) && ($view!='FULL') && ($view!='MINIMAL') && ($view!='BASIC'))
            {
                $response->format = \yii\web\Response::FORMAT_JSON;
                $response->setStatusCode(409);
                $response->data = ['message' => "View type unrecongized."];
                $response->send();
                return;

            }

            if (strpos($task,'task-'))
            {
                /*
                 * Query TESK about the task
                 */

                $url='v1/tasks/' . $task;
                $url.=(empty($view)) ? '' : "?view=$view";

                $client = new Client(['baseUrl' => 'https://tesk.egci-endpoints.imsi.athenarc.gr']);
                $teskResponse = $client->createRequest()
                                        ->setMethod('GET')
                                        ->setFormat(Client::FORMAT_JSON)
                                        ->setUrl($url)
                                        ->send();
                
                /*
                 * If TESK found the task, send its info directly to the user.
                 */
                $retCode=$teskResponse->getStatusCode();

                if ($teskResponse->getIsOk())
                { 
                    $response->format = \yii\web\Response::FORMAT_JSON;
                    $response->setStatusCode($retCode);
                    $response->data = $teskResponse->data;
                    $response->send();
                    return;
                }
                /*
                 * If task not found by TESK, and it is not a 404 error,
                 * return the TESK error.
                 */

                if ($retCode!=404)
                {
                    $response->format = \yii\web\Response::FORMAT_JSON;
                    $response->setStatusCode($retCode);
                    $response->data = $teskResponse->data;
                    $response->send();
                    return;
                }
            }
            
            /*
             * If job is found in the database, only limited view is supported
             */
            $history=RunHistory::find()->where(['jobid'=>$task])->one();
            // print_r($history);
            // exit();
            if (empty($history))
            {
                $response->format = \yii\web\Response::FORMAT_JSON;
                $response->setStatusCode(404);
                $response->data = ['message' => "Task not found", 'status'=>404];
                $response->send();
                return;
            }

            if (($history->remote_status_code<0) && ($history->remote_status_code>-9))
            {
                $history->status='SYSTEM_ERROR';
            }
            else if ($history->remote_status_code==-9)
            {
                $history->status='EXECUTOR_ERROR';
            }
            else if ($history->remote_status_code==-10)
            {
                $history->status='CANCELED';
            }
            else if ($history->remote_status_code==2)
            {
                $history->status='QUEUED';
            }
            else if ($history->remote_status_code==2)
            {
                $history->status='INITIALIZING';
            }
            else if ($history->remote_status_code==3)
            {
                $history->status='RUNNING';
            }
            else if ($history->remote_status_code==4)
            {
                $history->status='COMPLETE';
            }
            else
            {
                $history->status='UNKNOWN';
            }
            



            $response->format = \yii\web\Response::FORMAT_JSON;
            $response->setStatusCode(200);
            $response->data = [ 'id' => $task, 'state' => strtoupper($history->status) ];
            $response->send();
            return;

        }
            
    }
    
    public function actionProjectUsage($project)
    {

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
                    ['status'=>'Canceled'],
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
