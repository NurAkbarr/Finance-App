# 1. BASE IMAGE: Menggunakan PHP FPM 8.2 (atau 8.3 jika tersedia)
FROM php:8.2-fpm-alpine

# 2. INSTAL DEPENDENSI SISTEM
# Perintah untuk menginstal sistem tools (git, unzip, dll.)
RUN apk add --no-cache \
    git \
    build-base \
    autoconf \
    curl \
    unzip \
    libzip-dev \
    postgresql-dev \
    libxml2-dev

# 3. INSTAL EKSTENSI PHP KRITIS (PERBAIKAN KRITIS)
# Menginstal intl dan zip yang dibutuhkan oleh Filament dan paket lainnya.
RUN docker-php-ext-install pdo_pgsql intl zip
RUN docker-php-ext-enable pdo_pgsql

# 4. INSTAL COMPOSER
# Menginstal Composer secara global di dalam container
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. ARGUMENTS & ENVIRONMENT
# Menggunakan variabel lingkungan untuk konfigurasi Nginx dan Runtime
ARG APP_ENV=production
ENV APP_ENV=${APP_ENV}
ENV PATH="./vendor/bin:$PATH"

# 6. PENYIAPAN DIRECTORY KERJA
# Mengatur direktori kerja di dalam container
WORKDIR /app

# 7. SALIN KODE APLIKASI
# Menyalin kode aplikasi dan konfigurasi
COPY . /app

# 8. INSTAL DEPENDENSI VENDOR
# Jalankan composer install --no-dev untuk production
RUN composer install --no-dev --optimize-autoloader

# 9. HAK AKSES (Untuk menjalankan aplikasi dengan user yang tidak memiliki root)
# Memberi hak akses yang tepat ke direktori storage dan cache
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache
RUN chmod -R 775 /app/storage /app/bootstrap/cache

# 10. KONFIGURASI NGINX
# Menyalin konfigurasi Nginx yang sudah dibuat
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf

# 11. EXPOSE PORT
EXPOSE 8080

# 12. PERINTAH START
# Perintah untuk menjalankan PHP FPM dan Nginx
CMD sh -c "php-fpm && nginx -g 'daemon off;'"
