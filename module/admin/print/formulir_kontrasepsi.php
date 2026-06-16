<div class="kons-form-kontrasepsi-history">

   <style>
      @page {
         size: A4 portrait;
         margin: 10mm;
      }

      body {
         font-family: Arial, sans-serif;
         margin: 0;
         padding: 0;
      }

      .kons-page {
         width: 210mm;
         min-height: 297mm;
         padding: 10mm 15mm;
         box-sizing: border-box;
         margin: 0 auto;
      }

      .kons-two-col {
         display: flex;
         justify-content: space-between;
         gap: 15px;
      }

      .kons-col {
         width: 50%;
      }

      .kons-title {
         text-align: center;
         font-size: 17px;
         font-weight: bold;
         margin-bottom: 15px;
      }

      .kons-section-title {
         font-weight: bold;
         border-bottom: 1px solid #000;
         margin-top: 15px;
         margin-bottom: 10px;
         padding-bottom: 3px;
      }

      .kons-center {
         text-align: center;
      }

      .kons-row {
         display: flex;
         margin-bottom: 6px;
      }

      .kons-label {
         width: 180px;
         font-size: 14px;
      }

      .kons-underline {
         border-bottom: 1px solid #000;
         flex: 1;
         height: 16px;
      }

      .kons-underline-short {
         width: 60px;
         border-bottom: 1px solid #000;
         height: 16px;
      }

      .kons-indent {
         padding-left: 10px;
         font-size: 14px;
      }

      .kons-boxgroup,
      .kons-longboxgroup {
         letter-spacing: 4px;
         font-size: 16px;
      }

      .kons-checkbox-row label {
         margin-right: 15px;
         font-size: 14px;
      }

      .kons-box {
         display: inline-block;
         width: 14px;
         height: 14px;
         border: 1px solid #000;
         text-align: center;
         line-height: 14px;
         font-weight: bold;
         margin-right: 3px;
      }

      .kons-signature {
         display: flex;
         justify-content: space-between;
         margin-top: 40px;
      }

      .kons-sig-box {
         width: 45%;
         text-align: center;
      }

      .kons-sign-line {
         border-bottom: 1px solid #000;
         height: 40px;
      }

      .kons-sig-label {
         margin-top: 5px;
         font-size: 14px;
      }

      .kons-service-table {
         width: 100%;
         border-collapse: collapse;
         margin-top: 10px;
      }

      .kons-service-table th,
      .kons-service-table td {
         border: 1px solid #000;
         padding: 4px;
         font-size: 13px;
      }

      .kons-small {
         font-size: 13px;
         margin-top: 15px;
      }
   </style>

   <?php require 'kopsurat.php'; ?>

   <div class="kons-two-col">

      <!-- ==================== KOLOM KIRI ==================== -->
      <div class="kons-col">
         <h2 class="kons-title">
            LEMBAR PERSETUJUAN TINDAKAN MEDIK (INFORMED CONSENT)<br>
            PELAYANAN KONTRASEPSI
         </h2>

         <div class="kons-section-title">IDENTITAS TEMPAT PELAYANAN</div>

         <div class="kons-row">
            <div class="kons-label">Nama Faskes KB / Praktik</div>
            <div class="kons-underline"><span id="cons_nama_faskes"></span></div>
         </div>

         <div class="kons-row">
            <div class="kons-label">Nomor Kode Faskes KB</div>
            <div class="kons-boxgroup" id="cons_kode_faskes"></div>
         </div>

         <div class="kons-row">
            <div class="kons-label">Nomor Klien / Nomor Sakit</div>
            <div class="kons-longboxgroup" id="cons_nomor_klien"></div>
         </div>

         <div class="kons-row">
            <div class="kons-label">Kode Keluarga Indonesia (KKI)</div>
            <div class="kons-longboxgroup" id="cons_kki"></div>
         </div>

         <div class="kons-section-title kons-center">PERSETUJUAN KLIEN</div>

         <div class="kons-row">
            <div class="kons-label">Saya yang bertanda tangan di bawah ini</div>
         </div>

         <div class="kons-row kons-indent">
            <div class="kons-label">Nama</div>
            <div class="kons-underline"><span id="cons_nama_klien"></span></div>
         </div>

         <div class="kons-row kons-indent">
            <div class="kons-label">Umur</div>
            <div class="kons-underline-short"><span id="cons_umur_klien"></span></div>
            <span style="padding-left:5px">Tahun</span>
         </div>

         <div class="kons-row kons-indent">
            <div class="kons-label">Alamat Lengkap</div>
            <div class="kons-underline"><span id="cons_alamat_klien"></span></div>
         </div>

         <div class="kons-row kons-indent">
            Setelah mendapat penjelasan dan MENGERTI SEPENUHNYA PERILAKU
            KONTRASEPSI YANG SAYA PILIH
         </div>

         <div class="kons-checkbox-row kons-indent">
            <label><span class="kons-box" id="box_suntikan"></span> SUNTIKAN</label>
            <label><span class="kons-box" id="box_pil"></span> PIL</label>
            <label><span class="kons-box" id="box_iud"></span> IUD</label>
            <label><span class="kons-box" id="box_implan"></span> IMPLAN</label>
         </div>

         <div class="kons-section-title kons-center">PERSETUJUAN SUAMI / ISTRI KLIEN</div>

         <div class="kons-row kons-indent">
            <div class="kons-label">Nama</div>
            <div class="kons-underline"><span id="cons_nama_pendamping"></span></div>
         </div>

         <div class="kons-row kons-indent">
            <div class="kons-label">Alamat</div>
            <div class="kons-underline"><span id="cons_alamat_pendamping"></span></div>
         </div>

         <p class="kons-indent kons-small">
            Selaku SUAMI/ISTRI, saya menyetujui tindakan pelayanan KB terhadap istri/suami saya tersebut.
         </p>

         <div class="kons-signature">
            <div class="kons-sig-box">
               <div class="kons-sign-line"></div>
               <div class="kons-sig-label">Yang memberi persetujuan</div>
            </div>

            <div class="kons-sig-box">
               <div class="kons-sign-line"></div>
               <div class="kons-sig-label">Petugas Pelayanan KB</div>
            </div>
         </div>
      </div>

      <!-- ================= KOLOM KANAN ================= -->
      <div class="kons-col">

         <div class="kons-section-title">Nomor Kode Faskes</div>
         <div class="kons-row">
            <div class="kons-boxgroup" id="cons_kode_faskes_right"></div>
         </div>

         <div class="kons-section-title">Nomor Klien</div>
         <div class="kons-row">
            <div class="kons-longboxgroup" id="cons_nomor_klien_right"></div>
         </div>

         <div class="kons-section-title">Kode Keluarga Indonesia (KKI)</div>
         <div class="kons-row">
            <div class="kons-longboxgroup" id="cons_kki_right"></div>
         </div>

         <div class="kons-row">
            <div class="kons-label">Umur</div>
            <div class="kons-underline-short"><span id="cons_umur_klien_right"></span></div>
         </div>

         <div class="kons-section-title">Daftar Pelayanan</div>

         <table class="kons-service-table">
            <tr>
               <th>Tindakan</th>
               <th>Jenis Pelayanan</th>
               <th>No. Kode</th>
            </tr>
            <tr>
               <td rowspan="2">Operatif</td>
               <td>Mini Laparotomi</td>
               <td>02</td>
            </tr>
            <tr>
               <td>Vasektomi</td>
               <td>03</td>
            </tr>
            <tr>
               <td rowspan="2">Pemasangan</td>
               <td>Implan</td>
               <td>11</td>
            </tr>
            <tr>
               <td>IUD CuT 380A</td>
               <td>12</td>
            </tr>
            <tr>
               <td rowspan="2">Tindakan Lain</td>
               <td>Pemeriksaan Ulang IUD</td>
               <td>15</td>
            </tr>
            <tr>
               <td>Pencabutan Implan</td>
               <td>16</td>
            </tr>
            <tr>
               <td rowspan="2">Pelayanan</td>
               <td>Suntikan</td>
               <td>13</td>
            </tr>
            <tr>
               <td>Metode Barier</td>
               <td>14</td>
            </tr>
         </table>

         <p class="kons-small">
            - Isilah kotak tanda √ pada kolom layanan sesuai tindakan.<br>
            - Kembalikan lembar ini ke petugas.
         </p>

      </div>
   </div>
</div>

<!-- ================== SCRIPT AUTOFILL ================== -->
<script>
   document.addEventListener("DOMContentLoaded", function() {

      const url = new URLSearchParams(window.location.search);
      const no = url.get("no");
      const rm = url.get("rm");

      if (!no || !rm) return;

      fetch(`getkontrasepsi.php?no=${no}&rm=${rm}`)
         .then(res => res.json())
         .then(resp => {

            if (!resp || resp.status !== "success") return;

            const d = resp.data;

            const set = (id, val) => {
               const el = document.getElementById(id);
               if (el) el.innerText = val ?? "";
            };

            set("cons_nama_faskes", d.nama_faskes);
            set("cons_kode_faskes", d.kode_faskes);
            set("cons_nomor_klien", d.nomor_klien);
            set("cons_kki", d.kki);

            set("cons_nama_klien", d.nama_klien);
            set("cons_umur_klien", d.umur_klien);
            set("cons_alamat_klien", d.alamat_klien);

            set("cons_nama_pendamping", d.nama_pendamping);
            set("cons_alamat_pendamping", d.alamat_pendamping);

            // Mirror
            set("cons_kode_faskes_right", d.kode_faskes);
            set("cons_nomor_klien_right", d.nomor_klien);
            set("cons_kki_right", d.kki);
            set("cons_umur_klien_right", d.umur_klien);

            // Centang metode KB
            const metode = (d.metode_kontrasepsi || "").toUpperCase();
            ["box_suntikan", "box_pil", "box_iud", "box_implan"].forEach(id => {
               document.getElementById(id).innerText = "";
            });

            if (metode === "SUNTIKAN") document.getElementById("box_suntikan").innerText = "√";
            if (metode === "PIL") document.getElementById("box_pil").innerText = "√";
            if (metode === "IUD") document.getElementById("box_iud").innerText = "√";
            if (metode === "IMPLAN") document.getElementById("box_implan").innerText = "√";
         });
   });
</script>