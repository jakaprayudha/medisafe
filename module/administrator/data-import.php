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

  <?php require '../admin/library.php'; ?>
  <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
  <script>
    $(function() {
      const API = 'controller/master/faskesImportController.php';
      const JOBS = 'https://importjobs.medisafe.id/api/import';
      const MAX_MB = 20;
      const $i = id => document.getElementById(id);
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const fmt = n => Number(n || 0).toLocaleString('id-ID');
      const toast = (icon, title) => Swal.fire({ toast: true, position: 'top-end', icon, title, showConfirmButton: false, timer: 2600 });
      const KOLOM = ['pasien', 'dokter', 'nakes', 'farmasi', 'visit', 'poli', 'tarif', 'provider'];
      let faskes = [], filter = '', jobsSiap = false, jobAktif = 0, jobGagal = 0;

      /* ================= kelengkapan per faskes ================= */
      const cel = v => v > 0 ? `<span class="di-c g">${fmt(v)}</span>` : '<span class="di-c r">0</span>';
      const tabel = $('#faskesTable').DataTable({
        data: [], deferRender: true, pageLength: 25, order: [[1, 'asc']], autoWidth: false,
        language: { search: 'Cari faskes:', lengthMenu: 'Tampil _MENU_', info: '_START_–_END_ dari _TOTAL_ faskes', infoEmpty: '0 faskes', infoFiltered: '(dari _MAX_)', emptyTable: 'Belum ada data.', zeroRecords: 'Tidak ditemukan.', paginate: { previous: 'Sebelumnya', next: 'Berikutnya' } },
        columns: [{
            data: null, orderable: false, searchable: false, className: 'text-center',
            render: (d, t, r) => `<button type="button" class="btn btn-sm btn-primary btn-imp" data-id="${r.id_customer}" title="Import ke faskes ini"><i class="ti ti-upload"></i></button>`
          },
          { data: 'clinic_name', render: (v, t, r) => t === 'display' ? `<div class="di-nama">${esc(v)}</div><div class="di-sub">ID faskes ${r.id_customer}</div>` : v },
          ...KOLOM.map(k => ({ data: k, className: 'text-center', render: (v, t) => t === 'display' ? cel(v) : v }))
        ]
      });
      $.fn.dataTable.ext.search.push((s, d, idx) => {
        if (s.nTable.id !== 'faskesTable' || !filter) return true;
        const r = tabel.row(idx).data();
        if (!r) return true;
        if (filter === 'pasien') return r.pasien === 0;
        return KOLOM.some(k => r[k] === 0);
      });
      $('#fkFilter').on('click', '.di-chip', function() {
        filter = String($(this).data('f'));
        $('#fkFilter .di-chip').removeClass('on');
        $(this).addClass('on');
        tabel.draw();
      });

      async function muatFaskes() {
        $i('fkWrap').style.opacity = .55;
        try {
          const r = await fetch(API, { credentials: 'same-origin', cache: 'no-store' });
          const j = await r.json().catch(() => null);
          if (!r.ok || !j || j.status !== 'success') throw new Error((j && j.message) || 'Gagal memuat data (HTTP ' + r.status + ').');
          faskes = j.data;
          $i('kFaskes').textContent = fmt(faskes.length);
          $i('kLengkap').textContent = fmt(faskes.filter(f => KOLOM.every(k => f[k] > 0)).length);
          $i('kKosong').textContent = fmt(faskes.filter(f => f.pasien === 0).length);
          tabel.clear().rows.add(faskes).draw();
        } catch (e) {
          Swal.fire('Gagal', e.message, 'error');
        } finally {
          $i('fkWrap').style.opacity = 1;
        }
      }

      /* ================= riwayat import (layanan import) ================= */
      const ST = {
        pending: '<span class="di-b x"><i class="ti ti-hourglass-empty"></i>Menunggu</span>',
        processing: '<span class="di-b b"><i class="ti ti-loader-2 spin"></i>Diproses</span>',
        done: '<span class="di-b g"><i class="ti ti-circle-check"></i>Selesai</span>',
        error: '<span class="di-b r"><i class="ti ti-alert-circle"></i>Gagal</span>',
        failed: '<span class="di-b r"><i class="ti ti-alert-circle"></i>Gagal</span>'
      };
      const jobs = $('#jobsTable').DataTable({
        data: [], deferRender: true, pageLength: 10, order: [[1, 'desc']], autoWidth: false,
        language: { search: 'Cari:', lengthMenu: 'Tampil _MENU_', info: '_START_–_END_ dari _TOTAL_ import', infoEmpty: '0 import', infoFiltered: '(dari _MAX_)', emptyTable: 'Belum ada import.', zeroRecords: 'Tidak ditemukan.', paginate: { previous: 'Sebelumnya', next: 'Berikutnya' } },
        columns: [{
            data: null, orderable: false, searchable: false, className: 'text-center',
            render: (d, t, j) => j.status === 'done' ? `<button type="button" class="btn btn-sm btn-outline-primary job-detail" data-id="${esc(j.id)}">Lihat</button>` : '<span class="text-dark">-</span>'
          },
          { data: 'created_at', render: (v, t) => t === 'display' ? `<span class="text-nowrap">${esc(v || '-')}</span>` : (v || '') },
          { data: 'type', render: v => `<span class="di-b x">${esc(v || '-')}</span>` },
          { data: 'total_rows', className: 'text-end', render: v => fmt(v) },
          { data: 'success', className: 'text-end', render: v => `<b class="text-success">${fmt(v)}</b>` },
          { data: 'duplicates', className: 'text-end', render: v => `<b style="color:#b45309">${fmt(v)}</b>` },
          { data: 'errors', className: 'text-end', render: v => `<b class="text-danger">${fmt(v)}</b>` },
          {
            data: null, orderable: false,
            render: (d, t, j) => {
              if (j.status === 'pending') return '<span class="text-dark">-</span>';
              const tot = j.total_rows || 1, pr = (j.success || 0) + (j.duplicates || 0) + (j.errors || 0);
              const p = j.status === 'done' ? 100 : Math.min(100, Math.round(pr / tot * 100));
              return `<div class="progress" style="height:14px"><div class="progress-bar ${j.status === 'done' ? 'bg-success' : j.status === 'processing' ? 'progress-bar-striped progress-bar-animated' : 'bg-danger'}" style="width:${p}%">${p}%</div></div>`;
            }
          },
          { data: 'status', render: v => ST[v] || `<span class="di-b x">${esc(v)}</span>` }
        ]
      });
      let tJobs = null;
      async function muatJobs() {
        clearTimeout(tJobs);
        $i('jobsRefresh').querySelector('i').classList.add('spin');
        try {
          const ac = new AbortController(), to = setTimeout(() => ac.abort(), 15000);
          const r = await fetch(JOBS, { signal: ac.signal });
          clearTimeout(to);
          const j = await r.json();
          if (j.status !== 'success') throw new Error('status');
          const list = (j.data && j.data.data) ? j.data.data : [];
          jobs.clear().rows.add(list).draw();
          jobAktif = list.filter(x => x.status === 'pending' || x.status === 'processing').length;
          jobGagal = list.filter(x => x.status === 'error' || x.status === 'failed').length;
          $i('kJob').textContent = fmt(jobAktif) + ' / ' + fmt(jobGagal);
          $i('jobsInfo').textContent = 'Diperbarui ' + new Date().toLocaleTimeString('id-ID');
        } catch (e) {
          $i('kJob').textContent = '-';
          $i('jobsInfo').textContent = 'Layanan import tidak dapat dihubungi';
          jobs.clear().draw();
        } finally {
          $i('jobsRefresh').querySelector('i').classList.remove('spin');
          // lebih sering diperbarui selama ada import yang berjalan
          tJobs = setTimeout(muatJobs, jobAktif ? 15000 : 300000);
        }
      }
      $i('jobsRefresh').addEventListener('click', muatJobs);

      /* ================= ringkasan hasil ================= */
      const detailBaris = d => [d.ktp && 'KTP: ' + esc(d.ktp), d.rm && 'RM: ' + esc(d.rm), d.code && 'Kode: ' + esc(d.code), d.nama && 'Nama: ' + esc(d.nama)].filter(Boolean).join('<br>');
      function tabelMasalah(judul, ikon, kelas, arr, kolomAlasan, kunci) {
        if (!arr.length) return '';
        return `<div class="mb-3 text-start"><h6><strong>${ikon} ${judul} (${arr.length})</strong></h6>
          <div class="table-responsive" style="max-height:200px;overflow-y:auto"><table class="table table-sm table-bordered">
          <thead class="${kelas}"><tr><th>Baris</th><th>Detail</th><th>${kolomAlasan}</th></tr></thead><tbody>
          ${arr.map(d => `<tr><td>${esc(d.row)}</td><td>${detailBaris(d)}</td><td>${esc(d[kunci])}</td></tr>`).join('')}
          </tbody></table></div></div>`;
      }
      function tampilHasil(res, tutupModal) {
        const s = res.summary || {};
        const html = `<div class="mb-3 text-start"><h6><strong>Ringkasan Import</strong></h6>
          <table class="table table-sm table-borderless mb-0">
            <tr><td width="45%"><strong>Total data</strong></td><td>${fmt(s.total_rows)} baris</td></tr>
            <tr><td><strong>Berhasil</strong></td><td><span class="badge bg-success">${fmt(s.success)}</span></td></tr>
            <tr><td><strong>Duplikat</strong></td><td><span class="badge bg-warning text-dark">${fmt(s.duplicates)}</span></td></tr>
            <tr><td><strong>Error</strong></td><td><span class="badge bg-danger">${fmt(s.errors)}</span></td></tr>
          </table></div>` +
          tabelMasalah('Data duplikat', '⚠️', 'table-warning', res.duplicates || [], 'Alasan', 'reason') +
          tabelMasalah('Error', '❌', 'table-danger', res.errors || [], 'Error', 'error');
        Swal.fire({
          title: 'Hasil Import', html, icon: (s.errors || 0) > 0 ? 'warning' : 'success', confirmButtonText: 'OK', width: 720,
          didOpen: () => { const p = Swal.getHtmlContainer(); if (p) { p.style.maxHeight = '70vh'; p.style.overflowY = 'auto'; } }
        }).then(() => { if (tutupModal) $('#importModal').modal('hide'); });
      }
      $(document).on('click', '.job-detail', async function() {
        const id = $(this).data('id');
        try {
          const r = await fetch(JOBS + '/' + encodeURIComponent(id) + '/status');
          const j = await r.json();
          if (j.status === 'success' && j.summary) tampilHasil(j, false);
          else Swal.fire('Info', 'Detail tidak tersedia.', 'info');
        } catch (e) { Swal.fire('Gagal', 'Layanan import tidak dapat dihubungi.', 'error'); }
      });

      /* ================= modal import ================= */
      let excelData = [], poll = null, sibuk = false;
      function berhentiPoll() { if (poll) { clearTimeout(poll); poll = null; } }

      function isiFaskesSelect(pilih) {
        $i('faskesSelect').innerHTML = '<option value="">-- Pilih Faskes --</option>' + faskes.map(f => `<option value="${f.id_customer}">${esc(f.clinic_name)} (${f.id_customer})</option>`).join('');
        $i('faskesSelect').value = pilih || '';
      }
      function bukaModal(faskesId) {
        berhentiPoll();
        sibuk = false;
        $i('importType').value = '';
        $i('importFile').value = '';
        $i('fileInfo').textContent = '';
        $i('previewArea').style.display = 'none';
        $i('progressArea').style.display = 'none';
        $i('progressBar').style.width = '0%';
        $i('progressBar').textContent = '0%';
        $i('btnUpload').disabled = true;
        $('#importModal .is-invalid').removeClass('is-invalid');
        excelData = [];
        isiFaskesSelect(faskesId);
        $('#importModal').modal('show');
      }
      $i('btnImport').addEventListener('click', () => bukaModal(''));
      $(document).on('click', '.btn-imp', function() { bukaModal(String($(this).data('id'))); });

      $i('importFile').addEventListener('change', function(e) {
        const file = e.target.files[0];
        excelData = [];
        $i('previewArea').style.display = 'none';
        $i('btnUpload').disabled = true;
        $i('fileInfo').textContent = '';
        if (!file) return;
        if (!/\.(xlsx|xls)$/i.test(file.name)) { this.value = ''; return Swal.fire('Format tidak didukung', 'Pilih file Excel (.xlsx atau .xls).', 'warning'); }
        if (file.size > MAX_MB * 1024 * 1024) { this.value = ''; return Swal.fire('File terlalu besar', `Maksimal ${MAX_MB} MB (file ini ${(file.size / 1048576).toFixed(1)} MB). Pecah menjadi beberapa file.`, 'warning'); }
        $i('fileInfo').textContent = `${file.name} · ${(file.size / 1048576).toFixed(2)} MB — membaca pratinjau…`;
        const rd = new FileReader();
        rd.onload = function(ev) {
          try {
            // hanya 11 baris pertama yang dibaca untuk pratinjau (file besar tidak membekukan browser)
            const wb = XLSX.read(new Uint8Array(ev.target.result), { type: 'array', sheetRows: 11 });
            const ws = wb.Sheets[wb.SheetNames[0]];
            excelData = XLSX.utils.sheet_to_json(ws, { defval: '' });
            const penuh = ws['!fullref'] ? XLSX.utils.decode_range(ws['!fullref']) : null;
            const total = penuh ? Math.max(0, penuh.e.r) : excelData.length;
            if (!excelData.length) { $i('fileInfo').textContent = 'File kosong atau tidak memiliki baris data.'; return; }
            $i('fileInfo').textContent = `${file.name} · ${(file.size / 1048576).toFixed(2)} MB · sekitar ${fmt(total)} baris data`;
            const kolom = Object.keys(excelData[0]);
            $('#previewTable thead').html('<tr>' + kolom.map(c => `<th>${esc(c)}</th>`).join('') + '</tr>');
            $('#previewTable tbody').html(excelData.slice(0, 10).map(r => '<tr>' + kolom.map(c => `<td title="${esc(r[c])}">${esc(r[c])}</td>`).join('') + '</tr>').join(''));
            $i('previewInfo').textContent = `(10 baris pertama, ${kolom.length} kolom)`;
            $i('previewArea').style.display = 'block';
            $i('btnUpload').disabled = false;
          } catch (er) {
            $i('fileInfo').textContent = 'File tidak dapat dibaca. Pastikan file Excel yang valid.';
          }
        };
        rd.readAsArrayBuffer(file);
      });

      function mulaiPoll(jobId, mulai) {
        let jeda = 3000;
        const tik = async function() {
          try {
            const r = await fetch(JOBS + '/' + encodeURIComponent(jobId) + '/status');
            const j = await r.json();
            if (j.status !== 'success') throw new Error('status');
            const s = j.summary || {}, tot = s.total_rows || 1, pr = (s.success || 0) + (s.duplicates || 0) + (s.errors || 0);
            const p = Math.min(100, Math.round(pr / tot * 100));
            $i('progressBar').style.width = p + '%';
            $i('progressBar').textContent = p + '%';
            $i('progressStatus').textContent = fmt(pr) + ' / ' + fmt(tot) + ' baris diproses';
            if (j.job_status === 'done') { berhentiPoll(); sibuk = false; $i('progressArea').style.display = 'none'; muatJobs(); muatFaskes(); tampilHasil(j, true); return; }
            if (j.job_status === 'error') { berhentiPoll(); sibuk = false; $i('progressArea').style.display = 'none'; muatJobs(); Swal.fire('Gagal', j.error_message || 'Terjadi kesalahan saat memproses.', 'error'); return; }
          } catch (e) { /* gangguan jaringan sesaat: coba lagi */ }
          jeda = Math.min(jeda + 1000, 8000);
          poll = setTimeout(tik, jeda);
        };
        poll = setTimeout(tik, jeda);
      }

      $i('btnUpload').addEventListener('click', async function() {
        const type = $i('importType').value, id = $i('faskesSelect').value, file = $i('importFile').files[0];
        $('#importModal .is-invalid').removeClass('is-invalid');
        if (!id) { $i('faskesSelect').classList.add('is-invalid'); return Swal.fire('Pilih faskes tujuan', '', 'warning'); }
        if (!type) { $i('importType').classList.add('is-invalid'); return Swal.fire('Pilih jenis data', '', 'warning'); }
        if (!file) { $i('importFile').classList.add('is-invalid'); return Swal.fire('Pilih file Excel', '', 'warning'); }
        const nama = $i('faskesSelect').selectedOptions[0].textContent.trim();
        const ok = await Swal.fire({
          icon: 'question', title: 'Mulai import?', html: `Data <b>${esc(type)}</b> dari <b>${esc(file.name)}</b> akan dimasukkan ke <b>${esc(nama)}</b>.<br><small>Proses berjalan di server dan tidak bisa dibatalkan.</small>`,
          showCancelButton: true, confirmButtonText: 'Ya, import', cancelButtonText: 'Batal'
        });
        if (!ok.isConfirmed || sibuk) return;
        sibuk = true;
        const btn = this, awal = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengunggah…';
        const fd = new FormData();
        fd.append('type', type);
        fd.append('id_faskes', id);
        fd.append('file', file);
        try {
          const r = await fetch(JOBS, { method: 'POST', body: fd });
          const j = await r.json();
          if (j.status !== 'success') throw new Error(j.message || 'Terjadi kesalahan');
          $i('progressArea').style.display = 'block';
          $i('progressStatus').textContent = 'Menunggu proses dimulai…';
          mulaiPoll(j.import_job_id);
          muatJobs();
        } catch (e) {
          sibuk = false;
          btn.disabled = false;
          Swal.fire('Gagal', e.message || 'Layanan import tidak dapat dihubungi.', 'error');
        } finally {
          btn.innerHTML = awal;
        }
      });

      // menutup modal tidak membatalkan import di server
      $('#importModal').on('hide.bs.modal', function(e) {
        if (poll && sibuk) toast('info', 'Import tetap berjalan. Pantau di Riwayat Import.');
      }).on('hidden.bs.modal', berhentiPoll);

      muatFaskes();
      muatJobs();
    });
  </script>
</body>

</html>
