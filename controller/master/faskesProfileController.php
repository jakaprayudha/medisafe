<?php
/**
 * Profil faskes milik session (module/admin/setting_faskes).
 *   GET  -> setting_clinic + ms_faskes faskes ini
 *   POST -> simpan (insert bila belum ada)
 *
 * Perbaikan:
 *  - semua nilai POST dulu ditempel langsung ke SQL (SQL injection) -> prepared statement;
 *  - contract_start/contract_end dulu SELALU ditimpa, padahal form tidak mengirimnya, sehingga setiap simpan profil
 *    mengubah tanggal kontrak menjadi 1970-01-01 (dan contract_end diisi dari contract_start). Kini tanggal kontrak hanya
 *    diubah bila dikirim dan valid;
 *  - field yang tidak dikirim tidak lagi dikosongkan;
 *  - error query dulu tidak memicu rollback (try/catch tanpa exception) -> mode exception mysqli.
 */
require '../../database/connect.php';
require_once __DIR__ . '/../../utility/activityLog.php';
if (session_status() === PHP_SESSION_NONE) session_start();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

header('Content-Type: application/json');

$id_customer = (int) ($_SESSION['id_customer'] ?? 0);

if ($id_customer <= 0) {
   echo json_encode(['status' => 'error', 'message' => 'Session tidak ditemukan']);
   exit;
}

function fpQ(mysqli $k, string $sql, string $t = '', array $a = []): ?array
{
   $st = $k->prepare($sql);
   if ($t !== '') $st->bind_param($t, ...$a);
   $st->execute();
   $r = $st->get_result();
   $row = $r ? $r->fetch_assoc() : null;
   $st->close();
   return $row ?: null;
}
function fpExec(mysqli $k, string $sql, string $t, array $a): int
{
   $st = $k->prepare($sql);
   $st->bind_param($t, ...$a);
   $st->execute();
   $id = (int) $st->insert_id;
   $st->close();
   return $id;
}
/** Tanggal Y-m-d valid atau null. */
function fpTanggal($v): ?string
{
   $v = trim((string) $v);
   if ($v === '') return null;
   $t = strtotime($v);
   return $t ? date('Y-m-d', $t) : null;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

   // =========================
   // 🔹 GET DATA (FULL)
   // =========================
   case 'GET':
      $data = fpQ($koneksi, "SELECT sc.*, mf.*
         FROM setting_clinic sc
         LEFT JOIN ms_faskes mf ON mf.id_clinic = sc.id
         WHERE sc.id_customer = ?
         LIMIT 1", 'i', [$id_customer]);
      echo json_encode(['status' => 'success', 'data' => $data]);
      break;


   // =========================
   // 🔹 INSERT / UPDATE
   // =========================
   case 'POST':
      // field ms_faskes yang boleh diisi dari form; hanya yang dikirim yang diubah
      $teks = ['pic_name', 'pic_phone', 'pic_email', 'faskes_address', 'faskes_prov', 'faskes_city', 'faskes_district', 'faskes_village'];
      $faskes = [];
      foreach ($teks as $f) {
         if (array_key_exists($f, $_POST)) $faskes[$f] = mb_substr(trim((string) $_POST[$f]), 0, 500);
      }
      foreach (['contract_start', 'contract_end'] as $f) {
         $tgl = fpTanggal($_POST[$f] ?? '');
         if ($tgl !== null) $faskes[$f] = $tgl;
      }
      $clinic_name = array_key_exists('clinic_name', $_POST) ? mb_substr(trim((string) $_POST['clinic_name']), 0, 255) : null;
      if ($clinic_name === '') {
         echo json_encode(['status' => 'error', 'message' => 'Nama faskes wajib diisi']);
         exit;
      }

      $lgClinic = $lgFaskes = null;
      try {
         $koneksi->begin_transaction();

         $clinic = fpQ($koneksi, "SELECT id FROM setting_clinic WHERE id_customer = ? LIMIT 1", 'i', [$id_customer]);

         if ($clinic) {
            $id_clinic = (int) $clinic['id'];
            $lgClinic = logAmbilBaris($koneksi, 'setting_clinic', 'id', $id_clinic);
            $lgFaskes = logAmbilBaris($koneksi, 'ms_faskes', 'id_clinic', $id_clinic);

            if ($clinic_name !== null) {
               fpExec($koneksi, "UPDATE setting_clinic SET clinic_name = ? WHERE id = ? AND id_customer = ?", 'sii', [$clinic_name, $id_clinic, $id_customer]);
            }
         } else {
            $id_clinic = fpExec($koneksi, "INSERT INTO setting_clinic (clinic_name, id_customer, status) VALUES (?, ?, 1)", 'si', [(string) $clinic_name, $id_customer]);
         }

         $adaFaskes = fpQ($koneksi, "SELECT id_faskes FROM ms_faskes WHERE id_clinic = ? LIMIT 1", 'i', [$id_clinic]);
         if ($adaFaskes) {
            if ($faskes) {
               $set = implode(', ', array_map(fn($f) => "$f = ?", array_keys($faskes)));
               fpExec($koneksi, "UPDATE ms_faskes SET $set, udpated_at = NOW() WHERE id_clinic = ?", str_repeat('s', count($faskes)) . 'i', [...array_values($faskes), $id_clinic]);
            }
         } else {
            $baris = $faskes + ['id_clinic' => $id_clinic, 'faskes_status' => 1];
            $kolom = implode(', ', array_keys($baris));
            $ph = implode(', ', array_fill(0, count($baris), '?'));
            fpExec($koneksi, "INSERT INTO ms_faskes ($kolom) VALUES ($ph)", str_repeat('s', count($baris)), array_map('strval', array_values($baris)));
         }

         $koneksi->commit();
      } catch (Throwable $e) {
         try {
            $koneksi->rollback();
         } catch (Throwable $e2) {
         }
         error_log('[faskesProfile] ' . $e->getMessage());
         echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan data']);
         exit;
      }

      // log: gabungan setting_clinic + ms_faskes, tanpa gambar & kolom waktu
      $lgBuang = array_flip(['image_clinic', 'udpated_at', 'updated_at']);
      $lgAdaLama = $lgClinic !== null;
      $lgL = array_diff_key(($lgClinic ?? []) + ($lgFaskes ?? []), $lgBuang);
      $lgB = array_diff_key((logAmbilBaris($koneksi, 'setting_clinic', 'id', $id_clinic) ?? []) + (logAmbilBaris($koneksi, 'ms_faskes', 'id_clinic', $id_clinic) ?? []), $lgBuang);
      if ($lgAdaLama) [$lgL, $lgB] = logBeda($lgL, $lgB);
      if (!$lgAdaLama || $lgB) {
         $nama = $clinic_name ?? ($lgClinic['clinic_name'] ?? '');
         logAktivitas($koneksi, 'pengaturan', $lgAdaLama ? 'ubah' : 'tambah', ['tipe' => 'setting_clinic', 'id' => $id_clinic, 'label' => 'Profil faskes · ' . $nama], 'Profil faskes disimpan', $lgAdaLama ? $lgL : null, $lgB);
      }

      echo json_encode(['status' => 'success', 'message' => 'Data berhasil disimpan']);
      break;

   default:
      http_response_code(405);
      echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
      break;
}
