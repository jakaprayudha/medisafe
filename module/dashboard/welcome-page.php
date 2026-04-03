<style>
   .alert-welcome {
      border-left: 5px solid #0d6efd;
      border-radius: 10px;
   }

   .master-status span {
      display: inline-block;
      margin-right: 10px;
      font-size: 14px;
   }

   .badge-status {
      font-size: 12px;
   }
</style>
<div class="container mt-4">

   <!-- ALERT WELCOME -->
   <div id="welcomeAlert" class="alert alert-welcome bg-white shadow-sm p-4 d-flex justify-content-between align-items-start">

      <div>
         <p class="mb-3 text-dark">
            Untuk administrator, lengkapi data master terlebih dahulu supaya semua fitur bisa digunakan secara maksimal 🚀 dan apabila ada kendala melengkapi informasi master data bisa hubungi admin di group yang telah disediakan.
         </p>
      </div>

      <!-- CLOSE BUTTON -->
      <button type="button" class="btn-close" onclick="closeAlert()"></button>
   </div>

</div>