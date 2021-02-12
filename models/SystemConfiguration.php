<?php

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
        ];
    }
}
