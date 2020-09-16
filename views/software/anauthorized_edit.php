<?php
use yii\helpers\Html;

$this->title='Non-existent software';
$back_icon='<i class="fas fa-arrow-left"></i>';  


?>

<div class="row">&nbsp;</div>

<div class="alert alert-danger" role="alert">
	<div class='row'><div class='text-center col-md-12'><h3>Error: software "<?=$name?> v.<?=$version?>" does not exist or you are not authorized to edit its metadata.</h3></div></div>
</div>

<div class="row"><div class='col-md-12 text-center'><?= Html::a("$back_icon Back to available software", ['/software/index'], ['class'=>'btn btn-default']) ?></div></div>