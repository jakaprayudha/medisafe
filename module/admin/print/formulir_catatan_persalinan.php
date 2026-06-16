<div class="form-catatan-persalinan">
   <style>
      @page {
         size: A4;
         margin: 12mm;
      }

      .cp-body {
         font-family: "Times New Roman", serif;
         font-size: 11pt;
         color: #000;
         line-height: 1.3;
      }

      .cp-title {
         font-weight: bold;
         text-transform: uppercase;
         font-size: 15pt;
         margin-bottom: 10px;
         text-align: center;
      }

      .cp-two-col {
         display: flex;
         justify-content: space-between;
      }

      .cp-col {
         width: 49%;
      }

      .cp-line,
      .cp-longline {
         border-bottom: 1px dotted #000;
         display: inline-block;
      }

      .cp-line {
         width: 180px;
      }

      .cp-longline {
         width: 300px;
      }

      .cp-check {
         width: 11px;
         height: 11px;
         border: 1px solid #000;
         display: inline-flex;
         align-items: center;
         justify-content: center;
         margin-right: 4px;
         font-weight: bold;
         font-size: 12px;
      }

      .cp-table {
         width: 100%;
         border-collapse: collapse;
         margin-top: 8px;
      }

      .cp-table th,
      .cp-table td {
         border: 1px solid #000;
         padding: 4px;
         text-align: center;
         font-size: 10pt;
      }

      .cp-table th {
         background: #f4f4f4;
      }

      .cp-left {
         text-align: left;
      }
   </style>

   <div class="cp-body">
      <?php
      require 'kopsurat.php';
      ?>

      <div class="cp-title">CATATAN PERSALINAN</div>

      <div class="cp-two-col">
         <div class="cp-col">

            <p>1. Tanggal : <span id="tgl_persalinan" class="cp-line"></span></p>
            <p>2. Nama Bidan : <span id="nama_bidan" class="cp-longline"></span></p>

            <p>3. Tempat Persalinan :</p>
            <p>
               <span id="tp_rumah" class="cp-check"></span> Rumah Ibu &nbsp;
               <span id="tp_puskesmas" class="cp-check"></span> Puskesmas &nbsp;
               <span id="tp_polindes" class="cp-check"></span> Polindes &nbsp;
               <span id="tp_rs" class="cp-check"></span> Rumah Sakit &nbsp;
               <span id="tp_klinik" class="cp-check"></span> Klinik / Swasta
            </p>

            <p>4. Alamat tempat persalinan : <span id="alamat_tempat" class="cp-longline"></span></p>
            <p>5. Catatan I : <span id="catatan1" class="cp-line"></span> &nbsp; KALA : I / II / III / IV</p>
            <p>6. Tempat rujukan : <span id="tempat_rujukan" class="cp-line"></span></p>

            <p>7. Pendamping pada saat menunggu:</p>
            <p>
               <span id="pd_bidan" class="cp-check"></span> Bidan &nbsp;
               <span id="pd_suami" class="cp-check"></span> Suami &nbsp;
               <span id="pd_keluarga" class="cp-check"></span> Keluarga &nbsp;
               <span id="pd_tidakada" class="cp-check"></span> Tidak ada
            </p>

            <h4>KALA I</h4>

            <p>
               9. Partograf melewati garis waspada?
               <span id="waspada_ya" class="cp-check"></span> Ya
               <span id="waspada_tidak" class="cp-check"></span> Tidak
            </p>

            <p>10. Masalah lain : <span id="masalah_kala1" class="cp-longline"></span></p>
            <p>11. Penatalaksanaan : <span id="penatalaksanaan_kala1" class="cp-longline"></span></p>

            <h4>KALA II</h4>

            <p>
               13. Episiotomi :
               <span id="epi_ya" class="cp-check"></span> Ya
               <span id="epi_tidak" class="cp-check"></span> Tidak
            </p>

            <p>
               14. Pendamping persalinan :
               <span id="pend_suami" class="cp-check"></span> Suami
               <span id="pend_teman" class="cp-check"></span> Teman
               <span id="pend_none" class="cp-check"></span> Tidak ada
            </p>

            <p>
               15. Gawat janin :
               <span id="gawat_ya" class="cp-check"></span> Ya
               <span id="gawat_tidak" class="cp-check"></span> Tidak
            </p>

            <p>
               16. Distosia bahu :
               <span id="distosia_ya" class="cp-check"></span> Ya
               <span id="distosia_tidak" class="cp-check"></span> Tidak
            </p>

            <p>17. Masalah lain : <span id="masalah_kala2" class="cp-longline"></span></p>
            <p>18. Penatalaksanaan : <span id="penatalaksanaan_kala2" class="cp-longline"></span></p>

            <h4>KALA III</h4>

            <p>20. Lama kala III : <span id="lama_kala3" class="cp-line"></span></p>

            <p>
               21. Oksitosin 10U IM?
               <span id="ok_ya" class="cp-check"></span> Ya
               <span id="ok_tidak" class="cp-check"></span> Tidak
            </p>

            <p>
               22. Pemberian ulang 10U?
               <span id="okulang_ya" class="cp-check"></span> Ya
               <span id="okulang_tidak" class="cp-check"></span> Tidak
            </p>

            <p>
               23. Plasenta lahir ≤ 30 menit?
               <span id="pl_30_ya" class="cp-check"></span> Ya
               <span id="pl_30_tidak" class="cp-check"></span> Tidak
            </p>

            <p>
               24. Masase fundus?
               <span id="masase_ya" class="cp-check"></span> Ya
               <span id="masase_tidak" class="cp-check"></span> Tidak
            </p>

            <p>
               25. Plasenta lengkap?
               <span id="pl_lengkap_ya" class="cp-check"></span> Ya
               <span id="pl_lengkap_tidak" class="cp-check"></span> Tidak
            </p>

            <p>26. Jika tidak lengkap, tindakan : <span id="tindakan_plasenta" class="cp-longline"></span></p>

         </div>

         <!-- ======================= KOLOM KANAN ======================= -->
         <div class="cp-col">

            <p>27. Laserasi :</p>
            <p>
               <span id="las_ya" class="cp-check"></span> Ya, lokasi:
               <span id="laserasi_lokasi" class="cp-line"></span><br>
               <span id="las_tidak" class="cp-check"></span> Tidak
            </p>

            <p>28. Derajat Laserasi : 1 / 2 / 3 / 4</p>

            <p>29. Alasan tindakan : <span id="alasan_tindakan" class="cp-longline"></span></p>

            <p>30. Jumlah perdarahan : <span id="perdarahan" class="cp-line"></span> cc</p>

            <p>31. Masalah lain : <span id="masalah_lain" class="cp-longline"></span></p>

            <h4>BAYI BARU LAHIR</h4>

            <p>34. Berat Badan : <span id="bb" class="cp-line"></span> gram</p>
            <p>35. Panjang Badan : <span id="pb" class="cp-line"></span> cm</p>

            <p>
               36. Jenis Kelamin:
               <span id="jk_l" class="cp-check"></span> L
               <span id="jk_p" class="cp-check"></span> P
            </p>

            <p>38. Cacat bawaan : <span id="cacat" class="cp-longline"></span></p>

            <p>
               39. Pemberian ASI:
               <span id="asi_ya" class="cp-check"></span> Ya
               <span id="asi_tidak" class="cp-check"></span> Tidak
            </p>

            <p>Waktu : <span id="asi_waktu" class="cp-line"></span> jam setelah lahir</p>

            <h3>Pemantauan Persalinan Kala IV</h3>

            <table class="cp-table">
               <thead>
                  <tr>
                     <th>Jam Ke</th>
                     <th>Waktu</th>
                     <th>Tensi</th>
                     <th>Nadi</th>
                     <th>Tinggi Fundus</th>
                     <th>Kontraksi</th>
                     <th>Kandung Kemih</th>
                     <th>Perdarahan</th>
                  </tr>
               </thead>
               <tbody id="kala4_table"></tbody>
            </table>

            <p>41. Masalah Kala IV : <span id="masalah_kala4" class="cp-longline"></span></p>

         </div>
      </div>

   </div>
</div>

<script>
   document.addEventListener("DOMContentLoaded", function() {

      const url = new URLSearchParams(window.location.search);
      const no = url.get("no");
      const rm = url.get("rm");

      if (!no || !rm) return;

      fetch("get_persalinan.php?no=" + no + "&rm=" + rm)
         .then(r => r.json())
         .then(res => {
            if (res.status !== "success") return;

            const h = res.header;
            const d = res.detail;

            const set = (id, value) => {
               const el = document.getElementById(id);
               if (el) el.textContent = value ?? "";
            };

            const cek = (id, cond) => {
               const el = document.getElementById(id);
               if (el) el.textContent = cond ? "✓" : "";
            };

            // HEADER
            set("tgl_persalinan", h.tanggal_persalinan);
            set("nama_bidan", h.nama_bidan);

            cek("tp_rumah", h.tempat_persalinan === "rumah_ibu");
            cek("tp_puskesmas", h.tempat_persalinan === "puskesmas");
            cek("tp_polindes", h.tempat_persalinan === "polindes");
            cek("tp_rs", h.tempat_persalinan === "rs");
            cek("tp_klinik", h.tempat_persalinan === "klinik");

            set("alamat_tempat", h.alamat_tempat);
            set("catatan1", h.catatan_1);
            set("tempat_rujukan", h.tempat_rujukan);

            cek("pd_bidan", h.pendamping === "bidan");
            cek("pd_suami", h.pendamping === "suami");
            cek("pd_keluarga", h.pendamping === "keluarga");
            cek("pd_tidakada", h.pendamping === "none");

            // KALA 1
            cek("waspada_ya", h.partograf_waspada === "ya");
            cek("waspada_tidak", h.partograf_waspada === "tidak");

            set("masalah_kala1", h.masalah_kala1);
            set("penatalaksanaan_kala1", h.penatalaksanaan_kala1);

            // KALA 2
            cek("epi_ya", h.episiotomi === "ya");
            cek("epi_tidak", h.episiotomi === "tidak");

            cek("pend_suami", h.pendamping_persalinan === "suami");
            cek("pend_teman", h.pendamping_persalinan === "teman");
            cek("pend_none", h.pendamping_persalinan === "none");

            cek("gawat_ya", h.gawat_janin === "ya");
            cek("gawat_tidak", h.gawat_janin === "tidak");

            cek("distosia_ya", h.distosia_bahu === "ya");
            cek("distosia_tidak", h.distosia_bahu === "tidak");

            set("masalah_kala2", h.masalah_kala2);
            set("penatalaksanaan_kala2", h.penatalaksanaan_kala2);

            // KALA 3
            set("lama_kala3", h.lama_kala3);

            cek("ok_ya", h.oksitosin === "ya");
            cek("ok_tidak", h.oksitosin === "tidak");

            cek("okulang_ya", h.oksitosin_ulang === "ya");
            cek("okulang_tidak", h.oksitosin_ulang === "tidak");

            cek("pl_30_ya", h.plasenta_30mnt === "ya");
            cek("pl_30_tidak", h.plasenta_30mnt === "tidak");

            cek("masase_ya", h.masase === "ya");
            cek("masase_tidak", h.masase === "tidak");

            cek("pl_lengkap_ya", h.plasenta_lengkap === "ya");
            cek("pl_lengkap_tidak", h.plasenta_lengkap === "tidak");

            set("tindakan_plasenta", h.tindakan_plasenta);

            // LASERASI
            cek("las_ya", h.laserasi === "ya");
            cek("las_tidak", h.laserasi === "tidak");

            set("laserasi_lokasi", h.laserasi_lokasi);
            set("alasan_tindakan", h.alasan_tindakan);
            set("perdarahan", h.jumlah_perdarahan);
            set("masalah_lain", h.masalah_lain);

            // BAYI
            set("bb", h.bb);
            set("pb", h.pb);

            cek("jk_l", h.jk === "L");
            cek("jk_p", h.jk === "P");

            set("cacat", h.cacat_bawaan);

            cek("asi_ya", h.asi === "ya");
            cek("asi_tidak", h.asi === "tidak");

            set("asi_waktu", h.asi_waktu);

            // KALA 4 TABLE
            const tbody = document.getElementById("kala4_table");
            tbody.innerHTML = "";

            d.forEach(row => {
               tbody.innerHTML += `
               <tr>
                  <td>${row.jam_ke}</td>
                  <td>${row.waktu}</td>
                  <td>${row.tensi}</td>
                  <td>${row.nadi}</td>
                  <td>${row.tfu}</td>
                  <td>${row.kontraksi}</td>
                  <td>${row.kandung_kemih}</td>
                  <td>${row.perdarahan}</td>
               </tr>
            `;
            });

         });
   });
</script>