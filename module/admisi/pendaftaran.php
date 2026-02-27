<?php
$title = 'Pasien';
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
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card shadow-sm">

                                <!-- Header -->
                                <div class="card-header bg-light">
                                    <h5 class="mb-0 fw-semibold">
                                        <iconify-icon icon="solar:user-id-linear"></iconify-icon>
                                        Pencarian Data Pasien
                                    </h5>
                                    <small class="text-muted">
                                        Masukkan Nomor KTP atau BPJS untuk menampilkan data pasien
                                    </small>
                                </div>
                                <div class="card-body">
                                    <form class="row g-3 align-items-end">
                                        <div class="col-md-4">
                                            <label class="form-label fw-medium">
                                                Nomor KTP / BPJS
                                            </label>
                                            <input type="text"
                                                id="nomor"
                                                class="form-control"
                                                placeholder="Contoh: 000***">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button"
                                                class="btn btn-success w-100"
                                                id="cari">
                                                <iconify-icon icon="solar:magnifer-linear"></iconify-icon>
                                                Cari
                                            </button>
                                        </div>
                                    </form>
                                    <hr class="my-4">
                                    <div id="tampilan">
                                        <div class="text-muted text-center">
                                            <iconify-icon icon="solar:info-circle-linear" width="22"></iconify-icon>
                                            <p class="mb-0 mt-2">
                                                Data pasien akan ditampilkan di sini
                                            </p>
                                        </div>
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
    <script src="controller/admisi/pendaftaran.js"></script>
</body>

</html>