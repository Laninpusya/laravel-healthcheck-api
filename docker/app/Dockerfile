FROM php:8.4-fpm-alpine

WORKDIR /var/www

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apk add --no-cache $PHPIZE_DEPS git netcat-openbsd unzip \
    && docker-php-ext-install bcmath pdo_mysql \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --optimize-autoloader \
    --prefer-dist

COPY . .
COPY docker/app/entrypoint.sh /usr/local/bin/entrypoint

RUN chmod +x /usr/local/bin/entrypoint \
    && rm -f /var/www/bootstrap/cache/packages.php /var/www/bootstrap/cache/services.php \
    && composer dump-autoload --optimize --no-dev --no-scripts \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

ENTRYPOINT ["entrypoint"]
CMD ["php-fpm"]
