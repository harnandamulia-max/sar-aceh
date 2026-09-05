FROM php:8.2-cli

# Ekstensi PHP yang dibutuhkan (mysqli untuk koneksi database)
RUN docker-php-ext-install mysqli

WORKDIR /var/www/html
COPY . /var/www/html/

EXPOSE 80

# Pakai PHP built-in server, bukan Apache. Aplikasi ini tidak pakai
# .htaccess/mod_rewrite, jadi Apache cuma nambah kerumitan (dan sumber
# masalah "More than one MPM loaded" yang tidak kunjung ketemu akar
# penyebabnya). PHP built-in server jauh lebih sederhana dan cukup
# untuk skala aplikasi kalkulator kecil seperti ini.
# $PORT disediakan Railway saat container berjalan.
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-80} -t /var/www/html"]