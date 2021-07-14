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
/*
* Helper for creating link buttons used for tools
*
* @parameter $link : can either be a string which contains the link or an array with the link 
*  in the form of ['controller/action', various parameters]
* @parameter $link_attributes : defaults to []. The user can add other attributes for the link like ['target'=>'_blank']
*
* @author Kostis Zagganas
*/

namespace app\components;
use yii\helpers\Html;
use yii\helpers\Url;
use Yii;
use app\models\Software;
use app\models\SystemConfiguration;

class SoftIndexButton
{
	public static function button($type,$link='', $image_name='', $profiled='', $version='' )
	{
		if ($type=='run')
		{
			$icon='<i class="fas fa-play"></i>';
			$text="$icon";
			$class='btn run-button';
			$title="Run";
			// $hidden_input=Html::hiddenInput('run_hidden',Url::to([$link]),['class'=>'run_hidden']);

		}
		elseif ($type=='edit')
		{
			$icon='<i class="fas fa-edit"></i>';
			$text="$icon";
			$class='btn edit-button';
			$title="Edit";
			// $hidden_input=Html::hiddenInput('edit_hidden',Url::to(['software/edit-software', 'name'=>$image_name]),['class'=>'edit_hidden']);
		}
		elseif ($type=='delete')
		{
			$icon='<i class="fas fa-times"></i>';
			$text="$icon";
			$class='btn delete-button';
			$title="Delete";
			$link='javascript:void(0);';
		}
		elseif ($type=='analyze')
		{
			$icon='<i class="fas fa-chart-line"></i>';
			$text="$icon";
			$config=SystemConfiguration::find()->one();

        	$profiler=$config->profiler;
        	if($profiler)
			{
				
				$class='btn analyze-button';
				$title="Analyze";
				if($profiled==1)
				{
					$text='<i class="fas fa-chart-line" style="color:green"></i>';
					$title="Profile exists for this software";
				}
	
			}
			else
			{
				$class='btn analyze-button hidden';
				$title="";
			}


			
		}
		elseif ($type=='visualize')
		{
			$icon='<i class="fas fa-eye"></i>';
			$text="$icon";
			$class='btn visualize-button';
			$title="Visualize";
			$link='javascript:void(0);';
		}
		echo Html::a($text,$link,['class'=>$class,'title'=>$title, 'image_name'=>$image_name, 'profiled'=>$profiled, 'version'=>$version]);

		// if ($type=='edit')
		// {
		// 	echo $hidden_input;

		// }
		if ($type=='delete')
		{
			echo '<div class="modal fade" id="delete-modal" tabindex="-1" role="dialog" aria-labelledby="delete-modal" aria-hidden="true">';
			echo '<div class="modal-dialog modal-dialog-centered" role="document">';
    		echo '<div class="modal-content">';
   			echo '<div class="modal-header">';
			echo '<h5 class="modal-title" id="exampleModalLongTitle">Confirm delete</h5>';
			echo '<button type="button" class="close" data-dismiss="modal" aria-label="Close">';
			echo '<span aria-hidden="true">&times;</span>';
			echo '</button>';
			echo '</div>';
			echo '<div class="modal-body">';
			echo '';
			echo '</div>';
			echo '<div class="modal-loading"><b>Deleting <i class="fas fa-spinner fa-spin"></i></b></div>';
			echo '<div class="modal-footer">';
			echo '<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>';
			echo '<a class="btn btn-danger confirm-delete">Delete</a>';
			echo '</div>';
			echo '</div>';
			echo '</div>';
			echo '</div>';
		}
		
	}
	
}
