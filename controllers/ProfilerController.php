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
use app\models\Software;
use app\models\SoftwareInput;
use app\models\SoftwareProfiler;
use webvimark\modules\UserManagement\models\User;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;



class ProfilerController extends \yii\web\Controller
{
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
    public function actionAnalyze()
    {
        return $this->render('analyze');
    }

    // public function actionIndex()
    // {
    //     return $this->render('index');
    // }

    public function actionProvideInputs($name,$version)
    {
        $user=User::getCurrentUser()['username'];
        $superadmin=(User::hasRole("Admin", $superAdminAllowed = true)) ? 1 : 0;
        $errors=[];

        $query=Software::find()->where(['name'=>$name,'version'=>$version]);
        if (!$superadmin)
        {
            $query->andWhere(['uploaded_by'=>$user,]);
        }

        $software=$query->one();

        $fields=SoftwareInput::find()->where(['softwareid'=>$software->id])->orderBy(['position'=> SORT_ASC])->all();
        $required_icon="<span class='required-icon'>*</span>";
        /*
         * If the form has posted load the field values.
         * This was changed because it didn't work with checkboxes.
         */
        
        if (Yii::$app->request->getIsPost())
        {
            /*
             * Read field values
             * and assign them to each field
             */
            $fieldsNum=(!empty($fields)) ? count($fields) : 0;
            $field_values=[];
            $included=[];
            for ($i=0; $i<$fieldsNum; $i++)
            {
                $field_values[]=Yii::$app->request->post('field-' . $i);
                /*
                 * If user has selected to include field in classification
                 * add it to the profiler configuration
                 */
                if (Yii::$app->request->post('include-' . $i,false))
                {
                    $included[]=$i;
                }
            }
            if (!empty($fields))
            {
                $field_count=count($fields);
            }
            else
            {
                $field_count=0;
            }

            $emptyFields=true;
            
            for ($index=0; $index<$field_count; $index++)
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

            $username=explode('@',$user)[0];
            $systemMount=Yii::$app->params['userDataPath'] . '/' . $username . '/' . Yii::$app->request->post('systemmount');

            $totalErrors=SoftwareProfiler::createAnalysis($software,$fields,$systemMount,$included);
            $errors=$totalErrors[0];
            $runErrors=$totalErrors[1];
            // print_r($errors);
            exit(0);
            if (empty($errors))
            {
                if (empty($runErrors))
                {
                    $message="Profiling started successfully";
                    $type='success';
                }
                else
                {
                    $message="There was an error running the profiling. Please contact an administrator.";
                    $type='danger';
                }
                
                Yii::$app->session->setFlash($type, $message);
                return $this->redirect(['software/index']);
            }

        }


        return $this->render('provide-inputs',['software'=>$software,'fields'=>$fields,'username'=>$user,'name'=>$name,'version'=>$version,'errors'=>$errors]);
    }

}
