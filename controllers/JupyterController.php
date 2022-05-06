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
 *  along with SCHeMa.  If not, see <https://www.gnu.org/licenses/>.
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
use yii\httpclient\Client;
use yii\web\Controller;
use app\models\Software;
use app\models\JupyterServer;
use app\models\JupyterImages;
use yii\helpers\Url;
use webvimark\modules\UserManagement\models\User;
use yii\filters\VerbFilter;


class JupyterController extends Controller
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
    public function actionIndex()
    {
        $username=explode('@',User::getCurrentUser()['username'])[0];
        $userFolder=Yii::$app->params['userDataPath'] . $username;

        if (!is_dir($userFolder))
        {
            Software::exec_log("mkdir $userFolder");
            Software::exec_log("chmod 777 $userFolder");
        }
        /*
         * This piece of code gets the quotas from CLIMA
         * when the platforms are available.
         * 
         * In standalone mode, they are retrieved from the parameters.
         */ 

        if (!Yii::$app->params['standalone'])
        {
            $projects=JupyterServer::getActiveProjects();
            $projects=JupyterServer::matchServersWithProjects($projects);
            
        }
        else
        {
            $server=JupyterServer::find()->where(['active'=>true, 'created_by'=>$username])->one();
            $projects=['default'=>['cpu'=> Yii::$app->params['standaloneResources']['maxCores'], 'memory'=>Yii::$app->params['standaloneResources']['maxRam'] ] ];
            if (!empty($server))
            {
                $projects['default']['server']=$server;
            }

        }

        $img=JupyterImages::find()->all();
        $images=[];
        foreach ($img as $i)
        {
            $description=$i->description;
            if ($i->gpu==true)
            {
                $description.=' (GPU)';
            }

            $images[$i->id]=$description;
        }

        return $this->render('index',['projects'=>$projects,'images'=>$images]);

    }

    public function actionStartServer($project)
    {

        $form_params =
        [
            'action' => URL::to(['jupyter/start-server', 'project'=>$project]),
            'options' => 
            [
                'class' => 'jupyter_start_form',
                'id'=> "jupyter_start_form"
            ],
            'method' => 'POST'
        ];

        if (!Yii::$app->params['standalone'])
        {
                /*
                 * Project does not exist. User is trying something illegal.
                 */
                $quotas=JupyterServer::getProjectQuotas($project);
    
                if (empty($quotas))
                {
                    return $this->render('project_error',['project'=>$project]);
                }
        }
        else
        {
            /*
             * Project not active in standalone mode, so there's no use searching.
             */
            $quotas=['cores'=>Yii::$app->params['standaloneResources']['maxCores'],'ram'=>Yii::$app->params['standaloneResources']['maxRam'],'end_date'=>'2250-12-31' ];
        }

        $username=User::getCurrentUser()['username'];

        /*
         * Server has already been activated. User is trying something illegal.
         */
        $server=JupyterServer::find()->where(['active'=>true,'project'=>$project, 'created_by'=>$username])->one();
        if (!empty($server))
        {
            return $this->render('server_already_active');
        }

        $images=JupyterImages::find()->orderBy('description')->all();
        $imageDrop=[];
        foreach ($images as $image)
        {
            $description=$image->description;
            if ($image->gpu)
            {
                $description.=" (GPU enabled)";
            }
            $imageDrop[$image->id]=$description;
        }

        $model = new JupyterServer;
        if ($model->load(Yii::$app->request->post()) && $model->validate()) 
        {
            $model->cpu=$quotas['cores'];
            $model->memory=$quotas['ram'];
            $model->expires_on=$quotas['end_date'];
            $model->project=$project;
            $messages=$model->startServer();
            $success=$messages[0];
            $error=$messages[1];

            if (!empty($error))
            {
                Yii::$app->session->setFlash('danger',$error);
            }

            if (!empty($success))
            {
                Yii::$app->session->setFlash('success',$success);
            }
            
            return $this->redirect(['jupyter/index']);
        }

        return $this->render('start_server',['model'=>$model, 'imageDrop'=>$imageDrop,'form_params' => $form_params]);

    }
    public function actionStopServer($project,$return='s')
    {
        if ($return=='a')
        {
            $server=JupyterServer::find()->where(['active'=>true,'project'=>$project])->one();
        }
        else
        {
            $username=User::getCurrentUser()['username'];
            $server=JupyterServer::find()->where(['active'=>true,'project'=>$project,'created_by'=>$username])->one();
        }
        

        if (empty($server))
        {
            return $this->render('server_already_stopped');
        }

        $messages=$server->stopServer();
        $success=$messages[0];
        $error=$messages[1];

        if (!empty($error))
        {
            Yii::$app->session->setFlash('danger',$error);
        }

        if (!empty($success))
        {
            Yii::$app->session->setFlash('success',$success);
        }
        if ($return=='a')
        {
            return $this->redirect(['jupyter/active-servers']);
        }
        else
        {
            return $this->redirect(['jupyter/index']);
        }

    }
    
    public function actionImageList()
    {
        if (!User::hasRole("Admin", $superAdminAllowed = true))
        {
            return $this->render('unauthorized');
        }

        $images=JupyterImages::find()->orderBy('description')->all();

        return $this->render('image_list', ['images'=>$images]);
    }

    public function actionDeleteImage($id)
    {
        if (!User::hasRole("Admin", $superAdminAllowed = true))
        {
            return $this->render('unauthorized');
        }

        $image=JupyterImages::find()->where(['id'=>$id])->one();

        if (empty($image))
        {
            return $this->render('image_not_found');
        }
        $name=$image->image;
        $image->delete();

        Yii::$app->session->setFlash('success',"Image $name deleted successufully");

        $this->redirect(['jupyter/image-list']);
    }

    public function actionNewImage()
    {
        if (!User::hasRole("Admin", $superAdminAllowed = true))
        {
            return $this->render('unauthorized');
        }

        $form_params =
        [
            'action' => URL::to(['jupyter/new-image']),
            'options' => 
            [
                'class' => 'jupyter_new_image_form',
                'id'=> "jupyter_new_image_form"
            ],
            'method' => 'POST'
        ];

        $model=new JupyterImages;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) 
        {
            $model->save();

            Yii::$app->session->setFlash('success',"Image added successufully");

            $this->redirect(['jupyter/image-list']);
        }

        return $this->render('new_image',['model'=>$model,'form_params' => $form_params]);

    }

    public function actionEditImage($id)
    {
        if (!User::hasRole("Admin", $superAdminAllowed = true))
        {
            return $this->render('unauthorized');
        }

        $model=JupyterImages::find()->where(['id'=>$id])->one();

        if (empty($model))
        {
            return $this->render('image_not_found');
        }

        $form_params =
        [
            'action' => URL::to(['jupyter/edit-image','id'=>$id]),
            'options' => 
            [
                'class' => 'jupyter_edit_image_form',
                'id'=> "jupyter_new_edit_form"
            ],
            'method' => 'POST'
        ];

        if ($model->load(Yii::$app->request->post()) && $model->validate()) 
        {
            $model->gpu=($model->gpu==1) ? true : false;
            $model->save();

            Yii::$app->session->setFlash('success',"Image $model->image saved!");

            $this->redirect(['jupyter/image-list']);
        }

        return $this->render('edit_image',['model'=>$model,'form_params' => $form_params]);

    }

    public function actionActiveServers()
    {

        if (!User::hasRole("Admin", $superAdminAllowed = true))
        {
            return $this->render('unauthorized');
        }

        $servers=JupyterServer::find()->where(['active'=>true])->all();

        return $this->render('active_servers',['servers'=>$servers]);

    }
}
