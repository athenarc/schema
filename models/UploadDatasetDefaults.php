<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "upload_dataset_defaults".
 *
 * @property int $id
 * @property string $provider
 * @property string $provider_id
 * @property string $default_community
 * @property string $default_community_id
 */
class UploadDatasetDefaults extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'upload_dataset_defaults';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['provider', 'provider_id', 'default_community', 'default_community_id', 'name'], 'string'],
            ['enabled', 'boolean'],
            //['enabled', 'default', 'value'=> true],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'provider' => 'Provider',
            'provider_id' => 'Provider ID',
            'default_community' => 'Default Community',
            'default_community_id' => 'Default Community ID',
        ];
    }
}
