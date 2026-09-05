<div class="modal fade" id="programModal" tabindex="-1" aria-labelledby="programModalLabel" aria-hidden="true">

   <div class="modal-dialog modal-dialog-centered">

      <form id="programForm" class="modal-content">

         <div class="modal-header">

            <h5 class="modal-title" id="programModalLabel">
               Permintaan Farmasi
            </h5>

            <button
               type="button"
               class="btn-close"
               data-bs-dismiss="modal"
               aria-label="Close">
            </button>

         </div>

         <div class="modal-body">

            <input
               type="hidden"
               name="id_permintaan_farmasi"
               id="id_permintaan_farmasi">

            <input
               type="hidden"
               name="id_visit"
               id="id_visit"
               value="<?= $_GET['no'] ?>">

            <div class="row">

               <!-- INFO -->
               <div class="col-12">

                  <div
                     class="alert alert-primary d-flex align-items-start"
                     role="alert">

                     <i class="fas fa-info-circle me-2 mt-1"></i>

                     <div>
                        Buat Tiket Order Permintaan Farmasi
                        Sebelum Membuat Isi Rincian Obat
                        yang akan dibuat.
                     </div>

                  </div>

               </div>

               <!-- TIPE OBAT -->
               <div class="col-12">

                  <div class="mb-3">

                     <label
                        for="tipe_obat"
                        class="form-label required">
                        Tipe Obat
                     </label>

                     <select
                        class="form-select"
                        id="tipe_obat"
                        name="tipe_obat"
                        required>

                        <option value="">PILIH</option>

                        <option value="Racikan">
                           Racikan
                        </option>

                        <option value="Non Racikan">
                           Non Racikan
                        </option>

                     </select>

                  </div>

               </div>

               <!-- RACIKAN : JUMLAH -->
               <div class="col-6 racikan-field">

                  <div class="mb-3">

                     <label
                        for="rck_jumlah"
                        class="form-label">
                        Jumlah
                     </label>

                     <input
                        type="number"
                        name="rck_jumlah"
                        id="rck_jumlah"
                        class="form-control"
                        min="1">

                  </div>

               </div>

               <!-- RACIKAN : SATUAN -->
               <div class="col-6 racikan-field">

                  <div class="mb-3">

                     <label
                        for="rck_satuan"
                        class="form-label">
                        Satuan
                     </label>

                     <input
                        type="text"
                        id="rck_satuan"
                        name="rck_satuan"
                        class="form-control">

                  </div>

               </div>

               <!-- RACIKAN : SIGNA -->
               <div class="col-12 racikan-field">

                  <div class="mb-3">

                     <label
                        for="rck_signa"
                        class="form-label">
                        Signa
                     </label>

                     <input
                        type="text"
                        name="rck_signa"
                        id="rck_signa"
                        class="form-control">

                  </div>

               </div>

               <!-- CATATAN -->
               <div class="col-12">

                  <div class="mb-3">

                     <label
                        for="catatan_permintaan"
                        class="form-label">
                        Catatan
                     </label>

                     <textarea
                        name="catatan_permintaan"
                        id="catatan_permintaan"
                        class="form-control"
                        rows="5"></textarea>

                  </div>

               </div>

            </div>

         </div>

         <div class="modal-footer">

            <button
               type="button"
               class="btn btn-light"
               data-bs-dismiss="modal">

               Batal

            </button>

            <button
               type="submit"
               class="btn btn-primary">

               <i class="fas fa-save me-1"></i>
               Simpan

            </button>

         </div>

      </form>

   </div>

</div>


<script>
   $(document).ready(function() {

      /*
      |--------------------------------------------------------------------------
      | INITIALIZE SELECT2
      |--------------------------------------------------------------------------
      */

      function initTipeObat() {

         const $select = $('#tipe_obat');

         if (!$select.length) {
            return;
         }

         /*
         |--------------------------------------------------------------------------
         | HAPUS SELECT2 LAMA
         |--------------------------------------------------------------------------
         */

         if ($select.hasClass('select2-hidden-accessible')) {

            $select.select2('destroy');

         }

         /*
         |--------------------------------------------------------------------------
         | INITIALIZE SELECT2
         |--------------------------------------------------------------------------
         */

         $select.select2({

            dropdownParent: $('#programModal'),

            width: '100%',

            placeholder: 'Pilih Tipe Obat',

            allowClear: true

         });

      }


      /*
      |--------------------------------------------------------------------------
      | INIT SAAT DOCUMENT READY
      |--------------------------------------------------------------------------
      */

      initTipeObat();


      /*
      |--------------------------------------------------------------------------
      | INIT SAAT MODAL DIBUKA
      |--------------------------------------------------------------------------
      */

      $('#programModal').on('shown.bs.modal', function() {

         initTipeObat();

         $('#tipe_obat').trigger('change');

      });


      /*
      |--------------------------------------------------------------------------
      | CHANGE TIPE OBAT
      |--------------------------------------------------------------------------
      */

      $(document).on('change', '#tipe_obat', function() {

         const tipe = $(this).val();

         console.log('Tipe obat:', tipe);

         if (typeof toggleRacikan === 'function') {

            toggleRacikan();

         }

      });


      /*
      |--------------------------------------------------------------------------
      | RESET SAAT MODAL DITUTUP
      |--------------------------------------------------------------------------
      */

      $('#programModal').on('hidden.bs.modal', function() {

         /*
         | Jangan reset seluruh form jika form ini digunakan
         | untuk mode edit.
         |
         | Hanya reset ketika memang diperlukan.
         */

         $('#tipe_obat').val('').trigger('change');

      });

   });
</script>
<script>
   $(document).ready(function() {
      $('#id_pharmacy').select2({
         dropdownParent: $('#programModal'),
         width: '100%'
      });

      $('#id_pharmacy').on('change', function() {
         let harga = $(this).find(':selected').data('harga') || 0;
         $('#harga').val(harga);
      });
   });
</script>

<script>
   const apiUrl = 'controller/visit/permintaanFarmasi?no=<?= $_GET['no'] ?>';
   const urlParams = new URLSearchParams(window.location.search);
   const rmeParam = urlParams.get('rme') || 'c'; // default kalau kosong
   const nomorm = urlParams.get('rm') || 'c'; // default kalau kosong

   $(document).ready(function() {
      var table = $('#periodeTableFarmasi').DataTable({
         processing: true,
         serverSide: false, // 🔹 ubah jadi false
         ajax: {
            url: apiUrl,
            type: "GET",
            dataSrc: function(json) {
               return json.data.map(function(row) {
                  return {
                     "actions": (function() {

                        let btnDelete = '';

                        // ❌ kalau selesai → tidak ada delete
                        if (row.status_permintaan != 3) {
                           btnDelete = `
      <a class="btn btn-danger delete-btn" 
         href="javascript:;" 
         data-id="${row.id_permintaan_farmasi}">
        <i class="fas fa-trash"></i>
      </a>
              `;
                        }

                        return `
    <div class="text-center">
      <div class="btn-group btn-group-sm" role="group">
        
        <a class="btn btn-info" 
          href="module/admin/permintaan_farmasi_details?no=${urlParams.get('no')}&rm=${nomorm}&rme=${rmeParam}&id=${row.id_permintaan_farmasi}">
          <i class="fas fa-pencil"></i>
        </a>

        ${btnDelete}

      </div>
    </div>
  `;

                     })(),
                     "timestamp": row.created_at ?? "-",
                     "tipe_obat": row.tipe_obat ?? "-",
                     "catatan": row.catatan_permintaan ?? "-",
                     "status_permintaan": (function() {
                        let status = row.status_permintaan;

                        let badgeClass = '';
                        let label = '';

                        if (status == 1) {
                           badgeClass = 'bg-warning';
                           label = 'Sudah Kirim';
                        } else if (status == 2) {
                           badgeClass = 'bg-primary';
                           label = 'Persiapan';
                        } else if (status == 3) {
                           badgeClass = 'bg-success';
                           label = 'Selesai';
                        } else {
                           badgeClass = 'bg-dark';
                           label = 'Belum Dikirim';
                        }

                        return `<span class="badge ${badgeClass} d-block text-center">${label}</span>`;
                     })()
                  };
               });
            }
         },
         columns: [{
               data: "timestamp"
            },
            {
               data: "tipe_obat"
            },
            {
               data: "catatan"
            },
            {
               data: "status_permintaan"
            },
            {
               data: "actions",
               orderable: false,
               searchable: false
            },
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
      $('#btnTambah').on('click', function() {
         $('#programForm')[0].reset(); // ✅ pakai programForm, bukan addForm
         $('#id_permintaan_farmasi').val('');
         $('#programModal .modal-title').text('Tambah Data');
         $('#programModal').modal('show');
      });

      // 🔹 Submit (Tambah / Update)
      $('#programForm').on('submit', function(e) {
         e.preventDefault();
         let formData = new URLSearchParams(new FormData(this));
         let id = $('#id_permintaan_farmasi').val();

         fetch(apiUrl + (id ? `?id=${id}` : ''), {
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
                  $('#programModal').modal('hide');
                  table.ajax.reload(null, false);
               } else {
                  Swal.fire('Gagal!', data.message, 'error');
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
               fetch(apiUrl + `&id=${id}`, {
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
<script>
   $(document).ready(function() {

      function toggleRacikan() {
         let tipe = $('#tipe_obat').val();

         if (tipe === 'Racikan') {
            $('.racikan-field').show();
         } else {
            $('.racikan-field').hide();
            $('.racikan-field input').val(''); // reset value
         }
      }

      // event change
      $(document).on('change', '#tipe_obat', function() {
         toggleRacikan();
      });

      // initial load
      toggleRacikan();

   });
</script>
<script>
   $(document).ready(function() {
      const params = new URLSearchParams(window.location.search);
      const nomor_visit = params.get('no');
      $.ajax({
         url: 'controller/master/getAlergiObat.php',
         type: 'POST',
         data: {
            visit_id: nomor_visit
         },
         dataType: 'json',
         success: function(response) {
            const desAlObat = response.data?.desAlObat;
            if (response.status === 'success' && desAlObat) {
               $('.peringatanAlergi').removeClass('d-none');
               $('#desAplergi').text(desAlObat);
            } else {
               $('.peringatanAlergi').addClass('d-none');
               $('#desAplergi').text('');
            }
         },
      });
   })
</script>