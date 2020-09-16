<?php
use yii\helpers\Html;

echo Html::cssFile('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css',['integrity'=> 'sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u', 'crossorigin'=> 'anonymous']);
$this->registerJsFile('@web/js/software/image-description.js', ['depends' => [\yii\web\JqueryAsset::className()]] );

?>
<div class="row">&nbsp;</div>
<div class="row">&nbsp;</div>
<div class="row"><div class="col-md-12 text-center"><?=$model->description?></div></div>
<div class="row">&nbsp;</div>
<div class="row">&nbsp;</div>
<div class="row"><div class="col-md-12 text-center"><?=Html::a('Close','javascript:void(0);',['id'=>'close-button', 'class'=>'btn btn-default btn-md'])?></div></div>