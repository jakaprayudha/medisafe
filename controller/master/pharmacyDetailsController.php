<?php
// Detail & ubah master obat (module/admin/pharmacy_details).
//   GET  ?no=ID_PHARMACY                -> { success, data }
//   POST _method=PUT, id_pharmacy, ...  -> ubah field yang dikirim
// Obat master bawaan (id_customer 0) tidak diubah langsung: saat pertama diubah dibuat salinan
// khusus faskes (respon new_id) dan master bawaan disembunyikan untuk faskes tsb.
include '../../database/connect.php';
require_once __DIR__ . '/pharmacyAkses.php';
require_once __DIR__ . '/marginHarga.php';
mysqli_report(MYSQLI_REPORT_OFF);
header("Content-Type: application/json; charset=utf-8");

function jawab(array $data, int $code = 200): void
{
   http_response_code($code);
   echo json_encode($data, JSON_UNESCAPED_UNICODE);
   exit;
}

if (empty($_SESSION['id_customer'])) jawab(["success" => false, "message" => "Session tidak valid / expired"], 401);
$idc = (int) $_SESSION['id_customer'];

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST' && isset($_POST['_method'])) $method = strtoupper($_POST['_method']);

if ($method === 'GET') {
   $row = obatFaskes($koneksi, (int) ($_GET['no'] ?? 0), $idc);
   jawab($row ? ["success" => true, "data" => $row] : ["success" => false, "message" => "Obat tidak ditemukan"], $row ? 200 : 404);
}

if ($method !== 'PUT') jawab(["success" => false, "message" => "Method $method tidak diizinkan"], 405);

$id = (int) ($_POST['id_pharmacy'] ?? 0);
$row = obatFaskes($koneksi, $id, $idc);
if (!$row) jawab(["success" => false, "message" => "Obat tidak ditemukan"], 404);

$teks = [
   'pharmacy_code', 'pharmacy_name_generic', 'pharmacy_name_trade', 'pharmacy_category', 'pharmacy_sub_category',
   'pharmcy_golongan', 'pharmcy_jenis_drugs', 'pharmacy_bentuk_sediaan', 'pharmacy_dosis', 'pharmacy_unit',
   'pharmacy_kemasan', 'pharmacy_supplier', 'pharmacy_factory', 'pharmacy_code_catalog', 'fornas', 'formularium_rs',
   'pharmacy_description', 'pharmacy_status',
];
$angka = ['pharmacy_buy', 'pharmacy_sale', 'stok_min', 'stok_max'];

$data = [];
foreach ($teks as $f) {
   if (isset($_POST[$f])) $data[$f] = trim((string) $_POST[$f]);
}
foreach ($angka as $f) {
   if (!isset($_POST[$f])) continue;
   $v = trim((string) $_POST[$f]);
   if ($v === '') {
      $data[$f] = null;
      continue;
   }
   if (!is_numeric($v) || (float) $v < 0) jawab(["success" => false, "message" => "Nilai $f harus angka 0 atau lebih"], 422);
   $data[$f] = (string) round((float) $v);
}
if (!$data) jawab(["success" => false, "message" => "Tidak ada data diupdate"], 422);
if (array_key_exists('pharmacy_name_generic', $data) && $data['pharmacy_name_generic'] === '') {
   jawab(["success" => false, "message" => "Nama generik wajib diisi"], 422);
}
if (isset($data['pharmacy_status']) && !in_array($data['pharmacy_status'], ['0', '1'], true)) {
   jawab(["success" => false, "message" => "Status tidak valid"], 422);
}
$min = array_key_exists('stok_min', $data) ? $data['stok_min'] : $row['stok_min'];
$max = array_key_exists('stok_max', $data) ? $data['stok_max'] : $row['stok_max'];
if ($min !== null && $max !== null && $max !== '' && (float) $max > 0 && (float) $min > (float) $max) {
   jawab(["success" => false, "message" => "Stok minimum tidak boleh lebih besar dari stok maksimum"], 422);
}

// ---------- margin aktif: harga jual dihitung dari harga beli (lihat Setting Margin) ----------
$hargaOtomatis = null;
if (array_key_exists('pharmacy_buy', $data) || array_key_exists('pharmacy_sale', $data) || array_key_exists('pharmacy_category', $data)) {
   $pct = marginKategori(marginFaskes($koneksi, $idc), $data['pharmacy_category'] ?? $row['pharmacy_category']);
   $beli = (float) (array_key_exists('pharmacy_buy', $data) ? $data['pharmacy_buy'] : $row['pharmacy_buy']);
   if ($pct !== null && $beli > 0) {
      $hargaOtomatis = hargaJualMargin($beli, $pct);
      $data['pharmacy_sale'] = (string) $hargaOtomatis;
   }
}

// ---------- obat milik faskes: update langsung ----------
if ($row['sumber'] === 'faskes') {
   $set = implode(', ', array_map(fn($f) => "$f = ?", array_keys($data)));
   $stmt = $koneksi->prepare("UPDATE ms_pharmacy SET $set WHERE id_pharmacy = ? AND id_customer = ?");
   $vals = array_merge(array_values($data), [$id, $idc]);
   $stmt->bind_param(str_repeat('s', count($data)) . 'ii', ...$vals);
   $ok = $stmt->execute();
   $err = $stmt->error;
   $stmt->close();
   if (!$ok) {
      error_log('[pharmacyDetailsController] ' . $err);
      jawab(["success" => false, "message" => "Gagal menyimpan data obat"], 500);
   }
   jawab(["success" => true, "message" => "Data obat berhasil diperbarui", "harga_jual" => $hargaOtomatis]);
}

// ---------- master bawaan: salin ke faskes + sembunyikan bawaan ----------
$koneksi->begin_transaction();
try {
   $user = (string) ($_SESSION['fullname'] ?? ($_SESSION['username'] ?? ''));
   $stmt = $koneksi->prepare("INSERT INTO ms_pharmacy_parrent (id_customer_real, parent_id, status_log, user) VALUES (?, ?, 'UPDATE', ?)");
   $stmt->bind_param("iis", $idc, $id, $user);
   if (!$stmt->execute()) throw new RuntimeException($stmt->error);
   $stmt->close();

   $baru = $row;
   unset($baru['id_pharmacy'], $baru['created_at'], $baru['sumber']);
   $baru['id_customer'] = $idc;
   foreach ($data as $k => $v) $baru[$k] = $v;
   $kolom = array_keys($baru);
   $stmt = $koneksi->prepare("INSERT INTO ms_pharmacy (" . implode(',', $kolom) . ") VALUES (" . implode(',', array_fill(0, count($kolom), '?')) . ")");
   $vals = array_values($baru);
   $stmt->bind_param(str_repeat('s', count($kolom)), ...$vals);
   if (!$stmt->execute()) throw new RuntimeException($stmt->error);
   $newId = $koneksi->insert_id;
   $stmt->close();
   $koneksi->commit();
   jawab(["success" => true, "message" => "Data obat disimpan sebagai salinan khusus faskes Anda", "new_id" => $newId, "harga_jual" => $hargaOtomatis]);
} catch (Throwable $e) {
   $koneksi->rollback();
   error_log('[pharmacyDetailsController] ' . $e->getMessage());
   jawab(["success" => false, "message" => "Gagal menyimpan data obat"], 500);
}
