<?php
$title = 'Permintan Farmasi';
require '../../controller/view.php';

?>
<!doctype html>
<html lang="en">

<head>
  <base href="../../">
  <?php
  require '../../assets/template/head.php';
  ?>
</head>

<body>
  <!--  Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <!-- Sidebar Start -->
    <?php
    require 'sidebar.php';
    ?>
    <!--  Sidebar End -->
    <!--  Main wrapper -->
    <div class="body-wrapper">
      <!--  Header Start -->
      <?php
      require 'navbar.php';
      ?>
      <!--  Header End -->
      <div class="body-wrapper-inner">
        <div class="container-fluid">
          <?php
          $rme = $_GET['rme']; // default a
          if ($rme == 'a') {
            include 'menu_rme.php';
          } else if ($rme == 'b') {
            include 'menu_rmeb.php';
          } else if ($rme == 'c') {
            include 'menu_rme_inap.php';
          }
          ?>
          <div class="row">
            <div class="col-12">
              <?php require 'card-pasien.php';?>
            </div>
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Data Item Farmasi</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                      <a href="module/admin/print/print_all_resep?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>" target="_blank">
                        <button class="btn btn-light"><i class="fas fa-print"></i> Print</button>
                      </a>
                      <button class="btn btn-success d-none" id="btnKirimObat"><i class="ti ti-send"></i> Kirim Obat <span class="badge bg-light text-dark ms-1" id="btnKirimObatCount">0</span></button>
                      <button class="btn btn-primary" id="btnTambah"><i class="fas fa-plus"></i> Tambah</button>
                    </div>
                  </div>
                  <div class="alert alert-danger d-flex align-items-center mb-4 peringatanAlergi d-none" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <div>
                      <strong>Peringatan Alergi:</strong><span id="desAplergi"></span>.
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTable">
                      <thead>
                        <tr>
                          <th class="text-dark fw-normal">Tanggal</th>
                          <th class="text-dark fw-normal">Type</th>
                          <th scope="col" class="text-dark fw-normal">Catatan</th>
                          <th scope="col" class="text-dark fw-normal text-center">Status</th>
                          <th scope="col" class="text-dark fw-normal text-center">Actions</th>
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
  <?php
  require 'library.php';
  ?>
</body>
<div class="modal fade" id="programModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="programForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id_permintaan_farmasi" id="id_permintaan_farmasi">
        <input type="hidden" name="id_visit" id="id_visit" value="<?= $_GET['no'] ?>">
        <div class="row">
          <div class="col-12">
            <div class="alert alert-primary" role="alert">
              Buat Tiket Order Permintaan Farmasi Sebelum Membuat Isi Rincian Obat yang akan dibuat
            </div>
          </div>
          <div class="col-12">
            <div class="mb-3">
              <label class="form-label required">Type</label>
              <select class='form-select' required id="tipe_obat" name="tipe_obat">
                <option value="">PILIH</option>
                <option value="Racikan">Racikan</option>
                <option value="Non Racikan">Non Racikan</option>
                <option value="BMHP">BMHP</option>
              </select>
            </div>
          </div>
          <div class="col-6 racikan-field">
            <div class="mb-3">
              <label class="form-label">Jumlah </label>
              <input type="number" name="rck_jumlah" id="rck_jumlah" class='form-control'>
            </div>
          </div>

          <div class="col-6 racikan-field">
            <div class="mb-3">
              <label class="form-label">Satuan </label>
              <input type="text" id='rck_satuan' name='rck_satuan' class='form-control'>
            </div>
          </div>

          <div class="col-12 racikan-field">
            <div class="mb-3">
              <label class="form-label">Signa </label>
              <input type="text" name="rck_signa" id='rck_signa' class='form-control'>
            </div>
          </div>
          <div class="col-12">
            <div class="mb-3">
              <label class="form-label">Catatan Bebas</label>
              <textarea name="catatan_permintaan" id="catatan_permintaan" class="form-control" rows="5"></textarea>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>
<!-- 💊 Modal Rincian Obat -->
<div class="modal fade" id="rincianObatModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Rincian Obat <small class="text-muted fw-normal" id="rincianObatInfo"></small></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
          <table class="table table-sm table-bordered align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="text-center" style="width:40px">No</th>
                <th>Item</th>
                <th class="text-center">Qty</th>
                <th>Signa</th>
                <th>Catatan</th>
              </tr>
            </thead>
            <tbody id="rincianObatBody"></tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
<!-- 🧪 Modal Form Racikan -->
<style>
  #racikanModal .modal-content {
    border: 0;
    border-radius: 14px;
    overflow: hidden;
  }

  #racikanModal .modal-header {
    background: linear-gradient(135deg, #5d87ff 0%, #49beff 100%);
    color: #fff;
    border: 0;
  }

  #racikanModal .modal-header .btn-close {
    filter: invert(1) brightness(2);
  }

  #racikanModal .modal-body {
    background: #f6f8fb;
  }

  #racikanModal .rck-card {
    background: #fff;
    border: 1px solid #e5eaef;
    border-radius: 12px;
    padding: 18px 20px;
    margin-bottom: 16px;
  }

  #racikanModal .rck-step {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 14px;
  }

  #racikanModal .rck-step-no {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #5d87ff;
    color: #fff;
    font-weight: 600;
    font-size: .85rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  #racikanModal .rck-step-title {
    font-weight: 600;
    font-size: 1rem;
    margin: 0;
  }

  #racikanModal .rck-step-sub {
    font-size: .78rem;
    color: #6c757d;
    margin: 0;
  }

  #racikanModal .form-label {
    font-size: .8rem;
    font-weight: 600;
    color: #2a3547;
    margin-bottom: 4px;
  }

  #racikanModal .req {
    color: #fa896b;
  }

  #racikanModal .rck-help {
    font-size: .75rem;
    color: #6c757d;
  }

  /* pilihan tipe racik */
  #racikanModal .tipe-option {
    display: block;
    border: 2px solid #e5eaef;
    border-radius: 10px;
    padding: 10px 14px;
    cursor: pointer;
    height: 100%;
    transition: all .15s;
  }

  #racikanModal .btn-check:checked+.tipe-option {
    border-color: #5d87ff;
    background: #ecf2ff;
  }

  #racikanModal .tipe-option .tipe-nama {
    font-weight: 600;
  }

  #racikanModal .tipe-option .tipe-desc {
    font-size: .75rem;
    color: #6c757d;
  }

  /* template */
  #racikanModal .rck-template {
    background: #fff8e6;
    border: 1px dashed #ffae1f;
    border-radius: 12px;
    padding: 12px 16px;
    margin-bottom: 16px;
  }

  /* ringkasan */
  #racikanModal .rck-summary {
    background: #ecf2ff;
    border-radius: 10px;
    padding: 10px 14px;
    font-size: .85rem;
    color: #2a3547;
  }

  /* tabel bahan */
  #racikanModal .bahan-table th {
    font-size: .75rem;
    font-weight: 600;
    color: #5a6a85;
    background: #f6f8fb;
    white-space: nowrap;
  }

  #racikanModal .bahan-table td {
    vertical-align: middle;
  }

  #racikanModal .bahan-no {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: #e5eaef;
    font-size: .8rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }

  #racikanModal .bahan-total {
    display: inline-block;
    font-weight: 600;
    color: #13a58b;
    white-space: nowrap;
  }

  #racikanModal .bahan-total small {
    display: block;
    font-weight: 400;
    color: #6c757d;
  }

  #racikanModal .bahan-empty {
    text-align: center;
    color: #6c757d;
    padding: 24px 0;
  }

  #racikanModal .select2-container .select2-selection--single {
    height: 38px;
    border-color: #dfe5ef;
  }

  #racikanModal .select2-container .select2-selection--single .select2-selection__rendered {
    line-height: 36px;
  }

  #racikanModal .select2-container .select2-selection--single .select2-selection__arrow {
    height: 36px;
  }

  #racikanModal .is-invalid+.select2-container .select2-selection {
    border-color: #fa896b;
  }
</style>
<div class="modal fade" id="racikanModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <form id="racikanForm" class="modal-content" novalidate>
      <div class="modal-header">
        <div>
          <h5 class="modal-title text-white mb-0"><i class="fas fa-mortar-pestle me-2"></i>Form Racikan</h5>
          <small class="opacity-75" id="racikanInfo"></small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <input type="hidden" id="rckId">

        <!-- TEMPLATE -->
        <div class="rck-template d-flex flex-wrap align-items-center gap-3">
          <div class="d-flex align-items-center gap-2">
            <i class="fas fa-bookmark text-warning fs-5"></i>
            <div>
              <div class="fw-semibold">Pakai racikan tersimpan?</div>
              <div class="rck-help">Pilih template untuk mengisi form otomatis.</div>
            </div>
          </div>
          <div class="flex-grow-1" style="min-width:260px">
            <select id="rckTemplate" class="form-select" style="width:100%"></select>
          </div>
        </div>

        <!-- 1. INFORMASI RACIKAN -->
        <div class="rck-card">
          <div class="rck-step">
            <span class="rck-step-no">1</span>
            <div>
              <p class="rck-step-title">Informasi Racikan</p>
              <p class="rck-step-sub">Nama racikan dan berapa banyak yang dibuat.</p>
            </div>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" for="rckNama">Nama Racikan <span class="req">*</span></label>
              <input type="text" id="rckNama" class="form-control" maxlength="255" placeholder="Contoh: Racikan Batuk Anak">
            </div>
            <div class="col-md-6">
              <label class="form-label" for="rckJumlah">Jumlah yang Dibuat <span class="req">*</span></label>
              <div class="input-group">
                <input type="number" id="rckJumlah" class="form-control" min="1" step="1" placeholder="10">
                <input type="text" id="rckSatuan" class="form-control" list="rckSatuanList" maxlength="100" placeholder="Satuan, mis. Pulvis" style="max-width:55%">
              </div>
              <datalist id="rckSatuanList">
                <option value="Pulvis">
                <option value="Kapsul">
                <option value="Salep">
                <option value="Krim">
                <option value="Sirup">
                <option value="Botol">
                <option value="Pot">
              </datalist>
              <div class="rck-help mt-1">Contoh: 10 Pulvis = dibuat 10 bungkus puyer.</div>
            </div>

            <div class="col-12">
              <label class="form-label">Tipe Racik <span class="req">*</span></label>
              <div class="row g-2">
                <div class="col-md-6">
                  <input type="radio" class="btn-check" name="rckTipe" id="rckTipeDtd" value="DTD">
                  <label class="tipe-option" for="rckTipeDtd">
                    <div class="tipe-nama"><i class="fas fa-cube me-1 text-primary"></i> DTD</div>
                    <div class="tipe-desc">Jumlah bahan diisi <b>untuk 1 bungkus</b>. Total dihitung otomatis (per bungkus × jumlah dibuat).</div>
                  </label>
                </div>
                <div class="col-md-6">
                  <input type="radio" class="btn-check" name="rckTipe" id="rckTipeNonDtd" value="NON_DTD" checked>
                  <label class="tipe-option" for="rckTipeNonDtd">
                    <div class="tipe-nama"><i class="fas fa-cubes me-1 text-primary"></i> Non DTD</div>
                    <div class="tipe-desc">Jumlah bahan diisi <b>total untuk semua bungkus</b>, lalu dibagi rata.</div>
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 2. ATURAN PAKAI -->
        <div class="rck-card">
          <div class="rck-step">
            <span class="rck-step-no">2</span>
            <div>
              <p class="rck-step-title">Aturan Pakai</p>
              <p class="rck-step-sub">Isi frekuensi, signatura akan terisi otomatis.</p>
            </div>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Frekuensi Pemberian</label>
              <div class="input-group">
                <input type="number" id="rckFrekKali" class="form-control" min="0" step="any" placeholder="3">
                <span class="input-group-text">kali ×</span>
                <input type="number" id="rckFrekDosis" class="form-control" min="0" step="any" placeholder="1">
                <select id="rckFrekPeriode" class="form-select">
                  <option value="">Periode</option>
                  <option value="Hari">per Hari</option>
                  <option value="Minggu">per Minggu</option>
                  <option value="Bulan">per Bulan</option>
                </select>
              </div>
              <div class="rck-help mt-1">Contoh: 3 kali × 1 per Hari.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="rckSigna">Signatura <span class="req">*</span></label>
              <input type="text" id="rckSigna" class="form-control" maxlength="255" placeholder="Contoh: 3x1 sesudah makan">
              <div class="rck-help mt-1">Ketik <b>-</b> (strip) jika tidak ada signatura.</div>
            </div>
          </div>
        </div>

        <!-- 3. BAHAN -->
        <div class="rck-card">
          <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div class="rck-step mb-2">
              <span class="rck-step-no">3</span>
              <div>
                <p class="rck-step-title">Bahan Racikan <span class="badge bg-light text-dark ms-1" id="rckBahanCount">0</span></p>
                <p class="rck-step-sub" id="rckTipeHelp"></p>
              </div>
            </div>
            <button type="button" class="btn btn-outline-primary btn-sm" id="rckTambahBahan">
              <i class="fas fa-plus me-1"></i> Tambah Bahan
            </button>
          </div>

          <div class="table-responsive">
            <table class="table bahan-table align-top mb-0">
              <thead>
                <tr>
                  <th style="width:40px">#</th>
                  <th style="min-width:260px">Nama Obat <span class="req">*</span></th>
                  <th style="width:150px" id="rckQtyHeader">Jumlah <span class="req">*</span></th>
                  <th style="width:130px">Total Diambil</th>
                  <th style="min-width:200px">Keterangan</th>
                  <th style="width:50px"></th>
                </tr>
              </thead>
              <tbody id="rckBahanList"></tbody>
            </table>
          </div>
        </div>

        <!-- RINGKASAN -->
        <div class="rck-summary" id="rckSummary"></div>
      </div>
      <div class="modal-footer bg-white">
        <div class="form-check me-auto">
          <input class="form-check-input" type="checkbox" id="rckSimpanTemplate">
          <label class="form-check-label" for="rckSimpanTemplate">
            <i class="fas fa-bookmark text-warning me-1"></i> Simpan sebagai template racikan
          </label>
          <div class="rck-help">Bisa dipakai lagi untuk pasien lain. Nama yang sama akan ditimpa.</div>
        </div>
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary" id="rckSimpan"><i class="fas fa-save me-1"></i> Simpan Racikan</button>
      </div>
    </form>
  </div>
</div>
<script>
  $(function() {
    const rckApi = 'controller/farmasi/racikan.php';
    const $modal = $('#racikanModal');
    const $list = $('#rckBahanList');
    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, c => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[c]));
    const tipeRacik = () => $('input[name="rckTipe"]:checked').val() || 'NON_DTD';
    const angka = (v) => Math.round(v * 10000) / 10000;
    const fmt = (v) => Number(v).toLocaleString('id-ID', {
      maximumFractionDigits: 4
    });

    // select2 obat (ajax) untuk satu baris bahan
    function initSelectObat($select) {
      $select.select2({
        dropdownParent: $modal,
        width: '100%',
        placeholder: 'Ketik nama obat...',
        ajax: {
          url: rckApi,
          dataType: 'json',
          delay: 250,
          data: params => ({
            obat: 1,
            q: params.term || ''
          }),
          processResults: data => ({
            results: data.results || []
          })
        }
      });
    }

    function kosongkanBahan() {
      $list.find('.bahan-obat').each(function() {
        if ($(this).data('select2')) $(this).select2('destroy');
      });
      $list.empty();
    }

    function tambahBahan(b = {}, buka = false) {
      $list.find('.bahan-empty-row').remove();
      const $row = $(`
        <tr class="bahan-row">
          <td><span class="bahan-no"></span></td>
          <td><select class="form-select bahan-obat"></select></td>
          <td>
            <div class="input-group">
              <input type="number" class="form-control bahan-qty" min="0" step="any" placeholder="mis. 1">
            </div>
          </td>
          <td><span class="bahan-total">-</span></td>
          <td><input type="text" class="form-control bahan-catatan" maxlength="1000" placeholder="Opsional"></td>
          <td class="text-end">
            <button type="button" class="btn btn-light text-danger bahan-hapus" title="Hapus bahan">
              <i class="fas fa-trash"></i>
            </button>
          </td>
        </tr>`);
      $row.attr('data-id', b.id || '');
      const $select = $row.find('.bahan-obat');
      if (b.id_pharmacy) {
        $select.append(new Option(b.nama || ('#' + b.id_pharmacy), b.id_pharmacy, true, true));
      }
      $row.find('.bahan-qty').val(b.qty ?? '');
      $row.find('.bahan-catatan').val(b.catatan || '');
      $list.append($row);
      initSelectObat($select);
      perbarui();
      if (buka) $select.select2('open');
    }

    // hitung total per bahan + label + ringkasan
    function perbarui() {
      const dtd = tipeRacik() === 'DTD';
      const jumlah = parseFloat($('#rckJumlah').val()) || 0;
      const satuan = $('#rckSatuan').val().trim() || 'bungkus';
      const $rows = $list.find('.bahan-row');

      $('#rckQtyHeader').html((dtd ? 'Jumlah / 1 Bungkus' : 'Jumlah Total') + ' <span class="req">*</span>');
      $('#rckTipeHelp').html(dtd ?
        'DTD: isi jumlah bahan untuk <b>1 bungkus</b>, total dihitung otomatis.' :
        'Non DTD: isi jumlah <b>total</b> bahan untuk seluruh racikan.');
      $('#rckBahanCount').text($rows.length);

      if (!$rows.length) {
        $list.html(`<tr class="bahan-empty-row"><td colspan="6" class="bahan-empty">
          <i class="fas fa-pills fs-4 d-block mb-2"></i>Belum ada bahan. Klik <b>Tambah Bahan</b>.</td></tr>`);
      }

      $rows.each(function(i) {
        $(this).find('.bahan-no').text(i + 1);
        const qty = parseFloat($(this).find('.bahan-qty').val()) || 0;
        let total = '-';
        if (qty > 0) {
          const t = dtd ? (jumlah > 0 ? angka(qty * jumlah) : null) : qty;
          if (t === null) {
            total = '<span class="text-muted fw-normal">isi jumlah dibuat</span>';
          } else {
            total = Number.isInteger(t) ? fmt(t) :
              `${fmt(Math.ceil(t))}<small>${fmt(t)} dibulatkan ke atas</small>`;
          }
        }
        $(this).find('.bahan-total').html(total);
      });

      // ringkasan
      const nama = $('#rckNama').val().trim();
      const kali = $('#rckFrekKali').val(),
        dosis = $('#rckFrekDosis').val(),
        periode = $('#rckFrekPeriode').val();
      const frek = kali && dosis && periode ? `${kali} × ${dosis} per ${periode}` : '';
      const signa = $('#rckSigna').val().trim();
      const bagian = [
        `<b>${esc(nama || 'Racikan tanpa nama')}</b>`,
        jumlah ? `${esc(jumlah)} ${esc(satuan)}` : '<span class="text-muted">jumlah belum diisi</span>',
        dtd ? 'DTD' : 'Non DTD',
        `${$rows.length} bahan`,
        frek ? esc(frek) : (signa ? `Signa: ${esc(signa)}` : '<span class="text-muted">aturan pakai belum diisi</span>')
      ];
      $('#rckSummary').html(`<i class="fas fa-clipboard-check me-1 text-primary"></i> ${bagian.join(' &nbsp;•&nbsp; ')}`);
    }

    function isiForm(d) {
      $('#rckNama').val(d.nama || '');
      $(d.tipe === 'DTD' ? '#rckTipeDtd' : '#rckTipeNonDtd').prop('checked', true);
      $('#rckJumlah').val(d.jumlah || '');
      $('#rckSatuan').val(d.satuan || '');
      $('#rckSigna').val(d.signa || '');
      $('#rckFrekKali').val(d.frek_kali || '');
      $('#rckFrekDosis').val(d.frek_dosis || '');
      $('#rckFrekPeriode').val(d.frek_periode || '');
      $modal.find('.is-invalid').removeClass('is-invalid');
      kosongkanBahan();
      (d.bahan || []).forEach(b => tambahBahan(b));
      if (!(d.bahan || []).length) tambahBahan();
      perbarui();
    }

    // template racikan
    $('#rckTemplate').select2({
      dropdownParent: $modal,
      width: '100%',
      allowClear: true,
      placeholder: 'Cari template racikan...',
      ajax: {
        url: rckApi,
        dataType: 'json',
        delay: 250,
        data: params => ({
          templates: 1,
          q: params.term || ''
        }),
        processResults: data => ({
          results: data.results || []
        })
      }
    }).on('select2:select', function(e) {
      fetch(`${rckApi}?template=${encodeURIComponent(e.params.data.id)}`)
        .then(res => res.json())
        .then(res => {
          if (res.status !== 'success') {
            Swal.fire('Gagal!', res.message || 'Template tidak dapat dimuat.', 'error');
            return;
          }
          isiForm(res.data); // bahan lama diganti bahan dari template
        });
    });

    // buka form dari tombol "+ Racikan" di Actions
    $(document).on('click', '.racikan-btn', function() {
      const id = $(this).data('id');
      $('#rckId').val(id);
      $('#racikanInfo').text(`Resep #${id}`);
      $('#rckSimpanTemplate').prop('checked', false);
      $('#rckTemplate').val(null).trigger('change');
      kosongkanBahan();
      $list.html('<tr><td colspan="6" class="bahan-empty"><span class="spinner-border spinner-border-sm me-2"></span>Memuat data...</td></tr>');
      $modal.modal('show');

      fetch(`${rckApi}?id=${encodeURIComponent(id)}`)
        .then(res => res.json())
        .then(res => {
          if (res.status !== 'success') {
            $modal.modal('hide');
            Swal.fire('Gagal!', res.message || 'Data racikan tidak dapat dimuat.', 'error');
            return;
          }
          isiForm(res.data);
        })
        .catch(() => {
          $modal.modal('hide');
          Swal.fire('Gagal!', 'Terjadi kesalahan pada server.', 'error');
        });
    });

    $('#rckTambahBahan').on('click', () => tambahBahan({}, true));
    $list.on('click', '.bahan-hapus', function() {
      const $row = $(this).closest('.bahan-row');
      const $select = $row.find('.bahan-obat');
      if ($select.data('select2')) $select.select2('destroy');
      $row.remove();
      perbarui();
    });
    $list.on('input', '.bahan-qty', perbarui);
    $list.on('change', '.bahan-obat', function() {
      $(this).removeClass('is-invalid');
    });
    $modal.on('input change', '#rckNama, #rckJumlah, #rckSatuan, #rckSigna, #rckFrekKali, #rckFrekDosis, #rckFrekPeriode, input[name="rckTipe"]', function() {
      $(this).removeClass('is-invalid');
      perbarui();
    });

    // signatura otomatis dari frekuensi (kalau signatura masih kosong / hasil otomatis)
    $('#rckFrekKali, #rckFrekDosis').on('input', function() {
      const kali = $('#rckFrekKali').val(),
        dosis = $('#rckFrekDosis').val();
      const $signa = $('#rckSigna');
      if (kali && dosis && ($signa.val().trim() === '' || $signa.data('auto'))) {
        $signa.val(`${kali}x${dosis}`).data('auto', true);
        perbarui();
      }
    });
    $('#rckSigna').on('input', function() {
      $(this).data('auto', false);
    });

    function tandai($el) {
      $el.addClass('is-invalid');
      return false;
    }

    $('#racikanForm').on('submit', function(e) {
      e.preventDefault();
      $modal.find('.is-invalid').removeClass('is-invalid');

      // validasi di browser supaya field yang kurang langsung terlihat (validasi utama tetap di server)
      let valid = true;
      if (!$('#rckNama').val().trim()) valid = tandai($('#rckNama'));
      if (!(parseInt($('#rckJumlah').val(), 10) > 0)) valid = tandai($('#rckJumlah'));
      if (!$('#rckSatuan').val().trim()) valid = tandai($('#rckSatuan'));
      if (!$('#rckSigna').val().trim()) valid = tandai($('#rckSigna'));
      const $rows = $list.find('.bahan-row');
      $rows.each(function() {
        if (!$(this).find('.bahan-obat').val()) valid = tandai($(this).find('.bahan-obat'));
        if (!(parseFloat($(this).find('.bahan-qty').val()) > 0)) valid = tandai($(this).find('.bahan-qty'));
      });
      if (!$rows.length) {
        Swal.fire('Lengkapi data', 'Tambahkan minimal 1 bahan racikan.', 'warning');
        return;
      }
      if (!valid) {
        Swal.fire('Lengkapi data', 'Kolom bertanda merah wajib diisi.', 'warning');
        return;
      }

      const payload = {
        id: $('#rckId').val(),
        nama: $('#rckNama').val().trim(),
        tipe: tipeRacik(),
        jumlah: $('#rckJumlah').val(),
        satuan: $('#rckSatuan').val().trim(),
        signa: $('#rckSigna').val().trim(),
        frek_kali: $('#rckFrekKali').val(),
        frek_dosis: $('#rckFrekDosis').val(),
        frek_periode: $('#rckFrekPeriode').val(),
        simpan_template: $('#rckSimpanTemplate').is(':checked'),
        bahan: $rows.map(function() {
          return {
            id: $(this).attr('data-id') || null,
            id_pharmacy: $(this).find('.bahan-obat').val(),
            qty: $(this).find('.bahan-qty').val(),
            catatan: $(this).find('.bahan-catatan').val().trim()
          };
        }).get()
      };

      const $btn = $('#rckSimpan').prop('disabled', true);
      fetch(rckApi, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(res => {
          if (res.status === 'success') {
            $modal.modal('hide');
            Swal.fire('Berhasil!', res.message, 'success');
            $('#periodeTable').DataTable().ajax.reload(null, false);
          } else {
            Swal.fire('Gagal!', res.message || 'Racikan gagal disimpan.', 'error');
          }
        })
        .catch(() => Swal.fire('Gagal!', 'Terjadi kesalahan pada server.', 'error'))
        .finally(() => $btn.prop('disabled', false));
    });
  });
</script>
<script>
  $(document).ready(function() {
    $('#id_pharmacy').select2({
      dropdownParent: $('#programModal'),
      width: '100%'
    });

    $('#id_pharmacy').on('change', function() {
      let harga = $(this).find(':selected').data('harga') || 0;
      $('#harga').val(harga);
    });
  });
</script>
<script>
  const apiUrl = 'controller/visit/permintaanFarmasi?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>';
  const urlParams = new URLSearchParams(window.location.search);
  const rmeParam = urlParams.get('rme') || 'c'; // default kalau kosong
  const nomorm = urlParams.get('rm') || 'c'; // default kalau kosong

  let tiketBelumKirim = [];
  const escapeHtmlRow = (v) => String(v ?? '').replace(/[&<>"']/g, c => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
  }[c]));

  $(document).ready(function() {
    var table = $('#periodeTable').DataTable({
      processing: true,
      serverSide: false, // 🔹 ubah jadi false
      ajax: {
        url: apiUrl,
        type: "GET",
        dataSrc: function(json) {
          // 📤 kumpulkan tiket yang belum dikirim
          tiketBelumKirim = (json.data || [])
            .filter(row => row.status_permintaan == 0)
            .map(row => row.id_permintaan_farmasi);
          $('#btnKirimObatCount').text(tiketBelumKirim.length);
          $('#btnKirimObat').toggleClass('d-none', tiketBelumKirim.length === 0);

          return json.data.map(function(row) {
            return {
              "actions": (function() {

                let btnDelete = '';
                let btnKirim = '';
                let btnRacikan = '';

                // 🧪 tiket racikan yang belum selesai → form racikan
                if (String(row.tipe_obat || '').trim().toLowerCase() === 'racikan' && row.status_permintaan != 3) {
                  btnRacikan = `
      <a class="btn btn-warning racikan-btn"
         href="javascript:;"
         title="Form Racikan"
         data-id="${row.id_permintaan_farmasi}">
        <i class="fas fa-mortar-pestle"></i> + Racikan
      </a>
    `;
                }

                // 📤 belum dikirim → tombol kirim obat per tiket
                if (row.status_permintaan == 0) {
                  btnKirim = `
      <a class="btn btn-primary kirim-btn"
         href="javascript:;"
         title="Kirim Obat"
         data-id="${row.id_permintaan_farmasi}">
        <i class="ti ti-send"></i>
      </a>
    `;
                }

                // ❌ kalau selesai → tidak ada delete
                if (row.status_permintaan != 3) {
                  btnDelete = `
      <a class="btn btn-danger delete-btn" 
         href="javascript:;" 
         data-id="${row.id_permintaan_farmasi}">
        <i class="fas fa-trash"></i>
      </a>
    `;
                }

                return `
    <div class="text-center">
      <div class="btn-group btn-group-sm" role="group">

        <a class="btn btn-success rincian-btn"
           href="javascript:;"
           title="Rincian Obat"
           data-id="${row.id_permintaan_farmasi}"
           data-tipe="${row.tipe_obat ?? ''}">
          <i class="fas fa-pills"></i>
        </a>

        <a class="btn btn-info" 
          href="module/admin/permintaan_farmasi_details?no=${encodeURIComponent(urlParams.get('no'))}&rm=${encodeURIComponent(nomorm)}&rme=${rmeParam}&id=${row.id_permintaan_farmasi}&type=${encodeURIComponent(row.tipe_obat || '')}">
          <i class="fas fa-pencil"></i>
        </a>

        ${btnRacikan}

        ${btnKirim}

        ${btnDelete}

      </div>
    </div>
  `;

              })(),
              "timestamp": row.created_at ?? "-",
              "tipe_obat": escapeHtmlRow(row.tipe_obat ?? "-") + (row.rck_nama ? `<div class="small text-muted">${escapeHtmlRow(row.rck_nama)}</div>` : ''),
              "catatan": row.catatan_permintaan ?? "-",
              "status_permintaan": (function() {
                let status = row.status_permintaan;

                let badgeClass = '';
                let label = '';

                if (status == 1) {
                  badgeClass = 'bg-warning';
                  label = 'Sudah Kirim';
                } else if (status == 2) {
                  badgeClass = 'bg-primary';
                  label = 'Persiapan';
                } else if (status == 3) {
                  badgeClass = 'bg-success';
                  label = 'Selesai';
                } else {
                  badgeClass = 'bg-dark';
                  label = 'Belum Dikirim';
                }

                return `<span class="badge ${badgeClass} d-block text-center">${label}</span>`;
              })()
            };
          });
        }
      },
      columns: [{
          data: "timestamp"
        },
        {
          data: "tipe_obat"
        },
        {
          data: "catatan"
        },
        {
          data: "status_permintaan"
        },
        {
          data: "actions",
          orderable: false,
          searchable: false
        },
      ],
      footerCallback: function(row, data, start, end, display) {
        var api = this.api();

        // Hitung total bobot
        let total = api
          .column(3, {
            page: 'current'
          })
          .data()
          .reduce((a, b) => {
            return (parseFloat(a) || 0) + (parseFloat(b) || 0);
          }, 0);

        // Tampilkan di footer
        $(api.column(3).footer()).html(total.toFixed(2) + " %");
      }
    });

    $('#customSearch').on('keyup', function() {
      table.search(this.value).draw();
    });

    // 🔹 Tambah
    $('#btnTambah').on('click', function() {
      $('#programForm')[0].reset(); // ✅ pakai programForm, bukan addForm
      $('#id_permintaan_farmasi').val('');
      $('#programModal .modal-title').text('Tambah Data');
      $('#programModal').modal('show');
    });

    // 🔹 Submit (Tambah / Update)
    $('#programForm').on('submit', function(e) {
      e.preventDefault();
      let formData = new URLSearchParams(new FormData(this));
      let id = $('#id_permintaan_farmasi').val();

      fetch(apiUrl + (id ? `?id=${id}` : ''), {
          method: id ? 'PUT' : 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: formData
        })
        .then(res => res.json())
        .then(data => {
          if (data.status === 'success') {
            Swal.fire('Berhasil!', data.message, 'success');
            $('#programModal').modal('hide');
            table.ajax.reload(null, false);
          } else {
            Swal.fire('Gagal!', data.message, 'error');
          }
        });
    });

    // 💊 Rincian Obat per tiket
    const escapeHtml = (v) => String(v ?? '-').replace(/[&<>"']/g, c => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[c]));

    $(document).on('click', '.rincian-btn', function() {
      const id = $(this).data('id');
      const isRacikan = String($(this).data('tipe') || '').trim().toLowerCase() === 'racikan';
      const $body = $('#rincianObatBody');

      $('#rincianObatInfo').text($(this).data('tipe') ? `(${$(this).data('tipe')})` : '');
      $body.html('<tr><td colspan="5" class="text-center text-muted">Memuat data...</td></tr>');
      $('#rincianObatModal').modal('show');

      fetch(`controller/visit/permintaanFarmasiDetails?no=${encodeURIComponent(id)}`)
        .then(res => res.json())
        .then(res => {
          const items = (res && res.status === 'success') ? (res.data || []) : null;
          if (items === null) {
            $body.html(`<tr><td colspan="5" class="text-center text-danger">${escapeHtml(res?.message || 'Gagal memuat data.')}</td></tr>`);
            return;
          }
          if (!items.length) {
            $body.html('<tr><td colspan="5" class="text-center text-muted">Belum ada obat pada tiket ini.</td></tr>');
            return;
          }
          $body.html(items.map((item, i) => `
            <tr>
              <td class="text-center">${i + 1}</td>
              <td>${escapeHtml(item.pharmacy_name_generic)}</td>
              <td class="text-center">${escapeHtml(item.qty)}</td>
              <td>${isRacikan ? '-' : escapeHtml(item.signa)}</td>
              <td>${escapeHtml(item.catatan)}</td>
            </tr>
          `).join(''));
        })
        .catch(() => {
          $body.html('<tr><td colspan="5" class="text-center text-danger">Terjadi kesalahan pada server.</td></tr>');
        });
    });

    // 📤 Kirim Obat: satu tiket (tombol di Actions)
    $(document).on('click', '.kirim-btn', function() {
      kirimObat([$(this).data('id')], 'Pastikan data sudah benar.');
    });

    // 📤 Kirim Obat: semua tiket Belum Dikirim sekaligus (tombol header)
    $('#btnKirimObat').on('click', function() {
      const ids = tiketBelumKirim.slice();
      if (!ids.length) {
        Swal.fire('Info', 'Tidak ada tiket yang belum dikirim.', 'info');
        return;
      }
      kirimObat(ids, `${ids.length} tiket akan dikirim bersamaan. Pastikan data sudah benar.`);
    });

    function kirimObat(ids, konfirmasi) {
      Swal.fire({
        title: 'Kirim obat?',
        text: konfirmasi,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, kirim',
        cancelButtonText: 'Batal',
        reverseButtons: true
      }).then((result) => {
        if (!result.isConfirmed) {
          return;
        }
        Swal.fire({
          title: 'Sedang mengirim...',
          text: 'Mohon tunggu, proses sedang berjalan.',
          allowOutsideClick: false,
          allowEscapeKey: false,
          allowEnterKey: false,
          showConfirmButton: false,
          showCancelButton: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });
        $.ajax({
          url: 'controller/farmasi/kirimObat.php',
          type: 'POST',
          contentType: 'application/json',
          dataType: 'json',
          data: JSON.stringify({
            ids: ids
          }),
          success: function(res) {
            if (res.status == 'success') {
              Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: `${res.total || ids.length} tiket obat berhasil dikirim.`,
                confirmButtonText: 'OK'
              });
              table.ajax.reload(null, false);
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: res.message || 'Terjadi kesalahan.',
                confirmButtonText: 'OK'
              });
            }
          },
          error: function(xhr) {
            Swal.fire({
              icon: 'error',
              title: 'Gagal!',
              text: (xhr.responseJSON && xhr.responseJSON.message) || 'Terjadi kesalahan pada server.',
              confirmButtonText: 'OK'
            });
          }
        });
      });
    }

    // 🔹 Delete
    $(document).on('click', '.delete-btn', function() {
      let id = $(this).data('id');
      Swal.fire({
        title: 'Hapus Data?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          fetch(apiUrl + `&id=${id}`, {
              method: 'DELETE'
            })
            .then(res => res.json())
            .then(data => {
              if (data.status === 'success') {
                Swal.fire('Berhasil!', 'Data dihapus.', 'success');
                table.ajax.reload(null, false);
              }
            });
        }
      });
    });
  });
</script>
<script>
  $(document).ready(function() {

    function toggleRacikan() {
      let tipe = $('#tipe_obat').val();

      if (tipe === 'Racikan') {
        $('.racikan-field').show();
      } else {
        $('.racikan-field').hide();
        $('.racikan-field input').val(''); // reset value
      }
    }

    // event change
    $(document).on('change', '#tipe_obat', function() {
      toggleRacikan();
    });

    // initial load
    toggleRacikan();

  });
</script>
<script>
  $(document).ready(function() {
    const params = new URLSearchParams(window.location.search);
    const nomor_visit = params.get('no');
    $.ajax({
      url: 'controller/master/getAlergiObat.php',
      type: 'POST',
      data: {
        visit_id: nomor_visit
      },
      dataType: 'json',
      success: function(response) {
        const desAlObat = response.data?.desAlObat;
        if (response.status === 'success' && desAlObat) {
          $('.peringatanAlergi').removeClass('d-none');
          $('#desAplergi').text(desAlObat);
        } else {
          $('.peringatanAlergi').addClass('d-none');
          $('#desAplergi').text('');
        }
      },
    });
  })
</script>

</html>