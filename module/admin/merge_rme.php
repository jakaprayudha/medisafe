<?php
$title = 'Gabung RM Ganda';
require '../../controller/view.php';
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .min-w-0 { min-width: 0; }
    .gr-icon { width: 40px; height: 40px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
    .gr-kpi { border: 1px solid #e5eaef; border-radius: 12px; background: #fff; padding: 14px 16px; height: 100%; }
    .gr-val { font-size: 1.35rem; font-weight: 700; color: #2a3547; line-height: 1.2; }
    .gr-label { font-size: .75rem; color: #6c757d; }
    .gr-grup { border: 1px solid #e5eaef; border-radius: 12px; background: #fff; margin-bottom: 10px; transition: border-color .15s, box-shadow .15s; }
    .gr-grup:hover { border-color: #c9d6f7; box-shadow: 0 4px 14px rgba(93, 135, 255, .08); }
    .gr-rm { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-weight: 600; font-size: .8rem; background: #f1f5f9; border-radius: 6px; padding: 1px 7px; color: #334155; white-space: nowrap; }
    .gr-mem { display: flex; align-items: center; gap: 10px; padding: 8px 14px; border-top: 1px dashed #edf0f4; font-size: .82rem; }
    .gr-mem:first-of-type { border-top: 0; }
    .gr-chip { font-size: .66rem; font-weight: 600; padding: .2em .6em; border-radius: 20px; white-space: nowrap; }
    .gr-col { border: 1.5px solid #e5eaef; border-radius: 12px; background: #fff; height: 100%; display: flex; flex-direction: column; transition: border-color .15s, background .15s; }
    .gr-col.utama { border-color: #5d87ff; background: #f6f9ff; }
    .gr-col.ikut { border-color: #13deb9; }
    .gr-col.lepas { opacity: .55; }
    .gr-col dl { margin: 0; padding: 0 14px 10px; font-size: .8rem; }
    .gr-col dt { font-weight: 400; color: #6c757d; font-size: .7rem; margin-top: 6px; }
    .gr-col dd { margin: 0; font-weight: 500; color: #2a3547; word-break: break-word; }
    .gr-col dd.beda { color: #b45309; }
    .gr-col dd.beda::after { content: ' ≠'; font-weight: 700; }
    .gr-col dd.kosong { color: #adb5bd; font-weight: 400; }
    .gr-pilih { padding: 10px 14px; border-bottom: 1px solid #edf0f4; }
    .gr-pilih label { cursor: pointer; font-size: .8rem; }
    .gr-legend { width: 10px; height: 10px; border-radius: 3px; display: inline-block; }
    .gr-tl { max-height: 340px; overflow: auto; }
    .gr-tl table { font-size: .78rem; }
    .gr-tl thead th { position: sticky; top: 0; background: #f8fafc; z-index: 1; }
    .gr-sk { height: 92px; border-radius: 12px; margin-bottom: 10px; background: linear-gradient(90deg, #eef1f4 25%, #f6f8fb 50%, #eef1f4 75%); background-size: 200% 100%; animation: grsk 1.2s infinite; }
    @keyframes grsk { to { background-position: -200% 0; } }
    #grModal .modal-body { background: #fbfcfe; }
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

          <!-- HEADER -->
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-3">
              <div class="d-flex flex-wrap align-items-center gap-3">
                <span class="gr-icon bg-primary-subtle text-primary"><i class="fas fa-code-merge"></i></span>
                <div class="min-w-0 flex-grow-1">
                  <h5 class="fw-semibold mb-0">Gabung RM Ganda</h5>
                  <div class="text-muted small">Satukan riwayat pasien yang terdaftar dengan lebih dari satu nomor RM. Dicocokkan dari NIK (16 digit) atau no. kartu BPJS (13 digit) yang sama.</div>
                </div>
                <button type="button" class="btn btn-light btn-sm" id="grRefresh" title="Muat ulang"><i class="fas fa-sync-alt"></i></button>
              </div>
            </div>
          </div>

          <!-- KPI -->
          <div class="row g-3 mb-3">
            <div class="col-md-4"><div class="gr-kpi d-flex align-items-center gap-2"><span class="gr-icon bg-warning-subtle text-warning"><i class="fas fa-users"></i></span>
              <div><div class="gr-val" id="kGrup">-</div><div class="gr-label">Pasien dengan RM ganda</div></div></div></div>
            <div class="col-md-4"><div class="gr-kpi d-flex align-items-center gap-2"><span class="gr-icon bg-danger-subtle text-danger"><i class="fas fa-id-card"></i></span>
              <div><div class="gr-val" id="kRm">-</div><div class="gr-label">Nomor RM terlibat</div></div></div></div>
            <div class="col-md-4"><div class="gr-kpi d-flex align-items-center gap-2"><span class="gr-icon bg-success-subtle text-success"><i class="fas fa-circle-check"></i></span>
              <div><div class="gr-val" id="kSelesai">0</div><div class="gr-label">Digabung di sesi ini</div></div></div></div>
          </div>

          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
              <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                <div class="position-relative flex-grow-1" style="max-width:420px">
                  <i class="fas fa-search position-absolute text-muted" style="left:11px;top:50%;transform:translateY(-50%);font-size:.8rem"></i>
                  <input type="search" id="grCari" class="form-control ps-5" placeholder="Cari pasien: nomor RM, nama, NIK, atau no. BPJS…" autocomplete="off">
                </div>
                <span class="small text-muted" id="grInfo"></span>
              </div>
              <div id="grList"><?php for ($i = 0; $i < 4; $i++): ?><div class="gr-sk"></div><?php endfor; ?></div>
              <nav class="d-flex justify-content-between align-items-center mt-2" id="grPager"></nav>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <!-- MODAL TINJAU & GABUNG -->
  <div class="modal fade" id="grModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <div class="min-w-0"><h5 class="modal-title mb-0">Tinjau RM ganda</h5><div class="small text-muted" id="grSub"></div></div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body" id="grBody"></div>
        <div class="modal-footer d-block" id="grFoot">
          <div class="d-flex flex-wrap align-items-center gap-2">
            <div class="small text-muted flex-grow-1" id="grRingkas"></div>
            <div class="form-check mb-0 d-none" id="grPaksaWrap">
              <input class="form-check-input" type="checkbox" id="grPaksa">
              <label class="form-check-label small text-warning-emphasis" for="grPaksa">Saya sudah memastikan ini orang yang sama</label>
            </div>
            <input type="text" class="form-control form-control-sm" id="grKonfirmasi" style="width:220px" placeholder="Ketik nomor RM utama" autocomplete="off">
            <button type="button" class="btn btn-danger" id="grGabung" disabled><i class="fas fa-code-merge me-1"></i>Gabungkan</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php require 'library.php'; ?>
  <script>
    (function() {
      const API = 'controller/admin/gabungRme.php';
      const WARNA = ['#5d87ff', '#13deb9', '#ffae1f', '#fa896b', '#a855f7', '#14b8a6', '#f472b6', '#539bff'];
      const BLN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      const STATUS = { 0: ['Menunggu', 'secondary'], 1: ['Diperiksa', 'info'], 4: ['Selesai', 'success'], 10: ['Rawat inap', 'primary'], 99: ['Batal', 'danger'] };
      const $id = id => document.getElementById(id);
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const fmt = n => Number(n || 0).toLocaleString('id-ID');
      const tgl = s => { if (!s) return '-'; const m = String(s).match(/^(\d{4})-(\d{2})-(\d{2})/); return m && m[1] > '1900' ? (+m[3]) + ' ' + BLN[m[2] - 1] + ' ' + m[1] : '-'; };
      const jk = s => { s = String(s || '').toUpperCase()[0]; return s === 'L' || s === 'M' || s === '1' ? 'L' : (s === 'P' || s === 'F' || s === 'W' || s === '2' ? 'P' : ''); };
      async function api(q, opt) {
        const r = await fetch(API + '?' + q, Object.assign({ credentials: 'same-origin', cache: 'no-store' }, opt || {}));
        const j = await r.json().catch(() => null);
        if (!j) throw new Error('Respons server tidak valid (HTTP ' + r.status + ').');
        if (j.status !== 'success' && !j.perlu_paksa) throw Object.assign(new Error(j.message || 'Permintaan gagal.'), { data: j });
        return j;
      }

      /* ---------------- daftar kandidat & pencarian ---------------- */
      let hal = 1, mode = 'kandidat', selesai = 0;
      function skel() { $id('grList').innerHTML = '<div class="gr-sk"></div>'.repeat(4); $id('grPager').innerHTML = ''; }
      function memHtml(m, i) {
        return `<div class="gr-mem"><span class="gr-legend" style="background:${WARNA[i % WARNA.length]}"></span>
          <span class="gr-rm">${esc(m.rm || '-')}</span>
          <span class="fw-semibold text-truncate min-w-0" style="max-width:260px" title="${esc(m.nama)}">${esc(m.nama || '-')}</span>
          <span class="text-muted d-none d-md-inline">${tgl(m.lahir)}${jk(m.jk) ? ' · ' + jk(m.jk) : ''}</span>
          <span class="ms-auto text-muted text-nowrap">${fmt(m.kunjungan)} kunjungan${m.akhir ? ' · terakhir ' + tgl(m.akhir) : ''}</span></div>`;
      }
      function kunciSama(g) {
        const nik = g.filter(m => m.nik_valid).map(m => m.nik), bp = g.filter(m => m.bpjs_valid).map(m => m.bpjs);
        const out = [];
        if (nik.length > 1 && new Set(nik).size < nik.length) out.push('NIK sama');
        if (bp.length > 1 && new Set(bp).size < bp.length) out.push('BPJS sama');
        return out;
      }
      function bedaIdentitas(g) {
        const l = new Set(g.map(m => (m.lahir || '').slice(0, 10)).filter(x => x > '1900')), j = new Set(g.map(m => jk(m.jk)).filter(Boolean));
        return l.size > 1 || j.size > 1;
      }
      async function muatKandidat(p) {
        mode = 'kandidat';
        hal = p || 1;
        skel();
        try {
          const j = await api('action=kandidat&page=' + hal);
          $id('kGrup').textContent = fmt(j.total);
          $id('kRm').textContent = fmt(j.total_pasien);
          $id('grInfo').textContent = j.total ? 'Diurutkan dari RM ganda terbanyak' : '';
          if (!j.data.length) {
            $id('grList').innerHTML = '<div class="text-center text-muted py-5"><i class="fas fa-circle-check fa-2x text-success d-block mb-2"></i>Tidak ditemukan RM ganda dengan NIK / no. BPJS valid yang sama.<div class="small mt-1">Pasien tanpa NIK/BPJS valid tetap bisa dicari lewat kolom pencarian.</div></div>';
            return;
          }
          $id('grList').innerHTML = j.data.map(g => {
            const k = kunciSama(g), beda = bedaIdentitas(g);
            return `<div class="gr-grup"><div class="d-flex align-items-center gap-2 px-3 pt-2 pb-1">
              <span class="small fw-semibold">${g.length} RM</span>
              ${k.map(x => `<span class="gr-chip bg-primary-subtle text-primary">${x}</span>`).join('')}
              ${beda ? '<span class="gr-chip bg-warning-subtle text-warning"><i class="fas fa-triangle-exclamation me-1"></i>Identitas berbeda</span>' : ''}
              <span class="small text-muted">${fmt(g.reduce((a, m) => a + m.kunjungan, 0))} kunjungan total</span>
              <button type="button" class="btn btn-sm btn-primary ms-auto gr-buka" data-id="${g[0].id}"><i class="fas fa-magnifying-glass me-1"></i>Tinjau</button></div>
              ${g.map(memHtml).join('')}</div>`;
          }).join('');
          pager(j.page, j.pages);
        } catch (e) {
          $id('grList').innerHTML = `<div class="text-center text-danger py-5"><i class="fas fa-circle-exclamation fa-2x d-block mb-2"></i>${esc(e.message)}</div>`;
        }
      }
      function pager(p, n) {
        if (n <= 1) { $id('grPager').innerHTML = ''; return; }
        $id('grPager').innerHTML = `<span class="small text-muted">Halaman ${p} dari ${fmt(n)}</span><div class="btn-group btn-group-sm">
          <button class="btn btn-light gr-hal" data-p="${p - 1}" ${p <= 1 ? 'disabled' : ''}><i class="fas fa-chevron-left"></i></button>
          <button class="btn btn-light gr-hal" data-p="${p + 1}" ${p >= n ? 'disabled' : ''}><i class="fas fa-chevron-right"></i></button></div>`;
      }
      $id('grPager').addEventListener('click', e => { const b = e.target.closest('.gr-hal'); if (b) { muatKandidat(+b.dataset.p); $id('grList').scrollIntoView({ behavior: 'smooth', block: 'start' }); } });

      let tCari, nCari = 0;
      $id('grCari').addEventListener('input', () => {
        clearTimeout(tCari);
        const kw = $id('grCari').value.trim();
        if (!kw) { muatKandidat(1); return; }
        if (kw.length < 3) return;
        tCari = setTimeout(() => cari(kw), 300);
      });
      async function cari(kw) {
        mode = 'cari';
        const no = ++nCari;
        skel();
        $id('grInfo').textContent = '';
        try {
          const j = await api('action=cari&keyword=' + encodeURIComponent(kw));
          if (no !== nCari) return;
          $id('grInfo').textContent = j.data.length >= 30 ? 'Menampilkan 30 hasil teratas — perjelas kata kunci' : j.data.length + ' pasien';
          $id('grList').innerHTML = !j.data.length ? '<div class="text-center text-muted py-5">Pasien tidak ditemukan.</div>' : j.data.map(m => `<div class="gr-grup">
            <div class="gr-mem"><span class="gr-rm">${esc(m.rm || '-')}</span>
              <div class="min-w-0"><div class="fw-semibold text-truncate">${esc(m.nama || '-')}</div>
                <div class="small text-muted">${tgl(m.lahir)}${jk(m.jk) ? ' · ' + jk(m.jk) : ''} · NIK ${esc(m.nik || '-')}${m.nik && !m.nik_valid ? ' <span class="text-danger">(tidak valid)</span>' : ''} · BPJS ${esc(m.bpjs || '-')}</div></div>
              <span class="ms-auto text-muted text-nowrap d-none d-md-inline">${fmt(m.kunjungan)} kunjungan</span>
              ${m.ganda ? `<span class="gr-chip bg-warning-subtle text-warning">${m.ganda} RM lain</span>` : '<span class="gr-chip bg-light text-muted">Tunggal</span>'}
              <button type="button" class="btn btn-sm ${m.ganda ? 'btn-primary' : 'btn-light'} gr-buka" data-id="${m.id}">Tinjau</button></div></div>`).join('');
        } catch (e) {
          if (no === nCari) $id('grList').innerHTML = `<div class="text-center text-danger py-5">${esc(e.message)}</div>`;
        }
      }
      $id('grRefresh').addEventListener('click', () => { const kw = $id('grCari').value.trim(); kw.length >= 3 ? cari(kw) : muatKandidat(hal); });

      /* ---------------- modal tinjau ---------------- */
      const modal = bootstrap.Modal.getOrCreateInstance($id('grModal'));
      let G = null, utama = null, ikut = new Set();
      $id('grList').addEventListener('click', e => { const b = e.target.closest('.gr-buka'); if (b) buka(+b.dataset.id); });

      async function buka(id) {
        G = null;
        $id('grSub').textContent = '';
        $id('grBody').innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
        $id('grFoot').classList.add('d-none');
        modal.show();
        try {
          const j = await api('action=grup&id_patient=' + id);
          G = j;
          utama = j.data[0].id;
          ikut = new Set(j.data.slice(1).map(m => m.id));
          if (j.data.length < 2) {
            $id('grSub').textContent = j.data[0].rm + ' · ' + j.data[0].nama;
            $id('grBody').innerHTML = `<div class="text-center text-muted py-5"><i class="fas fa-circle-info fa-2x d-block mb-2"></i>${esc(j.pesan || 'Tidak ada RM ganda.')}</div>`;
            return;
          }
          $id('grFoot').classList.remove('d-none');
          render();
        } catch (e) {
          $id('grBody').innerHTML = `<div class="text-center text-danger py-5">${esc(e.message)}</div>`;
        }
      }

      const BARIS = [
        ['Nama', m => m.nama, 'nama'], ['Tanggal lahir', m => tgl(m.lahir), 'lahir'], ['Jenis kelamin', m => jk(m.jk) === 'L' ? 'Laki-laki' : jk(m.jk) === 'P' ? 'Perempuan' : '', 'jk'],
        ['NIK', m => m.nik + (m.nik && !m.nik_valid ? ' (tidak valid)' : ''), 'nik'], ['No. kartu BPJS', m => m.bpjs + (m.bpjs && !m.bpjs_valid ? ' (tidak valid)' : ''), 'bpjs'],
        ['ID Satu Sehat', m => m.ihs, 'ihs'], ['No. HP', m => m.hp, 'hp'], ['Alamat', m => m.alamat, 'alamat'],
        ['Terdaftar', m => tgl(m.daftar), 'daftar'], ['Kunjungan', m => fmt(m.kunjungan) + (m.kunjungan ? ' (' + tgl(m.awal) + ' – ' + tgl(m.akhir) + ')' : ''), 'kunjungan'],
      ];
      const nilaiBanding = (k, m) => k === 'lahir' ? String(m.lahir || '').slice(0, 10) : k === 'jk' ? jk(m.jk) : k === 'nama' ? String(m.nama || '').toUpperCase().replace(/\b(TN|NY|NN|AN|BY|SDR|SDRI)\b\.?/g, '').replace(/[^A-Z]/g, '') : '';
      function render() {
        const D = G.data, u = D.find(m => m.id === utama);
        $id('grSub').textContent = D.length + ' RM ditemukan untuk ' + u.nama;
        const warnaOf = id => WARNA[D.findIndex(m => m.id === id) % WARNA.length];
        const kolom = D.map(m => {
          const isU = m.id === utama, isI = ikut.has(m.id);
          return `<div class="col-md-6 col-xl-${D.length >= 4 ? 3 : 12 / Math.min(D.length, 3)}"><div class="gr-col ${isU ? 'utama' : isI ? 'ikut' : 'lepas'}">
            <div class="gr-pilih d-flex flex-column gap-1">
              <div class="d-flex align-items-center gap-2"><span class="gr-legend" style="background:${warnaOf(m.id)}"></span><span class="gr-rm">${esc(m.rm)}</span>
                ${isU ? '<span class="gr-chip bg-primary text-white ms-auto">RM UTAMA</span>' : isI ? '<span class="gr-chip bg-success-subtle text-success ms-auto">Digabung</span>' : '<span class="gr-chip bg-light text-muted ms-auto">Tidak ikut</span>'}</div>
              <div class="form-check mb-0"><input class="form-check-input gr-utama" type="radio" name="grUtama" id="gu${m.id}" value="${m.id}" ${isU ? 'checked' : ''}><label class="form-check-label" for="gu${m.id}">Jadikan RM utama (dipertahankan)</label></div>
              <div class="form-check mb-0"><input class="form-check-input gr-ikut" type="checkbox" id="gi${m.id}" value="${m.id}" ${isI ? 'checked' : ''} ${isU ? 'disabled' : ''}><label class="form-check-label" for="gi${m.id}">Gabungkan ke RM utama</label></div>
            </div>
            <dl>${BARIS.map(([lbl, f, k]) => {
              const v = f(m), b = !isU && ['nama', 'lahir', 'jk'].includes(k) && nilaiBanding(k, m) && nilaiBanding(k, u) && nilaiBanding(k, m) !== nilaiBanding(k, u);
              return `<dt>${lbl}</dt><dd class="${!v || v === '-' ? 'kosong' : ''} ${b ? 'beda' : ''}">${esc(v && v !== '-' ? v : '—')}</dd>`;
            }).join('')}${m.rm_lain ? `<dt>RM lain tercatat</dt><dd>${esc(m.rm_lain)}</dd>` : ''}</dl></div></div>`;
        }).join('');

        const peringatan = D.filter(m => ikut.has(m.id)).map(m => {
          const r = [];
          ['lahir', 'jk'].forEach(k => { if (nilaiBanding(k, m) && nilaiBanding(k, u) && nilaiBanding(k, m) !== nilaiBanding(k, u)) r.push(k === 'lahir' ? 'tanggal lahir berbeda' : 'jenis kelamin berbeda'); });
          return r.length ? `<li><span class="gr-rm">${esc(m.rm)}</span> ${esc(m.nama)}: ${r.join(', ')}</li>` : '';
        }).join('');
        const kunj = G.kunjungan;
        const tl = kunj.length ? `<div class="gr-tl border rounded-3 bg-white"><table class="table table-sm mb-0 align-middle"><thead><tr><th>Tanggal</th><th>RM</th><th>Poli</th><th>Dokter</th><th>Status</th><th>No. kunjungan</th></tr></thead><tbody>
          ${kunj.map(v => { const m = D.find(x => x.id === v.pasien) || {}; const s = STATUS[v.status] || [v.status, 'light'];
            return `<tr class="${ikut.has(v.pasien) || v.pasien === utama ? '' : 'opacity-50'}"><td class="text-nowrap">${tgl(v.tgl)}</td><td><span class="gr-legend me-1" style="background:${warnaOf(v.pasien)}"></span><span class="gr-rm">${esc(m.rm || '-')}</span></td>
            <td>${esc(v.poli || '-')}</td><td class="text-truncate" style="max-width:200px">${esc(v.dokter || '-')}</td><td><span class="gr-chip bg-${s[1]}-subtle text-${s[1]}">${esc(s[0])}</span></td><td class="small text-muted">${esc(v.id)}</td></tr>`; }).join('')}
          </tbody></table></div>${G.kunjungan_terpotong ? '<div class="small text-muted mt-1">Menampilkan 300 kunjungan terbaru.</div>' : ''}` : '<div class="text-muted small">Belum ada kunjungan.</div>';

        $id('grBody').innerHTML = `
          <div class="alert alert-info small py-2 d-flex gap-2"><i class="fas fa-circle-info mt-1"></i><div>
            Semua kunjungan, CPPT, resume, triase, surat, dan dokumen dari RM yang dicentang akan dipindah ke <b>RM utama</b>. Data identitas yang kosong di RM utama dilengkapi dari RM yang digabung, nomor RM lama dicatat di "RM lain", lalu RM lama dihapus (cadangan disimpan di server).</div></div>
          ${peringatan ? `<div class="alert alert-warning small py-2"><div class="fw-semibold mb-1"><i class="fas fa-triangle-exclamation me-1"></i>Periksa kembali — identitas berbeda dengan RM utama:</div><ul class="mb-1 ps-3">${peringatan}</ul>Bisa jadi anggota keluarga yang memakai NIK / kartu yang sama (mis. bayi dengan NIK ibu). Jangan digabung jika bukan orang yang sama.</div>` : ''}
          <div class="row g-3 mb-3">${kolom}</div>
          <div class="fw-semibold small mb-2"><i class="fas fa-clock-rotate-left me-1 text-muted"></i>Riwayat kunjungan gabungan</div>${tl}`;
        $id('grPaksaWrap').classList.toggle('d-none', !peringatan);
        if (!peringatan) $id('grPaksa').checked = false;
        $id('grKonfirmasi').placeholder = 'Ketik ' + u.rm + ' untuk konfirmasi';
        const nK = D.filter(m => ikut.has(m.id)).reduce((a, m) => a + m.kunjungan, 0);
        $id('grRingkas').innerHTML = ikut.size ? `<b>${ikut.size} RM</b> (${fmt(nK)} kunjungan) akan digabung ke <span class="gr-rm">${esc(u.rm)}</span>` : 'Pilih minimal satu RM untuk digabung.';
        cekTombol();
      }
      function cekTombol() {
        const u = G && G.data.find(m => m.id === utama);
        const ok = u && ikut.size > 0 && $id('grKonfirmasi').value.trim().toUpperCase() === String(u.rm).trim().toUpperCase() &&
          ($id('grPaksaWrap').classList.contains('d-none') || $id('grPaksa').checked);
        $id('grGabung').disabled = !ok;
      }
      $id('grBody').addEventListener('change', e => {
        if (e.target.classList.contains('gr-utama')) {
          const lama = utama;
          utama = +e.target.value;
          ikut.delete(utama);
          if (lama !== utama) ikut.add(lama);
          $id('grKonfirmasi').value = '';
          render();
        } else if (e.target.classList.contains('gr-ikut')) {
          const v = +e.target.value;
          e.target.checked ? ikut.add(v) : ikut.delete(v);
          render();
        }
      });
      $id('grKonfirmasi').addEventListener('input', cekTombol);
      $id('grPaksa').addEventListener('change', cekTombol);
      $id('grModal').addEventListener('hidden.bs.modal', () => { $id('grKonfirmasi').value = ''; $id('grPaksa').checked = false; });

      $id('grGabung').addEventListener('click', async () => {
        const btn = $id('grGabung'), label = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menggabungkan…';
        try {
          const j = await api('action=gabung', { method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ utama, gabung: [...ikut], konfirmasi: $id('grKonfirmasi').value.trim(), paksa: $id('grPaksa').checked }) });
          if (j.perlu_paksa) {
            // server mendeteksi perbedaan identitas yang belum dikonfirmasi
            $id('grPaksaWrap').classList.remove('d-none');
            Swal.fire({ icon: 'warning', title: 'Perlu dipastikan', html: esc(j.message) + '<ul class="text-start small mt-2">' + j.peringatan.map(p => `<li>${esc(p.rm)} ${esc(p.nama)}: ${esc(p.alasan.join(', '))}</li>`).join('') + '</ul>' });
            return;
          }
          selesai += j.digabung.length;
          $id('kSelesai').textContent = fmt(selesai);
          modal.hide();
          const det = j.digabung.map(d => `<li><span class="gr-rm">${esc(d.rm)}</span> ${esc(d.nama)} — ${fmt(d.kunjungan)} kunjungan</li>`).join('');
          Swal.fire({ icon: 'success', title: 'Berhasil digabung', html: `<div class="small text-start">${esc(j.message)}<ul class="mt-2">${det}</ul>${j.dilengkapi.length ? 'Identitas yang dilengkapi: ' + esc(j.dilengkapi.join(', ').replace(/patient_/g, '')) : ''}</div>` });
          const kw = $id('grCari').value.trim();
          kw.length >= 3 ? cari(kw) : muatKandidat(hal);
        } catch (e) {
          Swal.fire('Gagal menggabungkan', e.message, 'error');
        } finally {
          btn.innerHTML = label;
          cekTombol();
        }
      });

      muatKandidat(1);
    })();
  </script>
</body>

</html>
