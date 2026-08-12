FROM php:8.3-cli

WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    zip \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    cron \
    && docker-php-ext-install \
        pdo_mysql \
        zip \
        intl \
        bcmath \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . /var/www

RUN chown -R www-data:www-data /var/www

COPY docker/scheduler/crontab /etc/cron.d/laravel-scheduler

RUN chmod 0644 /etc/cron.d/laravel-scheduler \
    && crontab /etc/cron.d/laravel-scheduler

CMD ["cron", "-f"]
