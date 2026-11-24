<?php
/**
 * CPPT (Catatan Perkembangan Pasien Terintegrasi) per kunjungan — format SOAP + instruksi, verifikasi oleh dokter/admin.
 * Perbaikan: query pasien dirangkai dari URL (SQL injection) tanpa batas faskes; $_GET no/rm/rme tanpa default; id
 * kunjungan dicetak mentah ke input (XSS); isi SOAP/instruksi/nama ditampilkan tanpa escape; footer DataTables sisa
 * salinan ("total bobot %") menjumlahkan kolom instruksi; tombol di dalam <a>; modal di luar </body>; kolom Aksi di
 * kanan; badge "Input Dokter" tidak muncul untuk data lama "Dokter" (huruf besar) sehingga tombol verifikasi tampil.
 */
$title = 'CPPT';
require '../../controller/view.php';
$no = trim((string) ($_GET['no'] ?? ''));
$rm = trim((string) ($_GET['rm'] ?? ''));
$rme = (string) ($_GET['rme'] ?? '');
$idc = (int) ($_SESSION['id_customer'] ?? 0);
$uidSesi = (string) ($_SESSION['uid_user'] ?? '');
$h = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');

$st = $koneksi->prepare("SELECT id_user, uid_user, fullname, roles FROM ms_users
   WHERE id_customer = ? AND roles NOT IN ('admin','superadmin') ORDER BY FIELD(roles,'dokter','perawat','bidan') DESC, fullname");
$st->bind_param('i', $idc);
$st->execute();
$petugas = $st->get_result()->fetch_all(MYSQLI_ASSOC);
$st->close();
$labelRole = ['dokter' => 'Dokter', 'perawat' => 'Perawat', 'bidan' => 'Bidan', 'apoteker' => 'Apoteker', 'analislab' => 'Analis Lab', 'receptionis' => 'Resepsionis', 'kasir' => 'Kasir'];
$grup = [];
foreach ($petugas as $p) $grup[strtolower($p['roles'])][] = $p;
$idSesi = '';
foreach ($petugas as $p) if ($uidSesi !== '' && (string) $p['uid_user'] === $uidSesi) $idSesi = (string) $p['id_user'];
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .cp .kepala { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-bottom: 14px; }
    .cp .kepala .ic { width: 42px; height: 42px; border-radius: 12px; background: #ede9fe; color: #5b21b6; display: inline-flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
    .cp .kepala h5 { margin: 0; font-weight: 700; color: #0f172a; }
    .cp .kepala small { color: #475569; }
    .cp .filter { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; margin-bottom: 12px; }
    .cp .chip { border: 1px solid #cbd5e1; background: #fff; color: #1f2937; border-radius: 999px; padding: 4px 12px; font-size: .8rem; font-weight: 600; }
    .cp .chip.on { background: #0f172a; border-color: #0f172a; color: #fff; }
    .cp .chip .n { font-weight: 700; opacity: .75; margin-left: 3px; }
    .cp table.t { width: 100%; border-collapse: collapse; font-size: .86rem; }
    .cp table.t th { font-size: .7rem; text-transform: uppercase; color: #475569; font-weight: 700; background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 7px 8px; white-space: nowrap; }
    .cp table.t td { border-bottom: 1px solid #eef2f6; padding: 10px 8px; vertical-align: top; color: #1f2937; }
    .cp table.t tr.tgl td { background: #f1f5f9; font-weight: 700; color: #0f172a; font-size: .78rem; padding: 5px 8px; }
    .cp .aksi { white-space: nowrap; }
    .cp .aksi .btn { padding: 3px 9px; font-weight: 600; }
    .cp .jam { font-weight: 700; color: #0f172a; white-space: nowrap; }
    .cp .ppa { font-weight: 600; color: #0f172a; }
    .cp .prof { font-size: .68rem; font-weight: 700; border-radius: 6px; padding: 1px 7px; display: inline-block; white-space: nowrap; }
    .cp .prof-dokter { background: #dbeafe; color: #1e3a8a; }
    .cp .prof-perawat { background: #dcfce7; color: #14532d; }
    .cp .prof-bidan { background: #fce7f3; color: #831843; }
    .cp .prof-lain { background: #f1f5f9; color: #334155; }
    .cp .soap { display: grid; grid-template-columns: 22px 1fr; gap: 3px 6px; min-width: 280px; }
    .cp .soap b { color: #5b21b6; }
    .cp .soap div { white-space: pre-line; word-break: break-word; }
    .cp .instr { white-space: pre-line; word-break: break-word; min-width: 160px; }
    .cp .st { font-size: .72rem; font-weight: 700; border-radius: 6px; padding: 2px 8px; white-space: nowrap; display: inline-block; }
    .cp .st-ok { background: #dcfce7; color: #14532d; }
    .cp .st-belum { background: #fef3c7; color: #92400e; }
    .cp .kunci { font-size: .72rem; color: #475569; }
    .cp .kosong { text-align: center; padding: 26px 10px; color: #475569; }
    .cp .kosong i { font-size: 2rem; color: #94a3b8; display: block; margin-bottom: 6px; }
    #cpModal .soap-lbl { font-weight: 700; }
    #cpModal .soap-lbl .hrf { display: inline-flex; width: 22px; height: 22px; border-radius: 6px; background: #ede9fe; color: #5b21b6; align-items: center; justify-content: center; margin-right: 6px; font-size: .8rem; }
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
                <div class="card-body p-4 cp">
                  <div class="kepala">
                    <span class="ic"><i class="ti ti-notes"></i></span>
                    <div class="flex-grow-1">
                      <h5>CPPT</h5>
                      <small>Catatan Perkembangan Pasien Terintegrasi — format SOAP, diverifikasi dokter.</small>
                    </div>
                    <a class="btn btn-outline-dark" target="_blank" rel="noopener" href="module/admin/print/formulir_cppt?<?= $h(http_build_query(['no' => $no, 'rm' => $rm])) ?>"><i class="ti ti-printer me-1"></i>Cetak</a>
                    <button type="button" class="btn btn-primary" id="btnTambah" disabled><i class="ti ti-plus me-1"></i>Tambah CPPT</button>
                  </div>
                  <div class="alert alert-danger d-none" id="cpBatal"><i class="ti ti-ban me-1"></i>Kunjungan ini sudah dibatalkan — CPPT baru tidak bisa ditambahkan.</div>
                  <div class="filter" id="cpFilter"></div>
                  <div class="table-responsive">
                    <table class="t">
                      <thead>
                        <tr>
                          <th>Aksi</th>
                          <th>Jam</th>
                          <th>PPA</th>
                          <th>SOAP</th>
                          <th>Instruksi</th>
                          <th>Verifikasi</th>
                        </tr>
                      </thead>
                      <tbody id="cpBody">
                        <tr><td colspan="6" class="kosong"><span class="spinner-border spinner-border-sm me-2"></span>Memuat…</td></tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL FORM CPPT -->
  <div class="modal fade" id="cpModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <form id="cpForm" class="modal-content" novalidate autocomplete="off">
        <div class="modal-header">
          <h5 class="modal-title" id="cpJudul">Tambah CPPT</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_cppt" id="id_cppt">
          <div class="row g-3 mb-1">
            <div class="col-md-3 col-6">
              <label for="cppt_date" class="form-label small fw-semibold">Tanggal <span class="text-danger">*</span></label>
              <input type="date" name="cppt_date" class="form-control" id="cppt_date">
              <div class="invalid-feedback"></div>
            </div>
            <div class="col-md-3 col-6">
              <label for="cppt_time" class="form-label small fw-semibold">Jam <span class="text-danger">*</span></label>
              <input type="time" name="cppt_time" class="form-control" id="cppt_time">
              <div class="invalid-feedback"></div>
            </div>
            <div class="col-md-6">
              <label for="users_entry" class="form-label small fw-semibold">PPA (petugas) <span class="text-danger">*</span></label>
              <select name="users_entry" class="form-select" id="users_entry">
                <option value="">— Pilih petugas —</option>
                <?php foreach ($grup as $role => $list): ?>
                  <optgroup label="<?= $h($labelRole[$role] ?? ucfirst($role)) ?>">
                    <?php foreach ($list as $p): ?>
                      <option value="<?= (int) $p['id_user'] ?>" data-role="<?= $h($role) ?>"><?= $h($p['fullname']) ?></option>
                    <?php endforeach; ?>
                  </optgroup>
                <?php endforeach; ?>
              </select>
              <div class="invalid-feedback"></div>
            </div>
          </div>
          <div class="small text-muted mb-3">Isi minimal salah satu dari S, O, A, atau P.</div>
          <div class="row g-3">
            <div class="col-md-6">
              <label for="subjective" class="form-label soap-lbl"><span class="hrf">S</span>Subjective</label>
              <textarea name="subjective" class="form-control" id="subjective" rows="4" placeholder="Keluhan pasien, mis. nyeri kepala sejak 2 hari, mual"></textarea>
              <div class="invalid-feedback"></div>
            </div>
            <div class="col-md-6">
              <label for="objective" class="form-label soap-lbl"><span class="hrf">O</span>Objective</label>
              <textarea name="objective" class="form-control" id="objective" rows="4" placeholder="Pemeriksaan fisik & tanda vital, mis. TD 120/80 mmHg, N 80x/mnt, S 37°C"></textarea>
            </div>
            <div class="col-md-6">
              <label for="analysis" class="form-label soap-lbl"><span class="hrf">A</span>Assessment</label>
              <textarea name="analysis" class="form-control" id="analysis" rows="4" placeholder="Diagnosis kerja / masalah, mis. gastritis akut"></textarea>
            </div>
            <div class="col-md-6">
              <label for="planning" class="form-label soap-lbl"><span class="hrf">P</span>Planning</label>
              <textarea name="planning" class="form-control" id="planning" rows="4" placeholder="Terapi, pemeriksaan, edukasi, kontrol"></textarea>
            </div>
            <div class="col-12">
              <label for="instruction" class="form-label soap-lbl"><span class="hrf"><i class="ti ti-clipboard-list"></i></span>Instruksi</label>
              <textarea name="instruction" class="form-control" id="instruction" rows="3" placeholder="Instruksi tindak lanjut, mis. observasi TTV tiap 4 jam"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary px-4" id="btnSimpan"><i class="ti ti-device-floppy me-1"></i>Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <?php require 'library.php'; ?>
  <script>
    (function() {
      'use strict';
      const API = 'controller/visit/cpptController?no=' + encodeURIComponent(<?= json_encode($no) ?>);
      const ID_SESI = <?= json_encode($idSesi) ?>;
      const $id = i => document.getElementById(i);
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const HARI = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
      const BLN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      const tglPanjang = s => { const m = String(s || '').match(/^(\d{4})-(\d{2})-(\d{2})/); if (!m) return '-'; const d = new Date(Date.UTC(+m[1], m[2] - 1, +m[3])); return HARI[d.getUTCDay()] + ', ' + (+m[3]) + ' ' + BLN[m[2] - 1] + ' ' + m[1]; };
      const sekarang = () => { const p = new Date().toLocaleString('sv-SE', { timeZone: 'Asia/Jakarta' }); return { tgl: p.slice(0, 10), jam: p.slice(11, 16) }; };
      const LABEL = { dokter: 'Dokter', perawat: 'Perawat', bidan: 'Bidan', apoteker: 'Apoteker', analislab: 'Analis Lab', receptionis: 'Resepsionis', kasir: 'Kasir' };
      const profKelas = p => ['dokter', 'perawat', 'bidan'].includes(p) ? 'prof-' + p : 'prof-lain';
      const toast = (title, icon = 'success') => Swal.fire({ toast: true, position: 'top-end', icon, title, showConfirmButton: false, timer: 2200 });

      let DATA = [], AKSES = { verifikasi: false }, BATAL = true, filter = 'semua';

      async function api(url, opt) {
        const r = await fetch(url, opt);
        const j = await r.json().catch(() => ({ status: 'error', message: 'Respon server tidak valid.' }));
        if (!r.ok || j.status !== 'success') { const e = new Error(j.message || 'Terjadi kesalahan.'); e.field = j.field; throw e; }
        return j;
      }

      /* ================= DAFTAR ================= */
      function muat() {
        api(API).then(res => {
          DATA = res.data || [];
          AKSES = res.akses || {};
          BATAL = !!(res.kunjungan && res.kunjungan.batal);
          $id('btnTambah').disabled = BATAL;
          $id('cpBatal').classList.toggle('d-none', !BATAL);
          render();
        }).catch(e => {
          $id('cpBody').innerHTML = `<tr><td colspan="6" class="kosong text-danger"><i class="ti ti-alert-triangle"></i>${esc(e.message)}</td></tr>`;
        });
      }

      function render() {
        const grupProf = p => ['dokter', 'perawat', 'bidan'].includes(p) ? p : 'lain';
        const n = k => k === 'semua' ? DATA.length : k === 'belum' ? DATA.filter(r => !r.verifikasi && r.profesi !== 'dokter').length : DATA.filter(r => grupProf(r.profesi) === k).length;
        const chips = [['semua', 'Semua'], ['dokter', 'Dokter'], ['perawat', 'Perawat'], ['bidan', 'Bidan'], ['lain', 'PPA lain'], ['belum', 'Belum diverifikasi']];
        $id('cpFilter').innerHTML = chips.filter(([k]) => k === 'semua' || n(k) > 0)
          .map(([k, l]) => `<button type="button" class="chip${filter === k ? ' on' : ''}" data-filter="${k}">${l}<span class="n">${n(k)}</span></button>`).join('');

        const rows = DATA.filter(r => filter === 'semua' || (filter === 'belum' ? !r.verifikasi && r.profesi !== 'dokter' : grupProf(r.profesi) === filter));
        if (!rows.length) {
          $id('cpBody').innerHTML = `<tr><td colspan="6" class="kosong"><i class="ti ti-notes"></i>${DATA.length ? 'Tidak ada CPPT untuk filter ini.' : 'Belum ada CPPT untuk kunjungan ini.'}</td></tr>`;
          return;
        }
        let tglAkhir = '';
        $id('cpBody').innerHTML = rows.map(r => {
          let head = '';
          if (r.cppt_date !== tglAkhir) { tglAkhir = r.cppt_date; head = `<tr class="tgl"><td colspan="6"><i class="ti ti-calendar me-1"></i>${esc(tglPanjang(r.cppt_date))}</td></tr>`; }
          const soap = [['S', r.subjective], ['O', r.objective], ['A', r.analysis], ['P', r.planning]]
            .filter(([, v]) => v && v.trim()).map(([k, v]) => `<b>${k}</b><div>${esc(v)}</div>`).join('');
          // aturan lama: perawat/bidan/dll. tidak melihat tombol aksi catatan dokter
          const aksi = r.kunci
            ? `<span class="kunci" title="${esc(r.kunci)}"><i class="ti ti-lock me-1"></i>Terkunci</span>`
            : `<div class="aksi d-flex gap-1">
                <button type="button" class="btn btn-sm btn-warning" data-ubah="${r.id_cppt}" title="Ubah"><i class="ti ti-pencil"></i></button>
                <button type="button" class="btn btn-sm btn-danger" data-hapus="${r.id_cppt}" title="Hapus"><i class="ti ti-trash"></i></button></div>`;
          let ver;
          if (r.verifikasi) {
            ver = `<span class="st st-ok"><i class="ti ti-circle-check me-1"></i>Terverifikasi</span>` +
              (AKSES.verifikasi ? `<div><a href="javascript:;" class="small text-danger" data-unverify="${r.id_cppt}">Batalkan</a></div>` : '');
          } else if (r.profesi === 'dokter') {
            ver = '<span class="st prof-dokter"><i class="ti ti-stethoscope me-1"></i>Input dokter</span>';
          } else {
            ver = '<span class="st st-belum">Belum diverifikasi</span>' +
              (AKSES.verifikasi ? `<div class="mt-1"><button type="button" class="btn btn-sm btn-success" data-verify="${r.id_cppt}"><i class="ti ti-check me-1"></i>Verifikasi</button></div>` : '');
          }
          return head + `<tr>
            <td>${aksi}</td>
            <td class="jam">${esc(String(r.cppt_time || '').slice(0, 5) || '-')}</td>
            <td><div class="ppa">${esc(r.fullname || '-')}</div><span class="prof ${profKelas(r.profesi)}">${esc(LABEL[r.profesi] || r.profesi || '-')}</span></td>
            <td>${soap ? `<div class="soap">${soap}</div>` : '<span class="text-muted">-</span>'}</td>
            <td><div class="instr">${r.instruction && r.instruction.trim() ? esc(r.instruction) : '<span class="text-muted">-</span>'}</div></td>
            <td>${ver}</td>
          </tr>`;
        }).join('');
      }
      $id('cpFilter').addEventListener('click', e => { const b = e.target.closest('[data-filter]'); if (b) { filter = b.dataset.filter; render(); } });

      /* ================= FORM ================= */
      const modal = bootstrap.Modal.getOrCreateInstance($id('cpModal'));
      const form = $id('cpForm');
      function bersihkan() {
        form.querySelectorAll('.is-invalid').forEach(e => e.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(e => e.textContent = '');
      }
      function tandai(field, pesan) {
        const el = $id(field);
        if (!el) return false;
        el.classList.add('is-invalid');
        const fb = el.parentElement.querySelector('.invalid-feedback');
        if (fb) fb.textContent = pesan;
        el.focus();
        return true;
      }
      function pilihPetugas(id) {
        const sel = $id('users_entry');
        if (id && !sel.querySelector(`option[value="${CSS.escape(String(id))}"]`)) {
          sel.add(new Option('(petugas tidak aktif / bukan PPA) #' + id, id));   // data lama tetap tampil
        }
        sel.value = id || '';
      }
      function bukaForm(r) {
        form.reset();
        bersihkan();
        const now = sekarang();
        $id('cppt_date').max = now.tgl;
        if (r) {
          $id('cpJudul').textContent = 'Ubah CPPT';
          $id('id_cppt').value = r.id_cppt;
          ['cppt_date', 'subjective', 'objective', 'analysis', 'planning', 'instruction'].forEach(k => $id(k).value = r[k] ?? '');
          $id('cppt_time').value = String(r.cppt_time || '').slice(0, 5);
          pilihPetugas(r.users_entry);
        } else {
          $id('cpJudul').textContent = 'Tambah CPPT';
          $id('id_cppt').value = '';
          $id('cppt_date').value = now.tgl;
          $id('cppt_time').value = now.jam;
          pilihPetugas(ID_SESI);
        }
        modal.show();
        setTimeout(() => $id(r ? 'subjective' : ($id('users_entry').value ? 'subjective' : 'users_entry')).focus(), 350);
      }
      $id('btnTambah').addEventListener('click', () => bukaForm(null));

      let menyimpan = false;
      form.addEventListener('submit', function(e) {
        e.preventDefault();
        if (menyimpan) return;
        bersihkan();
        if (!$id('cppt_date').value) return tandai('cppt_date', 'Tanggal wajib diisi.');
        if (!$id('cppt_time').value) return tandai('cppt_time', 'Jam wajib diisi.');
        if (!$id('users_entry').value) return tandai('users_entry', 'Pilih petugas.');
        if (!['subjective', 'objective', 'analysis', 'planning'].some(k => $id(k).value.trim())) return tandai('subjective', 'Isi minimal salah satu dari S, O, A, atau P.');
        const id = $id('id_cppt').value;
        menyimpan = true;
        const btn = $id('btnSimpan'), label = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan…';
        api(API, { method: id ? 'PUT' : 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: new URLSearchParams(new FormData(form)) })
          .then(res => { modal.hide(); toast(res.message); muat(); })
          .catch(err => { if (!(err.field && tandai(err.field, err.message))) Swal.fire('Gagal', err.message, 'error'); })
          .finally(() => { menyimpan = false; btn.disabled = false; btn.innerHTML = label; });
      });

      /* ================= AKSI ================= */
      $id('cpBody').addEventListener('click', function(e) {
        const ubah = e.target.closest('[data-ubah]'), hapus = e.target.closest('[data-hapus]');
        const ver = e.target.closest('[data-verify]'), unver = e.target.closest('[data-unverify]');
        const cari = id => DATA.find(r => String(r.id_cppt) === String(id));
        if (ubah) bukaForm(cari(ubah.dataset.ubah));
        if (hapus) {
          const r = cari(hapus.dataset.hapus);
          Swal.fire({
            icon: 'warning', title: 'Hapus CPPT?', html: `CPPT <b>${esc(tglPanjang(r.cppt_date))} ${esc(String(r.cppt_time).slice(0, 5))}</b> oleh <b>${esc(r.fullname || '-')}</b> akan dihapus.`,
            showCancelButton: true, confirmButtonText: 'Hapus', cancelButtonText: 'Batal', confirmButtonColor: '#dc2626', cancelButtonColor: '#475569', reverseButtons: true
          }).then(x => {
            if (!x.isConfirmed) return;
            api(API + '&id=' + encodeURIComponent(r.id_cppt), { method: 'DELETE' }).then(res => { toast(res.message); muat(); }).catch(err => Swal.fire('Gagal', err.message, 'error'));
          });
        }
        if (ver || unver) {
          const batal = !!unver, id = (ver || unver).dataset[batal ? 'unverify' : 'verify'];
          Swal.fire({
            icon: batal ? 'warning' : 'question', title: batal ? 'Batalkan verifikasi?' : 'Verifikasi CPPT ini?',
            text: batal ? 'Catatan akan bisa diubah/dihapus kembali.' : 'Setelah diverifikasi, catatan terkunci dari perubahan.',
            showCancelButton: true, confirmButtonText: batal ? 'Batalkan verifikasi' : 'Ya, verifikasi', cancelButtonText: 'Batal',
            confirmButtonColor: batal ? '#dc2626' : '#16a34a', cancelButtonColor: '#475569', reverseButtons: true
          }).then(x => {
            if (!x.isConfirmed) return;
            api(API + (batal ? '&batal_verify=' : '&verify=') + encodeURIComponent(id), { method: 'POST' }).then(res => { toast(res.message); muat(); }).catch(err => Swal.fire('Gagal', err.message, 'error'));
          });
        }
      });

      muat();
    })();
  </script>
</body>

</html>
