<?php
/**
 * Permintaan laboratorium: daftar per kunjungan.
 * Data: controller/lab/permintaanLabList (GET per rentang tanggal); tab, status & filter lain di klien.
 */
$title = 'Permintaan Lab';
require '../../controller/view.php';
session_write_close();
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    @keyframes lb-spin { to { transform: rotate(360deg); } }
    .icon-spin { display: inline-block; animation: lb-spin 1s linear infinite; }
    .lb-icon { width: 40px; height: 40px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
    .lb-kpi { border: 1px solid #e5eaef; border-radius: 12px; background: #fff; padding: 12px 14px; display: flex; align-items: center; gap: 12px; height: 100%; cursor: pointer; transition: border-color .15s, box-shadow .15s; }
    .lb-kpi:hover { border-color: #5d87ff; box-shadow: 0 4px 14px rgba(93, 135, 255, .12); }
    .lb-kpi .n { font-size: 1.35rem; font-weight: 700; line-height: 1.1; color: #111827; }
    .lb-kpi .l { font-size: .78rem; color: #475569; }

    .lb-tabs .nav-link { color: #475569; font-weight: 600; border: 0; border-bottom: 2px solid transparent; border-radius: 0; padding: .55rem .9rem; }
    .lb-tabs .nav-link.active { color: #5d87ff; border-bottom-color: #5d87ff; background: transparent; }
    .lb-tabs .badge { font-size: .7rem; }
    .lb-filter .form-label { font-size: .75rem; color: #475569; margin-bottom: 2px; }
    .lb-filter .form-control, .lb-filter .form-select { font-size: .85rem; }
    .lb-chip { border: 1px solid #e5eaef; background: #fff; color: #334155; border-radius: 999px; padding: 3px 12px; font-size: .78rem; font-weight: 600; }
    .lb-chip.active { background: #5d87ff; border-color: #5d87ff; color: #fff; }
    .lb-chip .n { opacity: .8; margin-left: 4px; }
    .lb-cepat .btn { font-size: .78rem; padding: 4px 10px; background: #fff; border: 1px solid #cbd5e1; color: #1f2937; font-weight: 600; }
    .lb-cepat .btn:hover { background: #f1f5f9; color: #111827; }
    .lb-cepat .btn.active, .lb-cepat .btn.active:hover { background: #1e293b; border-color: #1e293b; color: #fff; }

    #tblLab { font-size: .86rem; }
    #tblLab thead th { font-size: .75rem; text-transform: uppercase; letter-spacing: .03em; color: #475569; font-weight: 600; white-space: nowrap; background: #f8fafc; }
    #tblLab td, #tblLab th { padding-left: .55rem; padding-right: .55rem; }
    #tblLab td { vertical-align: middle; white-space: nowrap; padding-top: .55rem; padding-bottom: .55rem; }
    .lb-nama { font-weight: 600; color: #111827; min-width: 170px; max-width: 260px; white-space: normal; line-height: 1.3; }
    .lb-sub { font-size: .76rem; color: #475569; }
    .lb-sub .sep { color: #cbd5e1; margin: 0 4px; }
    .lb-wrap { min-width: 130px; max-width: 210px; white-space: normal; line-height: 1.3; }
    .lb-tebal { font-weight: 600; color: #111827; }
    .lb-tunggu { display: inline-block; font-size: .72rem; font-weight: 700; border-radius: 6px; padding: 1px 6px; background: #eef2ff; color: #3730a3; }
    .lb-tunggu.lama { background: #fee2e2; color: #991b1b; }
    .lb-periksa { display: flex; flex-wrap: wrap; gap: 4px; min-width: 180px; max-width: 320px; white-space: normal; }
    .lb-p { display: inline-flex; align-items: center; gap: 4px; font-size: .74rem; font-weight: 600; border-radius: 6px; padding: 2px 7px; border: 1px solid; }
    .lb-p.belum { background: #fff; border-color: #fca5a5; color: #991b1b; }
    .lb-p.sebagian { background: #fffbeb; border-color: #fcd34d; color: #78350f; }
    .lb-p.lengkap { background: #f0fdf4; border-color: #86efac; color: #166534; }
    .badge.bg-warning-subtle { color: #1f2937 !important; background-color: #fef3c7 !important; }
    .lb-kosong { padding: 36px 12px; text-align: center; color: #475569; }
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
                <span class="lb-icon bg-primary-subtle text-primary"><i class="ti ti-microscope"></i></span>
                <div class="flex-grow-1" style="min-width:0">
                  <h5 class="fw-semibold mb-0">Permintaan Laboratorium</h5>
                  <div class="text-muted small"><span id="lbRentang">hari ini</span> · <span id="lbUpdate">memuat…</span></div>
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm" id="btnMuatUlang"><i class="ti ti-refresh me-1"></i>Muat ulang</button>
              </div>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-6 col-xl-3">
              <div class="lb-kpi" data-tab="permintaan" data-st="belum">
                <span class="lb-icon bg-danger-subtle text-danger"><i class="ti ti-hourglass"></i></span>
                <div><div class="n" id="kBelum">-</div><div class="l">Belum ada hasil</div></div>
              </div>
            </div>
            <div class="col-6 col-xl-3">
              <div class="lb-kpi" data-tab="permintaan" data-st="proses">
                <span class="lb-icon bg-warning-subtle" style="color:#b45309"><i class="ti ti-test-pipe"></i></span>
                <div><div class="n" id="kProses">-</div><div class="l">Sebagian pemeriksaan ada hasil</div></div>
              </div>
            </div>
            <div class="col-6 col-xl-3">
              <div class="lb-kpi" data-tab="selesai" data-st="semua">
                <span class="lb-icon bg-success-subtle text-success"><i class="ti ti-circle-check"></i></span>
                <div><div class="n" id="kSelesai">-</div><div class="l">Semua hasil selesai</div></div>
              </div>
            </div>
            <div class="col-6 col-xl-3">
              <div class="lb-kpi" data-tab="permintaan" data-st="tertunda" title="Belum lengkap dan diminta sebelum hari ini">
                <span class="lb-icon" style="background:#fee2e2;color:#991b1b"><i class="ti ti-alarm"></i></span>
                <div><div class="n" id="kTertunda">-</div><div class="l">Tertunda (lewat hari)</div></div>
              </div>
            </div>
          </div>

          <div class="card border-0 shadow-sm">
            <div class="card-body p-3 p-md-4">
              <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-2">
                <ul class="nav lb-tabs" id="lbTabs">
                  <li class="nav-item"><button class="nav-link active" type="button" data-tab="permintaan">Permintaan <span class="badge rounded-pill bg-danger ms-1" id="tPermintaan">0</span></button></li>
                  <li class="nav-item"><button class="nav-link" type="button" data-tab="selesai">Selesai <span class="badge rounded-pill bg-success ms-1" id="tSelesai">0</span></button></li>
                </ul>
                <div class="lb-filter d-flex flex-wrap align-items-end gap-2">
                  <div>
                    <label class="form-label d-block">Periode</label>
                    <div class="btn-group lb-cepat" id="lbCepat">
                      <button type="button" class="btn" data-r="hari">Hari ini</button>
                      <button type="button" class="btn" data-r="kemarin">Kemarin</button>
                      <button type="button" class="btn" data-r="7">7 hari</button>
                      <button type="button" class="btn" data-r="bulan">Bulan ini</button>
                    </div>
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
                </div>
              </div>

              <div class="lb-filter d-flex flex-wrap align-items-end gap-2 mb-3">
                <div>
                  <label for="lbCari" class="form-label">Cari</label>
                  <input type="search" id="lbCari" class="form-control form-control-sm" placeholder="Nama / No. RM / pemeriksaan" style="width:220px">
                </div>
                <div>
                  <label for="fPeriksa" class="form-label">Pemeriksaan</label>
                  <select id="fPeriksa" class="form-select form-select-sm" style="width:180px"><option value="">Semua</option></select>
                </div>
                <div>
                  <label for="fDokter" class="form-label">Dokter</label>
                  <select id="fDokter" class="form-select form-select-sm" style="width:200px"><option value="">Semua</option></select>
                </div>
                <div>
                  <label for="fPoli" class="form-label">Poli</label>
                  <select id="fPoli" class="form-select form-select-sm" style="width:160px"><option value="">Semua</option></select>
                </div>
                <div>
                  <label for="fBayar" class="form-label">Jenis bayar</label>
                  <select id="fBayar" class="form-select form-select-sm" style="width:150px"><option value="">Semua</option></select>
                </div>
                <button type="button" id="btnReset" class="btn btn-light btn-sm" title="Kembalikan semua filter"><i class="ti ti-arrow-back-up me-1"></i>Reset filter</button>
              </div>

              <div id="lbChipsWrap">
                <div class="d-flex flex-wrap gap-2 mb-3" id="lbChips">
                  <button type="button" class="lb-chip active" data-st="semua">Semua<span class="n" id="cSemua"></span></button>
                  <button type="button" class="lb-chip" data-st="belum">Belum ada hasil<span class="n" id="cBelum"></span></button>
                  <button type="button" class="lb-chip" data-st="proses">Sebagian ada hasil<span class="n" id="cProses"></span></button>
                  <button type="button" class="lb-chip" data-st="tertunda">Tertunda<span class="n" id="cTertunda"></span></button>
                </div>
              </div>

              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 w-100" id="tblLab">
                  <thead>
                    <tr>
                      <th style="width:1%">Aksi</th>
                      <th>Pasien</th>
                      <th>Diminta</th>
                      <th>Asal / Dokter</th>
                      <th>Pemeriksaan</th>
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
      const API = 'controller/lab/permintaanLabList';
      const KUNCI = 'permintaanLab.filter';
      const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      const HARI_INI = new Date().toLocaleDateString('sv-SE', { timeZone: 'Asia/Jakarta' });
      const geser = (ymd, n) => { const d = new Date(ymd + 'T12:00:00'); d.setDate(d.getDate() + n); return d.toISOString().slice(0, 10); };
      const STATUS = {
        belum: ['bg-danger-subtle text-danger', 'Belum ada hasil'],
        proses: ['bg-warning-subtle', 'Sebagian ada hasil'],
        selesai: ['bg-success-subtle text-success', 'Hasil selesai'],
      };
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const tglID = d => { if (!d) return '-'; const [y, m, h] = String(d).slice(0, 10).split('-'); return `${+h} ${BULAN[+m - 1]} ${y}`; };
      const jam = t => t ? String(t).slice(11, 16) : '';
      const menit = t => { const m = String(t || '').match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})/); if (!m) return null; const d = new Date(+m[1], m[2] - 1, +m[3], +m[4], +m[5]); return Math.max(0, Math.round((Date.now() - d) / 60000)); };
      const durasi = m => m < 60 ? `${m} mnt` : (m < 1440 ? `${Math.floor(m / 60)} jam ${m % 60} mnt` : `${Math.floor(m / 1440)} hari`);
      const tertunda = r => r.status !== 'selesai' && String(r.created_at).slice(0, 10) < HARI_INI;

      let f = { tab: 'permintaan', st: 'semua', dari: HARI_INI, sampai: HARI_INI, periksa: '', dokter: '', poli: '', bayar: '', cari: '' };
      try {
        const s = JSON.parse(localStorage.getItem(KUNCI) || 'null');
        if (s && typeof s === 'object') { Object.assign(f, s.f || {}); if (s.hari !== HARI_INI) { f.dari = HARI_INI; f.sampai = HARI_INI; } }
      } catch (e) {}
      const simpan = () => { try { localStorage.setItem(KUNCI, JSON.stringify({ hari: HARI_INI, f })); } catch (e) {} };

      let semua = [];
      let sibuk = false;
      $('#fromDate, #toDate').attr('max', HARI_INI);
      $('#fromDate').val(f.dari);
      $('#toDate').val(f.sampai);
      $('#lbCari').val(f.cari);

      $.fn.dataTable.ext.search.push((s, _d, i) => {
        if (s.nTable.id !== 'tblLab' || f.tab !== 'permintaan' || f.st === 'semua') return true;
        const r = s.aoData[i]._aData;
        return f.st === 'tertunda' ? tertunda(r) : r.status === f.st;
      });

      const table = $('#tblLab').DataTable({
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
          zeroRecords: '<div class="lb-kosong"><i class="ti ti-search fs-6 d-block mb-1"></i>Tidak ada permintaan yang cocok dengan filter.</div>',
          emptyTable: '<div class="lb-kosong"><i class="ti ti-mood-empty fs-6 d-block mb-1"></i>Tidak ada permintaan lab pada periode ini.</div>',
          paginate: { previous: '‹', next: '›' },
        },
        columns: [
          {
            data: null, orderable: false,
            render: (r, t) => t !== 'display' ? '' :
              `<a class="btn btn-sm ${r.status === 'selesai' ? 'btn-outline-primary' : 'btn-primary'}" href="module/lab/permintaan-lab-detail?no=${encodeURIComponent(r.visit_ID)}&rm=${encodeURIComponent(r.nomor_rm || '')}&id=${encodeURIComponent(r.id_inspection)}"><i class="ti ti-${r.status === 'selesai' ? 'file-text' : 'pencil'} me-1"></i>${r.status === 'selesai' ? 'Lihat hasil' : 'Input hasil'}</a>`
          },
          {
            data: null,
            render: (r, t) => {
              if (t !== 'display') return `${r.patient_name || ''} ${r.nomor_rm || ''}`;
              const umur = r.umur !== null && r.umur !== undefined ? `${r.umur} th` : '';
              const info = [r.nomor_rm ? 'RM ' + esc(r.nomor_rm) : '', [esc(r.patient_gender || ''), umur].filter(Boolean).join(', ')].filter(Boolean).join('<span class="sep">|</span>');
              const bpjs = String(r.id_provider) === '1';
              return `<div class="lb-nama">${esc(r.patient_name || '(tanpa nama)')}</div><div class="lb-sub">${info || '-'}</div>` +
                `<div class="lb-sub mt-1"><span class="badge ${bpjs ? 'bg-success-subtle text-success' : 'bg-light text-dark border'}" title="${esc(r.provider_name || '')}">${esc(bpjs ? 'BPJS' : (r.provider_name || '-'))}</span></div>`;
            }
          },
          {
            data: null,
            render: (r, t) => {
              if (t !== 'display') return r.created_at || '';
              const m = r.status === 'selesai' ? null : menit(r.created_at);
              const tunggu = m === null ? '' : `<div class="mt-1"><span class="lb-tunggu ${m > 60 ? 'lama' : ''}" title="Lama menunggu sejak diminta">${durasi(m)}</span></div>`;
              const tgl = String(r.created_at).slice(0, 10) === HARI_INI ? '' : `<div class="lb-sub">${tglID(r.created_at)}</div>`;
              return `<div class="lb-tebal">${esc(jam(r.created_at))}</div>${tgl}${tunggu}`;
            }
          },
          {
            data: null,
            render: (r, t) => t !== 'display' ? `${r.id_poli || ''} ${r.id_doctor || ''}` :
              `<div class="lb-wrap"><div>${esc(r.id_poli || r.source_hub || '-')}</div><div class="lb-sub">${esc(r.id_doctor || '-')}${r.source_hub && r.source_hub !== 'Poliklinik' ? ' · ' + esc(r.source_hub) : ''}</div></div>`
          },
          {
            data: null,
            render: (r, t) => {
              if (t !== 'display') return r.pemeriksaan.map(p => p.nama).join(' ');
              return `<div class="lb-periksa">${r.pemeriksaan.map(p => {
                const ada = p.status === 'lengkap';
                const angka = ada && p.harus > 0 ? ` <span class="fw-normal">${p.isi}/${p.harus}</span>` : '';
                return `<span class="lb-p ${p.status}" title="${esc(p.nama)}: ${ada ? `ada hasil (${p.isi} dari ${p.harus} parameter terisi)` : 'belum ada hasil'}"><i class="ti ti-${ada ? 'circle-check' : 'circle-dashed'}"></i>${esc(p.nama)}${angka}</span>`;
              }).join('')}</div>`;
            }
          },
          {
            data: null,
            render: (r, t) => {
              const [k, l] = STATUS[r.status] || ['bg-light text-dark', r.status];
              if (t !== 'display') return l;
              return `<span class="badge ${k}">${l}</span><div class="lb-sub mt-1">${r.lengkap}/${r.jumlah} pemeriksaan ada hasil</div>` +
                (tertunda(r) ? '<div class="lb-sub mt-1 text-danger"><i class="ti ti-alarm"></i> Tertunda</div>' : '');
            }
          },
        ],
      });

      function cocok(r) {
        if (f.periksa && !r.pemeriksaan.some(p => p.nama === f.periksa)) return false;
        if (f.dokter && String(r.id_doctor || '') !== f.dokter) return false;
        if (f.poli && String(r.id_poli || '') !== f.poli) return false;
        if (f.bayar && String(r.provider_name || '') !== f.bayar) return false;
        return true;
      }

      function isiPilihan(sel, daftarMentah, nilai) {
        const daftar = [...new Set(daftarMentah.map(v => String(v || '')).filter(Boolean))].sort((a, b) => a.localeCompare(b, 'id'));
        if (nilai && !daftar.includes(nilai)) daftar.unshift(nilai);
        $(sel).html('<option value="">Semua</option>' + daftar.map(v => `<option value="${esc(v)}">${esc(v)}</option>`).join('')).val(nilai);
      }

      function tampil() {
        const lolos = semua.filter(cocok);
        const minta = lolos.filter(r => r.status !== 'selesai');
        const selesai = lolos.filter(r => r.status === 'selesai').reverse();
        const hit = st => minta.filter(r => r.status === st).length;
        const nTunda = minta.filter(tertunda).length;
        $('#kBelum, #cBelum').text(hit('belum'));
        $('#kProses, #cProses').text(hit('proses'));
        $('#kTertunda, #cTertunda').text(nTunda);
        $('#kSelesai, #tSelesai').text(selesai.length);
        $('#tPermintaan, #cSemua').text(minta.length);
        $('#lbChipsWrap').toggle(f.tab === 'permintaan');
        $('#lbTabs .nav-link').removeClass('active').filter(`[data-tab="${f.tab}"]`).addClass('active');
        $('#lbChips .lb-chip').removeClass('active').filter(`[data-st="${f.st}"]`).addClass('active');
        table.clear().rows.add(f.tab === 'selesai' ? selesai : minta).draw(false);
      }

      function tandaiCepat() {
        const peta = { hari: [HARI_INI, HARI_INI], kemarin: [geser(HARI_INI, -1), geser(HARI_INI, -1)], 7: [geser(HARI_INI, -6), HARI_INI], bulan: [HARI_INI.slice(0, 8) + '01', HARI_INI] };
        $('#lbCepat .btn').each(function() { const p = peta[this.dataset.r]; $(this).toggleClass('active', p[0] === f.dari && p[1] === f.sampai); });
        return peta;
      }

      function muat(opsi = {}) {
        const d = $('#fromDate').val() || HARI_INI;
        const s = $('#toDate').val() || d;
        if (s < d) { Swal.fire({ icon: 'warning', title: 'Tanggal tidak valid', text: '"Sampai" tidak boleh sebelum "Dari".' }); return; }
        if ((new Date(s) - new Date(d)) / 864e5 > 62) { Swal.fire({ icon: 'warning', title: 'Rentang terlalu panjang', text: 'Maksimal 62 hari.' }); return; }
        if (sibuk) return;
        sibuk = true;
        f.dari = d; f.sampai = s; simpan(); tandaiCepat();
        if (!opsi.diam) $('#btnMuatUlang i').attr('class', 'ti ti-loader-2 icon-spin me-1');
        $.ajax({ url: API, dataType: 'json', data: { fromDate: d, toDate: s } })
          .done(res => {
            semua = res.data || [];
            isiPilihan('#fPeriksa', semua.flatMap(r => r.pemeriksaan.map(p => p.nama)), f.periksa);
            isiPilihan('#fDokter', semua.map(r => r.id_doctor), f.dokter);
            isiPilihan('#fPoli', semua.map(r => r.id_poli), f.poli);
            isiPilihan('#fBayar', semua.map(r => r.provider_name), f.bayar);
            tampil();
            $('#lbRentang').text(d === s ? (d === HARI_INI ? 'hari ini' : tglID(d)) : `${tglID(d)} – ${tglID(s)}`);
            $('#lbUpdate').text('diperbarui ' + new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }));
          })
          .fail(x => {
            if (opsi.diam) { $('#lbUpdate').text('gagal memperbarui'); return; }
            Swal.fire({ icon: 'error', title: 'Gagal memuat', text: x.responseJSON?.message || 'Server tidak merespon.' });
          })
          .always(() => { sibuk = false; $('#btnMuatUlang i').attr('class', 'ti ti-refresh me-1'); });
      }

      const gantiTab = (tab, st) => { f.tab = tab; f.st = st || 'semua'; simpan(); tampil(); };
      $('#lbTabs').on('click', '.nav-link', function() { gantiTab(this.dataset.tab); });
      $('.lb-kpi').on('click', function() { gantiTab(this.dataset.tab, this.dataset.st); });
      $('#lbChips').on('click', '.lb-chip', function() { f.st = this.dataset.st; simpan(); tampil(); });
      $('#lbCepat').on('click', '.btn', function() { const p = tandaiCepat()[this.dataset.r]; $('#fromDate').val(p[0]); $('#toDate').val(p[1]); muat(); });
      $('#btnFilter').on('click', () => muat());
      $('#fromDate, #toDate').on('keydown', e => { if (e.key === 'Enter') muat(); });
      $('#btnMuatUlang').on('click', () => muat());
      $('#fPeriksa, #fDokter, #fPoli, #fBayar').on('change', function() {
        f[{ fPeriksa: 'periksa', fDokter: 'dokter', fPoli: 'poli', fBayar: 'bayar' }[this.id]] = this.value;
        simpan(); tampil();
      });
      let tCari;
      $('#lbCari').on('input', function() { clearTimeout(tCari); tCari = setTimeout(() => { f.cari = this.value; simpan(); table.search(this.value).draw(); }, 200); });
      $('#btnReset').on('click', () => {
        f = { tab: f.tab, st: 'semua', dari: HARI_INI, sampai: HARI_INI, periksa: '', dokter: '', poli: '', bayar: '', cari: '' };
        $('#fromDate, #toDate').val(HARI_INI);
        $('#lbCari').val('');
        table.search('');
        muat();
      });

      setInterval(() => { if (!document.hidden && !Swal.isVisible() && f.sampai === HARI_INI) muat({ diam: true }); }, 60000);
      document.addEventListener('visibilitychange', () => { if (!document.hidden && f.sampai === HARI_INI) muat({ diam: true }); });

      table.search(f.cari);
      muat();
    })();
  </script>
</body>

</html>
