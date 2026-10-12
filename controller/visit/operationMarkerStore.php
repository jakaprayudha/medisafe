<?php

function operationMarkerTypes(): array
{
   return ['wanita' => 'Wanita', 'pria' => 'Pria', 'gigi' => 'Gigi & Mulut',
      'ekstrimitas' => 'Ekstremitas', 'mata' => 'Mata'];
}

function operationMarkerVisit(mysqli $db, string $visit, int $clinic): ?array
{
   $stmt = $db->prepare("SELECT v.visit_ID, p.nomor_rm, p.patient_name
      FROM pasien_visit v JOIN ms_patient p ON p.id_patient = v.id_patient
      WHERE v.visit_ID = ? AND v.id_customer = ? LIMIT 1");
   $stmt->bind_param('si', $visit, $clinic);
   $stmt->execute();
   return $stmt->get_result()->fetch_assoc();
}

function operationMarkerDocuments(mysqli $db, string $visit, int $clinic, ?int $id = null): array
{
   $sql = "SELECT * FROM operation_markers WHERE visit_ID = ? AND id_customer = ?";
   if ($id !== null) $sql .= " AND id = ?";
   $stmt = $db->prepare($sql . " ORDER BY id DESC");
   if ($id !== null) {
      $stmt->bind_param('sii', $visit, $clinic, $id);
   } else {
      $stmt->bind_param('si', $visit, $clinic);
   }
   $stmt->execute();
   return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
