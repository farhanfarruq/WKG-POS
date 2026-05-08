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

## 🚀 Langkah-langkah Instalasi

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
