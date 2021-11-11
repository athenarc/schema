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

$this->title="Jupyter Servers";

Headers::begin() ?>
<?php echo Headers::widget(
['title'=>$this->title, 
    'buttons'=>
    [
        
    ],
])
?>
<?php Headers::end()?>

<div class=" table-responsive">
    <table class="table table-striped">
        <thead>
            <th class="col-md-3">Project</th>
            <th class="col-md-1">Cores</th>
            <th class="col-md-1">RAM (GB)</th>
            <th class="col-md-3">Image</th>
            <th class="col-md-2">Expires on</th>
            <th class="col-md-2"></th>
        </thead>
        <tbody>
        <?php
        foreach ($projects as $name => $resources)
        {
            if (isset($resources['server']))
            {
                $date=new DateTime($resources['server']->expires_on);
                $date=$date->format('d-m-Y');
            }
            else
            {
                $date='N/A';
            }
        ?>
        <tr>
            <td class="col-md-3"><strong><?=$name?><strong></td>
            <td class="col-md-1"><?=$resources['cpu']?></td>
            <td class="col-md-1"><?=$resources['memory']?></td>
            <td class="col-md-3"><?=isset($resources['server'])? $images[$resources['server']->image] : 'N/A'?></td>
            <td class="col-md-2"><?=$date?></td>
            <td class="col-md-2">
                <?php
                    $start_icon='<i class="fas fa-play"></i>';
                    $stop_icon='<i class="fas fa-stop"></i>';
                    $access_icon='<i class="fas fa-external-link-alt"></i>';
                    $start_url=Url::to(['/jupyter/start-server','project'=>$name]);
                    $stop_url=Url::to(['/jupyter/stop-server','project'=>$name]);
                    if (isset($resources['server']))
                    {
                        $start_class="btn start-btn disabled";
                        $stop_class="btn stop-btn";
                        $access_class="btn access-btn";
                        $access_url=$resources['server']->url;
                    }
                    else
                    {
                        $start_class="btn start-btn";
                        $stop_class="btn stop-btn disabled";
                        $access_class="btn access-btn disabled";
                        $access_url='';
                    }
                ?>
                <?=Html::a($start_icon,$start_url,['class'=>$start_class, 'title'=> "Start server"])?>
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
