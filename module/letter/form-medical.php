<?php
/**
 * Surat Keterangan Berobat. Kerangka halaman, tabel, pemilih pasien, nomor surat & simpan: _kerangka_atas.php,
 * _kerangka_bawah.php, assets/js/surat-form.js. Di sini hanya field khusus surat berobat.
 *
 * Perbaikan dari versi lama: kolom "Dokter Pemeriksa" tabel berisi dokter tapi diberi nama nomor_rm, seluruh isi
 * tabel (nama pasien, keterangan) dirender sebagai HTML tanpa escape (XSS), seluruh riwayat surat dimuat sekaligus,
 * cek setting memanggil endpoint lain & mengarah ke halaman setting yang salah, nomor manual tidak bisa dikoreksi
 * saat ubah, dan form tidak memperlihatkan data kunjungan yang akan tercetak (tanggal, jam, poli, dokter, diagnosa).
 */
$SF = [
  'jenis' => 'berobat',
  'judul' => 'Surat Keterangan Berobat',
  'sub' => 'Bukti pasien telah datang dan mendapat pelayanan di faskes.',
  'ikon' => 'fa-stethoscope', 'warna' => 'primary',
  'kolom' => ['Kunjungan', 'Dokter', 'Keterangan'],
];
require '_kerangka_atas.php';
?>
          <style>
            .sb-kunj { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 8px 16px; border: 1px dashed #dbe2ea; border-radius: 12px; padding: 10px 14px; font-size: .84rem; }
            .sb-kunj .lbl { font-size: .7rem; color: #64748b; text-transform: uppercase; letter-spacing: .03em; }
            .sb-kunj .val { color: #111827; font-weight: 600; overflow-wrap: anywhere; }
            .sb-kunj .val.kosong { color: #94a3b8; font-weight: 400; }
            .sb-kunj .lebar { grid-column: 1 / -1; }
          </style>
          <div class="sf-sec">Kunjungan yang diterangkan</div>
          <div class="sb-kunj" id="sbKunj"><div class="lebar text-muted">Pilih pasien / kunjungan terlebih dahulu.</div></div>
          <div class="alert alert-warning small py-2 px-3 mt-2 mb-0 d-none" id="sbDobel"></div>
          <div class="form-text">Data ini diambil dari kunjungan dan tercetak di surat. Koreksi dilakukan di data kunjungan.</div>

          <div class="sf-sec">Keterangan</div>
          <textarea class="form-control" name="keterangan" id="keterangan" rows="3" maxlength="2000" placeholder="Mis. keluhan / tindakan / anjuran (opsional)"></textarea>
          <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
            <button type="button" class="btn btn-sm btn-light d-none" id="sbVital"><i class="fas fa-heart-pulse me-1"></i>Tambahkan tanda vital</button>
            <button type="button" class="btn btn-sm btn-light d-none" id="sbCatatan"><i class="far fa-note-sticky me-1"></i>Tambahkan catatan kunjungan</button>
            <span class="small text-muted ms-auto" id="sbHitung">0/2000</span>
          </div>
<?php require '_kerangka_bawah.php'; ?>
  <script>
    (function() {
      const $id = id => document.getElementById(id);
      const F = SuratForm, esc = F.esc;
      const KET_MAKS = 2000;
      let kunj = null, token = 0;

      // nilai kosong / "-" / "0" (tanda vital tidak diukur) dianggap tidak ada
      const ada = v => F.isi(v) && !/^(-+|0+([.,]0+)?)$/.test(String(v).trim());
      function teksVital(k) {
        const p = [];
        if (ada(k.tekanan_darah)) p.push('TD: ' + String(k.tekanan_darah).trim() + ' mmHg');
        if (ada(k.nadi)) p.push('Nadi: ' + String(k.nadi).trim() + ' x/menit');
        if (ada(k.suhu)) p.push('Suhu: ' + String(k.suhu).trim() + ' °C');
        if (ada(k.respirasi)) p.push('RR: ' + String(k.respirasi).trim() + ' x/menit');
        if (ada(k.saturasi)) p.push('SpO2: ' + String(k.saturasi).trim() + '%');
        return p.join(', ');
      }
      function diagnosa(k) {
        if (ada(k.kdDiag1)) return k.kdDiag1 + ' - ' + (k.nmDiag1 || '-');
        if (ada(k.diagnosa)) return k.diagnosa + (k.nama_diagnosa ? ' - ' + k.nama_diagnosa : '');
        return '';
      }
      function hitung() { $id('sbHitung').textContent = $id('keterangan').value.length + '/' + KET_MAKS; }
      function tambah(teks) {
        const t = $id('keterangan'), v = t.value.replace(/\s+$/, '');
        if (!teks || v.includes(teks)) return;
        t.value = (v ? v + '\n' : '') + teks;
        hitung();
      }

      // detail kunjungan (diagnosa hanya ada bila sudah dimuat dari server: dg = true)
      function tampil(k, dg) {
        kunj = k;
        const sel = (l, v, lebar) => `<div${lebar ? ' class="lebar"' : ''}><div class="lbl">${l}</div><div class="val${F.isi(v) ? '' : ' kosong'}">${F.isi(v) ? esc(v) : '-'}</div></div>`;
        if (!k) { $id('sbKunj').innerHTML = '<div class="lebar text-muted">Pilih pasien / kunjungan terlebih dahulu.</div>'; }
        else {
          $id('sbKunj').innerHTML = sel('Tanggal berobat', k.visit_date ? F.tgl(k.visit_date) : '') + sel('Jam', k.visit_time) + sel('Poli / layanan', k.id_poli) + sel('Dokter pemeriksa', k.id_doctor)
            + (dg ? sel('Diagnosa', diagnosa(k), true) : '<div class="lebar"><div class="lbl">Diagnosa</div><div class="val kosong"><span class="spinner-border spinner-border-sm me-1"></span>memuat…</div></div>');
        }
        $id('sbVital').classList.toggle('d-none', !(k && teksVital(k)));
        $id('sbCatatan').classList.toggle('d-none', !(k && ada(k.visit_notes)));
        const tgl = k && k.visit_date ? String(k.visit_date).slice(0, 10) : '';
        $id('tanggal_surat').min = tgl;
      }
      async function muatKunjungan(k, idSurat) {
        const t = ++token;
        $id('sbDobel').classList.add('d-none');
        try {
          const r = await fetch('controller/letter/suratBerobatController?id_visit=' + encodeURIComponent(k.id_visit), { credentials: 'same-origin', cache: 'no-store' });
          const j = await r.json().catch(() => null);
          if (t !== token) return;
          if (!j || j.status !== 'success') { tampil(k, true); return; }
          tampil(Object.assign({}, k, j.data), true);
          const lain = (j.data.surat || []).filter(s => +s.id !== +idSurat);
          if (lain.length) {
            $id('sbDobel').innerHTML = '<i class="fas fa-circle-info me-1"></i>Kunjungan ini sudah punya surat berobat: '
              + lain.map(s => `<b>${esc(s.nomor_surat || '-')}</b> (${F.tgl(s.tanggal_surat)})`).join(', ') + '. Tetap bisa disimpan bila memang perlu surat baru.';
            $id('sbDobel').classList.remove('d-none');
          }
        } catch (e) { if (t === token) tampil(k, true); }
      }

      $id('keterangan').addEventListener('input', hitung);
      $id('sbVital').addEventListener('click', () => kunj && tambah(teksVital(kunj)));
      $id('sbCatatan').addEventListener('click', () => kunj && tambah(String(kunj.visit_notes || '').trim()));
      $('#sfPilih').on('select2:clear', () => { token++; tampil(null); $id('sbDobel').classList.add('d-none'); });

      F.init({
        api: 'controller/letter/suratBerobatController',
        cetak: id => 'module/letter/print/surat-keterangan-berobat?id=' + encodeURIComponent(id),
        cari: 'controller/admisi/patientVisitController',
        nama: 'surat keterangan berobat',
        kolom: [
          { data: 'visit_date', render: (v, t, r) => t === 'display' ? `<div class="small sf-nw">${F.tglPendek(v)}${r.visit_time ? ' · ' + esc(String(r.visit_time).slice(0, 5)) : ''}</div><div class="small text-muted sf-dok" title="${esc(r.id_poli || '')}">${esc(r.id_poli || '-')}</div>` : [v, r.id_poli].join(' ') },
          { data: 'id_doctor', render: v => `<span class="small sf-dok" title="${esc(v || '')}">${esc(v || '-')}</span>` },
          { data: 'keterangan', render: (v, t) => t === 'display' ? (F.isi(v) ? `<div class="small sf-ket" title="${esc(v)}">${esc(v)}</div>` : '<span class="small text-muted">-</span>') : (v || '') }
        ],
        kosongkan() {
          token++;
          tampil(null);
          $id('sbDobel').classList.add('d-none');
          hitung();
        },
        pilihKunjungan(k, baru) {
          tampil(k, false);
          muatKunjungan(k, 0);
          // surat dibuat setelah kunjungan: tanggal surat tidak boleh sebelum tanggal berobat
          const tgl = k.visit_date ? String(k.visit_date).slice(0, 10) : '';
          if (baru && tgl && $id('tanggal_surat').value < tgl) $id('tanggal_surat').value = tgl;
        },
        isi(d) {
          token++;
          $id('keterangan').value = d.keterangan || '';
          hitung();
          tampil(d, true);
          $id('sbDobel').classList.add('d-none');
        },
        validasi(err, tandai) {
          const ts = $id('tanggal_surat').value, min = $id('tanggal_surat').min;
          if (ts && min && ts < min) tandai($id('tanggal_surat'), 'tanggal surat tidak boleh sebelum tanggal berobat (' + F.tgl(min) + ')');
          if ($id('keterangan').value.trim().length > KET_MAKS) tandai($id('keterangan'), 'keterangan maksimal ' + KET_MAKS + ' karakter');
        }
      });
    })();
  </script>
</body>

</html>
