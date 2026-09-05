  <div class="card w-100">
    <div class="card-body p-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="card-title fw-semibold">Data Resep Luar</h5>
        <!-- Grup tombol di sisi kanan -->
        <div class="d-flex ms-auto gap-2">
          <a href="module/admin/print_resep_luar?no=<?= $no ?>&rm=<?= $rm ?>" target="_blank">
            <button class="btn btn-info"><i class="fas fa-print"></i> Cetak</button>
          </a>
          <button class="btn btn-primary" id="btnTambahResep"><i class="fas fa-plus"></i> Tambah</button>
        </div>
      </div>
      <div class="table-responsive" data-simplebar>
        <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTableResep">
          <thead>
            <tr>
              <th class="text-dark fw-normal">Prescriptio</th>
              <th scope="col" class="text-dark fw-normal">Signatura</th>
              <th scope="col" class="text-dark fw-normal">Subscriptio</th>
              <th>Pro</th>
              <th scope="col" class="text-dark fw-normal text-center">Actions</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="modal fade" id="programModalResep" tabindex="-1">
    <div class="modal-dialog">
      <form id="programForm" class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_resep" id="id_resep">
          <input type="hidden" name="id_visit" id="id_visit" value="<?= $_GET['no'] ?>">
          <div class="row">
            <div class="col-12">
              <div class="mb-3">
                <label for="prescriptio" class="form-label">
                  Prescriptio <span class="text-danger">*</span>
                </label>
                <textarea name="prescriptio" class="form-control" id="prescriptio" rows="3"></textarea>
                <small class="form-text text-muted">
                  <strong>Praescriptio</strong> → inti resep, berisi nama obat dan jumlahnya.<br>
                  Contoh: <em>Tab. Paracetamol 500 mg No. X</em>
                </small>
              </div>
            </div>

            <div class="col-12">
              <div class="mb-3">
                <label for="signatura" class="form-label">
                  Signatura (Sig.) <span class="text-danger">*</span>
                </label>
                <textarea name="signatura" class="form-control" id="signatura" rows="3"></textarea>
                <small class="form-text text-muted">
                  <strong>Signatura</strong> → petunjuk pemakaian obat untuk pasien.<br>
                  Contoh: <em>S. 3 dd tab I (minum 1 tablet, 3 kali sehari)</em>
                </small>
              </div>
            </div>

            <div class="col-12">
              <div class="mb-3">
                <label for="subscriptio" class="form-label">
                  Subscriptio <span class="text-danger">*</span>
                </label>
                <textarea name="subscriptio" class="form-control" id="subscriptio" rows="3"></textarea>
                <small class="form-text text-muted">
                  <strong>Subscriptio</strong> → catatan tambahan atau perintah khusus untuk apoteker.<br>
                  Contoh: <em>M.f. pulv. (buat serbuk), M.f. sol. (buat larutan)</em>
                </small>
              </div>
            </div>

            <div class="col-12">
              <div class="mb-3">
                <label for="pro" class="form-label">
                  Pro <span class="text-danger">*</span>
                </label>
                <textarea name="pro" class="form-control" id="pro" rows="3"></textarea>
                <small class="form-text text-muted">
                  <strong>Pro</strong> → keterangan untuk siapa obat ditujukan.<br>
                  Contoh: <em>pro infante (untuk anak)</em>
                </small>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
  <script>
    const apiUrlResep = 'controller/visit/resepLuar?no=<?= $_GET['no'] ?>';

    $(document).ready(function() {
      var table = $('#periodeTableResep').DataTable({
        processing: true,
        serverSide: false, // 🔹 ubah jadi false
        ajax: {
          url: apiUrlResep,
          type: "GET",
          dataSrc: function(json) {
            return json.data.map(function(row) {
              return {
                "actions": `
                      <div class="text-center">
								<div class="btn-group btn-group-sm" role="group">
									<a class="btn btn-warning edit-btn" href="javascript:;" data-id="${row.id_resep}">
											<i class="fas fa-edit"></i>
									</a>
									<a class="btn btn-danger delete-btn" href="javascript:;" data-id="${row.id_resep}">
											<i class="fas fa-trash"></i>
									</a>
								</div>
							</div>
                    `,
                "prescriptio": row.prescriptio ?? "-",
                "signatura": row.signatura ?? "-",
                "subscriptio": row.subscriptio ?? "-",
                "pro": row.pro ?? "-"
              };
            });
          }
        },
        columns: [{
            data: "prescriptio",
            className: "text-wrap"
          },
          {
            data: "signatura",
            className: "text-wrap"
          },
          {
            data: "subscriptio",
            className: "text-wrap"
          },
          {
            data: "pro",
            className: "text-wrap"
          },
          {
            data: "actions",
            orderable: false,
            searchable: false
          }
        ],
        footerCallback: function(row, data, start, end, display) {
          var api = this.api();

          // Hitung total bobot
          let total = api
            .column(3, {
              page: 'current'
            })
            .data()
            .reduce((a, b) => {
              return (parseFloat(a) || 0) + (parseFloat(b) || 0);
            }, 0);

          // Tampilkan di footer
          $(api.column(3).footer()).html(total.toFixed(2) + " %");
        }
      });

      $('#customSearch').on('keyup', function() {
        table.search(this.value).draw();
      });

      // 🔹 Tambah
      $('#btnTambahResep').on('click', function() {
        $('#programForm')[0].reset(); // ✅ pakai programForm, bukan addForm
        $('#id_resep').val('');
        $('#programModalResep .modal-title').text('Tambah Data');
        $('#programModalResep').modal('show');
      });

      // 🔹 Submit (Tambah / Update)
      $('#programForm').on('submit', function(e) {
        e.preventDefault();
        let formData = new URLSearchParams(new FormData(this));
        let id = $('#id_resep').val();

        fetch(apiUrlResep + (id ? `?id=${id}` : ''), {
            method: id ? 'PUT' : 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: formData
          })
          .then(res => res.json())
          .then(data => {
            if (data.status === 'success') {
              Swal.fire('Berhasil!', data.message, 'success');
              $('#programModalResep').modal('hide');
              table.ajax.reload(null, false);
            } else {
              Swal.fire('Gagal!', data.message, 'error');
            }
          });
      });
      // 🔹 Edit
      $(document).on('click', '.edit-btn', function() {
        let id = $(this).data('id');
        fetch(apiUrlResep + `&id=${id}`)
          .then(res => res.json())
          .then(resp => {
            if (resp.status === 'success') {
              let d = resp.data;

              // isi otomatis berdasarkan name field
              for (let key in d) {
                $(`[name="${key}"]`).val(d[key]);
              }

              $('#programModalResep .modal-title').text('Edit Data');
              $('#programModalResep').modal('show');
            }
          });
      });
      // 🔹 Delete
      $(document).on('click', '.delete-btn', function() {
        let id = $(this).data('id');
        Swal.fire({
          title: 'Hapus Data?',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Hapus',
          cancelButtonText: 'Batal'
        }).then((result) => {
          if (result.isConfirmed) {
            fetch(apiUrlResep + `&id=${id}`, {
                method: 'DELETE'
              })
              .then(res => res.json())
              .then(data => {
                if (data.status === 'success') {
                  Swal.fire('Berhasil!', 'Data dihapus.', 'success');
                  table.ajax.reload(null, false);
                }
              });
          }
        });
      });
    });
  </script>