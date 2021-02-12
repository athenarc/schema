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

$this->title="Job history";


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

<div class="row"><div class="col-md-12"><?= LinkPager::widget(['pagination' => $pages]) ?></div></div>
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
				<td class="col-md-2" style="font-size: 14px;"><?=$res['softname']?> <?=$res['softversion']?></td>
				<td class="col-md-2" style="font-size: 14px;"><?=empty($res['start'])? '' : date("F j, Y, H:i:s",strtotime($res['start']))?></td>
				<td class="col-md-2" style="font-size: 14px;"><?=empty($res['stop'])? '' : date("F j, Y, H:i:s",strtotime($res['stop']))?></td>
				<td class="col-md-2" style="font-size: 14px;"><?=$res['project']?></td>
				<td class="col-md-1" style="font-size: 14px;"><?=(empty($res['status'])) ? "Running" : $res['status']?></td>
				<?php
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
							if (empty($res['status']))
							{
					?>
								<?= Html::a("$play_icon Re-attach",[$controller . '/reattach','jobid'=>$res['jobid']],['class'=>'btn btn-success btn-md'])?>
								
					<?php
							}
							else
							{
					?>
								<?= Html::a("$play_icon Re-run",[$controller . '/rerun','jobid'=>$res['jobid']],['class'=>'btn btn-success btn-md'])?>
					<?php
							}
					?>
								<?= Html::a("$details_icon Details",['software/job-details', 'jobid'=>$res['jobid']],['class'=>'btn btn-info btn-md'])?>
							</td>
				
			</tr>

<?php
						}
						else
						{
					?>
								<td class="col-md-3 text-center"><i>Image unavailable</i></td>
					<?php
						}
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