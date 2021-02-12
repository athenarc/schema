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

$this->title='MPI cluster already running';
$back_icon='<i class="fas fa-arrow-left"></i>'; 


?>

<div class="row">&nbsp;</div>

<div class="alert alert-warning" role="alert">
	<div class='row'><div class='text-center col-md-12'><h3>Unfortunately, our cluster can only support one active OpenMPI job at a time. Please wait a few minutes and try again.</h3></div></div>
</div>

<div class="row"><div class='col-md-12 text-center'><?= Html::a("$back_icon Back to available software", ['/software/index'], ['class'=>'btn btn-default']) ?></div></div>