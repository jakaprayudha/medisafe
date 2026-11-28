<?php
// Master poliklinik (module/admin/poli)
//   GET                         -> daftar poli
//   GET    ?id=ID               -> satu poli
//   GET    ?stats=1&tanggal=Y-m-d -> jumlah pasien per poli (hari itu, 7 hari, bulan berjalan)
//   POST                        -> tambah (poli_code, poli_name, poli_queue)
//   PUT                         -> ubah (id_poli, ...)  | toggle status: id_poli + poli_status
//   DELETE ?id=ID               -> hapus
session_start();
include '../../database/connect.php';
require_once __DIR__ . '/../../utility/activityLog.php';
mysqli_report(MYSQLI_REPORT_OFF);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function jawab(array $data, int $code = 200): void
{
   http_response_code($code);
   echo json_encode($data);
   exit;
}

function semua(mysqli $k, string $sql, string $types = '', array $params = []): array
{
   $stmt = $k->prepare($sql);
   if (!$stmt) {
      error_log('[poliController] ' . $k->error);
      jawab(['status' => 'error', 'message' => 'Gagal mengambil data.'], 500);
   }
   if ($types !== '') $stmt->bind_param($types, ...$params);
   $stmt->execute();
   $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
   $stmt->close();
   return $rows;
}

function eksekusi(mysqli $k, string $sql, string $types, array $params): int
{
   $stmt = $k->prepare($sql);
   if (!$stmt) {
      error_log('[poliController] ' . $k->error);
      jawab(['status' => 'error', 'message' => 'Gagal menyimpan data.'], 500);
   }
   $stmt->bind_param($types, ...$params);
   if (!$stmt->execute()) {
      error_log('[poliController] ' . $stmt->error);
      jawab(['status' => 'error', 'message' => 'Gagal menyimpan data.'], 500);
   }
   $n = $stmt->affected_rows;
   $stmt->close();
   return $n;
}

if (empty($_SESSION['id_customer'])) {
   jawab(['status' => 'error', 'message' => 'Session faskes tidak ditemukan.'], 401);
}
$idc = (int) $_SESSION['id_customer'];
$method = $_SERVER['REQUEST_METHOD'];

/** kunci pencocokan nama poli: pasien_visit.id_poli menyimpan NAMA poli (kadang beda penulisan) */
function kunciPoli(?string $nama): string
{
   $s = strtoupper(trim((string) $nama));
   $s = str_replace('&', ' DAN ', $s);
   $s = preg_replace('/\bPOLI(KLINIK)?\b/', ' ', $s);
   return preg_replace('/[^A-Z0-9]/', '', $s);
}

/** peta pencocokan nama kunjungan -> id_poli (nama persis dulu, lalu nama ternormalisasi) */
function petaPoli(array $polis): callable
{
   $exact = $norm = [];
   foreach ($polis as $p) {
      $exact[strtoupper(trim((string) $p['poli_name']))] ??= (int) $p['id_poli'];
      $kunci = kunciPoli($p['poli_name']);
      if ($kunci !== '') $norm[$kunci] ??= (int) $p['id_poli'];
   }
   return fn(?string $nama) => $exact[strtoupper(trim((string) $nama))] ?? ($norm[kunciPoli($nama)] ?? null);
}

/** total kunjungan pasien (semua waktu, termasuk batal) per id_poli */
function kunjunganPerPoli(mysqli $k, int $idc, array $polis): array
{
   $cari = petaPoli($polis);
   $out = [];
   $rows = semua($k, "SELECT id_poli AS nama, COUNT(*) n FROM pasien_visit WHERE id_customer = ? GROUP BY id_poli", 'i', [$idc]);
   foreach ($rows as $r) {
      $id = $cari($r['nama']);
      if ($id !== null) $out[$id] = ($out[$id] ?? 0) + (int) $r['n'];
   }
   return $out;
}

function inputPoli(array $in): array
{
   $kode = trim((string) ($in['poli_code'] ?? ''));
   $nama = trim(preg_replace('/\s+/', ' ', (string) ($in['poli_name'] ?? '')));
   $antri = trim((string) ($in['poli_queue'] ?? ''));
   if ($kode === '' || mb_strlen($kode) > 100) jawab(['status' => 'error', 'message' => 'Kode poli wajib diisi.'], 422);
   if ($nama === '' || mb_strlen($nama) > 100) jawab(['status' => 'error', 'message' => 'Nama poliklinik wajib diisi.'], 422);
   if ($antri === '' || mb_strlen($antri) > 100) jawab(['status' => 'error', 'message' => 'Kode antrean wajib diisi.'], 422);
   return [$kode, $nama, $antri];
}

// hanya nilai yang berubah yang dicek, agar data lama yang sudah dobel tetap bisa disunting
function cekDuplikat(mysqli $k, int $idc, string $kode, string $nama, int $kecuali = 0, ?array $lama = null): void
{
   $cekKode = !$lama || (string) $lama['poli_code'] !== $kode;
   $cekNama = !$lama || mb_strtolower(trim((string) $lama['poli_name'])) !== mb_strtolower($nama);
   if (!$cekKode && !$cekNama) return;
   $rows = semua(
      $k,
      "SELECT poli_code, poli_name FROM ms_poli WHERE id_customer = ? AND id_poli <> ? AND (poli_code = ? OR LOWER(TRIM(poli_name)) = LOWER(?))",
      'iiss',
      [$idc, $kecuali, $kode, $nama]
   );
   foreach ($rows as $r) {
      if ($cekKode && (string) $r['poli_code'] === $kode) jawab(['status' => 'error', 'message' => "Kode poli \"$kode\" sudah dipakai {$r['poli_name']}."], 409);
      if ($cekNama && mb_strtolower(trim((string) $r['poli_name'])) === mb_strtolower($nama)) jawab(['status' => 'error', 'message' => "Poliklinik \"$nama\" sudah ada."], 409);
   }
}

function poliLog(mysqli $k, int $id, ?array $lama, string $ket, ?string $aksi = null): void
{
   $r = $lama ?? logAmbilBaris($k, 'ms_poli', 'id_poli', $id) ?? [];
   logBaris($k, 'master', 'ms_poli', 'id_poli', $id, $lama, ['tipe' => 'poli', 'label' => trim(($r['poli_code'] ?? '') . ' · ' . ($r['poli_name'] ?? ''), ' ·')], $ket, $aksi);
}

function poliMilik(mysqli $k, int $id, int $idc): array
{
   $row = semua($k, "SELECT * FROM ms_poli WHERE id_poli = ? AND id_customer = ?", 'ii', [$id, $idc]);
   if (!$row) jawab(['status' => 'error', 'message' => 'Data tidak ditemukan.'], 404);
   return $row[0];
}

// ================= GET =================
if ($method === 'GET') {
   if (isset($_GET['stats'])) {
      $tgl = (string) ($_GET['tanggal'] ?? '');
      $dt = DateTime::createFromFormat('!Y-m-d', $tgl) ?: new DateTime('today');
      $tgl = $dt->format('Y-m-d');
      $awalBulan = $dt->format('Y-m-01');
      $awal7 = (clone $dt)->modify('-6 day')->format('Y-m-d');
      $mulai = min($awalBulan, $awal7);

      $polis = semua($koneksi, "SELECT id_poli, poli_name, poli_status FROM ms_poli WHERE id_customer = ? ORDER BY poli_status DESC, id_poli ASC", 'i', [$idc]);
      $cari = petaPoli($polis);

      $hari7 = [];
      for ($i = 6; $i >= 0; $i--) $hari7[] = (clone $dt)->modify("-$i day")->format('Y-m-d');
      $kosong = fn() => ['hari_ini' => 0, 'antre' => 0, 'selesai' => 0, 'batal' => 0, 'bulan' => 0, 'tren' => array_fill(0, 7, 0)];

      $stat = [];
      foreach ($polis as $p) $stat[(int) $p['id_poli']] = $kosong();
      $lain = [];

      $rows = semua(
         $koneksi,
         "SELECT id_poli AS nama, visit_date, visit_status, COUNT(*) n
          FROM pasien_visit
          WHERE id_customer = ? AND visit_date BETWEEN ? AND ?
          GROUP BY id_poli, visit_date, visit_status",
         'iss',
         [$idc, $mulai, $tgl]
      );
      foreach ($rows as $r) {
         $nama = (string) $r['nama'];
         $id = $cari($nama);
         if ($id === null) {
            $label = trim($nama) !== '' ? trim($nama) : '(tanpa poli)';
            $lain[$label] ??= $kosong();
            $s = &$lain[$label];
         } else {
            $s = &$stat[$id];
         }
         $n = (int) $r['n'];
         $batal = (int) $r['visit_status'] === 99;
         $d = $r['visit_date'];
         if ($d === $tgl) {
            if ($batal) $s['batal'] += $n;
            else {
               $s['hari_ini'] += $n;
               if ((int) $r['visit_status'] === 4) $s['selesai'] += $n;
               else $s['antre'] += $n;
            }
         }
         if (!$batal) {
            if ($d >= $awalBulan) $s['bulan'] += $n;
            $idx = array_search($d, $hari7, true);
            if ($idx !== false) $s['tren'][$idx] += $n;
         }
         unset($s);
      }

      $lainList = [];
      foreach ($lain as $label => $s) {
         if ($s['hari_ini'] || $s['bulan'] || $s['batal']) $lainList[] = ['nama' => $label] + $s;
      }
      usort($lainList, fn($a, $b) => $b['hari_ini'] <=> $a['hari_ini'] ?: $b['bulan'] <=> $a['bulan']);

      jawab([
         'status' => 'success',
         'tanggal' => $tgl,
         'hari' => $hari7,
         'data' => (object) $stat,
         'lain' => $lainList,
      ]);
   }

   if (isset($_GET['id'])) {
      jawab(['status' => 'success', 'data' => poliMilik($koneksi, (int) $_GET['id'], $idc)]);
   }

   $data = semua($koneksi, "SELECT * FROM ms_poli WHERE id_customer = ? ORDER BY poli_name ASC", 'i', [$idc]);
   // urutan sama dengan statistik agar nama dobel dipetakan ke poli yang sama
   $urut = $data;
   usort($urut, fn($a, $b) => ((int) $b['poli_status'] <=> (int) $a['poli_status']) ?: ((int) $a['id_poli'] <=> (int) $b['id_poli']));
   $jml = kunjunganPerPoli($koneksi, $idc, $urut);
   foreach ($data as &$d) $d['jml_kunjungan'] = $jml[(int) $d['id_poli']] ?? 0;
   unset($d);
   jawab(['status' => 'success', 'data' => $data]);
}

// ================= POST =================
if ($method === 'POST') {
   [$kode, $nama, $antri] = inputPoli($_POST);
   cekDuplikat($koneksi, $idc, $kode, $nama);
   eksekusi(
      $koneksi,
      "INSERT INTO ms_poli (poli_code, poli_name, poli_queue, id_customer) VALUES (?, ?, ?, ?)",
      'sssi',
      [$kode, $nama, $antri, $idc]
   );
   poliLog($koneksi, (int) $koneksi->insert_id, null, 'Poliklinik ditambahkan');
   jawab(['status' => 'success', 'message' => 'Poliklinik berhasil ditambahkan.']);
}

// ================= PUT =================
if ($method === 'PUT') {
   parse_str(file_get_contents('php://input'), $in);
   $id = (int) ($in['id_poli'] ?? 0);
   $lama = poliMilik($koneksi, $id, $idc);

   if (isset($in['poli_status'])) {
      $status = (int) $in['poli_status'];
      if (!in_array($status, [0, 1], true)) jawab(['status' => 'error', 'message' => 'Status tidak valid.'], 422);
      eksekusi($koneksi, "UPDATE ms_poli SET poli_status = ? WHERE id_poli = ? AND id_customer = ?", 'iii', [$status, $id, $idc]);
      poliLog($koneksi, $id, $lama, $status ? 'Poliklinik diaktifkan' : 'Poliklinik dinonaktifkan', $status ? 'aktifkan' : 'nonaktifkan');
      jawab(['status' => 'success', 'message' => $status ? 'Poliklinik diaktifkan.' : 'Poliklinik dinonaktifkan.']);
   }

   [$kode, $nama, $antri] = inputPoli($in);
   cekDuplikat($koneksi, $idc, $kode, $nama, $id, $lama);
   eksekusi(
      $koneksi,
      "UPDATE ms_poli SET poli_code = ?, poli_name = ?, poli_queue = ? WHERE id_poli = ? AND id_customer = ?",
      'sssii',
      [$kode, $nama, $antri, $id, $idc]
   );
   poliLog($koneksi, $id, $lama, 'Poliklinik diubah');
   jawab(['status' => 'success', 'message' => 'Poliklinik berhasil diperbarui.']);
}

// ================= DELETE =================
if ($method === 'DELETE') {
   $id = (int) ($_GET['id'] ?? 0);
   $poli = poliMilik($koneksi, $id, $idc);
   $semuaPoli = semua($koneksi, "SELECT id_poli, poli_name, poli_status FROM ms_poli WHERE id_customer = ? ORDER BY poli_status DESC, id_poli ASC", 'i', [$idc]);
   $jml = kunjunganPerPoli($koneksi, $idc, $semuaPoli)[$id] ?? 0;
   if ($jml > 0) {
      jawab(['status' => 'error', 'message' => "Poliklinik {$poli['poli_name']} sudah memiliki " . number_format($jml, 0, ',', '.') . " data kunjungan pasien sehingga tidak dapat dihapus. Nonaktifkan saja bila tidak dipakai."], 409);
   }
   eksekusi($koneksi, "DELETE FROM ms_poli WHERE id_poli = ? AND id_customer = ?", 'ii', [$id, $idc]);
   poliLog($koneksi, $id, $poli, 'Poliklinik dihapus');
   jawab(['status' => 'success', 'message' => 'Poliklinik berhasil dihapus.']);
}

jawab(['status' => 'error', 'message' => 'Method tidak diizinkan.'], 405);
