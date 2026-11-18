<?php
// Riwayat pengobatan pasien (dipakai module/admin/patient_details), dimuat bertahap.
//   GET ?pt=ID_PATIENT&offset=0&limit=10
//   -> { status, html, total, next }  (next = offset berikutnya, null bila habis)
// Hanya pasien milik faskes sesi; kunjungan batal (visit_status 99) tidak ditampilkan.
include '../../database/connect.php';
require_once __DIR__ . '/../visit/historyLabels.php';
mysqli_report(MYSQLI_REPORT_OFF);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function jawab(array $data, int $code = 200): void
{
   http_response_code($code);
   echo json_encode($data);
   exit;
}

function semua(mysqli $k, string $sql, string $types = '', array $params = []): array
{
   $stmt = $k->prepare($sql);
   if (!$stmt) {
      error_log('[patientHistory] ' . $k->error);
      jawab(['status' => 'error', 'message' => 'Gagal memuat riwayat.'], 500);
   }
   if ($types !== '') $stmt->bind_param($types, ...$params);
   $stmt->execute();
   $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
   $stmt->close();
   return $rows;
}

function e($v, string $kosong = '-'): string
{
   $v = trim((string) ($v ?? ''));
   return $v === '' ? $kosong : htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

function tgl(?string $t, string $fmt = 'd M Y'): string
{
   $t = trim((string) $t);
   if ($t === '' || !($ts = strtotime($t))) return '-';
   static $bln = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
   return strtr(date($fmt, $ts), array_combine(['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'], $bln));
}

function namaObat(array $d): string
{
   foreach (['pharmacy_name_generic', 'pharmacy_name_trade', 'item_name'] as $k) {
      if (trim((string) ($d[$k] ?? '')) !== '') return trim($d[$k]);
   }
   return 'Nama obat tidak ada';
}

function statusResep($s): array
{
   return [
      0 => ['Belum dikirim', 'bg-secondary-subtle text-secondary'],
      1 => ['Sudah dikirim', 'bg-info-subtle text-info'],
      2 => ['Persiapan', 'bg-warning-subtle text-warning'],
      3 => ['Selesai', 'bg-success-subtle text-success'],
   ][(int) $s] ?? ['-', 'bg-light text-dark'];
}

if (empty($_SESSION['id_customer'])) jawab(['status' => 'error', 'message' => 'Session tidak valid / expired'], 401);
$idc = (int) $_SESSION['id_customer'];
$pt = (int) ($_GET['pt'] ?? 0);
$offset = max(0, (int) ($_GET['offset'] ?? 0));
$limit = min(max((int) ($_GET['limit'] ?? 10), 1), 30);

$pasien = semua($koneksi, "SELECT id_patient, nomor_rm FROM ms_patient WHERE id_patient = ? AND id_customer = ?", 'ii', [$pt, $idc]);
if (!$pasien) jawab(['status' => 'error', 'message' => 'Pasien tidak ditemukan.'], 404);
$nomorRm = (string) $pasien[0]['nomor_rm'];
$ptStr = (string) $pt;

$total = (int) semua(
   $koneksi,
   "SELECT COUNT(*) n FROM pasien_visit WHERE id_patient = ? AND id_customer = ? AND (visit_status IS NULL OR visit_status <> 99)",
   'si',
   [$ptStr, $idc]
)[0]['n'];

$visits = $total === 0 ? [] : semua(
   $koneksi,
   "SELECT visit_ID, visit_date, visit_time, source_hub, status_rawatinap, status_pulang, id_poli, id_doctor,
           tekanan_darah, nadi, suhu, respirasi, saturasi, berat_badan, tinggi_badan,
           keluhan_penyerta, anamnesa, tindakan, diagnosa, diagnosa_sekunder,
           kdDiag1, nmDiag1, kdDiag2, nmDiag2, kdDiag3, nmDiag3, id_provider
    FROM pasien_visit
    WHERE id_patient = ? AND id_customer = ? AND (visit_status IS NULL OR visit_status <> 99)
    ORDER BY visit_date DESC, visit_time DESC
    LIMIT ?, ?",
   'siii',
   [$ptStr, $idc, $offset, $limit]
);

// ---------- data pendukung untuk kunjungan di halaman ini ----------
$resepPerVisit = $detailPerResep = $labPerVisit = $hasilPerLab = $icd = [];
$visitIds = array_values(array_unique(array_filter(array_column($visits, 'visit_ID'))));
if ($visitIds) {
   $ph = implode(',', array_fill(0, count($visitIds), '?'));
   $ts = str_repeat('s', count($visitIds));

   // resep lalu detailnya (2 query; tabel resep & detail belum ber-index id_visit / id_permintaan_farmasi,
   // JOIN langsung justru memaksa hash join seluruh tabel detail)
   $resepIds = [];
   foreach (semua(
      $koneksi,
      "SELECT id_permintaan_farmasi, id_visit, tipe_obat, status_permintaan, created_at, rck_jumlah, rck_satuan, rck_signa
       FROM permintaan_pharmacy WHERE id_visit IN ($ph) AND id_customer = ? ORDER BY id_permintaan_farmasi ASC",
      $ts . 'i',
      array_merge($visitIds, [$idc])
   ) as $r) {
      $resepPerVisit[$r['id_visit']][(int) $r['id_permintaan_farmasi']] = $r;
      $resepIds[] = (int) $r['id_permintaan_farmasi'];
   }
   if ($resepIds) {
      $phR = implode(',', array_fill(0, count($resepIds), '?'));
      foreach (semua(
         $koneksi,
         "SELECT d.id_permintaan_farmasi, d.qty, d.signa, d.harga, d.item_name,
                 p.pharmacy_name_generic, p.pharmacy_name_trade, p.pharmacy_price_item
          FROM permintaan_pharmacy_details d LEFT JOIN ms_pharmacy p ON p.id_pharmacy = d.id_pharmacy
          WHERE d.id_permintaan_farmasi IN ($phR) ORDER BY d.id_pharmacy_details ASC",
         str_repeat('i', count($resepIds)),
         $resepIds
      ) as $d) {
         $detailPerResep[(int) $d['id_permintaan_farmasi']][] = $d;
      }
   }

   // lab + hasil
   $labIds = [];
   foreach (semua($koneksi, "SELECT * FROM visit_inspection WHERE id_visit IN ($ph) ORDER BY inspection_date DESC, id_inspection DESC", $ts, $visitIds) as $l) {
      $labPerVisit[$l['id_visit']][] = $l;
      $labIds[] = (int) $l['id_inspection'];
   }
   if ($labIds) {
      $ph2 = implode(',', array_fill(0, count($labIds), '?'));
      foreach (semua(
         $koneksi,
         "SELECT lr.*, li.assemen, li.satuan, li.minimum, li.maksimum
          FROM laboratorium_result lr LEFT JOIN laboratorium_item li ON li.id = lr.id_item
          WHERE lr.id_inspection IN ($ph2) ORDER BY li.urutan ASC",
         str_repeat('i', count($labIds)),
         $labIds
      ) as $h) {
         $hasilPerLab[(int) $h['id_inspection']][] = $h;
      }
   }

   // ICD-10 dalam 1 query
   $kode = [];
   foreach ($visits as $v) {
      foreach (array_merge([$v['diagnosa'] ?? ''], explode(',', (string) ($v['diagnosa_sekunder'] ?? ''))) as $k) {
         if (trim($k) !== '') $kode[trim($k)] = true;
      }
   }
   if ($kode) {
      $kode = array_keys($kode);
      $ph3 = implode(',', array_fill(0, count($kode), '?'));
      foreach (semua($koneksi, "SELECT code, icd10 FROM icd_10 WHERE code IN ($ph3)", str_repeat('s', count($kode)), $kode) as $r) {
         $icd[strtoupper($r['code'])] ??= $r['code'] . ' - ' . $r['icd10'];
      }
   }
}
$namaIcd = fn($k) => $icd[strtoupper(trim((string) $k))] ?? trim((string) $k);

// ---------- render ----------
ob_start();
foreach ($visits as $i => $v):
   $vid = (string) $v['visit_ID'];
   $uid = 'v' . ($offset + $i);
   $jenis = historyVisitType($v);
   $ranap = (string) ($v['status_rawatinap'] ?? '') === '1';
   $warna = $ranap ? 'danger' : (in_array($jenis, ['UGD'], true) ? 'warning' : 'primary');

   $diagUtama = trim((string) ($v['kdDiag1'] ?? '')) !== ''
      ? trim($v['kdDiag1'] . ' - ' . ($v['nmDiag1'] ?? ''), ' -')
      : (trim((string) ($v['diagnosa'] ?? '')) !== '' ? $namaIcd($v['diagnosa']) : '');
   $diagSek = [];
   if (trim((string) ($v['kdDiag2'] ?? '')) !== '') $diagSek[] = trim($v['kdDiag2'] . ' - ' . ($v['nmDiag2'] ?? ''), ' -');
   elseif (trim((string) ($v['diagnosa_sekunder'] ?? '')) !== '') {
      foreach (explode(',', $v['diagnosa_sekunder']) as $k) if (trim($k) !== '') $diagSek[] = $namaIcd($k);
   }
   if (trim((string) ($v['kdDiag3'] ?? '')) !== '') $diagSek[] = trim($v['kdDiag3'] . ' - ' . ($v['nmDiag3'] ?? ''), ' -');

   $resep = $resepPerVisit[$vid] ?? [];
   $lab = $labPerVisit[$vid] ?? [];
   $jmlObat = 0;
   foreach ($resep as $r) $jmlObat += count($detailPerResep[(int) $r['id_permintaan_farmasi']] ?? []);
   $resume = 'module/admin/print/' . ($ranap ? 'formulir_resume_v2' : 'formulir_resume_poli')
      . '?no=' . urlencode($vid) . '&rm=' . urlencode($nomorRm) . '&download=1&ranap=' . urlencode((string) $v['status_rawatinap']);
?>
   <div class="rw-item">
      <div class="rw-date">
         <div class="d"><?= e(tgl($v['visit_date'], 'd')) ?></div>
         <div class="m"><?= e(tgl($v['visit_date'], 'M Y')) ?></div>
         <div class="t"><?= e(substr((string) $v['visit_time'], 0, 5), '') ?></div>
      </div>
      <div class="rw-dot bg-<?= $warna ?>"></div>
      <div class="rw-card">
         <div class="rw-head" data-bs-toggle="collapse" data-bs-target="#<?= $uid ?>" role="button" aria-expanded="<?= $offset + $i === 0 ? 'true' : 'false' ?>">
            <div class="min-w-0 flex-grow-1">
               <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                  <span class="rw-badge bg-<?= $warna ?>-subtle text-<?= $warna ?>"><?= e($jenis) ?></span>
                  <span class="fw-semibold text-dark"><?= e($v['id_poli']) ?></span>
                  <span class="text-muted small"><i class="fas fa-user-md me-1"></i><?= e($v['id_doctor']) ?></span>
               </div>
               <div class="rw-diag"><?= $diagUtama !== '' ? e($diagUtama) : '<span class="text-muted">Diagnosa belum diisi</span>' ?></div>
               <div class="d-flex flex-wrap gap-3 mt-1 rw-meta">
                  <span><i class="fas fa-pills me-1"></i><?= $jmlObat ?> obat</span>
                  <span><i class="fas fa-flask me-1"></i><?= count($lab) ?> lab</span>
                  <span><i class="fas fa-sign-out-alt me-1"></i><?= e(historyDischargeStatus($v)) ?></span>
                  <span class="text-muted"><?= e($vid) ?></span>
               </div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-shrink-0">
               <a href="<?= e($resume) ?>" target="_blank" class="btn btn-sm btn-light text-danger" title="Download resume <?= $ranap ? 'rawat inap' : 'poliklinik' ?>" onclick="event.stopPropagation()"><i class="fas fa-file-pdf me-1"></i>Resume</a>
               <i class="fas fa-chevron-down rw-chev"></i>
            </div>
         </div>

         <div id="<?= $uid ?>" class="collapse <?= $offset + $i === 0 ? 'show' : '' ?>">
            <div class="rw-body">
               <ul class="nav nav-pills rw-tabs mb-3" role="tablist">
                  <li class="nav-item"><button type="button" class="nav-link active" data-bs-toggle="pill" data-bs-target="#<?= $uid ?>p"><i class="fas fa-stethoscope me-1"></i>Pemeriksaan</button></li>
                  <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="pill" data-bs-target="#<?= $uid ?>o"><i class="fas fa-pills me-1"></i>Obat <span class="badge bg-light text-dark ms-1"><?= $jmlObat ?></span></button></li>
                  <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="pill" data-bs-target="#<?= $uid ?>l"><i class="fas fa-flask me-1"></i>Lab <span class="badge bg-light text-dark ms-1"><?= count($lab) ?></span></button></li>
               </ul>
               <div class="tab-content">
                  <!-- PEMERIKSAAN -->
                  <div class="tab-pane fade show active" id="<?= $uid ?>p">
                     <div class="rw-vitals mb-3">
                        <?php foreach ([
                           'tekanan_darah' => ['Tekanan darah', 'mmHg'],
                           'nadi' => ['Nadi', 'x/mnt'],
                           'suhu' => ['Suhu', '°C'],
                           'respirasi' => ['Respirasi', 'x/mnt'],
                           'saturasi' => ['SpO₂', '%'],
                           'berat_badan' => ['Berat', 'kg'],
                           'tinggi_badan' => ['Tinggi', 'cm'],
                        ] as $kol => [$label, $unit]):
                           $nilai = trim((string) ($v[$kol] ?? ''));
                           // 0 / 0/0 = belum diukur
                           if (preg_match('#^[0.,\s/]*$#', $nilai)) $nilai = ''; ?>
                           <div class="rw-vital">
                              <div class="val"><?= $nilai === '' ? '<span class="text-muted">-</span>' : e($nilai) . ' <small>' . $unit . '</small>' ?></div>
                              <div class="lbl"><?= $label ?></div>
                           </div>
                        <?php endforeach; ?>
                     </div>
                     <div class="row g-3 small">
                        <div class="col-md-6">
                           <div class="rw-field"><span>Keluhan utama</span>
                              <div><?= nl2br(e($v['keluhan_penyerta'])) ?></div>
                           </div>
                           <div class="rw-field"><span>Anamnesa</span>
                              <div><?= nl2br(e($v['anamnesa'])) ?></div>
                           </div>
                        </div>
                        <div class="col-md-6">
                           <div class="rw-field"><span>Diagnosa utama</span>
                              <div><?= e($diagUtama) ?></div>
                           </div>
                           <div class="rw-field"><span>Diagnosa sekunder</span>
                              <div><?= $diagSek ? implode('<br>', array_map('e', $diagSek)) : '-' ?></div>
                           </div>
                           <div class="rw-field"><span>Tindakan / terapi</span>
                              <div><?= trim((string) ($v['tindakan'] ?? '')) !== '' ? nl2br(e($v['tindakan'])) : ($jmlObat ? 'Lihat tab Obat' : '-') ?></div>
                           </div>
                        </div>
                     </div>
                  </div>

                  <!-- OBAT -->
                  <div class="tab-pane fade" id="<?= $uid ?>o">
                     <?php if (!$resep): ?>
                        <div class="rw-empty"><i class="fas fa-pills"></i>Tidak ada resep</div>
                     <?php endif; ?>
                     <?php foreach ($resep as $r):
                        [$stLabel, $stCls] = statusResep($r['status_permintaan']);
                        $det = $detailPerResep[(int) $r['id_permintaan_farmasi']] ?? [];
                        $racikan = strtolower((string) $r['tipe_obat']) === 'racikan';
                        $subtotal = 0; ?>
                        <div class="rw-resep">
                           <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                              <div><span class="fw-semibold">Resep #<?= (int) $r['id_permintaan_farmasi'] ?></span>
                                 <span class="text-muted small ms-2"><?= e(tgl($r['created_at'], 'd M Y H:i')) ?></span>
                              </div>
                              <div class="d-flex gap-1">
                                 <span class="rw-badge bg-primary-subtle text-primary"><?= e($r['tipe_obat']) ?></span>
                                 <span class="rw-badge <?= $stCls ?>"><?= $stLabel ?></span>
                              </div>
                           </div>
                           <?php if ($racikan): ?>
                              <div class="small text-muted mb-2"><i class="fas fa-mortar-pestle me-1"></i>Racikan: <?= e($r['rck_jumlah']) ?> <?= e($r['rck_satuan'], '') ?> · Signa <?= e($r['rck_signa']) ?></div>
                           <?php endif; ?>
                           <?php if ($det): ?>
                              <div class="table-responsive">
                                 <table class="table table-sm mb-0 small align-middle">
                                    <thead>
                                       <tr>
                                          <th>Obat</th>
                                          <th>Signa</th>
                                          <th class="text-end">Jumlah</th>
                                          <th class="text-end">Subtotal</th>
                                       </tr>
                                    </thead>
                                    <tbody>
                                       <?php foreach ($det as $d):
                                          $harga = (float) ($d['harga'] ?: $d['pharmacy_price_item'] ?: 0);
                                          $sub = $harga * (int) $d['qty'];
                                          $subtotal += $sub; ?>
                                          <tr>
                                             <td><?= e(namaObat($d)) ?></td>
                                             <td><?= e($d['signa']) ?></td>
                                             <td class="text-end"><?= (int) $d['qty'] ?></td>
                                             <td class="text-end"><?= $sub > 0 ? 'Rp ' . number_format($sub, 0, ',', '.') : '-' ?></td>
                                          </tr>
                                       <?php endforeach; ?>
                                    </tbody>
                                    <?php if ($subtotal > 0): ?>
                                       <tfoot>
                                          <tr>
                                             <th colspan="3" class="text-end">Total</th>
                                             <th class="text-end">Rp <?= number_format($subtotal, 0, ',', '.') ?></th>
                                          </tr>
                                       </tfoot>
                                    <?php endif; ?>
                                 </table>
                              </div>
                           <?php else: ?>
                              <div class="small text-muted">Tidak ada detail obat</div>
                           <?php endif; ?>
                        </div>
                     <?php endforeach; ?>
                  </div>

                  <!-- LAB -->
                  <div class="tab-pane fade" id="<?= $uid ?>l">
                     <?php if (!$lab): ?>
                        <div class="rw-empty"><i class="fas fa-flask"></i>Tidak ada pemeriksaan laboratorium</div>
                     <?php endif; ?>
                     <?php foreach ($lab as $l):
                        $hasil = $hasilPerLab[(int) $l['id_inspection']] ?? [];
                        $catatan = trim((string) ($l['inspection_note'] ?? ''));
                        if (count($hasil) === 1 && $catatan === trim((string) ($hasil[0]['hasil'] ?? ''))) $catatan = ''; ?>
                        <div class="rw-resep">
                           <div class="d-flex justify-content-between align-items-center mb-2">
                              <span class="fw-semibold"><i class="fas fa-flask text-primary me-1"></i><?= e($l['inspection_name']) ?></span>
                              <span class="text-muted small"><?= e(tgl($l['inspection_date'])) ?></span>
                           </div>
                           <?php if ($hasil): ?>
                              <div class="table-responsive">
                                 <table class="table table-sm mb-0 small align-middle">
                                    <thead>
                                       <tr>
                                          <th>Pemeriksaan</th>
                                          <th>Hasil</th>
                                          <th>Satuan</th>
                                          <th>Nilai normal</th>
                                          <th>Keterangan</th>
                                       </tr>
                                    </thead>
                                    <tbody>
                                       <?php foreach ($hasil as $h):
                                          $min = trim((string) ($h['minimum'] ?? ''));
                                          $max = trim((string) ($h['maksimum'] ?? ''));
                                          $abn = $min !== '' && $max !== '' && is_numeric($h['hasil']) && is_numeric($min) && is_numeric($max)
                                             && ((float) $h['hasil'] < (float) $min || (float) $h['hasil'] > (float) $max); ?>
                                          <tr>
                                             <td><?= e($h['assemen']) ?></td>
                                             <td class="<?= $abn ? 'text-danger fw-bold' : '' ?>"><?= e($h['hasil']) ?><?= $abn ? ' <i class="fas fa-exclamation-circle" title="Di luar nilai normal"></i>' : '' ?></td>
                                             <td><?= e($h['satuan'], '') ?></td>
                                             <td><?php $rentang = array_values(array_filter([$min, $max], fn($x) => trim($x, " -") !== '')); echo $rentang ? e(implode(' - ', $rentang)) : '-'; ?></td>
                                             <td><?= e($h['keterangan']) ?></td>
                                          </tr>
                                       <?php endforeach; ?>
                                    </tbody>
                                 </table>
                              </div>
                           <?php elseif ($catatan === ''): ?>
                              <div class="small text-muted">Belum ada hasil</div>
                           <?php endif; ?>
                           <?php if ($catatan !== ''): ?>
                              <div class="small mt-2"><span class="text-muted">Hasil / catatan:</span> <?= nl2br(e($catatan)) ?></div>
                           <?php endif; ?>
                        </div>
                     <?php endforeach; ?>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
<?php
endforeach;
$html = ob_get_clean();

$next = $offset + count($visits);
jawab([
   'status' => 'success',
   'html' => $html,
   'total' => $total,
   'next' => $next < $total ? $next : null,
]);
