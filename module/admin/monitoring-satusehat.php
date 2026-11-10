<?php
$title = 'Monitoring Satu Sehat';
require '../../controller/view.php';
$roles = $_SESSION['roles'] ?? '';
if (!in_array($roles, ['superadmin', 'admin'], true)) {
   http_response_code(403);
   exit('Akses tidak diizinkan.');
}
$isSuper = $roles === 'superadmin';
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
      <?php require $isSuper ? 'navbar-master.php' : 'navbar.php'; ?>
      <div class="body-wrapper-inner">
        <div class="container-fluid">
          <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-4">
            <h4 class="fw-semibold mb-0">Monitoring Satu Sehat</h4>
            <div class="d-flex gap-2 align-items-center">
              <?php if ($isSuper): ?>
              <select id="ssFaskes" class="form-select" style="min-width:260px"><option value="0">Semua Faskes</option></select>
              <?php endif; ?>
              <button type="button" class="btn btn-dark" id="ssRefresh"><i class="fas fa-sync me-1"></i>Muat Ulang</button>
            </div>
          </div>
          <div id="ssError" class="alert alert-danger d-none" role="alert"></div>

          <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3"><div class="card"><div class="card-body"><div class="text-muted">Berhasil</div><h3 class="mb-0 text-success" id="ssOk">-</h3></div></div></div>
            <div class="col-6 col-lg-3"><div class="card"><div class="card-body"><div class="text-muted">Gagal</div><h3 class="mb-0 text-danger" id="ssErr">-</h3></div></div></div>
            <div class="col-6 col-lg-3"><div class="card"><div class="card-body"><div class="text-muted">Menunggu</div><h3 class="mb-0 text-warning" id="ssPend">-</h3></div></div></div>
            <div class="col-6 col-lg-3"><div class="card"><div class="card-body"><div class="text-muted">Konfigurasi</div><div class="fw-semibold" id="ssCfg">-</div></div></div></div>
          </div>

          <div class="card mb-4"><div class="card-body">
            <h6>Status per Resource</h6>
            <div style="height:320px"><canvas id="ssChart"></canvas></div>
            <div class="table-responsive mt-3">
              <table class="table align-middle" id="ssResTable">
                <thead class="table-light"><tr><th>Resource</th><th class="text-end">Berhasil (log)</th><th class="text-end">Gagal (log)</th><th class="text-end">Sukses (data)</th><th class="text-end">Gagal (data)</th><th class="text-end">Menunggu (data)</th><th>Sukses Terakhir</th></tr></thead>
                <tbody></tbody>
              </table>
            </div>
          </div></div>

          <?php if ($isSuper): ?>
          <div class="card mb-4"><div class="card-body">
            <h6>Ringkasan per Faskes <small class="text-muted">(klik baris untuk memfilter)</small></h6>
            <div class="table-responsive">
              <table class="table table-hover align-middle w-100" id="ssFaskesTable">
                <thead class="table-light"><tr><th>Faskes</th><th class="text-end">Berhasil</th><th class="text-end">Gagal</th><th>Aktivitas Terakhir</th><th>Konfigurasi</th></tr></thead>
                <tbody></tbody>
              </table>
            </div>
          </div></div>
          <?php endif; ?>

          <div class="card mb-4"><div class="card-body">
            <h6>200 Error Terbaru</h6>
            <div class="table-responsive">
              <table class="table align-middle w-100" id="ssErrTable">
                <thead class="table-light"><tr><th>Waktu</th><th>Faskes</th><th>Resource</th><th>Ref ID</th><th>Pesan</th></tr></thead>
                <tbody></tbody>
              </table>
            </div>
          </div></div>
        </div>
      </div>
    </div>
  </div>
  <?php require 'library.php'; ?>
  <script>
    const api = 'controller/dashboard/satuSehatMonitoringController.php';
    const isSuper = <?= $isSuper ? 'true' : 'false' ?>;
    const faskesSel = document.getElementById('ssFaskes');
    const errBox = document.getElementById('ssError');
    const n = v => Number(v || 0).toLocaleString('id-ID');
    let chart, errTable, faskesTable, resTable, faskesLoaded = false;

    async function get(action) {
      const p = new URLSearchParams({ action, id_customer: faskesSel ? faskesSel.value : 0 });
      const r = await fetch(api + '?' + p);
      const j = await r.json();
      if (!r.ok || j.status !== 'success') throw new Error(j.message || 'Gagal memuat data.');
      return j;
    }
    const text = $.fn.dataTable.render.text();
    const lang = { search: 'Cari:', emptyTable: 'Tidak ada data.', zeroRecords: 'Tidak ditemukan.', paginate: { next: 'Berikutnya', previous: 'Sebelumnya' } };

    function renderSummary(s) {
      const map = {};
      for (const l of s.log) map[l.resource] = { resource: l.resource, lok: +l.ok, lerr: +l.error, last: l.last_success };
      // backlog labels differ from log resource_type for lab/rad variants; match on the base name.
      for (const b of s.backlog) {
        const key = Object.keys(map).find(k => b.resource === k || b.resource.startsWith(k + ' ')) || b.resource;
        const m = map[key] = map[key] || { resource: key, lok: 0, lerr: 0 };
        m.dok = (m.dok || 0) + b.ok; m.derr = (m.derr || 0) + b.error; m.dpend = (m.dpend || 0) + b.pending;
      }
      const rows = Object.values(map);
      const sum = f => rows.reduce((a, r) => a + (r[f] || 0), 0);
      document.getElementById('ssOk').textContent = n(sum('lok'));
      document.getElementById('ssErr').textContent = n(sum('lerr'));
      document.getElementById('ssPend').textContent = n(sum('dpend'));
      resTable.clear().rows.add(rows).draw();
      chart.data.labels = rows.map(r => r.resource);
      chart.data.datasets[0].data = rows.map(r => r.lok);
      chart.data.datasets[1].data = rows.map(r => r.lerr);
      chart.data.datasets[2].data = rows.map(r => r.dpend || 0);
      chart.update();
    }

    function renderConfig(rows) {
      const el = document.getElementById('ssCfg');
      if (!rows.length) { el.textContent = 'Belum dikonfigurasi'; return; }
      // token expiry is not shown: tokens are cached and refreshed on demand, so "expired" is normal.
      const ok = rows.filter(r => +r.credentials_ok && +r.location_ok).length;
      el.textContent = rows.length === 1
        ? (ok ? 'Kredensial & lokasi lengkap' : 'Belum lengkap')
        : ok + '/' + rows.length + ' faskes lengkap';
    }

    async function load() {
      errBox.classList.add('d-none');
      try {
        const [s, e, c] = await Promise.all([get('summary'), get('errors'), get('config')]);
        renderSummary(s);
        errTable.clear().rows.add(e.data).draw();
        renderConfig(c.data);
        if (isSuper) {
          const f = await get('by_faskes');
          faskesTable.clear().rows.add(f.data).draw();
          if (!faskesLoaded) {
            for (const r of f.data) faskesSel.add(new Option(r.clinic_name || ('#' + r.id_customer), r.id_customer));
            faskesLoaded = true;
          }
        }
      } catch (err) {
        errBox.textContent = err.message || 'Gagal terhubung ke server.';
        errBox.classList.remove('d-none');
      }
    }

    $(function () {
      chart = new Chart(document.getElementById('ssChart'), {
        type: 'bar',
        data: { labels: [], datasets: [
          { label: 'Berhasil', data: [], backgroundColor: '#13deb9' },
          { label: 'Gagal', data: [], backgroundColor: '#fa896b' },
          { label: 'Menunggu', data: [], backgroundColor: '#ffae1f' }
        ] },
        options: { maintainAspectRatio: false, scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } } }
      });
      const num = d => ({ data: d, className: 'text-end', defaultContent: '-', render: (v) => v == null ? '-' : n(v) });
      resTable = $('#ssResTable').DataTable({ data: [], paging: false, searching: false, info: false, language: lang, columns: [
        { data: 'resource', render: text }, num('lok'), num('lerr'), num('dok'), num('derr'), num('dpend'), { data: 'last', defaultContent: '-', render: text }
      ] });
      errTable = $('#ssErrTable').DataTable({ data: [], order: [[0, 'desc']], language: lang, columns: [
        { data: 'created_at', render: text },
        { data: 'clinic_name', defaultContent: '-', render: text },
        { data: 'resource_type', render: text },
        { data: 'reference_id', defaultContent: '-', render: text },
        { data: 'message', defaultContent: '-', render: text }
      ] });
      if (isSuper) {
        faskesTable = $('#ssFaskesTable').DataTable({ data: [], order: [[2, 'desc']], language: lang, columns: [
          { data: 'clinic_name', defaultContent: '-', render: text }, num('ok'), num('error'),
          { data: 'last_activity', defaultContent: '-', render: text },
          { data: 'configured', render: v => +v ? 'Ada' : 'Belum' }
        ] });
        $('#ssFaskesTable tbody').on('click', 'tr', function () {
          const d = faskesTable.row(this).data();
          if (d) { faskesSel.value = d.id_customer; load(); }
        });
        faskesSel.addEventListener('change', load);
      }
      document.getElementById('ssRefresh').addEventListener('click', load);
      load();
    });
  </script>
</body>
</html>
