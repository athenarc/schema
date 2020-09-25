<?php

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



/*
 * Register css file along with the js needed for the button functionality
 */
echo Html::CssFile('@web/css/software/add-example.css');
$this->registerJsFile('@web/js/software/add-example.js', ['depends' => [\yii\web\JqueryAsset::className()]] );

$this->title = "Add example for $name v.$version";



$back_icon='<i class="fas fa-arrow-left"></i>'; 
?>


<div class='title row'>
	<div class="col-md-11 headers">
		<h1><?= Html::encode($this->title) ?></h1>
	</div>


	<div class="col-md-1 back-btn">


		<?= Html::a("$back_icon Back", ['/workflow/index'], ['class'=>'btn btn-default']) ?>
	</div>
</div>

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
