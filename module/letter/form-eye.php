<?php

$title = 'Surat Pemeriksaan Mata';

require '../../controller/view.php';

?>

<!doctype html>

<html lang="id">

<head>

  <base href="../../">

  <?php
  require '../../assets/template/head.php';
  ?>

</head>


<body>


  <div
    class="page-wrapper"
    id="main-wrapper"
    data-layout="vertical"
    data-navbarbg="skin6"
    data-sidebartype="full"
    data-sidebar-position="fixed"
    data-header-position="fixed">


    <!-- SIDEBAR -->

    <?php
    require '../admin/sidebar.php';
    ?>


    <div class="body-wrapper">


      <!-- NAVBAR -->

      <?php
      require '../admin/navbar.php';
      ?>


      <div class="body-wrapper-inner">

        <div class="container-fluid">

          <div class="row">

            <div class="col-lg-12 d-flex align-items-stretch">

              <div class="card w-100">

                <div class="card-body p-4">


                  <!-- HEADER -->

                  <div
                    class="d-flex justify-content-between align-items-center mb-4">

                    <h5 class="card-title fw-semibold">

                      Surat Hasil Pemeriksaan Mata

                    </h5>


                    <div class="d-flex ms-auto gap-2">

                      <button
                        class="btn btn-primary"
                        id="btnTambah">

                        <i class="fas fa-plus"></i>

                        Tambah

                      </button>

                    </div>

                  </div>


                  <!-- TABLE -->

                  <div
                    class="table-responsive"
                    data-simplebar>


                    <table
                      class="table text-nowrap align-middle table-custom mb-0"
                      id="periodeTable">


                      <thead>

                        <tr>

                          <th>
                            Nomor Surat
                          </th>

                          <th>
                            Tanggal
                          </th>

                          <th>
                            Nama Pasien
                          </th>

                          <th>
                            RM
                          </th>

                          <th>
                            Visus OD
                          </th>

                          <th>
                            Visus OS
                          </th>

                          <th>
                            Kesimpulan
                          </th>

                          <th
                            class="text-center">

                            Actions

                          </th>

                        </tr>

                      </thead>


                      <tbody></tbody>


                    </table>

                  </div>


                </div>

              </div>

            </div>

          </div>

        </div>

      </div>

    </div>

  </div>


  <?php
  require '../admin/library.php';
  ?>

  <!-- =====================================================
     MODAL SURAT HASIL PEMERIKSAAN MATA
===================================================== -->

  <div
    class="modal fade"
    id="programModal"
    tabindex="-1"
    aria-hidden="true">


    <div
      class="modal-dialog modal-xl modal-dialog-scrollable">


      <form
        id="programForm"
        class="modal-content">


        <!-- =================================================
           HEADER
      ================================================== -->

        <div class="modal-header">

          <h5 class="modal-title">

            Tambah Surat Hasil Pemeriksaan Mata

          </h5>


          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal">
          </button>

        </div>


        <!-- =================================================
           BODY
      ================================================= -->

        <div class="modal-body">


          <!-- =================================================
             HIDDEN ID
        ================================================== -->

          <input
            type="hidden"
            name="id"
            id="id">


          <!-- =================================================
             ID CUSTOMER
             Controller tetap validasi dari SESSION
        ================================================== -->

          <input
            type="hidden"
            name="id_customer"
            id="id_customer"
            value="<?= htmlspecialchars(
                      $_SESSION['id_customer'] ?? ''
                    ) ?>">


          <!-- =================================================
             ID PATIENT
        ================================================== -->

          <input
            type="hidden"
            name="id_patient"
            id="id_patient">


          <!-- =================================================
             ID VISIT
        ================================================== -->

          <input
            type="hidden"
            name="id_visit"
            id="id_visit">


          <!-- =================================================
             A. IDENTITAS PASIEN
        ================================================== -->

          <div class="card border mb-3">

            <div class="card-header bg-light">

              <strong>

                <i class="fas fa-user me-1"></i>

                Identitas Pasien

              </strong>

            </div>


            <div class="card-body">

              <div class="row">


                <!-- PASIEN -->

                <div class="col-md-8 mb-3">

                  <label
                    for="id_patient_select"
                    class="form-label">

                    Nama Pasien

                  </label>


                  <select
                    name="id_patient_select"
                    id="id_patient_select"
                    class="form-select"
                    required>

                  </select>

                </div>


                <!-- NOMOR SURAT -->

                <div class="col-md-4 mb-3">

                  <label
                    for="nomor_surat"
                    class="form-label">

                    Nomor Surat

                  </label>


                  <input
                    type="text"
                    name="nomor_surat"
                    id="nomor_surat"
                    class="form-control"
                    placeholder="Nomor surat">


                  <!-- INFORMASI MODE NOMOR -->

                  <div
                    id="nomor_surat_info"
                    class="mt-2">

                  </div>

                </div>


                <!-- TANGGAL SURAT -->

                <div class="col-md-4 mb-0">

                  <label
                    for="tanggal_surat"
                    class="form-label">

                    Tanggal Surat

                  </label>


                  <input
                    type="date"
                    name="tanggal_surat"
                    id="tanggal_surat"
                    value="<?= date('Y-m-d') ?>"
                    class="form-control"
                    required>

                </div>


              </div>

            </div>

          </div>


          <!-- =================================================
             B. TANDA VITAL
        ================================================== -->

          <div class="card border mb-3">

            <div class="card-header bg-light">

              <strong>

                <i class="fas fa-heart-pulse me-1"></i>

                Tanda Vital

              </strong>

            </div>


            <div class="card-body">

              <div class="row">


                <!-- TEKANAN DARAH -->

                <div class="col-md-3 mb-3">

                  <label
                    for="tekanan_darah"
                    class="form-label">

                    Tekanan Darah

                  </label>


                  <input
                    type="text"
                    name="tekanan_darah"
                    id="tekanan_darah"
                    class="form-control"
                    placeholder="120/80">

                </div>


                <!-- NADI -->

                <div class="col-md-3 mb-3">

                  <label
                    for="nadi"
                    class="form-label">

                    Nadi

                  </label>


                  <input
                    type="text"
                    name="nadi"
                    id="nadi"
                    class="form-control"
                    placeholder="80">

                </div>


                <!-- SUHU -->

                <div class="col-md-3 mb-3">

                  <label
                    for="suhu"
                    class="form-label">

                    Suhu

                  </label>


                  <input
                    type="text"
                    name="suhu"
                    id="suhu"
                    class="form-control"
                    placeholder="36.5">

                </div>


                <!-- RESPIRASI -->

                <div class="col-md-3 mb-3">

                  <label
                    for="respirasi"
                    class="form-label">

                    Respirasi

                  </label>


                  <input
                    type="text"
                    name="respirasi"
                    id="respirasi"
                    class="form-control"
                    placeholder="20">

                </div>


              </div>

            </div>

          </div>


          <!-- =================================================
             C. PEMERIKSAAN LABORATORIUM
        ================================================== -->

          <div class="card border mb-3">

            <div class="card-header bg-light">

              <strong>

                <i class="fas fa-flask me-1"></i>

                Pemeriksaan Laboratorium / Penunjang

              </strong>

            </div>


            <div class="card-body">

              <div class="row">


                <!-- GDS -->

                <div class="col-md-6 mb-3">

                  <label
                    for="gula_darah_sewaktu"
                    class="form-label">

                    Gula Darah Sewaktu

                  </label>


                  <input
                    type="text"
                    name="gula_darah_sewaktu"
                    id="gula_darah_sewaktu"
                    class="form-control"
                    placeholder="mg/dL">

                </div>


                <!-- KETERANGAN GDS -->

                <div class="col-md-6 mb-3">

                  <label
                    for="gula_darah_keterangan"
                    class="form-label">

                    Keterangan Gula Darah

                  </label>


                  <input
                    type="text"
                    name="gula_darah_keterangan"
                    id="gula_darah_keterangan"
                    class="form-control"
                    placeholder="Normal / Tinggi / Rendah">

                </div>


                <!-- KOLESTEROL -->

                <div class="col-md-6 mb-3">

                  <label
                    for="kolesterol_total"
                    class="form-label">

                    Kolesterol Total

                  </label>


                  <input
                    type="text"
                    name="kolesterol_total"
                    id="kolesterol_total"
                    class="form-control"
                    placeholder="mg/dL">

                </div>


                <!-- KETERANGAN KOLESTEROL -->

                <div class="col-md-6 mb-3">

                  <label
                    for="kolesterol_keterangan"
                    class="form-label">

                    Keterangan Kolesterol

                  </label>


                  <input
                    type="text"
                    name="kolesterol_keterangan"
                    id="kolesterol_keterangan"
                    class="form-control"
                    placeholder="Normal / Tinggi">

                </div>


                <!-- ASAM URAT -->

                <div class="col-md-6 mb-3">

                  <label
                    for="asam_urat"
                    class="form-label">

                    Asam Urat

                  </label>


                  <input
                    type="text"
                    name="asam_urat"
                    id="asam_urat"
                    class="form-control"
                    placeholder="mg/dL">

                </div>


                <!-- KETERANGAN ASAM URAT -->

                <div class="col-md-6 mb-3">

                  <label
                    for="asam_urat_keterangan"
                    class="form-label">

                    Keterangan Asam Urat

                  </label>


                  <input
                    type="text"
                    name="asam_urat_keterangan"
                    id="asam_urat_keterangan"
                    class="form-control">

                </div>


                <!-- HEMOGLOBIN -->

                <div class="col-md-6 mb-3">

                  <label
                    for="hemoglobin"
                    class="form-label">

                    Hemoglobin

                  </label>


                  <input
                    type="text"
                    name="hemoglobin"
                    id="hemoglobin"
                    class="form-control"
                    placeholder="g/dL">

                </div>


                <!-- KETERANGAN HEMOGLOBIN -->

                <div class="col-md-6 mb-3">

                  <label
                    for="hemoglobin_keterangan"
                    class="form-label">

                    Keterangan Hemoglobin

                  </label>


                  <input
                    type="text"
                    name="hemoglobin_keterangan"
                    id="hemoglobin_keterangan"
                    class="form-control">

                </div>


              </div>

            </div>

          </div>


          <!-- =================================================
             D. PEMERIKSAAN VISUS
        ================================================== -->

          <div class="card border mb-3">

            <div class="card-header bg-light">

              <strong>

                <i class="fas fa-eye me-1"></i>

                Pemeriksaan Visus

              </strong>

            </div>


            <div class="card-body">


              <!-- OD -->

              <h6 class="fw-bold mb-3">

                Mata Kanan (OD)

              </h6>


              <div class="row">


                <div class="col-md-3 mb-3">

                  <label class="form-label">

                    Tanpa Koreksi - Jauh

                  </label>


                  <input
                    type="text"
                    name="visus_od_tanpa_koreksi_jauh"
                    id="visus_od_tanpa_koreksi_jauh"
                    class="form-control">

                </div>


                <div class="col-md-3 mb-3">

                  <label class="form-label">

                    Tanpa Koreksi - Dekat

                  </label>


                  <input
                    type="text"
                    name="visus_od_tanpa_koreksi_dekat"
                    id="visus_od_tanpa_koreksi_dekat"
                    class="form-control">

                </div>


                <div class="col-md-3 mb-3">

                  <label class="form-label">

                    Dengan Koreksi - Jauh

                  </label>


                  <input
                    type="text"
                    name="visus_od_dengan_koreksi_jauh"
                    id="visus_od_dengan_koreksi_jauh"
                    class="form-control">

                </div>


                <div class="col-md-3 mb-3">

                  <label class="form-label">

                    Dengan Koreksi - Dekat

                  </label>


                  <input
                    type="text"
                    name="visus_od_dengan_koreksi_dekat"
                    id="visus_od_dengan_koreksi_dekat"
                    class="form-control">

                </div>


              </div>


              <hr>


              <!-- OS -->

              <h6 class="fw-bold mb-3">

                Mata Kiri (OS)

              </h6>


              <div class="row">


                <div class="col-md-3 mb-3">

                  <label class="form-label">

                    Tanpa Koreksi - Jauh

                  </label>


                  <input
                    type="text"
                    name="visus_os_tanpa_koreksi_jauh"
                    id="visus_os_tanpa_koreksi_jauh"
                    class="form-control">

                </div>


                <div class="col-md-3 mb-3">

                  <label class="form-label">

                    Tanpa Koreksi - Dekat

                  </label>


                  <input
                    type="text"
                    name="visus_os_tanpa_koreksi_dekat"
                    id="visus_os_tanpa_koreksi_dekat"
                    class="form-control">

                </div>


                <div class="col-md-3 mb-3">

                  <label class="form-label">

                    Dengan Koreksi - Jauh

                  </label>


                  <input
                    type="text"
                    name="visus_os_dengan_koreksi_jauh"
                    id="visus_os_dengan_koreksi_jauh"
                    class="form-control">

                </div>


                <div class="col-md-3 mb-3">

                  <label class="form-label">

                    Dengan Koreksi - Dekat

                  </label>


                  <input
                    type="text"
                    name="visus_os_dengan_koreksi_dekat"
                    id="visus_os_dengan_koreksi_dekat"
                    class="form-control">

                </div>


              </div>


            </div>

          </div>


          <!-- =================================================
             E. PEMERIKSAAN REFRAKSI
        ================================================== -->

          <div class="card border mb-3">

            <div class="card-header bg-light">

              <strong>

                <i class="fas fa-glasses me-1"></i>

                Pemeriksaan Refraksi

              </strong>

            </div>


            <div class="card-body">


              <!-- OD -->

              <h6 class="fw-bold">

                OD - Mata Kanan

              </h6>


              <div class="row">


                <div class="col-md-3 mb-3">

                  <label class="form-label">
                    SPH
                  </label>


                  <input
                    type="text"
                    name="refraksi_od_sph"
                    id="refraksi_od_sph"
                    class="form-control">

                </div>


                <div class="col-md-3 mb-3">

                  <label class="form-label">
                    CYL
                  </label>


                  <input
                    type="text"
                    name="refraksi_od_cyl"
                    id="refraksi_od_cyl"
                    class="form-control">

                </div>


                <div class="col-md-3 mb-3">

                  <label class="form-label">
                    AXIS
                  </label>


                  <input
                    type="text"
                    name="refraksi_od_axis"
                    id="refraksi_od_axis"
                    class="form-control">

                </div>


                <div class="col-md-3 mb-3">

                  <label class="form-label">
                    ADD
                  </label>


                  <input
                    type="text"
                    name="refraksi_od_add"
                    id="refraksi_od_add"
                    class="form-control">

                </div>


              </div>


              <hr>


              <!-- OS -->

              <h6 class="fw-bold">

                OS - Mata Kiri

              </h6>


              <div class="row">


                <div class="col-md-3 mb-3">

                  <label class="form-label">
                    SPH
                  </label>


                  <input
                    type="text"
                    name="refraksi_os_sph"
                    id="refraksi_os_sph"
                    class="form-control">

                </div>


                <div class="col-md-3 mb-3">

                  <label class="form-label">
                    CYL
                  </label>


                  <input
                    type="text"
                    name="refraksi_os_cyl"
                    id="refraksi_os_cyl"
                    class="form-control">

                </div>


                <div class="col-md-3 mb-3">

                  <label class="form-label">
                    AXIS
                  </label>


                  <input
                    type="text"
                    name="refraksi_os_axis"
                    id="refraksi_os_axis"
                    class="form-control">

                </div>


                <div class="col-md-3 mb-3">

                  <label class="form-label">
                    ADD
                  </label>


                  <input
                    type="text"
                    name="refraksi_os_add"
                    id="refraksi_os_add"
                    class="form-control">

                </div>


              </div>


              <!-- PD -->

              <div class="row">

                <div class="col-md-4 mb-0">

                  <label
                    for="pd"
                    class="form-label">

                    PD

                  </label>


                  <input
                    type="text"
                    name="pd"
                    id="pd"
                    class="form-control">

                </div>

              </div>


            </div>

          </div>


          <!-- =================================================
             F. PEMERIKSAAN MATA LAINNYA
        ================================================== -->

          <div class="card border mb-3">

            <div class="card-header bg-light">

              <strong>

                <i class="fas fa-microscope me-1"></i>

                Pemeriksaan Mata Lainnya

              </strong>

            </div>


            <div class="card-body">

              <div class="row">


                <!-- TIO OD -->

                <div class="col-md-6 mb-3">

                  <label
                    for="tio_od"
                    class="form-label">

                    TIO OD

                  </label>


                  <input
                    type="text"
                    name="tio_od"
                    id="tio_od"
                    class="form-control"
                    placeholder="mmHg">

                </div>


                <!-- TIO OS -->

                <div class="col-md-6 mb-3">

                  <label
                    for="tio_os"
                    class="form-label">

                    TIO OS

                  </label>


                  <input
                    type="text"
                    name="tio_os"
                    id="tio_os"
                    class="form-control"
                    placeholder="mmHg">

                </div>


                <!-- ANTERIOR OD -->

                <div class="col-md-6 mb-3">

                  <label
                    for="segmen_anterior_od"
                    class="form-label">

                    Segmen Anterior OD

                  </label>


                  <textarea
                    name="segmen_anterior_od"
                    id="segmen_anterior_od"
                    class="form-control"
                    rows="3"></textarea>

                </div>


                <!-- ANTERIOR OS -->

                <div class="col-md-6 mb-3">

                  <label
                    for="segmen_anterior_os"
                    class="form-label">

                    Segmen Anterior OS

                  </label>


                  <textarea
                    name="segmen_anterior_os"
                    id="segmen_anterior_os"
                    class="form-control"
                    rows="3"></textarea>

                </div>


                <!-- POSTERIOR OD -->

                <div class="col-md-6 mb-3">

                  <label
                    for="segmen_posterior_od"
                    class="form-label">

                    Segmen Posterior OD

                  </label>


                  <textarea
                    name="segmen_posterior_od"
                    id="segmen_posterior_od"
                    class="form-control"
                    rows="3"></textarea>

                </div>


                <!-- POSTERIOR OS -->

                <div class="col-md-6 mb-3">

                  <label
                    for="segmen_posterior_os"
                    class="form-label">

                    Segmen Posterior OS

                  </label>


                  <textarea
                    name="segmen_posterior_os"
                    id="segmen_posterior_os"
                    class="form-control"
                    rows="3"></textarea>

                </div>


              </div>

            </div>

          </div>


          <!-- =================================================
             G. KESIMPULAN & REKOMENDASI
        ================================================== -->

          <div class="card border mb-3">

            <div class="card-header bg-light">

              <strong>

                <i class="fas fa-clipboard-check me-1"></i>

                Kesimpulan & Rekomendasi

              </strong>

            </div>


            <div class="card-body">


              <!-- KESIMPULAN -->

              <div class="mb-3">

                <label
                  for="kesimpulan"
                  class="form-label">

                  Kesimpulan

                </label>


                <textarea
                  name="kesimpulan"
                  id="kesimpulan"
                  class="form-control"
                  rows="4"
                  placeholder="Kesimpulan hasil pemeriksaan mata..."></textarea>

              </div>


              <!-- REKOMENDASI -->

              <div class="mb-0">

                <label
                  for="rekomendasi"
                  class="form-label">

                  Rekomendasi

                </label>


                <textarea
                  name="rekomendasi"
                  id="rekomendasi"
                  class="form-control"
                  rows="4"
                  placeholder="Rekomendasi dokter..."></textarea>

              </div>


            </div>

          </div>


        </div>


        <!-- =================================================
           FOOTER
      ================================================== -->

        <div class="modal-footer">

          <button
            type="button"
            class="btn btn-light"
            data-bs-dismiss="modal">

            Batal

          </button>


          <button
            type="submit"
            class="btn btn-primary"
            id="btnSimpanSurat">

            <i class="fas fa-save me-1"></i>

            Simpan

          </button>

        </div>


      </form>

    </div>

  </div>
</body>
<script>
  const apiUrl =
    'controller/letter/suratPemeriksaanMataController';


  /*
  |--------------------------------------------------------------------------
  | HELPER SET VALUE
  |--------------------------------------------------------------------------
  */

  function setValue(id, value) {

    $('#' + id).val(
      value !== null &&
      value !== undefined ?
      value :
      ''
    );

  }


  /*
  |--------------------------------------------------------------------------
  | HELPER ESCAPE HTML
  |--------------------------------------------------------------------------
  */

  function escapeHtml(value) {

    if (
      value === null ||
      value === undefined
    ) {

      return '';

    }

    return $('<div>')
      .text(value)
      .html();

  }


  /*
  |--------------------------------------------------------------------------
  | RESET NOMOR SURAT
  |--------------------------------------------------------------------------
  */

  function resetNomorSurat() {

    $('#nomor_surat')
      .val('')
      .prop('readonly', false)
      .removeClass('bg-light');

    $('#nomor_surat_info')
      .html('');

  }


  /*
  |--------------------------------------------------------------------------
  | TAMPILKAN MODE NOMOR
  |--------------------------------------------------------------------------
  */

  function tampilkanModeNomor(
    mode,
    format = '',
    nomor = ''
  ) {

    mode =
      String(mode || '')
      .toUpperCase();


    /*
    |--------------------------------------------------------------------------
    | MANUAL
    |--------------------------------------------------------------------------
    */

    if (mode === 'MANUAL') {

      $('#nomor_surat')
        .prop('readonly', false)
        .removeClass('bg-light')
        .attr(
          'placeholder',
          'Masukkan nomor surat'
        );


      $('#nomor_surat_info')
        .html(`

          <div class="alert alert-warning py-2 mb-0">

            <div class="d-flex align-items-center">

              <i class="fas fa-keyboard me-2"></i>

              <div>

                <strong>Penomoran Manual</strong>

                <div class="small">
                  Nomor surat diisi secara manual oleh pengguna.
                </div>

              </div>

            </div>

          </div>

        `);


      return;

    }


    /*
    |--------------------------------------------------------------------------
    | AUTO
    |--------------------------------------------------------------------------
    */

    if (mode === 'AUTO') {

      $('#nomor_surat')
        .val('')
        .prop('readonly', true)
        .addClass('bg-light')
        .attr(
          'placeholder',
          'Nomor surat dibuat otomatis'
        );


      let infoFormat =
        format || 'Format otomatis';


      let infoNomor =
        nomor !== '' ?
        nomor :
        '-';


      $('#nomor_surat_info')
        .html(`

          <div class="alert alert-success py-2 mb-0">

            <div class="d-flex align-items-center">

              <i class="fas fa-robot me-2"></i>

              <div>

                <strong>Penomoran Otomatis</strong>

                <div class="small">
                  Format:
                  <strong>
                    ${escapeHtml(infoFormat)}
                  </strong>
                </div>

                <div class="small">
                  Nomor terakhir:
                  <strong>
                    ${escapeHtml(infoNomor)}
                  </strong>
                </div>

              </div>

            </div>

          </div>

        `);


      return;

    }


    /*
    |--------------------------------------------------------------------------
    | BELUM DISETTING
    |--------------------------------------------------------------------------
    */

    $('#nomor_surat')
      .val('')
      .prop('readonly', true)
      .addClass('bg-light')
      .attr(
        'placeholder',
        'Setting nomor surat belum tersedia'
      );


    $('#nomor_surat_info')
      .html(`

        <div class="alert alert-danger py-2 mb-0">

          <i class="fas fa-exclamation-triangle me-1"></i>

          Penomoran surat belum dikonfigurasi.

        </div>

      `);

  }


  /*
  |--------------------------------------------------------------------------
  | CHECK SETTING NOMOR SURAT
  |--------------------------------------------------------------------------
  |
  | Endpoint controller harus mendukung:
  |
  | ?check_setting=1
  |
  |--------------------------------------------------------------------------
  */

  function checkSettingNomorSurat(
    callback
  ) {

    fetch(
        apiUrl +
        '?check_setting=1'
      )

      .then(
        res =>
        res.json()
      )

      .then(
        response => {

          /*
          |--------------------------------------------------------------------------
          | SETTING TIDAK ADA
          |--------------------------------------------------------------------------
          */

          if (
            response.status ===
            'setting_required'
          ) {

            Swal.fire({

                icon: 'warning',

                title: 'Setting Nomor Surat Belum Ada',

                html: `

                <div class="text-center">

                  <p class="mb-3">
                    Setting nomor surat untuk
                    <strong>
                      Surat Hasil Pemeriksaan Mata
                    </strong>
                    belum dikonfigurasi.
                  </p>

                  <p class="text-muted small mb-0">

                    Silakan atur mode
                    <strong>Manual</strong>
                    atau
                    <strong>Otomatis</strong>
                    terlebih dahulu.

                  </p>

                </div>

              `,

                showCancelButton: true,

                confirmButtonText: '<i class="fas fa-cog me-1"></i> Setting Nomor Surat',

                cancelButtonText: 'Batal',

                confirmButtonColor: '#0d6efd'

              })

              .then(
                result => {

                  if (
                    result.isConfirmed
                  ) {

                    window.location.href =
                      'module/letter/setting-surat';

                  }

                }
              );


            return;

          }


          /*
          |--------------------------------------------------------------------------
          | ERROR
          |--------------------------------------------------------------------------
          */

          if (
            response.status !==
            'success'
          ) {

            Swal.fire(
              'Gagal!',
              response.message ||
              'Gagal membaca setting nomor surat.',
              'error'
            );

            return;

          }


          /*
          |--------------------------------------------------------------------------
          | AMBIL DATA SETTING
          |--------------------------------------------------------------------------
          */

          let setting =
            response.data ||
            response.setting || {};


          /*
          |--------------------------------------------------------------------------
          | BEBERAPA CONTROLLER MENGEMBALIKAN:
          |
          | data: {
          |   mode_nomor,
          |   format_mata,
          |   nomor_mata
          | }
          |
          |--------------------------------------------------------------------------
          */

          let mode =
            setting.mode_nomor ||
            response.mode_nomor ||
            '';


          let format =
            setting.format_mata ||
            response.format_mata ||
            '';


          let nomor =
            setting.nomor_mata ??
            response.nomor_mata ??
            0;


          /*
          |--------------------------------------------------------------------------
          | MODE VALID
          |--------------------------------------------------------------------------
          */

          if (
            mode !== 'AUTO' &&
            mode !== 'MANUAL'
          ) {

            Swal.fire({

                icon: 'warning',

                title: 'Mode Nomor Surat Belum Valid',

                text: 'Silakan periksa kembali setting nomor surat.',

                confirmButtonText: 'Buka Setting'

              })
              .then(
                () => {

                  window.location.href =
                    'module/letter/setting-surat';

                }
              );


            return;

          }


          /*
          |--------------------------------------------------------------------------
          | CALLBACK
          |--------------------------------------------------------------------------
          */

          if (
            typeof callback ===
            'function'
          ) {

            callback({

              mode_nomor: mode,

              format_mata: format,

              nomor_mata: nomor

            });

          }

        }
      )

      .catch(
        error => {

          console.error(
            'checkSettingNomorSurat:',
            error
          );


          Swal.fire(
            'Error!',
            'Tidak dapat membaca setting nomor surat.',
            'error'
          );

        }
      );

  }


  /*
  |--------------------------------------------------------------------------
  | INIT SELECT2 PASIEN
  |--------------------------------------------------------------------------
  */

  function initPatientSelect() {

    const $select =
      $('#id_patient_select');


    if (
      $select.hasClass(
        'select2-hidden-accessible'
      )
    ) {

      $select.select2(
        'destroy'
      );

    }


    $select.select2({

      dropdownParent: $('#programModal'),

      width: '100%',

      placeholder: 'Cari Pasien Kunjungan...',

      allowClear: true,

      minimumInputLength: 2,

      ajax: {

        url: 'controller/admisi/patientVisitControllerInOut',

        type: 'GET',

        dataType: 'json',

        delay: 300,


        data: function(params) {

          return {

            search: params.term ||
              ''

          };

        },


        processResults: function(response) {

          let items =
            response.data || [];


          return {

            results:

              items.map(
                function(item) {

                  return {

                    id: item.id_patient,

                    text:

                      item.patient_name +
                      ' | RM: ' +
                      (
                        item.nomor_rm ||
                        '-'
                      ) +
                      ' | ' +
                      (
                        item.visit_date ||
                        '-'
                      ),

                    id_patient: item.id_patient,

                    id_visit: item.id_visit,

                    visit_ID: item.visit_ID,

                    patient_name: item.patient_name,

                    nomor_rm: item.nomor_rm,

                    patient_nik: item.patient_nik,

                    visit_date: item.visit_date,

                    id_doctor: item.id_doctor,

                    tekanan_darah: item.tekanan_darah,

                    nadi: item.nadi,

                    suhu: item.suhu,

                    respirasi: item.respirasi

                  };

                }
              )

          };

        },


        cache: true

      }

    });


    /*
    |--------------------------------------------------------------------------
    | SELECT PATIENT
    |--------------------------------------------------------------------------
    */

    $select.off(
      'select2:select'
    );


    $select.on(
      'select2:select',
      function(e) {

        const data =
          e.params.data;


        /*
        |--------------------------------------------------------------------------
        | RELASI
        |--------------------------------------------------------------------------
        */

        setValue(
          'id_patient',
          data.id_patient
        );


        setValue(
          'id_visit',
          data.id_visit
        );


        /*
        |--------------------------------------------------------------------------
        | TANDA VITAL
        |--------------------------------------------------------------------------
        */

        setValue(
          'tekanan_darah',
          data.tekanan_darah
        );


        setValue(
          'nadi',
          data.nadi
        );


        setValue(
          'suhu',
          data.suhu
        );


        setValue(
          'respirasi',
          data.respirasi
        );


        console.log(
          'Visit pemeriksaan mata:',
          data
        );

      }
    );


    /*
    |--------------------------------------------------------------------------
    | CLEAR
    |--------------------------------------------------------------------------
    */

    $select.off(
      'select2:clear'
    );


    $select.on(
      'select2:clear',
      function() {

        $('#id_patient')
          .val('');


        $('#id_visit')
          .val('');


        $('#tekanan_darah')
          .val('');


        $('#nadi')
          .val('');


        $('#suhu')
          .val('');


        $('#respirasi')
          .val('');

      }
    );

  }


  /*
  |--------------------------------------------------------------------------
  | DOCUMENT READY
  |--------------------------------------------------------------------------
  */

  $(document).ready(
    function() {


      /*
      |--------------------------------------------------------------------------
      | INIT DATATABLE
      |--------------------------------------------------------------------------
      */

      const table =
        $('#periodeTable').DataTable({

          processing: true,

          serverSide: false,

          scrollX: true,

          ajax: {

            url: apiUrl,

            type: 'GET',


            dataSrc: function(json) {

              if (
                json.status !==
                'success'
              ) {

                Swal.fire(
                  'Gagal!',
                  json.message ||
                  'Gagal mengambil data.',
                  'error'
                );


                return [];

              }


              return (

                json.data || []

              ).map(
                function(row) {

                  return {

                    id: row.id,

                    nomor_surat: row.nomor_surat ||
                      '-',

                    tanggal_surat: row.tanggal_surat ||
                      '-',

                    patient_name: row.patient_name ||
                      '-',

                    nomor_rm: row.nomor_rm ||
                      '-',

                    visus_od:

                      row.visus_od_dengan_koreksi_jauh ||
                      row.visus_od_tanpa_koreksi_jauh ||
                      '-',

                    visus_os:

                      row.visus_os_dengan_koreksi_jauh ||
                      row.visus_os_tanpa_koreksi_jauh ||
                      '-',

                    kesimpulan: row.kesimpulan ||
                      '-',

                    actions: `

                        <div class="text-end">

                          <div
                            class="btn-group btn-group-sm"
                            role="group">


                            <a
                              class="btn btn-primary"
                              href="module/letter/print/surat-pemeriksaan-mata?id=${row.id}"
                              target="_blank"
                              title="Cetak Surat">

                              <i class="fas fa-print"></i>

                            </a>


                            <a
                              class="btn btn-warning edit-btn"
                              href="javascript:;"
                              data-id="${row.id}"
                              title="Edit">

                              <i class="fas fa-edit"></i>

                            </a>


                            <a
                              class="btn btn-danger delete-btn"
                              href="javascript:;"
                              data-id="${row.id}"
                              title="Hapus">

                              <i class="fas fa-trash"></i>

                            </a>


                          </div>

                        </div>

                      `

                  };

                }
              );

            }

          },


          columns: [

            {
              data: 'nomor_surat'
            },

            {
              data: 'tanggal_surat'
            },

            {
              data: 'patient_name'
            },

            {
              data: 'nomor_rm'
            },

            {
              data: 'visus_od'
            },

            {
              data: 'visus_os'
            },

            {
              data: 'kesimpulan'
            },

            {
              data: 'actions',

              orderable: false,

              searchable: false

            }

          ],


          order: [

            [
              1,
              'desc'
            ]

          ]

        });


      /*
      |--------------------------------------------------------------------------
      | INIT SELECT2
      |--------------------------------------------------------------------------
      */

      initPatientSelect();


      /*
      |--------------------------------------------------------------------------
      | TAMBAH
      |--------------------------------------------------------------------------
      */

      $('#btnTambah').on(
        'click',
        function() {


          /*
          |--------------------------------------------------------------------------
          | RESET FORM
          |--------------------------------------------------------------------------
          */

          $('#programForm')[0]
            .reset();


          $('#id')
            .val('');


          $('#id_customer')
            .val(
              '<?= htmlspecialchars(
                  $_SESSION['id_customer'] ?? ''
                ) ?>'
            );


          $('#id_patient')
            .val('');


          $('#id_visit')
            .val('');


          /*
          |--------------------------------------------------------------------------
          | RESET SELECT2
          |--------------------------------------------------------------------------
          */

          $('#id_patient_select')
            .val(null)
            .trigger('change');


          /*
          |--------------------------------------------------------------------------
          | TANGGAL SURAT
          |--------------------------------------------------------------------------
          */

          $('#tanggal_surat')
            .val(
              '<?= date('Y-m-d') ?>'
            );


          /*
          |--------------------------------------------------------------------------
          | RESET NOMOR
          |--------------------------------------------------------------------------
          */

          resetNomorSurat();


          /*
          |--------------------------------------------------------------------------
          | TITLE
          |--------------------------------------------------------------------------
          */

          $('#programModal .modal-title')
            .text(
              'Tambah Surat Hasil Pemeriksaan Mata'
            );


          /*
          |--------------------------------------------------------------------------
          | CEK SETTING SEBELUM MODAL
          |--------------------------------------------------------------------------
          */

          checkSettingNomorSurat(
            function(setting) {

              tampilkanModeNomor(

                setting.mode_nomor,

                setting.format_mata,

                setting.nomor_mata

              );


              /*
              |--------------------------------------------------------------------------
              | BUKA MODAL
              |--------------------------------------------------------------------------
              */

              $('#programModal')
                .modal('show');

            }
          );

        }
      );


      /*
      |--------------------------------------------------------------------------
      | SUBMIT
      |--------------------------------------------------------------------------
      */

      $('#programForm').on(
        'submit',
        function(e) {

          e.preventDefault();


          const id =
            $('#id').val();


          const idPatient =
            $('#id_patient').val();


          const idVisit =
            $('#id_visit').val();


          const nomorSurat =
            $.trim(
              $('#nomor_surat').val()
            );


          /*
          |--------------------------------------------------------------------------
          | VALIDASI PASIEN
          |--------------------------------------------------------------------------
          */

          if (!idPatient) {

            Swal.fire(
              'Perhatian!',
              'Silakan pilih pasien.',
              'warning'
            );

            return;

          }


          /*
          |--------------------------------------------------------------------------
          | VALIDASI VISIT
          |--------------------------------------------------------------------------
          */

          if (!idVisit) {

            Swal.fire(
              'Perhatian!',
              'Visit pasien tidak ditemukan.',
              'warning'
            );

            return;

          }


          /*
          |--------------------------------------------------------------------------
          | VALIDASI NOMOR MANUAL
          |--------------------------------------------------------------------------
          |
          | Hanya berlaku jika input aktif.
          |
          |--------------------------------------------------------------------------
          */

          if (
            !$('#nomor_surat')
            .prop('readonly') &&
            !nomorSurat
          ) {

            Swal.fire(
              'Perhatian!',
              'Nomor surat wajib diisi.',
              'warning'
            );


            $('#nomor_surat')
              .focus();


            return;

          }


          /*
          |--------------------------------------------------------------------------
          | FORM DATA
          |--------------------------------------------------------------------------
          */

          const formData =
            new URLSearchParams(
              new FormData(this)
            );


          /*
          |--------------------------------------------------------------------------
          | SUBMIT BUTTON
          |--------------------------------------------------------------------------
          */

          const submitButton =
            $(this).find(
              'button[type="submit"]'
            );


          const original =
            submitButton.html();


          submitButton
            .prop(
              'disabled',
              true
            )
            .html(
              '<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...'
            );


          /*
          |--------------------------------------------------------------------------
          | REQUEST
          |--------------------------------------------------------------------------
          */

          fetch(

              apiUrl +

              (
                id ?
                '?id=' + encodeURIComponent(id) :
                ''
              ),

              {

                method: id ?
                  'PUT' :
                  'POST',

                headers: {

                  'Content-Type': 'application/x-www-form-urlencoded'

                },

                body: formData

              }

            )

            .then(
              res =>
              res.json()
            )

            .then(
              data => {


                submitButton
                  .prop(
                    'disabled',
                    false
                  )
                  .html(
                    original
                  );


                /*
                |--------------------------------------------------------------------------
                | SETTING REQUIRED
                |--------------------------------------------------------------------------
                */

                if (
                  data.status ===
                  'setting_required'
                ) {

                  Swal.fire({

                      icon: 'warning',

                      title: 'Setting Nomor Surat Belum Ada',

                      text: data.message ||
                        'Silakan setting nomor surat terlebih dahulu.',

                      showCancelButton: true,

                      confirmButtonText: 'Buka Setting',

                      cancelButtonText: 'Batal'

                    })
                    .then(
                      result => {

                        if (
                          result.isConfirmed
                        ) {

                          window.location.href =
                            'module/letter/setting-surat';

                        }

                      }
                    );


                  return;

                }


                /*
                |--------------------------------------------------------------------------
                | SUCCESS
                |--------------------------------------------------------------------------
                */

                if (
                  data.status ===
                  'success'
                ) {

                  Swal.fire(
                    'Berhasil!',
                    data.message ||
                    'Surat berhasil disimpan.',
                    'success'
                  );


                  $('#programModal')
                    .modal('hide');


                  table.ajax.reload(
                    null,
                    false
                  );


                  return;

                }


                /*
                |--------------------------------------------------------------------------
                | ERROR
                |--------------------------------------------------------------------------
                */

                Swal.fire(
                  'Gagal!',
                  data.message ||
                  'Gagal menyimpan surat.',
                  'error'
                );

              }
            )

            .catch(
              error => {

                console.error(
                  'Submit surat mata:',
                  error
                );


                submitButton
                  .prop(
                    'disabled',
                    false
                  )
                  .html(
                    original
                  );


                Swal.fire(
                  'Error!',
                  'Terjadi kesalahan pada server.',
                  'error'
                );

              }
            );

        }
      );


      /*
      |--------------------------------------------------------------------------
      | EDIT
      |--------------------------------------------------------------------------
      */

      $(document).on(
        'click',
        '.edit-btn',
        function() {

          const id =
            $(this).data('id');


          if (!id) {

            Swal.fire(
              'Error!',
              'ID surat tidak ditemukan.',
              'error'
            );

            return;

          }


          fetch(

              apiUrl +
              '?id=' +
              encodeURIComponent(id)

            )

            .then(
              res =>
              res.json()
            )

            .then(
              resp => {


                /*
                |--------------------------------------------------------------------------
                | ERROR
                |--------------------------------------------------------------------------
                */

                if (
                  resp.status !==
                  'success'
                ) {

                  Swal.fire(
                    'Gagal!',
                    resp.message ||
                    'Gagal mengambil data surat.',
                    'error'
                  );


                  return;

                }


                const d =
                  resp.data;


                /*
                |--------------------------------------------------------------------------
                | ID
                |--------------------------------------------------------------------------
                */

                setValue(
                  'id',
                  d.id
                );


                setValue(
                  'id_customer',
                  d.id_customer
                );


                setValue(
                  'id_patient',
                  d.id_patient
                );


                setValue(
                  'id_visit',
                  d.id_visit
                );


                /*
                |--------------------------------------------------------------------------
                | NOMOR SURAT
                |--------------------------------------------------------------------------
                |
                | Saat EDIT nomor surat lama selalu ditampilkan.
                | Tidak generate nomor baru.
                |
                |--------------------------------------------------------------------------
                */

                setValue(
                  'nomor_surat',
                  d.nomor_surat
                );


                $('#nomor_surat')
                  .prop(
                    'readonly',
                    true
                  )
                  .addClass(
                    'bg-light'
                  );


                $('#nomor_surat_info')
                  .html(`

                    <div class="alert alert-secondary py-2 mb-0">

                      <i class="fas fa-lock me-1"></i>

                      Nomor surat terkunci karena
                      data sedang diedit.

                    </div>

                  `);


                /*
                |--------------------------------------------------------------------------
                | TANGGAL SURAT
                |--------------------------------------------------------------------------
                */

                setValue(
                  'tanggal_surat',
                  d.tanggal_surat
                );


                /*
                |--------------------------------------------------------------------------
                | TANDA VITAL
                |--------------------------------------------------------------------------
                */

                setValue(
                  'tekanan_darah',
                  d.tekanan_darah
                );


                setValue(
                  'nadi',
                  d.nadi
                );


                setValue(
                  'suhu',
                  d.suhu
                );


                setValue(
                  'respirasi',
                  d.respirasi
                );


                /*
                |--------------------------------------------------------------------------
                | LAB
                |--------------------------------------------------------------------------
                */

                setValue(
                  'gula_darah_sewaktu',
                  d.gula_darah_sewaktu
                );


                setValue(
                  'gula_darah_keterangan',
                  d.gula_darah_keterangan
                );


                setValue(
                  'kolesterol_total',
                  d.kolesterol_total
                );


                setValue(
                  'kolesterol_keterangan',
                  d.kolesterol_keterangan
                );


                setValue(
                  'asam_urat',
                  d.asam_urat
                );


                setValue(
                  'asam_urat_keterangan',
                  d.asam_urat_keterangan
                );


                setValue(
                  'hemoglobin',
                  d.hemoglobin
                );


                setValue(
                  'hemoglobin_keterangan',
                  d.hemoglobin_keterangan
                );


                /*
                |--------------------------------------------------------------------------
                | VISUS OD
                |--------------------------------------------------------------------------
                */

                setValue(
                  'visus_od_tanpa_koreksi_jauh',
                  d.visus_od_tanpa_koreksi_jauh
                );


                setValue(
                  'visus_od_tanpa_koreksi_dekat',
                  d.visus_od_tanpa_koreksi_dekat
                );


                setValue(
                  'visus_od_dengan_koreksi_jauh',
                  d.visus_od_dengan_koreksi_jauh
                );


                setValue(
                  'visus_od_dengan_koreksi_dekat',
                  d.visus_od_dengan_koreksi_dekat
                );


                /*
                |--------------------------------------------------------------------------
                | VISUS OS
                |--------------------------------------------------------------------------
                */

                setValue(
                  'visus_os_tanpa_koreksi_jauh',
                  d.visus_os_tanpa_koreksi_jauh
                );


                setValue(
                  'visus_os_tanpa_koreksi_dekat',
                  d.visus_os_tanpa_koreksi_dekat
                );


                setValue(
                  'visus_os_dengan_koreksi_jauh',
                  d.visus_os_dengan_koreksi_jauh
                );


                setValue(
                  'visus_os_dengan_koreksi_dekat',
                  d.visus_os_dengan_koreksi_dekat
                );


                /*
                |--------------------------------------------------------------------------
                | REFRAKSI OD
                |--------------------------------------------------------------------------
                */

                setValue(
                  'refraksi_od_sph',
                  d.refraksi_od_sph
                );


                setValue(
                  'refraksi_od_cyl',
                  d.refraksi_od_cyl
                );


                setValue(
                  'refraksi_od_axis',
                  d.refraksi_od_axis
                );


                setValue(
                  'refraksi_od_add',
                  d.refraksi_od_add
                );


                /*
                |--------------------------------------------------------------------------
                | REFRAKSI OS
                |--------------------------------------------------------------------------
                */

                setValue(
                  'refraksi_os_sph',
                  d.refraksi_os_sph
                );


                setValue(
                  'refraksi_os_cyl',
                  d.refraksi_os_cyl
                );


                setValue(
                  'refraksi_os_axis',
                  d.refraksi_os_axis
                );


                setValue(
                  'refraksi_os_add',
                  d.refraksi_os_add
                );


                /*
                |--------------------------------------------------------------------------
                | PD
                |--------------------------------------------------------------------------
                */

                setValue(
                  'pd',
                  d.pd
                );


                /*
                |--------------------------------------------------------------------------
                | TIO
                |--------------------------------------------------------------------------
                */

                setValue(
                  'tio_od',
                  d.tio_od
                );


                setValue(
                  'tio_os',
                  d.tio_os
                );


                /*
                |--------------------------------------------------------------------------
                | SEGMENT ANTERIOR
                |--------------------------------------------------------------------------
                */

                setValue(
                  'segmen_anterior_od',
                  d.segmen_anterior_od
                );


                setValue(
                  'segmen_anterior_os',
                  d.segmen_anterior_os
                );


                /*
                |--------------------------------------------------------------------------
                | SEGMENT POSTERIOR
                |--------------------------------------------------------------------------
                */

                setValue(
                  'segmen_posterior_od',
                  d.segmen_posterior_od
                );


                setValue(
                  'segmen_posterior_os',
                  d.segmen_posterior_os
                );


                /*
                |--------------------------------------------------------------------------
                | KESIMPULAN
                |--------------------------------------------------------------------------
                */

                setValue(
                  'kesimpulan',
                  d.kesimpulan
                );


                /*
                |--------------------------------------------------------------------------
                | REKOMENDASI
                |--------------------------------------------------------------------------
                */

                setValue(
                  'rekomendasi',
                  d.rekomendasi
                );


                /*
                |--------------------------------------------------------------------------
                | SELECT2 PASIEN
                |--------------------------------------------------------------------------
                */

                const option =
                  new Option(

                    (
                      d.patient_name ||
                      '-'
                    ) +

                    ' - RM ' +

                    (
                      d.nomor_rm ||
                      '-'
                    ),

                    d.id_patient,

                    true,

                    true

                  );


                $('#id_patient_select')
                  .empty()
                  .append(option)
                  .trigger('change');


                /*
                |--------------------------------------------------------------------------
                | TITLE
                |--------------------------------------------------------------------------
                */

                $('#programModal .modal-title')
                  .text(
                    'Edit Surat Hasil Pemeriksaan Mata'
                  );


                /*
                |--------------------------------------------------------------------------
                | SHOW
                |--------------------------------------------------------------------------
                */

                $('#programModal')
                  .modal('show');

              }
            )

            .catch(
              error => {

                console.error(
                  'Edit surat mata:',
                  error
                );


                Swal.fire(
                  'Error!',
                  'Gagal mengambil data surat.',
                  'error'
                );

              }
            );

        }
      );


      /*
      |--------------------------------------------------------------------------
      | DELETE
      |--------------------------------------------------------------------------
      */

      $(document).on(
        'click',
        '.delete-btn',
        function() {

          const id =
            $(this).data('id');


          if (!id) {

            Swal.fire(
              'Error!',
              'ID surat tidak ditemukan.',
              'error'
            );

            return;

          }


          Swal.fire({

              title: 'Hapus Surat?',

              text: 'Data surat yang dihapus tidak dapat dikembalikan.',

              icon: 'warning',

              showCancelButton: true,

              confirmButtonText: 'Ya, Hapus',

              cancelButtonText: 'Batal',

              confirmButtonColor: '#d33'

            })

            .then(
              result => {

                if (
                  !result.isConfirmed
                ) {

                  return;

                }


                fetch(

                    apiUrl +
                    '?id=' +
                    encodeURIComponent(id),

                    {

                      method: 'DELETE'

                    }

                  )

                  .then(
                    res =>
                    res.json()
                  )

                  .then(
                    data => {

                      if (
                        data.status ===
                        'success'
                      ) {

                        Swal.fire(
                          'Berhasil!',
                          data.message ||
                          'Data berhasil dihapus.',
                          'success'
                        );


                        table.ajax.reload(
                          null,
                          false
                        );


                      } else {

                        Swal.fire(
                          'Gagal!',
                          data.message ||
                          'Gagal menghapus data.',
                          'error'
                        );

                      }

                    }
                  )

                  .catch(
                    error => {

                      console.error(
                        'Delete surat mata:',
                        error
                      );


                      Swal.fire(
                        'Error!',
                        'Gagal menghapus data.',
                        'error'
                      );

                    }
                  );

              }
            );

        }
      );


      /*
      |--------------------------------------------------------------------------
      | RESET MODAL SETELAH DITUTUP
      |--------------------------------------------------------------------------
      */

      $('#programModal').on(
        'hidden.bs.modal',
        function() {

          /*
          |--------------------------------------------------------------------------
          | RESET FORM
          |--------------------------------------------------------------------------
          */

          $('#programForm')[0]
            .reset();


          /*
          |--------------------------------------------------------------------------
          | RESET HIDDEN
          |--------------------------------------------------------------------------
          */

          $('#id')
            .val('');


          $('#id_patient')
            .val('');


          $('#id_visit')
            .val('');


          $('#id_customer')
            .val(
              '<?= htmlspecialchars(
                  $_SESSION['id_customer'] ?? ''
                ) ?>'
            );


          /*
          |--------------------------------------------------------------------------
          | RESET SELECT2
          |--------------------------------------------------------------------------
          */

          $('#id_patient_select')
            .val(null)
            .trigger('change');


          /*
          |--------------------------------------------------------------------------
          | RESET NOMOR
          |--------------------------------------------------------------------------
          */

          resetNomorSurat();


          /*
          |--------------------------------------------------------------------------
          | RESET TITLE
          |--------------------------------------------------------------------------
          */

          $('#programModal .modal-title')
            .text(
              'Tambah Surat Hasil Pemeriksaan Mata'
            );

        }
      );


    }
  );
</script>

</html>