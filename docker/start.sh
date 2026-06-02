#!/bin/sh
set -e

OPCACHE_VALIDATE_TIMESTAMPS="${OPCACHE_VALIDATE_TIMESTAMPS:-0}"
OPCACHE_REVALIDATE_FREQ="${OPCACHE_REVALIDATE_FREQ:-0}"

cat > /usr/local/etc/php/conf.d/opcache-runtime.ini <<EOL
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.validate_timestamps=${OPCACHE_VALIDATE_TIMESTAMPS}
opcache.revalidate_freq=${OPCACHE_REVALIDATE_FREQ}
opcache.fast_shutdown=1
EOL

if [ "$RUN_AS_ROOT" = "true" ]; then
    echo "WARNING: Running as root user (not recommended for production)"
    RUNTIME_USER="root"
    sed -i '/^user /d' /etc/nginx/nginx.conf
    sed -i '1s/^/user root;\n/' /etc/nginx/nginx.conf
else
    echo "Running as non-root user 'www' (recommended)"
    RUNTIME_USER="www"
    sed -i '/^user /d' /etc/nginx/nginx.conf
    sed -i '1s/^/user www;\n/' /etc/nginx/nginx.conf
    chown -R www:www /var/www/html/site-config/data /var/www/html/data /run/nginx /var/log/supervisor 2>/dev/null || true
    mkdir -p /var/run/supervisor && chown www:www /var/run/supervisor
    mkdir -p /var/lib/nginx/tmp /var/lib/nginx/logs /var/log/nginx
    touch /var/log/nginx/error.log /var/log/nginx/access.log /var/log/php-fpm.log
    chown -R www:www /var/lib/nginx /var/log/nginx /var/log/php-fpm.log
    touch /proc/self/fd/2 2>/dev/null || true
fi

cat > /etc/supervisor/conf.d/supervisord.conf <<EOL
[supervisord]
nodaemon=true
logfile=/var/log/supervisor/supervisord.log
pidfile=/var/run/supervisord.pid

[program:php-fpm]
command=/usr/local/sbin/php-fpm -F
autostart=true
autorestart=true
user=$RUNTIME_USER
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0

[program:nginx]
command=/usr/sbin/nginx -g 'daemon off;'
autostart=true
autorestart=true
user=$RUNTIME_USER
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
EOL

mkdir -p /var/www/html/site-config/data /var/www/html/data /var/www/html/backups
chmod 700 /var/www/html/site-config/data
chmod 755 /var/www/html/data
chmod 700 /var/www/html/backups

if [ "$RUNTIME_USER" = "www" ]; then
    chown -R www:www /var/www/html/site-config/data /var/www/html/data /var/www/html/backups
fi

if [ ! -f "/var/www/html/site-config/data/users.json" ]; then
    echo ""
    echo "========================================"
    echo "FIRST TIME SETUP DETECTED!"
    echo "========================================"
    echo ""
    echo "Please navigate to:"
    echo "http://localhost:${WEB_PORT:-8080}/site-config/install.php"
    echo ""
    echo "========================================"
    echo ""
fi

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
