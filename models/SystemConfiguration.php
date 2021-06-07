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

namespace app\models;

use Yii;

/**
 * This is the model class for table "system_configuration".
 *
 * @property int $id
 * @property string $admin_email
 */
class SystemConfiguration extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'system_configuration';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['admin_email'], 'string'],
            [['admin_email'], 'email'],
            [['profiler'], 'boolean'],
            [['home_page', 'privacy_page','help_page'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'admin_email' => 'Admin Email',
            'home_page' => 'Home page',
            'help_page' => 'Help page',
            'profiler'=>'',
        ];
    }


    /**
     * {@inheritdoc}
     */
   

    public function updateDB()
    {
        Yii::$app->db->createCommand()->update('system_configuration',[
            'admin_email'=>$this->admin_email, 
            'help_page'=>$this->help_page, 
            'home_page'=>$this->home_page, 
            'profiler'=>$this->profiler, 
        ], "TRUE")->execute();
    }

}
