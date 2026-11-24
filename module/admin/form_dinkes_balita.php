<?php
/**
 * Data Dinkes Balita (pasien < 5 tahun) per kunjungan. Disimpan & dikirim lewat controller/visit/dinkesBalitaController.php.
 * Z-score & stunting dihitung Dinkes dan ditampilkan setelah data terkirim.
 */
$title = 'Dinkes Balita';
require '../../controller/view.php';
$no = trim((string) ($_GET['no'] ?? ''));
$rm = trim((string) ($_GET['rm'] ?? ''));
$h = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');

// [key, label, tipe, atribut]
$kolom = [
   ['nama_orang_tua', 'Nama orang tua', 'text', 'required maxlength="150"', 6],
   ['nomor_kk', 'Nomor KK', 'text', 'maxlength="16" inputmode="numeric" pattern="\d{16}"', 6],
   ['bb_lahir', 'BB lahir (kg)', 'number', 'required min="0.5" max="7" step="0.01"', 3],
   ['tb_lahir', 'TB lahir (cm)', 'number', 'required min="20" max="70" step="0.1"', 3],
   ['lila_cm', 'LILA (cm)', 'number', 'required min="5" max="30" step="0.1"', 3],
   ['lingkar_kepala_cm', 'Lingkar kepala (cm)', 'number', 'required min="20" max="60" step="0.1"', 3],
   ['diagnosa', 'Diagnosa', 'text', 'maxlength="200"', 6],
   ['riwayat_sakit_kronis', 'Riwayat sakit kronis', 'text', 'maxlength="200" placeholder="-"', 6],
   ['usia_mpasi', 'Usia mulai MPASI (bulan)', 'number', 'min="0" max="60" step="1"', 4],
   ['ttd_jumlah', 'Tablet tambah darah (jumlah)', 'number', 'min="0" max="1000" step="1"', 4],
   ['usia_saat_hamil', 'Usia ibu saat hamil (tahun)', 'number', 'min="10" max="60" step="1"', 4],
];
$centang = [
   ['riwayat_diare', 'Diare 1 bulan terakhir'], ['riwayat_ispa', 'Riwayat ISPA'], ['imunisasi_lengkap', 'Imunisasi dasar lengkap'],
   ['asi_eksklusif', 'ASI eksklusif 0-6 bulan'], ['status_bumil', 'Ibu sedang hamil'], ['riwayat_anemia', 'Ibu riwayat anemia'],
];
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
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
          $rme = $_GET['rme'] ?? 'a';
          if ($rme == 'a') include 'menu_rme.php';
          else if ($rme == 'b') include 'menu_rmeb.php';
          else if ($rme == 'c') include 'menu_rme_inap.php';
          ?>
          <div class="row">
            <div class="col-12"><?php require 'card-pasien.php'; ?></div>
            <div class="col-12">
              <div class="card w-100">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title fw-semibold mb-0">Data Dinkes Balita</h5>
                    <span class="badge bg-secondary-subtle text-secondary" id="dbStatus">-</span>
                  </div>
                  <div class="alert alert-warning d-none" id="dbBukan">Pasien berusia 5 tahun atau lebih: kunjungan ini dikirim ke Dinkes sebagai pemeriksaan pasien dewasa, form ini tidak diperlukan.</div>
                  <div class="alert alert-danger d-none" id="dbError"></div>
                  <div class="alert alert-info d-none" id="dbZ"></div>
                  <form id="dbForm" class="row g-3" autocomplete="off">
                    <div class="col-12 small text-muted">BB/TB ukur diambil dari pemeriksaan kunjungan ini: <b id="dbUkur">-</b></div>
                    <?php foreach ($kolom as [$k, $lbl, $tipe, $attr, $lebar]): ?>
                      <div class="col-12 col-md-<?= $lebar ?>">
                        <label class="form-label" for="f_<?= $k ?>"><?= $h($lbl) ?></label>
                        <input type="<?= $tipe ?>" class="form-control" id="f_<?= $k ?>" data-f="<?= $k ?>" <?= $attr ?>>
                      </div>
                    <?php endforeach; ?>
                    <div class="col-12">
                      <div class="row g-2">
                        <?php foreach ($centang as [$k, $lbl]): ?>
                          <div class="col-12 col-md-4"><div class="form-check">
                            <input class="form-check-input" type="checkbox" id="c_<?= $k ?>" data-c="<?= $k ?>">
                            <label class="form-check-label" for="c_<?= $k ?>"><?= $h($lbl) ?></label>
                          </div></div>
                        <?php endforeach; ?>
                      </div>
                    </div>
                    <div class="col-12 col-md-6">
                      <label class="form-label" for="f_puskes_id">Puskesmas (kosong = default pengaturan)</label>
                      <select class="form-select" id="f_puskes_id"><option value="">Default pengaturan</option></select>
                    </div>
                    <div class="col-12 d-flex gap-2">
                      <button class="btn btn-primary" type="submit" id="dbSimpan"><i class="ti ti-device-floppy me-1"></i>Simpan &amp; kirim ke Dinkes</button>
                    </div>
                  </form>
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
      const API = 'controller/visit/dinkesBalitaController?no=' + encodeURIComponent(NO);
      const $ = id => document.getElementById(id);
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const toast = (title, icon = 'success') => Swal.fire({ toast: true, position: 'top-end', icon, title, showConfirmButton: false, timer: 2600 });
      const WARNA = { terkirim: 'success', gagal: 'danger', hapus_gagal: 'danger', belum: 'warning', dilewati: 'secondary', dihapus: 'secondary' };
      async function api(url, opt) {
        const r = await fetch(url, opt);
        const j = await r.json().catch(() => ({ status: 'error', message: 'Respon server tidak valid.' }));
        if (!r.ok || j.status !== 'success') throw new Error(j.message || 'Terjadi kesalahan.');
        return j;
      }

      function tampil(res) {
        const k = res.kunjungan || {};
        $('dbUkur').textContent = `BB ${k.bb || '-'} kg, TB ${k.tb || '-'} cm`;
        $('dbBukan').classList.toggle('d-none', !!k.balita);
        $('dbSimpan').disabled = !k.balita;
        Object.entries(res.data || {}).forEach(([key, v]) => {
          const el = document.querySelector(`[data-f="${key}"]`), cb = document.querySelector(`[data-c="${key}"]`);
          if (el) el.value = v ?? '';
          if (cb) cb.checked = v === true || v === 1 || v === '1';
        });
        if (res.puskes_id) $('f_puskes_id').innerHTML = `<option value="${esc(res.puskes_id)}">Puskesmas ID ${esc(res.puskes_id)}</option><option value="">Default pengaturan</option>`;
        const s = res.kirim ? res.kirim.status : (res.integrasi_aktif ? 'belum' : null);
        $('dbStatus').className = 'badge bg-' + (WARNA[s] || 'secondary') + '-subtle text-' + (WARNA[s] || 'secondary');
        $('dbStatus').textContent = s ? 'Dinkes: ' + s : (res.integrasi_aktif ? 'Dinkes: belum' : 'Integrasi Dinkes tidak aktif');
        const err = res.kirim && res.kirim.error;
        $('dbError').classList.toggle('d-none', !err);
        $('dbError').textContent = err || '';
        const z = res.zscore;
        $('dbZ').classList.toggle('d-none', !z);
        if (z) {
          $('dbZ').innerHTML = '<b>Hasil Dinkes</b> — ' + [['bb_u', 'BB/U'], ['tb_u', 'TB/U'], ['bb_tb', 'BB/TB']]
            .map(([key, lbl]) => z[key] ? `${lbl}: ${esc(z[key].zscore)} (${esc(z[key].interpretasi)})` : '').filter(Boolean).join(' · ')
            + (z.stunting ? ' · <span class="text-danger fw-bold">STUNTING</span>' : '');
        }
      }
      const muat = () => api(API).then(tampil).catch(e => toast(e.message, 'error'));
      muat().then(() => api(API + '&puskesmas=1').then(r => {
        const cur = $('f_puskes_id').value;
        $('f_puskes_id').innerHTML = '<option value="">Default pengaturan</option>' + r.data.map(p => `<option value="${esc(p.id)}">${esc(p.nama)}</option>`).join('');
        $('f_puskes_id').value = cur;
      }).catch(() => {}));

      $('dbForm').addEventListener('submit', async ev => {
        ev.preventDefault();
        const fd = new URLSearchParams();
        document.querySelectorAll('[data-f]').forEach(el => fd.set(el.dataset.f, el.value));
        document.querySelectorAll('[data-c]').forEach(el => fd.set(el.dataset.c, el.checked ? '1' : '0'));
        fd.set('puskes_id', $('f_puskes_id').value);
        try {
          const res = await api(API, { method: 'POST', body: fd });
          const st = res.kirim && res.kirim.status;
          toast(st === 'terkirim' ? 'Tersimpan & terkirim ke Dinkes' : 'Tersimpan. Status Dinkes: ' + (st || '-'), st === 'gagal' ? 'warning' : 'success');
          muat();
        } catch (e) { toast(e.message, 'error'); }
      });
    })();
  </script>
</body>

</html>
