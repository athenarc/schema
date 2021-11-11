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

echo Html::cssFile('@web/css/jupyter/image_list.css');
// $this->registerJsFile('@web/js/software/index.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

$this->title="List of TRS Endpoints";

$back_icon='<i class="fas fa-arrow-left"></i>';
$add_icon='<i class="fas fa-plus"></i>';
Headers::begin() ?>
<?php echo Headers::widget(
['title'=>$this->title, 
    'buttons'=>
    [
        ['fontawesome_class'=>$add_icon,'name'=> 'New Endpoint', 'action'=>['/administration/new-trs-endpoint'],
        'options'=>['class'=>'btn btn-primary'], 'type'=>'a'],
        ['fontawesome_class'=>$back_icon,'name'=> 'Back', 'action'=>['/administration/index'],
        'options'=>['class'=>'btn btn-default'], 'type'=>'a'],    
    ],
])
?>
<?php Headers::end()?>

<div class=" table-responsive">
    <table class="table table-striped">
        <thead>
            <th class="col-md-2">Name</th>
            <th class="col-md-4">URL</th>
            <th class="col-md-2">Push Tools</th>
            <th class="col-md-2">Get Workflows</th>
            <th class="col-md-1"></th>
        </thead>
        <tbody>
        <?php
        foreach ($trss as $trs)
        {
        ?>
        <tr>
            <td class="col-md-2"><?=$trs->name?></td>
            <td class="col-md-4"><?=$trs->url?></td>
            <td class="col-md-1"><?=($trs->push_tools)?"Yes":"No"?></td>
            <td class="col-md-1"><?=($trs->get_workflows)?"Yes":"No"?></td>
            <td class="col-md-1">
                <?php
                    $edit_icon='<i class="fas fa-edit"></i>';
                    $delete_icon='<i class="fas fa-times"></i>';
                    
                ?>
                <?=Html::a($edit_icon,['/administration/edit-trs-endpoint', 'id'=>$trs->id],['class'=>'btn edit-btn', 'title'=> "Edit endpoint"])?>
                <?=Html::a($delete_icon,['/administration/delete-trs-endpoint', 'id'=>$trs->id],['class'=>'btn delete-btn', 'title'=> "Delete endpoint"])?>
            </td>

        </tr>
        <?php
        }
        ?>
        </tbody>
    </table>
</div>
