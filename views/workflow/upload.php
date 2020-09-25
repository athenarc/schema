<?php
/**
 * View file for the uploading of docker images.
 * 
 * @author: Kostis Zagganas
 * First version: Dec 2018
 */
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\SoftwareUpload */
/* @var $form ActiveForm */
echo Html::cssFile('@web/css/workflow/upload-software.css');
$this->registerJsFile('@web/js/workflow/upload.js', ['depends' => [\yii\web\JqueryAsset::className()]] );

$this->title = "Add new workflow";

$cwl_label="Upload your CWL workflow definition file(s) (" . Html::a('more info','https://www.commonwl.org/',['target'=>'blank']) . ") *"

?>
<div class="software_upload">

    <div class="row">
        <div class="col-md-12 headers"><h1><?= Html::encode($this->title) ?></h1></div>
    </div>
    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); 

 $submit_icon='<i class="fas fa-check"></i>';
 $cancel_icon='<i class="fas fa-times"></i>';

    ?>

        <?= $form->field($model, 'name') ?>
        <?= $form->field($model, 'version') ?>
        <?= $form->field($model, 'description')->textarea(['rows' => '6']) ?>
        <?= $form->field($model, 'visibility')->dropDownList($dropdown) ?>
        <?= $form->field($model, 'covid19') -> checkbox(['id'=>'covid19', "uncheck"=>'0']) ?>
        <?= $form->field($model, 'biotools') ?>
        <div class="doi-container">
            <div class="row"><div class="col-md-12"><?= Html::label('Add relevant DOIs (optional)') ?></div></div>
            <div class="row">
                <div class="col-md-11"><?= Html::input('','doi-input','',['id'=>'doi-input'])?></div>
                <div class="col-md-1 float-right"><?= Html::a('Add','javascript:void(0);',['class'=>'btn btn-secondary','id'=>'doi-add-btn']) ?></div>
            </div>
            <div class="row">
                <div class="col-md-5"><div class="doi-list"></div></div>
            </div>
        </div>

        <div class="row">&nbsp;</div>

        <div class="cwl-input-container">
            <?= $form->field($model, 'workflowFile')->fileInput()->label($cwl_label) ?>&nbsp;&nbsp;
            <div class="cwl-logo"><img src="<?=Url::to('@web/img/cwl-logo.png')?>" class='cwl-logo-img'></div>
        </div>
        <br /><br />
        <div class="form-group">
            <?= Html::submitButton("$submit_icon Submit", ['class' => 'btn btn-primary']) ?>
            <?= Html::a("$cancel_icon Cancel", ['/software/index'], ['class'=>'btn btn-default']) ?>
        </div>
    <?php ActiveForm::end(); ?>

</div><!-- software_upload -->
