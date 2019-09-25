<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-api',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'api\controllers',
    'bootstrap' => ['log'],
    'modules' => [
        'v1' => [
            'class' => 'api\modules\v1\Module',
        ],
    ],
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-api',
            // Accept and parse JSON Requests
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => false,
            'enableSession' => false,
            'loginUrl' => null
        ],
        'botHelper' => [ // Component for bot helper functions
            'class' => 'api\components\BotHelper',
        ],
        'tapPayments' => [
            'class' => 'api\components\TapPayments',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                [ // SmsController
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/sms',
                    'pluralize' => false,
                    'patterns' => [
                        'POST' => 'receive',
                        'POST poll' => 'poll-for-message-to-send',
                        // OPTIONS VERBS
                        'OPTIONS' => 'options',
                        'OPTIONS poll' => 'options',
                        // 'OPTIONS employer' => 'options',
                        // 'OPTIONS change-password' => 'options'
                    ]
                ],
            ],
        ],
    ],
    'params' => $params,
];
