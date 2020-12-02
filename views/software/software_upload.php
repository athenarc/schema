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
use dosamigos\ckeditor\CKEditor;
use app\components\Headers;

/* @var $this yii\web\View */
/* @var $model app\models\SoftwareUpload */
/* @var $form ActiveForm */
echo Html::cssFile('@web/css/software/upload-software.css');
$this->registerJsFile('@web/js/software/upload-software.js', ['depends' => [\yii\web\JqueryAsset::className()]] );

$this->title = "Add new software";

$cwl_label="Upload your CWL input definition file (" . Html::a('example',['site/cwl-tutorial'],['target'=>'blank']) . ")";

Headers::begin() ?>
<?php echo Headers::widget(
['title'=>$this->title
   
])
?>
<?Headers::end()?>
<div class="software_upload">

    
<?php $form = ActiveForm::begin(); 
    
echo $form->errorSummary($model);


 $submit_icon='<i class="fas fa-check"></i>';
 $cancel_icon='<i class="fas fa-times"></i>';

    ?>

        <?= $form->field($model, 'name') ?>
        <?= $form->field($model, 'version') ?>
        <?=$form->field($model, 'description')->widget(CKEditor::className(), [
                     'options' => ['rows' => 4],
                     'preset' => 'basic'
                ])
                ?>
        <br /><br />
        <?= $form->field($model, 'visibility')->dropDownList($dropdown) ?>
        <?= $form->field($model,'commandRetrieval')-> dropDownList($command_drop) ?>
        <?= $form->field($model, 'iomount') -> checkbox(['id'=>'iomount']) ?>
        <div class='mount-fields'>
            <?= $form->field($model, 'imountpoint') ?>
            <?= $form->field($model, 'omountpoint') ?>
        </div>
        <!-- <?= $form->field($model, 'covid19') -> checkbox(['id'=>'covid19', "uncheck"=>'0']) ?> -->
        <?= $form->field($model, 'mpi') -> checkbox(['id'=>'mpi']) ?>
        <div class="mpi-warning">
        <div class="row mpi-warning-row">
            <div class="col-md-12">
                Warning: The image must be built using zagganas/kube-openmpi-schema:0.5 from DockerHub
            </div>
        </div>
        </div>
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
            <?= $form->field($model, 'cwlFile')->fileInput()->label($cwl_label) ?>&nbsp;&nbsp;
            <div class="cwl-logo"><img src="<?=Url::to('@web/img/cwl-logo.png')?>" class='cwl-logo-img'></div>
        </div>
        <br /><br />
        <?= $form->field($model,'imageInDockerHub')->checkbox(['id'=>'imageInDockerHub'])?>
        <?= $form->field($model, 'imageFile',['options'=>['class'=>'form-group invisible-div image-file-input']])->fileInput() ?>
        <br /><br />
        <?php
        echo $form->field($model, 'instructions')->widget(CKEditor::className(), [
                     'options' => ['rows' => 4],
                     'preset' => 'basic'
                ]);
                ?>
        <br /><br />
        <div class="form-group">
            <?= Html::submitButton("$submit_icon Submit", ['class' => 'btn btn-primary']) ?>
            <?= Html::a("$cancel_icon Cancel", ['/software/index'], ['class'=>'btn btn-default']) ?>
        </div>
    <?php ActiveForm::end(); ?>

</div><!-- software_upload -->
