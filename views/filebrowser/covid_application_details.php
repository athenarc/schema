<?php


/**
 * This view file prints the history of software runs made by a user
 * 
 * @author: Kostis Zagganas
 * First version: March 2019
 */

use yii\helpers\Html;
use yii\widgets\LinkPager;

$this->title="Application details";

echo Html::cssFile('@web/css/project/project_details.css');

$approve_icon='<i class="fas fa-check"></i>';
$reject_icon='<i class="fas fa-times"></i>';
$back_icon='<i class="fas fa-arrow-left"></i>';

/*
 * Users are able to view the name, version, start date, end date, mountpoint 
 * and running status of their previous software executions. 
 */
?>
<div class='title row'>
	<div class="col-md-11">
		<h1><?= Html::encode($this->title) ?></h1>
	</div>
	<div class="col-md-1 float-right">
		<?= Html::a("$back_icon Back", ['/filebrowser/covid-list-applications'], ['class'=>'btn btn-default']) ?>
	</div>
</div>

<div class="row">&nbsp;</div>

<div class="table-responsive">
	<table class="table table-striped">
		<tbody>
			<tr>
				<th class="col-md-6 text-right" scope="col">Dataset Name</th>
				<td class="col-md-6 text-left" scope="col"><?= $application->name ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Dataset description</th>
				<td class="col-md-6 text-left" scope="col"><?= $application->description ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Link to dataset</th>
				<td class="col-md-6 text-left" scope="col"><?= Html::a($application->link,$application->link,['target'=>'_blank']) ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Correspondance e-mail</th>
				<td class="col-md-6 text-left" scope="col"><?= $application->email?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Submitted by</th>
				<td class="col-md-6 text-left" scope="col"><?= explode('@',$application->username)[0]  ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Submitted at</th>
				<td class="col-md-6 text-left" scope="col"><?= $start=date("F j, Y, H:i:s",strtotime($application->submission_date))  ?></td>
			</tr>
		</body>
	</table>
</div>
<div class="row">&nbsp;</div>
