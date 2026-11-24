<?php
/**
 * RME rawat inap — Pemeriksaan Medis (menu "Pemeriksaan Medis" di menu_rme_inap).
 * Simpan: form POST ke halaman ini, diproses controller/visit/assesmen.php (dipakai bersama halaman RME lain),
 * lalu dialihkan (Post/Redirect/Get) agar refresh tidak mengirim ulang data.
 */
$title = 'Pemeriksaan Rawat Inap';
require '../../controller/view.php';
require '../../database/connect.php';
require_once '../../controller/ranap/generalisTubuhService.php';
define('ASESMEN_TANPA_ALERT', true);
// Hasil "Pemeriksaan Generalis Tubuh" disimpan sebagai blok di kolom pemeriksaan_fisik. Textarea di halaman ini
// hanya menampilkan teks bebasnya, jadi blok yang tersimpan disambung kembali sebelum disimpan (agar tidak terhapus).
if (isset($_POST['simpan_pemeriksaan'])) {
  $stBlok = $koneksi->prepare("SELECT pemeriksaan_fisik FROM pasien_visit WHERE visit_ID = ? AND id_customer = ? LIMIT 1");
  $noPost = (string) ($_POST['nomor_visit'] ?? '');
  $idcPost = (string) ($_SESSION['id_customer'] ?? '');
  $stBlok->bind_param('ss', $noPost, $idcPost);
  $stBlok->execute();
  $fisikLama = (string) (($stBlok->get_result()->fetch_row() ?: [''])[0] ?? '');
  $stBlok->close();
  $_POST['pemeriksaan_fisik'] = replaceGeneralisTubuhSection(generalisTubuhTanpaBlok($_POST['pemeriksaan_fisik'] ?? ''), generalisTubuhValues($fisikLama));
}
require '../../controller/visit/assesmen.php';

$no = (string) ($_GET['no'] ?? '');
$rm = (string) ($_GET['rm'] ?? '');
if (isset($_POST['simpan_pemeriksaan'])) {
  $tujuan = 'rme_inap?' . http_build_query(['no' => $no, 'rm' => $rm, 'rme' => $_GET['rme'] ?? 'c', 'simpan' => !empty($asesmenTersimpan) ? 'ok' : 'gagal']);
  header('Location: ' . $tujuan, true, 303);
  exit;
}

$idcHalaman = (string) ($_SESSION['id_customer'] ?? '');
// dibatasi faskes yang login (dulu kunjungan faskes lain bisa dibuka lewat URL)
$check = $koneksi->prepare("SELECT pasien_visit.*, ms_patient.nomor_rm, ms_patient.patient_name FROM pasien_visit
  LEFT JOIN ms_patient ON ms_patient.id_patient = pasien_visit.id_patient
  WHERE pasien_visit.visit_ID = ? AND ms_patient.nomor_rm = ? AND pasien_visit.id_customer = ? LIMIT 1");
$check->bind_param('sss', $no, $rm, $idcHalaman);
$check->execute();
$data = $check->get_result()->fetch_assoc() ?: [];
$check->close();

$h = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
$v = fn($k) => $h($data[$k] ?? '');
// angka 0 dari data lama (mis. TD "0/0", SpO2 0) ditampilkan kosong supaya perawat tahu belum diisi
$angka = fn($k) => (isset($data[$k]) && is_numeric($data[$k]) && (float) $data[$k] > 0) ? $h($data[$k]) : '';
[$sistol, $diastol] = array_pad(explode('/', (string) ($data['tekanan_darah'] ?? '')), 2, '');
$sistol = (int) $sistol > 0 ? (int) $sistol : '';
$diastol = (int) $diastol > 0 ? (int) $diastol : '';
$pilihan = function (array $opsi, ?string $nilai) use ($h) {
  $nilai = trim((string) $nilai);
  $html = '<option value="">— pilih —</option>';
  if ($nilai !== '' && !in_array($nilai, $opsi, true)) $opsi[] = $nilai;   // nilai lama di luar daftar tetap tampil
  foreach ($opsi as $o) $html .= '<option value="' . $h($o) . '"' . ($o === $nilai ? ' selected' : '') . '>' . $h($o) . '</option>';
  return $html;
};
$kodeSekunder = array_values(array_filter(array_map('trim', explode(',', (string) ($data['diagnosa_sekunder'] ?? ''))), 'strlen'));
$statusSimpan = $_GET['simpan'] ?? '';
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .ri-sec { background: #fff; border: 1px solid #e5eaef; border-radius: 14px; padding: 16px 18px; margin-bottom: 14px; }
    .ri-sec-h { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
    .ri-sec-h .ic { width: 34px; height: 34px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
    .ri-sec-h b { font-size: .98rem; color: #0f172a; }
    .ri-sec-h small { display: block; color: #475569; font-size: .76rem; font-weight: 400; }
    .ri .form-label { font-size: .8rem; font-weight: 600; color: #334155; margin-bottom: 3px; }
    .ri .form-control, .ri .form-select { font-size: .9rem; }
    .ri textarea.form-control { min-height: 70px; }
    .ri-vital { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
    .ri-vital .vf { border: 1px solid #edf0f4; border-radius: 10px; padding: 8px 10px; background: #fafbfc; }
    .ri-vital .vf.td { grid-column: span 2; }
    .ri-vital .vf label { font-size: .72rem; color: #475569; font-weight: 600; margin-bottom: 3px; display: block; }
    .ri-vital .vf .input-group-text { font-size: .75rem; color: #475569; background: #fff; }
    .ri-vital .vf .form-control { font-weight: 700; }
    .ri-vital .vf.abnormal .form-control { border-color: #f59e0b; background: #fffbeb; }
    .ri-vital .bmi-box { font-weight: 800; font-size: 1.05rem; color: #0f172a; }
    .ri-vital .bmi-ket { font-size: .76rem; font-weight: 700; border-radius: 6px; padding: 1px 7px; }
    @media (max-width: 575.98px) { .ri-vital { grid-template-columns: 1fr; } .ri-vital .vf.td { grid-column: auto; } }
    .ri-vital .vf .form-control { min-width: 0; }
    .ri-req::after { content: " *"; color: #dc2626; }
    .ri-bar { position: sticky; bottom: 0; z-index: 20; background: rgba(255, 255, 255, .96); border-top: 1px solid #e5eaef; padding: 10px 0; margin-top: 4px; backdrop-filter: blur(6px); }
    .ri-screening { background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 10px; padding: 8px 12px; font-size: .86rem; color: #1f2937; }
    .ri .select2-container--default .select2-selection--multiple { min-height: calc(2.25rem + 2px); border-color: #ced4da; }
  </style>
</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <?php require 'sidebar.php'; ?>
    <div class="body-wrapper">
      <?php require 'navbar.php'; ?>
      <div class="body-wrapper-inner">
        <div class="container-fluid ri">
          <div class="row">
            <?php require 'menu_rme_inap.php'; ?>
          </div>

          <?php if (!$data): ?>
            <div class="alert alert-danger mt-3">Kunjungan tidak ditemukan untuk faskes ini.</div>
          <?php else: ?>
          <?php require 'card-pasien.php'; ?>

          <form id="formPemeriksaan" method="POST" novalidate>
            <input type="hidden" name="nomor_rm" value="<?= $h($rm) ?>">
            <input type="hidden" name="nomor_visit" value="<?= $h($no) ?>">
            <input type="hidden" name="id_patient" id="id_patient" value="<?= $v('id_patient') ?>">
            <input type="hidden" name="simpan_pemeriksaan" value="1">

            <div class="row g-3">
              <div class="col-xl-5">
                <!-- VITAL SIGN -->
                <div class="ri-sec">
                  <div class="ri-sec-h">
                    <span class="ic bg-danger-subtle text-danger"><i class="ti ti-heartbeat"></i></span>
                    <div><b>Tanda Vital</b><small>Diisi perawat saat pasien masuk</small></div>
                  </div>
                  <?php if (trim((string) ($data['catatan_screening'] ?? '')) !== ''): ?>
                    <div class="ri-screening mb-3"><i class="ti ti-clipboard-text me-1 text-muted"></i><b>Catatan screening:</b> <?= $v('catatan_screening') ?></div>
                  <?php endif ?>
                  <div class="mb-3">
                    <label for="kondisi_masuk" class="form-label ri-req">Kondisi masuk</label>
                    <select name="kondisi_masuk" id="kondisi_masuk" class="form-select" required>
                      <?= $pilihan(['Baik', 'Sedang', 'Lemah', 'Buruk', 'Gawat Darurat', 'Tidak Sadar'], $data['kondisi_masuk'] ?? '') ?>
                    </select>
                  </div>
                  <div class="ri-vital">
                    <div class="vf td" data-batas="sistol">
                      <label class="ri-req">Tekanan darah</label>
                      <div class="input-group input-group-sm">
                        <input type="number" inputmode="numeric" id="sistolik" class="form-control" placeholder="Sistol" min="50" max="300" value="<?= $h($sistol) ?>" required>
                        <span class="input-group-text">/</span>
                        <input type="number" inputmode="numeric" id="diastolik" class="form-control" placeholder="Diastol" min="20" max="200" value="<?= $h($diastol) ?>" required>
                        <span class="input-group-text">mmHg</span>
                      </div>
                      <input type="hidden" id="tekanan_darah" name="tekanan_darah" value="<?= $v('tekanan_darah') ?>">
                    </div>
                    <div class="vf" data-min="36" data-max="37.5">
                      <label for="suhu" class="ri-req">Suhu</label>
                      <div class="input-group input-group-sm"><input type="number" inputmode="decimal" step="0.1" id="suhu" name="suhu" class="form-control" min="30" max="45" value="<?= $angka('suhu') ?>" required><span class="input-group-text">°C</span></div>
                    </div>
                    <div class="vf" data-min="60" data-max="100">
                      <label for="nadi" class="ri-req">Nadi</label>
                      <div class="input-group input-group-sm"><input type="number" inputmode="numeric" id="nadi" name="nadi" class="form-control" min="20" max="250" value="<?= $angka('nadi') ?>" required><span class="input-group-text">x/mnt</span></div>
                    </div>
                    <div class="vf" data-min="12" data-max="24">
                      <label for="respirasi" class="ri-req">Respirasi</label>
                      <div class="input-group input-group-sm"><input type="number" inputmode="numeric" id="respirasi" name="respirasi" class="form-control" min="4" max="80" value="<?= $angka('respirasi') ?>" required><span class="input-group-text">x/mnt</span></div>
                    </div>
                    <div class="vf" data-min="95" data-max="100">
                      <label for="saturasi">SpO₂</label>
                      <div class="input-group input-group-sm"><input type="number" inputmode="numeric" id="saturasi" name="saturasi" class="form-control" min="50" max="100" value="<?= $angka('saturasi') ?>"><span class="input-group-text">%</span></div>
                    </div>
                    <div class="vf">
                      <label for="tinggi" class="ri-req">Tinggi badan</label>
                      <div class="input-group input-group-sm"><input type="number" inputmode="decimal" step="0.1" id="tinggi" name="tinggi" class="form-control" min="30" max="250" value="<?= $angka('tinggi_badan') ?>" required><span class="input-group-text">cm</span></div>
                    </div>
                    <div class="vf">
                      <label for="berat" class="ri-req">Berat badan</label>
                      <div class="input-group input-group-sm"><input type="number" inputmode="decimal" step="0.1" id="berat" name="berat" class="form-control" min="0.5" max="350" value="<?= $angka('berat_badan') ?>" required><span class="input-group-text">kg</span></div>
                    </div>
                    <div class="vf td">
                      <label>BMI (otomatis)</label>
                      <div class="d-flex align-items-center gap-2"><span class="bmi-box" id="bmiTampil">—</span><span class="bmi-ket" id="bmiKetTampil"></span></div>
                      <input type="hidden" id="bmi" name="bmi" value="<?= $v('bmi') ?>">
                      <input type="hidden" id="bmi_keterangan" name="bmi_ket" value="<?= $v('bmi_keterangan') ?>">
                    </div>
                  </div>
                  <div class="small text-muted mt-2"><i class="ti ti-info-circle me-1"></i>Kotak kuning = di luar rentang normal dewasa.</div>
                </div>

                <!-- RIWAYAT -->
                <div class="ri-sec">
                  <div class="ri-sec-h">
                    <span class="ic bg-warning-subtle" style="color:#b45309"><i class="ti ti-history"></i></span>
                    <div><b>Riwayat</b><small>Alergi, penyakit &amp; pengobatan sebelumnya</small></div>
                  </div>
                  <div class="mb-3">
                    <label for="riwayat_alergi" class="form-label">Riwayat alergi</label>
                    <textarea id="riwayat_alergi" name="riwayat_alergi" rows="2" class="form-control" placeholder="Tulis &quot;Tidak ada&quot; bila tidak ada alergi"><?= $v('riwayat_alergi') ?></textarea>
                  </div>
                  <div class="mb-3">
                    <label for="riwayat_penyakit_pribadi" class="form-label">Riwayat penyakit dahulu</label>
                    <textarea id="riwayat_penyakit_pribadi" name="riwayat_penyakit_pribadi" rows="2" class="form-control"><?= $v('riwayat_penyakit_pribadi') ?></textarea>
                  </div>
                  <div class="mb-0">
                    <label for="riwayat_pengobatan" class="form-label">Riwayat pengobatan</label>
                    <textarea id="riwayat_pengobatan" name="riwayat_pengobatan" rows="2" class="form-control"><?= $v('riwayat_pengobatan') ?></textarea>
                  </div>
                </div>
              </div>

              <div class="col-xl-7">
                <!-- ANAMNESIS & PEMERIKSAAN -->
                <div class="ri-sec">
                  <div class="ri-sec-h">
                    <span class="ic bg-primary-subtle text-primary"><i class="ti ti-stethoscope"></i></span>
                    <div><b>Anamnesis &amp; Pemeriksaan Dokter</b><small>Subjektif &amp; objektif</small></div>
                  </div>
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label for="keluhan_penyerta" class="form-label ri-req">Keluhan utama</label>
                      <textarea id="keluhan_penyerta" name="keluhan_penyerta" rows="3" class="form-control" required><?= $v('keluhan_penyerta') ?></textarea>
                    </div>
                    <div class="col-md-6">
                      <label for="keluhan_utama" class="form-label ri-req">Anamnesa / keluhan lain</label>
                      <textarea id="keluhan_utama" name="keluhan_utama" rows="3" class="form-control" required><?= $v('anamnesa') ?></textarea>
                    </div>
                    <div class="col-12">
                      <label for="riwayat_penyakit_sekarang" class="form-label">Riwayat penyakit sekarang</label>
                      <textarea id="riwayat_penyakit_sekarang" name="riwayat_penyakit_sekarang" rows="2" class="form-control"><?= $v('riwayat_penyakit_sekarang') ?></textarea>
                    </div>
                    <div class="col-md-6">
                      <label for="pemeriksaan_fisik" class="form-label">Pemeriksaan fisik</label>
                      <textarea id="pemeriksaan_fisik" name="pemeriksaan_fisik" rows="3" class="form-control"><?= $h(generalisTubuhTanpaBlok($data['pemeriksaan_fisik'] ?? '')) ?></textarea>
                      <?php if (strpos((string) ($data['pemeriksaan_fisik'] ?? ''), '[PEMERIKSAAN GENERALIS TUBUH]') !== false): ?>
                        <div class="form-text"><i class="ti ti-link me-1"></i>Hasil <a href="module/admin/pemeriksaan_generalis_tubuh?<?= $h(http_build_query(['no' => $no, 'rm' => $rm, 'rme' => 'c'])) ?>">Pemeriksaan Generalis Tubuh</a> tersimpan terpisah dan tetap aman.</div>
                      <?php endif ?>
                    </div>
                    <div class="col-md-6">
                      <label for="pemeriksaan_fungsional" class="form-label">Pemeriksaan fungsional</label>
                      <textarea id="pemeriksaan_fungsional" name="pemeriksaan_fungsional" rows="3" class="form-control"><?= $v('pemeriksaan_fungsional') ?></textarea>
                    </div>
                  </div>
                </div>

                <!-- DIAGNOSA & RENCANA -->
                <div class="ri-sec">
                  <div class="ri-sec-h">
                    <span class="ic bg-success-subtle text-success"><i class="ti ti-clipboard-check"></i></span>
                    <div><b>Diagnosa &amp; Rencana</b><small>Asesmen &amp; plan</small></div>
                  </div>
                  <div class="row g-3">
                    <div class="col-12">
                      <label for="diagnosa" class="form-label ri-req">Diagnosa utama (ICD-10)</label>
                      <select id="diagnosa" name="diagnosa" class="form-select" style="width:100%" required></select>
                    </div>
                    <div class="col-12">
                      <label for="diagnosa_sekunder" class="form-label">Diagnosa sekunder (ICD-10)</label>
                      <input type="hidden" name="diagnosa_sekunder_ada" value="1"><!-- penanda: halaman ini mengelola diagnosa sekunder (boleh dikosongkan) -->
                      <select id="diagnosa_sekunder" name="diagnosa_sekunder[]" class="form-select" multiple style="width:100%"></select>
                    </div>
                    <div class="col-12">
                      <label for="tindakan" class="form-label">Tindakan / terapi / instruksi / rencana rawat</label>
                      <textarea id="tindakan" name="tindakan" rows="3" class="form-control"><?= $v('tindakan') ?></textarea>
                    </div>
                    <div class="col-md-7">
                      <label for="edukasi" class="form-label">Edukasi</label>
                      <textarea id="edukasi" name="edukasi" rows="2" class="form-control"><?= $v('edukasi') ?></textarea>
                    </div>
                    <div class="col-md-5">
                      <label for="cara_keluar" class="form-label ri-req">Cara keluar</label>
                      <select name="cara_keluar" id="cara_keluar" class="form-select" required>
                        <?= $pilihan(['Rawat Inap', 'Berobat Jalan', 'Rujuk', 'Meninggal'], $data['kondisi_keluar'] ?? '') ?>
                      </select>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="ri-bar">
              <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="small text-muted me-auto" id="riStatus"><i class="ti ti-pencil me-1"></i>Belum ada perubahan</span>
                <button type="submit" class="btn btn-primary px-4" id="btnSimpan"><i class="ti ti-device-floppy me-1"></i>Simpan Pemeriksaan</button>
              </div>
            </div>
          </form>
          <?php endif ?>
        </div>
      </div>
    </div>
  </div>

  <?php require 'library.php'; ?>
  <script>
    (function() {
      const form = document.getElementById('formPemeriksaan');
      const statusSimpan = <?= json_encode($statusSimpan) ?>;
      if (statusSimpan) {
        Swal.fire(statusSimpan === 'ok' ? { icon: 'success', title: 'Pemeriksaan disimpan', timer: 1500, showConfirmButton: false } : { icon: 'error', title: 'Gagal menyimpan pemeriksaan' });
        const u = new URL(location.href); u.searchParams.delete('simpan'); history.replaceState(null, '', u);
      }
      if (!form) return;
      const $id = id => document.getElementById(id);
      let berubah = false, menyimpan = false;

      // ---------- BMI & penanda vital di luar rentang ----------
      function hitungBMI() {
        const tb = parseFloat($id('tinggi').value), bb = parseFloat($id('berat').value);
        if (!(tb > 0 && bb > 0)) { $id('bmi').value = ''; $id('bmi_keterangan').value = ''; $id('bmiTampil').textContent = '—'; $id('bmiKetTampil').textContent = ''; return; }
        const bmi = bb / Math.pow(tb / 100, 2);
        const [ket, bg, fg] = bmi < 18.5 ? ['Kurus', '#fef3c7', '#78350f'] : bmi < 25 ? ['Normal', '#dcfce7', '#166534'] : bmi < 30 ? ['Overweight', '#fef3c7', '#78350f'] : ['Obesitas', '#fee2e2', '#991b1b'];
        $id('bmi').value = bmi.toFixed(2); $id('bmi_keterangan').value = ket;
        $id('bmiTampil').textContent = bmi.toFixed(1).replace('.', ',');
        Object.assign($id('bmiKetTampil').style, { background: bg, color: fg });
        $id('bmiKetTampil').textContent = ket;
      }
      function tandaiVital() {
        form.querySelectorAll('.vf[data-min]').forEach(vf => {
          const el = vf.querySelector('input'), v = parseFloat(el.value);
          vf.classList.toggle('abnormal', el.value !== '' && (v < +vf.dataset.min || v > +vf.dataset.max));
        });
        const s = parseFloat($id('sistolik').value), d = parseFloat($id('diastolik').value);
        form.querySelector('.vf.td[data-batas]').classList.toggle('abnormal', (s && (s >= 140 || s < 90)) || (d && (d >= 90 || d < 60)));
      }
      ['tinggi', 'berat'].forEach(i => $id(i).addEventListener('input', hitungBMI));
      form.querySelectorAll('.ri-vital input').forEach(el => el.addEventListener('input', tandaiVital));
      hitungBMI(); tandaiVital();

      // ---------- ICD-10 ----------
      const icd = (el, opsi) => $(el).select2(Object.assign({
        width: '100%', minimumInputLength: 2, language: { inputTooShort: () => 'Ketik minimal 2 huruf (kode atau nama penyakit)' },
        ajax: { url: 'controller/visit/getICD10.php', dataType: 'json', delay: 300, data: p => ({ search: p.term }), processResults: d => ({ results: d }) }
      }, opsi));
      icd('#diagnosa', { placeholder: 'Cari kode / nama diagnosa…', allowClear: true });
      icd('#diagnosa_sekunder', { placeholder: 'Cari diagnosa sekunder…', multiple: true });
      async function isiIcd(el, kode) {
        try {
          const r = await fetch('controller/visit/getICD10.php?search=' + encodeURIComponent(kode));
          const d = await r.json();
          const it = d.find(x => x.id === kode) || { id: kode, text: kode };
          $(el).append(new Option(it.text, it.id, true, true)).trigger('change');
        } catch (e) { $(el).append(new Option(kode, kode, true, true)).trigger('change'); }
      }
      const diagAwal = <?= json_encode(trim((string) ($data['diagnosa'] ?? ''))) ?>;
      const sekAwal = <?= json_encode($kodeSekunder) ?>;
      (async () => {
        if (diagAwal) await isiIcd('#diagnosa', diagAwal);
        for (const k of sekAwal) await isiIcd('#diagnosa_sekunder', k);
        berubah = false; $id('riStatus').innerHTML = '<i class="ti ti-pencil me-1"></i>Belum ada perubahan';
      })();

      // ---------- perubahan belum disimpan ----------
      const tandai = () => { if (!berubah) { berubah = true; $id('riStatus').innerHTML = '<span class="text-warning-emphasis"><i class="ti ti-alert-circle me-1"></i>Ada perubahan belum disimpan</span>'; } };
      form.addEventListener('input', tandai);
      $('#diagnosa, #diagnosa_sekunder').on('select2:select select2:unselect select2:clear', tandai);
      window.addEventListener('beforeunload', e => { if (berubah && !menyimpan) { e.preventDefault(); e.returnValue = ''; } });

      // ---------- validasi & simpan ----------
      form.addEventListener('submit', function(e) {
        form.querySelectorAll('.is-invalid').forEach(x => x.classList.remove('is-invalid'));
        const s = $id('sistolik').value.trim(), d = $id('diastolik').value.trim();
        $id('tekanan_darah').value = s && d ? `${s}/${d}` : '';
        const kurang = [];
        form.querySelectorAll('[required]').forEach(el => {
          const kosong = el.tagName === 'SELECT' ? !$(el).val() : el.value.trim() === '';
          const salah = !kosong && el.type === 'number' && !el.checkValidity();
          if (kosong || salah) {
            el.classList.add('is-invalid');
            const lbl = form.querySelector(`label[for="${el.id}"]`) || el.closest('.vf')?.querySelector('label');
            kurang.push((lbl ? lbl.textContent.replace('*', '').trim() : el.id) + (salah ? ' (nilai tidak wajar)' : ''));
          }
        });
        if (kurang.length) {
          e.preventDefault();
          Swal.fire({ icon: 'warning', title: 'Lengkapi data', html: [...new Set(kurang)].map(x => '• ' + x).join('<br>') });
          form.querySelector('.is-invalid')?.scrollIntoView({ block: 'center', behavior: 'smooth' });
          return;
        }
        menyimpan = true;
        $id('btnSimpan').disabled = true;
        $id('btnSimpan').innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan…';
      });
    })();
  </script>
</body>

</html>
