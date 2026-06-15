#!/usr/bin/env bash
# Деплой «Моделизм» на VPS Beget (31.207.75.124), домен dev-cloude.modelizmclub.ru.
# Запуск на сервере из каталога релиза. Идемпотентен.
set -euo pipefail

APP_DIR="/var/www/modelizmclub/current"
cd "$APP_DIR"

echo "→ Включаем режим обслуживания"
php artisan down --render="errors::503" || true

echo "→ Обновляем зависимости (prod)"
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

echo "→ Миграции БД"
php artisan migrate --force

echo "→ Кэш конфигов/маршрутов/представлений/событий"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "→ Кэш OpenAPI-документации (Scramble)"
php artisan scramble:cache || true

echo "→ Линк на публичное хранилище"
php artisan storage:link || true

echo "→ Перезапуск очередей и Reverb"
php artisan horizon:terminate || true
sudo supervisorctl restart modelizm-reverb || true

echo "→ Выключаем режим обслуживания"
php artisan up

echo "✓ Деплой завершён"
