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
use ricco\ticket\models\TicketHead;
?>
<p style="text-align: center;"><img src="#" alt=""/></p>
<p style="text-align: left; font-size: 14px;"><?= \yii\bootstrap\Html::encode($textTicket)?></p>
<hr/>
<p>
    <strong>Тикет:&nbsp;</strong><?=$nameTicket?>
    <br/>
    <strong>Статус:&nbsp;</strong>
    <?php
    switch ($status) {
        case TicketHead::OPEN :
            echo 'Открыт';break;
        case TicketHead::WAIT :
            echo 'Ожидание';break;
        case TicketHead::ANSWER :
            echo 'Отвечен';break;
        case TicketHead::CLOSED :
            echo 'Закрыт';break;
    }
    ?>
    <br/>
    <strong>Ссылка:&nbsp;
        <a
            href="<?=$link?>"><?=$link?>
        </a>
    </strong>
</p>
<hr/>
<em>
    <span style="color: #808080;">Это письмо сформировано автоматически. Отвечать на него не нужно</span>
</em
