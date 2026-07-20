<?php

return [
    'version_name' => env('ANDROID_TV_VERSION_NAME', '0.1.0'),
    'version_code' => (int) env('ANDROID_TV_VERSION_CODE', 1),
    'apk_url' => env('ANDROID_TV_APK_URL', '/android/masjid-smart-screen-tv.apk'),
    'release_notes' => env('ANDROID_TV_RELEASE_NOTES', 'Initial Android TV build for Masjid Smart Screen.'),
];
