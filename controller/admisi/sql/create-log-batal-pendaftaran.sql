-- Log pembatalan pendaftaran/antrean: siapa, kapan, alasan, dan data klinis yang sudah ada saat dibatalkan.
CREATE TABLE IF NOT EXISTS log_batal_pendaftaran (
  id_log BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  visit_ID VARCHAR(100) NOT NULL,
  id_customer INT NOT NULL,
  id_patient INT NULL,
  alasan TEXT NOT NULL,
  ada_asesmen TINYINT(1) NOT NULL DEFAULT 0,
  ada_cppt TINYINT(1) NOT NULL DEFAULT 0,
  rawat_inap TINYINT(1) NOT NULL DEFAULT 0,
  ringkasan_klinis TEXT NULL,
  dikonfirmasi TINYINT(1) NOT NULL DEFAULT 0,
  provider VARCHAR(10) NULL,
  hasil_bpjs VARCHAR(255) NULL,
  dibatalkan_oleh VARCHAR(100) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_log),
  KEY idx_visit (visit_ID),
  KEY idx_customer_tgl (id_customer, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
