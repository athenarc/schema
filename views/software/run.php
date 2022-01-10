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

use Yii;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Form;
use yii\bootstrap\Button;
use yii\captcha\Captcha;
use yii\widgets\ActiveForm;
use yii\bootstrap4\Modal;
use app\components\InstructionsModal;
use app\components\Headers;
use app\components\RunFormWidget;
use app\components\JobResourcesWidget;

echo Html::CssFile('@web/css/software/run.css');
$this->registerJsFile('@web/js/software/run-index.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

$this->title = "New job ($name v.$version) ";

$commandsDisabled= ($jobid!='') ? true : false;
if($commandsDisabled)
{
$commandBoxClass= 'disabled-box';
}
else
{
$commandBoxClass='';
}

if ($hasExample)
{
$exampleBtnLink='javascript:void(0);';
}
else
{
$exampleBtnLink=null;
}

/* 
* Show tabs
*/
$back_icon='<i class="fas fa-arrow-left"></i>';
$clear_file_icon='<i class="fas fa-times"></i>';
$instructions_icon='<i class="fa fa-file aria-hidden="true"></i>';

Headers::begin() ?>
<?php echo Headers::widget(
['title'=>$this->title, 
    'buttons'=>
    [
        ['fontawesome_class'=>$back_icon,'name'=> 'Available Software', 'action'=>['/software/index'],
        'options'=>['class'=>'btn btn-default'], 'type'=>'a'], 
        
    ],
])
?>
<?php Headers::end()?>


<?php 
ActiveForm::begin($form_params);
?>

<div class="site-software">
    <div class="row">&nbsp;</div>
    <div class="row">&nbsp;</div>
    <div class="row" style="text-align: center;">
        <div class="col-md-12">

            <?php           
            RunFormWidget::showOutputField($commandsDisabled,$outFolder,$username,$type);
            RunFormWidget::showHiddenFields($jobid,$name,$version,$example, $hasExample);
            RunFormWidget::showArguments($fields,$type,$commandBoxClass,$commandsDisabled);
            RunFormWidget::showResources($quotas,$maxCores,$maxMem,$commandsDisabled,$commandBoxClass);
            RunFormWidget::showRunButtons($superadmin,$username,$uploadedBy,$hasExample,$commandsDisabled,$type,$name,$version,$software_instructions);

            ?>
        </div>
    </div>
<?php
ActiveForm::end();
    

?>
    

		<div id="error-report">
		    <?php 
		    if (!empty($errors))
		    {
		        echo "<br />";
		        echo Html::label("Schedule errors:");
		        echo "<br />";

		        foreach ($errors as $error)
		        {
		            echo $error . "<br />";
		        }
		    }
		    if (!empty($runErrors))
		    {
		        echo "<br />";
		        echo Html::label("Kubernetes errors:");
		        echo "<br />";
		        echo $runErrors;

		    }
		    ?>
		</div>
		<div id="pod-logs"></div>
		<?php
		if ($podid!='')
	    {
	        echo "<div id='initial-status'>";
			echo "<h3>Runtime Info:</h3>";
			echo "<b>Status:</b> <div class='status-init'>Initializing</div><br />";
	        echo $this->registerJsFile('@web/js/software/logs.js', ['depends' => [\yii\web\JqueryAsset::className()]] );
	        
	    }?>
		<br />
	</div>
</div>    

<!-- <div class="name hidden"><?=$name?></div>
<div class="version hidden"><?=$version?></div> -->
<?php
// InstructionsModal::addModal($name, $version, $software_instructions);
?>



