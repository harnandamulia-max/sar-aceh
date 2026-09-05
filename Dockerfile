FROM php:8.2-apache

# Ekstensi PHP yang dibutuhkan (mysqli untuk koneksi database)
RUN docker-php-ext-install mysqli

# Aktifkan mod_rewrite (jaga-jaga, tidak wajib untuk app ini tapi umum dipakai)
RUN a2enmod rewrite

# Perbaikan "More than one MPM loaded": hapus paksa symlink modul MPM
# lain (event/worker) dan pastikan cuma mpm_prefork yang aktif.
# Dilakukan langsung manipulasi symlink (bukan lewat a2enmod/a2dismod)
# karena cara itu ternyata tidak konsisten berhasil di beberapa base image.
RUN rm -f /etc/apache2/mods-enabled/mpm_event.load \
          /etc/apache2/mods-enabled/mpm_event.conf \
          /etc/apache2/mods-enabled/mpm_worker.load \
          /etc/apache2/mods-enabled/mpm_worker.conf && \
    ln -sf /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load && \
    ln -sf /etc/apache2/mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf

# Salin semua file project ke folder web root Apache
COPY . /var/www/html/

# Skrip yang menyesuaikan port Apache ke $PORT dari Railway SAAT container
# dijalankan (bukan saat build).
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

CMD ["/entrypoint.sh"]