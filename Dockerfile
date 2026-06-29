FROM php:8.3-fpm

# Installer les dépendances système et extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    libssl-dev \
    ca-certificates \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définir le dossier de travail
WORKDIR /var/www

# Copier les fichiers du projet
COPY . .

# Installer les dépendances PHP
RUN composer config --global process-timeout 2000 \
    && composer install --no-interaction --optimize-autoloader --prefer-dist --no-progress -vvv || \
       composer install --no-interaction --optimize-autoloader --prefer-source --no-progress
# Permissions pour le stockage Laravel
RUN chmod -R 775 storage bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]