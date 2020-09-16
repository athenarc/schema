<?php
/**
 * This view file prints the list of available software
 * 
 * @author: Kostis Zagganas
 * First version: Dec 2018
 */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Form;
use yii\bootstrap\Button;
use yii\captcha\Captcha;
use app\components\SoftIndexButton;
use webvimark\modules\UserManagement\models\User;
use app\components\SoftDescrModal;



/*
 * Add stylesheet
 */

echo Html::cssFile('@web/css/software/index.css');
$this->registerJsFile('@web/js/software/index.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

if (empty($error))
{
	if (!empty($success))
	{
		echo '<div class="alert alert-success row" role="alert">';
		echo $success;
		echo '</div>';
	}
	if (!empty($warning))
	{
		echo '<div class="alert alert-warning row" role="alert">';
		echo $warning;
		echo '</div>';
	}

}
else
{
	echo '<div class="alert alert-danger row" role="alert">';
	echo $error;
	echo '</div>';

}



/*
 * Add button for new software upload
 */ 
$this->title = 'Available Software';
// $this->params['breadcrumbs'][] = $this->title;

$softwareAdd='<i class="fas fa-plus"></i>&nbsp;New software';
$projectAdd='<i class="fas fa-plus"></i>&nbsp;New project';


// $softwareAdd.='</div>';

$runIcon='<i class="fas fa-play"></i>';
$editIcon='<i class="fas fa-edit"></i>';
$deleteIcon='';

if (!empty($projectsDropdown))
{
	$key=array_key_first($projectsDropdown);
	$project_selected=(empty($selected_project) || !isset($projectsDropdown[$selected_project]) ) ? $projectsDropdown[$key] : $selected_project;
	$project_name=trim(explode('(',$project_selected)[0]);
	$dropdownLabel='Resources from project:';
	// print_r($project_name);
	// exit(0);
}
else
{
	$dropdownLabel='No active projects available.';
}

/*
 * Add software with "Run" button
 */

?>

<div class="row">
			<div class="col-md-1"><?=Html::a($softwareAdd,['software/upload'],['class'=>'btn btn-primary'])?>	</div>
			<div class="col-md-offset-3 col-md-8 text-right"><span class="project-dropdown-label"><?=$dropdownLabel?></span>&nbsp;
<?php
	if (!empty($projectsDropdown))
	{
?>
	
		<?=Html::dropDownList('dropdown', $project_selected, $projectsDropdown, ['class'=>'project-dropdown']) ?>&nbsp;
<?php		
	}
?>
		<?= Html::a($projectAdd, "https://egci-beta.imsi.athenarc.gr/index.php?r=project%2Fnew-request", ['class' => 'btn btn-secondary create-project-btn'])?></div>
	</div>

<div class="row">&nbsp;</div>


<div id="software-table">

<?php
	/*
	 * Add software table heading
	 */
	if (!empty($software))
	{

?>
	<div class="row software-header">
		<div class="col-md-3">Software Name</div>
		<div class="col-md-2">Version</div>
		<div class="col-md-2">Uploader</div>
		<div class="col-md-4"></div>
	</div>
	
<?php
	}
	else
	{
?>
	<div class="row">
		<div class="col-md-12 text-center"><h2>No images available.</h2></div>
	</div>
	<div class="row">
		<div class="col-md-12 text-center"><?=Html::img('@web/img/empty-white-box.svg', ['width'=>'150', 'height'=>'150'])?></h2></div>
	</div>
	<!-- <div class="row">
		<div class="col-md-12 text-center">Icon made by <a href="https://www.flaticon.com/authors/freepik" title="Freepik">Freepik</a> from <a href="https://www.flaticon.com/"             title="Flaticon">www.flaticon.com</a></div>
	</div> -->
	
<?php
	}

/*
 * Add software name, version, image location and "Run" button.
 * If the software belongs to the user or the user is superadmin
 * then add an "Edit" button.
 */


$publicIcon='<i class="fas fa-lock-open" title="This software is publicly available"></i>';
$privateIcon='<i class="fas fa-lock" title="This software is private"></i>';

// print_r($software);
// exit(0);
foreach ($software as $name=>$uploader)
{
	foreach($uploader as $upl=>$versions)
	{
		$uploaded_by=explode('@', $upl)[0];
		reset($versions);
		$first_key = key($versions);
		$visibility=explode('|',$first_key)[1];
		$lockIcon=($visibility=='public') ? $publicIcon : $privateIcon;
		

?>

		<div class="row software-row-$name">
			<div class="col-md-3 software-name-column"><div class="software-lock"><?=$lockIcon?></div><div class="software-name"><?=$name?></div><div class="software-description"><i class="fa fa-question-circle"></i></div></div>
			<div class="col-md-2 software-versions"><?=Html::dropDownList('versions_drop_down',$versions[$first_key],$versions,['class'=>'versionsDropDown align-middle'])?></div>
			<div class="col-md-2 software-image-uploader"><span class="align-middle"><?= $uploaded_by?></span></div>


<?php
	
		if((empty($projectsDropdown)) and !(User::hasRole("Admin",$superAdminAllowed=true)) )
		{
			$disabledClass='disabled';
		}
		else
		{
			$disabledClass='';
		}

?> 
			<div class="col-md-4 software-button-container $disabledClass"><?=SoftIndexButton::button('run',Url::to(['software/run','name'=>$name, 'version'=>$versions[$first_key],'project'=>$project_name]),$name)?>&nbsp;
				<?=( ($upl==$user) || ($superadmin==1) ) ? SoftIndexButton::button('edit',Url::to(['software/edit-software','name'=>$name, 'version'=>$versions[$first_key]]),$name) : ''?>&nbsp;
				<?=( ($upl==$user) || ($superadmin==1) ) ? SoftIndexButton::button('delete') : ''?>
			</div>
			<?=Html::hiddenInput('hiddenUrl',Url::base('http'),['class'=>'hidden_url']);?>
		</div>

		<div class="row">&nbsp;</div>
		<!-- <div class="row">&nbsp;</div>
		<div class="row">&nbsp;</div> -->
<?php
	}


}
?>
<?php
foreach ($descriptions as $soft)
{
	SoftDescrModal::addModal($soft['name'], $soft['version'], $soft['description']);
}


?>
</div>
