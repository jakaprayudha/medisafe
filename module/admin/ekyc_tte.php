<?php
// Dipakai dua peran: admin klinik (module/admin/ekyc_tte, hanya faskes sendiri) dan superadmin
// (module/administrator/ekyc_verify -> mendefinisikan EKYC_GLOBAL, melihat semua faskes + kolom/filter Faskes).
$ekGlobal = defined('EKYC_GLOBAL');
$title = $ekGlobal ? 'Verifikasi eKYC' : 'eKYC TTE';
require __DIR__ . '/../../controller/view.php';

if ($ekGlobal && (!isset($_SESSION['uid_user']) || !in_array($_SESSION['roles'] ?? '', ['superadmin', 'administrator'], true))) {
  header('Location: ../../');
  exit;
}

// 🔐 Simpan / upload / verifikasi / hapus hanya untuk module admin
$canManageEkyc = in_array($_SESSION['roles'] ?? '', ['admin', 'superadmin', 'administrator'], true);
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
    require __DIR__ . '/sidebar.php';
    ?>
    <!--  Sidebar End -->
    <!--  Main wrapper -->
    <div class="body-wrapper">
      <!--  Header Start -->
      <?php
      require __DIR__ . '/' . ($ekGlobal ? 'navbar-master.php' : 'navbar.php');
      ?>
      <!--  Header End -->
      <div class="body-wrapper-inner">
        <div class="container-fluid">

          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-3">
              <div class="d-flex flex-wrap align-items-center gap-3">
                <span class="ek-icon bg-primary-subtle text-primary"><i class="fas fa-id-card"></i></span>
                <div class="flex-grow-1" style="min-width:0">
                  <h5 class="fw-semibold mb-0"><?= $ekGlobal ? 'Verifikasi eKYC TTE' : 'eKYC Tanda Tangan Elektronik' ?></h5>
                  <div class="text-muted small"><?= $ekGlobal ? 'Antrean verifikasi pemohon TTE dari semua faskes: KTP diverifikasi, liveness wajah lewat kamera (dilakukan admin klinik), lalu verifikasi akhir.' : 'Verifikasi identitas pemohon TTE: KTP diverifikasi admin, lalu liveness wajah lewat kamera, lalu verifikasi akhir.' ?></div>
                </div>
                <button type="button" class="btn btn-light btn-sm" id="ekRefresh" title="Muat ulang"><i class="fas fa-sync-alt"></i></button>
                <?php if ($canManageEkyc && !$ekGlobal): ?>
                  <button class="btn btn-primary" id="btnTambah"><i class="fas fa-user-plus me-1"></i>Tambah pemohon</button>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-6 col-xl-3"><div class="ek-kpi"><div class="ek-label">Total pemohon</div><div class="ek-val" id="kTotal">-</div><div class="ek-sub" id="kAktif">&nbsp;</div></div></div>
            <div class="col-6 col-xl-3"><div class="ek-kpi"><div class="ek-label">Perlu tindakan admin</div><div class="ek-val text-warning" id="kTindak">-</div><div class="ek-sub">verifikasi KTP / verifikasi akhir</div></div></div>
            <div class="col-6 col-xl-3"><div class="ek-kpi"><div class="ek-label">Siap liveness</div><div class="ek-val text-info" id="kLiveness">-</div><div class="ek-sub">KTP terverifikasi, ambil wajah</div></div></div>
            <div class="col-6 col-xl-3"><div class="ek-kpi"><div class="ek-label">Terverifikasi</div><div class="ek-val text-success" id="kOk">-</div><div class="ek-sub" id="kTolak">&nbsp;</div></div></div>
          </div>

          <div class="card border-0 shadow-sm">
            <div class="card-body p-3 p-lg-4">
              <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                <div class="ek-tabs d-flex flex-wrap gap-1" id="ekTabs">
                  <button type="button" class="btn active" data-f="">Semua <span class="badge" data-n="semua">0</span></button>
                  <button type="button" class="btn" data-f="tindak">Perlu tindakan <span class="badge" data-n="tindak">0</span></button>
                  <button type="button" class="btn" data-f="proses">Dalam proses <span class="badge" data-n="proses">0</span></button>
                  <button type="button" class="btn" data-f="terverifikasi">Terverifikasi <span class="badge" data-n="terverifikasi">0</span></button>
                  <button type="button" class="btn" data-f="ditolak">Ditolak <span class="badge" data-n="ditolak">0</span></button>
                </div>
                <?php if ($ekGlobal): ?>
                  <select id="ekFaskes" class="form-select form-select-sm ms-auto" style="width:auto;max-width:240px"><option value="">Semua faskes</option></select>
                <?php endif; ?>
                <div class="position-relative <?= $ekGlobal ? '' : 'ms-auto' ?>" style="min-width:220px">
                  <i class="fas fa-search position-absolute text-muted" style="left:10px;top:50%;transform:translateY(-50%);font-size:.75rem"></i>
                  <input type="search" id="ekCari" class="form-control form-control-sm ps-4" placeholder="Cari nama, NIK, email, HP…">
                </div>
              </div>
              <div class="table-responsive">
                <table class="table align-middle mb-0 w-100" id="periodeTable">
                  <thead><tr>
                    <th class="text-dark fw-normal text-center">Aksi</th>
                    <?php if ($ekGlobal): ?><th class="text-dark fw-normal">Faskes</th><?php endif; ?>
                    <th class="text-dark fw-normal">Pemohon</th>
                    <th class="text-dark fw-normal">Kontak</th>
                    <th class="text-dark fw-normal">Tahap verifikasi</th>
                    <th class="text-dark fw-normal">Registrasi</th>
                    <th class="text-dark fw-normal text-center">Aktif</th>
                  </tr></thead>
                  <tbody></tbody>
                </table>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <?php
  require __DIR__ . '/library.php';
  ?>
</body>

<!-- MODAL TAMBAH EKYC -->
<div class="modal fade" id="ekycModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form id="ekycForm" class="modal-content" novalidate autocomplete="off">
      <div class="modal-header">
        <div><h5 class="modal-title mb-0"><i class="fas fa-user-plus text-primary me-2"></i>Tambah pemohon eKYC</h5><div class="small text-muted">Data harus sama persis dengan KTP pemohon.</div></div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label small fw-semibold" for="nama">Nama lengkap <span class="text-danger">*</span></label>
          <input type="text" name="nama" id="nama" class="form-control" placeholder="Sesuai KTP" maxlength="150" required>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-semibold" for="nik">NIK <span class="text-danger">*</span></label>
          <div class="input-group">
            <input type="text" name="nik" id="nik" class="form-control ek-mono" placeholder="16 digit" maxlength="16" inputmode="numeric" required>
            <span class="input-group-text small" id="nikHitung">0/16</span>
          </div>
        </div>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label small fw-semibold" for="email">Email</label>
            <input type="email" name="email" id="email" class="form-control" placeholder="email@contoh.com" maxlength="150">
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold" for="nomor_hp">Nomor HP</label>
            <input type="text" name="nomor_hp" id="nomor_hp" class="form-control" placeholder="08xxxxxxxxxx" maxlength="15" inputmode="numeric">
          </div>
        </div>
        <div class="small text-danger mt-3 d-none" id="ekError"></div>
      </div>
      <div class="modal-footer py-2">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary" id="ekSimpan"><i class="fas fa-save me-1"></i>Simpan</button>
      </div>
    </form>
  </div>
</div>

<style>
  .ek-icon { width: 40px; height: 40px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
  .ek-kpi { border: 1px solid #e5eaef; border-radius: 12px; background: #fff; padding: 12px 14px; height: 100%; }
  .ek-label { font-size: .72rem; color: #6c757d; }
  .ek-val { font-size: 1.35rem; font-weight: 700; color: #2a3547; line-height: 1.25; }
  .ek-sub { font-size: .7rem; color: #64748b; }
  .ek-tabs .btn { border-radius: 20px; font-size: .8rem; padding: .3rem .8rem; background: #fff; border: 1px solid #e5eaef; color: #5a6a85; }
  .ek-tabs .btn .badge { background: #eef1f4; color: #5a6a85; margin-left: 4px; }
  .ek-tabs .btn.active { background: #5d87ff; border-color: #5d87ff; color: #fff; }
  .ek-tabs .btn.active .badge { background: rgba(255, 255, 255, .25); color: #fff; }
  /* teks abu-abu dibuat lebih gelap agar terbaca */
  .ek-label, .ek-sub, #periodeTable .text-muted, #ekycDetailModal .text-muted, #ekycModal .text-muted { color: #475569 !important; }
  .ek-mono { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; letter-spacing: .03em; }
  .ek-nik { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: .74rem; background: #f1f5f9; border-radius: 4px; padding: 1px 5px; color: #1e293b; }
  #periodeTable td { vertical-align: middle; }
  #periodeTable > tbody > tr > td:first-child, #periodeTable > thead > tr > th:first-child { width: 1%; white-space: nowrap; }
  .ek-alur { display: flex; align-items: center; gap: 4px; }
  .ek-alur .t { width: 22px; height: 22px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: .66rem; border: 1px solid #e2e8f0; color: #94a3b8; background: #fff; }
  .ek-alur .t.ok { background: #13deb9; border-color: #13deb9; color: #fff; }
  .ek-alur .t.now { background: #fff7ed; border-color: #f59e0b; color: #b45309; }
  .ek-alur .t.no { background: #fee2e2; border-color: #fca5a5; color: #b91c1c; }
  .ek-alur .g { width: 14px; height: 2px; background: #e2e8f0; }
  .ek-alur .g.ok { background: #13deb9; }
  .ek-stat { font-size: .74rem; font-weight: 600; margin-top: 3px; }
  .badge.bg-warning, .badge.bg-warning-subtle { color: #1f2937 !important; }
  /* stepper modal detail */
  .ek-step { display: flex; gap: 6px; margin-bottom: 14px; }
  .ek-step > div { flex: 1; border: 1px solid #e5eaef; border-radius: 10px; padding: 8px 10px; font-size: .76rem; color: #64748b; background: #fff; }
  .ek-step > div b { display: block; font-size: .8rem; color: #334155; }
  .ek-step > div.ok { border-color: #a7f3d0; background: #f0fdf9; }
  .ek-step > div.ok b { color: #047857; }
  .ek-step > div.now { border-color: #fcd34d; background: #fffbeb; }
  .ek-step > div.now b { color: #92400e; }
  .ek-step > div.no { border-color: #fecaca; background: #fff5f5; }
  @media (max-width: 767.98px) { .ek-step { flex-direction: column; } }
  .lv-stage { position: relative; border-radius: 12px; overflow: hidden; background: #0f172a; aspect-ratio: 4 / 3; }
  .lv-stage video, .lv-stage #lvHasil { width: 100%; height: 100%; object-fit: cover; display: block; }
  .lv-stage video { transform: scaleX(-1); } /* tampilan cermin agar arah toleh natural */
  .lv-oval { position: absolute; left: 50%; top: 50%; width: 52%; height: 78%; transform: translate(-50%, -50%); border: 3px dashed rgba(255, 255, 255, .7); border-radius: 50%; pointer-events: none; transition: border-color .2s; }
  .lv-oval.ok { border-color: #13deb9; border-style: solid; }
  .lv-pesan { font-size: .9rem; font-weight: 600; color: #111827; text-align: center; min-height: 1.4em; }
  .lv-langkah { display: flex; flex-wrap: wrap; gap: 6px; justify-content: center; margin-top: 6px; }
  .lv-langkah span { font-size: .75rem; border: 1px solid #e5eaef; border-radius: 20px; padding: 3px 10px; color: #475569; background: #fff; }
  .lv-langkah span.aktif { border-color: #5d87ff; color: #3554d1; background: #ecf2ff; font-weight: 600; }
  .lv-langkah span.ok { border-color: #13deb9; color: #047857; background: #f0fdf9; }
</style>
<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
<script>
  const apiUrl = 'controller/master/ekycController';
  const canManageEkyc = <?= $canManageEkyc ? 'true' : 'false' ?>;
  const ekGlobal = <?= $ekGlobal ? 'true' : 'false' ?>;

  $(document).ready(function() {
    const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const BLN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const tgl = v => { const m = String(v || '').match(/^(\d{4})-(\d{2})-(\d{2})/); return m ? (+m[3]) + ' ' + BLN[m[2] - 1] + ' ' + m[1] : '-'; };
    // tahap: KTP -> verifikasi KTP -> liveness -> verifikasi akhir
    const TAHAP = {
      belum:             { n: 0, label: 'Belum upload KTP',      warna: '#64748b', grup: 'proses', aksi: 'Upload KTP' },
      ktp_menunggu:      { n: 1, label: 'KTP menunggu verifikasi', warna: '#b45309', grup: 'tindak', aksi: 'Verifikasi KTP' },
      ktp_terverifikasi: { n: 2, label: 'Siap liveness wajah',   warna: '#0369a1', grup: 'proses', aksi: 'Ambil liveness' },
      menunggu:          { n: 3, label: 'Menunggu verifikasi akhir', warna: '#b45309', grup: 'tindak', aksi: 'Verifikasi akhir' },
      terverifikasi:     { n: 4, label: 'Terverifikasi',         warna: '#047857', grup: 'terverifikasi', aksi: 'Lihat' },
      ditolak:           { n: -1, label: 'Ditolak',              warna: '#b91c1c', grup: 'ditolak', aksi: 'Lihat' }
    };
    const tahap = r => TAHAP[r.status_verifikasi] || TAHAP.belum;
    function alur(r) {
      const t = tahap(r), n = t.n;
      // titik: 1 KTP, 2 verifikasi KTP, 3 liveness, 4 verifikasi akhir
      const sel = i => n === -1 ? (i === 1 ? 'no' : '') : (n >= i ? 'ok' : (n === i - 1 ? 'now' : ''));
      const ik = ['fa-id-card', 'fa-user-check', 'fa-camera', 'fa-shield-halved'];
      let h = '<div class="ek-alur">';
      for (let i = 1; i <= 4; i++) h += (i > 1 ? `<span class="g ${n >= i ? 'ok' : ''}"></span>` : '') + `<span class="t ${sel(i)}" title="${['Upload KTP', 'Verifikasi KTP', 'Liveness wajah', 'Verifikasi akhir'][i - 1]}"><i class="fas ${ik[i - 1]}"></i></span>`;
      return h + `</div><div class="ek-stat" style="color:${t.warna}">${t.label}${r.status_verifikasi === 'terverifikasi' && r.verified_at ? ' · ' + tgl(r.verified_at) : ''}</div>`;
    }
    let filter = '';
    $.fn.dataTable.ext.search.push((st, _d, i) => {
      if (st.nTable.id !== 'periodeTable' || !filter) return true;
      return tahap(st.aoData[i]._aData).grup === filter;
    });
    let filterFaskes = '';
    $.fn.dataTable.ext.search.push((st, _d, i) => {
      if (st.nTable.id !== 'periodeTable' || !filterFaskes) return true;
      return String(st.aoData[i]._aData.id_customer) === filterFaskes;
    });
    function isiFaskes(rows) {
      if (!ekGlobal) return;
      const peta = new Map();
      rows.forEach(r => peta.set(String(r.id_customer), r.clinic_name || ('Faskes ' + r.id_customer)));
      const pilih = $('#ekFaskes'), lama = pilih.val();
      pilih.html('<option value="">Semua faskes</option>' + [...peta].sort((a, b) => a[1].localeCompare(b[1])).map(([k, v]) => `<option value="${esc(k)}">${esc(v)}</option>`).join(''));
      pilih.val(peta.has(lama) ? lama : '');
      filterFaskes = pilih.val();
    }
    function hitung(rows) {
      const g = { semua: rows.length, tindak: 0, proses: 0, terverifikasi: 0, ditolak: 0 };
      rows.forEach(r => g[tahap(r).grup]++);
      document.querySelectorAll('#ekTabs [data-n]').forEach(x => { x.textContent = g[x.dataset.n]; });
      $('#kTotal').text(rows.length);
      $('#kAktif').text(rows.filter(r => r.status == '1').length + ' aktif');
      $('#kTindak').text(g.tindak);
      $('#kLiveness').text(rows.filter(r => r.status_verifikasi === 'ktp_terverifikasi').length);
      $('#kOk').text(g.terverifikasi);
      $('#kTolak').text(g.ditolak ? g.ditolak + ' ditolak' : ' ');
    }

    var table = $('#periodeTable').DataTable({
      processing: true, serverSide: false, deferRender: true, autoWidth: false, order: [], pageLength: 25, lengthMenu: [10, 25, 50, 100],
      dom: 't<"d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2 small"lip>',
      language: { processing: '<div class="spinner-border spinner-border-sm text-primary"></div> Memuat…', emptyTable: 'Belum ada pemohon eKYC.', zeroRecords: 'Tidak ada data yang cocok.',
        info: '_START_–_END_ dari _TOTAL_ pemohon', infoEmpty: '0 pemohon', infoFiltered: '(disaring dari _MAX_)', lengthMenu: 'Tampil _MENU_', paginate: { previous: '‹', next: '›' } },
      ajax: {
        url: apiUrl, type: 'GET', cache: false,
        dataSrc: json => { const rows = (json && json.data) || []; hitung(rows); isiFaskes(rows); return rows; },
        error: x => Swal.fire('Gagal memuat data', x.status === 401 ? 'Sesi berakhir, silakan login ulang.' : 'Tidak dapat terhubung ke server.', 'error')
      },
      rowId: r => 'ek' + r.id_ekyc,
      columns: [
        { data: null, orderable: false, searchable: false, className: 'text-start', render: (v, t, r) => {
            if (t !== 'display') return '';
            const tp = tahap(r), utama = canManageEkyc && tp.grup !== 'terverifikasi' && tp.grup !== 'ditolak';
            return `<div class="btn-group btn-group-sm"><button type="button" class="btn ${utama ? 'btn-primary' : 'btn-light'} detail-btn" data-id="${r.id_ekyc}"><i class="fas ${utama ? 'fa-arrow-right' : 'fa-id-card'} me-1"></i>${canManageEkyc ? tp.aksi : 'Lihat'}</button>` +
              (canManageEkyc ? `<button type="button" class="btn btn-light text-danger delete-btn" data-id="${r.id_ekyc}" title="Hapus"><i class="fas fa-trash"></i></button>` : '') + '</div>';
          } },
        ...(ekGlobal ? [{ data: 'clinic_name', render: (v, t) => t === 'display' ? `<span class="fw-semibold text-dark">${esc(v || '-')}</span>` : (v || '') }] : []),
        { data: 'nama', render: (v, t, r) => t !== 'display' ? (v || '') + ' ' + (r.nik || '') :
            `<div class="fw-semibold text-dark">${esc(v || '-')}</div><span class="ek-nik">${esc(r.nik || '-')}</span>` },
        { data: 'email', render: (v, t, r) => t !== 'display' ? (v || '') + ' ' + (r.nomor_hp || '') :
            `<div class="small">${v ? `<i class="far fa-envelope me-1 text-muted"></i>${esc(v)}` : '<span class="text-muted">-</span>'}</div><div class="small">${r.nomor_hp ? `<i class="fas fa-mobile-alt me-1 text-muted"></i>${esc(r.nomor_hp)}` : ''}</div>` },
        { data: 'status_verifikasi', render: (v, t, r) => t === 'display' ? alur(r) : tahap(r).n },
        { data: 'created_at', render: (v, t, r) => t === 'display' ? `<div class="small">${tgl(v)}</div><div class="small text-muted">${esc(r.created_by || '')}</div>` : (v || '') },
        { data: 'status', className: 'text-center', orderable: false, render: (v, t, r) => t !== 'display' ? v : (canManageEkyc
            ? `<div class="form-check form-switch d-inline-block m-0"><input class="form-check-input toggle-status" type="checkbox" role="switch" data-id="${r.id_ekyc}" ${v == '1' ? 'checked' : ''} title="Aktif / nonaktif"></div>`
            : `<span class="badge ${v == '1' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'}">${v == '1' ? 'Aktif' : 'Nonaktif'}</span>`) }
      ]
    });
    $('#ekTabs').on('click', '.btn', function() {
      filter = this.dataset.f;
      $('#ekTabs .btn').removeClass('active');
      $(this).addClass('active');
      table.draw();
    });
    let tCari;
    $('#ekCari').on('input', function() { clearTimeout(tCari); const v = this.value; tCari = setTimeout(() => table.search(v).draw(), 200); });
    $('#ekFaskes').on('change', function() { filterFaskes = this.value; table.draw(); });
    $('#ekRefresh').on('click', () => table.ajax.reload(null, false));
    $('#nik').on('input', function() { this.value = this.value.replace(/\D/g, '').slice(0, 16); $('#nikHitung').text(this.value.length + '/16').toggleClass('text-success', this.value.length === 16); });
    $('#nomor_hp').on('input', function() { this.value = this.value.replace(/\D/g, '').slice(0, 15); });

    // 🔹 Tambah
    $('#btnTambah').on('click', function() {
      $('#ekycForm')[0].reset();
      $('#ekycForm .is-invalid').removeClass('is-invalid');
      $('#ekError').addClass('d-none');
      $('#nikHitung').text('0/16').removeClass('text-success');
      $('#ekycModal').modal('show');
    });
    $('#ekycModal').on('shown.bs.modal', () => $('#nama').trigger('focus'));

    // 🔹 Submit
    $('#ekycForm').on('submit', function(e) {
      e.preventDefault();
      const err = [], tandai = (sel, m) => { $(sel).addClass('is-invalid'); err.push(m); };
      $('#ekycForm .is-invalid').removeClass('is-invalid');
      const nama = $('#nama').val().trim(), nik = $('#nik').val(), email = $('#email').val().trim(), hp = $('#nomor_hp').val();
      if (nama.length < 3) tandai('#nama', 'nama minimal 3 huruf');
      if (!/^\d{16}$/.test(nik)) tandai('#nik', 'NIK 16 digit');
      if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) tandai('#email', 'format email');
      if (hp && !/^\d{10,15}$/.test(hp)) tandai('#nomor_hp', 'nomor HP 10–15 digit');
      $('#ekError').text(err.length ? 'Periksa: ' + err.join(', ') + '.' : '').toggleClass('d-none', !err.length);
      if (err.length) return;
      const btn = $('#ekSimpan'), asli = btn.html();
      btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan…');
      fetch(apiUrl, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: new URLSearchParams(new FormData(this)) })
        .then(res => res.json())
        .then(data => {
          if (data.status === 'success') {
            $('#ekycModal').modal('hide');
            Swal.fire({ icon: 'success', title: 'Pemohon ditambahkan', text: 'Lanjutkan dengan upload KTP di detail pemohon.', timer: 1800, showConfirmButton: false });
            table.ajax.reload(null, false);
          } else {
            $('#ekError').text(data.message || 'Data gagal disimpan.').removeClass('d-none');
          }
        })
        .catch(() => $('#ekError').text('Tidak dapat terhubung ke server.').removeClass('d-none'))
        .finally(() => btn.prop('disabled', false).html(asli));
    });

    // 🔹 Toggle Status
    $(document).on('change', '.toggle-status', function() {
      let id = $(this).data('id');
      let status = $(this).is(':checked') ? 1 : 0;

      fetch(apiUrl, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: `id_ekyc=${id}&status=${status}`
        })
        .then(res => res.json())
        .then(res => {
          if (res.status === 'success') {
            Swal.fire({
              icon: 'success',
              title: 'Status diperbarui',
              timer: 1200,
              showConfirmButton: false
            });
            table.ajax.reload(null, false);
          } else {
            Swal.fire('Gagal!', res.message, 'error');
            table.ajax.reload(null, false);
          }
        });
    });

    // 🔹 Delete
    $(document).on('click', '.delete-btn', function() {
      let id = $(this).data('id');
      Swal.fire({
        title: 'Hapus Data eKYC?',
        text: 'Data yang dihapus tidak dapat dikembalikan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#d33'
      }).then((result) => {
        if (result.isConfirmed) {
          fetch(apiUrl + `?id=${id}`, {
              method: 'DELETE'
            })
            .then(res => res.json())
            .then(data => {
              if (data.status === 'success') {
                Swal.fire('Berhasil!', data.message, 'success');
                table.ajax.reload(null, false);
              } else {
                Swal.fire('Gagal!', data.message, 'error');
              }
            });
        }
      });
    });

    /* =====================================================
       DETAIL & VERIFIKASI
    ===================================================== */

    let currentEkycId = null;
    let registeredNik = '';
    let registeredNama = '';
    let currentStatusVerifikasi = 'belum';

    const statusVerifikasiMap = {
      'belum': {
        label: 'Belum Upload',
        cls: 'bg-secondary'
      },
      'ktp_menunggu': {
        label: 'KTP Menunggu Verifikasi',
        cls: 'bg-warning text-dark'
      },
      'ktp_terverifikasi': {
        label: 'KTP Terverifikasi',
        cls: 'bg-info'
      },
      'menunggu': {
        label: 'Menunggu Verifikasi',
        cls: 'bg-warning text-dark'
      },
      'terverifikasi': {
        label: 'Terverifikasi',
        cls: 'bg-success'
      },
      'ditolak': {
        label: 'Ditolak',
        cls: 'bg-danger'
      }
    };

    let currentKtpFile = '';
    let ocrCocok = null; // null = belum dibaca, true = NIK & nama cocok, false = ada yang tidak cocok
    const TAHAP_KTP = ['belum', 'ktp_menunggu', 'ditolak'];

    // tombol "lanjut rekam wajah": muncul selama tahap KTP bila KTP sudah tersimpan atau file baru dipilih
    function aturTombolLanjut() {
      if (!canManageEkyc) return;
      const adaFileBaru = !!($('#fileKtp')[0] && $('#fileKtp')[0].files[0]);
      const bisa = TAHAP_KTP.includes(currentStatusVerifikasi) && (adaFileBaru || (currentKtpFile && currentStatusVerifikasi === 'ktp_menunggu'));
      $('#btnLanjutLiveness').toggleClass('d-none', !bisa)
        .html('<i class="fas fa-camera me-1"></i> ' + (adaFileBaru ? 'Simpan KTP, verifikasi &amp; lanjut rekam wajah' : 'Verifikasi KTP &amp; lanjut rekam wajah'));
      // tombol kecil lama diganti tombol utama ini
      $('#btnVerifikasiKtp').addClass('d-none');
      const panel = $('#ocrLanjut');
      if (bisa && ocrCocok !== null) {
        panel.removeClass('d-none').html(ocrCocok
          ? '<button type="button" class="btn btn-success btn-sm w-100 btn-lanjut-liveness"><i class="fas fa-circle-check me-1"></i>KTP cocok — lanjut rekam wajah</button>'
          : '<div class="small text-warning-emphasis mb-1"><i class="fas fa-triangle-exclamation me-1"></i>Data KTP belum cocok. Periksa manual sebelum melanjutkan.</div><button type="button" class="btn btn-outline-primary btn-sm w-100 btn-lanjut-liveness">Sudah diperiksa — lanjut rekam wajah</button>');
      } else {
        panel.addClass('d-none').empty();
      }
    }

    function kirimJson(opsi) {
      return fetch(apiUrl, opsi).then(r => r.json()).then(res => {
        if (!res || res.status !== 'success') throw new Error((res && res.message) || 'Terjadi kesalahan server.');
        return res;
      });
    }

    // simpan KTP (bila file baru) -> status KTP terverifikasi -> buka ulang detail: kamera liveness langsung aktif
    async function lanjutRekamWajah(btn) {
      if (!currentEkycId) return;
      const file = $('#fileKtp')[0] && $('#fileKtp')[0].files[0];
      if (!file && !currentKtpFile) { Swal.fire('Perhatian', 'Upload foto KTP terlebih dahulu.', 'warning'); return; }
      if (file && file.size > 5 * 1024 * 1024) { Swal.fire('Perhatian', 'Ukuran file KTP maksimal 5 MB.', 'warning'); return; }
      if (ocrCocok !== true) {
        const ok = await Swal.fire({ icon: 'question', title: 'Verifikasi KTP?',
          html: (ocrCocok === false ? '<div class="text-warning-emphasis mb-2">NIK / nama hasil baca KTP belum cocok.</div>' : '') + 'Pastikan NIK &amp; nama pada KTP sama dengan data pemohon:<br><b>' + $('<div>').text(registeredNama).html() + '</b> · ' + $('<div>').text(registeredNik).html(),
          showCancelButton: true, confirmButtonText: 'Ya, KTP sesuai', cancelButtonText: 'Batal', reverseButtons: true });
        if (!ok.isConfirmed) return;
      }
      const tombol = $('.btn-lanjut-liveness').prop('disabled', true);
      const asli = btn ? btn.html() : '';
      if (btn) btn.html('<span class="spinner-border spinner-border-sm me-1"></span>Memproses…');
      try {
        if (file) {
          const fd = new FormData();
          fd.append('action', 'upload'); fd.append('type', 'ktp'); fd.append('id_ekyc', currentEkycId); fd.append('file', file);
          await kirimJson({ method: 'POST', body: fd });
        }
        await kirimJson({ method: 'PUT', headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'action=verify&id_ekyc=' + encodeURIComponent(currentEkycId) + '&status_verifikasi=ktp_terverifikasi' });
        table.ajax.reload(null, false);
        Swal.fire({ icon: 'success', title: 'KTP terverifikasi', text: 'Kamera dinyalakan — minta pemohon menghadap kamera.', timer: 1800, showConfirmButton: false });
        openDetail(currentEkycId); // status ktp_terverifikasi -> kamera liveness aktif otomatis
      } catch (e) {
        Swal.fire('Gagal', e.message, 'error');
        if (btn) btn.html(asli);
      } finally {
        tombol.prop('disabled', false);
      }
    }
    $(document).on('click', '.btn-lanjut-liveness', function() { lanjutRekamWajah($(this)); });

    function renderPreview(file, imgEl, emptyEl) {
      if (file) {
        // file tercatat tapi tidak ada di server -> tampilkan pesan, bukan ikon gambar rusak
        $(imgEl).off('error.ek').one('error.ek', function() {
          $(this).addClass('d-none');
          $(emptyEl).removeClass('d-none').html('<i class="fas fa-triangle-exclamation text-warning me-1"></i>File tidak ditemukan di server. Unggah ulang.');
        });
        $(emptyEl).addClass('d-none').text('Belum diupload');
        $(imgEl).attr('src', 'uploads/ekyc/' + encodeURIComponent(file)).removeClass('d-none');
      } else {
        $(imgEl).addClass('d-none').attr('src', '');
        $(emptyEl).removeClass('d-none');
      }
    }

    function openDetail(id) {
      currentEkycId = id;

      fetch(apiUrl + '?id=' + id)
        .then(res => res.json())
        .then(res => {
          if (res.status !== 'success' || !res.data) {
            Swal.fire('Gagal!', res.message || 'Data tidak ditemukan.', 'error');
            return;
          }

          const d = res.data;

          registeredNik = d.nik || '';
          registeredNama = d.nama || '';
          currentStatusVerifikasi = d.status_verifikasi || 'belum';

          $('#dNama').text(d.nama || '-');
          $('#dNik').text(d.nik || '-');
          if (ekGlobal) $('#dFaskes').text(d.clinic_name || '-');
          $('#dEmail').text(d.email || '-');
          $('#dHp').text(d.nomor_hp || '-');

          const sv = statusVerifikasiMap[currentStatusVerifikasi] || statusVerifikasiMap['belum'];
          $('#dStatusVerifikasi')
            .removeClass()
            .addClass('badge ' + sv.cls)
            .text(sv.label);

          // stepper tahapan
          const urut = ['belum', 'ktp_menunggu', 'ktp_terverifikasi', 'menunggu', 'terverifikasi'];
          const pos = urut.indexOf(currentStatusVerifikasi), tolak = currentStatusVerifikasi === 'ditolak';
          const LANGKAH = [
            ['1. Upload KTP', d.ktp_file ? 'Foto KTP tersimpan' : 'Unggah foto KTP pemohon'],
            ['2. Verifikasi KTP', 'Admin cocokkan NIK & nama'],
            ['3. Liveness wajah', 'Kamera: kedip & toleh'],
            ['4. Verifikasi akhir', 'KTP + wajah sesuai']
          ];
          $('#ekStep').html(LANGKAH.map((l, i) => {
            const cls = tolak ? (i === 0 ? 'no' : '') : (pos > i ? 'ok' : (pos === i ? 'now' : ''));
            const ikon = cls === 'ok' ? '<i class="fas fa-circle-check me-1"></i>' : (cls === 'now' ? '<i class="fas fa-circle-dot me-1"></i>' : (cls === 'no' ? '<i class="fas fa-circle-xmark me-1"></i>' : ''));
            return `<div class="${cls}"><b>${ikon}${l[0]}</b>${tolak && i === 0 ? 'Ditolak — unggah ulang KTP untuk memulai lagi' : l[1]}</div>`;
          }).join(''));
          $('#btnTolak').toggleClass('d-none', tolak || currentStatusVerifikasi === 'belum');

          currentKtpFile = d.ktp_file || '';
          ocrCocok = null;
          renderPreview(d.ktp_file, '#prevKtp', '#prevKtpEmpty');
          renderPreview(d.face_file, '#prevFace', '#prevFaceEmpty');

          // reset OCR
          $('#ocrPanel').addClass('d-none');
          $('#ocrResult').addClass('d-none');
          $('#ocrLoading').removeClass('d-none');

          // 🔒 gating liveness: hanya setelah KTP terverifikasi -> kamera aktif otomatis (wajib)
          const bukaLiveness = currentStatusVerifikasi === 'ktp_terverifikasi';
          $('#livenessLock').toggleClass('d-none', bukaLiveness);
          lvSiapkan(bukaLiveness && canManageEkyc);

          // tombol verifikasi KTP hanya saat ktp_menunggu
          $('#btnVerifikasiKtp').toggleClass('d-none', currentStatusVerifikasi !== 'ktp_menunggu');

          // tombol verifikasi akhir hanya saat menunggu
          $('#btnVerifikasi').toggleClass('d-none', currentStatusVerifikasi !== 'menunggu');

          $('#fileKtp').val('');

          aturTombolLanjut();
          $('#ekycDetailModal').modal('show');
        })
        .catch(() => {
          Swal.fire('Gagal!', 'Tidak dapat terhubung ke server.', 'error');
        });
    }

    $(document).on('click', '.detail-btn', function() {
      openDetail($(this).data('id'));
    });

    // 🔹 Upload file (KTP / liveness)
    function uploadEkyc(type, fileInput, btn) {
      if (!currentEkycId) return;

      const file = fileInput.files[0];

      if (!file) {
        Swal.fire('Perhatian', 'Pilih file terlebih dahulu.', 'warning');
        return;
      }

      if (file.size > 5 * 1024 * 1024) {
        Swal.fire('Perhatian', 'Ukuran file maksimal 5 MB.', 'warning');
        return;
      }

      const formData = new FormData();
      formData.append('action', 'upload');
      formData.append('type', type);
      formData.append('id_ekyc', currentEkycId);
      formData.append('file', file);

      btn.prop('disabled', true);

      fetch(apiUrl, {
          method: 'POST',
          body: formData
        })
        .then(res => res.json())
        .then(res => {
          if (res.status === 'success') {
            Swal.fire({
              icon: 'success',
              title: 'Berhasil!',
              text: res.message,
              timer: 1200,
              showConfirmButton: false
            });
            openDetail(currentEkycId);
            table.ajax.reload(null, false);
          } else {
            Swal.fire('Gagal!', res.message, 'error');
          }
        })
        .catch(() => {
          Swal.fire('Gagal!', 'Gagal mengupload file.', 'error');
        })
        .finally(() => {
          btn.prop('disabled', false);
        });
    }

    $('#btnUploadKtp').on('click', function() {
      uploadEkyc('ktp', document.getElementById('fileKtp'), $(this));
    });

    /* =====================================================
       LIVENESS KAMERA (wajib setelah KTP terverifikasi)
       Tantangan: wajah di tengah -> kedip -> toleh kiri -> toleh kanan, lalu foto wajah lurus diambil otomatis.
       Deteksi: face-api (tinyFaceDetector + landmark 68) dari folder models/, dimuat hanya saat dibutuhkan.
    ===================================================== */
    const LV = { stream: null, jalan: false, timer: null, blob: null, tahap: {}, kedipTutup: false, aktif: false, modelP: null };
    const lvEl = id => document.getElementById(id);

    function lvMuatModel() {
      if (!LV.modelP) {
        const muat = window.faceapi ? Promise.resolve() : new Promise((ok, gagal) => {
          const sc = document.createElement('script');
          sc.src = 'assets/js/face-api.min.js';
          sc.onload = ok;
          sc.onerror = () => gagal(new Error('face-api gagal dimuat'));
          document.head.appendChild(sc);
        });
        LV.modelP = muat.then(() => Promise.all([
          faceapi.nets.tinyFaceDetector.loadFromUri('models'),
          faceapi.nets.faceLandmark68Net.loadFromUri('models')
        ])).catch(e => { LV.modelP = null; throw e; });
      }
      return LV.modelP;
    }
    function lvPesan(t, warna) { const el = lvEl('lvPesan'); if (el) { el.textContent = t; el.style.color = warna || ''; } }
    function lvLangkah() {
      const urut = ['wajah', 'kedip', 'kiri', 'kanan'];
      const berikut = urut.find(k => !LV.tahap[k]);
      document.querySelectorAll('#lvLangkah span').forEach(x => {
        const k = x.dataset.k, ok = !!LV.tahap[k];
        x.classList.toggle('ok', ok);
        x.classList.toggle('aktif', !ok && k === berikut);
        x.querySelector('i').className = ok ? 'fas fa-circle-check' : 'far fa-circle';
      });
      return berikut;
    }
    function lvStop() {
      LV.jalan = false;
      clearTimeout(LV.timer);
      if (LV.stream) { LV.stream.getTracks().forEach(t => t.stop()); LV.stream = null; }
      const v = lvEl('lvVideo'); if (v) v.srcObject = null;
    }
    function lvReset() {
      lvStop();
      LV.blob = null; LV.tahap = {}; LV.kedipTutup = false;
      const h = lvEl('lvHasil'); if (h) { if (h.src && h.src.startsWith('blob:')) URL.revokeObjectURL(h.src); h.classList.add('d-none'); h.removeAttribute('src'); }
      if (lvEl('lvVideo')) lvEl('lvVideo').classList.remove('d-none');
      if (lvEl('lvOval')) lvEl('lvOval').classList.remove('d-none', 'ok');
      if (lvEl('btnUploadFace')) lvEl('btnUploadFace').disabled = true;
      lvLangkah();
    }
    // dipanggil openDetail: aktifkan kamera hanya bila KTP sudah terverifikasi
    function lvSiapkan(aktif) {
      LV.aktif = aktif;
      lvReset();
      if (!lvEl('lvKamera')) return;
      lvEl('lvKamera').classList.toggle('d-none', !aktif);
      lvEl('lvFoto').classList.toggle('d-none', aktif);
      if (!aktif) return;
      // kamera dinyalakan setelah modal tampil penuh
      const m = document.getElementById('ekycDetailModal');
      const gulir = () => { const k = lvEl('lvKamera'); if (k) k.scrollIntoView({ behavior: 'smooth', block: 'center' }); };
      if (m.classList.contains('show')) { lvMulai(); gulir(); } else m.addEventListener('shown.bs.modal', () => { if (LV.aktif) { lvMulai(); gulir(); } }, { once: true });
    }
    window.lvSiapkan = lvSiapkan;

    async function lvMulai() {
      lvReset();
      if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        lvPesan('Browser tidak mendukung kamera. Buka lewat HTTPS dengan Chrome / Edge terbaru.', '#dc2626');
        return;
      }
      lvPesan('Menyalakan kamera…');
      try {
        const [stream] = await Promise.all([
          navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } }, audio: false }),
          lvMuatModel()
        ]);
        if (!LV.aktif) { stream.getTracks().forEach(t => t.stop()); return; }
        LV.stream = stream;
        const v = lvEl('lvVideo');
        v.srcObject = stream;
        await v.play().catch(() => {});
        LV.jalan = true;
        lvPesan('Posisikan wajah di dalam oval');
        lvLoop();
      } catch (e) {
        lvStop();
        const n = e && e.name;
        lvPesan(n === 'NotAllowedError' ? 'Izin kamera ditolak. Izinkan kamera di browser lalu klik Ulangi.'
          : n === 'NotFoundError' ? 'Kamera tidak ditemukan di perangkat ini.'
          : n === 'NotReadableError' ? 'Kamera sedang dipakai aplikasi lain.'
          : 'Gagal menyiapkan kamera / model wajah. Klik Ulangi.', '#dc2626');
      }
    }

    // rasio buka mata (EAR) dari 6 titik landmark
    const lvJarak = (a, b) => Math.hypot(a.x - b.x, a.y - b.y);
    const lvEar = p => (lvJarak(p[1], p[5]) + lvJarak(p[2], p[4])) / (2 * lvJarak(p[0], p[3]));

    async function lvLoop() {
      if (!LV.jalan) return;
      const v = lvEl('lvVideo');
      try {
        const hasil = await faceapi.detectAllFaces(v, new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 })).withFaceLandmarks();
        if (!LV.jalan) return;
        const oval = lvEl('lvOval');
        if (hasil.length !== 1) {
          oval.classList.remove('ok');
          lvPesan(hasil.length ? 'Hanya satu wajah yang boleh terlihat' : 'Wajah tidak terdeteksi — posisikan di dalam oval', hasil.length ? '#b45309' : '');
        } else {
          const d = hasil[0], box = d.detection.box, lm = d.landmarks.positions;
          const vw = v.videoWidth || 640, vh = v.videoHeight || 480;
          const cx = (box.x + box.width / 2) / vw, cy = (box.y + box.height / 2) / vh, lebar = box.width / vw;
          // yaw: posisi hidung di antara tepi rahang (0.5 = lurus). Gambar kamera tidak dicermin:
          // pemohon menoleh ke KIRI-nya -> hidung bergeser ke kanan gambar -> rasio naik.
          const yaw = (lm[30].x - lm[0].x) / Math.max(1, lm[16].x - lm[0].x);
          const ear = (lvEar(lm.slice(36, 42)) + lvEar(lm.slice(42, 48))) / 2;
          const diTengah = Math.abs(cx - 0.5) < 0.18 && Math.abs(cy - 0.5) < 0.22;
          const cukupDekat = lebar > 0.22, lurus = yaw > 0.4 && yaw < 0.6;
          oval.classList.toggle('ok', diTengah && cukupDekat);
          const berikut = lvLangkah();
          if (!diTengah || !cukupDekat) {
            lvPesan(!cukupDekat ? 'Dekatkan wajah ke kamera' : 'Posisikan wajah di tengah oval', '#b45309');
          } else if (berikut === 'wajah') {
            if (lurus) LV.tahap.wajah = true; else lvPesan('Hadapkan wajah lurus ke kamera');
          } else if (berikut === 'kedip') {
            lvPesan('Kedipkan mata');
            if (ear < 0.2) LV.kedipTutup = true;
            else if (LV.kedipTutup && ear > 0.24) { LV.tahap.kedip = true; LV.kedipTutup = false; }
          } else if (berikut === 'kiri') {
            lvPesan('Tolehkan kepala perlahan ke KIRI');
            if (yaw > 0.66) LV.tahap.kiri = true;
          } else if (berikut === 'kanan') {
            lvPesan('Tolehkan kepala perlahan ke KANAN');
            if (yaw < 0.34) LV.tahap.kanan = true;
          } else {
            // semua tantangan lolos -> ambil foto saat wajah kembali lurus & mata terbuka
            lvPesan('Bagus! Hadapkan wajah lurus untuk mengambil foto');
            if (lurus && ear > 0.22) { lvAmbil(v); return; }
          }
          lvLangkah();
        }
      } catch (e) { /* frame gagal diproses: lanjut frame berikutnya */ }
      LV.timer = setTimeout(lvLoop, 120);
    }

    function lvAmbil(v) {
      const c = document.createElement('canvas');
      c.width = v.videoWidth || 640; c.height = v.videoHeight || 480;
      c.getContext('2d').drawImage(v, 0, 0, c.width, c.height); // tidak dicermin = sesuai wajah asli
      c.toBlob(b => {
        if (!b) { lvPesan('Gagal mengambil foto. Klik Ulangi.', '#dc2626'); return; }
        LV.blob = b;
        lvStop();
        const h = lvEl('lvHasil');
        h.src = URL.createObjectURL(b);
        h.classList.remove('d-none');
        lvEl('lvVideo').classList.add('d-none');
        lvEl('lvOval').classList.add('d-none');
        lvLangkah();
        lvPesan('Liveness berhasil ✓ Periksa foto lalu klik Simpan Liveness', '#047857');
        lvEl('btnUploadFace').disabled = false;
      }, 'image/jpeg', 0.92);
    }

    $('#lvUlang').on('click', () => { if (LV.aktif) lvMulai(); });
    $('#ekycDetailModal').on('hide.bs.modal', () => { LV.aktif = false; lvReset(); }); // matikan kamera begitu modal mulai ditutup

    $('#btnUploadFace').on('click', function() {
      if (!currentEkycId) return;
      if (!LV.blob) { Swal.fire('Perhatian', 'Selesaikan liveness kamera terlebih dahulu.', 'warning'); return; }
      const btn = $(this), asli = btn.html();
      const fd = new FormData();
      fd.append('action', 'upload');
      fd.append('type', 'liveness');
      fd.append('id_ekyc', currentEkycId);
      fd.append('sumber', 'kamera');
      fd.append('liveness', JSON.stringify({ kedip: !!LV.tahap.kedip, kiri: !!LV.tahap.kiri, kanan: !!LV.tahap.kanan, waktu: new Date().toISOString() }));
      fd.append('file', LV.blob, 'liveness_' + currentEkycId + '.jpg');
      btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan…');
      fetch(apiUrl, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
          if (res.status === 'success') {
            Swal.fire({ icon: 'success', title: 'Liveness tersimpan', text: 'Status menjadi Menunggu Verifikasi.', timer: 1500, showConfirmButton: false });
            openDetail(currentEkycId);
            table.ajax.reload(null, false);
          } else {
            Swal.fire('Gagal!', res.message, 'error');
            btn.prop('disabled', false).html(asli);
          }
        })
        .catch(() => { Swal.fire('Gagal!', 'Gagal menyimpan liveness.', 'error'); btn.prop('disabled', false).html(asli); })
        .finally(() => { if (!LV.blob) btn.html(asli); });
    });

    /* =====================================================
       OCR BACA KTP (NIK & NAMA)
    ===================================================== */

    // Peta salah-baca umum OCR -> angka (O->0, I/l->1, dst)
    const OCR_DIGIT_MAP = {
      'O': '0',
      'o': '0',
      'Q': '0',
      'D': '0',
      'I': '1',
      'l': '1',
      'i': '1',
      '|': '1',
      '!': '1',
      'S': '5',
      's': '5',
      'B': '8',
      'Z': '2',
      'z': '2',
      'G': '6',
      'g': '9',
      'q': '9',
      'T': '7',
      'A': '4'
    };

    function mapOcrDigits(str) {
      let out = '';
      for (const ch of str) {
        if (ch >= '0' && ch <= '9') {
          out += ch;
        } else if (OCR_DIGIT_MAP[ch]) {
          out += OCR_DIGIT_MAP[ch];
        } else {
          out += ' '; // pisahkan agar label tidak menyatu dengan angka
        }
      }
      return out;
    }

    // cari window 16 digit yang tidak diapit digit lain
    function findNikWindow(mapped) {
      const re = /\d{16}/g;
      let m;
      let lastMatch = null;
      while ((m = re.exec(mapped)) !== null) {
        const before = mapped[m.index - 1] || ' ';
        const after = mapped[m.index + 16] || ' ';
        if (!/\d/.test(before) && !/\d/.test(after)) {
          return m[0];
        }
        lastMatch = m[0];
      }
      return lastMatch || '';
    }

    // Ekstraksi NIK 16 digit, toleran salah baca OCR
    function extractNikFromText(text) {
      const lines = text.split(/\r?\n/);

      // Prioritas 1: baris yang mengandung kata "NIK"
      const sortedLines = lines.slice().sort(function(a, b) {
        return (/NIK/i.test(b) ? 1 : 0) - (/NIK/i.test(a) ? 1 : 0);
      });

      for (const line of sortedLines) {
        const found = findNikWindow(mapOcrDigits(line));
        if (found) return found;

        // digit menyatu / label ikut terbaca -> squash & sliding window
        const squashed = mapOcrDigits(line).replace(/\s+/g, '');
        if (squashed.length >= 16) {
          let last = '';
          for (let i = 0; i + 16 <= squashed.length; i++) {
            last = squashed.substr(i, 16);
          }
          if (last) return last;
        }
      }

      // Prioritas 2: token alfanumerik panjang (NIK kadang terbaca menyatu)
      const tokens = text.match(/[0-9A-Za-z|!]{10,25}/g) || [];
      for (const token of tokens) {
        const squashed = mapOcrDigits(token).replace(/\s+/g, '');
        if (squashed.length === 16) return squashed;
        const m = squashed.match(/\d{16}/);
        if (m) return m[0];
      }

      return '';
    }

    // Ekstraksi nama dari baris berlabel "Nama"
    function extractNamaFromText(text) {
      const lines = text.split(/\r?\n/).map(function(l) {
        return l.trim();
      }).filter(Boolean);

      for (let i = 0; i < lines.length; i++) {
        if (/\bnama\b/i.test(lines[i])) {
          // teks setelah label pada baris yang sama
          const sameLine = lines[i].replace(/.*nama[:\s]*/i, '').trim();
          if (sameLine && /[A-Za-z]{3,}/.test(sameLine)) {
            return sameLine;
          }
          // baris berikutnya
          if (lines[i + 1]) {
            const next = lines[i + 1].replace(/[^A-Za-z .,'-]/g, ' ').replace(/\s+/g, ' ').trim();
            if (/[A-Za-z]{3,}/.test(next)) {
              return next;
            }
          }
        }
      }
      return '';
    }

    // Pra-pemrosesan gambar: upscale + grayscale + kontras
    // Menerima File (pilihan baru) atau URL string (KTP yang sudah terupload)
    function preprocessKtpImage(source) {
      return new Promise(function(resolve, reject) {
        const img = new Image();
        img.onload = function() {
          const scale = Math.min(3, 2400 / img.width);
          const canvas = document.createElement('canvas');
          canvas.width = Math.round(img.width * scale);
          canvas.height = Math.round(img.height * scale);
          const ctx = canvas.getContext('2d');
          ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

          const imgData = ctx.getImageData(0, 0, canvas.width, canvas.height);
          const d = imgData.data;
          for (let i = 0; i < d.length; i += 4) {
            const gray = 0.299 * d[i] + 0.587 * d[i + 1] + 0.114 * d[i + 2];
            const contrasted = Math.min(255, Math.max(0, (gray - 128) * 1.6 + 128));
            d[i] = d[i + 1] = d[i + 2] = contrasted;
          }
          ctx.putImageData(imgData, 0, 0);
          if (source instanceof File) {
            URL.revokeObjectURL(img.src);
          }
          resolve(canvas);
        };
        img.onerror = reject;
        img.src = source instanceof File ? URL.createObjectURL(source) : source;
      });
    }

    // Levenshtein kecil untuk toleransi salah baca OCR pada huruf
    function levenshtein(a, b) {
      const m = a.length,
        n = b.length;
      if (!m) return n;
      if (!n) return m;
      let prev = Array.from({
        length: n + 1
      }, function(_, i) {
        return i;
      });
      for (let i = 1; i <= m; i++) {
        const cur = [i];
        for (let j = 1; j <= n; j++) {
          cur[j] = Math.min(prev[j] + 1, cur[j - 1] + 1, prev[j - 1] + (a[i - 1] === b[j - 1] ? 0 : 1));
        }
        prev = cur;
      }
      return prev[n];
    }

    // bersihkan kandidat nama: huruf saja, buang kata label umum
    function cleanNameCandidate(str) {
      let s = String(str || '').toUpperCase().replace(/[^A-Z., ]/g, ' ');
      s = s.replace(/\b(NAMA|KTP|NIK|REPUBLIK|INDONESIA|KARTU|TANDA|PENDUDUK)\b/g, ' ');
      s = s.replace(/[.,]+/g, ' ').replace(/\s+/g, ' ').trim();
      return s;
    }

    // ekstrak nama dari label "Nama" (baris sama atau 1-2 baris berikutnya)
    function extractNamaFromText(text) {
      const lines = text.split(/\r?\n/).map(function(l) {
        return l.trim();
      }).filter(Boolean);

      for (let i = 0; i < lines.length; i++) {
        if (/nama/i.test(lines[i])) {
          // teks setelah label pada baris yang sama
          const after = lines[i].replace(/.*nama[:\s]*/i, '');
          let candidate = cleanNameCandidate(after);
          if (candidate.length >= 3) return candidate;

          // baris berikutnya
          for (let j = i + 1; j < Math.min(i + 3, lines.length); j++) {
            candidate = cleanNameCandidate(lines[j]);
            if (candidate.length >= 3) return candidate;
          }
        }
      }
      return '';
    }

    // cocokkan kata nama terdaftar dengan teks OCR (toleran 1-2 huruf salah baca)
    function matchRegisteredName(words, ocrTextUpper) {
      const ocrWords = ocrTextUpper.split(/\s+/).filter(function(w) {
        return w.length >= 2;
      });
      const matched = [];
      const missed = [];

      for (const w of words) {
        const tol = w.length >= 6 ? 2 : 1;
        const hit = ocrWords.some(function(ow) {
          if (ow === w) return true;
          if (Math.abs(ow.length - w.length) > tol) return false;
          return levenshtein(ow, w) <= tol;
        });
        (hit ? matched : missed).push(w);
      }

      return {
        matched: matched,
        missed: missed
      };
    }

    function runOcrKtp(file) {
      if (typeof Tesseract === 'undefined') return;

      $('#ocrPanel').removeClass('d-none');
      $('#ocrLoading').removeClass('d-none').html('<span class="spinner-border spinner-border-sm me-1"></span>Membaca KTP...');
      $('#ocrResult').addClass('d-none');

      preprocessKtpImage(file)
        .then(function(canvas) {
          // Bahasa Indonesia + Inggris, fallback Inggris jika data ind tidak tersedia
          return Tesseract.recognize(canvas, 'ind+eng', {
              logger: function() {}
            })
            .catch(function() {
              return Tesseract.recognize(canvas, 'eng', {
                logger: function() {}
              });
            });
        })
        .then(function(result) {
          const text = (result && result.data && result.data.text) || '';

          const detectedNik = extractNikFromText(text);

          // Nama: ekstrak dari label "Nama" lalu cocokkan (fuzzy) dengan nama terdaftar
          const detectedNama = extractNamaFromText(text);
          const textUpper = text.toUpperCase().replace(/[^A-Z ]/g, ' ');
          const words = (registeredNama || '').toUpperCase().split(/\s+/).filter(function(w) {
            return w.length >= 3;
          });
          const nameMatch = matchRegisteredName(words, textUpper);

          $('#ocrNik').text(detectedNik || 'Tidak terbaca');

          const nikCocok = detectedNik !== '' && detectedNik === registeredNik;
          $('#ocrNikBadge').html(nikCocok ?
            '<span class="badge bg-success-subtle text-success border border-success">Cocok</span>' :
            '<span class="badge bg-danger-subtle text-danger border border-danger">Tidak cocok</span>');

          $('#ocrNamaText').text(detectedNama || 'Tidak terbaca');

          const namaCocok = words.length > 0 && nameMatch.missed.length === 0;
          const namaText = words.length ? nameMatch.matched.length + '/' + words.length + ' kata cocok' : '-';
          $('#ocrNamaBadge').html(
            '<span class="badge ' + (namaCocok ? 'bg-success-subtle text-success border border-success' : 'bg-warning-subtle text-warning border border-warning') + '">' + namaText + '</span>'
          );

          $('#ocrLoading').addClass('d-none');
          $('#ocrResult').removeClass('d-none');
          // KTP cocok -> tawarkan lanjut langsung ke rekam wajah
          ocrCocok = nikCocok && namaCocok;
          aturTombolLanjut();
        })
        .catch(function() {
          $('#ocrLoading').html('<span class="text-danger fs-2">Gagal membaca gambar KTP.</span>');
        });
    }

    // preview lokal + OCR saat pilih file KTP
    $('#fileKtp').on('change', function() {
      const file = this.files[0];
      if (!file) return;

      const url = URL.createObjectURL(file);
      $('#prevKtp').attr('src', url).removeClass('d-none');
      $('#prevKtpEmpty').addClass('d-none');

      ocrCocok = null;
      aturTombolLanjut();
      runOcrKtp(file);
    });

    // 🔹 Verifikasi KTP (tahap 1)
    $('#btnVerifikasiKtp').on('click', function() {
      Swal.fire({
        title: 'Verifikasi KTP?',
        text: 'Pastikan NIK & nama pada KTP cocok dengan data registrasi.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Verifikasi KTP',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          setVerifikasi('ktp_terverifikasi');
        }
      });
    });

    // 🔹 Verifikasi / Tolak
    function setVerifikasi(status) {
      if (!currentEkycId) return;

      fetch(apiUrl, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: `action=verify&id_ekyc=${currentEkycId}&status_verifikasi=${status}`
        })
        .then(res => res.json())
        .then(res => {
          if (res.status === 'success') {
            table.ajax.reload(null, false);
            if (status === 'ktp_terverifikasi') {
              // jangan tutup modal: lanjut langsung ke rekam wajah (kamera aktif otomatis)
              Swal.fire({ icon: 'success', title: 'KTP terverifikasi', text: 'Lanjut rekam wajah pemohon.', timer: 1600, showConfirmButton: false });
              openDetail(currentEkycId);
              return;
            }
            Swal.fire('Berhasil!', res.message, 'success');
            $('#ekycDetailModal').modal('hide');
          } else {
            Swal.fire('Gagal!', res.message, 'error');
          }
        })
        .catch(() => {
          Swal.fire('Gagal!', 'Tidak dapat terhubung ke server.', 'error');
        });
    }

    $('#btnVerifikasi').on('click', function() {
      Swal.fire({
        title: 'Verifikasi eKYC?',
        text: 'Pastikan foto KTP dan liveness wajah sudah sesuai dengan identitas pemohon.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Verifikasi',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          setVerifikasi('terverifikasi');
        }
      });
    });

    $('#btnTolak').on('click', function() {
      Swal.fire({
        title: 'Tolak Verifikasi?',
        text: 'Status eKYC akan diubah menjadi Ditolak.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Tolak',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#d33'
      }).then((result) => {
        if (result.isConfirmed) {
          setVerifikasi('ditolak');
        }
      });
    });
  });
</script>

<!-- MODAL DETAIL & VERIFIKASI EKYC -->
<div class="modal fade" id="ekycDetailModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-id-card me-1"></i> Detail eKYC & Verifikasi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">

        <!-- IDENTITAS -->
        <div class="card border mb-3">
          <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
              <div>
                <h6 class="fw-bold mb-1" id="dNama">-</h6>
                <div class="text-muted fs-3">
                  NIK: <span class="fw-semibold text-dark" id="dNik">-</span>
                  <?php if ($ekGlobal): ?> | Faskes: <span class="fw-semibold text-dark" id="dFaskes">-</span><?php endif; ?>
                </div>
                <div class="text-muted fs-3">
                  Email: <span id="dEmail">-</span> | HP: <span id="dHp">-</span>
                </div>
              </div>
              <span id="dStatusVerifikasi" class="badge bg-secondary">-</span>
            </div>
          </div>
        </div>

        <!-- TAHAPAN -->
        <div class="ek-step" id="ekStep"></div>

        <div class="row g-3">

          <!-- UPLOAD KTP -->
          <div class="col-md-6">
            <div class="card border h-100">
              <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <h6 class="fw-bold mb-0"><i class="fas fa-address-card me-1 text-primary"></i> Foto KTP</h6>
                  <?php if ($canManageEkyc): ?>
                    <button class="btn btn-sm btn-info d-none" type="button" id="btnVerifikasiKtp">
                      <i class="fas fa-check me-1"></i> Verifikasi KTP
                    </button>
                  <?php endif; ?>
                </div>
                <div class="border rounded bg-light d-flex align-items-center justify-content-center mb-2"
                  style="min-height:180px; overflow:hidden;">
                  <img id="prevKtp" src="" alt="Belum ada KTP" class="img-fluid d-none" style="max-height:220px;">
                  <span id="prevKtpEmpty" class="text-muted fs-3">Belum diupload</span>
                </div>
                <?php if ($canManageEkyc): ?>
                  <div class="input-group mb-2">
                    <input type="file" class="form-control" id="fileKtp" accept="image/jpeg,image/png,image/webp">
                    <button class="btn btn-primary text-nowrap" type="button" id="btnUploadKtp">
                      <i class="fas fa-upload me-1"></i> Simpan KTP
                    </button>
                  </div>
                <?php endif; ?>

                <!-- HASIL BACA KTP (OCR) -->
                <div id="ocrPanel" class="border rounded p-2 d-none" style="background:#f8f9fc;">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="fs-2 text-muted mb-0" id="ocrLoading">
                      <span class="spinner-border spinner-border-sm me-1"></span>Membaca KTP...
                    </span>
                    <button class="btn btn-sm btn-outline-secondary py-0 px-1" type="button" id="btnBacaUlang" title="Baca ulang KTP">
                      <i class="fas fa-sync-alt"></i>
                    </button>
                  </div>
                  <div id="ocrResult" class="d-none">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                      <span class="fs-2 fw-semibold">NIK terbaca:</span>
                      <span class="fs-2"><code id="ocrNik">-</code> <span id="ocrNikBadge"></span></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                      <span class="fs-2 fw-semibold">Nama terbaca:</span>
                      <span class="fs-2" id="ocrNamaText">-</span>
                    </div>
                    <div class="d-flex justify-content-end align-items-center">
                      <span id="ocrNamaBadge"></span>
                    </div>
                  </div>
                  <div id="ocrLanjut" class="mt-2 d-none"></div>
                </div>

                <small class="text-muted">JPG/PNG/WEBP, maks 5 MB. NIK & nama pada KTP dibaca otomatis.</small>
              </div>
            </div>
          </div>

          <!-- UPLOAD LIVENESS -->
          <div class="col-md-6">
            <div class="card border h-100 position-relative">
              <div class="card-body p-3">
                <h6 class="fw-bold mb-2"><i class="fas fa-face-smile me-1 text-success"></i> Liveness Wajah</h6>
                <div id="lvFoto" class="border rounded bg-light d-flex align-items-center justify-content-center mb-2"
                  style="min-height:180px; overflow:hidden;">
                  <img id="prevFace" src="" alt="Belum ada liveness" class="img-fluid d-none" style="max-height:220px;">
                  <span id="prevFaceEmpty" class="text-muted fs-3">Belum ada liveness</span>
                </div>
                <?php if ($canManageEkyc): ?>
                  <!-- Liveness wajib lewat kamera (aktif otomatis setelah KTP terverifikasi) -->
                  <div id="lvKamera" class="d-none">
                    <div class="lv-stage mb-2">
                      <video id="lvVideo" autoplay muted playsinline></video>
                      <div class="lv-oval" id="lvOval"></div>
                      <img id="lvHasil" class="d-none" alt="Hasil liveness">
                    </div>
                    <div class="lv-pesan" id="lvPesan">Menyiapkan kamera…</div>
                    <div class="lv-langkah" id="lvLangkah">
                      <span data-k="wajah"><i class="far fa-circle"></i> Wajah di tengah</span>
                      <span data-k="kedip"><i class="far fa-circle"></i> Kedipkan mata</span>
                      <span data-k="kiri"><i class="far fa-circle"></i> Toleh ke kiri</span>
                      <span data-k="kanan"><i class="far fa-circle"></i> Toleh ke kanan</span>
                    </div>
                    <div class="d-flex gap-2 mt-2">
                      <button class="btn btn-light btn-sm" type="button" id="lvUlang"><i class="fas fa-rotate-right me-1"></i>Ulangi</button>
                      <button class="btn btn-success btn-sm ms-auto" type="button" id="btnUploadFace" disabled>
                        <i class="fas fa-save me-1"></i> Simpan Liveness
                      </button>
                    </div>
                  </div>
                <?php endif; ?>
                <small class="text-muted d-block mt-1" id="lvInfo">Liveness diambil langsung dari kamera: wajah pemohon harus berkedip dan menoleh. Upload foto tidak diterima.</small>
              </div>
              <!-- 🔒 Terkunci sampai KTP diverifikasi -->
              <div id="livenessLock" class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center text-center p-3"
                style="background:rgba(255,255,255,.94); border-radius:8px; z-index:5;">
                <div>
                  <iconify-icon icon="solar:lock-bold-duotone" class="fs-4 text-muted"></iconify-icon>
                  <div class="fs-3 text-muted mt-1">Liveness dibuka setelah<br><strong>KTP diverifikasi admin</strong></div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
      <div class="modal-footer d-flex justify-content-between flex-wrap gap-2">
        <?php if ($canManageEkyc): ?>
          <button type="button" class="btn btn-outline-danger" id="btnTolak">
            <i class="fas fa-times me-1"></i> Tolak
          </button>
        <?php else: ?>
          <span></span>
        <?php endif; ?>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
          <?php if ($canManageEkyc): ?>
            <button type="button" class="btn btn-primary d-none btn-lanjut-liveness" id="btnLanjutLiveness">
              <i class="fas fa-camera me-1"></i> Verifikasi KTP &amp; lanjut rekam wajah
            </button>
            <button type="button" class="btn btn-success" id="btnVerifikasi">
              <i class="fas fa-check me-1"></i> Verifikasi
            </button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

</html>
