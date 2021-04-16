<?php

use yii\helpers\Html;
use webvimark\modules\UserManagement\models\User;
use yii\widgets\ActiveForm;

$this->title="Edit page \"$page->title\"";
$this->registerJsFile('@web/js/administration/add-edit-page.js', ['depends' => [\yii\web\JqueryAsset::className()]] );
$back_icon='<i class="fas fa-arrow-left"></i>';
/*
 * Users are able to view the name, version, start date, end date, mountpoint 
 * and running status of their previous software executions. 
 */
?>
<div class='title row'>
	<div class="col-md-11">
		<h1><?= Html::encode($this->title) ?></h1>
	</div>
	<div class="col-md-1 float-right">
		<?= Html::a("$back_icon Back", ['/administration/manage-pages'], ['class'=>'btn btn-default']) ?>
	</div>
</div>

<?php $form = ActiveForm::begin($form_params);  ?>

<?= $form->field($page, 'content')->textarea(['rows'=>30,'id'=>'content-area']) ?>


 <div class="row">
    <div class="col-md-1"><?= Html::submitButton('<i class="fas fa-check"></i> Submit', ['class' => 'btn btn-primary']) ?></div>
    <div class="col-md-1"><?= Html::a('<i class="fas fa-times"></i> Cancel', ['/administration/index'], ['class'=>'btn btn-default']) ?></div>
    <div class="col-md-1"><?= Html::a('<i class="fas fa-eye"></i> Preview', "javascript:void(0);", ['class'=>'btn btn-secondary preview-btn']) ?></div>
           
</div>

<?php ActiveForm::end(); ?>

