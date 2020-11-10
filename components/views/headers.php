<?php

namespace app\components\views;
use yii\helpers\Html;



echo Html::cssFile('@web/css/components/headers.css');

?>

<div class='row'>
	<div class="col-md-7 headers">
		<span><?=$title?></span><span class="subtitle"><?=empty($subtitle)?'':"/$subtitle"?>
	</div>
	<div class="col-md-5 header-buttons">
		<?php
		foreach ($buttons as $button) 
		{
			if($button['type']=='a')
			{?>
				&nbsp; <span><?=Html::a("$button[fontawesome_class] $button[name]",
				$button['action'], $button['options']);?></span>
			<?php
			}
			elseif($button['type']=='submitButton')
			{?>
				&nbsp; <span><?=Html::submitButton("$button[fontawesome_class] $button[name]", $button['options']);?></span>
			<?php
			}
			elseif($button['type']=='tag')
			{?>
				&nbsp; <span><?=Html::tag("$button[button_name]", "$button[fontawesome_class] $button[name]", 
				$button['options']);?></span>
			<?php
			}
		}?>
	</div>
	<?php echo $special_content;?>
</div>

<div class="row">&nbsp;</div>


