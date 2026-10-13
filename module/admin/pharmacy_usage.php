<?php
$title = 'Pemakaian Obat Pasien';
require '../../controller/view.php';
require '../../utility/env.php';

$env = loadEnv();
$apiUrl = getenv('API_URL');
$selectedIdPharmacy = $_GET['id_pharmacy'] ?? '';
?>
<!doctype html>
<html lang="en">

<head>
  <base href="../../">
  <?php
  require '../../assets/template/head.php';
  ?>
  <style>
    /* Card Info Obat Terpilih */
    .drug-hero-card {
      background: linear-gradient(135deg, #f0f4ff 0%, #ffffff 100%);
      border: 1px solid #d0deff;
      border-radius: 12px;
    }

    /* Stat Cards */
    .stat-card-usage {
      border: 1px solid rgba(0, 0, 0, 0.08);
      border-radius: 12px;
      transition: all 0.25s ease;
      background: #ffffff;
    }

    .stat-card-usage:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
    }

    .stat-icon-wrap {
      width: 48px;
      height: 48px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
    }

    /* Styling Table */
    #tableUsage thead th {
      background-color: #f8f9fa;
      font-weight: 600;
      white-space: nowrap;
    }

    .select2-container .select2-selection--single {
      height: 40px !important;
      padding: 6px 12px;
      border-color: #dfe5ef;
      border-radius: 8px;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
      height: 38px !important;
    }

    /* Print styling */
    @media print {
      body * {
        visibility: hidden;
      }
      #printArea, #printArea * {
        visibility: visible;
      }
      #printArea {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
      }
      .no-print {
        display: none !important;
      }
    }
  </style>
</head>

<body>
  <!-- Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">

    <!-- Sidebar Start -->
    <?php require 'sidebar.php'; ?>
    <!-- Sidebar End -->

    <!-- Main wrapper -->
    <div class="body-wrapper">

      <!-- Header Start -->
      <?php require 'navbar.php'; ?>
      <!-- Header End -->

      <div class="body-wrapper-inner">
        <div class="container-fluid">

          <!-- Tab Menu Farmasi -->
          <?php require 'menu_farmasi.php'; ?>

          <!-- Header Judul & Deskripsi Halaman -->
          <div class="card mb-3 shadow-none border">
            <div class="card-body p-3">
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                  <h4 class="fw-bold mb-1 d-flex align-items-center gap-2">
                    <iconify-icon icon="solar:users-group-two-rounded-bold-duotone" class="text-primary fs-7"></iconify-icon>
                    Penelusuran Pemakaian Obat Pasien
                  </h4>
                  <p class="text-muted mb-0 fs-3">
                    Lacak dan pantau pasien siapa saja yang telah memakai atau diresepkan obat tertentu secara lengkap dengan dokter peresep, poli layanan, dosis, serta tanggal pemberian.
                  </p>
                </div>
                <div>
                  <button type="button" class="btn btn-outline-dark btn-sm" id="btnPrint">
                    <i class="ti ti-printer me-1"></i>Cetak Laporan
                  </button>
                  <button type="button" class="btn btn-primary btn-sm ms-1" id="btnRefresh">
                    <i class="ti ti-refresh me-1"></i>Refresh
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- CARD PILIHAN OBAT (SEARCH & SELECT2) -->
          <div class="card mb-3 shadow-none border">
            <div class="card-body p-3">
              <div class="row align-items-center g-2">
                <div class="col-lg-7 col-md-12">
                  <label for="selectObat" class="form-label fw-semibold fs-3 mb-1">
                    <i class="ti ti-search me-1 text-primary"></i>Pilih / Cari Obat:
                  </label>
                  <select id="selectObat" class="form-select select2 w-100">
                    <option value="all">-- Tampilkan Pemakaian Semua Obat --</option>
                  </select>
                  <div class="form-text fs-2">
                    Ketik nama generik, nama dagang/merk, atau kode obat untuk memfilter pasien yang menggunakan obat tersebut.
                  </div>
                </div>

                <div class="col-lg-5 col-md-12 text-lg-end mt-2 mt-lg-0">
                  <div class="d-flex justify-content-lg-end gap-2 flex-wrap">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnResetObat">
                      <i class="ti ti-x me-1"></i>Lihat Semua Obat
                    </button>
                    <a href="module/admin/pharmacy" class="btn btn-light btn-sm border">
                      <i class="ti ti-drugs me-1"></i>Master Obat
                    </a>
                    <a href="module/admin/pharmacy_expired" class="btn btn-light btn-sm border">
                      <i class="ti ti-calendar me-1"></i>Expired Obat
                    </a>
                  </div>
                </div>
              </div>

              <!-- BANNER INFO OBAT TERPILIH (JIKA SPESIFIK DIPILIH) -->
              <div class="drug-hero-card p-3 mt-3 d-none" id="selectedDrugCard">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                  <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary text-white rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                      <iconify-icon icon="mdi:pill" class="fs-7"></iconify-icon>
                    </div>
                    <div>
                      <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary" id="drugCodeBadge">-</span>
                        <span class="badge bg-secondary-subtle text-secondary" id="drugCategoryBadge">-</span>
                        <span class="badge bg-info-subtle text-info" id="drugGolonganBadge">-</span>
                      </div>
                      <h5 class="fw-bold my-1" id="drugGenericName">-</h5>
                      <div class="text-muted fs-3">
                        Nama Dagang / Pabrikan: <strong id="drugTradeName" class="text-dark">-</strong> | Satuan: <strong id="drugUnit" class="text-dark">-</strong>
                      </div>
                    </div>
                  </div>

                  <div class="d-flex align-items-center gap-4 text-end">
                    <div>
                      <div class="fs-2 text-muted">Sisa Stok Obat Saat Ini</div>
                      <h4 class="fw-bold text-success my-0" id="drugCurrentStock">0</h4>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>

          <!-- 4 KARTU STATISTIK PEMAKAIAN -->
          <div class="row g-3 mb-3">
            <!-- TOTAL PASIEN -->
            <div class="col-xl-3 col-sm-6">
              <div class="card stat-card-usage h-100 mb-0">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                  <div>
                    <span class="text-muted text-uppercase fs-2 fw-semibold">Pasien Pengguna</span>
                    <h3 class="fw-bold my-1 text-primary" id="statTotalPasien">0</h3>
                    <span class="fs-2 text-muted">Jumlah pasien unik</span>
                  </div>
                  <div class="stat-icon-wrap bg-primary-subtle text-primary">
                    <iconify-icon icon="solar:users-group-rounded-bold-duotone"></iconify-icon>
                  </div>
                </div>
              </div>
            </div>

            <!-- TOTAL QTY DIGUNAKAN -->
            <div class="col-xl-3 col-sm-6">
              <div class="card stat-card-usage h-100 mb-0">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                  <div>
                    <span class="text-muted text-uppercase fs-2 fw-semibold">Total Qty Terpakai</span>
                    <h3 class="fw-bold my-1 text-info" id="statTotalQty">0</h3>
                    <span class="fs-2 text-muted">Satuan / Butir obat keluar</span>
                  </div>
                  <div class="stat-icon-wrap bg-info-subtle text-info">
                    <iconify-icon icon="solar:box-minimalistic-bold-duotone"></iconify-icon>
                  </div>
                </div>
              </div>
            </div>

            <!-- TOTAL RESEP / KUNJUNGAN -->
            <div class="col-xl-3 col-sm-6">
              <div class="card stat-card-usage h-100 mb-0">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                  <div>
                    <span class="text-muted text-uppercase fs-2 fw-semibold">Frekuensi Resep</span>
                    <h3 class="fw-bold my-1 text-warning" id="statTotalResep">0</h3>
                    <span class="fs-2 text-muted">Total transaksi resep</span>
                  </div>
                  <div class="stat-icon-wrap bg-warning-subtle text-warning">
                    <iconify-icon icon="solar:document-medicine-bold-duotone"></iconify-icon>
                  </div>
                </div>
              </div>
            </div>

            <!-- TOTAL NOMINAL -->
            <div class="col-xl-3 col-sm-6">
              <div class="card stat-card-usage h-100 mb-0">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                  <div>
                    <span class="text-muted text-uppercase fs-2 fw-semibold">Estimasi Nilai Obat</span>
                    <h4 class="fw-bold my-1 text-success" id="statTotalNominal">Rp 0</h4>
                    <span class="fs-2 text-muted">Total nominal resep</span>
                  </div>
                  <div class="stat-icon-wrap bg-success-subtle text-success">
                    <iconify-icon icon="solar:wallet-money-bold-duotone"></iconify-icon>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- FILTER BAR (TANGGAL, STATUS, LAYANAN) -->
          <div class="card mb-3 shadow-none border">
            <div class="card-body p-3">
              <div class="row g-2 align-items-end">
                <div class="col-md-3 col-sm-6">
                  <label class="form-label fs-2 mb-1">Dari Tanggal</label>
                  <input type="date" class="form-control form-control-sm" id="fromDate">
                </div>
                <div class="col-md-3 col-sm-6">
                  <label class="form-label fs-2 mb-1">Sampai Tanggal</label>
                  <input type="date" class="form-control form-control-sm" id="toDate">
                </div>
                <div class="col-md-2 col-sm-6">
                  <label class="form-label fs-2 mb-1">Status Resep</label>
                  <select class="form-select form-select-sm" id="filterStatus">
                    <option value="all">Semua Status</option>
                    <option value="3">Selesai</option>
                    <option value="2">Diproses</option>
                    <option value="1">Persiapan</option>
                    <option value="0">Menunggu</option>
                  </select>
                </div>
                <div class="col-md-2 col-sm-6">
                  <label class="form-label fs-2 mb-1">Layanan / Poli</label>
                  <select class="form-select form-select-sm" id="filterLayanan">
                    <option value="">Semua Layanan</option>
                    <option value="Poliklinik">Poliklinik</option>
                    <option value="Rawat Inap">Rawat Inap</option>
                    <option value="IGD">IGD</option>
                  </select>
                </div>
                <div class="col-md-2 col-sm-12 d-flex gap-1">
                  <button type="button" class="btn btn-primary btn-sm w-100" id="btnApplyFilter">
                    <i class="ti ti-filter me-1"></i>Terapkan
                  </button>
                  <button type="button" class="btn btn-outline-secondary btn-sm" id="btnResetDate" title="Reset Periode">
                    <i class="ti ti-rotate-clockwise"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- TABEL DAFTAR PASIEN PENGGUNA OBAT -->
          <div class="card w-100 shadow-none border" id="printArea">
            <div class="card-body p-4">
              
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                  <iconify-icon icon="solar:clipboard-list-bold-duotone" class="text-primary fs-6"></iconify-icon>
                  Daftar Pasien Penerima Obat
                </h5>
                <span class="badge bg-light text-dark border fs-2" id="badgeResultCount">
                  Menampilkan 0 data
                </span>
              </div>

              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 w-100" id="tableUsage">
                  <thead>
                    <tr>
                      <th width="40" class="text-center">No</th>
                      <th width="120">Tgl Resep</th>
                      <th>Pasien</th>
                      <th>Obat Diberikan</th>
                      <th width="90" class="text-center">Jumlah</th>
                      <th>Signa / Aturan Minum</th>
                      <th>Dokter Peresep</th>
                      <th>Poli / Unit</th>
                      <th width="100" class="text-center">Status</th>
                      <th width="70" class="text-center no-print">Aksi</th>
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

  <!-- MODAL DETAIL PEMAKAIAN & RESEP PASIEN -->
  <div class="modal fade" id="modalUsageDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title d-flex align-items-center gap-2">
            <iconify-icon icon="solar:document-medicine-bold-duotone" class="text-primary fs-6"></iconify-icon>
            Detail Pemakaian Obat & Resep Pasien
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="modalUsageContent">
          <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">Memuat rincian resep...</p>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>

  <?php require 'library.php'; ?>

  <script>
    const usageApiUrl = 'controller/pharmacy/pharmacyUsageController.php';
    let tableUsage = null;
    let selectedIdPharmacy = '<?= htmlspecialchars($selectedIdPharmacy, ENT_QUOTES, 'UTF-8') ?>';

    // Format Tanggal Indo
    function formatTanggalIndo(dateStr) {
      if (!dateStr || dateStr === '0000-00-00') return '-';
      try {
        const parts = dateStr.split(' ')[0].split('-');
        if (parts.length !== 3) return dateStr;
        const bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const day = parseInt(parts[2], 10);
        const month = parseInt(parts[1], 10);
        const year = parts[0];
        const time = dateStr.includes(' ') ? ' ' + dateStr.split(' ')[1].substring(0, 5) : '';
        return `${day} ${bulan[month] || parts[1]} ${year}${time}`;
      } catch (e) {
        return dateStr;
      }
    }

    // Load Medicines into Select2 Dropdown
    function loadMedicinesDropdown(preselectedId) {
      $.ajax({
        url: usageApiUrl,
        type: 'GET',
        data: { action: 'get_medicines' },
        success: function(res) {
          if (res.status === 'success' && Array.isArray(res.data)) {
            let select = $('#selectObat');
            select.empty();
            select.append('<option value="all">-- Tampilkan Pemakaian Semua Obat --</option>');

            res.data.forEach(function(item) {
              const code = item.pharmacy_code ? `[${item.pharmacy_code}] ` : '';
              const generic = item.pharmacy_name_generic || '';
              const trade = item.pharmacy_name_trade ? ` (${item.pharmacy_name_trade})` : '';
              const unit = item.pharmacy_unit ? ` - Satuan: ${item.pharmacy_unit}` : '';
              const pasienCount = item.total_pasien_terlayani > 0 ? ` [${item.total_pasien_terlayani} Pasien]` : '';
              const isSelected = (preselectedId && String(item.id_pharmacy) === String(preselectedId)) ? 'selected' : '';

              select.append(`<option value="${item.id_pharmacy}" ${isSelected}>${code}${generic}${trade}${unit}${pasienCount}</option>`);
            });

            if (preselectedId && preselectedId !== 'all') {
              select.val(preselectedId).trigger('change');
            }
          }
        }
      });
    }

    // Update Summary UI
    function updateSummaryUI(summary) {
      if (!summary) return;

      $('#statTotalPasien').text(Number(summary.total_patients || 0).toLocaleString('id-ID'));
      $('#statTotalQty').text(Number(summary.total_qty || 0).toLocaleString('id-ID'));
      $('#statTotalResep').text(Number(summary.total_records || 0).toLocaleString('id-ID'));
      $('#statTotalNominal').text(summary.total_nominal_format || 'Rp 0');
      $('#badgeResultCount').text(`Menampilkan ${Number(summary.total_records || 0).toLocaleString('id-ID')} data pemakaian`);

      // Selected Drug Hero Card
      if (summary.selected_pharmacy) {
        const p = summary.selected_pharmacy;
        $('#selectedDrugCard').removeClass('d-none');
        $('#drugCodeBadge').text(p.pharmacy_code || 'OBAT');
        $('#drugCategoryBadge').text(p.pharmacy_category || 'Obat');
        $('#drugGolonganBadge').text(p.pharmcy_golongan || '-');
        $('#drugGenericName').text(p.pharmacy_name_generic || '-');
        $('#drugTradeName').text(p.pharmacy_name_trade || '-');
        $('#drugUnit').text(p.pharmacy_unit || '-');
        $('#drugCurrentStock').text(`${Number(p.pharmacy_stock || 0).toLocaleString('id-ID')} ${p.pharmacy_unit || ''}`);
      } else {
        $('#selectedDrugCard').addClass('d-none');
      }
    }

    $(document).ready(function() {

      // Initialize Select2
      if ($.fn.select2) {
        $('#selectObat').select2({
          placeholder: 'Pilih atau cari obat...',
          allowClear: false,
          width: '100%'
        });
      }

      // Load medicines
      loadMedicinesDropdown(selectedIdPharmacy);

      // Datatable init
      tableUsage = $('#tableUsage').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
          url: usageApiUrl,
          type: 'GET',
          data: function(d) {
            d.action = 'list';
            d.id_pharmacy = $('#selectObat').val() || selectedIdPharmacy || 'all';
            d.fromDate = $('#fromDate').val();
            d.toDate = $('#toDate').val();
            d.status_permintaan = $('#filterStatus').val();
            d.layanan = $('#filterLayanan').val();
          },
          dataSrc: function(json) {
            if (json.status !== 'success') {
              Swal.fire('Perhatian', json.message || 'Gagal mengambil data', 'warning');
              return [];
            }
            updateSummaryUI(json.summary);
            return json.data || [];
          }
        },
        columns: [
          {
            data: null,
            className: 'text-center text-muted fs-2',
            render: function(data, type, row, meta) {
              return meta.row + 1;
            }
          },
          {
            data: 'tgl_resep',
            render: function(data, type, row) {
              const tgl = formatTanggalIndo(data || row.visit_date);
              const noPermintaan = row.permintaan_number ? `<div class="fs-1 text-muted">No: ${row.permintaan_number}</div>` : '';
              return `<div><span class="fw-semibold">${tgl}</span>${noPermintaan}</div>`;
            }
          },
          {
            data: 'patient_name',
            render: function(data, type, row) {
              const rm = row.nomor_rm ? `<span class="badge bg-light text-dark border me-1">RM: ${row.nomor_rm}</span>` : '';
              const genderBadge = row.patient_gender ? `<span class="badge ${row.patient_gender === 'L' || row.patient_gender === 'Laki-laki' ? 'bg-primary-subtle text-primary' : 'bg-danger-subtle text-danger'} fs-1">${row.patient_gender}</span>` : '';
              const age = row.patient_age && row.patient_age !== '-' ? `<span class="text-muted fs-2 ms-1">(${row.patient_age})</span>` : '';
              return `
                <div>
                  <div class="fw-bold text-dark">${data || '-'}</div>
                  <div class="mt-1">${rm} ${genderBadge} ${age}</div>
                </div>
              `;
            }
          },
          {
            data: 'pharmacy_name_generic',
            render: function(data, type, row) {
              const code = row.pharmacy_code ? `<span class="badge bg-secondary-subtle text-secondary fs-1 me-1">${row.pharmacy_code}</span>` : '';
              const generic = data || row.item_name || '-';
              const trade = row.pharmacy_name_trade ? `<div class="fs-2 text-muted">Merk: ${row.pharmacy_name_trade}</div>` : '';
              return `<div><div class="fw-semibold">${code}${generic}</div>${trade}</div>`;
            }
          },
          {
            data: 'qty',
            className: 'text-center',
            render: function(data, type, row) {
              const unit = row.pharmacy_unit || '';
              return `<span class="badge bg-primary-subtle text-primary fw-bold fs-3">${Number(data || 0).toLocaleString('id-ID')} ${unit}</span>`;
            }
          },
          {
            data: 'signa',
            render: function(data, type, row) {
              const signaText = data ? `<div class="fw-semibold text-dark"><i class="ti ti-clock-check me-1 text-primary"></i>${data}</div>` : '<div class="text-muted fs-2 fst-italic">Aturan tidak tercantum</div>';
              const catatan = row.catatan ? `<div class="fs-1 text-muted mt-1"><i class="ti ti-notes me-1"></i>${row.catatan}</div>` : '';
              return `<div>${signaText}${catatan}</div>`;
            }
          },
          {
            data: 'doctor_name',
            render: function(data) {
              return `<div><i class="ti ti-stethoscope me-1 text-info"></i><span class="fs-2 fw-semibold">${data || '-'}</span></div>`;
            }
          },
          {
            data: 'poli_name',
            render: function(data, type, row) {
              const source = row.source_hub ? `<div class="fs-1 text-muted">${row.source_hub}</div>` : '';
              return `<div><span class="fs-2 fw-semibold text-dark">${data || '-'}</span>${source}</div>`;
            }
          },
          {
            data: 'status_badge',
            className: 'text-center',
            render: function(data) {
              return data || '<span class="badge bg-secondary">-</span>';
            }
          },
          {
            data: null,
            className: 'text-center no-print',
            orderable: false,
            render: function(data, type, row) {
              return `
                <button type="button" class="btn btn-sm btn-outline-primary btn-view-usage"
                        data-id="${row.id_pharmacy_details}"
                        title="Lihat Detail Resep">
                  <i class="ti ti-eye"></i>
                </button>
              `;
            }
          }
        ],
        order: [[1, 'desc']],
        pageLength: 25,
        language: {
          search: "Cari Cepat:",
          searchPlaceholder: "Nama pasien, RM, dokter, obat...",
          lengthMenu: "Tampilkan _MENU_ data",
          info: "Menampilkan _START_ - _END_ dari _TOTAL_ data pemakaian",
          infoEmpty: "Tidak ada data pemakaian ditemukan",
          zeroRecords: "Tidak ada riwayat pemakaian obat yang cocok",
          paginate: {
            first: "Awal",
            last: "Akhir",
            next: "»",
            previous: "«"
          }
        }
      });

      // Change on Select2 Obat
      $('#selectObat').on('change', function() {
        selectedIdPharmacy = $(this).val();
        tableUsage.ajax.reload();
      });

      // Reset Pilihan Obat
      $('#btnResetObat').on('click', function() {
        $('#selectObat').val('all').trigger('change');
      });

      // Terapkan Filter Tanggal / Status / Layanan
      $('#btnApplyFilter').on('click', function() {
        tableUsage.ajax.reload();
      });

      // Reset Periode Tanggal
      $('#btnResetDate').on('click', function() {
        $('#fromDate').val('');
        $('#toDate').val('');
        $('#filterStatus').val('all');
        $('#filterLayanan').val('');
        tableUsage.ajax.reload();
      });

      // Refresh Table
      $('#btnRefresh').on('click', function() {
        tableUsage.ajax.reload(null, false);
      });

      // Print Button
      $('#btnPrint').on('click', function() {
        window.print();
      });

      // View Detail Modal
      $(document).on('click', '.btn-view-usage', function() {
        const id = $(this).data('id');
        $('#modalUsageContent').html(`
          <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">Memuat rincian resep...</p>
          </div>
        `);
        $('#modalUsageDetail').modal('show');

        $.ajax({
          url: usageApiUrl,
          type: 'GET',
          data: { action: 'detail', id_pharmacy_details: id },
          success: function(res) {
            if (res.status !== 'success' || !res.data) {
              $('#modalUsageContent').html(`
                <div class="alert alert-danger mb-0">
                  <i class="ti ti-alert-triangle me-1"></i>${res.message || 'Data resep tidak ditemukan.'}
                </div>
              `);
              return;
            }

            const d = res.data;
            const tgl = formatTanggalIndo(d.tgl_resep || d.visit_date);

            // Other medicines list in the same prescription
            let otherMedsHtml = '';
            if (Array.isArray(d.other_medicines) && d.other_medicines.length > 0) {
              otherMedsHtml = `
                <div class="table-responsive mt-2">
                  <table class="table table-sm table-bordered mb-0 fs-2">
                    <thead class="table-light">
                      <tr>
                        <th>Obat dalam Resep</th>
                        <th width="70" class="text-center">Qty</th>
                        <th>Signa / Dosis</th>
                        <th>Catatan</th>
                      </tr>
                    </thead>
                    <tbody>
                      ${d.other_medicines.map(m => `
                        <tr class="${String(m.id_pharmacy_details) === String(d.id_pharmacy_details) ? 'table-primary fw-bold' : ''}">
                          <td>${m.nama_obat || '-'} ${String(m.id_pharmacy_details) === String(d.id_pharmacy_details) ? '<span class="badge bg-primary ms-1">Item Ini</span>' : ''}</td>
                          <td class="text-center">${Number(m.qty || 0).toLocaleString('id-ID')} ${m.pharmacy_unit || ''}</td>
                          <td>${m.signa || '-'}</td>
                          <td>${m.catatan || '-'}</td>
                        </tr>
                      `).join('')}
                    </tbody>
                  </table>
                </div>
              `;
            }

            const html = `
              <!-- INFORMASI PASIEN -->
              <div class="card bg-light border mb-3">
                <div class="card-body p-3">
                  <div class="row align-items-center">
                    <div class="col-md-7">
                      <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-primary">RM: ${d.nomor_rm || '-'}</span>
                        <span class="badge bg-secondary-subtle text-secondary">${d.patient_gender || '-'}</span>
                        <span class="badge bg-info-subtle text-info">${d.provider_name || 'Umum'}</span>
                      </div>
                      <h5 class="fw-bold mb-1">${d.patient_name || '-'}</h5>
                      <div class="text-muted fs-3">
                        <i class="ti ti-map-pin me-1"></i>Alamat: ${d.patient_address || '-'}
                      </div>
                    </div>
                    <div class="col-md-5 text-md-end mt-2 mt-md-0">
                      <div class="fs-2 text-muted">Tanggal Pemberian Resep</div>
                      <h6 class="fw-bold my-1 text-dark">${tgl}</h6>
                      <div class="fs-2 text-muted">No. Tiket: <strong>${d.permintaan_number || '-'}</strong></div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- DETAIL OBAT YANG DIPERIKSA -->
              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <div class="border rounded p-3 h-100">
                    <h6 class="fw-bold border-bottom pb-2 mb-2 text-primary">
                      <i class="ti ti-pill me-1"></i>Rincian Obat Terpilih
                    </h6>
                    <table class="table table-sm table-borderless mb-0 fs-3">
                      <tr>
                        <td width="130" class="text-muted">Kode Obat</td>
                        <td class="fw-bold"><code>${d.pharmacy_code || '-'}</code></td>
                      </tr>
                      <tr>
                        <td class="text-muted">Nama Generik</td>
                        <td class="fw-semibold">${d.pharmacy_name_generic || d.item_name || '-'}</td>
                      </tr>
                      <tr>
                        <td class="text-muted">Nama Pabrik/Dagang</td>
                        <td>${d.pharmacy_name_trade || '-'}</td>
                      </tr>
                      <tr>
                        <td class="text-muted">Jumlah Diberikan</td>
                        <td class="fw-bold text-primary fs-4">${Number(d.qty || 0).toLocaleString('id-ID')} ${d.pharmacy_unit || ''}</td>
                      </tr>
                      <tr>
                        <td class="text-muted">Signa / Aturan</td>
                        <td class="fw-bold text-success">${d.signa || '-'}</td>
                      </tr>
                      <tr>
                        <td class="text-muted">Catatan Resep</td>
                        <td>${d.catatan || '-'}</td>
                      </tr>
                    </table>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="border rounded p-3 h-100">
                    <h6 class="fw-bold border-bottom pb-2 mb-2 text-info">
                      <i class="ti ti-stethoscope me-1"></i>Layanan & Dokter
                    </h6>
                    <table class="table table-sm table-borderless mb-0 fs-3">
                      <tr>
                        <td width="130" class="text-muted">Dokter Peresep</td>
                        <td class="fw-bold">${d.doctor_name || '-'}</td>
                      </tr>
                      <tr>
                        <td class="text-muted">Poli / Unit</td>
                        <td class="fw-semibold">${d.poli_name || '-'} (${d.source_hub || 'Rawat Jalan'})</td>
                      </tr>
                      <tr>
                        <td class="text-muted">Visit ID</td>
                        <td><code>${d.visit_ID || '-'}</code></td>
                      </tr>
                      <tr>
                        <td class="text-muted">Jenis Resep</td>
                        <td><span class="badge bg-light text-dark border">${d.tipe_obat || 'Non Racikan'}</span></td>
                      </tr>
                      <tr>
                        <td class="text-muted">Catatan Dokter</td>
                        <td class="text-muted">${d.catatan_permintaan || '-'}</td>
                      </tr>
                    </table>
                  </div>
                </div>
              </div>

              <!-- DAFTAR LENGKAP OBAT DALAM RESEP -->
              <div class="border rounded p-3">
                <h6 class="fw-bold mb-2 d-flex align-items-center gap-1">
                  <iconify-icon icon="solar:folder-medicine-bold" class="text-primary fs-5"></iconify-icon>
                  Seluruh Obat dalam Kunjungan / Resep Ini
                </h6>
                ${otherMedsHtml}
              </div>
            `;

            $('#modalUsageContent').html(html);
          },
          error: function() {
            $('#modalUsageContent').html(`
              <div class="alert alert-danger mb-0">
                <i class="ti ti-alert-triangle me-1"></i>Gagal memuat detail resep dari server.
              </div>
            `);
          }
        });
      });

    });
  </script>
</body>

</html>
