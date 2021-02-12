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

/*
 * Widget for displaying a magic search box with the following properties:
 * i) A user types in a search keyword, denoting a specific entity (something 
 * found in a db table, such as a miRNA name, TF, gene, etc)
 * ii) The search box automatically suggests terms, while the user is writing
 * iii) After selecting one of the suggestions, the selection is displayed under
 *      the box. Additional selections may be made afterwards.
 * 
 * @params:
 * min_char_to_start: should be a number that sets the character threshold, after
 *                    which the form starts displaying suggestions
 * expansion: a constant (LEFT, RIGHT, BOTH), that determines on which side an
 *            SQL "like" query will put the wildcard character
 * suggestions_num: a number determining how many suggestions should be displayed
 *                  at a time
 * html_params[]: an  associative array for setting parameters such as html id etc.
 * ajax_action: the name of the controller/action responsible for getting suggested
 *              results
 * 
 * @author: Ilias Kanellos (First version: September 2015)
 */

# ---------------------------------------------------------------------------- #

/*
 * Define the namespace of the widget
 */
namespace app\components;

/*
 * Includes
 */
// use yii\base\Widget;
use app\models\Notification;
use webvimark\modules\UserManagement\models\User as Userw;
use yii\helpers\Html;

/*
 * The widget class
 */
class NotificationWidget //extends Widget
{
    
    /*
     * Running the widget a.k.a. rendering results
     */
    public static function createMenuItem()
    {
        $user=Userw::getCurrentUser()['id'];
        $notifications=Notification::find()->where(['seen'=>false])->andWhere(['recipient_id'=>$user])->all();

        if (empty($notifications))
        {
            $notifCount=0;
        }
        else
        {
            $notifCount=count($notifications);
        }

        $typeClass=[-1=>'notification-danger', 0=>'', 1=>'notification-warning', 2=>'notification-success'];
        $bellClass=($notifCount==0) ? 'grey-notification-bell' : 'color-notification-bell';
        // $notification_count=($notifCount==0) ? 'notification_count_zero' : 'notification_count_not_zero';
        
        $label="<span class='$bellClass notification-menu-header'><i class='fas fa-bell'></i>&nbsp;&nbsp;$notifCount</span>";
        
        $items=[];
;
        $items[]="<li class='dropdown-header'>You have $notifCount new messages.</li>";

        foreach ($notifications as $notification)
        {
            $type=$typeClass[$notification->type];
            $items[]=['label'=>$notification->message, 'url'=>['/site/notification-redirect','id'=>$notification->id], 
                                                       'options'=>['class'=>"notification $type"]];
        }
        $items[]=['label'=>'View notification history', 'url'=>['site/notification-history'],'options'=>['class'=>'notification-history']];
        if ($notifCount!=0)
        {
                $items[]=['label'=>'Mark all as seen','options'=>['id'=>'mark_all_seen']];
        }
        return [$label,$items];


    }

    public static function createDiv()
    {
        $user=Userw::getCurrentUser()['id'];
        


    }
}

?>