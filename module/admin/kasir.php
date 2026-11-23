<?php
/**
 * Kasir: daftar pasien yang pemeriksaannya sudah selesai, belum / sudah bayar.
 * Data: controller/visit/kasirList (GET per rentang tanggal kunjungan); tab, filter & cari di klien.
 */
$title = 'Kasir';
require '../../controller/view.php';
session_write_close();
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    @keyframes ks-spin { to { transform: rotate(360deg); } }
    .icon-spin { display: inline-block; animation: ks-spin 1s linear infinite; }
    .ks-icon { width: 40px; height: 40px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
    .ks-kpi { border: 1px solid #e5eaef; border-radius: 12px; background: #fff; padding: 12px 14px; display: flex; align-items: center; gap: 12px; height: 100%; cursor: pointer; transition: border-color .15s, box-shadow .15s; }
    .ks-kpi:hover { border-color: #5d87ff; box-shadow: 0 4px 14px rgba(93, 135, 255, .12); }
    .ks-kpi .n { font-size: 1.3rem; font-weight: 700; line-height: 1.1; color: #111827; }
    .ks-kpi .l { font-size: .78rem; color: #475569; }

    .ks-tabs .nav-link { color: #475569; font-weight: 600; border: 0; border-bottom: 2px solid transparent; border-radius: 0; padding: .55rem .9rem; }
    .ks-tabs .nav-link.active { color: #5d87ff; border-bottom-color: #5d87ff; background: transparent; }
    .ks-tabs .badge { font-size: .7rem; }
    .ks-filter .form-label { font-size: .75rem; color: #475569; margin-bottom: 2px; }
    .ks-filter .form-control, .ks-filter .form-select { font-size: .85rem; }
    .ks-chip { border: 1px solid #e5eaef; background: #fff; color: #334155; border-radius: 999px; padding: 3px 12px; font-size: .78rem; font-weight: 600; }
    .ks-chip.active { background: #5d87ff; border-color: #5d87ff; color: #fff; }
    .ks-chip .n { opacity: .8; margin-left: 4px; }
    .ks-cepat .btn { font-size: .78rem; padding: 4px 10px; background: #fff; border: 1px solid #cbd5e1; color: #1f2937; font-weight: 600; }
    .ks-cepat .btn:hover { background: #f1f5f9; color: #111827; }
    .ks-cepat .btn.active, .ks-cepat .btn.active:hover { background: #1e293b; border-color: #1e293b; color: #fff; }

    #tblKasir { font-size: .86rem; }
    #tblKasir thead th { font-size: .75rem; text-transform: uppercase; letter-spacing: .03em; color: #475569; font-weight: 600; white-space: nowrap; background: #f8fafc; }
    #tblKasir td, #tblKasir th { padding-left: .55rem; padding-right: .55rem; }
    #tblKasir td { vertical-align: middle; white-space: nowrap; padding-top: .55rem; padding-bottom: .55rem; }
    .ks-nama { font-weight: 600; color: #111827; min-width: 170px; max-width: 260px; white-space: normal; line-height: 1.3; }
    .ks-sub { font-size: .76rem; color: #475569; }
    .ks-sub .sep { color: #cbd5e1; margin: 0 4px; }
    .ks-wrap { min-width: 140px; max-width: 220px; white-space: normal; line-height: 1.3; }
    .ks-tebal { font-weight: 600; color: #111827; }
    .ks-rp { font-weight: 700; color: #111827; font-variant-numeric: tabular-nums; }
    .ks-rp.nol { color: #64748b; font-weight: 600; }
    .ks-aksi { display: flex; gap: 6px; }
    .ks-b { font-size: .78rem; font-weight: 600; padding: 4px 10px; border-radius: 6px; border: 1px solid; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap; }
    .ks-b-bayar { background: #2563eb; border-color: #2563eb; color: #fff; }
    .ks-b-bayar:hover { background: #1d4ed8; color: #fff; }
    .ks-b-invoice { background: #1e293b; border-color: #1e293b; color: #fff; }
    .ks-b-invoice:hover { background: #0f172a; color: #fff; }
    .ks-b-detail { background: #fff; border-color: #334155; color: #1f2937; }
    .ks-b-detail:hover { background: #1e293b; color: #fff; }
    .badge.bg-warning-subtle { color: #1f2937 !important; background-color: #fef3c7 !important; }
    .ks-kosong { padding: 36px 12px; text-align: center; color: #475569; }
  </style>
</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <?php require 'sidebar.php'; ?>
    <div class="body-wrapper">
      <?php require 'navbar.php'; ?>
      <div class="body-wrapper-inner">
        <div class="container-fluid">

          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-3">
              <div class="d-flex flex-wrap align-items-center gap-3">
                <span class="ks-icon bg-primary-subtle text-primary"><i class="ti ti-wallet"></i></span>
                <div class="flex-grow-1" style="min-width:0">
                  <h5 class="fw-semibold mb-0">Kasir</h5>
                  <div class="text-muted small"><span id="ksRentang">hari ini</span> · <span id="ksUpdate">memuat…</span></div>
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm" id="btnMuatUlang"><i class="ti ti-refresh me-1"></i>Muat ulang</button>
              </div>
              <div class="small text-muted mt-2"><i class="ti ti-info-circle me-1"></i>Pasien muncul di sini setelah dokter menekan <b>Selesai Pemeriksaan</b>. Tagihan = obat + tindakan/jasa (sama dengan halaman pembayaran).</div>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-6 col-xl-3">
              <div class="ks-kpi" data-tab="belum" data-st="semua">
                <span class="ks-icon bg-danger-subtle text-danger"><i class="ti ti-hourglass"></i></span>
                <div><div class="n" id="kBelum">-</div><div class="l">Belum bayar</div></div>
              </div>
            </div>
            <div class="col-6 col-xl-3">
              <div class="ks-kpi" data-tab="belum" data-st="tagihan">
                <span class="ks-icon bg-warning-subtle" style="color:#b45309"><i class="ti ti-receipt"></i></span>
                <div><div class="n" id="kTagihan">-</div><div class="l">Tagihan belum dibayar <span id="kTagihanN"></span></div></div>
              </div>
            </div>
            <div class="col-6 col-xl-3">
              <div class="ks-kpi" data-tab="lunas" data-st="semua">
                <span class="ks-icon bg-success-subtle text-success"><i class="ti ti-circle-check"></i></span>
                <div><div class="n" id="kLunas">-</div><div class="l">Sudah bayar</div></div>
              </div>
            </div>
            <div class="col-6 col-xl-3">
              <div class="ks-kpi" data-tab="lunas" data-st="semua">
                <span class="ks-icon" style="background:#dcfce7;color:#166534"><i class="ti ti-cash"></i></span>
                <div><div class="n" id="kDiterima">-</div><div class="l">Pembayaran diterima</div></div>
              </div>
            </div>
          </div>

          <div class="card border-0 shadow-sm">
            <div class="card-body p-3 p-md-4">
              <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-2">
                <ul class="nav ks-tabs" id="ksTabs">
                  <li class="nav-item"><button class="nav-link active" type="button" data-tab="belum">Belum bayar <span class="badge rounded-pill bg-danger ms-1" id="tBelum">0</span></button></li>
                  <li class="nav-item"><button class="nav-link" type="button" data-tab="lunas">Sudah bayar <span class="badge rounded-pill bg-success ms-1" id="tLunas">0</span></button></li>
                </ul>
                <div class="ks-filter d-flex flex-wrap align-items-end gap-2">
                  <div>
                    <label class="form-label d-block">Periode kunjungan</label>
                    <div class="btn-group ks-cepat" id="ksCepat">
                      <button type="button" class="btn" data-r="hari">Hari ini</button>
                      <button type="button" class="btn" data-r="kemarin">Kemarin</button>
                      <button type="button" class="btn" data-r="7">7 hari</button>
                      <button type="button" class="btn" data-r="bulan">Bulan ini</button>
                    </div>
                  </div>
                  <div>
                    <label for="fromDate" class="form-label">Dari</label>
                    <input type="date" id="fromDate" class="form-control form-control-sm">
                  </div>
                  <div>
                    <label for="toDate" class="form-label">Sampai</label>
                    <input type="date" id="toDate" class="form-control form-control-sm">
                  </div>
                  <button type="button" id="btnFilter" class="btn btn-dark btn-sm"><i class="ti ti-filter me-1"></i>Terapkan</button>
                </div>
              </div>

              <div class="ks-filter d-flex flex-wrap align-items-end gap-2 mb-3">
                <div>
                  <label for="ksCari" class="form-label">Cari</label>
                  <input type="search" id="ksCari" class="form-control form-control-sm" placeholder="Nama / No. RM / no. transaksi" style="width:230px">
                </div>
                <div>
                  <label for="fBayar" class="form-label">Jenis bayar</label>
                  <select id="fBayar" class="form-select form-select-sm" style="width:170px"><option value="">Semua</option></select>
                </div>
                <div>
                  <label for="fDokter" class="form-label">Dokter</label>
                  <select id="fDokter" class="form-select form-select-sm" style="width:210px"><option value="">Semua</option></select>
                </div>
                <div>
                  <label for="fPoli" class="form-label">Poli</label>
                  <select id="fPoli" class="form-select form-select-sm" style="width:170px"><option value="">Semua</option></select>
                </div>
                <button type="button" id="btnReset" class="btn btn-light btn-sm" title="Kembalikan semua filter"><i class="ti ti-arrow-back-up me-1"></i>Reset filter</button>
              </div>

              <div id="ksChipsWrap">
                <div class="d-flex flex-wrap gap-2 mb-3" id="ksChips">
                  <button type="button" class="ks-chip active" data-st="semua">Semua<span class="n" id="cSemua"></span></button>
                  <button type="button" class="ks-chip" data-st="tagihan">Ada tagihan<span class="n" id="cTagihan"></span></button>
                  <button type="button" class="ks-chip" data-st="nol">Tagihan Rp0<span class="n" id="cNol"></span></button>
                </div>
              </div>

              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 w-100" id="tblKasir">
                  <thead>
                    <tr>
                      <th style="width:1%">Aksi</th>
                      <th>Pasien</th>
                      <th>Kunjungan</th>
                      <th>Dokter / Poli</th>
                      <th>Jenis bayar</th>
                      <th class="text-end">Tagihan</th>
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
      const API = 'controller/visit/kasirList';
      const KUNCI = 'kasir.filter';
      const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      const HARI_INI = new Date().toLocaleDateString('sv-SE', { timeZone: 'Asia/Jakarta' });
      const geser = (ymd, n) => { const d = new Date(ymd + 'T12:00:00'); d.setDate(d.getDate() + n); return d.toISOString().slice(0, 10); };
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const tglID = d => { if (!d) return '-'; const [y, m, h] = String(d).slice(0, 10).split('-'); return `${+h} ${BULAN[+m - 1]} ${y}`; };
      const rp = n => 'Rp' + Number(n || 0).toLocaleString('id-ID');
      const rpPendek = n => n >= 1e6 ? 'Rp' + (n / 1e6).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' jt' : rp(n);
      const linkNo = r => `no=${encodeURIComponent(r.visit_ID)}&rm=${encodeURIComponent(r.nomor_rm || '')}`;

      let f = { tab: 'belum', st: 'semua', dari: HARI_INI, sampai: HARI_INI, bayar: '', dokter: '', poli: '', cari: '' };
      try {
        const s = JSON.parse(localStorage.getItem(KUNCI) || 'null');
        if (s && typeof s === 'object') { Object.assign(f, s.f || {}); if (s.hari !== HARI_INI) { f.dari = HARI_INI; f.sampai = HARI_INI; } }
      } catch (e) {}
      if (!['belum', 'lunas'].includes(f.tab)) f.tab = 'belum';
      const simpan = () => { try { localStorage.setItem(KUNCI, JSON.stringify({ hari: HARI_INI, f })); } catch (e) {} };

      let semua = [];
      let sibuk = false;
      $('#fromDate, #toDate').attr('max', HARI_INI);
      $('#fromDate').val(f.dari);
      $('#toDate').val(f.sampai);
      $('#ksCari').val(f.cari);

      $.fn.dataTable.ext.search.push((s, _d, i) => {
        if (s.nTable.id !== 'tblKasir' || f.tab !== 'belum' || f.st === 'semua') return true;
        const r = s.aoData[i]._aData;
        return f.st === 'tagihan' ? r.tagihan > 0 : r.tagihan === 0;
      });

      const table = $('#tblKasir').DataTable({
        data: [],
        deferRender: true,
        order: [],
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        dom: 'rt<"d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3"ilp>',
        language: {
          thousands: '.',
          info: '_START_–_END_ dari _TOTAL_ pasien',
          infoEmpty: '0 pasien',
          infoFiltered: '(disaring dari _MAX_)',
          lengthMenu: '_MENU_ / halaman',
          zeroRecords: '<div class="ks-kosong"><i class="ti ti-search fs-6 d-block mb-1"></i>Tidak ada pasien yang cocok dengan filter.</div>',
          emptyTable: '<div class="ks-kosong"><i class="ti ti-mood-empty fs-6 d-block mb-1"></i>Tidak ada pasien pada periode ini.</div>',
          paginate: { previous: '‹', next: '›' },
        },
        columns: [
          {
            data: null, orderable: false,
            render: (r, t) => t !== 'display' ? '' : (r.lunas
              ? `<div class="ks-aksi"><a class="ks-b ks-b-invoice" target="_blank" href="module/print/struk_billing?${linkNo(r)}"><i class="ti ti-printer"></i>Invoice</a>` +
                `<a class="ks-b ks-b-detail" href="module/admin/kasir_detail?${linkNo(r)}" title="Lihat rincian pembayaran"><i class="ti ti-file-text"></i></a></div>`
              : `<a class="ks-b ks-b-bayar" href="module/admin/kasir_detail?${linkNo(r)}"><i class="ti ti-cash"></i>Bayar</a>`)
          },
          {
            data: null,
            render: (r, t) => {
              if (t !== 'display') return `${r.patient_name || ''} ${r.nomor_rm || ''} ${r.nomor_transaksi || ''}`;
              const umur = r.umur !== null && r.umur !== undefined ? `${r.umur} th` : '';
              const info = [r.nomor_rm ? 'RM ' + esc(r.nomor_rm) : '', [esc(r.patient_gender || ''), umur].filter(Boolean).join(', ')].filter(Boolean).join('<span class="sep">|</span>');
              return `<div class="ks-nama">${esc(r.patient_name || '(tanpa nama)')}</div><div class="ks-sub">${info || '-'}</div>`;
            }
          },
          {
            data: null,
            render: (r, t) => {
              if (t !== 'display') return `${r.visit_date || ''} ${r.visit_time || ''}`;
              const jam = String(r.visit_time || '').slice(0, 5);
              return `<div class="ks-tebal">${String(r.visit_date).slice(0, 10) === HARI_INI ? 'Hari ini' : tglID(r.visit_date)}</div><div class="ks-sub">${esc(jam || '-')}</div>` +
                (r.rawat_inap ? '<div class="mt-1"><span class="badge bg-warning-subtle">Rawat inap</span></div>' : '');
            }
          },
          {
            data: null,
            render: (r, t) => t !== 'display' ? `${r.dokter || ''} ${r.poli || ''}` :
              `<div class="ks-wrap"><div>${esc(r.dokter || '-')}</div><div class="ks-sub">${esc(r.poli || '-')}</div></div>`
          },
          {
            data: null,
            render: (r, t) => {
              if (t !== 'display') return r.provider_name || '';
              const bpjs = r.id_provider === 1;
              return `<span class="badge ${bpjs ? 'bg-success-subtle text-success' : 'bg-light text-dark border'}">${esc(r.provider_name || '-')}</span>`;
            }
          },
          {
            data: null, className: 'text-end',
            render: (r, t) => {
              if (t !== 'display') return r.lunas ? r.dibayar : r.tagihan;
              if (r.lunas) {
                return `<div class="ks-rp">${rp(r.dibayar)}</div><div class="ks-sub"><span class="badge bg-success-subtle text-success">Lunas</span> ${esc(r.metode_bayar || '')}</div>` +
                  (r.nomor_transaksi ? `<div class="ks-sub">No. ${esc(r.nomor_transaksi)}</div>` : '');
              }
              const rinci = r.tagihan > 0 ? `<div class="ks-sub">Obat ${rp(r.obat)} · Jasa ${rp(r.jasa)}</div>` : '<div class="ks-sub">belum ada tagihan</div>';
              return `<div class="ks-rp ${r.tagihan > 0 ? '' : 'nol'}">${rp(r.tagihan)}</div>${rinci}`;
            }
          },
        ],
      });

      function cocok(r) {
        if (f.bayar && String(r.provider_name || '') !== f.bayar) return false;
        if (f.dokter && String(r.dokter || '') !== f.dokter) return false;
        if (f.poli && String(r.poli || '') !== f.poli) return false;
        return true;
      }

      function isiPilihan(sel, daftarMentah, nilai) {
        const daftar = [...new Set(daftarMentah.map(v => String(v || '')).filter(Boolean))].sort((a, b) => a.localeCompare(b, 'id'));
        if (nilai && !daftar.includes(nilai)) daftar.unshift(nilai);
        $(sel).html('<option value="">Semua</option>' + daftar.map(v => `<option value="${esc(v)}">${esc(v)}</option>`).join('')).val(nilai);
      }

      function tampil() {
        const lolos = semua.filter(cocok);
        const belum = lolos.filter(r => !r.lunas);
        const lunas = lolos.filter(r => r.lunas);
        const ada = belum.filter(r => r.tagihan > 0);
        $('#kBelum, #tBelum, #cSemua').text(belum.length);
        $('#kTagihan').text(rpPendek(ada.reduce((a, r) => a + r.tagihan, 0)));
        $('#kTagihanN').text(`(${ada.length} pasien)`);
        $('#cTagihan').text(ada.length);
        $('#cNol').text(belum.length - ada.length);
        $('#kLunas, #tLunas').text(lunas.length);
        $('#kDiterima').text(rpPendek(lunas.reduce((a, r) => a + r.dibayar, 0)));
        $('#ksChipsWrap').toggle(f.tab === 'belum');
        $('#ksTabs .nav-link').removeClass('active').filter(`[data-tab="${f.tab}"]`).addClass('active');
        $('#ksChips .ks-chip').removeClass('active').filter(`[data-st="${f.st}"]`).addClass('active');
        table.clear().rows.add(f.tab === 'lunas' ? lunas : belum).draw(false);
      }

      function tandaiCepat() {
        const peta = { hari: [HARI_INI, HARI_INI], kemarin: [geser(HARI_INI, -1), geser(HARI_INI, -1)], 7: [geser(HARI_INI, -6), HARI_INI], bulan: [HARI_INI.slice(0, 8) + '01', HARI_INI] };
        $('#ksCepat .btn').each(function() { const p = peta[this.dataset.r]; $(this).toggleClass('active', p[0] === f.dari && p[1] === f.sampai); });
        return peta;
      }

      function muat(opsi = {}) {
        const d = $('#fromDate').val() || HARI_INI;
        const s = $('#toDate').val() || d;
        if (s < d) { Swal.fire({ icon: 'warning', title: 'Tanggal tidak valid', text: '"Sampai" tidak boleh sebelum "Dari".' }); return; }
        if ((new Date(s) - new Date(d)) / 864e5 > 62) { Swal.fire({ icon: 'warning', title: 'Rentang terlalu panjang', text: 'Maksimal 62 hari.' }); return; }
        if (sibuk) return;
        sibuk = true;
        $('#fromDate').val(d); $('#toDate').val(s);
        f.dari = d; f.sampai = s; simpan(); tandaiCepat();
        if (!opsi.diam) $('#btnMuatUlang i').attr('class', 'ti ti-loader-2 icon-spin me-1');
        $.ajax({ url: API, dataType: 'json', data: { fromDate: d, toDate: s } })
          .done(res => {
            semua = res.data || [];
            isiPilihan('#fBayar', semua.map(r => r.provider_name), f.bayar);
            isiPilihan('#fDokter', semua.map(r => r.dokter), f.dokter);
            isiPilihan('#fPoli', semua.map(r => r.poli), f.poli);
            tampil();
            $('#ksRentang').text(d === s ? (d === HARI_INI ? 'hari ini' : tglID(d)) : `${tglID(d)} – ${tglID(s)}`);
            $('#ksUpdate').text('diperbarui ' + new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }));
          })
          .fail(x => {
            if (opsi.diam) { $('#ksUpdate').text('gagal memperbarui'); return; }
            Swal.fire({ icon: 'error', title: 'Gagal memuat', text: x.responseJSON?.message || 'Server tidak merespon.' });
          })
          .always(() => { sibuk = false; $('#btnMuatUlang i').attr('class', 'ti ti-refresh me-1'); });
      }

      const gantiTab = (tab, st) => { f.tab = tab; f.st = st || 'semua'; simpan(); tampil(); };
      $('#ksTabs').on('click', '.nav-link', function() { gantiTab(this.dataset.tab); });
      $('.ks-kpi').on('click', function() { gantiTab(this.dataset.tab, this.dataset.st); });
      $('#ksChips').on('click', '.ks-chip', function() { f.st = this.dataset.st; simpan(); tampil(); });
      $('#ksCepat').on('click', '.btn', function() { const p = tandaiCepat()[this.dataset.r]; $('#fromDate').val(p[0]); $('#toDate').val(p[1]); muat(); });
      $('#btnFilter').on('click', () => muat());
      $('#fromDate, #toDate').on('keydown', e => { if (e.key === 'Enter') muat(); });
      $('#btnMuatUlang').on('click', () => muat());
      $('#fBayar, #fDokter, #fPoli').on('change', function() {
        f[{ fBayar: 'bayar', fDokter: 'dokter', fPoli: 'poli' }[this.id]] = this.value;
        simpan(); tampil();
      });
      let tCari;
      $('#ksCari').on('input', function() { clearTimeout(tCari); tCari = setTimeout(() => { f.cari = this.value; simpan(); table.search(this.value).draw(); }, 200); });
      $('#btnReset').on('click', () => {
        f = { tab: f.tab, st: 'semua', dari: HARI_INI, sampai: HARI_INI, bayar: '', dokter: '', poli: '', cari: '' };
        $('#fromDate, #toDate').val(HARI_INI);
        $('#ksCari').val('');
        table.search('');
        muat();
      });

      setInterval(() => { if (!document.hidden && !Swal.isVisible() && f.sampai === HARI_INI) muat({ diam: true }); }, 60000);
      document.addEventListener('visibilitychange', () => { if (!document.hidden && f.sampai === HARI_INI) muat({ diam: true }); });

      table.search(f.cari);
      muat();
    })();
  </script>
</body>

</html>
