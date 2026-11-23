<?php
/**
 * Laporan Pelayanan Resep (farmasi): jumlah resep, obat keluar, nilai, per dokter / poli / penjamin.
 * Data: controller/report/resepController.php · Export: module/report/export_resep.php · Cetak: module/report/print_resep.php
 */
$title = 'Laporan Pelayanan Resep';
require '../../controller/view.php';
session_write_close();
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    @keyframes rs-spin { to { transform: rotate(360deg); } }
    .rs .spin { display: inline-block; animation: rs-spin 1s linear infinite; }
    .rs .kartu { background: #fff; border: 1px solid #e5eaef; border-radius: 14px; padding: 16px 18px; height: 100%; }
    .rs .judul { font-size: .95rem; font-weight: 700; color: #0f172a; margin: 0; }
    .rs .sub { font-size: .76rem; color: #475569; }
    .rs .kepala { display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; margin-bottom: 10px; }
    .rs-ikon { width: 40px; height: 40px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0; }
    .rs-filter { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 10px; }
    .rs-filter label { font-size: .72rem; color: #475569; display: block; margin-bottom: 2px; }
    .rs-filter .form-control, .rs-filter .form-select { font-size: .84rem; height: 34px; padding-top: 4px; padding-bottom: 4px; }
    .rs-cepat .btn { font-size: .78rem; padding: 6px 10px; background: #fff; border: 1px solid #cbd5e1; color: #1f2937; font-weight: 600; }
    .rs-cepat .btn:hover { background: #f1f5f9; }
    .rs-cepat .btn.active, .rs-cepat .btn.active:hover { background: #1e293b; border-color: #1e293b; color: #fff; }
    .rs-kpi { background: #fff; border: 1px solid #e5eaef; border-radius: 14px; padding: 12px 14px; height: 100%; }
    .rs-kpi .l { font-size: .75rem; color: #475569; font-weight: 600; display: flex; gap: 6px; align-items: center; }
    .rs-kpi .l i { font-size: 1rem; color: #64748b; }
    .rs-kpi .n { font-size: 1.4rem; font-weight: 800; color: #0f172a; line-height: 1.2; margin-top: 4px; font-variant-numeric: tabular-nums; }
    .rs-kpi .d { font-size: .74rem; color: #475569; margin-top: 2px; }
    .rs-bar { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 2px 10px; align-items: center; font-size: .82rem; padding: 5px 0; }
    .rs-bar + .rs-bar { border-top: 1px dashed #eef1f5; }
    .rs-bar .nm { color: #1f2937; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .rs-bar .vl { color: #0f172a; font-weight: 700; text-align: right; font-variant-numeric: tabular-nums; }
    .rs-bar .kc { color: #475569; font-weight: 400; font-size: .74rem; }
    .rs-bar .tr { grid-column: 1 / -1; height: 6px; background: #f1f5f9; border-radius: 4px; overflow: hidden; }
    .rs-bar .tr span { display: block; height: 100%; background: #2a78d6; border-radius: 4px; }
    .rs-leg { display: flex; gap: 12px; font-size: .78rem; color: #475569; }
    .rs-leg span::before { content: ""; display: inline-block; width: 10px; height: 10px; border-radius: 3px; margin-right: 5px; vertical-align: -1px; background: var(--w); }
    .rs-tab .btn { font-size: .76rem; padding: 3px 10px; background: #fff; border: 1px solid #cbd5e1; color: #1f2937; font-weight: 600; }
    .rs-tab .btn.active { background: #1e293b; border-color: #1e293b; color: #fff; }
    #tblRs { font-size: .84rem; }
    #tblRs thead th { font-size: .72rem; text-transform: uppercase; letter-spacing: .03em; color: #475569; font-weight: 600; white-space: nowrap; background: #f8fafc; }
    #tblRs td { vertical-align: middle; white-space: nowrap; }
    #tblRs .num { text-align: right; font-variant-numeric: tabular-nums; }
    .rs-st { display: inline-block; font-size: .72rem; font-weight: 700; border-radius: 6px; padding: 1px 7px; }
    .rs-st.s3 { background: #dcfce7; color: #166534; }
    .rs-st.s1, .rs-st.s2 { background: #fef3c7; color: #78350f; }
    .rs-st.s0 { background: #f1f5f9; color: #334155; }
    .rs-memuat { opacity: .55; pointer-events: none; }
    .rs-kosong { color: #475569; text-align: center; font-size: .84rem; padding: 18px; }
    .rs-b { font-size: .76rem; font-weight: 600; padding: 3px 9px; border-radius: 6px; border: 1px solid #1e293b; background: #1e293b; color: #fff; text-decoration: none; display: inline-flex; gap: 4px; align-items: center; }
    .rs-b:hover { background: #0f172a; color: #fff; }
  </style>
</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <?php require 'sidebar.php'; ?>
    <div class="body-wrapper">
      <?php require 'navbar.php'; ?>
      <div class="body-wrapper-inner">
        <div class="container-fluid rs">

          <div class="kartu mb-3" style="height:auto">
            <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
              <span class="rs-ikon bg-primary-subtle text-primary"><i class="ti ti-prescription"></i></span>
              <div class="flex-grow-1" style="min-width:0">
                <h5 class="fw-semibold mb-0">Laporan Pelayanan Resep</h5>
                <div class="sub"><span id="rsPeriode">-</span> · <span id="rsUpdate">memuat…</span></div>
              </div>
              <div class="d-flex gap-2">
                <a href="module/admin/rpt_farmasi" class="btn btn-sm btn-outline-dark"><i class="ti ti-packages me-1"></i>Laporan stok</a>
                <button type="button" class="btn btn-sm btn-success" id="btnExcel" disabled><i class="ti ti-file-spreadsheet me-1"></i>Excel</button>
                <button type="button" class="btn btn-sm btn-dark" id="btnCetak" disabled><i class="ti ti-printer me-1"></i>Cetak</button>
              </div>
            </div>
            <div class="rs-filter">
              <div>
                <label>Periode (tanggal resep)</label>
                <div class="btn-group rs-cepat" id="rsCepat">
                  <button type="button" class="btn" data-r="hari">Hari ini</button>
                  <button type="button" class="btn" data-r="kemarin">Kemarin</button>
                  <button type="button" class="btn" data-r="7">7 hari</button>
                  <button type="button" class="btn" data-r="bulan">Bulan ini</button>
                  <button type="button" class="btn" data-r="bulanlalu">Bulan lalu</button>
                </div>
              </div>
              <div><label for="rsDari">Dari</label><input type="date" id="rsDari" class="form-control"></div>
              <div><label for="rsSampai">Sampai</label><input type="date" id="rsSampai" class="form-control"></div>
              <div>
                <label for="rsStatus">Status</label>
                <select id="rsStatus" class="form-select" style="min-width:140px">
                  <option value="">Semua</option>
                  <option value="3">Selesai (diserahkan)</option>
                  <option value="proses">Dalam proses</option>
                  <option value="0">Draf dokter</option>
                </select>
              </div>
              <div>
                <label for="rsJenis">Jenis</label>
                <select id="rsJenis" class="form-select" style="min-width:130px">
                  <option value="">Semua</option>
                  <option value="Non Racikan">Non racikan</option>
                  <option value="Racikan">Racikan</option>
                  <option value="BMHP">BMHP</option>
                </select>
              </div>
              <div><label for="rsPenjamin">Penjamin</label><select id="rsPenjamin" class="form-select" style="min-width:150px"><option value="">Semua</option></select></div>
              <div><label for="rsDokter">Dokter</label><select id="rsDokter" class="form-select" style="min-width:170px"><option value="">Semua</option></select></div>
              <button type="button" class="btn btn-dark btn-sm" id="btnTerapkan" style="height:34px"><i class="ti ti-filter me-1"></i>Terapkan</button>
              <button type="button" class="btn btn-light btn-sm" id="btnReset" style="height:34px" title="Reset filter"><i class="ti ti-arrow-back-up"></i></button>
            </div>
          </div>

          <div id="rsIsi">
            <div class="row g-3 mb-3">
              <div class="col-6 col-xl"><div class="rs-kpi"><div class="l"><i class="ti ti-file-text"></i>Resep</div><div class="n" id="kResep">-</div><div class="d" id="kResepD">&nbsp;</div></div></div>
              <div class="col-6 col-xl"><div class="rs-kpi"><div class="l"><i class="ti ti-circle-check"></i>Selesai diserahkan</div><div class="n" id="kSelesai">-</div><div class="d" id="kSelesaiD">&nbsp;</div></div></div>
              <div class="col-6 col-xl"><div class="rs-kpi"><div class="l"><i class="ti ti-pill"></i>Obat keluar</div><div class="n" id="kQty">-</div><div class="d" id="kQtyD">&nbsp;</div></div></div>
              <div class="col-6 col-xl"><div class="rs-kpi"><div class="l"><i class="ti ti-cash"></i>Nilai obat</div><div class="n" id="kNilai">-</div><div class="d" id="kNilaiD">&nbsp;</div></div></div>
              <div class="col-12 col-xl"><div class="rs-kpi"><div class="l"><i class="ti ti-flask"></i>Racikan</div><div class="n" id="kRacik">-</div><div class="d" id="kRacikD">&nbsp;</div></div></div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-xl-8">
                <div class="kartu">
                  <div class="kepala"><div><h6 class="judul">Resep harian</h6><div class="sub" id="harianSub">-</div></div>
                    <div class="rs-leg"><span style="--w:#2a78d6">Selesai</span><span style="--w:#eb6834">Belum selesai</span></div></div>
                  <div style="position:relative;height:260px"><canvas id="cHarian" role="img" aria-label="Grafik resep harian"></canvas></div>
                </div>
              </div>
              <div class="col-xl-4">
                <div class="kartu">
                  <div class="kepala"><div><h6 class="judul">Status &amp; jenis resep</h6><div class="sub">Jumlah resep</div></div></div>
                  <div id="lStatus"></div>
                  <div class="sub mt-3 mb-1 fw-semibold">Jenis</div>
                  <div id="lJenis"></div>
                </div>
              </div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-xl-6">
                <div class="kartu">
                  <div class="kepala">
                    <div><h6 class="judul">15 obat teratas</h6><div class="sub" id="obatSub">-</div></div>
                    <div class="btn-group rs-tab" id="obatTab"><button type="button" class="btn active" data-k="qty">Jumlah</button><button type="button" class="btn" data-k="nilai">Nilai</button></div>
                  </div>
                  <div id="lObat"></div>
                </div>
              </div>
              <div class="col-xl-6">
                <div class="kartu">
                  <div class="kepala">
                    <div><h6 class="judul">Resep per kategori</h6><div class="sub">Jumlah resep &amp; nilai obat</div></div>
                    <div class="btn-group rs-tab" id="grupTab"><button type="button" class="btn active" data-g="dokter">Dokter</button><button type="button" class="btn" data-g="poli">Poli</button><button type="button" class="btn" data-g="penjamin">Penjamin</button></div>
                  </div>
                  <div id="lGrup"></div>
                </div>
              </div>
            </div>

            <div class="kartu mb-3" style="height:auto">
              <div class="kepala">
                <div><h6 class="judul">Rincian resep</h6><div class="sub" id="detailSub">-</div></div>
                <input type="search" id="rsCari" class="form-control form-control-sm" placeholder="Cari pasien / RM / no. resep / dokter" style="max-width:280px">
              </div>
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 w-100" id="tblRs">
                  <thead>
                    <tr>
                      <th style="width:1%">Aksi</th>
                      <th>Tanggal</th>
                      <th>No. resep</th>
                      <th>Pasien</th>
                      <th>Dokter / poli</th>
                      <th>Penjamin</th>
                      <th>Jenis</th>
                      <th>Status</th>
                      <th class="num">Item</th>
                      <th class="num">Qty</th>
                      <th class="num">Nilai</th>
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

  <?php require 'library.php'; ?>
  <script>
    (function() {
      "use strict";
      const API = 'controller/report/resepController.php';
      const KUNCI = 'laporanResep.filter';
      const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      const HARI_INI = new Date().toLocaleDateString('sv-SE', { timeZone: 'Asia/Jakarta' });
      const $id = id => document.getElementById(id);
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);
      const ang = n => Number(n || 0).toLocaleString('id-ID');
      const rp = n => 'Rp' + Number(n || 0).toLocaleString('id-ID');
      const pct = (a, b) => b ? (Math.round(a / b * 1000) / 10).toLocaleString('id-ID') : '0';
      const tglID = d => { const [y, m, h] = String(d).split('-'); return `${+h} ${BULAN[+m - 1]} ${y}`; };
      const geser = (ymd, n) => { const d = new Date(ymd + 'T12:00:00'); d.setDate(d.getDate() + n); return d.toISOString().slice(0, 10); };
      function rentang(r) {
        const awal = HARI_INI.slice(0, 8) + '01';
        if (r === 'hari') return [HARI_INI, HARI_INI];
        if (r === 'kemarin') return [geser(HARI_INI, -1), geser(HARI_INI, -1)];
        if (r === '7') return [geser(HARI_INI, -6), HARI_INI];
        if (r === 'bulan') return [awal, HARI_INI];
        const akhir = geser(awal, -1); return [akhir.slice(0, 8) + '01', akhir];
      }

      let f = { from: HARI_INI, to: HARI_INI, status: '', jenis: '', penjamin: '', dokter: '' };
      try { const s = JSON.parse(localStorage.getItem(KUNCI) || 'null'); if (s && s.f) { Object.assign(f, s.f); if (s.hari !== HARI_INI) { f.from = HARI_INI; f.to = HARI_INI; } } } catch (e) {}
      const simpan = () => { try { localStorage.setItem(KUNCI, JSON.stringify({ hari: HARI_INI, f })); } catch (e) {} };
      $id('rsDari').value = f.from; $id('rsSampai').value = f.to; $id('rsStatus').value = f.status; $id('rsJenis').value = f.jenis;
      $id('rsDari').max = $id('rsSampai').max = HARI_INI;
      const qs = (tambah) => new URLSearchParams(Object.assign({ from: f.from, to: f.to, status: f.status, jenis: f.jenis, penjamin: f.penjamin, dokter: f.dokter }, tambah || {})).toString();
      const tandai = () => document.querySelectorAll('#rsCepat .btn').forEach(b => { const r = rentang(b.dataset.r); b.classList.toggle('active', r[0] === f.from && r[1] === f.to); });

      const table = $('#tblRs').DataTable({
        data: [], deferRender: true, order: [[1, 'desc']], pageLength: 25, lengthMenu: [10, 25, 50, 100],
        dom: 'rt<"d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3"ilp>',
        language: { thousands: '.', info: '_START_–_END_ dari _TOTAL_ resep', infoEmpty: '0 resep', infoFiltered: '(disaring dari _MAX_)', lengthMenu: '_MENU_ / halaman',
          zeroRecords: '<div class="rs-kosong">Tidak ada resep yang cocok.</div>', emptyTable: '<div class="rs-kosong">Belum ada resep pada periode ini.</div>', paginate: { previous: '‹', next: '›' } },
        columns: [
          { data: null, orderable: false, render: (r, t) => t !== 'display' ? '' : `<a class="rs-b" href="module/admin/farmasi_order_detail?no=${encodeURIComponent(r.visit_ID)}&rm=${encodeURIComponent(r.nomor_rm)}"><i class="ti ti-eye"></i>Detail</a>` },
          { data: null, render: (r, t) => t !== 'display' ? `${r.tanggal} ${r.jam}` : `${esc(tglID(r.tanggal))}<div class="sub">${esc(r.jam)}</div>` },
          { data: 'nomor', render: v => esc(v) },
          { data: null, render: (r, t) => t !== 'display' ? `${r.pasien} ${r.nomor_rm}` : `<div class="fw-semibold">${esc(r.pasien)}</div><div class="sub">RM ${esc(r.nomor_rm)}</div>` },
          { data: null, render: (r, t) => t !== 'display' ? `${r.dokter} ${r.poli}` : `<div>${esc(r.dokter)}</div><div class="sub">${esc(r.poli)}</div>` },
          { data: 'penjamin', render: v => esc(v) },
          { data: null, render: (r, t) => t !== 'display' ? r.jenis : `${esc(r.jenis)}${r.racikan ? `<div class="sub">${esc(r.racikan)}</div>` : ''}` },
          { data: null, render: (r, t) => t !== 'display' ? r.status_label : `<span class="rs-st s${r.status}">${esc(r.status_label)}</span>` },
          { data: 'item', className: 'num' },
          { data: 'qty', className: 'num', render: (v, t) => t === 'display' ? ang(v) : v },
          { data: 'nilai', className: 'num fw-semibold', render: (v, t) => t === 'display' ? rp(v) : v },
        ],
      });
      let tCari;
      $id('rsCari').addEventListener('input', function() { clearTimeout(tCari); tCari = setTimeout(() => table.search(this.value).draw(), 200); });

      function bar(nama, nilai, maks, teks, kecil, warna) {
        const w = maks ? Math.max(2, Math.round(nilai / maks * 100)) : 0;
        return `<div class="rs-bar"><div class="nm" title="${esc(nama)}">${esc(nama)}${kecil ? ` <span class="kc">${kecil}</span>` : ''}</div><div class="vl">${teks}</div><div class="tr"><span style="width:${w}%;${warna ? 'background:' + warna : ''}"></span></div></div>`;
      }
      function isiOpsi(sel, items, nilai) {
        const ada = items.some(o => o.id === nilai);
        $id(sel).innerHTML = '<option value="">Semua</option>' + (nilai && !ada ? `<option value="${esc(nilai)}">${esc(nilai)}</option>` : '') + items.map(o => `<option value="${esc(o.id)}">${esc(o.nama)}</option>`).join('');
        $id(sel).value = nilai;
      }
      let grafik = null, terakhir = null, sibuk = false, modeObat = 'qty', modeGrup = 'dokter', dokterDikenal = new Set();

      async function muat() {
        const d = $id('rsDari').value || HARI_INI, s = $id('rsSampai').value || d;
        if (s < d) return Swal.fire('Tanggal tidak valid', '"Sampai" tidak boleh sebelum "Dari".', 'warning');
        if ((new Date(s) - new Date(d)) / 864e5 > 91) return Swal.fire('Rentang terlalu panjang', 'Maksimal 92 hari (±3 bulan).', 'warning');
        if (sibuk) return;
        sibuk = true;
        Object.assign(f, { from: d, to: s, status: $id('rsStatus').value, jenis: $id('rsJenis').value });
        if (terakhir) Object.assign(f, { penjamin: $id('rsPenjamin').value, dokter: $id('rsDokter').value });
        simpan(); tandai();
        $id('rsIsi').classList.add('rs-memuat');
        $id('btnTerapkan').innerHTML = '<i class="ti ti-loader-2 spin me-1"></i>Memuat';
        try {
          const r = await fetch(API + '?' + qs(), { credentials: 'same-origin' });
          const j = await r.json().catch(() => ({}));
          if (!r.ok || j.status !== 'success') throw new Error(j.message || 'Server tidak merespon (' + r.status + ').');
          terakhir = j;
          render(j);
          $id('rsUpdate').textContent = 'diperbarui ' + new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        } catch (e) {
          $id('rsUpdate').textContent = 'gagal memuat';
          Swal.fire('Gagal memuat laporan', e.message, 'error');
        } finally {
          sibuk = false;
          $id('rsIsi').classList.remove('rs-memuat');
          $id('btnTerapkan').innerHTML = '<i class="ti ti-filter me-1"></i>Terapkan';
        }
      }

      function gambarObat() {
        if (!terakhir) return;
        const arr = modeObat === 'qty' ? terakhir.obat_qty : terakhir.obat_nilai;
        const maks = Math.max(...arr.map(x => x[modeObat]), 1);
        $id('lObat').innerHTML = arr.length ? arr.map(x => bar(x.nama, x[modeObat], maks,
          modeObat === 'qty' ? `${ang(x.qty)}${x.satuan ? ' ' + esc(x.satuan) : ''}` : rp(x.nilai), `${ang(x.resep)} resep`)).join('') : '<div class="rs-kosong">Tidak ada data obat.</div>';
      }
      function gambarGrup() {
        if (!terakhir) return;
        const arr = terakhir.grup[modeGrup].slice(0, 12), total = terakhir.ringkasan.resep;
        const maks = Math.max(...arr.map(x => x.resep), 1);
        $id('lGrup').innerHTML = arr.length ? arr.map(x => bar(x.nama, x.resep, maks, `${ang(x.resep)} <span class="kc">${pct(x.resep, total)}%</span>`, x.nilai ? rp(x.nilai) : '')).join('') : '<div class="rs-kosong">Tidak ada data.</div>';
      }

      function render(j) {
        const k = j.ringkasan, p = j.periode;
        isiOpsi('rsPenjamin', j.opsi.penjamin, f.penjamin);
        j.opsi.dokter.forEach(x => dokterDikenal.add(x));
        isiOpsi('rsDokter', [...dokterDikenal].sort().map(x => ({ id: x, nama: x })), f.dokter);
        $id('rsPeriode').textContent = p.from === p.to ? tglID(p.from) : `${tglID(p.from)} – ${tglID(p.to)}`;
        $id('btnExcel').disabled = $id('btnCetak').disabled = !k.resep;

        $id('kResep').textContent = ang(k.resep);
        $id('kResepD').textContent = `${ang(k.kunjungan)} kunjungan · ${ang(k.pasien)} pasien`;
        $id('kSelesai').textContent = pct(k.selesai, k.resep) + '%';
        $id('kSelesaiD').textContent = `${ang(k.selesai)} selesai · ${ang(k.proses)} proses` + (k.draf ? ` · ${ang(k.draf)} draf` : '');
        $id('kQty').textContent = ang(k.qty);
        $id('kQtyD').textContent = `${ang(k.item)} item · rata-rata ${String(k.rata_item).replace('.', ',')} item/resep`;
        $id('kNilai').textContent = rp(k.nilai);
        $id('kNilaiD').textContent = k.nilai ? `diserahkan ${rp(k.nilai_diserahkan)} · rata-rata ${rp(k.rata_nilai)}/resep` : 'harga obat belum diisi di resep';
        $id('kRacik').textContent = ang(k.racikan);
        $id('kRacikD').textContent = `${pct(k.racikan, k.resep)}% dari resep`;

        $id('harianSub').textContent = `${j.harian.length} hari · rata-rata ${ang(Math.round(k.resep / Math.max(1, j.harian.length) * 10) / 10)} resep per hari`;
        if (typeof Chart !== 'undefined') {
          if (grafik) grafik.destroy();
          const h = j.harian.length;
          grafik = new Chart($id('cHarian'), {
            type: 'bar',
            data: {
              labels: j.harian.map(x => { const [, m, d] = x.tanggal.split('-'); return h > 62 ? `${+d} ${BULAN[+m - 1]}` : `${+d}/${+m}`; }),
              datasets: [
                { label: 'Selesai', data: j.harian.map(x => x.selesai), backgroundColor: '#2a78d6', borderRadius: 3, maxBarThickness: 30 },
                { label: 'Belum selesai', data: j.harian.map(x => x.resep - x.selesai), backgroundColor: '#eb6834', borderRadius: 3, maxBarThickness: 30 },
              ]
            },
            options: {
              responsive: true, maintainAspectRatio: false, animation: { duration: 250 }, interaction: { mode: 'index', intersect: false },
              scales: {
                x: { stacked: true, grid: { display: false }, ticks: { color: '#64748b', font: { size: 11 }, maxRotation: 0, autoSkip: true, maxTicksLimit: 16 }, border: { display: false } },
                y: { stacked: true, beginAtZero: true, grid: { color: '#eef1f5' }, border: { display: false }, ticks: { color: '#64748b', font: { size: 11 }, precision: 0 } }
              },
              plugins: { legend: { display: false }, tooltip: { callbacks: {
                title: it => tglID(j.harian[it[0].dataIndex].tanggal),
                label: c => `${c.dataset.label}: ${ang(c.raw)} resep`,
                footer: it => { const x = j.harian[it[0].dataIndex]; return `Total ${ang(x.resep)} resep · ${rp(x.nilai)}`; }
              } } }
            }
          });
        }

        const st = j.grup.status, maksSt = Math.max(...st.map(x => x.resep), 1);
        const warnaSt = { 'Selesai': '#1baf7a', 'Menunggu': '#eda100', 'Disiapkan': '#eda100', 'Draf dokter': '#94a3b8' };
        $id('lStatus').innerHTML = st.length ? st.map(x => bar(x.nama, x.resep, maksSt, `${ang(x.resep)} <span class="kc">${pct(x.resep, k.resep)}%</span>`, '', warnaSt[x.nama])).join('') : '<div class="rs-kosong">-</div>';
        const jn = j.grup.jenis, maksJ = Math.max(...jn.map(x => x.resep), 1);
        $id('lJenis').innerHTML = jn.length ? jn.map(x => bar(x.nama, x.resep, maksJ, `${ang(x.resep)} <span class="kc">${pct(x.resep, k.resep)}%</span>`)).join('') : '<div class="rs-kosong">-</div>';
        $id('obatSub').textContent = `${ang(j.jumlah_obat)} jenis obat/BMHP keluar`;
        gambarObat();
        gambarGrup();

        $id('detailSub').textContent = j.data_total > j.data.length
          ? `Menampilkan ${ang(j.data.length)} resep terbaru dari ${ang(j.data_total)} — unduh Excel untuk rincian lengkap`
          : `${ang(j.data_total)} resep`;
        table.clear().rows.add(j.data).draw();
      }

      document.querySelectorAll('#obatTab .btn').forEach(b => b.addEventListener('click', () => { modeObat = b.dataset.k; document.querySelectorAll('#obatTab .btn').forEach(x => x.classList.toggle('active', x === b)); gambarObat(); }));
      document.querySelectorAll('#grupTab .btn').forEach(b => b.addEventListener('click', () => { modeGrup = b.dataset.g; document.querySelectorAll('#grupTab .btn').forEach(x => x.classList.toggle('active', x === b)); gambarGrup(); }));

      $id('btnExcel').addEventListener('click', function() {
        const btn = this, token = Date.now().toString(36), label = btn.innerHTML;
        btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyiapkan…';
        const selesai = () => { clearInterval(cek); btn.innerHTML = label; btn.disabled = false; };
        const cek = setInterval(() => { if (document.cookie.includes('rs_unduh=' + token)) selesai(); }, 500);
        setTimeout(selesai, 120000);
        const ifr = document.createElement('iframe');
        ifr.hidden = true;
        ifr.addEventListener('load', () => {
          let pesan = ''; try { pesan = (ifr.contentDocument.body.innerText || '').trim().slice(0, 300); } catch (e) {}
          if (document.cookie.includes('rs_unduh=' + token)) return;
          selesai(); Swal.fire('Export gagal', pesan || 'Server tidak mengirim file Excel.', 'error');
        });
        ifr.src = 'module/report/export_resep.php?' + qs({ token });
        document.body.appendChild(ifr);
        setTimeout(() => ifr.remove(), 130000);
      });
      $id('btnCetak').addEventListener('click', () => window.open('module/report/print_resep.php?' + qs(), '_blank'));

      document.querySelectorAll('#rsCepat .btn').forEach(b => b.addEventListener('click', () => { const r = rentang(b.dataset.r); $id('rsDari').value = r[0]; $id('rsSampai').value = r[1]; muat(); }));
      $id('btnTerapkan').addEventListener('click', muat);
      ['rsStatus', 'rsJenis', 'rsPenjamin', 'rsDokter'].forEach(i => $id(i).addEventListener('change', muat));
      ['rsDari', 'rsSampai'].forEach(i => $id(i).addEventListener('keydown', e => { if (e.key === 'Enter') muat(); }));
      $id('btnReset').addEventListener('click', () => {
        $id('rsDari').value = $id('rsSampai').value = HARI_INI;
        ['rsStatus', 'rsJenis', 'rsPenjamin', 'rsDokter'].forEach(i => { $id(i).value = ''; });
        f.penjamin = f.dokter = '';
        muat();
      });

      tandai();
      muat();
    })();
  </script>
</body>

</html>
