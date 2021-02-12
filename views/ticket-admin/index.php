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
/** @var \ricco\ticket\models\TicketHead $dataProvider */
?>

<div class="panel page-block">
    <div class="container-fluid row">
    <div class="col-md-1">
            <a href="<?= \yii\helpers\Url::toRoute(['ticket-admin/open']) ?>" class="btn btn-primary" style="margin-bottom: 15px; margin-left:15px;">Open new ticket</a></div>
    <br><br>
    <div class="container-fluid">
        <div class="col-lg-12">
            <?= \yii\grid\GridView::widget([
                'dataProvider' => $dataProvider,
                'rowOptions'   => function ($model) {
                    $background = '';
                    if ($model->status == 0 || $model->status == 1) {
                        $background = 'background:#E6E6FA';
                    }
                    return [
                        'style'   => "c" . $background,
                    ];
                },
                'columns'      => [
                    [
                        'attribute' => 'userName',
                        'value'     => 'userName.username',
                    ],
                    [
                        'attribute' => 'department',
                        'value'     => 'department',
                    ],
                    [
                        'attribute' => 'topic',
                        'value'     => 'topic',
                    ],
                    [
                        'attribute' => 'status',
                        'value'     => function ($model) {
                            switch ($model->body['client']) {
                                case 0 :
                                    if ($model->status == \ricco\ticket\models\TicketHead::CLOSED) {
                                        return '<div class="label label-success">User</div>&nbsp;<div class="label label-default">Closed</div>';
                                    }

                                    return '<div class="label label-success">User</div>';
                                case 1 :
                                    if ($model->status == \ricco\ticket\models\TicketHead::CLOSED) {
                                        return '<div class="label label-primary">Administrator</div>&nbsp;<div class="label label-default">Closed</div>';
                                    }

                                    return '<div class="label label-primary">Open</div>';
                            }
                        },
                        'format'    => 'html',
                    ],
                    [
                        'attribute' => 'date_update',
                        'value'     => 'date_update',
                    ],
                    [
                        'class'         => 'yii\grid\ActionColumn',
                        'template'      => '{update}&nbsp;{delete}&nbsp;{closed}&nbsp;{reopen}',
                        'headerOptions' => [
                            'style' => 'width:230px',
                        ],
                        'buttons'       => [
                            'update' => function ($url, $model) {
                                return \yii\helpers\Html::a('Answer',
                                    \yii\helpers\Url::toRoute(['/ticket-admin/answer', 'id' => $model['id']]),
                                    ['class' => 'btn-xs btn-info']);
                            },
                            'delete' => function ($url, $model) {
                                return \yii\helpers\Html::a('Delete',
                                    \yii\helpers\Url::to(['/ticket-admin/delete', 'id' => $model['id']]),
                                    [
                                        'class'   => 'btn-xs btn-danger',
                                        'onclick' => 'return confirm("Are you sure you want to delete the ticket?")',
                                    ]
                                );
                            },
                            'closed' => function ($url, $model) {
                                return \yii\helpers\Html::a('Close',
                                    \yii\helpers\Url::to(['/ticket-admin/closed', 'id' => $model['id']]),
                                    [
                                        'class'   => 'btn-xs btn-primary',
                                        'onclick' => 'return confirm("Are you sure you want to close the ticket?")',
                                    ]
                                );
                            },
                            'reopen' => function ($url, $model) {
                                return \yii\helpers\Html::a('Re-open',
                                    \yii\helpers\Url::to(['ticket-admin/reopen', 'id' => $model['id']]),
                                    [
                                        'class'   => 'btn-xs btn-warning',
                                        'onclick' => 'return confirm("Are you sure you want to re-open the ticket?")',
                                    ]
                                );
                            },
                        ],
                    ],
                ],
            ]) ?>
        </div>
    </div>
</div></div>