<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\ImageRequest;
use app\models\User;
use yii\data\Pagination;
use app\models\Notification;
use app\models\UploadDatasetDefaults;
use app\models\SystemConfiguration;


class AdministrationController extends Controller
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
        

        return $this->render('system_configuration', ['configuration'=>$configuration]);
        
    }



}
