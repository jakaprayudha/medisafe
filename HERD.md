# Menjalankan Medisafe di Laravel Herd

Herd memakai Nginx, sehingga aturan `.htaccess` Apache tidak dijalankan.
`LocalValetDriver.php` memetakan URL tanpa ekstensi, misalnya
`/module/admisi/registrasi-poliklinik`, ke file PHP yang sesuai.
Folder dengan `index.php` dan file statis tetap dapat diakses.
URL yang tidak memiliki file tujuan menghasilkan 404, bukan halaman login
dengan jalur asset yang salah.

Arahkan domain Herd ke root repository dan buka `https://medisafe.test/`.
Driver ditemukan otomatis oleh Herd; tidak perlu mengubah konfigurasi global.
Koneksi database dan session tetap menggunakan konfigurasi aplikasi yang ada.
