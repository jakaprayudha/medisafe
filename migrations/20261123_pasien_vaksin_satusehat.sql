-- Vaksinasi -> SATUSEHAT (FHIR Immunization). Dipakai controller/visit/vaksin.php & controller/satusehat/immunizationService.php
-- Kolom lama (vaksin_name, vaksin_date, vaksin_dosis, vaksin_notes) tetap; vaksin_dosis = dosis ke- (angka).
ALTER TABLE pasien_vaksin
  ADD COLUMN vaksin_time TIME NULL AFTER vaksin_date,
  ADD COLUMN kfa_code VARCHAR(20) NULL COMMENT 'kode KFA vaksin' AFTER vaksin_name,
  ADD COLUMN reason_code VARCHAR(20) NULL COMMENT 'immunization-reason: IM-Dasar, IM-Baduta, ...',
  ADD COLUMN route_code VARCHAR(30) NULL COMMENT 'WHO ATC route: inj.intramuscular, O, ...',
  ADD COLUMN dose_ml DECIMAL(6,2) NULL COMMENT 'jumlah dosis (mL)',
  ADD COLUMN lot_number VARCHAR(50) NULL COMMENT 'nomor batch',
  ADD COLUMN expiration_date DATE NULL,
  ADD COLUMN ss_status ENUM('belum','terkirim','gagal','dibatalkan') NOT NULL DEFAULT 'belum' COMMENT 'status kirim SATUSEHAT',
  ADD COLUMN ss_id VARCHAR(64) NULL COMMENT 'id Immunization di SATUSEHAT',
  ADD COLUMN ss_error TEXT NULL,
  ADD COLUMN ss_attempts INT NOT NULL DEFAULT 0,
  ADD COLUMN ss_last_try DATETIME NULL,
  ADD COLUMN ss_sent_at DATETIME NULL,
  ADD INDEX idx_vaksin_visit (id_visit),
  ADD INDEX idx_vaksin_ss (ss_status);
