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

## Pemeriksaan odontogram

Di modul Odontogram atau bagian odontogram pada RME, pilih nomor gigi lalu
cari diagnosa ICD-10 BPJS dengan minimal tiga karakter kode atau nama.
Sumber pencarian sama dengan field Diagnosa Utama pada modul Pemeriksaan.
Kode dan nama diagnosa disimpan bersama; diagnosa lama tetap dapat diedit.
Jika pencarian gagal, form menampilkan pesan error, bukan hasil kosong
seolah berhasil.

ID klinik untuk simpan, edit, hapus, dan baca odontogram berasal dari
`id_customer` pada session login, bukan parameter `rm` (nomor rekam medis).
Kunjungan harus terdaftar pada klinik yang sedang login.
Untuk verifikasi, simpan satu entri uji pada kunjungan yang diizinkan,
muat ulang halaman, edit entri tersebut, lalu hapus dan pastikan tabel
kembali ke kondisi awal. Jangan mengirim entri uji sebagai pemeriksaan BPJS.

## Foto pada kartu pasien

Kartu pasien mengambil `patient_foto` dari master pasien melalui nomor
kunjungan. Foto yang disimpan di master tampil dari `uploads/patient/`.
Jika belum ada foto atau file gagal dimuat, avatar menampilkan inisial nama.
Nama menggunakan data kunjungan, dengan nama master pasien sebagai fallback.
Jika data kartu gagal diambil, kartu menampilkan pesan untuk memuat ulang.

## Simpan otomatis wajah kunjungan

Pada Ambil Foto di registrasi poliklinik, foto kunjungan otomatis disimpan
setelah wajah cocok dengan rekaman master (jarak descriptor maksimal 0.5)
dan posisi wajah memenuhi panduan kamera. Penyimpanan memakai alur yang
sama dengan tombol Ambil Wajah; foto master pasien tidak ditimpa.
Tanpa rekaman master yang terbaca, gunakan pengambilan manual.
Jika simpan otomatis gagal, error ditampilkan dan pengguna dapat mencoba
lagi melalui tombol. Tidak ada pengiriman ulang otomatis berulang.
