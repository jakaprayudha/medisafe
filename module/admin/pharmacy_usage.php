<?php
$title = 'Pemakaian Obat Pasien';
require '../../controller/view.php';
$selectedIdPharmacy = (int) ($_GET['id_pharmacy'] ?? 0);
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .min-w-0 {
      min-width: 0;
    }

    .pu-stat {
      border: 1px solid #e5eaef;
      border-radius: 12px;
      background: #fff;
      padding: 14px 16px;
      height: 100%;
    }

    .pu-icon {
      width: 40px;
      height: 40px;
      border-radius: 10px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 1.05rem;
      flex-shrink: 0;
    }

    .pu-val {
      font-size: 1.35rem;
      font-weight: 700;
      line-height: 1.2;
      color: #2a3547;
    }

    .pu-label {
      font-size: .75rem;
      color: #6c757d;
    }

    .pu-period .btn {
      border-radius: 20px;
      font-size: .78rem;
      padding: .28rem .8rem;
    }

    .pu-dates input {
      width: 145px;
    }

    .pu-obat {
      border: 1px solid #d0deff;
      border-radius: 14px;
      background: linear-gradient(120deg, #f0f4ff 0%, #fff 70%);
      padding: 14px 18px;
    }

    .pu-obat .ava {
      width: 46px;
      height: 46px;
      border-radius: 12px;
      background: #5d87ff;
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem;
      flex-shrink: 0;
    }

    .pu-badge {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      font-size: .7rem;
      font-weight: 600;
      padding: .22em .6em;
      border-radius: 6px;
      white-space: nowrap;
    }

    .pu-tabs {
      border-bottom: 1px solid #e5eaef;
      gap: 4px;
    }

    .pu-tabs .nav-link {
      border: 0;
      border-bottom: 2px solid transparent;
      border-radius: 0;
      color: #5a6a85;
      font-weight: 600;
      font-size: .875rem;
      padding: .7rem 1rem;
      background: none;
    }

    .pu-tabs .nav-link.active {
      color: #5d87ff;
      border-bottom-color: #5d87ff;
    }

    .pu-table {
      width: 100% !important;
    }

    .pu-table thead th {
      font-size: .72rem;
      font-weight: 600;
      color: #5a6a85;
      text-transform: uppercase;
      letter-spacing: .3px;
      background: #f6f9fc;
      border-bottom: 0;
      white-space: nowrap;
    }

    .pu-table td {
      font-size: .84rem;
      vertical-align: middle;
      padding-top: .55rem;
      padding-bottom: .55rem;
    }

    .pu-sub {
      font-size: .73rem;
      color: #7c8fac;
    }

    .pu-rm {
      font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
      font-size: .72rem;
      background: #f2f5f9;
      color: #2a3547;
      padding: .05em .4em;
      border-radius: 5px;
    }

    .pu-name {
      font-weight: 600;
      color: #2a3547;
      max-width: 240px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .pu-qty {
      font-weight: 700;
      color: #5d87ff;
    }

    .pu-bar {
      height: 6px;
      border-radius: 6px;
      background: #eef2f6;
      overflow: hidden;
      min-width: 80px;
    }

    .pu-bar span {
      display: block;
      height: 100%;
      background: #5d87ff;
      border-radius: 6px;
    }

    .pu-rank {
      width: 26px;
      height: 26px;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: .72rem;
      font-weight: 700;
      background: #f2f5f9;
      color: #5a6a85;
    }

    .pu-rank.top {
      background: #fff4e5;
      color: #e08a00;
    }

    #tblRekap tbody tr {
      cursor: pointer;
    }

    #tblRekap tbody tr:hover {
      background: #f6f9ff;
    }

    .pu-loading {
      position: absolute;
      inset: 0;
      background: rgba(255, 255, 255, .65);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 5;
      border-radius: 12px;
    }

    #selectObat+.select2 .select2-selection--single {
      height: 40px !important;
      border-color: #dfe5ef !important;
      border-radius: 8px !important;
      display: flex;
      align-items: center;
    }

    #selectObat+.select2 .select2-selection__arrow {
      height: 38px !important;
    }

    .dt-field>span {
      display: block;
      font-size: .7rem;
      color: #7c8fac;
      text-transform: uppercase;
      letter-spacing: .3px;
    }

    .dt-field>div {
      font-weight: 600;
      color: #2a3547;
      font-size: .875rem;
    }

    @media print {

      .left-sidebar,
      .app-header,
      .no-print,
      .dataTables_paginate,
      .dataTables_length {
        display: none !important;
      }

      .body-wrapper {
        margin-left: 0 !important;
        padding-top: 0 !important;
      }

      .tab-pane {
        display: none !important;
      }

      .tab-pane.active {
        display: block !important;
        opacity: 1 !important;
      }
    }
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

          <!-- HEADER -->
          <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
            <div>
              <h4 class="fw-semibold mb-1">Pemakaian Obat Pasien</h4>
              <div class="text-muted small">Lihat obat yang paling banyak diresepkan dan pasien yang menerima obat tertentu.</div>
            </div>
            <div class="d-flex gap-2 no-print">
              <button class="btn btn-light" id="btnRefresh" title="Muat ulang"><i class="fas fa-sync-alt"></i></button>
              <button class="btn btn-outline-secondary" id="btnPrint"><i class="fas fa-print me-1"></i> Cetak</button>
            </div>
          </div>

          <!-- FILTER UTAMA -->
          <div class="card mb-3 no-print">
            <div class="card-body p-3">
              <div class="row g-3 align-items-end">
                <div class="col-lg-5">
                  <label class="form-label small fw-semibold mb-1" for="selectObat">Obat</label>
                  <select id="selectObat" class="form-select">
                    <option value="0">Semua obat</option>
                  </select>
                </div>
                <div class="col-lg-7">
                  <label class="form-label small fw-semibold mb-1">Periode resep</label>
                  <div class="d-flex flex-wrap align-items-center gap-2">
                    <div class="pu-period d-flex flex-wrap gap-1" id="puPeriod">
                      <button class="btn btn-light" data-p="hari">Hari ini</button>
                      <button class="btn btn-light" data-p="7">7 hari</button>
                      <button class="btn btn-primary" data-p="bulan">Bulan ini</button>
                      <button class="btn btn-light" data-p="bulanlalu">Bulan lalu</button>
                      <button class="btn btn-light" data-p="90">3 bulan</button>
                    </div>
                    <div class="pu-dates d-flex align-items-center gap-1">
                      <input type="date" id="fromDate" class="form-control form-control-sm">
                      <span class="text-muted small">s.d.</span>
                      <input type="date" id="toDate" class="form-control form-control-sm">
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- OBAT TERPILIH -->
          <div class="pu-obat mb-3 d-none" id="puObat">
            <div class="d-flex flex-wrap align-items-center gap-3">
              <div class="ava"><i class="fas fa-pills"></i></div>
              <div class="flex-grow-1 min-w-0">
                <div class="d-flex flex-wrap gap-1 mb-1" id="puObatBadges"></div>
                <div class="fw-bold fs-5 text-dark" id="puObatNama"></div>
                <div class="pu-sub" id="puObatSub"></div>
              </div>
              <div class="d-flex gap-2 no-print">
                <a class="btn btn-sm btn-light" id="puObatDetail" href="#"><i class="fas fa-pen me-1"></i>Detail obat</a>
                <button class="btn btn-sm btn-outline-secondary" id="btnSemuaObat"><i class="fas fa-times me-1"></i>Semua obat</button>
              </div>
            </div>
          </div>

          <!-- STAT -->
          <div class="row g-3 mb-3">
            <div class="col-6 col-xl-3">
              <div class="pu-stat d-flex gap-3 align-items-center">
                <span class="pu-icon bg-primary-subtle text-primary"><i class="fas fa-user-injured"></i></span>
                <div class="min-w-0">
                  <div class="pu-val" id="stKunjungan">-</div>
                  <div class="pu-label">Kunjungan pasien</div>
                </div>
              </div>
            </div>
            <div class="col-6 col-xl-3">
              <div class="pu-stat d-flex gap-3 align-items-center">
                <span class="pu-icon bg-warning-subtle text-warning"><i class="fas fa-file-prescription"></i></span>
                <div class="min-w-0">
                  <div class="pu-val" id="stResep">-</div>
                  <div class="pu-label" id="stResepLbl">Resep</div>
                </div>
              </div>
            </div>
            <div class="col-6 col-xl-3">
              <div class="pu-stat d-flex gap-3 align-items-center">
                <span class="pu-icon bg-info-subtle text-info"><i class="fas fa-box-open"></i></span>
                <div class="min-w-0">
                  <div class="pu-val" id="stQty">-</div>
                  <div class="pu-label" id="stQtyLbl">Jumlah obat keluar</div>
                </div>
              </div>
            </div>
            <div class="col-6 col-xl-3">
              <div class="pu-stat d-flex gap-3 align-items-center">
                <span class="pu-icon bg-success-subtle text-success"><i class="fas fa-wallet"></i></span>
                <div class="min-w-0">
                  <div class="pu-val" id="stNominal" style="font-size:1.15rem">-</div>
                  <div class="pu-label">Estimasi nilai obat</div>
                </div>
              </div>
            </div>
          </div>

          <!-- KONTEN -->
          <div class="card position-relative">
            <div class="pu-loading" id="puLoading"><div class="spinner-border text-primary"></div></div>
            <div class="card-body p-0">
              <ul class="nav pu-tabs px-3 no-print" role="tablist">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabRekap" type="button" id="tabRekapBtn"><i class="fas fa-chart-bar me-1"></i>Rekap per Obat</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabDaftar" type="button" id="tabDaftarBtn"><i class="fas fa-list me-1"></i>Daftar Pemakaian</button></li>
              </ul>
              <div class="tab-content p-3 p-md-4">
                <!-- REKAP -->
                <div class="tab-pane fade show active" id="tabRekap" role="tabpanel">
                  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <div class="small text-muted" id="rekapInfo">Klik obat untuk melihat daftar pasien penerimanya.</div>
                    <div class="input-group input-group-sm no-print" style="width:240px">
                      <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                      <input type="search" id="rekapCari" class="form-control" placeholder="Cari obat di rekap">
                    </div>
                  </div>
                  <div class="table-responsive">
                    <table class="table align-middle mb-0 pu-table" id="tblRekap">
                      <thead>
                        <tr>
                          <th style="width:44px">#</th>
                          <th>Obat</th>
                          <th>Golongan</th>
                          <th class="text-end">Kunjungan</th>
                          <th style="min-width:180px">Jumlah keluar</th>
                          <th class="text-end">Estimasi nilai</th>
                        </tr>
                      </thead>
                      <tbody></tbody>
                    </table>
                  </div>
                </div>

                <!-- DAFTAR -->
                <div class="tab-pane fade" id="tabDaftar" role="tabpanel">
                  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 no-print">
                    <div class="d-flex flex-wrap gap-2">
                      <select id="fStatus" class="form-select form-select-sm" style="width:auto">
                        <option value="">Semua status</option>
                        <option value="3">Selesai</option>
                        <option value="2">Persiapan</option>
                        <option value="1">Sudah dikirim</option>
                        <option value="0">Belum dikirim</option>
                      </select>
                      <select id="fLayanan" class="form-select form-select-sm" style="width:auto">
                        <option value="">Semua layanan</option>
                        <option value="Poliklinik">Poliklinik</option>
                        <option value="Rawat Inap">Rawat Inap</option>
                        <option value="UGD">UGD</option>
                      </select>
                    </div>
                    <div class="input-group input-group-sm" style="width:300px;max-width:100%">
                      <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                      <input type="search" id="fCari" class="form-control" placeholder="Nama pasien, No. RM, dokter, no. resep">
                    </div>
                  </div>
                  <div class="table-responsive">
                    <table class="table align-middle mb-0 pu-table" id="tblDaftar">
                      <thead>
                        <tr>
                          <th>Tanggal</th>
                          <th>Pasien</th>
                          <th class="col-obat">Obat</th>
                          <th>Jumlah &amp; signa</th>
                          <th>Dokter / poli</th>
                          <th class="text-center">Status</th>
                          <th class="text-center no-print" style="width:56px"></th>
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
    </div>
  </div>

  <!-- MODAL DETAIL -->
  <div class="modal fade" id="modalDetail" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-file-prescription text-primary me-2"></i>Detail Resep</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="modalDetailBody"></div>
      </div>
    </div>
  </div>

  <?php require 'library.php'; ?>
  <script>
    (function() {
      const api = 'controller/pharmacy/pharmacyUsageController.php';
      const STATUS = {
        0: ['Belum dikirim', 'bg-secondary-subtle text-secondary'],
        1: ['Sudah dikirim', 'bg-info-subtle text-info'],
        2: ['Persiapan', 'bg-warning-subtle text-warning'],
        3: ['Selesai', 'bg-success-subtle text-success'],
      };
      const GOL = {
        'Bebas': ['#13a67a', '#e6fffa'],
        'Bebas Terbatas': ['#2e7dd7', '#e8f2ff'],
        'Keras': ['#e0473b', '#fdede8'],
        'Psikotropika': ['#7460ee', '#efecfd'],
        'Narkotika': ['#2a3547', '#e5eaef'],
      };
      let obatMap = {},
        idObat = <?= $selectedIdPharmacy ?>,
        rekap = [],
        totalDaftar = null,
        kunciDaftar = '';

      const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
      } [c]));
      const isi = v => {
        const s = String(v ?? '').trim();
        return /^(-+|null)$/i.test(s) ? '' : s;
      };
      const fmt = n => (Number(n) || 0).toLocaleString('id-ID');
      const rupiah = n => 'Rp ' + fmt(n);
      const ymd = d => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
      const BLN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      const tgl = s => {
        if (!s) return '-';
        const [d, t] = String(s).split(' ');
        const p = d.split('-');
        return p.length === 3 ? `${+p[2]} ${BLN[+p[1] - 1]} ${p[0]}` : s;
      };
      const jam = s => (String(s || '').split(' ')[1] || '').slice(0, 5);
      const umur = (lahir, ref) => {
        if (!/^\d{4}-\d{2}-\d{2}/.test(lahir || '')) return '';
        const b = new Date(lahir.slice(0, 10) + 'T00:00:00'),
          r = ref ? new Date(String(ref).replace(' ', 'T')) : new Date();
        let th = r.getFullYear() - b.getFullYear();
        if (r.getMonth() < b.getMonth() || (r.getMonth() === b.getMonth() && r.getDate() < b.getDate())) th--;
        return th >= 0 && th < 130 ? `${th} th` : '';
      };
      const namaObat = r => isi(r.pharmacy_name_generic) || isi(r.item_name) || '-';
      const golBadge = g => GOL[g] ? `<span class="pu-badge" style="background:${GOL[g][1]};color:${GOL[g][0]}">${esc(g)}</span>` : '<span class="pu-sub">-</span>';
      const loading = on => $('#puLoading').css('display', on ? 'flex' : 'none');
      const getJson = params => fetch(`${api}?${new URLSearchParams(params)}`).then(r => r.json());

      // ---------- periode ----------
      function setPeriode(p) {
        const n = new Date();
        let a = new Date(n),
          b = new Date(n);
        if (p === '7') a.setDate(n.getDate() - 6);
        else if (p === 'bulan') a = new Date(n.getFullYear(), n.getMonth(), 1);
        else if (p === 'bulanlalu') {
          a = new Date(n.getFullYear(), n.getMonth() - 1, 1);
          b = new Date(n.getFullYear(), n.getMonth(), 0);
        } else if (p === '90') a.setDate(n.getDate() - 89);
        $('#fromDate').val(ymd(a));
        $('#toDate').val(ymd(b));
        $('#puPeriod .btn').each(function() {
          $(this).toggleClass('btn-primary', this.dataset.p === p).toggleClass('btn-light', this.dataset.p !== p);
        });
      }
      const periode = () => ({
        from: $('#fromDate').val(),
        to: $('#toDate').val()
      });
      const labelPeriode = () => {
        const {
          from,
          to
        } = periode();
        return from === to ? tgl(from) : `${tgl(from)} – ${tgl(to)}`;
      };

      // ---------- master obat ----------
      $('#selectObat').select2({
        width: '100%',
        placeholder: 'Semua obat',
        language: {
          noResults: () => 'Obat tidak ditemukan'
        }
      });

      function muatObat() {
        return getJson({
          action: 'get_medicines'
        }).then(r => {
          if (r.status !== 'success') return;
          const opsi = ['<option value="0">Semua obat</option>'];
          (r.data || []).forEach(o => {
            obatMap[o.id_pharmacy] = o;
            const trade = isi(o.pharmacy_name_trade);
            opsi.push(`<option value="${o.id_pharmacy}">${esc(isi(o.pharmacy_name_generic))}${trade ? ' — ' + esc(trade) : ''}</option>`);
          });
          $('#selectObat').html(opsi.join(''));
          if (idObat && !obatMap[idObat]) $('#selectObat').append(new Option(`Obat #${idObat}`, idObat));
          $('#selectObat').val(String(idObat || 0)).trigger('change.select2');
        });
      }

      // ---------- rekap per obat ----------
      const tblRekap = $('#tblRekap').DataTable({
        data: [],
        autoWidth: false,
        pageLength: 25,
        order: [
          [4, 'desc']
        ],
        dom: 'rt<"d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2"ip>',
        language: {
          info: '_TOTAL_ obat dipakai',
          infoEmpty: 'Tidak ada pemakaian obat',
          infoFiltered: '(dari _MAX_)',
          emptyTable: '<div class="py-4 text-muted"><i class="fas fa-pills fa-2x mb-2 d-block"></i>Tidak ada pemakaian obat pada periode ini.</div>',
          zeroRecords: '<div class="py-4 text-muted">Obat tidak ditemukan.</div>',
          paginate: {
            previous: '‹',
            next: '›'
          }
        },
        columns: [{
          data: null,
          orderable: false,
          render: (d, t, r, m) => {
            const i = m.settings._iDisplayStart + m.row + 1;
            return `<span class="pu-rank ${i <= 3 ? 'top' : ''}">${i}</span>`;
          }
        }, {
          data: 'pharmacy_name_generic',
          render: (d, t, r) => {
            if (t !== 'display') return `${d} ${r.pharmacy_name_trade || ''} ${r.pharmacy_code || ''}`;
            const trade = isi(r.pharmacy_name_trade);
            return `<div class="pu-name" title="${esc(d)}">${esc(isi(d) || '-')}</div><div class="pu-sub">${[trade, isi(r.pharmacy_category)].filter(Boolean).map(esc).join(' · ')}</div>`;
          }
        }, {
          data: 'pharmcy_golongan',
          render: (d, t) => t === 'display' ? golBadge(d) : (d || '')
        }, {
          data: 'jml_kunjungan',
          className: 'text-end',
          render: (d, t) => t === 'display' ? fmt(d) : d
        }, {
          data: 'total_qty',
          render: (d, t, r) => {
            if (t !== 'display') return d;
            const max = rekap.length ? rekap[0].total_qty : 1;
            return `<div class="d-flex align-items-center gap-2"><span class="pu-qty" style="min-width:56px">${fmt(d)}</span><div class="pu-bar flex-grow-1"><span style="width:${Math.max(2, d / max * 100)}%"></span></div><span class="pu-sub">${esc(isi(r.pharmacy_unit))}</span></div>`;
          }
        }, {
          data: 'nominal',
          className: 'text-end',
          render: (d, t) => t === 'display' ? (+d ? rupiah(d) : '<span class="pu-sub">-</span>') : d
        }]
      });
      $('#tblRekap tbody').on('click', 'tr', function() {
        const r = tblRekap.row(this).data();
        if (r) pilihObat(r.id_pharmacy, true);
      });
      let tRekap;
      $('#rekapCari').on('input', function() {
        clearTimeout(tRekap);
        tRekap = setTimeout(() => tblRekap.search(this.value.trim()).draw(), 200);
      });

      function muatRekap(refresh = false) {
        loading(true);
        return getJson({
            action: 'ranking',
            ...periode(),
            ...(refresh ? {
              refresh: 1
            } : {})
          })
          .then(r => {
            if (r.status !== 'success') return Swal.fire('Gagal', r.message || 'Gagal memuat rekap.', 'error');
            rekap = (r.data || []).slice().sort((a, b) => b.total_qty - a.total_qty);
            tblRekap.clear().rows.add(rekap).draw();
            $('#rekapInfo').html(`<b>${fmt(rekap.length)}</b> obat dipakai · ${esc(labelPeriode())}. Klik obat untuk melihat pasien penerimanya.`);
            if (!idObat) tampilStat({
              kunjungan: r.summary.jml_kunjungan,
              resep: r.summary.jml_resep,
              qty: r.summary.total_qty,
              nominal: r.summary.nominal
            });
          })
          .catch(() => Swal.fire('Gagal', 'Tidak dapat terhubung ke server.', 'error'))
          .finally(() => loading(false));
      }

      function tampilStat(s) {
        $('#stKunjungan').text(fmt(s.kunjungan));
        $('#stResep').text(fmt(s.resep));
        $('#stQty').text(fmt(s.qty));
        $('#stNominal').text(+s.nominal ? rupiah(s.nominal) : '-');
        const unit = idObat && obatMap[idObat] ? isi(obatMap[idObat].pharmacy_unit) : '';
        $('#stQtyLbl').text('Jumlah obat keluar' + (unit ? ` (${unit})` : ''));
        $('#stResepLbl').text(idObat ? 'Resep berisi obat ini' : 'Resep');
      }

      // ---------- daftar pemakaian (server-side) ----------
      const tblDaftar = $('#tblDaftar').DataTable({
        serverSide: true,
        processing: false,
        ordering: false,
        searching: false,
        autoWidth: false,
        deferLoading: 0,
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        dom: 'rt<"d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2"<"d-flex align-items-center gap-2"li>p>',
        language: {
          info: 'Menampilkan _START_-_END_ dari _TOTAL_ pemakaian',
          infoEmpty: 'Tidak ada pemakaian',
          lengthMenu: '_MENU_',
          emptyTable: '<div class="py-4 text-muted"><i class="fas fa-user-injured fa-2x mb-2 d-block"></i>Tidak ada pemakaian pada periode / filter ini.</div>',
          paginate: {
            previous: '‹',
            next: '›'
          }
        },
        ajax: (d, cb) => {
          const filter = {
            action: 'list',
            ...periode(),
            id_pharmacy: idObat || 0,
            status: $('#fStatus').val(),
            layanan: $('#fLayanan').val(),
            q: $('#fCari').val().trim()
          };
          const kunci = JSON.stringify(filter);
          const params = {
            ...filter,
            draw: d.draw,
            start: d.start,
            length: d.length
          };
          if (kunci !== kunciDaftar || totalDaftar === null) params.summary = 1;
          else params.total = totalDaftar;
          loading(true);
          getJson(params).then(r => {
            if (r.status !== 'success') {
              Swal.fire('Gagal', r.message || 'Gagal memuat daftar.', 'error');
              return cb({
                draw: d.draw,
                recordsTotal: 0,
                recordsFiltered: 0,
                data: []
              });
            }
            kunciDaftar = kunci;
            totalDaftar = r.recordsTotal;
            if (r.summary && idObat) tampilStat(r.summary);
            cb({
              draw: d.draw,
              recordsTotal: r.recordsTotal,
              recordsFiltered: r.recordsFiltered,
              data: r.data || []
            });
          }).catch(() => {
            Swal.fire('Gagal', 'Tidak dapat terhubung ke server.', 'error');
            cb({
              draw: d.draw,
              recordsTotal: 0,
              recordsFiltered: 0,
              data: []
            });
          }).finally(() => loading(false));
        },
        columns: [{
          data: null,
          render: (d, t, r) => `<div class="fw-semibold" style="white-space:nowrap">${esc(tgl(r.tgl_resep || r.visit_date))}</div><div class="pu-sub">${esc(jam(r.tgl_resep))}${r.permintaan_number ? ' · ' + esc(r.permintaan_number) : ''}</div>`
        }, {
          data: null,
          render: (d, t, r) => {
            const g = /^l/i.test(r.patient_gender || '') ? '<i class="fas fa-mars text-primary"></i>' : /^p/i.test(r.patient_gender || '') ? '<i class="fas fa-venus" style="color:#e0569b"></i>' : '';
            const u = umur(r.patient_datebirth, r.tgl_resep);
            const nama = esc(isi(r.patient_name) || '-');
            return `<div class="pu-name" title="${nama}">${r.id_patient ? `<a class="text-reset" href="module/admin/patient_details?pt=${r.id_patient}">${nama}</a>` : nama}</div>
              <div class="pu-sub d-flex gap-2 align-items-center">${r.nomor_rm ? `<span class="pu-rm">${esc(String(r.nomor_rm).trim())}</span>` : ''}${g}${u ? `<span>${u}</span>` : ''}</div>`;
          }
        }, {
          data: null,
          className: 'col-obat',
          render: (d, t, r) => {
            const trade = isi(r.pharmacy_name_trade);
            return `<div class="pu-name" style="max-width:220px" title="${esc(namaObat(r))}">${esc(namaObat(r))}</div>${trade ? `<div class="pu-sub">${esc(trade)}</div>` : ''}`;
          }
        }, {
          data: null,
          render: (d, t, r) => `<div><span class="pu-qty">${fmt(r.qty)}</span> <span class="pu-sub">${esc(isi(r.pharmacy_unit))}</span>${String(r.tipe_obat || '').toLowerCase() === 'racikan' ? ' <span class="pu-badge bg-warning-subtle text-warning">Racikan</span>' : ''}</div>
            <div class="pu-sub">${isi(r.signa) ? esc(r.signa) : '<i>signa tidak diisi</i>'}${isi(r.catatan) ? ' · ' + esc(r.catatan) : ''}</div>`
        }, {
          data: null,
          render: (d, t, r) => `<div style="font-size:.82rem">${esc(isi(r.doctor_name) || '-')}</div><div class="pu-sub">${esc(isi(r.poli_name) || isi(r.source_hub) || '-')}${String(r.status_rawatinap) === '1' ? ' · Rawat Inap' : ''}</div>`
        }, {
          data: 'status_permintaan',
          className: 'text-center',
          render: d => {
            const s = STATUS[d] || ['-', 'bg-light text-muted'];
            return `<span class="pu-badge ${s[1]}">${s[0]}</span>`;
          }
        }, {
          data: null,
          className: 'text-center no-print',
          render: (d, t, r) => `<button class="btn btn-sm btn-light text-primary btn-detail" data-id="${r.id_pharmacy_details}" title="Detail resep"><i class="fas fa-eye"></i></button>`
        }]
      });

      function muatDaftar() {
        tblDaftar.column(2).visible(!idObat);
        tblDaftar.page(0);
        tblDaftar.ajax.reload();
      }

      let tCari;
      $('#fCari').on('input', () => {
        clearTimeout(tCari);
        tCari = setTimeout(muatDaftar, 450);
      });
      $('#fStatus, #fLayanan').on('change', muatDaftar);

      // ---------- obat terpilih ----------
      function tampilObat() {
        const o = obatMap[idObat];
        $('#puObat').toggleClass('d-none', !idObat);
        if (!idObat) return;
        $('#puObatNama').text(o ? isi(o.pharmacy_name_generic) : `Obat #${idObat}`);
        $('#puObatSub').text(o ? [isi(o.pharmacy_name_trade), isi(o.pharmacy_unit) && 'Satuan ' + o.pharmacy_unit].filter(Boolean).join(' · ') : '');
        $('#puObatBadges').html(o ? [
          isi(o.pharmacy_code) ? `<span class="pu-badge bg-primary text-white">BPJS ${esc(o.pharmacy_code)}</span>` : '',
          isi(o.pharmacy_category) ? `<span class="pu-badge bg-light text-dark">${esc(o.pharmacy_category)}</span>` : '',
          golBadge(o.pharmcy_golongan)
        ].join('') : '');
        $('#puObatDetail').attr('href', `module/admin/pharmacy_details?no=${idObat}`);
      }

      function pilihObat(id, keDaftar) {
        idObat = +id || 0;
        $('#selectObat').val(String(idObat)).trigger('change.select2');
        const u = new URL(location.href);
        idObat ? u.searchParams.set('id_pharmacy', idObat) : u.searchParams.delete('id_pharmacy');
        history.replaceState(null, '', u);
        tampilObat();
        totalDaftar = null;
        if (!idObat) muatRekap();
        if (keDaftar || idObat) bootstrap.Tab.getOrCreateInstance(document.getElementById('tabDaftarBtn')).show();
        if (isTabDaftar() || idObat) muatDaftar();
      }
      const isTabDaftar = () => $('#tabDaftar').hasClass('active');
      $('#selectObat').on('select2:select', e => pilihObat(e.params.data.id, true));
      $('#btnSemuaObat').on('click', () => {
        pilihObat(0);
        bootstrap.Tab.getOrCreateInstance(document.getElementById('tabRekapBtn')).show();
      });
      document.getElementById('tabDaftarBtn').addEventListener('shown.bs.tab', () => {
        if (totalDaftar === null) muatDaftar();
        tblDaftar.columns.adjust();
      });

      // ---------- periode berubah ----------
      function periodeBerubah() {
        totalDaftar = null;
        muatRekap();
        if (isTabDaftar() || idObat) muatDaftar();
      }
      $('#puPeriod').on('click', '.btn', function() {
        setPeriode(this.dataset.p);
        periodeBerubah();
      });
      $('#fromDate, #toDate').on('change', () => {
        $('#puPeriod .btn').removeClass('btn-primary').addClass('btn-light');
        if ($('#fromDate').val() && $('#toDate').val()) periodeBerubah();
      });
      $('#btnRefresh').on('click', () => {
        totalDaftar = null;
        muatRekap(true);
        if (isTabDaftar()) muatDaftar();
      });
      $('#btnPrint').on('click', () => window.print());

      // ---------- detail ----------
      $(document).on('click', '.btn-detail', function() {
        const id = $(this).data('id');
        $('#modalDetailBody').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');
        bootstrap.Modal.getOrCreateInstance('#modalDetail').show();
        getJson({
          action: 'detail',
          id_pharmacy_details: id
        }).then(r => {
          if (r.status !== 'success') return $('#modalDetailBody').html(`<div class="alert alert-danger mb-0">${esc(r.message || 'Data tidak ditemukan.')}</div>`);
          const d = r.data;
          const s = STATUS[d.status_permintaan] || ['-', 'bg-light'];
          const u = umur(d.patient_datebirth, d.tgl_resep);
          const obat = (d.other_medicines || []).map(m => `
            <tr class="${+m.id_pharmacy_details === +d.id_pharmacy_details ? 'table-primary' : ''}">
              <td><div class="fw-semibold">${esc(isi(m.nama_obat) || '-')}</div>${isi(m.pharmacy_name_trade) ? `<div class="pu-sub">${esc(m.pharmacy_name_trade)}</div>` : ''}</td>
              <td class="text-end"><b>${fmt(m.qty)}</b> <span class="pu-sub">${esc(isi(m.pharmacy_unit))}</span></td>
              <td>${esc(isi(m.signa) || '-')}</td>
              <td class="pu-sub">${esc(isi(m.catatan) || '-')}</td>
            </tr>`).join('');
          $('#modalDetailBody').html(`
            <div class="d-flex flex-wrap justify-content-between gap-3 p-3 rounded-3 mb-3" style="background:#f6f9fc">
              <div class="min-w-0">
                <div class="fw-bold fs-5 text-dark">${esc(isi(d.patient_name) || '-')}</div>
                <div class="d-flex flex-wrap gap-2 mt-1 small">
                  ${d.nomor_rm ? `<span class="pu-rm">${esc(String(d.nomor_rm).trim())}</span>` : ''}
                  ${d.patient_gender ? `<span>${esc(d.patient_gender)}${u ? ' · ' + u : ''}</span>` : ''}
                  ${d.provider_name ? `<span class="pu-badge bg-info-subtle text-info">${esc(d.provider_name)}</span>` : ''}
                </div>
                ${isi(d.patient_allergy) ? `<div class="small text-danger mt-1"><i class="fas fa-exclamation-triangle me-1"></i>Alergi: ${esc(d.patient_allergy)}</div>` : ''}
              </div>
              <div class="text-end">
                <span class="pu-badge ${s[1]}">${s[0]}</span>
                <div class="fw-semibold mt-1">${esc(tgl(d.tgl_resep))} ${esc(jam(d.tgl_resep))}</div>
                <div class="pu-sub">${esc(d.permintaan_number || '')}</div>
              </div>
            </div>
            <div class="row g-3 mb-3">
              <div class="col-6 col-md-3 dt-field"><span>Dokter</span><div>${esc(isi(d.doctor_name) || '-')}</div></div>
              <div class="col-6 col-md-3 dt-field"><span>Poli / layanan</span><div>${esc(isi(d.poli_name) || isi(d.source_hub) || '-')}</div></div>
              <div class="col-6 col-md-3 dt-field"><span>Jenis resep</span><div>${esc(isi(d.tipe_obat) || '-')}</div></div>
              <div class="col-6 col-md-3 dt-field"><span>Kunjungan</span><div class="small">${esc(d.visit_ID || '-')}</div></div>
              ${String(d.tipe_obat || '').toLowerCase() === 'racikan' ? `<div class="col-12 dt-field"><span>Racikan</span><div>${esc(isi(d.rck_jumlah))} ${esc(isi(d.rck_satuan))} · Signa ${esc(isi(d.rck_signa) || '-')}</div></div>` : ''}
              ${isi(d.catatan_permintaan) ? `<div class="col-12 dt-field"><span>Catatan dokter</span><div class="fw-normal">${esc(d.catatan_permintaan)}</div></div>` : ''}
            </div>
            <div class="fw-semibold mb-2">Isi resep</div>
            <div class="table-responsive">
              <table class="table table-sm align-middle mb-0">
                <thead class="table-light"><tr><th>Obat</th><th class="text-end">Jumlah</th><th>Signa</th><th>Catatan</th></tr></thead>
                <tbody>${obat || '<tr><td colspan="4" class="text-center text-muted">Tidak ada obat</td></tr>'}</tbody>
              </table>
            </div>
            ${d.id_patient ? `<div class="text-end mt-3"><a href="module/admin/patient_details?pt=${d.id_patient}" class="btn btn-sm btn-light"><i class="fas fa-history me-1"></i>Riwayat pasien</a></div>` : ''}`);
        }).catch(() => $('#modalDetailBody').html('<div class="alert alert-danger mb-0">Gagal memuat detail resep.</div>'));
      });

      // ---------- mulai ----------
      setPeriode('bulan');
      muatObat().then(() => {
        tampilObat();
        muatRekap().then(() => {
          if (idObat) pilihObat(idObat, true);
        });
      });
    })();
  </script>
</body>

</html>
