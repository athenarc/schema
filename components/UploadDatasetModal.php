<?php


namespace app\components;
use yii\helpers\Html;
use yii\helpers\Url;
use webvimark\modules\UserManagement\models\User;
use Yii;
use app\models\UploadDataset;
use yii\widgets\ActiveForm; 
use app\components\MagicSearchBox;

Yii::$app->getView()->registerJsFile('@web/js/components/uploadDataset.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

class UploadDatasetModal
{
	

	public static function addModal()
	{
		
		$username=User::getCurrentUser()['username'];
		$datasets=['Zenodo'=>'Zenodo','Helix'=>'Helix'];
		$licenses=	['notspecified'=>'License not specified',
					'CC-BY'=>'CC-BY 4.0 - Creative Commons Attribution 4.0 International',
					'CC-BY-SA'=>'CC-BY-SA 4.0 - Creative Commons Attribution-ShareAlike 4.0 International',
					'CC-BY-NC'=>'CC-BY-NC 4.0 - Creative Commons Attribution-NonCommercial 4.0 International',
					'CC-BY-NC-SA'=>'CC-BY-NC-SA 4.0 - Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International',
					'CC-ZERO'=>'CC-ZERO - Creative Commons CCZero',
					
					'other-restricted'=>'Other (restricted resource)',
					];
		$select_icon='<i class="fas fa-folder-open"></i>';
		$clear_icon='<i class="fas fa-times"></i>';
		$isystemMount='';
		$required='<span style="color:red">*</span>';
		$help_api_key='<i class="fa fa-question-circle" title="Create an account to Helix to obtain an API Key."></i>';
		$model=new UploadDataset;


		echo Html::cssFile('@web/css/components/uploadDataset.css');
		$form=ActiveForm::begin(['action'=>['filebrowser/upload-dataset'], 'method'=> 'POST', 'options' => ['enctype'=>'multipart/form-data']]);
		echo Html::hiddenInput('mountcaller',null ,['id'=>'mountcaller']);
		echo Html::hiddenInput('selectmounturl',Url::to(['software/select-mountpoint','username'=>$username]) ,['id'=>'selectmounturl']);

		echo "<div class='modal fade' tabindex='-1' role='dialog' id='upload-modal' aria-labelledby='upload-modal' aria-hidden='true'>";
		echo '<div class="modal-dialog modal-dialog-centered modal-lg modal-size" role="document">';
		echo '<div class="modal-content" >';
		echo '<div class="modal-header">';
		echo "<div class='modal-title text-center size'  id='exampleModalLongTitle'> Dataset details</div>";
		echo '</div>';
		echo '<div class="modal-body">';
		echo  '<div class="row body-row">
				 <span class="col-md-4 labels">Upload dataset to: </span>
				 <span class="col-md-8">'. $form->field($model,'provider')
				 ->dropdownList($datasets, ['class'=>'form-control provider-dropdown'])->label("").'</span>
				</div>';
		echo  '<div class="row body-row helix_field helix_hide">
					<span class="col-md-4 labels"> Title '. $required . ':</span> 
					<span class="col-md-8">'. $form->field($model,'title')->textInput(['class'=>'form-control'])->label("").'</span>
				</div>';
		echo  '<div class="row body-row helix_field helix_hide">
					<span class="col-md-4 labels"> Description '. $required . ':</span> 
					<span class="col-md-8">'. $form->field($model,'description')->textArea(['class'=>'form-control', 'column'=>10])->label("").'</span>
				</div>';
		// echo  '<div class="row body-row helix_field helix_hide">
		// 			<span class="col-md-4 labels"> Subject '. $required . ':</span> 
		// 			<span class="col-md-8">'. MagicSearchBox::widget(
  //           	['min_char_to_start' => 3, 
  //            	'expansion' => 'both', 
  //            	'suggestions_num' => 5, 
  //            	'html_params' => [ 'id' => 'user_search_box', 
  //            	'name'=>'subjects', 
  //            	'class'=>'form-control blue-rounded-textbox'],
  //            	'ajax_action' => Url::toRoute('filebrowser/auto-complete-names'),
  //            	'subjects' =>[],
  //           	]). '</span>
		// 		</div>';
		echo  '<div class="row body-row helix_field helix_hide">
					<span class="col-md-4 labels"> Creator '. $required . ' :</span> 
					<span class="col-md-8">'. $form->field($model,'creator')->textInput(['class'=>'form-control'])->label("").'</span>
				</div>';
		echo  '<div class="row body-row helix_field helix_hide">
					<span class="col-md-4 labels"> Contact Email '. $required . ':</span> 
					<span class="col-md-8">'. $form->field($model,'contact_email')->textInput(['class'=>'form-control'])->label("").'</span>
				</div>';
		echo  '<div class="row body-row helix_field helix_hide">
					<span class="col-md-4 labels"> License '. $required . ':</span> 
					<span class="col-md-8">'. $form->field($model,'license')->dropdownList($licenses,['class'=>'form-control', 'options'=>['notspecified'=>['Selected'=>true]]])->label("").'</span>
				</div>';
		echo  '<div class="row body-row helix_field helix_hide">
					<span class="col-md-4 labels"> Private :</span> 
					<span class="col-md-8">'. $form->field($model,'private')->checkBox()->label("").'</span>
				</div>';
		echo  '<div class="row body-row helix_field helix_hide">
					<span class="col-md-4 labels"> Related publication doi:</span> 
					<span class="col-md-8">'. $form->field($model,'publication_doi')->textInput(['class'=>'form-control'])->label("").'</span>
				</div>';
		echo  '<div class="row body-row">
					<span class="col-md-4 labels"> API Key '. $required. ' ' . $help_api_key . ':</span> 
					<span class="col-md-8">'. $form->field($model,'api_key')->textInput(['class'=>'form-control'])->label("").'</span>
				</div>';
		echo  '<span class="row body-row">
				<span class="col-md-4 label-dataset">Select dataset:</span>
		 		<span class="col-md-8">'.
		 		Html::textInput('dataset',$isystemMount,['id' => 'isystemmount','class'=>'mount-field','readonly'=>true,]).'&nbsp;&nbsp;' 
				. Html::a("$select_icon Select",'javascript:void(0);',['class'=>'select-mount-button btn btn-success btn-md']).'&nbsp'
		 			.Html::a("$clear_icon Clear",'javascript:void(0);',['class'=>'clear-mount-button btn btn-danger btn-md']).
		 		'</span>
			 	</span>';
	 
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

