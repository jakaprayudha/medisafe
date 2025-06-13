<?php
require '../../database/connect.php'; // koneksi

if (isset($_POST['simpan_pemeriksaan'])) {
   $rm = $_POST['nomor_rm'];
   $nomor_visit = $_POST['nomor_visit'];

   // Gabungkan semua field menjadi array asosiatif
   $data = [
      'kondisi_masuk' => $_POST['kondisi_masuk'],
      'tekanan_darah' => $_POST['tekanan_darah'],
      'suhu' => $_POST['suhu'],
      'nadi' => $_POST['nadi'],
      'respirasi' => $_POST['respirasi'],
      'tinggi' => $_POST['tinggi'],
      'berat' => $_POST['berat'],
      'keluhan_utama' => $_POST['keluhan_utama'],
      'keluhan_penyerta' => $_POST['keluhan_penyerta'],
      'riwayat_alergi' => $_POST['riwayat_alergi'],
      'riwayat_penyakit_pribadi' => $_POST['riwayat_penyakit_pribadi'],
      'riwayat_penyakit_sekarang' => $_POST['riwayat_penyakit_sekarang'],
      'riwayat_pengobatan' => $_POST['riwayat_pengobatan'],
      'pemeriksaan_fisik' => $_POST['pemeriksaan_fisik'],
      'pemeriksaan_fungsional' => $_POST['pemeriksaan_fungsional'],
      'diagnosa' => $_POST['diagnosa'],
      'tindakan' => $_POST['tindakan'],
      'edukasi' => $_POST['edukasi'],
      'cara_keluar' => $_POST['cara_keluar']
   ];

   $json_data = json_encode($data);

   // Cek apakah data sudah ada
   $cek = $koneksi->prepare("SELECT id FROM pasien_resume WHERE nomor_visit = ?");
   $cek->bind_param("s", $nomor_visit);
   $cek->execute();
   $cek->store_result();

   if ($cek->num_rows > 0) {
      // Update
      $stmt = $koneksi->prepare("UPDATE pasien_resume SET pemeriksaan = ? WHERE nomor_visit = ?");
      $stmt->bind_param("ss", $json_data, $nomor_visit);
   } else {
      // Insert
      $stmt = $koneksi->prepare("INSERT INTO pasien_resume (nomor_visit, nomor_rm, pemeriksaan) VALUES (?, ?, ?)");
      $stmt->bind_param("sss", $nomor_visit, $rm, $json_data);
   }

   if ($stmt->execute()) {
      echo "<script>alert('Pemeriksaan berhasil disimpan.'); window.location.href='pemeriksaan_details?no=$nomor_visit&rm=$rm';</script>";
   } else {
      echo "<script>alert('Gagal menyimpan.');</script>";
   }

   $stmt->close();
   $cek->close();
}
