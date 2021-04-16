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

namespace app\controllers;

use Yii;
use yii\helpers\Url;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\ImageRequest;
use app\models\User;
use yii\data\Pagination;
use app\models\Page;
use app\models\Notification;
use app\models\UploadDatasetDefaults;
use app\models\SystemConfiguration;


class AdministrationController extends Controller
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
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionDockerhubImageList()
    {
        $query=ImageRequest::find();

        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count()]);
        $requests = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->all();

        return $this->render('dockerhub_image_list', 
            [
                'requests' => $requests,
                'pages' => $pages,
        ]);
        
    }

    public function actionDockerhubImageDetails()
    {
        $id=$_GET['id'];
        $image=ImageRequest::find()->where(['id'=>$id])->one();
        $details=$image->details;
        return $this->render('image_details', ['details'=>$details]);
        
    }

    public function actionExternalRepositories()
    {
        
        $providers=UploadDatasetDefaults::find()->orderBy(['name'=>SORT_ASC])->all();
        $number_of_providers=sizeof($providers);
        
        
        if(Yii::$app->request->post())
        {
            foreach($providers as $i=>$provider)
            {
                if($provider->name=='Helix')
                {
                    $providers[$i]['provider_id']=$_POST['provider_id-'.$i];
                    $providers[$i]['default_community_id']=$_POST['community_id-'.$i];
                }
                $providers[$i]['enabled']=$_POST['enabled-'.$i];
                $providers[$i]->update(false);
            }
            

        }

        return $this->render('external_repositories', ['providers'=>$providers, 'number_of_providers'=>$number_of_providers]);
        
    }


     public function actionSystemConfiguration()
    {
        
        $configuration=SystemConfiguration::find()->one();
        $pages=Page::getPagesDropdown();
        $no_configuration=false;
        if (empty($configuration))
        {
            $no_configuration=true;
            $configuration=new SystemConfiguration;
        }
        
        if ($configuration->load(Yii::$app->request->post()))
        {
            if ($no_configuration)
            {
                $configuration->save(false);
            }
            else
            {
                $configuration->update(false);
            }
            Yii::$app->session->setFlash('success', 'System configuration has been succesfully saved');
            return $this->redirect(['administration/index']);
        }
        

        return $this->render('system_configuration', ['configuration'=>$configuration, 'pages'=>$pages]);
        
    }
    

    public function actionManagePages()
    {
        $pages=Page::find()->all();

        return $this->render('manage-pages',['pages'=>$pages]);

    }

    public function actionAddPage()
    {
        $model=new Page;
        $form_params =
        [
            'action' => URL::to(['administration/add-page']),
            'options' => 
            [
                'class' => 'add_page_form',
                'id'=> "add_page_form"
            ],
            'method' => 'POST'
        ];

        if ($model->load(Yii::$app->request->post()) && $model->validate())
        {
            $model->save();
            $this->redirect(['administration/manage-pages']);
        }

        return $this->render('add-page',['model'=>$model,'form_params'=>$form_params]);
        
    }
    public function actionEditPage($id)
    {
        $page=Page::find()->where(['id'=>$id])->one();

        if (empty($page))
        {
            return $this->render('error_page_exist');
        }

        $form_params =
        [
            'action' => URL::to(['administration/edit-page', 'id'=>$id]),
            'options' => 
            [
                'class' => 'edit_page_form',
                'id'=> "edit_page_form"
            ],
            'method' => 'POST'
        ];

        if ($page->load(Yii::$app->request->post()) && $page->validate())
        {
            $page->save();
            $this->redirect(['administration/manage-pages']);
        }

        return $this->render('edit-page',['page'=>$page,'form_params'=>$form_params]);
    }
    public function actionDeletePage($id)
    {
        $page=Page::find()->where(['id'=>$id])->one();

        if (empty($page))
        {
            return $this->render('error_page_exist');
        }

        $page->delete();
        $this->redirect(['administration/manage-pages']);

        
    }
    public function actionViewPage($id)
    {
        $page=Page::find()->where(['id'=>$id])->one();

        if (empty($page))
        {
            return $this->render('error_page_exist');
        }

        return $this->render('view-page',['page'=>$page]);
    }



}
