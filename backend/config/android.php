<?php

$metadataPath = base_path('public/android/latest.json');
$metadata = is_file($metadataPath)
    ? json_decode((string) file_get_contents($metadataPath), true)
    : [];

return [
    'version_name' => env('ANDROID_TV_VERSION_NAME', $metadata['version_name'] ?? '0.9.0'),
    'version_code' => (int) env('ANDROID_TV_VERSION_CODE', $metadata['version_code'] ?? 10),
    'apk_url' => env('ANDROID_TV_APK_URL', $metadata['apk_url'] ?? '/android/masjid-smart-screen-tv.apk?v=10'),
    'release_notes' => env('ANDROID_TV_RELEASE_NOTES', $metadata['release_notes'] ?? 'Latest bayanDigital Android TV build.'),
];
