-- Log perubahan akun PCare (halaman module/admin/setting_bpjs).
-- Password TIDAK disimpan di log; hanya dicatat bahwa password diganti.
CREATE TABLE IF NOT EXISTS setting_pcare_log (
   id INT NOT NULL AUTO_INCREMENT,
   id_customer INT NOT NULL,
   username_lama VARCHAR(100) NULL,
   username_baru VARCHAR(100) NULL,
   password_diganti TINYINT(1) NOT NULL DEFAULT 0,
   diubah_oleh VARCHAR(150) NULL,
   uid_user VARCHAR(100) NULL,
   ip VARCHAR(45) NULL,
   created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
   PRIMARY KEY (id),
   KEY idx_customer_waktu (id_customer, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
