#!/usr/bin/env bash
# Supervisor: Horizon (очереди), Reverb (WebSocket), Scheduler — для dev-cloude.
set -eu

APP=/var/www/modelizmclub-cloude
mkdir -p /var/log/supervisor

cat > /etc/supervisor/conf.d/modelizm-cloude-horizon.conf <<CONF
[program:modelizm-cloude-horizon]
process_name=%(program_name)s
command=php ${APP}/artisan horizon
directory=${APP}
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/supervisor/modelizm-cloude-horizon.log
stopwaitsecs=3600
stopsignal=TERM
CONF

cat > /etc/supervisor/conf.d/modelizm-cloude-reverb.conf <<CONF
[program:modelizm-cloude-reverb]
process_name=%(program_name)s
command=php ${APP}/artisan reverb:start --host=0.0.0.0 --port=8080
directory=${APP}
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/supervisor/modelizm-cloude-reverb.log
stopwaitsecs=10
CONF

cat > /etc/supervisor/conf.d/modelizm-cloude-scheduler.conf <<CONF
[program:modelizm-cloude-scheduler]
process_name=%(program_name)s
command=php ${APP}/artisan schedule:work
directory=${APP}
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/supervisor/modelizm-cloude-scheduler.log
CONF

supervisorctl reread
supervisorctl update
sleep 3
supervisorctl status | grep modelizm-cloude || true
