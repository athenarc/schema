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
use yii\widgets\LinkPager;
use yii\bootstrap\NavBar;
use yii\bootstrap\Nav;
use yii\helpers\Url;

use yii\widgets\ActiveForm;
use app\components\Headers;
use app\components\MagicSearchBox;
use webvimark\modules\UserManagement\models\User as Userw;  



// echo Html::cssFile('@web/css/administration/uploadDatasetDefaults.css');
// $this->registerJsFile('@web/js/administration/uploadDatasetDefaults.js', ['depends' => [\yii\web\JqueryAsset::className()]] );

$this->title="System configuration";
$back_button='<i class="fas fa-arrow-left"> </i>';
$submit_button='<i class="fas fa-check"></i>';

echo Html::CssFile('@web/css/site/configure.css');
$this->registerJsFile('@web/js/site/configure.js', ['depends' => [\yii\web\JqueryAsset::className()]] );

Headers::begin() ?>
<?php echo Headers::widget(
['title'=>$this->title, 
    'buttons'=>
    [ 
        ['fontawesome_class'=>$back_button,'name'=> 'Back', 'action'=>['/administration/index'],
         'options'=>['class'=>'btn btn-default'], 'type'=>'a'] 
    ],
])
?>
<?php Headers::end()?>
<div class="row">&nbsp;</div>

<?php $form=ActiveForm::begin()?>

<div class="row">
	<div class="col-md-12 text-center"><h3>Administration Email</h3></div>
	<div class="col-md-offset-3 col-md-6 text-center">
			<?=$form->field($configuration, 'admin_email')->textInput()->label('')?>
	</div>
	<div class="col-md-offset-3 col-md-6 text-center">
			<?= $form->field($configuration,'home_page')->dropDownList($pages,['prompt'=>'Please select a page', 'disabled'=>(empty($pages))? true : false ])?>
			<?= $form->field($configuration,'help_page')->dropDownList($pages,['prompt'=>'Please select a page', 'disabled'=>(empty($pages))? true : false ])?>
			<?= Html::a('Manage pages', ['/administration/manage-pages'], ['class'=>'btn btn-secondary']) ?>
	</div>
</div>

<div class="row">&nbsp;</div>
<div class="row">&nbsp;</div>
<div class="col-md-12 form-group text-center">
	<?=Html::submitButton("$submit_button Submit", ['class'=>'btn btn-primary'] )?>
</div>
<?php
$form = ActiveForm::end(); 
?>





	

	