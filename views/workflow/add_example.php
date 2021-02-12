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

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Form;
use yii\bootstrap\Button;
use yii\captcha\Captcha;
use yii\widgets\ActiveForm;
use app\components\Headers;



/*
 * Register css file along with the js needed for the button functionality
 */
echo Html::CssFile('@web/css/software/add-example.css');
$this->registerJsFile('@web/js/software/add-example.js', ['depends' => [\yii\web\JqueryAsset::className()]] );

$this->title = "Add example for $name v.$version";



$back_icon='<i class="fas fa-arrow-left"></i>'; 

Headers::begin() ?>
<?php echo Headers::widget(
['title'=>$this->title, 
    'buttons'=>
    [
        ['fontawesome_class'=>$back_icon,'name'=> 'Back', 'action'=>['/workflow/index'],
        'options'=>['class'=>'btn btn-default'], 'type'=>'a'], 
        
    ],
])
?>
<?Headers::end()?>



<div class="site-software">
<?php
	

	
if (!empty($fields))
{
?>

<?=Html::beginForm(['/workflow/add-example','name'=>$name, 'version'=>$version],'post',['class'=>'example_form','enctype'=>'multipart/form-data']); ?>
<div class="row">
	<div class="col-md-12"><h3>Determine arguments</h3></div>
</div> 
<?php
	$k=0;
	foreach ($fields as $field)
	{
		$error=!empty($valueErrors[$k]);
	?>
	<div class="input-row">
		<div class="row field-row">
			<div class="col-md-12 <?=$error ? 'has-error': '' ?>"><?=Html::label($field->name,null,[])?></div>
		</div>
		<div class="row">
<?php
			if ($field->field_type=='File')
			{
?>
				<div class="col-md-12"><?=Html::fileInput('field-' . $k,'',['class'=> ($error ? 'has-error': '') ])?></div>

<?php
			}
			else if ($field->field_type=='boolean')
			{
?>
				<div class="col-md-12"><?=Html::checkbox('field-' . $k,$field->value,['class'=> ($error ? 'has-error': ''), 'uncheck'=>"0"])?></div>
<?php
			}
			else if ($field->field_type=='enum')
			{
?>
				<div class="col-md-12"><?=Html::dropDownList('field-' . $k, $field->dropdownSelected, $field->dropdownValues)?></div>
<?php
			}
			else
			{
?>
				<div class="col-md-12"><?=Html::textInput('field-' . $k,'',['class'=> 'form-field ' . ($error ? 'has-error': '') ])?></div>
<?php
			}
?>
		</div>
<?php
		if (!empty($valueErrors[$k]))
		{
?>
			<div class="row field-row">
				<div class="col-md-12 has-error"><?=$valueErrors[$k]?></div>
			</div>
<?php
		}
		$k++;
?>
	</div>
	<?php
	}
	echo Html::endForm();
}
else
{
?>
<div>
	<h1>This software does not require any inputs</h1>
</div>

<?php
}

$submit_icon='<i class="fas fa-check"></i>';
?>

<div class="run-button-container"><?=Html::a("$submit_icon Submit",'javascript:void(0);',['id'=>'add-example-sumbit-button', 'class'=>"btn    btn-info btn-md"])?>
</div>

</div>
