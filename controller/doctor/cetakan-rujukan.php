<?php
include '../../database/connect.php';
$id_customer = $_SESSION['id_customer'];
$noKunjung = $_GET['id'];
$stmt = $koneksi->prepare("SELECT sc.clinic_name, pc.KodePPK, pv.*, pk.tglEstRujuk, pk.kdDiag1, pk.nmDiag1, pk.nmKategori, pk.nmfaskes, p.patient_name, p.patient_gender, p.patient_datebirth, p.patient_bpjs FROM pasien_visit pv LEFT JOIN ms_patient p ON pv.nokartu = p.patient_bpjs INNER JOIN pcare_kunjungan AS pk ON pv.noKunjung = pk.noKunjungan INNER JOIN setting_clinic AS sc ON sc.id_customer = pv.id_customer INNER JOIN setting_pcare AS pc ON pc.id_customer = pv.id_customer WHERE pv.noKunjung = ? AND pv.id_customer = ?");
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
$tgl_lahir     = $data['patient_birthdate'] ?? '';
$umur          = $tgl_lahir ? date_diff(date_create($tgl_lahir), date_create('today'))->y : '';

// ================= DATA KUNJUNGAN =================
$diagnosa         = $data['kdDiag1'] . "-" . $data['nmDiag1'];
$catatan          = $data['catatan'] ?? '';
$telah_diberikan  = $data['tindakan'] ?? '';
$tgl_kunjung      = $data['tglEstRujuk'] ?? '';
$no_rujukan       = $data['no_rujukan'] ?? '';
$no_kunjungan     = $data['noKunjungan'] ?? '';

// ================= DATA TAMBAHAN =================
$fktp         = $data['clinic_name'].'('.$data['kodePPK'] .')';
$kabupaten    = "KAB. DELI SERDANG(0032)";
$tujuan_poli  = $data['nmKategori'];
$tujuan_rs    = $data['nmfaskes'];
$nama_dokter  = $data['id_doctor'] ?? '';
$jadwal_praktek = $data['jadwal'] ?? '';


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
            /* Warna latar belakang PDF viewer */
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
        }

        .page {
            background-color: white;
            width: 210mm;
            min-height: 297mm;
            /* Ukuran A4 */
            padding: 10mm 15mm;
            box-sizing: border-box;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            font-size: 12px;
            line-height: 1.4;
            color: #000;
        }

        .table-no-border td {
            padding: 2px 0;
            vertical-align: top;
        }

        .kotak-header {
            border: 1px solid black;
            padding: 10px;
            margin-bottom: 20px;
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
            padding: 2px 8px;
            display: inline-block;
            text-align: center;
            font-weight: bold;
        }

        .checkbox {
            width: 30px;
            height: 22px;
            border: 1px solid black;
            display: inline-block;
            vertical-align: middle;
            margin-right: 10px;
        }

        hr.divider {
            border: 0;
            border-top: 1px solid black;
            margin: 25px 0 15px 0;
        }

        .dotted-line {
            border-bottom: 1px dotted black;
            display: inline-block;
            color: transparent;
        }
    </style>
</head>

<body>
    <div class="page">
        <table width="100%" style="margin-bottom: 15px;">
            <tr>
                <td width="50%">
                    <div style="display: flex; align-items: center;">
                        <img src="../../assets/images/logos/bpjslogo.svg" alt="Logo BPJS" style="height: 35px; margin-right: 10px;">
                    </div>
                </td>
                <td width="50%" align="right">
                    <table class="table-no-border" style="width: 100%; text-align: left; font-weight: bold; font-size: 13px;">
                        <tr>
                            <td width="130">Kedeputian Wilayah</td>
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

        <h3 style="text-align: center; margin: 10px 0 20px 0; font-size: 16px;">Surat Rujukan FKTP</h3>

        <div class="kotak-header">
            <table width="100%" class="table-no-border">
                <tr>
                    <td width="65%">
                        <table width="100%" class="table-no-border">
                            <tr>
                                <td width="120">No. Rujukan</td>
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

        <table class="table-no-border" width="100%" style="margin-bottom: 15px;">
            <tr>
                <td width="140">Kepada Yth. TS Dokter</td>
                <td width="10">:</td>
                <td><?= $tujuan_poli ?></td>
            </tr>
            <tr>
                <td>Di</td>
                <td>:</td>
                <td><?= $tujuan_rs ?></td>
            </tr>
        </table>

        <p style="margin-bottom: 15px;">Mohon pemeriksaan dan penanganan lebih lanjut pasien :</p>

        <table class="table-no-border" width="100%" style="margin-bottom: 25px;">
            <tr>
                <td width="110">Nama</td>
                <td width="10">:</td>
                <td width="240"><?= $nama_pasien ?></td>
                <td width="50">Umur :</td>
                <td width="30"><?= $umur ?></td>
                <td width="60">Tahun :</td>
                <td><?= $tgl_lahir ?></td>
            </tr>
            <tr>
                <td>No. Kartu BPJS</td>
                <td>:</td>
                <td><?= $no_bpjs ?></td>
                <td>Status :</td>
                <td colspan="3">
                    <span class="box-char"><?= $status_peserta ?></span> Utama/Tanggungan &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <span class="box-char"><?= $jenis_kelamin ?></span> (L / P)
                </td>
            </tr>
            <tr>
                <td>Diagnosa</td>
                <td>:</td>
                <td><?= $diagnosa ?></td>
                <td colspan="4" rowspan="2" style="padding-top: 10px;">Catatan :<br><?= $catatan ?></td>
            </tr>
            <tr>
                <td>Telah diberikan</td>
                <td>:</td>
                <td></td>
            </tr>
        </table>

        <table width="100%" class="table-no-border">
            <tr>
                <td width="60%">Atas bantuannya, diucapkan terima kasih</td>
                <td width="40%" align="center">Salam sejawat,<br><?= $tgl_cetak ?></td>
            </tr>
            <tr>
                <td>
                    <table class="table-no-border" style="margin-top: 10px;">
                        <tr>
                            <td width="150">Tgl. Rencana Berkunjung</td>
                            <td width="10">:</td>
                            <td><?= $tgl_kunjung ?></td>
                        </tr>
                        <tr>
                            <td>Jadwal Praktek</td>
                            <td>:</td>
                            <td><?= $jadwal_praktek ?></td>
                        </tr>
                    </table>
                </td>
                <td rowspan="2" align="center" valign="bottom" style="padding-top: 50px;">
                    <?= $nama_dokter ?>
                </td>
            </tr>
            <tr>
                <td style="padding-top: 10px;">Surat rujukan berlaku 1[satu] kali kunjungan, berlaku sampai dengan : &nbsp;&nbsp; <?= date('Y-m-d', strtotime($tgl_kunjung . ' +89 days')) ?></td>
            </tr>
        </table>

        <hr class="divider">

        <h4 style="text-align: center; text-decoration: underline; margin-bottom: 15px; font-size: 13px;">SURAT RUJUKAN BALIK</h4>

        <p style="margin-top: 0; margin-bottom: 15px;">Teman sejawat Yth.<br>Mohon kontrol selanjutnya penderita :</p>

        <table class="table-no-border" width="100%" style="margin-bottom: 20px;">
            <tr>
                <td width="120">Nama</td>
                <td width="10">:</td>
                <td>.....................................................................................................................................................................</td>
            </tr>
            <tr>
                <td>Diagnosa</td>
                <td>:</td>
                <td>.....................................................................................................................................................................</td>
            </tr>
            <tr>
                <td>Terapi</td>
                <td>:</td>
                <td>.....................................................................................................................................................................</td>
            </tr>
        </table>

        <p style="margin-bottom: 10px;">Tindak lanjut yang dianjurkan</p>

        <table width="100%" class="table-no-border">
            <tr>
                <td width="60%" valign="top">
                    <div style="margin-bottom: 12px; display: flex; align-items: center;">
                        <div class="checkbox"></div>
                        <div>
                            Pengobatan dengan obat- obatan :<br>
                            <span class="dotted-line" style="width: 250px;">.</span>
                        </div>
                    </div>
                    <div style="margin-bottom: 12px; display: flex; align-items: center;">
                        <div class="checkbox"></div>
                        <div>Kontrol kembali ke RS tanggal : .................................</div>
                    </div>
                    <div style="margin-bottom: 12px; display: flex; align-items: center;">
                        <div class="checkbox"></div>
                        <div>Lain-lain : ........................................................................</div>
                    </div>
                </td>
                <td width="40%" valign="top">
                    <div style="margin-bottom: 12px; display: flex; align-items: center;">
                        <div class="checkbox"></div>
                        <div>Perlu rawat inap</div>
                    </div>
                    <div style="margin-bottom: 25px; display: flex; align-items: center;">
                        <div class="checkbox"></div>
                        <div>Konsultasi selesai</div>
                    </div>
                    <div>
                        ..........................................tgl..........................................
                    </div>
                    <div style="text-align: right; margin-top: 20px; padding-right: 30px;">
                        Dokter RS,<br><br><br><br>
                        (...................................................)
                    </div>
                </td>
            </tr>
        </table>

    </div>

</body>

</html>