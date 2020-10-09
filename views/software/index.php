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
use app\components\SoftwareIndicatorList;

// print_r($_SESSION['selected_project']);
// exit();

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

$softwareAdd='<i class="fas fa-plus"></i>&nbsp;New image';
$softwareAddExisting='<i class="fas fa-plus"></i>&nbsp;Existing image';
$imageAdd='<i class="fas fa-plus"></i>&nbsp;Image Request';


// $softwareAdd.='</div>';

$runIcon='<i class="fas fa-play"></i>';
$editIcon='<i class="fas fa-edit"></i>';
$deleteIcon='';

/*
 * Add software with "Run" button
 */

?>


<div class="row">
	<div class="col-md-5">
		<span><?=Html::a($softwareAdd,['software/upload'],['class'=>'btn btn-primary'])?></span>&nbsp;<span><?=Html::a($softwareAddExisting,['software/upload-existing'],['class'=>'btn btn-secondary'])?></span>
		<?=Html::a($imageAdd,['software/image-request'],['class'=>'btn btn-secondary'])?></span>
	</div>
</div>



<div class="row">&nbsp;</div>
<div class="row">&nbsp;</div>


<div id="software-table">

<?php
	/*
	 * Add software table heading
	 */
	if (!empty($software))
	{

?>
	<div class=" table-responsive">
		<table class="table table-striped">
		<thead class="software-header">
			<th class="col-md-3">Software Name</th>
			<th class="col-md-1">Version</th>
			<th class="col-md-3">Image</th>
			<th class="col-md-1">Uploader</th>
			<th class="col-md-3"></th>
		</thead>
		<tbody>
	
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
		$image_location=$images[$name][$versions[$first_key]][1];
		$original_image=$images[$name][$versions[$first_key]][0];
		$image_location=$image_location ? 'dockerHub: ' : 'localImage: ';
		// print_r($upl);
		// exit(0);
		$indicatorList=$indicators[$name][$versions[$first_key]];
		$runLink=(isset($indicatorList['mpi'])) ? Url::to(['software-mpi/run','name'=>$name, 'version'=>$versions[$first_key],'project'=>$_SESSION['selected_project']]) : Url::to(['software/run','name'=>$name, 'version'=>$versions[$first_key],'project'=>$_SESSION['selected_project']]);
		

?>
		
		<tr class="software-row-$name">
			<td class="col-md-3 software-name-column"><div class="software-lock"><?=$lockIcon?></div><div class="software-name"><?=$name?></div><div class="software-description"><i class="fa fa-question-circle"></i></div><div class="indicators-div"><?=SoftwareIndicatorList::getIndicators($indicatorList)?></div></td>
			<td class="col-md-1 software-versions"><?=Html::dropDownList('versions_drop_down',$versions[$first_key],$versions,['class'=>'versionsDropDown align-middle'])?></td>
			<td class="col-md-3 software-image"><span class="align-middle image-field"><b><?=$image_location?></b><?=$original_image?></span></td>
			<td class="col-md-1 software-image-uploader"><span class="align-middle"><?= $uploaded_by?></span></td>
			

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
			<td class="col-md-3 software-button-container $disabledClass"><?=SoftIndexButton::button('run',$runLink,$name)?>&nbsp;
				<?=( ($upl==$user) || ($superadmin==1) ) ? SoftIndexButton::button('edit',Url::to(['software/edit-software','name'=>$name, 'version'=>$versions[$first_key]]),$name,'software') : ''?>&nbsp;
				 <?=( ($upl==$user) || ($superadmin==1) ) ? SoftIndexButton::button('delete') : ''?>
			</td>
			<?=Html::hiddenInput('hiddenUrl',Url::base('http'),['class'=>'hidden_url']);?>
		</tr>

		<!-- <div class="row">&nbsp;</div>
		<div class="row">&nbsp;</div> -->
<?php
	}


}
if (!empty($software))
{
?>
		</tbody>
		</table>
	</div>

<?php
}
?>
</div>

<?php
foreach ($descriptions as $soft)
{
	SoftDescrModal::addModal($soft['name'], $soft['version'], $soft['description']);
}

?>

<div id="hidden_run_links">
<?php
	foreach ($indicators as $name=>$versions)
	{
		foreach ($versions as $version=>$versionIndicators)
		{
			if (isset($versionIndicators['mpi']))
			{
				$link=Url::to(['software-mpi/run','name'=>$name]);
				$id='hidden-run-link-' . $name . '-' . $version;
				echo Html::hiddenInput('$id',$link,['id'=>$id]);
			}
			else
			{
				$link=Url::to(['software/run','name'=>$name]);
				$id='hidden-run-link-' . $name . '-' . $version;
				echo Html::hiddenInput('$id',$link,['id'=>$id]);
			}
		}
	}
?>
</div>

<div id="hidden_edit_links">
<?php
	foreach ($indicators as $name=>$versions)
	{
		foreach ($versions as $version=>$versionIndicators)
		{
			$link=Url::to(['software/edit-software','name'=>$name,'version'=>$version]);
			$id='hidden-edit-link-' . $name . '-' . $version;
			echo Html::hiddenInput('$id',$link,['id'=>$id]);
		}
	}
?>
</div>

<div id="hidden_indicators_div" class="invisible">
<?php
	foreach ($indicators as $name=>$versions)
	{
		foreach ($versions as $version=>$versionIndicators)
		{
			$id='hidden-indicators-'. $name . '-' . $version;
			$code=SoftwareIndicatorList::getIndicators($versionIndicators);
			$code="<div id=$id>" . $code . "</div>";
			echo $code;

		}
	}
?>
</div>

<div id="hidden_original_images" class="invisible">
<?php
	foreach ($images as $name=>$versions)
	{
		foreach ($versions as $version=>$image)
		{
			$location=$image[1];
			$imageName=$image[0];
			$location=$location ? 'dockerHub: '	: 'localImage: ';
			$id='hidden-image-' . $name . '-' . $version;
?>
			<div id="<?=$id?>"><b><?=$location?></b><?=$imageName?></div>
<?php
			
		}
	}
?>

</div>

