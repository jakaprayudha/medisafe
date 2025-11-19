<div class="form-partograf">

   <style>
      @page {
         size: A4;
         margin: 10mm;
      }

      body {
         font-family: "Times New Roman", serif;
         font-size: 11pt;
         color: #000;
      }

      .parto-title {
         font-size: 18pt;
         font-weight: bold;
         text-align: center;
         margin-bottom: 10px;
      }

      .parto-top-info span {
         display: inline-block;
      }

      .parto-line {
         border-bottom: 1px dotted #000;
         width: 120px;
      }

      .parto-bigline {
         border-bottom: 1px dotted #000;
         width: 220px;
      }

      /* GRID */
      table.parto-grid {
         width: 100%;
         border-collapse: collapse;
         margin-top: 10px;
      }

      .parto-grid td,
      .parto-grid th {
         border: 1px solid #000;
         width: 22px;
         height: 18px;
         text-align: center;
         font-size: 9pt;
         padding: 0;
      }

      .parto-section-title {
         font-weight: bold;
         margin-top: 12px;
         margin-bottom: 4px;
      }

      .parto-rotate {
         writing-mode: vertical-rl;
         transform: rotate(180deg);
         font-size: 9pt;
         text-align: center;
      }

      /* titik serviks & descent */
      .parto-dot {
         font-size: 14px;
         font-weight: bold;
         line-height: 14px;
      }

      .parto-dot-serviks {
         color: #000;
      }

      .parto-dot-descent {
         color: #0066cc;
      }

      /* wrapper serviks untuk garis */
      .parto-serviks-wrapper {
         position: relative;
         width: 100%;
         margin-top: 10px;
      }

      .parto-alert-line,
      .parto-action-line {
         position: absolute;
         width: 2px;
         top: 0;
         bottom: 0;
         pointer-events: none;
      }

      .parto-alert-line {
         background: orange;
      }

      .parto-action-line {
         background: red;
      }

      /* legenda */
      .parto-legend {
         margin-top: 10px;
         font-size: 9pt;
         display: flex;
         flex-wrap: wrap;
         gap: 12px;
      }

      .parto-legend-item {
         display: flex;
         align-items: center;
         gap: 4px;
      }

      .parto-legend-dot,
      .parto-legend-line {
         display: inline-block;
      }

      .parto-legend-dot {
         width: 10px;
         height: 10px;
         border-radius: 50%;
      }

      .parto-legend-dot-serviks {
         background: #000;
      }

      .parto-legend-dot-descent {
         width: 0;
         height: 0;
         border-left: 6px solid transparent;
         border-right: 6px solid transparent;
         border-bottom: 10px solid #0066cc;
      }

      .parto-legend-line-alert {
         width: 20px;
         height: 3px;
         background: orange;
      }

      .parto-legend-line-action {
         width: 20px;
         height: 3px;
         background: red;
      }
   </style>

   <?php
   require 'kopsurat.php';
   ?>

   <!-- ================= TITLE ================= -->
   <div class="parto-title">PARTOGRAF</div>

   <!-- ================= HEADER ================= -->
   <div class="parto-top-info">
      No. Register: <span id="parto_reg_no" class="parto-line"></span> &nbsp;&nbsp;
      No. Puskesmas: <span id="parto_pkm_no" class="parto-line"></span> &nbsp;&nbsp;
      Ketuban pecah jam: <span id="parto_ketuban" class="parto-line"></span>
      <br>

      Nama Ibu: <span id="parto_ibu_nama" class="parto-bigline"></span> &nbsp;&nbsp;
      Umur: <span id="parto_ibu_umur" class="parto-line"></span> &nbsp;&nbsp;
      G: <span id="parto_ibu_g" class="parto-line"></span>
      P: <span id="parto_ibu_p" class="parto-line"></span>
      A: <span id="parto_ibu_a" class="parto-line"></span>
      <br>

      Tanggal: <span id="parto_tgl" class="parto-line"></span> &nbsp;&nbsp;
      Jam: <span id="parto_jam" class="parto-line"></span> &nbsp;&nbsp;
      Mules sejak jam: <span id="parto_mules" class="parto-line"></span>
      <br>

      Alamat: <span id="parto_alamat" class="parto-bigline" style="width:300px;"></span>
   </div>

   <!-- ================= DJJ ================= -->
   <div class="parto-section-title">Denyut Jantung Janin (per menit)</div>
   <table class="parto-grid" id="parto_grid_djj"></table>

   <!-- ================= SERVIKS + DESCENT ================= -->
   <div class="parto-section-title">Pembukaan Serviks (cm) & Descent of Head</div>
   <div class="parto-serviks-wrapper" id="parto_serviks_wrap">
      <table class="parto-grid" id="parto_grid_serviks"></table>
      <div id="parto_alert_line" class="parto-alert-line"></div>
      <div id="parto_action_line" class="parto-action-line"></div>
   </div>

   <!-- ================= KONTRAKSI ================= -->
   <div class="parto-section-title">Kontraksi</div>
   <table class="parto-grid" id="parto_grid_kontraksi"></table>

   <!-- ================= OKSITOSIN ================= -->
   <div class="parto-section-title">Oksitosin U/min</div>
   <table class="parto-grid" id="parto_grid_oksitosin"></table>

   <!-- ================= NADI ================= -->
   <div class="parto-section-title">Nadi / Tekanan Darah</div>
   <table class="parto-grid" id="parto_grid_nadi"></table>

   <!-- ================= SUHU ================= -->
   <div class="parto-section-title">Suhu (°C)</div>
   <table class="parto-grid" id="parto_grid_suhu"></table>

   <!-- ================= URIN ================= -->
   <div class="parto-section-title">Urin (Protein, Aseton, Volume)</div>
   <table class="parto-grid" id="parto_grid_urin"></table>

   <!-- ================= LEGENDA ================= -->
   <div class="parto-legend">
      <div class="parto-legend-item">
         <span class="parto-legend-dot parto-legend-dot-serviks"></span>
         <span>• Pembukaan serviks</span>
      </div>
      <div class="parto-legend-item">
         <span class="parto-legend-dot-descent"></span>
         <span>▲ Descent of head</span>
      </div>
      <div class="parto-legend-item">
         <span class="parto-legend-line parto-legend-line-alert"></span>
         <span>Garis waspada</span>
      </div>
      <div class="parto-legend-item">
         <span class="parto-legend-line parto-legend-line-action"></span>
         <span>Garis tindakan</span>
      </div>
   </div>

</div>

<!-- ================= SCRIPT ================= -->
<script>
   document.addEventListener("DOMContentLoaded", function() {

      // ====== 1. BANGUN GRID DINAMIS ======
      const partoBuildGrid = (tableId, rows, cols, label, prefix) => {
         const table = document.getElementById(tableId);
         if (!table) return;

         table.innerHTML = "";

         const thead = document.createElement("thead");
         const trHead = document.createElement("tr");
         const tdLabel = document.createElement("td");
         tdLabel.rowSpan = rows + 1;
         tdLabel.className = "parto-rotate";
         tdLabel.textContent = label;

         const tdSpan = document.createElement("td");
         tdSpan.colSpan = cols;

         trHead.appendChild(tdLabel);
         trHead.appendChild(tdSpan);
         thead.appendChild(trHead);
         table.appendChild(thead);

         const tbody = document.createElement("tbody");

         for (let r = 0; r < rows; r++) {
            const tr = document.createElement("tr");
            for (let c = 0; c < cols; c++) {
               const td = document.createElement("td");
               td.id = `parto_${prefix}_${r}_${c}`;
               tr.appendChild(td);
            }
            tbody.appendChild(tr);
         }

         table.appendChild(tbody);
      };

      // bikin semua grid
      partoBuildGrid("parto_grid_djj", 10, 16, "Denyut Janin", "djj");
      partoBuildGrid("parto_grid_serviks", 8, 16, "Serviks / Descent", "serviks");
      partoBuildGrid("parto_grid_kontraksi", 6, 16, "Kontraksi", "kontraksi");
      partoBuildGrid("parto_grid_oksitosin", 4, 16, "Oksitosin", "oksitosin");
      partoBuildGrid("parto_grid_nadi", 8, 16, "Nadi / TD", "nadi");
      partoBuildGrid("parto_grid_suhu", 4, 16, "Suhu", "suhu");
      partoBuildGrid("parto_grid_urin", 4, 16, "Urin", "urin");

      // ====== 2. AMBIL PARAM URL ======
      const url = new URLSearchParams(window.location.search);
      const no = url.get("no");
      const rm = url.get("rm");

      if (!no || !rm) return;

      // ====== 3. FETCH DATA ======
      fetch("get_partograf.php?no=" + encodeURIComponent(no) + "&rm=" + encodeURIComponent(rm))
         .then(r => r.json())
         .then(res => {
            if (!res || res.status !== "success") return;

            const h = res.header || {};
            const d = res.detail || [];

            // ====== HEADER ======
            const assign = (id, val) => {
               const el = document.getElementById(id);
               if (el) el.textContent = val ?? "";
            };

            assign("parto_reg_no", h.no_register);
            assign("parto_pkm_no", h.no_puskesmas);
            assign("parto_ketuban", h.ketuban_pecah_jam);

            assign("parto_ibu_nama", h.nama_ibu);
            assign("parto_ibu_umur", h.umur);
            assign("parto_ibu_g", h.gravida);
            assign("parto_ibu_p", h.para);
            assign("parto_ibu_a", h.abortus);

            assign("parto_tgl", h.tanggal);
            assign("parto_jam", h.jam);
            assign("parto_mules", h.mules_jam);

            assign("parto_alamat", h.alamat);

            // ====== ISI GRID ======
            const serviksPoints = [];

            d.forEach(row => {
               const kategori = (row.kategori || "").toLowerCase();
               const r = parseInt(row.row_index, 10);
               const c = parseInt(row.col_index, 10);
               const v = row.value;

               if (isNaN(r) || isNaN(c)) return;

               if (kategori === "serviks") {
                  const cell = document.getElementById(`parto_serviks_${r}_${c}`);
                  if (cell) {
                     cell.innerHTML = '<span class="parto-dot parto-dot-serviks">•</span>';
                     serviksPoints.push({
                        r,
                        c
                     });
                  }
               } else if (kategori === "descent") {
                  const cell = document.getElementById(`parto_serviks_${r}_${c}`);
                  if (cell) {
                     // tambahkan descent head, bisa overlap dengan serviks
                     const span = document.createElement("span");
                     span.className = "parto-dot parto-dot-descent";
                     span.textContent = "▲";
                     cell.appendChild(span);
                  }
               } else {
                  // kategori lain: isi di grid masing-masing
                  const id = `parto_${kategori}_${r}_${c}`;
                  const cell = document.getElementById(id);
                  if (cell) {
                     cell.textContent = v;
                  }
               }
            });

            // ====== 4. Gambar Garis Waspada & Tindakan ======
            const drawAlertActionLines = () => {
               if (!serviksPoints.length) return;

               const wrapper = document.getElementById("parto_serviks_wrap");
               const table = document.getElementById("parto_grid_serviks");
               const alertLine = document.getElementById("parto_alert_line");
               const actionLine = document.getElementById("parto_action_line");

               if (!wrapper || !table || !alertLine || !actionLine) return;

               // kolom paling awal dari data serviks
               const minCol = serviksPoints.reduce((m, p) => Math.min(m, p.c), serviksPoints[0].c);
               const actionCol = minCol + 4; // 4 jam ke kanan

               const tbody = table.querySelector("tbody");
               if (!tbody) return;

               const firstRow = tbody.querySelector("tr");
               if (!firstRow) return;

               const getColCenterX = (colIndex) => {
                  const cell = firstRow.children[colIndex];
                  if (!cell) return null;
                  const cellRect = cell.getBoundingClientRect();
                  const wrapRect = wrapper.getBoundingClientRect();
                  return (cellRect.left - wrapRect.left) + (cellRect.width / 2);
               };

               const tableRect = table.getBoundingClientRect();
               const wrapRect = wrapper.getBoundingClientRect();
               const top = (tableRect.top - wrapRect.top);
               const height = tableRect.height;

               const alertX = getColCenterX(minCol);
               if (alertX !== null) {
                  alertLine.style.left = alertX + "px";
                  alertLine.style.top = top + "px";
                  alertLine.style.height = height + "px";
               }

               const actionX = getColCenterX(actionCol);
               if (actionX !== null) {
                  actionLine.style.left = actionX + "px";
                  actionLine.style.top = top + "px";
                  actionLine.style.height = height + "px";
               }
            };

            // panggil setelah layout fix
            setTimeout(drawAlertActionLines, 50);
         });
   });
</script>