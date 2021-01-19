<?php

namespace app\models;

use Yii;
use yii\httpclient\Client;
use yii\authclient\OAuth2;
use webvimark\modules\UserManagement\models\User as Userw;
use yii\helpers\FileHelper;
use yii\db\Query;

/**
 * This is the model class for table "upload_dataset".
 *
 * @property int $id
 * @property string $dataset_id
 * @property string $provider
 * @property int $user_id
 * @property string $api_key
 */
class UploadDatasetZenodo extends \yii\db\ActiveRecord
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
        	[['provider', 'api_key'], 'required'],
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



    public function uploadZenodoDataset($dataset_path,$provider,$api_key)
    {
        $error='';
        $success='';

        $data='{"metadata": {"upload_type": "presentation" } }';

        $client = new Client(['baseUrl' => "https://zenodo.org/api/deposit/depositions"]);
        $response = $client->createRequest()
        ->setMethod('POST')
        ->setFormat(Client::FORMAT_JSON)
        ->setUrl(["access_token" => $api_key])
        ->addHeaders(['content-type' => 'application/json'])
        ->setContent('{"metadata": {
             "title": "My first upload",
             "upload_type": "poster",
             "description": "This is my first upload",
             "creators": [
             {"name": "Doe, John", "affiliation": "Zenodo"}
             ] }}')
        ->send();

        $status=$response->headers['http-code'];

        if ($status!='201')
        {
            $error=json_decode($response->content,true)['message'];
            return ['error'=>$error];
        }
        else
        {
            $content=json_decode($response->content,true);

            $success='The dataset has been successfully uploaded to Zenodo.';
            return ['success'=>$success];
        }
    }

   


}
