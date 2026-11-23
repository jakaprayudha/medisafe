<?php
require '../../database/connect.php';

if (isset($_POST['simpan_pemeriksaan'])) {

   // dipakai bersama oleh beberapa halaman RME (form POST biasa)
   $in = fn($k) => trim((string) ($_POST[$k] ?? ''));
   $rm = $in('nomor_rm');
   $nomor_visit = $in('nomor_visit');
   $id_patient = $in('id_patient');
   $idCustomerAsesmen = (string) ($_SESSION['id_customer'] ?? '');
   $diagnosa_sekunder = isset($_POST['diagnosa_sekunder']) && is_array($_POST['diagnosa_sekunder'])
      ? implode(',', array_filter(array_map('trim', $_POST['diagnosa_sekunder']), 'strlen'))
      : '';
   // sebagian halaman mengirim "bmi_keterangan", handler dulu hanya membaca "bmi_ket" -> keterangan BMI tidak tersimpan
   $bmiKet = $in('bmi_ket') !== '' ? $in('bmi_ket') : $in('bmi_keterangan');


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
         kondisi_keluar = ?,
         saturasi = ?,
         diagnosa_sekunder = ?
      WHERE visit_ID = ? AND id_patient = ? AND id_customer = ?
   ");

   $stmt->bind_param(
      "sssssssssssssssssssssssssss",

      $in('kondisi_masuk'),
      $in('tekanan_darah'),
      $in('suhu'),
      $in('nadi'),
      $in('respirasi'),
      $in('tinggi'),
      $in('berat'),
      $in('bmi'),
      $bmiKet,

      $in('keluhan_utama'), // masuk ke anamnesa
      $in('keluhan_penyerta'),
      $in('riwayat_alergi'),
      $in('riwayat_penyakit_pribadi'),
      $in('riwayat_penyakit_sekarang'),
      $in('riwayat_pengobatan'),
      $in('pemeriksaan_fisik'),
      $in('pemeriksaan_fungsional'),
      $in('diagnosa'),
      $in('tindakan'),
      $in('edukasi'),
      $in('cara_keluar'),
      $in('cara_keluar'),
      $in('saturasi'),
      $diagnosa_sekunder,

      $nomor_visit,
      $id_patient,
      $idCustomerAsesmen
   );

   $asesmenTersimpan = $stmt->execute() && $stmt->affected_rows >= 0;
   // halaman yang mendefinisikan ASESMEN_TANPA_ALERT menampilkan notifikasinya sendiri (tanpa <script> sebelum <!doctype>)
   if (!defined('ASESMEN_TANPA_ALERT')) {
      echo $asesmenTersimpan ? "<script>alert('Pemeriksaan berhasil disimpan');</script>" : "<script>alert('Gagal menyimpan');</script>";
   }

   $stmt->close();
}
