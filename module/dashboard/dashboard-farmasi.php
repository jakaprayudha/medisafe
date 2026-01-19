<style>
/* ===== FARMASI DASHBOARD ===== */
.farmasi-card {
  border: none;
  border-radius: 14px;
  box-shadow: 0 6px 18px rgba(0,0,0,0.08);
  transition: transform .2s ease, box-shadow .2s ease;
}
.farmasi-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 26px rgba(0,0,0,0.12);
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
.farmasi-card h3 {
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
.table-sm th, .table-sm td {
  padding: .55rem .6rem;
}

/* LIST CLEAN */
.list-group-item {
  padding: .6rem .75rem;
}
</style>

<!-- ================= METRIC FARMASI ================= -->
<div class="row g-3">
  <div class="col-md-3">
    <div class="card farmasi-card">
      <div class="card-body d-flex align-items-center">
        <div class="icon-box bg-primary-subtle text-primary">
          <iconify-icon icon="mdi:prescription"></iconify-icon>
        </div>
        <div class="ms-3">
          <h3 class="mb-0">0</h3>
          <small class="text-muted">Resep Internal</small>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card farmasi-card">
      <div class="card-body d-flex align-items-center">
        <div class="icon-box bg-success-subtle text-success">
          <iconify-icon icon="mdi:pill"></iconify-icon>
        </div>
        <div class="ms-3">
          <h3 class="mb-0">0</h3>
          <small class="text-muted">Obat Bebas</small>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card farmasi-card">
      <div class="card-body d-flex align-items-center">
        <div class="icon-box bg-warning-subtle text-warning">
          <iconify-icon icon="mdi:account-clock"></iconify-icon>
        </div>
        <div class="ms-3">
          <h3 class="mb-0">0</h3>
          <small class="text-muted">Menunggu</small>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card farmasi-card">
      <div class="card-body d-flex align-items-center">
        <div class="icon-box bg-info-subtle text-info">
          <iconify-icon icon="mdi:check-decagram"></iconify-icon>
        </div>
        <div class="ms-3">
          <h3 class="mb-0">0</h3>
          <small class="text-muted">Selesai</small>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ================= DAFTAR RESEP ================= -->
<div class="row g-3 mt-1">
  <div class="col-md-8">
    <div class="card farmasi-card">
      <div class="card-body">
        <h6 class="section-title mb-3">
          <iconify-icon icon="mdi:clipboard-text-outline"></iconify-icon>
          Antrian Resep
        </h6>

        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>No Resep</th>
                <th>Pasien</th>
                <th>Jenis</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>R-001</td>
                <td>Siti Aminah</td>
                <td><span class="badge bg-primary">Internal</span></td>
                <td><span class="badge bg-warning">Menunggu</span></td>
                <td>
                  <button class="btn btn-sm btn-success">Proses</button>
                </td>
              </tr>
              <tr>
                <td>R-002</td>
                <td>Budi Santoso</td>
                <td><span class="badge bg-secondary">Obat Bebas</span></td>
                <td><span class="badge bg-success">Selesai</span></td>
                <td>
                  <button class="btn btn-sm btn-secondary" disabled>Selesai</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </div>

  <!-- ================= INFO FARMASI ================= -->
  <div class="col-md-4">
    <div class="card farmasi-card">
      <div class="card-body">
        <h6 class="section-title mb-3">
          <iconify-icon icon="mdi:information-outline"></iconify-icon>
          Informasi Farmasi
        </h6>

        <ul class="list-group list-group-flush">
          <li class="list-group-item">
            Periksa alergi sebelum serahkan obat
          </li>
          <li class="list-group-item">
            Dahulukan resep internal dari dokter
          </li>
          <li class="list-group-item">
            Jelaskan aturan pakai ke pasien
          </li>
          <li class="list-group-item">
            Pastikan stok mencukupi
          </li>
        </ul>

      </div>
    </div>
  </div>
</div>

<!-- ================= CATATAN FARMASI ================= -->
<div class="row mt-3">
  <div class="col-md-12">
    <div class="card farmasi-card">
      <div class="card-body">
        <h6 class="section-title mb-2">
          <iconify-icon icon="mdi:note-text-outline"></iconify-icon>
          Catatan Penting Farmasi
        </h6>
        <ul class="mb-0">
          <li>Gunakan FEFO untuk pengeluaran obat</li>
          <li>Laporkan stok kritis ke admin</li>
          <li>Resep luar dicatat sebagai penjualan bebas</li>
          <li>Pastikan etiket obat jelas & terbaca</li>
        </ul>
      </div>
    </div>
  </div>
</div>