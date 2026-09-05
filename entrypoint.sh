#!/bin/bash
set -e

# Perbaikan: image php:8.2-apache kadang mengaktifkan lebih dari satu
# modul MPM (mpm_prefork, mpm_event, mpm_worker) sekaligus, padahal
# Apache cuma boleh jalan dengan SATU MPM aktif. Pastikan cuma
# mpm_prefork yang aktif sebelum Apache start.
a2dismod mpm_event 2>/dev/null || true
a2dismod mpm_worker 2>/dev/null || true
a2enmod mpm_prefork 2>/dev/null || true

# Railway memberi tahu port yang harus dipakai lewat env var $PORT saat
# container BERJALAN (bukan saat build) -- makanya penggantian port di
# konfigurasi Apache harus dilakukan di sini, bukan di Dockerfile RUN.
PORT_TO_USE=${PORT:-80}

sed -i "s/80/${PORT_TO_USE}/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

exec apache2-foreground