FROM php:8.3-apache
RUN docker-php-ext-install pdo_mysql && a2enmod rewrite headers
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html
RUN sed -i 's/80/10000/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf
EXPOSE 10000
CMD ["apache2-foreground"]
