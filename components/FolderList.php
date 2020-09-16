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
use webvimark\modules\UserManagement\models\User;

class FolderList
{
	public static function printFolderList($folders,$level)
	{
		$indentation=0;
		for ($i=0; $i<$level; $i++) 
			$indentation+=30;

		foreach ($folders as $folder=>$subfolders)
		{
			$folder_value=str_replace(Yii::$app->params['userDataPath'] . explode('@',User::getCurrentUser()['username'])[0] . '/','',$folder);
			$folder_split=explode('/',$folder_value);
			$folder_name=end($folder_split);

			echo "<div class='row selection-row'>";
			echo "<div class='selectable col-md-3' style='margin-left:" . $indentation 
						. "px;''><i class='fa fa-folder' aria-hidden='true'></i>&nbsp;&nbsp;$folder_name";
			echo Html::hiddenInput('hiddenPath',$folder_value);
			echo "</div></div>";

			if (!empty($subfolders))
			{
				self::printFolderList($subfolders,$level+1);
			}
			
		}
		
	}
	
}
