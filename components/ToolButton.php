<?php
/*
* Helper for creating link buttons used for tools
*
* @parameter $link : can either be a string which contains the link or an array with the link 
*  in the form of ['controller/action', various parameters]
* @parameter $link_attributes : defaults to []. The user can add other attributes for the link like ['target'=>'_blank']
*
* @author Kostis Zagganas
*/

namespace app\components;
use yii\helpers\Html;
use Yii;

class ToolButton
{
	public static function createButton($title,$description,$link,$link_attributes=[])
	{
		
		echo Html::beginTag('div', ['class'=>'row']);
		echo Html::beginTag('div', ['class'=>'col-md-4 col-md-offset-4']);

		$btn = $title . "<br/>";
		$btn .= Html::tag('span', Yii::$app->formatter->asNtext($description), ['class'=> 'button-description']);
		echo Html::a($btn, $link, array_merge(['class'=>'btn btn-default btn-lg btn-block'],$link_attributes));
		
		echo Html::endTag('div');
		echo Html::endTag('div');
		
	}
	
}
