<?php
$title = 'Manajemen User';
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

    .us-stat {
      border: 1px solid #e5eaef;
      border-radius: 12px;
      background: #fff;
      padding: 14px 16px;
      height: 100%;
    }

    .us-icon {
      width: 40px;
      height: 40px;
      border-radius: 10px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 1.05rem;
      flex-shrink: 0;
    }

    .us-val {
      font-size: 1.35rem;
      font-weight: 700;
      line-height: 1.2;
      color: #2a3547;
    }

    .us-label {
      font-size: .75rem;
      color: #6c757d;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .us-chips .btn {
      border-radius: 20px;
      font-size: .8rem;
      padding: .3rem .8rem;
    }

    .us-chips .badge {
      font-weight: 600;
    }

    .us-search {
      width: 240px;
    }

    @media (max-width: 767.98px) {

      .us-tools,
      .us-search {
        width: 100%;
      }

      .us-search {
        flex: 1;
      }
    }

    #userTable {
      width: 100% !important;
    }

    #userTable thead th {
      font-size: .74rem;
      font-weight: 600;
      color: #5a6a85;
      text-transform: uppercase;
      letter-spacing: .3px;
      background: #f6f9fc;
      border-bottom: 0;
      white-space: nowrap;
    }

    #userTable td {
      font-size: .85rem;
      vertical-align: middle;
      padding-top: .6rem;
      padding-bottom: .6rem;
    }

    #userTable tr.off td:not(.us-keep) {
      opacity: .5;
    }

    .us-ava {
      width: 38px;
      height: 38px;
      border-radius: 50%;
      flex-shrink: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: .8rem;
      font-weight: 700;
    }

    .us-name {
      font-weight: 600;
      color: #2a3547;
      max-width: 260px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .us-sub {
      font-size: .74rem;
      color: #7c8fac;
      white-space: nowrap;
    }

    .us-badge {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      font-size: .7rem;
      font-weight: 600;
      padding: .25em .6em;
      border-radius: 6px;
      white-space: nowrap;
    }

    .us-info {
      display: flex;
      flex-wrap: wrap;
      gap: 4px;
      max-width: 300px;
    }

    .us-act {
      white-space: nowrap;
    }

    .us-act .btn {
      width: 32px;
      height: 32px;
      padding: 0;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 8px;
    }

    .us-loading {
      position: absolute;
      inset: 0;
      background: rgba(255, 255, 255, .6);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 5;
      border-radius: 12px;
    }

    .us-switch .form-check-input {
      width: 2.4em;
      height: 1.25em;
      cursor: pointer;
      margin: 0;
    }

    .role-grid input {
      display: none;
    }

    .role-grid label {
      border: 1.5px solid #e5eaef;
      border-radius: 10px;
      padding: 9px 8px;
      cursor: pointer;
      width: 100%;
      text-align: center;
      font-weight: 600;
      font-size: .8rem;
      margin: 0;
      transition: all .15s;
    }

    .role-grid label i {
      display: block;
      font-size: 1rem;
      margin-bottom: 3px;
    }

    .role-grid input:checked+label {
      border-color: var(--c);
      background: var(--bg);
      color: var(--c);
    }

    .pw-rules {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 2px 12px;
      font-size: .75rem;
      color: #7c8fac;
      margin-top: 6px;
    }

    .pw-rules .ok {
      color: #13a89e;
    }

    .pw-rules i {
      width: 14px;
    }

    .us-sec {
      font-size: .72rem;
      font-weight: 700;
      color: #5a6a85;
      text-transform: uppercase;
      letter-spacing: .4px;
      margin-bottom: .6rem;
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
              <h4 class="fw-semibold mb-1">Manajemen User</h4>
              <div class="text-muted small">Akun login petugas faskes: dokter, perawat, bidan, pendaftaran, kasir, apoteker dan analis lab.</div>
            </div>
            <div class="d-flex gap-2">
              <button class="btn btn-light" id="btnRefresh" title="Muat ulang"><i class="fas fa-sync-alt"></i></button>
              <button class="btn btn-outline-primary" id="btnTambahDokter"><i class="fas fa-user-md me-1"></i> Akses Dokter</button>
              <button class="btn btn-primary" id="btnTambah"><i class="fas fa-user-plus me-1"></i> Tambah User</button>
            </div>
          </div>

          <!-- STAT -->
          <div class="row g-3 mb-3">
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="us-stat d-flex gap-3 align-items-center">
                <span class="us-icon bg-primary-subtle text-primary"><i class="fas fa-users"></i></span>
                <div class="min-w-0">
                  <div class="us-val" id="stTotal">-</div>
                  <div class="us-label" id="stTotalSub">Total user</div>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="us-stat d-flex gap-3 align-items-center">
                <span class="us-icon bg-info-subtle text-info"><i class="fas fa-user-md"></i></span>
                <div class="min-w-0">
                  <div class="us-val" id="stDokter">-</div>
                  <div class="us-label" id="stDokterSub">Akun dokter</div>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="us-stat d-flex gap-3 align-items-center">
                <span class="us-icon bg-success-subtle text-success"><i class="fas fa-user-nurse"></i></span>
                <div class="min-w-0">
                  <div class="us-val" id="stStaf">-</div>
                  <div class="us-label" id="stStafSub">Petugas lain</div>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="us-stat d-flex gap-3 align-items-center">
                <span class="us-icon bg-warning-subtle text-warning"><i class="fas fa-signature"></i></span>
                <div class="min-w-0">
                  <div class="us-val" id="stTtd">-</div>
                  <div class="us-label" id="stTtdSub">Belum punya tanda tangan</div>
                </div>
              </div>
            </div>
          </div>

          <div class="alert alert-warning d-none d-flex flex-wrap align-items-center gap-2 py-2" id="issueBox">
            <i class="fas fa-exclamation-triangle"></i>
            <span class="small" style="flex:1 1 0;min-width:220px" id="issueText"></span>
            <button class="btn btn-sm btn-warning" id="btnIssue">Tampilkan</button>
          </div>

          <!-- TABEL -->
          <div class="card position-relative">
            <div class="us-loading" id="usLoading">
              <div class="spinner-border text-primary"></div>
            </div>
            <div class="card-body p-4">
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div class="us-chips d-flex flex-wrap gap-2" id="usChips"></div>
                <div class="d-flex flex-wrap gap-2 align-items-center us-tools">
                  <select id="fStatus" class="form-select form-select-sm" style="width:auto">
                    <option value="">Semua status</option>
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                    <option value="issue">Perlu perhatian</option>
                    <option value="nottd">Belum ada TTD</option>
                  </select>
                  <div class="input-group input-group-sm us-search">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="search" id="usSearch" class="form-control" placeholder="Cari nama / username" autocomplete="off">
                  </div>
                </div>
              </div>

              <div class="table-responsive">
                <table class="table align-middle mb-0" id="userTable">
                  <thead>
                    <tr>
                      <th>User</th>
                      <th>Role</th>
                      <th>Keterangan</th>
                      <th>Dibuat</th>
                      <th class="text-center">Aktif</th>
                      <th class="text-end">Aksi</th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                </table>
              </div>
              <div class="small text-muted mt-3"><i class="fas fa-info-circle me-1"></i>Akun admin hanya bisa diubah oleh pemiliknya. Anda tidak bisa menonaktifkan atau menghapus akun sendiri. Lebih aman <b>menonaktifkan</b> user yang sudah berhenti daripada menghapus, agar nama pembuat di riwayat pasien tetap tercatat.</div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <!-- MODAL USER -->
  <div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
      <form id="userForm" class="modal-content" novalidate autocomplete="off">
        <div class="modal-header">
          <h5 class="modal-title">Tambah User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_user" id="f_id">

          <!-- role (tambah staf) -->
          <div class="mb-3" id="grpRole">
            <label class="form-label required">Role</label>
            <div class="row g-2 role-grid" id="roleGrid"></div>
          </div>
          <!-- role (edit, read-only) -->
          <div class="mb-3 d-none" id="grpRoleView">
            <label class="form-label">Role</label>
            <div id="roleView"></div>
            <div class="form-text">Role tidak bisa diubah. Buat akun baru bila petugas pindah bagian.</div>
          </div>

          <!-- dokter -->
          <div class="mb-3 d-none" id="grpDokter">
            <label class="form-label required" for="f_doctor">Dokter</label>
            <select id="f_doctor" name="id_doctor" class="form-select"></select>
            <div class="form-text" id="dokterHint">Diambil dari master dokter aktif. Dokter yang sudah punya akun tidak bisa dipilih lagi.</div>
            <div class="alert alert-warning small py-2 mt-2 mb-0 d-none" id="dokterWarn"></div>
          </div>

          <!-- nama -->
          <div class="mb-3" id="grpNama">
            <label class="form-label required" for="f_fullname">Nama Lengkap</label>
            <input type="text" id="f_fullname" name="fullname" class="form-control" maxlength="100" placeholder="Nama sesuai identitas">
          </div>

          <div class="us-sec mt-4">Akun Login</div>
          <div class="mb-3">
            <label class="form-label required" for="f_username">Username</label>
            <div class="input-group">
              <span class="input-group-text bg-white"><i class="fas fa-at text-muted"></i></span>
              <input type="text" id="f_username" name="username" class="form-control" maxlength="50" autocomplete="off" spellcheck="false">
            </div>
            <div class="form-text">3–50 karakter tanpa spasi. Berlaku untuk login di semua faskes, jadi harus unik.</div>
          </div>
          <div class="mb-1">
            <label class="form-label" for="f_password" id="pwLabel">Password</label>
            <div class="input-group">
              <input type="password" id="f_password" name="password" class="form-control" autocomplete="new-password">
              <button class="btn btn-light border" type="button" id="pwShow" title="Tampilkan"><i class="fas fa-eye"></i></button>
              <button class="btn btn-light border" type="button" id="pwGen" title="Buat password acak"><i class="fas fa-key"></i></button>
            </div>
            <div class="form-text d-none" id="pwEditHint">Kosongkan bila password tidak diganti.</div>
            <div class="pw-rules" id="pwRules">
              <span data-r="len"><i class="fas fa-circle fa-xs"></i> Min. 8 karakter</span>
              <span data-r="up"><i class="fas fa-circle fa-xs"></i> Huruf besar</span>
              <span data-r="low"><i class="fas fa-circle fa-xs"></i> Huruf kecil</span>
              <span data-r="num"><i class="fas fa-circle fa-xs"></i> Angka</span>
              <span data-r="sym"><i class="fas fa-circle fa-xs"></i> Karakter khusus (*#$!&amp;)</span>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary" id="btnSimpan"><i class="fas fa-save me-1"></i> Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <?php require 'library.php'; ?>

  <script>
    const apiUrl = 'controller/master/userController';
    const ROLE = {
      admin: ['Admin', '#5d87ff', '#ecf2ff', 'fa-user-shield'],
      superadmin: ['Superadmin', '#5d87ff', '#ecf2ff', 'fa-user-shield'],
      dokter: ['Dokter', '#0b8fd6', '#e5f3fd', 'fa-user-md'],
      perawat: ['Perawat', '#13a89e', '#e6fffa', 'fa-user-nurse'],
      bidan: ['Bidan', '#d6338a', '#fdebf4', 'fa-baby'],
      receptionis: ['Pendaftaran', '#7b5cd6', '#f1ecff', 'fa-concierge-bell'],
      kasir: ['Kasir', '#c27c0e', '#fef5e5', 'fa-cash-register'],
      apoteker: ['Apoteker', '#0d8a5f', '#e3f7ee', 'fa-prescription-bottle-alt'],
      analislab: ['Analis Lab', '#fa5a5a', '#fdede8', 'fa-microscope']
    };
    const ROLE_STAF = ['perawat', 'bidan', 'receptionis', 'kasir', 'apoteker', 'analislab'];
    const roleInfo = r => ROLE[r] || [r || '-', '#5a6a85', '#eef2f7', 'fa-user'];

    let DATA = [];
    let fRole = '';
    let MODE = 'staf'; // staf | dokter
    let EDIT = null;

    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#39;'
    } [c]));
    const tgl = s => {
      if (!s) return '-';
      const d = new Date(String(s).replace(' ', 'T'));
      return isNaN(d) ? '-' : d.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
      });
    };
    const inisial = n => {
      const w = String(n || '?').replace(/^(drg|dr)\.?\s*/i, '').replace(/[^A-Za-z\s]/g, ' ').trim().split(/\s+/);
      return ((w[0] || '?')[0] + (w[1] ? w[1][0] : '')).toUpperCase();
    };
    const masalah = r => (r.dokter && ['beda', 'putus', 'kode'].includes(r.dokter.status)) || r.username_dobel > 0;

    function infoHtml(r) {
      const b = [];
      if (r.dokter) {
        const d = r.dokter;
        if (d.status === 'ok' || d.status === 'nama') {
          b.push(`<span class="us-badge" style="color:#13a89e;background:#e6fffa" title="${d.status === 'nama' ? 'Terhubung lewat nama (kode dokter belum tersimpan di akun)' : 'Terhubung lewat kode dokter'}"><i class="fas fa-link"></i>${esc(d.kode || 'tanpa kode')}</span>`);
        } else if (d.status === 'beda') {
          b.push(`<span class="us-badge" style="color:#c2410c;background:#fff4e5" title="Kode di akun menunjuk ke dokter lain: ${esc(d.nama)}. Edit akun lalu simpan untuk memperbaiki."><i class="fas fa-exclamation-triangle"></i>Kode milik ${esc(d.nama)}</span>`);
        } else if (d.status === 'kode') {
          b.push(`<span class="us-badge" style="color:#c2410c;background:#fff4e5" title="Kode ${esc(d.kode || '')} di akun tidak ada di master dokter faskes."><i class="fas fa-exclamation-triangle"></i>Kode tidak dikenal</span>`);
        } else {
          b.push(`<span class="us-badge" style="color:#fa5a5a;background:#fdede8" title="Nama/kode akun tidak cocok dengan master dokter faskes."><i class="fas fa-unlink"></i>Tidak terhubung ke master dokter</span>`);
        }
      }
      if (r.username_dobel > 0) {
        b.push(`<span class="us-badge" style="color:#c2410c;background:#fff4e5" title="Username sama dipakai ${r.username_dobel} akun lain. Saat login hanya salah satu akun yang terbaca. Ganti username agar unik."><i class="fas fa-clone"></i>Username dobel</span>`);
      }
      b.push(r.ttd ?
        `<span class="us-badge" style="color:#5a6a85;background:#eef2f7"><i class="fas fa-signature"></i>TTD</span>` :
        `<span class="us-badge" style="color:#a0aec0;background:#f6f9fc" title="Belum upload tanda tangan (menu Tanda Tangan Digital)"><i class="fas fa-signature"></i>Belum TTD</span>`);
      return `<div class="us-info">${b.join('')}</div>`;
    }

    // ===== TABEL =====
    const table = $('#userTable').DataTable({
      data: [],
      dom: 'rt<"d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2"ip>',
      pageLength: 25,
      order: [
        [0, 'asc']
      ],
      language: {
        emptyTable: 'Belum ada user.',
        zeroRecords: 'Tidak ada user yang cocok dengan filter.',
        info: 'Menampilkan _START_–_END_ dari _TOTAL_ user',
        infoEmpty: '0 user',
        infoFiltered: '(disaring dari _MAX_)',
        paginate: {
          previous: '‹',
          next: '›'
        }
      },
      createdRow: (tr, r) => {
        if (r.status != 1) tr.classList.add('off');
      },
      columns: [{
          data: 'fullname',
          render: (v, t, r) => {
            if (t !== 'display') return (v || '') + ' ' + (r.username || '');
            const R = roleInfo(r.roles);
            return `<div class="d-flex align-items-center gap-2">
              <span class="us-ava" style="color:${R[1]};background:${R[2]}">${esc(inisial(v))}</span>
              <div class="min-w-0">
                <div class="us-name" title="${esc(v)}">${esc(v) || '-'} ${r.is_self ? '<span class="badge bg-primary ms-1" style="font-size:.62rem">Anda</span>' : ''}</div>
                <div class="us-sub">@${esc(r.username || '-')}</div>
              </div>
            </div>`;
          }
        },
        {
          data: 'roles',
          render: (v, t) => {
            const R = roleInfo(v);
            return t !== 'display' ? R[0] : `<span class="us-badge" style="color:${R[1]};background:${R[2]}"><i class="fas ${R[3]}"></i>${esc(R[0])}</span>`;
          }
        },
        {
          data: null,
          orderable: false,
          render: (_, t, r) => t === 'display' ? infoHtml(r) : (r.dokter ? r.dokter.status : '') + (r.username_dobel ? ' dobel' : '')
        },
        {
          data: 'created_at',
          render: (v, t) => t === 'display' ? `<span class="us-sub">${tgl(v)}</span>` : (v || '')
        },
        {
          data: 'status',
          className: 'text-center us-keep',
          render: (v, t, r) => {
            if (t !== 'display') return v;
            const kunci = r.terkunci || r.is_self;
            const tip = r.is_self ? 'Akun Anda sendiri' : (r.terkunci ? 'Akun admin' : (v == 1 ? 'Aktif, klik untuk menonaktifkan' : 'Nonaktif, klik untuk mengaktifkan'));
            return `<div class="form-check form-switch us-switch d-inline-block" title="${tip}">
              <input class="form-check-input toggle-status-user" type="checkbox" data-id="${r.id_user}" ${v == 1 ? 'checked' : ''} ${kunci ? 'disabled' : ''}>
            </div>`;
          }
        },
        {
          data: null,
          orderable: false,
          searchable: false,
          className: 'text-end us-keep',
          render: (_, t, r) => {
            const hapusOff = r.terkunci || r.is_self;
            return `<div class="us-act">
              <span class="d-inline-block" title="${r.terkunci ? 'Akun admin hanya bisa diubah pemiliknya' : 'Edit / ganti password'}">
                <button class="btn btn-light text-primary edit-btn" data-id="${r.id_user}" ${r.terkunci ? 'disabled' : ''}><i class="fas fa-pen"></i></button>
              </span>
              <span class="d-inline-block" title="${r.is_self ? 'Tidak bisa menghapus akun sendiri' : (r.terkunci ? 'Akun admin tidak bisa dihapus' : 'Hapus')}">
                <button class="btn btn-light text-danger delete-btn" data-id="${r.id_user}" ${hapusOff ? 'disabled' : ''}><i class="fas fa-trash"></i></button>
              </span>
            </div>`;
          }
        }
      ]
    });

    $.fn.dataTable.ext.search.push((settings, _row, idx) => {
      if (settings.nTable.id !== 'userTable') return true;
      const r = settings.aoData[idx]._aData;
      if (fRole) {
        if (fRole === 'admin' ? !['admin', 'superadmin'].includes(r.roles) : r.roles !== fRole) return false;
      }
      const fs = $('#fStatus').val();
      if ((fs === '1' || fs === '0') && String(r.status) !== fs) return false;
      if (fs === 'issue' && !masalah(r)) return false;
      if (fs === 'nottd' && r.ttd) return false;
      return true;
    });

    function renderChips() {
      const cnt = {};
      DATA.forEach(r => {
        const k = r.roles === 'superadmin' ? 'admin' : r.roles;
        cnt[k] = (cnt[k] || 0) + 1;
      });
      const urut = ['admin', 'dokter', 'perawat', 'bidan', 'receptionis', 'kasir', 'apoteker', 'analislab'];
      Object.keys(cnt).forEach(k => {
        if (!urut.includes(k)) urut.push(k);
      });
      let h = `<button class="btn ${fRole === '' ? 'btn-primary' : 'btn-light'}" data-role="">Semua <span class="badge bg-white ${fRole === '' ? 'text-primary' : 'text-dark'} ms-1">${DATA.length}</span></button>`;
      urut.filter(k => cnt[k]).forEach(k => {
        const on = fRole === k;
        h += `<button class="btn ${on ? 'btn-primary' : 'btn-light'}" data-role="${esc(k)}">${esc(roleInfo(k)[0])} <span class="badge bg-white ${on ? 'text-primary' : 'text-dark'} ms-1">${cnt[k]}</span></button>`;
      });
      $('#usChips').html(h);
    }

    function hitungStat() {
      const aktif = DATA.filter(r => r.status == 1).length;
      $('#stTotal').text(DATA.length);
      $('#stTotalSub').text(`${aktif} aktif · ${DATA.length - aktif} nonaktif`);

      const dok = DATA.filter(r => r.roles === 'dokter');
      const dokBermasalah = dok.filter(r => r.dokter && !['ok', 'nama'].includes(r.dokter.status)).length;
      $('#stDokter').text(dok.length);
      $('#stDokterSub').html(dokBermasalah ? `<span class="text-warning fw-semibold">${dokBermasalah} perlu dicek tautannya</span>` : 'Semua terhubung ke master dokter');

      const staf = DATA.filter(r => !['dokter', 'admin', 'superadmin'].includes(r.roles));
      $('#stStaf').text(staf.length);
      const adm = DATA.length - dok.length - staf.length;
      $('#stStafSub').text(`Petugas non-dokter · ${adm} admin`);

      const nottd = DATA.filter(r => !r.ttd && r.status == 1).length;
      $('#stTtd').text(nottd);
      $('#stTtdSub').text(nottd ? 'User aktif belum punya tanda tangan' : 'Semua user aktif sudah punya TTD');

      const nIssue = DATA.filter(masalah).length;
      const dobel = DATA.filter(r => r.username_dobel > 0).length;
      if (nIssue) {
        const p = [];
        if (dokBermasalah) p.push(`<b>${dokBermasalah}</b> akun dokter tidak cocok dengan master dokter (kode dokter bisa tertukar saat login)`);
        if (dobel) p.push(`<b>${dobel}</b> username juga dipakai akun lain sehingga salah satunya tidak bisa login dengan benar`);
        $('#issueText').html(p.join('; ') + '. Edit akun tersebut lalu simpan untuk memperbaiki.');
        $('#issueBox').removeClass('d-none');
      } else {
        $('#issueBox').addClass('d-none');
      }
      renderChips();
    }

    function muat() {
      $('#usLoading').css('display', 'flex');
      return fetch(apiUrl)
        .then(r => r.json())
        .then(res => {
          if (res.status !== 'success') throw new Error(res.message || 'Gagal memuat');
          DATA = res.data || [];
          table.clear().rows.add(DATA).draw(false);
          hitungStat();
        })
        .catch(e => Swal.fire('Gagal', e.message || 'Tidak dapat memuat data user.', 'error'))
        .finally(() => $('#usLoading').hide());
    }

    // ===== FILTER =====
    let tmr;
    $('#usSearch').on('input', function() {
      clearTimeout(tmr);
      tmr = setTimeout(() => table.search(this.value).draw(), 200);
    });
    $('#fStatus').on('change', () => table.draw());
    $('#usChips').on('click', '.btn', function() {
      fRole = this.dataset.role;
      renderChips();
      table.draw();
    });
    $('#btnIssue').on('click', () => {
      fRole = '';
      renderChips();
      $('#fStatus').val('issue');
      table.draw();
    });
    $('#btnRefresh').on('click', muat);

    // ===== FORM =====
    $('#roleGrid').html(ROLE_STAF.map(k => {
      const R = ROLE[k];
      return `<div class="col-4">
        <input type="radio" name="roles" id="role_${k}" value="${k}">
        <label for="role_${k}" style="--c:${R[1]};--bg:${R[2]}"><i class="fas ${R[3]}"></i>${R[0]}</label>
      </div>`;
    }).join(''));

    const PW_RULE = {
      len: v => v.length >= 8,
      up: v => /[A-Z]/.test(v),
      low: v => /[a-z]/.test(v),
      num: v => /\d/.test(v),
      sym: v => /[^A-Za-z0-9]/.test(v)
    };

    function cekPw() {
      const v = $('#f_password').val();
      let ok = true;
      $('#pwRules [data-r]').each(function() {
        const lulus = PW_RULE[this.dataset.r](v);
        ok = ok && lulus;
        $(this).toggleClass('ok', lulus).find('i').attr('class', lulus ? 'fas fa-check' : 'fas fa-circle fa-xs');
      });
      return ok;
    }
    $('#f_password').on('input', cekPw);
    $('#pwShow').on('click', function() {
      const i = $('#f_password');
      const show = i.attr('type') === 'password';
      i.attr('type', show ? 'text' : 'password');
      $(this).find('i').attr('class', show ? 'fas fa-eye-slash' : 'fas fa-eye');
    });
    $('#pwGen').on('click', () => {
      const set = ['ABCDEFGHJKLMNPQRSTUVWXYZ', 'abcdefghijkmnopqrstuvwxyz', '23456789', '*#$!&@'];
      const rnd = n => {
        const a = new Uint32Array(1);
        crypto.getRandomValues(a);
        return a[0] % n;
      };
      let p = set.map(s => s[rnd(s.length)]);
      const all = set.join('');
      while (p.length < 10) p.push(all[rnd(all.length)]);
      for (let i = p.length - 1; i > 0; i--) {
        const j = rnd(i + 1);
        [p[i], p[j]] = [p[j], p[i]];
      }
      $('#f_password').val(p.join('')).attr('type', 'text');
      $('#pwShow i').attr('class', 'fas fa-eye-slash');
      cekPw();
    });

    function saranUsername(nama) {
      return String(nama || '').toLowerCase().replace(/^(drg|dr)\.?\s*/i, '').replace(/,.*$/, '')
        .normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/[^a-z0-9]+/g, '.').replace(/^\.+|\.+$/g, '').slice(0, 30);
    }

    function muatDokter(pilih, izinkanId) {
      const $s = $('#f_doctor').html('<option value="">Memuat…</option>').prop('disabled', true);
      return fetch(apiUrl + '?dokter=1')
        .then(r => r.json())
        .then(res => {
          if (res.status !== 'success') throw new Error(res.message);
          let h = '<option value="">— Pilih dokter —</option>';
          res.data.forEach(d => {
            const kunci = d.punya_akun && String(d.id_doctor) !== String(izinkanId || '') && MODE === 'dokter' && !EDIT;
            h += `<option value="${d.id_doctor}" data-nama="${esc(d.doctor_name)}" ${kunci ? 'disabled' : ''}>${esc(d.doctor_name)}${d.doctor_code ? ' · ' + esc(d.doctor_code) : ' · tanpa kode'}${kunci ? ' (sudah punya akun)' : ''}</option>`;
          });
          $s.html(h).prop('disabled', false);
          if (pilih) $s.val(String(pilih));
          if (!res.data.length) $('#dokterHint').html('<span class="text-danger">Belum ada dokter aktif. Tambahkan dulu di menu Master Dokter.</span>');
        })
        .catch(e => {
          $s.html('<option value="">Gagal memuat dokter</option>');
          Swal.fire('Gagal', e.message || 'Tidak dapat memuat daftar dokter', 'error');
        });
    }

    $('#f_doctor').on('change', function() {
      const nama = $(this).find(':selected').data('nama');
      if (!EDIT && nama && !$('#f_username').val()) $('#f_username').val(saranUsername(nama));
    });

    function bukaForm(mode, d) {
      MODE = mode;
      EDIT = d || null;
      const f = $('#userForm')[0];
      f.reset();
      $('#userForm .is-invalid').removeClass('is-invalid');
      $('#f_id').val(d ? d.id_user : '');
      $('#f_password').attr('type', 'password');
      $('#pwShow i').attr('class', 'fas fa-eye');
      $('#dokterWarn').addClass('d-none');
      $('#dokterHint').text(d ? 'Pilih ulang dokter bila akun ini tertaut ke dokter yang salah.' : 'Diambil dari master dokter aktif. Dokter yang sudah punya akun tidak bisa dipilih lagi.');

      const dokter = mode === 'dokter';
      $('#grpDokter').toggleClass('d-none', !dokter);
      $('#grpNama').toggleClass('d-none', dokter);
      $('#grpRole').toggleClass('d-none', !!d || dokter);
      $('#grpRoleView').toggleClass('d-none', !d);

      if (d) {
        const R = roleInfo(d.roles);
        $('#roleView').html(`<span class="us-badge" style="color:${R[1]};background:${R[2]};font-size:.8rem"><i class="fas ${R[3]}"></i>${esc(R[0])}</span>`);
        $('#f_fullname').val(d.fullname || '');
        $('#f_username').val(d.username || '');
        $('#pwLabel').removeClass('required').text('Password baru');
        $('#pwEditHint').removeClass('d-none');
        $('#userModal .modal-title').text(d.is_self ? 'Edit Akun Saya' : 'Edit User');
        if (dokter && d.dokter && ['beda', 'kode', 'putus'].includes(d.dokter.status)) {
          const msg = {
            beda: `Kode dokter di akun ini milik <b>${esc(d.dokter.nama)}</b>. Pastikan dokter di bawah sudah benar lalu simpan.`,
            kode: 'Kode dokter di akun ini tidak ada di master dokter faskes. Pilih dokter yang benar lalu simpan.',
            putus: 'Akun ini belum terhubung ke master dokter. Pilih dokter yang benar lalu simpan.'
          } [d.dokter.status];
          $('#dokterWarn').html('<i class="fas fa-exclamation-triangle me-1"></i>' + msg).removeClass('d-none');
        }
      } else {
        $('#pwLabel').addClass('required').text('Password');
        $('#pwEditHint').addClass('d-none');
        $('#userModal .modal-title').text(dokter ? 'Tambah Akses Dokter' : 'Tambah User');
        if (!dokter) $('#role_perawat').prop('checked', true);
      }
      cekPw();
      if (dokter) muatDokter(d && d.dokter ? d.dokter.id_doctor : '', d && d.dokter ? d.dokter.id_doctor : '');
      bootstrap.Modal.getOrCreateInstance(document.getElementById('userModal')).show();
    }

    $('#userModal').on('shown.bs.modal', () => {
      (MODE === 'dokter' && !EDIT ? $('#f_doctor') : (EDIT ? $('#f_username') : $('#f_fullname'))).trigger('focus');
    });
    $('#btnTambah').on('click', () => bukaForm('staf', null));
    $('#btnTambahDokter').on('click', () => bukaForm('dokter', null));

    $(document).on('click', '.edit-btn', function() {
      const btn = $(this).prop('disabled', true);
      fetch(apiUrl + `?id=${encodeURIComponent(this.dataset.id)}`)
        .then(r => r.json())
        .then(res => {
          if (res.status !== 'success') throw new Error(res.message);
          bukaForm(res.data.roles === 'dokter' ? 'dokter' : 'staf', res.data);
        })
        .catch(e => Swal.fire('Gagal', e.message || 'Data tidak ditemukan', 'error'))
        .finally(() => btn.prop('disabled', false));
    });

    $('#userForm').on('submit', function(e) {
      e.preventDefault();
      const salah = el => {
        $(el).addClass('is-invalid').trigger('focus');
        return false;
      };
      const user = $('#f_username').val().trim();
      const pw = $('#f_password').val();

      if (MODE === 'dokter' && !$('#f_doctor').val() && !EDIT) return salah('#f_doctor');
      if (MODE === 'staf' && !$('#f_fullname').val().trim()) return salah('#f_fullname');
      if (!user) return salah('#f_username');
      if ((!EDIT || user.toLowerCase() !== String(EDIT.username || '').toLowerCase()) && !/^[A-Za-z0-9._@-]{3,50}$/.test(user)) {
        Swal.fire('Username tidak valid', '3–50 karakter, hanya huruf, angka, titik, garis bawah, @ atau tanda minus (tanpa spasi).', 'warning');
        return salah('#f_username');
      }
      if ((!EDIT || pw) && !cekPw()) {
        Swal.fire('Password belum memenuhi syarat', 'Lihat daftar syarat di bawah kolom password.', 'warning');
        return salah('#f_password');
      }

      const body = new URLSearchParams();
      if (EDIT) body.set('id_user', EDIT.id_user);
      body.set('username', user);
      if (pw) body.set('password', pw);
      if (MODE === 'dokter') {
        body.set('roles', 'dokter');
        if ($('#f_doctor').val()) body.set('id_doctor', $('#f_doctor').val());
      } else {
        body.set('fullname', $('#f_fullname').val().trim());
        if (!EDIT) body.set('roles', $('input[name="roles"]:checked').val() || '');
      }

      const btn = $('#btnSimpan').prop('disabled', true);
      fetch(apiUrl + (EDIT ? `?id=${encodeURIComponent(EDIT.id_user)}` : ''), {
          method: EDIT ? 'PUT' : 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body
        })
        .then(r => r.json())
        .then(res => {
          if (res.status !== 'success') throw new Error(res.message);
          bootstrap.Modal.getOrCreateInstance(document.getElementById('userModal')).hide();
          Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: res.message,
            timer: 1400,
            showConfirmButton: false
          });
          muat();
        })
        .catch(e => Swal.fire('Gagal', e.message || 'Server error', 'error'))
        .finally(() => btn.prop('disabled', false));
    });
    $('#userForm').on('input change', '.is-invalid', function() {
      $(this).removeClass('is-invalid');
    });

    // ===== HAPUS =====
    $(document).on('click', '.delete-btn', function() {
      const id = this.dataset.id;
      const r = DATA.find(x => String(x.id_user) === String(id));
      Swal.fire({
        title: 'Hapus user?',
        html: `<b>${esc(r ? r.fullname : '')}</b> (@${esc(r ? r.username : '')}) akan dihapus permanen.<br><small class="text-muted">Nama pembuat pada riwayat yang dibuat user ini bisa ikut hilang. Bila petugas sudah berhenti, sebaiknya <b>nonaktifkan</b> saja.</small>`,
        icon: 'warning',
        showCancelButton: true,
        showDenyButton: r && r.status == 1,
        confirmButtonColor: '#fa5a5a',
        denyButtonColor: '#5d87ff',
        confirmButtonText: 'Hapus',
        denyButtonText: 'Nonaktifkan saja',
        cancelButtonText: 'Batal'
      }).then(res => {
        if (res.isDenied) {
          const sw = document.querySelector(`.toggle-status-user[data-id="${CSS.escape(id)}"]`);
          if (sw) {
            sw.checked = false;
            $(sw).trigger('change');
          }
          return;
        }
        if (!res.isConfirmed) return;
        fetch(apiUrl + `?id=${encodeURIComponent(id)}`, {
            method: 'DELETE'
          })
          .then(x => x.json())
          .then(d => {
            if (d.status !== 'success') throw new Error(d.message);
            Swal.fire({
              icon: 'success',
              title: 'Terhapus',
              timer: 1200,
              showConfirmButton: false
            });
            muat();
          })
          .catch(e => Swal.fire('Gagal', e.message || 'Server error', 'error'));
      });
    });

    // ===== TOGGLE STATUS =====
    $(document).on('change', '.toggle-status-user', function() {
      const el = this;
      const id = el.dataset.id;
      const status = el.checked ? 1 : 0;
      el.disabled = true;
      fetch(apiUrl + '?toggle_status=1', {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: `id_user=${encodeURIComponent(id)}&status=${status}`
        })
        .then(r => r.json())
        .then(res => {
          if (res.status !== 'success') throw new Error(res.message);
          const r = DATA.find(x => String(x.id_user) === String(id));
          if (r) r.status = status;
          $(el).closest('tr').toggleClass('off', !status);
          hitungStat();
        })
        .catch(e => {
          el.checked = !el.checked;
          Swal.fire('Gagal', e.message || 'Server error', 'error');
        })
        .finally(() => el.disabled = false);
    });

    muat();
  </script>
</body>

</html>
