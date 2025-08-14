FROM php:8.2-cli as base

# Configure workdir
WORKDIR /var/www/html

# Install system dependencies and build libraries
RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    unzip \
    libpq-dev \
    libonig-dev \
    libssl-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    libicu-dev \
    libzip-dev \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && docker-php-ext-install -j$(nproc) \
    pcntl \
    pdo_pgsql \
    pgsql \
    opcache \
    intl \
    zip \
    bcmath \
    soap \
    && pecl install redis xdebug \
    && docker-php-ext-enable redis xdebug\
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && apt-get autoremove -y && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY . .

FROM base as dev

EXPOSE 8000
CMD ["php", "artisan", "migrate"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
#CMD ["composer", "run", "dev"]

FROM base as prod_builder

RUN composer install --no-dev --no-interaction --optimize-autoloader

FROM php:8.2-fpm as production

WORKDIR /var/www/html

# Install only necessary production extensions and Nginx
RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx \
    curl \
    libpq-dev \
    libonig-dev \
    libssl-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    libicu-dev \
    libzip-dev \
    # We install the same extensions as base, but EXCLUDING xdebug \
    && docker-php-ext-install -j$(nproc) \
    pcntl \
    pgsql \
    opcache \
    intl \
    zip \
    bcmath \
    soap \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get autoremove -y && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Copy application files from the base stage
COPY --chown=www-data:www-data . .

# Copy production dependencies from the builder stage
COPY --from=prod_builder --chown=www-data:www-data /var/www/html/vendor/ ./vendor/

# Copy Nginx configuration and entrypoint script
COPY docker/nginx.conf /etc/nginx/sites-enabled/default
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Run Laravel optimization commands
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    php artisan event:cache

# Expose port 80 for Nginx
EXPOSE 80
# Set the entrypoint to start services
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

