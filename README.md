# SIMPEG - Sistem Manajemen Data Pegawai

Aplikasi web sederhana untuk mengelola data pegawai (tambah, lihat, ubah, hapus) dengan dashboard grafik. Dibuat menggunakan PHP native dan MySQL.

## Fitur
- Dashboard statistik dan grafik (jenis kelamin, pendidikan, usia)
- CRUD data pegawai
- Pencarian, filter, urut, dan paginasi
- Konfirmasi sebelum menghapus data

## Teknologi
- PHP 8 (native)
- MySQL
- Bootstrap 5
- Chart.js

## Cara Menjalankan
1. Pastikan PHP 8+ dan MySQL sudah terpasang dan berjalan.
2. Buat database dan isi data contoh:
   ```bash
   php database/install.php
   ```
   Atau impor `database/schema.sql` dan `database/seed.sql` melalui phpMyAdmin.
3. Sesuaikan konfigurasi database di `config/config.php` bila diperlukan.
4. Jalankan server lokal:
   ```bash
   php -S localhost:8000 -t public
   ```
5. Buka [http://localhost:8000](http://localhost:8000) di browser.

## Struktur Folder
- `config/` - konfigurasi aplikasi dan database
- `src/` - logika aplikasi (repository, validasi, helper, dsb.)
- `public/` - halaman web (document root)
- `database/` - skema, data contoh, dan installer
- `docs/` - dokumentasi dan gambar
