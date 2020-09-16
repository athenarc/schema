<?php

use yii\helpers\Html;
use app\components\ToolButton;
use webvimark\modules\UserManagement\models\User;

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

?>

<!--  <div class="text-center container-fluid">
 	<div class="row">
 		<div class="col-md-12 account-settings-title">
 			<h1>Select a </h1>
 		</div>
 	</div>
 </div> -->

<?= ToolButton::createButton("User administration", "",['/personal/superadmin-actions']) ?>
<br />
<?= ToolButton::createButton("Ticket support administration", "",['/ticket-admin/index']) ?>
<br />
<?= ToolButton::createButton("COVID-19 dataset requests", "",['/filebrowser/covid-list-applications']) ?>
<br />
