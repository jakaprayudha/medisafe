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
    .card {
        transition: 0.3s ease;
    }

    .table thead th {
        font-weight: 600;
        font-size: 14px;
    }

    .table tbody tr {
        transition: 0.2s ease;
    }

    .table tbody tr:hover {
        background-color: #f1f5ff;
    }

    #datapasien tbody td {
        padding-top: 12px;
        padding-bottom: 12px;
    }

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
                    <div class="card border-0 shadow-lg rounded-4">
                        <div class="card-header bg-white border-0 py-4 px-4">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h4 class="fw-bold mb-1 text-dark">
                                        <i class="bi bi-heart-pulse"></i>
                                        Data Medical Check Up
                                    </h4>
                                    <p class="text-muted mb-0">
                                        List Medical Check Up pasien
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-end gap-3 flex-wrap">
                                        <div style="min-width:220px;">
                                            <label class="form-label fw-semibold mb-1">
                                                Tanggal Pelayanan
                                            </label>
                                            <input type="date"
                                                class="form-control shadow-sm"
                                                id="tanggal">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- BODY -->
                        <div class="card-body px-4 pb-4">
                            <div class="table-responsive">
                                <table id="datamcu"
                                    class="table table-hover align-middle w-100">
                                    <thead class="table-light text-center">
                                        <tr>
                                            <th style="width:5%">No</th>
                                            <th>Tanggal</th>
                                            <th>No. Kunjungan</th>
                                            <th>No. Kartu</th>
                                            <th>Nama Pasien</th>
                                            <th style="width:15%">Action</th>
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

    <?php
    require '../admin/library.php';
    ?>
    <script src="controller/admisi/helper.js"></script>
    <script src="controller/admisi/mculist.js"></script>
</body>

</html>