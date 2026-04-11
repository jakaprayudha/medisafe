<style>
  /* ===== KASIR & KEUANGAN DASHBOARD ===== */
  .kasir-card {
    border: none;
    border-radius: 14px;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
    transition: transform .2s ease, box-shadow .2s ease;
  }

  .kasir-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 26px rgba(0, 0, 0, 0.12);
  }

  /* ICON BOX */
  .icon-box {
    width: 64px;
    height: 64px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .icon-box iconify-icon {
    font-size: 36px;
  }

  /* METRIC */
  .kasir-card h3 {
    font-size: 32px;
    font-weight: 700;
    line-height: 1.1;
  }

  /* SECTION TITLE */
  .section-title {
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
  }

  /* TABLE COMPACT */
  .table-sm th,
  .table-sm td {
    padding: .55rem .6rem;
  }

  /* LIST CLEAN */
  .list-group-item {
    padding: .6rem .75rem;
  }
</style>

<!-- ================= METRIC KASIR ================= -->
<div class="row g-3">
  <div class="col-md-3">
    <div class="card kasir-card">
      <div class="card-body d-flex align-items-center">
        <div class="icon-box bg-primary-subtle text-primary">
          <iconify-icon icon="mdi:receipt-text"></iconify-icon>
        </div>
        <div class="ms-3">
          <h3 class="mb-0" id="total_transaksi">0</h3>
          <small class="text-muted">Transaksi Hari Ini</small>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card kasir-card">
      <div class="card-body d-flex align-items-center">
        <div class="icon-box bg-success-subtle text-success">
          <iconify-icon icon="mdi:cash-multiple"></iconify-icon>
        </div>
        <div class="ms-3">
          <h3 class="mb-0" id="total_pendapatan">Rp 0</h3>
          <small class="text-muted">Pendapatan Hari Ini</small>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card kasir-card">
      <div class="card-body d-flex align-items-center">
        <div class="icon-box bg-warning-subtle text-warning">
          <iconify-icon icon="mdi:clock-alert-outline"></iconify-icon>
        </div>
        <div class="ms-3">
          <h3 class="mb-0" id="belum_bayar">0</h3>
          <small class="text-muted">Tagihan Menunggu</small>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card kasir-card">
      <div class="card-body d-flex align-items-center">
        <div class="icon-box bg-info-subtle text-info">
          <iconify-icon icon="mdi:credit-card-outline"></iconify-icon>
        </div>
        <div class="ms-3">
          <h3 class="mb-0" id="non_tunai">0</h3>
          <small class="text-muted">Pembayaran Non-Tunai</small>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ================= TRANSAKSI & METODE BAYAR ================= -->
<div class="row g-3 mt-1">
  <div class="col-md-8">
    <div class="card kasir-card">
      <div class="card-body">
        <h6 class="section-title mb-3">
          <iconify-icon icon="mdi:clipboard-list-outline"></iconify-icon>
          Transaksi Terbaru
        </h6>

        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>No Transaksi</th>
                <th>Pasien</th>
                <th>Jenis</th>
                <th>Total</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>

      </div>
    </div>
  </div>

  <!-- ================= METODE PEMBAYARAN ================= -->
  <div class="col-md-4">
    <div class="card kasir-card">
      <div class="card-body">
        <h6 class="section-title mb-3">
          <iconify-icon icon="mdi:wallet-outline"></iconify-icon>
          Metode Pembayaran
        </h6>

        <ul class="list-group list-group-flush">
          <li class="list-group-item d-flex justify-content-between">
            Tunai
            <span class="badge bg-primary">45%</span>
          </li>
          <li class="list-group-item d-flex justify-content-between">
            Transfer
            <span class="badge bg-info">30%</span>
          </li>
          <li class="list-group-item d-flex justify-content-between">
            QRIS
            <span class="badge bg-success">20%</span>
          </li>
          <li class="list-group-item d-flex justify-content-between">
            BPJS
            <span class="badge bg-secondary">5%</span>
          </li>
        </ul>

      </div>
    </div>
  </div>
</div>

<!-- ================= CATATAN KEUANGAN ================= -->
<div class="row mt-3">
  <div class="col-md-12">
    <div class="card kasir-card">
      <div class="card-body">
        <h6 class="section-title mb-2">
          <iconify-icon icon="mdi:note-text-outline"></iconify-icon>
          Catatan Penting Kasir & Keuangan
        </h6>
        <ul class="mb-0">
          <li>Pastikan transaksi ditutup sebelum pergantian shift</li>
          <li>Cek kesesuaian pembayaran tunai dan sistem</li>
          <li>Tagihan BPJS dicatat terpisah</li>
          <li>Laporan harian dicetak sebelum logout</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<script>
  fetch('controller/dashboard/kasir.php')
    .then(res => res.json())
    .then(resp => {

      if (resp.status !== 'success') return;

      let html = '';

      resp.data.forEach(row => {

        let status = row.status_bayar == 1 ?
          `<span class="badge bg-success">Lunas</span>` :
          `<span class="badge bg-warning">Belum Bayar</span>`;

        let metode = row.metode_bayar ?? '-';

        html += `
        <tr>
          <td>${row.visit_ID}</td>
          <td>${row.patient_name_pcare ?? '-'}</td>
          <td>${row.source_hub ?? '-'}</td>
          <td>Rp ${(row.amount_results || 0).toLocaleString('id-ID')}</td>
          <td>${status}</td>
          <td><span class="badge bg-info">${metode}</span></td>
        </tr>
      `;
      });

      $('table tbody').html(html);

      // ================= METRIC =================
      $('#total_transaksi').text(resp.summary.total_transaksi || 0);

      $('#total_pendapatan').text(
        'Rp ' + (resp.summary.total_pendapatan || 0).toLocaleString('id-ID')
      );

      $('#belum_bayar').text(resp.summary.belum_bayar || 0);
      $('#non_tunai').text(resp.summary.non_tunai || 0);

      // ================= METODE BAYAR =================
      let metodeHtml = '';

      for (let key in resp.metode) {
        metodeHtml += `
        <li class="list-group-item d-flex justify-content-between">
          ${key}
          <span class="badge bg-primary">${resp.metode[key]}%</span>
        </li>
      `;
      }

      $('.list-group').html(metodeHtml);

    });
</script>