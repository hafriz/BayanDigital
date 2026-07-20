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

## Automatic Kubernetes Deployment

Pushes to `main` that change the backend, container, or application manifests
trigger `.github/workflows/deploy.yml`. The job runs on a self-hosted runner,
builds the Laravel image, pushes both the commit SHA and `latest` tags to Harbor,
deploys the immutable SHA tag, and waits for the rollout to complete.

The self-hosted runner must have:

- Docker and `kubectl` installed.
- A Docker login for `harbor.development.rarecreation.xyz` in the runner user's
  Docker configuration.
- A Kubernetes context for the `development/hafriz-deployer` service account.
- Network access to Harbor and the Kubernetes API.

Before the first deployment, a cluster administrator must grant the scoped
permissions and register the local runner:

```bash
kubectl apply -f k8s/08-deployer-rbac.yaml
```

Create Kubernetes application secrets locally; never commit their values:

```bash
cp k8s/01-secrets.example.yaml k8s/01-secrets.yaml
```

Generate `APP_KEY`, database passwords, and Harbor credentials for production.
The previously committed credentials must be rotated because removing them from
the current tree does not remove them from Git history.

Android changes are built on a GitHub-hosted runner. A successful `main` build
updates the rolling `android-tv-latest` GitHub release, then the local runner
embeds the APK into the application image and deploys it to Kubernetes. Regular
backend deployments also download that release first, ensuring the public APK
remains available after website-only changes.

## Backend Management Console

The management console is available at `/admin/login`. Administrators can
manage user accounts, roles, registrations, masjid settings, and TV content.
Operators can manage masjids and TV content but cannot manage user accounts.

Android TVs use administrator-approved device pairing. The public masjid ID is
only an identifier: a TV searches for an approved location, displays a
time-limited six-digit pairing code, and receives a private device token only
after an administrator approves the matching request under **Paired TVs**.
Screen API requests without an active token are rejected, and administrators
can revoke a TV immediately.

Set `ADMIN_EMAIL` and `ADMIN_PASSWORD` in the `bayandigital-secrets` Kubernetes
secret before the first deployment. The startup seeder creates the first admin
only when that email does not already exist, so later deployments do not reset
an administrator's password. Change the temporary password from the Users page
after signing in.

## User Manual

A comprehensive user manual is available inside the admin panel at **/admin/manual**.
It covers masjid registration, screen content management, TV device pairing,
user management, backups, and troubleshooting.

## Support

- **Email:** [support@rarecreation.xyz](mailto:support@rarecreation.xyz)
- **GitHub Issues:** [hafriz/BayanDigital](https://github.com/hafriz/BayanDigital/issues)
- **Buy Me a Coffee:** [buymeacoffee.com/rarecreation](https://buymeacoffee.com/rarecreation)

If you find BayanDigital useful, consider buying us a coffee to support continued development.
