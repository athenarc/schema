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
* Helper for showing files and folders. The reason it exists is because it needs to be called recursively.
*
*
* @author Kostis Zagganas
*/

namespace app\components;
use yii\helpers\Html;
use Yii;
use webvimark\modules\UserManagement\models\User;

class FileListWorkflow
{
    public static function printFileList($files,$level)
    {
        $indentation=0;
        for ($i=0; $i<$level; $i++) 
            $indentation+=30;
        
        foreach ($files as $key=>$value)
        {
            if (strpos($key,'__file__')===false)
            {
                $folder_value=str_replace(Yii::$app->params['userDataPath'] . explode('@',User::getCurrentUser()['username'])[0] . '/','',$key);
                $folder_split=explode('/',$folder_value);
                $folder_name=end($folder_split);

                echo "<div class='row selection-row'>";
                echo "<div class='non-selectable col-md-7' style='margin-left:" . $indentation 
                            . "px;''><i class='fa fa-folder' aria-hidden='true'></i>&nbsp;&nbsp;$folder_name";
                echo Html::hiddenInput('hiddenPath',$folder_value);
                echo "</div></div>";

                if (!empty($value))
                {
                    self::printFileList($value,$level+1);
                }
            }
            else
            {
                $file_value=str_replace(Yii::$app->params['userDataPath'] . explode('@',User::getCurrentUser()['username'])[0] . '/','',$value);
                $file_split=explode('/',$file_value);
                $file_name=end($file_split);
                echo "<div class='row selection-row'>";
                echo "<div class='selectable col-md-7' style='margin-left:" . $indentation 
                            . "px;''><i class='fa fa-file-alt' aria-hidden='true'></i>&nbsp;&nbsp;$file_name";
                echo Html::hiddenInput('hiddenPath',$file_value);
                echo "</div></div>";
                
            }

            
            
        }
        
    }
    
}
