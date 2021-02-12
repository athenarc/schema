<?php


return [
    
    'bsDependencyEnabled' => false,
    'userDataPath' => 'directory where user files will be stored (must be exposed using NFS)',
    'tmpFolderPath' => 'directory where execution files for jobs will be stored',
    'tmpImagePath' => 'directory where user-uploaded images are stored',
    'tmpWorkflowPath' => 'directory where user-uploaded workflow files are temporarily stored',
    'systemUser' => 'user that with python and kubectl access',
    'nfsIp' => 'IP of the local NFS exposing dire',
    'ftpIp' => 'IP of the FTP service exposing the files of the users',
    'scriptsFolder' => 'directory where the scheduler_files are located <project-root/scheduler_files>',
    'workflowsFolder' => 'directory where uploaded workflows are permanently stored',
    'archivedWorkflowsFolder' => 'directory in which deleted workflows are archived',
    'teskEndpoint' => 'TESK endpoint',
    'wesEndpoint' => 'WES endpoint',
    'egciActiveQuotas' => 'location of API providing active project quotas (only if not running in standalone mode)',
    'egciSingleProjecteQuotas' => 'location of API providing quotas for a project (only if not running in standalone mode)',
    'standalone' => true, /* true when running in standalone mode */
    'standaloneResources'=> /* resources per job when running in standalone mode */
    [
        'maxCores'=> 8,
        'maxRam' => 16,
    ]

];
