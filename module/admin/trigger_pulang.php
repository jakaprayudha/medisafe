<button id="btnSelesai" class="nav-link text-success">
   <i class="fas fa-check-circle me-2"></i> Selesaikan Pemeriksaan
</button>
<script>
   document.getElementById("btnSelesai").addEventListener("click", function() {

      const url = new URLSearchParams(window.location.search);
      const visit_ID = url.get("no");

      Swal.fire({
         title: "Selesaikan Pemeriksaan?",
         text: "Pasien akan dianggap selesai diperiksa.",
         icon: "warning",
         showCancelButton: true,
         confirmButtonText: "Ya, Selesaikan",
         cancelButtonText: "Batal"
      }).then((result) => {

         if (result.isConfirmed) {

            fetch("controller/visit/selesaikanPemeriksaan.php", {
                  method: "POST",
                  headers: {
                     "Content-Type": "application/json"
                  },
                  body: JSON.stringify({
                     visit_ID: visit_ID
                  })
               })
               .then(res => res.json())
               .then(res => {

                  Swal.fire({
                     icon: res.status,
                     title: res.status === "success" ? "Berhasil" : "Gagal",
                     text: res.message
                  });

                  if (res.status === "success") {
                     setTimeout(() => {
                        window.location.href = "module/admin/index"; // redirect opsional
                     }, 1500);
                  }

               })
               .catch(err => {
                  console.error(err);
                  Swal.fire("Error", "Gagal koneksi ke server", "error");
               });

         }

      });

   });
</script>