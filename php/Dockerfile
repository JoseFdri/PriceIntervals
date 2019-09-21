FROM php:7.2-fpm

WORKDIR /tmp

#INSTALL COMPOSER
RUN php -r "readfile('https://getcomposer.org/installer');" > composer-setup.php
RUN php composer-setup.php --version=1.3.2
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/local/bin/composer
RUN chmod a+x /usr/local/bin/composer

# Use the default development configuration
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

RUN apt-get update && apt-get install -y libgmp-dev

RUN docker-php-ext-install gmp mysqli pdo_mysql

RUN apt-get install -y libmcrypt-dev
RUN pecl install mcrypt-1.0.1 && docker-php-ext-enable mcrypt

RUN apt-get install -y zip unzip
RUN apt-get install -y git

WORKDIR /var/www/html/app