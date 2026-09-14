# Aturan Pengembangan: Panduan Penerapan & Infrastruktur (*Deployment*)

Dokumen ini merumuskan konfigurasi infrastruktur, orkestrasi kontainer Docker, pipeline CI/CD, dan proses rilis build aplikasi mobile Flutter Koperasi Digital ke lingkungan produksi.

---

## 1. Arsitektur Kontainerisasi (*Docker Compose*)

Lingkungan server berjalan di atas kontainer Docker terisolasi:

```yaml
version: '3.8'

services:
  # 1. Reverse Proxy & Web Server
  webserver:
    image: nginx:alpine
    container_name: kopkar_nginx
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./backend:/var/www/html
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
      - ./docker/ssl:/etc/nginx/ssl
    depends_on:
      - app

  # 2. Backend API Laravel 13 (PHP 8.3/8.4 FPM)
  app:
    build:
      context: ./backend
      dockerfile: Dockerfile
    container_name: kopkar_app
    restart: unless-stopped
    environment:
      APP_ENV: production
      DB_CONNECTION: pgsql
      DB_HOST: postgres
      REDIS_HOST: redis
    volumes:
      - ./backend:/var/www/html
    depends_on:
      - postgres
      - redis

  # 3. Database Utama PostgreSQL 16
  postgres:
    image: postgres:16-alpine
    container_name: kopkar_postgres
    restart: unless-stopped
    environment:
      POSTGRES_DB: koperasi_digital
      POSTGRES_USER: kopkar_user
      POSTGRES_PASSWORD: ${DB_PASSWORD}
    volumes:
      - pgdata:/var/lib/postgresql/data
    ports:
      - "5432:5432"

  # 4. Cache & Queue Engine Redis
  redis:
    image: redis:7-alpine
    container_name: kopkar_redis
    restart: unless-stopped
    volumes:
      - redisdata:/data

  # 5. Background Queue Worker (Horizon)
  queue_worker:
    build:
      context: ./backend
      dockerfile: Dockerfile
    container_name: kopkar_worker
    restart: unless-stopped
    command: php artisan horizon
    depends_on:
      - app
      - redis

volumes:
  pgdata:
  redisdata:
```

---

## 2. Prosedur Rilis Produksi Backend (*Zero-Downtime Deployment*)

Setiap kali melakukan pembaruan di server produksi:
```bash
# 1. Masuk ke direktori proyek
cd /var/www/koperasi-digital

# 2. Ambil perubahan kode terbaru
git pull origin main

# 3. Instal dependensi produksi
composer install --no-dev --optimize-autoloader

# 4. Jalankan migrasi database
php artisan migrate --force

# 5. Bersihkan & optimalkan cache konfigurasi Laravel
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Muat ulang worker antrian Horizon
php artisan horizon:terminate

# 7. Restart PHP-FPM
sudo systemctl reload php8.3-fpm
```

---

## 3. Kompilasi & Distribusi Aplikasi Mobile Flutter

### 1. Build Android (Google Play Store / Internal App Bundle)
Gunakan opsi obfuscation untuk melindungi kode biner dari dekompilasi pihak luar:
```bash
flutter build appbundle --release \
  --obfuscate \
  --split-debug-info=./build/app/outputs/symbols
```

### 2. Build iOS (Apple App Store / TestFlight)
```bash
flutter build ipa --release \
  --obfuscate \
  --split-debug-info=./build/ios/outputs/symbols
```

### 3. Keamanan Variabel Lingkungan Mobile
* Jangan pernah menyematkan kredensial rahasia di dalam kode Dart.
* Gunakan `--dart-define` saat proses build untuk menginjeksi URL API dan kunci publik:
  ```bash
  flutter build appbundle --release \
    --dart-define=API_BASE_URL=https://api.koperasi.com/api/v1 \
    --dart-define=APP_ENV=production
  ```

---

## 4. Pipeline CI/CD (GitHub Actions)

Alur otomatisasi yang dijalankan pada setiap commit ke `main`:
1. **Linting Check**: Memvalidasi PSR-12 dengan Laravel Pint dan `flutter analyze`.
2. **Automated Testing**: Menjalankan seluruh test Pest PHP dan Flutter unit/widget test.
3. **Container Build**: Membuat image Docker backend jika test sukses.
4. **Deploy Staging/Production**: SSH otomatis ke server untuk menjalankan zero-downtime deployment.
