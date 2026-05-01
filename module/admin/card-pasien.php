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
            <div class="fw-semibold" id="patient_bpjs">-</div>
         </div>
         <div class="col-md-2">
            <div class="text-muted">NIK</div>
            <div class="fw-semibold" id="patient_nik">-</div>
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
   window._patientRendered = false;

   function safeVal(val) {
      return val && val !== "null" ? val : "-";
   }

   function setIfNotNull(id, val) {
      if (val && val !== "null") {
         document.getElementById(id).innerText = val;
      }
   }

   // ===============================
   // HELPER SAFE SET
   // ===============================
   function setText(id, value) {
      const el = document.getElementById(id);
      if (el) {
         el.innerText = value || "-";
      }
   }

   function hitungUmur(tglLahir) {

      if (!tglLahir) return null;

      // 🔥 convert DD-MM-YYYY → YYYY-MM-DD
      if (tglLahir.includes("-")) {
         let parts = tglLahir.split("-");
         if (parts[0].length === 2) {
            tglLahir = `${parts[2]}-${parts[1]}-${parts[0]}`;
         }
      }

      const birth = new Date(tglLahir);

      if (isNaN(birth.getTime())) return null;

      const today = new Date();

      let tahun = today.getFullYear() - birth.getFullYear();
      let bulan = today.getMonth() - birth.getMonth();

      if (bulan < 0 || (bulan === 0 && today.getDate() < birth.getDate())) {
         tahun--;
         bulan += 12;
      }

      return `${tahun} th ${bulan} bln`;
   }
   // ===============================
   // RENDER FUNCTION (ANTI ERROR)
   // ===============================
   function renderPatientCard(data) {

      if (!data) return;

      // 🔥 JANGAN overwrite kalau sudah pernah render dan data kosong
      if (window._patientRendered) {

         if (!data.patient_bpjs && !data.patient_nik && !data.patient_birth_date) {
            console.log("⛔ Skip overwrite (data kosong)");
            return;
         }
      }

      console.log("✅ RENDER EXECUTE:", data);

      window._patientRendered = true;

      const name = safeVal(data.patient_name_pcare);

      setText("pc_name", name);
      setText("pc_initial", name !== "-" ? name.charAt(0).toUpperCase() : "-");
      setText("pc_rm", "No RM: " + safeVal(data.nomor_rm));
      setText("pc_gender", safeVal(data.patient_gender));
      setText("pc_dokter", safeVal(data.id_doctor));
      setText("pc_provider", safeVal(data.provider_name));
      // BPJS
      if (data.patient_bpjs && data.patient_bpjs !== "null") {
         setText("patient_bpjs", data.patient_bpjs);
      }

      // NIK
      if (data.patient_nik && data.patient_nik !== "null") {
         setText("patient_nik", data.patient_nik);
      }

      // ===============================
      // 🔥 STATUS
      // ===============================
      let statusText = "Unknown";
      let statusClass = "bg-light";

      switch (parseInt(data.visit_status)) {
         case 0:
            statusText = "Perawatan";
            statusClass = "bg-primary";
            break;
         case 1:
            statusText = "Pemeriksan";
            statusClass = "bg-warning";
            break;
         case 4:
            statusText = "Pulang";
            statusClass = "bg-success";
            break;
      }

      const statusEl = document.getElementById("pc_status");
      if (statusEl) {
         statusEl.className = "badge " + statusClass;
         statusEl.innerText = statusText;
      }
      const umurEl = document.getElementById("idUmur");

      // reset dulu
      umurEl.innerHTML = "";

      // ambil data lama kalau ada
      let currentDob = data.patient_datebirth;

      // kalau null → jangan overwrite
      if (!currentDob || currentDob === "null") {
         currentDob = window._lastDob || null;
      } else {
         window._lastDob = currentDob; // simpan
      }

      const umur = hitungUmur(currentDob);

      if (!umur || umur === "null") {
         umurEl.innerHTML = `
      <span class="badge border border-danger text-danger">
         Tgl lahir belum diisi
      </span>
   `;
      } else {
         umurEl.innerText = umur;
      }

      // ===============================
      // 🔥 LOGO BPJS (optional)
      // ===============================
      const logo = document.getElementById("logobpjs");
      if (logo) {
         if (data.provider_name && data.provider_name.toLowerCase().includes("bpjs")) {
            logo.classList.remove("d-none");
         } else {
            logo.classList.add("d-none");
         }
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