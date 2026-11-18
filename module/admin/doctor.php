<?php
$title = 'Dokter';
require '../../controller/view.php';
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .min-w-0 {
      min-width: 0;
    }

    .dk-stat {
      border: 1px solid #e5eaef;
      border-radius: 12px;
      background: #fff;
      padding: 14px 16px;
      height: 100%;
    }

    .dk-stat.dk-click {
      cursor: pointer;
      transition: border-color .15s;
    }

    .dk-stat.dk-click:hover {
      border-color: #5d87ff;
    }

    .dk-icon {
      width: 40px;
      height: 40px;
      border-radius: 10px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 1.05rem;
      flex-shrink: 0;
    }

    .dk-val {
      font-size: 1.35rem;
      font-weight: 700;
      line-height: 1.2;
      color: #2a3547;
    }

    .dk-label {
      font-size: .75rem;
      color: #6c757d;
    }

    .dk-ellipsis {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .dk-chips .btn {
      border-radius: 20px;
      font-size: .8rem;
      padding: .3rem .85rem;
    }

    .dk-search {
      width: 230px;
    }

    @media (max-width: 575.98px) {

      .dk-tools,
      .dk-search {
        width: 100%;
      }

      .dk-search {
        flex: 1;
      }
    }

    /* kartu dokter */
    .dk-card {
      border: 1px solid #e5eaef;
      border-radius: 14px;
      background: #fff;
      height: 100%;
      display: flex;
      flex-direction: column;
      transition: box-shadow .15s, border-color .15s;
      position: relative;
      overflow: hidden;
    }

    .dk-card::before {
      content: '';
      position: absolute;
      left: 0;
      top: 0;
      right: 0;
      height: 4px;
      background: var(--dk-c);
    }

    .dk-card:hover {
      border-color: var(--dk-c);
      box-shadow: 0 6px 18px rgba(42, 53, 71, .08);
    }

    .dk-card.off {
      background: #fafbfc;
    }

    .dk-card.off .dk-body,
    .dk-card.off .dk-head {
      opacity: .55;
    }

    .dk-head {
      padding: 16px 16px 0;
      display: flex;
      gap: 12px;
      align-items: flex-start;
    }

    .dk-avatar {
      width: 48px;
      height: 48px;
      border-radius: 50%;
      flex-shrink: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 800;
      font-size: 1rem;
      color: var(--dk-c);
      background: var(--dk-bg);
      position: relative;
    }

    .dk-avatar .dot {
      position: absolute;
      right: 1px;
      bottom: 1px;
      width: 12px;
      height: 12px;
      border-radius: 50%;
      border: 2px solid #fff;
      background: #13deb9;
    }

    .dk-card.off .dk-avatar .dot {
      background: #adb5bd;
    }

    .dk-name {
      font-weight: 700;
      color: #2a3547;
      font-size: .95rem;
      line-height: 1.3;
    }

    .dk-poli {
      font-size: .78rem;
      color: var(--dk-c);
      font-weight: 600;
    }

    .dk-tag {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      font-size: .68rem;
      font-weight: 600;
      padding: .2em .55em;
      border-radius: 6px;
      background: #f2f5f9;
      color: #5a6a85;
      margin-top: 5px;
      margin-right: 4px;
    }

    .dk-body {
      padding: 14px 16px 14px;
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .dk-today {
      display: flex;
      align-items: flex-end;
      justify-content: space-between;
      gap: 10px;
    }

    .dk-big {
      font-size: 1.9rem;
      font-weight: 800;
      line-height: 1;
      color: #2a3547;
    }

    .dk-big small {
      font-size: .75rem;
      font-weight: 500;
      color: #7c8fac;
      margin-left: 4px;
    }

    .dk-spark {
      display: flex;
      align-items: flex-end;
      gap: 3px;
      height: 32px;
    }

    .dk-spark span {
      width: 7px;
      border-radius: 3px 3px 0 0;
      background: var(--dk-bg2);
      min-height: 3px;
    }

    .dk-spark span.now {
      background: var(--dk-c);
    }

    .dk-mini {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 6px;
      margin-top: 12px;
    }

    .dk-mini div {
      background: #f6f9fc;
      border-radius: 8px;
      padding: 6px 4px;
      text-align: center;
    }

    .dk-mini b {
      display: block;
      font-size: .88rem;
      color: #2a3547;
    }

    .dk-mini span {
      font-size: .64rem;
      color: #7c8fac;
    }

    .dk-info {
      margin-top: 10px;
      font-size: .76rem;
      color: #5a6a85;
      display: flex;
      flex-direction: column;
      gap: 4px;
    }

    .dk-info a {
      color: inherit;
    }

    .dk-info a:hover {
      color: #13deb9;
    }

    .dk-warn {
      font-size: .72rem;
      border-radius: 8px;
      padding: 5px 8px;
      margin-top: 8px;
    }

    .dk-foot {
      border-top: 1px solid #eef2f6;
      padding: 8px 12px 8px 16px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .dk-foot .form-check-input {
      cursor: pointer;
    }

    .dk-foot .btn {
      width: 32px;
      height: 32px;
      padding: 0;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 8px;
    }

    .dk-loading {
      position: absolute;
      inset: 0;
      background: rgba(255, 255, 255, .6);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 5;
      border-radius: 12px;
    }

    .dk-empty {
      border: 1.5px dashed #dfe5ef;
      border-radius: 14px;
      padding: 40px 16px;
      text-align: center;
      color: #7c8fac;
    }

    .dk-lain td {
      font-size: .82rem;
      padding: .45rem .6rem;
    }

    .dk-form-name {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 10px 12px;
      border-radius: 10px;
      background: #f6f9fc;
    }
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
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
            <div>
              <h4 class="fw-semibold mb-1">Dokter</h4>
              <div class="text-muted small">Kelola data dokter dan pantau jumlah pasien yang ditangani.</div>
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center">
              <div class="input-group flex-nowrap" style="width:auto">
                <button class="btn btn-outline-secondary" id="tglPrev" title="Hari sebelumnya"><i class="fas fa-chevron-left"></i></button>
                <input type="date" id="dkTanggal" class="form-control" style="width:160px">
                <button class="btn btn-outline-secondary" id="tglNext" title="Hari berikutnya"><i class="fas fa-chevron-right"></i></button>
              </div>
              <button class="btn btn-outline-primary d-none" id="tglHariIni">Hari ini</button>
              <button class="btn btn-light" id="btnRefresh" title="Muat ulang"><i class="fas fa-sync-alt"></i></button>
              <button class="btn btn-primary" id="btnTambah"><i class="fas fa-plus me-1"></i> Tambah Dokter</button>
            </div>
          </div>

          <!-- STAT -->
          <div class="row g-3 mb-3">
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="dk-stat d-flex gap-3 align-items-center">
                <span class="dk-icon bg-primary-subtle text-primary"><i class="fas fa-user-md"></i></span>
                <div class="min-w-0">
                  <div class="dk-val" id="stDokter">-</div>
                  <div class="dk-label" id="stDokterSub">Dokter aktif</div>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="dk-stat d-flex gap-3 align-items-center">
                <span class="dk-icon bg-success-subtle text-success"><i class="fas fa-user-friends"></i></span>
                <div class="min-w-0">
                  <div class="dk-val" id="stHari">-</div>
                  <div class="dk-label" id="stHariSub">Pasien hari ini</div>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="dk-stat d-flex gap-3 align-items-center">
                <span class="dk-icon bg-warning-subtle text-warning"><i class="fas fa-fire"></i></span>
                <div class="min-w-0">
                  <div class="dk-val dk-ellipsis" id="stTop" style="font-size:1.02rem">-</div>
                  <div class="dk-label" id="stTopSub">Dokter tersibuk</div>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="dk-stat dk-click d-flex gap-3 align-items-center" id="stPerhatianCard" title="Tampilkan dokter yang perlu dilengkapi">
                <span class="dk-icon bg-danger-subtle text-danger"><i class="fas fa-exclamation-triangle"></i></span>
                <div class="min-w-0">
                  <div class="dk-val" id="stPerhatian">-</div>
                  <div class="dk-label" id="stPerhatianSub">Perlu dilengkapi</div>
                </div>
              </div>
            </div>
          </div>

          <!-- DAFTAR DOKTER -->
          <div class="card position-relative mb-3">
            <div class="dk-loading" id="dkLoading"><div class="spinner-border text-primary"></div></div>
            <div class="card-body p-4">
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div class="dk-chips d-flex flex-wrap gap-2" id="dkChips">
                  <button class="btn btn-primary" data-s="">Semua <span class="badge bg-white text-primary ms-1" data-n=""></span></button>
                  <button class="btn btn-light" data-s="1">Aktif <span class="badge bg-white text-dark ms-1" data-n="1"></span></button>
                  <button class="btn btn-light" data-s="0">Nonaktif <span class="badge bg-white text-dark ms-1" data-n="0"></span></button>
                  <button class="btn btn-light" data-s="warn">Perlu dilengkapi <span class="badge bg-white text-dark ms-1" data-n="warn"></span></button>
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center dk-tools">
                  <select id="dkPoli" class="form-select form-select-sm" style="width:auto">
                    <option value="">Semua poli</option>
                  </select>
                  <select id="dkSort" class="form-select form-select-sm" style="width:auto">
                    <option value="pasien">Pasien terbanyak</option>
                    <option value="nama">Nama A-Z</option>
                  </select>
                  <div class="input-group input-group-sm dk-search">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="search" id="dkSearch" class="form-control" placeholder="Cari nama / kode / HP...">
                  </div>
                </div>
              </div>
              <div class="row g-3" id="dkGrid"></div>
            </div>
          </div>

          <!-- NAMA DOKTER TIDAK COCOK -->
          <div class="card d-none" id="dkLainCard">
            <div class="card-body p-4">
              <div class="d-flex align-items-center gap-2 mb-1">
                <i class="fas fa-info-circle text-warning"></i>
                <h6 class="fw-semibold mb-0">Kunjungan dengan nama dokter yang tidak ada di master</h6>
              </div>
              <div class="text-muted small mb-3">Nama dokter pada kunjungan ini berbeda penulisan dengan data dokter di atas, sehingga tidak terhitung di kartu dokter.</div>
              <div class="table-responsive">
                <table class="table table-sm align-middle mb-0 dk-lain">
                  <thead class="table-light">
                    <tr>
                      <th>Nama dokter pada kunjungan</th>
                      <th class="text-center">Hari ini</th>
                      <th class="text-center">Selesai</th>
                      <th class="text-center">Batal</th>
                      <th class="text-center">Bulan ini</th>
                    </tr>
                  </thead>
                  <tbody id="dkLainBody"></tbody>
                </table>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <!-- MODAL DOKTER -->
  <div class="modal fade" id="programModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <form id="programForm" class="modal-content" novalidate>
        <div class="modal-header">
          <h5 class="modal-title d-flex align-items-center gap-2"><i class="fas fa-user-md text-primary"></i> <span id="dkModalTitle">Tambah Dokter</span></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_doctor" id="id_doctor">
          <input type="hidden" id="doctor_name" name="doctor_name">
          <div class="row g-3">
            <div class="col-12" id="wrapPilihDokter">
              <label class="form-label required" for="kode_dokter">Dokter (PCare)</label>
              <select id="kode_dokter" name="doctor_code" class="form-select"></select>
              <div class="form-text">Daftar dokter diambil dari PCare BPJS.</div>
            </div>
            <div class="col-12 d-none" id="wrapNamaDokter">
              <label class="form-label">Dokter</label>
              <div class="dk-form-name">
                <div class="dk-avatar" id="dkFormAvatar" style="--dk-c:#5d87ff;--dk-bg:#ecf2ff;width:40px;height:40px"></div>
                <div class="min-w-0">
                  <div class="fw-semibold" id="dkFormNama"></div>
                  <div class="small text-muted" id="dkFormKode"></div>
                </div>
              </div>
            </div>
            <div class="col-12">
              <label class="form-label required" for="id_poli">Poliklinik</label>
              <select name="id_poli" id="id_poli" class="form-select"></select>
            </div>
            <div class="col-12">
              <label class="form-label" for="doctor_phone">No. Handphone</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-phone-alt"></i></span>
                <input type="tel" id="doctor_phone" name="doctor_phone" class="form-control" maxlength="20" placeholder="08xxxxxxxxxx" pattern="[0-9+\-\s]{8,20}">
              </div>
            </div>
            <div class="col-12">
              <label class="form-label" for="doctor_address">Alamat</label>
              <textarea name="doctor_address" id="doctor_address" class="form-control" rows="3" maxlength="255"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary" id="dkSave"><i class="fas fa-save me-1"></i> Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <?php require 'library.php'; ?>
  <script src="controller/admisi/helper.js"></script>
  <script>
    (function() {
      const apiUrl = 'controller/master/dokterController';
      const WARNA = [
        ['#5d87ff', '#ecf2ff', '#c9d7ff'],
        ['#13deb9', '#e6fffa', '#b5f3e6'],
        ['#7460ee', '#efecfd', '#d4cdfa'],
        ['#ffae1f', '#fef5e5', '#ffe1ad'],
        ['#49beff', '#e8f7ff', '#bde6ff'],
        ['#fa896b', '#fdede8', '#fcd0c4'],
        ['#e0569b', '#fdebf4', '#f6c3dc'],
        ['#3ea55d', '#e8f6ec', '#bfe5ca'],
      ];
      let dokter = [],
        stats = {},
        hari = [],
        filterStatus = '',
        warnaPoli = {},
        poliSiap = false,
        dokterSiap = false;

      const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
      } [c]));
      const fmt = n => (Number(n) || 0).toLocaleString('id-ID');
      const api = (url, opt = {}) => fetch(url, opt).then(r => r.json().catch(() => ({
        status: 'error',
        message: 'Respon server tidak valid.'
      })));
      const toast = (icon, title) => Swal.fire({
        toast: true,
        position: 'top-end',
        icon,
        title,
        showConfirmButton: false,
        timer: 2200,
        timerProgressBar: true
      });
      const ymd = d => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
      const hariIni = ymd(new Date());
      const kosong = () => ({
        hari_ini: 0,
        antre: 0,
        selesai: 0,
        batal: 0,
        bulan: 0,
        tren: [0, 0, 0, 0, 0, 0, 0]
      });
      const st = id => stats[id] || kosong();
      const labelTgl = t => t === hariIni ? 'hari ini' : new Date(t + 'T00:00:00').toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
      });
      const fmtTgl = t => new Date(t + 'T00:00:00').toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
      });
      const inisial = n => {
        const kata = String(n || '').replace(/^\s*(drg|dr)\b\.?\s*/i, '').replace(/,.*$/, '').trim().split(/\s+/).filter(Boolean);
        return ((kata[0] || '?')[0] + (kata.length > 1 ? kata[kata.length - 1][0] : '')).toUpperCase();
      };
      const warna = d => {
        const k = d.id_poli || '';
        if (!(k in warnaPoli)) warnaPoli[k] = WARNA[Object.keys(warnaPoli).length % WARNA.length];
        return warnaPoli[k];
      };
      const waLink = hp => {
        let n = String(hp || '').replace(/\D/g, '');
        if (n.startsWith('0')) n = '62' + n.slice(1);
        return n.length >= 9 ? `https://wa.me/${n}` : '';
      };

      // dokter yang perlu dilengkapi: SIP habis / segera habis, belum ada ID Satu Sehat, tanpa HP
      function catatan(d) {
        const out = [];
        const sip = d.sip_date_expaired;
        if (sip && /^\d{4}-\d{2}-\d{2}$/.test(sip)) {
          const sisa = Math.round((new Date(sip + 'T00:00:00') - new Date(hariIni + 'T00:00:00')) / 864e5);
          if (sisa < 0) out.push(['danger', `SIP kedaluwarsa sejak ${fmtTgl(sip)}`]);
          else if (sisa <= 60) out.push(['warning', `SIP berakhir ${fmtTgl(sip)} (${sisa} hari lagi)`]);
        }
        if (!d.idsh) out.push(['warning', 'Belum ada ID IHS Satu Sehat']);
        return out;
      }

      const $tgl = $('#dkTanggal').val(hariIni).attr('max', hariIni);
      const tanggal = () => $tgl.val() || hariIni;

      function load(withList = true) {
        $('#dkLoading').css('display', 'flex');
        const reqs = [api(`${apiUrl}?stats=1&tanggal=${encodeURIComponent(tanggal())}`)];
        if (withList) reqs.push(api(apiUrl));
        Promise.all(reqs).then(([s, l]) => {
          if (l) {
            if (l.status !== 'success') return Swal.fire('Gagal', l.message || 'Gagal memuat dokter.', 'error');
            dokter = l.data || [];
            isiFilterPoli();
          }
          if (s.status === 'success') {
            stats = s.data || {};
            hari = s.hari || [];
            renderLain(s.lain || []);
          } else {
            stats = {};
            toast('warning', s.message || 'Gagal memuat jumlah pasien.');
          }
          renderStats();
          render();
        }).catch(() => Swal.fire('Gagal', 'Tidak dapat terhubung ke server.', 'error'))
          .finally(() => $('#dkLoading').css('display', 'none'));
      }

      function isiFilterPoli() {
        const cur = $('#dkPoli').val();
        const map = {};
        dokter.forEach(d => {
          map[d.id_poli || ''] = d.nmPoli || d.id_poli || 'Tanpa poli';
        });
        $('#dkPoli').html('<option value="">Semua poli</option>' + Object.entries(map)
          .sort((a, b) => a[1].localeCompare(b[1]))
          .map(([k, v]) => `<option value="${esc(k)}">${esc(v)}</option>`).join('')).val(cur in map ? cur : '');
      }

      function renderStats() {
        const t = tanggal();
        const aktif = dokter.filter(d => +d.doctor_status === 1);
        let hariN = 0,
          selesai = 0,
          top = null;
        dokter.forEach(d => {
          const s = st(d.id_doctor);
          hariN += s.hari_ini;
          selesai += s.selesai;
          if (s.hari_ini > 0 && (!top || s.hari_ini > st(top.id_doctor).hari_ini)) top = d;
        });
        const perhatian = dokter.filter(d => +d.doctor_status === 1 && catatan(d).length).length;
        $('#stDokter').text(fmt(aktif.length));
        $('#stDokterSub').text(`Dokter aktif dari ${fmt(dokter.length)} dokter`);
        $('#stHari').text(fmt(hariN));
        $('#stHariSub').html(`Pasien ${esc(labelTgl(t))} · <span class="text-success">${fmt(selesai)} selesai</span>`);
        $('#stTop').text(top ? top.doctor_name : '-').attr('title', top ? top.doctor_name : '');
        $('#stTopSub').text(top ? `${fmt(st(top.id_doctor).hari_ini)} pasien ${labelTgl(t)}` : `Belum ada pasien ${labelTgl(t)}`);
        $('#stPerhatian').text(fmt(perhatian));
        $('#stPerhatianSub').text(perhatian ? 'Dokter aktif perlu dilengkapi (SIP / IHS)' : 'Data dokter aktif lengkap');

        $('#tglHariIni').toggleClass('d-none', t === hariIni);
        $('#tglNext').prop('disabled', t >= hariIni);
        $('#dkChips [data-n=""]').text(dokter.length);
        $('#dkChips [data-n="1"]').text(aktif.length);
        $('#dkChips [data-n="0"]').text(dokter.length - aktif.length);
        $('#dkChips [data-n="warn"]').text(perhatian);
      }

      function render() {
        const q = $('#dkSearch').val().trim().toLowerCase();
        const poli = $('#dkPoli').val();
        const sort = $('#dkSort').val();
        const coll = new Intl.Collator('id', {
          sensitivity: 'base'
        });
        const list = dokter.filter(d =>
          (filterStatus === '' ||
            (filterStatus === 'warn' ? +d.doctor_status === 1 && catatan(d).length > 0 : String(+d.doctor_status) === filterStatus)) &&
          (poli === '' || (d.id_poli || '') === poli) &&
          (!q || [d.doctor_name, d.doctor_code, d.doctor_phone, d.nmPoli].some(v => String(v || '').toLowerCase().includes(q)))
        ).sort((a, b) => {
          const nama = coll.compare(String(a.doctor_name || '').replace(/^\s*(drg|dr)\b\.?\s*/i, ''), String(b.doctor_name || '').replace(/^\s*(drg|dr)\b\.?\s*/i, ''));
          if (sort === 'nama') return nama;
          return (+b.doctor_status - +a.doctor_status) || (st(b.id_doctor).hari_ini - st(a.id_doctor).hari_ini) || (st(b.id_doctor).bulan - st(a.id_doctor).bulan) || nama;
        });

        if (!dokter.length) {
          $('#dkGrid').html(`<div class="col-12"><div class="dk-empty"><i class="fas fa-user-md fa-2x mb-2 d-block"></i>Belum ada dokter. Klik <b>Tambah Dokter</b> untuk memulai.</div></div>`);
          return;
        }
        if (!list.length) {
          $('#dkGrid').html(`<div class="col-12"><div class="dk-empty">Dokter tidak ditemukan.</div></div>`);
          return;
        }
        const maxHari = Math.max(0, ...dokter.map(d => st(d.id_doctor).hari_ini));
        $('#dkGrid').html(list.map(d => kartu(d, maxHari)).join(''));
      }

      function kartu(d, maxHari) {
        const s = st(d.id_doctor);
        const [c, bg, bg2] = warna(d);
        const on = +d.doctor_status === 1;
        const maxT = Math.max(1, ...s.tren);
        const spark = s.tren.map((n, i) => {
          const t = hari[i] ? new Date(hari[i] + 'T00:00:00').toLocaleDateString('id-ID', {
            weekday: 'short',
            day: 'numeric',
            month: 'short'
          }) : '';
          return `<span class="${i === 6 ? 'now' : ''}" style="height:${Math.max(8, Math.round(n / maxT * 100))}%" title="${esc(t)}: ${fmt(n)} pasien"></span>`;
        }).join('');
        const top = on && maxHari > 0 && s.hari_ini === maxHari ?
          '<span class="dk-tag" style="background:#fff4e5;color:#e08a00"><i class="fas fa-fire"></i>Tersibuk</span>' : '';
        const ihs = d.idsh ?
          `<span class="dk-tag bg-success-subtle text-success" title="ID IHS Satu Sehat: ${esc(d.idsh)}"><i class="fas fa-check-circle"></i>Satu Sehat</span>` : '';
        const wa = waLink(d.doctor_phone);
        const notes = on ? catatan(d) : [];
        const dipakai = +d.jml_kunjungan > 0;

        return `
        <div class="col-sm-6 col-lg-4 col-xxl-3">
          <div class="dk-card ${on ? '' : 'off'}" style="--dk-c:${c};--dk-bg:${bg};--dk-bg2:${bg2}">
            <div class="dk-head">
              <div class="dk-avatar">${esc(inisial(d.doctor_name))}<span class="dot" title="${on ? 'Aktif' : 'Nonaktif'}"></span></div>
              <div class="min-w-0">
                <div class="dk-name">${esc(d.doctor_name || '-')}</div>
                <div class="dk-poli"><i class="fas fa-stethoscope me-1"></i>${esc(d.nmPoli || d.id_poli || 'Tanpa poli')}</div>
                ${d.doctor_code ? `<span class="dk-tag" title="Kode dokter PCare"><i class="fas fa-hashtag"></i>${esc(d.doctor_code)}</span>` : ''}${ihs}${top}
              </div>
            </div>
            <div class="dk-body">
              <div class="dk-today">
                <div>
                  <div class="dk-label mb-1">Pasien ${esc(labelTgl(tanggal()))}</div>
                  <div class="dk-big">${fmt(s.hari_ini)}<small>pasien</small></div>
                </div>
                <div class="text-end">
                  <div class="dk-spark" title="7 hari terakhir">${spark}</div>
                  <div class="dk-label" style="font-size:.65rem">7 hari</div>
                </div>
              </div>
              <div class="dk-mini">
                <div><b class="text-warning">${fmt(s.antre)}</b><span>Antre</span></div>
                <div><b class="text-success">${fmt(s.selesai)}</b><span>Selesai</span></div>
                <div><b class="text-danger">${fmt(s.batal)}</b><span>Batal</span></div>
                <div><b>${fmt(s.bulan)}</b><span>Bulan ini</span></div>
              </div>
              <div class="dk-info">
                <div class="dk-ellipsis"><i class="fas fa-phone-alt me-2 text-muted"></i>${d.doctor_phone
                  ? (wa ? `<a href="${wa}" target="_blank" rel="noopener" title="Chat WhatsApp">${esc(d.doctor_phone)} <i class="fab fa-whatsapp text-success"></i></a>` : esc(d.doctor_phone))
                  : '<span class="text-muted">Belum ada nomor HP</span>'}</div>
                ${d.doctor_address ? `<div class="dk-ellipsis" title="${esc(d.doctor_address)}"><i class="fas fa-map-marker-alt me-2 text-muted"></i>${esc(d.doctor_address)}</div>` : ''}
              </div>
              ${notes.map(([cls, txt]) => `<div class="dk-warn bg-${cls}-subtle text-${cls}-emphasis"><i class="fas fa-exclamation-circle me-1"></i>${esc(txt)}</div>`).join('')}
            </div>
            <div class="dk-foot">
              <div class="form-check form-switch mb-0">
                <input class="form-check-input toggle-status" type="checkbox" role="switch" id="sw${d.id_doctor}" data-id="${d.id_doctor}" ${on ? 'checked' : ''}>
                <label class="form-check-label small ${on ? 'text-success' : 'text-muted'}" for="sw${d.id_doctor}">${on ? 'Aktif' : 'Nonaktif'}</label>
              </div>
              <div class="d-flex gap-1">
                <a class="btn btn-light text-info" href="module/admin/doctor_details?no=${encodeURIComponent(d.doctor_number || '')}" title="Detail, SIP/STR & jadwal"><i class="fas fa-id-badge"></i></a>
                <button class="btn btn-light text-primary edit-btn" data-id="${d.id_doctor}" title="Ubah"><i class="fas fa-pen"></i></button>
                ${dipakai
                  ? `<span class="d-inline-block" tabindex="0" title="Tidak bisa dihapus: sudah ada ${fmt(d.jml_kunjungan)} data kunjungan pasien. Nonaktifkan bila tidak bertugas."><button class="btn btn-light text-danger" disabled style="pointer-events:none"><i class="fas fa-trash"></i></button></span>`
                  : `<button class="btn btn-light text-danger delete-btn" data-id="${d.id_doctor}" title="Hapus"><i class="fas fa-trash"></i></button>`}
              </div>
            </div>
          </div>
        </div>`;
      }

      function renderLain(rows) {
        $('#dkLainCard').toggleClass('d-none', !rows.length);
        $('#dkLainBody').html(rows.map(r => `
          <tr>
            <td class="fw-semibold">${esc(r.nama)}</td>
            <td class="text-center">${fmt(r.hari_ini)}</td>
            <td class="text-center text-success">${fmt(r.selesai)}</td>
            <td class="text-center text-danger">${fmt(r.batal)}</td>
            <td class="text-center">${fmt(r.bulan)}</td>
          </tr>`).join(''));
      }

      // ---------- filter & tanggal ----------
      function pilihChip(s) {
        filterStatus = s;
        $('#dkChips .btn').each(function() {
          const aktif = String($(this).data('s')) === s;
          $(this).toggleClass('btn-primary', aktif).toggleClass('btn-light', !aktif)
            .find('.badge').toggleClass('text-primary', aktif).toggleClass('text-dark', !aktif);
        });
        render();
      }
      $('#dkChips').on('click', '.btn', function() {
        pilihChip(String($(this).data('s')));
      });
      $('#stPerhatianCard').on('click', () => pilihChip('warn'));
      $('#dkSearch').on('input', render);
      $('#dkPoli, #dkSort').on('change', render);
      $tgl.on('change', () => {
        if (!$tgl.val() || $tgl.val() > hariIni) $tgl.val(hariIni);
        load(false);
      });
      const geser = n => {
        const d = new Date(tanggal() + 'T00:00:00');
        d.setDate(d.getDate() + n);
        const v = ymd(d);
        $tgl.val(v > hariIni ? hariIni : v).trigger('change');
      };
      $('#tglPrev').on('click', () => geser(-1));
      $('#tglNext').on('click', () => geser(1));
      $('#tglHariIni').on('click', () => $tgl.val(hariIni).trigger('change'));
      $('#btnRefresh').on('click', () => load(true));

      // ---------- form ----------
      ['#kode_dokter', '#id_poli'].forEach(sel => $(sel).select2({
        dropdownParent: $('#programModal'),
        width: '100%',
        language: {
          noResults: () => sel === '#id_poli' ? 'Poli tidak ditemukan' : 'Dokter tidak ditemukan'
        }
      }));
      $('#kode_dokter').on('change', function() {
        $('#doctor_name').val($(this).find(':selected').data('nama') || '');
      });

      // daftar poli PCare dimuat sekali
      function muatPoli(nilai) {
        const isi = () => $('#id_poli').val(nilai || '').trigger('change');
        if (poliSiap) return Promise.resolve(isi());
        return APP.ambil_data('#id_poli', 'poli/fktp/0/100', 'kdPoli', 'nmPoli', true)
          .then(() => {
            poliSiap = $('#id_poli option').length > 1;
            if (nilai && !$(`#id_poli option[value="${CSS.escape(nilai)}"]`).length) {
              $('#id_poli').append(new Option(nilai, nilai));
            }
            isi();
          });
      }

      function bukaForm(d) {
        const f = $('#programForm')[0];
        f.reset();
        f.classList.remove('was-validated');
        $('#id_doctor').val(d ? d.id_doctor : '');
        $('#doctor_name').val(d ? d.doctor_name || '' : '');
        $('#wrapPilihDokter').toggleClass('d-none', !!d);
        $('#wrapNamaDokter').toggleClass('d-none', !d);
        $('#kode_dokter').prop('disabled', !!d);
        if (d) {
          $('#dkFormAvatar').text(inisial(d.doctor_name));
          $('#dkFormNama').text(d.doctor_name || '-');
          $('#dkFormKode').text(d.doctor_code ? `Kode PCare ${d.doctor_code}` : 'Kode PCare belum ada');
          $('#doctor_phone').val(d.doctor_phone || '');
          $('#doctor_address').val(d.doctor_address || '');
        } else if (!dokterSiap) {
          APP.ambil_data_save('#kode_dokter', 'dokter/0/100', 'nmDokter', 'kdDokter', true, '#doctor_name')
            .then(() => {
              dokterSiap = $('#kode_dokter option').length > 1;
            }).catch(() => {});
        } else {
          $('#kode_dokter').val('').trigger('change');
        }
        muatPoli(d ? d.id_poli : '').catch(() => {});
        $('#dkModalTitle').text(d ? 'Ubah Dokter' : 'Tambah Dokter');
        $('#programModal').modal('show');
      }
      $('#btnTambah').on('click', () => bukaForm(null));
      $(document).on('click', '.edit-btn', function() {
        const d = dokter.find(x => +x.id_doctor === +$(this).data('id'));
        if (d) bukaForm(d);
      });

      $('#programForm').on('submit', function(e) {
        e.preventDefault();
        const id = $('#id_doctor').val();
        if (!id && !$('#kode_dokter').val()) return Swal.fire('Lengkapi data', 'Pilih dokter dari daftar PCare.', 'warning');
        if (!$('#id_poli').val()) return Swal.fire('Lengkapi data', 'Pilih poliklinik.', 'warning');
        if (!this.checkValidity()) {
          this.classList.add('was-validated');
          return;
        }
        const body = new URLSearchParams(new FormData(this));
        if (id) body.delete('doctor_code'); // dokter tidak diganti saat ubah
        const $btn = $('#dkSave').prop('disabled', true);
        api(apiUrl, {
            method: id ? 'PUT' : 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            body
          })
          .then(r => {
            if (r.status !== 'success') return Swal.fire('Gagal', r.message || 'Gagal menyimpan.', 'error');
            $('#programModal').modal('hide');
            toast('success', r.message);
            load(true);
          })
          .catch(() => Swal.fire('Gagal', 'Tidak dapat terhubung ke server.', 'error'))
          .finally(() => $btn.prop('disabled', false));
      });

      // ---------- hapus ----------
      $(document).on('click', '.delete-btn', function() {
        const id = $(this).data('id');
        const d = dokter.find(x => +x.id_doctor === +id) || {};
        Swal.fire({
          title: 'Hapus dokter?',
          html: `<b>${esc(d.doctor_name || '')}</b> akan dihapus dari master dokter.`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Hapus',
          cancelButtonText: 'Batal',
          confirmButtonColor: '#fa896b'
        }).then(res => {
          if (!res.isConfirmed) return;
          api(`${apiUrl}?id=${id}`, {
            method: 'DELETE'
          }).then(r => {
            if (r.status !== 'success') return Swal.fire('Gagal', r.message || 'Gagal menghapus.', 'error');
            toast('success', r.message);
            load(true);
          });
        });
      });

      // ---------- status ----------
      $(document).on('change', '.toggle-status', function() {
        const el = this;
        const id = $(el).data('id');
        const status = el.checked ? 1 : 0;
        el.disabled = true;
        api(apiUrl, {
            method: 'PUT',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `id_doctor=${encodeURIComponent(id)}&doctor_status=${status}`
          })
          .then(r => {
            if (r.status !== 'success') {
              el.checked = !el.checked;
              return Swal.fire('Gagal', r.message || 'Gagal mengubah status.', 'error');
            }
            const d = dokter.find(x => +x.id_doctor === +id);
            if (d) d.doctor_status = status;
            toast('success', r.message);
            renderStats();
            render();
          })
          .catch(() => {
            el.checked = !el.checked;
            Swal.fire('Gagal', 'Tidak dapat terhubung ke server.', 'error');
          })
          .finally(() => {
            el.disabled = false;
          });
      });

      load(true);
    })();
  </script>
</body>

</html>
