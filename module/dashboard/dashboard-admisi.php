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
          <h3 class="mb-0" id="totalPasien">0</h3>
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
          <h3 class="mb-0" id="totalMenunggu">0</h3>
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
          <h3 class="mb-0" id="totalAntrian">0</h3>
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
          <h3 class="mb-0" id="totalSelesai">0</h3>
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
           <tbody id="jadwalDokter"></tbody>
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

        <ul class="list-group list-group-flush" id="listPoli">
        </ul>

      </div>
    </div>
  </div>
</div>

<!-- ================= INFO ADMISI ================= -->
<div class="row mt-3">
  <div class="col-md-12">
    <div class="card admisi-card border-start border-4 border-info">
      <div class="card-body">

        <div class="d-flex align-items-center mb-3">
          <div class="icon-box bg-info-subtle text-info me-2" style="width:42px;height:42px;">
            <iconify-icon icon="mdi:information-outline" style="font-size:22px;"></iconify-icon>
          </div>
          <h6 class="section-title mb-0">Informasi Penting Admisi</h6>
        </div>

        <ul class="list-group list-group-flush">
          <li class="list-group-item d-flex align-items-start">
            <iconify-icon icon="mdi:card-account-details-outline"
              class="text-primary me-2" style="font-size:18px;"></iconify-icon>
            <span>Pastikan identitas pasien sesuai <strong>KTP / BPJS</strong></span>
          </li>

          <li class="list-group-item d-flex align-items-start">
            <iconify-icon icon="mdi:calendar-check-outline"
              class="text-success me-2" style="font-size:18px;"></iconify-icon>
            <span>Cek <strong>jadwal dokter</strong> sebelum menentukan poli</span>
          </li>

          <li class="list-group-item d-flex align-items-start">
            <iconify-icon icon="mdi:monitor-eye"
              class="text-info me-2" style="font-size:18px;"></iconify-icon>
            <span>Gunakan menu <strong>Display</strong> untuk memanggil pasien</span>
          </li>

          <li class="list-group-item d-flex align-items-start">
            <iconify-icon icon="mdi:door-open"
              class="text-warning me-2" style="font-size:18px;"></iconify-icon>
            <span>Perhatikan status poli: <strong>Buka / Tutup</strong></span>
          </li>
        </ul>

      </div>
    </div>
  </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", () => {
   loadAdmisiDashboard();
});

function loadAdmisiDashboard() {
   fetch('controller/dashboard/admisi.php')
      .then(res => res.json())
      .then(res => {
         if (res.status !== 'success') return;

         renderMetrics(res.metrics);
         renderJadwal(res.jadwal_dokter);
         renderPoli(res.poli);
      })
      .catch(err => console.error(err));
}

/* ================= METRICS ================= */
function renderMetrics(data) {
   document.getElementById('totalPasien').textContent   = data.pasien_hari_ini;
   document.getElementById('totalMenunggu').textContent = data.menunggu;
   document.getElementById('totalAntrian').textContent  = data.antrian_aktif;
   document.getElementById('totalSelesai').textContent  = data.selesai;
}

/* ================= JADWAL DOKTER ================= */
function renderJadwal(rows) {
   const tbody = document.getElementById('jadwalDokter');
   tbody.innerHTML = '';

   if (!rows.length) {
      tbody.innerHTML = `
         <tr>
            <td colspan="4" class="text-center text-muted">
               Tidak ada jadwal hari ini
            </td>
         </tr>`;
      return;
   }

   rows.forEach(r => {
      const badge =
         r.status === 'praktek'
            ? 'bg-success'
            : r.status === 'menunggu'
            ? 'bg-warning'
            : 'bg-secondary';

      tbody.innerHTML += `
         <tr>
            <td>${r.nama_dokter}</td>
            <td>${r.poli}</td>
            <td>${r.jam_mulai} - ${r.jam_selesai}</td>
            <td><span class="badge ${badge}">${r.status_label}</span></td>
         </tr>
      `;
   });
}

/* ================= POLI ================= */
function renderPoli(rows) {
   const ul = document.getElementById('listPoli');
   ul.innerHTML = '';

   rows.forEach(p => {
      const badge =
         p.status === '1'
            ? 'bg-success'
            : p.status === '0'
            ? 'bg-warning'
            : 'bg-danger';

      ul.innerHTML += `
         <li class="list-group-item d-flex justify-content-between align-items-center">
            ${p.nama_poli}
            <span class="badge ${badge}">${p.label}</span>
         </li>
      `;
   });
}
</script>