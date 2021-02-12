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
use app\models\UploadDataset;
use app\components\MagicSearchBox;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $model app\models\SoftwareUpload */
/* @var $form ActiveForm */




$this->title = "Upload Dataset";
$submit_icon='<i class="fas fa-check"></i>';
$cancel_icon='<i class="fas fa-times"></i>';
$select_icon='<i class="fas fa-folder-open"></i>';
$clear_icon='<i class="fas fa-times"></i>';
$isystemMount='';
$required='<span style="color:red">*</span>';
$help_api_key_helix='<i class="fa fa-question-circle" title="Create an account to Helix to obtain an API Key."></i>';
$help_api_key_zenodo='<i class="fa fa-question-circle" title="Create an account to Helix to obtain an API Key."></i>';
$help_private='<i class="fa fa-question-circle" title="Tick the box if you do not want the dataset to appear as public."></i>';
$help_dataset_folder='<i class="fa fa-question-circle" title="The dataset should be saved in a folder inside the system ."></i>';
$help_creator_names_helix='<i class="fa fa-question-circle" title="You can separate multiple creator names with a comma."></i>';
$help_affiliations_helix='<i class="fa fa-question-circle" title="You can separate multiple creator affiliations with a comma."></i>';
$help_email_helix='<i class="fa fa-question-circle" title="You must specify only one contact point"></i>';
$help_doi_helix='<i class="fa fa-question-circle" title="You can separate multiple publication DOIs with a comma"></i>';
$subjects_link=Html::a('subjects', Url::to('https://hellenicdataservice.gr/project/page/subjects'), ['target'=>'blank']);
$help_doi_zenodo='<i class="fa fa-question-circle" title="Optional. Did your publisher already assign a DOI to your upload? If not, leave the field empty and we will register a new DOI for you. A DOI allows others to easily and unambiguously cite your upload. Please note that it is NOT possible to edit a Zenodo DOI once it has been registered by us, while it is always possible to edit a custom DOI"></i>';



?>

<!--  // $form=ActiveForm::begin(['action'=>['filebrowser/upload-dataset'], 'method'=> 'POST', 'options' => ['enctype'=>'multipart/form-data']]); -->
  
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
echo Html::cssFile('@web/css/components/uploadDataset.css');
Yii::$app->getView()->registerJsFile('@web/js/components/uploadDataset.js', ['depends' => [\yii\web\JqueryAsset::className()]]);


?>




<?php
if(!empty($datasets))
{
  $form = ActiveForm::begin([
     'id' => 'helix-form',
     'action' => Url::to(['/filebrowser/upload-dataset-helix'])
  ]);

  echo Html::hiddenInput('mountcaller',null ,['id'=>'mountcaller']);
  echo Html::hiddenInput('selectmounturl',Url::to(['software/select-mountpoint','username'=>$username]) ,['id'=>'selectmounturl']);
  
  ?>

  <div class="row">
    <div class="col-md-4">
    <span style="color:red">*</span>Required fields
    </div>
  </div>
  <div class="row">&nbsp;</div>
  <div class="row">&nbsp;</div>
  <div class="row provider-dropdown-padding">
      <div class="col-md-4 labels">Upload dataset to</div>
      <div class="col-md-8">
        <?=Html::dropDownList('provider',null, $datasets, ['class'=>'form-control provider-dropdown'])?>
      </div>
  </div>
  <div class="row helix_field helix_hide">
      <div class="col-md-4 labels"> Title<?=$required?></div> 
      <div class="col-md-8"><?=$form->field($model_helix,'title')->textInput(['class'=>'form-control'])->label("")?></div>
  </div>
  <div class="row  helix_field helix_hide">
      <div class="col-md-4 labels"> Description<?=$required?></div> 
      <div class="col-md-8"><?=$form->field($model_helix,'description')->textArea(['class'=>'form-control', 'column'=>10])->label("")?></div>
  </div>
  <div class="row subject-padding helix_field helix_hide">
      <div class="col-md-4 labels"> Subject<?=$required?>  </div>           
      <div class="col-md-8"><?=MagicSearchBox::widget(
            ['min_char_to_start' => 3, 
                'expansion' => 'both', 
                'suggestions_num' => 5, 
                'html_params' => [ 'id' => 'user_search_box', 
                'name'=>'subjects', 
                'class'=>'form-control blue-rounded-textbox field-upload-dataset-helix'],
                'ajax_action' => Url::toRoute('filebrowser/auto-complete-subjects'),
                'subjects' =>[],
            ])?>
      </div>
      <div class="col-md-4 subject-help">You can find the detailed classification list at <?=$subjects_link?></div>
      <div class="col-md-8 message_subject hidden" id='message-subject'> Subject can not be blank </div>
  </div>
  <div class="row body-row helix_field helix_hide">
      <div class="col-md-4 labels"> Creator name(s)<?=$required?> <?=$help_creator_names_helix?></div> 
      <div class="col-md-8"><?=$form->field($model_helix,'creator')->textInput(['class'=>'form-control'])->label("")?></div>
  </div>
  <div class="row body-row helix_field helix_hide">
      <div class="col-md-4 labels"> Creator affiliation(s)<?=$required?> <?=$help_affiliations_helix?></div> 
      <div class="col-md-8"><?=$form->field($model_helix,'affiliation')->textInput(['class'=>'form-control'])->label("")?></div>
  </div>
  <div class="row body-row helix_field helix_hide">
          <div class="col-md-4 labels"> Contact email<?=$required?> <?=$help_email_helix?></div> 
          <div class="col-md-8"><?=$form->field($model_helix,'contact_email')->textInput(['class'=>'form-control'])->label("")?>
          </div>
  </div>
  <div class="row body-row helix_field helix_hide">
          <div class="col-md-4 labels"> License<?=$required?></div> 
          <div class="col-md-8"><?=$form->field($model_helix,'license')->dropdownList($helix_defaults['helix_licenses'],['class'=>'form-control', 'options'=>['notspecified'=>['Selected'=>true]]])->label("")?></div>
  </div>
  <div class="row body-row helix_field helix_hide">
          <div class="col-md-4 labels"> Private <?=$help_private?></div> 
          <div class="col-md-8"><?=$form->field($model_helix,'private')->checkBox(['label'=>''])->label("")?>
          </div>
  </div>
  <div class="row body-row helix_field helix_hide">
      <div class="col-md-4 labels"> Related publication DOIs <?=$help_doi_helix?></div> 
      <div class="col-md-8"> <?=$form->field($model_helix,'publication_doi')->textInput(['class'=>'form-control'])->label("")?></div>
  </div>
  <div class="row body-row helix_field helix_hide">
      <div class="col-md-4"> API key<?=$required?> <?=$help_api_key_helix?></div> 
      <div class="col-md-8"><?=$form->field($model_helix,'api_key')->textInput(['class'=>'form-control'])->label("")?>
      </div>
  </div>
  <div class="row body-row helix_field helix_hide">
      <div class="col-md-4 label-dataset">Select dataset folder <?=$required?> <?=$help_dataset_folder?></div>
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
     'action' => Url::to(['/filebrowser/upload-dataset-zenodo'])
  ]);

  echo Html::hiddenInput('mountcaller',null ,['id'=>'mountcaller-zenodo']);
  echo Html::hiddenInput('selectmounturl-zenodo',Url::to(['software/select-mountpoint','username'=>$username]) ,['id'=>'selectmounturl-zenodo']);
  ?>

  
    <div class="row zenodo_field zenodo">
      <div class="col-md-4 labels"> Title<?=$required?></div> 
      <div class="col-md-8"><?=$form->field($model_zenodo,'title')->textInput(['class'=>'form-control'])->label("")?></div>
  </div>
  <div class="row  zenodo_field zenodo">
      <div class="col-md-4 labels"> Description<?=$required?></div> 
      <div class="col-md-8"><?=$form->field($model_zenodo,'description')->textArea(['class'=>'form-control', 'column'=>10])->label("")?></div>
  </div>
  <div class="row body-row zenodo_field zenodo">
          <div class="col-md-4 labels"> Upload type<?=$required?></div> 
          <div class="col-md-8"><?=$form->field($model_zenodo,'upload_type')->dropdownList($zenodo_defaults['zenodo_upload_type'],['class'=>'form-control upload-type','options'=>['poster'=>['Selected'=>true]]])->label("")?></div>
  </div>
  <div class="row body-row zenodo_field zenodo hidden" id='publication-type'>
          <div class="col-md-4 labels"> Publication type<?=$required?></div> 
          <div class="col-md-8"><?=$form->field($model_zenodo,'publication_type')
          ->dropdownList($zenodo_defaults['zenodo_publication_type'],['class'=>'form-control',])->label("")?></div>
  </div>
  <div class="row body-row zenodo_field zenodo hidden" id='image-type'>
          <div class="col-md-4 labels"> Image type<?=$required?></div> 
          <div class="col-md-8"><?=$form->field($model_zenodo,'image_type')
          ->dropdownList($zenodo_defaults['zenodo_image_type'],['class'=>'form-control',])->label("")?></div>
  </div>
  <div class="row body-row zenodo_field zenodo">
      <div class="col-md-4 labels"> Authors<?=$required?></div> 
      <div class="col-md-8 creator-div" id="first-creator">
        <span class="col-md-4 creator">
        <?=$form->field($model_zenodo,'creators_name[]')->textInput(['class'=>'form-control', 'placeholder'=>'Family name, given names'])->label("")?>
        </span>
        <span class="col-md-3">
        <?=$form->field($model_zenodo,'creators_affiliation[]')->textInput(['class'=>'form-control', 'placeholder'=>'Affiliation'])->label("")?>
        </span>
        <span class="col-md-4">
        <?=$form->field($model_zenodo,'creators_orcid[]')->textInput(['class'=>'form-control orcid', 
        'placeholder'=>"ORCID (optional)"])->label("")?>
        </span>
      </div>
       <div class="col-md-offset-4 col-md-2 add-div"> 
        <?= Html::button('<i class="fa fa-plus"></i> Add Creator', ['class'=>'btn btn-default add-items']) ?>
      </div>
  </div>
  <div class="row body-row zenodo_field zenodo">
      <div class="col-md-4 labels"> Access rights<?=$required?></div> 
      <div class="col-md-8"><?=$form->field($model_zenodo,'access_rights')
      ->dropdownList($zenodo_defaults['zenodo_access_rights'],['class'=>'form-control access-rights', 'id'=>'rights'])->label("")?></div>
  </div>
  <div class="row body-row zenodo_field zenodo hidden" id='zenodo-license'>
          <div class="col-md-4 labels"> License<?=$required?></div> 
          <div class="col-md-8"><?=$form->field($model_zenodo,'license')->dropdownList($zenodo_defaults['zenodo_licenses'],['class'=>'form-control', ])->label("")?></div>
  </div>
  <div class="row body-row zenodo_field zenodo hidden" id='embargo-date'>
          <div class="col-md-4 labels"> Embargo date<?=$required?></div> 
          <div class="col-md-8"><?=$form->field($model_zenodo, 'embargo_date')->widget(DatePicker::classname(), [
          'options' => ['class'=>'embargo', 'label'=>''],
          'pluginOptions' => [
          'format' => 'yyyy-mm-dd',
          'autoclose'=>true]
          ])->label('');?>
          </div>
  </div>
  <div class="row body-row zenodo_field zenodo hidden" id='access-conditions'>
          <div class="col-md-4 labels"> Access conditions<?=$required?> </div> 
          <div class="col-md-8"><?=$form->field($model_zenodo,'access_conditions')->textarea(['columns'=>4, 'class'=>'form-control'])->label("")?>
          </div>
  </div>
  <div class="row body-row zenodo_field zenodo">
      <div class="col-md-4 labels"> Publication DOI <?=$help_doi_zenodo?></div> 
      <div class="col-md-8"> <?=$form->field($model_zenodo,'doi')->textInput(['class'=>'form-control'])->label("")?></div>
  </div>
  <div class="row body-row zenodo_field zenodo">
      <div class="col-md-4"> API key<?=$required?> <?=$help_api_key_zenodo?></div> 
      <div class="col-md-8"><?=$form->field($model_zenodo,'api_key')->textInput(['class'=>'form-control'])->label("")?>
      </div>
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
  <div class="row">&nbsp;</div>
  <div class="row">&nbsp;</div>
  <div class="form-group zenodo_field text-center">
              <?= Html::submitButton("$submit_icon Submit", ['class' => 'btn btn-primary', 'name'=>'zenodo-submit']) ?>
  </div>

  <?php 
  $form=ActiveForm::end();
  
}
else
{?>
  <div class="text-center"><h2>No providers found to upload datasets. You can enable dataset upload to providers from the administration menu</h2></div>
<?php
}?>


<div class="modal fade" id="delete-modal" tabindex="-1" role="dialog" aria-labelledby="delete-modal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-body"><div class="text-center modal-message">The dataset is uploading. Please wait.</div> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <div class="text-center"><i class="fas fa-spinner fa-spin fa-5x text-center"></i></b></div>
      </div>
  </div>
  </div>
</div>