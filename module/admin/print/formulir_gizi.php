<?php
/**
 * Cetak Formulir Asesmen Gizi & Nutrisi (PAGT) — A4.
 *   GET ?no=<visit_ID>[&id=<id_gizi>]   asesmen tertentu; tanpa id: asesmen Selesai terakhir, atau terakhir bila belum ada.
 *   GET ?no=<visit_ID>&kosong=1         formulir kosong (identitas pasien terisi) untuk diisi manual.
 * Semua bagian & field selalu tercetak (pilihan sebagai ☑/☐, tabel dengan baris kosong) agar formatnya baku.
 * Definisi field dari controller/visit/giziSchema.php (sama dengan form di module/admin/form_gizi).
 */
require '../../../database/connect.php';
require_once __DIR__ . '/../../../controller/visit/giziSchema.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$idc = (int) ($_SESSION['id_customer'] ?? 0);
$pencetak = (string) ($_SESSION['fullname'] ?? '');
session_write_close();
$no = trim((string) ($_GET['no'] ?? ''));
$id = (int) ($_GET['id'] ?? 0);
$kosong = !empty($_GET['kosong']);
if ($idc <= 0) { http_response_code(401); exit('Sesi berakhir, silakan login ulang.'); }
if ($no === '') { http_response_code(422); exit('Parameter kunjungan tidak lengkap.'); }
$h = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');

$st = $koneksi->prepare("SELECT v.visit_ID, v.visit_date, v.id_poli, v.id_doctor, COALESCE(NULLIF(v.diagnosa_utama, ''), v.diagnosa) AS diagnosa,
      p.patient_name, p.nomor_rm, p.patient_gender, p.patient_datebirth
   FROM pasien_visit v LEFT JOIN ms_patient p ON p.id_patient = v.id_patient
   WHERE v.visit_ID = ? AND v.id_customer = ? LIMIT 1");
$st->bind_param('si', $no, $idc);
$st->execute();
$pasien = $st->get_result()->fetch_assoc();
$st->close();
if (!$pasien) { http_response_code(404); exit('Kunjungan tidak ditemukan untuk faskes ini.'); }

// nama diagnosis medis (ICD-10)
$diagnosa = trim((string) $pasien['diagnosa']);
if ($diagnosa !== '') {
   $st = $koneksi->prepare("SELECT icd10 FROM icd_10 WHERE code = ? LIMIT 1");
   $st->bind_param('s', $diagnosa);
   $st->execute();
   $nm = $st->get_result()->fetch_row()[0] ?? '';
   $st->close();
   if ($nm !== '') $diagnosa .= ' — ' . $nm;
}

$asesmen = null;
if (!$kosong && $koneksi->query("SHOW TABLES LIKE 'pasien_gizi'")->fetch_row()) {
   $sql = "SELECT id_gizi, tanggal_asesmen, nutrisionis, status, data, created_by, created_at, updated_at FROM pasien_gizi
      WHERE visit_ID = ? AND id_customer = ?" . ($id > 0 ? " AND id_gizi = ?" : "") . " ORDER BY status = 'Selesai' DESC, tanggal_asesmen DESC, id_gizi DESC LIMIT 1";
   $st = $koneksi->prepare($sql);
   $id > 0 ? $st->bind_param('sii', $no, $idc, $id) : $st->bind_param('si', $no, $idc);
   $st->execute();
   $asesmen = $st->get_result()->fetch_assoc();
   $st->close();
}
if (!$kosong && !$asesmen) {
   http_response_code(404);
   exit('Belum ada asesmen gizi untuk kunjungan ini. Untuk formulir kosong gunakan tombol "Formulir kosong" di halaman Gizi.');
}
$d = $asesmen ? (json_decode((string) $asesmen['data'], true) ?: []) : [];

$st = $koneksi->prepare("SELECT sc.clinic_name, sc.image_clinic, f.faskes_address, f.faskes_city, f.faskes_phone
   FROM setting_clinic sc LEFT JOIN ms_faskes f ON f.id_clinic = sc.id WHERE sc.id_customer = ? LIMIT 1");
$st->bind_param('i', $idc);
$st->execute();
$klinik = $st->get_result()->fetch_assoc() ?: [];
$st->close();

$BLN = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$tgl = fn($s) => preg_match('/^(\d{4})-(\d{2})-(\d{2})/', (string) $s, $m) ? (int) $m[3] . ' ' . $BLN[(int) $m[2]] . ' ' . $m[1] : '';
$tglAses = $asesmen['tanggal_asesmen'] ?? '';
$umur = '';
if (!empty($pasien['patient_datebirth']) && ($l = date_create(substr((string) $pasien['patient_datebirth'], 0, 10)))) {
   $u = $l->diff(date_create($tglAses ?: 'now'));
   $umur = $u->y > 0 ? $u->y . ' tahun' : $u->m . ' bulan';
}
$logo = !empty($klinik['image_clinic']) ? '../../../uploads/' . rawurlencode($klinik['image_clinic']) : '';
$titik = '<span class="isian"></span>';
const GZ_COL4 = '<colgroup><col style="width:18%"><col style="width:32%"><col style="width:18%"><col style="width:32%"></colgroup>';
const GZ_COL2 = '<colgroup><col style="width:18%"><col style="width:82%"></colgroup>';

/** Nilai teks / garis isian bila kosong. */
$nilai = function (string $key, array $def) use ($d, $h, $tgl, $titik): string {
   $v = $d[$key] ?? '';
   if ($v === '' || $v === null) return $titik;
   if ($def['tipe'] === 'date') return $h($tgl($v));
   $u = !empty($def['unit']) ? ' ' . $h($def['unit']) : '';
   return '<b>' . nl2br($h($v)) . '</b>' . $u;
};
/** Daftar pilihan dengan kotak centang. */
$kotak = function (array $opsi, $terpilih, array $label = []) use ($h): string {
   $terpilih = is_array($terpilih) ? $terpilih : [$terpilih];
   return implode('', array_map(fn($o) => '<span class="cb">' . (in_array($o, $terpilih, true) ? '☑' : '☐') . ' ' . $h($label[$o] ?? $o) . '</span>', $opsi));
};
?>
<!DOCTYPE html>
<html lang="id">
<head>
   <meta charset="UTF-8">
   <title>Asesmen Gizi — <?= $h($pasien['patient_name']) ?></title>
   <style>
      @page { size: A4; margin: 12mm 12mm 14mm; }
      * { box-sizing: border-box; }
      body { font-family: Arial, Helvetica, sans-serif; font-size: 9.6pt; color: #000; margin: 0; line-height: 1.35; }
      .kop { display: flex; align-items: center; gap: 12px; border-bottom: 3px double #000; padding-bottom: 6px; }
      .kop img { height: 56px; }
      .kop .nm { font-size: 14pt; font-weight: 700; text-transform: uppercase; }
      .kop .al { font-size: 8.6pt; }
      .judul { display: flex; align-items: flex-end; justify-content: space-between; margin: 8px 0 6px; }
      .judul h1 { font-size: 12.5pt; margin: 0; letter-spacing: .04em; }
      .judul .kode { font-size: 8pt; text-align: right; }
      .judul .st { display: inline-block; border: 1px solid #000; padding: 1px 6px; font-weight: 700; font-size: 8pt; }
      table { border-collapse: collapse; width: 100%; }
      .ident td { border: 1px solid #000; padding: 3px 5px; vertical-align: top; }
      .ident td.l { width: 15%; background: #f1f1f1; font-weight: 700; font-size: 8.6pt; }
      .sec { margin-top: 6px; page-break-inside: avoid; }
      .sec > .h { background: #e4e4e4; border: 1px solid #000; border-bottom: 0; padding: 3px 6px; font-weight: 700; font-size: 9.6pt; }
      .grid { table-layout: fixed; }
      .grid td { border: 1px solid #000; padding: 3px 5px; vertical-align: top; overflow-wrap: anywhere; }
      .grid td.l { background: #f7f7f7; font-size: 8.8pt; }
      .grid + .grid, .grid + .tb, .tb + .grid, .tb + .tb { border-top: 0; margin-top: -1px; }
      .tb th, .tb td { border: 1px solid #000; padding: 2px 4px; text-align: left; vertical-align: top; }
      .tb th { background: #f1f1f1; font-size: 8.4pt; }
      .tb td { height: 18px; }
      .cb { display: inline-block; margin-right: 12px; white-space: nowrap; }
      .isian { display: inline-block; min-width: 110px; border-bottom: 1px dotted #555; height: 1em; }
      .kotak-teks { min-height: 34px; }
      .catatan-kecil { font-size: 7.8pt; color: #333; }
      .ttd { margin-top: 10px; page-break-inside: avoid; }
      .ttd td { width: 50%; text-align: center; vertical-align: top; padding: 0 10px; }
      .ttd .ruang { height: 58px; }
      .ttd .nama { border-top: 1px solid #000; display: inline-block; min-width: 200px; padding-top: 2px; font-weight: 700; }
      .kaki { margin-top: 8px; font-size: 7.6pt; color: #444; display: flex; justify-content: space-between; border-top: 1px solid #999; padding-top: 3px; }
      .toolbar { position: fixed; top: 10px; right: 10px; display: flex; gap: 6px; }
      .toolbar button { font: 600 13px Arial; padding: 7px 14px; border-radius: 6px; border: 1px solid #0f172a; background: #0f172a; color: #fff; cursor: pointer; }
      .toolbar button.alt { background: #fff; color: #0f172a; }
      @media screen { body { max-width: 820px; margin: 24px auto; padding: 0 12px; background: #fff; } html { background: #e5e7eb; } }
      @media print { .toolbar { display: none; } }
   </style>
</head>
<body>
   <div class="toolbar"><button type="button" onclick="window.print()">Cetak</button><button type="button" class="alt" onclick="window.close()">Tutup</button></div>

   <div class="kop">
      <?php if ($logo): ?><img src="<?= $h($logo) ?>" alt=""><?php endif; ?>
      <div>
         <div class="nm"><?= $h($klinik['clinic_name'] ?? '') ?></div>
         <div class="al"><?= $h(trim(implode(', ', array_filter([$klinik['faskes_address'] ?? '', $klinik['faskes_city'] ?? ''])))) ?><?= !empty($klinik['faskes_phone']) ? ' · Telp. ' . $h($klinik['faskes_phone']) : '' ?></div>
      </div>
   </div>

   <div class="judul">
      <h1>ASESMEN GIZI &amp; NUTRISI<br><span style="font-size:8.6pt;font-weight:400;letter-spacing:0">Proses Asuhan Gizi Terstandar (PAGT)</span></h1>
      <div class="kode">
         <?php if ($asesmen): ?><span class="st"><?= $h(strtoupper($asesmen['status'])) ?></span><br>No. dokumen: GZ-<?= (int) $asesmen['id_gizi'] ?><?php else: ?><span class="st">FORMULIR KOSONG</span><?php endif; ?>
      </div>
   </div>

   <table class="ident">
      <tr><td class="l">Nama pasien</td><td><b><?= $h($pasien['patient_name']) ?></b></td><td class="l">No. RM</td><td><?= $h($pasien['nomor_rm']) ?></td></tr>
      <tr><td class="l">Tgl. lahir / Umur</td><td><?= $h($tgl($pasien['patient_datebirth'])) ?><?= $umur ? ' / ' . $h($umur) : '' ?></td><td class="l">Jenis kelamin</td><td><?= $h($pasien['patient_gender']) ?></td></tr>
      <tr><td class="l">No. kunjungan</td><td><?= $h($pasien['visit_ID']) ?></td><td class="l">Poli / ruang</td><td><?= $h($pasien['id_poli']) ?></td></tr>
      <tr><td class="l">DPJP</td><td><?= $h($pasien['id_doctor']) ?></td><td class="l">Diagnosis medis</td><td><?= $diagnosa !== '' ? $h($diagnosa) : $titik ?></td></tr>
      <tr><td class="l">Tanggal asesmen</td><td><?= $tglAses ? '<b>' . $h($tgl($tglAses)) . '</b>' : $titik ?></td><td class="l">Nutrisionis</td><td><?= !empty($asesmen['nutrisionis']) ? '<b>' . $h($asesmen['nutrisionis']) . '</b>' : $titik ?></td></tr>
   </table>

   <?php foreach (giziSkema() as $kode => [$judul, , $fields]):
      if ($kode === 'id') continue; ?>
      <div class="sec">
         <div class="h"><?= $h($kode . '. ' . $judul) ?></div>
         <?php
         // field sederhana dua per baris; field lebar (centang, teks panjang, tabel) satu baris penuh
         $sel = [];
         $flush = function () use (&$sel) {
            if (!$sel) return '';
            $html = '<table class="grid">' . GZ_COL4 . '<tr>';
            if (count($sel) === 1) $html .= '<td class="l">' . $sel[0][0] . '</td><td colspan="3">' . $sel[0][1] . '</td>';
            else foreach ($sel as [$lbl, $val]) $html .= '<td class="l">' . $lbl . '</td><td>' . $val . '</td>';
            $sel = [];
            return $html . '</tr></table>';
         };
         foreach ($fields as $key => $def):
            $label = $h(preg_replace('/\s+—.*$/u', '', $def['label']));
            switch ($def['tipe']):
               case 'radio':
               case 'select':
                  $isi = $def['tipe'] === 'radio' || count($def['opsi']) <= 5 ? $kotak($def['opsi'], $d[$key] ?? '') : $nilai($key, $def);
                  $sel[] = [$label, $isi];
                  break;
               case 'date':
               case 'text':
               case 'number':
               case 'calc':
                  if (!empty($def['panjang'])) {
                     echo $flush(), '<table class="grid">' . GZ_COL2 . '<tr><td class="l">' . $label . '</td><td class="kotak-teks">' . (($d[$key] ?? '') !== '' ? '<b>' . $h($d[$key]) . '</b>' : '') . '</td></tr></table>';
                     break;
                  }
                  $sel[] = [$label, $nilai($key, $def)];
                  break;
               case 'textarea':
                  echo $flush(), '<table class="grid">' . GZ_COL2 . '<tr><td class="l">' . $label . '</td><td class="kotak-teks">' . (($d[$key] ?? '') !== '' ? nl2br($h($d[$key])) : '') . '</td></tr></table>';
                  break;
               case 'checks':
                  echo $flush(), '<table class="grid">' . GZ_COL2 . '<tr><td class="l">' . $label . '</td><td>' . $kotak($def['opsi'], $d[$key] ?? [], ['Label Pangan' => 'Membaca Label Pangan']) . '</td></tr></table>';
                  break;
               case 'lab':
                  echo $flush(), '<table class="tb"><tr><th style="width:26%">Pemeriksaan</th><th style="width:20%">Hasil</th><th style="width:18%">Tanggal</th><th>Keterangan</th></tr>';
                  foreach (giziLab() as $lk => [$nama, $u]) {
                     $x = $d['lab'][$lk] ?? [];
                     echo '<tr><td>' . $h($nama) . '</td><td>' . (($x['nilai'] ?? '') !== '' ? '<b>' . $h($x['nilai']) . '</b> ' : '') . '<span class="catatan-kecil">' . $h($u) . '</span></td><td>' . $h($tgl($x['tanggal'] ?? '')) . '</td><td>' . $h($x['keterangan'] ?? '') . '</td></tr>';
                  }
                  echo '</table>';
                  break;
               case 'tabel':
                  $rows = $d[$key] ?? [];
                  $min = $key === 'recall' ? 5 : 3;   // baris kosong untuk isian manual
                  echo $flush(), '<table class="tb"><tr><th colspan="' . count($def['kolom']) . '" style="background:#f7f7f7">' . $h($def['label']) . '</th></tr><tr>';
                  foreach ($def['kolom'] as $c) echo '<th>' . $h($c[0]) . '</th>';
                  echo '</tr>';
                  for ($i = 0; $i < max($min, count($rows)); $i++) {
                     echo '<tr>';
                     foreach ($def['kolom'] as $ck => $c) {
                        $v = $rows[$i][$ck] ?? '';
                        echo '<td>' . $h($c[1] === 'date' ? $tgl($v) : $v) . '</td>';
                     }
                     echo '</tr>';
                  }
                  echo '</table>';
                  break;
            endswitch;
            if (count($sel) === 2) echo $flush();
         endforeach;
         echo $flush();
         ?>
      </div>
   <?php endforeach; ?>

   <table class="ttd">
      <tr>
         <td>Mengetahui,<br>Dokter Penanggung Jawab (DPJP)<div class="ruang"></div><span class="nama"><?= $h($pasien['id_doctor'] ?: ' ') ?></span></td>
         <td><?= $h(trim((string) ($klinik['faskes_city'] ?? ''))) ?><?= !empty($klinik['faskes_city']) ? ', ' : '' ?><?= $tglAses ? $h($tgl($tglAses)) : '.......................' ?><br>Nutrisionis / Dietisien<div class="ruang"></div><span class="nama"><?= $h($asesmen['nutrisionis'] ?? ' ') ?></span></td>
      </tr>
   </table>

   <div class="kaki">
      <span><?= $h($pasien['patient_name']) ?> · RM <?= $h($pasien['nomor_rm']) ?> · <?= $h($pasien['visit_ID']) ?></span>
      <span>Dicetak <?= $h(date('d/m/Y H:i')) ?><?= $pencetak !== '' ? ' oleh ' . $h($pencetak) : '' ?></span>
   </div>
</body>
</html>
