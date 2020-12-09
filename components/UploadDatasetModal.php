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


		echo Html::cssFile('@web/css/components/uploadDataset.css');
		$form=ActiveForm::begin(['action'=>['filebrowser/upload-dataset'], 'method'=> 'POST', 'options' => ['enctype'=>'multipart/form-data']]);//

		echo "<div class='modal fade' tabindex='-1' role='dialog' id='upload-modal' aria-labelledby='upload-modal' aria-hidden='true'>";
		echo '<div class="modal-dialog modal-dialog-centered modal-lg modal-size" role="document">';
		echo '<div class="modal-content" >';
		echo '<div class="modal-header">';
		echo "<div class='modal-title text-center size'  id='exampleModalLongTitle'> Dataset details</div>";
		echo '</div>';
		echo '<div class="modal-body">';
		echo  '<div class="row body-row">
				 <span class="col-md-4">Upload dataset to: </span>
				 <span class="col-md-8" >'. $form->field($model,'provider')
				 ->dropdownList($datasets, ['class'=>'form-control'])->label("").'</span>
				</div>';
		echo  '<div class="row body-row">
					<span class="col-md-4"> File:</span> 
					<span class="col-md-8">'. $form->field($model,'dataset_id')
					->textInput(['class'=>'form-control'])->label("").'</span>
				</div>';
		echo  '<div class="row body-row">
					<span class="col-md-4"> Metadata:</span> 
					<span class="col-md-8">'. Html::fileInput('metadata').'</span>
				</div>';
		echo  '<div class="row body-row">
					<span class="col-md-4"> API Key:</span> 
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

