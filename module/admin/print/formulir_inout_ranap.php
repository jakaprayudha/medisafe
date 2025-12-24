<?php
require '../../../database/connect.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <title>Lembar Rawat Inap</title>
   <style>
      table {
         width: 100%;
         border-collapse: collapse;
         border: 1px solid #000;
         font-size: 10pt;
      }

      th,
      td {
         border: 1px solid #000;
         padding: 4px 6px;
         vertical-align: top;
      }

      .center {
         text-align: center;
      }

      .no-border {
         border: none !important;
      }

      .no-print {
         margin-top: 15px;
         text-align: center;
      }

      @media print {
         .no-print {
            display: none;
         }
      }
   </style>
</head>

<body>

   <?php include 'kopsurat.php'; ?>

   <h3 class="center">Lembar Masuk dan Keluar Pasien Rawat Inap</h3>

   <table>
      <tr>
         <td>Nama Pasien: <span id="nama_pasien"></span></td>
         <td>No. Rekam Medik: <span id="nomor_rm"></span></td>
      </tr>

      <tr>
         <td>Tanggal Lahir: <span id="tanggal_lahir"></span></td>
         <td>Agama: <span id="agama"></span></td>
      </tr>

      <tr>
         <td>Pendidikan: <span id="pendidikan"></span></td>
         <td>Jenis Kelamin: <span id="sex"></span></td>
      </tr>

      <tr>
         <td>Pekerjaan: <span id="pekerjaan"></span></td>
         <td>No. BPJS: <span id="nomor_bpjs"></span></td>
      </tr>

      <tr>
         <td colspan="2">Alamat Lengkap: <span id="alamat"></span></td>
      </tr>

      <tr>
         <td>Asuransi Lain</td>
         <td>
            <strong>Cara Masuk Rawat Inap</strong><br>
            Klinik Tutun Sehati<br>
            1. Dokter / Paramedis<br>
            2. Pustu / Polindes<br>
            3. Instansi Kesehatan<br>
            4. Kasus Polisi<br>
            <strong>5. Datang Sendiri</strong> <input type="checkbox" checked>
         </td>
      </tr>

      <tr>
         <td colspan="2">Status Perkawinan: <span id="status_perkawinan"></span></td>
      </tr>

      <tr>
         <td colspan="2">
            Tanggal Masuk: <span id="tanggal_masuk"></span>
            Jam: <span id="jam_masuk"></span>
         </td>
      </tr>

      <tr>
         <td colspan="2">Penanggung Jawab: <span id="penanggung_jawab"></span></td>
      </tr>

      <tr>
         <td colspan="2">Alamat Penanggung Jawab: <span id="alamat_pj"></span></td>
      </tr>

      <tr>
         <td>Ruang Rawat / Kelas</td>
         <td id="ruang_rawat"></td>
      </tr>

      <tr>
         <td>
            Tanggal Keluar: <span id="tanggal_keluar"></span>
         </td>
         <td>
            Jam Keluar: <span id="jam_keluar"></span>
         </td>
      </tr>

      <tr>
         <td>Diagnosa Medik</td>
         <td>
            <span id="diagnosa_medik"></span><br>
            Lama Dirawat: <span id="lama_dirawat"></span> Hari
         </td>
      </tr>

      <tr>
         <td>Diagnosa Akhir</td>
         <td>
            Utama: <span id="diagnosa_utama"></span><br><br>
            Komplikasi: <span id="diagnosa_komplikasi"></span>
         </td>
      </tr>

      <tr>
         <td>Penyebab Cedera / Keracunan</td>
         <td id="penyebab_keracunan"></td>
      </tr>

      <tr>
         <td>Nama Operasi / Tindakan</td>
         <td id="nama_operasi"></td>
      </tr>

      <tr>
         <td>Infeksi Nosokomial</td>
         <td id="infeksi_nosokomial"></td>
      </tr>

      <tr>
         <td>Penyebab Infeksi</td>
         <td id="penyebab_infeksi"></td>
      </tr>

      <tr>
         <td>Alergi</td>
         <td id="alergi"></td>
      </tr>

      <tr>
         <td>Radioterapi / Kedokteran Nuklir</td>
         <td id="radioterapi"></td>
      </tr>

      <tr>
         <td>Imunisasi</td>
         <td id="imunisasi"></td>
      </tr>

      <tr>
         <td>Transfusi Darah</td>
         <td id="transfusi"></td>
      </tr>

      <tr>
         <td>Keadaan Keluar</td>
         <td id="keadaan_keluar"></td>
      </tr>

      <tr>
         <td>Cara Keluar</td>
         <td id="cara_keluar"></td>
      </tr>

      <tr>
         <td>Dokter Merawat</td>
         <td id="dokter_merawat"></td>
      </tr>
   </table>

   <div class="no-print">
      <button onclick="window.print()">🖨 Cetak Halaman</button>
   </div>

   <script>
      document.addEventListener("DOMContentLoaded", () => {
         const params = new URLSearchParams(window.location.search);
         const no = params.get("no");
         const rm = params.get("rm");

         if (!no || !rm) return;

         fetch(`getranap.php?no=${no}&rm=${rm}`)
            .then(res => res.json())
            .then(resp => {
               if (resp?.status !== "success") return;

               const d = resp.data;

               const set = (id, value) => {
                  const el = document.getElementById(id);
                  if (el) el.textContent = value ?? "-";
               };

               Object.keys(d).forEach(key => set(key, d[key]));
            })
            .catch(err => console.error(err));
      });
   </script>

</body>

</html>