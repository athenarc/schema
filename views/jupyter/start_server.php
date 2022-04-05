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
use yii\helpers\Url;
use app\components\Headers;
use yii\widgets\ActiveForm;


/*
 * Add stylesheet
 */

echo Html::cssFile('@web/css/jupyter/start-server.css');
$this->registerJsFile('@web/js/jupyter/start_server.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

$this->title="Start Jupyter server";

$back_icon='<i class="fas fa-arrow-left"></i>';
$start_icon='<i class="fas fa-play"></i>';

Headers::begin() ?>
<?php echo Headers::widget(
['title'=>$this->title, 
    'buttons'=>
    [
        ['fontawesome_class'=>$back_icon,'name'=> 'Back', 'action'=>['/jupyter/index'],
        'options'=>['class'=>'btn btn-default'], 'type'=>'a'], 
    ],
])
?>
<?php Headers::end();?>

<?php $form=ActiveForm::begin($form_params); ?>

    <?=$form->field($model,'image_id')->dropDownList($imageDrop)?>
    <?=$form->field($model,'password')->passwordInput()?>

    <?=Html::submitButton($start_icon . '&nbsp;Start',['class'=> 'btn btn-success submit-btn'])?>
<?php ActiveForm::end(); ?>



<div class="modal fade" id="creatingModal" tabindex="-1" role="dialog" aria-labelledby="server-being-created" aria-hidden="true">
  <div class="modal-dialog modal-xl" >
    <div class="modal-content">
      <div class="modal-body text-center">
            <h3 class="modal-text "><i class="fas fa-spinner fa-spin"></i>&nbsp; Please wait while server is being created...<br /></h3>
            <h4>This process may take up to 3 minutes. <br /> You will be redirected automatically.</h4>
      </div>
    </div>
  </div>
</div>