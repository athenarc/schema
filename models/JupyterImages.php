<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "jupyter_images".
 *
 * @property int $id
 * @property string|null $description
 * @property string|null $image
 */
class JupyterImages extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'jupyter_images';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description', 'image'], 'string'],
            [['gpu'],'boolean'],
            [['gpu'],'required'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'description' => 'Descriptive name',
            'image' => 'Image on dockerhub',
            'gpu'   => 'Image uses GPU'
        ];
    }
}
