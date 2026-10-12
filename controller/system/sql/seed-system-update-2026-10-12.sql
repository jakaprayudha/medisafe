START TRANSACTION;

INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT
    'Perbaikan simpan odontogram dan diagnosa ICD-10 BPJS',
    'Simpan odontogram menggunakan ID klinik dari sesi login, bukan nomor rekam medis. Diagnosa pada odontogram dan RME dapat dicari melalui ICD-10 BPJS dengan minimal tiga karakter. Validasi input, tampilan error, serta proses edit dan hapus diperbaiki.',
    'fix',
    '7856d69e',
    '2026-10-12 13:13:57',
    0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = '7856d69e');

INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT
    'Foto master pasien tampil pada kartu pasien',
    'Kartu pasien menampilkan foto yang tersimpan di master pasien. Jika foto belum ada atau gagal dimuat, avatar menampilkan inisial nama pasien. Pengambilan data kartu pasien dan pesan kegagalannya diperbaiki.',
    'improvement',
    'af475e5f',
    '2026-10-12 13:24:30',
    0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = 'af475e5f');

INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT
    'Simpan otomatis foto wajah kunjungan',
    'Pada registrasi poliklinik, foto kunjungan otomatis tersimpan saat wajah cocok dengan rekaman master pasien dan posisi kamera sudah pas. Simpan ganda dicegah. Jika belum ada master wajah atau penyimpanan gagal, tombol pengambilan manual tetap tersedia. Foto master tidak ditimpa.',
    'feature',
    '64193ee0',
    '2026-10-12 13:28:52',
    0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = '64193ee0');

INSERT INTO system_update_log (title, description, type, version, created_at, is_read)
SELECT
    'Kunjungan batal tidak tampil pada riwayat pengobatan',
    'Riwayat Pengobatan pada RME, termasuk rawat inap, dan detail pasien tidak lagi menampilkan kunjungan berstatus batal. Kunjungan lain tetap ditampilkan dan data pembatalan tidak dihapus.',
    'fix',
    'c3ee77ff',
    '2026-10-12 13:35:51',
    0
WHERE NOT EXISTS (SELECT 1 FROM system_update_log WHERE version = 'c3ee77ff');

COMMIT;
