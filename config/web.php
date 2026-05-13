<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            // !!! chèn một khóa bí mật vào phần dưới (nếu nó trống) - điều này là bắt buộc để xác thực cookie
            'cookieValidationKey' => 'lqog4-Od_VywviyPxtpNRF-zE9CzObX0',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // gửi tất cả thư đến một tệp theo mặc định.
            'useFileTransport' => true,
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
        
        // --- THÊM CẤU HÌNH GOOGLE LOGIN TẠI ĐÂY ---
           'authClientCollection' => [
            'class' => 'yii\authclient\Collection',
            'clients' => [
                'google' => [
                    'class' => 'yii\authclient\clients\Google',
                    'clientId' => getenv('GOOGLE_CLIENT_ID'),
                    'clientSecret' => getenv('GOOGLE_CLIENT_SECRET'),

                ],
            ],
        ],
        // ------------------------------------------
        // ------------------------------------------

        'db' => $db,
        
        
        'urlManager' => [
            'enablePrettyUrl' => false,  // Tắt pretty URL để tránh issues với trailing slash
            'showScriptName' => true,
            'rules' => [
                // Các quy tắc định tuyến của ông
            ],
        ],
        
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // cấu hình điều chỉnh cho môi trường 'dev'
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // bỏ comment dòng dưới để thêm IP của ông nếu ông không kết nối từ localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // bỏ comment dòng dưới để thêm IP của ông nếu ông không kết nối từ localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;