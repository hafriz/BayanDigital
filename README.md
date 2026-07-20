# Masjid Smart Screen System

This repository contains the Laravel SaaS backend and Android TV frontend for a Masjid/Surau smart screen deployment.

## Pull Request Status

The current branch is intended to be opened as a PR with the title **Add Android TV app (Compose) with backend API, registration UI, and CI build**.

The PR should include:

- Laravel SaaS landing, Android download, and masjid/surau self-registration pages.
- Tenant-specific setup through a masjid/surau unique ID.
- JAKIM e-Solat prayer-time caching and API payloads for Android TV clients.
- Android TV Kotlin/Compose app scaffolding with setup, offline cache, periodic sync, display states, and sound alerts.
- GitHub Actions Android APK build workflow.

## Local Validation

Use these checks before opening or updating the PR:

```bash
php -l backend/app/Http/Controllers/Web/LandingPageController.php
php -l backend/app/Http/Controllers/Web/AndroidDownloadController.php
php -l backend/app/Http/Controllers/Web/MasjidRegistrationController.php
php -l backend/app/Http/Controllers/Api/V1/MasjidScreenController.php
php -l backend/routes/web.php
php -l backend/routes/api.php
cd android && gradle projects
```

A full Android APK build requires an Android SDK via `ANDROID_HOME` or `android/local.properties`.
