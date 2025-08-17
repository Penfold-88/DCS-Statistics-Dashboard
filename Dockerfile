# Multi-stage build for smaller image
ARG PHP_VERSION=8.2
FROM php:${PHP_VERSION}-fpm-alpine AS base

# Install required PHP extensions and tools
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    zip \
    unzip \
    libzip-dev \
    oniguruma-dev \
    && docker-php-ext-install -j$(nproc) \
        zip \
        bcmath \
        mbstring \
        opcache \
        pdo \
        pdo_mysql \
    && rm -rf /tmp/* /var/tmp/*

# Create non-root user and directories
RUN addgroup -g 1000 -S www && \
    adduser -u 1000 -S www -G www && \
    mkdir -p /var/www/html /run/nginx /var/log/supervisor /etc/supervisor/conf.d && \
    mkdir -p /var/lib/nginx/tmp /var/lib/nginx/logs /var/log/nginx && \
    touch /var/log/php-fpm.log /var/log/nginx/error.log /var/log/nginx/access.log && \
    chown -R www:www /var/www /run/nginx /var/log/supervisor /var/lib/nginx /var/log/php-fpm.log /var/log/nginx

# Set the working directory
WORKDIR /var/www/html

# Copy the web application files
COPY --chown=www:www dcs-stats/ /var/www/html/

# Create required directories in the container with proper permissions
RUN mkdir -p /var/www/html/site-config/data \
    /var/www/html/data \
    /var/www/html/backups \
    && chmod 777 /var/www/html/site-config/data \
    && chmod 755 /var/www/html/data \
    && chmod 755 /var/www/html/backups \
    && chown -R www:www /var/www/html/site-config/data \
    && chown -R www:www /var/www/html/data \
    && chown -R www:www /var/www/html/backups

# Copy nginx configuration
COPY --chown=root:root <<'EOF' /etc/nginx/nginx.conf
# User will be set dynamically - comment out for now
# user www;
worker_processes auto;
error_log /var/log/nginx/error.log warn;
pid /run/nginx/nginx.pid;

events {
    worker_connections 1024;
}

http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;
    
    sendfile on;
    tcp_nopush on;
    keepalive_timeout 65;
    gzip on;
    
    # Security headers
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    
    # Hide nginx version
    server_tokens off;
    
    server {
        listen 80;
        server_name localhost;
        root /var/www/html;
        index index.php index.html;
        
        # Deny access to hidden files
        location ~ /\. {
            deny all;
        }
        
        # Deny access to data directories
        location ~ ^/(data|site-config/data)/ {
            deny all;
        }
        
        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }
        
        location ~ \.php$ {
            fastcgi_pass 127.0.0.1:9000;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include fastcgi_params;
            fastcgi_read_timeout 300;
        }
        
        # Static file caching
        location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ {
            expires 1y;
            add_header Cache-Control "public, immutable";
        }
    }
}
EOF

# PHP-FPM configuration
RUN { \
        echo '[global]'; \
        echo 'error_log = /var/log/php-fpm.log'; \
        echo 'daemonize = no'; \
        echo '[www]'; \
        echo 'user = www'; \
        echo 'group = www'; \
        echo 'listen = 127.0.0.1:9000'; \
        echo 'listen.owner = www'; \
        echo 'listen.group = www'; \
        echo 'pm = dynamic'; \
        echo 'pm.max_children = 50'; \
        echo 'pm.start_servers = 5'; \
        echo 'pm.min_spare_servers = 5'; \
        echo 'pm.max_spare_servers = 35'; \
        echo 'catch_workers_output = yes'; \
        echo 'access.log = /var/log/php-fpm-access.log'; \
        echo 'php_admin_value[error_log] = /var/log/php-fpm.log'; \
        echo 'php_admin_flag[log_errors] = on'; \
    } > /usr/local/etc/php-fpm.d/www.conf && \
    touch /var/log/php-fpm-access.log && \
    chown www:www /var/log/php-fpm-access.log

# Create startup script that handles user switching
COPY --chown=root:root <<'EOF' /usr/local/bin/start.sh
#!/bin/sh
set -e

# Detect if running on Windows (Docker Desktop)
IS_WINDOWS_HOST=false
if [ -f /proc/version ] && grep -qi microsoft /proc/version; then
    IS_WINDOWS_HOST=true
    echo "Detected Windows host (WSL2 backend)"
fi

# Determine which user to run as
if [ "$RUN_AS_ROOT" = "true" ] || [ "$IS_WINDOWS_HOST" = "true" ]; then
    if [ "$IS_WINDOWS_HOST" = "true" ]; then
        echo "Windows host detected - running as root for volume compatibility"
    else
        echo "WARNING: Running as root user (not recommended for production)"
    fi
    RUNTIME_USER="root"
    # Update nginx to run as root (remove existing user directive first)
    sed -i '/^user /d' /etc/nginx/nginx.conf
    sed -i '1s/^/user root;\n/' /etc/nginx/nginx.conf
else
    echo "Running as non-root user 'www' (recommended)"
    RUNTIME_USER="www"
    # Update nginx to run as www (remove existing user directive first)
    sed -i '/^user /d' /etc/nginx/nginx.conf
    sed -i '1s/^/user www;\n/' /etc/nginx/nginx.conf
    # Ensure directories are writable by www user
    chmod 777 /var/www/html/site-config/data 2>/dev/null || true
    chown -R www:www /var/www/html/site-config/data /var/www/html/data /run/nginx /var/log/supervisor 2>/dev/null || true
    # Create pid directory
    mkdir -p /var/run/supervisor && chown www:www /var/run/supervisor
    # Fix nginx directories
    mkdir -p /var/lib/nginx/tmp /var/lib/nginx/logs
    chown -R www:www /var/lib/nginx
    # Fix PHP-FPM log permissions
    touch /proc/self/fd/2 2>/dev/null || true
fi

# Create supervisor config
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

# Ensure directories exist (in case of bind mount)
mkdir -p /var/www/html/site-config/data /var/www/html/data /var/www/html/backups
chmod 777 /var/www/html/site-config/data
chmod 755 /var/www/html/data /var/www/html/backups

# Set ownership based on runtime user
if [ "$RUNTIME_USER" = "www" ]; then
    chown -R www:www /var/www/html/site-config/data /var/www/html/data /var/www/html/backups
else
    # Even as root, ensure write permissions
    chmod 777 /var/www/html/site-config/data
fi

# Check if installation is needed
if [ ! -f "/var/www/html/site-config/data/users.json" ]; then
    echo ""
    echo "========================================"
    echo "🚀 FIRST TIME SETUP DETECTED!"
    echo "========================================"
    echo ""
    echo "Please navigate to:"
    echo "http://localhost:${WEB_PORT:-9080}/site-config/install.php"
    echo ""
    echo "========================================"
    echo ""
fi

# Start supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
EOF

RUN chmod +x /usr/local/bin/start.sh

# Expose port 80
EXPOSE 80

# Volumes are handled by docker-compose.yml bind mount

# Create a health check script
RUN echo '<?php http_response_code(200); echo "OK"; ?>' > /var/www/html/health-check.php && \
    chown www:www /var/www/html/health-check.php

# Copy custom PHP configuration
RUN { \
        echo 'expose_php = Off'; \
        echo 'max_execution_time = 300'; \
        echo 'max_input_time = 300'; \
        echo 'memory_limit = 256M'; \
        echo 'post_max_size = 50M'; \
        echo 'upload_max_filesize = 50M'; \
        echo 'max_input_vars = 1000'; \
        echo 'date.timezone = UTC'; \
        echo 'session.cookie_httponly = 1'; \
        echo 'session.use_strict_mode = 1'; \
        echo 'session.cookie_samesite = Strict'; \
        echo 'session.gc_maxlifetime = 1440'; \
    } > /usr/local/etc/php/conf.d/dcs-stats.ini

# OPcache configuration for production
RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.interned_strings_buffer=8'; \
        echo 'opcache.max_accelerated_files=4000'; \
        echo 'opcache.revalidate_freq=2'; \
        echo 'opcache.fast_shutdown=1'; \
    } > /usr/local/etc/php/conf.d/opcache-recommended.ini

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/health-check.php || exit 1

# Don't switch user here - let the startup script handle it
# This allows the container to start as root and drop privileges if needed

# Start using our custom script
CMD ["/usr/local/bin/start.sh"]