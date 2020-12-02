<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "upload_dataset".
 *
 * @property int $id
 * @property string $dataset_id
 * @property string $provider
 * @property int $user_id
 * @property string $api_key
 */
class UploadDataset extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'upload_dataset';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['dataset_id', 'provider', 'api_key'], 'string'],
            [['user_id'], 'default', 'value' => null],
            [['user_id'], 'integer'],
            ['date','safe']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'dataset_id' => 'Dataset ID',
            'provider' => 'Provider',
            'user_id' => 'User ID',
            'api_key' => 'Api Key',
        ];
    }
}
