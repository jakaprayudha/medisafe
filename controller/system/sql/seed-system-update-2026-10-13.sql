START TRANSACTION;

-- 1. Label Riwayat Jenis Kunjungan dan Status Pulang
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT
    'Label riwayat jenis kunjungan dan status pulang pasien',
    'Menambahkan label informatif untuk tipe kunjungan (RME, Pelayanan, Lab, Imunisasi) serta status pulang rawat inap/jalan pada halaman detail pasien dan riwayat pemeriksaan RME.',
    'feature',
    '9d3bd09d',
    '2026-10-12 13:42:51',
    0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = '9d3bd09d');

-- 2. Peningkatan Performa Dashboard & Cache Busting
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT
    'Peningkatan dashboard dan cache busting script',
    'Optimasi render grafik pada dashboard admin dengan pengecekan elemen canvas, penyesuaian query kunjungan, serta penambahan cache busting otomatis untuk aset skrip dashboard.',
    'improvement',
    'ad381155',
    '2026-10-12 17:54:59',
    0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = 'ad381155');

-- 3. Modal Preview Foto Pasien dan Fallback Foto Kartu
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT
    'Preview modal foto pasien dan fallback gambar kartu',
    'Kartu pasien kini dilengkapi kemampuan preview foto resolusi penuh dalam modal dialog dan fallback otomatis ke foto tangkapan wajah jika foto profil utama belum tersedia.',
    'improvement',
    'f99e30c1',
    '2026-10-12 18:05:01',
    0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = 'f99e30c1');

-- 4. Fitur Penanda Operasi (Site Marking)
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT
    'Fitur penanda operasi (Site Marking) pada RME',
    'Modul penanda lokasi operasi dengan canvas gambar interaktif (coretan garis bebas & teks label lokasi tindakan) untuk template anatomi Wanita, Pria, Gigi & Mulut, Ekstremitas, dan Mata. Mendukung manajemen riwayat versi dokumen, pencatatan catatan klinis, cetak formulir, serta integrasi ke bundle klaim.',
    'feature',
    'e36bdea5',
    '2026-10-12 18:42:41',
    0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = 'e36bdea5');

-- 5. RME Satu Siklus & Resep Draft Terintegrasi BPJS
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT
    'Manajemen RME satu siklus dan resep draft terintegrasi',
    'Pembuatan dan rincian resep obat dapat disusun sebagai draft sebelum nomor kunjungan BPJS diterbitkan. Penggabungan seluruh terapi obat dilakukan otomatis saat simpan pemeriksaan, mencegah form bertumpuk dan menjamin integritas data resep.',
    'feature',
    '0db84071',
    '2026-10-12 19:00:00',
    0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = '0db84071');

-- 6. Riwayat Dokumen Penanda Operasi pada RME & Bundle Klaim
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT
    'Riwayat dokumen penanda operasi dan bundle klaim per versi',
    'Detail kunjungan pada riwayat pengobatan menampilkan dokumen penanda operasi yang tersimpan lengkap dengan gambar lazy load, catatan tindakan, pengguna pencatat, serta ekspor PDF per versi maupun semua versi ke bundle klaim.',
    'feature',
    '5782709e',
    '2026-10-12 19:13:02',
    0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = '5782709e');

-- 7. Monitoring Expired Obat Realtime
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT
    'Monitoring expired obat realtime (Tahun/Bulan/Hari & 3 Kategori)',
    'Menu baru pada modul Admin dan Farmasi untuk memantau masa kedaluwarsa obat & BMHP secara realtime (selisih tahun, bulan, hari). Dikelompokkan ke dalam 3 kategori standar: Aman (>6 bulan), Risiko (≤6 bulan / prioritas FEFO), dan Kadaluarsa/Lainnya (<0 hari / belum diset), dilengkapi kartu statistik interaktif dan filter lanjutan.',
    'feature',
    'a5b08dc1',
    '2026-10-13 09:25:59',
    0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = 'a5b08dc1');

-- 8. Penelusuran Pemakaian Obat Pasien
INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT
    'Penelusuran pemakaian obat pasien (Pasien siapa saja yang memakai obat)',
    'Menu baru pada modul Admin dan Farmasi untuk melacak pasien siapa saja yang telah diresepkan atau memakai obat tertentu. Menampilkan data pasien, No. RM, usia, dokter peresep, poliklinik/unit layanan, jumlah obat, aturan pakai (signa), serta modal rincian resep lengkap.',
    'feature',
    'e068e56a',
    '2026-10-13 09:48:56',
    0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = 'e068e56a');

COMMIT;
