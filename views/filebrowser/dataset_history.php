<?php




use yii\helpers\Html;
use yii\widgets\LinkPager;
use app\components\Headers;
use app\components\ROCrateModal;

$this->title="Dataset history";



Headers::begin() ?>
<?php echo Headers::widget(
['title'=>$this->title, 
'buttons'=>
	[
		['fontawesome_class'=>'<i class="fas fa-arrow-left"></i>','name'=> 'Back', 
		'action'=>['filebrowser/index'],
	     'options'=>['class'=>'btn btn-default',], 'type'=>'a'],
	],
])
?>
<?Headers::end()?>


<?php
if (!empty($results))
{
?>
	<div class="table-responsive">
		<table class="table table-striped" >
		<thead>
			<tr>
				<th class="col-md-2" scope="col">Id</th>
				<th class="col-md-2" scope="col">Name</th>
				<th class="col-md-1" scope="col">Version</th>
				<th class="col-md-2" scope="col">Downloaded on</th>
				<th class="col-md-2" scope="col">Provider</th>
				<th class="col-md-2" scope="col">Folder</th>
			</tr>
		</thead>
		<tbody>
	<?php
	foreach ($results as $res)
	{?>
			<tr>
				<td class="col-md-3" style="font-size: 14px;"><?=$res['dataset_id']?></td>
				<td class="col-md-2" style="font-size: 14px;"><?=$res['name']?></td>
				<td class="col-md-1" style="font-size: 14px;"><?=empty($res['version'])?'N/A': ($res['version'])?></td>
				<td class="col-md-2" style="font-size: 14px;"><?=explode(' ', $res['date'])[0]?></td>
				<td class="col-md-2" style="font-size: 14px;"><?=$res['provider']?></td>
				<td class="col-md-3" style="font-size: 14px;"><?=$res['folder_path']?></td>
			</tr>
	<?php
	}?>
		</tbody>
	</table>
	</div>
<?php	
}
else
{
?>

	<h2>You have not downloaded any dataset yet!</h2>
<?php
}?>


 