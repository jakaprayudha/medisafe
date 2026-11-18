<?php
// Helper akses master obat per faskes (dipakai pharmacyDetailsController, getPharmacyDocs, uploadPharmacy).
// Obat boleh diakses bila milik faskes sesi, atau master bawaan (id_customer 0) yang belum
// diganti/dihapus faskes tersebut (ms_pharmacy_parrent).

function obatFaskes(mysqli $k, int $idPharmacy, int $idc): ?array
{
   $stmt = $k->prepare(
      "SELECT mp.*, IF(mp.id_customer = 0, 'global', 'faskes') AS sumber
       FROM ms_pharmacy mp
       LEFT JOIN ms_pharmacy_parrent log ON log.parent_id = mp.id_pharmacy AND log.id_customer_real = ?
       WHERE mp.id_pharmacy = ?
         AND (mp.id_customer = ? OR (mp.id_customer = 0 AND log.id IS NULL))
       LIMIT 1"
   );
   if (!$stmt) return null;
   $stmt->bind_param('iii', $idc, $idPharmacy, $idc);
   $stmt->execute();
   $row = $stmt->get_result()->fetch_assoc();
   $stmt->close();
   return $row ?: null;
}
