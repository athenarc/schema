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
use yii\helpers\Url;
use yii\widgets\LinkPager;
use app\components\Headers;
use webvimark\modules\UserManagement\models\User;




$this->title="RO-crate objects";

$this->registerJsFile('@web/js/software/ro-crate-history.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

$current_user=User::getCurrentUser()['username'];
$current_user_name=explode($current_user, '@')[0];
$download_icon='<i class="fas fa-download" title="Download"></i>';


Headers::begin() ?>
<?php echo Headers::widget(
['title'=>$this->title,
'search'=>true,
'search_content'=> Html::a("<i class='fas fa-search' title='Search' id='search-button'></i>",null)
	//['software/search-ro-crate', 'search_parameter'=>$search_parameter])
.'&nbsp;&nbsp;'
.Html::textInput('search',$search_parameter, ['id'=>'text-search']).'&nbsp;',
'buttons'=>
	[
	     ['fontawesome_class'=>'<i class="fas fa-arrow-left"></i>','name'=> 'Back', 
		'action'=>['site/index'],
	     'options'=>['class'=>'btn btn-default',], 'type'=>'a'],
	],
])
?>
<?php Headers::end()?>




<?php

if (!empty($results_user) || !empty($results_public))
{	
	
	if(!empty($results_user))
	{?>
		<h3> My objects</h3>
		<div class="row">&nbsp;</div>
		

		<div class="table-responsive">
			<table class="table table-striped" >
			<thead>
				<tr>
					<th class="col-md-2" scope="col">Software name</th>
					<th class="col-md-2" scope="col">Object creation</th>
					<th class="col-md-4" scope="col">Description</th>
					<th class="col-md-2" scope="col">Job started on</th>
					<th class="col-md-2" scope="col">Job ended on</th>
					<th class="col-md-2" scope="col"></th>
				</tr>
			</thead>
			<tbody>
		<?php
		foreach ($results_user as $res)
		{
			if($res['public']==1)
			{
				$public='<i class="fas fa-lock-open" title="This object is public in SCHeMa"></i>';
			}
			else
			{
				$public='<i class="fas fa-lock" title="This object is visible by the uploader in SCHeMa"></i>';
			}

		?>
				<tr>
					<td class="col-md-2 matrix-entries"><?=$public?> &nbsp; <?=$res['softname']?></td>
					<td class="col-md-2 matrix-entries" ><?=explode(' ', $res['date'])[0]?></td>
					<?php
					if(strlen($res['experiment_description'])>200)
					{
						
						$experiment_description = substr($res['experiment_description'], 0, 200).'...';
					}
					else
					{
						$experiment_description = $res['experiment_description'];
					}?>
					<td class="col-md-4 matrix-entries" title="<?=$res['experiment_description']?>"><?=empty($experiment_description)?'N/A': ($experiment_description)?></td>
					<td class="col-md-2 matrix-entries"><?=$res['start']?></td>
					<td class="col-md-2 matrix-entries"><?=$res['stop']?></td>
					<td class="col-md-2 matrix-entries"><?=Html::a("$download_icon",$res['link'], ['class'=>'btn btn-success'])?></td>
					
				</tr>
		<?php
		}?>
			</tbody>
		</table>
		</div>

	<?php
	}
	if (!empty($results_public))
	{?>
		<h3> Public objects by other users </h3>
		<div class="row">&nbsp;</div>
		<div class="table-responsive">
			<table class="table table-striped" >
			<thead>
				<tr>
					<th class="col-md-2" scope="col">User</th>
					<th class="col-md-2" scope="col">Software name</th>
					<th class="col-md-2" scope="col">Object creation</th>
					<th class="col-md-2" scope="col">Description</th>
					<th class="col-md-2" scope="col">Job started on</th>
					<th class="col-md-2" scope="col">Job ended on</th>
					<th class="col-md-2" scope="col"></th>
				</tr>
			</thead>
			<tbody>
		<?php
		foreach ($results_public as $res)
		{?>
				<tr>
					<td class="col-md-2 matrix-entries"><?=explode('@',$res['username'])[0]?></td>
					<td class="col-md-2 matrix-entries"><?=$res['softname']?></td>
					<td class="col-md-2 matrix-entries" ><?=explode(' ', $res['date'])[0]?></td>
					<td class="col-md-2 matrix-entries"><?=empty($res['experiment_description'])?'N/A': ($res['experiment_description'])?></td>
					<td class="col-md-2 matrix-entries"><?=$res['start']?></td>
					<td class="col-md-2 matrix-entries"><?=$res['stop']?></td>
					<td class="col-md-2 matrix-entries"><?=Html::a("$download_icon",$res['link'], ['class'=>'btn btn-success'])?></td>
					
				</tr>
		<?php
		}?>
			</tbody>
		</table>
		</div>
		
	
	<?php
	}
	echo LinkPager::widget(['pagination' => $pagination,]);	
}
else
{
?>

	<h2>There are no RO-crate objects available!</h2>
<?php
}?>


 