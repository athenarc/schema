<?php

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

