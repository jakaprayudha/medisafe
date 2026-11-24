-- Asesmen Gizi & Nutrisi (module/admin/form_gizi, controller/visit/giziController.php).
-- Sebelumnya form gizi tidak menyimpan apa pun (tombol Simpan hanya console.log + pesan "berhasil").
-- 1 baris = 1 asesmen (awal / lanjutan) per kunjungan. Kolom ringkas untuk daftar/cetak, detail lengkap di `data` (JSON).
CREATE TABLE IF NOT EXISTS `pasien_gizi` (
  `id_gizi` INT NOT NULL AUTO_INCREMENT,
  `id_customer` INT NOT NULL,
  `visit_ID` VARCHAR(40) NOT NULL,
  `id_patient` INT DEFAULT NULL,
  `tanggal_asesmen` DATE NOT NULL,
  `nutrisionis` VARCHAR(150) DEFAULT NULL,
  `risiko_gizi` VARCHAR(20) DEFAULT NULL,
  `status` ENUM('Draft','Selesai') NOT NULL DEFAULT 'Draft',
  `diagnosis_pes` TEXT,
  `jenis_diet` VARCHAR(100) DEFAULT NULL,
  `data` JSON NOT NULL,
  `created_by` VARCHAR(150) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` VARCHAR(150) DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_gizi`),
  KEY `idx_gizi_visit` (`visit_ID`, `id_customer`),
  KEY `idx_gizi_patient` (`id_patient`, `id_customer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
