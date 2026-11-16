-- ============================================================
-- Form Racikan (module/admin/permintaan_farmasi -> tombol "+ Racikan")
-- Data racikan per tiket + template racikan yang bisa dipakai ulang.
-- MySQL 8+/9: ADD COLUMN memakai ALGORITHM=INSTANT (tanpa copy tabel).
-- ============================================================

-- Data racikan di tiket permintaan farmasi
ALTER TABLE permintaan_pharmacy
    ADD COLUMN rck_nama         VARCHAR(255) NULL COMMENT 'Nama racikan, mis. Batuk',
    ADD COLUMN rck_tipe         VARCHAR(10)  NULL COMMENT 'DTD / NON_DTD',
    ADD COLUMN rck_frek_kali    VARCHAR(10)  NULL COMMENT 'Frekuensi: berapa kali',
    ADD COLUMN rck_frek_dosis   VARCHAR(10)  NULL COMMENT 'Frekuensi: dosis per pemberian',
    ADD COLUMN rck_frek_periode VARCHAR(20)  NULL COMMENT 'Frekuensi: Hari/Minggu/Bulan',
    ALGORITHM=INSTANT;

-- Jumlah bahan seperti diinput dokter.
-- DTD = per bungkus, NON_DTD = total. Kolom qty tetap total (dipakai stok, billing, BPJS).
ALTER TABLE permintaan_pharmacy_details
    ADD COLUMN qty_racik DECIMAL(10,2) NULL COMMENT 'Jumlah input racikan (DTD: per bungkus)',
    ALGORITHM=INSTANT;

-- Template racikan per klinik
CREATE TABLE IF NOT EXISTS ms_racikan_template (
    id_template      INT AUTO_INCREMENT PRIMARY KEY,
    id_customer      INT          NOT NULL,
    nama_racikan     VARCHAR(255) NOT NULL,
    tipe_racik       VARCHAR(10)  NOT NULL DEFAULT 'NON_DTD',
    rck_jumlah       VARCHAR(20)  NULL,
    rck_satuan       VARCHAR(100) NULL,
    rck_signa        VARCHAR(255) NULL,
    frek_kali        VARCHAR(10)  NULL,
    frek_dosis       VARCHAR(10)  NULL,
    frek_periode     VARCHAR(20)  NULL,
    created_user     VARCHAR(255) NULL,
    created_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP    NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_racikan_template_nama (id_customer, nama_racikan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS ms_racikan_template_item (
    id_item      INT AUTO_INCREMENT PRIMARY KEY,
    id_template  INT           NOT NULL,
    id_pharmacy  INT           NOT NULL,
    qty          DECIMAL(10,2) NOT NULL,
    catatan      TEXT          NULL,
    urutan       INT           NOT NULL DEFAULT 0,
    INDEX idx_racikan_template_item (id_template)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- ROLLBACK (kalau perlu dibatalkan)
-- ------------------------------------------------------------
-- DROP TABLE ms_racikan_template_item;
-- DROP TABLE ms_racikan_template;
-- ALTER TABLE permintaan_pharmacy_details DROP COLUMN qty_racik;
-- ALTER TABLE permintaan_pharmacy
--     DROP COLUMN rck_nama, DROP COLUMN rck_tipe, DROP COLUMN rck_frek_kali,
--     DROP COLUMN rck_frek_dosis, DROP COLUMN rck_frek_periode;
