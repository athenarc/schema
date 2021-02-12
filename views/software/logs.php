<?php
/************************************************************************************
 *
 *  Copyright (c) 2018 Thanasis Vergoulis & Konstantinos Zagganas &  Loukas Kavouras
 *  for the Information Management Systems Institute, "Athena" Research Center.
 *  
 *  This file is part of SCHeMa.
 *  
 *  SCHeMa is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *  
 *  SCHeMa is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  
 *  You should have received a copy of the GNU General Public License
 *  along with Foobar.  If not, see <https://www.gnu.org/licenses/>.
 *
 ************************************************************************************/
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
<?php
if (Yii::$app->params['standalone']==false)
{
?>
	<div class="project-resources"><b>Resources from project:</b> <?=$project?></div>
<?php
}
else
{
?>
	<div class="project-resources"></div>
<?php	
}
?>
<div><b>Type of machine:</b> <?=$machineType?></div>
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
			<div class="col-md-8"><?=Html::img('@web/img/schema-01-loading.gif',['class'=>'float-right run-gif-img'])?></div>
		</div>
		<div class="container-logs">
		<?php
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