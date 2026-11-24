<?php
/**
 * Cetak Kartu Status Peserta KB (data dari kbAmbil() di controller/ranap/getFormStatusKB.php).
 * Tetap berupa potongan HTML agar aman di-bundle.
 * Perbaikan: getkb.php (SQL injection, tanpa batas faskes) menggabungkan ms_patient lewat nomor_rm saja -> bisa
 * menampilkan pasien faskes lain yang nomor RM-nya sama; data tidak pernah tampil (simpan tidak menyimpan visit_ID);
 * pendidikan & pekerjaan istri selalu "-"; usia dihitung ke hari ini (bukan tanggal kunjungan); tanggal format mentah.
 */
require_once '../../../database/connect.php';
if (!defined('KB_LIB')) define('KB_LIB', true);
require_once __DIR__ . '/../../../controller/ranap/getFormStatusKB.php';

$kbNo = trim((string) ($_GET['no'] ?? ''));
$kbIdc = (int) ($_SESSION['id_customer'] ?? 0);
$kbH = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
$st = $koneksi->prepare("SELECT id_visit FROM pasien_visit WHERE visit_ID = ? AND id_customer = ? LIMIT 1");
$st->bind_param('si', $kbNo, $kbIdc);
$st->execute();
$kbIdVisit = (int) ($st->get_result()->fetch_row()[0] ?? 0);
$st->close();
if (!$kbIdVisit) {
   echo '<p style="font-family:sans-serif;color:#b91c1c">Kunjungan tidak ditemukan untuk faskes ini.</p>';
   return;
}
$kb = kbAmbil($koneksi, $kbIdVisit);
$kbP = $kb['pasien'];
$kbD = $kb['statuskb'] ?: [];
$kbBln = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$kbTgl = fn($s) => preg_match('/^(\d{4})-(\d{2})-(\d{2})/', (string) $s, $m) ? (int) $m[3] . ' ' . $kbBln[(int) $m[2]] . ' ' . $m[1] : '-';
$kbV = fn($k, $suf = '') => ($x = trim((string) ($kbD[$k] ?? ''))) !== '' ? $kbH($x) . $suf : '-';
$kbYt = fn($k) => ['ya' => 'Ya', 'tidak' => 'Tidak'][strtolower((string) ($kbD[$k] ?? ''))] ?? '-';
?>
<div class="form-status-kb">
   <style>
      .form-status-kb { width: 210mm; min-height: 297mm; margin: 0 auto; font-family: "Times New Roman", serif; padding: 0 10mm; font-size: 13px; box-sizing: border-box; }
      .form-status-kb .title { text-align: center; font-size: 17px; font-weight: bold; text-decoration: underline; margin: 6px 0 12px; }
      .form-status-kb table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
      .form-status-kb td { border: 1px solid #000; padding: 4px 6px; vertical-align: top; }
      .form-status-kb .section-title { font-weight: bold; background: #eee; border: 1px solid #000; border-bottom: 0; padding: 4px 6px; }
      .form-status-kb .ttd { width: 70mm; margin: 14px 0 0 auto; text-align: center; }
      .form-status-kb .ttd .ruang { height: 18mm; }
      .form-status-kb .ttd b { display: block; border-top: 1px solid #000; padding-top: 2px; }
   </style>

   <?php include 'kopsurat.php'; ?>
   <div class="title">KARTU STATUS PESERTA KB</div>

   <table>
      <tr>
         <td width="50%"><strong>I. Nomor Kode Faskes KB</strong><br><?= $kbV('faskes_kode') ?></td>
         <td width="50%"><strong>II. Kode Keluarga Indonesia</strong><br><?= $kbV('kode_keluarga') ?></td>
      </tr>
   </table>

   <table>
      <tr>
         <td width="50%"><strong>III. Nama Peserta KB</strong><br><?= $kbH($kbP['nama_pasien'] ?: '-') ?> <span style="font-size:11px">(RM <?= $kbH($kbP['nomor_rm']) ?>)</span></td>
         <td><strong>IV. Tgl Lahir / Umur Istri</strong><br><?= $kbH($kbTgl($kbP['tanggal_lahir'])) ?> / <?= $kbH($kbP['usia'] ?: '-') ?></td>
      </tr>
      <tr>
         <td><strong>V. Nama Suami</strong><br><?= $kbV('nama_suami') ?></td>
         <td><strong>VI. Pendidikan Suami / Istri</strong><br>Suami: <?= $kbV('pendidikan_suami') ?><br>Istri: <?= $kbH($kbP['pendidikan_istri'] ?: '-') ?></td>
      </tr>
      <tr>
         <td><strong>VII. Alamat Peserta KB</strong><br><?= $kbH($kbP['alamat'] ?: '-') ?></td>
         <td><strong>VIII. Pekerjaan Suami / Istri</strong><br>Suami: <?= $kbV('pekerjaan_suami') ?><br>Istri: <?= $kbH($kbP['pekerjaan_istri'] ?: '-') ?></td>
      </tr>
   </table>

   <table>
      <tr>
         <td><strong>X. Jumlah Anak Hidup</strong><br>Laki-laki: <?= $kbV('anak_lk') ?> — Perempuan: <?= $kbV('anak_pr') ?></td>
         <td><strong>XI. Umur Anak Terakhir</strong><br><?= $kbV('umur_anak_terakhir', ' tahun') ?></td>
         <td><strong>XII. KB Terakhir</strong><br><?= $kbV('kb_terakhir') ?></td>
      </tr>
   </table>

   <div class="section-title">XIV. PENAPISAN (SKRINING)</div>
   <table>
      <tr>
         <td width="50%"><strong>1. Haid Terakhir</strong><br><?= $kbH($kbTgl($kbD['haid_terakhir'] ?? '')) ?></td>
         <td><strong>2. Hamil</strong><br><?= $kbYt('hamil') ?></td>
      </tr>
      <tr>
         <td><strong>3. Gravida / Partus / Abortus</strong><br>G <?= $kbV('gpa_g') ?> &nbsp; P <?= $kbV('gpa_p') ?> &nbsp; A <?= $kbV('gpa_a') ?></td>
         <td><strong>4. Menyusui</strong><br><?= $kbYt('menyusui') ?></td>
      </tr>
      <tr>
         <td><strong>5. Riwayat Penyakit</strong><br><span style="white-space:pre-line"><?= $kbV('riwayat_sakit') ?></span></td>
         <td><strong>6. Keadaan Umum</strong><br><?= $kbV('keadaan_umum') ?></td>
      </tr>
      <tr>
         <td><strong>7. Berat Badan</strong><br><?= $kbV('berat_badan', ' kg') ?></td>
         <td><strong>8. Tekanan Darah</strong><br><?= $kbV('tekanan_darah', ' mmHg') ?></td>
      </tr>
      <tr>
         <td colspan="2"><strong>9–12. Perlu Pemeriksaan Tambahan</strong><br><?= $kbYt('pemeriksaan_tambahan') ?></td>
      </tr>
   </table>

   <div class="section-title">XV. METODE KB YANG DIPILIH</div>
   <table>
      <tr><td><?= $kbV('metode_pilihan') ?></td></tr>
   </table>

   <table>
      <tr>
         <td width="50%"><strong>XVI. Tanggal Dilayani</strong><br><?= $kbH($kbTgl($kbD['tgl_dilayani'] ?? '')) ?></td>
         <td><strong>XVII. Tanggal Dicabut</strong><br><?= $kbH($kbTgl($kbD['tgl_dicabut'] ?? '')) ?></td>
      </tr>
   </table>

   <div class="ttd">
      XIX. Penanggung Jawab
      <div class="ruang"></div>
      <b><?= $kbV('penanggung_jawab') ?></b>
   </div>
   <?php if (!$kbD): ?>
      <p style="font-family:sans-serif;color:#92400e;font-size:12px" class="no-print">Catatan: kartu KB belum disimpan untuk kunjungan ini.</p>
   <?php endif; ?>
</div>
