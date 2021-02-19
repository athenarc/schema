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
		 ['fontawesome_class'=>'<i class="fa fa-history" aria-hidden="true"></i>','name'=> 'Downloaded Datasets', 
	 	'action'=>['filebrowser/dataset-history'],
	    'options'=>['class'=>'btn btn-default'], 'type'=>'a'], 
		['fontawesome_class'=>'<i class="fas fa-upload"></i>','name'=> 'Upload Dataset', 
		'action'=>['filebrowser/upload-dataset'],
	     'options'=>['class'=>'btn btn-default upload-dataset',], 'type'=>'a'],
	    ['fontawesome_class'=>'<i class="fas fa-download"></i>','name'=> 'Download Dataset', 
	 	'action'=>['filebrowser/download-dataset'],
	    'options'=>['class'=>'btn btn-default download-dataset'], 'type'=>'a'],

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
?>  

