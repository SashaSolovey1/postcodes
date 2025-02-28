FROM php:8.2-cli

WORKDIR /var/www

RUN apt-get update && apt-get install -y unzip libzip-dev libpq-dev cron \
    && docker-php-ext-install pdo pdo_mysql

RUN apt-get update && apt-get install -y \
    zip unzip libzip-dev \
    && docker-php-ext-install zip

RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-install gd iconv


# Встановлення Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Копіюємо crontab файл
COPY docker/crontab /etc/cron.d/import-cron

# Встановлюємо права і додаємо в crontab
RUN chmod 0644 /etc/cron.d/import-cron \
    && crontab /etc/cron.d/import-cron

# Створюємо файл логів
RUN touch /var/log/cron.log

# Запуск cron у фоні та PHP сервера
CMD service cron start && php -S 0.0.0.0:8080 -t public
