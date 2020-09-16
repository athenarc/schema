<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title='Request new COVID-19 dataset';
$back_icon='<i class="fas fa-arrow-left"></i>';

/* @var $this yii\web\View */
/* @var $model app\models\CovidDatasetApplication */
/* @var $form ActiveForm */
?>
<div class='title row'>
	<div class="col-md-11">
		<h1><?= Html::encode($this->title) ?></h1>
	</div>
	<div class="col-md-1 float-right">
		<?= Html::a("$back_icon Back", ['filebrowser/index'], ['class'=>'btn btn-default']) ?>
	</div>
</div>
<div class="filebrowser-dataset_application">

    <?php $form = ActiveForm::begin(); ?>

    	<?= $form->field($model, 'email') ?>
    	<?= $form->field($model, 'name') ?>
        <?= $form->field($model, 'link') ?>
        <?= $form->field($model, 'description')->textarea(['rows'=>6]) ?>
        
        <div class="form-group">
            <?= Html::submitButton('Submit Application', ['class' => 'btn btn-primary']) ?>
        </div>
    <?php ActiveForm::end(); ?>

</div><!-- filebrowser-dataset_application -->
