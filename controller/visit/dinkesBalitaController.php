<?php
/**
 * Data Dinkes Balita per kunjungan (module/admin/form_dinkes_balita), pasien < 5 tahun.
 *   GET  ?no=<visit_ID>  -> { kunjungan, data (isian), status kirim, z-score hasil Dinkes }
 *   POST ?no=<visit_ID>  -> simpan isian lalu kirim ke Dinkes (tambah / update bila sudah terkirim)
 * Isian disimpan di dinkes_kirim.data (JSON) walau pengiriman gagal. Skema: migrations/20261125_dinkes.sql.
 */
require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/../dinkes/dinkesService.php';
header('Content-Type: application/json; charset=utf-8');

$idc = (int) ($_SESSION['id_customer'] ?? 0);

function dbBalas(int $kode, array $isi): void
{
   http_response_code($kode);
   echo json_encode($isi, JSON_UNESCAPED_UNICODE);
   exit;
}
if ($idc <= 0) dbBalas(401, ['status' => 'error', 'message' => 'Sesi berakhir, silakan login ulang.']);

/** [tipe, min, max, wajib] */
const DB_FIELD = [
   'nama_orang_tua' => ['str', 0, 150, true],
   'nomor_kk' => ['kk', 0, 0, false],
   'bb_lahir' => ['num', 0.5, 7, true],
   'tb_lahir' => ['num', 20, 70, true],
   'lila_cm' => ['num', 5, 30, true],
   'lingkar_kepala_cm' => ['num', 20, 60, true],
   'diagnosa' => ['str', 0, 200, false],
   'riwayat_sakit_kronis' => ['str', 0, 200, false],
   'riwayat_diare' => ['bool', 0, 0, false],
   'riwayat_ispa' => ['bool', 0, 0, false],
   'imunisasi_lengkap' => ['bool', 0, 0, false],
   'asi_eksklusif' => ['bool', 0, 0, false],
   'usia_mpasi' => ['int', 0, 60, false],
   'ttd_jumlah' => ['int', 0, 1000, false],
   'status_bumil' => ['bool', 0, 0, false],
   'usia_saat_hamil' => ['int', 10, 60, false],
   'riwayat_anemia' => ['bool', 0, 0, false],
];

/** @return array{0:array,1:?string} [isian bersih, pesan error] */
function dbBersih(array $in): array
{
   $out = [];
   foreach (DB_FIELD as $k => [$tipe, $min, $max, $wajib]) {
      $v = $in[$k] ?? null;
      $v = is_scalar($v) ? trim((string) $v) : '';
      if ($v === '') {
         if ($wajib) return [[], "Isian '$k' wajib diisi."];
         continue;
      }
      switch ($tipe) {
         case 'str':
            $out[$k] = mb_substr($v, 0, $max);
            break;
         case 'kk':
            if (!preg_match('/^\d{16}$/', $v)) return [[], 'Nomor KK harus 16 digit.'];
            $out[$k] = $v;
            break;
         case 'num':
         case 'int':
            if (!is_numeric($v) || $v < $min || $v > $max) return [[], "Isian '$k' harus antara $min dan $max."];
            $out[$k] = $tipe === 'int' ? (int) $v : (float) $v;
            break;
         case 'bool':
            $out[$k] = in_array($v, ['1', 'true', 'Ya'], true);
            break;
      }
   }
   return [$out, null];
}

try {
   $no = trim((string) ($_GET['no'] ?? ''));
   $c = $no !== '' ? dkKonteks($koneksi, $idc, $no) : null;
   if (!$c) dbBalas(404, ['status' => 'error', 'message' => 'Kunjungan tidak ditemukan.']);
   $balita = $c['umur'] !== null && $c['umur'] < 5;
   $baris = dkBaris($koneksi, $idc, $no);
   $cfg = dkSetting($koneksi, $idc);

   if (isset($_GET['puskesmas'])) { // daftar untuk pilihan override per data
      if (!$cfg || trim((string) $cfg['token']) === '') dbBalas(422, ['status' => 'error', 'message' => 'Token Dinkes belum diisi.']);
      [$code, $res, $err] = dkHttp((string) $cfg['token'], 'GET', 'refrensi-puskesmas');
      if (!dkOk($code, $res)) dbBalas(502, ['status' => 'error', 'message' => dkPesan($code, $res, (string) $err)]);
      dbBalas(200, ['status' => 'success', 'data' => $res['data'] ?? []]);
   }

   if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (!$balita) dbBalas(422, ['status' => 'error', 'message' => 'Form ini hanya untuk pasien di bawah 5 tahun.']);
      [$isi, $err] = dbBersih($_POST);
      if ($err) dbBalas(422, ['status' => 'error', 'message' => $err]);
      $puskes = (int) ($_POST['puskes_id'] ?? 0);
      dkSimpanBalita($koneksi, $idc, $no, $isi, $puskes > 0 ? $puskes : null);
      $hasil = dkKirimVisit($koneksi, $idc, $no);
      dbBalas(200, ['status' => 'success', 'message' => 'Data tersimpan.', 'kirim' => $hasil]);
   }

   $isi = $baris && $baris['data'] ? (json_decode($baris['data'], true) ?: []) : [];
   if (!$isi) { // isian awal dari data yang sudah ada
      $isi = ['nomor_kk' => (string) $c['patient_kk']];
      if ($c['icd'] !== '') {
         $st = $koneksi->prepare("SELECT icd10 FROM icd_10 WHERE code=? LIMIT 1");
         $st->bind_param('s', $c['icd']);
         $st->execute();
         $isi['diagnosa'] = (string) ($st->get_result()->fetch_row()[0] ?? '');
         $st->close();
      }
   }
   $respon = $baris && $baris['dk_respon'] ? json_decode($baris['dk_respon'], true) : null;
   dbBalas(200, ['status' => 'success',
      'kunjungan' => ['nama' => $c['patient_name'], 'umur_tahun' => $c['umur'], 'balita' => $balita,
         'bb' => $c['berat'], 'tb' => $c['tinggi'], 'tanggal' => $c['visit_date']],
      'data' => $isi,
      'puskes_id' => $baris['puskes_id'] ?? null,
      'puskes_default' => $cfg['puskes_id'] ?? null,
      'integrasi_aktif' => $cfg && (int) $cfg['aktif'] === 1,
      'kirim' => $baris ? ['status' => $baris['dk_status'], 'error' => $baris['dk_error'], 'sent_at' => $baris['dk_sent_at']] : null,
      'zscore' => $respon['detail_zscore'] ?? null,
   ]);
} catch (Throwable $e) {
   error_log('[visit/dinkesBalita] ' . $e->getMessage());
   dbBalas(500, ['status' => 'error', 'message' => 'Terjadi kesalahan pada server.']);
}
