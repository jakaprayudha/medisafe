<?php
$visit = $_GET['no'] ?? '';
$id_customer = $_SESSION['id_customer'] ?? null;
$checkpasien = mysqli_query(
  $koneksi,
  "SELECT * FROM pasien_visit
   LEFT JOIN ms_patient ON ms_patient.id_patient = pasien_visit.id_patient
   WHERE pasien_visit.visit_ID='$visit' AND  pasien_visit.id_customer='$id_customer' LIMIT 1"
);
require '../../admin/getdataclinic.php';
$datapasien = mysqli_fetch_array($checkpasien);
$checktriase = mysqli_query($koneksi, "SELECT * FROM pasien_triase WHERE visit_ID='$visit' LIMIT 1");
$datatriase = mysqli_fetch_array($checktriase);

if (!function_exists('hitungUsia')) {

  function hitungUsia($tgl_lahir, $tgl_acuan = null)
  {
    if (!$tgl_lahir) return "-";
    try {
      $lahir = new DateTime($tgl_lahir);
      $acuan = $tgl_acuan ? new DateTime($tgl_acuan) : new DateTime();
      $d = $acuan->diff($lahir);
      if ($d->y > 0) return $d->y . " Th " . $d->m . " Bln";
      if ($d->m > 0) return $d->m . " Bln " . $d->d . " Hr";
      return $d->d . " Hr";
    } catch (Exception $e) {
      return "-";
    }
  }
}

$usia = hitungUsia(
  $datapasien['patient_datebirth'] ?? null,
  $datapasien['visit_date'] ?? null
);

$title    = $title    ?? "ASESMEN AWAL MEDIS";
$subtitle = $subtitle ?? "PASIEN GAWAT DARURAT (IGD)";
?>

<!-- =========================
     KOP SURAT (ISOLATED)
========================= -->
<div class="kop-scope">
  <div class="kop-inner">

    <style>
      /* ⛔ ISOLASI TOTAL */
      .kop-scope {
        width: 100%;
        box-sizing: border-box;
      }

      .kop-inner {
        max-width: 760px;
        /* 🔑 SAMA DENGAN FORM */
        margin: 0 auto;
        font-family: "Times New Roman", serif;
        color: #000;
      }

      .kop-inner * {
        box-sizing: border-box;
      }

      .kop-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 8px;
        border: 1px solid #000;
      }

      .kop-table td {
        padding: 6px;
        vertical-align: middle;
        border: 1px solid #000;
      }

      .kop-logo {
        text-align: center;
        width: 15%;
      }

      .kop-logo img {
        width: 75px;
      }

      .kop-title {
        text-align: center;
        width: 40%;
      }

      .kop-title h3 {
        margin: 0;
        font-size: 18px;
        font-weight: bold;
        text-transform: uppercase;
      }

      .kop-title p {
        margin: 3px 0 0;
        font-size: 12px;
      }

      .kop-pasien {
        font-size: 12px;
        width: 45%;
      }

      .kop-pasien table {
        width: 100%;
        border-collapse: collapse;
      }

      .kop-pasien td {
        padding: 2px 0;
      }

      .kop-divider {
        border-bottom: 2px solid #000;
        margin-top: 6px;
      }
    </style>

    <table class="kop-table">
      <tr>
        <td class="kop-logo">
          <img src="../../../uploads/<?= $datafaskes['image_clinic'] ?>">
        </td>

        <td class="kop-title">
          <h3><?= htmlspecialchars($title) ?></h3>
          <p><?= htmlspecialchars($subtitle) ?></p>
        </td>

        <td class="kop-pasien">
          <table>
            <tr>
              <td>Nama</td>
              <td>: <?= htmlspecialchars($datapasien['patient_name'] ?? '-') ?></td>
            </tr>
            <tr>
              <td>No. RM</td>
              <td>: <?= htmlspecialchars($datapasien['nomor_rm'] ?? '-') ?></td>
            </tr>
            <tr>
              <td>Tgl Lahir</td>
              <td>: <?= htmlspecialchars($datapasien['patient_datebirth'] ?? '-') ?></td>
            </tr>
            <tr>
              <td>Usia</td>
              <td>: <?= $usia ?></td>
            </tr>
            <tr>
              <td>JK</td>
              <td>: <?= htmlspecialchars($datapasien['patient_gender'] ?? '-') ?></td>
            </tr>
            <tr>
              <td>Masuk</td>
              <td>: <?= htmlspecialchars($datapasien['visit_date'] ?? '-') ?>
                <?= htmlspecialchars($datatriase['jam_masuk'] ?? '') ?></td>
            </tr>
            <tr>
              <td>Dokter</td>
              <td>: <?= htmlspecialchars($datapasien['id_doctor'] ?? '-') ?></td>
            </tr>
          </table>
        </td>
      </tr>
    </table>

    <div class="kop-divider"></div>
  </div>
</div>