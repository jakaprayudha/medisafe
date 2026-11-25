<?php
$title = 'Data Import';
require '../../controller/view.php';

// Import massal data faskes: hanya superadmin/administrator.
if (!isset($_SESSION['uid_user']) || !in_array($_SESSION['roles'] ?? '', ['superadmin', 'administrator'], true)) {
  header('Location: ../../');
  exit;
}
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .di-kpi { border: 1px solid #e2e8f0; border-radius: 14px; padding: 14px 16px; background: #fff; height: 100%; }
    .di-kpi .n { font-size: 24px; font-weight: 800; color: #0f172a; line-height: 1.1; }
    .di-kpi .l { font-size: 12px; font-weight: 600; color: #334155; margin-top: 2px; }
    .di-chip { border: 1px solid #cbd5e1; background: #fff; color: #334155; border-radius: 999px; padding: 4px 12px; font-size: 12px; font-weight: 700; cursor: pointer; }
    .di-chip:hover { background: #f1f5f9; }
    .di-chip.on { background: #1e293b; border-color: #1e293b; color: #fff; }
    table.di-t th { font-size: 11px; font-weight: 700; color: #334155; white-space: nowrap; text-transform: uppercase; letter-spacing: .3px; }
    table.di-t td { vertical-align: middle; font-size: 13px; color: #0f172a; }
    .di-nama { font-weight: 700; color: #0f172a; min-width: 190px; }
    .di-sub { font-size: 11px; color: #475569; }
    .di-c { display: inline-block; min-width: 46px; text-align: center; padding: 3px 7px; border-radius: 7px; font-size: 12px; font-weight: 700; }
    .di-c.g { background: #dcfce7; color: #166534; }
    .di-c.r { background: #fee2e2; color: #991b1b; }
    .di-b { display: inline-flex; align-items: center; gap: 4px; padding: 3px 9px; border-radius: 7px; font-size: 11px; font-weight: 700; }
    .di-b.g { background: #dcfce7; color: #166534; }
    .di-b.b { background: #dbeafe; color: #1e40af; }
    .di-b.x { background: #f1f5f9; color: #334155; }
    .di-b.r { background: #fee2e2; color: #991b1b; }
    .di-info { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px; color: #1e3a8a; font-size: 13px; }
    .di-modal .modal-content { border: 0; border-radius: 16px; }
    .di-modal .form-label { font-weight: 600; color: #1e293b; font-size: 13px; }
    .di-modal .is-invalid { border-color: #dc3545; }
    .di-hint { color: #475569; font-size: 12px; }
    #previewTable th { font-size: 11px; white-space: nowrap; color: #334155; background: #f8fafc; }
    #previewTable td { font-size: 12px; white-space: nowrap; max-width: 220px; overflow: hidden; text-overflow: ellipsis; color: #0f172a; }
    .di-skel { display: inline-block; height: 1.2rem; width: 56px; border-radius: 6px; background: linear-gradient(90deg, #e2e8f0 25%, #f1f5f9 50%, #e2e8f0 75%); background-size: 200% 100%; animation: dis 1.2s infinite; }
    @keyframes dis { to { background-position: -200% 0; } }
    .spin { animation: disp 1s linear infinite; }
    @keyframes disp { to { transform: rotate(360deg); } }
  </style>
</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
    <?php require '../admin/sidebar.php'; ?>
    <div class="body-wrapper">
      <?php require '../admin/navbar-master.php'; ?>
      <div class="body-wrapper-inner">
        <div class="container-fluid">

          <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
              <h4 class="fw-bold mb-0 text-dark"><i class="ti ti-download text-primary me-2"></i>Data Import</h4>
              <div class="text-dark small">Unggah data massal (pasien, farmasi) dari Excel ke faskes tertentu dan pantau kelengkapan datanya.</div>
            </div>
            <button class="btn btn-primary" id="btnImport"><i class="ti ti-upload me-1"></i>Import Data</button>
          </div>

          <div class="di-info p-3 mb-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
              <b>Sebelum import:</b> pakai template yang disediakan, pastikan kolom sesuai, dan lengkapi dulu data pendukung (poli, dokter, dsb.) di sistem.
              Data yang sudah ada (NIK/No. RM sama) dilewati sebagai duplikat.
            </div>
            <div class="dropdown">
              <button class="btn btn-sm btn-light border dropdown-toggle" data-bs-toggle="dropdown"><i class="ti ti-file-spreadsheet me-1"></i>Template Excel</button>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="templates/pasien.xlsx" target="_blank" rel="noopener">Master Pasien</a></li>
                <li><a class="dropdown-item" href="templates/dokter.xlsx" target="_blank" rel="noopener">Master Dokter</a></li>
                <li><a class="dropdown-item" href="templates/farmasi.xlsx" target="_blank" rel="noopener">Master Farmasi</a></li>
                <li><a class="dropdown-item" href="templates/visit.xlsx" target="_blank" rel="noopener">Master Visit</a></li>
                <li><a class="dropdown-item" href="templates/faskes.xlsx" target="_blank" rel="noopener">Master Faskes</a></li>
              </ul>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-6 col-xl-3"><div class="di-kpi"><div class="n" id="kFaskes"><span class="di-skel"></span></div><div class="l">Faskes aktif</div></div></div>
            <div class="col-6 col-xl-3"><div class="di-kpi"><div class="n" id="kLengkap"><span class="di-skel"></span></div><div class="l">Data dasar lengkap (8 jenis)</div></div></div>
            <div class="col-6 col-xl-3"><div class="di-kpi"><div class="n" id="kKosong"><span class="di-skel"></span></div><div class="l">Belum ada data pasien</div></div></div>
            <div class="col-6 col-xl-3"><div class="di-kpi"><div class="n" id="kJob"><span class="di-skel"></span></div><div class="l" id="kJobL">Import berjalan / gagal</div></div></div>
          </div>

          <div class="card border-0 shadow-sm mb-3" style="border-radius:16px">
            <div class="card-body p-3 p-md-4">
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                <div class="fw-semibold text-dark">Kelengkapan data per faskes</div>
                <div class="d-flex flex-wrap gap-2" id="fkFilter">
                  <button type="button" class="di-chip on" data-f="">Semua</button>
                  <button type="button" class="di-chip" data-f="kurang">Ada yang kosong</button>
                  <button type="button" class="di-chip" data-f="pasien">Belum ada pasien</button>
                </div>
              </div>
              <div class="table-responsive" id="fkWrap">
                <table class="table table-hover align-middle w-100 di-t" id="faskesTable">
                  <thead>
                    <tr>
                      <th class="text-center" width="70">Aksi</th>
                      <th>Faskes</th>
                      <th class="text-center">Pasien</th>
                      <th class="text-center">Dokter</th>
                      <th class="text-center">Nakes</th>
                      <th class="text-center">Farmasi</th>
                      <th class="text-center">Visit</th>
                      <th class="text-center">Poli</th>
                      <th class="text-center">Tarif</th>
                      <th class="text-center">Provider</th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                </table>
              </div>
            </div>
          </div>

          <div class="card border-0 shadow-sm" style="border-radius:16px">
            <div class="card-body p-3 p-md-4">
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                <div class="fw-semibold text-dark"><i class="ti ti-history me-1"></i>Riwayat Import</div>
                <div class="d-flex align-items-center gap-2">
                  <span class="di-sub" id="jobsInfo"></span>
                  <button type="button" class="btn btn-light border btn-sm" id="jobsRefresh" title="Muat ulang"><i class="ti ti-refresh"></i></button>
                </div>
              </div>
              <div class="table-responsive">
                <table class="table table-hover align-middle w-100 di-t" id="jobsTable">
                  <thead>
                    <tr>
                      <th class="text-center" width="80">Aksi</th>
                      <th>Waktu</th>
                      <th>Tipe</th>
                      <th class="text-end">Total</th>
                      <th class="text-end">Berhasil</th>
                      <th class="text-end">Duplikat</th>
                      <th class="text-end">Error</th>
                      <th style="min-width:120px">Progress</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                </table>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <div class="modal fade di-modal" id="importModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <div>
            <h5 class="modal-title fw-bold text-dark">Import Data</h5>
            <small class="text-dark">File Excel (.xlsx / .xls), maksimal 20 MB.</small>
          </div>
          <button class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label" for="faskesSelect">Faskes tujuan <span class="text-danger">*</span></label>
              <select id="faskesSelect" class="form-select"><option value="">-- Pilih Faskes --</option></select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label" for="importType">Jenis data <span class="text-danger">*</span></label>
              <select id="importType" class="form-select">
                <option value="">-- Pilih --</option>
                <option value="pasien">Master Pasien</option>
                <option value="farmasi">Master Farmasi</option>
              </select>
            </div>
            <div class="col-12 mb-3">
              <label class="form-label" for="importFile">File Excel <span class="text-danger">*</span></label>
              <input type="file" id="importFile" class="form-control" accept=".xlsx,.xls">
              <div class="di-hint mt-1" id="fileInfo"></div>
            </div>
          </div>
          <div id="previewArea" style="display:none">
            <div class="fw-semibold text-dark mb-1">Pratinjau <span class="di-hint" id="previewInfo"></span></div>
            <div class="table-responsive border rounded" style="max-height:280px;overflow:auto">
              <table class="table table-sm mb-0" id="previewTable"><thead></thead><tbody></tbody></table>
            </div>
          </div>
          <div id="progressArea" style="display:none" class="mt-3">
            <div class="fw-semibold text-dark mb-1">Sedang memproses…</div>
            <div class="progress mb-2" style="height:22px">
              <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" id="progressBar" role="progressbar" style="width:0%">0%</div>
            </div>
            <div id="progressStatus" class="di-hint text-center">Menunggu proses dimulai…</div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-light" data-bs-dismiss="modal" id="btnTutup">Tutup</button>
          <button class="btn btn-success" id="btnUpload" disabled><i class="ti ti-player-play me-1"></i>Proses Import</button>
        </div>
      </div>
    </div>
  </div>

  <?php
  require '../admin/library.php';
  ?>
  <script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
</body>

<script>
  const apiUrl = 'controller/master/faskesImportController';
  $(document).ready(function() {
    var table = $('#periodeTable').DataTable({
      processing: true,
      serverSide: false, // 🔹 ubah jadi false
      scrollX: true,
      ajax: {
        url: apiUrl,
        type: "GET",
        dataSrc: function(json) {
          return json.data.map(function(row) {
            return {
              code: row.faskes_code ?? '-',
              name: row.clinic_name ?? '-',
              pasien: row.total_pasien > 0 ? '✔️' : '❌',
              dokter: row.total_dokter > 0 ? '✔️' : '❌',
              nakes: row.total_nakes > 0 ? '✔️' : '❌',
              farmasi: row.total_farmasi > 0 ? '✔️' : '❌',
              visit: row.total_visit > 0 ? '✔️' : '❌',
              poli: row.total_poli > 0 ? '✔️' : '❌',
              tarif: row.total_tarif > 0 ? '✔️' : '❌',
              provider: row.total_provider > 0 ? '✔️' : '❌'
            };
          });
        }
      },
      columns: [{
          data: "code"
        },
        {
          data: "name"
        },
        {
          data: "pasien"
        },
        {
          data: "dokter"
        },
        {
          data: "nakes"
        },
        {
          data: "farmasi"
        },
        {
          data: "visit"
        },
        {
          data: "poli"
        },
        {
          data: "tarif"
        },
        {
          data: "provider"
        }
      ],
      footerCallback: function(row, data, start, end, display) {
        var api = this.api();
      }
    });

    $('#customSearch').on('keyup', function() {
      table.search(this.value).draw();
    });

    // 🔹 Tambah
    $('#btnTambah').on('click', function() {
      $('#programForm')[0].reset(); // ✅ pakai programForm, bukan addForm
      $('#id_faskes').val('');
      $('#programModal .modal-title').text('Tambah Data');
      $('#programModal').modal('show');
    });

    // 🔹 Submit (Tambah / Update)
    $('#programForm').on('submit', function(e) {
      e.preventDefault();
      let formData = new URLSearchParams(new FormData(this));
      let id = $('#id_faskes').val();

      fetch(apiUrl + (id ? `?id=${id}` : ''), {
          method: id ? 'PUT' : 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: formData
        })
        .then(res => res.json())
        .then(data => {
          if (data.status === 'success') {
            Swal.fire('Berhasil!', data.message, 'success');
            $('#programModal').modal('hide');
            table.ajax.reload(null, false);
          } else {
            Swal.fire('Gagal!', data.message, 'error');
          }
        });
    });
    // 🔹 Edit
    $(document).on('click', '.edit-btn', function() {
      let id = $(this).data('id');
      fetch(apiUrl + `?id=${id}`)
        .then(res => res.json())
        .then(resp => {
          if (resp.status === 'success') {
            let d = resp.data;

            // isi otomatis berdasarkan name field
            for (let key in d) {
              $(`[name="${key}"]`).val(d[key]);
            }

            $('#programModal .modal-title').text('Edit Data');
            $('#programModal').modal('show');
          }
        });
    });

    // 🔹 Delete
    $(document).on('click', '.delete-btn', function() {
      let id = $(this).data('id');
      Swal.fire({
        title: 'Hapus Data?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          fetch(apiUrl + `?id=${id}`, {
              method: 'DELETE'
            })
            .then(res => res.json())
            .then(data => {
              if (data.status === 'success') {
                Swal.fire('Berhasil!', 'Data dihapus.', 'success');
                table.ajax.reload(null, false);
              }
            });
        }
      });
    });

    $(document).on('change', '.toggle-status', function() {
      let id = $(this).data('id');
      let status = $(this).is(':checked') ? 1 : 0;

      fetch(apiUrl + '?toggle_status=1', {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: `id_faskes=${id}&faskes_status=${status}`
        })
        .then(res => res.json())
        .then(res => {
          if (res.status !== 'success') {
            Swal.fire('Gagal!', res.message, 'error');
          }
        });
    });
  });
</script>
<script>
  function loadFaskesDropdown() {
    fetch('controller/master/faskesController')
      .then(res => res.json())
      .then(res => {
        let opt = '<option value="">-- Pilih Faskes --</option>';

        res.data.forEach(f => {
          opt += `<option value="${f.id_customer}">
                  ${f.clinic_name} (${f.id_customer})
                </option>`;
        });

        $('#faskesSelect').html(opt);
      });
  }

  // load saat buka modal
  $('#btnImport').on('click', function() {
    // Reset modal state
    $('#importType').val('');
    $('#importFile').val('');
    $('#faskesSelect').val('');
    $('#previewArea').hide();
    $('#progressArea').hide();
    $('#progressBar').css('width', '0%').text('0%');
    $('#progressStatus').text('Menunggu proses dimulai...');
    $('#btnUpload').prop('disabled', true);
    excelData = [];
    loadFaskesDropdown();
    $('#importModal').modal('show');
  });

  // ── Polling helpers ───────────────────────────────────────
  let pollingInterval = null;

  function startPolling(job_id) {
    pollingInterval = setInterval(function() {
      fetch('https://importjobs.medisafe.id/api/import/' + encodeURIComponent(job_id) + '/status')
        .then(res => res.json())
        .then(function(response) {
          if (response.status !== 'success') {
            clearInterval(pollingInterval);
            pollingInterval = null;
            $('#progressArea').hide();
            Swal.fire('Error!', 'Gagal mengambil status job', 'error');
            return;
          }

          const summary = response.summary || {};
          const total   = summary.total_rows || 1;
          const processed = (summary.success || 0) + (summary.duplicates || 0) + (summary.errors || 0);
          const pct     = Math.min(100, Math.round((processed / total) * 100));

          $('#progressBar').css('width', pct + '%').text(pct + '%');
          $('#progressStatus').text(processed + ' / ' + total + ' baris diproses');

          if (response.job_status === 'done') {
            clearInterval(pollingInterval);
            pollingInterval = null;
            $('#progressArea').hide();
            showImportResult(response);
          } else if (response.job_status === 'error') {
            clearInterval(pollingInterval);
            pollingInterval = null;
            $('#progressArea').hide();
            Swal.fire('Gagal!', response.error_message || 'Terjadi kesalahan saat memproses', 'error');
          }
        })
        .catch(function() {
          // Keep polling on network hiccup
        });
    }, 60000);
  }

  function showImportResult(result) {
    if (!result) return;
    const s = result.summary || {};

    let summaryHtml = `
      <div class="mb-3">
        <h6><strong>📊 Ringkasan Import</strong></h6>
        <table class="table table-sm table-borderless">
          <tr><td width="45%"><strong>Total Data:</strong></td><td>${s.total_rows ?? 0} baris</td></tr>
          <tr><td><strong>Berhasil:</strong></td><td><span class="badge bg-success">${s.success ?? 0}</span></td></tr>
          <tr><td><strong>Duplikat:</strong></td><td><span class="badge bg-warning text-dark">${s.duplicates ?? 0}</span></td></tr>
          <tr><td><strong>Error:</strong></td><td><span class="badge bg-danger">${s.errors ?? 0}</span></td></tr>
        </table>
      </div>
    `;

    let duplicatesHtml = '';
    const duplicateData = result.duplicates || [];
    if (duplicateData.length > 0) {
      duplicatesHtml = `
        <div class="mb-3">
          <h6><strong>⚠️ Data Duplikat (${duplicateData.length})</strong></h6>
          <div class="table-responsive" style="max-height:200px;overflow-y:auto;">
            <table class="table table-sm table-bordered">
              <thead class="table-warning"><tr><th>Baris</th><th>Detail</th><th>Alasan</th></tr></thead>
              <tbody>
                ${duplicateData.map(d => `
                  <tr>
                    <td>${d.row}</td>
                    <td>
                      ${d.ktp  ? 'KTP: '  + d.ktp  + '<br>' : ''}
                      ${d.rm   ? 'RM: '   + d.rm   + '<br>' : ''}
                      ${d.code ? 'Kode: ' + d.code + '<br>' : ''}
                      ${d.nama ? 'Nama: ' + d.nama          : ''}
                    </td>
                    <td>${d.reason}</td>
                  </tr>`).join('')}
              </tbody>
            </table>
          </div>
        </div>
      `;
    }

    let errorsHtml = '';
    const errorData = result.errors || [];
    if (errorData.length > 0) {
      errorsHtml = `
        <div class="mb-3">
          <h6><strong>❌ Error (${errorData.length})</strong></h6>
          <div class="table-responsive" style="max-height:200px;overflow-y:auto;">
            <table class="table table-sm table-bordered">
              <thead class="table-danger"><tr><th>Baris</th><th>Detail</th><th>Error</th></tr></thead>
              <tbody>
                ${errorData.map(e => `
                  <tr>
                    <td>${e.row}</td>
                    <td>
                      ${e.ktp  ? 'KTP: '  + e.ktp  + '<br>' : ''}
                      ${e.rm   ? 'RM: '   + e.rm   + '<br>' : ''}
                      ${e.code ? 'Kode: ' + e.code + '<br>' : ''}
                      ${e.nama ? 'Nama: ' + e.nama          : ''}
                    </td>
                    <td>${e.error}</td>
                  </tr>`).join('')}
              </tbody>
            </table>
          </div>
        </div>
      `;
    }

    Swal.fire({
      title: 'Import Selesai!',
      html: summaryHtml + duplicatesHtml + errorsHtml,
      icon: 'success',
      confirmButtonText: 'OK',
      didOpen: function() {
        const popup = Swal.getHtmlContainer();
        if (popup) { popup.style.maxHeight = '70vh'; popup.style.overflowY = 'auto'; }
      }
    }).then(function() {
      $('#importModal').modal('hide');
    });
  }

  // Stop polling when modal is closed
  $('#importModal').on('hidden.bs.modal', function() {
    if (pollingInterval) {
      clearInterval(pollingInterval);
      pollingInterval = null;
    }
  });
</script>
<script>
  let excelData = [];

  $('#btnImport').on('click', function() {
    $('#importModal').modal('show');
  });

  // 🔹 preview excel
  $('#importFile').on('change', function(e) {
    const file = e.target.files[0];

    if (!file) return;

    const reader = new FileReader();

    reader.onload = function(evt) {
      const data = new Uint8Array(evt.target.result);
      const workbook = XLSX.read(data, {
        type: 'array'
      });

      const sheet = workbook.Sheets[workbook.SheetNames[0]];
      excelData = XLSX.utils.sheet_to_json(sheet);

      renderPreview(excelData);
    };

    reader.readAsArrayBuffer(file);
  });

  // 🔹 render preview
  function renderPreview(data) {
    if (!data.length) return;

    $('#previewArea').show();
    $('#btnUpload').prop('disabled', false);

    let columns = Object.keys(data[0]);

    let thead = '<tr>' + columns.map(c => `<th>${c}</th>`).join('') + '</tr>';
    $('#previewTable thead').html(thead);

    let rows = data.slice(0, 10).map(row => {
      return '<tr>' + columns.map(c => `<td>${row[c] ?? ''}</td>`).join('') + '</tr>';
    }).join('');

    $('#previewTable tbody').html(rows);
  }

  // 🔹 upload
  $('#btnUpload').on('click', function() {

    let type   = $('#importType').val();
    let faskes = $('#faskesSelect').val();
    let file   = $('#importFile')[0].files[0];

    if (!type) {
      Swal.fire('Error', 'Pilih jenis data', 'error');
      return;
    }

    if (!faskes) {
      Swal.fire('Error', 'Pilih faskes terlebih dahulu', 'error');
      return;
    }

    if (!file) {
      Swal.fire('Error', 'Pilih file Excel terlebih dahulu', 'error');
      return;
    }

    // set loading state
    const $btn = $(this);
    const originalText = $btn.html();
    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Sedang memproses...');

    const formData = new FormData();
    formData.append('type', type);
    formData.append('id_faskes', faskes);
    formData.append('file', file);

    fetch('https://importjobs.medisafe.id/api/import', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(res => {
        $btn.prop('disabled', false).html(originalText);

        if (res.status === 'success') {
          // Show progress area and start polling
          $('#progressArea').show();
          startPolling(res.import_job_id);
        } else {
          Swal.fire('Gagal!', res.message || 'Terjadi kesalahan', 'error');
        }
      })
      .catch(err => {
        // restore button state on error
        $btn.prop('disabled', false).html(originalText);
        Swal.fire('Error!', 'Terjadi kesalahan', 'error');
      });
  });
</script>

<script>
  // ── Import Jobs History Table ──────────────────────────────────
  const statusBadge = {
    pending:    '<span class="badge bg-secondary">⏳ Pending</span>',
    processing: '<span class="badge bg-primary">🔄 Processing</span>',
    done:  '<span class="badge bg-success">✅ Selesai</span>',
    error:      '<span class="badge bg-danger">❌ Gagal</span>',
  };

  function loadJobsTable() {
    fetch('https://importjobs.medisafe.id/api/import')
      .then(res => res.json())
      .then(function(data) {
        if (data.status !== 'success') {
          $('#jobsTableBody').html('<tr><td colspan="9" class="text-center text-danger">Gagal memuat riwayat job</td></tr>');
          return;
        }

        const jobs = data.data && data.data.data ? data.data.data : [];
        const now  = new Date().toLocaleTimeString('id-ID');
        $('#jobsLastRefresh').text('Diperbarui: ' + now);

        if (!jobs.length) {
          $('#jobsTableBody').html('<tr><td colspan="9" class="text-center text-muted">Belum ada job import</td></tr>');
          return;
        }

        const rows = jobs.map(function(j) {
          const total = j.total_rows || 1;
          const processed = (j.success || 0) + (j.duplicates || 0) + (j.errors || 0);
          const pct = j.status === 'processing' 
            ? Math.min(100, Math.round((processed / total) * 100))
            : (j.status === 'done' ? 100 : 0);

          const progBar = j.status === 'processing'
            ? `<div class="progress" style="height:16px;min-width:90px;">
                 <div class="progress-bar progress-bar-striped progress-bar-animated" style="width:${pct}%">${pct}%</div>
               </div>`
            : (j.status === 'done' ? `<div class="progress" style="height:16px;min-width:90px;">
                 <div class="progress-bar bg-success" style="width:100%">100%</div>
               </div>` : '-');

          const detailBtn = (j.status === 'done')
            ? `<button class="btn btn-xs btn-sm btn-outline-primary py-0 px-2 view-job-result" data-id="${j.id}">Lihat</button>`
            : '-';

          return `<tr>
            <td>${j.created_at || '-'}</td>
            <td><span class="badge bg-light text-dark border">${j.type}</span></td>
            <td>${j.total_rows}</td>
            <td><span class="text-success fw-bold">${j.success || 0}</span></td>
            <td><span class="text-warning fw-bold">${j.duplicates || 0}</span></td>
            <td><span class="text-danger fw-bold">${j.errors || 0}</span></td>
            <td>${progBar}</td>
            <td>${statusBadge[j.status] ?? j.status}</td>
            <td>${detailBtn}</td>
          </tr>`;
        });

        $('#jobsTableBody').html(rows.join(''));
      })
      .catch(function() {
        $('#jobsTableBody').html('<tr><td colspan="9" class="text-center text-danger">Gagal memuat riwayat job</td></tr>');
      });
  }

  // Click "Lihat" detail button
  $(document).on('click', '.view-job-result', function() {
    const job_id = $(this).data('id');
    fetch('https://importjobs.medisafe.id/api/import/' + encodeURIComponent(job_id) + '/status')
      .then(res => res.json())
      .then(function(response) {
        if (response.status === 'success' && response.summary) {
          showImportResult(response);
        } else {
          Swal.fire('Info', 'Detail tidak tersedia', 'info');
        }
      });
  });

  // Load on page ready and refresh every 5 minutes
  loadJobsTable();
  setInterval(loadJobsTable, 300000); // 300000 ms = 5 menit
</script>

</html>
