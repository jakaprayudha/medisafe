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
    .table-scroll {
      max-height: 600px;
      /* tinggi area scroll */
      overflow-y: auto;
      /* scroll vertical */
      overflow-x: auto;
      /* scroll horizontal */
      border: 1px solid #ddd;
    }

    #jadwalTable {
      font-size: 12px;
    }

    #jadwalTable th,
    #jadwalTable td {
      padding: 4px 6px;
      white-space: nowrap;
    }

    /* biar header tetap di atas saat scroll */
    .table-scroll thead th {
      position: sticky;
      top: 0;
      background: #fff;
      z-index: 2;
    }

    /* kolom jam tetap di kiri */
    .table-scroll tbody td:first-child,
    .table-scroll thead th:first-child {
      position: sticky;
      left: 0;
      background: #fff;
      z-index: 3;
    }

    .doctor-item.active {
      background: #0d6efd;
      color: white;
      font-weight: bold;
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
          <div class="row">
            <div class="col-3">
              <ul class="list-group">
                <li class="list-group-item">Data Dokter</li>
                <?php
                $getdoctor = tampildata("SELECT * FROM ms_doctor WHERE doctor_status='1'");
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
                <input type="date" id="datePicker" class="form-control" style="max-width:200px;">
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

    // ================= 🔥 CLICK DOKTER =================
    $(document).on('click', '.doctor-item', function() {

      $('.doctor-item').removeClass('active');
      $(this).addClass('active');

      selectedDoctor = $(this).data('id');
      selectedDoctorName = $(this).data('name');

      loadSchedule(selectedDoctor);
    });

    // ================= 🔥 LOAD SCHEDULE =================
    function loadSchedule(id_doctor) {

      let today = new Date();
      let endDate = new Date();
      endDate.setDate(today.getDate() + 3);

      let start = today.toISOString().split('T')[0];
      let end = endDate.toISOString().split('T')[0];

      fetch(`controller/master/scheduleController?id_doctor=${id_doctor}`)
        .then(res => res.json())
        .then(res => {
          if (res.status === 'success') {
            schedules = res.data;

            // 🔥 TAMBAHAN (AMBIL VISIT)
            fetch(`controller/visit/visitScheduleController?id_doctor=${id_doctor}&start=${start}&end=${end}`)
              .then(res => res.json())
              .then(res => {
                visits = Array.isArray(res.data) ? res.data : [];
                renderTable(new Date());
              });

          }
        });
    }

    // ================= DEFAULT LOAD =================
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

    // ================= RENDER TABLE =================
    function renderTable(baseDate) {

      const headerRow = $("#headerRow");
      const bodyRow = $("#bodyRow");

      headerRow.html(`<th>Jam</th>`);
      bodyRow.html("");

      let dateObjs = [];

      // HEADER
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

      // JAM
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

      let times = generateTimeSlots();

      // RENDER
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

            // 🔥 FILTER PASIEN
            let visitList = [];

            if (Array.isArray(visits)) {
              visitList = visits.filter(v => {
                return (
                  v.visit_date === dateStr &&
                  normalizeTime(v.visit_time) === time
                );
              });
            }

            // 🔥 WARNA DINAMIS
            let bg = "bg-success"; // default kosong

            if (visitList.length > 0) {
              bg = "bg-primary"; // ada pasien
            }

            // 🔥 LIST PASIEN
            let pasienHtml = "";

            if (visitList.length > 0) {
              pasienHtml = visitList.map(v => `
    <div style="font-size:11px; text-align:left; padding:2px 0;">
      <b>${v.visit_antrian}.</b> ${v.patient_name}
    </div>
  `).join('');
            }

            // 🔥 BUTTON TAMBAH
            let addButton = `
  <div style="margin-top:4px;">
    <button class="btn btn-light btn-xs w-100 add-patient-btn"
      data-time="${time}"
      data-date="${date.toISOString()}"
      data-doctor="${selectedDoctor}"
      data-doctor-name="${selectedDoctorName}">
      ➕ Tambah
    </button>
  </div>
`;

            row += `
  <td class="${bg} text-white jadwal-cell"
    data-time="${time}"
    data-date="${date.toISOString()}"
    data-doctor="${selectedDoctor}"
    data-doctor-name="${selectedDoctorName}"
    style="cursor:pointer; vertical-align:top;">

    <div style="font-size:12px"><b>Hadir</b></div>

    ${pasienHtml}

    ${addButton}

  </td>
`;
          } else {
            row += `<td>Tidak Praktik</td>`;
          }

        });

        row += `</tr>`;
        bodyRow.append(row);
      });
    }

    // ================= DATE PICKER =================
    $("#datePicker").on("change", function() {
      let selected = new Date($(this).val());
      renderTable(selected);
    });

    $("#btnToday").on("click", function() {
      renderTable(new Date());
    });

    // ================= CLICK CELL =================
    $(document).on('click', '.jadwal-cell', function() {

      let time = $(this).data('time');
      let date = new Date($(this).data('date'));
      let doctorId = $(this).data('doctor');
      let doctorName = $(this).data('doctor-name');

      let formattedDate = date.toLocaleDateString('id-ID', {
        weekday: 'long',
        day: '2-digit',
        month: 'long',
        year: 'numeric'
      });

      $('#visit_date').val(date.toISOString().split('T')[0]);
      $('#visit_time').val(time);
      $('#id_doctor').val(doctorId);
      $('#doctor_name').val(doctorName);

      $('#infoJadwal').text(`${formattedDate} - ${time}`);

      $('#modalPasien').modal('show');
    });

    // ================= SELECT2 PASIEN =================
    $('#modalPasien').on('shown.bs.modal', function() {

      if (!$('#id_patient').hasClass("select2-hidden-accessible")) {

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

      }

    });

    // ================= SAVE VISIT =================
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

          console.log(res); // 🔥 debug (lihat di console)

          if (res.status === 'success') {

            Swal.fire({
              icon: 'success',
              title: 'Berhasil!',
              text: `Antrian No: ${res.data.antrian}`,
              timer: 1500,
              showConfirmButton: false
            });

            // 🔥 RESET FORM
            form.reset();

            // 🔥 RESET SELECT2
            $('#id_patient').val(null).trigger('change');

            // 🔥 CLOSE MODAL (Bootstrap 5)
            let modal = bootstrap.Modal.getInstance(document.getElementById('modalPasien'));
            modal.hide();

            // 🔥 OPTIONAL: refresh table
            renderTable(new Date());

          } else {
            Swal.fire({
              icon: 'error',
              title: 'Gagal',
              text: res.message || 'Terjadi kesalahan'
            });
          }

        })
        .catch(err => {
          console.error(err);
          Swal.fire('Error', 'Server error', 'error');
        });

    });

    function normalizeTime(t) {
      return t.substring(0, 5); // fix 08:00:00 -> 08:00
    }



  });
</script>

</html>