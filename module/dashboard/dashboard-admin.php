<style>
  /* CARD */
  .card-ui {
    border-radius: 16px;
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.05);
    border: none;
  }

  /* KPI */
  .kpi {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .kpi .icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .kpi-title {
    font-size: 13px;
    color: #6b7280;
  }

  .kpi-value {
    font-size: 20px;
    font-weight: 600;
  }

  .badge-up {
    background: #dcfce7;
    color: #16a34a;
    font-size: 11px;
    padding: 2px 6px;
    border-radius: 6px;
  }

  .badge-down {
    background: #fee2e2;
    color: #dc2626;
    font-size: 11px;
    padding: 2px 6px;
    border-radius: 6px;
  }

  .chart-header {
    display: flex;
    gap: 10px;
    margin-bottom: 10px;
  }
</style>

<body>

  <div class="container-fluid p-3">

    <div class="row">

      <!-- LEFT -->
      <div class="col-lg-6">

        <div class="card card-ui p-3">

          <div class="chart-header">
            <select class="form-select">
              <option>Kunjungan Sakit</option>
            </select>
            <select class="form-select">
              <option>POLI UMUM</option>
            </select>
            <select class="form-select">
              <option>Bulan</option>
            </select>
          </div>

          <div class="d-flex align-items-center gap-2 mb-2">
            <h3 class="mb-0">18,845</h3>
            <span class="badge-down">↓ 47.19%</span>
          </div>

          <canvas id="chartUtama" height="120"></canvas>

        </div>

        <!-- BOTTOM -->
        <div class="row mt-3">

          <div class="col-md-6">
            <div class="card card-ui p-3">
              <div class="d-flex justify-content-between">
                <h6>Total Kunjungan</h6>
                <span class="badge bg-primary">BPJS</span>
              </div>

              <div class="d-flex align-items-center mt-2">
                <canvas id="donutChart" width="120"></canvas>
                <div class="ms-3" style="font-size:13px">

                </div>
              </div>
            </div>
          </div>

          <div class="col-md-6">

            <div class="card card-ui p-3 mb-3">
              <h6>Pendapatan Bulan Ini</h6>
              <h4>Rp0</h4>
              <span class="badge-up">+0%</span>
            </div>

            <div class="card card-ui p-3">
              <h6>Pengeluaran Bulan Ini</h6>
              <h4>Rp0</h4>
              <span class="badge-up">+0%</span>
            </div>

          </div>

        </div>

      </div>

      <!-- RIGHT -->
      <div class="col-lg-6">

        <div class="row" id="kpiContainer"></div>

        <div class="card card-ui p-3 mt-2">
          <h6>Pasien AntriCepat</h6>
          <table class="table table-sm">
            <tr>
              <th>Nama</th>
              <th>Dokter</th>
              <th>Status</th>
            </tr>
            <tr>
              <td>Budi</td>
              <td>Dr. Andi</td>
              <td><span class="badge bg-warning">Menunggu</span></td>
            </tr>
          </table>
        </div>

      </div>

    </div>
  </div>

  <script>
    // KPI DATA (NO PHP 🔥)
    const kpis = [{
        title: "Waktu Tunggu Dokter",
        value: "13 m 13 s",
        change: "+8.4%",
        type: "up",
        icon: "solar:clock-circle-bold",
        bg: "bg-primary-subtle"
      },
      {
        title: "Pasien Baru",
        value: "535",
        change: "-65%",
        type: "down",
        icon: "solar:user-plus-bold",
        bg: "bg-info-subtle"
      },
      {
        title: "Pasien Terdaftar",
        value: "45837",
        change: "+1.17%",
        type: "up",
        icon: "solar:users-group-rounded-bold",
        bg: "bg-danger-subtle"
      },
      {
        title: "Waktu Konsultasi",
        value: "13 m 48 s",
        change: "-68.9%",
        type: "down",
        icon: "solar:stethoscope-bold",
        bg: "bg-warning-subtle"
      },
      {
        title: "Stok Menipis",
        value: "5",
        change: "12 Items",
        type: "",
        icon: "solar:box-bold",
        bg: "bg-info-subtle"
      },
      {
        title: "Waktu Apotek",
        value: "0 m 41 s",
        change: "-21%",
        type: "down",
        icon: "solar:pill-bold",
        bg: "bg-success-subtle"
      }
    ];

    const container = document.getElementById("kpiContainer");

    kpis.forEach(k => {
      container.innerHTML += `
  <div class="col-md-6 mb-3">
    <div class="card card-ui p-3">
      <div class="kpi">
        <div>
          <div class="kpi-title">${k.title}</div>
          <div class="kpi-value">${k.value}</div>
          ${k.change ? `<span class="${k.type=='up'?'badge-up':'badge-down'}">${k.change}</span>` : ''}
        </div>
        <div class="icon ${k.bg}">
          <iconify-icon icon="${k.icon}" width="20"></iconify-icon>
        </div>
      </div>
    </div>
  </div>`;
    });

    // CHART
    new Chart(document.getElementById('chartUtama'), {
      type: 'bar',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr'],
        datasets: [{
          data: [7800, 7200, 3800, 2000],
          backgroundColor: '#3b82f6',
          borderRadius: 6
        }]
      },
      options: {
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          x: {
            grid: {
              display: false
            }
          },
          y: {
            grid: {
              color: '#eee'
            }
          }
        }
      }
    });

    new Chart(document.getElementById('donutChart'), {
      type: 'doughnut',
      data: {
        datasets: [{
          data: [184884, 158305],
          backgroundColor: ['#1e3a8a', '#93c5fd']
        }]
      },
      options: {
        plugins: {
          legend: {
            display: false
          }
        }
      }
    });
  </script>

</body>