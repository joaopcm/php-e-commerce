FROM php:7.2-apache

LABEL maintener 'João Pedro da Cruz Melo <joao.pedro6532@gmail.com>'

ADD . /var/www/html/

RUN a2enmod rewrite
RUN mkdir uploads
RUN chmod 777 uploads
RUN apt-get update && apt-get install -y wget mysql-client
RUN docker-php-ext-install pdo_mysql
RUN wget https://getcomposer.org/download/1.6.5/composer.phar
RUN php composer.phar install