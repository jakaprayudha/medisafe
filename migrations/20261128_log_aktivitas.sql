-- Log aktivitas umum (login, user, pasien, gabung RME, CPPT, resume medis, diagnosa/PCare, hasil lab, switch user).
-- Diisi lewat utility/activityLog.php -> logAktivitas(); dibaca di menu admin module/admin/log_aktivitas.
-- Tabel juga dibuat otomatis oleh logAktivitasSiapkan() bila belum ada. Password/token tidak pernah disimpan.
CREATE TABLE IF NOT EXISTS log_aktivitas (
   id_log BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
   id_customer INT NULL,
   modul VARCHAR(30) NOT NULL,
   aksi VARCHAR(40) NOT NULL,
   ref_tipe VARCHAR(30) NULL,
   ref_id VARCHAR(100) NULL,
   ref_label VARCHAR(200) NULL,
   visit_ID VARCHAR(100) NULL,
   keterangan TEXT NULL,
   data_lama MEDIUMTEXT NULL,
   data_baru MEDIUMTEXT NULL,
   uid_user VARCHAR(100) NULL,
   username VARCHAR(100) NULL,
   fullname VARCHAR(150) NULL,
   roles VARCHAR(50) NULL,
   pelaku_asli VARCHAR(150) NULL,
   ip VARCHAR(45) NULL,
   user_agent VARCHAR(255) NULL,
   created_at DATETIME NOT NULL,
   PRIMARY KEY (id_log),
   KEY idx_customer_waktu (id_customer, created_at),
   KEY idx_modul (id_customer, modul, created_at),
   KEY idx_ref (ref_tipe, ref_id),
   KEY idx_visit (visit_ID),
   KEY idx_user (username, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
