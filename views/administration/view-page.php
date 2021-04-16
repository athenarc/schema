<?php

use yii\helpers\Html;
use app\components\Headers;

echo Html::CssFile('@web/css/project/vm-details.css');

$this->title="View page \"$page->title\"";

$back_icon='<i class="fas fa-arrow-left"></i>';

Headers::begin() ?>
<?php echo Headers::widget(
['title'=>"$page->title", 
	'buttons'=>
	[
		['fontawesome_class'=>$back_icon,'name'=> 'Back', 'action'=> ['/administration/manage-pages'], 'type'=>'a', 'options'=>['class'=>'btn btn-default'] ],
		
	]
])
?>
<?Headers::end()?>


<?=$page->content?>
