FROM larueli/php-base-image:7.2

COPY . /var/www/html

USER 0:0

RUN /var/www/html/composer install && chmod -R g+rwx /var/www/html && php bin/console assetic:dump

USER 875864:0
