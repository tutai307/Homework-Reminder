FROM php:8.2-apache

# Cài extension cho Laravel
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip unzip git curl \
    libpq-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring bcmath gd

# Enable rewrite
RUN a2enmod rewrite

# Apache config cho Laravel
RUN printf "<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>\n" > /etc/apache2/sites-available/000-default.conf

# Cài composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

# Quyền thư mục
RUN chown -R www-data:www-data storage bootstrap/cache

# Cài package
RUN composer install --no-dev --optimize-autoloader

# OPTIONAL cho Render Free
RUN php artisan migrate --force || true
RUN php artisan storage:link || true

EXPOSE 80
