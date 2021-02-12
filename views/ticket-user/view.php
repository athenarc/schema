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

$this->title = 'Support';



?>
<div class="panel page-block">
    <div class="container-fluid row">
        <div class="col-md-offset-11 col-md-1">
            <a class="btn btn-default" href="<?= \yii\helpers\Url::to(['ticket-user/index']) ?>"
               style="margin-bottom: 10px;">Back</a>
        </div>
        <div class="col-lg-12">
            <a class="btn btn-primary" style="width: 100%" role="button" data-toggle="collapse" href="#collapseExample"
               aria-expanded="false" aria-controls="collapseExample">
                <i class="glyphicon glyphicon-pencil pull-left"></i><span>Write answer</span>
            </a>
            <?php if ($error = Yii::$app->session->getFlash('error')) : ?>
                <div class="alert alert-danger text-center" style="margin-top: 10px;"><?= $error ?></div>
            <?php endif; ?>
            <div class="collapse" id="collapseExample">
                <div class="well">
                    <?php $form = \yii\widgets\ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>
                    <?= $form->field($newTicket,
                        'text')->textarea(['style' => 'height: 150px; resize: none;'])->label('Message')->error() ?>
                    <?= $form->field($fileTicket, 'fileName[]')->fileInput([
                        'multiple' => true,
                        'accept'   => 'image/*',
                    ])->label('Attach a screenshot (optional)'); ?>
                    <div class="text-center">
                        <button class='btn btn-primary'>Submit</button>
                    </div>
                    <?= $form->errorSummary($newTicket) ?>
                    <?php $form->end() ?>
                </div>
            </div>
            <div class="clearfix" style="margin-bottom: 20px"></div>
            <?php foreach ($thisTicket as $ticket) : ?>
                <div class="panel panel-primary">
                    <div class="panel-heading">
                <span><?= $ticket['name_user'] ?>&nbsp;<span
                        style="font-size: 12px">(<?= ($ticket['client'] == 1) ? 'Administrator' : 'User' ?>)</span></span>
                        <span class="pull-right"><?= $ticket['date'] ?></span>
                    </div>
                    <div class="panel-body clearfix" style="word-wrap: break-word;">
                        <?= nl2br(Html::encode($ticket['text'])) ?>
                        <?php if (!empty($ticket->file)) : ?>
                            <hr>
                            <?php foreach ($ticket->file as $file) : ?>
                                <a href="<?=Url::to('@web/fileTicket')?>/<?= $file->fileName ?>" target="_blank"><img
                                        src="<?= Url::to('@web/fileTicket/reduced/') . $file->fileName ?> " alt="..."
                                        class="img-thumbnail"></a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
