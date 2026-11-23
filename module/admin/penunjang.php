<?php
/**
 * Pemeriksaan Penunjang (lab) — permintaan & hasil kunjungan ini + riwayat lab pasien di kunjungan lain.
 * Aturan: sumber hasil dikunci "Lab Klinik"; catatan wajib teks tanpa angka.
 * Perbaikan: query kunjungan dirangkai langsung dari URL (SQL injection) tanpa batas faskes, $_GET['rme'] tanpa
 * default, id kunjungan dicetak mentah (XSS), isi tabel/hasil disisipkan tanpa escape, select2 diinisialisasi ulang
 * tiap modal dibuka tanpa mode ketik -> nama pemeriksaan ketikan sendiri hilang & saat ubah nama bisa terkirim
 * kosong, pemeriksaan kunjungan lama ikut bisa diubah/dihapus dari kunjungan ini, modal di luar </body>,
 * gagal hapus tanpa pesan, kolom Aksi di paling kanan.
 */
$title = 'Pemeriksaan Penunjang';
require '../../controller/view.php';
$no = trim((string) ($_GET['no'] ?? ''));
$rm = trim((string) ($_GET['rm'] ?? ''));
$rme = (string) ($_GET['rme'] ?? '');
$idc = (int) ($_SESSION['id_customer'] ?? 0);
$h = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');

$st = $koneksi->prepare("SELECT visit_status FROM pasien_visit WHERE visit_ID = ? AND id_customer = ? LIMIT 1");
$st->bind_param('si', $no, $idc);
$st->execute();
$kv = $st->get_result()->fetch_assoc();
$st->close();

$st = $koneksi->prepare("SELECT assemen, tarif FROM laboratorium_detail WHERE status = '1' ORDER BY assemen");
$st->execute();
$masterLab = $st->get_result()->fetch_all(MYSQLI_ASSOC);
$st->close();
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .pj .kepala { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-bottom: 14px; }
    .pj .kepala .ic { width: 42px; height: 42px; border-radius: 12px; background: #e0f2fe; color: #075985; display: inline-flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
    .pj .kepala h5 { margin: 0; font-weight: 700; color: #0f172a; }
    .pj .kepala small { color: #475569; }
    .pj .sec { border: 1px solid #e2e8f0; border-radius: 14px; padding: 14px 16px; margin-bottom: 16px; background: #fff; }
    .pj .sec-h { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-bottom: 10px; }
    .pj .sec-h b { font-size: 1rem; color: #0f172a; }
    .pj table.t { width: 100%; border-collapse: collapse; font-size: .86rem; }
    .pj table.t th { font-size: .7rem; text-transform: uppercase; color: #475569; font-weight: 700; background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 7px 8px; white-space: nowrap; }
    .pj table.t td { border-bottom: 1px solid #eef2f6; padding: 8px; vertical-align: top; color: #1f2937; }
    .pj table.t tr:last-child td { border-bottom: 0; }
    .pj .aksi { white-space: nowrap; }
    .pj .aksi .btn { padding: 3px 9px; font-weight: 600; }
    .pj .nama { font-weight: 700; color: #0f172a; }
    .pj .nomor { font-size: .72rem; color: #64748b; }
    .pj .st { font-size: .72rem; font-weight: 700; border-radius: 6px; padding: 2px 8px; white-space: nowrap; display: inline-block; }
    .pj .st-ok { background: #dcfce7; color: #14532d; }
    .pj .st-belum { background: #fef3c7; color: #92400e; }
    .pj .hasil { font-weight: 700; color: #0f172a; }
    .pj .catatan { font-size: .8rem; color: #334155; white-space: pre-line; }
    .pj .kosong { text-align: center; padding: 26px 10px; color: #475569; }
    .pj .kosong i { font-size: 2rem; color: #94a3b8; display: block; margin-bottom: 6px; }
    #hasilModal .flag { font-size: .7rem; font-weight: 800; border-radius: 5px; padding: 1px 6px; }
    #hasilModal .flag-H { background: #fee2e2; color: #991b1b; }
    #hasilModal .flag-L { background: #dbeafe; color: #1e3a8a; }
    #hasilModal tr.abn .hasil-input { border-color: #f59e0b; background: #fffbeb; font-weight: 700; }
    #hasilModal .hasil-input { font-weight: 600; }
  </style>
</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <?php require 'sidebar.php'; ?>
    <div class="body-wrapper">
      <?php require 'navbar.php'; ?>
      <div class="body-wrapper-inner">
        <div class="container-fluid pj">
          <?php
          if ($rme === 'a') include 'menu_rme.php';
          elseif ($rme === 'b') include 'menu_rmeb.php';
          elseif ($rme === 'c') include 'menu_rme_inap.php';
          ?>
          <?php if (!$kv): ?>
            <div class="alert alert-danger mt-3">Kunjungan tidak ditemukan untuk faskes ini.</div>
          <?php else: ?>
          <?php require 'card-pasien.php'; ?>

          <div class="card w-100">
            <div class="card-body p-3 p-md-4">
              <div class="kepala">
                <span class="ic"><i class="ti ti-microscope"></i></span>
                <div class="flex-grow-1">
                  <h5>Pemeriksaan Penunjang</h5>
                  <small>Permintaan laboratorium kunjungan ini, input hasil, dan riwayat lab pasien.</small>
                </div>
                <div class="d-flex flex-wrap gap-2">
                  <a class="btn btn-outline-dark" target="_blank" href="module/admin/print/formulir_lab?no=<?= $h(urlencode($no)) ?>&rm=<?= $h(urlencode($rm)) ?>"><i class="ti ti-printer me-1"></i>Cetak</a>
                  <?php if ((int) $kv['visit_status'] !== 99): ?>
                    <button type="button" class="btn btn-primary" id="btnTambah"><i class="ti ti-plus me-1"></i>Tambah Pemeriksaan</button>
                  <?php endif ?>
                </div>
              </div>
              <?php if ((int) $kv['visit_status'] === 99): ?>
                <div class="alert alert-warning"><i class="ti ti-ban me-1"></i>Kunjungan ini sudah <b>dibatalkan</b>.</div>
              <?php endif ?>

              <div class="sec">
                <div class="sec-h">
                  <b><i class="ti ti-flask me-1"></i>Kunjungan ini</b>
                  <span class="badge bg-dark" id="nKini">0</span>
                  <span class="small text-muted ms-md-2" id="ringkasKini"></span>
                </div>
                <div class="table-responsive">
                  <table class="t">
                    <thead>
                      <tr>
                        <th style="width:1%">Aksi</th>
                        <th>Pemeriksaan</th>
                        <th>Tanggal</th>
                        <th>Hasil</th>
                        <th>Catatan</th>
                      </tr>
                    </thead>
                    <tbody id="tbKini"><tr><td colspan="5" class="kosong"><span class="spinner-border spinner-border-sm me-2"></span>Memuat...</td></tr></tbody>
                  </table>
                </div>
              </div>

              <div class="sec mb-0">
                <div class="sec-h">
                  <b><i class="ti ti-history me-1"></i>Riwayat lab kunjungan sebelumnya</b>
                  <span class="badge bg-dark" id="nRiwayat">0</span>
                  <input type="search" class="form-control ms-md-auto" id="cariRiwayat" placeholder="Cari pemeriksaan..." style="max-width:260px">
                </div>
                <div class="table-responsive" style="max-height:50vh">
                  <table class="t">
                    <thead>
                      <tr>
                        <th style="width:1%">Aksi</th>
                        <th>Pemeriksaan</th>
                        <th>Tanggal</th>
                        <th>Hasil</th>
                        <th>Catatan</th>
                      </tr>
                    </thead>
                    <tbody id="tbRiwayat"></tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
          <?php endif ?>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="programModal" tabindex="-1" aria-labelledby="programModalTitle" aria-hidden="true">
    <div class="modal-dialog">
      <form id="programForm" class="modal-content" novalidate>
        <div class="modal-header">
          <h5 class="modal-title" id="programModalTitle">Tambah Pemeriksaan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_inspection" id="id_inspection">
          <input type="hidden" name="id_visit" id="id_visit" value="<?= $h($no) ?>">
          <div class="mb-3">
            <label for="inspection_name" class="form-label fw-semibold">Nama pemeriksaan <span class="text-danger">*</span></label>
            <select name="inspection_name" id="inspection_name" class="form-select" style="width:100%">
              <option value=""></option>
              <?php foreach ($masterLab as $m): ?>
                <option value="<?= $h($m['assemen']) ?>"><?= $h($m['assemen']) ?></option>
              <?php endforeach ?>
            </select>
            <div class="form-text">Pilih dari daftar, atau ketik nama pemeriksaan lain.</div>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-6">
              <label for="inspection_date" class="form-label fw-semibold">Tanggal <span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="inspection_date" name="inspection_date" max="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-6">
              <label for="inspection_source" class="form-label fw-semibold">Sumber hasil</label>
              <input type="text" value="Lab Klinik" class="form-control bg-light" id="inspection_source" name="inspection_source" readonly tabindex="-1" title="Sumber hasil dikunci: Lab Klinik">
            </div>
          </div>
          <div>
            <label for="inspection_note" class="form-label fw-semibold">Catatan <span class="text-danger">*</span></label>
            <textarea class="form-control catatan-teks" id="inspection_note" name="inspection_note" rows="4" maxlength="2000" placeholder="Mis. puasa sejak malam, sampel darah vena"></textarea>
            <div class="form-text">Wajib diisi berupa teks — angka tidak bisa diketik.</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary" id="btnSimpan"><i class="ti ti-device-floppy me-1"></i>Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <div class="modal fade" id="hasilModal" tabindex="-1" aria-labelledby="hasilModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <div>
            <h5 class="modal-title mb-0" id="hasilModalTitle">Input Hasil Lab</h5>
            <small class="text-muted" id="hasilInfo"></small>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div id="previewContainer" style="display:none;">
            <iframe id="previewFrame" title="Preview hasil laboratorium" style="width:100%; height:72vh; border:none;"></iframe>
          </div>
          <div class="table-responsive" id="hasilWrap">
            <table class="table table-sm align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Parameter</th>
                  <th style="width:170px">Hasil</th>
                  <th>Satuan</th>
                  <th>Nilai normal</th>
                  <th>Keterangan</th>
                </tr>
              </thead>
              <tbody id="hasilBody"></tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer" id="hasilFooter">
          <span class="small text-muted me-auto" id="hasilProgres"></span>
          <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-primary" id="saveHasil"><i class="ti ti-device-floppy me-1"></i>Simpan Hasil</button>
        </div>
      </div>
    </div>
  </div>

  <?php require 'library.php'; ?>
<?php if ($kv): ?>
<script>
  (function() {
    const NO = <?= json_encode($no) ?>;
    const RM = <?= json_encode($rm) ?>;
    const apiUrl = 'controller/visit/penunjang?no=' + encodeURIComponent(NO);
    // pemeriksaan yang hasilnya dientri lewat modul lab (format khusus) -> hanya preview
    const LAB_KHUSUS = ['ureum / kreatinin', 'darah lengkap', 'widal'];
    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const tgl = (s) => { const d = s ? new Date(String(s).slice(0, 10) + 'T00:00:00') : null; return d && !isNaN(d) ? d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-'; };
    const hariIni = () => { const d = new Date(); d.setMinutes(d.getMinutes() - d.getTimezoneOffset()); return d.toISOString().slice(0, 10); };
    const isKhusus = (nama) => LAB_KHUSUS.some(k => String(nama || '').toLowerCase().includes(k));
    let data = [];

    // ---------- daftar ----------
    function muat() {
      return fetch(apiUrl, { cache: 'no-store' })
        .then(r => r.json())
        .then(res => {
          if (res.status !== 'success') throw new Error(res.message);
          data = res.data || [];
          render();
        })
        .catch(err => $('#tbKini').html(`<tr><td colspan="5" class="kosong text-danger"><i class="ti ti-alert-triangle"></i>${esc(err.message || 'Gagal memuat data.')}</td></tr>`));
    }

    function baris(r, kini) {
      const khusus = isKhusus(r.inspection_name);
      const terisi = +r.hasil_terisi > 0;
      const btnHasil = khusus ?
        `<button type="button" class="btn btn-sm btn-dark hasil-btn" data-id="${+r.id_inspection}"><i class="ti ti-eye me-1"></i>Lihat</button>` :
        (kini ?
          `<button type="button" class="btn btn-sm ${terisi ? 'btn-success' : 'btn-primary'} hasil-btn" data-id="${+r.id_inspection}"><i class="ti ti-${terisi ? 'pencil' : 'test-pipe'} me-1"></i>${terisi ? 'Ubah Hasil' : 'Isi Hasil'}</button>` :
          `<button type="button" class="btn btn-sm btn-dark hasil-btn" data-id="${+r.id_inspection}" data-lihat="1"><i class="ti ti-eye me-1"></i>Lihat</button>`);
      const aksi = btnHasil + (kini ?
        ` <button type="button" class="btn btn-sm btn-warning edit-btn" data-id="${+r.id_inspection}" title="Ubah permintaan"><i class="ti ti-edit"></i></button>
          <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="${+r.id_inspection}" title="Hapus"><i class="ti ti-trash"></i></button>` : '');
      const hasil = terisi ?
        `<span class="hasil">${esc(r.hasil)}</span>${r.keterangan ? ` <span class="text-muted small">(${esc(r.keterangan)})</span>` : ''}` :
        '<span class="st st-belum">Belum ada hasil</span>';
      return `<tr>
        <td class="aksi">${aksi}</td>
        <td><div class="nama">${esc(r.inspection_name || '-')}</div><div class="nomor">${esc(r.inspection_number || '')}${kini ? '' : ' · kunjungan ' + esc(tgl(r.visit_date))}</div></td>
        <td class="text-nowrap">${esc(tgl(r.inspection_date))}</td>
        <td>${hasil}</td>
        <td class="catatan">${esc(r.inspection_note || '-')}</td>
      </tr>`;
    }

    function render() {
      const kini = data.filter(r => +r.is_current === 1);
      const q = String($('#cariRiwayat').val() || '').trim().toLowerCase();
      const lama = data.filter(r => +r.is_current !== 1);
      const lamaTampil = lama.filter(r => !q || `${r.inspection_name} ${r.inspection_note} ${r.hasil || ''}`.toLowerCase().includes(q));
      const belum = kini.filter(r => !(+r.hasil_terisi > 0)).length;
      $('#nKini').text(kini.length);
      $('#nRiwayat').text(lama.length);
      $('#ringkasKini').text(kini.length ? (belum ? `${belum} pemeriksaan belum ada hasil` : 'Semua hasil sudah terisi') : '');
      $('#tbKini').html(kini.length ? kini.map(r => baris(r, true)).join('') :
        '<tr><td colspan="5" class="kosong"><i class="ti ti-flask-off"></i>Belum ada permintaan pemeriksaan pada kunjungan ini.</td></tr>');
      $('#tbRiwayat').html(lamaTampil.length ? lamaTampil.map(r => baris(r, false)).join('') :
        `<tr><td colspan="5" class="kosong">${lama.length ? 'Tidak ada riwayat yang cocok.' : 'Belum ada riwayat lab di kunjungan lain.'}</td></tr>`);
    }
    $('#cariRiwayat').on('input', render);

    // ---------- form permintaan ----------
    const $nama = $('#inspection_name').select2({
      dropdownParent: $('#programModal'),
      width: '100%',
      tags: true,
      placeholder: 'Ketik atau pilih pemeriksaan',
      createTag: p => { const t = $.trim(p.term); return t ? { id: t, text: t, newOption: true } : null; },
      templateResult: d => { const $r = $('<span></span>').text(d.text); if (d.newOption) $r.append(' <em class="text-muted">(baru)</em>'); return $r; }
    }).on('change', () => $('#programForm .select2-selection').removeClass('border-danger'));

    function setNama(v) {
      if (v && !$nama.find('option').filter((_, o) => o.value === v).length) $nama.append(new Option(v, v));
      $nama.val(v || '').trigger('change');
    }
    function bukaForm(d) {
      const f = $('#programForm')[0];
      f.reset();
      $(f).find('.is-invalid').removeClass('is-invalid');
      $('#id_inspection').val(d ? d.id_inspection : '');
      setNama(d ? d.inspection_name : '');
      $('#inspection_date').val(d ? String(d.inspection_date || '').slice(0, 10) : hariIni());
      $('#inspection_source').val('Lab Klinik');
      $('#inspection_note').val(d ? (d.inspection_note || '') : '');
      $('#programModalTitle').text(d ? 'Ubah Permintaan Pemeriksaan' : 'Tambah Pemeriksaan');
      $('#programModal').modal('show');
    }
    $('#btnTambah').on('click', () => bukaForm(null));
    $(document).on('click', '.edit-btn', function() {
      const d = data.find(r => +r.id_inspection === +$(this).data('id'));
      if (d) bukaForm(d);
    });

    // catatan: hanya teks (angka langsung dibuang saat diketik)
    $('#inspection_note').on('input', function() {
      const bersih = this.value.replace(/[0-9]/g, '');
      if (bersih !== this.value) {
        const pos = this.selectionStart - (this.value.length - bersih.length);
        this.value = bersih;
        this.setSelectionRange(pos, pos);
        $(this).addClass('is-invalid');
        clearTimeout(this._t);
        this._t = setTimeout(() => $(this).removeClass('is-invalid'), 1200);
      } else $(this).removeClass('is-invalid');
    });
    $('#inspection_date').on('input', function() { $(this).removeClass('is-invalid'); });

    $('#programForm').on('submit', function(e) {
      e.preventDefault();
      const nama = String($nama.val() || '').trim();
      const tanggal = $('#inspection_date').val();
      const cat = $('#inspection_note').val().trim();
      let pesan = '';
      if (!nama) { pesan = 'Nama pemeriksaan wajib dipilih.'; $('#programForm .select2-selection').addClass('border-danger'); }
      else if (!tanggal || tanggal > hariIni()) { pesan = 'Tanggal wajib diisi dan tidak boleh di masa depan.'; $('#inspection_date').addClass('is-invalid'); }
      else if (!cat) pesan = 'Catatan wajib diisi.';
      else if (/[0-9]/.test(cat)) pesan = 'Catatan tidak boleh berisi angka.';
      else if (!/[A-Za-zÀ-ɏ]/.test(cat)) pesan = 'Catatan harus berupa teks.';
      if (pesan) {
        if (/Catatan/.test(pesan)) $('#inspection_note').addClass('is-invalid');
        Swal.fire('Lengkapi data', pesan, 'warning');
        return;
      }
      $('#inspection_source').val('Lab Klinik');
      const $b = $('#btnSimpan').prop('disabled', true);
      fetch(apiUrl, { method: 'POST', body: new FormData(this) })
        .then(r => r.json())
        .then(res => {
          if (res.status === 'success') {
            $('#programModal').modal('hide');
            Swal.fire({ icon: 'success', title: 'Tersimpan', text: res.message, timer: 1400, showConfirmButton: false });
            muat();
          } else {
            if (res.field === 'inspection_name') $('#programForm .select2-selection').addClass('border-danger');
            else if (res.field) $('#' + res.field).addClass('is-invalid');
            Swal.fire('Gagal', res.message || 'Gagal menyimpan.', 'error');
          }
        })
        .catch(() => Swal.fire('Gagal', 'Terjadi kesalahan pada server.', 'error'))
        .finally(() => $b.prop('disabled', false));
    });

    $(document).on('click', '.delete-btn', function() {
      const id = $(this).data('id');
      const d = data.find(r => +r.id_inspection === +id) || {};
      Swal.fire({
        title: 'Hapus pemeriksaan?',
        html: `<b>${esc(d.inspection_name)}</b>` + (+d.hasil_terisi > 0 ? '<div class="alert alert-warning py-2 mt-2 mb-0 small">Hasil lab yang sudah diisi ikut terhapus.</div>' : ''),
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
          .then(res => {
            if (res.status === 'success') {
              Swal.fire({ icon: 'success', title: 'Dihapus', text: res.message, timer: 1400, showConfirmButton: false });
              muat();
            } else Swal.fire('Gagal', res.message || 'Gagal menghapus.', 'error');
          })
          .catch(() => Swal.fire('Gagal', 'Terjadi kesalahan pada server.', 'error'));
      });
    });

    // ---------- hasil ----------
    let currentInspectionId = null;
    const angka = (v) => { const s = String(v ?? '').trim().replace(/\s/g, ''); if (!/^-?[\d.,]+$/.test(s)) return null; const n = parseFloat(/,\d{1,2}$/.test(s) ? s.replace(/\./g, '').replace(',', '.') : s.replace(/,/g, '')); return isNaN(n) ? null : n; };
    function flag($tr) {
      const v = angka($tr.find('.hasil-input').val());
      const min = angka($tr.data('min')), max = angka($tr.data('max'));
      let f = '';
      if (v !== null && min !== null && v < min) f = 'L';
      else if (v !== null && max !== null && max !== 0 && v > max) f = 'H';
      $tr.toggleClass('abn', !!f).find('.flag').remove();
      if (f) $tr.find('.hasil-input').after(`<span class="flag flag-${f} ms-1">${f}</span>`);
    }
    function progres() {
      const $i = $('#hasilBody .hasil-input');
      const n = $i.filter(function() { return this.value.trim() !== ''; }).length;
      $('#hasilProgres').text($i.length ? `${n} dari ${$i.length} parameter terisi` : '');
    }

    $(document).on('click', '.hasil-btn', function() {
      const id = $(this).data('id');
      const d = data.find(r => +r.id_inspection === +id) || {};
      const lihat = !!$(this).data('lihat');
      $('#hasilInfo').text(`${d.inspection_name || ''} · ${tgl(d.inspection_date)}`);

      if (isKhusus(d.inspection_name)) {
        $('#hasilModalTitle').text('Preview Hasil Laboratorium');
        $('#hasilWrap, #hasilFooter').hide();
        $('#previewContainer').show();
        $('#previewFrame').attr('src', `module/admin/print/formulir_lab_id?no=${encodeURIComponent(NO)}&rm=${encodeURIComponent(RM)}&id_inspection=${encodeURIComponent(id)}`);
        $('#hasilModal').modal('show');
        return;
      }

      $('#previewContainer').hide();
      $('#hasilWrap').show();
      $('#hasilFooter').toggle(!lihat);
      $('#hasilModalTitle').text(lihat ? 'Hasil Lab' : 'Input Hasil Lab');
      currentInspectionId = id;
      $('#hasilBody').html('<tr><td colspan="5" class="text-center text-muted py-4"><span class="spinner-border spinner-border-sm me-2"></span>Memuat parameter...</td></tr>');
      $('#hasilModal').modal('show');

      fetch(`controller/lab/getLabItem?kode=${encodeURIComponent(d.inspection_name || '')}&id_inspection=${encodeURIComponent(id)}`)
        .then(r => r.json())
        .then(res => {
          if (res.status !== 'success') throw new Error(res.message);
          const items = res.data || [];
          if (!items.length) {
            $('#hasilBody').html('<tr><td colspan="5" class="text-center text-muted py-4">Pemeriksaan ini belum punya parameter di master lab.</td></tr>');
            $('#hasilFooter').hide();
            return;
          }
          $('#hasilBody').html(items.map(it => `
            <tr data-min="${esc(it.minimum)}" data-max="${esc(it.maksimum)}">
              <td class="fw-semibold">${esc(it.assemen)}</td>
              <td><div class="d-flex align-items-center">
                <input type="text" class="form-control form-control-sm hasil-input" data-id="${+it.id}" maxlength="255" ${lihat ? 'readonly' : ''}>
              </div></td>
              <td>${esc(it.satuan || '-')}</td>
              <td class="text-nowrap">${esc(it.minimum ?? '-')} – ${esc(it.maksimum ?? '-')}</td>
              <td class="small">${esc(it.catatan || '-')}</td>
            </tr>`).join(''));
          $('#hasilBody tr').each(function(i) {
            $(this).find('.hasil-input').val(items[i].hasil ?? '');
            flag($(this));
          });
          progres();
          if (!lihat) $('#hasilBody .hasil-input').filter(function() { return !this.value; }).first().trigger('focus');
        })
        .catch(err => $('#hasilBody').html(`<tr><td colspan="5" class="text-center text-danger py-4">${esc(err.message || 'Gagal memuat parameter.')}</td></tr>`));
    });
    $('#hasilBody').on('input', '.hasil-input', function() { flag($(this).closest('tr')); progres(); });
    $('#hasilModal').on('hidden.bs.modal', () => $('#previewFrame').attr('src', 'about:blank'));

    $('#saveHasil').on('click', function() {
      const results = $('#hasilBody .hasil-input').map(function() { return { id_item: $(this).data('id'), hasil: $(this).val() }; }).get();
      if (!results.length) return;
      const $b = $(this).prop('disabled', true);
      fetch('controller/lab/saveResult.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id_inspection: currentInspectionId, data: results })
        })
        .then(r => r.json())
        .then(res => {
          if (res.status === 'success') {
            $('#hasilModal').modal('hide');
            Swal.fire({ icon: 'success', title: 'Hasil lab disimpan', timer: 1300, showConfirmButton: false });
            muat(); // kolom Hasil langsung terisi tanpa refresh halaman
          } else Swal.fire('Gagal', res.message || 'Gagal menyimpan hasil.', 'error');
        })
        .catch(() => Swal.fire('Gagal', 'Terjadi kesalahan pada server.', 'error'))
        .finally(() => $b.prop('disabled', false));
    });

    muat();
  })();
</script>
<?php endif ?>
</body>

</html>
