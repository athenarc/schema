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
$this->title = "Admin panel";


Headers::begin() ?>
<?php echo Headers::widget(
['title'=>$this->title, 
    
])
?>
<?Headers::end()?>



<?= ToolButton::createButton("User administration", "",['/personal/superadmin-actions']) ?>
<br />
<?= ToolButton::createButton("Ticket support administration", "",['/ticket-admin/index']) ?>
<br />
<?= ToolButton::createButton("Dockerhub image requests", "",['/administration/dockerhub-image-list']) ?>
<br />
<?= ToolButton::createButton("Jupyter admin panel", "",['/administration/jupyter']) ?>
<br />
<?= ToolButton::createButton("External data repositories", "",['/administration/external-repositories']) ?>
<br />
<?= ToolButton::createButton("System configuration", "",['/administration/system-configuration']) ?>
<br />
<?php
if (isset(Yii::$app->params['metrics_url']) && (!empty(Yii::$app->params['metrics_url'])))
{
?>

	<?= ToolButton::createButton("Cluster Metrics", "", Yii::$app->params['metrics_url'], ['target'=>'_blank']) ?>
	<br />

<?php
}
?>
<?= ToolButton::createButton("Manage TRS endpoints", "",['/administration/manage-trs']) ?>
<br />
<!-- <?= ToolButton::createButton("Experiments", "",['/administration/experiments']) ?>
<br /> -->
