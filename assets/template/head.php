<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Medisafe | <?= $title ?>
</title>
<link rel="shortcut icon" type="image/png" href="assets/images/logos/favicon.png" />
<link rel="stylesheet" href="assets/css/styles.min.css" />
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">
<!-- Tambahkan Font Awesome CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<!-- CSS Select2 -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/css/select2.min.css" rel="stylesheet" />
<!-- <script src="assets/js/sweet-alert/sweetalert.min.js"></script> -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- jQuery (wajib untuk Select2) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


<style>
   .dataTables_wrapper {
      width: 100% !important;
   }

   table.dataTable {
      width: 100% !important;
   }
</style>
<style>
   /* Biar Select2 sama tinggi dengan form input lainnya */
   .select2-container--default .select2-selection--single {
      height: calc(2.25rem + 2px);
      /* sama dengan Bootstrap's .form-control */
      padding: 0.375rem 0.75rem;
      border: 1px solid #ced4da;
      border-radius: 0.375rem;
   }

   .select2-container--default .select2-selection--single .select2-selection__rendered {
      line-height: normal;
   }

   .select2-container--default .select2-selection--single .select2-selection__arrow {
      height: 100%;
      top: 0.3rem;
      right: 0.75rem;
   }

   /* Pastikan lebarnya 100% agar sama dengan form input lainnya */
   .select2-container {
      width: 100% !important;
   }

   /* styling untuk label yang required */
   .form-label.required::after {
      content: " *";
      color: red;
   }
</style>
<script type="text/javascript">
   var koneksiinternet = 0;
   setInterval(function() {
      if (koneksiinternet == 0 && navigator.onLine == 0) {
         koneksiinternet = 1
         Swal.fire({
            title: "Offline",
            text: "Koneksi Terputus. Periksa Sambungan Internet Anda",
            icon: "info",
            showCancelButton: false,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Ok"
         }).then((result) => {
            koneksiinternet = 0;
         });
      }
   }, 5000);
</script>

<style>
   /* Biar select2 di modal Bootstrap selalu full */
   .select2-container {
      width: 100% !important;
   }

   .select2-selection {
      height: calc(2.25rem + 2px) !important;
      /* biar sejajar sama form-control bootstrap */
      padding: 0.375rem 0.75rem !important;
      font-size: 1rem;
      border: 1px solid #ced4da !important;
      border-radius: 0.375rem !important;
   }
</style>