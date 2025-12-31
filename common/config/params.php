<?php
return [
    'adminEmail' => 'noreply@example.com',
    'supportEmail' => 'noreply@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'My Application robot',
    'user.passwordResetTokenExpire' => 3600,
    'user.passwordMinLength' => 8,

    'user.verificationTokenExpire' => 86400,

    'frontendUrl' => 'http://frontend.test',
    'backendUrl'  => 'http://admin.test',

    // Role → avatar base URL mapping
    'avatarBaseUrls' => [
        1 => 'http://admin.test',      
        2 => 'http://manager.test',    
        3 => 'http://staff.test',      
    ],

];
