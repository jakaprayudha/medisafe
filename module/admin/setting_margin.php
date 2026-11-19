<?php
$title = 'Margin Harga Jual';
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

    .mg-stat {
      border: 1px solid #e5eaef;
      border-radius: 12px;
      background: #fff;
      padding: 14px 16px;
      height: 100%;
    }

    .mg-icon {
      width: 40px;
      height: 40px;
      border-radius: 10px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 1.05rem;
      flex-shrink: 0;
    }

    .mg-val {
      font-size: 1.25rem;
      font-weight: 700;
      line-height: 1.2;
      color: #2a3547;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .mg-label {
      font-size: .75rem;
      color: #6c757d;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .mg-sec {
      font-size: .72rem;
      font-weight: 700;
      color: #5a6a85;
      text-transform: uppercase;
      letter-spacing: .4px;
      margin-bottom: .75rem;
    }

    /* kartu margin */
    .mg-box {
      border: 1.5px solid #e5eaef;
      border-radius: 14px;
      padding: 16px;
      height: 100%;
      transition: border-color .15s, background .15s;
      position: relative;
    }

    .mg-box.on {
      border-color: var(--c);
      background: var(--bg);
    }

    .mg-box .mg-state {
      position: absolute;
      top: 14px;
      right: 14px;
    }

    .mg-box .form-control {
      font-size: 1.6rem;
      font-weight: 700;
      text-align: right;
      height: 56px;
    }

    .mg-box .input-group-text {
      font-weight: 700;
      font-size: 1.15rem;
      min-width: 48px;
      justify-content: center;
    }

    .mg-chips .btn {
      border-radius: 20px;
      font-size: .75rem;
      padding: .18rem .7rem;
      background: #fff;
      border: 1px solid #e5eaef;
    }

    .mg-chips .btn.active {
      background: var(--c, #5d87ff);
      border-color: var(--c, #5d87ff);
      color: #fff;
    }

    .mg-pill {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      font-size: .7rem;
      font-weight: 600;
      padding: .25em .65em;
      border-radius: 20px;
      white-space: nowrap;
    }

    .p-on {
      color: #13a89e;
      background: #e6fffa;
    }

    .p-off {
      color: #7c8fac;
      background: #f2f5f9;
    }

    .p-dirty {
      color: #c2410c;
      background: #fff4e5;
    }

    /* simulasi */
    .mg-sim {
      background: #f6f9fc;
      border-radius: 14px;
      padding: 16px;
      height: 100%;
    }

    .mg-sim .row-c {
      display: flex;
      justify-content: space-between;
      font-size: .85rem;
      padding: 4px 0;
    }

    .mg-sim .row-c.tot {
      border-top: 1px dashed #d5dde8;
      margin-top: 6px;
      padding-top: 10px;
      font-weight: 700;
      font-size: 1.05rem;
    }

    .seg {
      display: inline-flex;
      background: #e9eef5;
      border-radius: 8px;
      padding: 2px;
    }

    .seg button {
      border: 0;
      background: none;
      font-size: .72rem;
      font-weight: 600;
      color: #5a6a85;
      padding: 3px 10px;
      border-radius: 6px;
    }

    .seg button.active {
      background: #fff;
      color: #5d87ff;
      box-shadow: 0 1px 3px rgba(42, 53, 71, .12);
    }

    /* bar aksi */
    .mg-bar {
      position: sticky;
      bottom: 12px;
      z-index: 20;
      background: #fff;
      border: 1px solid #e5eaef;
      border-radius: 14px;
      box-shadow: 0 6px 24px rgba(42, 53, 71, .10);
      padding: 12px 16px;
    }

    .mg-bar.dirty {
      border-color: #ffae1f;
    }

    /* pratinjau */
    .pv-chips .btn {
      border-radius: 20px;
      font-size: .78rem;
      padding: .28rem .8rem;
    }

    .pv-chips .badge {
      font-weight: 600;
    }

    #pvTable thead th {
      font-size: .72rem;
      font-weight: 600;
      color: #5a6a85;
      text-transform: uppercase;
      letter-spacing: .3px;
      background: #f6f9fc;
      border-bottom: 0;
      white-space: nowrap;
      position: sticky;
      top: 0;
      z-index: 1;
      cursor: pointer;
      user-select: none;
    }

    #pvTable thead th i {
      font-size: .65rem;
      margin-left: 4px;
      opacity: .5;
    }

    #pvTable td {
      font-size: .83rem;
      vertical-align: middle;
    }

    .pv-wrap {
      max-height: 520px;
      overflow: auto;
      border-radius: 10px;
    }

    .pv-name {
      font-weight: 600;
      color: #2a3547;
      max-width: 340px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .mg-badge {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      font-size: .68rem;
      font-weight: 600;
      padding: .2em .55em;
      border-radius: 6px;
      white-space: nowrap;
    }

    .num {
      text-align: right;
      white-space: nowrap;
    }

    .up {
      color: #13a89e;
    }

    .down {
      color: #fa5a5a;
    }

    .mg-empty {
      text-align: center;
      color: #7c8fac;
      padding: 40px 10px;
    }

    .mg-empty i {
      font-size: 1.8rem;
      display: block;
      margin-bottom: 8px;
      opacity: .6;
    }

    .mg-how {
      border: 1px solid #e5eaef;
      border-radius: 12px;
      padding: 14px;
      height: 100%;
      display: flex;
      gap: 12px;
      font-size: .82rem;
      color: #5a6a85;
    }

    .mg-how b {
      color: #2a3547;
    }

    .mg-search {
      width: 230px;
    }

    @media (max-width: 767.98px) {

      .mg-search,
      .mg-tools {
        width: 100%;
      }

      .mg-search {
        flex: 1;
      }
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
              <h4 class="fw-semibold mb-1">Margin Harga Jual Farmasi</h4>
              <div class="text-muted small">Harga jual = harga beli + margin, dibulatkan ke atas (kelipatan Rp 10 di bawah Rp 1.000, Rp 100 selebihnya).</div>
            </div>
            <div class="d-flex gap-2">
              <button class="btn btn-light" id="btnMuatUlang" title="Muat ulang"><i class="fas fa-sync-alt"></i></button>
              <a href="module/admin/pharmacy" class="btn btn-light"><i class="fas fa-prescription-bottle-alt me-1"></i> Master Obat</a>
            </div>
          </div>

          <!-- STAT -->
          <div class="row g-3 mb-3">
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="mg-stat d-flex gap-3 align-items-center">
                <span class="mg-icon bg-secondary-subtle text-secondary" id="icStatus"><i class="fas fa-percentage"></i></span>
                <div class="min-w-0">
                  <div class="mg-val" id="stStatus">-</div>
                  <div class="mg-label" id="stStatusSub">Status margin tersimpan</div>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="mg-stat d-flex gap-3 align-items-center">
                <span class="mg-icon bg-primary-subtle text-primary"><i class="fas fa-prescription-bottle-alt"></i></span>
                <div class="min-w-0">
                  <div class="mg-val" id="stTotal">-</div>
                  <div class="mg-label" id="stTotalSub">Obat milik faskes</div>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="mg-stat d-flex gap-3 align-items-center">
                <span class="mg-icon bg-danger-subtle text-danger"><i class="fas fa-tag"></i></span>
                <div class="min-w-0">
                  <div class="mg-val" id="stNol">-</div>
                  <div class="mg-label">Harga jual masih Rp 0</div>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="mg-stat d-flex gap-3 align-items-center">
                <span class="mg-icon bg-warning-subtle text-warning"><i class="fas fa-file-invoice-dollar"></i></span>
                <div class="min-w-0">
                  <div class="mg-val" id="stTanpaBeli">-</div>
                  <div class="mg-label">Belum ada harga beli</div>
                </div>
              </div>
            </div>
          </div>

          <!-- ATUR MARGIN -->
          <div class="card mb-3">
            <div class="card-body p-4">
              <div class="mg-sec">Atur Margin</div>
              <div class="row g-3">
                <div class="col-md-6 col-xl-4">
                  <div class="mg-box" data-grup="obat" style="--c:#5d87ff;--bg:#f5f8ff">
                    <span class="mg-state" id="stateObat"></span>
                    <div class="d-flex align-items-center gap-2 mb-3">
                      <span class="mg-icon bg-primary-subtle text-primary"><i class="fas fa-pills"></i></span>
                      <div>
                        <div class="fw-semibold">Margin Obat</div>
                        <div class="small text-muted">Obat keras, bebas, ethical, vitamin, dll.</div>
                      </div>
                    </div>
                    <div class="input-group">
                      <input type="text" inputmode="decimal" class="form-control mg-in" id="m_obat" placeholder="—" autocomplete="off">
                      <span class="input-group-text">%</span>
                    </div>
                    <div class="invalid-feedback d-block small" id="errObat"></div>
                    <div class="mg-chips d-flex flex-wrap gap-1 mt-2" data-target="#m_obat" style="--c:#5d87ff">
                      <button type="button" class="btn" data-v="10">10%</button>
                      <button type="button" class="btn" data-v="15">15%</button>
                      <button type="button" class="btn" data-v="20">20%</button>
                      <button type="button" class="btn" data-v="25">25%</button>
                      <button type="button" class="btn" data-v="30">30%</button>
                      <button type="button" class="btn text-muted" data-v="">Manual</button>
                    </div>
                  </div>
                </div>
                <div class="col-md-6 col-xl-4">
                  <div class="mg-box" data-grup="bmhp" style="--c:#13a89e;--bg:#f2fdfb">
                    <span class="mg-state" id="stateBmhp"></span>
                    <div class="d-flex align-items-center gap-2 mb-3">
                      <span class="mg-icon bg-success-subtle text-success"><i class="fas fa-syringe"></i></span>
                      <div>
                        <div class="fw-semibold">Margin BMHP &amp; Alkes</div>
                        <div class="small text-muted">Kategori BMHP, Alkes, Alat Kesehatan</div>
                      </div>
                    </div>
                    <div class="input-group">
                      <input type="text" inputmode="decimal" class="form-control mg-in" id="m_bmhp" placeholder="—" autocomplete="off">
                      <span class="input-group-text">%</span>
                    </div>
                    <div class="invalid-feedback d-block small" id="errBmhp"></div>
                    <div class="mg-chips d-flex flex-wrap gap-1 mt-2" data-target="#m_bmhp" style="--c:#13a89e">
                      <button type="button" class="btn" data-v="10">10%</button>
                      <button type="button" class="btn" data-v="15">15%</button>
                      <button type="button" class="btn" data-v="20">20%</button>
                      <button type="button" class="btn" data-v="25">25%</button>
                      <button type="button" class="btn" data-v="30">30%</button>
                      <button type="button" class="btn text-muted" data-v="">Manual</button>
                    </div>
                  </div>
                </div>
                <div class="col-xl-4">
                  <div class="mg-sim">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <span class="fw-semibold small"><i class="fas fa-calculator me-1 text-primary"></i>Simulasi</span>
                      <div class="seg" id="simGrup">
                        <button type="button" class="active" data-g="obat">Obat</button>
                        <button type="button" data-g="bmhp">BMHP</button>
                      </div>
                    </div>
                    <div class="input-group input-group-sm mb-2">
                      <span class="input-group-text">Harga beli Rp</span>
                      <input type="number" class="form-control text-end" id="exBeli" value="0" min="0" placeholder="0">
                    </div>
                    <div class="row-c"><span class="text-muted">Margin <span id="exPct">-</span></span><span id="exMargin">-</span></div>
                    <div class="row-c"><span class="text-muted">Pembulatan ke atas</span><span id="exBulat">-</span></div>
                    <div class="row-c tot"><span>Harga jual</span><span id="exJual">-</span></div>
                  </div>
                </div>
              </div>

              <div class="row g-3 mt-1">
                <div class="col-md-4">
                  <div class="mg-how"><i class="fas fa-edit text-primary mt-1"></i><span>Saat harga beli / kategori disimpan di <b>Detail Obat</b>. Kolom harga jual terkunci selama margin aktif.</span></div>
                </div>
                <div class="col-md-4">
                  <div class="mg-how"><i class="fas fa-truck-loading text-primary mt-1"></i><span>Saat <b>penerimaan stok</b> dengan harga beli disimpan. Harga beli &amp; harga jual obat ikut diperbarui.</span></div>
                </div>
                <div class="col-md-4">
                  <div class="mg-how"><i class="fas fa-layer-group text-primary mt-1"></i><span>Saat <b>Simpan &amp; Terapkan</b>: semua obat milik faskes yang punya harga beli dihitung ulang sekaligus.</span></div>
                </div>
              </div>
            </div>
          </div>

          <!-- PRATINJAU -->
          <div class="card mb-3">
            <div class="card-body p-4">
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                  <div class="mg-sec mb-0">Pratinjau Harga Jual</div>
                  <div class="small text-muted" id="pvInfo">-</div>
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center mg-tools">
                  <div class="pv-chips d-flex flex-wrap gap-1" id="pvChips">
                    <button class="btn btn-primary" data-f="">Semua <span class="badge bg-white text-primary ms-1" data-n=""></span></button>
                    <button class="btn btn-light" data-f="baru">Dari Rp 0 <span class="badge bg-white text-dark ms-1" data-n="baru"></span></button>
                    <button class="btn btn-light" data-f="naik">Naik <span class="badge bg-white text-dark ms-1" data-n="naik"></span></button>
                    <button class="btn btn-light" data-f="turun">Turun <span class="badge bg-white text-dark ms-1" data-n="turun"></span></button>
                  </div>
                  <div class="input-group input-group-sm mg-search">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="search" class="form-control" id="pvSearch" placeholder="Cari obat" autocomplete="off">
                  </div>
                </div>
              </div>
              <div class="pv-wrap">
                <table class="table align-middle mb-0" id="pvTable">
                  <thead>
                    <tr>
                      <th data-sort="nama">Obat<i class="fas fa-sort"></i></th>
                      <th class="num" data-sort="beli">Harga Beli<i class="fas fa-sort"></i></th>
                      <th class="num" data-sort="jual_lama">Jual Sekarang<i class="fas fa-sort"></i></th>
                      <th class="num" data-sort="jual_baru">Jual Baru<i class="fas fa-sort"></i></th>
                      <th class="num" data-sort="selisih">Selisih<i class="fas fa-sort"></i></th>
                    </tr>
                  </thead>
                  <tbody id="pvBody">
                    <tr>
                      <td colspan="5" class="mg-empty"><span class="spinner-border spinner-border-sm"></span></td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div class="d-flex justify-content-between align-items-center mt-2">
                <div class="small text-muted" id="pvFoot"></div>
                <button class="btn btn-sm btn-light d-none" id="pvMore">Tampilkan lebih banyak</button>
              </div>
            </div>
          </div>

          <!-- BAR AKSI -->
          <div class="mg-bar d-flex flex-wrap justify-content-between align-items-center gap-2" id="mgBar">
            <div class="small" id="barInfo"><span class="text-muted">Memuat…</span></div>
            <div class="d-flex flex-wrap gap-2">
              <button type="button" class="btn btn-light d-none" id="btnBatal"><i class="fas fa-undo me-1"></i> Batalkan</button>
              <button type="button" class="btn btn-outline-primary" id="btnSimpan" disabled><i class="fas fa-save me-1"></i> Simpan Margin</button>
              <button type="button" class="btn btn-primary" id="btnTerapkan" disabled><i class="fas fa-layer-group me-1"></i> Simpan &amp; Terapkan</button>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <?php require 'library.php'; ?>

  <script>
    (function() {
      const API = 'controller/master/marginFarmasiController';
      const LIMIT = 200;
      let SIMPAN = null; // margin tersimpan {obat, bmhp} (null = manual)
      let PV = null; // pratinjau sesuai isi form
      let pvKey = null; // margin yang dipakai pratinjau terakhir
      let pvReq = null;
      let fPv = '';
      let tampil = LIMIT;
      let urut = {
        k: 'nama',
        dir: 1
      };
      let simG = 'obat';
      let sibuk = false;

      const rp = n => 'Rp ' + Math.round(+n || 0).toLocaleString('id-ID');
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
      } [c]));
      /** null = kosong (manual), NaN = tidak valid, angka = margin */
      const baca = sel => {
        const v = String($(sel).val() || '').trim().replace('%', '').replace(',', '.');
        if (v === '') return null;
        return /^\d+(\.\d+)?$/.test(v) ? +v : NaN;
      };
      const aktif = v => typeof v === 'number' && !isNaN(v) && v > 0;
      const norm = v => aktif(v) ? v : null;
      const bulat = x => {
        const k = x < 1000 ? 10 : 100;
        return Math.ceil(x / k) * k;
      };
      const mentahOf = (beli, pct) => Math.round(beli * (1 + pct / 100) * 1e6) / 1e6;
      const form = () => ({
        obat: norm(baca('#m_obat')),
        bmhp: norm(baca('#m_bmhp'))
      });
      const kunci = m => `${m.obat ?? ''}|${m.bmhp ?? ''}`;
      const label = v => v === null ? 'manual' : v + '%';

      // ================= VALIDASI & STATE =================
      function valid() {
        let ok = true;
        [
          ['#m_obat', '#errObat'],
          ['#m_bmhp', '#errBmhp']
        ].forEach(([s, e]) => {
          const v = baca(s);
          const bad = v !== null && (isNaN(v) || v > 500);
          $(s).toggleClass('is-invalid', bad);
          $(e).text(bad ? 'Isi angka 0–500, atau kosongkan untuk harga manual.' : '');
          if (bad) ok = false;
          $(s).closest('.mg-box').toggleClass('on', aktif(v));
          $(s).closest('.mg-box').find('.mg-chips .btn').each(function() {
            const dv = this.dataset.v;
            $(this).toggleClass('active', dv === '' ? !aktif(v) && !bad : aktif(v) && +dv === v);
          });
        });
        return ok;
      }

      const dirty = () => !!SIMPAN && kunci(form()) !== kunci(SIMPAN);

      function pillState(sel, saved, now) {
        const beda = (saved ?? null) !== (now ?? null);
        $(sel).html(beda ? `<span class="mg-pill p-dirty"><i class="fas fa-pen"></i>Belum disimpan</span>` :
          (saved !== null ? `<span class="mg-pill p-on"><i class="fas fa-check"></i>Aktif</span>` : `<span class="mg-pill p-off">Manual</span>`));
      }

      function refreshBar() {
        const ok = valid();
        const f = form();
        const d = dirty();
        if (SIMPAN) {
          pillState('#stateObat', SIMPAN.obat, ok ? f.obat : undefined);
          pillState('#stateBmhp', SIMPAN.bmhp, ok ? f.bmhp : undefined);
        }
        const n = PV && pvKey === kunci(f) ? PV.perubahan.length : null;
        $('#mgBar').toggleClass('dirty', d);
        $('#btnBatal').toggleClass('d-none', !d);
        $('#btnSimpan').prop('disabled', sibuk || !ok || !d);
        $('#btnTerapkan').prop('disabled', sibuk || !ok || n === null || (n === 0 && !d))
          .html(`<i class="fas fa-layer-group me-1"></i> ${n ? `Simpan &amp; Terapkan ke ${n.toLocaleString('id-ID')} Obat` : 'Simpan &amp; Terapkan'}`);
        let info;
        if (!ok) info = '<span class="text-danger"><i class="fas fa-exclamation-circle me-1"></i>Periksa isian margin.</span>';
        else if (d) info = `<span class="text-warning fw-semibold"><i class="fas fa-pen me-1"></i>Perubahan belum disimpan</span> <span class="text-muted">· obat ${label(f.obat)}, BMHP ${label(f.bmhp)}</span>`;
        else if (SIMPAN && (SIMPAN.obat !== null || SIMPAN.bmhp !== null)) info = n ? `<i class="fas fa-info-circle me-1 text-primary"></i><b>${n}</b> obat harga jualnya belum sesuai margin. Klik <b>Terapkan</b> untuk menyamakan.` : '<span class="text-success"><i class="fas fa-check-circle me-1"></i>Semua harga jual sudah sesuai margin.</span>';
        else info = '<span class="text-muted"><i class="fas fa-info-circle me-1"></i>Isi margin agar harga jual dihitung otomatis.</span>';
        $('#barInfo').html(info);
      }

      function stat() {
        if (!SIMPAN) return;
        const on = SIMPAN.obat !== null || SIMPAN.bmhp !== null;
        $('#stStatus').text(on ? 'Aktif' : 'Manual');
        $('#stStatusSub').text(on ? `Obat ${label(SIMPAN.obat)} · BMHP ${label(SIMPAN.bmhp)}` : 'Harga jual diisi manual');
        $('#icStatus').attr('class', `mg-icon ${on ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'}`);
      }

      function statData(d) {
        const R = d.ringkasan;
        $('#stTotal').text((R.obat.total + R.bmhp.total).toLocaleString('id-ID'));
        $('#stTotalSub').text(`${R.obat.total} obat · ${R.bmhp.total} BMHP/alkes`);
        $('#stNol').text((R.obat.tanpa_jual + R.bmhp.tanpa_jual).toLocaleString('id-ID'));
        $('#stTanpaBeli').text(d.tanpa_beli.toLocaleString('id-ID'));
      }

      // ================= SIMULASI =================
      function simulasi() {
        const beli = +$('#exBeli').val() || 0;
        const pct = form()[simG];
        $('#exPct').text(pct === null ? '(manual)' : `${simG === 'bmhp' ? 'BMHP' : 'obat'} ${pct}%`);
        if (pct === null || !beli) {
          $('#exMargin, #exBulat').text('-');
          $('#exJual').text(beli && pct === null ? 'diisi manual' : '-');
          return;
        }
        const m = mentahOf(beli, pct);
        const j = bulat(m);
        $('#exMargin').text('+ ' + rp(m - beli));
        $('#exBulat').text('+ ' + (j - m).toLocaleString('id-ID', {
          maximumFractionDigits: 2
        }));
        $('#exJual').text(rp(j));
      }
      $('#simGrup').on('click', 'button', function() {
        simG = this.dataset.g;
        $('#simGrup button').removeClass('active');
        $(this).addClass('active');
        simulasi();
      });
      $('#exBeli').on('input', simulasi);

      // ================= PRATINJAU =================
      const jenis = r => !r.jual_lama ? 'baru' : (r.jual_baru > r.jual_lama ? 'naik' : (r.jual_baru < r.jual_lama ? 'turun' : 'sama'));

      function renderPv() {
        if (!PV) return;
        const all = PV.perubahan.map(r => Object.assign(r, {
          selisih: r.jual_baru - r.jual_lama,
          jenis: jenis(r)
        }));
        const cnt = {
          '': all.length,
          baru: 0,
          naik: 0,
          turun: 0
        };
        all.forEach(r => cnt[r.jenis] !== undefined && cnt[r.jenis]++);
        $('#pvChips [data-n]').each(function() {
          this.textContent = cnt[this.dataset.n] ?? 0;
        });

        const q = $('#pvSearch').val().trim().toLowerCase();
        const rows = all.filter(r => (!fPv || r.jenis === fPv) && (!q || r.nama.toLowerCase().includes(q)))
          .sort((a, b) => {
            const x = a[urut.k],
              y = b[urut.k];
            return (typeof x === 'string' ? x.localeCompare(y) : x - y) * urut.dir;
          });
        $('#pvTable thead th').each(function() {
          $(this).find('i').attr('class', 'fas ' + (this.dataset.sort === urut.k ? (urut.dir > 0 ? 'fa-sort-up' : 'fa-sort-down') : 'fa-sort'));
        });

        const f = form();
        if (!rows.length) {
          const msg = q || fPv ? 'Tidak ada obat yang cocok dengan filter.' :
            (f.obat === null && f.bmhp === null ? 'Isi margin untuk melihat harga jual baru.' : 'Semua harga jual sudah sesuai margin.');
          $('#pvBody').html(`<tr><td colspan="5" class="mg-empty"><i class="fas ${q || fPv ? 'fa-search' : 'fa-check-circle'}"></i>${msg}</td></tr>`);
          $('#pvMore').addClass('d-none');
        } else {
          $('#pvBody').html(rows.slice(0, tampil).map(r => {
            let sel;
            if (r.jenis === 'baru') sel = '<span class="mg-badge bg-primary-subtle text-primary">Harga baru</span>';
            else if (r.jenis === 'sama') sel = '<span class="mg-badge bg-secondary-subtle text-secondary" title="Harga jual tetap, harga beli master diisi dari penerimaan">Harga beli diisi</span>';
            else sel = `<span class="${r.selisih > 0 ? 'up' : 'down'} fw-semibold">${r.selisih > 0 ? '+' : '−'}${rp(Math.abs(r.selisih))}</span>`;
            return `<tr>
              <td><div class="pv-name" title="${esc(r.nama)}">${esc(r.nama)}</div>
                <div class="d-flex gap-1 mt-1">
                  <span class="mg-badge ${r.grup === 'bmhp' ? 'bg-success-subtle text-success' : 'bg-primary-subtle text-primary'}">${r.grup === 'bmhp' ? 'BMHP' : 'Obat'}</span>
                  ${r.aktif ? '' : '<span class="mg-badge bg-secondary-subtle text-secondary">Nonaktif</span>'}
                  ${r.sumber_beli === 'penerimaan' ? '<span class="mg-badge bg-warning-subtle text-warning" title="Harga beli master kosong, diambil dari penerimaan stok terakhir"><i class="fas fa-truck-loading"></i>beli dari penerimaan</span>' : ''}
                </div></td>
              <td class="num">${rp(r.beli)}</td>
              <td class="num text-muted">${r.jual_lama ? rp(r.jual_lama) : '<span class="text-danger">Rp 0</span>'}</td>
              <td class="num fw-bold">${rp(r.jual_baru)}</td>
              <td class="num">${sel}</td>
            </tr>`;
          }).join(''));
          $('#pvMore').toggleClass('d-none', rows.length <= tampil).text(`Tampilkan lebih banyak (${(rows.length - tampil).toLocaleString('id-ID')} lagi)`);
        }

        const foot = [];
        if (PV.tanpa_beli) foot.push(`${PV.tanpa_beli} obat belum punya harga beli (tidak dihitung).`);
        if (PV.master_bawaan) foot.push(`${PV.master_bawaan} obat master bawaan mengikuti margin saat disimpan dari Detail Obat.`);
        $('#pvFoot').html(foot.map(t => `<i class="fas fa-info-circle me-1"></i>${esc(t)}`).join(' &nbsp; '));
      }

      /** Ambil pratinjau untuk isi form; mengembalikan promise. Pratinjau usang tidak menimpa yang baru. */
      function pratinjau() {
        if (!valid()) return $.Deferred().reject('invalid').promise();
        const f = form();
        const key = kunci(f);
        if (PV && pvKey === key) return $.Deferred().resolve(PV).promise();
        if (pvReq && pvReq.key === key) return pvReq.p;
        $('#pvInfo').text(`Margin obat ${label(f.obat)}, BMHP ${label(f.bmhp)}`);
        $('#pvBody').css('opacity', .45);
        const p = $.getJSON(API, {
          obat: f.obat ?? '',
          bmhp: f.bmhp ?? ''
        }).then(res => {
          if (res.status !== 'success') return $.Deferred().reject(res.message || 'Gagal memuat pratinjau.');
          return res.data;
        }, x => $.Deferred().reject((x.responseJSON && x.responseJSON.message) || 'Gagal memuat pratinjau.'));
        pvReq = {
          key,
          p
        };
        refreshBar();
        p.done(d => {
          if (kunci(form()) !== key) return; // isi form sudah berubah
          PV = d;
          pvKey = key;
          tampil = LIMIT;
          renderPv();
        }).fail(m => {
          if (kunci(form()) !== key) return;
          PV = null;
          pvKey = null;
          $('#pvBody').html(`<tr><td colspan="5" class="mg-empty text-danger"><i class="fas fa-exclamation-circle"></i>${esc(m)}</td></tr>`);
        }).always(() => {
          if (pvReq && pvReq.key === key) pvReq = null;
          $('#pvBody').css('opacity', 1);
          refreshBar();
        });
        return p;
      }

      // ================= MUAT =================
      function muat() {
        $('#pvBody').css('opacity', .45);
        return $.getJSON(API).done(res => {
          if (res.status !== 'success') return;
          const d = res.data;
          SIMPAN = {
            obat: d.aktif.obat,
            bmhp: d.aktif.bmhp
          };
          $('#m_obat').val(SIMPAN.obat ?? '');
          $('#m_bmhp').val(SIMPAN.bmhp ?? '');
          PV = d;
          pvKey = kunci(SIMPAN);
          tampil = LIMIT;
          $('#pvInfo').text(`Margin tersimpan: obat ${label(SIMPAN.obat)}, BMHP ${label(SIMPAN.bmhp)}`);
          stat();
          statData(d);
          simulasi();
          renderPv();
          refreshBar();
        }).fail(x => {
          $('#barInfo').html(`<span class="text-danger"><i class="fas fa-exclamation-circle me-1"></i>${esc((x.responseJSON && x.responseJSON.message) || 'Gagal memuat data margin.')}</span>`);
          $('#pvBody').html('<tr><td colspan="5" class="mg-empty text-danger"><i class="fas fa-exclamation-circle"></i>Gagal memuat data.</td></tr>');
        }).always(() => $('#pvBody').css('opacity', 1));
      }

      // ================= EVENT =================
      let tmr;
      $('.mg-in').on('input', () => {
        refreshBar();
        simulasi();
        clearTimeout(tmr);
        tmr = setTimeout(() => pratinjau().fail(() => {}), 350);
      });
      $('.mg-chips').on('click', '.btn', function() {
        $($(this).parent().data('target')).val(this.dataset.v).trigger('input');
      });
      let t2;
      $('#pvSearch').on('input', () => {
        clearTimeout(t2);
        t2 = setTimeout(() => {
          tampil = LIMIT;
          renderPv();
        }, 150);
      });
      $('#pvChips').on('click', '.btn', function() {
        fPv = this.dataset.f;
        tampil = LIMIT;
        $('#pvChips .btn').removeClass('btn-primary').addClass('btn-light').find('.badge').removeClass('text-primary').addClass('text-dark');
        $(this).removeClass('btn-light').addClass('btn-primary').find('.badge').removeClass('text-dark').addClass('text-primary');
        renderPv();
      });
      $('#pvTable thead').on('click', 'th', function() {
        const k = this.dataset.sort;
        urut = urut.k === k ? {
          k,
          dir: -urut.dir
        } : {
          k,
          dir: k === 'nama' ? 1 : -1
        };
        renderPv();
      });
      $('#pvMore').on('click', () => {
        tampil += LIMIT;
        renderPv();
      });
      $('#btnBatal').on('click', () => {
        if (!SIMPAN) return;
        $('#m_obat').val(SIMPAN.obat ?? '');
        $('#m_bmhp').val(SIMPAN.bmhp ?? '');
        $('#m_obat').trigger('input');
      });
      $('#btnMuatUlang').on('click', () => {
        if (!dirty()) return muat();
        Swal.fire({
          icon: 'question',
          title: 'Buang perubahan?',
          text: 'Isian margin yang belum disimpan akan dikembalikan.',
          showCancelButton: true,
          confirmButtonText: 'Muat ulang',
          cancelButtonText: 'Batal'
        }).then(r => r.isConfirmed && muat());
      });
      window.addEventListener('beforeunload', e => {
        if (dirty() && !sibuk) {
          e.preventDefault();
          e.returnValue = '';
        }
      });

      // ================= SIMPAN =================
      function kirim(terapkan) {
        const f = form();
        sibuk = true;
        refreshBar();
        const btn = $(terapkan ? '#btnTerapkan' : '#btnSimpan');
        btn.html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan…');
        $.post(API, {
            margin_obat: f.obat ?? '',
            margin_bmhp: f.bmhp ?? '',
            terapkan: terapkan ? 1 : ''
          }, null, 'json')
          .done(res => {
            if (res.status !== 'success') return Swal.fire('Gagal', res.message || 'Gagal menyimpan.', 'error');
            Swal.fire({
              icon: 'success',
              title: 'Tersimpan',
              text: res.message,
              timer: 2200,
              showConfirmButton: false
            });
            muat();
          })
          .fail(x => Swal.fire('Gagal', (x.responseJSON && x.responseJSON.message) || 'Tidak dapat terhubung ke server.', 'error'))
          .always(() => {
            sibuk = false;
            $('#btnSimpan').html('<i class="fas fa-save me-1"></i> Simpan Margin');
            refreshBar();
          });
      }

      $('#btnSimpan').on('click', () => {
        if (!valid() || !dirty()) return;
        const f = form();
        const mati = (SIMPAN.obat !== null && f.obat === null) || (SIMPAN.bmhp !== null && f.bmhp === null);
        if (!mati) return kirim(false);
        Swal.fire({
          icon: 'warning',
          title: 'Kembalikan ke harga manual?',
          text: 'Harga jual tidak lagi dihitung otomatis untuk kelompok yang dikosongkan. Harga jual yang sudah ada tidak berubah.',
          showCancelButton: true,
          confirmButtonText: 'Simpan',
          cancelButtonText: 'Batal'
        }).then(r => r.isConfirmed && kirim(false));
      });

      $('#btnTerapkan').on('click', () => {
        if (!valid()) return;
        clearTimeout(tmr);
        $('#btnTerapkan').prop('disabled', true);
        // jumlah di konfirmasi selalu dari pratinjau isi form saat ini
        pratinjau().then(pv => {
          const f = form();
          const n = pv.perubahan.length;
          const turun = pv.perubahan.filter(r => r.jual_lama > 0 && r.jual_baru < r.jual_lama).length;
          const baru = pv.perubahan.filter(r => !r.jual_lama).length;
          return Swal.fire({
            icon: 'question',
            title: n ? `Terapkan ke ${n.toLocaleString('id-ID')} obat?` : 'Simpan margin?',
            html: (n ? `Harga jual ${n.toLocaleString('id-ID')} obat milik faskes dihitung ulang dengan margin obat <b>${label(f.obat)}</b> dan BMHP <b>${label(f.bmhp)}</b>.` : 'Tidak ada harga jual yang berubah.') +
              (baru ? `<div class="small mt-2">${baru} obat yang harga jualnya Rp 0 akan mendapat harga.</div>` : '') +
              (turun ? `<div class="alert alert-warning small py-2 mt-2 mb-0 text-start"><i class="fas fa-exclamation-triangle me-1"></i><b>${turun} obat</b> harga jualnya akan <b>turun</b>. Cek filter "Turun" di pratinjau bila ragu.</div>` : '') +
              '<div class="small text-muted mt-2">Tagihan pasien yang sudah dibuat tidak berubah.</div>',
            showCancelButton: true,
            confirmButtonText: n ? 'Terapkan' : 'Simpan',
            cancelButtonText: 'Batal'
          }).then(r => r.isConfirmed ? kirim(true) : refreshBar());
        }, m => {
          if (m !== 'invalid') Swal.fire('Gagal', typeof m === 'string' ? m : 'Gagal memuat pratinjau.', 'error');
          refreshBar();
        });
      });

      muat();
    })();
  </script>
</body>

</html>
