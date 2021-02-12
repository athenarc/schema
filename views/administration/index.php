<?php

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
