<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use app\components\Headers;
use webvimark\modules\UserManagement\models\User;
use app\models\UploadDataset;
use app\components\MagicSearchBox;

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
$help_creator_names='<i class="fa fa-question-circle" title="You can separate multiple creator names with a comma."></i>';
$help_affiliations='<i class="fa fa-question-circle" title="You can separate multiple creator names with a comma."></i>';
$help_email='<i class="fa fa-question-circle" title="You must specify only one contact point"></i>';
$help_doi='<i class="fa fa-question-circle" title="You can separate multiple publication DOIs with a comma"></i>';
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
$form = ActiveForm::begin([
   'id' => 'helix-form',
   'action' => Url::to(['/filebrowser/upload-dataset-helix'])
]);

echo Html::hiddenInput('mountcaller-helix',null ,['id'=>'mountcaller-helix']);
echo Html::hiddenInput('selectmounturl-helix',Url::to(['software/select-mountpoint','username'=>$username]) ,['id'=>'selectmounturl']);

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
    <div class="col-md-4 labels"> Subject<?=$required?></div>           
    <div class="col-md-8"><?=MagicSearchBox::widget(
          ['min_char_to_start' => 3, 
              'expansion' => 'both', 
              'suggestions_num' => 5, 
              'html_params' => [ 'id' => 'user_search_box', 
              'name'=>'subjects', 
              'class'=>'form-control blue-rounded-textbox'],
              'ajax_action' => Url::toRoute('filebrowser/auto-complete-subjects'),
              'subjects' =>[],
          ])?>
    </div>
</div>
<div class="row body-row helix_field helix_hide">
    <div class="col-md-4 labels"> Creator names<?=$required?> <?=$help_affiliations?></div> 
    <div class="col-md-8"><?=$form->field($model_helix,'creator')->textInput(['class'=>'form-control'])->label("")?></div>
</div>
<div class="row body-row helix_field helix_hide">
    <div class="col-md-4 labels"> Creator affiliations<?=$required?> <?=$help_affiliations?></div> 
    <div class="col-md-8"><?=$form->field($model_helix,'affiliation')->textInput(['class'=>'form-control'])->label("")?></div>
</div>
<div class="row body-row helix_field helix_hide">
        <div class="col-md-4 labels"> Contact email<?=$required?> <?=$help_email?></div> 
        <div class="col-md-8"><?=$form->field($model_helix,'contact_email')->textInput(['class'=>'form-control'])->label("")?>
        </div>
</div>
<div class="row body-row helix_field helix_hide">
        <div class="col-md-4 labels"> License<?=$required?></div> 
        <div class="col-md-8"><?=$form->field($model_helix,'license')->dropdownList($helix_licenses,['class'=>'form-control', 'options'=>['notspecified'=>['Selected'=>true]]])->label("")?></div>
</div>
<div class="row body-row helix_field helix_hide">
        <div class="col-md-4 labels"> Private <?=$help_private?></div> 
        <div class="col-md-8"><?=$form->field($model_helix,'private')->checkBox(['label'=>''])->label("")?>
        </div>
</div>
<div class="row body-row helix_field helix_hide">
    <div class="col-md-4 labels"> Related publication DOIs <?=$help_doi?></div> 
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
    <?=Html::textInput('dataset_helix',$isystemMount,['id' => 'helix-mount','class'=>'mount-field','readonly'=>true,]).'&nbsp;&nbsp;' 
    . Html::a("$select_icon Select",'javascript:void(0);',['class'=>'select-mount-button btn btn-success btn-md']).'&nbsp'
    . Html::a("$clear_icon Clear",'javascript:void(0);',['class'=>'clear-mount-button btn btn-danger btn-md'])?>
    </div>
</div>
<div class="row helix_field helix_hide">&nbsp;</div>
<div class="row helix_field helix_hide">&nbsp;</div>
<div class="text-center form-group helix_field helix_hide">
            <?= Html::submitButton("$submit_icon Submit", ['class' => 'btn btn-primary', 'name'=>'helix-submit']) ?>
</div>

<?php 
$form=ActiveForm::end();
?>

<?php
$form = ActiveForm::begin([
   'id' => 'zenodo-form',
   'action' => Url::to(['/filebrowser/upload-dataset-zenodo'])
]);

echo Html::hiddenInput('mountcaller',null ,['id'=>'mountcaller']);
echo Html::hiddenInput('selectmounturl',Url::to(['software/select-mountpoint','username'=>$username]) ,['id'=>'selectmounturl']);
?>

<div class="row body-row zenodo_field zenodo">
    <div class="col-md-4"> API key<?=$required?> <?=$help_api_key_zenodo?></div> 
    <div class="col-md-8"><?=$form->field($model_zenodo,'api_key')->textInput(['class'=>'form-control'])->label("")?>
    </div>
</div>
<div class="row body-row zenodo_field">
    <div class="col-md-4 label-dataset">Select dataset folder<?=$required?> <?=$help_dataset_folder?></div>
    <div class="col-md-8">
    <?=Html::textInput('dataset_zenodo',$isystemMount,['id' => 'zenodo-mount','class'=>'mount-field','readonly'=>true,]).'&nbsp;&nbsp;' 
    . Html::a("$select_icon Select",'javascript:void(0);',['class'=>'select-mount-button btn btn-success btn-md']).'&nbsp'
    . Html::a("$clear_icon Clear",'javascript:void(0);',['class'=>'clear-mount-button btn btn-danger btn-md'])?>
    </div>
</div>
<div class="row">&nbsp;</div>
<div class="row">&nbsp;</div>
<div class="form-group zenodo_field text-center">
            <?= Html::submitButton("$submit_icon Submit", ['class' => 'btn btn-primary', 'name'=>'zenodo-submit']) ?>
</div>

<?php 
$form=ActiveForm::end();
?>


