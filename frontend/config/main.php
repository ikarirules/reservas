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
    'name' => 'Reservas',
    'language' => 'es',
    'container' => [
        'definitions' => [
            // Paginación con estilos de Bootstrap 5 en todas las grillas.
            \yii\widgets\LinkPager::class => \yii\bootstrap5\LinkPager::class,
        ],
    ],
    // Toda la app requiere estar logueado con una categoría de personal.
    // Los invitados son redirigidos al login.
    'as access' => [
        'class' => \yii\filters\AccessControl::class,
        'ruleConfig' => ['class' => \common\components\RolAccessRule::class],
        'rules' => [
            [
                'controllers' => ['site'],
                'actions' => ['login', 'error', 'request-password-reset', 'reset-password'],
                'allow' => true,
            ],
            [
                'controllers' => ['site'],
                'actions' => ['logout'],
                'allow' => true,
                'roles' => ['@'],
            ],
            [
                'allow' => true,
                'roles' => array_keys(\common\models\User::roles()),
            ],
        ],
    ],
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-frontend',
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
