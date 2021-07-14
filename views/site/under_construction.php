<?php

/**
 * View file for the About page 
 * 
 * @author: Kostis Zagganas
 * First version: Dec 2018
 */

use yii\helpers\Html;

$this->title = 'Under construction';

echo Html::CssFile('@web/css/site/under_construction.css');
$back_icon='<i class="fas fa-arrow-left"></i>';
// $this->params['breadcrumbs'][] = $this->title;
?>
<div class='title row'>
	<div class="col-md-offset-11 col-md-1 float-right">
		<?= Html::a("$back_icon Back", ['site/index'], ['class'=>'btn btn-default']) ?>
	</div>
</div>
<div class="row">
    <div class="col-md-12 text-center">
    	<h1>This page is under construction.</h1>
    </div>
</div>

<div class="row">&nbsp;</div>

<div class="row">
	<div class="col-md-12 text-center">
    	<?=Html::img('@web/img/under-construction.jpg',['alt' => 'Under construction','class'=>'constr-image'])?>
    </div>
</div>


