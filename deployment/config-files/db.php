<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'pgsql:host=postgres.schema.svc.cluster.local;dbname=***',
    'username' => '***',
    'password' => '***',
    'charset' => 'utf8',

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
