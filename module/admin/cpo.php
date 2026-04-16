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
                              <h4 class="mb-3">Form CPO</h4>
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
   const petugas = "<?= $_SESSION['fullname'] ?? '' ?>";
   $(document).ready(function() {
      // kosong dulu, nanti dipanggil dari loadCPO
   });
   $('#addRow').on('click', function() {
      let today = new Date().toISOString().split('T')[0];

      $('#tableCPO tbody').append(`
    <tr>
      <td style="min-width:150px">
        <input type="date" name="tanggal[]" value="${today}" class="form-control">
      </td>

      <td style="min-width:250px">
        <input type="text" name="nama_obat[]" class="form-control" placeholder="Nama Obat">
      </td>

      <td style="min-width:150px">
        <input type="text" name="dosis[]" class="form-control" placeholder="Dosis">
      </td>

      <td style="min-width:150px">
        <input type="text" name="signature[]" class="form-control" placeholder="Signa">
      </td>

      <td style="min-width:120px">
        <input type="time" name="jam_pagi[]" class="form-control">
      </td>

      <td style="min-width:120px">
        <input type="time" name="jam_siang[]" class="form-control">
      </td>

      <td style="min-width:120px">
        <input type="time" name="jam_sore[]" class="form-control">
      </td>

      <td style="min-width:120px">
        <input type="time" name="jam_malam[]" class="form-control">
      </td>

      <td style="min-width:120px">
         <input type="text" value="${petugas}" class="form-control" readonly>
      </td>

      <td style="min-width:80px; text-align:center;">
        <button type="button" class="btn btn-danger btn-sm remove">X</button>
      </td>
    </tr>
  `);
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
      $.get('controller/visit/getCPO.php', {
         visit_ID: visitID
      }, function(res) {

         if (res.status === "success") {

            res.data.forEach(row => {

               $('#tableCPO tbody').append(`
          <tr style="background:#f8f9fa;">
            
            <td>
              <input type="date" value="${row.tanggal}" class="form-control" disabled>
            </td>

            <td>
              <input type="text" value="${row.nama_item}" class="form-control" disabled>
            </td>

            <td>
              <input type="text" value="${row.dosis}" class="form-control" disabled>
            </td>

            <td>
              <input type="text" value="${row.signature}" class="form-control" disabled>
            </td>

            <td>
              <input type="time" value="${row.jam_pagi}" class="form-control" disabled>
            </td>

            <td>
              <input type="time" value="${row.jam_siang}" class="form-control" disabled>
            </td>

            <td>
              <input type="time" value="${row.jam_sore}" class="form-control" disabled>
            </td>

            <td>
              <input type="time" value="${row.jam_malam}" class="form-control" disabled>
            </td>

            <td>
               <input type="text" value="${row.petugas}" class="form-control" disabled>
            </td>

            <td class="text-center">
              <span class="badge bg-success">Tersimpan</span>
            </td>

          </tr>
        `);

            });

            // 🔥 tambah row baru setelah data lama
            $('#addRow').click();
         }
      }, 'json');
   }
</script>

</html>