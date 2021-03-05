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
use app\models\RunHistory;
use app\models\Software;
use app\models\SoftwareMpi;
use app\models\Workflow;
use app\models\SoftwareInput;
use app\models\WorkflowInput;
use app\models\RoCrate;
use yii\widgets\ActiveForm; 



class ROCrateModal
{
	public static function addModal($result,$model, $fields, $software, $soft_type)
	{
		$software_icon='<i class="fas fa-question-circle" title="Software url to a public repository like dockerhub"></i>';
		$publication_icon='<i class="fas fa-question-circle" title="Include the DOI of the publication that describes the experiment"></i>';
		$download_icon='<i class="fas fa-question-circle" title="Download a local copy of the RO-Crate object to be produced"></i>';
		$edit_icon='<i class="fas fa-pencil-alt"></i>';
		$download_icon='<i class="fas fa-download"></i>';
		$required='<span style="color:red">*</span>';

		$public_url='';
		$image_url='';

		$history=$result;
		$jobid=$history->jobid;
		
		$software_id=$history->software_id;

		$disabled_fields=false;
		if(!empty($model))
		{
			$disabled_fields=true;
		}
		
		$disabled=false;
		if ((!empty($software)) && (empty($model->software_url)))
		{
			$docker=$software->docker_or_local;
        	if($docker)
        	{
        		$disabled=true;
            	$image=$software->original_image;
            	$image_url='https://hub.docker.com/r/'.$image;


        	}
        	
		}
		elseif ((!empty($software) || (!empty($workflow))) && (!empty($model->software_url)))
     	{
         		$image_url=$model->software_url;
        }
		
		

		
		
		$field_to_url=[];
        if(empty($model))
        {
            $model=new RoCrate();
        }
        else
        {
        	$inputs=json_decode($model->input, true);
        	foreach ($inputs as $input) 
		    {
		    	$field_to_url[$input['name']]=$input['url'];	
		    }
		    
		}
		
		$fieldspath=Yii::$app->params['tmpFolderPath']. "/". $jobid . "/fields.txt" ;
        $file = file_get_contents($fieldspath);
        $fields_file=json_decode($file,true);
        $fields_folder='/';
        if (!empty($history->imountpoint))
        {
        	$fields_folder.=$history->imountpoint;
        }
        elseif (!empty($history->iomountpoint))
        {
        	$fields_folder.=$history->iomountpoint;
        }
        
        

       	echo Html::cssFile('@web/css/components/roCrate.css');
		$form=ActiveForm::begin(['action'=>['software/create-experiment','jobid'=>$jobid], 'method'=> 'POST']);

		echo Html::hiddenInput('softname',$history->softname);
		echo Html::hiddenInput('softversion',$history->softversion);
		
		
		echo "<div class='modal fade' tabindex='-1' role='dialog' id='experiment-modal-$jobid' aria-labelledby='experiment-modal' aria-hidden='true'>";
		echo '<div class="modal-dialog modal-dialog-centered modal-lg modal-size" role="document">';
		echo '<div class="modal-content" >';
		echo '<div class="modal-header">';
		echo "<div class='modal-title text-center size' id='exampleModalLongTitle'>RO-Crate object details  &nbsp; &nbsp;" 
				. Html::img('@web/img/ro-crate.svg', ['width'=>'15%'])."</div>";
		echo '</div>';
		echo '<div class="rocrate-description">RO-Crate is a community effort to establish a lightweight approach to packaging research data with their metadata. For more details please visit the official '. Html::a('RO-Crate webpage','http://www.researchobject.org/ro-crate/', ['target'=>'_blank']).'.</div>';
		echo '<div class="inputs">';
		echo '<div class="modal-body modal-size">';
		echo  '<div class="row body-row">';
		echo     "<span class='col-md-7 field-row'>$soft_type public URL $required $software_icon  : </span>";
		echo		 '<span class="col-md-5" >'.$form->field($model,'software_url')->label("")->textInput(['value'=>$image_url, 'disabled'=>$disabled, 'disabled'=>$disabled_fields]).'</span>
				</div>';
		echo  '<div class="row body-row">
					<span class="col-md-7 field-row"> Public URL of the output dataset:</span> 
					<span class="col-md-5">'. $form->field($model,'output')->label("")
					->textInput(['value'=>$model->output, 'disabled'=>$disabled_fields]).'</span>';
		echo	'</div>';
		echo  "<div class='row body-row'>
					<span class='col-md-7 field-row'> Publication DOI $publication_icon :</span> 
					<span class='col-md-5'>". $form->field($model,'publication')->label("")
					->textInput(['value'=>$model->publication, 'disabled'=>$disabled_fields])."</span>
				</div>";
		echo "<div class='input-file-fields'>";
		$i=0;
				
		foreach ($fields as $field) 
		{
			
			if ($field->field_type=='File')
			{

				echo  "<div class='row body-row'>";
				echo "<span class='col-md-7 field-row'> Public URL for input $field->name $required : </span>";
				echo "<span class='col-md-5'>" . $form->field($model,"input[$field->name]")->label("")
				->textInput(['id'=>'field-' . $i, 'value'=>empty($field_to_url[$field->name])?'':$field_to_url[$field->name], 'disabled'=>$disabled_fields]) . '</span>';
				echo "<span class='col-md-12 local-file'>".empty($fields_file)?" ": "Used file: ". $fields_folder . '' . $fields_file[$field->name]."  </span>";
				echo "</div>";
				
				$i++;
			}
		}
		echo "</div>";
		echo '</div>';
		echo '</div>';
		echo '<div class="modal-footer">';
		if(!$disabled_fields)
		{
			echo Html::a("Submit",'javascript:void(0);',['class'=>"btn btn-success experiment-submit-btn", 'id'=>"submit-$jobid"]);
		}
		else
		{
			echo Html::a("Submit",'javascript:void(0);',['class'=>"btn btn-success experiment-submit-btn hidden", 'id'=>"submit-$jobid"]);
			echo "<div class='edit-buttons'>";
			echo Html::a("$download_icon Download RoCrate",['software/download-rocrate', 'jobid'=>$jobid],
				['class'=>"btn btn-success download-rocrate"]);
			echo Html::a("$edit_icon Edit RoCrate",'javascript:void(0);',
				['class'=>"btn btn-warning edit-rocrate",]);
			echo "</div>";
		}
		echo "<button type='button' class='btn btn-secondary' data-dismiss='modal'>Close</button>";
		echo '</div>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
		$form=ActiveForm::end();
		
	}
	
}

?>

