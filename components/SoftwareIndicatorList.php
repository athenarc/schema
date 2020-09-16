<?php
/*
* Helper for creating link buttons used for tools
*
* @parameter $link : can either be a string which contains the link or an array with the link 
*  in the form of ['controller/action', various parameters]
* @parameter $link_attributes : defaults to []. The user can add other attributes for the link like ['target'=>'_blank']
*
* @author Kostis Zagganas
*/

namespace app\components;
use yii\helpers\Html;
use Yii;

class SoftwareIndicatorList
{
	public static function getIndicators($indicators)
	{

		$output='';
		
		if (!empty($indicators))
		{
			$output.='<div class="indicator-padding"></div>';
		}

		if (isset($indicators['mpi']))
		{
			$output.='<div class="mpi-indicator" title="This software uses OpenMPI">OpenMPI&nbsp;<i class="fa fa-rocket" aria-hidden="true"></i></div>';
		}

		// if (isset($indicators['covid19']))
		// {
		// 	$output.='<div class="covid19-indicator" title="This software has been flagged as relevant to performing COVID-19-related analyses">COVID-19&nbsp;<i class="fa fa-certificate" aria-hidden="true"></i></div>';
		// }
		return $output;
	}	
	
}
