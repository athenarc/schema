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
use app\models\ContactForm;
use app\models\Software;
use app\models\SoftwareUpload;
use app\models\SoftwareUploadExisting;
use app\models\SoftwareEdit;
use app\models\SoftwareRemove;
use app\models\ImageRequest;
use app\models\RoCrate;
use yii\helpers\Url;
use webvimark\modules\UserManagement\models\User;
use app\models\RunHistory;
use app\models\SoftwareInput;
use app\models\WorkflowInput;
use app\models\Workflow;
use yii\data\Pagination;
use app\models\SoftwareProfiler;
use app\models\SystemConfiguration;

class SoftwareController extends Controller
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
     * Displays the list of all available software
     * along with buttons for running and editing software
     *
     * @return string
     */
    public function actionIndex($selected_project='')
    {
    	
        /**
         * If the user-data folder for the current user does not exist, 
         * create one
         */
        $userFolder=Yii::$app->params['userDataPath'] . explode('@',User::getCurrentUser()['username'])[0];
        $user=User::getCurrentUser()['username'];

        if (!is_dir($userFolder))
        {
            Software::exec_log("mkdir $userFolder");
            Software::exec_log("chmod 777 $userFolder");
        }

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
        /**
         * Get the list of software
         */
        $model=new Software;

        $software=$model::getSoftwareNames($softUser);

        
        $descriptions=Software::getSoftwareDescriptions($softUser);
        $images=Software::getOriginalImages($softUser);
        
        $indicators=Software::getIndicators($softUser);

        $profiled=Software::isProfiled();
     

        return $this->render('index',['software' => $software, 'user'=> $user,
                                      'superadmin' => $superadmin,'descriptions'=>$descriptions,
                                      'success'=>'','warning'=>'','error' =>'','indicators'=>$indicators,
                                      'images'=>$images, 'profiled'=>$profiled]);
    }

    /**
     * Action to run a docker image uploaded in the system
     */

    public function actionImageRequest()
    {
        $model=new ImageRequest;
        $user=User::getCurrentUser()['username'];
        $username=explode('@', $user)[0];
        
        if ($model->load(Yii::$app->request->post()) && $model->validate()) 
        {
            $date=date("Y-m-d H:i:s");
            $model->date=$date;
            $model->user_name=$username;
            $model->save();
            Yii::$app->session->setFlash('success', "Image request submitted successfully.");
            return $this->redirect(['software/index']);
        }
        return $this->render('image_request', ['model'=>$model]);
    }


    public function actionRun($name, $version)
    {
		if (!isset($_SESSION['selected_project'])) {
			$project='';
		} else {
			$project=$_SESSION['selected_project'];
		}

        $software=Software::find()->where(['name'=>$name,'version'=>$version])->one();

        if (empty($software))
        {
            return $this->render('no_software',['name'=>$name,'version'=>$version]);
        }

        /*
         * Get name and version from the browser link
         */
        $projects=(Yii::$app->params['standalone']==false) ? Software::getActiveProjects() : [];

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
        $example=(isset($_POST['example']) && ($_POST['example']=="1")) ? true : false;
        $outFolder=(isset($_POST['outFolder'])) ? $_POST['outFolder'] : '';


        $username=User::getCurrentUser()['username'];
        $homeFolder=Yii::$app->params['userDataPath'] . explode('@',$user)[0] . '/';
        
        if (!is_dir($homeFolder))
        {
            Software::exec_log("mkdir $homeFolder");
            Software::exec_log("chmod 777 $homeFolder");
        }
        $oSystemFolder=$outFolder;
        $iSystemFolder='';


        if ($example)
        {
            /*
             * @exampleFolder: folder where the examples for each software are stored
             * @ifolder: input folder without relative to user home
             * @iSystemFolder: absolute input folder for user
             */

            $exampleFolder=Yii::$app->params['userDataPath']. 'examples/' . $name . '/' . $version . '/input';
            $ifolder='examples/' . $name . '/' . $version . '/input/';
            $ofolder= 'examples/' . $name . '/' . $version . '/output';
            $iSystemFolder='examples/' . $name . '/' . $version . '/input/';
            $oSystemFolder='examples/' . $name . '/' . $version . '/output/';
            $iSystemFolderMkdir=$homeFolder .'examples/' . $name . '/' . $version . '/input/';
            $oSystemFolderMkdir=$homeFolder .'examples/' . $name . '/' . $version . '/output/';
            $outFolder=$ofolder;
            
            Software::exec_log("mkdir -p $iSystemFolderMkdir $oSystemFolderMkdir");
            Software::exec_log("chmod 777 $iSystemFolder");
            Software::exec_log("chmod 777 $oSystemFolder");
            Software::exec_log("cp -r $exampleFolder/* $iSystemFolder");

            
        }        

        /* 
         * Add parameters for the active form
         */
        $form_params =
        [
            'action' => URL::to(['software/run','name'=>$name, 'version'=>$version, 'project'=>$project]),
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

        $fields=SoftwareInput::find()->where(['softwareid'=>$software->id])->orderBy(['position'=> SORT_ASC])->all();

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

        /*
         * If the software uses the reference data stored in the shared folder
         * mount the shared folder to the pod
         */
        // $sharedFolder=($software->shared)? Yii::$app->params['sharedDataFolder']:'';
        /*
         * If the software uses gpus, pass the appropriate argument for execution
         * in the limits section
         */
        $gpu=($software->gpu)? '1':'0';


        if (!empty($fields))
        {
            $field_count=count($fields);
        }
        else
        {
            $field_count=0;
        }

        /*
         * $emptyFields is a variable that denotes whether all fields are empty.
         */
        $emptyFields=true;
        
        for ($index=0; $index<$field_count; $index++)
        {
            /*
             * If the example is used, the fields values are not empty
             * and values from the example are used
             */
            if ($example)
            {
                $emptyFields=false;
                if ($fields[$index]->field_type=='boolean')
                {
                    $fields[$index]->value=($fields[$index]->example=='true') ? true : false;
                }
                else if ($fields[$index]->field_type=='File')
                {
                    $fields[$index]->value=$ifolder . $fields[$index]->example;
                }
                else if ($fields[$index]->field_type=='Directory')
                {
                    $fields[$index]->value=$ifolder . $fields[$index]->example;
                }
                else
                {
                    $fields[$index]->value=$fields[$index]->example;
                }

            }
            else
            {
                /*
                 * If the field values are empty, assign 
                 * empty values to each field. Boolean fields
                 * get a false
                 *
                 * $emptyFields remains true
                 */
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
                /*
                 * Add the value to each respective field.
                 * For boolean values, strings are substituted
                 */
                else
                {
                    $emptyFields=false;
                    if ($fields[$index]->field_type=='boolean')
                    {
                        $fields[$index]->value=($field_values[$index]=="0") ? false : true;
                    }
                    else
                    {
                        $fields[$index]->value=$field_values[$index];
                    }
                }
            }
        }

        $container_command='';
        $errors=[];
        /*
         * If no inputs have been specified, 
         * the script is run without additional arguments
         */
        if (empty($fields))
        {
            /*
             * Check if form has posted
             */
            if (Yii::$app->request->getIsPost())
            {
                $container_command=$software->script;
            }
            $inputs=[];
            $output=[];
        }
        else
        {
            /*
             * If values were provided for each field,
             * create the command.
             */
            if (!$emptyFields)
            {
                $script=$software->script;
                $ios=Software::getIOs($software,$fields,$iSystemFolder,$oSystemFolder);
                $inputs=$ios[0];
                $output=$ios[1];
                $command_errors=Software::createCommand($software,$emptyFields,$fields);
                $errors=$command_errors[0];
                $container_command=$command_errors[1];
            }
            else
            {
                /*
                 * Check if form has posted and post an error
                 */
                if (Yii::$app->request->getIsPost())
                {
                    $errors=["You did not provide arguments for the script"];
                }
            }
        }


        /*
         * Get the user-data folder of the current user.
         * If it does not exist, then create it.
         */
       

        /**
         * Get the available jobs by getting the quota from eg-ci and counting the jobs
         * that are over 60 seconds long.
         */
        $quotas=Software::getOndemandProjectQuotas($user,$project);
        

        if(empty($quotas) && (Yii::$app->params['standalone']==false))
        {          
            return $this->render('project_error',['project'=>$project]);
        }
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


        $runErrors='';

        /**
         * Get the maximum value for memory an cpu from the project if:
         * 
         * 1. the user entered something in the field and it is not empty
         * 2. the value entered is negative
         * 3. the value is smaller than the one specified in the project quota
         *
         */
        $maxMem=(!isset($_POST['memory'])) || (empty($_POST['memory'])) || floatval($_POST['memory'])<=0 || (floatval($_POST['memory']) > floatval($quotas['ram'])) ? floatval($quotas['ram']) : floatval($_POST['memory']);
        $maxCores=(!isset($_POST['cores'])) || (empty($_POST['cores'])) || floatval($_POST['cores'])<=0 || (floatval($_POST['cores']) > floatval($quotas['cores'])) ? floatval($quotas['cores']) : floatval($_POST['cores']);

        /*
         * Check if the pod for the current job ID is already running
         */
        $jobAlreadyRunning=Software::isAlreadyRunning($jobid);

        $limits=['gpu'=>$gpu, 'cpu'=>$maxCores,'ram'=>$maxMem,'gpu'=>$gpu];
        /*
         * If the form has posted, it is not empty and the pod is not already running,
         * run the command for the specific docker image.
         */
        if ( (empty($errors)) && (!empty($container_command)) && (!$jobAlreadyRunning) )
        {
            
            $software->container_command=$container_command;
            $software->limits=$limits;
            $software->inputs=$inputs;
            $software->outputs=$output;
            $software->user=$user;
            $software->project=$project;
            $software->fields=$fields;
            $software->outFolder=$outFolder;

            /*
             * Send the job to TES
             */
            $software->runJob();

            $jobid=$software->jobid;
            $runErrors=$software->errors;
        }

        /*
         * If pod started without errors, then send its ID to the form.
         */
        // print_r($runErrors);
        // exit(0);

        return $this->render('run', ['form_params'=>$form_params, 'software'=>$software, 'example' => '0',
            'errors'=>$errors, 'runErrors'=>$runErrors, 
            'fields'=>$fields,  'username'=>$username, 'superadmin'=>$superadmin,'quotas'=>$quotas,
            'maxMem'=>$maxMem, 'maxCores'=>$maxCores, 'type'=>1, 'outFolder'=>$outFolder]);
    }

    /*
     * Get logs from the running pod to show on the webpage
     */
    public function actionGetLogs($jobid)
    {
        $results=Software::getLogs($jobid);
        $logs=$results[1];
        $status=$results[0];
        $time=$results[2];
        $history=RunHistory::find()->where(['jobid'=>$jobid])->one();

        

        return $this->renderPartial('logs',['logs'=>$logs, 'status'=>$status, 'time'=>$time,'project'=>$history->project]);
    }
    /*
     * Cancel job
     */
    public function actionCancelJob($jobid)
    {
        Software::cancelJob($jobid);
    }

    /*
     * Upload new software image
     */
    public function actionUpload()
    {
        $model = new SoftwareUpload();

        $dropdown=[
                'public'=>'Everyone',
                'private' => 'Only me',

        ];

        $dois=(isset($_POST['dois'])) ? $_POST['dois'] : [];

        // Eliminate duplicate doi entries
        $tmp=[];
        foreach ($dois as $doi)
        {
            $tmp[$doi]='';
        }
        $dois=[];
        foreach ($tmp as $doi => $dummyValue)
        {
            $dois[]=$doi;
        }
        /*
         * Get data from the posted form
         */
        if ($model->load(Yii::$app->request->post()) && $model->validate()) 
        {
            /*
             * Get the files and fields
             */
            $model->imageFile=UploadedFile::getInstance($model, 'imageFile');
            $model->cwlFile=UploadedFile::getInstance($model, 'cwlFile');
            $model->dois=implode('|',$dois);
            /*
             * Upload image
             */
            $messages=$model->upload();
            $user=User::getCurrentUser()['username'];


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
           /**
             * Get the list of software
             */
            $model=new Software;


            if(!empty($messages[1]))
            {
                Yii::$app->session->setFlash('success', "$messages[1]");
            }
            if(!empty($messages[2]))
            {
                Yii::$app->session->setFlash('warning', "$messages[2]");   
            }    
            if(!empty($messages[0]))
            {
                Yii::$app->session->setFlash('danger', "$messages[0]");
            }   

            return $this->redirect(['software/index']);
        
        }

        /*
         * Render the form
         */
        return $this->render('software_upload', [
                    'model' => $model,'dropdown'=> $dropdown, 'dois'=>$dois,]);
    }

    public function actionUploadExisting()
    {
        $model = new SoftwareUploadExisting();

        $dropdown=[
                'public'=>'Everyone',
                'private' => 'Only me',

        ];

    
        $superadmin=(User::hasRole("Admin", $superAdminAllowed = true)) ? 1 : 0;
        $user=User::getCurrentUser()['username'];
        if ($superadmin==1)
        {
            $softUser='admin';
        }
        else
        {
            $softUser=$user;
        }
        $image_drop=Software::getOriginalImagesUploadDropdown($softUser);

        $dois=(isset($_POST['dois'])) ? $_POST['dois'] : [];

        // Eliminate duplicate doi entries
        $tmp=[];
        foreach ($dois as $doi)
        {
            $tmp[$doi]='';
        }
        $dois=[];
        foreach ($tmp as $doi => $dummyValue)
        {
            $dois[]=$dois;
        }
        /*
         * Get data from the posted form
         */
        if ($model->load(Yii::$app->request->post()) && $model->validate()) 
        {
            /*
             * Get the files and fields
             */
            $model->cwlFile=UploadedFile::getInstance($model, 'cwlFile');
            $model->dois=implode('|',$dois);
            /*
             * Upload image
             */
            $messages=$model->upload();
            $user=User::getCurrentUser()['username'];


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
           /**
             * Get the list of software
             */

            if(!empty($messages[1]))
            {
                Yii::$app->session->setFlash('success', "$messages[1]");
            }
            if(!empty($messages[2]))
            {
                Yii::$app->session->setFlash('warning', "$messages[2]");   
            }    
            if(!empty($messages[0]))
            {
                Yii::$app->session->setFlash('danger', "$messages[0]");
            }

            return $this->redirect(['software/index']);

        }

        /*
         * Render the form
         */
        return $this->render('software_upload_existing', [
                    'model' => $model,'dropdown'=> $dropdown, 'dois'=>$dois, 'image_drop'=>$image_drop,
        ]);
    }

    /* 
     * Action to render the software name
     * and/or upload a CLW file (if not already done)
     */
    public function actionEditSoftware($name, $version)
    {
        $username=User::getCurrentUser()['username'];
        $superadmin=(User::hasRole("Admin", $superAdminAllowed = true)) ? true : false;

        if ($superadmin)
        {
            $model=SoftwareEdit::find()->where(['name'=>$name,'version'=>$version])->one();
        }
        else
        {
            $model=SoftwareEdit::find()->where(['name'=>$name,'version'=>$version,'uploaded_by'=>$username])->one();
        }
        
        if (empty($model))
        {
            return $this->render('anauthorized_edit',['name'=>$name,'version'=>$version]); 
        }
        
        if (empty($model->imountoint) && empty($model->omountpoint))
        {
            $model->iomount=false;
        }
        // $softwareModel=new Software;
        $vdropdown=[
                'public'=>'Everyone',
                'private' => 'Only me',];

        $model->dois=array_filter(explode('|', $model->dois));
        
        if ($model->load(Yii::$app->request->post()) && $model->validate()) 
        {
            
            $model->cwlFile=UploadedFile::getInstance($model, 'cwlFile');
            $dois=(isset($_POST['dois'])) ? $_POST['dois'] : [];

            // Eliminate duplicate doi entries
            $tmp=[];
            foreach ($dois as $doi)
            {
                $tmp[$doi]='';
            }
            $dois=[];
            foreach ($tmp as $doi => $dummyValue)
            {
                $dois[]=$doi;
            }
            
            $model->dois=implode('|',$dois);
            
            
            $messages=$model->softwareEdit();
            

            if(!empty($messages[1]))
            {
                Yii::$app->session->setFlash('success', "$messages[1]");
            }
            if(!empty($messages[2]))
            {
                Yii::$app->session->setFlash('warning', "$messages[2]");   
            }    
            if(!empty($messages[0]))
            {
                Yii::$app->session->setFlash('danger', "$messages[0]");
            }
            return $this->redirect(['software/index']);
        }

        return $this->render('software_edit',['model'=>$model,'vdropdown'=>$vdropdown, 'superadmin'=>$superadmin]);

        

        
    }


    public function actionRemoveSoftware($name, $version)
    {
        $model=new Software;
        
        $user=User::getCurrentUser()['username'];
        $superadmin=(User::hasRole("Admin", $superAdminAllowed = true)) ? 1 : 0;

        $query=Software::find()->where(['name'=>$name,'version'=>$version]);
        if (!$superadmin)
        {
            $query->andWhere(['uploaded_by'=>$user,]);
        }

        $image=$query->one();
        

        if (empty($image))
        {
            $success='';
            $error="Image $name v.$version does not exist or you are not allowed to delete it.";
        }
        else
        {
            $messages=$image->softwareRemove($name,$version);
            $success=$messages[0];
            $error=$messages[1];
        }
        
        if ($superadmin==1)
        {
            $softUser='admin';
        }
        else
        {
            $softUser=$user;
        }
        /**
         * Get the list of software
         */
        $model=new Software;

        $software=$model::getSoftwareNames($softUser);
        $descriptions=Software::getSoftwareDescriptions($softUser);
        $images=Software::getOriginalImages($softUser);
        $indicators=Software::getIndicators($softUser);;

        

        if(!empty($messages[0]))
        {
                Yii::$app->session->setFlash('success', "$messages[0]");
        }    
        elseif(!empty($messages[1]))
        {
                Yii::$app->session->setFlash('danger', "$messages[1]");
        }
        
        return $this->redirect(['software/index']);
    }

    public function actionSelectMountpoint($username)
    {
        $model=new Software;
        $directory=Yii::$app->params['userDataPath'] . explode('@',User::getCurrentUser()['username'])[0];

        $folders=Software::listDirectories($directory);
        return $this->renderAjax('select_mountpoint',['folders'=>$folders]);
    }

    public function actionHistory($crate_id='')
    {
        
        /*
         * Add check here whether the ro crate exists. If not, add a flash on top of the page
         * Create model etc or search files
         * If crate_id does not exist then make it an empty string ''
         */
        $user=User::getCurrentUser()['username'];

        $available=Software::getAvailableSoftware();

        $available_workflows=Workflow::getAvailableWorkflows();
        $query=RunHistory::find()->where(['username'=>$user])
        ->andWhere(['mpi_proc'=>null])
        ->orderBy(['start'=>SORT_DESC]);


        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count]);
        $results = $query->offset($pagination->offset)
                ->limit($pagination->limit)
                ->all();
        
        return $this->render('history',['results'=>$results,'pagination'=>$pagination,'available'=>$available,'available_workflows'=>$available_workflows,'crate_id'=>$crate_id]);
    }

    public function actionRerun($jobid)
    {


        /*
         * Get name and version from the browser link
         */
        
        
        $history=RunHistory::find()->where(['jobid'=>$jobid])->one();

        if (empty($history))
        {
            return $this->render('job_not_found',['jobid'=>$jobid]);
        }

        /*
         * If the posted values are filled, use that
         * or assign default values
         */

        $name=$history->softname;
        $version=$history->softversion;
        $project=$history->project;
        $outFolder=$history->omountpoint;
        $maxMem=$history->max_ram;
        $maxCores=$history->max_cpu;

        $software=Software::find()->where(['name'=>$name,'version'=>$version])->one();
        $uploadedBy=$software->uploaded_by;

        $fields=SoftwareInput::find()->where(['softwareid'=>$software->id])->orderBy(['position'=> SORT_ASC])->all();
        /*
         * fill the values for the fields and get back that object
         */
        $fields=Software::getRerunFieldValues($jobid,$fields);

        if (is_null($fields))
        {
            return $this->render('cwl_changed');
        }
        

        /* 
         * Add parameters for the active form
         */
        $form_params =
        [
            'action' => URL::to(['software/run','name'=>$name, 'version'=>$version, 'project'=>$project]),
            'options' => 
            [
                'class' => 'software_commands_form',
                'id'=> "software_commands_form"
            ],
            'method' => 'POST'
        ];
        

        $username=User::getCurrentUser()['username'];
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
        $type=1;

        return $this->render('run', ['form_params'=>$form_params, 'software'=>$software, 'example' => '0',
            'errors'=>'', 'runErrors'=>'', 
            'fields'=>$fields,  'username'=>$username, 'superadmin'=>$superadmin,'quotas'=>$quotas,
            'maxMem'=>$maxMem, 'maxCores'=>$maxCores, 'type'=>1, 'outFolder'=>$outFolder]);
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
        $outFolder=$history->omountpoint;
        $software=Software::find()->where(['name'=>$name,'version'=>$version])->one();
        $software->jobid=$jobid;
        $uploadedBy=$software->uploaded_by;

        /* 
         * Add parameters for the active form
         */
        $form_params =
        [
            'action' => URL::to(['software/run','name'=>$name, 'version'=>$version,'project'=>$project]),
            'options' => 
            [
                'class' => 'software_commands_form',
                'id'=> "software_commands_form"
            ],
            'method' => 'POST'
        ];
        
        $fields=SoftwareInput::find()->where(['softwareid'=>$software->id])->orderBy(['position'=> SORT_ASC])->all();
        /*
         * fill the values for the fields and get back that object
         */
        $fields=Software::getRerunFieldValues($jobid,$fields);

        $username=User::getCurrentUser()['username'];
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

        $type=1;
        return $this->render('run', ['form_params'=>$form_params, 'software'=>$software, 'example' => '0',
            'errors'=>'', 'runErrors'=>'', 
            'fields'=>$fields,  'username'=>$username, 'superadmin'=>$superadmin,'quotas'=>$quotas,
            'maxMem'=>$maxMem, 'maxCores'=>$maxCores, 'type'=>1, 'outFolder'=>$outFolder]);


    }


    public function actionDownloadPreviousCwl($name,$version)
    {
        $filepath=Software::getSoftwarePreviousCwl($name, $version);
        $fileexploded=explode('/',$filepath);
        $filename=end($fileexploded);
        return Yii::$app->response->sendFile($filepath,$filename);
    }

    public function actionAddExample($name,$version)
    {
        
        /*
         * If user is an Admin allow them to edit,
         * or else check if the software belongs to them
         */
        $username=User::getCurrentUser()['username'];
        $superadmin=(User::hasRole("Admin", $superAdminAllowed = true)) ? 1 : 0;

        if ($superadmin)
        {
            $software=Software::find()->where(['name'=>$name,'version'=>$version])->one();
        }
        else
        {
            $software=Software::find()->where(['name'=>$name,'version'=>$version,'uploaded_by'=>$username])->one();
        }
        
        if (empty($software))
        {
            return $this->render('anauthorized_example_add',['name'=>$name,'version'=>$version]);
        }


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
            Software::exec_log($command,$output,$ret);

            $command="chmod 777 $folder";
            Software::exec_log($command,$output,$ret);

            
            $values=[];

            

            if (!empty($fields))
            {
                $fieldCount=count($fields);
            }
            else
            {
                $fieldCount=0;
            }
            $errorsExist=false;
            for ($k=0; $k<$fieldCount; $k++)
            {
                $field=$fields[$k];
                if (!$field->optional)
                {
                    if ($field->field_type=='File')
                    {
                        if (empty($_FILES['field-' . $k]['name']))
                        {
                            $valueErrors[]='File cannot be empty.';
                            $errorsExist=true;
                        }
                        else
                        {
                            $fileName=$_FILES['field-' . $k]['name'];
                            $tmpFile=$_FILES['field-' . $k]['tmp_name'];
                            $path=$folder . $fileName;
                            move_uploaded_file($tmpFile,$path);
                            $field->example=$fileName;
                            $valueErrors[]='';
                        }
                    }
                    else if ($field->field_type=='boolean')
                    {
                        $field->example=($_POST['field-' . $k]=="1") ? 'true': 'false';
                        $valueErrors[]='';
                    }
                    else
                    {
                        if (empty($_POST['field-' . $k]))
                        {
                            $valueErrors[]='Field cannot be empty.';
                            $errorsExist=true;
                        }
                        else
                        {
                            $field->example=$_POST['field-' . $k];
                            $valueErrors[]='';
                        }
                        
                    }
                }
                else
                {
                    if ($field->field_type=='File')
                    {
                        if (empty($_FILES['field-' . $k]['name']))
                        {
                            $field->example='';
                        }
                        else
                        {
                            $fileName=$_FILES['field-' . $k]['name'];
                            $tmpFile=$_FILES['field-' . $k]['tmp_name'];
                            $path=$folder . $fileName;
                            move_uploaded_file($tmpFile,$path);
                            $field->example=$fileName;
                            $valueErrors[]='';
                        }
                    }
                    else if ($field->field_type=='boolean')
                    {
                        $field->example=($_POST['field-' . $k]=="1") ? 'true': 'false';
                        $valueErrors[]='';
                    }
                    else
                    {
                        if (empty($_POST['field-' . $k]))
                        {
                           $field->example='';
                        }
                        else
                        {
                            $field->example=$_POST['field-' . $k];
                            $valueErrors[]='';
                        }
                        
                    }
                }
            }
            
            foreach ($fields as $field)
            {
                $field->save(false);
            }
            $software->has_example=true;
            $software->save(false);
            
            if (!$errorsExist)
            {
                $superadmin=(User::hasRole("Admin", $superAdminAllowed = true)) ? 1 : 0;
                $username=User::getCurrentUser()['username'];
        
                if ($superadmin==1)
                {
                    $user='admin';
                }
                else
                {
                    $user=$username;
                }
                
                $software=Software::getSoftwareNames($user);
                $descriptions=Software::getSoftwareDescriptions($user);
                $images=Software::getOriginalImages($user);
                $success="Example for $name v.$version successfully added!";


                //$user=User::getCurrentUser()['username'];

                /**
                * Is the user SuperAdmin?
                */
        
                if ($superadmin==1)
                {
                    $softUser='admin';
                }
                else
                {
                    $softUser=$user;
                }
                $indicators=Software::getIndicators($softUser);;

                if(!empty($messages[1]))
                {
                    Yii::$app->session->setFlash('success', "$success");
                }
            

                return $this->redirect(['software/index']);

               
            }

        }

        return $this->render('add_example',[ 'fields' => $fields, 'name'=>$name, 'version'=>$version, 'valueErrors'=>$valueErrors]);
    }




    public function actionJobDetails($jobid)
    {

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
        $maxCpu=empty($result['cpu']) ? 'Not available' : $result['cpu'];

       

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
        $quotas=(!Yii::$app->params['standalone'])? Software::getAllProjectQuotas($user) : [];
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
        if (file_exists($filepath))
        {
            return Yii::$app->response->sendFile($filepath,$filename);
        }
        else
        {
            return false;
        }
        
    }



    public function actionSelectFile($caller,$folder)
    {
        if (empty($folder))
        {
            $directory=Yii::$app->params['userDataPath'] . explode('@',User::getCurrentUser()['username'])[0] . '/';
        }
        else
        {
            $directory=Yii::$app->params['userDataPath'] . explode('@',User::getCurrentUser()['username'])[0] . '/' . $folder . '/';
        }
       
        $files=Software::listFiles($directory);
        return $this->renderAjax('file_list',['files'=>$files, 'caller'=>$caller,'root'=>$directory]);
    }
    
    public function actionSelectFolder($caller,$folder)
    {
        if (empty($folder))
        {
            $directory=Yii::$app->params['userDataPath'] . explode('@',User::getCurrentUser()['username'])[0] . '/';
        }
        else
        {
            $directory=Yii::$app->params['userDataPath'] . explode('@',User::getCurrentUser()['username'])[0] . '/' . $folder . '/';
        }
        $folders=Software::listDirectories($directory);
        
        
        return $this->renderAjax('folder_list',['folders'=>$folders, 'caller'=>$caller,'root'=>$directory]);
    }

    public function actionSelectFileMultiple($caller,$folder)
    {
        if (empty($folder))
        {
            $directory=Yii::$app->params['userDataPath'] . explode('@',User::getCurrentUser()['username'])[0] . '/';
        }
        else
        {
            $directory=Yii::$app->params['userDataPath'] . explode('@',User::getCurrentUser()['username'])[0] . '/' . $folder . '/';
        }
        // print_r($directory);
        // exit(0);
        $files=Software::listFiles($directory);
        
        
        return $this->renderAjax('file_list_multiple',['files'=>$files, 'caller'=>$caller,'root'=>$directory]);
    }
    
    public function actionFillArrayField($caller,$content='')
    {
        $fields=explode(';',$content);

        /*
         * If no content is provided add one field
         * to print in the view file
         */
        if (empty($fields))
        {
            $fields[]='';
        }
        
        
        return $this->renderAjax('fill_array_field',['fields'=>$fields,'caller'=>$caller]);
    }


    public function actionCreateExperiment($jobid)
    {
        
        $model=new RoCrate();
        $history=RunHistory::find()->where(['jobid'=>$jobid])->one();
        if($history->type=='job')
        {
            $software_id=$history->software_id;
            $software=Software::find()->where(['id'=>$software_id])->one();
            $fields=SoftwareInput::find()->where(['softwareid'=>$software_id])->orderBy(['position'=> SORT_ASC])->all();
            $fields=Software::getRerunFieldValues($jobid,$fields);
        }
        else
        {
            $workflow_id=$history->software_id;
            $software=Workflow::find()->where(['id'=>$workflow_id])->one();
            $fields=WorkflowInput::find()->where(['workflow_id'=>$workflow_id])->orderBy(['position'=> SORT_ASC])->all();
            $fields=Workflow::getRerunFieldValues($jobid,$fields);
           
        }

       
        $input_data=[];
        foreach ($fields as $field) 
        {

            if(empty($field->optional))
            {
                $optional=0;
            }
            else
            {
                $optional=1;
            }

            $input_data[$field->name]=['id'=>uniqid(),'name'=>$field->name,'url'=>' ', 'type'=>$field->field_type, 'optional'=>$optional];

        }

        if($model->load(Yii::$app->request->post()))
        {
            
            $software_name=$_POST['softname'];
            $software_version=$_POST['softversion'];
            $software_url=$model->software_url;
            
            if($model->input)
            {
                foreach ($model->input as $input_name=>$input_url) 
                {
                   $input_data[$input_name]['url']=$input_url;
                     
                }
            }
           

            $output_data=$model->output;
            $publication=$model->publication;
            $public_value=$model->public;
            $experiment_description=$model->experiment_description;

            if($history->type=='job')
            {
                $result=ROCrate::CreateROObjectSoftware($jobid, $software_name,$software_version,$software_url,$input_data,$output_data,$publication,$experiment_description,$public_value);
            }
            else
            {
                $result=ROCrate::CreateROObjectWorkflow($jobid, $software_name,$software_version,$software_url,$input_data,$output_data,$publication,$experiment_description,$public_value);
            }

            // print_r($model);
            // exit(0);

            Yii::$app->session->setFlash('success', "$result[1]");
        }

        return  $this->redirect(['software/history']);
    }

    public function actionDownloadRocrate($jobid)
    {
        $filepath=Yii::$app->params['ROCratesFolder']. $jobid .'.zip';
        $filename=$jobid .'.zip';
        return Yii::$app->response->sendFile($filepath,$filename);
    }

    public function actionRoCrateHistory($search_parameter='')
    {
        $username=User::getCurrentUser()['username'];
        
        $results=ROCrate::searchROCrate($search_parameter);
        $ro_crates=$results['rocrates'];
        $pagination=$results['pagination'];
        $results_user=[];
        $results_public=[];
        
        foreach ($ro_crates as $ro_crate) 
        {   
            
            if($ro_crate['username']==$username)
            {
                $software_run=RunHistory::find()->where(['jobid'=>$ro_crate['jobid']])->one();
                if (empty($software_run))
                {
                    continue;
                }

                $results_user[$ro_crate['jobid']]=['softname'=>$software_run->softname, 'start'=>$software_run->start, 'stop'=>$software_run->stop, 'username'=>$username,
                'date'=>$ro_crate['date'], 'experiment_description'=>$ro_crate['experiment_description'], 'public'=>$ro_crate['public'], 'link'=>['software/download-rocrate', 'jobid'=>$ro_crate['jobid']]];
            }
            elseif ($ro_crate['public']==true) 
            {
                $software_run=RunHistory::find()->where(['jobid'=>$ro_crate['jobid']])->one();
                if (empty($software_run))
                {
                    continue;
                }

                $results_public[$ro_crate['jobid']]=['softname'=>$software_run->softname, 'start'=>$software_run->start, 'stop'=>$software_run->stop, 'username'=>$ro_crate['username'],
                'date'=>$ro_crate['date'], 'experiment_description'=>$ro_crate['experiment_description'], 'public'=>$ro_crate['public'], 'link'=>['software/download-rocrate', 'jobid'=>$ro_crate['jobid']]];
            }  
        }
        
        return $this->render('ro_crate_history', ['results_user'=>$results_user, 'results_public'=>$results_public, 'pagination'=>$pagination, 'search_parameter'=>$search_parameter]);
    }

    public function actionSearchROCrate($search_parameter)
    {
        $results=ROCrate::searchROCrate($search_parameter);
        return $this->redirect(['ro-crate-history', 'search_parameter'=>$search_parameter]);
    }



}