<?php 

use app\components\ArgumentsWidget;
use app\components\JobResourcesWidget;

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Form;
use yii\bootstrap\Button;
use yii\captcha\Captcha;
use yii\widgets\ActiveForm;
use app\components\InstructionsModal;
use app\components\Headers;



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


$back_icon='<i class="fas fa-arrow-left"></i>';
$clear_file_icon='<i class="fas fa-times"></i>';
$instructions_icon='<i class="fa fa-file aria-hidden="true"></i>';

Headers::begin() ?>
<?php echo Headers::widget(
['title'=>$this->title, 
    'buttons'=>
    [
        ['fontawesome_class'=>$back_icon,'name'=> 'Available Software', 'action'=>['/software/index'],
        'options'=>['class'=>'btn btn-default'], 'type'=>'a'], 
        
    ],
])
?>
<?Headers::end()?>





<?php 
ActiveForm::begin($form_params);



ArgumentsWidget::show(Yii::$app->request->absoluteUrl, $form_params, $name, $version, $jobid, $software_instructions,
            $errors, $runErrors, $podid='', $machineType='',
            $fields,$isystemMount, $osystemMount,
            $iosystemMount, $example, $hasExample,
            $username,$icontMount,$ocontMount,
            $iocontMount,$mountExistError,
            $superadmin,$jobUsage,$quotas,
            $maxMem,$maxCores,$project, $commandsDisabled, $commandBoxClass, $cluster, $outFolder='', $type);





JobResourcesWidget::show(Yii::$app->request->absoluteUrl, $form_params, $name, $version, $jobid, $software_instructions,
            $errors, $runErrors, $podid='', $machineType='',
            $fields,$isystemMount, $osystemMount,
            $iosystemMount, $example, $hasExample,
            $username,$icontMount,$ocontMount,
            $iocontMount,$mountExistError,
            $superadmin,$jobUsage,$quotas,
            $maxMem,$maxCores,$project, $commandsDisabled, $commandBoxClass, $processes, $pernode, $outFolder='', $type);




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

<div class="name hidden"><?=$name?></div>
<div class="version hidden"><?=$version?></div>
<?php
InstructionsModal::addModal($name, $version, $software_instructions);
?>





