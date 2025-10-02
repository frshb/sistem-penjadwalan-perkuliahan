# 🚀 Laravel Multi-Environment Deployment Guide

Panduan ini menjelaskan langkah-langkah **manual** untuk melakukan deployment Laravel ke berbagai environment (`local`, `staging`, `production`) berdasarkan script `deploy.sh`.

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

### 3. Salin File .env

```bash
cp .env.example .env
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

## 🔁 Alternatif Otomatis: Gunakan Script

Gunakan script `deploy.sh` untuk mempercepat proses:

```bash
# Tambahkan permission untuk eksekusi jika belum
chmod +x deploy.sh

# Jalankan sesuai environment:
./deploy.sh local
./deploy.sh staging
./deploy.sh production
```

---

## 📝 Catatan Tambahan

- Selalu **backup database** sebelum melakukan migrasi atau seeding di production.
- Pastikan menggunakan HTTPS di environment `production` dan `staging`.
- Untuk `queue`, `scheduler`, atau `horizon`, setup tambahan mungkin dibutuhkan.

---

Happy deploying! 🚀
