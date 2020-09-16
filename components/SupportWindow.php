<?php

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
use yii\helpers\Url;

/*
 * The widget class
 */
class SupportWindow //extends Widget
{
    
    /*
     * Running the widget a.k.a. rendering results
     */
    public static function show($link)
    {
    	echo Html::cssFile('@web/css/components/supportWindow.css');
    	echo "<div class='support-window-wrapper text-center'>";
    	echo 	"<div class='support-window-text'>Have you encountered a problem or do you have any suggestion?</div>";
    	echo 	"<div class='support-window-button'>";
    	echo 		Html::a('Click here',['ticket-user/open', 'link'=>urlencode($link)], ['class'=>'btn btn-info']);
    	echo	"</div>";
    	echo "</div>";

    }
}

?>