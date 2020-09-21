
<?php

use app\components\FileListSoftware;
use yii\helpers\Html;

echo Html::cssFile('@web/css/software/fill-array-field.css');
echo Html::cssFile('https://use.fontawesome.com/releases/v5.5.0/css/all.css', ['integrity'=> 'sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU', 'crossorigin'=> 'anonymous']);
echo Html::cssFile('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css',['integrity'=> 'sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u', 'crossorigin'=> 'anonymous']);
$this->registerJsFile('@web/js/software/fill-array-field.js', ['depends' => [\yii\web\JqueryAsset::className()]] );

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <div class="container">
       <br />
		<br />
        <div class="row">
            <div class=col-md-12>
                <h1>Fill values for the array field:</h1>
            </div>
        </div>
        <div class="row">&nbsp;</div> 
        <?=Html::beginForm()?>
        <?php
        foreach ($fields as $field)
        {
        ?>
        <div class="row">
            <div class="col-md-1">
                <?=Html::textInput('field',$field,['class'=>'value_input'])?>
            </div>
        </div>

        <?php
        }
        ?>
        <div class="row fields-end">&nbsp;</div>
        <?php
        $plus_sign='<i class="fas fa-plus"></i>';
        ?>
        <div class="row">
            <div class='col-md-12'>
                <?=Html::a("$plus_sign Add value",'javascript:void(0);',['id'=>'add-field-button', 'class'=>'btn btn-success btn-md'])?>
            </div>
        </div>
        <div class="row">&nbsp;</div>
		<div class="row">
            <div class='col-md-12'>
    			<?=Html::a('Submit values','javascript:void(0);',['id'=>'select-confirm-button', 'class'=>'btn btn-success btn-md'])?>
    			<?=Html::a('Cancel','javascript:void(0);',['id'=>'select-close-button', 'class'=>'btn btn-danger btn-md'])?>
            </div>
		</div>
        <?=Html::hiddenInput('hidden_caller',$caller,['id'=>'hidden_caller'])?>
        

        <div class='hidden_new_input_html'>
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-1">
                    <?=Html::textInput('field','',['class'=>'value_input'])?>
                </div>
            </div>
        </div>



        <?=Html::endForm()?>
</div>


<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>


	