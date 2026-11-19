<?php
$title = 'Integrasi BPJS Kesehatan';
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

    .bs-stat {
      border: 1px solid #e5eaef;
      border-radius: 12px;
      background: #fff;
      padding: 14px 16px;
      height: 100%;
    }

    .bs-icon {
      width: 40px;
      height: 40px;
      border-radius: 10px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 1.05rem;
      flex-shrink: 0;
    }

    .bs-val {
      font-size: 1.1rem;
      font-weight: 700;
      line-height: 1.25;
      color: #2a3547;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .bs-label {
      font-size: .75rem;
      color: #6c757d;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .bs-tabs {
      gap: 6px;
      flex-wrap: wrap;
    }

    .bs-tabs .nav-link {
      border-radius: 10px;
      font-weight: 600;
      font-size: .85rem;
      color: #5a6a85;
      background: #f2f5f9;
      padding: .5rem .9rem;
    }

    .bs-tabs .nav-link.active {
      background: #5d87ff;
      color: #fff;
    }

    .bs-tabs .nav-link .badge {
      font-weight: 600;
    }

    .bs-card {
      border: 1px solid #e5eaef;
      border-radius: 12px;
      padding: 18px;
      height: 100%;
      background: #fff;
    }

    .bs-sec {
      font-size: .72rem;
      font-weight: 700;
      color: #5a6a85;
      text-transform: uppercase;
      letter-spacing: .4px;
      margin-bottom: .75rem;
    }

    .kv {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 12px;
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
      word-break: break-all;
    }

    .kv .v code {
      color: #2a3547;
      background: #f6f9fc;
      padding: 2px 6px;
      border-radius: 5px;
      font-size: .8rem;
    }

    .bs-table {
      width: 100% !important;
    }

    .bs-table thead th {
      font-size: .74rem;
      font-weight: 600;
      color: #5a6a85;
      text-transform: uppercase;
      letter-spacing: .3px;
      background: #f6f9fc;
      border-bottom: 0;
      white-space: nowrap;
    }

    .bs-table td {
      font-size: .85rem;
      vertical-align: middle;
    }

    .bs-table tr.off td:not(.keep) {
      opacity: .5;
    }

    .bs-ava {
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

    .bs-name {
      font-weight: 600;
      color: #2a3547;
      max-width: 260px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .bs-sub {
      font-size: .74rem;
      color: #7c8fac;
      white-space: nowrap;
    }

    .bs-badge {
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

    .bs-act {
      white-space: nowrap;
    }

    .bs-act .btn {
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

    .bs-chips .btn {
      border-radius: 20px;
      font-size: .78rem;
      padding: .28rem .8rem;
    }

    .bs-search {
      width: 230px;
    }

    @media (max-width: 767.98px) {

      .bs-tools,
      .bs-search {
        width: 100%;
      }

      .bs-search {
        flex: 1;
      }
    }

    .bs-empty {
      text-align: center;
      color: #7c8fac;
      padding: 40px 10px;
    }

    .bs-empty i {
      font-size: 1.8rem;
      display: block;
      margin-bottom: 8px;
      opacity: .6;
    }

    .pw-cell {
      font-family: monospace;
      font-size: .8rem;
    }

    .jd-form {
      background: #f6f9fc;
      border-radius: 12px;
      padding: 14px 16px;
    }

    .jd-day {
      display: inline-block;
      min-width: 64px;
      text-align: center;
      font-weight: 700;
      font-size: .72rem;
      padding: .3em .5em;
      border-radius: 6px;
      background: #ecf2ff;
      color: #5d87ff;
    }

    .jd-kuota {
      width: 80px;
      margin: auto;
    }

    .jd-kuota.saving {
      background: #fff8e1;
    }

    .jd-kuota.saved {
      background: #e6fffa;
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
              <h4 class="fw-semibold mb-1">Integrasi BPJS Kesehatan</h4>
              <div class="text-muted small">Akun PCare, Antrean Online, data dokter BPJS beserta jadwal &amp; iCare, dan pemetaan dokter internal.</div>
            </div>
            <button class="btn btn-outline-primary" id="btnTes"><i class="fas fa-plug me-1"></i> Tes Koneksi PCare</button>
          </div>

          <!-- STATUS -->
          <div class="row g-3 mb-3">
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="bs-stat d-flex gap-3 align-items-center">
                <span class="bs-icon bg-primary-subtle text-primary" id="icPcare"><i class="fas fa-hospital"></i></span>
                <div class="min-w-0">
                  <div class="bs-val" id="stPcare">-</div>
                  <div class="bs-label" id="stPcareSub">PCare</div>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="bs-stat d-flex gap-3 align-items-center">
                <span class="bs-icon bg-success-subtle text-success" id="icAntrol"><i class="fas fa-ticket-alt"></i></span>
                <div class="min-w-0">
                  <div class="bs-val" id="stAntrol">-</div>
                  <div class="bs-label">Antrean Online (Mobile JKN)</div>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="bs-stat d-flex gap-3 align-items-center">
                <span class="bs-icon bg-info-subtle text-info"><i class="fas fa-user-md"></i></span>
                <div class="min-w-0">
                  <div class="bs-val" id="stDokter">-</div>
                  <div class="bs-label" id="stDokterSub">Dokter BPJS aktif</div>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="bs-stat d-flex gap-3 align-items-center">
                <span class="bs-icon bg-warning-subtle text-warning"><i class="fas fa-link"></i></span>
                <div class="min-w-0">
                  <div class="bs-val" id="stMap">-</div>
                  <div class="bs-label" id="stMapSub">Dokter internal terpetakan</div>
                </div>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-body p-4">
              <ul class="nav bs-tabs mb-4" role="tablist">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-pcare" type="button"><i class="fas fa-key me-1"></i> Akun PCare</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-antrol" type="button"><i class="fas fa-ticket-alt me-1"></i> Antrean Online</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-dokter" type="button"><i class="fas fa-user-md me-1"></i> Dokter BPJS, Jadwal &amp; iCare <span class="badge bg-white text-dark ms-1" id="nDokter">0</span></button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-internal" type="button"><i class="fas fa-random me-1"></i> Pemetaan Dokter Internal <span class="badge bg-white text-dark ms-1" id="nInternal">0</span></button></li>
              </ul>

              <div class="tab-content">
                <!-- ===== PCARE ===== -->
                <div class="tab-pane fade show active" id="tab-pcare">
                  <div class="row g-3">
                    <div class="col-lg-6">
                      <form class="bs-card" id="formPcare" autocomplete="off" novalidate>
                        <div class="bs-sec">Akun Login PCare</div>
                        <p class="small text-muted">Bila username / password diganti di aplikasi PCare, perbarui juga di sini. Bila tidak, semua fitur yang terhubung ke PCare (pendaftaran, kunjungan, obat) akan gagal.</p>
                        <div class="mb-3">
                          <label class="form-label required" for="pc_user">Username PCare</label>
                          <input type="text" class="form-control" id="pc_user" maxlength="100" autocomplete="off" spellcheck="false">
                        </div>
                        <div class="mb-3">
                          <label class="form-label" for="pc_pass">Password PCare</label>
                          <div class="input-group">
                            <input type="password" class="form-control" id="pc_pass" autocomplete="new-password">
                            <button class="btn btn-light border" type="button" data-eye="#pc_pass" title="Tampilkan"><i class="fas fa-eye"></i></button>
                          </div>
                          <div class="form-text" id="pc_passHint">Kosongkan bila password tidak diganti.</div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" id="btnPcare"><i class="fas fa-save me-1"></i> Simpan Akun PCare</button>
                      </form>
                    </div>
                    <div class="col-lg-6">
                      <div class="bs-card">
                        <div class="bs-sec d-flex justify-content-between">Kredensial API <span class="bs-badge b-mute"><i class="fas fa-lock"></i>Dikelola tim IT</span></div>
                        <div id="pcareKred">
                          <div class="bs-empty"><span class="spinner-border spinner-border-sm"></span></div>
                        </div>
                        <div class="small text-muted mt-2"><i class="fas fa-info-circle me-1"></i>Secret key &amp; user key disamarkan. Hubungi tim IT bila perlu diubah.</div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- ===== ANTROL ===== -->
                <div class="tab-pane fade" id="tab-antrol">
                  <div id="antrolBox">
                    <div class="bs-empty"><span class="spinner-border spinner-border-sm"></span></div>
                  </div>
                </div>

                <!-- ===== DOKTER BPJS ===== -->
                <div class="tab-pane fade" id="tab-dokter">
                  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <div class="bs-chips d-flex flex-wrap gap-2" id="dkChips">
                      <button class="btn btn-primary" data-f="">Semua</button>
                      <button class="btn btn-light" data-f="dokter">Dokter (dr/drg)</button>
                      <button class="btn btn-light" data-f="jadwal">Punya jadwal</button>
                      <button class="btn btn-light" data-f="noicare">Belum iCare</button>
                      <button class="btn btn-light" data-f="off">Nonaktif</button>
                    </div>
                    <div class="d-flex flex-wrap gap-2 align-items-center bs-tools">
                      <div class="input-group input-group-sm bs-search">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        <input type="search" class="form-control" id="dkSearch" placeholder="Cari nama / kode" autocomplete="off">
                      </div>
                      <button class="btn btn-primary btn-sm" id="btnSinkron"><i class="fas fa-sync-alt me-1"></i> Sinkron dari PCare</button>
                    </div>
                  </div>
                  <div class="table-responsive">
                    <table class="table align-middle mb-0 bs-table">
                      <thead>
                        <tr>
                          <th>Dokter / Tenaga Medis BPJS</th>
                          <th>Dokter Internal</th>
                          <th class="text-center">Jadwal</th>
                          <th>Akun iCare</th>
                          <th class="text-end">Aksi</th>
                        </tr>
                      </thead>
                      <tbody id="dkBody">
                        <tr>
                          <td colspan="5" class="bs-empty"><span class="spinner-border spinner-border-sm"></span></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                  <div class="small text-muted mt-3"><i class="fas fa-info-circle me-1"></i>Daftar diambil dari PCare. Yang sudah tidak ada di PCare dinonaktifkan saat sinkron (tidak dihapus), sehingga jadwal &amp; akun iCare-nya tetap tersimpan.</div>
                </div>

                <!-- ===== DOKTER INTERNAL ===== -->
                <div class="tab-pane fade" id="tab-internal">
                  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <div class="bs-chips d-flex flex-wrap gap-2" id="inChips">
                      <button class="btn btn-primary" data-f="">Semua</button>
                      <button class="btn btn-light" data-f="masalah">Perlu dicek</button>
                    </div>
                    <div class="d-flex flex-wrap gap-2 align-items-center bs-tools">
                      <div class="input-group input-group-sm bs-search">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        <input type="search" class="form-control" id="inSearch" placeholder="Cari nama dokter" autocomplete="off">
                      </div>
                      <button class="btn btn-primary btn-sm" id="btnTambahInternal"><i class="fas fa-plus me-1"></i> Tambah Dokter</button>
                    </div>
                  </div>
                  <div class="table-responsive">
                    <table class="table align-middle mb-0 bs-table">
                      <thead>
                        <tr>
                          <th>Dokter Internal</th>
                          <th>Dipetakan ke Dokter BPJS</th>
                          <th>Poliklinik</th>
                          <th class="text-end">Aksi</th>
                        </tr>
                      </thead>
                      <tbody id="inBody">
                        <tr>
                          <td colspan="4" class="bs-empty"><span class="spinner-border spinner-border-sm"></span></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                  <div class="small text-muted mt-3"><i class="fas fa-info-circle me-1"></i>Pemetaan menentukan kode dokter yang dikirim ke PCare saat kunjungan pasien BPJS. Pastikan nama dokter internal dan dokter BPJS adalah orang yang sama.</div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <!-- MODAL JADWAL -->
  <div class="modal fade" id="mdJadwal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <div>
            <h5 class="modal-title mb-0"><i class="fas fa-calendar-alt me-2 text-primary"></i>Jadwal Praktik</h5>
            <div class="small text-muted"><b id="jdNama">-</b> · <span id="jdKode">-</span></div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="small text-muted" id="jdInfo"></div>
            <button class="btn btn-primary btn-sm" id="btnJdTambah"><i class="fas fa-plus me-1"></i> Tambah Jadwal</button>
          </div>

          <form class="jd-form mb-3 d-none" id="jdForm" novalidate>
            <input type="hidden" id="jd_id">
            <div class="row g-2 align-items-end">
              <div class="col-6 col-md-2">
                <label class="form-label small required" for="jd_hari">Hari</label>
                <select class="form-select form-select-sm" id="jd_hari">
                  <option value="">Pilih</option>
                  <option>Senin</option>
                  <option>Selasa</option>
                  <option>Rabu</option>
                  <option>Kamis</option>
                  <option>Jumat</option>
                  <option>Sabtu</option>
                  <option>Minggu</option>
                </select>
              </div>
              <div class="col-6 col-md-3">
                <label class="form-label small required" for="jd_poli">Poliklinik</label>
                <select class="form-select form-select-sm" id="jd_poli"></select>
              </div>
              <div class="col-4 col-md-2">
                <label class="form-label small required" for="jd_mulai">Mulai</label>
                <input type="time" class="form-control form-control-sm" id="jd_mulai">
              </div>
              <div class="col-4 col-md-2">
                <label class="form-label small required" for="jd_selesai">Selesai</label>
                <input type="time" class="form-control form-control-sm" id="jd_selesai">
              </div>
              <div class="col-4 col-md-1">
                <label class="form-label small required" for="jd_kuota">Kuota</label>
                <input type="number" class="form-control form-control-sm" id="jd_kuota" min="1" max="9999" value="30">
              </div>
              <div class="col-12 col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-fill" id="btnJdSimpan"><i class="fas fa-save me-1"></i>Simpan</button>
                <button type="button" class="btn btn-light btn-sm" id="btnJdBatal" title="Batal"><i class="fas fa-times"></i></button>
              </div>
            </div>
          </form>

          <div class="table-responsive">
            <table class="table align-middle mb-0 bs-table">
              <thead>
                <tr>
                  <th>Hari</th>
                  <th>Poliklinik</th>
                  <th class="text-center">Jam</th>
                  <th class="text-center">Kuota JKN</th>
                  <th class="text-center">Aktif</th>
                  <th class="text-end">Aksi</th>
                </tr>
              </thead>
              <tbody id="jdBody"></tbody>
            </table>
          </div>
          <div class="small text-muted mt-2"><i class="fas fa-info-circle me-1"></i>Kuota bisa langsung diubah di tabel, tersimpan otomatis saat kolom ditinggalkan.</div>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL iCARE -->
  <div class="modal fade" id="mdIcare" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <form class="modal-content" id="icForm" autocomplete="off" novalidate>
        <div class="modal-header">
          <div>
            <h5 class="modal-title mb-0">Akun iCare</h5>
            <div class="small text-muted" id="icNama">-</div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="ic_id">
          <p class="small text-muted">Akun iCare dipakai dokter untuk membuka riwayat pelayanan peserta JKN dari halaman pemeriksaan.</p>
          <div class="mb-3">
            <label class="form-label required" for="ic_user">Username iCare</label>
            <input type="text" class="form-control" id="ic_user" autocomplete="off" spellcheck="false">
          </div>
          <div class="mb-1">
            <label class="form-label required" for="ic_pass">Password iCare</label>
            <div class="input-group">
              <input type="password" class="form-control" id="ic_pass" autocomplete="new-password">
              <button class="btn btn-light border" type="button" data-eye="#ic_pass" title="Tampilkan"><i class="fas fa-eye"></i></button>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary" id="btnIcSimpan"><i class="fas fa-save me-1"></i> Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL DOKTER INTERNAL -->
  <div class="modal fade" id="mdInternal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <form class="modal-content" id="inForm" autocomplete="off" novalidate>
        <div class="modal-header">
          <h5 class="modal-title" id="inTitle">Tambah Dokter Internal</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="in_id">
          <div class="mb-3">
            <label class="form-label required" for="in_nama">Nama Dokter</label>
            <input type="text" class="form-control" id="in_nama" maxlength="255" placeholder="mis. dr. Andi Pratama">
            <div class="form-text">Nama yang tampil di aplikasi (pendaftaran, resep, cetakan).</div>
          </div>
          <div class="mb-1">
            <label class="form-label required" for="in_kode">Dokter BPJS</label>
            <select class="form-select" id="in_kode"></select>
            <div class="form-text">Dokter BPJS yang sudah dipetakan ke dokter lain tidak bisa dipilih.</div>
          </div>
          <div class="alert alert-warning small py-2 mt-2 mb-0 d-none" id="inWarn"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary" id="btnInSimpan"><i class="fas fa-save me-1"></i> Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <?php require 'library.php'; ?>

  <script>
    (function() {
      const API = 'controller/master/bpjsSetting.php';
      let DOKTER = [];
      let INTERNAL = [];
      let POLI = null;
      let fDok = '';
      let fIn = '';
      let JD = null; // dokter yang jadwalnya sedang dibuka

      const modal = id => bootstrap.Modal.getOrCreateInstance(document.getElementById(id));
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
      } [c]));
      const inisial = n => {
        const w = String(n || '?').replace(/^(drg|dr|bd|ns)\.?\s*/i, '').replace(/,.*$/, '').replace(/[^A-Za-z\s]/g, ' ').trim().split(/\s+/);
        return ((w[0] || '?')[0] + (w[1] ? w[1][0] : '')).toUpperCase();
      };
      const kunci = n => String(n || '').toUpperCase().replace(/,.*$/, '').replace(/^(DRG|DR)\b\.?\s*/, '').replace(/[^A-Z0-9]/g, '');
      const namaSama = (a, b) => {
        const x = kunci(a),
          y = kunci(b);
        return !!x && !!y && (x === y || x.includes(y) || y.includes(x));
      };
      const isDokter = n => /^\s*(drg|dr)\b/i.test(n || '');
      const hari = h => {
        const s = String(h || '').toLowerCase();
        return s ? s[0].toUpperCase() + s.slice(1) : '';
      };

      function api(aksi, data, method) {
        const opt = method === 'GET' || !data ? {
          url: API + '?aksi=' + encodeURIComponent(aksi) + (data ? '&' + $.param(data) : ''),
          type: 'GET'
        } : {
          url: API,
          type: 'POST',
          data: Object.assign({
            aksi
          }, data)
        };
        return $.ajax(Object.assign(opt, {
          dataType: 'json'
        })).then(res => {
          if (!res || !res.success) return $.Deferred().reject(res && res.message ? res.message : 'Gagal');
          return res;
        }, xhr => $.Deferred().reject((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal terhubung ke server.'));
      }
      const gagal = m => Swal.fire('Gagal', typeof m === 'string' ? m : 'Terjadi kesalahan.', 'error');
      const sukses = m => Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: m,
        timer: 1400,
        showConfirmButton: false
      });

      $(document).on('click', '[data-eye]', function() {
        const i = $(this.dataset.eye);
        const show = i.attr('type') === 'password';
        i.attr('type', show ? 'text' : 'password');
        $(this).find('i').attr('class', show ? 'fas fa-eye-slash' : 'fas fa-eye');
      });

      // tab diingat lewat hash
      $('.bs-tabs [data-bs-toggle="tab"]').on('shown.bs.tab', e => history.replaceState(null, '', location.pathname + location.search + e.target.dataset.bsTarget.replace('tab-', '')));
      const h = location.hash && document.querySelector(`.bs-tabs [data-bs-target="${location.hash.replace('#', '#tab-')}"]`);
      if (h) bootstrap.Tab.getOrCreateInstance(h).show();

      // ================= STATUS / PCARE / ANTROL =================
      function kv(k, v, code) {
        return `<div class="kv"><span class="k">${k}</span><span class="v">${v ? (code ? `<code>${esc(v)}</code>` : esc(v)) : '<span class="text-muted fw-normal">-</span>'}</span></div>`;
      }

      function muatStatus() {
        return api('status', null, 'GET').then(res => {
          const p = res.pcare,
            a = res.antrol;
          if (p) {
            $('#pc_user').val(p.username || '');
            $('#pc_passHint').text(p.ada_password ? 'Kosongkan bila password tidak diganti.' : 'Password belum diisi.');
            $('#stPcare').text(p.kode_ppk || 'Terkonfigurasi');
            $('#stPcareSub').text('PCare · ' + (p.username ? 'akun ' + p.username : 'username belum diisi'));
            $('#pcareKred').html(kv('Kode PPK', p.kode_ppk, 1) + kv('Consumer ID', p.cons_id, 1) + kv('Service', p.service, 1) + kv('Server', p.server) + kv('Secret Key', p.secret_key, 1) + kv('User Key', p.user_key, 1));
          } else {
            $('#stPcare').text('Belum diatur');
            $('#stPcareSub').text('Hubungi tim IT');
            $('#icPcare').attr('class', 'bs-icon bg-danger-subtle text-danger');
            $('#pcareKred').html('<div class="bs-empty"><i class="fas fa-exclamation-circle"></i>Kredensial PCare faskes ini belum dibuat. Hubungi tim IT.</div>');
            $('#formPcare :input').prop('disabled', true);
            $('#btnTes').prop('disabled', true);
          }
          if (a) {
            $('#stAntrol').text('Terhubung');
            $('#antrolBox').html(`<div class="row g-3">
              <div class="col-lg-5"><div class="bs-card d-flex flex-column justify-content-center text-center">
                <span class="bs-icon bg-success-subtle text-success mx-auto mb-2" style="width:56px;height:56px;font-size:1.4rem"><i class="fas fa-check"></i></span>
                <div class="fw-semibold mb-1">Terhubung ke Antrean Online BPJS</div>
                <div class="small text-muted">Pasien dapat mengambil antrean lewat Mobile JKN. Konfigurasi dikelola tim IT dan hanya bisa dilihat di sini.</div>
              </div></div>
              <div class="col-lg-7"><div class="bs-card">
                <div class="bs-sec d-flex justify-content-between">Konfigurasi <span class="bs-badge b-mute"><i class="fas fa-lock"></i>Hanya baca</span></div>
                ${kv('Username', a.username)}${kv('Kode PPK', a.kode_ppk, 1)}${kv('Consumer ID', a.cons_id, 1)}${kv('Base URL', a.base_url)}${kv('Service', a.service, 1)}${kv('Secret Key', a.secret_key, 1)}${kv('User Key', a.user_key, 1)}
              </div></div>
            </div>`);
          } else {
            $('#stAntrol').text('Belum terhubung');
            $('#icAntrol').attr('class', 'bs-icon bg-secondary-subtle text-secondary');
            $('#antrolBox').html(`<div class="bs-card text-center py-5">
              <span class="bs-icon bg-warning-subtle text-warning mx-auto mb-2" style="width:56px;height:56px;font-size:1.4rem"><i class="fas fa-hourglass-half"></i></span>
              <div class="fw-semibold mb-1">Antrean Online belum terhubung</div>
              <div class="small text-muted mx-auto" style="max-width:520px">Proses integrasi (bridging) Antrean Online sedang dalam tahap koordinasi menunggu jadwal UAT. Untuk informasi lebih lanjut silakan hubungi tim IT kami.</div>
            </div>`);
          }
        }, m => {
          $('#pcareKred').html(`<div class="bs-empty text-danger"><i class="fas fa-exclamation-circle"></i>${esc(m)}</div>`);
          $('#antrolBox').html(`<div class="bs-empty text-danger"><i class="fas fa-exclamation-circle"></i>${esc(m)}</div>`);
        });
      }

      $('#formPcare').on('submit', function(e) {
        e.preventDefault();
        const user = $('#pc_user').val().trim();
        if (!user) return $('#pc_user').addClass('is-invalid').trigger('focus');
        const btn = $('#btnPcare').prop('disabled', true);
        api('simpan_pcare', {
            username: user,
            password: $('#pc_pass').val()
          })
          .then(res => {
            $('#pc_pass').val('').attr('type', 'password');
            sukses(res.message);
            muatStatus();
          }, gagal)
          .always(() => btn.prop('disabled', false));
      });
      $('#pc_user').on('input', function() {
        $(this).removeClass('is-invalid');
      });

      $('#btnTes').on('click', function() {
        const btn = $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menghubungi PCare…');
        api('tes', {})
          .then(res => Swal.fire({
            icon: 'success',
            title: 'Terhubung',
            text: `PCare merespons dalam ${res.ms} ms. Akun dan kredensial valid.`
          }), m => Swal.fire({
            icon: 'error',
            title: 'Tidak terhubung',
            html: esc(m).replace(/\n/g, '<br>') + '<br><small class="text-muted">Periksa username / password PCare di tab Akun PCare.</small>'
          }))
          .always(() => btn.prop('disabled', false).html('<i class="fas fa-plug me-1"></i> Tes Koneksi PCare'));
      });

      // ================= DOKTER BPJS =================
      function renderDokter() {
        const q = $('#dkSearch').val().trim().toLowerCase();
        const rows = DOKTER.filter(d => {
          if (fDok === 'dokter' && !isDokter(d.nmDokter)) return false;
          if (fDok === 'jadwal' && !d.jadwal) return false;
          if (fDok === 'noicare' && (d.icare_username || d.status != 1)) return false;
          if (fDok === 'off' && d.status == 1) return false;
          return !q || (d.nmDokter + ' ' + d.kdDokter + ' ' + d.internal.join(' ')).toLowerCase().includes(q);
        });
        if (!rows.length) {
          $('#dkBody').html(`<tr><td colspan="5" class="bs-empty"><i class="fas fa-user-md"></i>${DOKTER.length ? 'Tidak ada data yang cocok dengan filter.' : 'Belum ada data dokter BPJS. Klik <b>Sinkron dari PCare</b>.'}</td></tr>`);
          return;
        }
        $('#dkBody').html(rows.map(d => {
          const off = d.status != 1;
          const intern = d.internal.length ?
            d.internal.map(n => `<span class="bs-badge ${namaSama(n, d.nmDokter) ? 'b-ok' : 'b-warn'}" title="${namaSama(n, d.nmDokter) ? 'Dipetakan' : 'Nama berbeda dengan dokter BPJS, cek pemetaannya'}"><i class="fas ${namaSama(n, d.nmDokter) ? 'fa-link' : 'fa-exclamation-triangle'}"></i>${esc(n)}</span>`).join(' ') :
            '<span class="bs-sub">-</span>';
          const jad = d.jadwal ?
            `<div class="fw-semibold">${d.jadwal_aktif}<span class="text-muted fw-normal">/${d.jadwal}</span></div><div class="bs-sub">aktif</div>` :
            '<span class="bs-sub">Belum ada</span>';
          const ic = d.icare_username ?
            `<div class="fw-semibold small">${esc(d.icare_username)}</div>
               <div class="bs-sub pw-cell"><span class="pw" data-pw="${esc(d.icare_password || '')}">••••••••</span>
               <a href="javascript:;" class="ms-1 text-muted pw-eye" title="Tampilkan password"><i class="fas fa-eye"></i></a></div>` :
            '<span class="bs-badge b-mute">Belum diisi</span>';
          return `<tr class="${off ? 'off' : ''}">
            <td><div class="d-flex align-items-center gap-2">
              <span class="bs-ava ${isDokter(d.nmDokter) ? 'b-info' : 'b-mute'}">${esc(inisial(d.nmDokter))}</span>
              <div class="min-w-0"><div class="bs-name" title="${esc(d.nmDokter)}">${esc(d.nmDokter)}</div>
              <div class="bs-sub">Kode ${esc(d.kdDokter)}${off ? ' · <span class="text-danger">tidak ada lagi di PCare</span>' : ''}</div></div>
            </div></td>
            <td><div class="d-flex flex-wrap gap-1">${intern}</div></td>
            <td class="text-center">${jad}</td>
            <td>${ic}</td>
            <td class="text-end keep"><div class="bs-act">
              <button class="btn btn-light text-primary btn-jadwal" data-kode="${esc(d.kdDokter)}"><i class="fas fa-calendar-alt"></i> Jadwal</button>
              <button class="btn btn-light text-dark btn-icare" data-id="${d.id}" title="Atur akun iCare"><i class="fas fa-id-card"></i> iCare</button>
            </div></td>
          </tr>`;
        }).join(''));
      }

      function muatDokter() {
        return api('dokter', null, 'GET').then(res => {
          DOKTER = res.data || [];
          const aktif = DOKTER.filter(d => d.status == 1);
          const dr = aktif.filter(d => isDokter(d.nmDokter));
          $('#nDokter').text(aktif.length);
          $('#stDokter').text(aktif.length + ' tenaga medis');
          $('#stDokterSub').text(`${dr.length} dokter · ${aktif.filter(d => d.icare_username).length} punya iCare`);
          renderDokter();
        }, m => $('#dkBody').html(`<tr><td colspan="5" class="bs-empty text-danger"><i class="fas fa-exclamation-circle"></i>${esc(m)}</td></tr>`));
      }

      let t1;
      $('#dkSearch').on('input', () => {
        clearTimeout(t1);
        t1 = setTimeout(renderDokter, 150);
      });
      $('#dkChips').on('click', '.btn', function() {
        fDok = this.dataset.f;
        $('#dkChips .btn').removeClass('btn-primary').addClass('btn-light');
        $(this).removeClass('btn-light').addClass('btn-primary');
        renderDokter();
      });
      $('#dkBody').on('click', '.pw-eye', function() {
        const s = $(this).siblings('.pw');
        const show = s.text().startsWith('•');
        s.text(show ? s.data('pw') : '••••••••');
        $(this).find('i').attr('class', show ? 'fas fa-eye-slash' : 'fas fa-eye');
      });

      $('#btnSinkron').on('click', function() {
        Swal.fire({
          icon: 'question',
          title: 'Sinkron dokter dari PCare?',
          html: 'Data dokter diambil ulang dari PCare. Dokter baru ditambahkan, nama diperbarui, dan yang sudah tidak ada di PCare akan <b>dinonaktifkan</b>.',
          showCancelButton: true,
          confirmButtonText: 'Sinkron',
          cancelButtonText: 'Batal'
        }).then(r => {
          if (!r.isConfirmed) return;
          const btn = $('#btnSinkron').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Sinkron…');
          api('sinkron', {})
            .then(res => {
              sukses(res.message);
              muatDokter();
              muatInternal();
            }, gagal)
            .always(() => btn.prop('disabled', false).html('<i class="fas fa-sync-alt me-1"></i> Sinkron dari PCare'));
        });
      });

      // ---- iCare ----
      $('#dkBody').on('click', '.btn-icare', function() {
        const d = DOKTER.find(x => String(x.id) === String(this.dataset.id));
        if (!d) return;
        $('#ic_id').val(d.id);
        $('#icNama').text(d.nmDokter + ' · ' + d.kdDokter);
        $('#ic_user').val(d.icare_username || '').removeClass('is-invalid');
        $('#ic_pass').val(d.icare_password || '').attr('type', 'password').removeClass('is-invalid');
        $('#icForm [data-eye] i').attr('class', 'fas fa-eye');
        modal('mdIcare').show();
      });
      $('#icForm').on('submit', function(e) {
        e.preventDefault();
        const u = $('#ic_user').val().trim(),
          p = $('#ic_pass').val();
        if (!u) return $('#ic_user').addClass('is-invalid').trigger('focus');
        if (!p) return $('#ic_pass').addClass('is-invalid').trigger('focus');
        const btn = $('#btnIcSimpan').prop('disabled', true);
        api('simpan_icare', {
            id: $('#ic_id').val(),
            username: u,
            password: p
          })
          .then(res => {
            modal('mdIcare').hide();
            sukses(res.message);
            muatDokter();
          }, gagal)
          .always(() => btn.prop('disabled', false));
      });
      $('#icForm').on('input', '.is-invalid', function() {
        $(this).removeClass('is-invalid');
      });

      // ================= JADWAL =================
      function muatPoli() {
        if (POLI) return $.Deferred().resolve(POLI);
        return api('poli', null, 'GET').then(res => {
          POLI = res.data || [];
          $('#jd_poli').html('<option value="">Pilih poli</option>' + POLI.map(p => `<option value="${esc(p.kdPoli)}">${esc(p.nmPoli)}</option>`).join(''));
          return POLI;
        });
      }

      function muatJadwal() {
        $('#jdBody').html('<tr><td colspan="6" class="bs-empty"><span class="spinner-border spinner-border-sm"></span></td></tr>');
        return api('jadwal', {
          kode: JD.kdDokter
        }, 'GET').then(res => {
          const rows = res.data || [];
          JD.rows = rows;
          const aktif = rows.filter(r => r.status == 1);
          $('#jdInfo').text(rows.length ? `${aktif.length} jadwal aktif dari ${rows.length} · total kuota aktif ${aktif.reduce((a, r) => a + (+r.kuota || 0), 0).toLocaleString('id-ID')}/minggu` : '');
          if (!rows.length) {
            $('#jdBody').html('<tr><td colspan="6" class="bs-empty"><i class="fas fa-calendar-times"></i>Belum ada jadwal. Klik <b>Tambah Jadwal</b>.</td></tr>');
            return;
          }
          $('#jdBody').html(rows.map(r => `<tr class="${r.status == 1 ? '' : 'off'}">
            <td><span class="jd-day">${esc(hari(r.hari))}</span></td>
            <td>${esc(r.nmPoli || r.kdPoli || '-')}</td>
            <td class="text-center fw-semibold">${esc(String(r.mulai || '').slice(0, 5))} – ${esc(String(r.selesai || '').slice(0, 5))}</td>
            <td class="text-center"><input type="number" class="form-control form-control-sm text-center jd-kuota" min="0" max="9999" data-id="${r.id}" data-old="${r.kuota}" value="${r.kuota}"></td>
            <td class="text-center keep"><div class="form-check form-switch d-inline-block m-0"><input class="form-check-input jd-status" type="checkbox" data-id="${r.id}" ${r.status == 1 ? 'checked' : ''}></div></td>
            <td class="text-end keep"><div class="bs-act">
              <button class="btn btn-light text-primary jd-edit" data-id="${r.id}" title="Edit"><i class="fas fa-pen"></i></button>
              <button class="btn btn-light text-danger jd-hapus" data-id="${r.id}" title="Hapus"><i class="fas fa-trash"></i></button>
            </div></td>
          </tr>`).join(''));
        }, m => $('#jdBody').html(`<tr><td colspan="6" class="bs-empty text-danger"><i class="fas fa-exclamation-circle"></i>${esc(m)}</td></tr>`));
      }

      $('#dkBody').on('click', '.btn-jadwal', function() {
        JD = DOKTER.find(x => x.kdDokter === this.dataset.kode);
        if (!JD) return;
        $('#jdNama').text(JD.nmDokter);
        $('#jdKode').text('Kode ' + JD.kdDokter);
        $('#jdForm').addClass('d-none');
        $('#jdInfo').text('');
        modal('mdJadwal').show();
        muatJadwal();
      });
      $('#mdJadwal').on('hidden.bs.modal', () => muatDokter());

      function bukaFormJadwal(r) {
        muatPoli().then(() => {
          $('#jd_id').val(r ? r.id : '');
          $('#jd_hari').val(r ? hari(r.hari) : '');
          $('#jd_poli').val(r ? r.kdPoli : (POLI.length === 1 ? POLI[0].kdPoli : ''));
          $('#jd_mulai').val(r ? String(r.mulai).slice(0, 5) : '');
          $('#jd_selesai').val(r ? String(r.selesai).slice(0, 5) : '');
          $('#jd_kuota').val(r ? r.kuota : 30);
          $('#btnJdSimpan').html(`<i class="fas fa-save me-1"></i>${r ? 'Perbarui' : 'Simpan'}`);
          $('#jdForm').removeClass('d-none').find('.is-invalid').removeClass('is-invalid');
          $('#jd_hari').trigger('focus');
        }, gagal);
      }
      $('#btnJdTambah').on('click', () => bukaFormJadwal(null));
      $('#btnJdBatal').on('click', () => $('#jdForm').addClass('d-none'));
      $('#jdBody').on('click', '.jd-edit', function() {
        bukaFormJadwal(JD.rows.find(r => String(r.id) === String(this.dataset.id)));
      });

      $('#jdForm').on('submit', function(e) {
        e.preventDefault();
        const d = {
          id: $('#jd_id').val(),
          kode: JD.kdDokter,
          hari: $('#jd_hari').val(),
          poli: $('#jd_poli').val(),
          mulai: $('#jd_mulai').val(),
          selesai: $('#jd_selesai').val(),
          kuota: $('#jd_kuota').val()
        };
        const wajib = [
          ['#jd_hari', d.hari],
          ['#jd_poli', d.poli],
          ['#jd_mulai', d.mulai],
          ['#jd_selesai', d.selesai],
          ['#jd_kuota', d.kuota]
        ];
        for (const [sel, v] of wajib) {
          if (!v) return $(sel).addClass('is-invalid').trigger('focus');
        }
        if (d.mulai >= d.selesai) {
          $('#jd_selesai').addClass('is-invalid');
          return Swal.fire('Periksa jam', 'Jam selesai harus lebih besar dari jam mulai.', 'warning');
        }
        const btn = $('#btnJdSimpan').prop('disabled', true);
        api('simpan_jadwal', d)
          .then(res => {
            $('#jdForm').addClass('d-none');
            sukses(res.message);
            muatJadwal();
          }, gagal)
          .always(() => btn.prop('disabled', false));
      });
      $('#jdForm').on('input change', '.is-invalid', function() {
        $(this).removeClass('is-invalid');
      });

      $('#jdBody').on('change', '.jd-kuota', function() {
        const i = $(this);
        const v = i.val(),
          old = String(i.data('old'));
        if (v === old) return;
        if (v === '' || !/^\d+$/.test(v) || +v > 9999) {
          i.val(old);
          return Swal.fire('Kuota tidak valid', 'Isi angka 0–9999.', 'warning');
        }
        i.prop('disabled', true).addClass('saving');
        api('kuota', {
            id: i.data('id'),
            kuota: v
          })
          .then(() => {
            i.data('old', v).removeClass('saving').addClass('saved');
            const r = JD.rows.find(x => String(x.id) === String(i.data('id')));
            if (r) r.kuota = +v;
            setTimeout(() => i.removeClass('saved'), 900);
          }, m => {
            i.val(old).removeClass('saving');
            gagal(m);
          })
          .always(() => i.prop('disabled', false));
      });

      $('#jdBody').on('change', '.jd-status', function() {
        const el = this,
          s = el.checked ? 1 : 0;
        el.disabled = true;
        api('status_jadwal', {
            id: el.dataset.id,
            status: s
          })
          .then(() => {
            const r = JD.rows.find(x => String(x.id) === String(el.dataset.id));
            if (r) r.status = String(s);
            $(el).closest('tr').toggleClass('off', !s);
          }, m => {
            el.checked = !el.checked;
            gagal(m);
          })
          .always(() => el.disabled = false);
      });

      $('#jdBody').on('click', '.jd-hapus', function() {
        const r = JD.rows.find(x => String(x.id) === String(this.dataset.id));
        Swal.fire({
          icon: 'warning',
          title: 'Hapus jadwal?',
          html: r ? `<b>${esc(hari(r.hari))}</b>, ${esc(String(r.mulai).slice(0, 5))}–${esc(String(r.selesai).slice(0, 5))} (${esc(r.nmPoli || '')})<br><small class="text-muted">Bila hanya libur sementara, cukup nonaktifkan.</small>` : '',
          showCancelButton: true,
          confirmButtonColor: '#fa5a5a',
          confirmButtonText: 'Hapus',
          cancelButtonText: 'Batal'
        }).then(x => {
          if (!x.isConfirmed) return;
          api('hapus_jadwal', {
            id: this.dataset.id
          }).then(res => {
            sukses(res.message);
            muatJadwal();
          }, gagal);
        });
      });

      // ================= DOKTER INTERNAL =================
      function masalahInternal(r) {
        if (!r.doctor_code) return ['b-err', 'fa-unlink', 'Belum dipetakan'];
        if (!r.nmDokter) return ['b-err', 'fa-exclamation-circle', `Kode ${r.doctor_code} tidak ada di data BPJS`];
        if (r.bpjs_status != 1) return ['b-warn', 'fa-exclamation-triangle', 'Dokter BPJS nonaktif'];
        if (!namaSama(r.doctor_name, r.nmDokter)) return ['b-warn', 'fa-exclamation-triangle', 'Nama berbeda, pastikan orang yang sama'];
        return null;
      }

      function renderInternal() {
        const q = $('#inSearch').val().trim().toLowerCase();
        const rows = INTERNAL.filter(r => {
          if (fIn === 'masalah' && !masalahInternal(r)) return false;
          return !q || ((r.doctor_name || '') + ' ' + (r.nmDokter || '') + ' ' + (r.doctor_code || '')).toLowerCase().includes(q);
        });
        if (!rows.length) {
          $('#inBody').html(`<tr><td colspan="4" class="bs-empty"><i class="fas fa-user-md"></i>${INTERNAL.length ? 'Tidak ada data yang cocok.' : 'Belum ada dokter internal.'}</td></tr>`);
          return;
        }
        $('#inBody').html(rows.map(r => {
          const m = masalahInternal(r);
          return `<tr class="${r.doctor_status == 1 ? '' : 'off'}">
            <td><div class="d-flex align-items-center gap-2">
              <span class="bs-ava b-info">${esc(inisial(r.doctor_name))}</span>
              <div class="min-w-0"><div class="bs-name">${esc(r.doctor_name || '-')}</div>
              <div class="bs-sub">${r.doctor_status == 1 ? 'Aktif' : 'Nonaktif di Master Dokter'}</div></div>
            </div></td>
            <td>${r.nmDokter ? `<div class="fw-semibold">${esc(r.nmDokter)}</div><div class="bs-sub">Kode ${esc(r.doctor_code)}</div>` : ''}
              ${m ? `<span class="bs-badge ${m[0]} mt-1"><i class="fas ${m[1]}"></i>${esc(m[2])}</span>` : ''}</td>
            <td>${r.nmPoli ? `<span class="bs-badge b-mute">${esc(r.nmPoli)}</span>` : '<span class="bs-sub">-</span>'}</td>
            <td class="text-end keep"><div class="bs-act">
              <button class="btn btn-light text-primary in-edit" data-id="${r.id_doctor}" title="Edit"><i class="fas fa-pen"></i></button>
              <button class="btn btn-light text-danger in-hapus" data-id="${r.id_doctor}" title="Hapus"><i class="fas fa-trash"></i></button>
            </div></td>
          </tr>`;
        }).join(''));
      }

      function muatInternal() {
        return api('internal', null, 'GET').then(res => {
          INTERNAL = res.data || [];
          const aktif = INTERNAL.filter(r => r.doctor_status == 1);
          const ok = aktif.filter(r => !masalahInternal(r)).length;
          const cek = INTERNAL.filter(masalahInternal).length;
          $('#nInternal').text(INTERNAL.length);
          $('#stMap').text(`${ok} / ${aktif.length}`);
          $('#stMapSub').html(cek ? `<span class="text-warning fw-semibold">${cek} pemetaan perlu dicek</span>` : 'Semua dokter internal terpetakan');
          $('#inChips [data-f="masalah"]').html(`Perlu dicek${cek ? ` <span class="badge bg-warning text-dark ms-1">${cek}</span>` : ''}`);
          renderInternal();
        }, m => $('#inBody').html(`<tr><td colspan="4" class="bs-empty text-danger"><i class="fas fa-exclamation-circle"></i>${esc(m)}</td></tr>`));
      }

      let t2;
      $('#inSearch').on('input', () => {
        clearTimeout(t2);
        t2 = setTimeout(renderInternal, 150);
      });
      $('#inChips').on('click', '.btn', function() {
        fIn = this.dataset.f;
        $('#inChips .btn').removeClass('btn-primary').addClass('btn-light');
        $(this).removeClass('btn-light').addClass('btn-primary');
        renderInternal();
      });

      function cekNamaInternal() {
        const kd = $('#in_kode').val();
        const d = DOKTER.find(x => x.kdDokter === kd);
        const nama = $('#in_nama').val().trim();
        const beda = d && nama && !namaSama(nama, d.nmDokter);
        $('#inWarn').toggleClass('d-none', !beda).html(beda ? `<i class="fas fa-exclamation-triangle me-1"></i>Nama berbeda dengan dokter BPJS <b>${esc(d.nmDokter)}</b>. Pastikan ini orang yang sama, karena kode dokter ini yang dikirim ke PCare.` : '');
      }

      function bukaInternal(r) {
        const kodeSendiri = r ? r.doctor_code : null;
        const dipakai = {};
        INTERNAL.forEach(x => {
          if (x.doctor_code && (!r || x.id_doctor !== r.id_doctor)) dipakai[x.doctor_code] = x.doctor_name;
        });
        const aktif = DOKTER.filter(d => d.status == 1 || d.kdDokter === kodeSendiri)
          .sort((a, b) => (isDokter(b.nmDokter) - isDokter(a.nmDokter)) || a.nmDokter.localeCompare(b.nmDokter));
        $('#in_kode').html('<option value="">Pilih dokter BPJS</option>' + aktif.map(d => {
          const lock = dipakai[d.kdDokter] && d.kdDokter !== kodeSendiri;
          return `<option value="${esc(d.kdDokter)}" ${lock ? 'disabled' : ''}>${esc(d.nmDokter)} · ${esc(d.kdDokter)}${lock ? ' (sudah: ' + esc(dipakai[d.kdDokter]) + ')' : ''}</option>`;
        }).join(''));
        $('#in_id').val(r ? r.id_doctor : '');
        $('#in_nama').val(r ? r.doctor_name : '').removeClass('is-invalid');
        $('#in_kode').val(kodeSendiri || '').removeClass('is-invalid');
        $('#inTitle').text(r ? 'Edit Pemetaan Dokter' : 'Tambah Dokter Internal');
        cekNamaInternal();
        modal('mdInternal').show();
      }
      $('#in_nama, #in_kode').on('input change', function() {
        $(this).removeClass('is-invalid');
        cekNamaInternal();
      });

      $('#btnTambahInternal').on('click', () => {
        if (!DOKTER.length) return Swal.fire('Data dokter BPJS kosong', 'Sinkron dokter dari PCare terlebih dahulu di tab Dokter BPJS.', 'info');
        bukaInternal(null);
      });
      $('#inBody').on('click', '.in-edit', function() {
        bukaInternal(INTERNAL.find(x => String(x.id_doctor) === String(this.dataset.id)));
      });

      $('#inForm').on('submit', function(e) {
        e.preventDefault();
        const nama = $('#in_nama').val().trim(),
          kode = $('#in_kode').val();
        if (!nama) return $('#in_nama').addClass('is-invalid').trigger('focus');
        if (!kode) return $('#in_kode').addClass('is-invalid').trigger('focus');
        const btn = $('#btnInSimpan').prop('disabled', true);
        api('simpan_internal', {
            id: $('#in_id').val(),
            nama,
            kode
          })
          .then(res => {
            modal('mdInternal').hide();
            sukses(res.message);
            muatInternal();
            muatDokter();
          }, gagal)
          .always(() => btn.prop('disabled', false));
      });

      $('#inBody').on('click', '.in-hapus', function() {
        const r = INTERNAL.find(x => String(x.id_doctor) === String(this.dataset.id));
        Swal.fire({
          icon: 'warning',
          title: 'Hapus dokter internal?',
          html: `<b>${esc(r ? r.doctor_name : '')}</b> akan dihapus dari master dokter.<br><small class="text-muted">Dokter yang sudah punya riwayat kunjungan tidak bisa dihapus. Nonaktifkan lewat menu Master Dokter.</small>`,
          showCancelButton: true,
          confirmButtonColor: '#fa5a5a',
          confirmButtonText: 'Hapus',
          cancelButtonText: 'Batal'
        }).then(x => {
          if (!x.isConfirmed) return;
          api('hapus_internal', {
            id: this.dataset.id
          }).then(res => {
            sukses(res.message);
            muatInternal();
            muatDokter();
          }, gagal);
        });
      });

      // ================= INIT =================
      muatStatus();
      muatDokter().always(() => muatInternal());
    })();
  </script>
</body>

</html>
