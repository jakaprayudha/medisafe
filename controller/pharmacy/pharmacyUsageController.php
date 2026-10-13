<?php

include '../../database/connect.php';

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
   session_start();
}

$id_customer = $_SESSION['id_customer'] ?? null;
$username    = $_SESSION['username'] ?? 'system';

if (!$id_customer) {
   echo json_encode([
      'status'  => 'error',
      'message' => 'Session customer tidak ditemukan atau telah kedaluwarsa.'
   ]);
   exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
   case 'GET':
      $action = $_GET['action'] ?? 'list';
      switch ($action) {
         case 'get_medicines':
            getMedicinesList($id_customer);
            break;
         case 'detail':
            getUsageDetail($id_customer);
            break;
         case 'list':
         default:
            getUsageList($id_customer);
            break;
      }
      break;

   default:
      echo json_encode([
         'status'  => 'error',
         'message' => 'Metode HTTP tidak didukung.'
      ]);
      break;
}

/**
 * Mengambil daftar master obat untuk dropdown pencarian / pilihan obat
 */
function getMedicinesList($id_customer)
{
   global $koneksi;

   $search = trim($_GET['q'] ?? '');

   $sql = "
      SELECT 
         mp.id_pharmacy,
         mp.pharmacy_code,
         mp.pharmacy_name_generic,
         mp.pharmacy_name_trade,
         mp.pharmacy_category,
         mp.pharmcy_golongan,
         mp.pharmacy_unit,
         COALESCE(mp.pharmacy_stock, 0) AS pharmacy_stock,
         (
            SELECT COUNT(DISTINCT pp.id_visit)
            FROM permintaan_pharmacy_details ppd
            INNER JOIN permintaan_pharmacy pp 
               ON pp.id_permintaan_farmasi = ppd.id_permintaan_farmasi
            WHERE ppd.id_pharmacy = mp.id_pharmacy
              AND CAST(pp.id_customer AS CHAR) = CAST(mp.id_customer AS CHAR)
         ) AS total_pasien_terlayani,
         (
            SELECT COALESCE(SUM(ppd.qty), 0)
            FROM permintaan_pharmacy_details ppd
            INNER JOIN permintaan_pharmacy pp 
               ON pp.id_permintaan_farmasi = ppd.id_permintaan_farmasi
            WHERE ppd.id_pharmacy = mp.id_pharmacy
              AND CAST(pp.id_customer AS CHAR) = CAST(mp.id_customer AS CHAR)
         ) AS total_qty_terpakai
      FROM ms_pharmacy mp
      WHERE CAST(mp.id_customer AS CHAR) = CAST(? AS CHAR)
   ";

   $params = [$id_customer];
   $types  = 's';

   if (!empty($search)) {
      $sql .= " AND (
         mp.pharmacy_code LIKE ? 
         OR mp.pharmacy_name_generic LIKE ? 
         OR mp.pharmacy_name_trade LIKE ?
      )";
      $searchWildcard = '%' . $search . '%';
      $params[] = $searchWildcard;
      $params[] = $searchWildcard;
      $params[] = $searchWildcard;
      $types .= 'sss';
   }

   $sql .= " ORDER BY total_qty_terpakai DESC, mp.pharmacy_name_generic ASC";

   $stmt = $koneksi->prepare($sql);
   if (!$stmt) {
      echo json_encode([
         'status'  => 'error',
         'message' => 'Gagal menyiapkan query: ' . $koneksi->error
      ]);
      return;
   }

   $stmt->bind_param($types, ...$params);
   $stmt->execute();
   $result = $stmt->get_result();

   $data = [];
   while ($row = $result->fetch_assoc()) {
      $data[] = $row;
   }
   $stmt->close();

   echo json_encode([
      'status' => 'success',
      'data'   => $data
   ], JSON_UNESCAPED_UNICODE);
}

/**
 * Mengambil daftar riwayat pemakaian obat oleh pasien
 */
function getUsageList($id_customer)
{
   global $koneksi;

   $idPharmacy = trim($_GET['id_pharmacy'] ?? '');
   $fromDate   = trim($_GET['fromDate'] ?? '');
   $toDate     = trim($_GET['toDate'] ?? '');
   $statusReq  = trim($_GET['status_permintaan'] ?? 'all');
   $layanan    = trim($_GET['layanan'] ?? '');
   $search     = trim($_GET['search'] ?? '');

   $whereClauses = [
      "CAST(pp.id_customer AS CHAR) = CAST(? AS CHAR)"
   ];
   $params = [$id_customer];
   $types  = 's';

   // Filter Obat Spesifik
   if (!empty($idPharmacy) && $idPharmacy !== 'all') {
      $whereClauses[] = "ppd.id_pharmacy = ?";
      $params[] = (int) $idPharmacy;
      $types .= 'i';
   }

   // Filter Rentang Tanggal
   if (!empty($fromDate)) {
      $whereClauses[] = "COALESCE(DATE(pp.created_at), pv.visit_date) >= ?";
      $params[] = $fromDate;
      $types .= 's';
   }
   if (!empty($toDate)) {
      $whereClauses[] = "COALESCE(DATE(pp.created_at), pv.visit_date) <= ?";
      $params[] = $toDate;
      $types .= 's';
   }

   // Filter Status Permintaan
   if ($statusReq !== 'all' && $statusReq !== '') {
      $whereClauses[] = "pp.status_permintaan = ?";
      $params[] = (int) $statusReq;
      $types .= 'i';
   }

   // Filter Layanan / Source Hub
   if (!empty($layanan)) {
      $whereClauses[] = "(pv.source_hub = ? OR mpoli.poli_name = ?)";
      $params[] = $layanan;
      $params[] = $layanan;
      $types .= 'ss';
   }

   // Filter Pencarian Pasien / Obat / No RM
   if (!empty($search)) {
      $whereClauses[] = "(
         p.nomor_rm LIKE ?
         OR p.patient_name LIKE ?
         OR pv.patient_name_pcare LIKE ?
         OR mp.pharmacy_name_generic LIKE ?
         OR mp.pharmacy_name_trade LIKE ?
         OR pp.permintaan_number LIKE ?
         OR md.doctor_name LIKE ?
      )";
      $searchParam = '%' . $search . '%';
      for ($i = 0; $i < 7; $i++) {
         $params[] = $searchParam;
      }
      $types .= 'sssssss';
   }

   $whereSql = implode(' AND ', $whereClauses);

   $sql = "
      SELECT 
         ppd.id_pharmacy_details,
         ppd.id_permintaan_farmasi,
         ppd.id_pharmacy,
         ppd.item_name,
         ppd.signa,
         ppd.qty,
         ppd.catatan,
         ppd.harga,
         COALESCE(ppd.status_item, 1) AS status_item,
         COALESCE(ppd.created_at, pp.created_at) AS detail_created_at,

         pp.id_visit,
         pp.permintaan_number,
         pp.status_permintaan,
         pp.tipe_obat,
         pp.rck_jumlah,
         pp.rck_satuan,
         pp.rck_signa,
         pp.catatan_permintaan,
         pp.created_at AS tgl_resep,

         pv.visit_ID,
         pv.visit_date,
         pv.source_hub,
         pv.id_doctor,
         pv.id_poli,
         pv.id_provider,

         p.id_patient,
         COALESCE(p.nomor_rm, '-') AS nomor_rm,
         COALESCE(p.patient_name, pv.patient_name_pcare, '-') AS patient_name,
         p.patient_gender,
         p.patient_datebirth,
         p.patient_phone,
         p.patient_address,

         mp.pharmacy_code,
         mp.pharmacy_name_generic,
         mp.pharmacy_name_trade,
         mp.pharmacy_category,
         mp.pharmacy_sub_category,
         mp.pharmcy_golongan,
         mp.pharmacy_unit,

         COALESCE(md.doctor_name, pv.id_doctor, '-') AS doctor_name,
         COALESCE(mpoli.poli_name, pv.id_poli, pv.source_hub, '-') AS poli_name,
         COALESCE(mprov.provider_name, '-') AS provider_name

      FROM permintaan_pharmacy_details ppd

      INNER JOIN permintaan_pharmacy pp
         ON pp.id_permintaan_farmasi = ppd.id_permintaan_farmasi

      LEFT JOIN ms_pharmacy mp
         ON mp.id_pharmacy = ppd.id_pharmacy

      LEFT JOIN pasien_visit pv
         ON pv.visit_ID = pp.id_visit
         AND CAST(pv.id_customer AS CHAR) = CAST(pp.id_customer AS CHAR)

      LEFT JOIN ms_patient p
         ON p.id_patient = pv.id_patient

      LEFT JOIN ms_doctor md
         ON md.id_doctor = pv.id_doctor

      LEFT JOIN ms_poli mpoli
         ON mpoli.id_poli = pv.id_poli

      LEFT JOIN ms_provider mprov
         ON mprov.id_provider = pv.id_provider

      WHERE $whereSql

      ORDER BY COALESCE(pp.created_at, pv.visit_date) DESC, ppd.id_pharmacy_details DESC
   ";

   $stmt = $koneksi->prepare($sql);
   if (!$stmt) {
      echo json_encode([
         'status'  => 'error',
         'message' => 'Gagal mempersiapkan query: ' . $koneksi->error
      ]);
      return;
   }

   $stmt->bind_param($types, ...$params);
   $stmt->execute();
   $result = $stmt->get_result();

   $rows = [];
   $patientSet = [];
   $totalQty = 0;
   $totalNominal = 0;

   while ($row = $result->fetch_assoc()) {
      $qty = (float) ($row['qty'] ?? 0);
      $harga = (float) ($row['harga'] ?? 0);
      $subtotal = $qty * $harga;

      $totalQty += $qty;
      $totalNominal += $subtotal;

      // Unique patient identifier
      $patientKey = !empty($row['id_patient']) ? $row['id_patient'] : ($row['nomor_rm'] . '_' . $row['patient_name']);
      $patientSet[$patientKey] = true;

      // Hitung Usia Pasien
      $ageText = '-';
      if (!empty($row['patient_datebirth']) && $row['patient_datebirth'] !== '0000-00-00') {
         try {
            $dob = new DateTime($row['patient_datebirth']);
            $refDate = !empty($row['tgl_resep']) ? new DateTime($row['tgl_resep']) : new DateTime();
            $ageDiff = $dob->diff($refDate);
            $ageText = $ageDiff->y . ' Thn ' . $ageDiff->m . ' Bln';
         } catch (Throwable $e) {
            $ageText = '-';
         }
      }

      // Status Permintaan Label & Badge
      $statusVal = (int) ($row['status_permintaan'] ?? 0);
      switch ($statusVal) {
         case 3:
            $statusLabel = 'Selesai';
            $statusBadge = '<span class="badge bg-success-subtle text-success border border-success"><i class="ti ti-check me-1"></i>Selesai</span>';
            break;
         case 2:
            $statusLabel = 'Diproses';
            $statusBadge = '<span class="badge bg-primary-subtle text-primary border border-primary"><i class="ti ti-loader me-1"></i>Diproses</span>';
            break;
         case 1:
            $statusLabel = 'Persiapan';
            $statusBadge = '<span class="badge bg-info-subtle text-info border border-info"><i class="ti ti-clock me-1"></i>Persiapan</span>';
            break;
         case 0:
         default:
            $statusLabel = 'Menunggu';
            $statusBadge = '<span class="badge bg-warning-subtle text-warning border border-warning"><i class="ti ti-hourglass me-1"></i>Menunggu</span>';
            break;
      }

      $row['qty_formatted']       = number_format($qty, 0, ',', '.');
      $row['harga_formatted']     = 'Rp ' . number_format($harga, 0, ',', '.');
      $row['subtotal']            = $subtotal;
      $row['subtotal_formatted']  = 'Rp ' . number_format($subtotal, 0, ',', '.');
      $row['patient_age']         = $ageText;
      $row['status_label']        = $statusLabel;
      $row['status_badge']        = $statusBadge;
      $row['tgl_formatted']       = !empty($row['tgl_resep']) ? date('d/m/Y H:i', strtotime($row['tgl_resep'])) : (!empty($row['visit_date']) ? date('d/m/Y', strtotime($row['visit_date'])) : '-');

      $rows[] = $row;
   }

   $stmt->close();

   // Informasi obat terpilih jika memfilter 1 obat
   $selectedPharmacyInfo = null;
   if (!empty($idPharmacy) && $idPharmacy !== 'all') {
      $stmtInfo = $koneksi->prepare("
         SELECT 
            id_pharmacy,
            pharmacy_code,
            pharmacy_name_generic,
            pharmacy_name_trade,
            pharmacy_category,
            pharmacy_sub_category,
            pharmcy_golongan,
            pharmacy_unit,
            COALESCE(pharmacy_stock, 0) AS pharmacy_stock,
            stok_min,
            stok_max
         FROM ms_pharmacy
         WHERE id_pharmacy = ?
           AND CAST(id_customer AS CHAR) = CAST(? AS CHAR)
         LIMIT 1
      ");
      if ($stmtInfo) {
         $stmtInfo->bind_param('is', $idPharmacy, $id_customer);
         $stmtInfo->execute();
         $selectedPharmacyInfo = $stmtInfo->get_result()->fetch_assoc();
         $stmtInfo->close();
      }
   }

   $summary = [
      'total_records'          => count($rows),
      'total_patients'         => count($patientSet),
      'total_qty'              => $totalQty,
      'total_nominal'          => $totalNominal,
      'total_nominal_format'   => 'Rp ' . number_format($totalNominal, 0, ',', '.'),
      'selected_pharmacy'      => $selectedPharmacyInfo
   ];

   echo json_encode([
      'status'  => 'success',
      'summary' => $summary,
      'data'    => $rows
   ], JSON_UNESCAPED_UNICODE);
}

/**
 * Mengambil detail resep & penggunaan obat untuk satu record
 */
function getUsageDetail($id_customer)
{
   global $koneksi;

   $idDetail = $_GET['id_pharmacy_details'] ?? null;
   if (!$idDetail) {
      echo json_encode(['status' => 'error', 'message' => 'ID detail tidak ditemukan.']);
      return;
   }

   $sql = "
      SELECT 
         ppd.*,
         pp.id_visit,
         pp.permintaan_number,
         pp.status_permintaan,
         pp.tipe_obat,
         pp.catatan_permintaan,
         pp.created_at AS tgl_resep,

         pv.visit_ID,
         pv.visit_date,
         pv.source_hub,

         p.id_patient,
         COALESCE(p.nomor_rm, '-') AS nomor_rm,
         COALESCE(p.patient_name, pv.patient_name_pcare, '-') AS patient_name,
         p.patient_gender,
         p.patient_datebirth,
         p.patient_phone,
         p.patient_address,

         mp.pharmacy_code,
         mp.pharmacy_name_generic,
         mp.pharmacy_name_trade,
         mp.pharmacy_category,
         mp.pharmcy_golongan,
         mp.pharmacy_unit,

         COALESCE(md.doctor_name, pv.id_doctor, '-') AS doctor_name,
         COALESCE(mpoli.poli_name, pv.id_poli, pv.source_hub, '-') AS poli_name,
         COALESCE(mprov.provider_name, '-') AS provider_name

      FROM permintaan_pharmacy_details ppd
      INNER JOIN permintaan_pharmacy pp 
         ON pp.id_permintaan_farmasi = ppd.id_permintaan_farmasi
      LEFT JOIN ms_pharmacy mp 
         ON mp.id_pharmacy = ppd.id_pharmacy
      LEFT JOIN pasien_visit pv 
         ON pv.visit_ID = pp.id_visit
      LEFT JOIN ms_patient p 
         ON p.id_patient = pv.id_patient
      LEFT JOIN ms_doctor md 
         ON md.id_doctor = pv.id_doctor
      LEFT JOIN ms_poli mpoli 
         ON mpoli.id_poli = pv.id_poli
      LEFT JOIN ms_provider mprov 
         ON mprov.id_provider = pv.id_provider
      WHERE ppd.id_pharmacy_details = ?
        AND CAST(pp.id_customer AS CHAR) = CAST(? AS CHAR)
      LIMIT 1
   ";

   $stmt = $koneksi->prepare($sql);
   if (!$stmt) {
      echo json_encode(['status' => 'error', 'message' => $koneksi->error]);
      return;
   }

   $stmt->bind_param('is', $idDetail, $id_customer);
   $stmt->execute();
   $data = $stmt->get_result()->fetch_assoc();
   $stmt->close();

   if (!$data) {
      echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan.']);
      return;
   }

   // Ambil obat lain dalam resep yang sama
   $otherMeds = [];
   $stmtOther = $koneksi->prepare("
      SELECT 
         d.id_pharmacy_details,
         d.qty,
         d.signa,
         d.catatan,
         d.harga,
         COALESCE(m.pharmacy_name_generic, d.item_name) AS nama_obat,
         m.pharmacy_unit
      FROM permintaan_pharmacy_details d
      LEFT JOIN ms_pharmacy m ON m.id_pharmacy = d.id_pharmacy
      WHERE d.id_permintaan_farmasi = ?
      ORDER BY d.id_pharmacy_details ASC
   ");
   if ($stmtOther) {
      $stmtOther->bind_param('i', $data['id_permintaan_farmasi']);
      $stmtOther->execute();
      $resOther = $stmtOther->get_result();
      while ($r = $resOther->fetch_assoc()) {
         $otherMeds[] = $r;
      }
      $stmtOther->close();
   }

   $data['other_medicines'] = $otherMeds;

   echo json_encode([
      'status' => 'success',
      'data'   => $data
   ], JSON_UNESCAPED_UNICODE);
}
