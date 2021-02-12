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
            [['dock_link'], 'url'],
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
            'dock_link' => 'Dockerhub URL',
        ];
    }
}
