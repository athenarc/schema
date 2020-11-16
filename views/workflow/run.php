<?php 

use app\components\ArgumentsWidget;
use app\components\JobResourcesWidget;

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
<?Headers::end()?>





<?php 
ActiveForm::begin($form_params);


ArgumentsWidget::show(Yii::$app->request->absoluteUrl, $form_params, $name, $version, $jobid, 
			$software_instructions=$workflow_instructions,
            $errors, $runErrors, $podid='', $machineType='',
            $fields,$isystemMount='', $osystemMount='',
            $iosystemMount='', $example, $hasExample,
            $username,$icontMount='',$ocontMount=1,
            $iocontMount='',$mountExistError='',
            $superadmin,$jobUsage,$quotas,
            $maxMem,$maxCores,$project, $commandsDisabled, $commandBoxClass, $cluster='', $outFolder, $type);

JobResourcesWidget::show(Yii::$app->request->absoluteUrl, $form_params, $name, $version, $jobid, 
		$software_instructions=$workflow_instructions,
            $errors, $runErrors, $podid='', $machineType='',
            $fields,$isystemMount='', $osystemMount='',
            $iosystemMount='', $example, $hasExample,
            $username,$icontMount='',$ocontMount=1,
            $iocontMount='',$mountExistError=1,
            $superadmin,$jobUsage,$quotas,
            $maxMem,$maxCores,$project, $commandsDisabled, $commandBoxClass, $processes='', $pernode='', $outFolder, $type);   


ActiveForm::end();  
?>





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
</div>

<div class="name hidden"><?=$name?></div>
<div class="version hidden"><?=$version?></div>
<?php
WorkflowVisualizeModal::addModal($name, $version, $visualize);
InstructionsModal::addModal($name, $version, $software_instructions);
?>








