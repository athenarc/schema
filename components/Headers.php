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


namespace app\components;

/*
 * Includes
 */
use yii\base\Widget;
use yii\helpers\Html;
use webvimark\modules\UserManagement\models\User as Userw;

/*
 * The widget class
 */
class Headers extends Widget
{
    
    public $title;
    public $subtitle;
    public $fontawesome_class;
    public $options;
    public $name;
    public $action;
    public $buttons=array();
    public $special_content;
    public $type;
    
    
    
    /*
     * Widget initialisation a.k.a. setting widget properties
     */
    public function init()
    {
        parent::init();
        ob_start();
        
    }
    
   
    public function run()
    {
        $title=$this->title;
        $subtitle=$this->subtitle;
        $buttons=$this->buttons;
        $special_content=$this->special_content;
        return $this->render('headers', ['special_content'=>$special_content,'buttons'=>$buttons, 
            'title'=>$title, 'subtitle'=>$subtitle]);

    }



}

?>