<?php
/**
 * RME rawat inap — Pemeriksaan Generalis Tubuh.
 * Data disimpan sebagai blok [PEMERIKSAAN GENERALIS TUBUH] di pasien_visit.pemeriksaan_fisik
 * (controller/ranap/saveGeneralisTubuh.php). Cetak: module/print/generalis_tubuh.php.
 */
$title = 'Pemeriksaan Generalis Tubuh';
require '../../controller/view.php';
require_once '../../controller/ranap/generalisTubuhService.php';

$visitId = trim((string) ($_GET['no'] ?? ''));
$nomorRm = trim((string) ($_GET['rm'] ?? ''));
$customerId = (int) ($_SESSION['id_customer'] ?? 0);
$bolehUbah = in_array($_SESSION['roles'] ?? '', ['admin', 'dokter'], true);   // sama dengan pengecekan di controller simpan
$patient = null;
$pageError = '';

if ($visitId === '' || $nomorRm === '' || $customerId <= 0) {
   $pageError = 'Parameter kunjungan tidak lengkap.';
} else {
   $stmt = $koneksi->prepare("
      SELECT p.patient_name, p.nomor_rm, v.visit_ID, v.visit_date, v.pemeriksaan_fisik
      FROM pasien_visit v
      INNER JOIN ms_patient p ON p.id_patient = v.id_patient
      WHERE v.visit_ID = ? AND p.nomor_rm = ? AND v.id_customer = ?
      LIMIT 1
   ");
   $stmt->bind_param('ssi', $visitId, $nomorRm, $customerId);
   $stmt->execute();
   $patient = $stmt->get_result()->fetch_assoc();
   $stmt->close();
   if (!$patient) $pageError = 'Kunjungan tidak ditemukan untuk faskes ini.';
}

$fields = generalisTubuhFields();
$values = generalisTubuhValues($patient['pemeriksaan_fisik'] ?? '');
$total = 0;
foreach ($fields as $g) $total += count($g);
$e = static fn($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$urlCetak = 'module/print/generalis_tubuh?' . http_build_query(['no' => $visitId, 'rm' => $nomorRm]);
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .gt-head { background: #fff; border: 1px solid #e5eaef; border-radius: 14px; padding: 14px 18px; }
    .gt-ikon { width: 42px; height: 42px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; }
    .gt-progress { height: 6px; background: #eef2f7; border-radius: 4px; overflow: hidden; width: 160px; }
    .gt-progress span { display: block; height: 100%; background: #2a78d6; transition: width .2s; }
    .gt-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 14px; }
    .gt-grup { background: #fff; border: 1px solid #e5eaef; border-radius: 14px; padding: 14px 16px; }
    .gt-grup-h { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 6px; }
    .gt-grup-h b { font-size: .95rem; color: #0f172a; }
    .gt-grup-h .st { font-size: .72rem; font-weight: 700; border-radius: 6px; padding: 1px 8px; }
    .st.ok { background: #dcfce7; color: #166534; }
    .st.ab { background: #fee2e2; color: #991b1b; }
    .st.kosong { background: #f1f5f9; color: #475569; }
    .gt-f { display: grid; grid-template-columns: 130px minmax(0, 1fr); align-items: center; gap: 10px; padding: 7px 0; border-top: 1px dashed #eef1f5; }
    .gt-f > span { font-size: .84rem; font-weight: 600; color: #334155; }
    .gt-opsi { display: flex; flex-wrap: wrap; gap: 6px; }
    .gt-opsi input { position: absolute; opacity: 0; pointer-events: none; }
    .gt-opsi label { font-size: .8rem; font-weight: 600; padding: 4px 11px; border-radius: 999px; border: 1px solid #cbd5e1; background: #fff; color: #1f2937; cursor: pointer; user-select: none; transition: background .12s, border-color .12s; }
    .gt-opsi label:hover { border-color: #64748b; }
    .gt-opsi input:focus-visible + label { outline: 2px solid #2a78d6; outline-offset: 2px; }
    .gt-opsi input:checked + label.normal { background: #15803d; border-color: #15803d; color: #fff; }
    .gt-opsi input:checked + label.abnormal { background: #b91c1c; border-color: #b91c1c; color: #fff; }
    .gt-opsi input:disabled + label { cursor: default; opacity: .75; }
    .gt-bar { position: sticky; bottom: 0; z-index: 20; background: rgba(255, 255, 255, .96); border-top: 1px solid #e5eaef; padding: 10px 0; margin-top: 14px; backdrop-filter: blur(6px); }
    @media (max-width: 575.98px) { .gt-grid { grid-template-columns: 1fr; } .gt-f { grid-template-columns: 1fr; gap: 4px; } }
  </style>
</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
    <?php require 'sidebar.php'; ?>
    <div class="body-wrapper">
      <?php require 'navbar.php'; ?>
      <div class="body-wrapper-inner">
        <div class="container-fluid">
          <?php if ($visitId !== '' && $nomorRm !== '') require 'menu_rme_inap.php'; ?>

          <?php if ($pageError !== ''): ?>
            <div class="alert alert-danger mt-3" role="alert"><?= $e($pageError) ?></div>
          <?php else: ?>
            <div class="gt-head d-flex flex-wrap align-items-center gap-3 mb-3 mt-2">
              <span class="gt-ikon bg-danger-subtle text-danger"><i class="ti ti-stethoscope"></i></span>
              <div class="flex-grow-1" style="min-width:0">
                <h5 class="fw-semibold mb-0">Pemeriksaan Generalis Tubuh</h5>
                <div class="small text-muted">
                  <b class="text-dark"><?= $e($patient['patient_name']) ?></b> · RM <?= $e($patient['nomor_rm']) ?> · <?= $e($patient['visit_ID']) ?>
                </div>
              </div>
              <div class="text-end">
                <div class="small text-muted mb-1"><span id="gtTerisi">0</span>/<?= $total ?> terisi · <span id="gtAbnormal">0</span> abnormal</div>
                <div class="gt-progress ms-auto"><span id="gtBar" style="width:0"></span></div>
              </div>
              <div class="d-flex gap-2">
                <a class="btn btn-sm btn-outline-dark" id="btnCetak" href="<?= $e($urlCetak) ?>" target="_blank"><i class="ti ti-printer me-1"></i>Cetak</a>
                <?php if ($bolehUbah): ?>
                  <button type="button" class="btn btn-sm btn-success" id="btnNormal" title="Isi semua yang masih kosong dengan nilai normal"><i class="ti ti-checks me-1"></i>Isi normal</button>
                <?php endif ?>
              </div>
            </div>
            <?php if (!$bolehUbah): ?>
              <div class="alert alert-info py-2 small"><i class="ti ti-lock me-1"></i>Hanya dokter atau admin yang dapat mengubah pemeriksaan ini.</div>
            <?php endif ?>

            <form id="generalisForm" autocomplete="off">
              <input type="hidden" name="visit_ID" value="<?= $e($visitId) ?>">
              <input type="hidden" name="nomor_rm" value="<?= $e($nomorRm) ?>">
              <div class="gt-grid">
                <?php foreach ($fields as $group => $groupFields): ?>
                  <section class="gt-grup" aria-label="Pemeriksaan <?= $e($group) ?>">
                    <div class="gt-grup-h"><b><?= $e($group) ?></b><span class="st kosong" data-status></span></div>
                    <?php foreach ($groupFields as $key => $field): ?>
                      <div class="gt-f" data-normal="<?= $e($field['normal']) ?>">
                        <span><?= $e($field['label']) ?></span>
                        <div class="gt-opsi" role="radiogroup" aria-label="<?= $e($field['label']) ?>">
                          <?php foreach ($field['options'] as $index => $option): $inputId = $key . '_' . $index; ?>
                            <input type="radio" name="<?= $e($key) ?>" id="<?= $e($inputId) ?>" value="<?= $e($option) ?>" <?= ($values[$key] ?? '') === $option ? 'checked' : '' ?> <?= $bolehUbah ? '' : 'disabled' ?>>
                            <label for="<?= $e($inputId) ?>" class="<?= $option === $field['normal'] ? 'normal' : 'abnormal' ?>"><?= $e($option) ?></label>
                          <?php endforeach; ?>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </section>
                <?php endforeach; ?>
              </div>

              <?php if ($bolehUbah): ?>
                <div class="gt-bar">
                  <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="small text-muted me-auto" id="gtStatus"><i class="ti ti-info-circle me-1"></i>Klik pilihan yang sudah aktif untuk mengosongkannya.</span>
                    <button type="button" class="btn btn-light border" id="btnKosongkan"><i class="ti ti-eraser me-1"></i>Kosongkan semua</button>
                    <button type="submit" class="btn btn-primary px-4" id="saveGeneralis"><i class="ti ti-device-floppy me-1"></i>Simpan Pemeriksaan</button>
                  </div>
                </div>
              <?php endif ?>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <?php require 'library.php'; ?>
  <script>
    (function() {
      const form = document.getElementById('generalisForm');
      if (!form) return;
      const TOTAL = <?= (int) $total ?>;
      let berubah = false, menyimpan = false;
      const $id = id => document.getElementById(id);

      function ringkas() {
        let terisi = 0, abnormal = 0;
        form.querySelectorAll('.gt-grup').forEach(g => {
          let isi = 0, ab = 0;
          const n = g.querySelectorAll('.gt-f').length;
          g.querySelectorAll('.gt-f').forEach(f => {
            const c = f.querySelector('input:checked');
            if (c) { isi++; if (c.value !== f.dataset.normal) ab++; }
          });
          terisi += isi; abnormal += ab;
          const st = g.querySelector('[data-status]');
          st.className = 'st ' + (ab ? 'ab' : isi === n ? 'ok' : 'kosong');
          st.textContent = ab ? `${ab} abnormal` : isi === n ? 'Normal' : `${isi}/${n}`;
        });
        $id('gtTerisi').textContent = terisi;
        $id('gtAbnormal').textContent = abnormal;
        $id('gtBar').style.width = Math.round(terisi / TOTAL * 100) + '%';
      }
      const tandai = () => {
        berubah = true;
        if ($id('gtStatus')) $id('gtStatus').innerHTML = '<span class="text-warning-emphasis"><i class="ti ti-alert-circle me-1"></i>Ada perubahan belum disimpan</span>';
        ringkas();
      };

      // radio bisa dikosongkan lagi dengan mengklik pilihan yang sedang aktif (dulu sekali dipilih tidak bisa dibatalkan)
      form.querySelectorAll('.gt-opsi label').forEach(lbl => {
        lbl.addEventListener('mousedown', () => { const r = $id(lbl.htmlFor); lbl.dataset.tadi = r.checked ? '1' : ''; });
        lbl.addEventListener('click', ev => {
          const r = $id(lbl.htmlFor);
          if (r.disabled) return;
          if (lbl.dataset.tadi === '1') { ev.preventDefault(); r.checked = false; lbl.dataset.tadi = ''; tandai(); }
        });
      });
      form.addEventListener('change', tandai);

      $id('btnNormal')?.addEventListener('click', () => {
        let n = 0;
        form.querySelectorAll('.gt-f').forEach(f => {
          if (f.querySelector('input:checked')) return;
          const r = [...f.querySelectorAll('input')].find(x => x.value === f.dataset.normal);
          if (r) { r.checked = true; n++; }
        });
        if (n) { tandai(); Swal.fire({ icon: 'success', title: `${n} pemeriksaan diisi normal`, text: 'Periksa kembali lalu simpan.', timer: 1600, showConfirmButton: false }); }
        else Swal.fire({ icon: 'info', title: 'Semua sudah terisi', timer: 1200, showConfirmButton: false });
      });
      $id('btnKosongkan')?.addEventListener('click', async () => {
        const r = await Swal.fire({ icon: 'warning', title: 'Kosongkan semua pilihan?', text: 'Perubahan baru tersimpan setelah klik Simpan.', showCancelButton: true, confirmButtonText: 'Kosongkan', cancelButtonText: 'Batal' });
        if (!r.isConfirmed) return;
        form.querySelectorAll('.gt-opsi input:checked').forEach(x => { x.checked = false; });
        tandai();
      });

      // cetak memakai data tersimpan -> ingatkan bila masih ada perubahan
      $id('btnCetak').addEventListener('click', async ev => {
        if (!berubah) return;
        ev.preventDefault();
        const r = await Swal.fire({ icon: 'warning', title: 'Perubahan belum disimpan', text: 'Cetakan memakai data yang sudah disimpan. Simpan dulu?', showCancelButton: true, showDenyButton: true, confirmButtonText: 'Simpan & cetak', denyButtonText: 'Cetak data lama', cancelButtonText: 'Batal' });
        if (r.isConfirmed) { if (await simpan()) window.open($id('btnCetak').href, '_blank'); }
        else if (r.isDenied) window.open($id('btnCetak').href, '_blank');
      });

      async function simpan() {
        const fields = {};
        form.querySelectorAll('.gt-f').forEach(f => {
          const nama = f.querySelector('input').name, c = f.querySelector('input:checked');
          fields[nama] = c ? c.value : '';   // dikirim kosong agar pilihan yang dibatalkan ikut terhapus
        });
        const btn = $id('saveGeneralis');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan…';
        try {
          const res = await fetch('controller/ranap/saveGeneralisTubuh.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, credentials: 'same-origin',
            body: JSON.stringify({ visit_ID: form.elements.visit_ID.value, nomor_rm: form.elements.nomor_rm.value, fields })
          });
          const data = await res.json().catch(() => ({}));
          if (!res.ok || data.status !== 'success') throw new Error(data.message || 'Pemeriksaan gagal disimpan.');
          berubah = false;
          $id('gtStatus').innerHTML = '<span class="text-success"><i class="ti ti-circle-check me-1"></i>Tersimpan ' + new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + '</span>';
          Swal.fire({ icon: 'success', title: 'Tersimpan', text: data.message, timer: 1400, showConfirmButton: false });
          return true;
        } catch (err) {
          Swal.fire('Gagal', err.message || 'Gagal terhubung ke server.', 'error');
          return false;
        } finally {
          btn.disabled = false;
          btn.innerHTML = '<i class="ti ti-device-floppy me-1"></i>Simpan Pemeriksaan';
        }
      }
      form.addEventListener('submit', ev => { ev.preventDefault(); if (!menyimpan) { menyimpan = true; simpan().finally(() => { menyimpan = false; }); } });
      window.addEventListener('beforeunload', ev => { if (berubah) { ev.preventDefault(); ev.returnValue = ''; } });

      ringkas();
    })();
  </script>
</body>

</html>
