<?php
/**
 * Triase Kegawatdaruratan (menu RME: ?rme=a / b / c).
 * Data: controller/ranap/getTriase.php (muat) · controller/ranap/saveTriase.php (simpan) · cetak: module/admin/print/formulir_triase.
 */
$title = 'Triase Kegawatdaruratan';
require '../../controller/view.php';
$no = (string) ($_GET['no'] ?? '');
$rm = (string) ($_GET['rm'] ?? '');
$rme = (string) ($_GET['rme'] ?? '');
$idcHalaman = (int) ($_SESSION['id_customer'] ?? 0);
// data awal dari kunjungan (+ tanggal/jam masuk ranap bila ada); dulu query tanpa batas faskes & rawan SQL injection
$st = $koneksi->prepare("SELECT pv.visit_date, pv.visit_time, pv.anamnesa, pv.keluhan_penyerta, pv.tekanan_darah, pv.nadi, pv.respirasi, pv.suhu, pv.saturasi,
      pr.ranap_date, pr.ranap_time
   FROM pasien_visit pv
   JOIN ms_patient mp ON mp.id_patient = pv.id_patient
   LEFT JOIN permintaan_ranap pr ON pr.visit_ID_inpatient = pv.visit_ID
   WHERE pv.visit_ID = ? AND mp.nomor_rm = ? AND pv.id_customer = ? LIMIT 1");
$st->bind_param('ssi', $no, $rm, $idcHalaman);
$st->execute();
$kv = $st->get_result()->fetch_assoc() ?: null;
$st->close();
// triase tersimpan dimuat langsung di server -> form & kategori ATS tampil seketika (tanpa menunggu fetch)
$tr = null;
if ($kv) {
  $st = $koneksi->prepare("SELECT * FROM pasien_triase WHERE visit_ID = ? AND id_customer = ? ORDER BY id_triase DESC LIMIT 1");
  $st->bind_param('si', $no, $idcHalaman);
  $st->execute();
  $tr = $st->get_result()->fetch_assoc() ?: null;
  $st->close();
}
$h = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
$pos = fn($v) => (is_numeric($v) && (float) $v > 0) ? $v : '';
$awal = $kv ? [
  'tanggal_masuk' => $kv['ranap_date'] ?: $kv['visit_date'],
  'jam_masuk' => substr((string) ($kv['ranap_time'] ?: $kv['visit_time']), 0, 5),
  'keluhan_utama' => trim((string) ($kv['keluhan_penyerta'] ?: $kv['anamnesa'])),
  'tekanan_darah' => preg_match('/^[1-9]\d{1,2}\/[1-9]\d{1,2}$/', (string) $kv['tekanan_darah']) ? $kv['tekanan_darah'] : '',
  'nadi' => $pos($kv['nadi']), 'rr' => $pos($kv['respirasi']), 'suhu' => $pos($kv['suhu']), 'spo2' => $pos($kv['saturasi']),
] : [];
if ($tr) {
  $isi = fn($v) => $v !== null && (string) $v !== '' && (string) $v !== '0';
  if ($isi($tr['tanggal_masuk'])) $awal['tanggal_masuk'] = $tr['tanggal_masuk'];
  if ($isi($tr['jam_masuk'])) $awal['jam_masuk'] = substr((string) $tr['jam_masuk'], 0, 5);
  if (trim((string) $tr['keluhan_utama']) !== '') $awal['keluhan_utama'] = $tr['keluhan_utama'];
  if (preg_match('/^\d{2,3}\/\d{2,3}$/', (string) $tr['tekanan_darah'])) $awal['tekanan_darah'] = $tr['tekanan_darah'];
  foreach (['nadi' => 'nadi', 'rr' => 'rr', 'suhu' => 'suhu', 'spo2' => 'spo2'] as $k => $c) if ($isi($tr[$c])) $awal[$k] = $tr[$c];
}
$trAts = (string) ($tr['triase'] ?? '');
$trItems = array_map(fn($x) => trim((string) $x), (array) (json_decode((string) ($tr['referensi_triase'] ?? '[]'), true) ?: []));
$trGcs = ['e' => (int) ($tr['gcs_e'] ?? 0) ?: 4, 'v' => (int) ($tr['gcs_v'] ?? 0) ?: 5, 'm' => (int) ($tr['gcs_m'] ?? 0) ?: 6];
$trNyeri = ($tr && $tr['skala_nyeri'] !== null && $tr['skala_nyeri'] !== '') ? (int) $tr['skala_nyeri'] : null;
$sel = fn($a, $b) => (string) $a === (string) $b ? ' selected' : '';
$ATS = [
  'ATS 1' => ['Resusitasi', 'Segera', '#b91c1c', ['A. Airway' => ['Sumbatan Jalan Nafas'], 'B. Breathing' => ['Henti Nafas'], 'C. Circulation' => ['Henti Jantung'], 'D. Disability' => ['Nyeri berat tidak respon obat'], 'E. Exposure' => ['Kejang berkelanjutan'], 'F. Psikiatri' => ['Gangguan perilaku berat mengancam diri & orang lain']]],
  'ATS 2' => ['Emergensi', '≤ 10 menit', '#c2410c', ['A. Airway' => ['Tidak ada sumbatan'], 'B. Breathing' => ['RR < 10 x/menit, distress napas berat'], 'C. Circulation' => ['Sistolik < 80 mmHg'], 'D. Disability' => ['Nyeri sedang'], 'E. Exposure' => ['Nyeri dada tipikal', 'Defisit neurologis'], 'F. Psikiatri' => ['Datang dengan restrain']]],
  'ATS 3' => ['Urgensi', '≤ 30 menit', '#a16207', ['A. Airway' => ['Tidak ada sumbatan'], 'B. Breathing' => ['Takipnea / distress sedang'], 'C. Circulation' => ['Gangguan sirkulasi (akral dingin, nadi <50 atau>150)'], 'D. Disability' => ['Cedera kepala ringan'], 'E. Exposure' => ['Nyeri hebat', 'Multiple trauma'], 'F. Psikiatri' => ['Agresif fisik', 'Ancaman bunuh diri']]],
  'ATS 4' => ['Non-urgen', '≤ 60 menit', '#15803d', ['A. Airway' => ['Tidak ada sumbatan'], 'B. Breathing' => ['Dipsnea ringan'], 'C. Circulation' => ['Muntah / diare tanda dehidrasi ringan'], 'E. Exposure' => ['Luka kecil'], 'F. Psikiatri' => ['Keluhan minor']]],
];
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .tr .sec { background: #fff; border: 1px solid #e5eaef; border-radius: 14px; padding: 16px 18px; margin-bottom: 14px; }
    .tr .sec-h { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
    .tr .sec-h .ic { width: 34px; height: 34px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
    .tr .sec-h b { font-size: .98rem; color: #0f172a; }
    .tr .sec-h small { display: block; color: #475569; font-size: .76rem; }
    .tr .form-label { font-size: .8rem; font-weight: 600; color: #334155; margin-bottom: 3px; }
    .tr .vital { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
    .tr .vital .vf { border: 1px solid #edf0f4; border-radius: 10px; padding: 8px 10px; background: #fafbfc; }
    .tr .vital .vf.td { grid-column: span 2; }
    .tr .vital .vf label { font-size: .72rem; color: #475569; font-weight: 600; display: block; margin-bottom: 3px; }
    .tr .vital .form-control { font-weight: 700; min-width: 0; }
    .tr .vital .input-group-text { font-size: .74rem; background: #fff; color: #475569; }
    .tr .vital .vf.abnormal .form-control { border-color: #f59e0b; background: #fffbeb; }
    @media (max-width: 575.98px) { .tr .vital { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    .tr .skor { font-size: 1.6rem; font-weight: 800; line-height: 1; color: #0f172a; }
    .tr .lbl { display: inline-block; font-size: .74rem; font-weight: 700; border-radius: 6px; padding: 2px 8px; }
    .tr .nyeri-skala { display: grid; grid-template-columns: repeat(11, 1fr); gap: 4px; }
    .tr .nyeri-skala button { border: 1px solid #cbd5e1; background: #fff; border-radius: 8px; padding: 6px 0; font-weight: 700; font-size: .85rem; color: #1f2937; }
    .tr .nyeri-skala button.on { color: #fff; border-color: transparent; }
    .tr .ats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; }
    .tr .ats button { text-align: left; border: 2px solid #e5eaef; background: #fff; border-radius: 12px; padding: 10px 12px; }
    .tr .ats button b { display: block; font-size: .95rem; }
    .tr .ats button small { color: #475569; font-size: .74rem; }
    .tr .ats button.on { color: #fff; }
    .tr .ats button.on small { color: rgba(255, 255, 255, .9); }
    @media (max-width: 767.98px) { .tr .ats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    .tr .ceklis { border: 1px solid #e5eaef; border-radius: 12px; padding: 12px 14px; margin-top: 12px; }
    .tr .ceklis .grp { font-size: .74rem; font-weight: 700; color: #475569; text-transform: uppercase; margin-top: 8px; }
    .tr .ceklis .form-check { margin: 2px 0; }
    .tr .bar { position: sticky; bottom: 0; z-index: 20; background: rgba(255, 255, 255, .96); border-top: 1px solid #e5eaef; padding: 10px 0; backdrop-filter: blur(6px); }
  </style>
</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <?php require 'sidebar.php'; ?>
    <div class="body-wrapper">
      <?php require 'navbar.php'; ?>
      <div class="body-wrapper-inner">
        <div class="container-fluid tr">
          <?php
          if ($rme === 'a') include 'menu_rme.php';
          elseif ($rme === 'b') include 'menu_rmeb.php';
          elseif ($rme === 'c') include 'menu_rme_inap.php';
          ?>
          <?php if (!$kv): ?>
            <div class="alert alert-danger mt-3">Kunjungan tidak ditemukan untuk faskes ini.</div>
          <?php else: ?>
          <?php require 'card-pasien.php'; ?>

          <?php if ($tr): $info = $ATS[$trAts] ?? null; $gcsT = $trGcs['e'] + $trGcs['v'] + $trGcs['m']; ?>
            <div class="sec d-flex flex-wrap align-items-center gap-3" style="border-left:6px solid <?= $h($info[2] ?? '#94a3b8') ?>">
              <div class="text-center px-2" style="min-width:110px">
                <div class="small text-muted">Hasil triase</div>
                <div style="font-size:1.6rem;font-weight:800;line-height:1.1;color:<?= $h($info[2] ?? '#475569') ?>"><?= $h($trAts ?: 'Belum') ?></div>
                <div class="fw-semibold" style="color:<?= $h($info[2] ?? '#475569') ?>"><?= $h($info[0] ?? 'kategori belum dipilih') ?></div>
              </div>
              <div class="flex-grow-1 small" style="min-width:240px">
                <?php if ($info): ?><div><b>Target respon:</b> <?= $h($info[1]) ?></div><?php endif ?>
                <?php if ($trItems): ?><div><b>Temuan:</b> <?= $h(implode(', ', $trItems)) ?></div><?php endif ?>
                <div class="text-muted mt-1">GCS <?= $gcsT ?> (E<?= $trGcs['e'] ?> V<?= $trGcs['v'] ?> M<?= $trGcs['m'] ?>)<?= $trNyeri !== null ? ' · nyeri ' . $trNyeri . '/10' : '' ?><?= $awal['tekanan_darah'] ? ' · TD ' . $h($awal['tekanan_darah']) : '' ?><?= $awal['nadi'] ? ' · nadi ' . $h($awal['nadi']) : '' ?><?= $awal['spo2'] ? ' · SpO₂ ' . $h($awal['spo2']) . '%' : '' ?></div>
              </div>
              <div class="small text-muted text-end">Disimpan<br><b class="text-dark"><?= $h(substr((string) ($tr['updated_at'] ?: $tr['created_at']), 0, 16)) ?></b></div>
            </div>
          <?php else: ?>
            <div class="alert alert-warning py-2 small"><i class="ti ti-alert-triangle me-1"></i>Triase pasien ini belum pernah disimpan. Vital diambil dari pemeriksaan kunjungan.</div>
          <?php endif ?>

          <div class="row g-3">
            <div class="col-xl-6">
              <div class="sec">
                <div class="sec-h"><span class="ic bg-primary-subtle text-primary"><i class="ti ti-door-enter"></i></span>
                  <div><b>Kedatangan</b><small>Waktu masuk &amp; keluhan</small></div><span class="ms-auto small text-muted" id="sumber"></span></div>
                <div class="row g-2">
                  <div class="col-6 col-md-4"><label class="form-label" for="tanggal_masuk">Tanggal masuk</label><input type="date" id="tanggal_masuk" class="form-control" value="<?= $h($awal['tanggal_masuk']) ?>"></div>
                  <div class="col-6 col-md-3"><label class="form-label" for="jam_masuk">Jam masuk</label><input type="time" id="jam_masuk" class="form-control" value="<?= $h($awal['jam_masuk']) ?>"></div>
                  <div class="col-md-5"><label class="form-label" for="anamnesa_choice">Anamnesis</label>
                    <select id="anamnesa_choice" class="form-select"><option value="Auto Anamnesa">Auto anamnesa (pasien)</option><option value="Allo Anamnesa"<?= $sel('Allo Anamnesa', $tr['anamnesa_choice'] ?? '') ?>>Allo anamnesa (keluarga/pengantar)</option></select></div>
                  <div class="col-12"><label class="form-label" for="keluhan_utama">Keluhan utama</label><textarea id="keluhan_utama" rows="2" class="form-control"><?= $h($awal['keluhan_utama']) ?></textarea></div>
                </div>
              </div>

              <div class="sec">
                <div class="sec-h"><span class="ic bg-danger-subtle text-danger"><i class="ti ti-heartbeat"></i></span>
                  <div><b>Tanda vital</b><small>Terisi dari pemeriksaan kunjungan, bisa disesuaikan</small></div></div>
                <div class="vital">
                  <div class="vf td" data-td><label>Tekanan darah</label>
                    <div class="input-group input-group-sm"><input type="number" id="sistolik" class="form-control" placeholder="Sistol" min="0" max="300"><span class="input-group-text">/</span><input type="number" id="diastolik" class="form-control" placeholder="Diastol" min="0" max="200"><span class="input-group-text">mmHg</span></div></div>
                  <div class="vf" data-min="60" data-max="100"><label>Nadi</label><div class="input-group input-group-sm"><input type="number" id="nadi" class="form-control" value="<?= $h($awal['nadi']) ?>"><span class="input-group-text">x/m</span></div></div>
                  <div class="vf" data-min="12" data-max="24"><label>Respirasi</label><div class="input-group input-group-sm"><input type="number" id="rr" class="form-control" value="<?= $h($awal['rr']) ?>"><span class="input-group-text">x/m</span></div></div>
                  <div class="vf" data-min="36" data-max="37.5"><label>Suhu</label><div class="input-group input-group-sm"><input type="number" step="0.1" id="suhu" class="form-control" value="<?= $h($awal['suhu']) ?>"><span class="input-group-text">°C</span></div></div>
                  <div class="vf" data-min="95" data-max="100"><label>SpO₂</label><div class="input-group input-group-sm"><input type="number" id="spo2" class="form-control" value="<?= $h($awal['spo2']) ?>"><span class="input-group-text">%</span></div></div>
                </div>
              </div>

              <div class="sec">
                <div class="sec-h"><span class="ic bg-info-subtle text-info"><i class="ti ti-brain"></i></span>
                  <div><b>GCS &amp; skala nyeri</b><small>Tingkat kesadaran dan nyeri</small></div></div>
                <div class="row g-2 align-items-end">
                  <div class="col-md-3"><label class="form-label" for="gcs_e">Mata (E)</label><select id="gcs_e" class="form-select form-select-sm">
                    <option value="4"<?= $sel(4, $trGcs['e']) ?>>4 · Spontan</option><option value="3"<?= $sel(3, $trGcs['e']) ?>>3 · Suara</option><option value="2"<?= $sel(2, $trGcs['e']) ?>>2 · Nyeri</option><option value="1"<?= $sel(1, $trGcs['e']) ?>>1 · Tidak ada</option></select></div>
                  <div class="col-md-3"><label class="form-label" for="gcs_v">Verbal (V)</label><select id="gcs_v" class="form-select form-select-sm">
                    <option value="5"<?= $sel(5, $trGcs['v']) ?>>5 · Orientasi baik</option><option value="4"<?= $sel(4, $trGcs['v']) ?>>4 · Bingung</option><option value="3"<?= $sel(3, $trGcs['v']) ?>>3 · Kata tidak tepat</option><option value="2"<?= $sel(2, $trGcs['v']) ?>>2 · Suara tidak jelas</option><option value="1"<?= $sel(1, $trGcs['v']) ?>>1 · Tidak ada</option></select></div>
                  <div class="col-md-3"><label class="form-label" for="gcs_m">Motorik (M)</label><select id="gcs_m" class="form-select form-select-sm">
                    <option value="6"<?= $sel(6, $trGcs['m']) ?>>6 · Mengikuti perintah</option><option value="5"<?= $sel(5, $trGcs['m']) ?>>5 · Lokalisir nyeri</option><option value="4"<?= $sel(4, $trGcs['m']) ?>>4 · Menarik dari nyeri</option><option value="3"<?= $sel(3, $trGcs['m']) ?>>3 · Fleksi abnormal</option><option value="2"<?= $sel(2, $trGcs['m']) ?>>2 · Ekstensi abnormal</option><option value="1"<?= $sel(1, $trGcs['m']) ?>>1 · Tidak ada</option></select></div>
                  <div class="col-md-3 text-md-end"><div class="skor" id="gcsTotal">15</div><span class="lbl" id="gcsKet"></span></div>
                </div>
                <label class="form-label mt-3">Skala nyeri (0 = tidak nyeri, 10 = nyeri terberat) <span class="lbl ms-1" id="nyeriKet"></span></label>
                <div class="nyeri-skala" id="nyeriSkala"></div>
              </div>
            </div>

            <div class="col-xl-6">
              <div class="sec">
                <div class="sec-h"><span class="ic bg-warning-subtle" style="color:#b45309"><i class="ti ti-urgent"></i></span>
                  <div><b>Kategori triase (ATS)</b><small>Pilih kategori, lalu centang temuan yang sesuai</small></div></div>
                <div class="ats" id="atsPilih">
                  <?php foreach ($ATS as $kode => [$nama, $waktu, $warna]): ?>
                    <button type="button" data-ats="<?= $h($kode) ?>" data-warna="<?= $h($warna) ?>"<?= $kode === $trAts ? ' class="on" style="background:' . $h($warna) . ';border-color:' . $h($warna) . '"' : '' ?>><b style="color:<?= $kode === $trAts ? '#fff' : $h($warna) ?>"><?= $h($kode) ?></b><span class="fw-semibold"><?= $h($nama) ?></span><br><small>Respon <?= $h($waktu) ?></small></button>
                  <?php endforeach ?>
                </div>
                <?php foreach ($ATS as $kode => [$nama, , $warna, $grup]): $kls = strtolower(str_replace(' ', '', $kode)); ?>
                  <div class="ceklis" data-box="<?= $h($kode) ?>"<?= $kode === $trAts ? '' : ' hidden' ?> style="border-left:4px solid <?= $h($warna) ?>">
                    <div class="fw-bold" style="color:<?= $h($warna) ?>"><?= $h($kode) ?> · <?= $h($nama) ?></div>
                    <?php foreach ($grup as $g => $items): ?>
                      <div class="grp"><?= $h($g) ?></div>
                      <?php foreach ($items as $i => $it): $idc = $kls . '_' . md5($g . $it); ?>
                        <div class="form-check"><input class="form-check-input <?= $kls ?>" type="checkbox" id="<?= $idc ?>" value="<?= $h($it) ?>"<?= $kode === $trAts && in_array($it, $trItems, true) ? ' checked' : '' ?>><label class="form-check-label" for="<?= $idc ?>"><?= $h($it) ?></label></div>
                      <?php endforeach ?>
                    <?php endforeach ?>
                  </div>
                <?php endforeach ?>
                <div class="small text-muted mt-2" id="atsKosong"<?= $trAts !== '' ? ' hidden' : '' ?>><i class="ti ti-info-circle me-1"></i>Belum ada kategori dipilih.</div>
              </div>

              <div class="sec">
                <div class="sec-h"><span class="ic bg-secondary-subtle text-dark"><i class="ti ti-notes"></i></span><div><b>Catatan tambahan</b></div></div>
                <textarea id="catatan" rows="3" class="form-control"><?= $h($tr['catatan'] ?? '') ?></textarea>
              </div>
            </div>
          </div>

          <div class="bar">
            <div class="d-flex flex-wrap align-items-center gap-2">
              <span class="small text-muted me-auto" id="status"><i class="ti ti-loader-2 me-1"></i>Memuat data triase…</span>
              <a class="btn btn-outline-dark" id="btnCetak" target="_blank" href="module/admin/print/formulir_triase?<?= $h(http_build_query(['no' => $no, 'rm' => $rm])) ?>"><i class="ti ti-printer me-1"></i>Cetak</a>
              <button type="button" class="btn btn-primary px-4" id="btnSimpan"><i class="ti ti-device-floppy me-1"></i>Simpan triase</button>
            </div>
          </div>
          <?php endif ?>
        </div>
      </div>
    </div>
  </div>

  <?php require 'library.php'; ?>
  <script>
    (function() {
      const $id = id => document.getElementById(id);
      if (!$id('btnSimpan')) return;
      const NO = <?= json_encode($no) ?>, RM = <?= json_encode($rm) ?>;
      const TD_AWAL = <?= json_encode($awal['tekanan_darah']) ?>;
      let berubah = false, nyeri = null, ats = '';

      // ---------- GCS ----------
      function gcs() {
        const t = +$id('gcs_e').value + +$id('gcs_v').value + +$id('gcs_m').value;
        $id('gcsTotal').textContent = t;
        const [k, bg, fg] = t >= 15 ? ['Sadar penuh', '#dcfce7', '#166534'] : t >= 13 ? ['Cedera ringan', '#fef3c7', '#78350f'] : t >= 9 ? ['Cedera sedang', '#ffedd5', '#9a3412'] : ['Cedera berat / koma', '#fee2e2', '#991b1b'];
        Object.assign($id('gcsKet').style, { background: bg, color: fg });
        $id('gcsKet').textContent = k;
        return t;
      }
      ['gcs_e', 'gcs_v', 'gcs_m'].forEach(i => $id(i).addEventListener('change', gcs));

      // ---------- skala nyeri ----------
      const warnaNyeri = n => n === 0 ? '#15803d' : n <= 3 ? '#65a30d' : n <= 6 ? '#d97706' : '#b91c1c';
      const ketNyeri = n => n === 0 ? 'Tidak nyeri' : n <= 3 ? 'Ringan' : n <= 6 ? 'Sedang' : 'Berat';
      $id('nyeriSkala').innerHTML = Array.from({ length: 11 }, (_, i) => `<button type="button" data-n="${i}">${i}</button>`).join('');
      function setNyeri(n) {
        nyeri = n;
        $id('nyeriSkala').querySelectorAll('button').forEach(b => { const on = +b.dataset.n === n; b.classList.toggle('on', on); b.style.background = on ? warnaNyeri(n) : ''; });
        if (n === null) { $id('nyeriKet').textContent = 'belum dipilih'; Object.assign($id('nyeriKet').style, { background: '#f1f5f9', color: '#475569' }); return; }
        $id('nyeriKet').textContent = `${n} · ${ketNyeri(n)}`;
        Object.assign($id('nyeriKet').style, { background: warnaNyeri(n), color: '#fff' });
      }
      $id('nyeriSkala').addEventListener('click', e => { const b = e.target.closest('button'); if (b) { setNyeri(nyeri === +b.dataset.n ? null : +b.dataset.n); tandai(); } });

      // ---------- tanda vital ----------
      function vital() {
        document.querySelectorAll('.vital .vf[data-min]').forEach(vf => { const el = vf.querySelector('input'), v = parseFloat(el.value); vf.classList.toggle('abnormal', el.value !== '' && (v < +vf.dataset.min || v > +vf.dataset.max)); });
        const s = +$id('sistolik').value, d = +$id('diastolik').value;
        document.querySelector('.vital [data-td]').classList.toggle('abnormal', (s && (s >= 140 || s < 90)) || (d && (d >= 90 || d < 60)));
      }
      function setTD(td) { const m = String(td || '').match(/^(\d{2,3})\/(\d{2,3})$/); $id('sistolik').value = m ? m[1] : ''; $id('diastolik').value = m ? m[2] : ''; vital(); }
      document.querySelectorAll('.vital input').forEach(el => el.addEventListener('input', vital));

      // ---------- ATS ----------
      function setAts(k, items) {
        ats = k || '';
        document.querySelectorAll('#atsPilih button').forEach(b => { const on = b.dataset.ats === ats; b.classList.toggle('on', on); b.style.background = on ? b.dataset.warna : ''; b.style.borderColor = on ? b.dataset.warna : ''; b.querySelector('b').style.color = on ? '#fff' : b.dataset.warna; });
        document.querySelectorAll('[data-box]').forEach(box => { box.hidden = box.dataset.box !== ats; });
        $id('atsKosong').hidden = !!ats;
        if (items) {
          const kls = ats.replace(' ', '').toLowerCase();
          const pilih = items.map(x => String(x).trim());   // data lama ada yang berspasi di depan (" Sistolik < 80 mmHg")
          document.querySelectorAll('.' + kls).forEach(cb => { cb.checked = pilih.includes(cb.value.trim()); });
        }
      }
      $id('atsPilih').addEventListener('click', e => { const b = e.target.closest('button'); if (!b) return; setAts(ats === b.dataset.ats ? '' : b.dataset.ats); tandai(); });

      // ---------- perubahan ----------
      function tandai() { if (berubah) return; berubah = true; $id('status').innerHTML = '<span class="text-warning-emphasis"><i class="ti ti-alert-circle me-1"></i>Ada perubahan belum disimpan</span>'; }
      document.querySelector('.tr').addEventListener('input', tandai);
      document.querySelector('.tr').addEventListener('change', e => { if (e.target.matches('select, input[type=checkbox], input[type=date], input[type=time]')) tandai(); });
      window.addEventListener('beforeunload', e => { if (berubah) { e.preventDefault(); e.returnValue = ''; } });

      // ---------- muat ----------
      // data awal sudah dirender server (termasuk triase tersimpan) -> cukup sinkronkan tampilan
      setTD(TD_AWAL); gcs(); setNyeri(<?= json_encode($trNyeri) ?>); vital();
      ats = <?= json_encode($trAts) ?>;
      $id('sumber').textContent = <?= json_encode($tr ? 'Data triase tersimpan' : 'Triase baru') ?>;
      $id('status').innerHTML = '<i class="ti ti-pencil me-1"></i>Belum ada perubahan';

      // ---------- simpan ----------
      async function simpan() {
        const s = $id('sistolik').value.trim(), d = $id('diastolik').value.trim();
        if ((s && !d) || (!s && d)) { Swal.fire('Lengkapi data', 'Sistol dan diastol harus diisi berpasangan.', 'warning'); return false; }
        if (!ats) { const r = await Swal.fire({ icon: 'warning', title: 'Kategori triase belum dipilih', text: 'Tetap simpan tanpa kategori ATS?', showCancelButton: true, confirmButtonText: 'Tetap simpan', cancelButtonText: 'Pilih dulu' }); if (!r.isConfirmed) return false; }
        const kls = ats.replace(' ', '').toLowerCase();
        const data = {
          visit_ID: NO, nomor_rm: RM,
          tanggal_masuk: $id('tanggal_masuk').value, jam_masuk: $id('jam_masuk').value, keluhan_utama: $id('keluhan_utama').value,
          anamnesa_choice: $id('anamnesa_choice').value, tekanan_darah: s && d ? `${s}/${d}` : '',
          nadi: $id('nadi').value, rr: $id('rr').value, suhu: $id('suhu').value, spo2: $id('spo2').value,
          gcs_e: $id('gcs_e').value, gcs_v: $id('gcs_v').value, gcs_m: $id('gcs_m').value, total_gcs: gcs(),
          nyeri: nyeri === null ? '' : nyeri, triase: ats,
          referensi_triase: JSON.stringify(kls ? [...document.querySelectorAll('.' + kls + ':checked')].map(x => x.value) : []),
          catatan: $id('catatan').value,
        };
        const btn = $id('btnSimpan');
        btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan…';
        try {
          const r = await fetch('controller/ranap/saveTriase.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, credentials: 'same-origin', body: JSON.stringify(data) });
          const j = await r.json().catch(() => ({}));
          if (!r.ok || j.status !== 'success') throw new Error(j.message || 'Gagal menyimpan triase.');
          berubah = false;
          $id('status').innerHTML = '<span class="text-success"><i class="ti ti-circle-check me-1"></i>Tersimpan ' + new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + '</span>';
          Swal.fire({ icon: 'success', title: j.message, timer: 1200, showConfirmButton: false });
          setTimeout(() => location.reload(), 1200);   // perbarui ringkasan hasil triase di atas form
          return true;
        } catch (e) { Swal.fire('Gagal', e.message, 'error'); return false; }
        finally { btn.disabled = false; btn.innerHTML = '<i class="ti ti-device-floppy me-1"></i>Simpan triase'; }
      }
      $id('btnSimpan').addEventListener('click', simpan);
      $id('btnCetak').addEventListener('click', async e => {
        if (!berubah) return;
        e.preventDefault();
        const r = await Swal.fire({ icon: 'warning', title: 'Perubahan belum disimpan', text: 'Cetakan memakai data tersimpan. Simpan dulu?', showCancelButton: true, showDenyButton: true, confirmButtonText: 'Simpan & cetak', denyButtonText: 'Cetak data lama', cancelButtonText: 'Batal' });
        if (r.isConfirmed) { if (await simpan()) window.open($id('btnCetak').href, '_blank'); }
        else if (r.isDenied) window.open($id('btnCetak').href, '_blank');
      });
    })();
  </script>
</body>

</html>
