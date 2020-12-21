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
use yii\helpers\Url;
use webvimark\modules\UserManagement\models\User;
use app\models\RunHistory;
use app\models\SoftwareInput;
use app\models\Workflow;
use app\models\WorkflowInput;
use app\models\WorkflowUpload;
use yii\helpers\BaseFileHelper;


class WorkflowController extends Controller
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
         
         // $working_dir=getcwd();
         // print_r($working_dir);
         // exit(0);
        

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

        $workflows=Workflow::getWorkflowNames($softUser);

        $all_workflows=Workflow::find()->all();
        $nameversion_to_id=[];
        foreach ($all_workflows as $workflow) {
            $nameversion_to_id[$workflow->name][$workflow->version]=$workflow->id;
        }
        $id_to_vis=[];
        foreach ($all_workflows as $workflow) 
        {
            $id_to_vis[$workflow->id]=$workflow->visualize;
        }

       // print_r($nameversion_to_id);
       // exit(0);

       $descriptions=Workflow::getWorkflowDescriptions($softUser);
       $visualizations=Workflow::getWorkflowVisualizations($softUser);
       $indicators=Workflow::getIndicators($softUser);
        
                

        return $this->render('index',['workflows' => $workflows, 'user'=> $user,
                                      'superadmin' => $superadmin,'descriptions'=>$descriptions, 'nameversion_to_id'=>$nameversion_to_id,
                                      'success'=>'','warning'=>'','error' =>'','selected_project'=>$selected_project,'indicators'=>$indicators, 'id_to_vis'=>$id_to_vis, 'visualizations'=>$visualizations,
        ]);
    }

    /**
     * Action to run a docker image uploaded in the system
     */

   


    public function actionRun($name, $version, $project)
    {

        $workflow=Workflow::find()->where(['name'=>$name,'version'=>$version])->one();
        $visualize=$workflow->visualize;
        if (empty($workflow))
        {
            return $this->render('no_software',['name'=>$name,'version'=>$version]);
        }
        $workflow_instructions=$workflow->instructions;

        /*
         * Get name and version from the browser link
         */
        // $name=$_GET['name'];
        // $version=$_GET['version'];
        // $project=$_GET['project'];
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
        // $field_values=isset($_POST['field_values']) ? $_POST['field_values'] : [];
        $example=(isset($_POST['example']) && ($_POST['example']=="1")) ? true : false;
        $outFolder=(isset($_POST['outFolder'])) ? $_POST['outFolder'] : '';
        // print_r($outFolder);
        // exit(0);

        /*
         * contMount variables contain the mountpoints inside the container as specified during the addition process
         * SystemMount variableσ contain the local path that will be mounted to the container.
         */
        // $containerMounts=$softwareModel::getContainerMountpoint($name,$version);

        /* 
         * Fix this when there is an example
         */
        // if ($example)
        // {
        //     $iosystemMountField='';
        //     $isystemMountField='';
        //     $osystemMountField='';
        //     /*
        //      * If iomount is empty, then see if imount and omount are filled
        //      */
        //     if (!empty($iocontMount))
        //     {
        //         $iosystemMountField='examples/' . $name . '/' . $version . '/io/';
        //     }
        //     else 
        //     {
        //         if (!empty($icontMount)) 
        //         {
        //             $isystemMountField='examples/' . $name . '/' . $version . '/input/';
        //         }
        //         if (!empty($ocontMount)) 
        //         {
        //             $osystemMountField='examples/' . $name . '/' . $version . '/output/';
        //         }
        //     }
            
        // }
        

        if ($example)
        {

            $exampleFolder=Yii::$app->params['userDataPath'] . '/' .  'workflow_examples/' . $name . '/' . $version . '/input';
            $outFolder='workflow_examples/' . $name . '/' . $version . '/input';
            $folder=Yii::$app->params['userDataPath'] . '/' . explode('@',User::getCurrentUser()['username'])[0]  . '/'. 'workflow_examples/' . $name . '/' . $version . '/input';
            exec("mkdir -p $folder");
            exec("cp -r $exampleFolder/* $folder",$out,$ret);
            // print_r($folder);
            // exit(0);
            exec("chmod 777 $folder");

            
        }



        

        /* 
         * Add parameters for the active form
         */
        $form_params =
        [
            'action' => URL::to(['workflow/run','name'=>$name, 'version'=>$version, 'project'=>$project]),
            'options' => 
            [
                'class' => 'workflow_arguments_form',
                'id'=> "workflow_arguments_form"
            ],
            'method' => 'POST'
        ];

        /*
         * If the user has posted a CWL file, then get the fields
         * for the custom software form from the database.
         */

        $fields=WorkflowInput::find()->where(['workflow_id'=>$workflow->id])->orderBy(['position'=> SORT_ASC])->all();
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

        $hasExample=$workflow->has_example;
        $uploadedBy=$workflow->uploaded_by;
        

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
                else if ($fields[$index]->field_type=='enum')
                {
                    $tmp_array=explode('|',$fields[$index]->enum_fields);
                    $fields[$index]->dropdownValues=[];
                    foreach ($tmp_array as $item)
                    {
                        $fields[$index]->dropdownValues[$item]=$item;
                    }
                    $fields[$index]->dropdownSelected=$fields[$index]->example;
                    $fields[$index]->value=$fields[$index]->example;
                    // print_r($fields[$index]->dropdownValues);
                    // exit(0);
                }
                else
                {
                    $fields[$index]->value=$fields[$index]->example;
                }

            }
            else
            {
                /*
                 * If the values posted are empty or are not posted yet
                 */
                if (empty($field_values))
                {
                    if ($fields[$index]->field_type=='boolean')
                    {
                        $fields[$index]->value=false;
                    }
                    else if ($fields[$index]->field_type=='enum')
                    {
                        $tmp_array=explode('|',$fields[$index]->enum_fields);
                        $fields[$index]->dropdownValues=[];
                        foreach ($tmp_array as $item)
                        {
                            $fields[$index]->dropdownValues[$item]=$item;
                        }
                        $fields[$index]->dropdownSelected=$fields[$index]->default_value;
                        // print_r($fields[$index]->dropdownValues);
                        // exit(0);
                    }
                    else
                    {
                        $fields[$index]->value='';
                    }
                    
                }
                /*
                 * For each field assign its value for the reload after post
                 */
                else
                {
                    // print_r($field_values);
                    // exit(0);
                    $emptyFields=false;
                    if ($fields[$index]->field_type=='boolean')
                    {
                        // print_r($field_values[$index]);
                        // print_r("<br />");
                        $fields[$index]->value=($field_values[$index]=="0") ? false : true;
                    }
                    else if ($fields[$index]->field_type=='enum')
                    {
                        $tmp_array=explode('|',$fields[$index]->enum_fields);
                        $fields[$index]->dropdownValues=[];
                        foreach ($tmp_array as $item)
                        {
                            $fields[$index]->dropdownValues[$item]=$item;
                        }
                        $fields[$index]->dropdownSelected=$field_values[$index];
                        $fields[$index]->value=$field_values[$index];
                    }
                    else
                    {
                        $fields[$index]->value=$field_values[$index];
                    }
                }
            }
        }
       

        $errors=[];
        if (empty($fields))
        {
            /*
             * Check if form has posted
             */
            if (Yii::$app->request->getIsPost())
            {
                $workflowParams=Workflow::getParameters([]);
            }
        }
        else
        {
            if (!$emptyFields)
            {
                $workflowParams=Workflow::getParameters($fields);
                
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

        // print_r($workflowParams);
        // exit(0);
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
         * Check if the workflow for the current ID is already running
         */
        $workflowRunning=Software::isAlreadyRunning($jobid);


        /*
         * If the form has posted, it is not empty and the pod is not already running,
         * run the command for the specific docker image.
         */
        if ((empty($errors)) && ($workflowRunning==false) &&  (!empty($workflowParams)))
        {
            /*
             * reset jobid because it might be filled from a previous submission
             */
            $jobid='';
            
            $workflowLimits=Workflow::addLimits($workflow,$maxCores,$maxMem);
            $newLocation=$workflowLimits[0];
            $tmpWorkflowFolder=$workflowLimits[1];
            $result=Workflow::runWorkflow($workflow, $newLocation, $tmpWorkflowFolder, $workflowParams, $fields, $user, 
                                                    $project,$maxMem,$maxCores,$outFolder);
            $jobid=$result['jobid'];
            $runError=$result['error'];
        }

        /*
         * If pod started without errors, then send its ID to the form.
         */



        // print_r($iocontMount);
        // print_r("<br />");
        // print_r($icontMount);
        // print_r("<br />");
        // print_r($ocontMount);
        // print_r("<br />");
        // exit(0);
        $type=3;

        return $this->render('run', ['form_params'=>$form_params, 'name'=>$name, 
            'version'=>$version,  'jobid'=>$jobid, 
            'errors'=>$errors, 'runErrors'=>$runError,'fields'=>$fields,
            'example' => '0', 'hasExample'=>$hasExample,
            'username'=>$user,'superadmin'=>$superadmin,'uploadedBy'=>$uploadedBy,'jobUsage'=>$jobUsage,'quotas'=>$quotas, 'workflow_instructions'=>$workflow_instructions,
            'maxMem'=>$maxMem, 'maxCores'=>$maxCores, 'project'=>$project,'outFolder' => $outFolder, 'type'=>$type, 'visualize'=>$visualize]);
    }

    /*
     * Get logs from the running pod to show on the webpage
     */
    public function actionGetLogs($jobid)
    {

        $results=Workflow::getLogs($jobid);
        $taskLogs=$results[2];
        $status=$results[1];
        $time=$results[0];
        $history=RunHistory::find()->where(['jobid'=>$jobid])->one();
        $project=$history->project;
        // print_r($history->project);
        // exit(0);
        

        return $this->renderPartial('logs',['taskLogs'=>$taskLogs, 'status'=>$status, 'time'=>$time, 'project'=>$project]);
        // return $this->render('logs',['taskLogs'=>$taskLogs, 'status'=>$status, 'time'=>$time]);
    }

    /*
     * Clean up job and delete pod from the system
     */
    public function actionCleanUp($name,$jobid,$status)
    {
        $model=new Software;

        // $results=$model::cleanUp($name,$jobid,$status);

    }

    /*
     * Upload new software image
     */
    public function actionUpload()
    {
        $model = new WorkflowUpload();
        // print_r($model);
        // exit(0);

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
        if ($model->load(Yii::$app->request->post())) 
        {
            /*
             * Get the files and fields
             */
            $model->workflowFile=UploadedFile::getInstance($model, 'workflowFile');
            
            // print_r($model->workflowFile->extension);
            // exit(0);
            $model->dois=implode('|',$dois);
            /*
             * Upload image
             */

            if ($model->validate())
            {

                $messages=$model->upload();
                
                
                $success=$messages[1];
                $error=$messages[0];
                $warning=$messages[2];
                if($success)
                {
                    Yii::$app->session->setFlash('success', "$success");
                }
                elseif($error)
                {
                    Yii::$app->session->setFlash('danger', "$error");
                }
                else
                {
                    Yii::$app->session->setFlash('warning', "$warning");
                }
                
                return $this->redirect(['/workflow/index']);

            }
                

        }

        /*
         * Render the form
         */
        return $this->render('upload', [
                    'model' => $model,'dropdown'=> $dropdown, 'dois'=>$dois, 'command_drop'=>$command_drop,
        ]);
    }

    /* 
     * Action to render the software name
     * and/or upload a CLW file (if not already done)
     */
    public function actionEditWorkflow($name, $version)
    {
        $username=User::getCurrentUser()['username'];
        $superadmin=(User::hasRole("Admin", $superAdminAllowed = true)) ? 1 : 0;

        if ($superadmin)
        {
            $model=Workflow::find()->where(['name'=>$name,'version'=>$version])->one();
        }
        else
        {
            $model=Workflow::find()->where(['name'=>$name,'version'=>$version,'uploaded_by'=>$username])->one();
        }
        
        if (empty($model))
        {
            return $this->render('anauthorized_edit',['name'=>$name,'version'=>$version]); 
        }
        
        
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
            
            
            $messages=$model->save(false);
            $success="Workflow $name v.$version successfully updated!";

            if($success)
            {
                Yii::$app->session->setFlash('success', "$success");
            }
            elseif($error)
            {
                Yii::$app->session->setFlash('danger', "$error");
            }
            else
            {
                Yii::$app->session->setFlash('warning', "$warning");
            }
            
            return $this->redirect(['/workflow/index']);


        }

        return $this->render('edit',['model'=>$model,'vdropdown'=>$vdropdown]);

        

        
    }


    public function actionRemoveWorkflow($name, $version)
    {
        
        $user=User::getCurrentUser()['username'];
        $superadmin=(User::hasRole("Admin", $superAdminAllowed = true)) ? 1 : 0;

        $query=Workflow::find()->where(['name'=>$name,'version'=>$version]);
        if (!$superadmin)
        {
            $query->andWhere(['uploaded_by'=>$user,]);
        }

        $workflow=$query->one();
        // print_r(empty($image)?0:1);
        // exit(0);

        if (empty($workflow))
        {
            $success='';
            $error="Workflow $name v. $version does not exist or you are not allowed to delete it.";

            if($success)
            {
                Yii::$app->session->setFlash('success', "$success");
            }
            elseif($error)
            {
                Yii::$app->session->setFlash('danger', "$error");
            }
            else
            {
                Yii::$app->session->setFlash('warning', "$warning");
            }
            
            return $this->redirect(['/workflow/index']);
            
        }

        $og_folder=Yii::$app->params['workflowsFolder'] . '/' . $workflow->name . '/' . $workflow->version;
        
        /*
         * Use the timestamp and create a folder in order to store archived workflows by chronological order.
         */
        $timestamp=Yii::$app->formatter->asTimestamp(date('Y-d-m h:i:s'));
        $arch_folder=Yii::$app->params['archivedWorkflowsFolder'] . '/' . $workflow->name . '/' . $timestamp;

        if (!is_dir($arch_folder))
        {
            $command="mkdir -p $arch_folder";
            exec($command,$out,$ret);

            $command="chmod 777 $arch_folder";
            exec($command,$out,$ret);
        }


        $command="mv $og_folder $arch_folder";
        exec($command,$out,$ret);
        

        WorkflowInput::deleteAll(['workflow_id'=>$workflow->id]);

        
        $workflow->delete();
        $success="Workflow $name v. $version deleted successfully.";

        Yii::$app->session->setFlash('success', "$success");
        return $this->redirect(['/workflow/index']);
        
    }

    public function actionSelectOutput()
    {
        // $model=new Software;
        $directory=Yii::$app->params['userDataPath'] . explode('@',User::getCurrentUser()['username'])[0];

        $folders=Workflow::listDirectories($directory);
        // print_r($directory);
        // exit(0);
        
        return $this->renderAjax('folder_list_output',['folders'=>$folders]);
    }

    public function actionSelectFile($caller)
    {
        $directory=Yii::$app->params['userDataPath'] . explode('@',User::getCurrentUser()['username'])[0];

        $files=Workflow::listFiles($directory);
        // foreach ($files as $directory=>$items)
        // {
        //     print_r($directory . '<br />');
        //     print_r($items);
        //     print_r('<br />' . '<br />');
        // }
        // exit(0);
        
        return $this->renderAjax('file_list',['files'=>$files, 'caller'=>$caller]);
    }

    public function actionSelectFolder($caller)
    {
        $directory=Yii::$app->params['userDataPath'] . explode('@',User::getCurrentUser()['username'])[0] . '/';
        // print_r($directory);
        // exit(0);
        $folders=Workflow::listDirectories($directory);
        // foreach ($files as $directory=>$items)
        // {
        //     print_r($directory . '<br />');
        //     print_r($items);
        //     print_r('<br />' . '<br />');
        // }
        // exit(0);
        
        return $this->renderAjax('folder_list',['folders'=>$folders, 'caller'=>$caller,'root'=>$directory]);
    }

    public function actionSelectFileMultiple($caller)
    {
        $directory=Yii::$app->params['userDataPath'] . explode('@',User::getCurrentUser()['username'])[0];

        $files=Workflow::listFiles($directory);
        // foreach ($files as $directory=>$items)
        // {
        //     print_r($directory . '<br />');
        //     print_r($items);
        //     print_r('<br />' . '<br />');
        // }
        // exit(0);
        
        return $this->renderAjax('file_list_multiple',['files'=>$files, 'caller'=>$caller]);
    }

    public function actionSelectFolderMultiple($caller)
    {
        $directory=Yii::$app->params['userDataPath'] . explode('@',User::getCurrentUser()['username'])[0] . '/';
        // print_r($directory);
        // exit(0);
        $folders=Workflow::listDirectories($directory);
        // foreach ($files as $directory=>$items)
        // {
        //     print_r($directory . '<br />');
        //     print_r($items);
        //     print_r('<br />' . '<br />');
        // }
        // exit(0);
        
        return $this->renderAjax('folder_list_multiple',['folders'=>$folders, 'caller'=>$caller,'root'=>$directory]);
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

        $results=Software::getUserHistory($user);
        $available=Software::getAvailableSoftware();

        $pages=$results[0];
        $results=$results[1];
        
        return $this->render('history',['results'=>$results,'pages'=>$pages,'available'=>$available]);
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

        $workflow=Workflow::find()->where(['name'=>$name,'version'=>$version])->one();
        $workflow_instructions=$workflow->instructions;
        $visualize=$workflow->visualize;

        $fields=WorkflowInput::find()->where(['workflow_id'=>$workflow->id])->orderBy(['position'=> SORT_ASC])->all();
        /*
         * fill the values for the fields and get back that object
         */
        $fields=Workflow::getRerunFieldValues($jobid,$fields);            



        // $mountExistError=false;
        if (!empty($outFolder))
        {
            $folder=Yii::$app->params['userDataPath'] . explode('@',User::getCurrentUser()['username'])[0] . '/' .$outFolder;
            if (!is_dir($folder))
            {
                // $mountExistError=true;
                $command="mkdir -p $folder";
                exec($command,$ret,$out);
                $command="chmod 777 $folder";
                exec($command,$ret,$out);
            }
        }
        
        // print_r($outFolder);
        // exit(0);

        /* 
         * Add parameters for the active form
         */
        $form_params =
        [
            'action' => URL::to(['workflow/run','name'=>$name, 'version'=>$version, 'project'=>$project]),
            'options' => 
            [
                'class' => 'workflow_arguments_form',
                'id'=> "workflow_arguments_form"
            ],
            'method' => 'POST'
        ];
        
        
        $hasExample=$workflow->has_example;
        $username=User::getCurrentUser()['username'];
        $superadmin=(User::hasRole("Admin", $superAdminAllowed = true)) ? 1 : 0;
        $quotas=Software::getOndemandProjectQuotas($username,$project);
        $uploadedBy=$workflow->uploaded_by;

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

        $type=3;

        return $this->render('run', ['form_params'=>$form_params, 'name'=>$name, 
            'version'=>$version,  'jobid'=>'', 
            'errors'=>'', 'runErrors'=>'','fields'=>$fields,
            'example' => '0', 'hasExample'=>$hasExample,
            'username'=>$username,'superadmin'=>$superadmin,'uploadedBy'=>$uploadedBy,'jobUsage'=>$jobUsage,'quotas'=>$quotas,
            'maxMem'=>$maxMem, 'maxCores'=>$maxCores, 'project'=>$project,'outFolder' => $outFolder, 'workflow_instructions'=>$workflow_instructions, 'type'=>$type, 'visualize'=>$visualize]);
    }

    public function actionReattach($jobid)
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

        $workflow=Workflow::find()->where(['name'=>$name,'version'=>$version])->one();
        $workflow_instructions=$workflow->instructions;
        $visualize=$workflow->visualize;

        $fields=WorkflowInput::find()->where(['workflow_id'=>$workflow->id])->orderBy(['position'=> SORT_ASC])->all();
        /*
         * fill the values for the fields and get back that object
         */
        $fields=Workflow::getRerunFieldValues($jobid,$fields);            



        // $mountExistError=false;
        if (!empty($outFolder))
        {
            $folder=Yii::$app->params['userDataPath'] . explode('@',User::getCurrentUser()['username'])[0] . '/' .$outFolder;
            if (!is_dir($folder))
            {
                // $mountExistError=true;
                $command="mkdir -p $folder";
                exec($command,$ret,$out);
                $command="chmod 777 $folder";
                exec($command,$ret,$out);
            }
        }
        
        // print_r($outFolder);
        // exit(0);

        /* 
         * Add parameters for the active form
         */
        $form_params =
        [
            'action' => URL::to(['workflow/run','name'=>$name, 'version'=>$version, 'project'=>$project]),
            'options' => 
            [
                'class' => 'workflow_arguments_form',
                'id'=> "workflow_arguments_form"
            ],
            'method' => 'POST'
        ];
        
        
        $hasExample=$workflow->has_example;
        $username=User::getCurrentUser()['username'];
        $superadmin=(User::hasRole("Admin", $superAdminAllowed = true)) ? 1 : 0;
        $quotas=Software::getOndemandProjectQuotas($username,$project);
        $uploadedBy=$workflow->uploaded_by;

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

        $type=3;

        return $this->render('run', ['form_params'=>$form_params, 'name'=>$name, 
            'version'=>$version,  'jobid'=>$jobid, 
            'errors'=>'', 'runErrors'=>'','fields'=>$fields,
            'example' => '0', 'hasExample'=>$hasExample,
            'username'=>$username,'superadmin'=>$superadmin,'uploadedBy'=>$uploadedBy,'jobUsage'=>$jobUsage,'quotas'=>$quotas,
            'maxMem'=>$maxMem, 'maxCores'=>$maxCores, 'project'=>$project,'outFolder' => $outFolder, 'workflow_instructions'=>$workflow_instructions, 'type'=>$type, 'visualize'=>$visualize]);


    }


    public function actionDownloadFiles($name,$version)
    {
        $workflow=Workflow::find()->where(['name'=>$name, 'version'=>$version])->one();
        $filepath=$workflow->original_file;
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
            $workflow=Workflow::find()->where(['name'=>$name,'version'=>$version])->one();
        }
        else
        {
            $workflow=Workflow::find()->where(['name'=>$name,'version'=>$version,'uploaded_by'=>$username])->one();
        }
        
        if (empty($workflow))
        {
            return $this->render('anauthorized_example_add',['name'=>$name,'version'=>$version]);
        }


        $fields=WorkflowInput::find()->where(['workflow_id'=>$workflow->id])->orderBy(['position'=> SORT_ASC])->all();
        foreach ($fields as $index=>$field)
        {
            if ($fields[$index]->field_type=='enum')
            {
                $tmp_array=explode('|',$fields[$index]->enum_fields);
                $fields[$index]->dropdownValues=[];
                foreach ($tmp_array as $item)
                {
                    $fields[$index]->dropdownValues[$item]=$item;
                }
                $fields[$index]->dropdownSelected=$fields[$index]->default_value;
            }
        }

        $valueErrors=[];
        if (Yii::$app->request->getIsPost())
        {
            // print_r($_FILES);
            // exit(0);
            $folder=Yii::$app->params['userDataPath'] . '/workflow_examples/' . $name . '/' . $version . '/input/';
            $fieldPath='workflow_examples/' . $name . '/' . $version . '/input/';
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
                            $field->example=$fieldPath . $fileName;
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
                            $field->example=$fieldPath . $fileName;
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
            $workflow->has_example=true;
            $workflow->save(false);
            
            if (!$errorsExist)
            {

                $success="Example for $name v.$version successfully added!";
                $userFolder=Yii::$app->params['userDataPath'] . explode('@',User::getCurrentUser()['username'])[0];
                $user=User::getCurrentUser()['username'];

                if (!is_dir($userFolder))
                {
                    exec("mkdir $userFolder");
                    exec("chmod 777 $userFolder");
                }


                if($success)
                {
                    Yii::$app->session->setFlash('success', "$success");
                }
                elseif($error)
                {
                    Yii::$app->session->setFlash('danger', "$error");
                }
                else
                {
                    Yii::$app->session->setFlash('warning', "$warning");
                }
                
                return $this->redirect(['/workflow/index']);
                
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
