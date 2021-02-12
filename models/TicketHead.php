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

use \app\models;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use webvimark\modules\UserManagement\models\User as Userw;

/**
 * This is the model class for table "ticket_head".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $department
 * @property string $topic
 * @property integer $status
 * @property string $date_update
 */
class TicketHead extends \yii\db\ActiveRecord
{

    public $user = false;

    /** @var  Module */
    // private $module;

    /**
     * Статусы тикетов
     */
    const OPEN = 0;
    const WAIT = 1;
    const ANSWER = 2;
    const CLOSED = 3;
    const VIEWED = 4;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ticket_head}}';
    }

    /**
     * @inheritdoc
     */
    // public function init()
    // {
    //     // $this->module = new TicketConfig;
    //     parent::init();
    // }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'topic'], 'required'],
            [['user_id', 'status'], 'integer'],
            [['date_update'], 'safe'],
            [['department', 'topic'], 'string', 'max' => 255],
            [['department', 'topic'], 'filter', 'filter' => 'strip_tags'],
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'date_update',
                'updatedAtAttribute' => 'date_update',
                'value' => new Expression('NOW()'),
            ],
        ];
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'          => 'ID',
            'user_id'     => 'User ID',
            'department'  => 'Department',
            'topic'       => 'Subject',
            'status'      => 'Status',
            'date_update' => 'Last updated',
        ];
    }

    /**
     * dataProvider для пользователей
     *
     * @return ActiveDataProvider
     */
    public function dataProviderUser()
    {
        $query = TicketHead::find()->where("user_id = " . Yii::$app->user->id);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'date_update' => SORT_DESC
                ]
            ],
            'pagination' => [
                'pageSize' => TicketConfig::pageSize
            ]
        ]);

        return $dataProvider;
    }

    /**
     * dataProvider для админ панели
     *
     * @return ActiveDataProvider
     */
    public function dataProviderAdmin()
    {
        $query = TicketHead::find()->joinWith('userName');
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'date_update' => SORT_DESC
                ]
            ],
            'pagination' => [
                'pageSize' => TicketConfig::pageSize
            ]
        ]);

        return $dataProvider;
    }

    public function getUserName()
    {
        $userModel = new Userw;
        return $this->hasOne($userModel, ['id' => 'user_id']);
    }

    public function getBody()
    {
        return $this->hasOne(TicketBody::className(), ['id_head' => 'id'])->orderBy('date DESC');
    }

    /**
     * @return int|string Возвращает количество новых тикетов статус которых OPEN или WAIT
     */
    public static function getNewTicketCount()
    {
        return TicketHead::find()->where('status = 0 OR status = 1')->count();
    }

    /**
     * Возвращает количество тикетов в по статусам
     *
     * @param int $status int Статус тикета
     * @return int|string
     */
    public static function getNewTicketCountUser($status = 0)
    {
        return TicketHead::find()->where("status = $status AND user_id = " . Yii::$app->user->id . " ")->count();
    }

    /**
     * Если это новый тикет записываем id пользователя который его создал
     *
     * @return bool
     */
    public function beforeValidate()
    {
        if ($this->isNewRecord) {
            $this->user_id = ($this->user === false) ? Yii::$app->user->id : $this->user;
        }
        return parent::beforeValidate(); // TODO: Change the autogenerated stub
    }

    /**
     * @return bool
     */
    public function beforeDelete()
    {
        $files = TicketFile::find()
            ->joinWith('idBody', false)
            ->where(['id_head' => $this->id])
            ->all();
        foreach($files as $file) {
            @unlink(Yii::getAlias(TicketConfig::uploadFilesDirectory).'/'.$file->fileName);
            @unlink(Yii::getAlias(TicketConfig::uploadFilesDirectory).'/reduced/'.$file->fileName);
        }

        return parent::beforeDelete();
    }

}
