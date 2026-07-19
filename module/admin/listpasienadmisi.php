<?php
$title = 'Registrasi Poliklinik';
require '../../controller/view.php';
?>
<!doctype html>
<html lang="en">

<head>
    <base href="../../">
    <?php
    require '../../assets/template/head.php';
    ?>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style id="fixcss">
        .dropdown-menu {
            z-index: 999999 !important;
            min-width: 200px;
            max-width: 250px;
            width: auto;
            white-space: normal;
        }

        /* wrapper utama */
        .dataTables_wrapper {
            overflow: visible !important;
        }

        /* scroll container */
        .dataTables_scroll {
            overflow: visible !important;
        }

        /* biarkan scroll tetap jalan */
        .dataTables_scrollBody {
            overflow-x: auto !important;
            overflow-y: hidden !important;
        }



        /* responsive table */
        .table-responsive {
            overflow: visible !important;
        }

        /* card container */
        .card {
            overflow: visible !important;
        }

        .dropup .dropdown-menu {
            top: auto !important;
            bottom: 100% !important;
            margin-bottom: 5px;
            transform: none !important;
        }

        #cameraModal .modal-body {
            position: relative;
        }

        #cameraModal video {
            width: 100%;
        }

        #cameraModal canvas {
            position: absolute;
            top: 0;
            left: 0;
        }

        /* 🔥 freeze kolom pertama */
        #periodeTable th:first-child,
        #periodeTable td:first-child {
            position: sticky;
            left: 0;
            z-index: 5;
            background: #fff;
        }

        /* header lebih tinggi z-index */
        #periodeTable thead th:first-child {
            z-index: 6;
        }
    </style>
</head>

<body>
    <!--  Body Wrapper -->
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">
        <!-- Sidebar Start -->
        <?php
        require 'sidebar.php';
        ?>
        <!--  Sidebar End -->
        <!--  Main wrapper -->
        <div class="body-wrapper">
            <!--  Header Start -->
            <?php
            require 'navbar.php';
            ?>
            <!--  Header End -->
            <div class="body-wrapper-inner">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-12 d-flex align-items-stretch">
                            <div class="card w-100">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <h5 class="card-title fw-semibold">Data Mobile-JKN</h5>
                                        <div class="d-flex align-items-end gap-2 flex-wrap">
                                            <div class="col-auto">
                                                <button type="button" data-bs-toggle="modal" data-bs-target="#filterModal" class="btn btn-dark">
                                                    <i class="fas fa-filter"></i> Filter
                                                </button>
                                            </div>
                                            <div class="col-auto">
                                                <button type="button" id="btnReset" class="btn btn-light">
                                                    <i class="fas fa-undo"></i> Reset
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTable">
                                            <thead>
                                                <tr>
                                                    <th scope="col" class="text-dark fw-normal text-center">Actions</th>
                                                    <th scope="col" class="text-dark fw-normal text-center">Status</th>
                                                    <th scope="col" class="text-dark fw-normal">No.BPJS</th>
                                                    <th>Antrian</th>
                                                    <th class="text-dark fw-normal">Tanggal</th>
                                                    <th scope="col" class="text-dark fw-normal">Nama Pasien</th>
                                                    <th scope="col" class="text-dark fw-normal">P/L</th>
                                                    <th scope="col" class="text-dark fw-normal">Dokter</th>
                                                    <th scope="col" class="text-dark fw-normal">Poli</th>

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
    </div>
    <?php
    require 'library.php';
    ?>
    <script src="controller/admisi/helper.js"></script>
    <script>
        var table = $('#periodeTable').DataTable({
            processing: true,
            serverSide: false,
            responsive: true,
            scrollX: true,

            ajax: {
                url: 'controller/admisi/services/listpatientjkn.php',
                type: 'GET',
                dataSrc: function(json) {
                    return json.data.map(function(row) {
                        let statusClass = '';
                        let statusText = '';
                        if (row.visit_status == '10') {
                            statusClass = 'bg-warning';
                            statusText = 'Belum Check-in';
                        } else if (row.visit_status == '99') {
                            statusClass = 'bg-danger';
                            statusText = 'Batal';
                        } else {
                            statusClass = 'bg-success';
                            statusText = 'Sudah Check-in';
                        }
                        let actionBtn = '';

                        if (row.visit_status == '10') {
                            actionBtn = `
                            <a href="#" 
                                class="btn btn-sm btn-secondary btn-checkin"
                                data-visit="${row.visit_ID}"
                                title="Check-in Pasien">
                                <i class="ti ti-check"></i>
                            </a>
                            <a href="#"
                                class="btn btn-sm btn-danger btn-batal ms-1"
                                data-visit="${row.visit_ID}"
                                title="Batalkan Antrean">
                                <i class="ti ti-x"></i>
                            </a>

                        `;
                        }

                        return {
                            actions: `<div class="text-center">${actionBtn}</div>`,
                            status: `
                                <div class="text-center">
                                    <span class="badge ${statusClass}">
                                        ${statusText}
                                    </span>
                                </div>
                            `,

                            nobpjs: row.no_bpjs ?? '-',
                            antrian: row.visit_antrian,
                            tanggal: row.visit_date,
                            nama: row.patient_name,
                            gender: row.patient_gender,
                            dokter: row.doctor_name,
                            poli: row.poli_name,
                            screening: row.screening ?? '-',
                            bayar: row.provider_name
                        };
                    });
                }
            },

            columns: [{
                    data: 'actions'
                },
                {
                    data: 'status'
                },
                {
                    data: 'nobpjs'
                },
                {
                    data: 'antrian'
                },
                {
                    data: 'tanggal'
                },
                {
                    data: 'nama'
                },
                {
                    data: 'gender'
                },
                {
                    data: 'dokter'
                },
                {
                    data: 'poli'
                }
            ]
        });
        $(document).on('click', '.btn-checkin', function(e) {
            e.preventDefault();

            let $btn = $(this);
            let visit = $btn.data('visit');
            let originalHtml = $btn.html();

            Swal.fire({
                title: 'Check-in pasien?',
                text: 'Pastikan pasien sudah hadir',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Check-in',
                cancelButtonText: 'Batal'
            }).then((result) => {

                if (result.isConfirmed) {

                    // 🔥 loading di tombol
                    $btn.prop('disabled', true);
                    $btn.html('<span class="spinner-border spinner-border-sm"></span>');

                    // 🔥 loading popup
                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Sedang melakukan check-in',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: 'controller/admisi/services/chackin.php',
                        type: 'POST',
                        data: {
                            visit: visit
                        },
                        dataType: 'json',

                        success: function(res) {
                            if (res.success) {

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: res.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                });

                                table.ajax.reload(null, false);

                            } else {

                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: res.message
                                });

                                // balikin tombol
                                $btn.prop('disabled', false);
                                $btn.html(originalHtml);
                            }
                        },

                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Terjadi kesalahan server'
                            });

                            $btn.prop('disabled', false);
                            $btn.html(originalHtml);
                        }
                    });

                }

            });
        });
        $(document).on("click", ".btn-batal", function(e) {
            e.preventDefault();
            let visit = $(this).data("visit");
            Swal.fire({
                title: "Batalkan Antrean?",
                text: "Pasien akan dibatalkan dari antrean.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Ya, Batalkan",
                cancelButtonText: "Tidak"
            }).then((result) => {
                if (!result.isConfirmed) return;
                Swal.fire({
                    title: "Memproses...",
                    text: "Mohon tunggu",
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                $.ajax({
                    url: "controller/admisi/services/batalchackin.php",
                    type: "POST",
                    dataType: "json",
                    data: {
                        visit_ID: visit
                    },
                    success: function(res) {
                        if (res.success) {
                            Swal.fire({
                                icon: "success",
                                title: "Berhasil",
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                table.ajax.reload(null, false);
                            });
                        } else {
                            Swal.fire(
                                "Gagal",
                                res.message,
                                "error"
                            );
                        }
                    },
                    error: function() {
                        Swal.fire(
                            "Error",
                            "Terjadi kesalahan server.",
                            "error"
                        );
                    }
                });
            });
        });
    </script>
</body>
<div class="modal fade" id="filterModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Filter Data</h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-6 mb-3">
                        <label for="fromDate" class="form-label mb-0">Dari</label>
                        <input type="date" id="fromDate" name="fromDate" class="form-control">
                    </div>
                    <div class="col-6 mb-3">
                        <label for="toDate" class="form-label mb-0">Sampai</label>
                        <input type="date" id="toDate" name="toDate" class="form-control">
                    </div>
                    <div class="col-12 mb-3">
                        <label for="doctorSelect" class="form-label mb-0">Dokter</label>
                        <select name="doctorSelect" class="form-select" id="doctorSelect">
                            <option value="">Semua Dokter</option>
                        </select>
                    </div>
                    <div class="col-12 mb-3">
                        <label for="providerSelect" class="form-label mb-0">Provider</label>
                        <select name="providerSelect" class="form-select" id="providerSelect">
                            <option value="">Semua Metode Pembayaran</option>
                        </select>
                    </div>
                    <div class="col-12 mb-3">
                        <label for="poliSelect" class="form-label mb-0">Poliklinik</label>
                        <select name="poliSelect" class="form-select" id="poliSelect">
                            <option value="">Semua Poliklinik</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                <button class="btn btn-primary" id="btnApplyFilter">Terapkan Filter</button>
            </div>

        </div>
    </div>
</div>

</html>