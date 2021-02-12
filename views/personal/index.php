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

echo Html::CssFile('@web/css/personal/account-settings.css');
$this->title = "Administration Panel";
?>

 <div class="text-center container-fluid">
 	<div class="row">
 		<div class="col-md-12 account-settings-title">
 			<h1>Account settings</h1>
 		</div>
 	</div>
 </div>

<!-- <?= ToolButton::createButton("Change User Details", "",['user-management/user/update', 'id' => User::getCurrentUser()['id']]) ?>
<br /> -->
<?= ToolButton::createButton("Change Password", "",['/user-management/auth/change-own-password']) ?>
<br />

