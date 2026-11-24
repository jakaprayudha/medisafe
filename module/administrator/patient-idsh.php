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
    .pi-kpi { border: 1px solid #e2e8f0; border-radius: 14px; padding: 14px 16px; background: #fff; height: 100%; }
    .pi-kpi .n { font-size: 24px; font-weight: 800; color: #0f172a; line-height: 1.1; }
    .pi-kpi .l { font-size: 12px; font-weight: 600; color: #334155; margin-top: 2px; }
    .pi-kpi .s { font-size: 11px; color: #475569; }
    .pi-chip { border: 1px solid #cbd5e1; background: #fff; color: #334155; border-radius: 999px; padding: 4px 12px; font-size: 12px; font-weight: 700; cursor: pointer; }
    .pi-chip:hover { background: #f1f5f9; }
    .pi-chip.on { background: #1e293b; border-color: #1e293b; color: #fff; }
    #piTable th { font-size: 11px; font-weight: 700; color: #334155; white-space: nowrap; text-transform: uppercase; letter-spacing: .3px; }
    #piTable td { vertical-align: middle; font-size: 13px; color: #0f172a; }
    .pi-nama { font-weight: 700; color: #0f172a; min-width: 190px; }
    .pi-sub { font-size: 11px; color: #475569; }
    .pi-n { display: inline-block; min-width: 48px; text-align: center; padding: 3px 8px; border-radius: 7px; font-size: 12px; font-weight: 700; }
    .pi-n.g { background: #dcfce7; color: #166534; }
    .pi-n.b { background: #dbeafe; color: #1e40af; }
    .pi-n.y { background: #fef3c7; color: #92400e; }
    .pi-n.r { background: #fee2e2; color: #991b1b; }
    .pi-n.x { background: #f1f5f9; color: #475569; }
    .pi-stack { display: flex; height: 9px; border-radius: 5px; overflow: hidden; background: #e2e8f0; min-width: 70px; }
    .pi-stack > i { display: block; height: 100%; }
    .pi-skel { display: inline-block; height: 1.2rem; width: 56px; border-radius: 6px; background: linear-gradient(90deg, #e2e8f0 25%, #f1f5f9 50%, #e2e8f0 75%); background-size: 200% 100%; animation: pis 1.2s infinite; }
    @keyframes pis { to { background-position: -200% 0; } }
    .pi-loading { opacity: .55; pointer-events: none; }
    .spin { animation: pisp 1s linear infinite; }
    @keyframes pisp { to { transform: rotate(360deg); } }
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
              <h4 class="fw-bold mb-0 text-dark"><i class="ti ti-id-badge-2 text-success me-2"></i>ID Satu Sehat Pasien</h4>
              <div class="text-dark small" id="piInfo">Kelengkapan ID IHS pasien di semua faskes. Pasien tanpa ID IHS tidak bisa dikirim ke Satu Sehat.</div>
            </div>
            <button type="button" class="btn btn-light border" id="piRefresh" title="Muat ulang (abaikan cache)"><i class="ti ti-refresh"></i></button>
          </div>

          <div class="alert alert-danger d-none" id="piErr"></div>

          <div class="row g-3 mb-3">
            <div class="col-6 col-xl-3"><div class="pi-kpi"><div class="n" id="kTotal"><span class="pi-skel"></span></div><div class="l">Total pasien</div><div class="s" id="kTotalS">&nbsp;</div></div></div>
            <div class="col-6 col-xl-3"><div class="pi-kpi"><div class="n" id="kLengkap"><span class="pi-skel"></span></div><div class="l">Punya ID IHS</div><div class="s" id="kLengkapS">&nbsp;</div></div></div>
            <div class="col-6 col-xl-3"><div class="pi-kpi"><div class="n" id="kAntre"><span class="pi-skel"></span></div><div class="l">Menunggu sinkron</div><div class="s">NIK valid, belum dicek</div></div></div>
            <div class="col-6 col-xl-3"><div class="pi-kpi"><div class="n" id="kMasalah"><span class="pi-skel"></span></div><div class="l">Perlu perbaikan data</div><div class="s" id="kMasalahS">&nbsp;</div></div></div>
          </div>

          <div class="card border-0 shadow-sm" style="border-radius:16px">
            <div class="card-body p-3 p-md-4">
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                <div class="d-flex flex-wrap gap-2" id="piFilter">
                  <button type="button" class="pi-chip on" data-f="">Semua</button>
                  <button type="button" class="pi-chip" data-f="kurang">Perlu perhatian (&lt; 70%)</button>
                  <button type="button" class="pi-chip" data-f="baik">Baik (≥ 90%)</button>
                  <button type="button" class="pi-chip" data-f="kosong">Belum ada pasien</button>
                </div>
                <div class="small text-dark d-flex flex-wrap gap-2 align-items-center">
                  <span><i class="pi-dot" style="background:#16a34a"></i> Lengkap</span>
                  <span><i class="pi-dot" style="background:#3b82f6"></i> Menunggu</span>
                  <span><i class="pi-dot" style="background:#f59e0b"></i> Tidak ditemukan</span>
                  <span><i class="pi-dot" style="background:#ef4444"></i> NIK bermasalah</span>
                </div>
              </div>
              <div class="table-responsive" id="piWrap">
                <table class="table table-hover align-middle w-100" id="piTable">
                  <thead>
                    <tr>
                      <th class="text-center" width="70">Aksi</th>
                      <th>Faskes</th>
                      <th class="text-end">Pasien</th>
                      <th class="text-center">Lengkap</th>
                      <th class="text-center" title="NIK valid, belum dicek sinkronisasi">Menunggu</th>
                      <th class="text-center" title="Sudah dicek tetapi tidak terdaftar di Satu Sehat">Tidak ditemukan</th>
                      <th class="text-center" title="NIK kosong atau bukan 16 digit">NIK bermasalah</th>
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

  <style>.pi-dot { display: inline-block; width: 10px; height: 10px; border-radius: 3px; margin-right: 3px; }</style>
  <?php require '../admin/library.php'; ?>
  <script>
    $(function() {
      const API = 'controller/master/patientIDSHController.php';
      const $i = id => document.getElementById(id);
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const fmt = n => Number(n || 0).toLocaleString('id-ID');
      const pc = n => n == null ? '-' : String(n).replace('.', ',') + '%';
      const warna = p => p >= 90 ? '#16a34a' : p >= 70 ? '#d97706' : '#dc2626';
      let D = null, filter = '', seq = 0;

      const angka = (v, k) => v ? `<span class="pi-n ${k}">${fmt(v)}</span>` : '<span class="pi-n x">0</span>';

      const tabel = $('#piTable').DataTable({
        data: [],
        deferRender: true,
        pageLength: 25,
        order: [[7, 'asc']],
        autoWidth: false,
        language: {
          search: 'Cari faskes:', lengthMenu: 'Tampil _MENU_', info: '_START_–_END_ dari _TOTAL_ faskes', infoEmpty: '0 faskes',
          infoFiltered: '(dari _MAX_)', zeroRecords: 'Tidak ada faskes yang cocok', emptyTable: 'Belum ada data.',
          paginate: { previous: 'Sebelumnya', next: 'Berikutnya' }
        },
        columns: [{
            data: null, orderable: false, searchable: false, className: 'text-center',
            render: (d, t, r) => `<a class="btn btn-sm btn-primary" title="Lihat daftar pasien faskes ini" href="module/admin/patient-idsh?id_customer=${r.id_customer}"><i class="ti ti-eye"></i></a>`
          },
          { data: 'nama', render: (v, t) => t === 'display' ? `<div class="pi-nama">${esc(v)}</div>` : v },
          { data: 'total', className: 'text-end', render: (v, t) => t === 'display' ? `<b>${fmt(v)}</b>` : v },
          { data: 'lengkap', className: 'text-center', render: (v, t) => t === 'display' ? angka(v, 'g') : v },
          { data: 'antre', className: 'text-center', render: (v, t) => t === 'display' ? angka(v, 'b') : v },
          { data: 'tidak_ditemukan', className: 'text-center', render: (v, t) => t === 'display' ? angka(v, 'y') : v },
          { data: 'nik', className: 'text-center', render: (v, t) => t === 'display' ? angka(v, 'r') : v },
          {
            data: 'pct',
            render: (v, t, r) => {
              if (t !== 'display') return v === null ? 999 : v;
              if (v === null) return '<span class="pi-n x">–</span>';
              const w = x => (x / r.total * 100) + '%';
              return `<div class="d-flex align-items-center gap-2"><div class="pi-stack flex-grow-1"><i style="width:${w(r.lengkap)};background:#16a34a"></i><i style="width:${w(r.antre)};background:#3b82f6"></i><i style="width:${w(r.tidak_ditemukan)};background:#f59e0b"></i><i style="width:${w(r.nik)};background:#ef4444"></i></div><b style="color:${warna(v)};width:50px;text-align:right">${pc(v)}</b></div>`;
            }
          }
        ]
      });

      $.fn.dataTable.ext.search.push((s, d, idx) => {
        if (s.nTable.id !== 'piTable' || !filter) return true;
        const r = tabel.row(idx).data();
        if (!r) return true;
        if (filter === 'kosong') return r.total === 0;
        if (filter === 'kurang') return r.pct !== null && r.pct < 70;
        if (filter === 'baik') return r.pct !== null && r.pct >= 90;
        return true;
      });

      function tampil() {
        const t = D.total, f = D.faskes;
        const masalah = t.nik + t.tidak_ditemukan;
        $i('kTotal').textContent = fmt(t.total);
        $i('kTotalS').textContent = fmt(f.filter(x => x.total > 0).length) + ' dari ' + fmt(f.length) + ' faskes punya pasien';
        $i('kLengkap').textContent = pc(t.pct);
        $i('kLengkapS').textContent = fmt(t.lengkap) + ' dari ' + fmt(t.total) + ' pasien';
        $i('kAntre').textContent = fmt(t.antre);
        $i('kMasalah').textContent = fmt(masalah);
        $i('kMasalahS').textContent = fmt(t.nik) + ' NIK bermasalah · ' + fmt(t.tidak_ditemukan) + ' tidak ditemukan';
        $i('piInfo').textContent = 'Kelengkapan ID IHS pasien di semua faskes. Pasien tanpa ID IHS tidak bisa dikirim ke Satu Sehat.' +
          (D.cache > 0 ? ' · data tersimpan ' + Math.round(D.cache / 60) + ' mnt lalu' : '') + (D.ms ? ' · ' + D.ms + ' ms' : '');
        tabel.clear().rows.add(f).draw();
      }

      async function muat(segar) {
        const ini = ++seq;
        $i('piErr').classList.add('d-none');
        $i('piWrap').classList.add('pi-loading');
        $i('piRefresh').querySelector('i').classList.add('spin');
        try {
          const r = await fetch(API + (segar ? '?refresh=1' : ''), { credentials: 'same-origin', cache: 'no-store' });
          const j = await r.json().catch(() => null);
          if (ini !== seq) return;
          if (!r.ok || !j || j.status !== 'success') throw new Error((j && j.message) || 'Gagal memuat data (HTTP ' + r.status + ').');
          D = j;
          tampil();
        } catch (e) {
          $i('piErr').textContent = e.message;
          $i('piErr').classList.remove('d-none');
        } finally {
          if (ini === seq) {
            $i('piWrap').classList.remove('pi-loading');
            $i('piRefresh').querySelector('i').classList.remove('spin');
          }
        }
      }

      document.querySelectorAll('#piFilter .pi-chip').forEach(b => b.addEventListener('click', () => {
        filter = b.dataset.f;
        document.querySelectorAll('#piFilter .pi-chip').forEach(x => x.classList.toggle('on', x === b));
        tabel.draw();
      }));
      $i('piRefresh').addEventListener('click', () => muat(true));
      muat();
    });
  </script>
</body>

</html>
