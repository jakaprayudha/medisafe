<?php

require_once __DIR__ . '/../../database/connect.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function out($data, $code = 200)
{
   http_response_code($code);
   echo json_encode($data, JSON_UNESCAPED_UNICODE);
   exit;
}

$roles = $_SESSION['roles'] ?? '';
if (empty($_SESSION['id_customer']) || !in_array($roles, ['superadmin', 'admin'], true)) {
   out(['status' => 'error', 'message' => 'Akses tidak diizinkan.'], 403);
}
$isSuper = $roles === 'superadmin';
// Non-superadmin is always pinned to own faskes; the id_customer param is ignored. 0 = all faskes.
$idc = $isSuper ? (int) ($_GET['id_customer'] ?? 0) : (int) $_SESSION['id_customer'];

function rows($koneksi, $sql, $types = '', $params = [])
{
   $st = mysqli_prepare($koneksi, $sql);
   if (!$st) return [];
   if ($params) mysqli_stmt_bind_param($st, $types, ...$params);
   if (!mysqli_stmt_execute($st)) return [];
   $res = mysqli_stmt_get_result($st);
   return $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];
}

function hasColumns($koneksi, $table, array $cols)
{
   $in = implode(',', array_fill(0, count($cols), '?'));
   $r = rows(
      $koneksi,
      "SELECT COUNT(*) c FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name IN ($in)",
      str_repeat('s', count($cols) + 1),
      array_merge([$table], $cols)
   );
   return $r && (int) $r[0]['c'] === count($cols);
}

// [label, table, id column, error column|null]. Missing tables/columns are skipped (not every faskes DB has all of them).
const SS_RESOURCES = [
   ['Patient', 'ms_patient', 'idsh', null],
   ['Practitioner', 'ms_doctor', 'idsh', null],
   ['Encounter', 'pasien_visit', 'id_encounter', 'error_encounter'],
   ['Condition', 'pasien_visit', 'id_condition', 'error_condition'],
   ['Medication', 'obat', 'medication_id', 'error_medication'],
   // ['ServiceRequest (Lab)', 'permintaan_lab_detail', 'id_service_request', 'error_service_request'],
   // ['Observation (Lab)', 'permintaan_lab_detail', 'id_observation', 'error_observation'],
   // ['DiagnosticReport (Lab)', 'permintaan_lab_detail', 'id_diagnostic_report', 'error_diagnostic_report'],
   // ['ServiceRequest (Rad)', 'permintaan_radiologi_detail', 'id_service_request', 'error_service_request'],
   // ['ImagingStudy', 'permintaan_radiologi_detail', 'id_imaging_study', 'error_imaging_study'],
   // ['Observation (Rad)', 'permintaan_radiologi_detail', 'id_observation', 'error_observation'],
   // ['DiagnosticReport (Rad)', 'permintaan_radiologi_detail', 'id_diagnostic_report', 'error_diagnostic_report'],
   ['MedicationRequest', 'permintaan_pharmacy_details', 'medication_request_id', 'error_medication_request'],
   ['MedicationDispense', 'permintaan_pharmacy_details', 'medication_dispense_id', 'error_medication_dispense'],
   ['MedicationStatement', 'permintaan_pharmacy_details', 'medication_statement_id', 'error_medication_statement'],
];

// ok / error / pending per source row. Detail tables have no id_customer of their own, so they are only
// counted when they carry one; otherwise they are left to the satusehat_log numbers.
function backlog($koneksi, $idc)
{
   $out = [];
   foreach (SS_RESOURCES as [$label, $table, $idCol, $errCol]) {
      $cols = array_filter([$idCol, $errCol, 'id_customer']);
      if (!hasColumns($koneksi, $table, $cols)) continue;
      $err = $errCol ? "SUM($errCol IS NOT NULL AND $errCol <> '')" : '0';
      $pend = $errCol ? "SUM(($idCol IS NULL OR $idCol = '') AND ($errCol IS NULL OR $errCol = ''))" : "SUM($idCol IS NULL OR $idCol = '')";
      $sql = "SELECT SUM($idCol IS NOT NULL AND $idCol <> '') ok, $err error, $pend pending FROM `$table`" . ($idc ? ' WHERE id_customer = ?' : '');
      $r = $idc ? rows($koneksi, $sql, 'i', [$idc]) : rows($koneksi, $sql);
      $out[] = ['resource' => $label, 'ok' => (int) ($r[0]['ok'] ?? 0), 'error' => (int) ($r[0]['error'] ?? 0), 'pending' => (int) ($r[0]['pending'] ?? 0)];
   }
   return $out;
}

$action = $_GET['action'] ?? 'summary';

if ($action === 'summary') {
   $w = $idc ? ' WHERE id_customer = ?' : '';
   $p = $idc ? ['i', [$idc]] : ['', []];
   $log = rows(
      $koneksi,
      "SELECT resource_type resource, SUM(status = 'success') ok, SUM(status = 'error') error, MAX(IF(status = 'success', created_at, NULL)) last_success FROM satusehat_log$w GROUP BY resource_type ORDER BY resource_type",
      ...$p
   );
   out(['status' => 'success', 'log' => $log, 'backlog' => backlog($koneksi, $idc)]);
}

if ($action === 'errors') {
   $w = $idc ? " AND l.id_customer = ?" : '';
   $p = $idc ? ['i', [$idc]] : ['', []];
   $data = rows(
      $koneksi,
      "SELECT l.created_at, l.id_customer, c.clinic_name, l.resource_type, l.reference_id, LEFT(l.message, 500) message
       FROM satusehat_log l LEFT JOIN setting_clinic c ON c.id_customer = l.id_customer
       WHERE l.status = 'error'$w ORDER BY l.id DESC LIMIT 200",
      ...$p
   );
   out(['status' => 'success', 'data' => $data]);
}

if ($action === 'config') {
   // never expose client_secret / token
   $w = $idc ? ' WHERE s.id_customer = ?' : '';
   $p = $idc ? ['i', [$idc]] : ['', []];
   $data = rows(
      $koneksi,
      "SELECT s.id_customer, c.clinic_name,
              (s.client_id <> '' AND s.client_id IS NOT NULL AND s.client_secret <> '' AND s.organization_id <> '') credentials_ok,
              (s.location_id <> '' AND s.location_id IS NOT NULL) location_ok,
              s.expire_at, (s.expire_at IS NOT NULL AND s.expire_at > NOW()) token_valid
       FROM setting_satusehat s LEFT JOIN setting_clinic c ON c.id_customer = s.id_customer$w",
      ...$p
   );
   out(['status' => 'success', 'data' => $data]);
}

if ($action === 'by_faskes') {
   if (!$isSuper) out(['status' => 'error', 'message' => 'Akses tidak diizinkan.'], 403);
   $data = rows(
      $koneksi,
      "SELECT c.id_customer, c.clinic_name,
              COALESCE(SUM(l.status = 'success'), 0) ok, COALESCE(SUM(l.status = 'error'), 0) error,
              MAX(l.created_at) last_activity,
              (SELECT COUNT(*) FROM setting_satusehat s WHERE s.id_customer = c.id_customer) configured
       FROM setting_clinic c LEFT JOIN satusehat_log l ON l.id_customer = c.id_customer
       GROUP BY c.id_customer, c.clinic_name ORDER BY error DESC, c.clinic_name"
   );
   out(['status' => 'success', 'data' => $data]);
}

out(['status' => 'error', 'message' => 'Action tidak dikenal.'], 400);
