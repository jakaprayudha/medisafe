<?php
/**
 * Persalinan / ANC — ringkasan kehamilan saat ini + riwayat ANC pasien (semua kunjungan di faskes ini).
 * Catatan kunjungan ini bisa ditambah/diubah/dihapus; catatan kunjungan lain hanya dilihat.
 * Perbaikan: $_GET no/rm/rme tanpa default (warning) & dicetak mentah ke input (XSS), isi tabel & nama file gambar
 * disisipkan tanpa escape, tidak ada fitur ubah, HPL/usia kehamilan diketik manual, tidak ada pesan per kolom,
 * modal di luar </body>, kolom Aksi di paling kanan.
 */
$title = 'Persalinan (ANC)';
require '../../controller/view.php';
$no = trim((string) ($_GET['no'] ?? ''));
$rm = trim((string) ($_GET['rm'] ?? ''));
$rme = (string) ($_GET['rme'] ?? '');
$h = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .anc .kepala { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-bottom: 14px; }
    .anc .kepala .ic { width: 42px; height: 42px; border-radius: 12px; background: #fce7f3; color: #9d174d; display: inline-flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
    .anc .kepala h5 { margin: 0; font-weight: 700; color: #0f172a; }
    .anc .kepala small { color: #475569; }
    .anc .ringkas { border: 1px solid #fbcfe8; background: #fdf2f8; border-radius: 14px; padding: 14px 16px; margin-bottom: 16px; }
    .anc .ringkas .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 10px 16px; }
    .anc .ringkas small { display: block; font-size: .7rem; color: #831843; text-transform: uppercase; letter-spacing: .04em; font-weight: 600; }
    .anc .ringkas b { font-size: 1.02rem; color: #0f172a; }
    .anc .ringkas .sub { font-size: .78rem; color: #475569; }
    .anc .bar { height: 8px; background: #fbcfe8; border-radius: 6px; overflow: hidden; margin-top: 10px; }
    .anc .bar span { display: block; height: 100%; background: #db2777; border-radius: 6px; }
    .anc .sec { border: 1px solid #e2e8f0; border-radius: 14px; padding: 14px 16px; background: #fff; }
    .anc .sec-h { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-bottom: 10px; }
    .anc .sec-h b { font-size: 1rem; color: #0f172a; }
    .anc table.t { width: 100%; border-collapse: collapse; font-size: .86rem; }
    .anc table.t th { font-size: .7rem; text-transform: uppercase; color: #475569; font-weight: 700; background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 7px 8px; white-space: nowrap; }
    .anc table.t td { border-bottom: 1px solid #eef2f6; padding: 8px; vertical-align: top; color: #1f2937; }
    .anc table.t tr.ini td { background: #fffbeb; }
    .anc table.t tr:last-child td { border-bottom: 0; }
    .anc .aksi { white-space: nowrap; }
    .anc .aksi .btn { padding: 3px 9px; font-weight: 600; }
    .anc .tgl { font-weight: 700; color: #0f172a; white-space: nowrap; }
    .anc .tag { font-size: .68rem; font-weight: 700; border-radius: 6px; padding: 1px 7px; display: inline-block; white-space: nowrap; }
    .anc .tag-ini { background: #fef3c7; color: #92400e; }
    .anc .tag-lain { background: #f1f5f9; color: #475569; }
    .anc .nilai { white-space: nowrap; }
    .anc .waspada { color: #b91c1c; font-weight: 700; }
    .anc .waspada::after { content: " ▲"; font-size: .65rem; }
    .anc .catatan { font-size: .8rem; color: #334155; white-space: pre-line; max-width: 260px; }
    .anc .thumb { width: 48px; height: 48px; object-fit: cover; border-radius: 8px; cursor: zoom-in; border: 1px solid #e2e8f0; }
    .anc .kosong { text-align: center; padding: 26px 10px; color: #475569; }
    .anc .kosong i { font-size: 2rem; color: #94a3b8; display: block; margin-bottom: 6px; }
    #ancModal .judul-sec { font-size: .74rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: .04em; margin: 4px 0 8px; }
    #ancModal .form-text.auto { color: #0369a1; }
    #ancModal .peringatan { font-size: .78rem; color: #b91c1c; font-weight: 600; }
  </style>
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
          if ($rme === 'a') include 'menu_rme.php';
          elseif ($rme === 'b') include 'menu_rmeb.php';
          elseif ($rme === 'c') include 'menu_rme_inap.php';
          ?>
          <div class="row">
            <div class="col-12"><?php require 'card-pasien.php'; ?></div>
            <div class="col-12">
              <div class="card w-100">
                <div class="card-body p-4 anc">
                  <div class="kepala">
                    <span class="ic"><i class="ti ti-heart-handshake"></i></span>
                    <div class="flex-grow-1">
                      <h5>Antenatal Care (ANC)</h5>
                      <small>Pemeriksaan kehamilan per kunjungan. HPL &amp; usia kehamilan dihitung otomatis dari HPHT.</small>
                    </div>
                    <button type="button" class="btn btn-primary" id="btnTambah" disabled><i class="ti ti-plus me-1"></i>Tambah catatan ANC</button>
                  </div>
                  <div class="alert alert-danger d-none" id="ancBatal"><i class="ti ti-ban me-1"></i>Kunjungan ini sudah dibatalkan — catatan baru tidak bisa ditambahkan.</div>
                  <div id="ancRingkas"></div>
                  <div class="sec">
                    <div class="sec-h">
                      <b>Riwayat ANC pasien</b>
                      <span class="small text-muted" id="ancJumlah"></span>
                      <span class="small text-muted ms-auto"><span class="tag tag-ini">Kunjungan ini</span> bisa diubah/dihapus · catatan kunjungan lain hanya dilihat</span>
                    </div>
                    <div class="table-responsive">
                      <table class="t">
                        <thead>
                          <tr>
                            <th>Aksi</th>
                            <th>Tanggal</th>
                            <th>Usia kehamilan</th>
                            <th>TD</th>
                            <th>BB</th>
                            <th>TFU</th>
                            <th>DJJ</th>
                            <th>Presentasi</th>
                            <th>Protein urine</th>
                            <th>Edema</th>
                            <th>Gambar</th>
                            <th>Catatan</th>
                          </tr>
                        </thead>
                        <tbody id="ancBody">
                          <tr><td colspan="12" class="kosong"><span class="spinner-border spinner-border-sm me-2"></span>Memuat…</td></tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL FORM ANC -->
  <div class="modal fade" id="ancModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <form id="ancForm" class="modal-content" enctype="multipart/form-data" novalidate autocomplete="off">
        <div class="modal-header">
          <h5 class="modal-title" id="ancJudul">Tambah catatan ANC</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_anc" id="id_anc">
          <input type="hidden" name="nomor_rm" value="<?= $h($rm) ?>">

          <div class="judul-sec">Kehamilan</div>
          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label class="form-label small fw-semibold" for="tanggal_kontrol">Tanggal kontrol <span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="tanggal_kontrol" name="tanggal_kontrol">
              <div class="invalid-feedback"></div>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold" for="hpht">HPHT</label>
              <input type="date" class="form-control" id="hpht" name="hpht">
              <div class="invalid-feedback"></div>
              <div class="form-text">Hari pertama haid terakhir</div>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold" for="taksiran_persalinan">Taksiran persalinan (HPL)</label>
              <input type="date" class="form-control" id="taksiran_persalinan" name="taksiran_persalinan">
              <div class="invalid-feedback"></div>
              <div class="form-text auto" id="hplInfo"></div>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold" for="usia_gestasi">Usia kehamilan</label>
              <input type="text" class="form-control" id="usia_gestasi" name="usia_gestasi" maxlength="50" placeholder="mis. 32 minggu">
              <div class="invalid-feedback"></div>
              <div class="form-text auto" id="ukInfo"></div>
            </div>
            <div class="col-md-8">
              <label class="form-label small fw-semibold">G / P / A</label>
              <div class="input-group">
                <span class="input-group-text">G</span><input type="number" min="1" max="20" class="form-control" id="gravida" name="gravida" title="Gravida (jumlah kehamilan, termasuk sekarang)">
                <span class="input-group-text">P</span><input type="number" min="0" max="20" class="form-control" id="para" name="para" title="Para (persalinan)">
                <span class="input-group-text">A</span><input type="number" min="0" max="20" class="form-control" id="abortus" name="abortus" title="Abortus">
              </div>
              <div class="small text-danger mt-1 d-none" id="gpaErr"></div>
            </div>
          </div>

          <div class="judul-sec">Pemeriksaan</div>
          <div class="row g-3 mb-3">
            <div class="col-md-3 col-6">
              <label class="form-label small fw-semibold" for="tekanan_darah">Tekanan darah</label>
              <div class="input-group"><input type="text" inputmode="numeric" class="form-control" id="tekanan_darah" name="tekanan_darah" placeholder="120/80" maxlength="7"><span class="input-group-text">mmHg</span></div>
              <div class="invalid-feedback d-block"></div>
              <div class="peringatan" id="tdInfo"></div>
            </div>
            <div class="col-md-3 col-6">
              <label class="form-label small fw-semibold" for="berat_badan">Berat badan</label>
              <div class="input-group"><input type="text" inputmode="decimal" class="form-control" id="berat_badan" name="berat_badan"><span class="input-group-text">kg</span></div>
              <div class="invalid-feedback d-block"></div>
            </div>
            <div class="col-md-3 col-6">
              <label class="form-label small fw-semibold" for="tinggi_fundus">Tinggi fundus</label>
              <div class="input-group"><input type="text" inputmode="decimal" class="form-control" id="tinggi_fundus" name="tinggi_fundus"><span class="input-group-text">cm</span></div>
              <div class="invalid-feedback d-block"></div>
            </div>
            <div class="col-md-3 col-6">
              <label class="form-label small fw-semibold" for="djj">DJJ</label>
              <div class="input-group"><input type="text" inputmode="numeric" class="form-control" id="djj" name="djj" placeholder="140"><span class="input-group-text">x/mnt</span></div>
              <div class="invalid-feedback d-block"></div>
              <div class="peringatan" id="djjInfo"></div>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold" for="presentasi">Presentasi</label>
              <select class="form-select" id="presentasi" name="presentasi">
                <option value="">— Pilih —</option>
                <option>Sefalik</option><option>Bokong</option><option>Lintang</option><option>Belum Menetap</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold" for="protein_urine">Protein urine</label>
              <select class="form-select" id="protein_urine" name="protein_urine">
                <option value="">— Pilih —</option>
                <option>Negatif</option><option>Trace</option><option>+1</option><option>+2</option><option>+3</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold" for="edema">Edema</label>
              <select class="form-select" id="edema" name="edema">
                <option value="">— Pilih —</option>
                <option>Tidak Ada</option><option>Ringan</option><option>Sedang</option><option>Berat</option>
              </select>
            </div>
          </div>

          <div class="judul-sec">Dokumentasi &amp; catatan</div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold" for="gambar">Gambar (USG / dokumentasi)</label>
              <input type="file" class="form-control" id="gambar" name="gambar" accept="image/jpeg,image/png,image/webp">
              <div class="invalid-feedback"></div>
              <div class="form-text">JPG / PNG / WEBP, maks. 5 MB.</div>
              <div id="gambarLama" class="d-none mt-2 d-flex align-items-center gap-2">
                <img id="gambarLamaImg" class="rounded border" style="width:64px;height:64px;object-fit:cover" alt="">
                <div class="form-check mb-0">
                  <input class="form-check-input" type="checkbox" value="1" id="hapus_gambar" name="hapus_gambar">
                  <label class="form-check-label small" for="hapus_gambar">Hapus gambar ini</label>
                </div>
              </div>
              <img id="gambarPreview" class="img-fluid rounded border mt-2 d-none" style="max-height:160px" alt="Pratinjau">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold" for="catatan">Catatan</label>
              <textarea class="form-control" id="catatan" name="catatan" rows="5" maxlength="2000" placeholder="Keluhan, edukasi, tindak lanjut…"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary px-4" id="btnSimpan"><i class="ti ti-device-floppy me-1"></i>Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL GAMBAR -->
  <div class="modal fade" id="gambarModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Gambar ANC</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body text-center"><img id="gambarFull" class="img-fluid rounded" alt="Gambar ANC"></div>
      </div>
    </div>
  </div>

  <?php require 'library.php'; ?>
  <script>
    (function() {
      'use strict';
      const API = 'controller/visit/persalinanAncController?no=' + encodeURIComponent(<?= json_encode($no) ?>);
      const UPL = 'uploads/persalinan/';
      const $id = i => document.getElementById(i);
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const BLN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      const tglId = s => { const m = String(s || '').match(/^(\d{4})-(\d{2})-(\d{2})/); return m ? (+m[3]) + ' ' + BLN[m[2] - 1] + ' ' + m[1] : '-'; };
      const hariIni = () => new Date().toLocaleDateString('sv-SE', { timeZone: 'Asia/Jakarta' });
      const keUTC = s => { const [y, m, d] = s.split('-').map(Number); return Date.UTC(y, m - 1, d); };
      const selisihHari = (a, b) => Math.round((keUTC(b) - keUTC(a)) / 86400000);
      const tambahHari = (s, n) => new Date(keUTC(s) + n * 86400000).toISOString().slice(0, 10);
      const uk = hari => Math.floor(hari / 7) + ' minggu' + (hari % 7 ? ' ' + (hari % 7) + ' hari' : '');
      const angka = v => { const m = String(v ?? '').match(/^\s*(\d+(?:[.,]\d+)?)/); return m ? parseFloat(m[1].replace(',', '.')) : null; };
      // nilai yang perlu diwaspadai: TD >= 140/90 (hipertensi kehamilan), DJJ di luar 120–160, proteinuria +1 ke atas, edema sedang/berat
      const tdTinggi = v => { const m = String(v || '').match(/^(\d+)\/(\d+)/); return m && (+m[1] >= 140 || +m[2] >= 90); };
      const djjAbn = v => { const n = angka(v); return n !== null && (n < 120 || n > 160); };

      let DATA = [], BATAL = true;

      /* ================= DAFTAR ================= */
      function muat() {
        $.ajax({ url: API, dataType: 'json', cache: false }).done(res => {
          DATA = res.data || [];
          BATAL = !!(res.kunjungan && res.kunjungan.batal);
          $id('btnTambah').disabled = BATAL;
          $id('ancBatal').classList.toggle('d-none', !BATAL);
          render();
        }).fail(x => {
          $id('ancBody').innerHTML = `<tr><td colspan="12" class="kosong text-danger"><i class="ti ti-alert-triangle"></i>${esc((x.responseJSON && x.responseJSON.message) || 'Gagal memuat data ANC.')}</td></tr>`;
        });
      }

      function render() {
        $id('ancJumlah').textContent = DATA.length ? '· ' + DATA.length + ' catatan' : '';
        ringkasan();
        if (!DATA.length) {
          $id('ancBody').innerHTML = `<tr><td colspan="12" class="kosong"><i class="ti ti-heart-handshake"></i>Belum ada catatan ANC untuk pasien ini.</td></tr>`;
          return;
        }
        $id('ancBody').innerHTML = DATA.map(r => {
          const aksi = r.ini ? `<div class="aksi d-flex gap-1">
              <button type="button" class="btn btn-sm btn-warning" data-ubah="${r.id_anc}" title="Ubah"><i class="ti ti-pencil"></i></button>
              <button type="button" class="btn btn-sm btn-danger" data-hapus="${r.id_anc}" title="Hapus"><i class="ti ti-trash"></i></button></div>`
            : '<span class="tag tag-lain">Kunjungan lain</span>';
          const n = (v, sat, waspada) => v ? `<span class="nilai${waspada ? ' waspada' : ''}">${esc(v)}${sat && /^\d+(\.\d+)?$/.test(v) ? ' ' + sat : ''}</span>` : '<span class="text-muted">-</span>';
          const pu = r.protein_urine, ed = r.edema;
          return `<tr class="${r.ini ? 'ini' : ''}">
            <td>${aksi}</td>
            <td><div class="tgl">${esc(tglId(r.tanggal_kontrol))}</div>${r.ini ? '<span class="tag tag-ini">Kunjungan ini</span>' : ''}</td>
            <td>${n(r.usia_gestasi)}${r.gravida ? `<div class="small text-muted">G${esc(r.gravida)} P${esc(r.para || 0)} A${esc(r.abortus || 0)}</div>` : ''}</td>
            <td>${n(r.tekanan_darah, '', tdTinggi(r.tekanan_darah))}</td>
            <td>${n(r.berat_badan, 'kg')}</td>
            <td>${n(r.tinggi_fundus, 'cm')}</td>
            <td>${n(r.djj, '', djjAbn(r.djj))}</td>
            <td>${n(r.presentasi)}</td>
            <td>${n(pu, '', pu && /^\+/.test(pu))}</td>
            <td>${n(ed, '', ed === 'Sedang' || ed === 'Berat')}</td>
            <td>${r.gambar ? `<img src="${UPL + encodeURIComponent(r.gambar)}" class="thumb" data-gambar="${esc(r.gambar)}" alt="Gambar ANC" loading="lazy">` : '<span class="text-muted">-</span>'}</td>
            <td><div class="catatan">${r.catatan ? esc(r.catatan) : '<span class="text-muted">-</span>'}</div></td>
          </tr>`;
        }).join('');
      }

      // ringkasan kehamilan dari catatan terakhir yang punya HPHT
      function ringkasan() {
        const r = DATA.find(x => x.hpht);
        if (!r) { $id('ancRingkas').innerHTML = ''; return; }
        const hari = selisihHari(r.hpht, hariIni());
        // HPL tercatat dipakai bila wajar (±14 hari dari HPHT + 280); bila meleset tampilkan hasil hitung + catatan
        const hplHitung = tambahHari(r.hpht, 280);
        const meleset = r.taksiran_persalinan && Math.abs(selisihHari(hplHitung, r.taksiran_persalinan)) > 14;
        const hpl = r.taksiran_persalinan && !meleset ? r.taksiran_persalinan : hplHitung;
        const sisa = selisihHari(hariIni(), hpl);
        const selesai = hari > 44 * 7;   // kemungkinan sudah bersalin / data lama
        const tri = hari < 14 * 7 ? 'Trimester I' : hari < 28 * 7 ? 'Trimester II' : 'Trimester III';
        const kunj = DATA.filter(x => x.tanggal_kontrol >= r.hpht).length;
        const pct = Math.max(0, Math.min(100, hari / 280 * 100));
        $id('ancRingkas').innerHTML = `<div class="ringkas">
          <div class="grid">
            <div><small>HPHT</small><b>${esc(tglId(r.hpht))}</b></div>
            <div><small>Taksiran persalinan</small><b>${esc(tglId(hpl))}</b><div class="sub">${selesai ? 'lewat' : sisa >= 0 ? sisa + ' hari lagi' : 'lewat ' + (-sisa) + ' hari'}</div>${meleset ? `<div class="sub waspada" title="Ubah catatan lalu kosongkan HPL agar dihitung otomatis">Tercatat ${esc(tglId(r.taksiran_persalinan))} — tidak sesuai HPHT</div>` : ''}</div>
            <div><small>Usia kehamilan hari ini</small><b>${selesai ? '-' : esc(uk(hari))}</b><div class="sub">${selesai ? 'HPHT > 44 minggu — mungkin sudah bersalin' : tri}</div></div>
            <div><small>G / P / A</small><b>${r.gravida ? `G${esc(r.gravida)} P${esc(r.para || 0)} A${esc(r.abortus || 0)}` : '-'}</b></div>
            <div><small>Kunjungan ANC</small><b>${kunj}×</b><div class="sub">sejak HPHT (standar min. 6×)</div></div>
          </div>
          ${selesai ? '' : `<div class="bar" title="${Math.round(pct)}% dari 40 minggu"><span style="width:${pct}%"></span></div>`}
        </div>`;
      }

      /* ================= FORM ================= */
      const modal = bootstrap.Modal.getOrCreateInstance($id('ancModal'));
      const form = $id('ancForm');
      let hplOtomatis = true, ukOtomatis = true;

      function bersihkanError() {
        form.querySelectorAll('.is-invalid').forEach(e => e.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(e => e.textContent = '');
        $id('gpaErr').classList.add('d-none');
      }
      function tandai(field, pesan) {
        if (field === 'gravida' || field === 'para' || field === 'abortus') {
          $id(field).classList.add('is-invalid');
          $id('gpaErr').textContent = pesan;
          $id('gpaErr').classList.remove('d-none');
          return;
        }
        const el = $id(field);
        if (!el) return false;
        el.classList.add('is-invalid');
        const fb = el.closest('[class*="col-"]').querySelector('.invalid-feedback');
        if (fb) fb.textContent = pesan;
        el.focus();
        return true;
      }

      function hitungOtomatis() {
        const hpht = $id('hpht').value, tk = $id('tanggal_kontrol').value;
        $id('hplInfo').textContent = $id('ukInfo').textContent = '';
        if (!hpht) return;
        const hpl = tambahHari(hpht, 280);
        if (hplOtomatis || !$id('taksiran_persalinan').value) { $id('taksiran_persalinan').value = hpl; hplOtomatis = true; }
        $id('hplInfo').innerHTML = hplOtomatis ? 'Otomatis: HPHT + 280 hari' : ($id('taksiran_persalinan').value !== hpl ? 'Perhitungan HPHT: ' + esc(tglId(hpl)) + ' · <a href="javascript:;" id="pakaiHpl">Gunakan</a>' : '');
        if (tk && tk >= hpht) {
          const hari = selisihHari(hpht, tk);
          if (hari <= 45 * 7) {
            if (ukOtomatis || !$id('usia_gestasi').value) { $id('usia_gestasi').value = uk(hari); ukOtomatis = true; }
            $id('ukInfo').innerHTML = ukOtomatis ? 'Otomatis dari HPHT &amp; tanggal kontrol' : ($id('usia_gestasi').value !== uk(hari) ? 'Perhitungan HPHT: ' + esc(uk(hari)) + ' · <a href="javascript:;" id="pakaiUk">Gunakan</a>' : '');
          }
        }
      }
      form.addEventListener('click', e => {
        if (e.target.id === 'pakaiHpl') { hplOtomatis = true; $id('taksiran_persalinan').value = ''; hitungOtomatis(); }
        if (e.target.id === 'pakaiUk') { ukOtomatis = true; $id('usia_gestasi').value = ''; hitungOtomatis(); }
      });
      $id('hpht').addEventListener('change', hitungOtomatis);
      $id('tanggal_kontrol').addEventListener('change', hitungOtomatis);
      $id('taksiran_persalinan').addEventListener('input', () => { hplOtomatis = false; hitungOtomatis(); });
      $id('usia_gestasi').addEventListener('input', () => { ukOtomatis = false; hitungOtomatis(); });

      function cekNilai() {
        $id('tdInfo').textContent = tdTinggi($id('tekanan_darah').value) ? 'TD ≥ 140/90 — waspada hipertensi/preeklamsia' : '';
        $id('djjInfo').textContent = djjAbn($id('djj').value) ? 'Di luar 120–160 x/menit' : '';
      }
      ['tekanan_darah', 'djj'].forEach(i => $id(i).addEventListener('input', cekNilai));
      // "12080" -> "120/80" saat keluar dari kolom
      $id('tekanan_darah').addEventListener('blur', function() {
        const v = this.value.replace(/\s/g, '');
        if (/^\d{5,6}$/.test(v)) this.value = v.slice(0, 3) + '/' + v.slice(3);
        cekNilai();
      });

      $id('gambar').addEventListener('change', function() {
        const f = this.files[0];
        $id('gambarPreview').classList.toggle('d-none', !f);
        if (f) $id('gambarPreview').src = URL.createObjectURL(f);
        if (f && f.size > 5 * 1024 * 1024) tandai('gambar', 'Ukuran gambar maksimal 5 MB.');
      });

      function bukaForm(r) {
        form.reset();
        bersihkanError();
        $id('gambarPreview').classList.add('d-none');
        $id('gambarLama').classList.add('d-none');
        $id('tanggal_kontrol').max = hariIni();
        $id('hpht').max = hariIni();
        const isi = (o) => ['tanggal_kontrol', 'hpht', 'taksiran_persalinan', 'usia_gestasi', 'gravida', 'para', 'abortus', 'tekanan_darah', 'berat_badan',
          'tinggi_fundus', 'djj', 'presentasi', 'protein_urine', 'edema', 'catatan'].forEach(k => { if (o[k] !== undefined && o[k] !== null) $id(k).value = o[k]; });
        if (r) {
          $id('ancJudul').textContent = 'Ubah catatan ANC';
          $id('id_anc').value = r.id_anc;
          isi(r);
          hplOtomatis = !r.taksiran_persalinan;
          ukOtomatis = !r.usia_gestasi;
          if (r.gambar) {
            $id('gambarLamaImg').src = UPL + encodeURIComponent(r.gambar);
            $id('gambarLama').classList.remove('d-none');
          }
        } else {
          $id('ancJudul').textContent = 'Tambah catatan ANC';
          $id('id_anc').value = '';
          // bawa data kehamilan dari catatan terakhir (HPHT, HPL, G/P/A) agar tidak diketik ulang
          const akhir = DATA.find(x => x.hpht && selisihHari(x.hpht, hariIni()) <= 44 * 7);
          const hplWajar = akhir && akhir.taksiran_persalinan && Math.abs(selisihHari(tambahHari(akhir.hpht, 280), akhir.taksiran_persalinan)) <= 14 ? akhir.taksiran_persalinan : null;
          if (akhir) isi({ hpht: akhir.hpht, taksiran_persalinan: hplWajar, gravida: akhir.gravida, para: akhir.para, abortus: akhir.abortus });
          $id('tanggal_kontrol').value = hariIni();
          hplOtomatis = !(hplWajar && hplWajar !== tambahHari(akhir.hpht, 280));
          ukOtomatis = true;
        }
        hitungOtomatis();
        cekNilai();
        modal.show();
      }
      $id('btnTambah').addEventListener('click', () => bukaForm(null));

      let menyimpan = false;
      form.addEventListener('submit', function(e) {
        e.preventDefault();
        if (menyimpan) return;
        bersihkanError();
        if (!$id('tanggal_kontrol').value) return tandai('tanggal_kontrol', 'Tanggal kontrol wajib diisi.');
        const td = $id('tekanan_darah').value.trim();
        if (td && !/^\d{2,3}\s*\/\s*\d{2,3}$/.test(td)) return tandai('tekanan_darah', 'Tulis sistole/diastole, mis. 120/80.');
        menyimpan = true;
        const btn = $id('btnSimpan'), label = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan…';
        const fd = new FormData(form);
        if (!$id('gambar').files.length) fd.delete('gambar');
        fetch(API, { method: 'POST', body: fd })
          .then(r => r.json().catch(() => ({ status: 'error', message: 'Respon server tidak valid.' })))
          .then(res => {
            if (res.status === 'success') {
              modal.hide();
              Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: res.message, showConfirmButton: false, timer: 2200 });
              muat();
            } else if (res.field && tandai(res.field, res.message) !== false) {
              // pesan ditampilkan di bawah kolom
            } else {
              Swal.fire('Gagal', res.message || 'Gagal menyimpan.', 'error');
            }
          })
          .catch(() => Swal.fire('Gagal', 'Tidak dapat terhubung ke server.', 'error'))
          .finally(() => { menyimpan = false; btn.disabled = false; btn.innerHTML = label; });
      });

      /* ================= AKSI TABEL ================= */
      $id('ancBody').addEventListener('click', function(e) {
        const ubah = e.target.closest('[data-ubah]'), hapus = e.target.closest('[data-hapus]'), img = e.target.closest('[data-gambar]');
        if (img) {
          $id('gambarFull').src = UPL + encodeURIComponent(img.dataset.gambar);
          bootstrap.Modal.getOrCreateInstance($id('gambarModal')).show();
        }
        if (ubah) bukaForm(DATA.find(r => String(r.id_anc) === ubah.dataset.ubah));
        if (hapus) {
          const r = DATA.find(x => String(x.id_anc) === hapus.dataset.hapus);
          Swal.fire({
            icon: 'warning', title: 'Hapus catatan ANC?',
            html: `Catatan tanggal <b>${esc(tglId(r && r.tanggal_kontrol))}</b>${r && r.gambar ? ' beserta gambarnya' : ''} akan dihapus.`,
            showCancelButton: true, confirmButtonText: 'Hapus', cancelButtonText: 'Batal',
            confirmButtonColor: '#dc2626', cancelButtonColor: '#475569', reverseButtons: true
          }).then(x => {
            if (!x.isConfirmed) return;
            fetch(API + '&id=' + encodeURIComponent(hapus.dataset.hapus), { method: 'DELETE' })
              .then(r => r.json().catch(() => ({ status: 'error', message: 'Respon server tidak valid.' })))
              .then(res => {
                if (res.status === 'success') {
                  Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: res.message, showConfirmButton: false, timer: 2000 });
                  muat();
                } else Swal.fire('Gagal', res.message || 'Gagal menghapus.', 'error');
              })
              .catch(() => Swal.fire('Gagal', 'Tidak dapat terhubung ke server.', 'error'));
          });
        }
      });

      muat();
    })();
  </script>
</body>

</html>
