<?php
/**
 * Surat Keterangan Kematian. Kerangka halaman, tabel, pemilih pasien, nomor surat & simpan: _kerangka_atas.php,
 * _kerangka_bawah.php, assets/js/surat-form.js. Di sini hanya field khusus surat kematian.
 *
 * Perbaikan dari versi lama: ubah surat selalu gagal di server, data tabel tanpa escape HTML (XSS lewat nama pasien /
 * ruangan), daftar dokter disusun dengan SQL yang disambung string, setting dicek lewat endpoint lain setiap klik
 * "Tambah", tautan setting mengarah ke halaman yang tidak ada, tanggal/jam kematian tanpa validasi, modal di luar
 * <body>, dan seluruh riwayat surat dimuat sekaligus.
 */
$SF = [
  'jenis' => 'kematian',
  'judul' => 'Surat Keterangan Kematian',
  'sub' => 'Keterangan meninggal dunia pasien yang dinyatakan oleh dokter.',
  'ikon' => 'fa-file-medical', 'warna' => 'secondary',
  'kolom' => ['Waktu kematian', 'Tempat', 'Dokter'],
];
require '_kerangka_atas.php';

// dokter aktif faskes ini (nilai = ms_doctor.id_doctor, dibaca halaman cetak & verifikasi)
$skDokter = [];
try {
  $st = $koneksi->prepare("SELECT id_doctor, doctor_name FROM ms_doctor WHERE doctor_status = 1 AND id_customer = ? ORDER BY doctor_name");
  $st->bind_param('s', $SF_IDC);
  $st->execute();
  $skDokter = $st->get_result()->fetch_all(MYSQLI_ASSOC);
  $st->close();
} catch (Throwable $e) {
  error_log('[form-death] ' . $e->getMessage());
}
$skRuang = ['IGD', 'Rawat Inap', 'ICU', 'Ruang Bersalin', 'Ruang Tindakan', 'Poliklinik'];
?>
          <div class="sf-sec">Waktu kematian</div>
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label small fw-semibold" for="tanggal_kematian">Tanggal meninggal <span class="text-danger">*</span></label>
              <input type="date" class="form-control" name="tanggal_kematian" id="tanggal_kematian" required>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold" for="waktu_kematian">Pukul (WIB) <span class="text-danger">*</span></label>
              <input type="time" class="form-control" name="waktu_kematian" id="waktu_kematian" required>
            </div>
            <div class="col-md-4 d-flex align-items-end">
              <button type="button" class="btn btn-outline-secondary w-100" id="skSekarang"><i class="far fa-clock me-1"></i>Isi waktu sekarang</button>
            </div>
            <div class="col-12"><div class="form-text mt-0" id="skInfo">&nbsp;</div></div>
          </div>

          <div class="sf-sec">Tempat &amp; dokter</div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold" for="ruangan">Ruangan / tempat meninggal <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="ruangan" id="ruangan" maxlength="255" placeholder="Mis. IGD, Rawat Inap – Kamar Melati 2" required>
              <div class="sf-opsi mt-2" id="skRuangCepat">
                <?php foreach ($skRuang as $i => $r): ?>
                  <input type="radio" name="_ruang" id="skRuang<?= $i ?>" value="<?= $sfH($r) ?>"><label for="skRuang<?= $i ?>"><?= $sfH($r) ?></label>
                <?php endforeach; ?>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold" for="dokter_menyatakan">Dokter yang menyatakan <span class="text-danger">*</span></label>
              <select class="form-select" name="dokter_menyatakan" id="dokter_menyatakan" required>
                <option value="">Pilih dokter…</option>
                <?php foreach ($skDokter as $d): ?>
                  <option value="<?= $sfH($d['id_doctor']) ?>"><?= $sfH($d['doctor_name']) ?></option>
                <?php endforeach; ?>
              </select>
              <div class="form-text"><?= $skDokter ? 'Otomatis dipilih dari dokter kunjungan bila cocok.' : '<span class="text-danger">Belum ada dokter aktif di master dokter.</span>' ?></div>
            </div>
          </div>
<?php require '_kerangka_bawah.php'; ?>
  <script>
    (function() {
      const $id = id => document.getElementById(id);
      const F = SuratForm;
      const kini = () => new Date().toLocaleString('sv-SE', { timeZone: 'Asia/Jakarta' }).slice(0, 16); // "YYYY-MM-DD HH:MM"
      const tglSaja = v => (F.isi(v) && /^\d{4}-\d{2}-\d{2}/.test(String(v)) && String(v) >= '1900') ? String(v).slice(0, 10) : '';
      const elTgl = $id('tanggal_kematian'), elJam = $id('waktu_kematian'), elRuang = $id('ruangan'), elDok = $id('dokter_menyatakan');

      function usiaSaat(lahir, tgl) {
        lahir = tglSaja(lahir);
        if (!lahir || !tgl || tgl < lahir) return '';
        const [ly, lm, ld] = lahir.split('-').map(Number), [ty, tm, td] = tgl.split('-').map(Number);
        let th = ty - ly; if (tm < lm || (tm === lm && td < ld)) th--;
        if (th >= 1) return th + ' tahun';
        const hari = Math.round((Date.UTC(ty, tm - 1, td) - Date.UTC(ly, lm - 1, ld)) / 86400000);
        return hari >= 30 ? Math.floor(hari / 30) + ' bulan' : hari + ' hari';
      }
      function info() {
        const t = elTgl.value, j = elJam.value, k = F.kunjungan && F.kunjungan();
        elTgl.max = kini().slice(0, 10);
        elTgl.min = k ? tglSaja(k.visit_date) : '';
        if (!t) { $id('skInfo').innerHTML = '&nbsp;'; return; }
        const u = k ? usiaSaat(k.patient_datebirth, t) : '';
        $id('skInfo').innerHTML = `Meninggal <b>${F.tgl(t)}</b>${j ? ' pukul <b>' + F.esc(j) + ' WIB</b>' : ''}${u ? ' · usia ' + u : ''}`;
      }
      function sinkronRuang() {
        const v = elRuang.value.trim().toLowerCase();
        document.querySelectorAll('#skRuangCepat input').forEach(x => { x.checked = x.value.toLowerCase() === v; });
      }
      function pilihDokter(nama) {
        nama = String(nama || '').trim().toLowerCase();
        if (!nama) return;
        const o = [...elDok.options].find(x => x.value && x.text.trim().toLowerCase() === nama);
        if (o) elDok.value = o.value;
      }
      elTgl.addEventListener('change', info);
      elJam.addEventListener('change', info);
      elRuang.addEventListener('input', sinkronRuang);
      $id('skRuangCepat').addEventListener('change', e => { if (e.target.name === '_ruang') { elRuang.value = e.target.value; elRuang.classList.remove('is-invalid'); } });
      $id('skSekarang').addEventListener('click', () => { const n = kini(); elTgl.value = n.slice(0, 10); elJam.value = n.slice(11, 16); info(); });

      F.init({
        api: 'controller/letter/suratKematianController',
        cetak: id => 'module/letter/print/surat-keterangan-kematian?id=' + encodeURIComponent(id),
        cari: 'controller/admisi/patientVisitControllerInOut',
        nama: 'surat keterangan kematian',
        kolom: [
          { data: 'tanggal_kematian', render: (v, t, r) => t === 'display' ? `<div class="small fw-semibold sf-nw">${F.tglPendek(v)}</div><div class="small text-muted sf-nw">${F.isi(r.waktu_kematian) ? 'pukul ' + F.esc(String(r.waktu_kematian).slice(0, 5)) : '-'}</div>` : (v || '') + ' ' + (r.waktu_kematian || '') },
          { data: 'ruangan', render: (v, t) => t === 'display' ? (F.isi(v) ? `<div class="small sf-ket" title="${F.esc(v)}">${F.esc(v)}</div>` : '<span class="small text-muted">-</span>') : (v || '') },
          { data: 'doctor_name', render: (v, t, r) => { const n = v || r.dokter_menyatakan || '-'; return t === 'display' ? `<span class="small sf-dok" title="${F.esc(n)}">${F.esc(n)}</span>` : n; } }
        ],
        kosongkan() {
          elTgl.value = elJam.value = elRuang.value = '';
          elDok.value = '';
          elDok.querySelectorAll('option[data-lama]').forEach(o => o.remove());
          sinkronRuang();
          info();
        },
        pilihKunjungan(k, baru) {
          if (!baru) return;
          // umumnya pasien meninggal pada hari kunjungan / tanggal pulang (resume medis)
          const t = tglSaja(k.tanggal_pulang) || tglSaja(k.visit_date);
          if (t && t <= kini().slice(0, 10)) elTgl.value = t;
          const poli = String(k.id_poli || '').trim();
          if (!elRuang.value.trim() && poli && !/^mencari/i.test(poli)) { elRuang.value = poli; sinkronRuang(); }
          if (!elDok.value) pilihDokter(k.id_doctor);
          info();
        },
        isi(d) {
          elTgl.value = tglSaja(d.tanggal_kematian);
          elJam.value = F.isi(d.waktu_kematian) ? String(d.waktu_kematian).slice(0, 5) : '';
          elRuang.value = d.ruangan || '';
          elDok.querySelectorAll('option[data-lama]').forEach(o => o.remove());
          const dok = String(d.dokter_menyatakan || '');
          if (dok && ![...elDok.options].some(o => o.value === dok)) {
            // dokter sudah nonaktif: tetap tampilkan agar surat bisa disimpan ulang tanpa diganti
            const o = new Option((d.doctor_name || 'Dokter #' + dok) + ' (nonaktif)', dok);
            o.dataset.lama = '1';
            elDok.add(o);
          }
          elDok.value = dok;
          sinkronRuang();
          info();
        },
        validasi(err, tandai) {
          const t = elTgl.value, j = elJam.value, ts = $id('tanggal_surat').value, k = F.kunjungan && F.kunjungan();
          const kunj = k ? tglSaja(k.visit_date) : '', lahir = k ? tglSaja(k.patient_datebirth) : '';
          if (!t) tandai(elTgl, 'tanggal meninggal');
          if (!/^\d{2}:\d{2}/.test(j)) tandai(elJam, 'pukul meninggal');
          if (t && /^\d{2}:\d{2}/.test(j) && t + ' ' + j.slice(0, 5) > kini()) tandai(t > kini().slice(0, 10) ? elTgl : elJam, 'waktu meninggal tidak boleh di masa depan');
          if (t && lahir && t < lahir) tandai(elTgl, 'tanggal meninggal tidak boleh sebelum tanggal lahir');
          if (t && kunj && t < kunj) tandai(elTgl, 'tanggal meninggal tidak boleh sebelum tanggal kunjungan (' + F.tgl(kunj) + ')');
          if (t && ts && ts < t) tandai($id('tanggal_surat'), 'tanggal surat tidak boleh sebelum tanggal meninggal');
          if (!elRuang.value.trim()) tandai(elRuang, 'ruangan / tempat meninggal');
          if (!elDok.value) tandai(elDok, 'dokter yang menyatakan');
        }
      });
    })();
  </script>
</body>

</html>
