/**
 * Logika bersama form surat (module/letter/form-*). Dipakai bersama module/letter/_kerangka_atas.php & _kerangka_bawah.php.
 *
 * SuratForm.init({
 *   api: 'controller/letter/suratSakitController',
 *   cetak: id => 'module/letter/print/surat-keterangan-sakit?id=' + id,
 *   cari: 'controller/admisi/patientVisitControllerInOut',
 *   nama: 'surat keterangan sakit',
 *   kolom: [{ data, render(v, t, r) }],      // kolom tambahan (urut sama dengan $SF['kolom'])
 *   kosongkan(),                             // reset field khusus saat "Buat surat"
 *   isi(d),                                  // isi field khusus saat edit
 *   pilihKunjungan(k, baru),                 // kunjungan dipilih (baru=true saat buat baru)
 *   validasi(err, tandai),                   // tambah pesan ke err[] / tandai(el, pesan)
 *   data(fd),                                // ubah URLSearchParams sebelum dikirim (opsional)
 * })
 * Server: GET ?dari&sampai -> {status, data:[...]} | GET ?id= | POST | PUT ?id= | DELETE ?id=  (format controller surat).
 */
(function(global) {
    const $id = id => document.getElementById(id);
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const BLN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const tglJkt = h => new Date(Date.now() + (h || 0) * 86400000).toLocaleDateString('sv-SE', { timeZone: 'Asia/Jakarta' });
    const tgl = s => { const m = String(s || '').match(/^(\d{4})-(\d{2})-(\d{2})/); return m ? (+m[3]) + ' ' + BLN[m[2] - 1] + ' ' + m[1] : '-'; };
    const tglPendek = s => { const m = String(s || '').match(/^(\d{4})-(\d{2})-(\d{2})/); return m ? (+m[3]) + ' ' + BLN[m[2] - 1] + (m[1] !== tglJkt(0).slice(0, 4) ? ' ' + m[1].slice(2) : '') : '-'; };
    const isi = v => v !== null && v !== undefined && /[0-9A-Za-z]/.test(String(v));
    function umur(lahir) { const m = String(lahir || '').match(/^(\d{4})-(\d{2})-(\d{2})/); if (!m || m[1] < '1900') return ''; const d = new Date(), b = new Date(+m[1], m[2] - 1, +m[3]); let u = d.getFullYear() - b.getFullYear(); if (d < new Date(d.getFullYear(), b.getMonth(), b.getDate())) u--; return u >= 0 ? u + ' th' : ''; }
    const jk = g => { g = String(g || '').toLowerCase(); return g.startsWith('l') ? 'L' : (g.startsWith('p') ? 'P' : ''); };
    const tutup = m => { if (m._isTransitioning) m._element.addEventListener('shown.bs.modal', () => m.hide(), { once: true }); else m.hide(); };

    const SuratForm = { esc, tgl, tglPendek, tglJkt, isi, umur, jk };

    SuratForm.init = function(cfg) {
        const S = global.SF_SETTING || {};
        const modalEl = $id('sfModal'), modal = new bootstrap.Modal(modalEl);
        const form = $id('sfForm');
        let mode = 'baru', xhr = null, kunjungan = null;

        // ---------- filter tanggal ----------
        $id('sfDari').value = tglJkt(-29);
        $id('sfSampai').value = tglJkt(0);
        $id('sfDari').max = $id('sfSampai').max = tglJkt(0);

        // ---------- tabel ----------
        const kolomPasien = { data: 'patient_name', render: (v, t, r) => {
            if (t !== 'display') return [v, r.nomor_rm, r.patient_nik].join(' ');
            const k = jk(r.patient_gender), u = umur(r.patient_datebirth);
            return `<div class="fw-semibold sf-nama" title="${esc(v)}">${esc(v || '-')}</div><div class="small text-muted"><span class="sf-rm">${esc(r.nomor_rm || '-')}</span>${k ? ' · ' + k : ''}${u ? ' · ' + u : ''}</div>`;
        } };
        const table = $('#sfTable').DataTable({
            processing: true, serverSide: false, deferRender: true, autoWidth: false, order: [], pageLength: 25, lengthMenu: [10, 25, 50, 100],
            dom: 't<"d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2 small"lip>',
            language: { processing: '<div class="spinner-border spinner-border-sm text-primary"></div> Memuat…', emptyTable: 'Belum ada surat pada rentang tanggal ini.', zeroRecords: 'Tidak ada data yang cocok.',
                info: '_START_–_END_ dari _TOTAL_ surat', infoEmpty: '0 surat', infoFiltered: '(disaring dari _MAX_)', lengthMenu: 'Tampil _MENU_', thousands: '.', paginate: { previous: '‹', next: '›' } },
            ajax: function(_d, cb) {
                if (xhr && xhr.readyState !== 4) xhr.abort();
                xhr = $.ajax({ url: cfg.api, dataType: 'json', cache: false, data: { dari: $id('sfDari').value, sampai: $id('sfSampai').value } })
                    .done(j => {
                        if (!j || j.status !== 'success') { cb({ data: [] }); Swal.fire('Gagal memuat data', (j && j.message) || '', 'error'); return; }
                        $id('sfJumlah').textContent = j.data.length.toLocaleString('id-ID') + ' surat';
                        cb({ data: j.data });
                    })
                    .fail((x, st) => { if (st === 'abort') return; cb({ data: [] }); Swal.fire('Gagal memuat data', x.status === 401 ? 'Sesi berakhir, silakan login ulang.' : ((x.responseJSON && x.responseJSON.message) || 'Tidak dapat terhubung ke server.'), 'error'); });
            },
            columns: [
                { data: 'nomor_surat', render: (v, t) => t === 'display' ? `<span class="sf-no">${esc(v || '-')}</span>` : (v || '') },
                { data: 'tanggal_surat', render: (v, t) => t === 'display' ? `<span class="small">${tglPendek(v)}</span>` : (v || '') },
                kolomPasien,
                ...(cfg.kolom || []),
                { data: null, orderable: false, searchable: false, className: 'text-end', render: (v, t, r) => t !== 'display' ? '' :
                    `<div class="btn-group btn-group-sm"><a class="btn btn-primary" href="${esc(cfg.cetak(r.id))}" target="_blank" rel="noopener" title="Cetak"><i class="fas fa-print me-1"></i>Cetak</a>
                     <button type="button" class="btn btn-light sf-edit" data-id="${r.id}" title="Ubah"><i class="fas fa-pen"></i></button>
                     <button type="button" class="btn btn-light text-danger sf-hapus" data-id="${r.id}" title="Hapus"><i class="fas fa-trash"></i></button></div>` }
            ]
        });
        const muat = () => table.ajax.reload(null, false);
        const baris = id => table.rows().data().toArray().find(r => +r.id === +id);
        let tCari;
        $id('sfCari').addEventListener('input', e => { clearTimeout(tCari); tCari = setTimeout(() => table.search(e.target.value).draw(), 200); });
        ['sfDari', 'sfSampai'].forEach(i => $id(i).addEventListener('change', () => { document.querySelectorAll('#sfCepat .btn').forEach(b => b.classList.remove('active')); muat(); }));
        $id('sfCepat').addEventListener('click', e => {
            const b = e.target.closest('[data-h]');
            if (!b) return;
            document.querySelectorAll('#sfCepat .btn').forEach(x => x.classList.toggle('active', x === b));
            $id('sfSampai').value = tglJkt(0);
            $id('sfDari').value = b.dataset.h === 'bulan' ? tglJkt(0).slice(0, 8) + '01' : tglJkt(-b.dataset.h);
            muat();
        });

        // ---------- pemilih kunjungan ----------
        const $pilih = $('#sfPilih');
        $pilih.select2({
            dropdownParent: $(modalEl), width: '100%', placeholder: 'Cari pasien / kunjungan…', allowClear: true, minimumInputLength: 2,
            language: { inputTooShort: () => 'Ketik minimal 2 huruf', searching: () => 'Mencari…', noResults: () => 'Tidak ada kunjungan yang cocok', errorLoading: () => 'Gagal memuat hasil' },
            ajax: {
                url: cfg.cari, dataType: 'json', delay: 300,
                data: p => ({ search: p.term || '' }),
                processResults: r => ({ results: (r && r.data || []).map(k => Object.assign({ id: k.id_visit, text: k.patient_name + ' · ' + (k.nomor_rm || '-') + ' · ' + (k.visit_date || '') }, k)) })
            },
            templateResult: k => {
                if (k.loading || !k.id_visit) return k.text;
                const u = umur(k.patient_datebirth);
                return $(`<div class="sf-hasil"><b>${esc(k.patient_name)}</b> <span class="sf-rm">${esc(k.nomor_rm || '-')}</span>${u ? ' <small>· ' + u + '</small>' : ''}
                  <br><small><i class="far fa-calendar me-1"></i>${tgl(k.visit_date)}${k.id_poli ? ' · ' + esc(k.id_poli) : ''}${k.id_doctor ? ' · ' + esc(k.id_doctor) : ''}</small></div>`);
            },
            templateSelection: k => k.id_visit ? `${k.patient_name} · ${k.nomor_rm || '-'} · ${tgl(k.visit_date)}` : k.text
        });
        function tampilPasien(k) {
            if (!k) { $id('sfPasien').classList.add('d-none'); $id('sfPasien').innerHTML = ''; return; }
            const u = umur(k.patient_datebirth), g = jk(k.patient_gender);
            $id('sfPasien').innerHTML = `<div class="d-flex flex-wrap gap-2 align-items-start"><div class="min-w-0 flex-grow-1"><div class="fw-semibold">${esc(k.patient_name || '-')}</div>
              <div class="small"><span class="sf-rm">${esc(k.nomor_rm || '-')}</span>${g ? ' · ' + (g === 'L' ? 'Laki-laki' : 'Perempuan') : ''}${u ? ' · ' + u : ''}${isi(k.patient_nik) ? ' · NIK ' + esc(k.patient_nik) : ''}</div></div>
              <div class="small text-end">Kunjungan ${tgl(k.visit_date)}${k.id_doctor ? '<br>' + esc(k.id_doctor) : ''}</div></div>`;
            $id('sfPasien').classList.remove('d-none');
        }
        $pilih.on('select2:select', e => {
            kunjungan = e.params.data;
            $id('id_patient').value = kunjungan.id_patient || '';
            $id('id_visit').value = kunjungan.id_visit || '';
            tampilPasien(kunjungan);
            $id('sfPilih').closest('#sfPilihWrap').querySelector('.select2-selection').classList.remove('border-danger');
            if (cfg.pilihKunjungan) cfg.pilihKunjungan(kunjungan, mode === 'baru');
        });
        $pilih.on('select2:clear', () => { kunjungan = null; $id('id_patient').value = $id('id_visit').value = ''; tampilPasien(null); });

        // ---------- buka form ----------
        function bersih() {
            form.reset();
            form.querySelectorAll('.is-invalid').forEach(e => e.classList.remove('is-invalid'));
            $id('sfError').classList.add('d-none');
            $id('id').value = $id('id_patient').value = $id('id_visit').value = '';
            $pilih.val(null).trigger('change');
            kunjungan = null;
            tampilPasien(null);
        }
        $id('sfTambah').addEventListener('click', () => {
            if (!S.ada) return;
            mode = 'baru';
            bersih();
            $id('sfPilihWrap').classList.remove('d-none');
            $id('tanggal_surat').value = tglJkt(0);
            if ($id('sfNomorAuto')) $id('sfNomorAuto').textContent = 'Dibuat otomatis saat disimpan';
            $id('sfJudulModal').textContent = 'Buat ' + cfg.nama;
            $id('sfSubModal').textContent = '';
            if (cfg.kosongkan) cfg.kosongkan();
            modal.show();
            modalEl.addEventListener('shown.bs.modal', () => $pilih.select2('open'), { once: true });
        });
        $('#sfTable').on('click', '.sf-edit', async function() {
            const id = this.dataset.id, btn = this;
            btn.disabled = true;
            try {
                const r = await fetch(cfg.api + '?id=' + encodeURIComponent(id), { credentials: 'same-origin', cache: 'no-store' });
                const j = await r.json().catch(() => null);
                if (!j || j.status !== 'success') { Swal.fire('Gagal', (j && j.message) || 'Data surat tidak ditemukan.', 'error'); return; }
                const d = j.data;
                mode = 'ubah';
                bersih();
                $id('id').value = d.id;
                $id('id_patient').value = d.id_patient || '';
                $id('id_visit').value = d.id_visit || '';
                $id('nomor_surat').value = d.nomor_surat || '';
                $id('tanggal_surat').value = d.tanggal_surat || '';
                if ($id('sfNomorAuto')) $id('sfNomorAuto').textContent = d.nomor_surat || '-';
                $id('sfPilihWrap').classList.add('d-none'); // pasien tidak bisa diganti saat ubah
                kunjungan = d;
                tampilPasien(d);
                $id('sfJudulModal').textContent = 'Ubah ' + cfg.nama;
                $id('sfSubModal').textContent = d.nomor_surat || '';
                if (cfg.isi) cfg.isi(d);
                modal.show();
            } catch (e) {
                Swal.fire('Gagal', 'Tidak dapat terhubung ke server.', 'error');
            } finally { btn.disabled = false; }
        });

        // ---------- simpan ----------
        function validasi() {
            const err = [];
            const tandai = (el, pesan) => { if (el) el.classList.add('is-invalid'); err.push(pesan); };
            form.querySelectorAll('.is-invalid').forEach(e => e.classList.remove('is-invalid'));
            if (mode === 'baru' && !$id('id_visit').value) { err.push('pilih pasien / kunjungan'); const s = $id('sfPilihWrap').querySelector('.select2-selection'); if (s) s.classList.add('border-danger'); }
            if (S.mode === 'MANUAL' && !$id('nomor_surat').value.trim()) tandai($id('nomor_surat'), 'nomor surat');
            if (!$id('tanggal_surat').value) tandai($id('tanggal_surat'), 'tanggal surat');
            if (cfg.validasi) cfg.validasi(err, tandai);
            $id('sfError').textContent = err.length ? 'Periksa: ' + err.join('; ') + '.' : '';
            $id('sfError').classList.toggle('d-none', !err.length);
            return !err.length;
        }
        let menyimpan = false;
        form.addEventListener('submit', async e => {
            e.preventDefault();
            if (menyimpan || !validasi()) return;
            menyimpan = true;
            const btn = $id('sfSimpan'), asli = btn.innerHTML;
            btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan…';
            const fd = new URLSearchParams(new FormData(form));
            if (cfg.data) cfg.data(fd);
            const id = $id('id').value;
            try {
                const r = await fetch(cfg.api + (id ? '?id=' + encodeURIComponent(id) : ''), { method: id ? 'PUT' : 'POST', credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: fd });
                const j = await r.json().catch(() => null);
                if (j && j.status === 'success') {
                    tutup(modal);
                    muat();
                    const idSurat = (j.data && j.data.id) || id;
                    if (!id && j.data && j.data.nomor_surat && $id('sfTerakhir') && S.mode === 'AUTO') $id('sfTerakhir').textContent = (+$id('sfTerakhir').textContent || 0) + 1;
                    Swal.fire({ icon: 'success', title: id ? 'Surat diperbarui' : 'Surat tersimpan', html: j.data && j.data.nomor_surat ? `Nomor surat <b>${esc(j.data.nomor_surat)}</b>` : esc(j.message || ''),
                        showDenyButton: !!idSurat, denyButtonText: '<i class="fas fa-print me-1"></i>Cetak', denyButtonColor: '#5d87ff', confirmButtonText: 'Tutup', confirmButtonColor: '#6c757d' })
                        .then(x => { if (x.isDenied) window.open(cfg.cetak(idSurat), '_blank', 'noopener'); });
                } else if (j && j.status === 'setting_required') {
                    Swal.fire('Nomor surat belum diatur', j.message, 'warning');
                } else {
                    Swal.fire('Gagal menyimpan', (j && j.message) || 'Terjadi kesalahan server.', 'error');
                }
            } catch (err) {
                Swal.fire('Gagal menyimpan', 'Tidak dapat terhubung ke server.', 'error');
            } finally {
                menyimpan = false; btn.disabled = false; btn.innerHTML = asli;
            }
        });

        // ---------- hapus ----------
        $('#sfTable').on('click', '.sf-hapus', function() {
            const r = baris(this.dataset.id);
            if (!r) return;
            Swal.fire({
                icon: 'warning', title: 'Hapus surat?', html: `<b>${esc(r.nomor_surat || '-')}</b><br>${esc(r.patient_name || '-')} · ${tgl(r.tanggal_surat)}<div class="small text-muted mt-2">Surat yang dihapus tidak dapat dikembalikan. Nomor surat tidak dipakai ulang.</div>`,
                showCancelButton: true, confirmButtonText: 'Ya, hapus', cancelButtonText: 'Batal', confirmButtonColor: '#d33', reverseButtons: true,
                showLoaderOnConfirm: true, allowOutsideClick: () => !Swal.isLoading(),
                preConfirm: async () => {
                    try {
                        const x = await fetch(cfg.api + '?id=' + encodeURIComponent(r.id), { method: 'DELETE', credentials: 'same-origin' });
                        const j = await x.json().catch(() => null);
                        if (!j || j.status !== 'success') Swal.showValidationMessage((j && j.message) || 'Gagal menghapus.');
                        return j;
                    } catch (e) { Swal.showValidationMessage('Tidak dapat terhubung ke server.'); }
                }
            }).then(res => { if (res.isConfirmed) { Swal.fire({ icon: 'success', title: 'Surat dihapus', timer: 1300, showConfirmButton: false }); muat(); } });
        });

        SuratForm.table = table;
        SuratForm.kunjungan = () => kunjungan;
        SuratForm.mode = () => mode;
        return SuratForm;
    };

    global.SuratForm = SuratForm;
})(window);
