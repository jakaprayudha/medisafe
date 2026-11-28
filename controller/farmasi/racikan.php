<?php
// Form racikan per tiket permintaan farmasi (module/admin/permintaan_farmasi -> "+ Racikan")
//   GET  ?id=<id_permintaan_farmasi>  -> data racikan + bahan
//   GET  ?obat=1&q=<cari>             -> daftar obat untuk select2
//   GET  ?templates=1&q=<cari>        -> daftar template racikan klinik
//   GET  ?template=<id_template>      -> isi template
//   POST (JSON)                       -> simpan racikan (+ template opsional)
include __DIR__ . '/../../database/connect.php';
if (session_status() === PHP_SESSION_NONE) {
   session_start();
}
header('Content-Type: application/json');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

const RACIKAN_TIPE = ['DTD', 'NON_DTD'];
const RACIKAN_PERIODE = ['Hari', 'Minggu', 'Bulan'];

function jsonKeluar(array $data, int $code = 200): void
{
   http_response_code($code);
   echo json_encode($data);
   exit;
}

function gagal(string $pesan, int $code = 422): void
{
   jsonKeluar(['status' => 'error', 'message' => $pesan], $code);
}

$idCustomer = (string) ($_SESSION['id_customer'] ?? '');
if ($idCustomer === '') {
   gagal('Session tidak ditemukan, silakan login kembali.', 401);
}

// Obat yang boleh dipakai klinik ini (sama dengan daftar di permintaan_farmasi_details)
function obatAksesSql(): string
{
   return "(
         p.id_customer = ?
         OR (p.id_customer = 0 AND NOT EXISTS (
            SELECT 1 FROM ms_pharmacy_parrent l2
            WHERE l2.parent_id = p.id_pharmacy AND l2.id_customer_real = ?))
      )
      AND NOT EXISTS (
         SELECT 1 FROM ms_pharmacy_parrent l3
         WHERE l3.parent_id = p.id_pharmacy AND l3.id_customer_real = ? AND l3.status_log = 'DELETE')
      AND p.pharmacy_name_generic IS NOT NULL
      AND TRIM(p.pharmacy_name_generic) != ''";
}

function namaObat(array $o): string
{
   $nama = trim((string) ($o['pharmacy_name_generic'] ?? ''));
   $dagang = trim((string) ($o['pharmacy_name_trade'] ?? ''));
   return $dagang !== '' && strcasecmp($dagang, $nama) !== 0 ? "$nama ($dagang)" : $nama;
}

function ambilTiket(mysqli $koneksi, int $id, string $idCustomer, bool $kunci = false): ?array
{
   $stmt = $koneksi->prepare("SELECT * FROM permintaan_pharmacy
      WHERE id_permintaan_farmasi = ? AND id_customer = ?" . ($kunci ? ' FOR UPDATE' : ''));
   $stmt->bind_param('is', $id, $idCustomer);
   $stmt->execute();
   $tiket = $stmt->get_result()->fetch_assoc();
   $stmt->close();
   return $tiket ?: null;
}

function isRacikan(array $tiket): bool
{
   return strtolower(trim((string) $tiket['tipe_obat'])) === 'racikan';
}

try {
   switch ($_SERVER['REQUEST_METHOD']) {
      case 'GET':
         if (isset($_GET['obat'])) {
            cariObat($koneksi, $idCustomer);
         } elseif (isset($_GET['templates'])) {
            daftarTemplate($koneksi, $idCustomer);
         } elseif (isset($_GET['template'])) {
            ambilTemplate($koneksi, $idCustomer, (int) $_GET['template']);
         } else {
            ambilRacikan($koneksi, $idCustomer, (int) ($_GET['id'] ?? 0));
         }
         break;
      case 'POST':
         simpanRacikan($koneksi, $idCustomer);
         break;
      default:
         gagal('Method tidak diizinkan.', 405);
   }
} catch (mysqli_sql_exception $e) {
   if (isset($GLOBALS['racikanTrx'])) {
      $koneksi->rollback();
   }
   error_log('[racikan] ' . $e->getMessage());
   gagal('Terjadi kesalahan database, data tidak disimpan.', 500);
}

function cariObat(mysqli $koneksi, string $idCustomer): void
{
   $q = '%' . trim((string) ($_GET['q'] ?? '')) . '%';
   $stmt = $koneksi->prepare("SELECT p.id_pharmacy, p.pharmacy_name_generic, p.pharmacy_name_trade
      FROM ms_pharmacy p
      WHERE " . obatAksesSql() . "
        AND (p.pharmacy_name_generic LIKE ? OR p.pharmacy_name_trade LIKE ?)
      ORDER BY p.pharmacy_name_generic ASC
      LIMIT 30");
   $stmt->bind_param('sssss', $idCustomer, $idCustomer, $idCustomer, $q, $q);
   $stmt->execute();
   $hasil = [];
   foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $o) {
      $hasil[] = ['id' => (int) $o['id_pharmacy'], 'text' => namaObat($o)];
   }
   jsonKeluar(['results' => $hasil]);
}

function ambilRacikan(mysqli $koneksi, string $idCustomer, int $id): void
{
   $tiket = ambilTiket($koneksi, $id, $idCustomer);
   if (!$tiket) {
      gagal('Tiket tidak ditemukan.', 404);
   }
   if (!isRacikan($tiket)) {
      gagal('Tiket ini bukan tipe Racikan.');
   }

   $stmt = $koneksi->prepare("SELECT d.id_pharmacy_details, d.id_pharmacy, d.qty, d.qty_racik, d.catatan,
         p.pharmacy_name_generic, p.pharmacy_name_trade
      FROM permintaan_pharmacy_details d
      LEFT JOIN ms_pharmacy p ON p.id_pharmacy = d.id_pharmacy
      WHERE d.id_permintaan_farmasi = ?
      ORDER BY d.id_pharmacy_details ASC");
   $stmt->bind_param('i', $id);
   $stmt->execute();
   $tipe = in_array($tiket['rck_tipe'] ?? '', RACIKAN_TIPE, true) ? $tiket['rck_tipe'] : 'NON_DTD';
   $jumlahRacik = (float) ($tiket['rck_jumlah'] ?? 0);
   $bahan = [];
   foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $d) {
      // qty_racik kosong (data lama / diubah di halaman detail): hitung dari qty total
      if ($d['qty_racik'] !== null) {
         $qty = (float) $d['qty_racik'];
      } elseif ($tipe === 'DTD' && $jumlahRacik > 0) {
         $qty = round((float) $d['qty'] / $jumlahRacik, 2);
      } else {
         $qty = (float) $d['qty'];
      }
      $bahan[] = [
         'id' => (int) $d['id_pharmacy_details'],
         'id_pharmacy' => (int) $d['id_pharmacy'],
         'nama' => namaObat($d) ?: 'Obat tidak ditemukan',
         'qty' => $qty,
         'catatan' => (string) ($d['catatan'] ?? ''),
      ];
   }
   $stmt->close();

   jsonKeluar([
      'status' => 'success',
      'data' => [
         'id' => (int) $tiket['id_permintaan_farmasi'],
         'status_permintaan' => (int) $tiket['status_permintaan'],
         'nama' => (string) ($tiket['rck_nama'] ?? ''),
         'tipe' => $tipe,
         'jumlah' => (string) ($tiket['rck_jumlah'] ?? ''),
         'satuan' => (string) ($tiket['rck_satuan'] ?? ''),
         'signa' => (string) ($tiket['rck_signa'] ?? ''),
         'frek_kali' => (string) ($tiket['rck_frek_kali'] ?? ''),
         'frek_dosis' => (string) ($tiket['rck_frek_dosis'] ?? ''),
         'frek_periode' => (string) ($tiket['rck_frek_periode'] ?? ''),
         'bahan' => $bahan,
      ],
   ]);
}

function daftarTemplate(mysqli $koneksi, string $idCustomer): void
{
   $q = '%' . trim((string) ($_GET['q'] ?? '')) . '%';
   $stmt = $koneksi->prepare("SELECT id_template, nama_racikan FROM ms_racikan_template
      WHERE id_customer = ? AND nama_racikan LIKE ?
      ORDER BY nama_racikan ASC LIMIT 30");
   $stmt->bind_param('ss', $idCustomer, $q);
   $stmt->execute();
   $hasil = [];
   foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $t) {
      $hasil[] = ['id' => (int) $t['id_template'], 'text' => $t['nama_racikan']];
   }
   jsonKeluar(['results' => $hasil]);
}

function ambilTemplate(mysqli $koneksi, string $idCustomer, int $idTemplate): void
{
   $stmt = $koneksi->prepare("SELECT * FROM ms_racikan_template WHERE id_template = ? AND id_customer = ?");
   $stmt->bind_param('is', $idTemplate, $idCustomer);
   $stmt->execute();
   $t = $stmt->get_result()->fetch_assoc();
   $stmt->close();
   if (!$t) {
      gagal('Template tidak ditemukan.', 404);
   }

   // hanya bahan yang masih boleh dipakai klinik ini
   $stmt = $koneksi->prepare("SELECT i.id_pharmacy, i.qty, i.catatan, p.pharmacy_name_generic, p.pharmacy_name_trade
      FROM ms_racikan_template_item i
      JOIN ms_pharmacy p ON p.id_pharmacy = i.id_pharmacy
      WHERE i.id_template = ? AND " . obatAksesSql() . "
      ORDER BY i.urutan ASC, i.id_item ASC");
   $stmt->bind_param('isss', $idTemplate, $idCustomer, $idCustomer, $idCustomer);
   $stmt->execute();
   $bahan = [];
   foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $i) {
      $bahan[] = [
         'id_pharmacy' => (int) $i['id_pharmacy'],
         'nama' => namaObat($i),
         'qty' => (float) $i['qty'],
         'catatan' => (string) ($i['catatan'] ?? ''),
      ];
   }
   $stmt->close();

   jsonKeluar([
      'status' => 'success',
      'data' => [
         'nama' => $t['nama_racikan'],
         'tipe' => $t['tipe_racik'],
         'jumlah' => (string) ($t['rck_jumlah'] ?? ''),
         'satuan' => (string) ($t['rck_satuan'] ?? ''),
         'signa' => (string) ($t['rck_signa'] ?? ''),
         'frek_kali' => (string) ($t['frek_kali'] ?? ''),
         'frek_dosis' => (string) ($t['frek_dosis'] ?? ''),
         'frek_periode' => (string) ($t['frek_periode'] ?? ''),
         'bahan' => $bahan,
      ],
   ]);
}

function angkaPositif($nilai): ?float
{
   $nilai = str_replace(',', '.', trim((string) $nilai));
   return is_numeric($nilai) && (float) $nilai > 0 ? (float) $nilai : null;
}

function simpanRacikan(mysqli $koneksi, string $idCustomer): void
{
   $in = json_decode(file_get_contents('php://input'), true);
   if (!is_array($in)) {
      gagal('Data tidak valid.');
   }

   // ================== VALIDASI INPUT ==================
   $id = (int) ($in['id'] ?? 0);
   $nama = trim((string) ($in['nama'] ?? ''));
   $tipe = (string) ($in['tipe'] ?? '');
   $jumlah = angkaPositif($in['jumlah'] ?? '');
   $satuan = trim((string) ($in['satuan'] ?? ''));
   $signa = trim((string) ($in['signa'] ?? ''));
   $frekKali = trim((string) ($in['frek_kali'] ?? ''));
   $frekDosis = trim((string) ($in['frek_dosis'] ?? ''));
   $frekPeriode = trim((string) ($in['frek_periode'] ?? ''));
   $simpanTemplate = !empty($in['simpan_template']);
   $bahanInput = is_array($in['bahan'] ?? null) ? $in['bahan'] : [];

   if ($nama === '' || mb_strlen($nama) > 255) {
      gagal('Nama racikan wajib diisi (maks. 255 karakter).');
   }
   if (!in_array($tipe, RACIKAN_TIPE, true)) {
      gagal('Tipe racik tidak valid.');
   }
   if ($jumlah === null || floor($jumlah) != $jumlah || $jumlah > 1000) {
      gagal('Jumlah racikan harus bilangan bulat 1 - 1000.');
   }
   if ($satuan === '' || mb_strlen($satuan) > 100) {
      gagal('Satuan racikan wajib diisi.');
   }
   $adaFrek = $frekKali !== '' || $frekDosis !== '' || $frekPeriode !== '';
   if ($adaFrek) {
      if (angkaPositif($frekKali) === null || angkaPositif($frekDosis) === null || !in_array($frekPeriode, RACIKAN_PERIODE, true)) {
         gagal('Frekuensi pemberian belum lengkap (contoh: 3 x 1 per Hari).');
      }
      if (mb_strlen($frekKali) > 10 || mb_strlen($frekDosis) > 10) {
         gagal('Frekuensi pemberian tidak valid.');
      }
   }
   if ($signa === '' && $adaFrek) {
      $signa = $frekKali . 'x' . $frekDosis;
   }
   if ($signa === '' || mb_strlen($signa) > 255) {
      gagal('Signatura wajib diisi. Ketik - (strip) jika tidak ada signatura.');
   }
   if (!$bahanInput) {
      gagal('Minimal 1 bahan racikan.');
   }
   if (count($bahanInput) > 50) {
      gagal('Bahan racikan maksimal 50 item.');
   }

   $bahan = [];
   foreach (array_values($bahanInput) as $i => $b) {
      $no = $i + 1;
      $idObat = (int) ($b['id_pharmacy'] ?? 0);
      $qty = angkaPositif($b['qty'] ?? '');
      if ($idObat <= 0) {
         gagal("Bahan #$no: nama obat belum dipilih.");
      }
      if ($qty === null || $qty > 100000) {
         gagal("Bahan #$no: jumlah harus lebih dari 0.");
      }
      $qty = round($qty, 2);
      // qty di detail = total yang diambil dari stok (dibulatkan ke atas)
      $total = $tipe === 'DTD' ? $qty * $jumlah : $qty;
      $bahan[] = [
         'id' => (int) ($b['id'] ?? 0),
         'id_pharmacy' => $idObat,
         'qty_racik' => $qty,
         'qty' => (int) ceil(round($total, 4)),
         'catatan' => mb_substr(trim((string) ($b['catatan'] ?? '')), 0, 1000),
      ];
   }

   // ================== VALIDASI OBAT & HARGA ==================
   $idObat = array_values(array_unique(array_column($bahan, 'id_pharmacy')));
   $ph = implode(',', array_fill(0, count($idObat), '?'));
   $stmt = $koneksi->prepare("SELECT p.id_pharmacy, p.pharmacy_sale FROM ms_pharmacy p
      WHERE p.id_pharmacy IN ($ph) AND " . obatAksesSql());
   $params = array_merge($idObat, [$idCustomer, $idCustomer, $idCustomer]);
   $stmt->bind_param(str_repeat('i', count($idObat)) . 'sss', ...$params);
   $stmt->execute();
   $harga = [];
   foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $o) {
      $harga[(int) $o['id_pharmacy']] = (int) $o['pharmacy_sale'];
   }
   $stmt->close();
   foreach ($bahan as $i => $b) {
      if (!isset($harga[$b['id_pharmacy']])) {
         gagal('Bahan #' . ($i + 1) . ': obat tidak tersedia untuk klinik ini.');
      }
   }

   // ================== SIMPAN (TRANSAKSI) ==================
   $koneksi->begin_transaction();
   $GLOBALS['racikanTrx'] = true;

   $tiket = ambilTiket($koneksi, $id, $idCustomer, true);
   if (!$tiket) {
      $koneksi->rollback();
      gagal('Tiket tidak ditemukan.', 404);
   }
   if (!isRacikan($tiket)) {
      $koneksi->rollback();
      gagal('Tiket ini bukan tipe Racikan.');
   }
   if ((int) $tiket['status_permintaan'] === 3) {
      $koneksi->rollback();
      gagal('Tiket sudah selesai (approved), racikan tidak dapat diubah.', 403);
   }

   $jumlahStr = (string) (int) $jumlah;
   $frekKali = $adaFrek ? $frekKali : null;
   $frekDosis = $adaFrek ? $frekDosis : null;
   $frekPeriode = $adaFrek ? $frekPeriode : null;

   $stmt = $koneksi->prepare("UPDATE permintaan_pharmacy SET
         rck_nama = ?, rck_tipe = ?, rck_jumlah = ?, rck_satuan = ?, rck_signa = ?,
         rck_frek_kali = ?, rck_frek_dosis = ?, rck_frek_periode = ?
      WHERE id_permintaan_farmasi = ? AND id_customer = ?");
   $stmt->bind_param('ssssssssis', $nama, $tipe, $jumlahStr, $satuan, $signa, $frekKali, $frekDosis, $frekPeriode, $id, $idCustomer);
   $stmt->execute();
   $stmt->close();

   // detail lama milik tiket ini
   $stmt = $koneksi->prepare("SELECT id_pharmacy_details FROM permintaan_pharmacy_details WHERE id_permintaan_farmasi = ?");
   $stmt->bind_param('i', $id);
   $stmt->execute();
   $detailLama = array_map('intval', array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'id_pharmacy_details'));
   $stmt->close();
   // untuk log: komposisi racikan sebelum disimpan
   require_once __DIR__ . '/../../utility/activityLog.php';
   $ringkasBahan = function (array $ids) use ($koneksi) {
      $out = [];
      foreach ($ids as $d) if ($s = farmasiItemSnapshot($koneksi, (int) $d)) $out[] = $s['nama_obat'] . ' × ' . $s['qty'];
      return $out;
   };
   $bahanLama = $ringkasBahan($detailLama);

   $user = (string) ($_SESSION['fullname'] ?? '');
   $signaDetail = '-'; // signa racikan ada di header tiket (rck_signa)
   $dipakai = [];

   $stmtUpdate = $koneksi->prepare("UPDATE permintaan_pharmacy_details
      SET id_pharmacy = ?, qty = ?, qty_racik = ?, catatan = ?, signa = ?, harga = ?
      WHERE id_pharmacy_details = ? AND id_permintaan_farmasi = ?");
   $stmtInsert = $koneksi->prepare("INSERT INTO permintaan_pharmacy_details
      (id_permintaan_farmasi, id_pharmacy, qty, qty_racik, catatan, signa, harga, created_user)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

   foreach ($bahan as $b) {
      $hargaObat = $harga[$b['id_pharmacy']];
      if ($b['id'] > 0 && in_array($b['id'], $detailLama, true) && !isset($dipakai[$b['id']])) {
         $stmtUpdate->bind_param('iidssiii', $b['id_pharmacy'], $b['qty'], $b['qty_racik'], $b['catatan'], $signaDetail, $hargaObat, $b['id'], $id);
         $stmtUpdate->execute();
         $dipakai[$b['id']] = true;
      } else {
         $stmtInsert->bind_param('iiidssis', $id, $b['id_pharmacy'], $b['qty'], $b['qty_racik'], $b['catatan'], $signaDetail, $hargaObat, $user);
         $stmtInsert->execute();
      }
   }
   $stmtUpdate->close();
   $stmtInsert->close();

   // bahan yang dihapus dari form
   $dihapus = array_values(array_diff($detailLama, array_keys($dipakai)));
   if ($dihapus) {
      $ph = implode(',', array_fill(0, count($dihapus), '?'));
      $stmt = $koneksi->prepare("DELETE FROM permintaan_pharmacy_details
         WHERE id_permintaan_farmasi = ? AND id_pharmacy_details IN ($ph)");
      $params = array_merge([$id], $dihapus);
      $stmt->bind_param(str_repeat('i', count($params)), ...$params);
      $stmt->execute();
      $stmt->close();
   }

   if ($simpanTemplate) {
      simpanTemplate($koneksi, $idCustomer, $user, $nama, $tipe, $jumlahStr, $satuan, $signa, $frekKali, $frekDosis, $frekPeriode, $bahan);
   }

   $koneksi->commit();
   unset($GLOBALS['racikanTrx']);
   $stmt = $koneksi->prepare("SELECT id_pharmacy_details FROM permintaan_pharmacy_details WHERE id_permintaan_farmasi = ?");
   $stmt->bind_param('i', $id);
   $stmt->execute();
   $bahanBaru = $ringkasBahan(array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'id_pharmacy_details'));
   $stmt->close();
   $rckLama = ['rck_nama' => $tiket['rck_nama'] ?? null, 'rck_jumlah' => $tiket['rck_jumlah'] ?? null, 'rck_satuan' => $tiket['rck_satuan'] ?? null, 'rck_signa' => $tiket['rck_signa'] ?? null, 'bahan' => $bahanLama];
   $rckBaru = ['rck_nama' => $nama, 'rck_jumlah' => $jumlahStr, 'rck_satuan' => $satuan, 'rck_signa' => $signa, 'bahan' => $bahanBaru];
   logTiketResep($koneksi, $detailLama ? 'ubah' : 'tambah', $id, (string) ($tiket['id_visit'] ?? ''), 'Racikan "' . $nama . '" ' . $jumlahStr . ' ' . $satuan . ' · ' . count($bahanBaru) . ' bahan',
      $detailLama ? $rckLama : null, $rckBaru);

   jsonKeluar([
      'status' => 'success',
      'message' => 'Racikan berhasil disimpan' . ($simpanTemplate ? ' dan disimpan sebagai template.' : '.'),
   ]);
}

// Template disimpan per klinik berdasarkan nama racikan (nama sama = ditimpa)
function simpanTemplate(mysqli $koneksi, string $idCustomer, string $user, string $nama, string $tipe, string $jumlah, string $satuan, string $signa, ?string $frekKali, ?string $frekDosis, ?string $frekPeriode, array $bahan): void
{
   $stmt = $koneksi->prepare("INSERT INTO ms_racikan_template
         (id_customer, nama_racikan, tipe_racik, rck_jumlah, rck_satuan, rck_signa, frek_kali, frek_dosis, frek_periode, created_user)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
      ON DUPLICATE KEY UPDATE
         id_template = LAST_INSERT_ID(id_template),
         tipe_racik = VALUES(tipe_racik), rck_jumlah = VALUES(rck_jumlah), rck_satuan = VALUES(rck_satuan),
         rck_signa = VALUES(rck_signa), frek_kali = VALUES(frek_kali), frek_dosis = VALUES(frek_dosis),
         frek_periode = VALUES(frek_periode)");
   $stmt->bind_param('isssssssss', $idCustomer, $nama, $tipe, $jumlah, $satuan, $signa, $frekKali, $frekDosis, $frekPeriode, $user);
   $stmt->execute();
   $idTemplate = (int) $koneksi->insert_id;
   $stmt->close();

   $stmt = $koneksi->prepare("DELETE FROM ms_racikan_template_item WHERE id_template = ?");
   $stmt->bind_param('i', $idTemplate);
   $stmt->execute();
   $stmt->close();

   $stmt = $koneksi->prepare("INSERT INTO ms_racikan_template_item (id_template, id_pharmacy, qty, catatan, urutan) VALUES (?, ?, ?, ?, ?)");
   foreach ($bahan as $urutan => $b) {
      $stmt->bind_param('iidsi', $idTemplate, $b['id_pharmacy'], $b['qty_racik'], $b['catatan'], $urutan);
      $stmt->execute();
   }
   $stmt->close();
}
