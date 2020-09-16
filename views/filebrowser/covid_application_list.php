<?php


/**
 * This view file prints the history of software runs made by a user
 * 
 * @author: Kostis Zagganas
 * First version: March 2019
 */

use yii\helpers\Html;
use yii\widgets\LinkPager;
use yii\bootstrap\NavBar;
use yii\bootstrap\Nav;
use yii\helpers\Url;  


echo Html::CssFile('@web/css/project/request-list.css');

$this->title="COVID-19 dataset applications";


/*
 * Users are able to view the name, version, start date, end date, mountpoint 
 * and running status of their previous software executions. 
 */


?>

	<div class='title row'>
		<div class="col-md-12">
			<h1><?= Html::encode($this->title) ?></h1>
		</div>
	</div>

	<div class="row">&nbsp;</div>


<?php


?>
  	

<?php
if (!empty($applications))
{
	
?>
<div class="applications-table, table-responsive">
                  
			<table class="table">
				<thead>
					<tr>
						<th class="col-md-3" scope="col">Dataset Name</th>
						<th class="col-md-3" scope="col">E-mail</th>
						<th class="col-md-2" scope="col">Submitted on</th>
						<th class="col-md-2" scope="col">Submitted by</th>						
						<th class="col-md-2" scope="col">&nbsp;</th>
					</tr>
				</thead>
				<tbody>
<?php



foreach ($applications as $app)
{
	$view_icon='<i class="fas fa-eye"></i>';
	// $button_link=$button_link=$button_links[$res['project_type']];
	$user=explode('@',$app->username)[0];
	


?>
				<tr>
					<td class="col-md-3 align-middle"><?=$app->name?></td>
					<td class="col-md-3 align-middle"><?=$app->email?></td>
					<td class="col-md-2 align-middle"><?=date("F j, Y, H:i:s",strtotime($app->submission_date))?></td>
					<td class="col-md-2 align-middle"><?=$user?></td>
					<td class="col-md-2 align-middle"><?=Html::a("$view_icon Details",['/filebroser/covid-application-details','id'=>$app->id],['class'=>'btn btn-primary btn-md'])?></td>
				</tr>

<?php
}
?>
				</tbody>
			</table>

		</div>


<?php
}
else
{
?>
		<div class="col-md-12"><h3 class="empty-message">There are no recorded applications.</h3></div>
<?php
}
?>
	</div><!--row-->
	<div class="row">&nbsp;</div>
	<div class="row"><div class="col-md-12"><div class="float-right"><?= LinkPager::widget(['pagination' => $pages]) ?></div></div></div>

</div><!--container-fluid-->