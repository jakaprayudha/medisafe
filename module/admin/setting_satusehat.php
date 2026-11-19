<?php
$title = 'Integrasi Satu Sehat';
require '../../controller/view.php';
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .min-w-0 {
      min-width: 0;
    }

    .ss-stat {
      border: 1px solid #e5eaef;
      border-radius: 12px;
      background: #fff;
      padding: 14px 16px;
      height: 100%;
    }

    .ss-icon {
      width: 40px;
      height: 40px;
      border-radius: 10px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 1.05rem;
      flex-shrink: 0;
    }

    .ss-val {
      font-size: 1.1rem;
      font-weight: 700;
      line-height: 1.25;
      color: #2a3547;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .ss-label {
      font-size: .75rem;
      color: #6c757d;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .ss-tabs {
      gap: 6px;
      flex-wrap: wrap;
    }

    .ss-tabs .nav-link {
      border-radius: 10px;
      font-weight: 600;
      font-size: .85rem;
      color: #5a6a85;
      background: #f2f5f9;
      padding: .5rem .9rem;
    }

    .ss-tabs .nav-link.active {
      background: #5d87ff;
      color: #fff;
    }

    .ss-card {
      border: 1px solid #e5eaef;
      border-radius: 12px;
      padding: 18px;
      height: 100%;
      background: #fff;
    }

    .ss-sec {
      font-size: .72rem;
      font-weight: 700;
      color: #5a6a85;
      text-transform: uppercase;
      letter-spacing: .4px;
      margin-bottom: .75rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .kv {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 14px;
      padding: 9px 0;
      border-bottom: 1px dashed #e5eaef;
      font-size: .85rem;
    }

    .kv:last-child {
      border-bottom: 0;
    }

    .kv .k {
      color: #7c8fac;
      white-space: nowrap;
    }

    .kv .v {
      font-weight: 600;
      color: #2a3547;
      text-align: right;
      word-break: break-word;
    }

    .kv .v code {
      color: #2a3547;
      background: #f6f9fc;
      padding: 2px 6px;
      border-radius: 5px;
      font-size: .8rem;
      word-break: break-all;
    }

    .ss-badge {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      font-size: .7rem;
      font-weight: 600;
      padding: .25em .6em;
      border-radius: 6px;
      white-space: nowrap;
    }

    .b-ok {
      color: #13a89e;
      background: #e6fffa;
    }

    .b-warn {
      color: #c2410c;
      background: #fff4e5;
    }

    .b-err {
      color: #fa5a5a;
      background: #fdede8;
    }

    .b-mute {
      color: #7c8fac;
      background: #f2f5f9;
    }

    .b-info {
      color: #5d87ff;
      background: #ecf2ff;
    }

    .ss-table {
      width: 100% !important;
    }

    .ss-table thead th {
      font-size: .74rem;
      font-weight: 600;
      color: #5a6a85;
      text-transform: uppercase;
      letter-spacing: .3px;
      background: #f6f9fc;
      border-bottom: 0;
      white-space: nowrap;
    }

    .ss-table td {
      font-size: .85rem;
      vertical-align: middle;
    }

    .ss-table tr.off td:not(.keep) {
      opacity: .5;
    }

    .ss-ava {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      flex-shrink: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: .75rem;
      font-weight: 700;
    }

    .ss-name {
      font-weight: 600;
      color: #2a3547;
      max-width: 260px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .ss-sub {
      font-size: .74rem;
      color: #7c8fac;
      white-space: nowrap;
    }

    .ss-mono {
      font-family: monospace;
      font-size: .82rem;
    }

    .ss-act {
      white-space: nowrap;
    }

    .ss-act .btn {
      height: 32px;
      min-width: 32px;
      padding: 0 8px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 5px;
      border-radius: 8px;
      font-size: .78rem;
    }

    .ss-chips .btn {
      border-radius: 20px;
      font-size: .78rem;
      padding: .28rem .8rem;
    }

    .ss-search {
      width: 230px;
    }

    @media (max-width: 767.98px) {

      .ss-tools,
      .ss-search {
        width: 100%;
      }

      .ss-search {
        flex: 1;
      }
    }

    .ss-empty {
      text-align: center;
      color: #7c8fac;
      padding: 40px 10px;
    }

    .ss-empty i {
      font-size: 1.8rem;
      display: block;
      margin-bottom: 8px;
      opacity: .6;
    }

    .nik-input {
      font-family: monospace;
      letter-spacing: 2px;
      font-size: 1.05rem;
    }
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
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
            <div>
              <h4 class="fw-semibold mb-1">Integrasi Satu Sehat</h4>
              <div class="text-muted small">Konfigurasi faskes di platform SATUSEHAT Kemenkes dan ID Satu Sehat (IHS) tenaga medis untuk pengiriman data kunjungan.</div>
            </div>
            <button class="btn btn-outline-primary" id="btnTes"><i class="fas fa-plug me-1"></i> Tes Koneksi</button>
          </div>

          <!-- STATUS -->
          <div class="row g-3 mb-3">
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="ss-stat d-flex gap-3 align-items-center">
                <span class="ss-icon bg-primary-subtle text-primary" id="icKred"><i class="fas fa-key"></i></span>
                <div class="min-w-0">
                  <div class="ss-val" id="stKred">-</div>
                  <div class="ss-label" id="stKredSub">Kredensial</div>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="ss-stat d-flex gap-3 align-items-center">
                <span class="ss-icon bg-success-subtle text-success" id="icToken"><i class="fas fa-shield-alt"></i></span>
                <div class="min-w-0">
                  <div class="ss-val" id="stToken">-</div>
                  <div class="ss-label" id="stTokenSub">Token akses</div>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="ss-stat d-flex gap-3 align-items-center">
                <span class="ss-icon bg-info-subtle text-info" id="icLok"><i class="fas fa-map-marker-alt"></i></span>
                <div class="min-w-0">
                  <div class="ss-val" id="stLok">-</div>
                  <div class="ss-label" id="stLokSub">Lokasi faskes</div>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="ss-stat d-flex gap-3 align-items-center">
                <span class="ss-icon bg-warning-subtle text-warning"><i class="fas fa-user-md"></i></span>
                <div class="min-w-0">
                  <div class="ss-val" id="stDok">-</div>
                  <div class="ss-label" id="stDokSub">Dokter punya ID Satu Sehat</div>
                </div>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-body p-4">
              <ul class="nav ss-tabs mb-4" role="tablist">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-konfigurasi" type="button"><i class="fas fa-hospital me-1"></i> Konfigurasi Faskes</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-dokter" type="button"><i class="fas fa-id-card me-1"></i> ID Satu Sehat Dokter <span class="badge bg-warning text-dark ms-1 d-none" id="nPerlu"></span></button></li>
              </ul>

              <div class="tab-content">
                <!-- ===== KONFIGURASI ===== -->
                <div class="tab-pane fade show active" id="tab-konfigurasi">
                  <div class="row g-3" id="cfgBox">
                    <div class="col-12">
                      <div class="ss-empty"><span class="spinner-border spinner-border-sm"></span></div>
                    </div>
                  </div>
                </div>

                <!-- ===== DOKTER ===== -->
                <div class="tab-pane fade" id="tab-dokter">
                  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <div class="ss-chips d-flex flex-wrap gap-2" id="dkChips">
                      <button class="btn btn-primary" data-f="">Semua <span class="badge bg-white text-primary ms-1" data-n=""></span></button>
                      <button class="btn btn-light" data-f="ok">Siap <span class="badge bg-white text-dark ms-1" data-n="ok"></span></button>
                      <button class="btn btn-light" data-f="cari">Belum ada ID <span class="badge bg-white text-dark ms-1" data-n="cari"></span></button>
                      <button class="btn btn-light" data-f="nik">NIK belum valid <span class="badge bg-white text-dark ms-1" data-n="nik"></span></button>
                    </div>
                    <div class="d-flex flex-wrap gap-2 align-items-center ss-tools">
                      <div class="input-group input-group-sm ss-search">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        <input type="search" class="form-control" id="dkSearch" placeholder="Cari nama / NIK / ID" autocomplete="off">
                      </div>
                      <button class="btn btn-primary btn-sm" id="btnCariSemua"><i class="fas fa-search-plus me-1"></i> Cari ID yang belum ada</button>
                    </div>
                  </div>
                  <div class="table-responsive">
                    <table class="table align-middle mb-0 ss-table">
                      <thead>
                        <tr>
                          <th>Dokter</th>
                          <th>NIK</th>
                          <th>ID Satu Sehat (IHS)</th>
                          <th class="text-end">Aksi</th>
                        </tr>
                      </thead>
                      <tbody id="dkBody">
                        <tr>
                          <td colspan="4" class="ss-empty"><span class="spinner-border spinner-border-sm"></span></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                  <div class="small text-muted mt-3"><i class="fas fa-info-circle me-1"></i>ID Satu Sehat dicari otomatis dari NIK ke data SISDMK Kemenkes. Dokter tanpa ID Satu Sehat tidak bisa dicantumkan saat data kunjungan dikirim ke Satu Sehat.</div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <!-- MODAL NIK -->
  <div class="modal fade" id="mdNik" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <form class="modal-content" id="nikForm" autocomplete="off" novalidate>
        <div class="modal-header">
          <div>
            <h5 class="modal-title mb-0">NIK Dokter</h5>
            <div class="small text-muted" id="nikNama">-</div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="nik_id">
          <label class="form-label required" for="nik_val">NIK (sesuai KTP)</label>
          <input type="text" class="form-control nik-input" id="nik_val" inputmode="numeric" maxlength="16" placeholder="16 digit angka">
          <div class="d-flex justify-content-between mt-1">
            <small class="text-muted" id="nikHint">Hanya angka, 16 digit.</small>
            <small class="fw-semibold" id="nikCount">0/16</small>
          </div>
          <div class="alert alert-warning small py-2 mt-3 mb-0 d-none" id="nikWarn">
            <i class="fas fa-exclamation-triangle me-1"></i>NIK berubah: ID Satu Sehat yang lama akan dihapus dan dicari ulang dari NIK baru.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary" id="btnNikSimpan" disabled><i class="fas fa-save me-1"></i> Simpan &amp; Cari ID</button>
        </div>
      </form>
    </div>
  </div>

  <?php require 'library.php'; ?>

  <script>
    (function() {
      const API = 'controller/master/settingSatusehatController.php';
      let DOK = [];
      let fDok = '';

      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
      } [c]));
      const modal = id => bootstrap.Modal.getOrCreateInstance(document.getElementById(id));
      const inisial = n => {
        const w = String(n || '?').replace(/^(drg|dr|bd)\.?\s*/i, '').replace(/,.*$/, '').replace(/[^A-Za-z\s]/g, ' ').trim().split(/\s+/);
        return ((w[0] || '?')[0] + (w[1] ? w[1][0] : '')).toUpperCase();
      };
      const jam = s => {
        const d = new Date(String(s || '').replace(' ', 'T'));
        return isNaN(d) ? '' : d.toLocaleString('id-ID', {
          day: 'numeric',
          month: 'short',
          hour: '2-digit',
          minute: '2-digit'
        });
      };
      const masker = n => n && n.length === 16 ? n.slice(0, 6) + '••••••' + n.slice(-4) : (n || '');
      const statusDok = d => d.idsh ? 'ok' : (d.nik_valid ? 'cari' : 'nik');

      function api(aksi, data, method) {
        const opt = method === 'GET' ? {
          url: API + (aksi ? '?aksi=' + aksi : ''),
          type: 'GET'
        } : {
          url: API,
          type: 'POST',
          data: Object.assign({
            aksi
          }, data || {})
        };
        return $.ajax(Object.assign(opt, {
          dataType: 'json'
        })).then(res => res && res.status === 'success' ? res : $.Deferred().reject(res && res.message ? res.message : 'Gagal'),
          xhr => $.Deferred().reject((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal terhubung ke server.'));
      }
      const gagal = m => Swal.fire('Gagal', typeof m === 'string' ? m : 'Terjadi kesalahan.', 'error');

      // ================= KONFIGURASI =================
      function kv(k, v, code) {
        return `<div class="kv"><span class="k">${k}</span><span class="v">${v ? (code ? `<code>${esc(v)}</code>` : esc(v)) : '<span class="text-muted fw-normal">Belum diisi</span>'}</span></div>`;
      }

      function muatConfig() {
        return api('', null, 'GET').then(res => {
          const c = res.data;
          if (!c) {
            $('#stKred').text('Belum diatur');
            $('#stKredSub').text('Hubungi tim IT');
            $('#icKred').attr('class', 'ss-icon bg-danger-subtle text-danger');
            $('#stToken').text('-');
            $('#stLok').text('-');
            $('#btnTes').prop('disabled', true);
            $('#cfgBox').html('<div class="col-12"><div class="ss-card ss-empty"><i class="fas fa-exclamation-circle"></i>Faskes ini belum terdaftar di Satu Sehat. Hubungi tim IT untuk pendaftaran kredensial.</div></div>');
            return;
          }
          $('#stKred').text(c.lengkap ? 'Lengkap' : 'Belum lengkap');
          $('#stKredSub').text('Organization ID ' + (c.organization_id || '-'));
          if (!c.lengkap) $('#icKred').attr('class', 'ss-icon bg-danger-subtle text-danger');
          $('#stToken').text(c.token_aktif ? 'Aktif' : 'Kedaluwarsa');
          $('#stTokenSub').text(c.token_aktif ? 'Berlaku s/d ' + jam(c.token_sampai) : 'Dibuat ulang otomatis saat dipakai');
          if (!c.token_aktif) $('#icToken').attr('class', 'ss-icon bg-secondary-subtle text-secondary');
          $('#stLok').text(c.location_id ? 'Terdaftar' : 'Belum terdaftar');
          $('#stLokSub').text(c.location_id ? (c.address || 'Lokasi faskes') : 'Location ID belum ada');
          if (!c.location_id) $('#icLok').attr('class', 'ss-icon bg-danger-subtle text-danger');

          const ada = c.latitude && c.longitude;
          const peta = ada ? `https://www.google.com/maps?q=${encodeURIComponent(c.latitude + ',' + c.longitude)}` : '';
          $('#cfgBox').html(`
            <div class="col-lg-6"><div class="ss-card">
              <div class="ss-sec">Kredensial API <span class="ss-badge b-mute"><i class="fas fa-lock"></i>Dikelola tim IT</span></div>
              ${kv('Organization ID', c.organization_id, 1)}${kv('Client ID', c.client_id, 1)}${kv('Client Secret', c.client_secret, 1)}
              ${kv('Token akses', c.token_aktif ? 'Aktif s/d ' + jam(c.token_sampai) : 'Kedaluwarsa (dibuat ulang otomatis)')}
              ${c.diubah_oleh ? kv('Diubah oleh', c.diubah_oleh) : ''}
              <div class="small text-muted mt-2"><i class="fas fa-info-circle me-1"></i>Client secret disamarkan. Hubungi tim IT bila kredensial perlu diganti.</div>
            </div></div>
            <div class="col-lg-6"><div class="ss-card">
              <div class="ss-sec">Lokasi Faskes ${ada ? `<a href="${peta}" target="_blank" rel="noopener" class="ss-badge b-info text-decoration-none"><i class="fas fa-external-link-alt"></i>Buka peta</a>` : ''}</div>
              ${kv('Location ID', c.location_id, 1)}${kv('Alamat', c.address)}${kv('Kota', c.city)}${kv('Kode Pos', c.postal_code)}
              ${kv('Koordinat', ada ? c.latitude + ', ' + c.longitude : '')}
            </div></div>`);
        }, m => $('#cfgBox').html(`<div class="col-12"><div class="ss-card ss-empty text-danger"><i class="fas fa-exclamation-circle"></i>${esc(m)}</div></div>`));
      }

      $('#btnTes').on('click', function() {
        const btn = $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menghubungi Satu Sehat…');
        api('tes', {})
          .then(res => {
            Swal.fire({
              icon: 'success',
              title: 'Terhubung',
              html: `Satu Sehat merespons dalam ${res.ms} ms.${res.organisasi ? `<br>Organisasi: <b>${esc(res.organisasi)}</b>` : ''}`
            });
            muatConfig();
          }, m => Swal.fire({
            icon: 'error',
            title: 'Tidak terhubung',
            text: m
          }))
          .always(() => btn.prop('disabled', false).html('<i class="fas fa-plug me-1"></i> Tes Koneksi'));
      });

      // ================= DOKTER =================
      function render() {
        const q = $('#dkSearch').val().trim().toLowerCase();
        const rows = DOK.filter(d => (!fDok || statusDok(d) === fDok) &&
          (!q || ((d.doctor_name || '') + ' ' + (d.doctor_nik || '') + ' ' + (d.idsh || '')).toLowerCase().includes(q)));
        if (!rows.length) {
          $('#dkBody').html(`<tr><td colspan="4" class="ss-empty"><i class="fas fa-user-md"></i>${DOK.length ? 'Tidak ada dokter yang cocok.' : 'Belum ada data dokter. Tambahkan di menu Master Dokter.'}</td></tr>`);
          return;
        }
        $('#dkBody').html(rows.map(d => {
          const s = statusDok(d);
          const nik = d.doctor_nik ?
            `<span class="ss-mono nik" data-nik="${esc(d.doctor_nik)}">${esc(d.nik_valid ? masker(d.doctor_nik) : d.doctor_nik)}</span>
               ${d.nik_valid ? '<a href="javascript:;" class="ms-1 text-muted nik-eye" title="Tampilkan"><i class="fas fa-eye"></i></a>' : ''}
               ${!d.nik_valid ? '<div><span class="ss-badge b-err mt-1">Bukan 16 digit</span></div>' : ''}
               ${d.nik_dobel ? '<div><span class="ss-badge b-warn mt-1" title="NIK yang sama dipakai dokter lain di faskes ini"><i class="fas fa-clone"></i>NIK dobel</span></div>' : ''}` :
            '<span class="ss-badge b-err">Belum diisi</span>';
          const ihs = d.idsh ?
            `<span class="ss-badge b-ok"><i class="fas fa-check-circle"></i>${esc(d.idsh)}</span>` :
            (s === 'cari' ? '<span class="ss-badge b-warn"><i class="fas fa-search"></i>Belum dicari</span>' : '<span class="ss-badge b-mute">Lengkapi NIK dulu</span>');
          return `<tr class="${d.doctor_status == 1 ? '' : 'off'}" data-id="${d.id_doctor}">
            <td><div class="d-flex align-items-center gap-2">
              <span class="ss-ava ${d.idsh ? 'b-ok' : 'b-mute'}">${esc(inisial(d.doctor_name))}</span>
              <div class="min-w-0"><div class="ss-name" title="${esc(d.doctor_name)}">${esc(d.doctor_name || '-')}</div>
              <div class="ss-sub">${d.doctor_status == 1 ? (esc(d.doctor_phone) || 'Aktif') : 'Nonaktif'}</div></div>
            </div></td>
            <td>${nik}</td>
            <td class="ihs-cell">${ihs}</td>
            <td class="text-end keep"><div class="ss-act">
              <button class="btn btn-light text-primary btn-nik" data-id="${d.id_doctor}" title="Ubah NIK"><i class="fas fa-pen"></i> NIK</button>
              <button class="btn btn-light text-success btn-ihs" data-id="${d.id_doctor}" ${d.nik_valid ? '' : 'disabled'} title="${d.idsh ? 'Cari ulang ID Satu Sehat' : 'Cari ID Satu Sehat dari NIK'}"><i class="fas ${d.idsh ? 'fa-redo' : 'fa-search'}"></i> ${d.idsh ? 'Cek ulang' : 'Cari ID'}</button>
            </div></td>
          </tr>`;
        }).join(''));
      }

      function hitung() {
        const aktif = DOK.filter(d => d.doctor_status == 1);
        const ok = aktif.filter(d => d.idsh).length;
        const cnt = {
          '': DOK.length,
          ok: 0,
          cari: 0,
          nik: 0
        };
        DOK.forEach(d => cnt[statusDok(d)]++);
        $('#dkChips [data-n]').each(function() {
          this.textContent = cnt[this.dataset.n];
        });
        $('#stDok').text(`${ok} / ${aktif.length}`);
        const kurang = aktif.length - ok;
        $('#stDokSub').html(kurang ? `<span class="text-warning fw-semibold">${kurang} dokter aktif belum punya ID</span>` : 'Semua dokter aktif sudah punya ID');
        $('#nPerlu').text(kurang).toggleClass('d-none', !kurang);
        $('#btnCariSemua').prop('disabled', !DOK.some(d => statusDok(d) === 'cari'));
      }

      function muatDokter() {
        return api('dokter', null, 'GET').then(res => {
          DOK = res.data || [];
          hitung();
          render();
        }, m => $('#dkBody').html(`<tr><td colspan="4" class="ss-empty text-danger"><i class="fas fa-exclamation-circle"></i>${esc(m)}</td></tr>`));
      }

      let tmr;
      $('#dkSearch').on('input', () => {
        clearTimeout(tmr);
        tmr = setTimeout(render, 150);
      });
      $('#dkChips').on('click', '.btn', function() {
        fDok = this.dataset.f;
        $('#dkChips .btn').removeClass('btn-primary').addClass('btn-light').find('.badge').removeClass('text-primary').addClass('text-dark');
        $(this).removeClass('btn-light').addClass('btn-primary').find('.badge').removeClass('text-dark').addClass('text-primary');
        render();
      });
      $('#dkBody').on('click', '.nik-eye', function() {
        const s = $(this).siblings('.nik');
        const show = s.text().includes('•');
        s.text(show ? s.data('nik') : masker(String(s.data('nik'))));
        $(this).find('i').attr('class', show ? 'fas fa-eye-slash' : 'fas fa-eye');
      });

      // cari ID untuk satu dokter
      function cariIhs(id) {
        return api('ihs', {
          id_doctor: id
        }).then(res => {
          const d = DOK.find(x => String(x.id_doctor) === String(id));
          if (d) d.idsh = res.idsh;
          return res;
        });
      }

      $('#dkBody').on('click', '.btn-ihs', function() {
        const id = this.dataset.id;
        const btn = $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
        cariIhs(id).then(res => {
          hitung();
          render();
          const d = DOK.find(x => String(x.id_doctor) === String(id));
          Swal.fire({
            icon: 'success',
            title: 'ID Satu Sehat ditemukan',
            html: `<div class="ss-mono fs-5 mb-2">${esc(res.idsh)}</div>${res.nama_satusehat ? `Nama di Satu Sehat: <b>${esc(res.nama_satusehat)}</b><br><small class="text-muted">Pastikan sama dengan ${esc(d ? d.doctor_name : '')}.</small>` : ''}`
          });
        }, m => {
          render();
          gagal(m);
        });
      });

      $('#btnCariSemua').on('click', async function() {
        const antre = DOK.filter(d => statusDok(d) === 'cari');
        if (!antre.length) return;
        const btn = $(this).prop('disabled', true);
        let ok = 0;
        const gagalList = [];
        for (let i = 0; i < antre.length; i++) {
          btn.html(`<span class="spinner-border spinner-border-sm me-1"></span> ${i + 1}/${antre.length}`);
          $(`#dkBody tr[data-id="${antre[i].id_doctor}"] .ihs-cell`).html('<span class="spinner-border spinner-border-sm text-primary"></span>');
          try {
            await cariIhs(antre[i].id_doctor);
            ok++;
          } catch (m) {
            gagalList.push(`<li><b>${esc(antre[i].doctor_name)}</b>: ${esc(typeof m === 'string' ? m : 'gagal')}</li>`);
            if (typeof m === 'string' && /kredensial|terhubung/i.test(m)) break; // masalah koneksi: hentikan
          }
        }
        btn.html('<i class="fas fa-search-plus me-1"></i> Cari ID yang belum ada');
        hitung();
        render();
        Swal.fire({
          icon: gagalList.length ? (ok ? 'warning' : 'error') : 'success',
          title: `${ok} dari ${antre.length} ID ditemukan`,
          html: gagalList.length ? `<ul class="text-start small mb-0">${gagalList.join('')}</ul>` : 'Semua dokter yang NIK-nya valid sudah punya ID Satu Sehat.'
        });
      });

      // ---- NIK ----
      let NIK_LAMA = '';

      function cekNik() {
        const v = $('#nik_val').val();
        const ok = /^\d{16}$/.test(v);
        $('#nikCount').text(v.length + '/16').toggleClass('text-success', ok).toggleClass('text-danger', !ok && v.length > 0);
        $('#nikWarn').toggleClass('d-none', !(ok && NIK_LAMA && v !== NIK_LAMA && $('#nikForm').data('ihs')));
        $('#btnNikSimpan').prop('disabled', !ok);
      }
      $('#nik_val').on('input', function() {
        const pos = this.selectionStart;
        const bersih = this.value.replace(/\D/g, '').slice(0, 16);
        if (bersih !== this.value) {
          this.value = bersih;
          this.setSelectionRange(pos - 1, pos - 1);
        }
        cekNik();
      });

      $('#dkBody').on('click', '.btn-nik', function() {
        const d = DOK.find(x => String(x.id_doctor) === String(this.dataset.id));
        if (!d) return;
        NIK_LAMA = d.doctor_nik || '';
        $('#nikForm').data('ihs', !!d.idsh);
        $('#nik_id').val(d.id_doctor);
        $('#nikNama').text(d.doctor_name);
        $('#nik_val').val(String(d.doctor_nik || '').replace(/\D/g, '').slice(0, 16));
        cekNik();
        modal('mdNik').show();
      });
      $('#mdNik').on('shown.bs.modal', () => $('#nik_val').trigger('focus'));

      $('#nikForm').on('submit', function(e) {
        e.preventDefault();
        const id = $('#nik_id').val(),
          nik = $('#nik_val').val();
        if (!/^\d{16}$/.test(nik)) return;
        const btn = $('#btnNikSimpan').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan…');
        api('nik', {
            id_doctor: id,
            doctor_nik: nik
          })
          .then(res => {
            const d = DOK.find(x => String(x.id_doctor) === String(id));
            if (d && res.berubah) {
              d.doctor_nik = nik;
              d.nik_valid = true;
              d.idsh = null;
            }
            modal('mdNik').hide();
            if (d && d.idsh) {
              Swal.fire({
                icon: 'info',
                title: 'NIK tidak berubah',
                timer: 1300,
                showConfirmButton: false
              });
              return;
            }
            return cariIhs(id).then(r2 => Swal.fire({
              icon: 'success',
              title: 'NIK disimpan & ID ditemukan',
              html: `<div class="ss-mono fs-5 mb-2">${esc(r2.idsh)}</div>${r2.nama_satusehat ? `Nama di Satu Sehat: <b>${esc(r2.nama_satusehat)}</b>` : ''}`
            }), m => Swal.fire({
              icon: 'warning',
              title: 'NIK disimpan',
              text: 'Tetapi ID Satu Sehat belum ditemukan: ' + m
            }));
          }, gagal)
          .always(() => {
            btn.html('<i class="fas fa-save me-1"></i> Simpan &amp; Cari ID');
            cekNik();
            muatDokter();
          });
      });

      // tab diingat
      $('.ss-tabs [data-bs-toggle="tab"]').on('shown.bs.tab', e => history.replaceState(null, '', location.pathname + location.search + e.target.dataset.bsTarget.replace('tab-', '')));
      const h = location.hash && document.querySelector(`.ss-tabs [data-bs-target="${location.hash.replace('#', '#tab-')}"]`);
      if (h) bootstrap.Tab.getOrCreateInstance(h).show();

      muatConfig();
      muatDokter();
    })();
  </script>
</body>

</html>
