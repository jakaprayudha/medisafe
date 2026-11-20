<?php
$title = 'Data Pasien IDSH';
require '../../controller/view.php';
$isSuper = in_array($_SESSION['roles'] ?? '', ['superadmin', 'administrator'], true);
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .min-w-0 { min-width: 0; }
    .pi-icon { width: 40px; height: 40px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
    .pi-kpi { border: 1px solid #e5eaef; border-radius: 12px; background: #fff; padding: 14px 16px; height: 100%; cursor: pointer; transition: border-color .15s, box-shadow .15s; }
    .pi-kpi:hover { border-color: #c9d2dc; box-shadow: 0 4px 14px rgba(0, 0, 0, .05); }
    .pi-kpi.on { border-color: var(--c); box-shadow: 0 0 0 3px color-mix(in srgb, var(--c) 18%, transparent); }
    .pi-val { font-size: 1.35rem; font-weight: 700; color: #2a3547; line-height: 1.2; }
    .pi-label { font-size: .75rem; color: #6c757d; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .pi-sub { font-size: .7rem; color: #98a2b3; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .pi-prog { height: 10px; border-radius: 5px; background: #eef1f4; overflow: hidden; display: flex; }
    .pi-prog > div { height: 100%; transition: width .3s; }
    .pi-badge { font-size: .7rem; font-weight: 600; padding: .25em .65em; border-radius: 20px; white-space: nowrap; }
    .pi-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: .82rem; }
    #piTable td { font-size: .84rem; vertical-align: middle; white-space: nowrap; }
    #piTable th { font-size: .75rem; color: #5a6a85; font-weight: 600; white-space: nowrap; }
    .pi-legend span { display: inline-flex; align-items: center; gap: 5px; margin-right: 14px; }
    .pi-legend i { width: 10px; height: 10px; border-radius: 2px; display: inline-block; }
    .skel { display: inline-block; height: 1.1rem; width: 60px; border-radius: 6px; background: linear-gradient(90deg, #eef1f4 25%, #f6f8fb 50%, #eef1f4 75%); background-size: 200% 100%; animation: sk 1.2s infinite; }
    @keyframes sk { to { background-position: -200% 0; } }
  </style>
</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <?php require '../admin/sidebar.php'; ?>
    <div class="body-wrapper">
      <?php require $isSuper ? '../admin/navbar-master.php' : '../admin/navbar.php'; ?>
      <div class="body-wrapper-inner">
        <div class="container-fluid">

          <!-- HEADER -->
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-3">
              <div class="d-flex flex-wrap align-items-center gap-3">
                <span class="pi-icon bg-success-subtle text-success"><i class="fas fa-id-badge"></i></span>
                <div class="min-w-0 flex-grow-1">
                  <h5 class="fw-semibold mb-0">ID Satu Sehat Pasien</h5>
                  <div class="text-muted small">ID IHS pasien dicari otomatis dari NIK oleh sinkronisasi. Pasien tanpa ID IHS tidak bisa dikirim ke Satu Sehat.</div>
                </div>
                <button type="button" class="btn btn-light btn-sm" id="btnRefresh" title="Muat ulang"><i class="fas fa-sync-alt"></i></button>
              </div>
              <div class="mt-3">
                <div class="d-flex flex-wrap justify-content-between small mb-1 gap-2">
                  <span class="pi-legend text-muted"><span><i class="bg-success"></i>Lengkap</span><span><i style="background:#539bff"></i>Menunggu sinkron</span><span><i style="background:#ffae1f"></i>Tidak ditemukan</span><span><i style="background:#fa896b"></i>NIK belum valid</span></span>
                  <span class="fw-semibold" id="piPct">-</span>
                </div>
                <div class="pi-prog"><div class="bg-success" id="pgL" style="width:0"></div><div style="background:#539bff;width:0" id="pgA"></div><div style="background:#ffae1f;width:0" id="pgT"></div><div style="background:#fa896b;width:0" id="pgN"></div></div>
              </div>
            </div>
          </div>

          <!-- KPI (klik untuk menyaring) -->
          <div class="row g-3 mb-3">
            <div class="col-6 col-xl-3"><div class="pi-kpi" data-f="lengkap" style="--c:#13deb9"><div class="d-flex align-items-center gap-2 mb-1"><span class="pi-icon bg-success-subtle text-success"><i class="fas fa-check-circle"></i></span>
              <div class="min-w-0"><div class="pi-val" id="kL"><span class="skel"></span></div><div class="pi-label">Sudah punya ID IHS</div></div></div><div class="pi-sub" id="kLSub">&nbsp;</div></div></div>
            <div class="col-6 col-xl-3"><div class="pi-kpi" data-f="antre" style="--c:#539bff"><div class="d-flex align-items-center gap-2 mb-1"><span class="pi-icon bg-info-subtle text-info"><i class="fas fa-clock"></i></span>
              <div class="min-w-0"><div class="pi-val" id="kA"><span class="skel"></span></div><div class="pi-label">Menunggu sinkron</div></div></div><div class="pi-sub">NIK valid, belum dicek</div></div></div>
            <div class="col-6 col-xl-3"><div class="pi-kpi" data-f="tidak_ditemukan" style="--c:#ffae1f"><div class="d-flex align-items-center gap-2 mb-1"><span class="pi-icon bg-warning-subtle text-warning"><i class="fas fa-search-minus"></i></span>
              <div class="min-w-0"><div class="pi-val" id="kT"><span class="skel"></span></div><div class="pi-label">NIK tidak ditemukan</div></div></div><div class="pi-sub">Cek ulang NIK sesuai KTP/KK</div></div></div>
            <div class="col-6 col-xl-3"><div class="pi-kpi" data-f="nik" style="--c:#fa896b"><div class="d-flex align-items-center gap-2 mb-1"><span class="pi-icon bg-danger-subtle text-danger"><i class="fas fa-id-card"></i></span>
              <div class="min-w-0"><div class="pi-val" id="kN"><span class="skel"></span></div><div class="pi-label">NIK belum valid</div></div></div><div class="pi-sub">Kosong / bukan 16 digit</div></div></div>
          </div>
          <div class="alert alert-warning small py-2 d-none" id="piDampak"></div>

          <!-- TABEL -->
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
              <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                <div class="position-relative flex-grow-1" style="max-width:420px">
                  <i class="fas fa-search position-absolute text-muted" style="left:12px;top:50%;transform:translateY(-50%);font-size:.8rem"></i>
                  <input type="search" id="piCari" class="form-control form-control-sm ps-5" placeholder="Cari nama, No. RM, NIK atau No. BPJS…">
                </div>
                <select id="piUrut" class="form-select form-select-sm" style="width:auto">
                  <option value="baru">Terbaru didaftarkan</option>
                  <option value="nama">Nama A–Z</option>
                </select>
                <span class="small text-muted ms-auto" id="piInfo"></span>
              </div>
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 w-100" id="piTable">
                  <thead><tr><th>Pasien</th><th>No. RM / BPJS</th><th>NIK</th><th>ID IHS</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                  <tbody></tbody>
                </table>
              </div>
              <div class="small text-muted mt-2"><i class="fas fa-shield-alt me-1"></i>NIK ditampilkan tersamar. Perbaikan NIK dilakukan di Data Pasien.</div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
  <?php require '../admin/library.php'; ?>
  <script>
    (function() {
      const API = 'controller/master/patientIDSHDetailControllerByClinic.php';
      const API_SS = 'controller/master/settingSatusehatController.php';
      const ST = { lengkap: ['Lengkap', 'bg-success-subtle text-success'], antre: ['Menunggu sinkron', 'bg-info-subtle text-info'], tidak_ditemukan: ['NIK tidak ditemukan', 'bg-warning-subtle text-warning'], nik: ['NIK belum valid', 'bg-danger-subtle text-danger'] };
      const $id = id => document.getElementById(id);
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const fmt = n => Number(n || 0).toLocaleString('id-ID');
      const umur = s => { const d = new Date(s); if (!s || isNaN(d)) return ''; const u = Math.floor((Date.now() - d) / 31557600000); return u >= 0 && u < 130 ? u + ' th' : ''; };
      let filter = '';

      async function ringkasan() {
        try {
          const r = await fetch(API + '?action=ringkasan', { credentials: 'same-origin', cache: 'no-store' });
          const j = await r.json();
          if (j.status !== 'success') throw new Error(j.message);
          const s = j.status_pasien, t = j.total || 0;
          $id('kL').textContent = fmt(s.lengkap); $id('kA').textContent = fmt(s.antre); $id('kT').textContent = fmt(s.tidak_ditemukan); $id('kN').textContent = fmt(s.nik);
          $id('kLSub').textContent = 'dari ' + fmt(t) + ' pasien';
          const w = v => (t ? v / t * 100 : 0) + '%';
          $id('pgL').style.width = w(s.lengkap); $id('pgA').style.width = w(s.antre); $id('pgT').style.width = w(s.tidak_ditemukan); $id('pgN').style.width = w(s.nik);
          $id('piPct').textContent = (t ? Math.round(s.lengkap / t * 1000) / 10 : 0).toString().replace('.', ',') + '% lengkap';
          const k = j.kunjungan_30;
          $id('piDampak').classList.toggle('d-none', !k.tanpa_ihs);
          $id('piDampak').innerHTML = `<i class="fas fa-exclamation-triangle me-1"></i><b>${fmt(k.tanpa_ihs)} dari ${fmt(k.total)} kunjungan</b> dalam 30 hari terakhir (${fmt(k.pasien_tanpa_ihs)} pasien) belum bisa dikirim ke Satu Sehat karena pasiennya belum punya ID IHS.`;
        } catch (e) {
          ['kL', 'kA', 'kT', 'kN'].forEach(i => $id(i).textContent = '-');
        }
      }

      const jq = window.jQuery;
      let tCari;
      const tabel = jq('#piTable').DataTable({
        serverSide: true, processing: true, searching: false, ordering: false, pageLength: 25, lengthMenu: [10, 25, 50, 100], autoWidth: false,
        language: { processing: '<div class="spinner-border spinner-border-sm text-primary"></div> Memuat…', lengthMenu: 'Tampil _MENU_', info: '_START_–_END_ dari _TOTAL_ pasien', infoEmpty: '0 pasien',
          emptyTable: 'Tidak ada pasien pada filter ini', zeroRecords: 'Tidak ada pasien yang cocok', paginate: { previous: '‹', next: '›' } },
        ajax: (req, cb) => {
          const p = new URLSearchParams({ action: 'list', draw: req.draw, start: req.start, length: req.length, q: $id('piCari').value.trim(), status: filter, urut: $id('piUrut').value });
          fetch(API + '?' + p, { credentials: 'same-origin', cache: 'no-store' }).then(r => r.json()).then(j => {
            if (j.status !== 'success') throw new Error(j.message || 'Gagal memuat data.');
            $id('piInfo').textContent = fmt(j.recordsTotal) + ' pasien' + (filter ? ' · ' + ST[filter][0] : '');
            cb(j);
          }).catch(e => { cb({ draw: req.draw, recordsTotal: 0, recordsFiltered: 0, data: [] }); Swal.fire('Gagal', e.message || 'Tidak dapat terhubung ke server.', 'error'); });
        },
        columns: [
          { data: 'nama', render: (v, t, r) => `<div class="fw-semibold">${esc(v || '-')}</div><div class="small text-muted">${[r.gender === 'L' ? 'Laki-laki' : r.gender === 'P' ? 'Perempuan' : '', umur(r.lahir)].filter(Boolean).map(esc).join(' · ')}</div>` },
          { data: 'rm', render: (v, t, r) => `<span class="pi-mono">${esc(v || '-')}</span>` + (r.bpjs ? `<div class="small text-muted pi-mono">BPJS ${esc(r.bpjs)}</div>` : '') },
          { data: 'nik', render: (v, t, r) => r.nik_ada ? `<span class="pi-mono">${esc(v)}</span>` : '<span class="text-muted">Belum diisi</span>' },
          { data: 'idsh', render: v => v ? `<span class="pi-mono">${esc(v)}</span>` : '<span class="text-muted">-</span>' },
          { data: 'status', render: v => `<span class="pi-badge ${ST[v][1]}">${ST[v][0]}</span>` },
          { data: null, className: 'text-end', render: r => r.status === 'antre' || r.status === 'tidak_ditemukan'
              ? `<button type="button" class="btn btn-sm ${r.status === 'antre' ? 'btn-primary' : 'btn-light'} pi-ihs" data-id="${r.id_patient}"><i class="fas fa-search me-1"></i>${r.status === 'antre' ? 'Cari IHS' : 'Cari ulang'}</button>`
              : (r.status === 'nik' ? `<a class="btn btn-sm btn-light" href="module/admin/patient_details?pt=${r.id_patient}&tabs=1" target="_blank" rel="noopener"><i class="fas fa-pen me-1"></i>Perbaiki NIK</a>` : '') }
        ]
      });

      $id('piCari').addEventListener('input', () => { clearTimeout(tCari); tCari = setTimeout(() => tabel.ajax.reload(), 350); });
      $id('piUrut').addEventListener('change', () => tabel.ajax.reload());
      document.querySelectorAll('.pi-kpi').forEach(el => el.addEventListener('click', () => {
        filter = filter === el.dataset.f ? '' : el.dataset.f;
        document.querySelectorAll('.pi-kpi').forEach(x => x.classList.toggle('on', x.dataset.f === filter));
        tabel.ajax.reload();
      }));
      $id('btnRefresh').addEventListener('click', () => { ringkasan(); tabel.ajax.reload(null, false); });

      jq('#piTable').on('click', '.pi-ihs', async function() {
        const btn = this, label = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        const fd = new FormData();
        fd.append('aksi', 'ihs_pasien');
        fd.append('id_patient', btn.dataset.id);
        try {
          const r = await fetch(API_SS, { method: 'POST', body: fd, credentials: 'same-origin' });
          const j = await r.json().catch(() => null);
          if (!r.ok || !j || j.status !== 'success') throw new Error((j && j.message) || 'Pencarian gagal (HTTP ' + r.status + ').');
          Swal.fire({ icon: 'success', title: 'ID IHS ditemukan', html: `<span class="pi-mono">${esc(j.idsh)}</span>` + (j.nama_satusehat ? `<div class="small text-muted mt-1">Nama di Satu Sehat: ${esc(j.nama_satusehat)}</div>` : ''), timer: 2600 });
        } catch (e) {
          Swal.fire('ID IHS tidak ditemukan', e.message, 'warning');
        } finally {
          btn.disabled = false;
          btn.innerHTML = label;
          ringkasan();
          tabel.ajax.reload(null, false);
        }
      });

      ringkasan();
    })();
  </script>
</body>

</html>
