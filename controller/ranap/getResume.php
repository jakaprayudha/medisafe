<?php
/**
 * Data halaman Resume Medis (module/admin/resume_medis).
 *   GET ?no=<visit_ID>  -> { status, pasien:{...}, resume:{...}|null, saran:{terapi_rawat, terapi_pulang, pemeriksaan_fisik} }
 * Perbaikan: query dirangkai dari URL (SQL injection); tanpa cek login & batas faskes; difilter nomor RM dari URL
 * (format beda -> data tidak tampil); resume dobel per kunjungan (klik Simpan berulang) -> diambil yang terbaru;
 * SELECT v.* mengirim seluruh kolom kunjungan (termasuk tanda tangan/face image) ke browser.
 */
require '../../database/connect.php';
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');
$idc = (int) ($_SESSION['id_customer'] ?? 0);
session_write_close();

function rsBalas(int $kode, array $isi): void
{
   http_response_code($kode);
   echo json_encode($isi, JSON_UNESCAPED_UNICODE);
   exit;
}
if ($idc <= 0) rsBalas(401, ['status' => 'error', 'message' => 'Sesi berakhir, silakan login ulang.']);
$no = trim((string) ($_GET['no'] ?? ''));
if ($no === '') rsBalas(422, ['status' => 'error', 'message' => 'Parameter kunjungan tidak lengkap.']);

try {
   $q = function (string $sql, string $t, array $p) use ($koneksi): array {
      $st = $koneksi->prepare($sql);
      $st->bind_param($t, ...$p);
      $st->execute();
      $r = $st->get_result()->fetch_all(MYSQLI_ASSOC);
      $st->close();
      return $r;
   };

   $p = $q("SELECT v.visit_ID, v.visit_date, v.visit_time, v.visit_date_out, v.visit_status, v.id_doctor, v.diagnosa, i.icd10 AS diagnosa_nama,
         v.anamnesa, v.pemeriksaan_fisik, v.alergi_obat, v.riwayat_alergi, v.diagnosa_utama, v.diagnosa_sekunder, v.kondisi_pulang, v.cara_keluar,
         v.rencana_tindak_lanjut, v.tekanan_darah, v.nadi, v.respirasi, v.suhu, v.saturasi, v.berat_badan, v.tinggi_badan,
         pt.nomor_rm, pt.patient_name
      FROM pasien_visit v
      LEFT JOIN ms_patient pt ON pt.id_patient = v.id_patient
      LEFT JOIN icd_10 i ON i.code = v.diagnosa
      WHERE v.visit_ID = ? AND v.id_customer = ? LIMIT 1", 'si', [$no, $idc])[0] ?? null;
   if (!$p) rsBalas(404, ['status' => 'error', 'message' => 'Kunjungan tidak ditemukan untuk faskes ini.']);

   // permintaan rawat inap terbaru (indikasi / diagnosa awal, ruang)
   $ranap = $q("SELECT pr.ranap_date, pr.ranap_time, pr.diagnosa_awal, pr.ranap_notes, r.room_name, b.bed_name
      FROM permintaan_ranap pr LEFT JOIN ms_room r ON r.id_room = pr.id_room LEFT JOIN ms_room_bed b ON b.id_bed = pr.id_bed
      WHERE pr.visit_ID_inpatient = ? ORDER BY pr.id_ranap DESC LIMIT 1", 's', [$no])[0] ?? [];
   $p['ruang'] = trim(($ranap['room_name'] ?? '') . (!empty($ranap['bed_name']) ? ' / ' . $ranap['bed_name'] : ''));
   $p['tgl_masuk'] = $ranap['ranap_date'] ?? $p['visit_date'];
   $p['indikasi'] = trim(implode(' — ', array_filter([$ranap['diagnosa_awal'] ?? '', $ranap['ranap_notes'] ?? '']))) ?: (string) $p['anamnesa'];
   $p['batal'] = (int) $p['visit_status'] === 99;
   // diagnosa utama/sekunder tersimpan dalam beberapa format ("K30", "R11,R63.8", "R11 - Nausea; K30 - Gastritis")
   // -> diseragamkan jadi [{id: kode, text: "kode - nama"}] (1 query ICD untuk semua kode)
   $urai = function (?string $v): array {
      $kode = [];
      foreach (preg_split('/\s*;\s*/', trim((string) $v)) as $bag) {
         if ($bag === '') continue;
         if (str_contains($bag, ' - ')) { $kode[] = strtoupper(trim(explode(' - ', $bag, 2)[0])); continue; }
         foreach (preg_split('/\s*,\s*/', $bag) as $c) if ($c !== '') $kode[] = strtoupper(trim($c));
      }
      return array_values(array_unique($kode));
   };
   $kUtama = array_slice($urai($p['diagnosa_utama']), 0, 1);
   $kSek = $urai($p['diagnosa_sekunder']);
   $semua = array_values(array_unique(array_merge($kUtama, $kSek)));
   $nama = [];
   if ($semua) {
      $st = $koneksi->prepare("SELECT code, icd10 FROM icd_10 WHERE code IN (" . implode(',', array_fill(0, count($semua), '?')) . ")");
      $st->bind_param(str_repeat('s', count($semua)), ...$semua);
      $st->execute();
      foreach ($st->get_result()->fetch_all(MYSQLI_ASSOC) as $r) $nama[strtoupper($r['code'])] = $r['icd10'];
      $st->close();
   }
   $jadi = fn($c) => ['id' => $c, 'text' => isset($nama[$c]) ? $c . ' - ' . $nama[$c] : $c];
   $p['dx_utama'] = $kUtama ? $jadi($kUtama[0]) : null;
   $p['dx_sekunder'] = array_map($jadi, $kSek);
   unset($p['visit_status']);

   // resume tersimpan: yang terbaru (data lama bisa dobel per kunjungan)
   $resume = $q("SELECT id_resume, diagnosa, tindakan, pemeriksaan_penunjang, obat, instruksi, petugas, dokter, tanggal_pulang, jam_pulang, created_at
      FROM resume_medis WHERE visit_ID = ? ORDER BY id_resume DESC LIMIT 1", 's', [$no])[0] ?? null;

   // ---- saran isian ----
   // terapi selama rawat: rencana (P) dari seluruh CPPT, urut waktu
   $cppt = $q("SELECT cppt_date, cppt_time, planning, cppt_profesi FROM visit_cppt
      WHERE visit_ID = ? AND id_customer = ? AND planning IS NOT NULL AND TRIM(planning) <> ''
      ORDER BY cppt_date, cppt_time, id_cppt", 'ss', [$no, (string) $idc]);
   $baris = [];
   $sebelum = '';
   foreach ($cppt as $c) {
      $isi = trim(preg_replace('/\s+/', ' ', (string) $c['planning']));
      if ($isi === $sebelum) continue;   // CPPT berulang dengan rencana sama tidak diulang
      $sebelum = $isi;
      $baris[] = '- ' . date('d/m', strtotime((string) $c['cppt_date'])) . ' ' . substr((string) $c['cppt_time'], 0, 5) . ': ' . $isi;
   }
   // terapi pulang: obat dari tiket farmasi "obat pulang" (1 query, tanpa per-tiket)
   // tabel farmasi tanpa index: ambil tiket dulu, detail hanya bila ada tiket (join langsung ±0,8 dtk)
   $tiket = array_map('intval', array_column($q("SELECT id_permintaan_farmasi FROM permintaan_pharmacy
      WHERE id_visit = ? AND id_customer = ? AND status_obat_pulang = 1", 'si', [$no, $idc]), 'id_permintaan_farmasi'));
   $obat = $tiket ? $koneksi->query("SELECT COALESCE(NULLIF(m.pharmacy_name_generic, ''), d.item_name) AS nama, d.qty, d.signa
      FROM permintaan_pharmacy_details d LEFT JOIN ms_pharmacy m ON m.id_pharmacy = d.id_pharmacy
      WHERE d.id_permintaan_farmasi IN (" . implode(',', $tiket) . ") ORDER BY d.id_permintaan_farmasi, d.id_pharmacy_details")->fetch_all(MYSQLI_ASSOC) : [];
   $pulang = array_map(fn($o) => '- ' . trim($o['nama'] . ' ' . (is_numeric($o['qty']) ? (string) (0 + $o['qty']) : (string) $o['qty']) . ' ' . ($o['signa'] ?? '')), $obat);
   // pemeriksaan fisik: tanda vital kunjungan
   $ttv = array_filter([
      $p['tekanan_darah'] ? 'TD ' . $p['tekanan_darah'] . ' mmHg' : '',
      $p['nadi'] ? 'N ' . $p['nadi'] . ' x/mnt' : '',
      $p['respirasi'] ? 'RR ' . $p['respirasi'] . ' x/mnt' : '',
      $p['suhu'] ? 'S ' . $p['suhu'] . ' °C' : '',
      $p['saturasi'] ? 'SpO₂ ' . $p['saturasi'] . '%' : '',
      (float) $p['berat_badan'] > 0 ? 'BB ' . (0 + $p['berat_badan']) . ' kg' : '',
      (float) $p['tinggi_badan'] > 0 ? 'TB ' . (0 + $p['tinggi_badan']) . ' cm' : '',
   ]);
   foreach (['tekanan_darah', 'nadi', 'respirasi', 'suhu', 'saturasi', 'berat_badan', 'tinggi_badan'] as $k) unset($p[$k]);

   rsBalas(200, [
      'status' => 'success',
      'pasien' => $p,
      'resume' => $resume,
      'saran' => [
         'terapi_rawat' => implode("\n", $baris),
         'terapi_pulang' => implode("\n", $pulang),
         'pemeriksaan_fisik' => $ttv ? 'Tanda vital: ' . implode(', ', $ttv) : '',
         'alergi' => trim((string) $p['riwayat_alergi']),
      ],
   ]);
} catch (Throwable $e) {
   error_log('[ranap/getResume] ' . $e->getMessage());
   rsBalas(500, ['status' => 'error', 'message' => 'Terjadi kesalahan pada server.']);
}
