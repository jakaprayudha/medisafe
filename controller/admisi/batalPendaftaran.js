/**
 * Alur pembatalan pendaftaran/antrean (dipakai module/admin/registrasi & module/admisi/listpasiendaftar).
 *  1) cek data klinis kunjungan (asesmen, CPPT, rawat inap, tagihan)
 *  2) bila ada -> peringatan + konfirmasi Ya/Tidak
 *  3) alasan wajib (min. 10 karakter, sebut siapa yang sudah dikoordinasikan)
 *  4) batal antrean BPJS (provider 1) lalu batal pendaftaran; alasan disimpan di log_batal_pendaftaran
 * Dulu: tidak ada pengecekan sehingga kunjungan yang sudah diperiksa / rawat inap dibatalkan tanpa koordinasi.
 *
 *   BatalPendaftaran.mulai({ visit, prov, nama, onSelesai: fn })
 */
(function (w) {
    const esc = s => String(s == null ? '' : s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const pesan = (x, def) => (x && x.responseJSON && x.responseJSON.message) || def;
    const gagal = (m) => Swal.fire('Gagal membatalkan', m, 'error');

    function tanyaKlinis(k, nama) {
        const daftar = (k.rincian || []).map(r => `<li>${esc(r)}</li>`).join('');
        const inap = !!k.rawat_inap;
        return Swal.fire({
            icon: 'warning',
            title: inap ? 'Pasien sudah RAWAT INAP' : 'Pasien sudah diperiksa',
            html: `<div class="text-start small">
                ${nama ? `<div class="mb-2 fw-semibold">${esc(nama)}</div>` : ''}
                <div class="mb-1">Kunjungan ini sudah memiliki data:</div>
                <ul class="mb-2">${daftar}</ul>
                <div class="${inap ? 'text-danger fw-semibold' : ''}">Membatalkan dapat menyembunyikan data pemeriksaan${k.ada_cppt ? ' dan CPPT' : ''} dari daftar kunjungan.
                Pastikan dokter/perawat yang menangani sudah dikoordinasikan.</div>
                <div class="mt-2 fw-semibold">Tetap batalkan pendaftaran ini?</div></div>`,
            showCancelButton: true,
            confirmButtonText: 'Ya, lanjutkan',
            cancelButtonText: 'Tidak',
            confirmButtonColor: '#dc3545',
            reverseButtons: true,
            focusCancel: true
        });
    }

    function tanyaAlasan(visit, nama, prov) {
        const isBpjs = String(prov) === '1';
        return Swal.fire({
            icon: 'warning',
            title: 'Batalkan Pendaftaran?',
            html: `<div class="text-start small">
                ${nama ? `<div class="mb-2 fw-semibold fs-4 text-dark">${esc(nama)} <span class="text-muted fs-2">(${esc(visit)})</span></div>` : ''}
                <div class="alert alert-danger py-2 px-3 mb-3 border-danger bg-danger-subtle text-danger" style="border-radius:8px;">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    <strong>Peringatan Penting:</strong> Pasien yang sudah dibatalkan <strong>tidak bisa dikembalikan lagi</strong> dan harus didaftarkan ulang.
                    ${isBpjs ? `<div class="mt-1">Jika didaftarkan ulang pada hari yang sama, antreannya <strong>tidak masuk ke Antrean BPJS</strong> dan di PCare akan berstatus <strong>Bridging PCare</strong> (bukan Bridging Antrean).</div>` : ''}
                </div>
                <div class="mb-1 fw-semibold">Masukkan alasan pembatalan <span class="text-danger">*</span>:</div>
            </div>`,
            input: 'textarea',
            inputPlaceholder: 'Mis. pasien pulang / batal berobat, dikoordinasikan dengan dr. X / perawat Y pada jam …',
            inputAttributes: { 'aria-label': 'Alasan', maxlength: 500 },
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-times-circle me-1"></i>Batalkan pendaftaran',
            cancelButtonText: 'Kembali',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            reverseButtons: true,
            preConfirm: (a) => {
                a = (a || '').trim();
                if (a.length < 10) { Swal.showValidationMessage('Alasan wajib diisi (minimal 10 karakter)'); return false; }
                return a;
            }
        });
    }

    function kerjakan(o, alasan, konf) {
        Swal.fire({ title: 'Membatalkan…', text: 'Sedang memproses data', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        const data = { novisit: o.visit, alasan: alasan, provider: o.prov, konfirmasi_klinis: konf ? '1' : '0' };
        const hapus = () => $.ajax({ url: 'controller/admisi/services/deletePendaftaran.php', type: 'POST', data: data, dataType: 'json' })
            .done(res => {
                if (res && res.success) {
                    Swal.fire({ title: 'Pendaftaran dibatalkan', text: res.peringatan || res.message, icon: res.peringatan ? 'warning' : 'success' });
                    if (o.onSelesai) o.onSelesai(res);
                } else gagal((res && res.message) || 'Terjadi kesalahan.');
            })
            .fail(x => gagal(pesan(x, 'Terjadi kesalahan server.')));
        if (String(o.prov) !== '1') return hapus();
        $.ajax({ url: 'controller/wsbpjs/batalAntrian.php', type: 'POST', data: data, dataType: 'json' })
            .done(res => (res && res.success) ? hapus() : gagal((res && res.message) || 'Antrean BPJS gagal dibatalkan.'))
            .fail(x => gagal(pesan(x, 'Layanan antrean BPJS tidak merespons. Coba lagi.')));
    }

    // Aktifkan kembali kunjungan batal (CPPT/RME terkunci bila status batal). Alasan wajib.
    function aktifkan(o) {
        Swal.fire({
            icon: 'question',
            title: 'Aktifkan kembali kunjungan?',
            html: `<div class="small mb-2">${o.nama ? esc(o.nama) + ' · ' : ''}${esc(o.visit)}</div>
                <div class="small text-start">RME/CPPT kunjungan ini bisa diisi lagi. Pendaftaran PCare/antrean BPJS yang sudah dihapus <b>tidak</b> dibuat ulang otomatis.</div>`,
            input: 'textarea',
            inputPlaceholder: 'Alasan diaktifkan kembali (mis. salah batal, dikoordinasikan dengan dr. X)',
            inputAttributes: { 'aria-label': 'Alasan', maxlength: 500 },
            showCancelButton: true,
            confirmButtonText: 'Ya, aktifkan',
            cancelButtonText: 'Tidak',
            reverseButtons: true,
            preConfirm: (a) => {
                a = (a || '').trim();
                if (a.length < 10) { Swal.showValidationMessage('Alasan wajib diisi (minimal 10 karakter)'); return false; }
                return a;
            }
        }).then(r => {
            if (!r.isConfirmed) return;
            Swal.fire({ title: 'Memproses…', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            $.ajax({ url: 'controller/admisi/services/aktifkanKembaliPendaftaran.php', type: 'POST', data: { novisit: o.visit, alasan: r.value }, dataType: 'json' })
                .done(res => {
                    if (res && res.success) {
                        Swal.fire({ title: 'Kunjungan aktif kembali', text: res.peringatan || res.message, icon: res.peringatan ? 'warning' : 'success' });
                        if (o.onSelesai) o.onSelesai(res);
                    } else Swal.fire('Gagal', (res && res.message) || 'Terjadi kesalahan.', 'error');
                })
                .fail(x => Swal.fire('Gagal', pesan(x, 'Terjadi kesalahan server.'), 'error'));
        });
    }

    w.BatalPendaftaran = {
        aktifkan: aktifkan,
        mulai: function (o) {
            $.ajax({ url: 'controller/admisi/services/cekBatalPendaftaran.php', type: 'POST', data: { novisit: o.visit }, dataType: 'json' })
                .done(async res => {
                    if (!res || !res.success) return gagal((res && res.message) || 'Pengecekan kunjungan gagal.');
                    const k = res.klinis || {};
                    if (k.butuh_konfirmasi) {
                        const y = await tanyaKlinis(k, o.nama);
                        if (!y.isConfirmed) return;
                    }
                    const a = await tanyaAlasan(o.visit, o.nama, o.prov);
                    if (!a.isConfirmed) return;
                    kerjakan(o, a.value, !!k.butuh_konfirmasi);
                })
                .fail(x => gagal(pesan(x, 'Pengecekan kunjungan gagal. Coba lagi.')));
        }
    };
})(window);
