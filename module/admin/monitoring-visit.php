<?php
$title = 'Monitoring RME';
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
    .rm-icon { width: 40px; height: 40px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
    .rm-tabs .nav-link { font-weight: 600; color: #5a6a85; border: 0; border-bottom: 3px solid transparent; border-radius: 0; padding: .6rem 1rem; }
    .rm-tabs .nav-link.active { color: #5d87ff; border-bottom-color: #5d87ff; background: none; }
    .rm-chips .btn { border-radius: 20px; font-size: .75rem; padding: .2rem .75rem; background: #fff; border: 1px solid #e5eaef; color: #5a6a85; }
    .rm-chips .btn.active { background: #5d87ff; border-color: #5d87ff; color: #fff; }
    .rm-kpi { border: 1px solid #e5eaef; border-radius: 12px; background: #fff; padding: 14px 16px; height: 100%; }
    .rm-val { font-size: 1.35rem; font-weight: 700; color: #2a3547; line-height: 1.2; }
    .rm-label { font-size: .75rem; color: #6c757d; }
    .rm-sub { font-size: .7rem; color: #98a2b3; }
    .rm-sec { font-size: .72rem; font-weight: 700; color: #5a6a85; text-transform: uppercase; letter-spacing: .4px; }
    .rm-item { cursor: pointer; border-radius: 8px; padding: 6px 8px; margin: 0 -8px; transition: background .15s; }
    .rm-item:hover { background: #f6f8fb; }
    .rm-item.on { background: #f3f6ff; outline: 1px solid #c9d6ff; }
    .rm-bar { height: 8px; border-radius: 4px; background: #eef1f4; overflow: hidden; }
    .rm-bar > div { height: 100%; transition: width .3s; }
    .rm-dot { width: 22px; height: 22px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; font-size: .65rem; }
    .rm-dot.y { background: #dcfce7; color: #16a34a; }
    .rm-dot.n { background: #fee2e2; color: #dc2626; }
    .rm-dot.x { background: #f1f5f9; color: #cbd5e1; }
    .rm-dot.int.y { background: #dbeafe; color: #2563eb; }
    #rmTable td { font-size: .82rem; vertical-align: middle; white-space: nowrap; }
    #rmTable th { font-size: .7rem; color: #5a6a85; font-weight: 600; white-space: nowrap; }
    #rmTable th.rm-c { writing-mode: vertical-rl; transform: rotate(180deg); text-align: left; padding: 6px 4px; height: 110px; vertical-align: bottom; }
    #rmTable td.rm-c { text-align: center; padding: 4px 2px; }
    .rm-pct { font-weight: 700; font-size: .8rem; }
    .rm-loading { position: relative; }
    .rm-loading::after { content: ''; position: absolute; inset: 0; background: rgba(255, 255, 255, .6); border-radius: inherit; z-index: 3; }
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
            <div class="card-body pb-0">
              <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                <span class="rm-icon bg-primary-subtle text-primary"><i class="fas fa-clipboard-check"></i></span>
                <div class="min-w-0 flex-grow-1">
                  <h5 class="fw-semibold mb-0">Monitoring Kelengkapan RME</h5>
                  <div class="text-muted small" id="rmPeriodeInfo">Checklist rekam medis elektronik per kunjungan</div>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                  <input type="date" id="rmFrom" class="form-control form-control-sm" style="width:auto">
                  <span class="small text-muted">s.d.</span>
                  <input type="date" id="rmTo" class="form-control form-control-sm" style="width:auto">
                  <button type="button" class="btn btn-light btn-sm" id="rmRefresh" title="Muat ulang"><i class="fas fa-sync-alt"></i></button>
                </div>
              </div>
              <div class="d-flex flex-wrap justify-content-between align-items-end gap-2">
                <ul class="nav rm-tabs" role="tablist">
                  <li class="nav-item"><button class="nav-link active" data-jenis="poli" type="button"><i class="fas fa-stethoscope me-1"></i>Poli (rawat jalan)</button></li>
                  <li class="nav-item"><button class="nav-link" data-jenis="ranap" type="button"><i class="fas fa-procedures me-1"></i>Rawat inap</button></li>
                </ul>
                <div class="rm-chips d-flex flex-wrap gap-1 pb-2" id="rmRanges">
                  <button type="button" class="btn" data-r="hari">Hari ini</button>
                  <button type="button" class="btn" data-r="kemarin">Kemarin</button>
                  <button type="button" class="btn" data-r="7">7 hari</button>
                  <button type="button" class="btn" data-r="30">30 hari</button>
                  <button type="button" class="btn" data-r="bulan">Bulan ini</button>
                </div>
              </div>
            </div>
          </div>
          <div class="alert alert-danger d-none" id="rmErr"></div>

          <!-- KPI -->
          <div class="row g-3 mb-3">
            <div class="col-6 col-xl-3"><div class="rm-kpi"><div class="d-flex align-items-center gap-2"><span class="rm-icon bg-primary-subtle text-primary"><i class="fas fa-users"></i></span>
              <div class="min-w-0"><div class="rm-val" id="kTotal"><span class="skel"></span></div><div class="rm-label" id="kTotalLbl">Kunjungan</div></div></div><div class="rm-sub mt-1" id="kTotalSub">&nbsp;</div></div></div>
            <div class="col-6 col-xl-3"><div class="rm-kpi"><div class="d-flex align-items-center gap-2"><span class="rm-icon bg-success-subtle text-success"><i class="fas fa-check-double"></i></span>
              <div class="min-w-0"><div class="rm-val" id="kLengkap"><span class="skel"></span></div><div class="rm-label">RME lengkap</div></div></div><div class="rm-sub mt-1" id="kLengkapSub">&nbsp;</div></div></div>
            <div class="col-6 col-xl-3"><div class="rm-kpi"><div class="d-flex align-items-center gap-2"><span class="rm-icon bg-info-subtle text-info"><i class="fas fa-percentage"></i></span>
              <div class="min-w-0"><div class="rm-val" id="kRata"><span class="skel"></span></div><div class="rm-label">Rata-rata kelengkapan</div></div></div><div class="rm-sub mt-1">Per kunjungan, tanpa item integrasi</div></div></div>
            <div class="col-6 col-xl-3"><div class="rm-kpi"><div class="d-flex align-items-center gap-2"><span class="rm-icon bg-danger-subtle text-danger"><i class="fas fa-exclamation-circle"></i></span>
              <div class="min-w-0"><div class="rm-val" id="kLemah" style="font-size:1rem"><span class="skel"></span></div><div class="rm-label">Item paling sering kosong</div></div></div><div class="rm-sub mt-1" id="kLemahSub">&nbsp;</div></div></div>
          </div>

          <div class="row g-3 mb-3">
            <!-- CHECKLIST -->
            <div class="col-xl-5"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-2"><div class="rm-sec">Kelengkapan per item</div><small class="text-muted">Klik item untuk menyaring yang belum</small></div>
              <div id="rmItems"></div>
            </div></div></div>
            <!-- PER DOKTER -->
            <div class="col-xl-7"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body">
              <div class="rm-sec mb-2">Per dokter</div>
              <div class="table-responsive" style="max-height:420px">
                <table class="table table-sm align-middle mb-0" id="rmDokter">
                  <thead><tr><th class="small text-muted">Dokter</th><th class="small text-muted text-end">Kunjungan</th><th class="small text-muted text-end">Lengkap</th><th class="small text-muted" style="min-width:180px">Rata-rata kelengkapan</th></tr></thead>
                  <tbody></tbody>
                </table>
              </div>
            </div></div></div>
          </div>

          <!-- TABEL -->
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                <div><div class="rm-sec">Checklist per kunjungan</div><div class="small text-muted" id="rmInfo"></div></div>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                  <div class="rm-chips d-flex gap-1" id="rmStatus">
                    <button type="button" class="btn active" data-s="">Semua</button>
                    <button type="button" class="btn" data-s="kurang">Belum lengkap</button>
                    <button type="button" class="btn" data-s="lengkap">Lengkap</button>
                  </div>
                  <select id="rmFDokter" class="form-select form-select-sm" style="width:auto"><option value="">Semua dokter</option></select>
                </div>
              </div>
              <div class="small text-muted mb-2">
                <span class="rm-dot y"><i class="fas fa-check"></i></span> terisi
                <span class="rm-dot n ms-2"><i class="fas fa-times"></i></span> belum
                <span class="rm-dot x ms-2">–</span> tidak berlaku
                <span class="rm-dot int y ms-2"><i class="fas fa-check"></i></span> integrasi (tidak dihitung)
              </div>
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 w-100" id="rmTable"><thead></thead><tbody></tbody></table>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
  <?php require '../admin/library.php'; ?>
  <script>
    (function() {
      const API = 'controller/dashboard/rmeMonitoringController.php';
      // superadmin membuka faskes tertentu dari module/administrator/monitoring-visit (?id_customer=&from=&to=)
      const QS = new URLSearchParams(location.search);
      const IDC = <?= $isSuper ? "(parseInt(QS.get('id_customer'), 10) || 0)" : '0' ?>;
      const $id = id => document.getElementById(id);
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const fmt = n => Number(n || 0).toLocaleString('id-ID');
      const pc = n => n == null ? '-' : String(n).replace('.', ',') + '%';
      const BLN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      const tgl = s => s ? (+s.slice(8)) + ' ' + BLN[+s.slice(5, 7) - 1] : '-';
      const ymd = d => d.toLocaleDateString('sv-SE', { timeZone: 'Asia/Jakarta' });
      const warna = p => p >= 90 ? '#13deb9' : p >= 70 ? '#ffae1f' : '#fa896b';
      let jenis = 'poli', D = null, rows = [], fItem = -1, fStatus = '', seq = 0, tabel = null;
      const rentangAwal = { poli: '7', ranap: '30' };

      function hitung(r) {
        const now = new Date(ymd(new Date()) + 'T00:00:00');
        let a = new Date(now), b = new Date(now);
        if (r === 'kemarin') { a.setDate(a.getDate() - 1); b = new Date(a); }
        if (r === '7') a.setDate(a.getDate() - 6);
        if (r === '30') a.setDate(a.getDate() - 29);
        if (r === 'bulan') a = new Date(now.getFullYear(), now.getMonth(), 1);
        return [ymd(a), ymd(b)];
      }
      function tandai() {
        document.querySelectorAll('#rmRanges .btn').forEach(b => { const [a, z] = hitung(b.dataset.r); b.classList.toggle('active', a === $id('rmFrom').value && z === $id('rmTo').value); });
      }
      function setRange(r) { const [a, b] = hitung(r); $id('rmFrom').value = a; $id('rmTo').value = b; tandai(); }

      async function muat() {
        const ini = ++seq;
        $id('rmErr').classList.add('d-none');
        document.querySelectorAll('.rm-kpi, #rmItems, #rmDokter, #rmTable').forEach(el => el.closest('.card, .rm-kpi').classList.add('rm-loading'));
        $id('rmRefresh').querySelector('i').classList.add('fa-spin');
        try {
          const p = new URLSearchParams({ jenis, from: $id('rmFrom').value, to: $id('rmTo').value });
          if (IDC) p.set('id_customer', IDC);
          const r = await fetch(API + '?' + p, { credentials: 'same-origin', cache: 'no-store' });
          const j = await r.json().catch(() => null);
          if (ini !== seq) return;
          if (!r.ok || !j || j.status !== 'success') throw new Error((j && j.message) || 'Gagal memuat data (HTTP ' + r.status + ').');
          D = j;
          rows = j.data.map(a => Object.fromEntries(j.kolom.map((c, i) => [c, a[i]])));
          fItem = -1;
          tampil();
        } catch (e) {
          $id('rmErr').textContent = e.message;
          $id('rmErr').classList.remove('d-none');
        } finally {
          if (ini === seq) {
            document.querySelectorAll('.rm-loading').forEach(el => el.classList.remove('rm-loading'));
            $id('rmRefresh').querySelector('i').classList.remove('fa-spin');
          }
        }
      }

      function tampil() {
        const r = D.ringkasan, cl = D.checklist;
        $id('rmPeriodeInfo').innerHTML = `${IDC && D.faskes && D.faskes.nama ? '<b class="text-dark">' + esc(D.faskes.nama) + '</b> · <a href="module/administrator/monitoring-visit">« semua faskes</a> · ' : ''}<i class="far fa-calendar me-1"></i>${tgl(D.periode.from)}${D.periode.from !== D.periode.to ? ' – ' + tgl(D.periode.to) : ''} · ${jenis === 'poli' ? 'Kunjungan poli' : 'Pasien rawat inap'} (tanpa batal)`;
        $id('kTotal').textContent = fmt(r.total);
        $id('kTotalLbl').textContent = jenis === 'poli' ? 'Kunjungan poli' : 'Pasien rawat inap';
        $id('kTotalSub').textContent = fmt(r.dokter.length) + ' dokter';
        $id('kLengkap').textContent = pc(r.pct_lengkap);
        $id('kLengkapSub').textContent = fmt(r.lengkap) + ' dari ' + fmt(r.total) + ' kunjungan 100% terisi';
        $id('kRata').textContent = pc(r.rata);
        const wajib = r.item.map((x, i) => ({ ...x, cl: cl[i] })).filter(x => !x.cl.integrasi && x.pct !== null);
        const lemah = wajib.sort((a, b) => a.pct - b.pct)[0];
        $id('kLemah').textContent = lemah ? lemah.cl.label : '-';
        $id('kLemahSub').textContent = lemah ? pc(lemah.pct) + ' terisi · ' + fmt(lemah.berlaku - lemah.lengkap) + ' kunjungan kosong' : '';

        // kelengkapan per item
        $id('rmItems').innerHTML = cl.map((c, i) => {
          const it = r.item[i];
          return `<div class="rm-item ${fItem === i ? 'on' : ''}" data-i="${i}">
            <div class="d-flex justify-content-between small"><span class="fw-semibold">${esc(c.label)} ${c.integrasi ? '<span class="badge bg-info-subtle text-info ms-1" style="font-size:.6rem">integrasi</span>' : ''}</span>
              <span>${it.pct == null ? '<span class="text-muted">tidak berlaku</span>' : `<b style="color:${warna(it.pct)}">${pc(it.pct)}</b> <span class="text-muted">· ${fmt(it.berlaku - it.lengkap)} kosong</span>`}</span></div>
            <div class="rm-bar my-1"><div style="width:${it.pct || 0}%;background:${c.integrasi ? '#539bff' : warna(it.pct)}"></div></div>
            <div class="text-muted" style="font-size:.7rem">${esc(c.ket)}</div></div>`;
        }).join('');
        document.querySelectorAll('.rm-item').forEach(el => el.addEventListener('click', () => {
          const i = +el.dataset.i;
          fItem = fItem === i ? -1 : i;
          document.querySelectorAll('.rm-item').forEach(x => x.classList.toggle('on', +x.dataset.i === fItem));
          isiTabel();
          $id('rmTable').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }));

        // per dokter
        $id('rmDokter').querySelector('tbody').innerHTML = r.dokter.map(d => `<tr class="rm-dok" data-d="${esc(d.nama)}" style="cursor:pointer">
          <td class="small fw-semibold">${esc(d.nama)}</td><td class="small text-end">${fmt(d.kunjungan)}</td><td class="small text-end">${fmt(d.lengkap)}</td>
          <td><div class="d-flex align-items-center gap-2"><div class="rm-bar flex-grow-1"><div style="width:${d.rata}%;background:${warna(d.rata)}"></div></div><span class="small fw-semibold" style="width:46px">${pc(d.rata)}</span></div></td></tr>`).join('') ||
          '<tr><td colspan="4" class="text-center text-muted small py-4">Tidak ada data</td></tr>';
        document.querySelectorAll('.rm-dok').forEach(tr => tr.addEventListener('click', () => { $id('rmFDokter').value = tr.dataset.d; isiTabel(); $id('rmTable').scrollIntoView({ behavior: 'smooth', block: 'start' }); }));
        const lama = $id('rmFDokter').value;
        $id('rmFDokter').innerHTML = '<option value="">Semua dokter</option>' + r.dokter.map(d => `<option>${esc(d.nama)}</option>`).join('');
        $id('rmFDokter').value = r.dokter.some(d => d.nama === lama) ? lama : '';

        buatTabel();
      }

      // tabel dibuat ulang saat jenis berganti (jumlah kolom checklist berbeda poli vs ranap)
      function buatTabel() {
        const cl = D.checklist;
        if (tabel) { tabel.destroy(); $id('rmTable').querySelector('tbody').innerHTML = ''; }
        $id('rmTable').querySelector('thead').innerHTML = '<tr><th>Tanggal</th><th>Pasien</th><th>Dokter</th>' +
          cl.map(c => `<th class="rm-c" title="${esc(c.ket)}">${esc(c.label)}</th>`).join('') + '<th class="text-end">Kelengkapan</th></tr>';
        const sel = (v, int) => v === null ? '<span class="rm-dot x">–</span>' : v ? `<span class="rm-dot ${int ? 'int ' : ''}y"><i class="fas fa-check"></i></span>` : '<span class="rm-dot n"><i class="fas fa-times"></i></span>';
        tabel = jQuery('#rmTable').DataTable({
          data: [], deferRender: true, pageLength: 25, order: [[0, 'desc']], autoWidth: false,
          language: { search: 'Cari:', lengthMenu: 'Tampil _MENU_', info: '_START_–_END_ dari _TOTAL_ kunjungan', infoEmpty: '0 kunjungan', infoFiltered: '(dari _MAX_)', zeroRecords: 'Tidak ada kunjungan yang cocok', emptyTable: 'Tidak ada kunjungan pada periode ini', paginate: { previous: '‹', next: '›' } },
          columns: [
            { data: 'tanggal', render: (v, t, r) => t === 'display' ? `<span class="fw-semibold">${tgl(v)}</span> <span class="small text-muted">${esc(r.jam)}</span>` : v + ' ' + r.jam },
            { data: 'pasien', render: (v, t, r) => t === 'display' ? `<div class="fw-semibold">${esc(v)}</div><div class="small text-muted">${esc(r.rm || '')}${r.info ? ' · ' + esc(r.info) : ''}</div>` : v + ' ' + r.rm },
            { data: 'dokter', render: (v, t, r) => t === 'display' ? `<span class="small">${esc(v)}</span><div class="small text-muted">${esc(r.poli)}</div>` : v },
            ...cl.map((c, i) => ({ data: null, className: 'rm-c', orderable: false, searchable: false, render: r => sel(r.cek[i], c.integrasi) })),
            { data: 'pct', className: 'text-end', render: (v, t) => t !== 'display' ? v : `<span class="rm-pct" style="color:${warna(v)}">${v}%</span>` }
          ]
        });
        isiTabel();
      }
      function isiTabel() {
        const dok = $id('rmFDokter').value;
        const isi = rows.filter(r => (!dok || r.dokter === dok) && (fItem < 0 || r.cek[fItem] === 0) &&
          (!fStatus || (fStatus === 'lengkap' ? r.pct === 100 : r.pct < 100)));
        tabel.clear().rows.add(isi).draw();
        const ket = [];
        if (fItem >= 0) ket.push('belum: ' + D.checklist[fItem].label);
        if (dok) ket.push(dok);
        $id('rmInfo').textContent = fmt(isi.length) + ' kunjungan' + (ket.length ? ' · ' + ket.join(' · ') : '');
      }
      document.querySelectorAll('#rmStatus .btn').forEach(b => b.addEventListener('click', () => {
        fStatus = b.dataset.s;
        document.querySelectorAll('#rmStatus .btn').forEach(x => x.classList.toggle('active', x === b));
        isiTabel();
      }));
      $id('rmFDokter').addEventListener('change', isiTabel);

      document.querySelectorAll('.rm-tabs .nav-link').forEach(b => b.addEventListener('click', () => {
        if (b.dataset.jenis === jenis) return;
        jenis = b.dataset.jenis;
        document.querySelectorAll('.rm-tabs .nav-link').forEach(x => x.classList.toggle('active', x === b));
        $id('rmFDokter').value = '';
        setRange(rentangAwal[jenis]);
        muat();
      }));
      document.querySelectorAll('#rmRanges .btn').forEach(b => b.addEventListener('click', () => { setRange(b.dataset.r); muat(); }));
      let tunda;
      ['rmFrom', 'rmTo'].forEach(id => $id(id).addEventListener('change', () => { tandai(); clearTimeout(tunda); tunda = setTimeout(muat, 300); }));
      $id('rmRefresh').addEventListener('click', muat);

      if (/^\d{4}-\d{2}-\d{2}$/.test(QS.get('from') || '') && /^\d{4}-\d{2}-\d{2}$/.test(QS.get('to') || '')) { $id('rmFrom').value = QS.get('from'); $id('rmTo').value = QS.get('to'); tandai(); } else setRange(rentangAwal[jenis]);
      muat();
    })();
  </script>
</body>

</html>
