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
namespace app\models;

use Yii;

class TicketConfig extends \yii\base\Model
{
    /** @var bool Уведомление на почту о тикетах */
    const mailSend = false;

    /** @var string Тема email сообщения когда пользователю приходит ответ */
    const subjectAnswer = 'Ответ на тикет сайта exemple.com';

    /** @var  User */
    const userModel = false;

    const qq = [
        'Bug' => 'Вug',
        'New feature proposal' => 'New feature proposal',
        'Suggestion' => 'Suggestion',
    ];

    /** @var string  */
    const uploadFilesDirectory = '@webroot/fileTicket';

    /** @var string  */
    const uploadFilesExtensions = 'png, jpg';

    /** @var int  */
    const uploadFilesMaxFiles = 5;

    /** @var null|int */
    const uploadFilesMaxSize = null;

    /** @var bool|int */
    const pageSize = 20;
    const user = false;

    // /**
    //  * Статусы тикетов
    //  */
    // const OPEN = 0;
    // const WAIT = 1;
    // const ANSWER = 2;
    // const CLOSED = 3;
    // const VIEWED = 4;

    // public function rules()
    // {
    //     return [
    //         // username and password are both required
    //         [['mailSend', 'subjectAnswer','qq','userModel','uploadFilesDirectory',
    //         'uploadFilesExtensions','uploadFilesMaxSize','pageSize','user'], 'required'],
    //     ];

    // }
}
