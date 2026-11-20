<?php
/**
 * TIDAK DIPAKAI LAGI. Dulu dipakai module/admin/monitoring-visit: membaca faskes dari ?id_customer= tanpa cek role
 * dan mengirim seluruh kunjungan + NIK pasien. Diganti controller/dashboard/rmeMonitoringController.php.
 */
header('Content-Type: application/json; charset=utf-8');
http_response_code(410);
echo json_encode(['status' => 'error', 'message' => 'Endpoint ini sudah tidak dipakai. Gunakan controller/dashboard/rmeMonitoringController.php.']);
