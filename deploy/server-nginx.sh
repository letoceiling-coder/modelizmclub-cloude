#!/usr/bin/env bash
# Nginx-сайт + SSL для dev-cloude.modelizmclub.ru (не трогает dev.modelizmclub.ru).
set -eu

DOMAIN=dev-cloude.modelizmclub.ru
ROOT=/var/www/modelizmclub-cloude/public
AVAIL=/etc/nginx/sites-available/${DOMAIN}
ENABLED=/etc/nginx/sites-enabled/${DOMAIN}

echo "==== [1] Временный HTTP-конфиг для выпуска сертификата ===="
cat > "$AVAIL" <<NGINX
server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN};
    root ${ROOT};
    index index.php;
    location /.well-known/acme-challenge/ { root /var/www/html; }
    location / { try_files \$uri \$uri/ /index.php?\$query_string; }
    location ~ \.php\$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }
}
NGINX
ln -sf "$AVAIL" "$ENABLED"
nginx -t && systemctl reload nginx

echo "==== [2] Выпуск сертификата Let's Encrypt ===="
if [ ! -d "/etc/letsencrypt/live/${DOMAIN}" ]; then
    certbot certonly --nginx -d "${DOMAIN}" -n --agree-tos \
        --register-unsafely-without-email --keep-until-expiring
else
    echo "  сертификат уже есть"
fi

echo "==== [3] Полный HTTPS-конфиг ===="
cat > "$AVAIL" <<NGINX
map \$http_upgrade \$connection_upgrade_cloude {
    default upgrade;
    ''      close;
}

server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN};
    location /.well-known/acme-challenge/ { root /var/www/html; }
    location / { return 301 https://\$host\$request_uri; }
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name ${DOMAIN};

    root ${ROOT};
    index index.php;
    charset utf-8;
    client_max_body_size 220M;

    ssl_certificate     /etc/letsencrypt/live/${DOMAIN}/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/${DOMAIN}/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_prefer_server_ciphers off;

    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    location /app/ {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade \$http_upgrade;
        proxy_set_header Connection \$connection_upgrade_cloude;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
        proxy_read_timeout 3600s;
    }

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 120s;
    }

    location ~ /\.(?!well-known).* { deny all; }

    access_log /var/log/nginx/modelizm-cloude-access.log;
    error_log  /var/log/nginx/modelizm-cloude-error.log;
}
NGINX
nginx -t && systemctl reload nginx
echo "==== NGINX ГОТОВО ===="
