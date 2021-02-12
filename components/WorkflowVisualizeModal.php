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

namespace app\components;
use yii\helpers\Html;
use Yii;

class WorkflowVisualizeModal
{
	public static function addModal($name, $version, $visualize)
	{

		
		echo "<div class='modal fade' tabindex='-1' role='dialog' id='vis-modal-$name-$version' aria-labelledby='description-modal' aria-hidden='true'>";
		echo '<div class="modal-dialog modal-dialog-centered" role="document">';
		echo '<div class="modal-content" style="width:650px;">';
		echo '<div class="modal-header">';
		echo "<h5 class='modal-title' id='exampleModalLongTitle'>$name v. $version</h5>";
		echo "<button type='button' class='close' data-dismiss='modal' aria-label='Close'>";
		echo '<span aria-hidden="true">&times;</span>';
		echo '</button>';
		echo '</div>';
		echo '<div class="modal-body">';
		if(empty($visualize))
		{
			echo 'Workflow visualizaton not available';
		}
		else
		{	
			echo Html::img("@web/img/workflows/$visualize", ['width'=>"600px",'height'=>'400px']) ;
		}
		echo '</div>';
		echo '<div class="modal-footer">';
		echo "<button type='button' class='btn btn-secondary' data-dismiss='modal'>Close</button>";
		echo '</div>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
		
	}
	
}
