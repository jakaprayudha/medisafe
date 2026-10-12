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

Dashboard lokal dapat diakses melalui `/module/admin` maupun `/module/admin/`;
base `../../` pada keduanya mengarah ke root aplikasi. API dashboard menggunakan
session yang sudah dimulai oleh koneksi database, tanpa `session_start()` ulang
yang dapat mencampurkan notice PHP ke respons JSON. Grafik template hanya
diinisialisasi ketika elemen grafiknya tersedia pada halaman.

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
Jika foto upload belum tersedia atau gagal dimuat, kartu mencoba rekaman
wajah master `face_image` dari `uploads/faces/`. Path rekaman lama seperti
`/../../uploads/faces/nama.jpg` dinormalisasi ke folder rekaman wajah.
Jika kedua foto tidak tersedia atau gagal dimuat, avatar menampilkan inisial nama.
Klik foto yang sudah dimuat untuk membuka pratinjau besar dalam modal.
Avatar inisial tidak dapat diklik; modal dapat ditutup lewat tombol Tutup
atau Escape.
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

## Riwayat pengobatan

Riwayat Pengobatan pada RME (termasuk rawat inap) dan detail pasien tidak
menampilkan kunjungan yang dibatalkan (`visit_status = 99`). Kunjungan
dengan status lain atau status kosong tetap ditampilkan seperti sebelumnya.
Filter hanya mengubah daftar riwayat; data kunjungan batal tidak dihapus.
Badge jenis layanan menggunakan `source_hub` dan `status_rawatinap`, bukan
halaman RME yang sedang dibuka. Status pulang yang tercatat tetap ditampilkan;
jika kosong, badge menggunakan jenis layanan (Poliklinik, UGD, atau Rawat
Inap). Kode status pulang yang tidak dikenal ditampilkan sebagai `-`, bukan
dianggap Rawat Inap.

## Log Update

Modal Log Update, badge, daftar riwayat, pencarian, dan statistik hanya
menampilkan entri yang belum berumur satu bulan kalender sejak `created_at`.
Pada tepat satu bulan, entri tidak lagi ditampilkan. Tidak diperlukan cron;
filter diterapkan saat halaman atau API dimuat, dan badge diperiksa berkala.
Arsip lama tetap tersedia di master log administrator, tanpa dihapus.

Ringkasan empat commit tanggal 12 Oktober 2026 tersedia dalam
`controller/system/sql/seed-system-update-2026-10-12.sql`. Jalankan SQL ini pada database
deployment untuk menerbitkan ringkasannya. Tanggal mengikuti commit dan
`version` berisi hash singkat commit; eksekusi ulang tidak menambah duplikat.
Ringkasan berikutnya tetap ditambahkan melalui master log, bukan sinkronisasi
Git otomatis.
