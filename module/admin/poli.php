<?php
$title = 'Poliklinik';
require '../../controller/view.php';
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .pl-stat {
      border: 1px solid #e5eaef;
      border-radius: 12px;
      background: #fff;
      padding: 14px 16px;
      height: 100%;
    }

    .pl-icon {
      width: 40px;
      height: 40px;
      border-radius: 10px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 1.05rem;
      flex-shrink: 0;
    }

    .pl-val {
      font-size: 1.35rem;
      font-weight: 700;
      line-height: 1.2;
      color: #2a3547;
    }

    .pl-label {
      font-size: .75rem;
      color: #6c757d;
    }

    .min-w-0 {
      min-width: 0;
    }

    .pl-ellipsis {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .pl-chips .btn {
      border-radius: 20px;
      font-size: .8rem;
      padding: .3rem .85rem;
    }

    /* kartu poli */
    .pl-card {
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

    .pl-card::before {
      content: '';
      position: absolute;
      left: 0;
      top: 0;
      right: 0;
      height: 4px;
      background: var(--pl-c);
    }

    .pl-card:hover {
      border-color: var(--pl-c);
      box-shadow: 0 6px 18px rgba(42, 53, 71, .08);
    }

    .pl-card.off {
      background: #fafbfc;
    }

    .pl-card.off .pl-body {
      opacity: .55;
    }

    .pl-head {
      padding: 16px 16px 0;
      display: flex;
      gap: 12px;
      align-items: flex-start;
    }

    .pl-avatar {
      width: 46px;
      height: 46px;
      border-radius: 12px;
      flex-shrink: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 800;
      font-size: 1.05rem;
      color: var(--pl-c);
      background: var(--pl-bg);
    }

    .pl-name {
      font-weight: 700;
      color: #2a3547;
      font-size: .95rem;
      line-height: 1.3;
    }

    .pl-tag {
      display: inline-block;
      font-size: .7rem;
      font-weight: 600;
      padding: .2em .55em;
      border-radius: 6px;
      background: #f2f5f9;
      color: #5a6a85;
      margin-top: 4px;
      margin-right: 4px;
    }

    .pl-body {
      padding: 14px 16px 16px;
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .pl-today {
      display: flex;
      align-items: flex-end;
      justify-content: space-between;
      gap: 10px;
    }

    .pl-big {
      font-size: 2rem;
      font-weight: 800;
      line-height: 1;
      color: #2a3547;
    }

    .pl-big small {
      font-size: .75rem;
      font-weight: 500;
      color: #7c8fac;
      margin-left: 4px;
    }

    .pl-spark {
      display: flex;
      align-items: flex-end;
      gap: 3px;
      height: 34px;
    }

    .pl-spark span {
      width: 7px;
      border-radius: 3px 3px 0 0;
      background: var(--pl-bg2);
      min-height: 3px;
    }

    .pl-spark span.now {
      background: var(--pl-c);
    }

    .pl-prog {
      height: 6px;
      border-radius: 6px;
      background: #eef2f6;
      overflow: hidden;
      margin: 12px 0 6px;
    }

    .pl-prog span {
      display: block;
      height: 100%;
      background: #13deb9;
      border-radius: 6px;
      transition: width .3s;
    }

    .pl-progtxt {
      font-size: .72rem;
      color: #7c8fac;
    }

    .pl-mini {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 6px;
      margin-top: 12px;
    }

    .pl-mini div {
      background: #f6f9fc;
      border-radius: 8px;
      padding: 6px 4px;
      text-align: center;
    }

    .pl-mini b {
      display: block;
      font-size: .9rem;
      color: #2a3547;
    }

    .pl-mini span {
      font-size: .66rem;
      color: #7c8fac;
    }

    .pl-foot {
      border-top: 1px solid #eef2f6;
      padding: 8px 12px 8px 16px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .pl-foot .form-check-input {
      cursor: pointer;
    }

    .pl-foot .btn {
      width: 32px;
      height: 32px;
      padding: 0;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 8px;
    }

    .pl-rank {
      position: absolute;
      top: 12px;
      right: 12px;
      font-size: .68rem;
      font-weight: 700;
      padding: .25em .6em;
      border-radius: 20px;
      background: #fff4e5;
      color: #e08a00;
    }

    .pl-lain td {
      font-size: .82rem;
      padding: .45rem .6rem;
    }

    .pl-search {
      width: 220px;
    }

    @media (max-width: 575.98px) {
      .pl-tools,
      .pl-search {
        width: 100%;
      }

      .pl-search {
        flex: 1;
      }
    }

    .pl-loading {
      position: absolute;
      inset: 0;
      background: rgba(255, 255, 255, .6);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 5;
      border-radius: 12px;
    }

    .pl-empty {
      border: 1.5px dashed #dfe5ef;
      border-radius: 14px;
      padding: 40px 16px;
      text-align: center;
      color: #7c8fac;
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
              <h4 class="fw-semibold mb-1">Poliklinik</h4>
              <div class="text-muted small">Kelola poliklinik dan pantau jumlah pasien di setiap poli.</div>
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center">
              <div class="input-group flex-nowrap" style="width:auto">
                <button class="btn btn-outline-secondary" id="tglPrev" title="Hari sebelumnya"><i class="fas fa-chevron-left"></i></button>
                <input type="date" id="plTanggal" class="form-control" style="width:160px">
                <button class="btn btn-outline-secondary" id="tglNext" title="Hari berikutnya"><i class="fas fa-chevron-right"></i></button>
              </div>
              <button class="btn btn-outline-primary d-none" id="tglHariIni">Hari ini</button>
              <button class="btn btn-light" id="btnRefresh" title="Muat ulang"><i class="fas fa-sync-alt"></i></button>
              <button class="btn btn-primary" id="btnTambah"><i class="fas fa-plus me-1"></i> Tambah Poli</button>
            </div>
          </div>

          <!-- STAT -->
          <div class="row g-3 mb-3">
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="pl-stat d-flex gap-3 align-items-center">
                <span class="pl-icon bg-primary-subtle text-primary"><i class="fas fa-clinic-medical"></i></span>
                <div class="min-w-0">
                  <div class="pl-val" id="stPoli">-</div>
                  <div class="pl-label" id="stPoliSub">Poli aktif</div>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="pl-stat d-flex gap-3 align-items-center">
                <span class="pl-icon bg-success-subtle text-success"><i class="fas fa-user-friends"></i></span>
                <div class="min-w-0">
                  <div class="pl-val" id="stHari">-</div>
                  <div class="pl-label" id="stHariSub">Pasien hari ini</div>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="pl-stat d-flex gap-3 align-items-center">
                <span class="pl-icon bg-info-subtle text-info"><i class="fas fa-calendar-alt"></i></span>
                <div class="min-w-0">
                  <div class="pl-val" id="stBulan">-</div>
                  <div class="pl-label" id="stBulanSub">Pasien bulan ini</div>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="pl-stat d-flex gap-3 align-items-center">
                <span class="pl-icon bg-warning-subtle text-warning"><i class="fas fa-fire"></i></span>
                <div class="min-w-0">
                  <div class="pl-val pl-ellipsis" id="stTop" style="font-size:1.05rem">-</div>
                  <div class="pl-label" id="stTopSub">Poli tersibuk</div>
                </div>
              </div>
            </div>
          </div>

          <!-- DAFTAR POLI -->
          <div class="card position-relative mb-3">
            <div class="pl-loading" id="plLoading"><div class="spinner-border text-primary"></div></div>
            <div class="card-body p-4">
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div class="pl-chips d-flex flex-wrap gap-2" id="plChips">
                  <button class="btn btn-primary" data-s="">Semua <span class="badge bg-white text-primary ms-1" data-n=""></span></button>
                  <button class="btn btn-light" data-s="1">Aktif <span class="badge bg-white text-dark ms-1" data-n="1"></span></button>
                  <button class="btn btn-light" data-s="0">Nonaktif <span class="badge bg-white text-dark ms-1" data-n="0"></span></button>
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center pl-tools">
                  <select id="plSort" class="form-select form-select-sm" style="width:auto">
                    <option value="pasien">Pasien terbanyak</option>
                    <option value="nama">Nama A-Z</option>
                    <option value="kode">Kode</option>
                  </select>
                  <div class="input-group input-group-sm pl-search">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="search" id="plSearch" class="form-control" placeholder="Cari poli / kode...">
                  </div>
                </div>
              </div>
              <div class="row g-3" id="plGrid"></div>
            </div>
          </div>

          <!-- KUNJUNGAN POLI TIDAK TERDAFTAR -->
          <div class="card d-none" id="plLainCard">
            <div class="card-body p-4">
              <div class="d-flex align-items-center gap-2 mb-1">
                <i class="fas fa-info-circle text-warning"></i>
                <h6 class="fw-semibold mb-0">Kunjungan di poli yang tidak ada di master</h6>
              </div>
              <div class="text-muted small mb-3">Nama poli pada kunjungan ini (mis. dari PCare) tidak cocok dengan data poliklinik di atas, sehingga tidak terhitung di kartu poli.</div>
              <div class="table-responsive">
                <table class="table table-sm align-middle mb-0 pl-lain">
                  <thead class="table-light">
                    <tr>
                      <th>Nama poli pada kunjungan</th>
                      <th class="text-center">Hari ini</th>
                      <th class="text-center">Selesai</th>
                      <th class="text-center">Batal</th>
                      <th class="text-center">Bulan ini</th>
                    </tr>
                  </thead>
                  <tbody id="plLainBody"></tbody>
                </table>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <!-- MODAL POLI -->
  <div class="modal fade" id="programModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <form id="programForm" class="modal-content" novalidate>
        <div class="modal-header">
          <h5 class="modal-title d-flex align-items-center gap-2"><i class="fas fa-clinic-medical text-primary"></i> <span id="plModalTitle">Tambah Poliklinik</span></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_poli" id="id_poli">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label required" for="poli_name">Nama Poliklinik</label>
              <input type="text" id="poli_name" name="poli_name" class="form-control" maxlength="100" placeholder="cth. POLI UMUM" required>
            </div>
            <div class="col-6">
              <label class="form-label required" for="poli_code">Kode Poli</label>
              <input type="text" id="poli_code" name="poli_code" class="form-control" maxlength="100" placeholder="cth. 001" required>
              <div class="form-text">Samakan dengan kode poli PCare bila pasien BPJS.</div>
            </div>
            <div class="col-6">
              <label class="form-label required" for="poli_queue">Kode Antrean</label>
              <input type="text" id="poli_queue" name="poli_queue" class="form-control" maxlength="100" placeholder="cth. A" required>
              <div class="form-text">Awalan nomor antrean, mis. <b>A</b>-001.</div>
            </div>
          </div>
          <div class="d-flex align-items-center gap-3 mt-3 p-3 rounded-3 bg-light">
            <div class="pl-avatar" id="plPreviewAv" style="--pl-c:#5d87ff;--pl-bg:#ecf2ff">A</div>
            <div class="min-w-0">
              <div class="small text-muted">Pratinjau nomor antrean</div>
              <div class="fw-bold fs-5" id="plPreviewNo">A-001</div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary" id="plSave"><i class="fas fa-save me-1"></i> Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <?php require 'library.php'; ?>
  <script>
    (function() {
      const apiUrl = 'controller/master/poliController';
      const WARNA = [
        ['#5d87ff', '#ecf2ff', '#c9d7ff'],
        ['#13deb9', '#e6fffa', '#b5f3e6'],
        ['#ffae1f', '#fef5e5', '#ffe1ad'],
        ['#fa896b', '#fdede8', '#fcd0c4'],
        ['#7460ee', '#efecfd', '#d4cdfa'],
        ['#20b2d8', '#e5f7fc', '#b3e6f3'],
        ['#e0569b', '#fdebf4', '#f6c3dc'],
        ['#3ea55d', '#e8f6ec', '#bfe5ca'],
      ];
      let polis = [],
        stats = {},
        hari = [],
        filterStatus = '';

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
      const inisial = p => {
        const q = String(p.poli_queue || '').replace(/[^A-Za-z0-9]/g, '');
        if (q) return q.slice(0, 3).toUpperCase();
        return String(p.poli_name || '?').replace(/^poli(klinik)?\s+/i, '').trim().slice(0, 2).toUpperCase() || '?';
      };
      const warna = p => WARNA[(Number(p.id_poli) || 0) % WARNA.length];
      const labelTgl = t => t === hariIni ? 'hari ini' : new Date(t + 'T00:00:00').toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
      });

      const $tgl = $('#plTanggal').val(hariIni).attr('max', hariIni);

      function loading(on) {
        $('#plLoading').css('display', on ? 'flex' : 'none');
      }

      function load(withList = true) {
        loading(true);
        const tgl = $tgl.val() || hariIni;
        const reqs = [api(`${apiUrl}?stats=1&tanggal=${encodeURIComponent(tgl)}`)];
        if (withList) reqs.push(api(apiUrl));
        Promise.all(reqs).then(([s, l]) => {
          if (l) {
            if (l.status !== 'success') return Swal.fire('Gagal', l.message || 'Gagal memuat poliklinik.', 'error');
            polis = l.data || [];
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
        }).catch(() => Swal.fire('Gagal', 'Tidak dapat terhubung ke server.', 'error')).finally(() => loading(false));
      }

      function renderStats() {
        const tgl = $tgl.val() || hariIni;
        const aktif = polis.filter(p => +p.poli_status === 1).length;
        let hariN = 0,
          selesai = 0,
          antre = 0,
          bulan = 0,
          top = null;
        polis.forEach(p => {
          const s = st(p.id_poli);
          hariN += s.hari_ini;
          selesai += s.selesai;
          antre += s.antre;
          bulan += s.bulan;
          if (s.hari_ini > 0 && (!top || s.hari_ini > st(top.id_poli).hari_ini)) top = p;
        });
        $('#stPoli').text(fmt(aktif));
        $('#stPoliSub').text(`Poli aktif dari ${fmt(polis.length)} poli`);
        $('#stHari').text(fmt(hariN));
        $('#stHariSub').html(`Pasien ${esc(labelTgl(tgl))} · <span class="text-success">${fmt(selesai)} selesai</span>${antre ? ` · <span class="text-warning">${fmt(antre)} belum</span>` : ''}`);
        $('#stBulan').text(fmt(bulan));
        $('#stBulanSub').text(`Pasien ${new Date(tgl + 'T00:00:00').toLocaleDateString('id-ID', { month: 'long', year: 'numeric' })}${tgl === hariIni ? '' : ' (s.d. tanggal dipilih)'}`);
        $('#stTop').text(top ? top.poli_name : '-').attr('title', top ? top.poli_name : '');
        $('#stTopSub').text(top ? `${fmt(st(top.id_poli).hari_ini)} pasien ${labelTgl(tgl)}` : `Belum ada pasien ${labelTgl(tgl)}`);

        $('#tglHariIni').toggleClass('d-none', tgl === hariIni);
        $('#tglNext').prop('disabled', tgl >= hariIni);
        $('#plChips [data-n=""]').text(polis.length);
        $('#plChips [data-n="1"]').text(aktif);
        $('#plChips [data-n="0"]').text(polis.length - aktif);
      }

      function render() {
        const q = $('#plSearch').val().trim().toLowerCase();
        const sort = $('#plSort').val();
        const coll = new Intl.Collator('id', {
          numeric: true,
          sensitivity: 'base'
        });
        let list = polis.filter(p =>
          (filterStatus === '' || String(+p.poli_status) === filterStatus) &&
          (!q || [p.poli_name, p.poli_code, p.poli_queue].some(v => String(v || '').toLowerCase().includes(q)))
        );
        list.sort((a, b) => {
          if (sort === 'pasien') return (+b.poli_status - +a.poli_status) || (st(b.id_poli).hari_ini - st(a.id_poli).hari_ini) || (st(b.id_poli).bulan - st(a.id_poli).bulan) || coll.compare(a.poli_name, b.poli_name);
          if (sort === 'kode') return coll.compare(a.poli_code || '', b.poli_code || '');
          return coll.compare(a.poli_name || '', b.poli_name || '');
        });

        const maxHari = Math.max(0, ...polis.map(p => st(p.id_poli).hari_ini));
        if (!polis.length) {
          $('#plGrid').html(`<div class="col-12"><div class="pl-empty"><i class="fas fa-clinic-medical fa-2x mb-2 d-block"></i>Belum ada poliklinik. Klik <b>Tambah Poli</b> untuk memulai.</div></div>`);
          return;
        }
        if (!list.length) {
          $('#plGrid').html(`<div class="col-12"><div class="pl-empty">Poliklinik tidak ditemukan.</div></div>`);
          return;
        }
        $('#plGrid').html(list.map(p => kartu(p, maxHari)).join(''));
      }

      function kartu(p, maxHari) {
        const s = st(p.id_poli);
        const [c, bg, bg2] = warna(p);
        const on = +p.poli_status === 1;
        const pct = s.hari_ini ? Math.round(s.selesai / s.hari_ini * 100) : 0;
        const maxT = Math.max(1, ...s.tren);
        const spark = s.tren.map((n, i) => {
          const t = hari[i] ? new Date(hari[i] + 'T00:00:00').toLocaleDateString('id-ID', {
            weekday: 'short',
            day: 'numeric',
            month: 'short'
          }) : '';
          return `<span class="${i === 6 ? 'now' : ''}" style="height:${Math.max(8, Math.round(n / maxT * 100))}%" title="${esc(t)}: ${fmt(n)} pasien"></span>`;
        }).join('');
        const rank = on && maxHari > 0 && s.hari_ini === maxHari ? '<span class="pl-rank"><i class="fas fa-fire me-1"></i>Tersibuk</span>' : '';

        return `
        <div class="col-sm-6 col-lg-4 col-xxl-3">
          <div class="pl-card ${on ? '' : 'off'}" style="--pl-c:${c};--pl-bg:${bg};--pl-bg2:${bg2}">
            ${rank}
            <div class="pl-head pl-body" style="padding-bottom:0;flex:none">
              <div class="pl-avatar">${esc(inisial(p))}</div>
              <div class="min-w-0" style="padding-right:${rank ? '74px' : '0'}">
                <div class="pl-name">${esc(p.poli_name || '-')}</div>
                <span class="pl-tag" title="Kode poli"><i class="fas fa-hashtag me-1"></i>${esc(p.poli_code || '-')}</span>
                <span class="pl-tag" title="Kode antrean"><i class="fas fa-ticket-alt me-1"></i>${esc(p.poli_queue || '-')}</span>
              </div>
            </div>
            <div class="pl-body">
              <div class="pl-today">
                <div>
                  <div class="pl-label mb-1">Pasien ${esc(labelTgl($tgl.val() || hariIni))}</div>
                  <div class="pl-big">${fmt(s.hari_ini)}<small>pasien</small></div>
                </div>
                <div class="text-end">
                  <div class="pl-spark" title="7 hari terakhir">${spark}</div>
                  <div class="pl-label" style="font-size:.65rem">7 hari</div>
                </div>
              </div>
              <div class="pl-prog"><span style="width:${pct}%"></span></div>
              <div class="pl-progtxt">${s.hari_ini ? `${pct}% selesai dilayani` : 'Belum ada kunjungan'}</div>
              <div class="pl-mini">
                <div><b class="text-warning">${fmt(s.antre)}</b><span>Antre</span></div>
                <div><b class="text-success">${fmt(s.selesai)}</b><span>Selesai</span></div>
                <div><b class="text-danger">${fmt(s.batal)}</b><span>Batal</span></div>
                <div><b>${fmt(s.bulan)}</b><span>Bulan ini</span></div>
              </div>
            </div>
            <div class="pl-foot">
              <div class="form-check form-switch mb-0">
                <input class="form-check-input toggle-status" type="checkbox" role="switch" id="sw${p.id_poli}" data-id="${p.id_poli}" ${on ? 'checked' : ''}>
                <label class="form-check-label small ${on ? 'text-success' : 'text-muted'}" for="sw${p.id_poli}">${on ? 'Aktif' : 'Nonaktif'}</label>
              </div>
              <div class="d-flex gap-1">
                <button class="btn btn-light text-primary edit-btn" data-id="${p.id_poli}" title="Ubah"><i class="fas fa-pen"></i></button>
                ${+p.jml_kunjungan > 0
                  ? `<span class="d-inline-block" tabindex="0" title="Tidak bisa dihapus: sudah ada ${fmt(p.jml_kunjungan)} data kunjungan pasien. Nonaktifkan bila tidak dipakai."><button class="btn btn-light text-danger" disabled style="pointer-events:none"><i class="fas fa-trash"></i></button></span>`
                  : `<button class="btn btn-light text-danger delete-btn" data-id="${p.id_poli}" title="Hapus"><i class="fas fa-trash"></i></button>`}
              </div>
            </div>
          </div>
        </div>`;
      }

      function renderLain(rows) {
        $('#plLainCard').toggleClass('d-none', !rows.length);
        $('#plLainBody').html(rows.map(r => `
          <tr>
            <td class="fw-semibold">${esc(r.nama)}</td>
            <td class="text-center">${fmt(r.hari_ini)}</td>
            <td class="text-center text-success">${fmt(r.selesai)}</td>
            <td class="text-center text-danger">${fmt(r.batal)}</td>
            <td class="text-center">${fmt(r.bulan)}</td>
          </tr>`).join(''));
      }

      // ---------- filter & tanggal ----------
      $('#plChips').on('click', '.btn', function() {
        filterStatus = String($(this).data('s'));
        $('#plChips .btn').removeClass('btn-primary').addClass('btn-light')
          .find('.badge').removeClass('text-primary').addClass('text-dark');
        $(this).removeClass('btn-light').addClass('btn-primary').find('.badge').removeClass('text-dark').addClass('text-primary');
        render();
      });
      $('#plSearch').on('input', render);
      $('#plSort').on('change', render);
      $tgl.on('change', () => {
        if (!$tgl.val() || $tgl.val() > hariIni) $tgl.val(hariIni);
        load(false);
      });
      const geser = n => {
        const d = new Date(($tgl.val() || hariIni) + 'T00:00:00');
        d.setDate(d.getDate() + n);
        const v = ymd(d);
        $tgl.val(v > hariIni ? hariIni : v).trigger('change');
      };
      $('#tglPrev').on('click', () => geser(-1));
      $('#tglNext').on('click', () => geser(1));
      $('#tglHariIni').on('click', () => $tgl.val(hariIni).trigger('change'));
      $('#btnRefresh').on('click', () => load(true));

      // ---------- form ----------
      function preview() {
        const q = $('#poli_queue').val().trim() || 'A';
        $('#plPreviewNo').text(`${q.replace(/-+$/, '')}-001`);
        $('#plPreviewAv').text(q.replace(/[^A-Za-z0-9]/g, '').slice(0, 3).toUpperCase() || 'A');
      }
      $('#poli_queue').on('input', preview);

      function bukaForm(p) {
        const f = $('#programForm')[0];
        f.reset();
        f.classList.remove('was-validated');
        $('#id_poli').val(p ? p.id_poli : '');
        if (p) {
          $('#poli_name').val(p.poli_name || '');
          $('#poli_code').val(p.poli_code || '');
          $('#poli_queue').val(p.poli_queue || '');
        }
        $('#plModalTitle').text(p ? 'Ubah Poliklinik' : 'Tambah Poliklinik');
        preview();
        $('#programModal').modal('show');
      }
      $('#programModal').on('shown.bs.modal', () => $('#poli_name').trigger('focus'));
      $('#btnTambah').on('click', () => bukaForm(null));

      $(document).on('click', '.edit-btn', function() {
        const id = $(this).data('id');
        const p = polis.find(x => +x.id_poli === +id);
        if (p) return bukaForm(p);
        api(`${apiUrl}?id=${id}`).then(r => r.status === 'success' ? bukaForm(r.data) : Swal.fire('Gagal', r.message, 'error'));
      });

      $('#programForm').on('submit', function(e) {
        e.preventDefault();
        this.classList.add('was-validated');
        if (!this.checkValidity()) return;
        const id = $('#id_poli').val();
        const $btn = $('#plSave').prop('disabled', true);
        api(apiUrl, {
            method: id ? 'PUT' : 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams(new FormData(this))
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
        const p = polis.find(x => +x.id_poli === +id) || {};
        Swal.fire({
          title: 'Hapus poliklinik?',
          html: `<b>${esc(p.poli_name || '')}</b> akan dihapus dari master.`,
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
            body: `id_poli=${encodeURIComponent(id)}&poli_status=${status}`
          })
          .then(r => {
            if (r.status !== 'success') {
              el.checked = !el.checked;
              return Swal.fire('Gagal', r.message || 'Gagal mengubah status.', 'error');
            }
            const p = polis.find(x => +x.id_poli === +id);
            if (p) p.poli_status = status;
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
