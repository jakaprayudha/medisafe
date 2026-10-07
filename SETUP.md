# Persiapan setelah clone

## File dan folder yang diabaikan Git

- `.env`: pengaturan lokal dan rahasia, tidak dimasukkan ke commit.
- `database/`: file lokal dan dump database diabaikan; `connect.php` disertakan untuk koneksi aplikasi.
- `uploads/`: file unggahan lokal diabaikan. Folder-folder yang dibutuhkan aplikasi sudah memiliki placeholder.
- `logs/`: folder disertakan, tetapi file log diabaikan.
- `.htaccess`: konfigurasi lokal/server. Gunakan `.htaccess.example` sebagai acuan jika diperlukan.
- `vendor/`: dependency PHP dipasang menggunakan Composer.

## Langkah awal

1. Salin `.env.example` menjadi `.env`, lalu sesuaikan `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`, dan `API_URL`.
2. Buat database MySQL sesuai `DB_NAME`, lalu import dump/schema yang disediakan secara terpisah. Repository ini tidak menyertakan dump database.
3. Jalankan `composer install` untuk memasang dependency PHP.
4. Pastikan web server dapat menulis ke folder-folder yang diperlukan di `uploads/` dan `logs/`.
5. Jika memakai Apache, tinjau `.htaccess.example` dan sesuaikan URL/path aplikasi sebelum menyalinnya menjadi `.htaccess`.

`database/connect.php` membaca konfigurasi dari `.env`, memulai session seperti template koneksi sebelumnya, dan menyediakan koneksi `mysqli` melalui variabel `$koneksi`.
