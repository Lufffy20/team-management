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
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-frontend',
            'parsers' => [
            'application/json' => 'yii\web\JsonParser', // ✅ MUST
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
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                    'task/add-subtask' => 'task/add-subtask',
                    'task/toggle-subtask' => 'task/toggle-subtask',
                    'task/delete-subtask' => 'task/delete-subtask',
                    'managment/mytasks' => 'managment/mytasks',
                    'task/delete-image' => 'task/delete-image',

            ],
        ],
    ],
    'modules' => [
        'v1' => [
            'class' => 'frontend\modules\v1\Module'
        ]
    ],
    'timeZone' => 'Asia/Kolkata',
    'params' => $params,
];
