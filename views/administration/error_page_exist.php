<?php

use yii\helpers\Html;
use app\components\Headers;

echo Html::CssFile('@web/css/project/vm-details.css');

$this->title="Authorization error";

$back_icon='<i class="fas fa-arrow-left"></i>';

Headers::begin() ?>
<?php echo Headers::widget(
['title'=>"Page does not exist.", 
	'buttons'=>
	[
		['fontawesome_class'=>$back_icon,'name'=> 'Back', 'action'=> ['/administration/manage-pages'], 'type'=>'a', 'options'=>['class'=>'btn btn-default'] ],
		
	]
])
?>
<?Headers::end()?>



<div class="row">
	<div class="col-md-12"><h3>This page does not exist.</h3></div>
</div>
