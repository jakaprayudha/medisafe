<?php
$title = 'Tarif Layanan';
require '../../controller/view.php';

$providerList = [];
$stmtProv = $koneksi->prepare("SELECT id_provider, provider_name FROM ms_provider WHERE provider_status='1' ORDER BY id_provider");
$stmtProv->execute();
foreach ($stmtProv->get_result()->fetch_all(MYSQLI_ASSOC) as $p) {
  $providerList[] = ['id' => (string) $p['id_provider'], 'nama' => $p['provider_name']];
}
$stmtProv->close();
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

    .tf-stat {
      border: 1px solid #e5eaef;
      border-radius: 12px;
      background: #fff;
      padding: 14px 16px;
      height: 100%;
    }

    .tf-icon {
      width: 40px;
      height: 40px;
      border-radius: 10px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 1.05rem;
      flex-shrink: 0;
    }

    .tf-val {
      font-size: 1.3rem;
      font-weight: 700;
      line-height: 1.2;
      color: #2a3547;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .tf-label {
      font-size: .75rem;
      color: #6c757d;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .tf-chips .btn {
      border-radius: 20px;
      font-size: .8rem;
      padding: .3rem .85rem;
    }

    .tf-chips .badge {
      font-weight: 600;
    }

    .tf-search {
      width: 240px;
    }

    @media (max-width: 767.98px) {

      .tf-tools,
      .tf-search {
        width: 100%;
      }

      .tf-search {
        flex: 1;
      }
    }

    #tarifTable {
      width: 100% !important;
    }

    #tarifTable thead th {
      font-size: .74rem;
      font-weight: 600;
      color: #5a6a85;
      text-transform: uppercase;
      letter-spacing: .3px;
      background: #f6f9fc;
      border-bottom: 0;
      white-space: nowrap;
    }

    #tarifTable td {
      font-size: .85rem;
      vertical-align: middle;
      padding-top: .6rem;
      padding-bottom: .6rem;
    }

    #tarifTable tr.off td:not(.tf-keep) {
      opacity: .5;
    }

    .tf-ava {
      width: 38px;
      height: 38px;
      border-radius: 10px;
      flex-shrink: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: .95rem;
    }

    .tf-name {
      font-weight: 600;
      color: #2a3547;
      max-width: 230px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .tf-sub {
      font-size: .74rem;
      color: #7c8fac;
      white-space: nowrap;
    }

    .tf-badge {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      font-size: .7rem;
      font-weight: 600;
      padding: .25em .6em;
      border-radius: 6px;
      white-space: nowrap;
    }

    .tf-price {
      font-weight: 700;
      color: #2a3547;
      white-space: nowrap;
    }

    .tf-bar {
      display: flex;
      height: 7px;
      border-radius: 4px;
      overflow: hidden;
      background: #eef2f7;
      width: 120px;
    }

    .tf-bar span {
      display: block;
      height: 100%;
    }

    .c-dok {
      background: #5d87ff;
    }

    .c-per {
      background: #13deb9;
    }

    .c-adm {
      background: #ffae1f;
    }

    .c-kln {
      background: #c9d3e3;
    }

    .tf-legend {
      font-size: .72rem;
      color: #7c8fac;
      white-space: nowrap;
    }

    .tf-legend i {
      display: inline-block;
      width: 8px;
      height: 8px;
      border-radius: 2px;
      margin-right: 3px;
    }

    .tf-act {
      white-space: nowrap;
    }

    .tf-act .btn {
      width: 32px;
      height: 32px;
      padding: 0;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 8px;
    }

    .tf-loading {
      position: absolute;
      inset: 0;
      background: rgba(255, 255, 255, .6);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 5;
      border-radius: 12px;
    }

    .tf-switch .form-check-input {
      width: 2.4em;
      height: 1.25em;
      cursor: pointer;
      margin: 0;
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

    .tf-sec {
      font-size: .72rem;
      font-weight: 700;
      color: #5a6a85;
      text-transform: uppercase;
      letter-spacing: .4px;
      margin-bottom: .6rem;
    }

    .tf-split {
      background: #f6f9fc;
      border-radius: 12px;
      padding: 14px 16px;
    }

    .tf-split .tf-bar {
      width: 100%;
      height: 10px;
      border-radius: 5px;
    }

    .tf-split-row {
      display: flex;
      justify-content: space-between;
      font-size: .82rem;
      padding: 2px 0;
    }

    .tf-split-row.total {
      border-top: 1px dashed #d5dde8;
      margin-top: 6px;
      padding-top: 8px;
      font-weight: 700;
    }

    .input-group-text.rp {
      font-size: .8rem;
      color: #5a6a85;
      background: #f6f9fc;
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
              <h4 class="fw-semibold mb-1">Tarif Layanan</h4>
              <div class="text-muted small">Daftar tarif tindakan &amp; layanan yang ditagihkan ke pasien, beserta pembagian fee tenaga medis.</div>
            </div>
            <div class="d-flex gap-2">
              <button class="btn btn-light" id="btnRefresh" title="Muat ulang"><i class="fas fa-sync-alt"></i></button>
              <button class="btn btn-primary" id="btnTambah"><i class="fas fa-plus me-1"></i> Tambah Tarif</button>
            </div>
          </div>

          <!-- STAT -->
          <div class="row g-3 mb-3">
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="tf-stat d-flex gap-3 align-items-center">
                <span class="tf-icon bg-primary-subtle text-primary"><i class="fas fa-tags"></i></span>
                <div class="min-w-0">
                  <div class="tf-val" id="stTotal">-</div>
                  <div class="tf-label" id="stTotalSub">Total tarif</div>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="tf-stat d-flex gap-3 align-items-center">
                <span class="tf-icon bg-success-subtle text-success"><i class="fas fa-money-bill-wave"></i></span>
                <div class="min-w-0">
                  <div class="tf-val" id="stRata">-</div>
                  <div class="tf-label" id="stRataSub">Rata-rata tarif aktif</div>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="tf-stat d-flex gap-3 align-items-center">
                <span class="tf-icon bg-warning-subtle text-warning"><i class="fas fa-fire"></i></span>
                <div class="min-w-0">
                  <div class="tf-val" id="stTop" style="font-size:1.05rem">-</div>
                  <div class="tf-label" id="stTopSub">Paling sering ditagihkan</div>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="tf-stat d-flex gap-3 align-items-center">
                <span class="tf-icon bg-info-subtle text-info"><i class="fas fa-receipt"></i></span>
                <div class="min-w-0">
                  <div class="tf-val" id="stPakai">-</div>
                  <div class="tf-label" id="stPakaiSub">Baris tagihan memakai tarif</div>
                </div>
              </div>
            </div>
          </div>

          <!-- TABEL -->
          <div class="card position-relative">
            <div class="tf-loading" id="tfLoading">
              <div class="spinner-border text-primary"></div>
            </div>
            <div class="card-body p-4">
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div class="tf-chips d-flex flex-wrap gap-2" id="tfChips">
                  <button class="btn btn-primary" data-lay="">Semua <span class="badge bg-white text-primary ms-1" data-n=""></span></button>
                  <button class="btn btn-light" data-lay="Poliklinik">Poliklinik <span class="badge bg-white text-dark ms-1" data-n="Poliklinik"></span></button>
                  <button class="btn btn-light" data-lay="UGD">UGD <span class="badge bg-white text-dark ms-1" data-n="UGD"></span></button>
                  <button class="btn btn-light" data-lay="Rawat Inap">Rawat Inap <span class="badge bg-white text-dark ms-1" data-n="Rawat Inap"></span></button>
                  <button class="btn btn-light" data-lay="99">Semua layanan <span class="badge bg-white text-dark ms-1" data-n="99"></span></button>
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center tf-tools">
                  <select id="fProv" class="form-select form-select-sm" style="width:auto">
                    <option value="">Semua jaminan</option>
                    <?php foreach ($providerList as $p) : ?>
                      <option value="<?= htmlspecialchars($p['id']) ?>"><?= htmlspecialchars($p['nama']) ?></option>
                    <?php endforeach ?>
                    <option value="99">Seluruh jaminan</option>
                  </select>
                  <select id="fStatus" class="form-select form-select-sm" style="width:auto">
                    <option value="">Semua status</option>
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                    <option value="unused">Belum pernah dipakai</option>
                  </select>
                  <div class="input-group input-group-sm tf-search">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="search" id="tfSearch" class="form-control" placeholder="Cari nama / nomor tarif" autocomplete="off">
                  </div>
                </div>
              </div>

              <div class="table-responsive">
                <table class="table align-middle mb-0" id="tarifTable">
                  <thead>
                    <tr>
                      <th>Tarif</th>
                      <th>Jaminan</th>
                      <th class="text-end">Harga</th>
                      <th>Pembagian Fee</th>
                      <th class="text-center">Dipakai</th>
                      <th class="text-center">Aktif</th>
                      <th class="text-end">Aksi</th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                </table>
              </div>
              <div class="d-flex flex-wrap gap-3 mt-3 tf-legend">
                <span><i class="c-dok"></i>Dokter</span>
                <span><i class="c-per"></i>Perawat</span>
                <span><i class="c-adm"></i>Admin</span>
                <span><i class="c-kln"></i>Diterima klinik</span>
                <span class="ms-auto"><i class="fas fa-info-circle me-1"></i>Tarif yang sudah dipakai di tagihan tidak bisa dihapus, nonaktifkan saja.</span>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <!-- MODAL TAMBAH / EDIT -->
  <div class="modal fade" id="programModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <form id="programForm" class="modal-content" novalidate>
        <div class="modal-header">
          <h5 class="modal-title">Tambah Tarif</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_tarif" id="id_tarif">

          <div class="alert alert-warning d-none small py-2" id="renameWarn">
            <i class="fas fa-exclamation-triangle me-1"></i>
            Tarif ini sudah dipakai di <b id="renameN">0</b> tagihan. Tagihan lama tetap memakai nama lama, sehingga laporan fee tindakan untuk tagihan tersebut tidak lagi terhubung bila nama diubah.
          </div>

          <div class="row g-4">
            <div class="col-lg-7">
              <div class="tf-sec">Informasi Tarif</div>
              <div class="mb-3">
                <label class="form-label required" for="tarif_name">Nama Tarif</label>
                <input type="text" id="tarif_name" name="tarif_name" class="form-control" maxlength="255" placeholder="mis. Nebulizer, Heacting, Konsultasi Dokter Umum" required>
                <div class="form-text">Nama ini yang dipilih saat input tindakan/billing, harus unik.</div>
              </div>

              <div class="mb-3">
                <label class="form-label required">Layanan</label>
                <div class="row g-2 opt-grid">
                  <div class="col-6 col-md-3">
                    <input type="radio" name="tarif_services" id="lay_99" value="99" checked>
                    <label for="lay_99">Semua</label>
                  </div>
                  <div class="col-6 col-md-3">
                    <input type="radio" name="tarif_services" id="lay_poli" value="Poliklinik">
                    <label for="lay_poli" style="--c:#13a89e;--bg:#e6fffa">Poliklinik</label>
                  </div>
                  <div class="col-6 col-md-3">
                    <input type="radio" name="tarif_services" id="lay_ugd" value="UGD">
                    <label for="lay_ugd" style="--c:#fa5a5a;--bg:#fdede8">UGD</label>
                  </div>
                  <div class="col-6 col-md-3">
                    <input type="radio" name="tarif_services" id="lay_ranap" value="Rawat Inap">
                    <label for="lay_ranap" style="--c:#7b5cd6;--bg:#f1ecff">Rawat Inap</label>
                  </div>
                </div>
              </div>

              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label required" for="tarif_provider">Jaminan (Jenis Bayar)</label>
                  <select name="tarif_provider" id="tarif_provider" class="form-select" required>
                    <option value="99">Seluruh jaminan</option>
                    <?php foreach ($providerList as $p) : ?>
                      <option value="<?= htmlspecialchars($p['id']) ?>"><?= htmlspecialchars($p['nama']) ?></option>
                    <?php endforeach ?>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label required" for="tarif_amount">Tarif ke Pasien</label>
                  <div class="input-group">
                    <span class="input-group-text rp">Rp</span>
                    <input type="number" id="tarif_amount" name="tarif_amount" class="form-control fw-semibold" min="0" step="1" required>
                  </div>
                </div>
              </div>

              <div class="tf-sec mt-4">Fee Tenaga Medis</div>
              <div class="row g-3">
                <div class="col-md-4">
                  <label class="form-label" for="tarif_share_doctor">Fee Dokter</label>
                  <div class="input-group">
                    <span class="input-group-text rp">Rp</span>
                    <input type="number" id="tarif_share_doctor" name="tarif_share_doctor" class="form-control fee" min="0" step="1" value="0">
                  </div>
                </div>
                <div class="col-md-4">
                  <label class="form-label" for="tarif_share_nurse">Fee Perawat</label>
                  <div class="input-group">
                    <span class="input-group-text rp">Rp</span>
                    <input type="number" id="tarif_share_nurse" name="tarif_share_nurse" class="form-control fee" min="0" step="1" value="0">
                  </div>
                </div>
                <div class="col-md-4">
                  <label class="form-label" for="tarif_share_admin">Fee Admin</label>
                  <div class="input-group">
                    <span class="input-group-text rp">Rp</span>
                    <input type="number" id="tarif_share_admin" name="tarif_share_admin" class="form-control fee" min="0" step="1" value="0">
                  </div>
                </div>
              </div>
              <div class="form-text">Fee dipakai untuk laporan pembayaran jasa tenaga medis, tidak menambah tagihan pasien.</div>
            </div>

            <div class="col-lg-5">
              <div class="tf-sec">Ringkasan Pembagian</div>
              <div class="tf-split">
                <div class="tf-bar mb-3" id="pvBar"></div>
                <div class="tf-split-row"><span><span class="tf-legend"><i class="c-dok"></i></span>Dokter</span><b id="pvDok">Rp 0</b></div>
                <div class="tf-split-row"><span><span class="tf-legend"><i class="c-per"></i></span>Perawat</span><b id="pvPer">Rp 0</b></div>
                <div class="tf-split-row"><span><span class="tf-legend"><i class="c-adm"></i></span>Admin</span><b id="pvAdm">Rp 0</b></div>
                <div class="tf-split-row"><span><span class="tf-legend"><i class="c-kln"></i></span>Diterima klinik</span><b id="pvKln">Rp 0</b></div>
                <div class="tf-split-row total"><span>Tarif ke pasien</span><span id="pvTot">Rp 0</span></div>
              </div>
              <div class="alert alert-danger small py-2 mt-3 d-none" id="feeWarn">
                <i class="fas fa-exclamation-circle me-1"></i> Total fee melebihi tarif, klinik akan rugi pada tindakan ini.
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary" id="btnSimpan"><i class="fas fa-save me-1"></i> Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <?php require 'library.php'; ?>

  <script>
    const apiUrl = 'controller/master/tarifController';
    const PROVIDER = <?= json_encode(array_column($providerList, 'nama', 'id'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
    const LAYANAN = {
      'Poliklinik': ['Poliklinik', '#13a89e', '#e6fffa', 'fa-stethoscope'],
      'UGD': ['UGD', '#fa5a5a', '#fdede8', 'fa-ambulance'],
      'Rawat Inap': ['Rawat Inap', '#7b5cd6', '#f1ecff', 'fa-procedures'],
      '99': ['Semua layanan', '#5d87ff', '#ecf2ff', 'fa-layer-group']
    };
    const PROV_WARNA = {
      '1': ['#13a89e', '#e6fffa'],
      '2': ['#0d8a5f', '#e3f7ee'],
      '3': ['#7b5cd6', '#f1ecff'],
      '4': ['#c27c0e', '#fef5e5'],
      '5': ['#5a6a85', '#eef2f7'],
      '99': ['#5d87ff', '#ecf2ff']
    };

    let DATA = [];
    let fLay = '';
    let editLama = null;

    const rp = n => 'Rp ' + (Number(n) || 0).toLocaleString('id-ID');
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#39;'
    } [c]));
    const tgl = s => {
      if (!s) return '';
      const d = new Date(String(s).replace(' ', 'T'));
      return isNaN(d) ? '' : d.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
      });
    };
    const namaProv = id => id === '99' ? 'Seluruh jaminan' : (PROVIDER[id] || (id ? 'ID ' + id + ' (tidak ditemukan)' : '-'));

    function barHtml(r) {
      const t = Number(r.tarif_amount) || 0;
      const d = +r.tarif_share_doctor || 0,
        p = +r.tarif_share_nurse || 0,
        a = +r.tarif_share_admin || 0;
      const fee = d + p + a;
      const base = Math.max(t, fee) || 1;
      const kln = Math.max(t - fee, 0);
      const seg = (v, c) => v > 0 ? `<span class="${c}" style="width:${(v / base * 100).toFixed(1)}%"></span>` : '';
      return seg(d, 'c-dok') + seg(p, 'c-per') + seg(a, 'c-adm') + seg(kln, 'c-kln');
    }

    // ===== TABEL =====
    const table = $('#tarifTable').DataTable({
      data: [],
      dom: 'rt<"d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2"ip>',
      pageLength: 25,
      order: [
        [0, 'asc']
      ],
      language: {
        emptyTable: 'Belum ada tarif. Klik "Tambah Tarif" untuk menambahkan.',
        zeroRecords: 'Tidak ada tarif yang cocok dengan filter.',
        info: 'Menampilkan _START_–_END_ dari _TOTAL_ tarif',
        infoEmpty: '0 tarif',
        infoFiltered: '(disaring dari _MAX_)',
        paginate: {
          previous: '‹',
          next: '›'
        }
      },
      createdRow: (tr, r) => {
        if (r.tarif_status != 1) tr.classList.add('off');
      },
      columns: [{
          data: 'tarif_name',
          render: (v, t, r) => {
            if (t !== 'display') return (v || '') + ' ' + (r.tarif_number || '');
            const L = LAYANAN[r.tarif_services] || [r.tarif_services || '-', '#5a6a85', '#eef2f7', 'fa-tag'];
            return `<div class="d-flex align-items-center gap-2">
              <span class="tf-ava" style="color:${L[1]};background:${L[2]}"><i class="fas ${L[3]}"></i></span>
              <div class="min-w-0">
                <div class="tf-name" title="${esc(v)}">${esc(v) || '-'}</div>
                <div class="tf-sub">${esc(r.tarif_number || '')} · <span style="color:${L[1]}">${esc(L[0])}</span></div>
              </div>
            </div>`;
          }
        },
        {
          data: 'tarif_provider',
          render: (v, t) => {
            const n = namaProv(String(v ?? ''));
            if (t !== 'display') return n;
            const w = PROV_WARNA[v] || ['#5a6a85', '#eef2f7'];
            return `<span class="tf-badge" style="color:${w[0]};background:${w[1]}">${esc(n)}</span>`;
          }
        },
        {
          data: 'tarif_amount',
          className: 'text-end',
          render: (v, t) => t === 'display' ? `<span class="tf-price">${rp(v)}</span>` : (Number(v) || 0)
        },
        {
          data: null,
          orderable: false,
          searchable: false,
          render: (_, t, r) => {
            const fee = (+r.tarif_share_doctor || 0) + (+r.tarif_share_nurse || 0) + (+r.tarif_share_admin || 0);
            const kln = (Number(r.tarif_amount) || 0) - fee;
            const tip = `Dokter ${rp(r.tarif_share_doctor)} · Perawat ${rp(r.tarif_share_nurse)} · Admin ${rp(r.tarif_share_admin)}`;
            return `<div title="${esc(tip)}">
              <div class="tf-bar mb-1">${barHtml(r)}</div>
              <div class="tf-sub">${fee ? 'Fee ' + rp(fee) + ' · ' : ''}<span class="${kln < 0 ? 'text-danger fw-semibold' : ''}">Klinik ${rp(kln)}</span></div>
            </div>`;
          }
        },
        {
          data: 'jml_pakai',
          className: 'text-center',
          render: (v, t, r) => {
            if (t !== 'display') return Number(v) || 0;
            if (!v) return '<span class="tf-sub">Belum</span>';
            return `<div class="fw-semibold">${Number(v).toLocaleString('id-ID')}×</div>
              <div class="tf-sub" title="Terakhir ditagihkan">${tgl(r.terakhir_dipakai)}</div>`;
          }
        },
        {
          data: 'tarif_status',
          className: 'text-center tf-keep',
          render: (v, t, r) => t !== 'display' ? v : `<div class="form-check form-switch tf-switch d-inline-block">
              <input class="form-check-input toggle-status-tarif" type="checkbox" data-id="${r.id_tarif}" ${v == 1 ? 'checked' : ''} title="${v == 1 ? 'Aktif' : 'Nonaktif'}">
            </div>`
        },
        {
          data: null,
          orderable: false,
          searchable: false,
          className: 'text-end tf-keep',
          render: (_, t, r) => {
            const dipakai = Number(r.jml_pakai) > 0;
            return `<div class="tf-act">
              <button class="btn btn-light text-primary edit-btn" data-id="${r.id_tarif}" title="Edit"><i class="fas fa-pen"></i></button>
              <span class="d-inline-block" ${dipakai ? `title="Sudah dipakai di ${r.jml_pakai} tagihan, tidak bisa dihapus"` : 'title="Hapus"'}>
                <button class="btn btn-light text-danger delete-btn" data-id="${r.id_tarif}" ${dipakai ? 'disabled' : ''}><i class="fas fa-trash"></i></button>
              </span>
            </div>`;
          }
        }
      ]
    });

    $.fn.dataTable.ext.search.push((settings, _row, idx) => {
      if (settings.nTable.id !== 'tarifTable') return true;
      const r = settings.aoData[idx]._aData;
      if (fLay && r.tarif_services !== fLay) return false;
      const fp = $('#fProv').val();
      if (fp && String(r.tarif_provider) !== fp) return false;
      const fs = $('#fStatus').val();
      if (fs === 'unused' && Number(r.jml_pakai) > 0) return false;
      if ((fs === '1' || fs === '0') && String(r.tarif_status) !== fs) return false;
      return true;
    });

    function hitungStat() {
      const aktif = DATA.filter(r => r.tarif_status == 1);
      $('#stTotal').text(DATA.length.toLocaleString('id-ID'));
      $('#stTotalSub').text(`${aktif.length} aktif · ${DATA.length - aktif.length} nonaktif`);

      if (aktif.length) {
        const h = aktif.map(r => Number(r.tarif_amount) || 0);
        $('#stRata').text(rp(Math.round(h.reduce((a, b) => a + b, 0) / h.length)));
        $('#stRataSub').text(`${rp(Math.min(...h))} – ${Math.max(...h).toLocaleString('id-ID')}`).attr('title', 'Rentang tarif aktif');
      } else {
        $('#stRata').text('-');
        $('#stRataSub').text('Rata-rata tarif aktif');
      }

      const top = [...DATA].sort((a, b) => b.jml_pakai - a.jml_pakai)[0];
      if (top && top.jml_pakai > 0) {
        $('#stTop').text(top.tarif_name).attr('title', top.tarif_name);
        $('#stTopSub').text(`${Number(top.jml_pakai).toLocaleString('id-ID')}× ditagihkan · paling sering`);
      } else {
        $('#stTop').text('-');
        $('#stTopSub').text('Paling sering ditagihkan');
      }

      const total = DATA.reduce((a, r) => a + (Number(r.jml_pakai) || 0), 0);
      const belum = DATA.filter(r => !Number(r.jml_pakai)).length;
      $('#stPakai').text(total.toLocaleString('id-ID'));
      $('#stPakaiSub').text(belum ? `${belum} tarif belum pernah dipakai` : 'Semua tarif pernah dipakai');

      $('#tfChips [data-n]').each(function() {
        const k = this.dataset.n;
        this.textContent = k ? DATA.filter(r => r.tarif_services === k).length : DATA.length;
      });
    }

    function muat() {
      $('#tfLoading').css('display', 'flex');
      return fetch(apiUrl)
        .then(r => r.json())
        .then(res => {
          if (res.status !== 'success') throw new Error(res.message || 'Gagal memuat');
          DATA = res.data || [];
          table.clear().rows.add(DATA).draw(false);
          hitungStat();
        })
        .catch(e => Swal.fire('Gagal', e.message || 'Tidak dapat memuat data tarif.', 'error'))
        .finally(() => $('#tfLoading').hide());
    }

    // ===== FILTER =====
    let tmr;
    $('#tfSearch').on('input', function() {
      clearTimeout(tmr);
      tmr = setTimeout(() => table.search(this.value).draw(), 200);
    });
    $('#fProv, #fStatus').on('change', () => table.draw());
    $('#tfChips').on('click', '.btn', function() {
      fLay = this.dataset.lay;
      $('#tfChips .btn').removeClass('btn-primary').addClass('btn-light')
        .find('.badge').removeClass('text-primary').addClass('text-dark');
      $(this).removeClass('btn-light').addClass('btn-primary')
        .find('.badge').removeClass('text-dark').addClass('text-primary');
      table.draw();
    });
    $('#btnRefresh').on('click', muat);

    // ===== FORM =====
    function previewFee() {
      const t = Number($('#tarif_amount').val()) || 0;
      const r = {
        tarif_amount: t,
        tarif_share_doctor: Number($('#tarif_share_doctor').val()) || 0,
        tarif_share_nurse: Number($('#tarif_share_nurse').val()) || 0,
        tarif_share_admin: Number($('#tarif_share_admin').val()) || 0
      };
      const fee = r.tarif_share_doctor + r.tarif_share_nurse + r.tarif_share_admin;
      $('#pvBar').html(barHtml(r));
      $('#pvDok').text(rp(r.tarif_share_doctor));
      $('#pvPer').text(rp(r.tarif_share_nurse));
      $('#pvAdm').text(rp(r.tarif_share_admin));
      $('#pvKln').text(rp(t - fee)).toggleClass('text-danger', t - fee < 0);
      $('#pvTot').text(rp(t));
      $('#feeWarn').toggleClass('d-none', !(fee > t));
    }
    $('#tarif_amount, .fee').on('input', previewFee);

    $('#tarif_name').on('input', function() {
      const berubah = editLama && editLama.jml_pakai > 0 &&
        this.value.trim().toLowerCase() !== String(editLama.tarif_name || '').trim().toLowerCase();
      $('#renameWarn').toggleClass('d-none', !berubah);
    });

    function setProvider(v) {
      const $s = $('#tarif_provider');
      $s.find('option.legacy').remove();
      v = String(v ?? '99');
      if (!$s.find(`option[value="${CSS.escape(v)}"]`).length) {
        $s.append($('<option class="legacy">').val(v).text(namaProv(v)));
      }
      $s.val(v);
    }

    function bukaForm(d) {
      const f = $('#programForm')[0];
      f.reset();
      editLama = d || null;
      $('#renameWarn').addClass('d-none');
      $('#renameN').text(d ? d.jml_pakai : 0);
      $('#id_tarif').val(d ? d.id_tarif : '');
      $('#tarif_name').val(d ? d.tarif_name : '');
      $('#tarif_amount').val(d ? d.tarif_amount : '');
      $('#tarif_share_doctor').val(d ? (d.tarif_share_doctor ?? 0) : 0);
      $('#tarif_share_nurse').val(d ? (d.tarif_share_nurse ?? 0) : 0);
      $('#tarif_share_admin').val(d ? (d.tarif_share_admin ?? 0) : 0);
      const lay = d && LAYANAN[d.tarif_services] ? d.tarif_services : '99';
      $(`input[name="tarif_services"][value="${lay}"]`).prop('checked', true);
      setProvider(d ? d.tarif_provider : '99');
      $('#programModal .modal-title').text(d ? 'Edit Tarif' : 'Tambah Tarif');
      previewFee();
      $('#programModal').modal('show');
    }

    $('#programModal').on('shown.bs.modal', () => $('#tarif_name').trigger('focus'));
    $('#btnTambah').on('click', () => bukaForm(null));

    $(document).on('click', '.edit-btn', function() {
      const id = this.dataset.id;
      const btn = $(this).prop('disabled', true);
      fetch(apiUrl + `?id=${encodeURIComponent(id)}`)
        .then(r => r.json())
        .then(res => {
          if (res.status !== 'success') throw new Error(res.message);
          bukaForm(res.data);
        })
        .catch(e => Swal.fire('Gagal', e.message || 'Data tidak ditemukan', 'error'))
        .finally(() => btn.prop('disabled', false));
    });

    $('#programForm').on('submit', function(e) {
      e.preventDefault();
      const nama = $('#tarif_name').val().trim();
      const t = $('#tarif_amount').val();
      if (!nama) {
        $('#tarif_name').addClass('is-invalid').trigger('focus');
        return;
      }
      if (t === '' || Number(t) < 0) {
        $('#tarif_amount').addClass('is-invalid').trigger('focus');
        return;
      }
      if ([...document.querySelectorAll('.fee')].some(i => Number(i.value) < 0)) {
        Swal.fire('Periksa fee', 'Fee tidak boleh bernilai negatif.', 'warning');
        return;
      }

      const lanjut = $('#feeWarn').hasClass('d-none') ? Promise.resolve(true) :
        Swal.fire({
          icon: 'warning',
          title: 'Fee melebihi tarif',
          text: 'Total fee tenaga medis lebih besar dari tarif pasien. Tetap simpan?',
          showCancelButton: true,
          confirmButtonText: 'Tetap simpan',
          cancelButtonText: 'Periksa lagi'
        }).then(r => r.isConfirmed);

      lanjut.then(ok => {
        if (!ok) return;
        const id = $('#id_tarif').val();
        const body = new URLSearchParams(new FormData(this));
        body.set('tarif_name', nama);
        const btn = $('#btnSimpan').prop('disabled', true);
        fetch(apiUrl + (id ? `?id=${encodeURIComponent(id)}` : ''), {
            method: id ? 'PUT' : 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            body
          })
          .then(r => r.json())
          .then(res => {
            if (res.status !== 'success') throw new Error(res.message);
            $('#programModal').modal('hide');
            Swal.fire({
              icon: 'success',
              title: 'Berhasil',
              text: res.message,
              timer: 1400,
              showConfirmButton: false
            });
            muat();
          })
          .catch(e => Swal.fire('Gagal', e.message || 'Server error', 'error'))
          .finally(() => btn.prop('disabled', false));
      });
    });
    $('#programForm').on('input', '.is-invalid', function() {
      $(this).removeClass('is-invalid');
    });

    // ===== HAPUS =====
    $(document).on('click', '.delete-btn', function() {
      const id = this.dataset.id;
      const r = DATA.find(x => String(x.id_tarif) === String(id));
      Swal.fire({
        title: 'Hapus tarif?',
        html: `<b>${esc(r ? r.tarif_name : '')}</b> akan dihapus permanen.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#fa5a5a',
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batal'
      }).then(res => {
        if (!res.isConfirmed) return;
        fetch(apiUrl + `?id=${encodeURIComponent(id)}`, {
            method: 'DELETE'
          })
          .then(x => x.json())
          .then(d => {
            if (d.status !== 'success') throw new Error(d.message);
            Swal.fire({
              icon: 'success',
              title: 'Terhapus',
              timer: 1200,
              showConfirmButton: false
            });
            muat();
          })
          .catch(e => Swal.fire('Gagal', e.message || 'Server error', 'error'));
      });
    });

    // ===== TOGGLE STATUS =====
    $(document).on('change', '.toggle-status-tarif', function() {
      const el = this;
      const id = el.dataset.id;
      const status = el.checked ? 1 : 0;
      el.disabled = true;
      fetch(apiUrl + '?toggle_status=1', {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: `id_tarif=${encodeURIComponent(id)}&tarif_status=${status}`
        })
        .then(r => r.json())
        .then(res => {
          if (res.status !== 'success') throw new Error(res.message);
          const r = DATA.find(x => String(x.id_tarif) === String(id));
          if (r) r.tarif_status = status;
          $(el).closest('tr').toggleClass('off', !status);
          el.title = status ? 'Aktif' : 'Nonaktif';
          hitungStat();
        })
        .catch(e => {
          el.checked = !el.checked;
          Swal.fire('Gagal', e.message || 'Server error', 'error');
        })
        .finally(() => el.disabled = false);
    });

    muat();
  </script>
</body>

</html>
