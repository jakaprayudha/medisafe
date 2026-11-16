<?php
$title = 'Laporan Farmasi';
require '../../controller/view.php';
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .rf-stat {
      border: 1px solid #e5eaef;
      border-radius: 12px;
      background: #fff;
      padding: 14px 16px;
      height: 100%;
      transition: all .15s;
    }

    .rf-stat.rf-filter {
      cursor: pointer;
    }

    .rf-stat.rf-filter:hover {
      border-color: #5d87ff;
    }

    .rf-stat.active {
      border-color: #5d87ff;
      box-shadow: 0 0 0 3px rgba(93, 135, 255, .15);
    }

    .rf-stat .rf-icon {
      width: 38px;
      height: 38px;
      border-radius: 10px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 1rem;
      flex-shrink: 0;
    }

    .rf-stat .rf-val {
      font-size: 1.35rem;
      font-weight: 700;
      line-height: 1.2;
    }

    .rf-stat .rf-label {
      font-size: .75rem;
      color: #6c757d;
    }

    #rfTable td {
      font-size: .85rem;
    }

    #rfTable th {
      font-size: .78rem;
      white-space: nowrap;
    }

    #rfTable td.rf-num,
    #rfTable th.rf-num {
      text-align: right;
      white-space: nowrap;
    }

    #rfTable tbody tr.rf-habis td:first-child {
      box-shadow: inset 3px 0 0 #fa896b;
    }

    #rfTable tbody tr.rf-minimum td:first-child {
      box-shadow: inset 3px 0 0 #ffae1f;
    }

    .rf-loading {
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

          <!-- HEADER + FILTER -->
          <div class="card mb-3">
            <div class="card-body p-4">
              <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                  <h4 class="fw-semibold mb-1"><i class="fas fa-pills text-primary me-2"></i>Laporan Stok Farmasi</h4>
                  <div class="text-muted small" id="rfPeriodeLabel">-</div>
                </div>
                <div class="d-flex gap-2">
                  <button id="btnExcel" class="btn btn-success"><i class="fas fa-file-excel me-1"></i> Excel</button>
                  <button id="btnPrint" class="btn btn-primary"><i class="fas fa-print me-1"></i> Print</button>
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
                  <button type="button" class="btn btn-outline-secondary rf-range" data-range="today">Hari ini</button>
                  <button type="button" class="btn btn-outline-secondary rf-range" data-range="month">Bulan ini</button>
                  <button type="button" class="btn btn-outline-secondary rf-range" data-range="lastmonth">Bulan lalu</button>
                </div>
                <button type="button" id="btnApplyFilter" class="btn btn-dark"><i class="fas fa-search me-1"></i> Tampilkan</button>
              </div>
            </div>
          </div>

          <div id="rfError" class="alert alert-danger d-none" role="alert"></div>

          <!-- RINGKASAN (klik kartu status untuk memfilter tabel) -->
          <div class="row g-2 mb-3">
            <div class="col-6 col-md-4 col-xl">
              <div class="rf-stat rf-filter active d-flex gap-3 align-items-center" data-status="">
                <span class="rf-icon bg-primary-subtle text-primary"><i class="fas fa-boxes"></i></span>
                <div>
                  <div class="rf-val" id="sTotal">-</div>
                  <div class="rf-label">Semua obat</div>
                </div>
              </div>
            </div>
            <div class="col-6 col-md-4 col-xl">
              <div class="rf-stat rf-filter d-flex gap-3 align-items-center" data-status="Habis">
                <span class="rf-icon bg-danger-subtle text-danger"><i class="fas fa-times-circle"></i></span>
                <div>
                  <div class="rf-val text-danger" id="sHabis">-</div>
                  <div class="rf-label">Stok habis</div>
                </div>
              </div>
            </div>
            <div class="col-6 col-md-4 col-xl">
              <div class="rf-stat rf-filter d-flex gap-3 align-items-center" data-status="Di Bawah Minimum">
                <span class="rf-icon bg-warning-subtle text-warning"><i class="fas fa-arrow-down"></i></span>
                <div>
                  <div class="rf-val text-warning" id="sMin">-</div>
                  <div class="rf-label">Di bawah minimum</div>
                </div>
              </div>
            </div>
            <div class="col-6 col-md-4 col-xl">
              <div class="rf-stat rf-filter d-flex gap-3 align-items-center" data-status="Di Atas Maksimum">
                <span class="rf-icon bg-info-subtle text-info"><i class="fas fa-arrow-up"></i></span>
                <div>
                  <div class="rf-val text-info" id="sMax">-</div>
                  <div class="rf-label">Di atas maksimum</div>
                </div>
              </div>
            </div>
            <div class="col-6 col-md-4 col-xl">
              <div class="rf-stat d-flex gap-3 align-items-center">
                <span class="rf-icon bg-success-subtle text-success"><i class="fas fa-exchange-alt"></i></span>
                <div>
                  <div class="rf-val"><span class="text-success" id="sMasuk">-</span> <small class="text-muted">/</small> <span class="text-danger" id="sKeluar">-</span></div>
                  <div class="rf-label">Masuk / keluar periode</div>
                </div>
              </div>
            </div>
            <div class="col-12 col-md-4 col-xl">
              <div class="rf-stat d-flex gap-3 align-items-center">
                <span class="rf-icon bg-secondary-subtle text-secondary"><i class="fas fa-wallet"></i></span>
                <div>
                  <div class="rf-val" id="sNilai">-</div>
                  <div class="rf-label">Nilai stok akhir (harga beli)</div>
                </div>
              </div>
            </div>
          </div>

          <!-- TABEL -->
          <div class="card position-relative">
            <div class="rf-loading" id="rfLoading">
              <div class="text-center"><span class="spinner-border text-primary"></span>
                <div class="small text-muted mt-2">Memuat data...</div>
              </div>
            </div>
            <div class="card-body p-4">
              <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <h6 class="mb-0">Rincian Stok per Obat <span class="badge bg-light text-dark ms-1" id="rfShown">0</span></h6>
                <div class="d-flex gap-2 flex-wrap">
                  <select id="rfKategori" class="form-select form-select-sm" style="min-width:180px">
                    <option value="">Semua kategori</option>
                  </select>
                  <div class="form-check form-switch d-flex align-items-center gap-2 mb-0">
                    <input class="form-check-input" type="checkbox" id="rfAktif">
                    <label class="form-check-label small" for="rfAktif">Hanya yang ada transaksi</label>
                  </div>
                </div>
              </div>
              <div class="table-responsive">
                <table class="table align-middle mb-0 w-100" id="rfTable">
                  <thead class="table-light">
                    <tr>
                      <th>Nama Obat</th>
                      <th>Kategori</th>
                      <th class="text-center">Satuan</th>
                      <th class="rf-num">Stok Awal</th>
                      <th class="rf-num">Masuk</th>
                      <th class="rf-num">Keluar</th>
                      <th class="rf-num">Stok Akhir</th>
                      <th class="rf-num">Min / Max</th>
                      <th class="rf-num">Nilai Stok</th>
                      <th class="text-center">Status</th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                  <tfoot class="table-light fw-semibold">
                    <tr>
                      <td colspan="4">Total (sesuai filter)</td>
                      <td class="rf-num text-success" id="fMasuk">-</td>
                      <td class="rf-num text-danger" id="fKeluar">-</td>
                      <td></td>
                      <td></td>
                      <td class="rf-num" id="fNilai">-</td>
                      <td></td>
                    </tr>
                  </tfoot>
                </table>
              </div>
              <div class="small text-muted mt-3">
                <i class="fas fa-info-circle me-1"></i>
                <b>Stok Awal</b> = stok pada awal periode. <b>Stok Akhir</b> = Stok Awal + Masuk − Keluar, posisi di tanggal akhir periode.
                Masuk dari penerimaan obat, keluar dari resep klinik ini. Status dihitung dari stok akhir.
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
  <?php require 'library.php'; ?>

  <script>
    $(function() {
      const apiUrl = 'controller/report/farmasiController.php';
      const num = v => (parseFloat(v) || 0).toLocaleString('id-ID', {
        maximumFractionDigits: 2
      });
      const rp = v => 'Rp ' + Math.round(parseFloat(v) || 0).toLocaleString('id-ID');
      const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
      }[c]));
      // isian yang hanya tanda baca (".", "'", "-") dianggap kosong
      const isi = v => /[\p{L}\p{N}]/u.test(String(v ?? '')) ? String(v).trim() : '';
      const STATUS = {
        'Habis': ['bg-danger-subtle text-danger', 'fa-times-circle'],
        'Di Bawah Minimum': ['bg-warning-subtle text-warning', 'fa-arrow-down'],
        'Di Atas Maksimum': ['bg-info-subtle text-info', 'fa-arrow-up'],
        'Normal': ['bg-success-subtle text-success', 'fa-check-circle']
      };

      // ---------- periode ----------
      const ymd = d => d.toLocaleDateString('sv-SE', {
        timeZone: 'Asia/Jakarta'
      });
      const today = ymd(new Date());
      const fmtTgl = s => new Date(s + 'T00:00:00').toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
      });

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

      function periodeValid() {
        const from = $('#fromDate').val(),
          to = $('#toDate').val();
        if (!from || !to) {
          Swal.fire('Periode belum lengkap', 'Silakan pilih tanggal mulai dan tanggal akhir.', 'warning');
          return false;
        }
        if (from > to) {
          Swal.fire('Periode tidak valid', 'Tanggal mulai tidak boleh lebih besar dari tanggal akhir.', 'warning');
          return false;
        }
        return true;
      }

      // ---------- filter tambahan (status, kategori, ada transaksi) ----------
      let filterStatus = '';
      $.fn.dataTable.ext.search.push(function(settings, _data, _i, row) {
        if (settings.nTable.id !== 'rfTable') return true;
        if (filterStatus && row.status_stock !== filterStatus) return false;
        const kat = $('#rfKategori').val();
        if (kat && (isi(row.pharmacy_category) || '-') !== kat) return false;
        if ($('#rfAktif').is(':checked') && !(+row.stok_masuk || +row.stok_keluar)) return false;
        return true;
      });

      // ---------- tabel ----------
      const table = $('#rfTable').DataTable({
        data: [],
        deferRender: true,
        pageLength: 25,
        order: [[0, 'asc']],
        language: {
          search: 'Cari obat:',
          lengthMenu: 'Tampilkan _MENU_',
          info: '_START_–_END_ dari _TOTAL_ obat',
          infoEmpty: '0 obat',
          infoFiltered: '(dari _MAX_)',
          emptyTable: 'Tidak ada data.',
          zeroRecords: 'Obat tidak ditemukan.',
          paginate: {
            next: '›',
            previous: '‹'
          }
        },
        columns: [{
            data: 'pharmacy_name_generic',
            render: (v, t, r) => {
              const sub = [isi(r.pharmacy_name_trade), isi(r.pharmacy_code)].filter(Boolean);
              return t !== 'display' ? `${v || ''} ${sub.join(' ')}` :
                `<div class="fw-semibold">${esc(v || '-')}</div>` + (sub.length ? `<div class="small text-muted">${sub.map(esc).join(' • ')}</div>` : '');
            }
          },
          {
            data: 'pharmacy_category',
            render: v => esc(isi(v) || '-')
          },
          {
            data: 'pharmacy_unit',
            className: 'text-center',
            render: v => esc(isi(v) || '-')
          },
          {
            data: 'stok_awal',
            searchable: false,
            className: 'rf-num',
            render: (v, t) => t === 'display' ? num(v) : +v
          },
          {
            data: 'stok_masuk',
            searchable: false,
            className: 'rf-num',
            render: (v, t) => t !== 'display' ? +v : (+v > 0 ? `<span class="text-success fw-semibold">+${num(v)}</span>` : '<span class="text-muted">0</span>')
          },
          {
            data: 'stok_keluar',
            searchable: false,
            className: 'rf-num',
            render: (v, t) => t !== 'display' ? +v : (+v > 0 ? `<span class="text-danger fw-semibold">−${num(v)}</span>` : '<span class="text-muted">0</span>')
          },
          {
            data: 'stok_akhir',
            searchable: false,
            className: 'rf-num',
            render: (v, t) => t !== 'display' ? +v : `<span class="fw-bold ${+v <= 0 ? 'text-danger' : ''}">${num(v)}</span>`
          },
          {
            data: null,
            className: 'rf-num text-muted',
            orderable: false,
            searchable: false,
            render: r => (+r.stok_min || +r.stok_max) ? `${num(r.stok_min)} / ${num(r.stok_max)}` : '-'
          },
          {
            data: 'nilai_stok',
            searchable: false,
            className: 'rf-num',
            render: (v, t) => t === 'display' ? rp(v) : +v
          },
          {
            data: 'status_stock',
            className: 'text-center',
            render: (v, t) => {
              if (t !== 'display') return v;
              const s = STATUS[v] || STATUS.Normal;
              return `<span class="badge ${s[0]}"><i class="fas ${s[1]} me-1"></i>${esc(v)}</span>`;
            }
          }
        ],
        createdRow: (row, d) => {
          if (d.status_code === 'habis') row.classList.add('rf-habis');
          if (d.status_code === 'minimum') row.classList.add('rf-minimum');
        },
        drawCallback: function() {
          const api = this.api();
          const rows = api.rows({
            search: 'applied'
          }).data().toArray();
          const sum = k => rows.reduce((a, r) => a + (parseFloat(r[k]) || 0), 0);
          $('#fMasuk').text('+' + num(sum('stok_masuk')));
          $('#fKeluar').text('−' + num(sum('stok_keluar')));
          $('#fNilai').text(rp(sum('nilai_stok')));
          $('#rfShown').text(rows.length.toLocaleString('id-ID'));
        }
      });

      // ---------- muat data ----------
      function load() {
        if (!periodeValid()) return;
        const from = $('#fromDate').val(),
          to = $('#toDate').val();
        $('#rfError').addClass('d-none');
        $('#rfLoading').css('display', 'flex');
        $('#btnApplyFilter').prop('disabled', true);
        $.getJSON(apiUrl, {
            fromDate: from,
            toDate: to
          })
          .done(json => {
            if (json.status !== 'success') {
              $('#rfError').text(json.message || 'Gagal mengambil data.').removeClass('d-none');
              table.clear().draw();
              return;
            }
            const s = json.ringkasan || {};
            $('#sTotal').text(num(s.total_item));
            $('#sHabis').text(num(s.habis));
            $('#sMin').text(num(s.minimum));
            $('#sMax').text(num(s.maximum));
            $('#sMasuk').text(num(s.stok_masuk));
            $('#sKeluar').text(num(s.stok_keluar));
            $('#sNilai').text(rp(s.nilai_stok));
            $('#rfPeriodeLabel').text(`Periode ${fmtTgl(from)} s/d ${fmtTgl(to)} • ${num(json.total)} obat aktif`);

            const kategori = [...new Set(json.data.map(r => isi(r.pharmacy_category) || '-'))].sort();
            const pilih = $('#rfKategori').val();
            $('#rfKategori').html('<option value="">Semua kategori</option>' + kategori.map(k => `<option>${esc(k)}</option>`).join(''));
            if (kategori.includes(pilih)) $('#rfKategori').val(pilih);

            table.clear().rows.add(json.data).draw();
          })
          .fail(() => $('#rfError').text('Gagal terhubung ke server.').removeClass('d-none'))
          .always(() => {
            $('#rfLoading').hide();
            $('#btnApplyFilter').prop('disabled', false);
          });
      }

      $('#btnApplyFilter').on('click', load);
      $('.rf-range').on('click', function() {
        setRange($(this).data('range'));
        load();
      });
      $('.rf-filter').on('click', function() {
        $('.rf-filter').removeClass('active');
        $(this).addClass('active');
        filterStatus = $(this).data('status') || '';
        table.draw();
      });
      $('#rfKategori, #rfAktif').on('change', () => table.draw());

      // ---------- Excel / Print ----------
      // filter & urutan yang sedang tampil ikut ke Excel/Print agar isinya sama dengan tabel
      const SORT_KOLOM = ['nama', 'kategori', 'satuan', 'stok_awal', 'masuk', 'keluar', 'stok_akhir', null, 'nilai', 'status'];
      const urlLaporan = file => {
        const [kolom, arah] = table.order()[0] || [0, 'asc'];
        const p = new URLSearchParams({
          fromDate: $('#fromDate').val(),
          toDate: $('#toDate').val(),
          status: filterStatus,
          kategori: $('#rfKategori').val(),
          aktif: $('#rfAktif').is(':checked') ? 1 : 0,
          q: table.search(),
          sort: SORT_KOLOM[kolom] || 'nama',
          dir: arah
        });
        return `module/report/${file}?${p}`;
      };
      $('#btnExcel').on('click', () => periodeValid() && (window.location.href = urlLaporan('export_farmasi.php')));
      $('#btnPrint').on('click', () => periodeValid() && window.open(urlLaporan('print_farmasi.php'), '_blank'));

      load();
    });
  </script>
</body>

</html>
