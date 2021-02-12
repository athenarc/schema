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
 * This view file prints the history of software runs made by a user
 * 
 * @author: Kostis Zagganas
 * First version: March 2019
 */

use yii\helpers\Html;
use yii\widgets\LinkPager;
use app\components\Headers;
use app\components\RoCrateModal;
use app\models\RoCrate;

$this->title="Job history";

$this->registerJsFile('@web/js/software/history.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
/*
 * Users are able to view the name, version, start date, end date, mountpoint 
 * and running status of their previous software executions. 
 */

$stats_icon='<i class="fas fa-eye"></i>';
Headers::begin() ?>
<?php echo Headers::widget(
['title'=>$this->title, 
	'buttons'=>
	[
		
		['fontawesome_class'=>$stats_icon,'name'=> 'User Statistics', 'action'=>['/software/user-statistics'],
		 'options'=>['class'=>'btn btn-info'], 'type'=>'a'] 
	],
])
?>
<?Headers::end()?>






<?php
if (!empty($results))
{
?>


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
			<th class="col-md-3" scope="col">Project</th>
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
$experiment_icon='<i class="fa fa-flask" aria-hidden="true" style="color:white"></i>';
$details_icon='<i class="fas fa-eye"></i>';



	foreach ($results as $res)
	{
		if ($res->status=='Complete')
		{
			$completed='';
		}
		else
		{
			$completed='hidden';
		}
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
					<td class="col-md-3" style="font-size: 14px;"><?=$res->project?></td>
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
									<?= Html::a("$play_icon",[$controller . '/reattach','jobid'=>$res->jobid],['class'=>'btn btn-success btn-md', 'title'=>'Re-attach' ])?>
									<?= Html::a("$details_icon",['software/job-details', 'jobid'=>$res->jobid],['class'=>'btn btn-secondary btn-md', 'title'=>'Details'])?>
									
						<?php
								}
								else
								{
						?>			<?= Html::a("$play_icon",[$controller . '/rerun','jobid'=>$res->jobid],
									['class'=>'btn btn-success btn-md', 'title'=>'Re-run'])?>
									<?= Html::a("$details_icon",['software/job-details', 'jobid'=>$res->jobid],['class'=>'btn btn-secondary btn-md', 'title'=>'Details'])?>
									<?php
									$experiment=RoCrate::find()->where(['jobid'=>$res->jobid])->one(); 
									if (!empty($experiment)) 
									{
										$experiment_icon='<i class="fa fa-flask" aria-hidden="true" style="color:rgb(127, 255, 0)"></i>';
										$title='Edit the RO-crate object of this run';
									}
									else
									{
										$experiment_icon='<i class="fa fa-flask" aria-hidden="true" style="color:white"></i>';
										$title='Save the run in an RO-crate object, to facilitate the reproducibility of the corresponding experiment';
									}
									?>
									<?= Html::a("$experiment_icon", null,
										['class'=>"btn btn-secondary btn-md experiment $completed", 'data-target'=>"#experiment-modal-$res->jobid", 'title'=>"$title", 'id'=>"$res->jobid", ])?>
						<?php
								}
						?>
									
								</td>

						<?php
							}
							else
							{
						?>
									<td class="col-md-3" style="text-align: right;"><span><?=($res->type=='workflow')? '' : '<i style="font-size:14px;">Image N/A</i>'?> &nbsp;</span><span><?= Html::a("$details_icon",['software/job-details', 'jobid'=>$res['jobid']],['class'=>'btn btn-secondary btn-md', 'title'=>'Details'])?></span></td>
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
									<?= Html::a("$play_icon",['workflow/reattach','jobid'=>$res->jobid],['class'=>'btn btn-success btn-md', 'title'=>'Re-attach'])?>
									<?= Html::a("$details_icon",['software/job-details', 'jobid'=>$res->jobid],['class'=>'btn btn-secondary btn-md', 'title'=>'Details'])?>
									
						<?php
								}
								else
								{

						?>			<?= Html::a("$play_icon",['workflow/rerun','jobid'=>$res->jobid],
									['class'=>'btn btn-success btn-md', 'title'=>'Re-run'])?>	
									<?= Html::a("$details_icon",['software/job-details', 'jobid'=>$res->jobid],['class'=>'			btn btn-secondary btn-md', 'title'=>'Details'])?>
									<?php
									$experiment=RoCrate::find()->where(['jobid'=>$res->jobid])->one(); 
									if (!empty($experiment)) 
									{
										$experiment_icon='<i class="fa fa-flask" aria-hidden="true" style="color:rgb(127, 255, 0)"></i>';
										$title='Edit the RO-crate object of this run';
									}
									else
									{
										$experiment_icon='<i class="fa fa-flask" aria-hidden="true" style="color:white"></i>';
										$title='Save the run in an RO-crate object, to facilitate the reproducibility of the corresponding experiment';
									}
									?>
									<?= Html::a("$experiment_icon", null,
										['class'=>"btn btn-secondary btn-md experiment $completed", 'data-target'=>"#experiment-modal-$res->jobid", 'title'=>"$title", 'id'=>"$res->jobid", ])?>
						<?php
								}
						?>
									
								</td>

						<?php
							}
							else
							{
						?>
									<td class="col-md-3" style="text-align: right;"><span><i style="font-size: 14px;">Workflow N/A</i>&nbsp;</span><span><?= Html::a("$details_icon",['software/job-details', 'jobid'=>$res['jobid']],['class'=>'btn btn-secondary btn-md', 'title'=>'Details'])?></span></td>
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
}?>

<div class="row"><div class="col-md-12"><?= LinkPager::widget(['pagination' => $pagination]) ?></div></div>

<?php
foreach ($results as $result) 
{
	if ($result->type=='job' || $result->type=='workflow')
	{
		RoCrateModal::addModal($result->jobid);
	}
}


?>