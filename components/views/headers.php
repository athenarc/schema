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

namespace app\components\views;
use yii\helpers\Html;



echo Html::cssFile('@web/css/components/headers.css');

?>

<div class='row'>
	<div class="col-md-6 headers">
		<span><?=$title?></span><span class="subtitle"><?=empty($subtitle)?'':"/$subtitle"?>
	</div>
	<div class="col-md-6 header-buttons">
		<?php
		if ($search==true)
		{?>
			 	<?php echo $search_content?>
		<?php
		}
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


