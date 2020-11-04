<?php

/**
 * View file for the execution of a docker software image.
 * 
 * @author: Kostis Zagganas
 * First version: Dec 2018
 */
namespace app\components;


use Yii;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Form;
use yii\bootstrap\Button;
use yii\captcha\Captcha;
use yii\widgets\ActiveForm;
use yii\bootstrap4\Modal;

// echo Html::CssFile('@web/css/software/run.css');
// Yii::$app->getView()->registerJs('@web/js/software/run-index.js', \yii\web\View::POS_READY);



/*
 * Register css file along with the js needed for the button functionality
 */

class ArgumentsWidget
{

    public static function show($link, $form_params, $name, $version, $jobid, $software_instructions,
            $errors, $runErrors, $podid, $machineType,$fields,$isystemMount, $osystemMount,
            $iosystemMount, $example, $hasExample, $username,$icontMount,$ocontMount,
            $iocontMount,$mountExistError,$superadmin,$jobUsage,$quotas,
            $maxMem,$maxCores,$project, $commandsDisabled, $commandBoxClass, $cluster, $outFolder, $type)
    {
    ?>		
		<div class="site-software">
		<div class="row">&nbsp;</div>
		<div class="row">&nbsp;</div>
		<div class="row" style="text-align: center;">
		<div class="col-md-12">
			<?php
			$select_icon='<i class="fas fa-folder-open"></i>';
			$clear_icon='<i class="fas fa-times"></i>';
			echo Html::hiddenInput('mountcaller',null ,['id'=>'mountcaller']);
			/*
			 * type 1 is software
			 * type 2 is software-mpi
			 */
			if($type==1 || $type==2)
			{
				if(!empty($iocontMount))
				{?>
						    
					<div class="row">
						    <div class="col-md-12">  <h3>Input/Output directory <i class="fa fa-question-circle" style="font-size:20px; cursor: pointer" title="Select a folder to mount to the <?=$iocontMount?> directory in the container.")> </i></h3> </div>
					</div>
					<div class="row">
						<div class="col-md-12">      
							<?=Html::textInput('iosystemmount',$iosystemMount,['id' => 'iosystemmount','class'=>'mount-field','readonly'=>true,])?>
			                <?=Html::a("$select_icon Select",'javascript:void(0);',['class'=>'select-mount-button btn btn-success btn-md','disabled'=>($commandsDisabled)])?>
			                <?=Html::a("$clear_icon Clear",'javascript:void(0);',['class'=>'clear-mount-button btn btn-danger btn-md','disabled'=>($commandsDisabled)])?>
						</div>
					</div>
				<?php
				}
				else
				{
				    if ( (empty($icontMount)) && (empty($ocontMount)) )
				    {?>
						<br>
							<div class="alert alert-success col-md-offset-3 col-7" role="alert">
						        Based on the provided metadata, this docker image does not require any input/output mountpoint.
						    </div>
							<?php
					}
				    else
				    {
				        if (!empty($icontMount))
				        {?>
							<div class="row">
							    <div class="col-md-12">  <h3>Input directory <i class="fa fa-question-circle" style="font-size:20px; text-align: center" title="Select a folder to mount to the <?=$icontMount?> directory in the container.")> </i></h3> </div>
						    </div>
						    <div class="row">
							    <div class="col-md-12">      
									<?=Html::textInput('isystemmount',$isystemMount,['id' => 'isystemmount','class'=>'mount-field','readonly'=>true,])?>
									<?=Html::a("$select_icon Select",'javascript:void(0);',['class'=>'select-mount-button btn btn-success btn-md','disabled'=>($commandsDisabled)])?>
									<?=Html::a("$clear_icon Clear",'javascript:void(0);',['class'=>'clear-mount-button btn btn-danger btn-md','disabled'=>($commandsDisabled)])?>
							    </div>
						    </div>
						 <?php
				         }
				         if (!empty($ocontMount))
				         {
						 ?>
						    <div class="row">
							    <div class="col-md-12">  <h3>Output directory <i class="fa fa-question-circle" style="font-size:20px" title="Select a folder to mount to the <?=$ocontMount?> directory in the container.")> </i></h3> </div>
						    </div>
						    <div class="row">
							    <div class="col-md-12">      
							    <?=Html::textInput('osystemmount',$osystemMount,['id' => 'osystemmount','class'=>'mount-field','readonly'=>true,])?>
								<?=Html::a("$select_icon Select",'javascript:void(0);',['class'=>'select-mount-button btn btn-success btn-md','disabled'=>($commandsDisabled)])?>
								<?=Html::a("$clear_icon Clear",'javascript:void(0);',['class'=>'clear-mount-button btn btn-danger btn-md','disabled'=>($commandsDisabled)])?>
							    </div>
						    </div>
						   	<?php
						   	}
						}

					}?>
				    <?=Html::hiddenInput('selectmounturl',Url::to(['software/select-mountpoint','username'=>$username]) ,['id'=>'selectmounturl'])?>
					<?php
					if ($mountExistError)
					{?>
						<div class="row">One of the folders selected as a mountpoint in the previous run does not exist anymore.
						<br />Please select another folder.</div>
					</div>
					<?php
					}?>




					<?php
					/*
					 * This is the non active form, that does not really submit.
					 * The content of the fields is concatenated with JS, pasted
					 * in the command box and the active form is submitted.
					 */
					?>
					<div id="non-active-run-form">
					<?=Html::hiddenInput('jobid', $jobid,['id'=>'hidden_jobid_input'])?>
					<?=Html::hiddenInput('name', $name,['id'=>'hidden_name_input'])?>
					<?=Html::hiddenInput('version', $version,['id'=>'hidden_version_input'])?>
					<?php 
					if($cluster)
					{?>
						<?=Html::hiddenInput('cluster', $cluster,['id'=>'hidden_cluster_input'])?>
					<?php
					}?>
					<?=Html::hiddenInput('podid', $podid,['id'=>'hidden_podid_input'])?>
					<?=Html::hiddenInput('machineType', $machineType,['id'=>'hidden_machineType_input'])?>
					<?=Html::hiddenInput('example', $example,['id'=>'hidden_example_input'])?>
					<?=$hasExample ? Html::hiddenInput('has_example','',['id'=>'has_example']) : ''?>
					<?php
						
					if (!empty($fields))
					{?>

					<div class="row">
					<div class="col-md-12"><h3>Arguments <i class="fa fa-question-circle" style="font-size:20px; cursor: pointer" title="Select arguments for execution.")></i></h3></div>
					</div> 
					<?php

					    $default_icon='<i class="fas fa-magic"></i>';
					    $default_title='Fill field with default value.';
					    $clear_file_icon='<i class="fas fa-times"></i>';
					    $clear_file_title='Clear field.';
					    $index=0;
					    foreach ($fields as $field)
					    {?>
					        <div class="row">
					            <div class="col-md-offset-3 col-md-3" style="text-align: right;"><?=Html::label($field->name,null,[])?>
					            	
					            </div>
					   			 <?php
					            if ($field->field_type=='boolean')
					            {?>

					                <div class="col-md-3" style="text-align: left;">
					                    <?=Html::checkbox('field-' . $index,$field->value,['readonly'=>$commandsDisabled,'class'=>$commandBoxClass, 'id'=>'field-' . $index, 'uncheck'=>"0"])?>
					                </div>

					            <?php
					            }
					            else if ($field->field_type=='File')
					            {
					                if ($field->is_array)
					                {
					                    $slcbtnLink='software/select-file-multiple';
					                    $select_file_icon='<i class="fas fa-copy"></i>';
					                    $select_file_title='Select files';
					                }
					                else
					                {
					                    $slcbtnLink='software/select-file';
					                    $select_file_icon='<i class="fas fa-file"></i>';
					                    $select_file_title='Select file';
					                }

					            ?>
					                <div class="col-md-3" style="text-align: left;">
					                    <?=Html::textInput('field-' . $index, $field->value,['readonly'=>true,'class'=>'file_field input_field ' . $commandBoxClass, 'id'=>'field-' . $index])?>
					                    <?=Html::a($select_file_icon,'javascript:void(0);',['disabled'=>$commandsDisabled, 'class'=>"btn btn-success select-file-btn",'title'=>$select_file_title])?>
					                    <?=Html::a($clear_file_icon,'javascript:void(0);',['disabled'=>$commandsDisabled, 'class'=>'btn btn-danger clear-file-btn','title'=>$clear_file_title])?>
					                    <?=Html::hiddenInput('hidden_select_file_url', Url::to([$slcbtnLink, 'caller'=>'field-' . $index]), ['class'=>'hidden_select_file_url'])?>
					                </div>
					            <?php
					            }
					            else if ($field->field_type=='Directory')
					            {
					                if ($field->is_array)
					                {
					                    $slcbtnLink='software/select-folder-multiple';
					                    $select_file_icon='<i class="far fa-folder"></i>';
					                    $select_file_title='Select directories';

					                }
					                else
					                {
					                    $slcbtnLink='software/select-folder';
					                    $select_file_icon='<i class="far fa-folder"></i>';
					                    $select_file_title='Select directory';
					                }
					            ?>
					                <div class="col-md-3" style="text-align: left;">
					                    <?=Html::textInput('field-' . $index,$field->value,['readonly'=>true,'class'=>'folder_field input_field ' . $commandBoxClass, 'id'=>'field-' . $index])?>
					                    <?=Html::a($select_file_icon,'javascript:void(0);',['disabled'=>$commandsDisabled, 'class'=>"btn btn-success select-folder-btn",'title'=>$select_file_title])?>
					                    <?=Html::a($clear_file_icon,'javascript:void(0);',['disabled'=>$commandsDisabled, 'class'=>'btn btn-danger clear-folder-btn','title'=>$clear_file_title])?>
					                    <?=Html::hiddenInput('hidden_select_file_url', Url::to([$slcbtnLink, 'caller'=>'field-' . $index]), ['class'=>'hidden_select_folder_url'])?>
					                </div>
					            <?php
					            }       
					            else
					            {
					                if (!$field->is_array)
					                {

					                ?>
					                    <div class="col-md-3" style="text-align: left;">
					                        <?=Html::textInput('field-' . $index,$field->value,['readonly'=>$commandsDisabled,'class'=>'input_field ' . $commandBoxClass, 'id'=>'field-' . $index])?>
					                        <?=(($field->field_type!='file') && (!empty($field->default_value))) ? Html::a($default_icon,'javascript:void(0);',['id'=>'default-values', 'class'=>'btn btn-basic btn-default-values','title'=>$default_title]) : ''?>
					                    </div>
					                    <?=Html::hiddenInput('default_field_values[]',$field->default_value,['readonly'=>true, 'class'=>'hidden_default_value'])?>  
					                
					                <?php
					                }
					                else
					                {
					                    $fill_array_icon='<i class="fas fa-table"></i>';
					                    $fill_array_title='Fill array field'
					                ?>

					                    <div class="col-md-3" style="text-align: left;">
					                        <?=Html::textInput('field-' . $index,$field->value,['readonly'=>true,'class'=>'array_field input_field ' . $commandBoxClass, 'id'=>'field-' . $index])?>
					                        <?=Html::a($fill_array_icon,'javascript:void(0);',['disabled'=>$commandsDisabled, 'class'=>"btn btn-success fill-array-field-btn",'title'=>$fill_array_title])?>
					                    <?=Html::a($clear_file_icon,'javascript:void(0);',['disabled'=>$commandsDisabled, 'class'=>'btn btn-danger clear-folder-btn','title'=>$clear_file_title])?>
					                    <?=Html::hiddenInput('hidden_fill_array_field_url', Url::to(['software/fill-array-field', 'caller'=>'field-' . $index]), ['class'=>'hidden_fill_array_field_url'])?>
					                    </div>
					                
					                <?php
					                }
					            }   
					            ?>   
					        	</div>
					    	<?php
					    	$index++;
					    }
					    echo Html::hiddenInput('fieldsNum', count($fields),['id'=>'hidden_fieldsNum']);
					}
					else
					{
					?>
					<br>
					    <div class="row">
					    <div class="col-md-12"><h3>Arguments <i class="fa fa-question-circle" style="font-size:20px; cursor: pointer" title="Select arguments for execution.")></i> </h3></div>
					    </div> 

					    <?php
					    	echo '<div class="row">';
					        echo '<div class="alert alert-success col-md-offset-3 col-md-6" role="alert" style="width:50%;">';
					        echo "<div class='row' style='padding-left:15px;'>Based on the provided CWL description, this docker image does not require arguments. </div>";
					        echo '</div></div>';
					        ?>

					    <?php
					}?>
				</div>

		<?php
		}
		/*
		 * type 3 is workflows
		 */
		else
		{?>
			<div class="row">
				<div class="col-md-12">	<h3>Output directory <i class="fa fa-question-circle" style="font-size:20px" title="Select the folder where the outputs of the workflow will be placed")> </i></h3> </div>
			</div>
			<div class="row">
				<div class="col-md-12">		
					<?=Html::textInput('outFolder',$outFolder,['id' => 'outFolder','class'=>'mount-field','readonly'=>true,])?>
	            	<?=Html::a("$select_icon Select",'javascript:void(0);',['class'=>'select-output-button btn btn-success btn-md','disabled'=>($commandsDisabled)])?>
					<?=Html::a("$clear_icon Clear",'javascript:void(0);',['class'=>'clear-output-button btn btn-danger btn-md','disabled'=>($commandsDisabled)])?>
				</div>
			</div>
			<?=Html::hiddenInput('selectoutputurl',Url::to(['workflow/select-output','username'=>$username]) ,['id'=>'selectoutputurl'])?>
		
				<div id="non-active-run-form">
				<?=Html::hiddenInput('jobid', $jobid,['id'=>'hidden_jobid_input'])?>
				<?=Html::hiddenInput('name', $name,['id'=>'hidden_name_input'])?>
				<?=Html::hiddenInput('version', $version,['id'=>'hidden_version_input'])?>
				<?=Html::hiddenInput('example', $example,['id'=>'hidden_example_input'])?>
				<?=$hasExample ? Html::hiddenInput('has_example','',['id'=>'has_example']) : ''?>
				<?php
				/* 
				 * TODO PHP code here
				 */
			
				if (!empty($fields))
				{?>

				<div class="row">
				<div class="col-md-12"><h3>Arguments <i class="fa fa-question-circle" style="font-size:20px; cursor: pointer" title="Select arguments for execution.")></i></h3></div>
				</div> 
				<?php

					$default_icon='<i class="fas fa-magic"></i>';
					$default_title='Fill field with default value.';
					$select_file_title='Select file.';
					$clear_file_title='Clear field.';
					$index=0;
					foreach ($fields as $field)
					{
					?>
						<div class="row">
							<div class="col-md-5" style="text-align: right;"><?=Html::label($field->name,null,[])?></div>
					<?php
							if ($field->field_type=='boolean')
							{
					?>
								<div class="col-md-6" style="text-align: left;">
									<?=Html::checkbox('field-' . $index,$field->value,['readonly'=>$commandsDisabled,'class'=>$commandBoxClass, 'id'=>'field-' . $index, 'uncheck'=>"0"])?>
								</div>
								

							<?php
							}
							else if ($field->field_type=='enum')
							{
								// print_r($field->dropdownValues);
								// exit(0);
							?>
								<div class="col-md-6" style="text-align: left;">
									<?=Html::dropDownList('field-' . $index, $field->dropdownSelected, $field->dropdownValues ,['readonly'=>$commandsDisabled,'class'=>$commandBoxClass . ' field-dropdown', 'id'=>'field-' . $index])?>
								</div>
							<?php
							}
							else if ($field->field_type=='File')
							{
								if ($field->is_array)
				                {
				                    $slcbtnLink='workflow/select-file-multiple';
				                    $select_file_icon='<i class="fas fa-copy"></i>';
				                    $select_file_title='Select files';
				                }
				                else
				                {
				                    $slcbtnLink='workflow/select-file';
				                    $select_file_icon='<i class="fas fa-file"></i>';
				                    $select_file_title='Select file';
				                }
					        ?>
				                <div class="col-md-6" style="text-align: left;">
				                    <?=Html::textInput('field-' . $index, $field->value,['readonly'=>true,'class'=>'file_field input_field ' . $commandBoxClass, 'id'=>'field-' . $index])?>
				                    <?=Html::a($select_file_icon,'javascript:void(0);',['disabled'=>$commandsDisabled, 'class'=>"btn btn-success select-file-btn",'title'=>$select_file_title])?>
				                    <?=Html::a($clear_icon,'javascript:void(0);',['disabled'=>$commandsDisabled, 'class'=>'btn btn-danger clear-file-btn','title'=>$clear_file_title])?>
				                    <?=Html::hiddenInput('hidden_select_file_url', Url::to([$slcbtnLink, 'caller'=>'field-' . $index]), ['class'=>'hidden_select_file_url'])?>
				                </div>
							<?php
							}
							else if ($field->field_type=='Directory')
							{
								if ($field->is_array)
								{
									$slcbtnLink='workflow/select-folder-multiple';
				                    $select_file_icon='<i class="fas fa-folder"></i>';
				                    $select_file_title='Select directories';

								}
								else
								{
									$slcbtnLink='workflow/select-folder';
				                    $select_file_icon='<i class="fas fa-folder"></i>';
				                    $select_file_title='Select directory';
								}
							?>
								<div class="col-md-6" style="text-align: left;">
									<?=Html::textInput('field-' . $index,$field->value,['readonly'=>true,'class'=>'input_field ' . $commandBoxClass, 'id'=>'field-' . $index])?>
									<?=Html::a($select_file_icon,'javascript:void(0);',['disabled'=>$commandsDisabled, 'class'=>'btn btn-success select-folder-btn','title'=>$select_file_title])?>
									<?=Html::a($clear_icon,'javascript:void(0);',['disabled'=>$commandsDisabled, 'class'=>'btn btn-danger clear-file-btn','title'=>$clear_file_title])?>
									<?=Html::hiddenInput('hidden_select_file_url', Url::to([$slcbtnLink, 'caller'=>'field-' . $index]), ['class'=>'hidden_select_folder_url'])?>
								</div>
							<?php
							}	
							else
							{
								if (!$field->is_array)
				                {

				                ?>
				                    <div class="col-md-6" style="text-align: left;">
				                        <?=Html::textInput('field-' . $index,$field->value,['readonly'=>$commandsDisabled,'class'=>'input_field ' . $commandBoxClass, 'id'=>'field-' . $index])?>
				                        <?=(($field->field_type!='file') && (!empty($field->default_value))) ? Html::a($default_icon,'javascript:void(0);',['id'=>'default-values', 'class'=>'btn btn-basic btn-default-values','title'=>$default_title]) : ''?>
				                    </div>
				                    <?=Html::hiddenInput('default_field_values[]',$field->default_value,['readonly'=>true, 'class'=>'hidden_default_value'])?>  
				                
				                <?php
				                }
				                else
				                {
				                    $fill_array_icon='<i class="fas fa-table"></i>';
				                    $fill_array_title='Fill array field'
				                ?>

				                    <div class="col-md-6" style="text-align: left;">
				                        <?=Html::textInput('field-' . $index,$field->value,['readonly'=>true,'class'=>'array_field input_field ' . $commandBoxClass, 'id'=>'field-' . $index])?>
				                        <?=Html::a($fill_array_icon,'javascript:void(0);',['disabled'=>$commandsDisabled, 'class'=>"btn btn-success fill-array-field-btn",'title'=>$fill_array_title])?>
				                    <?=Html::a($clear_icon,'javascript:void(0);',['disabled'=>$commandsDisabled, 'class'=>'btn btn-danger clear-folder-btn','title'=>$clear_file_title])?>
				                    <?=Html::hiddenInput('hidden_fill_array_field_url', Url::to(['software/fill-array-field', 'caller'=>'field-' . $index]), ['class'=>'hidden_fill_array_field_url'])?>
				                    </div>
				                
				                <?php
				                }
							}	
				            ?>   
						</div>
					<?php
					$index++;
					}
					echo Html::hiddenInput('fieldsNum', count($fields),['id'=>'hidden_fieldsNum']);
				}
				else
				{
				?>
				<br>
					<div class="row">
					<div class="col-md-12"><h3>Arguments <i class="fa fa-question-circle" style="font-size:20px; cursor: pointer" title="Select arguments for execution.")></i></h3></div>
					</div> 

					<?php
						echo '<div class="alert alert-success row col-md-offset-3 col-7" role="alert">';
						echo "Based on the provided CWL description, this workflow does not require inputs.";
						echo '</div>';
						?>

					<?php
				}?>
			</div>
		<?php
		}	
	}

}?>		
