<?php
include '../../database/connect.php';

$data = json_decode(file_get_contents("php://input"), true);

$id_inspection = $data['id_inspection'] ?? null;
$items = $data['data'] ?? [];

header('Content-Type: application/json; charset=utf-8');
if (!$id_inspection || empty($items) || !is_array($items)) {
   echo json_encode([
      'status' => 'error',
      'message' => 'Data tidak lengkap'
   ]);
   exit;
}

// hanya permintaan lab milik faskes yang login (dulu: id_inspection faskes mana pun bisa ditulis)
$idcLab = (string) ($_SESSION['id_customer'] ?? '');
$id_inspection = (int) $id_inspection;
$cekLab = $koneksi->prepare("SELECT 1 FROM visit_inspection WHERE id_inspection = ? AND id_customer = ? LIMIT 1");
$cekLab->bind_param('is', $id_inspection, $idcLab);
$cekLab->execute();
$milik = (bool) $cekLab->get_result()->fetch_row();
$cekLab->close();
if ($idcLab === '' || !$milik) {
   http_response_code(403);
   echo json_encode([
      'status' => 'error',
      'message' => 'Permintaan lab tidak ditemukan untuk faskes ini.'
   ]);
   exit;
}

// tipe isian per parameter (master-lab-detail): numerik = hanya angka, teks / keduanya = bebas
$idsItem = [];
foreach ($items as $item) {
   if (is_array($item) && isset($item['id_item']) && ctype_digit((string) $item['id_item'])) $idsItem[] = (int) $item['id_item'];
}
$tipeItem = [];
if ($idsItem) {
   $ph = implode(',', array_fill(0, count($idsItem), '?'));
   $q = $koneksi->prepare("SELECT id, assemen, tipe_isian FROM laboratorium_item WHERE id IN ($ph)");
   $q->bind_param(str_repeat('i', count($idsItem)), ...$idsItem);
   $q->execute();
   foreach ($q->get_result()->fetch_all(MYSQLI_ASSOC) as $r) $tipeItem[(int) $r['id']] = $r;
   $q->close();
}
foreach ($items as $item) {
   if (!is_array($item) || !isset($item['id_item']) || !ctype_digit((string) $item['id_item'])) continue;
   $h = trim((string) ($item['hasil'] ?? ''));
   $t = $tipeItem[(int) $item['id_item']] ?? null;
   if ($t && $t['tipe_isian'] === 'numerik' && $h !== '' && !preg_match('/^-?\d+([.,]\d+)?$/', $h)) {
      http_response_code(422);
      echo json_encode(['status' => 'error', 'message' => 'Parameter "' . $t['assemen'] . '" hanya menerima angka (contoh: 12,5). Nilai "' . mb_substr($h, 0, 30) . '" tidak valid.']);
      exit;
   }
}
foreach ($items as $item) {

   if (!is_array($item) || !isset($item['id_item']) || !ctype_digit((string) $item['id_item'])) continue;
   $id_item = (int) $item['id_item'];
   $hasil   = mb_substr(trim((string) ($item['hasil'] ?? '')), 0, 255);

   // 🔹 cek apakah sudah ada
   $check = $koneksi->prepare("
      SELECT id FROM laboratorium_result 
      WHERE id_inspection=? AND id_item=?
   ");
   $check->bind_param("ii", $id_inspection, $id_item);
   $check->execute();
   $res = $check->get_result();

   if ($res->num_rows > 0) {

      // 🔁 UPDATE
      $stmt = $koneksi->prepare("
         UPDATE laboratorium_result 
         SET hasil=? 
         WHERE id_inspection=? AND id_item=?
      ");
      $stmt->bind_param("sii", $hasil, $id_inspection, $id_item);
   } else {

      // ➕ INSERT
      $stmt = $koneksi->prepare("
         INSERT INTO laboratorium_result (id_inspection, id_item, hasil)
         VALUES (?, ?, ?)
      ");
      $stmt->bind_param("iis", $id_inspection, $id_item, $hasil);
   }

   $stmt->execute();
}

echo json_encode([
   'status' => 'success',
   'message' => 'Hasil berhasil disimpan'
]);
