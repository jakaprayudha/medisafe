<?php
/**
 * Permintaan resep (farmasi): daftar per kunjungan.
 * Data: controller/farmasi/permintaanResepList (GET per rentang tanggal); tab, status & filter lain di klien.
 */
$title = 'Permintaan Resep';
require '../../controller/view.php';
session_write_close();
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    @keyframes pr-spin { to { transform: rotate(360deg); } }
    .icon-spin { display: inline-block; animation: pr-spin 1s linear infinite; }
    .pr-icon { width: 40px; height: 40px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
    .pr-kpi { border: 1px solid #e5eaef; border-radius: 12px; background: #fff; padding: 12px 14px; display: flex; align-items: center; gap: 12px; height: 100%; cursor: pointer; transition: border-color .15s, box-shadow .15s; }
    .pr-kpi:hover { border-color: #5d87ff; box-shadow: 0 4px 14px rgba(93, 135, 255, .12); }
    .pr-kpi .n { font-size: 1.35rem; font-weight: 700; line-height: 1.1; color: #111827; }
    .pr-kpi .l { font-size: .78rem; color: #475569; }

    .pr-tabs .nav-link { color: #475569; font-weight: 600; border: 0; border-bottom: 2px solid transparent; border-radius: 0; padding: .55rem .9rem; }
    .pr-tabs .nav-link.active { color: #5d87ff; border-bottom-color: #5d87ff; background: transparent; }
    .pr-tabs .badge { font-size: .7rem; }

    .pr-filter .form-label { font-size: .75rem; color: #475569; margin-bottom: 2px; }
    .pr-filter .form-control, .pr-filter .form-select { font-size: .85rem; }
    .pr-chip { border: 1px solid #e5eaef; background: #fff; color: #334155; border-radius: 999px; padding: 3px 12px; font-size: .78rem; font-weight: 600; }
    .pr-chip.active { background: #5d87ff; border-color: #5d87ff; color: #fff; }
    .pr-chip .n { opacity: .8; margin-left: 4px; }
    .pr-cepat .btn { font-size: .78rem; padding: 4px 10px; background: #fff; border: 1px solid #cbd5e1; color: #1f2937; font-weight: 600; }
    .pr-cepat .btn:hover { background: #f1f5f9; color: #111827; }
    .pr-cepat .btn.active, .pr-cepat .btn.active:hover { background: #1e293b; border-color: #1e293b; color: #fff; }
    .pr-draf { background: #f1f5f9 !important; color: #334155 !important; border: 1px solid #cbd5e1; }

    #tblResep { font-size: .86rem; }
    #tblResep thead th { font-size: .75rem; text-transform: uppercase; letter-spacing: .03em; color: #475569; font-weight: 600; white-space: nowrap; background: #f8fafc; }
    #tblResep td, #tblResep th { padding-left: .55rem; padding-right: .55rem; }
    #tblResep td { vertical-align: middle; white-space: nowrap; padding-top: .55rem; padding-bottom: .55rem; }
    .pr-nama { font-weight: 600; color: #111827; min-width: 180px; max-width: 280px; white-space: normal; line-height: 1.3; }
    .pr-sub { font-size: .76rem; color: #475569; }
    .pr-sub .sep { color: #cbd5e1; margin: 0 4px; }
    .pr-wrap { min-width: 140px; max-width: 230px; white-space: normal; line-height: 1.3; }
    .pr-tebal { font-weight: 600; color: #111827; }
    .pr-tunggu { display: inline-block; font-size: .72rem; font-weight: 700; border-radius: 6px; padding: 1px 6px; background: #eef2ff; color: #3730a3; }
    .pr-tunggu.lama { background: #fee2e2; color: #991b1b; }
    .badge.bg-warning, .badge.bg-warning-subtle { color: #1f2937 !important; }
    .badge.bg-warning-subtle { background-color: #fef3c7 !important; }
    .pr-kosong { padding: 36px 12px; text-align: center; color: #475569; }
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
                <span class="pr-icon bg-primary-subtle text-primary"><i class="ti ti-prescription"></i></span>
                <div class="flex-grow-1" style="min-width:0">
                  <h5 class="fw-semibold mb-0">Permintaan Resep</h5>
                  <div class="text-muted small"><span id="prRentang">hari ini</span> · <span id="prUpdate">memuat…</span></div>
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm" id="btnMuatUlang"><i class="ti ti-refresh me-1"></i>Muat ulang</button>
              </div>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-6 col-xl-3">
              <div class="pr-kpi" data-tab="permintaan" data-st="menunggu">
                <span class="pr-icon bg-danger-subtle text-danger"><i class="ti ti-hourglass"></i></span>
                <div><div class="n" id="kMenunggu">-</div><div class="l">Menunggu disiapkan</div></div>
              </div>
            </div>
            <div class="col-6 col-xl-3">
              <div class="pr-kpi" data-tab="permintaan" data-st="diproses">
                <span class="pr-icon bg-warning-subtle" style="color:#b45309"><i class="ti ti-pill"></i></span>
                <div><div class="n" id="kDiproses">-</div><div class="l">Sedang disiapkan</div></div>
              </div>
            </div>
            <div class="col-6 col-xl-3">
              <div class="pr-kpi" data-tab="selesai" data-st="semua">
                <span class="pr-icon bg-success-subtle text-success"><i class="ti ti-circle-check"></i></span>
                <div><div class="n" id="kSelesai">-</div><div class="l">Selesai</div></div>
              </div>
            </div>
            <div class="col-6 col-xl-3">
              <div class="pr-kpi" data-tab="permintaan" data-st="draf" title="Resep yang masih draf di dokter (belum dikirim ke farmasi)">
                <span class="pr-icon" style="background:#f1f5f9;color:#334155"><i class="ti ti-pencil"></i></span>
                <div><div class="n" id="kDraf">-</div><div class="l">Draf dokter (belum dikirim)</div></div>
              </div>
            </div>
          </div>

          <div class="card border-0 shadow-sm">
            <div class="card-body p-3 p-md-4">
              <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-2">
                <ul class="nav pr-tabs" id="prTabs">
                  <li class="nav-item"><button class="nav-link active" type="button" data-tab="permintaan">Permintaan <span class="badge rounded-pill bg-danger ms-1" id="tPermintaan">0</span></button></li>
                  <li class="nav-item"><button class="nav-link" type="button" data-tab="selesai">Selesai <span class="badge rounded-pill bg-success ms-1" id="tSelesai">0</span></button></li>
                </ul>
                <div class="pr-filter d-flex flex-wrap align-items-end gap-2">
                  <div>
                    <label class="form-label d-block">Periode</label>
                    <div class="btn-group pr-cepat" id="prCepat">
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

              <div class="pr-filter d-flex flex-wrap align-items-end gap-2 mb-3">
                <div>
                  <label for="prCari" class="form-label">Cari</label>
                  <input type="search" id="prCari" class="form-control form-control-sm" placeholder="Nama / No. RM / no. resep" style="width:220px">
                </div>
                <div>
                  <label for="fBayar" class="form-label">Jenis bayar</label>
                  <select id="fBayar" class="form-select form-select-sm" style="width:160px"><option value="">Semua</option></select>
                </div>
                <div>
                  <label for="fAsal" class="form-label">Asal</label>
                  <select id="fAsal" class="form-select form-select-sm" style="width:140px"><option value="">Semua</option></select>
                </div>
                <div>
                  <label for="fPoli" class="form-label">Poli</label>
                  <select id="fPoli" class="form-select form-select-sm" style="width:170px"><option value="">Semua</option></select>
                </div>
                <div>
                  <label for="fDokter" class="form-label">Dokter</label>
                  <select id="fDokter" class="form-select form-select-sm" style="width:200px"><option value="">Semua</option></select>
                </div>
                <div>
                  <label for="fJenis" class="form-label">Jenis obat</label>
                  <select id="fJenis" class="form-select form-select-sm" style="width:140px">
                    <option value="">Semua</option>
                    <option value="racikan">Ada racikan</option>
                    <option value="bmhp">Ada BMHP</option>
                    <option value="non">Non racikan saja</option>
                  </select>
                </div>
                <button type="button" id="btnReset" class="btn btn-light btn-sm" title="Kembalikan semua filter"><i class="ti ti-arrow-back-up me-1"></i>Reset filter</button>
              </div>

              <div class="d-flex flex-wrap gap-2 mb-3" id="prChips">
                <button type="button" class="pr-chip active" data-st="semua">Semua<span class="n" id="cSemua"></span></button>
                <button type="button" class="pr-chip" data-st="menunggu">Menunggu<span class="n" id="cMenunggu"></span></button>
                <button type="button" class="pr-chip" data-st="diproses">Disiapkan<span class="n" id="cDiproses"></span></button>
                <button type="button" class="pr-chip" data-st="draf">Draf dokter<span class="n" id="cDraf"></span></button>
              </div>

              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 w-100" id="tblResep">
                  <thead>
                    <tr>
                      <th style="width:1%">Aksi</th>
                      <th>Pasien</th>
                      <th>Diminta</th>
                      <th>Asal / Dokter</th>
                      <th>Resep</th>
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
      const API = 'controller/farmasi/permintaanResepList';
      const KUNCI = 'permintaanResep.filter'; // filter disimpan per hari (dulu: localStorage.clear() menghapus data aplikasi lain)
      const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      const tglLokal = d => d.toLocaleDateString('sv-SE', { timeZone: 'Asia/Jakarta' });
      const HARI_INI = tglLokal(new Date());
      const geser = (ymd, n) => { const d = new Date(ymd + 'T12:00:00'); d.setDate(d.getDate() + n); return d.toISOString().slice(0, 10); };
      const STATUS = {
        menunggu: ['bg-danger-subtle text-danger', 'Menunggu'],
        diproses: ['bg-warning-subtle', 'Disiapkan'],
        draf: ['pr-draf', 'Draf dokter'],
        selesai: ['bg-success-subtle text-success', 'Selesai'],
      };

      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const tglID = d => { if (!d) return '-'; const [y, m, h] = String(d).slice(0, 10).split('-'); return `${+h} ${BULAN[+m - 1]} ${y}`; };
      const jam = t => t ? String(t).slice(11, 16) : '';
      const menit = t => { const d = new Date(String(t).replace(' ', 'T')); return isNaN(d) ? null : Math.max(0, Math.round((Date.now() - d) / 60000)); };
      const durasi = m => m < 60 ? `${m} mnt` : (m < 1440 ? `${Math.floor(m / 60)} jam ${m % 60} mnt` : `${Math.floor(m / 1440)} hari`);

      // ---------- filter tersimpan ----------
      let f = { tab: 'permintaan', st: 'semua', dari: HARI_INI, sampai: HARI_INI, bayar: '', asal: '', poli: '', dokter: '', jenis: '', cari: '' };
      try {
        const s = JSON.parse(localStorage.getItem(KUNCI) || 'null');
        if (s && typeof s === 'object') {
          Object.assign(f, s.f || {});
          if (s.hari !== HARI_INI) { f.dari = HARI_INI; f.sampai = HARI_INI; } // ganti hari -> kembali ke hari ini
        }
      } catch (e) {}
      const simpan = () => { try { localStorage.setItem(KUNCI, JSON.stringify({ hari: HARI_INI, f })); } catch (e) {} };

      let semua = [];
      let sibuk = false;

      $('#fromDate, #toDate').attr('max', HARI_INI);
      $('#fromDate').val(f.dari);
      $('#toDate').val(f.sampai);
      $('#prCari').val(f.cari);
      $('#fJenis').val(f.jenis);

      $.fn.dataTable.ext.search.push((s, _d, i) => {
        if (s.nTable.id !== 'tblResep') return true;
        const r = s.aoData[i]._aData;
        if (f.st !== 'semua' && r.status !== f.st) return false;
        return true;
      });

      const table = $('#tblResep').DataTable({
        data: [],
        deferRender: true,
        order: [],
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        dom: 'rt<"d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3"ilp>',
        language: {
          info: '_START_–_END_ dari _TOTAL_ kunjungan',
          infoEmpty: '0 kunjungan',
          infoFiltered: '(disaring dari _MAX_)',
          lengthMenu: '_MENU_ / halaman',
          zeroRecords: '<div class="pr-kosong"><i class="ti ti-search fs-6 d-block mb-1"></i>Tidak ada resep yang cocok dengan filter.</div>',
          emptyTable: '<div class="pr-kosong"><i class="ti ti-mood-empty fs-6 d-block mb-1"></i>Tidak ada permintaan resep pada periode ini.</div>',
          paginate: { previous: '‹', next: '›' },
        },
        columns: [
          {
            data: null, orderable: false,
            render: (r, t) => t !== 'display' ? '' :
              `<a class="btn btn-sm ${r.status === 'selesai' ? 'btn-outline-primary' : 'btn-primary'}" href="module/admin/farmasi_order_detail?no=${encodeURIComponent(r.visit_ID)}&rm=${encodeURIComponent(r.nomor_rm || '')}&id=${encodeURIComponent(r.id_permintaan_farmasi)}"><i class="ti ti-file-text me-1"></i>${r.status === 'selesai' ? 'Lihat resep' : 'Proses resep'}</a>`
          },
          {
            data: null,
            render: (r, t) => {
              if (t !== 'display') return `${r.patient_name || ''} ${r.nomor_rm || ''} ${r.nomor || ''}`;
              const umur = r.umur !== null && r.umur !== undefined ? `${r.umur} th` : '';
              const info = [r.nomor_rm ? 'RM ' + esc(r.nomor_rm) : '', [esc(r.patient_gender || ''), umur].filter(Boolean).join(', ')].filter(Boolean).join('<span class="sep">|</span>');
              const bpjs = String(r.id_provider) === '1';
              return `<div class="pr-nama">${esc(r.patient_name || '(tanpa nama)')}</div><div class="pr-sub">${info || '-'}</div>` +
                `<div class="pr-sub mt-1"><span class="badge ${bpjs ? 'bg-success-subtle text-success' : 'bg-light text-dark border'}" title="${esc(r.provider_name || '')}">${esc(bpjs ? 'BPJS' : (r.provider_name || '-'))}</span></div>`;
            }
          },
          {
            data: null,
            render: (r, t) => {
              if (t !== 'display') return r.created_at || '';
              const m = r.status === 'selesai' ? null : menit(r.created_at);
              const tunggu = m === null ? '' : `<span class="pr-tunggu ${m > 30 ? 'lama' : ''}" title="Lama menunggu sejak resep masuk">${durasi(m)}</span>`;
              const tgl = String(r.created_at).slice(0, 10) === HARI_INI ? '' : `<div class="pr-sub">${tglID(r.created_at)}</div>`;
              return `<div class="pr-tebal">${esc(jam(r.created_at))}</div>${tgl}${tunggu ? `<div class="mt-1">${tunggu}</div>` : ''}`;
            }
          },
          {
            data: null,
            render: (r, t) => t !== 'display' ? `${r.source_hub || ''} ${r.id_poli || ''} ${r.id_doctor || ''}` :
              `<div class="pr-wrap"><div>${esc(r.id_poli || r.source_hub || '-')}</div><div class="pr-sub">${esc(r.id_doctor || '-')}${r.source_hub && r.source_hub !== 'Poliklinik' ? ' · ' + esc(r.source_hub) : ''}</div></div>`
          },
          {
            data: null,
            render: (r, t) => {
              if (t !== 'display') return r.nomor || '';
              const non = r.jumlah - r.racikan - r.bmhp;
              const bag = [];
              if (non > 0) bag.push(`${non} non racikan`);
              if (r.racikan > 0) bag.push(`<b>${r.racikan} racikan</b>`);
              if (r.bmhp > 0) bag.push(`${r.bmhp} BMHP`);
              return `<div class="pr-wrap"><div class="pr-tebal">${r.jumlah} tiket</div><div class="pr-sub">${bag.join(' · ')}</div>` +
                (r.nomor ? `<div class="pr-sub text-truncate" style="max-width:220px" title="${esc(r.nomor)}">${esc(r.nomor)}</div>` : '') + '</div>';
            }
          },
          {
            data: null,
            render: (r, t) => {
              const [k, l] = STATUS[r.status] || ['bg-light text-dark', r.status];
              if (t !== 'display') return l;
              const rinci = [];
              if (r.status !== 'selesai') {
                if (r.s1) rinci.push(`${r.s1} menunggu`);
                if (r.s2) rinci.push(`${r.s2} disiapkan`);
                if (r.s0) rinci.push(`${r.s0} draf`);
                if (r.s3) rinci.push(`${r.s3} selesai`);
              }
              return `<span class="badge ${k}">${l}</span>` + (rinci.length > 1 || (rinci.length && r.jumlah > 1) ? `<div class="pr-sub mt-1">${rinci.join(' · ')}</div>` : '') +
                (r.obat_pulang ? '<div class="pr-sub mt-1"><i class="ti ti-home"></i> Obat pulang</div>' : '');
            }
          },
        ],
      });

      // ---------- tampil ----------
      function cocok(r) {
        if (f.bayar && String(r.provider_name || '') !== f.bayar) return false;
        if (f.asal && String(r.source_hub || '') !== f.asal) return false;
        if (f.poli && String(r.id_poli || '') !== f.poli) return false;
        if (f.dokter && String(r.id_doctor || '') !== f.dokter) return false;
        if (f.jenis === 'racikan' && !r.racikan) return false;
        if (f.jenis === 'bmhp' && !r.bmhp) return false;
        if (f.jenis === 'non' && (r.racikan || r.bmhp)) return false;
        return true;
      }

      function isiPilihan(sel, kunci, nilai) {
        const daftar = [...new Set(semua.map(r => String(r[kunci] || '')).filter(Boolean))].sort((a, b) => a.localeCompare(b, 'id'));
        if (nilai && !daftar.includes(nilai)) daftar.unshift(nilai); // pilihan tersimpan tetap ada walau datanya kosong
        $(sel).html('<option value="">Semua</option>' + daftar.map(v => `<option value="${esc(v)}">${esc(v)}</option>`).join('')).val(nilai);
      }

      function tampil() {
        const lolos = semua.filter(cocok);
        const minta = lolos.filter(r => r.status !== 'selesai');
        const selesai = lolos.filter(r => r.status === 'selesai').sort((a, b) => String(b.terakhir).localeCompare(String(a.terakhir)));
        const hit = st => minta.filter(r => r.status === st).length;
        $('#kMenunggu').text(hit('menunggu'));
        $('#kDiproses').text(hit('diproses'));
        $('#kDraf').text(hit('draf'));
        $('#kSelesai, #tSelesai').text(selesai.length);
        $('#tPermintaan').text(minta.length);
        $('#cSemua').text(minta.length);
        $('#cMenunggu').text(hit('menunggu'));
        $('#cDiproses').text(hit('diproses'));
        $('#cDraf').text(hit('draf'));
        $('#prChips').toggleClass('d-none', f.tab !== 'permintaan');
        $('#prTabs .nav-link').removeClass('active').filter(`[data-tab="${f.tab}"]`).addClass('active');
        $('#prChips .pr-chip').removeClass('active').filter(`[data-st="${f.st}"]`).addClass('active');
        table.clear().rows.add(f.tab === 'selesai' ? selesai : minta).draw(false);
      }

      function tandaiCepat() {
        const peta = { hari: [HARI_INI, HARI_INI], kemarin: [geser(HARI_INI, -1), geser(HARI_INI, -1)], 7: [geser(HARI_INI, -6), HARI_INI], bulan: [HARI_INI.slice(0, 8) + '01', HARI_INI] };
        $('#prCepat .btn').each(function() { const p = peta[this.dataset.r]; $(this).toggleClass('active', p[0] === f.dari && p[1] === f.sampai); });
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
            isiPilihan('#fBayar', 'provider_name', f.bayar);
            isiPilihan('#fAsal', 'source_hub', f.asal);
            isiPilihan('#fPoli', 'id_poli', f.poli);
            isiPilihan('#fDokter', 'id_doctor', f.dokter);
            tampil();
            $('#prRentang').text(d === s ? (d === HARI_INI ? 'hari ini' : tglID(d)) : `${tglID(d)} – ${tglID(s)}`);
            $('#prUpdate').text('diperbarui ' + new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }));
          })
          .fail(x => {
            if (opsi.diam) { $('#prUpdate').text('gagal memperbarui'); return; }
            Swal.fire({ icon: 'error', title: 'Gagal memuat', text: x.responseJSON?.message || 'Server tidak merespon.' });
          })
          .always(() => { sibuk = false; $('#btnMuatUlang i').attr('class', 'ti ti-refresh me-1'); });
      }

      // ---------- event ----------
      const gantiTab = (tab, st) => { f.tab = tab; f.st = st || 'semua'; simpan(); tampil(); };
      $('#prTabs').on('click', '.nav-link', function() { gantiTab(this.dataset.tab); });
      $('.pr-kpi').on('click', function() { gantiTab(this.dataset.tab, this.dataset.st); });
      $('#prChips').on('click', '.pr-chip', function() { f.st = this.dataset.st; simpan(); tampil(); });
      $('#prCepat').on('click', '.btn', function() {
        const p = tandaiCepat()[this.dataset.r];
        $('#fromDate').val(p[0]); $('#toDate').val(p[1]);
        muat();
      });
      $('#btnFilter').on('click', () => muat());
      $('#fromDate, #toDate').on('keydown', e => { if (e.key === 'Enter') muat(); });
      $('#btnMuatUlang').on('click', () => muat());
      $('#fBayar, #fAsal, #fPoli, #fDokter, #fJenis').on('change', function() {
        f[{ fBayar: 'bayar', fAsal: 'asal', fPoli: 'poli', fDokter: 'dokter', fJenis: 'jenis' }[this.id]] = this.value;
        simpan(); tampil();
      });
      let tCari;
      $('#prCari').on('input', function() {
        clearTimeout(tCari);
        tCari = setTimeout(() => { f.cari = this.value; simpan(); table.search(this.value).draw(); }, 200);
      });
      $('#btnReset').on('click', () => {
        f = { tab: f.tab, st: 'semua', dari: HARI_INI, sampai: HARI_INI, bayar: '', asal: '', poli: '', dokter: '', jenis: '', cari: '' };
        $('#fromDate, #toDate').val(HARI_INI);
        $('#prCari').val(''); $('#fJenis').val('');
        table.search('');
        muat();
      });

      // segarkan tiap 60 dtk bila periode mencakup hari ini
      setInterval(() => { if (!document.hidden && !Swal.isVisible() && f.sampai === HARI_INI) muat({ diam: true }); }, 60000);
      document.addEventListener('visibilitychange', () => { if (!document.hidden && f.sampai === HARI_INI) muat({ diam: true }); });

      table.search(f.cari);
      muat();
    })();
  </script>
</body>

</html>
