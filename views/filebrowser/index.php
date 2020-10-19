<?php
use yii\helpers\Html;
/* @var $this \yii\web\View */
/* @var $connectorRoute string */

// Conflict between bootstrap-button.js and jQuery UI
// https://github.com/twbs/bootstrap/issues/6094
// $this->registerJs('jQuery.fn.btn = jQuery.fn.button.noConflict();');
echo Html::cssFile('@web/css/filebrowser/index.css');

$this->title = Yii::t('app', 'Data Browser');
$covidButtonIcon='<i class="fa fa-download" aria-hidden="true"></i>';
$covidDatasetRequest='<i class="fas fa-upload"></i>';

foreach ($messages as $message)
{
?>
	<div class="alert alert-<?=$message['class']?> row" role="alert"><?= $message['message'] ?></div>
<?php
}
?>
<div class="row">&nbsp;</div>
<!-- <div class="alert alert-warning" role="alert">
	<div class="row"><div class="col-md-12">In response to the COVID-19 outbreak, we provide a range of datasets that can be used by researchers working on SARS-CoV-2 research. If you are interested to load these datasets to your home folder, then click on the "Get COVID-19 data" button. You can anytime modify or remove the files using the file browser interface.</div></div>
	<div class="row"><div class="col-md-12 text-center covid-button-container"><?=Html::a("$covidButtonIcon Get COVID-19 data",['/filebrowser/get-covid-data'],['class'=>'btn btn-info'])?>&nbsp;&nbsp;<?=Html::a("$covidDatasetRequest Request new dataset",['/filebrowser/request-dataset'],['class'=>'btn btn-primary'])?></div></div>
</div> -->
<div class="row">&nbsp;</div>

<?= alexantr\elfinder\ElFinder::widget([
    'connectorRoute' => $connectorRoute,
    'settings' => [
        'height' => 640,
    ],
]) ?>