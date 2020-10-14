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


class JobResourcesWidget
{

  public static function show($link, $form_params, $name, $version, $jobid, $software_instructions,
            $errors, $runErrors, $podid, $machineType,
            $fields,$isystemMount, $osystemMount,
            $iosystemMount, $example, $hasExample,
            $username,$icontMount,$ocontMount,
            $iocontMount,$mountExistError,
            $superadmin,$jobUsage,$quotas,
            $maxMem,$maxCores,$project, $commandsDisabled, $commandBoxClass, $processes, $pernode, $type)
  {?>
            
            <div class="row">
    			<div class="col-md-12" style="padding-left: 10px;">
    				<h3>Job resources <i class="fa fa-question-circle" style="font-size:20px; cursor: pointer" title="Select job resources for execution.")></i></h3>
    			</div>
    			<!-- <div class="row">
    			<div class="col-md-6"><b class="quotas-line">Relevant Project:</b>&nbsp; <?=$project?> (<?=$quotas['num_of_jobs']-$jobUsage?> remaining jobs)&nbsp;<?=Html::a('Change project',['software/index'])?>
    			</div>
    			</div> -->
    		</div>
    		
    			<div class="row">
    				<div class="col-md-12">
    			    	<b class="quotas-line col-md-6" style="text-align: right; margin-bottom: 5px;">CPU cores:</b> &nbsp;

    			    <div class="col-md-3" style="text-align: left;">	 <?=Html::textInput('cores',$maxCores,['id' => 'cores','readonly'=>$commandsDisabled,'class'=>"$commandBoxClass"])?> &nbsp; out of <?=$quotas['cores']?>
    				</div>
    				</div>
    			</div>
    		<div class="row">
    				<div class="col-md-12">
    				<b class="quotas-line col-md-6" style="text-align: right;">Memory (in GBs):</b> &nbsp; 
    				<div class="col-md-3" style="text-align: left;"><?=Html::textInput('memory',$maxMem,['id' => 'memory','readonly'=>$commandsDisabled,'class'=>"$commandBoxClass"])?> &nbsp; out of <?=$quotas['ram']?> 
    				</div>
    				</div>
    		</div> 
            <?php //Software-MPi

            if($processes)
            {?>
            <div class="row">
                <div class="col-md-12">
                <b class="quotas-line col-md-6" style="text-align: right;">Open MPI processes:</b> &nbsp; 
                <div class="col-md-3" style="text-align: left;"><?=Html::textInput('processes',$processes,['id' => 'processes','readonly'=>$commandsDisabled,'class'=>"$commandBoxClass inputbox"])?>
                </div>
                </div>
            </div>
            <?php
            }
            if($pernode)
            {?>
            <div class="row">
                <div class="col-md-12">
                    <b class="quotas-line col-md-6" style="text-align: right;">Processes per Open MPI node (max 7):</b> &nbsp;  
                <div class="col-md-3" style="text-align: left;">
                <?=Html::textInput('pernode',$pernode,['id' => 'pernode','readonly'=>$commandsDisabled,'class'=>"$commandBoxClass inputbox"])?>
                </div>
                </div>
            </div>
            <?php
            }?>
        <div class="row">&nbsp;</div>
    		

    	</div>
    	</div>

    <?php
    
    /*
     * Run, Run example and Cancel buttons.
     */
    $classButtonHidden='';
    if($type==1 and $type==2)
    {
        if ($mountExistError)
        {
        $classButtonHidden='hidden-element';
        }
    }

    $play_icon='<i class="fas fa-play"></i>';

    $instructions_icon='<i class="fa fa-file aria-hidden="true" style="color:white"></i>';




    ?>

    <!-- <div class="run-button-container"> <i class="fa fa-play-circle", style="font-size:30px;color:green;background-color:white">
      <?=Html::a('Run','javascript:void(0);',['id'=>'software-start-run-button', 'style'=>"color: rgb(0,200,0)"])?> 
      </i>

        
    </div> -->

    <div class="row">
        <div class="run-button-container col-md-offset-4 col-md-1" style="text-align: right;"><?=Html::a("$play_icon Run",'javascript:void(0);',['id'=>'software-start-run-button', 'class'=>"btn btn-success btn-md $classButtonHidden",'disabled'=>($commandsDisabled)])?></div>
    <?php

    if(!empty($icontMount) || !empty($ocontMount) || !empty($iocontMount))
    {

    ?>

        <div class="run-button-container col-md-2" style="text-align: center;"><?=Html::a("$play_icon Run example",'javascript:void(0);',['id'=>'software-run-example-button', 'class'=>"btn btn-success btn-md",'disabled'=>((!$hasExample) || $commandsDisabled)])?></div>
        <div class="instructions col-md-1" style="margin-right: 55px; padding-left: 10px;"><?=Html::a("$instructions_icon <span style='color:white'>Instructions</span>",null,['id'=>'software-instructions', 'data-toggle'=>'modal', 
    												'data-target'=>"#per", 'class'=>'btn btn-secondary btn-md instructions-modal'])?></div>


    <?php

        if ((($superadmin) || ($username==$uploadedBy)) && (!$hasExample))
        {

            if ($commandsDisabled)
            {
                $addExampleHidden="add-example-link-hidden";
            }
            else
            {
                $addExampleHidden="";
            }
        
        	if($pernode)
        	{?>
    		<div class="add-example-link col-md-offset-5 col-md-2 <?=$addExampleHidden?>" style="text-align: center"><?=Html::a('Add example',['/software-mpi/add-example','name'=>$name, 'version' =>$version],['id'=>'software-add-example-button', 'class'=>'btn btn-link'])?></div>
        	<?php
        	}
        	elseif($mountExistError==1)
        	{?>
            <div class="add-example-link col-md-offset-5 col-md-2 <?=$addExampleHidden?>" style="text-align: center"><?=Html::a('Add example',['/workflow/add-example','name'=>$name, 'version' =>$version],['id'=>'software-add-example-button', 'class'=>'btn btn-link'])?></div>
            <?php
        	}
        	else
        	{?>
            	<div class="add-example-link col-md-offset-5 col-md-2 <?=$addExampleHidden?>" style="text-align: center"><?=Html::a('Add example',['/software/add-example','name'=>$name, 'version' =>$version],['id'=>'software-add-example-button', 'class'=>'btn btn-link'])?></div>
            <?php
        	}
        }
    }
    $cancel_icon='<i class="fas fa-times"></i>';
    ?>

		<div class="cancel-button-container col-md-1"><?=Html::a("$cancel_icon Cancel ",'javascript:void(0);',['id'=>'software-cancel-button', 'class'=>'btn btn-danger'])?></div>
    </div>




    <!-- </div>


    </div> -->

    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>

    <div class="modal fade" id="per" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content" style="width:450px;">
          <div class="modal-header">
            <h5 class="modal-title text-center" id="exampleModalLabel">Instructions</h5>
          </div>
          <div class="modal-body">
          	<div class="row">
               <div class="col-md-12 text-center" style="padding-bottom: 10px;"><?=empty($software_instructions)?'Instructions not available': $software_instructions ?></div>
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"> Close </i></button>
            </div>
       	 </div>
      	</div>
      </div>
    </div>
<?php
  }
}?>
