FROM php:8.2-fpm

# Instalar extensiones del sistema y PHP
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev zip curl \
    libonig-dev libxml2-dev \
    libpng-dev libjpeg-dev libfreetype6-dev libwebp-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

# Copiar entrypoint (lo busca en el contexto de build = api-clinica/)
COPY docker-entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# El código fuente llega via volumen en docker-compose.
# El entrypoint instala vendor/ y ejecuta migraciones al inicio.

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
