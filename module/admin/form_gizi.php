<?php
/**
 * Asesmen Gizi & Nutrisi (Proses Asuhan Gizi Terstandar) per kunjungan.
 * Formulir dibangun dari controller/visit/giziSchema.php; disimpan lewat controller/visit/giziController.php.
 * Perbaikan: tombol Simpan dulu tidak menyimpan apa pun (hanya console.log + pesan "berhasil" — data hilang);
 * tidak bisa membuka asesmen tersimpan; tombol Cetak mengarah ke halaman yang tidak ada; id_patient diisi nomor RM;
 * query pasien dirangkai dari URL (SQL injection) & tidak dipakai; $_GET no/rm/rme tanpa default; kategori IMT
 * memakai batas WHO internasional (bukan Kemenkes) & tetap dihitung untuk anak; BB ideal Broca tanpa memperhitungkan
 * jenis kelamin/tinggi; perubahan BB tanpa tanda naik/turun; hapus baris terakhir monitoring tidak mengosongkan select.
 */
$title = 'Asesmen Gizi';
require '../../controller/view.php';
require_once __DIR__ . '/../../controller/visit/giziSchema.php';
$no = trim((string) ($_GET['no'] ?? ''));
$rm = trim((string) ($_GET['rm'] ?? ''));
$rme = (string) ($_GET['rme'] ?? '');
$h = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
$skema = giziSkema();

/** Render 1 field formulir. */
function giziField(string $key, array $def, callable $h): string
{
   $lebar = (int) ($def['lebar'] ?? 12);
   $wajib = !empty($def['wajib']) ? ' <span class="text-danger">*</span>' : (!empty($def['wajib_selesai']) ? ' <span class="text-warning-emphasis" title="Wajib sebelum ditandai Selesai">•</span>' : '');
   $lbl = '<label class="form-label small fw-semibold" for="f_' . $key . '">' . $h($def['label']) . $wajib . '</label>';
   $fb = '<div class="invalid-feedback"></div>';
   $unit = fn($input) => !empty($def['unit']) ? '<div class="input-group">' . $input . '<span class="input-group-text">' . $h($def['unit']) . '</span></div>' : $input;
   switch ($def['tipe']) {
      case 'date':
         $el = $lbl . '<input type="date" class="form-control" id="f_' . $key . '" data-f="' . $key . '">' . $fb;
         break;
      case 'text':
         $el = $lbl . '<input type="text" class="form-control" id="f_' . $key . '" data-f="' . $key . '" maxlength="' . (int) ($def['max'] ?? 255) . '" placeholder="' . $h($def['ph'] ?? '') . '">' . $fb;
         break;
      case 'textarea':
         $el = $lbl . '<textarea class="form-control" id="f_' . $key . '" data-f="' . $key . '" rows="' . (int) ($def['baris'] ?? 3) . '" maxlength="5000" placeholder="' . $h($def['ph'] ?? '') . '"></textarea>' . $fb;
         break;
      case 'number':
         $el = $lbl . $unit('<input type="number" inputmode="decimal" class="form-control" id="f_' . $key . '" data-f="' . $key . '" min="' . $def['min'] . '" max="' . $def['max'] . '" step="' . $h($def['step']) . '">') . '<div class="invalid-feedback d-block"></div>';
         break;
      case 'select':
         $el = $lbl . '<select class="form-select" id="f_' . $key . '" data-f="' . $key . '"><option value="">— Pilih —</option>'
            . implode('', array_map(fn($o) => '<option>' . $h($o) . '</option>', $def['opsi'])) . '</select>' . $fb;
         break;
      case 'radio':
         $el = '<div class="form-label small fw-semibold" id="f_' . $key . '">' . $h($def['label']) . '</div><div class="seg" data-radio="' . $key . '">'
            . implode('', array_map(fn($o, $i) => '<input type="radio" name="r_' . $key . '" id="r_' . $key . $i . '" value="' . $h($o) . '"><label for="r_' . $key . $i . '">' . $h($o) . '</label>', $def['opsi'], array_keys($def['opsi'])))
            . '</div><div class="invalid-feedback d-block"></div>';
         break;
      case 'checks':
         $el = '<div class="form-label small fw-semibold" id="f_' . $key . '">' . $h($def['label']) . '</div><div class="chips" data-checks="' . $key . '">'
            . implode('', array_map(fn($o, $i) => '<input type="checkbox" id="c_' . $key . $i . '" value="' . $h($o) . '"><label for="c_' . $key . $i . '">' . $h($o === 'Label Pangan' ? 'Membaca Label Pangan' : $o) . '</label>', $def['opsi'], array_keys($def['opsi'])))
            . '</div>';
         break;
      case 'calc':
         $input = !empty($def['panjang'])
            ? '<textarea class="form-control hitung" id="f_' . $key . '" data-calc="' . $key . '" rows="2" readonly tabindex="-1"></textarea>'
            : '<input type="text" class="form-control hitung" id="f_' . $key . '" data-calc="' . $key . '" readonly tabindex="-1">';
         $el = '<label class="form-label small fw-semibold" for="f_' . $key . '">' . $h($def['label']) . ' <i class="ti ti-calculator text-muted" title="Dihitung otomatis"></i></label>' . $unit($input);
         break;
      case 'lab':
         $rows = '';
         foreach (giziLab() as $lk => [$nama, $u, $min, $max]) {
            $rows .= '<tr><td class="fw-semibold">' . $h($nama) . '</td>'
               . '<td><div class="input-group input-group-sm"><input type="number" inputmode="decimal" step="any" min="' . $min . '" max="' . $max . '" class="form-control" id="f_lab_' . $lk . '_nilai" data-lab="' . $lk . '" data-lk="nilai"><span class="input-group-text">' . $h($u) . '</span></div><div class="invalid-feedback d-block"></div></td>'
               . '<td><input type="date" class="form-control form-control-sm" id="f_lab_' . $lk . '_tanggal" data-lab="' . $lk . '" data-lk="tanggal"><div class="invalid-feedback"></div></td>'
               . '<td><input type="text" maxlength="255" class="form-control form-control-sm" data-lab="' . $lk . '" data-lk="keterangan"></td></tr>';
         }
         $el = '<div class="d-flex align-items-center mb-2"><span class="small text-muted">Isi yang tersedia saja.</span></div>'
            . '<div class="table-responsive"><table class="t"><thead><tr><th>Pemeriksaan</th><th style="min-width:170px">Hasil</th><th>Tanggal</th><th>Keterangan</th></tr></thead><tbody>' . $rows . '</tbody></table></div>';
         break;
      case 'tabel':
         $th = implode('', array_map(fn($c) => '<th>' . $h($c[0]) . '</th>', $def['kolom']));
         $el = '<div class="d-flex align-items-center mb-2" id="f_' . $key . '"><span class="form-label small fw-semibold mb-0">' . $h($def['label']) . '</span>'
            . '<button type="button" class="btn btn-sm btn-outline-dark ms-auto" data-tambah-baris="' . $key . '"><i class="ti ti-plus me-1"></i>Tambah baris</button></div>'
            . '<div class="table-responsive"><table class="t"><thead><tr><th style="width:44px">Aksi</th>' . $th . '</tr></thead><tbody data-tabel="' . $key . '"></tbody></table></div><div class="invalid-feedback d-block"></div>';
         break;
      default:
         $el = '';
   }
   return '<div class="col-md-' . $lebar . '">' . $el . '</div>';
}
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .gz .kepala { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-bottom: 16px; }
    .gz .kepala .ic { width: 42px; height: 42px; border-radius: 12px; background: #fef3c7; color: #92400e; display: inline-flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
    .gz .kepala h5 { margin: 0; font-weight: 700; color: #0f172a; }
    .gz .kepala small { color: #475569; }
    .gz table.t { width: 100%; border-collapse: collapse; font-size: .86rem; }
    .gz table.t th { font-size: .7rem; text-transform: uppercase; color: #475569; font-weight: 700; background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 7px 8px; white-space: nowrap; }
    .gz table.t td { border-bottom: 1px solid #eef2f6; padding: 7px 8px; vertical-align: top; color: #1f2937; }
    .gz table.t tr.aktif td { background: #fffbeb; }
    .gz .aksi { white-space: nowrap; }
    .gz .aksi .btn { padding: 3px 9px; font-weight: 600; }
    .gz .tag { font-size: .7rem; font-weight: 700; border-radius: 6px; padding: 2px 8px; display: inline-block; white-space: nowrap; }
    .gz .tg-draft { background: #f1f5f9; color: #334155; }
    .gz .tg-selesai { background: #dcfce7; color: #14532d; }
    .gz .tg-rendah { background: #dcfce7; color: #14532d; }
    .gz .tg-sedang { background: #fef3c7; color: #92400e; }
    .gz .tg-tinggi { background: #fee2e2; color: #991b1b; }
    .gz .kosong { text-align: center; padding: 26px 10px; color: #475569; }
    .gz .nav-sec { position: sticky; top: 90px; z-index: 5; background: #fff; border-bottom: 1px solid #e2e8f0; padding: 8px 0; margin-bottom: 14px; display: flex; gap: 6px; overflow-x: auto; }
    .gz .nav-sec a { white-space: nowrap; border: 1px solid #e2e8f0; border-radius: 999px; padding: 3px 11px; font-size: .78rem; font-weight: 600; color: #1f2937; text-decoration: none; }
    .gz .nav-sec a:hover { border-color: #94a3b8; }
    .gz .nav-sec a b { color: #92400e; margin-right: 3px; }
    .gz .sec { border: 1px solid #e2e8f0; border-radius: 14px; margin-bottom: 14px; scroll-margin-top: 150px; }
    .gz .sec-h { display: flex; align-items: center; gap: 8px; padding: 10px 14px; border-bottom: 1px solid #eef2f6; background: #f8fafc; border-radius: 14px 14px 0 0; }
    .gz .sec-h .huruf { width: 26px; height: 26px; border-radius: 8px; background: #fef3c7; color: #92400e; display: inline-flex; align-items: center; justify-content: center; font-weight: 800; font-size: .82rem; }
    .gz .sec-h b { color: #0f172a; }
    .gz .sec-b { padding: 14px; }
    .gz .seg { display: flex; gap: 6px; }
    .gz .seg input, .gz .chips input { position: absolute; opacity: 0; pointer-events: none; }
    .gz .seg label { flex: 1; text-align: center; border: 1px solid #cbd5e1; border-radius: 8px; padding: 6px 8px; cursor: pointer; font-weight: 600; font-size: .85rem; color: #1f2937; background: #fff; }
    .gz .seg input:checked + label { background: #0f172a; border-color: #0f172a; color: #fff; }
    .gz .seg input:focus-visible + label, .gz .chips input:focus-visible + label { outline: 2px solid #2563eb; outline-offset: 1px; }
    .gz .chips { display: flex; flex-wrap: wrap; gap: 6px; }
    .gz .chips label { border: 1px solid #cbd5e1; border-radius: 999px; padding: 4px 12px; cursor: pointer; font-size: .82rem; font-weight: 600; color: #1f2937; background: #fff; }
    .gz .chips input:checked + label { background: #fef3c7; border-color: #d97706; color: #78350f; }
    .gz .chips input:checked + label::before { content: "✓ "; }
    .gz .hitung { background: #f0f9ff !important; color: #0c4a6e; font-weight: 600; }
    .gz .bar-simpan { position: sticky; bottom: 0; z-index: 6; background: #fff; border-top: 1px solid #e2e8f0; padding: 10px 0; display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
    .gz .bar-simpan .info { font-size: .82rem; color: #475569; margin-right: auto; }
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
          <?php
          if ($rme === 'a') include 'menu_rme.php';
          elseif ($rme === 'b') include 'menu_rmeb.php';
          elseif ($rme === 'c') include 'menu_rme_inap.php';
          ?>
          <div class="row">
            <div class="col-12"><?php require 'card-pasien.php'; ?></div>
            <div class="col-12">
              <div class="card w-100">
                <div class="card-body p-4 gz">
                  <div class="kepala">
                    <span class="ic"><i class="ti ti-salad"></i></span>
                    <div class="flex-grow-1">
                      <h5>Asesmen Gizi &amp; Nutrisi</h5>
                      <small>Skrining, asesmen, diagnosis gizi (PES), intervensi diet, edukasi, serta monitoring &amp; evaluasi.</small>
                    </div>
                    <a class="btn btn-outline-dark" target="_blank" rel="noopener" title="Formulir asesmen kosong (identitas pasien terisi) untuk diisi manual"
                      href="module/admin/print/formulir_gizi?<?= $h(http_build_query(['no' => $no, 'rm' => $rm, 'kosong' => 1])) ?>"><i class="ti ti-file me-1"></i>Formulir kosong</a>
                    <button type="button" class="btn btn-primary" id="gzBtnBaru" disabled><i class="ti ti-plus me-1"></i>Asesmen baru</button>
                  </div>
                  <div class="alert alert-warning d-none" id="gzMigrasi"><i class="ti ti-alert-triangle me-1"></i>Tabel asesmen gizi belum dibuat (migrasi <code>20261124_pasien_gizi.sql</code> belum dijalankan) — asesmen belum bisa disimpan.</div>
                  <div class="alert alert-danger d-none" id="gzBatal"><i class="ti ti-ban me-1"></i>Kunjungan ini sudah dibatalkan — asesmen baru tidak bisa ditambahkan.</div>

                  <div class="table-responsive mb-3">
                    <table class="t">
                      <thead><tr><th>Aksi</th><th>Tanggal</th><th>Nutrisionis</th><th>Risiko</th><th>Diagnosis gizi (PES)</th><th>Diet</th><th>Status</th></tr></thead>
                      <tbody id="gzRiwayat"><tr><td colspan="7" class="kosong"><span class="spinner-border spinner-border-sm me-2"></span>Memuat…</td></tr></tbody>
                    </table>
                  </div>

                  <!-- FORMULIR -->
                  <div id="gzForm" class="d-none">
                    <div class="d-flex align-items-center gap-2 mb-1">
                      <h6 class="fw-bold mb-0" id="gzJudul">Asesmen baru</h6>
                      <span class="tag tg-draft" id="gzStatus">Draft</span>
                    </div>
                    <nav class="nav-sec" aria-label="Bagian formulir">
                      <?php foreach ($skema as $kode => [$judul]): ?>
                        <a href="#sec-<?= $kode ?>"><?= $kode === 'id' ? '' : '<b>' . $kode . '</b>' ?><?= $h($judul) ?></a>
                      <?php endforeach; ?>
                    </nav>
                    <?php foreach ($skema as $kode => [$judul, $ikon, $fields]): ?>
                      <section class="sec" id="sec-<?= $kode ?>">
                        <div class="sec-h">
                          <?= $kode === 'id' ? '<span class="huruf"><i class="ti ' . $ikon . '"></i></span>' : '<span class="huruf">' . $kode . '</span>' ?>
                          <b><?= $h($judul) ?></b>
                        </div>
                        <div class="sec-b"><div class="row g-3">
                          <?php foreach ($fields as $key => $def) echo giziField($key, $def, $h); ?>
                        </div></div>
                      </section>
                    <?php endforeach; ?>
                    <div class="bar-simpan">
                      <span class="info" id="gzInfo"><span class="text-warning-emphasis">•</span> = wajib sebelum ditandai Selesai</span>
                      <button type="button" class="btn btn-outline-dark" id="gzBtnTutup">Tutup</button>
                      <a class="btn btn-outline-dark disabled" id="gzBtnCetak" target="_blank" rel="noopener" aria-disabled="true" title="Simpan dulu untuk mencetak"><i class="ti ti-printer me-1"></i>Cetak</a>
                      <button type="button" class="btn btn-outline-primary" id="gzBtnDraft"><i class="ti ti-device-floppy me-1"></i>Simpan draft</button>
                      <button type="button" class="btn btn-success" id="gzBtnSelesai"><i class="ti ti-circle-check me-1"></i>Simpan &amp; selesai</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php require 'library.php'; ?>
  <script>
    (function() {
      'use strict';
      const NO = <?= json_encode($no) ?>, RM = <?= json_encode($rm) ?>;
      const API = 'controller/visit/giziController?no=' + encodeURIComponent(NO);
      const SKEMA = <?= json_encode(array_map(fn($s) => array_map(fn($f) => ['tipe' => $f['tipe']] + (isset($f['kolom']) ? ['kolom' => $f['kolom']] : []), $s[2]), $skema), JSON_UNESCAPED_UNICODE) ?>;
      const LAB = <?= json_encode(array_keys(giziLab())) ?>;
      const $id = i => document.getElementById(i);
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const BLN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      const tglId = s => { const m = String(s || '').match(/^(\d{4})-(\d{2})-(\d{2})/); return m ? (+m[3]) + ' ' + BLN[m[2] - 1] + ' ' + m[1] : '-'; };
      const toast = (title, icon = 'success') => Swal.fire({ toast: true, position: 'top-end', icon, title, showConfirmButton: false, timer: 2400 });
      const FIELDS = Object.assign({}, ...Object.values(SKEMA));
      let K = {}, DATA = [], MIGRASI = false, AKTIF = null, kotor = false;

      async function api(url, opt) {
        const r = await fetch(url, opt);
        const j = await r.json().catch(() => ({ status: 'error', message: 'Respon server tidak valid.' }));
        if (!r.ok || j.status !== 'success') { const e = new Error(j.message || 'Terjadi kesalahan.'); e.field = j.field; throw e; }
        return j;
      }

      /* ================= RIWAYAT ================= */
      function muat() {
        return api(API).then(res => {
          DATA = res.data || [];
          K = res.kunjungan || {};
          MIGRASI = !!res.migrasi;
          $id('gzMigrasi').classList.toggle('d-none', MIGRASI);
          $id('gzBatal').classList.toggle('d-none', !K.batal);
          $id('gzBtnBaru').disabled = !MIGRASI || K.batal;
          renderRiwayat();
        }).catch(e => { $id('gzRiwayat').innerHTML = `<tr><td colspan="7" class="kosong text-danger">${esc(e.message)}</td></tr>`; });
      }
      function renderRiwayat() {
        if (!DATA.length) {
          $id('gzRiwayat').innerHTML = `<tr><td colspan="7" class="kosong"><i class="ti ti-salad me-1"></i>Belum ada asesmen gizi untuk kunjungan ini.${MIGRASI && !K.batal ? ' Klik <b>Asesmen baru</b> untuk memulai.' : ''}</td></tr>`;
          return;
        }
        $id('gzRiwayat').innerHTML = DATA.map(r => {
          const cetak = 'module/admin/print/formulir_gizi?' + new URLSearchParams({ no: NO, rm: RM, id: r.id_gizi });
          return `<tr class="${AKTIF && +AKTIF.id_gizi === +r.id_gizi ? 'aktif' : ''}">
            <td><div class="aksi d-flex gap-1">
              <button type="button" class="btn btn-sm btn-primary" data-buka="${r.id_gizi}" title="Buka / ubah"><i class="ti ti-pencil"></i></button>
              <a class="btn btn-sm btn-outline-dark" href="${esc(cetak)}" target="_blank" rel="noopener" title="Cetak"><i class="ti ti-printer"></i></a>
              ${r.status === 'Draft' ? `<button type="button" class="btn btn-sm btn-danger" data-hapus="${r.id_gizi}" title="Hapus draft"><i class="ti ti-trash"></i></button>` : ''}
            </div></td>
            <td class="text-nowrap fw-semibold">${esc(tglId(r.tanggal_asesmen))}</td>
            <td>${esc(r.nutrisionis || '-')}</td>
            <td>${r.risiko_gizi ? `<span class="tag tg-${esc(r.risiko_gizi.toLowerCase())}">${esc(r.risiko_gizi)}</span>` : '<span class="text-muted">-</span>'}</td>
            <td style="max-width:360px">${r.diagnosis_pes ? esc(r.diagnosis_pes) : '<span class="text-muted">-</span>'}</td>
            <td>${esc(r.jenis_diet || '-')}</td>
            <td><span class="tag ${r.status === 'Selesai' ? 'tg-selesai' : 'tg-draft'}">${esc(r.status)}</span></td>
          </tr>`;
        }).join('');
      }

      /* ================= FORM: isi / baca ================= */
      const form = $id('gzForm');
      function barisTabel(key, r = {}) {
        const kol = FIELDS[key].kolom;
        const tr = document.createElement('tr');
        tr.innerHTML = `<td><button type="button" class="btn btn-sm btn-outline-danger" data-hapus-baris title="Hapus baris"><i class="ti ti-x"></i></button></td>` +
          Object.entries(kol).map(([ck, c]) => {
            const v = esc(r[ck] ?? '');
            if (c[1] === 'select') return `<td><select class="form-select form-select-sm" data-col="${ck}"><option value="">—</option>${c[2].map(o => `<option${o === r[ck] ? ' selected' : ''}>${esc(o)}</option>`).join('')}</select></td>`;
            return `<td><input type="${c[1]}" class="form-control form-control-sm" data-col="${ck}" value="${v}" placeholder="${esc(c[2] || '')}" maxlength="255"></td>`;
          }).join('');
        form.querySelector(`[data-tabel="${key}"]`).appendChild(tr);
      }
      function isi(d) {
        Object.entries(FIELDS).forEach(([key, def]) => {
          const v = d[key];
          if (['date', 'text', 'textarea', 'number', 'select'].includes(def.tipe)) form.querySelector(`[data-f="${key}"]`).value = v ?? '';
          else if (def.tipe === 'radio') form.querySelectorAll(`[name="r_${key}"]`).forEach(r => r.checked = r.value === v);
          else if (def.tipe === 'checks') form.querySelectorAll(`[data-checks="${key}"] input`).forEach(c => c.checked = Array.isArray(v) && v.includes(c.value));
          else if (def.tipe === 'lab') LAB.forEach(lk => ['nilai', 'tanggal', 'keterangan'].forEach(f => { form.querySelector(`[data-lab="${lk}"][data-lk="${f}"]`).value = (v && v[lk] && v[lk][f]) || ''; }));
          else if (def.tipe === 'tabel') {
            form.querySelector(`[data-tabel="${key}"]`).innerHTML = '';
            (Array.isArray(v) && v.length ? v : [{}]).forEach(r => barisTabel(key, r));
          }
        });
        hitung();
      }
      function baca() {
        const d = {};
        Object.entries(FIELDS).forEach(([key, def]) => {
          if (['date', 'text', 'textarea', 'number', 'select'].includes(def.tipe)) d[key] = form.querySelector(`[data-f="${key}"]`).value.trim();
          else if (def.tipe === 'radio') d[key] = (form.querySelector(`[name="r_${key}"]:checked`) || {}).value || '';
          else if (def.tipe === 'checks') d[key] = [...form.querySelectorAll(`[data-checks="${key}"] input:checked`)].map(c => c.value);
          else if (def.tipe === 'lab') { d.lab = {}; LAB.forEach(lk => { d.lab[lk] = {}; ['nilai', 'tanggal', 'keterangan'].forEach(f => d.lab[lk][f] = form.querySelector(`[data-lab="${lk}"][data-lk="${f}"]`).value.trim()); }); }
          else if (def.tipe === 'tabel') d[key] = [...form.querySelectorAll(`[data-tabel="${key}"] tr`)].map(tr => Object.fromEntries([...tr.querySelectorAll('[data-col]')].map(el => [el.dataset.col, el.value.trim()])));
        });
        return d;
      }

      /* ================= HITUNG OTOMATIS (sama dengan giziHitung() di server) ================= */
      const nilai = k => { const v = parseFloat(String(form.querySelector(`[data-f="${k}"]`).value).replace(',', '.')); return isFinite(v) && v > 0 ? v : null; };
      const setCalc = (k, v) => { form.querySelector(`[data-calc="${k}"]`).value = v; };
      function kategoriImt(imt) {
        if (imt === null) return '';
        if (K.umur !== null && K.umur !== undefined && K.umur < 18) return 'Anak: gunakan IMT/U';
        return imt < 17 ? 'Kurus berat' : imt < 18.5 ? 'Kurus ringan' : imt <= 25 ? 'Normal' : imt <= 27 ? 'Gemuk ringan' : 'Obesitas';
      }
      function hitung() {
        const bb = nilai('bb'), tb = nilai('tb'), biasa = nilai('bb_biasa');
        const imt = bb && tb ? Math.round(bb / Math.pow(tb / 100, 2) * 10) / 10 : null;
        setCalc('imt', imt === null ? '' : imt);
        setCalc('kategori_imt', kategoriImt(imt));
        if (tb && tb > 100) {
          const wanita = /^p/i.test(K.jk || '');
          const tanpaPotong = (wanita && tb < 150) || (!wanita && tb < 160);
          setCalc('bb_ideal', Math.round((tb - 100) * (tanpaPotong ? 1 : 0.9) * 10) / 10);
        } else setCalc('bb_ideal', '');
        if (bb && biasa) {
          const s = Math.round((bb - biasa) * 10) / 10;
          setCalc('perubahan_bb', (s > 0 ? '+' : '') + s);
          setCalc('persen_perubahan_bb', (s > 0 ? '+' : '') + Math.round(s / biasa * 1000) / 10);
        } else { setCalc('perubahan_bb', ''); setCalc('persen_perubahan_bb', ''); }
        const t = k => form.querySelector(`[data-f="${k}"]`).value.trim();
        const p = t('diagnosis_problem'), e = t('diagnosis_etiologi'), s = t('diagnosis_tanda');
        setCalc('diagnosis_pes', p ? p + (e ? ' berkaitan dengan ' + e : '') + (s ? ' dibuktikan dengan ' + s : '') : '');
      }
      form.addEventListener('input', e => {
        kotor = true;
        if (e.target.matches('[data-f="bb"],[data-f="tb"],[data-f="bb_biasa"],[data-f="diagnosis_problem"],[data-f="diagnosis_etiologi"],[data-f="diagnosis_tanda"]')) hitung();
        if (e.target.classList.contains('is-invalid')) e.target.classList.remove('is-invalid');
      });
      form.addEventListener('change', () => { kotor = true; });

      form.addEventListener('click', e => {
        const t = e.target.closest('[data-tambah-baris]'), h = e.target.closest('[data-hapus-baris]');
        if (t) { barisTabel(t.dataset.tambahBaris); kotor = true; }
        if (h) {
          const tb = h.closest('tbody');
          if (tb.children.length <= 1) h.closest('tr').querySelectorAll('[data-col]').forEach(el => el.value = '');   // baris terakhir dikosongkan (termasuk select)
          else h.closest('tr').remove();
          kotor = true;
        }
      });

      function aturCetak() {
        const b = $id('gzBtnCetak'), ada = AKTIF && AKTIF.id_gizi;
        b.classList.toggle('disabled', !ada);
        b.setAttribute('aria-disabled', ada ? 'false' : 'true');
        b.title = ada ? (kotor ? 'Mencetak versi yang terakhir disimpan' : 'Cetak asesmen ini') : 'Simpan dulu untuk mencetak';
        if (ada) b.href = 'module/admin/print/formulir_gizi?' + new URLSearchParams({ no: NO, rm: RM, id: AKTIF.id_gizi });
        else b.removeAttribute('href');
      }

      /* ================= BUKA / BARU ================= */
      function tampilkanForm(judul, status) {
        $id('gzJudul').textContent = judul;
        $id('gzStatus').textContent = status;
        $id('gzStatus').className = 'tag ' + (status === 'Selesai' ? 'tg-selesai' : 'tg-draft');
        form.classList.remove('d-none');
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
        kotor = false;
        renderRiwayat();
        aturCetak();
        window.scrollTo({ top: form.getBoundingClientRect().top + window.scrollY - 90, behavior: 'smooth' });
      }
      async function konfirmasiBuang() {
        if (!kotor || form.classList.contains('d-none')) return true;
        const r = await Swal.fire({ icon: 'question', title: 'Buang perubahan yang belum disimpan?', showCancelButton: true, confirmButtonText: 'Buang', cancelButtonText: 'Batal', confirmButtonColor: '#dc2626', cancelButtonColor: '#475569', reverseButtons: true });
        return r.isConfirmed;
      }
      $id('gzBtnBaru').addEventListener('click', async () => {
        if (!(await konfirmasiBuang())) return;
        AKTIF = null;
        // isian awal: tanggal hari ini, nutrisionis = pengguna, antropometri dari tanda vital kunjungan,
        // data latar (riwayat personal, alergi, BB biasa) dibawa dari asesmen terakhir bila ada
        const awal = { tanggal_asesmen: K.hari_ini, nutrisionis: K.pengguna || '', bb: K.bb || '', tb: K.tb || '', lingkar_pinggang: K.lingkar_pinggang || '' };
        if (DATA.length) {
          try {
            const last = (await api(API + '&id=' + DATA[0].id_gizi)).data.data || {};
            ['bb_biasa', 'pekerjaan', 'aktivitas_fisik', 'merokok', 'alergi_makanan', 'intoleransi_makanan', 'diet_sebelumnya', 'pantangan_makanan'].forEach(k => { if (last[k]) awal[k] = last[k]; });
            if (!awal.tb && last.tb) awal.tb = last.tb;
          } catch (e) { /* abaikan, mulai kosong */ }
        }
        isi(awal);
        tampilkanForm('Asesmen baru' + (DATA.length ? ' (lanjutan)' : ''), 'Draft');
      });
      $id('gzRiwayat').addEventListener('click', async e => {
        const b = e.target.closest('[data-buka]'), d = e.target.closest('[data-hapus]');
        if (b) {
          if (!(await konfirmasiBuang())) return;
          try {
            const res = await api(API + '&id=' + encodeURIComponent(b.dataset.buka));
            AKTIF = res.data;
            isi(Object.assign({}, res.data.data, { tanggal_asesmen: res.data.tanggal_asesmen, nutrisionis: res.data.nutrisionis }));
            tampilkanForm('Asesmen ' + tglId(res.data.tanggal_asesmen), res.data.status);
          } catch (err) { Swal.fire('Gagal', err.message, 'error'); }
        }
        if (d) {
          const r = DATA.find(x => +x.id_gizi === +d.dataset.hapus);
          Swal.fire({
            icon: 'warning', title: 'Hapus draft asesmen?', html: `Draft asesmen <b>${esc(tglId(r.tanggal_asesmen))}</b> akan dihapus.`,
            showCancelButton: true, confirmButtonText: 'Hapus', cancelButtonText: 'Batal', confirmButtonColor: '#dc2626', cancelButtonColor: '#475569', reverseButtons: true
          }).then(x => {
            if (!x.isConfirmed) return;
            api(API + '&id=' + encodeURIComponent(r.id_gizi), { method: 'DELETE' }).then(res => {
              toast(res.message);
              if (AKTIF && +AKTIF.id_gizi === +r.id_gizi) { AKTIF = null; form.classList.add('d-none'); kotor = false; }
              muat();
            }).catch(err => Swal.fire('Gagal', err.message, 'error'));
          });
        }
      });
      $id('gzBtnTutup').addEventListener('click', async () => {
        if (!(await konfirmasiBuang())) return;
        form.classList.add('d-none');
        AKTIF = null;
        kotor = false;
        renderRiwayat();
      });

      /* ================= SIMPAN ================= */
      function tandai(field, pesan) {
        const el = $id('f_' + field) || form.querySelector(`[data-f="${field}"]`);
        if (!el) return false;
        el.classList.add('is-invalid');
        const kol = el.closest('[class*="col-"]') || el.closest('td') || el.parentElement;
        const fb = kol && kol.querySelector('.invalid-feedback');
        if (fb) fb.textContent = pesan;
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        if (el.focus) el.focus({ preventScroll: true });
        return true;
      }
      let menyimpan = false;
      async function simpan(status) {
        if (menyimpan) return;
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
        const d = baca();
        if (!d.tanggal_asesmen) return tandai('tanggal_asesmen', 'Tanggal asesmen wajib diisi.');
        if (!d.nutrisionis) return tandai('nutrisionis', 'Nama nutrisionis wajib diisi.');
        menyimpan = true;
        const btn = $id(status === 'Selesai' ? 'gzBtnSelesai' : 'gzBtnDraft'), label = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan…';
        try {
          const res = await api(API, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id_gizi: AKTIF ? AKTIF.id_gizi : null, status, data: d }) });
          AKTIF = { id_gizi: res.id };
          kotor = false;
          aturCetak();
          toast(res.message);
          $id('gzStatus').textContent = status;
          $id('gzStatus').className = 'tag ' + (status === 'Selesai' ? 'tg-selesai' : 'tg-draft');
          $id('gzJudul').textContent = 'Asesmen ' + tglId(d.tanggal_asesmen);
          muat();
        } catch (err) {
          if (!(err.field && tandai(err.field, err.message))) Swal.fire('Gagal', err.message, 'error');
        } finally {
          menyimpan = false;
          btn.disabled = false;
          btn.innerHTML = label;
        }
      }
      $id('gzBtnDraft').addEventListener('click', () => simpan('Draft'));
      $id('gzBtnSelesai').addEventListener('click', () => simpan('Selesai'));
      window.addEventListener('beforeunload', e => { if (kotor && !form.classList.contains('d-none')) { e.preventDefault(); e.returnValue = ''; } });

      muat();
    })();
  </script>
</body>

</html>
