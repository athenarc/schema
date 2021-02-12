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

    <div class="text-center">
        <?=Html::img('@web/img/schema-logo-03.png') ?>
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
