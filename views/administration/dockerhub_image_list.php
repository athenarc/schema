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
use yii\bootstrap\NavBar;
use yii\bootstrap\Nav;
use yii\helpers\Url;  


echo Html::CssFile('@web/css/project/request-list.css');

$this->title="Dockerhub image requests";


/*
 * Users are able to view the name, version, start date, end date, mountpoint 
 * and running status of their previous software executions. 
 */


?>

	<div class='title row'>
		<div class="col-md-12">
			<h1><?= Html::encode($this->title) ?></h1>
		</div>
	</div>

	<div class="row">&nbsp;</div>


<?php


?>
  	

<?php
if (!empty($requests))
{
	
?>
<div class="applications-table, table-responsive">
                  
			<table class="table">
				<thead>
					<tr>
						<th class="col-md-4" scope="col">Dockerhub Url</th>
						<th class="col-md-2" scope="col">Submitted on</th>
						<th class="col-md-3" scope="col">Submitted by</th>						
						<th class="col-md-3" scope="col">&nbsp;</th>
					</tr>
				</thead>
				<tbody>
<?php



foreach ($requests as $req)
{
	$view_icon='<i class="fas fa-eye"></i>';
	// $button_link=$button_link=$button_links[$res['project_type']];
	
	


?>
				<tr>
					<td class="col-md-4 align-middle"><?=$req->dock_link?></td>
					<td class="col-md-2 align-middle"><?=$req->date?></td>
					<td class="col-md-3 align-middle"><?=$req->user_name?></td>
					<td class="col-md-3" style="text-align: center;"><?=Html::a("$view_icon Details",['/administration/dockerhub-image-details','id'=>$req->id],['class'=>'btn btn-primary btn-md'])?></td>
				</tr>

<?php
}
?>
				</tbody>
			</table>

		</div>


<?php
}
else
{
?>
		<div class="col-md-12"><h3 class="empty-message">There are no recorded requests.</h3></div>
<?php
}
?>
	</div><!--row-->
	<div class="row">&nbsp;</div>
	<div class="row"><div class="col-md-12"><div class="float-right"><?= LinkPager::widget(['pagination' => $pages]) ?></div></div></div>

</div><!--container-fluid-->