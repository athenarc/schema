<?php
/**
 * @var $this yii\web\View
 * @var $model webvimark\modules\UserManagement\models\forms\LoginForm
 */

use webvimark\modules\UserManagement\components\GhostHtml;
use webvimark\modules\UserManagement\UserManagementModule;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\bootstrap\BootstrapAsset;

BootstrapAsset::register($this);

// Yii::$app->response->redirect('https://egci-auth.imsi.athenarc.gr/secure/index.php?return=schema');
$this->title="Login using the ELIXIR AAI Service";

$return='schema';
 // $return='schema_openstack';
// $return='egci-beta';
// $return='rac-loukas';
// $return='rac-kostis';

// print_r($_GET);

$registerImage=Html::img('@web/img/elixir-register.png',['width'=>'200px']);
$loginImage=Html::img('@web/img/elixir-login.png',['width'=>'200px']);

$register="https://perun.elixir-czech.cz/registrar/?vo=elixir&targetNew=https://egci-beta.imsi.athenarc.gr/index.php?r=user-management/auth/login&targetexisting=https://egci-auth.imsi.athenarc.gr/elixir/index.php?return=$return";
$login="https://egci-auth.imsi.athenarc.gr/secure/index.php?return=$return";

?>
<div class="container">
	<div class="row"><div class="col-md-12 text-center"><h1><?=Html::encode($this->title)?></h1></div></div>
	<div class="row">&nbsp;</div>
	<div class="row">&nbsp;</div>
	<div class="row">&nbsp;</div>
	<div class="row"><div class="col-md-12 text-center"><?=Html::a($registerImage,$register)?>&nbsp;&nbsp;<?=Html::a($loginImage,$login)?></div></div>

	<div class="row"><div class="col-md-12"><h2>How can I sign-in to the resource management system?</h2></div></div>
		<ul class="square-list">
			<li>To sign-in to the resource management system an ELIXIR-AAI account is required (just click on the “Register” button).</li>
			<li>When you have your ELIXIR-AAI account ready, you can sign-in using your credentials (click the “Login” button).</li>
			<li>During the previous steps you have to give your consent that ELIXIR-AAI and EG-CI’s resource management system will have access to basic account information required for the flawless operation of the system.</li> 
		</ul>
		<div class="row"><div class="col-md-12">Learn <?=Html::a('more','https://elixir-europe.org/services/compute/aai', ['target'=>'_blank'])?> about the ELIXIR-AAI.</div></div>

</div>
