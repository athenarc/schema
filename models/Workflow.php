<?php

namespace app\models;

use Yii;
use webvimark\modules\UserManagement\models\User;
use yii\httpclient\Client;
use yii\db\Query;

/**
 * This is the model class for table "workflow".
 *
 * @property int $id
 * @property string $name
 * @property string $version
 * @property string $location
 * @property string $uploaded_by
 * @property string $visibility
 * @property string $description
 * @property string $cwl_path
 * @property bool $has_example
 * @property string $biotools
 * @property string $dois
 * @property bool $covid19
 * @property string $original_image
 * @property string $github_link
 */
class Workflow extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'workflow';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['location', 'visibility', 'description', 'github_link', 'instructions', 'visualize'], 'string'],
            [['has_example', 'covid19'], 'boolean'],
            [['name'], 'string', 'max' => 100],
            [['version'], 'string', 'max' => 80],
            [['uploaded_by', 'biotools'], 'string', 'max' => 255],
            // [['cwl_path'], 'string', 'max' => 150],
            [['original_file'], 'string', 'max' => 200],
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
            'version' => 'Version',
            'location' => 'Location',
            'uploaded_by' => 'Uploaded By',
            'visibility' => 'Visibility',
            'description' => 'Description',
            'cwl_path' => 'Cwl Path',
            'has_example' => 'Has Example',
            'biotools' => 'Biotools',
            'dois' => 'Dois',
            'covid19' => 'Covid19',
            'original_image' => 'Original Image',
            'github_link' => 'Github Link',
            'instructions'=>'Instructions',
        ];
    }

    public static function getParameters($fields)
    {
        $params=[];
        $userFolder=Yii::$app->params['userDataPath'] . explode('@',User::getCurrentUser()['username'])[0];
        // print_r($fields);
        // exit(0);
        foreach ($fields as $field)
        {
            //field is optional and empty
            if (($field->optional) && ($field->value==''))
            {
                continue;
            }
            if (!$field->is_array)
            {
                // print_r($field);
                // exit(0);
                if ($field->field_type=='File')
                {
                    $value=['class'=>$field->field_type, 'path'=> "ftp://" . Yii::$app->params['ftpIp'] . $userFolder . '/' . $field->value];
                    $params[$field->name]=$value;
                    // print_r($params);
                    // print_r("<br /><br />");
                }
                else if ($field->field_type=='Directory')
                {
                    $value=['class'=>$field->field_type, 'path'=> "ftp://" . Yii::$app->params['ftpIp'] . $userFolder . '/' . $field->value];
                    $params[$field->name]=$value;
                }
                else if ($field->field_type=='boolean')
                {
                    $params[$field->name]=$field->value ? true : false;
                }
                else
                {
                    $params[$field->name]=$field->value;
                }
            }
            else
            {
                $tmpArray=explode(';',$field->value);
                if ($field->field_type=='File')
                {
                    $finalArray=[];
                    foreach ($tmpArray as $val)
                    {
                        $value=['class'=>$field->field_type, 'path'=> "ftp://" . Yii::$app->params['ftpIp'] . $userFolder . '/' . $val];
                    }
                    
                    $params[$field->name]=$finalArray;
                }
                else if ($field->field_type=='Directory')
                {
                    $finalArray=[];
                    foreach ($tmpArray as $val)
                    {
                        $value=['class'=>$field->field_type, 'path'=> "ftp://" . Yii::$app->params['ftpIp'] . $userFolder . '/' . $val];
                    }
                    
                    $params[$field->name]=$finalArray;
                }
                else
                {
                    $params[$field->name]=$tmpArray;
                }
            }
            
        }
        // print_r("<br /><br />");
        // print_r($params);
        // exit(0);
        return json_encode($params,JSON_UNESCAPED_SLASHES);
    }

    /*
     * If the job did not submit, then return false
     */
    public static function isAlreadyRunning($jobid)
    {
        if ($jobid=='')
        {
            return false;
        }
        
    }

    /*
     * Returns a list of folders to be used in the 
     * select folder popup
     */
    public function listDirectories($directory)
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

    /*
     * Returns a nested list of files to be used in the 
     * select file popup.
     */
    public function listFiles($directory)
    {
        $files = scandir($directory);
        $results=[];
        $i=0;
        foreach($files as $key => $value)
        {
            $path = realpath($directory.DIRECTORY_SEPARATOR.$value);
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

    /*
     * Returns a list of files recursively (absolute paths)
     */
    public function listFilesNonNested($directory)
    {
        
        $files = scandir($directory);
        $results=[];
        $i=0;
        foreach($files as $key => $value)
        {
            $path = realpath($directory.DIRECTORY_SEPARATOR.$value);
            if ($value != "." && $value != ".." && $value[0]!='.')
            {
                if (is_dir($path))
                {
                    $result=self::listFilesNonNested($path); 
                    $results=$results + $result;
                }
                else
                {
                    $results[]=$path;
                }
                
            }

        }

        return $results;

    }

    public static function addLimits($workflow,$maxCores,$maxMem)
    {
        $allowedExt=['txt'=>'', 'cwl'=>'', 'yaml'=>'', 'yml'=>''];
        /*
         * Find the name of the main workflow file, in order to use it 
         * and find the file in the new location
         */
        $splitMainName=explode('/',$workflow->location);
        $mainName=end($splitMainName);
        
        /*
         * Copy files to the new location
         */
        $dataFolder=Yii::$app->params['tmpWorkflowPath'] . '/' . str_replace(' ','-',$workflow->name) . '/' . str_replace(' ','-',$workflow->version) . '/';
        $nid=uniqid();
        $tmpFolder=Yii::$app->params['tmpWorkflowPath'] . 'tmp-workflows/' . $nid ;
        $command="cp -r $dataFolder $tmpFolder";
        exec($command, $out, $ret);

        /*
         * Return all files in a list
         */
        $files=self::listFilesNonNested($tmpFolder);
        /*
         * First multiply mem because it is a float
         * and then turn it into an integer
         */
        $maxMem*=1024;
        $maxMem=intVal($maxMem);
        $maxCores=intVal($maxCores);

        foreach ($files as $file)
        {
            /*
             * Find the new main workflow file location
             * and discard any files that are not text files.
             */
            $fileSplit=explode('/',$file);
            $filename=end($fileSplit);
            if ($filename==$mainName)
            {
                $newMain=str_replace($tmpFolder,"/workflows/tmp-workflows/" . $nid ,$file);
                continue;
            }
            $fileSplit=explode('.',$filename);
            $extension=end($fileSplit);

            if (!isset($allowedExt[$extension]))
            {
                $command="rm $file";
                exec($command,$out,$ret);
                continue;
            }

            /*
             * Check if file is a commandLineTool
             * and add the resource limits to it
             */
            $content="";
            $content=yaml_parse_file($file);
            if (empty($content))
            {
                continue;
            }
            
            if (!isset($content['class']))
            {
                continue;
            }
            
            if ($content["class"]!='CommandLineTool')
            {
                continue;
            }

            /*
             * Find if a resource requirement already exists in hints or requirements
             */
            $hintsExist=isset($content['hints']);
            $reqsExist=isset($content['requirements']);
            $inHints=false;
            $inReqs=false;
            $hintsIsAssoc=false;
            $reqsIsAssoc=false;
            $requirement='';
            /*
             * Check the hints section and if it there is one
             * add or modify the limits
             */
            if ($hintsExist)
            {
                $hints=$content['hints'];
                $hintsIsAssoc= array_keys($hints) !== range(0, count($hints) - 1);
                if ($hintsIsAssoc)
                {
                    if (isset($hints['ResourceRequirement']))
                    {
                        $inHints=true;
                        $content['hints']['ResourceRequirement']['coresMax']=$maxCores;
                        $content['hints']['ResourceRequirement']['ramMax']=$maxMem;
                    }

                }
                else
                {
                    foreach ($hints as $hintsIndex=>$value)
                    {
                        if ($value['class']=='ResourceRequirement')
                        {
                            $inHints=true;
                            $content['hints'][$hintsIndex]['coresMax']=$maxCores;
                            $content['hints'][$hintsIndex]['ramMax']=$maxMem;
                        }
                    }
                }
            }
            /*
             * Check the requirements section and if it there is one
             * add or modify the limits
             */
            if ($reqsExist)
            {
                $reqs=$content['requirements'];
                $reqsIsAssoc= array_keys($reqs) !== range(0, count($reqs) - 1);
                if ($reqsIsAssoc)
                {
                    if (isset($reqs['ResourceRequirement']))
                    {
                        $inReqs=true;
                        $content['requirements']['ResourceRequirement']['coresMax']=$maxCores;
                        $content['requirements']['ResourceRequirement']['ramMax']=$maxMem;
                    }

                }
                else
                {
                    foreach ($reqs as $reqsIndex=>$value)
                    {
                        if ($value['class']=='ResourceRequirement')
                        {
                            $inReqs=true;
                            $content['requirements'][$reqsIndex]['coresMax']=$maxCores;
                            $content['requirements'][$reqsIndex]['ramMax']=$maxMem;
                        }
                    }
                }
            }

            if ((!$inHints) && (!$inReqs))
            {
                if (!$reqsExist)
                {
                    $content['requirements']=[];
                    $content['requirements']['ResourceRequirement']=[];
                    $content['requirements']['ResourceRequirement']['coresMax']=$maxCores;
                    $content['requirements']['ResourceRequirement']['ramMax']=$maxMem;
                }
                else
                {
                    if ($reqsIsAssoc)
                    {
                        if (isset($reqs['ResourceRequirement']))
                        {
                            $content['requirements']['ResourceRequirement']['coresMax']=$maxCores;
                            $content['requirements']['ResourceRequirement']['ramMax']=$maxMem;
                        }
                    }
                    else
                    {
                        foreach ($reqs as $reqsIndex=>$value)
                        {
                            if ($value['class']=='ResourceRequirement')
                            {
                                $content['requirements'][$reqsIndex]['coresMax']=$maxCores;
                                $content['requirements'][$reqsIndex]['ramMax']=$maxMem;
                            }
                        }
                    }

                }
            }
            $retVal=yaml_emit_file($file,$content);


            
        }

        return [$newMain,$tmpFolder];

    }

    public static function runWorkflow($workflow, $newLocation, $tmpWorkflowFolder, $workflowParams, $fields,$user, 
                                            $project,$maxMem,$maxCores,$outFolder)
    {
        $url=Yii::$app->params['wesEndpoint'] . '/ga4gh/wes/v1/runs';
        $client = new Client();
        $response = $client->createRequest()
                ->addHeaders(['Content-Type'=>'multipart/form-data','Accept'=>'application/json'])
                ->addContent('workflow_url',$newLocation)
                ->addContent('workflow_params',$workflowParams)
                ->addContent('workflow_type','CWL')
                ->addContent('workflow_type_version','v1.0')
                ->setMethod('POST')
                ->setUrl($url)
                ->send();
                // ->toString();

        $statusCode=$response->getStatusCode();
        if ($statusCode==400)
        {
            $error='Malformed request. Please contact an administrator';
            return ['jobid'=>'','error'=>$error];
        }
        else if ($statusCode==401)
        {
            $error='Request unauthorized. Please contact an administrator';
            return ['jobid'=>'','error'=>$error];
        }
        else if ($statusCode==403)
        {
            $error='Requester not authorized to perform this action. Please contact an administrator';
            return ['jobid'=>'','error'=>$error];
        }
        else if ($statusCode==500)
        {
            $error='An unexpected error occurred. Please contact an administrator';
            return ['jobid'=>'','error'=>$error];
        }
        else if ($statusCode==500)
        {
            $error='Error 500. Please contact an administrator';
            return ['jobid'=>'','error'=>$error];
        }
        else if ($statusCode==502)
        {
            $error='Error 502. Please contact an administrator';
            return ['jobid'=>'','error'=>$error];
        }
        /*
         * Save the outFolder field without the user data path in the database
         */
        $outFieldValue=$outFolder;
        $outFolder=Yii::$app->params['userDataPath'] . '/' . explode('@',User::getCurrentUser()['username'])[0] . '/' . $outFolder;
        $data=$response->data;
        
        $jobid=$data['run_id'];
        // print_r($data);
        // exit(0);

        $query=Yii::$app->db->createCommand()->insert('run_history',
                [

                    "username"=>User::getCurrentUser()['username'],
                    "jobid" => $jobid,
                    "omountpoint" => $outFieldValue,
                    "start" => 'NOW()',
                    "softname" => $workflow->name,
                    "softversion"=> $workflow->version,
                    "project"=>$project,
                    "max_ram"=> $maxMem,
                    "max_cpu" => $maxCores,
                    "software_id" => $workflow->id,
                    'type'=>'workflow',

                ]
            )->execute();
        
        $tmpFolder=Yii::$app->params['tmpFolderPath'] . '/' . $jobid;
        $command="mkdir -p $tmpFolder";
        exec($command,$ret,$out);
        $command="chmod 777 $tmpFolder";
        exec($command,$ret,$out);


        /*
         * Save field values in a file
         */
        $fieldValues=[];
        foreach($fields as $field)
        {
        
            $fieldValues[$field->name]=$field->value;
            
        }
        // $fieldValues=implode("\n",$fieldValues);
        $fieldValues=json_encode($fieldValues,JSON_UNESCAPED_SLASHES);

        $filename=$tmpFolder . '/' . 'fields.txt';
        file_put_contents($filename, $fieldValues);

        $filename=$tmpFolder . '/tmpWorkDir.txt';
        file_put_contents($filename,$tmpWorkflowFolder);



        $monitorScript=$scheduler="sudo -u ". Yii::$app->params['systemUser'] . " " . Yii::$app->params['scriptsFolder'] . "/workflowMonitorAndClean.py";
        $arguments=[
            $monitorScript, self::enclose($jobid),self::enclose(Yii::$app->params['wesEndpoint']),
            self::enclose(Yii::$app->params['teskEndpoint']), self::enclose($outFolder), self::enclose($tmpFolder)];
        $monitorCommand=implode(' ',$arguments);
        // print_r($monitorCommand);
        // exit(0);
        shell_exec(sprintf('%s > /dev/null 2>&1 &', $monitorCommand));



        return ['jobid'=>$jobid,'error'=>''];

    }

    public static function getLogs($jobid)
    {
        $url=Yii::$app->params['wesEndpoint'] . '/ga4gh/wes/v1/runs/' . $jobid;
        $client = new Client();
        $response = $client->createRequest()
                ->addHeaders(['Content-Type'=>'application/json','Accept'=>'application/json'])
                ->setMethod('GET')
                ->setUrl($url)
                ->send();
        $statusCode=$response->getStatusCode();
        if ($statusCode==400)
        {
            $error='Malformed request. Please contact an administrator';
            return ['jobid'=>'','error'=>$error];
        }
        else if ($statusCode==401)
        {
            $error='Request unauthorized. Please contact an administrator';
            return ['jobid'=>'','error'=>$error];
        }
        else if ($statusCode==403)
        {
            $error='Requester not authorized to perform this action. Please contact an administrator';
            return ['jobid'=>'','error'=>$error];
        }
        else if ($statusCode==500)
        {
            $error='An unexpected error occurred. Please contact an administrator';
            return ['jobid'=>'','error'=>$error];
        }
        else if ($statusCode==502)
        {
            $error='An unexpected error occurred. Please contact an administrator';
            return ['jobid'=>'','error'=>$error];
        }

        $data=$response->data;
        $outputs=$data['outputs'];
        $runLog=$data['run_log'];
        $taskLogs=$data['task_logs'];
        $status=$data['state'];
        $start=new \DateTime($runLog['task_started']);
        $now = new \DateTime();

        $running_time=$start->diff($now);

        $time='';
        if ($running_time->m!=0)
        {
            $time.=$running_time->m . 'mo, ';
        }
        if ($running_time->d!=0)
        {
            $time.=$running_time->d . 'd, ';
        }
        if ($running_time->h!=0)
        {
            $time.=$running_time->h . 'h, ';
        }
        if ($running_time->i!=0)
        {
            $time.=$running_time->i . 'm, ';
        }
        if ($running_time->s!=0)
        {
            $time.=$running_time->s . 's';
        }
        
        $logs=[];
        $i=1;
        // print_r($taskLogs);
        // exit(0);
        foreach ($taskLogs as $index=>$log)
        {
            // print_r($status);
            // exit(0);
            if (!empty($log))
            {
                $value=[
                            'step'=>$i,
                            'name'=>$log['name'],
                            'status' => $log['state'],
                            'description' => $log['description']
            
                        ];
            }
            else
            {
                $value=[];
            }
            
            $logs[]=$value;
            $i++;
        }

        // foreach ($taskLogs[0] as $key => $value)
        // {
        //     print_r($key);
        //     print_r("<br /><br />");
        //     print_r($value);
        //     print_r("<br /><br /><br /><br />");
        // }
        // print_r([$time,$status,$logs]);
        // exit(0);
        return [$time,$status,$logs];

    }

    
    public static function getWorkflowDescriptions($softUser)
    {
        $query=new Query;

        $query->select('name,version,description')
              ->from('workflow')
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
            
            $results[]=['name'=>$name, 'version'=>$version, 'description'=>$description,];
            // $results[$name][$uploader][$version[0]]=$visibility;
        }
        
        // print_r($results);
        // exit(0);

        
        return $results;

        // return $rows;
    }

    public static function getWorkflowVisualizations($softUser)
    {
        $query=new Query;

        $query->select('name,version,visualize')
              ->from('workflow')
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
            $visualize=$row['visualize'];
            $results[]=['name'=>$name, 'version'=>$version, 'visualize'=>$visualize];
            // $results[$name][$uploader][$version[0]]=$visibility;
        }
        
        

        
        return $results;

       
    }

    public static function getWorkflowNames($softUser)
    {
        $query=new Query;

        $query->select('name,version,description,uploaded_by,visibility')
              ->from('workflow')
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
            $uploader=$row['uploaded_by'];
            $visibility=$row['visibility'];
            
            
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
        
        // print_r($results);
        // exit(0);

        
        return $results;

        // return $rows;
    }

    
    public static function getIndicators($softUser)
    {
        $query=new Query;

        $query->select(['name','version','covid19'])
              ->from('workflow')
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
            $covid19=$row['covid19'];

            if (!isset($results[$name]))
            {
                $results[$name]=[];
            }
            if (!isset($results[$name][$version]))
            {
                $results[$name][$version]=[];
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

    public static function getRerunFieldValues($jobid,$fields)
    {
        $folder=Yii::$app->params['tmpFolderPath'] . '/' . $jobid . '/';

        $file=$folder . 'fields.txt';

        $content=file_get_contents($file);
        // print_r($file);
        $json=json_decode($content,true);

        foreach ($fields as $field)
        {
            if (!array_key_exists($field->name,$json))
            {
                return false;
            }

            if ($field->field_type=='enum')
            {
                $tmp_array=explode('|',$field->enum_fields);
                $field->dropdownValues=[];
                foreach ($tmp_array as $item)
                {
                    $field->dropdownValues[$item]=$item;
                }
                $field->dropdownSelected=$json[$field->name];
                // print_r($fields[$index]->dropdownValues);
                // exit(0);
            }
            else
            {
                $field->value=$json[$field->name];
            }
            


        }

        return $fields;
            
    }

    public function getAvailableWorkflows()
    {
        $workflows=self::find()->all();
        // echo $query->createCommand()->getRawSql();
        // exit(0);

        $results=[];
        foreach ($workflows as $workflow)
        {
            $name=$workflow->name;
            $version=$workflow->version;
            
            
            $key=$workflow->id;
            $value=$name . '-' . $version;

            $results[$key]=$value;
        }
        
        return $results;
        // return $rows;
    }

    public static function enclose($string)
    {
        return "'" . $string . "'";
    }
}
