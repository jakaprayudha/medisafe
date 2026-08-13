<?php
include '../../database/connect.php';
$id_customer = $_SESSION['id_customer'];
$noKunjung = $_GET['id'];
$stmt = $koneksi->prepare("SELECT  pk.jdwpraktek, sc.clinic_name, pc.KodePPK, pv.*, pk.tglEstRujuk, pk.kdDiag1, pk.nmDiag1,pk.nmSubSpesialis1, pk.nmKategori, pk.nmfaskes, p.patient_name, p.patient_gender, p.patient_datebirth, p.patient_bpjs FROM pasien_visit pv INNER JOIN ms_patient p ON pv.id_patient = p.id_patient INNER JOIN pcare_kunjungan AS pk ON pv.noKunjung = pk.noKunjungan INNER JOIN setting_clinic AS sc ON sc.id_customer = pv.id_customer INNER JOIN setting_pcare AS pc ON pc.id_customer = pv.id_customer WHERE pv.noKunjung = ? AND pv.id_customer = ?");
$stmt->bind_param('ss', $noKunjung, $id_customer);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
if (!$data) {
    die("Data tidak ditemukan");
}

// ================= DATA PASIEN =================
$nama_pasien   = $data['patient_name'] ?? '';
$no_bpjs       = $data['patient_bpjs'] ?? '';
$jenis_kelamin = ($data['patient_gender'] ?? '') == 'L' ? 'L' : 'P';
$tgl_lahir = !empty($data['patient_datebirth']) ? date('d-m-Y', strtotime($data['patient_datebirth'])) : '';
$visit_date = $data['visit_date'];
$umur          = $tgl_lahir ? date_diff(date_create($tgl_lahir), date_create($visit_date))->y : '';

// ================= DATA KUNJUNGAN =================
$diagnosa         = $data['kdDiag1'] . "-" . $data['nmDiag1'];
$catatan          = $data['catatan'] ?? '';
$telah_diberikan  = $data['tindakan'] ?? '';
$tgl_kunjung_db = $data['tglEstRujuk'] ?? '';
$tgl_kunjung = !empty($tgl_kunjung_db) ? date('d-m-Y', strtotime($tgl_kunjung_db)) : '';
$no_rujukan       = $data['no_rujukan'] ?? '';
$no_kunjungan     = $data['noKunjungan'] ?? '';

// ================= DATA TAMBAHAN =================
$fktp         = $data['clinic_name'] . '(' . $data['kodePPK'] . ')';
$kabupaten    = "KAB. DELI SERDANG(0032)";
$tujuan_poli  = empty($data['nmSubSpesialis1']) ? $data['nmKategori'] : $data['nmSubSpesialis1'];
$tujuan_rs    = $data['nmfaskes'];
$nama_dokter  = $data['id_doctor'] ?? '';
$jadwal_praktek = $data['jdwpraktek'] ?? '';

// ================= HEADER =================
$kedeputian = "KEDEPUTIAN WILAYAH I";
$cabang     = "LUBUK PAKAM";

// ================= STATUS =================
$status_peserta = "1";
$tgl_cetak = date('d-m-Y');

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Rujukan FKTP - <?= $nama_pasien ?></title>
    <style>
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
            width: 210mm;
            min-height: 297mm;
            padding: 10mm 15mm;
            box-sizing: border-box;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            font-size: 12px;
            line-height: 1.3;
            color: #000;
        }

        table {
            border-collapse: collapse;
        }

        .table-no-border td {
            padding: 3px 0;
            vertical-align: top;
        }

        .kotak-header {
            border: 1px solid black;
            padding: 10px;
            margin-bottom: 15px;
        }

        .barcode-dummy {
            height: 35px;
            width: 280px;
            background-image: repeating-linear-gradient(to right,
                    #000 0, #000 2px, transparent 2px, transparent 4px,
                    #000 4px, #000 5px, transparent 5px, transparent 8px,
                    #000 8px, #000 11px, transparent 11px, transparent 13px,
                    #000 13px, #000 16px, transparent 16px, transparent 18px);
            margin-top: 5px;
        }

        .box-char {
            border: 1px solid black;
            padding: 2px 10px;
            display: inline-block;
            text-align: center;
            font-weight: bold;
        }

        .checkbox {
            width: 18px;
            height: 14px;
            border: 1px solid black;
            display: inline-block;
            vertical-align: middle;
        }

        hr.divider {
            border: 0;
            border-top: 1px solid black;
            margin: 20px 0 15px 0;
        }

        .footer {
            margin-top: 10px;
            padding-bottom: 10px;
            text-align: center;
            font-size: 11px;
            color: #555;
            line-height: 1.4;
        }
    </style>
</head>

<body>
    <div class="page">
        <!-- HEADER -->
        <table width="100%" style="margin-bottom: 15px;">
            <tr>
                <td width="50%" valign="middle">
                    <img src="../../assets/images/logos/bpjslogo.svg" alt="Logo BPJS" style="height: 35px;">
                </td>
                <td width="50%" align="right" valign="middle">
                    <table class="table-no-border" style="width: 100%; text-align: left; font-weight: bold; font-size: 12px;">
                        <tr>
                            <td width="140">Kedeputian Wilayah</td>
                            <td width="10">:</td>
                            <td><?= $kedeputian ?></td>
                        </tr>
                        <tr>
                            <td>Kantor Cabang</td>
                            <td>:</td>
                            <td><?= $cabang ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <h3 style="text-align: center; margin: 10px 0 15px 0; font-size: 15px;">Surat Rujukan FKTP</h3>

        <div class="kotak-header">
            <table width="100%" class="table-no-border">
                <tr>
                    <td width="65%">
                        <table width="100%" class="table-no-border">
                            <tr>
                                <td width="130">No. Rujukan</td>
                                <td width="10">:</td>
                                <td><?= $noKunjung ?></td>
                            </tr>
                            <tr>
                                <td>FKTP</td>
                                <td>:</td>
                                <td><?= $fktp ?></td>
                            </tr>
                            <tr>
                                <td>Kabupaten / Kota</td>
                                <td>:</td>
                                <td><?= $kabupaten ?></td>
                            </tr>
                        </table>
                    </td>
                    <td width="35%" align="right" valign="top">
                        <div class="barcode-dummy"></div>
                    </td>
                </tr>
            </table>
        </div>

        <table class="table-no-border" width="100%" style="margin-bottom: 12px;">
            <tr>
                <td width="150">Kepada Yth. TS Dokter</td>
                <td width="10">:</td>
                <td><?= $tujuan_poli ?></td>
            </tr>
            <tr>
                <td>Di</td>
                <td>:</td>
                <td><?= $tujuan_rs ?></td>
            </tr>
        </table>

        <p style="margin-top: 10px; margin-bottom: 12px;">Mohon pemeriksaan dan penanganan lebih lanjut pasien :</p>

        <table class="table-no-border" width="100%" style="margin-bottom: 20px;">
            <tr>
                <td width="120">Nama</td>
                <td width="10">:</td>
                <td width="320"><?= $nama_pasien ?></td>
                <td width="55">Umur :</td>
                <td width="35"><?= $umur ?></td>
                <td width="65">Tahun :</td>
                <td><?= $tgl_lahir ?></td>
            </tr>
            <tr>
                <td>No. Kartu BPJS</td>
                <td>:</td>
                <td><?= $no_bpjs ?></td>
                <td>Status :</td>
                <td colspan="3">
                    <span class="box-char"><?= $status_peserta ?></span> Utama/Tanggungan &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <span class="box-char"><?= $jenis_kelamin ?></span>
                </td>
            </tr>
            <tr>
                <td>Diagnosa</td>
                <td>:</td>
                <td><?= $diagnosa ?></td>
                <td colspan="4" rowspan="2" style="padding-top: 5px;">Catatan :<br><?= $catatan ?></td>
            </tr>
            <tr>
                <td>Telah diberikan</td>
                <td>:</td>
                <td><?= $telah_diberikan ?></td>
            </tr>
        </table>

        <!-- FOOTER TTD -->
        <table width="100%" class="table-no-border" style="margin-top: 10px;">
            <tr>
                <td width="68%">Atas bantuannya, diucapkan terima kasih</td>
                <td width="32%" align="center">Salam sejawat,<br><?= $tgl_cetak ?></td>
            </tr>
        </table>

        <table width="100%" class="table-no-border" style="margin-top: 10px;">
            <tr>
                <td width="68%" valign="top">
                    <table class="table-no-border">
                        <tr>
                            <td width="160">Tgl. Rencana Berkunjung</td>
                            <td width="10">:</td>
                            <td><?= $tgl_kunjung ?></td>
                        </tr>
                        <tr>
                            <td>Jadwal Praktek</td>
                            <td>:</td>
                            <td><?= $jadwal_praktek ?></td>
                        </tr>
                    </table>
                    <div style="margin-top: 10px;">
                        Surat rujukan berlaku 1[satu] kali kunjungan, berlaku sampai dengan :
                        <span style="white-space: nowrap; font-weight: bold;"><?= date('d-m-Y', strtotime($tgl_kunjung_db . ' +3 months')) ?></span>
                    </div>
                </td>
                <td width="32%" align="center" valign="bottom" style="padding-top: 40px;">
                    <?= $nama_dokter ?>
                </td>
            </tr>
        </table>

        <hr class="divider">

        <h4 style="text-align: center; text-decoration: underline; margin: 10px 0 15px 0; font-size: 13px;">SURAT RUJUKAN BALIK</h4>

        <p style="margin-top: 0; margin-bottom: 15px;">Teman sejawat Yth.<br>Mohon kontrol selanjutnya penderita :</p>

        <!-- RUJUKAN BALIK FORM -->
        <table class="table-no-border" width="100%" style="margin-bottom: 15px;">
            <tr>
                <td width="90">Nama</td>
                <td width="10">:</td>
                <td>.........................................................................................................................................................</td>
            </tr>
            <tr>
                <td>Diagnosa</td>
                <td>:</td>
                <td>.........................................................................................................................................................</td>
            </tr>
            <tr>
                <td>Terapi</td>
                <td>:</td>
                <td>.........................................................................................................................................................</td>
            </tr>
        </table>

        <p style="margin-bottom: 10px;">Tindak lanjut yang dianjurkan</p>

        <!-- TINDAK LANJUT -->
        <table width="100%" class="table-no-border">
            <tr>
                <td width="60%" valign="top">
                    <table class="table-no-border" width="100%">
                        <tr>
                            <td width="30" style="padding-bottom: 5px;">
                                <div class="checkbox"></div>
                            </td>
                            <td style="padding-bottom: 5px;">
                                Pengobatan dengan obat-obatan :<br>
                                <span style="display: inline-block; margin-top: 5px;">........................................................................................</span>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-bottom: 5px;">
                                <div class="checkbox"></div>
                            </td>
                            <td style="padding-bottom: 5px;">
                                Kontrol kembali ke RS tanggal : .................................
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-bottom: 5px;">
                                <div class="checkbox"></div>
                            </td>
                            <td style="padding-bottom: 5px;">
                                Lain-lain : ......................................................................
                            </td>
                        </tr>
                    </table>
                </td>
                <td width="40%" valign="top">
                    <table class="table-no-border" width="100%">
                        <tr>
                            <td width="30" style="padding-bottom: 5px;">
                                <div class="checkbox"></div>
                            </td>
                            <td style="padding-bottom: 5px;">Perlu rawat inap</td>
                        </tr>
                        <tr>
                            <td style="padding-bottom: 5px;">
                                <div class="checkbox"></div>
                            </td>
                            <td style="padding-bottom: 5px;">Konsultasi selesai</td>
                        </tr>
                    </table>

                    <div style="margin-top: 10px;">
                        ..................................... tgl .....................................
                    </div>

                    <div style="text-align: right; margin-top: 30px; padding-right: 20px;">
                        Dokter RS,<br><br><br><br>
                        (...................................................)
                    </div>
                </td>
            </tr>
        </table>
        
        <div style="margin-top: 20px;">
            <hr style="border: 0; border-top: 1px solid black; margin-bottom: 10px;">
            <div class="footer">
                Surat ini diterbitkan melalui Sistem Informasi Klinik <strong>Medisafe</strong> dan merupakan
                dokumen resmi fasilitas pelayanan kesehatan. <br> <i>www.medisafe.id</i>
            </div>
        </div>
    </div>
</body>

</html>