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

    function tanyaAlasan(visit, nama) {
        return Swal.fire({
            icon: 'question',
            title: 'Alasan pembatalan',
            html: `<div class="small mb-2">${nama ? esc(nama) + ' · ' : ''}${esc(visit)}</div>`,
            input: 'textarea',
            inputPlaceholder: 'Mis. pasien pulang, dikoordinasikan dengan dr. X / perawat Y pada jam …',
            inputAttributes: { 'aria-label': 'Alasan', maxlength: 500 },
            showCancelButton: true,
            confirmButtonText: 'Batalkan pendaftaran',
            cancelButtonText: 'Kembali',
            confirmButtonColor: '#dc3545',
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

    w.BatalPendaftaran = {
        mulai: function (o) {
            $.ajax({ url: 'controller/admisi/services/cekBatalPendaftaran.php', type: 'POST', data: { novisit: o.visit }, dataType: 'json' })
                .done(async res => {
                    if (!res || !res.success) return gagal((res && res.message) || 'Pengecekan kunjungan gagal.');
                    const k = res.klinis || {};
                    if (k.butuh_konfirmasi) {
                        const y = await tanyaKlinis(k, o.nama);
                        if (!y.isConfirmed) return;
                    }
                    const a = await tanyaAlasan(o.visit, o.nama);
                    if (!a.isConfirmed) return;
                    kerjakan(o, a.value, !!k.butuh_konfirmasi);
                })
                .fail(x => gagal(pesan(x, 'Pengecekan kunjungan gagal. Coba lagi.')));
        }
    };
})(window);
