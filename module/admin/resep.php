<?php
/**
 * Resep Luar — resep untuk ditebus di apotek luar (per kunjungan).
 * Perbaikan: $_GET['rme'] tanpa default (warning), id kunjungan dicetak mentah ke form (XSS), isi resep disisipkan
 * ke tabel tanpa escape, URL simpan ganda "?" (…?no=X?id=Y), modal di luar </body>, gagal hapus tanpa pesan,
 * kolom Aksi di paling kanan.
 */
$title = 'Resep Luar';
require '../../controller/view.php';
$no = trim((string) ($_GET['no'] ?? ''));
$rm = trim((string) ($_GET['rm'] ?? ''));
$rme = (string) ($_GET['rme'] ?? '');
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .rl .kepala { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-bottom: 14px; }
    .rl .kepala .ic { width: 42px; height: 42px; border-radius: 12px; background: #ede9fe; color: #5b21b6; display: inline-flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
    .rl .kepala h5 { margin: 0; font-weight: 700; color: #0f172a; }
    .rl .kepala small { color: #475569; }
    .rl .resep { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 14px 16px; height: 100%; display: flex; gap: 14px; }
    .rl .resep .r { font: italic 700 1.6rem Georgia, 'Times New Roman', serif; color: #5b21b6; line-height: 1; flex-shrink: 0; width: 34px; }
    .rl .resep .isi { flex: 1; min-width: 0; }
    .rl .resep .atas { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-bottom: 6px; }
    .rl .resep .aksi { display: flex; gap: 6px; }
    .rl .resep .nomor { font-size: .74rem; color: #475569; margin-left: auto; }
    .rl .resep .pres { font-size: 1.02rem; font-weight: 700; color: #0f172a; white-space: pre-line; word-break: break-word; }
    .rl .resep .sig { font-size: .92rem; color: #1f2937; margin-top: 2px; white-space: pre-line; word-break: break-word; }
    .rl .resep .sig b { font-family: Georgia, 'Times New Roman', serif; font-style: italic; color: #5b21b6; }
    .rl .resep .ket { display: grid; grid-template-columns: 92px 1fr; gap: 2px 8px; font-size: .8rem; margin-top: 8px; padding-top: 8px; border-top: 1px dashed #e2e8f0; }
    .rl .resep .ket span { color: #64748b; }
    .rl .resep .ket div { color: #1f2937; white-space: pre-line; word-break: break-word; }
    .rl .kosong { text-align: center; padding: 40px 10px; color: #475569; }
    .rl .kosong i { font-size: 2.4rem; color: #94a3b8; display: block; margin-bottom: 6px; }
    #programModal .form-label { font-weight: 600; margin-bottom: 3px; }
    #programModal .contoh { font-size: .76rem; color: #64748b; }
    #programModal .chip { border: 1px solid #cbd5e1; background: #fff; color: #1f2937; border-radius: 999px; padding: 2px 10px; font-size: .76rem; margin: 4px 4px 0 0; }
    #programModal .chip:hover { background: #f1f5f9; }
  </style>
</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <?php require 'sidebar.php'; ?>
    <div class="body-wrapper">
      <?php require 'navbar.php'; ?>
      <div class="body-wrapper-inner">
        <div class="container-fluid rl">
          <?php
          if ($rme === 'a') include 'menu_rme.php';
          elseif ($rme === 'b') include 'menu_rmeb.php';
          elseif ($rme === 'c') include 'menu_rme_inap.php';
          ?>
          <?php require 'card-pasien.php'; ?>

          <div class="card w-100">
            <div class="card-body p-3 p-md-4">
              <div class="kepala">
                <span class="ic"><i class="ti ti-file-text"></i></span>
                <div class="flex-grow-1">
                  <h5>Resep Luar <span class="badge bg-dark ms-1" id="jumlahResep">0</span></h5>
                  <small>Resep untuk ditebus pasien di apotek luar klinik.</small>
                </div>
                <div class="d-flex flex-wrap gap-2">
                  <a class="btn btn-outline-dark disabled" id="btnCetak" target="_blank" href="module/admin/print_resep_luar?no=<?= $h(urlencode($no)) ?>&rm=<?= $h(urlencode($rm)) ?>"><i class="ti ti-printer me-1"></i>Cetak Resep</a>
                  <button type="button" class="btn btn-primary" id="btnTambah"><i class="ti ti-plus me-1"></i>Tambah Resep</button>
                </div>
              </div>
              <div class="mb-3" style="max-width:340px">
                <input type="search" class="form-control" id="customSearch" placeholder="Cari obat / signatura...">
              </div>
              <div class="row g-3" id="daftarResep">
                <div class="col-12 kosong"><span class="spinner-border spinner-border-sm me-2"></span>Memuat resep...</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="programModal" tabindex="-1" aria-labelledby="programModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <form id="programForm" class="modal-content" novalidate>
        <div class="modal-header">
          <h5 class="modal-title" id="programModalTitle">Tambah Resep Luar</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_resep" id="id_resep">
          <input type="hidden" name="id_visit" id="id_visit" value="<?= $h($no) ?>">
          <div class="row g-3">
            <div class="col-md-6">
              <label for="prescriptio" class="form-label">R/ Prescriptio <span class="text-danger">*</span></label>
              <textarea name="prescriptio" class="form-control" id="prescriptio" rows="3" maxlength="2000" required placeholder="Tab. Paracetamol 500 mg No. X"></textarea>
              <div class="contoh">Nama obat, kekuatan, bentuk sediaan, dan jumlahnya.</div>
            </div>
            <div class="col-md-6">
              <label for="signatura" class="form-label">S. Signatura <span class="text-danger">*</span></label>
              <textarea name="signatura" class="form-control" id="signatura" rows="3" maxlength="2000" required placeholder="3 dd tab I p.c."></textarea>
              <div class="contoh">Aturan pakai. Klik untuk menyisipkan:</div>
              <div>
                <?php foreach (['3 dd tab I', '2 dd tab I', '1 dd tab I', 'p.c. (sesudah makan)', 'a.c. (sebelum makan)', 'p.r.n. (bila perlu)', 'u.e. (pemakaian luar)'] as $c): ?>
                  <button type="button" class="chip" data-ke="signatura"><?= $h($c) ?></button>
                <?php endforeach ?>
              </div>
            </div>
            <div class="col-md-6">
              <label for="subscriptio" class="form-label">Subscriptio</label>
              <textarea name="subscriptio" class="form-control" id="subscriptio" rows="2" maxlength="2000" placeholder="m.f. pulv. dtd No. X"></textarea>
              <div class="contoh">Perintah untuk apoteker, mis. m.f. pulv. (buat serbuk), m.f. sol. (buat larutan).</div>
            </div>
            <div class="col-md-6">
              <label for="pro" class="form-label">Pro</label>
              <textarea name="pro" class="form-control" id="pro" rows="2" maxlength="2000" placeholder="Nama pasien / pro infante"></textarea>
              <div class="contoh">Untuk siapa obat ditujukan.</div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary" id="btnSimpan"><i class="ti ti-device-floppy me-1"></i>Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <?php require 'library.php'; ?>
  <script>
    (function() {
      const NO_VISIT = <?= json_encode($no) ?>;
      const apiUrl = 'controller/visit/resepLuar?no=' + encodeURIComponent(NO_VISIT);
      const esc = (v) => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const waktu = (s) => { const d = s ? new Date(String(s).replace(' ', 'T')) : null; return d && !isNaN(d) ? d.toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : ''; };
      const $list = $('#daftarResep');
      let resep = [];

      function muat() {
        return fetch(apiUrl, { cache: 'no-store' })
          .then(r => r.json())
          .then(res => {
            if (res.status !== 'success') throw new Error(res.message);
            resep = res.data || [];
            render();
          })
          .catch(err => $list.html(`<div class="col-12 kosong text-danger"><i class="ti ti-alert-triangle"></i>${esc(err.message || 'Gagal memuat resep.')}</div>`));
      }

      function kartu(r) {
        return `<div class="col-12 col-xl-6"><div class="resep">
          <div class="r">R/</div>
          <div class="isi">
            <div class="atas">
              <div class="aksi">
                <button type="button" class="btn btn-sm btn-warning edit-btn" data-id="${+r.id_resep}"><i class="ti ti-pencil me-1"></i>Ubah</button>
                <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="${+r.id_resep}" title="Hapus"><i class="ti ti-trash"></i></button>
              </div>
              <span class="nomor">${esc(r.resep_number || '')}${r.created_at ? ' · ' + esc(waktu(r.created_at)) : ''}</span>
            </div>
            <div class="pres">${esc(r.prescriptio || '-')}</div>
            <div class="sig"><b>S.</b> ${esc(r.signatura || '-')}</div>
            ${r.subscriptio || r.pro ? `<div class="ket">
              ${r.subscriptio ? `<span>Subscriptio</span><div>${esc(r.subscriptio)}</div>` : ''}
              ${r.pro ? `<span>Pro</span><div>${esc(r.pro)}</div>` : ''}
            </div>` : ''}
          </div>
        </div></div>`;
      }

      function render() {
        $('#jumlahResep').text(resep.length);
        $('#btnCetak').toggleClass('disabled', !resep.length);
        const q = String($('#customSearch').val() || '').trim().toLowerCase();
        const tampil = resep.filter(r => !q || [r.prescriptio, r.signatura, r.subscriptio, r.pro, r.resep_number].join(' ').toLowerCase().includes(q));
        if (!resep.length) {
          $list.html(`<div class="col-12 kosong"><i class="ti ti-file-off"></i><b>Belum ada resep luar.</b>
            <div class="small">Klik <b>Tambah Resep</b> di atas bila obat ditebus di apotek luar.</div></div>`);
          return;
        }
        $list.html(tampil.length ? tampil.map(kartu).join('') : '<div class="col-12 kosong"><i class="ti ti-search"></i>Tidak ada resep yang cocok.</div>');
      }
      $('#customSearch').on('input', render);

      function bukaForm(d) {
        const f = $('#programForm')[0];
        f.reset();
        $(f).find('.is-invalid').removeClass('is-invalid');
        $('#id_resep').val(d ? d.id_resep : '');
        ['prescriptio', 'signatura', 'subscriptio', 'pro'].forEach(k => $('#' + k).val(d ? (d[k] || '') : ''));
        $('#programModalTitle').text(d ? `Ubah Resep ${d.resep_number || ''}` : 'Tambah Resep Luar');
        $('#programModal').modal('show');
      }
      $('#programModal').on('shown.bs.modal', () => $('#prescriptio').trigger('focus'));
      $('#btnTambah').on('click', () => bukaForm(null));
      $(document).on('click', '.edit-btn', function() {
        const d = resep.find(r => +r.id_resep === +$(this).data('id'));
        if (d) bukaForm(d);
      });

      // sisipkan singkatan signatura
      $(document).on('click', '#programModal .chip', function() {
        const $t = $('#' + $(this).data('ke'));
        const v = $t.val().trim();
        $t.val((v ? v + ' ' : '') + $(this).text().trim()).trigger('focus').removeClass('is-invalid');
      });
      $('#programForm').on('input', 'textarea', function() { $(this).removeClass('is-invalid'); });

      $('#programForm').on('submit', function(e) {
        e.preventDefault();
        let salah = null;
        ['prescriptio', 'signatura'].forEach(k => {
          if (!$('#' + k).val().trim()) { $('#' + k).addClass('is-invalid'); salah = salah || $('#' + k); }
        });
        if (salah) return salah.trigger('focus');
        const id = $('#id_resep').val();
        const $b = $('#btnSimpan').prop('disabled', true);
        fetch(apiUrl + (id ? '&id=' + encodeURIComponent(id) : ''), {
            method: id ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams(new FormData(this))
          })
          .then(r => r.json())
          .then(data => {
            if (data.status === 'success') {
              $('#programModal').modal('hide');
              Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message, timer: 1400, showConfirmButton: false });
              muat();
            } else {
              if (data.field) $('#' + data.field).addClass('is-invalid');
              Swal.fire('Gagal', data.message || 'Resep gagal disimpan.', 'error');
            }
          })
          .catch(() => Swal.fire('Gagal', 'Terjadi kesalahan pada server.', 'error'))
          .finally(() => $b.prop('disabled', false));
      });

      $(document).on('click', '.delete-btn', function() {
        const id = $(this).data('id');
        const d = resep.find(r => +r.id_resep === +id) || {};
        Swal.fire({
          title: 'Hapus resep ini?',
          html: `<b>R/</b> ${esc(d.prescriptio || '')}`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Hapus',
          confirmButtonColor: '#dc2626',
          cancelButtonText: 'Batal',
          reverseButtons: true
        }).then(x => {
          if (!x.isConfirmed) return;
          fetch(apiUrl + '&id=' + encodeURIComponent(id), { method: 'DELETE' })
            .then(r => r.json())
            .then(data => {
              if (data.status === 'success') {
                Swal.fire({ icon: 'success', title: 'Dihapus', timer: 1200, showConfirmButton: false });
                muat();
              } else Swal.fire('Gagal', data.message || 'Resep gagal dihapus.', 'error');
            })
            .catch(() => Swal.fire('Gagal', 'Terjadi kesalahan pada server.', 'error'));
        });
      });

      muat();
    })();
  </script>
</body>

</html>
