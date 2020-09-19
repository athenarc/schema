<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "image_request".
 *
 * @property int $id
 * @property string $details
 * @property string $user_name
 * @property string $date
 * @property string $dock_link
 */
class ImageRequest extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'image_request';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [[['details', 'dock_link'], 'required'],
            [['details', 'user_name'], 'string'],
            [['date'], 'safe'],
            [['dock_link'], 'string', 'max' => 200],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'details' => 'Usage details',
            'user_name' => 'User name',
            'date' => 'Date',
            'dock_link' => 'Dockhub link',
        ];
    }
}
