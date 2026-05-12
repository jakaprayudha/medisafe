<?php
require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/../satusehat/variable.php';

function tampildata($query)
{
    global $koneksi;
    $result = mysqli_query($koneksi, $query);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

$clinics = tampildata("SELECT * FROM setting_satusehat where client_id is not null and client_secret is not null and organization_id is not null");
foreach ($clinics as $clinic) {
    $id_customer = $clinic['id_customer'];
    $organizationId = getSetting('organization_id', $id_customer);

    $patientVisits = tampildata("SELECT ps.id_visit, ps.id_encounter, ps.visit_date, ps.visit_time, ms_patient.idsh as idsh_pasien, ms_patient.patient_name as nama_pasien, ms_doctor.doctor_name, ms_doctor.idsh as idsh_dokter, icd_10.code, icd_10.icd10 as icd_name FROM pasien_visit ps JOIN ms_patient ON ms_patient.id_patient = ps.id_patient JOIN ms_doctor ON ps.id_doctor LIKE CONCAT('%', ms_doctor.doctor_name, '%') JOIN icd_10 ON icd_10.code = ps.diagnosa where ps.is_masuk_ruangan = 1 AND id_condition is null AND ps.id_customer = '$id_customer'");

    foreach ($patientVisits as $patient) {
        $id_pasien_visit = $patient['id_visit'];

        // Create a DateTime object from the original date-time string
        $date_time = new DateTime("$patient[visit_date] $patient[visit_time]");

        // Set the desired timezone (Asia/Jakarta, which is +07:00)
        $timezone = new DateTimeZone('Asia/Jakarta');
        $date_time->setTimezone($timezone);

        // Format the DateTime object to ISO 8601 format with timezone offset
        $dateTime = $date_time->format('Y-m-d\TH:i:sP');

        $jsonData = json_encode($data = [
            "resourceType" => "Condition",
            "clinicalStatus" => [
                "coding" => [
                    [
                        "system" => "http://terminology.hl7.org/CodeSystem/condition-clinical",
                        "code" => "active",
                        "display" => "Active"
                    ]
                ]
            ],
            "category" => [
                [
                    "coding" => [
                        [
                            "system" => "http://terminology.hl7.org/CodeSystem/condition-category",
                            "code" => "encounter-diagnosis",
                            "display" => "$patient[icd_name]"
                        ]
                    ]
                ]
            ],
            "code" => [
                "coding" => [
                    [
                        "system" => "http://hl7.org/fhir/sid/icd-10",
                        "code" => "$patient[code]",
                        "display" => "$patient[icd_name]"
                    ]
                ]
            ],
            "subject" => [
                "reference" => "Patient/$patient[idsh_pasien]",
                "display" => "$patient[nama_pasien]"
            ],
            "encounter" => [
                "reference" => "Encounter/$patient[id_encounter]"
            ],
            "onsetDateTime" => "$dateTime",
            "recordedDate" => "$dateTime",
            "recorder" => [
                "reference" => "Practitioner/$patient[idsh_dokter]",
                "display" => "$patient[doctor_name]"
            ],
            "note" => [
                [
                    "text" => "$patient[icd_name]"
                ]
            ]
        ]);

        $url = $fhirUrl . "/Condition";

        $token = json_decode(generateToken($id_customer), true);

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token['access_token']
        ];

        // Initialize cURL session
        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

        // echo json_encode($headers);
        // die;
        // Execute cURL session and store the response
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            echo 'cURL error: ' . curl_error($ch);
            die;
        }

        // Close cURL session
        curl_close($ch);

        // var_dump($response);die;

        $dataCondition = json_decode($response, true);

        $id_condition = $dataCondition['id'] ?? null;
        if (!$id_condition) {
            $error = mysqli_escape_string($koneksi, $dataCondition['issue'][0]['details']['text']);
            // Log error to satusehat_log
            $resourceType = 'Condition';
            $referenceId = $id_pasien_visit;
            $status = 'error';
            $message = mysqli_escape_string($koneksi, $response);
            mysqli_query($koneksi, "INSERT INTO satusehat_log (resource_type, reference_id, id_customer, status, message) VALUES ('$resourceType', '$referenceId', '$id_customer', '$status', '$message')");
            mysqli_query($koneksi, "UPDATE pasien_visit SET error_condition='$error' WHERE id_visit='$id_pasien_visit'");
            echo "Failed to create Condition for patient visit ID: $id_pasien_visit. Error: $error\n";
            continue;
        }

        // Log success to satusehat_log
        $resourceType = 'Condition';
        $referenceId = $id_pasien_visit;
        $resourceId = $id_condition;
        $status = 'success';
        $message = mysqli_escape_string($koneksi, $response);
        mysqli_query($koneksi, "INSERT INTO satusehat_log (resource_type, reference_id, resource_id, id_customer, status, message) VALUES ('$resourceType', '$referenceId', '$resourceId', '$id_customer', '$status', '$message')");
        // update data
        mysqli_query($koneksi, "UPDATE pasien_visit SET id_condition='$id_condition' WHERE id_visit='$id_pasien_visit'");
        echo "Updated Condition with ID: $id_condition for patient visit ID: $id_pasien_visit\n";
    }
}

echo "Condition Success";
