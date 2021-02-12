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

/*
 * Define the namespace of the widget
 */
namespace app\components;

/*
 * Includes
 */
use yii\base\Widget;
use webvimark\modules\UserManagement\models\User as Userw;

/*
 * The widget class
 */
class MagicSearchBox extends Widget
{
    /*
     * Widget properties
     */
    public $submit_action;
    public $ajax_action;
    public $suggestions_num;
    public $expansion;
    public $min_char_to_start;
    public $html_params = array();
    public $subjects;
    
    /*
     * Elements that have previously been selected by this input
     */
    public $selected_elements = array();
    
    
    /*
     * Widget initialisation a.k.a. setting widget properties
     */
    public function init()
    {
        parent::init();
        
    }
    
    
    public function run()
    {
        
        return $this->render('magicsearchbox');
    }

}

?>