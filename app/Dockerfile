FROM php:7.2-apache

LABEL maintener 'João Pedro da Cruz Melo <joao.pedro6532@gmail.com>'

ADD . /var/www/html/

RUN a2enmod rewrite
RUN apt-get update && apt-get install -y wget mysql-client libwebp-dev libjpeg62-turbo-dev libpng-dev libxpm-dev libfreetype6-dev
RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-configure gd --with-gd --with-webp-dir --with-jpeg-dir \
    --with-png-dir --with-zlib-dir --with-xpm-dir --with-freetype-dir \
    --enable-gd-native-ttf
RUN docker-php-ext-install gd
RUN wget https://getcomposer.org/download/1.6.5/composer.phar
RUN php composer.phar install