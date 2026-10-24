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