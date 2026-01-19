<style>
/* ===== ADMISI DASHBOARD ===== */
.admisi-card {
  border: none;
  border-radius: 14px;
  box-shadow: 0 6px 18px rgba(0,0,0,0.08);
  transition: transform .2s ease, box-shadow .2s ease;
}
.admisi-card:hover {
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
.admisi-card h3 {
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

<!-- ================= METRIC CARDS ================= -->
<div class="row g-3">
  <div class="col-md-3">
    <div class="card admisi-card">
      <div class="card-body d-flex align-items-center">
        <div class="icon-box bg-primary-subtle text-primary">
          <iconify-icon icon="mdi:account-multiple"></iconify-icon>
        </div>
        <div class="ms-3">
          <h3 class="mb-0">0</h3>
          <small class="text-muted">Pasien Hari Ini</small>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card admisi-card">
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
    <div class="card admisi-card">
      <div class="card-body d-flex align-items-center">
        <div class="icon-box bg-info-subtle text-info">
          <iconify-icon icon="mdi:bullhorn"></iconify-icon>
        </div>
        <div class="ms-3">
          <h3 class="mb-0">0</h3>
          <small class="text-muted">Antrian Aktif</small>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card admisi-card">
      <div class="card-body d-flex align-items-center">
        <div class="icon-box bg-success-subtle text-success">
          <iconify-icon icon="mdi:check-circle"></iconify-icon>
        </div>
        <div class="ms-3">
          <h3 class="mb-0">0</h3>
          <small class="text-muted">Selesai</small>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ================= SCHEDULE & ROOMS ================= -->
<div class="row g-3 mt-1">
  <div class="col-md-8">
    <div class="card admisi-card">
      <div class="card-body">
        <h6 class="section-title mb-3">
          <iconify-icon icon="mdi:calendar-clock"></iconify-icon>
          Jadwal Dokter Hari Ini
        </h6>

        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Dokter</th>
                <th>Poli</th>
                <th>Jam</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>dr. Ahmad Santoso</td>
                <td>Poli Umum</td>
                <td>08:00 - 12:00</td>
                <td><span class="badge bg-success">Praktek</span></td>
              </tr>
              <tr>
                <td>drg. Siti Rahma</td>
                <td>Poli Gigi</td>
                <td>09:00 - 13:00</td>
                <td><span class="badge bg-success">Praktek</span></td>
              </tr>
              <tr>
                <td>dr. Budi Hartono</td>
                <td>Poli Anak</td>
                <td>13:00 - 17:00</td>
                <td><span class="badge bg-warning">Menunggu</span></td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card admisi-card">
      <div class="card-body">
        <h6 class="section-title mb-3">
          <iconify-icon icon="mdi:hospital-building"></iconify-icon>
          Poli / Ruangan Aktif
        </h6>

        <ul class="list-group list-group-flush">
          <li class="list-group-item d-flex justify-content-between align-items-center">
            Poli Umum <span class="badge bg-success">Buka</span>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            Poli Gigi <span class="badge bg-success">Buka</span>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            Poli Anak <span class="badge bg-warning">Siang</span>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            Poli Kebidanan <span class="badge bg-danger">Tutup</span>
          </li>
        </ul>

      </div>
    </div>
  </div>
</div>

<!-- ================= INFO ADMISI ================= -->
<div class="row mt-3">
  <div class="col-md-12">
    <div class="card admisi-card">
      <div class="card-body">
        <h6 class="section-title mb-2">
          <iconify-icon icon="mdi:information-outline"></iconify-icon>
          Informasi Penting Admisi
        </h6>
        <ul class="mb-0">
          <li>Pastikan identitas pasien sesuai KTP / BPJS</li>
          <li>Cek jadwal dokter sebelum menentukan poli</li>
          <li>Gunakan menu Display untuk memanggil pasien</li>
          <li>Perhatikan status poli (buka / tutup)</li>
        </ul>
      </div>
    </div>
  </div>
</div>