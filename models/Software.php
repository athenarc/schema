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
use yii\db\Query;
use webvimark\modules\UserManagement\models\User;
use yii\data\Pagination;
use yii\httpclient\Client;
use app\models\RunHistory;


/**
 * This is the model class for table "software".
 *
 * @property int $id
 * @property string $name
 * @property string $image
 * @property string $script
 * @property string $version
 * @property string $uploaded_by
 * @property string $visibility
 * @property string $workingdir
 * @property string $imountpoint
 * @property string $description
 * @property string $cwl_path
 * @property bool $has_example
 * @property string $biotools
 * @property string $dois
 * @property string $omountpoint
 */
class Software extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'software';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['script', 'visibility', 'description', 'dois', 'instructions'], 'string'],
            [['has_example'], 'boolean'],
            [['name'], 'string', 'max' => 30],
            [['image', 'workingdir', 'imountpoint', 'omountpoint'], 'string', 'max' => 200],
            [['version'], 'string', 'max' => 80],
            [['uploaded_by', 'biotools'], 'string', 'max' => 255],
            [['cwl_path'], 'string', 'max' => 150],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'image' => 'Image',
            'script' => 'Script',
            'version' => 'Version',
            'uploaded_by' => 'Uploaded By',
            'visibility' => 'Visibility',
            'workingdir' => 'Workingdir',
            'imountpoint' => 'Imountpoint',
            'description' => 'Description',
            'cwl_path' => 'Cwl Path',
            'has_example' => 'Has Example',
            'biotools' => 'Biotools',
            'dois' => 'Dois',
            'omountpoint' => 'Omountpoint',
        ];
    }

    public static function getSoftwareNames($softUser)
    {
        $query=new Query;

        $query->select('name,version,description,image,uploaded_by,visibility,profiled')
              ->from('software')
              ->where(['mpi'=>false])
              ->orderBY(['name'=>SORT_ASC, 'uploaded_by'=>SORT_ASC, 'version' =>SORT_DESC]);

        if ($softUser!='admin')
        {
            $query->where(['visibility'=>'public'])
                  ->orWhere(['and',['visibility'=>'private','uploaded_by'=>$softUser]]);
        }
        // echo $query->createCommand()->getRawSql();
        // exit(0);

        $rows=$query->all();
        $results=[];
        foreach ($rows as $row)
        {
            $name=$row['name'];
            $version=$row['version'];
            $description=$row['description'];
            $image=$row['image'];
            $uploader=$row['uploaded_by'];
            $visibility=$row['visibility'];
            $profiled=$row['profiled'];
            
            if (!isset($results[$name]))
            {
                $results[$name]=[$uploader => [$version . '|' . $visibility =>$version]];
            }
            else
            {
                if (isset($results[$name][$uploader]))
                {
                    $results[$name][$uploader][$version . '|' . $visibility]=$version;

                }
                else
                {
                    $results[$name][$uploader]=[$version . '|' . $visibility=>$version];
                }
                    
            }
            // $results[$name][$uploader][$version[0]]=$visibility;
        }
        
        // print_r($results);
        // exit(0);

        
        return $results;

        // return $rows;
    }

    

    public static function isProfiled()
    {
        $query=new Query;

        $query->select(['name', 'version','profiled'])
              ->from('software')
              ->where(['mpi'=>false]);

        $results=$query->all();

        $profiled=[];

        foreach($results as $res)
        {
            if($res['profiled'])
            {
                 $profiled[$res['name']][$res['version']]=1;
            }
            else
            {
                $profiled[$res['name']][$res['version']]=-1;
            }
                       
        }
        

        return $profiled;

    }

    public static function getOriginalImages($softUser)
    {
        $query=new Query;
        $query->select(['name','version','original_image','docker_or_local'])
              ->from('software')
              ->orderBY(['name'=>SORT_ASC, 'uploaded_by'=>SORT_ASC, 'version' =>SORT_DESC]);
        if ($softUser!='admin')
        {
            $query->where(['visibility'=>'public'])
                  ->orWhere(['and',['visibility'=>'private','uploaded_by'=>$softUser]]);
        }

        $results=$query->all();
        $images=[];

        foreach ($results as $res)
        {
            $name=$res['name'];
            $version=$res['version'];
            $image=$res['original_image'];
            $dockerhub=$res['docker_or_local'];

            if (!isset($images[$name]))
            {
                $images[$name]=[];
            }
            if (!isset($images[$version]))
            {
                $images[$name][$version]=[];
            }
            $images[$name][$version]=[$image,$dockerhub];
        }
        return $images;
    }

    public static function getOriginalImagesUploadDropdown($softUser)
    {
        $query=new Query;
        $query->select(['name','version','image','original_image','docker_or_local','workingdir'])
              ->where(['mpi'=>false])
              ->from('software')
              ->orderBY(['name'=>SORT_ASC, 'uploaded_by'=>SORT_ASC, 'version' =>SORT_DESC]);
        if ($softUser!='admin')
        {
            $query->where(['visibility'=>'public'])
                  ->orWhere(['and',['visibility'=>'private','uploaded_by'=>$softUser]]);
        }

        $results=$query->all();
        $images=[];

        foreach ($results as $res)
        {
            $name=$res['name'];
            $version=$res['version'];
            $original_image=$res['original_image'];
            $dockerhub=$res['docker_or_local'];
            $image=$res['image'];
            $workingdir=$res['workingdir'];

            if ($dockerhub)
            {
                $prefix='dockerHub: ';
            }
            else
            {
                $prefix='localImage: ';
            }

            if (!isset($images[$image]))
            {
                $images[$image]=$name . " v.$version, " . $prefix . $original_image ;
            }
        }
        return $images;
    }

    public static function getSoftwareDescriptions($softUser)
    {
        $query=new Query;

        $query->select('name,version,description')
              ->from('software')
              ->orderBY(['name'=>SORT_ASC, 'uploaded_by'=>SORT_ASC, 'version' =>SORT_DESC]);
        if ($softUser!='admin')
        {
            $query->where(['visibility'=>'public'])
                  ->orWhere(['and',['visibility'=>'private','uploaded_by'=>$softUser]]);
        }
        // echo $query->createCommand()->getRawSql();
        // exit(0);

        $rows=$query->all();
        $results=[];
        foreach ($rows as $row)
        {
            $name=$row['name'];
            $version=$row['version'];
            $description=$row['description'];
            $results[]=['name'=>$name, 'version'=>$version, 'description'=>$description];
            // $results[$name][$uploader][$version[0]]=$visibility;
        }
        
        // print_r($results);
        // exit(0);

        
        return $results;

        // return $rows;
    }

    public static function getAvailableSoftware()
    {
        $query=new Query;

        $query->select('id,name,version,mpi')
              ->where(['mpi'=>false])
              ->from('software');
        // echo $query->createCommand()->getRawSql();
        // exit(0);

        $rows=$query->all();
        $results=[];
        
        foreach ($rows as $row)
        {
            $name=$row['name'];
            $version=$row['version'];
            $mpi=$row['mpi'];
            
            $key=$row['id'];
            $value=$name . '-' . $version;

            $results[$key]=[$value,$mpi];
        }
        
        return $results;
        // return $rows;
    }

    public static function getIndicators($softUser)
    {
        $query=new Query;

        $query->select(['name','version','mpi','covid19'])
              ->from('software')
              ->orderBY(['name'=>SORT_ASC,'version' =>SORT_DESC]);
        if ($softUser!='admin')
        {
            $query->where(['visibility'=>'public'])
                  ->orWhere(['and',['visibility'=>'private','uploaded_by'=>$softUser]]);
        }
        // echo $query->createCommand()->getRawSql();
        // exit(0);

        $rows=$query->all();
        $results=[];

        foreach ($rows as $row)
        {
            $name=$row['name'];
            $version=$row['version'];
            $mpi=$row['mpi'];
            $covid19=$row['covid19'];

            if (!isset($results[$name]))
            {
                $results[$name]=[];
            }
            if (!isset($results[$name][$version]))
            {
                $results[$name][$version]=[];
            }

            if ($mpi)
            {
                $results[$name][$version]['mpi']=true;
            }
            if ($covid19)
            {
                $results[$name][$version]['covid19']=true;
            }
        }
        // print_r($results);
        // exit(0);
        return $results;
    }

    /*
     * Get the fields needed for the edit software form 
     */

    public static function getSoftwareEditFields($name, $version)
    {
        $query=new Query;

        $query->select(['workingdir','description','visibility','mountpoint','cwl_path','biotools','dois'])
              ->from('software ')
              ->where(['name'=>$name, 'version'=>$version]);
        $rows=$query->one();
        
        
        
        return $rows;
    }

    public static function getSoftwarePreviousCwl($name, $version)
    {
        $query=new Query;

        $query->select(['cwl_path'])
              ->from('software')
              ->where(['name'=>$name, 'version'=>$version]);
        $row=$query->one();
        
        
        
        return $row['cwl_path'];
    }
    

    /*
     * Get the container mountpoint for the actions run software and re-run software 
     */
   


    public static function getContainerMountpoint($name, $version)
    {
        $query=new Query;

        $query->select(['imountpoint','omountpoint'])
              ->from('software ')
              ->where(['name'=>$name, 'version'=>$version]);
        $rows=$query->one();
        
        $imount=$rows['imountpoint'];
        $omount=$rows['omountpoint'];

        $iomount='';
        
        if ((!empty($imount)) && (!empty($omount)))
        {
            if ($imount==$omount)
            {
                $iomount=$imount;
            }
        }

        // print_r($iomount);
        // print_r("<br />");
        // print_r($imount);
        // print_r("<br />");
        // print_r($omount);
        // print_r("<br />");
        // exit(0);
        
        return [$imount, $omount, $iomount];
    }


    public static function getUserHistory($softUser)
    {
        $query=new Query;

        $query->select(['start','stop','command','status','softname', 'softversion','jobid', 'ram', 'cpu', 'machinetype', 'project','software_id'])
              ->from('run_history')
              ->where(['username'=>$softUser]);

        $pages = new Pagination(['totalCount' => $query->count()]);
        $pages->setPageSize(10);
        
        $results = $query->orderBy('start DESC')->offset($pages->offset)->limit($pages->limit)->all();

        return [$pages,$results];
    }

    

    /*
     * If the user has uploaded a CWL file, 
     * then get the custom form fields.
     */
    public static function getSoftwareFields($name,$version)
    {
        $query=new Query;

        $query->select('si.name, si.position, si.field_type, si.prefix, si.default_value, si.optional, si.separate si.example') 
              ->from('software sf')
              ->join('INNER JOIN', 'software_inputs si', 'si.softwareid=sf.id')
              ->where(['sf.name'=>$name, 'sf.version'=>$version])
              ->orderBY(['si.position'=>SORT_ASC]);
        $rows=$query->all();

        return $rows;
    }
    /*
     * If the user has uploaded a CWL file, 
     * then get the docker image script name.
     */
    public static function getScript($name,$version)
    {
        $query=new Query;

        $query->select('script, imountpoint,omountpoint')
              ->from('software')
              ->where(['name'=>$name, 'version'=>$version]);
        $path=$query->one();

        $imount=$path['imountpoint'];
        $omount=$path['omountpoint'];
        $iomount='';
        if ((!empty($imount)) || (empty($omount)))
        {
            if ($imount==$omount)
            {
                $iomount=$imount;
            }
        }

        return [$path['script'],$imount,$omount,$iomount];
    }


    /*
     * Check if the commands posted are empty.
     */
    public static function createCommand($script,$emptyFields,$fields,$mountpoint)
    {   

        $errors=[];
        /**
         * Return one command with the mountpoint attached and one without it
         */
        $command=$script;

        if ( (!$emptyFields))
        {
            // print_r(3);
            foreach ($fields as $field)
            {
                if ($field->field_type=='boolean')
                {
                    if ($field->value!="0")
                    {
                        $command.= ' ' . $field->prefix;
                    }
                    
                }
                else
                {
                    /*
                     * if the field is not of array type
                     */
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
                            if (empty($field->value) && ($field->field_type!='Directory'))
                            {
                                $errors[]="Field $field->name cannon be empty.";
                            }
                            else
                            {
                                if (($field->field_type=='File') || ($field->field_type=='Directory'))
                                {
                                    $command.= ' ' . $field->prefix . $field_gap . $mountpoint . '/' . $field->value;
                                }
                                else
                                {
                                    $command.= ' ' . $field->prefix . $field_gap . $field->value;
                                }
                            }

                        }
                        else
                        {
                            if (!empty($field->value))
                            {
                                if (($field->field_type=='File') || ($field->field_type=='Directory'))
                                {
                                    $command.= ' ' . $field->prefix . $field_gap . $mountpoint . '/' . $field->value;
                                }
                                else
                                {
                                    $command.= ' ' . $field->prefix . $field_gap . $field->value;
                                }
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
                            if (empty($field->value) && ($field->field_type!='Directory'))
                            {
                                $errors[]="Field $field->name cannon be empty.";
                            }
                            else
                            {

                                $tmpArray=explode(';',$field->value);
                                
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
                                            $finalValue.= ' ' . $field->prefix . $field_gap . $mountpoint . '/' . $val;
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
                                            $finalValue.= $mountpoint . '/' . $val . $separator;
                                        }
                                        else
                                        {
                                            $finalValue.= ' ' . $val . $separator;
                                        }
                                    }
                                    $finalValue=trim($finalValue, $separator);
                                }
                                
                                $command.= ' '. $finalValue;
                            }

                        }
                        /* 
                         * Array field is not optional
                         */
                        else
                        {
                            $tmpArray=explode(';',$field->value);
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
                                        $finalValue.= ' ' . $field->prefix . $field_gap . $mountpoint . '/' . $field->value;
                                    }
                                    else
                                    {
                                        $finalValue.= ' ' . $field->prefix . $field_gap . $field->value;
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
                                        $finalValue.= $mountpoint . '/' . $field->value . $separator;
                                    }
                                    else
                                    {
                                        $finalValue.= ' ' . $field->value . $separator;
                                    }
                                }
                                $finalValue=trim($finalValue, $separator);
                            }
                            
                            $command.=$finalValue;
                        }

                    }
                }
            }
        }
        // print_r($command);
        // exit(0);
        return [$errors, $command];
    }

    /*
     * This function creates the job YAML file and sends it to Kubernetes
     */
    public static function createAndRunJob($commands, $fields,
                                    $name, $version, $jobid, $user, 
                                    $podid, $machineType, 
                                    $isystemMount, $isystemMountField,
                                    $osystemMount, $osystemMountField,
                                    $iosystemMount, $iosystemMountField, 
                                    $project,$maxMem,$maxCores,$sharedFolder,$gpu)
    {
        
        /*
         * Scheduler (python) script physical location
         */
        $scheduler=self::sudoWrap(Yii::$app->params['scriptsFolder'] . "scheduler.py");
        $stats=self::sudoWrap(Yii::$app->params['scriptsFolder'] . "jobMonitor.py");
        
        /* 
         * Get image repository location from the DB
         */
        $software=Software::find()->where(['name'=>$name, 'version'=>$version])->one();
        $image=$software->image;
        $workingdir=$software->workingdir;
        $imountpoint=$software->imountpoint;
        $omountpoint=$software->omountpoint;
        $softwareId=$software->id;
        
        $iomountpoint='';
        if ((!empty($imountpoint)) || (empty($omountpoint)))
        {
            if ($imountpoint==$omountpoint)
            {
                $iomountpoint=$imountpoint;
            }
        }
        // $nameNoQuotes=$name;
        $softName=$name;
        $nameNoQuotes=str_replace('_','-',$name);
        $versionNoQuotes=$version;
        $name=self::enclose($name);
        $version=self::enclose($version);
        $iomountpoint=self::enclose($iomountpoint);
        $imountpoint=self::enclose($imountpoint);
        $omountpoint=self::enclose($omountpoint);
        $iosystemMount=self::enclose($iosystemMount);
        $isystemMount=self::enclose($isystemMount);
        $osystemMount=self::enclose($osystemMount);
        $workingdir=self::enclose($workingdir);
        $sharedFolder=self::enclose($sharedFolder);
        $gpu=self::enclose($gpu);

        /*
         * Create the tmp folder to store the YAML file
         */
        $folder=Yii::$app->params['tmpFolderPath'] . "$jobid/";

        if (file_exists($folder))
        {
            Software::exec_log("rm -r $folder", $out, $ret);
            Software::exec_log("mkdir $folder", $out, $ret);
        }
        else
        {
            Software::exec_log("mkdir $folder", $out, $ret);
        }
        
        /*
         * Store inputs in a file in the tmp directory
         */
        $fieldValues=[];
        foreach($fields as $field)
        {
                $fieldValues[$field->name]=$field->value;
            
        }

        $fieldValues=json_encode($fieldValues,JSON_UNESCAPED_SLASHES);

        $machineType=SoftwareProfiler::getMachineType($fields,$software,$folder,$isystemMount,$iosystemMount,$maxMem);

        $filename=$folder . 'fields.txt';
        file_put_contents($filename, $fieldValues);

        /*
         * Store the commands in a file in the tmp directory
         */
        $filename=$folder . 'commands.txt';

        file_put_contents($filename, $commands);



        Software::exec_log("chmod 777 $folder -R",$out,$ret);


        /*
         * Classify software, create YAML job configuration file,
         * send the file to Kubernetes to run and get the machine type.
         */
        $arguments=[
            $name,$version,$image,
            $jobid,$folder, $workingdir, 
            $imountpoint, $isystemMount,
            $omountpoint, $osystemMount,
            $iomountpoint, $iosystemMount,
            $maxMem,$maxCores*1000, Yii::$app->params['nfsIp'],
            $machineType,$sharedFolder,$gpu];

        $schedulerCommand=$scheduler . ' ' . implode(' ',$arguments) . " 2>&1";

        // print_r($schedulerCommand);
        // exit(0);
        Software::exec_log($schedulerCommand,$output,$ret);

        // print_r($output);
        // exit(0);
        $jobName=strtolower($nameNoQuotes) . '-' . $jobid;
        /*
         * This replacements are performed in configFileCreator.py too.
         * Anything changed here should be changed there.
         * Job cancelling is also affected.
         */
        $jobName=str_replace('_','-',$jobName);
        $jobName=str_replace(' ','-',$jobName);
        $jobName=str_replace("\t",'-',$jobName);
        $arguments=[$jobName, $jobid, $folder];
        $statsCommand=$stats . ' ' . implode(' ', $arguments);
        $monitorLog=$folder . 'monitorLog.txt';
        /*
         * Uncomment the following line and comment the other one to debug the monitor
         */
        // shell_exec(sprintf("%s > $monitorLog 2>&1 &", $statsCommand));
        shell_exec(sprintf("%s > /dev/null 2>&1 &", $statsCommand));

        
        /*
         * Read the output and check if the pod was created.
         * If not, add error message.
         */
        if (!preg_match("/job\.batch\/[a-z0-9:space:]+/",$output[0]))
        {   
            $error="";
            foreach ($output as $out)
            {
                $error.=$out . "<br />";
            }
            $error.="Please contact the system administrator.";
            return [$podid, $error, $machineType];  
        }

        /*
         * Get the pod ID by using the job name.
         */
        

        $query=Yii::$app->db->createCommand()->insert('run_history',
                [

                    "username"=>User::getCurrentUser()['username'],
                    "jobid" => $jobid,
                    "command" => $commands,
                    "imountpoint" => $isystemMountField,
                    "omountpoint" => $osystemMountField,
                    "iomountpoint" => $iosystemMountField,
                    "start" => 'NOW()',
                    "softname" => $softName,
                    "softversion"=> $versionNoQuotes,
                    "machinetype"=> $machineType,
                    "project"=>$project,
                    "max_ram"=> $maxMem,
                    "max_cpu" => $maxCores,
                    'software_id' => $softwareId,
                    'type'=>'job',

                ]
            )->execute();

        // print_r($statsCommand);
        // exit(0);

        unset($output);

        
        if (isset(Yii::$app->params['namespaces']['jobs']))
        {
            $namespace=Yii::$app->params['namespaces']['jobs'];
            $command=self::sudoWrap("kubectl get pods --no-headers -n $namespace 2>&1") . " | grep $jobName | tr -s ' ' ";

            Software::exec_log($command,$output,$ret);
        }
        else
        {
            $command=self::sudoWrap("kubectl get pods --no-headers 2>&1") . " | grep $jobName | tr -s ' ' ";
            Software::exec_log($command,$output,$ret);
        }
        

        $podString='';
        foreach ($output as $out)
        {   
            // print_r($out);
            // print_r("<br />");
            if (strpos($out,$jobName)!== false)
            {
                $podString=explode(' ', $out)[0];
                break;
            }

        }

        if (empty($podString))
        {
            $error="There was an error submitting the job to Kubernetes.<br />Please reload the page and try again or contact an administrator.";
            return [$podid, $error, $machineType];
        }
        return [$podString,'', $machineType];

    }

    /*
     * Get pod logs by using the pod ID
     */
    public static function getLogs($podid)
    {
        if (isset(Yii::$app->params['namespaces']['jobs']))
        {
            $namespace=Yii::$app->params['namespaces']['jobs'];
            $logsCommand=self::sudoWrap("kubectl logs $podid -n $namespace 2>&1");
            $command=self::sudoWrap("kubectl get pods --no-headers $podid -n $namespace 2>&1");   
        }
        else
        {
            $logsCommand=self::sudoWrap("kubectl logs $podid 2>&1");
            $command=self::sudoWrap("kubectl get pods --no-headers $podid 2>&1");
        }
        Software::exec_log($logsCommand,$logs,$ret);

        Software::exec_log($command,$output,$ret);
        $splt=preg_split('/[\s]+/', $output[0]);
        $status=$splt[2];
        $time=$splt[4];
        if ($status=='server')
        {
            $status='Completed';
            $time='N/A';
        }
        

        return [$status,$logs,$time];
    }

    /*
     * Erase job after it is completed or terminated.
     */
    public static function cleanUp($name,$jobid,$status)
    {
        $folder=$userFolder=Yii::$app->params['tmpFolderPath'] . "/$jobid/";

        #Clear job
        $jobName=strtolower($name). '-' . $jobid;
        $jobName=str_replace('_','-',$jobName);
        $jobName=str_replace(' ','-',$jobName);
        $jobName=str_replace("\t",'-',$jobName);
        $yaml=$folder . $jobName . '.yaml';

        if ($status=='Completed')
        {       
        
            $command=self::sudoWrap("kubectl describe job $jobName 2>&1");
            Software::exec_log($command, $output, $ret);
            // print_r($output);
            // exit(0);
            $start='';
            $stop='';
            foreach ($output as $line)
            {
                $startStr="Start Time:";
                $startlen=strlen($startStr);
                $stopStr="Completed At:";
                $stoplen=strlen($stopStr);
                // $cancelStr="Canceled At:";
                // $cancellen=strlen($cancelStr);
        
                if (substr($line,0,$startlen)==$startStr)
                {
                    $tokens=explode(',', $line)[1];
                    $tokens=explode('+', $tokens)[0];
                    $datetime=strtotime($tokens);
                    $start=date("Y-m-d H:i:s", $datetime);
                }
        
                if (substr($line,0,$stoplen)==$stopStr)
                {
                    $tokens=explode(',', $line)[1];
                    $tokens=explode('+', $tokens)[0];
                    $datetime=strtotime($tokens);
                    $stop=date("Y-m-d H:i:s", $datetime);
                }
        
                    // if (substr($line,0,$cancellen)==$cancelStr)
                    // {
                    //     $tokens=explode(',', $line)[1];
                    //     $tokens=explode('+', $tokens)[0];
                    //     $datetime=strtotime($tokens);
                    //     $stop=date("Y-m-d H:i:s", $datetime);
                    // }
        
            }
            Yii::$app->db->createCommand()->update('run_history',
                [
                    'start' => $start,
                    'stop' => $stop,
                    'status' => $status,
                ],
                "jobid='$jobid'"
            )->execute();
                // print_r($start);
                // print_r("<br />");
                // print_r($stop);
                // exit(0);
        }
        else
        {
            Yii::$app->db->createCommand()->update('run_history',
                [
                    'stop' => 'NOW()',
                    'status' => $status,
                ],
                "jobid='$jobid'"
            )->execute();
        }

        
                // print_r( Yii::$app->db->createCommand()->update('run_history',
                //     [
                //         'start' => $start,
                //         'stop' => $stop,
                //         'status' => $status,
                //     ],
                //     "softname='$name' AND username='$user' AND jobid='$jobid'"
                // )->getRawSql());

        $podid=self::runningPodIdByJob($name,$jobid);
        $command=self::sudoWrap("kubectl logs $podid 2>&1");
        Software::exec_log($command,$logs,$ret);
        file_put_contents($folder . 'logs.txt', implode("\n",$logs));

        $command=self::sudoWrap("kubectl delete -f $yaml");
        Software::exec_log($command,$out,$ret);

    }

    /*
     * Check is podid is running
     */
    public static function isAlreadyRunning($podid)
    {
        if ($podid=='')
        {
            return false;
        }

        /*
         * The following could have been implemented by using the "--no-headers"
         * flag and checking whether the output is empty instead of count($output)==1.
         */
        $command=self::sudoWrap("kubectl get pods $podid 2>&1");
        Software::exec_log($command,$output,$ret);
        if (count($output)==1)
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    /*
     * Use name and job ID to get the pod ID.
     */
    public static function runningPodIdByJob($name,$jobid)
    {
        $podid='';
        if (isset(Yii::$app->params['namespaces']['jobs']))
        {
            $namespace=Yii::$app->params['namespaces']['jobs'];
            $command=self::sudoWrap("kubectl get pods -n $namespace --no-headers 2>&1");
        }
        else
        {
            $command=self::sudoWrap("kubectl get pods --no-headers 2>&1");
        }
       

        Software::exec_log($command,$out,$ret);

        foreach ($out as $row)
        {
            $podTokens=preg_split('/[\s]+/', $row);

            $podString=$podTokens[0];
            $podStatus=$podTokens[2];
            $tokens=explode('-',$podString);
            if ((strtolower($name)==$tokens[0]) && ($jobid==$tokens[1]))
            {
                $podid=$podString;
                break;
            }
        }
        if ($podStatus=='Terminating')
        {
            $podid='';
        }
        return $podid;

    }

    public static function listDirectories($directory)
    {
        // $files = array_filter(scandir($directory),'is_dir');
        $files = scandir($directory);
        $results=[];
        // print_r($files);
        // exit(0);
        
        foreach($files as $key => $value)
        {
            $path = realpath($directory.DIRECTORY_SEPARATOR.$value);
            if( is_dir($path) && $value != "." && $value != ".." && $value[0]!='.') 
            {
                $result=self::listDirectories($path);
                $results[$path] = $result;

            }
        }

        return $results;

    }

    public static function softwareRemove($name,$version)
    {
        
        $encname=self::enclose($name);
        $encversion=self::enclose($version);

        $command=self::sudoWrap(Yii::$app->params['scriptsFolder'] . "imageRemover.py $encname $encversion 2>&1");

        $success='';
        $error='';
        
        // print_r($command);
        // print_r("<br /><br />");
        // exit(0);
        session_write_close();
        Software::exec_log($command,$out,$ret);
        session_start();
        // print_r($out);
        // print_r($ret);
        // exit(0);
        if ($ret==0)
        {
            $success="Successfully deleted image $name v.$version!";
        }
        else
        {
            $error="Error code $ret.";
            foreach ($out as $line)
            {
                $error.=$line . "<br />";
            }
            $error.="<br />Please contact an administrator.";
        }

        return [$success,$error];
    }

    public static function getRerunData($jobid)
    {
        $query=new Query;
        $result=$query->select(['command','imountpoint', 'omountpoint', 'iomountpoint', 'softname', 'softversion', 'machinetype','project','max_ram','max_cpu'])
              ->from('run_history')
              ->where(['jobid'=>$jobid])
              ->one();
        return $result;

    }

    public static function getRerunFieldValues($jobid,$fields)
    {
        $folder=Yii::$app->params['tmpFolderPath'] . '/' . $jobid . '/';

        $file=$folder . 'fields.txt';

        if (file_exists($file))
        {  
            $content=file_get_contents($file);
        }
        else
        {
            return $fields;
        }
        // print_r($file);
        $json=json_decode($content,true);

        if (empty($json))
        {
            $field_values=explode("\n",$content);

            /*
             * Modify $fields and return it
             */
            if (!empty($fields))
            {
                $fieldCount=count($fields);
            }
            else
            {
                $fieldCount=0;
            }

            for ($i=0; $i<$fieldCount; $i++)
            {
                if ($fields[$i]->field_type=='boolean')
                {
                    $fields[$i]->value=(trim($field_values[$i])=='true')? true : false;
                }
                else
                {
                    $fields[$i]->value=trim($field_values[$i]);
                }
            }
            // print_r($fields[--$i]->value?'true':'false');
            // exit(0);
        
        }
        else
        {
            foreach ($fields as $field)
            {
                if (!array_key_exists($field->name,$json))
                {
                    return false;
                }
                $field->value=$json[$field->name];
            }
        }
        return $fields;
            
    }

    public static function getJobDetails($jobid)
    {
        $query=new Query;
        $result=$query->select(['command','imountpoint','omountpoint','iomountpoint', 'ram','cpu','start', 'stop', 'machinetype','status','softname','softversion'])
              ->from('run_history')
              ->where(['jobid'=>$jobid])
              ->one();
        return $result;

    }

    public static function updateExampleFields($name,$version,$values)
    {

        $query=new Query;
        $result=$query->select(['id'])
              ->from('software')
              ->where(['name'=>$name, 'version'=>$version])
              ->one();

        $softId=$result['id'];
        
        
        $k=1;
        foreach ($values as $value)
        {

            Yii::$app->db->createCommand()->update('software_inputs',['example'=>$value], "softwareid='$softId' AND position=$k")->execute();
            $k++;

        }
        Yii::$app->db->createCommand()->update('software',['has_example'=>true], "id='$softId'")->execute();
        
    }

    public static function hasExample($name,$version)
    {
        $query=new Query;
        $result=$query->select(['has_example'])
              ->from('software')
              ->where(['name'=>$name, 'version'=>$version])
              ->one();

        return $result['has_example'];

    }
    public static function uploadedBy($name,$version)
    {
        $query=new Query;
        $result=$query->select(['uploaded_by'])
              ->from('software')
              ->where(['name'=>$name, 'version'=>$version])
              ->one();

        return $result['uploaded_by'];

    }

    public static function getInactiveJobs()
    {

        $command=self::sudoWrap('kubectl get jobs --no-headers 2>&1');
        
        Software::exec_log($command,$output,$ret);

        // print_r($output);
        // exit(0);
        if (trim($output[0])=="No resources found in default namespace.")
        {
            return [];
        }
        if (trim($output[0])=="No resources found.")
        {
            return [];
        }

        $inactive=[];
        $active=[];
        
        foreach ($output as $line)
        {
            $tokens_tmp=explode(' ',$line);
            $tokens=[];
            foreach ($tokens_tmp as $token)
            {
                if (!empty($token))
                    $tokens[]=$token;
            }
            // print_r($tokens);
            // exit(0);
            $completed_tokens=explode('/',$tokens[1]);
            $completed=intval($completed_tokens[0]);
            $total=intval($completed_tokens[1]);
            // var_dump($total);
            // var_dump($completed);
            // exit(0);
            if ($completed!=$total)
                continue;
            // $status='Completed';
            $job=$tokens[0];
            // if ($status=='Completed')
            // {
            $job=explode('-',$job);
            $inactive[]=$job;
            // }
        

        }
        // print_r($inactive);
        // exit(0);
        return $inactive;


    }


    public static function getActiveProjects()
    {
        $username=User::getCurrentUser()['username']; 
        // print_r("https://egci-beta.imsi.athenarc.gr/index.php?r=api/active-ondemand-quotas&username=$username");
        // exit(0);  
        $client = new Client();
        $response = $client->createRequest()
                ->setMethod('GET')
                ->setUrl(Yii::$app->params['egciActiveQuotas'] . "&username=$username")
                ->send();

        $projects=$response->data;
        // print_r($projects);
        // exit(0);
        
        $projectNames=[];
        $projectJobs=[];
        $dropdown=[];
        foreach($projects as $project) 
        {
            $projectNames[]=$project['name'];
            $dropdown[$project['name']]=$project['name'];
            $projectJobs[$project['name']]=$project['num_of_jobs'];
        }
        $query=new Query;
        $projectUsage=$query->select(['COUNT(*) as cnt','project'])
                            ->from('run_history')
                            ->where(['IN','project',$projectNames,])
                            ->andFilterWhere(
                            [
                                'or',
                                ['status'=>'Complete'],
                                [
                                    'and',
                                    ['status'=>'Cancelled'],
                                    "stop-start>='00:00:60'"
                                ]
                            ])
                            ->groupBy('project') //->createCommand()->getRawSql();
                            ->all();
        // print_r($projectUsage);
        // exit(0);
        $projectsDB=[];
        foreach($projectUsage as $project) 
        {
            $remaining=$projectJobs[$project['project']]-$project['cnt'];
            $projectsDB[$project['project']]=1;
            $dropdown[$project['project']].=" ($remaining remaining jobs)";
        }

        foreach ($projectNames as $project)
        {
            if (!isset($projectsDB[$project]))
            {
                $dropdown[$project].=" ($projectJobs[$project] remaining jobs)";
            }
        }

        return $dropdown;

                

    }

    


     public static function getUserStatistics($softUser)
    {
        $query=new Query;

        $result=$query->select(['DATE_TRUNC(\'second\',SUM(stop-start)) AS total_time', 'AVG(ram) AS ram', 'AVG(cpu) AS cpu'])
              ->from('run_history')
              ->where(['username'=>$softUser])
              ->all();
        
        $totaljobs=$query->count();
        $totalcompletedjobs=$query->andFilterWhere([
                'or',
                ['status'=>'Complete'],
                [
                    'and',
                    ['status'=>'Cancelled'],
                    "stop-start>='00:00:60'"
                ]
            ])->count();

        $results=[$totaljobs,$totalcompletedjobs,$result[0]['total_time'], $result[0]['ram'], $result[0]['cpu']];

        // print_r($results);
        // exit(0);
     

        return $results;

        

    }

    public static function getUserStatisticsPerProject($softUser)
    {
        $query=new Query;

        $result=$query->select(['COUNT(*) as count', 'DATE_TRUNC(\'second\',SUM(stop-start)) AS total_time', 'AVG(ram) AS ram', 'AVG(cpu) AS cpu', 'project'])
              ->from('run_history')
              ->where(['username'=>$softUser])
              ->andFilterWhere(
                [
                    'or',
                    ['status'=>'Complete'],
                    [
                        'and',
                        ['status'=>'Cancelled'],
                        "stop-start>='00:00:60'"
                    ]
                ])
              ->groupBy('project')
              // print_r($query->createCommand()->getRawSql());
              // exit(0);
              ->all(); 
        
        return $result;
    }


    public static function getActiveProjectQuotas($username)
    {
         
        // print_r("https://egci-beta.imsi.athenarc.gr/index.php?r=api/active-ondemand-quotas&username=$username");
        // exit(0);
        $client = new Client();
        $response = $client->createRequest()
                ->setMethod('GET')
                ->setUrl(Yii::$app->params['egciActiveQuotas'] ."&username=$username")
                ->send();

        $data=$response->data;
        
        $results=[];
        foreach($data as $row) 
        {
            $results[$row['name']]=[
                'num_of_jobs'=>$row['num_of_jobs'],
                'ram'=>$row['ram'],
                'cores'=>$row['cores'],
                'time_per_job'=>$row['time_per_job'],
            ];
        }

        return $results;

                

    }

    public static function getOndemandProjectQuotas($username,$project)
    {
        if (Yii::$app->params['standalone']==false)
        {
            $project=str_replace(' ','%20',$project);
            $url=Yii::$app->params['egciSingleProjecteQuotas'] . "&username=$username&project=$project";
            
            $client = new Client();
            $response = $client->createRequest()
                    ->setMethod('GET')
                    ->setUrl($url)
                    ->send();

            
            $data=$response->data;
        
        }
        /*
         * SCHeMa runs as standalone without 
         * the project management interface
         */
        else
        {
            $data=[
                [
                    'num_of_jobs'=>0,
                    'time_per_job'=>-1,
                    'ram' => Yii::$app->params['standaloneResources']['maxRam'],
                    'cores' => Yii::$app->params['standaloneResources']['maxCores'],
                ]
            ];
        }
        return $data;

                

    }

    public static function listFiles($directory)
    {
        // $files = array_filter(scandir($directory),'is_dir');
        $files = scandir($directory);
        $results=[];
        // print_r($files);
        // print_r("<br /><br />");
        // exit(0);
        $i=0;
        foreach($files as $key => $value)
        {
            $path = realpath($directory . DIRECTORY_SEPARATOR . $value);
            // print_r($directory . DIRECTORY_SEPARATOR . $value);
            // print_r("<br />");
            if ($value != "." && $value != ".." && $value[0]!='.')
            {
                if (is_dir($path))
                {
                    $result=self::listFiles($path);
                    $results[$path]= $result;
                    // print_r($results);
                }
                else
                {
                    $results['file_'.$i]=$path;
                    $i++;
                }
                
            }

        }

        return $results;

    }

    public static function enclose($string)
    {
        return "'" . $string . "'";
    }

    public static function sudoWrap($command)
    {
        if (file_exists('/data/containerized'))
        {
            return $command;
        }
        else
        {
            return "sudo -u ". Yii::$app->params['systemUser'] . " " . $command;
        }
    }

    public static function exec_log($command, array &$out=null, int &$ret=null)
    {
        exec($command,$out,$ret);
        if ($ret != 0) {
            error_log("ERROR (".$ret."): While running '".$command."'");
            error_log(implode(" ", $out));
        }
    }
}
