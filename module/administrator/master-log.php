<?php
$title = 'Master Log';
require '../../controller/view.php';

// Halaman ini hanya untuk superadmin (data log berlaku global untuk semua faskes).
if (!isset($_SESSION['uid_user']) || ($_SESSION['roles'] ?? '') !== 'superadmin') {
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
    .lg-stat { border: 1px solid #e2e8f0; border-radius: 14px; padding: 14px 16px; background: #fff; height: 100%; }
    .lg-stat .n { font-size: 24px; font-weight: 800; color: #0f172a; line-height: 1.1; }
    .lg-stat .l { font-size: 12px; font-weight: 600; color: #475569; margin-top: 2px; }
    .lg-chip { border: 1px solid #cbd5e1; background: #fff; color: #334155; border-radius: 999px; padding: 4px 12px; font-size: 12px; font-weight: 700; cursor: pointer; }
    .lg-chip:hover { background: #f1f5f9; }
    .lg-chip.on { background: #1e293b; border-color: #1e293b; color: #fff; }
    .lg-chip .c { opacity: .7; margin-left: 4px; }
    .type-badge { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 7px; font-size: 10px; font-weight: 800; text-transform: uppercase; background: #e0e7ff; color: #3730a3; }
    .type-feature { background: #dcfce7; color: #166534; }
    .type-improvement { background: #fef3c7; color: #92400e; }
    .type-bug, .type-fix { background: #fee2e2; color: #991b1b; }
    .type-security { background: #ede9fe; color: #5b21b6; }
    .type-maintenance { background: #e0f2fe; color: #075985; }
    .guide-badge { display: inline-flex; align-items: center; gap: 4px; padding: 4px 9px; border-radius: 7px; background: #f1f5f9; color: #1e293b; font-size: 11px; font-weight: 700; text-decoration: none; }
    .guide-badge:hover { background: #e2e8f0; color: #0f172a; }
    .lg-judul { font-weight: 700; color: #0f172a; }
    .lg-desc { color: #334155; font-size: 13px; max-width: 520px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; white-space: pre-line; }
    .lg-tgl { color: #334155; font-size: 13px; white-space: nowrap; }
    .lg-ver { font-family: ui-monospace, Menlo, monospace; font-size: 12px; background: #f1f5f9; color: #0f172a; border-radius: 6px; padding: 2px 7px; }
    #updateTable td { vertical-align: middle; }
    .update-modal .modal-content { border: 0; border-radius: 16px; }
    .update-modal .form-label { font-weight: 600; color: #1e293b; font-size: 13px; }
    .update-modal .is-invalid { border-color: #dc3545; }
    .lg-hint { color: #475569; font-size: 12px; }
    .lg-guide-box { border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px; background: #f8fafc; }
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
              <h4 class="fw-bold mb-0 text-dark"><i class="ti ti-history text-primary me-2"></i>Log Update</h4>
              <div class="text-dark small">Riwayat pembaruan sistem Medisafe. Muncul di notifikasi semua pengguna selama 1 bulan.</div>
            </div>
            <button type="button" class="btn btn-primary" id="btnTambah"><i class="ti ti-plus me-1"></i>Tambah Update</button>
          </div>

          <div class="row g-3 mb-3" id="lgStat">
            <div class="col-6 col-md-3"><div class="lg-stat"><div class="n" id="stTotal">-</div><div class="l">Total update</div></div></div>
            <div class="col-6 col-md-3"><div class="lg-stat"><div class="n" id="stBulan">-</div><div class="l">30 hari terakhir</div></div></div>
            <div class="col-6 col-md-3"><div class="lg-stat"><div class="n" id="stPanduan">-</div><div class="l">Punya panduan</div></div></div>
            <div class="col-6 col-md-3"><div class="lg-stat"><div class="n" id="stTerakhir" style="font-size:17px">-</div><div class="l">Update terakhir</div></div></div>
          </div>

          <div class="card border-0 shadow-sm" style="border-radius:16px">
            <div class="card-body p-3 p-md-4">
              <div class="d-flex flex-wrap gap-2 mb-3" id="lgChips"></div>
              <div class="table-responsive">
                <table class="table align-middle" id="updateTable" width="100%">
                  <thead>
                    <tr>
                      <th class="text-center" width="90">Aksi</th>
                      <th>Update</th>
                      <th>Tipe</th>
                      <th>Versi</th>
                      <th>Panduan</th>
                      <th>Tanggal</th>
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

  <div class="modal fade update-modal" id="updateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <form id="updateForm" class="modal-content" enctype="multipart/form-data" novalidate>
        <div class="modal-header">
          <div>
            <h5 class="modal-title fw-bold text-dark" id="updateModalTitle">Tambah Log Update</h5>
            <small class="text-dark">Tuliskan perubahan yang bisa dipahami pengguna klinik.</small>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="id">
          <input type="hidden" name="aksi" value="simpan">

          <div class="mb-3">
            <label class="form-label" for="title">Judul Update <span class="text-danger">*</span></label>
            <input type="text" name="title" id="title" class="form-control" maxlength="255" placeholder="Contoh: Pembaruan Modul Farmasi" required>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label" for="type">Tipe Update</label>
              <select name="type" id="type" class="form-select">
                <option value="update">Update</option>
                <option value="feature">Feature</option>
                <option value="improvement">Improvement</option>
                <option value="bug">Bug</option>
                <option value="fix">Fix</option>
                <option value="security">Security</option>
                <option value="maintenance">Maintenance</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label" for="version">Versi</label>
              <input type="text" name="version" id="version" class="form-control" maxlength="50" placeholder="Contoh: 1.2.0">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="description">Deskripsi</label>
            <textarea name="description" id="description" class="form-control" rows="5" placeholder="Jelaskan perubahan atau fitur yang ditambahkan..."></textarea>
          </div>

          <div class="lg-guide-box">
            <div class="fw-semibold mb-3 text-dark"><i class="ti ti-book-2 text-primary me-1"></i>Panduan Update</div>
            <div class="mb-3">
              <label class="form-label" for="guide_type">Jenis Panduan</label>
              <select name="guide_type" id="guide_type" class="form-select">
                <option value="none">Tidak Ada</option>
                <option value="url">Link URL</option>
                <option value="video">Video</option>
                <option value="pdf">PDF</option>
              </select>
            </div>
            <div id="guideUrlWrapper" style="display:none">
              <label class="form-label" for="guide_url">URL Panduan</label>
              <input type="url" name="guide_url" id="guide_url" class="form-control" maxlength="1000" placeholder="https://...">
              <div class="lg-hint mt-1">Dokumentasi, website, YouTube, Vimeo, dan sebagainya (http/https).</div>
            </div>
            <div id="guidePdfWrapper" style="display:none">
              <label class="form-label" for="guide_file">File PDF</label>
              <input type="file" name="guide_file" id="guide_file" class="form-control" accept="application/pdf,.pdf">
              <div class="lg-hint mt-1" id="pdfHint">Maksimal 20 MB. Format PDF.</div>
              <div id="currentPdf" class="mt-2" style="display:none"></div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary" id="btnSimpan"><i class="ti ti-device-floppy me-1"></i>Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <?php require '../admin/library.php'; ?>
  <script>
    $(function() {
      const apiUrl = 'controller/system/logUpdateController.php';
      const TIPE = ['update', 'feature', 'improvement', 'bug', 'fix', 'security', 'maintenance'];
      const esc = t => $('<div>').text(t ?? '').html();
      const amanUrl = u => /^(https?:\/\/|uploads\/)/i.test(u || '') ? u : '#';
      let semua = [];
      let filterTipe = '';

      function tglId(s) {
        if (!s) return '-';
        const d = new Date(String(s).replace(' ', 'T'));
        if (isNaN(d)) return s;
        return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) + ' ' +
          d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }).replace('.', ':');
      }

      function guideHtml(r) {
        if (r.guide_type === 'pdf' && r.guide_file)
          return `<a href="${esc(amanUrl(r.guide_file))}" target="_blank" rel="noopener" class="guide-badge"><i class="ti ti-file-text"></i>PDF</a>`;
        if ((r.guide_type === 'url' || r.guide_type === 'video') && r.guide_url)
          return `<a href="${esc(amanUrl(r.guide_url))}" target="_blank" rel="noopener noreferrer" class="guide-badge"><i class="ti ${r.guide_type === 'video' ? 'ti-player-play' : 'ti-link'}"></i>${r.guide_type === 'video' ? 'Video' : 'URL'}</a>`;
        return '<span class="text-dark">-</span>';
      }

      function ringkas() {
        const batas = Date.now() - 30 * 864e5;
        $('#stTotal').text(semua.length);
        $('#stBulan').text(semua.filter(r => new Date(String(r.created_at).replace(' ', 'T')).getTime() >= batas).length);
        $('#stPanduan').text(semua.filter(r => r.guide_type !== 'none').length);
        $('#stTerakhir').text(semua.length ? tglId(semua[0].created_at).split(' ').slice(0, 3).join(' ') : '-');
        const hitung = {};
        semua.forEach(r => { hitung[r.type] = (hitung[r.type] || 0) + 1; });
        let h = `<button type="button" class="lg-chip ${filterTipe === '' ? 'on' : ''}" data-tipe="">Semua<span class="c">${semua.length}</span></button>`;
        TIPE.filter(t => hitung[t]).forEach(t => {
          h += `<button type="button" class="lg-chip ${filterTipe === t ? 'on' : ''}" data-tipe="${t}">${t}<span class="c">${hitung[t]}</span></button>`;
        });
        $('#lgChips').html(h);
      }

      $.fn.dataTable.ext.search.push((s, d, i) => {
        if (s.nTable.id !== 'updateTable' || !filterTipe) return true;
        const r = table.row(i).data();
        return r && r.type === filterTipe;
      });

      const table = $('#updateTable').DataTable({
        processing: true,
        order: [[5, 'desc']],
        pageLength: 25,
        ajax: {
          url: apiUrl,
          type: 'GET',
          dataSrc: function(json) {
            if (!json || json.status !== 'success') {
              Swal.fire('Gagal', json?.message || 'Gagal mengambil data.', 'error');
              return [];
            }
            semua = json.data || [];
            ringkas();
            return semua;
          },
          error: function(xhr) {
            Swal.fire('Gagal', xhr.status === 403 || xhr.status === 401 ? 'Sesi berakhir atau akses ditolak.' : 'Gagal mengambil data.', 'error');
          }
        },
        columns: [{
            data: null,
            orderable: false,
            searchable: false,
            className: 'text-center',
            render: (d, t, r) => `<div class="btn-group btn-group-sm">
              <button type="button" class="btn btn-primary edit-btn" data-id="${r.id_update}" title="Edit"><i class="ti ti-edit"></i></button>
              <button type="button" class="btn btn-danger delete-btn" data-id="${r.id_update}" data-judul="${esc(r.title)}" title="Hapus"><i class="ti ti-trash"></i></button></div>`
          },
          {
            data: 'title',
            render: (d, t, r) => t !== 'display' ? (d + ' ' + (r.description || '')) :
              `<div class="lg-judul">${esc(d || 'Update Sistem')}</div><div class="lg-desc mt-1">${esc(r.description || '')}</div>`
          },
          { data: 'type', render: d => `<span class="type-badge type-${esc((d || 'update').toLowerCase())}">${esc(d || 'update')}</span>` },
          { data: 'version', render: d => d ? `<span class="lg-ver">${esc(d)}</span>` : '-' },
          { data: 'guide_type', render: (d, t, r) => t === 'display' ? guideHtml(r) : d },
          { data: 'created_at', render: (d, t) => t === 'display' ? `<span class="lg-tgl">${esc(tglId(d))}</span>` : (d || '') }
        ],
        language: {
          search: 'Cari:',
          lengthMenu: '_MENU_ data',
          info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
          infoEmpty: 'Tidak ada data',
          emptyTable: 'Belum ada log update.',
          zeroRecords: 'Data tidak ditemukan.',
          processing: 'Memuat...',
          paginate: { previous: 'Sebelumnya', next: 'Berikutnya' }
        }
      });

      $('#lgChips').on('click', '.lg-chip', function() {
        filterTipe = String($(this).data('tipe') || '');
        ringkas();
        table.draw();
      });

      function bersihkanGalat() { $('#updateForm .is-invalid').removeClass('is-invalid'); }

      function guideFields() {
        const t = $('#guide_type').val();
        $('#guideUrlWrapper').toggle(t === 'url' || t === 'video');
        $('#guidePdfWrapper').toggle(t === 'pdf');
      }
      $('#guide_type').on('change', guideFields);

      $('#btnTambah').on('click', function() {
        $('#updateForm')[0].reset();
        $('#id').val('');
        bersihkanGalat();
        $('#currentPdf').hide().html('');
        $('#pdfHint').text('Maksimal 20 MB. Format PDF.');
        $('#updateModalTitle').text('Tambah Log Update');
        guideFields();
        $('#updateModal').modal('show');
      });

      $(document).on('click', '.edit-btn', function() {
        const id = $(this).data('id');
        $.getJSON(apiUrl, { id: id }).done(function(res) {
          if (!res || res.status !== 'success') return Swal.fire('Gagal', res?.message || 'Data tidak ditemukan.', 'error');
          const d = res.data;
          $('#updateForm')[0].reset();
          bersihkanGalat();
          $('#id').val(d.id_update);
          $('#title').val(d.title);
          $('#description').val(d.description);
          $('#type').val(d.type || 'update');
          $('#version').val(d.version || '');
          $('#guide_type').val(d.guide_type || 'none');
          $('#guide_url').val(d.guide_url || '');
          if (d.guide_file) {
            $('#currentPdf').html(`<div class="alert alert-light border mb-0 py-2 text-dark"><i class="ti ti-file-text text-danger me-1"></i>PDF saat ini: <a href="${esc(amanUrl(d.guide_file))}" target="_blank" rel="noopener" class="fw-semibold">Lihat PDF</a></div>`).show();
            $('#pdfHint').text('Biarkan kosong bila PDF tidak diganti. Maksimal 20 MB.');
          } else {
            $('#currentPdf').hide().html('');
            $('#pdfHint').text('Maksimal 20 MB. Format PDF.');
          }
          guideFields();
          $('#updateModalTitle').text('Edit Log Update');
          $('#updateModal').modal('show');
        }).fail(() => Swal.fire('Error', 'Gagal mengambil data.', 'error'));
      });

      const tombolSimpan = '<i class="ti ti-device-floppy me-1"></i>Simpan';

      $('#updateForm').on('submit', function(e) {
        e.preventDefault();
        bersihkanGalat();
        if (!$('#title').val().trim()) {
          $('#title').addClass('is-invalid').focus();
          return Swal.fire('Judul wajib diisi', '', 'warning');
        }
        const t = $('#guide_type').val();
        if ((t === 'url' || t === 'video') && !/^https?:\/\//i.test($('#guide_url').val().trim())) {
          $('#guide_url').addClass('is-invalid').focus();
          return Swal.fire('URL panduan tidak valid', 'Gunakan alamat yang diawali http:// atau https://', 'warning');
        }
        $('#btnSimpan').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');
        $.ajax({
          url: apiUrl,
          type: 'POST',
          data: new FormData(this),
          processData: false,
          contentType: false,
          dataType: 'json'
        }).done(function(res) {
          if (res.status === 'success') {
            $('#updateModal').modal('hide');
            table.ajax.reload(null, false);
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: res.message, showConfirmButton: false, timer: 2200 });
          } else {
            Swal.fire('Gagal', res.message, 'error');
          }
        }).fail(function(xhr) {
          let m = 'Terjadi kesalahan server.';
          try {
            const r = JSON.parse(xhr.responseText);
            m = r.message || m;
            if (r.data && r.data.field) $('#' + r.data.field).addClass('is-invalid').focus();
          } catch (_) {}
          Swal.fire('Gagal', m, 'error');
        }).always(() => $('#btnSimpan').prop('disabled', false).html(tombolSimpan));
      });

      $(document).on('click', '.delete-btn', function() {
        const id = $(this).data('id');
        const judul = $(this).attr('data-judul') || '';
        Swal.fire({
          title: 'Hapus log update?',
          text: `"${judul}" dan file PDF panduannya (jika ada) akan dihapus.`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Ya, Hapus',
          cancelButtonText: 'Batal',
          confirmButtonColor: '#dc3545'
        }).then(function(r) {
          if (!r.isConfirmed) return;
          $.post(apiUrl, { aksi: 'hapus', id: id }, null, 'json').done(function(res) {
            if (res.status === 'success') {
              table.ajax.reload(null, false);
              Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: res.message, showConfirmButton: false, timer: 2200 });
            } else {
              Swal.fire('Gagal', res.message, 'error');
            }
          }).fail(xhr => Swal.fire('Gagal', (xhr.responseJSON && xhr.responseJSON.message) || 'Gagal menghapus data.', 'error'));
        });
      });
    });
  </script>
</body>

</html>
