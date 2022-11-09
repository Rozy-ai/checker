<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['common\SetupChecker'],
    'modules' => [],
    'as access' => [
        'class' => \yii\filters\AccessControl::class,
        'except' => [
            'auth/login',
            'gii/*',
            'debug/*',
        ],
        'rules' => [
            [
                'allow' => true,
                'roles' => ['@']
            ]
        ]
    ],
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-backend_',
        ],
        'user' => [
            'identityClass' => common\models\User::class,
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-backend', 'httpOnly' => true],
            'loginUrl' => ['auth/login'],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
    ],
    'params' => $params,
];
