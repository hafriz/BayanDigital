###############################################################################
# BayanDigital – Masjid Smart Screen Backend
# PHP 8.3 / FPM / Nginx / Debian Bookworm
###############################################################################
FROM php:8.4-fpm-bookworm AS base

ENV DEBIAN_FRONTEND=noninteractive \
    APP_ENV=production \
    APP_DEBUG=false

# ---------- System packages ----------
RUN apt-get update && apt-get install -y --no-install-recommends \
        ca-certificates curl git gnupg libpng-dev libjpeg62-turbo-dev \
        libfreetype6-dev libzip-dev libxml2-dev libicu-dev \
        libonig-dev libcurl4-openssl-dev libssl-dev \
        unzip zip libpq-dev libldap2-dev libsodium-dev \
        nginx supervisor cron sudo default-mysql-client \
    && docker-php-ext-configure gd \
        --with-freetype --with-jpeg \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) \
        pdo pdo_mysql mbstring xml curl zip bcmath sodium opcache \
    && pecl install redis apcu \
    && docker-php-ext-enable redis apcu \
    && apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false \
    && rm -rf /var/lib/apt/lists/*

# ---------- Composer ----------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ---------- Nginx config ----------
RUN { \
        echo 'server {'; \
        echo '    listen 80;'; \
        echo '    server_name _;'; \
        echo '    root /var/www/html/public;'; \
        echo '    index index.php;'; \
        echo '    charset utf-8;'; \
        echo '    location / {'; \
        echo '        try_files $uri $uri/ /index.php$is_args$args;'; \
        echo '    }'; \
        echo '    location ~* \.apk$ {'; \
        echo '        types { application/vnd.android.package-archive apk; }'; \
        echo '        add_header Content-Disposition "attachment";'; \
        echo '        try_files $uri =404;'; \
        echo '    }'; \
        echo '    location ~ \.php$ {'; \
        echo '        fastcgi_pass 127.0.0.1:9000;'; \
        echo '        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;'; \
        echo '        fastcgi_param DOCUMENT_ROOT $realpath_root;'; \
        echo '        include fastcgi_params;'; \
        echo '    }'; \
        echo '    location ~ /\.(?!well-known) { deny all; }'; \
        echo '}'; \
    } > /etc/nginx/sites-available/default

# ---------- PHP production config ----------
RUN { \
        echo 'memory_limit = 256M'; \
        echo 'upload_max_filesize = 64M'; \
        echo 'post_max_size = 64M'; \
        echo 'max_execution_time = 300'; \
        echo 'max_input_time = 300'; \
        echo 'date.timezone = Asia/Kuala_Lumpur'; \
        echo 'opcache.enable = 1'; \
        echo 'opcache.memory_consumption = 128'; \
        echo 'opcache.max_accelerated_files = 10000'; \
        echo 'opcache.revalidate_freq = 2'; \
        echo 'opcache.validate_timestamps = 0'; \
    } > /usr/local/etc/php/conf.d/99-app.ini

# ---------- Application ----------
WORKDIR /var/www/html

# Copy composer files first for dependency caching
COPY backend/composer.json backend/composer.lock* ./

RUN composer install --no-dev --no-scripts --prefer-dist --no-interaction 2>/dev/null || \
    composer install --no-dev --prefer-dist --no-interaction

# Copy application source
COPY backend/ .

RUN composer dump-autoload --optimize \
    && mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# ---------- Entrypoint ----------
COPY docker/entrypoint.sh /entrypoint.sh
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
RUN chmod +x /entrypoint.sh \
    && echo 'www-data ALL=(ALL) NOPASSWD: /usr/local/bin/php' > /etc/sudoers.d/www-data

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
    CMD curl -fsSL http://localhost/up || exit 1

ENTRYPOINT ["/entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
