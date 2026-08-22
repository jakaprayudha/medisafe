<?php
$title = 'Riwayat';
require '../../controller/view.php';
?>
<!doctype html>
<html lang="en">

<head>
  <base href="../../">
  <?php
  require '../../assets/template/head.php';
  ?>
  <style>
    .vital-box {
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      padding: 8px;
      background: #f9fafb;
    }

    .vital-value {
      font-size: 16px;
      font-weight: 600;
      color: #111827;
    }

    .vital-label {
      font-size: 11px;
      color: #6b7280;
    }
  </style>
</head>

<body>
  <!--  Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <!-- Sidebar Start -->
    <?php
    require 'sidebar.php';
    ?>
    <!--  Sidebar End -->
    <!--  Main wrapper -->
    <div class="body-wrapper">
      <!--  Header Start -->
      <?php
      require 'navbar.php';
      ?>
      <!--  Header End -->
      <div class="body-wrapper-inner">
        <div class="container-fluid">
          <?php
          $rme = $_GET['rme']; // default a
          if ($rme == 'a') {
            include 'menu_rme.php';
          } else if ($rme == 'b') {
            include 'menu_rmeb.php';
          } else if ($rme == 'c') {
            include 'menu_rme_inap.php';
          }
          ?>
          <div class="row">
            <div class="col-12">
              <?php
              require 'card-pasien.php';
              ?>
            </div>
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <div class="alert alert-info d-flex align-items-start justify-content-between">

                    <!-- 🔹 LEFT (icon + text) -->
                    <div class="d-flex align-items-start gap-2">

                      <i class="fas fa-info-circle mt-1"></i>

                      <div>
                        <strong>Informasi Riwayat Pengobatan:</strong>
                        <ul class="mb-0 small">
                          <li>
                            Riwayat pengobatan pada daftar ini merupakan data kunjungan pasien yang terdaftar dengan
                            <strong>MEDISAFE</strong>
                          </li>
                        </ul>
                      </div>

                    </div>

                    <!-- 🔹 RIGHT BUTTON -->
                    <!-- <div>
                      <a href="module/admin/icareInternal" target="_blank">
                        <button type="button" class="btn btn-sm btn-primary d-flex align-items-center gap-1">
                          <i class="fas fa-print"></i> Review RME
                        </button>
                      </a>
                    </div> -->

                  </div>
                  <div class="row">
                    <div class="col-12">

                      <!-- 🔥 WRAPPER SCROLL -->
                      <div style="max-height: 500px; overflow-y: auto; padding-right: 5px;">

                        <?php
                        $no = $_GET['no'];
                        $checkID = mysqli_query($koneksi, "SELECT id_patient FROM pasien_visit WHERE visit_ID='$no'");
                        $dataID = mysqli_fetch_array($checkID);
                        $pt = $dataID['id_patient'];
                        $getquery = tampildata("SELECT pv.*, mp.nomor_rm FROM pasien_visit pv LEFT JOIN ms_patient mp ON mp.id_patient = pv.id_patient WHERE pv.id_patient='$pt' ORDER BY visit_date DESC");
                        ?>
                        <?php if (!empty($getquery)): ?>
                          <div class="accordion" id="accordionExample">
                            <?php foreach ($getquery as $index => $row): ?>
                              <?php
                              $collapseId = "collapse" . $index;
                              $headingId = "heading" . $index;
                              ?>

                              <div class="accordion-item">
                                <style>
                                  .accordion-header-custom {
                                    position: relative;
                                  }

                                  .btn-download-resume {
                                    position: absolute;
                                    right: 55px;
                                    top: 50%;
                                    transform: translateY(-50%);
                                    z-index: 20;
                                  }
                                </style>

                                <h2 class="accordion-header accordion-header-custom"
                                  id="<?= $headingId ?>">

                                  <button class="accordion-button <?= $index != 0 ? 'collapsed' : '' ?>"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#<?= $collapseId ?>"
                                    aria-expanded="<?= $index == 0 ? 'true' : 'false' ?>">

                                    <?= $row['patient_name'] ?? 'Tanggal : ' ?>
                                    <?= $row['visit_date'] ?>
                                    <?= $row['visit_time'] ?>

                                  </button>

                                  <!-- 🔥 BUTTON DOWNLOAD -->
                                  <?php
                                  $statusranap = $row['status_rawatinap'] ?? '';
                                  if ($statusranap == '1') { ?>
                                    <a href="module/admin/print/formulir_resume_v2?no=<?= $row['visit_ID'] ?>&rm=<?= $row['nomor_rm'] ?>&download=1&ranap=<?= $row['status_rawatinap'] ?>"
                                      target="_blank"
                                      class="btn btn-sm btn-danger btn-download-resume">

                                      <i class="fas fa-file-pdf"></i>
                                      Download Resume Rawat Inap
                                    </a>
                                  <?php   } else { ?>
                                    <a href="module/admin/print/formulir_resume_poli?no=<?= $row['visit_ID'] ?>&rm=<?= $row['nomor_rm'] ?>&download=1&ranap=<?= $row['status_rawatinap'] ?>"
                                      target="_blank"
                                      class="btn btn-sm btn-danger btn-download-resume">

                                      <i class="fas fa-file-pdf"></i>
                                      Download Resume Poliklinik
                                    </a>
                                  <?php  }
                                  ?>


                                </h2>

                                <div id="<?= $collapseId ?>"
                                  class="accordion-collapse collapse <?= $index == 0 ? 'show' : '' ?>"
                                  data-bs-parent="#accordionExample">

                                  <div class="accordion-body p-3">

                                    <!-- HEADER INFO -->
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                      <div>
                                        <div class="fw-semibold">Detail Kunjungan</div>
                                        <small class="text-muted">
                                          <?= date('d M Y', strtotime($row['visit_date'])) ?> • <?= $row['visit_ID'] ?>
                                        </small>
                                      </div>
                                      <span class="badge bg-light text-dark border">
                                        <?= $row['id_poli'] ?>
                                      </span>
                                    </div>

                                    <!-- INFO GRID -->
                                    <div class="row g-2 mb-3">
                                      <div class="col-md-6">
                                        <div class="p-2 border rounded small">
                                          <div class="text-muted">Dokter</div>
                                          <div class="fw-semibold"><?= $row['id_doctor'] ?></div>
                                        </div>
                                      </div>

                                      <div class="col-md-6">
                                        <div class="p-2 border rounded small">
                                          <div class="text-muted">Poli</div>
                                          <div class="fw-semibold"><?= $row['id_poli'] ?></div>
                                        </div>
                                      </div>
                                    </div>

                                    <!-- TABS EMR -->
                                    <ul class="nav nav-tabs mb-3" id="tab<?= $index ?>" role="tablist">

                                      <li class="nav-item">
                                        <button type="button" class="nav-link active"
                                          data-bs-toggle="tab"
                                          data-bs-target="#pemeriksaan<?= $index ?>">
                                          🩺 Pemeriksaan
                                        </button>
                                      </li>

                                      <li class="nav-item">
                                        <button type="button" class="nav-link"
                                          data-bs-toggle="tab"
                                          data-bs-target="#obat<?= $index ?>">
                                          💊 Obat
                                        </button>
                                      </li>

                                      <li class="nav-item">
                                        <button type="button" class="nav-link"
                                          data-bs-toggle="tab"
                                          data-bs-target="#lab<?= $index ?>">
                                          🧪 Lab
                                        </button>
                                      </li>


                                    </ul>

                                    <div class="tab-content">

                                      <!-- 🩺 PEMERIKSAAN -->
                                      <div class="tab-pane fade show active" id="pemeriksaan<?= $index ?>">

                                        <!-- 🩺 VITAL SIGN -->
                                        <div class="border rounded p-3 mb-3">
                                          <div class="fw-semibold mb-2">🩺 Vital Sign</div>

                                          <div class="row g-2 text-center small">

                                            <div class="col-6 col-md-3">
                                              <div class="vital-box">
                                                <div class="vital-value"><?= $row['tekanan_darah'] ?? '-' ?></div>
                                                <div class="vital-label">Tekanan Darah</div>
                                              </div>
                                            </div>

                                            <div class="col-6 col-md-3">
                                              <div class="vital-box">
                                                <div class="vital-value"><?= $row['nadi'] ?? '-' ?></div>
                                                <div class="vital-label">Nadi</div>
                                              </div>
                                            </div>

                                            <div class="col-6 col-md-3">
                                              <div class="vital-box">
                                                <div class="vital-value"><?= $row['suhu'] ?? '-' ?></div>
                                                <div class="vital-label">Suhu (°C)</div>
                                              </div>
                                            </div>

                                            <div class="col-6 col-md-3">
                                              <div class="vital-box">
                                                <div class="vital-value"><?= $row['respirasi'] ?? '-' ?></div>
                                                <div class="vital-label">Respirasi</div>
                                              </div>
                                            </div>

                                            <div class="col-6 col-md-3">
                                              <div class="vital-box">
                                                <div class="vital-value"><?= $row['saturasi'] ?? '-' ?></div>
                                                <div class="vital-label">SpO₂</div>
                                              </div>
                                            </div>

                                            <div class="col-6 col-md-3">
                                              <div class="vital-box">
                                                <div class="vital-value"><?= $row['berat_badan'] ?? '-' ?></div>
                                                <div class="vital-label">Berat (kg)</div>
                                              </div>
                                            </div>

                                            <div class="col-6 col-md-3">
                                              <div class="vital-box">
                                                <div class="vital-value"><?= $row['tinggi_badan'] ?? '-' ?></div>
                                                <div class="vital-label">Tinggi (cm)</div>
                                              </div>
                                            </div>

                                            <div class="col-6 col-md-3">
                                              <div class="vital-box">
                                                <div class="vital-value"><?= $row['bmi'] ?? '-' ?></div>
                                                <div class="vital-label">BMI<?= !empty($row['bmi_keterangan']) ? ' (' . $row['bmi_keterangan'] . ')' : '' ?></div>
                                              </div>
                                            </div>

                                          </div>
                                        </div>

                                        <!-- 📋 PEMERIKSAAN -->
                                        <div class="border rounded p-3 small">

                                          <div class="mb-2">
                                            <strong>Keluhan Utama</strong>
                                            <div class="text-muted"><?= $row['keluhan_penyerta'] ?? '-' ?></div>
                                          </div>

                                          <div class="mb-2">
                                            <strong>Anamnesa</strong>
                                            <div class="text-muted"><?= $row['anamnesa'] ?? '-' ?></div>
                                          </div>

                                          <?php
                                          $diagnosa = $row['kdDiag1'] ?? $row['diagnosa'] ?? '';
                                          $diagnosaData = mysqli_query($koneksi, "SELECT * FROM icd_10 WHERE code='$diagnosa'");
                                          $icd = mysqli_fetch_array($diagnosaData);
                                          ?>
                                          <div class="mb-2">
                                            <strong>Diagnosa Utama</strong>
                                            <div class="text-muted">
                                              <?=
                                              !empty($row['kdDiag1'])
                                                ? $row['kdDiag1'] . ' - ' . $row['nmDiag1']
                                                : (
                                                  !empty($row['diagnosa'])
                                                  ? $icd['code'] . ' - ' . $icd['icd10']
                                                  : '-'
                                                )
                                              ?>
                                            </div>
                                          </div>
                                          <?php
                                          $diagnosaSekunder = $row['kdDiag2'] ?? $row['diagnosa_sekunder'] ?? '';
                                          $diagnosaDataSekunder = mysqli_query($koneksi, "SELECT * FROM icd_10 WHERE code='$diagnosaSekunder'");
                                          $icdSekunder = mysqli_fetch_array($diagnosaDataSekunder);
                                          ?>
                                          <div class="mb-2">
                                            <strong>Diagnosa Sekunder</strong>
                                            <div class="text-muted">
                                              <?php
                                              if (!empty($row['kdDiag2'])) {

                                                echo $row['kdDiag2'] . ' - ' . $row['nmDiag2'];
                                              } elseif (!empty($row['diagnosa_sekunder'])) {

                                                $kodeDiagnosa = array_map('trim', explode(',', $row['diagnosa_sekunder']));
                                                $hasilDiagnosa = [];

                                                foreach ($kodeDiagnosa as $kode) {

                                                  $query = mysqli_query(
                                                    $koneksi,
                                                    "SELECT code, icd10 FROM icd_10 WHERE code='$kode'"
                                                  );

                                                  if ($data = mysqli_fetch_assoc($query)) {
                                                    $hasilDiagnosa[] = $data['code'] . ' - ' . $data['icd10'];
                                                  } else {
                                                    $hasilDiagnosa[] = $kode;
                                                  }
                                                }

                                                echo implode('<br>', $hasilDiagnosa);
                                              } else {

                                                echo '-';
                                              }
                                              ?>
                                            </div>
                                            <?php
                                            if ($row['kdDiag3'] != null) { ?>
                                              <br>
                                              <div class="text-muted">
                                                <?= (!empty($row['kdDiag3']) && !empty($row['nmDiag3']))
                                                  ? $row['kdDiag3'] . ' - ' . $row['nmDiag3']
                                                  : '-' ?>
                                              </div>
                                            <?php   }
                                            ?>
                                          </div>

                                          <div class="mb-2">
                                            <strong>Tindakan / Terapi</strong>
                                            <div class="text-muted">
                                              <?php
                                              $id_visit = $row['visit_ID'] ?? '';

                                              if (!empty($row['tindakan'])) {

                                                echo nl2br($row['tindakan']);
                                              } else {

                                                $obatList = [];

                                                $qPermintaan = mysqli_query(
                                                  $koneksi,
                                                  "SELECT id_permintaan_farmasi
                                                      FROM permintaan_pharmacy
                                                      WHERE id_visit = '$id_visit'
                                                      ORDER BY id_permintaan_farmasi ASC"
                                                );

                                                while ($permintaan = mysqli_fetch_assoc($qPermintaan)) {

                                                  $idPermintaan = $permintaan['id_permintaan_farmasi'];

                                                  $qDetail = mysqli_query(
                                                    $koneksi,
                                                    "SELECT
                                                          d.qty,
                                                          d.signa,
                                                          d.item_name,
                                                          p.pharmacy_name_generic,
                                                          p.pharmacy_name_trade
                                                      FROM permintaan_pharmacy_details d
                                                      LEFT JOIN ms_pharmacy p
                                                          ON p.id_pharmacy = d.id_pharmacy
                                                      WHERE d.id_permintaan_farmasi = '$idPermintaan'"
                                                  );

                                                  while ($detail = mysqli_fetch_assoc($qDetail)) {
                                                    $namaObat =
                                                      !empty($detail['pharmacy_name_generic'])
                                                      ? $detail['pharmacy_name_generic']
                                                      : 'Nama Obat Tidak Ada';

                                                    $obatList[] =
                                                      $namaObat .
                                                      ' | Qty: ' . $detail['qty'] .
                                                      (!empty($detail['signa']) ? ' | Signa: ' . $detail['signa'] : '');
                                                  }
                                                }

                                                echo !empty($obatList)
                                                  ? implode('<br>', array_unique($obatList))
                                                  : '-';
                                              }
                                              ?>
                                            </div>
                                          </div>

                                          <div>
                                            <strong>Status Pulang</strong>
                                            <div>
                                              <span class="badge bg-success">
                                                <?php
                                                $statuspulang = $row['status_pulang'] ?? '';
                                                if ($statuspulang == '0') {
                                                  echo 'Berobat Jalan';
                                                } else if ($statuspulang == '3') {
                                                  echo 'Berobat Jalan';
                                                } elseif ($statuspulang == '4') {
                                                  echo 'Rujuk Lanjut';
                                                } elseif ($statuspulang == '5') {
                                                  echo 'Rujuk Internal';
                                                } else {
                                                  echo 'Rawat Inap';
                                                }
                                                ?>
                                              </span>
                                            </div>
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
                                                <th>Dosis</th>
                                                <th>Jumlah</th>
                                              </tr>
                                            </thead>
                                            <tbody>

                                              <?php
                                              $obat = tampildata("SELECT * 
                                                  FROM permintaan_pharmacy 
                                                  WHERE id_visit = '" . $row['visit_ID'] . "'
                                                  ORDER BY id_permintaan_farmasi ASC
                                                ");

                                              if ($obat) {

                                                foreach ($obat as $o) {

                                                  // 🔥 ambil detail tiap tiket
                                                  $detail = tampildata("SELECT * 
                                                        FROM permintaan_pharmacy_details LEFT JOIN ms_pharmacy ON permintaan_pharmacy_details.id_pharmacy = ms_pharmacy.id_pharmacy 
                                                        WHERE id_permintaan_farmasi = '" . $o['id_permintaan_farmasi'] . "'
                                                      ");
                                              ?>

                                                  <!-- 🔹 HEADER TIKET -->
                                                  <tr class="ticket-header">
                                                    <td colspan="4">
                                                      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                                        <div>
                                                          <strong>🧾 Resep #<?= $o['id_permintaan_farmasi'] ?></strong>
                                                          <div class="small text-muted">
                                                            <?= date('d M Y H:i', strtotime($o['created_at'])) ?>
                                                          </div>
                                                        </div>

                                                        <div>
                                                          <span class="badge bg-primary col-12"><?= $o['tipe_obat'] ?></span>
                                                        </div>
                                                      </div>
                                                    </td>
                                                  </tr>

                                                  <?php if (strtolower($o['tipe_obat']) == 'racikan') { ?>
                                                    <!-- 🔥 RACIKAN INFO -->
                                                    <tr class="racikan-box">
                                                      <td colspan="4">
                                                        <div class="racikan-content">

                                                          <div class="racikan-title">🧪 Racikan</div>

                                                          <div class="racikan-grid">
                                                            <div>
                                                              <span class="label">Jumlah:</span>
                                                              <span><?= $o['rck_jumlah'] ?? '-' ?></span>
                                                            </div>

                                                            <div>
                                                              <span class="label">Satuan:</span>
                                                              <span><?= $o['rck_satuan'] ?? '-' ?></span>
                                                            </div>

                                                            <div>
                                                              <span class="label">Signa:</span>
                                                              <span><?= $o['rck_signa'] ?? '-' ?></span>
                                                            </div>
                                                          </div>

                                                        </div>
                                                      </td>
                                                    </tr>
                                                  <?php } ?>

                                                  <?php if ($detail) { ?>

                                                    <?php foreach ($detail as $d) { ?>
                                                      <tr>
                                                        <td><?= $d['pharmacy_name_generic'] ?></td>
                                                        <td><?= $d['signa'] ?></td>
                                                        <td><?= $d['qty'] ?></td>
                                                        <td>Rp <?= number_format($d['pharmacy_price_item'] * $d['qty'], 0, ',', '.') ?></td>
                                                      </tr>
                                                    <?php } ?>

                                                  <?php } else { ?>
                                                    <tr>
                                                      <td colspan="4" class="text-center text-muted">
                                                        Tidak ada detail obat
                                                      </td>
                                                    </tr>
                                                  <?php } ?>

                                                <?php
                                                }
                                              } else {
                                                ?>

                                                <tr>
                                                  <td colspan="4" class="text-center text-muted">
                                                    Tidak ada data obat
                                                  </td>
                                                </tr>

                                              <?php } ?>

                                            </tbody>
                                          </table>
                                        </div>
                                      </div>

                                      <!-- 🧪 LAB -->
                                      <div class="tab-pane fade" id="lab<?= $index ?>">

                                        <?php
                                        $lab = tampildata("
                                                SELECT * 
                                                FROM visit_inspection 
                                                WHERE id_visit='" . $row['visit_ID'] . "'
                                                ORDER BY inspection_date DESC
                                              ");

                                        if ($lab) {
                                          foreach ($lab as $l) {
                                            // 🔥 ambil hasil + join item
                                            $hasil = tampildata("
                                              SELECT 
                                                  lr.*, 
                                                  li.assemen, 
                                                  li.satuan, 
                                                  li.minimum, 
                                                  li.maksimum
                                              FROM laboratorium_result lr
                                              LEFT JOIN laboratorium_item li 
                                                  ON li.id = lr.id_item
                                              WHERE lr.id_inspection = '" . $l['id'] . "'
                                              ORDER BY li.urutan ASC
                                            ");
                                        ?>
                                            <!-- 🔹 HEADER LAB -->
                                            <div class="lab-card mb-3">

                                              <div class="lab-header">
                                                🧪 <?= $l['inspection_name'] ?>
                                                <span class="lab-date">
                                                  <?= date('d M Y H:i', strtotime($l['inspection_date'])) ?>
                                                </span>
                                              </div>

                                              <?php if ($hasil) { ?>

                                                <div class="table-responsive">
                                                  <table class="table table-sm table-bordered mb-0 small">
                                                    <thead class="table-light">
                                                      <tr>
                                                        <th>Pemeriksaan</th>
                                                        <th>Hasil</th>
                                                        <th>Satuan</th>
                                                        <th>Nilai Normal</th>
                                                        <th>Keterangan</th>
                                                      </tr>
                                                    </thead>

                                                    <tbody>
                                                      <?php foreach ($hasil as $h) {

                                                        // 🔥 highlight abnormal
                                                        $flag = '';
                                                        if ($h['minimum'] && $h['maksimum'] && is_numeric($h['hasil'])) {
                                                          if ($h['hasil'] < $h['minimum'] || $h['hasil'] > $h['maksimum']) {
                                                            $flag = 'text-danger fw-bold';
                                                          }
                                                        }
                                                      ?>
                                                        <tr>
                                                          <td><?= $h['assemen'] ?></td>
                                                          <td class="<?= $flag ?>"><?= $h['hasil'] ?></td>
                                                          <td><?= $h['satuan'] ?></td>
                                                          <td><?= $h['minimum'] ?> - <?= $h['maksimum'] ?></td>
                                                          <td><?= $h['keterangan'] ?? '-' ?></td>
                                                        </tr>
                                                      <?php } ?>
                                                    </tbody>
                                                  </table>
                                                </div>

                                              <?php } else { ?>

                                                <div class="text-muted small p-2">
                                                  Tidak ada hasil lab
                                                </div>

                                              <?php } ?>

                                            </div>

                                          <?php
                                          }
                                        } else {
                                          ?>

                                          <div class="text-center text-muted py-3">
                                            🧪 Tidak ada data laboratorium
                                          </div>

                                        <?php } ?>

                                      </div>

                                    </div>

                                  </div>

                                </div>
                              </div>

                            <?php endforeach; ?>

                          </div>

                        <?php else: ?>

                          <!-- 🚨 ALERT KOSONG -->
                          <div class="alert alert-danger d-flex align-items-center gap-2" role="alert">
                            <i class="ti ti-info-circle"></i>
                            <div>
                              <strong>Belum ada riwayat kunjungan</strong><br>
                              <small>Pasien ini belum memiliki data kunjungan yang tercatat di sistem.</small>
                            </div>
                          </div>

                        <?php endif; ?>

                      </div>
                      <!-- END SCROLL -->

                    </div>
                  </div>

                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php
  require 'library.php';
  ?>
</body>


</html>