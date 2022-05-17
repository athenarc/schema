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

    public $container_command=[];
    public $fields=[];
    public $inputs=[];
    public $outputs=[];
    public $limits=[];
    public $project='';
    public $user='';
    public $errors=[];
    public $jobid='';
    public $outFolder='';
    /*
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
        }

        
        return $results;

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
        


        $rows=$query->all();
        $results=[];
        foreach ($rows as $row)
        {
            $name=$row['name'];
            $version=$row['version'];
            $description=$row['description'];
            $results[]=['name'=>$name, 'version'=>$version, 'description'=>$description];
        }
        
        
        return $results;

    }

    public static function getAvailableSoftware()
    {
        $query=new Query;

        $query->select('id,name,version,mpi')
              ->where(['mpi'=>false])
              ->from('software');

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
   
    public static function getIOs($software,$fields,$iSystemFolder,$oSystemFolder)
    {
        $username=explode('@',User::getCurrentUser()['username'])[0];
        $userFolder=Yii::$app->params['userDataPath'] . $username;
        $ofolder=$userFolder . '/' . $oSystemFolder;
        if (!is_dir($ofolder))
        {
            self::exec_log("mkdir -p $ofolder");
            self::exec_log("chmod 777 $ofolder");
        }
        /*
         * We installed schema on a server instead of deploying with helm
         * and our FTP is jailed, with the root being the user-data folder.
         * Change does not affect helm deployments, because the parameter does not exist.
         */
        if (isset(Yii::$app->params['ftpJailPath']) and (!empty(Yii::$app->params['ftpJailPath'])))
        {
            $userFolder=str_replace(Yii::$app->params['ftpJailPath'],'',$userFolder);
        }

        /*
         * Prepare output directory location.
         */
        if (Yii::$app->params['jobFileStore']=='ftp')
        {
            $url= "ftp://" . Yii::$app->params['ftpIp'] . '//' . $userFolder . '/' . $oSystemFolder;
        }
        else if (Yii::$app->params['jobFileStore']=='s3')
        {
            $url= "s3://" . $username . '/' . $oSystemFolder;
        }
        
        if (empty($software->omountpoint))
        {
            $outputs=[];
        }
        else
        {
            $outputs=[['type'=>'DIRECTORY','path'=>$software->omountpoint,'url'=>$url]];
        }
        
        $inputs=[];
        foreach ($fields as $field)
        {   
            if (($field->field_type!='Directory') && ($field->field_type!='File'))
            {
                continue;
            }
            $input=[];
            if ($field->field_type=='Directory')
            {
                if (Yii::$app->params['jobFileStore']=='ftp')
                {
                    $url="ftp://" . Yii::$app->params['ftpIp'] . '//' . $userFolder . '/' . $field->value;
                }
                else if (Yii::$app->params['jobFileStore']=='s3')
                {
                    $url="s3://" . $username . '/' . $field->value;
                }
                $path=$software->imountpoint . '/' . $field->value;
                $type='DIRECTORY';
            }
            else
            {
                if (Yii::$app->params['jobFileStore']=='ftp')
                {
                    $url="ftp://" . Yii::$app->params['ftpIp'] . '//' . $userFolder . '/' . $field->value;
                }
                else if (Yii::$app->params['jobFileStore']=='s3')
                {
                    $url="s3://" . $username . '/' . $field->value;
                }
                
                $filename=explode('/',$field->value);
                $filename=end($filename);
                $path=$software->imountpoint . '/' . $field->value;
                $type='FILE';
            }
            $inputs[]=['type'=>$type,'path'=>$path,'url'=>$url];
        }

        return [$inputs,$outputs];
    }

    /*
     * Check if the commands posted are empty.
     */
    public static function createCommand($software,$emptyFields,$fields)
    {   

        $errors=[];
        /**
         * Return one command with the mountpoint attached and one without it
         */
        $command=$software->script;

        if ( (!$emptyFields))
        {
            
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
                                    $command.= ' ' . $field->prefix . $field_gap . $software->imountpoint . '/' . $field->value;
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
                                    $command.= ' ' . $field->prefix . $field_gap . $software->imountpoint . '/' . $field->value;
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
                                            $finalValue.= ' ' . $field->prefix . $field_gap . $software->imountpoint . '/' . $val;
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
                                            $finalValue.= $software->imountpoint . '/' . $val . $separator;
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
                                        $finalValue.= ' ' . $field->prefix . $field_gap . $software->imountpoint . '/' . $field->value;
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
                                        $finalValue.= $software->imountpoint . '/' . $field->value . $separator;
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
        return [$errors, $command];
    }

    /*
     * This function creates the job YAML file and sends it to Kubernetes
     */
    public function runJob()
    {
        $data=[];
        /*
         * Create job name. Leftover from the old method, but it works.
         */
        $taskName=str_replace('_','-',$this->name);
        $taskName=str_replace(' ','-',$taskName);
        $taskName=str_replace("\t",'-',$taskName);
        $taskName.= '-' . $this->version;

        /*
         * Get container command and eliminate empty strings
         */
        $command_str=$this->container_command;
        $tmp=explode(' ',$this->container_command);
        $this->container_command=[];
        foreach ($tmp as $token)
        {
            if ($token=='')
            {
                continue;
            }
            $this->container_command[]=$token;
        }

        /*
         * Get data to be sent to TES
         */
        $data['name']=$taskName;
        $data['inputs']=$this->inputs;
        $data['outputs']=$this->outputs;

        /* 
         * Get resources
         */
        $resources=['cpu_cores'=>$this->limits['cpu'], 'ram_gb'=>$this->limits['ram'],'disk_gb'=>'30'];
        $data['resources']=$resources;

        /*
         * Create TES executor
         */
        $executor=[];
        $executor['image']=$this->image;
        $executor['command']=$this->container_command;
        $executor['workdir']=empty($this->workingdir) ? $this->omountpoint : $this->workingdir;
        $data['executors']=[$executor];
        // var_dump($data);
        // exit(0);
        $url=self::getTesUrl();
        $client=new Client();
        $response = $client->createRequest()
                    ->setFormat(Client::FORMAT_JSON)
                    ->setData($data)
                    ->setMethod('POST')
                    ->setUrl($url)
                    ->send();
    
        if (!$response->getIsOk())
        {
            $this->errors=['There was an error sending the job to TESK. <br />Please contact an administrator'];
            error_log(sprintf("ERROR while calling %s.",$url));
            error_log("DATA: ".json_encode($data));
            error_log("RESPONSE: ".$response);
            return;
        }

        $this->jobid=$response->data['id'];

        /*
         * Create the tmp folder to store the aux files
         */
        $folder=Yii::$app->params['tmpFolderPath'] . "$this->jobid/";

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
        foreach($this->fields as $field)
        {
                $fieldValues[$field->name]=$field->value;
            
        }

        $fieldValues=json_encode($fieldValues,JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);

        $filename=$folder . 'fields.json';
        file_put_contents($filename, $fieldValues);

        /*
         * Store the json call in a file in the tmp directory
         */
        $filename=$folder . 'tesCall.json';
        $tesCall=json_encode($data,JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
        file_put_contents($filename, $tesCall);



        Software::exec_log("chmod 777 $folder -R",$out,$ret);

        /*
         * Insert to DB
         */
        $history=new RunHistory();
        $history->username=User::getCurrentUser()['username'];
        $history->jobid=$this->jobid;
        $history->command=$command_str;
        $history->omountpoint=$this->outFolder;
        $history->start='NOW()';
        $history->softname=$this->name;
        $history->softversion=$this->version;
        $history->project=$this->project;
        $history->max_ram=$this->limits['cpu'];
        $history->max_cpu=$this->limits['ram'];
        $history->software_id=$this->id;
        $history->type='job';
        $history->save(false);

        /*
         * Run the monitor script in the background
         */
        $monitor=self::sudoWrap(Yii::$app->params['scriptsFolder'] . "jobMonitor.py");
        $jobid=self::enclose($this->jobid);
        $enfolder=self::enclose($folder);
        $tesEndpoint=self::enclose(Yii::$app->params['teskEndpoint']);
        $schemaTes=(isset(Yii::$app->params['schemaTes']) && (!empty(Yii::$app->params['schemaTes'])))?"'1'":"'0'";
        $arguments=[$jobid, $folder,$tesEndpoint,$schemaTes];
        $monitorCommand=$monitor . ' ' . implode(' ', $arguments);
        $monitorLog=$folder . 'monitorLog.txt';
        /*
         * Uncomment the following line and comment the other one to debug the monitor
         */
        shell_exec(sprintf("%s > $monitorLog 2>&1 &", $monitorCommand));
        // shell_exec(sprintf("%s > /dev/null 2>&1 &", $monitorCommand));

        
        return;

    }

    /*
     * Get pod logs by using the pod ID
     */
    public static function getLogs($jobid)
    {
        $url=self::getTesUrl();
        if (isset(Yii::$app->params['schemaTes']) && (!empty(Yii::$app->params['schemaTes'])))
        {
            $urlArray=[$url, 'jobid'=>$jobid, 'view'=>'FULL'];
        }
        else
        {
            $urlArray=[$url . "/$jobid", 'view'=>'FULL'];
        }
        $client=new Client();
        $response = $client->createRequest()
                    ->setFormat(Client::FORMAT_JSON)
                    ->setMethod('GET')
                    ->setUrl($urlArray)
                    ->send();

        if (!$response->getIsOk())
        {
            return['COMPLETE',['Job logs unavailable. Please visit the "Job History" page to download the logs.'],"N/A"];
        }

        $status=$response->data['state'];
        try
        {
            $logs=$response->data['logs'][0]['logs'][0]['stdout'];
        }
        catch (\Exception $e)
        {
            $logs="";
        }
        
        $logs=explode("\n",$logs);
        try
        {
            $start=$response->data['logs'][0]['logs'][0]['start_time'];
            $start=str_replace('T',' ',$start);
            $start=str_replace('Z',' ',$start);
            $startdate=new \DateTime($start);
            $enddate=new \DateTime('NOW');
            $diff=$enddate->diff($startdate);
            
            $timestr=$diff->s . "s";
            if ($diff->i>0)
            {
                $timestr=$diff->i . 'm ' . $timestr;
            }
            if ($diff->h>0)
            {
                $timestr=$diff->h . 'h ' . $timestr;
            }
            if ($diff->d>0)
            {
                $timestr=$diff->d . 'd, ' . $timestr;
            }
            if ($diff->m>0)
            {
                $timestr=$diff->m . 'm ' . $timestr;
            }
            if ($diff->y>0)
            {
                $timestr=$diff->y . 'y ' . $timestr;
            }
        }
        catch (\Exception $e)
        {
            $timestr="N/A";

        }        

            
        
        return [$status,$logs,$timestr];
    }

    public static function isAlreadyRunning($jobid)
    {
        /*
         * If jobid is empty, the page is loaded for the first time.
         * If not, ask TES whether the job exists in the system.
         * If the job results in 404 error, then it has run and deleted
         * or not has not run at all.
         */
        if (empty($jobid))
        {
            return false;
        }
        $url=self::getTesUrl();
        if (isset(Yii::$app->params['schemaTes']) && (!empty(Yii::$app->params['schemaTes'])))
        {
            $urlArray=[$url, 'jobid'=>$jobid];
        }
        else
        {
            $urlArray=[$url . "/$jobid"];
        }
        $client=new Client();
        $response = $client->createRequest()
                    ->setFormat(Client::FORMAT_JSON)
                    ->setMethod('GET')
                    ->setUrl($urlArray)
                    ->send();

        if ($response->getIsOk())
        {
            return true;
        }
        return false;
    }

    public static function cancelJob($jobid)
    {
        /*
         * Cancel job by making the appropriate call 
         * to the TES api.
         */
        $url=self::getTesUrl();
        if (isset(Yii::$app->params['schemaTes']) && (!empty(Yii::$app->params['schemaTes'])))
        {
            $urlArray=[$url . '/cancel', 'jobid'=>$jobid];
        }
        else
        {
            $urlArray=[$url . "/$jobid:cancel"];
        }
        $client=new Client();
        $response = $client->createRequest()
                    ->setFormat(Client::FORMAT_JSON)
                    ->setMethod('POST')
                    ->setUrl($urlArray)
                    ->send();
    }


    public static function listDirectories($directory)
    {
        $files = scandir($directory);
        $results=[];
        
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
        
        session_write_close();
        Software::exec_log($command,$out,$ret);
        session_start();
       
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

        $file=$folder . 'fields.json';

        if (file_exists($file))
        {  
            $content=file_get_contents($file);
        }
        else
        {
            /*
             * try fields.txt for backward compatibility
             */
            $file=$folder . 'fields.txt';
            
            if (file_exists($file))
            {  
                $content=file_get_contents($file);
            }
            else
            {
                return $fields;
            }

            
        }
        
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
        
        }
        else
        {
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
                if (!array_key_exists($fields[$i]->name,$json))
                {
                    continue;
                }
                $fields[$i]->value=$json[$fields[$i]->name];
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



    public static function getActiveProjects()
    {
        $username=User::getCurrentUser()['username']; 
        $client = new Client();
        $response = $client->createRequest()
                ->setMethod('GET')
                ->setUrl(Yii::$app->params['egciActiveQuotas'] . "&username=$username")
                ->send();

        $projects=$response->data;
        
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
                            ->groupBy('project')
                            ->all();
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
              ->all(); 
        
        return $result;
    }


    public static function getActiveProjectQuotas($username)
    {
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
            ];
        }

        return $results;

                

    }

    public static function getAllProjectQuotas($username)
    {
        $client = new Client();
        $response = $client->createRequest()
                ->setMethod('GET')
                ->setUrl(Yii::$app->params['egciAllQuotas'] ."&username=$username")
                ->send();

        $data=$response->data;
        
        $results=[];
        foreach($data as $row) 
        {
            $results[$row['name']]=[
                'num_of_jobs'=>$row['num_of_jobs'],
                'ram'=>$row['ram'],
                'cores'=>$row['cores'],
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
        $files = scandir($directory);
        $results=[];
        $i=0;
        foreach($files as $key => $value)
        {
            $path = realpath($directory . DIRECTORY_SEPARATOR . $value);
            
            if ($value != "." && $value != ".." && $value[0]!='.')
            {
                if (is_dir($path))
                {
                    $result=self::listFiles($path);
                    $results[$path]= $result;
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

    public static function getTesUrl()
    {
        return Yii::$app->params['teskEndpoint'] . "/v1/tasks";
    }


}
