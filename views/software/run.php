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

echo Html::CssFile('@web/css/software/run.css');
//Yii::$app->getView()->
$this->registerJsFile('@web/js/software/run-index.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
//$this->registerJsFile('@web/js/workflow/index.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

$title = "New job ($name v.$version) ";

$commandsDisabled= ($podid!='') ? true : false;
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
?>


<div class='title row'>
	<div class="col-md-10 headers"><h2><?= Html::encode($title) ?></h2></div>
	<div class="col-md-2 back-btn"><?= Html::a("$back_icon Available Software", ['/software/index'], ['class'=>'btn btn-default']) ?></div>
</div>


<?php 
ActiveForm::begin($form_params);


ArgumentsWidget::show(Yii::$app->request->absoluteUrl, $form_params, $name, $version, $jobid, $software_instructions,
            $errors, $runErrors, $podid, $machineType,
            $fields,$isystemMount, $osystemMount,
            $iosystemMount, $example, $hasExample,
            $username,$icontMount,$ocontMount,
            $iocontMount,$mountExistError,
            $superadmin,$jobUsage,$quotas,
            $maxMem,$maxCores,$project, $commandsDisabled, $commandBoxClass, $cluster='', $outFolder='', $type=1);

JobResourcesWidget::show(Yii::$app->request->absoluteUrl, $form_params, $name, $version, $jobid, $software_instructions,
            $errors, $runErrors, $podid, $machineType,
            $fields,$isystemMount, $osystemMount,
            $iosystemMount, $example, $hasExample,
            $username,$icontMount,$ocontMount,
            $iocontMount,$mountExistError,
            $superadmin,$jobUsage,$quotas,
            $maxMem,$maxCores,$project, $commandsDisabled, $commandBoxClass, $processes='', $pernode='', $outFolder='', $type=1);   


ActiveForm::end();  
?>


		<div id="error-report">
		    <?php 
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
		if ($podid!='')
	    {
	        echo "<div id='initial-status'>";
			echo "<h3>Runtime Info:</h3>";
			echo "<b>Status:</b> <div class='status-init'>Initializing</div><br />";
	        echo $this->registerJsFile('@web/js/software/logs.js', ['depends' => [\yii\web\JqueryAsset::className()]] );
	        
	    }?>
		<br />
	</div>
</div>    




