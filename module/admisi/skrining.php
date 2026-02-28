<?php
$title = 'Skrining';
require '../../controller/view.php';
?>
<!doctype html>
<html lang="en">

<head>
    <base href="../../">
    <?php
    require '../../assets/template/head.php';
    ?>
</head>
<style>
    .select2-container .select2-selection--single {
        height: calc(2.25rem + 2px);
        display: flex;
        align-items: center;
    }

    .select2-selection__rendered {
        line-height: normal !important;
    }

    .select2-selection__arrow {
        height: 100% !important;
    }
</style>

<body>
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">
        <?php require '../admin/sidebar.php' ?>
        <div class="body-wrapper">
            <?php require '../admin/navbar.php' ?>
            <div class="body-wrapper-inner">
                <div class="container-fluid">
                    <div class="row g-4">
                        <div class="col-lg-12">

                            <!-- ================= CARD SEARCH ================= -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-body bg-gradient-search rounded-top">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <iconify-icon icon="solar:user-id-linear" width="26"></iconify-icon>
                                        <h5 class="mb-0 fw-semibold">
                                            Pencarian Skrining Pasien
                                        </h5>
                                    </div>
                                    <small class="text-muted">
                                        Masukkan nomor peserta untuk menampilkan seluruh data skrining
                                    </small>
                                </div>

                                <div class="card-body">
                                    <form class="row g-3 align-items-end">
                                        <div class="col-md-4">
                                            <label class="form-label fw-medium">
                                                Nomor Peserta
                                            </label>
                                            <input type="text"
                                                id="nomor"
                                                class="form-control form-control-lg"
                                                placeholder="Contoh: 000xxxxxxxxx">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button"
                                                class="btn btn-success btn-lg w-100"
                                                id="cari">
                                                <iconify-icon icon="solar:magnifer-linear"></iconify-icon>
                                                Cari Data
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- ================= REKAPITULASI ================= -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-body">
                                    <div class="text-center mb-3">
                                        <span class="badge bg-primary-subtle text-primary mb-2">
                                            Rekapitulasi
                                        </span>
                                        <h5 class="fw-semibold mb-1">
                                            Skrining Riwayat Kesehatan Peserta
                                        </h5>
                                        <small class="text-muted">
                                            Ringkasan hasil skrining penyakit kronis
                                        </small>
                                    </div>

                                    <div class="table-responsive">
                                        <table id="tableRekapitulasi"
                                            class="table table-hover table-bordered table-sm align-middle w-100">
                                            <thead class="table-primary text-center align-middle">
                                                <tr>
                                                    <th>No</th>
                                                    <th>Nama</th>
                                                    <th>Anemia</th>
                                                    <th>Hepatitis B</th>
                                                    <th>Hepatitis C</th>
                                                    <th>Hipertensi / Stroke / Jantung</th>
                                                    <th>Kanker Paru</th>
                                                    <th>Kanker Payudara</th>
                                                    <th>Kanker Serviks</th>
                                                    <th>Kanker Kolorektal</th>
                                                    <th>PPOK</th>
                                                    <th>Diabetes Mellitus</th>
                                                    <th>Thalasemia</th>
                                                    <th>Tuberkulosis</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- ================= PROLANIS DM ================= -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <h6 class="fw-semibold mb-0">
                                            <iconify-icon icon="solar:heart-pulse-linear" class="me-1"></iconify-icon>
                                            Data Prolanis Diabetes Mellitus
                                        </h6>
                                    </div>

                                    <div class="table-responsive">
                                        <table id="tableProlanisDiabetes"
                                            class="table table-hover table-bordered table-sm align-middle w-100">
                                            <thead class="table-success text-center align-middle">
                                                <tr>
                                                    <th>No</th>
                                                    <th>Nama</th>
                                                    <th>Diagnosa Terakhir</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- ================= PROLANIS HIPERTENSI ================= -->
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <h6 class="fw-semibold mb-0">
                                            <iconify-icon icon="solar:heart-linear" class="me-1"></iconify-icon>
                                            Data Prolanis Hipertensi
                                        </h6>
                                    </div>

                                    <div class="table-responsive">
                                        <table id="tableHipertensi"
                                            class="table table-hover table-bordered table-sm align-middle w-100">
                                            <thead class="table-warning text-center align-middle">
                                                <tr>
                                                    <th>No</th>
                                                    <th>Nama</th>
                                                    <th>Diagnosa Terakhir</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
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
    <script src="controller/admisi/helper.js"></script>
    <script src="controller/admisi/skrining.js"></script>
</body>

</html>