<?php
$title = 'MCU';
require '../../controller/view.php';
date_default_timezone_set('Asia/Jakarta');
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
    body {
        background-color: #f4f6f9;
        font-family: "Segoe UI", sans-serif;
    }

    /* HEADER MCU */
    .mcu-header {
        background: #ffffff;
        border-left: 6px solid #0d6efd;
        padding: 16px 20px;
        margin-bottom: 20px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, .05);
    }

    .mcu-title {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .mcu-title i {
        font-size: 30px;
        color: #0d6efd;
    }

    .mcu-title h4 {
        margin: 0;
        font-weight: 600;
    }

    .mcu-title small {
        color: #6c757d;
    }

    /* PANEL */
    .mcu-panel {
        background: #fff;
        border: 1px solid #dee2e6;
        padding: 16px 18px;
        margin-bottom: 18px;
    }

    .mcu-panel-title {
        font-size: 14px;
        font-weight: 600;
        color: #495057;
        border-bottom: 1px dashed #ced4da;
        padding-bottom: 6px;
        margin-bottom: 14px;
    }

    .mcu-panel-title i {
        color: #0d6efd;
        margin-right: 6px;
    }

    /* FORM */
    .form-label {
        font-size: 12px;
        font-weight: 500;
        color: #495057;
    }

    /* FOOTER */
    .mcu-footer {
        background: #fff;
        border-top: 1px solid #dee2e6;
        padding: 14px 18px;
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
                    <div class="mcu-header d-flex justify-content-between align-items-center">
                        <div class="mcu-title d-flex align-items-center gap-2">
                            <i class="bi bi-heart-pulse text-danger"></i>
                            <div>
                                <h4 class="mb-0">MCU</h4>
                                <small>Medical Check Up – Pemeriksaan Kesehatan Menyeluruh</small>
                            </div>
                        </div>
                        <div class="mcu-meta text-end">
                            <div class="fw-semibold">
                                <i class="bi bi-ticket-detailed"></i>
                                <span id="metaNoKunjungan">No Kunjungan: -</span>
                            </div>
                            <small class="text-muted">
                                <i class="bi bi-calendar-event"></i>
                                <span id="metaTglDaftar">Tanggal: -</span>
                            </small>
                        </div>
                    </div>
                    <form id="isiform">
                        <input type="hidden" name="kdMCU">
                        <input type="hidden" name="noKunjungan">
                        <input type="hidden" name="tglPelayanan">
                        <div class="col-12">
                            <div class="row">
                                <div class="col-2">
                                    <div class="mcu-panel">
                                        <div class="mcu-panel-title">
                                            <i class="bi bi-activity"></i> Tekanan Darah
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-6">
                                                <label class="form-label">Sistole</label>
                                                <input type="number" name="tekananDarahSistole" class="form-control">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label">Diastole</label>
                                                <input type="number" name="tekananDarahDiastole" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-10">
                                    <div class="mcu-panel">
                                        <div class="mcu-panel-title">
                                            <i class="bi bi-droplet"></i> Pemeriksaan Darah Rutin
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-2">
                                                <label class="form-label">Hemoglobin</label>
                                                <input type="number" name="darahRutinHemo" class="form-control">
                                            </div>
                                            <div class="col-2">
                                                <label class="form-label">Leukosit</label>
                                                <input type="number" name="darahRutinLeu" class="form-control">
                                            </div>
                                            <div class="col-2">
                                                <label class="form-label">Eritrosit</label>
                                                <input type="number" name="darahRutinErit" class="form-control">
                                            </div>
                                            <div class="col-2">
                                                <label class="form-label">Trombosit</label>
                                                <input type="number" name="darahRutinTrom" class="form-control">
                                            </div>
                                            <div class="col-2">
                                                <label class="form-label">Laju Endap Darah</label>
                                                <input type="number" name="darahRutinLaju" class="form-control">
                                            </div>
                                            <div class="col-2">
                                                <label class="form-label">Hematokrit</label>
                                                <input type="number" name="darahRutinHema" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-2 d-none">
                                    <div class="mcu-panel">
                                        <div class="mcu-panel-title">
                                            <i class="bi bi-xray"></i> Radiologi
                                        </div>
                                        <input type="text" name="radiologiFoto" class="form-control"
                                            placeholder="Keterangan / URL / File">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mcu-panel">
                                        <div class="mcu-panel-title">
                                            <i class="bi bi-droplet-half"></i> Lemak Darah
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <label class="form-label">HDL</label>
                                                <input type="number" name="lemakDarahHDL" class="form-control">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">LDL</label>
                                                <input type="number" name="lemakDarahLDL" class="form-control">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Kolesterol Total</label>
                                                <input type="number" name="lemakDarahChol" class="form-control">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Trigliserida</label>
                                                <input type="number" name="lemakDarahTrigli" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mcu-panel">
                                        <div class="mcu-panel-title">
                                            <i class="bi bi-clipboard2-pulse"></i> Gula Darah
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <label class="form-label">Sewaktu</label>
                                                <input type="number" name="gulaDarahSewaktu" class="form-control">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Puasa</label>
                                                <input type="number" name="gulaDarahPuasa" class="form-control">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Post Prandial</label>
                                                <input type="number" name="gulaDarahPostPrandial" class="form-control">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">HbA1c</label>
                                                <input type="number" name="gulaDarahHbA1c" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mcu-panel">
                                        <div class="mcu-panel-title">
                                            <i class="bi bi-clipboard-pulse"></i> Fungsi Hati
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <label class="form-label">SGOT</label>
                                                <input type="number" name="fungsiHatiSGOT" class="form-control">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">SGPT</label>
                                                <input type="number" name="fungsiHatiSGPT" class="form-control">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Gamma GT</label>
                                                <input type="number" name="fungsiHatiGamma" class="form-control">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">ProtKual</label>
                                                <input type="number" name="fungsiHatiProtKual" class="form-control">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Albumin</label>
                                                <input type="number" name="fungsiHatiAlbumin" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mcu-panel">
                                        <div class="mcu-panel-title">
                                            <i class="bi bi-water"></i> Fungsi Ginjal
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Creatinin</label>
                                                <input type="number" name="fungsiGinjalCrea" class="form-control">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Ureum</label>
                                                <input type="number" name="fungsiGinjalUreum" class="form-control">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Asam Urat</label>
                                                <input type="number" name="fungsiGinjalAsam" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mcu-panel">
                                        <div class="mcu-panel-title">
                                            <i class="bi bi-heart-pulse"></i> Fungsi Jantung
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">ABI</label>
                                                <input type="number" name="fungsiJantungABI" class="form-control">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">EKG</label>
                                                <input type="text" name="fungsiJantungEKG" class="form-control">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">ECHO</label>
                                                <input type="text" name="fungsiJantungEcho" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mcu-panel">
                                        <div class="mcu-panel-title">
                                            <i class="bi bi-eye"></i> Funduskopi
                                        </div>
                                        <label class="form-label">Keterangan</label>
                                        <input type="text" name="funduskopi" class="form-control">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mcu-panel">
                                        <div class="mcu-panel-title">
                                            <i class="bi bi-search"></i> Pemeriksaan Lain
                                        </div>
                                        <textarea name="pemeriksaanLain" rows="3" class="form-control"></textarea>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mcu-panel">
                                        <div class="mcu-panel-title">
                                            <i class="bi bi-chat-text"></i> Keterangan
                                        </div>
                                        <textarea name="keterangan" rows="3" class="form-control"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mcu-footer d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-primary px-4" id="btnSaveMCU">
                                <i class="bi bi-save"></i> Simpan MCU
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php
    require '../admin/library.php';
    ?>
    <script src="controller/admisi/helper.js"></script>
    <script src="controller/admisi/mcu.js"></script>
</body>

</html>