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
</div>
<div class="status-div align-middle row" style="padding-left: 15px;">
		<span class="status-label"><b>Workflow status:</b></span>&nbsp;
		<span id="status-value" class="status-<?=$status?>"><?=$status?></span>
</div><br>
<div class="status-div align-middle row" style="padding-left: 15px;">
		<span class="status-label"><b>Running time:</b></span>&nbsp;
		<span id="exec-time-value" class="status-<?=$status?>"><?=$time?></span>
</div>
	<?php
	if (Yii::$app->params['standalone']==false)
	{
	?>
		<div class="project-resources row" style="padding-left: 15px;"><b>Resources from project:</b> &nbsp; <?=$project?></div>
	<?php	
	}
	else
	{
	?>
		<div class="project-resources row" style="padding-left: 15px;"></div>
	<?php
	}

	if ($status!='COMPLETE')
	{
	?>  <div class="col-md-6"><?=Html::img('@web/img/schema-01-loading.gif',['class'=>'float-right run-gif-img'])?></div>
	<?php	
	}
	else
	{
	?>
		<div class="col-md-6">&nbsp;</div>
	<?php	
	}
	?>

<div class="row">&nbsp;</div>

<div class="steps-box">
	<?php
	foreach ($taskLogs as $log)
	{
		if (empty($log))
		{
			continue;
		}
	?>
	<div class="row">
		<div class=col-md-1>Step <?=$log['step']?>: </div>
		<div class=col-md-10><strong><?=$log['name']?></strong> (<?=$log['description']?>) ----> <span class="status-<?=$log['status']?>"><?=$log['status']?></span></div>
	</div>
		
	<?php	
	}
	?>
</div>