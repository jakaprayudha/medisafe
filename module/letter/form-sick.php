<?php
/**
 * Surat Keterangan Sakit. Kerangka halaman, tabel, pemilih pasien, nomor surat & simpan: _kerangka_atas.php,
 * _kerangka_bawah.php, assets/js/surat-form.js. Di sini hanya field khusus surat sakit.
 *
 * Perbaikan dari versi lama: dua select2 berbeda dipasang pada elemen yang sama (pencarian ganti-ganti endpoint),
 * kolom "Dokter Pemeriksa" diisi dari kolom yang salah, mode nomor surat tidak pernah dibaca (input nomor manual
 * selalu tampil walau mode otomatis), "clear" pasien mengosongkan field yang tidak ada, seluruh riwayat surat dimuat
 * sekaligus, dan data tabel tanpa escape HTML.
 */
$SF = [
  'jenis' => 'sakit',
  'judul' => 'Surat Keterangan Sakit',
  'sub' => 'Keterangan istirahat pasien berdasarkan pemeriksaan dokter.',
  'ikon' => 'fa-bed', 'warna' => 'danger',
  'kolom' => ['Istirahat', 'Dokter', 'Keterangan'],
];
require '_kerangka_atas.php';
?>
          <div class="sf-sec">Masa istirahat</div>
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label small fw-semibold" for="tanggal_mulai">Mulai <span class="text-danger">*</span></label>
              <input type="date" class="form-control" name="tanggal_mulai" id="tanggal_mulai" required>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold" for="lamaHari">Lama istirahat <span class="text-danger">*</span></label>
              <div class="input-group">
                <input type="number" class="form-control" id="lamaHari" min="1" max="366" inputmode="numeric">
                <span class="input-group-text">hari</span>
              </div>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold" for="tanggal_selesai">Sampai <span class="text-danger">*</span></label>
              <input type="date" class="form-control" name="tanggal_selesai" id="tanggal_selesai" required>
            </div>
            <div class="col-12">
              <div class="sf-opsi" id="lamaCepat">
                <?php foreach ([1, 2, 3, 5, 7] as $n): ?>
                  <input type="radio" name="_lama" id="lama<?= $n ?>" value="<?= $n ?>"><label for="lama<?= $n ?>"><?= $n ?> hari</label>
                <?php endforeach; ?>
              </div>
              <div class="form-text" id="lamaInfo">&nbsp;</div>
            </div>
          </div>

          <div class="sf-sec">Keterangan</div>
          <textarea class="form-control" name="keterangan" id="keterangan" rows="3" maxlength="1000" placeholder="Mis. diagnosa singkat / anjuran dokter (opsional)"></textarea>
<?php require '_kerangka_bawah.php'; ?>
  <script>
    (function() {
      const $id = id => document.getElementById(id);
      const F = SuratForm;
      const geser = (t, n) => { const d = new Date(t + 'T00:00:00Z'); d.setUTCDate(d.getUTCDate() + n); return d.toISOString().slice(0, 10); };
      const selisih = (a, b) => Math.round((new Date(b + 'T00:00:00Z') - new Date(a + 'T00:00:00Z')) / 86400000) + 1;

      // tiga field saling terhubung: mulai + lama -> sampai, mulai + sampai -> lama
      function dariLama() {
        const m = $id('tanggal_mulai').value, n = parseInt($id('lamaHari').value, 10);
        if (m && n >= 1) $id('tanggal_selesai').value = geser(m, n - 1);
        info();
      }
      function dariSampai() {
        const m = $id('tanggal_mulai').value, s = $id('tanggal_selesai').value;
        if (m && s && s >= m) $id('lamaHari').value = selisih(m, s);
        info();
      }
      function info() {
        const m = $id('tanggal_mulai').value, s = $id('tanggal_selesai').value, n = parseInt($id('lamaHari').value, 10);
        document.querySelectorAll('#lamaCepat input').forEach(x => { x.checked = +x.value === n; });
        $id('lamaInfo').innerHTML = m && s && s >= m ? `Istirahat <b>${n} hari</b>: ${F.tgl(m)} s.d. ${F.tgl(s)}` : '&nbsp;';
      }
      $id('tanggal_mulai').addEventListener('change', () => ($id('lamaHari').value ? dariLama() : dariSampai()));
      $id('lamaHari').addEventListener('input', dariLama);
      $id('tanggal_selesai').addEventListener('change', dariSampai);
      $id('lamaCepat').addEventListener('change', e => { if (e.target.name === '_lama') { $id('lamaHari').value = e.target.value; dariLama(); } });

      F.init({
        api: 'controller/letter/suratSakitController',
        cetak: id => 'module/letter/print/surat-keterangan-sakit?id=' + encodeURIComponent(id),
        cari: 'controller/admisi/patientVisitControllerInOut',
        nama: 'surat keterangan sakit',
        kolom: [
          { data: 'tanggal_mulai', render: (v, t, r) => t === 'display' ? `<div class="small fw-semibold sf-nw">${r.lama || '-'} hari</div><div class="small text-muted sf-nw">${v === r.tanggal_selesai ? F.tglPendek(v) : F.tglPendek(v) + ' – ' + F.tglPendek(r.tanggal_selesai)}</div>` : (v || '') },
          { data: 'id_doctor', render: v => `<span class="small sf-dok" title="${F.esc(v || '')}">${F.esc(v || '-')}</span>` },
          { data: 'keterangan', render: (v, t) => t === 'display' ? (F.isi(v) ? `<div class="small sf-ket" title="${F.esc(v)}">${F.esc(v)}</div>` : '<span class="small text-muted">-</span>') : (v || '') }
        ],
        kosongkan() {
          $id('tanggal_mulai').value = F.tglJkt(0);
          $id('lamaHari').value = 1;
          dariLama();
        },
        pilihKunjungan(k, baru) {
          // istirahat biasanya mulai di hari kunjungan
          if (baru && k.visit_date) { $id('tanggal_mulai').value = String(k.visit_date).slice(0, 10); dariLama(); }
        },
        isi(d) {
          $id('tanggal_mulai').value = d.tanggal_mulai || '';
          $id('tanggal_selesai').value = d.tanggal_selesai || '';
          $id('lamaHari').value = d.lama || '';
          $id('keterangan').value = d.keterangan || '';
          dariSampai();
        },
        validasi(err, tandai) {
          const m = $id('tanggal_mulai').value, s = $id('tanggal_selesai').value, n = parseInt($id('lamaHari').value, 10);
          if (!m) tandai($id('tanggal_mulai'), 'tanggal mulai');
          if (!s) tandai($id('tanggal_selesai'), 'tanggal sampai');
          if (m && s && s < m) tandai($id('tanggal_selesai'), 'tanggal sampai tidak boleh sebelum mulai');
          if (!(n >= 1 && n <= 366)) tandai($id('lamaHari'), 'lama istirahat 1–366 hari');
        }
      });
    })();
  </script>
</body>

</html>
