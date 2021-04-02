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
use app\components\UserIdentity;
use yii\db\Query;
use webvimark\modules\UserManagement\models\User as Userw;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $username
 * @property string $auth_key
 * @property string $password_hash
 * @property string $confirmation_token
 * @property int $status
 * @property int $superadmin
 * @property int $created_at
 * @property int $updated_at
 * @property string $registration_ip
 * @property string $bind_to_ip
 * @property string $email
 * @property int $email_confirmed
 * @property string $name
 * @property string $surname
 *
 * @property AuthAssignment[] $authAssignments
 * @property AuthItem[] $itemNames
 * @property UserVisitLog[] $userVisitLogs
 */
class User extends UserIdentity
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const STATUS_BANNED = -1;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'auth_key', 'password_hash', 'created_at', 'updated_at'], 'required'],
            [['status', 'superadmin', 'created_at', 'updated_at', 'email_confirmed'], 'default', 'value' => null],
            [['status', 'superadmin', 'created_at', 'updated_at', 'email_confirmed'], 'integer'],
            [['username', 'password_hash', 'confirmation_token', 'bind_to_ip'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
            [['registration_ip'], 'string', 'max' => 15],
            [['email'], 'string', 'max' => 128],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'auth_key' => 'Auth Key',
            'password_hash' => 'Password Hash',
            'confirmation_token' => 'Confirmation Token',
            'status' => 'Status',
            'superadmin' => 'Superadmin',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'registration_ip' => 'Registration Ip',
            'bind_to_ip' => 'Bind To Ip',
            'email' => 'Email',
            'email_confirmed' => 'Email Confirmed',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthAssignments()
    {
        return $this->hasMany(AuthAssignment::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemNames()
    {
        return $this->hasMany(AuthItem::className(), ['name' => 'item_name'])->viaTable('auth_assignment', ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserVisitLogs()
    {
        return $this->hasMany(UserVisitLog::className(), ['user_id' => 'id']);
    }

    public static function getNamesAutoComplete($expansion, $max_num = 5, $term)
    {
        $query=new Query;

        $expansion = strtolower($expansion);
        if($expansion == "left")
        {
            $search_type = $term;
        }
        else if($expansion == 'right')
        {
            $search_type = $term;
        }
        else
        {
            $search_type = $term;
        }
        $rows=$query->select(["levenshtein(name, '$term') as dist","CONCAT(name,' ',surname) AS full_name"])
              ->from('user')
              ->where(['ilike',"name",$search_type])
              ->orWhere(['ilike',"surname",$search_type])
              ->limit($max_num)
              ->orderBy(['dist' => SORT_ASC])
              ->all();

        return json_encode(array_map("array_pop", $rows));
    }

    public static function returnIdByName($name,$surname)
    {
        $query=new Query;

        $row=$query->select(['id'])
                   ->from('user')
                   ->where(['name'=>$name, 'surname'=>$surname])
                   ->one();

        return $row['id'];
    }

    public static function returnIdByUsername($username)
    {
        $query=new Query;

        $row=$query->select(['id'])
                   ->from('user')
                   ->where(['username'=>$username])
                   ->one();

        return $row['id'];
    }

    public static function createNewUser($username, $persistent_id)
    {
        Yii::$app->db->createCommand()->insert('user', 
        [ 
            'username' => $username,
            'auth_key' => 'dummy',
            'password_hash' => $persistent_id,
            'status' => self::STATUS_ACTIVE,
            'created_at' => time(),
            'updated_at' => time(),
            'email_confirmed' => 1,
        ])->execute();

        $userId=Yii::$app->db->getLastInsertID();

        // Userw::assignRole($userId, 'PlatformUser');
        

    }
    
    public static function getAdminIds()
    {
        $results=AuthAssignment::find()->where(['item_name'=> 'Admin'])->all();

        $ids=[];
        foreach ($results as $res)
        {
            $ids[]=$res->user_id;
        }

        return $ids;
    }


}
