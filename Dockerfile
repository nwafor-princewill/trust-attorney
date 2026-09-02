FROM php:8.2-apache

# PDO MySQL extension (required by db.php) + mysql client (used by entrypoint.sh to auto-import schema.sql)
RUN docker-php-ext-install pdo pdo_mysql \
    && apt-get update \
    && apt-get install -y --no-install-recommends default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# Apache config: allow .htaccess overrides (not required here, but harmless).
# Force-remove any non-prefork MPM symlinks directly (a2dismod alone wasn't
# reliably clearing this on Railway's build) and enable only mpm_prefork.
RUN rm -f /etc/apache2/mods-enabled/mpm_event.load /etc/apache2/mods-enabled/mpm_event.conf \
           /etc/apache2/mods-enabled/mpm_worker.load /etc/apache2/mods-enabled/mpm_worker.conf \
    && a2enmod mpm_prefork rewrite

# App cod
COPY . /var/www/html/
WORKDIR /var/www/html
RUN chmod +x /var/www/html/entrypoint.sh

# Railway assigns a dynamic $PORT at runtime — point Apache at it
RUN sed -i 's/80/${PORT}/g' /etc/apache2/ports.conf \
    && sed -i 's/:80/:${PORT}/g' /etc/apache2/sites-enabled/000-default.conf

# Reasonable production PHP settings
RUN { \
    echo 'display_errors=Off'; \
    echo 'log_errors=On'; \
    echo 'upload_max_filesize=10M'; \
    echo 'post_max_size=10M'; \
  } > /usr/local/etc/php/conf.d/app.ini

EXPOSE 8080
ENV PORT=8080

CMD ["/var/www/html/entrypoint.sh"]
