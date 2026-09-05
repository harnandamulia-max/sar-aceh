FROM php:8.2-apache

# Hapus paksa file modul MPM yang sering bikin bentrok di Debian/Ubuntu Apache
RUN rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.* \
    && ln -s /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/ \
    && ln -s /etc/apache2/mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/

# Aktifkan ekstensi PHP yang dibutuhkan (mysqli untuk koneksi database)
RUN docker-php-ext-install mysqli

# Aktifkan mod_rewrite Apache
RUN a2enmod rewrite

# Salin semua file project ke folder web server
COPY . /var/www/html/

# Pastikan Apache mendengarkan di port yang diberikan Railway
ENV PORT=8080
RUN sed -i "s/80/\${PORT}/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

EXPOSE 8080