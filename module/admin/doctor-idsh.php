<?php
$title = 'Data Dokter';
require '../../controller/view.php';
$isSuper = in_array($_SESSION['roles'] ?? '', ['superadmin', 'administrator'], true);
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .min-w-0 { min-width: 0; }
    .di-icon { width: 40px; height: 40px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
    .di-kpi { border: 1px solid #e5eaef; border-radius: 12px; background: #fff; padding: 14px 16px; height: 100%; cursor: pointer; transition: border-color .15s, box-shadow .15s; }
    .di-kpi:hover { border-color: #c9d2dc; box-shadow: 0 4px 14px rgba(0, 0, 0, .05); }
    .di-kpi.on { border-color: var(--c); box-shadow: 0 0 0 3px color-mix(in srgb, var(--c) 18%, transparent); }
    .di-val { font-size: 1.35rem; font-weight: 700; color: #2a3547; line-height: 1.2; }
    .di-label { font-size: .75rem; color: #6c757d; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .di-sub { font-size: .7rem; color: #98a2b3; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .di-prog { height: 8px; border-radius: 4px; background: #eef1f4; overflow: hidden; display: flex; }
    .di-prog > div { height: 100%; }
    .di-badge { font-size: .7rem; font-weight: 600; padding: .25em .65em; border-radius: 20px; white-space: nowrap; }
    #diTable td { font-size: .84rem; vertical-align: middle; }
    #diTable th { font-size: .75rem; color: #5a6a85; font-weight: 600; white-space: nowrap; }
    #diTable tr.nonaktif td { color: #adb5bd; }
    .di-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: .82rem; }
    .di-ava { width: 34px; height: 34px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: .8rem; flex-shrink: 0; }
    .nik-input { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; letter-spacing: 1px; font-size: 1.05rem; }
  </style>
</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <?php require '../admin/sidebar.php'; ?>
    <div class="body-wrapper">
      <?php require $isSuper ? '../admin/navbar-master.php' : '../admin/navbar.php'; ?>
      <div class="body-wrapper-inner">
        <div class="container-fluid">

          <!-- HEADER -->
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-3">
              <div class="d-flex flex-wrap align-items-center gap-3">
                <span class="di-icon bg-primary-subtle text-primary"><i class="fas fa-user-md"></i></span>
                <div class="min-w-0 flex-grow-1">
                  <h5 class="fw-semibold mb-0">ID Satu Sehat Dokter</h5>
                  <div class="text-muted small">Dokter wajib punya NIK & ID IHS (Practitioner) agar kunjungan bisa dikirim ke Satu Sehat.</div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                  <button type="button" class="btn btn-primary btn-sm" id="btnCariSemua" disabled><i class="fas fa-search me-1"></i>Cari ID IHS semua</button>
                  <a href="module/admin/setting_satusehat" class="btn btn-light btn-sm"><i class="fas fa-cog me-1"></i>Pengaturan Satu Sehat</a>
                  <button type="button" class="btn btn-light btn-sm" id="btnRefresh" title="Muat ulang"><i class="fas fa-sync-alt"></i></button>
                </div>
              </div>
              <div class="mt-3">
                <div class="d-flex justify-content-between small mb-1"><span class="text-muted">Kelengkapan dokter aktif</span><span class="fw-semibold" id="diPct">-</span></div>
                <div class="di-prog"><div class="bg-success" id="diProgOk" style="width:0"></div><div style="background:#ffae1f;width:0" id="diProgIhs"></div></div>
              </div>
            </div>
          </div>

          <!-- KPI (klik untuk menyaring) -->
          <div class="row g-3 mb-3">
            <div class="col-6 col-xl-3"><div class="di-kpi on" data-f="" style="--c:#5d87ff"><div class="d-flex align-items-center gap-2 mb-1"><span class="di-icon bg-primary-subtle text-primary"><i class="fas fa-users"></i></span>
              <div class="min-w-0"><div class="di-val" id="kTotal">-</div><div class="di-label">Dokter aktif</div></div></div><div class="di-sub" id="kTotalSub">&nbsp;</div></div></div>
            <div class="col-6 col-xl-3"><div class="di-kpi" data-f="lengkap" style="--c:#13deb9"><div class="d-flex align-items-center gap-2 mb-1"><span class="di-icon bg-success-subtle text-success"><i class="fas fa-check-circle"></i></span>
              <div class="min-w-0"><div class="di-val" id="kLengkap">-</div><div class="di-label">Sudah punya ID IHS</div></div></div><div class="di-sub">Siap kirim ke Satu Sehat</div></div></div>
            <div class="col-6 col-xl-3"><div class="di-kpi" data-f="cari_ihs" style="--c:#ffae1f"><div class="d-flex align-items-center gap-2 mb-1"><span class="di-icon bg-warning-subtle text-warning"><i class="fas fa-search"></i></span>
              <div class="min-w-0"><div class="di-val" id="kCari">-</div><div class="di-label">NIK ada, ID IHS belum</div></div></div><div class="di-sub">Klik "Cari ID IHS"</div></div></div>
            <div class="col-6 col-xl-3"><div class="di-kpi" data-f="nik" style="--c:#fa896b"><div class="d-flex align-items-center gap-2 mb-1"><span class="di-icon bg-danger-subtle text-danger"><i class="fas fa-id-card"></i></span>
              <div class="min-w-0"><div class="di-val" id="kNik">-</div><div class="di-label">NIK belum valid</div></div></div><div class="di-sub" id="kNikSub">&nbsp;</div></div></div>
          </div>
          <div class="alert alert-warning small py-2 d-none" id="diDampak"></div>

          <!-- TABEL -->
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                <div class="small text-muted" id="diInfo"></div>
                <div class="form-check form-switch mb-0"><input class="form-check-input" type="checkbox" id="diNonaktif"><label class="form-check-label small" for="diNonaktif">Tampilkan dokter nonaktif</label></div>
              </div>
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 w-100" id="diTable">
                  <thead><tr><th>Dokter</th><th>NIK</th><th>ID IHS (Practitioner)</th><th class="text-end" title="Kunjungan 30 hari terakhir">Kunjungan 30 hr</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                  <tbody></tbody>
                </table>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <!-- MODAL NIK -->
  <div class="modal fade" id="nikModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form class="modal-content" id="nikForm" autocomplete="off" novalidate>
        <div class="modal-header">
          <div><h5 class="modal-title mb-0">NIK Dokter</h5><div class="small text-muted" id="nikNama"></div></div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <label class="form-label small" for="nikInput">NIK (16 digit, sesuai KTP & SISDMK)</label>
          <input type="text" inputmode="numeric" maxlength="20" class="form-control nik-input" id="nikInput" placeholder="0000000000000000">
          <div class="d-flex justify-content-between small mt-1"><span class="text-danger" id="nikErr"></span><span class="text-muted" id="nikCount">0/16</span></div>
          <div class="form-check mt-3"><input class="form-check-input" type="checkbox" id="nikCari" checked><label class="form-check-label small" for="nikCari">Langsung cari ID IHS setelah disimpan</label></div>
          <div class="small text-muted mt-2"><i class="fas fa-info-circle me-1"></i>Mengubah NIK akan mengosongkan ID IHS lama.</div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary" id="nikSimpan"><i class="fas fa-save me-1"></i>Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <?php require '../admin/library.php'; ?>
  <script>
    (function() {
      const API = 'controller/master/doctorIDSHDetailControllerByClinic.php';
      const API_SS = 'controller/master/settingSatusehatController.php';
      const ST = { lengkap: ['Lengkap', 'bg-success-subtle text-success'], cari_ihs: ['ID IHS belum dicari', 'bg-warning-subtle text-warning'], nik: ['NIK belum valid', 'bg-danger-subtle text-danger'] };
      const WARNA = ['#5d87ff', '#13deb9', '#ffae1f', '#fa896b', '#a855f7', '#539bff', '#14b8a6', '#f472b6'];
      const $id = id => document.getElementById(id);
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const fmt = n => Number(n || 0).toLocaleString('id-ID');
      const inisial = s => String(s || '?').replace(/^\s*drg?\.?\s*/i, '').replace(/[^\p{L}\s]/gu, ' ').trim().split(/\s+/).slice(0, 2).map(w => w[0] || '').join('').toUpperCase() || '?';
      const masker = n => n && n.length >= 8 ? n.slice(0, 4) + '•'.repeat(Math.max(0, n.length - 8)) + n.slice(-4) : (n || '');
      let D = [], filter = '', sibuk = false;

      async function muat() {
        $id('btnRefresh').querySelector('i').classList.add('fa-spin');
        try {
          const r = await fetch(API, { credentials: 'same-origin', cache: 'no-store' });
          const j = await r.json().catch(() => null);
          if (!r.ok || !j || j.status !== 'success') throw new Error((j && j.message) || 'Gagal memuat data (HTTP ' + r.status + ').');
          D = j.data;
          tampil();
        } catch (e) {
          tabel.clear().draw();
          $id('diTable').querySelector('tbody').innerHTML = `<tr><td colspan="6" class="text-center text-danger py-5"><i class="fas fa-exclamation-circle fa-2x d-block mb-2"></i>${esc(e.message)}</td></tr>`;
        } finally {
          $id('btnRefresh').querySelector('i').classList.remove('fa-spin');
        }
      }

      function tampil() {
        const aktif = D.filter(d => d.aktif);
        const c = k => aktif.filter(d => d.status === k).length;
        const lengkap = c('lengkap'), cari = c('cari_ihs'), nik = c('nik');
        $id('kTotal').textContent = fmt(aktif.length);
        $id('kTotalSub').textContent = D.length - aktif.length ? fmt(D.length - aktif.length) + ' dokter nonaktif' : 'Semua dokter aktif';
        $id('kLengkap').textContent = fmt(lengkap);
        $id('kCari').textContent = fmt(cari);
        $id('kNik').textContent = fmt(nik);
        const dobel = aktif.filter(d => d.nik_dobel).length;
        $id('kNikSub').textContent = 'Kosong / bukan 16 digit' + (dobel ? ' · ' + fmt(dobel) + ' dokter NIK ganda' : '');
        const pct = aktif.length ? Math.round(lengkap / aktif.length * 100) : 0;
        $id('diPct').textContent = pct + '% (' + fmt(lengkap) + '/' + fmt(aktif.length) + ')';
        $id('diProgOk').style.width = (aktif.length ? lengkap / aktif.length * 100 : 0) + '%';
        $id('diProgIhs').style.width = (aktif.length ? cari / aktif.length * 100 : 0) + '%';
        const terdampak = aktif.filter(d => d.status !== 'lengkap');
        const kunj = terdampak.reduce((a, d) => a + d.kunjungan_30, 0);
        $id('diDampak').classList.toggle('d-none', !kunj);
        $id('diDampak').innerHTML = `<i class="fas fa-exclamation-triangle me-1"></i><b>${fmt(kunj)} kunjungan</b> dalam 30 hari terakhir ditangani ${fmt(terdampak.filter(d => d.kunjungan_30).length)} dokter yang belum punya ID IHS. Kunjungan ini tidak bisa terkirim ke Satu Sehat (error "Practitioner/").`;
        $id('btnCariSemua').disabled = !cari || sibuk;
        $id('btnCariSemua').innerHTML = `<i class="fas fa-search me-1"></i>Cari ID IHS semua${cari ? ' (' + cari + ')' : ''}`;
        document.querySelectorAll('.di-kpi').forEach(el => el.classList.toggle('on', el.dataset.f === filter));
        isiTabel();
      }

      const jq = window.jQuery;
      const tabel = jq('#diTable').DataTable({
        data: [], pageLength: 25, order: [[3, 'desc']], autoWidth: false,
        language: { search: 'Cari dokter:', lengthMenu: 'Tampil _MENU_', info: '_START_–_END_ dari _TOTAL_ dokter', infoEmpty: '0 dokter', infoFiltered: '(dari _MAX_)', zeroRecords: 'Tidak ada dokter yang cocok', emptyTable: 'Belum ada data dokter', paginate: { previous: '‹', next: '›' } },
        createdRow: (row, d) => { if (!d.aktif) row.classList.add('nonaktif'); },
        columns: [
          { data: 'doctor_name', render: (v, t, r) => t !== 'display' ? v : `<div class="d-flex align-items-center gap-2"><span class="di-ava" style="background:${WARNA[r.id_doctor % WARNA.length]}22;color:${WARNA[r.id_doctor % WARNA.length]}">${esc(inisial(v))}</span>
              <div class="min-w-0"><div class="fw-semibold">${esc(v || '-')}</div><div class="small text-muted">${esc(r.doctor_phone || '')}${r.aktif ? '' : ' · nonaktif'}</div></div></div>` },
          { data: 'doctor_nik', render: (v, t, r) => t !== 'display' ? v : (v ? `<span class="di-mono" title="${esc(v)}">${esc(masker(v))}</span>` + (!r.nik_valid ? '<div class="small text-danger">bukan 16 digit</div>' : '') + (r.nik_dobel ? '<div class="small text-danger">dipakai dokter lain</div>' : '') : '<span class="text-muted">Belum diisi</span>') },
          { data: 'idsh', render: (v, t) => t !== 'display' ? v : (v ? `<span class="di-mono">${esc(v)}</span> <button type="button" class="btn btn-link btn-sm p-0 ms-1 di-copy" data-v="${esc(v)}" title="Salin"><i class="far fa-copy"></i></button>` : '<span class="text-muted">-</span>') },
          { data: 'kunjungan_30', className: 'text-end', render: (v, t, r) => t !== 'display' ? v : (v ? (r.status !== 'lengkap' ? `<b class="text-danger">${fmt(v)}</b>` : fmt(v)) : '<span class="text-muted">0</span>') },
          { data: 'status', render: (v, t) => t !== 'display' ? ST[v][0] : `<span class="di-badge ${ST[v][1]}">${ST[v][0]}</span>` },
          { data: null, orderable: false, className: 'text-end text-nowrap', render: r => {
            const b = [];
            b.push(`<button type="button" class="btn btn-sm btn-light di-nik" data-id="${r.id_doctor}" title="${r.doctor_nik ? 'Ubah' : 'Isi'} NIK"><i class="fas fa-id-card me-1"></i>${r.doctor_nik ? 'Ubah' : 'Isi'} NIK</button>`);
            if (r.nik_valid) b.push(`<button type="button" class="btn btn-sm ${r.idsh ? 'btn-light' : 'btn-primary'} di-ihs" data-id="${r.id_doctor}" title="${r.idsh ? 'Perbarui' : 'Cari'} ID IHS dari Satu Sehat"><i class="fas fa-${r.idsh ? 'sync-alt' : 'search'} me-1"></i>${r.idsh ? 'Perbarui' : 'Cari IHS'}</button>`);
            return b.join(' ');
          } }
        ]
      });
      function isiTabel() {
        const nonaktif = $id('diNonaktif').checked;
        const rows = D.filter(d => (nonaktif || d.aktif) && (!filter || d.status === filter));
        tabel.clear().rows.add(rows).draw();
        $id('diInfo').textContent = fmt(rows.length) + ' dokter' + (filter ? ' · ' + ST[filter][0] : '') + ' · diurutkan berdasarkan kunjungan 30 hari';
      }
      document.querySelectorAll('.di-kpi').forEach(el => el.addEventListener('click', () => { filter = filter === el.dataset.f ? '' : el.dataset.f; tampil(); }));
      $id('diNonaktif').addEventListener('change', isiTabel);
      $id('btnRefresh').addEventListener('click', muat);

      /* ---------- aksi ---------- */
      async function post(data) {
        const fd = new FormData();
        Object.entries(data).forEach(([k, v]) => fd.append(k, v));
        const r = await fetch(API_SS, { method: 'POST', body: fd, credentials: 'same-origin' });
        const j = await r.json().catch(() => null);
        if (!r.ok || !j || j.status !== 'success') throw new Error((j && j.message) || 'Permintaan gagal (HTTP ' + r.status + ').');
        return j;
      }
      async function cariIhs(id, diam) {
        const d = D.find(x => x.id_doctor === id);
        const j = await post({ aksi: 'ihs', id_doctor: id });
        d.idsh = j.idsh;
        d.status = 'lengkap';
        if (!diam) Swal.fire({ icon: 'success', title: 'ID IHS ditemukan', html: `<b>${esc(d.doctor_name)}</b><br><span class="di-mono">${esc(j.idsh)}</span>` + (j.nama_satusehat ? `<div class="small text-muted mt-1">Nama di Satu Sehat: ${esc(j.nama_satusehat)}</div>` : ''), timer: 3000 });
        return j;
      }
      jq('#diTable').on('click', '.di-ihs', async function() {
        const btn = this, id = +btn.dataset.id, label = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        try { await cariIhs(id); tampil(); }
        catch (e) { Swal.fire('ID IHS tidak ditemukan', e.message, 'warning'); btn.disabled = false; btn.innerHTML = label; }
      });
      jq('#diTable').on('click', '.di-copy', function() {
        navigator.clipboard?.writeText(this.dataset.v).then(() => Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'ID IHS disalin', timer: 1200, showConfirmButton: false }));
      });

      // modal NIK
      const modal = bootstrap.Modal.getOrCreateInstance($id('nikModal'));
      let nikId = 0;
      const cekNik = () => {
        const v = $id('nikInput').value.replace(/\D/g, '');
        $id('nikCount').textContent = v.length + '/16';
        const dobel = v.length === 16 && D.some(d => d.id_doctor !== nikId && d.doctor_nik === v);
        $id('nikErr').textContent = v && v.length !== 16 ? 'NIK harus 16 digit.' : dobel ? 'NIK ini sudah dipakai dokter lain.' : '';
        $id('nikSimpan').disabled = v.length !== 16;
        return v;
      };
      jq('#diTable').on('click', '.di-nik', function() {
        nikId = +this.dataset.id;
        const d = D.find(x => x.id_doctor === nikId);
        $id('nikNama').textContent = d.doctor_name;
        $id('nikInput').value = d.doctor_nik || '';
        cekNik();
        modal.show();
      });
      $id('nikModal').addEventListener('shown.bs.modal', () => $id('nikInput').focus());
      $id('nikInput').addEventListener('input', function() { this.value = this.value.replace(/\D/g, '').slice(0, 16); cekNik(); });
      $id('nikForm').addEventListener('submit', async e => {
        e.preventDefault();
        const nik = cekNik();
        if (nik.length !== 16) return;
        const btn = $id('nikSimpan'), label = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan…';
        try {
          const j = await post({ aksi: 'nik', id_doctor: nikId, doctor_nik: nik });
          const d = D.find(x => x.id_doctor === nikId);
          if (j.berubah) { d.doctor_nik = nik; d.idsh = ''; d.nik_valid = true; d.status = 'cari_ihs'; }
          modal.hide();
          if ($id('nikCari').checked) {
            try { await cariIhs(nikId); } catch (er) { Swal.fire('NIK disimpan, ID IHS belum ditemukan', er.message, 'warning'); }
          } else {
            Swal.fire({ icon: 'success', title: j.message, timer: 1400, showConfirmButton: false });
          }
          await muat();
        } catch (er) {
          $id('nikErr').textContent = er.message;
        } finally {
          btn.disabled = false;
          btn.innerHTML = label;
        }
      });

      // cari IHS semua yang NIK-nya valid (berurutan, agar tidak kena rate limit)
      $id('btnCariSemua').addEventListener('click', async () => {
        const target = D.filter(d => d.aktif && d.status === 'cari_ihs');
        if (!target.length) return;
        const r = await Swal.fire({ icon: 'question', title: 'Cari ID IHS ' + target.length + ' dokter?', text: 'Dicari satu per satu ke Satu Sehat berdasarkan NIK.', showCancelButton: true, confirmButtonText: 'Mulai', cancelButtonText: 'Batal', reverseButtons: true });
        if (!r.isConfirmed) return;
        sibuk = true;
        let ok = 0;
        const gagal = [];
        Swal.fire({ title: 'Mencari ID IHS…', html: '<div id="swProg">0/' + target.length + '</div>', allowOutsideClick: false, showConfirmButton: false, didOpen: () => Swal.showLoading() });
        for (let i = 0; i < target.length; i++) {
          try { await cariIhs(target[i].id_doctor, true); ok++; } catch (e) { gagal.push(target[i].doctor_name + ': ' + e.message); }
          const p = document.getElementById('swProg');
          if (p) p.textContent = (i + 1) + '/' + target.length + ' · ditemukan ' + ok;
        }
        sibuk = false;
        await muat();
        Swal.fire({ icon: gagal.length ? (ok ? 'warning' : 'error') : 'success', title: ok + ' dari ' + target.length + ' ID IHS ditemukan',
          html: gagal.length ? '<div class="text-start small" style="max-height:220px;overflow:auto">' + gagal.map(esc).join('<br>') + '</div>' : '' });
      });

      muat();
    })();
  </script>
</body>

</html>
