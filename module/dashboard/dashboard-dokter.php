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
              </tr>
            </thead>
          <tbody></tbody>
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

        <ul class="list-group list-group-flush" id="doctorSchedule">
          <li class="list-group-item text-center text-muted">
            Memuat jadwal...
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>

<!-- ================= CATATAN DOKTER ================= -->
<div class="row mt-3">
  <div class="col-md-12">
    <div class="card doctor-card border-start border-4 border-info">
      <div class="card-body">

        <!-- HEADER -->
        <div class="d-flex align-items-center mb-3">
          <div class="icon-box bg-info-subtle text-info me-2" style="width:42px;height:42px;">
            <iconify-icon icon="mdi:note-text-outline" style="font-size:22px;"></iconify-icon>
          </div>
          <h6 class="section-title mb-0">Catatan & Pengingat Dokter</h6>
        </div>

        <!-- LIST -->
        <ul class="list-group list-group-flush">
          <li class="list-group-item d-flex align-items-start">
            <iconify-icon icon="mdi:clipboard-text-outline"
              class="text-primary me-2" style="font-size:18px;"></iconify-icon>
            <span>Lengkapi <strong>SOAP</strong> sebelum menyelesaikan pemeriksaan</span>
          </li>

          <li class="list-group-item d-flex align-items-start">
            <iconify-icon icon="mdi:prescription"
              class="text-success me-2" style="font-size:18px;"></iconify-icon>
            <span>Pastikan <strong>resep</strong> telah dikirim ke farmasi</span>
          </li>

          <li class="list-group-item d-flex align-items-start">
            <iconify-icon icon="mdi:alert-circle-outline"
              class="text-warning me-2" style="font-size:18px;"></iconify-icon>
            <span>Periksa <strong>alergi pasien</strong> sebelum input obat</span>
          </li>

          <li class="list-group-item d-flex align-items-start">
            <iconify-icon icon="mdi:book-open-page-variant-outline"
              class="text-info me-2" style="font-size:18px;"></iconify-icon>
            <span>Gunakan <strong>kode ICD</strong> dengan tepat</span>
          </li>
        </ul>

      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
   loadDoctorDashboard();
});

function loadDoctorDashboard() {
   // Bisa diganti dari session PHP
  
const doctorName = <?= json_encode($_SESSION['fullname'] ?? '') ?>;
   fetch(`controller/dashboard/doctor.php?doctor_name=${encodeURIComponent(doctorName)}`)
      .then(res => res.json())
      .then(res => {
         if (res.status !== 'success') {
            alert(res.message);
            return;
         }

         renderMetric(res.metric);
         renderQueue(res.queue);
        renderSchedule(res.schedule);
      });
}

/* ================= METRIC ================= */
function renderMetric(m) {
   document.querySelectorAll('.doctor-card h3')[0].textContent = m.total;
   document.querySelectorAll('.doctor-card h3')[1].textContent = m.menunggu;
   document.querySelectorAll('.doctor-card h3')[2].textContent = m.diperiksa;
   document.querySelectorAll('.doctor-card h3')[3].textContent = m.selesai;
}

/* ================= QUEUE TABLE ================= */
function renderQueue(rows) {
   const tbody = document.querySelector('tbody');
   tbody.innerHTML = '';

   if (!rows.length) {
      tbody.innerHTML = `
        <tr>
          <td colspan="5" class="text-center text-muted">
            Tidak ada antrean
          </td>
        </tr>`;
      return;
   }

   rows.forEach((r) => {
      let badge = 'secondary';
      let label = 'Tidak diketahui';

      // status: 0=menunggu, 1=diperiksa, 2=selesai
      if (r.visit_status === 0) {
         badge = 'warning';
         label = 'Menunggu';
      }
      else if (r.visit_status === 1) {
         badge = 'info';
         label = 'Diperiksa';
      }
      else if (r.visit_status === 2) {
         badge = 'success';
         label = 'Selesai';
      }

      tbody.innerHTML += `
        <tr>
          <td>${r.visit_antrian}</td>
          <td>${r.patient_name}</td>
          <td>${r.visit_notes ?? '-'}</td>
          <td>
            <span class="badge bg-${badge}">
              ${label}
            </span>
          </td>
        </tr>
      `;
   });
}

function renderSchedule(rows) {
   const ul = document.getElementById('doctorSchedule');
   ul.innerHTML = '';

   if (!rows.length) {
      ul.innerHTML = `
        <li class="list-group-item text-center text-muted">
          Tidak ada jadwal praktek
        </li>`;
      return;
   }

   rows.forEach(r => {
      ul.innerHTML += `
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <div>
            <strong>${r.poli_name}</strong><br>
            <small class="text-muted">${r.day_of_week}</small>
          </div>
          <span class="badge bg-success">
            ${r.start_time} - ${r.end_time}
          </span>
        </li>
      `;
   });
}
</script>