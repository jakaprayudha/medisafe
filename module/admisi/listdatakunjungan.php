<?php
$title = 'Pasien Kunjungan';
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
                                        <i class="bi bi-people-fill text-primary me-2"></i>
                                        Data Kunjungan Pasien
                                    </h4>
                                    <p class="text-muted mb-0">
                                        Data kunjungan pasien
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-end gap-3 flex-wrap align-items-end">
                                        <div style="min-width:250px;">
                                            <label class="form-label fw-semibold mb-1">
                                                Nomor Kartu
                                            </label>
                                            <input type="text"
                                                id="noKartuSearch"
                                                class="form-control shadow-sm"
                                                placeholder="Masukkan nomor kartu BPJS">
                                        </div>
                                        <div>
                                            <button type="button"
                                                id="btnCariPasien"
                                                class="btn btn-primary shadow-sm">
                                                <i class="bi bi-search me-1"></i> Cari
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- BODY -->
                        <div class="card-body px-4 pb-4">
                            <div class="table-responsive">
                                <table id="datapasien"
                                    class="table table-hover align-middle w-100">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:5%">No</th>
                                            <th>Tgl. Kunjungan</th>
                                            <th>No. Kunjung</th>
                                            <th>Jns Kunjung</th>
                                            <th>Nama Pasien</th>
                                            <th>Poli/Kegiatan</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">
                                                Cari Data Dengan No Kartu Pasien
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- List Tindakan -->
    <div class="modal fade" id="modalTindakanList" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Daftar Tindakan</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-end mb-2">
                        <button class="btn btn-primary btn-sm" id="btnTambahTindakan">
                            + Tambah Tindakan
                        </button>
                    </div>
                    <table id="tableTindakan" class="table table-striped table-bordered w-100">
                        <thead class="table-primary">
                            <tr>
                                <th>Tindakan</th>
                                <th>Keterangan</th>
                                <th>Hasil</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tambah Tindakan -->
    <div class="modal fade" id="modalTambahTindakan" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Tindakan</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formTambahTindakan">
                        <input type="hidden" name="kdTindakanSK" value="0">
                        <div class="mb-2">
                            <label class="form-label">No Kunjungan</label>
                            <input type="text" class="form-control" name="noKunjungan" id="noKunjungan" readonly>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Tindakan</label>
                            <select class="form-select" name="kdTindakan" id="kdTindakan" required></select>
                            <input type="hidden" name="nmTindakan">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Biaya</label>
                            <input type="text" class="form-control" name="biaya" readonly>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Keterangan</label>
                            <textarea class="form-control" name="keterangan" id="keterangan"></textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Hasil</label>
                            <input type="text" class="form-control" name="hasil" placeholder="Isi hasil disini...">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-success" id="btnSimpanTindakan">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- List Obat -->
    <div class="modal fade" id="modalListObat" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Daftar Obat</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-end mb-2">
                        <button class="btn btn-primary btn-sm" id="btnTambahObat">
                            + Tambah Obat
                        </button>
                    </div>
                    <table id="tableListObat" class="table table-striped table-bordered w-100">
                        <thead class="table-primary">
                            <tr>
                                <th>Nama Obat</th>
                                <th>Signa</th>
                                <th>Jumlah</th>
                                <th>Jumlah Permintaan</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Obat -->
    <div class="modal fade" id="modalTambahObat" tabindex="-1"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-semibold">Tambah Obat</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formTambahObat">
                        <input type="hidden" name="kdObatSK">
                        <input type="hidden" name="kdRacikan">
                        <input type="hidden" name="nmObat">
                        <div class="mb-2">
                            <label class="form-label fw-semibold">No Kunjungan</label>
                            <input type="text" class="form-control"
                                name="noKunjungan" id="noKunjunganobat" readonly>
                        </div>
                        <div class="d-flex gap-4 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                    name="racikan" id="racikan">
                                <label class="form-check-label">Obat Racikan</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                    name="obatDPHO" id="obatDPHO" checked>
                                <label class="form-check-label">Obat DPHO</label>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-semibold">Obat</label>
                            <select class="form-select"
                                name="kdObat" id="kdObat" style="width:100%"></select>
                            <small class="text-muted fst-italic">
                                Ketik minimal 3 karakter untuk mencari obat
                            </small>
                        </div>
                        <div class="border rounded p-2 mb-2 bg-light">
                            <label class="form-label fw-semibold mb-1">
                                Aturan Pakai (Signa)
                            </label>
                            <div class="d-flex align-items-center gap-2">
                                <div class="input-group input-group-sm"
                                    style="max-width:140px">
                                    <input type="number"
                                        class="form-control text-center"
                                        name="signa1" min="1">
                                    <span class="input-group-text">×</span>
                                </div>
                                <span class="text-muted small">per hari, tiap</span>
                                <div class="input-group input-group-sm"
                                    style="max-width:140px">
                                    <input type="number"
                                        class="form-control text-center"
                                        name="signa2" min="1">
                                    <span class="input-group-text">tablet</span>
                                </div>
                            </div>
                            <small class="text-primary fw-semibold" id="previewSigna"></small>
                            <small class="text-muted fst-italic d-block">
                                Contoh: 3 × 1 = 3 kali sehari, 1 tablet tiap minum
                            </small>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-2">
                                <label class="form-label fw-semibold">Jumlah Obat</label>
                                <input type="number" class="form-control"
                                    name="jmlObat" min="1">
                            </div>
                            <div class="col-6 mb-2">
                                <label class="form-label fw-semibold">Jumlah Permintaan</label>
                                <input type="number" class="form-control"
                                    name="jmlPermintaan" min="1">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-semibold">
                                Nama Obat Non DPHO
                            </label>
                            <input type="text" class="form-control"
                                name="nmObatNonDPHO"
                                placeholder="Isi jika non DPHO / racikan">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button class="btn btn-success" id="btnSimpanObat">
                        Simpan
                    </button>
                </div>

            </div>
        </div>
    </div>



    <?php
    require '../admin/library.php';
    ?>
    <script src="controller/admisi/helper.js"></script>
    <script src="controller/admisi/listkunjungan.js"></script>
</body>

</html>