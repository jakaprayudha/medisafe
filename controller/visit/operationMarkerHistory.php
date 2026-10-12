<?php
require_once __DIR__ . '/operationMarkerStore.php';

function operationMarkerHistoryHtml(array $documents, string $rm): string
{
   if (!$documents) return '';
   $escape = static fn($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
   ob_start();
   ?>
   <section class="operation-history border rounded p-3 mt-3">
      <h6 class="fw-semibold">Laporan / Penanda Operasi <span class="badge bg-primary"><?= count($documents) ?> dokumen</span></h6>
      <p class="small text-muted">Dokumen penanda lokasi tindakan tersimpan. Tidak menggantikan laporan operasi lengkap.</p>
      <?php foreach ($documents as $index => $document):
         $imageUrl = 'controller/visit/operationMarkerImage.php?' . http_build_query([
            'visit_ID' => $document['visit_ID'], 'id' => $document['id']
         ]);
         $pdfUrl = 'module/admin/print/formulir_penanda_operasi?' . http_build_query([
            'no' => $document['visit_ID'], 'rm' => $rm, 'id' => $document['id']
         ]);
         ?>
         <article class="operation-history-document border rounded p-3 mb-3">
            <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
               <div>
                  <strong><?= $escape(operationMarkerTypes()[$document['marker_type']] ?? $document['marker_type']) ?></strong>
                  <span class="small text-muted">Versi <?= count($documents) - $index ?> / Dokumen #<?= $escape($document['id']) ?></span>
                  <div class="small text-muted"><?= $escape($document['created_at']) ?> WIB - <?= $escape($document['created_by']) ?></div>
               </div>
               <a href="<?= $escape($pdfUrl) ?>" class="btn btn-sm btn-outline-primary align-self-start" target="_blank" rel="noopener">Cetak / PDF</a>
            </div>
            <div class="small mb-2"><strong>Catatan:</strong><br><?= nl2br($escape($document['notes'] !== '' ? $document['notes'] : '-')) ?></div>
            <?php if (!empty($document['has_image'])): ?>
               <div class="operation-history-picture text-center">
                  <img src="<?= $escape($imageUrl) ?>" class="img-fluid border rounded"
                     loading="lazy" alt="Penanda operasi <?= $escape(operationMarkerTypes()[$document['marker_type']] ?? $document['marker_type']) ?>"
                     style="max-height: 450px; object-fit: contain;">
                  <div class="alert alert-danger mt-2 d-none operation-history-image-error" role="alert">
                     Gagal memuat gambar penanda operasi. Silakan muat ulang halaman.
                  </div>
                  <div class="mt-2"><a href="<?= $escape($imageUrl) ?>" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener">Lihat Gambar Besar</a></div>
               </div>
            <?php endif; ?>
         </article>
      <?php endforeach; ?>
   </section>
   <?php
   return ob_get_clean();
}
