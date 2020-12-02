<?php


namespace app\components;
use yii\helpers\Html;
use yii\helpers\Url;
use webvimark\modules\UserManagement\models\User;
use Yii;
use app\models\UploadDataset;
use yii\widgets\ActiveForm; 



class UploadDatasetModal
{
	public static function addModal()
	{
		
		$datasets=['Helix'=>'Helix'];
		$model=new UploadDataset;



		$form=ActiveForm::begin(['action'=>['filebrowser/upload-dataset'], 'method'=> 'POST', 'options' => ['enctype'=>'multipart/form-data']]);//

		echo "<div class='modal fade' tabindex='-1' role='dialog' id='upload-modal' aria-labelledby='upload-modal' aria-hidden='true'>";
		echo '<div class="modal-dialog modal-dialog-centered modal-lg" role="document" style="width:600px;">';
		echo '<div class="modal-content" >';
		echo '<div class="modal-header">';
		echo "<h5 class='modal-title text-center' style='font-size:25px' id='exampleModalLongTitle'> Dataset details</h5>";
		echo '</div>';
		echo '<div class="modal-body">';
		echo  '<div class="row" style="font-size:15px; margin-bottom:10px;">
				 <span class="col-md-4" style="margin-top:5px;">Upload dataset to: </span>
				 <span class="col-md-8" >'. $form->field($model,'provider')
				 ->dropdownList($datasets, ['class'=>'form-control'])->label("").'</span>
				</div>';
		echo  '<div class="row" style="font-size:15px; margin-bottom:10px;">
					<span class="col-md-4" style="margin-top:7px;"> File:</span> 
					<span class="col-md-8">'. $form->field($model,'dataset_id')
					->textInput(['class'=>'form-control'])->label("").'</span>
				</div>';
		echo  '<div class="row" style="font-size:15px; margin-bottom:10px;">
					<span class="col-md-4" style="margin-top:7px;"> Metadata:</span> 
					<span class="col-md-8">'. Html::fileInput('metadata').'</span>
				</div>';
		echo  '<div class="row" style="font-size:15px; margin-bottom:10px;">
					<span class="col-md-4" style="margin-top:7px;"> API Key:</span> 
					<span class="col-md-8">'. $form->field($model,'api_key')
					->textInput(['class'=>'form-control'])->label("").'</span>
				</div>';
	 
		echo '</div>';
		echo '<div class="modal-footer">';
		echo Html::submitButton("Upload",['class'=>"btn btn-success"]);
		echo "<button type='button' class='btn btn-secondary' data-dismiss='modal'>Close</button>";
		echo '</div>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
		$form=ActiveForm::end();
		
	}
	
}

?>

