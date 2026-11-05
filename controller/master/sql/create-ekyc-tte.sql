-- ============================================================
-- eKYC TTE (Tanda Tangan Elektronik)
-- Registrasi identitas pemohon TTE: nama, email, no HP, NIK
-- ============================================================

CREATE TABLE IF NOT EXISTS tte_ekyc (
    id_ekyc           INT AUTO_INCREMENT PRIMARY KEY,
    id_customer       VARCHAR(255) NOT NULL,
    nama              VARCHAR(150) NOT NULL,
    email             VARCHAR(150) NULL,
    nomor_hp          VARCHAR(30)  NULL,
    nik               VARCHAR(20)  NOT NULL,
    ktp_file          VARCHAR(255) NULL,
    face_file         VARCHAR(255) NULL,
    status_verifikasi VARCHAR(20)  NOT NULL DEFAULT 'belum',
    verified_at       TIMESTAMP    NULL,
    status            TINYINT(1)   NOT NULL DEFAULT 1,
    created_at        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by        VARCHAR(150) NULL,
    INDEX idx_tte_ekyc_customer (id_customer),
    INDEX idx_tte_ekyc_nik (nik)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
