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
echo Html::CssFile('@web/css/software-mpi/run.css');
$this->registerJsFile('@web/js/software-mpi/run-index.js', ['depends' => [\yii\web\JqueryAsset::className()]] );

$this->title = "New job ($name v.$version) ";
//$this->title = "$name v.$version ";



// $this->params['breadcrumbs'][] = $this->title;

/*
 * If a pod is running, then show the command box first
 * or else show the field list (if there is one)
 */
$commandsDisabled= ($cluster!='down') ? true : false;


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


if (!empty($projectsDropdown))
{
	$key=array_key_first($projectsDropdown);
	$selected=(empty($selected_project) || !isset($projectsDropdown[$selected_project]) ) ? $projectsDropdown[$key] : $selected_project;
}



?>


<div class='title row'>
	
	<div class="col-md-10">
		
		<h2 class="title-div headers"><?= Html::encode($this->title) ?></h2><div class="mpi-indicator" title="This software uses OpenMPI">OpenMPI&nbsp;<i class="fa fa-rocket" aria-hidden="true"></i></div>
		
	</div>



	<div class="col-md-2 back-btn">
		<?= Html::a("$back_icon Available Software", ['/software/index'], ['class'=>'btn btn-default']) ?>
	</div>


</div>

<div class="row">
	
</div>



<div class="site-software">
<?php
	ActiveForm::begin($form_params); 

?>




<div class="row">&nbsp;</div>
<div class="row">&nbsp;</div>



<div class="row" style="text-align: center;">

<div class="col-md-12">
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
	if(!empty($iocontMount))
	{

?>
	
	<div class="row">
	<div class="col-md-12">	<h3>Input/Output directory <i class="fa fa-question-circle" style="font-size:20px;" title="Select a folder to mount to the <?=$iocontMount?> directory in the container.")> </i></h3> </div>
	</div>


	<div class="row">
	<div class="col-md-12">		

				<?=Html::textInput('iosystemmount',$iosystemMount,['id' => 'iosystemmount','class'=>'mount-field','readonly'=>true,])?>
	            <?=Html::a("$select_icon Select",'javascript:void(0);',['class'=>'select-mount-button btn btn-success btn-md','disabled'=>($commandsDisabled)])?>
				<?=Html::a("$clear_icon Clear",'javascript:void(0);',['class'=>'clear-mount-button btn btn-danger btn-md','disabled'=>($commandsDisabled)])?>
				
	</div>
						
	</div>
<?php
	}
	else
	{
		if ( (empty($icontMount)) && (empty($ocontMount)) )
		{
?>
<br>


	<div class="alert alert-success row" role="alert">
		Based on the provided metadata, this docker image does not require any input/output mountpoint.
	</div>

	<?php
		}
		else
		{
			if (!empty($icontMount))
			{
	?>

	<div class="row">
	<div class="col-md-12">	<h3>Input directory <i class="fa fa-question-circle" style="font-size:20px" title="Select a folder to mount to the <?=$icontMount?> directory in the container.")> </i></h3> </div>
	</div>
	<div class="row">
	<div class="col-md-12">		

				<?=Html::textInput('isystemmount',$isystemMount,['id' => 'isystemmount','class'=>'mount-field','readonly'=>true,])?>
	            <?=Html::a("$select_icon Select",'javascript:void(0);',['class'=>'select-mount-button btn btn-success btn-md','disabled'=>($commandsDisabled)])?>
				<?=Html::a("$clear_icon Clear",'javascript:void(0);',['class'=>'clear-mount-button btn btn-danger btn-md','disabled'=>($commandsDisabled)])?>
	</div>
						
	</div>

	<?php
			}
			if (!empty($ocontMount))
			{
	?>
	<div class="row">
	<div class="col-md-7">	<h3>Output directory <i class="fa fa-question-circle" style="font-size:20px" title="Select a folder to mount to the <?=$ocontMount?> directory in the container.")> </i></h3> </div>
	</div>
	<div class="row">
	<div class="col-md-7">		

				<?=Html::textInput('osystemmount',$osystemMount,['id' => 'osystemmount','class'=>'mount-field','readonly'=>true,])?>
	            <?=Html::a("$select_icon Select",'javascript:void(0);',['class'=>'select-mount-button btn btn-success btn-md','disabled'=>($commandsDisabled)])?>
				<?=Html::a("$clear_icon Clear",'javascript:void(0);',['class'=>'clear-mount-button btn btn-danger btn-md','disabled'=>($commandsDisabled)])?>
	</div>
						
	</div>

	<?php
			}
		}

	}
?>
	<?=Html::hiddenInput('selectmounturl',Url::to(['software/select-mountpoint','username'=>$username]) ,['id'=>'selectmounturl'])?>
<?php
	if ($mountExistError)
	{
	?>
<div class="row">One of the folders selected as a mountpoint in the previous run does not exist anymore.<br /> 
					Please select another folder.</div>
</div>
<?php
	}
?>

<?php
/*
 * This is the non active form, that does not really submit.
 * The content of the fields is concatenated with JS, pasted
 * in the command box and the active form is submitted.
 */
?>




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
<?=Html::hiddenInput('cluster', $cluster,['id'=>'hidden_cluster_input'])?>
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
<div class="col-md-12"><h3>Arguments</h3></div>
</div> 
<?php

	$default_icon='<i class="fas fa-magic"></i>';
	$default_title='Fill field with default value.';
	$index=0;
	foreach ($fields as $field)
	{
	?>
		<div class="row" style="text-align: center;">
			<div class="col-md-6" style="text-align: right;"><?=Html::label($field->name,null,[])?></div>
	<?php
			if ($field->field_type!='boolean')
			{
	?>

				<div class="col-md-5" style="text-align: left;">
					<?=Html::textInput('field-' . $index,$field->value,['readonly'=>$commandsDisabled,'class'=>$commandBoxClass, 'id'=>'field-' . $index])?>
					<?=(($field->field_type!='file') && (!empty($field->default_value))) ? Html::a($default_icon,'javascript:void(0);',['id'=>'default-values', 'class'=>'btn btn-basic btn-default-values','title'=>$default_title]) : ''?>
				</div>
			   	<?=Html::hiddenInput('default_field_values[]',$field->default_value,['readonly'=>true, 'class'=>'hidden_default_value'])?>

			<?php
			}
			else
			{
			?>
				<div class="col-md-5" style="text-align: left;">
					<?=Html::checkbox('field-' . $index,$field->value,['readonly'=>$commandsDisabled,'class'=>$commandBoxClass, 'id'=>'field-' . $index, 'uncheck'=>"0"])?>
				</div>
			<?php
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
	<div class="col-md-12"><h3>Arguments</h3></div>
	</div> 

	<?php
		echo '<div class="alert alert-success row" role="alert">';
		echo "Based on the provided CWL description, this docker image does not require arguments.";
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





<!-- <div class="col-md-5 project-quotas-div rcorners">

<div class="row headers">
<h3 id="centerheaders"><b>Job resources</b></h3>
</div>




<div class="row">&nbsp;</div>

<div class="row">
	<b class="quotas-line">Relevant Project:</b>&nbsp; <?=$project?> (<?=$quotas['num_of_jobs']-$jobUsage?> remaining jobs)&nbsp;<?=Html::a('Change project',['software/index'])?>
</div>
<div class="row">&nbsp;</div>




<div class="row">
	<b class="quotas-line">No. of cores:</b> &nbsp; <?=Html::textInput('cores',$maxCores,['id' => 'cores','readonly'=>$commandsDisabled,'class'=>"$commandBoxClass inputbox"])?> &nbsp; out of <?=$quotas['cores']?>
</div>
<div class="row">&nbsp;</div>
<div class="row">
	<b class="quotas-line">No. of Open MPI processes:</b> &nbsp; <?=Html::textInput('processes',$processes,['id' => 'processes','readonly'=>$commandsDisabled,'class'=>"$commandBoxClass inputbox"])?>
</div>
<div class="row">&nbsp;</div>
<div class="row">
	<b class="quotas-line">No. of processes per Open MPI node (max 7):</b> &nbsp; <?=Html::textInput('pernode',$pernode,['id' => 'pernode','readonly'=>$commandsDisabled,'class'=>"$commandBoxClass inputbox"])?> 
</div>
<div class="row">&nbsp;</div>
<div class="row">
	<b class="quotas-line">Memory (in GBs) per node:</b> &nbsp; <?=Html::textInput('memory',$maxMem,['id' => 'memory','readonly'=>$commandsDisabled,'class'=>"$commandBoxClass inputbox"])?> &nbsp; out of <?=$quotas['ram']?> 
</div>

</div>


</div>

 -->









</div>





<?php
ActiveForm::end();
/*
 * Run, Run example and Cancel buttons.
 */
$classButtonHidden='';
if ($mountExistError)
{
	$classButtonHidden='hidden-element';
}

$play_icon='<i class="fas fa-play"></i>';
$instructions_icon='<i class="fa fa-file aria-hidden="true"></i>';

?>

<!-- <div class="run-button-container"> <i class="fa fa-play-circle", style="font-size:30px;color:green;background-color:white">
  <?=Html::a('Run','javascript:void(0);',['id'=>'software-start-run-button', 'style'=>"color: rgb(0,200,0)"])?> 
  </i>

	
</div> -->

<div class="row">
	<div class="run-button-container col-md-offset-4 col-md-1" style="text-align: right;"><?=Html::a("$play_icon Run",'javascript:void(0);',['id'=>'software-start-run-button', 'class'=>"btn    btn-success btn-md $classButtonHidden",'disabled'=>($commandsDisabled)])?></div>
<?php
if(!empty($icontMount) || !empty($ocontMount) || !empty($iocontMount))
{

?>

	<div class="run-button-container col-md-2" style="text-align: center;"><?=Html::a("$play_icon Run example",'javascript:void(0);',['id'=>'software-run-example-button', 'class'=>"btn btn-success btn-md",'disabled'=>((!$hasExample) || $commandsDisabled)])?></div>
	<div class="instructions col-md-1" style="margin-right: 55px; padding-left: 10px;"><?=Html::a("$instructions_icon Instructions",null,['id'=>'software-instructions', 'data-toggle'=>'modal', 
												'data-target'=>"#per", 'class'=>'btn btn-secondary'])?></div>

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

	<div class="add-example-link col-md-offset-5 col-md-2 <?=$addExampleHidden?>" style="text-align: center;"><?=Html::a('Add example',['/software/add-example','name'=>$name, 'version' =>$version],['id'=>'software-add-example-button', 'class'=>'btn btn-link'])?></div>
	

<?php

	}
}
$cancel_icon='<i class="fas fa-times"></i>';
?>


	<div class="cancel-button-container col-md-1"><?=Html::a("$cancel_icon Cancel ",'javascript:void(0);',['id'=>'software-cancel-button', 'class'=>'btn btn-danger'])?></div>
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
		echo $error;
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
<div id="pod-logs">
</div>
<?php
	/*
	 * If a pod is running, then register the JS file
	 * with the AJAX that updates the logs div. At the beginning
	 * keep the status at "Initializing".
	 */
	
	if ($commandsDisabled)
	{
		echo "<div id='initial-status'>";
		echo "<h3>Runtime Info:</h3>";
		echo "<b>Status:</b> <div class='status-init'>Initializing cluster...please do not close this window</div><br />";
		echo $this->registerJsFile('@web/js/software-mpi/logs.js', ['depends' => [\yii\web\JqueryAsset::className()]] );
		
	}
	/*
	 * Uncomment this line to be able to refresh the logs.js file
	 */
	// echo $this->registerJsFile('@web/js/software-mpi/logs.js', ['depends' => [\yii\web\JqueryAsset::className()]] );
?>
<br />

</div>





</div>

<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js"></script>
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>

<div class="modal fade" id="per" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content" style="width:450px;">
      <div class="modal-header">
        <h5 class="modal-title text-center" id="exampleModalLabel">Instructions</h5>
      </div>
      <div class="modal-body">
      	<div class="row">
           <div class="col-md-12 text-center" style="padding-bottom: 10px;"><?=$software_instructions?></div>
        </div>
        <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"> Close </i></button>
        </div>
   	 </div>
  	</div>
  </div>
</div>
