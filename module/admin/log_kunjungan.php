<?php
$title = 'Log Kunjungan';
require '../../controller/view.php';
// hanya peran admin (data dari controller/visit/logKunjunganController juga dibatasi peran yang sama)
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
    .lk-icon { width: 40px; height: 40px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
    .lk-kpi { border: 1px solid #e5eaef; border-radius: 12px; background: #fff; padding: 12px 14px; height: 100%; }
    .lk-label { font-size: .72rem; color: #6c757d; }
    .lk-val { font-size: 1.35rem; font-weight: 700; color: #2a3547; line-height: 1.25; }
    .lk-chips { display: flex; flex-wrap: wrap; gap: 6px; }
    .lk-chip { border: 1px solid #e5eaef; background: #fff; color: #5a6a85; border-radius: 20px; padding: .25rem .75rem; font-size: .78rem; cursor: pointer; }
    .lk-chip .c { background: #eef1f4; color: #5a6a85; border-radius: 10px; padding: 0 6px; margin-left: 4px; font-size: .7rem; }
    .lk-chip.on { background: #5d87ff; border-color: #5d87ff; color: #fff; }
    .lk-chip.on .c { background: rgba(255, 255, 255, .25); color: #fff; }
    .lk-chip:disabled { opacity: .45; cursor: default; }
    .lk-badge { display: inline-block; border-radius: 6px; padding: 2px 8px; font-size: .72rem; font-weight: 600; white-space: nowrap; }
    .lk-daftar { background: #e6fffa; color: #047857; }
    .lk-ubah { background: #ecf2ff; color: #3b5bdb; }
    .lk-selesai { background: #e0f2fe; color: #075985; }
    .lk-ranap { background: #f3e8ff; color: #6b21a8; }
    .lk-batal { background: #fff4e5; color: #b45309; }
    .lk-hapus { background: #fee2e2; color: #b91c1c; }
    .lk-bayar { background: #fef9c3; color: #854d0e; }
    .lk-rm { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: .72rem; background: #f1f5f9; border-radius: 4px; padding: 0 5px; color: #334155; }
    .lk-ket { max-width: 360px; white-space: normal; font-size: .8rem; color: #475569; }
    .lk-sub { font-size: .72rem; color: #94a3b8; }
    .lk-asli { display: inline-block; background: #fff4e5; color: #b45309; border-radius: 4px; padding: 0 5px; font-size: .68rem; }
    #lkTable td { vertical-align: middle; }
    #lkTable > thead > tr > th, #lkTable > tbody > tr > td { padding: .55rem .55rem; }
    .lk-diff td, .lk-diff th { font-size: .8rem; vertical-align: top; }
    .lk-diff .beda { background: #fffbeb; }
    .lk-diff code { white-space: pre-wrap; word-break: break-word; color: #334155; }
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
                <span class="lk-icon bg-primary-subtle text-primary"><i class="fas fa-clipboard-list"></i></span>
                <div class="min-w-0 flex-grow-1">
                  <h5 class="fw-semibold mb-0">Log Kunjungan</h5>
                  <div class="text-muted small">Jejak pendaftaran, perubahan, pembatalan, penghapusan, pulang, dan pembayaran kunjungan pasien beserta user pelakunya.</div>
                </div>
                <a class="btn btn-outline-warning btn-sm" href="module/admin/log_aktivitas"><i class="fas fa-user-shield me-1"></i>Log Aktivitas</a>
                <button type="button" class="btn btn-light btn-sm" id="lkRefresh" title="Muat ulang"><i class="fas fa-sync-alt"></i></button>
                <button type="button" class="btn btn-outline-success btn-sm" id="lkExport"><i class="fas fa-file-csv me-1"></i>Export CSV</button>
              </div>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-6 col-xl-3"><div class="lk-kpi"><div class="lk-label">Total aktivitas</div><div class="lk-val" id="kSemua">-</div></div></div>
            <div class="col-6 col-xl-3"><div class="lk-kpi"><div class="lk-label">Pendaftaran</div><div class="lk-val text-success" id="kDaftar">-</div></div></div>
            <div class="col-6 col-xl-3"><div class="lk-kpi"><div class="lk-label">Batal / tidak hadir</div><div class="lk-val text-warning" id="kBatal">-</div></div></div>
            <div class="col-6 col-xl-3"><div class="lk-kpi"><div class="lk-label">Dihapus permanen</div><div class="lk-val" id="kHapus">-</div><div class="lk-sub" id="kUser">&nbsp;</div></div></div>
          </div>

          <div class="card border-0 shadow-sm">
            <div class="card-body">
              <div class="row g-2 align-items-end mb-3">
                <div class="col-6 col-md-2">
                  <label class="form-label small mb-1" for="lkDari">Dari</label>
                  <input type="date" class="form-control form-control-sm" id="lkDari">
                </div>
                <div class="col-6 col-md-2">
                  <label class="form-label small mb-1" for="lkSampai">Sampai</label>
                  <input type="date" class="form-control form-control-sm" id="lkSampai">
                </div>
                <div class="col-12 col-md-5">
                  <label class="form-label small mb-1" for="lkCari">Cari</label>
                  <input type="search" class="form-control form-control-sm" id="lkCari" placeholder="No. kunjungan, No. RM, nama pasien, user, keterangan…">
                </div>
                <div class="col-12 col-md-3 d-flex gap-1 flex-wrap">
                  <button type="button" class="btn btn-sm btn-light border lk-cepat" data-h="0">Hari ini</button>
                  <button type="button" class="btn btn-sm btn-light border lk-cepat" data-h="6">7 hari</button>
                  <button type="button" class="btn btn-sm btn-light border lk-cepat" data-h="29">30 hari</button>
                </div>
              </div>
              <div class="lk-chips mb-3" id="lkChips"></div>
              <div class="table-responsive">
                <table class="table table-hover align-middle w-100 mb-0" id="lkTable">
                  <thead class="table-light">
                    <tr>
                      <th>Waktu</th>
                      <th>Aksi</th>
                      <th>Pasien / Kunjungan</th>
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

  <div class="modal fade" id="lkModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <div class="min-w-0"><h5 class="modal-title mb-0" id="lkmJudul">Detail log</h5><div class="small text-muted text-truncate" id="lkmSub"></div></div>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="lkmIsi"></div>
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
      const waktu = s => { const m = String(s || '').match(/^(\d{4})-(\d{2})-(\d{2}) (\d{2}:\d{2})(:\d{2})?/); return m ? `${+m[3]} ${BLN[m[2] - 1]} ${m[1]}` + `<div class="lk-sub">${m[4]}${m[5] || ''}</div>` : esc(s); };
      const API = 'controller/visit/logKunjunganController';
      const GRUP = {
        daftar: 'daftar', ubah: 'ubah', ubah_waktu: 'ubah', ganti_dokter: 'ubah', checkin: 'ubah', panggil: 'ubah',
        selesai: 'selesai', pulang: 'selesai', ranap_minta: 'ranap', ranap_masuk: 'ranap', pindah_bed: 'ranap', ranap_aktif: 'ranap', aktif_kembali: 'ranap',
        batal: 'batal', tidak_hadir: 'batal', ranap_batal: 'batal', hapus: 'hapus', bayar: 'bayar'
      };
      const LABEL_DATA = {
        visit_status: 'Status kunjungan', visit_date: 'Tanggal', visit_time: 'Jam', id_doctor: 'Dokter', code_doctor: 'Kode dokter', id_poli: 'Poli',
        visit_notes: 'Catatan', status_ranap: 'Status ranap', id_bed: 'Bed', id_room: 'Ruang', id_ranap: 'ID ranap', cara_keluar: 'Cara keluar',
        kondisi_pulang: 'Kondisi pulang', status_bayar: 'Status bayar', metode_bayar: 'Metode bayar', nomor_transaksi: 'No. transaksi', total: 'Total',
        bayar: 'Dibayar', kembalian: 'Kembalian', id_provider: 'Jenis bayar', source_hub: 'Layanan', created_user: 'Jalur daftar', nama_pasien: 'Pasien',
        nomor_rm: 'No. RM', visit_ID: 'No. kunjungan', id_visit: 'ID kunjungan', id_patient: 'ID pasien', status_rawatinap: 'Rawat inap', diagnosa_awal: 'Diagnosa awal'
      };
      const STATUS = { 0: 'Menunggu', 1: 'Diperiksa', 2: 'Diperiksa', 3: 'Selesai', 4: 'Selesai', 10: 'Belum check-in', 99: 'Batal' };
      const nilai = (k, v) => {
        if (v === null || v === undefined || v === '') return '<span class="text-muted">-</span>';
        if (k === 'visit_status' && STATUS[v] !== undefined) return `${esc(STATUS[v])} <span class="text-muted">(${esc(v)})</span>`;
        if (['total', 'bayar', 'kembalian'].includes(k)) return 'Rp' + fmt(v);
        return `<code>${esc(typeof v === 'object' ? JSON.stringify(v) : v)}</code>`;
      };
      let aksi = '', label = {}, xhr = null, terakhir = null;
      $id('lkDari').value = tglJkt(6);
      $id('lkSampai').value = tglJkt(0);
      const filter = () => ({ dari: $id('lkDari').value, sampai: $id('lkSampai').value, aksi, q: $id('lkCari').value.trim() });

      function chips(j) {
        const urut = Object.keys(j.label);
        $id('lkChips').innerHTML = `<button type="button" class="lk-chip ${aksi === '' ? 'on' : ''}" data-a="">Semua<span class="c">${fmt(j.semua)}</span></button>` +
          urut.map(a => `<button type="button" class="lk-chip ${aksi === a ? 'on' : ''}" data-a="${a}" ${!j.hitung[a] && aksi !== a ? 'disabled' : ''}>${esc(j.label[a])}<span class="c">${fmt(j.hitung[a])}</span></button>`).join('');
        const h = j.hitung;
        $id('kSemua').textContent = fmt(j.semua);
        $id('kDaftar').textContent = fmt(h.daftar);
        $id('kBatal').textContent = fmt((h.batal || 0) + (h.tidak_hadir || 0) + (h.ranap_batal || 0));
        $id('kHapus').textContent = fmt(h.hapus);
        $id('kHapus').classList.toggle('text-danger', h.hapus > 0);
        $id('kUser').textContent = fmt(j.pengguna) + ' user tercatat';
      }

      const table = $('#lkTable').DataTable({
        processing: true, serverSide: true, searching: false, ordering: false, autoWidth: false, pageLength: 25, lengthMenu: [25, 50, 100, 200],
        dom: 't<"d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2 small"lip>',
        language: { processing: '<div class="spinner-border spinner-border-sm text-primary"></div> Memuat…', emptyTable: 'Belum ada aktivitas pada periode ini.', zeroRecords: 'Tidak ada data yang cocok.',
          info: '_START_–_END_ dari _TOTAL_ aktivitas', infoEmpty: '0 aktivitas', lengthMenu: 'Tampil _MENU_', thousands: '.', paginate: { previous: '‹', next: '›' } },
        ajax: function(d, cb) {
          if (xhr && xhr.readyState !== 4) xhr.abort();
          xhr = $.ajax({ url: API, dataType: 'json', cache: false, data: Object.assign({ draw: d.draw, start: d.start, length: d.length }, filter()) })
            .done(j => {
              if (!j || j.status !== 'success') { cb({ draw: d.draw, data: [], recordsTotal: 0, recordsFiltered: 0 }); Swal.fire('Gagal memuat log', (j && j.message) || '', 'error'); return; }
              label = j.label; terakhir = j; chips(j);
              cb(j);
            })
            .fail((x, s) => {
              if (s === 'abort') return;
              cb({ draw: d.draw, data: [], recordsTotal: 0, recordsFiltered: 0 });
              Swal.fire('Gagal memuat log', (x.responseJSON && x.responseJSON.message) || (x.status === 401 ? 'Sesi berakhir, silakan login ulang.' : 'Periksa koneksi lalu coba lagi.'), 'error');
            });
        },
        columns: [
          { data: 'created_at', render: v => waktu(v), className: 'text-nowrap' },
          { data: 'aksi', render: (v, t, r) => `<span class="lk-badge lk-${GRUP[v] || 'ubah'}">${esc(r.aksi_label)}</span>` + (r.sumber ? `<div class="lk-sub">${esc(r.sumber)}</div>` : '') },
          { data: 'nama_pasien', render: (v, t, r) => `<div class="fw-semibold small">${esc(v || '-')}</div><div class="lk-sub">${r.nomor_rm ? `<span class="lk-rm">${esc(r.nomor_rm)}</span> ` : ''}${esc(r.visit_ID || '')}</div>` },
          { data: 'keterangan', render: v => `<div class="lk-ket">${esc(v || '')}</div>` },
          { data: 'username', render: (v, t, r) => `<div class="small">${esc(r.fullname || v || '-')}</div><div class="lk-sub">${esc([r.fullname && v ? v : '', r.roles].filter(Boolean).join(' · '))}${r.ip ? ' · ' + esc(r.ip) : ''}</div>` +
              (r.pelaku_asli ? `<span class="lk-asli" title="Aksi dilakukan saat memakai akun lain (switch user)">oleh ${esc(r.pelaku_asli)}</span>` : '') },
          { data: 'id_log', className: 'text-end', render: (v, t, r) => r.ada_data ? `<button type="button" class="btn btn-sm btn-light border lk-detail" data-id="${v}" title="Lihat perubahan data"><i class="fas fa-eye"></i></button>` : '' }
        ]
      });
      const muat = () => table.ajax.reload();

      $(document).on('click', '.lk-chip', function() { if (this.disabled) return; aksi = this.dataset.a; table.page(0); muat(); });
      $id('lkRefresh').addEventListener('click', muat);
      ['lkDari', 'lkSampai'].forEach(i => $id(i).addEventListener('change', () => { table.page(0); muat(); }));
      let tunda = null;
      $id('lkCari').addEventListener('input', () => { clearTimeout(tunda); tunda = setTimeout(() => { table.page(0); muat(); }, 400); });
      document.querySelectorAll('.lk-cepat').forEach(b => b.addEventListener('click', () => { $id('lkDari').value = tglJkt(+b.dataset.h); $id('lkSampai').value = tglJkt(0); table.page(0); muat(); }));
      $id('lkExport').addEventListener('click', () => { window.location.href = API + '?' + new URLSearchParams(Object.assign({ export: 'csv' }, filter())).toString(); });

      $(document).on('click', '.lk-detail', function() {
        $id('lkmIsi').innerHTML = '<div class="text-center text-muted py-4"><div class="spinner-border spinner-border-sm"></div> Memuat…</div>';
        $id('lkmJudul').textContent = 'Detail log';
        $id('lkmSub').textContent = '';
        bootstrap.Modal.getOrCreateInstance($id('lkModal')).show();
        $.getJSON(API, { id: this.dataset.id }).done(j => {
          if (!j || j.status !== 'success') { $id('lkmIsi').innerHTML = `<div class="text-danger small">${esc((j && j.message) || 'Gagal memuat.')}</div>`; return; }
          const r = j.data, lama = r.data_lama || {}, baru = r.data_baru || {};
          $id('lkmJudul').textContent = r.aksi_label + ' · ' + (r.nama_pasien || '-');
          $id('lkmSub').textContent = [r.created_at, r.visit_ID, r.fullname || r.username, r.pelaku_asli ? 'pelaku asli ' + r.pelaku_asli : ''].filter(Boolean).join(' · ');
          const kunci = [...new Set([...Object.keys(lama), ...Object.keys(baru)])];
          const duaKolom = r.data_lama && r.data_baru && r.aksi !== 'hapus';
          const baris = kunci.map(k => {
            const a = lama[k], b = baru[k], beda = duaKolom && JSON.stringify(a) !== JSON.stringify(b);
            return `<tr class="${beda ? 'beda' : ''}"><th class="text-muted fw-normal text-nowrap">${esc(LABEL_DATA[k] || k)}</th>` +
              (duaKolom ? `<td>${k in lama ? nilai(k, a) : '<span class="text-muted">-</span>'}</td><td>${k in baru ? nilai(k, b) : '<span class="text-muted">-</span>'}</td>` : `<td>${nilai(k, r.data_baru ? b : a)}</td>`) + '</tr>';
          }).join('');
          $id('lkmIsi').innerHTML = (r.keterangan ? `<p class="small mb-3">${esc(r.keterangan)}</p>` : '') +
            (r.aksi === 'hapus' ? '<div class="alert alert-danger py-2 small">Data kunjungan saat dihapus (sudah tidak ada di database).</div>' : '') +
            `<table class="table table-sm lk-diff mb-0"><thead class="table-light"><tr><th>Data</th>${duaKolom ? '<th>Sebelum</th><th>Sesudah</th>' : '<th>Nilai</th>'}</tr></thead><tbody>${baris || '<tr><td colspan="3" class="text-muted">Tidak ada data.</td></tr>'}</tbody></table>`;
        }).fail(() => { $id('lkmIsi').innerHTML = '<div class="text-danger small">Gagal memuat detail.</div>'; });
      });
    })();
  </script>
</body>

</html>
