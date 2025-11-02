FROM php:8.1-fpm

# Instala dependências do sistema
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libzip-dev \
    libonig-dev \
    libxml2-dev

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Extensões PHP padrão
RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd

# Instala PCOV via PECL e habilita
RUN pecl install pcov \
    && docker-php-ext-enable pcov

# Opcional: configuração básica do PCOV
RUN echo "pcov.enabled=1\npcov.directory=/var/www\npcov.exclude=\"~(vendor|tests)~\"" > /usr/local/etc/php/conf.d/pcov.ini

# Instala o Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copia o código
COPY . /var/www
WORKDIR /var/www

# Instala dependências do projeto
RUN composer install

# Permissões de pasta
RUN chown -R www-data:www-data /var/www
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
