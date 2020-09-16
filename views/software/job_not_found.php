<?php


/**
 * This view file prints the history of software runs made by a user
 * 
 * @author: Kostis Zagganas
 * First version: March 2019
 */

use yii\helpers\Html;
use yii\widgets\LinkPager;

$this->title="Job not found";

// $approve_icon='<i class="fas fa-check"></i>';
// $reject_icon='<i class="fas fa-times"></i>';
$back_icon='<i class="fas fa-arrow-left"></i>';
$logs_icon='<i class="fas fa-cloud-download-alt"></i>';

/*
 * Users are able to view the name, version, start date, end date, mountpoint 
 * and running status of their previous software executions. 
 */
?>
<div class='title row'>
	<div class="col-md-11">
		<!-- <h1><?= Html::encode($this->title) ?></h1> -->
	</div>
	<div class="col-md-1 float-right">
		<?= Html::a("$back_icon Back", ['software/history'], ['class'=>'btn btn-default']) ?>
	</div>
</div>

<div class="row"><div class="col-md-12 text-center"><h3>We could not find a job with an id <?=$jobid?> in our system.</h3></div></div>


 

