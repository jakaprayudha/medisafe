<?php
$title = 'Log Aktivitas';
require '../../controller/view.php';
// hanya peran admin (controller/visit/logAktivitasController juga dibatasi peran yang sama)
if (!isset($_SESSION['uid_user']) || !in_array((string) ($_SESSION['roles'] ?? ''), ['admin', 'superadmin', 'administrator'], true)) {
  header('Location: ../../');
  exit;
}
session_write_close();
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .min-w-0 { min-width: 0; }
    .la-icon { width: 40px; height: 40px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
    .la-kpi { border: 1px solid #e5eaef; border-radius: 12px; background: #fff; padding: 12px 14px; height: 100%; }
    .la-label { font-size: .72rem; color: #6c757d; }
    .la-val { font-size: 1.35rem; font-weight: 700; color: #2a3547; line-height: 1.25; }
    .la-chips { display: flex; flex-wrap: wrap; gap: 6px; }
    .la-chip { border: 1px solid #e5eaef; background: #fff; color: #5a6a85; border-radius: 20px; padding: .25rem .75rem; font-size: .78rem; cursor: pointer; }
    .la-chip .c { background: #eef1f4; color: #5a6a85; border-radius: 10px; padding: 0 6px; margin-left: 4px; font-size: .7rem; }
    .la-chip.on { background: #5d87ff; border-color: #5d87ff; color: #fff; }
    .la-chip.on .c { background: rgba(255, 255, 255, .25); color: #fff; }
    .la-chip:disabled { opacity: .45; cursor: default; }
    .la-badge { display: inline-block; border-radius: 6px; padding: 2px 8px; font-size: .72rem; font-weight: 600; white-space: nowrap; background: #ecf2ff; color: #3b5bdb; }
    .la-tambah, .la-login, .la-simpan, .la-aktifkan, .la-verifikasi { background: #e6fffa; color: #047857; }
    .la-hapus, .la-hapus_file, .la-login_gagal, .la-login_terkunci, .la-switch_tolak, .la-nonaktifkan { background: #fee2e2; color: #b91c1c; }
    .la-switch_user, .la-switch_kembali, .la-reset_password, .la-ubah_peran, .la-gabung, .la-batal_verifikasi { background: #fff4e5; color: #b45309; }
    .la-logout, .la-login_nonaktif { background: #f1f5f9; color: #475569; }
    .la-kirim_pcare { background: #e0f2fe; color: #075985; }
    .la-sub { font-size: .72rem; color: #94a3b8; }
    .la-ket { max-width: 380px; white-space: normal; font-size: .8rem; color: #475569; }
    .la-asli { display: inline-block; background: #fff4e5; color: #b45309; border-radius: 4px; padding: 0 5px; font-size: .68rem; }
    #laTable td { vertical-align: middle; }
    #laTable > thead > tr > th, #laTable > tbody > tr > td { padding: .55rem .55rem; }
    .la-diff td, .la-diff th { font-size: .8rem; vertical-align: top; }
    .la-diff .beda { background: #fffbeb; }
    .la-diff code { white-space: pre-wrap; word-break: break-word; color: #334155; }
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

          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-3">
              <div class="d-flex flex-wrap align-items-center gap-3">
                <span class="la-icon bg-warning-subtle text-warning"><i class="fas fa-user-shield"></i></span>
                <div class="min-w-0 flex-grow-1">
                  <h5 class="fw-semibold mb-0">Log Aktivitas</h5>
                  <div class="text-muted small">Login, switch user, perubahan user &amp; hak akses, data pasien, gabung RME, CPPT, resume medis, diagnosa/PCare, dan hasil lab.</div>
                </div>
                <a class="btn btn-outline-primary btn-sm" href="module/admin/log_kunjungan"><i class="fas fa-clipboard-list me-1"></i>Log Kunjungan</a>
                <button type="button" class="btn btn-light btn-sm" id="laRefresh" title="Muat ulang"><i class="fas fa-sync-alt"></i></button>
                <button type="button" class="btn btn-outline-success btn-sm" id="laExport"><i class="fas fa-file-csv me-1"></i>Export CSV</button>
              </div>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-6 col-xl-3"><div class="la-kpi"><div class="la-label">Total aktivitas</div><div class="la-val" id="kSemua">-</div><div class="la-sub" id="kUser">&nbsp;</div></div></div>
            <div class="col-6 col-xl-3"><div class="la-kpi"><div class="la-label">Login gagal / terkunci</div><div class="la-val" id="kGagal">-</div></div></div>
            <div class="col-6 col-xl-3"><div class="la-kpi"><div class="la-label">Switch user</div><div class="la-val" id="kSwitch">-</div></div></div>
            <div class="col-6 col-xl-3"><div class="la-kpi"><div class="la-label">Aksi saat memakai akun lain</div><div class="la-val" id="kVia">-</div><div class="la-sub">dicatat dengan pelaku asli</div></div></div>
          </div>

          <div class="card border-0 shadow-sm">
            <div class="card-body">
              <div class="row g-2 align-items-end mb-3">
                <div class="col-6 col-md-2">
                  <label class="form-label small mb-1" for="laDari">Dari</label>
                  <input type="date" class="form-control form-control-sm" id="laDari">
                </div>
                <div class="col-6 col-md-2">
                  <label class="form-label small mb-1" for="laSampai">Sampai</label>
                  <input type="date" class="form-control form-control-sm" id="laSampai">
                </div>
                <div class="col-6 col-md-2">
                  <label class="form-label small mb-1" for="laAksi">Aksi</label>
                  <select class="form-select form-select-sm" id="laAksi"><option value="">Semua aksi</option></select>
                </div>
                <div class="col-6 col-md-4">
                  <label class="form-label small mb-1" for="laCari">Cari</label>
                  <input type="search" class="form-control form-control-sm" id="laCari" placeholder="Nama/No. RM, username, No. kunjungan, IP…">
                </div>
                <div class="col-12 col-md-2 d-flex gap-1 flex-wrap">
                  <button type="button" class="btn btn-sm btn-light border la-cepat" data-h="0">Hari ini</button>
                  <button type="button" class="btn btn-sm btn-light border la-cepat" data-h="6">7 hari</button>
                  <button type="button" class="btn btn-sm btn-light border la-cepat" data-h="29">30 hari</button>
                </div>
              </div>
              <div class="la-chips mb-3" id="laChips"></div>
              <div class="table-responsive">
                <table class="table table-hover align-middle w-100 mb-0" id="laTable">
                  <thead class="table-light">
                    <tr>
                      <th>Waktu</th>
                      <th>Aksi</th>
                      <th>Objek</th>
                      <th>Keterangan</th>
                      <th>User</th>
                      <th></th>
                    </tr>
                  </thead>
                </table>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="laModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <div class="min-w-0"><h5 class="modal-title mb-0" id="lamJudul">Detail log</h5><div class="small text-muted text-truncate" id="lamSub"></div></div>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="lamIsi"></div>
      </div>
    </div>
  </div>

  <?php require 'library.php'; ?>
  <script>
    (function() {
      const $id = id => document.getElementById(id);
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const fmt = n => Number(n || 0).toLocaleString('id-ID');
      const BLN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      const tglJkt = h => new Date(Date.now() - (h || 0) * 86400000).toLocaleDateString('sv-SE', { timeZone: 'Asia/Jakarta' });
      const waktu = s => { const m = String(s || '').match(/^(\d{4})-(\d{2})-(\d{2}) (\d{2}:\d{2})(:\d{2})?/); return m ? `${+m[3]} ${BLN[m[2] - 1]} ${m[1]}<div class="la-sub">${m[4]}${m[5] || ''}</div>` : esc(s); };
      const API = 'controller/visit/logAktivitasController';
      const nilai = v => v === null || v === undefined || v === '' ? '<span class="text-muted">-</span>' : `<code>${esc(typeof v === 'object' ? JSON.stringify(v, null, 1) : v)}</code>`;
      let modul = '', xhr = null;
      $id('laDari').value = tglJkt(6);
      $id('laSampai').value = tglJkt(0);
      const filter = () => ({ dari: $id('laDari').value, sampai: $id('laSampai').value, modul, aksi: $id('laAksi').value, q: $id('laCari').value.trim() });

      function ringkas(j) {
        $id('laChips').innerHTML = `<button type="button" class="la-chip ${modul === '' ? 'on' : ''}" data-m="">Semua<span class="c">${fmt(j.semua)}</span></button>` +
          Object.keys(j.modul).map(m => `<button type="button" class="la-chip ${modul === m ? 'on' : ''}" data-m="${m}" ${!j.hitung[m] && modul !== m ? 'disabled' : ''}>${esc(j.modul[m])}<span class="c">${fmt(j.hitung[m])}</span></button>`).join('');
        const sel = $id('laAksi'), pilih = sel.value;
        sel.innerHTML = '<option value="">Semua aksi</option>' + j.aksi_ada.map(a => `<option value="${esc(a.aksi)}">${esc(a.label)} (${fmt(a.n)})</option>`).join('');
        if (pilih && !j.aksi_ada.some(a => a.aksi === pilih)) sel.insertAdjacentHTML('beforeend', `<option value="${esc(pilih)}">${esc(pilih)}</option>`);
        sel.value = pilih;
        const r = j.ringkas || {};
        $id('kSemua').textContent = fmt(j.semua);
        $id('kUser').textContent = fmt(r.pengguna) + ' user tercatat';
        $id('kGagal').textContent = fmt(r.gagal);
        $id('kGagal').classList.toggle('text-danger', r.gagal > 0);
        $id('kSwitch').textContent = fmt(r.switchu);
        $id('kVia').textContent = fmt(r.via_switch);
      }

      const table = $('#laTable').DataTable({
        processing: true, serverSide: true, searching: false, ordering: false, autoWidth: false, pageLength: 25, lengthMenu: [25, 50, 100, 200],
        dom: 't<"d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2 small"lip>',
        language: { processing: '<div class="spinner-border spinner-border-sm text-primary"></div> Memuat…', emptyTable: 'Belum ada aktivitas pada periode ini.', zeroRecords: 'Tidak ada data yang cocok.',
          info: '_START_–_END_ dari _TOTAL_ aktivitas', infoEmpty: '0 aktivitas', lengthMenu: 'Tampil _MENU_', thousands: '.', paginate: { previous: '‹', next: '›' } },
        ajax: function(d, cb) {
          if (xhr && xhr.readyState !== 4) xhr.abort();
          const kosong = { draw: d.draw, data: [], recordsTotal: 0, recordsFiltered: 0 };
          xhr = $.ajax({ url: API, dataType: 'json', cache: false, data: Object.assign({ draw: d.draw, start: d.start, length: d.length }, filter()) })
            .done(j => {
              if (!j || j.status !== 'success') { cb(kosong); Swal.fire('Gagal memuat log', (j && j.message) || '', 'error'); return; }
              ringkas(j);
              cb(j);
            })
            .fail((x, s) => {
              if (s === 'abort') return;
              cb(kosong);
              Swal.fire('Gagal memuat log', (x.responseJSON && x.responseJSON.message) || (x.status === 401 ? 'Sesi berakhir, silakan login ulang.' : 'Periksa koneksi lalu coba lagi.'), 'error');
            });
        },
        columns: [
          { data: 'created_at', render: v => waktu(v), className: 'text-nowrap' },
          { data: 'aksi', render: (v, t, r) => `<span class="la-badge la-${esc(v)}">${esc(r.aksi_label)}</span><div class="la-sub">${esc(r.modul_label)}</div>` },
          { data: 'ref_label', render: (v, t, r) => `<div class="small fw-semibold">${esc(v || '-')}</div><div class="la-sub">${esc([r.ref_tipe, r.visit_ID].filter(Boolean).join(' · '))}</div>` },
          { data: 'keterangan', render: v => `<div class="la-ket">${esc(v || '')}</div>` },
          { data: 'username', render: (v, t, r) => `<div class="small">${esc(r.fullname || v || '-')}</div><div class="la-sub">${esc([r.fullname && v ? v : '', r.roles].filter(Boolean).join(' · '))}${r.ip ? ' · ' + esc(r.ip) : ''}</div>` +
              (r.pelaku_asli ? `<span class="la-asli" title="Aksi dilakukan saat memakai akun lain (switch user)">oleh ${esc(r.pelaku_asli)}</span>` : '') },
          { data: 'id_log', className: 'text-end', render: (v, t, r) => r.ada_data ? `<button type="button" class="btn btn-sm btn-light border la-detail" data-id="${v}" title="Lihat data"><i class="fas fa-eye"></i></button>` : '' }
        ]
      });
      const muat = () => { table.page(0); table.ajax.reload(); };

      $(document).on('click', '.la-chip', function() { if (this.disabled) return; modul = this.dataset.m; $id('laAksi').value = ''; muat(); });
      $id('laAksi').addEventListener('change', muat);
      $id('laRefresh').addEventListener('click', () => table.ajax.reload(null, false));
      ['laDari', 'laSampai'].forEach(i => $id(i).addEventListener('change', muat));
      let tunda = null;
      $id('laCari').addEventListener('input', () => { clearTimeout(tunda); tunda = setTimeout(muat, 400); });
      document.querySelectorAll('.la-cepat').forEach(b => b.addEventListener('click', () => { $id('laDari').value = tglJkt(+b.dataset.h); $id('laSampai').value = tglJkt(0); muat(); }));
      $id('laExport').addEventListener('click', () => { window.location.href = API + '?' + new URLSearchParams(Object.assign({ export: 'csv' }, filter())).toString(); });

      $(document).on('click', '.la-detail', function() {
        $id('lamIsi').innerHTML = '<div class="text-center text-muted py-4"><div class="spinner-border spinner-border-sm"></div> Memuat…</div>';
        $id('lamJudul').textContent = 'Detail log';
        $id('lamSub').textContent = '';
        bootstrap.Modal.getOrCreateInstance($id('laModal')).show();
        $.getJSON(API, { id: this.dataset.id }).done(j => {
          if (!j || j.status !== 'success') { $id('lamIsi').innerHTML = `<div class="text-danger small">${esc((j && j.message) || 'Gagal memuat.')}</div>`; return; }
          const r = j.data, lama = r.data_lama || {}, baru = r.data_baru || {};
          $id('lamJudul').textContent = r.modul_label + ' · ' + r.aksi_label;
          $id('lamSub').textContent = [r.created_at, r.ref_label, r.fullname || r.username, r.pelaku_asli ? 'pelaku asli ' + r.pelaku_asli : ''].filter(Boolean).join(' · ');
          const dua = !!(r.data_lama && r.data_baru);
          const kunci = [...new Set([...Object.keys(lama), ...Object.keys(baru)])];
          const baris = kunci.map(k => {
            const beda = dua && JSON.stringify(lama[k]) !== JSON.stringify(baru[k]);
            return `<tr class="${beda ? 'beda' : ''}"><th class="text-muted fw-normal text-nowrap">${esc(k)}</th>` +
              (dua ? `<td>${nilai(lama[k])}</td><td>${nilai(baru[k])}</td>` : `<td>${nilai(r.data_baru ? baru[k] : lama[k])}</td>`) + '</tr>';
          }).join('');
          const judulKolom = dua ? '<th>Sebelum</th><th>Sesudah</th>' : `<th>${r.data_baru ? 'Data' : 'Data sebelum dihapus / diubah'}</th>`;
          $id('lamIsi').innerHTML = (r.keterangan ? `<p class="small mb-3">${esc(r.keterangan)}</p>` : '') +
            `<table class="table table-sm la-diff mb-2"><thead class="table-light"><tr><th>Kolom</th>${judulKolom}</tr></thead><tbody>${baris || '<tr><td colspan="3" class="text-muted">Tidak ada data.</td></tr>'}</tbody></table>` +
            (r.user_agent ? `<div class="la-sub">${esc(r.user_agent)}</div>` : '');
        }).fail(() => { $id('lamIsi').innerHTML = '<div class="text-danger small">Gagal memuat detail.</div>'; });
      });
    })();
  </script>
</body>

</html>
