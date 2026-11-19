<?php
$title = 'Farmasi Stock';
require '../../controller/view.php';
$no = (int) ($_GET['no'] ?? 0);
require_once '../../controller/master/marginHarga.php';
$marginFaskes = marginFaskes($koneksi, (int) ($_SESSION['id_customer'] ?? 0));
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .ps-stat {
      border: 1px solid #e5eaef;
      border-radius: 12px;
      background: #fff;
      padding: 14px 16px;
      height: 100%;
    }

    .ps-stat .ps-label {
      font-size: .75rem;
      color: #6c757d;
      font-weight: 600;
    }

    .ps-stat .ps-val {
      font-size: 1.45rem;
      font-weight: 700;
      line-height: 1.25;
    }

    .ps-stat .ps-sub {
      font-size: .72rem;
      color: #8a94a6;
    }

    .ps-stat.ps-main {
      border-color: #5d87ff;
      background: #f4f7ff;
    }

    .ps-op {
      font-size: 1.1rem;
      color: #adb5bd;
      align-self: center;
    }

    .ps-icon {
      width: 46px;
      height: 46px;
      border-radius: 12px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem;
      background: #ecf2ff;
      color: #5d87ff;
      flex-shrink: 0;
    }

    #tableKartu td,
    #tablePenerimaan td {
      font-size: .85rem;
    }

    #tableKartu th,
    #tablePenerimaan th {
      font-size: .78rem;
      white-space: nowrap;
    }

    .ps-num {
      text-align: right;
      white-space: nowrap;
    }

    .ps-loading {
      position: absolute;
      inset: 0;
      background: rgba(255, 255, 255, .7);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 10;
      border-radius: 12px;
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

          <!-- HEADER OBAT + FILTER -->
          <div class="card mb-3">
            <div class="card-body p-4">
              <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div class="d-flex gap-3 align-items-center">
                  <span class="ps-icon"><i class="fas fa-pills"></i></span>
                  <div>
                    <div class="text-muted small">Stok Farmasi</div>
                    <h4 class="fw-semibold mb-1" id="pharmacyName">Memuat...</h4>
                    <div class="d-flex flex-wrap gap-1 align-items-center">
                      <small class="text-muted me-1" id="pharmacyCode"></small>
                      <span class="badge bg-light text-dark" id="pharmacyKategori"></span>
                      <span id="pharmacyStatus"></span>
                      <span class="badge bg-secondary-subtle text-secondary d-none" id="pharmacyShared"><i class="fas fa-lock me-1"></i>Master bersama (hanya lihat)</span>
                    </div>
                  </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                  <a href="module/admin/pharmacy" class="btn btn-light"><i class="fas fa-arrow-left me-1"></i> Daftar Obat</a>
                  <a href="module/admin/pharmacy_details?no=<?= urlencode((string) ($no ?? '')) ?>" class="btn btn-outline-info"><i class="fas fa-info-circle me-1"></i> Detail Farmasi</a>
                  <button type="button" class="btn btn-primary btn-tambah-penerimaan"><i class="fas fa-plus me-1"></i> Tambah Penerimaan</button>
                </div>
              </div>

              <div class="d-flex align-items-end flex-wrap gap-2 mt-3">
                <div>
                  <label for="fromDate" class="form-label small mb-1">Dari</label>
                  <input type="date" id="fromDate" class="form-control">
                </div>
                <div>
                  <label for="toDate" class="form-label small mb-1">Sampai</label>
                  <input type="date" id="toDate" class="form-control">
                </div>
                <div class="btn-group">
                  <button type="button" class="btn btn-outline-secondary ps-range" data-range="today">Hari ini</button>
                  <button type="button" class="btn btn-outline-secondary ps-range" data-range="month">Bulan ini</button>
                  <button type="button" class="btn btn-outline-secondary ps-range" data-range="lastmonth">Bulan lalu</button>
                </div>
                <button type="button" id="btnTampilkan" class="btn btn-dark"><i class="fas fa-search me-1"></i> Tampilkan</button>
                <a href="module/admin/rpt_farmasi" class="btn btn-link btn-sm ms-auto" id="linkLaporan"><i class="fas fa-file-alt me-1"></i> Lihat di Laporan Farmasi</a>
              </div>
            </div>
          </div>

          <div id="psError" class="alert alert-danger d-none" role="alert"></div>

          <!-- RINGKASAN STOK PERIODE: awal + masuk - keluar = akhir -->
          <div class="d-flex gap-2 mb-3 flex-wrap flex-xl-nowrap">
            <div class="ps-stat flex-fill">
              <div class="ps-label"><i class="fas fa-box me-1"></i> Stok Awal</div>
              <div class="ps-val" id="sAwal">-</div>
              <div class="ps-sub" id="sAwalSub">awal periode</div>
            </div>
            <span class="ps-op d-none d-xl-block">+</span>
            <div class="ps-stat flex-fill">
              <div class="ps-label text-success"><i class="fas fa-arrow-down me-1"></i> Masuk</div>
              <div class="ps-val text-success" id="sMasuk">-</div>
              <div class="ps-sub">penerimaan periode</div>
            </div>
            <span class="ps-op d-none d-xl-block">−</span>
            <div class="ps-stat flex-fill">
              <div class="ps-label text-danger"><i class="fas fa-arrow-up me-1"></i> Keluar</div>
              <div class="ps-val text-danger" id="sKeluar">-</div>
              <div class="ps-sub">resep periode</div>
            </div>
            <span class="ps-op d-none d-xl-block">=</span>
            <div class="ps-stat ps-main flex-fill">
              <div class="ps-label text-primary"><i class="fas fa-boxes me-1"></i> Stok Akhir</div>
              <div class="ps-val text-primary" id="sAkhir">-</div>
              <div class="ps-sub" id="sAkhirSub">akhir periode</div>
            </div>
            <div class="ps-stat flex-fill">
              <div class="ps-label"><i class="fas fa-clock me-1"></i> Stok Saat Ini</div>
              <div class="ps-val" id="sSaatIni">-</div>
              <div class="ps-sub" id="sMinMax">-</div>
            </div>
            <div class="ps-stat flex-fill">
              <div class="ps-label"><i class="fas fa-wallet me-1"></i> Nilai Stok Akhir</div>
              <div class="ps-val" id="sNilai">-</div>
              <div class="ps-sub" id="sHarga">harga beli</div>
            </div>
          </div>

          <div class="row g-3">
            <!-- KARTU STOK & PENERIMAAN -->
            <div class="col-xl-9">
              <div class="card position-relative mb-0">
                <div class="ps-loading" id="psLoading">
                  <div class="text-center"><span class="spinner-border text-primary"></span>
                    <div class="small text-muted mt-2">Memuat data...</div>
                  </div>
                </div>
                <div class="card-body p-4">
                  <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabKartu" type="button"><i class="fas fa-exchange-alt me-1"></i> Kartu Stok <span class="badge bg-light text-dark ms-1" id="countKartu">0</span></button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabPenerimaan" type="button"><i class="fas fa-truck-loading me-1"></i> Penerimaan <span class="badge bg-light text-dark ms-1" id="countPenerimaan">0</span></button></li>
                  </ul>
                  <div class="tab-content">
                    <div class="tab-pane fade show active" id="tabKartu">
                      <div class="table-responsive">
                        <table id="tableKartu" class="table align-middle w-100 mb-0">
                          <thead class="table-light">
                            <tr>
                              <th>Waktu</th>
                              <th>Referensi</th>
                              <th>Keterangan</th>
                              <th class="ps-num">Masuk</th>
                              <th class="ps-num">Keluar</th>
                              <th class="ps-num">Saldo</th>
                            </tr>
                          </thead>
                          <tbody></tbody>
                          <tfoot class="table-light fw-semibold">
                            <tr>
                              <td colspan="3">Total periode</td>
                              <td class="ps-num text-success" id="fMasuk">-</td>
                              <td class="ps-num text-danger" id="fKeluar">-</td>
                              <td class="ps-num" id="fSaldo">-</td>
                            </tr>
                          </tfoot>
                        </table>
                      </div>
                      <div class="small text-muted mt-2"><i class="fas fa-info-circle me-1"></i> Saldo dimulai dari stok awal periode. Keluar dihitung dari resep klinik ini.</div>
                    </div>
                    <div class="tab-pane fade" id="tabPenerimaan">
                      <div class="table-responsive">
                        <table id="tablePenerimaan" class="table align-middle w-100 mb-0">
                          <thead class="table-light">
                            <tr>
                              <th>Tanggal</th>
                              <th>Faktur / Supplier</th>
                              <th>Batch</th>
                              <th>Expired</th>
                              <th class="ps-num">Jumlah</th>
                              <th class="ps-num">Harga Beli</th>
                              <th class="ps-num">Total</th>
                              <th>Keterangan</th>
                              <th class="text-center">Aksi</th>
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

            <!-- STOK DASAR -->
            <div class="col-xl-3">
              <div class="card mb-0">
                <div class="card-body p-4">
                  <h6 class="fw-semibold mb-1"><i class="fas fa-box-open me-1 text-primary"></i> Stok Dasar</h6>
                  <p class="small text-muted mb-3">Stok awal di master obat (misalnya hasil stok opname). Semua penerimaan dan resep dihitung dari angka ini.</p>
                  <form id="stockAwalForm">
                    <label class="form-label small" for="inputStockAwal">Jumlah <span class="text-danger">*</span></label>
                    <div class="input-group mb-2">
                      <input type="number" id="inputStockAwal" class="form-control" min="0" step="1" required>
                      <span class="input-group-text" id="stockUnit">unit</span>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" id="btnSimpanStockAwal"><i class="fas fa-save me-1"></i> Simpan Stok Dasar</button>
                  </form>
                  <div class="alert alert-light border small mt-3 mb-0 d-none" id="sharedInfo">
                    <i class="fas fa-lock me-1"></i> Obat master bersama: stok dasar dan penerimaan hanya bisa diubah pada obat milik klinik.
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <!-- MODAL PENERIMAAN -->
  <div class="modal fade" id="modalPenerimaan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <form class="modal-content" id="formPenerimaan" novalidate>
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-truck-loading me-2 text-primary"></i>Tambah Penerimaan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label small">Tanggal Terima <span class="text-danger">*</span></label>
              <input type="date" name="tanggal" id="penerimaan_tanggal" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label small">No. Faktur</label>
              <input type="text" name="nomor_faktur" class="form-control" maxlength="255">
            </div>
            <div class="col-md-4">
              <label class="form-label small">Supplier</label>
              <input type="text" name="supplier" class="form-control" maxlength="255">
            </div>
            <div class="col-md-4">
              <label class="form-label small">Batch</label>
              <input type="text" name="batch" class="form-control" maxlength="255">
            </div>
            <div class="col-md-4">
              <label class="form-label small">Expired</label>
              <input type="date" name="expired" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label small">Jumlah <span class="text-danger">*</span></label>
              <div class="input-group">
                <input type="number" name="jumlah" id="jumlah" class="form-control" min="1" step="1" required>
                <span class="input-group-text modal-unit">unit</span>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label small">Harga Beli / Unit</label>
              <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="number" name="harga_beli" id="harga_beli" class="form-control" min="0" step="any">
              </div>
              <div class="form-text" id="hintMargin"></div>
            </div>
            <div class="col-md-6">
              <label class="form-label small">Total Harga</label>
              <input type="text" id="totalHarga" class="form-control bg-light fw-semibold" readonly value="Rp 0">
            </div>
            <div class="col-12">
              <label class="form-label small">Keterangan</label>
              <textarea name="keterangan" class="form-control" rows="2" maxlength="2000"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary" id="btnSimpanPenerimaan"><i class="fas fa-save me-1"></i> Simpan Penerimaan</button>
        </div>
      </form>
    </div>
  </div>

  <?php require 'library.php'; ?>
  <script>
    $(function() {
      const api = 'controller/pharmacy/pharmacyStockController.php';
      const idObat = <?= $no ?>;
      const num = v => (parseFloat(v) || 0).toLocaleString('id-ID', {
        maximumFractionDigits: 2
      });
      const rp = v => 'Rp ' + Math.round(parseFloat(v) || 0).toLocaleString('id-ID');
      const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
      }[c]));
      const isi = v => /[\p{L}\p{N}]/u.test(String(v ?? '')) ? String(v).trim() : '';
      const STATUS = {
        habis: ['bg-danger-subtle text-danger', 'fa-times-circle'],
        minimum: ['bg-warning-subtle text-warning', 'fa-arrow-down'],
        maximum: ['bg-info-subtle text-info', 'fa-arrow-up'],
        normal: ['bg-success-subtle text-success', 'fa-check-circle']
      };
      const lang = {
        search: 'Cari:',
        lengthMenu: 'Tampilkan _MENU_',
        info: '_START_–_END_ dari _TOTAL_',
        infoEmpty: '0 data',
        infoFiltered: '(dari _MAX_)',
        emptyTable: 'Tidak ada transaksi pada periode ini.',
        zeroRecords: 'Tidak ditemukan.',
        paginate: {
          next: '›',
          previous: '‹'
        }
      };
      const gagal = (xhr, fallback) => (xhr && xhr.responseJSON && xhr.responseJSON.message) || fallback;
      let obat = null;

      if (!idObat) {
        $('#psError').text('ID obat tidak ditemukan.').removeClass('d-none');
        return;
      }

      // ---------- periode (sama dengan Laporan Farmasi) ----------
      const ymd = d => d.toLocaleDateString('sv-SE', {
        timeZone: 'Asia/Jakarta'
      });
      const today = ymd(new Date());
      const fmtTgl = s => s ? new Date(s.slice(0, 10) + 'T00:00:00').toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
      }) : '-';

      function setRange(r) {
        const now = new Date(today + 'T00:00:00');
        let from = now,
          to = now;
        if (r === 'month') from = new Date(now.getFullYear(), now.getMonth(), 1);
        if (r === 'lastmonth') {
          from = new Date(now.getFullYear(), now.getMonth() - 1, 1);
          to = new Date(now.getFullYear(), now.getMonth(), 0);
        }
        $('#fromDate').val(ymd(from));
        $('#toDate').val(ymd(to));
      }
      $('#fromDate, #toDate').attr('max', today);
      setRange('month');
      const periode = () => ({
        fromDate: $('#fromDate').val(),
        toDate: $('#toDate').val()
      });

      function periodeValid() {
        const p = periode();
        if (!p.fromDate || !p.toDate) {
          Swal.fire('Periode belum lengkap', 'Silakan pilih tanggal mulai dan tanggal akhir.', 'warning');
          return false;
        }
        if (p.fromDate > p.toDate) {
          Swal.fire('Periode tidak valid', 'Tanggal mulai tidak boleh lebih besar dari tanggal akhir.', 'warning');
          return false;
        }
        return true;
      }

      // ---------- tabel ----------
      const tKartu = $('#tableKartu').DataTable({
        data: [],
        ordering: false,
        pageLength: 25,
        language: lang,
        columns: [{
            data: 'waktu',
            render: (v, t, r) => r.jenis === 'awal' ? `<span class="text-muted">${fmtTgl(v)}</span>` :
              `${fmtTgl(v)}${r.jenis === 'keluar' ? `<div class="small text-muted">${esc(String(v).slice(11, 16))}</div>` : ''}`
          },
          {
            data: 'referensi',
            render: (v, t, r) => r.jenis === 'awal' ? `<b>${esc(v)}</b>` :
              `<span class="badge ${r.jenis === 'masuk' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'}">${esc(v)}</span>`
          },
          {
            data: 'keterangan',
            render: (v, t, r) => t !== 'display' ? `${v || ''} ${r.sub || ''}` :
              `${esc(v || '-')}${r.sub && r.sub !== v ? `<div class="small text-muted">${esc(r.sub)}</div>` : ''}`
          },
          {
            data: 'masuk',
            className: 'ps-num',
            render: v => +v > 0 ? `<span class="text-success fw-semibold">+${num(v)}</span>` : '<span class="text-muted">-</span>'
          },
          {
            data: 'keluar',
            className: 'ps-num',
            render: v => +v > 0 ? `<span class="text-danger fw-semibold">−${num(v)}</span>` : '<span class="text-muted">-</span>'
          },
          {
            data: 'saldo',
            className: 'ps-num',
            render: v => `<b class="${+v < 0 ? 'text-danger' : ''}">${num(v)}</b>`
          }
        ]
      });

      const tPenerimaan = $('#tablePenerimaan').DataTable({
        data: [],
        order: [[0, 'desc']],
        pageLength: 10,
        language: {
          ...lang,
          emptyTable: 'Belum ada penerimaan pada periode ini.'
        },
        columns: [{
            data: 'tanggal',
            render: (v, t) => t === 'display' ? fmtTgl(v) : v
          },
          {
            data: null,
            render: r => `${esc(r.nomor_faktur || '-')}<div class="small text-muted">${esc(r.supplier || '')}</div>`
          },
          {
            data: 'batch',
            render: v => esc(v || '-')
          },
          {
            data: 'expired',
            render: (v, t) => {
              if (t !== 'display') return v || '';
              if (!v || v === '0000-00-00') return '<span class="text-muted">-</span>';
              const sisa = (new Date(v + 'T00:00:00') - new Date(today + 'T00:00:00')) / 864e5;
              const cls = sisa < 0 ? 'bg-danger-subtle text-danger' : sisa <= 90 ? 'bg-warning-subtle text-warning' : 'bg-light text-dark';
              return `<span class="badge ${cls}">${fmtTgl(v)}</span>`;
            }
          },
          {
            data: 'jumlah',
            className: 'ps-num',
            render: (v, t) => t === 'display' ? `<b>${num(v)}</b>` : +v
          },
          {
            data: 'harga_beli',
            className: 'ps-num',
            render: (v, t) => t === 'display' ? rp(v) : +v
          },
          {
            data: null,
            className: 'ps-num',
            render: (r, t) => t === 'display' ? rp((+r.jumlah || 0) * (+r.harga_beli || 0)) : (+r.jumlah || 0) * (+r.harga_beli || 0)
          },
          {
            data: 'keterangan',
            render: v => `<span class="small">${esc(v || '-')}</span>`
          },
          {
            data: 'id_penerimaan',
            className: 'text-center',
            orderable: false,
            render: v => obat && obat.bisa_ubah ?
              `<button type="button" class="btn btn-sm btn-light text-danger btnDeletePenerimaan" data-id="${+v}" title="Hapus"><i class="fas fa-trash"></i></button>` : ''
          }
        ]
      });

      // ---------- muat data ----------
      function renderObat(d, p) {
        obat = d;
        const satuan = d.satuan || 'unit';
        $('#pharmacyName').text(d.pharmacy_name_generic || '-');
        $('#pharmacyCode').text(d.nama_dagang_kode || '');
        $('#pharmacyKategori').text(d.kategori || '-');
        const s = STATUS[d.status_code] || STATUS.normal;
        $('#pharmacyStatus').html(`<span class="badge ${s[0]}"><i class="fas ${s[1]} me-1"></i>${esc(d.status_stock)}</span>`);
        $('#pharmacyShared, #sharedInfo').toggleClass('d-none', !!d.bisa_ubah);
        $('#stockAwalForm :input, .btn-tambah-penerimaan').prop('disabled', !d.bisa_ubah);
        $('#stockUnit, .modal-unit').text(satuan);
        $('#inputStockAwal').val(d.stok_dasar);

        $('#sAwal').text(num(d.stok_awal));
        $('#sAwalSub').text('per ' + fmtTgl(p.fromDate));
        $('#sMasuk').text('+' + num(d.stok_masuk));
        $('#sKeluar').text('−' + num(d.stok_keluar));
        $('#sAkhir').text(num(d.stok_akhir)).toggleClass('text-danger', +d.stok_akhir <= 0).toggleClass('text-primary', +d.stok_akhir > 0);
        $('#sAkhirSub').text('per ' + fmtTgl(p.toDate) + ' • ' + satuan);
        $('#sSaatIni').text(num(d.stok_saat_ini)).toggleClass('text-danger', +d.stok_saat_ini <= 0);
        $('#sMinMax').text((+d.stok_min || +d.stok_max) ? `min ${num(d.stok_min)} • max ${num(d.stok_max)}` : 'min/max belum diatur');
        $('#sNilai').text(rp(d.nilai_stok));
        $('#sHarga').text('harga beli ' + rp(d.pharmacy_price_buy) + ' / ' + satuan);
        $('#linkLaporan').attr('href', `module/admin/rpt_farmasi`);
        document.title = (d.pharmacy_name_generic || 'Stok') + ' - Stok Farmasi';
      }

      function load() {
        if (!periodeValid()) return;
        const p = periode();
        $('#psError').addClass('d-none');
        $('#psLoading').css('display', 'flex');
        $('#btnTampilkan').prop('disabled', true);
        const q = {
          id_pharmacy: idObat,
          ...p
        };
        $.when(
            $.getJSON(api, {
              action: 'detail',
              ...q
            }),
            $.getJSON(api, {
              action: 'kartu',
              ...q
            }),
            $.getJSON(api, {
              action: 'penerimaan',
              ...q
            })
          )
          .done((detail, kartu, penerimaan) => {
            renderObat(detail[0].data, p);
            const k = kartu[0];
            const baris = [{
              waktu: p.fromDate,
              jenis: 'awal',
              referensi: 'Saldo awal',
              keterangan: 'Stok awal periode',
              masuk: 0,
              keluar: 0,
              saldo: k.stok_awal
            }, ...k.data];
            tKartu.clear().rows.add(baris).draw();
            $('#countKartu').text(k.data.length);
            const sum = f => k.data.reduce((a, r) => a + (+r[f] || 0), 0);
            $('#fMasuk').text('+' + num(sum('masuk')));
            $('#fKeluar').text('−' + num(sum('keluar')));
            $('#fSaldo').text(num(k.stok_akhir));
            tPenerimaan.clear().rows.add(penerimaan[0].data).draw();
            $('#countPenerimaan').text(penerimaan[0].data.length);
          })
          .fail(xhr => $('#psError').text(gagal(xhr, 'Gagal memuat data stok.')).removeClass('d-none'))
          .always(() => {
            $('#psLoading').hide();
            $('#btnTampilkan').prop('disabled', false);
          });
      }

      $('#btnTampilkan').on('click', load);
      $('.ps-range').on('click', function() {
        setRange($(this).data('range'));
        load();
      });

      // ---------- stok dasar ----------
      $('#stockAwalForm').on('submit', function(e) {
        e.preventDefault();
        const nilai = $('#inputStockAwal').val();
        if (nilai === '' || +nilai < 0) {
          Swal.fire('Data belum valid', 'Stok dasar harus angka 0 atau lebih.', 'warning');
          return;
        }
        Swal.fire({
          title: 'Simpan stok dasar?',
          html: `Stok dasar diubah menjadi <b>${num(nilai)}</b>.<br><small class="text-muted">Semua stok awal, akhir, dan saat ini akan dihitung ulang dari angka ini.</small>`,
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Ya, simpan',
          cancelButtonText: 'Batal',
          reverseButtons: true
        }).then(r => {
          if (!r.isConfirmed) return;
          const $btn = $('#btnSimpanStockAwal').prop('disabled', true);
          $.post(api, {
              action: 'stock_awal',
              id_pharmacy: idObat,
              stock_awal: nilai
            }, null, 'json')
            .done(res => {
              Swal.fire('Berhasil', res.message, 'success');
              load();
            })
            .fail(xhr => Swal.fire('Gagal', gagal(xhr, 'Stok dasar gagal disimpan.'), 'error'))
            .always(() => $btn.prop('disabled', false));
        });
      });

      // ---------- penerimaan ----------
      const MARGIN = <?= json_encode($marginFaskes) ?>;

      function hitungTotal() {
        $('#totalHarga').val(rp((+$('#jumlah').val() || 0) * (+$('#harga_beli').val() || 0)));
        // margin aktif: harga jual ikut diperbarui saat penerimaan disimpan (lihat Setting Margin)
        const bmhp = /^\s*(bmhp|alkes|alat)/i.test((obat && obat.pharmacy_category) || '');
        const pct = bmhp ? MARGIN.bmhp : MARGIN.obat;
        const beli = +$('#harga_beli').val() || 0;
        if (pct === null) {
          $('#hintMargin').html('Margin belum diatur, harga jual obat tidak berubah. <a href="module/admin/setting_margin">Atur margin</a>');
        } else if (beli > 0) {
          const mentah = Math.round(beli * (1 + pct / 100) * 1e6) / 1e6;
          const kel = mentah < 1000 ? 10 : 100;
          const jual = Math.ceil(mentah / kel) * kel;
          $('#hintMargin').html(`<i class="fas fa-calculator me-1 text-primary"></i>Harga jual akan diperbarui menjadi <b>${rp(jual)}</b> (margin ${bmhp ? 'BMHP' : 'obat'} ${pct}%).`);
        } else {
          $('#hintMargin').text(`Isi harga beli agar harga jual dihitung otomatis (margin ${bmhp ? 'BMHP' : 'obat'} ${pct}%).`);
        }
      }
      $('#jumlah, #harga_beli').on('input', hitungTotal);

      $('.btn-tambah-penerimaan').on('click', function() {
        $('#formPenerimaan')[0].reset();
        $('#penerimaan_tanggal').val(today).attr('max', today);
        if (obat && +obat.pharmacy_price_buy > 0) $('#harga_beli').val(obat.pharmacy_price_buy);
        hitungTotal();
        $('#modalPenerimaan').modal('show');
      });

      $('#formPenerimaan').on('submit', function(e) {
        e.preventDefault();
        const f = this;
        if (!f.tanggal.value) {
          Swal.fire('Data belum lengkap', 'Tanggal terima wajib diisi.', 'warning');
          return;
        }
        if (!(+f.jumlah.value > 0)) {
          Swal.fire('Data belum lengkap', 'Jumlah harus lebih dari 0.', 'warning');
          return;
        }
        const data = $(f).serializeArray();
        data.push({
          name: 'action',
          value: 'penerimaan'
        }, {
          name: 'id_pharmacy',
          value: idObat
        });
        const $btn = $('#btnSimpanPenerimaan').prop('disabled', true);
        $.post(api, $.param(data), null, 'json')
          .done(res => {
            $('#modalPenerimaan').modal('hide');
            Swal.fire('Berhasil', res.message, 'success');
            load();
          })
          .fail(xhr => Swal.fire('Gagal', gagal(xhr, 'Penerimaan gagal disimpan.'), 'error'))
          .always(() => $btn.prop('disabled', false));
      });

      $(document).on('click', '.btnDeletePenerimaan', function() {
        const id = $(this).data('id');
        Swal.fire({
          title: 'Hapus penerimaan?',
          text: 'Stok akan dihitung ulang tanpa penerimaan ini.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Ya, hapus',
          cancelButtonText: 'Batal',
          confirmButtonColor: '#fa896b',
          reverseButtons: true
        }).then(r => {
          if (!r.isConfirmed) return;
          $.ajax({
              url: api + '?id_penerimaan=' + encodeURIComponent(id),
              type: 'DELETE',
              dataType: 'json'
            })
            .done(res => {
              Swal.fire('Berhasil', res.message, 'success');
              load();
            })
            .fail(xhr => Swal.fire('Gagal', gagal(xhr, 'Data penerimaan gagal dihapus.'), 'error'));
        });
      });

      load();
    });
  </script>
</body>

</html>
