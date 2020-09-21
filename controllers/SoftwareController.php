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
use app\models\ContactForm;
use app\models\Software;
use app\models\SoftwareUpload;
use app\models\SoftwareUploadExisting;
use app\models\SoftwareEdit;
use app\models\SoftwareRemove;
use app\models\ImageRequest;
use yii\helpers\Url;
use webvimark\modules\UserManagement\models\User;
use app\models\RunHistory;
use app\models\SoftwareInput;
use app\models\Workflow;
use yii\data\Pagination;

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
            exec("mkdir $userFolder");
            exec("chmod 777 $userFolder");
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

        // print_r($software);
        // exit(0);
        $descriptions=Software::getSoftwareDescriptions($softUser);
        $images=Software::getOriginalImages($softUser);
        $projectsDropdown=Software::getActiveProjects();
        $remaining_jobs_array=[];
        foreach ($projectsDropdown as $project) 
        {
              $project_name=trim(explode('(',$project)[0]);
              $remaining=trim(explode(' ',$project)[1]);
              $remaining_jobs=trim(explode('(', $remaining)[1]);
              $projectsDropdown[$project_name]=$project_name;
              $remaining_jobs_array["$project_name"]=$remaining_jobs;
              
        }


        
         $indicators=Software::getIndicators($softUser);
        
                

        return $this->render('index',['software' => $software, 'user'=> $user,
                                      'superadmin' => $superadmin, 'projectsDropdown'=>$projectsDropdown,'descriptions'=>$descriptions,
                                      'success'=>'','warning'=>'','error' =>'','selected_project'=>$selected_project,'indicators'=>$indicators,
                                      'images'=>$images, 'remaining_jobs_array'=>$remaining_jobs_array]);
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


    public function actionRun($name, $version,$project)
    {

        $software=Software::find()->where(['name'=>$name,'version'=>$version])->one();
        if (empty($software))
        {
            return $this->render('no_software',['name'=>$name,'version'=>$version]);
        }

        /*
         * Get name and version from the browser link
         */
        // $name=$_GET['name'];
        // $version=$_GET['version'];
        // $project=$_GET['project'];
        $projects=Software::getActiveProjects();

        if(!isset($projects[$project]))
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
        $machineType=isset($_POST['machineType']) ? $_POST['machineType'] : '';
        // $field_values=isset($_POST['field_values']) ? $_POST['field_values'] : [];
        $example=(isset($_POST['example']) && ($_POST['example']=="1")) ? true : false;

        /*
         * contMount variables contain the mountpoints inside the container as specified during the addition process
         * SystemMount variableσ contain the local path that will be mounted to the container.
         */
        // $containerMounts=$softwareModel::getContainerMountpoint($name,$version);
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
        // print_r($iocontMount);
        // exit(0);

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

        $podid=Software::runningPodIdByJob($name,$jobid);
        // print_r($podid);
        // exit(0);
        

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
        // print_r($fields);
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
        // var_dump($fields[--$index]);
        // exit(0);
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
        $maxMem=(!isset($_POST['memory'])) || (empty($_POST['memory'])) || floatval($_POST['memory'])<=0 || (floatval($_POST['memory']) > floatval($quotas['ram'])) ? floatval($quotas['ram']) : floatval($_POST['memory']);
        $maxCores=(!isset($_POST['cores'])) || (empty($_POST['cores'])) || floatval($_POST['cores'])<=0 || (floatval($_POST['cores']) > floatval($quotas['cores'])) ? floatval($quotas['cores']) : floatval($_POST['cores']);

        /*
         * Check if the pod for the current job ID is already running
         */
        $podRunning=Software::isAlreadyRunning($podid);


        /*
         * If the form has posted, it is not empty and the pod is not already running,
         * run the command for the specific docker image.
         */
        if ((empty($errors)) && (!empty($container_command)) && ($podRunning==false) )
        {
            // print_r($οsystemMount);
            // exit(0);
            
            $jobid=uniqid();
            $result=Software::createAndRunJob($container_command, $fields, 
                                                    $name, $version, $jobid, $user, 
                                                    $podid, $machineType, 
                                                    $isystemMount, $isystemMountField,
                                                    $osystemMount, $osystemMountField,
                                                    $iosystemMount, $iosystemMountField,
                                                    $project,$maxMem,$maxCores);
            $runPodId=$result[0];
            $runError=$result[1];
            $machineType=$result[2];
        }

        /*
         * If pod started without errors, then send its ID to the form.
         */

        if ($runPodId!='')

        {
            $podid=$runPodId;
        }

        // print_r($iocontMount);
        // print_r("<br />");
        // print_r($icontMount);
        // print_r("<br />");
        // print_r($ocontMount);
        // print_r("<br />");
        // exit(0);

        return $this->render('run', ['form_params'=>$form_params, 'name'=>$name, 
            'version'=>$version,  'jobid'=>$jobid, 
            'errors'=>$errors, 'runErrors'=>$runError, 'podid'=>$podid, 'machineType'=>$machineType,
            'fields'=>$fields,'isystemMount' => $isystemMountField, 'osystemMount' => $osystemMountField,
            'iosystemMount' => $iosystemMountField, 'example' => '0', 'hasExample'=>$hasExample,
            'username'=>$user,'icontMount'=>$icontMount,'ocontMount'=>$ocontMount,
            'iocontMount'=>$iocontMount,'mountExistError'=>false,
            'superadmin'=>$superadmin,'uploadedBy'=>$uploadedBy,'jobUsage'=>$jobUsage,'quotas'=>$quotas,
            'maxMem'=>$maxMem, 'maxCores'=>$maxCores, 'project'=>$project]);
    }

    /*
     * Get logs from the running pod to show on the webpage
     */
    public function actionGetLogs($podid,$machineType)
    {

        $results=Software::getLogs($podid);
        $logs=$results[1];
        $status=$results[0];
        $time=$results[2];
        

        return $this->renderPartial('logs',['logs'=>$logs, 'status'=>$status, 'machineType'=>$machineType,'time'=>$time]);
    }

    /*
     * Clean up job and delete pod from the system
     */
    public function actionCleanUp($name,$jobid,$status)
    {
        $model=new Software;

        $results=$model::cleanUp($name,$jobid,$status);

    }

    /*
     * Upload new software image
     */
    public function actionUpload()
    {
        $model = new SoftwareUpload();

        $dropdown=[
                'public'=>'Everyone',
                'private' => 'Only me',];
        $command_drop=[
            'cwl'=>'the CWL file',
            'image' => 'the docker image'

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
            $projectsDropdown=Software::getActiveProjects();


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
            $indicators=Software::getIndicators($softUser);;


            return $this->render('index',['software' => $software, 'user'=> $user,
                                      'superadmin' => $superadmin, //'visibility'=>$visibility,
                                      'success'=>$messages[1],'warning'=>$messages[2], 
                                      'error' =>$messages[0], 'projectsDropdown'=>$projectsDropdown,
                                      'descriptions'=>$descriptions,'indicators'=>$indicators,
                                      'images'=>$images,]);

        }

        /*
         * Render the form
         */
        return $this->render('software_upload', [
                    'model' => $model,'dropdown'=> $dropdown, 'dois'=>$dois, 'command_drop'=>$command_drop,
        ]);
    }

    public function actionUploadExisting()
    {
        $model = new SoftwareUploadExisting();

        $dropdown=[
                'public'=>'Everyone',
                'private' => 'Only me',];
        $command_drop=[
            'cwl'=>'the CWL file',
            'image' => 'the docker image'

        ];

        // print_r($_POST);
        // exit(0);
        /*
         * Get dropdown of existing images
         */
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
            // $model->imageFile=UploadedFile::getInstance($model, 'imageFile');
            $model->cwlFile=UploadedFile::getInstance($model, 'cwlFile');
            $model->dois=implode('|',$dois);
            /*
             * Upload image
             */
            $messages=$model->upload();
            $user=User::getCurrentUser()['username'];
            $projectsDropdown=Software::getActiveProjects();


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
            $indicators=Software::getIndicators($softUser);;


            return $this->render('index',['software' => $software, 'user'=> $user,
                                      'superadmin' => $superadmin, //'visibility'=>$visibility,
                                      'success'=>$messages[1],'warning'=>$messages[2], 
                                      'error' =>$messages[0], 'projectsDropdown'=>$projectsDropdown,
                                      'descriptions'=>$descriptions,'indicators'=>$indicators,
                                      'images'=>$images,]);

        }

        /*
         * Render the form
         */
        return $this->render('software_upload_existing', [
                    'model' => $model,'dropdown'=> $dropdown, 'dois'=>$dois, 'command_drop'=>$command_drop,
                    'image_drop'=>$image_drop,
        ]);
    }

    /* 
     * Action to render the software name
     * and/or upload a CLW file (if not already done)
     */
    public function actionEditSoftware($name, $version)
    {
        $username=User::getCurrentUser()['username'];
        $superadmin=(User::hasRole("Admin", $superAdminAllowed = true)) ? 1 : 0;

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

        // $model->dois=explode('|', $model->dois);
        // $dbfields=$softwareModel::getSoftwareEditFields($name, $version);
        
        // $description=(isset($_POST['description']))? $_POST['description'] : $dbfields['description'];
        // $visibility=(isset($_POST['visibility']))? $_POST['visibility'] : $dbfields['visibility'];
        

        // $mountpoint=(isset($_POST['mountpoint']))? $_POST['mountpoint'] : $dbfields['mountpoint'];
        // $workingdir=(isset($_POST['workingdir']))? $_POST['workingdir'] : $dbfields['workingdir'];
        // $biotools=(isset($_POST['biotools'])) ? $_POST['biottols'] : $dbfields['biotools'];
        $model->dois=array_filter(explode('|', $model->dois));
        // print_r($model->description);
        // exit(0);
        /** 
         * If the form has posted get file and software name
         *
         */
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
            $success="Software $name v.$version successfully updated!";

            $user=User::getCurrentUser()['username'];

            $projectsDropdown=Software::getActiveProjects();
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

            $software=Software::getSoftwareNames($softUser);
            $descriptions=Software::getSoftwareDescriptions($softUser);
            $images=Software::getOriginalImages($softUser);
            $indicators=Software::getIndicators($softUser);;

            return $this->render('index',['software' => $software, 'user'=> $user,
                                      'superadmin' => $superadmin,
                                      'success'=>$messages[1],'warning'=>$messages[2],
                                      'error' =>$messages[0], 'projectsDropdown'=>$projectsDropdown,
                                      'descriptions'=>$descriptions,'indicators'=>$indicators,
                                      'images'=>$images,]);


        }

        return $this->render('software_edit',['model'=>$model,'vdropdown'=>$vdropdown]);

        

        
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
        // print_r(empty($image)?0:1);
        // exit(0);

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


        // $user=User::getCurrentUser()['username'];
        $projectsDropdown=Software::getActiveProjects();


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
        /**
         * Get the list of software
         */
        $model=new Software;

        $software=$model::getSoftwareNames($softUser);
        $descriptions=Software::getSoftwareDescriptions($softUser);
        $images=Software::getOriginalImages($softUser);
        $indicators=Software::getIndicators($softUser);
        

        return $this->render('index',['software' => $software, 'user'=> $user,
                                      'superadmin' => $superadmin,
                                      'success'=>$success,'warning'=>'',
                                      'error' =>$error, 'projectsDropdown'=>$projectsDropdown,
                                      'descriptions'=>$descriptions,'indicators'=>$indicators,
                                      'images'=>$images,]);
    }

    public function actionSelectMountpoint($username)
    {
        $model=new Software;
        $directory=Yii::$app->params['userDataPath'] . explode('@',User::getCurrentUser()['username'])[0];

        $folders=Software::listDirectories($directory);
        // print_r($directory);
        // exit(0);
        
        return $this->renderAjax('select_mountpoint',['folders'=>$folders]);
    }

    public function actionHistory()
    {


        $user=User::getCurrentUser()['username'];


        $inactiveJobs=Software::getInactiveJobs();
        // print_r($inactiveJobs);
        // exit(0);
        
        foreach ($inactiveJobs as $job)
        {
            Software::cleanUp($job[0],$job[1],'Complete');

        }
        $available=Software::getAvailableSoftware();
        $available_workflows=Workflow::getAvailableWorkflows();
        // $results=Software::getUserHistory($user);
        $query=RunHistory::find()->where(['username'=>$user])->orderBy(['start'=>SORT_DESC]);
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count]);
        $results = $query->offset($pagination->offset)
                ->limit($pagination->limit)
                ->all();


        // $pages=$results[0];
        // $results=$results[1];
        
        return $this->render('history',['results'=>$results,'pagination'=>$pagination,'available'=>$available,'available_workflows'=>$available_workflows]);
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
        $commands='';

        $software=Software::find()->where(['name'=>$name,'version'=>$version])->one();

        $fields=SoftwareInput::find()->where(['softwareid'=>$software->id])->orderBy(['position'=> SORT_ASC])->all();
        /*
         * fill the values for the fields and get back that object
         */
        $fields=Software::getRerunFieldValues($jobid,$fields);

        if (is_null($fields))
        {
            return $this->render('cwl_changed');
        }


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
            'action' => URL::to(['software/run','name'=>$name, 'version'=>$version, 'project'=>$project]),
            'options' => 
            [
                'class' => 'software_commands_form',
                'id'=> "software_commands_form"
            ],
            'method' => 'POST'
        ];
        
        // $fields=SoftwareInput::find()->where(['softwareid'=>$software->id])->orderBy(['position'=> SORT_ASC])->all();

        // if (!empty($fields))
        // {
        //     $k=0;
        //     for ($i=0; $i<count($fields); $i++)
        //     {
        //         /**
        //          * If the field has a prefix
        //          */
        //         if (!empty($fields[$i]['prefix']))
        //         {
        //             $k++;
        //         }

        //         if (empty($field_values))
        //         {
        //             $fields[$i]['value']='';
        //         }
        //         else
        //         {
        //             if (!empty($field_values[$i]))
        //             {
        //                 $emptyFields=false;
        //             }
        //             if (($fields[$i]['field_type']!='file') && ($fields[$i]['field_type']!='File'))
        //             {
        //                 $fields[$i]['value']=$field_values[$k];
        //             }
        //             else
        //             {
        //                 $fields[$i]['value']=substr($field_values[$k],strlen($icontMount));
        //                 // print_r($fields[$i]['value']);
        //                 // echo " ";
        //                 if (substr($fields[$i]['value'],0,1)=='/')
        //                 {
        //                     $fields[$i]['value']=substr($fields[$i]['value'],1);
        //                 }
        //                 // print_r($fields[$i]['value']);
        //                 // echo "<br />";
        //             }
        //         }
        //         $k++;
        //     }
        // }
        // print_r($fields);
        // exit(0);
        $hasExample=$software->has_example;
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

        return $this->render('run', ['form_params'=>$form_params, 'name'=>$name, 
            'version'=>$version,  'jobid'=>'',
            'errors'=>'', 'runErrors'=>'', 'podid'=>'', 'machineType'=>'',
            'fields'=>$fields,'isystemMount' => $isystemMountField, 'osystemMount' => $osystemMountField,
            'iosystemMount' => $iosystemMountField, 'example' => '0', 
            'hasExample'=>$hasExample, 'superadmin'=>$superadmin,
            'username'=>$username,'icontMount'=>$icontMount,'ocontMount'=>$ocontMount,
            'iocontMount'=>$iocontMount,'mountExistError'=>$mountExistError,
            'jobUsage'=>$jobUsage,'quotas'=>$quotas,
            'maxMem'=>$maxMem, 'maxCores'=>$maxCores, 'project'=>$project]);
    }

    public function actionReattach($jobid)
    {

    	// $result=$softwareModel::getRerunData($jobid);
        
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

        $podid=Software::runningPodIdByJob($name,$jobid);
        /** 
         * The following eliminates empty arguments if the user 
         * has used a space char athe the end of the argument
         */
        // $commTokensTmp=str_replace($software->script,'',$result['command']);
        // // print_r($result['command']);
        // // print_r($commTokensTmp);
        // // exit(0);

        // $commTokensTmp=explode(' ',$commTokensTmp);
        // $commTokens=[];
        // foreach ($commTokensTmp as $token)
        // {
        //     $token=trim($token);
        //     if ((empty($token)))
        //     {
        //         continue;
        //     }
        //     $commTokens[]=$token;
        // }

        // print_r($commTokens);
        // exit(0);
        // $field_values=$commTokens;
        // $fields=$softwareModel::getSoftwareFields($name,$version);
        $software=Software::find()->where(['name'=>$name,'version'=>$version])->one();
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

        // if (!empty($fields))
        // {
        //     $k=0;
        //     for ($i=0; $i<count($fields); $i++)
        //     {
        //         /**
        //          * If the field has a prefix
        //          */
        //         if (!empty($fields[$i]['prefix']))
        //         {
        //             $k++;
        //         }

        //         if (empty($field_values))
        //         {
        //             $fields[$i]['value']='';
        //         }
        //         else
        //         {
        //             if (!empty($field_values[$i]))
        //             {
        //                 $emptyFields=false;
        //             }
        //             if ($fields[$i]['field_type']!='file')
        //             {
        //                 $fields[$i]['value']=$field_values[$k];
        //             }
        //             else
        //             {
        //                 $fields[$i]['value']=substr($field_values[$k],strlen($icontMount));
        //                 if (substr($fields[$i]['value'],0,1)=='/')
        //                 {
        //                     $fields[$i]['value']=substr($fields[$i]['value'],1);
        //                 }
        //             }
        //         }
        //         $k++;
        //     }
        // }
        $hasExample=$software->has_example;
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

        return $this->render('run', ['form_params'=>$form_params, 'name'=>$name, 
            'version'=>$version,  'jobid'=>$jobid,
            'errors'=>'', 'runErrors'=>'', 'podid'=>$podid, 'machineType'=>$machineType,
            'fields'=>$fields,'isystemMount' => $isystemMountField, 'osystemMount' => $osystemMountField,
            'iosystemMount' => $iosystemMountField, 'example' => '0', 
            'hasExample'=>$hasExample, 'superadmin'=>$superadmin,
            'username'=>$username,'icontMount'=>$icontMount,'ocontMount'=>$ocontMount,'iocontMount'=>$iocontMount,
            'mountExistError'=>false,
            'jobUsage'=>$jobUsage,'quotas'=>$quotas,
            'maxMem'=>$maxMem, 'maxCores'=>$maxCores, 'project'=>$project]);


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
            exec($command,$output,$ret);

            $command="chmod 777 $folder";
            exec($command,$output,$ret);

            // $i=0;
            // $j=0;
            // $k=0;
            $values=[];

            // print_r($_FILES);
            // print_r("<br />");
            // print_r($_POST);
            // exit(0);
            // $fieldValues=isset($_POST['field_values']) ? $_POST['field_values'] : [];
            // print_r($_FILES['field_values']);
            // exit(0);
            // $fileNames=$_FILES['field_values']['name'];
            // $tmpFiles=$_FILES['field_values']['tmp_name'];
            // print_r($_POST);
            // exit(0);
            // print_r($fileNames);
            // print_r("<br />");
            // print_r($tmpFiles);
            // exit(0);

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
                
                $projectsDropdown=Software::getActiveProjects();
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

                return $this->render('index',['software' => $software, 'user'=> $user,
                                      'superadmin' => $superadmin,
                                      'success'=>$success,'warning'=>'',
                                      'error' =>'', 'projectsDropdown'=>$projectsDropdown,
                                      'descriptions'=>$descriptions,'indicators'=>$indicators,
                                      'images'=>$images,]);
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
        // print_r($directory);
        // exit(0);
        $files=Software::listFiles($directory);
        // foreach ($files as $directory=>$items)
        // {
        //     print_r($directory . '<br />');
        //     print_r($items);
        //     print_r('<br />' . '<br />');
        // }
        // exit(0);
        
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
        // print_r($directory);
        // exit(0);
        $folders=Software::listDirectories($directory);
        // foreach ($files as $directory=>$items)
        // {
        //     print_r($directory . '<br />');
        //     print_r($items);
        //     print_r('<br />' . '<br />');
        // }
        // exit(0);
        
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
        // foreach ($files as $directory=>$items)
        // {
        //     print_r($directory . '<br />');
        //     print_r($items);
        //     print_r('<br />' . '<br />');
        // }
        // exit(0);
        
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
}



