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

## RME satu siklus dan resep

Halaman `module/admin/rme_new` memakai controller khusus
`controller/doctor/saveRmeCycle.php`. Resep dan rincian obat dapat dibuat
sebagai draft sebelum nomor kunjungan BPJS tersedia. Link rincian resep dari
halaman ini membawa `cycle=1`; tombol Kirim Obat manual tidak ditampilkan.
Rincian dibuka pada tab baru agar isian pemeriksaan di tab RME tidak hilang.
Kembali ke tab RME, lengkapi pemeriksaan, lalu klik Simpan Pemeriksaan.

Controller membaca resep dari database klinik/kunjungan (bukan terapi dari
browser), memvalidasi rincian, dan menggabungkan seluruh daftar obat ke
`terapiObat` saat membuat/memperbarui kunjungan. Setelah kunjungan berhasil,
tiket draft berubah menjadi Sudah Kirim. Status Persiapan/Selesai tidak diubah.
Jika kunjungan gagal atau rincian draft kosong/tidak lengkap, tiket tetap draft.
Tanpa resep, simpan pemeriksaan tetap berjalan. Tidak diperlukan migrasi.
Modul pemeriksaan dan kirim resep lama tetap memakai endpoint sebelumnya.

Form pemeriksaan pada halaman satu siklus dipisahkan dari form modal komponen
agar HTML tidak memiliki form bertumpuk dan semua field pemeriksaan ikut
terkirim. Controller socket menggunakan session yang sudah aktif agar daftar
resep tetap JSON valid pada Herd.
Uji regresi: `php tests/rmeCycleTest.php`. Uji menggunakan tabel sementara
pada koneksi tersendiri dan stub simpan kunjungan, bukan data pasien nyata
atau request BPJS.

## Penanda Operasi

Menu Penanda Operasi berada setelah Riwayat Pengobatan pada RME a, b,
dan rawat inap c (mengikuti akses menu Riwayat Pengobatan).
Halaman mempertahankan parameter kunjungan, nomor RM, dan jenis RME.
Tab Wanita, Pria, Gigi & Mulut, Ekstremitas, dan Mata menampilkan gambar `site-marker-*.png` dari
`assets/images/`. Pilih alat Coretan atau Teks, warna, dan ukuran garis.
Untuk teks, isi teks lalu klik lokasi pada gambar. Urungkan menghapus penanda
terakhir; Bersihkan mengosongkan gambar aktif. Draft tiap gambar tetap ada
saat berpindah tab. Isi catatan, lalu Simpan Versi Baru untuk gambar aktif.

Sebelum mengaktifkan penyimpanan, jalankan migrasi
`controller/visit/sql/create-operation-markers.sql` pada database aplikasi.
Tabel `operation_markers` menyimpan PNG resolusi asli, koordinat yang dapat
dilanjutkan, catatan, pengguna pencatat, klinik, kunjungan, dan waktu.
Gambar disimpan dalam database, bukan URL upload publik. Akses baca, simpan,
dan PDF dibatasi ke klinik pada session; simpan memerlukan token formulir.
Setiap simpan membuat dokumen baru, tidak mengubah dokumen sebelumnya.

Daftar dokumen menyediakan Buka / Lanjutkan serta Cetak / PDF per versi.
Cetak / PDF Semua Versi dan Bundle Klaim memuat **semua versi** pada kunjungan,
bukan hanya versi terakhir. Tidak ada halaman penanda tambahan jika kunjungan
belum memiliki dokumen. Jika pembacaan atau rendering penanda gagal, Bundle
Klaim dihentikan dengan pesan error agar dokumen tidak terlewat.
Dokumen ini adalah pendukung lokasi tindakan, bukan pengganti laporan operasi,
persetujuan tindakan, atau jaminan penerimaan klaim.

Riwayat Pengobatan menampilkan bagian Laporan / Penanda Operasi pada detail
kunjungan yang memiliki dokumen tersimpan, termasuk semua versi, catatan,
pencatat, waktu, gambar hasil penandaan, link gambar besar, dan PDF per versi.
Kunjungan tanpa dokumen tidak menampilkan bagian ini. Riwayat dokumen dibatasi
ke pasien dan klinik login, serta tidak menyertakan kunjungan batal.
Gambar dimuat secara lazy melalui endpoint session klinik
`controller/visit/operationMarkerImage.php`, bukan URL publik; kegagalan baca
riwayat atau gambar ditampilkan sebagai pesan error.
Uji tampilan/query riwayat dengan `php tests/operationMarkerHistoryTest.php`;
fixture memakai tabel sementara dan tidak menambah rekam medis pasien.

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
