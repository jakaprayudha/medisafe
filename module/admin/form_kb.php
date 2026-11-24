<?php
/**
 * Kartu Status Peserta KB (K/IV/KB). API controller/ranap/getFormStatusKB.php & saveStatusKB.php,
 * cetak print/formulir_status_kb.
 * Perbaikan: $_GET tanpa default & nomor kunjungan/RM dicetak ke JS tanpa escape; data tidak dimuat bila URL tanpa rm;
 * dokter kosong (JOIN ms_doctor); simpan tidak pernah tersimpan ke kunjungan ini (lihat saveStatusKB.php) & gagal untuk
 * isian kosong; "Pemeriksaan Tambahan" teks bebas padahal kolomnya ya/tidak; umur anak teks bebas padahal angka;
 * "Haid Terakhir" bertanda required tapi tidak divalidasi; label "Keaadaan Umum" salah ketik; error hanya "alert";
 * tidak ada isian awal (BB/TD kunjungan, G/P/A dari ANC) & penanda data tersimpan.
 */
$title = 'Kartu Status Peserta KB';
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
$metode = ['Suntik 1 bulan', 'Suntik 3 bulan', 'Pil KB', 'Implan', 'IUD / AKDR', 'Kondom', 'MOW (Tubektomi)', 'MOP (Vasektomi)', 'MAL (Laktasi)'];
$pendidikan = ['Tidak sekolah', 'SD', 'SMP', 'SMA', 'D1/D2/D3', 'S1/D4', 'S2', 'S3'];
$daftar = fn(string $id, array $l) => '<datalist id="' . $id . '">' . implode('', array_map(fn($o) => '<option value="' . $h($o) . '">', $l)) . '</datalist>';
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .kb .kepala { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-bottom: 16px; }
    .kb .kepala .ic { width: 42px; height: 42px; border-radius: 12px; background: #ede9fe; color: #5b21b6; display: inline-flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
    .kb .kepala h5 { margin: 0; font-weight: 700; color: #0f172a; }
    .kb .kepala small { color: #475569; }
    .kb .st { display: inline-block; font-size: .72rem; font-weight: 700; border-radius: 6px; padding: 2px 8px; vertical-align: middle; }
    .kb .st-ok { background: #dcfce7; color: #14532d; }
    .kb .st-baru { background: #fef3c7; color: #78350f; }
    .kb .bag { border: 1px solid #e2e8f0; border-radius: 14px; margin-bottom: 16px; }
    .kb .bag-h { display: flex; align-items: center; gap: 8px; padding: 10px 16px; border-bottom: 1px solid #eef2f6; background: #f8fafc; border-radius: 14px 14px 0 0; font-weight: 700; color: #0f172a; }
    .kb .bag-h i { color: #6d28d9; font-size: 1.1rem; }
    .kb .no { font-size: .72rem; font-weight: 700; color: #5b21b6; background: #ede9fe; border-radius: 5px; padding: 1px 6px; }
    .kb .bag-b { padding: 14px 16px; }
    .kb .form-label { font-weight: 600; color: #1e293b; font-size: .85rem; margin-bottom: 4px; }
    .kb .form-control[readonly] { background: #f1f5f9; color: #0f172a; }
    .kb .bantu { font-size: .75rem; color: #334155; margin-top: 3px; }
    .kb .yt { display: flex; gap: 6px; }
    .kb .yt .btn { flex: 1; font-weight: 600; }
    .kb-bar { position: sticky; bottom: 0; z-index: 20; background: rgba(255, 255, 255, .96); border-top: 1px solid #e2e8f0; padding: 10px 0; display: flex; flex-wrap: wrap; gap: 8px; align-items: center; justify-content: flex-end; }
    .kb-bar .ket { margin-right: auto; font-size: .82rem; color: #334155; }
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
                <div class="card-body p-4 kb">
                  <div class="kepala">
                    <span class="ic"><i class="ti ti-heart-handshake"></i></span>
                    <div class="flex-grow-1">
                      <h5>Kartu Status Peserta KB <span class="st" id="kbStatus"></span></h5>
                      <small>Kartu K/IV/KB — identitas, penapisan (skrining) & metode kontrasepsi yang dilayani.</small>
                    </div>
                    <?php if ($ada): ?>
                      <a class="btn btn-outline-dark" target="_blank" rel="noopener" href="module/admin/print/formulir_status_kb?<?= $h(http_build_query(['no' => $no, 'rm' => $rm])) ?>"><i class="ti ti-printer me-1"></i>Cetak</a>
                    <?php endif; ?>
                  </div>

                  <?php if (!$ada): ?>
                    <div class="alert alert-danger mb-0"><i class="ti ti-alert-triangle me-1"></i>Kunjungan tidak ditemukan untuk faskes ini.</div>
                  <?php else: ?>
                    <div id="kbBatal" class="alert alert-warning d-none"><i class="ti ti-ban me-1"></i>Kunjungan sudah dibatalkan — data hanya dapat dilihat.</div>
                    <?= $daftar('kbMetode', $metode) . $daftar('kbPendidikan', $pendidikan) ?>
                    <form id="kbForm" novalidate>
                      <fieldset id="kbFieldset" disabled>
                        <div class="bag">
                          <div class="bag-h"><i class="ti ti-user"></i>Identitas Peserta</div>
                          <div class="bag-b row g-3">
                            <div class="col-md-3"><label class="form-label">Nama Pasien</label><input type="text" id="patient_name" class="form-control" readonly></div>
                            <div class="col-md-3"><label class="form-label">Gender</label><input type="text" id="patient_gender" class="form-control" readonly></div>
                            <div class="col-md-3"><label class="form-label">Usia</label><input type="text" id="usia" class="form-control" readonly></div>
                            <div class="col-md-3"><label class="form-label">Dokter</label><input type="text" id="doctor_name" class="form-control" readonly></div>
                            <div class="col-md-3"><label class="form-label" for="faskes_kode"><span class="no me-1">I</span>Nomor Kode Faskes KB</label><input type="text" id="faskes_kode" class="form-control" maxlength="10"><div class="invalid-feedback"></div></div>
                            <div class="col-md-3"><label class="form-label" for="kode_keluarga"><span class="no me-1">II</span>Kode Keluarga Indonesia</label><input type="text" id="kode_keluarga" class="form-control" maxlength="20"><div class="invalid-feedback"></div></div>
                            <div class="col-md-6"><label class="form-label" for="nama_suami"><span class="no me-1">V</span>Nama Suami</label><input type="text" id="nama_suami" class="form-control" maxlength="100"><div class="invalid-feedback"></div></div>
                            <div class="col-md-3"><label class="form-label" for="pendidikan_suami">Pendidikan Suami</label><input type="text" id="pendidikan_suami" class="form-control" maxlength="50" list="kbPendidikan"><div class="invalid-feedback"></div></div>
                            <div class="col-md-3"><label class="form-label">Pendidikan Istri</label><input type="text" id="pendidikan_istri" class="form-control" readonly><div class="bantu">Dari data pasien</div></div>
                            <div class="col-md-3"><label class="form-label" for="pekerjaan_suami">Pekerjaan Suami</label><input type="text" id="pekerjaan_suami" class="form-control" maxlength="100"><div class="invalid-feedback"></div></div>
                            <div class="col-md-3"><label class="form-label">Pekerjaan Istri</label><input type="text" id="pekerjaan_istri" class="form-control" readonly><div class="bantu">Dari data pasien</div></div>
                          </div>
                        </div>

                        <div class="bag">
                          <div class="bag-h"><i class="ti ti-users"></i>Anak & Riwayat KB</div>
                          <div class="bag-b row g-3">
                            <div class="col-md-3 col-6"><label class="form-label" for="anak_lk"><span class="no me-1">X</span>Anak Hidup Laki-laki</label><input type="number" id="anak_lk" class="form-control" min="0" max="30"><div class="invalid-feedback"></div></div>
                            <div class="col-md-3 col-6"><label class="form-label" for="anak_pr">Anak Hidup Perempuan</label><input type="number" id="anak_pr" class="form-control" min="0" max="30"><div class="invalid-feedback"></div></div>
                            <div class="col-md-3"><label class="form-label" for="umur_anak_terakhir"><span class="no me-1">XI</span>Umur Anak Terakhir</label><div class="input-group"><input type="number" id="umur_anak_terakhir" class="form-control" min="0" max="60"><span class="input-group-text">tahun</span></div><div class="invalid-feedback d-block" data-fb="umur_anak_terakhir"></div></div>
                            <div class="col-md-3"><label class="form-label" for="kb_terakhir"><span class="no me-1">XII</span>KB Terakhir</label><input type="text" id="kb_terakhir" class="form-control" maxlength="100" list="kbMetode" placeholder="Belum pernah / metode"><div class="invalid-feedback"></div></div>
                          </div>
                        </div>

                        <div class="bag">
                          <div class="bag-h"><i class="ti ti-stethoscope"></i><span class="no">XIV</span>Penapisan (Skrining)</div>
                          <div class="bag-b row g-3">
                            <div class="col-md-3"><label class="form-label" for="haid_terakhir">1. Haid Terakhir</label><input type="date" id="haid_terakhir" class="form-control"><div class="invalid-feedback"></div></div>
                            <div class="col-md-3"><label class="form-label">2. Hamil</label><div class="yt" data-yt="hamil"></div><input type="hidden" id="hamil"><div class="invalid-feedback d-block" data-fb="hamil"></div></div>
                            <div class="col-md-3"><label class="form-label">4. Menyusui</label><div class="yt" data-yt="menyusui"></div><input type="hidden" id="menyusui"></div>
                            <div class="col-md-3"><label class="form-label">9–12. Perlu Pemeriksaan Tambahan</label><div class="yt" data-yt="pemeriksaan_tambahan"></div><input type="hidden" id="pemeriksaan_tambahan"></div>
                            <div class="col-md-1 col-4"><label class="form-label" for="gpa_g">3. G</label><input type="number" id="gpa_g" class="form-control" min="0" max="30"><div class="invalid-feedback"></div></div>
                            <div class="col-md-1 col-4"><label class="form-label" for="gpa_p">P</label><input type="number" id="gpa_p" class="form-control" min="0" max="30"><div class="invalid-feedback"></div></div>
                            <div class="col-md-1 col-4"><label class="form-label" for="gpa_a">A</label><input type="number" id="gpa_a" class="form-control" min="0" max="30"><div class="invalid-feedback"></div></div>
                            <div class="col-md-3"><label class="form-label" for="keadaan_umum">6. Keadaan Umum</label><select id="keadaan_umum" class="form-select"><option value="">— Pilih —</option><option>Baik</option><option>Sedang</option><option>Kurang</option></select><div class="invalid-feedback"></div></div>
                            <div class="col-md-3"><label class="form-label" for="berat_badan">7. Berat Badan</label><div class="input-group"><input type="number" id="berat_badan" class="form-control" min="1" max="300" step="0.1"><span class="input-group-text">kg</span></div><div class="invalid-feedback d-block" data-fb="berat_badan"></div></div>
                            <div class="col-md-3"><label class="form-label" for="tekanan_darah">8. Tekanan Darah</label><div class="input-group"><input type="text" id="tekanan_darah" class="form-control" maxlength="7" placeholder="120/80" inputmode="numeric"><span class="input-group-text">mmHg</span></div><div class="invalid-feedback d-block" data-fb="tekanan_darah"></div></div>
                            <div class="col-12"><label class="form-label" for="riwayat_sakit">5. Riwayat Penyakit</label><textarea id="riwayat_sakit" class="form-control" rows="2" placeholder="mis. hipertensi, DM, penyakit jantung, keputihan, tumor…"></textarea></div>
                          </div>
                        </div>

                        <div class="bag">
                          <div class="bag-h"><i class="ti ti-pill"></i>Pelayanan KB</div>
                          <div class="bag-b row g-3">
                            <div class="col-md-4"><label class="form-label" for="metode_pilihan"><span class="no me-1">XV</span>Metode KB yang Dipilih</label><input type="text" id="metode_pilihan" class="form-control" maxlength="50" list="kbMetode" placeholder="Pilih atau ketik"><div class="invalid-feedback"></div></div>
                            <div class="col-md-2"><label class="form-label" for="tgl_dilayani"><span class="no me-1">XVI</span>Tgl Dilayani</label><input type="date" id="tgl_dilayani" class="form-control"><div class="invalid-feedback"></div></div>
                            <div class="col-md-2"><label class="form-label" for="tgl_dicabut"><span class="no me-1">XVII</span>Tgl Dicabut</label><input type="date" id="tgl_dicabut" class="form-control"><div class="invalid-feedback"></div></div>
                            <div class="col-md-4"><label class="form-label" for="penanggung_jawab"><span class="no me-1">XIX</span>Penanggung Jawab</label><input type="text" id="penanggung_jawab" class="form-control" maxlength="100"><div class="invalid-feedback"></div></div>
                          </div>
                        </div>
                      </fieldset>

                      <div class="kb-bar">
                        <span class="ket" id="kbKet"></span>
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
      const FIELDS = ['faskes_kode', 'kode_keluarga', 'nama_suami', 'pendidikan_suami', 'pekerjaan_suami', 'anak_lk', 'anak_pr', 'umur_anak_terakhir',
        'kb_terakhir', 'haid_terakhir', 'hamil', 'gpa_g', 'gpa_p', 'gpa_a', 'menyusui', 'riwayat_sakit', 'keadaan_umum', 'berat_badan',
        'tekanan_darah', 'pemeriksaan_tambahan', 'metode_pilihan', 'tgl_dilayani', 'tgl_dicabut', 'penanggung_jawab'];
      const form = $id('kbForm');
      let BATAL = false, berubah = false;
      const hariIni = () => { const d = new Date(); return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`; };

      // tombol Ya / Tidak (kolom ENUM ya|tidak)
      document.querySelectorAll('[data-yt]').forEach(box => {
        const f = box.dataset.yt;
        box.innerHTML = ['ya', 'tidak'].map(v => `<button type="button" class="btn btn-sm btn-outline-dark" data-v="${v}">${v === 'ya' ? 'Ya' : 'Tidak'}</button>`).join('');
        box.addEventListener('click', e => {
          const b = e.target.closest('[data-v]');
          if (!b) return;
          setYt(f, $id(f).value === b.dataset.v ? '' : b.dataset.v);   // klik lagi = kosongkan
          berubah = true;
        });
      });
      function setYt(f, v) {
        v = String(v ?? '').toLowerCase();
        $id(f).value = v;
        document.querySelectorAll(`[data-yt="${f}"] [data-v]`).forEach(b => {
          const on = b.dataset.v === v;
          b.classList.toggle('btn-dark', on && v === 'tidak');
          b.classList.toggle('btn-danger', on && v === 'ya' && f === 'hamil');
          b.classList.toggle('btn-primary', on && v === 'ya' && f !== 'hamil');
          b.classList.toggle('btn-outline-dark', !on);
        });
        if (f === 'hamil') peringatanHamil();
      }
      function peringatanHamil() {
        const fb = document.querySelector('[data-fb="hamil"]');
        fb.textContent = $id('hamil').value === 'ya' ? 'Pasien hamil — metode KB tidak boleh diberikan.' : '';
      }

      function muat() {
        $.ajax({ url: 'controller/ranap/getFormStatusKB.php', data: { no: NO }, dataType: 'json', cache: false })
          .done(res => {
            const p = res.pasien || {}, s = res.statuskb, awal = res.awal || {};
            BATAL = !!res.batal;
            $id('patient_name').value = p.nama_pasien || '';
            $id('patient_gender').value = p.jk || '';
            $id('usia').value = p.usia || '';
            $id('doctor_name').value = p.doctor_name || '';
            $id('pendidikan_istri').value = p.pendidikan_istri || '-';
            $id('pekerjaan_istri').value = p.pekerjaan_istri || '-';
            const src = s || awal;
            FIELDS.forEach(f => {
              const el = $id(f), v = src[f] ?? '';
              if (['hamil', 'menyusui', 'pemeriksaan_tambahan'].includes(f)) return setYt(f, v);
              if (el.tagName === 'SELECT' && v && ![...el.options].some(o => o.value === v)) el.add(new Option(v + ' (data lama)', v));
              el.value = v;
            });
            ['haid_terakhir', 'tgl_dilayani'].forEach(f => $id(f).max = hariIni());
            const st = $id('kbStatus');
            st.className = 'st ' + (s ? 'st-ok' : 'st-baru');
            st.textContent = s ? 'Tersimpan' : 'Belum disimpan';
            $id('kbKet').innerHTML = s ? '<i class="ti ti-circle-check text-success me-1"></i>Data tersimpan.'
              : '<i class="ti ti-info-circle me-1"></i>BB, TD, G/P/A & tanggal dilayani diisi dari data kunjungan / ANC — periksa lalu Simpan.';
            $id('kbBatal').classList.toggle('d-none', !BATAL);
            $id('kbFieldset').disabled = BATAL;
            $id('openModal').disabled = BATAL;
            berubah = false;
          })
          .fail(x => form.insertAdjacentHTML('beforebegin', `<div class="alert alert-danger"><i class="ti ti-alert-triangle me-1"></i>${esc((x.responseJSON && x.responseJSON.message) || 'Gagal memuat data KB.')}</div>`));
      }

      // hanya angka & "/"; saat keluar kolom angka tanpa "/" dirapikan: 9060 -> 90/60, 12080 -> 120/80, 140100 -> 140/100
      $id('tekanan_darah').addEventListener('input', function() { this.value = this.value.replace(/[^\d/]/g, '').slice(0, 7); });
      $id('tekanan_darah').addEventListener('blur', function() {
        const d = this.value.replace(/\D/g, '');
        if (!this.value.includes('/') && d.length >= 4) this.value = d.length === 4 ? d.slice(0, 2) + '/' + d.slice(2) : d.slice(0, 3) + '/' + d.slice(3, 6);
      });
      form.addEventListener('input', () => { berubah = true; });
      window.addEventListener('beforeunload', e => { if (berubah) { e.preventDefault(); e.returnValue = ''; } });

      function bersih() { form.querySelectorAll('.is-invalid').forEach(i => i.classList.remove('is-invalid')); form.querySelectorAll('[data-fb]').forEach(f => { if (f.dataset.fb !== 'hamil') f.textContent = ''; }); }
      function tandai(f, pesan) {
        const el = $id(f);
        if (!el) return false;
        const fb = document.querySelector(`[data-fb="${f}"]`) || el.parentElement.querySelector('.invalid-feedback');
        if (el.type !== 'hidden') el.classList.add('is-invalid');
        if (fb) { fb.textContent = pesan; fb.style.display = 'block'; }
        (el.type === 'hidden' ? document.querySelector(`[data-yt="${f}"]`) : el).scrollIntoView({ block: 'center', behavior: 'smooth' });
        if (el.type !== 'hidden') el.focus({ preventScroll: true });
        return true;
      }

      form.addEventListener('submit', async e => {
        e.preventDefault();
        if (BATAL) return;
        bersih();
        const v = f => $id(f).value.trim();
        for (const f of ['anak_lk', 'anak_pr', 'umur_anak_terakhir', 'gpa_g', 'gpa_p', 'gpa_a']) if (v(f) !== '' && !/^\d{1,2}$/.test(v(f))) return tandai(f, 'Harus angka bulat.');
        if (v('gpa_g') !== '' && (+v('gpa_p') || 0) + (+v('gpa_a') || 0) > +v('gpa_g')) return tandai('gpa_p', 'P + A melebihi G.');
        if (v('tekanan_darah') && !/^\d{2,3}\/\d{2,3}$/.test(v('tekanan_darah'))) return tandai('tekanan_darah', 'Format sistole/diastole, mis. 120/80.');
        if (v('tgl_dicabut') && v('tgl_dilayani') && v('tgl_dicabut') < v('tgl_dilayani')) return tandai('tgl_dicabut', 'Tidak boleh sebelum tanggal dilayani.');
        if (v('hamil') === 'ya' && v('metode_pilihan')) return tandai('metode_pilihan', 'Pasien hamil — kosongkan metode KB.');

        const data = { visit_ID: NO };
        FIELDS.forEach(f => { data[f] = $id(f).value ?? ''; });
        const btn = $id('openModal');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan…';
        try {
          const r = await fetch('controller/ranap/saveStatusKB.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
          const res = await r.json().catch(() => ({ status: 'error', message: 'Respon server tidak valid.' }));
          if (res.status !== 'success') {
            if (!(res.field && tandai(res.field, res.message))) Swal.fire('Gagal', res.message || 'Gagal menyimpan.', 'error');
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
