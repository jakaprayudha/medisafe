<?php
$title = 'Integrasi Dinkes';
require '../../controller/view.php';
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <?php require 'sidebar.php'; ?>
    <div class="body-wrapper">
      <?php require 'navbar.php'; ?>
      <div class="body-wrapper-inner">
        <div class="container-fluid">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
            <div>
              <h4 class="fw-semibold mb-1">Integrasi Dinkes Deli Serdang</h4>
              <div class="text-muted small">Kunjungan pasien &lt; 5 tahun dikirim sebagai data balita, selebihnya sebagai pemeriksaan pasien dewasa. Z-score &amp; stunting dihitung oleh Dinkes.</div>
            </div>
            <a class="btn btn-outline-primary" href="module/admin/monitoring-dinkes"><i class="ti ti-activity me-1"></i>Monitoring pengiriman</a>
          </div>

          <div class="card">
            <div class="card-body">
              <form id="dkForm" class="row g-3" autocomplete="off">
                <div class="col-12 col-lg-8">
                  <div class="small" id="dkTokenInfo"></div>
                  <div class="form-text">Token Dinkes satu untuk semua faskes dan dipasang admin sistem di server (DINKES_TOKEN di .env). Tidak ditampilkan maupun dicatat di log.</div>
                </div>
                <div class="col-12 col-lg-8">
                  <label class="form-label" for="dkPuskes">Puskesmas default</label>
                  <div class="input-group">
                    <select class="form-select" id="dkPuskes"><option value="">— Muat daftar puskesmas —</option></select>
                    <button class="btn btn-outline-secondary" type="button" id="dkMuat">Muat dari Dinkes</button>
                  </div>
                  <div class="form-text">Dipakai untuk semua pengiriman; bisa diganti per data balita di form "Dinkes Balita".</div>
                </div>
                <div class="col-12">
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="dkAktif">
                    <label class="form-check-label" for="dkAktif">Aktifkan pengiriman otomatis setelah kunjungan disimpan</label>
                  </div>
                </div>
                <div class="col-12 d-flex gap-2">
                  <button class="btn btn-primary" type="submit"><i class="ti ti-device-floppy me-1"></i>Simpan</button>
                  <button class="btn btn-outline-primary" type="button" id="dkTes"><i class="ti ti-plug me-1"></i>Tes koneksi</button>
                </div>
                <div class="col-12 small text-muted" id="dkInfo"></div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php require 'library.php'; ?>
  <script>
    (function() {
      'use strict';
      const API = 'controller/master/settingDinkesController';
      const $ = id => document.getElementById(id);
      const toast = (title, icon = 'success') => Swal.fire({ toast: true, position: 'top-end', icon, title, showConfirmButton: false, timer: 2600 });
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      async function api(url, opt) {
        const r = await fetch(url, opt);
        const j = await r.json().catch(() => ({ status: 'error', message: 'Respon server tidak valid.' }));
        if (!r.ok || j.status !== 'success') throw new Error(j.message || 'Terjadi kesalahan.');
        return j;
      }
      const post = obj => api(API, { method: 'POST', body: new URLSearchParams(obj) });
      let puskesTersimpan = '';

      function isiPuskes(list) {
        $('dkPuskes').innerHTML = '<option value="">— Pilih puskesmas —</option>' +
          list.map(p => `<option value="${esc(p.id)}">${esc(p.nama)}</option>`).join('');
        $('dkPuskes').value = puskesTersimpan;
      }
      async function muatPuskes(diam) {
        try { isiPuskes((await api(API + '?aksi=puskesmas')).data); }
        catch (e) { if (!diam) toast(e.message, 'error'); }
      }

      api(API).then(res => {
        const ok = !!res.token_server;
        $('dkTokenInfo').innerHTML = ok ? '<span class="text-success"><i class="ti ti-circle-check me-1"></i>Token Dinkes terpasang di server</span>' : '<span class="text-danger"><i class="ti ti-alert-triangle me-1"></i>Token Dinkes belum dipasang di server — hubungi admin sistem</span>';
        if (ok) muatPuskes(true);
        const d = res.data;
        if (!d) return;
        puskesTersimpan = d.puskes_id ? String(d.puskes_id) : '';
        $('dkAktif').checked = !!+d.aktif;
        if (d.puskes_id) $('dkPuskes').innerHTML = `<option value="${esc(d.puskes_id)}">Puskesmas ID ${esc(d.puskes_id)}</option>`, $('dkPuskes').value = puskesTersimpan;
        $('dkInfo').textContent = d.diubah_oleh ? `Terakhir diubah oleh ${d.diubah_oleh}${d.diubah_pada ? ' pada ' + d.diubah_pada : ''}.` : '';
      }).catch(e => toast(e.message, 'error'));

      $('dkMuat').addEventListener('click', () => muatPuskes(false));
      $('dkTes').addEventListener('click', async () => {
        try { toast((await post({ aksi: 'tes' })).message); } catch (e) { toast(e.message, 'error'); }
      });
      $('dkForm').addEventListener('submit', async ev => {
        ev.preventDefault();
        try {
          const res = await post({ aksi: 'simpan', puskes_id: $('dkPuskes').value, aktif: $('dkAktif').checked ? 1 : 0 });
          toast(res.message);
          setTimeout(() => location.reload(), 900);
        } catch (e) { toast(e.message, 'error'); }
      });
    })();
  </script>
</body>

</html>
