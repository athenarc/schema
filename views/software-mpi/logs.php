<?php
/**
 * View file that prints the logs of the running pod
 * 
 * @author: Kostis Zagganas
 * First version: Dec 2018
 */
use yii\helpers\Html;
	
	// echo Html::CssFile('@web/css/software.css');
?>
<h3>Runtime Info:</h3>
<div class="status-div col-md-3>">
	<div class="status-label"><b>Status:</b></div>
	<div id="status-value" class="<?= ($status=="Completed") ? "status-completed" : "status-running" ?> "><?=$status?></div>
</div>
<div class="status-div col-md-3>">
	<div class="status-label"><b>Running time:</b></div>
	<div id="exec-time-value" class="<?= ($status=="Completed") ? "status-completed" : "status-running" ?> "><?=$time?></div>
</div>


<?php
/*
 * Do not show logs while the container is being created
 */
if ($status!='ContainerCreating')
{
?>
	<div class="job-output">
		<div class="row">
			<div class="col-md-4"><h3 class="job-logs-header">Job output:</h3> </div>
			<div class="col-md-8"><?=Html::img('@web/img/schema-01-loading.gif',['class'=>'float-right run-gif-img running-logo'])?></div>
		</div>
		<div class="container-logs">
		<?php
		// print_r($logs);
		// exit(0);
		foreach ($logs as $log)
		{
			echo $log . "<br />";
		}

		?>
		</div>
	</div>
<?php
}
?>