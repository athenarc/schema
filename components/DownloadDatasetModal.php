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
use yii\helpers\Url;
use webvimark\modules\UserManagement\models\User;
use Yii;
use app\models\DownloadDataset;
use yii\widgets\ActiveForm; 



class DownloadDatasetModal
{
	public static function addModal()
	{
		$username=User::getCurrentUser()['username'];
		$datasets=['Helix'=>'Helix', 'Zenodo'=>'Zenodo', 'Url'=>'Any Url'];
		$select_icon='<i class="fas fa-folder-open"></i>';
		$clear_icon='<i class="fas fa-times"></i>';
		$help_icon='<i class="fa fa-question-circle" title="The ID of the dataset in the public repository or the URL if the '.htmlspecialchars('"Any URL"').' option is selected"></i>';
		$osystemMount='';

		$model=new DownloadDataset;


		echo Html::cssFile('@web/css/components/downloadDataset.css');
		$form=ActiveForm::begin(['action'=>['filebrowser/download-dataset'], 'method'=> 'POST']);
		echo Html::hiddenInput('mountcaller',null ,['id'=>'mountcaller']);
		echo Html::hiddenInput('selectmounturl',Url::to(['software/select-mountpoint','username'=>$username]) ,['id'=>'selectmounturl']);

		echo "<div class='modal fade' tabindex='-1' role='dialog' id='download-modal' aria-labelledby='download-modal' aria-hidden='true'>";
		echo '<div class="modal-dialog modal-dialog-centered modal-lg modal-size" role="document">';
		echo '<div class="modal-content" >';
		echo '<div class="modal-header">';
		echo "<div class='modal-title text-center size' id='exampleModalLongTitle'> Dataset details</div>";
		echo '</div>';
		echo '<div class="modal-body">';
		echo  '<div class="row body-row">
				 <span class="col-md-4">Download dataset from: </span>
				 <span class="col-md-7" >'. $form->field($model,'provider')
				 ->dropdownList($datasets, ['class'=>'form-control'])->label("").'</span>
				</div>';
		echo  '<div class="row body-row">';
		echo			"<span class='col-md-4'> Dataset id $help_icon:</span>"; 
		echo			'<span class="col-md-7">'. $form->field($model,'dataset_id')
					->textInput(['class'=>'form-control'])->label("").'</span>
				</div>';
		echo  '<span class="row body-row">
				<span class="col-md-4">Store the dataset in:</span>
		 		<span class="col-md-8">'.
		 	
				Html::textInput('osystemmount',$osystemMount,['id' => 'osystemmount','class'=>'mount-field','readonly'=>true,]).'&nbsp;&nbsp;' 
				. Html::a("$select_icon Select",'javascript:void(0);',['class'=>'select-mount-button btn btn-success btn-md']).'&nbsp'
		 			.Html::a("$clear_icon Clear",'javascript:void(0);',['class'=>'clear-mount-button btn btn-danger btn-md']).
		 		'</span>
			 	</span>';
		echo '</div>';
		echo '<div class="modal-footer">';
		// echo '<div class="modal-loading hidden"><b>Downloading files <i class="fas fa-spinner fa-spin"></i></b></div>';
		echo Html::submitButton("Download",['class'=>"btn btn-success", 'id'=>'download-button']);
		echo "<button type='button' class='btn btn-secondary' data-dismiss='modal'>Close</button>";
		echo '</div>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
		$form=ActiveForm::end();
		
	}
	
}

?>

