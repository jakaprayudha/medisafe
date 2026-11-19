<?php
$title = 'Master Obat & BMHP';
require '../../controller/view.php';
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

    .ph-stat {
      border: 1px solid #e5eaef;
      border-radius: 12px;
      background: #fff;
      padding: 14px 16px;
      height: 100%;
      cursor: pointer;
      transition: border-color .15s;
    }

    .ph-stat:hover,
    .ph-stat.active {
      border-color: #5d87ff;
    }

    .ph-icon {
      width: 40px;
      height: 40px;
      border-radius: 10px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 1.05rem;
      flex-shrink: 0;
    }

    .ph-val {
      font-size: 1.35rem;
      font-weight: 700;
      line-height: 1.2;
      color: #2a3547;
    }

    .ph-label {
      font-size: .75rem;
      color: #6c757d;
    }

    .ph-chips .btn {
      border-radius: 20px;
      font-size: .8rem;
      padding: .3rem .85rem;
    }

    .ph-chips .badge {
      font-weight: 600;
    }

    .ph-search {
      width: 260px;
    }

    @media (max-width: 767.98px) {

      .ph-tools,
      .ph-search {
        width: 100%;
      }

      .ph-search {
        flex: 1;
      }
    }

    #obatTable {
      width: 100% !important;
    }

    #obatTable thead th {
      font-size: .74rem;
      font-weight: 600;
      color: #5a6a85;
      text-transform: uppercase;
      letter-spacing: .3px;
      background: #f6f9fc;
      border-bottom: 0;
      white-space: nowrap;
    }

    #obatTable td {
      font-size: .85rem;
      vertical-align: middle;
      padding-top: .6rem;
      padding-bottom: .6rem;
    }

    #obatTable tr.off td {
      opacity: .55;
    }

    .ph-ava {
      width: 38px;
      height: 38px;
      border-radius: 10px;
      flex-shrink: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: .95rem;
    }

    .ph-name {
      font-weight: 600;
      color: #2a3547;
      max-width: 300px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .ph-sub {
      font-size: .74rem;
      color: #7c8fac;
      max-width: 340px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .ph-badge {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      font-size: .7rem;
      font-weight: 600;
      padding: .25em .6em;
      border-radius: 6px;
      white-space: nowrap;
    }

    .ph-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      display: inline-block;
    }

    .ph-act {
      white-space: nowrap;
    }

    .ph-act .btn {
      width: 32px;
      height: 32px;
      padding: 0;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 8px;
    }

    .ph-loading {
      position: absolute;
      inset: 0;
      background: rgba(255, 255, 255, .6);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 5;
      border-radius: 12px;
    }

    .opt-grid input {
      display: none;
    }

    .opt-grid label {
      border: 1.5px solid #e5eaef;
      border-radius: 10px;
      padding: 7px 10px;
      cursor: pointer;
      width: 100%;
      text-align: center;
      font-weight: 600;
      font-size: .8rem;
      margin: 0;
      transition: all .15s;
    }

    .opt-grid input:checked+label {
      border-color: var(--c, #5d87ff);
      background: var(--bg, #ecf2ff);
      color: var(--c, #5d87ff);
    }

    .src-switch {
      display: flex;
      background: #f2f5f9;
      border-radius: 10px;
      padding: 3px;
    }

    .src-switch button {
      flex: 1;
      border: 0;
      background: none;
      border-radius: 8px;
      padding: 6px 10px;
      font-size: .82rem;
      font-weight: 600;
      color: #5a6a85;
    }

    .src-switch button.active {
      background: #fff;
      color: #5d87ff;
      box-shadow: 0 1px 4px rgba(42, 53, 71, .1);
    }

    #programModal .select2-container {
      width: 100% !important;
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
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
            <div>
              <h4 class="fw-semibold mb-1">Master Obat &amp; BMHP</h4>
              <div class="text-muted small">Daftar obat, BMHP dan alat kesehatan yang dipakai faskes untuk resep dan stok.</div>
            </div>
            <div class="d-flex gap-2">
              <button class="btn btn-light" id="btnRefresh" title="Muat ulang"><i class="fas fa-sync-alt"></i></button>
              <button class="btn btn-primary" id="btnTambah"><i class="fas fa-plus me-1"></i> Tambah Obat</button>
            </div>
          </div>

          <!-- STAT -->
          <div class="row g-3 mb-3">
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="ph-stat d-flex gap-3 align-items-center" data-kat="">
                <span class="ph-icon bg-primary-subtle text-primary"><i class="fas fa-prescription-bottle-alt"></i></span>
                <div class="min-w-0">
                  <div class="ph-val" id="stTotal">-</div>
                  <div class="ph-label" id="stTotalSub">Total item</div>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="ph-stat d-flex gap-3 align-items-center" data-kat="obat">
                <span class="ph-icon bg-success-subtle text-success"><i class="fas fa-pills"></i></span>
                <div class="min-w-0">
                  <div class="ph-val" id="stObat">-</div>
                  <div class="ph-label">Obat</div>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="ph-stat d-flex gap-3 align-items-center" data-kat="bmhp">
                <span class="ph-icon bg-info-subtle text-info"><i class="fas fa-syringe"></i></span>
                <div class="min-w-0">
                  <div class="ph-val" id="stBmhp">-</div>
                  <div class="ph-label">BMHP &amp; Alkes</div>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="ph-stat d-flex gap-3 align-items-center" data-gol="keras">
                <span class="ph-icon bg-danger-subtle text-danger"><i class="fas fa-exclamation-triangle"></i></span>
                <div class="min-w-0">
                  <div class="ph-val" id="stKeras">-</div>
                  <div class="ph-label">Obat keras / psikotropika / narkotika</div>
                </div>
              </div>
            </div>
          </div>

          <!-- TABEL -->
          <div class="card position-relative">
            <div class="ph-loading" id="phLoading"><div class="spinner-border text-primary"></div></div>
            <div class="card-body p-4">
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div class="ph-chips d-flex flex-wrap gap-2" id="phChips">
                  <button class="btn btn-primary" data-kat="">Semua <span class="badge bg-white text-primary ms-1" data-n=""></span></button>
                  <button class="btn btn-light" data-kat="obat">Obat <span class="badge bg-white text-dark ms-1" data-n="obat"></span></button>
                  <button class="btn btn-light" data-kat="bmhp">BMHP &amp; Alkes <span class="badge bg-white text-dark ms-1" data-n="bmhp"></span></button>
                  <button class="btn btn-light" data-kat="lain">Lainnya <span class="badge bg-white text-dark ms-1" data-n="lain"></span></button>
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center ph-tools">
                  <select id="fGol" class="form-select form-select-sm" style="width:auto">
                    <option value="">Semua golongan</option>
                    <option value="Bebas">Bebas</option>
                    <option value="Bebas Terbatas">Bebas Terbatas</option>
                    <option value="Keras">Keras</option>
                    <option value="Psikotropika">Psikotropika</option>
                    <option value="Narkotika">Narkotika</option>
                    <option value="keras">Perlu resep (keras+)</option>
                    <option value="-">Belum diisi</option>
                  </select>
                  <select id="fSumber" class="form-select form-select-sm" style="width:auto">
                    <option value="">Semua sumber</option>
                    <option value="faskes">Ditambahkan faskes</option>
                    <option value="global">Master bawaan</option>
                  </select>
                  <div class="input-group input-group-sm ph-search">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="search" id="phSearch" class="form-control" placeholder="Cari nama generik / dagang / kode" autocomplete="off">
                  </div>
                </div>
              </div>

              <div class="table-responsive">
                <table class="table align-middle mb-0" id="obatTable">
                  <thead>
                    <tr>
                      <th>Obat</th>
                      <th>Kategori</th>
                      <th>Golongan</th>
                      <th class="text-center">Satu Sehat</th>
                      <th class="text-center" style="width:160px">Aksi</th>
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

  <!-- MODAL TAMBAH -->
  <div class="modal fade" id="programModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <form id="programForm" class="modal-content" novalidate>
        <div class="modal-header">
          <h5 class="modal-title d-flex align-items-center gap-2"><i class="fas fa-prescription-bottle-alt text-primary"></i> Tambah Obat</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="pharmacy_code" id="pharmacy_code">
          <input type="hidden" name="id_pharmacy" id="id_pharmacy">

          <div class="row g-3">
            <div class="col-12">
              <label class="form-label required">Nama Generik</label>
              <div class="src-switch mb-2" id="srcSwitch">
                <button type="button" class="active" data-src="manual"><i class="fas fa-keyboard me-1"></i>Isi manual</button>
                <button type="button" data-src="bpjs"><i class="fas fa-id-card me-1"></i>Ambil dari daftar obat BPJS</button>
              </div>
              <input type="text" id="pharmacy_name_generic" name="pharmacy_name_generic" class="form-control" placeholder="cth. Paracetamol 500 mg" maxlength="255">
              <div id="wrapBpjs" class="d-none">
                <select id="kdObat" class="form-select">
                  <option value="">Pilih obat BPJS</option>
                </select>
                <div class="form-text" id="kdObatInfo">Ketik minimal 3 huruf untuk mencari di PCare.</div>
              </div>
            </div>
            <div class="col-md-8">
              <label class="form-label required" for="pharmacy_name_trade">Nama Pabrikan / Dagang</label>
              <input type="text" id="pharmacy_name_trade" name="pharmacy_name_trade" class="form-control" maxlength="255" placeholder="cth. Sanmol (isi - bila tidak ada)">
            </div>
            <div class="col-md-4">
              <label class="form-label" for="pharmacy_sub_category">Sub Kategori</label>
              <input type="text" id="pharmacy_sub_category" name="pharmacy_sub_category" class="form-control" placeholder="cth. Tablet, Sirup" list="listSub">
              <datalist id="listSub">
                <option value="Tablet">
                <option value="Kapsul">
                <option value="Sirup">
                <option value="Krim/Salep">
                <option value="Injeksi">
                <option value="Tetes">
              </datalist>
            </div>

            <div class="col-12">
              <label class="form-label required">Kategori</label>
              <div class="row g-2 opt-grid">
                <div class="col-4"><input type="radio" name="pharmacy_category" id="katObat" value="Obat"><label for="katObat" style="--c:#13deb9;--bg:#e6fffa"><i class="fas fa-pills me-1"></i>Obat</label></div>
                <div class="col-4"><input type="radio" name="pharmacy_category" id="katBmhp" value="BMHP"><label for="katBmhp" style="--c:#49beff;--bg:#e8f7ff"><i class="fas fa-syringe me-1"></i>BMHP</label></div>
                <div class="col-4"><input type="radio" name="pharmacy_category" id="katAlkes" value="Alkes"><label for="katAlkes" style="--c:#7460ee;--bg:#efecfd"><i class="fas fa-stethoscope me-1"></i>Alkes</label></div>
              </div>
            </div>

            <div class="col-12">
              <label class="form-label required">Golongan</label>
              <div class="row g-2 opt-grid">
                <div class="col-6 col-md"><input type="radio" name="pharmcy_golongan" id="golB" value="Bebas"><label for="golB" style="--c:#13a67a;--bg:#e6fffa">Bebas</label></div>
                <div class="col-6 col-md"><input type="radio" name="pharmcy_golongan" id="golBT" value="Bebas Terbatas"><label for="golBT" style="--c:#2e7dd7;--bg:#e8f2ff">Bebas Terbatas</label></div>
                <div class="col-4 col-md"><input type="radio" name="pharmcy_golongan" id="golK" value="Keras"><label for="golK" style="--c:#e0473b;--bg:#fdede8">Keras</label></div>
                <div class="col-4 col-md"><input type="radio" name="pharmcy_golongan" id="golP" value="Psikotropika"><label for="golP" style="--c:#7460ee;--bg:#efecfd">Psikotropika</label></div>
                <div class="col-4 col-md"><input type="radio" name="pharmcy_golongan" id="golN" value="Narkotika"><label for="golN" style="--c:#2a3547;--bg:#e5eaef">Narkotika</label></div>
              </div>
            </div>

            <div class="col-12">
              <label class="form-label required">Jenis</label>
              <div class="row g-2 opt-grid">
                <div class="col-4"><input type="radio" name="pharmcy_jenis_drugs" id="jnsG" value="Generik"><label for="jnsG">Generik</label></div>
                <div class="col-4"><input type="radio" name="pharmcy_jenis_drugs" id="jnsP" value="Paten"><label for="jnsP">Paten</label></div>
                <div class="col-4"><input type="radio" name="pharmcy_jenis_drugs" id="jnsN" value="Non-Generik"><label for="jnsN">Non-Generik</label></div>
              </div>
            </div>
          </div>
          <div class="small text-muted mt-3"><i class="fas fa-info-circle me-1"></i>Harga, satuan, KFA Satu Sehat dan dokumen dilengkapi di halaman <b>Detail</b> setelah obat disimpan.</div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary" id="btnSimpanObat"><i class="fas fa-save me-1"></i> Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <?php require 'library.php'; ?>
  <script>
    (function() {
      const apiUrl = 'controller/master/pharmacyController';
      const GOL = {
        'Bebas': ['#13a67a', '#e6fffa'],
        'Bebas Terbatas': ['#2e7dd7', '#e8f2ff'],
        'Keras': ['#e0473b', '#fdede8'],
        'Psikotropika': ['#7460ee', '#efecfd'],
        'Narkotika': ['#2a3547', '#e5eaef'],
      };
      const KERAS = ['Keras', 'Psikotropika', 'Narkotika'];
      let semua = [],
        fKat = '',
        sumberNama = 'manual';

      const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
      } [c]));
      const fmt = n => (Number(n) || 0).toLocaleString('id-ID');
      const isi = v => {
        const s = String(v ?? '').trim();
        return /^(-+|null|na|n\/a)$/i.test(s) ? '' : s;
      };
      const toast = (icon, title) => Swal.fire({
        toast: true,
        position: 'top-end',
        icon,
        title,
        showConfirmButton: false,
        timer: 2200,
        timerProgressBar: true
      });
      const grupKat = r => {
        const k = String(r.pharmacy_category || '').toLowerCase();
        if (k === 'bmhp' || k.startsWith('alkes') || k.startsWith('alat kesehatan')) return 'bmhp';
        if (k.includes('obat') || k === 'ethical' || k.includes('suplemen')) return 'obat';
        return 'lain';
      };

      // ---------- tabel ----------
      const table = $('#obatTable').DataTable({
        data: [],
        autoWidth: false,
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        order: [
          [0, 'asc']
        ],
        dom: 'rt<"d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2"<"d-flex align-items-center gap-2"li>p>',
        language: {
          info: 'Menampilkan _START_-_END_ dari _TOTAL_ item',
          infoEmpty: 'Tidak ada item',
          infoFiltered: '(dari _MAX_)',
          lengthMenu: '_MENU_',
          emptyTable: '<div class="py-4 text-muted"><i class="fas fa-prescription-bottle-alt fa-2x mb-2 d-block"></i>Belum ada obat. Klik <b>Tambah Obat</b>.</div>',
          zeroRecords: '<div class="py-4 text-muted"><i class="fas fa-search fa-2x mb-2 d-block"></i>Obat tidak ditemukan.</div>',
          paginate: {
            previous: '‹',
            next: '›'
          }
        },
        createdRow: (tr, r) => {
          if (String(r.pharmacy_status) === '0') tr.classList.add('off');
        },
        columns: [{
          data: 'pharmacy_name_generic',
          render: (d, t, r) => {
            const gen = isi(r.pharmacy_name_generic) || '-';
            const trade = isi(r.pharmacy_name_trade);
            if (t !== 'display') return t === 'sort' ? gen.toLowerCase() : `${gen} ${trade} ${r.pharmacy_code || ''} ${r.pharmacy_number || ''}`;
            const g = grupKat(r);
            const [ic, cls] = g === 'bmhp' ? ['fa-syringe', 'bg-info-subtle text-info'] : g === 'obat' ? ['fa-pills', 'bg-success-subtle text-success'] : ['fa-box', 'bg-secondary-subtle text-secondary'];
            const kode = [isi(r.pharmacy_code) && `BPJS ${esc(r.pharmacy_code)}`, esc(r.pharmacy_number || '')].filter(Boolean).join(' · ');
            return `<div class="d-flex align-items-center gap-3">
              <span class="ph-ava ${cls}"><i class="fas ${ic}"></i></span>
              <div class="min-w-0">
                <a href="module/admin/pharmacy_details?no=${encodeURIComponent(r.id_pharmacy)}" class="ph-name d-block text-reset" title="${esc(gen)}">${esc(gen)}</a>
                <div class="ph-sub" title="${esc(trade)}">${trade ? esc(trade) + ' · ' : ''}${kode}</div>
              </div>
            </div>`;
          }
        }, {
          data: 'pharmacy_category',
          render: (d, t, r) => {
            if (t !== 'display') return `${d || ''} ${r.pharmacy_sub_category || ''} ${r.pharmcy_jenis_drugs || ''}`;
            const sub = [isi(r.pharmacy_sub_category), isi(r.pharmcy_jenis_drugs)].filter(Boolean).join(' · ');
            return `<div class="fw-semibold" style="font-size:.82rem">${esc(isi(d) || '-')}</div>${sub ? `<div class="ph-sub">${esc(sub)}</div>` : ''}`;
          }
        }, {
          data: 'pharmcy_golongan',
          render: (d, t) => {
            if (t !== 'display') return d || '';
            const c = GOL[d];
            return c ? `<span class="ph-badge" style="background:${c[1]};color:${c[0]}"><span class="ph-dot" style="background:${c[0]}"></span>${esc(d)}</span>` :
              '<span class="ph-sub">Belum diisi</span>';
          }
        }, {
          data: 'kfa_code',
          className: 'text-center',
          render: (d, t) => {
            const k = isi(d);
            if (t !== 'display') return k ? 1 : 0;
            return k ? `<span class="ph-badge bg-success-subtle text-success" title="Kode KFA ${esc(k)}"><i class="fas fa-check-circle"></i>KFA</span>` :
              '<span class="ph-badge bg-light text-muted" title="Belum dipetakan ke KFA Satu Sehat">Belum</span>';
          }
        }, {
          data: null,
          orderable: false,
          className: 'text-center',
          render: (d, t, r) => `<div class="ph-act d-flex gap-1 justify-content-center">
              <a class="btn btn-light text-warning" href="module/admin/pharmacy_stock?no=${encodeURIComponent(r.id_pharmacy)}" title="Stok"><i class="fas fa-boxes-stacked"></i></a>
              <a class="btn btn-light text-primary" href="module/admin/pharmacy_usage?id_pharmacy=${r.id_pharmacy}" title="Pasien pengguna obat"><i class="fas fa-users"></i></a>
              <a class="btn btn-light text-info" href="module/admin/pharmacy_details?no=${encodeURIComponent(r.id_pharmacy)}" title="Detail & ubah"><i class="fas fa-pen"></i></a>
              <button class="btn btn-light text-danger delete-btn" data-id="${r.id_pharmacy}" data-nama="${esc(isi(r.pharmacy_name_generic))}" data-sumber="${r.sumber}" title="Hapus dari daftar"><i class="fas fa-trash"></i></button>
            </div>`
        }]
      });

      // filter kustom (kategori, golongan, sumber)
      $.fn.dataTable.ext.search.push((settings, _row, idx) => {
        if (settings.nTable.id !== 'obatTable') return true;
        const r = table.row(idx).data();
        if (fKat && grupKat(r) !== fKat) return false;
        const g = $('#fGol').val();
        if (g === 'keras' && !KERAS.includes(r.pharmcy_golongan)) return false;
        if (g === '-' && GOL[r.pharmcy_golongan]) return false;
        if (g && g !== 'keras' && g !== '-' && r.pharmcy_golongan !== g) return false;
        const s = $('#fSumber').val();
        if (s && r.sumber !== s) return false;
        return true;
      });

      function load() {
        $('#phLoading').css('display', 'flex');
        fetch(apiUrl).then(r => r.json()).then(r => {
          if (r.status !== 'success') return Swal.fire('Gagal', r.message || 'Gagal memuat data obat.', 'error');
          semua = r.data || [];
          table.clear().rows.add(semua).draw();
          renderStats();
        }).catch(() => Swal.fire('Gagal', 'Tidak dapat terhubung ke server.', 'error'))
          .finally(() => $('#phLoading').hide());
      }

      function renderStats() {
        const n = {
          '': semua.length,
          obat: 0,
          bmhp: 0,
          lain: 0
        };
        let keras = 0,
          faskes = 0;
        semua.forEach(r => {
          n[grupKat(r)]++;
          if (KERAS.includes(r.pharmcy_golongan)) keras++;
          if (r.sumber === 'faskes') faskes++;
        });
        $('#stTotal').text(fmt(semua.length));
        $('#stTotalSub').text(`Total item · ${fmt(faskes)} ditambahkan faskes`);
        $('#stObat').text(fmt(n.obat));
        $('#stBmhp').text(fmt(n.bmhp));
        $('#stKeras').text(fmt(keras));
        Object.entries(n).forEach(([k, v]) => $(`#phChips [data-n="${k}"]`).text(fmt(v)));
        $('#phChips [data-kat="lain"]').toggleClass('d-none', !n.lain);
      }

      function setKat(k) {
        fKat = k;
        $('#phChips .btn').each(function() {
          const on = String($(this).data('kat')) === k;
          $(this).toggleClass('btn-primary', on).toggleClass('btn-light', !on)
            .find('.badge').toggleClass('text-primary', on).toggleClass('text-dark', !on);
        });
        $('.ph-stat').removeClass('active');
        if (k) $(`.ph-stat[data-kat="${k}"]`).addClass('active');
        table.draw();
      }
      $('#phChips').on('click', '.btn', function() {
        if ($('#fGol').val() === 'keras') $('#fGol').val('');
        setKat(String($(this).data('kat')));
      });
      $('.ph-stat').on('click', function() {
        if (this.dataset.gol) {
          setKat('');
          $('#fGol').val('keras');
          $(this).addClass('active');
          table.draw();
        } else {
          $('#fGol').val('');
          setKat(this.dataset.kat || '');
        }
      });
      let tCari;
      $('#phSearch').on('input', function() {
        clearTimeout(tCari);
        tCari = setTimeout(() => table.search(this.value.trim()).draw(), 200);
      });
      $('#customSearch').on('keyup', function() {
        $('#phSearch').val(this.value).trigger('input');
      });
      $('#fGol, #fSumber').on('change', () => {
        $('.ph-stat[data-gol]').toggleClass('active', $('#fGol').val() === 'keras');
        table.draw();
      });
      $('#btnRefresh').on('click', load);

      // ---------- form tambah ----------
      function setSumber(src) {
        sumberNama = src;
        $('#srcSwitch button').removeClass('active').filter(`[data-src="${src}"]`).addClass('active');
        $('#pharmacy_name_generic').toggleClass('d-none', src === 'bpjs');
        $('#wrapBpjs').toggleClass('d-none', src !== 'bpjs');
        if (src === 'manual') {
          $('#kdObat').val(null).trigger('change');
          $('#pharmacy_code').val('');
        } else {
          $('#pharmacy_name_generic').val('');
          setTimeout(() => $('#kdObat').select2('open'), 150);
        }
      }
      $('#srcSwitch').on('click', 'button', function() {
        setSumber(this.dataset.src);
      });

      $('#kdObat').select2({
        dropdownParent: $('#programModal'),
        placeholder: 'Cari obat BPJS...',
        width: '100%',
        minimumInputLength: 3,
        language: {
          inputTooShort: a => 'Ketik minimal ' + a.minimum + ' karakter',
          searching: () => 'Mencari obat...',
          noResults: () => 'Obat tidak ditemukan',
          errorLoading: () => 'Gagal memuat daftar obat BPJS'
        },
        ajax: {
          url: 'controller/admisi/services/getObat.php',
          dataType: 'json',
          delay: 350,
          data: p => ({
            keyword: p.term
          }),
          processResults: data => ({
            results: (data && Array.isArray(data.data) ? data.data : []).map(item => ({
              id: item.kdObat,
              text: item.nmObat
            }))
          }),
          cache: true
        }
      }).on('select2:select', function(e) {
        $('#pharmacy_code').val(e.params.data.id);
        $('#pharmacy_name_generic').val(e.params.data.text);
        $('#kdObatInfo').html(`Kode obat BPJS: <b>${esc(e.params.data.id)}</b>`);
      });

      $('#btnTambah').on('click', function() {
        const f = $('#programForm')[0];
        f.reset();
        $('#id_pharmacy, #pharmacy_code').val('');
        $('#kdObatInfo').text('Ketik minimal 3 huruf untuk mencari di PCare.');
        setSumber('manual');
        $('#programModal').modal('show');
      });
      $('#programModal').on('shown.bs.modal', () => {
        if (sumberNama === 'manual') $('#pharmacy_name_generic').trigger('focus');
      });

      $('#programForm').on('submit', function(e) {
        e.preventDefault();
        const nama = $('#pharmacy_name_generic').val().trim();
        const cek = [
          [!nama, sumberNama === 'bpjs' ? 'Pilih obat dari daftar BPJS.' : 'Isi nama generik.'],
          [!$('#pharmacy_name_trade').val().trim(), 'Isi nama pabrikan/dagang (isi - bila tidak ada).'],
          [!$('input[name="pharmacy_category"]:checked').length, 'Pilih kategori.'],
          [!$('input[name="pharmcy_golongan"]:checked').length, 'Pilih golongan.'],
          [!$('input[name="pharmcy_jenis_drugs"]:checked').length, 'Pilih jenis.'],
        ].find(([gagal]) => gagal);
        if (cek) return Swal.fire('Lengkapi data', cek[1], 'warning');

        const $btn = $('#btnSimpanObat').prop('disabled', true);
        fetch(apiUrl, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams(new FormData(this))
          })
          .then(r => r.json())
          .then(r => {
            if (r.status !== 'success') return Swal.fire('Gagal', r.message || 'Gagal menyimpan.', 'error');
            $('#programModal').modal('hide');
            toast('success', r.message || 'Obat ditambahkan');
            $('#phSearch').val(nama);
            table.search(nama);
            load();
          })
          .catch(() => Swal.fire('Gagal', 'Tidak dapat terhubung ke server.', 'error'))
          .finally(() => $btn.prop('disabled', false));
      });

      // ---------- hapus ----------
      $(document).on('click', '.delete-btn', function() {
        const id = $(this).data('id');
        const global = $(this).data('sumber') === 'global';
        Swal.fire({
          title: 'Hapus dari daftar?',
          html: `<b>${esc($(this).data('nama'))}</b> tidak akan muncul lagi di daftar obat faskes Anda.` +
            (global ? '<div class="small text-muted mt-2">Item ini master bawaan; faskes lain tidak terpengaruh.</div>' : '') +
            '<div class="small text-muted mt-1">Riwayat resep yang sudah memakai obat ini tetap tersimpan.</div>',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Hapus',
          cancelButtonText: 'Batal',
          confirmButtonColor: '#fa896b'
        }).then(res => {
          if (!res.isConfirmed) return;
          fetch(`${apiUrl}?id=${encodeURIComponent(id)}`, {
              method: 'DELETE'
            })
            .then(r => r.json())
            .then(r => {
              if (r.status !== 'success') return Swal.fire('Gagal', r.message || 'Gagal menghapus.', 'error');
              toast('success', r.message || 'Obat dihapus');
              semua = semua.filter(x => +x.id_pharmacy !== +id);
              table.rows((i, d) => +d.id_pharmacy === +id).remove().draw(false);
              renderStats();
            })
            .catch(() => Swal.fire('Gagal', 'Tidak dapat terhubung ke server.', 'error'));
        });
      });

      load();
    })();
  </script>
</body>

</html>
