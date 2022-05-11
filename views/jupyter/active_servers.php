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
use yii\helpers\Url;
use app\components\Headers;


/*
 * Add stylesheet
 */

echo Html::cssFile('@web/css/jupyter/index.css');
// $this->registerJsFile('@web/js/software/index.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

$this->title="Active Jupyter servers";

$back_icon='<i class="fas fa-arrow-left"></i>';
$expired_icon='<i class="fas fa-exclamation-triangle"></i>';
Headers::begin() ?>
<?php echo Headers::widget(
['title'=>$this->title, 
    'buttons'=>
    [
        ['fontawesome_class'=>$expired_icon,'name'=> 'Clear expired project servers', 'action'=>['/jupyter/stop-expired-servers'],
        'options'=>['class'=>'btn btn-default'], 'type'=>'a'],
        ['fontawesome_class'=>$back_icon,'name'=> 'Back', 'action'=>['/administration/jupyter'],
        'options'=>['class'=>'btn btn-default'], 'type'=>'a'],
        
    ],
])
?>
<?php Headers::end()?>

<div class=" table-responsive">
    <table class="table table-striped">
        <thead>
            <th class="col-md-1">Created by</th>
            <th class="col-md-2">Project</th>
            <th class="col-md-3">Image</th>
            <th class="col-md-1">Created on</th>
            <th class="col-md-2">Expires on</th>
            <th class="col-md-2"></th>
        </thead>
        <tbody>
        <?php
        $now=new DateTime();

        foreach ($servers as $server)
        {
            $creation=new DateTime($server->created_at);
            $creation=$creation->format('d-m-Y');
            $expiration=new DateTime($server->expires_on);
            if ($now>$expiration)
            {
                $expiration=$expiration->format('d-m-Y') . '&nbsp;' . $expired_icon;
            }
            else
            {
                $expiration=$expiration->format('d-m-Y');
            }
            
        ?>
        <tr>
            <td class="col-md-1"><?=explode('@',$server->created_by)[0]?></td>
            <td class="col-md-2"><?=$server->project?></td>
            <td class="col-md-3"><?=$server->image?></td>
            <td class="col-md-1"><?=$creation?></td>
            <td class="col-md-2"><?=$expiration?></td>
            <td class="col-md-2">
                <?php
                    $stop_icon='<i class="fas fa-stop"></i>';
                    $access_icon='<i class="fas fa-external-link-alt"></i>';
                    $stop_url=Url::to(['/jupyter/stop-server','project'=>$server->project,'return'=>'a']);
                    $stop_class="btn stop-btn";
                    $access_class="btn access-btn";
                    $access_url=$server->url;
                ?>
                <?=Html::a($stop_icon,$stop_url,['class'=>$stop_class, 'title'=> "Stop server"])?>
                <?=Html::a($access_icon,$access_url,['class'=>$access_class, 'title'=> "Access server", "target"=>"_blank"])?>
            </td>

        </tr>
        <?php
        }
        ?>
        </tbody>
    </table>
</div>
