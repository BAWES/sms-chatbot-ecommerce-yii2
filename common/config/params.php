<?php
return [
    'adminEmail' => 'admin@example.com',
    'supportEmail' => 'support@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    'user.passwordResetTokenExpire' => 3600,
    'allowedOrigins' => [
        //'https://employer.dev.pogi.io/',
        //Dev Envs
        null,
        '*' //remove this once app is live
    ]
];
