FROM php:8.3-apache

RUN apachectl -M || true

RUN ls -la /etc/apache2/mods-enabled/ || true

COPY . /var/www/html/

RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

CMD ["bash", "-c", "apachectl -M && apache2-foreground"]