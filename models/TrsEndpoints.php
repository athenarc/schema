<?php

namespace app\models;

use Yii;
use yii\httpclient\Client;

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
 *  along with SCHeMa.  If not, see <https://www.gnu.org/licenses/>.
 *
 ************************************************************************************/

/**
 * This is the model class for table "trs_endpoints".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $url
 * @property bool|null $push_tools
 * @property bool|null $get_workflows
 */
class TrsEndpoints extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'trs_endpoints';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'url'], 'string'],
            [['push_tools', 'get_workflows'], 'boolean'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'url' => 'URL',
            'push_tools' => 'Push Tools on Endpoint',
            'get_workflows' => 'Get Workflows from Endpoint',
        ];
    }

    public static function getWorkflows()
    {
        $endpoints=TrsEndpoints::find()->where(['get_workflows'=>true])->all();
        
        $workflows=[];
        foreach ($endpoints as $endpoint)
        {
            $client = new Client();
            $response = $client->createRequest()
                    ->setMethod('GET')
                    ->setUrl($endpoint->url . '/tools')
                    ->setData(['descriptorType'=>'cwl', 'toolclass'=>'Workflow'])
                    ->send();

            $workflows=$response->data;
            foreach ($workflows as $workflow)
            {
                var_dump($workflow);
                exit(0);
            }
            exit(0);
        }
            
    }
}
