<?php
/**
 * Pemeriksaan pasien rawat inap (dokter).
 * Data: controller/doctor/registrasiInpatientController (GET; tab & hitungan dari server).
 */
$title = 'Pemeriksaan Rawat Inap';
require '../../controller/view.php';
session_write_close();
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    @keyframes ri-spin { to { transform: rotate(360deg); } }
    .icon-spin { display: inline-block; animation: ri-spin 1s linear infinite; }

    .ri-icon { width: 40px; height: 40px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
    .ri-kpi { border: 1px solid #e5eaef; border-radius: 12px; background: #fff; padding: 12px 14px; display: flex; align-items: center; gap: 12px; height: 100%; }
    .ri-kpi .n { font-size: 1.35rem; font-weight: 700; line-height: 1.1; color: #111827; }
    .ri-kpi .l { font-size: .78rem; color: #475569; }
    .ri-kpi.klik { cursor: pointer; transition: border-color .15s, box-shadow .15s; }
    .ri-kpi.klik:hover { border-color: #5d87ff; box-shadow: 0 4px 14px rgba(93, 135, 255, .12); }

    .ri-tabs .nav-link { color: #475569; font-weight: 600; border: 0; border-bottom: 2px solid transparent; border-radius: 0; padding: .55rem .9rem; }
    .ri-tabs .nav-link.active { color: #5d87ff; border-bottom-color: #5d87ff; background: transparent; }
    .ri-tabs .badge { font-size: .7rem; }

    .ri-filter .form-label { font-size: .75rem; color: #475569; margin-bottom: 2px; }
    .ri-filter .form-control, .ri-filter .form-select { font-size: .85rem; }
    .ri-chip { border: 1px solid #e5eaef; background: #fff; color: #334155; border-radius: 999px; padding: 3px 12px; font-size: .78rem; font-weight: 600; }
    .ri-chip.active { background: #5d87ff; border-color: #5d87ff; color: #fff; }
    .ri-chip .n { opacity: .8; margin-left: 4px; }

    #tblInap { font-size: .86rem; }
    #tblInap thead th { font-size: .75rem; text-transform: uppercase; letter-spacing: .03em; color: #475569; font-weight: 600; white-space: nowrap; background: #f8fafc; }
    #tblInap td, #tblInap th { padding-left: .55rem; padding-right: .55rem; }
    #tblInap td { vertical-align: middle; white-space: nowrap; padding-top: .55rem; padding-bottom: .55rem; }
    .ri-nama { font-weight: 600; color: #111827; min-width: 180px; max-width: 280px; white-space: normal; line-height: 1.3; }
    .ri-sub { font-size: .76rem; color: #475569; }
    .ri-sub .sep { color: #cbd5e1; margin: 0 4px; }
    .ri-wrap { min-width: 130px; max-width: 220px; white-space: normal; line-height: 1.3; }
    .ri-tebal { font-weight: 600; color: #111827; }
    .ri-status { min-width: 130px; white-space: normal; line-height: 1.3; }
    .ri-hari { display: inline-block; font-size: .72rem; font-weight: 700; border-radius: 6px; padding: 1px 6px; background: #eef2ff; color: #3730a3; }
    .ri-hari.lama { background: #fee2e2; color: #991b1b; }
    .ri-aksi { display: flex; gap: 6px; flex-wrap: nowrap; }
    .ri-aksi .btn { white-space: nowrap; }
    /* warna aksi kontras (secondary di tema ini cyan muda -> tidak terbaca di latar putih) */
    .ri-aksi .ri-b { min-width: 32px; border: 1px solid transparent; font-weight: 600; }
    .ri-aksi .ri-b i { font-size: 1rem; }
    .ri-b-salin { background: #f5f3ff; border-color: #7c3aed !important; color: #5b21b6; }
    .ri-b-salin:hover { background: #7c3aed; color: #fff; }
    .ri-b-klaim { background: #334155; color: #fff; }
    .ri-b-klaim:hover { background: #1e293b; color: #fff; }
    .ri-b-unduh { background: #15803d; color: #fff; }
    .ri-b-unduh:hover { background: #166534; color: #fff; }
    .badge.bg-warning, .badge.bg-warning-subtle { color: #1f2937 !important; }
    .badge.bg-warning-subtle { background-color: #fef3c7 !important; }
    .ri-kosong { padding: 36px 12px; text-align: center; color: #475569; }
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

          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-3">
              <div class="d-flex flex-wrap align-items-center gap-3">
                <span class="ri-icon bg-primary-subtle text-primary"><i class="ti ti-bed"></i></span>
                <div class="flex-grow-1" style="min-width:0">
                  <h5 class="fw-semibold mb-0">Pemeriksaan Pasien Rawat Inap</h5>
                  <div class="text-muted small"><span id="riKet">Semua pasien rawat inap</span> · <span id="riUpdate">memuat…</span></div>
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm" id="btnMuatUlang"><i class="ti ti-refresh me-1"></i>Muat ulang</button>
              </div>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-6 col-xl-3">
              <div class="ri-kpi klik" data-tab="belum" title="Lihat pasien belum ada CPPT">
                <span class="ri-icon bg-danger-subtle text-danger"><i class="ti ti-hourglass"></i></span>
                <div><div class="n" id="kBelum">-</div><div class="l">Belum ada CPPT</div></div>
              </div>
            </div>
            <div class="col-6 col-xl-3">
              <div class="ri-kpi klik" data-tab="selesai" title="Lihat pasien yang sudah ada CPPT">
                <span class="ri-icon bg-primary-subtle text-primary"><i class="ti ti-notes"></i></span>
                <div><div class="n" id="kSelesai">-</div><div class="l">Sudah ada CPPT, belum pulang</div></div>
              </div>
            </div>
            <div class="col-6 col-xl-3">
              <div class="ri-kpi klik" data-tab="pulang" title="Lihat pasien pulang">
                <span class="ri-icon bg-success-subtle text-success"><i class="ti ti-home-check"></i></span>
                <div><div class="n" id="kPulang">-</div><div class="l" id="kPulangL">Pulang (31 hari terakhir)</div></div>
              </div>
            </div>
            <div class="col-6 col-xl-3">
              <div class="ri-kpi klik" id="kpiResume" title="Pasien berstatus pulang di rawat inap, tetapi resume medis belum diisi">
                <span class="ri-icon bg-warning-subtle" style="color:#b45309"><i class="ti ti-file-alert"></i></span>
                <div><div class="n" id="kResume">-</div><div class="l">Pulang, resume belum diisi</div></div>
              </div>
            </div>
          </div>

          <div class="card border-0 shadow-sm">
            <div class="card-body p-3 p-md-4">
              <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-2">
                <ul class="nav ri-tabs" id="riTabs">
                  <li class="nav-item"><button class="nav-link active" type="button" data-tab="belum">Belum dilayani <span class="badge rounded-pill bg-danger ms-1" id="tBelum">0</span></button></li>
                  <li class="nav-item"><button class="nav-link" type="button" data-tab="selesai">Sudah dilayani <span class="badge rounded-pill bg-primary ms-1" id="tSelesai">0</span></button></li>
                  <li class="nav-item"><button class="nav-link" type="button" data-tab="pulang">Pulang <span class="badge rounded-pill bg-success ms-1" id="tPulang">0</span></button></li>
                </ul>
                <form class="ri-filter d-flex flex-wrap align-items-end gap-2" onsubmit="return false">
                  <div>
                    <label for="riCari" class="form-label">Cari</label>
                    <input type="search" id="riCari" class="form-control form-control-sm" placeholder="Nama / RM / BPJS / ruang / bayar" style="width:210px">
                  </div>
                  <div>
                    <label for="fromDate" class="form-label">Masuk dari</label>
                    <input type="date" id="fromDate" class="form-control form-control-sm">
                  </div>
                  <div>
                    <label for="toDate" class="form-label">Sampai</label>
                    <input type="date" id="toDate" class="form-control form-control-sm">
                  </div>
                  <button type="button" id="btnFilter" class="btn btn-dark btn-sm"><i class="ti ti-filter me-1"></i>Terapkan</button>
                  <button type="button" id="btnReset" class="btn btn-light btn-sm" title="Hapus filter tanggal"><i class="ti ti-arrow-back-up"></i></button>
                </form>
              </div>
              <div id="riChipsWrap"><div class="d-flex flex-wrap gap-2 mb-3" id="riChips">
                <button type="button" class="ri-chip active" data-f="semua">Semua<span class="n" id="cSemua"></span></button>
                <button type="button" class="ri-chip" data-f="dirawat">Masih dirawat<span class="n" id="cDirawat"></span></button>
                <button type="button" class="ri-chip" data-f="resume">Pulang, resume belum diisi<span class="n" id="cResume"></span></button>
              </div></div>

              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 w-100" id="tblInap">
                  <thead>
                    <tr>
                      <th style="width:1%">Aksi</th>
                      <th>Pasien</th>
                      <th>Masuk</th>
                      <th>Ruang</th>
                      <th>DPJP</th>
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
  <script>
    (function() {
      const API = 'controller/doctor/registrasiInpatientController';
      const HARI_INI = new Date().toLocaleDateString('sv-SE', { timeZone: 'Asia/Jakarta' });
      const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      let tab = 'belum';
      let saring = 'semua';
      let sibuk = false;

      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const tglID = d => { if (!d) return '-'; const [y, m, h] = String(d).slice(0, 10).split('-'); return h ? `${+h} ${BULAN[+m - 1]} ${y}` : esc(d); };
      const jam = t => t ? String(t).slice(0, 5) : '';
      const pulangTanpaResume = r => r.status_ranap === 'pulang' && !r.tanggal_pulang;
      const q = r => `no=${encodeURIComponent(r.visit_ID)}&rm=${encodeURIComponent(r.nomor_rm || '')}&rme=c`;

      $('#fromDate, #toDate').attr('max', HARI_INI);

      // saring cepat (chip) di sisi klien
      $.fn.dataTable.ext.search.push((s, _d, i) => {
        if (s.nTable.id !== 'tblInap' || saring === 'semua') return true;
        const r = s.aoData[i]._aData;
        if (saring === 'dirawat') return r.status_ranap === 'aktif';
        if (saring === 'resume') return pulangTanpaResume(r);
        return true;
      });

      const table = $('#tblInap').DataTable({
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
          zeroRecords: '<div class="ri-kosong"><i class="ti ti-search fs-6 d-block mb-1"></i>Tidak ada pasien yang cocok.</div>',
          emptyTable: '<div class="ri-kosong"><i class="ti ti-mood-empty fs-6 d-block mb-1"></i>Tidak ada pasien pada tab ini.</div>',
          paginate: { previous: '‹', next: '›' },
        },
        columns: [
          { data: null, orderable: false, render: (r, t) => t === 'display' ? tombol(r) : '' },
          {
            data: null,
            render: (r, t) => {
              if (t !== 'display') return `${r.patient_name || ''} ${r.nomor_rm || ''} ${r.patient_bpjs || ''} ${r.noKartu || ''} ${r.provider_name || ''}`;
              const umur = r.umur !== null && r.umur !== undefined ? `${r.umur} th` : '';
              const info = [r.nomor_rm ? 'RM ' + esc(r.nomor_rm) : '', [esc(r.patient_gender || ''), umur].filter(Boolean).join(', ')].filter(Boolean).join('<span class="sep">|</span>');
              const kartu = r.patient_bpjs || r.noKartu;
              const isBpjs = String(r.id_provider) === '1';
              const bayar = `<span class="badge ${isBpjs ? 'bg-success-subtle text-success' : 'bg-light text-dark border'} me-1" title="${esc(r.provider_name || '')}">${esc(isBpjs ? 'BPJS' : (r.provider_name || '-'))}</span>`;
              return `<div class="ri-nama">${esc(r.patient_name || '(tanpa nama)')}</div><div class="ri-sub">${info || '-'}</div>` +
                `<div class="ri-sub mt-1">${bayar}${kartu ? 'No. ' + esc(kartu) : ''}</div>`;
            }
          },
          {
            data: null,
            render: (r, t) => {
              if (t !== 'display') return `${r.tanggal_masuk || ''} ${r.ranap_time || ''}`;
              const h = r.hari_rawat;
              const lbl = h ? `<span class="ri-hari ${h > 7 && !r.tanggal_pulang ? 'lama' : ''}" title="Lama rawat">${r.tanggal_pulang ? h + ' hari' : 'Hari ke-' + h}</span>` : '';
              return `<div class="ri-tebal">${tglID(r.tanggal_masuk)}</div><div class="ri-sub">${esc(jam(r.ranap_time)) || '-'} ${lbl}</div>`;
            }
          },
          {
            data: null,
            render: (r, t) => {
              const bed = r.bed_name && r.bed_name !== '-' ? r.bed_name : '';
              if (t !== 'display') return `${r.room_name || ''} ${bed}`;
              if (!r.room_name && !bed) return '<span class="ri-sub">-</span>';
              return `<div class="ri-wrap"><div>${esc(r.room_name || '-')}</div><div class="ri-sub">${bed ? 'Bed ' + esc(bed) : ''}${r.service_class && r.service_class !== r.room_name ? (bed ? ' · ' : '') + esc(r.service_class) : ''}</div></div>`;
            }
          },
          { data: null, render: (r, t) => t === 'display' ? `<div class="ri-wrap">${esc(r.dpjp || r.id_doctor || '-')}</div>` : (r.dpjp || r.id_doctor || '') },
          {
            data: null,
            render: (r, t) => {
              let b, sub = '';
              if (r.tanggal_pulang) {
                b = '<span class="badge bg-success-subtle text-success">Pulang</span>';
                sub = `${tglID(r.tanggal_pulang)} ${esc(jam(r.jam_pulang))}`;
              } else if (r.jumlah_cppt > 0) {
                b = '<span class="badge bg-primary-subtle text-primary">Sudah dilayani</span>';
              } else {
                b = '<span class="badge bg-danger-subtle text-danger">Belum dilayani</span>';
              }
              if (t !== 'display') return r.tanggal_pulang ? 'Pulang' : (r.jumlah_cppt > 0 ? 'Sudah dilayani' : 'Belum dilayani');
              if (r.jumlah_cppt > 0) sub += (sub ? ' · ' : '') + `${r.jumlah_cppt} CPPT` + (r.cppt_terakhir ? `, terakhir ${tglID(r.cppt_terakhir)}` : '');
              let x = b + (sub ? `<div class="ri-sub mt-1">${sub}</div>` : '');
              if (pulangTanpaResume(r)) x += '<div class="mt-1"><span class="badge bg-warning-subtle" title="Status rawat inap sudah pulang, tetapi resume medis / tanggal pulang belum diisi"><i class="ti ti-alert-triangle me-1"></i>Resume belum diisi</span></div>';
              return `<div class="ri-status">${x}</div>`;
            }
          },
        ],
      });

      function tombol(r) {
        let x = `<a class="btn btn-sm btn-primary" href="module/admin/rme_inap?${q(r)}" title="Pemeriksaan rawat inap (CPPT)"><i class="ti ti-stethoscope me-1"></i>${r.tanggal_pulang ? 'Buka RME' : 'Periksa'}</a>`;
        if (r.patient_gender === 'Perempuan') x += `<a class="btn btn-sm ri-b ri-b-salin" href="module/admin/rme_persalinan?${q(r)}" title="RME persalinan"><i class="ti ti-baby-carriage"></i></a>`;
        const klaim = `module/admin/print/bundle_klaim?id_patient=${encodeURIComponent(r.id_patient)}&${q(r)}`;
        x += `<a class="btn btn-sm ri-b ri-b-klaim" target="_blank" rel="noopener" href="${klaim}" title="Preview RME klaim"><i class="ti ti-file-search"></i></a>`;
        x += `<a class="btn btn-sm ri-b ri-b-unduh" href="${klaim}&download=1" title="Download PDF klaim"><i class="ti ti-download"></i></a>`;
        return `<div class="ri-aksi">${x}</div>`;
      }

      function hitungChip() {
        const d = table.rows().data().toArray();
        $('#cSemua').text(d.length);
        $('#cDirawat').text(d.filter(r => r.status_ranap === 'aktif').length);
        $('#cResume').text(d.filter(pulangTanpaResume).length);
      }
      let resume = { belum: 0, selesai: 0 };

      function muat(opsi = {}) {
        const d = $('#fromDate').val(), s = $('#toDate').val();
        if (d && s && s < d) { Swal.fire({ icon: 'warning', title: 'Tanggal tidak valid', text: '"Sampai" tidak boleh sebelum "Masuk dari".' }); return; }
        if (sibuk) return;
        sibuk = true;
        if (!opsi.diam) $('#btnMuatUlang i').attr('class', 'ti ti-loader-2 icon-spin me-1');
        $.ajax({ url: API, dataType: 'json', data: { tab, fromDate: d, toDate: s } })
          .done(res => {
            const data = res.data || [];
            // yang masih dirawat di atas (urutan masuk tetap)
            if (tab !== 'pulang') data.sort((a, b) => (b.status_ranap === 'aktif') - (a.status_ranap === 'aktif'));
            table.clear().rows.add(data).draw(false);
            const h = res.hitung || {};
            $('#kBelum, #tBelum').text(h.belum ?? 0);
            $('#kSelesai, #tSelesai').text(h.selesai ?? 0);
            $('#kPulang, #tPulang').text(h.pulang ?? 0);
            const filterTgl = d || s;
            $('#kPulangL').text(filterTgl ? 'Pulang (sesuai filter)' : 'Pulang (31 hari terakhir)');
            resume = { belum: h.resume_belum || 0, selesai: h.resume_selesai || 0 };
            $('#kResume').text(resume.belum + resume.selesai);
            hitungChip();
            $('#riKet').text(filterTgl ? `Masuk ${d ? tglID(d) : '…'} – ${s ? tglID(s) : '…'}` : (tab === 'pulang' ? 'Pulang: masuk 31 hari terakhir' : 'Semua pasien rawat inap'));
            $('#riUpdate').text('diperbarui ' + new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }));
            $('#riChipsWrap').toggle(tab !== 'pulang');
          })
          .fail(x => {
            if (opsi.diam) { $('#riUpdate').text('gagal memperbarui'); return; }
            Swal.fire({ icon: 'error', title: 'Gagal memuat', text: x.responseJSON?.message || 'Server tidak merespon.' });
          })
          .always(() => { sibuk = false; $('#btnMuatUlang i').attr('class', 'ti ti-refresh me-1'); });
      }

      function gantiTab(t, chip) {
        saring = chip || 'semua';
        $('#riChips .ri-chip').removeClass('active').filter(`[data-f="${saring}"]`).addClass('active');
        if (t === tab) { table.draw(); return; }
        tab = t;
        $('#riTabs .nav-link').removeClass('active').filter(`[data-tab="${t}"]`).addClass('active');
        muat();
      }

      $('#riTabs').on('click', '.nav-link', function() { gantiTab(this.dataset.tab); });
      $('.ri-kpi.klik[data-tab]').on('click', function() { gantiTab(this.dataset.tab); });
      // KPI resume: buka tab yang punya kasus (belum dulu), saring "resume"
      $('#kpiResume').on('click', () => gantiTab(tab !== 'pulang' && resume[tab] > 0 ? tab : (resume.belum > 0 || !resume.selesai ? 'belum' : 'selesai'), 'resume'));
      $('#riChips').on('click', '.ri-chip', function() {
        saring = this.dataset.f;
        $('#riChips .ri-chip').removeClass('active');
        $(this).addClass('active');
        table.draw();
      });
      $('#btnFilter').on('click', () => muat());
      $('#fromDate, #toDate').on('keydown', e => { if (e.key === 'Enter') muat(); });
      $('#btnMuatUlang').on('click', () => muat());
      $('#btnReset').on('click', () => { $('#fromDate, #toDate').val(''); $('#riCari').val(''); table.search(''); muat(); });
      let tCari;
      $('#riCari').on('input', function() { clearTimeout(tCari); tCari = setTimeout(() => table.search(this.value).draw(), 200); });

      setInterval(() => { if (!document.hidden && !Swal.isVisible()) muat({ diam: true }); }, 120000);
      document.addEventListener('visibilitychange', () => { if (!document.hidden) muat({ diam: true }); });

      muat();
    })();
  </script>
</body>

</html>
