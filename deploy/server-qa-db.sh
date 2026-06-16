#!/usr/bin/env bash
# Создание QA PostgreSQL-базы и наполнение тестовыми данными для Swagger CRUD.
# Запуск на VPS: bash deploy/server-qa-db.sh
set -eu

APP_DIR="${APP_DIR:-/var/www/modelizmclub-cloude}"
cd "$APP_DIR"

# Читаем основные креды из .env
DB_USER=$(grep -E '^DB_USERNAME=' .env | cut -d= -f2- | tr -d '"')
DB_PASS=$(grep -E '^DB_PASSWORD=' .env | cut -d= -f2- | tr -d '"')
QA_DB="${DB_QA_DATABASE:-modelizm_cloude_qa}"
QA_USER="${DB_QA_USERNAME:-$DB_USER}"

echo ">> QA database: ${QA_DB} (user: ${QA_USER})"

if ! sudo -u postgres psql -tAc "SELECT 1 FROM pg_database WHERE datname='${QA_DB}'" | grep -q 1; then
    echo ">> CREATE DATABASE ${QA_DB}"
    sudo -u postgres psql -c "CREATE DATABASE ${QA_DB} OWNER ${QA_USER};"
fi

sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE ${QA_DB} TO ${QA_USER};" >/dev/null
sudo -u postgres psql -d "${QA_DB}" -c "GRANT ALL ON SCHEMA public TO ${QA_USER};" >/dev/null 2>&1 || true

set_env() {
    local key="$1" val="$2"
    if grep -q "^${key}=" .env; then
        sed -i "s|^${key}=.*|${key}=${val}|" .env
    else
        echo "${key}=${val}" >> .env
    fi
}

echo ">> update .env (QA sandbox)"
set_env DB_USE_QA "true"
set_env DB_QA_CONNECTION "pgsql_qa"
set_env DB_QA_HOST "127.0.0.1"
set_env DB_QA_PORT "5432"
set_env DB_QA_DATABASE "${QA_DB}"
set_env DB_QA_USERNAME "${QA_USER}"
set_env DB_QA_PASSWORD "${DB_PASS}"
set_env SANCTUM_EMAIL "demo@modelizmclub.ru"
set_env SANCTUM_PASSWORD "DemoPass123"

echo ">> rebuild config cache"
php artisan config:clear
php artisan config:cache

echo ">> qa:reset (migrate:fresh + QaDatabaseSeeder)"
php artisan qa:reset --force

echo "QA_DB_DONE"
