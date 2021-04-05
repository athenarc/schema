<?php

return [
    /*
     * The following parameters must be left unchanged
     */
    'bsDependencyEnabled' => false,
    'adminEmail' => 'admin@example.com',
    'userDataPath' => '/data/docker/user-data/',
    'tmpFolderPath' => '/data/docker/tmp/',
    'ROCratesFolder' => '/data/docker/RO-crates/',
    'profilesFolderPath' => '/data/docker/profiles',
    'tmpImagePath' => '/data/docker/tmp-images/',
    'tmpWorkflowPath' => '/data/docker/workflows/',
    'scriptsFolder' => '/app/web/schema/scheduler_files/',
    'workflowsFolder' => '/data/docker/workflows',
    'archivedWorkflowsFolder' => '/data/docker/archived_workflows',
    'systemUser' => 'root',
    'nfsIp' => '****',
    /*
     * Change the following parameters according to your installation
     */
    'ftpIp' => 'ftp.schema.svc.cluster.local',
    'teskEndpoint' => '<your_tesk_installation_url>',
    'wesEndpoint' => '<your_wes_installation_url>',
    'standalone' => true,
    'standaloneResources'=>
    [
        'maxCores'=> 8,
        'maxRam' => 16,
    ],
    'classifierMemLimit'=>8,
    'metrics_url' => '*******',
    'namespaces' => [
        'jobs'=>'schema'
    ]



];

?>
