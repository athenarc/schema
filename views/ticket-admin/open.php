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
/** @var \ricco\ticket\models\TicketHead $ticketHead */
use yii\helpers\Html;
use yii\web\JsExpression;

/** @var \ricco\ticket\models\TicketBody $ticketBody */
?>
<div class="panel page-block">
    <div class="container-fluid row">
        <div class="col-md-offset-11 col-md-1">
            <a class="btn btn-default" href="<?= \yii\helpers\Url::to(['ticket-admin/index']) ?>" style="margin-bottom: 10px">Back</a>
        </div>
        <div class="col-md-12">
                
            <div class="well">
                <?php $form = \yii\widgets\ActiveForm::begin([]) ?>
                <label for="">Username</label>
                <?= \yii\jui\AutoComplete::widget([
                    'clientOptions' => [
                        'source'   => $users,
                        'autoFill' => true,
                        'select'   => new JsExpression("function( event, ui ) {
                                     $('#tickethead-user_id').val(ui.item.id);
                            }"),
                    ],
                    'options'       => [
                        'class' => 'form-control',
                    ],

                ]); ?>
                <?= Html::activeHiddenInput($ticketHead, 'user_id') ?>
                <?= $form->field($ticketHead, 'department')
                    ->dropDownList($qq)
                    ->label('Message')->error() ?>
                <?= $form->field($ticketHead, 'topic')
                    ->textInput() ?>
                <?= $form->field($ticketBody, 'text')
                    ->textarea([
                        'style' => 'height: 150px; resize: none;',
                    ])->label('Message'); ?>
                <div class="text-center">
                    <button class='btn btn-primary'>Submit</button>
                </div>
                <?= $form->errorSummary($ticketBody) ?>
                <?php $form->end() ?>
            </div>
        </div>

    </div>
</div>
</div><!-- contentpanel -->
</div><!-- mainpanel -->