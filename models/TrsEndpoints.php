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
        
        $result=[];
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
                $id=$workflow['id'];
                $wname=$workflow['name'];
                $description=$workflow['description'];
                $versions=$workflow['versions'];
                $version=-1;
                $downloadable=true;
                foreach ($versions as $ver)
                {
                    $newVersion=intval($ver['id']);
                    if ($newVersion>$version)
                    {
                        $version=$newVersion;
                    }
                }

                $client = new Client();
                $response = $client->createRequest()
                    ->setMethod('GET')
                    ->setUrl($endpoint->url . "/tools/$id/versions/$version/cwl/descriptor")
                    ->setData(['descriptorType'=>'cwl', 'toolclass'=>'Workflow'])
                    ->send();

                $descriptor=$response->data['content'];
                $descriptor=yaml_parse($descriptor);
                $class=$descriptor['class'];
                $downloadable=true;
                if ($class!='Workflow')
                {
                    $downloadable=false;
                }
                else
                {
                    $steps=$descriptor['steps'];
                    /*
                     * numOfRequired starts at one
                     * in order to include the main
                     * workflow file.
                     */
                    $numOfRequired=1;
                    foreach ($steps as $step)
                    {
                        try
                        {
                            $file=explode('/',$step['run']);
                            $file=end($file);
                            $ext=explode('.',$file)[1];
                            if ($ext=='cwl')
                            {
                                $numOfRequired++;
                            }
                        }
                        catch (yii\base\ErrorException $e)
                        {
                            /*
                             * if the file has a funny form
                             * then get out and do not allow 
                             * the workflow to be downloaded.
                             */
                            $downloadable=false;
                            break;
                        }
                        
                    }

                    $client = new Client();
                    $response = $client->createRequest()
                        ->setMethod('GET')
                        ->setUrl($endpoint->url . "/tools/$id/versions/$version/cwl/files")
                        ->setData(['descriptorType'=>'cwl', 'toolclass'=>'Workflow'])
                        ->send();
                    $files=$response->data;
                    $numOfProvided=0;
                    foreach ($files as $file)
                    {
                        $name=$file['path'];
                        $ext=explode('.',$name)[1];
                        if ($ext=='cwl')
                        {
                            $numOfProvided++;
                        }
                    }
                    
                    if (($numOfProvided >= $numOfRequired) && $downloadable)
                    {
                        $downloadable=true;
                    }
                    else
                    {
                        $downloadable=false;
                    }

                }

                if (!isset($result[$endpoint->name]))
                {
                    $result[$endpoint->name]=[];
                }
                $tmp=['name'=>$wname, 'description'=>$description, 'downloadable'=>$downloadable, 'version'=>$version];
                $result[$endpoint->name][]=$tmp;
                
            }
        }
        return $result;
            
    }
}
