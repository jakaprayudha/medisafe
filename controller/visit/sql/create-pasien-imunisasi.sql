-- ============================================================
-- Imunisasi Pasien
-- Riwayat pemberian imunisasi pada kunjungan (module/admin/kunjungan)
-- ============================================================

CREATE TABLE IF NOT EXISTS pasien_imunisasi (
    id_imunisasi     INT AUTO_INCREMENT PRIMARY KEY,
    imunisasi_number VARCHAR(50)  NOT NULL,
    id_visit         VARCHAR(100) NOT NULL,
    imunisasi_name   VARCHAR(150) NOT NULL,
    imunisasi_date   DATE         NULL,
    imunisasi_dosis  VARCHAR(50)  NULL,
    imunisasi_batch  VARCHAR(100) NULL,
    imunisasi_notes  TEXT         NULL,
    created_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_imunisasi_visit (id_visit),
    INDEX idx_imunisasi_number (imunisasi_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
