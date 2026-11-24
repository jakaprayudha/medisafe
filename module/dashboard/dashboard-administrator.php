<?php
/**
 * Dashboard Superadmin (dimuat module/admin/index.php untuk role superadmin). Data: controller/dashboard/administratorDashboardController.php
 * Perubahan: filter periode jadi tombol cepat; 6 KPI (faskes, kunjungan, pasien, IDSH dokter/pasien, SatuSehat); grafik tren
 * kunjungan & faskes teraktif; "Perlu Perhatian" kini berisi tindakan nyata (faskes pending, eKYC menunggu, import gagal, SatuSehat
 * gagal, RME tidak lengkap) dengan tautan; import & integrasi dibaca dari tabel sungguhan (dulu "belum tersedia" / "BELUM DIMONITOR");
 * semua tautan "#" diganti halaman yang ada; kerangka (skeleton) saat memuat; tampil umur cache; teks abu-abu diganti gelap.
 */
?>
<style>
   .adm { --p: #4f46e5; --t: #0f172a; --m: #334155; --b: #e2e8f0; --g: #15803d; --gs: #dcfce7; --r: #b91c1c; --rs: #fee2e2; --o: #b45309; --os: #fef3c7; --bl: #1d4ed8; --bs: #dbeafe; color: var(--t); }
   .adm *, .adm *::before, .adm *::after { box-sizing: border-box; }
   .adm .bar { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-bottom: 16px; }
   .adm .bar h5 { margin: 0; font-weight: 700; font-size: 1.15rem; }
   .adm .bar small { color: var(--m); display: block; }
   .adm .pil { display: inline-flex; align-items: center; gap: 8px; border-radius: 999px; padding: 4px 12px; font-size: .78rem; font-weight: 700; background: var(--gs); color: var(--g); }
   .adm .pil.warn { background: var(--os); color: var(--o); }
   .adm .pil.err { background: var(--rs); color: var(--r); }
   .adm .pil i { width: 8px; height: 8px; border-radius: 50%; background: currentColor; display: inline-block; }
   .adm .filter { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; background: #fff; border: 1px solid var(--b); border-radius: 14px; padding: 10px 12px; margin-bottom: 16px; }
   .adm .seg { display: inline-flex; background: #f1f5f9; border-radius: 10px; padding: 3px; }
   .adm .seg button { border: 0; background: transparent; padding: 6px 14px; border-radius: 8px; font-weight: 600; font-size: .85rem; color: var(--m); }
   .adm .seg button.on { background: #fff; color: var(--p); box-shadow: 0 1px 3px rgba(15, 23, 42, .15); }
   .adm .filter input[type=date] { width: auto; }
   .adm .filter .info { margin-left: auto; font-size: .78rem; color: var(--m); }
   .adm .kpis { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 12px; margin-bottom: 16px; }
   @media (max-width: 1399.98px) { .adm .kpis { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
   @media (max-width: 575.98px) { .adm .kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
   .adm .kpi { background: #fff; border: 1px solid var(--b); border-radius: 14px; padding: 14px; }
   .adm .kpi .ik { width: 36px; height: 36px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; background: #eef2ff; color: var(--p); margin-bottom: 8px; }
   .adm .kpi .ik.g { background: var(--gs); color: var(--g); } .adm .kpi .ik.b { background: var(--bs); color: var(--bl); } .adm .kpi .ik.o { background: var(--os); color: var(--o); }
   .adm .kpi .lb { font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; color: var(--m); }
   .adm .kpi .vl { font-size: 1.6rem; font-weight: 800; line-height: 1.15; }
   .adm .kpi .sb { font-size: .78rem; color: var(--m); margin-top: 2px; }
   .adm .grid { display: grid; gap: 16px; margin-bottom: 16px; }
   .adm .g2 { grid-template-columns: minmax(0, 7fr) minmax(0, 5fr); }
   .adm .g2b { grid-template-columns: minmax(0, 5fr) minmax(0, 7fr); }
   @media (max-width: 1199.98px) { .adm .g2, .adm .g2b { grid-template-columns: 1fr; } }
   .adm .card2 { background: #fff; border: 1px solid var(--b); border-radius: 14px; padding: 16px; min-width: 0; }
   .adm .card2 h6 { margin: 0; font-weight: 700; }
   .adm .card2 .hd { display: flex; align-items: flex-start; gap: 8px; margin-bottom: 12px; }
   .adm .card2 .hd small { color: var(--m); display: block; }
   .adm .card2 .hd a { margin-left: auto; font-size: .82rem; font-weight: 600; white-space: nowrap; }
   .adm .chart { display: flex; align-items: flex-end; gap: 4px; height: 160px; padding-top: 8px; }
   .adm .chart .col { flex: 1; min-width: 0; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; height: 100%; }
   .adm .chart .bar2 { width: 100%; max-width: 34px; background: var(--p); border-radius: 5px 5px 0 0; min-height: 2px; transition: opacity .15s; }
   .adm .chart .col:hover .bar2 { opacity: .75; }
   .adm .chart .n { font-size: .68rem; font-weight: 700; color: var(--t); }
   .adm .chart .d { font-size: .65rem; color: var(--m); margin-top: 3px; white-space: nowrap; }
   .adm .rank { display: flex; flex-direction: column; gap: 10px; }
   .adm .rank .r { display: grid; grid-template-columns: 22px minmax(0, 1fr) auto; gap: 8px; align-items: center; font-size: .88rem; }
   .adm .rank .no { width: 22px; height: 22px; border-radius: 50%; background: #eef2ff; color: var(--p); font-weight: 700; font-size: .72rem; display: inline-flex; align-items: center; justify-content: center; }
   .adm .rank .nm { font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
   .adm .rank .tr { height: 5px; border-radius: 3px; background: #eef2f7; margin-top: 3px; } .adm .rank .tr i { display: block; height: 100%; border-radius: 3px; background: var(--p); }
   .adm .rank .v { font-weight: 800; }
   .adm .al { display: flex; gap: 12px; align-items: flex-start; border-radius: 12px; padding: 12px; margin-bottom: 8px; background: #f8fafc; border-left: 4px solid #94a3b8; }
   .adm .al.danger { background: #fef2f2; border-color: #dc2626; } .adm .al.warning { background: #fffbeb; border-color: #d97706; } .adm .al.info { background: #eff6ff; border-color: #2563eb; } .adm .al.ok { background: #f0fdf4; border-color: #16a34a; }
   .adm .al .tt { font-weight: 700; } .adm .al .tx { font-size: .85rem; color: var(--m); }
   .adm .al .go { margin-left: auto; white-space: nowrap; }
   .adm .it { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f1f5f9; }
   .adm .it:last-child { border-bottom: 0; }
   .adm .it .ik { width: 36px; height: 36px; border-radius: 10px; background: #f1f5f9; color: var(--m); display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
   .adm .it .nm { font-weight: 700; } .adm .it .ds { font-size: .8rem; color: var(--m); }
   .adm .tag { margin-left: auto; font-size: .68rem; font-weight: 800; border-radius: 6px; padding: 3px 8px; white-space: nowrap; }
   .adm .adm-online { background: var(--gs); color: var(--g); } .adm .adm-warning { background: var(--os); color: var(--o); } .adm .adm-error { background: var(--rs); color: var(--r); }
   .adm .prog { height: 8px; border-radius: 4px; background: #eef2f7; overflow: hidden; margin-top: 6px; } .adm .prog i { display: block; height: 100%; border-radius: 4px; background: var(--g); transition: width .4s; }
   .adm .prog.b i { background: var(--bl); } .adm .prog.p i { background: var(--p); }
   .adm .idsh { margin-bottom: 14px; } .adm .idsh .tp { display: flex; justify-content: space-between; font-weight: 700; } .adm .idsh .ds { font-size: .8rem; color: var(--m); }
   .adm table.tb { width: 100%; font-size: .85rem; } .adm table.tb th { font-size: .7rem; text-transform: uppercase; letter-spacing: .03em; color: var(--m); font-weight: 700; padding: 6px 8px; border-bottom: 1px solid var(--b); } .adm table.tb td { padding: 8px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
   .adm .quick { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 10px; }
   @media (max-width: 1199.98px) { .adm .quick { grid-template-columns: repeat(3, minmax(0, 1fr)); } } @media (max-width: 575.98px) { .adm .quick { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
   .adm .quick a { display: flex; align-items: center; gap: 10px; border: 1px solid var(--b); border-radius: 12px; padding: 12px; color: var(--t); text-decoration: none; transition: border-color .15s, background .15s; }
   .adm .quick a:hover { border-color: var(--p); background: #f5f3ff; }
   .adm .quick .ik { width: 36px; height: 36px; border-radius: 10px; background: #eef2ff; color: var(--p); display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
   .adm .quick b { display: block; font-size: .88rem; } .adm .quick span { font-size: .75rem; color: var(--m); }
   .adm .kosong { text-align: center; color: var(--m); padding: 18px 8px; font-size: .88rem; }
   .adm .sk { background: linear-gradient(90deg, #eef2f7 25%, #f8fafc 50%, #eef2f7 75%); background-size: 200% 100%; animation: admsk 1.2s infinite; border-radius: 6px; display: inline-block; min-width: 40px; height: 1em; color: transparent !important; }
   @keyframes admsk { to { background-position: -200% 0; } }
   @media (prefers-reduced-motion: reduce) { .adm .sk { animation: none; } }
</style>

<div class="adm" id="adm">
   <div class="bar">
      <div class="flex-grow-1">
         <h5>Dashboard Superadmin</h5>
         <small>Pantauan seluruh faskes: kunjungan, integrasi, import, eKYC, dan kualitas data</small>
      </div>
      <span class="pil" id="admSistem"><i></i><span id="admSistemTeks">Memuat…</span></span>
   </div>

   <div class="filter">
      <div class="seg" id="admSeg">
         <button type="button" data-p="today" class="on">Hari ini</button>
         <button type="button" data-p="week">Minggu ini</button>
         <button type="button" data-p="month">Bulan ini</button>
         <button type="button" data-p="custom">Kustom</button>
      </div>
      <input type="date" class="form-control form-control-sm d-none" id="admMulai" max="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>" aria-label="Tanggal mulai">
      <input type="date" class="form-control form-control-sm d-none" id="admSelesai" max="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>" aria-label="Tanggal selesai">
      <button type="button" class="btn btn-sm btn-primary d-none" id="admTerapkan">Terapkan</button>
      <span class="info" id="admInfo"></span>
      <button type="button" class="btn btn-sm btn-outline-dark" id="admMuat" title="Hitung ulang tanpa cache"><iconify-icon icon="solar:refresh-bold"></iconify-icon> Muat ulang</button>
   </div>

   <div class="kpis">
      <div class="kpi"><span class="ik"><iconify-icon icon="solar:hospital-bold" width="20"></iconify-icon></span><div class="lb">Faskes</div><div class="vl sk" id="kFaskes">0000</div><div class="sb" id="kFaskesSub">&nbsp;</div></div>
      <div class="kpi"><span class="ik g"><iconify-icon icon="solar:stethoscope-bold" width="20"></iconify-icon></span><div class="lb">Kunjungan</div><div class="vl sk" id="kKunj">0000</div><div class="sb" id="kKunjSub">&nbsp;</div></div>
      <div class="kpi"><span class="ik b"><iconify-icon icon="solar:users-group-rounded-bold" width="20"></iconify-icon></span><div class="lb">Data Pasien</div><div class="vl sk" id="kPasien">0000</div><div class="sb">Terdata di platform</div></div>
      <div class="kpi"><span class="ik g"><iconify-icon icon="solar:user-id-bold" width="20"></iconify-icon></span><div class="lb">IDSH Dokter</div><div class="vl sk" id="kDokter">0000</div><div class="sb" id="kDokterSub">&nbsp;</div></div>
      <div class="kpi"><span class="ik b"><iconify-icon icon="solar:user-check-bold" width="20"></iconify-icon></span><div class="lb">IDSH Pasien</div><div class="vl sk" id="kIdshP">0000</div><div class="sb" id="kIdshPSub">&nbsp;</div></div>
      <div class="kpi"><span class="ik o"><iconify-icon icon="solar:link-bold" width="20"></iconify-icon></span><div class="lb">SatuSehat</div><div class="vl sk" id="kSS">0000</div><div class="sb" id="kSSSub">&nbsp;</div></div>
   </div>

   <div class="grid g2">
      <div class="card2">
         <div class="hd"><div><h6>Tren Kunjungan</h6><small id="admTrenSub">Seluruh faskes pada periode terpilih</small></div></div>
         <div id="admTren"><div class="kosong">Memuat…</div></div>
      </div>
      <div class="card2">
         <div class="hd"><div><h6>Faskes Teraktif</h6><small>Kunjungan terbanyak pada periode</small></div><a href="module/administrator/monitoring-visit">Monitoring</a></div>
         <div id="admTop"><div class="kosong">Memuat…</div></div>
      </div>
   </div>

   <div class="grid g2">
      <div class="card2">
         <div class="hd"><div><h6>Perlu Perhatian</h6><small>Hal yang membutuhkan tindak lanjut superadmin</small></div></div>
         <div id="admAlert"><div class="kosong">Memuat…</div></div>
      </div>
      <div class="card2">
         <div class="hd"><div><h6>Kualitas Data</h6><small>IDSH & kelengkapan RME</small></div></div>
         <div id="admMutu"><div class="kosong">Memuat…</div></div>
      </div>
   </div>

   <div class="grid g2b">
      <div class="card2">
         <div class="hd"><div><h6>Integrasi & Layanan</h6><small>Status dihitung dari data sistem</small></div></div>
         <div id="admInteg"><div class="kosong">Memuat…</div></div>
      </div>
      <div class="card2">
         <div class="hd"><div><h6>Import Terbaru</h6><small>5 proses import data terakhir</small></div><a href="module/administrator/data-import">Kelola</a></div>
         <div id="admImport"><div class="kosong">Memuat…</div></div>
      </div>
   </div>

   <div class="grid g2b">
      <div class="card2">
         <div class="hd"><div><h6>Faskes Terbaru</h6><small>Terakhir didaftarkan</small></div><a href="module/administrator/master-faskes">Lihat semua</a></div>
         <div id="admFaskes"><div class="kosong">Memuat…</div></div>
      </div>
      <div class="card2">
         <div class="hd"><div><h6>Akses Cepat</h6><small>Menu pengelolaan platform</small></div></div>
         <div class="quick">
            <a href="module/administrator/master-faskes"><span class="ik"><iconify-icon icon="solar:hospital-bold" width="20"></iconify-icon></span><div><b>Data Faskes</b><span>Kelola faskes & user</span></div></a>
            <a href="module/administrator/data-import"><span class="ik"><iconify-icon icon="solar:cloud-upload-bold" width="20"></iconify-icon></span><div><b>Data Import</b><span>Import data</span></div></a>
            <a href="module/administrator/master-satu-sehat"><span class="ik"><iconify-icon icon="solar:link-bold" width="20"></iconify-icon></span><div><b>Satu Sehat</b><span>Integrasi API</span></div></a>
            <a href="module/administrator/master-lab"><span class="ik"><iconify-icon icon="solar:test-tube-bold" width="20"></iconify-icon></span><div><b>Laboratorium</b><span>Master lab</span></div></a>
            <a href="module/administrator/monitoring-visit"><span class="ik"><iconify-icon icon="solar:monitor-bold" width="20"></iconify-icon></span><div><b>Monitoring RME</b><span>Kualitas data</span></div></a>
            <a href="module/administrator/ekyc_verify"><span class="ik"><iconify-icon icon="solar:shield-user-bold" width="20"></iconify-icon></span><div><b>Verifikasi eKYC</b><span>KTP & wajah</span></div></a>
            <a href="module/administrator/validasi-kontrak"><span class="ik"><iconify-icon icon="solar:document-add-bold" width="20"></iconify-icon></span><div><b>Kontrak</b><span>Validasi kontrak</span></div></a>
            <a href="module/administrator/master-log"><span class="ik"><iconify-icon icon="solar:history-bold" width="20"></iconify-icon></span><div><b>Log Update</b><span>Catatan rilis</span></div></a>
            <a href="module/administrator/doctor-idsh"><span class="ik"><iconify-icon icon="solar:user-id-bold" width="20"></iconify-icon></span><div><b>IDSH Dokter</b><span>Identitas dokter</span></div></a>
         </div>
      </div>
   </div>
</div>

<script>
   (function() {
      'use strict';
      const $ = id => document.getElementById(id);
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const num = n => new Intl.NumberFormat('id-ID').format(Number(n || 0));
      const iso = d => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
      const BLN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      const tglPendek = s => { const m = /^\d{4}-(\d{2})-(\d{2})/.exec(s || ''); return m ? `${+m[2]} ${BLN[m[1] - 1]}` : ''; };
      const tglPanjang = s => { const m = /^(\d{4})-(\d{2})-(\d{2})/.exec(s || ''); return m ? `${+m[3]} ${BLN[m[2] - 1]} ${m[1]}` : '-'; };
      const aman = u => /^module\/administrator\/[a-z0-9_-]+$/i.test(u || '') ? u : null;   // tautan hanya ke halaman administrator
      let periode = 'today', sedangMuat = false, req = 0;

      function setKpi(id, teks) { const e = $(id); e.classList.remove('sk'); e.textContent = teks; }

      /* ===== filter ===== */
      function tanggalPeriode(p) {
         const t = new Date(), a = new Date(t);
         if (p === 'week') a.setDate(t.getDate() - ((t.getDay() + 6) % 7));
         if (p === 'month') a.setDate(1);
         return [iso(a), iso(t)];
      }
      function pilih(p) {
         periode = p;
         document.querySelectorAll('#admSeg button').forEach(b => b.classList.toggle('on', b.dataset.p === p));
         const kustom = p === 'custom';
         ['admMulai', 'admSelesai', 'admTerapkan'].forEach(i => $(i).classList.toggle('d-none', !kustom));
         if (!kustom) { [$('admMulai').value, $('admSelesai').value] = tanggalPeriode(p); muat(false); }
      }
      $('admSeg').addEventListener('click', e => { const b = e.target.closest('button[data-p]'); if (b) pilih(b.dataset.p); });
      $('admTerapkan').addEventListener('click', () => muat(false));
      $('admMuat').addEventListener('click', () => muat(true));

      /* ===== muat ===== */
      async function muat(segar) {
         const mulai = $('admMulai').value, selesai = $('admSelesai').value;
         if (!mulai || !selesai) return alert('Tanggal mulai dan selesai wajib diisi.');
         if (mulai > selesai) return alert('Tanggal mulai tidak boleh setelah tanggal selesai.');
         const id = ++req;   // hanya respons terbaru yang dipakai (klik cepat berganti periode)
         $('admMuat').disabled = true;
         $('admInfo').textContent = 'Memuat…';
         try {
            const q = new URLSearchParams({ action: 'dashboard', periode, tanggal_mulai: mulai, tanggal_selesai: selesai });
            if (segar) q.set('refresh', '1');
            const r = await fetch('controller/dashboard/administratorDashboardController.php?' + q, { cache: 'no-store', headers: { Accept: 'application/json' } });
            const d = await r.json().catch(() => null);
            if (!d || !d.status) throw new Error((d && d.message) || 'Gagal mengambil data dashboard.');
            if (id !== req) return;
            render(d);
            const c = d.cache || {};
            $('admInfo').textContent = `${tglPanjang(d.period.start)}${d.period.start !== d.period.end ? ' – ' + tglPanjang(d.period.end) : ''} · ${c.global_detik ? 'cache ' + Math.ceil(c.global_detik / 60) + ' mnt' : 'data terbaru'} · ${c.waktu_ms} ms`;
         } catch (e) {
            if (id !== req) return;
            $('admInfo').textContent = '';
            $('admSistem').classList.remove('warn');
            $('admSistem').classList.add('err');
            $('admSistemTeks').textContent = 'Gagal memuat';
            $('admAlert').innerHTML = `<div class="al danger"><iconify-icon icon="solar:danger-triangle-bold" width="20"></iconify-icon><div><div class="tt">Dashboard gagal dimuat</div><div class="tx">${esc(e.message)}</div></div><button type="button" class="btn btn-sm btn-outline-dark go" id="admUlang">Coba lagi</button></div>`;
            const u = $('admUlang'); if (u) u.onclick = () => muat(true);
         } finally {
            if (id === req) $('admMuat').disabled = false;
         }
      }

      /* ===== render ===== */
      function render(d) {
         const k = d.kpi, f = k.faskes;
         setKpi('kFaskes', num(f.total));
         $('kFaskesSub').textContent = `${num(f.aktif)} aktif · ${num(f.pending)} pending`;
         setKpi('kKunj', num(k.kunjungan.total));
         $('kKunjSub').textContent = `${num(k.kunjungan.faskes_aktif)} faskes aktif`;
         setKpi('kPasien', num(k.pasien.total));
         setKpi('kDokter', k.dokter.persentase + '%');
         $('kDokterSub').textContent = `${num(k.dokter.terverifikasi)} dari ${num(k.dokter.total)} dokter`;
         setKpi('kIdshP', d.idsh.pasien.persentase + '%');
         $('kIdshPSub').textContent = `${num(d.idsh.pasien.terverifikasi)} dari ${num(d.idsh.pasien.total)} pasien`;
         setKpi('kSS', k.satusehat.total ? k.satusehat.persentase + '%' : '-');
         $('kSSSub').textContent = k.satusehat.total ? `${num(k.satusehat.sukses)} sukses · ${num(k.satusehat.gagal)} gagal` : 'Tidak ada pengiriman';

         // merah hanya bila database bermasalah; layanan lain yang gagal/antre = oranye "perlu dicek"
         const db = d.integration.find(i => i.name === 'Database'), cek = d.integration.some(i => i.status === 'error' || i.status === 'warning');
         $('admSistem').classList.remove('err', 'warn');
         $('admSistem').classList.add(...(db && db.status === 'error' ? ['err'] : cek ? ['warn'] : []));
         $('admSistemTeks').textContent = db && db.status === 'error' ? 'Database bermasalah' : cek ? 'Ada yang perlu dicek' : 'Sistem berjalan normal';

         renderTren(d.kunjungan.per_hari, d.period);
         renderTop(d.kunjungan.top);
         renderAlert(d.alerts);
         renderMutu(d);
         renderInteg(d.integration);
         renderImport(d.import.jobs);
         renderFaskes(d.faskes);
      }

      function renderTren(hari, p) {
         const map = {}; (hari || []).forEach(h => map[h.d] = +h.n);
         const seri = [], a = new Date(p.start + 'T00:00:00'), b = new Date(p.end + 'T00:00:00');
         for (let t = new Date(a); t <= b && seri.length < 62; t.setDate(t.getDate() + 1)) seri.push([iso(t), map[iso(t)] || 0]);
         if (!seri.some(s => s[1])) { $('admTren').innerHTML = '<div class="kosong">Belum ada kunjungan pada periode ini.</div>'; return; }
         const maks = Math.max(...seri.map(s => s[1])), langka = seri.length > 16 ? Math.ceil(seri.length / 10) : 1;
         $('admTren').innerHTML = `<div class="chart" role="img" aria-label="Grafik kunjungan per hari">${seri.map(([t, n], i) =>
            `<div class="col" title="${esc(tglPanjang(t))}: ${num(n)} kunjungan"><span class="n">${seri.length <= 16 || n === maks ? num(n) : ''}</span><div class="bar2" style="height:${Math.max(2, n / maks * 120)}px"></div><span class="d">${i % langka === 0 ? esc(tglPendek(t)) : '&nbsp;'}</span></div>`).join('')}</div>`;
      }
      function renderTop(top) {
         if (!top || !top.length) { $('admTop').innerHTML = '<div class="kosong">Belum ada kunjungan pada periode ini.</div>'; return; }
         const maks = +top[0].n;
         $('admTop').innerHTML = `<div class="rank">${top.map((r, i) => `<div class="r"><span class="no">${i + 1}</span><div style="min-width:0"><div class="nm" title="${esc(r.nama)}">${esc(r.nama)}</div><div class="tr"><i style="width:${(r.n / maks * 100).toFixed(0)}%"></i></div></div><span class="v">${num(r.n)}</span></div>`).join('')}</div>`;
      }
      function renderAlert(al) {
         $('admAlert').innerHTML = al.map(a => {
            const u = aman(a.href);
            return `<div class="al ${esc(a.type)}"><iconify-icon icon="${esc(a.icon)}" width="22"></iconify-icon><div style="min-width:0"><div class="tt">${esc(a.title)}</div><div class="tx">${esc(a.text)}</div></div>${u ? `<a class="btn btn-sm btn-outline-dark go" href="${esc(u)}">${esc(a.aksi)}</a>` : ''}</div>`;
         }).join('');
      }
      function renderMutu(d) {
         const bar = (judul, x, cls) => `<div class="idsh"><div class="tp"><span>${judul}</span><span>${x.persentase}%</span></div><div class="ds">${num(x.terverifikasi ?? x.lengkap)} dari ${num(x.total)}</div><div class="prog ${cls}"><i style="width:${Math.min(100, x.persentase)}%"></i></div></div>`;
         $('admMutu').innerHTML = bar('IDSH Dokter', d.idsh.dokter, '') + bar('IDSH Pasien', d.idsh.pasien, 'b')
            + bar('Kelengkapan RME (periode)', d.rme, 'p') + `<div class="ds" style="font-size:.78rem;color:var(--m)">RME lengkap = anamnesa, diagnosa & tindakan terisi.</div>`;
      }
      function renderInteg(items) {
         $('admInteg').innerHTML = items.map(i => {
            const u = aman(i.href), isi = `<span class="ik"><iconify-icon icon="${esc(i.icon)}" width="20"></iconify-icon></span><div style="min-width:0"><div class="nm">${esc(i.name)}</div><div class="ds">${esc(i.description)}${i.info ? ' · ' + esc(i.info) : ''}</div></div><span class="tag ${esc(i.status_class)}">${esc(i.status_label)}</span>`;
            return u ? `<a class="it text-reset text-decoration-none" href="${esc(u)}">${isi}</a>` : `<div class="it">${isi}</div>`;
         }).join('');
      }
      function renderImport(jobs) {
         if (!jobs || !jobs.length) { $('admImport').innerHTML = '<div class="kosong">Belum ada proses import.</div>'; return; }
         const st = { pending: ['MENUNGGU', 'adm-warning'], processing: ['DIPROSES', 'adm-warning'], done: ['SELESAI', 'adm-online'], failed: ['GAGAL', 'adm-error'] };
         $('admImport').innerHTML = `<div class="table-responsive"><table class="tb"><thead><tr><th>Jenis</th><th>Faskes</th><th>Progres</th><th>Status</th><th>Waktu</th></tr></thead><tbody>${jobs.map(j => {
            const s = st[j.status] || [j.status, 'adm-warning'];
            return `<tr><td class="fw-semibold">${esc(j.type)}</td><td>#${esc(j.id_faskes)}</td><td>${num(j.processed_rows)}/${num(j.total_rows)}${+j.error_count ? ` <span style="color:var(--r)">(${num(j.error_count)} error)</span>` : ''}</td><td><span class="tag ${s[1]}" style="margin:0">${s[0]}</span></td><td class="text-nowrap">${esc(tglPanjang(j.created_at))}</td></tr>`;
         }).join('')}</tbody></table></div>`;
      }
      function renderFaskes(list) {
         if (!list || !list.length) { $('admFaskes').innerHTML = '<div class="kosong">Belum ada faskes.</div>'; return; }
         $('admFaskes').innerHTML = list.map(f => {
            const aktif = +f.faskes_status === 1, lokasi = [f.faskes_district, f.faskes_city].filter(Boolean).join(', ');
            return `<div class="it"><span class="ik"><iconify-icon icon="solar:hospital-bold" width="20"></iconify-icon></span><div style="min-width:0"><div class="nm text-truncate">${esc(f.clinic_name || 'Faskes #' + f.id_faskes)}</div><div class="ds">${esc(lokasi || '-')} · ${esc(tglPanjang(f.created_at))}</div></div><span class="tag ${aktif ? 'adm-online' : 'adm-warning'}">${aktif ? 'AKTIF' : 'PENDING'}</span></div>`;
         }).join('');
      }

      $('admMulai').value = $('admSelesai').value = iso(new Date());
      muat(false);
   })();
</script>
