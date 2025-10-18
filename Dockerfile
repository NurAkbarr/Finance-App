# 1. BASE IMAGE: Mulai dari PHP versi 8.3 dengan FPM (FastCGI Process Manager)
# FPM adalah proses PHP yang menerima request dari web server (Nginx)
FROM php:8.3-fpm-alpine

# 2. INSTALASI DEPENDENSI SISTEM
# alpine adalah versi Linux yang ringan. Kita instal Nginx, Supervisor, dan tools CLI
RUN apk add --no-cache \
    nginx \
    supervisor \
    openssl \
    git \
    curl \
    mysql-client \
    bash \
    coreutils 

# 3. INSTALASI EKSTENSI PHP
# Instal ekstensi yang dibutuhkan Laravel (pdo, pdo_mysql, opcache)
RUN docker-php-ext-install pdo pdo_mysql opcache

# 4. INSTALASI COMPOSER
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# 5. WORKDIR: Set direktori kerja di dalam container
WORKDIR /app

# 6. SALIN KONFIGURASI NGINX
# Salin file konfigurasi Nginx dari folder 'docker' lokal ke lokasi konfigurasi Nginx di container
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf

# 7. SALIN KODE APLIKASI
# Salin semua kode project Anda ke direktori kerja container (/app)
COPY . /app

# 8. HAK AKSES (Permissions)
# Berikan hak akses penuh pada direktori storage dan cache yang dibutuhkan Laravel
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# 9. INSTAL DEPENDENSI PHP
# Jalankan composer install untuk menginstal semua vendor (dengan optimasi untuk production)
RUN composer install --no-dev --optimize-autoloader

# 10. PORTS
# Container akan berjalan di port 80
EXPOSE 80

# 11. START COMMAND (CMD)
# Jalankan PHP-FPM (process PHP) dan Nginx (web server) ketika container dimulai
CMD ["sh", "-c", "php-fpm && nginx -g 'daemon off;'"]
