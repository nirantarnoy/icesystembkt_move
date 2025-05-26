<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-frontend',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'frontend\controllers',
    'modules' => [
        'api' => [
            'class' => 'frontend\modules\api\Api',
        ],
    ],
    'timeZone' => 'Asia/Bangkok',
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-frontend',
            'class' => 'yii\web\Request',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-frontend', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the frontend
            'name' => 'advanced-frontend',
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
        'errorHandler' => [
            'errorAction' => 'site_/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
          //  'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                '<controller:\w+>/<id:\d+>' => '<controller>/view',
                '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
                'module/<module:\w+>/<controller:\w+>/<action:\w+>' => '<module>/<controller>/<action>',
            ],
        ],
        'urlManagerBackend' => [
            'class' => 'yii\web\urlManager',
            'baseUrl' => '/icesystem/backend/web',
            'scriptUrl'=>'/icesystem/backend/web/index.php',
//            'baseUrl' => 'http://app.sst.ac.th',
//            'scriptUrl' => 'http://app.sst.ac.th/index.php',
//            'baseUrl' => 'http://app.sst.ac.th',
//            'scriptUrl' => 'http://app.sst.ac.th/index.php',
            'enablePrettyUrl' => false,
            'showScriptName' => true,
        ],
        /*
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        */
    ],
    'params' => $params,
];
