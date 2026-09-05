FROM php:8.2-apache

# Ekstensi PHP yang dibutuhkan (mysqli untuk koneksi database)
RUN docker-php-ext-install mysqli

# Aktifkan mod_rewrite (jaga-jaga, tidak wajib untuk app ini tapi umum dipakai)
RUN a2enmod rewrite

# Salin semua file project ke folder web root Apache
COPY . /var/www/html/

# Skrip yang menyesuaikan port Apache ke $PORT dari Railway SAAT container
# dijalankan (bukan saat build -- itu bug di versi sebelumnya).
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

CMD ["/entrypoint.sh"]