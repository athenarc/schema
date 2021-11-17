<?php

namespace app\models;

use Yii;
use app\models\Softare;
use webvimark\modules\UserManagement\models\User;
use yii\helpers\Html;
use yii\httpclient\Client;

/**
 * This is the model class for table "jupyter_server".
 *
 * @property int $id
 * @property string|null $manifest
 * @property string|null $project
 * @property string|null $server_id
 * @property string|null $created_at
 * @property string|null $deleted_at
 * @property string|null $created_by
 * @property string|null $deleted_by
 * @property string|null $project_end_date
 * @property string|null $url
 * @property bool|null $active
 */
class JupyterServer extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public $cpu,$memory,$password;

    public static function tableName()
    {
        return 'jupyter_server';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'deleted_at', 'project_end_date'], 'safe'],
            [['created_by', 'deleted_by', 'url', 'image'], 'string'],
            [['active'], 'boolean'],
            [['manifest', 'project'], 'string', 'max' => 100],
            [['server_id'], 'string', 'max' => 20],
            [['password','image'],'required']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'manifest' => 'Manifest',
            'project' => 'Project',
            'server_id' => 'Server ID',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'created_by' => 'Created By',
            'deleted_by' => 'Deleted By',
            'project_end_date' => 'Project End Date',
            'url' => 'Url',
            'active' => 'Active',
            'password' => "Password for the Jupyter server",
            'image' => "Please select an image:",
        ];
    }

    public static function getActiveProjects()
    {
        $username=User::getCurrentUser()['username']; 
        $client = new Client();
        $response = $client->createRequest()
                ->setMethod('GET')
                ->setUrl(Yii::$app->params['egciActiveQuotas'] . "&username=$username")
                ->send();

        $apiProjects=$response->data;
        $projects=[];

        foreach ($apiProjects as $project)
        {
            $projects[$project['name']]=['cpu'=>$project['cores'], 'memory'=>$project['ram'], 'expires'=>$project['end_date']];
        }

        return $projects;

    }


    public static function matchServersWithProjects($projects)
    {
        $names=[];

        $servers=JupyterServer::find()->where(['active'=>true])->all();

        foreach ($servers as $server)
        {
            /*
             * Servers belong to the project and not the user.
             * Only one server can be active for a project at a single point in time.
             * This is done because the CLIMA API also returns projects in which 
             * the user participates but is not the owner.
             */
            if (isset($projects[$server->project]))
            {
                $projects[$server->project]['server']=$server;   
            }
        }

        return $projects;
    }

    public static function getProjectQuotas($project)
    {
        $username=User::getCurrentUser()['username']; 
        $url=Yii::$app->params['egciSingleProjecteQuotas'] . "&username=$username&project=$project";
        
        $client = new Client();
        $response = $client->createRequest()
                ->setMethod('GET')
                ->setUrl($url)
                ->send();
        $quotas=$response->data;

        if (!empty($quotas))
        {
            $quotas=$quotas[0];
        }
        return $quotas;

    }

    public function startServer()
    {
        $sid=uniqid();
        $username=User::getCurrentUser()['username'];
        $user=explode('@',$username)[0];

        $data=[];
        if (file_exists('/data/containerized'))
        {
            $nfs="container";
        }
        else
        {
            $nfs=Yii::$app->params['nfsIp'];
        }

        $data['nfs']=$nfs;
        $data['image']=$this->image;
        $data['id']=$sid;
        $data['folder']=Yii::$app->params['tmpFolderPath'] . '/' . $sid . '/';
        $data['mountFolder']=Yii::$app->params['userDataPath'] . $user . '/';
        $data['password']=$this->password;
        $data['expires']=$this->expires_on;
        $data['project']=$this->project;
        $data['user']=$username;
        $data['resources']=['cpu'=>$this->cpu,'mem'=>$this->memory];

        $file=$data['folder'] . $sid . '.json';

        $json_data=json_encode($data);

        exec("mkdir " . $data['folder']);
        exec ("chmod 777 -R " . $data['folder']);
        file_put_contents($file, $json_data);

        $command=Yii::$app->params['scriptsFolder'] . "jupyterServerStart.py " . self::enclose($file) . ' 2>&1' ;

        $command=Software::sudoWrap($command);

        exec($command,$out,$ret);
        
        $success='';
        $error='';
        $status='';
        session_write_close();
        if ($ret==0)
        {
            $server=JupyterServer::find()->where(['active'=>true,'project'=>$data['project']])->one();

            $isDown=true;
            $toleration=180;
            $appName=$sid . '-jupyter';
            while ($isDown)
            {
                sleep(5);

                try
                {
                    $client = new Client();
                    $response = $client->createRequest()
                            ->setMethod('GET')
                            ->setUrl($server->url)
                            ->send();
                    if ($response->getIsOk())
                    {
                        $isDown=false;
                    }

                }
                catch (yii\httpclient\Exception $e)
                {
                    /*
                     * This block is left empty on purpose.
                     * If the response fails for some reason (ingress not ready)
                     * just reduce the toleration variable (look at line before the if)
                     * and continue.
                     */
                }
                if ($toleration<=0)
                {
                    $namespace=Yii::$app->params['namespaces']['jupyter'];
                    $podComm="kubectl get pod -n $namespace -l app=$appName --no-headers | tr -s ' '";
                    $podComm=Software::sudoWrap($podComm);
                    exec($podComm,$podOut,$podRet);
                    $status='';
                    /*
                     * For some reason kubectl returns only the pod name without the 
                     * status. In that case pass in order to go to the next loop.
                     */
                    try
                    {
                        $podOut=explode(' ', $podOut[0]);
                        $status=$podOut[2];
                    }
                    catch (\Exception $e)
                    {
                        
                    }
                    
                    if (!empty($status))
                    {
                        if (($podOut[0]=='No') || (($podOut[2]!='Running') && ($podOut[2]!='ContainerCreating')))
                        {
                            break;
                        }
                        else if (($podOut[2]=='Running') || ($podOut[2]=='ContainerCreating'))
                        {
                            /*
                             * In this case you should wait until the server
                             * is ready, even if the toleration has been exhausted.
                             */
                        }
                    }
                }
                $toleration--;
                
            }
            if (!$isDown)
            {
                $success='Server was started successfully! It can be accessed <a href="' . $server->url . '" target="blank">here</a>.';
            }
            else
            {
                $stopText=Html::a('stop the server',['jupyter/stop-server','project'=>$this->project]);
                $error="There was an error creating the Jupyter server. Please $stopText and contact an administrator; report the following status: $status";
            }

        }
        else
        {
            $error='There was an error creating the Jupyter server. Please contact an administrator and report the following: <br />' . implode('<br />',$out);
        }
        session_start();
        return [$success,$error];
       
    }

    public function stopServer()
    {
        $username=User::getCurrentUser()['username'];

        $command=Yii::$app->params['scriptsFolder'] . "jupyterServerStop.py " . self::enclose($this->server_id) . ' '. self::enclose($username) . ' 2>&1' ;

        $command=Software::sudoWrap($command);

        exec($command,$out,$ret);

        $success='';
        $error='';
        if ($ret==0)
        {
            $success='Server was stopped successfully!';
        }
        else
        {
            $error='There was an error stopping the Jupyter server. Please contact an administrator and report the following: <br />' . implode('<br />',$out);
        }
        

        return [$success,$error];
    }

    public static function enclose($s)
    {
        return "'" . $s . "'";
    }


}
