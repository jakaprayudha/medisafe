<?php
/**
 * Detail permintaan lab satu kunjungan: daftar pemeriksaan + input hasil.
 * Data : controller/visit/penunjangLabRoles (daftar/tambah/ubah/hapus), controller/lab/getLabItem (parameter + hasil),
 *        controller/lab/saveResult (simpan hasil). ?id=<id_inspection> menyorot baris dari halaman daftar.
 */
$title = 'Permintaan Lab';
require '../../controller/view.php';
$no = (string) ($_GET['no'] ?? '');
$rm = (string) ($_GET['rm'] ?? '');
$idSorot = (int) ($_GET['id'] ?? 0);
$opsiLab = [];
$st = $koneksi->prepare("SELECT assemen, MAX(tarif) tarif FROM laboratorium_detail WHERE status = '1' GROUP BY assemen ORDER BY assemen");
$st->execute();
$opsiLab = $st->get_result()->fetch_all(MYSQLI_ASSOC);
$st->close();
session_write_close();
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    @keyframes ld-spin { to { transform: rotate(360deg); } }
    .icon-spin { display: inline-block; animation: ld-spin 1s linear infinite; }
    .ld-icon { width: 40px; height: 40px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
    .ld-ring { display: flex; flex-wrap: wrap; gap: 8px; }
    .ld-ring span { font-size: .8rem; font-weight: 600; border-radius: 8px; padding: 4px 10px; border: 1px solid #e5eaef; background: #fff; color: #334155; }
    .ld-ring b { font-size: .95rem; margin-right: 3px; }
    #tblPeriksa { font-size: .86rem; }
    #tblPeriksa thead th { font-size: .75rem; text-transform: uppercase; letter-spacing: .03em; color: #475569; font-weight: 600; white-space: nowrap; background: #f8fafc; }
    #tblPeriksa td { vertical-align: middle; padding-top: .6rem; padding-bottom: .6rem; }
    #tblPeriksa tr.ld-sorot td { background: #eef2ff; }
    .ld-nama { font-weight: 600; color: #111827; }
    .ld-sub { font-size: .76rem; color: #475569; }
    .ld-catatan { max-width: 320px; white-space: pre-line; color: #334155; }
    .ld-aksi { display: flex; gap: 6px; white-space: nowrap; }
    .ld-b { font-size: .78rem; font-weight: 600; padding: 4px 10px; border-radius: 6px; border: 1px solid; display: inline-flex; align-items: center; gap: 4px; }
    .ld-b-input { background: #2563eb; border-color: #2563eb; color: #fff; }
    .ld-b-input:hover { background: #1d4ed8; color: #fff; }
    .ld-b-lihat { background: #15803d; border-color: #15803d; color: #fff; }
    .ld-b-lihat:hover { background: #166534; color: #fff; }
    .ld-b-ubah { background: #fff; border-color: #334155; color: #1f2937; padding: 4px 8px; }
    .ld-b-ubah:hover { background: #1e293b; color: #fff; }
    .ld-b-hapus { background: #fff; border-color: #dc2626; color: #b91c1c; padding: 4px 8px; }
    .ld-b-hapus:hover { background: #dc2626; color: #fff; }
    .ld-st { display: inline-flex; align-items: center; gap: 4px; font-size: .74rem; font-weight: 700; border-radius: 6px; padding: 2px 8px; border: 1px solid; white-space: nowrap; }
    .ld-st.belum { background: #fff; border-color: #fca5a5; color: #991b1b; }
    .ld-st.lengkap { background: #f0fdf4; border-color: #86efac; color: #166534; }
    .ld-kosong { padding: 36px 12px; text-align: center; color: #475569; }

    #tblHasil { font-size: .86rem; }
    #tblHasil thead th { position: sticky; top: 0; z-index: 1; background: #f8fafc; font-size: .74rem; text-transform: uppercase; color: #475569; }
    #tblHasil td { vertical-align: middle; }
    #tblHasil .hs-input { min-width: 120px; }
    #tblHasil tr.terisi .hs-input { border-color: #86efac; background: #f0fdf4; }
    #tblHasil tr.ubah .hs-input { border-color: #f59e0b; background: #fffbeb; }
    #tblHasil .hs-normal { color: #475569; font-size: .8rem; max-width: 220px; white-space: normal; }
    #hasilModal .modal-body { max-height: 65vh; overflow-y: auto; padding-top: 0; }
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
          <div class="mb-2">
            <a href="module/lab/permintaan-lab" class="btn btn-sm btn-outline-dark"><i class="ti ti-arrow-left me-1"></i>Kembali ke daftar permintaan</a>
          </div>
          <?php require '../admin/card-pasien.php'; ?>

          <div class="card border-0 shadow-sm">
            <div class="card-body p-3 p-md-4">
              <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                <span class="ld-icon bg-primary-subtle text-primary"><i class="ti ti-microscope"></i></span>
                <div class="flex-grow-1" style="min-width:0">
                  <h5 class="fw-semibold mb-1">Pemeriksaan Laboratorium</h5>
                  <div class="ld-ring" id="ldRingkas"><span>memuat…</span></div>
                </div>
                <div class="d-flex gap-2">
                  <button type="button" class="btn btn-sm btn-outline-dark" id="btnMuat"><i class="ti ti-refresh me-1"></i>Muat ulang</button>
                  <a class="btn btn-sm btn-outline-dark" target="_blank" href="module/admin/print/formulir_lab?no=<?= urlencode($no) ?>&rm=<?= urlencode($rm) ?>"><i class="ti ti-printer me-1"></i>Cetak</a>
                  <button type="button" class="btn btn-sm btn-primary" id="btnTambah"><i class="ti ti-plus me-1"></i>Tambah pemeriksaan</button>
                </div>
              </div>
              <div class="table-responsive">
                <table class="table align-middle mb-0" id="tblPeriksa">
                  <thead>
                    <tr>
                      <th style="width:1%">Aksi</th>
                      <th>Pemeriksaan</th>
                      <th>Status hasil</th>
                      <th>Tanggal</th>
                      <th>Sumber</th>
                      <th>Catatan</th>
                    </tr>
                  </thead>
                  <tbody id="bodyPeriksa"><tr><td colspan="6" class="ld-kosong">Memuat…</td></tr></tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="programModal" tabindex="-1">
    <div class="modal-dialog">
      <form id="programForm" class="modal-content" novalidate>
        <div class="modal-header">
          <h5 class="modal-title">Tambah pemeriksaan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_inspection" id="id_inspection">
          <input type="hidden" name="id_visit" value="<?= $h($no) ?>">
          <div class="mb-3">
            <label for="inspection_name" class="form-label required">Nama pemeriksaan</label>
            <select name="inspection_name" id="inspection_name" class="form-select" required>
              <option value=""></option>
              <?php foreach ($opsiLab as $o): ?>
                <option value="<?= $h($o['assemen']) ?>"><?= $h($o['assemen']) ?></option>
              <?php endforeach ?>
            </select>
            <div class="form-text" id="infoNama"></div>
          </div>
          <div class="row g-2">
            <div class="col-6 mb-3">
              <label for="inspection_date" class="form-label required">Tanggal pemeriksaan</label>
              <input type="date" class="form-control" id="inspection_date" name="inspection_date" required>
            </div>
            <div class="col-6 mb-3">
              <label for="inspection_source" class="form-label required">Sumber hasil</label>
              <input type="text" class="form-control" id="inspection_source" name="inspection_source" maxlength="255" required>
            </div>
          </div>
          <div class="mb-1">
            <label for="inspection_note" class="form-label">Catatan</label>
            <textarea class="form-control" id="inspection_note" name="inspection_note" rows="4" maxlength="5000"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary" id="btnSimpanForm">Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <div class="modal fade" id="hasilModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <div>
            <h5 class="modal-title mb-0" id="hasilJudul">Hasil lab</h5>
            <div class="ld-sub" id="hasilInfo"></div>
          </div>
          <button type="button" class="btn-close" id="hasilTutupX"></button>
        </div>
        <div class="modal-body">
          <div class="position-sticky top-0 bg-white py-2" style="z-index:2">
            <input type="search" class="form-control form-control-sm" id="hasilCari" placeholder="Cari parameter…" style="max-width:260px">
          </div>
          <table class="table table-sm table-bordered mb-0" id="tblHasil">
            <thead>
              <tr>
                <th style="width:40px">#</th>
                <th>Parameter</th>
                <th style="width:200px">Hasil</th>
                <th>Satuan</th>
                <th>Nilai normal</th>
                <th>Keterangan</th>
              </tr>
            </thead>
            <tbody id="hasilBody"></tbody>
          </table>
        </div>
        <div class="modal-footer justify-content-between">
          <div class="ld-sub"><kbd>Enter</kbd> pindah ke parameter berikutnya · <span id="hasilHitung"></span></div>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-dark" id="hasilBatal">Tutup</button>
            <button type="button" class="btn btn-primary" id="saveHasil"><i class="ti ti-device-floppy me-1"></i>Simpan hasil</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php require '../admin/library.php'; ?>
  <script>
    (function() {
      const NO = <?= json_encode($no) ?>;
      const SOROT = <?= $idSorot ?>;
      const API = 'controller/visit/penunjangLabRoles';
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);
      const kosong = v => v === null || v === undefined || String(v).trim() === '' || String(v).trim() === '-';
      const hariIni = () => { const d = new Date(); return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0'); };
      const fmtTgl = s => { if (!s) return '-'; const p = String(s).slice(0, 10).split('-'); return p.length === 3 ? `${p[2]}/${p[1]}/${p[0]}` : esc(s); };
      const jam = s => s && String(s).length >= 16 ? String(s).slice(11, 16) : '';
      async function ambilJson(url, opt) {
        const r = await fetch(url, Object.assign({ credentials: 'same-origin' }, opt || {}));
        let j = null;
        try { j = await r.json(); } catch (e) { throw new Error('Respon server tidak valid (' + r.status + ').'); }
        if (!r.ok || j.status !== 'success') throw new Error(j.message || ('Gagal (' + r.status + ').'));
        return j;
      }
      const gagal = e => Swal.fire('Gagal', esc(e.message || e), 'error');

      // ---------- daftar pemeriksaan ----------
      let daftar = [];
      let sudahSorot = false;
      async function muat() {
        $('#btnMuat i').addClass('icon-spin');
        try {
          const j = await ambilJson(API + '?no=' + encodeURIComponent(NO));
          daftar = j.data || [];
          const r = j.ringkas || {};
          $('#ldRingkas').html(
            `<span><b>${r.jumlah || 0}</b>pemeriksaan</span>` +
            `<span style="border-color:#86efac;color:#166534"><b>${r.ada_hasil || 0}</b>ada hasil</span>` +
            `<span style="border-color:#fca5a5;color:#991b1b"><b>${r.belum || 0}</b>belum ada hasil</span>`);
          gambar();
        } catch (e) {
          $('#bodyPeriksa').html(`<tr><td colspan="6" class="ld-kosong text-danger">${esc(e.message)}</td></tr>`);
          $('#ldRingkas').html('');
        } finally { $('#btnMuat i').removeClass('icon-spin'); }
      }
      function gambar() {
        if (!daftar.length) {
          $('#bodyPeriksa').html('<tr><td colspan="6" class="ld-kosong"><i class="ti ti-flask-off d-block fs-6 mb-1"></i>Belum ada pemeriksaan lab untuk kunjungan ini.</td></tr>');
          return;
        }
        $('#bodyPeriksa').html(daftar.map(d => {
          const ada = d.isi > 0;
          const info = d.harus > 0 ? `${d.isi}/${d.harus} parameter` : (ada ? `${d.isi} parameter` : 'parameter tidak ada di master');
          return `<tr data-id="${d.id_inspection}" class="${d.id_inspection === SOROT ? 'ld-sorot' : ''}">
            <td><div class="ld-aksi">
              <button type="button" class="ld-b ${ada ? 'ld-b-lihat' : 'ld-b-input'} b-hasil" data-id="${d.id_inspection}"><i class="ti ti-${ada ? 'file-text' : 'pencil'}"></i>${ada ? 'Lihat / ubah hasil' : 'Input hasil'}</button>
              <button type="button" class="ld-b ld-b-ubah b-ubah" data-id="${d.id_inspection}" title="Ubah data pemeriksaan"><i class="ti ti-edit"></i></button>
              <button type="button" class="ld-b ld-b-hapus b-hapus" data-id="${d.id_inspection}" title="Hapus pemeriksaan"><i class="ti ti-trash"></i></button>
            </div></td>
            <td><div class="ld-nama">${esc(d.inspection_name || '-')}</div><div class="ld-sub">${esc(d.inspection_number || '')}${d.created_at ? ' · diminta ' + fmtTgl(d.created_at) + ' ' + jam(d.created_at) : ''}</div></td>
            <td><span class="ld-st ${ada ? 'lengkap' : 'belum'}"><i class="ti ti-${ada ? 'circle-check' : 'hourglass'}"></i>${ada ? 'Ada hasil' : 'Belum ada hasil'}</span><div class="ld-sub mt-1">${info}${d.terakhir ? ' · ' + fmtTgl(d.terakhir) + ' ' + jam(d.terakhir) : ''}</div></td>
            <td class="text-nowrap">${fmtTgl(d.inspection_date)}</td>
            <td>${esc(d.inspection_source || '-')}</td>
            <td><div class="ld-catatan">${kosong(d.inspection_note) ? '<span class="text-muted">-</span>' : esc(d.inspection_note)}</div></td>
          </tr>`;
        }).join(''));
        if (SOROT && !sudahSorot) {
          sudahSorot = true;
          const tr = document.querySelector(`#bodyPeriksa tr[data-id="${SOROT}"]`);
          if (tr) tr.scrollIntoView({ block: 'center' });
        }
      }
      const cari = id => daftar.find(d => d.id_inspection === Number(id));

      // ---------- tambah / ubah ----------
      const $nama = $('#inspection_name');
      $nama.select2({
        dropdownParent: $('#programModal'), width: '100%', tags: true, placeholder: 'Ketik atau pilih pemeriksaan',
        createTag: p => { const t = $.trim(p.term); return t ? { id: t, text: t, newOption: true } : null; },
        templateResult: d => { const s = $('<span>').text(d.text); if (d.newOption) s.append(' <em>(di luar master, hasil tidak bisa diinput per parameter)</em>'); return s; }
      });
      function setNama(v) {
        if (v && !$nama.find('option').filter((i, o) => o.value === v).length) $nama.append(new Option(v, v, false, false));
        $nama.val(v || '').trigger('change');
      }
      let namaAsal = '', isiAsal = 0;
      $nama.on('change', function() {
        const v = $(this).val();
        $('#infoNama').html(isiAsal > 0 && v !== namaAsal ? '<span class="text-danger">Hasil sudah diisi — jenis pemeriksaan tidak bisa diganti.</span>' : '');
      });
      $('#btnTambah').on('click', function() {
        $('#programForm')[0].reset();
        $('#id_inspection').val('');
        namaAsal = ''; isiAsal = 0;
        setNama('');
        $('#inspection_date').val(hariIni());
        $('#inspection_source').val('Lab Klinik');
        $('#programModal .modal-title').text('Tambah pemeriksaan');
        $('#programModal').modal('show');
      });
      $(document).on('click', '.b-ubah', function() {
        const d = cari($(this).data('id'));
        if (!d) return;
        $('#programForm')[0].reset();
        $('#id_inspection').val(d.id_inspection);
        namaAsal = d.inspection_name || ''; isiAsal = d.isi;
        setNama(namaAsal);
        $('#inspection_date').val(String(d.inspection_date || '').slice(0, 10) || hariIni());
        $('#inspection_source').val(d.inspection_source || 'Lab Klinik');
        $('#inspection_note').val(d.inspection_note || '');
        $('#programModal .modal-title').text('Ubah pemeriksaan');
        $('#programModal').modal('show');
      });
      $('#programForm').on('submit', async function(e) {
        e.preventDefault();
        if (!$nama.val()) return Swal.fire('Lengkapi data', 'Nama pemeriksaan wajib dipilih.', 'warning');
        if (!$('#inspection_date').val()) return Swal.fire('Lengkapi data', 'Tanggal pemeriksaan wajib diisi.', 'warning');
        const btn = $('#btnSimpanForm').prop('disabled', true);
        try {
          const j = await ambilJson(API, { method: 'POST', body: new FormData(this) });
          $('#programModal').modal('hide');
          Swal.fire({ icon: 'success', title: j.message, timer: 1300, showConfirmButton: false });
          await muat();
        } catch (er) { gagal(er); } finally { btn.prop('disabled', false); }
      });
      $(document).on('click', '.b-hapus', async function() {
        const d = cari($(this).data('id'));
        if (!d) return;
        const r = await Swal.fire({
          title: 'Hapus pemeriksaan?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Hapus', cancelButtonText: 'Batal', confirmButtonColor: '#dc2626',
          html: `<b>${esc(d.inspection_name)}</b>` + (d.isi > 0 ? `<div class="text-danger mt-2">${d.isi} hasil parameter yang sudah diisi ikut terhapus.</div>` : '')
        });
        if (!r.isConfirmed) return;
        try {
          const j = await ambilJson(API + '?id=' + encodeURIComponent(d.id_inspection), { method: 'DELETE' });
          Swal.fire({ icon: 'success', title: j.message, timer: 1300, showConfirmButton: false });
          await muat();
        } catch (er) { gagal(er); }
      });

      // ---------- input hasil ----------
      let hasilId = null;
      const $hb = $('#hasilBody');
      const normal = it => {
        const mn = kosong(it.minimum) ? '' : String(it.minimum).trim();
        const mx = kosong(it.maksimum) || String(it.maksimum).trim() === '0' ? '' : String(it.maksimum).trim();
        if (mn && mx) return /\d/.test(mx) && mx.length < 15 && mn.length < 15 ? `${esc(mn)} – ${esc(mx)}` : `${esc(mn)}<br>${esc(mx)}`;
        return esc(mn || mx) || '<span class="text-muted">-</span>';
      };
      function hitungHasil() {
        const semua = $hb.find('.hs-input');
        const isi = semua.filter((i, el) => el.value.trim() !== '').length;
        const ubah = $hb.find('tr.ubah').length;
        $('#hasilHitung').text(`${isi}/${semua.length} terisi` + (ubah ? ` · ${ubah} belum disimpan` : ''));
      }
      $(document).on('click', '.b-hasil', async function() {
        const d = cari($(this).data('id'));
        if (!d) return;
        hasilId = d.id_inspection;
        $('#hasilJudul').text('Hasil ' + (d.inspection_name || 'lab'));
        $('#hasilInfo').text([d.inspection_number, fmtTgl(d.inspection_date), d.inspection_source].filter(Boolean).join(' · '));
        $('#hasilCari').val('');
        $hb.html('<tr><td colspan="6" class="ld-kosong">Memuat parameter…</td></tr>');
        $('#saveHasil').prop('disabled', true);
        $('#hasilHitung').text('');
        $('#hasilModal').modal('show');
        try {
          const j = await ambilJson(`controller/lab/getLabItem?kode=${encodeURIComponent(d.inspection_name || '')}&id_inspection=${d.id_inspection}`);
          const items = j.data || [];
          if (!items.length) {
            $hb.html(`<tr><td colspan="6" class="ld-kosong">"${esc(d.inspection_name)}" tidak punya parameter di master laboratorium,<br>jadi hasil per parameter tidak bisa diinput. Tulis hasilnya di <b>Catatan</b> (tombol ubah).</td></tr>`);
            return;
          }
          $hb.html(items.map((it, i) => {
            const v = it.hasil ?? '';
            return `<tr class="${String(v).trim() ? 'terisi' : ''}" data-cari="${esc(String(it.assemen || '').toLowerCase())}">
              <td class="text-muted">${i + 1}</td>
              <td class="fw-semibold">${esc(it.assemen)}</td>
              <td><input type="text" class="form-control form-control-sm hs-input" maxlength="255" data-id="${esc(it.id)}" data-awal="${esc(v)}" value="${esc(v)}" aria-label="Hasil ${esc(it.assemen)}"></td>
              <td>${kosong(it.satuan) ? '<span class="text-muted">-</span>' : esc(it.satuan)}</td>
              <td class="hs-normal">${normal(it)}</td>
              <td class="hs-normal">${kosong(it.catatan) ? '<span class="text-muted">-</span>' : esc(it.catatan)}</td>
            </tr>`;
          }).join(''));
          $('#saveHasil').prop('disabled', false);
          hitungHasil();
          setTimeout(() => { const f = $hb.find('.hs-input').filter((i, el) => !el.value.trim()).first(); (f.length ? f : $hb.find('.hs-input').first()).trigger('focus'); }, 350);
        } catch (er) {
          $hb.html(`<tr><td colspan="6" class="ld-kosong text-danger">${esc(er.message)}</td></tr>`);
        }
      });
      $hb.on('input', '.hs-input', function() {
        const tr = $(this).closest('tr');
        tr.toggleClass('ubah', this.value.trim() !== String($(this).data('awal') ?? '').trim());
        tr.toggleClass('terisi', this.value.trim() !== '');
        hitungHasil();
      });
      $hb.on('keydown', '.hs-input', function(e) {
        if (e.key !== 'Enter') return;
        e.preventDefault();
        const semua = $hb.find('tr:visible .hs-input');
        const n = semua.index(this);
        if (n < semua.length - 1) semua.eq(n + 1).trigger('focus').trigger('select');
        else $('#saveHasil').trigger('focus');
      });
      $('#hasilCari').on('input', function() {
        const q = this.value.trim().toLowerCase();
        $hb.find('tr[data-cari]').each(function() { $(this).toggle(!q || this.dataset.cari.includes(q)); });
      });
      async function tutupHasil() {
        const ubah = $hb.find('tr.ubah').length;
        if (ubah) {
          const r = await Swal.fire({ icon: 'warning', title: 'Perubahan belum disimpan', text: `${ubah} hasil belum disimpan. Tutup tanpa menyimpan?`, showCancelButton: true, confirmButtonText: 'Tutup tanpa simpan', cancelButtonText: 'Kembali', confirmButtonColor: '#dc2626' });
          if (!r.isConfirmed) return;
        }
        $('#hasilModal').modal('hide');
      }
      $('#hasilBatal, #hasilTutupX').on('click', tutupHasil);
      $('#hasilModal').on('keydown', function(e) { if (e.key === 'Escape' && !Swal.isVisible()) { e.preventDefault(); tutupHasil(); } });
      $('#saveHasil').on('click', async function() {
        const data = [];
        $hb.find('tr.ubah .hs-input').each(function() { data.push({ id_item: String($(this).data('id')), hasil: this.value.trim() }); });
        if (!data.length) return Swal.fire({ icon: 'info', title: 'Tidak ada perubahan', timer: 1200, showConfirmButton: false });
        const btn = $(this).prop('disabled', true).html('<i class="ti ti-loader-2 icon-spin me-1"></i>Menyimpan…');
        try {
          await ambilJson('controller/lab/saveResult.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id_inspection: hasilId, data }) });
          $hb.find('tr.ubah').removeClass('ubah');
          $('#hasilModal').modal('hide');
          Swal.fire({ icon: 'success', title: `${data.length} hasil disimpan`, timer: 1300, showConfirmButton: false });
          muat();
        } catch (er) { gagal(er); } finally { btn.prop('disabled', false).html('<i class="ti ti-device-floppy me-1"></i>Simpan hasil'); }
      });

      $('#btnMuat').on('click', muat);
      muat();
    })();
  </script>
</body>

</html>
