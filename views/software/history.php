<?php


/**
 * This view file prints the history of software runs made by a user
 * 
 * @author: Kostis Zagganas
 * First version: March 2019
 */

use yii\helpers\Html;
use yii\widgets\LinkPager;

$this->title="Job history";


/*
 * Users are able to view the name, version, start date, end date, mountpoint 
 * and running status of their previous software executions. 
 */
$stats_icon='<i class="fas fa-eye"></i>';
?>
<div class='title row'>
	
	<div class="col-md-12 headers">
		<?= Html::encode($this->title) ?>
		<span class="pull-right"> 
			<?= Html::a("$stats_icon User Statistics", ['/software/user-statistics'], ['class'=>'btn btn-info']) ?> </span>
			
			
	</div>	

	
</div>	


<div class="row">&nbsp;</div>



<?php
if (!empty($results))
{
?>

<div class="row"><div class="col-md-12"><?= LinkPager::widget(['pagination' => $pagination]) ?></div></div>
<div class="table-responsive"><table class="table table-striped" >
	<thead>
		<tr>
			<th class="col-md-2" scope="col">Software</th>
		<?php
		if (Yii::$app->params['standalone'])
		{
		?>
			<th class="col-md-3" scope="col">Started on</th>
			<th class="col-md-3" scope="col">Stopped on</th>
		<?php	
		}
		else
		{
		?>
			<th class="col-md-2" scope="col">Started on</th>
			<th class="col-md-2" scope="col">Stopped on</th>
			<th class="col-md-2" scope="col">Project</th>
		<?php	
		}
		?>
			
			<th class="col-md-1" scope="col">Status</th>
			<th class="col-md-3" scope="col">&nbsp;</th>
		</tr>
	</thead>
	<tbody>
<?php


/*
 * Users may also re run a software image via the "Re-run" button
 */

$play_icon='<i class="fas fa-play"></i>';
$details_icon='<i class="fas fa-eye"></i>';


	foreach ($results as $res)
	{
		$key=$res['software_id'];
	?>
			<tr>
				<td class="col-md-2" style="font-size: 14px;"><span><?=$res['softname']?> <?=$res->softversion?></span><span><?=($res->type=='workflow') ? Html::img('@web/img/cwl-32x32-transparent.png',['height'=>'25px;']) : '' ?></span></td>

				<?php
				if (Yii::$app->params['standalone'])
				{
				?>
					<td class="col-md-3" style="font-size: 14px;"><?=empty($res->start)? '' : date("F j, Y, H:i:s",strtotime($res->start))?></td>
					<td class="col-md-3" style="font-size: 14px;"><?=empty($res->stop)? '' : date("F j, Y, H:i:s",strtotime($res->stop))?></td>
				<?php	
				}
				else
				{
				?>
					<td class="col-md-2" style="font-size: 14px;"><?=empty($res->start)? '' : date("F j, Y, H:i:s",strtotime($res->start))?></td>
					<td class="col-md-2" style="font-size: 14px;"><?=empty($res->stop)? '' : date("F j, Y, H:i:s",strtotime($res->stop))?></td>
					<td class="col-md-2" style="font-size: 14px;"><?=$res->project?></td>
				<?php	
				}
				?>

				
				<td class="col-md-1" style="font-size: 14px;"><?=(empty($res->status)) ? "Running" : $res->status?></td>
				<?php
						if ($res->type!='workflow')
						{	
							/**
							 * available contains the available software
							 */
							if (isset($available[$key]))
							{	
								$mpi=$available[$key][1];

								if ($mpi)
								{
									$controller='software-mpi';
								}
								else
								{
									$controller='software';
								}
						?>
								<td class="col-md-3" style="text-align: right;">
						<?php
								if (empty($res->status))
								{
						?>
									<?= Html::a("$play_icon Re-attach",[$controller . '/reattach','jobid'=>$res->jobid],['class'=>'btn btn-success btn-md'])?>
									
						<?php
								}
								else
								{
						?>
									<?= Html::a("$play_icon Re-run",[$controller . '/rerun','jobid'=>$res->jobid],['class'=>'btn btn-success btn-md'])?>
						<?php
								}
						?>
									<?= Html::a("$details_icon Details",['software/job-details', 'jobid'=>$res->jobid],['class'=>'btn btn-info btn-md'])?>
								</td>

						<?php
							}
							else
							{
						?>
									<td class="col-md-3" style="text-align: right;"><span><?=($res->type=='workflow')? '' : '<i style="font-size:14px;">Image N/A</i>'?> &nbsp;</span><span><?= Html::a("$details_icon Details",['software/job-details', 'jobid'=>$res['jobid']],['class'=>'btn btn-info btn-md'])?></span></td>
						<?php
							}
						}
						else
						{
							/**
							 * available contains the available software
							 */
							if (isset($available_workflows[$key]))
							{	
						?>
								<td class="col-md-3" style="text-align: right;">
						<?php
								if (empty($res->status))
								{
						?>
									<?= Html::a("$play_icon Re-attach",['workflow/reattach','jobid'=>$res->jobid],['class'=>'btn btn-success btn-md'])?>
									
						<?php
								}
								else
								{
						?>
									<?= Html::a("$play_icon Re-run",['workflow/rerun','jobid'=>$res->jobid],['class'=>'btn btn-success btn-md'])?>
						<?php
								}
						?>
									<?= Html::a("$details_icon Details",['software/job-details', 'jobid'=>$res->jobid],['class'=>'btn btn-info btn-md'])?>
								</td>

						<?php
							}
							else
							{
						?>
									<td class="col-md-3" style="text-align: right;"><span><i style="font-size: 14px;">Workflow N/A</i>&nbsp;</span><span><?= Html::a("$details_icon Details",['software/job-details', 'jobid'=>$res['jobid']],['class'=>'btn btn-info btn-md'])?></span></td>
						<?php
							}
						}
						?>
		</tr>
<?php
	}
?>
	</tbody>
</table></div>

<?php
}
else
{
?>

	<h2>You have not run any software yet!</h2>
<?php
}