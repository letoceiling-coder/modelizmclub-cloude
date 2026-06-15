#!/usr/bin/env bash
# Первичный деплой dev-cloude.modelizmclub.ru (отдельный сайт, не трогает dev.modelizmclub.ru).
set -eu

APP_DIR=/var/www/modelizmclub-cloude
DOMAIN=dev-cloude.modelizmclub.ru
DB_NAME=modelizm_cloude
DB_USER=modelizm_cloude

echo "==== [0] PHP extensions ===="
for e in pdo_pgsql redis mbstring xml curl gd zip bcmath intl exif fileinfo; do
    if php -m | grep -qi "^${e}$"; then echo "  $e OK"; else echo "  $e MISSING"; fi
done

echo "==== [1] PostgreSQL: пользователь и база ===="
DB_PASS=$(tr -dc 'A-Za-z0-9' </dev/urandom | head -c 24)
if sudo -u postgres psql -tAc "SELECT 1 FROM pg_roles WHERE rolname='${DB_USER}'" | grep -q 1; then
    sudo -u postgres psql -c "ALTER USER ${DB_USER} WITH PASSWORD '${DB_PASS}';"
    echo "  user exists -> password reset"
else
    sudo -u postgres psql -c "CREATE USER ${DB_USER} WITH PASSWORD '${DB_PASS}';"
    echo "  user created"
fi
if ! sudo -u postgres psql -tAc "SELECT 1 FROM pg_database WHERE datname='${DB_NAME}'" | grep -q 1; then
    sudo -u postgres psql -c "CREATE DATABASE ${DB_NAME} OWNER ${DB_USER};"
    echo "  db created"
else
    echo "  db exists"
fi
sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE ${DB_NAME} TO ${DB_USER};" >/dev/null
# Postgres 16: права на схему public
sudo -u postgres psql -d "${DB_NAME}" -c "GRANT ALL ON SCHEMA public TO ${DB_USER};" >/dev/null

echo "==== [2] Reverb креды ===="
REVERB_APP_ID=$(tr -dc '0-9' </dev/urandom | head -c 7)
REVERB_APP_KEY=$(tr -dc 'a-z0-9' </dev/urandom | head -c 20)
REVERB_APP_SECRET=$(tr -dc 'a-z0-9' </dev/urandom | head -c 20)

echo "==== [3] .env ===="
cd "$APP_DIR"
cat > .env <<ENV
APP_NAME="Modelizm Cloude"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://${DOMAIN}
APP_LOCALE=ru
APP_FALLBACK_LOCALE=ru
APP_FAKER_LOCALE=ru_RU

API_VERSION=v1
SCRAMBLE_ENABLED=true

APP_MAINTENANCE_DRIVER=file
PHP_CLI_SERVER_WORKERS=4
BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=warning

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=${DB_NAME}
DB_USERNAME=${DB_USER}
DB_PASSWORD=${DB_PASS}

SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_ENCRYPT=false

BROADCAST_CONNECTION=reverb
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
CACHE_STORE=redis
CACHE_PREFIX=modelizm_cloude_cache

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=3
REDIS_CACHE_DB=4

MAIL_MAILER=log
MAIL_FROM_ADDRESS="no-reply@${DOMAIN}"
MAIL_FROM_NAME="\${APP_NAME}"

REVERB_APP_ID=${REVERB_APP_ID}
REVERB_APP_KEY=${REVERB_APP_KEY}
REVERB_APP_SECRET=${REVERB_APP_SECRET}
REVERB_HOST=${DOMAIN}
REVERB_PORT=443
REVERB_SCHEME=https
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8080

# Selectel S3 (заполнить реальными ключами и переключить FILESYSTEM_DISK=s3)
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=ru-1
AWS_BUCKET=
AWS_ENDPOINT=https://s3.ru-1.storage.selcloud.ru
AWS_USE_PATH_STYLE_ENDPOINT=true
ENV
echo "  .env записан"

echo "==== [4] composer install ===="
export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --optimize-autoloader --no-interaction

echo "==== [5] APP_KEY ===="
php artisan key:generate --force

echo "==== [6] Миграции и сидеры ===="
php artisan migrate --force --seed

echo "==== [7] storage:link + кэш ===="
php artisan storage:link || true
php artisan config:cache
php artisan route:cache
php artisan event:cache

echo "==== [8] Права ===="
chown -R www-data:www-data "$APP_DIR"
chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

echo "==== ГОТОВО ===="
echo "DB_PASSWORD=${DB_PASS}"
echo "REVERB_APP_ID=${REVERB_APP_ID}"
echo "REVERB_APP_KEY=${REVERB_APP_KEY}"
echo "REVERB_APP_SECRET=${REVERB_APP_SECRET}"
