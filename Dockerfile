FROM php:7.4-apache

EXPOSE 80

RUN apt-get update && apt-get -y upgrade && apt-get install -y ntp zip git curl libonig-dev \
	&& docker-php-ext-install -j$(nproc) opcache mysqli mbstring \
	&& curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
	&& echo Europe/Rome > /etc/timezone && ln -sf /usr/share/zoneinfo/Europe/Rome /etc/localtime && dpkg-reconfigure -f noninteractive tzdata

RUN  printf "date.timezone = Europe/Rome" > /usr/local/etc/php/php.ini

COPY app /var/www/html
RUN chown -R www-data:www-data /var/www
