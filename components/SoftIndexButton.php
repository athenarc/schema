<?php
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

class SoftIndexButton
{
	public static function button($type,$link='',$image_name='')
	{
		if ($type=='run')
		{
			$icon='<i class="fas fa-play"></i>';
			$text="$icon Run";
			$class='btn btn-success btn-md run-button';
			// $hidden_input=Html::hiddenInput('run_hidden',Url::to([$link]),['class'=>'run_hidden']);

		}
		elseif ($type=='edit')
		{
			$icon='<i class="fas fa-edit"></i>';
			$text="$icon Edit";
			$class='btn btn-secondary btn-md edit-button';
			// $hidden_input=Html::hiddenInput('edit_hidden',Url::to(['software/edit-software', 'name'=>$image_name]),['class'=>'edit_hidden']);
		}
		elseif ($type=='delete')
		{
			$icon='<i class="fas fa-times"></i>';
			$text="$icon Delete";
			$class='btn btn-secondary btn-md delete-button';
			$link='javascript:void(0);';
		}
		echo Html::a($text,$link,['class'=>$class]);
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
