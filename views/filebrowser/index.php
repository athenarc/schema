<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap\Modal;
use app\components\Headers;
use app\components\DownloadDatasetModal;


echo Html::cssFile('@web/css/filebrowser/index.css');
$this->registerJsFile('@web/js/filebrowser/index.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

$this->title = Yii::t('app', 'Data Browser');

Headers::begin() ?>
<?php echo Headers::widget(
['title'=>$this->title,
'buttons'=>
	[
		 ['fontawesome_class'=>'<i class="fas fa-file"></i>','name'=> 'Downloaded Datasets', 
	 	'action'=>['filebrowser/dataset-history'],
	    'options'=>['class'=>'btn btn-default'], 'type'=>'a'], 
		['fontawesome_class'=>'<i class="fas fa-upload"></i>','name'=> 'Upload Dataset', 
		'action'=>['filebrowser/upload-dataset'],
	     'options'=>['class'=>'btn btn-default upload-dataset',], 'type'=>'a'],
	    ['fontawesome_class'=>'<i class="fas fa-download"></i>','name'=> 'Download Dataset', 
	 	'action'=>null,
	    'options'=>['class'=>'btn btn-default download-dataset', 'data-target'=>"#download-modal"], 'type'=>'a'],

	]
])
?>
<?Headers::end()?>


<?php




foreach ($messages as $message)
{
?>
	<div class="alert alert-<?=$message['class']?> row" role="alert"><?= $message['message'] ?></div>
<?php
}
?>
<div class="row">&nbsp;</div>
<div class="row">&nbsp;</div>

<?= alexantr\elfinder\ElFinder::widget([
    'connectorRoute' => $connectorRoute,
    'settings' => [
        'height' => 640,
    ],
]) ?>

<?php
DownloadDatasetModal::addModal();
?>  

