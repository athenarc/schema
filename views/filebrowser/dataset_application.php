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
