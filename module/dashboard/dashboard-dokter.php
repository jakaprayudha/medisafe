<style>
/* ===== DOKTER DASHBOARD ===== */
.doctor-card {
  border: none;
  border-radius: 14px;
  box-shadow: 0 6px 18px rgba(0,0,0,0.08);
  transition: transform .2s ease, box-shadow .2s ease;
}
.doctor-card:hover {
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
.doctor-card h3 {
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

<!-- ================= METRIC DOKTER ================= -->
<div class="row g-3">
  <div class="col-md-3">
    <div class="card doctor-card">
      <div class="card-body d-flex align-items-center">
        <div class="icon-box bg-primary-subtle text-primary">
          <iconify-icon icon="mdi:account-heart"></iconify-icon>
        </div>
        <div class="ms-3">
          <h3 class="mb-0">0</h3>
          <small class="text-muted">Pasien Hari Ini</small>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card doctor-card">
      <div class="card-body d-flex align-items-center">
        <div class="icon-box bg-warning-subtle text-warning">
          <iconify-icon icon="mdi:account-clock-outline"></iconify-icon>
        </div>
        <div class="ms-3">
          <h3 class="mb-0">0</h3>
          <small class="text-muted">Menunggu</small>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card doctor-card">
      <div class="card-body d-flex align-items-center">
        <div class="icon-box bg-info-subtle text-info">
          <iconify-icon icon="mdi:stethoscope"></iconify-icon>
        </div>
        <div class="ms-3">
          <h3 class="mb-0">0</h3>
          <small class="text-muted">Sedang Diperiksa</small>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card doctor-card">
      <div class="card-body d-flex align-items-center">
        <div class="icon-box bg-success-subtle text-success">
          <iconify-icon icon="mdi:clipboard-check"></iconify-icon>
        </div>
        <div class="ms-3">
          <h3 class="mb-0">0</h3>
          <small class="text-muted">Selesai</small>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ================= ANTRIAN PASIEN ================= -->
<div class="row g-3 mt-1">
  <div class="col-md-8">
    <div class="card doctor-card">
      <div class="card-body">
        <h6 class="section-title mb-3">
          <iconify-icon icon="mdi:clipboard-list-outline"></iconify-icon>
          Antrian Pasien
        </h6>

        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>No</th>
                <th>Nama Pasien</th>
                <th>Keluhan</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>U010</td>
                <td>Siti Aminah</td>
                <td>Demam</td>
                <td><span class="badge bg-warning">Menunggu</span></td>
                <td>
                  <button class="btn btn-sm btn-primary">
                    Periksa
                  </button>
                </td>
              </tr>
              <tr>
                <td>U011</td>
                <td>Budi Santoso</td>
                <td>Batuk</td>
                <td><span class="badge bg-info">Diperiksa</span></td>
                <td>
                  <button class="btn btn-sm btn-secondary" disabled>
                    Proses
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </div>

  <!-- ================= JADWAL PRAKTEK ================= -->
  <div class="col-md-4">
    <div class="card doctor-card">
      <div class="card-body">
        <h6 class="section-title mb-3">
          <iconify-icon icon="mdi:calendar-check"></iconify-icon>
          Jadwal Praktek Saya
        </h6>

        <ul class="list-group list-group-flush">
          <li class="list-group-item d-flex justify-content-between">
            Poli Umum
            <span class="badge bg-success">08:00 - 12:00</span>
          </li>
          <li class="list-group-item d-flex justify-content-between">
            Poli Umum
            <span class="badge bg-warning">13:00 - 17:00</span>
          </li>
        </ul>

      </div>
    </div>
  </div>
</div>

<!-- ================= CATATAN DOKTER ================= -->
<div class="row mt-3">
  <div class="col-md-12">
    <div class="card doctor-card">
      <div class="card-body">
        <h6 class="section-title mb-2">
          <iconify-icon icon="mdi:note-text-outline"></iconify-icon>
          Catatan & Pengingat
        </h6>
        <ul class="mb-0">
          <li>Lengkapi SOAP sebelum menyelesaikan pemeriksaan</li>
          <li>Pastikan resep dikirim ke farmasi</li>
          <li>Periksa alergi pasien sebelum input obat</li>
          <li>Gunakan ICD dengan tepat</li>
        </ul>
      </div>
    </div>
  </div>
</div>