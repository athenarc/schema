<?php


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
