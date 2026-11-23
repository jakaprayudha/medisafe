<?php
/**
 * Kasir: rincian tagihan & pembayaran satu kunjungan.
 * Data : controller/visit/kasirDetail (kunjungan + rincian + total), controller/visit/tindakan (tambah/ubah/hapus jasa),
 *        controller/visit/bayar (simpan pembayaran; total dihitung ulang di server).
 */
$title = 'Kasir';
require '../../controller/view.php';
$no = (string) ($_GET['no'] ?? '');
$rm = (string) ($_GET['rm'] ?? '');
$idc = (string) ($_SESSION['id_customer'] ?? '');
// dulu: semua tarif dari semua faskes ditampilkan -> memilih tarif faskes lain tersimpan Rp0
$st = $koneksi->prepare("SELECT tarif_name, MAX(tarif_amount) tarif_amount, MAX(tarif_services) tarif_services FROM ms_tarif
   WHERE id_customer = ? AND tarif_status = 1 GROUP BY tarif_name ORDER BY tarif_name");
$st->bind_param('s', $idc);
$st->execute();
$tarif = $st->get_result()->fetch_all(MYSQLI_ASSOC);
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
    @keyframes kd-spin { to { transform: rotate(360deg); } }
    .icon-spin { display: inline-block; animation: kd-spin 1s linear infinite; }
    .kd-avatar { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 22px; font-weight: 700; color: #fff; background: linear-gradient(135deg, #4f46e5, #6366f1); flex-shrink: 0; }
    .kd-nama { font-size: 1.25rem; font-weight: 700; color: #0f172a; line-height: 1.2; }
    .kd-info { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px; }
    .kd-info span { font-size: .78rem; border: 1px solid #e5eaef; border-radius: 999px; padding: 2px 10px; color: #334155; background: #fff; }
    .kd-info i { color: #64748b; margin-right: 3px; }
    .kd-card-title { font-size: 1rem; font-weight: 700; color: #111827; margin: 0; display: flex; align-items: center; gap: 8px; }
    .kd-card-title i { color: #5d87ff; }
    .kd-tbl { font-size: .86rem; margin: 0; }
    .kd-tbl thead th { font-size: .74rem; text-transform: uppercase; letter-spacing: .03em; color: #475569; font-weight: 600; background: #f8fafc; white-space: nowrap; }
    .kd-tbl td { vertical-align: middle; }
    .kd-tbl .num { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
    .kd-tbl tfoot td { font-weight: 700; background: #f8fafc; }
    .kd-sub { font-size: .76rem; color: #475569; }
    .kd-tiket { background: #f1f5f9; font-size: .78rem; font-weight: 700; color: #334155; }
    .kd-b { font-size: .78rem; font-weight: 600; padding: 3px 8px; border-radius: 6px; border: 1px solid; background: #fff; }
    .kd-b-ubah { border-color: #334155; color: #1f2937; }
    .kd-b-ubah:hover { background: #1e293b; color: #fff; }
    .kd-b-hapus { border-color: #dc2626; color: #b91c1c; }
    .kd-b-hapus:hover { background: #dc2626; color: #fff; }
    .kd-kosong { padding: 24px 12px; text-align: center; color: #64748b; font-size: .86rem; }
    .kd-ring { position: sticky; top: 90px; }
    .kd-ring .baris { display: flex; justify-content: space-between; font-size: .9rem; padding: 6px 0; color: #334155; }
    .kd-ring .baris b { font-variant-numeric: tabular-nums; color: #111827; }
    .kd-total { border-top: 1px dashed #cbd5e1; margin-top: 6px; padding-top: 10px; display: flex; justify-content: space-between; align-items: baseline; }
    .kd-total .rp { font-size: 1.6rem; font-weight: 800; color: #b91c1c; font-variant-numeric: tabular-nums; }
    .kd-total.lunas .rp { color: #15803d; }
    .kd-metode { display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px; }
    .kd-metode .btn { font-size: .82rem; font-weight: 600; background: #fff; border: 1px solid #cbd5e1; color: #1f2937; padding: 8px 4px; }
    .kd-metode .btn.active { background: #1e293b; border-color: #1e293b; color: #fff; }
    .kd-cepat .btn { font-size: .78rem; font-weight: 600; background: #fff; border: 1px solid #cbd5e1; color: #1f2937; }
    .kd-cepat .btn:hover { background: #f1f5f9; }
    #uangDiterima { font-size: 1.4rem; font-weight: 700; text-align: right; }
    .kd-kembali { font-size: 1.3rem; font-weight: 800; font-variant-numeric: tabular-nums; }
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
          <div class="mb-2"><a href="module/admin/kasir" class="btn btn-sm btn-outline-dark"><i class="ti ti-arrow-left me-1"></i>Kembali ke daftar kasir</a></div>

          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-3">
              <div class="d-flex flex-wrap align-items-center gap-3">
                <div class="kd-avatar" id="pAvatar">-</div>
                <div class="flex-grow-1" style="min-width:0">
                  <div class="d-flex flex-wrap align-items-center gap-2">
                    <div class="kd-nama" id="pNama">Memuat…</div>
                    <span id="pStatus"></span>
                  </div>
                  <div class="kd-info" id="pInfo"></div>
                </div>
              </div>
              <div id="pError" class="alert alert-danger mt-3 mb-0 d-none"></div>
            </div>
          </div>

          <div class="row g-3">
            <div class="col-xl-8">
              <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-3 p-md-4">
                  <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="kd-card-title"><i class="ti ti-stethoscope"></i>Tindakan &amp; administrasi</h6>
                    <button type="button" class="btn btn-sm btn-primary" id="btnTambah" disabled><i class="ti ti-plus me-1"></i>Tambah tindakan</button>
                  </div>
                  <div class="table-responsive">
                    <table class="table kd-tbl">
                      <thead><tr><th style="width:1%">Aksi</th><th>Item</th><th class="num">Qty</th><th class="num">Harga</th><th class="num">Diskon</th><th class="num">Subtotal</th></tr></thead>
                      <tbody id="bodyJasa"><tr><td colspan="6" class="kd-kosong">Memuat…</td></tr></tbody>
                      <tfoot><tr><td colspan="5" class="text-end">Total tindakan</td><td class="num" id="fJasa">-</td></tr></tfoot>
                    </table>
                  </div>
                </div>
              </div>

              <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-3 p-md-4">
                  <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="kd-card-title"><i class="ti ti-pill"></i>Obat &amp; BMHP</h6>
                    <span class="kd-sub">harga dari farmasi</span>
                  </div>
                  <div id="warnTiket" class="alert alert-warning py-2 small d-none"></div>
                  <div class="table-responsive">
                    <table class="table kd-tbl">
                      <thead><tr><th>Obat</th><th class="num">Qty</th><th class="num">Harga</th><th class="num">Subtotal</th></tr></thead>
                      <tbody id="bodyObat"><tr><td colspan="4" class="kd-kosong">Memuat…</td></tr></tbody>
                      <tfoot><tr><td colspan="3" class="text-end">Total obat</td><td class="num" id="fObat">-</td></tr></tfoot>
                    </table>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-xl-4">
              <div class="card border-0 shadow-sm kd-ring">
                <div class="card-body p-3 p-md-4">
                  <h6 class="kd-card-title mb-2"><i class="ti ti-receipt"></i>Ringkasan tagihan</h6>
                  <div class="baris"><span>Tindakan &amp; administrasi</span><b id="rJasa">-</b></div>
                  <div class="baris"><span>Obat &amp; BMHP</span><b id="rObat">-</b></div>
                  <div class="kd-total" id="rTotalWrap"><span class="fw-semibold">Total</span><span class="rp" id="rTotal">-</span></div>
                  <div id="rLunas" class="mt-3 d-none"></div>
                  <div class="d-grid gap-2 mt-3">
                    <button type="button" class="btn btn-primary btn-lg" id="btnBayar" disabled><i class="ti ti-cash me-1"></i>Bayar</button>
                    <a class="btn btn-outline-dark" id="btnInvoice" target="_blank" href="module/print/struk_billing?no=<?= urlencode($no) ?>&rm=<?= urlencode($rm) ?>"><i class="ti ti-printer me-1"></i>Cetak invoice</a>
                    <button type="button" class="btn btn-sm btn-light" id="btnMuat"><i class="ti ti-refresh me-1"></i>Muat ulang</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="mTambah" tabindex="-1">
    <div class="modal-dialog">
      <form class="modal-content" id="formTambah" novalidate>
        <div class="modal-header"><h5 class="modal-title">Tambah tindakan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <input type="hidden" name="nomor_visit" value="<?= $h($no) ?>">
          <input type="hidden" name="nomor_rm" value="<?= $h($rm) ?>">
          <div class="mb-3">
            <label for="tItem" class="form-label required">Nama tindakan</label>
            <select name="item" id="tItem" class="form-select" required>
              <option value=""></option>
              <?php foreach ($tarif as $t): ?>
                <option value="<?= $h($t['tarif_name']) ?>" data-harga="<?= (int) $t['tarif_amount'] ?>"><?= $h($t['tarif_name']) ?> — Rp<?= number_format((int) $t['tarif_amount'], 0, ',', '.') ?></option>
              <?php endforeach ?>
            </select>
            <?php if (!$tarif): ?><div class="form-text text-danger">Belum ada tarif aktif di master tarif faskes ini.</div><?php endif ?>
          </div>
          <div class="mb-3">
            <label for="tKategori" class="form-label">Kategori</label>
            <select name="kategori" id="tKategori" class="form-select">
              <option value="Tindakan">Tindakan</option>
              <option value="Konsultasi">Konsultasi</option>
              <option value="Obat/BMHP/Alkes">Obat/BMHP/Alkes</option>
              <option value="Lainnya">Lainnya</option>
            </select>
          </div>
          <div class="row g-2">
            <div class="col-4 mb-3"><label for="tQty" class="form-label required">Jumlah</label><input type="number" min="1" step="1" value="1" name="qty" id="tQty" class="form-control"></div>
            <div class="col-8 mb-3"><label for="tDiskon" class="form-label">Diskon (Rp)</label><input type="number" min="0" step="1" name="diskon" id="tDiskon" class="form-control" placeholder="0"></div>
          </div>
          <div class="mb-2"><label for="tCatatan" class="form-label">Catatan</label><textarea name="catatan" id="tCatatan" class="form-control" rows="2" maxlength="2000"></textarea></div>
          <div class="kd-sub">Subtotal: <b id="tSubtotal">Rp0</b></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
      </form>
    </div>
  </div>

  <div class="modal fade" id="mUbah" tabindex="-1">
    <div class="modal-dialog">
      <form class="modal-content" id="formUbah" novalidate>
        <div class="modal-header"><h5 class="modal-title">Ubah tindakan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <input type="hidden" name="id" id="uId">
          <div class="fw-semibold mb-3" id="uNama"></div>
          <div class="row g-2">
            <div class="col-5 mb-3"><label for="uHarga" class="form-label">Harga (Rp)</label><input type="number" min="0" step="1" name="harga" id="uHarga" class="form-control"></div>
            <div class="col-3 mb-3"><label for="uQty" class="form-label">Jumlah</label><input type="number" min="1" step="1" name="qty" id="uQty" class="form-control"></div>
            <div class="col-4 mb-3"><label for="uDiskon" class="form-label">Diskon (Rp)</label><input type="number" min="0" step="1" name="diskon" id="uDiskon" class="form-control"></div>
          </div>
          <div class="mb-2"><label for="uCatatan" class="form-label">Catatan</label><textarea name="catatan" id="uCatatan" class="form-control" rows="2" maxlength="2000"></textarea></div>
          <div class="kd-sub">Subtotal: <b id="uSubtotal">Rp0</b></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
      </form>
    </div>
  </div>

  <div class="modal fade" id="mBayar" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Pembayaran</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div class="text-center p-3 rounded-3 mb-3" style="background:#f0fdf4;border:1px solid #bbf7d0">
            <div class="kd-sub">Total tagihan</div>
            <div class="fw-bold" style="font-size:1.9rem;color:#166534" id="bTotal">Rp0</div>
          </div>
          <label class="form-label">Metode bayar</label>
          <div class="kd-metode mb-3" id="bMetode">
            <button type="button" class="btn active" data-m="Tunai">Tunai</button>
            <button type="button" class="btn" data-m="Transfer">Transfer</button>
            <button type="button" class="btn" data-m="QRIS">QRIS</button>
            <button type="button" class="btn" data-m="Lainnya">Lainnya</button>
          </div>
          <div id="bTunai">
            <label for="uangDiterima" class="form-label">Uang diterima</label>
            <input type="text" inputmode="numeric" id="uangDiterima" class="form-control mb-2" placeholder="0" autocomplete="off">
            <div class="d-flex flex-wrap gap-1 kd-cepat mb-3" id="bCepat"></div>
            <div class="d-flex justify-content-between align-items-center p-2 rounded-2" style="background:#f8fafc">
              <span class="kd-sub">Kembalian</span><span class="kd-kembali" id="bKembali">Rp0</span>
            </div>
            <div class="text-danger small mt-2 d-none" id="bKurang"><i class="ti ti-alert-triangle me-1"></i>Uang diterima kurang dari total tagihan.</div>
          </div>
          <div id="bNonTunai" class="kd-sub d-none">Pembayaran non-tunai dicatat sebesar total tagihan.</div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Batal</button><button type="button" class="btn btn-primary" id="bSimpan"><i class="ti ti-check me-1"></i>Simpan pembayaran</button></div>
      </div>
    </div>
  </div>

  <?php require 'library.php'; ?>
  <script>
    (function() {
      const NO = <?= json_encode($no) ?>;
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);
      const rp = n => 'Rp' + Number(n || 0).toLocaleString('id-ID');
      const angka = v => parseInt(String(v ?? '').replace(/\D/g, ''), 10) || 0;
      const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      const tglID = d => { if (!d) return '-'; const [y, m, h] = String(d).slice(0, 10).split('-'); return `${+h} ${BULAN[+m - 1]} ${y}`; };
      const STATUS_TIKET = { 0: ['Draf dokter', 'bg-secondary-subtle text-dark'], 1: ['Menunggu farmasi', 'bg-warning-subtle'], 2: ['Disiapkan', 'bg-info-subtle text-dark'], 3: ['Selesai', 'bg-success-subtle text-success'] };
      async function ambilJson(url, opt) {
        const r = await fetch(url, Object.assign({ credentials: 'same-origin' }, opt || {}));
        let j = null;
        try { j = await r.json(); } catch (e) { throw new Error('Respon server tidak valid (' + r.status + ').'); }
        if (!r.ok || j.status !== 'success') { const e = new Error(j.message || ('Gagal (' + r.status + ').')); e.data = j; throw e; }
        return j;
      }
      const gagal = e => Swal.fire('Gagal', e.message || String(e), 'error');
      const sukses = t => Swal.fire({ icon: 'success', title: t, timer: 1300, showConfirmButton: false });

      let D = null;
      const lunas = () => !!(D && D.kunjungan.lunas);

      async function muat() {
        $('#btnMuat i').addClass('icon-spin');
        try {
          D = await ambilJson('controller/visit/kasirDetail?no=' + encodeURIComponent(NO));
          gambar();
        } catch (e) {
          $('#pNama').text('Gagal memuat');
          $('#pError').removeClass('d-none').text(e.message);
          $('#bodyJasa, #bodyObat').html(`<tr><td colspan="6" class="kd-kosong">-</td></tr>`);
        } finally { $('#btnMuat i').removeClass('icon-spin'); }
      }

      function gambar() {
        const k = D.kunjungan, t = D.tagihan;
        $('#pAvatar').text((k.patient_name || '-').trim().charAt(0).toUpperCase());
        $('#pNama').text(k.patient_name || '(tanpa nama)');
        $('#pStatus').html(k.lunas ? '<span class="badge bg-success"><i class="ti ti-circle-check me-1"></i>Lunas</span>' : '<span class="badge bg-danger">Belum bayar</span>');
        let umur = '';
        if (k.patient_datebirth) { const b = new Date(String(k.patient_datebirth).slice(0, 10) + 'T00:00:00'); if (!isNaN(b)) { const n = new Date(); umur = n.getFullYear() - b.getFullYear() - ((n.getMonth() < b.getMonth() || (n.getMonth() === b.getMonth() && n.getDate() < b.getDate())) ? 1 : 0); } }
        $('#pInfo').html([
          ['id-badge-2', 'RM ' + (k.nomor_rm || '-')],
          ['user', [k.patient_gender, umur !== '' ? umur + ' th' : ''].filter(Boolean).join(', ') || '-'],
          ['wallet', k.provider_name || '-'],
          ['stethoscope', k.id_doctor || '-'],
          ['building-hospital', k.id_poli || '-'],
          ['calendar', tglID(k.visit_date) + (k.visit_time ? ' ' + String(k.visit_time).slice(0, 5) : '')],
        ].map(([i, v]) => `<span><i class="ti ti-${i}"></i>${esc(v)}</span>`).join('') + (k.status_rawatinap == 1 ? '<span style="background:#fef3c7;border-color:#fcd34d">Rawat inap</span>' : ''));

        // tindakan
        $('#bodyJasa').html(t.jasa.length ? t.jasa.map(b => `<tr>
            <td><div class="d-flex gap-1">${k.lunas ? '<span class="kd-sub"><i class="ti ti-lock"></i></span>' :
              `<button type="button" class="kd-b kd-b-ubah b-ubah" data-id="${b.id_billing}" title="Ubah"><i class="ti ti-edit"></i></button><button type="button" class="kd-b kd-b-hapus b-hapus" data-id="${b.id_billing}" title="Hapus"><i class="ti ti-trash"></i></button>`}</div></td>
            <td><div class="fw-semibold">${esc(b.billing_item)}</div><div class="kd-sub">${esc(b.billing_category || '')}${b.billing_notes ? ' · ' + esc(b.billing_notes) : ''}</div></td>
            <td class="num">${b.billing_qty}</td><td class="num">${rp(b.billing_price)}</td>
            <td class="num">${b.billing_discount ? '−' + rp(b.billing_discount) : '-'}</td><td class="num fw-semibold">${rp(b.subtotal)}</td></tr>`).join('')
          : `<tr><td colspan="6" class="kd-kosong">Belum ada tindakan.${k.lunas ? '' : ' Klik <b>Tambah tindakan</b> bila ada biaya jasa.'}</td></tr>`);
        $('#fJasa, #rJasa').text(rp(t.total_jasa));

        // obat, dikelompokkan per tiket resep
        let html = '', tiketLalu = null, ke = 0;
        t.obat.forEach(o => {
          if (o.id_tiket !== tiketLalu) {
            tiketLalu = o.id_tiket; ke++;
            const [l, c] = STATUS_TIKET[o.status_tiket] || ['-', 'bg-light text-dark'];
            const jamTiket = o.tiket_dibuat ? ' · ' + esc(String(o.tiket_dibuat).slice(11, 16)) : '';
            html += `<tr class="kd-tiket"><td colspan="4">${esc(o.tiket || 'Resep ' + ke)}${jamTiket} <span class="badge ${c} ms-1">${l}</span></td></tr>`;
          }
          html += `<tr><td><div>${esc(o.nama)}</div>${o.racikan ? `<div class="kd-sub">Racikan: ${esc(o.racikan)}</div>` : ''}</td>
            <td class="num">${o.qty}${o.satuan ? ' <span class="kd-sub">' + esc(o.satuan) + '</span>' : ''}</td><td class="num">${rp(o.harga)}</td><td class="num fw-semibold">${rp(o.subtotal)}</td></tr>`;
        });
        $('#bodyObat').html(html || '<tr><td colspan="4" class="kd-kosong">Tidak ada resep untuk kunjungan ini.</td></tr>');
        $('#fObat, #rObat').text(rp(t.total_obat));
        $('#warnTiket').toggleClass('d-none', !t.tiket_belum_selesai || k.lunas)
          .html(`<i class="ti ti-alert-triangle me-1"></i>${t.tiket_belum_selesai} resep belum selesai di farmasi. Harga obat masih bisa berubah — pastikan farmasi sudah menyelesaikan resep sebelum menerima pembayaran.`);

        $('#rTotal').text(rp(k.lunas ? k.amount_results : t.total));
        $('#rTotalWrap').toggleClass('lunas', k.lunas);
        if (k.lunas) {
          const beda = k.amount_results !== t.total ? `<div class="text-danger small mt-1"><i class="ti ti-alert-triangle me-1"></i>Rincian sekarang ${rp(t.total)}, berbeda dari yang dibayar.</div>` : '';
          $('#rLunas').removeClass('d-none').html(`<div class="p-2 rounded-2" style="background:#f0fdf4;border:1px solid #bbf7d0">
            <div class="d-flex justify-content-between small"><span>Metode</span><b>${esc(k.metode_bayar === 'AUTO' ? 'Tanpa tagihan' : (k.metode_bayar || '-'))}</b></div>
            <div class="d-flex justify-content-between small"><span>No. transaksi</span><b>${esc(k.nomor_transaksi || '-')}</b></div>
            <div class="d-flex justify-content-between small"><span>Diterima</span><b>${rp(k.amount_payment)}</b></div>
            <div class="d-flex justify-content-between small"><span>Kembalian</span><b>${rp(k.amount_changes)}</b></div></div>${beda}`);
        } else $('#rLunas').addClass('d-none');
        $('#btnBayar').toggleClass('d-none', k.lunas).prop('disabled', false)
          .html(t.total > 0 ? '<i class="ti ti-cash me-1"></i>Bayar ' + rp(t.total) : '<i class="ti ti-check me-1"></i>Tandai lunas (tanpa tagihan)');
        $('#btnTambah').prop('disabled', k.lunas).toggleClass('d-none', k.lunas);
      }

      // ---------- tambah tindakan ----------
      const $item = $('#tItem');
      $item.select2({ dropdownParent: $('#mTambah'), width: '100%', placeholder: 'Cari tindakan…' });
      const subTambah = () => $('#tSubtotal').text(rp(Math.max(0, (Number($item.find(':selected').data('harga')) || 0) * (angka($('#tQty').val()) || 1) - angka($('#tDiskon').val()))));
      $item.on('change', subTambah); $('#tQty, #tDiskon').on('input', subTambah);
      $('#btnTambah').on('click', () => { $('#formTambah')[0].reset(); $item.val('').trigger('change'); $('#mTambah').modal('show'); });
      $('#formTambah').on('submit', async function(e) {
        e.preventDefault();
        if (!$item.val()) return Swal.fire('Lengkapi data', 'Pilih tindakan terlebih dahulu.', 'warning');
        const btn = $(this).find('[type=submit]').prop('disabled', true);
        try {
          const j = await ambilJson('controller/visit/tindakan', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: new URLSearchParams(new FormData(this)) });
          $('#mTambah').modal('hide'); sukses(j.message); await muat();
        } catch (er) { gagal(er); } finally { btn.prop('disabled', false); }
      });

      // ---------- ubah / hapus ----------
      const cariJasa = id => D.tagihan.jasa.find(b => b.id_billing === Number(id));
      const subUbah = () => $('#uSubtotal').text(rp(Math.max(0, angka($('#uHarga').val()) * (angka($('#uQty').val()) || 1) - angka($('#uDiskon').val()))));
      $('#uHarga, #uQty, #uDiskon').on('input', subUbah);
      $(document).on('click', '.b-ubah', function() {
        const b = cariJasa($(this).data('id')); if (!b) return;
        $('#uId').val(b.id_billing); $('#uNama').text(b.billing_item);
        $('#uHarga').val(b.billing_price); $('#uQty').val(b.billing_qty); $('#uDiskon').val(b.billing_discount); $('#uCatatan').val(b.billing_notes || '');
        subUbah(); $('#mUbah').modal('show');
      });
      $('#formUbah').on('submit', async function(e) {
        e.preventDefault();
        const btn = $(this).find('[type=submit]').prop('disabled', true);
        try {
          const j = await ambilJson('controller/visit/tindakan', { method: 'PUT', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: new URLSearchParams(new FormData(this)).toString() });
          $('#mUbah').modal('hide'); sukses(j.message); await muat();
        } catch (er) { gagal(er); } finally { btn.prop('disabled', false); }
      });
      $(document).on('click', '.b-hapus', async function() {
        const b = cariJasa($(this).data('id')); if (!b) return;
        const r = await Swal.fire({ icon: 'warning', title: 'Hapus tindakan?', html: `<b>${esc(b.billing_item)}</b> · ${rp(b.subtotal)}`, showCancelButton: true, confirmButtonText: 'Hapus', cancelButtonText: 'Batal', confirmButtonColor: '#dc2626' });
        if (!r.isConfirmed) return;
        try { const j = await ambilJson('controller/visit/tindakan?id=' + b.id_billing, { method: 'DELETE' }); sukses(j.message); await muat(); } catch (er) { gagal(er); }
      });

      // ---------- bayar ----------
      let metode = 'Tunai';
      const totalBayar = () => D ? D.tagihan.total : 0;
      function hitungKembali() {
        const u = angka($('#uangDiterima').val()), t = totalBayar();
        $('#bKembali').text(rp(Math.max(0, u - t))).toggleClass('text-success', u >= t && u > 0);
        const kurang = metode === 'Tunai' && u < t;
        $('#bKurang').toggleClass('d-none', !kurang || $('#uangDiterima').val() === '');
        $('#bSimpan').prop('disabled', kurang);
      }
      function cepatUang(t) {
        const pilih = [t];
        [5000, 10000, 20000, 50000, 100000].forEach(p => { const v = Math.ceil(t / p) * p; if (v > t && !pilih.includes(v)) pilih.push(v); });
        return pilih.sort((a, b) => a - b).slice(0, 5);
      }
      $('#uangDiterima').on('input', function() {
        const n = angka(this.value);
        this.value = n ? n.toLocaleString('id-ID') : '';
        hitungKembali();
      }).on('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); $('#bSimpan').trigger('click'); } });
      $('#bCepat').on('click', '.btn', function() { $('#uangDiterima').val(Number(this.dataset.v).toLocaleString('id-ID')); hitungKembali(); });
      $('#bMetode').on('click', '.btn', function() {
        metode = this.dataset.m;
        $('#bMetode .btn').removeClass('active'); $(this).addClass('active');
        $('#bTunai').toggleClass('d-none', metode !== 'Tunai'); $('#bNonTunai').toggleClass('d-none', metode === 'Tunai');
        hitungKembali();
      });
      async function kirimBayar(isi) {
        try {
          const j = await ambilJson('controller/visit/bayar.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(Object.assign({ id_visit: NO, total: totalBayar() }, isi)) });
          $('#mBayar').modal('hide');
          await Swal.fire({ icon: 'success', title: 'Pembayaran berhasil', html: `No. transaksi <b>${esc(j.nomor_transaksi)}</b>` + (j.kembalian > 0 ? `<div class="mt-2 fs-5">Kembalian <b>${rp(j.kembalian)}</b></div>` : ''),
            showCancelButton: true, confirmButtonText: '<i class="ti ti-printer me-1"></i>Cetak invoice', cancelButtonText: 'Tutup' })
            .then(r => { if (r.isConfirmed) window.open($('#btnInvoice').attr('href'), '_blank'); });
        } catch (er) { await gagal(er); }
        await muat();
      }
      $('#btnBayar').on('click', async () => {
        if (!D || lunas()) return;
        const t = totalBayar();
        if (t === 0) {
          const r = await Swal.fire({ icon: 'question', title: 'Tandai lunas?', text: 'Kunjungan ini tidak punya tagihan. Tandai sebagai lunas?', showCancelButton: true, confirmButtonText: 'Tandai lunas', cancelButtonText: 'Batal' });
          if (r.isConfirmed) kirimBayar({ metode_bayar: 'AUTO', bayar: 0 });
          return;
        }
        if (D.tagihan.tiket_belum_selesai) {
          const r = await Swal.fire({ icon: 'warning', title: 'Resep belum selesai', text: `${D.tagihan.tiket_belum_selesai} resep belum selesai di farmasi; harga obat masih bisa berubah. Lanjutkan pembayaran?`, showCancelButton: true, confirmButtonText: 'Lanjutkan', cancelButtonText: 'Batal' });
          if (!r.isConfirmed) return;
        }
        $('#bTotal').text(rp(t));
        $('#bCepat').html(cepatUang(t).map((v, i) => `<button type="button" class="btn btn-sm" data-v="${v}">${i === 0 ? 'Uang pas' : rp(v)}</button>`).join(''));
        $('#uangDiterima').val('');
        $('#bMetode .btn[data-m="Tunai"]').trigger('click');
        $('#mBayar').modal('show');
      });
      $('#mBayar').on('shown.bs.modal', () => $('#uangDiterima').trigger('focus'));
      $('#bSimpan').on('click', async function() {
        const t = totalBayar(), u = metode === 'Tunai' ? angka($('#uangDiterima').val()) : t;
        if (metode === 'Tunai' && u < t) { $('#bKurang').removeClass('d-none'); return; }
        const r = await Swal.fire({ icon: 'question', title: 'Simpan pembayaran?', html: `${esc(metode)} · <b>${rp(t)}</b>` + (u > t ? `<br>Kembalian <b>${rp(u - t)}</b>` : ''), showCancelButton: true, confirmButtonText: 'Ya, simpan', cancelButtonText: 'Batal' });
        if (!r.isConfirmed) return;
        const btn = $(this).prop('disabled', true);
        await kirimBayar({ metode_bayar: metode, bayar: u });
        btn.prop('disabled', false);
      });

      $('#btnMuat').on('click', muat);
      muat();
    })();
  </script>
</body>

</html>
