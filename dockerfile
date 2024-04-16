FROM php:7.1
ARG UID
ARG GID

USER root

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --version=1.10.0

RUN addgroup --gid $GID user
RUN adduser --disabled-password --gecos '' --uid $UID --gid $GID user
RUN docker-php-ext-install pdo_mysql
RUN apt-get update && apt-get install -y git
RUN apt-get install -y \
    libzip-dev \
    zip \
  && docker-php-ext-install zip
  
USER user

EXPOSE 85
WORKDIR /var/www/html/app/web
COPY . /var/www/html/app
#RUN composer install
CMD ["php", "-S", "0.0.0.0:85", "-t", "/var/www/html/app/web"]