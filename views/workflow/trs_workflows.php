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
 * This view file prints the list of available software
 * 
 * @author: Kostis Zagganas
 * First version: Dec 2018
 */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Form;
use yii\bootstrap\Button;
use yii\captcha\Captcha;
use app\components\SoftIndexButton;
use webvimark\modules\UserManagement\models\User;
use app\components\SoftDescrModal;
use app\components\WorkflowVisualizeModal;
use app\components\SoftwareIndicatorList;
use app\components\Headers;



/*
 * Add stylesheet
 */

echo Html::cssFile('@web/css/workflow/index.css');
$this->registerJsFile('@web/js/workflow/index.js', ['depends' => [\yii\web\JqueryAsset::className()]]);



/*
 * Add button for new software upload
 */ 
$this->title = 'TRS workflows';
// $this->params['breadcrumbs'][] = $this->title;

$workflowAdd='<i class="fas fa-plus"></i>&nbsp;New workflow';
$projectAdd='<i class="fas fa-plus"></i>&nbsp;New project';
$external='<i class="fas fa-external-link-alt"></i>';


// $softwareAdd.='</div>';

Headers::begin() ?>
<?php echo Headers::widget(
['title'=>$this->title, 
	'buttons'=>
	[ 		
		['fontawesome_class'=>'<i class="fas fa-arrow-left"></i>','name'=> 'Back', 'action'=>['/workflow/index'],
		 'options'=>['class'=>'btn btn-default'], 'type'=>'a'],

	],
])
?>
<?php Headers::end()?>


<?php
	/*
	 * Add software table heading
	 */
	if (empty($trs))
	{

?>
		<div class="row">
			<div class="col-md-12 text-center"><h2>No workflows available.</h2></div>
		</div>
		<div class="row">
			<div class="col-md-12 text-center"><?=Html::img('@web/img/empty-white-box.svg', ['width'=>'150', 'height'=>'150'])?></h2></div>
		</div>
		
<?php
	}
	else
	{
		foreach ($trs as $endpoint=>$workflows)
		{
?>
			<h2>Workflows in <?=$endpoint?></h2>
			<div class=" table-responsive">
				<table class="table table-striped">
				<thead class="software-header">
					<th class="col-md-8"> Name</th>
					<th class="col-md-2 text-center">Version</th>
					<th class="col-md-2"></th>
				</thead>
				<tbody>
<?php
			foreach ($workflows as $workflow)
			{

				if ($workflow['downloadable'])
				{
					$disabled='';
				}
				else
				{
					$disabled='disabled';
				}

?>
				<tr>
					<td class="col-md-8"><strong><?=$workflow['name']?></strong></td>
					<td class="col-md-2 text-center"><?=$workflow['version']?></td>
					<td class="col-md-2 text-right"><?=Html::a('<i class="fas fa-download"></i>&nbsp;Download',['workflow/trs-download'],["class"=>"btn btn-md btn-secondary $disabled"] )?></td>
				</tr>
<?php
			}
?>
				</tbody>
				</table>
			</div>
<?php
		}
?>		
			
	
<?php
	}
