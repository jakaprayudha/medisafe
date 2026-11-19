<?php
$title = 'Laporan Diagnosis ICD-10';
require '../../controller/view.php';
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .min-w-0 { min-width: 0; }
    .dg-icon { width: 40px; height: 40px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
    .dg-kpi { border: 1px solid #e5eaef; border-radius: 12px; background: #fff; padding: 14px 16px; height: 100%; }
    .dg-val { font-size: 1.35rem; font-weight: 700; color: #2a3547; line-height: 1.2; white-space: nowrap; }
    .dg-label { font-size: .75rem; color: #6c757d; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .dg-sub { font-size: .7rem; color: #98a2b3; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .dg-sec { font-size: .72rem; font-weight: 700; color: #5a6a85; text-transform: uppercase; letter-spacing: .4px; }
    .dg-chips .btn { border-radius: 20px; font-size: .75rem; padding: .2rem .7rem; background: #fff; border: 1px solid #e5eaef; color: #5a6a85; }
    .dg-chips .btn.active { background: #5d87ff; border-color: #5d87ff; color: #fff; }
    .dg-filter .form-label { font-size: .72rem; color: #6c757d; margin-bottom: 2px; }
    .dg-loading { position: relative; }
    .dg-loading::after { content: ''; position: absolute; inset: 0; background: rgba(255, 255, 255, .6); border-radius: inherit; z-index: 3; }
    .dg-bar { position: fixed; top: 0; left: 0; height: 3px; background: #5d87ff; z-index: 9999; transition: width .3s, opacity .3s; }
    #dgTable td { font-size: .82rem; vertical-align: middle; }
    #dgTable th { font-size: .75rem; color: #5a6a85; font-weight: 600; white-space: nowrap; }
    #dgTable tr.dg-top td { background: #fffbeb; }
    .dg-rank { width: 30px; height: 30px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: .8rem; background: #f1f5f9; color: #5a6a85; }
    .dg-rank.r1 { background: #fde68a; color: #92400e; }
    .dg-rank.r2 { background: #e2e8f0; color: #334155; }
    .dg-rank.r3 { background: #fed7aa; color: #9a3412; }
    .dg-rank.t10 { background: #dbeafe; color: #1d4ed8; }
    .dg-code { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-weight: 700; color: #2a3547; }
    .dg-pct { height: 6px; border-radius: 3px; background: #eef1f4; overflow: hidden; min-width: 70px; }
    .dg-pct > div { height: 100%; background: #5d87ff; }
    .dg-name { min-width: 280px; white-space: normal; }
    .skel { display: inline-block; height: 1.1rem; width: 60px; border-radius: 6px; background: linear-gradient(90deg, #eef1f4 25%, #f6f8fb 50%, #eef1f4 75%); background-size: 200% 100%; animation: sk 1.2s infinite; }
    @keyframes sk { to { background-position: -200% 0; } }
  </style>
</head>

<body>
  <div class="dg-bar" id="dgBar" style="width:0"></div>
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
                <span class="dg-icon bg-danger-subtle text-danger"><i class="fas fa-notes-medical"></i></span>
                <div class="min-w-0 flex-grow-1">
                  <h5 class="fw-semibold mb-0">Laporan Diagnosis ICD-10</h5>
                  <div class="text-muted small" id="dgPeriode">Semua diagnosis pada periode, diurutkan dari yang terbanyak</div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                  <button type="button" class="btn btn-success btn-sm" id="btnExcel" disabled><i class="fas fa-file-excel me-1"></i>Export Excel</button>
                  <div class="btn-group">
                    <button type="button" class="btn btn-primary btn-sm" id="btnPrint" disabled><i class="fas fa-print me-1"></i>Cetak</button>
                    <button type="button" class="btn btn-primary btn-sm dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" id="btnPrintMenu" disabled><span class="visually-hidden">Pilihan cetak</span></button>
                    <ul class="dropdown-menu dropdown-menu-end">
                      <li><a class="dropdown-item" href="#" data-cetak="semua"><i class="fas fa-list me-2 text-muted"></i>Semua diagnosis</a></li>
                      <li><a class="dropdown-item" href="#" data-cetak="top10"><i class="fas fa-trophy me-2 text-muted"></i>10 teratas saja</a></li>
                    </ul>
                  </div>
                </div>
              </div>

              <div class="d-flex flex-wrap gap-3 align-items-center mb-3">
                <div class="dg-chips d-flex flex-wrap gap-1" id="dgPresets">
                  <button type="button" class="btn" data-preset="7">7 hari</button>
                  <button type="button" class="btn" data-preset="30">30 hari</button>
                  <button type="button" class="btn" data-preset="bulan">Bulan ini</button>
                  <button type="button" class="btn" data-preset="bulanlalu">Bulan lalu</button>
                  <button type="button" class="btn" data-preset="3bulan">3 bulan</button>
                  <button type="button" class="btn" data-preset="tahun">Tahun ini</button>
                </div>
                <div class="btn-group btn-group-sm ms-md-auto" role="group" aria-label="Jenis diagnosis">
                  <input type="radio" class="btn-check" name="jenis" id="jenisUtama" value="utama" checked>
                  <label class="btn btn-outline-primary" for="jenisUtama" title="Hanya diagnosis utama kunjungan">Diagnosis utama</label>
                  <input type="radio" class="btn-check" name="jenis" id="jenisSemua" value="semua">
                  <label class="btn btn-outline-primary" for="jenisSemua" title="Diagnosis utama + diagnosis sekunder">Utama + sekunder</label>
                </div>
              </div>

              <div class="row g-2 dg-filter align-items-end">
                <div class="col-6 col-md-3 col-xl-2"><label class="form-label" for="fromDate">Dari</label><input type="date" id="fromDate" class="form-control form-control-sm"></div>
                <div class="col-6 col-md-3 col-xl-2"><label class="form-label" for="toDate">Sampai</label><input type="date" id="toDate" class="form-control form-control-sm"></div>
                <div class="col-6 col-md-3 col-xl-2"><label class="form-label" for="doctorSelect">Dokter</label><select id="doctorSelect" class="form-select form-select-sm"><option value="">Semua dokter</option></select></div>
                <div class="col-6 col-md-3 col-xl-2"><label class="form-label" for="poliSelect">Poli</label><select id="poliSelect" class="form-select form-select-sm"><option value="">Semua poli</option></select></div>
                <div class="col-6 col-md-3 col-xl-2"><label class="form-label" for="providerSelect">Jenis bayar</label><select id="providerSelect" class="form-select form-select-sm"><option value="">Semua jenis bayar</option></select></div>
                <div class="col-6 col-md-3 col-xl"><label class="form-label" for="genderSelect">Gender</label>
                  <select id="genderSelect" class="form-select form-select-sm"><option value="">Semua</option><option value="pria">Laki-laki</option><option value="wanita">Perempuan</option></select></div>
                <div class="col-12 col-md-6 col-xl-auto d-grid"><button type="button" class="btn btn-light btn-sm" id="btnReset"><i class="fas fa-undo me-1"></i>Reset</button></div>
              </div>
              <div class="small text-danger mt-2" id="dgErr" hidden></div>
            </div>
          </div>

          <!-- KPI -->
          <div class="row g-3 mb-3" id="dgKpi">
            <div class="col-6 col-md-4 col-xl-2"><div class="dg-kpi"><div class="d-flex align-items-center gap-2 mb-1"><span class="dg-icon bg-primary-subtle text-primary"><i class="fas fa-users"></i></span>
              <div class="min-w-0"><div class="dg-val" id="kKunjungan"><span class="skel"></span></div><div class="dg-label">Kunjungan</div></div></div><div class="dg-sub">Tanpa kunjungan batal</div></div></div>
            <div class="col-6 col-md-4 col-xl-2"><div class="dg-kpi"><div class="d-flex align-items-center gap-2 mb-1"><span class="dg-icon bg-success-subtle text-success"><i class="fas fa-stethoscope"></i></span>
              <div class="min-w-0"><div class="dg-val" id="kTerdiagnosis"><span class="skel"></span></div><div class="dg-label">Dengan diagnosis</div></div></div><div class="dg-sub" id="kPasien">&nbsp;</div></div></div>
            <div class="col-6 col-md-4 col-xl-2"><div class="dg-kpi" title="Kunjungan yang belum diisi kode ICD-10 utama"><div class="d-flex align-items-center gap-2 mb-1"><span class="dg-icon bg-warning-subtle text-warning"><i class="fas fa-exclamation-triangle"></i></span>
              <div class="min-w-0"><div class="dg-val" id="kTanpa"><span class="skel"></span></div><div class="dg-label">Tanpa diagnosis</div></div></div><div class="dg-sub" id="kTanpaSub">&nbsp;</div></div></div>
            <div class="col-6 col-md-4 col-xl-2"><div class="dg-kpi"><div class="d-flex align-items-center gap-2 mb-1"><span class="dg-icon bg-info-subtle text-info"><i class="fas fa-tags"></i></span>
              <div class="min-w-0"><div class="dg-val" id="kKode"><span class="skel"></span></div><div class="dg-label">Kode diagnosis</div></div></div><div class="dg-sub" id="kKodeSub">&nbsp;</div></div></div>
            <div class="col-6 col-md-4 col-xl-2"><div class="dg-kpi"><div class="d-flex align-items-center gap-2 mb-1"><span class="dg-icon bg-danger-subtle text-danger"><i class="fas fa-trophy"></i></span>
              <div class="min-w-0"><div class="dg-val" id="kTop10"><span class="skel"></span></div><div class="dg-label">Porsi 10 teratas</div></div></div><div class="dg-sub" id="kTop10Sub">&nbsp;</div></div></div>
            <div class="col-6 col-md-4 col-xl-2"><div class="dg-kpi"><div class="d-flex align-items-center gap-2 mb-1"><span class="dg-icon" style="background:#f3e8ff;color:#a855f7"><i class="fas fa-crown"></i></span>
              <div class="min-w-0"><div class="dg-val" id="kNo1"><span class="skel"></span></div><div class="dg-label" id="kNo1Label">Diagnosis #1</div></div></div><div class="dg-sub" id="kNo1Sub">&nbsp;</div></div></div>
          </div>

          <!-- GRAFIK -->
          <div class="row g-3 mb-3">
            <div class="col-xl-7">
              <div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-1"><div class="dg-sec">10 diagnosis teratas</div><div class="small text-muted">Klik batang untuk melihat pasien</div></div>
                <div id="chTop" style="min-height:360px"></div>
              </div></div>
            </div>
            <div class="col-xl-5">
              <div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body">
                <div class="dg-sec mb-1">Kelompok bab ICD-10</div>
                <div id="chBab" style="min-height:360px"></div>
              </div></div>
            </div>
            <div class="col-12">
              <div class="card border-0 shadow-sm mb-0"><div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-1"><div class="dg-sec" id="trenJudul">Tren 5 diagnosis teratas</div><div class="small text-muted" id="trenInfo"></div></div>
                <div id="chTren" style="min-height:260px"></div>
              </div></div>
            </div>
          </div>

          <!-- TABEL SEMUA DIAGNOSIS -->
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                <div>
                  <h6 class="fw-semibold mb-0">Semua diagnosis</h6>
                  <div class="small text-muted" id="tabelInfo">Diurutkan dari yang terbanyak · 10 teratas ditandai</div>
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                  <div class="dg-chips d-flex gap-1" id="dgTampil">
                    <button type="button" class="btn active" data-tampil="semua">Semua</button>
                    <button type="button" class="btn" data-tampil="top10">10 teratas</button>
                  </div>
                  <select id="babSelect" class="form-select form-select-sm" style="width:auto"><option value="">Semua bab</option></select>
                </div>
              </div>
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 w-100" id="dgTable">
                  <thead>
                    <tr>
                      <th class="text-center">#</th><th>Kode</th><th>Diagnosis</th><th>Bab</th>
                      <th class="text-end" title="Sebagai diagnosis utama">Utama</th><th class="text-end" title="Sebagai diagnosis sekunder">Sekunder</th>
                      <th class="text-end" title="Laki-laki / Perempuan (diagnosis utama)">L / P</th><th class="text-end">Jumlah</th><th>Porsi</th><th class="text-center">Pasien</th>
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

  <div class="modal fade" id="dgModal" tabindex="-1" aria-labelledby="dgModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <div class="min-w-0">
            <h5 class="modal-title mb-0" id="dgModalTitle">Daftar Pasien</h5>
            <div class="small text-muted" id="dgModalSub"></div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div id="dgModalStatus" class="small mb-2" role="status" aria-live="polite"></div>
          <div class="table-responsive">
            <table class="table align-middle w-100" id="dgPasien">
              <thead><tr><th>Tanggal</th><th>RM</th><th>Nama Pasien</th><th>L/P</th><th>Dokter</th><th>Poli</th><th>Sebagai</th></tr></thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php require 'library.php'; ?>
  <script>
    (function() {
      const API = 'controller/report/topDiagnosisController.php';
      const PALET = ['#5d87ff', '#13deb9', '#ffae1f', '#fa896b', '#a855f7', '#539bff', '#14b8a6', '#f472b6', '#94a3b8', '#64748b', '#0ea5e9', '#84cc16'];
      const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      const $id = id => document.getElementById(id);
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const fmt = n => Number(n || 0).toLocaleString('id-ID');
      const pc = n => String(n).replace('.', ',') + '%';
      const ymd = d => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
      const tglIndo = s => { const t = String(s).split('-'); return t.length === 3 ? (+t[2]) + ' ' + BULAN[+t[1] - 1] + ' ' + t[0] : (t.length === 2 ? BULAN[+t[1] - 1] + ' ' + t[0] : s); };

      let D = null, ctrl = null, seq = 0, tampil = 'semua';
      const charts = {};

      /* ---------- filter ---------- */
      function filter() {
        return {
          fromDate: $id('fromDate').value, toDate: $id('toDate').value,
          jenis: document.querySelector('[name=jenis]:checked').value,
          doctor: $id('doctorSelect').value, poli: $id('poliSelect').value,
          provider: $id('providerSelect').value, gender: $id('genderSelect').value
        };
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
        else if (p === '30') a.setDate(a.getDate() - 29);
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

      /* ---------- muat ---------- */
      let tunda;
      const muatNanti = () => { clearTimeout(tunda); tunda = setTimeout(muat, 250); };
      async function muat() {
        const f = filter(), err = $id('dgErr');
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
          const r = await fetch(API + '?' + qs(f, { format: 'json' }), { signal: ctrl.signal, credentials: 'same-origin', cache: 'no-store' });
          const j = await r.json().catch(() => null);
          if (ini !== seq) return;
          if (!r.ok || !j || j.status !== 'success') throw new Error((j && j.message) || 'Gagal memuat laporan (HTTP ' + r.status + ').');
          D = j;
          isiOpsi(j.opsi, f);
          tampilkan();
        } catch (e) {
          if (e.name === 'AbortError' || ini !== seq) return;
          D = null;
          tabel.clear().draw();
          $id('dgTable').querySelector('tbody').innerHTML = `<tr><td colspan="10" class="text-center text-danger py-5"><i class="fas fa-exclamation-circle fa-2x d-block mb-2"></i>${esc(e.message)}<div class="mt-2"><button class="btn btn-sm btn-primary" id="dgRetry">Coba lagi</button></div></td></tr>`;
          $id('dgRetry').onclick = muat;
          ['kKunjungan', 'kTerdiagnosis', 'kTanpa', 'kKode', 'kTop10', 'kNo1'].forEach(id => $id(id).textContent = '-');
          ['btnExcel', 'btnPrint', 'btnPrintMenu'].forEach(id => $id(id).disabled = true);
        } finally {
          if (ini === seq) sibuk(false);
        }
      }
      function sibuk(on) {
        const bar = $id('dgBar');
        if (on) { bar.style.opacity = 1; bar.style.width = '70%'; } else { bar.style.width = '100%'; setTimeout(() => { bar.style.opacity = 0; bar.style.width = 0; }, 300); }
        document.querySelectorAll('.dg-kpi, #chTop, #chBab, #chTren').forEach(el => el.classList.toggle('dg-loading', on));
      }
      function isiOpsi(o, f) {
        if (!o) return;
        const set = (id, list, semua, val) => {
          const sel = $id(id);
          const ada = list.some(x => String(x.v) === String(val));
          sel.innerHTML = `<option value="">${semua}</option>` + list.map(x => `<option value="${esc(x.v)}">${esc(x.t)}</option>`).join('') +
            (val && !ada ? `<option value="${esc(val)}">${esc(val)} (tidak ada di periode ini)</option>` : '');
          sel.value = val || '';
        };
        set('doctorSelect', o.dokter.map(d => ({ v: d, t: d })), 'Semua dokter', f.doctor);
        set('poliSelect', o.poli.map(d => ({ v: d, t: d })), 'Semua poli', f.poli);
        set('providerSelect', o.provider.map(d => ({ v: d.id_provider, t: d.provider_name })), 'Semua jenis bayar', f.provider);
      }

      /* ---------- tampil ---------- */
      function tampilkan() {
        const r = D.ringkasan;
        $id('dgPeriode').innerHTML = '<i class="far fa-calendar me-1"></i>' + esc(tglIndo(D.fromDate)) + (D.fromDate !== D.toDate ? ' – ' + esc(tglIndo(D.toDate)) : '') + ' · ' + D.hari + ' hari · ' + esc(D.label);
        $id('kKunjungan').textContent = fmt(r.kunjungan);
        $id('kTerdiagnosis').textContent = fmt(r.terdiagnosis);
        $id('kPasien').textContent = fmt(r.pasien) + ' pasien unik';
        $id('kTanpa').textContent = fmt(r.tanpa_diagnosis);
        $id('kTanpaSub').textContent = pc(r.pct_tanpa) + ' kunjungan belum dikode';
        $id('kKode').textContent = fmt(r.kode_unik);
        $id('kKodeSub').textContent = fmt(r.total_diagnosis) + ' diagnosis dihitung';
        $id('kTop10').textContent = pc(r.pct_top10);
        $id('kTop10Sub').textContent = fmt(r.top10) + ' dari ' + fmt(r.total_diagnosis);
        const no1 = D.data[0];
        $id('kNo1').textContent = no1 ? no1.icd_code : '-';
        $id('kNo1Label').textContent = no1 ? no1.diagnosis_name : 'Diagnosis #1';
        $id('kNo1Label').title = no1 ? no1.diagnosis_name : '';
        $id('kNo1Sub').textContent = no1 ? fmt(no1.total_visits) + ' kunjungan · ' + pc(no1.pct) : ' ';

        // pilihan bab
        const babSel = $id('babSelect'), babLama = babSel.value;
        babSel.innerHTML = '<option value="">Semua bab</option>' + r.bab.map(b => `<option value="${esc(b.bab)}">Bab ${esc(b.bab)} · ${esc(b.nama)} (${fmt(b.kode)})</option>`).join('');
        babSel.value = r.bab.some(b => b.bab === babLama) ? babLama : '';

        grafik();
        isiTabel();
        ['btnExcel', 'btnPrint', 'btnPrintMenu'].forEach(id => $id(id).disabled = !D.data.length);
      }

      function chart(id, opt) {
        const el = $id(id);
        if (typeof ApexCharts === 'undefined') { el.innerHTML = '<div class="text-center text-muted small py-5">Grafik tidak tersedia.</div>'; return; }
        if (charts[id]) { charts[id].updateOptions(opt, true, false); return; }
        charts[id] = new ApexCharts(el, Object.assign({}, opt, { chart: Object.assign({ fontFamily: 'inherit', toolbar: { show: false }, animations: { enabled: false }, parentHeightOffset: 0 }, opt.chart) }));
        charts[id].render();
      }
      const kosong = { noData: { text: 'Tidak ada data diagnosis', style: { color: '#98a2b3' } } };

      function grafik() {
        const top = D.data.slice(0, 10), r = D.ringkasan;
        chart('chTop', Object.assign({}, kosong, {
          chart: { type: 'bar', height: 360, events: { dataPointSelection: (e, c, o) => { const d = top[o.dataPointIndex]; if (d) pasien(d); } } },
          series: [{ name: 'Kunjungan', data: top.map(d => d.total_visits) }],
          xaxis: { categories: top.map(d => d.icd_code + '  ' + (d.diagnosis_name.length > 34 ? d.diagnosis_name.slice(0, 33) + '…' : d.diagnosis_name)), labels: { formatter: v => fmt(v) } },
          yaxis: { labels: { maxWidth: 300, style: { fontSize: '11px' } } },
          plotOptions: { bar: { horizontal: true, barHeight: '65%', borderRadius: 3, distributed: true } },
          colors: PALET, legend: { show: false },
          dataLabels: { enabled: true, formatter: v => fmt(v), style: { fontSize: '10px' } },
          grid: { borderColor: '#eef1f4', strokeDashArray: 3 },
          tooltip: { y: { formatter: (v, o) => fmt(v) + ' kunjungan (' + pc(top[o.dataPointIndex].pct) + ')', title: { formatter: (s, o) => top[o.dataPointIndex].diagnosis_name } } },
          states: { active: { filter: { type: 'none' } } }
        }));

        const bab = r.bab.slice(0, 8);
        const sisa = r.bab.slice(8).reduce((a, b) => a + b.jumlah, 0);
        if (sisa) bab.push({ bab: '', nama: 'Lainnya', jumlah: sisa });
        chart('chBab', Object.assign({}, kosong, {
          chart: { type: 'donut', height: 360, events: { dataPointSelection: (e, c, o) => { const b = bab[o.dataPointIndex]; if (b && b.bab) { $id('babSelect').value = b.bab; isiTabel(); $id('dgTable').scrollIntoView({ behavior: 'smooth' }); } } } },
          series: bab.map(b => b.jumlah),
          labels: bab.map(b => (b.bab ? b.bab + ' · ' : '') + b.nama),
          colors: PALET,
          legend: { position: 'bottom', fontSize: '11px' },
          dataLabels: { formatter: v => Math.round(v) + '%' },
          plotOptions: { pie: { donut: { size: '60%', labels: { show: true, total: { show: true, label: 'Diagnosis', formatter: w => fmt(w.globals.seriesTotals.reduce((a, b) => a + b, 0)) } } } } },
          tooltip: { y: { formatter: v => fmt(v) } }
        }));

        const t = r.tren;
        $id('trenJudul').textContent = 'Tren 5 diagnosis utama teratas (' + (t.periode === 'hari' ? 'harian' : 'bulanan') + ')';
        $id('trenInfo').textContent = t.seri.length ? t.seri.map(s => s.kode).join(' · ') : '';
        chart('chTren', Object.assign({}, kosong, {
          chart: { type: 'line', height: 260 },
          series: t.seri.map(s => ({ name: s.kode + ' — ' + s.nama.slice(0, 40), data: s.data })),
          xaxis: { categories: t.label.map(l => t.periode === 'hari' ? (+l.slice(8)) + ' ' + BULAN[+l.slice(5, 7) - 1] : tglIndo(l)), labels: { rotate: -45, hideOverlappingLabels: true, style: { fontSize: '10px' } }, tickAmount: Math.min(t.label.length, 16) },
          yaxis: { labels: { formatter: v => fmt(Math.round(v)) } },
          stroke: { width: 2, curve: 'smooth' }, markers: { size: t.label.length > 40 ? 0 : 3 },
          colors: PALET, legend: { position: 'top', horizontalAlign: 'left', fontSize: '11px' },
          grid: { borderColor: '#eef1f4', strokeDashArray: 3 },
          tooltip: { shared: true, y: { formatter: v => fmt(v) } }
        }));
      }

      /* ---------- tabel ---------- */
      const jq = window.jQuery;
      const tabel = jq('#dgTable').DataTable({
        data: [], deferRender: true, pageLength: 25, lengthMenu: [10, 25, 50, 100], order: [[0, 'asc']], autoWidth: false,
        language: {
          search: 'Cari kode/nama:', lengthMenu: 'Tampil _MENU_', info: '_START_–_END_ dari _TOTAL_ diagnosis', infoEmpty: '0 diagnosis',
          infoFiltered: '(disaring dari _MAX_)', zeroRecords: 'Tidak ada diagnosis yang cocok', emptyTable: 'Tidak ada diagnosis pada periode ini', paginate: { previous: '‹', next: '›' }
        },
        createdRow: (row, d) => { if (d.rank <= 10) row.classList.add('dg-top'); },
        columns: [
          { data: 'rank', className: 'text-center', render: (d, t) => t !== 'display' ? d : `<span class="dg-rank ${d <= 3 ? 'r' + d : d <= 10 ? 't10' : ''}">${d}</span>` },
          { data: 'icd_code', render: (d, t) => t === 'display' ? `<span class="dg-code">${esc(d)}</span>` : d },
          { data: 'diagnosis_name', className: 'dg-name', render: (d, t) => t === 'display' ? esc(d) : d },
          { data: 'bab', render: (d, t, r) => t === 'display' ? `<span class="small text-muted" title="${esc(r.bab_nama)}">${esc(d)}</span>` : d },
          { data: 'utama', className: 'text-end', render: (d, t) => t === 'display' ? fmt(d) : d },
          { data: 'sekunder', className: 'text-end', render: (d, t) => t === 'display' ? (d ? fmt(d) : '<span class="text-muted">-</span>') : d },
          { data: 'laki', className: 'text-end text-nowrap', orderable: false, render: (d, t, r) => t === 'display' ? `<span class="text-primary">${fmt(r.laki)}</span> / <span class="text-danger">${fmt(r.perempuan)}</span>` : d },
          { data: 'total_visits', className: 'text-end fw-semibold', render: (d, t) => t === 'display' ? fmt(d) : d },
          { data: 'pct', render: (d, t) => t !== 'display' ? d : `<div class="d-flex align-items-center gap-2"><div class="dg-pct flex-grow-1"><div style="width:${Math.min(100, d / (D.data[0] ? D.data[0].pct : 1) * 100)}%"></div></div><span class="small text-muted" style="width:44px">${pc(d)}</span></div>` },
          { data: null, orderable: false, className: 'text-center', render: (d, t, r) => `<button type="button" class="btn btn-sm btn-outline-primary" data-pasien="${r.rank - 1}" title="Lihat pasien ${esc(r.icd_code)}"><i class="fas fa-users"></i></button>` }
        ]
      });

      function isiTabel() {
        if (!D) return;
        const bab = $id('babSelect').value;
        let rows = D.data;
        if (tampil === 'top10') rows = rows.slice(0, 10);
        if (bab) rows = rows.filter(d => d.bab === bab);
        tabel.clear().rows.add(rows).draw();
        $id('tabelInfo').textContent = fmt(D.data.length) + ' kode diagnosis · diurutkan dari yang terbanyak · 10 teratas ditandai';
      }
      document.querySelectorAll('[data-tampil]').forEach(b => b.addEventListener('click', () => {
        tampil = b.dataset.tampil;
        document.querySelectorAll('[data-tampil]').forEach(x => x.classList.toggle('active', x === b));
        isiTabel();
      }));
      $id('babSelect').addEventListener('change', isiTabel);
      $id('dgTable').addEventListener('click', e => {
        const b = e.target.closest('[data-pasien]');
        if (b && D) pasien(D.data[+b.dataset.pasien]);
      });

      /* ---------- drilldown pasien ---------- */
      const modalEl = $id('dgModal');
      const tPasien = jq('#dgPasien').DataTable({
        data: [], deferRender: true, pageLength: 10, order: [[0, 'desc']],
        language: { search: 'Cari pasien:', lengthMenu: 'Tampil _MENU_', info: '_START_–_END_ dari _TOTAL_ kunjungan', infoEmpty: '0 kunjungan', emptyTable: 'Tidak ada data', zeroRecords: 'Tidak ada yang cocok', paginate: { previous: '‹', next: '›' } },
        columns: [
          { data: 'visit_date', render: (d, t) => t === 'display' ? esc(tglIndo(d)) : d },
          { data: 'nomor_rm', render: jq.fn.dataTable.render.text() },
          { data: 'patient_name', render: jq.fn.dataTable.render.text() },
          { data: 'patient_gender', className: 'text-center' },
          { data: 'doctor_name', render: jq.fn.dataTable.render.text() },
          { data: 'poli', render: jq.fn.dataTable.render.text() },
          { data: 'peran', render: (d, t) => t === 'display' ? `<span class="badge ${d === 'Utama' ? 'bg-primary-subtle text-primary' : 'bg-secondary-subtle text-secondary'}">${esc(d)}</span>` : d }
        ]
      });
      modalEl.addEventListener('shown.bs.modal', () => tPasien.columns.adjust());
      let pCtrl = null;
      async function pasien(d) {
        if (pCtrl) pCtrl.abort();
        pCtrl = new AbortController();
        $id('dgModalTitle').textContent = d.icd_code + ' — ' + d.diagnosis_name;
        $id('dgModalSub').textContent = tglIndo(D.fromDate) + ' – ' + tglIndo(D.toDate) + ' · ' + D.label;
        const st = $id('dgModalStatus');
        st.className = 'small mb-2 text-muted';
        st.textContent = 'Memuat daftar pasien…';
        tPasien.clear().search('').draw();
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
        try {
          const r = await fetch(API + '?' + qs(filter(), { action: 'patients', icd_code: d.icd_code, format: 'json' }), { signal: pCtrl.signal, credentials: 'same-origin' });
          const j = await r.json().catch(() => null);
          if (!r.ok || !j || j.status !== 'success') throw new Error((j && j.message) || 'Gagal memuat daftar pasien.');
          tPasien.clear().rows.add(j.data).draw();
          const sek = j.data.filter(x => x.peran === 'Sekunder').length;
          st.textContent = fmt(j.total) + ' kunjungan' + (sek ? ' (' + fmt(sek) + ' sebagai diagnosis sekunder)' : '');
        } catch (e) {
          if (e.name === 'AbortError') return;
          st.className = 'small mb-2 text-danger';
          st.textContent = e.message;
        }
      }
      modalEl.addEventListener('hidden.bs.modal', () => { if (pCtrl) pCtrl.abort(); });

      /* ---------- export & cetak ---------- */
      $id('btnExcel').addEventListener('click', () => {
        const btn = $id('btnExcel'), label = btn.innerHTML, token = Date.now().toString(36);
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyiapkan…';
        const selesai = () => { clearInterval(cek); btn.innerHTML = label; btn.disabled = !(D && D.data.length); };
        const cek = setInterval(() => { if (document.cookie.includes('dg_unduh=' + token)) selesai(); }, 500);
        setTimeout(selesai, 120000);
        const ifr = document.createElement('iframe');
        ifr.hidden = true;
        ifr.addEventListener('load', () => {
          if (document.cookie.includes('dg_unduh=' + token)) return;
          let pesan = '';
          try { pesan = (ifr.contentDocument.body.innerText || '').trim().slice(0, 300); } catch (e) {}
          selesai();
          Swal.fire('Export gagal', pesan || 'Server tidak mengirim file Excel.', 'error');
        });
        ifr.src = API + '?' + qs(filter(), { format: 'excel', token });
        document.body.appendChild(ifr);
        setTimeout(() => ifr.remove(), 130000);
      });
      const cetak = isi => window.open(API + '?' + qs(filter(), { format: 'print', isi }), '_blank');
      $id('btnPrint').addEventListener('click', () => cetak(tampil === 'top10' ? 'top10' : 'semua'));
      document.querySelectorAll('[data-cetak]').forEach(a => a.addEventListener('click', e => { e.preventDefault(); cetak(a.dataset.cetak); }));

      /* ---------- event filter ---------- */
      document.querySelectorAll('[data-preset]').forEach(b => b.addEventListener('click', () => {
        const [a, z] = preset(b.dataset.preset);
        $id('fromDate').value = a; $id('toDate').value = z;
        muat();
      }));
      ['fromDate', 'toDate'].forEach(id => $id(id).addEventListener('change', muatNanti));
      ['doctorSelect', 'poliSelect', 'providerSelect', 'genderSelect'].forEach(id => $id(id).addEventListener('change', muat));
      document.querySelectorAll('[name=jenis]').forEach(r => r.addEventListener('change', muat));
      $id('btnReset').addEventListener('click', () => {
        const [a, z] = preset('bulan');
        $id('fromDate').value = a; $id('toDate').value = z;
        ['doctorSelect', 'poliSelect', 'providerSelect', 'genderSelect'].forEach(id => $id(id).value = '');
        $id('jenisUtama').checked = true;
        muat();
      });

      /* ---------- awal ---------- */
      const u = new URLSearchParams(location.search);
      const [a, z] = preset('bulan');
      $id('fromDate').value = u.get('fromDate') || a;
      $id('toDate').value = u.get('toDate') || z;
      $id('genderSelect').value = u.get('gender') || '';
      if (u.get('jenis') === 'semua') $id('jenisSemua').checked = true;
      ['doctor:doctorSelect', 'poli:poliSelect', 'provider:providerSelect'].forEach(p => {
        const [k, id] = p.split(':');
        if (u.get(k)) $id(id).innerHTML = `<option value="${esc(u.get(k))}" selected>${esc(u.get(k))}</option>`;
      });
      muat();
    })();
  </script>
</body>

</html>
