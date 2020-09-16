<?php
use yii\helpers\Html;

$this->title='Non-existent project';
$back_icon='<i class="fas fa-arrow-left"></i>'; 


?>

<div class="row">&nbsp;</div>

<div class="alert alert-danger" role="alert">
	<div class='row'><div class='text-center col-md-12'><h3>Error: project "<?=$project?>" is not available. Either it is not an active project or you are have not been added as a participant.</h3></div></div>
</div>

<div class="row"><div class='col-md-12 text-center'><?= Html::a("$back_icon Back to available software", ['/software/index'], ['class'=>'btn btn-default']) ?></div></div>