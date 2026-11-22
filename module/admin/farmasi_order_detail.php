<?php
/**
 * Detail resep satu kunjungan (farmasi): status tiket, item obat per tiket, tambah/ubah/hapus item, panggil pasien.
 * Data tiket+item: controller/farmasi/resepDetailVisit (1 request). Simpan/hapus item: controller/visit/permintaanFarmasiDetails
 * (cek faskes & kunci tiket selesai). Status: controller/farmasi/approveTiketOrder. PCare: controller/farmasi/sinkronObatPcare.
 */
$title = 'Detail Resep';
require '../../controller/view.php';

$FD_IDC = (string) ($_SESSION['id_customer'] ?? '');
session_write_close();
$FD_NO = trim((string) ($_GET['no'] ?? ''));
$h = fn($s) => htmlspecialchars((string) ($s ?? ''), ENT_QUOTES, 'UTF-8');

// identitas kunjungan (dulu: query tanpa prepared statement & tanpa batas faskes)
$pasien = null;
if ($FD_NO !== '') {
  $st = $koneksi->prepare("SELECT pv.visit_ID, pv.visit_date, pv.visit_time, pv.visit_antrian, pv.id_doctor, pv.id_poli, pv.source_hub, pv.id_provider, pv.noKartu,
      COALESCE(NULLIF(TRIM(pv.patient_name_pcare), ''), mp.patient_name) AS nama, mp.patient_name, mp.nomor_rm, mp.patient_gender, mp.patient_datebirth,
      mp.patient_allergy, prv.provider_name
    FROM pasien_visit pv
    LEFT JOIN ms_patient mp ON mp.id_patient = pv.id_patient
    LEFT JOIN ms_provider prv ON prv.id_provider = pv.id_provider
    WHERE pv.visit_ID = ? AND pv.id_customer = ? LIMIT 1");
  $st->bind_param('ss', $FD_NO, $FD_IDC);
  $st->execute();
  $pasien = $st->get_result()->fetch_assoc() ?: null;
  $st->close();
}

// master obat faskes (untuk form tambah/ubah)
$obat = [];
if ($pasien) {
  $st = $koneksi->prepare("SELECT id_pharmacy, pharmacy_name_generic, pharmacy_name_trade, pharmacy_sale, pharmacy_unit, pharmacy_stock
    FROM ms_pharmacy WHERE pharmacy_status = '1' AND id_customer = ? ORDER BY pharmacy_name_generic ASC");
  $st->bind_param('s', $FD_IDC);
  $st->execute();
  $obat = $st->get_result()->fetch_all(MYSQLI_ASSOC);
  $st->close();
}
$namaIsi = fn($v) => preg_match('/[\p{L}\p{N}]/u', (string) $v) ? trim((string) $v) : '';

$BLN = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
$tglID = function ($d) use ($BLN) {
  if (!$d || !($x = DateTime::createFromFormat('!Y-m-d', substr((string) $d, 0, 10)))) return '-';
  return (int) $x->format('d') . ' ' . $BLN[(int) $x->format('n')] . ' ' . $x->format('Y');
};
$umur = null;
if ($pasien && !empty($pasien['patient_datebirth']) && ($lahir = DateTime::createFromFormat('!Y-m-d', substr((string) $pasien['patient_datebirth'], 0, 10)))) {
  $umur = $lahir->diff(new DateTime('today', new DateTimeZone('Asia/Jakarta')));
}
$alergi = $pasien ? trim((string) $pasien['patient_allergy']) : '';
if (!preg_match('/[\p{L}\p{N}]/u', $alergi) || preg_match('/^(tidak( ada)?|tdk|-|none|nihil|0)$/i', $alergi)) $alergi = '';
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    @keyframes fd-spin { to { transform: rotate(360deg); } }
    .icon-spin { display: inline-block; animation: fd-spin 1s linear infinite; }
    .fd-icon { width: 44px; height: 44px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0; }
    .fd-nama { font-size: 1.15rem; font-weight: 700; color: #111827; line-height: 1.25; }
    .fd-sub { font-size: .8rem; color: #475569; }
    .fd-sub .sep { color: #cbd5e1; margin: 0 6px; }
    .fd-info { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px 18px; margin-top: 14px; padding-top: 14px; border-top: 1px dashed #e5eaef; }
    .fd-info .l { font-size: .7rem; text-transform: uppercase; letter-spacing: .04em; color: #64748b; font-weight: 600; }
    .fd-info .v { font-size: .9rem; color: #111827; font-weight: 600; word-break: break-word; }
    .fd-alergi { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 10px; padding: 8px 12px; font-size: .85rem; }

    .fd-status { display: inline-flex; border: 1px solid #cbd5e1; border-radius: 10px; overflow: hidden; }
    .fd-status button { border: 0; background: #fff; color: #334155; font-weight: 600; font-size: .82rem; padding: 7px 14px; display: inline-flex; align-items: center; gap: 6px; }
    .fd-status button + button { border-left: 1px solid #cbd5e1; }
    .fd-status button:hover:not(:disabled) { background: #f1f5f9; }
    .fd-status button:disabled { cursor: not-allowed; opacity: .55; }
    .fd-status button.on[data-s="1"] { background: #dc2626; color: #fff; opacity: 1; }
    .fd-status button.on[data-s="2"] { background: #2563eb; color: #fff; opacity: 1; }
    .fd-status button.on[data-s="3"] { background: #15803d; color: #fff; opacity: 1; }

    .fd-tiket { border: 1px solid #e5eaef; border-radius: 14px; background: #fff; margin-bottom: 14px; overflow: hidden; }
    .fd-tiket.s1 { border-left: 4px solid #dc2626; }
    .fd-tiket.s2 { border-left: 4px solid #2563eb; }
    .fd-tiket.s3 { border-left: 4px solid #15803d; }
    .fd-thead { padding: 12px 14px; background: #f8fafc; border-bottom: 1px solid #e5eaef; display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-start; }
    .fd-thead .judul { font-weight: 700; color: #111827; }
    .fd-catatan { background: #fffbeb; border: 1px solid #fde68a; color: #78350f; border-radius: 8px; padding: 6px 10px; font-size: .82rem; margin-top: 6px; }
    .fd-racik { background: #eef2ff; border: 1px solid #c7d2fe; color: #312e81; border-radius: 8px; padding: 6px 10px; font-size: .82rem; margin-top: 6px; }
    .fd-tbl { font-size: .86rem; margin: 0; }
    .fd-tbl thead th { font-size: .72rem; text-transform: uppercase; letter-spacing: .03em; color: #475569; font-weight: 600; background: #fff; white-space: nowrap; }
    .fd-tbl td { vertical-align: middle; }
    .fd-tbl .obat { font-weight: 600; color: #111827; }
    .fd-tbl tfoot td { font-weight: 700; background: #f8fafc; }
    .fd-aksi .btn { white-space: nowrap; }
    .fd-b-struk { background: #334155; color: #fff; }
    .fd-b-struk:hover { background: #1e293b; color: #fff; }
    .fd-b-resep { background: #fff; color: #92400e; border: 1px solid #d97706; }
    .fd-b-resep:hover { background: #d97706; color: #fff; }
    .badge.bg-warning-subtle { color: #1f2937 !important; background-color: #fef3c7 !important; }
    .fd-kosong { padding: 28px 12px; text-align: center; color: #475569; }
    .select2-container { width: 100% !important; }
    .select2-container--default .select2-selection--single { height: 38px !important; border: 1px solid #dee2e6 !important; border-radius: 6px !important; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 36px !important; padding-left: 12px !important; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px !important; }
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

          <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
            <a href="module/pharmacy/permintaan-resep" id="btnKembali" class="btn btn-light btn-sm"><i class="ti ti-arrow-left me-1"></i>Kembali</a>
            <span class="text-muted small">Farmasi · Detail resep</span>
          </div>

          <?php if (!$pasien): ?>
            <div class="card border-0 shadow-sm">
              <div class="card-body fd-kosong">
                <i class="ti ti-file-off fs-6 d-block mb-2"></i>
                <b>Kunjungan tidak ditemukan.</b><br>
                Nomor kunjungan tidak ada atau bukan milik faskes ini.
              </div>
            </div>
          <?php else: ?>

            <!-- identitas -->
            <div class="card border-0 shadow-sm mb-3">
              <div class="card-body p-3 p-md-4">
                <div class="d-flex flex-wrap align-items-start gap-3">
                  <span class="fd-icon bg-primary-subtle text-primary"><i class="ti ti-prescription"></i></span>
                  <div class="flex-grow-1" style="min-width:220px">
                    <div class="fd-nama"><?= $h($pasien['nama'] ?: '(tanpa nama)') ?></div>
                    <div class="fd-sub">
                      RM <?= $h($pasien['nomor_rm'] ?: '-') ?>
                      <span class="sep">|</span><?= $h($pasien['patient_gender'] ?: '-') ?><?= $umur ? ', ' . $umur->y . ' th' : '' ?>
                      <span class="sep">|</span><span class="badge <?= (string) $pasien['id_provider'] === '1' ? 'bg-success-subtle text-success' : 'bg-light text-dark border' ?>"><?= $h($pasien['provider_name'] ?: '-') ?></span>
                    </div>
                  </div>
                  <div class="d-flex flex-wrap align-items-center gap-2">
                    <div class="fd-status" id="fdStatus" title="Ubah status semua tiket yang belum selesai">
                      <button type="button" data-s="1"><i class="ti ti-hourglass"></i>Menunggu</button>
                      <button type="button" data-s="2"><i class="ti ti-pill"></i>Disiapkan</button>
                      <button type="button" data-s="3"><i class="ti ti-circle-check"></i>Selesai</button>
                    </div>
                    <button type="button" class="btn btn-warning btn-sm" id="btnPanggil"><i class="ti ti-volume me-1"></i>Panggil pasien</button>
                  </div>
                </div>
                <?php if ($alergi !== ''): ?>
                  <div class="fd-alergi mt-3"><i class="ti ti-alert-triangle me-1"></i><b>Alergi:</b> <?= $h($alergi) ?></div>
                <?php endif; ?>
                <div class="fd-info">
                  <div><div class="l">Dokter</div><div class="v"><?= $h($pasien['id_doctor'] ?: '-') ?></div></div>
                  <div><div class="l">Poli / asal</div><div class="v"><?= $h($pasien['id_poli'] ?: '-') ?><?= $pasien['source_hub'] && $pasien['source_hub'] !== 'Poliklinik' ? ' · ' . $h($pasien['source_hub']) : '' ?></div></div>
                  <div><div class="l">Kunjungan</div><div class="v"><?= $tglID($pasien['visit_date']) ?><?= $pasien['visit_time'] ? ' · ' . $h(substr((string) $pasien['visit_time'], 0, 5)) : '' ?></div></div>
                  <div><div class="l">Antrian</div><div class="v"><?= $h($pasien['visit_antrian'] ?: '-') ?></div></div>
                  <div><div class="l">Tanggal lahir</div><div class="v"><?= $tglID($pasien['patient_datebirth']) ?></div></div>
                  <div><div class="l">Total resep</div><div class="v" id="fdTotal">-</div></div>
                </div>
              </div>
            </div>

            <!-- tiket -->
            <div class="card border-0 shadow-sm">
              <div class="card-body p-3 p-md-4">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                  <div>
                    <h6 class="fw-semibold mb-0">Tiket resep</h6>
                    <div class="fd-sub" id="fdRingkas">memuat…</div>
                  </div>
                  <button type="button" class="btn btn-outline-primary btn-sm" id="btnMuatUlang"><i class="ti ti-refresh me-1"></i>Muat ulang</button>
                </div>
                <div id="fdTiket"><div class="fd-kosong"><i class="ti ti-loader-2 icon-spin"></i> Memuat resep…</div></div>
              </div>
            </div>

          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <?php if ($pasien): ?>
    <!-- modal tambah / ubah obat -->
    <div class="modal fade" id="programModal" tabindex="-1">
      <div class="modal-dialog">
        <form id="programForm" class="modal-content" novalidate>
          <div class="modal-header">
            <h5 class="modal-title" id="fdModalJudul">Tambah obat</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="id_pharmacy_details" id="id_pharmacy_details">
            <input type="hidden" name="id_permintaan_farmasi" id="id_permintaan_farmasi">
            <input type="hidden" name="id_visit" id="id_visit" value="<?= $h($FD_NO) ?>">
            <div id="fdKunciInfo" class="alert alert-info py-2 small d-none mb-3"><i class="ti ti-info-circle me-1"></i>Tiket ini sudah selesai. Obat baru tetap bisa ditambahkan; obat yang sudah ada terkunci.</div>
            <div class="mb-3">
              <label for="id_pharmacy" class="form-label">Obat / BMHP <span class="text-danger">*</span></label>
              <select name="id_pharmacy" id="id_pharmacy" class="form-select" required>
                <option value="">Pilih obat…</option>
                <?php foreach ($obat as $o):
                  $g = $namaIsi($o['pharmacy_name_generic']);
                  $t = $namaIsi($o['pharmacy_name_trade']);
                  $label = $g !== '' ? $g : $t;
                  if ($g !== '' && $t !== '' && strcasecmp($g, $t) !== 0) $label .= ' / ' . $t;
                ?>
                  <option value="<?= $h($o['id_pharmacy']) ?>" data-harga="<?= $h((int) $o['pharmacy_sale']) ?>" data-unit="<?= $h($o['pharmacy_unit']) ?>" data-stok="<?= $h($o['pharmacy_stock']) ?>"><?= $h($label !== '' ? $label : '(tanpa nama)') ?></option>
                <?php endforeach; ?>
              </select>
              <div class="form-text" id="fdObatInfo"></div>
            </div>
            <div class="row g-3">
              <div class="col-6">
                <label for="qty" class="form-label">Jumlah <span class="text-danger">*</span></label>
                <input type="number" id="qty" name="qty" class="form-control" min="1" step="1" value="1" required>
              </div>
              <div class="col-6">
                <label for="harga" class="form-label">Harga satuan</label>
                <input type="text" id="harga" class="form-control bg-light" value="-" readonly tabindex="-1">
                <div class="form-text">Mengikuti master obat.</div>
              </div>
              <div class="col-12">
                <label for="signa" class="form-label">Signa / aturan pakai <span class="text-danger">*</span></label>
                <input type="text" id="signa" name="signa" class="form-control" maxlength="255" placeholder="Contoh: 3 x 1 sesudah makan" required>
              </div>
              <div class="col-12">
                <label for="catatan" class="form-label">Catatan</label>
                <textarea id="catatan" name="catatan" class="form-control" rows="3"></textarea>
              </div>
            </div>
            <div id="fdFormError" class="alert alert-danger py-2 small mt-3 mb-0 d-none"></div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary" id="fdSimpan"><i class="ti ti-device-floppy me-1"></i>Simpan</button>
          </div>
        </form>
      </div>
    </div>
  <?php endif; ?>

  <?php require 'library.php'; ?>
  <?php if ($pasien): ?>
    <script src="controller/socket/socket.js"></script>
    <script>
      (function() {
        const VISIT = <?= json_encode($FD_NO) ?>;
        const RM = <?= json_encode((string) $pasien['nomor_rm']) ?>;
        const PASIEN = <?= json_encode(['nama' => (string) ($pasien['patient_name'] ?: $pasien['nama']), 'poli' => (string) $pasien['id_poli']], JSON_UNESCAPED_UNICODE) ?>;
        const STATUS = { 1: ['bg-danger-subtle text-danger', 'Menunggu'], 2: ['bg-primary-subtle text-primary', 'Disiapkan'], 3: ['bg-success-subtle text-success', 'Selesai'] };
        const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
        const rp = n => (Number(n) || 0) > 0 ? 'Rp ' + Number(n).toLocaleString('id-ID') : '-';
        // "2026-11-22 10:13:05" -> "22 Nov 2026 10:13" (new Date() pada format ini gagal di Safari)
        const waktu = s => { const m = String(s || '').match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})/); return m ? `${+m[3]} ${BULAN[+m[2] - 1]} ${m[1]} ${m[4]}:${m[5]}` : esc(s || '-'); };
        const isi = v => /[\p{L}\p{N}]/u.test(String(v ?? '')) ? String(v).trim() : '';
        const namaObat = r => { const g = isi(r.pharmacy_name_generic), t = isi(r.pharmacy_name_trade); return esc(g && t && g.toLowerCase() !== t.toLowerCase() ? `${g} / ${t}` : (g || t || r.item_name || '-')); };

        let tiket = [];
        const tiketStatus = {};
        const itemById = {};
        let sibuk = false;

        // kembali: ke halaman sebelumnya bila dari aplikasi ini
        $('#btnKembali').on('click', function(e) {
          if (document.referrer && document.referrer.indexOf(location.origin) === 0 && history.length > 1) { e.preventDefault(); history.back(); }
        });

        // ---------- muat ----------
        function muat(opsi = {}) {
          if (sibuk) return;
          sibuk = true;
          $('#btnMuatUlang i').attr('class', 'ti ti-loader-2 icon-spin me-1');
          $.ajax({ url: 'controller/farmasi/resepDetailVisit', dataType: 'json', data: { no: VISIT } })
            .done(res => { tiket = res.tiket || []; render(res.ringkas || {}); })
            .fail(x => {
              if (!opsi.diam) $('#fdTiket').html(`<div class="fd-kosong text-danger"><i class="ti ti-alert-circle d-block fs-6 mb-1"></i>${esc(x.responseJSON?.message || 'Gagal memuat resep.')} <button class="btn btn-sm btn-outline-danger ms-2" id="btnCobaLagi">Coba lagi</button></div>`);
            })
            .always(() => { sibuk = false; $('#btnMuatUlang i').attr('class', 'ti ti-refresh me-1'); });
        }
        $(document).on('click', '#btnCobaLagi', () => muat());
        $('#btnMuatUlang').on('click', () => muat());
        // socket.js memanggil APP.updateDataLokal saat ada pembaruan farmasi (dulu fungsi ini tidak ada -> error)
        window.APP = window.APP || {};
        window.APP.updateDataLokal = () => muat({ diam: true });

        function render(rk) {
          const bag = [];
          if (rk.menunggu) bag.push(`${rk.menunggu} menunggu`);
          if (rk.disiapkan) bag.push(`${rk.disiapkan} disiapkan`);
          if (rk.selesai) bag.push(`${rk.selesai} selesai`);
          $('#fdRingkas').text(tiket.length ? `${tiket.length} tiket · ${bag.join(' · ')}` : 'Belum ada tiket yang dikirim dokter');
          $('#fdTotal').text(tiket.length ? rp(rk.total) : '-');
          aturStatus();

          if (!tiket.length) {
            $('#fdTiket').html('<div class="fd-kosong"><i class="ti ti-mood-empty d-block fs-6 mb-1"></i>Belum ada resep yang dikirim ke farmasi untuk kunjungan ini.</div>');
            return;
          }
          $('#fdTiket').html(tiket.map(htmlTiket).join(''));
        }

        function htmlTiket(t) {
          const id = t.id_permintaan_farmasi;
          tiketStatus[id] = t.status_permintaan;
          const [k, l] = STATUS[t.status_permintaan] || ['bg-light text-dark', '-'];
          const selesai = t.status_permintaan === 3;
          const tipe = String(t.tipe_obat || '');
          const racikan = tipe.toLowerCase() === 'racikan'; // dulu dibandingkan dengan 'racikan' (huruf kecil) -> info racikan tidak pernah tampil
          const q = `no=${encodeURIComponent(VISIT)}&rm=${encodeURIComponent(RM)}&id=${id}`;
          const adaHarga = t.items.some(d => d.harga > 0); // harga 0 semua -> kolom harga disembunyikan
          const nKol = adaHarga ? 7 : 5;

          const baris = t.items.map((d, i) => {
            itemById[d.id_pharmacy_details] = d;
            const unit = d.pharmacy_unit ? ` <span class="fd-sub">${esc(d.pharmacy_unit)}</span>` : '';
            return `<tr>
              <td class="text-muted">${i + 1}</td>
              <td><div class="obat">${namaObat(d)}</div>${d.catatan && isi(d.catatan) ? `<div class="fd-sub">${esc(d.catatan)}</div>` : ''}</td>
              <td class="text-nowrap">${esc(d.qty)}${unit}</td>
              <td>${esc(d.signa || '-')}</td>
              ${adaHarga ? `<td class="text-end text-nowrap">${rp(d.harga)}</td><td class="text-end text-nowrap">${rp(d.subtotal)}</td>` : ''}
              <td class="text-end text-nowrap">${selesai
                ? '<span class="fd-sub" title="Tiket sudah selesai"><i class="ti ti-lock"></i> Terkunci</span>'
                : `<button type="button" class="btn btn-sm btn-outline-primary btn-edit" data-id="${d.id_pharmacy_details}" title="Ubah"><i class="ti ti-pencil"></i></button>
                   <button type="button" class="btn btn-sm btn-outline-danger btn-hapus" data-id="${d.id_pharmacy_details}" data-tiket="${id}" title="Hapus"><i class="ti ti-trash"></i></button>`}</td>
            </tr>`;
          }).join('');

          const rck = racikan && (t.rck_nama || t.rck_jumlah || t.rck_signa)
            ? `<div class="fd-racik"><b>Racikan${t.rck_nama ? ': ' + esc(t.rck_nama) : ''}</b> · ${esc(t.rck_jumlah || '-')} ${esc(t.rck_satuan || '')}${t.rck_signa ? ' · signa ' + esc(t.rck_signa) : ''}</div>` : '';
          const cat = isi(t.catatan_permintaan) ? `<div class="fd-catatan"><i class="ti ti-note me-1"></i><b>Catatan dokter:</b> ${esc(t.catatan_permintaan)}</div>` : '';

          return `<div class="fd-tiket s${t.status_permintaan}">
            <div class="fd-thead">
              <div class="flex-grow-1" style="min-width:220px">
                <div class="d-flex flex-wrap align-items-center gap-2">
                  <span class="judul">${esc(tipe || 'Resep')}</span>
                  <span class="badge ${k}">${l}</span>
                  ${+t.status_obat_pulang ? '<span class="badge bg-light text-dark border"><i class="ti ti-home me-1"></i>Obat pulang</span>' : ''}
                </div>
                <div class="fd-sub mt-1">${t.permintaan_number ? esc(t.permintaan_number) + ' · ' : ''}${waktu(t.created_at)} · ${t.items.length} item</div>
                ${rck}${cat}
              </div>
              <div class="fd-aksi d-flex flex-wrap gap-2">
                <a class="btn btn-sm fd-b-struk" target="_blank" rel="noopener" href="module/print/struk_obat?${q}" title="Cetak struk obat"><i class="ti ti-printer me-1"></i>Struk</a>
                <a class="btn btn-sm fd-b-resep" target="_blank" rel="noopener" href="module/print/resep?${q}" title="Cetak resep"><i class="ti ti-file-text me-1"></i>Resep</a>
                <button type="button" class="btn btn-sm btn-primary btn-tambah" data-id="${id}"><i class="ti ti-plus me-1"></i>Tambah obat</button>
              </div>
            </div>
            <div class="table-responsive">
              <table class="table fd-tbl">
                <thead><tr><th style="width:1%">#</th><th>Obat</th><th>Jumlah</th><th>Signa</th>${adaHarga ? '<th class="text-end">Harga</th><th class="text-end">Subtotal</th>' : ''}<th class="text-end" style="width:1%">Aksi</th></tr></thead>
                <tbody>${baris || `<tr><td colspan="${nKol}" class="fd-kosong py-3">Belum ada obat di tiket ini.</td></tr>`}</tbody>
                ${adaHarga ? `<tfoot><tr><td colspan="5" class="text-end">Total tiket</td><td class="text-end text-nowrap">${rp(t.total)}</td><td></td></tr></tfoot>` : ''}
              </table>
            </div>
          </div>`;
        }

        // ---------- status semua tiket ----------
        let statusKini = null;
        function aturStatus() {
          const ada = s => tiket.some(t => t.status_permintaan === s);
          const terbuka = ada(1) || ada(2);
          // prioritas sama seperti sebelumnya: disiapkan > menunggu > selesai
          statusKini = !tiket.length ? null : (ada(2) ? 2 : (ada(1) ? 1 : 3));
          $('#fdStatus button').each(function() {
            const s = +this.dataset.s;
            $(this).toggleClass('on', s === statusKini).prop('disabled', !terbuka || s === statusKini);
          });
          $('#fdStatus').attr('title', !tiket.length ? 'Belum ada tiket' : (terbuka ? 'Ubah status semua tiket yang belum selesai' : 'Semua tiket sudah selesai (terkunci)'));
        }

        $('#fdStatus').on('click', 'button', function() {
          const s = +this.dataset.s;
          const nama = { 1: 'Menunggu', 2: 'Disiapkan', 3: 'Selesai' }[s];
          const kunci = s === 3;
          Swal.fire({
            icon: kunci ? 'warning' : 'question',
            title: `Ubah status ke "${nama}"?`,
            html: kunci ? 'Semua tiket yang belum selesai akan ditandai <b>Selesai</b>.<br><span class="small text-danger">Obat pada tiket selesai tidak bisa diubah atau dihapus lagi.</span>' : 'Berlaku untuk semua tiket yang belum selesai.',
            showCancelButton: true, confirmButtonText: 'Ya, ubah', cancelButtonText: 'Batal', reverseButtons: true,
            confirmButtonColor: kunci ? '#15803d' : '#5d87ff',
          }).then(x => {
            if (!x.isConfirmed) return;
            $('#fdStatus button').prop('disabled', true);
            fetch('controller/farmasi/approveTiketOrder.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ visit: VISIT, status: s }) })
              .then(r => r.json())
              .then(r => {
                if (r.status === 'success') Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: `Status: ${nama}`, timer: 1800, showConfirmButton: false });
                else Swal.fire({ icon: 'error', title: 'Gagal', text: r.message || 'Status tidak berubah.' });
              })
              .catch(() => Swal.fire({ icon: 'error', title: 'Gagal', text: 'Server tidak merespon.' }))
              .finally(() => muat());
          });
        });

        // ---------- PCare ----------
        // Kirim ulang terapi obat kunjungan ke PCare bila tiket sudah pernah dikirim (status >= 1)
        function sinkronPcare(idTiket, pesan) {
          if ((tiketStatus[idTiket] || 0) < 1) {
            Swal.fire({ icon: 'success', title: 'Berhasil', text: pesan, timer: 1500, showConfirmButton: false });
            return Promise.resolve();
          }
          Swal.fire({ title: 'Mengirim ke PCare…', html: `${esc(pesan)}<br><small class="text-muted">Memperbarui obat kunjungan di PCare BPJS</small>`, allowOutsideClick: false, didOpen: () => Swal.showLoading() });
          return fetch('controller/farmasi/sinkronObatPcare.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id_permintaan_farmasi: idTiket }) })
            .then(r => r.json().catch(() => ({ status: 'error', message: `Respon server tidak valid (HTTP ${r.status}).` })))
            .then(r => {
              if (r.status === 'success') Swal.fire({ icon: 'success', title: 'Berhasil', html: `${esc(pesan)}<br><b>Obat di PCare sudah diperbarui.</b>`, timer: 2200, showConfirmButton: false });
              else if (r.status === 'skip') Swal.fire({ icon: 'success', title: 'Berhasil', html: `${esc(pesan)}<br><small class="text-muted">${esc(r.message)}</small>`, timer: 2200, showConfirmButton: false });
              else Swal.fire({
                icon: 'warning', title: 'PCare belum diperbarui',
                html: `${esc(r.message || 'Gagal mengirim ke PCare.')}<br><small class="text-muted">Data obat sudah tersimpan. Coba lagi nanti atau kirim ulang dari Monitoring Antrean.</small>`,
                showCancelButton: true, confirmButtonText: 'Coba lagi', cancelButtonText: 'Tutup'
              }).then(x => x.isConfirmed && sinkronPcare(idTiket, 'Mengulang pengiriman obat.'));
            })
            .catch(() => Swal.fire('PCare belum diperbarui', 'Koneksi ke server gagal. Data obat sudah tersimpan.', 'warning'));
        }

        // ---------- form tambah / ubah ----------
        const $modal = $('#programModal');
        const $obat = $('#id_pharmacy');
        $obat.select2({ width: '100%', placeholder: 'Cari nama obat…', allowClear: true, dropdownParent: $modal });
        $obat.on('change', function() {
          const o = this.selectedOptions[0];
          if (!o || !o.value) { $('#harga').val('-'); $('#fdObatInfo').text(''); return; }
          $('#harga').val(rp(o.dataset.harga));
          const stok = o.dataset.stok;
          $('#fdObatInfo').html([o.dataset.unit ? 'Satuan ' + esc(o.dataset.unit) : '', stok !== '' && stok !== undefined ? `Stok tercatat <b>${esc(stok)}</b>` : ''].filter(Boolean).join(' · '));
        });
        $modal.on('shown.bs.modal', () => { if (!$obat.val()) $obat.select2('open'); });

        function bukaForm(idTiket, item) {
          $('#programForm')[0].reset();
          $('#fdFormError').addClass('d-none');
          $('#id_permintaan_farmasi').val(idTiket);
          $('#id_pharmacy_details').val(item ? item.id_pharmacy_details : '');
          $obat.val(item ? String(item.id_pharmacy) : null).trigger('change');
          $('#qty').val(item ? item.qty : 1);
          $('#signa').val(item ? (item.signa || '') : '');
          $('#catatan').val(item ? (item.catatan || '') : '');
          $('#fdKunciInfo').toggleClass('d-none', !(tiketStatus[idTiket] === 3 && !item));
          const t = tiket.find(x => x.id_permintaan_farmasi === +idTiket);
          $('#fdModalJudul').text(`${item ? 'Ubah obat' : 'Tambah obat'} · ${t && t.tipe_obat ? t.tipe_obat : 'Tiket #' + idTiket}`);
          $modal.modal('show');
        }

        $(document).on('click', '.btn-tambah', function() { bukaForm(this.dataset.id, null); });
        $(document).on('click', '.btn-edit', function() {
          const d = itemById[this.dataset.id];
          if (d) bukaForm(String(d.id_permintaan_farmasi), d);
        });

        $('#programForm').on('submit', function(e) {
          e.preventDefault();
          const idDetail = $('#id_pharmacy_details').val();
          const idTiket = $('#id_permintaan_farmasi').val();
          const qty = $('#qty').val();
          const err = [];
          if (!$obat.val()) err.push('pilih obat');
          if (!/^\d+$/.test(qty) || +qty < 1) err.push('jumlah minimal 1 (bilangan bulat)');
          if (!$('#signa').val().trim()) err.push('signa wajib diisi');
          if (err.length) { $('#fdFormError').removeClass('d-none').text('Periksa: ' + err.join('; ') + '.'); return; }
          $('#fdFormError').addClass('d-none');

          const edit = /^\d+$/.test(idDetail);
          const data = new URLSearchParams({
            id_permintaan_farmasi: idTiket, id_pharmacy: $obat.val(), qty, signa: $('#signa').val().trim(), catatan: $('#catatan').val().trim(),
          });
          if (edit) data.set('id_pharmacy_details', idDetail);
          const $b = $('#fdSimpan').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan…');
          fetch('controller/visit/permintaanFarmasiDetails.php', {
            method: edit ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: data,
          })
            .then(r => r.json().catch(() => { throw new Error(`HTTP ${r.status}`); }))
            .then(r => {
              if (r.status !== 'success') { $('#fdFormError').removeClass('d-none').text(r.message || 'Gagal menyimpan.'); return; }
              $modal.modal('hide');
              sinkronPcare(idTiket, edit ? 'Obat berhasil diperbarui.' : 'Obat berhasil ditambahkan.');
              muat();
            })
            .catch(e => $('#fdFormError').removeClass('d-none').text('Gagal menyimpan: ' + e.message))
            .finally(() => $b.prop('disabled', false).html('<i class="ti ti-device-floppy me-1"></i>Simpan'));
        });

        // ---------- hapus (lewat permintaanFarmasiDetails: cek faskes & tiket selesai) ----------
        $(document).on('click', '.btn-hapus', function() {
          const d = itemById[this.dataset.id];
          const idTiket = this.dataset.tiket;
          if (!d) return;
          Swal.fire({
            icon: 'warning', title: 'Hapus obat ini?', html: `<b>${namaObat(d)}</b><br><span class="small text-muted">${esc(d.qty)} · ${esc(d.signa || '-')}</span>`,
            showCancelButton: true, confirmButtonText: 'Ya, hapus', cancelButtonText: 'Batal', confirmButtonColor: '#dc2626', reverseButtons: true,
          }).then(x => {
            if (!x.isConfirmed) return;
            fetch(`controller/visit/permintaanFarmasiDetails.php?id=${encodeURIComponent(d.id_pharmacy_details)}`, { method: 'DELETE' })
              .then(r => r.json().catch(() => ({ status: 'error', message: `HTTP ${r.status}` })))
              .then(r => {
                if (r.status === 'success') sinkronPcare(idTiket, 'Obat berhasil dihapus.');
                else Swal.fire('Gagal', r.message || 'Gagal menghapus obat.', 'error');
              })
              .catch(() => Swal.fire('Gagal', 'Server tidak merespon.', 'error'))
              .finally(() => muat());
          });
        });

        // ---------- panggil pasien ----------
        $('#btnPanggil').on('click', function() {
          const $b = $(this);
          const requestId = crypto.randomUUID ? crypto.randomUUID() : String(Date.now()) + Math.random();
          sessionStorage.setItem('requestId', requestId);
          $b.prop('disabled', true).find('i').attr('class', 'ti ti-loader-2 icon-spin me-1');
          const pulih = () => $b.prop('disabled', false).find('i').attr('class', 'ti ti-volume me-1');
          $.ajax({
            url: 'controller/admisi/soundFarmasi.php', type: 'POST', dataType: 'json',
            data: { text: `pasien ${PASIEN.nama}, dipersilahkan untuk mengambil obat`, requestIdFarmasi: requestId, nama_pasien: PASIEN.nama, asalpoli: PASIEN.poli, visit_id: VISIT }
          })
            .done(() => { Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Pasien dipanggil', timer: 1800, showConfirmButton: false }); setTimeout(pulih, 5000); })
            .fail(x => { pulih(); Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: x.responseJSON?.message || 'Gagal memanggil pasien', timer: 3000, showConfirmButton: false }); });
        });

        muat();
      })();
    </script>
  <?php endif; ?>
</body>

</html>
