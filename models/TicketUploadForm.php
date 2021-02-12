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
namespace app\models;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;
// use ricco\ticket\Module;

class TicketUploadForm extends Model
{
    /**
     * @var UploadedFile
     */
    public $imageFiles;
    public $nameFile;

    /** @var  Module */
    private $module;

    /**
     * @inheritdoc
     */
    public function init()
    {
        // $this->module = Module::getInstance();
        parent::init();
    }

    public function rules()
    {
        return [
            [['imageFiles'], 'file',
                'skipOnEmpty' => true,
                'extensions' => TicketConfig::uploadFilesExtensions,
                'maxFiles' => TicketConfig::uploadFilesMaxFiles,
                'maxSize' => TicketConfig::uploadFilesMaxSize
            ],
        ];
    }

    public function upload()
    {
        if ($this->validate()) {
            $dir = TicketConfig::uploadFilesDirectory;
            $dirReduced = TicketConfig::uploadFilesDirectory.'/reduced';

            if (!file_exists(Yii::getAlias($dir))) {
                mkdir(Yii::getAlias($dir));
                mkdir(Yii::getAlias($dirReduced));
            }

            foreach ($this->imageFiles as $file) {
                $hashName = md5($file->baseName . time()) . '.' . $file->extension;
                $fullHashName = Yii::getAlias($dir) . '/'. $hashName;
                $fullReducedHashName = Yii::getAlias($dirReduced) . '/'. $hashName;
                $this->nameFile[] = ['real' => $hashName, 'document' => $file->baseName . '.' . $file->extension];
                $file->saveAs($fullHashName);
                $this->resice($fullHashName, 1024);
                copy($fullHashName, $fullReducedHashName);
                $this->resice($fullReducedHashName, 100);
            }
            return true;
        } else {
            return false;
        }
    }

    public function getName()
    {
        return $this->nameFile;
    }

    public function resice($src, $widthNew)
    {
        if (!$this->isImage($src)) {
            return false;
        }
		
        $size = getimagesize($src);

        $format = strtolower(substr($size['mime'], strpos($size['mime'], '/')+1));
        $icfunc = "imagecreatefrom" . $format;

        $width = $size[0];
        $height = $size[1];

        if( $height > $width ) {
            // коэффициент
            $k = $widthNew / $height;
            $new_w = round( $width * $k );
            $new_h = $widthNew;

        } elseif( $width >= $height ) {
            // коэффициент
            $k = $widthNew / $width;
            $new_w = $widthNew;
            $new_h = round( $height * $k );
        }

        $isrc = $icfunc($src);
        $idest = imagecreatetruecolor($new_w, $new_h);

        imagecopyresampled($idest, $isrc, 0, 0, 0, 0,
            $new_w, $new_h, $width, $height);

        imagejpeg($idest, $src, 100);
    }

	    protected function isImage($src)
    {
        $mimeType = mime_content_type($src);
        return substr($mimeType, 0, 5) == 'image';
    }
	
}
