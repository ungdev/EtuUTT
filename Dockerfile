FROM larueli/php-base-image:7.1

COPY . /var/www/html

RUN /var/www/html/composer install
