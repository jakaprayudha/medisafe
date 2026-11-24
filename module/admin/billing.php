<?php
/**
 * Tindakan & biaya jasa kunjungan (menu RME "Tindakan").
 * Perbaikan: saat Ubah harga terhapus (diisi dari kolom "harga" yang tidak ada) dan item ketikan sendiri hilang dari
 * pilihan select2 -> tersimpan kosong; master tarif dirangkai langsung ke SQL; $_GET['rme'] tanpa default; id
 * kunjungan dicetak mentah (XSS); isi tabel tanpa escape; URL simpan ganda "?"; modal di luar </body>; total tidak
 * ditampilkan; diskon bebas (negatif / melebihi subtotal); item tetap bisa diubah setelah lunas; kolom Aksi di kanan.
 */
$title = 'Tindakan';
require '../../controller/view.php';
$EMBED = !empty($_GET['embed']);   // ditampilkan di dalam View RME Baru
if ($EMBED) $_GET['rme'] = '';     // tanpa menu RME
$no = trim((string) ($_GET['no'] ?? ''));
$rm = trim((string) ($_GET['rm'] ?? ''));
$rme = (string) ($_GET['rme'] ?? '');
$idc = (int) ($_SESSION['id_customer'] ?? 0);
$h = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');

$st = $koneksi->prepare("SELECT tarif_name, tarif_amount, tarif_services FROM ms_tarif WHERE tarif_status = '1' AND id_customer = ? ORDER BY tarif_services, tarif_name");
$st->bind_param('i', $idc);
$st->execute();
$tarif = $st->get_result()->fetch_all(MYSQLI_ASSOC);
$st->close();
$grupTarif = [];
foreach ($tarif as $t) $grupTarif[trim((string) $t['tarif_services']) ?: 'Lainnya'][] = $t;
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; require __DIR__ . '/embed_mode.php'; ?>
  <style>
    .bl .kepala { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-bottom: 14px; }
    .bl .kepala .ic { width: 42px; height: 42px; border-radius: 12px; background: #fef3c7; color: #92400e; display: inline-flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
    .bl .kepala h5 { margin: 0; font-weight: 700; color: #0f172a; }
    .bl .kepala small { color: #475569; }
    .bl table.t { width: 100%; border-collapse: collapse; font-size: .87rem; }
    .bl table.t th { font-size: .7rem; text-transform: uppercase; color: #475569; font-weight: 700; background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 7px 8px; white-space: nowrap; }
    .bl table.t td { border-bottom: 1px solid #eef2f6; padding: 8px; vertical-align: top; color: #1f2937; }
    .bl table.t tfoot td { border-bottom: 0; padding: 4px 8px; font-weight: 600; }
    .bl table.t tfoot tr.grand td { font-size: 1.05rem; font-weight: 800; color: #0f172a; border-top: 2px solid #0f172a; padding-top: 8px; }
    .bl .aksi { white-space: nowrap; }
    .bl .aksi .btn { padding: 3px 9px; }
    .bl .nama { font-weight: 700; color: #0f172a; }
    .bl .kat { font-size: .7rem; font-weight: 700; border-radius: 6px; padding: 1px 7px; border: 1px solid; display: inline-block; margin-top: 2px; }
    .bl .kat-Tindakan { color: #1e3a8a; border-color: #93c5fd; background: #eff6ff; }
    .bl .kat-Konsultasi { color: #14532d; border-color: #86efac; background: #f0fdf4; }
    .bl .kat-Obat { color: #581c87; border-color: #d8b4fe; background: #faf5ff; }
    .bl .kat-Lainnya { color: #334155; border-color: #cbd5e1; background: #f8fafc; }
    .bl .catatan { font-size: .78rem; color: #475569; white-space: pre-line; margin-top: 2px; }
    .bl .num { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
    .bl .kosong { text-align: center; padding: 30px 10px; color: #475569; }
    .bl .kosong i { font-size: 2.2rem; color: #94a3b8; display: block; margin-bottom: 6px; }
    #programModal .form-label { font-weight: 600; margin-bottom: 3px; }
    #programModal .ringkas { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 8px 12px; font-size: .86rem; }
    #programModal .ringkas b { font-size: 1.05rem; color: #0f172a; }
  </style>
</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <?php if (!$EMBED) require 'sidebar.php'; ?>
    <div class="body-wrapper">
      <?php if (!$EMBED) require 'navbar.php'; ?>
      <div class="body-wrapper-inner">
        <div class="container-fluid bl">
          <?php
          if ($rme === 'a') include 'menu_rme.php';
          elseif ($rme === 'b') include 'menu_rmeb.php';
          elseif ($rme === 'c') include 'menu_rme_inap.php';
          ?>
          <?php if (!$EMBED) require 'card-pasien.php'; ?>

          <div class="card w-100">
            <div class="card-body p-3 p-md-4">
              <div class="kepala">
                <span class="ic"><i class="ti ti-stethoscope"></i></span>
                <div class="flex-grow-1">
                  <h5>Tindakan &amp; Biaya Jasa</h5>
                  <small>Tindakan, konsultasi, dan biaya lain kunjungan ini — ditagihkan di kasir bersama obat.</small>
                </div>
                <div class="d-flex flex-wrap gap-2">
                  <a class="btn btn-outline-dark" target="_blank" href="module/print/struk_billing?no=<?= $h(urlencode($no)) ?>&rm=<?= $h(urlencode($rm)) ?>"><i class="ti ti-printer me-1"></i>Cetak Struk</a>
                  <button type="button" class="btn btn-primary" id="btnTambah"><i class="ti ti-plus me-1"></i>Tambah Item</button>
                </div>
              </div>
              <div class="alert alert-success d-none" id="alertLunas"><i class="ti ti-lock me-1"></i>Tagihan kunjungan ini sudah <b>lunas</b>. Item dikunci — batalkan pembayaran di kasir bila perlu mengubah.</div>
              <div class="alert alert-warning d-none" id="alertBatal"><i class="ti ti-ban me-1"></i>Kunjungan ini sudah <b>dibatalkan</b>.</div>

              <div class="table-responsive">
                <table class="t">
                  <thead>
                    <tr>
                      <th style="width:1%">Aksi</th>
                      <th>Item</th>
                      <th class="num">Qty</th>
                      <th class="num">Harga</th>
                      <th class="num">Diskon</th>
                      <th class="num">Total</th>
                    </tr>
                  </thead>
                  <tbody id="tbItem"><tr><td colspan="6" class="kosong"><span class="spinner-border spinner-border-sm me-2"></span>Memuat...</td></tr></tbody>
                  <tfoot id="tfTotal" class="d-none">
                    <tr><td colspan="5" class="num text-muted">Subtotal</td><td class="num" id="fSub"></td></tr>
                    <tr><td colspan="5" class="num text-muted">Diskon</td><td class="num" id="fDisk"></td></tr>
                    <tr class="grand"><td colspan="5" class="num">Total jasa</td><td class="num" id="fTotal"></td></tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="programModal" tabindex="-1" aria-labelledby="programModalTitle" aria-hidden="true">
    <div class="modal-dialog">
      <form id="programForm" class="modal-content" novalidate>
        <div class="modal-header">
          <h5 class="modal-title" id="programModalTitle">Tambah Item</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_billing" id="id_billing">
          <input type="hidden" name="id_visit" value="<?= $h($no) ?>">
          <div class="mb-3">
            <label for="billing_item" class="form-label">Nama item <span class="text-danger">*</span></label>
            <select name="billing_item" id="billing_item" class="form-select" style="width:100%">
              <option value=""></option>
              <?php foreach ($grupTarif as $g => $items): ?>
                <optgroup label="<?= $h($g) ?>">
                  <?php foreach ($items as $t): ?>
                    <option value="<?= $h($t['tarif_name']) ?>" data-harga="<?= (int) $t['tarif_amount'] ?>"><?= $h($t['tarif_name']) ?> — Rp <?= number_format((int) $t['tarif_amount'], 0, ',', '.') ?></option>
                  <?php endforeach ?>
                </optgroup>
              <?php endforeach ?>
            </select>
            <div class="form-text">Pilih dari master tarif (harga terisi otomatis), atau ketik nama item lain.</div>
          </div>
          <div class="row g-3">
            <div class="col-6">
              <label class="form-label" for="harga">Harga (Rp) <span class="text-danger">*</span></label>
              <input type="number" id="harga" name="billing_price" class="form-control" min="0" step="1">
            </div>
            <div class="col-6">
              <label class="form-label" for="qty">Qty <span class="text-danger">*</span></label>
              <input type="number" id="qty" name="billing_qty" class="form-control" min="1" step="1" value="1">
            </div>
            <div class="col-6">
              <label class="form-label" for="billing_discount">Diskon (Rp)</label>
              <input type="number" id="billing_discount" name="billing_discount" class="form-control" min="0" step="1" value="0">
            </div>
            <div class="col-6">
              <label class="form-label" for="billing_category">Kategori <span class="text-danger">*</span></label>
              <select name="billing_category" id="billing_category" class="form-select">
                <option value="Tindakan">Tindakan</option>
                <option value="Konsultasi">Konsultasi</option>
                <option value="Obat/BMHP/Alkes">Obat/BMHP/Alkes</option>
                <option value="Lainnya">Lainnya</option>
              </select>
            </div>
            <div class="col-12">
              <div class="ringkas d-flex justify-content-between align-items-center"><span id="rumus">Rp 0 × 1 − Rp 0</span><b id="totalItem">Rp 0</b></div>
            </div>
            <div class="col-12">
              <label class="form-label" for="billing_notes">Catatan</label>
              <textarea name="billing_notes" id="billing_notes" class="form-control" rows="2" maxlength="2000" placeholder="Opsional"></textarea>
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
      const NO = <?= json_encode($no) ?>;
      const apiUrl = 'controller/visit/billingController?no=' + encodeURIComponent(NO);
      const esc = (v) => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const rp = (n) => 'Rp ' + Math.round(+n || 0).toLocaleString('id-ID');
      let data = [];
      let lunas = false;

      function muat() {
        return fetch(apiUrl, { cache: 'no-store' })
          .then(r => r.json())
          .then(res => {
            if (res.status !== 'success') throw new Error(res.message);
            data = res.data || [];
            lunas = !!(res.kunjungan && res.kunjungan.lunas);
            const batal = !!(res.kunjungan && res.kunjungan.batal);
            $('#alertLunas').toggleClass('d-none', !lunas);
            $('#alertBatal').toggleClass('d-none', !batal);
            $('#btnTambah').prop('disabled', lunas || batal);
            render(res.total || {});
          })
          .catch(err => $('#tbItem').html(`<tr><td colspan="6" class="kosong text-danger"><i class="ti ti-alert-triangle"></i>${esc(err.message || 'Gagal memuat data.')}</td></tr>`));
      }

      function render(total) {
        if (!data.length) {
          $('#tbItem').html('<tr><td colspan="6" class="kosong"><i class="ti ti-clipboard-off"></i>Belum ada tindakan / biaya jasa pada kunjungan ini.</td></tr>');
          $('#tfTotal').addClass('d-none');
          return;
        }
        $('#tbItem').html(data.map(r => {
          const sub = (+r.billing_price || 0) * (+r.billing_qty || 0);
          const tot = Math.max(0, sub - (+r.billing_discount || 0));
          const kat = String(r.billing_category || 'Lainnya');
          const aksi = lunas ? '<span class="text-muted small"><i class="ti ti-lock"></i></span>' :
            `<button type="button" class="btn btn-sm btn-warning edit-btn" data-id="${+r.id_billing}" title="Ubah"><i class="ti ti-edit"></i></button>
             <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="${+r.id_billing}" title="Hapus"><i class="ti ti-trash"></i></button>`;
          return `<tr>
            <td class="aksi">${aksi}</td>
            <td><div class="nama">${esc(r.billing_item || '-')}</div><span class="kat kat-${esc(kat.split('/')[0])}">${esc(kat)}</span>
              ${r.billing_notes ? `<div class="catatan">${esc(r.billing_notes)}</div>` : ''}</td>
            <td class="num">${esc(r.billing_qty)}</td>
            <td class="num">${rp(r.billing_price)}</td>
            <td class="num">${+r.billing_discount ? '− ' + rp(r.billing_discount) : '-'}</td>
            <td class="num fw-bold">${rp(tot)}</td>
          </tr>`;
        }).join(''));
        $('#fSub').text(rp(total.subtotal));
        $('#fDisk').text(+total.diskon ? '− ' + rp(total.diskon) : '-');
        $('#fTotal').text(rp(total.total));
        $('#tfTotal').removeClass('d-none');
      }

      // ---------- form ----------
      const $item = $('#billing_item').select2({
        dropdownParent: $('#programModal'),
        width: '100%',
        tags: true,
        placeholder: 'Ketik atau pilih item',
        createTag: p => { const t = $.trim(p.term); return t ? { id: t, text: t, newOption: true } : null; },
        templateResult: d => { const $r = $('<span></span>').text(d.text); if (d.newOption) $r.append(' <em class="text-muted">(baru)</em>'); return $r; }
      });
      let isiOtomatis = true;   // harga dari master hanya diisi saat petugas memilih item, bukan saat form Ubah dibuka
      $item.on('change', function() {
        $(this).next('.select2').find('.select2-selection').removeClass('border-danger');
        if (!isiOtomatis) return;
        const harga = $(this).find(':selected').data('harga');
        if (harga !== undefined) $('#harga').val(harga);
        hitung();
      });

      function hitung() {
        const h = +$('#harga').val() || 0, q = +$('#qty').val() || 0, d = +$('#billing_discount').val() || 0;
        $('#rumus').text(`${rp(h)} × ${q}${d ? ' − ' + rp(d) : ''}`);
        $('#totalItem').text(rp(Math.max(0, h * q - d))).toggleClass('text-danger', d > h * q);
      }
      $('#harga, #qty, #billing_discount').on('input', function() { $(this).removeClass('is-invalid'); hitung(); });

      function setItem(v) {
        if (v && !$item.find('option').filter((_, o) => o.value === v).length) $item.append(new Option(v, v));
        isiOtomatis = false;
        $item.val(v || '').trigger('change');
        isiOtomatis = true;
      }
      function bukaForm(d) {
        const f = $('#programForm')[0];
        f.reset();
        $(f).find('.is-invalid').removeClass('is-invalid');
        $('#id_billing').val(d ? d.id_billing : '');
        setItem(d ? d.billing_item : '');
        $('#harga').val(d ? d.billing_price : '');
        $('#qty').val(d ? d.billing_qty : 1);
        $('#billing_discount').val(d ? (+d.billing_discount || 0) : 0);
        $('#billing_category').val(d && d.billing_category ? d.billing_category : 'Tindakan');
        $('#billing_notes').val(d ? (d.billing_notes || '') : '');
        $('#programModalTitle').text(d ? `Ubah Item ${d.billing_number || ''}` : 'Tambah Item');
        hitung();
        $('#programModal').modal('show');
      }
      $('#btnTambah').on('click', () => bukaForm(null));
      $(document).on('click', '.edit-btn', function() {
        const d = data.find(r => +r.id_billing === +$(this).data('id'));
        if (d) bukaForm(d);
      });

      function tandai(f) {
        if (f === 'billing_item') $item.next('.select2').find('.select2-selection').addClass('border-danger');
        else $(f === 'billing_price' ? '#harga' : f === 'billing_qty' ? '#qty' : '#' + f).addClass('is-invalid').trigger('focus');
      }

      $('#programForm').on('submit', function(e) {
        e.preventDefault();
        const h = $('#harga').val(), q = +$('#qty').val(), d = +$('#billing_discount').val() || 0;
        let salah = null;
        if (!String($item.val() || '').trim()) salah = ['billing_item', 'Nama item wajib diisi.'];
        else if (h === '' || +h < 0) salah = ['billing_price', 'Harga wajib diisi (0 atau lebih).'];
        else if (!(q >= 1)) salah = ['billing_qty', 'Qty minimal 1.'];
        else if (d < 0 || d > +h * q) salah = ['billing_discount', 'Diskon tidak boleh negatif atau melebihi subtotal.'];
        if (salah) { tandai(salah[0]); Swal.fire('Periksa data', salah[1], 'warning'); return; }

        const id = $('#id_billing').val();
        const $b = $('#btnSimpan').prop('disabled', true);
        fetch(apiUrl + (id ? '&id=' + encodeURIComponent(id) : ''), {
            method: id ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams(new FormData(this))
          })
          .then(r => r.json())
          .then(res => {
            if (res.status === 'success') {
              $('#programModal').modal('hide');
              Swal.fire({ icon: 'success', title: 'Tersimpan', text: res.message, timer: 1300, showConfirmButton: false });
              muat();
            } else {
              if (res.field) tandai(res.field);
              Swal.fire('Gagal', res.message || 'Gagal menyimpan.', 'error');
            }
          })
          .catch(() => Swal.fire('Gagal', 'Terjadi kesalahan pada server.', 'error'))
          .finally(() => $b.prop('disabled', false));
      });

      $(document).on('click', '.delete-btn', function() {
        const id = $(this).data('id');
        const d = data.find(r => +r.id_billing === +id) || {};
        Swal.fire({
          title: 'Hapus item?',
          html: `<b>${esc(d.billing_item)}</b> — ${rp(Math.max(0, d.billing_price * d.billing_qty - (d.billing_discount || 0)))}`,
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
              if (res.status === 'success') Swal.fire({ icon: 'success', title: 'Dihapus', timer: 1200, showConfirmButton: false });
              else Swal.fire('Gagal', res.message || 'Gagal menghapus.', 'error');
              muat();
            })
            .catch(() => Swal.fire('Gagal', 'Terjadi kesalahan pada server.', 'error'));
        });
      });

      muat();
    })();
  </script>
</body>

</html>
