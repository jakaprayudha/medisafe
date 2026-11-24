<?php
/**
 * Resume Medis (ringkasan pulang rawat inap). Data: controller/ranap/getResume.php, simpan: controller/ranap/saveResumeMedis.php,
 * cetak: module/admin/print/formulir_resume_v2.
 * Perbaikan: $_SESSION dibaca sebelum sesi dimulai; query kunjungan/CPPT/obat dirangkai dari URL (SQL injection);
 * INNER JOIN icd_10 membuat data kosong bila diagnosa masuk bukan kode ICD; difilter nomor RM dari URL; atribut
 * value pada <textarea> (tidak berfungsi); terapi selama rawat hanya dari CPPT pertama; obat pulang dicari per tiket
 * (N+1); diagnosa sekunder lama berformat "R11,R63.8" terbaca sebagai satu diagnosa; tombol Simpan bisa diklik
 * berulang (resume dobel) tanpa validasi; id kunjungan/RM dicetak mentah ke JavaScript (XSS); tombol di dalam <a>.
 */
$title = 'Resume Medis';
require '../../controller/view.php';
$no = trim((string) ($_GET['no'] ?? ''));
$rm = trim((string) ($_GET['rm'] ?? ''));
$rme = (string) ($_GET['rme'] ?? '');
$h = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
$kondisi = ['Sembuh', 'Membaik', 'Belum Sembuh', 'Mati < 48 Jam', 'Mati > 48 Jam', 'Lainnya'];
$cara = ['Diizinkan Pulang', 'Pulang Paksa', 'Lari', 'Pindah Ke FKTP Rawat Inap Lain', 'Dirujuk Ke RS', 'Lainnya'];
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .rs .kepala { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-bottom: 14px; }
    .rs .kepala .ic { width: 42px; height: 42px; border-radius: 12px; background: #e0e7ff; color: #3730a3; display: inline-flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
    .rs .kepala h5 { margin: 0; font-weight: 700; color: #0f172a; }
    .rs .kepala small { color: #475569; }
    .rs .st { font-size: .72rem; font-weight: 700; border-radius: 6px; padding: 2px 8px; white-space: nowrap; display: inline-block; }
    .rs .st-ok { background: #dcfce7; color: #14532d; }
    .rs .st-belum { background: #fef3c7; color: #92400e; }
    .rs .info { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 10px 18px; border: 1px solid #e2e8f0; background: #f8fafc; border-radius: 12px; padding: 12px 14px; margin-bottom: 16px; }
    .rs .info small { display: block; font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #475569; }
    .rs .info b { color: #0f172a; font-size: .9rem; word-break: break-word; }
    .rs .info .lebar { grid-column: 1 / -1; }
    .rs .sec { border: 1px solid #e2e8f0; border-radius: 14px; margin-bottom: 14px; }
    .rs .sec-h { display: flex; align-items: center; gap: 8px; padding: 10px 14px; border-bottom: 1px solid #eef2f6; background: #f8fafc; border-radius: 14px 14px 0 0; }
    .rs .sec-h .no { width: 26px; height: 26px; border-radius: 8px; background: #e0e7ff; color: #3730a3; display: inline-flex; align-items: center; justify-content: center; font-weight: 800; font-size: .82rem; }
    .rs .sec-h b { color: #0f172a; }
    .rs .sec-b { padding: 14px; }
    .rs .lbl { display: flex; align-items: center; gap: 6px; }
    .rs .lbl .btn-link { font-size: .78rem; padding: 0; margin-left: auto; text-decoration: none; font-weight: 600; }
    .rs .chips { display: flex; flex-wrap: wrap; gap: 6px; }
    .rs .chips input { position: absolute; opacity: 0; pointer-events: none; }
    .rs .chips label { border: 1px solid #cbd5e1; border-radius: 999px; padding: 5px 12px; cursor: pointer; font-size: .84rem; font-weight: 600; color: #1f2937; background: #fff; }
    .rs .chips input:checked + label { background: #0f172a; border-color: #0f172a; color: #fff; }
    .rs .chips input:focus-visible + label { outline: 2px solid #2563eb; outline-offset: 1px; }
    .rs .chips.is-invalid label { border-color: #dc3545; }
    .rs .bar { position: sticky; bottom: 0; z-index: 6; background: #fff; border-top: 1px solid #e2e8f0; padding: 10px 0; display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
    .rs .bar .ket { margin-right: auto; font-size: .82rem; color: #475569; }
    .rs .is-invalid + .select2 .select2-selection { border-color: #dc3545 !important; }
    .rs .select2-container .select2-selection--multiple { min-height: 38px; }
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
                <div class="card-body p-4 rs">
                  <div class="kepala">
                    <span class="ic"><i class="ti ti-file-certificate"></i></span>
                    <div class="flex-grow-1">
                      <h5>Resume Medis <span class="st st-belum" id="rsStatus">Memuat…</span></h5>
                      <small id="rsSub">Ringkasan perawatan saat pasien pulang dari rawat inap.</small>
                    </div>
                    <a class="btn btn-outline-dark disabled" id="rsCetak" target="_blank" rel="noopener" aria-disabled="true"
                      href="module/admin/print/formulir_resume_v2?<?= $h(http_build_query(['no' => $no, 'rm' => $rm])) ?>"><i class="ti ti-printer me-1"></i>Cetak</a>
                  </div>
                  <div class="alert alert-danger d-none" id="rsGagal"></div>
                  <div class="alert alert-danger d-none" id="rsBatal"><i class="ti ti-ban me-1"></i>Kunjungan ini sudah dibatalkan — resume tidak bisa disimpan.</div>

                  <div id="rsIsi" class="d-none">
                    <div class="info">
                      <div><small>Tanggal masuk</small><b id="iMasuk">-</b></div>
                      <div><small>Ruang / bed</small><b id="iRuang">-</b></div>
                      <div><small>DPJP</small><b id="iDpjp">-</b></div>
                      <div><small>Diagnosa masuk</small><b id="iDx">-</b></div>
                      <div class="lebar"><small>Indikasi rawat inap</small><b id="iIndikasi" style="font-weight:500">-</b></div>
                    </div>

                    <section class="sec">
                      <div class="sec-h"><span class="no">1</span><b>Kepulangan</b></div>
                      <div class="sec-b"><div class="row g-3">
                        <div class="col-md-3 col-6">
                          <label class="form-label small fw-semibold" for="tanggal_pulang">Tanggal pulang <span class="text-danger">*</span></label>
                          <input type="date" id="tanggal_pulang" class="form-control">
                          <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-3 col-6">
                          <label class="form-label small fw-semibold lbl" for="jam_pulang">Jam pulang <span class="text-danger">*</span><button type="button" class="btn btn-link" id="rsSekarang">Sekarang</button></label>
                          <input type="time" id="jam_pulang" class="form-control">
                          <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                          <label class="form-label small fw-semibold" for="cara_keluar">Cara keluar <span class="text-danger">*</span></label>
                          <select id="cara_keluar" class="form-select">
                            <option value="">— Pilih —</option>
                            <?php foreach ($cara as $c): ?><option><?= $h($c) ?></option><?php endforeach; ?>
                          </select>
                          <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-12">
                          <div class="form-label small fw-semibold" id="kondisi_pulang">Kondisi pasien saat pulang <span class="text-danger">*</span></div>
                          <div class="chips" id="kondisiChips">
                            <?php foreach ($kondisi as $i => $c): ?>
                              <input type="radio" name="kondisi" id="kd<?= $i ?>" value="<?= $h($c) ?>"><label for="kd<?= $i ?>"><?= $h($c) ?></label>
                            <?php endforeach; ?>
                          </div>
                          <div class="invalid-feedback d-block"></div>
                        </div>
                      </div></div>
                    </section>

                    <section class="sec">
                      <div class="sec-h"><span class="no">2</span><b>Diagnosis akhir</b></div>
                      <div class="sec-b"><div class="row g-3">
                        <div class="col-md-6">
                          <label class="form-label small fw-semibold" for="diagnosa_utama">Diagnosa utama (ICD-10) <span class="text-danger">*</span></label>
                          <select id="diagnosa_utama" class="form-select"></select>
                          <div class="invalid-feedback d-block"></div>
                        </div>
                        <div class="col-md-6">
                          <label class="form-label small fw-semibold" for="diagnosa_sekunder">Diagnosa sekunder (ICD-10)</label>
                          <select id="diagnosa_sekunder" class="form-select" multiple></select>
                          <div class="invalid-feedback d-block"></div>
                        </div>
                      </div></div>
                    </section>

                    <section class="sec">
                      <div class="sec-h"><span class="no">3</span><b>Pemeriksaan &amp; terapi</b></div>
                      <div class="sec-b"><div class="row g-3">
                        <div class="col-md-6">
                          <label class="form-label small fw-semibold lbl" for="pemeriksaan_fisik">Pemeriksaan fisik<button type="button" class="btn btn-link" data-isi="pemeriksaan_fisik">Isi dari tanda vital</button></label>
                          <textarea id="pemeriksaan_fisik" class="form-control" rows="6" maxlength="10000"></textarea>
                        </div>
                        <div class="col-md-6">
                          <label class="form-label small fw-semibold lbl" for="terapi_rawat">Terapi selama rawat inap<button type="button" class="btn btn-link" data-isi="terapi_rawat">Isi dari CPPT</button></label>
                          <textarea id="terapi_rawat" class="form-control" rows="6" maxlength="10000"></textarea>
                        </div>
                        <div class="col-md-6">
                          <label class="form-label small fw-semibold lbl" for="terapi_pulang">Terapi pulang<button type="button" class="btn btn-link" data-isi="terapi_pulang">Isi dari resep obat pulang</button></label>
                          <textarea id="terapi_pulang" class="form-control" rows="5" maxlength="10000"></textarea>
                        </div>
                        <div class="col-md-6">
                          <label class="form-label small fw-semibold lbl" for="alergi_obat">Alergi obat<button type="button" class="btn btn-link" data-isi="alergi">Isi dari riwayat alergi</button></label>
                          <textarea id="alergi_obat" class="form-control" rows="2" maxlength="2000"></textarea>
                          <label class="form-label small fw-semibold mt-3" for="rencana_tindak_lanjut">Rencana tindak lanjut</label>
                          <textarea id="rencana_tindak_lanjut" class="form-control" rows="2" maxlength="5000" placeholder="mis. kontrol poli 3 hari lagi"></textarea>
                        </div>
                      </div></div>
                    </section>

                    <div class="bar">
                      <span class="ket" id="rsKet"></span>
                      <button type="button" class="btn btn-primary px-4" id="rsSimpan"><i class="ti ti-device-floppy me-1"></i>Simpan resume</button>
                    </div>
                  </div>
                  <div id="rsMuat" class="text-center text-muted py-5"><span class="spinner-border spinner-border-sm me-2"></span>Memuat data kunjungan…</div>
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
      const NO = <?= json_encode($no) ?>;
      const $id = i => document.getElementById(i);
      const BLN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      const tglId = s => { const m = String(s || '').match(/^(\d{4})-(\d{2})-(\d{2})/); return m ? (+m[3]) + ' ' + BLN[m[2] - 1] + ' ' + m[1] : '-'; };
      const sekarang = () => { const p = new Date().toLocaleString('sv-SE', { timeZone: 'Asia/Jakarta' }); return { tgl: p.slice(0, 10), jam: p.slice(11, 16) }; };
      const toast = (title, icon = 'success') => Swal.fire({ toast: true, position: 'top-end', icon, title, showConfirmButton: false, timer: 2400 });
      let SARAN = {}, P = {}, kotor = false;

      // select2 ICD-10 (dibuat sekali)
      function initICD(sel, multiple) {
        $(sel).select2({
          width: '100%', placeholder: 'Cari kode / nama diagnosa…', minimumInputLength: 2, multiple, allowClear: !multiple,
          ajax: { url: 'controller/visit/getICD10.php', dataType: 'json', delay: 300, data: p => ({ search: p.term }), processResults: d => ({ results: d }) }
        }).on('change', () => { kotor = true; $(sel).removeClass('is-invalid'); });
      }
      initICD('#diagnosa_utama', false);
      initICD('#diagnosa_sekunder', true);
      const setICD = (sel, items) => {
        const $s = $(sel).empty();
        (items || []).forEach(it => $s.append(new Option(it.text, it.id, true, true)));
        $s.trigger('change');
      };

      function statusResume(r) {
        const ada = !!r;
        $id('rsStatus').className = 'st ' + (ada ? 'st-ok' : 'st-belum');
        $id('rsStatus').textContent = ada ? 'Tersimpan' : 'Belum dibuat';
        $id('rsCetak').classList.toggle('disabled', !ada);
        $id('rsCetak').setAttribute('aria-disabled', ada ? 'false' : 'true');
        $id('rsKet').textContent = ada ? `Terakhir disimpan ${r.created_at ? tglId(r.created_at) : ''}${r.petugas ? ' oleh ' + r.petugas : ''}` : 'Isian awal diambil dari data kunjungan, CPPT & resep — periksa sebelum disimpan.';
      }

      fetch('controller/ranap/getResume?no=' + encodeURIComponent(NO))
        .then(r => r.json().then(j => ({ ok: r.ok, j })))
        .then(({ ok, j }) => {
          $id('rsMuat').classList.add('d-none');
          if (!ok || j.status !== 'success') {
            $id('rsGagal').textContent = j.message || 'Gagal memuat data.';
            $id('rsGagal').classList.remove('d-none');
            $id('rsStatus').classList.add('d-none');
            return;
          }
          P = j.pasien; SARAN = j.saran || {};
          const r = j.resume;
          $id('rsIsi').classList.remove('d-none');
          $id('iMasuk').textContent = tglId(P.tgl_masuk) + (P.visit_time ? ' ' + String(P.visit_time).slice(0, 5) : '');
          $id('iRuang').textContent = P.ruang || '-';
          $id('iDpjp').textContent = P.id_doctor || '-';
          $id('iDx').textContent = P.diagnosa ? P.diagnosa + (P.diagnosa_nama ? ' — ' + P.diagnosa_nama : '') : '-';
          $id('iIndikasi').textContent = P.indikasi || '-';
          $id('rsBatal').classList.toggle('d-none', !P.batal);
          $id('rsSimpan').disabled = !!P.batal;

          const now = sekarang();
          $id('tanggal_pulang').max = now.tgl;
          $id('tanggal_pulang').min = String(P.tgl_masuk || '').slice(0, 10);
          // nilai tersimpan; bila belum ada resume -> saran otomatis
          $id('tanggal_pulang').value = (r && r.tanggal_pulang) || (P.visit_date_out ? String(P.visit_date_out).slice(0, 10) : '');
          $id('jam_pulang').value = (r && r.jam_pulang) ? String(r.jam_pulang).slice(0, 5) : '';
          $id('cara_keluar').value = P.cara_keluar || '';
          document.querySelectorAll('[name="kondisi"]').forEach(x => x.checked = x.value === P.kondisi_pulang);
          setICD('#diagnosa_utama', P.dx_utama ? [P.dx_utama] : []);
          setICD('#diagnosa_sekunder', P.dx_sekunder);
          $id('pemeriksaan_fisik').value = (r && r.tindakan) || P.pemeriksaan_fisik || SARAN.pemeriksaan_fisik || '';
          $id('terapi_rawat').value = r ? (r.pemeriksaan_penunjang || '') : (SARAN.terapi_rawat || '');
          $id('terapi_pulang').value = r ? (r.instruksi || '') : (SARAN.terapi_pulang || '');
          $id('alergi_obat').value = P.alergi_obat || (r && r.obat) || SARAN.alergi || '';
          $id('rencana_tindak_lanjut').value = P.rencana_tindak_lanjut || '';
          document.querySelectorAll('[data-isi]').forEach(b => { const k = b.dataset.isi; b.disabled = !SARAN[k]; b.title = SARAN[k] ? '' : 'Tidak ada data'; });
          statusResume(r);
          kotor = false;
        })
        .catch(() => { $id('rsMuat').classList.add('d-none'); $id('rsGagal').textContent = 'Tidak dapat terhubung ke server.'; $id('rsGagal').classList.remove('d-none'); });

      // tombol "Isi dari ..." — konfirmasi bila kolom sudah berisi
      const TUJUAN = { pemeriksaan_fisik: 'pemeriksaan_fisik', terapi_rawat: 'terapi_rawat', terapi_pulang: 'terapi_pulang', alergi: 'alergi_obat' };
      document.querySelectorAll('[data-isi]').forEach(b => b.addEventListener('click', async () => {
        const el = $id(TUJUAN[b.dataset.isi]), isi = SARAN[b.dataset.isi] || '';
        if (!isi) return;
        if (el.value.trim() && el.value.trim() !== isi.trim()) {
          const r = await Swal.fire({ icon: 'question', title: 'Kolom sudah berisi', text: 'Ganti isinya, atau tambahkan di bawah?', showDenyButton: true, showCancelButton: true,
            confirmButtonText: 'Ganti', denyButtonText: 'Tambahkan', cancelButtonText: 'Batal', denyButtonColor: '#0f172a', cancelButtonColor: '#475569' });
          if (r.isDismissed) return;
          el.value = r.isConfirmed ? isi : el.value.replace(/\s+$/, '') + '\n' + isi;
        } else el.value = isi;
        kotor = true;
        el.focus();
      }));
      $id('rsSekarang').addEventListener('click', () => { const n = sekarang(); $id('jam_pulang').value = n.jam; if (!$id('tanggal_pulang').value) $id('tanggal_pulang').value = n.tgl; kotor = true; });
      $id('rsIsi').addEventListener('input', () => { kotor = true; });
      $id('rsIsi').addEventListener('change', e => { kotor = true; e.target.classList && e.target.classList.remove('is-invalid'); if (e.target.name === 'kondisi') $id('kondisiChips').classList.remove('is-invalid'); });

      /* ================= SIMPAN ================= */
      function bersihkan() {
        document.querySelectorAll('#rsIsi .is-invalid').forEach(e => e.classList.remove('is-invalid'));
        document.querySelectorAll('#rsIsi .invalid-feedback').forEach(e => e.textContent = '');
      }
      function tandai(field, pesan) {
        const el = field === 'kondisi_pulang' ? $id('kondisiChips') : $id(field);
        if (!el) return false;
        el.classList.add('is-invalid');
        const fb = el.closest('[class*="col-"]').querySelector('.invalid-feedback');
        if (fb) fb.textContent = pesan;
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        if (field.startsWith('diagnosa')) $(el).select2('open'); else if (el.focus) el.focus({ preventScroll: true });
        return true;
      }
      let menyimpan = false;
      $id('rsSimpan').addEventListener('click', async () => {
        if (menyimpan) return;
        bersihkan();
        const data = {
          tanggal_pulang: $id('tanggal_pulang').value,
          jam_pulang: $id('jam_pulang').value,
          cara_keluar: $id('cara_keluar').value,
          kondisi_pulang: (document.querySelector('[name="kondisi"]:checked') || {}).value || '',
          diagnosa_utama: $('#diagnosa_utama').val() || '',
          diagnosa_sekunder: $('#diagnosa_sekunder').val() || [],
          pemeriksaan_fisik: $id('pemeriksaan_fisik').value,
          terapi_rawat: $id('terapi_rawat').value,
          terapi_pulang: $id('terapi_pulang').value,
          alergi_obat: $id('alergi_obat').value,
          rencana_tindak_lanjut: $id('rencana_tindak_lanjut').value,
        };
        if (!data.tanggal_pulang) return tandai('tanggal_pulang', 'Tanggal pulang wajib diisi.');
        if (!data.jam_pulang) return tandai('jam_pulang', 'Jam pulang wajib diisi.');
        if (!data.cara_keluar) return tandai('cara_keluar', 'Pilih cara keluar.');
        if (!data.kondisi_pulang) return tandai('kondisi_pulang', 'Pilih kondisi pasien saat pulang.');
        if (!data.diagnosa_utama) return tandai('diagnosa_utama', 'Diagnosa utama wajib diisi.');
        menyimpan = true;
        const btn = $id('rsSimpan'), label = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan…';
        try {
          const r = await fetch('controller/ranap/saveResumeMedis.php?no=' + encodeURIComponent(NO), { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
          const j = await r.json().catch(() => ({ status: 'error', message: 'Respon server tidak valid.' }));
          if (!r.ok || j.status !== 'success') { if (!(j.field && tandai(j.field, j.message))) Swal.fire('Gagal', j.message || 'Gagal menyimpan.', 'error'); return; }
          kotor = false;
          statusResume({ created_at: sekarang().tgl, petugas: '' });
          $id('rsKet').textContent = 'Tersimpan ' + sekarang().jam + ' — siap dicetak.';
          toast(j.message);
        } catch (e) {
          Swal.fire('Gagal', 'Tidak dapat terhubung ke server.', 'error');
        } finally {
          menyimpan = false;
          btn.disabled = !!P.batal;
          btn.innerHTML = label;
        }
      });
      window.addEventListener('beforeunload', e => { if (kotor) { e.preventDefault(); e.returnValue = ''; } });
    })();
  </script>
</body>

</html>
