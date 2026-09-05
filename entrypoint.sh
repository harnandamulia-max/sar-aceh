#!/bin/bash
set -e

# Railway memberi tahu port yang harus dipakai lewat env var $PORT saat
# container BERJALAN (bukan saat build) -- makanya penggantian port di
# konfigurasi Apache harus dilakukan di sini, bukan di Dockerfile RUN.
PORT_TO_USE=${PORT:-80}

sed -i "s/80/${PORT_TO_USE}/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

exec apache2-foreground