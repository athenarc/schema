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

$this->title = "Request Image";

$submit_icon='<i class="fas fa-check"></i>';
$cancel_icon='<i class="fas fa-times"></i>';

?>
<?php $form = ActiveForm::begin();?>
<div class="software_upload">

    <div class="row">
        <div class="col-md-9"><h1><?= Html::encode($this->title) ?></h1></div>
        <div class="form-group col-md-3" style="padding-top: 25px; text-align: right;">
            <?= Html::submitButton("$submit_icon Submit", ['class' => 'btn btn-primary']) ?>
            <?= Html::a("$cancel_icon Cancel", ['/software/index'], ['class'=>'btn btn-default']) ?>
        </div>
    </div>
    <div class="row" style="padding-left: 15px;"> * Submit this form to request for a dockerhub image to be included in the software catalogue.</div>
    <div class="row">&nbsp;</div>
    <div class="row">&nbsp;</div>
    <?= $form->field($model, 'dock_link') ?>
    <?= $form->field($model, 'details')->textarea(['rows' => '6']) ?>


    <?php ActiveForm::end(); ?>

</div><!-- software_upload -->
