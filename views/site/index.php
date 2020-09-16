<?php
/**
 * View file for the index page
 * 
 * @author: Kostis Zagganas
 * First version: Dec 2018
 */

use yii\helpers\Html;

/* @var $this yii\web\View */

$this->title = 'SCHeMa scheduler';
?>
<div class="site-index">

    <div class="jumbotron">
        <?=Html::img('@web/img/logo.png') ?>
        <!-- <h1>Congratulations!</h1> -->
        <br />
        <br />

        <p class="lead">Scheduling scientific containers on a cluster of heterogeneous machines</p>

        <p><?=Html::a('Run software images',['software/index'],['class'=>"btn btn-lg btn-success"])?></p>
    </div>
    <!-- <h1 style="color:red; text-align:center;">Due to an upgrade in the GRNET's infrastructure, SCHeMa is currently inoperational. Please try again later. Thank you. </h1> -->
    <div class="body-content">


    </div>
</div>
