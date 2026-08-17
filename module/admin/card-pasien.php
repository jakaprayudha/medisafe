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
            <span id="logosatusehat" class="d-none"><img src="assets/icon/icon-satusehat.png" width="40" height="40"></span>
            <span id="logobpjs" class="d-none"><img src="assets/icon/bpjs.png" width="30" height="30"></span>
            <span id="pc_status" class="badge bg-secondary">-</span>
         </div>
      </div>
      <hr class="my-3">
      <div class="patient-info-grid mt-3">

         <div class="patient-info-item">
            <div class="info-icon bg-primary-subtle text-primary">
               <i class="fas fa-venus-mars"></i>
            </div>

            <div class="info-content">
               <div class="info-label">Gender</div>
               <div class="info-value" id="pc_gender">-</div>
            </div>
         </div>

         <div class="patient-info-item">
            <div class="info-icon bg-danger-subtle text-danger">
               <i class="fas fa-cake-candles"></i>
            </div>

            <div class="info-content">
               <div class="info-label">Tanggal Lahir</div>
               <div class="info-value" id="pc_birth_date">-</div>
            </div>
         </div>

         <div class="patient-info-item">
            <div class="info-icon bg-warning-subtle text-warning">
               <i class="fas fa-hourglass-half"></i>
            </div>

            <div class="info-content">
               <div class="info-label">Umur</div>
               <div class="info-value" id="idUmur">-</div>
            </div>
         </div>

         <div class="patient-info-item">
            <div class="info-icon bg-success-subtle text-success">
               <i class="fas fa-user-doctor"></i>
            </div>

            <div class="info-content">
               <div class="info-label">Dokter</div>
               <div class="info-value" id="pc_dokter">-</div>
            </div>
         </div>

         <div class="patient-info-item">
            <div class="info-icon bg-info-subtle text-info">
               <i class="fas fa-wallet"></i>
            </div>

            <div class="info-content">
               <div class="info-label">Pembayaran</div>
               <div class="info-value" id="pc_provider">-</div>
            </div>
         </div>

         <div class="patient-info-item">
            <div class="info-icon bg-secondary-subtle text-secondary">
               <i class="fas fa-id-card"></i>
            </div>

            <div class="info-content">
               <div class="info-label">No BPJS</div>
               <div class="info-value" id="patient_bpjs">-</div>
            </div>
         </div>

         <div class="patient-info-item">
            <div class="info-icon bg-dark-subtle text-dark">
               <i class="fas fa-address-card"></i>
            </div>

            <div class="info-content">
               <div class="info-label">NIK</div>
               <div class="info-value" id="patient_nik">-</div>
            </div>
         </div>

         <div class="patient-info-item">
            <div class="info-icon bg-info-subtle text-info">
               <i class="fas fa-file-medical"></i>
            </div>

            <div class="info-content">
               <div class="info-label">PRB</div>
               <div class="info-value" id="patient_prb">-</div>
            </div>
         </div>

         <div class="patient-info-item">
            <div class="info-icon bg-danger-subtle text-danger">
               <i class="fas fa-notes-medical"></i>
            </div>

            <div class="info-content">
               <div class="info-label">Prolanis</div>
               <div class="info-value" id="patient_prolanis">-</div>
            </div>
         </div>
      </div>
   </div>
</div>
<style>
      #patientCard {
         border-radius: 18px;
         overflow: hidden;
         border: none;
         background: linear-gradient(to bottom, #ffffff, #f8fafc);
      }

      #patientCard .card-body {
         padding: 16px 18px;
      }

      /* =========================
      AVATAR
   ========================= */
      .avatar-circle {
         width: 46px;
         height: 46px;
         border-radius: 14px;
         display: flex;
         align-items: center;
         justify-content: center;
         font-weight: 700;
         font-size: 18px;
         background: linear-gradient(135deg, #4f46e5, #6366f1);
         box-shadow: 0 6px 18px rgba(99, 102, 241, .18);
         flex-shrink: 0;
      }

      /* =========================
      TITLE
   ========================= */
      #pc_name {
         font-size: 22px;
         font-weight: 700;
         color: #0f172a;
         line-height: 1.1;
         margin-bottom: 2px;
      }

      #pc_rm {
         font-size: 12px;
         color: #64748b !important;
      }

      /* =========================
      GRID
   ========================= */
      .patient-info-grid {
         display: grid;
         grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
         gap: 10px;
      }

      /* =========================
      ITEM
   ========================= */
      .patient-info-item {
         display: flex;
         align-items: center;
         gap: 10px;
         background: #fff;
         border: 1px solid #eef2f7;
         border-radius: 14px;
         padding: 10px 12px;
         transition: all .2s ease;
         min-height: 62px;
      }

      .patient-info-item:hover {
         transform: translateY(-1px);
         border-color: #dbeafe;
         box-shadow: 0 6px 16px rgba(15, 23, 42, .05);
      }

      /* =========================
      ICON
   ========================= */
      .info-icon {
         width: 38px;
         height: 38px;
         border-radius: 12px;
         display: flex;
         align-items: center;
         justify-content: center;
         font-size: 14px;
         flex-shrink: 0;
      }

      /* =========================
      TEXT
   ========================= */
      .info-content {
         flex: 1;
         min-width: 0;
      }

      .info-label {
         font-size: 10px;
         color: #64748b;
         margin-bottom: 1px;
         text-transform: uppercase;
         letter-spacing: .3px;
      }

      .info-value {
         font-size: 13px;
         font-weight: 700;
         color: #0f172a;
         line-height: 1.25;
         word-break: break-word;
      }

      /* =========================
      STATUS BADGE
   ========================= */
      #pc_status {
         font-size: 11px;
         padding: 6px 10px;
         border-radius: 10px;
         font-weight: 600;
      }

      /* =========================
      MOBILE
   ========================= */
      @media (max-width: 768px) {

         #pc_name {
            font-size: 18px;
         }

         .patient-info-grid {
            grid-template-columns: 1fr 1fr;
         }

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
      setText("patient_prb", safeVal(data.SPRB));
      setText("patient_prolanis", safeVal(data.Sprolanis));
      // BPJS
      if (data.patient_bpjs && data.patient_bpjs !== "null") {
         setText("patient_bpjs", data.patient_bpjs);
      }

      // NIK
      if (data.patient_nik && data.patient_nik !== "null") {
         setText("patient_nik", data.patient_nik);
      }


      let birthDate = safeVal(data.patient_datebirth);

      if (birthDate !== '-') {

         const d = new Date(birthDate);

         birthDate = d.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
         });
      }

      setText("pc_birth_date", birthDate);

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

      const logosatusehat = document.getElementById("logosatusehat");

      if (logosatusehat) {

         if (data.idsh && data.idsh !== "null") {

            logosatusehat.classList.remove("d-none");

         } else {

            logosatusehat.classList.add("d-none");

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