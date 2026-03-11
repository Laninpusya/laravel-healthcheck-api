#!/bin/sh
set -e

cd /var/www

until nc -z "$DB_HOST" "$DB_PORT"; do
    echo "Waiting for MySQL at $DB_HOST:$DB_PORT..."
    sleep 2
done

until nc -z "$REDIS_HOST" "$REDIS_PORT"; do
    echo "Waiting for Redis at $REDIS_HOST:$REDIS_PORT..."
    sleep 2
done

# Локальные cache-файлы могут содержать dev-провайдеры, которых нет в production image.
rm -f bootstrap/cache/packages.php bootstrap/cache/services.php

# Генерируем discovery-артефакты уже в собранном контейнере с актуальным env.
php artisan package:discover --ansi
php artisan migrate --force --ansi

exec "$@"
