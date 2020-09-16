<?php
$this->title = 'Support';

/** @var \ricco\ticket\models\TicketHead $ticketHead */
/** @var \ricco\ticket\models\TicketBody $ticketBody */
?>
<div class="panel page-block">
    <div class="col-sx-12">
        <div class=col-md-offset-11 col-md-1>
            <a class="btn btn-default col-md-offset-11" href="<?= \yii\helpers\Url::toRoute(['/ticket-user/index']) ?>"
               style="margin-bottom: 10px; margin-left: 15px">Back</a>
        </div>
        <?php $form = \yii\widgets\ActiveForm::begin([]) ?>
        <div class="col-xs-12">
            <?= $form->field($ticketBody, 'name_user')->textInput([
                'readonly' => '',
                'value'    => Yii::$app->user->identity['username'],
            ]) ?>
        </div>
        <div class="col-xs-12">
            <?= $form->field($ticketHead, 'topic')->textInput()->label('Subject')->error() ?>
        </div>
        <div class="col-xs-12">
            <?= $form->field($ticketHead, 'department')->dropDownList($qq) ?>
        </div>
        <div class="col-xs-12">
            <?= $form->field($ticketBody, 'text')->textarea([
                'style' => 'height: 150px; resize: none;',
            ]) ?>
        </div>
        <div class="col-xs-12">
            <?= $form->field($fileTicket, 'fileName[]')->fileInput([
                'multiple' => true,
                'accept'   => 'image/*',
            ])->label('Attach a screenshot (optional)'); ?>
        </div>
        <div class="text-center">
            <button class='btn btn-primary'>Submit</button>
        </div>
        <?php $form->end() ?>
    </div>
</div>
