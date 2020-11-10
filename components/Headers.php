<?php


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