<?php
$title = 'Jenis Bayar';
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

    .pv-stat {
      border: 1px solid #e5eaef;
      border-radius: 12px;
      background: #fff;
      padding: 14px 16px;
      height: 100%;
    }

    .pv-icon {
      width: 40px;
      height: 40px;
      border-radius: 10px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 1.05rem;
      flex-shrink: 0;
    }

    .pv-val {
      font-size: 1.35rem;
      font-weight: 700;
      line-height: 1.2;
      color: #2a3547;
    }

    .pv-label {
      font-size: .75rem;
      color: #6c757d;
    }

    /* komposisi */
    .pv-stack {
      display: flex;
      height: 14px;
      border-radius: 8px;
      overflow: hidden;
      background: #eef2f6;
    }

    .pv-stack span {
      height: 100%;
      transition: width .3s;
    }

    .pv-legend {
      display: flex;
      flex-wrap: wrap;
      gap: 6px 18px;
      margin-top: 12px;
      font-size: .8rem;
      color: #5a6a85;
    }

    .pv-legend i {
      display: inline-block;
      width: 10px;
      height: 10px;
      border-radius: 3px;
      margin-right: 6px;
      vertical-align: -1px;
    }

    .pv-legend b {
      color: #2a3547;
      margin-left: 4px;
    }

    /* kartu jenis bayar */
    .pv-card {
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

    .pv-card::before {
      content: '';
      position: absolute;
      left: 0;
      top: 0;
      right: 0;
      height: 4px;
      background: var(--pv-c);
    }

    .pv-card:hover {
      border-color: var(--pv-c);
      box-shadow: 0 6px 18px rgba(42, 53, 71, .08);
    }

    .pv-card.off .pv-body {
      opacity: .55;
    }

    .pv-head {
      padding: 16px 16px 0;
      display: flex;
      gap: 12px;
      align-items: flex-start;
    }

    .pv-avatar {
      width: 46px;
      height: 46px;
      border-radius: 12px;
      flex-shrink: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.25rem;
      color: var(--pv-c);
      background: var(--pv-bg);
    }

    .pv-name {
      font-weight: 700;
      color: #2a3547;
      font-size: .95rem;
      line-height: 1.3;
    }

    .pv-tag {
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

    .pv-body {
      padding: 14px 16px 16px;
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .pv-today {
      display: flex;
      align-items: flex-end;
      justify-content: space-between;
      gap: 10px;
    }

    .pv-big {
      font-size: 2rem;
      font-weight: 800;
      line-height: 1;
      color: #2a3547;
    }

    .pv-big small {
      font-size: .75rem;
      font-weight: 500;
      color: #7c8fac;
      margin-left: 4px;
    }

    .pv-spark {
      display: flex;
      align-items: flex-end;
      gap: 3px;
      height: 34px;
    }

    .pv-spark span {
      width: 7px;
      border-radius: 3px 3px 0 0;
      background: var(--pv-bg2);
      min-height: 3px;
    }

    .pv-spark span.now {
      background: var(--pv-c);
    }

    .pv-share {
      display: flex;
      align-items: center;
      gap: 8px;
      margin: 12px 0 2px;
    }

    .pv-share .bar {
      flex: 1;
      height: 6px;
      border-radius: 6px;
      background: #eef2f6;
      overflow: hidden;
    }

    .pv-share .bar span {
      display: block;
      height: 100%;
      background: var(--pv-c);
      border-radius: 6px;
      transition: width .3s;
    }

    .pv-share b {
      font-size: .78rem;
      color: #2a3547;
      min-width: 38px;
      text-align: right;
    }

    .pv-sharetxt {
      font-size: .72rem;
      color: #7c8fac;
    }

    .pv-mini {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 6px;
      margin-top: 12px;
    }

    .pv-mini div {
      background: #f6f9fc;
      border-radius: 8px;
      padding: 6px 4px;
      text-align: center;
    }

    .pv-mini b {
      display: block;
      font-size: .9rem;
      color: #2a3547;
    }

    .pv-mini span {
      font-size: .66rem;
      color: #7c8fac;
    }

    .pv-rank {
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

    .pv-chips .btn {
      border-radius: 20px;
      font-size: .8rem;
      padding: .3rem .85rem;
    }

    .pv-search {
      width: 220px;
    }

    @media (max-width: 575.98px) {

      .pv-tools,
      .pv-search {
        width: 100%;
      }

      .pv-search {
        flex: 1;
      }
    }

    .pv-loading {
      position: absolute;
      inset: 0;
      background: rgba(255, 255, 255, .6);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 5;
      border-radius: 12px;
    }

    .pv-empty {
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
              <h4 class="fw-semibold mb-1">Jenis Bayar</h4>
              <div class="text-muted small">Pantau jumlah pasien per jenis pembayaran di faskes Anda.</div>
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center">
              <div class="input-group flex-nowrap" style="width:auto">
                <button class="btn btn-outline-secondary" id="tglPrev" title="Hari sebelumnya"><i class="fas fa-chevron-left"></i></button>
                <input type="date" id="pvTanggal" class="form-control" style="width:160px">
                <button class="btn btn-outline-secondary" id="tglNext" title="Hari berikutnya"><i class="fas fa-chevron-right"></i></button>
              </div>
              <button class="btn btn-outline-primary d-none" id="tglHariIni">Hari ini</button>
              <button class="btn btn-light" id="btnRefresh" title="Muat ulang"><i class="fas fa-sync-alt"></i></button>
            </div>
          </div>

          <!-- STAT -->
          <div class="row g-3 mb-3">
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="pv-stat d-flex gap-3 align-items-center">
                <span class="pv-icon bg-primary-subtle text-primary"><i class="fas fa-user-friends"></i></span>
                <div class="min-w-0">
                  <div class="pv-val" id="stHari">-</div>
                  <div class="pv-label" id="stHariSub">Pasien hari ini</div>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="pv-stat d-flex gap-3 align-items-center">
                <span class="pv-icon bg-success-subtle text-success"><i class="fas fa-id-card"></i></span>
                <div class="min-w-0">
                  <div class="pv-val" id="stBpjs">-</div>
                  <div class="pv-label" id="stBpjsSub">Pasien BPJS Kesehatan</div>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="pv-stat d-flex gap-3 align-items-center">
                <span class="pv-icon bg-warning-subtle text-warning"><i class="fas fa-wallet"></i></span>
                <div class="min-w-0">
                  <div class="pv-val" id="stNon">-</div>
                  <div class="pv-label" id="stNonSub">Pasien non-BPJS</div>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="pv-stat d-flex gap-3 align-items-center">
                <span class="pv-icon bg-info-subtle text-info"><i class="fas fa-calendar-alt"></i></span>
                <div class="min-w-0">
                  <div class="pv-val" id="stBulan">-</div>
                  <div class="pv-label" id="stBulanSub">Pasien bulan ini</div>
                </div>
              </div>
            </div>
          </div>

          <!-- KOMPOSISI -->
          <div class="card mb-3">
            <div class="card-body p-4">
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h6 class="fw-semibold mb-0">Komposisi pasien <span class="text-muted fw-normal" id="kompLabel"></span></h6>
                <div class="btn-group btn-group-sm" id="kompMode">
                  <button class="btn btn-outline-primary active" data-m="hari">Hari ini</button>
                  <button class="btn btn-outline-primary" data-m="bulan">Bulan ini</button>
                </div>
              </div>
              <div class="pv-stack" id="kompBar"></div>
              <div class="pv-legend" id="kompLegend"></div>
            </div>
          </div>

          <!-- DAFTAR JENIS BAYAR -->
          <div class="card position-relative">
            <div class="pv-loading" id="pvLoading"><div class="spinner-border text-primary"></div></div>
            <div class="card-body p-4">
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div class="pv-chips d-flex flex-wrap gap-2" id="pvChips">
                  <button class="btn btn-primary" data-s="">Semua <span class="badge bg-white text-primary ms-1" data-n=""></span></button>
                  <button class="btn btn-light" data-s="1">Aktif <span class="badge bg-white text-dark ms-1" data-n="1"></span></button>
                  <button class="btn btn-light" data-s="0">Nonaktif <span class="badge bg-white text-dark ms-1" data-n="0"></span></button>
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center pv-tools">
                  <select id="pvSort" class="form-select form-select-sm" style="width:auto">
                    <option value="pasien">Pasien terbanyak</option>
                    <option value="nama">Nama A-Z</option>
                    <option value="kode">Kode</option>
                  </select>
                  <div class="input-group input-group-sm pv-search">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="search" id="pvSearch" class="form-control" placeholder="Cari jenis bayar / kode...">
                  </div>
                </div>
              </div>
              <div class="row g-3" id="pvGrid"></div>
              <div class="small text-muted mt-3"><i class="fas fa-info-circle me-1"></i>Jenis bayar adalah data master bersama untuk semua faskes dan hanya dapat diubah oleh superadmin.</div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <?php require 'library.php'; ?>
  <script>
    (function() {
      const apiUrl = 'controller/master/providerController';
      // warna & ikon per kode jenis bayar
      const GAYA = {
        'BPJS-KES': ['#13deb9', '#e6fffa', '#b5f3e6', 'fas fa-id-card'],
        'BPJS-KET': ['#49beff', '#e8f7ff', '#bde6ff', 'fas fa-hard-hat'],
        'ASR': ['#7460ee', '#efecfd', '#d4cdfa', 'fas fa-shield-alt'],
        'PSR': ['#ffae1f', '#fef5e5', '#ffe1ad', 'fas fa-building'],
        'UM': ['#5d87ff', '#ecf2ff', '#c9d7ff', 'fas fa-money-bill-wave'],
      };
      const CADANGAN = [
        ['#fa896b', '#fdede8', '#fcd0c4', 'fas fa-wallet'],
        ['#e0569b', '#fdebf4', '#f6c3dc', 'fas fa-wallet'],
        ['#3ea55d', '#e8f6ec', '#bfe5ca', 'fas fa-wallet'],
      ];
      const LAIN = ['#adb5bd', '#f2f5f9', '#dfe5ef', 'fas fa-question'];
      const ID_BPJS = 1;

      let list = [],
        stats = {},
        lain = null,
        hari = [],
        filterStatus = '',
        kompMode = 'hari';

      const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
      } [c]));
      const fmt = n => (Number(n) || 0).toLocaleString('id-ID');
      const pct = (a, b) => b ? Math.round(a / b * 1000) / 10 : 0;
      const api = url => fetch(url).then(r => r.json().catch(() => ({
        status: 'error',
        message: 'Respon server tidak valid.'
      })));
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
      const gaya = p => GAYA[String(p.provider_code || '').toUpperCase()] || CADANGAN[(Number(p.id_provider) || 0) % CADANGAN.length];
      const labelTgl = t => t === hariIni ? 'hari ini' : new Date(t + 'T00:00:00').toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
      });
      const namaBulan = t => new Date(t + 'T00:00:00').toLocaleDateString('id-ID', {
        month: 'long',
        year: 'numeric'
      });

      const $tgl = $('#pvTanggal').val(hariIni).attr('max', hariIni);
      const tanggal = () => $tgl.val() || hariIni;

      function load(withList = true) {
        $('#pvLoading').css('display', 'flex');
        const reqs = [api(`${apiUrl}?stats=1&tanggal=${encodeURIComponent(tanggal())}`)];
        if (withList) reqs.push(api(apiUrl));
        Promise.all(reqs).then(([s, l]) => {
          if (l) {
            if (l.status !== 'success') return Swal.fire('Gagal', l.message || 'Gagal memuat jenis bayar.', 'error');
            list = l.data || [];
          }
          if (s.status === 'success') {
            stats = s.data || {};
            lain = s.lain || null;
            hari = s.hari || [];
          } else {
            stats = {};
            lain = null;
            Swal.fire({
              toast: true,
              position: 'top-end',
              icon: 'warning',
              title: s.message || 'Gagal memuat jumlah pasien.',
              showConfirmButton: false,
              timer: 2500
            });
          }
          renderStats();
          renderKomposisi();
          render();
        }).catch(() => Swal.fire('Gagal', 'Tidak dapat terhubung ke server.', 'error'))
          .finally(() => $('#pvLoading').css('display', 'none'));
      }

      function total(key) {
        return list.reduce((a, p) => a + st(p.id_provider)[key], 0) + (lain ? lain[key] : 0);
      }

      function renderStats() {
        const t = tanggal();
        const hariN = total('hari_ini');
        const bulan = total('bulan');
        const bpjs = st(ID_BPJS).hari_ini;
        const non = hariN - bpjs;
        $('#stHari').text(fmt(hariN));
        $('#stHariSub').html(`Pasien ${esc(labelTgl(t))} · <span class="text-success">${fmt(total('selesai'))} selesai</span>`);
        $('#stBpjs').text(fmt(bpjs));
        $('#stBpjsSub').text(`BPJS Kesehatan · ${pct(bpjs, hariN)}%`);
        $('#stNon').text(fmt(non));
        $('#stNonSub').text(`Non-BPJS · ${pct(non, hariN)}%`);
        $('#stBulan').text(fmt(bulan));
        $('#stBulanSub').text(`Pasien ${namaBulan(t)}${t === hariIni ? '' : ' (s.d. tanggal dipilih)'}`);

        $('#tglHariIni').toggleClass('d-none', t === hariIni);
        $('#tglNext').prop('disabled', t >= hariIni);
        const aktif = list.filter(p => +p.provider_status === 1).length;
        $('#pvChips [data-n=""]').text(list.length);
        $('#pvChips [data-n="1"]').text(aktif);
        $('#pvChips [data-n="0"]').text(list.length - aktif);
        $('#kompMode [data-m="hari"]').text(t === hariIni ? 'Hari ini' : labelTgl(t));
      }

      function renderKomposisi() {
        const key = kompMode === 'hari' ? 'hari_ini' : 'bulan';
        const items = list.map(p => ({
          nama: p.provider_name,
          n: st(p.id_provider)[key],
          c: gaya(p)[0]
        }));
        if (lain && lain[key]) items.push({
          nama: 'Tidak diketahui',
          n: lain[key],
          c: LAIN[0]
        });
        items.sort((a, b) => b.n - a.n);
        const sum = items.reduce((a, x) => a + x.n, 0);
        $('#kompLabel').text(`· ${fmt(sum)} pasien ${kompMode === 'hari' ? labelTgl(tanggal()) : namaBulan(tanggal())}`);
        $('#kompBar').html(sum ? items.filter(x => x.n).map(x =>
          `<span style="width:${x.n / sum * 100}%;background:${x.c}" title="${esc(x.nama)}: ${fmt(x.n)} (${pct(x.n, sum)}%)"></span>`).join('') : '');
        $('#kompLegend').html(sum ? items.filter(x => x.n).map(x =>
          `<span><i style="background:${x.c}"></i>${esc(x.nama)}<b>${fmt(x.n)}</b> <span class="text-muted">(${pct(x.n, sum)}%)</span></span>`).join('') :
          '<span class="text-muted">Belum ada kunjungan.</span>');
      }

      function render() {
        const q = $('#pvSearch').val().trim().toLowerCase();
        const sort = $('#pvSort').val();
        const coll = new Intl.Collator('id', {
          numeric: true,
          sensitivity: 'base'
        });
        let rows = list.filter(p =>
          (filterStatus === '' || String(+p.provider_status) === filterStatus) &&
          (!q || [p.provider_name, p.provider_code].some(v => String(v || '').toLowerCase().includes(q)))
        );
        rows.sort((a, b) => {
          if (sort === 'pasien') return (st(b.id_provider).hari_ini - st(a.id_provider).hari_ini) || (st(b.id_provider).bulan - st(a.id_provider).bulan) || coll.compare(a.provider_name, b.provider_name);
          if (sort === 'kode') return coll.compare(a.provider_code || '', b.provider_code || '');
          return coll.compare(a.provider_name || '', b.provider_name || '');
        });

        const hariN = total('hari_ini');
        const maxHari = Math.max(0, ...list.map(p => st(p.id_provider).hari_ini));
        let html = rows.map(p => kartu({
          id: p.id_provider,
          nama: p.provider_name,
          kode: p.provider_code,
          on: +p.provider_status === 1,
          s: st(p.id_provider),
          g: gaya(p),
          top: maxHari > 0 && st(p.id_provider).hari_ini === maxHari
        }, hariN)).join('');
        if (lain && filterStatus === '' && !q) html += kartu({
          nama: 'Tidak diketahui',
          kode: 'tanpa jenis bayar',
          on: true,
          s: lain,
          g: LAIN,
          catatan: 'Kunjungan tanpa jenis bayar yang valid.'
        }, hariN);

        $('#pvGrid').html(html || `<div class="col-12"><div class="pv-empty">${list.length ? 'Jenis bayar tidak ditemukan.' : 'Belum ada jenis bayar.'}</div></div>`);
      }

      function kartu(k, hariN) {
        const {
          s,
          g
        } = k;
        const [c, bg, bg2, icon] = g;
        const share = pct(s.hari_ini, hariN);
        const maxT = Math.max(1, ...s.tren);
        const spark = s.tren.map((n, i) => {
          const t = hari[i] ? new Date(hari[i] + 'T00:00:00').toLocaleDateString('id-ID', {
            weekday: 'short',
            day: 'numeric',
            month: 'short'
          }) : '';
          return `<span class="${i === 6 ? 'now' : ''}" style="height:${Math.max(8, Math.round(n / maxT * 100))}%" title="${esc(t)}: ${fmt(n)} pasien"></span>`;
        }).join('');
        const rank = k.top ? '<span class="pv-tag" style="background:#fff4e5;color:#e08a00"><i class="fas fa-crown me-1"></i>Terbanyak</span>' : '';
        const status = k.id === undefined ? '' : (k.on ?
          '<span class="pv-tag bg-success-subtle text-success">Aktif</span>' :
          '<span class="pv-tag bg-secondary-subtle text-secondary">Nonaktif</span>');

        return `
        <div class="col-sm-6 col-lg-4 col-xxl-3">
          <div class="pv-card ${k.on ? '' : 'off'}" style="--pv-c:${c};--pv-bg:${bg};--pv-bg2:${bg2}">
            <div class="pv-head">
              <div class="pv-avatar"><i class="${icon}"></i></div>
              <div class="min-w-0">
                <div class="pv-name">${esc(k.nama || '-')}</div>
                <span class="pv-tag"><i class="fas fa-hashtag me-1"></i>${esc(k.kode || '-')}</span>${status}${rank}
              </div>
            </div>
            <div class="pv-body">
              <div class="pv-today">
                <div>
                  <div class="pv-label mb-1">Pasien ${esc(labelTgl(tanggal()))}</div>
                  <div class="pv-big">${fmt(s.hari_ini)}<small>pasien</small></div>
                </div>
                <div class="text-end">
                  <div class="pv-spark" title="7 hari terakhir">${spark}</div>
                  <div class="pv-label" style="font-size:.65rem">7 hari</div>
                </div>
              </div>
              <div class="pv-share"><div class="bar"><span style="width:${share}%"></span></div><b>${share}%</b></div>
              <div class="pv-sharetxt">${k.catatan ? esc(k.catatan) : (s.hari_ini ? `dari seluruh pasien ${esc(labelTgl(tanggal()))}` : 'Belum ada kunjungan')}</div>
              <div class="pv-mini">
                <div><b class="text-warning">${fmt(s.antre)}</b><span>Antre</span></div>
                <div><b class="text-success">${fmt(s.selesai)}</b><span>Selesai</span></div>
                <div><b class="text-danger">${fmt(s.batal)}</b><span>Batal</span></div>
                <div><b>${fmt(s.bulan)}</b><span>Bulan ini</span></div>
              </div>
            </div>
          </div>
        </div>`;
      }

      // ---------- interaksi ----------
      $('#pvChips').on('click', '.btn', function() {
        filterStatus = String($(this).data('s'));
        $('#pvChips .btn').removeClass('btn-primary').addClass('btn-light')
          .find('.badge').removeClass('text-primary').addClass('text-dark');
        $(this).removeClass('btn-light').addClass('btn-primary').find('.badge').removeClass('text-dark').addClass('text-primary');
        render();
      });
      $('#kompMode').on('click', '.btn', function() {
        kompMode = $(this).data('m');
        $('#kompMode .btn').removeClass('active');
        $(this).addClass('active');
        renderKomposisi();
      });
      $('#pvSearch').on('input', render);
      $('#pvSort').on('change', render);
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

      load(true);
    })();
  </script>
</body>

</html>
