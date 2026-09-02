FROM php:8.2-apache

# Install dependencies and extensions
RUN docker-php-ext-install pdo pdo_mysql \
    && apt-get update \
    && apt-get install -y --no-install-recommends default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# Fix MPM conflict - disable ALL MPMs first, then enable only prefork
RUN a2dismod mpm_event mpm_worker mpm_prefork \
    && a2enmod mpm_prefork \
    && a2enmod rewrite

# Setup directory and application files
WORKDIR /var/www/html
COPY . /var/www/html/
RUN chmod +x /var/www/html/entrypoint.sh

# Configure dynamic PORT for Railway
RUN sed -i 's/Listen 80/Listen ${PORT}/g' /etc/apache2/ports.conf \
    && sed -i 's/:80/:${PORT}/g' /etc/apache2/sites-enabled/000-default.conf \
    && echo "Listen ${PORT}" >> /etc/apache2/ports.conf

# PHP configuration
RUN { \
    echo 'display_errors=Off'; \
    echo 'log_errors=On'; \
    echo 'upload_max_filesize=10M'; \
    echo 'post_max_size=10M'; \
  } > /usr/local/etc/php/conf.d/app.ini

EXPOSE 8080
ENV PORT=8080

CMD ["/var/www/html/entrypoint.sh"]