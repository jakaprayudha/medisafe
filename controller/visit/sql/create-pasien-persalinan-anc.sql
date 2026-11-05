-- ============================================================
-- Persalinan / ANC (Antenatal Care) pada RME
-- Asuhan kehamilan per kunjungan, dapat menyertakan gambar
-- (mis. foto USG / dokumentasi) pada setiap catatan kontrol.
-- ============================================================

CREATE TABLE IF NOT EXISTS pasien_persalinan_anc (
    id_anc              INT AUTO_INCREMENT PRIMARY KEY,
    anc_number          VARCHAR(50)  NOT NULL,
    id_visit            VARCHAR(100) NOT NULL,
    nomor_rm            VARCHAR(50)  NULL,
    id_customer         VARCHAR(255) NULL,
    tanggal_kontrol     DATE         NULL,
    hpht                DATE         NULL,
    taksiran_persalinan DATE         NULL,
    usia_gestasi        VARCHAR(50)  NULL,
    gravida             VARCHAR(20)  NULL,
    para                VARCHAR(20)  NULL,
    abortus             VARCHAR(20)  NULL,
    tinggi_fundus       VARCHAR(50)  NULL,
    presentasi          VARCHAR(100) NULL,
    djj                 VARCHAR(50)  NULL,
    tekanan_darah       VARCHAR(50)  NULL,
    berat_badan         VARCHAR(50)  NULL,
    protein_urine       VARCHAR(50)  NULL,
    edema               VARCHAR(50)  NULL,
    catatan             TEXT         NULL,
    gambar              VARCHAR(255) NULL,
    created_at          TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by          VARCHAR(150) NULL,
    INDEX idx_anc_visit (id_visit),
    INDEX idx_anc_number (anc_number),
    INDEX idx_anc_customer (id_customer)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
