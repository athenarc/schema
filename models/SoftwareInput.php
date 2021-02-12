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
 * This is the model class for table "software_inputs".
 *
 * @property int $id
 * @property string $name
 * @property int $softwareid
 * @property int $position
 * @property string $field_type
 * @property string $prefix
 * @property string $default_value
 * @property string $example
 * @property bool $optional
 * @property bool $separate
 */
class SoftwareInput extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public $value='';
    
    public static function tableName()
    {
        return 'software_inputs';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['softwareid', 'position'], 'default', 'value' => null],
            [['softwareid', 'position'], 'integer'],
            [['optional', 'separate'], 'boolean'],
            [['name'], 'string', 'max' => 100],
            [['field_type'], 'string', 'max' => 15],
            [['prefix'], 'string', 'max' => 50],
            [['default_value', 'example'], 'string', 'max' => 150],
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
            'softwareid' => 'Softwareid',
            'position' => 'Position',
            'field_type' => 'Field Type',
            'prefix' => 'Prefix',
            'default_value' => 'Default Value',
            'example' => 'Example',
            'optional' => 'Optional',
            'separate' => 'Separate',
        ];
    }
}
