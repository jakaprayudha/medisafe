<?php
/**
 * Surat Keterangan Rawat Inap. Kerangka halaman, tabel, pemilih pasien, nomor surat & simpan: _kerangka_atas.php,
 * _kerangka_bawah.php, assets/js/surat-form.js. Di sini hanya field khusus surat rawat inap.
 * Pemilih pasien memakai pencarian kunjungan rawat inap milik controller ini (?cari=).
 *
 * Perbaikan dari versi lama: pencarian tanpa batas & minimal 1 huruf (hasil ganda bila resume medis / permintaan ranap
 * lebih dari satu), ruangan tidak pernah terisi dari kamar pasien, tanggal pulang tidak bisa diisi bila resume medis
 * belum ada (surat kedua dibuat hanya untuk tanggal pulang), lama rawat bisa diketik tetapi diabaikan server, nomor
 * surat terkunci saat ubah walau mode manual, tautan pengaturan nomor salah (module/letter/setting-surat), seluruh
 * riwayat surat dimuat sekaligus, dan script berada di luar </body>.
 */
$SF = [
  'jenis' => 'rawat_inap',
  'judul' => 'Surat Keterangan Rawat Inap',
  'sub' => 'Keterangan pasien sedang / telah menjalani perawatan rawat inap.',
  'ikon' => 'fa-procedures', 'warna' => 'info',
  'kolom' => ['Masa rawat', 'Ruangan', 'Dokter', 'Diagnosa'],
];
require '_kerangka_atas.php';
?>
          <div class="small mt-2 d-none" id="riSudah"></div>

          <div class="sf-sec">Perawatan</div>
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label small fw-semibold" for="tanggal_masuk">Tanggal masuk <span class="text-danger">*</span></label>
              <input type="date" class="form-control" name="tanggal_masuk" id="tanggal_masuk" required>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold" for="tanggal_pulang">Tanggal pulang</label>
              <input type="date" class="form-control" name="tanggal_pulang" id="tanggal_pulang">
              <div class="form-check mt-1">
                <input class="form-check-input" type="checkbox" id="riDirawat">
                <label class="form-check-label small" for="riDirawat">Masih dirawat</label>
              </div>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold">Lama rawat</label>
              <div class="form-control bg-light" id="riLama">-</div>
              <div class="form-text">Dihitung dari tanggal masuk &amp; pulang.</div>
            </div>
            <div class="col-12 d-none" id="riSaran"></div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold" for="ruangan">Ruangan / kamar</label>
              <input type="text" class="form-control" name="ruangan" id="ruangan" maxlength="255" placeholder="Mis. MELATI-01">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Dokter penanggung jawab</label>
              <div class="form-control bg-light text-truncate" id="riDokter">-</div>
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold">Diagnosa</label>
              <div class="form-control bg-light" id="riDiagnosa" style="min-height:38px">-</div>
              <div class="form-text">Diambil dari resume medis / diagnosa kunjungan.</div>
            </div>
          </div>

          <div class="sf-sec">Keterangan</div>
          <div class="sf-opsi mb-2" id="riKetCepat">
            <input type="radio" name="_ket" id="riKet1" value="Pasien masih menjalani perawatan rawat inap."><label for="riKet1">Masih dirawat</label>
            <input type="radio" name="_ket" id="riKet2" value="Pasien telah selesai menjalani perawatan rawat inap."><label for="riKet2">Selesai dirawat</label>
          </div>
          <textarea class="form-control" name="keterangan" id="keterangan" rows="3" maxlength="2000" placeholder="Keterangan tambahan (opsional)"></textarea>
<?php require '_kerangka_bawah.php'; ?>
  <script>
    (function() {
      const $id = id => document.getElementById(id);
      const F = SuratForm;
      const hari = (a, b) => Math.round((new Date(b + 'T00:00:00Z') - new Date(a + 'T00:00:00Z')) / 86400000);
      const lamaRawat = (m, p) => (m && p && p >= m ? Math.max(1, hari(m, p)) : ''); // sama dengan server
      const teksDiagnosa = r => F.isi(r.diagnosa) ? r.diagnosa : (F.isi(r.kdDiag1) ? r.kdDiag1 + ' - ' + (r.nmDiag1 || '') : (r.diagnosa_kode || ''));

      function info() {
        const m = $id('tanggal_masuk').value, p = $id('tanggal_pulang').value;
        $id('tanggal_pulang').readOnly = $id('riDirawat').checked;
        $id('tanggal_pulang').classList.toggle('bg-light', $id('riDirawat').checked);
        if (m) $id('tanggal_pulang').min = m; else $id('tanggal_pulang').removeAttribute('min');
        const n = lamaRawat(m, p);
        $id('riLama').innerHTML = n ? `<b>${n} hari</b> <span class="text-muted small">(${F.tglPendek(m)} – ${F.tglPendek(p)})</span>` : ($id('riDirawat').checked ? '<span class="text-muted">Masih dirawat</span>' : '-');
      }
      function setPulang(v) {
        $id('tanggal_pulang').value = v || '';
        $id('riDirawat').checked = !v;
        info();
      }
      function setSaran(html) { $id('riSaran').innerHTML = html || ''; $id('riSaran').classList.toggle('d-none', !html); }
      function setSudah(html) { $id('riSudah').innerHTML = html || ''; $id('riSudah').classList.toggle('d-none', !html); }
      function setTampil(dokter, diag) {
        $id('riDokter').textContent = F.isi(dokter) ? dokter : '-';
        $id('riDokter').title = dokter || '';
        $id('riDiagnosa').textContent = F.isi(diag) ? diag : '-';
      }

      $id('tanggal_masuk').addEventListener('change', info);
      $id('tanggal_pulang').addEventListener('change', () => { if ($id('tanggal_pulang').value) $id('riDirawat').checked = false; info(); });
      $id('riDirawat').addEventListener('change', () => { if ($id('riDirawat').checked) $id('tanggal_pulang').value = ''; info(); });
      $id('riKetCepat').addEventListener('change', e => { if (e.target.name === '_ket') $id('keterangan').value = e.target.value; });
      $id('riSaran').addEventListener('click', e => {
        const b = e.target.closest('[data-pulang]');
        if (b) { setPulang(b.dataset.pulang); setSaran(''); }
      });

      F.init({
        api: 'controller/letter/suratRawatInapController',
        cetak: id => 'module/letter/print/surat-keterangan-rawat-inap?id=' + encodeURIComponent(id),
        cari: 'controller/letter/suratRawatInapController?cari=1',
        nama: 'surat keterangan rawat inap',
        kolom: [
          { data: 'tanggal_masuk', render: (v, t, r) => t !== 'display' ? (v || '') :
            `<div class="small fw-semibold sf-nw">${F.isi(r.lama) ? F.esc(r.lama) + ' hari' : (r.tanggal_pulang ? '-' : '<span class="badge bg-warning-subtle">Masih dirawat</span>')}</div>
             <div class="small text-muted sf-nw">${F.tglPendek(v)}${r.tanggal_pulang ? ' – ' + F.tglPendek(r.tanggal_pulang) : ''}</div>` },
          { data: 'ruangan', render: (v, t) => t === 'display' ? `<span class="small sf-dok" title="${F.esc(v || '')}">${F.esc(F.isi(v) ? v : '-')}</span>` : (v || '') },
          { data: 'id_doctor', render: (v, t) => t === 'display' ? `<span class="small sf-dok" title="${F.esc(v || '')}">${F.esc(F.isi(v) ? v : '-')}</span>` : (v || '') },
          { data: 'diagnosa', render: (v, t, r) => { const d = teksDiagnosa(r); return t !== 'display' ? d : (F.isi(d) ? `<div class="small sf-ket" style="max-width:220px" title="${F.esc(d)}">${F.esc(d)}</div>` : '<span class="small text-muted">-</span>'); } }
        ],
        kosongkan() {
          $id('tanggal_masuk').value = '';
          setPulang('');
          $id('riDirawat').checked = false;
          info();
          setTampil('', '');
          setSaran('');
          setSudah('');
        },
        pilihKunjungan(k) {
          // isi dari kunjungan rawat inap: tanggal masuk, pulang (resume medis), kamar terakhir, DPJP, diagnosa
          $id('tanggal_masuk').value = String(k.visit_date || '').slice(0, 10);
          setPulang(k.tanggal_pulang || '');
          if (!k.tanggal_pulang && k.status_ranap !== 'aktif') { $id('riDirawat').checked = false; info(); } // sudah pulang tapi resume belum ada
          $id('ruangan').value = k.ruangan || '';
          setTampil(k.id_doctor, k.diagnosa);
          setSaran(!k.tanggal_pulang && k.status_ranap !== 'aktif' ? '<div class="small text-warning-emphasis"><i class="fas fa-circle-info me-1"></i>Pasien tercatat sudah pulang, tetapi tanggal pulang belum ada di resume medis. Isi tanggal pulang secara manual.</div>' : '');
          const n = +k.jumlah_surat || 0;
          setSudah(n ? `<span class="text-warning-emphasis"><i class="fas fa-triangle-exclamation me-1"></i>Kunjungan ini sudah memiliki <b>${n}</b> surat rawat inap.</span>` : '');
        },
        isi(d) {
          $id('tanggal_masuk').value = d.tanggal_masuk || String(d.visit_date || '').slice(0, 10);
          setPulang(d.tanggal_pulang || '');
          $id('ruangan').value = d.ruangan || '';
          $id('keterangan').value = d.keterangan || '';
          const k = d.kunjungan || {};
          setTampil(d.id_doctor, F.isi(d.diagnosa) ? d.diagnosa : k.diagnosa);
          setSudah('');
          // surat dibuat saat pasien masih dirawat, sekarang resume medis sudah mencatat tanggal pulang
          setSaran(!d.tanggal_pulang && k.tanggal_pulang ? `<div class="sf-pasien d-flex flex-wrap align-items-center gap-2"><i class="fas fa-circle-info text-primary"></i>
            <div class="flex-grow-1 small">Resume medis mencatat pasien pulang <b>${F.tgl(k.tanggal_pulang)}</b>.</div>
            <button type="button" class="btn btn-sm btn-outline-primary" data-pulang="${F.esc(k.tanggal_pulang)}">Pakai tanggal ini</button></div>` : '');
        },
        validasi(err, tandai) {
          const m = $id('tanggal_masuk').value, p = $id('tanggal_pulang').value;
          if (!m) tandai($id('tanggal_masuk'), 'tanggal masuk');
          if (!p && !$id('riDirawat').checked) tandai($id('tanggal_pulang'), 'tanggal pulang (atau centang "Masih dirawat")');
          if (m && p && p < m) tandai($id('tanggal_pulang'), 'tanggal pulang tidak boleh sebelum tanggal masuk');
          if (m && p && p >= m && hari(m, p) > 366) tandai($id('tanggal_pulang'), 'lama rawat maksimal 1 tahun');
        },
        data(fd) {
          fd.set('tanggal_pulang', $id('riDirawat').checked ? '' : $id('tanggal_pulang').value);
          fd.delete('_ket');
        }
      });
    })();
  </script>
</body>

</html>
