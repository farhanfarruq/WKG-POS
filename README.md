# POS Warkop

Aplikasi Point of Sales (POS) untuk Warkop. Panduan ini dibuat khusus agar aplikasi dapat langsung dijalankan dengan mudah dari file ZIP yang diberikan, mendukung berbagai sistem operasi (Windows, Mac, atau Linux) bagi yang belum terbiasa dengan coding.

## 🛠️ Persyaratan Sistem (Prerequisites)

Sebelum menjalankan project ini, pastikan kamu sudah menginstal beberapa software berikut di komputer/laptop kamu. Pilih panduan sesuai Sistem Operasi yang kamu gunakan:

### 1. Server Database (MySQL) & PHP (Minimal versi 8.1)
* **Windows:** Install **[XAMPP](https://www.apachefriends.org/download.html)** atau **[Laragon](https://laragon.org/download/)**.
* **Mac OS:** Install **[MAMP](https://www.mamp.info/en/downloads/)**, **[Laravel Herd](https://herd.laravel.com/)** (untuk PHP) + DBngin (untuk MySQL), atau gunakan Homebrew (`brew install php mysql`).
* **Linux (Ubuntu/Debian):** Install XAMPP untuk Linux (LAMPP), atau install secara native via terminal: `sudo apt install php-cli php-mysql mysql-server`.

### 2. Dependency Manager
* **Composer** (Untuk mengelola library PHP). Bisa didownload di [getcomposer.org](https://getcomposer.org/).
* **Node.js** (Untuk mengelola package frontend). Bisa didownload di [nodejs.org](https://nodejs.org/).

---

## 🐳 Instalasi Sekali Klik (Menggunakan Docker)

Cara termudah dan paling minim error untuk menjalankan aplikasi ini tanpa menginstal PHP, Node.js, atau MySQL di komputer kamu adalah menggunakan **Docker**. Semua sudah dikonfigurasi otomatis agar berjalan dalam satu perintah saja.

**Persyaratan:** Kamu hanya perlu menginstal [Docker Desktop](https://www.docker.com/products/docker-desktop/).

**Langkah-langkah:**
1. Buka Terminal / Command Prompt, lalu arahkan ke folder project ini (`cd /path/ke/folder/pos-warkop`).
2. Jalankan perintah berikut:
   ```bash
   docker compose up -d --build
   ```
3. Tunggu proses instalasi selesai (Docker akan otomatis mengunduh dependencies, melakukan migrasi, dan mengatur environment).
4. Buka browser dan akses: **[http://localhost:8000/admin](http://localhost:8000/admin)**
5. *(Selesai! Aplikasi sudah bisa langsung digunakan menggunakan akun di tabel bawah).*

> **Catatan:** Jika ingin mematikan aplikasi, jalankan perintah `docker compose down`. Data kamu (database & foto) akan tetap aman dan tidak hilang.

---

## 🚀 Langkah-langkah Instalasi (Manual)

Ikuti langkah-langkah di bawah ini secara berurutan:

### 1. Ekstrak File
Ekstrak file ZIP project yang diberikan ke dalam folder yang mudah dicari.
* *Contoh Windows:* `C:\xampp\htdocs\` atau bebas di `Documents`.
* *Contoh Mac:* `/Applications/MAMP/htdocs/` atau bebas di `Desktop`.
* *Contoh Linux:* `/var/www/html/` atau bebas di folder `Home`.

### 2. Setup Database
1. **Jalankan Service MySQL & Apache:**
   * **Windows:** Buka XAMPP/Laragon Control Panel, lalu klik Start pada Apache dan MySQL.
   * **Mac OS:** Buka aplikasi MAMP dan klik "Start Servers", atau jika pakai Herd+DBngin pastikan service database sudah berjalan.
   * **Linux:** Jika menggunakan native, jalankan perintah `sudo systemctl start mysql`. Jika pakai LAMPP, start via panel controlnya.
2. Buka browser (Chrome/Safari/Firefox) dan akses URL: `http://localhost/phpmyadmin` (atau gunakan database client seperti TablePlus / DBeaver jika tidak pakai XAMPP).
3. Buat database baru dengan nama: **`pos_warkop`**. (Klik menu "Baru" atau "New", ketik `pos_warkop`, lalu klik "Buat" atau "Create").

*Catatan Penting: Konfigurasi default di project ini disamakan dengan `.env` bawaan, yaitu menggunakan username MySQL `root` dan password `password`. Jika MySQL kamu (misal bawaan XAMPP Windows/Linux) menggunakan password kosong untuk root, silakan buka file `.env` di dalam folder project, cari baris `DB_PASSWORD=password` dan ubah menjadi `DB_PASSWORD=` (biarkan kosong).*

### 3. Buka Terminal / Command Prompt
Buka aplikasi terminal dan arahkan ke folder project yang sudah diekstrak tadi.
* **Windows:** Buka folder project di File Explorer, klik di bagian address bar atas, ketik `cmd` lalu tekan Enter.
* **Mac OS:** Buka aplikasi **Terminal**, ketik `cd ` (dengan spasi di akhir), lalu drag & drop folder project dari Finder ke dalam Terminal, lalu tekan Enter.
* **Linux:** Buka file manager, klik kanan pada folder project lalu pilih "Open in Terminal".

### 4. Jalankan Perintah Instalasi
Di dalam terminal yang sudah terbuka di folder project, jalankan perintah-perintah berikut satu per satu (tekan enter setelah tiap baris dan tunggu sampai prosesnya selesai):

```bash
# 1. Install semua dependencies/library PHP
composer install

# 2. Buat APP_KEY (Bisa dilewati jika file .env dari ZIP sudah ada isinya)
php artisan key:generate

# 3. Setup database (membuat tabel dan mengisi data awal/akun login otomatis)
php artisan migrate:fresh --seed

# 4. Install dependencies frontend untuk tampilan
npm install

# 5. Build file tampilan
npm run build
```

### 5. Jalankan Aplikasi
Masih di dalam terminal yang sama, jalankan perintah ini untuk menghidupkan server lokal:
```bash
php artisan serve
```
Setelah perintah ini dijalankan, biarkan terminal tetap terbuka. Aplikasi sudah siap digunakan!

---

## 💻 Cara Penggunaan (URL & Akun)

Aplikasi ini menggunakan satu pintu masuk (URL) yang sama untuk semua peran (Admin, Kasir, dll). Sistem akan otomatis menyesuaikan menu dan fitur yang tampil sesuai dengan peran akun yang login.

🔗 **URL Halaman Login:** 
Buka di browser kamu: **[http://localhost:8000/admin](http://localhost:8000/admin)**

### Daftar Akun (Sudah Dibuat Otomatis)

Data awal (Seeder) untuk akun dan menu (permissions) sudah otomatis dibuat saat kamu menjalankan perintah migrate di atas. Gunakan kombinasi email dan password di bawah ini untuk masuk ke dalam aplikasi:

| Peran (Role) | Email Login | Password | Kegunaan / Akses Menu |
|-------------|-------------|----------|-----------------------|
| **Super Admin** | `superadmin@warkop.com` | `password` | Akses penuh ke seluruh fitur dan pengaturan aplikasi tanpa batasan. |
| **Admin Warkop** | `admin@warkop.com` | `password` | Mengelola data Master (Produk, Inventaris, Pengguna), Laporan, dan Shift. |
| **Kasir** | `kasir@warkop.com` | `password` | Khusus untuk Operasional: Melakukan transaksi penjualan, melihat produk, dan melakukan tutup shift. |
| **Barista** | `barista@warkop.com` | `password` | Khusus untuk melihat pesanan yang masuk ke dapur (Kitchen Display System). |

*Catatan: Semua akun di atas sudah otomatis memiliki akses menu sesuai dengan porsinya masing-masing. Jadi teman yang akan mencoba tidak perlu repot setting hak akses manual lagi.*

## 💡 Tips Tambahan
- Jika saat menjalankan `php artisan migrate:fresh --seed` terjadi error tulisan merah, pastikan MySQL kamu sudah berjalan dengan baik dan nama database `pos_warkop` sudah terbuat. Periksa juga kesesuaian password `root` di file `.env`.
- Jangan tutup terminal hitam (CMD/Terminal) yang menjalankan `php artisan serve` selama kamu masih membuka aplikasi di browser. Jika tertutup tidak sengaja, aplikasi akan *offline*. Solusinya: buka Terminal lagi di folder project dan ketik ulang `php artisan serve`.

## 📱 Panduan Instalasi Lengkap untuk macOS

Berikut langkah langkah terperinci untuk menyiapkan lingkungan di macOS (menggunakan zsh). Panduan ini menggunakan Homebrew sebagai package manager karena paling umum dipakai di macOS modern.

Catatan singkat: jika kamu sudah menggunakan MAMP, Laragon, atau Laravel Herd + DBngin, beberapa langkah (install PHP / MySQL) bisa dilewati — cukup pastikan service PHP & MySQL berjalan dan bisa diakses dari terminal.

1) Pasang Homebrew (jika belum):

```bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
# Setelah install, pastikan brew tersedia di PATH (tergantung arsitektur Mac):
echo 'eval "$(/opt/homebrew/bin/brew shellenv)"' >> ~/.zprofile
eval "$(/opt/homebrew/bin/brew shellenv)"
```

2) Install PHP (versi minimal 8.1) dan MySQL:

```bash
# Install PHP (instal versi terbaru yang tersedia, pastikan >= 8.1)
brew install php

# Install MySQL (biasanya MySQL 8)
brew install mysql

# Jalankan service agar otomatis on-login
brew services start php
brew services start mysql

# Cek versi
php -v
mysql --version
```

3) (Opsional) Konfigurasi MySQL awal:

```bash
# Jika kamu belum set password root, jalankan:
mysql_secure_installation

# Buat database yang dipakai project
mysql -u root -p -e "CREATE DATABASE pos_warkop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
# Jika root tanpa password, gunakan: mysql -u root -e "CREATE DATABASE pos_warkop ..."
```

4) Install Composer (dependency manager PHP):

```bash
brew install composer
# Verifikasi
composer --version
```

5) Install Node.js dan pnpm (atau npm saja):

```bash
brew install node
# Aktifkan corepack (opsional) dan pnpm
corepack enable
corepack prepare pnpm@latest --activate
# Atau: npm install -g pnpm
pnpm --version
```

6) Siapkan project (dari folder project ini):

```bash
# Masuk ke folder project
cd /path/ke/pos-warkop

# Salin file env contoh (jika belum ada)
cp .env.example .env

# Edit .env (sesuaikan DB_*) — contoh minimal untuk MySQL via brew:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=pos_warkop
# DB_USERNAME=root
# DB_PASSWORD=password   # ganti sesuai konfigurasi mysql kamu; bisa kosong

# Install dependency PHP
composer install --no-interaction --prefer-dist

# Generate app key
php artisan key:generate

# Migrasi dan seeder (akan membuat tabel & data awal)
php artisan migrate:fresh --seed

# Buat symbolic link storage (untuk file upload)
php artisan storage:link

# Install dependency frontend dan compile (pakai pnpm atau npm)
pnpm install
pnpm run build
# atau gunakan npm:
# npm install
# npm run build

# Jalankan server development
php artisan serve --host=127.0.0.1 --port=8000

# Buka: http://127.0.0.1:8000/admin  (atau http://localhost:8000/admin)
```

7) Alternatif: gunakan Laravel Valet (untuk pengalaman macOS yang lebih rapi)

```bash
# Install valet (memerlukan composer global bin di PATH)
composer global require laravel/valet
~/.composer/vendor/bin/valet install

# Di folder project:
cd /path/ke/pos-warkop
valet park   # atau valet link pos-warkop
# Lalu buka: http://pos-warkop.test
```

Troubleshooting singkat:
- Jika koneksi MySQL gagal, pastikan MySQL berjalan: `brew services list` atau `brew services start mysql`.
- Jika `php artisan key:generate` gagal karena tidak menemukan `.env`, pastikan sudah men-copy `.env.example` ke `.env`.
- Jika ada error extension PHP, instal extension yang dibutuhkan. Umumnya Laravel memerlukan ekstensi: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `gd`.
- Jika port 8000 sudah dipakai, jalankan `php artisan serve --port=8080` atau port lain.

Jika butuh, saya bisa tambahkan skrip kecil untuk memeriksa dependensi (cek versi PHP, Composer, Node, MySQL) atau menambahkan instruksi instalasi untuk Apple Silicon vs Intel khusus.

### Troubleshooting (lebih detail)

- Permission & file ownership:
   - Jika terjadi masalah permission saat membuat file di `storage/` atau `bootstrap/cache`, jalankan:

```bash
sudo chown -R $(whoami):staff storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache
```

- PHP extensions missing:
   - Periksa extensions yang aktif:

```bash
php -m
```

   - Untuk menambahkan extension di brew PHP (mis. gd) gunakan pecl atau brew formulas tambahan:

```bash
brew install libpng freetype jpeg
pecl install gd
```

- Jika npm/ pnpm build gagal karena Node version, coba gunakan nvm untuk mengelola versi Node:

```bash
brew install nvm
# ikuti instruksi after install untuk menambahkan ke ~/.zprofile
nvm install --lts
nvm use --lts
```

- Masalah queue (background worker) atau websockets: pastikan service pendukung (redis, supervisord) diinstall dan berjalan jika digunakan.

Jika kamu mau, saya bisa menambahkan skrip `scripts/check-env.sh` yang menjalankan cek cepat pada macOS untuk memastikan PHP, Composer, Node, MySQL terpasang dan versi sesuai.
