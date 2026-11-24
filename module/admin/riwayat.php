<?php
/**
 * Riwayat pengobatan pasien (semua kunjungan di faskes ini): pemeriksaan, obat, lab, penanda operasi.
 * Performa: dulu tiap kunjungan menjalankan query sendiri (ICD-10 utama + tiap kode sekunder, resep 2x, rincian obat
 * per tiket) ke tabel resep/rincian yang tidak ber-index -> pasien dengan ±40 kunjungan butuh ±30 detik (habis batas
 * waktu PHP, halaman terpotong). Kini semua dimuat sekaligus: 1 query ICD, 1 query tiket resep, 1 query rincian obat.
 * Perbaikan lain: isi kunjungan (anamnesa, keluhan, dokter, diagnosa, obat) dicetak tanpa escape (XSS) dan kode
 * diagnosa dirangkai langsung ke SQL; kolom Harga obat tidak punya judul (header 3 kolom, isi 4) dan memakai harga
 * master saat ini, bukan harga saat resep; <style> diulang di setiap kunjungan; warning bila kode ICD tidak ditemukan.
 */
$title = 'Riwayat';
require '../../controller/view.php';
$EMBED = !empty($_GET['embed']);   // ditampilkan di dalam View RME Baru
if ($EMBED) $_GET['rme'] = '';     // tanpa menu RME
require_once __DIR__ . '/../../controller/visit/historyLabels.php';
require_once __DIR__ . '/../../controller/visit/operationMarkerHistory.php';
$no = $_GET['no'] ?? '';
$clinic = (int) $_SESSION['id_customer'];
if (!is_string($no) || $no === '') {
  http_response_code(400);
  exit('Nomor kunjungan wajib diisi.');
}
$e = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
$historyVisitStmt = $koneksi->prepare("SELECT id_patient FROM pasien_visit WHERE visit_ID = ? AND id_customer = ? LIMIT 1");
$historyVisitStmt->bind_param('si', $no, $clinic);
$historyVisitStmt->execute();
$historyPatient = $historyVisitStmt->get_result()->fetch_assoc();
if (!$historyPatient) {
  http_response_code(404);
  exit('Kunjungan tidak ditemukan pada klinik ini.');
}
$pt = (string) $historyPatient['id_patient'];
$historyStmt = $koneksi->prepare("SELECT pv.*, mp.nomor_rm FROM pasien_visit pv
  LEFT JOIN ms_patient mp ON mp.id_patient = pv.id_patient
  WHERE pv.id_patient = ? AND pv.id_customer = ?
  AND (pv.visit_status IS NULL OR pv.visit_status <> 99) ORDER BY visit_date DESC, visit_time DESC");
$historyStmt->bind_param('si', $pt, $clinic);
$historyStmt->execute();
$getquery = $historyStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$visitIds = array_values(array_unique(array_filter(array_column($getquery, 'visit_ID'), fn($v) => $v !== null && $v !== '')));
$tandaVisit = implode(',', array_fill(0, count($visitIds), '?'));

// ---- ICD-10: semua kode diagnosa semua kunjungan dalam 1 query ----
$kodeIcd = [];
foreach ($getquery as $r) {
  foreach (array_merge([(string) ($r['diagnosa'] ?? '')], explode(',', (string) ($r['diagnosa_sekunder'] ?? ''))) as $k) {
    $k = trim($k);
    if ($k !== '') $kodeIcd[$k] = true;
  }
}
$icdNama = [];
if ($kodeIcd) {
  $kode = array_keys($kodeIcd);
  $st = $koneksi->prepare("SELECT code, icd10 FROM icd_10 WHERE code IN (" . implode(',', array_fill(0, count($kode), '?')) . ")");
  $st->bind_param(str_repeat('s', count($kode)), ...$kode);
  $st->execute();
  foreach ($st->get_result()->fetch_all(MYSQLI_ASSOC) as $x) $icdNama[$x['code']] = $x['icd10'];
  $st->close();
}
$diag = fn($kode) => ($kode = trim((string) $kode)) === '' ? '' : $kode . (isset($icdNama[$kode]) ? ' - ' . $icdNama[$kode] : '');

// ---- resep: semua tiket + rincian obat semua kunjungan (2 query, bukan per kunjungan/per tiket) ----
$tiketPerVisit = [];
$obatPerTiket = [];
if ($visitIds) {
  $st = $koneksi->prepare("SELECT id_permintaan_farmasi, id_visit, created_at, tipe_obat, rck_nama, rck_jumlah, rck_satuan, rck_signa, status_permintaan
    FROM permintaan_pharmacy WHERE id_customer = ? AND id_visit IN ($tandaVisit) ORDER BY id_permintaan_farmasi ASC");
  $st->bind_param('s' . str_repeat('s', count($visitIds)), ...array_merge([(string) $clinic], $visitIds));
  $st->execute();
  $tiket = $st->get_result()->fetch_all(MYSQLI_ASSOC);
  $st->close();
  foreach ($tiket as $t) $tiketPerVisit[$t['id_visit']][] = $t;
  if ($tiket) {
    // id dari DB (int) -> aman dirangkai; query biasa menghemat 1 round-trip ke DB remote
    $idTiket = implode(',', array_map(fn($t) => (int) $t['id_permintaan_farmasi'], $tiket));
    $res = $koneksi->query("SELECT d.id_permintaan_farmasi, d.qty, d.qty_racik, d.signa, d.harga, d.catatan,
        COALESCE(NULLIF(p.pharmacy_name_generic, ''), d.item_name) AS nama, p.pharmacy_unit AS satuan
      FROM permintaan_pharmacy_details d LEFT JOIN ms_pharmacy p ON p.id_pharmacy = d.id_pharmacy
      WHERE d.id_permintaan_farmasi IN ($idTiket) ORDER BY d.id_pharmacy_details");
    while ($d = $res->fetch_assoc()) $obatPerTiket[(int) $d['id_permintaan_farmasi']][] = $d;
    $res->free();
  }
}

// ---- hasil laboratorium per kunjungan (1 query permintaan + 1 query hasil) ----
$labPerVisit = [];
if ($visitIds) {
  $labStmt = $koneksi->prepare("SELECT id_inspection, id_visit, inspection_name, inspection_date, inspection_source, inspection_note, created_at
    FROM visit_inspection WHERE id_customer = ? AND id_visit IN ($tandaVisit) ORDER BY created_at DESC, id_inspection DESC");
  $labStmt->bind_param('i' . str_repeat('s', count($visitIds)), $clinic, ...$visitIds);
  $labStmt->execute();
  $labInsp = $labStmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $labStmt->close();
  $labHasil = [];
  if ($labInsp) {
    $idInsp = array_map(fn($x) => (int) $x['id_inspection'], $labInsp);
    $labStmt = $koneksi->prepare("SELECT lr.id_inspection, lr.hasil, lr.keterangan, li.assemen, li.satuan, li.minimum, li.maksimum, li.catatan
      FROM laboratorium_result lr LEFT JOIN laboratorium_item li ON li.id = lr.id_item
      WHERE lr.id_inspection IN (" . implode(',', array_fill(0, count($idInsp), '?')) . ") AND TRIM(COALESCE(lr.hasil, '')) <> ''
      ORDER BY lr.id_inspection, li.id");
    $labStmt->bind_param(str_repeat('i', count($idInsp)), ...$idInsp);
    $labStmt->execute();
    foreach ($labStmt->get_result()->fetch_all(MYSQLI_ASSOC) as $h) $labHasil[(int) $h['id_inspection']][] = $h;
    $labStmt->close();
  }
  foreach ($labInsp as $x) {
    $x['hasil'] = $labHasil[(int) $x['id_inspection']] ?? [];
    $labPerVisit[$x['id_visit']][] = $x;
  }
}
/** Tandai hasil di luar rentang normal (hanya bila hasil & batas berupa angka biasa). */
function labFlagRiwayat(array $h): string
{
  // "4.000" / "150.000" = format ribuan Indonesia (titik + tepat 3 digit); "4.10" / "27.0" / "36,5" = desimal
  $angka = function ($v) {
    $v = trim((string) $v);
    if (preg_match('/^\d{1,3}(\.\d{3})+$/', $v)) return (float) str_replace('.', '', $v);
    return preg_match('/^-?\d+(?:[.,]\d+)?$/', $v) ? (float) str_replace(',', '.', $v) : null;
  };
  $v = $angka($h['hasil']);
  $mn = $angka($h['minimum']);
  $mx = $angka($h['maksimum']);
  if ($v === null || $mn === null || $mx === null || $mx <= 0 || $mx <= $mn) return '';
  return $v < $mn ? 'L' : ($v > $mx ? 'H' : '');
}
$operationHistory = [];
$operationHistoryError = '';
try {
  $operationHistory = operationMarkerPatientHistory($koneksi, $pt, $clinic);
} catch (mysqli_sql_exception $error) {
  error_log('Operation marker history: ' . $error->getMessage());
  $operationHistoryError = 'Gagal membaca riwayat penanda operasi. Hubungi administrator untuk memeriksa database.';
}
$tglId = fn($d, $f = 'd M Y') => $d && strtotime((string) $d) ? date($f, strtotime((string) $d)) : '-';
$isi = fn($v) => trim((string) ($v ?? '')) !== '' ? nl2br($e(trim((string) $v))) : '-';
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; require __DIR__ . '/embed_mode.php'; ?>
  <style>
    .vital-box { border: 1px solid #e5e7eb; border-radius: 8px; padding: 8px; background: #f9fafb; }
    .vital-value { font-size: 16px; font-weight: 600; color: #111827; }
    .vital-label { font-size: 11px; color: #1f2937; }

    .lab-card { border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden; background: #fff; }
    .lab-header { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 6px; padding: 8px 12px; background: #f8fafc; border-bottom: 1px solid #e5e7eb; font-size: 14px; color: #111827; }
    .lab-date { font-size: 12px; color: #1f2937; }
    .lab-card .lab-tabel { margin: 0; }
    .lab-card .lab-tabel th { font-weight: 600; color: #1f2937; }
    .lab-abnormal { color: #b91c1c; font-weight: 700; }
    .lab-flag { display: inline-block; font-size: 11px; font-weight: 700; color: #fff; background: #b91c1c; border-radius: 4px; padding: 0 5px; margin-left: 4px; }
    .lab-catatan { padding: 8px 12px; font-size: 13px; color: #111827; background: #fffbeb; border-top: 1px solid #fde68a; }

    /* teks abu-abu (text-muted) di isi riwayat sulit dibaca -> gelap */
    .body-wrapper-inner .text-muted { color: #1f2937 !important; }

    .rw-scroll { max-height: 72vh; overflow-y: auto; padding-right: 5px; }
    .rw-head { display: flex; flex-wrap: wrap; align-items: center; gap: 6px 12px; width: 100%; padding-right: 8px; }
    .rw-head .tgl { font-weight: 700; color: #0f172a; min-width: 150px; }
    .rw-head .meta { font-size: .82rem; color: #334155; }
    .rw-head .dx { font-size: .8rem; color: #1e3a8a; background: #eff6ff; border-radius: 6px; padding: 1px 8px; max-width: 360px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .rw-tag { font-size: .7rem; font-weight: 700; border-radius: 6px; padding: 2px 7px; }
    .rw-kini { background: #0f172a; color: #fff; }
    .rw-ranap { background: #fef3c7; color: #92400e; }
    .rw-resume { display: flex; justify-content: flex-end; margin: -4px 0 10px; }
    .rw-label { font-size: .74rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: .02em; }
    .rw-racik { background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 6px 10px; font-size: .8rem; color: #78350f; }
    .rw-tiket td { background: #f8fafc; }
  </style>
</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <?php if (!$EMBED) require 'sidebar.php'; ?>
    <div class="body-wrapper">
      <?php if (!$EMBED) require 'navbar.php'; ?>
      <div class="body-wrapper-inner">
        <div class="container-fluid">
          <?php
          $rme = $_GET['rme'] ?? '';
          if ($rme == 'a') include 'menu_rme.php';
          elseif ($rme == 'b') include 'menu_rmeb.php';
          elseif ($rme == 'c') include 'menu_rme_inap.php';
          ?>
          <div class="row">
            <div class="col-12">
              <?php if (!$EMBED) require 'card-pasien.php'; ?>
            </div>
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-3 p-md-4">
                  <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                    <h5 class="mb-0 fw-bold"><i class="ti ti-history me-1"></i>Riwayat Pengobatan</h5>
                    <span class="badge bg-dark"><?= count($getquery) ?> kunjungan</span>
                    <small class="text-muted ms-md-2">Data kunjungan pasien yang tercatat di <b>MEDISAFE</b> pada faskes ini, terbaru di atas.</small>
                  </div>

                  <div class="rw-scroll">
                    <?php if ($operationHistoryError !== ''): ?>
                      <div class="alert alert-danger" role="alert"><?= $e($operationHistoryError) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($getquery)): ?>
                      <div class="accordion" id="accordionExample">
                        <?php foreach ($getquery as $index => $row):
                          $vid = (string) $row['visit_ID'];
                          $collapseId = 'collapse' . $index;
                          $ranap = (string) ($row['status_rawatinap'] ?? '') === '1';
                          $dxUtama = !empty($row['kdDiag1']) ? $row['kdDiag1'] . ' - ' . $row['nmDiag1'] : $diag($row['diagnosa'] ?? '');
                          $tiketKunjungan = $tiketPerVisit[$vid] ?? [];
                          $labKunjungan = $labPerVisit[$vid] ?? [];
                        ?>
                          <div class="accordion-item">
                            <h2 class="accordion-header" id="heading<?= $index ?>">
                              <button class="accordion-button <?= $index != 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse"
                                data-bs-target="#<?= $collapseId ?>" aria-expanded="<?= $index == 0 ? 'true' : 'false' ?>" aria-controls="<?= $collapseId ?>">
                                <span class="rw-head">
                                  <span class="tgl"><?= $e($tglId($row['visit_date'])) ?><?= !empty($row['visit_time']) ? ' · ' . $e(substr((string) $row['visit_time'], 0, 5)) : '' ?></span>
                                  <span class="meta"><?= $e($row['id_poli'] ?: '-') ?> · <?= $e($row['id_doctor'] ?: '-') ?></span>
                                  <?php if ($dxUtama !== ''): ?><span class="dx" title="<?= $e($dxUtama) ?>"><?= $e($dxUtama) ?></span><?php endif ?>
                                  <?php if ($vid === $no): ?><span class="rw-tag rw-kini">Kunjungan ini</span><?php endif ?>
                                  <?php if ($ranap): ?><span class="rw-tag rw-ranap">Rawat Inap</span><?php endif ?>
                                </span>
                              </button>
                            </h2>

                            <div id="<?= $collapseId ?>" class="accordion-collapse collapse <?= $index == 0 ? 'show' : '' ?>" data-bs-parent="#accordionExample">
                              <div class="accordion-body p-3">
                                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                                  <div>
                                    <div class="fw-semibold">Detail Kunjungan</div>
                                    <small class="text-muted"><?= $e($tglId($row['visit_date'])) ?> • <?= $e($vid) ?></small>
                                  </div>
                                  <div class="d-flex flex-wrap gap-2 align-items-center">
                                    <span class="badge bg-light text-dark border"><?= $e(historyVisitType($row)) ?> | <?= $e($row['id_poli']) ?></span>
                                    <a href="module/admin/print/<?= $ranap ? 'formulir_resume_v2' : 'formulir_resume_poli' ?>?no=<?= $e(urlencode($vid)) ?>&rm=<?= $e(urlencode((string) ($row['nomor_rm'] ?? ''))) ?>&download=1&ranap=<?= $e(urlencode((string) ($row['status_rawatinap'] ?? ''))) ?>"
                                      target="_blank" class="btn btn-sm btn-danger">
                                      <i class="ti ti-file-type-pdf me-1"></i>Resume <?= $ranap ? 'Rawat Inap' : 'Poliklinik' ?>
                                    </a>
                                  </div>
                                </div>

                                <div class="row g-2 mb-3">
                                  <div class="col-md-6">
                                    <div class="p-2 border rounded small">
                                      <div class="text-muted">Dokter</div>
                                      <div class="fw-semibold"><?= $e($row['id_doctor'] ?: '-') ?></div>
                                    </div>
                                  </div>
                                  <div class="col-md-6">
                                    <div class="p-2 border rounded small">
                                      <div class="text-muted">Poli</div>
                                      <div class="fw-semibold"><?= $e($row['id_poli'] ?: '-') ?></div>
                                    </div>
                                  </div>
                                </div>

                                <ul class="nav nav-tabs mb-3" role="tablist">
                                  <li class="nav-item">
                                    <button type="button" class="nav-link active" data-bs-toggle="tab" data-bs-target="#pemeriksaan<?= $index ?>">🩺 Pemeriksaan</button>
                                  </li>
                                  <li class="nav-item">
                                    <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#obat<?= $index ?>">💊 Obat<?php if ($tiketKunjungan): ?> <span class="badge rounded-pill bg-primary"><?= count($tiketKunjungan) ?></span><?php endif ?></button>
                                  </li>
                                  <li class="nav-item">
                                    <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#lab<?= $index ?>">🧪 Lab<?php if ($labKunjungan): ?> <span class="badge rounded-pill bg-primary"><?= count($labKunjungan) ?></span><?php endif ?></button>
                                  </li>
                                </ul>

                                <div class="tab-content">
                                  <!-- 🩺 PEMERIKSAAN -->
                                  <div class="tab-pane fade show active" id="pemeriksaan<?= $index ?>">
                                    <div class="border rounded p-3 mb-3">
                                      <div class="fw-semibold mb-2">🩺 Vital Sign</div>
                                      <div class="row g-2 text-center small">
                                        <?php foreach ([
                                          ['tekanan_darah', 'Tekanan Darah'], ['nadi', 'Nadi'], ['suhu', 'Suhu (°C)'], ['respirasi', 'Respirasi'],
                                          ['saturasi', 'SpO₂'], ['berat_badan', 'Berat (kg)'], ['tinggi_badan', 'Tinggi (cm)'], ['bmi', 'BMI'],
                                        ] as [$k, $label]):
                                          $nilai = trim((string) ($row[$k] ?? ''));
                                          if ($k === 'bmi' && !empty($row['bmi_keterangan'])) $label .= ' (' . $row['bmi_keterangan'] . ')';
                                        ?>
                                          <div class="col-6 col-md-3">
                                            <div class="vital-box">
                                              <div class="vital-value"><?= $e($nilai !== '' ? $nilai : '-') ?></div>
                                              <div class="vital-label"><?= $e($label) ?></div>
                                            </div>
                                          </div>
                                        <?php endforeach ?>
                                      </div>
                                    </div>

                                    <div class="border rounded p-3 small">
                                      <div class="mb-2">
                                        <strong>Keluhan Utama</strong>
                                        <div class="text-muted"><?= $isi($row['keluhan_penyerta'] ?? '') ?></div>
                                      </div>
                                      <div class="mb-2">
                                        <strong>Anamnesa</strong>
                                        <div class="text-muted"><?= $isi($row['anamnesa'] ?? '') ?></div>
                                      </div>
                                      <div class="mb-2">
                                        <strong>Diagnosa Utama</strong>
                                        <div class="text-muted"><?= $e($dxUtama !== '' ? $dxUtama : '-') ?></div>
                                      </div>
                                      <div class="mb-2">
                                        <strong>Diagnosa Sekunder</strong>
                                        <div class="text-muted">
                                          <?php
                                          $sekunder = [];
                                          if (!empty($row['kdDiag2'])) $sekunder[] = $row['kdDiag2'] . ' - ' . $row['nmDiag2'];
                                          elseif (!empty($row['diagnosa_sekunder'])) foreach (explode(',', (string) $row['diagnosa_sekunder']) as $k) if (trim($k) !== '') $sekunder[] = $diag($k);
                                          if (!empty($row['kdDiag3'])) $sekunder[] = $row['kdDiag3'] . (!empty($row['nmDiag3']) ? ' - ' . $row['nmDiag3'] : '');
                                          echo $sekunder ? implode('<br>', array_map($e, $sekunder)) : '-';
                                          ?>
                                        </div>
                                      </div>
                                      <div class="mb-2">
                                        <strong>Tindakan / Terapi</strong>
                                        <div class="text-muted">
                                          <?php
                                          if (trim((string) ($row['tindakan'] ?? '')) !== '') {
                                            echo $isi($row['tindakan']);
                                          } else {
                                            $obatList = [];
                                            foreach ($tiketKunjungan as $t) foreach ($obatPerTiket[(int) $t['id_permintaan_farmasi']] ?? [] as $d) {
                                              $obatList[] = ($d['nama'] ?: 'Nama obat tidak ada') . ' | Qty: ' . $d['qty'] . (trim((string) $d['signa']) !== '' ? ' | Signa: ' . $d['signa'] : '');
                                            }
                                            echo $obatList ? implode('<br>', array_map($e, array_unique($obatList))) : '-';
                                          }
                                          ?>
                                        </div>
                                      </div>
                                      <div>
                                        <strong>Status Pulang</strong>
                                        <div><span class="badge bg-success"><?= $e(historyDischargeStatus($row)) ?></span></div>
                                      </div>
                                    </div>
                                  </div>

                                  <!-- 💊 OBAT -->
                                  <div class="tab-pane fade" id="obat<?= $index ?>">
                                    <div class="table-responsive small">
                                      <table class="table table-sm mb-0">
                                        <thead class="bg-light">
                                          <tr>
                                            <th>Nama Obat</th>
                                            <th>Signa</th>
                                            <th class="text-end">Jumlah</th>
                                            <th class="text-end">Harga</th>
                                          </tr>
                                        </thead>
                                        <tbody>
                                          <?php if (!$tiketKunjungan): ?>
                                            <tr><td colspan="4" class="text-center text-muted">Tidak ada data obat</td></tr>
                                          <?php endif ?>
                                          <?php foreach ($tiketKunjungan as $t):
                                            $rincian = $obatPerTiket[(int) $t['id_permintaan_farmasi']] ?? [];
                                            $racik = strtolower(trim((string) $t['tipe_obat'])) === 'racikan';
                                          ?>
                                            <tr class="rw-tiket">
                                              <td colspan="4">
                                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                                  <div>
                                                    <strong>🧾 Resep #<?= (int) $t['id_permintaan_farmasi'] ?></strong>
                                                    <span class="small text-muted ms-1"><?= $e($tglId($t['created_at'], 'd M Y H:i')) ?></span>
                                                  </div>
                                                  <span class="badge bg-primary"><?= $e($t['tipe_obat'] ?: '-') ?></span>
                                                </div>
                                                <?php if ($racik): ?>
                                                  <div class="rw-racik mt-1">🧪 <b><?= $e($t['rck_nama'] ?: 'Racikan') ?></b>
                                                    · Jumlah: <?= $e($t['rck_jumlah'] ?: '-') ?> <?= $e($t['rck_satuan'] ?: '') ?>
                                                    · Signa: <?= $e($t['rck_signa'] ?: '-') ?></div>
                                                <?php endif ?>
                                              </td>
                                            </tr>
                                            <?php if (!$rincian): ?>
                                              <tr><td colspan="4" class="text-center text-muted">Tidak ada detail obat</td></tr>
                                            <?php endif ?>
                                            <?php foreach ($rincian as $d): ?>
                                              <tr>
                                                <td><?= $e($d['nama'] ?: '-') ?></td>
                                                <td><?= $e(trim((string) $d['signa']) !== '' ? $d['signa'] : '-') ?></td>
                                                <td class="text-end text-nowrap"><?= $e($racik && $d['qty_racik'] !== null ? (float) $d['qty_racik'] : $d['qty']) ?> <?= $e($d['satuan'] ?: '') ?></td>
                                                <td class="text-end text-nowrap">Rp <?= number_format((float) $d['harga'] * (float) $d['qty'], 0, ',', '.') ?></td>
                                              </tr>
                                            <?php endforeach ?>
                                          <?php endforeach ?>
                                        </tbody>
                                      </table>
                                    </div>
                                  </div>

                                  <!-- 🧪 LAB -->
                                  <div class="tab-pane fade" id="lab<?= $index ?>">
                                    <?php if ($labKunjungan) {
                                      foreach ($labKunjungan as $l) {
                                        $catatanLab = trim((string) $l['inspection_note']);
                                    ?>
                                        <div class="lab-card mb-3">
                                          <div class="lab-header">
                                            <div>
                                              <span class="fw-semibold">🧪 <?= $e($l['inspection_name']) ?></span>
                                              <?php if ($l['hasil']): ?>
                                                <span class="badge bg-success-subtle text-success ms-1">Ada hasil</span>
                                              <?php elseif ($catatanLab === ''): ?>
                                                <span class="badge bg-danger-subtle text-danger ms-1">Belum ada hasil</span>
                                              <?php endif ?>
                                            </div>
                                            <span class="lab-date">
                                              <?= $e($tglId($l['inspection_date'])) ?>
                                              <?= !empty($l['inspection_source']) ? ' · ' . $e($l['inspection_source']) : '' ?>
                                            </span>
                                          </div>
                                          <?php if ($l['hasil']) { ?>
                                            <div class="table-responsive">
                                              <table class="table table-sm table-bordered mb-0 small lab-tabel">
                                                <thead class="table-light">
                                                  <tr>
                                                    <th>Pemeriksaan</th>
                                                    <th>Hasil</th>
                                                    <th>Satuan</th>
                                                    <th>Nilai normal</th>
                                                    <th>Keterangan</th>
                                                  </tr>
                                                </thead>
                                                <tbody>
                                                  <?php foreach ($l['hasil'] as $h) {
                                                    $flag = labFlagRiwayat($h);
                                                    $mn = trim((string) $h['minimum']);
                                                    $mx = trim((string) $h['maksimum']);
                                                    if ($mx === '0') $mx = '';
                                                    $normal = $mn !== '' && $mx !== '' ? $mn . ' – ' . $mx : ($mn !== '' ? $mn : ($mx !== '' ? $mx : '-'));
                                                    $ket = trim((string) ($h['keterangan'] ?: $h['catatan']));
                                                  ?>
                                                    <tr>
                                                      <td><?= $e($h['assemen'] ?: '-') ?></td>
                                                      <td class="<?= $flag ? 'lab-abnormal' : 'fw-semibold' ?>"><?= $e($h['hasil']) ?><?= $flag ? ' <span class="lab-flag" title="' . ($flag === 'H' ? 'Di atas nilai normal' : 'Di bawah nilai normal') . '">' . $flag . '</span>' : '' ?></td>
                                                      <td><?= $e(trim((string) $h['satuan']) ?: '-') ?></td>
                                                      <td><?= $e($normal) ?></td>
                                                      <td><?= $e($ket !== '' ? $ket : '-') ?></td>
                                                    </tr>
                                                  <?php } ?>
                                                </tbody>
                                              </table>
                                            </div>
                                          <?php } ?>
                                          <?php if ($catatanLab !== '') { ?>
                                            <div class="lab-catatan"><strong>Catatan / hasil:</strong> <?= nl2br($e($catatanLab)) ?></div>
                                          <?php } elseif (!$l['hasil']) { ?>
                                            <div class="text-muted small p-2">Hasil belum diinput.</div>
                                          <?php } ?>
                                        </div>
                                      <?php }
                                    } else { ?>
                                      <div class="text-center text-muted py-3">🧪 Tidak ada pemeriksaan laboratorium pada kunjungan ini</div>
                                    <?php } ?>
                                  </div>
                                </div>
                                <?= operationMarkerHistoryHtml($operationHistory[$vid] ?? [], (string) $row['nomor_rm']) ?>
                              </div>
                            </div>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    <?php else: ?>
                      <div class="alert alert-danger d-flex align-items-center gap-2" role="alert">
                        <i class="ti ti-info-circle"></i>
                        <div>
                          <strong>Belum ada riwayat kunjungan</strong><br>
                          <small>Pasien ini belum memiliki data kunjungan yang tercatat di sistem.</small>
                        </div>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php require 'library.php'; ?>
  <script>
    document.querySelectorAll('.operation-history-picture img').forEach(image => {
      const showError = () => {
        image.classList.add('d-none');
        image.parentElement.querySelector('.operation-history-image-error').classList.remove('d-none');
      };
      image.addEventListener('error', showError);
      if (image.complete && !image.naturalWidth) showError();
    });
  </script>
</body>

</html>
