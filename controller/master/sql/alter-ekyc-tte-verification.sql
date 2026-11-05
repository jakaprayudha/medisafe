-- ============================================================
-- Migrasi eKYC TTE: upload KTP + liveness wajah + verifikasi
-- Jalankan pada tabel tte_ekyc yang sudah ada
-- ============================================================

ALTER TABLE tte_ekyc
    ADD COLUMN ktp_file VARCHAR(255) NULL AFTER nik,
    ADD COLUMN face_file VARCHAR(255) NULL AFTER ktp_file,
    ADD COLUMN status_verifikasi VARCHAR(20) NOT NULL DEFAULT 'belum' AFTER face_file,
    ADD COLUMN verified_at TIMESTAMP NULL AFTER status_verifikasi;
