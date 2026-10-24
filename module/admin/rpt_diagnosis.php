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
                <tr><th>Peringkat</th><th>Kode ICD-10</th><th>Diagnosis</th><th class="text-end">Jumlah Kunjungan</th></tr>
              </thead>
              <tbody><tr><td colspan="4" class="text-center py-4">Memuat data...</td></tr></tbody>
            </table>
          </div>
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
      diagnosisBody.innerHTML = '<tr><td colspan="4" class="text-center py-4">Memuat data...</td></tr>';
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
          diagnosisBody.appendChild(tableRow);
        }
        if (!result.data.length) diagnosisBody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">Tidak ada data diagnosis pada periode ini.</td></tr>';
        diagnosisTotal.textContent = 'Total kunjungan pada 10 diagnosis: ' + Number(result.total_top10).toLocaleString('id-ID');
      } catch (error) {
        if (error.name === 'AbortError') return;
        diagnosisError.textContent = error.message || 'Gagal terhubung ke server.';
        diagnosisError.classList.remove('d-none');
        diagnosisBody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">Laporan gagal dimuat.</td></tr>';
        diagnosisTotal.textContent = '-';
      }
    }

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