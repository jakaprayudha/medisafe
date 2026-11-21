<?php
/**
 * Surat & Dokumen: pintu masuk pembuatan surat pasien.
 *
 * Setting nomor surat + jumlah surat bulan ini disiapkan di server (2 query) dan disisipkan ke halaman.
 * Dulu: setiap klik "Buat Surat" memanggil API setting lagi, lalu menampilkan 2 popup (loading + konfirmasi mode)
 * sebelum pindah ke form. Bila setting belum disimpan, banner di sini dan di form surat menjelaskannya
 * (controller surat menolak membuat surat tanpa setting).
 */
$title = 'Surat & Dokumen';
require '../../controller/view.php';
date_default_timezone_set('Asia/Jakarta');

$FL_IDC = (string) ($_SESSION['id_customer'] ?? '');
$FL_ADMIN = in_array((string) ($_SESSION['roles'] ?? ''), ['admin', 'superadmin', 'administrator'], true);
session_write_close();

// jenis surat: kunci setting => tampilan (urutan = urutan kartu)
$FL_JENIS = [
  'sakit'      => ['Surat Keterangan Sakit', 'Istirahat', 'Menyatakan pasien perlu istirahat sesuai pemeriksaan dokter.', 'fa-bed', 'danger', 'module/letter/form-sick', 'SKS/{NO}/{MM}/{YYYY}', 'surat_sakit'],
  'sehat'      => ['Surat Keterangan Sehat', 'Kesehatan', 'Menerangkan kondisi sehat pasien berdasarkan hasil pemeriksaan.', 'fa-heart-pulse', 'success', 'module/letter/form-sks', 'SKH/{NO}/{MM}/{YYYY}', 'surat_sehat'],
  'berobat'    => ['Surat Keterangan Berobat', 'Kunjungan', 'Bukti pasien telah datang dan mendapat pelayanan di faskes.', 'fa-stethoscope', 'primary', 'module/letter/form-medical', 'SKB/{NO}/{MM}/{YYYY}', 'surat_berobat'],
  'rawat_inap' => ['Surat Rawat Inap', 'Rawat inap', 'Keterangan pasien menjalani perawatan rawat inap.', 'fa-procedures', 'info', 'module/letter/form-outpatient', 'SRI/{NO}/{MM}/{YYYY}', 'surat_rawat_inap'],
  'mata'       => ['Surat Pemeriksaan Mata', 'Pemeriksaan', 'Hasil pemeriksaan visus / buta warna untuk keperluan administrasi.', 'fa-eye', 'warning', 'module/letter/form-eye', 'SPM/{NO}/{MM}/{YYYY}', 'surat_pemeriksaan_mata'],
  'kematian'   => ['Surat Keterangan Kematian', 'Kematian', 'Catatan / keterangan kematian pasien.', 'fa-file-medical', 'secondary', 'module/letter/form-death', 'SKM/{NO}/{MM}/{YYYY}', 'surat_kematian'],
];

$flSetting = null;
$flJumlah = array_fill_keys(array_keys($FL_JENIS), null);
$flAwal = date('Y-m-01');
$flAkhir = date('Y-m-t');
try {
  $st = $koneksi->prepare("SELECT * FROM setting_surat WHERE id_customer = ? LIMIT 1");
  $st->bind_param('s', $FL_IDC);
  $st->execute();
  $flSetting = $st->get_result()->fetch_assoc() ?: null;
  $st->close();

  // jumlah surat bulan ini per jenis (satu query). surat_sakit tidak menyimpan id_customer -> lewat pasien_visit.
  $bag = [];
  $tipe = '';
  $par = [];
  foreach ($FL_JENIS as $k => $j) {
    $bag[] = $k === 'sakit'
      ? "SELECT 'sakit' j, COUNT(*) n FROM surat_sakit s JOIN pasien_visit pv ON pv.id_visit = s.id_visit WHERE pv.id_customer = ? AND s.tanggal_surat BETWEEN ? AND ?"
      : "SELECT '$k' j, COUNT(*) n FROM `{$j[7]}` WHERE id_customer = ? AND tanggal_surat BETWEEN ? AND ?";
    $tipe .= 'sss';
    array_push($par, $FL_IDC, $flAwal, $flAkhir);
  }
  $st = $koneksi->prepare(implode(' UNION ALL ', $bag));
  $st->bind_param($tipe, ...$par);
  $st->execute();
  foreach ($st->get_result()->fetch_all(MYSQLI_ASSOC) as $r) $flJumlah[$r['j']] = (int) $r['n'];
  $st->close();
} catch (Throwable $e) {
  error_log('[form-letter] ' . $e->getMessage());
}
$flMode = strtoupper((string) ($flSetting['mode_nomor'] ?? 'AUTO'));
if (!in_array($flMode, ['AUTO', 'MANUAL'], true)) $flMode = 'AUTO';
$flTotal = array_sum(array_map('intval', $flJumlah));
$BLN = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$h = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .fl-icon { width: 40px; height: 40px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
    .fl-mode { border-radius: 12px; border: 1px solid #e5eaef; background: #fff; padding: 12px 14px; }
    .fl-mode.auto { border-color: #c7d7fe; background: #f5f8ff; }
    .fl-mode.manual { border-color: #a7f3d0; background: #f0fdf9; }
    .fl-mode.belum { border-color: #fde68a; background: #fffbeb; }
    .fl-card { border: 1px solid #e5eaef; border-radius: 14px; background: #fff; padding: 16px; height: 100%; display: flex; flex-direction: column; gap: 10px;
      text-decoration: none; color: inherit; transition: border-color .15s, box-shadow .15s, transform .15s; }
    .fl-card:hover, .fl-card:focus-visible { border-color: #5d87ff; box-shadow: 0 6px 18px rgba(93, 135, 255, .12); transform: translateY(-1px); color: inherit; outline: none; }
    .fl-card h6 { font-size: .98rem; margin: 0; color: #111827; }
    .fl-card .desc { font-size: .82rem; color: #475569; margin: 0; flex-grow: 1; }
    .fl-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 6px 12px; font-size: .76rem; color: #334155; border-top: 1px dashed #e5eaef; padding-top: 10px; }
    .fl-meta code { font-size: .74rem; color: #1e293b; background: #f1f5f9; border-radius: 4px; padding: 1px 5px; }
    .fl-go { margin-left: auto; font-weight: 600; color: #5d87ff; white-space: nowrap; }
    .fl-card:hover .fl-go { text-decoration: underline; }
    .fl-n { font-weight: 700; color: #111827; }
    .badge.bg-warning-subtle { color: #1f2937 !important; background-color: #fef3c7 !important; }
  </style>
</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <?php require 'sidebar.php'; ?>
    <div class="body-wrapper">
      <?php require 'navbar.php'; ?>
      <div class="body-wrapper-inner">
        <div class="container-fluid">

          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-3">
              <div class="d-flex flex-wrap align-items-center gap-3">
                <span class="fl-icon bg-primary-subtle text-primary"><i class="fas fa-envelope-open-text"></i></span>
                <div class="flex-grow-1" style="min-width:0">
                  <h5 class="fw-semibold mb-0">Surat &amp; Dokumen</h5>
                  <div class="text-muted small">Pilih jenis surat untuk dibuat dan dicetak. <?= $flTotal ?> surat dibuat pada <?= $BLN[(int) date('n')] . ' ' . date('Y') ?>.</div>
                </div>
                <?php if ($FL_ADMIN): ?>
                  <a class="btn btn-outline-primary btn-sm" href="module/admin/setting_surat"><i class="fas fa-sliders-h me-1"></i>Atur nomor surat</a>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <?php if (!$flSetting): ?>
            <div class="fl-mode belum d-flex flex-wrap align-items-center gap-2 mb-3">
              <i class="fas fa-triangle-exclamation text-warning"></i>
              <div class="small flex-grow-1"><b>Nomor surat belum diatur.</b> Surat baru belum bisa dibuat sampai pengaturan nomor surat (otomatis / manual) disimpan. Surat lama tetap bisa dilihat dan dicetak.</div>
              <?php if ($FL_ADMIN): ?><a class="btn btn-sm btn-warning text-dark" href="module/admin/setting_surat">Atur sekarang</a><?php else: ?><span class="small text-muted">Hubungi admin untuk mengatur.</span><?php endif; ?>
            </div>
          <?php elseif ($flMode === 'AUTO'): ?>
            <div class="fl-mode auto d-flex align-items-center gap-2 mb-3">
              <i class="fas fa-wand-magic-sparkles text-primary"></i>
              <div class="small"><b>Nomor surat otomatis.</b> Sistem memberi nomor sesuai format &amp; nomor terakhir di bawah setiap jenis surat.</div>
            </div>
          <?php else: ?>
            <div class="fl-mode manual d-flex align-items-center gap-2 mb-3">
              <i class="fas fa-pen-to-square text-success"></i>
              <div class="small"><b>Nomor surat manual.</b> Nomor diisi sendiri pada form masing-masing surat.</div>
            </div>
          <?php endif; ?>

          <div class="row g-3">
            <?php foreach ($FL_JENIS as $k => [$nama, $tag, $desc, $ikon, $warna, $url, $fmtBawaan]):
              $fmt = $flSetting["format_$k"] ?? $fmtBawaan;
              $no = (int) ($flSetting["nomor_$k"] ?? 0);
              $n = $flJumlah[$k];
            ?>
              <div class="col-12 col-md-6 col-xl-4">
                <a class="fl-card" href="<?= $h($url) ?>" data-jenis="<?= $h($k) ?>">
                  <div class="d-flex align-items-center gap-3">
                    <span class="fl-icon bg-<?= $warna ?>-subtle text-<?= $warna === 'secondary' ? 'dark' : $warna ?>"><i class="fas <?= $ikon ?>"></i></span>
                    <div style="min-width:0">
                      <h6 class="fw-semibold"><?= $h($nama) ?></h6>
                      <span class="badge bg-<?= $warna ?>-subtle text-<?= $warna === 'secondary' ? 'dark' : $warna ?>"><?= $h($tag) ?></span>
                    </div>
                  </div>
                  <p class="desc"><?= $h($desc) ?></p>
                  <div class="fl-meta">
                    <span title="Surat <?= $h($nama) ?> dengan tanggal surat bulan ini"><i class="far fa-calendar me-1"></i>Bulan ini <span class="fl-n"><?= $n === null ? '-' : $n ?></span></span>
                    <?php if ($flMode === 'AUTO'): ?>
                      <span title="Format nomor · nomor terakhir yang terpakai"><code><?= $h($fmt) ?></code> · terakhir <span class="fl-n"><?= $no ?></span></span>
                    <?php else: ?>
                      <span><i class="fas fa-pen me-1"></i>Nomor manual</span>
                    <?php endif; ?>
                    <span class="fl-go">Buat surat <i class="fas fa-arrow-right ms-1"></i></span>
                  </div>
                </a>
              </div>
            <?php endforeach; ?>
          </div>

        </div>
      </div>
    </div>
  </div>
  <?php require 'library.php'; ?>
</body>

</html>
