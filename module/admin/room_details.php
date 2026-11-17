<?php
$title = 'Ruangan Rawat Inap';
require '../../database/connect.php';
require '../../controller/view.php';

$idroom = (int) ($_SESSION['selected_room_id'] ?? 0);
$stmt = $koneksi->prepare("SELECT * FROM ms_room WHERE id_room = ? AND id_customer = ? LIMIT 1");
$idc = (int) ($_SESSION['id_customer'] ?? 0);
$stmt->bind_param('ii', $idroom, $idc);
$stmt->execute();
$dataroom = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$dataroom) {
  header('Location: room');
  exit;
}
$e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$isBpjs = stripos((string) $dataroom['service_class'], 'bpjs') !== false;
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .rd-hero {
      border: 1px solid #e5eaef;
      border-radius: 14px;
      background: linear-gradient(135deg, #f4f7ff 0%, #ffffff 60%);
      padding: 20px 22px;
    }

    .rd-hero .rd-icon {
      width: 56px;
      height: 56px;
      border-radius: 14px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 1.4rem;
      flex-shrink: 0;
    }

    .rd-hero h4 {
      font-weight: 700;
      margin: 0;
    }

    .rd-desc {
      font-size: .82rem;
      color: #7c8fac;
    }

    .rd-class {
      font-size: .72rem;
      font-weight: 600;
      padding: .3em .65em;
      border-radius: 6px;
      white-space: nowrap;
    }

    .rd-crumb {
      font-size: .8rem;
    }

    .rd-crumb a {
      color: #7c8fac;
      text-decoration: none;
    }

    .rd-crumb a:hover {
      color: #5d87ff;
    }

    .rd-stat {
      border: 1px solid #e5eaef;
      border-radius: 12px;
      background: #fff;
      padding: 12px 14px;
      height: 100%;
      cursor: pointer;
      transition: all .15s;
    }

    .rd-stat:hover {
      border-color: #5d87ff;
    }

    .rd-stat.active {
      border-color: #5d87ff;
      box-shadow: 0 0 0 3px rgba(93, 135, 255, .15);
    }

    .rd-stat .v {
      font-size: 1.3rem;
      font-weight: 700;
      line-height: 1.2;
    }

    .rd-stat .l {
      font-size: .75rem;
      color: #6c757d;
    }

    .rd-cap {
      height: 6px;
      border-radius: 6px;
      background: #eef2f6;
      overflow: hidden;
    }

    .rd-cap > span {
      display: block;
      height: 100%;
      background: #5d87ff;
      border-radius: 6px;
      transition: width .3s;
    }

    .rd-cap.over > span {
      background: #fa896b;
    }

    /* kartu bed */
    .bed-card {
      border: 1px solid #e5eaef;
      border-left: 4px solid #13deb9;
      border-radius: 12px;
      padding: 14px 16px;
      height: 100%;
      background: #fff;
      display: flex;
      flex-direction: column;
      transition: box-shadow .15s;
    }

    .bed-card:hover {
      box-shadow: 0 6px 18px rgba(42, 53, 71, .08);
    }

    .bed-card.terisi {
      border-left-color: #ffae1f;
      background: #fffcf5;
    }

    .bed-card .bed-name {
      font-weight: 700;
      font-size: 1rem;
    }

    .bed-card .bed-meta {
      font-size: .76rem;
      color: #7c8fac;
    }

    .bed-card .bed-pasien {
      font-size: .8rem;
      background: #fff;
      border: 1px dashed #f3d9a4;
      border-radius: 8px;
      padding: 7px 9px;
      margin-top: 10px;
    }

    .bed-card .bed-empty {
      font-size: .78rem;
      color: #13a58b;
      background: #effcf8;
      border-radius: 8px;
      padding: 7px 9px;
      margin-top: 10px;
    }

    .bed-card .bed-warn {
      font-size: .72rem;
      color: #c0392b;
      margin-top: 6px;
    }

    .bed-card .bed-foot {
      margin-top: auto;
      padding-top: 10px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
    }

    .bed-card .bed-foot .btn {
      width: 30px;
      height: 30px;
      padding: 0;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 8px;
    }

    .bed-gender {
      font-size: .7rem;
      font-weight: 600;
      padding: .25em .55em;
      border-radius: 6px;
    }

    .bed-add {
      border: 2px dashed #d5dde8;
      border-radius: 12px;
      height: 100%;
      min-height: 150px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 6px;
      color: #7c8fac;
      cursor: pointer;
      background: transparent;
      width: 100%;
      transition: all .15s;
    }

    .bed-add:hover {
      border-color: #5d87ff;
      color: #5d87ff;
      background: #f6f8ff;
    }

    .bed-add i {
      font-size: 1.4rem;
    }

    .gender-opt input {
      display: none;
    }

    .gender-opt label {
      border: 1.5px solid #e5eaef;
      border-radius: 10px;
      padding: 10px 8px;
      cursor: pointer;
      width: 100%;
      text-align: center;
      font-weight: 600;
      font-size: .82rem;
      transition: all .15s;
    }

    .gender-opt label i {
      display: block;
      font-size: 1.1rem;
      margin-bottom: 4px;
    }

    .gender-opt input:checked + label {
      border-color: #5d87ff;
      background: #f1f5ff;
      color: #5d87ff;
    }

    .rd-loading {
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

          <div class="rd-crumb mb-2">
            <a href="module/admin/room"><i class="fas fa-door-open me-1"></i>Ruangan Rawat Inap</a>
            <span class="text-muted mx-1">/</span>
            <span class="fw-semibold"><?= $e($dataroom['room_name']) ?></span>
          </div>

          <!-- INFO RUANGAN -->
          <div class="rd-hero mb-3">
            <div class="d-flex flex-wrap gap-3 align-items-start justify-content-between">
              <div class="d-flex gap-3 align-items-start" style="min-width:0;flex:1">
                <span class="rd-icon <?= $isBpjs ? 'bg-success-subtle text-success' : 'bg-primary-subtle text-primary' ?>"><i class="fas fa-door-open"></i></span>
                <div style="min-width:0">
                  <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <h4><?= $e($dataroom['room_name']) ?></h4>
                    <span class="rd-class <?= $isBpjs ? 'bg-success-subtle text-success' : 'bg-primary-subtle text-primary' ?>"><?= $e($dataroom['service_class']) ?></span>
                    <?php if ((int) $dataroom['room_status'] === 1): ?>
                      <span class="rd-class bg-success-subtle text-success"><i class="fas fa-circle me-1" style="font-size:.5rem;vertical-align:middle"></i>Aktif</span>
                    <?php else: ?>
                      <span class="rd-class bg-danger-subtle text-danger"><i class="fas fa-circle me-1" style="font-size:.5rem;vertical-align:middle"></i>Tidak aktif</span>
                    <?php endif; ?>
                  </div>
                  <div class="rd-desc"><?= $dataroom['room_description'] !== null && trim($dataroom['room_description']) !== '' ? nl2br($e($dataroom['room_description'])) : '<i>Belum ada deskripsi fasilitas.</i>' ?></div>
                </div>
              </div>
              <div class="d-flex gap-2 flex-wrap">
                <a href="module/admin/room" class="btn btn-light"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
                <button class="btn btn-outline-danger" id="btnResetBed" title="Kosongkan bed yang tidak ditempati pasien aktif"><i class="fas fa-broom me-1"></i> Kosongkan Bed</button>
                <button class="btn btn-primary" id="btnTambah"><i class="fas fa-plus me-1"></i> Tambah Bed</button>
              </div>
            </div>
          </div>

          <!-- RINGKASAN -->
          <div class="row g-3 mb-3">
            <div class="col-6 col-lg-3">
              <div class="rd-stat active" data-f="">
                <div class="v" id="stTotal">-</div>
                <div class="l">Tempat tidur</div>
              </div>
            </div>
            <div class="col-6 col-lg-3">
              <div class="rd-stat" data-f="kosong">
                <div class="v text-success" id="stKosong">-</div>
                <div class="l">Kosong</div>
              </div>
            </div>
            <div class="col-6 col-lg-3">
              <div class="rd-stat" data-f="terisi">
                <div class="v text-warning" id="stTerisi">-</div>
                <div class="l">Terisi</div>
              </div>
            </div>
            <div class="col-6 col-lg-3">
              <div class="rd-stat" style="cursor:default" title="Jumlah bed terdaftar dibanding kapasitas ruangan">
                <div class="d-flex justify-content-between align-items-baseline">
                  <div class="v" id="stCap">-</div>
                  <div class="l" id="stCapPct"></div>
                </div>
                <div class="l mb-1">Bed terdaftar / kapasitas</div>
                <div class="rd-cap" id="stCapBar"><span style="width:0"></span></div>
              </div>
            </div>
          </div>

          <div id="rdWarn" class="alert alert-warning py-2 small d-none mb-3"></div>

          <!-- DAFTAR BED -->
          <div class="card position-relative">
            <div class="rd-loading" id="rdLoading"><div class="spinner-border text-primary"></div></div>
            <div class="card-body p-4">
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h5 class="fw-semibold mb-0">Daftar Tempat Tidur</h5>
                <div class="input-group input-group-sm" style="width:240px">
                  <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                  <input type="search" id="rdSearch" class="form-control" placeholder="Cari bed / pasien...">
                </div>
              </div>
              <div class="row g-3" id="bedGrid"></div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <!-- MODAL BED -->
  <div class="modal fade" id="programModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <form id="programForm" class="modal-content" novalidate>
        <div class="modal-header">
          <h5 class="modal-title d-flex align-items-center gap-2"><i class="fas fa-bed text-primary"></i> <span id="rdModalTitle">Tambah Tempat Tidur</span></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_bed" id="id_bed">
          <div class="mb-3">
            <label class="form-label required" for="bed_name">Nama / Nomor Tempat Tidur</label>
            <input type="text" id="bed_name" name="bed_name" class="form-control" maxlength="100" placeholder="cth. 1A" required>
          </div>
          <label class="form-label required">Peruntukan</label>
          <div class="row g-2 mb-3">
            <div class="col-4 gender-opt">
              <input type="radio" name="bed_gender" id="gPria" value="Pria">
              <label for="gPria"><i class="fas fa-mars text-primary"></i>Pria</label>
            </div>
            <div class="col-4 gender-opt">
              <input type="radio" name="bed_gender" id="gWanita" value="Wanita">
              <label for="gWanita"><i class="fas fa-venus text-danger"></i>Wanita</label>
            </div>
            <div class="col-4 gender-opt">
              <input type="radio" name="bed_gender" id="gCampur" value="Pria-Wanita">
              <label for="gCampur"><i class="fas fa-venus-mars text-secondary"></i>Pria-Wanita</label>
            </div>
          </div>
          <div>
            <label class="form-label" for="bed_notes">Catatan</label>
            <textarea name="bed_notes" rows="3" id="bed_notes" class="form-control" maxlength="255" placeholder="cth. dekat jendela, bed elektrik"></textarea>
          </div>
          <div class="small text-muted mt-2" id="rdCapInfo"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary" id="rdSave"><i class="fas fa-save me-1"></i> Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <?php require 'library.php'; ?>
  <script>
    (function() {
      const apiUrl = 'controller/master/roomDetailsController?no=<?= (int) $idroom ?>';
      let room = {
          room_capacity: <?= (int) $dataroom['room_capacity'] ?>
        },
        beds = [],
        filter = '';

      const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
      } [c]));
      const fmtTgl = d => d ? new Date(d + 'T00:00:00').toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
      }) : '';
      const api = (url, opt = {}) => fetch(url, opt).then(r => r.json().catch(() => ({
        status: 'error',
        message: 'Respon server tidak valid.'
      })));
      const form = body => ({
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams(body)
      });
      const toast = (icon, title) => Swal.fire({
        toast: true,
        position: 'top-end',
        icon,
        title,
        showConfirmButton: false,
        timer: 2400,
        timerProgressBar: true
      });
      const GENDER = {
        'Pria': ['fa-mars', 'bg-primary-subtle text-primary'],
        'Wanita': ['fa-venus', 'bg-danger-subtle text-danger'],
        'Pria-Wanita': ['fa-venus-mars', 'bg-secondary-subtle text-secondary']
      };

      function load() {
        $('#rdLoading').css('display', 'flex');
        return api(apiUrl).then(res => {
          if (res.status !== 'success') throw new Error(res.message);
          room = res.room;
          beds = res.data || [];
          render();
        }).catch(e => Swal.fire('Gagal!', e.message || 'Gagal memuat tempat tidur.', 'error'))
          .finally(() => $('#rdLoading').hide());
      }

      function render() {
        const kosong = beds.filter(b => b.bed_status === 1).length;
        const terisi = beds.length - kosong;
        const cap = room.room_capacity || 0;
        $('#stTotal').text(beds.length);
        $('#stKosong').text(kosong);
        $('#stTerisi').text(terisi);
        $('#stCap').text(`${beds.length}/${cap}`);
        const pct = cap ? Math.round(beds.length / cap * 100) : (beds.length ? 100 : 0);
        $('#stCapPct').text(cap ? pct + '%' : '');
        $('#stCapBar').toggleClass('over', beds.length > cap).find('span').css('width', Math.min(pct, 100) + '%');

        const warn = [];
        if (beds.length !== cap) warn.push(beds.length > cap ? `Jumlah bed (${beds.length}) melebihi kapasitas ruangan (${cap}).` : `Baru ${beds.length} dari ${cap} bed (kapasitas) yang terdaftar.`);
        const tanpa = beds.filter(b => b.bed_status !== 1 && !b.pasien).length;
        const salah = beds.filter(b => b.bed_status === 1 && b.pasien).length;
        if (tanpa) warn.push(`${tanpa} bed berstatus terisi tanpa pasien rawat inap aktif.`);
        if (salah) warn.push(`${salah} bed berstatus kosong padahal ada pasien aktif.`);
        $('#rdWarn').toggleClass('d-none', !warn.length).html(warn.length ? '<i class="fas fa-exclamation-triangle me-1"></i> ' + warn.join(' ') : '');

        const q = ($('#rdSearch').val() || '').trim().toLowerCase();
        const list = beds.filter(b => {
          if (filter === 'kosong' && b.bed_status !== 1) return false;
          if (filter === 'terisi' && b.bed_status === 1) return false;
          if (q && !`${b.bed_name} ${b.bed_notes || ''} ${b.pasien?.nama || ''} ${b.pasien?.rm || ''}`.toLowerCase().includes(q)) return false;
          return true;
        });

        let html = list.map(card).join('');
        if (!filter && !q) {
          html += `<div class="col-sm-6 col-lg-4 col-xl-3"><button type="button" class="bed-add" id="bedAddCard"><i class="fas fa-plus-circle"></i><span class="fw-semibold">Tambah tempat tidur</span>${beds.length < cap ? `<span class="small">${cap - beds.length} lagi sesuai kapasitas</span>` : ''}</button></div>`;
        } else if (!list.length) {
          html = '<div class="col-12 text-center text-muted py-5"><i class="fas fa-bed fa-2x mb-2 d-block opacity-50"></i>Tidak ada tempat tidur yang sesuai.</div>';
        }
        $('#bedGrid').html(html);
      }

      function card(b) {
        const kosong = b.bed_status === 1,
          p = b.pasien;
        const g = GENDER[b.bed_gender] || ['fa-bed', 'bg-light text-muted'];
        let warn = '';
        if (!kosong && !p) warn = '<div class="bed-warn"><i class="fas fa-exclamation-circle"></i> Terisi, tapi tidak ada pasien rawat inap aktif</div>';
        if (kosong && p) warn = '<div class="bed-warn"><i class="fas fa-exclamation-circle"></i> Kosong, tapi masih ada pasien aktif</div>';
        if (p && p.jml_aktif > 1) warn += `<div class="bed-warn"><i class="fas fa-exclamation-circle"></i> ${p.jml_aktif} pasien aktif di bed ini</div>`;
        return `<div class="col-sm-6 col-lg-4 col-xl-3"><div class="bed-card ${kosong ? '' : 'terisi'}">
          <div class="d-flex justify-content-between align-items-start gap-2">
            <div style="min-width:0">
              <div class="bed-name text-truncate"><i class="fas fa-bed me-1 ${kosong ? 'text-success' : 'text-warning'}"></i> Bed ${esc(b.bed_name || '-')}</div>
              <div class="mt-1"><span class="bed-gender ${g[1]}"><i class="fas ${g[0]} me-1"></i>${esc(b.bed_gender || '-')}</span></div>
            </div>
            <span class="badge ${kosong ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning'}">${kosong ? 'Kosong' : 'Terisi'}</span>
          </div>
          ${p ? `<div class="bed-pasien"><div class="fw-semibold text-truncate" title="${esc(p.nama)}"><i class="fas fa-user-injured text-warning me-1"></i>${esc(p.nama || '-')}</div>
              <div class="text-muted" style="font-size:.72rem">${p.rm ? 'RM ' + esc(p.rm) : ''}${p.tgl_masuk ? ' · masuk ' + esc(fmtTgl(p.tgl_masuk)) : ''}</div></div>`
            : kosong ? '<div class="bed-empty"><i class="fas fa-check-circle me-1"></i>Siap digunakan</div>' : ''}
          ${b.bed_notes ? `<div class="bed-meta mt-2 text-truncate" title="${esc(b.bed_notes)}"><i class="far fa-sticky-note me-1"></i>${esc(b.bed_notes)}</div>` : ''}
          ${warn}
          <div class="bed-foot">
            <label class="switch mb-0" title="${kosong ? 'Tandai terisi' : 'Tandai kosong'}">
              <input type="checkbox" class="toggle-bed" data-id="${b.id_bed}" ${kosong ? '' : 'checked'}><span class="slider"></span>
            </label>
            <div class="d-flex gap-1">
              <button class="btn btn-light text-warning edit-btn" data-id="${b.id_bed}" title="Ubah"><i class="fas fa-pen"></i></button>
              <button class="btn btn-light text-danger delete-btn" data-id="${b.id_bed}" title="Hapus" ${p ? 'disabled' : ''}><i class="fas fa-trash"></i></button>
            </div>
          </div>
        </div></div>`;
      }

      $('#rdSearch').on('input', render);
      $('.rd-stat[data-f]').on('click', function() {
        filter = $(this).data('f') || '';
        $('.rd-stat[data-f]').removeClass('active');
        $(this).addClass('active');
        render();
      });

      // ---------- tambah / ubah ----------
      function openForm(b) {
        $('#programForm')[0].reset();
        $('#programForm .is-invalid').removeClass('is-invalid');
        $('#id_bed').val(b ? b.id_bed : '');
        $('#rdModalTitle').text(b ? 'Ubah Tempat Tidur' : 'Tambah Tempat Tidur');
        if (b) {
          $('#bed_name').val(b.bed_name);
          $('#bed_notes').val(b.bed_notes || '');
          $(`input[name="bed_gender"]`).filter((_, el) => el.value === b.bed_gender).prop('checked', true);
          $('#rdCapInfo').text('');
        } else {
          $('#gCampur').prop('checked', true);
          const cap = room.room_capacity || 0;
          $('#rdCapInfo').html(beds.length >= cap ?
            `<i class="fas fa-info-circle text-warning me-1"></i>Ruangan sudah memiliki ${beds.length} bed dari kapasitas ${cap}. Bed tetap bisa ditambahkan.` :
            `<i class="fas fa-info-circle me-1"></i>${beds.length} dari ${cap} bed sudah terdaftar.`);
        }
        $('#programModal').modal('show');
      }
      $('#programModal').on('shown.bs.modal', () => $('#bed_name').trigger('focus'));
      $('#btnTambah').on('click', () => openForm(null));
      $(document).on('click', '#bedAddCard', () => openForm(null));
      $(document).on('click', '.edit-btn', function() {
        openForm(beds.find(b => b.id_bed === +$(this).data('id')));
      });

      $('#programForm').on('submit', function(e) {
        e.preventDefault();
        const nama = $('#bed_name').val().trim();
        const gender = $('input[name="bed_gender"]:checked').val() || '';
        $('#bed_name').toggleClass('is-invalid', !nama);
        if (!nama) return $('#bed_name').trigger('focus');
        if (!gender) return Swal.fire('Perhatian', 'Pilih peruntukan tempat tidur.', 'warning');
        const id = $('#id_bed').val();
        const $btn = $('#rdSave').prop('disabled', true);
        api(apiUrl, {
          method: id ? 'PUT' : 'POST',
          ...form({
            id_bed: id,
            bed_name: nama,
            bed_gender: gender,
            bed_notes: $('#bed_notes').val().trim()
          })
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
        const b = beds.find(x => x.id_bed === +$(this).data('id'));
        if (!b) return;
        Swal.fire({
          title: 'Hapus tempat tidur?',
          html: `Bed <b>${esc(b.bed_name)}</b> akan dihapus permanen dari ruangan ini.`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#fa896b',
          confirmButtonText: 'Hapus',
          cancelButtonText: 'Batal'
        }).then(x => {
          if (!x.isConfirmed) return;
          api(apiUrl + '&id=' + b.id_bed, {
            method: 'DELETE'
          }).then(res => {
            if (res.status !== 'success') throw new Error(res.message);
            toast('success', res.message);
            load();
          }).catch(e => Swal.fire('Gagal!', e.message || 'Gagal menghapus.', 'error'));
        });
      });

      // ---------- status ----------
      $(document).on('change', '.toggle-bed', function() {
        const el = this,
          b = beds.find(x => x.id_bed === +$(el).data('id'));
        const status = el.checked ? 0 : 1;
        const kirim = () => {
          el.disabled = true;
          api(apiUrl + '&toggle=1', {
            method: 'PUT',
            ...form({
              id_bed: b.id_bed,
              bed_status: status
            })
          }).then(res => {
            if (res.status !== 'success') throw new Error(res.message);
            b.bed_status = status;
            toast('success', res.message);
            render();
          }).catch(e => {
            el.checked = !el.checked;
            el.disabled = false;
            Swal.fire('Gagal!', e.message || 'Gagal mengubah status.', 'error');
          });
        };
        if (status === 1 && b && b.pasien) {
          Swal.fire({
            icon: 'warning',
            title: 'Bed masih ditempati',
            html: `<b>${esc(b.pasien.nama || 'Pasien')}</b> masih tercatat rawat inap aktif di bed ini. Tetap tandai kosong?`,
            showCancelButton: true,
            confirmButtonText: 'Tetap kosongkan',
            cancelButtonText: 'Batal'
          }).then(x => x.isConfirmed ? kirim() : (el.checked = true));
        } else kirim();
      });

      // ---------- kosongkan ----------
      $('#btnResetBed').on('click', function() {
        const target = beds.filter(b => b.bed_status !== 1 && !b.pasien).length;
        const ditahan = beds.filter(b => b.bed_status !== 1 && b.pasien).length;
        if (!target) {
          return Swal.fire('Tidak ada yang dikosongkan', ditahan ? `${ditahan} bed terisi masih ditempati pasien rawat inap aktif.` : 'Semua tempat tidur sudah kosong.', 'info');
        }
        Swal.fire({
          title: 'Kosongkan tempat tidur?',
          html: `<b>${target}</b> bed terisi tanpa pasien aktif akan ditandai kosong.${ditahan ? `<br><small class="text-muted">${ditahan} bed yang masih ditempati pasien aktif tidak diubah.</small>` : ''}`,
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Ya, kosongkan',
          cancelButtonText: 'Batal'
        }).then(x => {
          if (!x.isConfirmed) return;
          api(apiUrl + '&reset=1', {
            method: 'POST'
          }).then(res => {
            if (res.status !== 'success') throw new Error(res.message);
            Swal.fire('Berhasil!', res.message, 'success');
            load();
          }).catch(e => Swal.fire('Gagal!', e.message || 'Gagal mengosongkan bed.', 'error'));
        });
      });

      load();
    })();
  </script>
</body>

</html>
