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
use yii\helpers\Url;
use yii\helpers\Html;
use ricco\ticket\models\TicketHead;

/** @var TicketHead $dataProvider */

$this->title = 'Support';

$this->registerJs("

    $('td').click(function (e) {
        var id = $(this).closest('tr').data('id');
        if(e.target == this)
           location.href = '" . Url::toRoute(['ticket-user/view', 'id' => '']) . "' + id ;
    });

");
?>

<style>
    .ticket:hover
    {
        cursor:pointer;
    }
</style>

<div class="panel page-block">
    <div class="container-fluid row">
        <div class="col-lg-12">
            <a type="button" href="<?= Url::to(['/ticket-user/open']) ?>" class="btn btn-primary pull-right"
               style="">New ticket</a>
            <div class="clearfix" style="margin-bottom: 10px"></div>
            <div>
                <?= \yii\grid\GridView::widget([
                    'dataProvider' => $dataProvider,
                    'rowOptions'   => function ($model) {
                        return ['data-id' => $model->id, 'class' => 'ticket'];
                    },
                    'columns'      => [
                        'department',
                        'topic',
                        [
                            'contentOptions' => [
                                'style' => 'text-align:center;',
                            ],
                            'value'          => function ($model) {
                                switch ($model['status']) {
                                    case TicketHead::OPEN :
                                        return '<div class="label label-default">Open</div>';
                                    case TicketHead::WAIT :
                                        return '<div class="label label-warning">Answer pending</div>';
                                    case TicketHead::ANSWER :
                                        return '<div class="label label-success">Answered</div>';
                                    case TicketHead::CLOSED :
                                        return '<div class="label label-info">Closed</div>';
                                }
                            },
                            'format'         => 'html',
                        ],
                        [
                            'contentOptions' => [
                                'style' => 'text-align:right; font-size:13px',
                            ],
                            'attribute'      => 'date_update',
                            'value'          => "date_update",
                        ],
                    ],
                ]) ?>
            </div>
        </div>
    </div>
</div>
<div class="row">
        <div class="col-md-12" style="padding-left:30px;">Powered by&nbsp;<?= Html::a('ricco381/yii2-ticket', 'https://github.com/ricco381/yii2-ticket/blob/master/README_EN.md', ['target'=>'_blank'])?></div>
    </div>

