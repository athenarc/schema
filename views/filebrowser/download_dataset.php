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

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use app\components\Headers;
use webvimark\modules\UserManagement\models\User;
use app\models\DownloadDataset;


/* @var $this yii\web\View */
/* @var $model app\models\SoftwareUpload */
/* @var $form ActiveForm */




$this->title = "Download Dataset";
$submit_icon='<i class="fas fa-check"></i>';
$cancel_icon='<i class="fas fa-times"></i>';
$select_icon='<i class="fas fa-folder-open"></i>';
$clear_icon='<i class="fas fa-times"></i>';
$isystemMount='';
$required='<span style="color:red">*</span>';
$help_id='<i class="fa fa-question-circle" title="The ID of the dataset in the public repository or the URL if the '.htmlspecialchars('"Any URL"').' option is selected"></i>';
$help_dataset_folder='<i class="fa fa-question-circle" title="The dataset should be saved in a folder inside the system ."></i>';



?>


  
<?Headers::begin() ?>
<?php echo Headers::widget(
['title'=>$this->title, 
    'buttons'=>
    [ 
        ['fontawesome_class'=>$cancel_icon,'name'=> 'Cancel', 'action'=>['/filebrowser/index'],
         'options'=>['class'=>'btn btn-default'], 'type'=>'a'] 
    ],
])
?>
<?Headers::end()?>



<?php
echo Html::cssFile('@web/css/components/downloadDataset.css');
Yii::$app->getView()->registerJsFile('@web/js/components/downloadDataset.js', ['depends' => [\yii\web\JqueryAsset::className()]]);


?>




<?php
if(!empty($datasets))
{
  



  
  ?>

  <div class="row">
    <div class="col-md-4">
    <span style="color:red">*</span>Required fields
    </div>
  </div>
  <div class="row">&nbsp;</div>
  <div class="row">&nbsp;</div>
  <div class="row provider-dropdown-padding">
      <div class="col-md-4 labels">Download dataset from:</div>
      <div class="col-md-8">
        <?=Html::dropDownList('provider',null, $datasets, ['class'=>'form-control provider-dropdown'])?>
      </div>
  </div>
  <?php
  $form = ActiveForm::begin([
     'id' => 'helix-form',
     'action' => Url::to(['/filebrowser/download-dataset'])
  ]);

  echo Html::hiddenInput('mountcaller',null ,['id'=>'mountcaller']);
  echo Html::hiddenInput('selectmounturl',Url::to(['software/select-mountpoint','username'=>$username]) ,['id'=>'selectmounturl']);
  echo Html::hiddenInput('provider_name','Helix');


  ?>
  <div class="row helix_field">&nbsp;</div>
  <div class="row helix_field helix_hide">
      <div class="col-md-4 labels"> Dataset ID<?=$required?> <?=$help_id?></div> 
      <div class="col-md-8"><?=$form->field($model,'dataset_id')->textInput(['class'=>'form-control'])->label("")?></div>
  </div>
  <div class="row body-row helix_field helix_hide">
      <div class="col-md-4 label-dataset">Select dataset folder<?=$required?> <?=$help_dataset_folder?></div>
      <div class="col-md-8">
      <?=Html::textInput('dataset_helix',$isystemMount,['id' => 'helix-mount','class'=>'mount-field-helix dataset-input-size','readonly'=>true,]).'&nbsp;&nbsp;' 
      . Html::a("$select_icon Select",'javascript:void(0);',['class'=>'select-mount-button-helix btn btn-success btn-md']).'&nbsp'
      . Html::a("$clear_icon Clear",'javascript:void(0);',['class'=>'clear-mount-button-helix btn btn-danger btn-md'])?>
      <div class="col-md-8 message_folder_helix hidden" id='message-folder-helix'> Dataset folder can not be blank </div>
      </div>
  </div> 
  <div class="row helix_field helix_hide">&nbsp;</div>
  <div class="row helix_field helix_hide">&nbsp;</div>
  <div class="text-center form-group helix_field helix_hide">
              <?= Html::submitButton("$submit_icon Submit", ['class' => 'btn btn-primary', 'name'=>'helix-submit', 'id'=>'helix-submit']) ?>
  </div>

  <?php 
  $form=ActiveForm::end();
  ?>

  <?php
  $form = ActiveForm::begin([
     'id' => 'zenodo-form',
     'action' => Url::to(['/filebrowser/download-dataset'])
  ]);

  echo Html::hiddenInput('mountcaller',null ,['id'=>'mountcaller-zenodo']);
  echo Html::hiddenInput('selectmounturl-zenodo',Url::to(['software/select-mountpoint','username'=>$username]) ,['id'=>'selectmounturl-zenodo']);
  echo Html::hiddenInput('provider_name','Zenodo');
  ?>

  
  <div class="row zenodo_field zenodo_hide padding-top">
      <div class="col-md-4 labels"> Dataset ID<?=$required?> <?=$help_id?></div> 
      <div class="col-md-8"><?=$form->field($model,'dataset_id')->textInput(['class'=>'form-control'])->label("")?></div>
  </div>
  <div class="row body-row zenodo_field">
      <div class="col-md-4 label-dataset">Select dataset folder<?=$required?> <?=$help_dataset_folder?></div>
      <div class="col-md-8">
      <?=Html::textInput('dataset_zenodo',$isystemMount,['id' => 'zenodo-mount','class'=>'mount-field-zenodo
        dataset-input-size','readonly'=>true,]).'&nbsp;&nbsp;' 
      . Html::a("$select_icon Select",'javascript:void(0);',['class'=>'select-mount-button-zenodo btn btn-success btn-md']).'&nbsp'
      . Html::a("$clear_icon Clear",'javascript:void(0);',['class'=>'clear-mount-button-zenodo btn btn-danger btn-md'])?>
      <div class="col-md-8 message_folder_zenodo hidden" id='message-folder-zenodo'> Dataset folder can not be blank </div>
      </div>

  </div>
  <div class="row zenodo_field zenodo_hide">&nbsp;</div>
  <div class="row zenodo_field zenodo_hide">&nbsp;</div>
  <div class="form-group zenodo_field text-center">
              <?= Html::submitButton("$submit_icon Submit", ['class' => 'btn btn-primary', 'name'=>'zenodo-submit']) ?>
  </div>

  <?php 
  $form=ActiveForm::end();

  $form = ActiveForm::begin([
     'id' => 'url-form',
     'action' => Url::to(['/filebrowser/download-dataset'])
  ]);

  echo Html::hiddenInput('mountcaller',null ,['id'=>'mountcaller-url']);
  echo Html::hiddenInput('selectmounturl-url',Url::to(['software/select-mountpoint','username'=>$username]) ,['id'=>'selectmounturl-url']);
  echo Html::hiddenInput('provider_name','Any Url');
  ?>

  
   <div class="row url_field url_hide padding-top">
      <div class="col-md-4 labels"> Dataset URL<?=$required?> <?=$help_id?></div> 
      <div class="col-md-8"><?=$form->field($model,'dataset_id')->textInput(['class'=>'form-control'])->label("")?></div>
  </div>
  <div class="row body-row url_field url_hide">
      <div class="col-md-4 label-dataset">Select dataset folder<?=$required?> <?=$help_dataset_folder?></div>
      <div class="col-md-8">
      <?=Html::textInput('dataset_url',$isystemMount,['id' => 'url-mount','class'=>'mount-field-url
        dataset-input-size','readonly'=>true,]).'&nbsp;&nbsp;' 
      . Html::a("$select_icon Select",'javascript:void(0);',['class'=>'select-mount-button-url btn btn-success btn-md']).'&nbsp'
      . Html::a("$clear_icon Clear",'javascript:void(0);',['class'=>'clear-mount-button-url btn btn-danger btn-md'])?>
      <div class="col-md-8 message_folder_url hidden" id='message-folder-url'> Dataset folder can not be blank </div>
      </div>

  </div>
  <div class="row">&nbsp;</div>
  <div class="row">&nbsp;</div>
  <div class="form-group url_field text-center">
              <?= Html::submitButton("$submit_icon Submit", ['class' => 'btn btn-primary', 'name'=>'url-submit']) ?>
  </div>

  <?php 
  $form=ActiveForm::end();
  
}
else
{?>
  <div class="text-center"><h2>No providers found to download datasets.</h2></div>
<?php
}?>


<div class="modal fade" id="delete-modal" tabindex="-1" role="dialog" aria-labelledby="delete-modal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-body"><div class="text-center modal-message">The dataset is downloading. Please wait.</div> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <div class="text-center"><i class="fas fa-spinner fa-spin fa-5x text-center"></i></b></div>
      </div>
  </div>
  </div>
</div>