# Dockerfile ini memberitahu Railway cara menjalankan project PHP ini.
# Taruh file ini di folder PALING LUAR project (sejajar dengan index.php).

FROM php:8.2-apache

# Aktifkan ekstensi PHP yang dibutuhkan (mysqli untuk koneksi database)
RUN docker-php-ext-install mysqli

# Aktifkan mod_rewrite Apache (jaga-jaga kalau nanti butuh URL rewriting)
RUN a2enmod rewrite

# Salin semua file project ke folder web server
COPY . /var/www/html/

# Pastikan Apache mendengarkan di port yang diberikan Railway
ENV PORT=8080
RUN sed -i "s/80/\${PORT}/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

EXPOSE 8080
