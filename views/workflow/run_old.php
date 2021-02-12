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



/*
 * Register css file along with the js needed for the button functionality
 */
echo Html::CssFile('@web/css/workflow/run.css');
$this->registerJsFile('@web/js/workflow/run-index.js', ['depends' => [\yii\web\JqueryAsset::className()]] );

$this->title = "Run workflow ($name v.$version) ";
//$this->title = "$name v.$version ";



// $this->params['breadcrumbs'][] = $this->title;

/*
 * If a pod is running, then show the command box first
 * or else show the field list (if there is one)
 */
$commandsDisabled= ($jobid!='') ? true : false;


if ($commandsDisabled)
{
	$commandBoxClass= 'disabled-box';
}
else
{
	$commandBoxClass='';
}

if ($hasExample)
{
	$exampleBtnLink='javascript:void(0);';
}
else
{
	$exampleBtnLink=null;
}

/* 
 * Show tabs
 */
$back_icon='<i class="fas fa-arrow-left"></i>';
$clear_file_icon='<i class="fas fa-times"></i>';
$select_file_icon='<i class="fas fa-folder-open"></i>';


?>


<div class='title row'>
	
	<div class="col-md-10" style="color:#E6833B">
		
		<h2><?= Html::encode($this->title) ?></h2>
		
	</div>



	<div class="col-md-2 back-btn">
		<?= Html::a("$back_icon Available Workflows", ['/workflow/index'], ['class'=>'btn btn-default']) ?>
	</div>


</div>



<div class="site-software">
<?php
	ActiveForm::begin($form_params); 

?>




<div class="row">&nbsp;</div>
<div class="row">&nbsp;</div>



<div class="row">

<div class="col-md-7">
<?php

	$select_icon='<i class="fas fa-folder-open"></i>';
	$clear_icon='<i class="fas fa-times"></i>';

	// print_r($iocontMount);
	// print_r("<br />");
	// print_r($icontMount);
	// print_r("<br />");
	// print_r($ocontMount);
	// print_r("<br />");
	// exit(0);
	echo Html::hiddenInput('mountcaller',null ,['id'=>'mountcaller']);

?>
	
	<div class="row">
	<div class="col-md-7">	<h3>Output directory <i class="fa fa-question-circle" style="font-size:20px" title="Select the folder where the outputs of the workflow will be placed")> </i></h3> </div>
	</div>


	<div class="row">
	<div class="col-md-7">		

				<?=Html::textInput('outFolder',$outFolder,['id' => 'outFolder','class'=>'mount-field','readonly'=>true,])?>
	            <?=Html::a("$select_icon Select",'javascript:void(0);',['class'=>'select-output-button btn btn-success btn-md','disabled'=>($commandsDisabled)])?>
				<?=Html::a("$clear_icon Clear",'javascript:void(0);',['class'=>'clear-output-button btn btn-danger btn-md','disabled'=>($commandsDisabled)])?>
				
	</div>
						
	</div>

	<?=Html::hiddenInput('selectoutputurl',Url::to(['workflow/select-output','username'=>$username]) ,['id'=>'selectoutputurl'])?>




<!-- <a href='javascript:void(0);' class='form-btn-link'><div id="select-non-active-form-btn" class='<?php//=$simpleFormButtonClass?>'>Use field form</div></a>
<a href='javascript:void(0);' class='form-btn-link'><div id="select-active-form-btn" class='<?php//=$activeFormButtonClass?>'>Use text box</div></a>
<br /><br /> -->




<?php
/*
 * This is the non active form, that does not really submit.
 * The content of the fields is concatenated with JS, pasted
 * in the command box and the active form is submitted.
 */
?>
<div id="non-active-run-form">
<?=Html::hiddenInput('jobid', $jobid,['id'=>'hidden_jobid_input'])?>
<?=Html::hiddenInput('name', $name,['id'=>'hidden_name_input'])?>
<?=Html::hiddenInput('version', $version,['id'=>'hidden_version_input'])?>
<?=Html::hiddenInput('example', $example,['id'=>'hidden_example_input'])?>
<?=$hasExample ? Html::hiddenInput('has_example','',['id'=>'has_example']) : ''?>
<?php
/* 
 * TODO PHP code here
 */

if (!empty($fields))
{




	
?>

<div class="row">
<div class="col-md-7"><h3>Arguments</h3></div>
</div> 
<?php

	$default_icon='<i class="fas fa-magic"></i>';
	$default_title='Fill field with default value.';
	$select_file_title='Select file.';
	$clear_file_title='Clear field.';
	$index=0;
	foreach ($fields as $field)
	{
	?>
		<div class="row">
			<div class="col-md-7"><?=Html::label($field->name,null,[])?></div>
	<?php
			if ($field->field_type=='boolean')
			{
	?>
				<div class="col-md-5">
					<?=Html::checkbox('field-' . $index,$field->value,['readonly'=>$commandsDisabled,'class'=>$commandBoxClass, 'id'=>'field-' . $index, 'uncheck'=>"0"])?>
				</div>
				

			<?php
			}
			else if ($field->field_type=='enum')
			{
				// print_r($field->dropdownValues);
				// exit(0);
			?>
				<div class="col-md-5">
					<?=Html::dropDownList('field-' . $index, $field->dropdownSelected, $field->dropdownValues ,['readonly'=>$commandsDisabled,'class'=>$commandBoxClass . ' field-dropdown', 'id'=>'field-' . $index])?>
				</div>
			<?php
			}
			else if ($field->field_type=='File')
			{
				if ($field->is_array)
                {
                    $slcbtnLink='workflow/select-file-multiple';
                    $select_file_icon='<i class="fas fa-copy"></i>';
                    $select_file_title='Select files';
                }
                else
                {
                    $slcbtnLink='workflow/select-file';
                    $select_file_icon='<i class="fas fa-file"></i>';
                    $select_file_title='Select file';
                }
	        ?>
                <div class="col-md-5">
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
					$slcbtnLink='workflow/select-folder-multiple';
                    $select_file_icon='<i class="fas fa-folder"></i>';
                    $select_file_title='Select directories';

				}
				else
				{
					$slcbtnLink='workflow/select-folder';
                    $select_file_icon='<i class="fas fa-folder"></i>';
                    $select_file_title='Select directory';
				}
			?>
				<div class="col-md-5">
					<?=Html::textInput('field-' . $index,$field->value,['readonly'=>true,'class'=>'input_field ' . $commandBoxClass, 'id'=>'field-' . $index])?>
					<?=Html::a($select_file_icon,'javascript:void(0);',['disabled'=>$commandsDisabled, 'class'=>'btn btn-success select-file-btn','title'=>$select_file_title])?>
					<?=Html::a($clear_file_icon,'javascript:void(0);',['disabled'=>$commandsDisabled, 'class'=>'btn btn-danger clear-file-btn','title'=>$clear_file_title])?>
					<?=Html::hiddenInput('hidden_select_file_url', Url::to([$slcbtnLink, 'caller'=>'field-' . $index]), ['class'=>'hidden_select_folder_url'])?>
				</div>
			<?php
			}	
			else
			{
				if (!$field->is_array)
                {

                ?>
                    <div class="col-md-5">
                        <?=Html::textInput('field-' . $index,$field->value,['readonly'=>$commandsDisabled,'class'=>'input_field ' . $commandBoxClass, 'id'=>'field-' . $index])?>
                        <?=(($field->field_type!='file') && (!empty($field->default_value))) ? Html::a($default_icon,'javascript:void(0);',['id'=>'default-values', 'class'=>'btn btn-basic btn-default-values','title'=>$default_title]) : ''?>
                    </div>
                    <?=Html::hiddenInput('default_field_values[]',$field->default_value,['readonly'=>true, 'class'=>'hidden_default_value'])?>  
                
                <?php
                }
                else
                {
                    $fill_array_icon='<i class="fas fa-table"></i>';
                    $fill_array_title='Fill array field'
                ?>

                    <div class="col-md-5">
                        <?=Html::textInput('field-' . $index,$field->value,['readonly'=>true,'class'=>'array_field input_field ' . $commandBoxClass, 'id'=>'field-' . $index])?>
                        <?=Html::a($fill_array_icon,'javascript:void(0);',['disabled'=>$commandsDisabled, 'class'=>"btn btn-success fill-array-field-btn",'title'=>$fill_array_title])?>
                    <?=Html::a($clear_file_icon,'javascript:void(0);',['disabled'=>$commandsDisabled, 'class'=>'btn btn-danger clear-folder-btn','title'=>$clear_file_title])?>
                    <?=Html::hiddenInput('hidden_fill_array_field_url', Url::to(['software/fill-array-field', 'caller'=>'field-' . $index]), ['class'=>'hidden_fill_array_field_url'])?>
                    </div>
                
                <?php
                }
			}	
            ?>   
		</div>
	<?php
	$index++;
	}
	echo Html::hiddenInput('fieldsNum', count($fields),['id'=>'hidden_fieldsNum']);
}
else
{
?>
<br>
	<div class="row">
	<div class="col-md-7"><h3>Arguments</h3></div>
	</div> 

	<?php
		echo '<div class="alert alert-success row" role="alert">';
		echo "Based on the provided CWL description, this workflow does not require inputs.";
		echo '</div>';
		?>

	<?php



}

// $numOfFields=empty($fields)? 0 : count($fields);

// echo Html::hiddenInput('fieldNum', $numOfFields,['id'=>'hidden_fieldNum']);
// echo Html::hiddenInput('fieldScript', $script ,['id'=>'hidden_fieldScript']);

?>

</div>



</div>





<div class="col-md-5 project-quotas-div rcorners">

<div class="row run-headers">
<h3 id="centerheaders"><b>Job resources</b></h3>
</div>




<div class="row">&nbsp;</div>

<div class="row">
	<b class="quotas-line">Relevant Project:</b>&nbsp; <?=$project?> (<?=$quotas['num_of_jobs']-$jobUsage?> remaining jobs)&nbsp;<?=Html::a('Change project',['workflow/index'])?>
</div>
<div class="row">&nbsp;</div>





<div class="row">
	<b class="quotas-line">CPU cores for the job:</b> &nbsp; <?=Html::textInput('cores',$maxCores,['id' => 'cores','readonly'=>$commandsDisabled,'class'=>"$commandBoxClass inputbox"])?> &nbsp; out of <?=$quotas['cores']?>
</div>
<div class="row">&nbsp;</div>
<div class="row">
	<b class="quotas-line">Memory (in GBs) for the job:</b> &nbsp; <?=Html::textInput('memory',$maxMem,['id' => 'memory','readonly'=>$commandsDisabled,'class'=>"$commandBoxClass inputbox"])?> &nbsp; out of <?=$quotas['ram']?> 

</div>

</div>


</div>







<?php
ActiveForm::end();
/*
 * Run, Run example and Cancel buttons.
 */

$play_icon='<i class="fas fa-play"></i>';

?>



	
</div>

<div class="row">
	<div class="run-button-container col-md-1"><?=Html::a("$play_icon Run",'javascript:void(0);',['id'=>'software-start-run-button', 'class'=>"btn    btn-success btn-md",'disabled'=>($commandsDisabled)])?></div>

	<div class="run-button-container col-md-2"><?=Html::a("$play_icon Run example",'javascript:void(0);',['id'=>'software-run-example-button', 'class'=>"btn btn-success btn-md",'disabled'=>((!$hasExample) || $commandsDisabled)])?></div>

<?php

	if ((($superadmin) || ($username==$uploadedBy)) && (!$hasExample))
	{
		if ($commandsDisabled)
		{
			$addExampleHidden="add-example-link-hidden";
		}
		else
		{
			$addExampleHidden="";
		}
	
?>

<?php


?>

	<div class="add-example-link col-md-3 <?=$addExampleHidden?>"><?=Html::a('Add example',['/workflow/add-example','name'=>$name, 'version' =>$version],['id'=>'software-add-example-button', 'class'=>'btn btn-link'])?></div>
<?php

	}

$cancel_icon='<i class="fas fa-times"></i>';
?>


	<!--<div class="cancel-button-container col-md-2"><?=Html::a("$cancel_icon Cancel ",'javascript:void(0);',['id'=>'software-cancel-button', 'class'=>'btn btn-danger'])?></div>-->
</div>



<div id="error-report">
<?php 
/*
 * Scheduler errors
 */

if (!empty($errors))
{
	echo "<br />";
	echo Html::label("Schedule errors:");
	echo "<br />";

	foreach ($errors as $error)
	{
		echo $error . "<br />";
	}
}
/*
 * Kubernetes errors
 */
if (!empty($runErrors))
{
	echo "<br />";
	echo Html::label("Kubernetes errors:");
	echo "<br />";
	echo $runErrors;

}
?>
</div>
<div id="pod-logs"></div>

<?php
	/*
	 * If a pod is running, then register the JS file
	 * with the AJAX that updates the logs div. At the beginning
	 * keep the status at "Initializing".
	 */
	
	if (!empty($jobid))
	{
	?>
		<div id="initial-status">
			<div class="row">
				<div class="col-md-12"><h3>Runtime Info:</h3></div>
			</div>
			<div class="row" id="initial-status">
				<div class=" col-md-5">
					<span class="status-label"><b>Workflow status:</b></span>
					<span id="status-value" class="status-INITIALIZING">INITIALIZING</span>
				</div>
			</div>
		</div>
		
	<?php
		echo $this->registerJsFile('@web/js/workflow/logs.js', ['depends' => [\yii\web\JqueryAsset::className()]] );
	}

?>






</div>
