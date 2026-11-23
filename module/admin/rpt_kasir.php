<?php
/**
 * Laporan Kasir: penerimaan pembayaran per periode.
 * Data: controller/report/kasirController.php · Export: module/report/export_kasir.php · Cetak: module/report/print_kasir.php
 */
$title = 'Laporan Kasir';
require '../../controller/view.php';
session_write_close();
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    @keyframes rk-spin { to { transform: rotate(360deg); } }
    .rk .spin { display: inline-block; animation: rk-spin 1s linear infinite; }
    .rk .kartu { background: #fff; border: 1px solid #e5eaef; border-radius: 14px; padding: 16px 18px; height: 100%; }
    .rk .judul { font-size: .95rem; font-weight: 700; color: #0f172a; margin: 0; }
    .rk .sub { font-size: .76rem; color: #475569; }
    .rk .kepala { display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; margin-bottom: 10px; }
    .rk-ikon { width: 40px; height: 40px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0; }
    .rk-filter { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 10px; }
    .rk-filter label { font-size: .72rem; color: #475569; display: block; margin-bottom: 2px; }
    .rk-filter .form-control, .rk-filter .form-select { font-size: .84rem; height: 34px; padding-top: 4px; padding-bottom: 4px; }
    .rk-cepat .btn { font-size: .78rem; padding: 6px 10px; background: #fff; border: 1px solid #cbd5e1; color: #1f2937; font-weight: 600; }
    .rk-cepat .btn:hover { background: #f1f5f9; }
    .rk-cepat .btn.active, .rk-cepat .btn.active:hover { background: #1e293b; border-color: #1e293b; color: #fff; }
    .rk-kpi { background: #fff; border: 1px solid #e5eaef; border-radius: 14px; padding: 12px 14px; height: 100%; }
    .rk-kpi .l { font-size: .75rem; color: #475569; font-weight: 600; display: flex; gap: 6px; align-items: center; }
    .rk-kpi .l i { font-size: 1rem; color: #64748b; }
    .rk-kpi .n { font-size: 1.4rem; font-weight: 800; color: #0f172a; line-height: 1.2; margin-top: 4px; font-variant-numeric: tabular-nums; }
    .rk-kpi .d { font-size: .74rem; color: #475569; margin-top: 2px; }
    .rk-kpi.warn { border-color: #fcd34d; background: #fffbeb; }
    .rk-bar { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 2px 10px; align-items: center; font-size: .82rem; padding: 5px 0; }
    .rk-bar + .rk-bar { border-top: 1px dashed #eef1f5; }
    .rk-bar .nm { color: #1f2937; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .rk-bar .vl { color: #0f172a; font-weight: 700; text-align: right; font-variant-numeric: tabular-nums; }
    .rk-bar .kc { color: #475569; font-weight: 400; font-size: .74rem; }
    .rk-bar .tr { grid-column: 1 / -1; height: 6px; background: #f1f5f9; border-radius: 4px; overflow: hidden; }
    .rk-bar .tr span { display: block; height: 100%; background: #2a78d6; border-radius: 4px; }
    .rk-leg { display: flex; gap: 12px; font-size: .78rem; color: #475569; }
    .rk-leg span::before { content: ""; display: inline-block; width: 10px; height: 10px; border-radius: 3px; margin-right: 5px; vertical-align: -1px; background: var(--w); }
    #tblRk { font-size: .84rem; }
    #tblRk thead th { font-size: .72rem; text-transform: uppercase; letter-spacing: .03em; color: #475569; font-weight: 600; white-space: nowrap; background: #f8fafc; }
    #tblRk td { vertical-align: middle; white-space: nowrap; }
    #tblRk .num { text-align: right; font-variant-numeric: tabular-nums; }
    .rk-memuat { opacity: .55; pointer-events: none; }
    .rk-kosong { color: #475569; text-align: center; font-size: .84rem; padding: 18px; }
    .rk-b-inv { font-size: .76rem; font-weight: 600; padding: 3px 9px; border-radius: 6px; border: 1px solid #1e293b; background: #1e293b; color: #fff; text-decoration: none; display: inline-flex; gap: 4px; align-items: center; }
    .rk-b-inv:hover { background: #0f172a; color: #fff; }
  </style>
</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <?php require 'sidebar.php'; ?>
    <div class="body-wrapper">
      <?php require 'navbar.php'; ?>
      <div class="body-wrapper-inner">
        <div class="container-fluid rk">

          <div class="kartu mb-3" style="height:auto">
            <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
              <span class="rk-ikon bg-primary-subtle text-primary"><i class="ti ti-report-money"></i></span>
              <div class="flex-grow-1" style="min-width:0">
                <h5 class="fw-semibold mb-0">Laporan Kasir</h5>
                <div class="sub"><span id="rkPeriode">-</span> · <span id="rkUpdate">memuat…</span></div>
              </div>
              <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-success" id="btnExcel" disabled><i class="ti ti-file-spreadsheet me-1"></i>Excel</button>
                <button type="button" class="btn btn-sm btn-dark" id="btnCetak" disabled><i class="ti ti-printer me-1"></i>Cetak</button>
              </div>
            </div>
            <div class="rk-filter">
              <div>
                <label>Periode</label>
                <div class="btn-group rk-cepat" id="rkCepat">
                  <button type="button" class="btn" data-r="hari">Hari ini</button>
                  <button type="button" class="btn" data-r="kemarin">Kemarin</button>
                  <button type="button" class="btn" data-r="7">7 hari</button>
                  <button type="button" class="btn" data-r="bulan">Bulan ini</button>
                  <button type="button" class="btn" data-r="bulanlalu">Bulan lalu</button>
                </div>
              </div>
              <div><label for="rkDari">Dari</label><input type="date" id="rkDari" class="form-control"></div>
              <div><label for="rkSampai">Sampai</label><input type="date" id="rkSampai" class="form-control"></div>
              <div>
                <label for="rkDasar">Berdasarkan</label>
                <select id="rkDasar" class="form-select" style="min-width:150px">
                  <option value="bayar">Tanggal bayar</option>
                  <option value="kunjungan">Tanggal kunjungan</option>
                </select>
              </div>
              <div><label for="rkMetode">Metode bayar</label><select id="rkMetode" class="form-select" style="min-width:140px"><option value="">Semua</option></select></div>
              <div><label for="rkPenjamin">Penjamin</label><select id="rkPenjamin" class="form-select" style="min-width:150px"><option value="">Semua</option></select></div>
              <div><label for="rkPoli">Poli</label><select id="rkPoli" class="form-select" style="min-width:140px"><option value="">Semua</option></select></div>
              <button type="button" class="btn btn-dark btn-sm" id="btnTerapkan" style="height:34px"><i class="ti ti-filter me-1"></i>Terapkan</button>
              <button type="button" class="btn btn-light btn-sm" id="btnReset" style="height:34px" title="Reset filter"><i class="ti ti-arrow-back-up"></i></button>
            </div>
          </div>

          <div id="rkIsi">
            <div class="row g-3 mb-3">
              <div class="col-6 col-xl"><div class="rk-kpi"><div class="l"><i class="ti ti-cash"></i>Total diterima</div><div class="n" id="kTotal">-</div><div class="d" id="kTotalD">&nbsp;</div></div></div>
              <div class="col-6 col-xl"><div class="rk-kpi"><div class="l"><i class="ti ti-receipt"></i>Transaksi</div><div class="n" id="kTrx">-</div><div class="d" id="kTrxD">&nbsp;</div></div></div>
              <div class="col-6 col-xl"><div class="rk-kpi"><div class="l"><i class="ti ti-calculator"></i>Rata-rata / transaksi</div><div class="n" id="kRata">-</div><div class="d" id="kRataD">&nbsp;</div></div></div>
              <div class="col-6 col-xl"><div class="rk-kpi"><div class="l"><i class="ti ti-wallet"></i>Tunai · Non-tunai</div><div class="n" id="kTunai">-</div><div class="d" id="kTunaiD">&nbsp;</div></div></div>
              <div class="col-12 col-xl"><a href="module/admin/kasir" class="text-decoration-none"><div class="rk-kpi warn"><div class="l"><i class="ti ti-alert-triangle" style="color:#b45309"></i>Belum dibayar</div><div class="n" id="kBelum">-</div><div class="d" id="kBelumD">&nbsp;</div></div></a></div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-xl-8">
                <div class="kartu">
                  <div class="kepala"><div><h6 class="judul">Penerimaan harian</h6><div class="sub" id="harianSub">-</div></div>
                    <div class="rk-leg"><span style="--w:#2a78d6">Tunai</span><span style="--w:#eb6834">Non-tunai</span></div></div>
                  <div style="position:relative;height:260px"><canvas id="cHarian" role="img" aria-label="Grafik penerimaan harian"></canvas></div>
                </div>
              </div>
              <div class="col-xl-4">
                <div class="kartu">
                  <div class="kepala"><div><h6 class="judul">Per metode bayar</h6><div class="sub">Total diterima</div></div></div>
                  <div id="lMetode"></div>
                  <div class="kepala mt-3 mb-1"><div><h6 class="judul">Komposisi tagihan</h6><div class="sub">Dari rincian tagihan transaksi</div></div></div>
                  <div id="lKomposisi"></div>
                </div>
              </div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-xl-6"><div class="kartu"><div class="kepala"><div><h6 class="judul">Per penjamin</h6><div class="sub">Transaksi &amp; total diterima</div></div></div><div id="lPenjamin"></div></div></div>
              <div class="col-xl-6"><div class="kartu"><div class="kepala"><div><h6 class="judul">Per poli</h6><div class="sub">Transaksi &amp; total diterima</div></div></div><div id="lPoli"></div></div></div>
            </div>

            <div class="kartu mb-3" style="height:auto">
              <div class="kepala">
                <div><h6 class="judul">Rincian transaksi</h6><div class="sub" id="detailSub">-</div></div>
                <input type="search" id="rkCari" class="form-control form-control-sm" placeholder="Cari pasien / RM / no. transaksi" style="max-width:260px">
              </div>
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 w-100" id="tblRk">
                  <thead>
                    <tr>
                      <th style="width:1%">Aksi</th>
                      <th>Tgl bayar</th>
                      <th>No. transaksi</th>
                      <th>Pasien</th>
                      <th>Penjamin</th>
                      <th>Poli / dokter</th>
                      <th>Metode</th>
                      <th class="num">Obat</th>
                      <th class="num">Tindakan</th>
                      <th class="num">Total</th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                  <tfoot>
                    <tr class="fw-bold"><td colspan="7" class="text-end">Total (sesuai filter tabel)</td><td class="num" id="fObat">-</td><td class="num" id="fTindakan">-</td><td class="num" id="fTotal">-</td></tr>
                  </tfoot>
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
      const API = 'controller/report/kasirController.php';
      const KUNCI = 'laporanKasir.filter';
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

      // filter: tersimpan per peramban, periode kembali ke "hari ini" saat hari berganti
      let f = { from: HARI_INI, to: HARI_INI, dasar: 'bayar', metode: '', penjamin: '', poli: '' };
      try { const s = JSON.parse(localStorage.getItem(KUNCI) || 'null'); if (s && s.f) { Object.assign(f, s.f); if (s.hari !== HARI_INI) { f.from = HARI_INI; f.to = HARI_INI; } } } catch (e) {}
      const simpan = () => { try { localStorage.setItem(KUNCI, JSON.stringify({ hari: HARI_INI, f })); } catch (e) {} };
      $id('rkDari').value = f.from; $id('rkSampai').value = f.to; $id('rkDasar').value = f.dasar;
      $id('rkDari').max = $id('rkSampai').max = HARI_INI;
      const qs = (tambah) => new URLSearchParams(Object.assign({ from: f.from, to: f.to, dasar: f.dasar, metode: f.metode, penjamin: f.penjamin, poli: f.poli }, tambah || {})).toString();
      const tandai = () => document.querySelectorAll('#rkCepat .btn').forEach(b => { const r = rentang(b.dataset.r); b.classList.toggle('active', r[0] === f.from && r[1] === f.to); });

      const table = $('#tblRk').DataTable({
        data: [], deferRender: true, order: [[1, 'desc'], [2, 'desc']], pageLength: 25, lengthMenu: [10, 25, 50, 100],
        dom: 'rt<"d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3"ilp>',
        language: { thousands: '.', info: '_START_–_END_ dari _TOTAL_ transaksi', infoEmpty: '0 transaksi', infoFiltered: '(disaring dari _MAX_)', lengthMenu: '_MENU_ / halaman',
          zeroRecords: '<div class="rk-kosong">Tidak ada transaksi yang cocok.</div>', emptyTable: '<div class="rk-kosong">Belum ada pembayaran pada periode ini.</div>', paginate: { previous: '‹', next: '›' } },
        columns: [
          { data: null, orderable: false, render: (r, t) => t !== 'display' ? '' : `<a class="rk-b-inv" target="_blank" href="module/print/struk_billing?no=${encodeURIComponent(r.visit_ID)}&rm=${encodeURIComponent(r.nomor_rm)}"><i class="ti ti-printer"></i>Invoice</a>` },
          { data: 'tgl_bayar', render: (v, t, r) => t !== 'display' ? v : `${esc(tglID(v))}${r.tgl_kunjungan !== v ? `<div class="sub">kunjungan ${esc(tglID(r.tgl_kunjungan))}</div>` : ''}` },
          { data: 'no_transaksi', render: v => esc(v) },
          { data: null, render: (r, t) => t !== 'display' ? `${r.pasien} ${r.nomor_rm}` : `<div class="fw-semibold">${esc(r.pasien)}</div><div class="sub">RM ${esc(r.nomor_rm)}</div>` },
          { data: 'penjamin', render: v => esc(v) },
          { data: null, render: (r, t) => t !== 'display' ? `${r.poli} ${r.dokter}` : `<div>${esc(r.poli)}</div><div class="sub">${esc(r.dokter)}</div>` },
          { data: 'metode', render: v => esc(v) },
          { data: 'obat', className: 'num', render: (v, t) => t === 'display' ? (v ? rp(v) : '-') : v },
          { data: 'tindakan', className: 'num', render: (v, t) => t === 'display' ? (v ? rp(v) : '-') : v },
          { data: 'total', className: 'num fw-semibold', render: (v, t) => t === 'display' ? rp(v) : v },
        ],
        drawCallback: function() {
          const d = this.api().rows({ search: 'applied' }).data().toArray();
          $id('fObat').textContent = rp(d.reduce((a, x) => a + x.obat, 0));
          $id('fTindakan').textContent = rp(d.reduce((a, x) => a + x.tindakan, 0));
          $id('fTotal').textContent = rp(d.reduce((a, x) => a + x.total, 0));
        }
      });
      let tCari;
      $id('rkCari').addEventListener('input', function() { clearTimeout(tCari); tCari = setTimeout(() => table.search(this.value).draw(), 200); });

      function bar(nama, nilai, maks, teks, kecil, warna) {
        const w = maks ? Math.max(2, Math.round(nilai / maks * 100)) : 0;
        return `<div class="rk-bar"><div class="nm" title="${esc(nama)}">${esc(nama)}${kecil ? ` <span class="kc">${kecil}</span>` : ''}</div><div class="vl">${teks}</div><div class="tr"><span style="width:${w}%;${warna ? 'background:' + warna : ''}"></span></div></div>`;
      }
      const daftar = (el, arr, total) => {
        const maks = Math.max(...arr.map(x => x.total), 1);
        $id(el).innerHTML = arr.length ? arr.map(x => bar(x.nama, x.total, maks, rp(x.total), `${ang(x.transaksi)} trx · ${pct(x.total, total)}%`)).join('') : '<div class="rk-kosong">Tidak ada data.</div>';
      };
      function isiOpsi(sel, items, nilai) {
        const ada = items.some(o => o.id === nilai);
        $id(sel).innerHTML = '<option value="">Semua</option>' + (nilai && !ada ? `<option value="${esc(nilai)}">${esc(nilai)}</option>` : '') + items.map(o => `<option value="${esc(o.id)}">${esc(o.nama)}</option>`).join('');
        $id(sel).value = nilai;
      }
      let grafik = null, terakhir = null, sibuk = false, poliDikenal = new Set();

      async function muat() {
        const d = $id('rkDari').value || HARI_INI, s = $id('rkSampai').value || d;
        if (s < d) return Swal.fire('Tanggal tidak valid', '"Sampai" tidak boleh sebelum "Dari".', 'warning');
        if ((new Date(s) - new Date(d)) / 864e5 > 365) return Swal.fire('Rentang terlalu panjang', 'Maksimal 366 hari.', 'warning');
        if (sibuk) return;
        sibuk = true;
        Object.assign(f, { from: d, to: s, dasar: $id('rkDasar').value });
        if (terakhir) Object.assign(f, { metode: $id('rkMetode').value, penjamin: $id('rkPenjamin').value, poli: $id('rkPoli').value });
        simpan(); tandai();
        $id('rkIsi').classList.add('rk-memuat');
        $id('btnTerapkan').innerHTML = '<i class="ti ti-loader-2 spin me-1"></i>Memuat';
        try {
          const r = await fetch(API + '?' + qs(), { credentials: 'same-origin' });
          const j = await r.json().catch(() => ({}));
          if (!r.ok || j.status !== 'success') throw new Error(j.message || 'Server tidak merespon (' + r.status + ').');
          terakhir = j;
          render(j);
          $id('rkUpdate').textContent = 'diperbarui ' + new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        } catch (e) {
          $id('rkUpdate').textContent = 'gagal memuat';
          Swal.fire('Gagal memuat laporan', e.message, 'error');
        } finally {
          sibuk = false;
          $id('rkIsi').classList.remove('rk-memuat');
          $id('btnTerapkan').innerHTML = '<i class="ti ti-filter me-1"></i>Terapkan';
        }
      }

      function render(j) {
        const k = j.ringkasan, p = j.periode;
        isiOpsi('rkMetode', j.opsi.metode.map(m => ({ id: m, nama: m })), f.metode);
        isiOpsi('rkPenjamin', j.opsi.penjamin, f.penjamin);
        j.poli.forEach(x => poliDikenal.add(x.nama));
        isiOpsi('rkPoli', [...poliDikenal].filter(x => x !== '-').sort().map(x => ({ id: x, nama: x })), f.poli);
        $id('rkPeriode').textContent = (p.from === p.to ? tglID(p.from) : `${tglID(p.from)} – ${tglID(p.to)}`) + (p.dasar === 'kunjungan' ? ' (tgl kunjungan)' : ' (tgl bayar)');
        $id('btnExcel').disabled = $id('btnCetak').disabled = !k.transaksi;

        $id('kTotal').textContent = rp(k.total);
        $id('kTotalD').textContent = k.kembalian ? `uang masuk ${rp(k.dibayar)} · kembalian ${rp(k.kembalian)}` : 'sesuai total tagihan lunas';
        $id('kTrx').textContent = ang(k.transaksi);
        $id('kTrxD').textContent = `${ang(k.berbayar)} berbayar · ${ang(k.tanpa_tagihan)} tanpa tagihan`;
        $id('kRata').textContent = rp(k.rata);
        $id('kRataD').textContent = `terbesar ${rp(k.terbesar)}`;
        $id('kTunai').textContent = `${pct(k.tunai, k.total)}% · ${pct(k.non_tunai, k.total)}%`;
        $id('kTunaiD').textContent = `tunai ${rp(k.tunai)} · non-tunai ${rp(k.non_tunai)}`;
        $id('kBelum').textContent = rp(j.belum_bayar.total);
        $id('kBelumD').textContent = `${ang(j.belum_bayar.kunjungan)} kunjungan selesai diperiksa, belum lunas`;

        // harian: batang bertumpuk tunai / non-tunai
        $id('harianSub').textContent = `${j.harian.length} hari · rata-rata ${rp(Math.round(k.total / Math.max(1, j.harian.length)))} per hari`;
        if (typeof Chart !== 'undefined') {
          if (grafik) grafik.destroy();
          const h = j.harian.length;
          grafik = new Chart($id('cHarian'), {
            type: 'bar',
            data: {
              labels: j.harian.map(x => { const [, m, d] = x.tanggal.split('-'); return h > 62 ? `${+d} ${BULAN[+m - 1]}` : `${+d}/${+m}`; }),
              datasets: [
                { label: 'Tunai', data: j.harian.map(x => x.tunai), backgroundColor: '#2a78d6', borderRadius: 3, maxBarThickness: 30 },
                { label: 'Non-tunai', data: j.harian.map(x => x.non_tunai), backgroundColor: '#eb6834', borderRadius: 3, maxBarThickness: 30 },
              ]
            },
            options: {
              responsive: true, maintainAspectRatio: false, animation: { duration: 250 }, interaction: { mode: 'index', intersect: false },
              scales: {
                x: { stacked: true, grid: { display: false }, ticks: { color: '#64748b', font: { size: 11 }, maxRotation: 0, autoSkip: true, maxTicksLimit: 16 }, border: { display: false } },
                y: { stacked: true, beginAtZero: true, grid: { color: '#eef1f5' }, border: { display: false }, ticks: { color: '#64748b', font: { size: 11 }, callback: v => v >= 1e6 ? (v / 1e6).toLocaleString('id-ID') + ' jt' : v >= 1e3 ? (v / 1e3).toLocaleString('id-ID') + ' rb' : v } }
              },
              plugins: { legend: { display: false }, tooltip: { callbacks: {
                title: it => tglID(j.harian[it[0].dataIndex].tanggal),
                label: c => `${c.dataset.label}: ${rp(c.raw)}`,
                footer: it => { const x = j.harian[it[0].dataIndex]; return `Total ${rp(x.total)} · ${ang(x.transaksi)} transaksi`; }
              } } }
            }
          });
        }

        daftar('lMetode', j.metode, k.total);
        const kom = k.obat + k.tindakan;
        $id('lKomposisi').innerHTML = kom ? bar('Obat & BMHP', k.obat, kom, rp(k.obat), pct(k.obat, kom) + '%', '#2a78d6') + bar('Tindakan & administrasi', k.tindakan, kom, rp(k.tindakan), pct(k.tindakan, kom) + '%', '#eb6834') : '<div class="rk-kosong">Tidak ada rincian.</div>';
        daftar('lPenjamin', j.penjamin, k.total);
        daftar('lPoli', j.poli, k.total);

        $id('detailSub').textContent = `${ang(j.data.length)} transaksi lunas`;
        table.clear().rows.add(j.data).draw();
      }

      // ---------- export & cetak ----------
      $id('btnExcel').addEventListener('click', function() {
        const btn = this, token = Date.now().toString(36), label = btn.innerHTML;
        btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyiapkan…';
        const selesai = () => { clearInterval(cek); btn.innerHTML = label; btn.disabled = false; };
        const cek = setInterval(() => { if (document.cookie.includes('rk_unduh=' + token)) selesai(); }, 500);
        setTimeout(selesai, 120000);
        const ifr = document.createElement('iframe');
        ifr.hidden = true;
        ifr.addEventListener('load', () => {   // file terunduh tidak memicu load; bila terpicu = halaman error
          let pesan = ''; try { pesan = (ifr.contentDocument.body.innerText || '').trim().slice(0, 300); } catch (e) {}
          if (document.cookie.includes('rk_unduh=' + token)) return;
          selesai(); Swal.fire('Export gagal', pesan || 'Server tidak mengirim file Excel.', 'error');
        });
        ifr.src = 'module/report/export_kasir.php?' + qs({ token });
        document.body.appendChild(ifr);
        setTimeout(() => ifr.remove(), 130000);
      });
      $id('btnCetak').addEventListener('click', () => window.open('module/report/print_kasir.php?' + qs(), '_blank'));

      // ---------- event ----------
      document.querySelectorAll('#rkCepat .btn').forEach(b => b.addEventListener('click', () => { const r = rentang(b.dataset.r); $id('rkDari').value = r[0]; $id('rkSampai').value = r[1]; muat(); }));
      $id('btnTerapkan').addEventListener('click', muat);
      ['rkDasar', 'rkMetode', 'rkPenjamin', 'rkPoli'].forEach(i => $id(i).addEventListener('change', muat));
      ['rkDari', 'rkSampai'].forEach(i => $id(i).addEventListener('keydown', e => { if (e.key === 'Enter') muat(); }));
      $id('btnReset').addEventListener('click', () => {
        $id('rkDari').value = $id('rkSampai').value = HARI_INI; $id('rkDasar').value = 'bayar';
        ['rkMetode', 'rkPenjamin', 'rkPoli'].forEach(i => { $id(i).value = ''; });
        f.metode = f.penjamin = f.poli = '';
        muat();
      });

      tandai();
      muat();
    })();
  </script>
</body>

</html>
