<?php
require '../../../database/connect.php';
require '../../admin/getdataclinic.php';

/*
 * Tanda tangan dirender langsung di server (dulu lewat fetch):
 * - "Yang Membuat Pernyataan" = tanda tangan keluarga dari form persetujuan -> pasien_visit.signature_opname.
 *   Dulu memanggil controller/visit/getTTD.php yang membaca signature_path (tanda tangan registrasi) -> selalu kosong.
 * - Dokter: dulu fullname LIKE '%nama%' tanpa filter faskes -> bisa mengambil user faskes lain (mis. dokter bernama sama
 *   di klinik lain yang belum punya tanda tangan). Sekarang dibatasi faskes kunjungan: kdDokter = code_doctor, lalu nama.
 * - Saksi: user yang mencetak; bila belum punya tanda tangan tidak lagi menampilkan gambar rusak.
 */
function spE($v): string
{
   return htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
}
function spNama($s): string
{
   // buang gelar depan/belakang & tanda baca untuk mencocokkan nama dokter
   $s = strtolower((string) $s);
   $s = preg_replace('/\b(drg|dr|bd|ns|apt|amd|keb|kep|kes|skm|sked|mkes|spog|spa|sppd|ssi|skep)\b\.?/', ' ', $s);
   return preg_replace('/[^a-z]/', '', $s);
}
function spFileAda(string $folder, ?string $file): ?string
{
   $file = basename((string) $file);
   if ($file === '' || !is_file(__DIR__ . "/../../../uploads/$folder/$file")) return null;
   return "../../../uploads/$folder/" . rawurlencode($file);
}

$spIdc = (int) ($_SESSION['id_customer'] ?? 0);
$spNo = (string) ($_GET['no'] ?? '');
$spVisit = null;
if ($spNo !== '' && $spIdc > 0) {
   $st = $koneksi->prepare("SELECT id_doctor, code_doctor, signature_opname, opname_keluarga_name FROM pasien_visit WHERE visit_ID = ? AND id_customer = ? LIMIT 1");
   $st->bind_param('si', $spNo, $spIdc);
   $st->execute();
   $spVisit = $st->get_result()->fetch_assoc() ?: null;
   $st->close();
}

// saksi = user yang sedang login
$st = $koneksi->prepare("SELECT signature_user FROM ms_users WHERE uid_user = ? LIMIT 1");
$spUid = (string) ($_SESSION['uid_user'] ?? '');
$st->bind_param('s', $spUid);
$st->execute();
$signatureSaksi = spFileAda('ttd_faskes', ($st->get_result()->fetch_assoc() ?: [])['signature_user'] ?? null);
$st->close();

// dokter: dalam faskes yang sama, kdDokter dulu lalu nama
$signatureDokter = null;
if ($spVisit && trim((string) $spVisit['id_doctor']) !== '') {
   $st = $koneksi->prepare("SELECT fullname, kdDokter, signature_user FROM ms_users WHERE id_customer = ? AND signature_user IS NOT NULL AND signature_user <> ''");
   $st->bind_param('i', $spIdc);
   $st->execute();
   $kand = $st->get_result()->fetch_all(MYSQLI_ASSOC);
   $st->close();
   $kode = trim((string) $spVisit['code_doctor']);
   $nama = spNama($spVisit['id_doctor']);
   $pilih = null;
   foreach ($kand as $u) if ($kode !== '' && trim((string) $u['kdDokter']) === $kode) { $pilih = $u; break; }
   if (!$pilih) foreach ($kand as $u) if ($nama !== '' && spNama($u['fullname']) === $nama) { $pilih = $u; break; }
   if (!$pilih) foreach ($kand as $u) { $n = spNama($u['fullname']); if ($nama !== '' && $n !== '' && (str_contains($n, $nama) || str_contains($nama, $n))) { $pilih = $u; break; } }
   if ($pilih) $signatureDokter = spFileAda('ttd_faskes', $pilih['signature_user']);
}

// yang membuat pernyataan (keluarga / pasien) dari form persetujuan
$signaturePenyetuju = $spVisit ? spFileAda('ttd', $spVisit['signature_opname']) : null;
?>
<div class="form-surat-persetujuan">

   <style>
      .form-surat-persetujuan {
         width: 210mm;
         min-height: 297mm;
         margin: 0 auto;
         font-family: "Times New Roman", serif;
         padding: 0 10mm;
      }

      .form-surat-persetujuan header {
         text-align: center;
      }

      .ttd-kanan {
         text-align: left;
         /* 🔥 ini kunci */
      }

      .form-surat-persetujuan .judul {
         margin-top: 20px;
         text-align: center;
         font-weight: bold;
         font-size: 18px;
         text-decoration: underline;
      }

      .form-surat-persetujuan .section {
         margin-top: 20px;
         font-size: 16px;
         line-height: 1.45;
      }

      .form-surat-persetujuan .data {
         margin-left: 20px;
         margin-bottom: 15px;
         width: 100%;
      }

      .form-surat-persetujuan .data td {
         padding: 3px 5px;
         vertical-align: top;
         font-size: 16px;
      }

      .form-surat-persetujuan ol li {
         margin-bottom: 6px;
      }

      .form-surat-persetujuan .ttd-wrapper {
         display: flex;
         justify-content: space-between;
         margin-top: 5px;
      }

      .form-surat-persetujuan .kolom-ttd {
         width: 30%;
         text-align: center;
         font-size: 16px;
      }

      .form-surat-persetujuan .ttd-box {
         margin-top: 55px;
         border-top: 1px solid #000;
         padding-top: 5px;
         font-size: 16px;
      }

      .form-surat-persetujuan .tanggal {
         text-align: right;
         margin-top: 30px;
         font-size: 16px;
      }
   </style>

   <?php include 'kopsurat.php'; ?>

   <h3 class="judul">SURAT PERSETUJUAN TINDAKAN MEDIS</h3>

   <div class="section">

      <p>Saya yang bertandatangan di bawah ini:</p>

      <!-- DATA PENYETUJU (family / wali / diri sendiri) -->
      <table class="data">
         <tr>
            <td>Nama</td>
            <td>: <span id="nama_keluarga"></span></td>
         </tr>
         <tr>
            <td>Umur</td>
            <td>: <span id="umur_keluarga"></span> Tahun</td>
         </tr>
         <tr>
            <td>Jenis Kelamin</td>
            <td>: <span id="gender_keluarga"></span></td>
         </tr>
      </table>

      <p>
         Dengan ini menyatakan sesungguhnya telah memberikan persetujuan/penolakan untuk dilakukan tindakan medis berupa <strong>Opname dan Pengobatan</strong>, terhadap <span id="hubungan"></span>, dengan
         <b><span id="sp_tindakan"></span></b>
      </p>

      <!-- DATA PASIEN -->
      <table class="data">
         <tr>
            <td>Nama</td>
            <td>: <span id="sp_nama"></span></td>
         </tr>
         <tr>
            <td>Umur</td>
            <td>: <span id="sp_usia"></span></td>
         </tr>
         <tr>
            <td>Jenis Kelamin</td>
            <td>: <span id="sp_jk"></span></td>
         </tr>
         <tr>
            <td>No. Kartu</td>
            <td>: <span id="sp_bpjs"></span></td>
         </tr>
      </table>

      <p>Saya Juga telah menyatakan dengan sesungguhnya bahwa saya :</p>

      <ol>
         <li>Telah diberikan informasi dan penjelasan terhadap tindakan medis yang akan dilakukan tersebut.</li>
         <li>Telah memahami sepenuhnya seluruh informasi dan penjelasan yang diberikan oleh dokter.</li>
      </ol>

      <p>
         Demikian pernyataan persetujuan tindakan medis ini saya buat dengan penuh kesadaran dan tanpa paksaan
      </p>

   </div>

   <p class="tanggal"><span id="sp_tanggal"></span></p>
   <div class="ttd-wrapper">
      <div class="kolom-ttd">
         <p>Saksi</p>
         <?php if ($signatureSaksi): ?>
            <img src="<?= spE($signatureSaksi) ?>" style="height:100px;" alt="Tanda tangan saksi">
         <?php else: ?>
            <div style="height:100px"></div>
         <?php endif; ?>
         <div class="ttd-box"><?= spE($_SESSION['fullname'] ?? '') ?></div>

      </div>



      <div class="kolom-ttd">
         <p>Dokter yang Merawat</p>
         <?php if ($signatureDokter): ?>
            <img src="<?= spE($signatureDokter) ?>" style="height:100px;" alt="Tanda tangan dokter">
         <?php else: ?>
            <div style="height:100px"></div>
         <?php endif; ?>
         <div class="ttd-box"><span id="sp_dokter"><?= spE($spVisit['id_doctor'] ?? '') ?></span></div>
      </div>


      <div class="kolom-ttd ttd-kanan">
         <p>Yang Membuat Pernyataan</p>
         <div id="ttdPersetujuan">
            <?php if ($signaturePenyetuju): ?>
               <img src="<?= spE($signaturePenyetuju) ?>" style="height:100px;" alt="Tanda tangan yang membuat pernyataan">
            <?php else: ?>
               <div style="height:100px" class="no-print-hint"></div>
            <?php endif; ?>
         </div>
         <div class="ttd-box">
            <span id="sp_nama_penyetuju_ttd"><?= spE($spVisit['opname_keluarga_name'] ?? '') ?></span>
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

      // GET DATA PASIEN
      fetch(`getpasien.php?no=${encodeURIComponent(no)}&rm=${encodeURIComponent(rm)}`)
         .then(res => res.json())
         .then(data => {
            if (!data) return;

            // Hitung umur
            let age = "";
            if (data.patient_datebirth) {
               const b = new Date(data.patient_datebirth);
               const t = new Date();
               age = (t.getFullYear() - b.getFullYear()) + " tahun";
            }
            document.getElementById("nama_keluarga").innerText = data.opname_keluarga_name;
            document.getElementById("umur_keluarga").innerText = data.opname_umur;
            document.getElementById("gender_keluarga").innerText = data.opname_gender;
            document.getElementById("hubungan").innerText = data.opname_hubungan_pasien;
            // Isi bagian PASIEN
            document.getElementById("sp_nama").innerText = data.patient_name;
            document.getElementById("sp_usia").innerText = age;
            document.getElementById("sp_jk").innerText = data.patient_gender;
            document.getElementById("sp_bpjs").innerText = data.patient_bpjs ?? "-";

            document.getElementById("sp_nama_penyetuju_ttd").innerText = data.opname_keluarga_name;


            // Dokter
            document.getElementById("sp_dokter").innerText = data.id_doctor;

            // Tindakan default
            document.getElementById("sp_tindakan").innerText = "Pemeriksaan / Pengobatan Medis";
            // 🔥 SET TANGGAL TTD
            if (data.visit_date) {
               let tanggal = formatTanggal(data.visit_date);

               document.getElementById("sp_tanggal").innerText =
                  "<?= $datafaskes['faskes_district'] ?>, " + tanggal;
            }
         });

   });

   // 🔥 FORMAT TANGGAL INDONESIA
   function formatTanggal(tgl) {
      const bulan = [
         "Januari", "Februari", "Maret", "April", "Mei", "Juni",
         "Juli", "Agustus", "September", "Oktober", "November", "Desember"
      ];

      let d = new Date(tgl);
      let hari = d.getDate();
      let bln = bulan[d.getMonth()];
      let thn = d.getFullYear();

      return `${hari} ${bln} ${thn}`;
   }
</script>