<?php
return [
    'user1' => [
        'id' => 1,
        'username' => 'testuser',
        'email' => 'test@example.com',
        'auth_key' => 'testkey',
        'password_hash' => Yii::$app->security->generatePasswordHash('password'),
        'status' => 10,
    ],
];
