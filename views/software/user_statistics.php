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

$this->title="User statistics";


// $approve_icon='<i class="fas fa-check"></i>';
// $reject_icon='<i class="fas fa-times"></i>';
$back_icon='<i class="fas fa-arrow-left"></i>';

Headers::begin() ?>
<?php echo Headers::widget(
['title'=>$this->title,
'buttons'=>
    [
        ['fontawesome_class'=>$back_icon,'name'=> 'Back', 'action'=>['/software/history'],
        'options'=>['class'=>'btn btn-default'], 'type'=>'a'], 
        
    ],
   
])
?>
<?php Headers::end();
/*
 * Users are able to view the name, version, start date, end date, mountpoint 
 * and running status of their previous software executions. 
 */
?>


<div class="row">&nbsp;</div>

<?php
if (!empty($projectAggr))
{
?>

 <table class="table table-responsive table-striped">
	<tbody>
			<tr>
				<th class="col-md-5" scope="col">Number of jobs</th>
				<td class="col-md-4" scope="col"><?=$projectTotals[0]?></td>
			</tr>
			<tr>
				<th class="col-md-5" scope="col">Number of completed jobs</th>
				<td class="col-md-4" scope="col"><?=$projectTotals[1]?></td>
			</tr><tr>
				<th class="col-md-5" scope="col">Total execution time</th>
				<td class="col-md-4" scope="col"><?=$projectTotals[2]?></td>
			</tr><tr>
				<th class="col-md-5" scope="col">Average memory footprint (GB)</th>
				<td class="col-md-4" scope="col"><?=$projectTotals[3]?></td>
			</tr><tr>
				<th class="col-md-5" scope="col">Average CPU load (cores)</th>
				<td class="col-md-4" scope="col"><?=round($projectTotals[4])/1000?></td>
			</tr>

		
	</body>
</table>



<div class="row">&nbsp;</div>

<div class='title row'>
	<div class="col-md-11">
		<h1> User statistics per project</h1>
	</div>
</div>

<div class="row">&nbsp;</div>





<table class="table table-responsive table-striped">
	<thead>
		<tr>
			<th class="col-md-2 text-center" scope="col">Project</th>
			<th class="col-md-1 text-center" scope="col">Past jobs</th>
			<th class="col-md-1 text-center" scope="col">Remain.&nbsp;jobs</th>
			<th class="col-md-2 text-center" scope="col">Average RAM (GB)</th>
			<th class="col-md-2 text-center" scope="col">Average CPU</th>
			<th class="col-md-2 text-center" scope="col">Total execution time</th>
			<th class="col-md-3 text-center" scope="col">Remain.&nbsp;exec.&nbsp;time</th>
		</tr>
	</thead>
	<tbody>
<?php




foreach ($projectAggr as $res)
{
	         
?>
			 <tr class="text-center">
				<td class="col-md-2"><?=(empty($res['project'])) ? " " : $res['project']?></td>
				<td class="col-md-1"><?=(empty($res['count'])) ? " " : $res['count']?></td>
				<td class="col-md-1"><?=(empty($res['count'])) ? $quotas[$res['project']]['num_of_jobs'] : $quotas[$res['project']]['num_of_jobs'] - $res['count']?></td>
				<td class="col-md-2"><?=empty($res['ram'])? '' : $res['ram']?></td>
				<td class="col-md-2"><?=empty($res['cpu'])? '' : round($res['cpu'])/1000?></td>
				<td class="col-md-2"><?=(empty($res['total_time'])) ? " " : $res['total_time']?></td>
				<td class="col-md-3"><?=(empty($res['total_time'])) ? " " :" " ?></td>
				
			</tr> 

<?php
}
?>
	</tbody>
</table>

<?php
}
else
{
?>

	<h2>You have not run any software yet!</h2>
<?php
}




 

