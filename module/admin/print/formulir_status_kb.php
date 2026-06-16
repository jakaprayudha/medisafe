<div class="form-status-kb">

   <style>
      .form-status-kb {
         width: 210mm;
         min-height: 297mm;
         margin: 0 auto;
         font-family: "Times New Roman", serif;
         padding: 0 10mm;
         font-size: 14px;
      }

      .form-status-kb .title {
         text-align: center;
         font-size: 18px;
         font-weight: bold;
         text-decoration: underline;
         margin-bottom: 15px;
      }

      .form-status-kb table {
         width: 100%;
         border-collapse: collapse;
         margin-bottom: 12px;
      }

      .form-status-kb td,
      .form-status-kb th {
         border: 1px solid #000;
         padding: 4px 6px;
         vertical-align: top;
      }

      .form-status-kb .no-border td {
         border: none !important;
      }

      .form-status-kb .box {
         display: inline-block;
         width: 18px;
         height: 18px;
         border: 1px solid #000;
         margin-right: 3px;
         text-align: center;
         line-height: 18px;
         font-weight: bold;
         font-size: 13px;
      }

      .form-status-kb .section-title {
         font-weight: bold;
         background: #eee;
         border: 1px solid #000;
         padding: 5px;
      }
   </style>

   <?php include 'kopsurat.php'; ?>

   <div class="title">KARTU STATUS PESERTA KB</div>

   <!-- Contoh, nanti isi via JS -->
   <table>
      <tr>
         <td width="50%">
            <strong>I. Nomor Kode Faskes KB</strong><br>
            <span id="kb_faskes"></span>
         </td>
         <td width="50%">
            <strong>II. Kode Keluarga Indonesia</strong><br>
            <span id="kb_keluarga"></span>
         </td>
      </tr>
   </table>

   <table>
      <tr>
         <td><strong>III. Nama Peserta KB</strong><br><span id="kb_nama_istri"></span></td>
         <td><strong>IV. Tgl Lahir / Umur Istri</strong><br><span id="kb_tgllahir"></span> Usia : <span id="kb_usia"></span></td>
      </tr>

      <tr>
         <td><strong>V. Nama Suami</strong><br><span id="kb_nama_suami"></span></td>
         <td><strong>VI. Pendidikan Suami/Istri</strong><br>
            Suami: <span id="kb_pendidikan_suami"></span><br>
            Istri: <span id="kb_pendidikan_istri"></span>
         </td>
      </tr>

      <tr>
         <td><strong>VII. Alamat Peserta KB</strong><br><span id="kb_alamat"></span></td>
         <td><strong>VIII. Pekerjaan Suami/Istri</strong><br>
            Suami: <span id="kb_pekerjaan_suami"></span><br>
            Istri: <span id="kb_pekerjaan_istri"></span>
         </td>
      </tr>
   </table>

   <table>
      <tr>
         <td><strong>X. Jumlah Anak Hidup</strong><br>
            Lk: <span id="kb_anak_lk"></span> — Pr: <span id="kb_anak_pr"></span>
         </td>
         <td><strong>XI. Umur Anak Terakhir</strong><br><span id="kb_umur_last"></span> Tahun</td>
         <td><strong>XII. KB Terakhir</strong><br><span id="kb_terakhir"></span></td>
      </tr>
   </table>

   <div class="section-title">XIV. PENAPISAN (SKRINING)</div>

   <table>
      <tr>
         <td><strong>1. Haid Terakhir</strong><br><span id="kb_haid"></span></td>
         <td><strong>2. Hamil?</strong><br><span id="kb_hamil"></span></td>
      </tr>

      <tr>
         <td><strong>3. GPA</strong><br>
            G: <span id="kb_g"></span>
            P: <span id="kb_p"></span>
            A: <span id="kb_a"></span>
         </td>
         <td><strong>4. Menyusui</strong><br><span id="kb_menyusui"></span></td>
      </tr>

      <tr>
         <td><strong>5. Riwayat Penyakit</strong><br><span id="kb_sakit"></span></td>
         <td><strong>6. Keadaan Umum</strong><br><span id="kb_keadaan"></span></td>
      </tr>

      <tr>
         <td><strong>7. Berat Badan</strong><br><span id="kb_bb"></span> Kg</td>
         <td><strong>8. Tekanan Darah</strong><br><span id="kb_td"></span></td>
      </tr>

      <tr>
         <td colspan="2"><strong>9–12. Pemeriksaan Tambahan</strong><br><span id="kb_periksa_tamb"></span></td>
      </tr>
   </table>

   <div class="section-title">XV. Metode KB yang dipilih</div>
   <table>
      <tr>
         <td><span id="kb_pilihan"></span></td>
      </tr>
   </table>

   <table>
      <tr>
         <td><strong>XVI. Tanggal Dilayani</strong><br><span id="kb_tgl_layan"></span></td>
         <td><strong>XVII. Tanggal Dicabut</strong><br><span id="kb_tgl_cabut"></span></td>
      </tr>
      <tr>
         <td colspan="2"><strong>XIX. Penanggung Jawab</strong><br><span id="kb_pj"></span></td>
      </tr>
   </table>

</div>
<script>
   document.addEventListener("DOMContentLoaded", function() {
      const url = new URLSearchParams(window.location.search);
      const no = url.get("no");
      const rm = url.get("rm");

      fetch(`getkb.php?no=${no}&rm=${rm}`)
         .then(res => res.json())
         .then(resp => {

            if (!resp || resp.status !== "success") return;

            const d = resp.data;

            const set = (id, val) => document.getElementById(id).innerText = val ?? "-";

            set("kb_faskes", d.faskes_kode);
            set("kb_keluarga", d.kode_keluarga);
            set("kb_nama_istri", d.patient_name);
            set("kb_tgllahir", d.patient_datebirth);
            set("kb_usia", d.usia_format); // ⬅️ ini tambahan
            set("kb_nama_suami", d.nama_suami);

            set("kb_pendidikan_suami", d.pendidikan_suami);
            set("kb_pendidikan_istri", d.pendidikan_istri);

            set("kb_alamat", d.patient_address);
            set("kb_pekerjaan_suami", d.pekerjaan_suami);
            set("kb_pekerjaan_istri", d.pekerjaan_istri);

            set("kb_anak_lk", d.anak_lk);
            set("kb_anak_pr", d.anak_pr);
            set("kb_umur_last", d.umur_anak_terakhir);

            set("kb_terakhir", d.kb_terakhir);

            set("kb_haid", d.haid_terakhir);
            set("kb_hamil", d.hamil);

            set("kb_g", d.gpa_g);
            set("kb_p", d.gpa_p);
            set("kb_a", d.gpa_a);

            set("kb_menyusui", d.menyusui);
            set("kb_sakit", d.riwayat_sakit);
            set("kb_keadaan", d.keadaan_umum);
            set("kb_bb", d.berat_badan);
            set("kb_td", d.tekanan_darah);

            set("kb_periksa_tamb", d.pemeriksaan_tambahan);

            set("kb_pilihan", d.metode_pilihan);

            set("kb_tgl_layan", d.tgl_dilayani);
            set("kb_tgl_cabut", d.tgl_dicabut);

            set("kb_pj", d.penanggung_jawab);
         });
   });
</script>