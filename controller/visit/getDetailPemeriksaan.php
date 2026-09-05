<?php

include '../../database/connect.php';

header('Content-Type: application/json');

$id = $_GET['id'] ?? '';

if (empty($id)) {
    echo json_encode([
        'status' => false,
        'message' => 'ID visit tidak ditemukan.'
    ]);
    exit;
}


/*
|--------------------------------------------------------------------------
| AMBIL DATA VISIT
|--------------------------------------------------------------------------
|
| Parameter $id di sini adalah pv.id_visit.
|
| Contoh:
| pv.id_visit = 76202
| pv.visit_ID = VIS-1-260904-8D8D46
|
*/

$query = "
    SELECT 
        pv.id_visit,
        pv.visit_ID,
        pv.id_patient,
        pv.id_doctor,
        pv.visit_date,
        pv.visit_time,
        pv.id_poli,
        pv.patient_name_pcare,
        pv.anamnesa,
        pv.catatan_screening,

        pv.diagnosa,

        -- Keterangan ICD-10
        icd.code AS diagnosa_code,
        icd.icd10 AS diagnosa_keterangan,
        icd.icd10_ind AS diagnosa_keterangan_ind,

        pv.kdDiag1,
        pv.kdDiag2,
        pv.kdDiag3,
        pv.nmDiag1,
        pv.nmDiag2,
        pv.nmDiag3,

        pv.tindakan,
        pv.tekanan_darah,
        pv.suhu,
        pv.nadi,
        pv.respirasi,
        pv.tinggi_badan,
        pv.berat_badan,
        pv.bmi,
        pv.bmi_keterangan,
        pv.saturasi,

        mp.patient_datebirth

    FROM pasien_visit AS pv

    LEFT JOIN ms_patient AS mp
        ON mp.id_patient = pv.id_patient

    LEFT JOIN icd_10 AS icd
        ON icd.code = pv.diagnosa

    WHERE pv.id_visit = ?

    LIMIT 1
";

$stmt = $koneksi->prepare($query);

if (!$stmt) {
    echo json_encode([
        'status' => false,
        'message' => 'Prepare query visit gagal: ' . $koneksi->error
    ]);
    exit;
}

$stmt->bind_param("s", $id);

if (!$stmt->execute()) {
    echo json_encode([
        'status' => false,
        'message' => 'Execute query visit gagal: ' . $stmt->error
    ]);
    $stmt->close();
    exit;
}

$result = $stmt->get_result();

$data = $result->fetch_assoc();

$stmt->close();


/*
|--------------------------------------------------------------------------
| CEK DATA VISIT
|--------------------------------------------------------------------------
*/

if (!$data) {
    echo json_encode([
        'status' => false,
        'message' => 'Data pemeriksaan tidak ditemukan.'
    ]);
    exit;
}


/*
|--------------------------------------------------------------------------
| IDENTIFIER VISIT
|--------------------------------------------------------------------------
|
| PENTING:
|
| pasien_visit.id_visit
|     = 76202
|
| pasien_visit.visit_ID
|     = VIS-1-260904-8D8D46
|
| permintaan_pharmacy.id_visit
|     = VIS-1-260904-8D8D46
|
| Jadi untuk pharmacy kita menggunakan visit_ID.
|
*/

$visitID = $data['visit_ID'] ?? '';



/*
|--------------------------------------------------------------------------
| AMBIL DATA TINDAKAN / BILLING
|--------------------------------------------------------------------------
|
| pasien_billing menggunakan visit_ID.
|
*/

$tindakan = [];

if (!empty($visitID)) {

    $qTindakan = "
        SELECT
            id_billing,
            id_visit,
            billing_item,
            billing_category

        FROM pasien_billing

        WHERE id_visit = ?

        ORDER BY id_billing ASC
    ";

    $stmtTindakan = $koneksi->prepare($qTindakan);

    if ($stmtTindakan) {

        $stmtTindakan->bind_param(
            "s",
            $visitID
        );

        if ($stmtTindakan->execute()) {

            $resultTindakan = $stmtTindakan->get_result();

            while ($row = $resultTindakan->fetch_assoc()) {

                if (!empty($row['billing_item'])) {
                    $tindakan[] = $row;
                }
            }
        }

        $stmtTindakan->close();
    }
}



/*
|--------------------------------------------------------------------------
| AMBIL DATA OBAT
|--------------------------------------------------------------------------
|
| RELASI:
|
| pasien_visit.visit_ID
|        ↓
| permintaan_pharmacy.id_visit
|        ↓
| permintaan_pharmacy.id_permintaan_farmasi
|        ↓
| permintaan_pharmacy_details.id_permintaan_farmasi
|        ↓
| permintaan_pharmacy_details.id_pharmacy
|        ↓
| ms_pharmacy.id_pharmacy
|
*/

$obat = [];

if (!empty($visitID)) {

    $qObat = "
        SELECT

            pp.id_permintaan_farmasi,
            pp.id_visit,
            pp.created_at,
            pp.status_permintaan,
            pp.permintaan_number,
            pp.status_obat_pulang,
            pp.tipe_obat,

            pp.rck_jumlah,
            pp.rck_satuan,
            pp.rck_signa,

            ppd.id_pharmacy_details,
            ppd.id_pharmacy,
            ppd.item_name,
            ppd.signa,
            ppd.qty,
            ppd.catatan,
            ppd.harga,
            ppd.status_item,
            ppd.created_at AS detail_created_at,
            ppd.created_user,

            mp.*

        FROM permintaan_pharmacy AS pp

        LEFT JOIN permintaan_pharmacy_details AS ppd
            ON ppd.id_permintaan_farmasi =
               pp.id_permintaan_farmasi

        LEFT JOIN ms_pharmacy AS mp
            ON mp.id_pharmacy = ppd.id_pharmacy

        WHERE pp.id_visit = ?

        ORDER BY
            pp.id_permintaan_farmasi ASC,
            ppd.id_pharmacy_details ASC
    ";

    $stmtObat = $koneksi->prepare($qObat);

    if ($stmtObat) {

        $stmtObat->bind_param(
            "s",
            $visitID
        );

        if ($stmtObat->execute()) {

            $resultObat = $stmtObat->get_result();

            while ($row = $resultObat->fetch_assoc()) {

                /*
                 * Jangan lagi menggunakan:
                 *
                 * if (!empty($row['item_name']))
                 *
                 * karena item_name di database NULL.
                 *
                 * Kita ambil nama dari ms_pharmacy.
                 */

                $namaObat = '';

                /*
                 * Prioritas pertama:
                 * item_name dari detail
                 */

                if (!empty($row['item_name'])) {

                    $namaObat = trim($row['item_name']);
                }

                /*
                 * Kalau item_name NULL,
                 * cari nama obat dari ms_pharmacy.
                 *
                 * Sesuaikan kandidat ini jika struktur
                 * ms_pharmacy kamu menggunakan nama field lain.
                 */

                if (empty($namaObat)) {

                    $possibleNameFields = [
                        'nama_obat',
                        'nama_pharmacy',
                        'nama_barang',
                        'nama',
                        'name',
                        'item_name',
                        'pharmacy_name',
                        'drug_name'
                    ];

                    foreach ($possibleNameFields as $field) {

                        if (
                            isset($row[$field]) &&
                            !empty($row[$field])
                        ) {
                            $namaObat = trim($row[$field]);
                            break;
                        }
                    }
                }

                /*
                 * Simpan nama obat yang ditemukan
                 */

                $row['nama_obat'] = $namaObat;

                /*
                 * Masukkan selama detail pharmacy tersedia.
                 *
                 * Tidak menggunakan nama_obat sebagai syarat,
                 * supaya data tetap terlihat meskipun nama field
                 * ms_pharmacy belum cocok.
                 */

                if (!empty($row['id_pharmacy_details'])) {

                    $obat[] = $row;
                }
            }
        }

        $stmtObat->close();
    }
}



/*
|--------------------------------------------------------------------------
| MASUKKAN DATA KE RESPONSE
|--------------------------------------------------------------------------
*/

$data['tindakan'] = $tindakan;

$data['obat'] = $obat;



/*
|--------------------------------------------------------------------------
| BUAT TEXT TINDAKAN
|--------------------------------------------------------------------------
*/

$tindakanText = [];

foreach ($tindakan as $item) {

    if (!empty($item['billing_item'])) {

        $tindakanText[] = trim(
            $item['billing_item']
        );
    }
}

$data['tindakan_text'] = !empty($tindakanText)
    ? implode(', ', $tindakanText)
    : '-';



/*
|--------------------------------------------------------------------------
| BUAT TEXT OBAT
|--------------------------------------------------------------------------
*/

$obatText = [];

foreach ($obat as $item) {

    /*
     * Nama obat sudah diambil dari:
     *
     * ppd.item_name
     * atau
     * ms_pharmacy
     */

    $namaObat = $item['nama_obat'] ?? '';

    /*
     * Kalau nama obat belum ditemukan,
     * gunakan ID pharmacy sebagai fallback.
     */

    if (empty($namaObat)) {

        $namaObat =
            'Obat ID ' .
            ($item['id_pharmacy'] ?? '-');
    }

    $namaObat = trim($namaObat);


    /*
     * Signa
     */

    if (!empty($item['signa'])) {

        $namaObat .=
            ' (' .
            trim($item['signa']) .
            ')';
    }


    /*
     * Qty
     */

    if (
        isset($item['qty']) &&
        $item['qty'] !== '' &&
        $item['qty'] !== null
    ) {

        $namaObat .=
            ' x ' .
            $item['qty'];
    }


    $obatText[] = $namaObat;
}

$data['obat_text'] = !empty($obatText)
    ? implode(', ', $obatText)
    : '-';



/*
|--------------------------------------------------------------------------
| GABUNG TINDAKAN + OBAT
|--------------------------------------------------------------------------
*/

$tindakanObatText = [];

if (!empty($tindakanText)) {

    $tindakanObatText[] =
        'Tindakan: ' .
        implode(', ', $tindakanText);
}

if (!empty($obatText)) {

    $tindakanObatText[] =
        'Obat: ' .
        implode(', ', $obatText);
}

$data['tindakan_obat_text'] =
    !empty($tindakanObatText)
    ? implode('; ', $tindakanObatText)
    : '-';



/*
|--------------------------------------------------------------------------
| RESPONSE
|--------------------------------------------------------------------------
*/

echo json_encode(
    [
        'status' => true,
        'data' => $data
    ],
    JSON_UNESCAPED_UNICODE
);
