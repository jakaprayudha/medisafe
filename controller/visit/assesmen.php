<?php
require '../../database/connect.php';

if (isset($_POST['simpan_pemeriksaan'])) {

   $rm = $_POST['nomor_rm'];
   $nomor_visit = $_POST['nomor_visit'];
   $id_patient = $_POST['id_patient'];


   $stmt = $koneksi->prepare("UPDATE pasien_visit SET
         kondisi_masuk = ?,
         tekanan_darah = ?,
         suhu = ?,
         nadi = ?,
         respirasi = ?,
         tinggi_badan = ?,
         berat_badan = ?,
         bmi = ?,
         bmi_keterangan = ?,

         anamnesa = ?,
         keluhan_penyerta = ?,
         riwayat_alergi = ?,
         riwayat_penyakit_pribadi = ?,
         riwayat_penyakit_sekarang = ?,
         riwayat_pengobatan = ?,
         pemeriksaan_fisik = ?,
         pemeriksaan_fungsional = ?,
         diagnosa = ?,
         tindakan = ?,
         edukasi = ?,
         visit_out = ?, 
         kondisi_keluar = ?

      WHERE visit_ID = ? AND id_patient = ?
   ");

   $stmt->bind_param(
      "ssssssssssssssssssssssss",

      $_POST['kondisi_masuk'],
      $_POST['tekanan_darah'],
      $_POST['suhu'],
      $_POST['nadi'],
      $_POST['respirasi'],
      $_POST['tinggi'],
      $_POST['berat'],
      $_POST['bmi'],
      $_POST['bmi_ket'],

      $_POST['keluhan_utama'], // masuk ke anamnesa
      $_POST['keluhan_penyerta'],
      $_POST['riwayat_alergi'],
      $_POST['riwayat_penyakit_pribadi'],
      $_POST['riwayat_penyakit_sekarang'],
      $_POST['riwayat_pengobatan'],
      $_POST['pemeriksaan_fisik'],
      $_POST['pemeriksaan_fungsional'],
      $_POST['diagnosa'],
      $_POST['tindakan'],
      $_POST['edukasi'],
      $_POST['cara_keluar'],
      $_POST['cara_keluar'],

      $nomor_visit,
      $id_patient
   );

   if ($stmt->execute()) {
      echo "<script>alert('Pemeriksaan berhasil disimpan');</script>";
   } else {
      echo "<script>alert('Gagal menyimpan');</script>";
   }

   $stmt->close();
}
