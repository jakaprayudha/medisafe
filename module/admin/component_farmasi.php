  <div class="card w-100">
    <div class="card-body p-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="card-title fw-semibold">Data Item Farmasi</h5>
        <!-- Grup tombol di sisi kanan -->
        <div class="d-flex ms-auto gap-2">
          <a href="module/admin/print/print_all_resep?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>" target="_blank">
            <button class="btn btn-light"><i class="fas fa-print"></i> Print</button>
          </a>
          <button class="btn btn-primary" id="btnTambah"><i class="fas fa-plus"></i> Tambah</button>
        </div>
      </div>
      <div class="alert alert-danger d-flex align-items-center mb-4 peringatanAlergi d-none" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <div>
          <strong>Peringatan Alergi:</strong><span id="desAplergi"></span>.
        </div>
      </div>
      <div class="table-responsive" data-simplebar>
        <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTableFarmasi">
          <thead>
            <tr>
              <th class="text-dark fw-normal">Tanggal</th>
              <th class="text-dark fw-normal">Tipe Obat</th>
              <th scope="col" class="text-dark fw-normal">Catatan</th>
              <th scope="col" class="text-dark fw-normal text-center">Status</th>
              <th scope="col" class="text-dark fw-normal text-center">Actions</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>