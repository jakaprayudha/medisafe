<?php
$title = 'Kegiatan Kelompok';
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
                                        <i class="bi bi-view-list text-primary me-2"></i>
                                        Data Kegiatan Kelompok
                                    </h4>
                                    <p class="text-muted mb-0">
                                        <!-- Data Kegiatan Kelompok -->
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-end gap-3 flex-wrap align-items-end">
                                        <div class="row">
                                            <div class="col-6">
                                                <input type="date"
                                                    id="tglSearch"
                                                    class="form-control shadow-sm">
                                            </div>
                                            <div class="col-6">
                                                <button class="btn btn-info" id="addTambahKelompok"><i class="bi bi-plus-square-fill"></i> Tambah Kelompok</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- BODY -->
                        <div class="card-body px-4 pb-4">
                            <div class="table-responsive">
                                <table id="datakegiatan"
                                    class="table table-hover align-middle w-100">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:5%">No</th>
                                            <th>Pelayanan</th>
                                            <th>Clup Prolanis</th>
                                            <th>Kegiatan</th>
                                            <th>Materi</th>
                                            <th>Nama Ketua</th>
                                            <th>Telp. Ketua</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalKegiatan" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h4 class="fw-bold mb-1">Tambah Kegiatan</h4>
                        <small class="text-muted">Lengkapi informasi kegiatan dengan benar</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <hr class="mx-4">
                <div class="modal-body px-4">
                    <form id="formKegiatan">
                        <input type="hidden" name="eduId">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tanggal Pelayanan</label>
                                <input type="date" class="form-control form-control-lg rounded-3 shadow-sm" name="tglPelayanan" id="tglPelayanan">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Kegiatan</label>
                                <select class="form-select rounded-3 shadow-sm py-2"
                                    name="kdKegiatan"
                                    id="idKegiatan" required>
                                    <option value="" selected disabled>-- Pilih Kegiatan --</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Kelompok</label>
                                <select class="form-select rounded-3 shadow-sm py-2"
                                    name="kdKelompok"
                                    id="idkelompok" required>
                                    <option value="" selected disabled>-- Pilih Kelompok --</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Club Prolanis</label>
                                <select class="form-select rounded-3 shadow-sm py-2"
                                    name="kdClpprolanis"
                                    id="idClpprolanis" required>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Materi</label>
                                <input type="text" class="form-control rounded-3 shadow-sm" name="materi">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Pembicara</label>
                                <input type="text" class="form-control rounded-3 shadow-sm" name="pembicara">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Lokasi</label>
                                <input type="text" class="form-control rounded-3 shadow-sm" name="lokasi">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Biaya (Rp)</label>
                                <input type="text" class="form-control rounded-3 shadow-sm" name="biaya" id="idbiaya" placeholder="Rp 0">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Keterangan</label>
                                <textarea class="form-control rounded-3 shadow-sm" rows="3" name="keterangan"></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 pt-4">
                    <button class="btn btn-light px-4 rounded-3" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="button" id="btnInsertKegiatan"
                        class="btn btn-primary px-4 rounded-3 shadow-sm">
                        Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalAddPesertaKelompok" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h4 class="fw-bold mb-1">Tambah Peserta Kelompok</h4>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <hr class="mx-4">
                <div class="modal-body px-4">
                    <div class="card-body px-4 pb-4">
                        <div class="table-responsive">
                            <table id="dataPesertaKelompok"
                                class="table table-hover align-middle w-100">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:5%">No</th>
                                        <th>Nomor Kartu</th>
                                        <th>Nama Peserta</th>
                                        <th>Tgl. Lahir</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="9" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="spinner-border text-primary mb-3" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                                <strong>Memuat data...</strong>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-4">
                    <button class="btn btn-light px-4 rounded-3" data-bs-dismiss="modal">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalListPesertaKelompok" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h4 class="fw-bold mb-1">Peserta Kelompok</h4>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <hr class="mx-4">
                <div class="modal-body px-4">
                    <div class="card-body px-4 pb-4">
                        <div class="table-responsive">
                            <table id="PesertaKelompok"
                                class="table table-hover align-middle w-100">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:5%">No</th>
                                        <th>Nomor Kartu</th>
                                        <th>Nama Peserta</th>
                                        <th>Tgl. Lahir</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyPeserta">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-4">
                    <button class="btn btn-light px-4 rounded-3" data-bs-dismiss="modal">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php
    require '../admin/library.php';
    ?>
    <script src="controller/admisi/helper.js"></script>
    <script src="controller/admisi/kegiatan.js"></script>
</body>

</html>