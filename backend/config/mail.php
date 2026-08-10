<?php

return [
    'default' => env('MAIL_MAILER', 'log'),

    'mailers' => [
        'resend' => [
            'transport' => 'resend',
        ],
        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],
    ],

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'notifications@example.com'),
        'name' => env('MAIL_FROM_NAME', 'Masjid Smart Screen'),
    ],
];
