FROM php:8.2-apache

# Install dependencies and extensions
RUN docker-php-ext-install pdo pdo_mysql \
    && apt-get update \
    && apt-get install -y --no-install-recommends default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# Clean up ALL MPM references and re-link strictly prefork
RUN rm -f /etc/apache2/mods-enabled/mpm_* \
    && ln -s /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/ \
    && ln -s /etc/apache2/mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/ \
    && a2enmod rewrite

# Setup directory and application files
WORKDIR /var/www/html
COPY . /var/www/html/
RUN chmod +x /var/www/html/entrypoint.sh

# Configure dynamic PORT for Railway
RUN sed -i 's/80/${PORT}/g' /etc/apache2/ports.conf \
    && sed -i 's/:80/:${PORT}/g' /etc/apache2/sites-enabled/000-default.conf

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