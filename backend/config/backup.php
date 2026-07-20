<?php

return [
    'google' => [
        'client_id' => env('GOOGLE_DRIVE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_DRIVE_CLIENT_SECRET'),
        'redirect_uri' => env('GOOGLE_DRIVE_REDIRECT_URI'),
        'folder_name' => env('GOOGLE_DRIVE_FOLDER_NAME', 'BayanDigital Backups'),
    ],

    'schedule' => [
        'enabled' => env('BACKUP_SCHEDULE_ENABLED', true),
        'frequency' => env('BACKUP_SCHEDULE_FREQUENCY', 'daily'),
        'time' => env('BACKUP_SCHEDULE_TIME', '03:00'),
        'keep_days' => env('BACKUP_KEEP_DAYS', 30),
    ],
];
