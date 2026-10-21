<?php
$title = 'Pemeriksaan Generalis Tubuh';
require '../../controller/view.php';
require_once '../../controller/ranap/generalisTubuhService.php';

$visitId = trim((string) ($_GET['no'] ?? ''));
$nomorRm = trim((string) ($_GET['rm'] ?? ''));
$customerId = (int) ($_SESSION['id_customer'] ?? 0);
$patient = null;
$pageError = '';

if ($visitId === '' || $nomorRm === '' || $customerId <= 0) {
   $pageError = 'Parameter kunjungan tidak lengkap.';
} else {
   $stmt = $koneksi->prepare("
      SELECT p.patient_name, p.nomor_rm, v.visit_ID, v.visit_date,
             v.pemeriksaan_fisik
      FROM pasien_visit v
      INNER JOIN ms_patient p ON p.id_patient = v.id_patient
      WHERE v.visit_ID = ? AND p.nomor_rm = ? AND v.id_customer = ?
      LIMIT 1
   ");
   if (!$stmt) {
      $pageError = 'Gagal menyiapkan data kunjungan.';
   } else {
      $stmt->bind_param('ssi', $visitId, $nomorRm, $customerId);
      $stmt->execute();
      $patient = $stmt->get_result()->fetch_assoc();
      $stmt->close();
      if (!$patient) $pageError = 'Kunjungan tidak ditemukan.';
   }
}

$values = generalisTubuhValues($patient['pemeriksaan_fisik'] ?? '');
$e = static function ($value) {
   return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
?>
<!doctype html>
<html lang="id">
<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .generalis-group {
      height: 100%;
      border: 1px solid #e5e7eb;
      border-left: 4px solid #c85c5c;
      border-radius: 6px;
      padding: 18px;
      background: #fff;
    }
    .generalis-group h5 {
      color: #b44f4f;
      font-size: 1rem;
      font-weight: 700;
      margin-bottom: 16px;
    }
    .generalis-field {
      display: grid;
      grid-template-columns: minmax(120px, 0.8fr) minmax(0, 1.7fr);
      align-items: start;
      gap: 12px;
      padding: 8px 0;
      border-bottom: 1px solid #f0f1f3;
    }
    .generalis-field:last-child { border-bottom: 0; }
    .generalis-field > span { font-weight: 600; }
    .generalis-options { display: flex; flex-wrap: wrap; gap: 8px 16px; }
    .generalis-options .form-check { margin: 0; white-space: nowrap; }
    @media (max-width: 576px) {
      .generalis-field { grid-template-columns: 1fr; gap: 6px; }
    }
  </style>
</head>
<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
    <?php require 'sidebar.php'; ?>
    <div class="body-wrapper">
      <?php require 'navbar.php'; ?>
      <div class="body-wrapper-inner">
        <div class="container-fluid">
          <?php if ($visitId !== '' && $nomorRm !== '') require 'menu_rme_inap.php'; ?>

          <?php if ($pageError !== ''): ?>
            <div class="alert alert-danger" role="alert"><?= $e($pageError) ?></div>
          <?php else: ?>
            <div class="card border shadow-none">
              <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
                  <div>
                    <h4 class="fw-bold mb-1">Pemeriksaan Generalis Tubuh</h4>
                    <div class="text-muted">
                      <?= $e($patient['patient_name']) ?>
                      <span class="badge bg-light text-dark border ms-1">RM <?= $e($patient['nomor_rm']) ?></span>
                      <span class="badge bg-light text-dark border ms-1">Kunjungan <?= $e($patient['visit_ID']) ?></span>
                    </div>
                  </div>
                  <a class="btn btn-light border" href="module/admin/rme_inap?no=<?= urlencode($visitId) ?>&amp;rm=<?= urlencode($nomorRm) ?>&amp;rme=c">
                    <i class="ti ti-arrow-left me-1"></i>Pemeriksaan Medis
                  </a>
                </div>

                <form id="generalisForm">
                  <input type="hidden" name="visit_ID" value="<?= $e($visitId) ?>">
                  <input type="hidden" name="nomor_rm" value="<?= $e($nomorRm) ?>">
                  <div class="row g-3">
                    <?php foreach (generalisTubuhFields() as $group => $fields): ?>
                      <div class="col-xl-6 col-12">
                        <section class="generalis-group" aria-label="Pemeriksaan <?= $e($group) ?>">
                          <h5><?= $e($group) ?></h5>
                          <?php foreach ($fields as $key => $field): ?>
                            <div class="generalis-field">
                              <span><?= $e($field['label']) ?></span>
                              <div class="generalis-options">
                                <?php foreach ($field['options'] as $index => $option):
                                   $inputId = $key . '_' . $index;
                                ?>
                                  <label class="form-check form-check-inline" for="<?= $e($inputId) ?>">
                                    <input class="form-check-input" type="radio" name="<?= $e($key) ?>" id="<?= $e($inputId) ?>" value="<?= $e($option) ?>" <?= ($values[$key] ?? '') === $option ? 'checked' : '' ?>>
                                    <span class="form-check-label"><?= $e($option) ?></span>
                                  </label>
                                <?php endforeach; ?>
                              </div>
                            </div>
                          <?php endforeach; ?>
                        </section>
                      </div>
                    <?php endforeach; ?>
                  </div>

                  <div class="d-flex justify-content-end gap-2 mt-4">
                    <button type="reset" class="btn btn-light border"><i class="ti ti-rotate-clockwise me-1"></i>Reset Pilihan</button>
                    <button type="submit" class="btn btn-primary" id="saveGeneralis"><i class="ti ti-device-floppy me-1"></i>Simpan Pemeriksaan</button>
                  </div>
                </form>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <?php require 'library.php'; ?>
  <script>
    $('#generalisForm').on('submit', function(event) {
      event.preventDefault();
      const form = this;
      const fields = {};
      new FormData(form).forEach((value, key) => {
        if (key !== 'visit_ID' && key !== 'nomor_rm') fields[key] = value;
      });
      const button = $('#saveGeneralis');
      button.prop('disabled', true);

      fetch('controller/ranap/saveGeneralisTubuh.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          visit_ID: form.elements.visit_ID.value,
          nomor_rm: form.elements.nomor_rm.value,
          fields: fields
        })
      })
      .then(async response => {
        const data = await response.json();
        if (!response.ok || data.status !== 'success') throw new Error(data.message || 'Pemeriksaan gagal disimpan.');
        return data;
      })
      .then(data => Swal.fire('Tersimpan', data.message, 'success'))
      .catch(error => Swal.fire('Gagal', error.message || 'Gagal terhubung ke server.', 'error'))
      .finally(() => button.prop('disabled', false));
    });
  </script>
</body>
</html>
