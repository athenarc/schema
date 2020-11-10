<?php


/**
 * This view file prints the history of software runs made by a user
 * 
 * @author: Kostis Zagganas
 * First version: March 2019
 */

use yii\helpers\Html;
use yii\widgets\LinkPager;
use app\components\Headers;

$this->title="Job details";

// $approve_icon='<i class="fas fa-check"></i>';
// $reject_icon='<i class="fas fa-times"></i>';
$back_icon='<i class="fas fa-arrow-left"></i>';
$logs_icon='<i class="fas fa-cloud-download-alt"></i>';

/*
 * Users are able to view the name, version, start date, end date, mountpoint 
 * and running status of their previous software executions. 
 */
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
<?Headers::end()?>


<div class="table-responsive">
	<table class="table table-striped">
		<tbody>
			<tr>
				<th class="col-md-6" scope="col">Software name</th>
				<td class="col-md-6" scope="col"><?=$name?></td>
			</tr>
			<tr>
				<th class="col-md-6" scope="col">Software version</th>
				<td class="col-md-6" scope="col"><?=$version?></td>
			</tr>
			<tr>
				<th class="col-md-6" scope="col">Status</th>
				<td class="col-md-6" scope="col"><?=$status?></td>
			</tr>
			<tr>
				<th class="col-md-6" scope="col">Started on</th>
				<td class="col-md-6" scope="col"><?=$start?></td>
			</tr>
			<tr>
				<th class="col-md-6" scope="col">Stopped on</th>
				<td class="col-md-6" scope="col"><?=$stop?></td>
			</tr>
			<tr>
				<th class="col-md-6" scope="col">Total execution time</th>
				<td class="col-md-6" scope="col"><?=$execTime?></td>
			</tr>
			<tr>
				<th class="col-md-6" scope="col">Maximum memory footprint (GB)</th>
				<td class="col-md-6" scope="col"><?=$ram?></td>
			</tr>
			<tr>
				<th class="col-md-6" scope="col">Maximum CPU load (cores)</th>
				<td class="col-md-6" scope="col"><?=$cpu?></td>
			</tr>
			<tr>
				<th class="col-md-6" scope="col">Machine Type</th>
				<td class="col-md-6" scope="col"><?=$machineType?></td>
			</tr>
			<?php
			if (!empty($iomount))
			{
			?>
			<tr>
				<th class="col-md-6" scope="col">I/O Mountpoint</th>
				<td class="col-md-6" scope="col"><?=$iomount?></td>
			</tr>
			<?php	
			}
			else
			{
			?>
			<tr>
				<th class="col-md-6" scope="col">Input Mountpoint</th>
				<td class="col-md-6" scope="col"><?=$imount?></td>
			</tr>
			<tr>
				<th class="col-md-6" scope="col">Output Mountpoint</th>
				<td class="col-md-6" scope="col"><?=$omount?></td>
			</tr>
			<?php	
			}
			?>

		</tbody>
	</table>
</div>

<div class="row">&nbsp;</div>

<?php
if (($status=='Complete') || ($status=='Error'))
{
?>
<div class="row">
	<div class="col-md-12 text-center">
		<?= Html::a("$logs_icon Logs (std. output)",['software/download-logs','jobid'=>$jobid],['class'=>'btn btn-success btn-md'])?>
	</div>
</div>
<?php
}
?>


 

