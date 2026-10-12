<?php
require_once __DIR__ . '/operationMarkerStore.php';

function operationMarkerHtml(array $patient, array $documents): string
{
   $escape = static fn($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
   ob_start();
   ?>
   <!doctype html>
   <html lang="id">
   <head>
      <meta charset="utf-8">
      <title>Penanda Operasi</title>
      <style>
         @page { size: A4; margin: 15mm; }
         body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #111; }
         .operation-marker-document { page-break-after: always; }
         .operation-marker-document:last-child { page-break-after: auto; }
         h2 { text-align: center; font-size: 15pt; margin: 0 0 15px; }
         table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
         td { padding: 3px; vertical-align: top; }
         .marker-picture { text-align: center; margin-bottom: 12px; }
         .marker-picture img { max-width: 100%; max-height: 650px; }
         .marker-notes { white-space: pre-wrap; overflow-wrap: break-word; }
         .marker-disclaimer { font-size: 8pt; margin-top: 12px; }
      </style>
   </head>
   <body>
   <?php foreach ($documents as $document): ?>
      <section class="operation-marker-document">
         <h2>RME - Penanda Operasi</h2>
         <table>
            <tr><td>Nama pasien</td><td><?= $escape($patient['patient_name']) ?></td></tr>
            <tr><td>No. RM / Kunjungan</td><td><?= $escape($patient['nomor_rm']) ?> / <?= $escape($patient['visit_ID']) ?></td></tr>
            <tr><td>Jenis gambar / ID dokumen</td><td><?= $escape(operationMarkerTypes()[$document['marker_type']]) ?> / <?= $escape($document['id']) ?></td></tr>
            <tr><td>Dicatat pada</td><td><?= $escape($document['created_at']) ?> WIB</td></tr>
            <tr><td>Pencatat</td><td><?= $escape($document['created_by']) ?></td></tr>
         </table>
         <div class="marker-picture">
            <img src="data:image/png;base64,<?= base64_encode($document['image_png']) ?>" alt="Penanda operasi tersimpan">
         </div>
         <strong>Catatan operasi / keterangan penanda:</strong>
         <div class="marker-notes"><?= nl2br($escape($document['notes'])) ?></div>
         <div class="marker-disclaimer">Dokumen penanda lokasi tindakan. Tidak menggantikan laporan operasi dan persetujuan tindakan.</div>
      </section>
   <?php endforeach; ?>
   </body>
   </html>
   <?php
   return ob_get_clean();
}
