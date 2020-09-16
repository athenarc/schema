<?php
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