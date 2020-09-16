<?php
use yii\helpers\Html;

$this->title='MPI cluster already running';
$back_icon='<i class="fas fa-arrow-left"></i>'; 


?>

<div class="row">&nbsp;</div>

<div class="alert alert-warning" role="alert">
	<div class='row'><div class='text-center col-md-12'><h3>Unfortunately, our cluster can only support one active OpenMPI job at a time. Please wait a few minutes and try again.</h3></div></div>
</div>

<div class="row"><div class='col-md-12 text-center'><?= Html::a("$back_icon Back to available software", ['/software/index'], ['class'=>'btn btn-default']) ?></div></div>