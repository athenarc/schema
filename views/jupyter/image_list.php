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

$this->title="List of Jupyter images";

$back_icon='<i class="fas fa-arrow-left"></i>';
$add_icon='<i class="fas fa-plus"></i>';
Headers::begin() ?>
<?php echo Headers::widget(
['title'=>$this->title, 
    'buttons'=>
    [
        ['fontawesome_class'=>$add_icon,'name'=> 'New Image', 'action'=>['/jupyter/new-image'],
        'options'=>['class'=>'btn btn-primary'], 'type'=>'a'],
        ['fontawesome_class'=>$back_icon,'name'=> 'Back', 'action'=>['/administration/jupyter'],
        'options'=>['class'=>'btn btn-default'], 'type'=>'a'],    
    ],
])
?>
<?php Headers::end()?>

<div class=" table-responsive">
    <table class="table table-striped">
        <thead>
            <th class="col-md-3">Description</th>
            <th class="col-md-3">Dockerhub image</th>
            <th class="col-md-1"></th>
        </thead>
        <tbody>
        <?php
        foreach ($images as $image)
        {
        ?>
        <tr>
            <td class="col-md-3"><?=$image->description?></td>
            <td class="col-md-3"><?=$image->image?></td>
            <td class="col-md-1">
                <?php
                    $edit_icon='<i class="fas fa-edit"></i>';
                    $delete_icon='<i class="fas fa-times"></i>';
                    
                ?>
                <?=Html::a($edit_icon,['/jupyter/edit-image', 'id'=>$image->id],['class'=>'btn edit-btn', 'title'=> "Edit image"])?>
                <?=Html::a($delete_icon,['/jupyter/delete-image', 'id'=>$image->id],['class'=>'btn delete-btn', 'title'=> "Delete image"])?>
            </td>

        </tr>
        <?php
        }
        ?>
        </tbody>
    </table>
</div>
