<?php
$title = 'Laporan Tindakan & Prosedur';
require '../../controller/view.php';
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .min-w-0 { min-width: 0; }
    .pr-icon { width: 40px; height: 40px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
    .pr-kpi { border: 1px solid #e5eaef; border-radius: 12px; background: #fff; padding: 14px 16px; height: 100%; }
    .pr-val { font-size: 1.25rem; font-weight: 700; color: #2a3547; line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .pr-label { font-size: .75rem; color: #6c757d; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .pr-sub { font-size: .7rem; color: #98a2b3; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .pr-sec { font-size: .72rem; font-weight: 700; color: #5a6a85; text-transform: uppercase; letter-spacing: .4px; }
    .pr-chips .btn { border-radius: 20px; font-size: .75rem; padding: .2rem .7rem; background: #fff; border: 1px solid #e5eaef; color: #5a6a85; }
    .pr-chips .btn.active { background: #5d87ff; border-color: #5d87ff; color: #fff; }
    .pr-filter .form-label { font-size: .72rem; color: #6c757d; margin-bottom: 2px; }
    .pr-loading { position: relative; }
    .pr-loading::after { content: ''; position: absolute; inset: 0; background: rgba(255, 255, 255, .6); border-radius: inherit; z-index: 3; }
    .pr-bar { position: fixed; top: 0; left: 0; height: 3px; background: #5d87ff; z-index: 9999; transition: width .3s, opacity .3s; }
    .pr-tabs .nav-link { font-size: .85rem; color: #5a6a85; border: 0; border-bottom: 2px solid transparent; border-radius: 0; padding: .5rem .9rem; }
    .pr-tabs .nav-link.active { color: #5d87ff; border-bottom-color: #5d87ff; background: none; font-weight: 600; }
    .pr-table td { font-size: .82rem; vertical-align: middle; white-space: nowrap; }
    .pr-table th { font-size: .75rem; color: #5a6a85; font-weight: 600; white-space: nowrap; }
    .pr-table tr.pr-top td { background: #fffbeb; }
    .pr-rank { width: 28px; height: 28px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: .78rem; background: #f1f5f9; color: #5a6a85; }
    .pr-rank.r1 { background: #fde68a; color: #92400e; } .pr-rank.r2 { background: #e2e8f0; color: #334155; } .pr-rank.r3 { background: #fed7aa; color: #9a3412; } .pr-rank.t10 { background: #dbeafe; color: #1d4ed8; }
    .pr-warn { background: #fff8ec; border: 1px solid #ffe2b3; border-radius: 10px; padding: 8px 12px; font-size: .8rem; color: #8a5a00; }
    .skel { display: inline-block; height: 1.1rem; width: 70px; border-radius: 6px; background: linear-gradient(90deg, #eef1f4 25%, #f6f8fb 50%, #eef1f4 75%); background-size: 200% 100%; animation: sk 1.2s infinite; }
    @keyframes sk { to { background-position: -200% 0; } }
  </style>
</head>

<body>
  <div class="pr-bar" id="prBar" style="width:0"></div>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <?php require 'sidebar.php'; ?>
    <div class="body-wrapper">
      <?php require 'navbar.php'; ?>
      <div class="body-wrapper-inner">
        <div class="container-fluid">

          <!-- HEADER + FILTER -->
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
              <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                <span class="pr-icon bg-warning-subtle text-warning"><i class="fas fa-syringe"></i></span>
                <div class="min-w-0 flex-grow-1">
                  <h5 class="fw-semibold mb-0">Laporan Tindakan &amp; Prosedur</h5>
                  <div class="text-muted small" id="prPeriode">Tindakan, pendapatan, dan fee per dokter</div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                  <button type="button" class="btn btn-success btn-sm" id="btnExcel" disabled><i class="fas fa-file-excel me-1"></i>Export Excel</button>
                  <div class="btn-group">
                    <button type="button" class="btn btn-primary btn-sm" id="btnPrint" disabled><i class="fas fa-print me-1"></i>Cetak</button>
                    <button type="button" class="btn btn-primary btn-sm dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" id="btnPrintMenu" disabled><span class="visually-hidden">Pilihan cetak</span></button>
                    <ul class="dropdown-menu dropdown-menu-end">
                      <li><a class="dropdown-item" href="#" data-cetak="rekap"><i class="fas fa-list me-2 text-muted"></i>Rekap per tindakan &amp; dokter</a></li>
                      <li><a class="dropdown-item" href="#" data-cetak="ringkasan"><i class="fas fa-chart-pie me-2 text-muted"></i>Ringkasan saja</a></li>
                      <li><a class="dropdown-item" href="#" data-cetak="detail"><i class="fas fa-receipt me-2 text-muted"></i>Rekap + detail transaksi</a></li>
                    </ul>
                  </div>
                </div>
              </div>

              <div class="pr-chips d-flex flex-wrap gap-1 mb-3">
                <button type="button" class="btn" data-preset="hari">Hari ini</button>
                <button type="button" class="btn" data-preset="7">7 hari</button>
                <button type="button" class="btn" data-preset="bulan">Bulan ini</button>
                <button type="button" class="btn" data-preset="bulanlalu">Bulan lalu</button>
                <button type="button" class="btn" data-preset="3bulan">3 bulan</button>
                <button type="button" class="btn" data-preset="tahun">Tahun ini</button>
              </div>

              <div class="row g-2 pr-filter align-items-end">
                <div class="col-6 col-md-3 col-xl-2"><label class="form-label" for="fromDate">Dari</label><input type="date" id="fromDate" class="form-control form-control-sm"></div>
                <div class="col-6 col-md-3 col-xl-2"><label class="form-label" for="toDate">Sampai</label><input type="date" id="toDate" class="form-control form-control-sm"></div>
                <div class="col-6 col-md-3 col-xl-2"><label class="form-label" for="doctorSelect">Dokter</label><select id="doctorSelect" class="form-select form-select-sm"><option value="">Semua dokter</option></select></div>
                <div class="col-6 col-md-3 col-xl-2"><label class="form-label" for="poliSelect">Poli</label><select id="poliSelect" class="form-select form-select-sm"><option value="">Semua poli</option></select></div>
                <div class="col-6 col-md-3 col-xl-2"><label class="form-label" for="providerSelect">Jenis bayar</label><select id="providerSelect" class="form-select form-select-sm"><option value="">Semua jenis bayar</option></select></div>
                <div class="col-6 col-md-3 col-xl"><label class="form-label" for="kategoriSelect">Kategori</label>
                  <select id="kategoriSelect" class="form-select form-select-sm"><option value="">Semua</option><option>Tindakan</option><option>Konsultasi</option><option>Obat/BMHP/Alkes</option><option>Lainnya</option></select></div>
                <div class="col-12 col-md-6 col-xl-auto d-grid"><button type="button" class="btn btn-light btn-sm" id="btnReset"><i class="fas fa-undo me-1"></i>Reset</button></div>
              </div>
              <div class="small text-danger mt-2" id="prErr" hidden></div>
            </div>
          </div>

          <!-- KPI -->
          <div class="row g-3 mb-3">
            <div class="col-6 col-md-4 col-xl-2"><div class="pr-kpi"><div class="d-flex align-items-center gap-2 mb-1"><span class="pr-icon bg-success-subtle text-success"><i class="fas fa-wallet"></i></span>
              <div class="min-w-0"><div class="pr-val" id="kTotal"><span class="skel"></span></div><div class="pr-label">Pendapatan</div></div></div><div class="pr-sub" id="kTotalSub">&nbsp;</div></div></div>
            <div class="col-6 col-md-4 col-xl-2"><div class="pr-kpi"><div class="d-flex align-items-center gap-2 mb-1"><span class="pr-icon bg-primary-subtle text-primary"><i class="fas fa-hand-holding-medical"></i></span>
              <div class="min-w-0"><div class="pr-val" id="kQty"><span class="skel"></span></div><div class="pr-label">Tindakan</div></div></div><div class="pr-sub" id="kQtySub">&nbsp;</div></div></div>
            <div class="col-6 col-md-4 col-xl-2"><div class="pr-kpi"><div class="d-flex align-items-center gap-2 mb-1"><span class="pr-icon bg-info-subtle text-info"><i class="fas fa-users"></i></span>
              <div class="min-w-0"><div class="pr-val" id="kKunj"><span class="skel"></span></div><div class="pr-label">Kunjungan</div></div></div><div class="pr-sub" id="kKunjSub">&nbsp;</div></div></div>
            <div class="col-6 col-md-4 col-xl-2"><div class="pr-kpi"><div class="d-flex align-items-center gap-2 mb-1"><span class="pr-icon" style="background:#f3e8ff;color:#a855f7"><i class="fas fa-receipt"></i></span>
              <div class="min-w-0"><div class="pr-val" id="kRata"><span class="skel"></span></div><div class="pr-label">Per kunjungan</div></div></div><div class="pr-sub">Rata-rata kunjungan bertindakan</div></div></div>
            <div class="col-6 col-md-4 col-xl-2"><div class="pr-kpi"><div class="d-flex align-items-center gap-2 mb-1"><span class="pr-icon bg-warning-subtle text-warning"><i class="fas fa-user-md"></i></span>
              <div class="min-w-0"><div class="pr-val" id="kFeeDok"><span class="skel"></span></div><div class="pr-label">Fee dokter</div></div></div><div class="pr-sub" id="kFeeDokSub">&nbsp;</div></div></div>
            <div class="col-6 col-md-4 col-xl-2"><div class="pr-kpi"><div class="d-flex align-items-center gap-2 mb-1"><span class="pr-icon bg-danger-subtle text-danger"><i class="fas fa-user-nurse"></i></span>
              <div class="min-w-0"><div class="pr-val" id="kFeeLain"><span class="skel"></span></div><div class="pr-label">Fee perawat+admin</div></div></div><div class="pr-sub" id="kFeeLainSub">&nbsp;</div></div></div>
          </div>
          <div class="pr-warn mb-3" id="prWarn" hidden></div>

          <!-- GRAFIK -->
          <div class="row g-3 mb-3">
            <div class="col-xl-6"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-1"><div class="pr-sec">10 tindakan terbanyak</div><div class="small text-muted">berdasarkan jumlah</div></div>
              <div id="chTop" style="min-height:330px"></div></div></div></div>
            <div class="col-xl-6"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-1"><div class="pr-sec" id="trenJudul">Pendapatan tindakan</div><div class="small text-muted" id="trenInfo"></div></div>
              <div id="chTren" style="min-height:330px"></div></div></div></div>
            <div class="col-md-6 col-xl-5"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body">
              <div class="pr-sec mb-1">Pendapatan &amp; fee per dokter</div><div id="chDokter" style="min-height:250px"></div></div></div></div>
            <div class="col-md-6 col-xl-4"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body">
              <div class="pr-sec mb-1">Kategori</div><div id="chKategori" style="min-height:250px"></div></div></div></div>
            <div class="col-md-6 col-xl-3"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body">
              <div class="pr-sec mb-1">Jenis bayar</div><div id="chBayar" style="min-height:250px"></div></div></div></div>
          </div>

          <!-- TABEL -->
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
              <ul class="nav pr-tabs border-bottom mb-3" role="tablist">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabItem" type="button">Per tindakan <span class="badge bg-light text-muted ms-1" id="nItem">0</span></button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabDokter" type="button">Per dokter <span class="badge bg-light text-muted ms-1" id="nDokter">0</span></button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabDetail" type="button">Detail transaksi <span class="badge bg-light text-muted ms-1" id="nDetail">0</span></button></li>
              </ul>
              <div class="tab-content">
                <div class="tab-pane fade show active" id="tabItem">
                  <div class="small text-muted mb-2">Semua tindakan, diurutkan dari yang terbanyak · 10 teratas ditandai</div>
                  <div class="table-responsive"><table class="table table-hover align-middle mb-0 w-100 pr-table" id="tItem">
                    <thead><tr><th class="text-center">#</th><th>Tindakan</th><th>Kategori</th><th class="text-end">Jumlah</th><th class="text-end">Kunjungan</th><th class="text-end">Rata-rata harga</th><th class="text-end">Pendapatan</th><th class="text-end">Fee dokter</th><th class="text-end">Fee perawat</th><th class="text-end">Fee admin</th><th>Dokter terbanyak</th></tr></thead>
                    <tbody></tbody></table></div>
                </div>
                <div class="tab-pane fade" id="tabDokter">
                  <div class="small text-muted mb-2">Rekap untuk perhitungan honor/fee dokter</div>
                  <div class="table-responsive"><table class="table table-hover align-middle mb-0 w-100 pr-table" id="tDokter">
                    <thead><tr><th>Dokter</th><th class="text-end">Kunjungan</th><th class="text-end">Jumlah tindakan</th><th class="text-end">Jenis</th><th class="text-end">Pendapatan</th><th class="text-end">Fee dokter</th><th class="text-end">Fee perawat</th><th class="text-end">Fee admin</th></tr></thead>
                    <tbody></tbody><tfoot><tr class="fw-semibold"><td>Total</td><td class="text-end" id="fK"></td><td class="text-end" id="fQ"></td><td class="text-end" id="fJ"></td><td class="text-end" id="fT"></td><td class="text-end" id="fD"></td><td class="text-end" id="fP"></td><td class="text-end" id="fA"></td></tr></tfoot></table></div>
                </div>
                <div class="tab-pane fade" id="tabDetail">
                  <div class="table-responsive"><table class="table table-hover align-middle mb-0 w-100 pr-table" id="tDetail">
                    <thead><tr><th>Tanggal</th><th>Pasien</th><th>Dokter</th><th>Tindakan</th><th>Kategori</th><th class="text-end">Qty</th><th class="text-end">Harga</th><th class="text-end">Diskon</th><th class="text-end">Total</th><th class="text-end">Fee dokter</th><th>Jenis bayar</th></tr></thead>
                    <tbody></tbody></table></div>
                </div>
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
      const API = 'controller/report/prosedurController.php';
      const PALET = ['#5d87ff', '#13deb9', '#ffae1f', '#fa896b', '#a855f7', '#539bff', '#14b8a6', '#f472b6', '#94a3b8', '#64748b'];
      const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      const $id = id => document.getElementById(id);
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const fmt = n => Number(n || 0).toLocaleString('id-ID');
      const rp = n => 'Rp ' + fmt(Math.round(n || 0));
      const rpS = n => { n = Math.round(n || 0); return n >= 1e9 ? 'Rp ' + (n / 1e9).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' M' : n >= 1e6 ? 'Rp ' + (n / 1e6).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' jt' : n >= 1e4 ? 'Rp ' + (n / 1e3).toLocaleString('id-ID', { maximumFractionDigits: 0 }) + ' rb' : rp(n); };
      const ymd = d => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
      const tglIndo = s => { const t = String(s || '').split('-'); return t.length === 3 ? (+t[2]) + ' ' + BULAN[+t[1] - 1] + ' ' + t[0] : (t.length === 2 ? BULAN[+t[1] - 1] + ' ' + t[0] : s); };
      let D = null, ctrl = null, seq = 0;
      const charts = {};

      function filter() {
        return { fromDate: $id('fromDate').value, toDate: $id('toDate').value, doctor: $id('doctorSelect').value, poli: $id('poliSelect').value, provider: $id('providerSelect').value, kategori: $id('kategoriSelect').value };
      }
      function qs(f, extra) {
        const q = new URLSearchParams();
        Object.entries(Object.assign({}, f, extra || {})).forEach(([k, v]) => { if (v !== '' && v != null) q.set(k, v); });
        return q.toString();
      }
      function preset(p) {
        const n = new Date();
        let a = new Date(n), b = new Date(n);
        if (p === '7') a.setDate(a.getDate() - 6);
        else if (p === 'bulan') a = new Date(n.getFullYear(), n.getMonth(), 1);
        else if (p === 'bulanlalu') { a = new Date(n.getFullYear(), n.getMonth() - 1, 1); b = new Date(n.getFullYear(), n.getMonth(), 0); }
        else if (p === '3bulan') a = new Date(n.getFullYear(), n.getMonth() - 2, 1);
        else if (p === 'tahun') a = new Date(n.getFullYear(), 0, 1);
        return [ymd(a), ymd(b)];
      }
      function tandaiPreset() {
        const f = $id('fromDate').value, t = $id('toDate').value;
        document.querySelectorAll('[data-preset]').forEach(b => { const [a, z] = preset(b.dataset.preset); b.classList.toggle('active', a === f && z === t); });
      }

      let tunda;
      const muatNanti = () => { clearTimeout(tunda); tunda = setTimeout(muat, 250); };
      async function muat() {
        const f = filter(), err = $id('prErr');
        if (!f.fromDate || !f.toDate) return;
        if (f.toDate < f.fromDate) { err.textContent = 'Tanggal "Sampai" lebih awal dari "Dari".'; err.hidden = false; return; }
        if ((new Date(f.toDate) - new Date(f.fromDate)) / 864e5 + 1 > 366) { err.textContent = 'Rentang maksimal 366 hari.'; err.hidden = false; return; }
        err.hidden = true;
        tandaiPreset();
        try { history.replaceState(null, '', location.pathname + '?' + qs(f)); } catch (e) {}
        if (ctrl) ctrl.abort();
        ctrl = new AbortController();
        const ini = ++seq;
        sibuk(true);
        try {
          const r = await fetch(API + '?' + qs(f), { signal: ctrl.signal, credentials: 'same-origin', cache: 'no-store' });
          const j = await r.json().catch(() => null);
          if (ini !== seq) return;
          if (!r.ok || !j || j.status !== 'success') throw new Error((j && j.message) || 'Gagal memuat laporan (HTTP ' + r.status + ').');
          D = j;
          isiOpsi(j.opsi, f);
          tampil();
        } catch (e) {
          if (e.name === 'AbortError' || ini !== seq) return;
          D = null;
          [tItem, tDokter, tDetail].forEach(t => t.clear().draw());
          $id('tItem').querySelector('tbody').innerHTML = `<tr><td colspan="11" class="text-center text-danger py-5"><i class="fas fa-exclamation-circle fa-2x d-block mb-2"></i>${esc(e.message)}<div class="mt-2"><button class="btn btn-sm btn-primary" id="prRetry">Coba lagi</button></div></td></tr>`;
          $id('prRetry').onclick = muat;
          ['kTotal', 'kQty', 'kKunj', 'kRata', 'kFeeDok', 'kFeeLain'].forEach(id => $id(id).textContent = '-');
          ['btnExcel', 'btnPrint', 'btnPrintMenu'].forEach(id => $id(id).disabled = true);
        } finally {
          if (ini === seq) sibuk(false);
        }
      }
      function sibuk(on) {
        const bar = $id('prBar');
        if (on) { bar.style.opacity = 1; bar.style.width = '70%'; } else { bar.style.width = '100%'; setTimeout(() => { bar.style.opacity = 0; bar.style.width = 0; }, 300); }
        document.querySelectorAll('.pr-kpi, [id^=ch]').forEach(el => el.classList.toggle('pr-loading', on));
      }
      function isiOpsi(o, f) {
        if (!o) return;
        const set = (id, list, semua, val) => {
          const sel = $id(id), ada = list.some(x => String(x.v) === String(val));
          sel.innerHTML = `<option value="">${semua}</option>` + list.map(x => `<option value="${esc(x.v)}">${esc(x.t)}</option>`).join('') + (val && !ada ? `<option value="${esc(val)}">${esc(val)}</option>` : '');
          sel.value = val || '';
        };
        set('doctorSelect', o.dokter.map(d => ({ v: d, t: d })), 'Semua dokter', f.doctor);
        set('poliSelect', o.poli.map(d => ({ v: d, t: d })), 'Semua poli', f.poli);
        set('providerSelect', o.provider.map(d => ({ v: d.id_provider, t: d.provider_name })), 'Semua jenis bayar', f.provider);
      }

      function tampil() {
        const r = D.ringkasan, f = D.filter;
        $id('prPeriode').innerHTML = '<i class="far fa-calendar me-1"></i>' + esc(tglIndo(f.from)) + (f.from !== f.to ? ' – ' + esc(tglIndo(f.to)) : '') + ' · ' + f.hari + ' hari · ' + esc(f.label);
        $id('kTotal').textContent = rpS(r.total);
        $id('kTotal').title = rp(r.total);
        $id('kTotalSub').textContent = r.diskon ? 'Diskon ' + rp(r.diskon) : 'Tanpa diskon';
        $id('kQty').textContent = fmt(r.qty);
        $id('kQtySub').textContent = fmt(r.jenis) + ' jenis tindakan';
        $id('kKunj').textContent = fmt(r.kunjungan_tindakan);
        $id('kKunjSub').textContent = String(r.pct_kunjungan).replace('.', ',') + '% dari ' + fmt(r.kunjungan) + ' kunjungan';
        $id('kRata').textContent = rpS(r.rata_per_kunjungan);
        $id('kFeeDok').textContent = rpS(r.fee_dokter);
        $id('kFeeDok').title = rp(r.fee_dokter);
        $id('kFeeDokSub').textContent = r.total ? Math.round(r.fee_dokter / r.total * 100) + '% dari pendapatan' : ' ';
        $id('kFeeLain').textContent = rpS(r.fee_perawat + r.fee_admin);
        $id('kFeeLainSub').textContent = 'Perawat ' + rpS(r.fee_perawat) + ' · admin ' + rpS(r.fee_admin);
        const w = $id('prWarn');
        w.hidden = !r.tanpa_tarif;
        if (r.tanpa_tarif) w.innerHTML = `<i class="fas fa-exclamation-triangle me-1"></i><b>${fmt(r.tanpa_tarif)} jenis tindakan</b> tidak ditemukan di master tarif faskes ini, sehingga fee-nya 0: ${r.tanpa_tarif_item.map(x => '<code>' + esc(x) + '</code>').join(', ')}${r.tanpa_tarif > r.tanpa_tarif_item.length ? ', …' : ''}. Samakan nama tindakan dengan master tarif agar fee terhitung.`;
        grafik(r);
        tabel(r);
        ['btnExcel', 'btnPrint', 'btnPrintMenu'].forEach(id => $id(id).disabled = !D.data.length);
      }

      function chart(id, opt) {
        const el = $id(id);
        if (typeof ApexCharts === 'undefined') { el.innerHTML = '<div class="text-center text-muted small py-5">Grafik tidak tersedia.</div>'; return; }
        if (charts[id]) { charts[id].updateOptions(opt, true, false); return; }
        charts[id] = new ApexCharts(el, Object.assign({}, opt, { chart: Object.assign({ fontFamily: 'inherit', toolbar: { show: false }, animations: { enabled: false }, parentHeightOffset: 0 }, opt.chart) }));
        charts[id].render();
      }
      const kosong = { noData: { text: 'Tidak ada tindakan', style: { color: '#98a2b3' } } };
      const grid = { borderColor: '#eef1f4', strokeDashArray: 3 };
      const donut = (id, data) => chart(id, Object.assign({}, kosong, {
        chart: { type: 'donut', height: 250 }, series: data.map(d => d.jumlah), labels: data.map(d => d.nama), colors: PALET,
        legend: { position: 'bottom', fontSize: '11px' }, dataLabels: { formatter: v => Math.round(v) + '%' },
        plotOptions: { pie: { donut: { size: '62%', labels: { show: true, total: { show: true, label: 'Pendapatan', formatter: w => rpS(w.globals.seriesTotals.reduce((a, b) => a + b, 0)) } } } } },
        tooltip: { y: { formatter: v => rp(v) } }
      }));

      function grafik(r) {
        const top = r.items.slice(0, 10);
        chart('chTop', Object.assign({}, kosong, {
          chart: { type: 'bar', height: 330 },
          series: [{ name: 'Jumlah', data: top.map(d => d.qty) }],
          xaxis: { categories: top.map(d => d.item.length > 32 ? d.item.slice(0, 31) + '…' : d.item), labels: { formatter: v => fmt(v) } },
          yaxis: { labels: { maxWidth: 220, style: { fontSize: '11px' } } },
          plotOptions: { bar: { horizontal: true, barHeight: '62%', borderRadius: 3, distributed: true } }, colors: PALET, legend: { show: false },
          dataLabels: { enabled: true, formatter: v => fmt(v), style: { fontSize: '10px' } }, grid,
          tooltip: { y: { formatter: (v, o) => fmt(v) + '× · ' + rp(top[o.dataPointIndex].total) } }
        }));
        const t = r.tren;
        $id('trenJudul').textContent = 'Pendapatan tindakan (' + (r.periode === 'hari' ? 'harian' : 'bulanan') + ')';
        const puncak = t.reduce((m, x) => x.total > m.n ? { n: x.total, t: x.t } : m, { n: 0, t: null });
        $id('trenInfo').textContent = puncak.t ? 'Tertinggi ' + rpS(puncak.n) + ' · ' + tglIndo(puncak.t) : '';
        chart('chTren', Object.assign({}, kosong, {
          chart: { type: 'line', height: 330 },
          series: r.total ? [{ name: 'Pendapatan', type: 'column', data: t.map(x => x.total) }, { name: 'Jumlah tindakan', type: 'line', data: t.map(x => x.qty) }] : [],
          colors: ['#13deb9', '#5d87ff'], stroke: { width: [0, 2], curve: 'smooth' },
          xaxis: { categories: t.map(x => r.periode === 'hari' ? (+x.t.slice(8)) + ' ' + BULAN[+x.t.slice(5, 7) - 1] : tglIndo(x.t)), labels: { rotate: -45, hideOverlappingLabels: true, style: { fontSize: '10px' } }, tickAmount: Math.min(t.length, 16) },
          yaxis: [{ labels: { formatter: v => rpS(v) } }, { opposite: true, labels: { formatter: v => fmt(Math.round(v)) } }],
          plotOptions: { bar: { columnWidth: t.length > 40 ? '85%' : '55%', borderRadius: 2 } },
          dataLabels: { enabled: false }, legend: { position: 'top', horizontalAlign: 'left', fontSize: '11px' }, grid,
          tooltip: { shared: true, intersect: false, y: { formatter: (v, o) => o.seriesIndex === 0 ? rp(v) : fmt(v) + '×' } }
        }));
        const dk = r.dokter.slice(0, 8);
        chart('chDokter', Object.assign({}, kosong, {
          chart: { type: 'bar', height: Math.max(250, dk.length * 36 + 50), stacked: false },
          series: [{ name: 'Pendapatan', data: dk.map(d => d.total) }, { name: 'Fee dokter', data: dk.map(d => d.fee_dokter) }],
          xaxis: { categories: dk.map(d => d.dokter.length > 26 ? d.dokter.slice(0, 25) + '…' : d.dokter), tickAmount: 3, labels: { formatter: v => rpS(v) } },
          yaxis: { labels: { maxWidth: 180, style: { fontSize: '11px' } } },
          plotOptions: { bar: { horizontal: true, barHeight: '70%', borderRadius: 2 } }, colors: ['#5d87ff', '#ffae1f'],
          dataLabels: { enabled: false }, legend: { position: 'top', horizontalAlign: 'left', fontSize: '11px' }, grid,
          tooltip: { shared: true, intersect: false, y: { formatter: v => rp(v) } }
        }));
        donut('chKategori', r.kategori);
        donut('chBayar', r.bayar);
      }

      /* ---------- tabel ---------- */
      const jq = window.jQuery;
      const bahasa = (s) => ({ search: 'Cari:', lengthMenu: 'Tampil _MENU_', info: '_START_–_END_ dari _TOTAL_ ' + s, infoEmpty: '0 ' + s, infoFiltered: '(disaring dari _MAX_)', zeroRecords: 'Tidak ada yang cocok', emptyTable: 'Tidak ada tindakan pada periode ini', paginate: { previous: '‹', next: '›' } });
      const angka = (cls = 'text-end') => ({ className: cls, render: (d, t) => t === 'display' ? fmt(d) : d });
      const uang = (cls = 'text-end') => ({ className: cls, render: (d, t) => t === 'display' ? (d ? rp(d) : '<span class="text-muted">-</span>') : d });
      const tItem = jq('#tItem').DataTable({
        data: [], deferRender: true, pageLength: 25, order: [[0, 'asc']], autoWidth: false, language: bahasa('tindakan'),
        createdRow: (row, d) => { if (d.rank <= 10) row.classList.add('pr-top'); },
        columns: [
          { data: 'rank', className: 'text-center', render: (d, t) => t !== 'display' ? d : `<span class="pr-rank ${d <= 3 ? 'r' + d : d <= 10 ? 't10' : ''}">${d}</span>` },
          { data: 'item', render: (d, t, r) => t === 'display' ? `<span class="fw-semibold">${esc(d)}</span>` + (r.tarif ? '' : ' <span class="badge bg-warning-subtle text-warning ms-1" title="Tidak ada di master tarif — fee 0">tanpa tarif</span>') : d },
          { data: 'kategori', render: (d, t) => t === 'display' ? `<span class="small text-muted">${esc(d)}</span>` : d },
          Object.assign({ data: 'qty' }, angka('text-end fw-semibold')), Object.assign({ data: 'kunjungan' }, angka()),
          Object.assign({ data: 'rata' }, uang()), Object.assign({ data: 'total' }, uang('text-end fw-semibold')),
          Object.assign({ data: 'fee_dokter' }, uang()), Object.assign({ data: 'fee_perawat' }, uang()), Object.assign({ data: 'fee_admin' }, uang()),
          { data: 'dokter_utama', render: (d, t) => t === 'display' ? `<span class="small">${esc(d)}</span>` : d }
        ]
      });
      const tDokter = jq('#tDokter').DataTable({
        data: [], pageLength: 25, order: [[4, 'desc']], autoWidth: false, language: bahasa('dokter'),
        columns: [
          { data: 'dokter', render: (d, t) => t === 'display' ? `<span class="fw-semibold">${esc(d)}</span>` : d },
          Object.assign({ data: 'kunjungan' }, angka()), Object.assign({ data: 'qty' }, angka()), Object.assign({ data: 'jenis' }, angka()),
          Object.assign({ data: 'total' }, uang('text-end fw-semibold')), Object.assign({ data: 'fee_dokter' }, uang('text-end fw-semibold text-warning')),
          Object.assign({ data: 'fee_perawat' }, uang()), Object.assign({ data: 'fee_admin' }, uang())
        ]
      });
      const tDetail = jq('#tDetail').DataTable({
        data: [], deferRender: true, pageLength: 25, order: [[0, 'desc']], autoWidth: false, language: bahasa('baris'),
        columns: [
          { data: 'urut', render: (d, t, r) => t === 'display' ? `${esc(tglIndo(r.tanggal))} <span class="small text-muted">${esc(r.jam)}</span>` : d },
          { data: 'nama', render: (d, t, r) => t === 'display' ? `<div class="fw-semibold">${esc(d)}</div><div class="small text-muted">${esc(r.rm || '-')}</div>` : d + ' ' + r.rm },
          { data: 'dokter', render: (d, t) => t === 'display' ? esc(d) : d },
          { data: 'item', render: (d, t, r) => t === 'display' ? esc(d) + (r.tarif ? '' : ' <i class="fas fa-exclamation-circle text-warning" title="Tidak ada di master tarif"></i>') : d },
          { data: 'kategori', render: (d, t) => t === 'display' ? `<span class="small text-muted">${esc(d)}</span>` : d },
          Object.assign({ data: 'qty' }, angka()), Object.assign({ data: 'harga' }, uang()), Object.assign({ data: 'diskon' }, uang()),
          Object.assign({ data: 'total' }, uang('text-end fw-semibold')), Object.assign({ data: 'fee_dokter' }, uang()),
          { data: 'bayar', render: (d, t) => t === 'display' ? esc(d) : d }
        ]
      });
      document.querySelectorAll('.pr-tabs [data-bs-toggle=tab]').forEach(b => b.addEventListener('shown.bs.tab', () => [tItem, tDokter, tDetail].forEach(t => t.columns.adjust())));

      function tabel(r) {
        tItem.clear().rows.add(r.items).draw();
        tDokter.clear().rows.add(r.dokter).draw();
        const k = D.kolom;
        const det = D.data.map(a => { const o = {}; k.forEach((c, i) => o[c] = a[i]); o.urut = o.tanggal + ' ' + o.jam; return o; });
        tDetail.clear().rows.add(det).draw();
        $id('nItem').textContent = fmt(r.items.length);
        $id('nDokter').textContent = fmt(r.dokter.length);
        $id('nDetail').textContent = fmt(det.length);
        $id('fK').textContent = fmt(r.kunjungan_tindakan); $id('fQ').textContent = fmt(r.qty); $id('fJ').textContent = fmt(r.jenis);
        $id('fT').textContent = rp(r.total); $id('fD').textContent = rp(r.fee_dokter); $id('fP').textContent = rp(r.fee_perawat); $id('fA').textContent = rp(r.fee_admin);
      }

      /* ---------- export & cetak ---------- */
      $id('btnExcel').addEventListener('click', () => {
        const btn = $id('btnExcel'), label = btn.innerHTML, token = Date.now().toString(36);
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyiapkan…';
        const selesai = () => { clearInterval(cek); btn.innerHTML = label; btn.disabled = !(D && D.data.length); };
        const cek = setInterval(() => { if (document.cookie.includes('pr_unduh=' + token)) selesai(); }, 500);
        setTimeout(selesai, 120000);
        const ifr = document.createElement('iframe');
        ifr.hidden = true;
        ifr.addEventListener('load', () => {
          if (document.cookie.includes('pr_unduh=' + token)) return;
          let pesan = '';
          try { pesan = (ifr.contentDocument.body.innerText || '').trim().slice(0, 300); } catch (e) {}
          selesai();
          Swal.fire('Export gagal', pesan || 'Server tidak mengirim file Excel.', 'error');
        });
        ifr.src = 'module/report/export_prosedur.php?' + qs(filter(), { token });
        document.body.appendChild(ifr);
        setTimeout(() => ifr.remove(), 130000);
      });
      const cetak = isi => window.open(API + '?' + qs(filter(), { format: 'print', isi }), '_blank');
      $id('btnPrint').addEventListener('click', () => cetak('rekap'));
      document.querySelectorAll('[data-cetak]').forEach(a => a.addEventListener('click', e => { e.preventDefault(); cetak(a.dataset.cetak); }));

      document.querySelectorAll('[data-preset]').forEach(b => b.addEventListener('click', () => {
        const [a, z] = preset(b.dataset.preset);
        $id('fromDate').value = a; $id('toDate').value = z;
        muat();
      }));
      ['fromDate', 'toDate'].forEach(id => $id(id).addEventListener('change', muatNanti));
      ['doctorSelect', 'poliSelect', 'providerSelect', 'kategoriSelect'].forEach(id => $id(id).addEventListener('change', muat));
      $id('btnReset').addEventListener('click', () => {
        const [a, z] = preset('bulan');
        $id('fromDate').value = a; $id('toDate').value = z;
        ['doctorSelect', 'poliSelect', 'providerSelect', 'kategoriSelect'].forEach(id => $id(id).value = '');
        muat();
      });

      const u = new URLSearchParams(location.search);
      const [a, z] = preset('bulan');
      $id('fromDate').value = u.get('fromDate') || a;
      $id('toDate').value = u.get('toDate') || z;
      if (u.get('kategori')) $id('kategoriSelect').value = u.get('kategori');
      ['doctor:doctorSelect', 'poli:poliSelect', 'provider:providerSelect'].forEach(p => {
        const [k, id] = p.split(':');
        if (u.get(k)) $id(id).innerHTML = `<option value="${esc(u.get(k))}" selected>${esc(u.get(k))}</option>`;
      });
      muat();
    })();
  </script>
</body>

</html>
