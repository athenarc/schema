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

use Yii;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Form;
use yii\bootstrap\Button;
use yii\captcha\Captcha;
use yii\widgets\ActiveForm;
use yii\bootstrap4\Modal;
use app\components\WorkflowVisualizeModal;
use app\components\InstructionsModal;
use app\components\Headers;
use app\components\RunFormWidget;
use app\components\JobResourcesWidget;


echo Html::CssFile('@web/css/workflow/run.css');
$this->registerJsFile('@web/js/workflow/run-index.js', ['depends' => [\yii\web\JqueryAsset::className()]] );




  

  


$this->title = "Workflow ($name v.$version) ";

$commandsDisabled= ($jobid!='') ? true : false;
if($commandsDisabled)
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
$instructions_icon='<i class="fa fa-file aria-hidden="true"></i>';

Headers::begin() ?>
<?php echo Headers::widget(
['title'=>$this->title, 
	'buttons'=>
	[
		
		['fontawesome_class'=>$back_icon,'name'=> 'Available Workflows', 'action'=>['/workflow/index'],
		 'options'=>['class'=>'btn btn-default'], 'type'=>'a'] 
	],
])
?>
<?php Headers::end()?>





<?php 
ActiveForm::begin($form_params);
?>

<div class="site-software">
    <div class="row">&nbsp;</div>
    <div class="row">&nbsp;</div>
    <div class="row" style="text-align: center;">
    <div class="col-md-12">

<?php
    RunFormWidget::showOutputField($commandsDisabled,$outFolder,$username,$type);
    RunFormWidget::showHiddenFields($jobid,$name,$version,$example, $hasExample);
    RunFormWidget::showArguments($fields,$type,$commandBoxClass,$commandsDisabled);
    RunFormWidget::showResources($quotas,$maxCores,$maxMem,$commandsDisabled,$commandBoxClass);
    RunFormWidget::showRunButtons($superadmin,$username,$uploadedBy,$hasExample,$commandsDisabled,$type,$name,$version,$workflow_instructions,$visualize);


ActiveForm::end();  
?>


    <?=RunFormWidget::showErrors($errors, $runErrors)?>
<div id="pod-logs"></div>

<?php
	/*
	 * If a pod is running, then register the JS file
	 * with the AJAX that updates the logs div. At the beginning
	 * keep the status at "Initializing".
	 */
	
	if ($commandsDisabled)
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
</div>









