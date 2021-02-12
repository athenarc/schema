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
use Yii;
use app\models\Notification;
use webvimark\modules\UserManagement\models\User as Userw;
use yii\helpers\Html;
use yii\helpers\Url;

/*
 * The widget class
 */
Yii::$app->getView()->registerJsFile('@web/js/components/supportWindow.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
class SupportWindow //extends Widget
{
    
    /*
     * Running the widget a.k.a. rendering results
     */
    public static function show($link)
    {
    	echo Html::cssFile('@web/css/components/supportWindow.css');
    	echo "<div class='support-window-wrapper text-center'>";
        echo    "<div class='col-md-1 col-md-offset-2 support-window-text support-arrow-right'  style='padding-top:5px;
                     cursor:pointer; padding-left:100px;  ' title='Minimize support window'>
                    <i class='fa fa-angle-double-right' aria-hidden='true'></i></div>";
    	echo 	"<div class='support-window-text'>Have you encountered a problem or do you have any suggestion?</div>";
        echo 	"<div class='support-window-button'>";
    	echo 		Html::a('Click here',['ticket-user/open', 'link'=>urlencode($link)], ['class'=>'btn btn-info']);
    	echo	"</div>";
    	echo "</div>";
        //minimized window
        echo  "<div class='support-minimized' style='display:none; text-align:left;'>";
        echo        "<span class='col-md-12 support-arrow-left'   style='cursor:pointer; padding-left:8px; padding-top:3px;' title='Maximize support window'>
                    <i class='fa fa-angle-double-left' aria-hidden='true'></i></span>";
        echo    "</div>";

    }
}

?>