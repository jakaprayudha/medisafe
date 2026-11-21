<?php
/**
 * Surat Keterangan Sehat. Kerangka halaman, tabel, pemilih pasien, nomor surat & simpan: _kerangka_atas.php,
 * _kerangka_bawah.php, assets/js/surat-form.js. Di sini hanya field khusus surat sehat.
 *
 * Perbaikan dari versi lama: dua select2 dipasang pada elemen yang sama (pencarian ganti-ganti endpoint), tanda vital
 * tidak terisi dari kunjungan di mode nomor manual (handler berhenti sebelum mengisi), suhu/respirasi/BMI tampil
 * sebagai isian padahal tidak pernah disimpan, kolom "Dokter Pemeriksa" diisi dari kolom yang salah, setting nomor
 * diambil ulang lewat AJAX setiap buka form, seluruh riwayat surat dimuat sekaligus, dan data tabel tanpa escape HTML.
 */
$SF = [
  'jenis' => 'sehat',
  'judul' => 'Surat Keterangan Sehat',
  'sub' => 'Menerangkan kondisi sehat pasien berdasarkan hasil pemeriksaan.',
  'ikon' => 'fa-heart-pulse', 'warna' => 'success',
  'kolom' => ['Pemeriksaan', 'Keperluan', 'Dokter'],
];
require '_kerangka_atas.php';
$SH_KEPERLUAN = ['Administrasi', 'Pekerjaan', 'Pendidikan', 'Lainnya'];
?>
          <div class="sf-sec">Pemeriksaan fisik</div>
          <div class="row g-3">
            <div class="col-md-6 col-lg-3">
              <label class="form-label small fw-semibold" for="tekanan_darah">Tekanan darah</label>
              <div class="input-group">
                <input type="text" class="form-control" name="tekanan_darah" id="tekanan_darah" maxlength="20" placeholder="120/80" inputmode="numeric">
                <span class="input-group-text">mmHg</span>
              </div>
            </div>
            <div class="col-md-6 col-lg-3">
              <label class="form-label small fw-semibold" for="nadi">Nadi</label>
              <div class="input-group">
                <input type="text" class="form-control" name="nadi" id="nadi" maxlength="10" placeholder="80" inputmode="numeric">
                <span class="input-group-text">x/menit</span>
              </div>
            </div>
            <div class="col-md-6 col-lg-3">
              <label class="form-label small fw-semibold" for="berat_badan">Berat badan</label>
              <div class="input-group">
                <input type="text" class="form-control" name="berat_badan" id="berat_badan" maxlength="10" placeholder="65" inputmode="decimal">
                <span class="input-group-text">kg</span>
              </div>
            </div>
            <div class="col-md-6 col-lg-3">
              <label class="form-label small fw-semibold" for="tinggi_badan">Tinggi badan</label>
              <div class="input-group">
                <input type="text" class="form-control" name="tinggi_badan" id="tinggi_badan" maxlength="10" placeholder="170" inputmode="decimal">
                <span class="input-group-text">cm</span>
              </div>
            </div>
            <div class="col-12">
              <div class="sh-info" id="shInfo"></div>
              <div class="form-text">Terisi otomatis dari tanda vital kunjungan; boleh dikoreksi. Suhu yang dicetak diambil dari data kunjungan.</div>
            </div>
          </div>

          <div class="sf-sec">Keperluan</div>
          <div class="sf-opsi" id="shKeperluan">
            <?php foreach ($SH_KEPERLUAN as $i => $k): ?>
              <input type="radio" name="keperluan" id="kep<?= $i ?>" value="<?= $sfH($k) ?>"><label for="kep<?= $i ?>"><?= $sfH($k) ?></label>
            <?php endforeach; ?>
          </div>

          <div class="sf-sec">Keterangan</div>
          <textarea class="form-control" name="keterangan" id="keterangan" rows="3" maxlength="1000" placeholder="Mis. hasil pemeriksaan tambahan / catatan dokter (opsional)"></textarea>
<?php require '_kerangka_bawah.php'; ?>
  <style>
    .sh-info { display: flex; flex-wrap: wrap; gap: 6px; }
    .sh-info span { border: 1px solid #e5eaef; border-radius: 8px; background: #f8fafc; padding: 3px 10px; font-size: .8rem; color: #334155; }
    .sh-info span b { color: #111827; }
    .sh-pem { font-size: .78rem; color: #334155; white-space: nowrap; }
    #shKeperluan.sh-salah label { border-color: #fa896b; }
  </style>
  <script>
    (function() {
      const $id = id => document.getElementById(id);
      const F = SuratForm, esc = F.esc;
      const KEP = <?= json_encode($SH_KEPERLUAN, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
      // nilai lama bisa berisi satuan ("95 kg", "78 x/menit") -> ambil angkanya saja
      const angka = v => { const m = String(v ?? '').match(/\d+(?:[.,]\d+)?/); return m ? m[0].replace(',', '.') : ''; };
      const td = v => { const m = String(v ?? '').match(/(\d{2,3})\s*\/\s*(\d{2,3})/); return m ? m[1] + '/' + m[2] : String(v ?? '').trim(); };
      let vital = {}; // suhu / respirasi / saturasi dari kunjungan (hanya informasi)

      function bmi() {
        const bb = parseFloat(angka($id('berat_badan').value)), tb = parseFloat(angka($id('tinggi_badan').value));
        if (!(bb > 0 && tb >= 30)) return null;
        const n = bb / Math.pow(tb / 100, 2);
        return { n: n.toFixed(1), k: n < 18.5 ? 'kurang' : n < 25 ? 'normal' : n < 30 ? 'berlebih' : 'obesitas' };
      }
      function info() {
        const b = bmi(), x = [];
        if (b) x.push(`IMT <b>${b.n}</b> (${b.k})`);
        if (F.isi(vital.suhu)) x.push(`Suhu <b>${esc(vital.suhu)}</b> °C`);
        if (F.isi(vital.respirasi)) x.push(`Respirasi <b>${esc(vital.respirasi)}</b> x/menit`);
        if (F.isi(vital.saturasi)) x.push(`SpO₂ <b>${esc(vital.saturasi)}</b> %`);
        $id('shInfo').innerHTML = x.map(s => `<span>${s}</span>`).join('');
      }
      ['berat_badan', 'tinggi_badan'].forEach(i => $id(i).addEventListener('input', info));

      function setKeperluan(v) {
        v = String(v || '').trim();
        const box = $id('shKeperluan');
        box.classList.remove('sh-salah');
        box.querySelectorAll('.sh-ekstra').forEach(e => e.remove());
        box.querySelectorAll('input').forEach(i => { i.checked = false; });
        if (!v) return;
        let el = [...box.querySelectorAll('input')].find(i => i.value.toLowerCase() === v.toLowerCase());
        if (!el) { // nilai lama di luar daftar tetap bisa dipertahankan
          box.insertAdjacentHTML('beforeend', `<input type="radio" class="sh-ekstra" name="keperluan" id="kepX" value="${esc(v)}"><label class="sh-ekstra" for="kepX">${esc(v)}</label>`);
          el = $id('kepX');
        }
        el.checked = true;
      }
      $id('shKeperluan').addEventListener('change', () => $id('shKeperluan').classList.remove('sh-salah'));

      function isiVital(s) {
        $id('tekanan_darah').value = F.isi(s.tekanan_darah) ? td(s.tekanan_darah) : '';
        $id('nadi').value = angka(s.nadi);
        $id('berat_badan').value = angka(s.berat_badan);
        $id('tinggi_badan').value = angka(s.tinggi_badan);
      }

      const rentang = (el, label, min, max, err, tandai) => {
        const v = el.value.trim();
        if (v === '') return;
        if (!/^\d{1,3}([.,]\d{1,2})?$/.test(v)) return tandai(el, label + ' harus angka');
        const n = parseFloat(v.replace(',', '.'));
        if (n < min || n > max) tandai(el, `${label} ${min}–${max}`);
      };

      F.init({
        api: 'controller/letter/suratSehatController',
        cetak: id => 'module/letter/print/surat-keterangan-sehat?id=' + encodeURIComponent(id),
        cari: 'controller/admisi/patientVisitController',
        nama: 'surat keterangan sehat',
        kolom: [
          { data: 'tekanan_darah', orderable: false, render: (v, t, r) => {
            const a = [F.isi(v) ? 'TD ' + td(v) : '', F.isi(r.nadi) ? 'N ' + angka(r.nadi) : ''].filter(Boolean).join(' · ');
            const b = [F.isi(r.berat_badan) ? angka(r.berat_badan) + ' kg' : '', F.isi(r.tinggi_badan) ? angka(r.tinggi_badan) + ' cm' : ''].filter(Boolean).join(' / ');
            if (t !== 'display') return a + ' ' + b;
            return a || b ? `<div class="sh-pem">${esc(a || '-')}</div><div class="sh-pem text-muted">${esc(b || '-')}</div>` : '<span class="small text-muted">-</span>';
          } },
          { data: 'keperluan', render: (v, t, r) => t !== 'display' ? [v, r.keterangan].join(' ') :
            `<div class="small fw-semibold">${esc(v || '-')}</div>${F.isi(r.keterangan) ? `<div class="small text-muted sf-ket" title="${esc(r.keterangan)}">${esc(r.keterangan)}</div>` : ''}` },
          { data: 'id_doctor', render: v => `<span class="small sf-dok" title="${esc(v || '')}">${esc(v || '-')}</span>` }
        ],
        kosongkan() {
          isiVital({});
          vital = {};
          setKeperluan('');
          info();
        },
        pilihKunjungan(k, baru) {
          vital = { suhu: k.suhu, respirasi: k.respirasi, saturasi: k.saturasi };
          if (baru) isiVital(k);
          info();
        },
        isi(d) {
          isiVital(d);
          vital = { suhu: d.suhu, respirasi: d.respirasi, saturasi: d.saturasi };
          setKeperluan(d.keperluan);
          $id('keterangan').value = d.keterangan || '';
          info();
        },
        validasi(err, tandai) {
          const t = $id('tekanan_darah'), v = t.value.trim();
          if (v !== '') {
            const m = v.match(/^(\d{2,3})\s*\/\s*(\d{2,3})\s*(mmhg)?$/i);
            if (!m) tandai(t, 'tekanan darah berformat 120/80');
            else if (+m[1] < 50 || +m[1] > 300 || +m[2] < 20 || +m[2] > 200 || +m[2] >= +m[1]) tandai(t, 'nilai tekanan darah tidak wajar');
          }
          rentang($id('nadi'), 'nadi', 20, 250, err, tandai);
          rentang($id('berat_badan'), 'berat badan', 1, 400, err, tandai);
          rentang($id('tinggi_badan'), 'tinggi badan', 30, 250, err, tandai);
          if (!document.querySelector('#shKeperluan input:checked')) { $id('shKeperluan').classList.add('sh-salah'); err.push('keperluan'); }
        }
      });
    })();
  </script>
</body>

</html>
