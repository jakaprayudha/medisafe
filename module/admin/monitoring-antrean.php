<?php
$title = 'Monitoring Antrean Online';
require '../../controller/view.php';
$roles = $_SESSION['roles'] ?? '';
if (!in_array($roles, ['superadmin', 'admin'], true)) {
  http_response_code(403);
  exit('Akses tidak diizinkan.');
}
$isSuper = $roles === 'superadmin';
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .ma-kpi .card-body {
      padding: 18px 20px;
    }

    .ma-kpi .ma-label {
      font-size: .8rem;
      color: #5a6a85;
      font-weight: 600;
    }

    .ma-kpi .ma-value {
      font-size: 1.9rem;
      font-weight: 700;
      line-height: 1.2;
    }

    .ma-kpi .ma-sub {
      font-size: .78rem;
      color: #6c757d;
    }

    .ma-kpi .progress {
      height: 6px;
    }

    .ma-mini {
      border: 1px solid #e5eaef;
      border-radius: 10px;
      padding: 10px 14px;
      background: #fff;
      height: 100%;
    }

    .ma-mini .ma-mini-val {
      font-size: 1.25rem;
      font-weight: 700;
    }

    .ma-mini .ma-mini-label {
      font-size: .75rem;
      color: #6c757d;
    }

    .ma-badge {
      font-size: .72rem;
      font-weight: 600;
      white-space: nowrap;
    }

    #maDetailTable td,
    #maTindakTable td {
      font-size: .82rem;
    }

    .ma-def dt {
      font-size: .82rem;
    }

    .ma-def dd {
      font-size: .8rem;
      color: #5a6a85;
      margin-bottom: .6rem;
    }
    /* select2 faskes: disamakan dengan .form-control (input tanggal) */
    #maFaskesWrap .select2-container--default .select2-selection--single {
      /* !important: menimpa aturan global .select2-selection di assets/template/head.php */
      height: 43px !important; padding: 0 !important; border: 1px solid #e0e6eb !important; border-radius: 8px !important; background-color: transparent;
      box-shadow: inset 0 1px 2px rgba(0, 0, 0, .075); transition: border-color .15s, box-shadow .15s;
    }
    #maFaskesWrap .select2-container--default .select2-selection--single .select2-selection__rendered {
      line-height: 41px; padding-left: 16px; padding-right: 36px; font-size: 14px; color: #29343d;
    }
    #maFaskesWrap .select2-container--default .select2-selection--single .select2-selection__placeholder { color: #8a97a3; }
    #maFaskesWrap .select2-container--default .select2-selection--single .select2-selection__arrow { height: 41px; width: 34px; top: 1px; right: 4px; }
    #maFaskesWrap .select2-container--default .select2-selection--single .select2-selection__arrow b {
      border: solid #5a6a85; border-width: 0 1.5px 1.5px 0; padding: 3px; margin: -5px 0 0 -4px; transform: rotate(45deg);
    }
    #maFaskesWrap .select2-container--default.select2-container--open .select2-selection__arrow b { transform: rotate(-135deg); margin-top: -1px; }
    #maFaskesWrap .select2-container--default.select2-container--focus .select2-selection--single,
    #maFaskesWrap .select2-container--default.select2-container--open .select2-selection--single {
      border-color: #b1adff !important; box-shadow: inset 0 1px 2px rgba(0, 0, 0, .075), 0 0 0 .25rem rgba(99, 91, 255, .25);
    }
    .select2-container--default .ma-faskes-dd { border: 1px solid #e0e6eb; border-radius: 8px; overflow: hidden; box-shadow: 0 8px 24px rgba(41, 52, 61, .12); margin-top: 4px; }
    .select2-container--default .ma-faskes-dd .select2-search--dropdown { padding: 8px; }
    .select2-container--default .ma-faskes-dd .select2-search__field { border: 1px solid #e0e6eb; border-radius: 6px; padding: 6px 10px; font-size: 13px; outline: 0; }
    .select2-container--default .ma-faskes-dd .select2-search__field:focus { border-color: #b1adff; }
    .select2-container--default .ma-faskes-dd .select2-results__option { font-size: 13px; padding: 7px 12px; color: #29343d; }
    .select2-container--default .ma-faskes-dd .select2-results__option--highlighted.select2-results__option--selectable { background: #635bff; color: #fff; }
    .select2-container--default .ma-faskes-dd .select2-results__option--selected { background: #efeeff; color: #635bff; font-weight: 600; }
    .select2-container--default .ma-faskes-dd .select2-results__option--highlighted .ma-off { color: #e6e4ff; }
    .ma-faskes-dd .ma-off, #maFaskesWrap .ma-off { color: #8a97a3; font-size: 11px; }
    #maDetailTable td, #maTindakTable td { white-space: nowrap; vertical-align: middle; }
    #maTindakTable td.ma-masalah { white-space: normal; min-width: 220px; max-width: 320px; }
    #maDetailTable th, #maTindakTable th, .ma-rekap th { font-size: .75rem; color: #5a6a85; font-weight: 600; white-space: nowrap; }
    .ma-chips .btn { border-radius: 20px; font-size: .75rem; padding: .2rem .75rem; background: #fff; border: 1px solid #e5eaef; color: #5a6a85; }
    .ma-chips .btn.active { background: #5d87ff; border-color: #5d87ff; color: #fff; }
    .ma-chips .btn .badge { font-size: .65rem; }
    .ma-sec { font-size: .72rem; font-weight: 700; color: #5a6a85; text-transform: uppercase; letter-spacing: .4px; }
    .ma-bar { position: fixed; top: 0; left: 0; height: 3px; background: #5d87ff; z-index: 9999; transition: width .3s, opacity .3s; }
    .ma-loading { position: relative; }
    .ma-loading::after { content: ''; position: absolute; inset: 0; background: rgba(255, 255, 255, .6); border-radius: inherit; z-index: 3; }
  </style>
</head>

<body>
  <div class="ma-bar" id="maBar" style="width:0"></div>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
    <?php require 'sidebar.php'; ?>
    <div class="body-wrapper">
      <?php require $isSuper ? 'navbar-master.php' : 'navbar.php'; ?>
      <div class="body-wrapper-inner">
        <div class="container-fluid">

          <!-- HEADER + FILTER -->
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-3">
              <div class="d-flex flex-wrap align-items-center gap-3 mb-2">
                <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary-subtle text-primary" style="width:40px;height:40px"><i class="fas fa-ticket-alt"></i></span>
                <div class="flex-grow-1" style="min-width:0">
                  <h5 class="fw-semibold mb-0">Monitoring Antrean Online BPJS</h5>
                  <div class="text-muted small" id="maPeriodeInfo">Kepatuhan antrean online FKTP: pemanfaatan, status panggil, batal, dan waktu layanan</div>
                </div>
                <button type="button" class="btn btn-light btn-sm" id="maRefresh" title="Muat ulang"><i class="fas fa-sync-alt"></i></button>
              </div>
              <div class="d-flex flex-wrap align-items-end gap-2">
                <?php if ($isSuper): ?>
                  <div id="maFaskesWrap" style="min-width:280px">
                    <label class="form-label small mb-1">Faskes</label>
                    <select id="maFaskes" class="form-select"></select>
                  </div>
                <?php endif; ?>
                <div>
                  <label class="form-label small mb-1" for="maFrom">Dari</label>
                  <input type="date" id="maFrom" class="form-control form-control-sm">
                </div>
                <div>
                  <label class="form-label small mb-1" for="maTo">Sampai</label>
                  <input type="date" id="maTo" class="form-control form-control-sm">
                </div>
                <div class="ma-chips d-flex flex-wrap gap-1 pb-1" id="maRanges">
                  <button type="button" class="btn ma-range" data-range="today">Hari ini</button>
                  <button type="button" class="btn ma-range" data-range="yesterday">Kemarin</button>
                  <button type="button" class="btn ma-range" data-range="7">7 hari</button>
                  <button type="button" class="btn ma-range" data-range="month">Bulan ini</button>
                  <button type="button" class="btn ma-range" data-range="lastmonth">Bulan lalu</button>
                </div>
              </div>
            </div>
          </div>
          <div id="maError" class="alert alert-danger d-none" role="alert"></div>
          <div id="maConfig" class="alert d-flex align-items-center gap-2 py-2 mb-3 d-none" role="alert"></div>

          <!-- KPI UTAMA -->
          <div class="row g-3 mb-3 ma-kpi">
            <div class="col-md-6 col-xl-3">
              <div class="card h-100 mb-0">
                <div class="card-body">
                  <div class="ma-label"><i class="fas fa-mobile-alt me-1 text-primary"></i> Pemanfaatan Antrean Online</div>
                  <div class="ma-value text-primary" id="kpiOnline">-</div>
                  <div class="progress my-2">
                    <div class="progress-bar bg-primary" id="kpiOnlineBar" style="width:0%"></div>
                  </div>
                  <div class="ma-sub" id="kpiOnlineSub">-</div>
                </div>
              </div>
            </div>
            <div class="col-md-6 col-xl-3">
              <div class="card h-100 mb-0">
                <div class="card-body">
                  <div class="ma-label"><i class="fas fa-mobile me-1 text-info"></i> Lewat Mobile JKN</div>
                  <div class="ma-value text-info" id="kpiMobile">-</div>
                  <div class="progress my-2">
                    <div class="progress-bar bg-info" id="kpiMobileBar" style="width:0%"></div>
                  </div>
                  <div class="ma-sub" id="kpiMobileSub">-</div>
                </div>
              </div>
            </div>
            <div class="col-md-6 col-xl-3">
              <div class="card h-100 mb-0">
                <div class="card-body">
                  <div class="ma-label"><i class="fas fa-bullhorn me-1 text-success"></i> Status Panggil Terlapor</div>
                  <div class="ma-value text-success" id="kpiTerlapor">-</div>
                  <div class="progress my-2">
                    <div class="progress-bar bg-success" id="kpiTerlaporBar" style="width:0%"></div>
                  </div>
                  <div class="ma-sub" id="kpiTerlaporSub">-</div>
                </div>
              </div>
            </div>
            <div class="col-md-6 col-xl-3">
              <div class="card h-100 mb-0">
                <div class="card-body">
                  <div class="ma-label"><i class="fas fa-stopwatch me-1 text-warning"></i> Waktu Hadir → Selesai Periksa</div>
                  <div class="ma-value text-warning" id="kpiWaktu">-</div>
                  <div class="ma-sub mt-2" id="kpiWaktuSub">-</div>
                </div>
              </div>
            </div>
          </div>

          <!-- ANGKA RINGKAS -->
          <div class="row g-2 mb-4">
            <div class="col-6 col-md-3 col-xl">
              <div class="ma-mini">
                <div class="ma-mini-val" id="cTotal">-</div>
                <div class="ma-mini-label">Kunjungan BPJS (rawat jalan)</div>
              </div>
            </div>
            <div class="col-6 col-md-3 col-xl">
              <div class="ma-mini">
                <div class="ma-mini-val text-info" id="cMobile">-</div>
                <div class="ma-mini-label">Antrean Mobile JKN</div>
              </div>
            </div>
            <div class="col-6 col-md-3 col-xl">
              <div class="ma-mini">
                <div class="ma-mini-val text-primary" id="cOnsite">-</div>
                <div class="ma-mini-label">Antrean Onsite (ke BPJS)</div>
              </div>
            </div>
            <div class="col-6 col-md-3 col-xl">
              <div class="ma-mini">
                <div class="ma-mini-val text-danger" id="cTanpa">-</div>
                <div class="ma-mini-label">Kunjungan sakit tanpa antrean online</div>
              </div>
            </div>
            <div class="col-6 col-md-3 col-xl">
              <div class="ma-mini">
                <div class="ma-mini-val text-danger" id="cBatal">-</div>
                <div class="ma-mini-label">Batal / tidak hadir</div>
              </div>
            </div>
            <div class="col-6 col-md-3 col-xl">
              <div class="ma-mini">
                <div class="ma-mini-val text-secondary" id="cSehat">-</div>
                <div class="ma-mini-label">Kunjungan sehat (tidak wajib antrean)</div>
              </div>
            </div>
            <div class="col-6 col-md-3 col-xl">
              <div class="ma-mini border-warning">
                <div class="ma-mini-val text-warning" id="cObat">-</div>
                <div class="ma-mini-label">Obat belum dikirim ke PCare</div>
              </div>
            </div>
          </div>

          <!-- GRAFIK HARIAN -->
          <div class="card mb-4">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Tren Harian</h6>
                <small class="text-muted">Batang: kunjungan sakit dilayani per sumber • Garis: % antrean online</small>
              </div>
              <div style="height:300px"><canvas id="maChart"></canvas></div>
            </div>
          </div>

          <!-- GRAFIK TAMBAHAN -->
          <div class="row g-3 mb-4">
            <div class="col-md-6 col-xl-4"><div class="card h-100 mb-0"><div class="card-body">
              <div class="ma-sec mb-2">Status panggil antrean online</div>
              <div style="height:240px"><canvas id="maChartPanggil"></canvas></div></div></div></div>
            <div class="col-md-6 col-xl-4"><div class="card h-100 mb-0"><div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-2"><div class="ma-sec">Waktu hadir → selesai periksa</div><small class="text-muted" id="maTungguInfo"></small></div>
              <div style="height:240px"><canvas id="maChartTunggu"></canvas></div></div></div></div>
            <div class="col-xl-4"><div class="card h-100 mb-0"><div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-2"><div class="ma-sec">Kedatangan per jam</div><small class="text-muted" id="maJamInfo"></small></div>
              <div style="height:240px"><canvas id="maChartJam"></canvas></div></div></div></div>
          </div>

          <!-- PERLU TINDAK LANJUT -->
          <div class="card mb-4 border-danger-subtle">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                <h6 class="mb-0"><i class="fas fa-exclamation-triangle text-danger me-1"></i> Perlu Tindak Lanjut <span class="badge bg-danger ms-1" id="tindakCount">0</span></h6>
                <small class="text-muted">Antrean belum dilaporkan hadir/tidak hadir, booking Mobile JKN tidak check-in, kunjungan sakit tanpa antrean online, dan obat yang belum dikirim ke PCare.</small>
              </div>
              <div class="ma-chips d-flex flex-wrap gap-1 mb-2" id="maTindakChips"></div>
              <div class="table-responsive">
                <table class="table table-sm align-middle w-100" id="maTindakTable">
                  <thead class="table-light">
                    <tr>
                      <th>Tanggal</th>
                      <th>No. Antrean</th>
                      <th>Pasien</th>
                      <th>Poli</th>
                      <th>Dokter</th>
                      <th>Masalah</th>
                      <th>Obat PCare</th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- REKAP PER POLI / DOKTER / HARI -->
          <div class="card mb-4">
            <div class="card-body">
              <ul class="nav nav-tabs mb-3" role="tablist">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabPoli" type="button">Per Poli</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabDokter" type="button">Per Dokter</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabHari" type="button">Per Hari</button></li>
              </ul>
              <div class="tab-content">
                <?php foreach (['Poli' => 'Poli', 'Dokter' => 'Dokter', 'Hari' => 'Tanggal'] as $id => $judul): ?>
                  <div class="tab-pane fade <?= $id === 'Poli' ? 'show active' : '' ?>" id="tab<?= $id ?>">
                    <div class="table-responsive">
                      <table class="table table-sm align-middle w-100 ma-rekap" id="maRekap<?= $id ?>">
                        <thead class="table-light">
                          <tr>
                            <th><?= $judul ?></th>
                            <th class="text-end">Dilayani</th>
                            <th class="text-end">% Online</th>
                            <th class="text-end">Mobile JKN</th>
                            <th class="text-end">Onsite</th>
                            <th class="text-end">Tanpa Antrol</th>
                            <th class="text-end">Hadir</th>
                            <th class="text-end">Batal</th>
                            <th class="text-end">Belum Lapor</th>
                            <th class="text-end">Median Tunggu</th>
                          </tr>
                        </thead>
                        <tbody></tbody>
                      </table>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>

          <!-- DETAIL -->
          <div class="card mb-4">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                <h6 class="mb-0">Detail Antrean</h6>
                <div class="d-flex gap-2">
                  <select id="maFilterSumber" class="form-select form-select-sm" style="min-width:170px">
                    <option value="">Semua sumber</option>
                    <option value="Mobile JKN">Mobile JKN</option>
                    <option value="Onsite">Onsite</option>
                    <option value="Tanpa Antrol">Tanpa Antrol</option>
                    <option value="Kunjungan Sehat">Kunjungan Sehat</option>
                  </select>
                  <select id="maFilterPanggil" class="form-select form-select-sm" style="min-width:190px">
                    <option value="">Semua status panggil</option>
                  </select>
                  <select id="maFilterObat" class="form-select form-select-sm" style="min-width:190px">
                    <option value="">Semua status obat</option>
                  </select>
                </div>
              </div>
              <div class="table-responsive">
                <table class="table table-sm align-middle w-100" id="maDetailTable">
                  <thead class="table-light">
                    <tr>
                      <th>Tanggal</th>
                      <th>No. Antrean</th>
                      <th>Pasien</th>
                      <th>Poli / Dokter</th>
                      <th>Sumber</th>
                      <th>Add Antrean</th>
                      <th>Status Panggil</th>
                      <th>Obat PCare</th>
                      <th>Ambil</th>
                      <th>Hadir</th>
                      <th>Selesai Periksa</th>
                      <th>Resep</th>
                      <th class="text-end">Tunggu</th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- DEFINISI -->
          <div class="card mb-4">
            <div class="card-body">
              <a class="fw-semibold text-decoration-none" data-bs-toggle="collapse" href="#maDefinisi"><i class="fas fa-info-circle me-1"></i> Cara membaca indikator</a>
              <div class="collapse mt-3" id="maDefinisi">
                <dl class="ma-def row mb-0">
                  <dt class="col-md-3">Pemanfaatan Antrean Online</dt>
                  <dd class="col-md-9">Kunjungan sakit BPJS yang dilayani dan masuk antrean online BPJS (Mobile JKN atau onsite yang dikirim lewat <code>antrean/add</code>), dibagi seluruh kunjungan sakit BPJS yang dilayani. Kunjungan sehat tidak dihitung.</dd>
                  <dt class="col-md-3">Lewat Mobile JKN</dt>
                  <dd class="col-md-9">Bagian dari kunjungan sakit yang dilayani dan berasal dari booking Mobile JKN.</dd>
                  <dt class="col-md-3">Status Panggil Terlapor</dt>
                  <dd class="col-md-9">Antrean online yang statusnya sudah final (hadir atau batal/tidak hadir), dibanding antrean yang seharusnya sudah final (termasuk yang belum dilaporkan dan booking yang tidak check-in di hari yang sudah lewat). Antrean hari ini yang masih menunggu tidak dihitung.</dd>
                  <dt class="col-md-3">Waktu Hadir → Selesai Periksa</dt>
                  <dd class="col-md-9">Dari jam check-in/pendaftaran sampai kunjungan PCare tersimpan. Nilai di luar 0–12 jam diabaikan.</dd>
                  <dt class="col-md-3">Obat PCare</dt>
                  <dd class="col-md-9"><b>Terkirim</b> = semua resep kunjungan sudah dikirim (status tiket bukan "Belum Dikirim"). Tombol <b>Kirim ke PCare</b> memakai proses yang sama dengan tombol Kirim Obat di halaman permintaan farmasi: obat dari semua resep digabung lalu dikirim ke kunjungan PCare. Pengiriman butuh kunjungan PCare yang sudah tersimpan dan resep yang berisi obat.</dd>
                  <dt class="col-md-3">Sumber status</dt>
                  <dd class="col-md-9">Respons BPJS tidak disimpan, jadi status disimpulkan dari data sistem: antrean onsite hanya tersimpan bila <code>antrean/add</code> berhasil, dan status hadir dari data panggil/check-in antrean poli.</dd>
                </dl>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
  <?php require 'library.php'; ?>
  <script>
    (function() {
      const api = 'controller/dashboard/antreanMonitoringController.php';
      const isSuper = <?= $isSuper ? 'true' : 'false' ?>;
      const $faskes = $('#maFaskes');
      const n = v => v == null ? '-' : Number(v).toLocaleString('id-ID');
      const pct = v => v == null ? '-' : Number(v).toLocaleString('id-ID', {
        maximumFractionDigits: 1
      }) + '%';
      const mnt = v => v == null ? '-' : Number(v).toLocaleString('id-ID', {
        maximumFractionDigits: 1
      }) + ' mnt';
      const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
      }[c]));
      const jam = v => v ? esc(String(v).slice(11, 16) || v) : '-';
      const tgl = v => v ? new Date(v + 'T00:00:00').toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short'
      }) : '-';
      const lang = {
        search: 'Cari:',
        lengthMenu: 'Tampilkan _MENU_',
        info: '_START_–_END_ dari _TOTAL_',
        infoEmpty: '0 data',
        infoFiltered: '(dari _MAX_)',
        emptyTable: 'Tidak ada data.',
        zeroRecords: 'Tidak ditemukan.',
        paginate: {
          next: '›',
          previous: '‹'
        }
      };

      const SUMBER = {
        mobile: ['Mobile JKN', 'bg-info-subtle text-info'],
        onsite: ['Onsite', 'bg-primary-subtle text-primary'],
        sehat: ['Kunjungan Sehat', 'bg-secondary-subtle text-secondary'],
        lain: ['Tanpa Antrol', 'bg-danger-subtle text-danger']
      };
      const ADD = {
        mobile: ['Tercatat (Mobile JKN)', 'bg-success-subtle text-success'],
        terkirim: ['Terkirim', 'bg-success-subtle text-success'],
        nonaktif: ['Tidak terkirim (antrol nonaktif)', 'bg-warning-subtle text-warning'],
        tidak_wajib: ['Tidak wajib', 'bg-secondary-subtle text-secondary'],
        tidak_dikirim: ['Tidak dikirim', 'bg-danger-subtle text-danger']
      };
      const PANGGIL = {
        hadir: ['Hadir', 'bg-success-subtle text-success'],
        batal: ['Batal / Tidak hadir', 'bg-secondary-subtle text-secondary'],
        belum_lapor: ['Belum dilaporkan', 'bg-danger-subtle text-danger'],
        tidak_checkin: ['Booking tidak check-in', 'bg-danger-subtle text-danger'],
        menunggu: ['Menunggu', 'bg-warning-subtle text-warning'],
        belum_checkin: ['Belum check-in', 'bg-warning-subtle text-warning'],
        tanpa_antrean: ['Tidak ada data antrean', 'bg-light text-muted']
      };
      const OBAT = {
        terkirim: ['Terkirim', 'bg-success-subtle text-success'],
        belum: ['Belum dikirim', 'bg-danger-subtle text-danger'],
        belum_pcare: ['Belum dikirim, kunjungan PCare belum ada', 'bg-warning-subtle text-warning'],
        tiket_kosong: ['Resep belum berisi obat', 'bg-warning-subtle text-warning'],
        tidak_ada: ['Tidak ada resep', 'bg-light text-muted'],
        batal: ['-', 'bg-light text-muted']
      };
      let bisaKirim = false;
      const obatCell = (r, t) => {
        if (t !== 'display') return (OBAT[r.status_obat] || ['-'])[0];
        let html = badge(OBAT, r.status_obat);
        if (r.status_obat === 'belum' && bisaKirim) {
          html += `<button type="button" class="btn btn-sm btn-success ms-1 py-0 px-2 ma-kirim-obat" data-visit="${esc(r.visit_ID)}" data-ids="${esc((r.tiket_belum || []).join(','))}" data-pasien="${esc(r.pasien || '')}" title="Kirim obat ke PCare"><i class="ti ti-send"></i> Kirim ke PCare</button>`;
        }
        return html;
      };
      const badge = (map, key) => key && map[key] ?
        `<span class="badge ma-badge ${map[key][1]}">${map[key][0]}</span>` : '<span class="text-muted">-</span>';

      Object.entries(PANGGIL).forEach(([k, v]) => $('#maFilterPanggil').append(new Option(v[0], v[0])));
      Object.entries(OBAT).filter(([k]) => k !== 'batal').forEach(([k, v]) => $('#maFilterObat').append(new Option(v[0], v[0])));

      // ---------- tanggal ----------
      const ymd = d => d.toLocaleDateString('sv-SE', {
        timeZone: 'Asia/Jakarta'
      });
      const today = ymd(new Date());

      function hitungRange(r) {
        r = String(r); // jQuery .data() mengubah "7" menjadi angka 7
        const now = new Date(today + 'T00:00:00');
        let from = new Date(now), to = new Date(now);
        if (r === 'yesterday') { from.setDate(now.getDate() - 1); to = new Date(from); }
        if (r === '7') from.setDate(now.getDate() - 6);
        if (r === 'month') from = new Date(now.getFullYear(), now.getMonth(), 1);
        if (r === 'lastmonth') { from = new Date(now.getFullYear(), now.getMonth() - 1, 1); to = new Date(now.getFullYear(), now.getMonth(), 0); }
        return [ymd(from), ymd(to)];
      }
      function tandaiRange() {
        $('.ma-range').each(function() {
          const [a, b] = hitungRange($(this).data('range'));
          $(this).toggleClass('active', a === $('#maFrom').val() && b === $('#maTo').val());
        });
      }
      function setRange(r) {
        const [a, b] = hitungRange(r);
        $('#maFrom').val(a);
        $('#maTo').val(b);
        tandaiRange();
      }
      // tanggal diubah manual -> muat otomatis (tanpa tombol Tampilkan)
      let tundaTgl;
      $('#maFrom, #maTo').on('change', () => { tandaiRange(); clearTimeout(tundaTgl); tundaTgl = setTimeout(load, 300); });
      $('#maFrom, #maTo').attr('max', today);
      setRange('today');
      $('.ma-range').on('click', function() {
        setRange($(this).data('range'));
        load();
      });

      // ---------- tabel ----------
      const rekapCols = (namaRender) => [{
          data: 'nama',
          render: namaRender
        },
        {
          data: 'dilayani',
          className: 'text-end',
          render: n
        },
        {
          data: 'pct_online',
          className: 'text-end',
          render: (v, t) => t === 'display' ? `<b class="${v == null ? '' : v >= 80 ? 'text-success' : v >= 50 ? 'text-warning' : 'text-danger'}">${pct(v)}</b>` : (v ?? -1)
        },
        {
          data: 'mobile',
          className: 'text-end',
          render: n
        },
        {
          data: 'onsite',
          className: 'text-end',
          render: n
        },
        {
          data: 'tanpa_antrol',
          className: 'text-end',
          render: (v, t) => t === 'display' && v > 0 ? `<span class="text-danger fw-semibold">${n(v)}</span>` : n(v)
        },
        {
          data: 'hadir',
          className: 'text-end',
          render: n
        },
        {
          data: 'batal',
          className: 'text-end',
          render: n
        },
        {
          data: null,
          className: 'text-end',
          render: (r, t) => {
            const v = r.belum_lapor + r.tidak_checkin;
            return t === 'display' && v > 0 ? `<span class="text-danger fw-semibold">${n(v)}</span>` : v;
          }
        },
        {
          data: 'm_hadir_periksa_med',
          className: 'text-end',
          render: (v, t) => t === 'display' ? mnt(v) : (v ?? -1)
        }
      ];
      const opsiRekap = {
        data: [],
        paging: false,
        searching: false,
        info: false,
        language: lang
      };
      const tPoli = $('#maRekapPoli').DataTable({
        ...opsiRekap,
        order: [[1, 'desc']],
        columns: rekapCols(esc)
      });
      const tDokter = $('#maRekapDokter').DataTable({
        ...opsiRekap,
        order: [[1, 'desc']],
        columns: rekapCols(esc)
      });
      const tHari = $('#maRekapHari').DataTable({
        ...opsiRekap,
        order: [[0, 'desc']],
        columns: rekapCols((v, t) => t === 'display' ? new Date(v + 'T00:00:00').toLocaleDateString('id-ID', {
          weekday: 'short',
          day: '2-digit',
          month: 'short',
          year: 'numeric'
        }) : v)
      });

      const tDetail = $('#maDetailTable').DataTable({
        data: [],
        deferRender: true,
        pageLength: 25,
        order: [[0, 'desc'], [9, 'desc']],
        language: lang,
        columns: [{
            data: 'tanggal',
            render: (v, t) => t === 'display' ? tgl(v) : v
          },
          {
            data: 'antrean',
            render: v => `<b>${esc(v || '-')}</b>`
          },
          {
            data: null,
            render: r => `${esc(r.pasien || '-')}<div class="text-muted small">${esc(r.noKartu || '')}</div>`
          },
          {
            data: null,
            render: r => `${esc(r.poli || '-')}<div class="text-muted small">${esc(r.dokter || '')}</div>`
          },
          {
            data: 'sumber',
            render: (v, t) => t === 'display' ? badge(SUMBER, v) : (SUMBER[v] || [v])[0]
          },
          {
            data: 'status_add',
            render: (v, t) => t === 'display' ? badge(ADD, v) : (ADD[v] || [v])[0]
          },
          {
            data: 'status_panggil',
            render: (v, t) => t === 'display' ? badge(PANGGIL, v) : (PANGGIL[v] || ['-'])[0]
          },
          {
            data: null,
            render: obatCell
          },
          {
            data: 'ambil_at',
            render: (v, t) => t === 'display' ? jam(v) : v
          },
          {
            data: 'hadir_at',
            render: (v, t) => t === 'display' ? jam(v) : v
          },
          {
            data: 'periksa_at',
            render: (v, t) => t === 'display' ? jam(v) : v
          },
          {
            data: 'resep_at',
            render: (v, t) => t === 'display' ? jam(v) : v
          },
          {
            data: 'm_hadir_periksa',
            className: 'text-end',
            render: (v, t) => t === 'display' ? mnt(v) : (v ?? -1)
          }
        ]
      });
      $('#maFilterSumber').on('change', function() {
        tDetail.column(4).search(this.value ? '^' + $.fn.dataTable.util.escapeRegex(this.value) + '$' : '', true, false).draw();
      });
      $('#maFilterPanggil').on('change', function() {
        tDetail.column(6).search(this.value ? '^' + $.fn.dataTable.util.escapeRegex(this.value) + '$' : '', true, false).draw();
      });
      $('#maFilterObat').on('change', function() {
        tDetail.column(7).search(this.value ? '^' + $.fn.dataTable.util.escapeRegex(this.value) + '$' : '', true, false).draw();
      });

      const JENIS_MASALAH = { lapor: 'Belum dilaporkan', checkin: 'Booking tidak check-in', antrol: 'Tanpa antrean online', nonaktif: 'Antrol nonaktif', obat: 'Obat belum ke PCare' };
      const MASALAH = r => {
        const m = [];
        if (r.status_panggil === 'belum_lapor') m.push(['Belum dilaporkan hadir / tidak hadir', 'text-danger', 'lapor']);
        if (r.status_panggil === 'tidak_checkin') m.push(['Booking Mobile JKN tidak check-in, belum dibatalkan', 'text-danger', 'checkin']);
        if (r.status_add === 'tidak_dikirim' && r.dilayani) m.push(['Kunjungan sakit tidak masuk antrean online', 'text-warning', 'antrol']);
        if (r.status_add === 'nonaktif' && r.dilayani) m.push(['Antrol belum aktif, antrean tidak dikirim', 'text-warning', 'nonaktif']);
        if (['belum', 'belum_pcare', 'tiket_kosong'].includes(r.status_obat)) m.push(['Obat belum dikirim ke PCare', 'text-warning', 'obat']);
        return m.length ? m : null;
      };
      const tTindak = $('#maTindakTable').DataTable({
        data: [],
        pageLength: 10,
        order: [[0, 'desc']],
        language: lang,
        columns: [{
            data: 'tanggal',
            render: (v, t) => t === 'display' ? tgl(v) : v
          },
          {
            data: 'antrean',
            render: v => `<b>${esc(v || '-')}</b>`
          },
          {
            data: null,
            render: r => `${esc(r.pasien || '-')}<div class="text-muted small">${esc(r.visit_ID)}</div>`
          },
          {
            data: 'poli',
            render: v => esc(v || '-')
          },
          {
            data: 'dokter',
            render: v => esc(v || '-')
          },
          {
            data: null,
            className: 'ma-masalah',
            render: r => (MASALAH(r) || []).map(m => `<div class="${m[1]} fw-semibold">${m[0]}</div>`).join('') || '-'
          },
          {
            data: null,
            render: obatCell
          }
        ]
      });

      // ---------- grafik ----------
      const chart = new Chart(document.getElementById('maChart'), {
        data: {
          labels: [],
          datasets: [{
              type: 'bar',
              label: 'Mobile JKN',
              data: [],
              backgroundColor: '#49beff',
              stack: 's',
              yAxisID: 'y'
            },
            {
              type: 'bar',
              label: 'Onsite (antrol)',
              data: [],
              backgroundColor: '#5d87ff',
              stack: 's',
              yAxisID: 'y'
            },
            {
              type: 'bar',
              label: 'Tanpa antrean online',
              data: [],
              backgroundColor: '#fa896b',
              stack: 's',
              yAxisID: 'y'
            },
            {
              type: 'line',
              label: '% Online',
              data: [],
              borderColor: '#13deb9',
              backgroundColor: '#13deb9',
              tension: .3,
              yAxisID: 'y1'
            }
          ]
        },
        options: {
          maintainAspectRatio: false,
          interaction: {
            mode: 'index',
            intersect: false
          },
          scales: {
            x: {
              stacked: true
            },
            y: {
              stacked: true,
              beginAtZero: true,
              title: {
                display: true,
                text: 'Kunjungan'
              }
            },
            y1: {
              position: 'right',
              min: 0,
              max: 100,
              grid: {
                drawOnChartArea: false
              },
              ticks: {
                callback: v => v + '%'
              }
            }
          }
        }
      });

      // ---------- render ----------
      function setKpi(id, value, sub, bar) {
        $('#' + id).text(value);
        $('#' + id + 'Sub').html(sub);
        if (bar !== undefined) $('#' + id + 'Bar').css('width', Math.min(100, bar || 0) + '%');
      }

      function render(d) {
        const k = d.kpi;
        const c = d.config;
        bisaKirim = !!c.bisa_kirim;
        $('#maConfig').removeClass('d-none alert-success alert-warning')
          .addClass(c.antrol_aktif ? 'alert-success' : 'alert-warning')
          .html(c.antrol_aktif ?
            `<i class="fas fa-check-circle"></i> Bridging antrean online aktif${c.kode_ppk ? ` • Kode PPK <b>${esc(c.kode_ppk)}</b>` : ''}` :
            `<i class="fas fa-exclamation-triangle"></i> Bridging antrean online <b>belum dikonfigurasi</b> untuk faskes ini. Antrean onsite tidak dikirim ke BPJS.`);

        setKpi('kpiOnline', pct(k.pct_online), `${n(k.online_dilayani)} dari ${n(k.dilayani)} kunjungan sakit dilayani`, k.pct_online);
        setKpi('kpiMobile', pct(k.pct_mobile), `${n(k.mobile_dilayani)} dari ${n(k.dilayani)} kunjungan sakit dilayani`, k.pct_mobile);
        setKpi('kpiTerlapor', pct(k.pct_terlapor),
          `Belum dilaporkan <b class="text-danger">${n(k.belum_lapor)}</b> • Booking tidak check-in <b class="text-danger">${n(k.tidak_checkin)}</b>`, k.pct_terlapor);
        setKpi('kpiWaktu', k.m_hadir_periksa_med == null ? '-' : mnt(k.m_hadir_periksa_med),
          `Median • rata-rata ${mnt(k.m_hadir_periksa_avg)} (${n(k.m_hadir_periksa_n)} pasien)<br>Selesai periksa → resep: median ${mnt(k.m_periksa_resep_med)}`);

        $('#cTotal').text(n(k.total));
        $('#cMobile').text(n(k.mobile));
        $('#cOnsite').text(n(k.onsite));
        $('#cTanpa').text(n(k.tanpa_antrol));
        $('#cBatal').text(n(k.batal));
        $('#cSehat').text(n(k.sehat));
        $('#cObat').text(n(k.obat_belum));

        const harian = [...d.harian].sort((a, b) => a.nama.localeCompare(b.nama));
        chart.data.labels = harian.map(h => tgl(h.nama));
        // batang: kunjungan sakit dilayani per sumber
        const perSumber = {};
        d.detail.forEach(r => {
          if (!r.dilayani || r.sumber === 'sehat') return;
          const s = perSumber[r.tanggal] = perSumber[r.tanggal] || {
            mobile: 0,
            onsite: 0,
            lain: 0
          };
          s[r.online ? r.sumber : 'lain']++;
        });
        chart.data.datasets[0].data = harian.map(h => (perSumber[h.nama] || {}).mobile || 0);
        chart.data.datasets[1].data = harian.map(h => (perSumber[h.nama] || {}).onsite || 0);
        chart.data.datasets[2].data = harian.map(h => (perSumber[h.nama] || {}).lain || 0);
        chart.data.datasets[3].data = harian.map(h => h.pct_online);
        chart.update();

        const rekap = arr => arr.filter(r => r.sakit > 0);
        tPoli.clear().rows.add(rekap(d.poli)).draw();
        tDokter.clear().rows.add(rekap(d.dokter)).draw();
        tHari.clear().rows.add(rekap(d.harian)).draw();

        tDetail.clear().rows.add(d.detail).draw();
        tindakSemua = d.detail.filter(r => MASALAH(r));
        $('#tindakCount').text(tindakSemua.length);
        const hitung = {};
        tindakSemua.forEach(r => MASALAH(r).forEach(m => hitung[m[2]] = (hitung[m[2]] || 0) + 1));
        if (filterTindak && !hitung[filterTindak]) filterTindak = '';
        $('#maTindakChips').html(`<button type="button" class="btn ${filterTindak ? '' : 'active'}" data-m="">Semua <span class="badge bg-light text-dark ms-1">${n(tindakSemua.length)}</span></button>` +
          Object.entries(JENIS_MASALAH).filter(([k]) => hitung[k]).map(([k, v]) => `<button type="button" class="btn ${filterTindak === k ? 'active' : ''}" data-m="${k}">${esc(v)} <span class="badge bg-light text-dark ms-1">${n(hitung[k])}</span></button>`).join(''));
        isiTindak();
        grafikTambahan(d);
        const p = d.periode;
        $('#maPeriodeInfo').html(`<i class="far fa-calendar me-1"></i>${tgl(p.from)}${p.from !== p.to ? ' – ' + tgl(p.to) : ''} · ${n(k.total)} kunjungan BPJS rawat jalan`);
      }
      let tindakSemua = [], filterTindak = '';
      function isiTindak() {
        tTindak.clear().rows.add(filterTindak ? tindakSemua.filter(r => MASALAH(r).some(m => m[2] === filterTindak)) : tindakSemua).draw();
      }
      $(document).on('click', '#maTindakChips .btn', function() {
        filterTindak = this.dataset.m;
        $('#maTindakChips .btn').removeClass('active');
        $(this).addClass('active');
        isiTindak();
      });

      // ---------- grafik tambahan ----------
      const chartBaru = (id, cfg) => new Chart(document.getElementById(id), cfg);
      const chPanggil = chartBaru('maChartPanggil', { type: 'doughnut', data: { labels: [], datasets: [{ data: [], backgroundColor: [] }] },
        options: { maintainAspectRatio: false, cutout: '62%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } } } });
      const chTunggu = chartBaru('maChartTunggu', { type: 'bar', data: { labels: ['< 15 mnt', '15–30', '30–60', '1–2 jam', '> 2 jam'], datasets: [{ label: 'Pasien', data: [], backgroundColor: ['#13deb9', '#5d87ff', '#ffae1f', '#fa896b', '#ef4444'], borderRadius: 4 }] },
        options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } } });
      const chJam = chartBaru('maChartJam', { type: 'bar', data: { labels: [], datasets: [{ label: 'Hadir', data: [], backgroundColor: '#5d87ff', borderRadius: 3 }] },
        options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } } });
      const WARNA_PANGGIL = { hadir: '#13deb9', batal: '#94a3b8', belum_lapor: '#ef4444', tidak_checkin: '#f97316', menunggu: '#ffae1f', belum_checkin: '#fcd34d', tanpa_antrean: '#cbd5e1' };
      function grafikTambahan(d) {
        const st = {};
        d.detail.forEach(r => { if (r.status_panggil) st[r.status_panggil] = (st[r.status_panggil] || 0) + 1; });
        const kunci = Object.keys(PANGGIL).filter(k => st[k]);
        chPanggil.data.labels = kunci.map(k => PANGGIL[k][0] + ' (' + n(st[k]) + ')');
        chPanggil.data.datasets[0].data = kunci.map(k => st[k]);
        chPanggil.data.datasets[0].backgroundColor = kunci.map(k => WARNA_PANGGIL[k]);
        chPanggil.update();
        const bin = [0, 0, 0, 0, 0];
        d.detail.forEach(r => { const m = r.m_hadir_periksa; if (m == null) return; bin[m < 15 ? 0 : m < 30 ? 1 : m < 60 ? 2 : m < 120 ? 3 : 4]++; });
        chTunggu.data.datasets[0].data = bin;
        chTunggu.update();
        const tot = bin.reduce((a, b) => a + b, 0);
        $('#maTungguInfo').text(tot ? Math.round((bin[0] + bin[1]) / tot * 100) + '% ≤ 30 menit' : '');
        const jam = Array(24).fill(0);
        d.detail.forEach(r => { if (r.hadir_at && r.dilayani) jam[+String(r.hadir_at).slice(11, 13)]++; });
        let a = jam.findIndex(v => v > 0), z = 23 - [...jam].reverse().findIndex(v => v > 0);
        if (a < 0) { a = 7; z = 17; }
        chJam.data.labels = jam.slice(a, z + 1).map((_, i) => String(a + i).padStart(2, '0'));
        chJam.data.datasets[0].data = jam.slice(a, z + 1);
        chJam.update();
        const puncak = jam.indexOf(Math.max(...jam));
        $('#maJamInfo').text(Math.max(...jam) ? 'Tersibuk ' + String(puncak).padStart(2, '0') + ':00' : '');
      }

      let muatSeq = 0;
      async function load() {
        const ini = ++muatSeq;
        $('#maError').addClass('d-none');
        $('#maBar').css({ opacity: 1, width: '70%' });
        $('.ma-kpi .card, #maChart, #maChartPanggil, #maChartTunggu, #maChartJam').closest('.card').addClass('ma-loading');
        const p = new URLSearchParams({
          action: 'data',
          from: $('#maFrom').val(),
          to: $('#maTo').val()
        });
        if (isSuper && $faskes.val()) p.set('id_customer', $faskes.val());
        $('#maRefresh').prop('disabled', true).find('i').addClass('fa-spin');
        try {
          const r = await fetch(api + '?' + p);
          const j = await r.json();
          if (!r.ok || j.status !== 'success') throw new Error(j.message || 'Gagal memuat data.');
          // detail dikirim ringkas: kolom + baris array
          if (Array.isArray(j.kolom)) j.detail = j.detail.map(a => Object.fromEntries(j.kolom.map((c, i) => [c, a[i]])));
          if (ini !== muatSeq) return;
          render(j);
        } catch (e) {
          $('#maError').text(e.message || 'Gagal terhubung ke server.').removeClass('d-none');
        } finally {
          if (ini === muatSeq) {
            $('#maRefresh').prop('disabled', false).find('i').removeClass('fa-spin');
            $('#maBar').css('width', '100%');
            setTimeout(() => $('#maBar').css({ opacity: 0, width: 0 }), 300);
            $('.ma-loading').removeClass('ma-loading');
          }
        }
      }

      $('#maRefresh').on('click', load);

      // 📤 kirim obat ke PCare (proses sama dengan tombol Kirim Obat di permintaan farmasi)
      $(document).on('click', '.ma-kirim-obat', function() {
        const ids = String($(this).data('ids') || '').split(',').filter(Boolean);
        const pasien = $(this).data('pasien');
        if (!ids.length) return;
        Swal.fire({
          title: 'Kirim obat ke PCare?',
          html: `${esc(pasien)}<br><small class="text-muted">${ids.length} resep belum dikirim akan digabung dengan resep yang sudah terkirim.</small>`,
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Ya, kirim',
          cancelButtonText: 'Batal',
          reverseButtons: true
        }).then(res => {
          if (!res.isConfirmed) return;
          Swal.fire({
            title: 'Mengirim ke PCare...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
          });
          fetch('controller/farmasi/kirimObat.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify({
                ids
              })
            })
            .then(r => r.json().catch(() => ({
              status: 'error',
              message: 'Respons server tidak valid.'
            })))
            .then(r => {
              if (r.status === 'success') {
                Swal.fire('Berhasil!', `${r.total || ids.length} resep berhasil dikirim ke PCare.`, 'success');
                load();
              } else {
                Swal.fire('Gagal!', r.message || 'Obat gagal dikirim.', 'error');
              }
            })
            .catch(() => Swal.fire('Gagal!', 'Terjadi kesalahan pada server.', 'error'));
        });
      });

      if (isSuper) {
        fetch(api + '?action=faskes').then(r => r.json()).then(j => {
          (j.data || []).forEach(f => {
            const opt = new Option(f.clinic_name || ('#' + f.id_customer), f.id_customer);
            opt.dataset.off = +f.antrol_aktif ? '' : '1';
            $faskes.append(opt);
          });
          $faskes.val('<?= (int) ($_SESSION['id_customer'] ?? 0) ?>');
          if (!$faskes.val()) $faskes.prop('selectedIndex', 0);
          const fmtFaskes = o => {
            if (!o.id || !o.element || !o.element.dataset.off) return o.text;
            return $('<span>').text(o.text + ' ').append($('<span class="ma-off">').text('(antrol nonaktif)'));
          };
          if ($.fn.select2) {
            $faskes.select2({
              width: '100%',
              placeholder: 'Cari faskes...',
              dropdownCssClass: 'ma-faskes-dd',
              templateResult: fmtFaskes,
              templateSelection: fmtFaskes,
              language: { noResults: () => 'Faskes tidak ditemukan', searching: () => 'Mencari...' }
            });
          }
          $faskes.on('change', load);
          load();
        }).catch(load);
      } else {
        load();
      }
    })();
  </script>
</body>

</html>
