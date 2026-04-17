<?php
$title = 'Pendaftaran Pasien';
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
        height: 42px !important;
        padding: 4px 10px;
        font-size: 14px;
        border-radius: 6px;
        background-color: #fff;
        color: #212529;
        border: 1px solid #ced4da;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 32px !important;
        color: #212529 !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 42px;
    }

    .select2-dropdown {
        background-color: #fff;
        color: #212529;
    }

    .select2-result-item {
        padding: 8px;
    }

    .select2-result-item .nama {
        font-weight: 600;
        font-size: 14px;
        color: #212529;
    }

    .select2-result-item .detail {
        font-size: 12px;
        color: #6c757d;
    }

    .select2-results__option--highlighted .nama,
    .select2-results__option--highlighted .detail {
        color: #fff !important;
    }

    .select2-results__option--highlighted {
        background-color: #0d6efd !important;
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
                                        Masukkan Nama Pasien untuk menampilkan data pasien
                                    </small>
                                </div>
                                <div class="card-body">
                                    <form class="row g-3 align-items-end">
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">
                                                Cari Pasien (Nama)
                                            </label>
                                            <select id="pasienSelect" class="form-control" style="width:100%"></select>
                                        </div>
                                        <!-- <div class="col-md-2">
                                            <button type="button"
                                                class="btn btn-success w-100"
                                                id="cari">
                                                <iconify-icon icon="solar:magnifer-linear"></iconify-icon>
                                                Cari
                                            </button>
                                        </div> -->
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
    <?php require '../admin/library.php'; ?>
    <script src="controller/admisi/helper.js"></script>
    <script src="controller/admisi/pendaftaran.js"></script>
    <div class="modal fade" id="modalCariBPJS" tabindex="-1"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-sm">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-semibold">
                        Pencarian Pasien
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-2">
                    <label class="form-label fw-medium">Nomor NIK</label>
                    <div class="input-group mb-2">
                        <input type="text"
                            id="inputNIK"
                            class="form-control form-control-lg"
                            placeholder="Masukkan NIK (16 digit)">
                        <button id="btnSearchBPJS" class="btn btn-primary px-4">
                            <span id="textCari">Cari</span>
                        </button>
                    </div>
                    <small class="text-muted">
                        Masukkan NIK untuk pencarian data BPJS
                    </small>
                    <form id="formBPJS" class="d-none mt-3">
                        <hr>
                        <input type="hidden" id="noKartuDaftar" name="noKartu">
                        <div class="mb-3">
                            <label class="form-label fw-medium">Nama Lengkap</label>
                            <input type="text" id="regNama" name="nama" class="form-control" readonly>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">Tanggal Lahir</label>
                                <input type="text" id="regTglLahir" name="tgl_lahir" class="form-control" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">Jenis Kelamin</label>
                                <input type="text" id="regJnsKelamin" name="jenis_kelamin" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">Nomor BPJS</label>
                                <input type="text" id="regNoBPJS" name="no_bpjs" class="form-control" readonly>
                            </div>
                        </div>
                        <small class="text-muted">
                            Periksa kembali data pasien sebelum menambahkan ke sistem.
                        </small>
                    </form>
                </div>
                <div class="modal-footer border-0 pt-0 d-flex justify-content-between">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button id="btnTambahPasien" class="btn btn-success px-3 d-none">
                        + Tambah Pasien
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>