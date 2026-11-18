<?php
$title = 'Detail Pasien';
require '../../controller/view.php';

// pasien wajib milik faskes sesi
$idPasien = (int) ($_GET['pt'] ?? 0);
$idcSesi = (int) ($_SESSION['id_customer'] ?? 0);
$pasien = null;
$ringkas = ['total' => 0, 'terakhir' => null];
if ($idPasien > 0 && $idcSesi > 0) {
  $stmt = $koneksi->prepare("SELECT * FROM ms_patient WHERE id_patient = ? AND id_customer = ? LIMIT 1");
  $stmt->bind_param('ii', $idPasien, $idcSesi);
  $stmt->execute();
  $pasien = $stmt->get_result()->fetch_assoc() ?: null;
  $stmt->close();

  if ($pasien) {
    $ptStr = (string) $idPasien;
    $stmt = $koneksi->prepare("SELECT COUNT(*) total, MAX(visit_date) terakhir FROM pasien_visit
      WHERE id_patient = ? AND id_customer = ? AND (visit_status IS NULL OR visit_status <> 99)");
    $stmt->bind_param('si', $ptStr, $idcSesi);
    $stmt->execute();
    $ringkas = $stmt->get_result()->fetch_assoc() ?: $ringkas;
    $stmt->close();
  }
}

$h = fn($v, $kosong = '-') => trim((string) ($v ?? '')) === '' ? $kosong : htmlspecialchars(trim((string) $v), ENT_QUOTES, 'UTF-8');
$umur = function (?string $tgl): string {
  if (!$tgl || !preg_match('/^\d{4}-\d{2}-\d{2}/', $tgl)) return '';
  try {
    $d = (new DateTime(substr($tgl, 0, 10)))->diff(new DateTime('today'));
  } catch (Exception $e) {
    return '';
  }
  if ($d->invert) return '';
  return $d->y > 0 ? "{$d->y} tahun" : "{$d->m} bulan";
};
$tglId = function (?string $t): string {
  if (!$t || !($ts = strtotime($t))) return '-';
  $bln = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
  return date('j', $ts) . ' ' . $bln[(int) date('n', $ts)] . ' ' . date('Y', $ts);
};

// ?tabs=1..5 (dipakai navbar): 1 data umum, 2 kontak, 3 darurat, 4 dokumen, 5 riwayat
$tabAwal = ['1' => 'umum', '2' => 'kontak', '3' => 'darurat', '4' => 'dokumen', '5' => 'riwayat'][$_GET['tabs'] ?? ''] ?? 'riwayat';
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .min-w-0 {
      min-width: 0;
    }

    /* ---------- profil ---------- */
    .pd-hero {
      border-radius: 16px;
      background: #fff;
      border: 1px solid #e5eaef;
      overflow: hidden;
    }

    .pd-hero-top {
      background: linear-gradient(120deg, #5d87ff 0%, #4570ea 60%, #49beff 100%);
      height: 72px;
    }

    .pd-hero-body {
      padding: 0 24px 20px;
      margin-top: -40px;
    }

    .pd-ava {
      width: 88px;
      height: 88px;
      border-radius: 50%;
      border: 4px solid #fff;
      box-shadow: 0 4px 14px rgba(42, 53, 71, .12);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.8rem;
      font-weight: 800;
      overflow: hidden;
      flex-shrink: 0;
    }

    .pd-ava img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .pd-ava.l {
      background: #ecf2ff;
      color: #5d87ff;
    }

    .pd-ava.p {
      background: #fdebf4;
      color: #e0569b;
    }

    .pd-ava.x {
      background: #f2f5f9;
      color: #7c8fac;
    }

    .pd-headtext {
      padding-top: 48px;
    }

    @media (max-width: 767.98px) {
      .pd-headtext {
        padding-top: 0;
        flex-basis: 100%;
      }
    }

    .pd-name {
      font-size: 1.35rem;
      font-weight: 700;
      color: #2a3547;
      line-height: 1.25;
    }

    .pd-badge {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      font-size: .74rem;
      font-weight: 600;
      padding: .3em .65em;
      border-radius: 6px;
    }

    .pd-rm {
      font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
      background: #2a3547;
      color: #fff;
    }

    .pd-info {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
      gap: 12px 20px;
      margin-top: 16px;
    }

    .pd-info span {
      display: block;
      font-size: .7rem;
      color: #7c8fac;
      text-transform: uppercase;
      letter-spacing: .3px;
    }

    .pd-info b {
      font-size: .86rem;
      color: #2a3547;
      font-weight: 600;
      word-break: break-word;
    }

    .pd-info .mono {
      font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
    }

    .pd-kpi {
      border: 1px solid #e5eaef;
      border-radius: 12px;
      padding: 10px 16px;
      text-align: center;
      min-width: 110px;
    }

    .pd-kpi .v {
      font-size: 1.3rem;
      font-weight: 800;
      color: #2a3547;
      line-height: 1.2;
    }

    .pd-kpi .l {
      font-size: .7rem;
      color: #7c8fac;
    }

    .pd-alergi {
      border-radius: 10px;
      background: #fdede8;
      color: #c0392b;
      padding: 8px 12px;
      font-size: .84rem;
      margin-top: 14px;
    }

    /* ---------- tab ---------- */
    .pd-tabs {
      border-bottom: 1px solid #e5eaef;
      gap: 4px;
      flex-wrap: nowrap;
      overflow-x: auto;
      scrollbar-width: none;
    }

    .pd-tabs .nav-link {
      border: 0;
      border-bottom: 2px solid transparent;
      border-radius: 0;
      color: #5a6a85;
      font-weight: 600;
      font-size: .875rem;
      padding: .75rem 1rem;
      white-space: nowrap;
      background: none;
    }

    .pd-tabs .nav-link:hover {
      color: #5d87ff;
    }

    .pd-tabs .nav-link.active {
      color: #5d87ff;
      border-bottom-color: #5d87ff;
      background: none;
    }

    .pd-sec {
      font-size: .74rem;
      font-weight: 700;
      color: #5a6a85;
      text-transform: uppercase;
      letter-spacing: .4px;
      display: flex;
      align-items: center;
      gap: 8px;
      margin: 6px 0 14px;
    }

    .pd-sec::after {
      content: '';
      flex: 1;
      height: 1px;
      background: #e5eaef;
    }

    .pd-save {
      position: sticky;
      bottom: 0;
      background: #fff;
      padding: 12px 0 0;
      border-top: 1px solid #eef2f6;
      margin-top: 8px;
    }

    /* ---------- riwayat (timeline) ---------- */
    .rw-list {
      position: relative;
    }

    .rw-item {
      display: grid;
      grid-template-columns: 74px 18px 1fr;
      gap: 0 12px;
      position: relative;
      padding-bottom: 14px;
    }

    .rw-item::before {
      content: '';
      position: absolute;
      left: 94px;
      top: 22px;
      bottom: -4px;
      width: 2px;
      background: #e5eaef;
    }

    .rw-item:last-child::before {
      display: none;
    }

    .rw-date {
      text-align: right;
      padding-top: 10px;
      line-height: 1.15;
    }

    .rw-date .d {
      font-size: 1.35rem;
      font-weight: 800;
      color: #2a3547;
    }

    .rw-date .m {
      font-size: .72rem;
      color: #5a6a85;
      font-weight: 600;
    }

    .rw-date .t {
      font-size: .7rem;
      color: #7c8fac;
    }

    .rw-dot {
      width: 14px;
      height: 14px;
      border-radius: 50%;
      margin-top: 16px;
      border: 3px solid #fff;
      box-shadow: 0 0 0 2px #e5eaef;
      position: relative;
      z-index: 1;
    }

    .rw-card {
      border: 1px solid #e5eaef;
      border-radius: 12px;
      background: #fff;
      min-width: 0;
      transition: box-shadow .15s;
    }

    .rw-card:hover {
      box-shadow: 0 4px 14px rgba(42, 53, 71, .06);
    }

    .rw-head {
      display: flex;
      gap: 12px;
      padding: 12px 14px;
      cursor: pointer;
      align-items: center;
    }

    .rw-head[aria-expanded="true"] .rw-chev {
      transform: rotate(180deg);
    }

    .rw-chev {
      color: #7c8fac;
      transition: transform .2s;
    }

    .rw-badge {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      font-size: .7rem;
      font-weight: 600;
      padding: .2em .6em;
      border-radius: 6px;
    }

    .rw-diag {
      font-size: .86rem;
      color: #2a3547;
    }

    .rw-meta {
      font-size: .74rem;
      color: #5a6a85;
    }

    .rw-body {
      border-top: 1px solid #eef2f6;
      padding: 14px;
    }

    .rw-tabs .nav-link {
      font-size: .8rem;
      padding: .35rem .8rem;
      border-radius: 20px;
      color: #5a6a85;
    }

    .rw-tabs .nav-link.active {
      background: #ecf2ff;
      color: #5d87ff;
    }

    .rw-vitals {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
      gap: 8px;
    }

    .rw-vital {
      background: #f6f9fc;
      border-radius: 10px;
      padding: 8px 10px;
    }

    .rw-vital .val {
      font-weight: 700;
      color: #2a3547;
      font-size: .95rem;
    }

    .rw-vital .val small {
      font-weight: 500;
      color: #7c8fac;
      font-size: .7rem;
    }

    .rw-vital .lbl {
      font-size: .7rem;
      color: #7c8fac;
    }

    .rw-field {
      margin-bottom: 10px;
    }

    .rw-field>span {
      display: block;
      font-size: .7rem;
      color: #7c8fac;
      text-transform: uppercase;
      letter-spacing: .3px;
      margin-bottom: 2px;
    }

    .rw-field>div {
      color: #2a3547;
    }

    .rw-resep {
      border: 1px solid #eef2f6;
      border-radius: 10px;
      padding: 10px 12px;
      margin-bottom: 10px;
    }

    .rw-resep thead th {
      font-size: .7rem;
      color: #7c8fac;
      font-weight: 600;
      border-bottom-width: 1px;
    }

    .rw-empty {
      text-align: center;
      color: #7c8fac;
      font-size: .85rem;
      padding: 18px 0;
    }

    .rw-empty i {
      display: block;
      font-size: 1.4rem;
      margin-bottom: 6px;
      opacity: .6;
    }

    .rw-skel {
      height: 86px;
      border-radius: 12px;
      margin: 0 0 14px 104px;
      background: linear-gradient(90deg, #f2f5f9 25%, #e9eef5 37%, #f2f5f9 63%);
      background-size: 400% 100%;
      animation: rwSkel 1.4s ease infinite;
    }

    @keyframes rwSkel {
      0% {
        background-position: 100% 50%;
      }

      100% {
        background-position: 0 50%;
      }
    }

    @media (max-width: 575.98px) {
      .rw-item {
        grid-template-columns: 52px 14px 1fr;
        gap: 0 8px;
      }

      .rw-item::before {
        left: 65px;
      }

      .rw-date .d {
        font-size: 1.1rem;
      }

      .rw-head {
        flex-wrap: wrap;
      }

      .rw-skel {
        margin-left: 74px;
      }
    }

    /* ---------- dokumen ---------- */
    .dk-doc {
      border: 1.5px dashed #dfe5ef;
      border-radius: 14px;
      padding: 14px;
      height: 100%;
      display: flex;
      flex-direction: column;
      gap: 10px;
      background: #fff;
    }

    .dk-doc.ada {
      border-style: solid;
      border-color: #e5eaef;
    }

    .dk-thumb {
      aspect-ratio: 16 / 10;
      border-radius: 10px;
      background: #f6f9fc;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      color: #adb5bd;
      font-size: 2rem;
      cursor: default;
    }

    .dk-doc.ada .dk-thumb {
      cursor: zoom-in;
    }

    .dk-thumb img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
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

          <?php if (!$pasien): ?>
            <div class="card">
              <div class="card-body text-center py-5">
                <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
                <h5 class="fw-semibold">Pasien tidak ditemukan</h5>
                <p class="text-muted mb-4">Data pasien tidak ada atau bukan milik faskes Anda.</p>
                <a href="module/admin/patient" class="btn btn-primary"><i class="fas fa-arrow-left me-1"></i> Kembali ke Data Pasien</a>
              </div>
            </div>
          <?php else:
            $g = preg_match('/^l/i', (string) $pasien['patient_gender']) ? 'l' : (preg_match('/^p/i', (string) $pasien['patient_gender']) ? 'p' : 'x');
            $foto = ltrim(preg_replace('#^(\.\./)+#', '', (string) ($pasien['face_image'] ?? '')), '/');
            $kata = preg_split('/\s+/', trim((string) $pasien['patient_name'])) ?: ['?'];
            $inisial = strtoupper(mb_substr($kata[0], 0, 1) . (count($kata) > 1 ? mb_substr(end($kata), 0, 1) : ''));
            $alamat = implode(', ', array_filter(array_map('trim', [
              (string) $pasien['patient_address'],
              (string) $pasien['patient_kelurahan'],
              (string) $pasien['patient_kecamatan'],
              (string) $pasien['patient_kabupaten'],
            ])));
            $hp = trim((string) $pasien['patient_phone']);
            $wa = preg_replace('/\D/', '', $hp);
            if (str_starts_with($wa, '0')) $wa = '62' . substr($wa, 1);
          ?>

            <!-- PROFIL -->
            <div class="pd-hero mb-3">
              <div class="pd-hero-top"></div>
              <div class="pd-hero-body">
                <div class="d-flex flex-wrap align-items-end gap-3">
                  <div class="pd-ava <?= $g ?>" id="pdAva">
                    <?php if ($foto !== ''): ?>
                      <img src="<?= $h($foto) ?>" alt="Foto <?= $h($pasien['patient_name']) ?>" data-inisial="<?= $h($inisial) ?>" onerror="const p=this.parentNode;p.textContent=this.dataset.inisial">
                    <?php else: ?>
                      <?= $h($inisial) ?>
                    <?php endif; ?>
                  </div>
                  <div class="flex-grow-1 min-w-0 pb-1 pd-headtext">
                    <div class="pd-name" id="pdNama"><?= $h($pasien['patient_name']) ?></div>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                      <span class="pd-badge pd-rm" title="Nomor rekam medis"><i class="fas fa-hashtag"></i><span id="pdRm"><?= $h($pasien['nomor_rm']) ?></span></span>
                      <?php if ($g !== 'x'): ?>
                        <span class="pd-badge <?= $g === 'l' ? 'bg-primary-subtle text-primary' : '' ?>" <?= $g === 'p' ? 'style="background:#fdebf4;color:#e0569b"' : '' ?>>
                          <i class="fas fa-<?= $g === 'l' ? 'mars' : 'venus' ?>"></i><?= $h($pasien['patient_gender']) ?><?= $umur($pasien['patient_datebirth']) ? ' · ' . $umur($pasien['patient_datebirth']) : '' ?>
                        </span>
                      <?php endif; ?>
                      <?php if (trim((string) $pasien['patient_bpjs']) !== ''): ?>
                        <span class="pd-badge bg-success-subtle text-success"><i class="fas fa-id-card"></i>BPJS</span>
                      <?php else: ?>
                        <span class="pd-badge bg-secondary-subtle text-secondary">Non-BPJS</span>
                      <?php endif; ?>
                      <?php if (trim((string) $pasien['idsh']) !== ''): ?>
                        <span class="pd-badge bg-info-subtle text-info" title="ID IHS Satu Sehat"><i class="fas fa-heartbeat"></i>IHS <?= $h($pasien['idsh']) ?></span>
                      <?php endif; ?>
                      <?php if (trim((string) $pasien['patient_blood']) !== ''): ?>
                        <span class="pd-badge bg-danger-subtle text-danger"><i class="fas fa-tint"></i>Gol. <?= $h($pasien['patient_blood']) ?></span>
                      <?php endif; ?>
                    </div>
                  </div>
                  <div class="d-flex gap-2 pb-1 pd-headtext">
                    <div class="pd-kpi">
                      <div class="v"><?= number_format((int) $ringkas['total'], 0, ',', '.') ?></div>
                      <div class="l">Kunjungan</div>
                    </div>
                    <div class="pd-kpi">
                      <div class="v" style="font-size:.95rem;padding:4px 0"><?= $h($tglId($ringkas['terakhir'])) ?></div>
                      <div class="l">Kunjungan terakhir</div>
                    </div>
                  </div>
                </div>

                <div class="pd-info">
                  <div><span>NIK</span><b class="mono"><?= $h($pasien['patient_nik']) ?></b></div>
                  <div><span>No. BPJS</span><b class="mono"><?= $h($pasien['patient_bpjs']) ?></b></div>
                  <div><span>Tempat, tanggal lahir</span><b><?= $h($pasien['patient_place'], '') ?><?= trim((string) $pasien['patient_place']) !== '' ? ', ' : '' ?><?= $h($tglId($pasien['patient_datebirth'])) ?></b></div>
                  <div><span>No. HP</span><b><?php if ($hp !== '' && strlen($wa) >= 9): ?><a href="https://wa.me/<?= $h($wa) ?>" target="_blank" rel="noopener" class="text-reset"><?= $h($hp) ?> <i class="fab fa-whatsapp text-success"></i></a><?php else: ?><?= $h($hp) ?><?php endif; ?></b></div>
                  <div style="grid-column: span 2"><span>Alamat</span><b><?= $h($alamat) ?></b></div>
                </div>

                <?php if (trim((string) $pasien['patient_allergy']) !== ''): ?>
                  <div class="pd-alergi"><i class="fas fa-exclamation-triangle me-2"></i><b>Alergi:</b> <?= $h($pasien['patient_allergy']) ?></div>
                <?php endif; ?>

                <div class="d-flex flex-wrap gap-2 mt-3">
                  <a href="module/admin/patient" class="btn btn-sm btn-light"><i class="fas fa-arrow-left me-1"></i> Data Pasien</a>
                  <a href="module/admin/icareInternal" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-notes-medical me-1"></i> Review RME</a>
                </div>
              </div>
            </div>

            <!-- TAB -->
            <div class="card">
              <div class="card-body p-0">
                <ul class="nav pd-tabs px-3" role="tablist">
                  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-riwayat" data-tab="riwayat" type="button"><i class="fas fa-history me-1"></i>Riwayat Pengobatan <span class="badge bg-primary-subtle text-primary ms-1"><?= (int) $ringkas['total'] ?></span></button></li>
                  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-umum" data-tab="umum" type="button"><i class="fas fa-user me-1"></i>Data Umum</button></li>
                  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-kontak" data-tab="kontak" type="button"><i class="fas fa-map-marker-alt me-1"></i>Kontak &amp; Alamat</button></li>
                  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-darurat" data-tab="darurat" type="button"><i class="fas fa-phone-volume me-1"></i>Kontak Darurat</button></li>
                  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-dokumen" data-tab="dokumen" type="button"><i class="fas fa-folder-open me-1"></i>Dokumen</button></li>
                </ul>

                <div class="tab-content p-3 p-md-4">
                  <!-- RIWAYAT -->
                  <div class="tab-pane fade" id="tab-riwayat" role="tabpanel">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                      <div class="small text-muted"><i class="fas fa-info-circle me-1"></i>Kunjungan pasien di faskes ini yang tercatat di MEDISAFE (kunjungan batal tidak ditampilkan).</div>
                      <div class="small text-muted" id="rwInfo"></div>
                    </div>
                    <div class="rw-list" id="rwList"></div>
                    <div id="rwSkel">
                      <div class="rw-skel"></div>
                      <div class="rw-skel"></div>
                      <div class="rw-skel"></div>
                    </div>
                    <div class="text-center mt-2">
                      <button class="btn btn-outline-primary btn-sm d-none" id="rwMore"><i class="fas fa-chevron-down me-1"></i> Muat kunjungan sebelumnya</button>
                    </div>
                  </div>

                  <!-- DATA UMUM -->
                  <div class="tab-pane fade" id="tab-umum" role="tabpanel">
                    <form id="formIdentitas" class="pd-form" novalidate>
                      <div class="pd-sec">Identitas</div>
                      <div class="row g-3 mb-4">
                        <div class="col-md-6 col-xl-3">
                          <label class="form-label required">Nomor Rekam Medis</label>
                          <input type="text" class="form-control" name="nomor_rm" required>
                        </div>
                        <div class="col-md-6 col-xl-3">
                          <label class="form-label">NIK</label>
                          <input type="text" class="form-control" name="patient_nik" inputmode="numeric" maxlength="16">
                        </div>
                        <div class="col-md-6 col-xl-3">
                          <label class="form-label">No. Kartu Keluarga</label>
                          <input type="text" class="form-control" name="patient_kk" inputmode="numeric" maxlength="16">
                        </div>
                        <div class="col-md-6 col-xl-3">
                          <label class="form-label">No. BPJS</label>
                          <input type="text" class="form-control" name="patient_bpjs" inputmode="numeric" maxlength="13">
                        </div>
                      </div>

                      <div class="pd-sec">Data Diri</div>
                      <div class="row g-3 mb-4">
                        <div class="col-md-6">
                          <label class="form-label required">Nama Pasien</label>
                          <input type="text" class="form-control" name="patient_name" required>
                        </div>
                        <div class="col-md-6 col-xl-3">
                          <label class="form-label required">Tempat Lahir</label>
                          <input type="text" class="form-control" name="patient_place" required>
                        </div>
                        <div class="col-md-6 col-xl-3">
                          <label class="form-label required">Tanggal Lahir</label>
                          <input type="date" class="form-control" name="patient_datebirth" required>
                        </div>
                        <div class="col-md-4 col-xl-3">
                          <label class="form-label required">Jenis Kelamin</label>
                          <select name="patient_gender" class="form-select" required>
                            <option value="">PILIH</option>
                            <option value="Laki-laki">Laki-laki</option>
                            <option value="Perempuan">Perempuan</option>
                          </select>
                        </div>
                        <div class="col-md-4 col-xl-3">
                          <label class="form-label required">Agama</label>
                          <select name="patient_religion" class="form-select" required>
                            <option value="">PILIH</option>
                            <option value="Islam">Islam</option>
                            <option value="Kristen">Kristen</option>
                            <option value="Katolik">Katolik</option>
                            <option value="Hindu">Hindu</option>
                            <option value="Budha">Budha</option>
                          </select>
                        </div>
                        <div class="col-md-4 col-xl-3">
                          <label class="form-label">Status Perkawinan</label>
                          <select name="patient_marital_status" class="form-select">
                            <option value="">PILIH</option>
                            <option value="Kawin">Kawin</option>
                            <option value="Belum Kawin">Belum Kawin</option>
                            <option value="Janda/Duda">Janda/Duda</option>
                          </select>
                        </div>
                        <div class="col-md-4 col-xl-3">
                          <label class="form-label">Kewarganegaraan</label>
                          <input type="text" class="form-control" name="patient_nationality" placeholder="Indonesia">
                        </div>
                        <div class="col-md-4 col-xl-3">
                          <label class="form-label">Pendidikan Terakhir</label>
                          <select name="patient_education" class="form-select">
                            <option value="">PILIH</option>
                            <option value="SD">SD</option>
                            <option value="SMP">SMP</option>
                            <option value="SMA">SMA</option>
                            <option value="D3">D3</option>
                            <option value="S1">S1</option>
                            <option value="S2">S2</option>
                            <option value="S3">S3</option>
                          </select>
                        </div>
                        <div class="col-md-4 col-xl-3">
                          <label class="form-label">Pekerjaan</label>
                          <input type="text" class="form-control" name="patient_occupation">
                        </div>
                      </div>

                      <div class="pd-sec">Informasi Medis</div>
                      <div class="row g-3">
                        <div class="col-md-4 col-xl-2">
                          <label class="form-label">Golongan Darah</label>
                          <select name="patient_blood" class="form-select">
                            <option value="">PILIH</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="O">O</option>
                            <option value="AB">AB</option>
                          </select>
                        </div>
                        <div class="col-md-8 col-xl-5">
                          <label class="form-label">Alergi</label>
                          <input type="text" class="form-control" name="patient_allergy" placeholder="cth. Amoxicillin, seafood">
                        </div>
                        <div class="col-md-12 col-xl-5">
                          <label class="form-label">Disabilitas</label>
                          <input type="text" class="form-control" name="patient_disability">
                        </div>
                        <div class="col-12">
                          <label class="form-label">Catatan Pasien</label>
                          <textarea name="patient_notes" class="form-control" rows="3"></textarea>
                        </div>
                      </div>
                      <div class="pd-save text-end">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan Perubahan</button>
                      </div>
                    </form>
                  </div>

                  <!-- KONTAK & ALAMAT -->
                  <div class="tab-pane fade" id="tab-kontak" role="tabpanel">
                    <form id="formKontakAlamat" class="pd-form" novalidate>
                      <div class="pd-sec">Kontak</div>
                      <div class="row g-3 mb-4">
                        <div class="col-md-6">
                          <label class="form-label">No. HP</label>
                          <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-phone-alt"></i></span>
                            <input type="tel" class="form-control" name="patient_phone" placeholder="08xxxxxxxxxx">
                          </div>
                        </div>
                        <div class="col-md-6">
                          <label class="form-label">Email</label>
                          <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" name="patient_mail">
                          </div>
                        </div>
                      </div>
                      <div class="pd-sec">Alamat</div>
                      <div class="small text-muted mb-2" id="wilayahInfo"></div>
                      <div class="row g-3">
                        <div class="col-md-6 col-xl-3">
                          <label class="form-label">Provinsi</label>
                          <select id="provinsi" class="form-select">
                            <option value="">Memuat...</option>
                          </select>
                        </div>
                        <div class="col-md-6 col-xl-3">
                          <label class="form-label">Kabupaten/Kota</label>
                          <select id="kabupaten" class="form-select">
                            <option value="">PILIH</option>
                          </select>
                        </div>
                        <div class="col-md-6 col-xl-3">
                          <label class="form-label">Kecamatan</label>
                          <select id="kecamatan" class="form-select">
                            <option value="">PILIH</option>
                          </select>
                        </div>
                        <div class="col-md-6 col-xl-3">
                          <label class="form-label">Kelurahan</label>
                          <select id="kelurahan" class="form-select">
                            <option value="">PILIH</option>
                          </select>
                        </div>
                        <input type="hidden" name="patient_provinsi" id="provinsi_text">
                        <input type="hidden" name="patient_kabupaten" id="kabupaten_text">
                        <input type="hidden" name="patient_kecamatan" id="kecamatan_text">
                        <input type="hidden" name="patient_kelurahan" id="kelurahan_text">
                        <div class="col-12">
                          <label class="form-label">Alamat Lengkap</label>
                          <textarea class="form-control" name="patient_address" rows="2" placeholder="Jalan, nomor rumah, RT/RW"></textarea>
                        </div>
                      </div>
                      <div class="pd-save text-end">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan Perubahan</button>
                      </div>
                    </form>
                  </div>

                  <!-- KONTAK DARURAT -->
                  <div class="tab-pane fade" id="tab-darurat" role="tabpanel">
                    <form id="formEmergency" class="pd-form" novalidate style="max-width:720px">
                      <div class="pd-sec">Kontak yang dapat dihubungi saat darurat</div>
                      <div class="row g-3">
                        <div class="col-md-6">
                          <label class="form-label required">Nama Kontak Darurat</label>
                          <input type="text" class="form-control" name="patient_emergency_contact_name" required>
                        </div>
                        <div class="col-md-6">
                          <label class="form-label required">Hubungan</label>
                          <input type="text" class="form-control" name="patient_emergency_contact_relation" list="listHubungan" placeholder="cth. Suami, Istri, Anak" required>
                          <datalist id="listHubungan">
                            <option value="Suami">
                            <option value="Istri">
                            <option value="Ayah">
                            <option value="Ibu">
                            <option value="Anak">
                            <option value="Saudara">
                          </datalist>
                        </div>
                        <div class="col-md-6">
                          <label class="form-label required">No. HP Darurat</label>
                          <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-phone-alt"></i></span>
                            <input type="tel" class="form-control" name="patient_emergency_contact_phone" required>
                          </div>
                        </div>
                      </div>
                      <div class="pd-save text-end">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan Perubahan</button>
                      </div>
                    </form>
                  </div>

                  <!-- DOKUMEN -->
                  <div class="tab-pane fade" id="tab-dokumen" role="tabpanel">
                    <div class="small text-muted mb-3">
                      <i class="fas fa-info-circle me-1"></i>Format JPG/PNG, maksimal 2 MB, posisi <b>landscape</b>
                      (<a href="javascript:void(0)" id="lihatContoh">lihat contoh</a>), pastikan tulisan terbaca jelas.
                    </div>
                    <div class="row g-3" id="dokGrid"></div>
                  </div>
                </div>
              </div>
            </div>
          <?php endif; ?>

        </div>
      </div>
    </div>
  </div>

  <?php require 'library.php'; ?>

  <?php if ($pasien): ?>
    <div class="modal fade" id="modalSample" tabindex="-1">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Contoh Dokumen Landscape</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body text-center">
            <img src="assets/images/sample-landscape.jpg" class="img-fluid rounded border" alt="Contoh landscape" loading="lazy">
            <p class="mt-2 text-muted small">Dokumen dengan posisi landscape (lebar lebih besar dari tinggi).</p>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="filePreviewModal" tabindex="-1">
      <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="previewTitle">Preview Dokumen</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body text-center">
            <img id="previewImage" src="" class="img-fluid rounded" alt="">
          </div>
        </div>
      </div>
    </div>

    <script>
      (function() {
        const PASIEN = <?= json_encode($pasien, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?>;
        const ID = PASIEN.id_patient;
        const TAB_AWAL = <?= json_encode($tabAwal) ?>;
        const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({
          '&': '&amp;',
          '<': '&lt;',
          '>': '&gt;',
          '"': '&quot;',
          "'": '&#39;'
        } [c]));
        const toast = (icon, title) => Swal.fire({
          toast: true,
          position: 'top-end',
          icon,
          title,
          showConfirmButton: false,
          timer: 2200,
          timerProgressBar: true
        });

        // ---------- tab ----------
        const tabBtn = t => document.querySelector(`.pd-tabs [data-tab="${t}"]`);
        bootstrap.Tab.getOrCreateInstance(tabBtn(TAB_AWAL) || tabBtn('riwayat')).show();
        document.querySelectorAll('.pd-tabs [data-tab]').forEach(b => b.addEventListener('shown.bs.tab', () => {
          const t = b.dataset.tab;
          if (t === 'riwayat') muatRiwayat();
          if (t === 'kontak') muatWilayah();
          const u = new URL(location.href);
          u.searchParams.set('tabs', {
            umum: 1,
            kontak: 2,
            darurat: 3,
            dokumen: 4,
            riwayat: 5
          } [t]);
          history.replaceState(null, '', u);
        }));

        // ---------- isi form dari data yang sudah dimuat server ----------
        document.querySelectorAll('.pd-form [name]').forEach(el => {
          if (el.type === 'hidden' || !(el.name in PASIEN)) return;
          let v = PASIEN[el.name] ?? '';
          if (el.type === 'date') v = String(v).slice(0, 10);
          el.value = v;
          // nilai lama di luar pilihan tetap ditampilkan
          if (el.tagName === 'SELECT' && v !== '' && el.value !== String(v)) {
            el.add(new Option(v, v, true, true));
          }
        });
        ['provinsi', 'kabupaten', 'kecamatan', 'kelurahan'].forEach(k => {
          document.getElementById(k + '_text').value = PASIEN['patient_' + k] ?? '';
        });
        if (!PASIEN.patient_nationality) document.querySelector('[name="patient_nationality"]').value = 'Indonesia';

        // ---------- simpan ----------
        function pasangSimpan(id, sesudah) {
          const form = document.getElementById(id);
          form.addEventListener('submit', e => {
            e.preventDefault();
            if (!form.checkValidity()) {
              form.classList.add('was-validated');
              form.reportValidity();
              return;
            }
            const btn = form.querySelector('[type="submit"]');
            const asli = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
            const fd = new FormData(form);
            fd.append('id_patient', ID);
            fd.append('_method', 'PUT');
            fetch('controller/master/patientDetailsController.php', {
                method: 'POST',
                body: fd
              })
              .then(r => r.json())
              .then(r => {
                if (!r.success) return Swal.fire('Gagal', r.message || 'Gagal menyimpan.', 'error');
                form.classList.remove('was-validated');
                fd.forEach((v, k) => {
                  if (k in PASIEN) PASIEN[k] = v;
                });
                toast('success', r.message || 'Data pasien tersimpan');
                if (sesudah) sesudah();
              })
              .catch(() => Swal.fire('Gagal', 'Tidak dapat terhubung ke server.', 'error'))
              .finally(() => {
                btn.disabled = false;
                btn.innerHTML = asli;
              });
          });
        }
        pasangSimpan('formIdentitas', () => {
          document.getElementById('pdNama').textContent = PASIEN.patient_name;
          document.getElementById('pdRm').textContent = PASIEN.nomor_rm;
        });
        pasangSimpan('formKontakAlamat');
        pasangSimpan('formEmergency');

        // ---------- riwayat (bertahap) ----------
        let rwNext = 0,
          rwLoading = false,
          rwMulai = false;

        function muatRiwayat(lanjut = false) {
          if (rwLoading || (rwMulai && !lanjut) || rwNext === null) return;
          rwMulai = true;
          rwLoading = true;
          $('#rwSkel').show();
          $('#rwMore').prop('disabled', true);
          fetch(`controller/master/patientHistory.php?pt=${encodeURIComponent(ID)}&offset=${rwNext}&limit=10`)
            .then(r => r.json())
            .then(r => {
              if (r.status !== 'success') throw new Error(r.message);
              if (!r.total) {
                $('#rwList').html(`<div class="rw-empty py-5"><i class="fas fa-notes-medical"></i>Pasien belum memiliki riwayat kunjungan.</div>`);
              } else {
                $('#rwList').append(r.html);
              }
              rwNext = r.next;
              const tampil = $('#rwList .rw-item').length;
              $('#rwInfo').text(r.total ? `Menampilkan ${tampil} dari ${r.total} kunjungan` : '');
              $('#rwMore').toggleClass('d-none', r.next === null);
            })
            .catch(e => {
              $('#rwList').append(`<div class="alert alert-danger small">Gagal memuat riwayat. <a href="javascript:void(0)" id="rwRetry">Coba lagi</a></div>`);
              rwMulai = false;
            })
            .finally(() => {
              rwLoading = false;
              $('#rwSkel').hide();
              $('#rwMore').prop('disabled', false);
            });
        }
        $('#rwMore').on('click', () => muatRiwayat(true));
        $(document).on('click', '#rwRetry', function() {
          $(this).closest('.alert').remove();
          muatRiwayat();
        });
        if (TAB_AWAL === 'riwayat') muatRiwayat();

        // ---------- wilayah (dimuat saat tab Kontak dibuka) ----------
        const apiWilayah = 'controller/master/wilayah.php';
        let wilayahMulai = false;
        const ambilWilayah = (type, id = '') => fetch(`${apiWilayah}?type=${type}${id ? '&id=' + encodeURIComponent(id) : ''}`).then(r => r.json()).catch(() => []);
        const sama = (a, b) => String(a || '').trim().toUpperCase() === String(b || '').trim().toUpperCase();

        function isi(sel, data, pilihNama) {
          let pilih = '';
          const html = ['<option value="">PILIH</option>'].concat((Array.isArray(data) ? data : []).map(d => {
            if (pilihNama && sama(d.nama, pilihNama)) pilih = d.id;
            return `<option value="${esc(d.id)}">${esc(d.nama)}</option>`;
          }));
          $(sel).html(html.join('')).val(pilih);
          return pilih;
        }

        async function muatWilayah() {
          if (wilayahMulai) return;
          wilayahMulai = true;
          const lama = [PASIEN.patient_kelurahan, PASIEN.patient_kecamatan, PASIEN.patient_kabupaten, PASIEN.patient_provinsi].filter(Boolean).join(', ');
          $('#wilayahInfo').html(lama ? `<i class="fas fa-map-marker-alt me-1"></i>Tersimpan: <b>${esc(lama)}</b>` : '');
          const prov = isi('#provinsi', await ambilWilayah('provinsi'), PASIEN.patient_provinsi);
          if (!prov) return;
          const kab = isi('#kabupaten', await ambilWilayah('kabupaten', prov), PASIEN.patient_kabupaten);
          if (!kab) return;
          const kec = isi('#kecamatan', await ambilWilayah('kecamatan', kab), PASIEN.patient_kecamatan);
          if (!kec) return;
          isi('#kelurahan', await ambilWilayah('kelurahan', kec), PASIEN.patient_kelurahan);
        }
        if (TAB_AWAL === 'kontak') muatWilayah();

        const teks = sel => $(sel).val() ? $(sel).find('option:selected').text() : '';
        $('#provinsi').on('change', async function() {
          $('#provinsi_text').val(teks(this));
          $('#kabupaten_text, #kecamatan_text, #kelurahan_text').val('');
          $('#kecamatan, #kelurahan').html('<option value="">PILIH</option>');
          isi('#kabupaten', this.value ? await ambilWilayah('kabupaten', this.value) : []);
        });
        $('#kabupaten').on('change', async function() {
          $('#kabupaten_text').val(teks(this));
          $('#kecamatan_text, #kelurahan_text').val('');
          $('#kelurahan').html('<option value="">PILIH</option>');
          isi('#kecamatan', this.value ? await ambilWilayah('kecamatan', this.value) : []);
        });
        $('#kecamatan').on('change', async function() {
          $('#kecamatan_text').val(teks(this));
          $('#kelurahan_text').val('');
          isi('#kelurahan', this.value ? await ambilWilayah('kelurahan', this.value) : []);
        });
        $('#kelurahan').on('change', function() {
          $('#kelurahan_text').val(teks(this));
        });

        // ---------- dokumen ----------
        const DOK = [{
            key: 'ktp',
            kolom: 'patient_ktp_file',
            label: 'KTP',
            icon: 'fa-id-card'
          },
          {
            key: 'kk',
            kolom: 'patient_kk_file',
            label: 'Kartu Keluarga',
            icon: 'fa-users'
          },
          {
            key: 'bpjs',
            kolom: 'patient_bpjs_file',
            label: 'Kartu BPJS',
            icon: 'fa-address-card'
          },
        ];
        const urlDok = f => 'uploads/patient/' + encodeURIComponent(f);

        function renderDok() {
          $('#dokGrid').html(DOK.map(d => {
            const f = PASIEN[d.kolom];
            return `<div class="col-md-6 col-xl-4">
              <div class="dk-doc ${f ? 'ada' : ''}" data-key="${d.key}">
                <div class="d-flex justify-content-between align-items-center">
                  <span class="fw-semibold"><i class="fas ${d.icon} text-primary me-2"></i>${d.label}</span>
                  ${f ? '<span class="pd-badge bg-success-subtle text-success"><i class="fas fa-check"></i>Terunggah</span>' : '<span class="pd-badge bg-light text-muted">Belum ada</span>'}
                </div>
                <div class="dk-thumb" ${f ? `data-src="${esc(urlDok(f))}" data-label="${d.label}"` : ''}>
                  ${f ? `<img src="${esc(urlDok(f))}" alt="${d.label}" loading="lazy" onerror="this.replaceWith(Object.assign(document.createElement('i'),{className:'fas fa-file-image'}))">` : `<i class="fas ${d.icon}"></i>`}
                </div>
                <div class="d-flex gap-2 mt-auto">
                  <label class="btn btn-sm ${f ? 'btn-light' : 'btn-primary'} flex-grow-1 mb-0">
                    <i class="fas fa-upload me-1"></i>${f ? 'Ganti' : 'Unggah'}
                    <input type="file" class="d-none dk-input" data-key="${d.key}" accept="image/jpeg,image/png">
                  </label>
                  ${f ? `<button type="button" class="btn btn-sm btn-light text-danger dk-hapus" data-key="${d.key}" title="Hapus"><i class="fas fa-trash"></i></button>` : ''}
                </div>
              </div>
            </div>`;
          }).join(''));
        }
        renderDok();

        $('#lihatContoh').on('click', () => bootstrap.Modal.getOrCreateInstance('#modalSample').show());
        $(document).on('click', '.dk-thumb[data-src]', function() {
          $('#previewImage').attr('src', this.dataset.src);
          $('#previewTitle').text(this.dataset.label);
          bootstrap.Modal.getOrCreateInstance('#filePreviewModal').show();
        });

        $(document).on('change', '.dk-input', function() {
          const file = this.files[0];
          const key = this.dataset.key;
          const d = DOK.find(x => x.key === key);
          if (!file) return;
          if (!['image/jpeg', 'image/png'].includes(file.type)) return Swal.fire('Format tidak didukung', 'Gunakan file JPG atau PNG.', 'warning');
          if (file.size > 2 * 1024 * 1024) return Swal.fire('File terlalu besar', 'Ukuran maksimal 2 MB.', 'warning');
          const box = $(this).closest('.dk-doc');
          box.find('.dk-thumb').html('<div class="spinner-border text-primary"></div>');
          const fd = new FormData();
          fd.append('id_patient', ID);
          fd.append(key, file);
          $.ajax({
            url: 'controller/master/uploadPatient.php',
            type: 'POST',
            data: fd,
            contentType: false,
            processData: false,
            dataType: 'json'
          }).done(r => {
            if (r.status === 'success') {
              PASIEN[d.kolom] = r.files[key];
              toast('success', `${d.label} berhasil diunggah`);
            } else {
              Swal.fire('Gagal', r.message || 'Upload gagal.', 'error');
            }
          }).fail(() => Swal.fire('Gagal', 'Upload gagal, periksa koneksi.', 'error')).always(renderDok);
        });

        $(document).on('click', '.dk-hapus', function() {
          const key = this.dataset.key;
          const d = DOK.find(x => x.key === key);
          Swal.fire({
            title: `Hapus ${d.label}?`,
            text: 'File dokumen akan dihapus permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#fa896b'
          }).then(res => {
            if (!res.isConfirmed) return;
            $.ajax({
              url: 'controller/master/deletePatientFile.php',
              type: 'POST',
              dataType: 'json',
              data: {
                id_patient: ID,
                field: key
              }
            }).done(r => {
              if (r.status === 'success') {
                PASIEN[d.kolom] = null;
                renderDok();
                toast('success', `${d.label} dihapus`);
              } else {
                Swal.fire('Gagal', r.message || 'Gagal menghapus.', 'error');
              }
            }).fail(() => Swal.fire('Gagal', 'Tidak dapat terhubung ke server.', 'error'));
          });
        });
      })();
    </script>
  <?php endif; ?>
</body>

</html>
