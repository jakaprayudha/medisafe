<?php
$title = 'Kontrak Kerjasama';
require '../../controller/view.php';

/*
 * Ringkasan + pratinjau kontrak kerja sama faskes yang sedang login.
 * Dokumen kontrak dirender module/administrator/kontrak.php (?embed=1 = tanpa toolbar) di dalam iframe.
 */
$idc = (int) ($_SESSION['id_customer'] ?? 0);
$k = null;
if ($idc > 0) {
  $st = mysqli_prepare($koneksi, "SELECT sc.id, sc.clinic_name, f.id_faskes, f.contract_number, f.order_number, f.contract_date, f.contract_start, f.contract_end,
      f.contract_amount, f.faskes_payment, f.faskes_status, f.pic_name, f.pic_phone, f.pic_email
    FROM setting_clinic sc LEFT JOIN ms_faskes f ON f.id_clinic = sc.id WHERE sc.id_customer = ? LIMIT 1");
  mysqli_stmt_bind_param($st, 'i', $idc);
  mysqli_stmt_execute($st);
  $k = mysqli_fetch_assoc(mysqli_stmt_get_result($st)) ?: null;
  mysqli_stmt_close($st);
}

function kTgl($v): ?int
{
  $t = $v ? strtotime($v) : false;
  return ($t && (int) date('Y', $t) >= 2000) ? $t : null; // 1970-01-01 / 0000-00-00 = kosong
}
function kTglIndo(?int $t): string
{
  if (!$t) return '-';
  $b = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
  return date('j', $t) . ' ' . $b[(int) date('n', $t)] . ' ' . date('Y', $t);
}
function kE($v): string
{
  return htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
}

$ada = $k && $k['id_faskes'];
$mulai = $ada ? kTgl($k['contract_start']) : null;
$akhir = $ada ? kTgl($k['contract_end']) : null;
$hariIni = strtotime('today');
$sisa = $akhir ? (int) floor(($akhir - $hariIni) / 86400) : null;
$durasi = ($mulai && $akhir && $akhir > $mulai) ? ($akhir - $mulai) / 86400 : 0;
$jalan = $durasi ? max(0, min(100, round(($hariIni - $mulai) / 86400 / $durasi * 100))) : 0;

if (!$ada) {
  $status = ['Belum ada data', 'secondary', 'fa-file-circle-question'];
} elseif (!$akhir) {
  $status = ['Masa berlaku belum diisi', 'secondary', 'fa-calendar-xmark'];
} elseif ($sisa < 0) {
  $status = ['Berakhir', 'danger', 'fa-circle-xmark'];
} elseif ($mulai && $mulai > $hariIni) {
  $status = ['Belum dimulai', 'info', 'fa-hourglass-start'];
} elseif ($sisa <= 60) {
  $status = ['Segera berakhir', 'warning', 'fa-triangle-exclamation'];
} else {
  $status = ['Aktif', 'success', 'fa-circle-check'];
}
if ($sisa === null) $sisaTeks = '-';
elseif ($sisa < 0) $sisaTeks = 'Berakhir ' . abs($sisa) . ' hari lalu';
elseif ($sisa === 0) $sisaTeks = 'Berakhir hari ini';
else $sisaTeks = $sisa . ' hari lagi';

$docUrl = $k ? 'module/administrator/kontrak.php?no=' . (int) $k['id'] : '';
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .min-w-0 { min-width: 0; }
    .kt-icon { width: 40px; height: 40px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
    .kt-kpi { border: 1px solid #e5eaef; border-radius: 12px; background: #fff; padding: 14px 16px; height: 100%; }
    .kt-label { font-size: .72rem; color: #6c757d; text-transform: uppercase; letter-spacing: .03em; }
    .kt-val { font-size: 1.05rem; font-weight: 700; color: #2a3547; line-height: 1.3; word-break: break-word; }
    .kt-prog { height: 8px; border-radius: 4px; background: #eef1f4; overflow: hidden; }
    .kt-prog > div { height: 100%; border-radius: 4px; }
    .kt-doc { background: #e9ecef; border-radius: 0 0 12px 12px; position: relative; min-height: 420px; }
    #ktFrame { display: block; width: 100%; height: 1200px; border: 0; opacity: 0; transition: opacity .2s; }
    #ktFrame.siap { opacity: 1; }
    #ktLoading { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; flex-direction: column; color: #6c757d; }
    .kt-pic a { color: inherit; }
    @media (max-width: 575.98px) { .kt-actions .btn span { display: none; } }
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

          <!-- HEADER -->
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-3">
              <div class="d-flex flex-wrap align-items-center gap-3">
                <span class="kt-icon bg-primary-subtle text-primary"><i class="fas fa-file-contract"></i></span>
                <div class="min-w-0 flex-grow-1">
                  <h5 class="fw-semibold mb-0">Kontrak Kerjasama</h5>
                  <div class="text-muted small">Perjanjian penyediaan &amp; penggunaan Sistem Informasi Medisafe<?= $k ? ' — ' . kE($k['clinic_name']) : '' ?></div>
                </div>
                <?php if ($ada): ?>
                  <span class="badge bg-<?= $status[1] ?>-subtle text-<?= $status[1] ?> px-3 py-2"><i class="fas <?= $status[2] ?> me-1"></i><?= kE($status[0]) ?></span>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <?php if (!$k): ?>
            <div class="card border-0 shadow-sm"><div class="card-body text-center py-5">
              <span class="kt-icon bg-warning-subtle text-warning mb-3" style="width:64px;height:64px;border-radius:50%;font-size:1.6rem"><i class="fas fa-file-circle-exclamation"></i></span>
              <h5 class="fw-semibold">Data klinik tidak ditemukan</h5>
              <p class="text-muted mb-0">Sesi faskes tidak valid atau pengaturan klinik belum dibuat. Silakan login ulang atau hubungi tim Medisafe.</p>
            </div></div>
          <?php elseif (!$ada): ?>
            <div class="card border-0 shadow-sm"><div class="card-body text-center py-5">
              <span class="kt-icon bg-secondary-subtle text-secondary mb-3" style="width:64px;height:64px;border-radius:50%;font-size:1.6rem"><i class="fas fa-file-circle-question"></i></span>
              <h5 class="fw-semibold">Kontrak belum tersedia</h5>
              <p class="text-muted mb-0">Data kontrak kerja sama untuk <?= kE($k['clinic_name']) ?> belum diinput oleh tim Medisafe.</p>
            </div></div>
          <?php else: ?>

            <!-- RINGKASAN -->
            <div class="row g-3 mb-3">
              <div class="col-sm-6 col-xl-3">
                <div class="kt-kpi">
                  <div class="d-flex align-items-center gap-2 mb-2"><span class="kt-icon bg-<?= $status[1] ?>-subtle text-<?= $status[1] ?>"><i class="fas fa-calendar-days"></i></span>
                    <div class="min-w-0"><div class="kt-label">Masa berlaku</div><div class="kt-val"><?= kE($sisaTeks) ?></div></div></div>
                  <div class="kt-prog"><div class="bg-<?= $status[1] ?>" style="width:<?= $jalan ?>%"></div></div>
                  <div class="d-flex justify-content-between small text-muted mt-1"><span><?= kTglIndo($mulai) ?></span><span><?= kTglIndo($akhir) ?></span></div>
                </div>
              </div>
              <div class="col-sm-6 col-xl-3">
                <div class="kt-kpi">
                  <div class="d-flex align-items-center gap-2"><span class="kt-icon bg-primary-subtle text-primary"><i class="fas fa-hashtag"></i></span>
                    <div class="min-w-0"><div class="kt-label">Nomor kontrak</div><div class="kt-val"><?= kE($k['contract_number'] ?: '-') ?></div></div></div>
                  <div class="small text-muted mt-2">Ditandatangani <?= kTglIndo(kTgl($k['contract_date'])) ?><?= $k['order_number'] ? ' · No. order ' . kE($k['order_number']) : '' ?></div>
                </div>
              </div>
              <div class="col-sm-6 col-xl-3">
                <div class="kt-kpi">
                  <div class="d-flex align-items-center gap-2"><span class="kt-icon bg-success-subtle text-success"><i class="fas fa-money-bill-wave"></i></span>
                    <div class="min-w-0"><div class="kt-label">Nilai kontrak</div><div class="kt-val">Rp <?= number_format((float) $k['contract_amount'], 0, ',', '.') ?></div></div></div>
                  <div class="small text-muted mt-2">Pembayaran: <?= kE($k['faskes_payment'] ?: '-') ?></div>
                </div>
              </div>
              <div class="col-sm-6 col-xl-3">
                <div class="kt-kpi kt-pic">
                  <div class="d-flex align-items-center gap-2"><span class="kt-icon bg-info-subtle text-info"><i class="fas fa-user-tie"></i></span>
                    <div class="min-w-0"><div class="kt-label">Penanggung jawab</div><div class="kt-val text-truncate" title="<?= kE($k['pic_name']) ?>"><?= kE($k['pic_name'] ?: '-') ?></div></div></div>
                  <div class="small text-muted mt-2 text-truncate">
                    <?php if ($k['pic_phone']): ?><i class="fas fa-phone me-1"></i><a href="tel:<?= kE(preg_replace('/[^\d+]/', '', $k['pic_phone'])) ?>"><?= kE($k['pic_phone']) ?></a><?php endif; ?>
                    <?php if ($k['pic_email']): ?><span class="ms-2"><i class="fas fa-envelope me-1"></i><a href="mailto:<?= kE($k['pic_email']) ?>"><?= kE($k['pic_email']) ?></a></span><?php endif; ?>
                    <?php if (!$k['pic_phone'] && !$k['pic_email']): ?>Kontak belum diisi<?php endif; ?>
                  </div>
                </div>
              </div>
            </div>

            <?php if ($status[1] === 'warning' || $status[1] === 'danger'): ?>
              <div class="alert alert-<?= $status[1] ?> d-flex align-items-center gap-2 py-2">
                <i class="fas <?= $status[2] ?>"></i>
                <div class="small"><?= $status[1] === 'danger' ? 'Masa berlaku kontrak sudah berakhir.' : 'Masa berlaku kontrak akan berakhir ' . kE($sisaTeks) . '.' ?> Hubungi tim Medisafe untuk perpanjangan.</div>
              </div>
            <?php endif; ?>

            <!-- DOKUMEN -->
            <div class="card border-0 shadow-sm mb-3">
              <div class="card-body py-2 px-3 d-flex flex-wrap align-items-center gap-2 border-bottom">
                <div class="fw-semibold small flex-grow-1"><i class="fas fa-file-lines me-1 text-muted"></i>Dokumen perjanjian</div>
                <div class="kt-actions d-flex gap-1">
                  <button type="button" class="btn btn-light btn-sm" id="ktRefresh" title="Muat ulang"><i class="fas fa-sync-alt"></i></button>
                  <a class="btn btn-light btn-sm" href="<?= kE($docUrl) ?>" target="_blank" rel="noopener" title="Buka di tab baru"><i class="fas fa-up-right-from-square me-1"></i><span>Tab baru</span></a>
                  <button type="button" class="btn btn-primary btn-sm" id="ktPrint" disabled><i class="fas fa-print me-1"></i><span>Cetak / PDF</span></button>
                </div>
              </div>
              <div class="kt-doc">
                <div id="ktLoading"><div class="spinner-border text-primary mb-2" role="status"></div><div class="small">Memuat dokumen kontrak…</div></div>
                <iframe id="ktFrame" title="Dokumen Kontrak Kerjasama" src="<?= kE($docUrl) ?>&amp;embed=1"></iframe>
              </div>
            </div>

          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <?php require 'library.php'; ?>
  <?php if ($ada): ?>
    <script>
      (function() {
        const frame = document.getElementById('ktFrame');
        const loading = document.getElementById('ktLoading');
        const btnPrint = document.getElementById('ktPrint');
        const URL_DOC = <?= json_encode($docUrl . '&embed=1') ?>;
        let ro = null;

        // tinggi iframe mengikuti isi dokumen (tidak ada scroll ganda)
        function pasTinggi() {
          try {
            const d = frame.contentDocument;
            if (d && d.body) frame.style.height = Math.max(400, d.documentElement.scrollHeight) + 'px';
          } catch (e) {}
        }
        frame.addEventListener('load', function() {
          loading.style.display = 'none';
          frame.classList.add('siap');
          btnPrint.disabled = false;
          pasTinggi();
          try {
            if (ro) ro.disconnect();
            ro = new ResizeObserver(pasTinggi);
            ro.observe(frame.contentDocument.body);
          } catch (e) {}
        });
        document.getElementById('ktRefresh').addEventListener('click', function() {
          loading.style.display = 'flex';
          frame.classList.remove('siap');
          btnPrint.disabled = true;
          frame.src = URL_DOC + '&t=' + Date.now();
        });
        btnPrint.addEventListener('click', function() {
          try {
            frame.contentWindow.focus();
            frame.contentWindow.print();
          } catch (e) {
            // fallback: buka tab baru untuk dicetak dari sana
            window.open(URL_DOC.replace('&embed=1', ''), '_blank', 'noopener');
          }
        });
      })();
    </script>
  <?php endif; ?>
</body>

</html>
