<div class="card shadow-sm border-0 mb-3" id="patientCard">
   <div class="card-body">
      <div class="d-flex align-items-center justify-content-between">
         <div class="d-flex align-items-center gap-3">
            <div class="avatar-circle bg-primary text-white">
               <span id="pc_initial">-</span>
            </div>
            <div>
               <h5 class="mb-0 fw-semibold" id="pc_name">-</h5>
               <small class="text-muted" id="pc_rm">No RM: -</small>
            </div>
         </div>
         <div>
            <span id="logobpjs" class="d-none"><img src="assets/icon/bpjs.png" width="30" height="30"></span>
            <span id="pc_status" class="badge bg-secondary">-</span>
         </div>
      </div>
      <hr class="my-3">
      <div class="row text-sm">
         <div class="col-md-2">
            <div class="text-muted">Gender</div>
            <div class="fw-semibold" id="pc_gender">-</div>
         </div>
         <div class="col-md-2">
            <div class="text-muted">Dokter</div>
            <div class="fw-semibold" id="pc_dokter">-</div>
         </div>
         <div class="col-md-2">
            <div class="text-muted">Pembayaran</div>
            <div class="fw-semibold" id="pc_provider">-</div>
         </div>
         <div class="col-md-2">
            <div class="text-muted">BPJS</div>
            <div class="fw-semibold" id="nomor_bpjs">-</div>
         </div>
         <div class="col-md-2">
            <div class="text-muted">NIK</div>
            <div class="fw-semibold" id="nomor_nik">-</div>
         </div>
         <div class="col-md-2">
            <div class="text-muted">Umur</div>
            <div class="fw-semibold"><span id="idUmur"></span></div>
         </div>
      </div>
   </div>
</div>
<style>
   .avatar-circle {
      width: 45px;
      height: 45px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      font-size: 18px;
   }
</style>

<script>
   // ===============================
   // HELPER SAFE SET
   // ===============================
   function setText(id, value) {
      const el = document.getElementById(id);
      if (el) {
         el.innerText = value || "-";
      }
   }

   // ===============================
   // RENDER FUNCTION (ANTI ERROR)
   // ===============================
   function renderPatientCard(data) {

      if (!data) return;

      const name = (data.patient_name_pcare || "-").trim();

      setText("pc_name", name);
      setText("pc_initial", name.charAt(0).toUpperCase());
      setText("pc_rm", "No RM: " + (data.nomor_rm || "-"));
      setText("pc_gender", data.patient_gender);
      setText("pc_dokter", data.id_doctor);
      setText("pc_provider", data.provider_name);

      let statusText = "Unknown";
      let statusClass = "bg-secondary";

      switch (parseInt(data.visit_status)) {
         case 0:
            statusText = "Perawatan";
            statusClass = "bg-primary";
            break;
         case 1:
            statusText = "Pulang";
            statusClass = "bg-success";
            break;
      }

      const statusEl = document.getElementById("pc_status");
      if (statusEl) {
         statusEl.className = "badge " + statusClass;
         statusEl.innerText = statusText;
      }
   }

   // ===============================
   // AUTO LOAD (ANTI DOM ISSUE)
   // ===============================
   document.addEventListener("DOMContentLoaded", () => {

      const url = new URLSearchParams(window.location.search);
      const no = url.get("no");

      if (!no) return;

      fetch(`controller/master/getPatientCard.php?no=${no}`)
         .then(res => res.json())
         .then(res => {

            console.log("PATIENT DATA:", res); // 🔥 debug

            if (res.status === "success") {

               // 🔥 pastikan DOM sudah siap
               requestAnimationFrame(() => {
                  renderPatientCard(res.data);
               });

            }
         })
         .catch(err => console.error("PatientCard Error:", err));

   });
</script>