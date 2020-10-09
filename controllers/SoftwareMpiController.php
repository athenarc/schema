<?php

/**
 * The main SCHEMA Controller
 *
 * @author: Kostis Zagganas
 * First Version: November 2018
 */

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\Software;
use app\models\ContactForm;
use app\models\SoftwareMpi;
use app\models\SoftwareUpload;
use app\models\SoftwareEdit;
use app\models\SoftwareRemove;
use yii\helpers\Url;
use webvimark\modules\UserManagement\models\User;
use app\models\RunHistory;
use app\models\SoftwareInput;

class SoftwareMpiController extends Controller
{
    /**
     * {@inheritdoc}
     */
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
     * Action to run a docker image uploaded in the system
     */
    public function actionRun($name, $version,$project)
    {

        // $softwareModel=new Software;

        /*
         * Get name and version from the browser link
         */
        // $name=$_GET['name'];
        // $version=$_GET['version'];
        // $project=$_GET['project'];
        $type=2;
        $projects=Software::getActiveProjects();

        $mpiAlreadyRunning=SoftwareMpi::anotherJobRunning();

        if ($mpiAlreadyRunning)
        {
            return $this->render('mpi_already_running');
        }

              
        $user=User::getCurrentUser()['username'];
        
        $software=SoftwareMpi::find()->where(['name'=>$name,'version'=>$version])->one();
        $software_instructions=$software->instructions;
        // print_r($software);
        // exit(0);
        if (empty($software))
        {
            return $this->render('no_software',['name'=>$name,'version'=>$version]);
        }

        if(!isset($projects[$project]) && (Yii::$app->params['standalone']==false) )
        {
            
            return $this->render('project_error',['project'=>$project]);
        }

             
        $user=User::getCurrentUser()['username'];
        /*
         * If the posted values are filled, use that
         * or assign default values
         */
        $jobid=isset($_POST['jobid']) ? $_POST['jobid'] : '';
        $podid=isset($_POST['podid']) ? $_POST['podid'] : '';
        $cluster=isset($_POST['cluster']) ? $_POST['cluster'] : 'down';
        $machineType=isset($_POST['machineType']) ? $_POST['machineType'] : '';
        $field_values=isset($_POST['field_values']) ? $_POST['field_values'] : [];
        $example=(isset($_POST['example']) && ($_POST['example']=="1")) ? true : false;
        $pernode=(isset($_POST['pernode'])) ? $_POST['pernode'] : '7';


        // print_r($_POST['pernode']);
        // exit(0);

        /*
         * contMount variables contain the mountpoints inside the container as specified during the addition process
         * SystemMount variableσ contain the local path that will be mounted to the container.
         */
        $icontMount=$software->imountpoint;
        $ocontMount=$software->omountpoint;
        $iocontMount='';
        if ((!empty($icontMount)) && (!empty($ocontMount)))
        {
            if ($icontMount==$ocontMount)
            {
                $iocontMount=$icontMount;
            }
        }


        $isystemMountField=isset($_POST['isystemmount']) ? $_POST['isystemmount'] : '' ;
        $osystemMountField=isset($_POST['osystemmount']) ? $_POST['osystemmount'] : '' ;
        $iosystemMountField=isset($_POST['iosystemmount']) ? $_POST['iosystemmount'] : '' ;

        if ($example)
        {
            $iosystemMountField='';
            $isystemMountField='';
            $osystemMountField='';
            /*
             * If iomount is empty, then see if imount and omount are filled
             */
            if (!empty($iocontMount))
            {
                $iosystemMountField='examples/' . $name . '/' . $version . '/io/';
            }
            else 
            {
                if (!empty($icontMount)) 
                {
                    $isystemMountField='examples/' . $name . '/' . $version . '/input/';
                }
                if (!empty($ocontMount)) 
                {
                    $osystemMountField='examples/' . $name . '/' . $version . '/output/';
                }
            }
            
        }


        $iosystemMount='';
        $isystemMount='';
        $osystemMount='';
        /*
         * If iomount is empty, then see if imount and omount are fi
         */
        if (!empty($iocontMount))
        {
            $iosystemMount=Yii::$app->params['userDataPath'] . explode('@',$user)[0] . '/' . $iosystemMountField;
        }
        else 
        {
            if (!empty($icontMount)) 
            {   
                $isystemMount=Yii::$app->params['userDataPath'] . explode('@',$user)[0] . '/' . $isystemMountField;
            }
            if (!empty($ocontMount)) 
            {
                $osystemMount=Yii::$app->params['userDataPath'] . explode('@',$user)[0] . '/' . $osystemMountField;
                // print_r($osystemMount);
                // exit(0);
            }
        }

        
        $mountpointExistError=false;
        // print_r($οsystemMount);
        // exit(0);
        
        /*
         * Check if i/o folders exist. If not, create them. Depends on whether there is one mountpoint for the 
         */
        
        if (!empty($iocontMount))
        {
            if (!is_dir($iosystemMount))
            {
                exec("mkdir -p $iosystemMount");
                exec("chmod 777 $iosystemMount");
            }
            else
            {
                exec("chmod 777 $iosystemMount -R");
                // print_r('done');
                // exit(0);
            }

        }
        else
        {
            if (!empty($icontMount))
            {
                if (!is_dir($isystemMount))
                {
                    exec("mkdir -p $isystemMount");
                    exec("chmod 777 $isystemMount");
                }
                else
                {
                    exec("chmod 777 $isystemMount -R");
                }
            }
            if (!empty($ocontMount))
            {
                // print_r($osystemMount);
                // exit(0);
                if (!is_dir($osystemMount))
                {
                    exec("mkdir -p $osystemMount");
                    exec("chmod 777 $osystemMount");
                }
                else
                {
                    exec("chmod 777 $osystemMount -R");
                }
            }
        }

        if ($example)
        {

            $exampleFolder=Yii::$app->params['userDataPath']. 'examples/' . $name . '/' . $version . '/input';
            if (!empty($iosystemMount))
            {
                $folder=$iosystemMount;
            }
            else
            {
                $folder=$isystemMount;
            }
            exec("cp -r $exampleFolder/* $folder");
            exec("chmod 777 $folder");
            
        }

        /*
         * If pod is already running, get its ID
         * by using the jobid and the name of the software
         */

        // $podid=$softwareModel::runningPodIdByJob($name,$jobid);
        // print_r($podid);
        // exit(0);
        

        /* 
         * Add parameters for the active form
         */
        $form_params =
        [
            'action' => URL::to(['software-mpi/run','name'=>$name, 'version'=>$version, 'project'=>$project]),
            'options' => 
            [
                'class' => 'software_commands_form',
                'id'=> "software_commands_form"
            ],
            'method' => 'POST'
        ];

        /*
         * If the user has posted a CWL file, then get the fields
         * for the custom software form from the database.
         */
        // $fields=Software::getSoftwareFields($name,$version);
        $superadmin=(User::hasRole("Admin", $superAdminAllowed = true)) ? 1 : 0;

        $hasExample=$software->has_example;
        $uploadedBy=$software->uploaded_by;

       //      foreach ($fields as &$record) {

                //  $record["default_value"] = 1;

                // }
        $fields=SoftwareInput::find()->where(['softwareid'=>$software->id])->orderBy(['position'=> SORT_ASC])->all();
        // print_r($software->id);
        // exit(0);
        /*
         * If the form has posted load the field values.
         * This was changed because it didn't work with checkboxes.
         */
        $fieldsNum=(!empty($fields)) ? count($fields) : 0;
        $field_values=[];
        if (Yii::$app->request->getIsPost())
        {
            for ($i=0; $i<$fieldsNum; $i++)
            {
                $field_values[]=Yii::$app->request->post('field-'.$i);
            }
        }
        $superadmin=(User::hasRole("Admin", $superAdminAllowed = true)) ? 1 : 0;

        $hasExample=$software->has_example;
        $uploadedBy=$software->uploaded_by;

        // var_dump($field_values);
        // exit(0);
        if (!empty($fields))
        {
            $field_count=count($fields);
        }
        else
        {
            $field_count=0;
        }

        $emptyFields=true;
        // print_r($_POST);
        // print_r("<br />");
        // exit(0);
        for ($index=0; $index<$field_count; $index++)
        {
            if ($example)
            {
                $emptyFields=false;
                if ($fields[$index]->field_type=='boolean')
                {
                    $fields[$index]->value=($fields[$index]->example=='true') ? true : false;
                }
                else
                {
                    $fields[$index]->value=$fields[$index]->example;
                }
            }
            else
            {
                if (empty($field_values))
                {
                    if ($fields[$index]->field_type=='boolean')
                    {
                        $fields[$index]->value=false;
                    }
                    else
                    {
                        $fields[$index]->value='';
                    }
                    
                }
                else
                {
                    $emptyFields=false;
                    if ($fields[$index]->field_type=='boolean')
                    {
                        // print_r($field_values[$index]);
                        // print_r("<br />");
                        $fields[$index]->value=($field_values[$index]=="0") ? false : true;
                    }
                    else
                    {
                        $fields[$index]->value=$field_values[$index];
                    }
                }
            }
        }
        // $emptyFields=true;
        // if (!empty($fields))
        // {
        //     for ($i=0; $i<count($fields); $i++)
        //     {
        //         if (empty($field_values))
        //         {
        //             if (!$example)
        //             {
        //                 $fields[$i]['value']='';
        //             }
        //             else
        //             {
        //                 $fields[$i]['value']=$fields[$i]['example'];
        //                 $emptyFields=false;
        //             }
        //             // print_r($fields[$i]['default_value']);
                    
        //         }
                
        //         else
        //         {
        //             if (!$example)
        //             {
        //                 if (!empty($field_values[$i]))
        //                 {
        //                     $emptyFields=false;
        //                 }
        //                 $fields[$i]['value']=$field_values[$i];
        //             }
        //             else
        //             {
        //                 $fields[$i]['value']=$fields[$i]['example'];
        //                 $emptyFields=false;
        //             }
                    

                    
        //         }
        //     }
        //     //print_r($fields);
        //     //exit(0);
        // }

        // $script=$softwareModel::getScript($name,$version);
        $container_command='';
        $errors=[];
        if (empty($fields))
        {
            /*
             * Check if form has posted
             */
            if (Yii::$app->request->getIsPost())
            {
                $script=$software->script;
                $imountpoint=$icontMount;
                $omountpoint=$ocontMount;
                $iomountpoint=$iocontMount;
                $container_command=$script;
            }
        }
        else
        {
            if (!$emptyFields)
            {
                $script=$software->script;
                $imountpoint=$icontMount;
                $omountpoint=$ocontMount;
                $iomountpoint=$iocontMount;
                $command_errors=Software::createCommand($script,$emptyFields,$fields,$imountpoint);
                $errors=$command_errors[0];
                $container_command=$command_errors[1];
                // print_r($container_command);
                // exit(0);
            }
            else
            {
                /*
                 * Check if form has posted
                 */
                if (Yii::$app->request->getIsPost())
                {
                    $errors=["You did not provide arguments for the script"];
                }
            }
        }

        // if(!isset($projects[$project]))
        // {
        	
        //     return $this->render('project_error',['project'=>$project]);
        // }
        



        /*
         * If the form has posted but the commands box is empty, then add error in the error list
         */
        // $errors=(isset($_POST['commands'])) ? $softwareModel::checkErrors($commands) : [];


        /*
         * Get the user-data folder of the current user.
         * If it does not exist, then create it.
         */
        $userFolder=Yii::$app->params['userDataPath'] . explode('@',User::getCurrentUser()['username'])[0];
        $user=User::getCurrentUser()['username'];

        /**
         * Get the available jobs by getting the quota from eg-ci and counting the jobs
         * that are over 60 seconds long.
         */
        $quotas=Software::getOndemandProjectQuotas($user,$project);

        if(empty($quotas))
        {
            
            return $this->render('project_error',['project'=>$project]);
        }
        $quotas=$quotas[0];
        // print_r($quotas);
        // exit(0);

        $jobUsage=RunHistory::find()->where(['project'=>$project,])
            ->andFilterWhere(
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


        if (!is_dir($userFolder))
        {
            exec("mkdir $userFolder");
            exec("chmod 777 $userFolder");
        }
        $runError='';
        $runPodId='';

        /**
         * Get the maximum value for memory an cpu from the project if:
         * 
         * 1. the user entered something in the field and it is not empty
         * 2. the value entered is negative
         * 3. the value is smaller than the one specified in the project quota
         *
         */
        $maxMem=(!isset($_POST['memory'])) || (empty($_POST['memory'])) || (intval($_POST['memory'])<=0) || (floatval($_POST['memory']) > floatval($quotas['ram'])) ? floatval($quotas['ram']) : floatval($_POST['memory']);
        $maxCores=(!isset($_POST['cores'])) || (empty($_POST['cores'])) || (intval($_POST['cores'])<=0) || (floatval($_POST['cores']) > floatval($quotas['cores'])) ? floatval($quotas['cores']) : floatval($_POST['cores']);
        $pernode=(!isset($_POST['pernode'])) || (empty($_POST['pernode'])) || (intval($_POST['pernode'])<= 0 )|| (intval($_POST['pernode']) > 7) ? 7 : intval($_POST['pernode']);
        $processes=(!isset($_POST['processes'])) || (empty($_POST['processes'])) || (intval($_POST['processes'])<= 0 ) /*|| (intval($_POST['processes']) > 1000)*/ ? $quotas['cores'] : intval($_POST['processes']);
        // print_r($pernode);
        // exit(0);

        /*
         * Check if the pod for the current job ID is already running
         */
        $clusterRunning=($cluster!='down') ? true: false;
        /*
         * If the form has posted, it is not empty and the pod is not already running,
         * run the command for the specific docker image.
         */
        if ((empty($errors)) && (!empty($container_command)) && ($clusterRunning==false) )
        {
            // print_r($οsystemMount);
            // exit(0);
            
            $jobid=uniqid();
            $cluster="start";
            SoftwareMpi::addJob($container_command, $fields, 
                                                    $name, $version, $jobid, $user, 
                                                    $isystemMountField,$osystemMountField,$iosystemMountField,
                                                    $project,$maxMem,$maxCores,$pernode,$processes);
            
        }

        /*
         * If pod started without errors, then send its ID to the form.
         */

        // if ($runPodId!='')

        // {
        //     $podid=$runPodId;
        // }

        // print_r($iocontMount);
        // print_r("<br />");
        // print_r($icontMount);
        // print_r("<br />");
        // print_r($ocontMount);
        // print_r("<br />");
        // exit(0);
        

        return $this->render('run', ['form_params'=>$form_params, 'name'=>$name, 
            'version'=>$version,  'jobid'=>$jobid, 
            'errors'=>$errors, 'runErrors'=>$runError,
            'fields'=>$fields,'isystemMount' => $isystemMountField, 'osystemMount' => $osystemMountField,
            'iosystemMount' => $iosystemMountField, 'example' => '0', 'hasExample'=>$hasExample,
            'username'=>$user,'icontMount'=>$icontMount,'ocontMount'=>$ocontMount,
            'iocontMount'=>$iocontMount,'mountExistError'=>false, 'cluster'=>$cluster,
            'superadmin'=>$superadmin,'uploadedBy'=>$uploadedBy,'jobUsage'=>$jobUsage,'quotas'=>$quotas,
            'maxMem'=>$maxMem, 'maxCores'=>$maxCores, 'project'=>$project,'pernode'=>$pernode,
            'processes'=>$processes, 'software_instructions'=>$software_instructions, 'type'=>$type]);
    }

    public function actionSetupCluster($jobid)
    {
        SoftwareMpi::clusterSetup($jobid);

    }

    public function actionStartJob($jobid)
    {
        SoftwareMpi::startJob($jobid);

    }

    /*
     * Get logs from the running pod to show on the webpage
     */
    public function actionGetLogs($jobid)
    {

        $results=SoftwareMPI::getLogs($jobid);
        $status=$results[0];
        $logs=$results[1];
        $time=$results[2];
        $history=RunHistory::find()->where(['jobid'=>$jobid])->one();
        

        return $this->renderPartial('logs',['logs'=>$logs, 'status'=>$status,'time'=>$time, 'project'=>$history->project]);
    }

    /*
     * Clean up job and delete pod from the system
     */
    public function actionCancel($jobid)
    {

        SoftwareMpi::cancelJob($jobid);

    }


    public function actionSelectMountpoint($username)
    {
        $model=new Software;
        $directory='/data/docker/user-data/' . $username;

        $folders=Software::listDirectories($directory);
        
        return $this->renderAjax('folder_list',['folders'=>$folders]);
    }

    

    public function actionRerun($jobid)
    {

        $softwareModel=new Software;

        /*
         * Get name and version from the browser link
         */
        
        $mpiAlreadyRunning=SoftwareMpi::anotherJobRunning();

        if ($mpiAlreadyRunning)
        {
            return $this->render('mpi_already_running');
        }

        $result=SoftwareMpi::getRerunData($jobid);

        /*
         * If the posted values are filled, use that
         * or assign default values
         */

        $history=RunHistory::find()->where(['jobid'=>$jobid])->one();

        if (empty($history))
        {
            return $this->render('job_not_found',['jobid'=>$jobid]);
        }

        $errors='';
        $runError='';
        $podid='';
        $machineType='';
        $name=$history->softname;
        $version=$history->softversion;
        $project=$history->project;
        $isystemMountField=$history->imountpoint;
        $osystemMountField=$history->omountpoint;
        $iosystemMountField=$history->iomountpoint;
        $maxMem=$history->max_ram;
        $maxCores=$history->max_cpu;
        $pernode=$history->mpi_proc_per_node;
        $processes=$history->mpi_proc;
        $commands='';
        $cluster='down';


        $software=Software::find()->where(['name'=>$name,'version'=>$version])->one();
        $software_instructions=$software->instructions;

        $fields=SoftwareInput::find()->where(['softwareid'=>$software->id])->orderBy(['position'=> SORT_ASC])->all();
        /*
         * fill the values for the fields and get back that object
         */
        $fields=Software::getRerunFieldValues($jobid,$fields);


        $icontMount=$software->imountpoint;
        $ocontMount=$software->omountpoint;
        $iocontMount='';
        if ((!empty($icontMount)) && (!empty($ocontMount)))
        {
            if ($icontMount==$ocontMount)
            {
                $iocontMount=$icontMount;
            }
        }

        $mountExistError=false;
        if (!empty($iosystemMountField))
        {
            $folder=Yii::$app->params['userDataPath'] . explode('@',User::getCurrentUser()['username'])[0] . '/' .$iosystemMountField;
            if (!is_dir($folder))
            {
                $mountExistError=true;
            }
        }
        else
        {
            $ifolder=Yii::$app->params['userDataPath'] . explode('@',User::getCurrentUser()['username'])[0] . '/' .$isystemMountField;
            $ofolder=Yii::$app->params['userDataPath'] . explode('@',User::getCurrentUser()['username'])[0] . '/' .$osystemMountField;

            if ( (!is_dir($ifolder)) || (!is_dir($ofolder)) )
            {
                $mountExistError=true;
            }
        }
        
        

        /* 
         * Add parameters for the active form
         */
        $form_params =
        [
            'action' => URL::to(['software-mpi/run','name'=>$name, 'version'=>$version, 'project'=>$project]),
            'options' => 
            [
                'class' => 'software_commands_form',
                'id'=> "software_commands_form"
            ],
            'method' => 'POST'
        ];
        

        // print_r($fields);
        // exit(0);
        $hasExample=$softwareModel::hasExample($name,$version);
        $username=User::getCurrentUser()['username'];
        $uploadedBy=Software::uploadedBy($name,$version);
        $superadmin=(User::hasRole("Admin", $superAdminAllowed = true)) ? 1 : 0;
        $quotas=Software::getOndemandProjectQuotas($username,$project);

        if(empty($quotas))
        {
            
            return $this->render('project_error',['project'=>$project]);
        }
        $quotas=$quotas[0];

        $jobUsage=RunHistory::find()->where(['username'=>$username,'project'=>$project,])
            ->andFilterWhere(
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

        $type=2;

        return $this->render('run', ['form_params'=>$form_params, 'name'=>$name, 
            'version'=>$version,  'jobid'=>$jobid, 
            'errors'=>$errors, 'runErrors'=>$runError,
            'fields'=>$fields,'isystemMount' => $isystemMountField, 'osystemMount' => $osystemMountField,
            'iosystemMount' => $iosystemMountField, 'example' => '0', 'hasExample'=>$hasExample,
            'username'=>$username,'icontMount'=>$icontMount,'ocontMount'=>$ocontMount,
            'iocontMount'=>$iocontMount,'mountExistError'=>false, 'cluster'=>$cluster,
            'superadmin'=>$superadmin,'uploadedBy'=>$uploadedBy,'jobUsage'=>$jobUsage,'quotas'=>$quotas,
            'maxMem'=>$maxMem, 'maxCores'=>$maxCores, 'project'=>$project,'pernode'=>$pernode,'processes'=>$processes, 'software_instructions'=>$software_instructions, 'type'=>$type]);


    }

    public function actionReattach($jobid)
    {

    	$history=RunHistory::find()->where(['jobid'=>$jobid])->one();
        if (empty($history))
        {
            return $this->render('job_not_found',['jobid'=>$jobid]);
        }

        $name=$history->softname;
        
        $machineType=$history->machinetype;
        $project=$history->project;
        $version=$history->softversion;
        $maxMem=$history->max_ram;
        $maxCores=$history->max_cpu;
        $isystemMountField=$history->imountpoint;
        $osystemMountField=$history->omountpoint;
        $iosystemMountField=$history->iomountpoint;
        $pernode=$history->mpi_proc_per_node;
        $processes=$history->mpi_proc;
        $cluster='running';
        
        $software=Software::find()->where(['name'=>$name,'version'=>$version])->one();
        $software_instructions=$software->instructions;
        $icontMount=$software->imountpoint;
        $ocontMount=$software->omountpoint;
        $iocontMount='';
        if ((!empty($icontMount)) && (!empty($ocontMount)))
        {
            if ($icontMount==$ocontMount)
            {
                $iocontMount=$icontMount;
            }
        }
        

        /* 
         * Add parameters for the active form
         */
        // $form_params =
        // [
        //     'action' => URL::to(['software-mpi/run','name'=>$name, 'version'=>$version,'project'=>$project]),
        //     'options' => 
        //     [
        //         'class' => 'software_commands_form',
        //         'id'=> "software_commands_form"
        //     ],
        //     'method' => 'POST'
        // ];
        

       $fields=SoftwareInput::find()->where(['softwareid'=>$software->id])->orderBy(['position'=> SORT_ASC])->all();
        /*
         * fill the values for the fields and get back that object
         */
        $fields=Software::getRerunFieldValues($jobid,$fields);

        $hasExample=Software::hasExample($name,$version);
        $superadmin=(User::hasRole("Admin", $superAdminAllowed = true)) ? 1 : 0;
        $user=User::getCurrentUser()['username'];

        $quotas=Software::getOndemandProjectQuotas($user,$project);

        if(empty($quotas))
        {
            return $this->render('project_error',['project'=>$project]);
        }
        $quotas=$quotas[0];

        

        $projects=Software::getActiveProjects();

        
        $mountpointExistError=false;
        // print_r($οsystemMount);
        // exit(0);
        

        /* 
         * Add parameters for the active form
         */
        $form_params =
        [
            'action' => URL::to(['software-mpi/run','name'=>$name, 'version'=>$version, 'project'=>$project]),
            'options' => 
            [
                'class' => 'software_commands_form',
                'id'=> "software_commands_form"
            ],
            'method' => 'POST'
        ];

        /*
         * If the user has posted a CWL file, then get the fields
         * for the custom software form from the database.
         */
        // $fields=Software::getSoftwareFields($name,$version);
        $superadmin=(User::hasRole("Admin", $superAdminAllowed = true)) ? 1 : 0;

        $hasExample=Software::hasExample($name,$version);
        $uploadedBy=Software::uploadedBy($name,$version);


        // $script=$softwareModel::getScript($name,$version);
        $container_command='';
        $errors='';

        if(!isset($projects[$project]))
        {
            
            return $this->render('project_error',['project'=>$project]);
        }
        



        /*
         * If the form has posted but the commands box is empty, then add error in the error list
         */
        // $errors=(isset($_POST['commands'])) ? $softwareModel::checkErrors($commands) : [];


        /*
         * Get the user-data folder of the current user.
         * If it does not exist, then create it.
         */
        $userFolder=Yii::$app->params['userDataPath'] . explode('@',User::getCurrentUser()['username'])[0];
        $user=User::getCurrentUser()['username'];

        /**
         * Get the available jobs by getting the quota from eg-ci and counting the jobs
         * that are over 60 seconds long.
         */
        $quotas=Software::getOndemandProjectQuotas($user,$project);

        // if(empty($quotas))
        // {
            
        //     return $this->render('project_error',['project'=>$project]);
        // }
        $quotas=$quotas[0];

        $jobUsage=RunHistory::find()->where(['project'=>$project,])
            ->andFilterWhere(
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

        $errors='';
        $runError='';
        // print_r($fields);
        // print_r("<br /><br />");
        // print_r($field_values);
        // exit(0);
        /*
         * Check if the pod for the current job ID is already running
         */
        $clusterRunning=($cluster!='down') ? true: false;

        $type=2;

        return $this->render('run', ['form_params'=>$form_params, 'name'=>$name, 
            'version'=>$version,  'jobid'=>$jobid, 
            'errors'=>$errors, 'runErrors'=>$runError,
            'fields'=>$fields,'isystemMount' => $isystemMountField, 'osystemMount' => $osystemMountField,
            'iosystemMount' => $iosystemMountField, 'example' => '0', 'hasExample'=>$hasExample,
            'username'=>$user,'icontMount'=>$icontMount,'ocontMount'=>$ocontMount,
            'iocontMount'=>$iocontMount,'mountExistError'=>false, 'cluster'=>$cluster,
            'superadmin'=>$superadmin,'uploadedBy'=>$uploadedBy,'jobUsage'=>$jobUsage,'quotas'=>$quotas,
            'maxMem'=>$maxMem, 'maxCores'=>$maxCores, 'project'=>$project,'pernode'=>$pernode,'processes'=>$processes, 'software_instructions'=>$software_instructions, 'type'=>$type]);
    }



    public function actionDownloadPreviousCwl($name,$version)
    {
        $filepath=Software::getSoftwarePreviousCwl($name, $version);
        $fileexploded=explode('/',$filepath);
        $filename=end($fileexploded);
        // print_r($filename);
        // print_r("<br />");
        // print_r($filepath);
        // exit(0);

        return Yii::$app->response->sendFile($filepath,$filename);
    }

    public function actionAddExample($name,$version)
    {
        $model=new Software;

        $fields=SoftwareInput::find()->where(['softwareid'=>$software->id])->orderBy(['position'=> SORT_ASC])->all();
        $valueErrors=[];
        if (Yii::$app->request->getIsPost())
        {
            // print_r($_FILES);
            // exit(0);
            $folder=Yii::$app->params['userDataPath'] . '/examples/' . $name . '/' . $version . '/input/';
            // print_r($folder);
            // exit(0);
            $command="mkdir -p $folder";
            exec($command,$output,$ret);

            $command="chmod 777 $folder";
            exec($command,$output,$ret);

            $i=0;
            $j=0;
            $k=0;
            $values=[];
            $fieldValues=isset($_POST['field_values']) ? $_POST['field_values'] : [];
            // print_r($_FILES['field_values']);
            // exit(0);
            $fileNames=$_FILES['field_values']['name'];
            $tmpFiles=$_FILES['field_values']['tmp_name'];
            // print_r($_POST);
            // exit(0);
            // print_r($fileNames);
            // print_r("<br />");
            // print_r($tmpFiles);
            // exit(0);

            $errorsExist=false;
            foreach ($fields as $field)
            {
                if ($field['field_type']=='file')
                {
                    if (empty($fileNames[$i]))
                    {
                        $valueErrors[]='File cannot be empty.';
                        $errorsExist=true;
                    }
                    else
                    {
                        $path=$folder . $fileNames[$i];
                        // print_r($tmpFiles[$i]);
                        // print_r($path);
                        // exit(0);
                        move_uploaded_file($tmpFiles[$i],$path);
                        $values[]=$fileNames[$i];
                        $valueErrors[]='';
                    }

                    $i++;
                }
                else
                {
                    if (empty($fieldValues[$j]))
                    {
                        $valueErrors[]='Field cannot be empty.';
                        $errorsExist=true;
                    }
                    else
                    {
                        $values[]=$fieldValues[$j];
                        $valueErrors[]='';
                    }

                    $j++;

                }

                $k++;
            }
            
            $model::updateExampleFields($name,$version,$values);

            if (!$errorsExist)
            {
                $user=User::getCurrentUser()['username'];
                $projectsDropdown=Software::getActiveProjects();
                $software=$model::getSoftwareNames($user);
                $descriptions=Software::getSoftwareDescriptions($user);
                $success="Example for $name v.$version successfully added!";


                //$user=User::getCurrentUser()['username'];

                /**
                * Is the user SuperAdmin?
                */
                $superadmin=(User::hasRole("Admin", $superAdminAllowed = true)) ? 1 : 0;
        
                if ($superadmin==1)
                {
                    $softUser='admin';
                }
                else
                {
                    $softUser=$user;
                }

                return $this->render('index',['software' => $software, 'user'=> $user,
                                      'superadmin' => $superadmin,
                                      'success'=>$success,'warning'=>'',
                                      'error' =>'', 'projectsDropdown'=>$projectsDropdown,
                                      'descriptions'=>$descriptions,]);
            }

        }

        return $this->render('add_example',[ 'fields' => $fields, 'name'=>$name, 'version'=>$version, 'valueErrors'=>$valueErrors]);
    }




    public function actionJobDetails($jobid)
    {

        // $softwareModel=new Software;
        $result=Software::getJobDetails($jobid);
        if (empty($result))
        {
            return $this->render('job_not_found',['jobid'=>$jobid]);
        }

        $machineType=$result['machinetype'];
        $status=empty($result['status']) ? 'Running' : $result['status'];
        $start=date("F j, Y, H:i:s",strtotime($result['start']));
        $stop=empty($result['stop']) ? 'Not available yet' : date("F j, Y, H:i:s",strtotime($result['stop']));     
        $isystemMountField=$result['imountpoint'];
        $osystemMountField=$result['omountpoint'];
        $iosystemMountField=$result['iomountpoint'];
        if(empty($result['stop']))
        {
            $totalExecutionTime='Not available yet';  
        }
        else
        {
            $totalExecutionTime=date("H:i:s",(strtotime($stop)-strtotime($start)));
        }

        $maxRam=empty($result['ram']) ? 'Not available' : $result['ram'];
        $maxCpu=empty($result['cpu']) ? 'Not available' : $result['cpu']/1000;

       
       // rint_r(strtotime($stop));
       // print_r("");
       // print_r(strtotime($start));
    
             

        // $rows=[
        //     'Software name' => $result['softname'],
        //     'Software version' => $result['softversion'],
        //     'Status'=> $status,
        //     'Started on'=>$start,
        //     'Stopped on' => $stop,
        //     'Total execution time' => $totalExecutionTime,
        //     'Maximum memory footprint (GB)' => $maxRam,
        //     'Maximum CPU load (cores)' => $maxCpu,
        //     'Machine Type' => $machineType,
        // ];

        // if (!empty($iosystemMountField))
        // {
        //     $rows['I/O Mountpoint']=$iosystemMountField;
        // }
        // else
        // {
        //     if (!empty($isystemMountField))
        //     {
        //         $rows['Input Mountpoint']=$isystemMountField;
        //     }

        //     if (!empty($osystemMountField))
        //     {
        //         $rows['Output Mountpoint']=$osystemMountField;
        //     }
        // }

        return $this->render('job_details',['name'=>$result['softname'],'version'=>$result['softversion'],
                                            'status'=>$status, 'start'=>$start, 'stop'=>$stop, 'execTime'=>$totalExecutionTime,
                                            'ram'=>$maxRam,'cpu'=>$maxCpu,'machineType' =>$machineType,
                                            'iomount'=>$iosystemMountField, 'imount'=>$isystemMountField, 'omount'=>$osystemMountField,
                                            'jobid'=>$jobid,
                                            ]);


    }


    public function actionUserStatistics()
    {

        $softwareModel=new Software;
        $user=User::getCurrentUser()['username'];
        $projectTotals=$softwareModel::getUserStatistics($user);
        $projectAggr=$softwareModel::getUserStatisticsPerProject($user);
        $quotas=Software::getActiveProjectQuotas($user);
      //  $tim=str_replace(":", "," , $result[2]);
      //  $time=str_replace("," , "." , $result[2]);
       
        
        

         // print_r($result);
         // exit(0);

        // $rows=[
        //     'Number of jobs'=>$result[0],
        //     'Number of completed jobs'=>$result[1],
        //     // 'Started on'=>$start,
        //     // 'Stopped on' => $stop,
        //     'Total execution time (in hours)' => $result[2],
        //     'Average memory footprint (GB)' => $result[3],
        //     'Average CPU load (cores)' => round($result[4])/1000,
            
        // ];

      
        
        return $this->render('user_statistics',['projectTotals'=>$projectTotals,'projectAggr'=>$projectAggr, 'quotas'=>$quotas]);       
              

    }

    public function actionImageDescription($name,$version)
    {
        $model=Software::find()->where(['name'=>$name, 'version'=>$version])->one();

        return $this->renderAjax('image_description',['model'=>$model]);
    }

    public function actionDownloadLogs($jobid)
    {
        $filepath=Yii::$app->params['tmpFolderPath'] . $jobid . '/logs.txt';
        $filename='logs.txt';
        // print_r($filename);
        // print_r("<br />");
        // print_r($filepath);
        // exit(0);
        if (file_exists($filepath))
        {
            return Yii::$app->response->sendFile($filepath,$filename);
        }
        else
        {
            return false;
        }
        
    }
    
}



