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



/*
 * Add stylesheet
 */

echo Html::cssFile('@web/css/workflow/index.css');
$this->registerJsFile('@web/js/workflow/index.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

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
$this->title = 'Available Workflows';
// $this->params['breadcrumbs'][] = $this->title;

$workflowAdd='<i class="fas fa-plus"></i>&nbsp;New workflow';
$projectAdd='<i class="fas fa-plus"></i>&nbsp;New project';


// $softwareAdd.='</div>';

$runIcon='<i class="fas fa-play"></i>';
$editIcon='<i class="fas fa-edit"></i>';
$visualizeIcon='<i class="fas fa-eye"></i>';
$deleteIcon='';

// if (!empty($projectsDropdown))
// {
// 	$key=array_key_first($projectsDropdown);
// 	$project_selected=(empty($selected_project) || !isset($projectsDropdown[$selected_project]) ) ? $projectsDropdown[$key] : $selected_project;
// 	$project_name=trim(explode('(',$project_selected)[0]);
// 	$dropdownLabel='Resources from project:';
// 	// print_r($project_name);
// 	// exit(0);
// }
// else
// {
// 	$dropdownLabel='No active projects available.';
// 	$project_selected='';
// 	$project_name='';
// }

/*
 * Add software with "Run" button
 */

?>

<div class="row">
	<div class="col-md-5"><?=Html::a($workflowAdd,['workflow/upload'],['class'=>'btn btn-primary'])?>	</div>
</div>

<div class="row">&nbsp;</div>
<div class="row">&nbsp;</div>
<div class="row">&nbsp;</div>
<div class="row">&nbsp;</div>


<div id="software-table">

<?php
	/*
	 * Add software table heading
	 */
	if (!empty($workflows))
	{

?>
	<div class=" table-responsive">
		<table class="table table-striped">
		<thead class="software-header">
			<th class="col-md-3">Software Name</th>
			<th class="col-md-2">Version</th>
			<th class="col-md-3">Uploader</th>
			<th class="col-md-4"></th>
		</thead>
		<tbody>
	
<?php
	}
	else
	{
?>		
	<div class="row">
		<div class="col-md-12 text-center"><h2>No workflows available.</h2></div>
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
foreach ($workflows as $name=>$uploader)
{
	foreach($uploader as $upl=>$versions)
	{
		
		$uploaded_by=explode('@', $upl)[0];
		reset($versions);
		$first_key = key($versions);
		$id=$nameversion_to_id[$name][$versions[$first_key]];
		$visibility=explode('|',$first_key)[1];
		$lockIcon=($visibility=='public') ? $publicIcon : $privateIcon;
		// $image_location=$images[$name][$versions[$first_key]][1];
		// $original_image=$images[$name][$versions[$first_key]][0];
		// $image_location=$image_location ? 'dockerHub: ' : 'localImage: ';
		// print_r($upl);
		// exit(0);
		$indicatorList=$indicators[$name][$versions[$first_key]];
		$runLink=Url::to(['workflow/run','name'=>$name, 'version'=>$versions[$first_key],'project'=>$_SESSION['selected_project']]);

		$visualizeLink=Url::to(['workflow/visualize','name'=>$name, 'version'=>$versions[$first_key]]);
		

?>
		
		<tr class="software-row-$name">
			<td class="software-name-column"><div class="software-lock"><?=$lockIcon?></div><div class="software-name"><?=$name?></div><div class="software-description"><i class="fa fa-question-circle"></i></div><div class="indicators-div"><?=SoftwareIndicatorList::getIndicators($indicatorList)?></div></td>
			<td class="software-versions"><?=Html::dropDownList('versions_drop_down',$versions[$first_key],$versions,['class'=>'versionsDropDown align-middle'])?></td>
			
			<td class="software-image-uploader"><span class="align-middle"><?= $uploaded_by?></span></td>
			

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
			<td class="software-button-container $disabledClass">
				<?=Html::a(" <span style='color:white'> $visualizeIcon Visualize </span>",null, ['id'=>'software-instructions', 'data-toggle'=>'modal', 
    												'data-target'=>"#vis$id", 'class'=>'btn btn-primary']);?>  &nbsp;
				<?=SoftIndexButton::button('run',$runLink,$name)?>&nbsp;
				<?=( ($upl==$user) || ($superadmin==1) ) ? SoftIndexButton::button('edit',Url::to(['workflow/edit-workflow','name'=>$name, 'version'=>$versions[$first_key]]),$name) : ''?>&nbsp;
				<?=( ($upl==$user) || ($superadmin==1) ) ? SoftIndexButton::button('delete') : ''?>
			</td>
			<?=Html::hiddenInput('hiddenUrl',Url::base('http'),['class'=>'hidden_url']);?>
		</tr>

		<!-- <div class="row">&nbsp;</div>
		<div class="row">&nbsp;</div> -->
<?php
	}


}
if (!empty($workflows))
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
				$link=Url::to(['workflow/run','name'=>$name]);
				$id='hidden-run-link-' . $name . '-' . $version;
				echo Html::hiddenInput('$id',$link,['id'=>$id]);
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
				$link=Url::to(['workflow/edit-workflow','name'=>$name,'version'=>$version]);
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

<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js"></script>
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
<?php
foreach ($workflows as $name=>$uploader)
{
	foreach($uploader as $upl=>$versions)
	{
		
		$first_key = key($versions);
		$id=$nameversion_to_id[$name][$versions[$first_key]];
		?>
	    <div class="modal fade" id="vis<?=$id?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	      <div class="modal-dialog" role="document">
	        <div class="modal-content" style="width:650px;">
	          <div class="modal-header">
	            <h5 class="modal-title text-center" id="exampleModalLabel">Workflow</h5>
	          </div>
	          <div class="modal-body">
	          	<div class="row">
	               <div class="col-md-12 text-center" style="padding-bottom: 10px;">
	               	<?=empty($id_to_vis[$id]) ? "Workflow visualizaton not available" : Html::img("@web/img/workflows/$id_to_vis[$id]", ['width'=>'600px','height'=> '400px'])?>
	               </div>
	            </div>
	            <div class="modal-footer">
	            <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"> Close </i></button>
	            </div>
	       	 </div>
	      	</div>
	      </div>
	    </div>
<?php
	}	
}?>
