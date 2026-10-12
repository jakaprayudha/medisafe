<?php
require_once __DIR__ . '/../../../database/connect.php';
require_once __DIR__ . '/../../../controller/visit/operationMarkerPrint.php';

$markerVisit = $_GET['no'] ?? '';
$markerId = !empty($operationMarkerBundle) ? null : ($_GET['id'] ?? null);
if (!is_string($markerVisit) || $markerVisit === '' ||
    ($markerId !== null && (!filter_var($markerId, FILTER_VALIDATE_INT) || (int) $markerId < 1))) {
   http_response_code(400);
   exit('Parameter dokumen penanda operasi tidak valid.');
}
try {
   $markerPatient = operationMarkerVisit($koneksi, $markerVisit, (int) $_SESSION['id_customer']);
   if (!$markerPatient) {
      http_response_code(404);
      exit('Kunjungan tidak ditemukan pada klinik ini.');
   }
   $markerDocuments = operationMarkerDocuments($koneksi, $markerVisit, (int) $_SESSION['id_customer'],
      $markerId === null ? null : (int) $markerId);
   if (!$markerDocuments) {
      http_response_code(404);
      exit('Belum ada dokumen penanda operasi tersimpan.');
   }
   $markerHtml = operationMarkerHtml($markerPatient, $markerDocuments);
   if (!empty($operationMarkerBundle)) {
      echo $markerHtml;
      return;
   }
   require_once __DIR__ . '/../../../vendor/autoload.php';
   $markerOptions = new \Dompdf\Options();
   $markerOptions->set('isRemoteEnabled', false);
   $markerOptions->set('defaultFont', 'DejaVu Sans');
   $markerPdf = new \Dompdf\Dompdf($markerOptions);
   $markerPdf->loadHtml($markerHtml);
   $markerPdf->setPaper('A4', 'portrait');
   $markerPdf->render();
   $markerPdf->stream('penanda-operasi.pdf', ['Attachment' => false]);
} catch (Exception $error) {
   error_log('Operation marker PDF: ' . $error->getMessage());
   http_response_code(500);
   exit('Gagal membuat PDF penanda operasi. Silakan hubungi administrator.');
}
