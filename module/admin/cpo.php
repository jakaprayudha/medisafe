<?php
/**
 * CPO — Catatan Pemberian Obat pasien rawat inap.
 * Riwayat dikelompokkan per tanggal (ubah/hapus per baris) + form tambah banyak baris sekaligus.
 * Perbaikan: query kunjungan dulu dirangkai langsung dari URL (SQL injection) tanpa batas faskes, nilai URL dicetak
 * mentah (XSS), data riwayat disisipkan ke HTML tanpa escape, daftar petugas diminta ulang per baris (N request),
 * gagal ubah tidak memberi pesan, judul halaman salah ("Pemeriksaan Rawat Inap").
 */
$title = 'CPO — Catatan Pemberian Obat';
require '../../controller/view.php';

$no = trim((string) ($_GET['no'] ?? ''));
$rm = trim((string) ($_GET['rm'] ?? ''));
$idc = (int) ($_SESSION['id_customer'] ?? 0);
$uidUser = (string) ($_SESSION['uid_user'] ?? '');
$h = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');

$st = $koneksi->prepare("SELECT pv.visit_ID, pv.visit_status, pv.id_patient FROM pasien_visit pv
   INNER JOIN ms_patient mp ON mp.id_patient = pv.id_patient
   WHERE pv.visit_ID = ? AND mp.nomor_rm = ? AND pv.id_customer = ? LIMIT 1");
$st->bind_param('ssi', $no, $rm, $idc);
$st->execute();
$kv = $st->get_result()->fetch_assoc();
$st->close();

// petugas klinis faskes ini (1x di server, bukan 1 request per baris)
$st = $koneksi->prepare("SELECT id_user, uid_user, fullname, roles FROM ms_users
   WHERE id_customer = ? AND roles IN ('dokter','bidan','perawat','apoteker') ORDER BY fullname");
$st->bind_param('i', $idc);
$st->execute();
$petugas = $st->get_result()->fetch_all(MYSQLI_ASSOC);
$st->close();
$petugasSaya = '';
foreach ($petugas as $p) if ($uidUser !== '' && (string) $p['uid_user'] === $uidUser) $petugasSaya = (string) $p['id_user'];
?>
<!doctype html>
<html lang="id">

<head>
   <base href="../../">
   <?php require '../../assets/template/head.php'; ?>
   <style>
      .cpo .kepala { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-bottom: 14px; }
      .cpo .kepala .ic { width: 42px; height: 42px; border-radius: 12px; background: #dcfce7; color: #166534; display: inline-flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
      .cpo .kepala h5 { margin: 0; font-weight: 700; color: #0f172a; }
      .cpo .kepala small { color: #475569; }
      .cpo .kpi { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; margin-bottom: 16px; }
      .cpo .kpi div { border: 1px solid #e2e8f0; border-radius: 12px; padding: 10px 12px; background: #fff; }
      .cpo .kpi b { display: block; font-size: 1.35rem; color: #0f172a; line-height: 1.1; }
      .cpo .kpi span { font-size: .74rem; color: #475569; }
      .cpo .kpi .warn b { color: #b45309; }
      @media (max-width: 767.98px) { .cpo .kpi { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
      .cpo .sec { border: 1px solid #e2e8f0; border-radius: 14px; padding: 14px 16px; margin-bottom: 16px; background: #fff; }
      .cpo .sec-h { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin-bottom: 10px; }
      .cpo .sec-h b { font-size: 1rem; color: #0f172a; }
      .cpo table.t { width: 100%; border-collapse: separate; border-spacing: 0; font-size: .86rem; }
      .cpo table.t th { font-size: .7rem; text-transform: uppercase; letter-spacing: .02em; color: #475569; font-weight: 700; background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 7px 8px; white-space: nowrap; position: sticky; top: 0; z-index: 2; }
      .cpo table.t td { border-bottom: 1px solid #eef2f6; padding: 6px 8px; vertical-align: middle; color: #1f2937; }
      .cpo table.t tr.grp td { background: #f1f5f9; font-weight: 700; color: #0f172a; font-size: .82rem; border-bottom: 1px solid #e2e8f0; }
      .cpo table.t tr.grp .n { font-weight: 500; color: #475569; }
      .cpo table.t tr.editing td { background: #fffbeb; }
      .cpo table.t .form-control, .cpo table.t .form-select { font-size: .84rem; padding: 4px 8px; min-width: 0; }
      .cpo .jam { display: inline-block; min-width: 52px; text-align: center; font-weight: 700; font-size: .8rem; border-radius: 6px; padding: 2px 6px; background: #dcfce7; color: #14532d; }
      .cpo .jam.kosong { background: transparent; color: #94a3b8; font-weight: 400; }
      .cpo .rencana { font-size: .7rem; font-weight: 700; color: #92400e; background: #fef3c7; border-radius: 6px; padding: 1px 6px; margin-left: 4px; }
      .cpo .ptg { font-size: .8rem; }
      .cpo .ptg small { display: block; color: #64748b; font-size: .7rem; }
      .cpo .aksi { white-space: nowrap; }
      .cpo .aksi .btn { padding: 3px 8px; }
      .cpo .kosong-data { text-align: center; color: #475569; padding: 28px 10px; }
      .cpo .kosong-data i { font-size: 2rem; color: #94a3b8; display: block; margin-bottom: 6px; }
      .cpo #tabelBaru td { padding: 5px 4px; }
      .cpo .bar { position: sticky; bottom: 0; z-index: 20; background: rgba(255, 255, 255, .96); border-top: 1px solid #e2e8f0; padding: 10px 0 2px; margin-top: 10px; backdrop-filter: blur(6px); display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
   </style>
</head>

<body>
   <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
      data-sidebar-position="fixed" data-header-position="fixed">
      <?php require 'sidebar.php'; ?>
      <div class="body-wrapper">
         <?php require 'navbar.php'; ?>
         <div class="body-wrapper-inner">
            <div class="container-fluid cpo">
               <?php require 'menu_rme_inap.php'; ?>
               <?php if (!$kv): ?>
                  <div class="alert alert-danger mt-3">Kunjungan tidak ditemukan untuk faskes ini.</div>
               <?php else: ?>
               <?php require 'card-pasien.php'; ?>

               <div class="card w-100">
                  <div class="card-body p-3 p-md-4">
                     <div class="kepala">
                        <span class="ic"><i class="ti ti-clipboard-list"></i></span>
                        <div class="flex-grow-1">
                           <h5>Catatan Pemberian Obat (CPO)</h5>
                           <small>Catat setiap obat yang diberikan beserta jam pemberian dan petugasnya.</small>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                           <a class="btn btn-outline-dark" target="_blank" href="module/admin/print/formulir_cpo?no=<?= $h(urlencode($no)) ?>&rm=<?= $h(urlencode($rm)) ?>"><i class="ti ti-printer me-1"></i>Cetak</a>
                           <a class="btn btn-primary" href="javascript:;" id="keTambah"><i class="ti ti-plus me-1"></i>Tambah Pemberian</a>
                        </div>
                     </div>
                     <?php if ((int) $kv['visit_status'] === 99): ?>
                        <div class="alert alert-warning"><i class="ti ti-ban me-1"></i>Kunjungan ini sudah <b>dibatalkan</b>.</div>
                     <?php endif ?>

                     <div class="kpi">
                        <div><b id="kTotal">0</b><span>Catatan pemberian</span></div>
                        <div><b id="kObat">0</b><span>Jenis obat</span></div>
                        <div><b id="kHari">0</b><span>Hari tercatat</span></div>
                        <div class="warn"><b id="kRencana">0</b><span>Belum ada jam pemberian</span></div>
                     </div>

                     <!-- RIWAYAT -->
                     <div class="sec">
                        <div class="sec-h">
                           <b><i class="ti ti-history me-1"></i>Riwayat pemberian</b>
                           <div class="ms-md-auto d-flex flex-wrap gap-2" style="width:100%;max-width:440px">
                              <select class="form-select" id="fTanggal" style="max-width:190px"><option value="">Semua tanggal</option></select>
                              <input type="search" class="form-control" id="fCari" placeholder="Cari obat / petugas..." style="flex:1;min-width:150px">
                           </div>
                        </div>
                        <div class="table-responsive" style="max-height:62vh">
                           <table class="t" id="tabelRiwayat">
                              <thead>
                                 <tr>
                                    <th style="width:1%">Aksi</th>
                                    <th style="min-width:200px">Obat</th>
                                    <th>Dosis</th>
                                    <th>Signa</th>
                                    <th class="text-center">Pagi</th>
                                    <th class="text-center">Siang</th>
                                    <th class="text-center">Sore</th>
                                    <th class="text-center">Malam</th>
                                    <th style="min-width:160px">Petugas</th>
                                 </tr>
                              </thead>
                              <tbody><tr><td colspan="9" class="kosong-data"><span class="spinner-border spinner-border-sm me-2"></span>Memuat data...</td></tr></tbody>
                           </table>
                        </div>
                     </div>

                     <!-- TAMBAH -->
                     <form id="formCPO" class="sec mb-0" novalidate>
                        <input type="hidden" name="nomor_visit" value="<?= $h($no) ?>">
                        <div class="sec-h">
                           <b><i class="ti ti-pencil-plus me-1"></i>Tambah pemberian obat</b>
                           <small class="text-muted">Nama obat yang pernah dicatat akan mengisi dosis &amp; signa otomatis.</small>
                        </div>
                        <div class="table-responsive">
                           <table class="t" id="tabelBaru" style="min-width:1080px">
                              <thead>
                                 <tr>
                                    <th style="width:1%">Aksi</th>
                                    <th style="width:140px">Tanggal <span class="text-danger">*</span></th>
                                    <th style="min-width:200px">Nama obat <span class="text-danger">*</span></th>
                                    <th style="width:110px">Dosis</th>
                                    <th style="width:100px">Signa</th>
                                    <th style="width:100px">Pagi</th>
                                    <th style="width:100px">Siang</th>
                                    <th style="width:100px">Sore</th>
                                    <th style="width:100px">Malam</th>
                                    <th style="min-width:170px">Petugas</th>
                                 </tr>
                              </thead>
                              <tbody></tbody>
                           </table>
                        </div>
                        <datalist id="dlObat"></datalist>
                        <div class="bar">
                           <button type="button" class="btn btn-outline-dark" id="addRow"><i class="ti ti-plus me-1"></i>Baris</button>
                           <button type="button" class="btn btn-outline-dark" id="salinTerakhir" disabled><i class="ti ti-copy me-1"></i>Salin obat tanggal terakhir</button>
                           <span class="small text-muted ms-md-2" id="infoBaru"></span>
                           <button type="submit" class="btn btn-primary ms-auto" id="btnSimpan"><i class="ti ti-device-floppy me-1"></i>Simpan</button>
                        </div>
                     </form>
                  </div>
               </div>
               <?php endif ?>
            </div>
         </div>
      </div>
   </div>

   <template id="tplPetugas">
      <option value="">— Pilih petugas —</option>
      <?php foreach ($petugas as $p): ?>
         <option value="<?= $h($p['id_user']) ?>"><?= $h($p['fullname']) ?> (<?= $h($p['roles']) ?>)</option>
      <?php endforeach ?>
   </template>

   <?php require 'library.php'; ?>
<?php if ($kv): ?>
<script>
   (function() {
      const VISIT = <?= json_encode($no) ?>;
      const PETUGAS_SAYA = <?= json_encode($petugasSaya) ?>;
      const JAM = ['jam_pagi', 'jam_siang', 'jam_sore', 'jam_malam'];
      const esc = (v) => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const hariIni = () => { const d = new Date(); d.setMinutes(d.getMinutes() - d.getTimezoneOffset()); return d.toISOString().slice(0, 10); };
      const tglPanjang = (s) => { const d = new Date(s + 'T00:00:00'); return isNaN(d) ? (s || '-') : d.toLocaleDateString('id-ID', { weekday: 'long', day: '2-digit', month: 'short', year: 'numeric' }); };
      const opsiPetugas = (pilih) => {
         const $s = $('<select class="form-select"></select>').html($('#tplPetugas').html());
         if (pilih && !$s.find(`option[value="${CSS.escape(String(pilih))}"]`).length) $s.append(new Option('Petugas #' + pilih, pilih));
         $s.val(pilih || '');
         return $s;
      };

      let data = [];
      let berubah = false;

      // ---------- RIWAYAT ----------
      function muat() {
         return $.getJSON('controller/visit/getCpo.php', { visit_ID: VISIT })
            .done(res => {
               if (res.status !== 'success') return gagalMuat(res.message);
               data = res.data || [];
               ringkas();
               render();
            })
            .fail(xhr => gagalMuat(xhr.responseJSON?.message));
      }
      function gagalMuat(pesan) {
         $('#tabelRiwayat tbody').html(`<tr><td colspan="9" class="kosong-data text-danger"><i class="ti ti-alert-triangle"></i>${esc(pesan || 'Gagal memuat data CPO.')}</td></tr>`);
      }

      function ringkas() {
         const tgl = [...new Set(data.map(r => r.tanggal))].sort();
         $('#kTotal').text(data.length);
         $('#kObat').text(new Set(data.map(r => String(r.nama_item || '').trim().toLowerCase())).size);
         $('#kHari').text(tgl.length);
         $('#kRencana').text(data.filter(r => JAM.every(k => !r[k])).length);
         const pilih = $('#fTanggal').val();
         $('#fTanggal').html('<option value="">Semua tanggal</option>' + tgl.slice().reverse().map(t => `<option value="${esc(t)}">${esc(tglPanjang(t))}</option>`).join(''));
         if (tgl.includes(pilih)) $('#fTanggal').val(pilih);
         // datalist nama obat + dosis/signa terakhirnya
         const terakhir = {};
         data.forEach(r => { terakhir[String(r.nama_item || '').trim()] = r; });
         $('#dlObat').html(Object.keys(terakhir).filter(Boolean).sort().map(n => `<option value="${esc(n)}">`).join(''));
         window.__cpoTerakhir = terakhir;
         $('#salinTerakhir').prop('disabled', !tgl.length);
      }

      function selJam(v) {
         return v ? `<span class="jam">${esc(v)}</span>` : '<span class="jam kosong">—</span>';
      }

      function barisLihat(r) {
         const rencana = JAM.every(k => !r[k]);
         return `<tr data-id="${+r.id}">
            <td class="aksi">
               <button type="button" class="btn btn-sm btn-warning btn-edit" title="Ubah"><i class="ti ti-pencil"></i></button>
               <button type="button" class="btn btn-sm btn-danger btn-delete" title="Hapus"><i class="ti ti-trash"></i></button>
            </td>
            <td><b>${esc(r.nama_item)}</b>${rencana ? '<span class="rencana">belum ada jam</span>' : ''}</td>
            <td>${esc(r.dosis || '-')}</td>
            <td>${esc(r.signature || '-')}</td>
            ${JAM.map(k => `<td class="text-center">${selJam(r[k])}</td>`).join('')}
            <td class="ptg">${r.petugas_nama ? `${esc(r.petugas_nama)}<small>${esc(r.petugas_role || '')}</small>` : (r.petugas ? `Petugas #${esc(r.petugas)}` : '<span class="text-muted">—</span>')}</td>
         </tr>`;
      }

      function render() {
         const fT = $('#fTanggal').val();
         const q = String($('#fCari').val() || '').trim().toLowerCase();
         const tampil = data.filter(r => (!fT || r.tanggal === fT) &&
            (!q || `${r.nama_item} ${r.dosis} ${r.signature} ${r.petugas_nama || ''}`.toLowerCase().includes(q)));
         const $tb = $('#tabelRiwayat tbody');
         if (!data.length) {
            $tb.html('<tr><td colspan="9" class="kosong-data"><i class="ti ti-clipboard-off"></i>Belum ada catatan pemberian obat. Isi di bagian <b>Tambah pemberian obat</b> di bawah.</td></tr>');
            return;
         }
         if (!tampil.length) {
            $tb.html('<tr><td colspan="9" class="kosong-data"><i class="ti ti-search"></i>Tidak ada data yang cocok dengan filter.</td></tr>');
            return;
         }
         // terbaru di atas, dikelompokkan per tanggal
         const grup = {};
         tampil.forEach(r => (grup[r.tanggal] = grup[r.tanggal] || []).push(r));
         $tb.html(Object.keys(grup).sort().reverse().map(t =>
            `<tr class="grp"><td colspan="9"><i class="ti ti-calendar me-1"></i>${esc(tglPanjang(t))} <span class="n">· ${grup[t].length} obat</span></td></tr>` +
            grup[t].map(barisLihat).join('')).join(''));
      }

      $('#fTanggal').on('change', render);
      $('#fCari').on('input', render);

      // ubah inline
      $(document).on('click', '#tabelRiwayat .btn-edit', function() {
         const $tr = $(this).closest('tr');
         const r = data.find(x => +x.id === +$tr.data('id'));
         if (!r) return;
         $tr.addClass('editing').html(`
            <td class="aksi">
               <button type="button" class="btn btn-sm btn-success btn-save" title="Simpan"><i class="ti ti-check"></i></button>
               <button type="button" class="btn btn-sm btn-outline-dark btn-batal" title="Batal"><i class="ti ti-x"></i></button>
            </td>
            <td><input class="form-control mb-1" data-f="nama_obat" maxlength="255" list="dlObat"><input type="date" class="form-control" data-f="tanggal"></td>
            <td><input class="form-control" data-f="dosis" maxlength="100"></td>
            <td><input class="form-control" data-f="signature" maxlength="100"></td>
            ${JAM.map(k => `<td><input type="time" class="form-control" data-f="${k}"></td>`).join('')}
            <td data-ptg></td>`);
         $tr.find('[data-f="nama_obat"]').val(r.nama_item);
         $tr.find('[data-f="tanggal"]').val(r.tanggal);
         $tr.find('[data-f="dosis"]').val(r.dosis);
         $tr.find('[data-f="signature"]').val(r.signature);
         JAM.forEach(k => $tr.find(`[data-f="${k}"]`).val(r[k] || ''));
         $tr.find('[data-ptg]').append(opsiPetugas(r.petugas).attr('data-f', 'petugas'));
         $tr.find('[data-f="nama_obat"]').trigger('focus');
      });
      $(document).on('click', '#tabelRiwayat .btn-batal', render);
      $(document).on('click', '#tabelRiwayat .btn-save', function() {
         const $tr = $(this).closest('tr');
         const kirim = { id: $tr.data('id') };
         $tr.find('[data-f]').each(function() { kirim[$(this).data('f')] = $(this).val(); });
         if (!String(kirim.nama_obat).trim()) return $tr.find('[data-f="nama_obat"]').addClass('is-invalid').trigger('focus');
         const $b = $(this).prop('disabled', true);
         $.post('controller/visit/updateCPO.php', kirim, null, 'json')
            .done(res => {
               if (res.status === 'success') {
                  Swal.fire({ icon: 'success', title: 'Tersimpan', text: res.message, timer: 1300, showConfirmButton: false });
                  muat();
               } else Swal.fire('Gagal', res.message || 'Gagal memperbarui.', 'error');
            })
            .fail(xhr => Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan pada server.', 'error'))
            .always(() => $b.prop('disabled', false));
      });

      $(document).on('click', '#tabelRiwayat .btn-delete', function() {
         const id = $(this).closest('tr').data('id');
         const r = data.find(x => +x.id === +id) || {};
         Swal.fire({
            title: 'Hapus catatan ini?',
            html: `<b>${esc(r.nama_item)}</b> — ${esc(tglPanjang(r.tanggal))}`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Hapus',
            confirmButtonColor: '#dc2626',
            cancelButtonText: 'Batal',
            reverseButtons: true
         }).then(x => {
            if (!x.isConfirmed) return;
            $.post('controller/visit/deleteCpo.php', { id }, null, 'json')
               .done(res => {
                  if (res.status === 'success') {
                     Swal.fire({ icon: 'success', title: 'Dihapus', timer: 1200, showConfirmButton: false });
                     muat();
                  } else Swal.fire('Gagal', res.message || 'Gagal menghapus.', 'error');
               })
               .fail(xhr => Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan pada server.', 'error'));
         });
      });

      // ---------- TAMBAH ----------
      function tambahBaris(isi = {}) {
         const $tr = $(`<tr>
            <td class="aksi"><button type="button" class="btn btn-sm btn-outline-danger btn-remove" title="Hapus baris"><i class="ti ti-x"></i></button></td>
            <td><input type="date" name="tanggal[]" class="form-control" required></td>
            <td><input type="text" name="nama_obat[]" class="form-control" list="dlObat" maxlength="255" placeholder="Nama obat"></td>
            <td><input type="text" name="dosis[]" class="form-control" maxlength="100" placeholder="1 tab"></td>
            <td><input type="text" name="signature[]" class="form-control" maxlength="100" placeholder="3x1"></td>
            ${JAM.map(k => `<td><input type="time" name="${k}[]" class="form-control"></td>`).join('')}
            <td data-ptg></td>
         </tr>`);
         $tr.find('[name="tanggal[]"]').val(isi.tanggal || hariIni()).attr('max', hariIni());
         $tr.find('[name="nama_obat[]"]').val(isi.nama_item || '');
         $tr.find('[name="dosis[]"]').val(isi.dosis || '');
         $tr.find('[name="signature[]"]').val(isi.signature || '');
         $tr.find('[data-ptg]').append(opsiPetugas(isi.petugas || PETUGAS_SAYA).attr('name', 'petugas[]'));
         $('#tabelBaru tbody').append($tr);
         infoBaru();
         return $tr;
      }
      const barisTerisi = () => $('#tabelBaru tbody tr').filter(function() { return $(this).find('[name="nama_obat[]"]').val().trim() !== ''; });
      function infoBaru() {
         const n = barisTerisi().length;
         berubah = n > 0;
         $('#infoBaru').text(n ? `${n} obat siap disimpan` : '');
      }

      $('#addRow').on('click', () => tambahBaris().find('[name="nama_obat[]"]').trigger('focus'));
      $('#keTambah').on('click', () => {
         document.getElementById('formCPO').scrollIntoView({ behavior: 'smooth', block: 'start' });
         const $kosong = $('#tabelBaru tbody tr').filter(function() { return !$(this).find('[name="nama_obat[]"]').val(); }).first();
         ($kosong.length ? $kosong : tambahBaris()).find('[name="nama_obat[]"]').trigger('focus');
      });
      $(document).on('click', '#tabelBaru .btn-remove', function() {
         $(this).closest('tr').remove();
         if (!$('#tabelBaru tbody tr').length) tambahBaris();
         infoBaru();
      });
      $('#tabelBaru').on('input change', 'input, select', function() {
         $(this).removeClass('is-invalid');
         infoBaru();
      });
      // nama obat yang pernah dicatat -> isi dosis & signa bila masih kosong
      $('#tabelBaru').on('change', '[name="nama_obat[]"]', function() {
         const r = (window.__cpoTerakhir || {})[$(this).val().trim()];
         if (!r) return;
         const $tr = $(this).closest('tr');
         if (!$tr.find('[name="dosis[]"]').val()) $tr.find('[name="dosis[]"]').val(r.dosis || '');
         if (!$tr.find('[name="signature[]"]').val()) $tr.find('[name="signature[]"]').val(r.signature || '');
      });

      // salin daftar obat tanggal terakhir ke tanggal hari ini (jam dikosongkan)
      $('#salinTerakhir').on('click', function() {
         const tgl = [...new Set(data.map(r => r.tanggal))].sort().pop();
         if (!tgl) return;
         const unik = {};
         data.filter(r => r.tanggal === tgl).forEach(r => { unik[String(r.nama_item).trim().toLowerCase()] = r; });
         $('#tabelBaru tbody tr').filter(function() { return !$(this).find('[name="nama_obat[]"]').val().trim(); }).remove();
         Object.values(unik).forEach(r => tambahBaris({ nama_item: r.nama_item, dosis: r.dosis, signature: r.signature }));
         Swal.fire({ icon: 'info', title: `${Object.keys(unik).length} obat disalin`, text: `Dari ${tglPanjang(tgl)}. Lengkapi jam pemberian lalu simpan.`, timer: 2200, showConfirmButton: false });
      });

      $('#formCPO').on('submit', function(e) {
         e.preventDefault();
         const $isi = barisTerisi();
         if (!$isi.length) {
            $('#tabelBaru [name="nama_obat[]"]').first().addClass('is-invalid').trigger('focus');
            Swal.fire('Belum ada obat', 'Isi nama obat minimal 1 baris.', 'warning');
            return;
         }
         let salah = null;
         $isi.each(function() {
            const $t = $(this).find('[name="tanggal[]"]');
            if (!$t.val() || $t.val() > hariIni()) { $t.addClass('is-invalid'); salah = salah || $t; }
         });
         if (salah) {
            salah.trigger('focus');
            Swal.fire('Periksa tanggal', 'Tanggal wajib diisi dan tidak boleh di masa depan.', 'warning');
            return;
         }
         const $b = $('#btnSimpan').prop('disabled', true);
         // kirim hanya baris yang terisi
         const fd = new URLSearchParams();
         fd.append('nomor_visit', VISIT);
         $isi.each(function() {
            $(this).find('[name]').each(function() { fd.append(this.name, $(this).val() ?? ''); });
         });
         $.ajax({ url: 'controller/visit/cpoController.php', type: 'POST', data: fd.toString(), dataType: 'json' })
            .done(res => {
               if (res.status === 'success') {
                  berubah = false;
                  $('#tabelBaru tbody').empty();
                  tambahBaris();
                  Swal.fire({ icon: 'success', title: 'Tersimpan', text: res.message, timer: 1500, showConfirmButton: false });
                  muat();
               } else Swal.fire('Gagal', res.message || 'Gagal menyimpan.', 'error');
            })
            .fail(xhr => Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan pada server.', 'error'))
            .always(() => $b.prop('disabled', false));
      });

      window.addEventListener('beforeunload', e => {
         if (berubah) { e.preventDefault(); e.returnValue = ''; }
      });

      tambahBaris();
      muat();
   })();
</script>
<?php endif ?>
</body>

</html>
