<?php

return [
    /*
     * The following parameters must be left unchanged
     */
    'name'=> '{{ .Values.wes.url }}',
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
    'nfsIp' => '{{ .Values.nfsIp }}',
    /*
     * Change the following parameters according to your installation
     */
    'ftpIp' => 'ftp.schema.svc.cluster.local',
    'teskEndpoint' => '{{ .Values.tesk.url }}',
    'wesEndpoint' => '{{ .Values.wes.url }}',
    'standalone' => {{ .Values.standalone.isStandalone }},
    'standaloneResources'=>
    [
        'maxCores'=> {{ .Values.standalone.resources.maxCores }},
        'maxRam' => {{ .Values.standalone.resources.maxRam }},
    ],
    'classifierMemLimit'=>8,
    'metrics_url' => '{{ .Values.metrics.url }}',
    'namespaces' => [
        'jobs'=>'schema'
    ]



];

?>
