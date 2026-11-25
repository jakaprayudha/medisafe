<?php
// Judul harus 'Master Data' agar menu sidebar ikut menyala (lihat menu-administrator.php).
$title = 'Master Data';
require '../../controller/view.php';

// Master data global (berlaku untuk semua faskes): hanya superadmin/administrator.
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
    .md-card { display: block; border: 1px solid #e2e8f0; border-radius: 14px; padding: 14px 16px; background: #fff; height: 100%; text-decoration: none; transition: box-shadow .15s, border-color .15s; }
    a.md-card:hover { border-color: #93c5fd; box-shadow: 0 6px 18px rgba(30, 64, 175, .08); }
    .md-card .ic { width: 38px; height: 38px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 8px; }
    .md-card .n { font-size: 22px; font-weight: 800; color: #0f172a; line-height: 1.1; }
    .md-card .l { font-size: 13px; font-weight: 700; color: #0f172a; margin-top: 2px; }
    .md-card .s { font-size: 11px; color: #475569; }
    .md-tabs { display: flex; gap: 6px; flex-wrap: wrap; border-bottom: 1px solid #e2e8f0; margin-bottom: 14px; }
    .md-tab { border: 0; background: none; padding: 9px 14px; font-weight: 700; font-size: 13px; color: #475569; border-bottom: 3px solid transparent; cursor: pointer; }
    .md-tab.on { color: #1d4ed8; border-bottom-color: #1d4ed8; }
    .md-tab .c { font-size: 11px; background: #e2e8f0; color: #334155; border-radius: 999px; padding: 1px 7px; margin-left: 4px; }
    .md-pane { display: none; }
    .md-pane.on { display: block; }
    table.md-t th { font-size: 11px; font-weight: 700; color: #334155; white-space: nowrap; text-transform: uppercase; letter-spacing: .3px; }
    table.md-t td { vertical-align: middle; font-size: 13px; color: #0f172a; }
    .md-kode { font-family: ui-monospace, Menlo, monospace; font-size: 12px; background: #f1f5f9; color: #0f172a; border-radius: 6px; padding: 2px 7px; white-space: nowrap; }
    .md-b { display: inline-block; padding: 3px 9px; border-radius: 7px; font-size: 11px; font-weight: 700; }
    .md-b.g { background: #dcfce7; color: #166534; }
    .md-b.b { background: #dbeafe; color: #1e40af; }
    .md-b.x { background: #f1f5f9; color: #334155; }
    .md-n { display: inline-block; min-width: 34px; text-align: center; padding: 3px 8px; border-radius: 7px; font-size: 12px; font-weight: 700; background: #f1f5f9; color: #334155; }
    .md-hint { color: #475569; font-size: 12px; }
    .md-modal .modal-content { border: 0; border-radius: 16px; }
    .md-modal .form-label { font-weight: 600; color: #1e293b; font-size: 13px; }
    .md-modal .is-invalid { border-color: #dc3545; }
  </style>
</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
    <?php require '../admin/sidebar.php'; ?>
    <div class="body-wrapper">
      <?php require '../admin/navbar-master.php'; ?>
      <div class="body-wrapper-inner">
        <div class="container-fluid">

          <div class="mb-3">
            <h4 class="fw-bold mb-0 text-dark"><i class="ti ti-database text-primary me-2"></i>Master Data</h4>
            <div class="text-dark small">Data referensi yang berlaku untuk semua faskes. Pilih kartu untuk membuka halaman pengelolaannya.</div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-6 col-lg-3"><a class="md-card" href="module/administrator/master-faskes"><span class="ic bg-primary-subtle text-primary"><i class="ti ti-building-hospital"></i></span><div class="n" id="cFaskes">-</div><div class="l">Faskes</div><div class="s">Kelola data faskes &amp; kontrak</div></a></div>
            <div class="col-6 col-lg-3"><a class="md-card" href="module/administrator/master-lab"><span class="ic bg-success-subtle text-success"><i class="ti ti-flask"></i></span><div class="n" id="cLab">-</div><div class="l">Pemeriksaan laboratorium</div><div class="s" id="cLabS">&nbsp;</div></a></div>
            <div class="col-6 col-lg-3"><a class="md-card" href="module/administrator/master-satu-sehat"><span class="ic bg-warning-subtle text-warning"><i class="ti ti-plug"></i></span><div class="n" id="cSS">-</div><div class="l">Kredensial Satu Sehat</div><div class="s">Organization &amp; client per faskes</div></a></div>
            <div class="col-6 col-lg-3"><a class="md-card" href="module/administrator/data-import"><span class="ic bg-info-subtle text-info"><i class="ti ti-download"></i></span><div class="n"><i class="ti ti-arrow-right"></i></div><div class="l">Data Import</div><div class="s">Impor data pasien &amp; kunjungan</div></a></div>
          </div>

          <div class="card border-0 shadow-sm" style="border-radius:16px">
            <div class="card-body p-3 p-md-4">
              <div class="md-tabs" id="mdTabs">
                <button type="button" class="md-tab on" data-p="poli"><i class="ti ti-stethoscope me-1"></i>Poli Master <span class="c" id="tPoli">-</span></button>
                <button type="button" class="md-tab" data-p="icd10"><i class="ti ti-report-medical me-1"></i>ICD-10 <span class="c" id="tI10">-</span></button>
                <button type="button" class="md-tab" data-p="icd9"><i class="ti ti-binary me-1"></i>ICD-9 CM <span class="c" id="tI9">-</span></button>
              </div>

              <!-- POLI -->
              <div class="md-pane on" id="pane-poli">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                  <div class="md-hint">Daftar poli standar (kode PCare) yang dipilih saat pendaftaran. Nama/kode poli yang sudah dipakai kunjungan dikunci.</div>
                  <button class="btn btn-primary btn-sm" id="btnPoli"><i class="ti ti-plus me-1"></i>Tambah Poli</button>
                </div>
                <div class="table-responsive">
                  <table class="table table-hover align-middle w-100 md-t" id="poliTable">
                    <thead>
                      <tr>
                        <th class="text-center" width="100">Aksi</th>
                        <th>Kode</th>
                        <th>Nama Poli</th>
                        <th>Jenis</th>
                        <th class="text-center" title="Jumlah kunjungan yang memakai poli ini">Dipakai</th>
                        <th class="text-center">Aktif</th>
                      </tr>
                    </thead>
                    <tbody></tbody>
                  </table>
                </div>
              </div>

              <!-- ICD-10 -->
              <div class="md-pane" id="pane-icd10">
                <div class="md-hint mb-2">Klasifikasi diagnosis (hanya baca). Cari kode (mis. <b>J06</b>) atau nama penyakit.</div>
                <div class="table-responsive">
                  <table class="table table-hover align-middle w-100 md-t" id="icd10Table">
                    <thead><tr><th width="140">Kode</th><th>Diagnosis</th></tr></thead>
                    <tbody></tbody>
                  </table>
                </div>
              </div>

              <!-- ICD-9 -->
              <div class="md-pane" id="pane-icd9">
                <div class="md-hint mb-2">Klasifikasi prosedur/tindakan (hanya baca). Cari kode atau nama tindakan.</div>
                <div class="table-responsive">
                  <table class="table table-hover align-middle w-100 md-t" id="icd9Table">
                    <thead><tr><th width="140">Kode</th><th>Prosedur</th></tr></thead>
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

  <div class="modal fade md-modal" id="poliModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form id="poliForm" class="modal-content" novalidate>
        <div class="modal-header">
          <h5 class="modal-title fw-bold text-dark" id="poliTitle">Tambah Poli</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="aksi" value="poli_simpan">
          <input type="hidden" name="id" id="poli_id">
          <div class="row">
            <div class="col-4 mb-3">
              <label class="form-label" for="kdPoli">Kode <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="kdPoli" name="kdPoli" maxlength="5" required>
            </div>
            <div class="col-8 mb-3">
              <label class="form-label" for="nmPoli">Nama Poli <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="nmPoli" name="nmPoli" maxlength="100" required>
            </div>
            <div class="col-12"><div class="md-hint mb-3" id="poliHint">Kode mengikuti referensi poli PCare/BPJS (maks. 5 karakter).</div></div>
            <div class="col-6 mb-1">
              <label class="form-label" for="poliSakit">Jenis</label>
              <select class="form-select" id="poliSakit" name="poliSakit">
                <option value="1">Poli sakit</option>
                <option value="0">Kegiatan / sehat</option>
              </select>
            </div>
            <div class="col-6 mb-1">
              <label class="form-label" for="status_poli">Status</label>
              <select class="form-select" id="status_poli" name="status_poli">
                <option value="1">Aktif</option>
                <option value="0">Nonaktif</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary" id="poliSimpan"><i class="ti ti-device-floppy me-1"></i>Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <?php require '../admin/library.php'; ?>
  <script>
    $(function() {
      const API = 'controller/master/masterDataController.php';
      const esc = t => $('<div>').text(t ?? '').html();
      const fmt = n => Number(n || 0).toLocaleString('id-ID');
      const toast = (icon, title) => Swal.fire({ toast: true, position: 'top-end', icon, title, showConfirmButton: false, timer: 2400 });
      const bhs = { search: 'Cari:', lengthMenu: 'Tampil _MENU_', info: '_START_–_END_ dari _TOTAL_', infoEmpty: 'Tidak ada data', infoFiltered: '(dari _MAX_)', emptyTable: 'Belum ada data.', zeroRecords: 'Tidak ditemukan.', processing: 'Memuat...', paginate: { previous: 'Sebelumnya', next: 'Berikutnya' } };

      /* ---------- ringkasan ---------- */
      $.getJSON(API, { aksi: 'ringkasan' }).done(function(res) {
        if (res.status !== 'success') return;
        const d = res.data;
        $('#cFaskes').text(fmt(d.faskes));
        $('#cLab').text(fmt(d.lab));
        $('#cLabS').text(fmt(d.lab_aktif) + ' aktif');
        $('#cSS').text(fmt(d.satusehat));
        $('#tPoli').text(fmt(d.poli));
        $('#tI10').text(fmt(d.icd10));
        $('#tI9').text(fmt(d.icd9));
      });

      /* ---------- tab ---------- */
      const dibuat = {};
      function buatIcd(id, jenis) {
        dibuat[id] = $('#' + id).DataTable({
          serverSide: true, processing: true, pageLength: 25, ordering: false, searchDelay: 350, language: bhs,
          ajax: {
            url: API, type: 'GET', data: d => ({ aksi: 'icd', jenis: jenis, draw: d.draw, start: d.start, length: d.length, q: d.search.value }),
            dataSrc: json => (json && json.status === 'success') ? json.data : [],
            error: xhr => Swal.fire('Gagal', xhr.status === 401 || xhr.status === 403 ? 'Sesi berakhir atau akses ditolak.' : 'Gagal mengambil data.', 'error')
          },
          columns: [
            { data: 'kode', render: v => `<span class="md-kode">${esc(v)}</span>` },
            { data: 'nama', render: v => esc(v) }
          ]
        });
      }
      $('#mdTabs').on('click', '.md-tab', function() {
        const p = $(this).data('p');
        $('#mdTabs .md-tab').removeClass('on');
        $(this).addClass('on');
        $('.md-pane').removeClass('on');
        $('#pane-' + p).addClass('on');
        if (p === 'icd10' && !dibuat.icd10Table) buatIcd('icd10Table', 10);
        if (p === 'icd9' && !dibuat.icd9Table) buatIcd('icd9Table', 9);
      });

      /* ---------- poli ---------- */
      const poli = $('#poliTable').DataTable({
        processing: true, pageLength: 25, order: [[1, 'asc']], language: bhs,
        ajax: {
          url: API, type: 'GET', data: { aksi: 'poli' },
          dataSrc: json => (json && json.status === 'success') ? json.data : [],
          error: xhr => Swal.fire('Gagal', xhr.status === 401 || xhr.status === 403 ? 'Sesi berakhir atau akses ditolak.' : 'Gagal mengambil data.', 'error')
        },
        columns: [{
            data: null, orderable: false, searchable: false, className: 'text-center',
            render: (d, t, r) => `<div class="btn-group btn-group-sm">
              <button type="button" class="btn btn-warning poli-edit" data-id="${r.id}" title="Edit"><i class="ti ti-edit"></i></button>
              <button type="button" class="btn btn-danger poli-hapus" data-id="${r.id}" data-nama="${esc(r.nmPoli)}" title="Hapus"><i class="ti ti-trash"></i></button></div>`
          },
          { data: 'kdPoli', render: (v, t) => t === 'display' ? `<span class="md-kode">${esc(v)}</span>` : v },
          { data: 'nmPoli', render: (v, t) => t === 'display' ? `<b>${esc(v)}</b>` : v },
          { data: 'poliSakit', render: (v, t) => t === 'display' ? (v ? '<span class="md-b b">Poli sakit</span>' : '<span class="md-b x">Kegiatan / sehat</span>') : v },
          { data: 'dipakai', className: 'text-center', render: (v, t) => t === 'display' ? `<span class="md-n">${fmt(v)}</span>` : v },
          { data: 'status_poli', className: 'text-center', render: (v, t, r) => t === 'display' ? `<div class="form-check form-switch d-inline-block m-0"><input class="form-check-input poli-toggle" type="checkbox" role="switch" style="cursor:pointer" data-id="${r.id}" ${v === 1 ? 'checked' : ''} aria-label="Aktif"></div>` : v }
        ]
      });
      const bersih = () => $('#poliForm .is-invalid').removeClass('is-invalid');

      $('#btnPoli').on('click', function() {
        $('#poliForm')[0].reset();
        $('#poli_id').val('');
        bersih();
        $('#poliTitle').text('Tambah Poli');
        $('#poliModal').modal('show');
      });
      $(document).on('click', '.poli-edit', function() {
        const r = poli.rows().data().toArray().find(x => x.id === Number($(this).data('id')));
        if (!r) return;
        $('#poliForm')[0].reset();
        bersih();
        $('#poli_id').val(r.id);
        $('#kdPoli').val(r.kdPoli);
        $('#nmPoli').val(r.nmPoli);
        $('#poliSakit').val(String(r.poliSakit));
        $('#status_poli').val(String(r.status_poli));
        const kunci = r.dipakai > 0;
        $('#kdPoli, #nmPoli').prop('readonly', kunci);
        $('#poliHint').text(kunci ? `Kode & nama dikunci: sudah dipakai di ${fmt(r.dipakai)} kunjungan. Jenis dan status tetap bisa diubah.` : 'Kode mengikuti referensi poli PCare/BPJS (maks. 5 karakter).');
        $('#poliTitle').text('Edit Poli');
        $('#poliModal').modal('show');
      });
      $('#btnPoli').on('click', () => { $('#kdPoli, #nmPoli').prop('readonly', false); $('#poliHint').text('Kode mengikuti referensi poli PCare/BPJS (maks. 5 karakter).'); });

      $('#poliForm').on('submit', function(e) {
        e.preventDefault();
        bersih();
        for (const [id, msg] of [['kdPoli', 'Kode poli wajib diisi'], ['nmPoli', 'Nama poli wajib diisi']]) {
          if (!$('#' + id).val().trim()) { $('#' + id).addClass('is-invalid').focus(); return Swal.fire(msg, '', 'warning'); }
        }
        $('#poliSimpan').prop('disabled', true);
        $.post(API, $(this).serialize(), null, 'json').done(function(res) {
          $('#poliModal').modal('hide');
          poli.ajax.reload(null, false);
          toast('success', res.message);
        }).fail(function(xhr) {
          const r = xhr.responseJSON || {};
          if (r.data && r.data.field) $('#' + r.data.field).addClass('is-invalid').focus();
          Swal.fire('Gagal', r.message || 'Terjadi kesalahan server.', 'error');
        }).always(() => $('#poliSimpan').prop('disabled', false));
      });

      $(document).on('click', '.poli-hapus', function() {
        const id = $(this).data('id'), nama = $(this).attr('data-nama');
        Swal.fire({ title: 'Hapus poli?', text: `"${nama}" akan dihapus dari master.`, icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus', cancelButtonText: 'Batal', confirmButtonColor: '#dc3545' }).then(function(r) {
          if (!r.isConfirmed) return;
          $.post(API, { aksi: 'poli_hapus', id: id }, null, 'json').done(function(res) {
            poli.ajax.reload(null, false);
            toast('success', res.message);
          }).fail(xhr => Swal.fire('Tidak bisa dihapus', (xhr.responseJSON && xhr.responseJSON.message) || 'Gagal menghapus data.', 'error'));
        });
      });
      $(document).on('change', '.poli-toggle', function() {
        const cb = this, aktif = cb.checked;
        $.post(API, { aksi: 'poli_status', id: $(cb).data('id'), status: aktif ? 1 : 0 }, null, 'json').done(function(res) {
          toast('success', res.message);
          poli.ajax.reload(null, false);
        }).fail(function(xhr) {
          cb.checked = !aktif;
          Swal.fire('Gagal', (xhr.responseJSON && xhr.responseJSON.message) || 'Gagal mengubah status.', 'error');
        });
      });
    });
  </script>
</body>

</html>
