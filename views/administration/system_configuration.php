<?php

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


