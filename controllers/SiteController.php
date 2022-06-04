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
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\User;
use app\models\Notification;
use app\models\Software;
use app\models\SoftwareUpload;
use app\models\SoftwareUploadExisting;
use app\models\SoftwareEdit;
use app\models\SoftwareRemove;
use app\models\ImageRequest;
use yii\helpers\Url;
use app\models\Page;
use app\models\SystemConfiguration;
use webvimark\modules\UserManagement\models\User as Userw;
use app\models\RunHistory;
use app\models\SoftwareInput;


class SiteController extends Controller
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
        $config=SystemConfiguration::find()->one();
        $id=$config->home_page;
        $page=Page::find()->where(['id'=>$id])->one();
        
        return $this->render('index',['page'=>$page]);
    }

    public function actionUnderConstruction()
    {
        return $this->render('under_construction');
    }
    

    public function actionAuthConfirmed($token)
    {
    
            
        if (empty($token))
        {
            return $this->render('login_error');
        }
        else
        {
            $query=new \yii\db\Query;

            $sql=$query->select('*')->from('auth_user')->where(['token'=>$token])->createCommand()->getRawSql();

            $result=Yii::$app->db2->createCommand($sql)->queryOne();
            
            $username=$result['username'];
            $persistent_id=$result['persistent_id'];

            /*
             * Auth server changed, so persistent id changed.
             * In order not to break database of users already existing
             * the search is performed by username instead of persistent id.
             * Someone didn't think that far ahead it seems. :)
             */
            $identityP=User::findByPersistentId($persistent_id);
            $identityU=User::findByUsername($username);
            $identity='';
            
            if (empty($identityU) && empty($identityP))
            {
                /*
                 * If user doesn't exist
                 */
                User::createNewUser($username, $persistent_id);
                $identity=User::findByUsername($username);
                $message="A new user with username $username has been created";
                EmailEventsAdmin::NotifyByEmail('user_creation', -1,$message);
            }
            else if ((!empty($identityU)) && empty($identityP))
            {
                /*
                 * If auth server and persistent ID changed
                 */
                $identityU->password_hash=$persistent_id;
                $identityU->save();
                $identity=$identityU;
            }
            else if ((!empty($identityP)) && empty($identityU))
            {
                /*
                 * If user was renamed
                 */
                $identityP->username=$username;
                $identityP->save();
                $identity=$identityP;
            }
            else
            {
                /*
                 * If user was not altered in any way.
                 *
                 * Any other case in here means that there was an error
                 * because both the username and the persistent id 
                 * exist and point to different users (not really expected
                 * but just to be safe). 
                 */
                if ($identityP==$identityU)
                {
                    $identity=$identityU;
                }

                $userFolder=Yii::$app->params['userDataPath'] . $username=explode('@',$identity->username)[0];;

                if(Yii::$app->params['ftpLocal'])
                {
                    if (!is_dir($userFolder))
                    {
                        Software::exec_log("mkdir $userFolder");
                        Software::exec_log("chmod 777 $userFolder");
                    }
                }
                else
                {
                    $conn_id = ftp_connect(Yii::$app->params['ftpIp']);
                    $login_result = ftp_login($conn_id,
                                            Yii::$app->params['ftpUser'],
                                            Yii::$app->params['ftpPass']);

                    if (!$login_result) {
                        error_log(sprintf("Login to %s failed", Yii::$app->params['ftpIp']));
                    }
                    ftp_pasv($conn_id,true);

                    $folder=explode('@',$identity->username)[0];
                    if (!@ftp_chdir($conn_id, $folder))
                    {

                        if (!@ftp_mkdir($conn_id, $folder))
                        {
                            error_log("ERROR while creating folder $folder");
                        }
                    }
                }

            }
            
            if (empty($identity))
            {
                Yii::$app->session->setFlash('danger', 'There was an error with your login. Please contact an administrator');
                return $this->redirect(['site/index']);
            }
            else
            {
                Yii::$app->user->login($identity,0);

                return $this->goHome();
            }
            
        }
        

    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');

    }

   
    public function actionNotificationRedirect($id)
    {
        $notification=Notification::find()->where(['id'=>$id])->one();

        $notification->markAsSeen();

        return $this->redirect($notification->url);


    }

    public function actionMarkAllNotificationsSeen()
    {
        Notification::markAllAsSeen();
    }

    public function actionNotificationHistory()
    {
        $typeClass=[Notification::DANGER=>'notification-danger', Notification::NORMAL=>'', 
                    Notification::WARNING=>'notification-warning', Notification::SUCCESS=>'notification-success'];
        $results=Notification::getNotificationHistory();
        $pages=$results[0];
        $notifications=$results[1];


        return $this->render('notification_history',['notifications'=>$notifications,'pages'=>$pages,'typeClass'=>$typeClass,]);
    }

    public function actionCwlTutorial()
    {
        return $this->render('cwl_tutorial');
    }

    public function actionChangeProjectSession($project, $jobs)
    {
        $_SESSION['selected_project']=$project;
        $_SESSION['remaining_jobs']=$jobs;
    }

    public function actionHelp()
    {
        $config=SystemConfiguration::find()->one();
        $id=$config->help_page;
        $page=Page::find()->where(['id'=>$id])->one();
        
        return $this->render('help',['page'=>$page]);
    }

}
