<?php
$title = 'Tenaga Kesehatan';
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

    .nk-stat {
      border: 1px solid #e5eaef;
      border-radius: 12px;
      background: #fff;
      padding: 14px 16px;
      height: 100%;
    }

    .nk-stat.nk-click {
      cursor: pointer;
      transition: border-color .15s;
    }

    .nk-stat.nk-click:hover {
      border-color: #5d87ff;
    }

    .nk-icon {
      width: 40px;
      height: 40px;
      border-radius: 10px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 1.05rem;
      flex-shrink: 0;
    }

    .nk-val {
      font-size: 1.35rem;
      font-weight: 700;
      line-height: 1.2;
      color: #2a3547;
    }

    .nk-label {
      font-size: .75rem;
      color: #6c757d;
    }

    .nk-ellipsis {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    /* komposisi profesi */
    .nk-stack {
      display: flex;
      height: 12px;
      border-radius: 8px;
      overflow: hidden;
      background: #eef2f6;
    }

    .nk-stack span {
      height: 100%;
    }

    .nk-prof {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      margin-top: 14px;
    }

    .nk-prof button {
      border: 1.5px solid #e5eaef;
      background: #fff;
      border-radius: 20px;
      padding: .3rem .8rem .3rem .4rem;
      font-size: .8rem;
      color: #5a6a85;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: all .15s;
    }

    .nk-prof button i.ic {
      width: 22px;
      height: 22px;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: .7rem;
      color: var(--nk-c);
      background: var(--nk-bg);
    }

    .nk-prof button b {
      color: #2a3547;
    }

    .nk-prof button:hover {
      border-color: var(--nk-c);
    }

    .nk-prof button.active {
      border-color: var(--nk-c);
      background: var(--nk-bg);
      color: var(--nk-c);
    }

    .nk-chips .btn {
      border-radius: 20px;
      font-size: .8rem;
      padding: .3rem .85rem;
    }

    .nk-search {
      width: 230px;
    }

    @media (max-width: 575.98px) {

      .nk-tools,
      .nk-search {
        width: 100%;
      }

      .nk-search {
        flex: 1;
      }
    }

    /* kartu nakes */
    .nk-card {
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

    .nk-card::before {
      content: '';
      position: absolute;
      left: 0;
      top: 0;
      right: 0;
      height: 4px;
      background: var(--nk-c);
    }

    .nk-card:hover {
      border-color: var(--nk-c);
      box-shadow: 0 6px 18px rgba(42, 53, 71, .08);
    }

    .nk-card.off {
      background: #fafbfc;
    }

    .nk-card.off .nk-head,
    .nk-card.off .nk-body {
      opacity: .55;
    }

    .nk-head {
      padding: 16px 16px 0;
      display: flex;
      gap: 12px;
      align-items: flex-start;
    }

    .nk-avatar {
      width: 48px;
      height: 48px;
      border-radius: 50%;
      flex-shrink: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 800;
      font-size: 1rem;
      color: var(--nk-c);
      background: var(--nk-bg);
      position: relative;
    }

    .nk-avatar .dot {
      position: absolute;
      right: 1px;
      bottom: 1px;
      width: 12px;
      height: 12px;
      border-radius: 50%;
      border: 2px solid #fff;
      background: #13deb9;
    }

    .nk-card.off .nk-avatar .dot {
      background: #adb5bd;
    }

    .nk-name {
      font-weight: 700;
      color: #2a3547;
      font-size: .95rem;
      line-height: 1.3;
    }

    .nk-profesi {
      font-size: .78rem;
      color: var(--nk-c);
      font-weight: 600;
    }

    .nk-tag {
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

    .nk-body {
      padding: 12px 16px 14px;
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .nk-docs {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 6px;
    }

    .nk-docs div {
      background: #f6f9fc;
      border-radius: 8px;
      padding: 6px 8px;
      min-width: 0;
    }

    .nk-docs span {
      display: block;
      font-size: .64rem;
      color: #7c8fac;
    }

    .nk-docs b {
      display: block;
      font-size: .74rem;
      color: #2a3547;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .nk-docs b.kosong {
      color: #adb5bd;
      font-weight: 500;
    }

    .nk-info {
      margin-top: 10px;
      font-size: .76rem;
      color: #5a6a85;
      display: flex;
      flex-direction: column;
      gap: 4px;
    }

    .nk-info a {
      color: inherit;
    }

    .nk-info a:hover {
      color: #13deb9;
    }

    .nk-warn {
      font-size: .72rem;
      border-radius: 8px;
      padding: 5px 8px;
      margin-top: 8px;
    }

    .nk-foot {
      border-top: 1px solid #eef2f6;
      padding: 8px 12px 8px 16px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .nk-foot .form-check-input {
      cursor: pointer;
    }

    .nk-foot .btn {
      width: 32px;
      height: 32px;
      padding: 0;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 8px;
    }

    .nk-loading {
      position: absolute;
      inset: 0;
      background: rgba(255, 255, 255, .6);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 5;
      border-radius: 12px;
    }

    .nk-empty {
      border: 1.5px dashed #dfe5ef;
      border-radius: 14px;
      padding: 40px 16px;
      text-align: center;
      color: #7c8fac;
    }

    /* pilihan profesi di form */
    .prof-opt input {
      display: none;
    }

    .prof-opt label {
      border: 1.5px solid #e5eaef;
      border-radius: 10px;
      padding: 8px 10px;
      cursor: pointer;
      width: 100%;
      display: flex;
      align-items: center;
      gap: 8px;
      font-weight: 600;
      font-size: .8rem;
      transition: all .15s;
      height: 100%;
    }

    .prof-opt label i {
      color: var(--nk-c);
      width: 16px;
      text-align: center;
    }

    .prof-opt input:checked+label {
      border-color: var(--nk-c);
      background: var(--nk-bg);
      color: var(--nk-c);
    }

    .gen-opt input {
      display: none;
    }

    .gen-opt label {
      border: 1.5px solid #e5eaef;
      border-radius: 10px;
      padding: 8px 10px;
      cursor: pointer;
      width: 100%;
      text-align: center;
      font-weight: 600;
      font-size: .82rem;
    }

    .gen-opt input:checked+label {
      border-color: #5d87ff;
      background: #ecf2ff;
      color: #5d87ff;
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
              <h4 class="fw-semibold mb-1">Tenaga Kesehatan</h4>
              <div class="text-muted small">Kelola perawat, bidan, apoteker dan tenaga kesehatan lain di faskes Anda.</div>
            </div>
            <div class="d-flex gap-2">
              <button class="btn btn-light" id="btnRefresh" title="Muat ulang"><i class="fas fa-sync-alt"></i></button>
              <button class="btn btn-primary" id="btnTambah"><i class="fas fa-plus me-1"></i> Tambah Nakes</button>
            </div>
          </div>

          <!-- STAT -->
          <div class="row g-3 mb-3">
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="nk-stat d-flex gap-3 align-items-center">
                <span class="nk-icon bg-primary-subtle text-primary"><i class="fas fa-users"></i></span>
                <div class="min-w-0">
                  <div class="nk-val" id="stAktif">-</div>
                  <div class="nk-label" id="stAktifSub">Nakes aktif</div>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="nk-stat d-flex gap-3 align-items-center">
                <span class="nk-icon bg-info-subtle text-info"><i class="fas fa-layer-group"></i></span>
                <div class="min-w-0">
                  <div class="nk-val" id="stProfesi">-</div>
                  <div class="nk-label nk-ellipsis" id="stProfesiSub">Jenis profesi</div>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="nk-stat d-flex gap-3 align-items-center">
                <span class="nk-icon bg-success-subtle text-success"><i class="fas fa-certificate"></i></span>
                <div class="min-w-0">
                  <div class="nk-val" id="stLengkap">-</div>
                  <div class="nk-label" id="stLengkapSub">STR &amp; SIP lengkap</div>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="nk-stat nk-click d-flex gap-3 align-items-center" id="stWarnCard" title="Tampilkan nakes yang perlu dilengkapi">
                <span class="nk-icon bg-danger-subtle text-danger"><i class="fas fa-exclamation-triangle"></i></span>
                <div class="min-w-0">
                  <div class="nk-val" id="stWarn">-</div>
                  <div class="nk-label" id="stWarnSub">Perlu dilengkapi</div>
                </div>
              </div>
            </div>
          </div>

          <!-- KOMPOSISI PROFESI -->
          <div class="card mb-3">
            <div class="card-body p-4">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-semibold mb-0">Komposisi profesi <span class="text-muted fw-normal small">· nakes aktif</span></h6>
                <span class="small text-muted">Klik profesi untuk memfilter</span>
              </div>
              <div class="nk-stack" id="nkStack"></div>
              <div class="nk-prof" id="nkProf"></div>
            </div>
          </div>

          <!-- DAFTAR -->
          <div class="card position-relative">
            <div class="nk-loading" id="nkLoading"><div class="spinner-border text-primary"></div></div>
            <div class="card-body p-4">
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div class="nk-chips d-flex flex-wrap gap-2" id="nkChips">
                  <button class="btn btn-primary" data-s="">Semua <span class="badge bg-white text-primary ms-1" data-n=""></span></button>
                  <button class="btn btn-light" data-s="1">Aktif <span class="badge bg-white text-dark ms-1" data-n="1"></span></button>
                  <button class="btn btn-light" data-s="0">Nonaktif <span class="badge bg-white text-dark ms-1" data-n="0"></span></button>
                  <button class="btn btn-light" data-s="warn">Perlu dilengkapi <span class="badge bg-white text-dark ms-1" data-n="warn"></span></button>
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center nk-tools">
                  <select id="nkSort" class="form-select form-select-sm" style="width:auto">
                    <option value="nama">Nama A-Z</option>
                    <option value="profesi">Profesi</option>
                    <option value="baru">Terbaru ditambahkan</option>
                  </select>
                  <div class="input-group input-group-sm nk-search">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="search" id="nkSearch" class="form-control" placeholder="Cari nama / HP / STR...">
                  </div>
                </div>
              </div>
              <div class="row g-3" id="nkGrid"></div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <!-- MODAL -->
  <div class="modal fade" id="programModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <form id="programForm" class="modal-content" novalidate>
        <div class="modal-header">
          <h5 class="modal-title d-flex align-items-center gap-2"><i class="fas fa-user-nurse text-primary"></i> <span id="nkModalTitle">Tambah Tenaga Kesehatan</span></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_nurse" id="id_nurse">
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label required" for="nurse_name">Nama Lengkap</label>
              <input type="text" id="nurse_name" name="nurse_name" class="form-control" maxlength="255" placeholder="cth. Siti Aminah, Amd.Keb" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Jenis Kelamin</label>
              <div class="row g-2">
                <div class="col-6 gen-opt"><input type="radio" name="nurse_gender" id="genL" value="L"><label for="genL"><i class="fas fa-mars me-1"></i>Laki-laki</label></div>
                <div class="col-6 gen-opt"><input type="radio" name="nurse_gender" id="genP" value="P"><label for="genP"><i class="fas fa-venus me-1"></i>Perempuan</label></div>
              </div>
            </div>
            <div class="col-12">
              <label class="form-label required">Profesi</label>
              <div class="row g-2" id="profOpts"></div>
            </div>
            <div class="col-md-5">
              <label class="form-label" for="nurse_phone">No. Handphone</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-phone-alt"></i></span>
                <input type="tel" id="nurse_phone" name="nurse_phone" class="form-control" maxlength="20" placeholder="08xxxxxxxxxx" pattern="[0-9+\-\s]{8,20}">
              </div>
            </div>
            <div class="col-md-7">
              <label class="form-label" for="nurse_address">Alamat</label>
              <input type="text" name="nurse_address" id="nurse_address" class="form-control" maxlength="255">
            </div>
          </div>
          <div class="small text-muted mt-3"><i class="fas fa-info-circle me-1"></i>STR, SIP, NIK dan tanggal berlaku diisi di halaman <b>Detail</b>.</div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary" id="nkSave"><i class="fas fa-save me-1"></i> Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <?php require 'library.php'; ?>
  <script>
    (function() {
      const apiUrl = 'controller/master/nurseController';
      // warna & ikon per profesi
      const PROFESI = {
        'Perawat': ['#5d87ff', '#ecf2ff', 'fas fa-user-nurse'],
        'Bidan': ['#e0569b', '#fdebf4', 'fas fa-baby'],
        'Apoteker': ['#13deb9', '#e6fffa', 'fas fa-prescription-bottle-alt'],
        'Farmasi': ['#3ea55d', '#e8f6ec', 'fas fa-pills'],
        'Analis Laboratorium': ['#7460ee', '#efecfd', 'fas fa-flask'],
        'Analis': ['#7460ee', '#efecfd', 'fas fa-flask'],
        'Ahli Gizi': ['#ffae1f', '#fef5e5', 'fas fa-apple-alt'],
        'Psikolog': ['#49beff', '#e8f7ff', 'fas fa-brain'],
        'Entomolog': ['#8d6e63', '#f3ece9', 'fas fa-bug'],
        'Fisioterapis': ['#fa896b', '#fdede8', 'fas fa-walking'],
        'Konselor': ['#20b2d8', '#e5f7fc', 'fas fa-comments'],
        'Lainnya': ['#7c8fac', '#f2f5f9', 'fas fa-user'],
      };
      const gaya = p => PROFESI[p] || PROFESI['Lainnya'];
      let nakes = [],
        daftarProfesi = Object.keys(PROFESI).filter(p => p !== 'Analis'),
        filterStatus = '',
        filterProfesi = '';

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
      const fmtTgl = t => new Date(t + 'T00:00:00').toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
      });
      const inisial = n => {
        const kata = String(n || '').replace(/,.*$/, '').trim().split(/\s+/).filter(Boolean);
        return ((kata[0] || '?')[0] + (kata.length > 1 ? kata[kata.length - 1][0] : '')).toUpperCase();
      };
      const waLink = hp => {
        let n = String(hp || '').replace(/\D/g, '');
        if (n.startsWith('0')) n = '62' + n.slice(1);
        return n.length >= 9 ? `https://wa.me/${n}` : '';
      };
      const aktif = n => +n.nurse_status === 1;

      // kelengkapan dokumen: STR, SIP, masa berlaku SIP
      function catatan(n) {
        const out = [];
        const exp = n.nurse_expaired;
        if (exp && /^\d{4}-\d{2}-\d{2}$/.test(exp)) {
          const sisa = Math.round((new Date(exp + 'T00:00:00') - new Date(hariIni + 'T00:00:00')) / 864e5);
          if (sisa < 0) out.push(['danger', `SIP kedaluwarsa sejak ${fmtTgl(exp)}`]);
          else if (sisa <= 60) out.push(['warning', `SIP berakhir ${fmtTgl(exp)} (${sisa} hari lagi)`]);
        }
        const kurang = [!n.nurse_str && 'STR', !n.nurse_sip && 'SIP'].filter(Boolean);
        if (kurang.length) out.push(['warning', `${kurang.join(' & ')} belum diisi`]);
        return out;
      }

      function load() {
        $('#nkLoading').css('display', 'flex');
        api(apiUrl).then(r => {
          if (r.status !== 'success') return Swal.fire('Gagal', r.message || 'Gagal memuat data.', 'error');
          nakes = r.data || [];
          if (Array.isArray(r.profesi)) daftarProfesi = r.profesi.filter(p => p !== 'Analis');
          renderStats();
          renderProfesi();
          render();
        }).catch(() => Swal.fire('Gagal', 'Tidak dapat terhubung ke server.', 'error'))
          .finally(() => $('#nkLoading').css('display', 'none'));
      }

      function hitungProfesi(list) {
        const m = {};
        list.forEach(n => {
          const p = n.nurse_profesi || 'Lainnya';
          m[p] = (m[p] || 0) + 1;
        });
        return Object.entries(m).sort((a, b) => b[1] - a[1]);
      }

      function renderStats() {
        const on = nakes.filter(aktif);
        const prof = hitungProfesi(on);
        const lengkap = on.filter(n => n.nurse_str && n.nurse_sip).length;
        const warn = on.filter(n => catatan(n).length).length;
        $('#stAktif').text(fmt(on.length));
        $('#stAktifSub').text(`Nakes aktif dari ${fmt(nakes.length)}`);
        $('#stProfesi').text(fmt(prof.length));
        $('#stProfesiSub').text(prof.length ? prof.slice(0, 3).map(([p, c]) => `${p} ${c}`).join(' · ') : 'Jenis profesi');
        $('#stLengkap').text(fmt(lengkap));
        $('#stLengkapSub').text(`STR & SIP lengkap · ${on.length ? Math.round(lengkap / on.length * 100) : 0}%`);
        $('#stWarn').text(fmt(warn));
        $('#stWarnSub').text(warn ? 'Nakes aktif perlu dilengkapi' : 'Dokumen nakes aktif lengkap');
        $('#nkChips [data-n=""]').text(nakes.length);
        $('#nkChips [data-n="1"]').text(on.length);
        $('#nkChips [data-n="0"]').text(nakes.length - on.length);
        $('#nkChips [data-n="warn"]').text(warn);
      }

      function renderProfesi() {
        const prof = hitungProfesi(nakes.filter(aktif));
        const total = prof.reduce((a, [, c]) => a + c, 0);
        $('#nkStack').html(prof.map(([p, c]) => `<span style="width:${c / total * 100}%;background:${gaya(p)[0]}" title="${esc(p)}: ${c}"></span>`).join(''));
        const semua = `<button type="button" data-p="" class="${filterProfesi === '' ? 'active' : ''}" style="--nk-c:#5d87ff;--nk-bg:#ecf2ff"><i class="ic fas fa-users"></i>Semua <b>${fmt(nakes.length)}</b></button>`;
        const semuaProf = hitungProfesi(nakes);
        $('#nkProf').html(semua + semuaProf.map(([p, c]) => {
          const [col, bg, ic] = gaya(p);
          return `<button type="button" data-p="${esc(p)}" class="${filterProfesi === p ? 'active' : ''}" style="--nk-c:${col};--nk-bg:${bg}"><i class="ic ${ic}"></i>${esc(p)} <b>${fmt(c)}</b></button>`;
        }).join(''));
        if (!nakes.length) $('#nkProf').html('<span class="text-muted small">Belum ada data.</span>');
      }

      function render() {
        const q = $('#nkSearch').val().trim().toLowerCase();
        const sort = $('#nkSort').val();
        const coll = new Intl.Collator('id', {
          sensitivity: 'base'
        });
        const list = nakes.filter(n =>
          (filterStatus === '' || (filterStatus === 'warn' ? aktif(n) && catatan(n).length > 0 : String(+n.nurse_status) === filterStatus)) &&
          (filterProfesi === '' || (n.nurse_profesi || 'Lainnya') === filterProfesi) &&
          (!q || [n.nurse_name, n.nurse_phone, n.nurse_str, n.nurse_sip, n.nurse_profesi].some(v => String(v || '').toLowerCase().includes(q)))
        ).sort((a, b) => {
          if (sort === 'baru') return +b.id_nurse - +a.id_nurse;
          const nama = coll.compare(a.nurse_name || '', b.nurse_name || '');
          if (sort === 'profesi') return coll.compare(a.nurse_profesi || '', b.nurse_profesi || '') || nama;
          return (+b.nurse_status - +a.nurse_status) || nama;
        });

        if (!nakes.length) {
          $('#nkGrid').html(`<div class="col-12"><div class="nk-empty"><i class="fas fa-user-nurse fa-2x mb-2 d-block"></i>Belum ada tenaga kesehatan. Klik <b>Tambah Nakes</b> untuk memulai.</div></div>`);
          return;
        }
        $('#nkGrid').html(list.length ? list.map(kartu).join('') : `<div class="col-12"><div class="nk-empty">Tenaga kesehatan tidak ditemukan.</div></div>`);
      }

      function kartu(n) {
        const [c, bg, ic] = gaya(n.nurse_profesi);
        const on = aktif(n);
        const wa = waLink(n.nurse_phone);
        const notes = on ? catatan(n) : [];
        const gen = n.nurse_gender === 'L' ? '<span class="nk-tag"><i class="fas fa-mars"></i>Laki-laki</span>' :
          n.nurse_gender === 'P' ? '<span class="nk-tag"><i class="fas fa-venus"></i>Perempuan</span>' : '';
        const dok = (label, v) => `<div title="${esc(v || '')}"><span>${label}</span><b class="${v ? '' : 'kosong'}">${esc(v || 'Belum diisi')}</b></div>`;
        return `
        <div class="col-sm-6 col-lg-4 col-xxl-3">
          <div class="nk-card ${on ? '' : 'off'}" style="--nk-c:${c};--nk-bg:${bg}">
            <div class="nk-head">
              <div class="nk-avatar">${esc(inisial(n.nurse_name))}<span class="dot" title="${on ? 'Aktif' : 'Nonaktif'}"></span></div>
              <div class="min-w-0">
                <div class="nk-name">${esc(n.nurse_name || '-')}</div>
                <div class="nk-profesi"><i class="${ic} me-1"></i>${esc(n.nurse_profesi || 'Lainnya')}</div>
                ${gen}${n.nurse_str && n.nurse_sip ? '<span class="nk-tag bg-success-subtle text-success"><i class="fas fa-check-circle"></i>Dokumen lengkap</span>' : ''}
              </div>
            </div>
            <div class="nk-body">
              <div class="nk-docs">
                ${dok('STR', n.nurse_str)}
                ${dok('SIP', n.nurse_sip)}
              </div>
              <div class="nk-info">
                ${n.nurse_expaired ? `<div><i class="fas fa-calendar-check me-2 text-muted"></i>SIP berlaku s.d. ${esc(fmtTgl(n.nurse_expaired))}</div>` : ''}
                <div class="nk-ellipsis"><i class="fas fa-phone-alt me-2 text-muted"></i>${n.nurse_phone
                  ? (wa ? `<a href="${wa}" target="_blank" rel="noopener" title="Chat WhatsApp">${esc(n.nurse_phone)} <i class="fab fa-whatsapp text-success"></i></a>` : esc(n.nurse_phone))
                  : '<span class="text-muted">Belum ada nomor HP</span>'}</div>
                ${n.nurse_address ? `<div class="nk-ellipsis" title="${esc(n.nurse_address)}"><i class="fas fa-map-marker-alt me-2 text-muted"></i>${esc(n.nurse_address)}</div>` : ''}
              </div>
              ${notes.map(([cls, txt]) => `<div class="nk-warn bg-${cls}-subtle text-${cls}-emphasis"><i class="fas fa-exclamation-circle me-1"></i>${esc(txt)}</div>`).join('')}
            </div>
            <div class="nk-foot">
              <div class="form-check form-switch mb-0">
                <input class="form-check-input toggle-status" type="checkbox" role="switch" id="sw${n.id_nurse}" data-id="${n.id_nurse}" ${on ? 'checked' : ''}>
                <label class="form-check-label small ${on ? 'text-success' : 'text-muted'}" for="sw${n.id_nurse}">${on ? 'Aktif' : 'Nonaktif'}</label>
              </div>
              <div class="d-flex gap-1">
                <a class="btn btn-light text-info" href="module/admin/nurse_details?no=${encodeURIComponent(n.nurse_number || '')}" title="Detail: STR, SIP, NIK"><i class="fas fa-id-badge"></i></a>
                <button class="btn btn-light text-primary edit-btn" data-id="${n.id_nurse}" title="Ubah"><i class="fas fa-pen"></i></button>
                <button class="btn btn-light text-danger delete-btn" data-id="${n.id_nurse}" title="Hapus"><i class="fas fa-trash"></i></button>
              </div>
            </div>
          </div>
        </div>`;
      }

      // ---------- filter ----------
      function pilihChip(s) {
        filterStatus = s;
        $('#nkChips .btn').each(function() {
          const on = String($(this).data('s')) === s;
          $(this).toggleClass('btn-primary', on).toggleClass('btn-light', !on)
            .find('.badge').toggleClass('text-primary', on).toggleClass('text-dark', !on);
        });
        render();
      }
      $('#nkChips').on('click', '.btn', function() {
        pilihChip(String($(this).data('s')));
      });
      $('#stWarnCard').on('click', () => pilihChip('warn'));
      $('#nkProf').on('click', 'button', function() {
        filterProfesi = String($(this).data('p'));
        renderProfesi();
        render();
      });
      $('#nkSearch').on('input', render);
      $('#nkSort').on('change', render);
      $('#btnRefresh').on('click', load);

      // ---------- form ----------
      function isiOpsiProfesi(nilai) {
        const opsi = [...daftarProfesi];
        if (nilai && !opsi.includes(nilai)) opsi.push(nilai);
        $('#profOpts').html(opsi.map((p, i) => {
          const [c, bg, ic] = gaya(p);
          return `<div class="col-6 col-md-3 prof-opt" style="--nk-c:${c};--nk-bg:${bg}">
            <input type="radio" name="nurse_profesi" id="prof${i}" value="${esc(p)}" ${p === nilai ? 'checked' : ''}>
            <label for="prof${i}"><i class="${ic}"></i>${esc(p)}</label>
          </div>`;
        }).join(''));
      }

      function bukaForm(n) {
        const f = $('#programForm')[0];
        f.reset();
        f.classList.remove('was-validated');
        $('#id_nurse').val(n ? n.id_nurse : '');
        isiOpsiProfesi(n ? n.nurse_profesi : (filterProfesi || ''));
        if (n) {
          $('#nurse_name').val(n.nurse_name || '');
          $('#nurse_phone').val(n.nurse_phone || '');
          $('#nurse_address').val(n.nurse_address || '');
          $(`input[name="nurse_gender"][value="${n.nurse_gender}"]`).prop('checked', true);
        }
        $('#nkModalTitle').text(n ? 'Ubah Tenaga Kesehatan' : 'Tambah Tenaga Kesehatan');
        $('#programModal').modal('show');
      }
      $('#programModal').on('shown.bs.modal', () => $('#nurse_name').trigger('focus'));
      $('#btnTambah').on('click', () => bukaForm(null));
      $(document).on('click', '.edit-btn', function() {
        const n = nakes.find(x => +x.id_nurse === +$(this).data('id'));
        if (n) bukaForm(n);
      });

      $('#programForm').on('submit', function(e) {
        e.preventDefault();
        this.classList.add('was-validated');
        if (!this.checkValidity()) return;
        if (!$('input[name="nurse_profesi"]:checked').length) return Swal.fire('Lengkapi data', 'Pilih profesi.', 'warning');
        const id = $('#id_nurse').val();
        const body = new URLSearchParams(new FormData(this));
        if (!body.has('nurse_gender')) body.set('nurse_gender', '');
        const $btn = $('#nkSave').prop('disabled', true);
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
            load();
          })
          .catch(() => Swal.fire('Gagal', 'Tidak dapat terhubung ke server.', 'error'))
          .finally(() => $btn.prop('disabled', false));
      });

      // ---------- hapus ----------
      $(document).on('click', '.delete-btn', function() {
        const id = $(this).data('id');
        const n = nakes.find(x => +x.id_nurse === +id) || {};
        Swal.fire({
          title: 'Hapus tenaga kesehatan?',
          html: `<b>${esc(n.nurse_name || '')}</b> (${esc(n.nurse_profesi || '-')}) akan dihapus beserta data STR/SIP-nya.` +
            '<div class="small text-muted mt-2">Jika hanya tidak bertugas sementara, sebaiknya <b>nonaktifkan</b> saja.</div>',
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
            load();
          });
        });
      });

      // ---------- status ----------
      $(document).on('change', '.toggle-status', function() {
        const el = this;
        const id = $(el).data('id');
        const status = el.checked ? 1 : 0;
        el.disabled = true;
        api(apiUrl + '?toggle_status=1', {
            method: 'PUT',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `id_nurse=${encodeURIComponent(id)}&nurse_status=${status}`
          })
          .then(r => {
            if (r.status !== 'success') {
              el.checked = !el.checked;
              return Swal.fire('Gagal', r.message || 'Gagal mengubah status.', 'error');
            }
            const n = nakes.find(x => +x.id_nurse === +id);
            if (n) n.nurse_status = status;
            toast('success', r.message);
            renderStats();
            renderProfesi();
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

      load();
    })();
  </script>
</body>

</html>
