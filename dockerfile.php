
FROM php:8.2-fpm

# Instala dependencias y extensiones PHP necesarias
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev libzip-dev zip unzip git curl nginx supervisor \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip bcmath

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copia todo el código
COPY . .

# Instala dependencias PHP de Laravel/Themosis
RUN composer install --no-dev --optimize-autoloader

# Instala dependencias JS si usas (opcional)
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs \
    && npm install \
    && npm run production

# Configura permisos para storage y bootstrap/cache
RUN chown -R www-data:www-data storage bootstrap/cache htdocs/wp-content/uploads

# Copia configuración nginx y supervisor (debes crear estos archivos)
COPY ./docker/nginx.conf /etc/nginx/nginx.conf
COPY ./docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 80

CMD ["/usr/bin/supervisord", "-n"]
