<?php
$title = 'Monitoring RME';
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
    .mv-kpi { border: 1px solid #e2e8f0; border-radius: 14px; padding: 14px 16px; background: #fff; height: 100%; }
    .mv-kpi .n { font-size: 24px; font-weight: 800; color: #0f172a; line-height: 1.1; }
    .mv-kpi .l { font-size: 12px; font-weight: 600; color: #334155; margin-top: 2px; }
    .mv-kpi .s { font-size: 11px; color: #475569; }
    .mv-chip { border: 1px solid #cbd5e1; background: #fff; color: #334155; border-radius: 999px; padding: 4px 12px; font-size: 12px; font-weight: 700; cursor: pointer; }
    .mv-chip:hover { background: #f1f5f9; }
    .mv-chip.on { background: #1e293b; border-color: #1e293b; color: #fff; }
    .mv-sel { height: 34px; border-radius: 9px; border: 1px solid #cbd5e1; padding: 0 10px; font-size: 13px; color: #0f172a; background: #fff; }
    #mvTable th { font-size: 11px; font-weight: 700; color: #334155; white-space: nowrap; text-transform: uppercase; letter-spacing: .3px; }
    #mvTable td { vertical-align: middle; font-size: 13px; color: #0f172a; }
    .mv-nama { font-weight: 700; color: #0f172a; min-width: 190px; }
    .mv-sub { font-size: 11px; color: #475569; }
    .mv-c { display: inline-block; min-width: 46px; text-align: center; padding: 3px 6px; border-radius: 7px; font-size: 12px; font-weight: 700; }
    .mv-c.g { background: #dcfce7; color: #166534; }
    .mv-c.y { background: #fef3c7; color: #92400e; }
    .mv-c.r { background: #fee2e2; color: #991b1b; }
    .mv-c.x { background: #f1f5f9; color: #475569; }
    .mv-bar { height: 8px; border-radius: 4px; background: #e2e8f0; overflow: hidden; min-width: 90px; }
    .mv-bar > div { height: 100%; }
    .mv-skel { display: inline-block; height: 1.2rem; width: 56px; border-radius: 6px; background: linear-gradient(90deg, #e2e8f0 25%, #f1f5f9 50%, #e2e8f0 75%); background-size: 200% 100%; animation: mvs 1.2s infinite; }
    @keyframes mvs { to { background-position: -200% 0; } }
    .mv-loading { opacity: .55; pointer-events: none; }
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
              <h4 class="fw-bold mb-0 text-dark"><i class="ti ti-clipboard-check text-primary me-2"></i>Monitoring RME Faskes</h4>
              <div class="text-dark small" id="mvInfo">Kelengkapan rekam medis elektronik rawat jalan di semua faskes.</div>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
              <input type="date" id="mvFrom" class="mv-sel">
              <span class="small text-dark">s.d.</span>
              <input type="date" id="mvTo" class="mv-sel">
              <button type="button" class="btn btn-light border" id="mvRefresh" title="Muat ulang (abaikan cache)"><i class="ti ti-refresh"></i></button>
            </div>
          </div>

          <div class="d-flex flex-wrap gap-2 mb-3" id="mvRanges">
            <button type="button" class="mv-chip" data-r="hari">Hari ini</button>
            <button type="button" class="mv-chip" data-r="7">7 hari</button>
            <button type="button" class="mv-chip" data-r="30">30 hari</button>
            <button type="button" class="mv-chip on" data-r="bulan">Bulan ini</button>
          </div>

          <div class="alert alert-danger d-none" id="mvErr"></div>

          <div class="row g-3 mb-3">
            <div class="col-6 col-xl-3"><div class="mv-kpi"><div class="n" id="kFaskes"><span class="mv-skel"></span></div><div class="l">Faskes aktif</div><div class="s" id="kFaskesS">&nbsp;</div></div></div>
            <div class="col-6 col-xl-3"><div class="mv-kpi"><div class="n" id="kJalan"><span class="mv-skel"></span></div><div class="l">Kunjungan rawat jalan</div><div class="s" id="kJalanS">&nbsp;</div></div></div>
            <div class="col-6 col-xl-3"><div class="mv-kpi"><div class="n" id="kPct"><span class="mv-skel"></span></div><div class="l">RME lengkap (semua faskes)</div><div class="s" id="kPctS">&nbsp;</div></div></div>
            <div class="col-6 col-xl-3"><div class="mv-kpi"><div class="n" id="kLemah"><span class="mv-skel"></span></div><div class="l">Faskes perlu perhatian</div><div class="s">Kelengkapan di bawah 70%</div></div></div>
          </div>

          <div class="card border-0 shadow-sm" style="border-radius:16px">
            <div class="card-body p-3 p-md-4">
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                <div class="d-flex flex-wrap gap-2" id="mvFilter">
                  <button type="button" class="mv-chip on" data-f="">Semua</button>
                  <button type="button" class="mv-chip" data-f="kurang">Perlu perhatian</button>
                  <button type="button" class="mv-chip" data-f="baik">Baik (≥ 90%)</button>
                  <button type="button" class="mv-chip" data-f="kosong">Tanpa kunjungan</button>
                </div>
                <div class="small text-dark" id="mvLegend">
                  <span class="mv-c g">≥90%</span> <span class="mv-c y">70–89%</span> <span class="mv-c r">&lt;70%</span> <span class="mv-c x">–</span> tidak ada data
                </div>
              </div>
              <div class="table-responsive" id="mvWrap">
                <table class="table table-hover align-middle w-100" id="mvTable">
                  <thead>
                    <tr>
                      <th class="text-center" width="70">Aksi</th>
                      <th>Faskes</th>
                      <th class="text-end">Jalan</th>
                      <th class="text-end">Ranap</th>
                      <th class="text-center" title="Tekanan darah, nadi, suhu">TTV</th>
                      <th class="text-center">BB/TB</th>
                      <th class="text-center">Anamnesa</th>
                      <th class="text-center">Diagnosis</th>
                      <th class="text-center" title="Tindakan atau resep obat">Terapi</th>
                      <th class="text-center">Selesai</th>
                      <th class="text-center" title="Khusus pasien BPJS">PCare</th>
                      <th class="text-center">SatuSehat</th>
                      <th>Lengkap</th>
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
      const API = 'controller/master/RMEpatientIDSHController.php';
      const $i = id => document.getElementById(id);
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const fmt = n => Number(n || 0).toLocaleString('id-ID');
      const BLN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      const tgl = s => s ? (+s.slice(8)) + ' ' + BLN[+s.slice(5, 7) - 1] + ' ' + s.slice(0, 4) : '-';
      const ymd = d => d.toLocaleDateString('sv-SE', { timeZone: 'Asia/Jakarta' });
      const warna = p => p >= 90 ? '#16a34a' : p >= 70 ? '#d97706' : '#dc2626';
      let D = null, filter = '', seq = 0;

      function rentang(r) {
        const now = new Date(ymd(new Date()) + 'T00:00:00');
        let a = new Date(now);
        if (r === '7') a.setDate(a.getDate() - 6);
        if (r === '30') a.setDate(a.getDate() - 29);
        if (r === 'bulan') a = new Date(now.getFullYear(), now.getMonth(), 1);
        return [ymd(a), ymd(now)];
      }
      function tandai() {
        document.querySelectorAll('#mvRanges .mv-chip').forEach(b => {
          const [a, z] = rentang(b.dataset.r);
          b.classList.toggle('on', a === $i('mvFrom').value && z === $i('mvTo').value);
        });
      }
      function setRange(r) { const [a, b] = rentang(r); $i('mvFrom').value = a; $i('mvTo').value = b; tandai(); }

      // sel persentase: jumlah terisi / pembagi
      function sel(n, d) {
        if (!d) return '<span class="mv-c x">–</span>';
        const p = n / d * 100;
        const k = p >= 90 ? 'g' : p >= 70 ? 'y' : 'r';
        return `<span class="mv-c ${k}" title="${fmt(n)} dari ${fmt(d)}">${p.toFixed(p === 100 || p === 0 ? 0 : 1).replace('.', ',')}%</span>`;
      }

      const tabel = $('#mvTable').DataTable({
        data: [],
        deferRender: true,
        pageLength: 25,
        order: [[12, 'asc']],
        autoWidth: false,
        language: {
          search: 'Cari faskes:', lengthMenu: 'Tampil _MENU_', info: '_START_–_END_ dari _TOTAL_ faskes', infoEmpty: '0 faskes',
          infoFiltered: '(dari _MAX_)', zeroRecords: 'Tidak ada faskes yang cocok', emptyTable: 'Belum ada data.',
          paginate: { previous: 'Sebelumnya', next: 'Berikutnya' }
        },
        columns: [{
            data: null, orderable: false, searchable: false, className: 'text-center',
            render: (d, t, r) => `<a class="btn btn-sm btn-primary" title="Buka monitoring RME faskes ini" href="module/admin/monitoring-visit?id_customer=${r.id_customer}&from=${D.periode.from}&to=${D.periode.to}"><i class="ti ti-eye"></i></a>`
          },
          { data: 'nama', render: (v, t, r) => t === 'display' ? `<div class="mv-nama">${esc(v)}</div><div class="mv-sub">${r.bpjs ? fmt(r.bpjs) + ' BPJS' : 'Tanpa BPJS'}</div>` : v },
          { data: 'jalan', className: 'text-end', render: (v, t) => t === 'display' ? `<b>${fmt(v)}</b>` : v },
          { data: 'ranap', className: 'text-end', render: (v, t) => t === 'display' ? fmt(v) : v },
          ...[['ttv'], ['bbtb'], ['anam'], ['dx'], ['terapi'], ['selesai']].map(([k]) => ({
            data: null, className: 'text-center',
            render: (d, t, r) => t === 'display' ? sel(r[k], r.jalan) : (r.jalan ? r[k] / r.jalan : -1)
          })),
          { data: null, className: 'text-center', render: (d, t, r) => t === 'display' ? sel(r.pcare, r.bpjs) : (r.bpjs ? r.pcare / r.bpjs : -1) },
          { data: null, className: 'text-center', render: (d, t, r) => t === 'display' ? sel(r.satusehat, r.jalan) : (r.jalan ? r.satusehat / r.jalan : -1) },
          {
            data: 'pct',
            render: (v, t) => {
              if (t !== 'display') return v === null ? 999 : v;
              if (v === null) return '<span class="mv-c x">–</span>';
              return `<div class="d-flex align-items-center gap-2"><div class="mv-bar flex-grow-1"><div style="width:${v}%;background:${warna(v)}"></div></div><b style="color:${warna(v)};width:48px;text-align:right">${String(v).replace('.', ',')}%</b></div>`;
            }
          }
        ]
      });

      $.fn.dataTable.ext.search.push((s, d, idx) => {
        if (s.nTable.id !== 'mvTable' || !filter) return true;
        const r = tabel.row(idx).data();
        if (!r) return true;
        if (filter === 'kosong') return r.jalan === 0 && r.ranap === 0;
        if (filter === 'kurang') return r.pct !== null && r.pct < 70;
        if (filter === 'baik') return r.pct !== null && r.pct >= 90;
        return true;
      });

      function tampil() {
        const t = D.total, f = D.faskes;
        const aktif = f.filter(x => x.jalan > 0).length;
        $i('kFaskes').textContent = fmt(f.length);
        $i('kFaskesS').textContent = fmt(aktif) + ' punya kunjungan di periode ini';
        $i('kJalan').textContent = fmt(t.jalan);
        $i('kJalanS').textContent = '+ ' + fmt(t.ranap) + ' rawat inap';
        $i('kPct').textContent = t.pct === null ? '-' : String(t.pct).replace('.', ',') + '%';
        $i('kPctS').textContent = fmt(t.lengkap) + ' dari ' + fmt(t.jalan) + ' kunjungan 100% terisi';
        $i('kLemah').textContent = fmt(f.filter(x => x.pct !== null && x.pct < 70).length);
        $i('mvInfo').textContent = tgl(D.periode.from) + (D.periode.from !== D.periode.to ? ' – ' + tgl(D.periode.to) : '') +
          ' · Checklist: TTV, BB/TB, anamnesa, diagnosis, terapi, pemeriksaan selesai' +
          (D.cache > 0 ? ' · data tersimpan ' + D.cache + ' dtk lalu' : '') + (D.ms ? ' · ' + D.ms + ' ms' : '');
        tabel.clear().rows.add(f).draw();
      }

      async function muat(segar) {
        const ini = ++seq;
        $i('mvErr').classList.add('d-none');
        $i('mvWrap').classList.add('mv-loading');
        $i('mvRefresh').querySelector('i').classList.add('spin');
        try {
          const p = new URLSearchParams({ from: $i('mvFrom').value, to: $i('mvTo').value });
          if (segar) p.set('refresh', '1');
          const r = await fetch(API + '?' + p, { credentials: 'same-origin', cache: 'no-store' });
          const j = await r.json().catch(() => null);
          if (ini !== seq) return;
          if (!r.ok || !j || j.status !== 'success') throw new Error((j && j.message) || 'Gagal memuat data (HTTP ' + r.status + ').');
          D = j;
          tampil();
        } catch (e) {
          $i('mvErr').textContent = e.message;
          $i('mvErr').classList.remove('d-none');
        } finally {
          if (ini === seq) {
            $i('mvWrap').classList.remove('mv-loading');
            $i('mvRefresh').querySelector('i').classList.remove('spin');
          }
        }
      }

      document.querySelectorAll('#mvRanges .mv-chip').forEach(b => b.addEventListener('click', () => { setRange(b.dataset.r); muat(); }));
      document.querySelectorAll('#mvFilter .mv-chip').forEach(b => b.addEventListener('click', () => {
        filter = b.dataset.f;
        document.querySelectorAll('#mvFilter .mv-chip').forEach(x => x.classList.toggle('on', x === b));
        tabel.draw();
      }));
      let tunda;
      ['mvFrom', 'mvTo'].forEach(id => $i(id).addEventListener('change', () => { tandai(); clearTimeout(tunda); tunda = setTimeout(() => muat(), 300); }));
      $i('mvRefresh').addEventListener('click', () => muat(true));
      setRange('bulan');
      muat();
    });
  </script>
  <style>
    .spin { animation: mvspin 1s linear infinite; }
    @keyframes mvspin { to { transform: rotate(360deg); } }
  </style>
</body>

</html>
