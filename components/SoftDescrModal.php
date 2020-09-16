<?php
/**
* Helper for creating modals with software descriptions
*
* @param software_name
* @param software_version
* @param description
*
* @author Kostis Zagganas
*/

namespace app\components;
use yii\helpers\Html;
use Yii;

class SoftDescrModal
{
	public static function addModal($name, $version, $description)
	{

		
		echo "<div class='modal fade' tabindex='-1' role='dialog' id='descr-modal-$name-$version' aria-labelledby='description-modal' aria-hidden='true'>";
		echo '<div class="modal-dialog modal-dialog-centered" role="document">';
		echo '<div class="modal-content">';
		echo '<div class="modal-header">';
		echo "<h5 class='modal-title' id='exampleModalLongTitle'>$name v. $version</h5>";
		echo "<button type='button' class='close' data-dismiss='modal' aria-label='Close'>";
		echo '<span aria-hidden="true">&times;</span>';
		echo '</button>';
		echo '</div>';
		echo '<div class="modal-body">';
		echo "$description";
		echo '</div>';
		echo '<div class="modal-footer">';
		echo "<button type='button' class='btn btn-secondary' data-dismiss='modal'>Close</button>";
		echo '</div>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
		
	}
	
}
