FROM php:8.2-apache

# Install required PHP extensions & mysql client
RUN docker-php-ext-install pdo pdo_mysql \
    && apt-get update \
    && apt-get install -y --no-install-recommends default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# Fix MPM configuration:
# 1. Disable event and worker modules cleanly
# 2. Force remove all MPM load files from mods-enabled
# 3. Create a single, explicit symlink for mpm_prefork
RUN a2dismod mpm_event mpm_worker || true \
    && rm -f /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf \
    && ln -s /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load \
    && ln -s /etc/apache2/mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf \
    && a2enmod rewrite

# Application code setup
WORKDIR /var/www/html
COPY . /var/www/html/
RUN chmod +x /var/www/html/entrypoint.sh

# Configure dynamic port binding for Railway
RUN sed -i 's/80/${PORT}/g' /etc/apache2/ports.conf \
    && sed -i 's/:80/:${PORT}/g' /etc/apache2/sites-enabled/000-default.conf

# Production PHP settings
RUN { \
    echo 'display_errors=Off'; \
    echo 'log_errors=On'; \
    echo 'upload_max_filesize=10M'; \
    echo 'post_max_size=10M'; \
  } > /usr/local/etc/php/conf.d/app.ini

EXPOSE 8080
ENV PORT=8080

CMD ["/var/www/html/entrypoint.sh"]