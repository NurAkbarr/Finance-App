# STAGE 1: Build Image (untuk mengkompilasi aset Node.js)
FROM node:18-alpine AS node_builder

# Menginstal Node.js dan NPM
WORKDIR /app
# Copy package metadata (package-lock.json optional)
COPY package*.json ./
# Instal dependensi Node.js
RUN npm install
# Salin konfigurasi Vite/Tailwind (jika ada) sebelum build
COPY vite.config.js ./
COPY tailwind.config.js ./

# Salin sumber daya frontend
COPY resources/ /app/resources

# Kompilasi aset frontend
RUN npm run build


# STAGE 2: Production Image (untuk menjalankan PHP)
FROM php:8.2-fpm-alpine

# 1. INSTAL DEPENDENSI SISTEM
# Perintah untuk menginstal sistem tools (git, unzip, dll.)
RUN apk add --no-cache \
    git \
    build-base \
    autoconf \
    curl \
    unzip \
    libzip-dev \
    postgresql-dev \
    libxml2-dev \
    nodejs \
    npm

# 2. INSTAL EKSTENSI PHP KRITIS
RUN docker-php-ext-install pdo_pgsql intl zip
RUN docker-php-ext-enable pdo_pgsql

# 3. INSTAL COMPOSER
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. ARGUMENTS & ENVIRONMENT
ARG APP_ENV=production
ENV APP_ENV=${APP_ENV}
ENV PATH="./vendor/bin:$PATH"

# 5. PENYIAPAN DIRECTORY KERJA
WORKDIR /app

# 6. SALIN KODE APLIKASI
# Menyalin kode aplikasi dan konfigurasi
COPY . /app

# 7. SALIN ASET YANG DIKOMPILASI (KRITIS)
# Menyalin file yang dikompilasi dari Node Stage
COPY --from=node_builder /app/public/build /app/public/build

# 8. INSTAL DEPENDENSI VENDOR
RUN composer install --no-dev --optimize-autoloader

# 9. HAK AKSES
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache
RUN chmod -R 775 /app/storage /app/bootstrap/cache

# 10. KONFIGURASI NGINX
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf

# 11. EXPOSE PORT
EXPOSE 8080

# 12. PERINTAH START
CMD sh -c "php-fpm && nginx -g 'daemon off;'"
