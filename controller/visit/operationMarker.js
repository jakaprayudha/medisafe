/*
 * Penanda operasi (module/admin/penanda_operasi).
 * Format anotasi tetap sama dengan versi sebelumnya (koordinat relatif 0..1), sehingga dokumen lama tetap bisa dibuka.
 * Perbaikan: kelima template (~8 MB) dulu dimuat sekaligus -> kini per tab saat dibuka; "Buka" dulu mengunduh PNG
 * yang tidak dipakai; nomor versi lintas jenis gambar; konfirmasi memakai confirm() bawaan; tanpa Ctrl+Z;
 * tidak ada tanda tab yang belum disimpan; simpan tetap bisa saat kunjungan batal.
 */
(() => {
    "use strict";
    const config = window.operationMarkerConfig;
    const endpoint = "controller/visit/operationMarkerController.php";
    const $id = id => document.getElementById(id);
    const esc = s => String(s ?? "").replace(/[&<>"']/g, c => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
    const BLN = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];
    const waktu = s => { const m = String(s || "").match(/^(\d{4})-(\d{2})-(\d{2})\s+(\d{2}:\d{2})/); return m ? `${+m[3]} ${BLN[m[2] - 1]} ${m[1]}, ${m[4]}` : (s || "-"); };
    const toast = (title, icon = "success") => Swal.fire({ toast: true, position: "top-end", icon, title, showConfirmButton: false, timer: 2600 });
    const gagal = text => Swal.fire({ icon: "error", title: "Gagal", text });

    const states = new Map();
    let saving = false, loading = false, batal = true, riwayat = [];
    let alat = "stroke", warna = "#dc2626";
    const notes = $id("po-catatan"), save = $id("po-simpan");

    async function request(params, options) {
        const response = await fetch(`${endpoint}?${new URLSearchParams(params)}`, options);
        const result = await response.json().catch(() => ({ message: "Respon server tidak valid" }));
        if (!response.ok || result.status !== "success") throw new Error(result.message || "Gagal mengakses penanda operasi");
        return result;
    }

    /* ================= KANVAS ================= */
    function aktifKey() { return document.querySelector(".po .tabs .nav-link.active").dataset.marker; }
    function active() { return states.get(aktifKey()); }

    function render(state) {
        const { canvas, image, annotations } = state;
        const ctx = canvas.getContext("2d");
        ctx.fillStyle = "#ffffff";
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(image, 0, 0);
        for (const item of annotations) {
            ctx.strokeStyle = item.color;
            ctx.fillStyle = item.color;
            ctx.lineWidth = item.size;
            ctx.lineCap = "round";
            ctx.lineJoin = "round";
            if (item.kind === "text") {
                ctx.font = `${item.size}px Arial`;
                ctx.textBaseline = "top";
                ctx.fillText(item.text, item.x * canvas.width, item.y * canvas.height);
            } else {
                ctx.beginPath();
                item.points.forEach(([x, y], i) => {
                    if (i === 0) ctx.moveTo(x * canvas.width, y * canvas.height);
                    else ctx.lineTo(x * canvas.width, y * canvas.height);
                });
                if (item.points.length === 1) {
                    const [x, y] = item.points[0];
                    ctx.arc(x * canvas.width, y * canvas.height, item.size / 2, 0, Math.PI * 2);
                    ctx.fill();
                } else ctx.stroke();
            }
        }
    }
    function position(event, canvas) {
        const rect = canvas.getBoundingClientRect();
        return [
            Math.max(0, Math.min(1, (event.clientX - rect.left) / rect.width)),
            Math.max(0, Math.min(1, (event.clientY - rect.top) / rect.height))
        ];
    }
    function tandaiKotor(state, kotor) {
        state.dirty = kotor;
        $id(`marker-tab-${state.key}`).querySelector(".titik").classList.toggle("d-none", !kotor);
    }

    // template dimuat saat tab pertama kali dibuka
    function muatTemplate(state) {
        if (state.siap) return state.siap;
        state.siap = new Promise((resolve, reject) => {
            const wrap = state.canvas.parentElement;
            state.image.onload = () => {
                state.canvas.width = state.image.naturalWidth;
                state.canvas.height = state.image.naturalHeight;
                state.ready = true;
                render(state);
                wrap.querySelector(".muat").remove();
                state.canvas.classList.remove("d-none");
                resolve(state);
            };
            state.image.onerror = () => {
                state.siap = null;
                wrap.querySelector(".muat").innerHTML = `<span class="text-danger">Gagal memuat template ${esc(config.types[state.key])}. <a href="javascript:;" class="po-coba">Coba lagi</a></span>`;
                reject(new Error(`Template ${config.types[state.key]} gagal dimuat`));
            };
            state.image.src = `assets/images/site-marker-${state.key}.png`;
        });
        return state.siap;
    }
    document.addEventListener("click", e => {
        if (!e.target.classList.contains("po-coba")) return;
        const st = states.get(e.target.closest(".tab-pane").querySelector("canvas").dataset.marker);
        e.target.closest(".muat").innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memuat…';
        muatTemplate(st).catch(() => {});
    });

    document.querySelectorAll(".marker-canvas").forEach(canvas => {
        const key = canvas.dataset.marker;
        const state = { key, canvas, image: new Image(), annotations: [], notes: "", dirty: false, ready: false, siap: null, stroke: null, pointer: null, sumber: null };
        states.set(key, state);

        canvas.addEventListener("pointerdown", event => {
            if (!state.ready || saving || loading || state.stroke || (event.pointerType === "mouse" && event.button !== 0)) return;
            if (state.annotations.length >= 1000) return toast("Maksimal 1000 penanda per dokumen.", "warning");
            const [x, y] = position(event, canvas);
            const size = Number($id("po-ukuran").value);
            if (alat === "text") {
                const text = $id("po-teks").value.trim();
                if (!text) { $id("po-teks").classList.add("is-invalid"); $id("po-teks").focus(); return toast("Isi teks dulu, lalu klik gambar.", "warning"); }
                state.annotations.push({ kind: "text", x, y, text, color: warna, size: Math.max(16, size * 5) });
            } else {
                state.stroke = { kind: "stroke", color: warna, size, points: [[x, y]] };
                state.pointer = event.pointerId;
                state.annotations.push(state.stroke);
                canvas.setPointerCapture(event.pointerId);
            }
            tandaiKotor(state, true);
            render(state);
            perbaruiTombol();
        });
        canvas.addEventListener("pointermove", event => {
            if (!state.stroke || saving || loading || event.pointerId !== state.pointer || state.stroke.points.length >= 10000) return;
            state.stroke.points.push(position(event, canvas));
            render(state);
        });
        const stop = event => {
            if (event.pointerId === state.pointer) { state.stroke = null; state.pointer = null; }
        };
        canvas.addEventListener("pointerup", stop);
        canvas.addEventListener("pointercancel", stop);
        canvas.addEventListener("lostpointercapture", stop);
    });

    /* ================= ALAT ================= */
    $id("po-alat").addEventListener("click", e => {
        const b = e.target.closest("[data-alat]");
        if (!b) return;
        alat = b.dataset.alat;
        $id("po-alat").querySelectorAll("button").forEach(x => x.classList.toggle("on", x === b));
        $id("po-teks-wrap").classList.toggle("d-none", alat !== "text");
        document.querySelectorAll(".marker-canvas").forEach(c => c.classList.toggle("mode-text", alat === "text"));
        if (alat === "text") $id("po-teks").focus();
    });
    $id("po-teks").addEventListener("input", function () { this.classList.remove("is-invalid"); });
    function pilihWarna(w, tombol) {
        warna = w;
        $id("po-warna").querySelectorAll("button").forEach(x => x.classList.toggle("on", x === tombol));
        contoh();
    }
    $id("po-warna").addEventListener("click", e => { const b = e.target.closest("[data-warna]"); if (b) pilihWarna(b.dataset.warna, b); });
    $id("po-warna-lain").addEventListener("input", function () { pilihWarna(this.value, null); });
    function contoh() {
        const n = +$id("po-ukuran").value, c = $id("po-contoh");
        $id("po-ukuran-nilai").textContent = n;
        c.style.width = c.style.height = Math.max(3, Math.min(24, n)) + "px";
        c.style.background = warna;
    }
    $id("po-ukuran").addEventListener("input", contoh);
    contoh();

    function urungkan() {
        const state = active();
        if (!state.ready || saving || loading || !state.annotations.length) return;
        state.stroke = null;
        state.annotations.pop();
        tandaiKotor(state, true);
        render(state);
        perbaruiTombol();
    }
    $id("po-urung").addEventListener("click", urungkan);
    document.addEventListener("keydown", e => {
        if ((e.ctrlKey || e.metaKey) && !e.shiftKey && e.key.toLowerCase() === "z" && !/^(INPUT|TEXTAREA|SELECT)$/.test(document.activeElement.tagName)) {
            e.preventDefault();
            urungkan();
        }
    });
    $id("po-bersih").addEventListener("click", () => {
        const state = active();
        if (!state.ready || saving || loading || !state.annotations.length) return;
        Swal.fire({
            icon: "warning", title: `Bersihkan gambar ${config.types[state.key]}?`, text: "Semua coretan dan teks pada gambar ini dihapus dari layar (versi tersimpan tidak terpengaruh).",
            showCancelButton: true, confirmButtonText: "Bersihkan", cancelButtonText: "Batal", confirmButtonColor: "#dc2626", cancelButtonColor: "#475569", reverseButtons: true
        }).then(r => {
            if (!r.isConfirmed) return;
            state.stroke = null;
            state.annotations = [];
            tandaiKotor(state, true);
            render(state);
            perbaruiTombol();
        });
    });

    /* ================= STATUS / TAB ================= */
    function perbaruiTombol() {
        const st = active();
        const label = config.types[st.key];
        save.innerHTML = `<i class="ti ti-device-floppy me-1"></i>Simpan versi baru — ${esc(label)}`;
        save.disabled = batal || saving || loading || !st.ready || !st.annotations.length;
        $id("po-urung").disabled = $id("po-bersih").disabled = !st.ready || !st.annotations.length;
        const s = $id("po-sumber");
        s.classList.toggle("lanjut", !!st.sumber);
        s.innerHTML = st.sumber
            ? `<i class="ti ti-history me-1"></i>Melanjutkan <b>${esc(label)} v${esc(st.sumber.versi)}</b>${st.dirty ? " · ada perubahan" : ""}`
            : (st.annotations.length ? `Gambar baru · ${st.annotations.length} penanda` : `Gambar baru — mulai mencoret pada gambar ${esc(label)}`);
    }
    document.querySelectorAll('.po .tabs [data-bs-toggle="pill"]').forEach(tab => {
        tab.addEventListener("shown.bs.tab", () => {
            const st = active();
            notes.value = st.notes;
            muatTemplate(st).then(perbaruiTombol).catch(() => {});
            perbaruiTombol();
            tandaiBaris();
        });
    });
    notes.addEventListener("input", () => {
        const st = active();
        st.notes = notes.value;
        tandaiKotor(st, true);
        perbaruiTombol();
    });

    /* ================= RIWAYAT ================= */
    function tandaiBaris() {
        const st = active();
        document.querySelectorAll("#po-riwayat tr[data-id]").forEach(tr => tr.classList.toggle("aktif", !!st.sumber && +tr.dataset.id === st.sumber.id));
    }
    async function muatRiwayat() {
        const res = await request({ visit_ID: config.visit });
        riwayat = res.data;
        batal = !!(res.kunjungan && res.kunjungan.batal);
        $id("po-batal").classList.toggle("d-none", !batal);
        $id("po-jumlah").textContent = riwayat.length ? `· ${riwayat.length} versi` : "";
        const body = $id("po-riwayat");
        if (!riwayat.length) {
            body.innerHTML = '<tr><td colspan="5" class="kosong">Belum ada penanda operasi tersimpan untuk kunjungan ini.</td></tr>';
        } else {
            body.innerHTML = riwayat.map(d => {
                const pdf = `module/admin/print/formulir_penanda_operasi?${new URLSearchParams({ no: config.visit, rm: config.rm, id: d.id })}`;
                return `<tr data-id="${+d.id}">
                    <td class="aksi"><div class="d-flex gap-1">
                        <button type="button" class="btn btn-sm btn-primary" data-buka="${+d.id}" title="Buka di editor untuk dilanjutkan"><i class="ti ti-pencil me-1"></i>Buka</button>
                        <a class="btn btn-sm btn-outline-dark" href="${esc(pdf)}" target="_blank" rel="noopener" title="Cetak / PDF"><i class="ti ti-printer"></i></a></div></td>
                    <td><b>${esc(config.types[d.marker_type] || d.marker_type)}</b> v${+d.versi} ${d.terbaru ? '<span class="tag tag-baru">Terbaru</span>' : ""}<div class="small text-muted">Dokumen #${+d.id}</div></td>
                    <td>${+d.jumlah_penanda || 0}</td>
                    <td><div class="catatan">${d.notes ? esc(d.notes) : '<span class="text-muted">-</span>'}</div></td>
                    <td class="text-nowrap">${esc(waktu(d.created_at))}<div class="small text-muted">${esc(d.created_by || "-")}</div></td>
                </tr>`;
            }).join("");
        }
        tandaiBaris();
        perbaruiTombol();
    }

    $id("po-riwayat").addEventListener("click", async e => {
        const b = e.target.closest("[data-buka]");
        if (!b || saving || loading) return;
        const meta = riwayat.find(d => +d.id === +b.dataset.buka);
        const state = states.get(meta.marker_type);
        if (state.dirty && state.annotations.length) {
            const r = await Swal.fire({
                icon: "question", title: "Ganti draft yang belum disimpan?",
                html: `Gambar <b>${esc(config.types[meta.marker_type])}</b> punya perubahan yang belum disimpan. Ganti dengan versi v${+meta.versi}?`,
                showCancelButton: true, confirmButtonText: "Ya, ganti", cancelButtonText: "Batal", cancelButtonColor: "#475569", reverseButtons: true
            });
            if (!r.isConfirmed) return;
        }
        loading = true;
        b.disabled = true;
        perbaruiTombol();
        try {
            const [res] = await Promise.all([request({ visit_ID: config.visit, id: meta.id }), muatTemplate(state)]);
            state.annotations = res.data.annotations;
            state.notes = res.data.notes || "";
            state.stroke = null;
            state.sumber = { id: +meta.id, versi: +meta.versi };
            tandaiKotor(state, false);
            render(state);
            bootstrap.Tab.getOrCreateInstance($id(`marker-tab-${meta.marker_type}`)).show();
            notes.value = state.notes;
            tandaiBaris();
            window.scrollTo({ top: $id("marker-tab-" + meta.marker_type).getBoundingClientRect().top + window.scrollY - 100, behavior: "smooth" });
            toast(`${config.types[meta.marker_type]} v${meta.versi} dibuka. Simpan akan membuat versi baru.`, "info");
        } catch (error) {
            console.error(error);
            gagal(error.message);
        } finally {
            loading = false;
            b.disabled = false;
            perbaruiTombol();
        }
    });

    /* ================= SIMPAN ================= */
    save.addEventListener("click", async () => {
        const state = active();
        if (saving || loading || batal) return;
        if (!state.ready || !state.annotations.length) return toast("Tambahkan coretan atau teks sebelum menyimpan.", "warning");
        state.stroke = null;
        saving = true;
        notes.disabled = true;
        perbaruiTombol();
        save.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan…';
        try {
            const result = await request({}, {
                method: "POST", headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    visit_ID: config.visit, marker_type: state.key,
                    annotations: state.annotations, image: state.canvas.toDataURL("image/png"),
                    notes: state.notes, token: config.token
                })
            });
            tandaiKotor(state, false);
            try {
                await muatRiwayat();
                const baru = riwayat.find(d => +d.id === +result.id);
                if (baru) state.sumber = { id: +baru.id, versi: +baru.versi };
                toast(`${config.types[state.key]} v${baru ? baru.versi : ""} tersimpan.`);
            } catch (error) {
                console.error(error);
                gagal(`Versi #${result.id} tersimpan, tetapi daftar versi gagal dimuat: ${error.message}`);
            }
        } catch (error) {
            console.error(error);
            gagal(error.message);
        } finally {
            saving = false;
            notes.disabled = false;
            tandaiBaris();
            perbaruiTombol();
        }
    });

    window.addEventListener("beforeunload", event => {
        if (![...states.values()].some(s => s.dirty && s.annotations.length) && !saving) return;
        event.preventDefault();
        event.returnValue = "";
    });

    muatTemplate(active()).then(perbaruiTombol).catch(() => {});
    muatRiwayat().catch(error => {
        console.error(error);
        $id("po-riwayat").innerHTML = `<tr><td colspan="5" class="kosong text-danger">${esc(error.message)}</td></tr>`;
    });
})();
