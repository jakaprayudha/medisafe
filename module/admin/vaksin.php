<?php
/**
 * Vaksinasi pasien + kirim ke SATUSEHAT (Immunization).
 * Klik Simpan -> data disimpan di database lalu langsung dikirim. Bila gagal, data tetap tersimpan dengan status
 * "Gagal" + alasannya, dan bisa dikirim ulang (per baris atau "Kirim ulang semua").
 * Perbaikan: $_GET['rme'] tanpa default, id kunjungan dicetak mentah (XSS), isi tabel tanpa escape, URL simpan
 * ganda "?", modal di luar </body>, gagal hapus tanpa pesan, kolom Aksi di paling kanan, form tidak memuat data
 * yang dibutuhkan SATUSEHAT (kode vaksin KFA, batch, rute, jenis imunisasi).
 */
$title = 'Vaksin';
require '../../controller/view.php';
require_once '../../controller/satusehat/immunizationService.php';
$no = trim((string) ($_GET['no'] ?? ''));
$rm = trim((string) ($_GET['rm'] ?? ''));
$rme = (string) ($_GET['rme'] ?? '');
$h = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
$katalog = imVaksinKatalog();
$grup = [];
foreach ($katalog as $kode => [$nama, $g]) $grup[$g][$kode] = $nama;
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .vk .kepala { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-bottom: 14px; }
    .vk .kepala .ic { width: 42px; height: 42px; border-radius: 12px; background: #dcfce7; color: #166534; display: inline-flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
    .vk .kepala h5 { margin: 0; font-weight: 700; color: #0f172a; }
    .vk .kepala small { color: #475569; }
    .vk .siap { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; border: 1px solid #e2e8f0; border-radius: 12px; padding: 8px 12px; margin-bottom: 14px; font-size: .8rem; background: #f8fafc; }
    .vk .siap .it { display: inline-flex; align-items: center; gap: 4px; font-weight: 600; }
    .vk .siap .ok { color: #166534; }
    .vk .siap .no { color: #b45309; }
    .vk table.t { width: 100%; border-collapse: collapse; font-size: .86rem; }
    .vk table.t th { font-size: .7rem; text-transform: uppercase; color: #475569; font-weight: 700; background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 7px 8px; white-space: nowrap; }
    .vk table.t td { border-bottom: 1px solid #eef2f6; padding: 8px; vertical-align: top; color: #1f2937; }
    .vk .aksi { white-space: nowrap; }
    .vk .aksi .btn { padding: 3px 9px; font-weight: 600; }
    .vk .nama { font-weight: 700; color: #0f172a; }
    .vk .kfa { font-size: .72rem; color: #64748b; }
    .vk .ss { display: inline-flex; align-items: center; gap: 4px; font-size: .72rem; font-weight: 700; border-radius: 6px; padding: 2px 8px; white-space: nowrap; }
    .vk .ss-terkirim { background: #dcfce7; color: #14532d; }
    .vk .ss-gagal { background: #fee2e2; color: #991b1b; }
    .vk .ss-belum { background: #fef3c7; color: #92400e; }
    .vk .ss-dibatalkan { background: #e2e8f0; color: #334155; }
    .vk .ss-err { font-size: .74rem; color: #991b1b; margin-top: 3px; max-width: 360px; white-space: normal; }
    .vk .ss-meta { font-size: .7rem; color: #64748b; margin-top: 2px; }
    .vk .kosong { text-align: center; padding: 30px 10px; color: #475569; }
    .vk .kosong i { font-size: 2.2rem; color: #94a3b8; display: block; margin-bottom: 6px; }
    #programModal .form-label { font-weight: 600; margin-bottom: 3px; font-size: .84rem; }
    #programModal .grp-t { font-size: .72rem; font-weight: 700; text-transform: uppercase; color: #475569; letter-spacing: .03em; margin: 6px 0 8px; border-bottom: 1px dashed #e2e8f0; padding-bottom: 4px; }
  </style>
</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <?php require 'sidebar.php'; ?>
    <div class="body-wrapper">
      <?php require 'navbar.php'; ?>
      <div class="body-wrapper-inner">
        <div class="container-fluid vk">
          <?php
          if ($rme === 'a') include 'menu_rme.php';
          elseif ($rme === 'b') include 'menu_rmeb.php';
          elseif ($rme === 'c') include 'menu_rme_inap.php';
          ?>
          <?php require 'card-pasien.php'; ?>

          <div class="card w-100">
            <div class="card-body p-3 p-md-4">
              <div class="kepala">
                <span class="ic"><i class="ti ti-vaccine"></i></span>
                <div class="flex-grow-1">
                  <h5>Vaksinasi</h5>
                  <small>Setiap data yang disimpan langsung dikirim ke SATUSEHAT (Immunization). Bila gagal, data tetap tersimpan dan bisa dikirim ulang.</small>
                </div>
                <div class="d-flex flex-wrap gap-2">
                  <button type="button" class="btn btn-warning d-none" id="btnKirimSemua"><i class="ti ti-send me-1"></i>Kirim ulang semua <span class="badge bg-dark ms-1" id="nPending">0</span></button>
                  <button type="button" class="btn btn-primary" id="btnTambah"><i class="ti ti-plus me-1"></i>Tambah Vaksin</button>
                </div>
              </div>

              <div class="siap" id="kesiapan">
                <b class="me-1">Kesiapan SATUSEHAT:</b>
                <span class="it" data-k="setting"><i class="ti ti-loader"></i>Setting faskes</span>
                <span class="it" data-k="pasien_ihs"><i class="ti ti-loader"></i>ID pasien (IHS)</span>
                <span class="it" data-k="encounter"><i class="ti ti-loader"></i>Encounter kunjungan</span>
                <span class="text-muted" id="kesiapanInfo"></span>
              </div>

              <div class="table-responsive">
                <table class="t">
                  <thead>
                    <tr>
                      <th style="width:1%">Aksi</th>
                      <th>Vaksin</th>
                      <th>Pemberian</th>
                      <th>Batch</th>
                      <th>SATUSEHAT</th>
                    </tr>
                  </thead>
                  <tbody id="tbVaksin"><tr><td colspan="5" class="kosong"><span class="spinner-border spinner-border-sm me-2"></span>Memuat...</td></tr></tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="programModal" tabindex="-1" aria-labelledby="programModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <form id="programForm" class="modal-content" novalidate>
        <div class="modal-header">
          <h5 class="modal-title" id="programModalTitle">Tambah Vaksin</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_vaksin" id="id_vaksin">
          <input type="hidden" name="id_visit" value="<?= $h($no) ?>">
          <input type="hidden" name="vaksin_name" id="vaksin_name">

          <div class="grp-t">Vaksin</div>
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label" for="kfa_code">Jenis vaksin (KFA) <span class="text-danger">*</span></label>
              <select class="form-select" id="kfa_code" name="kfa_code" style="width:100%">
                <option value=""></option>
                <?php foreach ($grup as $g => $items): ?>
                  <optgroup label="<?= $h($g) ?>">
                    <?php foreach ($items as $kode => $nama): ?>
                      <option value="<?= $h($kode) ?>" data-grup="<?= $h($g) ?>"><?= $h($nama) ?> — <?= $h($kode) ?></option>
                    <?php endforeach ?>
                  </optgroup>
                <?php endforeach ?>
              </select>
              <div class="form-text" id="infoLama"></div>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="reason_code">Jenis imunisasi <span class="text-danger">*</span></label>
              <select class="form-select" id="reason_code" name="reason_code">
                <option value="">— Pilih —</option>
                <?php foreach (imAlasan() as $k => $v): ?><option value="<?= $h($k) ?>"><?= $h($v) ?></option><?php endforeach ?>
              </select>
            </div>
            <div class="col-6 col-md-3">
              <label class="form-label" for="vaksin_dosis">Dosis ke- <span class="text-danger">*</span></label>
              <input type="number" class="form-control" id="vaksin_dosis" name="vaksin_dosis" min="1" max="10" step="1" placeholder="1">
            </div>
            <div class="col-6 col-md-3">
              <label class="form-label" for="dose_ml">Jumlah (mL)</label>
              <input type="number" class="form-control" id="dose_ml" name="dose_ml" min="0.01" max="10" step="0.01" placeholder="0.5">
            </div>
          </div>

          <div class="grp-t mt-3">Pemberian</div>
          <div class="row g-3">
            <div class="col-6 col-md-3">
              <label class="form-label" for="vaksin_date">Tanggal <span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="vaksin_date" name="vaksin_date" max="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-6 col-md-3">
              <label class="form-label" for="vaksin_time">Jam</label>
              <input type="time" class="form-control" id="vaksin_time" name="vaksin_time">
            </div>
            <div class="col-md-6">
              <label class="form-label" for="route_code">Rute pemberian <span class="text-danger">*</span></label>
              <select class="form-select" id="route_code" name="route_code">
                <option value="">— Pilih —</option>
                <?php foreach (imRute() as $k => $v): ?><option value="<?= $h($k) ?>"><?= $h($v) ?><?= $k === 'p' ? ' (intradermal, mis. BCG)' : '' ?></option><?php endforeach ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="lot_number">Nomor batch (lot) <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="lot_number" name="lot_number" maxlength="50" placeholder="Tertera di kemasan vaksin">
            </div>
            <div class="col-md-6">
              <label class="form-label" for="expiration_date">Tanggal kedaluwarsa</label>
              <input type="date" class="form-control" id="expiration_date" name="expiration_date">
            </div>
            <div class="col-12">
              <label class="form-label" for="vaksin_notes">Catatan</label>
              <textarea class="form-control" id="vaksin_notes" name="vaksin_notes" rows="2" maxlength="2000" placeholder="Opsional, mis. lokasi suntik paha kiri"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <span class="small text-muted me-auto"><i class="ti ti-info-circle me-1"></i>Simpan = simpan ke database lalu kirim ke SATUSEHAT.</span>
          <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary" id="btnSimpan"><i class="ti ti-device-floppy me-1"></i>Simpan &amp; Kirim</button>
        </div>
      </form>
    </div>
  </div>

  <?php require 'library.php'; ?>
  <script>
    (function() {
      const NO = <?= json_encode($no) ?>;
      const apiUrl = 'controller/visit/vaksin?no=' + encodeURIComponent(NO);
      const KATALOG = <?= json_encode($katalog, JSON_UNESCAPED_UNICODE) ?>;
      const ALASAN = <?= json_encode(imAlasan(), JSON_UNESCAPED_UNICODE) ?>;
      const RUTE = <?= json_encode(imRute(), JSON_UNESCAPED_UNICODE) ?>;
      // saran rute & volume per kelompok vaksin (bisa diubah petugas)
      const SARAN = {
        'BCG': ['p', 0.05], 'Polio Oral (OPV)': ['O', 0.1], 'Rotavirus': ['O', null], 'Campak Rubella (MR)': ['inj.subcutaneous', 0.5],
        'Japanese Encephalitis': ['inj.subcutaneous', 0.5], 'Varisela': ['inj.subcutaneous', 0.5], 'Yellow Fever': ['inj.subcutaneous', 0.5]
      };
      const esc = (v) => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const tgl = (s) => { const d = s ? new Date(String(s).slice(0, 10) + 'T00:00:00') : null; return d && !isNaN(d) ? d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-'; };
      const waktu = (s) => { const d = s ? new Date(String(s).replace(' ', 'T')) : null; return d && !isNaN(d) ? d.toLocaleString('id-ID', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' }) : ''; };
      const hariIni = () => { const d = new Date(); d.setMinutes(d.getMinutes() - d.getTimezoneOffset()); return d.toISOString().slice(0, 10); };
      const jamKini = () => new Date().toTimeString().slice(0, 5);
      let data = [];

      function muat() {
        return fetch(apiUrl, { cache: 'no-store' })
          .then(r => r.json())
          .then(res => {
            if (res.status !== 'success') throw new Error(res.message);
            data = res.data || [];
            if (res.migrasi === false) {
              $('#kesiapan').html('<span class="text-danger fw-semibold"><i class="ti ti-database-exclamation me-1"></i>Database vaksin belum diperbarui untuk SATUSEHAT (migrasi belum dijalankan). Data lama tetap tampil; tambah/ubah/kirim belum bisa dipakai.</span>');
              $('#btnTambah').prop('disabled', true);
            } else kesiapan(res.kesiapan || {});
            render();
          })
          .catch(err => $('#tbVaksin').html(`<tr><td colspan="5" class="kosong text-danger"><i class="ti ti-alert-triangle"></i>${esc(err.message || 'Gagal memuat data.')}</td></tr>`));
      }

      function kesiapan(k) {
        let kurang = [];
        Object.entries({ setting: 'Setting faskes', pasien_ihs: 'ID pasien (IHS)', encounter: 'Encounter kunjungan' }).forEach(([key, label]) => {
          const ok = !!k[key];
          if (!ok) kurang.push(key);
          $(`#kesiapan [data-k="${key}"]`).attr('class', 'it ' + (ok ? 'ok' : 'no')).html(`<i class="ti ti-${ok ? 'circle-check' : 'alert-circle'}"></i>${label}`);
        });
        $('#kesiapanInfo').text(!kurang.length ? 'Siap dikirim.' :
          kurang.includes('encounter') && kurang.length === 1 ? 'Encounter dibuat otomatis setelah diagnosa diisi — data tetap disimpan, kirim ulang nanti.' :
          'Data tetap disimpan; lengkapi yang kurang lalu kirim ulang.');
      }

      function statusSS(r) {
        const st = r.ss_status || 'belum';
        const label = { terkirim: 'Terkirim', gagal: 'Gagal kirim', belum: 'Belum dikirim', dibatalkan: 'Dibatalkan' }[st] || st;
        const ikon = { terkirim: 'circle-check', gagal: 'alert-triangle', belum: 'clock', dibatalkan: 'ban' }[st] || 'clock';
        let html = `<span class="ss ss-${esc(st)}"><i class="ti ti-${ikon}"></i>${label}</span>`;
        if (st === 'terkirim' && r.ss_id) html += `<div class="ss-meta">ID ${esc(r.ss_id)}${r.ss_sent_at ? ' · ' + esc(waktu(r.ss_sent_at)) : ''}</div>`;
        if (st === 'gagal' && r.ss_error) html += `<div class="ss-err">${esc(r.ss_error)}</div>`;
        if (st !== 'terkirim' && +r.ss_attempts > 0) html += `<div class="ss-meta">${+r.ss_attempts}× percobaan${r.ss_last_try ? ', terakhir ' + esc(waktu(r.ss_last_try)) : ''}</div>`;
        return html;
      }

      function render() {
        const pending = data.filter(r => ['belum', 'gagal'].includes(r.ss_status || 'belum'));
        $('#nPending').text(pending.length);
        $('#btnKirimSemua').toggleClass('d-none', pending.length < 2);
        if (!data.length) {
          $('#tbVaksin').html('<tr><td colspan="5" class="kosong"><i class="ti ti-vaccine-off"></i>Belum ada data vaksinasi pada kunjungan ini.</td></tr>');
          return;
        }
        $('#tbVaksin').html(data.map(r => {
          const st = r.ss_status || 'belum';
          const kat = KATALOG[r.kfa_code];
          const aksi = [];
          if (st !== 'dibatalkan') aksi.push(`<button type="button" class="btn btn-sm btn-warning edit-btn" data-id="${+r.id_vaksin}" title="Ubah"><i class="ti ti-edit"></i></button>`);
          if (['belum', 'gagal'].includes(st)) aksi.push(`<button type="button" class="btn btn-sm btn-success kirim-btn" data-id="${+r.id_vaksin}"><i class="ti ti-send me-1"></i>Kirim ulang</button>`);
          aksi.push(`<button type="button" class="btn btn-sm btn-danger delete-btn" data-id="${+r.id_vaksin}" title="Hapus"><i class="ti ti-trash"></i></button>`);
          return `<tr>
            <td class="aksi">${aksi.join(' ')}</td>
            <td><div class="nama">${esc(r.vaksin_name || (kat ? kat[1] : '-'))} · dosis ${esc(r.vaksin_dosis || '-')}</div>
              <div class="kfa">${kat ? esc(kat[0]) + ' (KFA ' + esc(r.kfa_code) + ')' : '<span class="text-danger">Kode KFA belum dipilih — ubah data untuk melengkapi</span>'}</div>
              ${r.reason_code ? `<div class="kfa">${esc(ALASAN[r.reason_code] || r.reason_code)}</div>` : ''}</td>
            <td class="text-nowrap">${esc(tgl(r.vaksin_date))}${r.vaksin_time ? ' ' + esc(String(r.vaksin_time).slice(0, 5)) : ''}
              <div class="kfa">${esc(RUTE[r.route_code] || '-')}${+r.dose_ml ? ' · ' + esc(+r.dose_ml) + ' mL' : ''}</div></td>
            <td>${esc(r.lot_number || '-')}${r.expiration_date ? `<div class="kfa">ED ${esc(tgl(r.expiration_date))}</div>` : ''}</td>
            <td>${statusSS(r)}</td>
          </tr>`;
        }).join(''));
      }

      // ---------- form ----------
      const $kfa = $('#kfa_code').select2({ dropdownParent: $('#programModal'), width: '100%', placeholder: 'Cari vaksin (nama / kode KFA)...' });
      $kfa.on('change', function() {
        const g = $(this).find(':selected').data('grup') || '';
        $('#vaksin_name').val(g);
        $(this).next('.select2').find('.select2-selection').removeClass('border-danger');
        // saran rute & volume mengikuti jenis vaksin, kecuali sudah diubah manual oleh petugas
        if (!g) return;
        const s = SARAN[g] || ['inj.intramuscular', 0.5];
        const $r = $('#route_code'), $m = $('#dose_ml');
        if (!$r.val() || $r.data('auto')) $r.val(s[0]).data('auto', true);
        if (!$m.val() || $m.data('auto')) $m.val(s[1] || '').data('auto', true);
      });
      $('#route_code, #dose_ml').on('input change', function(e) { if (e.originalEvent) $(this).data('auto', false); });
      $('#programForm').on('input change', 'input, select, textarea', function() { $(this).removeClass('is-invalid'); });

      function bukaForm(d) {
        const f = $('#programForm')[0];
        f.reset();
        $(f).find('.is-invalid').removeClass('is-invalid');
        $('#id_vaksin').val(d ? d.id_vaksin : '');
        $('#route_code, #dose_ml').val('').data('auto', false);
        $kfa.val(d && d.kfa_code ? d.kfa_code : null).trigger('change');
        if (d) {
          ['vaksin_name', 'reason_code', 'lot_number', 'vaksin_notes'].forEach(k => $('#' + k).val(d[k] || ''));
          if (d.route_code) $('#route_code').val(d.route_code);   // data lama tanpa rute: pakai saran dari jenis vaksin
          $('#vaksin_dosis').val(/^\d+$/.test(String(d.vaksin_dosis || '')) ? d.vaksin_dosis : '');
          if (+d.dose_ml) $('#dose_ml').val(+d.dose_ml);
          $('#vaksin_date').val(String(d.vaksin_date || '').slice(0, 10));
          $('#vaksin_time').val(d.vaksin_time ? String(d.vaksin_time).slice(0, 5) : '');
          $('#expiration_date').val(d.expiration_date || '');
          $('#infoLama').html(!d.kfa_code ? `Data lama: <b>${esc(d.vaksin_name || '-')}</b>${d.vaksin_dosis && !/^\d+$/.test(d.vaksin_dosis) ? ', dosis "' + esc(d.vaksin_dosis) + '"' : ''} — pilih vaksin yang sesuai agar bisa dikirim.` : '');
        } else {
          $('#vaksin_date').val(hariIni());
          $('#vaksin_time').val(jamKini());
          $('#infoLama').text('');
        }
        $('#programModalTitle').text(d ? `Ubah Vaksin ${d.vaksin_number || ''}` : 'Tambah Vaksin');
        $('#btnSimpan').html(`<i class="ti ti-device-floppy me-1"></i>${d && d.ss_status === 'terkirim' ? 'Simpan &amp; Perbarui SATUSEHAT' : 'Simpan &amp; Kirim'}`);
        $('#programModal').modal('show');
      }
      $('#btnTambah').on('click', () => bukaForm(null));
      $(document).on('click', '.edit-btn', function() {
        const d = data.find(r => +r.id_vaksin === +$(this).data('id'));
        if (d) bukaForm(d);
      });

      function tandai(field) {
        if (field === 'kfa_code') $kfa.next('.select2').find('.select2-selection').addClass('border-danger');
        else $('#' + field).addClass('is-invalid').trigger('focus');
      }

      $('#programForm').on('submit', function(e) {
        e.preventDefault();
        const wajib = [['kfa_code', 'Pilih jenis vaksin.'], ['reason_code', 'Pilih jenis imunisasi.'], ['vaksin_dosis', 'Isi dosis ke-.'], ['vaksin_date', 'Isi tanggal vaksinasi.'], ['route_code', 'Pilih rute pemberian.'], ['lot_number', 'Isi nomor batch vaksin.']];
        for (const [f, pesan] of wajib) {
          if (!String($('#' + f).val() || '').trim()) { tandai(f); Swal.fire('Lengkapi data', pesan, 'warning'); return; }
        }
        if ($('#vaksin_date').val() > hariIni()) { tandai('vaksin_date'); Swal.fire('Periksa tanggal', 'Tanggal vaksinasi tidak boleh di masa depan.', 'warning'); return; }
        const ed = $('#expiration_date').val();
        if (ed && ed < $('#vaksin_date').val()) { tandai('expiration_date'); Swal.fire('Vaksin kedaluwarsa', 'Tanggal kedaluwarsa sebelum tanggal pemberian.', 'warning'); return; }

        const id = $('#id_vaksin').val();
        const $b = $('#btnSimpan').prop('disabled', true);
        Swal.fire({ title: 'Menyimpan & mengirim ke SATUSEHAT...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        fetch(apiUrl + (id ? '&id=' + encodeURIComponent(id) : ''), {
            method: id ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams(new FormData(this))
          })
          .then(r => r.json())
          .then(res => {
            if (res.status !== 'success') {
              if (res.field) tandai(res.field);
              Swal.fire('Gagal', res.message || 'Gagal menyimpan.', 'error');
              return;
            }
            $('#programModal').modal('hide');
            muat();
            const ss = res.satusehat || {};
            Swal.fire(ss.ok ?
              { icon: 'success', title: 'Tersimpan & terkirim', text: 'Data vaksin tersimpan dan sudah terkirim ke SATUSEHAT.', timer: 1800, showConfirmButton: false } :
              { icon: 'warning', title: 'Tersimpan, belum terkirim', html: `Data vaksin <b>sudah tersimpan</b>, tetapi gagal dikirim ke SATUSEHAT:<div class="alert alert-danger py-2 mt-2 mb-0 small text-start">${esc(ss.pesan || '-')}</div><div class="small mt-2">Gunakan tombol <b>Kirim ulang</b> setelah masalah diperbaiki.</div>` });
          })
          .catch(() => Swal.fire('Gagal', 'Terjadi kesalahan pada server.', 'error'))
          .finally(() => $b.prop('disabled', false));
      });

      // ---------- kirim ulang ----------
      function kirimUlang(target) {
        Swal.fire({ title: 'Mengirim ke SATUSEHAT...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        return fetch(apiUrl + '&kirim=' + encodeURIComponent(target), { method: 'POST' })
          .then(r => r.json())
          .then(res => {
            muat();
            const ok = res.status === 'success';
            Swal.fire({ icon: ok ? 'success' : (res.status === 'partial' ? 'warning' : 'error'), title: ok ? 'Terkirim' : 'Belum semua terkirim', text: res.message || '' });
          })
          .catch(() => Swal.fire('Gagal', 'Terjadi kesalahan pada server.', 'error'));
      }
      $(document).on('click', '.kirim-btn', function() { kirimUlang(String($(this).data('id'))); });
      $('#btnKirimSemua').on('click', () => kirimUlang('semua'));

      $(document).on('click', '.delete-btn', function() {
        const id = $(this).data('id');
        const d = data.find(r => +r.id_vaksin === +id) || {};
        const terkirim = !!d.ss_id && d.ss_status !== 'dibatalkan';
        Swal.fire({
          title: 'Hapus data vaksin?',
          html: `<b>${esc(d.vaksin_name || '')}</b> · dosis ${esc(d.vaksin_dosis || '')} — ${esc(tgl(d.vaksin_date))}` +
            (terkirim ? '<div class="alert alert-warning py-2 mt-2 mb-0 small">Data ini sudah ada di SATUSEHAT dan akan <b>dibatalkan</b> di sana (entered-in-error) sebelum dihapus.</div>' : ''),
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Hapus',
          confirmButtonColor: '#dc2626',
          cancelButtonText: 'Batal',
          reverseButtons: true
        }).then(x => {
          if (!x.isConfirmed) return;
          fetch(apiUrl + '&id=' + encodeURIComponent(id), { method: 'DELETE' })
            .then(r => r.json())
            .then(res => {
              if (res.status === 'success') Swal.fire({ icon: 'success', title: 'Dihapus', text: res.message, timer: 1600, showConfirmButton: false });
              else Swal.fire('Gagal', res.message || 'Gagal menghapus.', 'error');
              muat();
            })
            .catch(() => Swal.fire('Gagal', 'Terjadi kesalahan pada server.', 'error'));
        });
      });

      muat();
    })();
  </script>
</body>

</html>
