<?php
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
 *  along with Foobar.  If not, see <https://www.gnu.org/licenses/>.
 *
 ************************************************************************************/
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
