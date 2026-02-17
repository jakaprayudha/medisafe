<?php
$title = 'Pencarian Kunjungan';
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
                                <div class="card-header bg-light">
                                    <h5 class="mb-0 fw-semibold">
                                        <iconify-icon icon="solar:user-id-linear"></iconify-icon>
                                        Pencarian Kunjungan Pasien
                                    </h5>
                                    <small class="text-muted">
                                        Masukkan nomor kunjungan untuk menampilkan data kunjungan pasien
                                    </small>
                                </div>
                                <div class="card-body">
                                    <form class="row g-3 align-items-end">
                                        <div class="col-md-4">
                                            <label class="form-label fw-medium">
                                                Nomor Kunjungan
                                            </label>
                                            <input type="text"
                                                id="nomor"
                                                class="form-control"
                                                placeholder="Contoh: 000***" value="0032B0370226Y000001">
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
                                                Data kunjungan pasien akan ditampilkan di sini
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
    <div class="modal fade" id="modalDetailRujukan" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Detail Rujukan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="row g-3">

                        <!-- Informasi Rujukan -->
                        <div class="col-12">
                            <h6 class="fw-bold border-bottom pb-2">Informasi Rujukan</h6>
                        </div>

                        <div class="col-md-6">
                            <label>No Rujukan</label>
                            <input type="text" class="form-control" id="m_noRujukan" readonly>
                        </div>

                        <div class="col-md-6">
                            <label>Tanggal Kunjungan</label>
                            <input type="text" class="form-control" id="m_tglKunjungan" readonly>
                        </div>

                        <!-- PPK -->
                        <div class="col-12">
                            <h6 class="fw-bold border-bottom pb-2 mt-3">PPK</h6>
                        </div>

                        <div class="col-md-6">
                            <label>Kode PPK</label>
                            <input type="text" class="form-control" id="m_kdPPK" readonly>
                        </div>

                        <div class="col-md-6">
                            <label>Nama PPK</label>
                            <input type="text" class="form-control" id="m_nmPPK" readonly>
                        </div>

                        <div class="col-md-6">
                            <label>Kantor Cabang</label>
                            <input type="text" class="form-control" id="m_nmKC" readonly>
                        </div>

                        <div class="col-md-6">
                            <label>Kota</label>
                            <input type="text" class="form-control" id="m_nmDati" readonly>
                        </div>

                        <!-- Pasien -->
                        <div class="col-12">
                            <h6 class="fw-bold border-bottom pb-2 mt-3">Data Peserta</h6>
                        </div>

                        <div class="col-md-6">
                            <label>No Kartu</label>
                            <input type="text" class="form-control" id="m_nokaPst" readonly>
                        </div>

                        <div class="col-md-6">
                            <label>Nama Peserta</label>
                            <input type="text" class="form-control" id="m_nmPst" readonly>
                        </div>

                        <div class="col-md-6">
                            <label>Tanggal Lahir</label>
                            <input type="text" class="form-control" id="m_tglLahir" readonly>
                        </div>

                        <div class="col-md-6">
                            <label>Jenis Kelamin</label>
                            <input type="text" class="form-control" id="m_sex" readonly>
                        </div>

                        <!-- Poli & Diagnosa -->
                        <div class="col-12">
                            <h6 class="fw-bold border-bottom pb-2 mt-3">Pelayanan</h6>
                        </div>

                        <div class="col-md-6">
                            <label>Poli</label>
                            <input type="text" class="form-control" id="m_nmPoli" readonly>
                        </div>

                        <div class="col-md-6">
                            <label>Diagnosa Utama</label>
                            <input type="text" class="form-control" id="m_nmDiag1" readonly>
                        </div>

                        <div class="col-md-6">
                            <label>Dokter</label>
                            <input type="text" class="form-control" id="m_nmDokter" readonly>
                        </div>

                        <div class="col-12">
                            <label>Info Denda</label>
                            <textarea class="form-control" id="m_infoDenda" rows="2" readonly></textarea>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>

            </div>
        </div>
    </div>

    <?php
    require '../admin/library.php';
    ?>
    <script src="controller/admisi/helper.js"></script>
    <script src="controller/admisi/serckunjungan.js"></script>
</body>

</html>