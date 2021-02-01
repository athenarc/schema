<?php




use yii\helpers\Html;
use yii\widgets\LinkPager;
use yii\bootstrap\NavBar;
use yii\bootstrap\Nav;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\components\Headers;  



echo Html::cssFile('@web/css/administration/uploadDatasetDefaults.css');
$this->registerJsFile('@web/js/administration/uploadDatasetDefaults.js', ['depends' => [\yii\web\JqueryAsset::className()]] );

$this->title="External data repositories";
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

<?=Html::beginForm()?>
<?php
foreach ($providers as $i => $provider) 
{

	if($provider->enabled==1)
	{
		$checked="checked";
		$class='';
	}
	else
	{
		$checked="";
		$class='provider-div-hidden';
	}?>

	<?=Html::hiddenInput("enabled-".$i,$provider->enabled,['id'=>"enabled-".$i])?>
	<div class="provider-padding text-center <?=$provider->name?>">
	<div><span class="provider-title">Upload from provider <?=$provider->name?></span>
	<label class="switch" id="<?=$i?>">
			<input type="checkbox" name="checkbox-<?=$i?>" <?=$checked?>> 
			<span class="slider round"></span>
		</label>
	</div>
	</div>
	<?php
	if($provider->name=='Helix')
	{?>
		<div class="row padding hid-<?=$i?> <?=$class?>">
			<div class="col-md-offset-3 col-md-2 label-pd"> <label> Provider ID</label></div>
			<div class="col-md-4 field-rows"> 
			<?=Html::input('text','provider_id-'.$i, $provider->provider_id,
			['class'=>"form-control provider-input-<?=$i?>" ])?>
		</div>
		</div>
		<div class="row padding hid-<?=$i?> <?=$class?>">
			<div class="col-md-offset-3 col-md-2 label-pad"> <label> Community ID</label></div>
			<div class="col-md-4 field-rows">
			<?=Html::input('text','community_id-'.$i, $provider->default_community_id,['class'=>"form-control provider-input-<?=$i?>"])?>
		</div>
		</div>
	<?php
	}?>
	<div class="row">&nbsp;</div> 
<?php
}
?>
<div class="row">&nbsp;</div>
<div class="form-group text-center">
	<?=Html::submitButton("$submit_button Submit", ['class'=>'btn btn-primary'] )?>
</div>
<?php
$form = Html::endForm(); 
?>


