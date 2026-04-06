<?php
$title = 'Jadwal Dokter';
require '../../controller/view.php';
?>
<!doctype html>
<html lang="en">

<head>
  <base href="../../">
  <?php
  require '../../assets/template/head.php';
  ?>
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <style>
    /* ================= RESET GLOBAL HOVER (KUNCI UTAMA) ================= */
    .table-scroll table tbody tr:hover td,
    .table-scroll table tbody tr:hover th {
      background: unset !important;
      color: inherit !important;
    }

    /* ================= CONTAINER ================= */
    .table-scroll {
      max-height: 600px;
      overflow: auto;
      border: 1px solid #ddd;
      position: relative;
    }

    /* ================= TABLE ================= */
    .table-scroll #jadwalTable {
      font-size: 12px;
      border-collapse: separate;
      border-spacing: 0;
      width: 100%;
      transform: translateZ(0);
    }

    /* ================= CELL ================= */
    .table-scroll #jadwalTable th,
    .table-scroll #jadwalTable td {
      padding: 6px 8px;
      white-space: nowrap;
      position: relative;
      z-index: 1;
      background: #fff;
    }

    /* ================= HEADER ================= */
    .table-scroll thead th {
      position: sticky;
      top: 0;
      z-index: 5;
      background: #f8f9fa !important;
    }

    /* ================= KOLOM KIRI ================= */
    .table-scroll tbody td:first-child,
    .table-scroll thead th:first-child {
      position: sticky;
      left: 0;
      z-index: 6;
      background: #fff !important;
    }

    /* pojok */
    .table-scroll thead th:first-child {
      z-index: 7;
    }

    /* ================= SLOT (DI PROTECT KERAS) ================= */

    /* 🟢 dokter hadir */
    .table-scroll .jadwal-tersedia {
      background: linear-gradient(135deg, #28a745, #20c997) !important;
      color: #fff !important;
    }

    /* 🔵 dokter + pasien */
    .table-scroll .jadwal-penuh {
      background: linear-gradient(135deg, #0d6efd, #4dabf7) !important;
      color: #fff !important;
    }

    /* tidak praktik */
    .table-scroll td.text-muted {
      background: #f1f3f5 !important;
      color: #999 !important;
    }

    /* ================= CELL CONTENT ================= */
    .jadwal-cell {
      cursor: pointer;
      vertical-align: top;
      text-align: left;
      position: relative;
      z-index: 2;
    }

    /* header "Hadir" */
    .jadwal-cell>div:first-child {
      font-weight: bold;
      font-size: 12px;
      margin-bottom: 4px;
    }

    /* ================= PASIEN ================= */
    .pasien-item {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 11px;
      padding: 4px 6px;
      margin-bottom: 4px;
      border-radius: 6px;

      background: rgba(255, 255, 255, 0.25) !important;
      border-left: 4px solid #fff !important;

      transition: all 0.15s ease;
    }

    .pasien-item:hover {
      background: rgba(255, 255, 255, 0.4) !important;
      transform: translateX(2px);
    }

    .pasien-item .antrian {
      font-weight: bold;
      /* background: #fff !important; */
      color: white !important;
      padding: 2px 6px;
      border-radius: 4px;
      min-width: 22px;
      text-align: center;
    }

    .status-icons {
      display: flex;
      align-items: center;
      gap: 4px;
    }

    .status-icon {
      width: 14px;
      height: 14px;
      object-fit: contain;
      opacity: 0.9;
      transition: 0.2s;
    }

    .status-icon:hover {
      transform: scale(1.2);
    }

    .pasien-item .nama {
      flex: 1;
      text-align: left;
      font-weight: 500;
    }

    /* ================= VARIASI WARNA ================= */
    .warna-0 {
      border-left: 4px solid #ffd43b !important;
    }

    .warna-1 {
      border-left: 4px solid #ff6b6b !important;
    }

    .warna-2 {
      border-left: 4px solid #69db7c !important;
    }

    .warna-3 {
      border-left: 4px solid #4dabf7 !important;
    }

    .warna-4 {
      border-left: 4px solid #b197fc !important;
    }

    /* ================= BUTTON ================= */
    .jadwal-cell .btn {
      font-size: 11px;
      margin-top: 4px;
      text-align: left;
      z-index: 10;
      position: relative;
    }

    /* ================= HOVER CUSTOM (AMAN) ================= */

    /* hanya affect cell NON jadwal */
    .table-scroll tbody tr:hover td:not(.jadwal-cell) {
      background: #f8f9fa !important;
    }

    /* PROTECT jadwal dari hover global */
    .table-scroll tbody tr:hover td.jadwal-cell {
      background: inherit !important;
      color: #fff !important;
    }

    /* efek hover halus (opsional) */
    .jadwal-cell::after {
      content: "";
      position: absolute;
      inset: 0;
      background: transparent;
      transition: 0.2s;
      pointer-events: none;
    }

    .table-scroll tbody tr:hover .jadwal-cell::after {
      background: rgba(255, 255, 255, 0.08);
    }

    /* ================= FIX RENDER ================= */
    .table-scroll * {
      backface-visibility: hidden;
    }
  </style>
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
          <div class="alert alert-primary" role="alert">
            Status Pasien : <div class="d-inline-flex gap-1">
              <span class="badge bg-danger">Pending</span> <span class="badge bg-warning">Confirmed</span><span class="badge bg-secondary">Waiting</span><span class="badge bg-primary">Engaged</span><span class="badge bg-success">Success</span>
            </div>
          </div>
          <div class="row">
            <div class="col-3">
              <ul class="list-group">
                <li class="list-group-item">Data Dokter</li>
                <?php
                $id_customer = $_SESSION['id_customer'];
                $getdoctor = tampildata("SELECT * FROM ms_doctor WHERE doctor_status='1' AND id_customer='$id_customer' ");
                ?>
                <?php foreach ($getdoctor as $doctor): ?>
                  <li class="list-group-item doctor-item"
                    data-id="<?= $doctor['id_doctor'] ?>"
                    data-name="<?= $doctor['doctor_name'] ?>">
                    <?= $doctor['doctor_name'] ?>
                  </li>
                <?php endforeach   ?>
              </ul>
            </div>
            <div class="col-9">
              <div class="col-9 mb-2 d-flex gap-2">
                <input type="date" value="<?php echo date('Y-m-d') ?>" id="datePicker" class="form-control" style="max-width:200px;">
                <button id="btnToday" class="btn btn-primary">Hari Ini</button>
              </div>
              <div class="table-scroll">
                <table class="table table-sm table-bordered table-hover align-middle text-center" id="jadwalTable">
                  <thead>
                    <tr id="headerRow">
                      <th>Jam</th>
                    </tr>
                  </thead>
                  <tbody id="bodyRow"></tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="modal fade" id="modalPasien">
    <div class="modal-dialog">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">Registrasi Pasien</h5>
        </div>

        <div class="modal-body">

          <div class="alert alert-info" id="infoJadwal"></div>

          <form id="formVisit">
            <div class="mb-2">
              <label>Dokter</label>
              <input type="text" id="doctor_name" class="form-control" readonly>
              <input type="hidden" name="id_doctor" id="id_doctor">
            </div>

            <div class="mb-2">
              <label>Tanggal</label>
              <input type="date" name="visit_date" id="visit_date" class="form-control">
            </div>

            <div class="mb-2">
              <label>Jam</label>
              <input type="time" name="visit_time" id="visit_time" class="form-control">
            </div>

            <div class="mb-2">
              <label>Pasien</label>
              <select name="id_patient" id="id_patient"
                class="form-select js-example-basic-item" required>
              </select>
            </div>

          </form>

        </div>

        <div class="modal-footer">
          <button class="btn btn-primary" id="saveVisit">Simpan</button>
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

    let visits = [];
    let schedules = [];
    let selectedDoctor = null;
    let selectedDoctorName = null;
    let currentBaseDate = new Date(); // 🔥 simpan state tanggal

    // ================= 🔥 CLICK DOKTER =================
    $(document).on('click', '.doctor-item', function() {

      $('.doctor-item').removeClass('active');
      $(this).addClass('active');

      selectedDoctor = $(this).data('id');
      selectedDoctorName = $(this).data('name');

      loadSchedule(selectedDoctor);
    });

    $('#modalPasien').on('shown.bs.modal', function() {

      // 🔥 DESTROY dulu kalau sudah ada
      if ($.fn.select2 && $('#id_patient').hasClass("select2-hidden-accessible")) {
        $('#id_patient').select2('destroy');
      }

      // 🔥 INIT ULANG (SELALU FRESH)
      $('#id_patient').select2({
        dropdownParent: $('#modalPasien'),
        width: '100%',
        placeholder: 'Cari pasien...',
        minimumInputLength: 2,
        ajax: {
          url: 'controller/admisi/patientSearchController',
          type: 'GET',
          dataType: 'json',
          delay: 300,
          data: function(params) {
            return {
              search: params.term
            };
          },
          processResults: function(data) {
            return {
              results: data.data.map(item => ({
                id: item.id_patient,
                text: `${item.patient_name} (${item.nomor_rm})`
              }))
            };
          },
          cache: true
        }
      });

    });

    // ================= 🔥 LOAD DATA =================
    function loadSchedule(id_doctor) {

      $("#bodyRow").html(`<tr><td colspan="5">Loading...</td></tr>`);

      let today = new Date();
      let endDate = new Date();
      endDate.setDate(today.getDate() + 3);

      let start = today.toISOString().split('T')[0];
      let end = endDate.toISOString().split('T')[0];

      fetch(`controller/master/scheduleController?id_doctor=${id_doctor}`)
        .then(res => res.json())
        .then(res => {

          schedules = res.data || [];

          return fetch(`controller/visit/visitScheduleController?id_doctor=${id_doctor}&start=${start}&end=${end}`);
        })
        .then(res => res.json())
        .then(res => {

          visits = Array.isArray(res.data) ? res.data : [];

          renderTable(currentBaseDate);
        })
        .catch(err => {
          console.error(err);
        });
    }

    // ================= DEFAULT =================
    let firstDoctor = $('.doctor-item').first();
    if (firstDoctor.length) {
      firstDoctor.addClass('active');
      selectedDoctor = firstDoctor.data('id');
      selectedDoctorName = firstDoctor.data('name');
      loadSchedule(selectedDoctor);
    }

    // ================= HELPER =================
    function getDayName(date) {
      return date.toLocaleDateString('id-ID', {
        weekday: 'long'
      });
    }

    function normalizeDay(day) {
      return day.toLowerCase().replace("'", "").trim();
    }

    function formatTime(t) {
      return t.replace('.', ':');
    }

    function toMinutes(timeStr) {
      let [h, m] = timeStr.split(':').map(Number);
      return h * 60 + m;
    }

    function normalizeTime(t) {
      return t.substring(0, 5);
    }

    function generateTimeSlots() {
      let times = [];
      let current = new Date();
      current.setHours(8, 0, 0);

      let end = new Date();
      end.setHours(23, 59, 0);

      while (current <= end) {
        let hh = String(current.getHours()).padStart(2, '0');
        let mm = String(current.getMinutes()).padStart(2, '0');
        times.push(`${hh}:${mm}`);
        current.setMinutes(current.getMinutes() + 15);
      }

      return times;
    }

    // ================= 🔥 RENDER =================
    function renderTable(baseDate) {

      currentBaseDate = baseDate;

      const headerRow = $("#headerRow");
      const bodyRow = $("#bodyRow");

      headerRow.html(`<th>Jam</th>`);

      let dateObjs = [];

      for (let i = 0; i < 3; i++) {
        let d = new Date(baseDate);
        d.setDate(d.getDate() + i);
        dateObjs.push(d);

        let formatted = d.toLocaleDateString('id-ID', {
          weekday: 'long',
          day: '2-digit',
          month: 'long'
        });

        headerRow.append(`<th>${formatted}</th>`);
      }

      let times = generateTimeSlots();
      let allRows = "";

      times.forEach(time => {

        let row = `<tr><td>${time}</td>`;

        dateObjs.forEach(date => {

          let dayName = getDayName(date);

          let found = schedules.find(s => {
            let start = toMinutes(formatTime(s.start_time));
            let end = toMinutes(formatTime(s.end_time));
            let current = toMinutes(time);

            return (
              normalizeDay(s.day_of_week) === normalizeDay(dayName) &&
              current >= start &&
              current <= end
            );
          });

          if (found) {

            let dateStr = date.toISOString().split('T')[0];

            let visitList = visits.filter(v =>
              v.visit_date === dateStr &&
              normalizeTime(v.visit_time) === time
            );

            let bgClass = "jadwal-tersedia";

            if (visitList.length > 0) {
              bgClass = "jadwal-penuh";
            }
            let pasienHtml = visitList.map(v => {

              // ================= STATUS BADGE ANTRIAN =================
              const statusMap = {
                99: "bg-danger",
                1: "bg-warning",
                2: "bg-secondary",
                3: "bg-primary",
                4: "bg-success"
              };
              let statusClass = statusMap[String(v.visit_status)] || "bg-secondary";
              console.log(v.visit_status, typeof v.visit_status);
              let icareIcon = v.status_icare == 1 ?
                `<span class='badge bg-info'>iCare</span>` :
                '';

              let sehatIcon = v.status_satusehat == 1 ?
                `<span class='badge bg-success'>Satu Sehat</span>` :
                '';

              let antrianBadge = `
                    <span class="antrian badge ${statusClass}">
                      ${v.visit_antrian}
                    </span>
                  `;


              return `
              <div class="pasien-item">
                ${antrianBadge}

                <span class="nama">
                  ${v.patient_name}
                </span>

                <span class="status-icons">
                  ${icareIcon}
                  ${sehatIcon}
                </span>
              </div>
            `;
            }).join('');

            let addButton = `
            <button class="btn btn-light btn-xs w-100 add-patient-btn"
              data-time="${time}"
              data-date="${date.toISOString()}"
              data-doctor="${selectedDoctor}"
              data-doctor-name="${selectedDoctorName}">
              ➕ Tambah Pasien
            </button>
          `;

            row += `
            <td class="jadwal-cell ${bgClass}"
              data-time="${time}"
              data-date="${date.toISOString()}"
              data-doctor="${selectedDoctor}"
              data-doctor-name="${selectedDoctorName}">

              <div><b>Hadir</b></div>
              ${pasienHtml}
              <div class="mt-1">${addButton}</div>

            </td>
          `;
          } else {
            row += `<td class="text-muted">Tidak Praktik</td>`;
          }

        });

        row += `</tr>`;
        allRows += row;
      });

      bodyRow.html(allRows);
    }

    // ================= DATE =================
    $("#datePicker").on("change", function() {
      renderTable(new Date($(this).val()));
    });

    $("#btnToday").on("click", function() {
      renderTable(new Date());
    });

    // ================= 🔥 CLICK CELL =================
    $(document).on('click', '.jadwal-cell', function() {

      let time = $(this).data('time');
      let date = new Date($(this).data('date'));

      openModal(time, date);
    });

    // ================= 🔥 CLICK BUTTON (FIX BUG) =================
    $(document).on('click', '.add-patient-btn', function(e) {
      e.stopPropagation(); // 🔥 penting

      let time = $(this).data('time');
      let date = new Date($(this).data('date'));

      openModal(time, date);
    });

    function openModal(time, date) {

      let formattedDate = date.toLocaleDateString('id-ID', {
        weekday: 'long',
        day: '2-digit',
        month: 'long',
        year: 'numeric'
      });

      $('#visit_date').val(date.toISOString().split('T')[0]);
      $('#visit_time').val(time);
      $('#id_doctor').val(selectedDoctor);
      $('#doctor_name').val(selectedDoctorName);

      $('#infoJadwal').text(`${formattedDate} - ${time}`);

      $('#modalPasien').modal('show');
    }

    // ================= 🔥 SAVE =================
    $('#saveVisit').on('click', function() {

      let form = $('#formVisit')[0];
      let formData = new URLSearchParams(new FormData(form));

      fetch('controller/visit/visitController', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: formData
        })
        .then(res => res.json())
        .then(res => {

          if (res.status === 'success') {

            let newVisit = {
              visit_date: $('#visit_date').val(),
              visit_time: $('#visit_time').val(),
              patient_name: $('#id_patient option:selected').text(),
              visit_antrian: res.data.antrian
            };

            // 🔥 TAMBAH KE STATE (NO REFRESH)
            visits.push(newVisit);

            renderTable(currentBaseDate); // 🔥 langsung update UI

            Swal.fire({
              icon: 'success',
              title: 'Berhasil!',
              text: `Antrian No: ${res.data.antrian}`,
              timer: 1200,
              showConfirmButton: false
            });

            form.reset();
            $('#id_patient').val(null).trigger('change');

            let modal = bootstrap.Modal.getInstance(document.getElementById('modalPasien'));
            modal.hide();

          } else {
            Swal.fire('Gagal', res.message, 'error');
          }
        })
        .catch(err => {
          console.error(err);
          Swal.fire('Error', 'Server error', 'error');
        });

    });

  });
</script>

</html>