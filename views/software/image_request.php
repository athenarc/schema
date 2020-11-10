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
use app\components\Headers;

/* @var $this yii\web\View */
/* @var $model app\models\SoftwareUpload */
/* @var $form ActiveForm */

$this->title = "Request Image";
$submit_icon='<i class="fas fa-check"></i>';
$cancel_icon='<i class="fas fa-times"></i>';

 $form = ActiveForm::begin();?>
<? 
Headers::begin() ?>
<?php echo Headers::widget(
['title'=>$this->title, 
    'buttons'=>
    [
        ['fontawesome_class'=>$submit_icon,'name'=> 'Submit', 
        'options'=>['class'=>'btn btn-primary'], 'type'=>'submitButton'], 
        ['fontawesome_class'=>$cancel_icon,'name'=> 'Cancel', 'action'=>['/software/index'],
         'options'=>['class'=>'btn btn-default'], 'type'=>'a'] 
    ],
])
?>
<?Headers::end()?>



<div class="software_upload">
    <div class="row" style="padding-left: 15px;"> * Submit this form to request for a dockerhub image to be included in the software catalogue.</div>
    <div class="row">&nbsp;</div>
    <div class="row">&nbsp;</div>

    <?= $form->field($model, 'dock_link') ?>
    <?= $form->field($model, 'details')->textarea(['rows' => '6']) ?>


    <?php ActiveForm::end(); ?>

</div><!-- software_upload -->
