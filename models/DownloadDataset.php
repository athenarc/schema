<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "dataset".
 *
 * @property int $id
 * @property string $folder_path
 * @property string $download_url
 * @property string $provider
 */
class DownloadDataset extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'download_dataset';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['folder_path', 'dataset_id', 'provider'], 'string'],
            ['user_id', 'integer'],
            ['date','safe']
          //  [['folder_path', 'download_url', 'provider'], 'required'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'folder_path' => 'Folder Path',
            'dataset_id' => 'Dataset id',
            'provider' => 'Provider',
        ];
    }
}
