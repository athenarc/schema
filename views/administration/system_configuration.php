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



// echo Html::cssFile('@web/css/administration/uploadDatasetDefaults.css');
// $this->registerJsFile('@web/js/administration/uploadDatasetDefaults.js', ['depends' => [\yii\web\JqueryAsset::className()]] );

$this->title="System configuration";
$back_button='<i class="fas fa-arrow-left"> </i>';
$submit_button='<i class="fas fa-check"></i>';



?>
<<?Headers::begin() ?>
<?php echo Headers::widget(
['title'=>$this->title, 
    'buttons'=>
    [ 
        ['fontawesome_class'=>$back_button,'name'=> 'Back', 'action'=>['/administration/index'],
         'options'=>['class'=>'btn btn-default'], 'type'=>'a'] 
    ],
])
?>
<?Headers::end()?>
<div class="row">&nbsp;</div>

<?php $form=ActiveForm::begin()?>
<div class="text-center"><h3>Administration Email</h3></div>
<div class="col-md-offset-3 col-md-6 text-center">
		<?=$form->field($configuration, 'admin_email')->textInput()->label('')?>
</div>
<div class="row">&nbsp;</div>
<div class="row">&nbsp;</div>
<div class="col-md-12 form-group text-center">
	<?=Html::submitButton("$submit_button Submit", ['class'=>'btn btn-primary'] )?>
</div>
<?php
$form = ActiveForm::end(); 
?>


