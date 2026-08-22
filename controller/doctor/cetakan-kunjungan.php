<?php
include '../../database/connect.php';
$id_customer = $_SESSION['id_customer'];
$noKunjung = $_GET['id'];
$stmt = $koneksi->prepare("SELECT pk.jdwpraktek, sc.clinic_name, pc.KodePPK, pv.*, pk.tglEstRujuk, pk.kdDiag1, pk.nmDiag1,pk.nmSubSpesialis1, pk.nmKategori, pk.nmfaskes, p.patient_name, p.patient_gender, p.patient_datebirth, p.patient_bpjs FROM pasien_visit pv INNER JOIN ms_patient p ON pv.id_patient = p.id_patient INNER JOIN pcare_kunjungan AS pk ON pv.noKunjung = pk.noKunjungan INNER JOIN setting_clinic AS sc ON sc.id_customer = pv.id_customer INNER JOIN setting_pcare AS pc ON pc.id_customer = pv.id_customer WHERE pv.noKunjung = ? AND pv.id_customer = ?");
$stmt->bind_param('ss', $noKunjung, $id_customer);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
if (!$data) {
    die("Data tidak ditemukan");
}

// ================= DATA PASIEN =================
$nama_pasien   = $data['patient_name'];
$no_bpjs       = $data['patient_bpjs'];
$jenis_kelamin = ($data['patient_gender'] ?? 'P') == 'L' ? 'L' : 'P';
$tgl_lahir     = $data['patient_datebirth'];
$umur          = $tgl_lahir ? date_diff(date_create($tgl_lahir), date_create('today'))->y : '';

// ================= DATA KUNJUNGAN =================
$diagnosa         = ($data['kdDiag1']) . " - " . ($data['nmDiag1']);
$catatan          = $data['catatan'];
$telah_diberikan  = $data['tindakan'];
$no_kunjungan     = $noKunjung;

// ================= DATA TAMBAHAN =================
$fktp         = ($data['clinic_name'] ?? 'MEDISAFE CS') . '(' . ($data['KodePPK']) . ')';
$kabupaten    = "KAB. DELI SERDANG(0032)";
$nama_dokter  = $data['id_doctor'];

// ================= HEADER =================
$kedeputian = "KEDEPUTIAN WILAYAH I";
$cabang     = "LUBUK PAKAM";

// ================= STATUS =================
$status_peserta = "1";
$tgl_cetak = date('d F Y');

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kunjungan - <?= $nama_pasien ?></title>
    <style>
        /* Instruksi khusus PDF Generator agar otomatis Landscape */
        @page {
            size: A4 landscape;
            margin: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #525659;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
        }

        .page {
            background-color: white;
            /* Ukuran ditukar untuk A4 Landscape */
            width: 297mm;
            min-height: 210mm;
            padding: 10mm 15mm;
            box-sizing: border-box;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            font-size: 13px;
            line-height: 1.4;
            color: #000;
        }

        table {
            border-collapse: collapse;
        }

        .table-no-border td {
            padding: 4px 0;
            vertical-align: middle;
        }

        .main-box {
            border: 1px solid black;
            padding: 0;
            margin-top: 15px;
            height: auto;
            /* Tinggi minimal dikurangi agar tidak tembus ke halaman 2 */
            min-height: 400px;
            position: relative;
        }

        .barcode-dummy {
            height: 40px;
            width: 280px;
            background-image: repeating-linear-gradient(to right,
                    #000 0, #000 2px, transparent 2px, transparent 4px,
                    #000 4px, #000 5px, transparent 5px, transparent 8px,
                    #000 8px, #000 11px, transparent 11px, transparent 13px,
                    #000 13px, #000 16px, transparent 16px, transparent 18px);
        }

        .box-char {
            border: 1px solid black;
            padding: 3px 10px;
            display: inline-block;
            text-align: center;
            font-weight: bold;
        }

        .divider-box {
            border-bottom: 1px solid black;
        }

        .nowrap {
            white-space: nowrap;
        }

        /* Style untuk Footer Dokumen */
        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 11px;
            color: #555;
            line-height: 1.3;
        }
    </style>
</head>

<body>
    <div class="page">
        <!-- HEADER -->
        <table width="100%" style="margin-bottom: 20px;">
            <tr>
                <td width="40%" valign="middle">
                    <img src="../../assets/images/logos/bpjslogo.svg" alt="Logo BPJS" style="height: 40px;">
                </td>
                <td width="60%" align="right" valign="middle">
                    <table class="table-no-border" style="width: 100%; text-align: left; font-weight: bold; font-size: 13px;">
                        <tr>
                            <td width="70%" align="right">Divisi Regional</td>
                            <td width="5%" align="center"></td>
                            <td class="nowrap"><?= $kedeputian ?></td>
                        </tr>
                        <tr>
                            <td align="right">Kantor Cabang</td>
                            <td align="center"></td>
                            <td class="nowrap"><?= $cabang ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <h3 style="text-align: center; margin: 5px 0 10px 0; font-size: 16px;">Data Kunjungan Puskesmas / Dokter Keluarga</h3>

        <!-- KOTAK UTAMA -->
        <div class="main-box">

            <!-- SECTION ATAS (Kunjungan & Barcode) -->
            <div class="divider-box" style="padding: 15px 20px;">
                <table width="100%" class="table-no-border">
                    <tr>
                        <td width="65%">
                            <table width="100%" class="table-no-border">
                                <tr>
                                    <td width="200">No. Kunjungan</td>
                                    <td width="15">:</td>
                                    <td class="nowrap"><?= $no_kunjungan ?></td>
                                </tr>
                                <tr>
                                    <td>Puskesmas / Dokter Keluarga</td>
                                    <td>:</td>
                                    <td class="nowrap"><?= $fktp ?></td>
                                </tr>
                                <tr>
                                    <td>Kabupaten / Kota</td>
                                    <td>:</td>
                                    <td class="nowrap"><?= $kabupaten ?></td>
                                </tr>
                            </table>
                        </td>
                        <td width="35%" align="right" valign="middle">
                            <div class="barcode-dummy"></div>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- SECTION TENGAH (Data Pasien) -->
            <div style="padding: 25px 20px;">
                <table width="100%" class="table-no-border">
                    <tr>
                        <td width="120">Nama</td>
                        <td width="15">:</td>
                        <!-- Kolom ini lebih besar sekarang karena kertas mendatar -->
                        <td width="350" class="nowrap"><?= $nama_pasien ?></td>
                        <td width="50">Umur</td>
                        <td width="15">:</td>
                        <td width="40"><?= $umur ?></td>
                        <td width="50">Tahun</td>
                        <td class="nowrap"><?= $tgl_lahir ? date('d-M-Y', strtotime($tgl_lahir)) : '' ?></td>
                    </tr>
                    <tr>
                        <td>No. Kartu BPJS</td>
                        <td>:</td>
                        <td class="nowrap"><?= $no_bpjs ?></td>
                        <td>Status</td>
                        <td>:</td>
                        <td colspan="3" class="nowrap">
                            <span class="box-char"><?= $status_peserta ?></span> Utama/Tanggungan &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <span class="box-char"><?= $jenis_kelamin ?></span> ( L / P )
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top: 15px;">Diagnosa</td>
                        <td style="padding-top: 15px;">:</td>
                        <td colspan="6" style="padding-top: 15px;"><?= $diagnosa ?></td>
                    </tr>
                    <tr>
                        <td style="padding-top: 15px;">Telah diberikan</td>
                        <td style="padding-top: 15px;">:</td>
                        <td colspan="6" style="padding-top: 15px;"></td>
                    </tr>
                </table>

                <!-- SECTION BAWAH (Tanda Tangan) -->
                <!-- Margin atas dikurangi agar pas untuk kertas mendatar -->
                <table width="100%" class="table-no-border" style="margin-top: 50px;">
                    <tr>
                        <td width="65%" valign="top">
                            Demikian surat ini dibuat untuk dapat dipergunakan sebagaimana mestinya
                        </td>
                        <td width="35%" align="center" valign="top">
                            Salam sejawat, <?= $tgl_cetak ?>
                            <br><br><br><br><br><br>
                            <?php

                            $namaDokter = trim($nama_dokter ?? '');

                            /*
|--------------------------------------------------------------------------
| Hapus prefix dokter jika sudah ada
| dr. / Dr. / DR. / dR.
|--------------------------------------------------------------------------
*/

                            $namaDokter = preg_replace(
                                '/^\s*dr\.?\s+/i',
                                '',
                                $namaDokter
                            );

                            /*
|--------------------------------------------------------------------------
| Tampilkan dengan format standar
|--------------------------------------------------------------------------
*/

                            $namaDokterTampil = 'dr. ' . $namaDokter;

                            ?>

                            <?= htmlspecialchars($namaDokterTampil) ?>
                        </td>
                    </tr>
                </table>
            </div>

        </div> <!-- End of .main-box -->

        <!-- FOOTER DOKUMEN -->
        <div class="footer">
            Surat ini diterbitkan melalui Sistem Informasi Klinik <strong>Medisafe</strong> dan merupakan
            dokumen resmi fasilitas pelayanan kesehatan. <br> <i>www.medisafe.id</i>
        </div>

    </div> <!-- End of .page -->
</body>

</html>