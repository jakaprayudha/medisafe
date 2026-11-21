<?php
/**
 * Pemeriksaan pasien poliklinik (dokter).
 * Data: controller/doctor/registrasiController (GET, filter tab & tanggal di server).
 */
$title = 'Pemeriksaan';
require '../../controller/view.php';

$PM_IDC = (string) ($_SESSION['id_customer'] ?? '');
$PM_NAMA = (string) ($_SESSION['fullname'] ?? '');
$PM_ADMIN = in_array((string) ($_SESSION['roles'] ?? ''), ['admin', 'superadmin'], true);

// tipe RME faskes ini (dulu: baris pertama setting_clinic untuk semua faskes). Kosong -> 1 (kunjungan), sama seperti sebelumnya.
$rmeType = '1';
$st = $koneksi->prepare("SELECT rme_type FROM setting_clinic WHERE id_customer = ? ORDER BY id LIMIT 1");
$st->bind_param('s', $PM_IDC);
$st->execute();
$r = $st->get_result()->fetch_assoc();
$st->close();
if ($r && trim((string) $r['rme_type']) !== '') $rmeType = trim((string) $r['rme_type']);
session_write_close();
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    @keyframes pm-spin { to { transform: rotate(360deg); } }
    .icon-spin { display: inline-block; animation: pm-spin 1s linear infinite; }

    .pm-icon { width: 40px; height: 40px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
    .pm-kpi { border: 1px solid #e5eaef; border-radius: 12px; background: #fff; padding: 12px 14px; display: flex; align-items: center; gap: 12px; height: 100%; }
    .pm-kpi .n { font-size: 1.35rem; font-weight: 700; line-height: 1.1; color: #111827; }
    .pm-kpi .l { font-size: .78rem; color: #475569; }
    .pm-kpi.klik { cursor: pointer; transition: border-color .15s, box-shadow .15s; }
    .pm-kpi.klik:hover { border-color: #5d87ff; box-shadow: 0 4px 14px rgba(93, 135, 255, .12); }

    .pm-tabs .nav-link { color: #475569; font-weight: 600; border: 0; border-bottom: 2px solid transparent; border-radius: 0; padding: .55rem .9rem; }
    .pm-tabs .nav-link.active { color: #5d87ff; border-bottom-color: #5d87ff; background: transparent; }
    .pm-tabs .badge { font-size: .7rem; }

    .pm-filter .form-label { font-size: .75rem; color: #475569; margin-bottom: 2px; }
    .pm-filter .form-control, .pm-filter .form-select { font-size: .85rem; }

    #tblPeriksa { font-size: .86rem; }
    #tblPeriksa thead th { font-size: .75rem; text-transform: uppercase; letter-spacing: .03em; color: #475569; font-weight: 600; white-space: nowrap; background: #f8fafc; }
    #tblPeriksa td, #tblPeriksa th { padding-left: .55rem; padding-right: .55rem; }
    #tblPeriksa td { vertical-align: middle; white-space: nowrap; padding-top: .55rem; padding-bottom: .55rem; }
    .pm-jam { font-weight: 600; color: #111827; }
    .pm-antri { display: inline-flex; align-items: center; justify-content: center; min-width: 46px; height: 34px; padding: 0 8px; border-radius: 8px;
      font-weight: 700; font-size: .95rem; background: #eef2ff; color: #3730a3; }
    .pm-nama { font-weight: 600; color: #111827; min-width: 190px; max-width: 300px; white-space: normal; line-height: 1.3; }
    .pm-sub { font-size: .76rem; color: #475569; }
    .pm-sub .sep { color: #cbd5e1; margin: 0 4px; }
    .pm-dok { min-width: 150px; max-width: 230px; white-space: normal; line-height: 1.3; }
    .pm-aksi { display: flex; gap: 6px; justify-content: flex-start; flex-wrap: nowrap; }
    .pm-aksi .btn { white-space: nowrap; }
    .badge.bg-warning, .badge.text-bg-warning, .badge.bg-warning-subtle { color: #1f2937 !important; }
    .badge.bg-warning-subtle { background-color: #fef3c7 !important; }
    tr.pm-aktif td { background: #fffbeb !important; }
    .pm-kosong { padding: 36px 12px; text-align: center; color: #475569; }
  </style>
</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <?php require '../admin/sidebar.php'; ?>
    <div class="body-wrapper">
      <?php require '../admin/navbar.php'; ?>
      <div class="body-wrapper-inner">
        <div class="container-fluid">

          <!-- judul -->
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-3">
              <div class="d-flex flex-wrap align-items-center gap-3">
                <span class="pm-icon bg-primary-subtle text-primary"><i class="ti ti-stethoscope"></i></span>
                <div class="flex-grow-1" style="min-width:0">
                  <h5 class="fw-semibold mb-0">Pemeriksaan Pasien Poliklinik</h5>
                  <div class="text-muted small">
                    <?= $PM_ADMIN ? 'Semua dokter' : 'Pasien ' . htmlspecialchars($PM_NAMA, ENT_QUOTES, 'UTF-8') ?>
                    · <span id="pmRentang">hari ini</span> · <span id="pmUpdate">memuat…</span>
                  </div>
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm" id="btnMuatUlang"><i class="ti ti-refresh me-1"></i>Muat ulang</button>
              </div>
            </div>
          </div>

          <!-- KPI -->
          <div class="row g-3 mb-3">
            <div class="col-6 col-xl-3">
              <div class="pm-kpi klik" data-tab="belum" title="Lihat antrian">
                <span class="pm-icon bg-danger-subtle text-danger"><i class="ti ti-hourglass"></i></span>
                <div><div class="n" id="kMenunggu">-</div><div class="l">Menunggu diperiksa</div></div>
              </div>
            </div>
            <div class="col-6 col-xl-3">
              <div class="pm-kpi klik" data-tab="belum" title="Lihat antrian">
                <span class="pm-icon bg-warning-subtle" style="color:#b45309"><i class="ti ti-heartbeat"></i></span>
                <div><div class="n" id="kDiperiksa">-</div><div class="l">Sedang diperiksa</div></div>
              </div>
            </div>
            <div class="col-6 col-xl-3">
              <div class="pm-kpi klik" data-tab="sudah" title="Lihat pasien selesai">
                <span class="pm-icon bg-success-subtle text-success"><i class="ti ti-circle-check"></i></span>
                <div><div class="n" id="kSelesai">-</div><div class="l">Selesai dilayani</div></div>
              </div>
            </div>
            <div class="col-6 col-xl-3">
              <div class="pm-kpi">
                <span class="pm-icon bg-info-subtle text-info"><i class="ti ti-id-badge-2"></i></span>
                <div><div class="n" id="kKonfirmasi">-</div><div class="l">BPJS perlu konfirmasi hadir</div></div>
              </div>
            </div>
          </div>

          <div class="card border-0 shadow-sm">
            <div class="card-body p-3 p-md-4">
              <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-3">
                <ul class="nav pm-tabs" id="pmTabs" role="tablist">
                  <li class="nav-item"><button class="nav-link active" type="button" data-tab="belum">Antrian <span class="badge rounded-pill bg-danger ms-1" id="tBelum">0</span></button></li>
                  <li class="nav-item"><button class="nav-link" type="button" data-tab="sudah">Selesai <span class="badge rounded-pill bg-success ms-1" id="tSudah">0</span></button></li>
                </ul>
                <form id="filterForm" class="pm-filter d-flex flex-wrap align-items-end gap-2" onsubmit="return false">
                  <div>
                    <label for="pmCari" class="form-label">Cari</label>
                    <input type="search" id="pmCari" class="form-control form-control-sm" placeholder="Nama / No. RM / antrian" style="width:200px">
                  </div>
                  <div>
                    <label for="provider" class="form-label">Jenis bayar</label>
                    <select id="provider" class="form-select form-select-sm" style="width:170px">
                      <option value="">Semua</option>
                    </select>
                  </div>
                  <div>
                    <label for="fromDate" class="form-label">Dari</label>
                    <input type="date" id="fromDate" class="form-control form-control-sm">
                  </div>
                  <div>
                    <label for="toDate" class="form-label">Sampai</label>
                    <input type="date" id="toDate" class="form-control form-control-sm">
                  </div>
                  <button type="button" id="btnFilter" class="btn btn-dark btn-sm"><i class="ti ti-filter me-1"></i>Terapkan</button>
                  <button type="button" id="btnReset" class="btn btn-light btn-sm" title="Kembali ke hari ini"><i class="ti ti-arrow-back-up"></i></button>
                </form>
              </div>

              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 w-100" id="tblPeriksa">
                  <thead>
                    <tr>
                      <th style="width:1%">Aksi</th>
                      <th style="width:1%">Antrian</th>
                      <th>Pasien</th>
                      <th>Daftar</th>
                      <th>Poli / Dokter</th>
                      <th>Jenis bayar</th>
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

  <?php require '../admin/library.php'; ?>
  <script src="controller/socket/socket.js"></script>
  <script>
    (function() {
      const API = 'controller/doctor/registrasiController';
      const RME_FILE = <?= json_encode($rmeType === '1' ? 'kunjungan' : 'pemeriksaan_b') ?>;
      const HARI_INI = new Date().toLocaleDateString('sv-SE', { timeZone: 'Asia/Jakarta' });
      const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      const STATUS = {
        0: ['bg-danger-subtle text-danger', 'Belum dilayani'],
        1: ['bg-warning-subtle', 'Sedang diperiksa'],
        4: ['bg-success-subtle text-success', 'Selesai'],
      };

      let tab = 'belum';
      let baris = new Map(); // id_visit -> row
      let konfirmasiTerakhir = null;
      let sibuk = false;

      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const tglID = d => { if (!d) return '-'; const [y, m, h] = String(d).slice(0, 10).split('-'); return `${+h} ${BULAN[+m - 1]} ${y}`; };
      const jam = t => t ? String(t).slice(0, 5) : '';
      // A12 -> urutan alami (huruf, lalu angka)
      const urutAntri = a => { const m = String(a || '').match(/^(\D*)(\d*)/); return (m[1] || '~').toUpperCase().padEnd(3, ' ') + String(+m[2] || 0).padStart(5, '0'); };

      $('#fromDate, #toDate').val(HARI_INI).attr('max', HARI_INI);

      const table = $('#tblPeriksa').DataTable({
        data: [],
        deferRender: true,
        order: [],
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        dom: 'rt<"d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3"ilp>',
        language: {
          info: '_START_–_END_ dari _TOTAL_ pasien',
          infoEmpty: '0 pasien',
          infoFiltered: '(disaring dari _MAX_)',
          lengthMenu: '_MENU_ / halaman',
          zeroRecords: '<div class="pm-kosong"><i class="ti ti-search fs-6 d-block mb-1"></i>Tidak ada pasien yang cocok.</div>',
          emptyTable: '<div class="pm-kosong"><i class="ti ti-mood-empty fs-6 d-block mb-1"></i>Belum ada pasien pada rentang tanggal ini.</div>',
          paginate: { previous: '‹', next: '›' },
        },
        columns: [
          { data: null, orderable: false, render: (r, t) => t === 'display' ? tombol(r) : '' },
          { data: null, render: (r, t) => t === 'display' ? `<span class="pm-antri">${esc(r.visit_antrian || '-')}</span>` : urutAntri(r.visit_antrian) },
          {
            data: null,
            render: (r, t) => {
              if (t !== 'display') return `${r.nama || ''} ${r.nomor_rm || ''} ${r.visit_antrian || ''} ${r.noKartu || ''}`;
              const jk = r.patient_gender ? esc(r.patient_gender) : '';
              const umur = r.umur !== null && r.umur !== undefined ? `${r.umur} th` : '';
              const info = [r.nomor_rm ? 'RM ' + esc(r.nomor_rm) : '', [jk, umur].filter(Boolean).join(', ')].filter(Boolean).join('<span class="sep">|</span>');
              return `<div class="pm-nama">${esc(r.nama || '(tanpa nama)')}</div><div class="pm-sub">${info || '-'}</div>`;
            }
          },
          {
            data: null,
            render: (r, t) => t === 'display'
              ? `<div class="pm-jam">${esc(jam(r.visit_time)) || '-'}</div><div class="pm-sub">${tglID(r.visit_date)}</div>`
              : `${r.visit_date || ''} ${r.visit_time || ''}`
          },
          { data: null, render: (r, t) => t === 'display' ? `<div class="pm-dok"><div>${esc(r.id_poli || '-')}</div><div class="pm-sub">${esc(r.id_doctor || '-')}</div></div>` : `${r.id_poli || ''} ${r.id_doctor || ''}` },
          {
            data: null,
            render: (r, t) => {
              if (t !== 'display') return r.provider_name || '';
              const bpjs = String(r.id_provider) === '1';
              const nama = bpjs ? 'BPJS' : (r.provider_name || '-');
              return `<span class="badge ${bpjs ? 'bg-success-subtle text-success' : 'bg-light text-dark border'}" title="${esc(r.provider_name || '')}">${esc(nama)}</span>`;
            }
          },
          {
            data: null,
            render: (r, t) => {
              const [k, l] = STATUS[r.visit_status] || ['bg-secondary-subtle text-dark', 'Status ' + r.visit_status];
              if (t !== 'display') return l;
              return `<span class="badge ${k}">${l}</span>` + (r.perlu_hadir ? `<div class="pm-sub mt-1"><i class="ti ti-alert-circle"></i> Perlu konfirmasi hadir</div>` : '');
            }
          },
        ],
        createdRow: (tr, r) => { if (r.visit_status === 1) tr.classList.add('pm-aktif'); },
      });

      function tombol(r) {
        const id = esc(r.id_visit);
        let x = '';
        if (r.perlu_hadir) {
          x += `<button type="button" class="btn btn-sm btn-success btn-hadir" data-id="${id}" data-status="1" title="Konfirmasi pasien hadir"><i class="ti ti-check me-1"></i>Hadir</button>`;
          x += `<button type="button" class="btn btn-sm btn-outline-danger btn-hadir" data-id="${id}" data-status="2" title="Pasien tidak hadir"><i class="ti ti-x"></i></button>`;
        } else {
          x += `<button type="button" class="btn btn-sm btn-primary btn-pemeriksaan" data-id="${id}"><i class="ti ti-stethoscope me-1"></i>${r.visit_status === 4 ? 'Buka' : 'Periksa'}</button>`;
        }
        if (tab === 'belum' && r.source_hub === 'Poliklinik') {
          x += `<button type="button" class="btn btn-sm btn-warning btn-call" data-id="${id}" title="Panggil pasien"><i class="ti ti-volume"></i></button>`;
        }
        return `<div class="pm-aksi">${x}</div>`;
      }

      function rentangTeks(d, s) {
        if (d === s) return d === HARI_INI ? 'hari ini' : tglID(d);
        return `${tglID(d)} – ${tglID(s)}`;
      }

      function muat(opsi = {}) {
        const d = $('#fromDate').val() || HARI_INI;
        let s = $('#toDate').val() || d;
        if (s < d) { Swal.fire({ icon: 'warning', title: 'Tanggal tidak valid', text: '"Sampai" tidak boleh sebelum "Dari".' }); return; }
        if ((new Date(s) - new Date(d)) / 864e5 > 31) { Swal.fire({ icon: 'warning', title: 'Rentang terlalu panjang', text: 'Maksimal 31 hari.' }); return; }
        if (sibuk) return;
        sibuk = true;
        if (!opsi.diam) $('#btnMuatUlang i').attr('class', 'ti ti-loader-2 icon-spin me-1');
        $.ajax({ url: API, dataType: 'json', data: { fromDate: d, toDate: s, tab, provider: $('#provider').val() } })
          .done(res => {
            const data = res.data || [];
            baris = new Map(data.map(r => [String(r.id_visit), r]));
            const hal = table.page();
            table.clear().rows.add(data).draw(false);
            if (opsi.tetap) table.page(hal).draw(false);
            const h = res.hitung || {};
            $('#kMenunggu').text(h.menunggu ?? 0);
            $('#kDiperiksa').text(h.diperiksa ?? 0);
            $('#kSelesai').text(h.selesai ?? 0);
            if (h.konfirmasi !== null && h.konfirmasi !== undefined) konfirmasiTerakhir = h.konfirmasi;
            $('#kKonfirmasi').text(konfirmasiTerakhir ?? '-');
            $('#tBelum').text((h.menunggu || 0) + (h.diperiksa || 0));
            $('#tSudah').text(h.selesai || 0);
            $('#pmRentang').text(rentangTeks(res.rentang?.dari || d, res.rentang?.sampai || s));
            $('#pmUpdate').text('diperbarui ' + new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }));
          })
          .fail(x => {
            const m = x.responseJSON?.message || 'Server tidak merespon.';
            if (opsi.diam) { $('#pmUpdate').text('gagal memperbarui'); return; }
            Swal.fire({ icon: 'error', title: 'Gagal memuat', text: m });
          })
          .always(() => { sibuk = false; $('#btnMuatUlang i').attr('class', 'ti ti-refresh me-1'); });
      }

      function gantiTab(t) {
        if (t === tab) return;
        tab = t;
        $('#pmTabs .nav-link').removeClass('active').filter(`[data-tab="${t}"]`).addClass('active');
        muat();
      }

      $('#pmTabs').on('click', '.nav-link', function() { gantiTab(this.dataset.tab); });
      $('.pm-kpi.klik').on('click', function() { gantiTab(this.dataset.tab); });
      $('#btnFilter').on('click', () => muat());
      $('#provider').on('change', () => muat());
      $('#fromDate, #toDate').on('keydown', e => { if (e.key === 'Enter') muat(); });
      $('#btnMuatUlang').on('click', () => muat());
      $('#btnReset').on('click', () => {
        $('#fromDate, #toDate').val(HARI_INI);
        $('#provider').val('');
        $('#pmCari').val('');
        table.search('');
        muat();
      });
      let tCari;
      $('#pmCari').on('input', function() { clearTimeout(tCari); tCari = setTimeout(() => table.search(this.value).draw(), 200); });

      // segarkan antrian tiap 60 dtk bila tab browser terlihat & tidak ada dialog terbuka
      setInterval(() => {
        if (tab === 'belum' && !document.hidden && !Swal.isVisible()) muat({ diam: true, tetap: true });
      }, 60000);
      document.addEventListener('visibilitychange', () => { if (!document.hidden && tab === 'belum') muat({ diam: true, tetap: true }); });

      // jenis bayar
      $.getJSON('controller/admisi/services/get_provider.php', { type: 'BPJS' })
        .done(list => {
          $('#provider').html('<option value="">Semua</option>' + (list || []).map(p => `<option value="${esc(p.id)}">${esc(p.text)}</option>`).join(''));
        })
        .fail(() => $('#provider').html('<option value="">Semua (gagal memuat)</option>'));

      muat();

      const ambil = el => baris.get(String($(el).data('id')));
      const keHalaman = r => { window.location.href = `module/admin/${RME_FILE}?no=${encodeURIComponent(r.visit_ID)}&rm=${encodeURIComponent(r.nomor_rm || '')}`; };

      // ===== pemeriksaan =====
      $(document).on('click', '.btn-pemeriksaan', function() {
        const r = ambil(this);
        if (!r) return;
        if (String(r.id_provider) !== '1') return keHalaman(r);
        // tab iCare dibuka langsung saat klik (window.open setelah ajax diblokir popup blocker)
        const w = window.open('', '_blank');
        if (w) { try { w.document.write('<p style="font:14px sans-serif;padding:16px">Memuat iCare…</p>'); } catch (e) {} }
        Swal.fire({ title: 'Memeriksa iCare…', text: 'Mohon tunggu', allowOutsideClick: false, showConfirmButton: false, didOpen: () => Swal.showLoading() });
        $.ajax({ url: 'controller/admisi/services/validateicare', type: 'POST', dataType: 'json', data: { id: r.id_visit } })
          .done(res => {
            const url = res?.success ? res?.message?.data?.url : null;
            if (url) {
              if (w && !w.closed) w.location.href = url; else window.open(url, '_blank');
              keHalaman(r);
              return;
            }
            if (w && !w.closed) w.close();
            Swal.fire({
              icon: 'warning', title: 'iCare tidak dapat diakses',
              text: res?.message?.message || (typeof res?.message === 'string' ? res.message : 'Coba beberapa saat lagi.'),
              showCancelButton: true, confirmButtonText: 'Buka tanpa iCare', cancelButtonText: 'Kembali', reverseButtons: true
            }).then(x => { if (x.isConfirmed) keHalaman(r); });
          })
          .fail(() => {
            if (w && !w.closed) w.close();
            Swal.fire({
              icon: 'error', title: 'Validasi iCare gagal', text: 'Server tidak merespon.',
              showCancelButton: true, confirmButtonText: 'Buka tanpa iCare', cancelButtonText: 'Kembali', reverseButtons: true
            }).then(x => { if (x.isConfirmed) keHalaman(r); });
          });
      });

      // ===== hadir / tidak hadir (BPJS) =====
      $(document).on('click', '.btn-hadir', function() {
        const r = ambil(this);
        if (!r) return;
        const hadir = String($(this).data('status')) === '1';
        Swal.fire({
          icon: 'question',
          title: hadir ? 'Pasien hadir?' : 'Pasien tidak hadir?',
          html: `<b>${esc(r.nama)}</b><br><span class="small text-muted">Antrian ${esc(r.visit_antrian || '-')} · RM ${esc(r.nomor_rm || '-')}</span>` +
            (hadir ? '' : '<div class="small text-danger mt-2">Kunjungan akan dibatalkan.</div>'),
          showCancelButton: true,
          confirmButtonText: hadir ? 'Ya, hadir' : 'Ya, tidak hadir',
          confirmButtonColor: hadir ? '#13deb9' : '#fa896b',
          cancelButtonText: 'Batal',
          reverseButtons: true,
        }).then(x => {
          if (!x.isConfirmed) return;
          Swal.fire({ title: 'Sedang diproses…', text: 'Mohon tunggu', allowOutsideClick: false, allowEscapeKey: false, didOpen: () => Swal.showLoading() });
          $.ajax({
            url: 'controller/wsbpjs/setHadir.php', type: 'POST', dataType: 'json',
            data: { visit_id: r.visit_ID, type: r.provider_name || '', statushadir: hadir ? 1 : 2 }
          }).done(res => {
            if (!res?.success) {
              Swal.fire({ icon: 'error', title: 'Gagal', text: res?.message || 'Gagal memproses.' });
              muat({ tetap: true });
              return;
            }
            if (!hadir) {
              Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Kunjungan pasien dibatalkan.', timer: 1800, showConfirmButton: false });
              muat({ tetap: true });
              return;
            }
            $.ajax({ url: 'controller/admisi/services/chackinv2.php', type: 'POST', dataType: 'json', data: { visit: r.visit_ID } })
              .done(p => {
                if (p?.success) Swal.fire({ icon: 'success', title: 'Berhasil', text: p.message || 'Pasien hadir & terdaftar di PCare.' });
                else Swal.fire({ icon: 'warning', title: 'Hadir tercatat', text: 'Set hadir berhasil, namun pendaftaran PCare gagal: ' + (p?.message || '-') });
              })
              .fail(() => Swal.fire({ icon: 'warning', title: 'Hadir tercatat', text: 'Set hadir berhasil, namun server PCare tidak merespon.' }))
              .always(() => muat({ tetap: true }));
          }).fail(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Server tidak merespon.' }));
        });
      });

      // ===== panggil pasien =====
      $(document).on('click', '.btn-call', function() {
        const r = ambil(this);
        if (!r) return;
        const $b = $(this);
        const dokter = String(r.id_doctor || '').replace(/\s+/g, ' ').trim();
        let text;
        if (/^(drg|dr)\.?\s+/i.test(dokter)) text = `Pasien atas nama ${r.nama}, dipersilakan masuk ke ruangan dokter ${dokter.replace(/^(drg|dr)\.?\s*/i, '').trim()}`;
        else if (/^bd\.?\s+/i.test(dokter)) text = `Pasien atas nama ${r.nama}, dipersilakan masuk ke ruangan bidan ${dokter.replace(/^bd\.?\s*/i, '').trim()}`;
        else text = `Pasien atas nama ${r.nama}, dipersilakan masuk ke ruangan ${dokter}`;
        const requestId = (crypto.randomUUID ? crypto.randomUUID() : String(Date.now()) + Math.random());
        sessionStorage.setItem('requestId', requestId);
        $b.prop('disabled', true).find('i').attr('class', 'ti ti-loader-2 icon-spin');
        const pulih = () => $b.prop('disabled', false).find('i').attr('class', 'ti ti-volume');
        $.ajax({
          url: 'controller/admisi/sound.php', type: 'POST', dataType: 'json',
          data: { text, requestId, nama: r.nama, nomor_visit: r.visit_ID }
        }).done(() => {
          setTimeout(pulih, 5000);
        }).fail(x => {
          pulih();
          Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: x.responseJSON?.message || 'Gagal memanggil pasien', timer: 3000, showConfirmButton: false });
        });
      });
    })();
  </script>
</body>

</html>
