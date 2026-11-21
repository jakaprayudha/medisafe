<?php
/**
 * Surat Hasil Pemeriksaan Mata. Kerangka halaman, tabel, pemilih pasien, nomor surat & simpan: _kerangka_atas.php,
 * _kerangka_bawah.php, assets/js/surat-form.js. Di sini hanya field khusus surat mata. Aturan validasi field sama
 * dengan server (controller/letter/suratMataAturan.php).
 *
 * Perbaikan dari versi lama: tabel tanpa escape HTML (nama pasien / kesimpulan), tanda vital "diisi dari kunjungan"
 * tidak pernah terisi (endpoint pencarian yang dipakai tidak mengembalikan tanda vital), tautan "Setting nomor surat"
 * menuju halaman yang tidak ada (module/letter/setting-surat), seluruh riwayat surat dimuat sekaligus, nomor manual
 * tidak bisa dikoreksi saat ubah, dan pengecekan setting nomor dilakukan lewat request terpisah setiap buka form.
 */
require_once __DIR__ . '/../../controller/letter/suratMataAturan.php';
$SF = [
  'jenis' => 'mata',
  'judul' => 'Surat Hasil Pemeriksaan Mata',
  'sub' => 'Hasil pemeriksaan visus, refraksi, TIO & segmen mata pasien.',
  'ikon' => 'fa-eye', 'warna' => 'info',
  'kolom' => ['Visus OD', 'Visus OS', 'Kesimpulan', 'Dokter'],
  'modal' => 'modal-xl',
];
require '_kerangka_atas.php';
$smKet = ['Normal', 'Tinggi', 'Rendah'];
$smLab = [
  ['gula_darah_sewaktu', 'gula_darah_keterangan', 'Gula darah sewaktu', 'mg/dL'],
  ['kolesterol_total', 'kolesterol_keterangan', 'Kolesterol total', 'mg/dL'],
  ['asam_urat', 'asam_urat_keterangan', 'Asam urat', 'mg/dL'],
  ['hemoglobin', 'hemoglobin_keterangan', 'Hemoglobin', 'g/dL'],
];
$smVisus = [['tanpa_koreksi_jauh', 'Tanpa koreksi', 'jauh'], ['tanpa_koreksi_dekat', 'Tanpa koreksi', 'dekat'],
  ['dengan_koreksi_jauh', 'Dengan koreksi', 'jauh'], ['dengan_koreksi_dekat', 'Dengan koreksi', 'dekat']];
$smMata = ['od' => 'OD · mata kanan', 'os' => 'OS · mata kiri'];
?>
          <style>
            .sm-grid { width: 100%; border-collapse: separate; border-spacing: 0 6px; }
            .sm-grid th { font-size: .74rem; font-weight: 600; color: #475569; padding: 0 6px; white-space: nowrap; }
            .sm-grid td { padding: 0 6px; vertical-align: middle; }
            .sm-grid td:first-child, .sm-grid th:first-child { padding-left: 0; }
            .sm-grid td.sm-lbl { font-size: .8rem; color: #334155; white-space: nowrap; width: 1%; padding-right: 12px; }
            .sm-grid td.sm-lbl small { color: #64748b; }
            .sm-mata { font-size: .74rem; font-weight: 700; color: #1e3a8a; }
            .sm-blok { border: 1px solid #eef1f4; border-radius: 10px; padding: 10px 12px; height: 100%; }
            .sm-blok .form-label { margin-bottom: 4px; }
            .sm-mini { font-size: .72rem; padding: 1px 8px; border-radius: 20px; }
            .sm-opsi label { padding: 2px 10px !important; font-size: .74rem !important; }
            #vitalInfo:empty { display: none; }
          </style>

          <div class="sf-sec">Tanda vital</div>
          <div class="row g-3">
            <?php foreach ([["tekanan_darah", "Tekanan darah", "mmHg"], ["nadi", "Nadi", "x/mnt"], ["suhu", "Suhu", "°C"], ["respirasi", "Respirasi", "x/mnt"]] as [$k, $l, $u]): ?>
              <div class="col-6 col-md-3">
                <label class="form-label small fw-semibold" for="<?= $k ?>"><?= $l ?></label>
                <div class="input-group">
                  <input type="text" class="form-control sm-f" name="<?= $k ?>" id="<?= $k ?>" inputmode="decimal">
                  <span class="input-group-text small"><?= $u ?></span>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="form-text" id="vitalInfo"></div>

          <div class="sf-sec">Visus</div>
          <div class="row g-3">
            <div class="col-lg-7">
              <table class="sm-grid">
                <thead><tr><th></th><?php foreach ($smMata as $m => $l): ?><th><span class="sm-mata"><?= $l ?></span></th><?php endforeach; ?></tr></thead>
                <tbody>
                  <?php foreach ($smVisus as [$k, $l, $jarak]): ?>
                    <tr>
                      <td class="sm-lbl"><?= $l ?> <small>· <?= $jarak ?></small></td>
                      <?php foreach ($smMata as $m => $_): ?>
                        <td><input type="text" class="form-control form-control-sm sm-f" name="visus_<?= $m ?>_<?= $k ?>" id="visus_<?= $m ?>_<?= $k ?>"
                          list="<?= $jarak === 'jauh' ? 'dlJauh' : 'dlDekat' ?>" aria-label="Visus <?= strtoupper($m) ?> <?= strtolower($l) ?> <?= $jarak ?>"></td>
                      <?php endforeach; ?>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
              <datalist id="dlJauh"><?php foreach (['6/6', '6/7.5', '6/9', '6/12', '6/15', '6/18', '6/21', '6/24', '6/30', '6/36', '6/60', '5/60', '4/60', '3/60', '2/60', '1/60', '1/300', '1/~', '0'] as $v): ?><option value="<?= $v ?>"><?php endforeach; ?></datalist>
              <datalist id="dlDekat"><?php foreach (['J1', 'J2', 'J3', 'J4', 'J5', 'J6', 'J7', 'N5', 'N6', 'N8', 'N10', 'N12'] as $v): ?><option value="<?= $v ?>"><?php endforeach; ?></datalist>
              <div class="form-text">Jauh: Snellen (6/6, 1/60, 1/300 = goyang tangan, 1/~ = persepsi cahaya, 0 = tanpa persepsi cahaya). Dekat: Jaeger (J1…) atau N.</div>
            </div>
            <div class="col-lg-5">
              <div class="sm-blok">
                <div class="small fw-semibold mb-1">Refraksi (koreksi)</div>
                <table class="sm-grid">
                  <thead><tr><th></th><th>SPH</th><th>CYL</th><th>AXIS</th><th>ADD</th></tr></thead>
                  <tbody>
                    <?php foreach ($smMata as $m => $_): ?>
                      <tr>
                        <td class="sm-lbl"><span class="sm-mata"><?= strtoupper($m) ?></span></td>
                        <?php foreach (["sph", "cyl", "axis", "add"] as $u): ?>
                          <td><input type="text" class="form-control form-control-sm sm-f" name="refraksi_<?= $m ?>_<?= $u ?>" id="refraksi_<?= $m ?>_<?= $u ?>" aria-label="<?= strtoupper($u . ' ' . $m) ?>" inputmode="decimal"></td>
                        <?php endforeach; ?>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
                <div class="d-flex align-items-center gap-2 mt-2">
                  <label class="small fw-semibold mb-0" for="pd">PD</label>
                  <div class="input-group input-group-sm" style="max-width:170px">
                    <input type="text" class="form-control sm-f" name="pd" id="pd">
                    <span class="input-group-text">mm</span>
                  </div>
                </div>
                <div class="form-text">Dioptri: angka bertanda (mis. -1.25, +0.75) atau "plano". AXIS 0–180.</div>
              </div>
            </div>
          </div>

          <div class="sf-sec">TIO &amp; segmen mata</div>
          <div class="row g-3">
            <?php foreach ($smMata as $m => $l): ?>
              <div class="col-md-6">
                <div class="sm-blok">
                  <div class="sm-mata mb-2"><?= $l ?></div>
                  <label class="form-label small fw-semibold" for="tio_<?= $m ?>">TIO</label>
                  <div class="input-group input-group-sm mb-2" style="max-width:170px">
                    <input type="text" class="form-control sm-f" name="tio_<?= $m ?>" id="tio_<?= $m ?>" inputmode="decimal">
                    <span class="input-group-text">mmHg</span>
                  </div>
                  <?php foreach (['anterior' => 'Segmen anterior', 'posterior' => 'Segmen posterior'] as $s => $sl): ?>
                    <div class="d-flex align-items-center justify-content-between">
                      <label class="form-label small fw-semibold" for="segmen_<?= $s ?>_<?= $m ?>"><?= $sl ?></label>
                      <button type="button" class="btn btn-light sm-mini mb-1 sm-dbn" data-target="segmen_<?= $s ?>_<?= $m ?>" title="Isi &quot;Dalam batas normal&quot;">Dalam batas normal</button>
                    </div>
                    <textarea class="form-control form-control-sm sm-f mb-2" name="segmen_<?= $s ?>_<?= $m ?>" id="segmen_<?= $s ?>_<?= $m ?>" rows="2"></textarea>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="sf-sec">Laboratorium / penunjang <span class="fw-normal text-lowercase">(opsional)</span></div>
          <div class="row g-3">
            <?php foreach ($smLab as [$k, $kk, $l, $u]): ?>
              <div class="col-md-6">
                <div class="row g-2 align-items-end">
                  <div class="col-5">
                    <label class="form-label small fw-semibold" for="<?= $k ?>"><?= $l ?></label>
                    <div class="input-group input-group-sm">
                      <input type="text" class="form-control sm-f" name="<?= $k ?>" id="<?= $k ?>" inputmode="decimal">
                      <span class="input-group-text"><?= $u ?></span>
                    </div>
                  </div>
                  <div class="col-7">
                    <label class="form-label small text-muted" for="<?= $kk ?>">Keterangan</label>
                    <input type="text" class="form-control form-control-sm sm-f sm-ket" name="<?= $kk ?>" id="<?= $kk ?>" placeholder="Pilih di bawah atau ketik">
                  </div>
                  <div class="col-12">
                    <div class="sf-opsi sm-opsi" data-ket="<?= $kk ?>">
                      <?php foreach ($smKet as $o): ?>
                        <input type="radio" name="_<?= $kk ?>" id="_<?= $kk . $o ?>" value="<?= $o ?>"><label for="_<?= $kk . $o ?>"><?= $o ?></label>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="sf-sec">Kesimpulan &amp; rekomendasi</div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold" for="kesimpulan">Kesimpulan</label>
              <div class="sf-opsi sm-opsi mb-2 sm-tambah" data-target="kesimpulan">
                <?php foreach (['Mata normal', 'Miopia', 'Hipermetropia', 'Astigmatisma', 'Presbiopia', 'Katarak'] as $i => $o): ?>
                  <input type="checkbox" id="_kes<?= $i ?>" value="<?= $o ?>"><label for="_kes<?= $i ?>">+ <?= $o ?></label>
                <?php endforeach; ?>
              </div>
              <textarea class="form-control sm-f" name="kesimpulan" id="kesimpulan" rows="3" placeholder="Kesimpulan hasil pemeriksaan mata…"></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold" for="rekomendasi">Rekomendasi</label>
              <div class="sf-opsi sm-opsi mb-2 sm-tambah" data-target="rekomendasi">
                <?php foreach (['Gunakan kacamata koreksi', 'Kontrol ulang 6 bulan', 'Rujuk ke dokter spesialis mata'] as $i => $o): ?>
                  <input type="checkbox" id="_rek<?= $i ?>" value="<?= $o ?>"><label for="_rek<?= $i ?>">+ <?= $o ?></label>
                <?php endforeach; ?>
              </div>
              <textarea class="form-control sm-f" name="rekomendasi" id="rekomendasi" rows="3" placeholder="Rekomendasi dokter…"></textarea>
            </div>
          </div>
<?php require '_kerangka_bawah.php'; ?>
  <script>
    window.SM_ATURAN = <?= json_encode(['field' => MATA_FIELD, 'inti' => MATA_INTI], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
    (function() {
      const $id = id => document.getElementById(id);
      const F = SuratForm, A = window.SM_ATURAN, VITAL = ['tekanan_darah', 'nadi', 'suhu', 'respirasi'];

      // aturan sama dengan smCocok() di controller/letter/suratMataAturan.php
      function cocok(v, aturan) {
        if (v === '' || !aturan) return true;
        if (aturan === 'td') return /^\d{2,3}\s*\/\s*\d{2,3}$/.test(v);
        if (aturan === 'dioptri') return /^([+-]?\d{1,2}([.,]\d{1,2})?|plano|pl)$/i.test(v);
        if (aturan === 'axis') return /^\d{1,3}$/.test(v) && +v <= 180;
        if (aturan.startsWith('angka:')) {
          const [, min, max] = aturan.split(':');
          if (!/^\d{1,4}([.,]\d{1,2})?$/.test(v)) return false;
          const n = parseFloat(v.replace(',', '.'));
          return n >= +min && n <= +max;
        }
        return true;
      }
      const nilai = k => ($id(k) ? $id(k).value.trim() : '');

      // chip keterangan lab <-> input teks
      function sinkronKet() {
        document.querySelectorAll('.sm-opsi[data-ket]').forEach(g => {
          const v = nilai(g.dataset.ket).toLowerCase();
          g.querySelectorAll('input').forEach(r => { r.checked = r.value.toLowerCase() === v; });
        });
      }
      document.querySelectorAll('.sm-opsi[data-ket]').forEach(g => g.addEventListener('change', e => { $id(g.dataset.ket).value = e.target.value; }));
      document.querySelectorAll('.sm-ket').forEach(i => i.addEventListener('input', sinkronKet));

      // chip kesimpulan / rekomendasi: tambah / hapus baris teks
      function sinkronTambah() {
        document.querySelectorAll('.sm-tambah').forEach(g => {
          const baris = nilai(g.dataset.target).split(/\n|;\s*/).map(s => s.trim().toLowerCase());
          g.querySelectorAll('input').forEach(c => { c.checked = baris.includes(c.value.toLowerCase()); });
        });
      }
      document.querySelectorAll('.sm-tambah').forEach(g => g.addEventListener('change', e => {
        const ta = $id(g.dataset.target), v = e.target.value;
        let baris = ta.value.split('\n').map(s => s.trim()).filter(Boolean);
        baris = e.target.checked ? (baris.some(b => b.toLowerCase() === v.toLowerCase()) ? baris : baris.concat(v)) : baris.filter(b => b.toLowerCase() !== v.toLowerCase());
        ta.value = baris.join('\n');
      }));
      ['kesimpulan', 'rekomendasi'].forEach(k => $id(k).addEventListener('input', sinkronTambah));
      document.querySelectorAll('.sm-dbn').forEach(b => b.addEventListener('click', () => { $id(b.dataset.target).value = 'Dalam batas normal'; $id(b.dataset.target).classList.remove('is-invalid'); }));
      document.querySelectorAll('.sm-f').forEach(i => i.addEventListener('input', () => i.classList.remove('is-invalid')));

      // kolom tabel: visus jauh, tampil "tanpa koreksi → dengan koreksi"
      const visus = m => (v, t, r) => {
        const tk = r['visus_' + m + '_tanpa_koreksi_jauh'], dk = r['visus_' + m + '_dengan_koreksi_jauh'];
        if (t !== 'display') return [tk, dk].join(' ');
        if (!F.isi(tk) && !F.isi(dk)) return '<span class="small text-muted">-</span>';
        return `<span class="small sf-nw" title="Jauh: tanpa koreksi ${F.esc(tk || '-')} · dengan koreksi ${F.esc(dk || '-')}">${F.esc(tk || '-')}${F.isi(dk) ? ' <span class="text-muted">→</span> <b>' + F.esc(dk) + '</b>' : ''}</span>`;
      };

      F.init({
        api: 'controller/letter/suratPemeriksaanMataController',
        cetak: id => 'module/letter/print/surat-pemeriksaan-mata?id=' + encodeURIComponent(id),
        cari: 'controller/admisi/patientVisitController', // endpoint ini juga mengembalikan tanda vital kunjungan
        nama: 'surat hasil pemeriksaan mata',
        kolom: [
          { data: 'visus_od_tanpa_koreksi_jauh', render: visus('od') },
          { data: 'visus_os_tanpa_koreksi_jauh', render: visus('os') },
          { data: 'kesimpulan', render: (v, t) => t === 'display' ? (F.isi(v) ? `<div class="small sf-ket" title="${F.esc(v)}">${F.esc(v)}</div>` : '<span class="small text-muted">-</span>') : (v || '') },
          { data: 'id_doctor', render: v => `<span class="small sf-dok" title="${F.esc(v || '')}">${F.esc(v || '-')}</span>` }
        ],
        kosongkan() {
          $id('vitalInfo').textContent = '';
          sinkronKet(); sinkronTambah();
        },
        pilihKunjungan(k, baru) {
          if (!baru) return;
          // tanda vital dari kunjungan; nilai kosong / 0 / di luar aturan tidak disalin
          const diisi = [], dilewati = [];
          VITAL.forEach(f => {
            const v = String(k[f] ?? '').trim(), nol = !/[1-9]/.test(v);
            $id(f).value = '';
            if (v === '' || nol) return;
            if (cocok(v, A.field[f][1])) { $id(f).value = v; diisi.push(f); } else dilewati.push(A.field[f][2] + ' "' + v + '"');
          });
          $id('vitalInfo').innerHTML = diisi.length || dilewati.length
            ? (diisi.length ? `<i class="fas fa-circle-info me-1"></i>Tanda vital diambil dari kunjungan ${F.tgl(k.visit_date)}.` : '')
              + (dilewati.length ? ` <span class="text-warning-emphasis">Tidak disalin (format tidak valid): ${F.esc(dilewati.join(', '))}.</span>` : '')
            : '';
        },
        isi(d) {
          Object.keys(A.field).forEach(f => { if ($id(f)) $id(f).value = d[f] ?? ''; });
          $id('vitalInfo').textContent = '';
          sinkronKet(); sinkronTambah();
        },
        validasi(err, tandai) {
          const salah = [];
          Object.entries(A.field).forEach(([f, [maks, aturan, label]]) => {
            const el = $id(f); if (!el) return;
            const v = el.value.trim();
            if (v.length > maks) tandai(el, label + ' maks. ' + maks + ' karakter');
            else if (!cocok(v, aturan)) { el.classList.add('is-invalid'); salah.push(label); }
          });
          if (salah.length) err.push('perbaiki format ' + salah.join(', '));
          if (!A.inti.some(f => nilai(f) !== '')) err.push('minimal satu hasil pemeriksaan mata (visus, refraksi, TIO, segmen, atau kesimpulan)');
        },
        data(fd) {
          // input bantu chip tidak ikut dikirim
          [...fd.keys()].filter(k => k.startsWith('_')).forEach(k => fd.delete(k));
        }
      });
    })();
  </script>
</body>

</html>
