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
   $bmi = $_POST['bmi'];
   $bmi_ket = $_POST['bmi_ket'];
   $diagnosa = $_POST['diagnosa'];
   $anamnesa_text = $_POST['anamnesa_text'];

   $koneksi->begin_transaction();
   try {
      // 1. Update pemeriksaan perawat/dokter
      $stmt = $koneksi->prepare("UPDATE visit_pemeriksaan SET 
      kondisi_masuk=?, 
      tekanan_darah=?, 
      suhu=?, 
      nadi=?, 
      respirasi=?, 
      tinggi=?, 
      berat=?, 
      analyst=?, 
      riwayat_konsumsi=?, 
      pemeriksaan_fisik=?, 
      bmi=?, 
      bmi_ket=?,
      diagnosa =?,
      anamnesa =?
WHERE nomor_rm=? AND nomor_visit=?");

      $stmt->bind_param(
         "ssssdddsssssssss",
         $kondisi_masuk,     // s
         $tekanan_darah,     // s
         $suhu,              // s
         $nadi,              // s
         $respirasi,         // d
         $tinggi,            // d
         $berat,             // d
         $analyst,           // s
         $riwayat_konsumsi,  // s
         $pemeriksaan_fisik, // s
         $bmi,               // d
         $bmi_ket,           // s
         $diagnosa,
         $anamnesa_text,
         $nomor_rm,          // s (WHERE)
         $nomor_visit        // s (WHERE)
      );

      $stmt->execute();
      $stmt->close();

      // 2. Simpan anamnesa
      // 2. Hapus anamnesa lama lalu simpan ulang
      $stmt = $koneksi->prepare("DELETE FROM visit_anamnesa WHERE nomor_visit=?");
      $stmt->bind_param("s", $nomor_visit);
      $stmt->execute();
      $stmt->close();

      if (isset($_POST['anamnesa'])) {
         foreach ($_POST['anamnesa'] as $id_anamnesa => $detail) {
            $stmt = $koneksi->prepare("INSERT INTO visit_anamnesa (nomor_visit, id_anamnesa_detail, detail) VALUES (?, ?, ?)");
            $stmt->bind_param("sis", $nomor_visit, $id_anamnesa, $detail);
            $stmt->execute();
            $stmt->close();
         }
      }

      // 3. Simpan terapi
      // 3. Hapus terapi lama lalu simpan ulang
      $stmt = $koneksi->prepare("DELETE FROM visit_terapi WHERE nomor_visit=?");
      $stmt->bind_param("s", $nomor_visit);
      $stmt->execute();
      $stmt->close();

      if (isset($_POST['terapi'])) {
         foreach ($_POST['terapi'] as $id_terapi => $detail) {
            // cek kalau kosong, skip
            if (empty(trim($detail))) continue;

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
