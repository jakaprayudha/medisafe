<?php
$title = 'Pasien Terdaftar';
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
                    <div class="container-fluid py-4">
                        <div class="card border-0 shadow-sm rounded-3">

                            <!-- Header -->
                            <div class="card-header bg-white border-bottom py-3">
                                <div class="row align-items-center g-3">

                                    <!-- Judul -->
                                    <div class="col-md-6">
                                        <h5 class="fw-bold mb-0 text-dark">
                                            <i class="bi bi-clipboard-data text-primary me-2"></i>
                                            Data Pendaftaran Pasien
                                        </h5>
                                        <small class="text-muted">
                                            Monitoring pendaftaran pasien berdasarkan tanggal
                                        </small>
                                    </div>

                                    <!-- Filter Tanggal -->
                                    <div class="col-md-3 ms-auto">
                                        <label class="form-label small fw-semibold mb-1 text-muted">
                                            <i class="bi bi-calendar-event me-1 text-primary"></i>
                                            Filter Tanggal
                                        </label>
                                        <input type="date"
                                            class="form-control shadow-sm"
                                            id="tanggal"
                                            value="<?= date('Y-m-d') ?>">
                                    </div>

                                </div>
                            </div>

                            <!-- Body -->
                            <div class="card-body">

                                <!-- Search + Info Row -->
                                <div class="row mb-3 align-items-center">
                                    <div class="col-md-4">
                                        <div class="input-group shadow-sm">
                                            <span class="input-group-text bg-white">
                                                <i class="bi bi-search text-muted"></i>
                                            </span>
                                            <input type="text"
                                                id="searchLocal"
                                                class="form-control"
                                                placeholder="Cari No. Urut, Nama, Poli...">
                                        </div>
                                    </div>
                                </div>

                                <!-- Table -->
                                <div class="table-responsive">
                                    <table id="datapasien"
                                        class="table table-hover table-bordered align-middle table-sm w-100 mb-0">

                                        <thead class="table-light text-center">
                                            <tr>
                                                <th style="width:5%">No</th>
                                                <th>No. Urut</th>
                                                <th>No. Kartu</th>
                                                <th>Nama Pasien</th>
                                                <th>Kelamin</th>
                                                <th>Poli</th>
                                                <th style="width:10%">Action</th>
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

    <?php
    require '../admin/library.php';
    ?>
    <script src="controller/admisi/helper.js"></script>
    <script src="controller/admisi/listpendaftaran.js"></script>
</body>

</html>