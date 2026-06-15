#!/usr/bin/env bash
# Selectel S3 + поддомен img.modelizmclub.ru для отдачи медиа.
# Секреты передаются через переменные окружения (не хранятся в репозитории):
#   AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, AWS_BUCKET, AWS_DEFAULT_REGION,
#   AWS_ENDPOINT, MEDIA_PREFIX (опционально)
set -eu

APP_DIR="${APP_DIR:-/var/www/modelizmclub-cloude}"
IMG_DOMAIN="${IMG_DOMAIN:-img.modelizmclub.ru}"
S3_HOST="${S3_HOST:-s3.ru-3.storage.selcloud.ru}"
MEDIA_PREFIX="${MEDIA_PREFIX:-modelizm-cloude}"
MEDIA_URL="https://${IMG_DOMAIN}"

: "${AWS_ACCESS_KEY_ID:?AWS_ACCESS_KEY_ID required}"
: "${AWS_SECRET_ACCESS_KEY:?AWS_SECRET_ACCESS_KEY required}"
: "${AWS_BUCKET:?AWS_BUCKET required}"
: "${AWS_DEFAULT_REGION:?AWS_DEFAULT_REGION required}"
: "${AWS_ENDPOINT:?AWS_ENDPOINT required}"

cd "$APP_DIR"

set_env() {
  local key="$1" val="$2"
  if grep -q "^${key}=" .env 2>/dev/null; then
    sed -i "s|^${key}=.*|${key}=${val}|" .env
  else
    echo "${key}=${val}" >> .env
  fi
}

echo "==== [1] .env: S3 + медиа ===="
set_env FILESYSTEM_DISK "s3"
set_env MEDIA_DISK "s3"
set_env MEDIA_URL "$MEDIA_URL"
set_env MEDIA_PREFIX "$MEDIA_PREFIX"
set_env AWS_ACCESS_KEY_ID "$AWS_ACCESS_KEY_ID"
set_env AWS_SECRET_ACCESS_KEY "$AWS_SECRET_ACCESS_KEY"
set_env AWS_DEFAULT_REGION "$AWS_DEFAULT_REGION"
set_env AWS_BUCKET "$AWS_BUCKET"
set_env AWS_ENDPOINT "$AWS_ENDPOINT"
set_env AWS_USE_PATH_STYLE_ENDPOINT "true"
# AWS_URL не задаём — URL медиа берётся из MEDIA_URL (img-поддомен).
sed -i '/^AWS_URL=/d' .env 2>/dev/null || true

echo "==== [2] Проверка записи/чтения S3 ===="
php artisan config:clear
TEST_KEY="${MEDIA_PREFIX}/.healthcheck-$(date +%s).txt"
php -r "
require 'vendor/autoload.php';
\$app = require 'bootstrap/app.php';
\$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
\$disk = Illuminate\Support\Facades\Storage::disk('s3');
\$disk->put('${TEST_KEY}', 'ok', 'public');
if (\$disk->get('${TEST_KEY}') !== 'ok') { fwrite(STDERR, 'S3 read failed\n'); exit(1); }
echo 'S3_OK ' . \$disk->url('${TEST_KEY}') . PHP_EOL;
\$disk->delete('${TEST_KEY}');
"

echo "==== [3] Nginx: ${IMG_DOMAIN} → Laravel (прокси S3, приватный бакет) ===="
AVAIL="/etc/nginx/sites-available/${IMG_DOMAIN}"
ENABLED="/etc/nginx/sites-enabled/${IMG_DOMAIN}"
ROOT="${APP_DIR}/public"

cat > "$AVAIL" <<NGINX
server {
    listen 80;
    listen [::]:80;
    server_name ${IMG_DOMAIN};
    location /.well-known/acme-challenge/ { root /var/www/html; }
    location / { return 301 https://\$host\$request_uri; }
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name ${IMG_DOMAIN};

    root ${ROOT};
    index index.php;
    charset utf-8;
    client_max_body_size 220M;

    ssl_certificate     /etc/letsencrypt/live/${IMG_DOMAIN}/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/${IMG_DOMAIN}/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;

    add_header X-Content-Type-Options "nosniff" always;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }
}
NGINX

# Временный HTTP для certbot
if [ ! -d "/etc/letsencrypt/live/${IMG_DOMAIN}" ]; then
  cat > "$AVAIL" <<NGINX
server {
    listen 80;
    listen [::]:80;
    server_name ${IMG_DOMAIN};
    root /var/www/html;
    location /.well-known/acme-challenge/ { root /var/www/html; }
    location / { return 200 'pending ssl'; add_header Content-Type text/plain; }
}
NGINX
  ln -sf "$AVAIL" "$ENABLED"
  nginx -t && systemctl reload nginx
  certbot certonly --nginx -d "${IMG_DOMAIN}" -n --agree-tos \
    --register-unsafely-without-email --keep-until-expiring || true
  # Перезаписать полный конфиг после cert
  cat > "$AVAIL" <<NGINX
server {
    listen 80;
    listen [::]:80;
    server_name ${IMG_DOMAIN};
    location /.well-known/acme-challenge/ { root /var/www/html; }
    location / { return 301 https://\$host\$request_uri; }
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name ${IMG_DOMAIN};

    root ${ROOT};
    index index.php;
    charset utf-8;
    client_max_body_size 220M;

    ssl_certificate     /etc/letsencrypt/live/${IMG_DOMAIN}/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/${IMG_DOMAIN}/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }
}
NGINX
fi

ln -sf "$AVAIL" "$ENABLED"
nginx -t && systemctl reload nginx

echo "==== [4] Кэш Laravel ===="
php artisan config:cache
php artisan route:cache
chown www-data:www-data .env
supervisorctl restart modelizm-cloude-horizon || true

echo "S3_IMG_DONE media_url=${MEDIA_URL} prefix=${MEDIA_PREFIX} bucket=${AWS_BUCKET}"
