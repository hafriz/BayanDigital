#!/bin/bash
set -euo pipefail

APP_DIR="/var/www/html"

# ── Wait for MySQL ────────────────────────────────────────────────────────────
DB_HOST="${DB_HOST:-mariadb}"
DB_PORT="${DB_PORT:-3306}"
DB_USER="${DB_USER:-bayandigital}"
DB_PASS="${DB_PASS:?DB_PASS is required}"
DB_NAME="${DB_NAME:-bayandigital}"

echo "Waiting for MySQL at ${DB_HOST}:${DB_PORT}..."
for i in $(seq 1 60); do
    if mysqladmin ping -h "${DB_HOST}" -P "${DB_PORT}" -u "${DB_USER}" -p"${DB_PASS}" --silent 2>/dev/null; then
        echo "MySQL is ready."
        break
    fi
    echo "  Attempt ${i}/60..."
    sleep 2
done

# ── Generate APP_KEY if empty ─────────────────────────────────────────────────
if ! grep -q 'APP_KEY=base64:' "${APP_DIR}/.env" 2>/dev/null; then
    echo "Generating APP_KEY..."
    KEY=$(php "${APP_DIR}/artisan" key:generate --show 2>/dev/null || head -c 32 /dev/urandom | base64)
    if [ -f "${APP_DIR}/.env" ]; then
        sed -i "s|^APP_KEY=.*|APP_KEY=${KEY}|" "${APP_DIR}/.env"
    else
        echo "APP_KEY=${KEY}" > "${APP_DIR}/.env"
    fi
fi

# ── Run migrations ────────────────────────────────────────────────────────────
echo "Running migrations..."
php "${APP_DIR}/artisan" migrate --force 2>&1 || echo "Warning: migration issue (non-fatal)"

# ── Cache config ──────────────────────────────────────────────────────────────
php "${APP_DIR}/artisan" config:cache 2>/dev/null || true
php "${APP_DIR}/artisan" route:cache 2>/dev/null || true
php "${APP_DIR}/artisan" view:cache 2>/dev/null || true

# ── Fix permissions ───────────────────────────────────────────────────────────
chown -R www-data:www-data "${APP_DIR}/storage" "${APP_DIR}/bootstrap/cache" 2>/dev/null || true

# ── Start cron in background ──────────────────────────────────────────────────
mkdir -p /var/run/cron
cron || echo "Warning: cron daemon failed to start (non-fatal)"

exec "$@"
