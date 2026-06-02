# Multi-stage build for the DCS Statistics Dashboard image
ARG PHP_VERSION=8.2
FROM php:${PHP_VERSION}-fpm-alpine AS base

# Install required PHP extensions and tools
RUN apk add --no-cache \
    ca-certificates \
    nginx \
    supervisor \
    curl \
    curl-dev \
    git \
    openssl \
    zip \
    unzip \
    libzip-dev \
    oniguruma-dev \
    && update-ca-certificates \
    && docker-php-ext-install -j$(nproc) \
        curl \
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
    touch /var/log/php-fpm.log /var/log/php-fpm.access.log /var/log/nginx/error.log /var/log/nginx/access.log && \
    chown -R www:www /var/www /run/nginx /var/log/supervisor /var/lib/nginx /var/log/nginx /var/log/php-fpm.log /var/log/php-fpm.access.log

# Set the working directory
WORKDIR /var/www/html

# Copy the web application files
COPY --chown=www:www dcs-stats/ /var/www/html/

# Create required directories in the container
RUN mkdir -p /var/www/html/site-config/data \
    /var/www/html/data \
    /var/www/html/backups \
    && chmod 700 /var/www/html/site-config/data \
    && chmod 755 /var/www/html/data \
    && chmod 700 /var/www/html/backups \
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

        # Explicitly deny Git metadata even if hidden-file handling changes.
        location ~ (^|/)\.git(/|$) {
            deny all;
        }
        
        # Deny access to sensitive runtime directories
        location ~ ^/(data|backups|site-config/data|site-config/theme_backups)/ {
            deny all;
        }

        # Mirror dcs-stats/.htaccess intent: deny direct access to log, SQL,
        # and JSON files. .htaccess is silently ignored under nginx, so the
        # API key in api_config.json would otherwise be web-fetchable.
        # Two JSON files JS callers need stay accessible via exact-match
        # locations (higher priority than the regex deny below).
        location = /server_data.json { try_files $uri =404; }
        location = /mission_data.json { try_files $uri =404; }
        location ~ \.(log|sql|json)$ {
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
RUN sed -i 's|^;\?error_log.*|error_log = /var/log/php-fpm.log|' /usr/local/etc/php-fpm.conf && \
    if [ -f /usr/local/etc/php-fpm.d/docker.conf ]; then \
        sed -i 's|^error_log = .*|error_log = /var/log/php-fpm.log|' /usr/local/etc/php-fpm.d/docker.conf; \
        sed -i 's|^access.log = .*|access.log = /var/log/php-fpm.access.log|' /usr/local/etc/php-fpm.d/docker.conf; \
    fi

RUN { \
        echo '[www]'; \
        echo 'user = www'; \
        echo 'group = www'; \
        echo 'listen = 127.0.0.1:9000'; \
        echo 'pm = dynamic'; \
        echo 'pm.max_children = 50'; \
        echo 'pm.start_servers = 5'; \
        echo 'pm.min_spare_servers = 5'; \
        echo 'pm.max_spare_servers = 35'; \
        echo 'catch_workers_output = yes'; \
        echo 'php_admin_value[error_log] = /var/log/php-fpm.log'; \
        echo 'php_admin_flag[log_errors] = on'; \
    } > /usr/local/etc/php-fpm.d/www.conf

# Copy startup script that handles user switching
COPY --chown=root:root docker/start.sh /usr/local/bin/start.sh
RUN sed -i 's/\r$//' /usr/local/bin/start.sh && chmod +x /usr/local/bin/start.sh

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

# OPcache defaults are hardened at container startup by /usr/local/bin/start.sh.
RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.interned_strings_buffer=8'; \
        echo 'opcache.max_accelerated_files=4000'; \
        echo 'opcache.validate_timestamps=0'; \
        echo 'opcache.revalidate_freq=0'; \
        echo 'opcache.fast_shutdown=1'; \
    } > /usr/local/etc/php/conf.d/opcache-recommended.ini

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/health-check.php || exit 1

# Don't switch user here - let the startup script handle it
# This allows the container to start as root and drop privileges if needed

# Start using our custom script
CMD ["/usr/local/bin/start.sh"]
