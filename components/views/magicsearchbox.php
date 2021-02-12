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
 * Magic search box view!
 * 
 * @author: Ilias Kanellos (First Version: September 2015)
 * @author: Serafeim Catzopoulos (Last Modified: May 2016)
 * 
 */
namespace yii\jui;

use yii\jui\AutoComplete;
use yii\helpers\Html;



/*
 * Include widget css
 */
echo Html::cssFile('@web/css/components/magicSearchBox.css');
//echo Html::jsFile('@web/js/components/widgets/magic_search_box.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
/*
 * Javascript files should be registered at the end of a page, in order to be more efficient in page loading etc.
 * Therefore the helper used for registering JS files is not suitable - at least until we find out how to make it
 * append scripts at the end of the html code. Instead, we use the classic register method here.
 */
$this->registerJsFile('@web/js/components/magicSearchBox.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

/*
 * Echo all view code
 */

$field="\"<div class='hidden_selected_element'><i class='fas fa-times'></i>&nbsp;\" + ui.item.value + ";
$field.="\"<input type='hidden' id='hidden_selected_element_input' name='subjects[]' value='\" + ui.item.value + \"'>";
$field.="</div>\"";

echo "<div class='magic_search_box_wrapper'>";
    echo AutoComplete::widget([
        'name' => 'user_search_box',
        'clientOptions' =>
        [
            'minLength' => $this->context->min_char_to_start,
            'source' => $this->context->ajax_action . 
                               "&expansion=" . $this->context->expansion . 
                               "&max_num=" . $this->context->suggestions_num,
        ],
        'clientEvents' =>
        [
            'select' => 
                'function(event, ui)'
            .   '{ '
                    /*
                     * Get the value selected, add it to the list of names and 
                     * also add the handle for the remove button.
                     */
            . '     if(ui.item.value == "No suggestions found") return false; '
            . '     var selected_elements=$(".hidden_element_box").html(); '
            . '     selected_elements=' . $field . ' + selected_elements; '
            . '     $(".hidden_element_box").html(selected_elements); '
            
            // . '     $(this).val(ui.item.value); '
            // . '     $(".magic_search_box_wrapper).append(ui.item.value);''
            .   '$(".fas.fa-times").click(function(){$(this).parent().remove();}); '
            .   '} ',
            'close' => 'function( event, ui ) {$("#user_search_box").val("");}',

        ],
        //html options
        'options' => $this->context->html_params,
    ]);
    echo "<div class='hidden_element_box'>";
    foreach ($this->context->subjects as $subject)
    {
        // print_r($currentUser);
        // print_r(" " . $part);
        echo "<div class='hidden_selected_element'>";
        // if ($currentUser!=$subject)
        // {
        //     echo "<i class='fas fa-times'></i>";
        // }
        // else
        // {
            
        // }
        echo "$subject<input type='hidden' id='hidden_selected_element_input' name='subjects[]' value='$subject'>";
        echo "</div>";
    }
    echo "</div>";
    
    
echo "</div>";


?>
