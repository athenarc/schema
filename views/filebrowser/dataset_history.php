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
<?php Headers::end()?>


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


 