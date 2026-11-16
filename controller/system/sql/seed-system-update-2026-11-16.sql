START TRANSACTION;

-- 1. Kirim Obat ke PCare per Tiket & Sekaligus
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT
    'Kirim obat ke PCare per tiket dan sekaligus',
    'Halaman permintaan farmasi kini memiliki tombol Kirim Obat di setiap tiket dan tombol kirim semua tiket yang belum dikirim sekaligus. Obat dari seluruh tiket kunjungan digabung otomatis sebelum dikirim ke PCare, disertai validasi tiket kosong dan kunjungan yang belum tersedia. Ditambahkan juga tombol Rincian Obat untuk melihat isi setiap tiket.',
    'feature',
    'b928c72e',
    '2026-11-09 21:16:48',
    0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = 'b928c72e');

-- 2. Tiket Resep Selesai Terkunci
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT
    'Tiket resep yang sudah selesai tidak bisa diubah',
    'Obat pada tiket resep yang sudah disetujui (Selesai) tidak dapat lagi ditambah, diubah, atau dihapus. Obat yang dihapus dari rincian resep juga tidak muncul kembali setelah halaman dibuka ulang.',
    'fix',
    'a04946e4',
    '2026-11-10 10:35:55',
    0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = 'a04946e4');

-- 3. HR dan RR Tertukar
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT
    'Nadi (HR) dan respirasi (RR) tidak lagi tertukar',
    'Perbaikan posisi Nadi dan Respirasi pada pemeriksaan medis pasien non-BPJS yang sebelumnya tertukar. Data kunjungan lama yang tertukar sudah dikoreksi.',
    'fix',
    'e3fc12de',
    '2026-11-12 09:45:32',
    0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = 'e3fc12de');

-- 4. Hasil Lab di Riwayat Pengobatan
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT
    'Hasil laboratorium tampil di riwayat pengobatan pasien',
    'Tab Lab pada riwayat pengobatan detail pasien kini menampilkan hasil pemeriksaan per item lengkap dengan satuan, nilai normal, penanda hasil di luar normal, dan catatan lab. Kunjungan yang batal tidak ditampilkan pada riwayat.',
    'fix',
    '095f57da',
    '2026-11-12 16:48:20',
    0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = '095f57da');

-- 5. Riwayat Pengobatan Lebih Cepat
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT
    'Riwayat pengobatan detail pasien lebih cepat',
    'Riwayat pengobatan pasien dengan banyak kunjungan kini terbuka jauh lebih cepat. Tab Obat menampilkan status setiap resep dan subtotal sesuai harga saat resep dibuat, serta tampilan data pemeriksaan dan diagnosa dibuat lebih aman.',
    'improvement',
    'a7473484',
    '2026-11-16 09:01:24',
    0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = 'a7473484');

-- 6. Form Racikan & Template Racikan
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT
    'Form racikan dan template racikan',
    'Tiket resep bertipe racikan kini memiliki tombol + Racikan untuk mengisi nama racikan, tipe racik (DTD / Non DTD), jumlah dan satuan, aturan pakai, serta daftar bahan dalam satu form. Untuk DTD, jumlah bahan diisi per bungkus dan total dihitung otomatis. Racikan dapat disimpan sebagai template untuk dipakai kembali.',
    'feature',
    'dd49f6d0',
    '2026-11-16 09:30:23',
    0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = 'dd49f6d0');

-- 7. Tiket Belum Dikirim Ikut Selesai
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT
    'Tiket resep belum dikirim tidak ikut berstatus Selesai',
    'Perubahan status oleh farmasi (Waiting, Persiapan, Selesai) kini hanya berlaku untuk tiket yang sudah dikirim. Tiket yang belum dikirim tidak lagi ikut berubah menjadi Selesai.',
    'fix',
    'dd49f6d0-1',
    '2026-11-16 09:30:24',
    0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = 'dd49f6d0-1');

-- 8. Monitoring Antrean Online BPJS
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT
    'Monitoring Antrean Online BPJS',
    'Menu baru untuk memantau kepatuhan antrean online FKTP: pemanfaatan antrean online dan Mobile JKN, status panggil hadir / tidak hadir, batal, serta waktu tunggu pelayanan. Dilengkapi tren harian, rekap per poli, dokter, dan hari, serta daftar pasien yang perlu ditindaklanjuti.',
    'feature',
    'ec7c00c6',
    '2026-11-16 09:43:38',
    0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = 'ec7c00c6');

-- 9. Cek & Kirim Obat PCare dari Monitoring Antrean
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT
    'Cek dan kirim obat ke PCare dari Monitoring Antrean',
    'Monitoring Antrean Online kini menampilkan status obat setiap kunjungan BPJS (terkirim, belum dikirim, resep kosong, kunjungan PCare belum ada) dan menyediakan tombol Kirim ke PCare untuk resep yang belum terkirim.',
    'feature',
    '1ab0d500',
    '2026-11-16 09:49:57',
    0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = '1ab0d500');

-- 10. Laporan Farmasi Lebih Cepat & Sinkron
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT
    'Laporan stok farmasi lebih cepat dan sinkron dengan Excel/Print',
    'Laporan farmasi kini terbuka dalam hitungan detik. Tampilan baru dengan ringkasan stok, filter status, kategori, dan pencarian; Excel dan Print mengikuti filter dan urutan yang sama dengan tabel. Stok keluar dihitung dari resep klinik sendiri, stok awal sesuai awal periode, dan kop cetak menampilkan nama klinik yang benar.',
    'improvement',
    'bff8fa2f',
    '2026-11-16 22:14:42',
    0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = 'bff8fa2f');

-- 11. Stok per Obat & Kartu Stok
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT
    'Stok per obat dengan filter periode dan kartu stok',
    'Halaman stok obat kini memakai perhitungan yang sama dengan laporan farmasi, dilengkapi filter periode, ringkasan stok awal, masuk, keluar, dan akhir, serta tab Kartu Stok berisi mutasi penerimaan dan resep dengan saldo berjalan.',
    'improvement',
    '2a87f189',
    '2026-11-16 22:27:58',
    0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = '2a87f189');

-- 12. RME ANC di Poliklinik
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT
    'RME Persalinan (ANC) tersedia di Poliklinik',
    'Tab Persalinan (ANC) yang sebelumnya hanya ada di RME Rawat Inap kini juga tersedia di RME Poliklinik.',
    'feature',
    'daf1d112',
    '2026-11-16 22:32:55',
    0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = 'daf1d112');

COMMIT;
