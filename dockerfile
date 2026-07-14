FROM php:8.2-apache

# Sposta il contenuto della sotto-cartella nella root principale di Apache
RUN mv /var/www/html/circolo-micologico-php/* /var/www/html/ || true

# Dai i permessi corretti ad Apache per leggere i file
RUN chown -R www-data:www-data /var/www/html
