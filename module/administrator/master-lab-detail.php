<?php
$title = 'Master Laboratorium';
require '../../controller/view.php';

// Data master lab bersifat global: hanya superadmin/administrator.
if (!isset($_SESSION['uid_user']) || !in_array($_SESSION['roles'] ?? '', ['superadmin', 'administrator'], true)) {
  header('Location: ../../');
  exit;
}

// Dulu $_GET['kode'] disisipkan langsung ke SQL (injection) & nama dicetak tanpa escape (XSS).
$kode = trim((string) ($_GET['kode'] ?? ''));
$st = $koneksi->prepare('SELECT id_lab, kode, assemen, tarif, status FROM laboratorium_detail WHERE kode = ? LIMIT 1');
$st->bind_param('s', $kode);
$st->execute();
$lab = $st->get_result()->fetch_assoc();
$st->close();
if (!$lab) {
  header('Location: master-lab');
  exit;
}
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    #ldTable th { font-size: 11px; font-weight: 700; color: #334155; white-space: nowrap; text-transform: uppercase; letter-spacing: .3px; }
    #ldTable td { vertical-align: middle; font-size: 13px; color: #0f172a; }
    .ld-nama { font-weight: 700; color: #0f172a; }
    .ld-kode { font-family: ui-monospace, Menlo, monospace; font-size: 12px; background: #f1f5f9; color: #0f172a; border-radius: 6px; padding: 2px 7px; }
    .ld-n { display: inline-block; min-width: 34px; text-align: center; padding: 3px 8px; border-radius: 7px; font-size: 12px; font-weight: 700; background: #f1f5f9; color: #334155; }
    .ld-modal .modal-content { border: 0; border-radius: 16px; }
    .ld-modal .form-label { font-weight: 600; color: #1e293b; font-size: 13px; }
    .ld-modal .is-invalid { border-color: #dc3545; }
    .ld-hint { color: #475569; font-size: 12px; }
    .ld-tipe { display: inline-block; padding: 3px 9px; border-radius: 7px; font-size: 11px; font-weight: 700; }
    .ld-tipe.numerik { background: #dbeafe; color: #1e40af; }
    .ld-tipe.teks { background: #fef3c7; color: #92400e; }
    .ld-tipe.keduanya { background: #e0e7ff; color: #3730a3; }
  </style>
</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
    <?php require '../admin/sidebar.php'; ?>
    <div class="body-wrapper">
      <?php require '../admin/navbar-master.php'; ?>
      <div class="body-wrapper-inner">
        <div class="container-fluid">

          <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
              <h4 class="fw-bold mb-0 text-dark"><i class="ti ti-flask text-primary me-2"></i><?= $h($lab['assemen']) ?></h4>
              <div class="text-dark small d-flex flex-wrap align-items-center gap-2 mt-1">
                <span class="ld-kode"><?= $h($lab['kode']) ?></span>
                <span>Tarif: <b><?= (int) $lab['tarif'] ? 'Rp ' . number_format((int) $lab['tarif'], 0, ',', '.') : 'belum diatur' ?></b></span>
                <span class="badge <?= (int) $lab['status'] === 1 ? 'bg-success' : 'bg-danger' ?>"><?= (int) $lab['status'] === 1 ? 'AKTIF' : 'TIDAK AKTIF' ?></span>
              </div>
            </div>
            <div class="d-flex gap-2">
              <a class="btn btn-light border" href="module/administrator/master-lab"><i class="ti ti-arrow-left me-1"></i>Kembali</a>
              <button class="btn btn-primary" id="btnTambah"><i class="ti ti-plus me-1"></i>Tambah Parameter</button>
            </div>
          </div>

          <div class="card border-0 shadow-sm" style="border-radius:16px">
            <div class="card-body p-3 p-md-4">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="fw-semibold text-dark">Parameter pemeriksaan <span class="ld-n ms-1" id="ldJumlah">-</span></div>
                <div class="small text-dark">Parameter yang sudah punya hasil pasien tidak bisa dihapus.</div>
              </div>
              <div class="table-responsive">
                <table class="table table-hover align-middle w-100" id="ldTable">
                  <thead>
                    <tr>
                      <th class="text-center" width="100">Aksi</th>
                      <th>Urutan</th>
                      <th>Parameter</th>
                      <th>Satuan</th>
                      <th title="Jenis isian hasil di modul lab">Tipe Isian</th>
                      <th>Minimum</th>
                      <th>Maksimum</th>
                      <th class="text-center" title="Jumlah hasil pasien yang memakai parameter ini">Hasil</th>
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

  <div class="modal fade ld-modal" id="programModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form id="programForm" class="modal-content" novalidate>
        <div class="modal-header">
          <h5 class="modal-title fw-bold text-dark" id="ldModalTitle">Tambah Parameter</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="aksi" value="simpan">
          <input type="hidden" name="id_item" id="id_item">
          <input type="hidden" name="kode_lab" id="kode_lab" value="<?= $h($lab['kode']) ?>">
          <div class="row">
            <div class="col-6 mb-3">
              <label class="form-label" for="urutan">Urutan</label>
              <input type="number" min="0" max="99999" name="urutan" id="urutan" class="form-control" placeholder="otomatis">
              <div class="ld-hint mt-1">Kosongkan untuk ditaruh paling akhir.</div>
            </div>
            <div class="col-6 mb-3">
              <label class="form-label" for="satuan">Satuan</label>
              <input type="text" name="satuan" id="satuan" class="form-control" maxlength="100" placeholder="mg/dL">
            </div>
            <div class="col-12 mb-3">
              <label class="form-label" for="nama">Nama Parameter <span class="text-danger">*</span></label>
              <input type="text" name="nama" id="nama" class="form-control" maxlength="100" required>
            </div>
            <div class="col-12 mb-3">
              <label class="form-label" for="tipe_isian">Tipe Isian Hasil</label>
              <select name="tipe_isian" id="tipe_isian" class="form-select">
                <option value="keduanya">Angka atau teks (bebas)</option>
                <option value="numerik">Angka saja (numerik)</option>
                <option value="teks">Teks saja</option>
              </select>
              <div class="ld-hint mt-1" id="tipeHint"></div>
            </div>
            <div class="col-6 mb-1">
              <label class="form-label" for="minimum">Minimum</label>
              <input type="text" name="minimum" id="minimum" class="form-control" maxlength="250">
            </div>
            <div class="col-6 mb-1">
              <label class="form-label" for="maksimum">Maksimum</label>
              <input type="text" name="maksimum" id="maksimum" class="form-control" maxlength="100">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary" id="ldSimpan"><i class="ti ti-device-floppy me-1"></i>Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <?php require '../admin/library.php'; ?>
  <script>
    $(function() {
      const API = 'controller/master/labDetailController.php';
      const KODE = <?= json_encode($lab['kode'], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
      const esc = t => $('<div>').text(t ?? '').html();
      const fmt = n => Number(n || 0).toLocaleString('id-ID');
      const toast = (icon, title) => Swal.fire({ toast: true, position: 'top-end', icon, title, showConfirmButton: false, timer: 2400 });
      const TIPE = { numerik: 'Angka', teks: 'Teks', keduanya: 'Angka / teks' };
      const HINT = {
        numerik: 'Petugas lab hanya bisa mengisi angka (contoh 12,5). Hasil otomatis diberi tanda ↑/↓ bila di luar nilai minimum–maksimum. Minimum/maksimum harus angka.',
        teks: 'Petugas lab mengisi teks bebas, mis. Positif/Negatif atau Kuning jernih.',
        keduanya: 'Petugas lab boleh mengisi angka maupun teks. Tanda ↑/↓ hanya muncul bila hasil dan batasnya berupa angka.'
      };
      const hint = () => $('#tipeHint').text(HINT[$('#tipe_isian').val()] || '');
      $('#tipe_isian').on('change', hint);
      const kosong = v => (v === null || v === '') ? '<span class="text-dark">-</span>' : esc(v);

      const tabel = $('#ldTable').DataTable({
        processing: true,
        paging: false,
        order: [[1, 'asc']],
        ajax: {
          url: API,
          type: 'GET',
          data: { kode: KODE },
          dataSrc: function(json) {
            if (!json || json.status !== 'success') {
              Swal.fire('Gagal', (json && json.message) || 'Gagal mengambil data.', 'error');
              return [];
            }
            $('#ldJumlah').text(fmt((json.data || []).length));
            return json.data || [];
          },
          error: xhr => Swal.fire('Gagal', xhr.status === 401 || xhr.status === 403 ? 'Sesi berakhir atau akses ditolak.' : 'Gagal mengambil data.', 'error')
        },
        language: { search: 'Cari:', info: '_TOTAL_ parameter', infoEmpty: 'Belum ada parameter', infoFiltered: '(dari _MAX_)', emptyTable: 'Belum ada parameter. Klik "Tambah Parameter".', zeroRecords: 'Tidak ditemukan.', processing: 'Memuat...' },
        columns: [{
            data: null, orderable: false, searchable: false, className: 'text-center',
            render: (d, t, r) => `<div class="btn-group btn-group-sm">
              <button type="button" class="btn btn-warning edit-btn" data-id="${r.id}" title="Edit"><i class="ti ti-edit"></i></button>
              <button type="button" class="btn btn-danger delete-btn" data-id="${r.id}" data-nama="${esc(r.assemen)}" data-hasil="${r.dipakai}" title="Hapus"><i class="ti ti-trash"></i></button></div>`
          },
          { data: 'urutan', render: (v, t) => t === 'display' ? kosong(v) : (parseInt(v, 10) || 0) },
          { data: 'assemen', render: (v, t) => t === 'display' ? `<span class="ld-nama">${esc(v)}</span>` : v },
          { data: 'satuan', render: (v, t) => t === 'display' ? kosong(v) : v },
          { data: 'tipe_isian', render: (v, t) => t === 'display' ? `<span class="ld-tipe ${esc(v)}">${esc(TIPE[v] || v)}</span>` : v },
          { data: 'minimum', render: (v, t) => t === 'display' ? kosong(v) : v },
          { data: 'maksimum', render: (v, t) => t === 'display' ? kosong(v) : v },
          { data: 'dipakai', className: 'text-center', render: (v, t) => t === 'display' ? `<span class="ld-n">${fmt(v)}</span>` : v }
        ]
      });

      function bersih() { $('#programForm .is-invalid').removeClass('is-invalid'); }

      $('#btnTambah').on('click', function() {
        $('#programForm')[0].reset();
        $('#id_item').val('');
        bersih();
        hint();
        $('#ldModalTitle').text('Tambah Parameter');
        $('#programModal').modal('show');
      });

      $(document).on('click', '.edit-btn', function() {
        $.getJSON(API, { id: $(this).data('id') }).done(function(res) {
          if (res.status !== 'success') return Swal.fire('Gagal', res.message || 'Data tidak ditemukan.', 'error');
          const d = res.data;
          $('#programForm')[0].reset();
          bersih();
          $('#id_item').val(d.id);
          $('#urutan').val(d.urutan);
          $('#nama').val(d.assemen);
          $('#satuan').val(d.satuan);
          $('#minimum').val(d.minimum);
          $('#maksimum').val(d.maksimum);
          $('#tipe_isian').val(d.tipe_isian || 'keduanya');
          hint();
          $('#ldModalTitle').text('Edit Parameter');
          $('#programModal').modal('show');
        }).fail(() => Swal.fire('Error', 'Gagal mengambil data.', 'error'));
      });

      $('#programForm').on('submit', function(e) {
        e.preventDefault();
        bersih();
        if (!$('#nama').val().trim()) {
          $('#nama').addClass('is-invalid').focus();
          return Swal.fire('Nama parameter wajib diisi', '', 'warning');
        }
        $('#ldSimpan').prop('disabled', true);
        $.post(API, $(this).serialize(), null, 'json').done(function(res) {
          $('#programModal').modal('hide');
          tabel.ajax.reload(null, false);
          toast('success', res.message);
        }).fail(function(xhr) {
          const r = xhr.responseJSON || {};
          if (r.data && r.data.field) $('#' + (r.data.field === 'nama' ? 'nama' : r.data.field)).addClass('is-invalid').focus();
          Swal.fire('Gagal', r.message || 'Terjadi kesalahan server.', 'error');
        }).always(() => $('#ldSimpan').prop('disabled', false));
      });

      $(document).on('click', '.delete-btn', function() {
        const id = $(this).data('id'), nama = $(this).attr('data-nama');
        if (Number($(this).attr('data-hasil')) > 0) {
          return Swal.fire('Tidak bisa dihapus', `"${nama}" sudah punya ${fmt($(this).attr('data-hasil'))} hasil pasien. Ubah saja namanya bila perlu.`, 'info');
        }
        Swal.fire({
          title: 'Hapus parameter?', text: `"${nama}" akan dihapus.`, icon: 'warning',
          showCancelButton: true, confirmButtonText: 'Ya, Hapus', cancelButtonText: 'Batal', confirmButtonColor: '#dc3545'
        }).then(function(r) {
          if (!r.isConfirmed) return;
          $.post(API, { aksi: 'hapus', id_item: id }, null, 'json').done(function(res) {
            tabel.ajax.reload(null, false);
            toast('success', res.message);
          }).fail(xhr => Swal.fire('Tidak bisa dihapus', (xhr.responseJSON && xhr.responseJSON.message) || 'Gagal menghapus data.', 'error'));
        });
      });
    });
  </script>
</body>

</html>
