<?php
$title = 'Pasien Terdaftar';
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
    .subtext {
        color: #6c757d;
        transition: 0.2s;
    }

    .btn-check:checked+.btn .subtext {
        color: #ffffff;
    }

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
                    <form id="isiform">
                        <input type="hidden" name="noKartu" id="noKartu">
                        <input type="hidden" name="kdPoli" id="kode_poli">
                        <input type="hidden" name="nmPoli" id="nama_poli">
                        <input type="hidden" name="noKunjungan" id="noKunjungan">
                        <div class="card">
                            <h5 class="card-header p-3">Kunjungan</h5>
                            <div class="card-body" style="padding-top:10px; padding-bottom: 10px;">
                                <div class="row g-3 align-items-center">
                                    <div class="col-12">
                                        <div class="row">
                                            <div class="col-2">
                                                <label for="jeniskunjung" class="col-form-label col-form-label-sm">Jenis Kunjungan<span class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-4">
                                                <select class="form-select" name="kunjungan" id="kunjungan" readonly></select>
                                            </div>
                                            <div class="col-2">
                                                <label for="jeniskunjung" class="col-form-label col-form-label-sm">Perawatan<span class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-4">
                                                <select class="form-select" name="rujukan" id="rujukan" readonly>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="row">
                                            <div class="col-2">
                                                <label class="col-form-label col-form-label-sm">Tanggal Kunjungan<span class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-10">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <input type="date" required="" name="tglDaftar" id="tglDaftar" class="form-control" readonly>
                                                    </div>
                                                    <div class="col-6">
                                                        <input type="time" required="" value="<?= date('H:i:s') ?>" id="timeKunjungan" class="form-control">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="row">
                                            <div class="col-2">
                                                <label for="" class="col-form-label col-form-label-sm">Keluhan<span class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-10">
                                                <div class="form">
                                                    <textarea class="form-control" id="keluhan" name="keluhan" style="height: 80px"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12" id="formAnamnesa">
                                        <div class="row">
                                            <div class="col-2">
                                                <label for="" class="col-form-label col-form-label-sm">Anamnesa<span class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-10">
                                                <div class="form">
                                                    <textarea class="form-control" id="anamnesa" name="anamnesa" style="height: 80px"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12" id="formalergi">
                                        <div class="row">
                                            <div class="col-2">
                                                <label for="" class="col-form-label col-form-label-sm">Alergi Makan<span class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-3">
                                                <select class="form-select" name="alergiMakan" id="alergiMakan"></select>
                                            </div>
                                            <div class="col-3">
                                                <select class="form-select" name="alergiUdara" id="alergiUdara"></select>
                                            </div>
                                            <div class="col-4">
                                                <select class="form-select" name="alergiObat" id="alergiObat"></select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12" id="formpronosa">
                                        <div class="row">
                                            <div class="col-2">
                                                <label for="" class="col-form-label col-form-label-sm">Prognosa<span class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-10">
                                                <select class="form-select" name="kdPrognosa" id="kdPrognosa">
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="row">
                                            <div class="col-2">
                                                <label for="" class="col-form-label col-form-label-sm">Terapi Obat<span class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-10">
                                                <div class="form">
                                                    <textarea class="form-control" id="terapiObat" name="terapiObat" style="height: 80px"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12" id="formnonobat">
                                        <div class="row">
                                            <div class="col-2">
                                                <label for="" class="col-form-label col-form-label-sm">Terapi Non Obat<span class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-10">
                                                <div class="form">
                                                    <textarea class="form-control" id="terapiNonObat" name="terapiNonObat" style="height: 80px"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12" id="formbmhp">
                                        <div class="row">
                                            <div class="col-2">
                                                <label for="" class="col-form-label col-form-label-sm">BMPH<span class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-10">
                                                <div class="form">
                                                    <textarea class="form-control" id="bmhp" name="bmhp" style="height: 80px"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="row">
                                            <div class="row g-3 col-10 offset-2">
                                                <div class="col-md-12">
                                                    <label class="form-label fw-semibold">
                                                        Diagnosa Utama <span class="text-danger">*</span>
                                                    </label>
                                                    <select id="diag1" name="diag1" class="form-select" required></select>
                                                    <input type="hidden" id="nmDiag1" name="nmDiag1">
                                                    <input type="hidden" id="kdnonSpesialis1">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">
                                                        Diagnosa Sekunder 1
                                                    </label>
                                                    <select id="diag2" name="diag2" class="form-select"></select>
                                                    <input type="hidden" id="nmDiag2" name="nmDiag2">
                                                    <input type="hidden" id="kdnonSpesialis2">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">
                                                        Diagnosa Sekunder 2
                                                    </label>
                                                    <select id="diag3" name="diag3" class="form-select"></select>
                                                    <input type="hidden" id="nmDiag3" name="nmDiag3">
                                                    <input type="hidden" id="kdnonSpesialis3">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="row">
                                            <div class="col-2">
                                                <label for="" class="col-form-label col-form-label-sm">Kesadaran<span class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-4">
                                                <select class="form-select" name="kdSadar" id="kdSadar">
                                                </select>
                                            </div>
                                            <div class="col-1">
                                                <label for="" class="col-form-label col-form-label-sm">Suhu<span class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-5">
                                                <div class="input-group mb-3">
                                                    <input
                                                        type="number"
                                                        class="form-control"
                                                        name="suhu"
                                                        id="suhu"
                                                        placeholder="Suhu"
                                                        min="25"
                                                        max="45"
                                                        step="0.1">
                                                    <span class="input-group-text"><b>℃</b></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr />
                                    <div class="col-12">
                                        <div class="row">
                                            <div class="col-2">
                                                <label for="" class="col-form-label col-form-label-sm">Pemeriksaan Fisik<span class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-10">
                                                <div class="row">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <label for="tinggiBadan">Tinggi Badan <span class="text-danger">*</span></label>
                                                            <div class="input-group mb-2">
                                                                <input
                                                                    type="number"
                                                                    class="form-control form-control-sm"
                                                                    name="tinggiBadan"
                                                                    id="tinggiBadan"
                                                                    min="30"
                                                                    max="250"
                                                                    step="0.1"
                                                                    required>
                                                                <span class="input-group-text">CM</span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label for="beratBadan">Berat Badan <span class="text-danger">*</span></label>
                                                            <div class="input-group mb-2">
                                                                <input
                                                                    type="number"
                                                                    class="form-control form-control-sm"
                                                                    name="beratBadan"
                                                                    id="beratBadan"
                                                                    min="1"
                                                                    max="300"
                                                                    step="0.1"
                                                                    required>
                                                                <span class="input-group-text">Kg</span>
                                                            </div>
                                                        </div>

                                                        <!-- Lingkar Perut -->
                                                        <div class="col-md-4">
                                                            <label for="lingkarPerut">Lingkar Perut</label>
                                                            <div class="input-group mb-2">
                                                                <input
                                                                    type="number"
                                                                    class="form-control form-control-sm"
                                                                    name="lingkarPerut"
                                                                    id="lingkarPerut"
                                                                    min="30"
                                                                    max="200"
                                                                    step="0.1">
                                                                <span class="input-group-text">CM</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr />
                                    <div class="col-12">
                                        <div class="row">
                                            <div class="col-2">
                                                <label for="" class="col-form-label col-form-label-sm">Tekanan Darah<span class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-10">
                                                <div class="row">

                                                    <!-- Sistole -->
                                                    <div class="col-md-3">
                                                        <label for="sistole">Sistole <span class="text-danger">*</span></label>
                                                        <div class="input-group mb-2">
                                                            <input type="number"
                                                                class="form-control form-control-sm"
                                                                name="sistole"
                                                                id="sistole"
                                                                min="50"
                                                                max="300"
                                                                step="1"
                                                                required>
                                                            <span class="input-group-text">mmHg</span>
                                                        </div>
                                                    </div>

                                                    <!-- Diastole -->
                                                    <div class="col-md-3">
                                                        <label for="diastole">Diastole <span class="text-danger">*</span></label>
                                                        <div class="input-group mb-2">
                                                            <input type="number"
                                                                class="form-control form-control-sm"
                                                                name="diastole"
                                                                id="diastole"
                                                                min="30"
                                                                max="200"
                                                                step="1"
                                                                required>
                                                            <span class="input-group-text">mmHg</span>
                                                        </div>
                                                    </div>

                                                    <!-- Respiratory Rate -->
                                                    <div class="col-md-3">
                                                        <label for="respRate">Respiratory Rate <span class="text-danger">*</span></label>
                                                        <div class="input-group mb-2">
                                                            <input type="number"
                                                                class="form-control form-control-sm"
                                                                name="respRate"
                                                                id="respRate"
                                                                min="5"
                                                                max="60"
                                                                step="1"
                                                                required>
                                                            <span class="input-group-text">/Minute</span>
                                                        </div>
                                                    </div>

                                                    <!-- Heart Rate -->
                                                    <div class="col-md-3">
                                                        <label for="heartRate">Heart Rate <span class="text-danger">*</span></label>
                                                        <div class="input-group mb-2">
                                                            <input type="number"
                                                                class="form-control form-control-sm"
                                                                name="heartRate"
                                                                id="heartRate"
                                                                min="30"
                                                                max="220"
                                                                step="1"
                                                                required>
                                                            <span class="input-group-text">BPM</span>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <hr />
                                <!-- <div class="col-12">
                                    <div class="row mb-3">
                                        <div class="col-2">
                                            <label for="" class="col-form-label col-form-label-sm">Kasus KLL<span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-10">
                                            <label class="btn btn-light p-2">
                                                <input class="form-check-input" type="checkbox" id="tanggalKll" name="tanggalKll" aria-label="Checkbox for following text input" style="margin-right: 0.6em;"> Kecelakaan Lalu Lintas
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div id="tampilanKll" class="d-none">
                                        <div class="row mb-3">
                                            <div class="col-2">Tanggal Kejadian</div>
                                            <div class="col-10">
                                                <input class="form-control" type="date" data-language="en" id="tanggalKejadian" name="tanggalKejadian">
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-2">Lokasi Kejadian</div>
                                            <div class="col-8"><select class="form-select" id="provinsi" name="provinsi">
                                                    <option value="0">provinsi</option>
                                                    <option value="1">Option 1</option>
                                                    <option value="2">Option 2</option>
                                                    <option value="3">Option 3</option>
                                                </select>
                                                <br />
                                                <select class="form-select" id="kabupaten" name="kabupaten">
                                                    <option value="0">kabupaten</option>
                                                    <option value="1">Option 1</option>
                                                    <option value="2">Option 2</option>
                                                    <option value="3">Option 3</option>
                                                </select>
                                                <br />
                                                <select class="form-select" id="kecamatan" name="kecamatan">
                                                    <option value="0">kecamatan</option>
                                                    <option value="1">Option 1</option>
                                                    <option value="2">Option 2</option>
                                                    <option value="3">Option 3</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div> -->
                                <div class="col-12">
                                    <div class="row mb-3">
                                        <div class="col-2">
                                            <label for="" class="col-form-label col-form-label-sm">Tenaga Medis<span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-10">
                                            <div class="input-group mb-3" style="flex-wrap:nowrap">
                                                <select class=" form-control" id="kdDokter" name="kdDokter">
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- <div class="col-12">
                                    <div class="row mb-3">
                                        <div class="col-2">
                                            <label for="listNonKapitasi_slc" class="col-form-label col-form-label-sm">Pelayanan Non Kapitasi<span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-10">
                                            <select class="form-control" id="listNonKapitasi_slc" name="listNonKapitasi_slc" multiple="multiple" style="margin-bottom: 2px;">
                                                <option value="01">Option 1</option>
                                                <option value="02">Option 2</option>
                                                <option value="03">Option 3</option>
                                                <option value="04">Option 4</option>
                                                <option value="05">Option 5</option>
                                            </select>
                                        </div>
                                    </div>
                                </div> -->
                                <div class="col-12">
                                    <div class="row mb-3">
                                        <div class="col-2">
                                            <label for="" class="col-form-label col-form-label-sm">Tanggal Pulang<span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-10">
                                            <input type="date" required="" name="tglPulang" id="tglPulang" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="row mb-3">
                                        <div class="col-2">
                                            <label for="" class="col-form-label col-form-label-sm">Status Pulang<span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-10">
                                            <select class="form-select" id="kdStatusPulang" name="kdStatusPulang">
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 d-none" id="rujukvertikal">
                                    <hr>
                                    <div class="row mb-3">
                                        <div class="col-2">
                                            <label class="col-form-label col-form-label-sm">
                                                Status Rujukan <span class="text-danger">*</span>
                                            </label>
                                        </div>
                                        <div class="col-10">
                                            <div class="row g-2">
                                                <div class="col-md-6">
                                                    <input type="radio" class="btn-check" name="kdStatusRujuk"
                                                        id="rujukSpesialis" value="SP" required>
                                                    <label class="btn btn-outline-primary w-100 text-start p-3"
                                                        for="rujukSpesialis">
                                                        <strong>Rujukan Spesialis</strong><br>
                                                        <small class="subtext">
                                                            Rujukan ke sub spesialis sesuai indikasi medis
                                                        </small>
                                                    </label>
                                                </div>
                                                <div class="col-md-6">
                                                    <input type="radio" class="btn-check" name="kdStatusRujuk"
                                                        id="rujukKhusus" value="KH" required>
                                                    <label class="btn btn-outline-success w-100 text-start p-3"
                                                        for="rujukKhusus">
                                                        <strong>Rujukan Khusus</strong><br>
                                                        <small class="subtext">
                                                            Rujukan dengan kriteria atau program khusus
                                                        </small>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 d-none" id="rujukhorizontal">
                                    <hr>
                                    <p>Rujukan Horizontal</p>
                                </div>
                                <input type="hidden" name="typeRujukan" id="typeRujukan" value="normal">
                                <div class="col-12 d-none" id="formrujukanvertikal">
                                    <!-- Kategori -->
                                    <div class="row mb-3">
                                        <div class="col-2">
                                            <label for="kdKategori" class="col-form-label">
                                                Kategori <span class="text-danger">*</span>
                                            </label>
                                        </div>
                                        <div class="col-5">
                                            <label for="kdKategori" class="form-label fw-semibold">
                                                Spesialis
                                            </label>
                                            <select class="form-select"
                                                id="kdKategori"
                                                name="kdKategori"
                                                required>
                                            </select>
                                        </div>
                                        <div class="col-2 d-none" id="subspesialis">
                                            <label for="kdKategori" class="form-label fw-semibold">
                                                Sub Spesialis
                                            </label>
                                            <select class="form-select"
                                                id="kdsubspesialis"
                                                name="kdSubSpesialis1">
                                            </select>
                                        </div>
                                        <div class="col-3 d-none" id="sarana">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input"
                                                    type="checkbox"
                                                    id="useSarana"
                                                    value="1">
                                                <label class="form-check-label fw-semibold" for="useSarana">
                                                    Sarana
                                                </label>
                                            </div>
                                            <select class="form-select"
                                                id="kdSarana">
                                            </select>
                                            <input type="hidden"
                                                name="kdSarana"
                                                id="kdSaranaHidden"
                                                value="9">
                                        </div>
                                        <div class="col-5">
                                            <label for="kdKategori" class="form-label fw-semibold">
                                                Tgl Kunjung
                                            </label>
                                            <div class="input-group">
                                                <input type="date"
                                                    class="form-control"
                                                    id="tglRujukan"
                                                    name="tglRujukan"
                                                    required>
                                                <button type="button" id="btnCariFaskes"
                                                    class="btn btn-primary">
                                                    <i class="bi bi-search"></i> Cari Faskes
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Alasan -->
                                    <div class="row mb-3 d-none" id="alasanrujuk">
                                        <div class="col-2">
                                            <label for="alasanRujukan" class="col-form-label">
                                                Alasan <span class="text-danger">*</span>
                                            </label>
                                        </div>
                                        <div class="col-10">
                                            <textarea class="form-control"
                                                id="alasanRujukan"
                                                name="alasanRujukan"
                                                rows="3"
                                                placeholder="Masukkan alasan rujukan..."
                                                required></textarea>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="row mb-3">
                                            <div class="col-2">
                                                <label for="" class="col-form-label col-form-label-sm">Faskes<span class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-10">
                                                <input type="text" id="nmfaskes" name="nmfaskes" class="form-control" readonly>
                                                <input type="hidden" name="kdppk" id="kdfaskes">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 d-none" id="formTacc">
                                    <div class="row mb-3">
                                        <div class="col-2">
                                            <label for="kdKategori" class="col-form-label">
                                                TACC <span class="text-danger">*</span>
                                            </label>
                                        </div>
                                        <div class="col-3">
                                            <label for="kdTacc" class="form-label fw-semibold">
                                                TACC
                                            </label>
                                            <select class="form-select" id="kdTacc" name="kdTacc">
                                                <option value="0">- Pilih -</option>
                                                <option value="-1">Tanpa TACC</option>
                                                <option value="1">Time</option>
                                                <option value="2">Age</option>
                                                <option value="3">Complication</option>
                                                <option value="4">Comorbidity</option>
                                            </select>
                                        </div>
                                        <div class="col-7">
                                            <label for="alasanTacc" class="form-label fw-semibold">
                                                Alasan
                                            </label>
                                            <input type="text" class="form-control"
                                                id="alasanTacc"
                                                name="alasanTacc">
                                            </input>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 d-none" id="noLaporanPolisi">
                                    <div class="row mb-3">
                                        <div class="col-2">
                                            <label for="kdKategori" class="col-form-label">
                                                Nomor LP <span class="text-danger">*</span>
                                            </label>
                                        </div>
                                        <div class="col-10">
                                            <input type="text"
                                                class="form-control"
                                                placeholder="Masukan Nomor Laporan Polisi Disini..."
                                                id="nomorLp"
                                                name="nomorLp">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="row">
                                        <div class="col-5 text-start pt-2">
                                            <button class="btn btn-outline-primary" type="button" id="simpanEntry" name="simpanEntry"><i class="fa fa-floppy-o" aria-hidden="true"></i> Save</button>
                                        </div>
                                        <!-- <div class="col-7 text-end">
                                            <div class="btn-group p-2" role="group" aria-label="Basic outlined example">
                                                <button type="button" class="btn btn-outline-primary p-2" name="cetakSPP"><i class="fa fa-print"></i> SPP</button>
                                                <button type="button" class="btn btn-outline-primary p-2" name="cetakKunjungan"><i class="fa fa-print"></i> Kunjungan</button>
                                                <button type="button" class="btn btn-outline-primary p-2" name="cetakRiwayat"><i class="fa fa-print"></i> Riwayat</button>
                                                <button type="button" class="btn btn-outline-primary p-2" name="cetakRujukan"><i class="fa fa-print"></i> Rujukan</button>
                                            </div>
                                        </div> -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Daftar Faskes -->
    <div class="modal fade" id="modalFaskes" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
            <div class="modal-content shadow-lg rounded-3 border-0">
                <div class="modal-header bg-gradient-primary text-white">
                    <h5 class="modal-title d-flex align-items-center gap-2">
                        <i class="bi bi-hospital fs-3"></i>
                        Daftar Fasilitas Kesehatan
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered align-middle text-center" id="tableRujukan">
                            <thead class="table-dark text-white">
                                <tr>
                                    <th>No</th>
                                    <th>Faskes</th>
                                    <th>Kelas</th>
                                    <th>Kantor Cabang</th>
                                    <th>Alamat</th>
                                    <th>Telp</th>
                                    <th>Jarak</th>
                                    <th>Total Rujukan</th>
                                    <th>Kapasitas</th>
                                    <th>%</th>
                                    <th>Jadwal</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data akan diisi JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    require '../admin/library.php';
    ?>
    <script src="controller/admisi/helper.js"></script>
    <script src="controller/admisi/kunjungan.js"></script>
</body>

</html>