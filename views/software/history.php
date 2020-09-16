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
	
	<div class="col-md-12">
		<h1><?= Html::encode($this->title) ?>
		<span class="pull-right"> 
			<?= Html::a("$stats_icon User Statistics", ['/software/user-statistics'], ['class'=>'btn btn-info']) ?> </span>
			
		</h1>	
	</div>	

	
</div>	


<div class="row">&nbsp;</div>



<?php
if (!empty($results))
{
?>

<div class="row"><div class="col-md-12"><?= LinkPager::widget(['pagination' => $pagination]) ?></div></div>
<div class="table-responsive"><table class="table table-striped">
	<thead>
		<tr>
			<th class="col-md-2" scope="col">Software</th>
			<th class="col-md-2" scope="col">Started on</th>
			<th class="col-md-2" scope="col">Stopped on</th>
			<th class="col-md-2" scope="col">Project</th>
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
				<td class="col-md-2"><span><?=$res['softname']?> <?=$res->softversion?></span><span><?=($res->type=='workflow') ? Html::img('@web/img/cwl-32x32-transparent.png',['height'=>'25px;']) : '' ?></span></td>
				<td class="col-md-2"><?=empty($res->start)? '' : date("F j, Y, H:i:s",strtotime($res->start))?></td>
				<td class="col-md-2"><?=empty($res->stop)? '' : date("F j, Y, H:i:s",strtotime($res->stop))?></td>
				<td class="col-md-2"><?=$res->project?></td>
				<td class="col-md-1"><?=(empty($res->status)) ? "Running" : $res->status?></td>
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
								<td class="col-md-3 text-center">
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
									<td class="col-md-3 text-center"><span><?=($res->type=='workflow')? '' : '<i>Image unavailable</i>&nbsp;'?></span><span><?= Html::a("$details_icon Details",['software/job-details', 'jobid'=>$res['jobid']],['class'=>'btn btn-info btn-md'])?></span></td>
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
								<td class="col-md-3 text-center">
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
									<td class="col-md-3 text-center"><span><i>Workflow unavailable</i>&nbsp;</span><span><?= Html::a("$details_icon Details",['software/job-details', 'jobid'=>$res['jobid']],['class'=>'btn btn-info btn-md'])?></span></td>
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