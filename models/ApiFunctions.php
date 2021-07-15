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
class ApiFunctions extends Model
{
    public static function runTesk($teskData,$username,$project,$executor)
    {

        /*
         * Send job to TESK
         */
        $client = new Client(['baseUrl' => 'https://tesk.egci-endpoints.imsi.athenarc.gr']);
        $teskResponse = $client->createRequest()
                                ->setMethod('POST')
                                ->setFormat(Client::FORMAT_JSON)
                                ->setUrl('v1/tasks')
                                ->setData($teskData)
                                ->send();
        
        if (!$teskResponse->getIsOk())
        {
            $status=$teskResponse->getStatusCode();
            
            
            return [$status,[]];

        }

        
        /*
         * Get ID from TESK response 
         * and add job to the DB.
         */
        $teskResponseData=$teskResponse->data;

        $task=$teskResponseData['id'];

        $history=new RunHistory();

        $history->username=$user->username;
        $history->jobid = $task;
        $history->command = implode(' ',$executor['command']);
        $history->image= $executor['image'];
        $history->name= 'Remote Job';
        $history->start = 'NOW()';
        $history->project=$project;
        $history->type='remote-tesk-job';
        $history->remote_status_code=0;

        $history->insert();

        $tmpFolder=Yii::$app->params['tmpFolderPath'] . '/' . $task;
        Software::exec_log("mkdir $tmpFolder",$out,$ret);
        Software::exec_log("chmod 777 $tmpFolder -R");

        /*
         * Call a script that monitors the job
         * and fills the DB when the job is complete
         */
        $monitorScript=Software::sudoWrap(Yii::$app->params['scriptsFolder'] . "/remoteJobMonitor.py");
        $arguments=[$monitorScript, Software::enclose($task), Software::enclose(Yii::$app->params['teskEndpoint']), Software::enclose($tmpFolder)];
        $monitorCommand=implode(' ',$arguments);
        // print_r($monitorCommand);
        // exit(0);

        shell_exec(sprintf('%s > /dev/null 2>&1 &', $monitorCommand));

        return [200,$teskResponseData];
    }
    public static function runSchemaTes($tesData,$project,$username,$quotas)
    {
        /*
         * Assign unique id to job
         */
        $jobid=uniqid();
        
        /*
         * Create temporary folder where the data will be saved
         */
        $folder=Yii::$app->params['tmpFolderPath'] . '/' . $jobid;
        if (!file_exists($folder))
        {
            Software::exec_log("mkdir $folder",$out,$ret);
        }
        
        Software::exec_log("chmod 777 $folder -R");
        /*
         * Save data file
         */
        $dataFile=$folder . '/data.json';
        $strData=json_encode($tesData);
        file_put_contents($dataFile,$strData);
        /*
         * get quotas
         */
        $cores=$quotas[0]['cores']*1000;
        $mem=$quotas[0]['ram'];
        $script=Software::sudoWrap(Yii::$app->params['scriptsFolder'] . "/schema-tes.py");
        $arguments=[$script,$dataFile, $folder, $jobid, Yii::$app->params['nfsIp'], $cores, $mem];
        $command=implode(' ',$arguments);
        // print_r($command);
        // exit(0);

        shell_exec(sprintf('%s > /dev/null 2>&1 &', $command));

        $executor=$tesData['executors'][0];
        $history=new RunHistory();

        $history->username=$username;
        $history->jobid = $jobid;
        $history->command = implode(' ',$executor['command']);
        $history->image= $executor['image'];
        $history->start = 'NOW()';
        $history->project=$project;
        $history->type='remote-schema-job';
        $history->remote_status_code=1;

        $history->insert();

        $tmpFolder=Yii::$app->params['tmpFolderPath'] . '/' . $jobid;
        Software::exec_log("mkdir $tmpFolder",$out,$ret);
        Software::exec_log("chmod 777 $tmpFolder -R");

        return [200,['id'=>$jobid]];

    }
    
}
