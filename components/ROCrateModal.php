<?php


namespace app\components;
use yii\helpers\Html;
use yii\helpers\Url;
use webvimark\modules\UserManagement\models\User;
use Yii;
use app\models\RunHistory;
use app\models\Software;
use app\models\ROCrate;
use yii\widgets\ActiveForm; 



class ROCrateModal
{
	public static function addModal($jobid)
	{
		
		$history=RunHistory::find()->where(['jobid'=>$jobid])->one();
		$username=User::getCurrentUser()['username'];
		$model=new ROCrate();

		echo Html::cssFile('@web/css/components/roCrate.css');
		$form=ActiveForm::begin(['action'=>['software/create-experiment', 'jobid'=>$jobid], 'method'=> 'POST']);

		echo Html::hiddenInput('softname',$history->softname);
		echo Html::hiddenInput('softversion',$history->softversion);

		echo "<div class='modal fade' tabindex='-1' role='dialog' id='experiment-modal-$jobid' aria-labelledby='experiment-modal' aria-hidden='true'>";
		echo '<div class="modal-dialog modal-dialog-centered modal-lg modal-size" role="document">';
		echo '<div class="modal-content" >';
		echo '<div class="modal-header">';
		echo "<div class='modal-title text-center size' id='exampleModalLongTitle'>RO-Crate object details</div>";
		echo '</div>';
		echo '<div class="modal-body modal-size">';
		echo  '<div class="row body-row">
				 <span class="col-md-6 field-row"">Software URL: </span>
				 <span class="col-md-5" >'.$form->field($model,'software_url')->label("").'</span>
				</div>';
		echo  '<div class="row body-row">
					<span class="col-md-6 field-row"> Input data URL:</span> 
					<span class="col-md-5">'.$form->field($model,'input_data')->label("").'</span>
				</div>';
		echo  '<div class="row body-row">
					<span class="col-md-6 field-row"> Output data URL:</span> 
					<span class="col-md-5">'. $form->field($model,'output_data')->label("").'</span>
				</div>';
		echo  '<div class="row body-row">
					<span class="col-md-6 field-row"> Publication DOI:</span> 
					<span class="col-md-5">'. $form->field($model,'publication')->label("").'</span>
				</div>';
		// echo  '<div class="row body-row">
		// 			<span class="col-md-6 field-row"> Upload experiment on Schema:</span> 
		// 			<span class="col-md-5">'. $form->field($model,'schema')->checkBox(['label'=>'']).'</span>
		// 		</div>';
		echo  '<div class="row body-row">
					<span class="col-md-6"> Download experiment locally:</span> 
					<span class="col-md-5">'.$form->field($model,'local_download')->checkBox(['label'=>'']).'</span>
				</div>';
		echo '</div>';
		echo '<div class="modal-footer">';
		echo '<div class="modal-loading hidden"><b>Creating experiment <i class="fas fa-spinner fa-spin"></i></b></div>';
		echo Html::submitButton("Submit",['class'=>"btn btn-success", 'id'=>'experiment-button']);
		echo "<button type='button' class='btn btn-secondary' data-dismiss='modal'>Close</button>";
		echo '</div>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
		$form=ActiveForm::end();
		
	}
	
}

?>

