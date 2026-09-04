<style>
  /* =========================================================
       DASHBOARD ADMIN
    ========================================================= */

  .admin-dashboard {
    --ad-primary: #635bff;
    --ad-primary-soft: #eeecff;
    --ad-text: #273444;
    --ad-muted: #7b8494;
    --ad-border: #edf0f5;
  }

  .admin-dashboard .dash-card {
    background: #fff;
    border: 1px solid var(--ad-border);
    border-radius: 18px;
    padding: 20px;
    height: 100%;
  }

  .admin-dashboard .dash-card:hover {
    box-shadow: 0 10px 30px rgba(30, 40, 60, .05);
  }

  /* =========================================================
       KPI
    ========================================================= */

  .admin-dashboard .kpi-card {
    background: #fff;
    border: 1px solid var(--ad-border);
    border-radius: 18px;
    padding: 20px;
    height: 100%;
    transition: .2s ease;
  }

  .admin-dashboard .kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 28px rgba(30, 40, 60, .06);
  }

  .admin-dashboard .kpi-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
  }

  .admin-dashboard .kpi-icon {
    width: 46px;
    height: 46px;
    border-radius: 14px;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 23px;
  }

  .admin-dashboard .kpi-purple {
    background: #eeecff;
    color: #635bff;
  }

  .admin-dashboard .kpi-blue {
    background: #e8f4ff;
    color: #2185d0;
  }

  .admin-dashboard .kpi-green {
    background: #e8f8ef;
    color: #1a9b5f;
  }

  .admin-dashboard .kpi-orange {
    background: #fff2e3;
    color: #ed8b22;
  }

  .admin-dashboard .kpi-title {
    font-size: 13px;
    color: var(--ad-muted);
    margin-bottom: 5px;
  }

  .admin-dashboard .kpi-value {
    color: var(--ad-text);
    font-size: 26px;
    font-weight: 700;
    line-height: 1.2;
  }

  .admin-dashboard .kpi-info {
    font-size: 11px;
    margin-top: 7px;
  }

  .admin-dashboard .kpi-up {
    color: #16a34a;
  }

  .admin-dashboard .kpi-down {
    color: #dc2626;
  }

  /* =========================================================
       CARD HEADER
    ========================================================= */

  .admin-dashboard .dash-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;

    margin-bottom: 18px;
  }

  .admin-dashboard .dash-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--ad-text);
    margin: 0;
  }

  .admin-dashboard .dash-subtitle {
    color: var(--ad-muted);
    font-size: 12px;
    margin-top: 3px;
  }

  .admin-dashboard .dash-filter {
    width: auto;
    min-width: 110px;

    border-radius: 10px;
    border-color: var(--ad-border);

    font-size: 12px;
  }

  /* =========================================================
       QUEUE
    ========================================================= */

  .admin-dashboard .queue-item {
    display: flex;
    align-items: center;
    gap: 12px;

    padding: 12px 0;

    border-bottom: 1px solid #f0f1f4;
  }

  .admin-dashboard .queue-item:last-child {
    border-bottom: 0;
  }

  .admin-dashboard .queue-number {
    width: 38px;
    height: 38px;

    border-radius: 11px;

    display: flex;
    align-items: center;
    justify-content: center;

    background: #f0efff;
    color: var(--ad-primary);

    font-size: 12px;
    font-weight: 700;

    flex-shrink: 0;
  }

  .admin-dashboard .queue-name {
    color: var(--ad-text);
    font-size: 13px;
    font-weight: 600;
  }

  .admin-dashboard .queue-detail {
    color: var(--ad-muted);
    font-size: 11px;
    margin-top: 2px;
  }

  .admin-dashboard .queue-status {
    margin-left: auto;

    padding: 5px 9px;

    border-radius: 8px;

    font-size: 10px;
    white-space: nowrap;
  }

  .admin-dashboard .status-waiting {
    background: #fff3d9;
    color: #c47a00;
  }

  .admin-dashboard .status-process {
    background: #e8f2ff;
    color: #2377c7;
  }

  .admin-dashboard .status-done {
    background: #e7f8ee;
    color: #168a4e;
  }

  /* =========================================================
       ROOM
    ========================================================= */

  .admin-dashboard .room-item {
    display: flex;
    align-items: center;
    justify-content: space-between;

    padding: 12px 0;

    border-bottom: 1px solid #f0f1f4;
  }

  .admin-dashboard .room-item:last-child {
    border-bottom: 0;
  }

  .admin-dashboard .room-name {
    font-size: 13px;
    font-weight: 600;
    color: var(--ad-text);
  }

  .admin-dashboard .room-total {
    font-size: 11px;
    color: var(--ad-muted);
    margin-top: 2px;
  }

  .admin-dashboard .room-progress {
    width: 120px;
    height: 6px;

    background: #edf0f4;

    border-radius: 20px;

    overflow: hidden;

    margin-top: 7px;
  }

  .admin-dashboard .room-progress span {
    display: block;

    height: 100%;

    background: var(--ad-primary);

    border-radius: inherit;
  }

  .admin-dashboard .room-value {
    font-size: 11px;
    font-weight: 600;
    color: var(--ad-text);
  }

  /* =========================================================
       STOCK
    ========================================================= */

  .admin-dashboard .stock-item {
    display: flex;
    align-items: center;
    justify-content: space-between;

    padding: 12px 0;

    border-bottom: 1px solid #f0f1f4;
  }

  .admin-dashboard .stock-item:last-child {
    border-bottom: 0;
  }

  .admin-dashboard .stock-name {
    font-size: 12px;
    font-weight: 600;
    color: var(--ad-text);
  }

  .admin-dashboard .stock-code {
    font-size: 10px;
    color: var(--ad-muted);
    margin-top: 2px;
  }

  .admin-dashboard .stock-value {
    color: #dc2626;
    font-size: 12px;
    font-weight: 700;
  }

  /* =========================================================
       ACTIVITY
    ========================================================= */

  .admin-dashboard .activity-item {
    display: flex;
    gap: 12px;

    padding: 12px 0;

    border-bottom: 1px solid #f0f1f4;
  }

  .admin-dashboard .activity-item:last-child {
    border-bottom: 0;
  }

  .admin-dashboard .activity-icon {
    width: 35px;
    height: 35px;

    border-radius: 10px;

    background: var(--ad-primary-soft);
    color: var(--ad-primary);

    display: flex;
    align-items: center;
    justify-content: center;

    flex-shrink: 0;
  }

  .admin-dashboard .activity-text {
    font-size: 12px;
    line-height: 1.5;
    color: var(--ad-text);
  }

  .admin-dashboard .activity-time {
    font-size: 10px;
    color: var(--ad-muted);
    margin-top: 2px;
  }

  /* =========================================================
       QUICK ACCESS
    ========================================================= */

  .admin-dashboard .quick-item {
    display: block;

    background: #f9fafc;

    border: 1px solid #f0f1f4;

    border-radius: 14px;

    padding: 15px;

    text-decoration: none;

    transition: .2s ease;
  }

  .admin-dashboard .quick-item:hover {
    background: #f5f4ff;
    border-color: #e2dfff;
  }

  .admin-dashboard .quick-icon {
    width: 36px;
    height: 36px;

    border-radius: 10px;

    display: flex;
    align-items: center;
    justify-content: center;

    background: #eeecff;
    color: var(--ad-primary);

    font-size: 19px;
  }

  .admin-dashboard .quick-title {
    color: var(--ad-text);

    font-size: 12px;
    font-weight: 600;

    margin-top: 9px;
  }

  .admin-dashboard .quick-desc {
    color: var(--ad-muted);

    font-size: 10px;

    margin-top: 2px;
  }

  /* =========================================================
       RESPONSIVE
    ========================================================= */

  @media (max-width: 767px) {

    .admin-dashboard .dash-header {
      align-items: flex-start;
    }

    .admin-dashboard .dash-filter {
      min-width: 95px;
    }

    .admin-dashboard .room-progress {
      width: 90px;
    }

  }
</style>


<div class="admin-dashboard">

  <!-- =====================================================
         KPI
    ====================================================== -->

  <div class="row g-3 mb-3">

    <!-- TOTAL PASIEN -->

    <div class="col-xl-3 col-md-6">

      <div class="kpi-card">

        <div class="kpi-top">

          <div>

            <div class="kpi-title">
              Total Pasien Hari Ini
            </div>

            <div class="kpi-value">
              248
            </div>

            <div class="kpi-info kpi-up">
              ↑ 12,4% dari kemarin
            </div>

          </div>

          <div class="kpi-icon kpi-purple">

            <iconify-icon
              icon="solar:users-group-rounded-bold">
            </iconify-icon>

          </div>

        </div>

      </div>

    </div>


    <!-- PASIEN BARU -->

    <div class="col-xl-3 col-md-6">

      <div class="kpi-card">

        <div class="kpi-top">

          <div>

            <div class="kpi-title">
              Pasien Baru
            </div>

            <div class="kpi-value">
              37
            </div>

            <div class="kpi-info kpi-up">
              ↑ 8,2% bulan ini
            </div>

          </div>

          <div class="kpi-icon kpi-blue">

            <iconify-icon
              icon="solar:user-plus-bold">
            </iconify-icon>

          </div>

        </div>

      </div>

    </div>


    <!-- KUNJUNGAN -->

    <div class="col-xl-3 col-md-6">

      <div class="kpi-card">

        <div class="kpi-top">

          <div>

            <div class="kpi-title">
              Kunjungan Rawat Jalan
            </div>

            <div class="kpi-value">
              184
            </div>

            <div class="kpi-info kpi-up">
              ↑ 5,7% minggu ini
            </div>

          </div>

          <div class="kpi-icon kpi-green">

            <iconify-icon
              icon="solar:stethoscope-bold">
            </iconify-icon>

          </div>

        </div>

      </div>

    </div>


    <!-- PENDAPATAN -->

    <div class="col-xl-3 col-md-6">

      <div class="kpi-card">

        <div class="kpi-top">

          <div>

            <div class="kpi-title">
              Pendapatan Hari Ini
            </div>

            <div class="kpi-value"
              style="font-size:22px">

              Rp 18,4 Jt

            </div>

            <div class="kpi-info kpi-up">
              ↑ 10,1% dari kemarin
            </div>

          </div>

          <div class="kpi-icon kpi-orange">

            <iconify-icon
              icon="solar:wallet-money-bold">
            </iconify-icon>

          </div>

        </div>

      </div>

    </div>

  </div>


  <!-- =====================================================
         CHART
    ====================================================== -->

  <div class="row g-3 mb-3">

    <!-- KUNJUNGAN -->

    <div class="col-xl-8">

      <div class="dash-card">

        <div class="dash-header">

          <div>

            <h6 class="dash-title">
              Statistik Kunjungan
            </h6>

            <div class="dash-subtitle">
              Jumlah kunjungan pasien
            </div>

          </div>

          <select
            class="form-select dash-filter">

            <option>7 Hari</option>
            <option>30 Hari</option>
            <option>3 Bulan</option>

          </select>

        </div>

        <div style="height:280px">

          <canvas id="adminVisitChart"></canvas>

        </div>

      </div>

    </div>


    <!-- JENIS PEMBAYARAN -->

    <div class="col-xl-4">

      <div class="dash-card">

        <div class="dash-header">

          <div>

            <h6 class="dash-title">
              Jenis Pembayaran
            </h6>

            <div class="dash-subtitle">
              Distribusi pasien hari ini
            </div>

          </div>

        </div>

        <div style="height:210px">

          <canvas id="adminPaymentChart"></canvas>

        </div>

        <div class="row text-center mt-3">

          <div class="col-4">

            <div class="fw-bold">
              56%
            </div>

            <small class="text-muted">
              BPJS
            </small>

          </div>

          <div class="col-4">

            <div class="fw-bold">
              31%
            </div>

            <small class="text-muted">
              Umum
            </small>

          </div>

          <div class="col-4">

            <div class="fw-bold">
              13%
            </div>

            <small class="text-muted">
              Asuransi
            </small>

          </div>

        </div>

      </div>

    </div>

  </div>


  <!-- =====================================================
         OPERASIONAL
    ====================================================== -->

  <div class="row g-3 mb-3">

    <!-- ANTRIAN -->

    <div class="col-xl-5">

      <div class="dash-card">

        <div class="dash-header">

          <div>

            <h6 class="dash-title">
              Antrian Pasien
            </h6>

            <div class="dash-subtitle">
              Pelayanan hari ini
            </div>

          </div>

          <span class="badge bg-primary">
            18 Menunggu
          </span>

        </div>


        <div class="queue-item">

          <div class="queue-number">
            A-021
          </div>

          <div>

            <div class="queue-name">
              Budi Santoso
            </div>

            <div class="queue-detail">
              Poli Umum · Dr. Andi
            </div>

          </div>

          <span class="queue-status status-waiting">
            Menunggu
          </span>

        </div>


        <div class="queue-item">

          <div class="queue-number">
            A-022
          </div>

          <div>

            <div class="queue-name">
              Siti Rahma
            </div>

            <div class="queue-detail">
              Poli Anak · Dr. Rina
            </div>

          </div>

          <span class="queue-status status-process">
            Dipanggil
          </span>

        </div>


        <div class="queue-item">

          <div class="queue-number">
            A-023
          </div>

          <div>

            <div class="queue-name">
              Ahmad Fauzi
            </div>

            <div class="queue-detail">
              Poli Umum · Dr. Andi
            </div>

          </div>

          <span class="queue-status status-process">
            Pemeriksaan
          </span>

        </div>


        <div class="queue-item">

          <div class="queue-number">
            A-024
          </div>

          <div>

            <div class="queue-name">
              Dewi Lestari
            </div>

            <div class="queue-detail">
              Poli Gigi · Dr. Maya
            </div>

          </div>

          <span class="queue-status status-waiting">
            Menunggu
          </span>

        </div>


        <div class="queue-item">

          <div class="queue-number">
            A-025
          </div>

          <div>

            <div class="queue-name">
              Rudi Hartono
            </div>

            <div class="queue-detail">
              Poli Umum · Dr. Andi
            </div>

          </div>

          <span class="queue-status status-done">
            Selesai
          </span>

        </div>

      </div>

    </div>


    <!-- KAMAR -->

    <div class="col-xl-4">

      <div class="dash-card">

        <div class="dash-header">

          <div>

            <h6 class="dash-title">
              Ketersediaan Kamar
            </h6>

            <div class="dash-subtitle">
              Status rawat inap
            </div>

          </div>

          <iconify-icon
            icon="solar:bed-bold"
            width="23">
          </iconify-icon>

        </div>


        <div class="room-item">

          <div>

            <div class="room-name">
              VIP
            </div>

            <div class="room-total">
              8 kamar
            </div>

            <div class="room-progress">
              <span style="width:75%"></span>
            </div>

          </div>

          <div class="room-value">
            2 kosong
          </div>

        </div>


        <div class="room-item">

          <div>

            <div class="room-name">
              Kelas I
            </div>

            <div class="room-total">
              12 kamar
            </div>

            <div class="room-progress">
              <span style="width:58%"></span>
            </div>

          </div>

          <div class="room-value">
            5 kosong
          </div>

        </div>


        <div class="room-item">

          <div>

            <div class="room-name">
              Kelas II
            </div>

            <div class="room-total">
              16 kamar
            </div>

            <div class="room-progress">
              <span style="width:81%"></span>
            </div>

          </div>

          <div class="room-value">
            3 kosong
          </div>

        </div>


        <div class="room-item">

          <div>

            <div class="room-name">
              Kelas III
            </div>

            <div class="room-total">
              24 kamar
            </div>

            <div class="room-progress">
              <span style="width:67%"></span>
            </div>

          </div>

          <div class="room-value">
            8 kosong
          </div>

        </div>

      </div>

    </div>


    <!-- STOK OBAT -->

    <div class="col-xl-3">

      <div class="dash-card">

        <div class="dash-header">

          <div>

            <h6 class="dash-title">
              Stok Menipis
            </h6>

            <div class="dash-subtitle">
              Perlu diperhatikan
            </div>

          </div>

          <span class="badge bg-danger">
            5 Item
          </span>

        </div>


        <div class="stock-item">

          <div>

            <div class="stock-name">
              Paracetamol 500mg
            </div>

            <div class="stock-code">
              OB-001
            </div>

          </div>

          <div class="stock-value">
            8
          </div>

        </div>


        <div class="stock-item">

          <div>

            <div class="stock-name">
              Amoxicillin 500mg
            </div>

            <div class="stock-code">
              OB-014
            </div>

          </div>

          <div class="stock-value">
            6
          </div>

        </div>


        <div class="stock-item">

          <div>

            <div class="stock-name">
              Omeprazole
            </div>

            <div class="stock-code">
              OB-031
            </div>

          </div>

          <div class="stock-value">
            4
          </div>

        </div>


        <div class="stock-item">

          <div>

            <div class="stock-name">
              Cetirizine
            </div>

            <div class="stock-code">
              OB-044
            </div>

          </div>

          <div class="stock-value">
            3
          </div>

        </div>

      </div>

    </div>

  </div>


  <!-- =====================================================
         ACTIVITY + QUICK ACCESS
    ====================================================== -->

  <div class="row g-3">

    <!-- ACTIVITY -->

    <div class="col-xl-7">

      <div class="dash-card">

        <div class="dash-header">

          <div>

            <h6 class="dash-title">
              Aktivitas Terbaru
            </h6>

            <div class="dash-subtitle">
              Aktivitas sistem hari ini
            </div>

          </div>

          <a href="#"
            class="small text-decoration-none">

            Lihat Semua

          </a>

        </div>


        <div class="activity-item">

          <div class="activity-icon">

            <iconify-icon
              icon="solar:user-plus-bold">
            </iconify-icon>

          </div>

          <div>

            <div class="activity-text">

              <strong>Pasien baru</strong>
              berhasil didaftarkan ke sistem.

            </div>

            <div class="activity-time">
              5 menit yang lalu
            </div>

          </div>

        </div>


        <div class="activity-item">

          <div class="activity-icon">

            <iconify-icon
              icon="solar:document-text-bold">
            </iconify-icon>

          </div>

          <div>

            <div class="activity-text">

              RME pasien
              <strong>Budi Santoso</strong>
              telah dilengkapi.

            </div>

            <div class="activity-time">
              12 menit yang lalu
            </div>

          </div>

        </div>


        <div class="activity-item">

          <div class="activity-icon">

            <iconify-icon
              icon="solar:pills-3-bold">
            </iconify-icon>

          </div>

          <div>

            <div class="activity-text">

              Resep pasien
              <strong>Siti Rahma</strong>
              telah diproses farmasi.

            </div>

            <div class="activity-time">
              18 menit yang lalu
            </div>

          </div>

        </div>


        <div class="activity-item">

          <div class="activity-icon">

            <iconify-icon
              icon="solar:wallet-money-bold">
            </iconify-icon>

          </div>

          <div>

            <div class="activity-text">

              Pembayaran transaksi
              <strong>#TRX-09231</strong>
              berhasil.

            </div>

            <div class="activity-time">
              25 menit yang lalu
            </div>

          </div>

        </div>

      </div>

    </div>


    <!-- QUICK ACCESS -->

    <div class="col-xl-5">

      <div class="dash-card">

        <div class="dash-header">

          <div>

            <h6 class="dash-title">
              Akses Cepat
            </h6>

            <div class="dash-subtitle">
              Modul yang sering digunakan
            </div>

          </div>

        </div>


        <div class="row g-2">

          <div class="col-6">

            <a href="#"
              class="quick-item">

              <div class="quick-icon">

                <iconify-icon
                  icon="solar:user-plus-bold">
                </iconify-icon>

              </div>

              <div class="quick-title">
                Pasien Baru
              </div>

              <div class="quick-desc">
                Registrasi pasien
              </div>

            </a>

          </div>


          <div class="col-6">

            <a href="#"
              class="quick-item">

              <div class="quick-icon">

                <iconify-icon
                  icon="solar:stethoscope-bold">
                </iconify-icon>

              </div>

              <div class="quick-title">
                Poliklinik
              </div>

              <div class="quick-desc">
                Pelayanan pasien
              </div>

            </a>

          </div>


          <div class="col-6">

            <a href="#"
              class="quick-item">

              <div class="quick-icon">

                <iconify-icon
                  icon="solar:pills-3-bold">
                </iconify-icon>

              </div>

              <div class="quick-title">
                Farmasi
              </div>

              <div class="quick-desc">
                Resep & obat
              </div>

            </a>

          </div>


          <div class="col-6">

            <a href="#"
              class="quick-item">

              <div class="quick-icon">

                <iconify-icon
                  icon="solar:document-text-bold">
                </iconify-icon>

              </div>

              <div class="quick-title">
                Monitoring RME
              </div>

              <div class="quick-desc">
                Rekam medis elektronik
              </div>

            </a>

          </div>

        </div>

      </div>

    </div>

  </div>

</div>


<script>
  /* =========================================================
       CHART KUNJUNGAN
    ========================================================= */

  document.addEventListener("DOMContentLoaded", function() {

    const visitCanvas =
      document.getElementById("adminVisitChart");

    if (visitCanvas && typeof Chart !== "undefined") {

      new Chart(visitCanvas, {

        type: "line",

        data: {

          labels: [
            "Sen",
            "Sel",
            "Rab",
            "Kam",
            "Jum",
            "Sab",
            "Min"
          ],

          datasets: [

            {
              label: "Kunjungan",

              data: [
                185,
                210,
                178,
                235,
                248,
                162,
                91
              ],

              borderColor: "#635bff",

              backgroundColor: "rgba(99,91,255,.08)",

              fill: true,

              tension: .4,

              borderWidth: 3,

              pointRadius: 3,

              pointHoverRadius: 5

            }

          ]

        },

        options: {

          responsive: true,

          maintainAspectRatio: false,

          plugins: {

            legend: {
              display: false
            }

          },

          scales: {

            x: {

              grid: {
                display: false
              },

              border: {
                display: false
              }

            },

            y: {

              beginAtZero: true,

              grid: {
                color: "#f0f1f5"
              },

              border: {
                display: false
              }

            }

          }

        }

      });

    }


    /* =====================================================
       CHART PEMBAYARAN
    ===================================================== */

    const paymentCanvas =
      document.getElementById("adminPaymentChart");

    if (paymentCanvas && typeof Chart !== "undefined") {

      new Chart(paymentCanvas, {

        type: "doughnut",

        data: {

          labels: [
            "BPJS",
            "Umum",
            "Asuransi"
          ],

          datasets: [

            {

              data: [
                56,
                31,
                13
              ],

              backgroundColor: [
                "#635bff",
                "#8fd3ff",
                "#9fe2bd"
              ],

              borderWidth: 0,

              spacing: 4

            }

          ]

        },

        options: {

          responsive: true,

          maintainAspectRatio: false,

          cutout: "72%",

          plugins: {

            legend: {
              display: false
            }

          }

        }

      });

    }

  });
</script>