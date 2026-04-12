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
                              <h4 class="mb-3">Form Pemeriksaan Medis</h4>
                              <hr>
                              <?php
                              require 'card-pasien.php';
                              ?>

                              <hr>
                              <!-- Pemeriksaan oleh Perawat -->
                              <h5>CPO</h5>

                              <!-- HEADER -->
                              <div class="row mb-3">
                                 <div class="col-md-6">
                                    <label>Ruangan</label>
                                    <input type="text" name="ruangan" class="form-control">
                                 </div>
                                 <div class="col-md-6">
                                    <label>Diagnosa</label>
                                    <input type="text" name="diagnosa" class="form-control">
                                 </div>
                              </div>

                              <!-- TABLE INPUT -->
                              <table class="table table-bordered" id="tableCPO">
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
                                       <th>Aksi</th>
                                    </tr>
                                 </thead>
                                 <tbody></tbody>
                              </table>

                              <button type="button" id="addRow" class="btn btn-light">+ Tambah</button>
                              <button type="submit" class="btn btn-primary">Simpan</button>
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
   $('#addRow').on('click', function() {
      $('#tableCPO tbody').append(`
    <tr>
      <td><input type="date" name="tanggal[]" class="form-control"></td>
      <td><input type="text" name="nama_obat[]" class="form-control"></td>
      <td><input type="text" name="dosis[]" class="form-control"></td>
      <td><input type="text" name="signature[]" class="form-control"></td>
      <td><input type="text" name="jam_pagi[]" class="form-control"></td>
      <td><input type="text" name="jam_siang[]" class="form-control"></td>
      <td><input type="text" name="jam_sore[]" class="form-control"></td>
      <td><input type="text" name="jam_malam[]" class="form-control"></td>
      <td>
        <button type="button" class="btn btn-danger btn-sm remove">X</button>
      </td>
    </tr>
  `);
   });

   // hapus row
   $(document).on('click', '.remove', function() {
      $(this).closest('tr').remove();
   });

   $('#formCPO').on('submit', function(e) {
      e.preventDefault();

      let formData = $(this).serialize();

      $.post('controller/cpo/store.php', formData, function(res) {
         if (res.status === 'success') {
            alert('Berhasil');

            // redirect ke print
            window.open(`module/admin/print/cpo?no=${res.no}&rm=${res.rm}`, '_blank');
         }
      }, 'json');
   });
</script>


</html>