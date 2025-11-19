<?php
require '../../../database/connect.php';
?>


<body>
   <?php include 'kopsurat.php'; ?>
   <div class="form-inoutranap">
      <style>
         table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            font-size: 10pt;
         }

         td,
         th {
            border: 1px solid #000;
            padding: 3px 5px;
            vertical-align: top;
         }

         .no-border {
            border: none !important;
         }

         .center {
            text-align: center;
         }

         .no-print {
            margin-top: 10px;
            text-align: center;
         }

         @media print {
            .no-print {
               display: none;
            }
         }
      </style>

      <table>
         <tr>
            <td>Nama Pasien :</td>
            <td colspan="2" id="nama_pasien"></td>
            <td>Nomor Dokumentasi Medik</td>
         </tr>

         <tr>
            <td>Tanggal Lahir :</td>
            <td id="tanggal_lahir"></td>
            <td>Agama :</td>
            <td id="agama"></td>
         </tr>

         <tr>
            <td>Pendidikan :</td>
            <td id="pendidikan"></td>
            <td>Sex : LK/PR</td>
            <td id="sex"></td>
         </tr>

         <tr>
            <td>Pekerjaan :</td>
            <td id="pekerjaan"></td>
            <td>No. Kartu Peserta BPJS :</td>
            <td id="nomor_bpjs"></td>
         </tr>

         <tr>
            <td>Alamat Lengkap :</td>
            <td colspan="3" id="alamat"></td>
         </tr>

         <tr>
            <td>Asuransi Lain</td>
            <td colspan="3">
               <strong>Cara Masuk Rawat Inap</strong><br>
               Klinik Tutun Sehati<br>
               1. Dokter/Para Medis<br>
               2. Pustu/Polindes<br>
               3. Instansi Kesehatan<br>
               4. Kasus Polisi<br>
               5. Datang Sendiri
            </td>
         </tr>

         <tr>
            <td>Status Perkawinan</td>
            <td colspan="3" id="status_perkawinan"></td>
         </tr>

         <tr>
            <td>Tanggal Masuk</td>
            <td id="tanggal_masuk"></td>
            <td>Jam :</td>
            <td id="jam_masuk"></td>
         </tr>

         <tr>
            <td>Nama Penanggung Jawab:</td>
            <td colspan="3" id="penanggung_jawab"></td>
         </tr>

         <tr>
            <td>Alamat Lengkap:</td>
            <td colspan="2" id="alamat_pj"></td>
            <td>Tanggal Dipindahkan: <span id="tanggal_pindah"></span><br>Jam: <span id="jam_pindah"></span></td>
         </tr>

         <tr>
            <td>Bagian / Ruang Rawat / Kelas</td>
            <td colspan="2" id="ruang_rawat"></td>
            <td></td>
         </tr>

         <tr>
            <td>Tanggal Keluar</td>
            <td colspan="2" id="tanggal_keluar"></td>
            <td>Jam: <span id="jam_keluar"></span></td>
         </tr>

         <tr>
            <td>Diagnosa Medik :</td>
            <td colspan="2" id="diagnosa_medik"></td>
            <td>Lama Dirawat: <span id="lama_dirawat"></span> Hari</td>
         </tr>

         <tr>
            <td>Diagnosa Akhir</td>
            <td colspan="3">
               Utama : <span id="diagnosa_utama"></span><br><br>
               Komplikasi : <span id="diagnosa_komplikasi"></span>
            </td>
         </tr>

         <tr>
            <td>Penyebab Luar Cedera & Keracunan / Morfologi Neoplasma</td>
            <td colspan="3" id="penyebab_keracunan"></td>
         </tr>

         <tr>
            <td>Nama Operasi / Tindakan</td>
            <td colspan="3" id="nama_operasi"></td>
         </tr>

         <tr>
            <td>Infeksi Nosokomial :</td>
            <td id="infeksi_nosokomial"></td>
            <td>Penyebab Infeksi :</td>
            <td id="penyebab_infeksi"></td>
         </tr>

         <tr>
            <td>Alergi Terhadap :</td>
            <td id="alergi"></td>
            <td>Radio Therapy / Kedokteran Nuklir</td>
            <td id="radioterapi"></td>
         </tr>

         <tr>
            <td>Imunisasi Selama Dirawat:</td>
            <td id="imunisasi"></td>
            <td>Transfusi Darah:</td>
            <td id="transfusi"></td>
         </tr>

         <tr>
            <td>Keadaan Keluar :</td>
            <td colspan="3" id="keadaan_keluar"></td>
         </tr>

         <tr>
            <td>Cara Keluar :</td>
            <td colspan="3" id="cara_keluar"></td>
         </tr>

         <tr>
            <td>Dokter yang merawat</td>
            <td colspan="3" id="dokter_merawat"></td>
         </tr>
      </table>

      <div class="no-print">
         <button onclick="window.print()">🖨 Cetak Halaman</button>
      </div>
   </div>

</body>
<script>
   document.addEventListener("DOMContentLoaded", function() {

      const url = new URLSearchParams(window.location.search);
      const no = url.get("no");
      const rm = url.get("rm");

      if (!no || !rm) return;

      fetch(`getranap.php?no=${no}&rm=${rm}`)
         .then(res => res.json())
         .then(resp => {

            if (!resp || resp.status !== "success") return;

            const d = resp.data;

            const set = (id, val) => {
               const el = document.getElementById(id);
               if (el) el.innerText = val ?? "";
            };

            set("nama_pasien", d.nama_pasien);
            set("tanggal_lahir", d.tanggal_lahir);
            set("agama", d.agama);
            set("pendidikan", d.pendidikan);
            set("sex", d.sex);
            set("pekerjaan", d.pekerjaan);
            set("nomor_bpjs", d.nomor_bpjs);
            set("alamat", d.alamat);

            set("status_perkawinan", d.status_perkawinan);
            set("tanggal_masuk", d.tanggal_masuk);
            set("jam_masuk", d.jam_masuk);

            set("penanggung_jawab", d.penanggung_jawab);
            set("alamat_pj", d.alamat_pj);
            set("tanggal_pindah", d.tanggal_pindah);
            set("jam_pindah", d.jam_pindah);

            set("ruang_rawat", d.ruang_rawat);
            set("tanggal_keluar", d.tanggal_keluar);
            set("jam_keluar", d.jam_keluar);

            set("diagnosa_medik", d.diagnosa_medik);
            set("lama_dirawat", d.lama_dirawat);
            set("diagnosa_utama", d.diagnosa_utama);
            set("diagnosa_komplikasi", d.diagnosa_komplikasi);

            set("penyebab_keracunan", d.penyebab_keracunan);
            set("nama_operasi", d.nama_operasi);

            set("infeksi_nosokomial", d.infeksi_nosokomial);
            set("penyebab_infeksi", d.penyebab_infeksi);

            set("alergi", d.alergi);
            set("radioterapi", d.radioterapi);
            set("imunisasi", d.imunisasi);
            set("transfusi", d.transfusi);

            set("keadaan_keluar", d.keadaan_keluar);
            set("cara_keluar", d.cara_keluar);
            set("dokter_merawat", d.dokter_merawat);

         });
   });
</script>