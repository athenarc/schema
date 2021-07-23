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
    	
    	 // $session = Yii::$app->session;
   		 // $session->set('user_id', '1234');
   		 // print_r($session['new_session']);
   		 // exit(0);
    	
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

    public function actionIsProfiled($name,$version)
    {
        $profiled_value=Software::find()->select('profiled')->where(['name'=>$name])->andWhere(['version'=>$version])->scalar();
        return $profiled_value;
        
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
        $software_id=$software->id;
        $software_instructions=$software->instructions;
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
               
            }
        }

        
        $mountpointExistError=false;
        
        
        /*
         * Check if i/o folders exist. If not, create them. Depends on whether there is one mountpoint for the 
         */
        
        if (!empty($iocontMount))
        {
            if (!is_dir($iosystemMount))
            {
                Software::exec_log("mkdir -p $iosystemMount");
                Software::exec_log("chmod 777 $iosystemMount");
            }
            else
            {
                Software::exec_log("chmod 777 $iosystemMount -R");
            }

        }
        else
        {
            if (!empty($icontMount))
            {
                if (!is_dir($isystemMount))
                {
                    Software::exec_log("mkdir -p $isystemMount");
                    Software::exec_log("chmod 777 $isystemMount");
                }
                else
                {
                    Software::exec_log("chmod 777 $isystemMount -R");
                }
            }
            if (!empty($ocontMount))
            {
                // print_r($osystemMount);
                // exit(0);
                if (!is_dir($osystemMount))
                {
                    Software::exec_log("mkdir -p $osystemMount");
                    Software::exec_log("chmod 777 $osystemMount");
                }
                else
                {
                    Software::exec_log("chmod 777 $osystemMount -R");
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
            Software::exec_log("cp -r $exampleFolder/* $folder");
            Software::exec_log("chmod 777 $folder");
            
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
        // print_r($quotas);
        // exit(0);

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


        if (!is_dir($userFolder))
        {
            Software::exec_log("mkdir $userFolder");
            Software::exec_log("chmod 777 $userFolder");
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

        
        $type=1;

        return $this->render('run', ['form_params'=>$form_params, 'name'=>$name, 
            'version'=>$version,  'jobid'=>$jobid, 'software_id'=>$software_id, 'software_instructions'=>$software_instructions,
            'errors'=>$errors, 'runErrors'=>$runError, 'podid'=>$podid, 'machineType'=>$machineType,
            'fields'=>$fields,'isystemMount' => $isystemMountField, 'osystemMount' => $osystemMountField,
            'iosystemMount' => $iosystemMountField, 'example' => '0', 'hasExample'=>$hasExample,
            'username'=>$user,'icontMount'=>$icontMount,'ocontMount'=>$ocontMount,
            'iocontMount'=>$iocontMount,'mountExistError'=>false,
            'superadmin'=>$superadmin,'uploadedBy'=>$uploadedBy,'jobUsage'=>$jobUsage,'quotas'=>$quotas,
            'maxMem'=>$maxMem, 'maxCores'=>$maxCores, 'project'=>$project, 'type'=>$type]);
    }

    /*
     * Get logs from the running pod to show on the webpage
     */
    public function actionGetLogs($podid,$machineType,$jobid)
    {

        $results=Software::getLogs($podid);
        $logs=$results[1];
        $status=$results[0];
        $time=$results[2];
        $history=RunHistory::find()->where(['jobid'=>$jobid])->one();

        

        return $this->renderPartial('logs',['logs'=>$logs, 'status'=>$status, 'machineType'=>$machineType,'time'=>$time,'project'=>$history->project]);
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

            $software=$model::getSoftwareNames($softUser);
            $descriptions=Software::getSoftwareDescriptions($softUser);
            $images=Software::getOriginalImages($softUser);
            $indicators=Software::getIndicators($softUser);;

            if(!empty($messages[1]))
            {
                Yii::$app->session->setFlash('success', "$messages[1]");
            }
            if(!empty($messages[2]))
            {
                Yii::$app->session->setFlash('danger', "$messages[2]");   
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
            // $model->imageFile=UploadedFile::getInstance($model, 'imageFile');
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

            $software=$model::getSoftwareNames($softUser);
            $descriptions=Software::getSoftwareDescriptions($softUser);
            $images=Software::getOriginalImages($softUser);
            $indicators=Software::getIndicators($softUser);

            if(!empty($messages[1]))
            {
                Yii::$app->session->setFlash('success', "$messages[1]");
            }
            if(!empty($messages[2]))
            {
                Yii::$app->session->setFlash('danger', "$messages[0]");   
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
            $success="Software $name v.$version successfully updated!";

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
            

            $software=Software::getSoftwareNames($softUser);
            $descriptions=Software::getSoftwareDescriptions($softUser);
            $images=Software::getOriginalImages($softUser);
            $indicators=Software::getIndicators($softUser);;

            if(!empty($messages[1]))
            {
                Yii::$app->session->setFlash('success', "$messages[1]");
            }
            if(!empty($messages[2]))
            {
                Yii::$app->session->setFlash('danger', "$messages[0]");   
            }    
            if(!empty($messages[0]))
            {
                Yii::$app->session->setFlash('danger', "$messages[0]");
            }
            return $this->redirect(['software/index']);
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


        // $inactiveJobs=Software::getInactiveJobs();
        // print_r($inactiveJobs);
        // exit(0);
        
        // foreach ($inactiveJobs as $job)
        // {
        //     Software::cleanUp($job[0],$job[1],'Complete');

        // }
        $available=Software::getAvailableSoftware();

        $available_workflows=Workflow::getAvailableWorkflows();
        // $results=Software::getUserHistory($user);
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
        $software_instructions=$software->instructions;
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
        $type=1;

        return $this->render('run', ['form_params'=>$form_params, 'name'=>$name, 
            'version'=>$version,  'jobid'=>'',
            'errors'=>'', 'runErrors'=>'', 'podid'=>'', 'machineType'=>'',
            'fields'=>$fields,'isystemMount' => $isystemMountField, 'osystemMount' => $osystemMountField,
            'iosystemMount' => $iosystemMountField, 'example' => '0', 
            'hasExample'=>$hasExample, 'superadmin'=>$superadmin,
            'username'=>$username,'icontMount'=>$icontMount,'ocontMount'=>$ocontMount,
            'iocontMount'=>$iocontMount,'mountExistError'=>$mountExistError,
            'jobUsage'=>$jobUsage,'quotas'=>$quotas,
            'maxMem'=>$maxMem, 'maxCores'=>$maxCores, 'project'=>$project, 'software_instructions'=>$software_instructions, 'type'=>$type, 'uploadedBy'=>$uploadedBy]);
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
        $software=Software::find()->where(['name'=>$name])->andWhere(['version'=>$version])->one();
        $software_instructions=$software->instructions;
        $uploadedBy=$software->uploaded_by;
        

        $podid=Software::runningPodIdByJob($name,$jobid);
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

        $type=1;
        
        return $this->render('run', ['form_params'=>$form_params, 'name'=>$name, 
            'version'=>$version,  'jobid'=>$jobid,
            'errors'=>'', 'runErrors'=>'', 'podid'=>$podid, 'machineType'=>$machineType,
            'fields'=>$fields,'isystemMount' => $isystemMountField, 'osystemMount' => $osystemMountField,
            'iosystemMount' => $iosystemMountField, 'example' => '0', 
            'hasExample'=>$hasExample, 'superadmin'=>$superadmin,
            'username'=>$username,'icontMount'=>$icontMount,'ocontMount'=>$ocontMount,'iocontMount'=>$iocontMount,
            'mountExistError'=>false,
            'jobUsage'=>$jobUsage,'quotas'=>$quotas,
            'maxMem'=>$maxMem, 'maxCores'=>$maxCores, 'project'=>$project, 'software_instructions'=>$software_instructions,'type'=>$type, 'uploadedBy'=>$uploadedBy]);


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

    public function actionRoCrateHistory()
    {
        $username=User::getCurrentUser()['username'];
        $query=RoCrate::find()->orderBy(['date'=>SORT_DESC]);
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count]);
        $ro_crates = $query->offset($pagination->offset)
                ->limit($pagination->limit)
                ->all();
        $results_user=[];
        $results_public=[];
        
        foreach ($ro_crates as $ro_crate) 
        {   
            
            if($ro_crate->username==$username)
            {
                $software_run=RunHistory::find()->where(['jobid'=>$ro_crate->jobid])->one();
                if (empty($software_run))
                {
                    continue;
                }

                $results_user[$ro_crate->jobid]=['softname'=>$software_run->softname, 'start'=>$software_run->start, 'stop'=>$software_run->stop, 'username'=>$ro_crate->username,
                'date'=>$ro_crate->date, 'experiment_description'=>$ro_crate->experiment_description, 'public'=>$ro_crate->public, 'link'=>['software/download-rocrate', 'jobid'=>$ro_crate->jobid]];
            }
            elseif ($ro_crate->public==true) 
            {
                $software_run=RunHistory::find()->where(['jobid'=>$ro_crate->jobid])->one();
                if (empty($software_run))
                {
                    continue;
                }

                $results_public[$ro_crate->jobid]=['softname'=>$software_run->softname, 'start'=>$software_run->start, 'stop'=>$software_run->stop, 'username'=>$ro_crate->username,
                'date'=>$ro_crate->date, 'experiment_description'=>$ro_crate->experiment_description, 'public'=>$ro_crate->public, 'link'=>['software/download-rocrate', 'jobid'=>$ro_crate->jobid]];
            }  
        }
        
        return $this->render('ro_crate_history', ['results_user'=>$results_user, 'results_public'=>$results_public, 'pagination'=>$pagination]);
    }



}