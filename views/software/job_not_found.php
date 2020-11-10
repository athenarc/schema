<?php


/**
 * This view file prints the history of software runs made by a user
 * 
 * @author: Kostis Zagganas
 * First version: March 2019
 */

use yii\helpers\Html;
use yii\widgets\LinkPager;
use app\components\Headers;

$this->title="Job not found";

// $approve_icon='<i class="fas fa-check"></i>';
// $reject_icon='<i class="fas fa-times"></i>';
$back_icon='<i class="fas fa-arrow-left"></i>';
$logs_icon='<i class="fas fa-cloud-download-alt"></i>';

Headers::begin() ?>
<?php echo Headers::widget(
['title'=>$this->title, 
    'buttons'=>
    [
        ['fontawesome_class'=>$back_icon,'name'=> 'Back', 'action'=>['/software/history'],
        'options'=>['class'=>'btn btn-default'], 'type'=>'a'], 
        
    ],
])
?>
<?Headers::end()?>


<div class="row"><div class="col-md-12 text-center"><h3>We could not find a job with an id <?=$jobid?> in our system.</h3></div></div>


 

