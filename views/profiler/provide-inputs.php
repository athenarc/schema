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
use app\components\Headers;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title="Provide profiler inputs for $software->name v.$software->version";
/*
 * Include css and js files
 */

echo Html::CssFile('@web/css/profiler/provide-inputs.css');
$this->registerJsFile('@web/js/profiler/provide-inputs.js', ['depends' => [\yii\web\JqueryAsset::className()]]);



$select_icon='<i class="fas fa-folder-open"></i>';
$clear_icon='<i class="fas fa-times"></i>';
$required_icon="<span class='required-star'>*</span>";
$clear_file_icon='<i class="fas fa-times"></i>';
$clear_file_title='Clear field.';

/*
 * Create headers
 */
Headers::begin();
echo Headers::widget(
['title'=>$this->title, 
    'buttons'=>
    [
        ['fontawesome_class'=>'<i class="fas fa-arrow-left"></i>','name'=> 'Available Software', 'action'=>['/software/index'],
        'options'=>['class'=>'btn btn-default'], 'type'=>'a'], 
        
    ],
]);

Headers::end();

?>

<div class="row">&nbsp;</div>

<?php
/*
 * Create mountpoint select field
 */
?>
<?=Html::beginForm(['/profiler/provide-inputs','name'=>$name,'version'=>$version],'post')?>
<div class="row">
        <div class="col-md-12 text-center">  <h3>Input/Output directory <i class="fa fa-question-circle" class="mount-icon" title="Select a folder to mount to the <?=$software->imountpoint?> directory in the container.")> </i></h3> </div>
</div>
<div class="row">
    <div class="col-md-12 text-center">      
        <?=Html::textInput('systemmount','',['id' => 'systemmount','class'=>'mount-field','readonly'=>true,])?>
        <?=Html::a("$select_icon Select",'javascript:void(0);',['class'=>'select-mount-button btn btn-success btn-md'])?>
        <?=Html::a("$clear_icon Clear",'javascript:void(0);',['class'=>'clear-mount-button btn btn-danger btn-md'])?>
    </div>
    <?=Html::hiddenInput('selectmounturl',Url::to(['software/select-mountpoint','username'=>$username]) ,['id'=>'selectmounturl'])?>
    <?=Html::hiddenInput('mountcaller','#systemmount' ,['id'=>'mountcaller']);?>
</div>

<div class="row">&nbsp;</div>
<?php
if (!empty($errors))
{
?>
<div class="row">
    <div class="col-md-offset-3 col-md-6 errors-div">
        <ul>
<?php
    foreach ($errors as $error)
    {
        echo '<li>' . $error . '</li>';
    }
?>
        </ul>
    </div>
</div>
<?php
}
?>

<div class="row">
    <div class="col-md-12 text-center"><h3>Arguments</h3></div>
</div>
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

<?php
$index=0;
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
        }?>
        <div class="col-md-offset-3 col-md-3 optional <?=$optional_fields?>" style="text-align: right;" id='field-names'><span class="required hidden"><?=Html::label($field_name,null,[])?></span>
            <span class="non-required"><?=Html::label($field->name,null,[])?></span>
        </div>
        
        <?php
        if ($field->field_type=='boolean')
        {?>

            <div class="col-md-4 optional <?=$optional_fields?>" style="text-align: left;">
                <?=Html::checkbox('field-' . $index,$field->value,['id'=>'field-' . $index, 'uncheck'=>"0"])?>
            </div>

        <?php
        }
        else if ($field->field_type=='File')
        {
            $slcbtnLink='software/select-file-multiple';
            $select_file_icon='<i class="fas fa-copy"></i>';
            $select_file_title='Select files';
            $incTitle='Include in classification';            

        ?>
            <div class="col-md-4 <?=$optional_fields?>" style="text-align: left;">
                <?=Html::textInput('field-' . $index, $field->value,['readonly'=>true,'class'=>'file_field input_field ', 'id'=>'field-' . $index])?>
                <?=Html::a($select_file_icon,'javascript:void(0);',['class'=>"btn btn-success select-file-btn",'title'=>$select_file_title])?>
                <?=Html::a($clear_file_icon,'javascript:void(0);',['class'=>'btn btn-danger clear-file-btn','title'=>$clear_file_title])?>
                <?=Html::checkbox('include-' . $index,true,['id'=>'include-' . $index, 'title'=>$incTitle, 'class'=>'include-check'])?>
                <?=Html::hiddenInput('hidden_select_file_url', Url::to([$slcbtnLink, 'caller'=>'field-' . $index]), ['class'=>'hidden_select_file_url'])?>
            </div>
        <?php
        }
        else if ($field->field_type=='Directory')
        {
            $slcbtnLink='software/select-folder-multiple';
            $select_file_icon='<i class="far fa-folder"></i>';
            $select_file_title='Select directories';

        ?>
            <div class="col-md-4 <?=$optional_fields?>" style="text-align: left;">
                <?=Html::textInput('field-' . $index,$field->value,['readonly'=>true,'class'=>'folder_field input_field ', 'id'=>'field-' . $index])?>
                <?=Html::a($select_file_icon,'javascript:void(0);',['class'=>"btn btn-success select-folder-btn",'title'=>$select_file_title])?>
                <?=Html::a($clear_file_icon,'javascript:void(0);',['class'=>'btn btn-danger clear-folder-btn','title'=>$clear_file_title])?>
                <?=Html::checkbox('include-' . $index,true,['id'=>'include-' . $index, 'title'=>$incTitle, 'class'=>'include-check'])?>
                <?=Html::hiddenInput('hidden_select_file_url', Url::to([$slcbtnLink, 'caller'=>'field-' . $index]), ['class'=>'hidden_select_folder_url'])?>
            </div>
        <?php
        }       
        else
        {

            $fill_array_icon='<i class="fas fa-table"></i>';
            $fill_array_title='Fill array field'
        ?>

            <div class="col-md-4 <?=$optional_fields?>" style="text-align: left;">
                <?=Html::textInput('field-' . $index,$field->value,['readonly'=>true,'class'=>'array_field input_field ', 'id'=>'field-' . $index])?>
                <?=Html::a($fill_array_icon,'javascript:void(0);',['class'=>"btn btn-success fill-array-field-btn",'title'=>$fill_array_title])?>
                <?=Html::a($clear_file_icon,'javascript:void(0);',['class'=>'btn btn-danger clear-folder-btn','title'=>$clear_file_title])?>
                <?=Html::checkbox('include-' . $index,true,['id'=>'include-' . $index, 'title'=>$incTitle, 'class'=>'include-check'])?>
                <?=Html::hiddenInput('hidden_fill_array_field_url', Url::to(['software/fill-array-field', 'caller'=>'field-' . $index]), ['class'=>'hidden_fill_array_field_url'])?>
            </div>
            
        <?php
            
        }
        echo Html::hiddenInput('fieldsNum', count($fields),['id'=>'hidden_fieldsNum']);
        $index++;   
        ?>   
        </div>
    <?php
        
}

?>
    <div class="row">&nbsp;</div>
    <div class="row">
        <div class="col-md-12 text-center">
            <?=Html::submitButton('Submit',['class'=>'btn btn-success btn-md'])?>
        </div>
    </div>
    <?=Html::endForm()?>

<?php
if (!empty($software->profiled))
{
?>

<!-- Modal -->
<div class="modal fade" id="existingProfileModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Execution profile already exists!</h5>
      </div>
      <div class="modal-body">
        An execution profile for <?=$software->name?> v.<?=$software->version?> already exists. You can discard the existing model and create a new one, or return to the previous page.
      </div>
      <div class="modal-footer">
        <?=Html::a('Return',['/software/index'],['class'=>'btn btn-primary'])?>
        <button type="button" class="btn btn-default" data-dismiss="modal">Discard existing & create new</button>
      </div>
    </div>
  </div>
</div>

<?php
}
?>