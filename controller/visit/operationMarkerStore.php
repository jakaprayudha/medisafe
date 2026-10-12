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

function operationMarkerPatientHistory(mysqli $db, string $patient, int $clinic): array
{
   $stmt = $db->prepare("SELECT m.id, m.visit_ID, m.marker_type, m.notes,
      m.created_by, m.created_at, (OCTET_LENGTH(m.image_png) > 0) AS has_image
      FROM operation_markers m
      JOIN pasien_visit v ON v.visit_ID = m.visit_ID COLLATE utf8mb4_general_ci AND v.id_customer = m.id_customer
      WHERE v.id_patient = ? AND m.id_customer = ?
      AND (v.visit_status IS NULL OR v.visit_status <> 99)
      ORDER BY m.id DESC");
   $stmt->bind_param('si', $patient, $clinic);
   $stmt->execute();
   $history = [];
   foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $document) {
      $history[$document['visit_ID']][] = $document;
   }
   return $history;
}
