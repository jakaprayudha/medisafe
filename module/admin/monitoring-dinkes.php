<?php
$title = 'Monitoring Dinkes';
require '../../controller/view.php';
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <?php require 'sidebar.php'; ?>
    <div class="body-wrapper">
      <?php require 'navbar.php'; ?>
      <div class="body-wrapper-inner">
        <div class="container-fluid">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
            <div>
              <h4 class="fw-semibold mb-1">Monitoring Dinkes</h4>
              <div class="text-muted small">Status pengiriman kunjungan ke Dinkes Deli Serdang. Data selalu tersimpan di Medisafe walau pengiriman gagal.</div>
            </div>
            <a class="btn btn-outline-primary" href="module/admin/setting_dinkes"><i class="ti ti-settings me-1"></i>Pengaturan</a>
          </div>
          <div class="d-flex flex-wrap gap-2 mb-3" id="dmRingkas"></div>
          <div class="card">
            <div class="card-body">
              <div class="row g-2 mb-3">
                <div class="col-6 col-md-3">
                  <select class="form-select" id="dmStatus">
                    <option value="">Semua status</option>
                    <option>belum</option><option>terkirim</option><option>gagal</option>
                    <option>dilewati</option><option>dihapus</option><option>hapus_gagal</option>
                  </select>
                </div>
                <div class="col-6 col-md-3">
                  <select class="form-select" id="dmJenis">
                    <option value="">Dewasa &amp; balita</option><option>dewasa</option><option>balita</option>
                  </select>
                </div>
              </div>
              <div class="table-responsive">
                <table class="table table-sm align-middle">
                  <thead><tr><th>Tanggal</th><th>Pasien</th><th>Jenis</th><th>Status</th><th>ID Dinkes</th><th>Percobaan</th><th>Pesan</th><th></th></tr></thead>
                  <tbody id="dmBody"><tr><td colspan="8" class="text-center text-muted">Memuat…</td></tr></tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php require 'library.php'; ?>
  <script>
    (function() {
      'use strict';
      const API = 'controller/dashboard/dinkesMonitoringController';
      const $ = id => document.getElementById(id);
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const toast = (title, icon = 'success') => Swal.fire({ toast: true, position: 'top-end', icon, title, showConfirmButton: false, timer: 2600 });
      const WARNA = { terkirim: 'success', gagal: 'danger', hapus_gagal: 'danger', belum: 'warning', dilewati: 'secondary', dihapus: 'secondary' };
      async function api(url, opt) {
        const r = await fetch(url, opt);
        const j = await r.json().catch(() => ({ status: 'error', message: 'Respon server tidak valid.' }));
        if (!r.ok || j.status !== 'success') throw new Error(j.message || 'Terjadi kesalahan.');
        return j;
      }
      function muat() {
        const q = new URLSearchParams({ status: $('dmStatus').value, jenis: $('dmJenis').value });
        return api(API + '?' + q).then(res => {
          $('dmRingkas').innerHTML = Object.entries(res.ringkas).map(([s, n]) => `<span class="badge bg-${WARNA[s] || 'secondary'}-subtle text-${WARNA[s] || 'secondary'} fs-3">${esc(s)}: ${n}</span>`).join('') || '<span class="text-muted small">Belum ada data.</span>';
          $('dmBody').innerHTML = res.data.length ? res.data.map(r => `<tr>
            <td class="text-nowrap">${esc(r.visit_date || '-')}</td>
            <td>${esc(r.patient_name || '-')}<div class="text-muted small">${esc(r.patient_nik || '')}</div></td>
            <td>${esc(r.jenis)}</td>
            <td><span class="badge bg-${WARNA[r.dk_status] || 'secondary'}-subtle text-${WARNA[r.dk_status] || 'secondary'}">${esc(r.dk_status)}</span></td>
            <td>${esc(r.dk_id ?? '-')}</td><td>${esc(r.dk_attempts)}</td>
            <td style="max-width:320px" class="small">${esc(r.dk_error || '')}</td>
            <td class="text-nowrap">
              ${r.dk_status === 'hapus_gagal'
                ? `<button class="btn btn-sm btn-danger" data-aksi="hapus" data-v="${esc(r.visit_ID)}">Ulangi hapus</button>`
                : (['gagal', 'belum', 'dilewati'].includes(r.dk_status) ? `<button class="btn btn-sm btn-primary" data-aksi="kirim" data-v="${esc(r.visit_ID)}">Kirim ulang</button>` : '')}
            </td></tr>`).join('') : '<tr><td colspan="8" class="text-center text-muted">Tidak ada data.</td></tr>';
        }).catch(e => { $('dmBody').innerHTML = `<tr><td colspan="8" class="text-danger text-center">${esc(e.message)}</td></tr>`; });
      }
      $('dmStatus').addEventListener('change', muat);
      $('dmJenis').addEventListener('change', muat);
      $('dmBody').addEventListener('click', async ev => {
        const b = ev.target.closest('[data-aksi]');
        if (!b) return;
        b.disabled = true;
        try {
          const res = await api(API, { method: 'POST', body: new URLSearchParams({ aksi: b.dataset.aksi, visit: b.dataset.v }) });
          toast(res.hasil.message, ['terkirim', 'dihapus'].includes(res.hasil.status) ? 'success' : 'warning');
        } catch (e) { toast(e.message, 'error'); }
        muat();
      });
      muat();
    })();
  </script>
</body>

</html>
