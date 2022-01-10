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

/**
 * View file for the execution of a docker software image.
 * 
 * @author: Kostis Zagganas
 * First version: Dec 2018
 */
namespace app\components;


use Yii;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Form;
use yii\bootstrap\Button;
use yii\captcha\Captcha;
use yii\widgets\ActiveForm;
use yii\bootstrap4\Modal;


/*
 * Register css file along with the js needed for the button functionality
 */

class RunFormWidget
{

    public static function showOutputField($commandsDisabled,$outFolder,$username,$type)
    {

        $select_icon='<i class="fas fa-folder-open"></i>';
        $clear_icon='<i class="fas fa-times"></i>';
        if ($type==1)
        {
            $runType='job';
        }
        if ($type==3)
        {
            $runType='workflow';
        }
        echo Html::hiddenInput('mountcaller',null ,['id'=>'mountcaller']);
        ?>
        <div class="row">
                <div class="col-md-12"> <h3>Output directory <i class="fa fa-question-circle question-symbol"  title="Select the folder where the output of the <?=$runType?> will be placed")> </i></h3> </div>
            </div>
            <div class="row">
                <div class="col-md-12">     
                    <?=Html::textInput('outFolder',$outFolder,['id' => 'outFolder','class'=>'mount-field','readonly'=>true,])?>
                    <?=Html::a("$select_icon Select",'javascript:void(0);',['class'=>'select-output-button btn btn-success btn-md','disabled'=>($commandsDisabled)])?>
                    <?=Html::a("$clear_icon Clear",'javascript:void(0);',['class'=>'clear-output-button btn btn-danger btn-md','disabled'=>($commandsDisabled)])?>
                </div>
            </div>
            <?=Html::hiddenInput('selectoutputurl',Url::to(['workflow/select-output','username'=>$username]) ,['id'=>'selectoutputurl'])?>
        <?php
    }

    public static function showHiddenFields($jobid,$name,$version,$example, $hasExample)
    {
        ?>
                    <?=Html::hiddenInput('jobid', $jobid,['id'=>'hidden_jobid_input'])?>
                    <?=Html::hiddenInput('name', $name,['id'=>'hidden_name_input'])?>
                    <?=Html::hiddenInput('version', $version,['id'=>'hidden_version_input'])?>
                    <?=Html::hiddenInput('example', $example,['id'=>'hidden_example_input'])?>
                    <?=$hasExample ? Html::hiddenInput('has_example','',['id'=>'has_example']) : ''?>
        <?php
    }


    public static function showArguments($fields,$type,$commandBoxClass,$commandsDisabled)
    {   

        /*
         * type 1 is software
         * type 2 is software-mpi (deprecated)
         * type 3 is workflows
         */
        ?>
        <div class="row">
            <div class="col-md-12"><h3>Arguments <i class="fa fa-question-circle" style="font-size:20px; cursor: pointer" title="Select arguments for execution.")></i></h3></div>
        </div>
        <?php
        if (!empty($fields))
        {
        ?>          
            <div class="row">
                <div class="col-md-6 text-right">
                <strong>Show optional fields: &nbsp;</strong>
                </div>
                <div class="col-md-6 text-left">
                    <label class="switch" title="Show optional fields">
                          <input type="checkbox"> 
                          <span class="slider round"></span>
                    </label>
                </div>
            </div> 
            <div class="row">&nbsp;</div>
        <?php
            $default_icon='<i class="fas fa-magic"></i>';
            $default_title='Fill field with default value.';
            $select_file_title='Select file.';
            $clear_file_title='Clear field.';
            $clear_file_icon='<i class="fas fa-times"></i>';
            $select_file_icon='<i class="fas fa-copy"></i>';
            $required_icon="<span style='color:red;'>*</span>";
            $index=0;
            if ($type==1)
            {
                $controller='software';
            }
            else if ($type==3)
            {
                $controller='workflow';
            }
                
        
            foreach ($fields as $field)
            {
        ?>
            <div class="row">
        <?php 
                    
                if($field->optional==1)
                {
                    $optional_fields='hid';
                    $field_name=$field->name;

                }
                else
                {
                    $optional_fields='show';
                    $field_name="$required_icon $field->name";
                }
        ?>
                <div class="col-md-offset-3 col-md-3 optional <?=$optional_fields?>" style="text-align: right;" id='field-names'><span class="required hidden"><?=Html::label($field_name,null,[])?></span>
                    <span class="non-required"><?=Html::label($field->name,null,[])?></span>
                </div>
                        
        <?php
                if ($field->field_type=='boolean')
                {
        ?>

                    <div class="col-md-6 <?=$optional_fields?>" style="text-align: left;">
                        <?=Html::checkbox('field-' . $index,$field->value,['readonly'=>$commandsDisabled,'class'=>$commandBoxClass, 'id'=>'field-' . $index, 'uncheck'=>"0"])?>
                    </div>

        <?php
                }
                else if ($field->field_type=='enum')
                {
                    
        ?>
                    <div class="col-md-6 <?=$optional_fields?>" style="text-align: left;">
                        <?=Html::dropDownList('field-' . $index, $field->dropdownSelected, $field->dropdownValues ,['readonly'=>$commandsDisabled,'class'=>$commandBoxClass . ' field-dropdown', 'id'=>'field-' . $index])?>
                    </div>
        <?php
                }
                else if ($field->field_type=='File')
                {
                    if ($field->is_array)
                    {
                        $slcbtnLink="$controller/select-file-multiple";
                        $select_file_icon='<i class="fas fa-copy"></i>';
                        $select_file_title='Select files';
                    }
                    else
                    {
                        $slcbtnLink="$controller/select-file";
                        $select_file_icon='<i class="fas fa-file"></i>';
                        $select_file_title='Select file';
                    }

        ?>
                    <div class="col-md-6 <?=$optional_fields?>" style="text-align: left;">
                        <?=Html::textInput('field-' . $index, $field->value,['readonly'=>true,'class'=>'file_field input_field ' . $commandBoxClass, 'id'=>'field-' . $index])?>
                        <?=Html::a($select_file_icon,'javascript:void(0);',['disabled'=>$commandsDisabled, 'class'=>"btn btn-success select-file-btn",'title'=>$select_file_title])?>
                        <?=Html::a($clear_file_icon,'javascript:void(0);',['disabled'=>$commandsDisabled, 'class'=>'btn btn-danger clear-file-btn','title'=>$clear_file_title])?>
                        <?=Html::hiddenInput('hidden_select_file_url', Url::to([$slcbtnLink, 'caller'=>'field-' . $index]), ['class'=>'hidden_select_file_url'])?>
                    </div>
        <?php
                }

                else if ($field->field_type=='Directory')
                {
                    if ($field->is_array)
                    {
                        $slcbtnLink="$controller/select-folder-multiple";
                        $select_file_icon='<i class="far fa-folder"></i>';
                        $select_file_title='Select directories';

                    }
                    else
                    {
                        $slcbtnLink="$controller/select-folder";
                        $select_file_icon='<i class="far fa-folder"></i>';
                        $select_file_title='Select directory';
                    }
        ?>
                    <div class="col-md-6 <?=$optional_fields?>" style="text-align: left;">
                        <?=Html::textInput('field-' . $index,$field->value,['readonly'=>true,'class'=>'folder_field input_field ' . $commandBoxClass, 'id'=>'field-' . $index])?>
                        <?=Html::a($select_file_icon,'javascript:void(0);',['disabled'=>$commandsDisabled, 'class'=>"btn btn-success select-folder-btn",'title'=>$select_file_title])?>
                        <?=Html::a($clear_file_icon,'javascript:void(0);',['disabled'=>$commandsDisabled, 'class'=>'btn btn-danger clear-folder-btn','title'=>$clear_file_title])?>
                        <?=Html::hiddenInput('hidden_select_file_url', Url::to([$slcbtnLink, 'caller'=>'field-' . $index]), ['class'=>'hidden_select_folder_url'])?>

                    </div>
        <?php
                }
                else
                {
                    if (!$field->is_array)
                    {
        ?>
                        <div class="col-md-6 <?=$optional_fields?>" style="text-align: left;">
                            <?=Html::textInput('field-' . $index,$field->value,['readonly'=>$commandsDisabled,'class'=>'input_field ' . $commandBoxClass, 'id'=>'field-' . $index])?>
                            <?=(($field->field_type!='file') && (!empty($field->default_value))) ? Html::a($default_icon,'javascript:void(0);',['id'=>'default-values', 'class'=>'btn btn-basic btn-default-values','title'=>$default_title]) : ''?>
                        </div>
                        <?=Html::hiddenInput('default_field_values[]',$field->default_value,['readonly'=>true, 'class'=>'hidden_default_value'])?>  
                    
         <?php
                    }
                    else
                    {
                        $fill_array_icon='<i class="fas fa-table"></i>';
                        $fill_array_title='Fill array field';
        ?>

                        <div class="col-md-6 <?=$optional_fields?>" style="text-align: left;">
                            <?=Html::textInput('field-' . $index,$field->value,['readonly'=>true,'class'=>'array_field input_field ' . $commandBoxClass, 'id'=>'field-' . $index])?>
                            <?=Html::a($fill_array_icon,'javascript:void(0);',['disabled'=>$commandsDisabled, 'class'=>"btn btn-success fill-array-field-btn",'title'=>$fill_array_title])?>
                        <?=Html::a($clear_file_icon,'javascript:void(0);',['disabled'=>$commandsDisabled, 'class'=>'btn btn-danger clear-folder-btn','title'=>$clear_file_title])?>
                        <?=Html::hiddenInput('hidden_fill_array_field_url', Url::to(["$controller/fill-array-field", 'caller'=>'field-' . $index]), ['class'=>'hidden_fill_array_field_url'])?>
                        </div>
                    
                <?php
                    }
                }
            
                $index++;  
        ?>

            </div>
        <?php
            
            }
            echo Html::hiddenInput('fieldsNum', count($fields),['id'=>'hidden_fieldsNum']);
        }
                
        else
        {
            if ($type==1)
            {
                $object='docker image';
            }
            else if ($type==3)
            {
                $object='workflow';
            }
        ?>
        <div class="alert alert-success col-md-offset-3 col-7" role="alert">
            Based on the provided CWL description, this <?=$object?> does not require arguments.
        </div>
        <?php
        }
    }

    public static function showResources($quotas,$maxCores,$maxMem,$commandsDisabled,$commandBoxClass)
    {
    ?>
            
            <div class="row">
                <div class="col-md-12" style="padding-left: 10px;">
                    <h3>Job resources <i class="fa fa-question-circle" style="font-size:20px; cursor: pointer" title="Select job resources for execution.")></i></h3>
                </div>
            </div>
                <div class="row">
                    <div class="col-md-12">
                        <b class="quotas-line col-md-6" style="text-align: right; margin-bottom: 5px;">CPU cores:</b> &nbsp;

                    <div class="col-md-3" style="text-align: left;"><?=Html::textInput('cores',$maxCores,['id' => 'cores','readonly'=>$commandsDisabled,'class'=>"$commandBoxClass"])?> &nbsp; out of <?=$quotas['cores']?>
                    </div>
                    </div>
                </div>
            <div class="row">
                    <div class="col-md-12">
                    <b class="quotas-line col-md-6" style="text-align: right;">Memory (in GBs):</b> &nbsp; 
                    <div class="col-md-3" style="text-align: left;"><?=Html::textInput('memory',$maxMem,['id' => 'memory','readonly'=>$commandsDisabled,'class'=>"$commandBoxClass"])?> &nbsp; out of <?=$quotas['ram']?> 
                    </div>
                    </div>
            </div> 
        <div class="row">&nbsp;</div>
            

        </div>
        </div>

    <?php
    }

    public static function showRunButtons($superadmin,$username,$uploadedBy,$hasExample,$commandsDisabled,$type,$name,$version,$instructions,$visualize='')
    {
    /*
     * Run, Run example and Cancel buttons.
     */

        $play_icon='<i class="fas fa-play"></i>';
        $visualize_icon='<i class="fas fa-eye"></i>';
        $instructions_icon='<i class="fa fa-file aria-hidden="true" style="color:white"></i>';
        $cancel_icon='<i class="fas fa-times"></i>';
        
        $addExampleClass="hidden-element";
        if ((($superadmin) || ($username==$uploadedBy)) && (!$hasExample))
        {

            if ($commandsDisabled)
            {
                $addExampleClass="hidden-element";
            }
            else
            {
                $addExampleClass="";
            }
           
        }
        $visualize_class=($type==3) ? '' : 'hidden-element';
        $cancel_class=($commandsDisabled) ? '' : 'hidden-element';
    
    /*
     * The control-buttons class is added to all the buttons except
     * the first one, in order to add some css padding
     */
    ?>
    <div class="row">
        <div class="col-md-12 text-center">
            <?=Html::a("$play_icon Run",'javascript:void(0);',['id'=>'software-start-run-button', 'class'=>"btn btn-success btn-md",'disabled'=>($commandsDisabled)])?>
            <?=Html::a("$play_icon Run example",'javascript:void(0);',['id'=>'software-run-example-button', 'class'=>"btn btn-success btn-md control-buttons",'disabled'=>((!$hasExample) || $commandsDisabled)])?>
            <?=Html::a("$instructions_icon Instructions</span>",null,['id'=>'instructions-btn', 'data-toggle'=>'modal','data-target'=>"#per", 'class'=>'btn btn-secondary btn-md instructions control-buttons'])?>
            <?=Html::a("$visualize_icon Visualize",null,['id'=>'visualization-btn', 'class'=>"btn btn-primary btn-md $visualize_class control-buttons"])?>
            <?=Html::a("$cancel_icon Cancel ",'javascript:void(0);',['id'=>'software-cancel-button', 'class'=>"btn btn-danger $cancel_class control-buttons"])?></div>
        </div>
        <div class="row">
            <div class="col-md-12 text-center">
                <?=Html::a('Add example',['/workflow/add-example','name'=>$name, 'version' =>$version],['id'=>'software-add-example-button', 'class'=>"btn btn-link $addExampleClass"])?>
            </div>
        </div>


    
    <div class='modal fade' tabindex='-1' role='dialog' id='instructions-modal' aria-labelledby='description-modal' aria-hidden='true'>
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class='modal-title' id='exampleModalLongTitle'><?="$name v.$version"?></h5>
                    <button type='button' class='close' data-dismiss='modal' aria-label='Close'>
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                 <div class="modal-body">
                    <?=(empty($instructions)) ? 'Instructions not available' : $instructions ?>
                </div>
                <div class="modal-footer">
                    <button type='button' class='btn btn-secondary' data-dismiss='modal'>Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class='modal fade' tabindex='-1' role='dialog' id='vis-modal' aria-labelledby='description-modal' aria-hidden='true'>
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content vis-modal-content" >
                <div class="modal-header">
                    <h5 class='modal-title' id='exampleModalLongTitle'><?="$name v. $version"?></h5>
                        <button type='button' class='close' data-dismiss='modal' aria-label='Close'>
                            <span aria-hidden="true">&times;</span>
                        </button>
                </div>
                <div class="modal-body">
                    <?=(empty($visualize)) ? 'Workflow visualizaton not available' : Html::img("@web/img/workflows/$visualize", ['width'=>"600px",'height'=>'400px']) ?>
                </div>
                <div class="modal-footer">
                    <button type='button' class='btn btn-secondary' data-dismiss='modal'>Close</button>
                </div>
            </div>
        </div>
    </div>
    <?php
    }
    
}
?>     
