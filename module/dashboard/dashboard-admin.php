<?php
/**
 * DASHBOARD ADMIN (dimuat oleh module/admin/index.php untuk role admin)
 *  - Panel masa kontrak faskes   : controller/dashboard/contractReminderController.php (tidak diubah)
 *  - Statistik & operasional     : controller/dashboard/adminDashboard.php
 */
?>
<style>
  /* =========================================================
     REMINDER MASA KONTRAK
  ========================================================== */

  .admin-dashboard .contract-reminder-wrapper {
    background: #fff;
    border: 1px solid var(--ad-border);
    border-radius: 18px;
    overflow: hidden;
  }

  .admin-dashboard .contract-reminder-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    padding: 17px 20px;
    border-bottom: 1px solid var(--ad-border);
  }

  .admin-dashboard .contract-reminder-title {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .admin-dashboard .contract-reminder-icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: #eeecff;
    color: #635bff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 21px;
    flex-shrink: 0;
  }

  .admin-dashboard .contract-reminder-heading {
    font-size: 13px;
    font-weight: 700;
    color: var(--ad-text);
  }

  .admin-dashboard .contract-reminder-description {
    font-size: 11px;
    color: var(--ad-muted);
    margin-top: 2px;
  }

  .admin-dashboard .contract-reminder-body {
    padding: 17px 20px;
  }

  .admin-dashboard .contract-info-card,
  .admin-dashboard .contract-countdown-card {
    height: 100%;
    border: 1px solid var(--ad-border);
    border-radius: 14px;
    padding: 15px;
    background: #fafbfc;
  }

  .admin-dashboard .contract-info-label {
    font-size: 10px;
    color: var(--ad-muted);
    font-weight: 600;
    margin-bottom: 6px;
  }

  .admin-dashboard .contract-info-value {
    font-size: 15px;
    line-height: 1.35;
    font-weight: 700;
    color: var(--ad-text);
  }

  .admin-dashboard .contract-info-sub {
    margin-top: 5px;
    font-size: 11px;
    color: var(--ad-muted);
  }

  .admin-dashboard .contract-countdown-card {
    background: linear-gradient(135deg, #f8f7ff, #ffffff);
    border-color: #e5e2ff;
  }

  .admin-dashboard .contract-countdown-title {
    font-size: 11px;
    color: var(--ad-muted);
    font-weight: 600;
    margin-bottom: 9px;
  }

  .admin-dashboard .contract-countdown {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
  }

  .admin-dashboard .countdown-item {
    min-width: 54px;
    text-align: center;
  }

  .admin-dashboard .countdown-value {
    font-size: 24px;
    line-height: 1;
    font-weight: 800;
    color: var(--ad-primary);
  }

  .admin-dashboard .countdown-label {
    margin-top: 5px;
    font-size: 9px;
    color: var(--ad-muted);
    text-transform: uppercase;
    letter-spacing: .35px;
  }

  .admin-dashboard .countdown-separator {
    font-size: 18px;
    font-weight: 700;
    color: #adb5bd;
    margin-top: -12px;
  }

  .admin-dashboard .contract-summary {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
  }

  .admin-dashboard .contract-summary-item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 10px;
    border-radius: 8px;
    background: #f8f9fa;
    font-size: 11px;
    color: var(--ad-muted);
  }

  .admin-dashboard .contract-summary-item strong {
    color: var(--ad-text);
    font-weight: 700;
  }

  .admin-dashboard .contract-summary-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    display: inline-block;
    flex-shrink: 0;
  }

  @media (max-width: 767px) {
    .admin-dashboard .contract-reminder-header {
      align-items: flex-start;
    }

    .admin-dashboard .contract-countdown {
      gap: 4px;
    }

    .admin-dashboard .countdown-item {
      min-width: 46px;
    }

    .admin-dashboard .countdown-value {
      font-size: 20px;
    }

    .admin-dashboard .countdown-separator {
      font-size: 15px;
    }
  }
</style>

<!-- =========================================================
     REMINDER MASA KONTRAK FASKES
========================================================== -->
<div class="admin-dashboard mb-3">

  <div class="contract-reminder-wrapper">

    <div class="contract-reminder-header">

      <div class="contract-reminder-title">

        <div class="contract-reminder-icon">
          <iconify-icon icon="solar:document-text-bold"></iconify-icon>
        </div>

        <div>
          <div class="contract-reminder-heading">
            Masa Kontrak Faskes
          </div>

          <div class="contract-reminder-description">
            Monitoring masa berlaku kontrak dan pengingat perpanjangan
          </div>
        </div>

      </div>

      <div id="contractReminderStatus">
        <span class="badge bg-secondary-subtle text-secondary">
          Memuat...
        </span>
      </div>

    </div>

    <div class="contract-reminder-body">

      <div class="row g-3">

        <div class="col-lg-4">
          <div class="contract-info-card">
            <div class="contract-info-label">Faskes</div>

            <div class="contract-info-value" id="contractFaskes">
              Memuat...
            </div>

            <div class="contract-info-sub" id="contractNumber">
              -
            </div>
          </div>
        </div>

        <div class="col-lg-3">
          <div class="contract-info-card">
            <div class="contract-info-label">Periode Kontrak</div>

            <div class="contract-info-value" id="contractPeriod">
              -
            </div>

            <div class="contract-info-sub" id="contractEndDate">
              -
            </div>
          </div>
        </div>

        <div class="col-lg-5">
          <div class="contract-countdown-card">

            <div class="contract-countdown-title">
              <iconify-icon
                icon="solar:alarm-bold"
                class="me-1">
              </iconify-icon>
              Sisa Masa Kontrak
            </div>

            <div class="contract-countdown" id="contractCountdown">

              <div class="countdown-item">
                <div class="countdown-value" id="contractYears">00</div>
                <div class="countdown-label">Tahun</div>
              </div>

              <div class="countdown-separator">:</div>

              <div class="countdown-item">
                <div class="countdown-value" id="contractMonths">00</div>
                <div class="countdown-label">Bulan</div>
              </div>

              <div class="countdown-separator">:</div>

              <div class="countdown-item">
                <div class="countdown-value" id="contractDays">00</div>
                <div class="countdown-label">Hari</div>
              </div>

              <div class="countdown-separator">:</div>

              <div class="countdown-item">
                <div class="countdown-value" id="contractHours">00</div>
                <div class="countdown-label">Jam</div>
              </div>

            </div>

          </div>
        </div>

      </div>



    </div>

  </div>

</div>

<!-- =========================================================
     DASHBOARD ADMIN — statistik & operasional
     Data: controller/dashboard/adminDashboard.php (?bagian=statistik | operasional)
========================================================== -->
<style>
  .adb { --adb-c1: #2a78d6; --adb-c2: #eb6834; --adb-c3: #1baf7a; --adb-c4: #eda100; --adb-muted: #94a3b8; --adb-line: #e7ebf0; --adb-text: #0f172a; --adb-sub: #475569; }
  .adb .card-x { background: #fff; border: 1px solid var(--adb-line); border-radius: 16px; padding: 16px 18px; height: 100%; }
  .adb .judul { font-size: .95rem; font-weight: 700; color: var(--adb-text); margin: 0; }
  .adb .sub { font-size: .76rem; color: var(--adb-sub); }
  .adb .kepala { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; margin-bottom: 10px; }
  .adb-filter { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 10px; }
  .adb-filter label { font-size: .72rem; color: var(--adb-sub); display: block; margin-bottom: 2px; }
  .adb-filter .form-control, .adb-filter .form-select { font-size: .84rem; height: 34px; padding-top: 4px; padding-bottom: 4px; }
  .adb-cepat .btn { font-size: .78rem; padding: 6px 10px; background: #fff; border: 1px solid #cbd5e1; color: #1f2937; font-weight: 600; }
  .adb-cepat .btn:hover { background: #f1f5f9; }
  .adb-cepat .btn.active, .adb-cepat .btn.active:hover { background: #1e293b; border-color: #1e293b; color: #fff; }
  .adb-kpi { background: #fff; border: 1px solid var(--adb-line); border-radius: 14px; padding: 12px 14px; height: 100%; }
  .adb-kpi .l { font-size: .75rem; color: var(--adb-sub); font-weight: 600; display: flex; align-items: center; gap: 6px; }
  .adb-kpi .l i { font-size: 1rem; color: #64748b; }
  .adb-kpi .n { font-size: 1.45rem; font-weight: 800; color: var(--adb-text); line-height: 1.2; margin-top: 4px; font-variant-numeric: tabular-nums; }
  .adb-kpi .d { font-size: .74rem; color: var(--adb-sub); margin-top: 2px; }
  .adb-delta { font-weight: 700; border-radius: 6px; padding: 0 5px; }
  .adb-delta.naik { color: #166534; background: #dcfce7; }
  .adb-delta.turun { color: #991b1b; background: #fee2e2; }
  .adb-delta.tetap { color: #475569; background: #f1f5f9; }
  .adb-insight { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 8px; }
  .adb-insight div { background: #f8fafc; border: 1px solid var(--adb-line); border-radius: 12px; padding: 9px 12px; font-size: .82rem; color: #1f2937; display: flex; gap: 8px; align-items: flex-start; }
  .adb-insight i { color: var(--adb-c1); font-size: 1rem; margin-top: 1px; }
  .adb-bar { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 2px 10px; align-items: center; font-size: .82rem; padding: 5px 0; }
  .adb-bar + .adb-bar { border-top: 1px dashed #eef1f5; }
  .adb-bar .nm { color: #1f2937; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .adb-bar .vl { color: var(--adb-text); font-weight: 700; font-variant-numeric: tabular-nums; text-align: right; }
  .adb-bar .tr { grid-column: 1 / -1; height: 6px; background: #f1f5f9; border-radius: 4px; overflow: hidden; }
  .adb-bar .tr span { display: block; height: 100%; background: var(--adb-c1); border-radius: 4px; }
  .adb-bar .kc { color: var(--adb-sub); font-weight: 400; font-size: .74rem; }
  .adb-leg { display: flex; flex-wrap: wrap; gap: 4px 12px; font-size: .78rem; color: var(--adb-sub); }
  .adb-leg span::before { content: ""; display: inline-block; width: 10px; height: 10px; border-radius: 3px; margin-right: 5px; vertical-align: -1px; background: var(--w); }
  .adb-tbl { width: 100%; font-size: .82rem; }
  .adb-tbl th { font-size: .7rem; text-transform: uppercase; letter-spacing: .03em; color: var(--adb-sub); font-weight: 600; padding: 6px 4px; border-bottom: 1px solid var(--adb-line); }
  .adb-tbl td { padding: 6px 4px; border-bottom: 1px dashed #eef1f5; vertical-align: middle; }
  .adb-tbl .num { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
  .adb-st { display: inline-flex; align-items: center; gap: 4px; font-size: .72rem; font-weight: 700; border-radius: 6px; padding: 1px 7px; }
  .adb-st.merah { background: #fee2e2; color: #991b1b; }
  .adb-st.kuning { background: #fef3c7; color: #78350f; }
  .adb-st.hijau { background: #dcfce7; color: #166534; }
  .adb-st.abu { background: #f1f5f9; color: #334155; }
  .adb-kosong { color: var(--adb-sub); font-size: .82rem; text-align: center; padding: 18px 8px; }
  .adb-quick { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 8px; }
  .adb-quick a { display: flex; align-items: center; gap: 8px; border: 1px solid var(--adb-line); border-radius: 12px; padding: 10px 12px; color: #1f2937; font-weight: 600; font-size: .84rem; text-decoration: none; background: #fff; }
  .adb-quick a:hover { border-color: #635bff; color: #635bff; }
  .adb-quick i { font-size: 1.15rem; color: #635bff; }
  .adb .chart-box { position: relative; height: 260px; }
  .adb .chart-box.kecil { height: 200px; }
  .adb-memuat { opacity: .55; transition: opacity .15s; pointer-events: none; }
  @keyframes adb-spin { to { transform: rotate(360deg); } }
  .adb .spin { display: inline-block; animation: adb-spin 1s linear infinite; }
</style>

<div class="adb" id="adbRoot">
  <!-- FILTER -->
  <div class="card-x mb-3" style="height:auto">
    <div class="adb-filter">
      <div>
        <label>Periode</label>
        <div class="btn-group adb-cepat" id="adbCepat">
          <button type="button" class="btn" data-r="hari">Hari ini</button>
          <button type="button" class="btn" data-r="7">7 hari</button>
          <button type="button" class="btn" data-r="30">30 hari</button>
          <button type="button" class="btn" data-r="bulan">Bulan ini</button>
          <button type="button" class="btn" data-r="bulanlalu">Bulan lalu</button>
          <button type="button" class="btn" data-r="tahun">Tahun ini</button>
        </div>
      </div>
      <div><label for="adbDari">Dari</label><input type="date" id="adbDari" class="form-control"></div>
      <div><label for="adbSampai">Sampai</label><input type="date" id="adbSampai" class="form-control"></div>
      <div><label for="adbPoli">Poli</label><select id="adbPoli" class="form-select" style="min-width:150px"><option value="">Semua poli</option></select></div>
      <div><label for="adbPenjamin">Penjamin</label><select id="adbPenjamin" class="form-select" style="min-width:160px"><option value="">Semua penjamin</option></select></div>
      <button type="button" class="btn btn-dark btn-sm" id="adbTerapkan" style="height:34px"><i class="ti ti-filter me-1"></i>Terapkan</button>
      <button type="button" class="btn btn-light btn-sm" id="adbReset" style="height:34px" title="Kembalikan filter"><i class="ti ti-arrow-back-up"></i></button>
      <div class="ms-auto text-end sub" style="min-width:180px">
        <div id="adbRentang">-</div>
        <div><span id="adbBanding">-</span></div>
        <div id="adbUpdate">memuat…</div>
      </div>
    </div>
  </div>

  <div id="adbStat">
    <!-- KPI -->
    <div class="row g-3 mb-3">
      <div class="col-6 col-md-4 col-xxl-2"><div class="adb-kpi"><div class="l"><i class="ti ti-users"></i>Kunjungan</div><div class="n" id="kKunjungan">-</div><div class="d" id="kKunjunganD">&nbsp;</div></div></div>
      <div class="col-6 col-md-4 col-xxl-2"><div class="adb-kpi"><div class="l"><i class="ti ti-user-check"></i>Pasien unik</div><div class="n" id="kPasien">-</div><div class="d" id="kPasienD">&nbsp;</div></div></div>
      <div class="col-6 col-md-4 col-xxl-2"><div class="adb-kpi"><div class="l"><i class="ti ti-cash"></i>Pendapatan diterima</div><div class="n" id="kPendapatan">-</div><div class="d" id="kPendapatanD">&nbsp;</div></div></div>
      <div class="col-6 col-md-4 col-xxl-2"><div class="adb-kpi"><div class="l"><i class="ti ti-calendar-stats"></i>Rata-rata / hari</div><div class="n" id="kRata">-</div><div class="d" id="kRataD">&nbsp;</div></div></div>
      <div class="col-6 col-md-4 col-xxl-2"><div class="adb-kpi"><div class="l"><i class="ti ti-circle-check"></i>Selesai dilayani</div><div class="n" id="kSelesai">-</div><div class="d" id="kSelesaiD">&nbsp;</div></div></div>
      <div class="col-6 col-md-4 col-xxl-2"><div class="adb-kpi"><div class="l"><i class="ti ti-circle-x"></i>Kunjungan batal</div><div class="n" id="kBatal">-</div><div class="d" id="kBatalD">&nbsp;</div></div></div>
    </div>

    <!-- INSIGHT -->
    <div class="card-x mb-3" style="height:auto">
      <div class="kepala"><div><h6 class="judul"><i class="ti ti-bulb me-1" style="color:#eda100"></i>Insight periode ini</h6><div class="sub">Dihitung otomatis dari data kunjungan</div></div></div>
      <div class="adb-insight" id="adbInsight"><div>Memuat…</div></div>
    </div>

    <!-- TREN + PENJAMIN -->
    <div class="row g-3 mb-3">
      <div class="col-xl-8">
        <div class="card-x">
          <div class="kepala">
            <div><h6 class="judul">Tren kunjungan</h6><div class="sub" id="trenSub">Kunjungan per hari</div></div>
            <div class="adb-leg"><span style="--w:var(--adb-c1)">Periode ini</span><span style="--w:var(--adb-muted)">Periode sebelumnya</span></div>
          </div>
          <div class="chart-box"><canvas id="cTren" role="img" aria-label="Grafik tren kunjungan"></canvas></div>
        </div>
      </div>
      <div class="col-xl-4">
        <div class="card-x">
          <div class="kepala"><div><h6 class="judul">Penjamin</h6><div class="sub">Kunjungan &amp; pendapatan per penjamin</div></div></div>
          <div class="chart-box kecil"><canvas id="cPenjamin" role="img" aria-label="Komposisi penjamin"></canvas></div>
          <div id="lPenjamin" class="mt-2"></div>
        </div>
      </div>
    </div>

    <!-- JAM / HARI / DEMOGRAFI -->
    <div class="row g-3 mb-3">
      <div class="col-xl-4 col-md-6">
        <div class="card-x">
          <div class="kepala"><div><h6 class="judul">Jam sibuk</h6><div class="sub" id="jamSub">Kunjungan per jam daftar</div></div></div>
          <div class="chart-box kecil"><canvas id="cJam" role="img" aria-label="Kunjungan per jam"></canvas></div>
        </div>
      </div>
      <div class="col-xl-4 col-md-6">
        <div class="card-x">
          <div class="kepala"><div><h6 class="judul">Hari dalam seminggu</h6><div class="sub">Rata-rata kunjungan per hari</div></div></div>
          <div class="chart-box kecil"><canvas id="cHari" role="img" aria-label="Rata-rata kunjungan per hari"></canvas></div>
        </div>
      </div>
      <div class="col-xl-4">
        <div class="card-x">
          <div class="kepala"><div><h6 class="judul">Demografi pasien</h6><div class="sub" id="demoSub">Pasien unik periode ini</div></div></div>
          <div id="lGender" class="mb-2"></div>
          <div id="lUmur"></div>
        </div>
      </div>
    </div>

    <!-- DOKTER / DIAGNOSA / POLI+METODE -->
    <div class="row g-3 mb-3">
      <div class="col-xl-4">
        <div class="card-x">
          <div class="kepala"><div><h6 class="judul">Dokter teratas</h6><div class="sub" id="dokSub">Berdasarkan jumlah kunjungan</div></div></div>
          <div id="lDokter"></div>
        </div>
      </div>
      <div class="col-xl-4">
        <div class="card-x">
          <div class="kepala"><div><h6 class="judul">10 diagnosa terbanyak</h6><div class="sub" id="diagSub">Diagnosa utama (ICD-10)</div></div>
            <a href="module/admin/rpt_diagnosis" class="small text-decoration-none">Laporan</a></div>
          <div id="lDiag"></div>
        </div>
      </div>
      <div class="col-xl-4">
        <div class="card-x">
          <div class="kepala"><div><h6 class="judul">Poli</h6><div class="sub">Kunjungan per poli</div></div></div>
          <div id="lPoli" class="mb-3"></div>
          <div class="kepala mb-1"><div><h6 class="judul">Pembayaran diterima</h6><div class="sub">Per metode bayar</div></div></div>
          <div id="lMetode"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- OPERASIONAL HARI INI -->
  <div class="row g-3 mb-3" id="adbOps">
    <div class="col-xl-5">
      <div class="card-x">
        <div class="kepala"><div><h6 class="judul">Hari ini per poli</h6><div class="sub" id="opsSub">Status kunjungan hari ini</div></div>
          <a href="module/doctor/pemeriksaan" class="small text-decoration-none">Pemeriksaan</a></div>
        <div id="lOpsRingkas" class="d-flex flex-wrap gap-1 mb-2"></div>
        <div id="lOpsPoli"><div class="adb-kosong">Memuat…</div></div>
      </div>
    </div>
    <div class="col-xl-3 col-md-6">
      <div class="card-x">
        <div class="kepala"><div><h6 class="judul">Bed rawat inap</h6><div class="sub">Keterisian saat ini</div></div>
          <a href="module/admin/rawat_inap" class="small text-decoration-none">Rawat inap</a></div>
        <div id="lBed"><div class="adb-kosong">Memuat…</div></div>
      </div>
    </div>
    <div class="col-xl-4 col-md-6">
      <div class="card-x">
        <div class="kepala"><div><h6 class="judul">Stok obat kritis</h6><div class="sub" id="stokSub">Habis / di bawah minimum</div></div>
          <a href="module/admin/rpt_farmasi" class="small text-decoration-none">Laporan stok</a></div>
        <div id="lStok"><div class="adb-kosong">Memuat…</div></div>
      </div>
    </div>
  </div>

  <!-- AKSES CEPAT -->
  <div class="card-x mb-3" style="height:auto">
    <div class="kepala"><div><h6 class="judul">Akses cepat</h6></div></div>
    <div class="adb-quick">
      <a href="module/admisi/pendaftaran"><i class="ti ti-user-plus"></i>Pendaftaran</a>
      <a href="module/doctor/pemeriksaan"><i class="ti ti-stethoscope"></i>Pemeriksaan</a>
      <a href="module/pharmacy/permintaan-resep"><i class="ti ti-pill"></i>Permintaan resep</a>
      <a href="module/lab/permintaan-lab"><i class="ti ti-microscope"></i>Permintaan lab</a>
      <a href="module/admin/kasir"><i class="ti ti-cash"></i>Kasir</a>
      <a href="module/admin/rawat_inap"><i class="ti ti-bed"></i>Rawat inap</a>
    </div>
  </div>
</div>

<script>
  (function() {
    "use strict";
    const API = 'controller/dashboard/adminDashboard.php';
    const KUNCI = 'dashboardAdmin.filter';
    const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const HARI = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
    const HARI_PANJANG = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
    const WARNA = ['#2a78d6', '#eb6834', '#1baf7a', '#eda100'];   // slot kategorikal tetap (tervalidasi CVD); ke-5+ dilipat ke "Lainnya"
    const ABU = '#94a3b8';
    const HARI_INI = new Date().toLocaleDateString('sv-SE', { timeZone: 'Asia/Jakarta' });
    const $ = id => document.getElementById(id);
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);
    const ang = n => Number(n || 0).toLocaleString('id-ID');
    const rp = n => 'Rp' + Number(n || 0).toLocaleString('id-ID');
    const rpPendek = n => n >= 1e9 ? 'Rp' + (n / 1e9).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' M' : n >= 1e6 ? 'Rp' + (n / 1e6).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' jt' : rp(n);
    const persen = (a, b) => b ? Math.round(a / b * 1000) / 10 : 0;
    const pct = (a, b) => persen(a, b).toLocaleString('id-ID');
    const tglID = d => { const [y, m, h] = String(d).split('-'); return `${+h} ${BULAN[+m - 1]} ${y}`; };
    const geser = (ymd, n) => { const d = new Date(ymd + 'T12:00:00'); d.setDate(d.getDate() + n); return d.toISOString().slice(0, 10); };

    // ---------- filter (tersimpan per peramban; periode kembali ke default saat hari berganti) ----------
    function rentangCepat(r) {
      const awalBulan = HARI_INI.slice(0, 8) + '01';
      if (r === 'hari') return [HARI_INI, HARI_INI];
      if (r === '7') return [geser(HARI_INI, -6), HARI_INI];
      if (r === '30') return [geser(HARI_INI, -29), HARI_INI];
      if (r === 'bulan') return [awalBulan, HARI_INI];
      if (r === 'bulanlalu') { const akhir = geser(awalBulan, -1); return [akhir.slice(0, 8) + '01', akhir]; }
      if (r === 'tahun') return [HARI_INI.slice(0, 4) + '-01-01', HARI_INI];
      return null;
    }
    let f = { dari: rentangCepat('bulan')[0], sampai: HARI_INI, poli: '', penjamin: '' };
    try {
      const s = JSON.parse(localStorage.getItem(KUNCI) || 'null');
      if (s && s.f) { f.poli = s.f.poli || ''; f.penjamin = s.f.penjamin || ''; if (s.hari === HARI_INI) { f.dari = s.f.dari || f.dari; f.sampai = s.f.sampai || f.sampai; } }
    } catch (e) {}
    const simpan = () => { try { localStorage.setItem(KUNCI, JSON.stringify({ hari: HARI_INI, f })); } catch (e) {} };
    $('adbDari').value = f.dari; $('adbSampai').value = f.sampai;
    $('adbDari').max = $('adbSampai').max = HARI_INI;
    function tandaiCepat() {
      document.querySelectorAll('#adbCepat .btn').forEach(b => { const r = rentangCepat(b.dataset.r); b.classList.toggle('active', r[0] === f.dari && r[1] === f.sampai); });
    }

    // ---------- chart ----------
    const grafik = {};
    function gambarGrafik(id, cfg) {
      if (typeof Chart === 'undefined') return;
      if (grafik[id]) grafik[id].destroy();
      grafik[id] = new Chart($(id), cfg);
    }
    const sumbu = { grid: { color: '#eef1f5' }, ticks: { color: '#64748b', font: { size: 11 } }, border: { display: false } };
    const tanpaGrid = { grid: { display: false }, ticks: { color: '#64748b', font: { size: 11 } }, border: { display: false } };
    const dasar = { responsive: true, maintainAspectRatio: false, animation: { duration: 250 }, plugins: { legend: { display: false } } };

    function delta(kini, lalu, balik) {
      if (!lalu) return kini ? '<span class="adb-delta tetap">baru</span>' : '';
      const p = Math.round((kini - lalu) / lalu * 1000) / 10;
      const naik = p > 0, kelas = p === 0 ? 'tetap' : ((naik !== !!balik) ? 'naik' : 'turun');
      return `<span class="adb-delta ${kelas}" title="dibanding periode sebelumnya">${p > 0 ? '▲' : p < 0 ? '▼' : '='} ${Math.abs(p).toLocaleString('id-ID')}%</span>`;
    }
    function bar(nama, nilai, maks, teks, kecil, warna) {
      const w = maks ? Math.max(2, Math.round(nilai / maks * 100)) : 0;
      return `<div class="adb-bar"><div class="nm" title="${esc(nama)}">${esc(nama)}${kecil ? ` <span class="kc">${kecil}</span>` : ''}</div><div class="vl">${teks}</div><div class="tr"><span style="width:${w}%;${warna ? 'background:' + warna : ''}"></span></div></div>`;
    }

    // ---------- statistik ----------
    let sibuk = false, opsiSiap = false;
    async function muatStatistik() {
      const d = $('adbDari').value || HARI_INI, s = $('adbSampai').value || d;
      if (s < d) return Swal.fire({ icon: 'warning', title: 'Tanggal tidak valid', text: '"Sampai" tidak boleh sebelum "Dari".' });
      if ((new Date(s) - new Date(d)) / 864e5 > 365) return Swal.fire({ icon: 'warning', title: 'Rentang terlalu panjang', text: 'Maksimal 366 hari.' });
      if (sibuk) return;
      sibuk = true;
      f.dari = d; f.sampai = s;
      if (opsiSiap) { f.poli = $('adbPoli').value; f.penjamin = $('adbPenjamin').value; }   // muat pertama: pakai filter tersimpan
      simpan(); tandaiCepat();
      $('adbStat').classList.add('adb-memuat');
      $('adbTerapkan').innerHTML = '<i class="ti ti-loader-2 spin me-1"></i>Memuat';
      try {
        const q = new URLSearchParams({ bagian: 'statistik', dari: d, sampai: s, poli: f.poli, penjamin: f.penjamin });
        const r = await fetch(API + '?' + q, { credentials: 'same-origin' });
        const j = await r.json().catch(() => ({}));
        if (!r.ok || j.status !== 'success') throw new Error(j.message || 'Server tidak merespon (' + r.status + ').');
        render(j);
        $('adbUpdate').textContent = 'diperbarui ' + new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
      } catch (e) {
        $('adbUpdate').textContent = 'gagal memuat';
        Swal.fire({ icon: 'error', title: 'Gagal memuat dashboard', text: e.message });
      } finally {
        sibuk = false;
        $('adbStat').classList.remove('adb-memuat');
        $('adbTerapkan').innerHTML = '<i class="ti ti-filter me-1"></i>Terapkan';
      }
    }

    function isiOpsi(sel, daftar, nilai, semua) {
      const ada = daftar.some(o => String(o.id) === nilai);
      $(sel).innerHTML = `<option value="">${semua}</option>` + (nilai && !ada ? `<option value="${esc(nilai)}">${esc(nilai)}</option>` : '') +
        daftar.map(o => `<option value="${esc(o.id)}">${esc(o.nama)}</option>`).join('');
      $(sel).value = nilai;
    }

    function render(j) {
      const k = j.kpi, p = j.sebelum, h = j.rentang.hari;
      isiOpsi('adbPoli', j.opsi.poli, f.poli, 'Semua poli');
      isiOpsi('adbPenjamin', j.opsi.penjamin, f.penjamin, 'Semua penjamin');
      opsiSiap = true;
      $('adbRentang').textContent = j.rentang.dari === j.rentang.sampai ? tglID(j.rentang.dari) : `${tglID(j.rentang.dari)} – ${tglID(j.rentang.sampai)} (${h} hari)`;
      $('adbBanding').textContent = 'dibanding ' + (p.dari === p.sampai ? tglID(p.dari) : `${tglID(p.dari)} – ${tglID(p.sampai)}`);

      // KPI
      $('kKunjungan').textContent = ang(k.kunjungan);
      $('kKunjunganD').innerHTML = `${delta(k.kunjungan, p.kunjungan)} ${k.rawat_inap ? '· ' + ang(k.rawat_inap) + ' rawat inap' : ''}`;
      $('kPasien').textContent = ang(k.pasien_unik);
      $('kPasienD').innerHTML = `${delta(k.pasien_unik, p.pasien_unik)} · ${ang(k.pasien_baru)} pasien baru`;
      $('kPendapatan').textContent = rpPendek(k.pendapatan);
      $('kPendapatan').title = rp(k.pendapatan);
      $('kPendapatanD').innerHTML = `${delta(k.pendapatan, p.pendapatan)} · ${ang(k.transaksi)} transaksi`;
      $('kRata').textContent = ang(Math.round(k.kunjungan / h * 10) / 10);
      $('kRataD').innerHTML = `${delta(k.kunjungan / h, p.kunjungan / h)} kunjungan/hari`;
      $('kSelesai').textContent = pct(k.selesai, k.kunjungan) + '%';
      $('kSelesaiD').innerHTML = `${ang(k.selesai)} selesai · ${ang(k.dilayani)} dalam layanan` + (k.belum_checkin ? ` · ${ang(k.belum_checkin)} belum check-in` : '');
      $('kBatal').textContent = ang(k.batal);
      $('kBatalD').innerHTML = `${delta(k.batal, p.batal, true)} · ${persen(k.batal, k.batal + k.kunjungan).toLocaleString('id-ID')}% dari pendaftaran`;

      // tren
      const label = j.tren.map(t => { const [, m, d] = t.tgl.split('-'); return h > 62 ? `${+d} ${BULAN[+m - 1]}` : `${+d}/${+m}`; });
      $('trenSub').textContent = `Kunjungan per hari · total ${ang(k.kunjungan)}, periode sebelumnya ${ang(p.kunjungan)}`;
      gambarGrafik('cTren', {
        data: {
          labels: label,
          datasets: [
            { type: 'bar', label: 'Periode ini', data: j.tren.map(t => t.kunjungan), backgroundColor: WARNA[0], borderRadius: 4, maxBarThickness: 28, order: 2 },
            { type: 'line', label: 'Periode sebelumnya', data: j.tren_lalu, borderColor: ABU, borderWidth: 2, borderDash: [5, 4], pointRadius: 0, pointHoverRadius: 4, tension: .3, order: 1 },
          ]
        },
        options: Object.assign({}, dasar, {
          interaction: { mode: 'index', intersect: false },
          scales: { x: Object.assign({}, tanpaGrid, { ticks: { color: '#64748b', font: { size: 11 }, maxRotation: 0, autoSkip: true, maxTicksLimit: 16 } }), y: Object.assign({ beginAtZero: true }, sumbu) },
          plugins: { legend: { display: false }, tooltip: { callbacks: {
            title: it => tglID(j.tren[it[0].dataIndex].tgl),
            afterBody: it => { const t = j.tren[it[0].dataIndex]; return [`Batal: ${ang(t.batal)}`, `Pendapatan: ${rp(t.pendapatan)}`]; }
          } } }
        })
      });

      // penjamin: 4 slot warna tetap, sisanya dilipat ke "Lainnya"
      let pj = j.penjamin.slice(0, 3);
      const sisa = j.penjamin.slice(3);
      if (sisa.length) pj.push({ nama: sisa.length === 1 ? sisa[0].nama : 'Lainnya', n: sisa.reduce((a, x) => a + x.n, 0), pendapatan: sisa.reduce((a, x) => a + x.pendapatan, 0) });
      gambarGrafik('cPenjamin', {
        type: 'doughnut',
        data: { labels: pj.map(x => x.nama), datasets: [{ data: pj.map(x => x.n), backgroundColor: WARNA, borderColor: '#fff', borderWidth: 2 }] },
        options: Object.assign({}, dasar, { cutout: '62%', plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => `${c.label}: ${ang(c.raw)} (${pct(c.raw, k.kunjungan)}%)` } } } })
      });
      $('lPenjamin').innerHTML = pj.length ? pj.map((x, i) => bar(x.nama, x.n, pj[0].n, `${ang(x.n)} <span class="kc">${pct(x.n, k.kunjungan)}%</span>`, x.pendapatan ? rpPendek(x.pendapatan) : '', WARNA[i])).join('') : '<div class="adb-kosong">Tidak ada data.</div>';

      // jam sibuk
      const jamAda = j.jam.map((n, i) => ({ i, n })).filter(x => x.n > 0);
      const jMin = jamAda.length ? Math.max(0, Math.min(jamAda[0].i, 6)) : 6, jMax = jamAda.length ? Math.max(jamAda[jamAda.length - 1].i, 18) : 18;
      const rJam = []; for (let i = jMin; i <= jMax; i++) rJam.push(i);
      const puncak = jamAda.reduce((a, x) => x.n > a.n ? x : a, { i: null, n: 0 });
      $('jamSub').textContent = puncak.i !== null ? `Tersibuk pukul ${String(puncak.i).padStart(2, '0')}.00–${String(puncak.i + 1).padStart(2, '0')}.00 (${ang(puncak.n)} kunjungan)` : 'Belum ada data';
      gambarGrafik('cJam', {
        type: 'bar',
        data: { labels: rJam.map(i => String(i).padStart(2, '0')), datasets: [{ data: rJam.map(i => j.jam[i]), backgroundColor: rJam.map(i => i === puncak.i ? WARNA[1] : WARNA[0]), borderRadius: 4, maxBarThickness: 22 }] },
        options: Object.assign({}, dasar, { scales: { x: tanpaGrid, y: Object.assign({ beginAtZero: true }, sumbu) }, plugins: { legend: { display: false }, tooltip: { callbacks: { title: it => `Pukul ${it[0].label}.00`, label: c => `${ang(c.raw)} kunjungan` } } } })
      });

      // hari dalam seminggu: rata-rata = total / jumlah hari tsb dalam periode
      const jumlahHari = Array(7).fill(0);
      j.tren.forEach(t => { jumlahHari[(new Date(t.tgl + 'T12:00:00').getDay() + 6) % 7]++; });
      const rataHari = j.hari_minggu.map((n, i) => jumlahHari[i] ? Math.round(n / jumlahHari[i] * 10) / 10 : 0);
      const hTop = rataHari.indexOf(Math.max(...rataHari));
      gambarGrafik('cHari', {
        type: 'bar',
        data: { labels: HARI, datasets: [{ data: rataHari, backgroundColor: rataHari.map((_, i) => i === hTop && rataHari[i] > 0 ? WARNA[1] : WARNA[0]), borderRadius: 4, maxBarThickness: 34 }] },
        options: Object.assign({}, dasar, { scales: { x: tanpaGrid, y: Object.assign({ beginAtZero: true }, sumbu) }, plugins: { legend: { display: false }, tooltip: { callbacks: { title: it => HARI_PANJANG[it[0].dataIndex], label: c => `rata-rata ${ang(c.raw)} kunjungan (${jumlahHari[c.dataIndex]} hari)` } } } })
      });

      // demografi
      const g = j.gender, totG = g.L + g.P + g['?'];
      $('demoSub').textContent = `${ang(k.pasien_unik)} pasien unik`;
      $('lGender').innerHTML = bar('Perempuan', g.P, totG, `${ang(g.P)} <span class="kc">${pct(g.P, totG)}%</span>`, '', WARNA[0]) +
        bar('Laki-laki', g.L, totG, `${ang(g.L)} <span class="kc">${pct(g.L, totG)}%</span>`, '', WARNA[0]);
      const um = Object.entries(j.umur).filter(([u, n]) => u !== '?' || n > 0);
      const maksU = Math.max(...um.map(x => x[1]), 1);
      $('lUmur').innerHTML = '<div class="sub mb-1 mt-2">Kelompok umur</div>' + um.map(([u, n]) => bar(u === '?' ? 'Tidak diketahui' : u + ' th', n, maksU, `${ang(n)} <span class="kc">${pct(n, totG)}%</span>`)).join('');

      // dokter
      const dk = j.dokter;
      $('dokSub').textContent = `${ang(j.dokter_total)} dokter melayani periode ini`;
      $('lDokter').innerHTML = dk.length ? dk.map(x => bar(x.nama, x.n, dk[0].n, ang(x.n), `${pct(x.n, k.kunjungan)}%`)).join('') : '<div class="adb-kosong">Belum ada data dokter.</div>';

      // diagnosa
      $('diagSub').textContent = j.diagnosa_terisi ? `Dari ${ang(j.diagnosa_terisi)} kunjungan berkode diagnosa (${pct(j.diagnosa_terisi, k.kunjungan)}%)` : 'Belum ada kunjungan berkode diagnosa';
      $('lDiag').innerHTML = j.diagnosa.length ? j.diagnosa.map(x => bar(`${x.kode} · ${x.nama || ''}`, x.n, j.diagnosa[0].n, ang(x.n), `${pct(x.n, j.diagnosa_terisi)}%`)).join('') : '<div class="adb-kosong">Tidak ada data diagnosa.</div>';

      // poli & metode
      $('lPoli').innerHTML = j.poli.length ? j.poli.map(x => bar(x.nama, x.n, j.poli[0].n, `${ang(x.n)} <span class="kc">${pct(x.n, k.kunjungan)}%</span>`)).join('') : '<div class="adb-kosong">Tidak ada data.</div>';
      const mt = j.metode.filter(x => x.total > 0 || x.n > 0);
      const maksM = Math.max(...mt.map(x => x.total), 1);
      $('lMetode').innerHTML = mt.length ? mt.map(x => bar(x.nama, x.total, maksM, rpPendek(x.total), `${ang(x.n)} trx`)).join('') : '<div class="adb-kosong">Belum ada pembayaran.</div>';

      // insight
      const ins = [];
      if (p.kunjungan) {
        const dp = Math.round((k.kunjungan - p.kunjungan) / p.kunjungan * 1000) / 10;
        ins.push(['trending-' + (dp >= 0 ? 'up' : 'down'), `Kunjungan <b>${dp >= 0 ? 'naik' : 'turun'} ${Math.abs(dp).toLocaleString('id-ID')}%</b> dibanding periode sebelumnya (${ang(p.kunjungan)} → ${ang(k.kunjungan)}).`]);
      }
      if (j.penjamin.length) { const t = j.penjamin[0]; ins.push(['shield-check', `<b>${esc(t.nama)}</b> mendominasi dengan ${pct(t.n, k.kunjungan)}% kunjungan.`]); }
      if (puncak.i !== null && h >= 1) ins.push(['clock', `Jam tersibuk <b>${String(puncak.i).padStart(2, '0')}.00–${String(puncak.i + 1).padStart(2, '0')}.00</b>; pertimbangkan tambahan petugas di jam ini.`]);
      if (h >= 7 && rataHari[hTop] > 0) ins.push(['calendar-event', `Hari tersibuk <b>${HARI_PANJANG[hTop]}</b>, rata-rata ${ang(rataHari[hTop])} kunjungan.`]);
      if (j.diagnosa.length && j.diagnosa_terisi >= k.kunjungan * 0.1) ins.push(['virus', `Diagnosa terbanyak <b>${esc(j.diagnosa[0].kode)} ${esc(j.diagnosa[0].nama || '')}</b> (${ang(j.diagnosa[0].n)} kasus).`]);
      if (k.batal && persen(k.batal, k.batal + k.kunjungan) >= 3) ins.push(['alert-triangle', `<b>${pct(k.batal, k.batal + k.kunjungan)}%</b> pendaftaran batal (${ang(k.batal)}). Periksa penyebab pembatalan.`]);
      if (k.kunjungan && k.pasien_baru) ins.push(['user-plus', `<b>${ang(k.pasien_baru)}</b> pasien baru (${pct(k.pasien_baru, k.pasien_unik)}% dari pasien unik).`]);
      $('adbInsight').innerHTML = ins.length ? ins.map(([i, t]) => `<div><i class="ti ti-${i}"></i><span>${t}</span></div>`).join('') : '<div><i class="ti ti-info-circle"></i><span>Belum ada kunjungan pada periode ini.</span></div>';
    }

    // ---------- operasional hari ini (dimuat terpisah: stok butuh ±1 dtk) ----------
    async function muatOperasional() {
      try {
        const r = await fetch(API + '?bagian=operasional', { credentials: 'same-origin' });
        const j = await r.json().catch(() => ({}));
        if (!r.ok || j.status !== 'success') throw new Error(j.message || 'Gagal (' + r.status + ')');
        const hi = j.hari_ini;
        $('opsSub').textContent = 'Status kunjungan ' + tglID(j.tanggal);
        const chip = (kls, teks, n) => n ? `<span class="adb-st ${kls}">${teks} ${ang(n)}</span>` : '';
        $('lOpsRingkas').innerHTML = chip('kuning', 'Dalam layanan', hi.layanan) + chip('hijau', 'Selesai', hi.selesai) + chip('abu', 'Belum check-in', hi.checkin) +
          chip('merah', 'Batal', hi.batal);
        $('lOpsPoli').innerHTML = j.per_poli.length ? `<table class="adb-tbl"><thead><tr><th>Poli</th><th class="num">Layanan</th><th class="num">Selesai</th><th class="num">Check-in</th><th class="num">Batal</th></tr></thead><tbody>` +
          j.per_poli.map(x => `<tr><td>${esc(x.poli)}</td><td class="num">${ang(x.layanan)}</td><td class="num">${ang(x.selesai)}</td><td class="num">${ang(x.checkin)}</td><td class="num">${ang(x.batal)}</td></tr>`).join('') + '</tbody></table>'
          : '<div class="adb-kosong">Belum ada kunjungan hari ini.</div>';
        $('lBed').innerHTML = j.bed.length ? j.bed.map(b => {
          const p = persen(b.terisi, b.total), kls = p >= 90 ? 'merah' : p >= 70 ? 'kuning' : 'hijau';
          return bar(b.kelas, b.terisi, b.total, `${ang(b.terisi)}/${ang(b.total)}`, `<span class="adb-st ${kls}">${p.toLocaleString('id-ID')}%</span>`, p >= 90 ? '#e34948' : WARNA[0]);
        }).join('') : '<div class="adb-kosong">Belum ada data kamar.</div>';
        const s = j.stok;
        $('stokSub').textContent = `${ang(s.habis)} habis · ${ang(s.minimum)} di bawah minimum · dari ${ang(s.total_item)} item`;
        if (s.total_item && s.habis === s.total_item) {
          $('lStok').innerHTML = '<div class="adb-kosong"><i class="ti ti-info-circle d-block fs-5 mb-1"></i>Semua item tercatat stok 0 — stok farmasi faskes ini tampaknya belum dikelola lewat penerimaan barang.</div>';
        } else {
          $('lStok').innerHTML = s.items.length ? `<table class="adb-tbl"><tbody>` + s.items.map(o => `<tr><td>${esc(o.nama)}${o.keluar_hari_ini ? ' <span class="adb-st kuning">dipakai hari ini</span>' : ''}</td>
              <td class="num"><span class="adb-st ${o.stok <= 0 ? 'merah' : 'kuning'}">${ang(o.stok)}${o.satuan ? ' ' + esc(o.satuan) : ''}</span>${o.min ? `<div class="sub">min ${ang(o.min)}</div>` : ''}</td></tr>`).join('') + '</tbody></table>'
            : '<div class="adb-kosong"><i class="ti ti-circle-check d-block fs-5 mb-1" style="color:#1baf7a"></i>Tidak ada stok kritis.</div>';
        }
      } catch (e) {
        ['lOpsPoli', 'lBed', 'lStok'].forEach(i => { $(i).innerHTML = `<div class="adb-kosong text-danger">${esc(e.message)}</div>`; });
      }
    }

    // ---------- event ----------
    document.querySelectorAll('#adbCepat .btn').forEach(b => b.addEventListener('click', () => { const r = rentangCepat(b.dataset.r); $('adbDari').value = r[0]; $('adbSampai').value = r[1]; muatStatistik(); }));
    $('adbTerapkan').addEventListener('click', muatStatistik);
    ['adbPoli', 'adbPenjamin'].forEach(i => $(i).addEventListener('change', muatStatistik));
    ['adbDari', 'adbSampai'].forEach(i => $(i).addEventListener('keydown', e => { if (e.key === 'Enter') muatStatistik(); }));
    $('adbReset').addEventListener('click', () => { const r = rentangCepat('bulan'); $('adbDari').value = r[0]; $('adbSampai').value = r[1]; $('adbPoli').value = ''; $('adbPenjamin').value = ''; muatStatistik(); });
    // segarkan otomatis tiap 5 menit selama periode mencakup hari ini & tab terlihat
    setInterval(() => { if (!document.hidden && f.sampai === HARI_INI) { muatStatistik(); muatOperasional(); } }, 5 * 60 * 1000);

    tandaiCepat();
    muatStatistik();
    muatOperasional();
  })();
</script>

<!-- =========================================================
     CONTRACT REMINDER JAVASCRIPT
========================================================== -->
<script>
  (function() {

    "use strict";

    const CONTRACT_URL =
      "controller/dashboard/contractReminderController.php?action=dashboard";

    let contractCountdownTimer = null;


    function escapeContractHtml(value) {

      if (value === null || value === undefined) {
        return "";
      }

      return String(value)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
    }


    function formatContractDate(value) {

      if (!value) {
        return "-";
      }

      const date = new Date(value + "T00:00:00");

      if (isNaN(date.getTime())) {
        return value;
      }

      return date.toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "short",
        year: "numeric"
      });
    }


    function setContractZero() {

      const ids = [
        "contractYears",
        "contractMonths",
        "contractDays",
        "contractHours"
      ];

      ids.forEach(function(id) {

        const element = document.getElementById(id);

        if (element) {
          element.textContent = "00";
        }

      });
    }


    function updateContractCountdown(timestamp) {

      const now = new Date();
      const end = new Date(Number(timestamp));

      if (isNaN(end.getTime()) || end <= now) {

        setContractZero();

        return false;
      }


      /*
       * Hitung kalender secara presisi:
       * Tahun -> Bulan -> Hari -> Jam
       */
      let cursor = new Date(now);


      let years =
        end.getFullYear() -
        cursor.getFullYear();


      let yearTest = new Date(cursor);

      yearTest.setFullYear(
        cursor.getFullYear() + years
      );


      if (yearTest > end) {
        years--;
      }


      cursor.setFullYear(
        cursor.getFullYear() + years
      );


      let months =
        (end.getFullYear() - cursor.getFullYear()) * 12 +
        (end.getMonth() - cursor.getMonth());


      let monthTest = new Date(cursor);

      monthTest.setMonth(
        cursor.getMonth() + months
      );


      if (monthTest > end) {
        months--;
      }


      cursor.setMonth(
        cursor.getMonth() + months
      );


      let remainingMs = end.getTime() - cursor.getTime();

      let days = Math.floor(
        remainingMs / (1000 * 60 * 60 * 24)
      );

      cursor.setDate(
        cursor.getDate() + days
      );


      remainingMs = end.getTime() - cursor.getTime();

      let hours = Math.floor(
        remainingMs / (1000 * 60 * 60)
      );


      document.getElementById("contractYears").textContent =
        String(Math.max(0, years)).padStart(2, "0");

      document.getElementById("contractMonths").textContent =
        String(Math.max(0, months)).padStart(2, "0");

      document.getElementById("contractDays").textContent =
        String(Math.max(0, days)).padStart(2, "0");

      document.getElementById("contractHours").textContent =
        String(Math.max(0, hours)).padStart(2, "0");


      return true;
    }


    function startContractCountdown(timestamp) {

      if (contractCountdownTimer) {

        clearInterval(contractCountdownTimer);

        contractCountdownTimer = null;
      }


      const run = function() {

        const stillActive =
          updateContractCountdown(timestamp);

        if (!stillActive && contractCountdownTimer) {

          clearInterval(contractCountdownTimer);

          contractCountdownTimer = null;
        }

      };


      run();

      /*
       * Update setiap menit karena tampilan hanya
       * sampai satuan jam.
       */
      contractCountdownTimer =
        setInterval(run, 60 * 1000);
    }


    function renderContractReminder(response) {

      /*
       * Data kontrak bersifat PER KONTRAK.
       *
       * Backend lama bisa mengirim:
       * nearest: null
       * items: [ ...kontrak... ]
       *
       * Jadi frontend tidak boleh bergantung pada nearest.
       * Gunakan nearest jika tersedia, jika tidak gunakan
       * kontrak pertama dari items.
       */
      const items = Array.isArray(response.items) ?
        response.items :
        [];

      const nearest =
        response.nearest ||
        (items.length > 0 ? items[0] : null);

      const statusElement =
        document.getElementById("contractReminderStatus");

      if (!nearest) {

        document.getElementById("contractFaskes").textContent =
          "Tidak ada data kontrak";

        document.getElementById("contractNumber").textContent =
          "-";

        document.getElementById("contractPeriod").textContent =
          "-";

        document.getElementById("contractEndDate").textContent =
          "-";

        statusElement.innerHTML =
          '<span class="badge bg-secondary-subtle text-secondary">' +
          'Tidak Ada Data' +
          '</span>';

        setContractZero();

        return;
      }

      /*
       * IDENTITAS FASKES
       */
      document.getElementById("contractFaskes").textContent =
        nearest.faskes_code || "-";

      /*
       * NOMOR KONTRAK
       */
      document.getElementById("contractNumber").textContent =
        nearest.contract_number &&
        nearest.contract_number !== "-" ?
        "No. Kontrak: " + nearest.contract_number :
        "Nomor kontrak tidak tersedia";

      /*
       * PERIODE KONTRAK
       */
      document.getElementById("contractPeriod").textContent =
        formatContractDate(nearest.contract_start) +
        " - " +
        formatContractDate(nearest.contract_end);

      /*
       * TANGGAL BERAKHIR
       */
      document.getElementById("contractEndDate").textContent =
        "Berakhir pada " +
        formatContractDate(nearest.contract_end);

      /*
       * STATUS KONTRAK
       */
      let statusHtml = "";

      if (nearest.status === "active") {

        statusHtml =
          '<span class="badge bg-success-subtle text-success">' +
          'Kontrak Aktif' +
          '</span>';

      } else if (nearest.status === "warning") {

        statusHtml =
          '<span class="badge bg-warning-subtle text-warning">' +
          'Perlu Perhatian' +
          '</span>';

      } else if (nearest.status === "critical") {

        statusHtml =
          '<span class="badge bg-danger-subtle text-danger">' +
          'Segera Berakhir' +
          '</span>';

      } else {

        statusHtml =
          '<span class="badge bg-dark-subtle text-dark">' +
          'Kontrak Berakhir' +
          '</span>';
      }

      statusElement.innerHTML = statusHtml;

      /*
       * COUNTDOWN
       *
       * Untuk kontrak aktif:
       * Tahun : Bulan : Hari : Jam
       *
       * Untuk kontrak expired:
       * 00 : 00 : 00 : 00
       */
      startContractCountdown(
        nearest.contract_end_timestamp
      );
    }


    async function loadContractReminder() {

      try {

        const response =
          await fetch(CONTRACT_URL, {
            method: "GET",
            headers: {
              "Accept": "application/json",
              "X-Requested-With": "XMLHttpRequest"
            },
            cache: "no-store"
          });


        if (!response.ok) {

          throw new Error(
            "HTTP Error " + response.status
          );
        }


        const result =
          await response.json();


        console.log(
          "Contract Reminder Response:",
          result
        );


        if (!result.status) {

          throw new Error(
            result.message ||
            "Gagal mengambil data kontrak."
          );
        }


        renderContractReminder(result);


      } catch (error) {

        console.error(
          "Contract Reminder Error:",
          error
        );


        document.getElementById("contractFaskes").textContent =
          "Gagal memuat data";

        document.getElementById("contractNumber").textContent =
          error.message || "Terjadi kesalahan";

        document.getElementById("contractPeriod").textContent =
          "-";

        document.getElementById("contractEndDate").textContent =
          "-";

        document.getElementById("contractReminderStatus").innerHTML =
          '<span class="badge bg-danger-subtle text-danger">' +
          'Error' +
          '</span>';

        setContractZero();
      }

    }


    loadContractReminder();

  })();
</script>
