#!/bin/bash
set -e

# Laravel key生成（すでにあるならスキップ）
if [ ! -f .env ]; then
  cp .env.example .env
  php artisan key:generate
fi

echo ">>> [entrypoint] Laravel init start"
echo "mysqladmin ping -h$DB_HOST -P${DB_PORT:-3306} -u$DB_USERNAME -p$DB_PASSWORD --silent"

# .env をシェル変数へ読み込む
export $(grep -v '^#' /var/www/.env | xargs)
# DB待ち
echo "Waiting for MySQL to be ready..."
echo "mysqladmin ping -h$DB_HOST -P${DB_PORT:-3306} -u$DB_USERNAME -p$DB_PASSWORD --silent"
until nc -z "$DB_HOST" "${DB_PORT:-3306}"; do
  sleep 1
done
echo "MySQL is up - continuing"

# vendor が無ければインストール（1 回目だけ）
if [ ! -d vendor ]; then
  echo "Running composer install (dev)..."
  composer install --no-interaction --prefer-dist --no-scripts
fi

php artisan config:clear

if php artisan migrate:status | grep -q "Yes"; then
  echo ">>> Migration already applied. Skipping migrate & seed."
else
  echo ">>> Running initial migration & seeding..."
  php artisan migrate --force
  php artisan db:seed --force
fi

php artisan cache:clear
php artisan route:clear

echo ">>> [entrypoint] Laravel init done"

# サーバを起動（例：PHP-FPM）
exec php-fpm