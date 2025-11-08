# 1) build stage – Composer 
FROM composer:2.8 AS composer_stage

WORKDIR /app
# lock ファイルだけ先にコピーしてキャッシュを最大化
COPY ./src/composer.json ./src/composer.lock ./
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-scripts

# 2) runtime stage – PHP + Alpine 
FROM php:8.2-fpm-alpine
# システム依存パッケージ
RUN apk add --no-cache \
    bash \
    git \
    curl \
    zip \
    unzip \
    mysql-client \ 
    libzip-dev \
    libxml2-dev \ 
    oniguruma-dev \
    icu-dev \
    zlib-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    build-base \
 && rm -rf /var/cache/apk/*

# PHP拡張
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    xml \
    ctype \
    zip \
    bcmath \
    intl

# PHP 拡張 
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install gd

# Composerインストール
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 作業ディレクトリ
WORKDIR /var/www
COPY --from=composer_stage /app/vendor ./
COPY ./src ./

RUN chown -R www-data:www-data storage bootstrap/cache
RUN chmod -R 755 storage bootstrap/cache

COPY ./entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh
# Entrypoint に差し替える
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]