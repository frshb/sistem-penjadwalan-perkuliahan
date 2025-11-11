# 🚀 Laravel Multi-Environment Deployment Guide

Panduan ini menjelaskan langkah-langkah **manual** untuk melakukan deployment Laravel

---

## 📦 Prasyarat

- PHP ≥ 8.2
- Composer
- MySQL atau PostgreSQL
- Node.js & NPM (jika menggunakan frontend build seperti Vite)
- Web server (Apache/Nginx)
- Akses terminal ke server (SSH)

---

## 🛠️ Langkah Manual Deployment

### 1. Clone Repository

```bash
git clone https://github.com/frshb/sistem-penjadwalan-perkuliahan.git
cd project-tsu
```

---

### 2. Install Dependency

```bash
# Untuk local / staging:
composer install --no-interaction --prefer-dist --optimize-autoloader

# Untuk production (tanpa dev dependencies):
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
```

---

### 3. Buat File .env
```
APP_NAME="Sistem Penjadwalan"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

# Kunci ini akan kita isi di Langkah 3
APP_KEY=base64:HC0ZJqyxMmrd1CsTGMdnzrt61Zezxebk3keBTgtZ9Nc=

LOG_CHANNEL=stack
LOG_LEVEL=debug

# --- KONFIGURASI DATABASE ANDA ---
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_penjadwalan_ft
DB_USERNAME=root
DB_PASSWORD=
# ----------------------------------

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
MEMCACHE_HOST=127.0.0.1
```
---

### 4. Generate Application Key

```bash
php artisan key:generate
```

---

### 5. Atur Permission

```bash
# Untuk linux:
chmod -R 775 storage bootstrap/cache
```


### 7. (Opsional) Seed Database

```bash
# Hanya untuk local/staging
php artisan db:seed --force
atau
php artisan migrate:fresh --seed
```

---

### 8. Buat Storage Symlink

```bash
php artisan storage:link
```

---

### 9. Optimasi Cache (Staging & Production)

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

> Untuk local gunakan `php artisan config:clear` dan sejenisnya.

---

### 10. Build Frontend (Jika Ada)

```bash
npm install

# Untuk local/staging:
npm run dev

# Untuk production:
npm run build
```
---

## 📝 Catatan Tambahan

- Selalu **backup database** sebelum melakukan migrasi atau seeding di production.

Happy deploying! 🚀
