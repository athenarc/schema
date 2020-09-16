<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "covid_dataset_application".
 *
 * @property int $id
 * @property string $email
 * @property string $link
 * @property string $description
 */
class CovidDatasetApplication extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'covid_dataset_application';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['link', 'description','name'], 'string'],
            [['email','username'], 'string', 'max' => 200],
            ['status','integer'],
            [['email'],'email'],
            [['email','link','description','name'],'required'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'email' => 'Correspondance e-mail',
            'link' => 'Link to the dataset',
            'description' => 'A few words about the dataset',
            'name' => 'Name of the dataset',
        ];
    }
}
