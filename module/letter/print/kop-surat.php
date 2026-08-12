 <!-- =========================
             KOP SURAT
        ========================== -->
 <?php
  require '../../../database/connect.php';
  $idcustomer =  $_SESSION['id_customer'];
  $checkClinic = mysqli_query($koneksi, "SELECT * FROM setting_clinic LEFT JOIN ms_faskes ON ms_faskes.id_clinic = setting_clinic.id  WHERE setting_clinic.id_customer='$idcustomer'");
  $dataClinic = mysqli_fetch_array($checkClinic);
  ?>
 <div class="kop">
   <div class="kop-logo">
     <img src="../../../uploads/<?= $dataClinic['image_clinic'] ?>" alt="Logo Klinik" />
   </div>

   <div class="kop-content">

     <div class="nama-instansi">
       PEMERINTAH KABUPATEN DELI SERDANG
     </div>


     <div class="nama-klinik">
       <?= htmlspecialchars(
          !empty($dataClinic['clinic_name'])
            ? $dataClinic['clinic_name']
            : 'Clinic Medisafe'
        ) ?>
     </div>


     <div class="alamat">

       <?= htmlspecialchars(
          !empty($dataClinic['faskes_address'])
            ? $dataClinic['faskes_address']
            : 'Jl. Deli Tua'
        ) ?>,

       <?= htmlspecialchars(
          !empty($dataClinic['faskes_city'])
            ? $dataClinic['faskes_city']
            : 'Deli Serdang'
        ) ?>

       -

       <?= htmlspecialchars(
          !empty($dataClinic['faskes_prov'])
            ? $dataClinic['faskes_prov']
            : 'Sumatera Utara'
        ) ?>

     </div>


     <div class="kontak">

       Telp.

       <?= htmlspecialchars(
          !empty($dataClinic['pic_phone'])
            ? $dataClinic['pic_phone']
            : '0821-6652-4717'
        ) ?>

       &nbsp; | &nbsp;

       Email:

       <?= htmlspecialchars(
          !empty($dataClinic['pic_email'])
            ? $dataClinic['pic_email']
            : 'info@medisafe.id'
        ) ?>

     </div>

   </div>

   <div class="kop-empty"></div>
 </div>