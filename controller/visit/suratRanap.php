<?php
header('Content-Type: application/json');
require '../../database/connect.php'; // sesuaikan path-nya
require_once __DIR__ . '/../../utility/visitGuard.php';
require_once __DIR__ . '/../../utility/activityLog.php';

$input = json_decode(file_get_contents("php://input"), true);
$action = $input['action'] ?? '';
$data = $input['data'] ?? [];

if ($action === "save") {
   $visit_ID = mysqli_real_escape_string($koneksi, $data['visit_ID']);
   $id_patient = mysqli_real_escape_string($koneksi, $data['id_patient']);
   $number_letter = mysqli_real_escape_string($koneksi, $data['number_letter']);
   $letter_sign = mysqli_real_escape_string($koneksi, $data['letter_sign']);
   $letter_sign_position = mysqli_real_escape_string($koneksi, $data['letter_sign_position']);
   $tanggal_surat = date('Y-m-d');
   visitGuard($koneksi, 'visit_ID', (string) ($data['visit_ID'] ?? '')); // hanya kunjungan faskes sesi

   // cek apakah data sudah ada
   $cek = mysqli_query($koneksi, "SELECT * FROM pasien_surat_ranap WHERE visit_ID='$visit_ID' AND id_patient='$id_patient'");
   $srLama = mysqli_fetch_assoc($cek) ?: null;
   $srLog = function (string $ket) use ($koneksi, $srLama, $visit_ID, $id_patient) {
      $st = $koneksi->prepare("SELECT * FROM pasien_surat_ranap WHERE visit_ID = ? AND id_patient = ? LIMIT 1");
      $st->bind_param('ss', $visit_ID, $id_patient);
      $st->execute();
      $baru = $st->get_result()->fetch_assoc() ?: null;
      $st->close();
      [$l, $b] = $srLama && $baru ? logBeda($srLama, $baru) : [$srLama, $baru];
      if ($b) logAktivitas($koneksi, 'surat', $srLama ? 'ubah' : 'tambah', ['tipe' => 'pasien_surat_ranap', 'label' => 'Surat rawat inap ' . ($baru['number_letter'] ?? ''), 'visit_ID' => $visit_ID], $ket, $l, $b);
   };
   if ($srLama) {
      // update data
      $update = mysqli_query($koneksi, "UPDATE pasien_surat_ranap SET 
            number_letter='$number_letter',
            letter_sign='$letter_sign',
            letter_sign_position='$letter_sign_position',
            tanggal_surat='$tanggal_surat'
            WHERE visit_ID='$visit_ID' AND id_patient='$id_patient'");
      if ($update) {
         $srLog('Surat rawat inap diperbarui');
         echo json_encode(["status" => "success", "message" => "Data berhasil diperbarui."]);
      } else {
         echo json_encode(["status" => "error", "message" => "Gagal memperbarui data."]);
      }
   } else {
      // insert baru
      $insert = mysqli_query($koneksi, "INSERT INTO pasien_surat_ranap 
            (visit_ID, id_patient, number_letter, tanggal_surat, letter_sign, letter_sign_position, created_at)
            VALUES ('$visit_ID','$id_patient','$number_letter','$tanggal_surat','$letter_sign','$letter_sign_position', NOW())");
      if ($insert) {
         $srLog('Surat rawat inap dibuat');
         echo json_encode(["status" => "success", "message" => "Data berhasil disimpan."]);
      } else {
         echo json_encode(["status" => "error", "message" => "Gagal menyimpan data."]);
      }
   }
   exit;
} elseif ($action === "get") {
   $visit_ID = mysqli_real_escape_string($koneksi, $input['visit_ID'] ?? '');
   $id_patient = mysqli_real_escape_string($koneksi, $input['id_patient'] ?? '');
   $query = mysqli_query($koneksi, "SELECT * FROM pasien_surat_ranap WHERE visit_ID='$visit_ID' AND id_patient='$id_patient' LIMIT 1");
   if ($row = mysqli_fetch_assoc($query)) {
      echo json_encode(["status" => "success", "data" => $row]);
   } else {
      echo json_encode(["status" => "error", "message" => "Data tidak ditemukan."]);
   }
   exit;
}

echo json_encode(["status" => "error", "message" => "Aksi tidak valid."]);
