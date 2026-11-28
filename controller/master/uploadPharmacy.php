<?php
// Upload gambar / dokumen obat (module/admin/pharmacy_details).
//   POST id_pharmacy, gambar (jpg/png) dan/atau dokumen (jpg/png/pdf), maks 2 MB
//   POST id_pharmacy, hapus=gambar|dokumen  -> hapus file
// Hanya obat milik faskes (master bawaan harus disimpan dulu agar menjadi salinan faskes).
include "../../database/connect.php";
require_once __DIR__ . '/pharmacyAkses.php';
require_once __DIR__ . '/../../utility/activityLog.php';
header("Content-Type: application/json; charset=utf-8");

function jawab(array $data, int $code = 200): void
{
   http_response_code($code);
   echo json_encode($data);
   exit;
}

function upLog(string $aksi, array $lama, array $baru, string $ket): void
{
   global $koneksi, $id, $row;
   logAktivitas($koneksi, 'stok', $aksi, ['tipe' => 'obat', 'id' => $id,
      'label' => (string) (($row['pharmacy_name_trade'] ?? '') ?: ($row['pharmacy_name_generic'] ?? ('Obat #' . $id)))], $ket, $lama, $baru);
}

$idc = (int) ($_SESSION['id_customer'] ?? 0);
$id = (int) ($_POST['id_pharmacy'] ?? ($_POST['pharmacy_number'] ?? 0));
$row = $idc > 0 ? obatFaskes($koneksi, $id, $idc) : null;
if (!$row) jawab(["status" => "error", "message" => "Obat tidak ditemukan."], 404);
if ($row['sumber'] !== 'faskes') {
   jawab(["status" => "error", "message" => "Obat ini master bawaan. Simpan data obat terlebih dahulu agar menjadi data faskes Anda, lalu unggah dokumen."], 409);
}

$uploadDir = __DIR__ . "/../../uploads/pharmacy/";
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$kolom = ["gambar" => "pharmacy_image", "dokumen" => "pharmacy_docs"];

// ---------- hapus ----------
if (isset($_POST['hapus'])) {
   $f = (string) $_POST['hapus'];
   if (!isset($kolom[$f])) jawab(["status" => "error", "message" => "Jenis file tidak valid."], 422);
   $lama = basename((string) $row[$kolom[$f]]);
   if ($lama !== '' && is_file($uploadDir . $lama)) @unlink($uploadDir . $lama);
   $stmt = $koneksi->prepare("UPDATE ms_pharmacy SET {$kolom[$f]} = NULL WHERE id_pharmacy = ? AND id_customer = ?");
   $stmt->bind_param("ii", $id, $idc);
   $stmt->execute();
   $stmt->close();
   upLog('hapus_file', [$kolom[$f] => $row[$kolom[$f]] ?? null], [$kolom[$f] => null], 'Hapus ' . $f . ' obat');
   jawab(["status" => "success", "message" => "File dihapus."]);
}

// ---------- upload ----------
$izin = [
   "gambar" => ['image/jpeg' => 'jpg', 'image/png' => 'png'],
   "dokumen" => ['image/jpeg' => 'jpg', 'image/png' => 'png', 'application/pdf' => 'pdf'],
];
$finfo = new finfo(FILEINFO_MIME_TYPE);
$set = [];
foreach ($kolom as $field => $col) {
   if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) continue;
   if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) jawab(["status" => "error", "message" => "Upload $field gagal."], 422);
   if ($_FILES[$field]['size'] > 2 * 1024 * 1024) jawab(["status" => "error", "message" => "Ukuran file maksimal 2 MB."], 422);
   // jenis file ditentukan dari isi, bukan nama file (cegah upload skrip)
   $mime = $finfo->file($_FILES[$field]['tmp_name']);
   if (!isset($izin[$field][$mime])) {
      jawab(["status" => "error", "message" => $field === 'gambar' ? "Gambar harus JPG atau PNG." : "Dokumen harus JPG, PNG atau PDF."], 422);
   }
   $nama = $field . '_' . $id . '_' . time() . '.' . $izin[$field][$mime];
   if (!move_uploaded_file($_FILES[$field]['tmp_name'], $uploadDir . $nama)) jawab(["status" => "error", "message" => "Gagal menyimpan file."], 500);
   $lama = basename((string) $row[$col]);
   if ($lama !== '' && is_file($uploadDir . $lama)) @unlink($uploadDir . $lama);
   $set[$col] = $nama;
}
if (!$set) jawab(["status" => "error", "message" => "Tidak ada file yang diupload."], 422);

$sql = "UPDATE ms_pharmacy SET " . implode(', ', array_map(fn($c) => "$c = ?", array_keys($set))) . " WHERE id_pharmacy = ? AND id_customer = ?";
$stmt = $koneksi->prepare($sql);
$vals = array_merge(array_values($set), [$id, $idc]);
$stmt->bind_param(str_repeat('s', count($set)) . 'ii', ...$vals);
$ok = $stmt->execute();
$stmt->close();
if ($ok) upLog('upload_file', array_intersect_key($row, $set), $set, 'Upload ' . implode(' & ', array_keys(array_intersect($kolom, array_keys($set)))) . ' obat');
jawab($ok ? ["status" => "success", "message" => "File berhasil diupload.", "files" => $set] : ["status" => "error", "message" => "Gagal menyimpan ke database."], $ok ? 200 : 500);
