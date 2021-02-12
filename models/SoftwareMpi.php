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
use app\models\Software;

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
class SoftwareMpi extends \yii\db\ActiveRecord
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
            [['script', 'visibility', 'description', 'dois'], 'string'],
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
        
        if ((!empty($imount)) || (empty($omount)))
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



    /*
     * This function creates the job YAML file and sends it to Kubernetes
     */
    public static function addJob($command,$fields,
                                    $name, $version, $jobid, $user, 
                                    $isystemMountField, $osystemMountField, $iosystemMountField, 
                                    $project,$maxMem,$maxCores,$pernode,$processes)
    {
        
        /*
         * Scheduler (python) script physical location
         */
        
        /* 
         * Get image repository location from the DB
         */

        $software=Software::find()->where(['name'=>$name,'version'=>$version])->one();
        $image=$software->image;
        $workingdir='';
        $softwareId=$software->id;

        /*
         * Create the tmp folder to store the YAML file
         */
        $folder=Yii::$app->params['tmpFolderPath'] . "$jobid/";

        if (file_exists($folder))
        {
            exec("rm -r $folder", $out, $ret);
            exec("mkdir $folder", $out, $ret);
        }
        else
        {
            exec("mkdir $folder", $out, $ret);
        }
        
        /*
         * Store inputs in a file in the tmp directory
         */
        $fieldValues=[];
        foreach($fields as $field)
        {
            if ($field->field_type=='boolean')
            {
                $fieldValues[]=($field->value) ? 'true' : 'false';
            }
            else
            {
                $fieldValues[]=$field->value;
            }
            
        }
        $fieldValues=implode("\n",$fieldValues);

        $filename=$folder . 'fields.txt';
        file_put_contents($filename, $fieldValues);
        /*
         * Store the commands in a file in the tmp directory
         */
        $filename=$folder . 'commands.txt';

        file_put_contents($filename, $command);

        // $filename=$folder . 'user.txt';

        // file_put_contents($filename, User::getCurrentUser()['username']);

        // $filename=$folder . 'startTime.txt';

        // $dateTime= date("F j, Y, H:i:s");

        // file_put_contents($filename, $dateTime);


        exec("chmod 777 $folder -R");


        // print_r($osystemMount);
        // exit(0);
        /*
         * Classify software, create YAML job configuration file,
         * send the file to Kubernetes to run and get the machine type.
         */
        

        $query=Yii::$app->db->createCommand()->insert('run_history',
                [

                    "username"=>User::getCurrentUser()['username'],
                    "jobid" => $jobid,
                    "command" => $command,
                    "imountpoint" => $isystemMountField,
                    "omountpoint" => $osystemMountField,
                    "iomountpoint" => $iosystemMountField,
                    "softname" => $name,
                    "softversion"=> $version,
                    "project"=>$project,
                    "max_ram"=> $maxMem,
                    "max_cpu" => $maxCores,
                    "ram"=> $maxMem,
                    "cpu" => $maxCores*1000,
                    'software_id' => $softwareId,
                    'mpi_proc_per_node' =>$pernode,
                    'mpi_proc' => $processes,
                    'type'=>'job',

                ]
            )->execute();


    }

    public static function clusterSetup($jobid)
    {
        $script="sudo -u ". Yii::$app->params['systemUser'] . " " . Yii::$app->params['scriptsFolder'] . "setupMpiCluster.py";

        $folder=Yii::$app->params['tmpFolderPath'] . "$jobid/";

        $userFolder=Yii::$app->params['userDataPath'] . explode('@',User::getCurrentUser()['username'])[0] . '/';

        $history=RunHistory::find()->where(['jobid'=>$jobid])->one();

        $name=$history->softname;
        $version=$history->softversion;
        $software=Software::find()->where(['name'=>$name,'version'=>$version])->one();

        $iomountpoint='';
        if ((!empty($software->imountpoint)) && (!empty($software->omountpoint)))
        {
            if ($software->imountpoint==$software->omountpoint)
            {
                $iomountpoint=$software->imountpoint;
            }
        }

        $name=self::enclose($name);
        $image=self::enclose($software->image);
        $jobidquoted=self::enclose($jobid);
        $tmpfolder=self::enclose($folder);
        $imountpoint=self::enclose($software->imountpoint);
        $isystemMount=self::enclose($userFolder . $history->imountpoint);
        $omountpoint=self::enclose($software->omountpoint);
        $osystemMount=self::enclose($userFolder . $history->omountpoint);
        $iomountpoint=self::enclose($iomountpoint);
        $iosystemMount=self::enclose($userFolder . $history->iomountpoint);
        $maxMem=self::enclose($history->max_ram);
        $maxCores=self::enclose($history->max_cpu);
        $nfsIp=self::enclose(Yii::$app->params['nfsIp']);
        $pernode=self::enclose($history->mpi_proc_per_node);

        $arguments=[
            $name,$image,
            $jobidquoted,$tmpfolder,
            $imountpoint, $isystemMount,
            $omountpoint, $osystemMount,
            $iomountpoint, $iosystemMount,
            $maxMem,$maxCores, $pernode, $nfsIp];

        $schedulerCommand=$script . ' ' . implode(' ',$arguments) . " 2>&1";

        // print_r($schedulerCommand);
        // exit(0);
        session_write_close();
        exec($schedulerCommand,$output,$ret);
        session_start();
        print_r($output);

        /*
         * Start job to avoid another ajax since it is now non-blocking
         */
        $commandfile=$folder . 'commands.txt';
        $command=file_get_contents($commandfile);
        $command=trim($command);
        $podCommand='"mpiexec -n ' . $history->mpi_proc . 
                    ' -npernode '. $history->mpi_proc_per_node . ' --allow-run-as-root --hostfile /kube-openmpi/generated/hostfile ' . $command .
                    ' > /logs.txt 2>&1"';
        $kubeCommand="kubectl exec --request-timeout='0' -n mpi-cluster mpi-master -c mpi-master -- /bin/sh -c ";
        // print_r($podCommand);
        // exit(0);
        $fullCommand='sudo -u ' . Yii::$app->params['systemUser'] . ' ' . $kubeCommand . $podCommand;


        // print_r($fullCommand);
        // exit(0);

        $history->start='NOW()';
        $history->save();
        session_write_close();
        shell_exec(sprintf('%s > /dev/null 2>&1 &', $fullCommand));
        session_start();

        $jobName=strtolower($history->softname). '-' . $jobid;
        $yaml=$folder . $jobName . '.yaml';

        $arguments=[$tmpfolder,$yaml,$jobidquoted];
        $command="sudo -u ". Yii::$app->params['systemUser'] . " " . Yii::$app->params['scriptsFolder'] . 
                        '/mpiMonitorAndClean.py ' . implode(' ',$arguments);

        // exec($command,$out,$ret);
        // print_r($command);
        session_write_close();
        shell_exec(sprintf('%s > /dev/null 2>&1 &', $command));
        session_start();

    }
    public static function startJob($jobid)
    {
        $history=RunHistory::find()->where(['jobid'=>$jobid])->one();
        $folder=Yii::$app->params['tmpFolderPath'] . "$jobid/";
        $commandfile=$folder . 'commands.txt';

        $command=file_get_contents($commandfile);
        $command=trim($command);
        $podCommand='"mpiexec -n ' . $history->mpi_proc . 
                    ' -npernode '. $history->mpi_proc_per_node . ' --allow-run-as-root --hostfile /kube-openmpi/generated/hostfile ' . $command .
                    ' > /logs.txt 2>&1"';
        $kubeCommand="kubectl exec --request-timeout='0' -n mpi-cluster mpi-master -c mpi-master -- /bin/sh -c ";
        // print_r($podCommand);
        // exit(0);
        $fullCommand='sudo -u ' . Yii::$app->params['systemUser'] . ' ' . $kubeCommand . $podCommand;


        // print_r($fullCommand);
        // exit(0);

        $history->start='NOW()';
        $history->save();
        session_write_close();
        shell_exec(sprintf('%s > /dev/null 2>&1 &', $fullCommand));
        session_start();

        // print_r($fullCommand);
        // print_r($ret);
        // if ($ret!=0)
        // {
        //     $history->status='Error';
        //     $history->stop='NOW()';
        // }
        // else
        // {
        //     $history->status='Complete';
        //     $history->stop='NOW()';
        // }
        // $history->save();

        // if (($history->status=='Complete') || ($history->status=='Error'))
        // {
        //     self::cleanUp($jobid);
        // }

    }

    public static function checkJobRunning()
    {
        $command="sudo -u " . Yii::$app->params['systemUser'] ." kubectl get pods -n mpi-cluster --no-headers";
        session_write_close();
        exec($command,$out,$ret);
        session_start();
        if (empty($out))
        {
            return false;
        }

        $command='sudo -u ' . Yii::$app->params['systemUser'] .
                " kubectl exec --request-timeout='0' -n mpi-cluster mpi-master -c mpi-master -- /bin/sh -c" . ' "ps aux | grep mpiexec 2>&1" 2>&1';
        session_write_close();
        exec($command,$psout,$ret);
        session_start();
        
        $psout=array_filter($psout);
        if (count($psout)>2)
        {
            return true;
        }

        return false;


    }
    /*
     * Get pod logs by using the pod ID
     */
    public static function getLogs($jobid)
    {
        $logs=[];

        if (empty($history->stop))
        {
            if (!self::checkJobRunning())
            {
                $status='Completed';
                $logfile=Yii::$app->params['tmpFolderPath'] . "/$jobid/logs.txt";
                $i=0;
                $tries=3;
                if (!file_exists($logfile))
                {
                    /*
                     * Maybe there's something wrong. Give it 3 tries before you give up
                     */
                    
                    while ($i<$tries)
                    {
                        sleep(4);
                        if (!file_exists($logfile))
                        {
                            $i++;
                            continue;
                        }
                        else
                        {
                            break;
                        }
                    }
                    

                }
                if ($i==$tries)
                {
                    return [$status,$logs,'Timing error'];
                }
                $logs=file_get_contents($logfile);
                $logs=explode("\n",$logs);
            }
            else
            {
                $status='Running';
                $command='sudo -u ' . Yii::$app->params['systemUser'] .
                        " kubectl exec --request-timeout='0' -n mpi-cluster mpi-master -c mpi-master -- /bin/sh -c" . ' "cat /logs.txt" 2>&1';
                
                exec($command,$logs,$ret);
                // print_r($out);
                // exit(0);
                // foreach ($out as $line)
                // {
                //     $logs.=$line . "<br />";
                // }
            }
            
        }
        else
        {
            $logfile=Yii::$app->params['tmpFolderPath'] . "/$jobid/logs.txt";
            $logs=file_get_contents($file);
            $logs=explode("\n",$logs);
            $status='Completed';
        }
        // $status='Running';

        // print_r($logs);
        // exit(0);

        $history=RunHistory::find()->where(['jobid'=>$jobid])->one();

        $start=new \DateTime($history->start);
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
        
        

        return [$status,$logs,$time];
    }

    /*
     * Erase job after it is completed or terminated.
     */
    public function cancelJob($jobid)
    {
        // $folder="/data/docker/tmp/$jobid/";
        $folder=Yii::$app->params['tmpFolderPath'] . "/$jobid/";
        $history=RunHistory::find()->where(['jobid'=>$jobid])->one();
        // if (empty($history->stop))
        // {
        //     $history->stop='NOW()';
        //     $history->save();
        // }
        
        // $filename=$folder . 'endTime.txt';
        // $dateTime= date("F j, Y, H:i:s");
        // file_put_contents($filename, $dateTime);
        #Clear job

        $command='sudo -u ' . Yii::$app->params['systemUser'] .
                        " kubectl exec --request-timeout='0' -n mpi-cluster mpi-master -c mpi-master -- /bin/sh -c" . ' "cat /logs.txt" 2>&1';
        exec($command,$logs,$ret);

        $logs=implode("\n",$logs);

        file_put_contents($folder . '/logs.txt',$logs);
        
        $jobName=strtolower($history->softname). '-' . $jobid;
        $yaml=$folder . '/' . $jobName . '.yaml';

        $command="sudo -u " . Yii::$app->params['systemUser'] ." kubectl delete -f $yaml -n mpi-cluster";
        session_write_close();
        exec($command,$out,$ret);
        session_start();
        // print_r($command);

    }

    public static function anotherJobRunning()
    {
        $command="sudo -u " . Yii::$app->params['systemUser'] .' kubectl get pods -n mpi-cluster --no-headers 2>&1';

        exec($command,$out,$ret);
        // print_r($command);
        // print_r($out);
        // exit(0);

        if ($out[0]=='No resources found in mpi-cluster namespace.')
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    public function getRerunData($jobid)
    {
        $query=new Query;
        $result=$query->select(['command','imountpoint', 'omountpoint', 'iomountpoint', 'softname', 'softversion', 'machinetype','project','max_ram','max_cpu','mpi_proc_per_node','mpi_proc'])
              ->from('run_history')
              ->where(['jobid'=>$jobid])
              ->one();
        return $result;

    }

    public function getJobDetails($jobid)
    {
        $query=new Query;
        $result=$query->select(['command','imountpoint','omountpoint','iomountpoint', 'ram','cpu','start', 'stop', 'machinetype','status','softname','softversion'])
              ->from('run_history')
              ->where(['jobid'=>$jobid])
              ->one();
        return $result;

    }

    

    public static function getInactiveJobs()
    {

        $command='sudo -u '. Yii::$app->params['systemUser'] . ' kubectl get jobs --no-headers 2>&1';
        
        exec($command,$output,$ret);

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

    public function createCommand($script,$emptyFields,$fields,$mountpoint)
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
                        if (empty($field->value))
                        {
                            $errors[]="Field $field->name cannon be empty.";
                        }
                        else
                        {
                            if ($field->field_type=='File')
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
                            if ($field->field_type=='File')
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
            }
        }
        // else
        // {
        //     // print_r(4);
        //     $command.= ' ' . $argumentString;
        //     $choose_form=false;
        // }
        // print_r($command);
        // exit(0);
        return [$errors, $command];
    }


    public static function enclose($string)
    {
        return "'" . $string . "'";
    }
}
