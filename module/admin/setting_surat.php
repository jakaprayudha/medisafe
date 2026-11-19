<?php
$title = 'Penomoran Surat';
require '../../controller/view.php';
?>
<!doctype html>
<html lang="en">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .min-w-0 {
      min-width: 0;
    }

    .ns-sec {
      font-size: .72rem;
      font-weight: 700;
      color: #5a6a85;
      text-transform: uppercase;
      letter-spacing: .4px;
      margin-bottom: .75rem;
    }

    .ns-icon {
      width: 42px;
      height: 42px;
      border-radius: 11px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 1.05rem;
      flex-shrink: 0;
    }

    .ns-pill {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      font-size: .7rem;
      font-weight: 600;
      padding: .25em .65em;
      border-radius: 20px;
      white-space: nowrap;
    }

    /* ---------- mode ---------- */
    .ns-mode {
      border: 1.5px solid #e5eaef;
      border-radius: 14px;
      padding: 16px;
      height: 100%;
      cursor: pointer;
      background: #fff;
      transition: border-color .15s, background .15s, box-shadow .15s;
      display: flex;
      gap: 14px;
      align-items: flex-start;
      text-align: left;
      width: 100%;
    }

    .ns-mode:hover:not(:disabled) {
      box-shadow: 0 4px 14px rgba(0, 0, 0, .05);
      border-color: #c9d2dc;
    }

    .ns-mode.on {
      border-color: var(--c);
      background: var(--bg);
    }

    .ns-mode:disabled {
      cursor: wait;
      opacity: .7;
    }

    .ns-mode .ns-radio {
      width: 20px;
      height: 20px;
      border-radius: 50%;
      border: 2px solid #c9d2dc;
      flex-shrink: 0;
      margin-top: 2px;
      position: relative;
    }

    .ns-mode.on .ns-radio {
      border-color: var(--c);
    }

    .ns-mode.on .ns-radio::after {
      content: '';
      position: absolute;
      inset: 3px;
      border-radius: 50%;
      background: var(--c);
    }

    /* ---------- baris jenis surat ---------- */
    .ns-row {
      border-top: 1px solid #eef1f4;
      padding: 16px 0;
    }

    .ns-row:first-child {
      border-top: 0;
      padding-top: 4px;
    }

    .ns-row.dirty .ns-name::after {
      content: '';
      display: inline-block;
      width: 7px;
      height: 7px;
      border-radius: 50%;
      background: #ffae1f;
      margin-left: 6px;
      vertical-align: middle;
    }

    .ns-name {
      font-weight: 600;
      color: #2a3547;
      font-size: .92rem;
    }

    .ns-format {
      font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
      font-size: .88rem;
    }

    .ns-tokens .btn {
      font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
      font-size: .68rem;
      padding: .1rem .45rem;
      border-radius: 6px;
      background: #f6f8fb;
      border: 1px solid #e5eaef;
      color: #5a6a85;
    }

    .ns-tokens .btn:hover {
      background: #ecf2ff;
      border-color: #5d87ff;
      color: #5d87ff;
    }

    .ns-next {
      background: #f6f8fb;
      border: 1px dashed #d3dbe4;
      border-radius: 10px;
      padding: 8px 12px;
      min-height: 58px;
    }

    .ns-next-val {
      font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
      font-weight: 700;
      color: #2a3547;
      font-size: .95rem;
      word-break: break-all;
    }

    .ns-next.err {
      border-color: #fa896b;
      background: #fff5f2;
    }

    .ns-next.err .ns-next-val {
      color: #fa896b;
      font-family: inherit;
      font-size: .8rem;
      font-weight: 600;
    }

    .ns-num .btn {
      width: 38px;
    }

    .ns-num .form-control {
      text-align: center;
      font-weight: 600;
    }

    .ns-legend code {
      background: #f6f8fb;
      border: 1px solid #e5eaef;
      border-radius: 6px;
      padding: .05rem .4rem;
      color: #5d87ff;
      font-size: .78rem;
      white-space: nowrap;
    }

    /* ---------- bar aksi ---------- */
    .ns-bar {
      position: sticky;
      bottom: 0;
      z-index: 5;
      background: rgba(255, 255, 255, .96);
      backdrop-filter: blur(4px);
      border: 1px solid #e5eaef;
      border-radius: 14px;
      box-shadow: 0 -4px 18px rgba(0, 0, 0, .06);
      padding: 12px 16px;
      margin-bottom: 16px;
    }

    .ns-skel {
      height: 14px;
      border-radius: 6px;
      background: linear-gradient(90deg, #eef1f4 25%, #f6f8fb 50%, #eef1f4 75%);
      background-size: 200% 100%;
      animation: nsSk 1.2s infinite;
    }

    @keyframes nsSk {
      to {
        background-position: -200% 0;
      }
    }

    @media (max-width: 575.98px) {
      .ns-bar .btn {
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
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-3">
              <div class="d-flex flex-wrap align-items-center gap-3">
                <span class="ns-icon bg-primary-subtle text-primary"><i class="fas fa-hashtag"></i></span>
                <div class="min-w-0 flex-grow-1">
                  <h5 class="fw-semibold mb-0">Penomoran Surat</h5>
                  <div class="text-muted small">Atur cara nomor surat dibuat untuk tiap jenis surat di faskes ini.</div>
                </div>
                <div class="text-md-end small" id="nsMeta">
                  <div class="ns-skel" style="width:160px"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- MODE -->
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
              <div class="ns-sec">Metode penomoran</div>
              <div class="row g-3">
                <div class="col-md-6">
                  <button type="button" class="ns-mode" data-mode="AUTO" style="--c:#5d87ff;--bg:#f3f6ff" disabled>
                    <span class="ns-radio"></span>
                    <span class="min-w-0">
                      <span class="d-flex align-items-center gap-2 mb-1">
                        <span class="fw-semibold text-dark">Otomatis</span>
                        <span class="ns-pill bg-primary-subtle text-primary d-none" data-aktif>Aktif</span>
                      </span>
                      <span class="d-block small text-muted">Nomor dibuat sistem dari format di bawah dan naik satu
                        setiap surat baru. Petugas tidak perlu mengetik nomor.</span>
                    </span>
                  </button>
                </div>
                <div class="col-md-6">
                  <button type="button" class="ns-mode" data-mode="MANUAL" style="--c:#13deb9;--bg:#effdfa" disabled>
                    <span class="ns-radio"></span>
                    <span class="min-w-0">
                      <span class="d-flex align-items-center gap-2 mb-1">
                        <span class="fw-semibold text-dark">Manual</span>
                        <span class="ns-pill bg-success-subtle text-success d-none" data-aktif>Aktif</span>
                      </span>
                      <span class="d-block small text-muted">Petugas mengetik nomor surat sendiri di setiap form surat.
                        Cocok bila faskes memakai buku register terpisah.</span>
                    </span>
                  </button>
                </div>
              </div>
              <div class="small text-muted mt-2"><i class="fas fa-info-circle me-1"></i>Perubahan metode langsung
                disimpan dan berlaku untuk surat yang dibuat setelahnya.</div>
            </div>
          </div>

          <div class="card border-0 shadow-sm mb-3" id="nsConfig">
            <div class="card-body">
              <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-2">
                <div>
                  <h6 class="fw-semibold mb-1">Format &amp; nomor terakhir</h6>
                  <div class="small text-muted">Nomor berikutnya = nomor terakhir + 1. Klik penanda untuk
                    menyisipkannya ke format.</div>
                </div>
              </div>
              <div class="alert alert-success-subtle bg-success-subtle text-success small py-2 px-3 d-none"
                id="nsManualNote">
                <i class="fas fa-pen me-1"></i><strong>Mode manual aktif.</strong> Format &amp; nomor di bawah baru
                dipakai bila beralih ke Otomatis, tetapi tetap bisa disiapkan dan disimpan sekarang.
              </div>
              <div id="nsRows">
                <div class="py-4">
                  <div class="ns-skel mb-3" style="width:60%"></div>
                  <div class="ns-skel mb-3" style="width:85%"></div>
                  <div class="ns-skel" style="width:40%"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- PANDUAN -->
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body ns-legend">
              <h6 class="fw-semibold mb-3">Penanda format</h6>
              <div class="row g-2 mb-3">
                <div class="col-md-6 col-xl-4 d-flex gap-2"><code>{NO}</code><span class="small">Nomor urut. Wajib ada agar nomor tiap surat berbeda.</span></div>
                <div class="col-md-6 col-xl-4 d-flex gap-2"><code>{NO:5}</code><span class="small">Nomor urut panjang tetap (00012). Hanya Surat Berobat &amp; Kematian.</span></div>
                <div class="col-md-6 col-xl-4 d-flex gap-2"><code>{ROMAN}</code><span class="small">Bulan romawi (I–XII). Hanya Surat Pemeriksaan Mata.</span></div>
                <div class="col-md-6 col-xl-4 d-flex gap-2"><code>{DD}</code><span class="small">Tanggal 2 digit (01–31).</span></div>
                <div class="col-md-6 col-xl-4 d-flex gap-2"><code>{MM}</code><span class="small">Bulan 2 digit (01–12).</span></div>
                <div class="col-md-6 col-xl-4 d-flex gap-2"><code>{YYYY}</code> <code>{YY}</code><span class="small">Tahun 4 / 2 digit.</span></div>
              </div>
              <div class="small text-muted border-top pt-3 d-flex flex-column flex-lg-row gap-2 gap-lg-4">
                <div><i class="fas fa-exclamation-triangle text-warning me-1"></i>Nomor urut <strong>tidak</strong> kembali ke 1 otomatis tiap bulan/tahun. Untuk memulai dari awal, ubah nomor terakhir menjadi 0.</div>
                <div><i class="fas fa-info-circle me-1"></i>Tombol sisip di tiap baris hanya menampilkan penanda yang didukung jenis surat tersebut.</div>
              </div>
            </div>
          </div>

          <!-- BAR AKSI -->
          <div class="ns-bar d-flex flex-wrap align-items-center gap-2">
            <div class="small me-auto" id="nsBarInfo"><span class="text-muted">Memuat pengaturan…</span></div>
            <button type="button" class="btn btn-light btn-sm" id="nsBtnBatal" disabled>
              <i class="fas fa-undo me-1"></i>Batalkan
            </button>
            <button type="button" class="btn btn-primary btn-sm" id="nsBtnSimpan" disabled>
              <i class="fas fa-save me-1"></i>Simpan Format &amp; Nomor
            </button>
          </div>

        </div>
      </div>
    </div>
  </div>
  <?php require 'library.php'; ?>
  <script>
    (function() {
      const API = 'controller/letter/settingSuratController.php';

      /* harus sama dengan generator nomor di controller surat masing-masing */
      const JENIS = [{
          k: 'sakit',
          nama: 'Surat Keterangan Sakit',
          ikon: 'fa-procedures',
          warna: 'danger',
          bawaan: 'SKS/{NO}/{MM}/{YYYY}',
          pad: 3,
          token: ['NO', 'DD', 'MM', 'YY', 'YYYY']
        },
        {
          k: 'berobat',
          nama: 'Surat Keterangan Berobat',
          ikon: 'fa-stethoscope',
          warna: 'warning',
          bawaan: 'SKB/{NO}/{MM}/{YYYY}',
          pad: 4,
          token: ['NO', 'NO:n', 'DD', 'MM', 'YY', 'YYYY']
        },
        {
          k: 'sehat',
          nama: 'Surat Keterangan Sehat',
          ikon: 'fa-heartbeat',
          warna: 'success',
          bawaan: 'SKH/{NO}/{MM}/{YYYY}',
          pad: 4,
          token: ['NO', 'DD', 'MM', 'YYYY']
        },
        {
          k: 'rawat_inap',
          nama: 'Surat Rawat Inap',
          ikon: 'fa-bed',
          warna: 'primary',
          bawaan: 'SRI/{NO}/{MM}/{YYYY}',
          pad: 3,
          token: ['NO', 'DD', 'MM', 'YY', 'YYYY']
        },
        {
          k: 'kematian',
          nama: 'Surat Keterangan Kematian',
          ikon: 'fa-file-medical',
          warna: 'secondary',
          bawaan: 'SKM/{NO}/{MM}/{YYYY}',
          pad: 0,
          token: ['NO', 'NO:n', 'DD', 'MM', 'YYYY']
        },
        {
          k: 'mata',
          nama: 'Surat Pemeriksaan Mata',
          ikon: 'fa-eye',
          warna: 'info',
          bawaan: 'SPM/{NO}/{MM}/{YYYY}',
          pad: 3,
          token: ['NO', 'MM', 'YYYY', 'ROMAN']
        }
      ];
      const ROMAWI = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];

      let asli = null; // data dari server
      let mode = null;
      let sibuk = false;

      const $ = (s, r = document) => r.querySelector(s);
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
      } [c]));
      const angka = n => Number(n || 0).toLocaleString('id-ID');

      function tglIndo(s) {
        if (!s) return '';
        const d = new Date(String(s).replace(' ', 'T'));
        if (isNaN(d)) return s;
        return d.toLocaleDateString('id-ID', {
          day: 'numeric',
          month: 'short',
          year: 'numeric'
        }) + ' ' + d.toLocaleTimeString('id-ID', {
          hour: '2-digit',
          minute: '2-digit'
        });
      }

      async function api(opts) {
        const r = await fetch(API, Object.assign({
          credentials: 'same-origin',
          cache: 'no-store'
        }, opts || {}));
        let j = null;
        try {
          j = await r.json();
        } catch (e) {}
        if (!r.ok || !j || j.status !== 'success') {
          const e = new Error((j && j.message) || ('Server error (HTTP ' + r.status + ')'));
          e.code = r.status;
          throw e;
        }
        return j.data || {};
      }

      /* ---------- validasi & pratinjau (meniru generator server) ---------- */
      function cekFormat(f, j) {
        if (!f) return 'Format tidak boleh kosong.';
        if (f.length > 100) return 'Maksimal 100 karakter.';
        let adaNo = false;
        for (const m of f.matchAll(/\{([^{}]*)\}/g)) {
          const t = /^NO:\d{1,2}$/.test(m[1]) ? 'NO:n' : m[1];
          if (!j.token.includes(t)) {
            return m[1].toUpperCase() !== m[1] && j.token.includes(m[1].toUpperCase()) ?
              'Tulis penanda dengan huruf besar: {' + m[1].toUpperCase() + '}.' :
              'Penanda {' + m[1] + '} tidak dikenali untuk surat ini.';
          }
          adaNo = adaNo || t === 'NO' || t === 'NO:n';
        }
        if (!adaNo) return 'Wajib memuat {NO} agar setiap surat bernomor unik.';
        if (/[{}]/.test(f.replace(/\{[^{}]*\}/g, ''))) return 'Kurung kurawal tidak berpasangan.';
        return null;
      }

      function buatNomor(f, j, n) {
        const d = new Date();
        const p2 = x => String(x).padStart(2, '0');
        const no = j.pad ? String(n).padStart(j.pad, '0') : String(n);
        return f
          .replace(/\{NO:(\d{1,2})\}/g, (_, w) => String(n).padStart(+w, '0'))
          .replace(/\{NO\}/g, no)
          .replace(/\{YYYY\}/g, d.getFullYear())
          .replace(/\{YY\}/g, String(d.getFullYear()).slice(-2))
          .replace(/\{MM\}/g, p2(d.getMonth() + 1))
          .replace(/\{DD\}/g, p2(d.getDate()))
          .replace(/\{ROMAN\}/g, ROMAWI[d.getMonth()]);
      }

      function bacaNomor(v) {
        v = String(v ?? '').trim().replace(/\./g, '');
        if (v === '') return null;
        if (!/^\d+$/.test(v)) return NaN;
        const n = parseInt(v, 10);
        return n > 99999999 ? NaN : n;
      }

      /* ---------- render ---------- */
      function renderBaris() {
        $('#nsRows').innerHTML = JENIS.map(j => {
          const chips = j.token.map(t => {
            const ins = t === 'NO:n' ? '{NO:4}' : '{' + t + '}';
            return `<button type="button" class="btn" data-ins="${ins}" tabindex="-1">${ins}</button>`;
          }).join('');
          return `
          <div class="ns-row" data-k="${j.k}">
            <div class="row g-3 align-items-start">
              <div class="col-lg-3 col-md-12">
                <div class="d-flex align-items-center gap-2">
                  <span class="ns-icon bg-${j.warna}-subtle text-${j.warna}"><i class="fas ${j.ikon}"></i></span>
                  <div class="min-w-0">
                    <div class="ns-name">${esc(j.nama)}</div>
                    <div class="small text-muted" data-info></div>
                  </div>
                </div>
              </div>
              <div class="col-lg-4 col-md-6">
                <label class="form-label small text-muted mb-1" for="f_${j.k}">Format nomor</label>
                <div class="input-group input-group-sm">
                  <input type="text" class="form-control ns-format" id="f_${j.k}" maxlength="100"
                    autocomplete="off" spellcheck="false" placeholder="${esc(j.bawaan)}">
                  <button type="button" class="btn btn-outline-secondary" data-bawaan title="Kembalikan ke format bawaan (${esc(j.bawaan)})">
                    <i class="fas fa-undo-alt"></i>
                  </button>
                </div>
                <div class="ns-tokens d-flex flex-wrap gap-1 mt-1">${chips}</div>
              </div>
              <div class="col-lg-2 col-md-6 col-sm-5">
                <label class="form-label small text-muted mb-1" for="n_${j.k}">Nomor terakhir</label>
                <div class="input-group input-group-sm ns-num">
                  <button type="button" class="btn btn-outline-secondary" data-step="-1" aria-label="Kurangi">−</button>
                  <input type="text" inputmode="numeric" class="form-control" id="n_${j.k}" autocomplete="off">
                  <button type="button" class="btn btn-outline-secondary" data-step="1" aria-label="Tambah">+</button>
                </div>
                <div class="small mt-1" data-nwarn></div>
              </div>
              <div class="col-lg-3 col-md-12 col-sm-7">
                <div class="form-label small text-muted mb-1">Nomor berikutnya</div>
                <div class="ns-next"><div class="ns-next-val" data-next>-</div><div class="small text-muted" data-nextnote></div></div>
              </div>
            </div>
          </div>`;
        }).join('');
      }

      function isi(d) {
        asli = d;
        JENIS.forEach(j => {
          $('#f_' + j.k).value = d['format_' + j.k] || j.bawaan;
          $('#n_' + j.k).value = String(parseInt(d['nomor_' + j.k] || 0, 10));
        });
        setMode(d.mode_nomor || 'AUTO');
        meta();
        segarkan();
      }

      function meta() {
        const d = asli || {};
        const w = d.updated_at || d.created_at;
        $('#nsMeta').innerHTML = !d.id ?
          '<span class="ns-pill bg-warning-subtle text-warning"><i class="fas fa-circle" style="font-size:.45rem"></i>Belum pernah disimpan, memakai bawaan</span>' :
          (w ? '<span class="text-muted">Terakhir diubah</span><div class="fw-semibold">' + esc(tglIndo(w)) + '</div>' : '');
      }

      function setMode(m) {
        mode = m;
        document.querySelectorAll('.ns-mode').forEach(b => {
          const on = b.dataset.mode === m;
          b.classList.toggle('on', on);
          b.setAttribute('aria-pressed', on);
          b.querySelector('[data-aktif]').classList.toggle('d-none', !on);
          b.disabled = sibuk;
        });
        $('#nsManualNote').classList.toggle('d-none', m !== 'MANUAL');
      }

      /** Hitung status semua baris, perbarui pratinjau dan bar aksi. */
      function segarkan() {
        if (!asli) return;
        let ubah = 0,
          salah = 0;
        JENIS.forEach(j => {
          const row = $('.ns-row[data-k="' + j.k + '"]');
          const fEl = $('#f_' + j.k),
            nEl = $('#n_' + j.k);
          const f = fEl.value.trim();
          const n = bacaNomor(nEl.value);
          const nAsli = parseInt(asli['nomor_' + j.k] || 0, 10);
          const fAsli = asli['format_' + j.k] || j.bawaan;

          const eF = cekFormat(f, j);
          const eN = n === null ? 'Wajib diisi.' : (isNaN(n) ? 'Angka bulat 0 – 99.999.999.' : null);
          fEl.classList.toggle('is-invalid', !!eF);
          nEl.classList.toggle('is-invalid', !!eN);

          const next = row.querySelector('.ns-next');
          next.classList.toggle('err', !!(eF || eN));
          row.querySelector('[data-next]').textContent = eF || eN || buatNomor(f, j, n + 1);
          row.querySelector('[data-nextnote]').textContent = eF || eN ? '' : 'urutan ke-' + angka(n + 1);

          const w = row.querySelector('[data-nwarn]');
          if (!eN && n < nAsli) {
            w.className = 'small mt-1 text-warning';
            w.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i>Turun dari ' + angka(nAsli);
          } else if (!eN && n !== nAsli) {
            w.className = 'small mt-1 text-muted';
            w.textContent = 'Sebelumnya ' + angka(nAsli);
          } else {
            w.className = 'small mt-1 text-danger';
            w.textContent = eN && n !== null ? eN : '';
          }

          row.querySelector('[data-info]').textContent = nAsli ? 'Tersimpan: no. ' + angka(nAsli) : 'Belum ada nomor terpakai';
          row.querySelector('[data-bawaan]').disabled = f === j.bawaan;

          const dirty = f !== fAsli || n !== nAsli;
          row.classList.toggle('dirty', dirty);
          ubah += dirty ? 1 : 0;
          salah += eF || eN ? 1 : 0;
        });

        const info = $('#nsBarInfo');
        if (salah) {
          info.innerHTML = '<span class="text-danger fw-semibold"><i class="fas fa-times-circle me-1"></i>' + salah + ' jenis surat belum valid</span>';
        } else if (ubah) {
          info.innerHTML = '<span class="text-warning fw-semibold"><i class="fas fa-circle me-1" style="font-size:.5rem;vertical-align:middle"></i>' + ubah + ' jenis surat belum disimpan</span>';
        } else {
          info.innerHTML = '<span class="text-success"><i class="fas fa-check-circle me-1"></i>Semua perubahan tersimpan</span>';
        }
        $('#nsBtnSimpan').disabled = sibuk || !ubah || !!salah;
        $('#nsBtnBatal').disabled = sibuk || !ubah;
        return {
          ubah,
          salah
        };
      }

      function adaPerubahan() {
        return !!asli && JENIS.some(j => $('#f_' + j.k).value.trim() !== (asli['format_' + j.k] || j.bawaan) ||
          bacaNomor($('#n_' + j.k).value) !== parseInt(asli['nomor_' + j.k] || 0, 10));
      }

      function gagalMuat(msg) {
        $('#nsRows').innerHTML = `<div class="text-center py-5">
          <div class="text-danger mb-2"><i class="fas fa-exclamation-circle fa-2x"></i></div>
          <div class="fw-semibold">Pengaturan gagal dimuat</div>
          <div class="small text-muted mb-3">${esc(msg)}</div>
          <button type="button" class="btn btn-sm btn-primary" id="nsRetry"><i class="fas fa-redo me-1"></i>Coba lagi</button></div>`;
        $('#nsMeta').innerHTML = '';
        $('#nsBarInfo').innerHTML = '<span class="text-danger">Tidak dapat menyimpan sebelum pengaturan dimuat.</span>';
        $('#nsRetry').onclick = muat;
      }

      async function muat() {
        sibuk = true;
        asli = null;
        $('#nsBtnSimpan').disabled = $('#nsBtnBatal').disabled = true;
        document.querySelectorAll('.ns-mode').forEach(b => b.disabled = true);
        try {
          const d = await api();
          renderBaris();
          sibuk = false;
          isi(d);
        } catch (e) {
          sibuk = false;
          gagalMuat(e.message);
        }
      }

      /* ---------- mode (langsung disimpan) ---------- */
      document.querySelectorAll('.ns-mode').forEach(b => b.addEventListener('click', async () => {
        const m = b.dataset.mode;
        if (!asli || sibuk || m === mode) return;
        const r = await Swal.fire({
          icon: 'question',
          title: m === 'MANUAL' ? 'Beralih ke nomor manual?' : 'Beralih ke nomor otomatis?',
          html: m === 'MANUAL' ?
            'Petugas harus <b>mengetik nomor surat sendiri</b> di setiap form surat mulai sekarang.' :
            'Nomor surat akan <b>dibuat otomatis</b> dari format tersimpan, mulai dari nomor terakhir + 1.' +
            (adaPerubahan() ? '<div class="small text-warning mt-2">Perubahan format/nomor yang belum disimpan tidak ikut terpakai sampai Anda menyimpannya.</div>' : ''),
          showCancelButton: true,
          confirmButtonText: 'Ya, ubah metode',
          cancelButtonText: 'Batal',
          reverseButtons: true
        });
        if (!r.isConfirmed) return;
        sibuk = true;
        setMode(mode);
        segarkan();
        const fd = new FormData();
        fd.append('action', 'update_mode');
        fd.append('mode_nomor', m);
        try {
          const d = await api({
            method: 'POST',
            body: fd
          });
          // hanya mode & waktu yang diperbarui; isian format/nomor yang sedang diedit tidak ditimpa
          asli.mode_nomor = m;
          asli.id = d.id ?? asli.id ?? 1;
          asli.updated_at = d.updated_at || asli.updated_at;
          asli.created_at = d.created_at || asli.created_at;
          sibuk = false;
          setMode(m);
          meta();
          segarkan();
          Swal.fire({
            icon: 'success',
            title: m === 'MANUAL' ? 'Mode manual aktif' : 'Mode otomatis aktif',
            timer: 1500,
            showConfirmButton: false
          });
        } catch (e) {
          sibuk = false;
          setMode(mode);
          segarkan();
          Swal.fire('Gagal', e.message, 'error');
        }
      }));

      /* ---------- interaksi baris ---------- */
      let fokusFormat = null;
      $('#nsRows').addEventListener('focusin', e => {
        if (e.target.classList.contains('ns-format')) fokusFormat = e.target;
      });
      $('#nsRows').addEventListener('input', e => {
        if (e.target.matches('input')) segarkan();
      });
      $('#nsRows').addEventListener('keydown', e => {
        if (e.target.matches('[id^="n_"]') && (e.key === 'ArrowUp' || e.key === 'ArrowDown')) {
          e.preventDefault();
          langkah(e.target, e.key === 'ArrowUp' ? 1 : -1);
        }
        if (e.key === 'Enter' && e.target.matches('input')) {
          e.preventDefault();
          if (!$('#nsBtnSimpan').disabled) simpan();
        }
      });

      function langkah(inp, d) {
        const n = bacaNomor(inp.value);
        inp.value = String(Math.max(0, (isNaN(n) || n === null ? 0 : n) + d));
        segarkan();
      }

      $('#nsRows').addEventListener('click', e => {
        const row = e.target.closest('.ns-row');
        if (!row) return;
        const k = row.dataset.k;
        const j = JENIS.find(x => x.k === k);
        const fEl = $('#f_' + k);
        const ins = e.target.closest('[data-ins]');
        const st = e.target.closest('[data-step]');
        if (ins) {
          const s = fokusFormat === fEl ? fEl.selectionStart : fEl.value.length;
          const t = fokusFormat === fEl ? fEl.selectionEnd : fEl.value.length;
          fEl.value = fEl.value.slice(0, s) + ins.dataset.ins + fEl.value.slice(t);
          fEl.focus();
          const pos = s + ins.dataset.ins.length;
          fEl.setSelectionRange(pos, pos);
          segarkan();
        } else if (st) {
          langkah($('#n_' + k), +st.dataset.step);
        } else if (e.target.closest('[data-bawaan]')) {
          fEl.value = j.bawaan;
          segarkan();
        }
      });

      /* ---------- simpan ---------- */
      async function simpan() {
        const s = segarkan();
        if (!s || !s.ubah || s.salah || sibuk) return;

        const turun = [],
          ubahFormat = [];
        JENIS.forEach(j => {
          const n = bacaNomor($('#n_' + j.k).value);
          const nAsli = parseInt(asli['nomor_' + j.k] || 0, 10);
          if (n < nAsli) turun.push(`<li><b>${esc(j.nama)}</b>: ${angka(nAsli)} → ${angka(n)}</li>`);
          const f = $('#f_' + j.k).value.trim();
          if (f !== (asli['format_' + j.k] || j.bawaan)) {
            ubahFormat.push(`<li><b>${esc(j.nama)}</b>: <code>${esc(buatNomor(f, j, n + 1))}</code></li>`);
          }
        });

        let html = '';
        if (ubahFormat.length) html += '<div class="text-start small mb-2">Nomor berikutnya dengan format baru:<ul class="mb-0 ps-3">' + ubahFormat.join('') + '</ul></div>';
        if (turun.length) {
          html += '<div class="text-start small alert alert-warning py-2 mb-0"><b>Nomor terakhir diturunkan:</b><ul class="mb-1 ps-3">' +
            turun.join('') + '</ul>Nomor yang pernah terbit bisa terpakai lagi. Pastikan formatnya berbeda (mis. tahun/bulan sudah berganti) agar tidak bentrok.</div>';
        }
        const r = await Swal.fire({
          icon: turun.length ? 'warning' : 'question',
          title: 'Simpan ' + s.ubah + ' perubahan?',
          html: html || 'Perubahan nomor terakhir akan disimpan.',
          showCancelButton: true,
          confirmButtonText: 'Simpan',
          cancelButtonText: 'Periksa lagi',
          reverseButtons: true,
          width: 520
        });
        if (!r.isConfirmed) return;

        const fd = new FormData();
        fd.append('action', 'save_setting');
        fd.append('mode_nomor', asli.mode_nomor || mode || 'AUTO');
        JENIS.forEach(j => {
          fd.append('format_' + j.k, $('#f_' + j.k).value.trim());
          fd.append('nomor_' + j.k, String(bacaNomor($('#n_' + j.k).value)));
          if (asli.id) fd.append('nomor_' + j.k + '_awal', String(parseInt(asli['nomor_' + j.k] || 0, 10)));
        });

        sibuk = true;
        segarkan();
        const btn = $('#nsBtnSimpan');
        const label = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan…';
        try {
          const d = await api({
            method: 'POST',
            body: fd
          });
          sibuk = false;
          isi(d);
          Swal.fire({
            icon: 'success',
            title: 'Tersimpan',
            text: 'Format & nomor surat diperbarui.',
            timer: 1500,
            showConfirmButton: false
          });
        } catch (e) {
          sibuk = false;
          segarkan();
          if (e.code === 409) {
            const rr = await Swal.fire({
              icon: 'warning',
              title: 'Nomor sudah berubah',
              text: e.message,
              showCancelButton: true,
              confirmButtonText: 'Muat ulang data',
              cancelButtonText: 'Tutup'
            });
            if (rr.isConfirmed) muat();
          } else {
            Swal.fire('Gagal', e.message, 'error');
          }
        } finally {
          btn.innerHTML = label;
        }
      }

      $('#nsBtnSimpan').addEventListener('click', simpan);
      $('#nsBtnBatal').addEventListener('click', async () => {
        const r = await Swal.fire({
          icon: 'question',
          title: 'Batalkan perubahan?',
          text: 'Isian dikembalikan ke pengaturan tersimpan.',
          showCancelButton: true,
          confirmButtonText: 'Ya, batalkan',
          cancelButtonText: 'Tidak',
          reverseButtons: true
        });
        if (r.isConfirmed && asli) isi(asli);
      });

      window.addEventListener('beforeunload', e => {
        if (adaPerubahan()) {
          e.preventDefault();
          e.returnValue = '';
        }
      });

      muat();
    })();
  </script>
</body>

</html>
