<?php
$title = 'Ruangan Rawat Inap';
require '../../controller/view.php';
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .rm-stat {
      border: 1px solid #e5eaef;
      border-radius: 12px;
      background: #fff;
      padding: 14px 16px;
      height: 100%;
      transition: all .15s;
    }

    .rm-stat.rm-click {
      cursor: pointer;
    }

    .rm-stat.rm-click:hover {
      border-color: #5d87ff;
    }

    .rm-stat .rm-icon {
      width: 40px;
      height: 40px;
      border-radius: 10px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 1.05rem;
      flex-shrink: 0;
    }

    .rm-stat .rm-val {
      font-size: 1.35rem;
      font-weight: 700;
      line-height: 1.2;
    }

    .rm-stat .rm-label {
      font-size: .75rem;
      color: #6c757d;
    }

    .rm-occ {
      height: 6px;
      border-radius: 6px;
      background: #eef2f6;
      overflow: hidden;
    }

    .rm-occ > span {
      display: block;
      height: 100%;
      border-radius: 6px;
      background: #13deb9;
      transition: width .3s;
    }

    .rm-occ.warn > span {
      background: #ffae1f;
    }

    .rm-occ.full > span {
      background: #fa896b;
    }

    .rm-chips .btn {
      border-radius: 20px;
      font-size: .8rem;
      padding: .3rem .85rem;
    }

    #roomTable td {
      font-size: .875rem;
      vertical-align: middle;
    }

    #roomTable th,
    #roomTable td {
      padding-left: .6rem;
      padding-right: .6rem;
    }

    #roomTable th {
      font-size: .78rem;
      white-space: nowrap;
    }

    #roomTable tr.rm-off td {
      opacity: .55;
    }

    .rm-name {
      font-weight: 600;
      color: #2a3547;
    }

    .rm-desc {
      font-size: .76rem;
      color: #7c8fac;
      max-width: 280px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .rm-bedcell {
      min-width: 170px;
      cursor: pointer;
    }

    .rm-bedcell:hover .rm-bedtxt {
      color: #5d87ff;
    }

    .rm-bedtxt {
      font-size: .8rem;
      margin-bottom: 4px;
    }

    .rm-act {
      white-space: nowrap;
    }

    .rm-act .btn {
      width: 32px;
      height: 32px;
      padding: 0;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 8px;
    }

    .rm-class {
      white-space: nowrap;
      display: inline-block;
      font-size: .72rem;
      font-weight: 600;
      padding: .3em .65em;
      border-radius: 6px;
    }

    /* bed grid */
    .bed-card {
      border: 1px solid #e5eaef;
      border-radius: 12px;
      padding: 12px 14px;
      height: 100%;
      background: #fff;
      position: relative;
      border-left: 4px solid #13deb9;
    }

    .bed-card.terisi {
      border-left-color: #ffae1f;
      background: #fffcf5;
    }

    .bed-card .bed-name {
      font-weight: 700;
      font-size: .95rem;
    }

    .bed-card .bed-meta {
      font-size: .75rem;
      color: #7c8fac;
    }

    .bed-card .bed-pasien {
      font-size: .8rem;
      background: #f6f9fc;
      border-radius: 8px;
      padding: 6px 8px;
      margin-top: 8px;
    }

    .bed-card .bed-warn {
      font-size: .72rem;
      color: #c0392b;
      margin-top: 6px;
    }

    .bed-group-title {
      font-size: .8rem;
      font-weight: 700;
      color: #5a6a85;
      text-transform: uppercase;
      letter-spacing: .3px;
      margin: 18px 0 10px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .bed-group-title:first-child {
      margin-top: 0;
    }

    .bed-group-title .line {
      flex: 1;
      height: 1px;
      background: #e5eaef;
    }

    .class-opt input {
      display: none;
    }

    .class-opt label {
      border: 1.5px solid #e5eaef;
      border-radius: 10px;
      padding: 10px 12px;
      cursor: pointer;
      width: 100%;
      display: flex;
      align-items: center;
      gap: 10px;
      font-weight: 600;
      font-size: .875rem;
      transition: all .15s;
    }

    .class-opt input:checked + label {
      border-color: #5d87ff;
      background: #f1f5ff;
      color: #5d87ff;
    }

    .rm-loading {
      position: absolute;
      inset: 0;
      background: rgba(255, 255, 255, .7);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 5;
      border-radius: 12px;
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
              <h4 class="fw-semibold mb-1">Ruangan Rawat Inap</h4>
              <div class="text-muted small">Kelola ruangan, kapasitas, dan pantau ketersediaan tempat tidur.</div>
            </div>
            <div class="d-flex gap-2">
              <button class="btn btn-outline-primary" id="btnLihatTempatTidur"><i class="fas fa-bed me-1"></i> Tempat Tidur</button>
              <button class="btn btn-primary" id="btnTambah"><i class="fas fa-plus me-1"></i> Tambah Ruangan</button>
            </div>
          </div>

          <!-- STAT -->
          <div class="row g-3 mb-3">
            <div class="col-6 col-lg-3">
              <div class="rm-stat d-flex gap-3 align-items-center">
                <span class="rm-icon bg-primary-subtle text-primary"><i class="fas fa-door-open"></i></span>
                <div>
                  <div class="rm-val" id="stRuang">-</div>
                  <div class="rm-label" id="stRuangSub">Ruangan</div>
                </div>
              </div>
            </div>
            <div class="col-6 col-lg-3">
              <div class="rm-stat rm-click d-flex gap-3 align-items-center" data-bed="">
                <span class="rm-icon bg-secondary-subtle text-secondary"><i class="fas fa-bed"></i></span>
                <div>
                  <div class="rm-val" id="stBed">-</div>
                  <div class="rm-label" id="stBedSub">Tempat tidur</div>
                </div>
              </div>
            </div>
            <div class="col-6 col-lg-3">
              <div class="rm-stat rm-click d-flex gap-3 align-items-center" data-bed="kosong">
                <span class="rm-icon bg-success-subtle text-success"><i class="fas fa-check"></i></span>
                <div>
                  <div class="rm-val text-success" id="stKosong">-</div>
                  <div class="rm-label">Bed kosong</div>
                </div>
              </div>
            </div>
            <div class="col-6 col-lg-3">
              <div class="rm-stat rm-click" data-bed="terisi">
                <div class="d-flex gap-3 align-items-center">
                  <span class="rm-icon bg-warning-subtle text-warning"><i class="fas fa-user-injured"></i></span>
                  <div class="flex-grow-1">
                    <div class="rm-val text-warning" id="stTerisi">-</div>
                    <div class="rm-label" id="stTerisiSub">Bed terisi</div>
                  </div>
                </div>
                <div class="rm-occ mt-2" id="stOccBar"><span style="width:0"></span></div>
              </div>
            </div>
          </div>

          <div id="rmWarn" class="alert alert-warning py-2 small d-none mb-3"></div>

          <!-- TABLE -->
          <div class="card position-relative">
            <div class="rm-loading" id="rmLoading"><div class="spinner-border text-primary"></div></div>
            <div class="card-body p-4">
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div class="rm-chips d-flex flex-wrap gap-2" id="rmChips">
                  <button class="btn btn-primary" data-kelas="">Semua</button>
                </div>
                <div class="d-flex gap-2 align-items-center">
                  <select id="rmStatus" class="form-select form-select-sm" style="width:auto">
                    <option value="">Semua status</option>
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                  </select>
                  <div class="input-group input-group-sm" style="width:230px">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="search" id="rmSearch" class="form-control" placeholder="Cari ruangan...">
                  </div>
                </div>
              </div>

              <div class="table-responsive">
                <table class="table align-middle mb-0 w-100" id="roomTable">
                  <thead class="table-light">
                    <tr>
                      <th>Ruangan</th>
                      <th>Kelas</th>
                      <th>Tempat Tidur</th>
                      <th class="text-center">Kapasitas</th>
                      <th class="text-center">Aktif</th>
                      <th class="text-center" style="width:116px">Aksi</th>
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

  <!-- MODAL RUANGAN -->
  <div class="modal fade" id="programModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <form id="programForm" class="modal-content" novalidate>
        <div class="modal-header">
          <h5 class="modal-title d-flex align-items-center gap-2"><i class="fas fa-door-open text-primary"></i> <span id="rmModalTitle">Tambah Ruangan</span></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_room" id="id_room">
          <label class="form-label required">Kelas Layanan</label>
          <div class="row g-2 mb-3">
            <div class="col-6 class-opt">
              <input type="radio" name="service_class" id="kelasUmum" value="Umum">
              <label for="kelasUmum"><i class="fas fa-user"></i> Umum</label>
            </div>
            <div class="col-6 class-opt">
              <input type="radio" name="service_class" id="kelasBpjs" value="BPJS Kesehatan">
              <label for="kelasBpjs"><i class="fas fa-id-card"></i> BPJS Kesehatan</label>
            </div>
            <div class="col-12 d-none" id="kelasLainWrap">
              <div class="class-opt">
                <input type="radio" name="service_class" id="kelasLain" value="">
                <label for="kelasLain"><i class="fas fa-tag"></i> <span id="kelasLainTxt"></span></label>
              </div>
            </div>
          </div>
          <div class="row g-3">
            <div class="col-8">
              <label class="form-label required" for="room_name">Nama Ruangan</label>
              <input type="text" id="room_name" name="room_name" class="form-control" maxlength="150" placeholder="cth. Melati 1" required>
            </div>
            <div class="col-4">
              <label class="form-label required" for="room_capacity">Kapasitas</label>
              <div class="input-group">
                <input type="number" id="room_capacity" name="room_capacity" class="form-control" min="0" max="500" step="1" required>
                <span class="input-group-text">bed</span>
              </div>
            </div>
            <div class="col-12">
              <label class="form-label" for="room_description">Deskripsi / Fasilitas</label>
              <textarea name="room_description" rows="3" id="room_description" class="form-control" placeholder="cth. AC, kamar mandi dalam, televisi"></textarea>
            </div>
          </div>
          <div class="small text-muted mt-2" id="rmBedInfo"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary" id="rmSave"><i class="fas fa-save me-1"></i> Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL TEMPAT TIDUR -->
  <div class="modal fade" id="bedModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <div>
            <h5 class="modal-title"><i class="fas fa-bed text-primary me-1"></i> Ketersediaan Tempat Tidur</h5>
            <div class="small text-muted" id="bedSummary"></div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" style="min-height:300px">
          <div class="row g-2 mb-3">
            <div class="col-md-3">
              <select id="filterClass" class="form-select form-select-sm">
                <option value="">Semua kelas</option>
              </select>
            </div>
            <div class="col-md-3">
              <select id="filterRoom" class="form-select form-select-sm">
                <option value="">Semua ruangan</option>
              </select>
            </div>
            <div class="col-md-3">
              <div class="btn-group btn-group-sm w-100" id="filterBedStatus">
                <button class="btn btn-outline-primary active" data-v="">Semua</button>
                <button class="btn btn-outline-primary" data-v="kosong">Kosong</button>
                <button class="btn btn-outline-primary" data-v="terisi">Terisi</button>
              </div>
            </div>
            <div class="col-md-3">
              <input type="search" id="filterBedQ" class="form-control form-control-sm" placeholder="Cari bed / pasien...">
            </div>
          </div>
          <div id="bedGrid"></div>
        </div>
      </div>
    </div>
  </div>

  <?php require 'library.php'; ?>
  <script>
    (function() {
      const apiUrl = 'controller/master/roomController';
      const KELAS_STD = ['Umum', 'BPJS Kesehatan'];
      let rooms = [],
        beds = [],
        roomById = {},
        filterKelas = '';

      const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
      } [c]));
      const num = v => Number(v) || 0;
      const fmtTgl = d => d ? new Date(d + 'T00:00:00').toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
      }) : '';
      const kelasBadge = k => /bpjs/i.test(k || '') ?
        `<span class="rm-class bg-success-subtle text-success">${esc(k)}</span>` :
        `<span class="rm-class bg-primary-subtle text-primary">${esc(k || '-')}</span>`;
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

      // ---------- tabel ruangan ----------
      const table = $('#roomTable').DataTable({
        data: [],
        autoWidth: false,
        dom: 'rt<"d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2"ip>',
        pageLength: 15,
        order: [
          [1, 'asc'],
          [0, 'asc']
        ],
        language: {
          info: 'Menampilkan _START_ - _END_ dari _TOTAL_ ruangan',
          infoEmpty: 'Tidak ada ruangan',
          infoFiltered: '(difilter dari _MAX_)',
          emptyTable: '<div class="py-4 text-muted"><i class="fas fa-door-closed fa-2x mb-2 d-block"></i>Belum ada ruangan. Klik <b>Tambah Ruangan</b> untuk memulai.</div>',
          zeroRecords: '<div class="py-4 text-muted">Ruangan tidak ditemukan.</div>',
          paginate: {
            previous: '‹',
            next: '›'
          }
        },
        createdRow: (tr, r) => {
          if (+r.room_status !== 1) tr.classList.add('rm-off');
        },
        columns: [{
            data: 'room_name',
            render: (d, t, r) => t === 'sort' || t === 'type' ? String(d || '').toLowerCase().replace(/\d+/g, n => n.padStart(6, '0')) : t !== 'display' ? `${d} ${r.room_description || ''}` :
              `<div class="rm-name">${esc(d)}</div>${r.room_description ? `<div class="rm-desc" title="${esc(r.room_description)}">${esc(r.room_description)}</div>` : ''}`
          },
          {
            data: 'service_class',
            render: (d, t) => t === 'display' ? kelasBadge(d) : d
          },
          {
            data: 'total_bed',
            render: (d, t, r) => {
              const total = num(r.total_bed),
                terisi = num(r.bed_terisi);
              if (t !== 'display') return total ? terisi / total : -1;
              if (!total) return `<div class="rm-bedcell text-muted small" data-room="${r.id_room}"><i class="fas fa-plus-circle me-1"></i>Belum ada bed</div>`;
              const pct = Math.round(terisi / total * 100);
              return `<div class="rm-bedcell" data-room="${r.id_room}" title="Lihat tempat tidur">
                <div class="rm-bedtxt"><b>${terisi}</b>/${total} terisi · <span class="text-success">${num(r.bed_kosong)} kosong</span></div>
                <div class="rm-occ ${pct >= 100 ? 'full' : pct >= 75 ? 'warn' : ''}"><span style="width:${pct}%"></span></div></div>`;
            }
          },
          {
            data: 'room_capacity',
            className: 'text-center',
            render: (d, t, r) => {
              if (t !== 'display') return num(d);
              const beda = num(r.total_bed) !== num(d);
              return `${num(d)}${beda ? ` <i class="fas fa-exclamation-circle text-warning" title="Jumlah bed terdaftar (${num(r.total_bed)}) berbeda dengan kapasitas"></i>` : ''}`;
            }
          },
          {
            data: 'room_status',
            className: 'text-center',
            orderable: false,
            render: (d, t, r) => t !== 'display' ? d : `<label class="switch mb-0"><input type="checkbox" class="toggle-status-room" data-id="${r.id_room}" ${+d === 1 ? 'checked' : ''}><span class="slider"></span></label>`
          },
          {
            data: null,
            className: 'text-center',
            orderable: false,
            searchable: false,
            render: r => `<div class="rm-act d-inline-flex gap-1">
              <button class="btn btn-light text-info details-btn" data-id="${r.id_room}" title="Kelola tempat tidur"><i class="fas fa-bed"></i></button>
              <button class="btn btn-light text-warning edit-btn" data-id="${r.id_room}" title="Ubah"><i class="fas fa-pen"></i></button>
              <button class="btn btn-light text-danger delete-btn" data-id="${r.id_room}" title="Hapus"><i class="fas fa-trash"></i></button></div>`
          }
        ]
      });

      // filter kelas & status (custom search)
      $.fn.dataTable.ext.search.push((settings, _d, idx) => {
        if (settings.nTable.id !== 'roomTable') return true;
        const r = table.row(idx).data();
        const st = $('#rmStatus').val();
        return (!filterKelas || r.service_class === filterKelas) && (st === '' || String(+r.room_status) === st);
      });
      $('#rmSearch').on('input', function() {
        table.search(this.value).draw();
      });
      $('#rmStatus').on('change', () => table.draw());
      $('#rmChips').on('click', 'button', function() {
        filterKelas = $(this).data('kelas') || '';
        $('#rmChips button').removeClass('btn-primary').addClass('btn-light');
        $(this).removeClass('btn-light').addClass('btn-primary');
        table.draw();
      });

      function load() {
        $('#rmLoading').css('display', 'flex');
        return api(apiUrl).then(res => {
          if (res.status !== 'success') throw new Error(res.message);
          rooms = res.data || [];
          beds = res.beds || [];
          roomById = Object.fromEntries(rooms.map(r => [String(r.id_room), r]));
          table.clear().rows.add(rooms).draw(false);
          renderStats();
          renderChips();
          if ($('#bedModal').hasClass('show')) {
            buildBedFilter();
            renderBeds();
          }
        }).catch(e => Swal.fire('Gagal!', e.message || 'Gagal memuat data ruangan.', 'error'))
          .finally(() => $('#rmLoading').hide());
      }

      function renderChips() {
        const per = {};
        rooms.forEach(r => per[r.service_class || '-'] = (per[r.service_class || '-'] || 0) + 1);
        const html = [`<button class="btn ${!filterKelas ? 'btn-primary' : 'btn-light'}" data-kelas="">Semua <span class="opacity-75">${rooms.length}</span></button>`]
          .concat(Object.keys(per).sort().map(k => `<button class="btn ${filterKelas === k ? 'btn-primary' : 'btn-light'}" data-kelas="${esc(k)}">${esc(k)} <span class="opacity-75">${per[k]}</span></button>`));
        $('#rmChips').html(html.join(''));
      }

      function renderStats() {
        const aktif = rooms.filter(r => +r.room_status === 1).length;
        const total = beds.length,
          kosong = beds.filter(b => b.bed_status === 1).length,
          terisi = total - kosong;
        const pct = total ? Math.round(terisi / total * 100) : 0;
        $('#stRuang').text(rooms.length);
        $('#stRuangSub').text(`Ruangan · ${aktif} aktif`);
        $('#stBed').text(total);
        $('#stBedSub').text(`Tempat tidur · kapasitas ${rooms.reduce((a, r) => a + num(r.room_capacity), 0)}`);
        $('#stKosong').text(kosong);
        $('#stTerisi').text(terisi);
        $('#stTerisiSub').text(`Bed terisi · okupansi ${pct}%`);
        $('#stOccBar').toggleClass('warn', pct >= 75 && pct < 100).toggleClass('full', pct >= 100).find('span').css('width', pct + '%');

        const tanpaPasien = beds.filter(b => b.bed_status !== 1 && !b.pasien).length;
        const kosongAdaPasien = beds.filter(b => b.bed_status === 1 && b.pasien).length;
        const msg = [];
        if (tanpaPasien) msg.push(`<b>${tanpaPasien}</b> bed berstatus terisi tanpa pasien rawat inap aktif`);
        if (kosongAdaPasien) msg.push(`<b>${kosongAdaPasien}</b> bed berstatus kosong padahal ada pasien aktif`);
        $('#rmWarn').toggleClass('d-none', !msg.length)
          .html(msg.length ? `<i class="fas fa-exclamation-triangle me-1"></i> Perlu dicek: ${msg.join(' dan ')}. <a href="javascript:;" class="fw-semibold" id="rmWarnLink">Lihat tempat tidur</a>` : '');
      }

      // ---------- tempat tidur ----------
      let bedStatus = '';

      function buildBedFilter() {
        const kelasNow = $('#filterClass').val(),
          roomNow = $('#filterRoom').val();
        const kelas = [...new Set(rooms.map(r => r.service_class).filter(Boolean))].sort();
        $('#filterClass').html('<option value="">Semua kelas</option>' + kelas.map(k => `<option>${esc(k)}</option>`).join('')).val(kelas.includes(kelasNow) ? kelasNow : '');
        fillRoomFilter(roomNow);
      }

      function fillRoomFilter(keep) {
        const k = $('#filterClass').val();
        const list = rooms.filter(r => !k || r.service_class === k);
        $('#filterRoom').html('<option value="">Semua ruangan</option>' + list.map(r => `<option value="${r.id_room}">${esc(r.room_name)}${k ? '' : ' · ' + esc(r.service_class)}</option>`).join(''))
          .val(list.some(r => String(r.id_room) === String(keep)) ? keep : '');
      }

      function renderBeds() {
        const k = $('#filterClass').val(),
          rid = $('#filterRoom').val(),
          q = ($('#filterBedQ').val() || '').trim().toLowerCase();
        const list = beds.filter(b => {
          const r = roomById[String(b.id_room)] || {};
          if (k && r.service_class !== k) return false;
          if (rid && String(b.id_room) !== String(rid)) return false;
          if (bedStatus === 'kosong' && b.bed_status !== 1) return false;
          if (bedStatus === 'terisi' && b.bed_status === 1) return false;
          if (q && !`${b.bed_name} ${r.room_name} ${b.pasien?.nama || ''} ${b.pasien?.rm || ''}`.toLowerCase().includes(q)) return false;
          return true;
        });
        const kosong = list.filter(b => b.bed_status === 1).length;
        $('#bedSummary').text(`${list.length} tempat tidur · ${kosong} kosong · ${list.length - kosong} terisi`);

        if (!list.length) {
          $('#bedGrid').html('<div class="text-center text-muted py-5"><i class="fas fa-bed fa-2x mb-2 d-block opacity-50"></i>Tidak ada tempat tidur yang sesuai filter.</div>');
          return;
        }
        const grup = {};
        list.forEach(b => (grup[b.id_room] = grup[b.id_room] || []).push(b));
        const urut = Object.keys(grup).sort((a, b) => {
          const ra = roomById[a] || {},
            rb = roomById[b] || {};
          return String(ra.service_class).localeCompare(rb.service_class) || String(ra.room_name).localeCompare(rb.room_name, 'id', {
            numeric: true
          });
        });
        $('#bedGrid').html(urut.map(id => {
          const r = roomById[id] || {
            room_name: 'Ruangan #' + id
          };
          const items = grup[id].sort((a, b) => String(a.bed_name).localeCompare(b.bed_name, 'id', {
            numeric: true
          }));
          return `<div class="bed-group-title"><i class="fas fa-door-open"></i> ${esc(r.room_name)} ${r.service_class ? kelasBadge(r.service_class) : ''}<span class="line"></span><span class="fw-normal text-muted text-lowercase">${items.filter(b => b.bed_status === 1).length}/${items.length} kosong</span></div>
            <div class="row g-2">${items.map(bedCard).join('')}</div>`;
        }).join(''));
      }

      function bedCard(b) {
        const kosong = b.bed_status === 1,
          p = b.pasien;
        let warn = '';
        if (!kosong && !p) warn = '<div class="bed-warn"><i class="fas fa-exclamation-circle"></i> Terisi, tapi tidak ada pasien rawat inap aktif</div>';
        if (kosong && p) warn = '<div class="bed-warn"><i class="fas fa-exclamation-circle"></i> Kosong, tapi masih ada pasien aktif</div>';
        if (p && p.jml_aktif > 1) warn += `<div class="bed-warn"><i class="fas fa-exclamation-circle"></i> ${p.jml_aktif} pasien aktif di bed ini</div>`;
        return `<div class="col-sm-6 col-lg-4 col-xl-3"><div class="bed-card ${kosong ? '' : 'terisi'}">
          <div class="d-flex justify-content-between align-items-start gap-2">
            <div>
              <div class="bed-name"><i class="fas fa-bed me-1 ${kosong ? 'text-success' : 'text-warning'}"></i> Bed ${esc(b.bed_name || '-')}</div>
              <div class="bed-meta">${esc(b.bed_gender || '-')} · <span class="${kosong ? 'text-success' : 'text-warning'} fw-semibold">${kosong ? 'Kosong' : 'Terisi'}</span></div>
            </div>
            <label class="switch mb-0 flex-shrink-0" title="${kosong ? 'Tandai terisi' : 'Tandai kosong'}">
              <input type="checkbox" class="toggle-status-bed" data-id="${b.id_bed}" ${kosong ? '' : 'checked'}><span class="slider"></span>
            </label>
          </div>
          ${p ? `<div class="bed-pasien"><div class="fw-semibold text-truncate" title="${esc(p.nama)}"><i class="fas fa-user-injured text-warning me-1"></i>${esc(p.nama || '-')}</div>
            <div class="text-muted" style="font-size:.72rem">${p.rm ? 'RM ' + esc(p.rm) : ''}${p.tgl_masuk ? ' · masuk ' + esc(fmtTgl(p.tgl_masuk)) : ''}</div></div>` : ''}
          ${b.bed_notes ? `<div class="bed-meta mt-1 text-truncate" title="${esc(b.bed_notes)}">${esc(b.bed_notes)}</div>` : ''}
          ${warn}
        </div></div>`;
      }

      function openBeds(opt = {}) {
        buildBedFilter();
        if (opt.room) {
          $('#filterClass').val('');
          fillRoomFilter(opt.room);
        }
        if (opt.status !== undefined) {
          bedStatus = opt.status;
          $('#filterBedStatus button').removeClass('active').filter(`[data-v="${opt.status}"]`).addClass('active');
        }
        renderBeds();
        $('#bedModal').modal('show');
      }

      $('#btnLihatTempatTidur').on('click', () => openBeds({
        status: ''
      }));
      $(document).on('click', '.rm-stat[data-bed]', function() {
        openBeds({
          status: $(this).data('bed') || ''
        });
      });
      $(document).on('click', '#rmWarnLink', () => openBeds({
        status: ''
      }));
      $('#roomTable').on('click', '.rm-bedcell', function() {
        const id = $(this).data('room');
        if (!num((roomById[String(id)] || {}).total_bed)) return openDetail(id);
        openBeds({
          room: id,
          status: ''
        });
      });
      $('#filterClass').on('change', () => {
        fillRoomFilter($('#filterRoom').val());
        renderBeds();
      });
      $('#filterRoom').on('change', renderBeds);
      $('#filterBedQ').on('input', renderBeds);
      $('#filterBedStatus').on('click', 'button', function() {
        bedStatus = $(this).data('v') || '';
        $('#filterBedStatus button').removeClass('active');
        $(this).addClass('active');
        renderBeds();
      });

      $(document).on('change', '.toggle-status-bed', function() {
        const el = this,
          id = $(el).data('id'),
          status = el.checked ? 0 : 1;
        el.disabled = true;
        api(apiUrl + '?toggle_bed=1', {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: new URLSearchParams({
            id_bed: id,
            bed_status: status
          })
        }).then(res => {
          if (res.status !== 'success') throw new Error(res.message);
          toast('success', res.message);
          load();
        }).catch(e => {
          el.checked = !el.checked;
          el.disabled = false;
          Swal.fire('Gagal!', e.message || 'Gagal mengubah status bed.', 'error');
        });
      });

      // ---------- tambah / ubah ----------
      function setKelas(v) {
        const std = KELAS_STD.includes(v);
        $('#kelasLainWrap').toggleClass('d-none', std || !v);
        $('#kelasLain').val(std ? '' : v);
        $('#kelasLainTxt').text(v || '');
        $('input[name="service_class"]').prop('checked', false);
        if (v) $(`input[name="service_class"]`).filter((_, el) => el.value === v).prop('checked', true);
      }

      $('#btnTambah').on('click', function() {
        $('#programForm')[0].reset();
        $('#programForm .is-invalid').removeClass('is-invalid');
        $('#id_room').val('');
        setKelas('');
        $('#rmBedInfo').text('');
        $('#rmModalTitle').text('Tambah Ruangan');
        $('#programModal').modal('show');
      });

      $(document).on('click', '.edit-btn', function() {
        const r = roomById[String($(this).data('id'))];
        if (!r) return;
        $('#programForm')[0].reset();
        $('#programForm .is-invalid').removeClass('is-invalid');
        $('#id_room').val(r.id_room);
        setKelas(r.service_class || '');
        $('#room_name').val(r.room_name);
        $('#room_capacity').val(num(r.room_capacity));
        $('#room_description').val(r.room_description || '');
        $('#rmBedInfo').html(`<i class="fas fa-info-circle me-1"></i>Saat ini terdaftar <b>${num(r.total_bed)}</b> tempat tidur (${num(r.bed_terisi)} terisi). Tempat tidur dikelola dari tombol <i class="fas fa-bed"></i>.`);
        $('#rmModalTitle').text('Ubah Ruangan');
        $('#programModal').modal('show');
      });

      $('#programModal').on('shown.bs.modal', () => $('#room_name').trigger('focus'));

      $('#programForm').on('submit', function(e) {
        e.preventDefault();
        const kelas = $('input[name="service_class"]:checked').val() || '';
        const nama = $('#room_name').val().trim();
        const kap = $('#room_capacity').val();
        $('#room_name').toggleClass('is-invalid', !nama);
        $('#room_capacity').toggleClass('is-invalid', kap === '' || +kap < 0 || +kap > 500 || !Number.isInteger(+kap));
        if (!kelas) return Swal.fire('Perhatian', 'Pilih kelas layanan terlebih dahulu.', 'warning');
        if ($('#programForm .is-invalid').length) return;

        const id = $('#id_room').val();
        const body = new URLSearchParams({
          id_room: id,
          service_class: kelas,
          room_name: nama,
          room_capacity: kap,
          room_description: $('#room_description').val().trim()
        });
        const $btn = $('#rmSave').prop('disabled', true);
        api(apiUrl, {
          method: id ? 'PUT' : 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body
        }).then(res => {
          if (res.status !== 'success') throw new Error(res.message);
          $('#programModal').modal('hide');
          toast('success', res.message);
          load();
        }).catch(e => Swal.fire('Gagal!', e.message || 'Gagal menyimpan.', 'error'))
          .finally(() => $btn.prop('disabled', false));
      });

      // ---------- hapus ----------
      $(document).on('click', '.delete-btn', function() {
        const r = roomById[String($(this).data('id'))];
        if (!r) return;
        if (num(r.total_bed)) {
          return Swal.fire({
            icon: 'info',
            title: 'Ruangan masih memiliki bed',
            html: `<b>${esc(r.room_name)}</b> masih memiliki ${num(r.total_bed)} tempat tidur. Hapus tempat tidurnya dulu melalui menu kelola bed, atau nonaktifkan ruangan.`,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-bed"></i> Kelola bed',
            cancelButtonText: 'Tutup'
          }).then(x => x.isConfirmed && openDetail(r.id_room));
        }
        Swal.fire({
          title: 'Hapus ruangan?',
          html: `Ruangan <b>${esc(r.room_name)}</b> (${esc(r.service_class)}) akan dihapus permanen.`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#fa896b',
          confirmButtonText: 'Hapus',
          cancelButtonText: 'Batal'
        }).then(x => {
          if (!x.isConfirmed) return;
          api(apiUrl + '?id=' + encodeURIComponent(r.id_room), {
            method: 'DELETE'
          }).then(res => {
            if (res.status !== 'success') throw new Error(res.message);
            toast('success', res.message);
            load();
          }).catch(e => Swal.fire('Gagal!', e.message || 'Gagal menghapus.', 'error'));
        });
      });

      // ---------- status ruangan ----------
      $(document).on('change', '.toggle-status-room', function() {
        const el = this,
          id = $(el).data('id'),
          status = el.checked ? 1 : 0;
        el.disabled = true;
        api(apiUrl, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: new URLSearchParams({
            id_room: id,
            room_status: status
          })
        }).then(res => {
          if (res.status !== 'success') throw new Error(res.message);
          const r = roomById[String(id)];
          if (r) r.room_status = status;
          $(el).closest('tr').toggleClass('rm-off', !status);
          renderStats();
          toast('success', res.message);
        }).catch(e => {
          el.checked = !el.checked;
          Swal.fire('Gagal!', e.message || 'Gagal mengubah status.', 'error');
        }).finally(() => el.disabled = false);
      });

      // ---------- detail (kelola bed) ----------
      function openDetail(id) {
        api('controller/session/set_room_session', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: new URLSearchParams({
            id_room: id
          })
        }).then(resp => {
          if (resp.status === 'success') window.location.href = 'module/admin/room_details';
          else Swal.fire('Gagal!', resp.message || 'Tidak dapat membuka detail.', 'error');
        });
      }
      $(document).on('click', '.details-btn', function() {
        openDetail($(this).data('id'));
      });

      load();
    })();
  </script>
</body>

</html>
