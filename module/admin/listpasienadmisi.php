<?php
$title = 'Booking Mobile JKN';
require '../../controller/view.php';
require_once __DIR__ . '/../../controller/visit/registrasiService.php';
$lpOpsi = ['dokter' => [], 'poli' => []];
try {
  $lpIdc = (int) ($_SESSION['id_customer'] ?? 0);
  if ($lpIdc > 0) $lpOpsi = rgOpsi($koneksi, $lpIdc);
} catch (Throwable $e) {
  error_log('[listpasienadmisi] ' . $e->getMessage());
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
    .lp-icon { width: 40px; height: 40px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
    .lp-kpi { border: 1px solid #e5eaef; border-radius: 12px; background: #fff; padding: 12px 14px; height: 100%; }
    .lp-label { font-size: .72rem; color: #6c757d; }
    .lp-val { font-size: 1.35rem; font-weight: 700; color: #2a3547; line-height: 1.25; }
    .lp-sub { font-size: .7rem; color: #94a3b8; }
    .lp-prog { height: 6px; border-radius: 3px; background: #eef1f4; overflow: hidden; margin-top: 6px; }
    .lp-prog > div { height: 100%; background: #13deb9; }
    .lp-tabs .btn { border-radius: 20px; font-size: .8rem; padding: .3rem .8rem; background: #fff; border: 1px solid #e5eaef; color: #5a6a85; }
    .lp-tabs .btn .badge { background: #eef1f4; color: #5a6a85; margin-left: 4px; }
    .lp-tabs .btn.active { background: #5d87ff; border-color: #5d87ff; color: #fff; }
    .lp-tabs .btn.active .badge { background: rgba(255, 255, 255, .25); color: #fff; }
    .lp-cepat .btn { font-size: .78rem; }
    .lp-rm { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: .72rem; background: #f1f5f9; border-radius: 4px; padding: 0 5px; color: #334155; }
    .lp-antri { font-weight: 700; font-size: .95rem; color: #2a3547; }
    .lp-nama { max-width: 260px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    #lpTable td { vertical-align: middle; }
    .lp-aksi { display: flex; gap: 4px; justify-content: center; }
    /* badge kuning: teks hitam agar terbaca */
    .badge.bg-warning, .badge.bg-warning-subtle, .badge.text-bg-warning, .badge.text-warning { color: #1f2937 !important; }
    .badge.bg-warning-subtle { background-color: #fef3c7 !important; }
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

          <!-- HEADER -->
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-3">
              <div class="d-flex flex-wrap align-items-center gap-3">
                <span class="lp-icon bg-success-subtle text-success"><i class="fas fa-mobile-alt"></i></span>
                <div class="min-w-0 flex-grow-1">
                  <h5 class="fw-semibold mb-0">Booking Mobile JKN</h5>
                  <div class="text-muted small" id="lpPeriode">Booking antrean pasien BPJS dari aplikasi Mobile JKN</div>
                </div>
                <div class="form-check form-switch mb-0 small" title="Muat ulang otomatis tiap 60 detik">
                  <input class="form-check-input" type="checkbox" id="lpAuto" checked>
                  <label class="form-check-label text-muted" for="lpAuto">Auto refresh</label>
                </div>
                <button type="button" class="btn btn-light btn-sm" id="lpRefresh" title="Muat ulang"><i class="fas fa-sync-alt"></i></button>
              </div>
            </div>
          </div>

          <!-- KPI -->
          <div class="row g-3 mb-3">
            <div class="col-6 col-md-4 col-xl-2"><div class="lp-kpi"><div class="lp-label">Total booking</div><div class="lp-val" id="kTotal">-</div><div class="lp-sub">&nbsp;</div></div></div>
            <div class="col-6 col-md-4 col-xl-2"><div class="lp-kpi"><div class="lp-label">Belum check-in</div><div class="lp-val text-warning" id="kBelum">-</div><div class="lp-sub" id="kTidakHadir">&nbsp;</div></div></div>
            <div class="col-6 col-md-4 col-xl-2"><div class="lp-kpi"><div class="lp-label">Sudah check-in</div><div class="lp-val text-primary" id="kSudah">-</div><div class="lp-sub">menunggu / dilayani</div></div></div>
            <div class="col-6 col-md-4 col-xl-2"><div class="lp-kpi"><div class="lp-label">Selesai</div><div class="lp-val text-success" id="kSelesai">-</div><div class="lp-sub">&nbsp;</div></div></div>
            <div class="col-6 col-md-4 col-xl-2"><div class="lp-kpi"><div class="lp-label">Batal</div><div class="lp-val text-danger" id="kBatal">-</div><div class="lp-sub">&nbsp;</div></div></div>
            <div class="col-6 col-md-4 col-xl-2"><div class="lp-kpi"><div class="lp-label">Tingkat kehadiran</div><div class="lp-val" id="kHadir">-</div><div class="lp-prog"><div id="kHadirBar" style="width:0"></div></div></div></div>
          </div>

          <div class="card border-0 shadow-sm">
            <div class="card-body p-3 p-lg-4">
              <!-- FILTER -->
              <div class="d-flex flex-wrap align-items-end gap-2 mb-3">
                <div class="btn-group btn-group-sm lp-cepat" role="group">
                  <button type="button" class="btn btn-outline-primary active" data-r="hari">Hari ini</button>
                  <button type="button" class="btn btn-outline-primary" data-r="besok">Besok</button>
                  <button type="button" class="btn btn-outline-primary" data-r="depan">7 hari ke depan</button>
                  <button type="button" class="btn btn-outline-primary" data-r="lalu">7 hari terakhir</button>
                </div>
                <div class="d-flex align-items-center gap-1">
                  <input type="date" id="fromDate" class="form-control form-control-sm" style="width:140px" aria-label="Dari tanggal">
                  <span class="text-muted small">–</span>
                  <input type="date" id="toDate" class="form-control form-control-sm" style="width:140px" aria-label="Sampai tanggal">
                </div>
                <select id="poliSelect" class="form-select form-select-sm" style="width:auto;max-width:190px" aria-label="Poli"><option value="">Semua poli</option></select>
                <select id="doctorSelect" class="form-select form-select-sm" style="width:auto;max-width:210px" aria-label="Dokter"><option value="">Semua dokter</option></select>
                <button type="button" id="btnReset" class="btn btn-sm btn-light" title="Reset filter"><i class="fas fa-undo"></i></button>
                <div class="position-relative ms-lg-auto" style="min-width:220px">
                  <i class="fas fa-search position-absolute text-muted" style="left:10px;top:50%;transform:translateY(-50%);font-size:.75rem"></i>
                  <input type="search" id="lpCari" class="form-control form-control-sm ps-4" placeholder="Cari nama, RM, no. BPJS, antrian…">
                </div>
              </div>

              <div class="lp-tabs d-flex flex-wrap gap-1 mb-3" id="lpTabs">
                <button type="button" class="btn active" data-s="">Semua <span class="badge" data-n="total">0</span></button>
                <button type="button" class="btn" data-s="belum">Belum check-in <span class="badge" data-n="belum">0</span></button>
                <button type="button" class="btn" data-s="sudah">Sudah check-in <span class="badge" data-n="sudah">0</span></button>
                <button type="button" class="btn" data-s="selesai">Selesai <span class="badge" data-n="selesai">0</span></button>
                <button type="button" class="btn" data-s="batal">Batal <span class="badge" data-n="batal">0</span></button>
              </div>

              <div class="table-responsive">
                <table class="table align-middle mb-0 w-100" id="lpTable">
                  <thead>
                    <tr>
                      <th class="text-dark fw-normal text-center" style="width:110px">Aksi</th>
                      <th class="text-dark fw-normal">Antrian</th>
                      <th class="text-dark fw-normal">Pasien</th>
                      <th class="text-dark fw-normal">Poli / Dokter</th>
                      <th class="text-dark fw-normal">Tanggal</th>
                      <th class="text-dark fw-normal text-center">Status</th>
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

  <?php require 'library.php'; ?>
  <script>
    (function() {
      const $id = id => document.getElementById(id);
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const fmt = n => Number(n || 0).toLocaleString('id-ID');
      const BLN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      const HARI = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
      const tglJkt = h => new Date(Date.now() + (h || 0) * 86400000).toLocaleDateString('sv-SE', { timeZone: 'Asia/Jakarta' });
      const tglIndo = s => { const m = String(s || '').match(/^(\d{4})-(\d{2})-(\d{2})/); if (!m) return '-'; const d = new Date(+m[1], m[2] - 1, +m[3]); return HARI[d.getDay()] + ', ' + (+m[3]) + ' ' + BLN[m[2] - 1]; };
      let hariIni = tglJkt(0), fStatus = '', terakhir = 0, xhr = null;

      // pilihan filter disisipkan server (dulu 2 request setiap modal filter dibuka)
      const OPSI = <?= json_encode($lpOpsi, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
      $id('poliSelect').innerHTML += (OPSI.poli || []).map(n => `<option value="${esc(n)}">${esc(n)}</option>`).join('');
      $id('doctorSelect').innerHTML += (OPSI.dokter || []).map(n => `<option value="${esc(n)}">${esc(n)}</option>`).join('');

      function rentang(r) {
        const m = { hari: [0, 0], besok: [1, 1], depan: [0, 7], lalu: [-7, 0] }[r] || [0, 0];
        $id('fromDate').value = tglJkt(m[0]);
        $id('toDate').value = tglJkt(m[1]);
      }
      rentang('hari');

      function statusInfo(r) {
        const s = r.visit_status;
        if (s === 10) return r.visit_date < hariIni ? ['Tidak hadir', 'secondary', 'belum'] : ['Belum check-in', 'warning', 'belum'];
        if (s === 99) return ['Batal', 'danger', 'batal'];
        if (s === 4) return ['Selesai', 'success', 'selesai'];
        if (s === 0) return ['Menunggu', 'primary', 'sudah'];
        return ['Dilayani', 'info', 'sudah'];
      }
      function aksiHtml(r) {
        if (r.visit_status !== 10) return '<span class="text-muted small">—</span>';
        const nanti = r.visit_date > hariIni, lewat = r.visit_date < hariIni;
        return `<div class="lp-aksi">
          <button type="button" class="btn btn-sm ${nanti || lewat ? 'btn-light' : 'btn-success'} btn-checkin" data-visit="${esc(r.visit_ID)}" data-nama="${esc(r.patient_name)}" ${nanti ? 'disabled title="Belum tanggal kunjungan"' : 'title="Check-in pasien"'}><i class="fas fa-check me-1"></i>Check-in</button>
          <button type="button" class="btn btn-sm btn-light text-danger btn-batal" data-visit="${esc(r.visit_ID)}" data-nama="${esc(r.patient_name)}" title="Batalkan booking"><i class="fas fa-times"></i></button></div>`;
      }

      const table = $('#lpTable').DataTable({
        processing: true, serverSide: false, deferRender: true, autoWidth: false, order: [], pageLength: 25,
        lengthMenu: [10, 25, 50, 100, 250],
        dom: 't<"d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2 small"lip>',
        language: {
          processing: '<div class="spinner-border spinner-border-sm text-primary"></div> Memuat…', emptyTable: 'Tidak ada booking Mobile JKN pada periode ini.',
          zeroRecords: 'Tidak ada data yang cocok.', info: '_START_–_END_ dari _TOTAL_ booking', infoEmpty: '0 booking', infoFiltered: '(disaring dari _MAX_)',
          lengthMenu: 'Tampil _MENU_', thousands: '.', paginate: { previous: '‹', next: '›' }
        },
        ajax: function(_d, cb) {
          if (xhr && xhr.readyState !== 4) xhr.abort();
          xhr = $.ajax({ url: 'controller/admisi/services/listpatientjkn.php', dataType: 'json', cache: false,
            data: { fromDate: $id('fromDate').value, toDate: $id('toDate').value, doctor: $id('doctorSelect').value, poli: $id('poliSelect').value } })
            .done(j => {
              terakhir = Date.now();
              if (j.hari_ini) hariIni = j.hari_ini;
              hitung(j.hitung || {}, j.data || []);
              cb({ data: j.data || [] });
            })
            .fail((x, s) => { if (s === 'abort') return; cb({ data: [] }); Swal.fire('Gagal memuat data', x.status === 401 ? 'Sesi berakhir, silakan login kembali.' : 'Periksa koneksi lalu coba lagi.', 'error'); });
        },
        columns: [
          { data: null, orderable: false, searchable: false, className: 'text-center', render: (v, t, r) => t === 'display' ? aksiHtml(r) : '' },
          { data: 'visit_antrian', render: (v, t, r) => t === 'display' ? `<div class="lp-antri">${esc(v || '-')}</div><div class="small text-muted">${esc(String(r.visit_time || '').slice(0, 5))}</div>` : (v || '') },
          { data: 'patient_name', render: (v, t, r) => {
              if (t !== 'display') return [v, r.nomor_rm, r.no_bpjs, r.visit_antrian, r.patient_phone].join(' ');
              const jk = String(r.patient_gender || '').charAt(0).toUpperCase();
              return `<div class="fw-semibold lp-nama" title="${esc(v)}">${esc(v || '-')}</div>
                <div class="small text-muted"><span class="lp-rm">${esc(r.nomor_rm || '-')}</span>${jk ? ' · ' + (jk === 'L' ? 'L' : 'P') : ''}${r.no_bpjs ? ' · <span class="font-monospace">' + esc(r.no_bpjs) + '</span>' : ''}${r.patient_phone ? ' · <i class="fas fa-phone fa-xs"></i> ' + esc(r.patient_phone) : ''}</div>`;
            } },
          { data: 'poli_name', render: (v, t, r) => t === 'display' ? `<div class="small fw-semibold">${esc(v || '-')}</div><div class="small text-muted">${esc(r.doctor_name || '-')}</div>` : (v || '') + ' ' + (r.doctor_name || '') },
          { data: 'visit_date', render: (v, t) => t === 'display' ? `<span class="small ${v === hariIni ? 'fw-semibold text-primary' : ''}">${esc(tglIndo(v))}${v === hariIni ? ' · hari ini' : ''}</span>` : v },
          { data: 'visit_status', className: 'text-center', render: (v, t, r) => { const s = statusInfo(r); return t === 'display' ? `<span class="badge bg-${s[1]}-subtle text-${s[1]}">${s[0]}</span>` : s[2]; } },
        ]
      });

      // filter tab status di sisi klien (instan, tanpa request)
      $.fn.dataTable.ext.search.push((settings, data, i, row) => settings.nTable.id !== 'lpTable' || !fStatus || statusInfo(row)[2] === fStatus);
      $id('lpTabs').addEventListener('click', e => {
        const b = e.target.closest('.btn[data-s]');
        if (!b) return;
        fStatus = b.dataset.s;
        document.querySelectorAll('#lpTabs .btn').forEach(x => x.classList.toggle('active', x === b));
        table.draw();
      });

      function hitung(h, rows) {
        document.querySelectorAll('#lpTabs [data-n]').forEach(el => el.textContent = fmt(h[el.dataset.n]));
        $id('kTotal').textContent = fmt(h.total);
        $id('kBelum').textContent = fmt(h.belum);
        const tidakHadir = rows.filter(r => r.visit_status === 10 && r.visit_date < hariIni).length;
        $id('kTidakHadir').textContent = tidakHadir ? fmt(tidakHadir) + ' tidak hadir (tanggal lewat)' : ' ';
        $id('kSudah').textContent = fmt(h.sudah);
        $id('kSelesai').textContent = fmt(h.selesai);
        $id('kBatal').textContent = fmt(h.batal);
        const dasar = (h.total || 0) - (h.batal || 0), hadir = (h.sudah || 0) + (h.selesai || 0);
        const pct = dasar ? Math.round(hadir / dasar * 100) : 0;
        $id('kHadir').textContent = dasar ? pct + '%' : '-';
        $id('kHadirBar').style.width = pct + '%';
        const f = $id('fromDate').value, t = $id('toDate').value;
        $id('lpPeriode').textContent = 'Booking Mobile JKN · ' + (f === t ? tglIndo(f) : tglIndo(f) + ' – ' + tglIndo(t));
      }

      const muat = () => table.ajax.reload(null, false);
      document.querySelectorAll('.lp-cepat .btn').forEach(b => b.addEventListener('click', () => {
        rentang(b.dataset.r);
        document.querySelectorAll('.lp-cepat .btn').forEach(x => x.classList.toggle('active', x === b));
        muat();
      }));
      ['fromDate', 'toDate'].forEach(i => $id(i).addEventListener('change', () => {
        if ($id('fromDate').value > $id('toDate').value) $id('toDate').value = $id('fromDate').value;
        document.querySelectorAll('.lp-cepat .btn').forEach(x => x.classList.remove('active'));
        muat();
      }));
      ['poliSelect', 'doctorSelect'].forEach(i => $id(i).addEventListener('change', muat));
      $id('btnReset').addEventListener('click', () => {
        rentang('hari');
        $id('poliSelect').value = $id('doctorSelect').value = $id('lpCari').value = '';
        table.search('');
        document.querySelectorAll('.lp-cepat .btn').forEach((x, i) => x.classList.toggle('active', i === 0));
        muat();
      });
      $id('lpRefresh').addEventListener('click', muat);
      let tCari;
      $id('lpCari').addEventListener('input', function() { clearTimeout(tCari); const v = this.value; tCari = setTimeout(() => table.search(v).draw(), 200); });
      setInterval(() => {
        if (!$id('lpAuto').checked || document.hidden || Date.now() - terakhir < 55000) return;
        if ($id('fromDate').value > hariIni || $id('toDate').value < hariIni) return;
        if ((window.Swal && Swal.isVisible()) || document.querySelector('.modal.show')) return;
        muat();
      }, 15000);

      /* ---------- check-in ---------- */
      $(document).on('click', '.btn-checkin', function() {
        const b = this, visit = b.dataset.visit, nama = b.dataset.nama;
        Swal.fire({ title: 'Check-in pasien?', html: `<div class="small">${esc(nama)}</div><div class="small text-muted mt-1">Pastikan pasien sudah hadir di klinik.</div>`, icon: 'question',
          showCancelButton: true, confirmButtonText: 'Ya, check-in', cancelButtonText: 'Batal', confirmButtonColor: '#13deb9', reverseButtons: true })
          .then(r => {
            if (!r.isConfirmed) return;
            b.disabled = true;
            b.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            $.post('controller/admisi/services/chackin.php', { visit }, null, 'json')
              .done(res => {
                if (res && res.success) { Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: res.message, showConfirmButton: false, timer: 1800 }); muat(); }
                else { Swal.fire('Gagal check-in', (res && res.message) || 'Terjadi kesalahan.', 'error'); muat(); }
              })
              .fail(x => { Swal.fire('Gagal check-in', (x.responseJSON && x.responseJSON.message) || 'Terjadi kesalahan server.', 'error'); muat(); });
          });
      });

      /* ---------- batal (antrean BPJS + lokal) ---------- */
      $(document).on('click', '.btn-batal', function() {
        const visit = this.dataset.visit, nama = this.dataset.nama;
        Swal.fire({ title: 'Batalkan booking?', html: `<div class="small mb-2">${esc(nama)} · ${esc(visit)}</div>`, icon: 'warning', input: 'textarea',
          inputPlaceholder: 'Alasan pembatalan…', showCancelButton: true, confirmButtonText: 'Ya, batalkan', cancelButtonText: 'Kembali', confirmButtonColor: '#dc3545', reverseButtons: true,
          preConfirm: a => { if (!a || !a.trim()) Swal.showValidationMessage('Alasan wajib diisi'); return a && a.trim(); } })
          .then(async r => {
            if (!r.isConfirmed) return;
            const alasan = r.value;
            Swal.fire({ title: 'Membatalkan…', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            let peringatan = '';
            try {
              // dulu hanya dibatalkan di klinik, antrean di BPJS tetap aktif
              const b = await $.post('controller/wsbpjs/batalAntrian.php', { novisit: visit, alasan }, null, 'json');
              if (b && b.peringatan) peringatan = b.peringatan;
            } catch (e) { peringatan = 'Antrean di BPJS tidak dapat dihubungi. Batalkan manual bila perlu.'; }
            $.post('controller/admisi/services/batalchackin.php', { visit_ID: visit, alasan }, null, 'json')
              .done(res => {
                if (res && res.success) Swal.fire({ icon: peringatan ? 'warning' : 'success', title: 'Booking dibatalkan', text: peringatan || res.message });
                else Swal.fire('Gagal membatalkan', (res && res.message) || 'Terjadi kesalahan.', 'error');
                muat();
              })
              .fail(() => Swal.fire('Gagal membatalkan', 'Terjadi kesalahan server.', 'error'));
          });
      });
    })();
  </script>
</body>

</html>
