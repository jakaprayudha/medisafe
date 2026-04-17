<?php
$title = 'Pemeriksaan Rawat Inap';
require '../../controller/view.php';
require '../../database/connect.php';
require '../../controller/visit/assesmen.php';
$no = $_GET['no'];
$rm = $_GET['rm'];
$check = mysqli_query($koneksi, "SELECT * FROM pasien_visit LEFT JOIN ms_patient ON ms_patient.id_patient = pasien_visit.id_patient WHERE pasien_visit.visit_ID='$no' AND ms_patient.nomor_rm='$rm'");
$data = mysqli_fetch_array($check);

// Hitung usia jika data ditemukan
// if ($data) {
//   $tanggal_lahir = new DateTime($data['patient_datebirth']);
//   $tanggal_visit = new DateTime($data['visit_date']);

//   $usia = $tanggal_lahir->diff($tanggal_visit);
// }

?>
<!doctype html>
<html lang="en">

<head>
   <base href="../../">
   <?php
   require '../../assets/template/head.php';
   ?>
</head>

<body>
   <!--  Body Wrapper -->
   <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
      data-sidebar-position="fixed" data-header-position="fixed">
      <!-- Sidebar Start -->
      <?php
      require 'sidebar.php';
      ?>
      <!--  Sidebar End -->
      <!--  Main wrapper -->
      <div class="body-wrapper">
         <!--  Header Start -->
         <?php
         require 'navbar.php';
         ?>
         <!--  Header End -->
         <div class="body-wrapper-inner">
            <div class="container-fluid">
               <div class="row">
                  <?php
                  require 'menu_rme_inap.php';
                  ?>
                  <div class="col-lg-12 d-flex align-items-stretch">
                     <div class="card w-100">
                        <div class="card-body p-4">
                           <form id="formCPO" class="p-4 border rounded shadow-sm" method="POST">
                              <input type="hidden" name="nomor_rm" value="<?= $rm ?>">
                              <input type="hidden" name="nomor_visit" value="<?= $no ?>">
                              <input type="hidden" name="id_patient" id="id_patient" value="<?= $data['id_patient'] ?>" hidden>
                              <div class="d-flex justify-content-between align-items-center mb-3">
                                 <h4 class="mb-0">Form CPO</h4>

                                 <a href="module/admin/print/formulir_cpo?no=<?= $no ?>&rm=<?= $rm ?>"
                                    target="_blank"
                                    class="btn btn-light">
                                    🖨 Cetak
                                 </a>
                              </div>
                              <hr>
                              <?php
                              require 'card-pasien.php';
                              ?>

                              <hr>
                              <!-- Pemeriksaan oleh Perawat -->
                              <h5>CPO</h5>


                              <!-- TABLE INPUT -->
                              <div class="table-responsive" style="overflow-x:auto;">
                                 <table class="table table-bordered" id="tableCPO" style="min-width:1200px;">
                                    <thead>
                                       <tr>
                                          <th>Tanggal</th>
                                          <th>Nama Obat</th>
                                          <th>Dosis</th>
                                          <th>Sign</th>
                                          <th>Pagi</th>
                                          <th>Siang</th>
                                          <th>Sore</th>
                                          <th>Malam</th>
                                          <th>Petugas</th>
                                          <th>Aksi</th>
                                       </tr>
                                    </thead>
                                    <tbody></tbody>
                                 </table>
                                 <button type="button" id="addRow" class="btn btn-light">+ Tambah</button>
                                 <button type="submit" class="btn btn-primary">Simpan</button>
                              </div>
                           </form>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>

   <?php
   require 'library.php';
   ?>
</body>

<script>
   $(document).ready(function() {
      // kosong dulu, nanti dipanggil dari loadCPO
   });

   $('#addRow').on('click', function() {

      let today = new Date().toISOString().split('T')[0];

      let html = `
   <tr>
      <td style="min-width:150px">
         <input type="date" name="tanggal[]" value="${today}" class="form-control">
      </td>

      <td style="min-width:250px">
         <input type="text" name="nama_obat[]" class="form-control">
      </td>

      <td style="min-width:150px">
         <input type="text" name="dosis[]" class="form-control">
      </td>

      <td style="min-width:150px">
         <input type="text" name="signature[]" class="form-control">
      </td>

      <td style="min-width:120px"><input type="time" name="jam_pagi[]" class="form-control"></td>
      <td style="min-width:120px"><input type="time" name="jam_siang[]" class="form-control"></td>
      <td style="min-width:120px"><input type="time" name="jam_sore[]" class="form-control"></td>
      <td style="min-width:120px"><input type="time" name="jam_malam[]" class="form-control"></td>

      <td style="min-width:150px">
         <select name="petugas[]" class="form-control petugas-select"></select>
      </td>

      <td style="min-width:80px; text-align:center;">
         <button type="button" class="btn btn-danger btn-sm remove">X</button>
      </td>
   </tr>
   `;

      $('#tableCPO tbody').append(html);

      // 🔥 ambil select terakhir (tanpa ubah struktur)
      let select = $('#tableCPO tbody tr:last .petugas-select');

      loadPetugas(select);
   });

   $('#formCPO').on('submit', function(e) {
      e.preventDefault();

      let formData = $(this).serialize();

      $.ajax({
         url: 'controller/visit/cpoController.php',
         type: 'POST',
         data: formData,
         dataType: 'json',
         success: function(res) {
            if (res.status === 'success') {
               Swal.fire({
                  icon: 'success',
                  title: 'Berhasil',
                  text: 'Data CPO tersimpan'
               });

               // 🔥 RESET INPUT BARU
               $('#tableCPO tbody tr').not('[style]').remove();
               // artinya: hapus row input (bukan yang disabled)

               // 🔥 LOAD ULANG DATA DARI DB
               loadCPO();
            } else {
               Swal.fire('Error', res.message, 'error');
            }
         },
         error: function(xhr) {
            console.log(xhr.responseText);
            Swal.fire('Error', 'Server error', 'error');
         }
      });
   });
</script>
<script>
   const visitID = $("input[name='nomor_visit']").val();

   $(document).ready(function() {
      loadCPO();
   });

   function loadCPO() {
      $('#tableCPO tbody').html('');
      $.get('controller/visit/getCpo.php', {
         visit_ID: visitID
      }, function(res) {

         if (res.status === "success") {

            res.data.forEach(row => {

               let tr = $(`
   <tr style="background:#f8f9fa;" data-id="${row.id}">
      
      <td><input type="date" value="${row.tanggal}" class="form-control" disabled></td>
      <td><input type="text" value="${row.nama_item}" class="form-control" disabled></td>
      <td><input type="text" value="${row.dosis}" class="form-control" disabled></td>
      <td><input type="text" value="${row.signature}" class="form-control" disabled></td>

      <td><input type="time" value="${row.jam_pagi}" class="form-control" disabled></td>
      <td><input type="time" value="${row.jam_siang}" class="form-control" disabled></td>
      <td><input type="time" value="${row.jam_sore}" class="form-control" disabled></td>
      <td><input type="time" value="${row.jam_malam}" class="form-control" disabled></td>

      <td>
         <select class="form-control petugas-select" disabled></select>
      </td>

      <td class="text-center">
         <button type="button" class="btn btn-warning btn-sm btn-edit">Edit</button>
         <button type="button" class="btn btn-success btn-sm btn-save d-none">Save</button>
      </td>

   </tr>
   `);

               $('#tableCPO tbody').append(tr);

               // 🔥 load petugas
               let select = tr.find('.petugas-select');
               loadPetugas(select, row.id_user);

            });

            // 🔥 tambah row baru setelah data lama
            $('#addRow').click();
         }
      }, 'json');
   }

   $(document).on('click', '.btn-update', function() {

      let row = $(this).closest('tr');

      let id = row.data('id');

      let data = {
         id: id,
         tanggal: row.find("input[name='tanggal_existing[]']").val(),
         nama_obat: row.find("input[name='nama_obat_existing[]']").val(),
         dosis: row.find("input[name='dosis_existing[]']").val(),
         signature: row.find("input[name='signature_existing[]']").val(),
         jam_pagi: row.find("input[name='jam_pagi_existing[]']").val(),
         jam_siang: row.find("input[name='jam_siang_existing[]']").val(),
         jam_sore: row.find("input[name='jam_sore_existing[]']").val(),
         jam_malam: row.find("input[name='jam_malam_existing[]']").val()
      };

      $.ajax({
         url: 'controller/visit/updateCPO.php',
         type: 'POST',
         data: data,
         dataType: 'json',
         success: function(res) {
            if (res.status === 'success') {
               Swal.fire('Success', 'Data berhasil diupdate', 'success');
            } else {
               Swal.fire('Error', res.message, 'error');
            }
         }
      });

   });

   // row.find("input").on("change", function() {
   //    row.css("background", "#fff3cd"); // kuning
   // });

   $(document).on("change", "#tableCPO input, #tableCPO select", function() {
      let tr = $(this).closest("tr");
      // 🔥 hanya jalan kalau memang sedang edit
      if (tr.attr("data-editing") === "true") {
         tr.css("background", "#fff3cd");
      }

   });

   function loadPetugas(selectElement, selected = null) {
      $.get('controller/master/user.php', function(res) {

         if (res.status === 'success') {

            let option = '<option value="">-- Pilih Petugas --</option>';

            res.data.forEach(u => {
               option += `<option value="${u.id_user}">
               ${u.fullname} [${u.roles}]
            </option>`;
            });

            selectElement.html(option);

            if (selected) {
               selectElement.val(selected);
            }
         }

      }, 'json');
   }

   $(document).on('click', '.btn-edit', function() {
      let tr = $(this).closest('tr');
      tr.attr("data-editing", "true"); // 🔥 FLAG
      tr.find('input, select').prop('disabled', false);
      tr.css("background", "#fff3cd");
      $(this).addClass('d-none');
      tr.find('.btn-save').removeClass('d-none');
   });
   $(document).on('click', '.btn-save', function() {

      let tr = $(this).closest('tr');

      let data = {
         id: tr.data('id'),
         tanggal: tr.find("input[type='date']").val(),
         nama_obat: tr.find("input[type='text']").eq(0).val(),
         dosis: tr.find("input[type='text']").eq(1).val(),
         signature: tr.find("input[type='text']").eq(2).val(),
         jam_pagi: tr.find("input[type='time']").eq(0).val(),
         jam_siang: tr.find("input[type='time']").eq(1).val(),
         jam_sore: tr.find("input[type='time']").eq(2).val(),
         jam_malam: tr.find("input[type='time']").eq(3).val(),
         id_user: tr.find("select").val()
      };

      $.post('controller/visit/updateCPO.php', data, function(res) {

         if (res.status === 'success') {

            Swal.fire('Success', 'Data berhasil diupdate', 'success');

            tr.find('input, select').prop('disabled', true);

            tr.removeAttr("data-editing"); // 🔥 penting

            tr.find('.btn-save').addClass('d-none');
            tr.find('.btn-edit').removeClass('d-none');

            tr.css("background", "#f8f9fa"); // balik normal

         }

      }, 'json');
   });
</script>

</html>