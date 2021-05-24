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

/* @var $this \yii\web\View */
/* @var $content string */
use app\widgets\Alert;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;
use webvimark\modules\UserManagement\models\User;
use app\components\SupportWindow;
use app\components\ProjectWindow;
use app\components\NotificationWidget;



$footerImage=Html::img(Yii::$app->params['logo-footer'],['class'=>'footer-elixir-image']);
$footerImageLink=Html::a($footerImage,'https://elixir-greece.org',['target'=>'_blank']);
if (isset(Yii::$app->params['twitter_url']))
{
    $twitter_icon='<i class="fab fa-twitter fa-2x"></i>';
    $twitter_link=Html::a($twitter_icon,Yii::$app->params['twitter_url'],['target'=>'_blank']);
}
else
{
    $twitter_icon='';
    $twitter_link='';
}
if (isset(Yii::$app->params['youtube_url']))
{
    $youtube_icon='<i class="fab fa-youtube fa-2x" style="color:red"></i>';
    $youtube_link=Html::a($youtube_icon,Yii::$app->params['youtube_url'],['target'=>'_blank']);
}
else
{
    $youtube_icon='';
    $youtube_link='';
}

echo Html::cssFile('@web/css/components/notificationWidget.css');
$this->registerJsFile('@web/js/components/notificationWidget.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
if (isset(Yii::$app->params['favicon']) && (!empty(Yii::$app->params['favicon'])))
{
    $this->registerLinkTag(['rel' => 'icon', 'type' => 'image/png', 'href' => Yii::$app->params['favicon']]);
}



AppAsset::register($this);

//Include font-awsome icons
echo Html::cssFile('https://use.fontawesome.com/releases/v5.5.0/css/all.css', ['integrity'=> 'sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU', 'crossorigin'=> 'anonymous']);
?>



<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode(Yii::$app->params['name']) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php

    if (!isset($_SESSION['selected_project']))
    {
        $_SESSION['selected_project']='';
    }

    if (Yii::$app->user->getIsGuest() == false)
    {
        SupportWindow::show(Yii::$app->request->absoluteUrl);
    }
    if (Yii::$app->user->getIsGuest() == false)
    {
        if (Yii::$app->params['standalone']==false)
        {
            ProjectWindow::show(Yii::$app->request->absoluteUrl);
        }
        
    }

    $headerLogo=(isset(Yii::$app->params['logo-header']) && !empty(Yii::$app->params['logo-header'])) ? Yii::$app->params['logo-header'] : '@web/img/schema-logo-03.png';
    NavBar::begin([
        // 'brandLabel' => Yii::$app->name,
        'brandLabel' => Html::img($headerLogo,['class'=>"navbar-logo"]),
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            // 'class' => 'navbar-default navbar-fixed-top',
            'class' => 'navbar navbar-default navbar-fixed-top navbar-expand-md bg-light', 
        ],
    ]);

    // print_r(User::getCurrentUser());
    // exit(0);
    $menuItems=[];
            //['label' => 'Home', 'url' => ['/site/index']],];
            // ['label' => 'About', 'url' => ['/site/about']],];
            // ['label' => 'Contact', 'url' => ['/site/contact']]];
    if(Yii::$app->user->getIsGuest() == true)
    {
        $menuItems[]=['label' => 'Login', 'url'=> ['/user-management/auth/login']];
    }
    if(Yii::$app->user->getIsGuest() == false)
    {
    
        $menuItems[]=['label' => 'Software', 'url' => ['/software/index']];
        $menuItems[]=['label' => 'Workflows', 'url' => ['/workflow/index']];
        $menuItems[]=['label' => 'Data','url' => ['/filebrowser/index']];
        $menuItems[]=['label' => 'Job history','url' => ['/software/history']];
        $menuItems[]=['label' => 'RO-Crate objects','url' => ['/software/ro-crate-history']];
        $menuItems[]=['label' => 'Help', 
                      'url' => 'https://docs.google.com/document/d/1NokaxEG5e2O5Wmv6OPnOlJlmw5lKt6JGBjMMiCgvnkI/edit?usp=sharing',
                      'linkOptions' => ['target'=>'_blank']];
    }

    if(User::hasRole("Admin", $superAdminAllowed = true)){
       $menuItems[]=['label' => 'Admin Options', 'url' => ['/administration/index']];
    }

    if(Yii::$app->user->getIsGuest() == false)
    {
        $username=explode('@',User::getCurrentUser()['username'])[0];
        $menuItems[]=[
            'label' => 'Logout (' . $username . ')',
            'url' => ['/user-management/auth/logout'],
            'linkOptions' => ['data-method' => 'post']
        ];

        $notifications=NotificationWidget::createMenuItem();

        $menuItems[]=
        [
            'label'=>$notifications[0],
            'items'=>$notifications[1],
        ];
    }

    echo Nav::widget([

        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => $menuItems,
        'encodeLabels' => false,
        // [
        //     ['label' => 'Home', 'url' => ['/site/index']],
        //     ['label' => 'About', 'url' => ['/site/about']],
        //     ['label' => 'Contact', 'url' => ['/site/contact']],
        //     Yii::$app->user->isGuest ? (
        //         ['label' => 'Login', 'url' => ['/site/login']]
        //     ) : (
        //         '<li>'
        //         . Html::beginForm(['/site/logout'], 'post')
        //         . Html::submitButton(
        //             'Logout (' . Yii::$app->user->identity->username . ')',
        //             ['class' => 'btn btn-link logout']
        //         )
        //         . Html::endForm()
        //         . '</li>'
        //     )
        // ],
    ]);
    NavBar::end();
    ?>

    <div class="container">
        <!-- <div class="row">
            <div class="alert alert-danger col-md-12" role="alert">
                Due to maintenance schema is not available right now. Please try again later.
            </div>
        </div>  -->
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer bg-light">
    <div class="container">
        <p class="pull-left">&copy; <?=Yii::$app->params['copyright']?></p>
        <p class="powered col-md-2">Powered by  <?= Html::a('SCHeMa', 'https://github.com/athenarc/schema', 
        ['target'=>'_blank'])?></p>
        <p class="col-md-offset-2 col-md-3"><?=$footerImageLink?></p>
        <p class="col-md-offset-1 col-md-2"><?=Html::a('Privacy & cookie policy','https://egci-beta.imsi.athenarc.gr/index.php?r=site/privacy')?></p>
        <p class="pull-right"><?=$twitter_link?>&nbsp;<?=$youtube_link?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
