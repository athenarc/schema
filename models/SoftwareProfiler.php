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
use app\models\Software;

/**
 * This is the model class for table "upload_dataset".
 *
 * @property int $id
 * @property string $dataset_id
 * @property string $provider
 * @property int $user_id
 * @property string $api_key
 */
class SoftwareProfiler extends Model
{
    public static function createAnalysis($software,$fields,$systemMount,$included)
    {
        $data=[];
        $data['commands']=[[$software->script]];
        $errors=[];
        $runErrors=[];
        $fileSizes=[];
        foreach ($fields as $field)
        {
            $result=self::addField($data['commands'],$field,$errors,$software->imountpoint,$systemMount,$fileSizes);
            $data['commands']=$result[0];
            $errors=$result[1];
            $fileSizes=$result[2];
        }
        
        $pid=uniqid();
        $data['image']=$software->image;
        $data['systemMount']=$systemMount;
        $data['nfs']=Yii::$app->params['nfsIp'];
        $data['id']=$pid;
        $data['mountpoint']=$software->imountpoint;
        $data['name']=$software->name;
        $data['version']=$software->version;
        $data['workdir']=$software->workingdir;
        $data['fileSizes']=$fileSizes;
        $data['included']=$included;
        $data['memLimit']=Yii::$app->params['classifierMemLimit'];


        if (!empty($errors))
        {
            return [$errors,[]];
        }

        $folder=Yii::$app->params['profilesFolderPath'] . '/' . $software->name . '/' . $software->version . '/';
        $file=$folder . $data['id'] . '.json';

        exec("mkdir -p $folder");
        exec("mkdir -p $folder/manifests");
        exec("chmod 777 $folder -R");

        $data['folder']=$folder;

        $json=json_encode($data,JSON_UNESCAPED_SLASHES);
        file_put_contents($file,$json);

        /*
         * Run the scripts and retun errors
         */
        $profiler=Software::sudoWrap(Yii::$app->params['scriptsFolder'] . "profiler.py $file");
        $profilerLog="$folder/profiler-log-$pid.txt";
        shell_exec(sprintf("%s > $profilerLog 2>&1 &", $profiler));
        
        $classifier=Software::sudoWrap(Yii::$app->params['scriptsFolder'] . "classifier.py $file");
        $classifierLog="$folder/classifier-log-$pid.txt";
        shell_exec(sprintf("%s > $classifierLog 2>&1 &", $classifier));

        $software->profiled=false;
        $software->model_fields=$included;
        $software->profile_id=$pid;
        $software->save(false);

        return [$errors,$runErrors];
    }
    
    public static function addField($previous,$field,$errors,$mountpoint,$systemMount,$fileSizes)
    {

        $delimiter=';';
        $values=explode($delimiter,$field->value);
        $new=[];
        $finalValue=[];
        foreach ($values as $fvalue)
        {
            if ($field->field_type=='boolean')
            {
                if ($fvalue!="0")
                {
                    $finalValue=[$field->prefix];
                }
                
            }
            else
            {
                #if the field is not of array type
                if (!$field->is_array)
                {
                    if ($field->separate)
                    {
                        $field_gap=' ';
                    }
                    else
                    {
                        $field_gap='';
                    }


                    /*
                     * If field is not optional
                     */
                    if (!$field->optional)
                    {
                        /*
                         * Not optional and empty should throw an error
                         */
                        if (empty($fvalue) && ($field->field_type!='Directory'))
                        {
                            $errors[]="Field $field->name cannon be empty.";
                        }
                        else
                        {
                            if (($field->field_type=='File') || ($field->field_type=='Directory'))
                            {
                                $contFile=$mountpoint . '/' . $fvalue;
                                $finalValue=$field->prefix . $field_gap . $contFile;
                                if ($field->field_type=='File')
                                {
                                    $local=$systemMount . '/' . $fvalue;
                                    exec("ls -la $local | cut -d ' ' -f 5",$out,$ret);
                                    $size=$out[0];
                                    unset($out);
                                    
                                    $fileSizes[$contFile]=$size;
                                }

                            }
                            else
                            {
                                $finalValue=$field->prefix . $field_gap . $fvalue;
                            }
                            $finalValue=trim($finalValue);
                            $finalValue=explode(' ',$finalValue);
                        }

                    }
                    else
                    {
                        if (!empty($fvalue))
                        {
                            if (($field->field_type=='File') || ($field->field_type=='Directory'))
                            {
                                $contFile=$mountpoint . '/' . $fvalue;
                                $finalValue=$field->prefix . $field_gap . $contFile;
                                if ($field->field_type=='File')
                                {
                                    $local=$systemMount . '/' . $fvalue;
                                    exec("ls -la $local | cut -d ' ' -f 5",$out,$ret);
                                    $size=$out[0];
                                    unset($out);
                                    
                                    $fileSizes[$contFile]=$size;
                                }
                            }
                            else
                            {
                                $finalValue=$field->prefix . $field_gap . $fvalue;
                            }

                            $finalValue=trim($finalValue);
                            $finalValue=explode(' ',$finalValue);
                        }
                    }
                }
                /*
                 * if field is array
                 */
                else
                {
                    if (!$field->optional)
                    {
                        /*
                         * Not optional and empty should throw an error
                         */
                        if (empty($fvalue) && ($field->field_type!='Directory'))
                        {
                            $errors[]="Field $field->name cannon be empty.";
                        }
                        else
                        {

                            $tmpArray=explode(';',$fvalue);
                            $finalValue='';
                            /*
                             * if the value is separate from the prefix,
                             * e.g. -Α value1 -A value2
                             * given that the field has an inputBinding selector inside
                             */
                            if ($field->separate)
                            {
                                $field_gap=' ';
                            }
                            /*
                             * if the value is not separate from the prefix,
                             * e.g. -Α=value1 -A=value2
                             * given that the field has an inputBinding selector inside
                             */
                            else
                            {
                                $field_gap='';
                            }
                            if ($field->nested_array_binding)
                            {
                                foreach ($tmpArray as $val)
                                {
                                    if (($field->field_type=='File') || ($field->field_type=='Directory'))
                                    {
                                        $contFile=$mountpoint . '/' . $val;
                                        $finalValue.= ' ' . $field->prefix . $field_gap . $contFile;
                                        if ($field->field_type=='File')
                                        {
                                            $local=$systemMount . '/' . $val;
                                            exec("ls -la $local | cut -d ' ' -f 5",$out,$ret);
                                            $size=$out[0];
                                            unset($out);
                                            
                                            $fileSizes[$contFile]=$size;
                                        }
                                    }
                                    else
                                    {
                                        $finalValue.= ' ' . $field->prefix . $field_gap . $val;
                                    }
                                }

                            }
                            /*
                             * Field has no inside inputBinding selector,
                             * e.g -A value1 value2 value3
                             */
                            else
                            {
                                /* 
                                 * field is separate from the prefix, e.g
                                 * -A=value1,value2,value3
                                 */
                                if ($field->separate)
                                {
                                    $field_gap=' ';
                                }
                                else
                                {
                                    $field_gap='';
                                }

                                if (!empty($field->array_separator))
                                {
                                    $separator=$field->array_separator;
                                }
                                else
                                {
                                    $separator=' ';
                                }

                                $finalValue.=$field->prefix . $field_gap;

                                foreach ($tmpArray as $val)
                                {
                                    if (($field->field_type=='File') || ($field->field_type=='Directory'))
                                    {
                                        // $finalValue.= $mountpoint . '/' . $val . $separator;
                                        $contFile=$mountpoint . '/' . $val;
                                        $finalValue=$contFile . $separator;
                                        if ($field->field_type=='File')
                                        {
                                            $local=$systemMount . '/' . $val;
                                            exec("ls -la $local | cut -d ' ' -f 5",$out,$ret);
                                            $size=$out[0];
                                            unset($out);
                                            
                                            $fileSizes[$contFile]=$size;
                                        }
                                    }
                                    else
                                    {
                                        $finalValue.= ' ' . $val . $separator;
                                    }
                                }
                                $finalValue=trim($finalValue, $separator);
                            }
                            $finalValue=trim($finalValue);
                            $finalValue=explode(' ',$finalValue);
                        }

                    }
                    /* 
                     * Array field is not optional
                     */
                    else
                    {
                        $tmpArray=explode(';',$fvalue);
                        $finalValue='';
                        /*
                         * if the value is separate from the prefix,
                         * e.g. -Α value1 -A value2
                         * given that the field has an inputBinding selector inside
                         */
                        if ($field->separate)
                        {
                            $field_gap=' ';
                        }
                        /*
                         * if the value is not separate from the prefix,
                         * e.g. -Α=value1 -A=value2
                         * given that the field has an inputBinding selector inside
                         */
                        else
                        {
                            $field_gap='';
                        }
                        if ($field->nested_array_binding)
                        {
                            foreach ($tmpArray as $val)
                            {
                                if (($field->field_type=='File') || ($field->field_type=='Directory'))
                                {
                                    $contFile=$mountpoint . '/' . $val;
                                    $finalValue.= ' ' . $field->prefix . $field_gap . $contFile;
                                    if ($field->field_type=='File')
                                    {
                                        $local=$systemMount . '/' . $val;
                                        exec("ls -la $local | cut -d ' ' -f 5",$out,$ret);
                                        $size=$out[0];
                                        unset($out);
                                        
                                        $fileSizes[$contFile]=$size;
                                    }
                                }
                                else
                                {
                                    $finalValue.= ' ' . $field->prefix . $field_gap . $val;
                                }
                            }

                        }
                        /*
                         * Field has no inside inputBinding selector,
                         * e.g -A value1 value2 value3
                         */
                        else
                        {
                            /* 
                             * field is separate from the prefix, e.g
                             * -A=value1,value2,value3
                             */
                            if ($field->separate)
                            {
                                $field_gap=' ';
                            }
                            else
                            {
                                $field_gap='';
                            }

                            if (!empty($field->array_separator))
                            {
                                $separator=$field->array_separator;
                            }
                            else
                            {
                                $separator=' ';
                            }

                            $finalValue.=$field->prefix . $field_gap;

                            foreach ($tmpArray as $val)
                            {
                                if (($field->field_type=='File') || ($field->field_type=='Directory'))
                                {
                                    $contFile=$mountpoint . '/' . $val;
                                    $finalValue.=$contFile . $separator;
                                    if ($field->field_type=='File')
                                    {
                                        $local=$systemMount . '/' . $val;
                                        exec("ls -la $local | cut -d ' ' -f 5",$out,$ret);
                                        $size=$out[0];
                                        unset($out);
                                        
                                        $fileSizes[$contFile]=$size;
                                    }
                                }
                                else
                                {
                                    $finalValue.= ' ' . $val . $separator;
                                }
                            }
                            $finalValue=trim($finalValue, $separator);
                        }
                        $finalValue=trim($finalValue,' ');
                        $finalValue=explode(' ',$finalValue);
                    }

                }
            }
            
            foreach ($previous as $prev)
            {
                $new[]=array_merge($prev,$finalValue);
            }
        }
        return [$new,$errors,$fileSizes];
    }

    public static function getMachineType($fields,$software,$jobFolder,$isystemMount,$iosystemMount,$maxMem)
    {
        // $software=Software::find()->where(['name'=>$name,'version'=>$version])->one();

        if ($maxMem>=64)
        {
            return 'fat-node';
        }
        if (!$software->profiled)
        {
            return 'converged-node';
        }
        
        /*
         * Save fields values to be used 
         * for prediction.
         */
        $modelLine=[];
        $folder= (!empty($iosystemMount))? $iosystemMount : $isystemMount;
        $folder=trim($folder,"'");
        /*
         * For file fields calculate their size in bytes
         */
        foreach ($software->model_fields as $findex)
        {
            $field=$fields[$findex];
            $value=$fields[$findex]->value;

            if ($field->field_type=='File')
            {
                $file=$folder . '/' . $value;
                exec("ls -la $file | cut -d ' ' -f 5",$out,$ret);
                $size=$out[0];
                unset($out);

                $modelLine[]=$file;
                $modelLine[]=$size;

            }
            else
            {
                $modelLine[]=$field->value;
            }
        }
        /*
         * Write fields to a file inside the job folder
         */
        $modelLine=implode('|',$modelLine);
        
        $modelFieldsFile=$jobFolder . '/modelFields.txt';
        file_put_contents($modelFieldsFile, $modelLine);

        /*
         * Predict node type
         */
        $model=Yii::$app->params['profilesFolderPath'] . "/$software->name/$software->version/model-$software->profile_id.pkl";
        $scaler=Yii::$app->params['profilesFolderPath'] . "/$software->name/$software->version/scaler-$software->profile_id.pkl";
        
        $command=Yii::$app->params['scriptsFolder'] . "/node-selector.py $model $scaler $modelFieldsFile 2>&1";
        exec($command,$nodeOut,$ret);
        
        $nodeType=$nodeOut[0];

        return $nodeType;
    }
  
}

