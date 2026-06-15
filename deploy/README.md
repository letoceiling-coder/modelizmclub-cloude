# Деплой платформы «Моделизм»

Боевое окружение: **VPS Beget `31.207.75.124`**, домен `dev-cloude.modelizmclub.ru`
(DNS: `A *.modelizmclub.ru → 31.207.75.124`). Все данные в РФ (S3 — Selectel).

## Стек на сервере

- Nginx + PHP-FPM 8.3
- PostgreSQL 16
- Redis (кэш, очереди, сессии)
- Supervisor: Horizon (очереди), Reverb (WebSocket), Scheduler
- Certbot (Let's Encrypt, wildcard `*.modelizmclub.ru`)
- S3 Selectel (диск `s3`, медиа через spatie/medialibrary)

## 1. Подготовка сервера

```bash
sudo apt update && sudo apt install -y nginx postgresql redis-server supervisor \
    php8.3-fpm php8.3-cli php8.3-pgsql php8.3-redis php8.3-mbstring php8.3-xml \
    php8.3-curl php8.3-gd php8.3-zip php8.3-bcmath php8.3-intl php8.3-exif \
    certbot python3-certbot-nginx
```

PostgreSQL:

```bash
sudo -u postgres psql -c "CREATE USER modelizm WITH PASSWORD '••••••';"
sudo -u postgres psql -c "CREATE DATABASE modelizm OWNER modelizm;"
```

## 2. Код и зависимости

```bash
sudo mkdir -p /var/www/modelizmclub && cd /var/www/modelizmclub
git clone git@github.com:letoceiling-coder/modelizmclub-cloude.git current
cd current
cp .env.example .env   # заполнить секреты (БД, S3 Selectel, Reverb, SMTP, OAuth, платёжки)
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force --seed   # сидеры справочников (без DemoSeeder в проде)
php artisan storage:link
```

## 3. SSL (wildcard)

```bash
sudo certbot certonly --manual --preferred-challenges=dns \
    -d modelizmclub.ru -d '*.modelizmclub.ru'
# затем подставить пути в deploy/nginx/dev-cloude.modelizmclub.ru.conf
```

## 4. Nginx и Supervisor

```bash
sudo cp deploy/nginx/dev-cloude.modelizmclub.ru.conf /etc/nginx/sites-available/
sudo ln -s /etc/nginx/sites-available/dev-cloude.modelizmclub.ru.conf /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx

sudo cp deploy/supervisor/*.conf /etc/supervisor/conf.d/
sudo supervisorctl reread && sudo supervisorctl update
```

## 5. S3 Selectel

В `.env`:

```
FILESYSTEM_DISK=s3
AWS_ENDPOINT=https://s3.ru-1.storage.selcloud.ru
AWS_DEFAULT_REGION=ru-1
AWS_BUCKET=...
AWS_USE_PATH_STYLE_ENDPOINT=true
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
```

## 6. Бэкапы БД

`spatie/laravel-backup` запускается планировщиком (см. `routes/console.php`):
`backup:run --only-db` ежедневно в 03:00, очистка старых копий в 02:30.
Назначение хранилища бэкапов настраивается в `config/backup.php` (`backup.destination.disks`).

## 7. Последующие деплои

```bash
cd /var/www/modelizmclub/current && git pull && bash deploy/deploy.sh
```

## Проверка

- API health: `https://dev-cloude.modelizmclub.ru/api/v1/ping`
- OpenAPI-документация (Scramble): `https://dev-cloude.modelizmclub.ru/docs/api`
- WebSocket: `wss://dev-cloude.modelizmclub.ru/app/<REVERB_APP_KEY>`
