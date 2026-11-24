-- Log update: perubahan 1-24 November 2026 yang belum tercatat (sebelumnya log terakhir 16 Nov).
-- Idempoten: setiap baris dilewati bila version (hash commit) sudah ada.
START TRANSACTION;

-- 1. Stok obat ditampilkan secara realtime
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Stok obat ditampilkan secara realtime', 'Perhitungan stok obat kini langsung mengikuti transaksi terbaru (realtime), tidak lagi memakai perhitungan rata-rata 30 hari.', 'improvement', 'f881cd94', '2026-11-03 14:56:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = 'f881cd94');

-- 2. eKYC untuk Tanda Tangan Elektronik (TTE)
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'eKYC untuk Tanda Tangan Elektronik (TTE)', 'Menu baru eKYC TTE: unggah KTP dan verifikasi wajah (liveness) sebelum tanda tangan elektronik dapat dipakai, lengkap dengan halaman pengelolaan untuk admin.', 'feature', '7f501292', '2026-11-05 11:06:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = '7f501292');

-- 3. Pencatatan imunisasi pasien
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Pencatatan imunisasi pasien', 'Menu Imunisasi pada RME untuk mencatat, mengubah, dan menghapus riwayat imunisasi pasien per kunjungan.', 'feature', 'a3cba102', '2026-11-05 14:01:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = 'a3cba102');

-- 4. Info jenis peserta, kelas, dan hubungan keluarga BPJS
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Info jenis peserta, kelas, dan hubungan keluarga BPJS', 'Hasil cek peserta BPJS kini menampilkan jenis peserta, kelas rawat, dan status hubungan keluarga.', 'improvement', '7f38bc64', '2026-11-09 07:18:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = '7f38bc64');

-- 5. BMHP dapat diminta dan dikirim ke PCare
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'BMHP dapat diminta dan dikirim ke PCare', 'Permintaan farmasi kini mendukung tipe BMHP (bahan medis habis pakai) yang ikut dikirim ke PCare bersama obat kunjungan. Pengiriman obat ke PCare juga dibuat lebih cepat dan status pengiriman pasien rawat inap diperbaiki.', 'feature', 'b146f1af', '2026-11-16 23:24:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = 'b146f1af');

-- 6. Tampilan baru halaman login dan reset password
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Tampilan baru halaman login dan reset password', 'Halaman masuk dan reset password didesain ulang agar lebih rapi dan mudah digunakan.', 'improvement', '56b12506', '2026-11-17 20:59:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = '56b12506');

-- 7. Pengelolaan ruang dan tempat tidur diperbarui
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Pengelolaan ruang dan tempat tidur diperbarui', 'Halaman master ruang dan detail tempat tidur tampil lebih rapi dengan pengelolaan bed yang lebih mudah dan aman.', 'improvement', '753e547a', '2026-11-17 21:57:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = '753e547a');

-- 8. Label Anamnesa dan Catatan Bebas diperjelas
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Label Anamnesa dan Catatan Bebas diperjelas', 'Penamaan kolom anamnesa dan catatan bebas pada pemeriksaan dan permintaan farmasi disesuaikan agar tidak membingungkan.', 'improvement', '450c95e8', '2026-11-18 11:26:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = '450c95e8');

-- 9. Perubahan terapi otomatis disinkronkan ke PCare
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Perubahan terapi otomatis disinkronkan ke PCare', 'Obat yang diubah setelah dikirim kini dapat disinkronkan ulang ke PCare dari detail order farmasi. Nama obat kosong ditangani dan pesan kesalahan tiket lebih jelas.', 'feature', '5cb9eabb', '2026-11-18 12:35:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = '5cb9eabb');

-- 10. Peningkatan keamanan login, akun, dan sesi
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Peningkatan keamanan login, akun, dan sesi', 'Proses login diperketat, perubahan profil hanya untuk akun sendiri, kebijakan password dan penyimpanan password lebih aman, serta pembatasan hak akses per role pada pengelolaan user.', 'fix', '4a1c376f', '2026-11-18 16:27:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = '4a1c376f');

-- 11. Detail pasien: riwayat berobat bertahap dan dokumen pasien
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Detail pasien: riwayat berobat bertahap dan dokumen pasien', 'Halaman detail pasien memuat riwayat pengobatan secara bertahap (lebih cepat) serta pengelolaan dokumen KTP, KK, dan kartu BPJS pasien.', 'improvement', '667cc90c', '2026-11-19 01:01:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = '667cc90c');

-- 12. Tampilan baru halaman Master Data
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Tampilan baru halaman Master Data', 'Halaman master Poli, Display, Penjamin, Dokter, Perawat, Obat & BMHP, Tarif, dan User didesain ulang agar lebih rapi, cepat, dan aman.', 'improvement', 'bc0869a0', '2026-11-19 12:51:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = 'bc0869a0');

-- 13. Harga jual obat otomatis berdasarkan margin
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Harga jual obat otomatis berdasarkan margin', 'Pengaturan margin harga farmasi: harga jual obat dihitung otomatis dari harga beli dan persentase margin yang ditetapkan.', 'feature', '9cf486ae', '2026-11-19 13:28:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = '9cf486ae');

-- 14. Video dan playlist pada display antrean admisi
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Video dan playlist pada display antrean admisi', 'Display antrean admisi dapat memutar video atau playlist video per faskes, dan suara video otomatis mengecil saat panggilan antrean berbunyi.', 'feature', 'f30ecb6f', '2026-11-19 15:29:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = 'f30ecb6f');

-- 15. Laporan kunjungan, diagnosis, rawat inap, dan prosedur diperbarui
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Laporan kunjungan, diagnosis, rawat inap, dan prosedur diperbarui', 'Laporan appointment, 10 besar diagnosis, rawat inap, dan prosedur kini lebih cepat dengan export Excel dan cetak yang sesuai dengan tampilan.', 'improvement', 'bf7d8605', '2026-11-19 22:22:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = 'bf7d8605');

-- 16. Analisis stok, obat kedaluwarsa, dan pemakaian obat
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Analisis stok, obat kedaluwarsa, dan pemakaian obat', 'Laporan farmasi dilengkapi analisis stok, laporan obat kedaluwarsa, serta dashboard pemakaian obat dengan export.', 'feature', '23d4559d', '2026-11-19 23:23:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = '23d4559d');

-- 17. Monitoring Antrean Online dan Satu Sehat lebih informatif
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Monitoring Antrean Online dan Satu Sehat lebih informatif', 'Halaman monitoring kini memiliki indikator memuat, ringkasan KPI yang lebih jelas, pilihan faskes yang lebih mudah, dan penanganan kesalahan yang lebih baik.', 'improvement', '04569610', '2026-11-20 16:26:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = '04569610');

-- 18. Pengaturan Satu Sehat dan data IHS dokter/pasien
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Pengaturan Satu Sehat dan data IHS dokter/pasien', 'Halaman pengaturan Satu Sehat serta daftar IHS dokter dan pasien didesain ulang agar lebih mudah dicek dan disinkronkan.', 'improvement', 'c99ae8c9', '2026-11-20 16:33:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = 'c99ae8c9');

-- 19. Monitoring kelengkapan RME
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Monitoring kelengkapan RME', 'Menu Monitoring RME menampilkan checklist kelengkapan rekam medis per kunjungan beserta ringkasan KPI per dokter.', 'feature', '008a33bd', '2026-11-20 17:47:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = '008a33bd');

-- 20. Pengelolaan tanda tangan petugas
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Pengelolaan tanda tangan petugas', 'Halaman tanda tangan menampilkan waktu terakhir tanda tangan diperbarui dan lebih mudah dikelola.', 'improvement', 'ecc68264', '2026-11-20 18:11:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = 'ecc68264');

-- 21. Gabung RME pasien ganda
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Gabung RME pasien ganda', 'Menu Gabung RME untuk menggabungkan rekam medis pasien yang terdaftar ganda ke satu nomor RM.', 'feature', 'e1c65379', '2026-11-20 18:35:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = 'e1c65379');

-- 22. Tanda tangan keluarga dan form persetujuan
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Tanda tangan keluarga dan form persetujuan', 'Penyimpanan tanda tangan pasien/keluarga, foto wajah, dan dokumen kunjungan diperbaiki, serta form dan cetakan surat persetujuan lebih rapi.', 'improvement', 'a08a2db9', '2026-11-20 20:06:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = 'a08a2db9');

-- 23. Pendaftaran pasien tanpa identitas
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Pendaftaran pasien tanpa identitas', 'Pasien tanpa identitas (anonim) kini dapat didaftarkan di rawat jalan dan UGD, lalu dilengkapi datanya kemudian.', 'feature', '6524fb4b', '2026-11-20 20:50:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = '6524fb4b');

-- 24. Daftar pasien admisi dan batal antrean
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Daftar pasien admisi dan batal antrean', 'Daftar pasien admisi lebih rapi dan proses check-in serta pembatalan antrean JKN lebih aman.', 'improvement', '10c8e1d2', '2026-11-20 20:57:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = '10c8e1d2');

-- 25. Status pulang dan pengelolaan bed rawat inap
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Status pulang dan pengelolaan bed rawat inap', 'Halaman Status Pulang Ranap didesain ulang untuk memproses kepulangan pasien, persetujuan/pembatalan rawat inap, dan ketersediaan bed.', 'improvement', 'f9c62ac6', '2026-11-21 09:02:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = 'f9c62ac6');

-- 26. Pendaftaran Poliklinik tampilan baru
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Pendaftaran Poliklinik tampilan baru', 'Pendaftaran pasien diganti menjadi Pendaftaran Poliklinik: cari pasien lebih cepat, cek BPJS berjalan di belakang, pilih poli dan dokter lebih mudah, serta validasi tanda vital yang wajar.', 'improvement', '1832195a', '2026-11-21 09:15:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = '1832195a');

-- 27. Surat keterangan dengan nomor surat otomatis
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Surat keterangan dengan nomor surat otomatis', 'Form surat (sakit, berobat, kematian, rawat inap, pemeriksaan mata) memakai nomor surat otomatis, validasi isian, dan cetakan yang hanya dapat dibuka faskes pemilik.', 'feature', 'd8c3ea51', '2026-11-21 09:44:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = 'd8c3ea51');

-- 28. Halaman eKYC TTE diperbarui
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Halaman eKYC TTE diperbarui', 'Tampilan dan proses verifikasi eKYC TTE dirapikan.', 'improvement', '3ac88477', '2026-11-21 10:11:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = '3ac88477');

-- 29. Halaman pemeriksaan dokter rawat jalan dan rawat inap
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Halaman pemeriksaan dokter rawat jalan dan rawat inap', 'Daftar dan halaman pemeriksaan dokter (rawat jalan dan rawat inap) didesain ulang, termasuk panggilan suara dan validasi i-Care yang lebih stabil.', 'improvement', 'f0a0f00c', '2026-11-21 11:57:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = 'f0a0f00c');

-- 30. Permintaan resep farmasi dengan filter tanggal dan status
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Permintaan resep farmasi dengan filter tanggal dan status', 'Halaman permintaan resep di farmasi memiliki filter tanggal dan status, serta rincian resep per kunjungan.', 'improvement', '66f388ca', '2026-11-22 12:10:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = '66f388ca');

-- 31. Pengelolaan permintaan laboratorium
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Pengelolaan permintaan laboratorium', 'Menu permintaan lab untuk petugas laboratorium: filter, status permintaan, dan input hasil pemeriksaan per item.', 'feature', '578d7c3a', '2026-11-22 20:29:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = '578d7c3a');

-- 32. Dashboard admin
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Dashboard admin', 'Dashboard admin menampilkan statistik dan data operasional harian faskes.', 'feature', 'cb76fd15', '2026-11-23 09:04:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = 'cb76fd15');

-- 33. Riwayat pengobatan jauh lebih cepat
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Riwayat pengobatan jauh lebih cepat', 'Riwayat pengobatan pasien dengan banyak kunjungan kini terbuka dalam hitungan detik (sebelumnya bisa terhenti). Tabel obat memakai harga saat resep dibuat dan ringkasan per kunjungan lebih informatif.', 'improvement', 'b4e745d9', '2026-11-23 09:13:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = 'b4e745d9');

-- 34. Kasir dan cetak struk baru
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Kasir dan cetak struk baru', 'Halaman kasir menampilkan daftar dan detail tagihan per kunjungan, pembayaran, serta struk tagihan yang lebih rapi.', 'feature', 'bf46ad79', '2026-11-23 09:25:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = 'bf46ad79');

-- 35. Pesan kesalahan BPJS lebih mudah dipahami
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Pesan kesalahan BPJS lebih mudah dipahami', 'Balasan error dari layanan BPJS kini diterjemahkan menjadi pesan yang jelas bagi petugas.', 'improvement', '0de03cd5', '2026-11-23 09:31:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = '0de03cd5');

-- 36. Laporan kasir dan resep dengan export/cetak
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Laporan kasir dan resep dengan export/cetak', 'Laporan kasir dan laporan resep baru lengkap dengan export Excel dan cetak, serta tersedia di menu farmasi dan kasir.', 'feature', '787d5e4b', '2026-11-23 09:51:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = '787d5e4b');

-- 37. Pengaturan akun PCare dan Antrol
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Pengaturan akun PCare dan Antrol', 'Pengaturan BPJS (PCare dan Antrean Online) diperbarui: tampil/sembunyikan password dan riwayat perubahan pengaturan.', 'improvement', 'c1af278e', '2026-11-23 10:01:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = 'c1af278e');

-- 38. Diagnosis pada cetakan kunjungan
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Diagnosis pada cetakan kunjungan', 'Cetakan kunjungan kini menampilkan diagnosis lengkap beserta kode ICD-10 di dalam tanda kurung.', 'fix', '8d86394c', '2026-11-23 10:05:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = '8d86394c');

-- 39. Menu samping lebih rapi
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Menu samping lebih rapi', 'Menu untuk role farmasi dan kasir disederhanakan, dan menu yang sedang dibuka ditandai dengan jelas.', 'improvement', '17d36d29', '2026-11-23 10:21:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = '17d36d29');

-- 40. Pemeriksaan Generalis Tubuh
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Pemeriksaan Generalis Tubuh', 'Form pemeriksaan generalis tubuh (kepala hingga ekstremitas) pada RME rawat inap, lengkap dengan cetakan.', 'feature', 'fe2827bc', '2026-11-23 11:23:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = 'fe2827bc');

-- 41. Asesmen RME rawat inap dan triase
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Asesmen RME rawat inap dan triase', 'Form asesmen rawat inap, pencarian ICD-10, dan triase lebih aman dengan validasi isian yang lebih baik.', 'improvement', 'd5c808bd', '2026-11-23 11:32:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = 'd5c808bd');

-- 42. Permintaan farmasi tampilan kartu tiket
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Permintaan farmasi tampilan kartu tiket', 'Tiket permintaan farmasi tampil sebagai kartu dengan status, filter, dan daftar obat langsung terlihat. Kirim per tiket atau kirim semua, serta tiket kunjungan batal tidak dapat ditambah.', 'improvement', '6075ec88', '2026-11-23 12:27:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = '6075ec88');

-- 43. CPO (catatan pemberian obat) lebih mudah
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'CPO (catatan pemberian obat) lebih mudah', 'Riwayat CPO dikelompokkan per tanggal dengan ubah langsung, tambah beberapa obat sekaligus, dan salin obat dari tanggal terakhir.', 'improvement', 'ed4103b3', '2026-11-23 12:35:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = 'ed4103b3');

-- 44. Resep luar dan cetakan resep
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Resep luar dan cetakan resep', 'Halaman resep luar lebih rapi dengan pilihan signa cepat, serta cetak resep ukuran A5 lengkap dengan kop dan logo klinik.', 'improvement', 'c92f2dd4', '2026-11-23 12:43:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = 'c92f2dd4');

-- 45. Pemeriksaan penunjang dan input hasil lab
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Pemeriksaan penunjang dan input hasil lab', 'Halaman penunjang menampilkan permintaan kunjungan ini dan riwayat lab pasien. Input hasil langsung menandai nilai di atas atau di bawah normal (H/L).', 'improvement', '1251c123', '2026-11-23 12:50:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = '1251c123');

-- 46. Vaksin terkirim ke Satu Sehat
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Vaksin terkirim ke Satu Sehat', 'Pencatatan vaksin memakai kode vaksin KFA, alasan, rute, dosis, nomor batch, dan kedaluwarsa, lalu otomatis dikirim ke Satu Sehat (Immunization). Bila gagal, data tetap tersimpan dan bisa dikirim ulang.', 'feature', '66b95703', '2026-11-23 13:15:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = '66b95703');

-- 47. Tindakan: harga tidak lagi terhapus saat diubah
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Tindakan: harga tidak lagi terhapus saat diubah', 'Halaman Tindakan menampilkan total biaya, harga tidak ter-reset saat membuka form ubah, dan item terkunci bila tagihan kunjungan sudah lunas.', 'fix', '52f86481', '2026-11-23 13:27:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = '52f86481');

-- 48. Peringatan peserta BPJS terdaftar di FKTP lain
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Peringatan peserta BPJS terdaftar di FKTP lain', 'Saat pendaftaran, bila FKTP peserta BPJS bukan faskes ini akan muncul peringatan berisi FKTP peserta dan faskes Anda. Pendaftaran tetap dapat dilanjutkan.', 'feature', 'c55b7d2e', '2026-11-24 08:20:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = 'c55b7d2e');

-- 49. Persalinan (ANC): HPL dan usia kehamilan otomatis
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Persalinan (ANC): HPL dan usia kehamilan otomatis', 'Ringkasan kehamilan (HPHT, HPL, usia kehamilan, trimester), riwayat ANC lintas kunjungan, fitur ubah, serta HPL dan usia kehamilan dihitung otomatis dari HPHT. Nilai yang perlu diwaspadai ditandai merah.', 'improvement', 'bf81565a', '2026-11-24 08:41:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = 'bf81565a');

-- 50. Penanda operasi lebih cepat dan mudah
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Penanda operasi lebih cepat dan mudah', 'Gambar template dimuat per tab, versi tersimpan bernomor per jenis gambar, dan tersedia pilihan warna cepat, Ctrl+Z, serta tanda perubahan belum disimpan.', 'improvement', 'fd52c4a4', '2026-11-24 08:46:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = 'fd52c4a4');

-- 51. CPPT: verifikasi oleh dokter dan catatan terkunci
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'CPPT: verifikasi oleh dokter dan catatan terkunci', 'CPPT tampil per tanggal dengan filter PPA. Verifikasi hanya oleh dokter/admin dan catatan terverifikasi terkunci dari perubahan. Data pengguna tidak lagi ikut terkirim ke browser.', 'improvement', 'e918dd57', '2026-11-24 08:54:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = 'e918dd57');

-- 52. Foto perawatan: unggah banyak foto sekaligus
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Foto perawatan: unggah banyak foto sekaligus', 'Galeri foto 4 kolom, unggah beberapa foto sekaligus (seret dan lepas), foto otomatis diperkecil agar cepat, dan file ikut terhapus saat data dihapus.', 'improvement', '92a7aed5', '2026-11-24 09:12:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = '92a7aed5');

-- 53. Dokumen pasien bisa diunggah langsung
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Dokumen pasien bisa diunggah langsung', 'KTP, KK, dan kartu BPJS dapat diunggah, diganti, dan dihapus langsung dari menu Dokumen Pasien, dan cetakan hanya untuk pasien faskes sendiri.', 'improvement', 'c8ca5466', '2026-11-24 09:30:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = 'c8ca5466');

-- 54. EKG: EKG 1 dan EKG 2 dalam satu pemeriksaan
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'EKG: EKG 1 dan EKG 2 dalam satu pemeriksaan', 'Setiap pemeriksaan EKG kini berisi EKG 1 dan EKG 2 sekaligus dan dapat diubah. Data EKG yang sebelumnya tidak tampil karena format nomor RM kini muncul dan ikut tercetak.', 'fix', '1e332a12', '2026-11-24 09:46:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = '1e332a12');

-- 55. Asesmen Gizi & Nutrisi tersimpan dan bisa dicetak
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Asesmen Gizi & Nutrisi tersimpan dan bisa dicetak', 'Form gizi kini benar-benar tersimpan (sebelumnya tidak menyimpan data), dengan draft/selesai, IMT dan BB ideal sesuai standar Kemenkes, diagnosis PES otomatis, serta cetakan formulir lengkap dan formulir kosong.', 'feature', '275f6575', '2026-11-24 10:03:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = '275f6575');

-- 56. Resume medis tidak dobel dan dokter tercatat
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT 'Resume medis tidak dobel dan dokter tercatat', 'Resume medis tidak lagi tersimpan berulang saat tombol Simpan diklik lebih dari sekali, DPJP ikut tercatat, cetakan memakai resume terbaru, dan isian terapi dapat diambil dari CPPT atau resep pulang.', 'fix', 'b54edecd', '2026-11-24 10:13:00', 0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = 'b54edecd');

COMMIT;
