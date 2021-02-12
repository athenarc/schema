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

use yii\helpers\Html;
use app\components\ToolButton;
use webvimark\modules\UserManagement\models\User;
use app\components\Headers;

echo Html::CssFile('@web/css/personal-account-settings.css');
$this->title = "New Request";

if (empty($errors))
{
	if (!empty($success))
	{
		echo '<div class="alert alert-success row" role="alert">';
		echo $success;
		echo '</div>';
	}
	if (!empty($warning))
	{
		echo '<div class="alert alert-warning row" role="alert">';
		echo $warning;
		echo '</div>';
	}

}
else
{
	echo '<div class="alert alert-danger row" role="alert">';
	echo $errors;
	echo '</div>';

}

Headers::begin() ?>
<?php echo Headers::widget(
['title'=>'', 
    
])
?>
<?Headers::end()?>



<?= ToolButton::createButton("User administration", "",['/personal/superadmin-actions']) ?>
<br />
<?= ToolButton::createButton("Ticket support administration", "",['/ticket-admin/index']) ?>
<br />
<?= ToolButton::createButton("Dockerhub image requests", "",['/administration/dockerhub-image-list']) ?>
<br />
<?= ToolButton::createButton("External data repositories", "",['/administration/external-repositories']) ?>
<br />
<?= ToolButton::createButton("System configuration", "",['/administration/system-configuration']) ?>
<br />
<!-- <?= ToolButton::createButton("Experiments", "",['/administration/experiments']) ?>
<br /> -->
