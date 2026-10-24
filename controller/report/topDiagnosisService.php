<?php

function topDiagnosisPeriod(array $input): array
{
   $fromDate = $input['fromDate'] ?? date('Y-m-01');
   $toDate = $input['toDate'] ?? date('Y-m-d');
   foreach ([$fromDate, $toDate] as $value) {
      if (!is_string($value)) {
         throw new InvalidArgumentException('Format tanggal tidak valid.');
      }
      $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
      if (!$date || $date->format('Y-m-d') !== $value) {
         throw new InvalidArgumentException('Format tanggal tidak valid.');
      }
   }
   if ($fromDate > $toDate) {
      throw new InvalidArgumentException('Tanggal mulai tidak boleh melebihi tanggal akhir.');
   }
   return ['fromDate' => $fromDate, 'toDate' => $toDate];
}

function topDiagnosisSql(): string
{
   return "
      SELECT ranked.icd_code,
             COALESCE(NULLIF(catalog.diagnosis_name, ''), 'Nama diagnosis tidak tersedia') AS diagnosis_name,
             ranked.total_visits
      FROM (
         SELECT UPPER(TRIM(pv.diagnosa)) AS icd_code,
                COUNT(DISTINCT pv.visit_ID) AS total_visits
         FROM pasien_visit pv
         WHERE pv.id_customer = ?
           AND pv.visit_date >= ? AND pv.visit_date < ?
           AND (pv.visit_status IS NULL OR pv.visit_status <> 99)
           AND pv.diagnosa IS NOT NULL AND TRIM(pv.diagnosa) <> ''
         GROUP BY UPPER(TRIM(pv.diagnosa))
      ) ranked
      LEFT JOIN (
         SELECT UPPER(TRIM(code)) AS icd_code, MAX(icd10) AS diagnosis_name
         FROM icd_10
         GROUP BY UPPER(TRIM(code))
      ) catalog ON catalog.icd_code = ranked.icd_code
      ORDER BY ranked.total_visits DESC, ranked.icd_code ASC
      LIMIT 10
   ";
}

function topDiagnosisReport(mysqli $connection, string $customerId, array $input): array
{
   $period = topDiagnosisPeriod($input);
   $fromDate = $period['fromDate'];
   $untilDate = (new DateTimeImmutable($period['toDate']))->modify('+1 day')->format('Y-m-d');
   $statement = $connection->prepare(topDiagnosisSql());
   if (!$statement) {
      throw new RuntimeException('Gagal menyiapkan laporan diagnosis.');
   }
   $statement->bind_param('sss', $customerId, $fromDate, $untilDate);
   if (!$statement->execute()) {
      throw new RuntimeException('Gagal mengambil laporan diagnosis.');
   }
   $rows = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
   $statement->close();
   $total = 0;
   foreach ($rows as $index => &$row) {
      $row['rank'] = $index + 1;
      $row['total_visits'] = (int) $row['total_visits'];
      $total += $row['total_visits'];
   }
   unset($row);
   return array_merge($period, ['data' => $rows, 'total_top10' => $total]);
}

function topDiagnosisPatientsSql(): string
{
   return "
      SELECT DISTINCT pv.visit_ID,
             COALESCE(NULLIF(ms.nomor_rm, ''), '-') AS nomor_rm,
             COALESCE(NULLIF(ms.patient_name, ''), NULLIF(pv.patient_name_pcare, ''), '-') AS patient_name,
             COALESCE(NULLIF(ms.patient_gender, ''), '-') AS patient_gender,
             COALESCE(NULLIF(md.doctor_name, ''), NULLIF(pv.id_doctor, ''), '-') AS doctor_name
      FROM pasien_visit pv
      LEFT JOIN ms_patient ms ON ms.id_patient = pv.id_patient
      LEFT JOIN ms_doctor md ON CAST(md.id_doctor AS CHAR) = CAST(pv.id_doctor AS CHAR)
      WHERE pv.id_customer = ?
        AND pv.visit_date >= ? AND pv.visit_date < ?
        AND (pv.visit_status IS NULL OR pv.visit_status <> 99)
        AND UPPER(TRIM(pv.diagnosa)) = ?
      ORDER BY patient_name ASC, pv.visit_ID ASC
   ";
}

function topDiagnosisPatients(mysqli $connection, string $customerId, array $input): array
{
   $period = topDiagnosisPeriod($input);
   $code = $input['icd_code'] ?? '';
   if (!is_string($code) || trim($code) === '' || strlen($code) > 32) {
      throw new InvalidArgumentException('Kode diagnosis tidak valid.');
   }
   $code = strtoupper(trim($code));
   $fromDate = $period['fromDate'];
   $untilDate = (new DateTimeImmutable($period['toDate']))->modify('+1 day')->format('Y-m-d');
   $statement = $connection->prepare(topDiagnosisPatientsSql());
   if (!$statement) {
      throw new RuntimeException('Gagal menyiapkan daftar pasien diagnosis.');
   }
   $statement->bind_param('ssss', $customerId, $fromDate, $untilDate, $code);
   if (!$statement->execute()) {
      throw new RuntimeException('Gagal mengambil daftar pasien diagnosis.');
   }
   $rows = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
   $statement->close();
   return array_merge($period, ['icd_code' => $code, 'data' => $rows, 'total' => count($rows)]);
}