<?php
/**
 * Instruksi Dokter / perjalanan penyakit rawat inap. API: controller/ranap/instruksiController.php. Cetak: print/formulir_instruksi.
 * Perbaikan: query pasien dirangkai dari URL (SQL injection) & tidak dipakai; $_GET no/rm/rme tanpa default; id kunjungan &
 * no RM dicetak mentah ke input (XSS); isi catatan ditampilkan tanpa escape; petugas bisa diketik bebas; URL ubah
 * memakai "?" ganda; teks bantuan tiap kolom tertukar (mis. "Pemeriksaan Fisik" berisi contoh rencana terapi);
 * footer DataTables sisa salinan ("total bobot %"); tombol di dalam <a>; modal di luar </body>; Aksi di kanan.
 */
$title = 'Instruksi Dokter';
require '../../controller/view.php';
$no = trim((string) ($_GET['no'] ?? ''));
$rm = trim((string) ($_GET['rm'] ?? ''));
$rme = (string) ($_GET['rme'] ?? '');
$h = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
$pengguna = (string) ($_SESSION['fullname'] ?? '');
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .ins .kepala { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-bottom: 14px; }
    .ins .kepala .ic { width: 42px; height: 42px; border-radius: 12px; background: #dbeafe; color: #1e3a8a; display: inline-flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
    .ins .kepala h5 { margin: 0; font-weight: 700; color: #0f172a; }
    .ins .kepala small { color: #1f2937; }
    .ins .hari { display: flex; align-items: center; gap: 8px; font-weight: 700; color: #0f172a; margin: 18px 0 8px; font-size: .9rem; }
    .ins .hari:first-child { margin-top: 0; }
    .ins .hari .n { font-size: .72rem; background: #f1f5f9; color: #334155; border-radius: 999px; padding: 1px 8px; }
    .ins .catat { border: 1px solid #e2e8f0; border-radius: 14px; margin-bottom: 12px; background: #fff; }
    .ins .catat-h { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; padding: 9px 14px; border-bottom: 1px solid #eef2f6; background: #f8fafc; border-radius: 14px 14px 0 0; }
    .ins .catat-h .aksi { display: flex; gap: 4px; }
    .ins .catat-h .aksi .btn { padding: 3px 9px; font-weight: 600; }
    .ins .catat-h .jam { font-weight: 800; color: #0f172a; }
    .ins .catat-h .dr { color: #1f2937; font-size: .86rem; }
    .ins .catat-b { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px 18px; padding: 12px 14px; }
    @media (max-width: 767.98px) { .ins .catat-b { grid-template-columns: 1fr; } }
    .ins .blok small { display: block; font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #1e3a8a; margin-bottom: 2px; }
    .ins .blok div { white-space: pre-line; color: #1f2937; font-size: .88rem; word-break: break-word; }
    .ins .blok.utama div { font-weight: 600; color: #0f172a; }
    .ins .kosong { text-align: center; padding: 34px 10px; color: #1f2937; border: 1px dashed #cbd5e1; border-radius: 12px; }
    .ins .kosong i { font-size: 2rem; color: #64748b; display: block; margin-bottom: 6px; }
    #insModal .form-label { font-size: .82rem; font-weight: 600; color: #1f2937; }
    #insModal .form-text { color: #334155; }
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
                <div class="card-body p-4 ins">
                  <div class="kepala">
                    <span class="ic"><i class="ti ti-file-text"></i></span>
                    <div class="flex-grow-1">
                      <h5>Instruksi Dokter <span class="small text-muted fw-normal" id="insJumlah"></span></h5>
                      <small>Perjalanan penyakit &amp; instruksi harian dokter selama rawat inap, berurutan per tanggal.</small>
                    </div>
                    <a class="btn btn-outline-dark" target="_blank" rel="noopener" href="module/admin/print/formulir_instruksi?<?= $h(http_build_query(['no' => $no, 'rm' => $rm])) ?>"><i class="ti ti-printer me-1"></i>Cetak</a>
                    <button type="button" class="btn btn-primary" id="insTambah" disabled><i class="ti ti-plus me-1"></i>Tambah instruksi</button>
                  </div>
                  <div class="alert alert-danger d-none" id="insBatal"><i class="ti ti-ban me-1"></i>Kunjungan ini sudah dibatalkan — instruksi baru tidak bisa ditambahkan.</div>
                  <div id="insIsi"><div class="kosong"><span class="spinner-border spinner-border-sm me-2"></span>Memuat…</div></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL FORM -->
  <div class="modal fade" id="insModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <form id="insForm" class="modal-content" novalidate autocomplete="off">
        <div class="modal-header">
          <h5 class="modal-title" id="insJudul">Tambah instruksi</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_cppt" id="id_cppt">
          <input type="hidden" name="visit_ID" id="visit_ID" value="<?= $h($no) ?>">
          <input type="hidden" name="nomor_rm" id="nomor_rm" value="<?= $h($rm) ?>">
          <div class="row g-3 mb-1">
            <div class="col-md-3 col-6">
              <label for="cppt_date" class="form-label">Tanggal <span class="text-danger">*</span></label>
              <input type="date" name="cppt_date" class="form-control" id="cppt_date">
              <div class="invalid-feedback"></div>
            </div>
            <div class="col-md-3 col-6">
              <label for="cppt_time" class="form-label">Jam <span class="text-danger">*</span></label>
              <input type="time" name="cppt_time" class="form-control" id="cppt_time">
              <div class="invalid-feedback"></div>
            </div>
            <div class="col-md-4">
              <label for="users_entry" class="form-label">Dokter / petugas</label>
              <input type="text" value="<?= $h($pengguna) ?>" name="users_entry" class="form-control bg-light" id="users_entry" readonly tabindex="-1">
              <div class="form-text">Tercatat otomatis sesuai akun yang login.</div>
            </div>
            <div class="col-md-2 d-flex align-items-end">
              <button type="button" class="btn btn-outline-dark w-100" id="insSalin" title="Salin K/U, pemeriksaan fisik, diagnosa & pengobatan dari catatan terakhir"><i class="ti ti-copy me-1"></i>Salin terakhir</button>
            </div>
          </div>
          <div class="small text-muted mb-3">Isi minimal perjalanan penyakit atau instruksi dokter.</div>
          <div class="row g-3">
            <div class="col-md-6">
              <label for="perjalanan" class="form-label">Perjalanan penyakit</label>
              <textarea name="perjalanan" class="form-control" id="perjalanan" rows="4" placeholder="Keluhan & perkembangan gejala, mis. demam turun, mual berkurang, nafsu makan membaik"></textarea>
              <div class="invalid-feedback"></div>
            </div>
            <div class="col-md-6">
              <label for="tindakan" class="form-label">Instruksi dokter / permintaan &amp; pengobatan</label>
              <textarea name="tindakan" class="form-control" id="tindakan" rows="4" placeholder="Instruksi untuk perawat, mis. observasi TTV tiap 4 jam, cek darah rutin besok pagi, aff infus bila klinis baik"></textarea>
            </div>
            <div class="col-md-6">
              <label for="ku" class="form-label">K/U (keadaan umum)</label>
              <textarea name="ku" class="form-control" id="ku" rows="3" placeholder="Keadaan umum, kesadaran & tanda vital, mis. sedang, CM, TD 120/80, N 84, S 37,2"></textarea>
            </div>
            <div class="col-md-6">
              <label for="pemeriksaan_fisik" class="form-label">Pemeriksaan fisik</label>
              <textarea name="pemeriksaan_fisik" class="form-control" id="pemeriksaan_fisik" rows="3" placeholder="Temuan pemeriksaan fisik, mis. abdomen: nyeri tekan epigastrium berkurang"></textarea>
            </div>
            <div class="col-md-6">
              <label for="diagnosa" class="form-label">Diagnosa</label>
              <textarea name="diagnosa" class="form-control" id="diagnosa" rows="2" placeholder="Diagnosa kerja, mis. Dispepsia, Febris H-3"></textarea>
            </div>
            <div class="col-md-6">
              <label for="pengobatan" class="form-label">Pengobatan</label>
              <textarea name="pengobatan" class="form-control" id="pengobatan" rows="2" placeholder="Terapi, mis. IVFD RL 20 tpm, inj. ranitidin 2x1 amp, PCT 3x500 mg"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary px-4" id="insSimpan"><i class="ti ti-device-floppy me-1"></i>Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <?php require 'library.php'; ?>
  <script>
    (function() {
      'use strict';
      const API = 'controller/ranap/instruksiController?no=' + encodeURIComponent(<?= json_encode($no) ?>);
      const $id = i => document.getElementById(i);
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const HARI = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
      const BLN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      const tglPanjang = s => { const m = String(s || '').match(/^(\d{4})-(\d{2})-(\d{2})/); if (!m) return '-'; const d = new Date(Date.UTC(+m[1], m[2] - 1, +m[3])); return HARI[d.getUTCDay()] + ', ' + (+m[3]) + ' ' + BLN[m[2] - 1] + ' ' + m[1]; };
      const sekarang = () => { const p = new Date().toLocaleString('sv-SE', { timeZone: 'Asia/Jakarta' }); return { tgl: p.slice(0, 10), jam: p.slice(11, 16) }; };
      const toast = (title, icon = 'success') => Swal.fire({ toast: true, position: 'top-end', icon, title, showConfirmButton: false, timer: 2200 });
      const BLOK = [['perjalanan', 'Perjalanan penyakit', true], ['tindakan', 'Instruksi dokter / permintaan & pengobatan', true], ['ku', 'K/U'], ['pemeriksaan_fisik', 'Pemeriksaan fisik'], ['diagnosa', 'Diagnosa'], ['pengobatan', 'Pengobatan']];
      let DATA = [], K = { batal: true };

      async function api(url, opt) {
        const r = await fetch(url, opt);
        const j = await r.json().catch(() => ({ status: 'error', message: 'Respon server tidak valid.' }));
        if (!r.ok || j.status !== 'success') { const e = new Error(j.message || 'Terjadi kesalahan.'); e.field = j.field; throw e; }
        return j;
      }

      function muat() {
        api(API).then(res => {
          DATA = res.data || [];
          K = res.kunjungan || {};
          $id('insTambah').disabled = !!K.batal;
          $id('insBatal').classList.toggle('d-none', !K.batal);
          render();
        }).catch(e => { $id('insIsi').innerHTML = `<div class="kosong text-danger"><i class="ti ti-alert-triangle"></i>${esc(e.message)}</div>`; });
      }
      function render() {
        $id('insJumlah').textContent = DATA.length ? '· ' + DATA.length + ' catatan' : '';
        if (!DATA.length) {
          $id('insIsi').innerHTML = '<div class="kosong"><i class="ti ti-file-text"></i>Belum ada instruksi dokter untuk kunjungan ini.</div>';
          return;
        }
        const grup = new Map();
        DATA.forEach((d, i) => { const k = d.cppt_date || ''; if (!grup.has(k)) grup.set(k, []); grup.get(k).push([d, i]); });
        $id('insIsi').innerHTML = [...grup].map(([tgl, list]) => `
          <div class="hari"><i class="ti ti-calendar"></i>${esc(tglPanjang(tgl))}<span class="n">${list.length} catatan</span></div>
          ${list.map(([d, i]) => `
            <div class="catat">
              <div class="catat-h">
                <div class="aksi">
                  <button type="button" class="btn btn-sm btn-warning" data-ubah="${i}" title="Ubah"><i class="ti ti-pencil"></i></button>
                  <button type="button" class="btn btn-sm btn-danger" data-hapus="${i}" title="Hapus"><i class="ti ti-trash"></i></button>
                </div>
                <span class="jam"><i class="ti ti-clock me-1"></i>${esc(String(d.cppt_time || '').slice(0, 5) || '-')}</span>
                <span class="dr"><i class="ti ti-stethoscope me-1"></i>${esc(d.users_entry || '-')}</span>
              </div>
              <div class="catat-b">
                ${BLOK.filter(([k]) => d[k] && String(d[k]).trim()).map(([k, l, utama]) => `<div class="blok${utama ? ' utama' : ''}"><small>${esc(l)}</small><div>${esc(d[k])}</div></div>`).join('') || '<div class="text-muted">-</div>'}
              </div>
            </div>`).join('')}`).join('');
      }

      /* ---------- form ---------- */
      const modal = bootstrap.Modal.getOrCreateInstance($id('insModal'));
      const form = $id('insForm');
      const FIELD = ['cppt_date', 'cppt_time', 'perjalanan', 'tindakan', 'ku', 'pemeriksaan_fisik', 'diagnosa', 'pengobatan'];
      function bersihkan() { form.querySelectorAll('.is-invalid').forEach(e => e.classList.remove('is-invalid')); form.querySelectorAll('.invalid-feedback').forEach(e => e.textContent = ''); }
      function tandai(f, pesan) {
        const el = $id(f); if (!el) return false;
        el.classList.add('is-invalid');
        const fb = el.parentElement.querySelector('.invalid-feedback'); if (fb) fb.textContent = pesan;
        el.focus(); return true;
      }
      function buka(d) {
        form.reset(); bersihkan();
        const n = sekarang();
        $id('cppt_date').max = K.hari_ini || n.tgl;
        $id('cppt_date').min = K.tanggal || '';
        if (d) {
          $id('insJudul').textContent = 'Ubah instruksi';
          $id('id_cppt').value = d.id_cppt;
          FIELD.forEach(f => $id(f).value = d[f] ?? '');
          $id('cppt_time').value = String(d.cppt_time || '').slice(0, 5);
          $id('users_entry').value = d.users_entry || '';
        } else {
          $id('insJudul').textContent = 'Tambah instruksi';
          $id('id_cppt').value = '';
          $id('cppt_date').value = n.tgl;
          $id('cppt_time').value = n.jam;
          $id('users_entry').value = <?= json_encode($pengguna) ?>;
        }
        $id('insSalin').disabled = !DATA.length;
        modal.show();
        setTimeout(() => $id('perjalanan').focus(), 350);
      }
      $id('insTambah').addEventListener('click', () => buka(null));
      $id('insSalin').addEventListener('click', () => {
        if (!DATA.length) return;
        // tiap kolom diambil dari catatan terakhir yang berisi (catatan terbaru bisa saja hanya berisi instruksi)
        let n = 0;
        ['ku', 'pemeriksaan_fisik', 'diagnosa', 'pengobatan'].forEach(f => {
          const src = [...DATA].reverse().find(d => d[f] && String(d[f]).trim());
          if (src && !$id(f).value.trim()) { $id(f).value = src[f]; n++; }
        });
        toast(n ? 'K/U, pemeriksaan fisik, diagnosa & pengobatan terakhir disalin — sesuaikan bila ada perubahan.' : 'Tidak ada isian untuk disalin.', n ? 'info' : 'warning');
      });

      let menyimpan = false;
      form.addEventListener('submit', async e => {
        e.preventDefault();
        if (menyimpan) return;
        bersihkan();
        if (!$id('cppt_date').value) return tandai('cppt_date', 'Tanggal wajib diisi.');
        if (!$id('cppt_time').value) return tandai('cppt_time', 'Jam wajib diisi.');
        if (!$id('perjalanan').value.trim() && !$id('tindakan').value.trim()) return tandai('perjalanan', 'Isi perjalanan penyakit atau instruksi dokter.');
        const id = $id('id_cppt').value;
        menyimpan = true;
        const btn = $id('insSimpan'), label = btn.innerHTML;
        btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan…';
        try {
          const res = await api(API, { method: id ? 'PUT' : 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: new URLSearchParams(new FormData(form)) });
          modal.hide(); toast(res.message); muat();
        } catch (err) {
          if (!(err.field && tandai(err.field, err.message))) Swal.fire('Gagal', err.message, 'error');
        } finally { menyimpan = false; btn.disabled = false; btn.innerHTML = label; }
      });

      $id('insIsi').addEventListener('click', e => {
        const u = e.target.closest('[data-ubah]'), h = e.target.closest('[data-hapus]');
        if (u) buka(DATA[+u.dataset.ubah]);
        if (h) {
          const d = DATA[+h.dataset.hapus];
          Swal.fire({
            icon: 'warning', title: 'Hapus instruksi?', html: `Catatan <b>${esc(tglPanjang(d.cppt_date))} ${esc(String(d.cppt_time || '').slice(0, 5))}</b> akan dihapus.`,
            showCancelButton: true, confirmButtonText: 'Hapus', cancelButtonText: 'Batal', confirmButtonColor: '#dc2626', cancelButtonColor: '#475569', reverseButtons: true
          }).then(x => {
            if (!x.isConfirmed) return;
            api(API + '&id=' + encodeURIComponent(d.id_cppt), { method: 'DELETE' }).then(res => { toast(res.message); muat(); }).catch(err => Swal.fire('Gagal', err.message, 'error'));
          });
        }
      });

      muat();
    })();
  </script>
</body>

</html>
