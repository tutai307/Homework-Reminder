FROM php:8.2-apache

# Cài extension cần cho Laravel
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip unzip git curl \
    libpq-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring bcmath gd

# Enable apache rewrite
RUN a2enmod rewrite

# Đổi DocumentRoot sang public/
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf

# Cài composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

# Quyền thư mục
RUN chown -R www-data:www-data storage bootstrap/cache

# Cài package PHP
RUN composer install --no-dev --optimize-autoloader
RUN php artisan migrate --force || true
RUN php artisan storage:link || true

EXPOSE 80
