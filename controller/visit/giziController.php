<?php
/**
 * Asesmen Gizi & Nutrisi per kunjungan (module/admin/form_gizi). Tabel pasien_gizi (migrations/20261124_pasien_gizi.sql).
 *   GET    ?no=<visit_ID>            -> { status, migrasi, data:[ringkasan asesmen], kunjungan:{...prefill} }
 *   GET    ?no=..&id=<id_gizi>       -> 1 asesmen lengkap
 *   POST   ?no=..  (JSON {id_gizi?, status, data:{...}})  -> simpan baru / ubah
 *   DELETE ?no=..&id=<id_gizi>       -> hapus (hanya Draft)
 * Sebelumnya tidak ada backend sama sekali: tombol Simpan di form hanya console.log lalu menampilkan "berhasil".
 * Semua field divalidasi terhadap giziSkema(); nilai hitungan (IMT, BB ideal, perubahan BB, PES) dihitung ulang di server.
 */
include '../../database/connect.php';
require_once __DIR__ . '/../../utility/activityLog.php';
require_once __DIR__ . '/giziSchema.php';
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');
$idc = (int) ($_SESSION['id_customer'] ?? 0);
$pengguna = (string) ($_SESSION['fullname'] ?? ($_SESSION['username'] ?? ''));
session_write_close();

function gzBalas(int $kode, array $isi): void
{
   http_response_code($kode);
   echo json_encode($isi, JSON_UNESCAPED_UNICODE);
   exit;
}
if ($idc <= 0) gzBalas(401, ['status' => 'error', 'message' => 'Sesi berakhir, silakan login ulang.']);

try {
   $no = trim((string) ($_GET['no'] ?? ''));
   $k = gzKunjungan($no, $idc);
   if (!$k) gzBalas(404, ['status' => 'error', 'message' => 'Kunjungan tidak ditemukan untuk faskes ini.']);
   $siap = gzSiapDb();
   if (!$siap && $_SERVER['REQUEST_METHOD'] !== 'GET') {
      gzBalas(503, ['status' => 'error', 'message' => 'Tabel asesmen gizi belum dibuat (migrasi belum dijalankan). Hubungi administrator.']);
   }
   switch ($_SERVER['REQUEST_METHOD']) {
      case 'GET':
         isset($_GET['id']) ? gzGetId((int) $_GET['id'], $no, $idc) : gzList($no, $k, $idc, $pengguna, $siap);
         break;
      case 'POST':
         gzSimpan($no, $k, $idc, $pengguna);
         break;
      case 'DELETE':
         gzHapus((int) ($_GET['id'] ?? 0), $no, $idc);
         break;
      default:
         gzBalas(405, ['status' => 'error', 'message' => 'Method tidak diizinkan.']);
   }
} catch (Throwable $e) {
   error_log('[visit/giziController] ' . $e->getMessage());
   gzBalas(500, ['status' => 'error', 'message' => 'Terjadi kesalahan pada server.']);
}

function gzSiapDb(): bool
{
   global $koneksi;
   return (bool) $koneksi->query("SHOW TABLES LIKE 'pasien_gizi'")->fetch_row();
}

/** Kunjungan milik faskes + data pasien untuk prefill & hitungan. */
function gzKunjungan(string $no, int $idc): ?array
{
   global $koneksi;
   if ($no === '') return null;
   $st = $koneksi->prepare("SELECT v.id_patient, v.visit_status, v.visit_date, v.berat_badan, v.tinggi_badan, v.lingkar_perut,
         p.patient_gender, p.patient_datebirth
      FROM pasien_visit v LEFT JOIN ms_patient p ON p.id_patient = v.id_patient
      WHERE v.visit_ID = ? AND v.id_customer = ? LIMIT 1");
   $st->bind_param('si', $no, $idc);
   $st->execute();
   $r = $st->get_result()->fetch_assoc();
   $st->close();
   if (!$r) return null;
   $r['umur'] = null;
   if (!empty($r['patient_datebirth']) && ($lahir = date_create(substr((string) $r['patient_datebirth'], 0, 10)))) {
      $r['umur'] = $lahir->diff(new DateTime())->y;
   }
   return $r;
}

function gzList(string $no, array $k, int $idc, string $pengguna, bool $siap): void
{
   global $koneksi;
   $data = [];
   if ($siap) {
      $st = $koneksi->prepare("SELECT id_gizi, tanggal_asesmen, nutrisionis, risiko_gizi, status, diagnosis_pes, jenis_diet, created_by, created_at, updated_by, updated_at
         FROM pasien_gizi WHERE visit_ID = ? AND id_customer = ? ORDER BY tanggal_asesmen DESC, id_gizi DESC");
      $st->bind_param('si', $no, $idc);
      $st->execute();
      $data = $st->get_result()->fetch_all(MYSQLI_ASSOC);
      $st->close();
   }
   $angka = fn($v) => is_numeric($v) && (float) $v > 0 ? rtrim(rtrim((string) (float) $v, '0'), '.') : '';
   gzBalas(200, [
      'status' => 'success',
      'migrasi' => $siap,
      'data' => $data,
      'kunjungan' => [
         'batal' => (int) $k['visit_status'] === 99,
         'tanggal' => substr((string) $k['visit_date'], 0, 10),
         'hari_ini' => date('Y-m-d'),
         'jk' => (string) $k['patient_gender'],
         'umur' => $k['umur'],
         'pengguna' => $pengguna,
         // prefill antropometri dari tanda vital kunjungan
         'bb' => $angka($k['berat_badan']),
         'tb' => $angka($k['tinggi_badan']),
         'lingkar_pinggang' => $angka($k['lingkar_perut']),
      ],
   ]);
}

function gzCari(int $id, string $no, int $idc): ?array
{
   global $koneksi;
   $st = $koneksi->prepare("SELECT id_gizi, tanggal_asesmen, nutrisionis, risiko_gizi, status, data, created_by, created_at, updated_by, updated_at
      FROM pasien_gizi WHERE id_gizi = ? AND visit_ID = ? AND id_customer = ? LIMIT 1");
   $st->bind_param('isi', $id, $no, $idc);
   $st->execute();
   $r = $st->get_result()->fetch_assoc();
   $st->close();
   return $r ?: null;
}

function gzGetId(int $id, string $no, int $idc): void
{
   $r = gzCari($id, $no, $idc);
   if (!$r) gzBalas(404, ['status' => 'error', 'message' => 'Asesmen tidak ditemukan.']);
   $r['data'] = json_decode((string) $r['data'], true) ?: [];
   gzBalas(200, ['status' => 'success', 'data' => $r]);
}

/** Validasi & normalisasi isi form terhadap skema -> data bersih. */
function gzIsi(array $in, array $k, bool $selesai): array
{
   $tolak = fn($pesan, $f) => gzBalas(422, ['status' => 'error', 'message' => $pesan, 'field' => $f]);
   $teks = fn($v, $max) => mb_substr(trim((string) (is_scalar($v) ? $v : '')), 0, $max);
   $tgl = function ($v, $f, $label) use ($teks, $tolak) {
      $v = $teks($v, 10);
      if ($v === '') return '';
      $d = DateTime::createFromFormat('!Y-m-d', $v);
      if (!$d || $d->format('Y-m-d') !== $v) $tolak("$label tidak valid.", $f);
      return $v;
   };
   $num = function ($v, $f, $label, $min, $max, $unit = '') use ($teks, $tolak) {
      $v = str_replace(',', '.', $teks($v, 20));
      if ($v === '') return '';
      if (!is_numeric($v)) $tolak("$label harus berupa angka.", $f);
      if ((float) $v < $min || (float) $v > $max) $tolak("$label harus {$min}–{$max}" . ($unit ? " $unit" : '') . '.', $f);
      return (string) (0 + $v);
   };

   $d = [];
   foreach (giziSkema() as [, , $fields]) {
      foreach ($fields as $key => $def) {
         $v = $in[$key] ?? '';
         $label = $def['label'];
         switch ($def['tipe']) {
            case 'date':
               $d[$key] = $tgl($v, $key, $label);
               break;
            case 'text':
               $d[$key] = $teks($v, $def['max'] ?? 255);
               break;
            case 'textarea':
               $d[$key] = $teks($v, 5000);
               break;
            case 'number':
               $d[$key] = $num($v, $key, $label, $def['min'], $def['max'], $def['unit'] ?? '');
               break;
            case 'select':
            case 'radio':
               $v = $teks($v, 100);
               if ($v !== '' && !in_array($v, $def['opsi'], true)) $tolak("Pilihan $label tidak valid.", $key);
               $d[$key] = $v;
               break;
            case 'checks':
               $v = is_array($v) ? array_values(array_unique(array_filter(array_map('strval', $v), fn($x) => in_array($x, $def['opsi'], true)))) : [];
               $d[$key] = $v;
               break;
            case 'lab':
               $lab = [];
               foreach (giziLab() as $lk => [$lnama, $lunit, $lmin, $lmax]) {
                  $baris = is_array($in['lab'][$lk] ?? null) ? $in['lab'][$lk] : [];
                  $r = [
                     'nilai' => $num($baris['nilai'] ?? '', "lab_{$lk}_nilai", $lnama, $lmin, $lmax, $lunit),
                     'tanggal' => $tgl($baris['tanggal'] ?? '', "lab_{$lk}_tanggal", "Tanggal $lnama"),
                     'keterangan' => $teks($baris['keterangan'] ?? '', 255),
                  ];
                  if ($r['tanggal'] !== '' && $r['tanggal'] > date('Y-m-d')) $tolak("Tanggal $lnama tidak boleh di masa depan.", "lab_{$lk}_tanggal");
                  if (implode('', $r) !== '') $lab[$lk] = $r;
               }
               $d['lab'] = $lab;
               break;
            case 'tabel':
               $rows = [];
               foreach (array_slice(is_array($v) ? $v : [], 0, 50) as $i => $baris) {
                  if (!is_array($baris)) continue;
                  $r = [];
                  foreach ($def['kolom'] as $ck => $cdef) {
                     $cv = $teks($baris[$ck] ?? '', 255);
                     if ($cv !== '' && $cdef[1] === 'date') $cv = $tgl($cv, $key, "Tanggal $label baris " . ($i + 1));
                     if ($cv !== '' && $cdef[1] === 'time' && !preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $cv)) $tolak("Waktu $label baris " . ($i + 1) . ' tidak valid.', $key);
                     if ($cv !== '' && $cdef[1] === 'select' && !in_array($cv, $cdef[2], true)) $tolak("Status $label baris " . ($i + 1) . ' tidak valid.', $key);
                     $r[$ck] = $cv;
                  }
                  if (implode('', $r) !== '') $rows[] = $r;   // baris kosong dibuang
               }
               $d[$key] = $rows;
               break;
            case 'calc':
               break;   // dihitung di server
         }
      }
   }

   // aturan tanggal
   if ($d['tanggal_asesmen'] === '') $tolak('Tanggal asesmen wajib diisi.', 'tanggal_asesmen');
   if ($d['tanggal_asesmen'] > date('Y-m-d')) $tolak('Tanggal asesmen tidak boleh di masa depan.', 'tanggal_asesmen');
   $awal = substr((string) $k['visit_date'], 0, 10);
   if ($awal !== '' && $d['tanggal_asesmen'] < $awal) $tolak('Tanggal asesmen tidak boleh sebelum tanggal kunjungan.', 'tanggal_asesmen');
   if ($d['tanggal_kontrol_gizi'] !== '' && $d['tanggal_kontrol_gizi'] < $d['tanggal_asesmen']) $tolak('Rencana kontrol tidak boleh sebelum tanggal asesmen.', 'tanggal_kontrol_gizi');
   if ($d['nutrisionis'] === '') $tolak('Nama nutrisionis / dietisien wajib diisi.', 'nutrisionis');

   // field wajib saat status Selesai
   if ($selesai) {
      foreach (giziSkema() as [, , $fields]) {
         foreach ($fields as $key => $def) {
            if (!empty($def['wajib_selesai']) && ($d[$key] ?? '') === '') $tolak($def['label'] . ' wajib diisi sebelum asesmen ditandai Selesai.', $key);
         }
      }
   }
   return giziHitung($d, (string) $k['patient_gender'], $k['umur']);
}

function gzSimpan(string $no, array $k, int $idc, string $pengguna): void
{
   global $koneksi;
   $body = json_decode((string) file_get_contents('php://input', false, null, 0, 1024 * 1024), true);
   if (!is_array($body) || !is_array($body['data'] ?? null)) gzBalas(400, ['status' => 'error', 'message' => 'Data tidak valid.']);
   $status = ($body['status'] ?? '') === 'Selesai' ? 'Selesai' : 'Draft';
   $id = (int) ($body['id_gizi'] ?? 0);
   if ($id > 0) {
      if (!gzCari($id, $no, $idc)) gzBalas(404, ['status' => 'error', 'message' => 'Asesmen tidak ditemukan.']);
   } elseif ((int) $k['visit_status'] === 99) {
      gzBalas(422, ['status' => 'error', 'message' => 'Kunjungan sudah dibatalkan.']);
   }
   $d = gzIsi($body['data'], $k, $status === 'Selesai');
   $json = json_encode($d, JSON_UNESCAPED_UNICODE);
   $pasien = (int) $k['id_patient'];
   $pes = $d['diagnosis_pes'] !== '' ? $d['diagnosis_pes'] : null;
   $risiko = $d['risiko_gizi'] !== '' ? $d['risiko_gizi'] : null;
   $diet = $d['jenis_diet'] !== '' ? $d['jenis_diet'] : null;
   if ($id > 0) {
      $__lg ??= logAmbilBaris($koneksi, 'pasien_gizi', 'id_gizi', $id); // log: kondisi sebelum diubah/dihapus
      $st = $koneksi->prepare("UPDATE pasien_gizi SET tanggal_asesmen = ?, nutrisionis = ?, risiko_gizi = ?, status = ?, diagnosis_pes = ?, jenis_diet = ?, data = ?, updated_by = ?
         WHERE id_gizi = ? AND visit_ID = ? AND id_customer = ?");
      $st->bind_param('ssssssssisi', $d['tanggal_asesmen'], $d['nutrisionis'], $risiko, $status, $pes, $diet, $json, $pengguna, $id, $no, $idc);
   } else {
      $st = $koneksi->prepare("INSERT INTO pasien_gizi (id_customer, visit_ID, id_patient, tanggal_asesmen, nutrisionis, risiko_gizi, status, diagnosis_pes, jenis_diet, data, created_by)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
      $st->bind_param('isissssssss', $idc, $no, $pasien, $d['tanggal_asesmen'], $d['nutrisionis'], $risiko, $status, $pes, $diet, $json, $pengguna);
   }
   $st->execute();
   if ($id <= 0) $id = $st->insert_id;
   $st->close();
   logBaris($koneksi, 'klinis', 'pasien_gizi', 'id_gizi', $id, $__lg ?? null, ['label' => 'Asesmen gizi', 'visit_ID' => $no], 'Asesmen gizi');
   gzBalas(200, ['status' => 'success', 'message' => $status === 'Selesai' ? 'Asesmen gizi disimpan & ditandai selesai.' : 'Draft asesmen gizi disimpan.', 'id' => $id, 'data' => $d]);
}

function gzHapus(int $id, string $no, int $idc): void
{
   global $koneksi;
   $r = $id > 0 ? gzCari($id, $no, $idc) : null;
   if (!$r) gzBalas(404, ['status' => 'error', 'message' => 'Asesmen tidak ditemukan atau sudah terhapus.']);
   if ($r['status'] === 'Selesai') gzBalas(423, ['status' => 'error', 'message' => 'Asesmen berstatus Selesai tidak bisa dihapus. Ubah status ke Draft terlebih dahulu.']);
   $__lg ??= logAmbilBaris($koneksi, 'pasien_gizi', 'id_gizi', $id); // log: kondisi sebelum diubah/dihapus
   $st = $koneksi->prepare("DELETE FROM pasien_gizi WHERE id_gizi = ? AND visit_ID = ? AND id_customer = ? AND status = 'Draft'");
   $st->bind_param('isi', $id, $no, $idc);
   $st->execute();
   $st->close();
   logBaris($koneksi, 'klinis', 'pasien_gizi', 'id_gizi', $id, $__lg ?? null, ['label' => 'Asesmen gizi', 'visit_ID' => $no], 'Asesmen gizi');
   gzBalas(200, ['status' => 'success', 'message' => 'Draft asesmen dihapus.']);
}
