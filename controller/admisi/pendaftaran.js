/*
 * Pendaftaran poliklinik (module/admisi/pendaftaran).
 * Alur: cari pasien -> data lokal tampil langsung -> cek BPJS berjalan di belakang -> pilih poli, dokter, penjamin -> daftar.
 * Field POST ke prosesPendaftaranTunggal.php tetap sama dengan versi sebelumnya.
 */
(function () {
    'use strict';
    const $id = id => document.getElementById(id);
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const BLN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const tglIndo = s => { const m = String(s || '').match(/^(\d{4})-(\d{2})-(\d{2})/) || String(s || '').match(/^(\d{2})-(\d{2})-(\d{4})/); if (!m) return '-'; const [y, mo, d] = m[1].length === 4 ? [m[1], m[2], m[3]] : [m[3], m[2], m[1]]; return (+d) + ' ' + BLN[mo - 1] + ' ' + y; };
    const tglJkt = h => new Date(Date.now() + (h || 0) * 86400000).toLocaleDateString('sv-SE', { timeZone: 'Asia/Jakarta' });
    const inisial = n => String(n || '?').replace(/^\s*(an|by|ny|tn|nn|sdr|sdri|drg?)\.?\s+/i, '').trim().split(/\s+/).slice(0, 2).map(w => w[0] || '').join('').toUpperCase() || '?';
    const API = 'controller/admisi/services/pendaftaranPasien.php';
    const getJSON = (url, data) => $.ajax({ url, data, dataType: 'json', cache: false });

    let P = null;          // pasien terpilih (data lokal)
    let B = null;          // hasil cek BPJS
    let bpjsXhr = null, poliCache = null, providerCache = {}, dokterXhr = null;

    /* =============== PENCARIAN =============== */
    let hasil = [], idx = -1, tCari = null, cariXhr = null, qTerakhir = '';
    function sorot(teks, q) {
        const t = esc(teks);
        if (!q) return t;
        const i = String(teks).toLowerCase().indexOf(q.toLowerCase());
        return i < 0 ? t : esc(teks.slice(0, i)) + '<span class="pd-mark">' + esc(teks.slice(i, i + q.length)) + '</span>' + esc(teks.slice(i + q.length));
    }
    function renderHasil(q) {
        const box = $id('pdHasil');
        if (!hasil.length) {
            box.innerHTML = `<div class="pd-kosong">Pasien "<b>${esc(q)}</b>" tidak ditemukan.<div class="mt-2 d-flex gap-2 justify-content-center">
              <button type="button" class="btn btn-sm btn-primary" data-baru="bpjs"><i class="fas fa-id-card me-1"></i>Pasien baru (cek BPJS)</button>
              <a class="btn btn-sm btn-outline-secondary" href="module/admisi/registrasi-new">Daftar manual</a></div></div>`;
        } else {
            box.innerHTML = hasil.map((r, i) => `<div class="pd-item ${i === idx ? 'aktif' : ''}" data-i="${i}">
              <span class="pd-ava ${r.jk === 'Perempuan' ? 'p' : ''}">${esc(inisial(r.nama))}</span>
              <div class="min-w-0 flex-grow-1">
                <div class="fw-semibold text-truncate">${sorot(r.nama, q)}</div>
                <div class="small text-muted text-truncate"><span class="pd-rm">${sorot(r.rm || '-', q)}</span>
                  ${r.umur !== null ? ' · ' + r.umur + ' th' : ''}${r.jk ? ' · ' + (r.jk === 'Laki-laki' ? 'L' : 'P') : ''}
                  ${r.nik ? ' · NIK ' + sorot(r.nik, q) : ''}${r.bpjs ? ' · BPJS ' + sorot(r.bpjs, q) : ''}</div>
              </div>
              <span class="small text-muted text-nowrap d-none d-md-inline">${r.terakhir ? 'Terakhir ' + tglIndo(r.terakhir) : 'Belum pernah berkunjung'}</span>
            </div>`).join('');
        }
        box.classList.remove('d-none');
    }
    function cari(q) {
        if (cariXhr) cariXhr.abort();
        $id('pdHasil').innerHTML = '<div class="pd-kosong"><span class="spinner-border spinner-border-sm me-2"></span>Mencari…</div>';
        $id('pdHasil').classList.remove('d-none');
        cariXhr = getJSON(API, { aksi: 'cari', q }).done(res => {
            if (q !== qTerakhir) return;
            hasil = (res && res.data) || [];
            idx = hasil.length ? 0 : -1;
            renderHasil(q);
        }).fail((x, s) => { if (s !== 'abort') $id('pdHasil').innerHTML = '<div class="pd-kosong text-danger">Gagal mencari. Coba lagi.</div>'; });
    }
    $id('pdCari').addEventListener('input', function () {
        const q = this.value.trim();
        qTerakhir = q;
        clearTimeout(tCari);
        if (q.length < 2) { $id('pdHasil').classList.add('d-none'); return; }
        tCari = setTimeout(() => cari(q), 250);
    });
    $id('pdCari').addEventListener('keydown', function (e) {
        if ($id('pdHasil').classList.contains('d-none') || !hasil.length) return;
        if (e.key === 'ArrowDown') { e.preventDefault(); idx = Math.min(hasil.length - 1, idx + 1); renderHasil(qTerakhir); scrollAktif(); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); idx = Math.max(0, idx - 1); renderHasil(qTerakhir); scrollAktif(); }
        else if (e.key === 'Enter') { e.preventDefault(); if (hasil[idx]) pilihPasien(hasil[idx].id); }
        else if (e.key === 'Escape') $id('pdHasil').classList.add('d-none');
    });
    const scrollAktif = () => { const el = $id('pdHasil').querySelector('.pd-item.aktif'); if (el) el.scrollIntoView({ block: 'nearest' }); };
    $id('pdHasil').addEventListener('mousedown', e => {
        const it = e.target.closest('.pd-item');
        if (it) { e.preventDefault(); pilihPasien(hasil[+it.dataset.i].id); return; }
        if (e.target.closest('[data-baru]')) { e.preventDefault(); bukaPasienBaru($id('pdCari').value.trim()); }
    });
    $id('pdCari').addEventListener('blur', () => setTimeout(() => $id('pdHasil').classList.add('d-none'), 150));
    $id('pdCari').addEventListener('focus', function () { if (this.value.trim().length >= 2 && hasil.length) renderHasil(qTerakhir); });

    /* =============== PASIEN TERPILIH =============== */
    function pilihPasien(id) {
        $id('pdHasil').classList.add('d-none');
        $id('pdArea').classList.remove('d-none');
        $id('pdNama').textContent = 'Memuat…';
        $id('pdBpjsBox').className = 'pd-bpjs info mb-3';
        $id('pdBpjsBox').innerHTML = '<div class="pd-sk" style="height:56px"></div>';
        if (bpjsXhr) bpjsXhr.abort();
        B = null;
        getJSON(API, { aksi: 'detail', id }).done(res => {
            if (!res || !res.success) { Swal.fire('Gagal', (res && res.message) || 'Data pasien tidak ditemukan.', 'error'); return; }
            P = res.data;
            tampilPasien();
            resetForm();
            cekBpjs();
            window.scrollTo({ top: $id('pdArea').getBoundingClientRect().top + window.scrollY - 90, behavior: 'smooth' });
        }).fail(x => Swal.fire('Gagal', (x.responseJSON && x.responseJSON.message) || 'Gagal memuat data pasien.', 'error'));
    }
    function tampilPasien() {
        $id('pdAva').textContent = inisial(P.nama);
        $id('pdAva').classList.toggle('p', P.jk === 'Perempuan');
        $id('pdNama').textContent = P.nama || '-';
        $id('pdSub').innerHTML = `<span class="pd-rm">${esc(P.rm || '-')}</span>${P.umur !== null ? ' · ' + P.umur + ' tahun' : ''}`;
        $id('pdNik').textContent = P.nik || (P.nik_mentah ? P.nik_mentah + ' (tidak valid)' : '-');
        $id('pdBpjs').textContent = P.bpjs || (P.bpjs_mentah ? P.bpjs_mentah + ' (tidak valid)' : '-');
        $id('pdLahir').textContent = tglIndo(P.lahir);
        $id('pdJk').textContent = P.jk || '-';
        $id('pdHp').value = P.hp || '';
        // field yang dikirim
        $id('noNIK').value = P.nik || P.nik_mentah || '';
        $id('namapatient').value = P.nama || '';
        $id('Kelamin').value = P.jk === 'Perempuan' ? 'Perempuan' : 'Laki - Laki';
        $id('tgllahir').value = P.lahir || '';
        $id('norm').value = P.rm || '';
        $id('id_patient').value = P.id_patient;
        $id('noKartu').value = P.bpjs || '';
        $id('SProlanis').value = P.prolanis || '';
        $id('SPRB').value = P.prb || '';
        const k = P.kunjungan_hari_ini || [];
        $id('pdHariIni').classList.toggle('d-none', !k.length);
        $id('pdHariIni').innerHTML = k.length ? '<i class="fas fa-triangle-exclamation me-1"></i><b>Sudah terdaftar hari ini:</b><ul class="mb-0 ps-3 mt-1">' +
            k.map(v => `<li>${esc(v.id_poli)} · ${esc(v.id_doctor || '-')} · antrian ${esc(v.visit_antrian || '-')}</li>`).join('') + '</ul>' : '';
    }
    function bpjsBox(cls, html) { $id('pdBpjsBox').className = 'pd-bpjs ' + cls + ' mb-3'; $id('pdBpjsBox').innerHTML = html; }
    function cekBpjs() {
        if (!P.bpjs && !P.nik) {
            bpjsBox('info', '<div class="small"><i class="fas fa-circle-info me-1 text-muted"></i>Pasien tidak memiliki NIK / no. BPJS valid — didaftarkan sebagai <b>pasien umum</b>.</div>');
            terapkanPenjamin();
            return;
        }
        bpjsBox('info', '<div class="small text-muted"><span class="spinner-border spinner-border-sm me-2"></span>Mengecek kepesertaan BPJS…</div>');
        const id = P.id_patient;
        bpjsXhr = getJSON(API, { aksi: 'bpjs', id }).done(res => {
            if (!P || P.id_patient !== id) return;
            if (!res || !res.success) {
                B = { aktif: false };
                bpjsBox('no', `<div class="fw-semibold text-danger small"><i class="fas fa-circle-xmark me-1"></i>BPJS tidak dapat diverifikasi</div><div class="small">${esc((res && res.message) || 'Layanan BPJS tidak merespons.')}</div>
                  <div class="small text-muted mt-1">Pasien bisa didaftarkan sebagai pasien umum. <a href="javascript:;" id="pdCekUlang">Cek ulang</a></div>`);
            } else {
                B = Object.assign({ aktif: res.aktif, peringatan: res.peringatan, message: res.message }, res.data);
                if (B.aktif) {
                    $id('noKartu').value = B.noKartu || P.bpjs || '';
                    if (!P.bpjs && B.noKartu) $id('pdBpjs').innerHTML = esc(B.noKartu) + ' <span class="badge bg-info-subtle text-info">dari BPJS</span>';
                    $id('kdProviderPeserta').value = B.kdProvider || '';
                    $id('SProlanis').value = B.prolanis || '';
                    $id('SPRB').value = B.prb || '';
                    if (!$id('pdHp').value && B.noHP) $id('pdHp').value = B.noHP;
                }
                const chip = (t, c) => `<span class="badge bg-${c}-subtle text-${c} me-1">${esc(t)}</span>`;
                // "PEGAWAI SWASTA" -> "Pegawai Swasta", "KELAS I" -> "Kelas 1"
                const SINGKAT = /^(PBI|APBN|APBD|PPU|PBPU|BP|TNI|POLRI|PNS|BUMN|BUMD|PT|CV|RS|RSU|RSUD|UPT|UPTD|BPJS|KK|DR|DRG|II|III|IV)$/i;
                const judul = t => String(t || '').toLowerCase().replace(/(^|[\s\/(-])([a-z])/g, (m, a, b) => a + b.toUpperCase())
                    .replace(/[A-Za-z]+/g, w => SINGKAT.test(w) ? w.toUpperCase() : w);
                const kelas = t => judul(t).replace(/\b(Kelas\s+)(III|II|I)\b/i, (m, a, r) => a + ({ I: 1, II: 2, III: 3 }[r.toUpperCase()]));
                const baris = (lbl, v) => `<div class="pd-bpjs-row"><span>${lbl}</span><b>${v ? esc(v) : '-'}</b></div>`;
                bpjsBox(B.aktif ? 'ok' : 'no', `
                  <div class="d-flex flex-wrap align-items-center gap-2 mb-2"><span class="pd-bpjs-judul ${B.aktif ? 'text-success' : 'text-danger'}"><i class="fas ${B.aktif ? 'fa-circle-check' : 'fa-circle-xmark'} me-1"></i>BPJS ${B.aktif ? 'AKTIF' : 'TIDAK AKTIF'}</span>
                    <span class="pd-bpjs-noka ms-auto">${esc(B.noKartu || '')}</span></div>
                  ${B.aktif ? '' : `<div class="mb-2 text-danger">${esc(B.message || '')}</div>`}
                  ${baris('Segmen Peserta', judul(B.jnsPeserta))}
                  ${baris('Kelas', kelas(B.kelas))}
                  ${baris('Status', judul(B.hubunganKeluarga))}
                  ${baris('FKTP Terdaftar', judul(B.nmProvider))}
                  <div class="mt-2">${B.prolanis ? chip('Prolanis ' + B.prolanis, 'danger') : ''}${B.prb ? chip('PRB ' + B.prb, 'warning') : ''}${+B.tunggakan > 0 ? chip('Tunggakan', 'danger') : ''}</div>
                  ${B.peringatan ? `<div class="text-warning-emphasis mt-1"><i class="fas fa-triangle-exclamation me-1"></i>${esc(B.peringatan)}</div>` : ''}`);
            }
            terapkanPenjamin();
        }).fail((x, s) => {
            if (s === 'abort') return;
            B = { aktif: false };
            bpjsBox('no', '<div class="small text-danger"><i class="fas fa-circle-exclamation me-1"></i>Gagal mengecek BPJS. <a href="javascript:;" id="pdCekUlang">Cek ulang</a></div>');
            terapkanPenjamin();
        });
    }
    $(document).on('click', '#pdCekUlang', () => P && cekBpjs());
    $id('pdGanti').addEventListener('click', () => { batal(); });

    /* =============== FORM =============== */
    function sakit() { return $id('kunjSakit').value === 'true'; }
    function resetForm() {
        $id('pdForm').reset();
        penjaminManual = false;
        $id('paksa').value = '0';
        $id('jSakit').checked = true;
        $id('kunjSakit').value = 'true';
        $id('tanggalKunjung').value = tglJkt(0);
        $id('tanggalKunjung').max = tglJkt(0);
        $id('tanggalKunjung').min = tglJkt(-7);
        $id('kodedokter').value = $id('namadokter').value = $id('namadokterPcare').value = $id('jampraktek').value = '';
        $id('pdError').classList.add('d-none');
        $id('pdForm').querySelectorAll('.is-invalid').forEach(e => e.classList.remove('is-invalid'));
        hitungBmi();
        aturJenis();
    }
    function aturJenis() {
        const s = sakit();
        $id('statusKsehat').classList.toggle('d-none', !s);
        $id('pdTkpWrap').classList.toggle('d-none', !s);
        $id('kunjungan').value = s ? $id('pdTkp').value : '10';
        muatPoli();
        terapkanPenjamin();
        ringkas();
    }
    $id('pdJenis').addEventListener('change', e => { $id('kunjSakit').value = e.target.value; aturJenis(); });
    $id('pdTkp').addEventListener('change', function () { $id('kunjungan').value = this.value; });

    function muatPoli() {
        const s = sakit();
        const isi = list => {
            const pilihan = list.filter(p => p.poliSakit === s);
            const lama = $id('kodepoli').value;
            $id('kodepoli').innerHTML = '<option value="">— Pilih poli —</option>' + pilihan.map(p => `<option value="${esc(p.kdPoli)}">${esc(p.nmPoli)}</option>`).join('');
            if (pilihan.some(p => p.kdPoli === lama)) $id('kodepoli').value = lama;
            else if (pilihan.length === 1) $id('kodepoli').value = pilihan[0].kdPoli;
            gantiPoli();
        };
        if (poliCache) return isi(poliCache);
        $.ajax({ url: 'controller/admisi/services/getPoli.php', type: 'POST', dataType: 'json' })
            .done(res => { poliCache = (res && res.data) || []; isi(poliCache); })
            .fail(() => { $id('kodepoli').innerHTML = '<option value="">Gagal memuat poli</option>'; });
    }
    function gantiPoli() {
        $id('nmPoli').value = $id('kodepoli').value ? $id('kodepoli').selectedOptions[0].text : '';
        $id('kodepoli').classList.remove('is-invalid');
        pilihDokter(null);
        if (sakit()) muatDokter();
        ringkas();
    }
    $id('kodepoli').addEventListener('change', gantiPoli);
    $id('tanggalKunjung').addEventListener('change', () => { if (sakit()) muatDokter(); ringkas(); });

    let dokterList = [];
    function muatDokter() {
        const poli = $id('kodepoli').value, tgl = $id('tanggalKunjung').value;
        if (dokterXhr) dokterXhr.abort();
        if (!poli) { $id('pdDokter').innerHTML = '<div class="small text-muted">Pilih poli terlebih dahulu.</div>'; $id('pdDokterInfo').textContent = ''; return; }
        $id('pdDokter').innerHTML = '<div class="pd-sk" style="height:48px"></div><div class="pd-sk" style="height:48px"></div>';
        const lokal = window.USE_LOCAL_DOKTER !== false;
        const req = lokal ? $.ajax({ url: 'controller/admisi/services/getDokterlocal.php', data: { kdpoli: poli, tanggal: tgl }, dataType: 'json' })
            : $.ajax({ url: 'controller/admisi/services/getApi.php', type: 'POST', data: { url: 'dokter/0/100' }, dataType: 'json' });
        dokterXhr = req.done(res => {
            dokterList = lokal ? ((res && res.data) || []).map(d => ({ kode: String(d.kodedokter), nama: d.namadokter, jam: d.jampraktek, pcare: d.nmDokterBpjs || d.namadokter, tutup: !!d.exp }))
                : ((res && res.list) || []).map(d => ({ kode: String(d.kdDokter), nama: d.nmDokter, jam: '', pcare: d.nmDokter, tutup: false }));
            $id('pdDokterInfo').textContent = lokal && res && res.hari ? 'Jadwal hari ' + res.hari : '';
            renderDokter();
        }).fail((x, s) => { if (s !== 'abort') $id('pdDokter').innerHTML = '<div class="small text-danger">Gagal memuat dokter. <a href="javascript:;" id="pdDokterUlang">Coba lagi</a></div>'; });
    }
    $(document).on('click', '#pdDokterUlang', muatDokter);
    function renderDokter() {
        if (!dokterList.length) { $id('pdDokter').innerHTML = '<div class="small text-warning-emphasis"><i class="fas fa-calendar-xmark me-1"></i>Tidak ada dokter dengan jadwal di poli ini pada tanggal tersebut.</div>'; return; }
        const buka = dokterList.filter(d => !d.tutup);
        $id('pdDokter').innerHTML = dokterList.map(d => `<button type="button" class="pd-dk ${d.kode === $id('kodedokter').value ? 'pilih' : ''}" data-kode="${esc(d.kode)}" ${d.tutup ? 'disabled title="Jam praktik sudah selesai"' : ''}>
            <span class="pd-ava">${esc(inisial(d.nama))}</span>
            <span class="min-w-0"><span class="d-block fw-semibold small text-truncate">${esc(d.nama)}</span>
              <span class="d-block small ${d.tutup ? 'text-danger' : 'text-muted'}">${d.jam ? esc(d.jam) : 'Jadwal PCare'}${d.tutup ? ' · sudah tutup' : ''}</span>
              ${namaBeda(d) ? `<span class="d-block small text-warning-emphasis" title="Kode dokter di master jadwal mengarah ke tenaga lain di BPJS"><i class="fas fa-triangle-exclamation me-1"></i>Di BPJS: ${esc(d.pcare)}</span>` : ''}</span></button>`).join('');
        if (buka.length === 1 && !$id('kodedokter').value) pilihDokter(buka[0]);
    }
    // nama dokter lokal vs nama di master BPJS (kode dokter salah petik -> pendaftaran PCare atas nama orang lain)
    function namaBeda(d) {
        const n = x => String(x || '').toLowerCase().replace(/\b(drg?|bd|ns|amd|keb|kep|kes|skeb|skm)\b\.?/g, ' ').replace(/[^a-z]/g, '');
        const a = n(d.nama), b = n(d.pcare);
        return a && b && !(a.includes(b) || b.includes(a));
    }
    function pilihDokter(d) {
        $id('kodedokter').value = d ? d.kode : '';
        $id('namadokter').value = d ? d.nama : '';
        $id('namadokterPcare').value = d ? d.pcare : '';
        $id('jampraktek').value = d ? d.jam : '';
        $id('pdDokter').querySelectorAll('.pd-dk').forEach(b => b.classList.toggle('pilih', !!d && b.dataset.kode === d.kode));
        $id('pdDokter').classList.remove('border', 'border-danger', 'rounded');
        ringkas();
    }
    $id('pdDokter').addEventListener('click', e => {
        const b = e.target.closest('.pd-dk');
        if (b && !b.disabled) pilihDokter(dokterList.find(d => d.kode === b.dataset.kode));
    });

    /* penjamin: BPJS hanya bila peserta aktif; tipe pasien mengikuti penjamin (seperti sebelumnya) */
    function terapkanPenjamin() {
        const bpjsOk = !!(B && B.aktif);
        const tipe = bpjsOk ? 'BPJS' : 'UMUM';
        const isi = list => {
            const lama = $id('kodeprov').value;
            $id('kodeprov').innerHTML = list.map(p => `<option value="${esc(p.id)}">${esc(p.text)}</option>`).join('');
            const bp = list.find(p => /bpjs kesehatan/i.test(p.text));
            // peserta aktif -> BPJS otomatis, kecuali petugas sudah memilih penjamin sendiri
            if (bpjsOk && bp && !penjaminManual) $id('kodeprov').value = bp.id;
            else if (list.some(p => String(p.id) === String(lama))) $id('kodeprov').value = lama;
            else { const tunai = list.find(p => /tunai|umum/i.test(p.text)); if (tunai) $id('kodeprov').value = tunai.id; }
            setTipe();
        };
        if (providerCache[tipe]) return isi(providerCache[tipe]);
        $.getJSON('controller/admisi/services/get_provider.php', { type: tipe }).done(d => { providerCache[tipe] = d || []; isi(providerCache[tipe]); });
    }
    function setTipe() {
        const nama = ($id('kodeprov').selectedOptions[0] || {}).text || '';
        const tipe = sakit() ? (/bpjs kesehatan/i.test(nama) ? 'BPJS' : 'UMUM') : 'BPJS';
        $id('typePasien').value = tipe;
        $id('pdTipeInfo').innerHTML = tipe === 'BPJS' ? '<i class="fas fa-circle-check text-success me-1"></i>Didaftarkan ke PCare &amp; antrean BPJS' : 'Pasien umum / non-BPJS';
        ringkas();
    }
    let penjaminManual = false;
    $id('kodeprov').addEventListener('change', e => { if (e.isTrusted) penjaminManual = true; setTipe(); });

    /* BMI */
    function hitungBmi() {
        const tb = parseFloat($id('tinggiBadan').value), bb = parseFloat($id('beratBadan').value);
        if (tb >= 30 && bb > 0) {
            const bmi = bb / Math.pow(tb / 100, 2), ket = bmi < 18.5 ? 'Kurus' : bmi < 25 ? 'Normal' : bmi < 30 ? 'Gemuk' : 'Obesitas';
            $id('bmi').value = bmi.toFixed(2); $id('bmiKet').value = ket; $id('pdBmi').textContent = bmi.toFixed(2) + ' · ' + ket;
        } else { $id('bmi').value = ''; $id('bmiKet').value = ''; $id('pdBmi').textContent = '—'; }
    }
    ['tinggiBadan', 'beratBadan'].forEach(i => $id(i).addEventListener('input', hitungBmi));

    function ringkas() {
        const poli = $id('nmPoli').value, dk = $id('namadokter').value, tipe = $id('typePasien').value;
        $id('pdRingkas').innerHTML = poli ? `${esc(poli)}${sakit() && dk ? ' · ' + esc(dk) : ''} · <b>${esc(tipe || '-')}</b>` : '';
    }

    function validasi() {
        const err = [];
        const tandai = (el, pesan) => { el.classList.add('is-invalid'); err.push(pesan); };
        $id('pdForm').querySelectorAll('.is-invalid').forEach(e => e.classList.remove('is-invalid'));
        $id('pdDokter').classList.remove('border', 'border-danger', 'rounded');
        if (!P) err.push('pilih pasien');
        if (!$id('kodepoli').value) tandai($id('kodepoli'), 'poli tujuan');
        if (sakit() && !$id('kodedokter').value) { err.push('dokter'); $id('pdDokter').classList.add('border', 'border-danger', 'rounded'); }
        if (!$id('keluhan').value.trim()) tandai($id('keluhan'), 'keluhan');
        const t = $id('tanggalKunjung').value;
        if (!t || t > tglJkt(0) || t < tglJkt(-7)) tandai($id('tanggalKunjung'), 'tanggal (hari ini s.d. 7 hari ke belakang)');
        if ($id('typePasien').value === 'BPJS' && !(B && B.aktif)) err.push('peserta BPJS tidak aktif / belum terverifikasi — pilih penjamin non-BPJS');
        const hp = $id('pdHp').value.replace(/\D/g, '');
        if ($id('typePasien').value === 'BPJS' && hp.length < 10) tandai($id('pdHp'), 'no. HP (min. 10 digit) untuk antrean BPJS');
        // tanda vital wajib diisi semua + rentang wajar
        const VITAL = [['sistole', 'sistole', 50, 300], ['diastole', 'diastole', 20, 200], ['heartRate', 'nadi', 20, 250], ['respRate', 'respirasi', 4, 80], ['suhu', 'suhu', 30, 45],
            ['saturasiOksigen', 'SpO₂', 50, 100], ['tinggiBadan', 'tinggi badan', 30, 250], ['beratBadan', 'berat badan', 0.5, 350], ['lingkarPerut', 'lingkar perut', 20, 250]];
        const kosong = [], salah = [];
        VITAL.forEach(([k, nm, a, b]) => {
            const el = $id(k), v = el.value.trim().replace(',', '.');
            if (v === '') { el.classList.add('is-invalid'); kosong.push(nm); }
            else if (isNaN(+v) || +v < a || +v > b) { el.classList.add('is-invalid'); salah.push(nm + ' ' + a + '–' + b); }
        });
        if (kosong.length) err.push('tanda vital (' + kosong.join(', ') + ')');
        if (salah.length) err.push('nilai tidak wajar: ' + salah.join(', '));
        if (kosong.length || salah.length) bootstrap.Collapse.getOrCreateInstance($id('pdVitalBox'), { toggle: false }).show();
        $id('pdError').textContent = err.length ? 'Lengkapi: ' + err.join(', ') + '.' : '';
        $id('pdError').classList.toggle('d-none', !err.length);
        return !err.length;
    }

    /* =============== SIMPAN =============== */
    let menyimpan = false;
    $id('pdForm').addEventListener('submit', e => { e.preventDefault(); simpan(); });
    function simpan() {
        if (menyimpan || !validasi()) return;
        $id('nohp').value = $id('pdHp').value.replace(/\D/g, '');
        menyimpan = true;
        const btn = $id('create'), label = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Mendaftarkan…';
        $.ajax({ type: 'POST', url: 'controller/admisi/services/prosesPendaftaranTunggal.php', dataType: 'json', data: $('#pdForm').serialize() })
            .done(res => {
                if (res && res.success) {
                    Swal.fire({
                        icon: 'success', title: 'Pasien terdaftar',
                        html: `<div class="small">${esc(P.nama)} · ${esc($id('nmPoli').value)}${$id('namadokter').value && sakit() ? ' · ' + esc($id('namadokter').value) : ''}</div>` +
                            (res.antian ? `<div class="mt-2">Nomor antrian</div><div style="font-size:2.2rem;font-weight:800;color:#5d87ff">${esc(res.antian)}</div>` : ''),
                        showDenyButton: true, confirmButtonText: '<i class="fas fa-user-plus me-1"></i>Daftar pasien lain', denyButtonText: 'Ke daftar registrasi', denyButtonColor: '#6c757d'
                    }).then(r => { if (r.isDenied) location.assign('module/admisi/registrasi-poliklinik'); else batal(); });
                } else if (res && res.dobel) {
                    Swal.fire({ icon: 'warning', title: 'Sudah terdaftar', text: res.message + ' Tetap daftarkan lagi?', showCancelButton: true, confirmButtonText: 'Ya, daftarkan lagi', cancelButtonText: 'Batal', reverseButtons: true })
                        .then(r => { if (r.isConfirmed) { $id('paksa').value = '1'; simpan(); $id('paksa').value = '0'; } });
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal mendaftar', text: (res && res.message) || 'Terjadi kesalahan.' });
                }
            })
            .fail(xhr => Swal.fire({ icon: 'error', title: 'Gagal menghubungi server', text: 'Periksa koneksi lalu coba lagi. Bila pendaftaran BPJS sempat diproses, cek daftar registrasi sebelum mengulang.' }))
            .always(() => { menyimpan = false; btn.disabled = false; btn.innerHTML = label; });
    }
    function batal() {
        P = null; B = null;
        if (bpjsXhr) bpjsXhr.abort();
        $id('pdArea').classList.add('d-none');
        $id('pdCari').value = '';
        $id('pdCari').focus();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    $id('pdReset').addEventListener('click', batal);

    /* =============== PASIEN BARU (CEK BPJS) =============== */
    const nbModal = bootstrap.Modal.getOrCreateInstance($id('modalCariBPJS'));
    function bukaPasienBaru(nomor) {
        $id('inputNIK').value = /^\d{13,16}$/.test(nomor || '') ? nomor : '';
        $id('formBPJS').classList.add('d-none');
        $id('btnTambahPasien').classList.add('d-none');
        $id('nbHint').innerHTML = '&nbsp;';
        nbModal.show();
        setTimeout(() => $id('inputNIK').focus(), 300);
    }
    $id('btnCariBPJS').addEventListener('click', () => bukaPasienBaru(''));
    $id('inputNIK').addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '');
        const n = this.value.length;
        $id('nbHint').textContent = n === 16 ? 'NIK' : n === 13 ? 'No. kartu BPJS' : n ? n + ' digit (butuh 13 atau 16)' : ' ';
    });
    $id('inputNIK').addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); $id('btnSearchBPJS').click(); } });
    $id('btnSearchBPJS').addEventListener('click', function () {
        const no = $id('inputNIK').value.trim();
        if (no.length !== 13 && no.length !== 16) { $id('nbHint').innerHTML = '<span class="text-danger">Masukkan NIK 16 digit atau no. BPJS 13 digit.</span>'; return; }
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        $id('formBPJS').classList.add('d-none');
        $id('btnTambahPasien').classList.add('d-none');
        $.ajax({ url: 'controller/admisi/services/getPasien.php', data: { tipe: no.length === 16 ? 'nik' : 'noka', nokartu: no }, dataType: 'json' })
            .done(res => {
                const h = (res && res.success && res.data) ? res : (res && res.result && res.result.success ? res.result : null);
                if (!h) {
                    Swal.fire({ icon: 'warning', title: 'Peserta tidak ditemukan', text: (res && res.message) || 'Peserta BPJS tidak ditemukan / tidak aktif.', showDenyButton: true, confirmButtonText: 'Daftar manual', denyButtonText: 'Kembali' })
                        .then(r => { if (r.isConfirmed) location.assign('module/admisi/registrasi-new'); });
                    return;
                }
                const d = h.data;
                if (d.id_patient) {
                    // sudah ada di sistem -> langsung pilih
                    nbModal.hide();
                    pilihPasien(d.id_patient);
                    return;
                }
                $id('noKartuDaftar').value = no;
                $id('regNik').value = d.noKTP || (no.length === 16 ? no : '');
                $id('regNama').value = d.nama || '';
                $id('regTglLahir').value = d.tglLahir || '';
                $id('regJnsKelamin').value = /^p/i.test(d.sex || '') ? 'Perempuan' : 'Laki-laki';
                $id('regNoBPJS').value = d.noKartu || (no.length === 13 ? no : '');
                $id('nbNama').textContent = d.nama || '-';
                $id('nbDetail').textContent = [d.noKartu ? 'BPJS ' + d.noKartu : '', d.tglLahir ? 'Lahir ' + tglIndo(d.tglLahir) : '', $id('regJnsKelamin').value].filter(Boolean).join(' · ');
                $id('nbStatus').innerHTML = h.warning ? `<span class="text-warning-emphasis"><i class="fas fa-triangle-exclamation me-1"></i>${esc(h.message)}</span>` : '<span class="text-success"><i class="fas fa-circle-check me-1"></i>Peserta aktif</span>';
                $id('formBPJS').classList.remove('d-none');
                $id('btnTambahPasien').classList.remove('d-none');
            })
            .fail(() => Swal.fire('Gagal', 'Gagal menghubungi layanan BPJS.', 'error'))
            .always(() => { btn.disabled = false; btn.textContent = 'Cek'; });
    });
    $id('btnTambahPasien').addEventListener('click', function () {
        const btn = this, label = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan…';
        $.ajax({ url: 'controller/admisi/services/addDataPatient.php', type: 'POST', data: $('#formBPJS').serialize(), dataType: 'json' })
            .done(res => {
                if (res && (res.success || res.sudah_ada) && res.id_patient) {
                    nbModal.hide();
                    Swal.fire({ toast: true, position: 'top-end', icon: res.success ? 'success' : 'info', title: res.message, showConfirmButton: false, timer: 2500 });
                    pilihPasien(res.id_patient);
                } else Swal.fire('Gagal', (res && res.message) || 'Gagal menambahkan pasien.', 'warning');
            })
            .fail(() => Swal.fire('Gagal', 'Terjadi kesalahan server.', 'error'))
            .always(() => { btn.disabled = false; btn.innerHTML = label; });
    });

    // buka langsung pasien tertentu: ?id_patient=123
    const awal = new URLSearchParams(location.search).get('id_patient');
    if (awal) pilihPasien(+awal);
})();
