<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'pgsql:host=postgres;dbname={{ .Values.postgres.deployment.dbName }}',
    'username' => '{{ .Values.postgres.deployment.dbUsername }}',
    'password' => '{{ .Values.postgres.deployment.dbPassword }}',
    'charset' => 'utf8',

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
