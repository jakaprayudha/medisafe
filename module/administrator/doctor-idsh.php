<?php
$title = 'IDSH Satu Sehat';
require '../../controller/view.php';

// Ringkasan lintas faskes: hanya superadmin/administrator.
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
    .dk-kpi { border: 1px solid #e2e8f0; border-radius: 14px; padding: 14px 16px; background: #fff; height: 100%; }
    .dk-kpi .n { font-size: 24px; font-weight: 800; color: #0f172a; line-height: 1.1; }
    .dk-kpi .l { font-size: 12px; font-weight: 600; color: #334155; margin-top: 2px; }
    .dk-kpi .s { font-size: 11px; color: #475569; }
    .dk-chip { border: 1px solid #cbd5e1; background: #fff; color: #334155; border-radius: 999px; padding: 4px 12px; font-size: 12px; font-weight: 700; cursor: pointer; }
    .dk-chip:hover { background: #f1f5f9; }
    .dk-chip.on { background: #1e293b; border-color: #1e293b; color: #fff; }
    #dkTable th { font-size: 11px; font-weight: 700; color: #334155; white-space: nowrap; text-transform: uppercase; letter-spacing: .3px; }
    #dkTable td { vertical-align: middle; font-size: 13px; color: #0f172a; }
    .dk-nama { font-weight: 700; color: #0f172a; min-width: 190px; }
    .dk-n { display: inline-block; min-width: 44px; text-align: center; padding: 3px 8px; border-radius: 7px; font-size: 12px; font-weight: 700; }
    .dk-n.g { background: #dcfce7; color: #166534; }
    .dk-n.y { background: #fef3c7; color: #92400e; }
    .dk-n.r { background: #fee2e2; color: #991b1b; }
    .dk-n.x { background: #f1f5f9; color: #475569; }
    .dk-stack { display: flex; height: 9px; border-radius: 5px; overflow: hidden; background: #e2e8f0; min-width: 70px; }
    .dk-stack > i { display: block; height: 100%; }
    .dk-dot { display: inline-block; width: 10px; height: 10px; border-radius: 3px; margin-right: 3px; }
    .dk-skel { display: inline-block; height: 1.2rem; width: 56px; border-radius: 6px; background: linear-gradient(90deg, #e2e8f0 25%, #f1f5f9 50%, #e2e8f0 75%); background-size: 200% 100%; animation: dks 1.2s infinite; }
    @keyframes dks { to { background-position: -200% 0; } }
    .dk-loading { opacity: .55; pointer-events: none; }
    .spin { animation: dksp 1s linear infinite; }
    @keyframes dksp { to { transform: rotate(360deg); } }
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
              <h4 class="fw-bold mb-0 text-dark"><i class="ti ti-user-check text-primary me-2"></i>ID Satu Sehat Dokter</h4>
              <div class="text-dark small" id="dkInfo">Dokter aktif wajib punya NIK &amp; ID IHS (Practitioner) agar kunjungan bisa dikirim ke Satu Sehat.</div>
            </div>
            <button type="button" class="btn btn-light border" id="dkRefresh" title="Muat ulang"><i class="ti ti-refresh"></i></button>
          </div>

          <div class="alert alert-danger d-none" id="dkErr"></div>

          <div class="row g-3 mb-3">
            <div class="col-6 col-xl-3"><div class="dk-kpi"><div class="n" id="kTotal"><span class="dk-skel"></span></div><div class="l">Dokter aktif</div><div class="s" id="kTotalS">&nbsp;</div></div></div>
            <div class="col-6 col-xl-3"><div class="dk-kpi"><div class="n" id="kLengkap"><span class="dk-skel"></span></div><div class="l">Punya ID IHS</div><div class="s" id="kLengkapS">&nbsp;</div></div></div>
            <div class="col-6 col-xl-3"><div class="dk-kpi"><div class="n" id="kMasalah"><span class="dk-skel"></span></div><div class="l">Perlu tindakan</div><div class="s" id="kMasalahS">&nbsp;</div></div></div>
            <div class="col-6 col-xl-3"><div class="dk-kpi"><div class="n" id="kDampak"><span class="dk-skel"></span></div><div class="l">Kunjungan terdampak (30 hari)</div><div class="s" id="kDampakS">&nbsp;</div></div></div>
          </div>

          <div class="card border-0 shadow-sm" style="border-radius:16px">
            <div class="card-body p-3 p-md-4">
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                <div class="d-flex flex-wrap gap-2" id="dkFilter">
                  <button type="button" class="dk-chip on" data-f="">Semua</button>
                  <button type="button" class="dk-chip" data-f="kurang">Perlu perhatian (&lt; 100%)</button>
                  <button type="button" class="dk-chip" data-f="lengkap">Semua lengkap</button>
                  <button type="button" class="dk-chip" data-f="dobel">NIK dobel</button>
                  <button type="button" class="dk-chip" data-f="kosong">Belum ada dokter</button>
                </div>
                <div class="small text-dark d-flex flex-wrap gap-2 align-items-center">
                  <span><i class="dk-dot" style="background:#16a34a"></i> Punya IHS</span>
                  <span><i class="dk-dot" style="background:#f59e0b"></i> Belum dicari</span>
                  <span><i class="dk-dot" style="background:#ef4444"></i> NIK bermasalah</span>
                </div>
              </div>
              <div class="table-responsive" id="dkWrap">
                <table class="table table-hover align-middle w-100" id="dkTable">
                  <thead>
                    <tr>
                      <th class="text-center" width="70">Aksi</th>
                      <th>Faskes</th>
                      <th class="text-end">Dokter</th>
                      <th class="text-center">Punya IHS</th>
                      <th class="text-center" title="NIK 16 digit, ID IHS belum dicari">Belum dicari</th>
                      <th class="text-center" title="NIK kosong atau bukan 16 digit">NIK bermasalah</th>
                      <th class="text-center" title="NIK yang sama dipakai lebih dari satu dokter">NIK dobel</th>
                      <th class="text-end" title="Kunjungan 30 hari terakhir yang dokternya belum punya IHS">Terdampak</th>
                      <th>Kelengkapan</th>
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

  <?php require '../admin/library.php'; ?>
  <script>
    $(function() {
      const API = 'controller/master/doctorIDSHController.php';
      const $i = id => document.getElementById(id);
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const fmt = n => Number(n || 0).toLocaleString('id-ID');
      const pc = n => n == null ? '-' : String(n).replace('.', ',') + '%';
      const warna = p => p >= 100 ? '#16a34a' : p >= 70 ? '#d97706' : '#dc2626';
      let D = null, filter = '', seq = 0;
      const angka = (v, k) => v ? `<span class="dk-n ${k}">${fmt(v)}</span>` : '<span class="dk-n x">0</span>';

      const tabel = $('#dkTable').DataTable({
        data: [],
        deferRender: true,
        pageLength: 25,
        order: [[8, 'asc']],
        autoWidth: false,
        language: {
          search: 'Cari faskes:', lengthMenu: 'Tampil _MENU_', info: '_START_–_END_ dari _TOTAL_ faskes', infoEmpty: '0 faskes',
          infoFiltered: '(dari _MAX_)', zeroRecords: 'Tidak ada faskes yang cocok', emptyTable: 'Belum ada data.',
          paginate: { previous: 'Sebelumnya', next: 'Berikutnya' }
        },
        columns: [{
            data: null, orderable: false, searchable: false, className: 'text-center',
            render: (d, t, r) => `<a class="btn btn-sm btn-primary" title="Lihat daftar dokter faskes ini" href="module/admin/doctor-idsh?id_customer=${r.id_customer}"><i class="ti ti-eye"></i></a>`
          },
          { data: 'nama', render: (v, t) => t === 'display' ? `<div class="dk-nama">${esc(v)}</div>` : v },
          { data: 'total', className: 'text-end', render: (v, t) => t === 'display' ? `<b>${fmt(v)}</b>` : v },
          { data: 'lengkap', className: 'text-center', render: (v, t) => t === 'display' ? angka(v, 'g') : v },
          { data: 'cari', className: 'text-center', render: (v, t) => t === 'display' ? angka(v, 'y') : v },
          { data: 'nik', className: 'text-center', render: (v, t) => t === 'display' ? angka(v, 'r') : v },
          { data: 'dobel', className: 'text-center', render: (v, t) => t === 'display' ? angka(v, 'r') : v },
          { data: 'terdampak', className: 'text-end', render: (v, t, r) => t === 'display' ? (v ? `<b class="text-danger">${fmt(v)}</b><div class="small text-dark">dari ${fmt(r.kunjungan)}</div>` : '<span class="text-dark">0</span>') : v },
          {
            data: 'pct',
            render: (v, t, r) => {
              if (t !== 'display') return v === null ? 999 : v;
              if (v === null) return '<span class="dk-n x">–</span>';
              const w = x => (x / r.total * 100) + '%';
              return `<div class="d-flex align-items-center gap-2"><div class="dk-stack flex-grow-1"><i style="width:${w(r.lengkap)};background:#16a34a"></i><i style="width:${w(r.cari)};background:#f59e0b"></i><i style="width:${w(r.nik)};background:#ef4444"></i></div><b style="color:${warna(v)};width:50px;text-align:right">${pc(v)}</b></div>`;
            }
          }
        ]
      });

      $.fn.dataTable.ext.search.push((s, d, idx) => {
        if (s.nTable.id !== 'dkTable' || !filter) return true;
        const r = tabel.row(idx).data();
        if (!r) return true;
        if (filter === 'kosong') return r.total === 0;
        if (filter === 'kurang') return r.total > 0 && r.lengkap < r.total;
        if (filter === 'lengkap') return r.total > 0 && r.lengkap === r.total;
        if (filter === 'dobel') return r.dobel > 0;
        return true;
      });

      function tampil() {
        const t = D.total, f = D.faskes;
        $i('kTotal').textContent = fmt(t.total);
        $i('kTotalS').textContent = fmt(f.filter(x => x.total > 0).length) + ' dari ' + fmt(f.length) + ' faskes punya dokter';
        $i('kLengkap').textContent = pc(t.pct);
        $i('kLengkapS').textContent = fmt(t.lengkap) + ' dari ' + fmt(t.total) + ' dokter';
        $i('kMasalah').textContent = fmt(t.cari + t.nik);
        $i('kMasalahS').textContent = fmt(t.nik) + ' NIK bermasalah · ' + fmt(t.cari) + ' belum dicari IHS' + (t.dobel ? ' · ' + fmt(t.dobel) + ' NIK dobel' : '');
        $i('kDampak').textContent = fmt(t.terdampak);
        $i('kDampakS').textContent = t.kunjungan ? pc(Math.round(t.terdampak / t.kunjungan * 1000) / 10) + ' dari ' + fmt(t.kunjungan) + ' kunjungan' : '-';
        $i('dkInfo').textContent = 'Dokter aktif wajib punya NIK & ID IHS (Practitioner) agar kunjungan bisa dikirim ke Satu Sehat.' + (D.ms ? ' · ' + D.ms + ' ms' : '');
        tabel.clear().rows.add(f).draw();
      }

      async function muat() {
        const ini = ++seq;
        $i('dkErr').classList.add('d-none');
        $i('dkWrap').classList.add('dk-loading');
        $i('dkRefresh').querySelector('i').classList.add('spin');
        try {
          const r = await fetch(API, { credentials: 'same-origin', cache: 'no-store' });
          const j = await r.json().catch(() => null);
          if (ini !== seq) return;
          if (!r.ok || !j || j.status !== 'success') throw new Error((j && j.message) || 'Gagal memuat data (HTTP ' + r.status + ').');
          D = j;
          tampil();
        } catch (e) {
          $i('dkErr').textContent = e.message;
          $i('dkErr').classList.remove('d-none');
        } finally {
          if (ini === seq) {
            $i('dkWrap').classList.remove('dk-loading');
            $i('dkRefresh').querySelector('i').classList.remove('spin');
          }
        }
      }

      document.querySelectorAll('#dkFilter .dk-chip').forEach(b => b.addEventListener('click', () => {
        filter = b.dataset.f;
        document.querySelectorAll('#dkFilter .dk-chip').forEach(x => x.classList.toggle('on', x === b));
        tabel.draw();
      }));
      $i('dkRefresh').addEventListener('click', muat);
      muat();
    });
  </script>
</body>

</html>
