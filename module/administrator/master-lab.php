<?php
$title = 'Master Laboratorium';
require '../../controller/view.php';

// Data master lab bersifat global: hanya superadmin/administrator.
if (!isset($_SESSION['uid_user']) || !in_array($_SESSION['roles'] ?? '', ['superadmin', 'administrator'], true)) {
  header('Location: ../../');
  exit;
}
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .lb-kpi { border: 1px solid #e2e8f0; border-radius: 14px; padding: 14px 16px; background: #fff; height: 100%; }
    .lb-kpi .n { font-size: 24px; font-weight: 800; color: #0f172a; line-height: 1.1; }
    .lb-kpi .l { font-size: 12px; font-weight: 600; color: #334155; margin-top: 2px; }
    .lb-chip { border: 1px solid #cbd5e1; background: #fff; color: #334155; border-radius: 999px; padding: 4px 12px; font-size: 12px; font-weight: 700; cursor: pointer; }
    .lb-chip:hover { background: #f1f5f9; }
    .lb-chip.on { background: #1e293b; border-color: #1e293b; color: #fff; }
    #lbTable th { font-size: 11px; font-weight: 700; color: #334155; white-space: nowrap; text-transform: uppercase; letter-spacing: .3px; }
    #lbTable td { vertical-align: middle; font-size: 13px; color: #0f172a; }
    .lb-nama { font-weight: 700; color: #0f172a; }
    .lb-kode { font-family: ui-monospace, Menlo, monospace; font-size: 12px; background: #f1f5f9; color: #0f172a; border-radius: 6px; padding: 2px 7px; }
    .lb-n { display: inline-block; min-width: 34px; text-align: center; padding: 3px 8px; border-radius: 7px; font-size: 12px; font-weight: 700; background: #e0e7ff; color: #3730a3; }
    .lb-n.x { background: #fee2e2; color: #991b1b; }
    .lb-n.u { background: #f1f5f9; color: #334155; }
    .lb-modal .modal-content { border: 0; border-radius: 16px; }
    .lb-modal .form-label { font-weight: 600; color: #1e293b; font-size: 13px; }
    .lb-modal .is-invalid { border-color: #dc3545; }
    .lb-hint { color: #475569; font-size: 12px; }
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
              <h4 class="fw-bold mb-0 text-dark"><i class="ti ti-flask text-primary me-2"></i>Master Laboratorium</h4>
              <div class="text-dark small">Daftar pemeriksaan lab beserta tarif dan parameternya. Berlaku untuk semua faskes.</div>
            </div>
            <button class="btn btn-primary" id="btnTambah"><i class="ti ti-plus me-1"></i>Tambah Pemeriksaan</button>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-6 col-xl-3"><div class="lb-kpi"><div class="n" id="kTotal">-</div><div class="l">Total pemeriksaan</div></div></div>
            <div class="col-6 col-xl-3"><div class="lb-kpi"><div class="n" id="kAktif">-</div><div class="l">Aktif (tampil di permintaan)</div></div></div>
            <div class="col-6 col-xl-3"><div class="lb-kpi"><div class="n" id="kKosong">-</div><div class="l">Aktif tanpa parameter</div></div></div>
            <div class="col-6 col-xl-3"><div class="lb-kpi"><div class="n" id="kTarif0">-</div><div class="l">Aktif tanpa tarif</div></div></div>
          </div>

          <div class="card border-0 shadow-sm" style="border-radius:16px">
            <div class="card-body p-3 p-md-4">
              <div class="d-flex flex-wrap gap-2 mb-3" id="lbFilter">
                <button type="button" class="lb-chip on" data-f="">Semua</button>
                <button type="button" class="lb-chip" data-f="1">Aktif</button>
                <button type="button" class="lb-chip" data-f="0">Nonaktif</button>
                <button type="button" class="lb-chip" data-f="kosong">Aktif tanpa parameter</button>
              </div>
              <div class="table-responsive">
                <table class="table table-hover align-middle w-100" id="lbTable">
                  <thead>
                    <tr>
                      <th class="text-center" width="120">Aksi</th>
                      <th>Kode</th>
                      <th>Nama Pemeriksaan</th>
                      <th class="text-end">Tarif</th>
                      <th class="text-center">Parameter</th>
                      <th class="text-center" title="Jumlah kunjungan yang memakai pemeriksaan ini">Dipakai</th>
                      <th class="text-center">Status</th>
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

  <div class="modal fade lb-modal" id="programModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form id="programForm" class="modal-content" novalidate>
        <div class="modal-header">
          <h5 class="modal-title fw-bold text-dark" id="lbModalTitle">Tambah Pemeriksaan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="aksi" value="simpan">
          <input type="hidden" name="id_lab" id="id_lab">
          <div class="mb-3">
            <label class="form-label" for="assemen">Nama Pemeriksaan <span class="text-danger">*</span></label>
            <input type="text" id="assemen" name="assemen" class="form-control" maxlength="100" required>
            <div class="lb-hint mt-1" id="assemenHint">Nama harus unik.</div>
          </div>
          <div class="mb-1">
            <label class="form-label" for="tarif">Tarif (Rp)</label>
            <input type="text" inputmode="numeric" id="tarif" name="tarif" class="form-control" maxlength="9" placeholder="0">
            <div class="lb-hint mt-1">Angka bulat tanpa titik. Kode dibuat otomatis dan tidak bisa diubah.</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary" id="lbSimpan"><i class="ti ti-device-floppy me-1"></i>Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <?php require '../admin/library.php'; ?>
  <script>
    $(function() {
      const API = 'controller/master/labController.php';
      const esc = t => $('<div>').text(t ?? '').html();
      const fmt = n => Number(n || 0).toLocaleString('id-ID');
      const rp = n => n ? 'Rp ' + fmt(n) : '<span class="text-danger fw-semibold">Belum diatur</span>';
      let semua = [], filter = '';

      const toast = (icon, title) => Swal.fire({ toast: true, position: 'top-end', icon, title, showConfirmButton: false, timer: 2400 });

      function ringkas() {
        const aktif = semua.filter(r => r.status === 1);
        $('#kTotal').text(fmt(semua.length));
        $('#kAktif').text(fmt(aktif.length));
        $('#kKosong').text(fmt(aktif.filter(r => !r.n_param).length));
        $('#kTarif0').text(fmt(aktif.filter(r => !r.tarif).length));
      }

      const tabel = $('#lbTable').DataTable({
        processing: true,
        pageLength: 25,
        order: [[6, 'desc'], [2, 'asc']],
        language: { search: 'Cari:', lengthMenu: 'Tampil _MENU_', info: '_START_–_END_ dari _TOTAL_ pemeriksaan', infoEmpty: 'Tidak ada data', infoFiltered: '(dari _MAX_)', emptyTable: 'Belum ada pemeriksaan.', zeroRecords: 'Tidak ditemukan.', processing: 'Memuat...', paginate: { previous: 'Sebelumnya', next: 'Berikutnya' } },
        ajax: {
          url: API,
          type: 'GET',
          dataSrc: function(json) {
            if (!json || json.status !== 'success') {
              Swal.fire('Gagal', (json && json.message) || 'Gagal mengambil data.', 'error');
              return [];
            }
            semua = json.data || [];
            ringkas();
            return semua;
          },
          error: xhr => Swal.fire('Gagal', xhr.status === 401 || xhr.status === 403 ? 'Sesi berakhir atau akses ditolak.' : 'Gagal mengambil data.', 'error')
        },
        columns: [{
            data: null, orderable: false, searchable: false, className: 'text-center',
            render: (d, t, r) => `<div class="btn-group btn-group-sm">
              <a class="btn btn-primary" title="Kelola parameter" href="module/administrator/master-lab-detail?kode=${encodeURIComponent(r.kode)}"><i class="ti ti-list-details"></i></a>
              <button type="button" class="btn btn-warning edit-btn" data-id="${r.id_lab}" title="Edit"><i class="ti ti-edit"></i></button>
              <button type="button" class="btn btn-danger delete-btn" data-id="${r.id_lab}" data-nama="${esc(r.assemen)}" title="Hapus"><i class="ti ti-trash"></i></button></div>`
          },
          { data: 'kode', render: (v, t) => t === 'display' ? `<span class="lb-kode">${esc(v)}</span>` : v },
          { data: 'assemen', render: (v, t) => t === 'display' ? `<span class="lb-nama">${esc(v)}</span>` : v },
          { data: 'tarif', className: 'text-end', render: (v, t) => t === 'display' ? rp(v) : v },
          { data: 'n_param', className: 'text-center', render: (v, t, r) => t === 'display' ? `<span class="lb-n ${v ? '' : (r.status === 1 ? 'x' : 'u')}">${fmt(v)}</span>` : v },
          { data: 'dipakai', className: 'text-center', render: (v, t) => t === 'display' ? `<span class="lb-n u">${fmt(v)}</span>` : v },
          {
            data: 'status', className: 'text-center',
            render: (v, t, r) => t === 'display' ? `<div class="form-check form-switch d-inline-block m-0"><input class="form-check-input toggle-status" type="checkbox" role="switch" style="cursor:pointer" data-id="${r.id_lab}" ${v === 1 ? 'checked' : ''} aria-label="Aktif"></div>` : v
          }
        ]
      });

      $.fn.dataTable.ext.search.push((s, d, idx) => {
        if (s.nTable.id !== 'lbTable' || !filter) return true;
        const r = tabel.row(idx).data();
        if (!r) return true;
        if (filter === 'kosong') return r.status === 1 && !r.n_param;
        return String(r.status) === filter;
      });
      $('#lbFilter').on('click', '.lb-chip', function() {
        filter = String($(this).data('f'));
        $('#lbFilter .lb-chip').removeClass('on');
        $(this).addClass('on');
        tabel.draw();
      });

      function bersih() { $('#programForm .is-invalid').removeClass('is-invalid'); }

      $('#btnTambah').on('click', function() {
        $('#programForm')[0].reset();
        $('#id_lab').val('');
        bersih();
        $('#assemen').prop('readonly', false);
        $('#assemenHint').text('Nama harus unik.');
        $('#lbModalTitle').text('Tambah Pemeriksaan');
        $('#programModal').modal('show');
      });

      $(document).on('click', '.edit-btn', function() {
        $.getJSON(API, { id: $(this).data('id') }).done(function(res) {
          if (res.status !== 'success') return Swal.fire('Gagal', res.message || 'Data tidak ditemukan.', 'error');
          const d = res.data;
          $('#programForm')[0].reset();
          bersih();
          $('#id_lab').val(d.id_lab);
          $('#assemen').val(d.assemen).prop('readonly', d.dipakai > 0);
          $('#assemenHint').text(d.dipakai > 0 ? `Nama dikunci: sudah dipakai di ${fmt(d.dipakai)} kunjungan (hasil dicari berdasarkan nama). Tarif tetap bisa diubah.` : 'Nama harus unik.');
          $('#tarif').val(d.tarif || '');
          $('#lbModalTitle').text('Edit Pemeriksaan');
          $('#programModal').modal('show');
        }).fail(() => Swal.fire('Error', 'Gagal mengambil data.', 'error'));
      });

      $('#programForm').on('submit', function(e) {
        e.preventDefault();
        bersih();
        if (!$('#assemen').val().trim()) {
          $('#assemen').addClass('is-invalid').focus();
          return Swal.fire('Nama pemeriksaan wajib diisi', '', 'warning');
        }
        if ($('#tarif').val().trim() !== '' && !/^\d{1,9}$/.test($('#tarif').val().trim())) {
          $('#tarif').addClass('is-invalid').focus();
          return Swal.fire('Tarif tidak valid', 'Isi angka bulat saja, tanpa titik atau koma.', 'warning');
        }
        $('#lbSimpan').prop('disabled', true);
        $.post(API, $(this).serialize(), null, 'json').done(function(res) {
          $('#programModal').modal('hide');
          tabel.ajax.reload(null, false);
          toast('success', res.message);
        }).fail(function(xhr) {
          const r = xhr.responseJSON || {};
          if (r.data && r.data.field) $('#' + r.data.field).addClass('is-invalid').focus();
          Swal.fire('Gagal', r.message || 'Terjadi kesalahan server.', 'error');
        }).always(() => $('#lbSimpan').prop('disabled', false));
      });

      $(document).on('click', '.delete-btn', function() {
        const id = $(this).data('id'), nama = $(this).attr('data-nama');
        Swal.fire({
          title: 'Hapus pemeriksaan?',
          text: `"${nama}" beserta semua parameternya akan dihapus.`,
          icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus', cancelButtonText: 'Batal', confirmButtonColor: '#dc3545'
        }).then(function(r) {
          if (!r.isConfirmed) return;
          $.post(API, { aksi: 'hapus', id_lab: id }, null, 'json').done(function(res) {
            tabel.ajax.reload(null, false);
            toast('success', res.message);
          }).fail(xhr => Swal.fire('Tidak bisa dihapus', (xhr.responseJSON && xhr.responseJSON.message) || 'Gagal menghapus data.', 'error'));
        });
      });

      $(document).on('change', '.toggle-status', function() {
        const cb = this, aktif = cb.checked;
        $.post(API, { aksi: 'status', id_lab: $(cb).data('id'), status: aktif ? 1 : 0 }, null, 'json').done(function(res) {
          toast('success', res.message);
          tabel.ajax.reload(null, false);
        }).fail(function(xhr) {
          cb.checked = !aktif;
          Swal.fire('Gagal', (xhr.responseJSON && xhr.responseJSON.message) || 'Gagal mengubah status.', 'error');
        });
      });
    });
  </script>
</body>

</html>
