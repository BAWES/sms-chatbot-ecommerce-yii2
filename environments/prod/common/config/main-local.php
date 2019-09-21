<?php
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=sms-production-cluster.cluster-c8mekjvvbygf.eu-west-2.rds.amazonaws.com;dbname=yo3an',
            'username' => 'yo3an',
            'password' => 'iamyo3an',
            'charset' => 'utf8mb4',

            // common configuration for slaves
            'slaveConfig' => [
                'username' => 'yo3an',
                'password' => 'iamyo3an',
                'attributes' => [
                    // use a smaller connection timeout
                    PDO::ATTR_TIMEOUT => 10,
                ],
            ],
            // list of slave configurations for Read-write splitting
            'slaves' => [
                ['dsn' => 'mysql:host=sms-production-cluster.cluster-ro-c8mekjvvbygf.eu-west-2.rds.amazonaws.com;dbname=yo3an']
            ],

            // Enable Caching of Schema to Reduce SQL Queries
            'enableSchemaCache' => true,
            // Duration of schema cache.
            'schemaCacheDuration' => 3600, // 1 hr
            // Name of the cache component used to store schema information
            'schemaCache' => 'cache',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.sendgrid.net',
                'username' => 'apikey',
                'password' => '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',
                'port' => '587',
                'encryption' => 'tls',
                'plugins' => [
                    [
                        'class' => 'Openbuildings\Swiftmailer\CssInlinerPlugin',
                    ],
                ],
            ],
        ],
    ],
];
