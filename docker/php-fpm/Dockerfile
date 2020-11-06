FROM php:7.4-fpm-alpine

COPY wait-for-it.sh /usr/bin/wait-for-it
RUN chmod +x /usr/bin/wait-for-it

RUN apk --update --no-cache add git postgresql-dev

RUN docker-php-ext-install pdo pdo_pgsql

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ADD php.ini /etc/php/conf.d/
ADD php.ini /etc/php/cli/conf.d/
ADD php-fpm.conf /etc/php/php-fpm.d/
#ADD xdebug.ini  /etc/php/conf.d/

WORKDIR /var/www/symfony

CMD composer install ; wait-for-it db:5432 -- bin/console doctrine:migrations:migrate ;  php-fpm

EXPOSE 9000

CMD ["php-fpm", "-F"]
