<?php
/**
 * Data daftar registrasi (dipakai controller/visit/registrasiController.php dan module/admin/registrasi.php).
 * Halaman memakai fungsi yang sama untuk menyisipkan data awal ke HTML, sehingga saat halaman dibuka
 * tidak perlu request tambahan (masing-masing membuka koneksi DB & session baru).
 */

if (!defined('RG_TAB')) {
   define('RG_TAB', [
      'belum' => "pv.visit_status NOT IN (3,4,99,10) AND NOT (pv.created_user <=> 'JKNSehat')",
      'selesai' => "pv.visit_status IN (3,4) AND NOT (pv.created_user <=> 'JKNSehat')",
      'batal' => "pv.visit_status = 99 AND NOT (pv.created_user <=> 'JKNSehat')",
      'checkin' => "pv.visit_status = 10 AND NOT (pv.created_user <=> 'JKNSehat')",
      'sehat' => "pv.created_user = 'JKNSehat' AND pv.visit_status NOT IN (3,4,99,10)",
      'sehatbatal' => "pv.created_user = 'JKNSehat' AND pv.visit_status = 99",
   ]);
}

function rgTanggal(?string $v, string $def): string
{
   return ($v && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) ? $v : $def;
}

/** Filter dasar (faskes, tanggal, dokter, penjamin, poli, tipe) - dipakai daftar & hitungan. */
function rgFilter(int $idc, array $in): array
{
   $today = date('Y-m-d');
   $from = rgTanggal($in['fromDate'] ?? null, $today);
   $to = rgTanggal($in['toDate'] ?? null, $today);
   if ($from > $to) [$from, $to] = [$to, $from];
   $w = ['pv.id_customer = ?', 'pv.visit_date BETWEEN ? AND ?'];
   $t = 'iss';
   $p = [$idc, $from, $to];
   foreach (['doctor' => 'pv.id_doctor', 'provider' => 'pv.id_provider', 'poli' => 'pv.id_poli', 'tipe_pasien' => 'pv.source_hub'] as $k => $kol) {
      $v = trim((string) ($in[$k] ?? ''));
      if ($v !== '') {
         $w[] = "$kol = ?";
         $t .= 's';
         $p[] = $v;
      }
   }
   return [$w, $t, $p];
}

function rgQ(mysqli $db, string $sql, string $t = '', array $p = []): array
{
   $st = $db->prepare($sql);
   if (!$st) throw new RuntimeException($db->error);
   if ($t !== '') $st->bind_param($t, ...$p);
   if (!$st->execute()) throw new RuntimeException($st->error);
   $r = $st->get_result()->fetch_all(MYSQLI_ASSOC);
   $st->close();
   return $r;
}

/** Daftar kunjungan satu tab + jumlah semua tab. */
function rgDaftar(mysqli $db, int $idc, array $in): array
{
   $tab = isset(RG_TAB[$in['tab'] ?? '']) ? $in['tab'] : 'belum';
   [$w, $t, $p] = rgFilter($idc, $in);
   $where = implode(' AND ', $w);

   // jumlah per tab (satu query agregat, index idx_date_cust)
   $sel = [];
   foreach (RG_TAB as $k => $cond) $sel[] = "SUM($cond) AS `$k`";
   $sel[] = "SUM(" . RG_TAB['belum'] . " AND (pv.tekanan_darah IS NULL OR pv.tekanan_darah = '')) AS tanpa_vital";
   $sel[] = "SUM(pv.id_provider = '1' AND pv.visit_status <> 99) AS bpjs";
   $sel[] = "SUM(pv.visit_status <> 99) AS aktif";
   $h = rgQ($db, "SELECT " . implode(', ', $sel) . " FROM pasien_visit pv WHERE $where", $t, $p)[0] ?? [];
   $hitung = array_map('intval', $h);

   $rows = rgQ($db, "SELECT pv.id_visit, pv.visit_ID, pv.id_patient, pv.visit_date, pv.visit_time, pv.visit_antrian, pv.visit_status,
         pv.id_doctor, pv.id_poli, pv.id_provider, pv.created_user, pv.patient_name_pcare, pv.tekanan_darah,
         pv.signature_path, (pv.face_image_visit IS NOT NULL AND pv.face_image_visit <> '') AS ada_foto,
         (COALESCE(pv.file_spp, '') <> '' OR COALESCE(pv.sep_file, '') <> '') AS ada_sep,
         (COALESCE(pv.file_fkpp, '') <> '' OR COALESCE(pv.fkpp_file, '') <> '') AS ada_fkpp,
         ms.patient_name, ms.nomor_rm, ms.patient_gender, ms.patient_datebirth, ms.patient_bpjs, ms.face_image AS patient_face_image,
         pr.provider_name, dk.dk_status
      FROM pasien_visit pv
      LEFT JOIN ms_patient ms ON ms.id_patient = pv.id_patient
      LEFT JOIN ms_provider pr ON pr.id_provider = pv.id_provider
      LEFT JOIN dinkes_kirim dk ON dk.id_customer = pv.id_customer AND dk.visit_ID = pv.visit_ID
      WHERE $where AND " . RG_TAB[$tab] . "
      ORDER BY pv.visit_date ASC, pv.id_poli ASC, pv.id_doctor ASC, LEFT(pv.visit_antrian, 1) ASC,
         CAST(REGEXP_SUBSTR(pv.visit_antrian, '[0-9]+$') AS UNSIGNED) ASC, pv.id_visit ASC
      LIMIT 5000", $t, $p);
   foreach ($rows as &$r) {
      $r['id_visit'] = (int) $r['id_visit'];
      $r['visit_status'] = (int) $r['visit_status'];
      $r['ada_foto'] = (int) $r['ada_foto'];
      $r['ada_sep'] = (int) $r['ada_sep'];
      $r['ada_fkpp'] = (int) $r['ada_fkpp'];
   }
   unset($r);
   return ['status' => 'success', 'tab' => $tab, 'data' => $rows, 'hitung' => $hitung, 'terpotong' => count($rows) >= 5000];
}

/** Pilihan filter (dokter, penjamin, poli) - disisipkan ke halaman, dulu 3 request terpisah. */
function rgOpsi(mysqli $db, int $idc): array
{
   $dok = array_column(rgQ($db, "SELECT DISTINCT doctor_name FROM ms_doctor WHERE id_customer = ? AND doctor_name <> '' ORDER BY doctor_name", 'i', [$idc]), 'doctor_name');
   $prov = rgQ($db, "SELECT id_provider, provider_name FROM ms_provider ORDER BY id_provider");
   $poli = array_column(rgQ($db, "SELECT DISTINCT nmPoli FROM master_poli WHERE nmPoli <> '' ORDER BY nmPoli"), 'nmPoli');
   return ['dokter' => $dok, 'penjamin' => $prov, 'poli' => $poli];
}
