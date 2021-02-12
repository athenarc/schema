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

echo Html::cssFile('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css',['integrity'=> 'sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u', 'crossorigin'=> 'anonymous']);
$this->registerJsFile('@web/js/software/image-description.js', ['depends' => [\yii\web\JqueryAsset::className()]] );

?>
<div class="row">&nbsp;</div>
<div class="row">&nbsp;</div>
<div class="row"><div class="col-md-12 text-center"><?=$model->description?></div></div>
<div class="row">&nbsp;</div>
<div class="row">&nbsp;</div>
<div class="row"><div class="col-md-12 text-center"><?=Html::a('Close','javascript:void(0);',['id'=>'close-button', 'class'=>'btn btn-default btn-md'])?></div></div>