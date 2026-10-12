<?php

function historyVisitType(array $visit): string
{
   $source = trim((string) ($visit['source_hub'] ?? ''));
   if ((int) ($visit['status_rawatinap'] ?? 0) === 1 || strcasecmp($source, 'Rawat Inap') === 0) {
      return 'Rawat Inap';
   }
   if (in_array(strtoupper($source), ['UGD', 'IGD'], true)) {
      return 'UGD';
   }
   if (strcasecmp($source, 'Poliklinik') === 0) {
      return 'Poliklinik';
   }
   return $source !== '' ? $source : '-';
}

function historyDischargeStatus(array $visit): string
{
   $status = trim((string) ($visit['status_pulang'] ?? ''));
   $labels = [
      '0' => 'Berobat Jalan',
      '1' => 'Meninggal',
      '3' => 'Berobat Jalan',
      '4' => 'Rujuk Lanjut',
      '5' => 'Rujuk Internal',
      '6' => 'Rujuk Horizontal',
      'A' => 'Rujuk Atas Permintaan Sendiri (APS)',
   ];
   if (isset($labels[$status])) {
      return $labels[$status];
   }
   if ($status === '') {
      return historyVisitType($visit);
   }
   return '-';
}
