<?php
$title = '10 Diagnosis ICD-10 Tertinggi';
require '../../controller/view.php';
if (($_SESSION['roles'] ?? '') !== 'admin') {
   http_response_code(403);
   exit('Akses laporan tidak diizinkan.');
}
?>
<!doctype html>
<html lang="id">
<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
</head>
<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
    <?php require 'sidebar.php'; ?>
    <div class="body-wrapper">
      <?php require 'navbar.php'; ?>
      <div class="body-wrapper-inner">
        <div class="container-fluid">
          <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-4">
            <h4 class="fw-semibold mb-0">10 Diagnosis ICD-10 Tertinggi</h4>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-success" id="diagnosisExcel"><i class="fas fa-file-excel me-1"></i>Excel</button>
              <button type="button" class="btn btn-primary" id="diagnosisPdf"><i class="fas fa-file-pdf me-1"></i>Cetak / PDF</button>
            </div>
          </div>
          <form id="diagnosisFilters" class="row g-3 align-items-end mb-4">
            <div class="col-sm-4 col-lg-3">
              <label for="diagnosisFrom" class="form-label">Dari Tanggal</label>
              <input type="date" class="form-control" id="diagnosisFrom" value="<?= date('Y-m-01') ?>" required>
            </div>
            <div class="col-sm-4 col-lg-3">
              <label for="diagnosisTo" class="form-label">Sampai Tanggal</label>
              <input type="date" class="form-control" id="diagnosisTo" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="col-sm-4 col-lg-6 d-flex gap-2 flex-wrap">
              <button type="submit" class="btn btn-dark"><i class="fas fa-filter me-1"></i>Terapkan</button>
              <button type="button" class="btn btn-light" id="diagnosisReset"><i class="fas fa-undo me-1"></i>Reset</button>
            </div>
          </form>
          <div id="diagnosisError" class="alert alert-danger d-none" role="alert"></div>
          <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap border-top pt-3 mb-3">
            <h6 class="mb-0">Diagnosis Utama</h6>
            <span id="diagnosisTotal" class="text-muted" aria-live="polite">Memuat data...</span>
          </div>
          <div class="table-responsive">
            <table class="table align-middle" id="diagnosisTable">
              <thead class="table-light">
                <tr><th>Peringkat</th><th>Kode ICD-10</th><th>Diagnosis</th><th class="text-end">Jumlah Kunjungan</th><th class="text-center">Aksi</th></tr>
              </thead>
              <tbody><tr><td colspan="5" class="text-center py-4">Memuat data...</td></tr></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="modal fade" id="diagnosisPatientsModal" tabindex="-1" aria-labelledby="diagnosisPatientsTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="diagnosisPatientsTitle">Daftar Pasien Diagnosis</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div id="diagnosisPatientsPeriod" class="text-muted mb-2"></div>
          <div id="diagnosisPatientsStatus" class="mb-3" role="status" aria-live="polite"></div>
          <div class="table-responsive">
            <table class="table align-middle w-100" id="diagnosisPatientsTable">
              <thead class="table-light"><tr><th>RM</th><th>Nama Pasien</th><th>Gender</th><th>Dokter</th></tr></thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>
  <?php require 'library.php'; ?>
  <script>
    const diagnosisApi = 'controller/report/topDiagnosisController.php';
    const diagnosisForm = document.getElementById('diagnosisFilters');
    const diagnosisBody = document.querySelector('#diagnosisTable tbody');
    const diagnosisError = document.getElementById('diagnosisError');
    const diagnosisTotal = document.getElementById('diagnosisTotal');
    let diagnosisRequest = null;
    let diagnosisPatientsRequest = null;
    const patientsModalElement = document.getElementById('diagnosisPatientsModal');
    const patientsStatus = document.getElementById('diagnosisPatientsStatus');
    const patientsTable = $('#diagnosisPatientsTable').DataTable({
      data: [],
      columns: ['nomor_rm', 'patient_name', 'patient_gender', 'doctor_name'].map(field => ({
        data: field, defaultContent: '-', render: $.fn.dataTable.render.text()
      })),
      pageLength: 10,
      order: [[1, 'asc']],
      language: {
        search: 'Cari Pasien:',
        lengthMenu: 'Tampilkan _MENU_ data',
        info: 'Menampilkan _START_ - _END_ dari _TOTAL_ kunjungan',
        infoEmpty: 'Tidak ada data',
        emptyTable: 'Tidak ada data pasien.',
        zeroRecords: 'Tidak ada pasien yang sesuai pencarian.',
        paginate: { next: 'Berikutnya', previous: 'Sebelumnya' }
      }
    });
    patientsModalElement.addEventListener('shown.bs.modal', () => patientsTable.columns.adjust());
    patientsModalElement.addEventListener('hidden.bs.modal', () => {
      if (diagnosisPatientsRequest) diagnosisPatientsRequest.abort();
      patientsTable.clear().draw();
    });

    function diagnosisParams(format = 'json') {
      if (!diagnosisForm.reportValidity()) return null;
      const fromDate = document.getElementById('diagnosisFrom').value;
      const toDate = document.getElementById('diagnosisTo').value;
      if (fromDate > toDate) {
        diagnosisError.textContent = 'Tanggal mulai tidak boleh melebihi tanggal akhir.';
        diagnosisError.classList.remove('d-none');
        return null;
      }
      diagnosisError.classList.add('d-none');
      return new URLSearchParams({ fromDate, toDate, format });
    }

    async function loadDiagnosis() {
      const params = diagnosisParams();
      if (!params) return;
      if (diagnosisRequest) diagnosisRequest.abort();
      const request = new AbortController();
      diagnosisRequest = request;
      diagnosisBody.innerHTML = '<tr><td colspan="5" class="text-center py-4">Memuat data...</td></tr>';
      diagnosisTotal.textContent = 'Memuat data...';
      try {
        const response = await fetch(diagnosisApi + '?' + params, { signal: request.signal });
        const result = await response.json();
        if (!response.ok || result.status !== 'success') throw new Error(result.message || 'Gagal memuat laporan.');
        diagnosisBody.replaceChildren();
        for (const row of result.data) {
          const tableRow = document.createElement('tr');
          for (const [index, value] of [row.rank, row.icd_code, row.diagnosis_name, Number(row.total_visits).toLocaleString('id-ID')].entries()) {
            const cell = document.createElement('td');
            cell.textContent = value;
            if (index === 3) cell.className = 'text-end fw-semibold';
            tableRow.appendChild(cell);
          }
          const actionCell = document.createElement('td');
          actionCell.className = 'text-center';
          const actionButton = document.createElement('button');
          actionButton.type = 'button';
          actionButton.className = 'btn btn-sm btn-outline-primary diagnosis-patients';
          actionButton.title = 'Lihat pasien ' + row.icd_code;
          actionButton.setAttribute('aria-label', actionButton.title);
          actionButton.innerHTML = '<i class="fas fa-users" aria-hidden="true"></i>';
          actionButton.dataset.code = row.icd_code;
          actionButton.dataset.name = row.diagnosis_name;
          actionButton.dataset.fromDate = result.fromDate;
          actionButton.dataset.toDate = result.toDate;
          actionCell.appendChild(actionButton);
          tableRow.appendChild(actionCell);
          diagnosisBody.appendChild(tableRow);
        }
        if (!result.data.length) diagnosisBody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">Tidak ada data diagnosis pada periode ini.</td></tr>';
        diagnosisTotal.textContent = 'Total kunjungan pada 10 diagnosis: ' + Number(result.total_top10).toLocaleString('id-ID');
      } catch (error) {
        if (error.name === 'AbortError') return;
        diagnosisError.textContent = error.message || 'Gagal terhubung ke server.';
        diagnosisError.classList.remove('d-none');
        diagnosisBody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">Laporan gagal dimuat.</td></tr>';
        diagnosisTotal.textContent = '-';
      }
    }

    diagnosisBody.addEventListener('click', async event => {
      const button = event.target.closest('.diagnosis-patients');
      if (!button) return;
      if (diagnosisPatientsRequest) diagnosisPatientsRequest.abort();
      const request = new AbortController();
      diagnosisPatientsRequest = request;
      document.getElementById('diagnosisPatientsTitle').textContent = button.dataset.code + ' - ' + button.dataset.name;
      document.getElementById('diagnosisPatientsPeriod').textContent = 'Periode: ' + button.dataset.fromDate + ' s/d ' + button.dataset.toDate;
      patientsStatus.className = 'mb-3 text-muted';
      patientsStatus.textContent = 'Memuat daftar pasien...';
      patientsTable.clear().search('').draw();
      bootstrap.Modal.getOrCreateInstance(patientsModalElement).show();
      const params = new URLSearchParams({
        action: 'patients', format: 'json', icd_code: button.dataset.code,
        fromDate: button.dataset.fromDate, toDate: button.dataset.toDate
      });
      try {
        const response = await fetch(diagnosisApi + '?' + params, { signal: request.signal });
        const result = await response.json();
        if (!response.ok || result.status !== 'success') throw new Error(result.message || 'Gagal memuat daftar pasien.');
        patientsTable.clear().rows.add(result.data).draw();
        patientsStatus.textContent = 'Jumlah kunjungan: ' + Number(result.total).toLocaleString('id-ID');
      } catch (error) {
        if (error.name === 'AbortError') return;
        patientsStatus.className = 'mb-3 text-danger';
        patientsStatus.textContent = error.message || 'Gagal memuat daftar pasien.';
      }
    });

    diagnosisForm.addEventListener('submit', event => { event.preventDefault(); loadDiagnosis(); });
    document.getElementById('diagnosisReset').addEventListener('click', () => { diagnosisForm.reset(); loadDiagnosis(); });
    document.getElementById('diagnosisPdf').addEventListener('click', () => {
      const params = diagnosisParams('pdf');
      if (params) window.open(diagnosisApi + '?' + params, '_blank', 'noopener');
    });
    document.getElementById('diagnosisExcel').addEventListener('click', () => {
      const params = diagnosisParams('excel');
      if (params) window.location.href = diagnosisApi + '?' + params;
    });
    loadDiagnosis();
  </script>
</body>
</html>