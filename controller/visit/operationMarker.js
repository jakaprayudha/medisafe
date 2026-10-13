(() => {
    "use strict";
    const config = window.operationMarkerConfig;
    const endpoint = "controller/visit/operationMarkerController.php";
    const states = new Map();
    let saving = false;
    let loading = false;
    const message = document.getElementById("marker-message");
    const notes = document.getElementById("marker-notes");
    const save = document.getElementById("marker-save");

    function notify(text, error = false) {
        message.textContent = text;
        message.className = `alert ${error ? "alert-danger" : "alert-success"}`;
    }
    function active() {
        return states.get(document.querySelector(".tab-pane.active canvas").dataset.marker);
    }
    function render(state) {
        const {canvas, image, annotations} = state;
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
    async function request(params, options) {
        const response = await fetch(`${endpoint}?${new URLSearchParams(params)}`, options);
        const result = await response.json();
        if (!response.ok || result.status !== "success") throw new Error(result.message || "Gagal mengakses penanda operasi");
        return result;
    }
    async function history() {
        const result = await request({visit_ID: config.visit});
        const tbody = document.getElementById("marker-history");
        tbody.replaceChildren();
        if (!result.data.length) {
            const cell = tbody.insertRow().insertCell();
            cell.colSpan = 5;
            cell.textContent = "Belum ada dokumen penanda operasi tersimpan.";
        }
        result.data.forEach((document, index) => {
            const tr = tbody.insertRow();
            for (const text of [result.data.length - index, config.types[document.marker_type], document.created_at, document.created_by]) {
                tr.insertCell().textContent = text;
            }
            const actions = tr.insertCell();
            const load = window.document.createElement("button");
            load.type = "button";
            load.className = "btn btn-sm btn-outline-primary me-2";
            load.textContent = "Buka / Lanjutkan";
            load.addEventListener("click", async () => {
                if (saving || loading) return;
                loading = true;
                save.disabled = true;
                notes.disabled = true;
                load.disabled = true;
                try {
                    const result = await request({visit_ID: config.visit, id: document.id});
                    const state = states.get(result.data.marker_type);
                    if (!state.ready) throw new Error("Template gambar belum tersedia");
                    if (state.dirty && !confirm("Ganti draft yang belum disimpan pada gambar ini dengan dokumen tersebut?")) return;
                    state.annotations = result.data.annotations;
                    state.notes = result.data.notes;
                    state.dirty = false;
                    state.stroke = null;
                    render(state);
                    bootstrap.Tab.getOrCreateInstance(window.document.getElementById(`marker-tab-${result.data.marker_type}`)).show();
                    notes.value = state.notes;
                    notify("Dokumen dibuka. Perubahan berikutnya akan disimpan sebagai versi baru.");
                } catch (error) {
                    console.error(error);
                    notify(error.message, true);
                } finally {
                    loading = false;
                    save.disabled = false;
                    notes.disabled = false;
                    load.disabled = false;
                }
            });
            const pdf = window.document.createElement("a");
            pdf.className = "btn btn-sm btn-outline-secondary";
            pdf.textContent = "Cetak / PDF";
            pdf.target = "_blank";
            pdf.rel = "noopener";
            pdf.href = `module/admin/print/formulir_penanda_operasi?${new URLSearchParams({no: config.visit, rm: config.rm, id: document.id})}`;
            actions.append(load, pdf);
        });
    }
    document.querySelectorAll(".marker-canvas").forEach(canvas => {
        const image = new Image();
        const state = {canvas, image, annotations: [], notes: "", dirty: false, ready: false, stroke: null, pointer: null};
        states.set(canvas.dataset.marker, state);
        image.onload = () => {
            canvas.width = image.naturalWidth;
            canvas.height = image.naturalHeight;
            state.ready = true;
            render(state);
        };
        image.onerror = () => notify(`Gagal memuat template ${config.types[canvas.dataset.marker]}. Muat ulang halaman.`, true);
        image.src = `assets/images/site-marker-${canvas.dataset.marker}.png`;
        canvas.addEventListener("pointerdown", event => {
            if (!state.ready || saving || loading || state.stroke || (event.pointerType === "mouse" && event.button !== 0)) return;
            if (state.annotations.length >= 1000) {
                notify("Maksimal 1000 penanda per dokumen.", true);
                return;
            }
            const [x, y] = position(event, canvas);
            const color = document.getElementById("marker-color").value;
            const size = Number(document.getElementById("marker-size").value);
            if (!Number.isFinite(size) || size < 1 || size > 20) {
                notify("Ukuran garis harus antara 1 dan 20.", true);
                return;
            }
            if (document.getElementById("marker-tool").value === "text") {
                const text = document.getElementById("marker-text").value.trim();
                if (!text) {
                    notify("Isi teks sebelum mengeklik gambar.", true);
                    return;
                }
                state.annotations.push({kind: "text", x, y, text, color, size: Math.max(16, size * 5)});
            } else {
                state.stroke = {kind: "stroke", color, size, points: [[x, y]]};
                state.pointer = event.pointerId;
                state.annotations.push(state.stroke);
                canvas.setPointerCapture(event.pointerId);
            }
            state.dirty = true;
            render(state);
        });
        canvas.addEventListener("pointermove", event => {
            if (!state.stroke || saving || loading || event.pointerId !== state.pointer || state.stroke.points.length >= 10000) return;
            state.stroke.points.push(position(event, canvas));
            render(state);
        });
        const stop = event => {
            if (event.pointerId === state.pointer) {
                state.stroke = null;
                state.pointer = null;
            }
        };
        canvas.addEventListener("pointerup", stop);
        canvas.addEventListener("pointercancel", stop);
        canvas.addEventListener("lostpointercapture", stop);
    });
    document.querySelectorAll('[data-bs-toggle="pill"]').forEach(tab => {
        tab.addEventListener("shown.bs.tab", () => {
            notes.value = active().notes;
        });
    });
    document.getElementById("marker-undo").addEventListener("click", () => {
        const state = active();
        if (!state.ready || saving || loading) return;
        state.stroke = null;
        state.annotations.pop();
        state.dirty = true;
        render(state);
    });
    document.getElementById("marker-clear").addEventListener("click", () => {
        const state = active();
        if (!state.ready || saving || loading || !confirm("Bersihkan semua coretan dan teks pada gambar ini?")) return;
        state.stroke = null;
        state.annotations = [];
        state.dirty = true;
        render(state);
    });
    notes.addEventListener("input", () => {
        active().notes = notes.value;
        active().dirty = true;
    });
    save.addEventListener("click", async () => {
        const state = active();
        if (saving || loading) return;
        if (!state.ready || !state.annotations.length) {
            notify("Tambahkan coretan atau teks sebelum menyimpan.", true);
            return;
        }
        state.stroke = null;
        saving = true;
        notes.disabled = true;
        save.disabled = true;
        save.textContent = "Menyimpan...";
        try {
            const result = await request({}, {
                method: "POST", headers: {"Content-Type": "application/json"},
                body: JSON.stringify({
                    visit_ID: config.visit, marker_type: state.canvas.dataset.marker,
                    annotations: state.annotations, image: state.canvas.toDataURL("image/png"),
                    notes: state.notes, token: config.token
                })
            });
            state.dirty = false;
            notify(result.message);
            try {
                await history();
            } catch (error) {
                console.error(error);
                notify(`Versi #${result.id} tersimpan, tetapi riwayat gagal dimuat: ${error.message}`, true);
            }
        } catch (error) {
            console.error(error);
            notify(error.message, true);
        } finally {
            saving = false;
            notes.disabled = false;
            save.disabled = false;
            save.textContent = "Simpan Versi Baru";
        }
    });
    window.addEventListener("beforeunload", event => {
        if (![...states.values()].some(state => state.dirty) && !saving && !loading) return;
        event.preventDefault();
        event.returnValue = "";
    });
    history().catch(error => {
        console.error(error);
        notify(error.message, true);
    });
})();
