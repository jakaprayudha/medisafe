<?php
$title = 'Master Satu Sehat';
require '../../controller/view.php';

// Kredensial semua faskes: hanya superadmin/administrator.
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
    .ss-kpi { border: 1px solid #e2e8f0; border-radius: 14px; padding: 14px 16px; background: #fff; height: 100%; }
    .ss-kpi .n { font-size: 24px; font-weight: 800; color: #0f172a; line-height: 1.1; }
    .ss-kpi .l { font-size: 12px; font-weight: 600; color: #334155; margin-top: 2px; }
    .ss-chip { border: 1px solid #cbd5e1; background: #fff; color: #334155; border-radius: 999px; padding: 4px 12px; font-size: 12px; font-weight: 700; cursor: pointer; }
    .ss-chip:hover { background: #f1f5f9; }
    .ss-chip.on { background: #1e293b; border-color: #1e293b; color: #fff; }
    #ssTable th { font-size: 11px; font-weight: 700; color: #334155; white-space: nowrap; text-transform: uppercase; letter-spacing: .3px; }
    #ssTable td { vertical-align: middle; font-size: 13px; color: #0f172a; }
    .ss-nama { font-weight: 700; color: #0f172a; min-width: 190px; }
    .ss-mono { font-family: ui-monospace, Menlo, monospace; font-size: 12px; color: #0f172a; }
    .ss-b { display: inline-flex; align-items: center; gap: 4px; padding: 3px 9px; border-radius: 7px; font-size: 11px; font-weight: 700; }
    .ss-b.g { background: #dcfce7; color: #166534; }
    .ss-b.y { background: #fef3c7; color: #92400e; }
    .ss-b.r { background: #fee2e2; color: #991b1b; }
    .ss-b.x { background: #f1f5f9; color: #334155; }
    .ss-sub { font-size: 11px; color: #475569; }
    .ss-modal .modal-content { border: 0; border-radius: 16px; }
    .ss-modal .form-label { font-weight: 600; color: #1e293b; font-size: 13px; }
    .ss-modal .is-invalid { border-color: #dc3545; }
    .ss-hint { color: #475569; font-size: 12px; }
    .ss-sec { font-size: 11px; font-weight: 800; color: #334155; text-transform: uppercase; letter-spacing: .4px; margin: 4px 0 10px; }
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
              <h4 class="fw-bold mb-0 text-dark"><i class="ti ti-plug text-primary me-2"></i>Kredensial Satu Sehat</h4>
              <div class="text-dark small">Organization ID, Client ID/Secret, dan lokasi tiap faskes untuk integrasi Satu Sehat. Admin faskes hanya bisa melihat.</div>
            </div>
            <button type="button" class="btn btn-light border" id="ssRefresh" title="Muat ulang"><i class="ti ti-refresh"></i></button>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-6 col-xl-3"><div class="ss-kpi"><div class="n" id="kTotal">-</div><div class="l">Faskes aktif</div></div></div>
            <div class="col-6 col-xl-3"><div class="ss-kpi"><div class="n" id="kLengkap">-</div><div class="l">Kredensial lengkap</div></div></div>
            <div class="col-6 col-xl-3"><div class="ss-kpi"><div class="n" id="kBelum">-</div><div class="l">Belum diatur / tidak lengkap</div></div></div>
            <div class="col-6 col-xl-3"><div class="ss-kpi"><div class="n" id="kToken">-</div><div class="l">Token sedang aktif</div></div></div>
          </div>

          <div class="card border-0 shadow-sm" style="border-radius:16px">
            <div class="card-body p-3 p-md-4">
              <div class="d-flex flex-wrap gap-2 mb-3" id="ssFilter">
                <button type="button" class="ss-chip on" data-f="">Semua</button>
                <button type="button" class="ss-chip" data-f="belum">Perlu dilengkapi</button>
                <button type="button" class="ss-chip" data-f="lengkap">Lengkap</button>
                <button type="button" class="ss-chip" data-f="notoken">Tanpa token aktif</button>
              </div>
              <div class="table-responsive">
                <table class="table table-hover align-middle w-100" id="ssTable">
                  <thead>
                    <tr>
                      <th class="text-center" width="70">Aksi</th>
                      <th>Faskes</th>
                      <th>Organization ID</th>
                      <th>Client ID</th>
                      <th>Client Secret</th>
                      <th>Lokasi</th>
                      <th>Token</th>
                      <th>Status</th>
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

  <div class="modal fade ss-modal" id="programModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <form id="programForm" class="modal-content" novalidate autocomplete="off">
        <div class="modal-header">
          <div>
            <h5 class="modal-title fw-bold text-dark" id="ssModalTitle">Kredensial Satu Sehat</h5>
            <small class="text-dark" id="ssModalSub"></small>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="aksi" value="simpan">
          <input type="hidden" name="id_customer" id="id_customer">
          <div class="ss-sec">Kredensial</div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label" for="organization_id">Organization ID <span class="text-danger">*</span></label>
              <input type="text" id="organization_id" name="organization_id" class="form-control" maxlength="100" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label" for="client_id">Client ID <span class="text-danger">*</span></label>
              <input type="text" id="client_id" name="client_id" class="form-control" maxlength="200" required>
            </div>
            <div class="col-12 mb-3">
              <label class="form-label" for="client_secret">Client Secret <span class="text-danger" id="secWajib">*</span></label>
              <input type="password" id="client_secret" name="client_secret" class="form-control" maxlength="300" autocomplete="new-password">
              <div class="ss-hint mt-1" id="secHint"></div>
            </div>
          </div>
          <div class="ss-sec">Lokasi</div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label" for="latitude">Latitude</label>
              <input type="text" inputmode="decimal" id="latitude" name="latitude" class="form-control" placeholder="3.5935168">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label" for="longitude">Longitude</label>
              <input type="text" inputmode="decimal" id="longitude" name="longitude" class="form-control" placeholder="98.5753784">
            </div>
            <div class="col-12 mb-3">
              <label class="form-label" for="address">Alamat</label>
              <textarea id="address" name="address" class="form-control" rows="2" maxlength="1000"></textarea>
            </div>
            <div class="col-md-5 mb-3">
              <label class="form-label" for="city">Kota</label>
              <input type="text" id="city" name="city" class="form-control" maxlength="255">
            </div>
            <div class="col-md-3 mb-3">
              <label class="form-label" for="postal_code">Kode Pos</label>
              <input type="text" id="postal_code" name="postal_code" class="form-control" maxlength="20">
            </div>
            <div class="col-md-4 mb-1">
              <label class="form-label" for="location_id">Location ID</label>
              <input type="text" id="location_id" name="location_id" class="form-control" maxlength="255">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary" id="ssSimpan"><i class="ti ti-device-floppy me-1"></i>Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <?php require '../admin/library.php'; ?>
  <script>
    $(function() {
      const API = 'controller/master/satuSehatController.php';
      const esc = t => $('<div>').text(t ?? '').html();
      const fmt = n => Number(n || 0).toLocaleString('id-ID');
      const toast = (icon, title) => Swal.fire({ toast: true, position: 'top-end', icon, title, showConfirmButton: false, timer: 2600 });
      const BLN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      const tglJam = s => { const m = /^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2})/.exec(s || ''); return m ? `${+m[3]} ${BLN[+m[2] - 1]} ${m[4]}:${m[5]}` : '-'; };
      let semua = [], filter = '';

      function ringkas() {
        $('#kTotal').text(fmt(semua.length));
        $('#kLengkap').text(fmt(semua.filter(r => r.lengkap).length));
        $('#kBelum').text(fmt(semua.filter(r => !r.lengkap).length));
        $('#kToken').text(fmt(semua.filter(r => r.token_aktif).length));
      }

      const tabel = $('#ssTable').DataTable({
        processing: true,
        pageLength: 25,
        order: [[7, 'asc'], [1, 'asc']],
        language: { search: 'Cari faskes:', lengthMenu: 'Tampil _MENU_', info: '_START_–_END_ dari _TOTAL_ faskes', infoEmpty: 'Tidak ada data', infoFiltered: '(dari _MAX_)', emptyTable: 'Belum ada faskes.', zeroRecords: 'Tidak ditemukan.', processing: 'Memuat...', paginate: { previous: 'Sebelumnya', next: 'Berikutnya' } },
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
            render: (d, t, r) => `<button type="button" class="btn btn-sm btn-warning edit-btn" data-id="${r.id_customer}" title="Atur kredensial"><i class="ti ti-edit"></i></button>`
          },
          { data: 'clinic_name', render: (v, t, r) => t === 'display' ? `<div class="ss-nama">${esc(v)}</div>${r.diubah_oleh ? `<div class="ss-sub">diubah oleh ${esc(r.diubah_oleh)}</div>` : ''}` : v },
          { data: 'organization_id', render: (v, t) => t === 'display' ? (v ? `<span class="ss-mono">${esc(v)}</span>` : '<span class="text-dark">-</span>') : v },
          { data: 'client_id', render: (v, t) => t === 'display' ? (v ? `<span class="ss-mono" title="${esc(v)}">${esc(v.length > 16 ? v.slice(0, 8) + '…' + v.slice(-4) : v)}</span>` : '<span class="text-dark">-</span>') : v },
          { data: 'client_secret', render: (v, t) => t === 'display' ? (v ? `<span class="ss-mono">${esc(v)}</span>` : '<span class="text-dark">-</span>') : (v || '') },
          { data: null, render: (d, t, r) => { const s = [r.latitude && r.longitude ? 'koordinat ✓' : 'tanpa koordinat', r.location_id ? 'Location ID ✓' : 'tanpa Location ID']; return t === 'display' ? `<span class="ss-sub" style="white-space:nowrap">${s.map(esc).join('<br>')}</span>` : s.join(' '); } },
          { data: null, render: (d, t, r) => t === 'display' ? (r.token_aktif ? `<span class="ss-b g"><i class="ti ti-circle-check"></i>Aktif</span><div class="ss-sub">s.d. ${esc(tglJam(r.token_sampai))}</div>` : `<span class="ss-b x">Tidak aktif</span>${r.token_sampai ? `<div class="ss-sub">kedaluwarsa ${esc(tglJam(r.token_sampai))}</div>` : ''}`) : (r.token_aktif ? 1 : 0) },
          { data: null, render: (d, t, r) => t === 'display' ? (r.lengkap ? '<span class="ss-b g">Lengkap</span>' : (r.ada ? '<span class="ss-b y">Belum lengkap</span>' : '<span class="ss-b r">Belum diatur</span>')) : (r.lengkap ? 2 : (r.ada ? 1 : 0)) }
        ]
      });

      $.fn.dataTable.ext.search.push((s, d, idx) => {
        if (s.nTable.id !== 'ssTable' || !filter) return true;
        const r = tabel.row(idx).data();
        if (!r) return true;
        if (filter === 'lengkap') return r.lengkap;
        if (filter === 'belum') return !r.lengkap;
        if (filter === 'notoken') return r.lengkap && !r.token_aktif;
        return true;
      });
      $('#ssFilter').on('click', '.ss-chip', function() {
        filter = String($(this).data('f'));
        $('#ssFilter .ss-chip').removeClass('on');
        $(this).addClass('on');
        tabel.draw();
      });
      $('#ssRefresh').on('click', () => tabel.ajax.reload(null, false));

      function bersih() { $('#programForm .is-invalid').removeClass('is-invalid'); }

      $(document).on('click', '.edit-btn', function() {
        $.getJSON(API, { id_customer: $(this).data('id') }).done(function(res) {
          if (res.status !== 'success') return Swal.fire('Gagal', res.message || 'Data tidak ditemukan.', 'error');
          const d = res.data;
          $('#programForm')[0].reset();
          bersih();
          $('#id_customer').val(d.id_customer);
          $('#ssModalSub').text(d.clinic_name);
          $('#organization_id').val(d.organization_id);
          $('#client_id').val(d.client_id);
          $('#client_secret').val('');
          $('#secWajib').toggle(!d.client_secret);
          $('#secHint').text(d.client_secret ? `Tersimpan (${d.client_secret}). Kosongkan bila tidak diganti.` : 'Belum ada secret tersimpan.');
          $('#latitude').val(d.latitude);
          $('#longitude').val(d.longitude);
          $('#address').val(d.address);
          $('#city').val(d.city);
          $('#postal_code').val(d.postal_code);
          $('#location_id').val(d.location_id);
          $('#programModal').modal('show');
        }).fail(() => Swal.fire('Error', 'Gagal mengambil data.', 'error'));
      });

      $('#programForm').on('submit', function(e) {
        e.preventDefault();
        bersih();
        for (const id of ['organization_id', 'client_id']) {
          if (!$('#' + id).val().trim()) { $('#' + id).addClass('is-invalid').focus(); return Swal.fire('Isian wajib belum lengkap', id === 'client_id' ? 'Client ID wajib diisi.' : 'Organization ID wajib diisi.', 'warning'); }
        }
        $('#ssSimpan').prop('disabled', true);
        $.post(API, $(this).serialize(), null, 'json').done(function(res) {
          $('#programModal').modal('hide');
          tabel.ajax.reload(null, false);
          toast('success', res.message);
        }).fail(function(xhr) {
          const r = xhr.responseJSON || {};
          if (r.data && r.data.field) $('#' + r.data.field).addClass('is-invalid').focus();
          Swal.fire('Gagal', r.message || 'Terjadi kesalahan server.', 'error');
        }).always(() => $('#ssSimpan').prop('disabled', false));
      });
    });
  </script>
</body>

</html>
