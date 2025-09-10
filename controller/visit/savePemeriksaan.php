<?php
include '../../database/connect.php'; // file koneksi

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   $nomor_rm = $_POST['nomor_rm'];
   $nomor_visit = $_POST['nomor_visit'];
   $kondisi_masuk = $_POST['kondisi_masuk'];
   $tekanan_darah = $_POST['tekanan_darah'];
   $suhu = $_POST['suhu'];
   $nadi = $_POST['nadi'];
   $respirasi = $_POST['respirasi'];
   $tinggi = $_POST['tinggi'];
   $berat = $_POST['berat'];
   $analyst = $_POST['analyst'];
   $riwayat_konsumsi = $_POST['riwayat_konsumsi'];
   $pemeriksaan_fisik = $_POST['pemeriksaan_fisik'];

   $koneksi->begin_transaction();
   try {
      // 1. Simpan pemeriksaan perawat/dokter
      $stmt = $koneksi->prepare("INSERT INTO visit_pemeriksaan 
            (nomor_rm, nomor_visit, kondisi_masuk, tekanan_darah, suhu, nadi, respirasi, tinggi, berat, analyst, riwayat_konsumsi, pemeriksaan_fisik)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
      $stmt->bind_param(
         "ssssddddssss",
         $nomor_rm,
         $nomor_visit,
         $kondisi_masuk,
         $tekanan_darah,
         $suhu,
         $nadi,
         $respirasi,
         $tinggi,
         $berat,
         $analyst,
         $riwayat_konsumsi,
         $pemeriksaan_fisik
      );
      $stmt->execute();
      $stmt->close();

      // 2. Simpan anamnesa
      if (isset($_POST['anamnesa'])) {
         foreach ($_POST['anamnesa'] as $id_anamnesa => $detail) {
            $stmt = $koneksi->prepare("INSERT INTO visit_anamnesa (nomor_visit, id_anamnesa_detail, detail) VALUES (?, ?, ?)");
            $stmt->bind_param("sis", $nomor_visit, $id_anamnesa, $detail);
            $stmt->execute();
            $stmt->close();
         }
      }

      // 3. Simpan terapi
      if (isset($_POST['terapi'])) {
         foreach ($_POST['terapi'] as $id_terapi => $detail) {
            $stmt = $koneksi->prepare("INSERT INTO visit_terapi (nomor_visit, id_terapi, detail) VALUES (?, ?, ?)");
            $stmt->bind_param("sis", $nomor_visit, $id_terapi, $detail);
            $stmt->execute();
            $stmt->close();
         }
      }

      $koneksi->commit();
      echo json_encode(['status' => 'success', 'message' => 'Data pemeriksaan berhasil disimpan']);
   } catch (Exception $e) {
      $koneksi->rollback();
      echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
   }
}
