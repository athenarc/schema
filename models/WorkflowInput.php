<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "workflow_inputs".
 *
 * @property int $id
 * @property string $name
 * @property int $workflow_id
 * @property int $position
 * @property string $field_type
 * @property string $prefix
 * @property string $default_value
 * @property string $example
 * @property bool $optional
 * @property bool $separate
 */
class WorkflowInput extends \yii\db\ActiveRecord
{
    public $value='';
    public $dropdownValues='';
    public $dropdownSelected='';
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'workflow_inputs';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['workflow_id', 'position'], 'default', 'value' => null],
            [['workflow_id', 'position'], 'integer'],
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
            'workflow_id' => 'Workflow ID',
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
