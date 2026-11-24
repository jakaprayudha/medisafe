<?php
/**
 * Form masuk & keluar pasien rawat inap. API controller/ranap/getFormInap.php (baca) & saveInap.php (simpan).
 * Perbaikan: query halaman dirangkai dari URL (SQL injection) dengan INNER JOIN ms_doctor ON id_doctor (berisi nama) &
 * INNER JOIN icd_10 -> data kosong, semua isian awal hilang; $_GET tanpa default; nilai dicetak ke input tanpa escape;
 * "Keadaan Pulang" ber-id kondisi_pulang sehingga tidak pernah terkirim (server membaca keadaan_keluar); jam keluar diisi
 * visit_out yang berisi teks "Rawat Inap"; status perkawinan & alamat PJ ikut disimpan tapi tidak ada isiannya; simpan
 * selalu gagal (tanggal kosong ke kolom DATE) dan error hanya "alert"; lama dirawat dihitung +1 (masuk & keluar hari
 * yang sama = 1, besoknya = 2); tidak ada penanda data sudah tersimpan / belum.
 */
$title = 'Form Masuk dan Keluar Pasien Rawat Inap';
require '../../controller/view.php';
$no = trim((string) ($_GET['no'] ?? ''));
$rm = trim((string) ($_GET['rm'] ?? ''));
$rme = (string) ($_GET['rme'] ?? '');
$idc = (int) ($_SESSION['id_customer'] ?? 0);
$h = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');

$st = $koneksi->prepare("SELECT 1 FROM pasien_visit WHERE visit_ID = ? AND id_customer = ? LIMIT 1");
$st->bind_param('si', $no, $idc);
$st->execute();
$ada = (bool) $st->get_result()->fetch_row();
$st->close();
$keadaan = ['Sembuh', 'Membaik', 'Belum Sembuh', 'Lemah', 'Rujuk', 'Mati < 48 Jam', 'Mati > 48 Jam', 'Lainnya'];
$cara = ['Diizinkan Pulang', 'Pulang', 'Pulang Paksa', 'Lari', 'Pindah Ke FKTP Rawat Inap Lain', 'Dirujuk Ke RS', 'Lainnya'];
$opsi = fn(array $l) => implode('', array_map(fn($o) => '<option value="' . $h($o) . '">' . $h($o) . '</option>', $l));
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .io .kepala { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-bottom: 16px; }
    .io .kepala .ic { width: 42px; height: 42px; border-radius: 12px; background: #e0f2fe; color: #075985; display: inline-flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
    .io .kepala h5 { margin: 0; font-weight: 700; color: #0f172a; }
    .io .kepala small { color: #475569; }
    .io .st { display: inline-block; font-size: .72rem; font-weight: 700; border-radius: 6px; padding: 2px 8px; vertical-align: middle; }
    .io .st-ok { background: #dcfce7; color: #14532d; }
    .io .st-baru { background: #fef3c7; color: #78350f; }
    .io .bag { border: 1px solid #e2e8f0; border-radius: 14px; margin-bottom: 16px; }
    .io .bag-h { display: flex; align-items: center; gap: 8px; padding: 10px 16px; border-bottom: 1px solid #eef2f6; background: #f8fafc; border-radius: 14px 14px 0 0; font-weight: 700; color: #0f172a; }
    .io .bag-h i { color: #2563eb; font-size: 1.1rem; }
    .io .bag-b { padding: 14px 16px 4px; }
    .io .form-label { font-weight: 600; color: #1e293b; font-size: .85rem; margin-bottom: 4px; }
    .io .form-control[readonly] { background: #f1f5f9; color: #0f172a; }
    .io .bantu { font-size: .75rem; color: #334155; margin-top: 2px; }
    .io .lama { font-size: 1.1rem; padding-top: 6px; font-weight: 700; color: #0f172a; line-height: 1.2; }
    .io-bar { position: sticky; bottom: 0; z-index: 20; background: rgba(255, 255, 255, .96); border-top: 1px solid #e2e8f0; padding: 10px 0; margin: 0 -4px; display: flex; flex-wrap: wrap; gap: 8px; align-items: center; justify-content: flex-end; }
    .io-bar .ket { margin-right: auto; font-size: .82rem; color: #334155; }
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
                <div class="card-body p-4 io">
                  <div class="kepala">
                    <span class="ic"><i class="ti ti-transfer-in"></i></span>
                    <div class="flex-grow-1">
                      <h5>Masuk & Keluar Pasien Rawat Inap <span class="st" id="ioStatus"></span></h5>
                      <small>Lembar ringkasan masuk / keluar rawat inap. Isian awal diambil dari data kunjungan, permintaan rawat inap & data pasien.</small>
                    </div>
                    <?php if ($ada): ?>
                      <a class="btn btn-outline-dark" target="_blank" rel="noopener" href="module/admin/print/formulir_inout_ranap?<?= $h(http_build_query(['no' => $no, 'rm' => $rm])) ?>"><i class="ti ti-printer me-1"></i>Cetak</a>
                    <?php endif; ?>
                  </div>

                  <?php if (!$ada): ?>
                    <div class="alert alert-danger mb-0"><i class="ti ti-alert-triangle me-1"></i>Kunjungan tidak ditemukan untuk faskes ini.</div>
                  <?php else: ?>
                    <div id="ioBatal" class="alert alert-warning d-none"><i class="ti ti-ban me-1"></i>Kunjungan sudah dibatalkan — data hanya dapat dilihat.</div>
                    <form id="ioForm" novalidate>
                      <fieldset id="ioFieldset" disabled>
                        <!-- IDENTITAS -->
                        <div class="bag">
                          <div class="bag-h"><i class="ti ti-user"></i>Identitas Pasien</div>
                          <div class="bag-b row g-3">
                            <div class="col-md-3"><label class="form-label">Nama Pasien</label><input type="text" id="patient_name" class="form-control" readonly></div>
                            <div class="col-md-3"><label class="form-label">Gender</label><input type="text" id="patient_gender" class="form-control" readonly></div>
                            <div class="col-md-3"><label class="form-label">Usia</label><input type="text" id="usia" class="form-control" readonly></div>
                            <div class="col-md-3"><label class="form-label">Dokter</label><input type="text" id="doctor_name" class="form-control" readonly></div>
                            <div class="col-md-3"><label class="form-label" for="status_perkawinan">Status Perkawinan</label><input type="text" id="status_perkawinan" class="form-control" maxlength="20"></div>
                            <div class="col-md-3"><label class="form-label" for="penanggung_jawab">Penanggung Jawab</label><input type="text" id="penanggung_jawab" class="form-control" maxlength="255" placeholder="Nama & hubungan"></div>
                            <div class="col-md-6"><label class="form-label" for="alamat_pj">Alamat Penanggung Jawab</label><input type="text" id="alamat_pj" class="form-control" maxlength="2000"></div>
                          </div>
                        </div>

                        <!-- MASUK / PINDAH / KELUAR -->
                          <div class="bag">
                            <div class="bag-h"><i class="ti ti-calendar-time"></i>Waktu Masuk, Pindah & Keluar</div>
                            <div class="bag-b row g-3">
                              <div class="col-md-3"><label class="form-label" for="tanggal_masuk">Tanggal Masuk <span class="text-danger">*</span></label><input type="date" id="tanggal_masuk" class="form-control"><div class="invalid-feedback"></div></div>
                              <div class="col-md-3"><label class="form-label" for="jam_masuk">Jam Masuk</label><input type="time" id="jam_masuk" class="form-control"><div class="invalid-feedback"></div></div>
                              <div class="col-md-3"><label class="form-label" for="tanggal_pindah">Tanggal Pindah</label><input type="date" id="tanggal_pindah" class="form-control"><div class="invalid-feedback"></div><div class="bantu">Pindah ke ruang rawat inap</div></div>
                              <div class="col-md-3"><label class="form-label" for="jam_pindah">Jam Pindah</label><input type="time" id="jam_pindah" class="form-control"><div class="invalid-feedback"></div></div>
                              <div class="col-md-3"><label class="form-label" for="tanggal_keluar">Tanggal Keluar</label><input type="date" id="tanggal_keluar" class="form-control"><div class="invalid-feedback"></div></div>
                              <div class="col-md-3"><label class="form-label" for="jam_keluar">Jam Keluar</label><input type="time" id="jam_keluar" class="form-control"><div class="invalid-feedback"></div></div>
                              <div class="col-md-3"><label class="form-label" for="ruang_rawat">Ruang Rawat / Kelas</label><input type="text" id="ruang_rawat" class="form-control" readonly></div>
                              <div class="col-md-3"><label class="form-label" for="lama_dirawat">Lama Dirawat</label><input type="hidden" id="lama_dirawat"><div class="lama" id="lamaTeks">-</div><div class="bantu">Otomatis: tanggal keluar − tanggal masuk</div></div>
                              <div class="col-md-6"><label class="form-label" for="keadaan_keluar">Keadaan Pulang</label><select id="keadaan_keluar" class="form-select"><option value="">— Pilih —</option><?= $opsi($keadaan) ?></select><div class="invalid-feedback"></div></div>
                              <div class="col-md-6"><label class="form-label" for="cara_keluar">Cara Keluar</label><select id="cara_keluar" class="form-select"><option value="">— Pilih —</option><?= $opsi($cara) ?></select><div class="invalid-feedback"></div></div>
                            </div>
                          </div>

                        <!-- DIAGNOSA -->
                        <div class="bag">
                          <div class="bag-h"><i class="ti ti-stethoscope"></i>Diagnosa & Tindakan</div>
                          <div class="bag-b row g-3">
                            <div class="col-md-6"><label class="form-label" for="diagnosa_medik">Anamnesa Masuk</label><textarea id="diagnosa_medik" class="form-control" rows="2"></textarea></div>
                            <div class="col-md-6"><label class="form-label" for="diagnosa_utama">Diagnosa Utama</label><input type="text" id="diagnosa_utama" class="form-control" readonly><div class="bantu">Dari diagnosa kunjungan (ICD-10)</div></div>
                            <div class="col-md-6"><label class="form-label" for="diagnosa_komplikasi">Diagnosa Sekunder / Komplikasi</label><textarea id="diagnosa_komplikasi" class="form-control" rows="2"></textarea></div>
                            <div class="col-md-6"><label class="form-label" for="nama_operasi">Nama Operasi / Tindakan</label><textarea id="nama_operasi" class="form-control" rows="2"></textarea></div>
                            <div class="col-12"><label class="form-label" for="penyebab_keracunan">Penyebab Cedera / Keracunan</label><textarea id="penyebab_keracunan" class="form-control" rows="2"></textarea></div>
                          </div>
                        </div>

                        <!-- CATATAN MEDIS -->
                        <div class="bag">
                          <div class="bag-h"><i class="ti ti-notes"></i>Catatan Medis Lainnya</div>
                          <div class="bag-b row g-3">
                            <div class="col-md-3"><label class="form-label" for="infeksi_nosokomial">Infeksi Nosokomial</label><select id="infeksi_nosokomial" class="form-select"><option value="">— Pilih —</option><option>Tidak</option><option>Ya</option></select></div>
                            <div class="col-md-3"><label class="form-label" for="penyebab_infeksi">Penyebab Infeksi</label><input type="text" id="penyebab_infeksi" class="form-control" disabled><div class="invalid-feedback"></div></div>
                            <div class="col-md-6"><label class="form-label" for="alergi">Alergi</label><input type="text" id="alergi" class="form-control"></div>
                            <div class="col-md-4"><label class="form-label" for="radioterapi">Radio Therapy</label><input type="text" id="radioterapi" class="form-control"></div>
                            <div class="col-md-4"><label class="form-label" for="imunisasi">Imunisasi</label><input type="text" id="imunisasi" class="form-control"></div>
                            <div class="col-md-4"><label class="form-label" for="transfusi">Transfusi Darah</label><input type="text" id="transfusi" class="form-control"></div>
                            <div class="col-md-6"><label class="form-label" for="dokter_merawat">Dokter Merawat</label><input type="text" id="dokter_merawat" class="form-control" readonly></div>
                          </div>
                        </div>
                      </fieldset>

                      <div class="io-bar">
                        <span class="ket" id="ioKet"></span>
                        <button type="submit" id="openModal" class="btn btn-primary" disabled><i class="ti ti-device-floppy me-1"></i>Simpan</button>
                      </div>
                    </form>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php require 'library.php'; ?>
  <?php if ($ada): ?>
  <script>
    (function() {
      'use strict';
      const NO = <?= json_encode($no) ?>;
      const $id = i => document.getElementById(i);
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const toast = (title, icon = 'success') => Swal.fire({ toast: true, position: 'top-end', icon, title, showConfirmButton: false, timer: 2200 });
      const FIELDS = ['tanggal_masuk', 'jam_masuk', 'status_perkawinan', 'penanggung_jawab', 'alamat_pj', 'tanggal_pindah', 'jam_pindah', 'ruang_rawat',
        'tanggal_keluar', 'jam_keluar', 'diagnosa_medik', 'diagnosa_utama', 'diagnosa_komplikasi', 'penyebab_keracunan', 'nama_operasi',
        'infeksi_nosokomial', 'penyebab_infeksi', 'alergi', 'radioterapi', 'imunisasi', 'transfusi', 'keadaan_keluar', 'cara_keluar', 'dokter_merawat'];
      const form = $id('ioForm');
      let BATAL = false, berubah = false;
      const hariIni = () => { const d = new Date(); return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`; };

      function setNilai(el, v) {
        v = v ?? '';
        if (el.tagName === 'SELECT' && v && ![...el.options].some(o => o.value === v)) el.add(new Option(v + ' (data lama)', v));   // nilai lama di luar daftar tetap tampil
        el.value = v;
      }

      function muat() {
        $.ajax({ url: 'controller/ranap/getFormInap.php', data: { no: NO }, dataType: 'json', cache: false })
          .done(res => {
            const p = res.pasien || {}, awal = res.awal || {}, inap = res.inap;
            BATAL = !!res.batal;
            $id('patient_name').value = p.nama_pasien || '';
            $id('patient_gender').value = p.jk || '';
            $id('usia').value = p.usia || '';
            $id('doctor_name').value = p.doctor_name || '';
            // data tersimpan dipakai bila ada; kolom yang selalu dari kunjungan (ruang, diagnosa utama, dokter) tetap dari data terbaru
            FIELDS.forEach(f => setNilai($id(f), inap && !['ruang_rawat', 'diagnosa_utama', 'dokter_merawat'].includes(f) ? inap[f] : awal[f]));
            const st = $id('ioStatus');
            st.className = 'st ' + (inap ? 'st-ok' : 'st-baru');
            st.textContent = inap ? 'Tersimpan' : 'Belum disimpan';
            $id('ioKet').innerHTML = inap ? '<i class="ti ti-circle-check text-success me-1"></i>Data tersimpan — perubahan perlu disimpan ulang.'
              : '<i class="ti ti-info-circle me-1"></i>Isian awal dari data kunjungan — periksa lalu klik Simpan.';
            $id('ioBatal').classList.toggle('d-none', !BATAL);
            $id('ioFieldset').disabled = BATAL;
            $id('openModal').disabled = BATAL;
            ['tanggal_masuk', 'tanggal_pindah', 'tanggal_keluar'].forEach(f => $id(f).max = hariIni());
            infeksi();
            hitungLamaDirawat();
            berubah = false;
          })
          .fail(x => {
            form.insertAdjacentHTML('beforebegin', `<div class="alert alert-danger"><i class="ti ti-alert-triangle me-1"></i>${esc((x.responseJSON && x.responseJSON.message) || 'Gagal memuat data rawat inap.')}</div>`);
          });
      }

      function hitungLamaDirawat() {
        const m = $id('tanggal_masuk').value, k = $id('tanggal_keluar').value;
        let lama = '';
        if (m && k && k >= m) lama = Math.max(1, Math.round((Date.UTC(...k.split('-').map((v, i) => i === 1 ? v - 1 : +v)) - Date.UTC(...m.split('-').map((v, i) => i === 1 ? v - 1 : +v))) / 864e5));
        $id('lama_dirawat').value = lama;
        $id('lamaTeks').textContent = lama ? lama + ' hari' : (m && k ? 'Tanggal keluar < masuk' : 'Belum keluar');
      }
      function infeksi() {
        const ya = $id('infeksi_nosokomial').value === 'Ya';
        $id('penyebab_infeksi').disabled = !ya;
        if (!ya) $id('penyebab_infeksi').value = '';
      }
      $id('tanggal_masuk').addEventListener('change', hitungLamaDirawat);
      $id('tanggal_keluar').addEventListener('change', hitungLamaDirawat);
      $id('infeksi_nosokomial').addEventListener('change', infeksi);
      form.addEventListener('input', () => { berubah = true; });
      window.addEventListener('beforeunload', e => { if (berubah) { e.preventDefault(); e.returnValue = ''; } });

      function bersih() { form.querySelectorAll('.is-invalid').forEach(i => i.classList.remove('is-invalid')); }
      function tandai(f, pesan) {
        const el = $id(f);
        if (!el) return false;
        el.classList.add('is-invalid');
        const fb = el.parentElement.querySelector('.invalid-feedback');
        if (fb) fb.textContent = pesan;
        el.scrollIntoView({ block: 'center', behavior: 'smooth' });
        el.focus({ preventScroll: true });
        return true;
      }

      form.addEventListener('submit', async e => {
        e.preventDefault();
        if (BATAL) return;
        bersih();
        const v = f => $id(f).value.trim();
        if (!v('tanggal_masuk')) return tandai('tanggal_masuk', 'Tanggal masuk wajib diisi.');
        if (v('tanggal_pindah') && v('tanggal_pindah') < v('tanggal_masuk')) return tandai('tanggal_pindah', 'Tidak boleh sebelum tanggal masuk.');
        if (v('tanggal_keluar') && v('tanggal_keluar') < v('tanggal_masuk')) return tandai('tanggal_keluar', 'Tidak boleh sebelum tanggal masuk.');
        if (v('tanggal_keluar') && v('tanggal_pindah') && v('tanggal_keluar') < v('tanggal_pindah')) return tandai('tanggal_keluar', 'Tidak boleh sebelum tanggal pindah.');
        if (v('infeksi_nosokomial') === 'Ya' && !v('penyebab_infeksi')) return tandai('penyebab_infeksi', 'Isi penyebab infeksi.');

        const data = { visit_ID: NO };
        FIELDS.forEach(f => { data[f] = $id(f).value ?? ''; });
        const btn = $id('openModal');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan…';
        try {
          const r = await fetch('controller/ranap/saveInap.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
          const res = await r.json().catch(() => ({ status: 'error', message: 'Respon server tidak valid.' }));
          if (res.status !== 'success') {
            if (!(res.field && tandai(res.field, res.message))) Swal.fire('Gagal', res.message || 'Gagal menyimpan.', 'error');
            else toast(res.message, 'error');
            return;
          }
          berubah = false;
          toast(res.message || 'Tersimpan.');
          muat();
        } catch (err) {
          Swal.fire('Gagal', 'Tidak dapat terhubung ke server.', 'error');
        } finally {
          btn.disabled = BATAL;
          btn.innerHTML = '<i class="ti ti-device-floppy me-1"></i>Simpan';
        }
      });

      muat();
    })();
  </script>
  <?php endif; ?>
</body>

</html>
