<?php

/**
 * View file for the About page 
 * 
 * @author: Kostis Zagganas
 * First version: Dec 2018
 */

use yii\helpers\Html;

$this->title = 'About';
// $this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-about">
    <h1><?= Html::encode($this->title) ?></h1>
    The structure of the SCHeMa scheduler system can be seen in the following figure:
    <br />
    <br />

    <?= Html::img('@web/img/system_architecture.png')?>
    
</div>
