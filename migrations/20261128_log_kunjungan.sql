-- Log aktivitas kunjungan pasien (pendaftaran, perubahan, pembatalan, penghapusan, pulang, pembayaran).
-- Diisi lewat utility/visitLog.php -> logKunjungan(); dibaca di menu admin module/admin/log_kunjungan.
-- Tabel juga dibuat otomatis oleh logKunjunganSiapkan() bila belum ada.
CREATE TABLE IF NOT EXISTS log_kunjungan (
   id_log BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
   id_customer INT NOT NULL,
   visit_ID VARCHAR(100) NULL,
   id_visit INT NULL,
   id_patient VARCHAR(50) NULL,
   nama_pasien VARCHAR(150) NULL,
   aksi VARCHAR(30) NOT NULL,
   sumber VARCHAR(50) NULL,
   keterangan TEXT NULL,
   data_lama TEXT NULL,
   data_baru TEXT NULL,
   uid_user VARCHAR(100) NULL,
   username VARCHAR(100) NULL,
   fullname VARCHAR(150) NULL,
   roles VARCHAR(50) NULL,
   pelaku_asli VARCHAR(150) NULL,
   ip VARCHAR(45) NULL,
   created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
   PRIMARY KEY (id_log),
   KEY idx_customer_waktu (id_customer, created_at),
   KEY idx_visit (visit_ID),
   KEY idx_aksi (id_customer, aksi, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
