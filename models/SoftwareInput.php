<?php

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
