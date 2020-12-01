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
            $executor=$executors[0];
            // print_r($executor);
            // exit(0);
            /*
             * Send job to TESK
             */
            $client = new Client(['baseUrl' => 'https://tesk.egci-endpoints.imsi.athenarc.gr']);
            $teskResponse = $client->createRequest()
                                    ->setMethod('POST')
                                    ->setFormat(Client::FORMAT_JSON)
                                    ->setUrl('v1/tasks')
                                    ->setData($teskData)
                                    ->send();
            
            if (!$teskResponse->getIsOk())
            {
                $status=$teskResponse->getStatusCode();
                
                $response->format = \yii\web\Response::FORMAT_JSON;
                $response->setStatusCode($status);
                $response->data = $teskResponse->data;
                // $respones->data=$data;
                $response->send();
                return;

            }

            
            /*
             * Get ID from TESK response 
             * and add job to the DB.
             */
            $teskResponseData=$teskResponse->data;

            $task=$teskResponseData['id'];

            $history=new RunHistory();

            $history->username=$user->username;
            $history->jobid = $task;
            $history->command = implode(' ',$executor['command']);
            $history->image= $executor['image'];
            $history->start = 'NOW()';
            $history->project=$project;
            $history->type='remote-job';

            $history->insert();

            $tmpFolder=Yii::$app->params['tmpFolderPath'] . '/' . $task;
            exec("mkdir $tmpFolder",$out,$ret);
            exec("chmod 777 $tmpFolder -R");

            /*
             * Call a script that monitors the job
             * and fills the DB when the job is complete
             */
            $monitorScript=$scheduler="sudo -u ". Yii::$app->params['systemUser'] . " " . Yii::$app->params['scriptsFolder'] . "/remoteJobMonitor.py";
            $arguments=[$monitorScript, Software::enclose($task), Software::enclose(Yii::$app->params['teskEndpoint']), Software::enclose($tmpFolder)];
            $monitorCommand=implode(' ',$arguments);

            shell_exec(sprintf('%s > /dev/null 2>&1 &', $monitorCommand));

            /*
             * Return jobid in response
             */
            $response->format = \yii\web\Response::FORMAT_JSON;
            $response->setStatusCode(200);
            $response->data = $teskResponseData;
            // $response->data=$data;
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
                $response->data = ['message' => "Task is empty"];
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
                $response->data = $teskResponse->data;
                $response->send();
                return;
            }

            $response->format = \yii\web\Response::FORMAT_JSON;
            $response->setStatusCode(200);
            $response->data = [ $task => strtoupper($history->status) ];
            $response->send();
            return;

        }
            
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
