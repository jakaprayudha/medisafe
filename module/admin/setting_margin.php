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
    .mg-card {
      border: 1px solid #e5eaef;
      border-radius: 12px;
      background: #fff;
      padding: 20px;
      height: 100%;
    }

    .mg-sec {
      font-size: .72rem;
      font-weight: 700;
      color: #5a6a85;
      text-transform: uppercase;
      letter-spacing: .4px;
      margin-bottom: .75rem;
    }

    .mg-input {
      border: 1.5px solid #e5eaef;
      border-radius: 12px;
      padding: 14px;
      transition: border-color .15s;
    }

    .mg-input.on {
      border-color: #5d87ff;
      background: #f8faff;
    }

    .mg-input .form-control {
      font-size: 1.4rem;
      font-weight: 700;
      text-align: right;
    }

    .mg-input .input-group-text {
      font-weight: 700;
      font-size: 1.1rem;
    }

    .mg-chips .btn {
      border-radius: 20px;
      font-size: .75rem;
      padding: .15rem .65rem;
    }

    .mg-ico {
      width: 36px;
      height: 36px;
      border-radius: 10px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .mg-status {
      border-radius: 12px;
      padding: 12px 16px;
      display: flex;
      gap: 12px;
      align-items: center;
    }

    .mg-ex {
      background: #f6f9fc;
      border-radius: 12px;
      padding: 14px 16px;
    }

    .mg-ex .row-c {
      display: flex;
      justify-content: space-between;
      font-size: .85rem;
      padding: 3px 0;
    }

    .mg-ex .row-c.tot {
      border-top: 1px dashed #d5dde8;
      margin-top: 6px;
      padding-top: 8px;
      font-weight: 700;
      font-size: .95rem;
    }

    .mg-step {
      display: flex;
      gap: 10px;
      font-size: .82rem;
      color: #5a6a85;
      padding: 5px 0;
    }

    .mg-step i {
      width: 18px;
      text-align: center;
      color: #5d87ff;
      margin-top: 3px;
    }

    .mg-kpi {
      border: 1px solid #e5eaef;
      border-radius: 10px;
      padding: 10px 12px;
      height: 100%;
    }

    .mg-kpi .v {
      font-size: 1.2rem;
      font-weight: 700;
      color: #2a3547;
      line-height: 1.2;
    }

    .mg-kpi .l {
      font-size: .72rem;
      color: #7c8fac;
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
    }

    #pvTable td {
      font-size: .82rem;
      vertical-align: middle;
    }

    .pv-wrap {
      max-height: 460px;
      overflow: auto;
      border-radius: 10px;
    }

    .pv-name {
      font-weight: 600;
      color: #2a3547;
      max-width: 260px;
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
      padding: 36px 10px;
    }

    .mg-empty i {
      font-size: 1.8rem;
      display: block;
      margin-bottom: 8px;
      opacity: .6;
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

          <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
            <div>
              <h4 class="fw-semibold mb-1">Margin Harga Jual Farmasi</h4>
              <div class="text-muted small">Harga jual obat &amp; BMHP dihitung otomatis dari harga beli + margin, dibulatkan ke atas (Rp 10 untuk harga di bawah Rp 1.000, Rp 100 untuk selebihnya).</div>
            </div>
            <a href="module/admin/pharmacy" class="btn btn-light"><i class="fas fa-prescription-bottle-alt me-1"></i> Master Obat</a>
          </div>

          <div class="mg-status mb-3 bg-light" id="mgStatus">
            <span class="spinner-border spinner-border-sm text-primary"></span><span class="small text-muted">Memuat…</span>
          </div>

          <div class="row g-3">
            <!-- FORM -->
            <div class="col-lg-5">
              <form class="mg-card" id="mgForm" novalidate autocomplete="off">
                <div class="mg-sec">Besaran Margin</div>

                <div class="mg-input mb-3" data-grup="obat">
                  <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="mg-ico bg-primary-subtle text-primary"><i class="fas fa-pills"></i></span>
                    <div>
                      <div class="fw-semibold">Obat</div>
                      <div class="small text-muted">Semua kategori obat (keras, bebas, ethical, vitamin, dll.)</div>
                    </div>
                  </div>
                  <div class="input-group">
                    <input type="text" inputmode="decimal" class="form-control mg-val" id="m_obat" name="margin_obat" placeholder="Nonaktif">
                    <span class="input-group-text">%</span>
                  </div>
                  <div class="mg-chips d-flex flex-wrap gap-1 mt-2" data-target="#m_obat">
                    <button type="button" class="btn btn-light" data-v="10">10%</button>
                    <button type="button" class="btn btn-light" data-v="15">15%</button>
                    <button type="button" class="btn btn-light" data-v="20">20%</button>
                    <button type="button" class="btn btn-light" data-v="25">25%</button>
                    <button type="button" class="btn btn-light" data-v="30">30%</button>
                    <button type="button" class="btn btn-light text-muted" data-v="">Nonaktif</button>
                  </div>
                </div>

                <div class="mg-input mb-3" data-grup="bmhp">
                  <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="mg-ico bg-info-subtle text-info"><i class="fas fa-syringe"></i></span>
                    <div>
                      <div class="fw-semibold">BMHP &amp; Alat Kesehatan</div>
                      <div class="small text-muted">Kategori BMHP, Alkes, Alat Kesehatan</div>
                    </div>
                  </div>
                  <div class="input-group">
                    <input type="text" inputmode="decimal" class="form-control mg-val" id="m_bmhp" name="margin_bmhp" placeholder="Nonaktif">
                    <span class="input-group-text">%</span>
                  </div>
                  <div class="mg-chips d-flex flex-wrap gap-1 mt-2" data-target="#m_bmhp">
                    <button type="button" class="btn btn-light" data-v="10">10%</button>
                    <button type="button" class="btn btn-light" data-v="15">15%</button>
                    <button type="button" class="btn btn-light" data-v="20">20%</button>
                    <button type="button" class="btn btn-light" data-v="25">25%</button>
                    <button type="button" class="btn btn-light" data-v="30">30%</button>
                    <button type="button" class="btn btn-light text-muted" data-v="">Nonaktif</button>
                  </div>
                </div>

                <div class="mg-ex mb-3">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small fw-semibold text-muted">Simulasi</span>
                    <div class="input-group input-group-sm" style="width:170px">
                      <span class="input-group-text">Rp</span>
                      <input type="number" class="form-control" id="exBeli" value="0" min="0" placeholder="0">
                    </div>
                  </div>
                  <div class="row-c"><span class="text-muted">Margin obat <span id="exPct"></span></span><span id="exMargin">-</span></div>
                  <div class="row-c"><span class="text-muted">Pembulatan ke atas</span><span id="exBulat">-</span></div>
                  <div class="row-c tot"><span>Harga jual</span><span id="exJual">-</span></div>
                </div>

                <div class="d-grid gap-2">
                  <button type="button" class="btn btn-primary" id="btnTerapkan"><i class="fas fa-calculator me-1"></i> Simpan &amp; Terapkan ke Semua Obat</button>
                  <button type="submit" class="btn btn-outline-primary" id="btnSimpan"><i class="fas fa-save me-1"></i> Simpan Margin Saja</button>
                </div>

                <hr class="my-3">
                <div class="mg-sec mb-2">Kapan harga jual dihitung otomatis?</div>
                <div class="mg-step"><i class="fas fa-edit"></i><span>Saat harga beli / kategori diubah di <b>Detail Obat</b>. Kolom harga jual terkunci selama margin aktif.</span></div>
                <div class="mg-step"><i class="fas fa-truck-loading"></i><span>Saat <b>penerimaan stok</b> dengan harga beli disimpan. Harga beli terbaru dan harga jual obat ikut diperbarui.</span></div>
                <div class="mg-step"><i class="fas fa-calculator"></i><span>Saat menekan <b>Simpan &amp; Terapkan</b>: semua obat milik faskes yang punya harga beli dihitung ulang.</span></div>
                <div class="mg-step"><i class="fas fa-ban"></i><span>Kosongkan margin bila harga jual ingin diisi manual.</span></div>
              </form>
            </div>

            <!-- PRATINJAU -->
            <div class="col-lg-7">
              <div class="mg-card">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                  <div>
                    <div class="mg-sec mb-0">Pratinjau Perubahan Harga Jual</div>
                    <div class="small text-muted" id="pvInfo">Berdasarkan margin di form.</div>
                  </div>
                  <div class="input-group input-group-sm" style="width:220px">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="search" class="form-control" id="pvSearch" placeholder="Cari obat">
                  </div>
                </div>
                <div class="row g-2 mb-3">
                  <div class="col-6 col-md-3">
                    <div class="mg-kpi">
                      <div class="v" id="kBerubah">-</div>
                      <div class="l">Harga jual akan berubah</div>
                    </div>
                  </div>
                  <div class="col-6 col-md-3">
                    <div class="mg-kpi">
                      <div class="v" id="kAdaBeli">-</div>
                      <div class="l">Obat punya harga beli</div>
                    </div>
                  </div>
                  <div class="col-6 col-md-3">
                    <div class="mg-kpi">
                      <div class="v" id="kTanpaBeli">-</div>
                      <div class="l">Belum ada harga beli</div>
                    </div>
                  </div>
                  <div class="col-6 col-md-3">
                    <div class="mg-kpi">
                      <div class="v" id="kTanpaJual">-</div>
                      <div class="l">Harga jual masih Rp 0</div>
                    </div>
                  </div>
                </div>
                <div class="pv-wrap">
                  <table class="table align-middle mb-0" id="pvTable">
                    <thead>
                      <tr>
                        <th>Obat</th>
                        <th class="num">Harga Beli</th>
                        <th class="num">Jual Sekarang</th>
                        <th class="num">Jual Baru</th>
                      </tr>
                    </thead>
                    <tbody id="pvBody">
                      <tr>
                        <td colspan="4" class="mg-empty"><span class="spinner-border spinner-border-sm"></span></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <div class="small text-muted mt-2" id="pvFoot"></div>
              </div>
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
      let SIMPAN = null; // data margin tersimpan
      let PV = null; // hasil pratinjau terakhir
      let reqId = 0;

      const rp = n => 'Rp ' + Math.round(+n || 0).toLocaleString('id-ID');
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
      } [c]));
      const baca = sel => {
        const v = String($(sel).val() || '').trim().replace(',', '.');
        if (v === '') return null;
        return isNaN(+v) ? NaN : +v;
      };
      const aktif = v => v !== null && !isNaN(v) && v > 0;
      const bulat = x => {
        const k = x < 1000 ? 10 : 100;
        return Math.ceil(x / k) * k;
      };
      const hitung = (beli, pct) => bulat(Math.round(beli * (1 + pct / 100) * 1e6) / 1e6);

      function valid() {
        let ok = true;
        ['#m_obat', '#m_bmhp'].forEach(s => {
          const v = baca(s);
          const bad = v !== null && (isNaN(v) || v < 0 || v > 500);
          $(s).toggleClass('is-invalid', bad);
          if (bad) ok = false;
          $(s).closest('.mg-input').toggleClass('on', aktif(v));
        });
        return ok;
      }

      function simulasi() {
        const beli = +$('#exBeli').val() || 0;
        const pct = baca('#m_obat');
        $('#exPct').text(aktif(pct) ? pct + '%' : '(nonaktif)');
        if (!aktif(pct) || !beli) {
          $('#exMargin, #exBulat').text('-');
          $('#exJual').text(beli && !aktif(pct) ? 'diisi manual' : '-');
          return;
        }
        const mentah = Math.round(beli * (1 + pct / 100) * 1e6) / 1e6;
        const jual = hitung(beli, pct);
        $('#exMargin').text('+ ' + rp(mentah - beli));
        $('#exBulat').text('+ ' + rp(jual - mentah));
        $('#exJual').text(rp(jual));
      }

      function status() {
        if (!SIMPAN) return;
        const o = SIMPAN.aktif.obat,
          b = SIMPAN.aktif.bmhp;
        const on = o !== null || b !== null;
        const teks = on ?
          `<b>Margin aktif</b>: obat ${o !== null ? o + '%' : '<span class="text-muted">manual</span>'} · BMHP &amp; alkes ${b !== null ? b + '%' : '<span class="text-muted">manual</span>'}. Harga jual dihitung otomatis.` :
          '<b>Margin belum aktif.</b> Harga jual obat masih diisi manual satu per satu.';
        $('#mgStatus').attr('class', `mg-status mb-3 ${on ? 'bg-success-subtle' : 'bg-warning-subtle'}`)
          .html(`<span class="mg-ico ${on ? 'bg-success text-white' : 'bg-warning text-white'}"><i class="fas ${on ? 'fa-check' : 'fa-exclamation'}"></i></span><span class="small">${teks}</span>`);
      }

      function renderPv() {
        if (!PV) return;
        const q = $('#pvSearch').val().trim().toLowerCase();
        const rows = PV.perubahan.filter(r => !q || r.nama.toLowerCase().includes(q));
        const R = PV.ringkasan;
        $('#kBerubah').text(PV.perubahan.length.toLocaleString('id-ID'));
        $('#kAdaBeli').text((R.obat.ada_beli + R.bmhp.ada_beli).toLocaleString('id-ID'));
        $('#kTanpaBeli').text(PV.tanpa_beli.toLocaleString('id-ID'));
        $('#kTanpaJual').text((R.obat.tanpa_jual + R.bmhp.tanpa_jual).toLocaleString('id-ID'));
        $('#btnTerapkan').html(`<i class="fas fa-calculator me-1"></i> Simpan &amp; Terapkan ke ${PV.perubahan.length.toLocaleString('id-ID')} Obat`)
          .prop('disabled', !PV.perubahan.length);

        if (!rows.length) {
          const o = baca('#m_obat'),
            b = baca('#m_bmhp');
          const msg = q ? 'Tidak ada obat yang cocok.' :
            (!aktif(o) && !aktif(b) ? 'Isi margin untuk melihat harga jual baru.' : 'Semua harga jual sudah sesuai margin.');
          $('#pvBody').html(`<tr><td colspan="4" class="mg-empty"><i class="fas ${q ? 'fa-search' : 'fa-check-circle'}"></i>${msg}</td></tr>`);
        } else {
          const MAX = 300;
          $('#pvBody').html(rows.slice(0, MAX).map(r => {
            const d = r.jual_baru - r.jual_lama;
            return `<tr>
              <td><div class="pv-name" title="${esc(r.nama)}">${esc(r.nama)}</div>
                <div class="d-flex gap-1 mt-1">
                  <span class="mg-badge ${r.grup === 'bmhp' ? 'bg-info-subtle text-info' : 'bg-primary-subtle text-primary'}">${r.grup === 'bmhp' ? 'BMHP' : 'Obat'}</span>
                  ${r.aktif ? '' : '<span class="mg-badge bg-secondary-subtle text-secondary">Nonaktif</span>'}
                </div></td>
              <td class="num">${rp(r.beli)}${r.sumber_beli === 'penerimaan' ? '<div><span class="mg-badge bg-warning-subtle text-warning" title="Harga beli master kosong, diambil dari penerimaan stok terakhir">dari penerimaan</span></div>' : ''}</td>
              <td class="num text-muted">${r.jual_lama ? rp(r.jual_lama) : '<span class="text-danger">Rp 0</span>'}</td>
              <td class="num"><b>${rp(r.jual_baru)}</b>${r.jual_lama ? `<div class="small ${d >= 0 ? 'up' : 'down'}">${d >= 0 ? '+' : '−'}${rp(Math.abs(d)).replace('Rp ', 'Rp ')}</div>` : ''}</td>
            </tr>`;
          }).join('') + (rows.length > MAX ? `<tr><td colspan="4" class="text-center small text-muted">… dan ${rows.length - MAX} obat lainnya</td></tr>` : ''));
        }
        const foot = [];
        if (PV.tanpa_beli) foot.push(`${PV.tanpa_beli} obat belum punya harga beli sehingga tidak dihitung, isi lewat Detail Obat atau penerimaan stok.`);
        if (PV.master_bawaan) foot.push(`${PV.master_bawaan} obat master bawaan tidak ikut dihitung massal; harganya mengikuti margin saat disimpan dari Detail Obat.`);
        $('#pvFoot').html(foot.map(t => `<div><i class="fas fa-info-circle me-1"></i>${esc(t)}</div>`).join(''));
      }

      function pratinjau() {
        if (!valid()) return;
        const id = ++reqId;
        const o = baca('#m_obat'),
          b = baca('#m_bmhp');
        $('#pvInfo').text(`Margin di form: obat ${aktif(o) ? o + '%' : 'manual'}, BMHP ${aktif(b) ? b + '%' : 'manual'}.`);
        $('#pvBody').css('opacity', .5);
        $.getJSON(API, {
          obat: o ?? '',
          bmhp: b ?? ''
        }).done(res => {
          if (id !== reqId || res.status !== 'success') return;
          PV = res.data;
          renderPv();
        }).fail(x => $('#pvBody').html(`<tr><td colspan="4" class="mg-empty text-danger"><i class="fas fa-exclamation-circle"></i>${esc((x.responseJSON && x.responseJSON.message) || 'Gagal memuat pratinjau.')}</td></tr>`))
          .always(() => id === reqId && $('#pvBody').css('opacity', 1));
      }

      function muat() {
        return $.getJSON(API).done(res => {
          if (res.status !== 'success') return;
          SIMPAN = res.data;
          const fmt = v => v === null || +v === 0 ? '' : String(+v);
          $('#m_obat').val(fmt(SIMPAN.margin_obat));
          $('#m_bmhp').val(fmt(SIMPAN.margin_bmhp));
          PV = res.data;
          valid();
          simulasi();
          status();
          renderPv();
          $('#pvInfo').text('Berdasarkan margin tersimpan.');
        }).fail(x => {
          $('#mgStatus').attr('class', 'mg-status mb-3 bg-danger-subtle').html(`<span class="small text-danger">${esc((x.responseJSON && x.responseJSON.message) || 'Gagal memuat data margin.')}</span>`);
          $('#pvBody').html('<tr><td colspan="4" class="mg-empty">-</td></tr>');
        });
      }

      let tmr;
      $('.mg-val').on('input', () => {
        valid();
        simulasi();
        clearTimeout(tmr);
        tmr = setTimeout(pratinjau, 400);
      });
      $('#exBeli').on('input', simulasi);
      $('.mg-chips').on('click', '.btn', function() {
        $($(this).parent().data('target')).val(this.dataset.v).trigger('input');
      });
      let t2;
      $('#pvSearch').on('input', () => {
        clearTimeout(t2);
        t2 = setTimeout(renderPv, 150);
      });

      function simpan(terapkan) {
        if (!valid()) return Swal.fire('Periksa margin', 'Margin harus angka 0–500 (%), atau kosong untuk nonaktif.', 'warning');
        const o = baca('#m_obat'),
          b = baca('#m_bmhp');
        const kirim = () => {
          const btns = $('#btnSimpan, #btnTerapkan').prop('disabled', true);
          $.post(API, {
              margin_obat: o ?? '',
              margin_bmhp: b ?? '',
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
            .always(() => btns.prop('disabled', false));
        };
        if (!terapkan) return kirim();
        const n = PV ? PV.perubahan.length : 0;
        const turun = PV ? PV.perubahan.filter(r => r.jual_lama > 0 && r.jual_baru < r.jual_lama).length : 0;
        Swal.fire({
          icon: 'question',
          title: `Terapkan ke ${n} obat?`,
          html: `Harga jual ${n} obat milik faskes akan diganti sesuai margin (obat ${aktif(o) ? o + '%' : 'manual'}, BMHP ${aktif(b) ? b + '%' : 'manual'}).${turun ? `<div class="alert alert-warning small py-2 mt-2 mb-0"><b>${turun} obat</b> harga jualnya akan <b>turun</b> dari harga sekarang. Periksa pratinjau bila ragu.</div>` : ''}<br><small class="text-muted">Tagihan pasien yang sudah dibuat tidak berubah.</small>`,
          showCancelButton: true,
          confirmButtonText: 'Terapkan',
          cancelButtonText: 'Batal'
        }).then(r => r.isConfirmed && kirim());
      }
      $('#mgForm').on('submit', e => {
        e.preventDefault();
        simpan(false);
      });
      $('#btnTerapkan').on('click', () => simpan(true));

      muat();
    })();
  </script>
</body>

</html>
