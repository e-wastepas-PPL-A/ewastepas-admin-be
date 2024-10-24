FROM phpswoole/swoole:5.1.3-php8.3
LABEL author="Alfarozy.id"
# Copy composer.lock and composer.json to /var/www
COPY composer.lock composer.json /var/www/

# Set working directory
WORKDIR /var/www

# Install required dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libicu-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    # cron \
    unzip \
    git \
    nano \
    # supervisor \
    libpq-dev \
    librsvg2-bin \
    --no-install-recommends \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install bcmath gd pdo_pgsql pcntl intl
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

## Configuring CRON JOB
# RUN crontab -l | { cat; echo "* * * * * /usr/local/bin/php /var/www/artisan schedule:run >> /var/log/cron.log 2>&1"; } | crontab - \
#     && touch /var/log/cron.log

# Copy existing application directory contents

RUN composer install --ignore-platform-reqs

COPY . /var/www


USER root

EXPOSE 8000

CMD ["php", "artisan", "octane:start", "--server=swoole", "--host=0.0.0.0", "--port=8000"]
