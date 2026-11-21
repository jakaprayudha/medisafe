<?php
$title = 'Registrasi Rawat Inap';
require '../../controller/view.php';
require_once __DIR__ . '/../../controller/visit/registrasiService.php';
$rrOpsi = ['dokter' => [], 'penjamin' => []];
try {
  $rrIdc = (int) ($_SESSION['id_customer'] ?? 0);
  if ($rrIdc > 0) $rrOpsi = rgOpsi($koneksi, $rrIdc);
} catch (Throwable $e) {
  error_log('[registrasi_ranap] ' . $e->getMessage());
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
    .rr-icon { width: 40px; height: 40px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
    .rr-kpi { border: 1px solid #e5eaef; border-radius: 12px; background: #fff; padding: 12px 14px; height: 100%; }
    .rr-label { font-size: .72rem; color: #6c757d; }
    .rr-val { font-size: 1.35rem; font-weight: 700; color: #2a3547; line-height: 1.25; }
    .rr-sub { font-size: .7rem; color: #94a3b8; }
    .rr-prog { height: 6px; border-radius: 3px; background: #eef1f4; overflow: hidden; margin-top: 6px; }
    .rr-prog > div { height: 100%; }
    .rr-tabs .btn { border-radius: 20px; font-size: .8rem; padding: .3rem .8rem; background: #fff; border: 1px solid #e5eaef; color: #5a6a85; }
    .rr-tabs .btn .badge { background: #eef1f4; color: #5a6a85; margin-left: 4px; }
    .rr-tabs .btn.active { background: #5d87ff; border-color: #5d87ff; color: #fff; }
    .rr-tabs .btn.active .badge { background: rgba(255, 255, 255, .25); color: #fff; }
    .rr-rm { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: .72rem; background: #f1f5f9; border-radius: 4px; padding: 0 5px; color: #334155; }
    .rr-nama { max-width: 260px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .rr-lama { font-weight: 700; }
    #rrTable td { vertical-align: middle; white-space: nowrap; }
    #rrTable th { white-space: nowrap; }
    #rrTable td.rr-dd-buka { position: relative; z-index: 1060 !important; }
    #rrTable .dropdown-menu { z-index: 1070 !important; }
    .rr-sec { border: 1px solid #edf0f4; border-radius: 12px; padding: 12px 14px; margin-bottom: 12px; background: #fff; }
    .rr-sec-h { font-weight: 600; font-size: .85rem; color: #2a3547; margin-bottom: 8px; }
    .rr-vital { border-radius: 10px; background: #f8fafc; padding: 8px 10px; height: 100%; }
    .rr-vital.kosong { opacity: .55; }
    .rr-vital-l { font-size: .68rem; color: #64748b; }
    .rr-vital-v { font-weight: 700; color: #2a3547; }
    #rrDetail .modal-body { background: #fafbfc; }
    .rr-sk { border-radius: 10px; background: linear-gradient(90deg, #eef1f4 25%, #f6f8fb 50%, #eef1f4 75%); background-size: 200% 100%; animation: rrsk 1.2s infinite; }
    @keyframes rrsk { to { background-position: -200% 0; } }
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
                <span class="rr-icon bg-primary-subtle text-primary"><i class="fas fa-procedures"></i></span>
                <div class="min-w-0 flex-grow-1">
                  <h5 class="fw-semibold mb-0">Registrasi Rawat Inap</h5>
                  <div class="text-muted small">Pasien yang sedang dirawat dan riwayat pasien pulang</div>
                </div>
                <button type="button" class="btn btn-light btn-sm" id="rrRefresh" title="Muat ulang"><i class="fas fa-sync-alt"></i></button>
                <a class="btn btn-outline-secondary btn-sm" href="module/admin/status_pulang"><i class="fas fa-bed me-1"></i>Bed &amp; status pulang</a>
                <a class="btn btn-primary btn-sm" href="module/admin/registrasi_booking_ranap"><i class="fas fa-user-plus me-1"></i>Permintaan rawat inap</a>
              </div>
            </div>
          </div>

          <!-- KPI -->
          <div class="row g-3 mb-3">
            <div class="col-6 col-md-4 col-xl-2"><div class="rr-kpi"><div class="rr-label">Sedang dirawat</div><div class="rr-val text-primary" id="kDirawat">-</div><div class="rr-sub" id="kLama">&nbsp;</div></div></div>
            <div class="col-6 col-md-4 col-xl-2"><div class="rr-kpi"><div class="rr-label">Masuk hari ini</div><div class="rr-val" id="kMasuk">-</div><div class="rr-sub">&nbsp;</div></div></div>
            <div class="col-6 col-md-4 col-xl-2"><div class="rr-kpi"><div class="rr-label">Pulang (periode)</div><div class="rr-val text-success" id="kPulang">-</div><div class="rr-sub" id="kPeriode">&nbsp;</div></div></div>
            <div class="col-6 col-md-6 col-xl-3"><div class="rr-kpi"><div class="rr-label">Bed terisi (data bed)</div><div class="rr-val" id="kBed">-</div><div class="rr-prog"><div id="kBedBar" class="bg-primary" style="width:0"></div></div><div class="rr-sub mt-1" id="kBedInfo">&nbsp;</div></div></div>
            <div class="col-12 col-md-6 col-xl-3"><div class="rr-kpi"><div class="rr-label">Dirawat &gt; 14 hari</div><div class="rr-val" id="kAlos">-</div><div class="rr-sub">perlu dicek: sudah pulang tetapi belum dipulangkan di sistem?</div></div></div>
          </div>
          <div class="alert alert-warning small py-2 d-none" id="rrPeringatan"></div>

          <div class="card border-0 shadow-sm">
            <div class="card-body p-3 p-lg-4">
              <div class="d-flex flex-wrap align-items-end gap-2 mb-3">
                <div class="rr-tabs d-flex flex-wrap gap-1" id="rrTabs">
                  <button type="button" class="btn active" data-tab="dirawat">Sedang dirawat <span class="badge" data-n="dirawat">0</span></button>
                  <button type="button" class="btn" data-tab="pulang">Sudah pulang <span class="badge" data-n="pulang">0</span></button>
                </div>
                <div class="d-flex align-items-center gap-1 d-none" id="rrRentang">
                  <input type="date" id="fromDate" class="form-control form-control-sm" style="width:140px" aria-label="Masuk dari">
                  <span class="text-muted small">–</span>
                  <input type="date" id="toDate" class="form-control form-control-sm" style="width:140px" aria-label="Masuk sampai">
                </div>
                <select id="doctorSelect" class="form-select form-select-sm" style="width:auto;max-width:210px" aria-label="Dokter"><option value="">Semua dokter</option></select>
                <select id="providerSelect" class="form-select form-select-sm" style="width:auto;max-width:180px" aria-label="Penjamin"><option value="">Semua penjamin</option></select>
                <button type="button" id="btnReset" class="btn btn-sm btn-light" title="Reset filter"><i class="fas fa-undo"></i></button>
                <div class="position-relative ms-lg-auto" style="min-width:220px">
                  <i class="fas fa-search position-absolute text-muted" style="left:10px;top:50%;transform:translateY(-50%);font-size:.75rem"></i>
                  <input type="search" id="rrCari" class="form-control form-control-sm ps-4" placeholder="Cari nama, RM, no. BPJS, ruang…">
                </div>
              </div>
              <div class="table-responsive">
                <table class="table align-middle mb-0 w-100" id="rrTable">
                  <thead>
                    <tr>
                      <th class="text-dark fw-normal text-center" style="width:52px">Aksi</th>
                      <th class="text-dark fw-normal">Pasien</th>
                      <th class="text-dark fw-normal">Ruang</th>
                      <th class="text-dark fw-normal">Masuk</th>
                      <th class="text-dark fw-normal">Dokter / diagnosa</th>
                      <th class="text-dark fw-normal">Penjamin</th>
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

  <!-- MODAL DETAIL -->
  <div class="modal fade" id="rrDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <div class="min-w-0"><h5 class="modal-title mb-0 text-truncate" id="rdNama">Hasil pemeriksaan</h5><div class="small text-muted" id="rdSub">&nbsp;</div></div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body" id="rdBody"></div>
        <div class="modal-footer py-2">
          <a class="btn btn-outline-primary btn-sm me-auto" id="rdRme" href="#"><i class="fas fa-notes-medical me-1"></i>Buka RME rawat inap</a>
          <button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal">Tutup</button>
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
      const tglJkt = h => new Date(Date.now() + (h || 0) * 86400000).toLocaleDateString('sv-SE', { timeZone: 'Asia/Jakarta' });
      const tglIndo = s => { const m = String(s || '').match(/^(\d{4})-(\d{2})-(\d{2})/); return m ? (+m[3]) + ' ' + BLN[m[2] - 1] + ' ' + m[1] : '-'; };
      const tglPendek = s => { const m = String(s || '').match(/^(\d{4})-(\d{2})-(\d{2})/); return m ? (+m[3]) + ' ' + BLN[m[2] - 1] + (m[1] !== tglJkt(0).slice(0, 4) ? ' ' + m[1].slice(2) : '') : '-'; };
      const isi = v => v !== null && v !== undefined && /[0-9A-Za-z]/.test(String(v));
      const LAMA_CURIGA = 14;
      let tab = 'dirawat', xhr = null;

      const OPSI = <?= json_encode($rrOpsi, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
      $id('doctorSelect').innerHTML += (OPSI.dokter || []).map(n => `<option value="${esc(n)}">${esc(n)}</option>`).join('');
      $id('providerSelect').innerHTML += (OPSI.penjamin || []).map(p => `<option value="${esc(p.id_provider)}">${esc(p.provider_name)}</option>`).join('');
      // dulu toISOString() (UTC) -> tanggal salah 00.00-07.00 WIB
      $id('fromDate').value = tglJkt(-29);
      $id('toDate').value = tglJkt(0);

      function umur(lahir) { const m = String(lahir || '').match(/^(\d{4})-(\d{2})-(\d{2})/); if (!m || m[1] < '1900') return ''; const d = new Date(), b = new Date(+m[1], m[2] - 1, +m[3]); let u = d.getFullYear() - b.getFullYear(); if (d < new Date(d.getFullYear(), b.getMonth(), b.getDate())) u--; return u >= 0 ? u + ' th' : ''; }
      function aksiHtml(r) {
        const q = 'no=' + encodeURIComponent(r.visit_ID) + '&rm=' + encodeURIComponent(r.nomor_rm || '');
        return `<div class="dropdown">
          <button class="btn btn-sm btn-light border" type="button" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}' aria-label="Aksi"><i class="fas fa-ellipsis-h"></i></button>
          <ul class="dropdown-menu shadow">
            <li><a class="dropdown-item rr-detail" href="javascript:;" data-id="${r.id_visit}"><i class="fas fa-eye me-2 text-info"></i>Hasil pemeriksaan</a></li>
            <li><a class="dropdown-item" href="module/admin/rme_inap?${q}&rme=c"><i class="fas fa-notes-medical me-2 text-primary"></i>RME rawat inap</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="module/admin/form_upload_klaim?${q}"><i class="fas fa-file-arrow-up me-2 text-info"></i>Upload SEP &amp; FKPP</a></li>
            <li><a class="dropdown-item" href="module/admin/form_pernyataan?${q}"><i class="fas fa-file-lines me-2 text-secondary"></i>Surat pernyataan pasien</a></li>
            <li><a class="dropdown-item" href="module/admin/form_persetujuan?${q}"><i class="fas fa-file-signature me-2 text-danger"></i>Surat persetujuan opname</a></li>
            ${r.status_ranap !== 'pulang' ? `<li><hr class="dropdown-divider"></li><li><a class="dropdown-item" href="module/admin/status_pulang"><i class="fas fa-bed me-2 text-success"></i>Pindah bed / pulangkan</a></li>` : ''}
          </ul></div>`;
      }

      const table = $('#rrTable').DataTable({
        processing: true, serverSide: false, deferRender: true, autoWidth: false, order: [], pageLength: 25, lengthMenu: [10, 25, 50, 100],
        dom: 't<"d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2 small"lip>',
        language: { processing: '<div class="spinner-border spinner-border-sm text-primary"></div> Memuat…', emptyTable: 'Tidak ada pasien.', zeroRecords: 'Tidak ada data yang cocok.',
          info: '_START_–_END_ dari _TOTAL_ pasien', infoEmpty: '0 pasien', infoFiltered: '(disaring dari _MAX_)', lengthMenu: 'Tampil _MENU_', thousands: '.', paginate: { previous: '‹', next: '›' } },
        ajax: function(_d, cb) {
          if (xhr && xhr.readyState !== 4) xhr.abort();
          xhr = $.ajax({ url: 'controller/visit/registrasiRanapController', dataType: 'json', cache: false,
            data: { v: 2, tab, fromDate: $id('fromDate').value, toDate: $id('toDate').value, doctor: $id('doctorSelect').value, provider: $id('providerSelect').value } })
            .done(j => {
              if (!j || j.status !== 'success') { cb({ data: [] }); Swal.fire('Gagal memuat data', (j && j.message) || '', 'error'); return; }
              ringkas(j);
              cb({ data: j.data });
            })
            .fail((x, s) => { if (s === 'abort') return; cb({ data: [] }); Swal.fire('Gagal memuat data', x.status === 401 ? 'Sesi berakhir, silakan login kembali.' : 'Periksa koneksi lalu coba lagi.', 'error'); });
        },
        columns: [
          { data: null, orderable: false, searchable: false, className: 'text-center', render: (v, t, r) => t === 'display' ? aksiHtml(r) : '' },
          { data: 'patient_name', render: (v, t, r) => {
              const nama = v || r.patient_name_pcare || '-';
              if (t !== 'display') return [nama, r.nomor_rm, r.patient_bpjs, r.room_name, r.bed_name].join(' ');
              const jk = String(r.patient_gender || '').charAt(0).toUpperCase(), u = umur(r.patient_datebirth);
              return `<div class="fw-semibold rr-nama" title="${esc(nama)}">${esc(nama)}</div><div class="small text-muted"><span class="rr-rm">${esc(r.nomor_rm || '-')}</span>${jk ? ' · ' + (jk === 'L' ? 'L' : 'P') : ''}${u ? ' · ' + u : ''}${r.patient_bpjs ? ' · <span class="font-monospace">' + esc(r.patient_bpjs) + '</span>' : ''}</div>`;
            } },
          { data: 'room_name', render: (v, t, r) => t === 'display' ? (isi(v) || isi(r.bed_name) ? `<div class="small fw-semibold">${esc(v || '-')}</div><div class="small text-muted">${isi(r.bed_name) ? 'Bed ' + esc(r.bed_name) : 'Bed -'}${isi(r.service_class) && r.service_class !== v ? ' · ' + esc(r.service_class) : ''}</div>` : '<span class="small text-muted">Belum ada bed</span>') : (v || '') },
          { data: 'visit_date', render: (v, t, r) => {
              if (t !== 'display') return v;
              const lama = r.lama_hari, curiga = r.status_ranap !== 'pulang' && lama > LAMA_CURIGA;
              if (r.status_ranap === 'pulang') return `<div class="small">${esc(tglPendek(v))} · ${esc(String(r.visit_time || '').slice(0, 5))}</div><span class="badge bg-success-subtle text-success">${isi(r.cara_keluar) ? esc(r.cara_keluar) : 'Pulang'}</span>`;
              return `<div class="small">${esc(tglPendek(v))} · ${esc(String(r.visit_time || '').slice(0, 5))}</div>` +
                (r.status_ranap !== 'pulang' && lama ? `<div class="small ${curiga ? 'text-danger' : 'text-muted'}"><span class="rr-lama">${lama}</span> hari${curiga ? ' <i class="fas fa-triangle-exclamation" title="Lebih dari ' + LAMA_CURIGA + ' hari — cek apakah sudah dipulangkan"></i>' : ''}</div>` : '');
            } },
          { data: 'id_doctor', render: (v, t, r) => t === 'display' ? `<div class="small fw-semibold">${esc(v || '-')}</div>${isi(r.diagnosa_awal) ? `<div class="small text-muted text-truncate" style="max-width:220px" title="${esc(r.diagnosa_awal)}" style="max-width:200px">${esc(r.diagnosa_awal)}</div>` : ''}` : (v || '') + ' ' + (r.diagnosa_awal || '') },
          { data: 'provider_name', render: (v, t) => t === 'display' ? `<span class="small">${esc(v || '-')}</span>` : (v || '') }
        ]
      });

      function ringkas(j) {
        const h = j.hitung || {}, b = j.bed || {}, rows = j.data || [];
        document.querySelectorAll('#rrTabs [data-n]').forEach(el => el.textContent = fmt(h[el.dataset.n]));
        $id('kDirawat').textContent = fmt(h.dirawat);
        $id('kMasuk').textContent = fmt(h.masuk_hari_ini);
        $id('kPulang').textContent = fmt(h.pulang);
        $id('kPeriode').textContent = tglIndo($id('fromDate').value) + ' – ' + tglIndo($id('toDate').value);
        $id('kBed').textContent = b.total ? fmt(b.terisi) + ' / ' + fmt(b.total) : '-';
        $id('kBedBar').style.width = (b.total ? b.terisi / b.total * 100 : 0) + '%';
        const selisih = (b.terisi || 0) - (h.dirawat || 0);
        $id('kBedInfo').innerHTML = b.total ? (selisih > 0 ? `<span class="text-warning-emphasis">${fmt(selisih)} bed tercatat terisi tanpa pasien dirawat</span>` : fmt(b.total - b.terisi) + ' bed kosong') : '&nbsp;';
        if (j.tab === 'dirawat') {
          const lama = rows.filter(r => r.lama_hari > LAMA_CURIGA).length;
          $id('kLama').innerHTML = lama ? `<span class="text-danger">${fmt(lama)} pasien &gt; ${LAMA_CURIGA} hari</span>` : '&nbsp;';
          $id('kAlos').textContent = fmt(lama);
          $id('kAlos').classList.toggle('text-danger', lama > 0);
          $id('rrPeringatan').classList.toggle('d-none', !lama && selisih <= 0);
          $id('rrPeringatan').innerHTML = `<i class="fas fa-triangle-exclamation me-1"></i>` + [
            lama ? `<b>${fmt(lama)} pasien</b> tercatat dirawat lebih dari ${LAMA_CURIGA} hari — kemungkinan sudah pulang tetapi belum dipulangkan di sistem.` : '',
            selisih > 0 ? `<b>${fmt(selisih)} bed</b> tercatat terisi padahal tidak ada pasien dirawat di sana.` : ''
          ].filter(Boolean).join(' ') + ' Perbarui di <a href="module/admin/status_pulang">Bed &amp; status pulang</a>.';
        }
      }

      const muat = () => table.ajax.reload(null, false);
      $id('rrTabs').addEventListener('click', e => {
        const b = e.target.closest('.btn[data-tab]');
        if (!b) return;
        tab = b.dataset.tab;
        document.querySelectorAll('#rrTabs .btn').forEach(x => x.classList.toggle('active', x === b));
        // rentang tanggal hanya untuk pasien pulang; "sedang dirawat" selalu tampil semua
        $id('rrRentang').classList.toggle('d-none', tab !== 'pulang');
        muat();
      });
      ['fromDate', 'toDate', 'doctorSelect', 'providerSelect'].forEach(i => $id(i).addEventListener('change', () => {
        if ($id('fromDate').value > $id('toDate').value) $id('toDate').value = $id('fromDate').value;
        muat();
      }));
      $id('btnReset').addEventListener('click', () => {
        $id('fromDate').value = tglJkt(-29); $id('toDate').value = tglJkt(0);
        $id('doctorSelect').value = $id('providerSelect').value = $id('rrCari').value = '';
        table.search('');
        muat();
      });
      $id('rrRefresh').addEventListener('click', muat);
      let tCari;
      $id('rrCari').addEventListener('input', function() { clearTimeout(tCari); const v = this.value; tCari = setTimeout(() => table.search(v).draw(), 200); });
      $('#rrTable').on('show.bs.dropdown', e => $(e.target).closest('td').addClass('rr-dd-buka')).on('hidden.bs.dropdown', e => $(e.target).closest('td').removeClass('rr-dd-buka'));

      /* ---------- detail ---------- */
      const modal = bootstrap.Modal.getOrCreateInstance($id('rrDetail'));
      let aktif = null;
      $(document).on('click', '.rr-detail', function() {
        const id = +this.dataset.id, r = table.rows().data().toArray().find(x => x.id_visit === id) || {};
        aktif = id;
        $id('rdNama').textContent = r.patient_name || r.patient_name_pcare || 'Hasil pemeriksaan';
        $id('rdSub').innerHTML = [r.nomor_rm ? `<span class="rr-rm">${esc(r.nomor_rm)}</span>` : '', esc(r.room_name || ''), r.bed_name ? 'Bed ' + esc(r.bed_name) : '', 'masuk ' + esc(tglIndo(r.visit_date))].filter(Boolean).join(' · ');
        $id('rdRme').href = 'module/admin/rme_inap?no=' + encodeURIComponent(r.visit_ID || '') + '&rm=' + encodeURIComponent(r.nomor_rm || '') + '&rme=c';
        $id('rdBody').innerHTML = '<div class="rr-sk mb-3" style="height:70px"></div><div class="rr-sk mb-3" style="height:70px"></div><div class="rr-sk" style="height:110px"></div>';
        modal.show();
        $.getJSON('controller/visit/getDetailPemeriksaan', { id }).done(res => {
          if (aktif !== id) return;
          if (!res || res.status !== true) { $id('rdBody').innerHTML = `<div class="text-center text-danger py-5">${esc((res && res.message) || 'Data tidak ditemukan.')}</div>`; return; }
          const d = res.data;
          const td = isi(d.tekanan_darah) ? d.tekanan_darah : (isi(d.sistole) && isi(d.diastole) ? d.sistole + '/' + d.diastole : '');
          const V = [['TD', td, 'mmHg'], ['Nadi', d.nadi, 'x/mnt'], ['Suhu', d.suhu, '°C'], ['RR', d.respirasi, 'x/mnt'], ['SpO₂', d.saturasi, '%'], ['TB', d.tinggi_badan, 'cm'], ['BB', d.berat_badan, 'kg'], ['BMI', d.bmi, d.bmi_keterangan || '']];
          const sec = (judul, html) => `<div class="rr-sec"><div class="rr-sec-h">${judul}</div>${html}</div>`;
          const diag = [[d.kdDiag1 || d.diagnosa, d.nmDiag1 || (d.diagnosa_keterangan_ind !== 'ICD10_2010' ? d.diagnosa_keterangan_ind : '') || d.diagnosa_keterangan], [d.kdDiag2, d.nmDiag2], [d.kdDiag3, d.nmDiag3]].filter(x => isi(x[0]) || isi(x[1]));
          $id('rdBody').innerHTML =
            sec('<i class="fas fa-heart-pulse text-danger me-2"></i>Vital sign', '<div class="row g-2">' + V.map(v => `<div class="col-3"><div class="rr-vital ${isi(v[1]) ? '' : 'kosong'}"><div class="rr-vital-l">${v[0]}</div><div class="rr-vital-v">${isi(v[1]) ? esc(v[1]) : '—'} <small class="text-muted fw-normal">${isi(v[1]) ? esc(v[2]) : ''}</small></div></div></div>`).join('') + '</div>') +
            sec('<i class="fas fa-comment-medical text-primary me-2"></i>Anamnesa', isi(d.anamnesa) ? esc(d.anamnesa).replace(/\n/g, '<br>') : '<span class="text-muted fst-italic small">Belum diisi</span>') +
            sec('<i class="fas fa-file-medical text-success me-2"></i>Diagnosa', diag.length ? diag.map((x, i) => `<div class="mb-1"><span class="badge bg-success-subtle text-success me-1">${esc(x[0] || '')}</span>${esc(x[1] || '')}${i ? ' <span class="text-muted small">(sekunder)</span>' : ''}</div>`).join('') : '<span class="text-muted fst-italic small">Belum diisi</span>') +
            sec('<i class="fas fa-syringe text-warning me-2"></i>Tindakan', isi(d.tindakan_text) ? esc(d.tindakan_text) : '<span class="text-muted fst-italic small">Tidak ada</span>') +
            sec('<i class="fas fa-pills text-primary me-2"></i>Obat', isi(d.obat_text) ? esc(d.obat_text) : '<span class="text-muted fst-italic small">Tidak ada</span>');
        }).fail(() => { if (aktif === id) $id('rdBody').innerHTML = '<div class="text-center text-danger py-5">Gagal memuat data.</div>'; });
      });
    })();
  </script>
</body>

</html>
