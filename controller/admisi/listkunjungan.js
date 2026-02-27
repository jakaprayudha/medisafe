window.APP = window.APP || {};
$(function () {
    sessionStorage.setItem('dataMcu', JSON.stringify([]));
    sessionStorage.setItem('dataKunjungan', JSON.stringify([]));
    $("#btnCariPasien").prop("disabled", true);
    $("#noKartuSearch").on("input", function () {
        this.value = this.value.replace(/[^0-9]/g, "");
        if (this.value.length > 12) {
            this.value = this.value.substring(0, 13);
            $("#btnCariPasien").prop("disabled", false);
        } else {
            $("#btnCariPasien").prop("disabled", true);
        }
    });
    $("#btnCariPasien").on("click", function () {
        loadTable();
    });
    $(document).on("click", ".btn-edit", function () {
        const data = $(this).data("item");
        const nokunjung = data.noKunjungan;
        Swal.fire({
            title: "Konformasi",
            text: "Edit Kunjungan: " + nokunjung + "?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Ya",
        }).then((result) => {
            if (result.isConfirmed) {
                sessionStorage.setItem("dataPasien", JSON.stringify(data));
                window.location.href = "module/admisi/listkunjungan.php";
            }
        });
    });
    $(document).on("click", ".btn-delete", function () {
        const btn = $(this);
        const no = btn.data("nokunjung");
        const tgl = btn.data("tgl");
        const poli = btn.data("poli");
        const kartu = btn.data("kartu");
        Swal.fire({
            title: "Apakah Kamu Yakin?",
            text: "Menghapus Kunjungan",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Ya, Hapus",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "controller/admisi/services/deleteKunjungan.php",
                    type: "POST",
                    dataType: "json",
                    data: { nomor: no, tanggal: tgl, poli: poli, kartu: kartu },
                    beforeSend: function () {
                        APP.load_btn_aktif(btn);
                    },
                    complete: function () {
                        APP.load_btn_non(btn, `<i class="bi bi-file-earmark-x"></i>`);
                    },
                    success: function (res) {
                        if (res.success) {
                            Swal.fire({
                                title: "Sucess",
                                text: res.message,
                                icon: "success",
                            });
                            loadTable();
                        } else {
                            Swal.fire({
                                title: "Warning",
                                text: res.message,
                                icon: "error",
                            });
                        }
                    },
                });
            }
        });
    });
    $(document).on("click", ".btn-mcu", function () {
        const data = $(this).data("item");
        sessionStorage.setItem("dataKunjungan", JSON.stringify(data));
        window.location.href = "module/admisi/formmcu.php";
    });
    let noKnjtindakan = null;
    let nokdTkp = null;
    let kelamin = null;
    $(document).on('click', '.btn-tindakan', function () {
        const btn = $(this);
        noKnjtindakan = btn.data('nokunjung');
        nokdTkp = btn.data('kode');
        kelamin = btn.data('klm');
        $('#modalTindakanList').modal('show');
    })
    let tableTindakan;
    $('#modalTindakanList').on('shown.bs.modal', function () {
        if (!$.fn.DataTable.isDataTable('#tableTindakan')) {
            tableTindakan = $('#tableTindakan').DataTable({
                processing: true,
                ajax: {
                    url: 'controller/admisi/services/getDataTindakan.php',
                    type: 'GET',
                    data: function (d) {
                        d.no_kunjungan = noKnjtindakan;
                    },
                    dataSrc: ''
                },
                columns: [
                    { data: 'nmTindakan' },
                    { data: 'keterangan' },
                    { data: 'hasil' },
                    {
                        data: null,
                        orderable: false,
                        className: 'text-center',
                        render: function (data) {
                            return `
                            <button class="btn btn-sm btn-success btn-edit-tindakan"
                            data-id="${data.kdTindakanSK}"
                            data-kd="${data.kdTindakan}"
                            data-nomor="${data.noKunjungan}"
                            data-nama="${data.nmTindakan}"
                            data-biaya="${data.biaya}"
                            data-keterangan="${data.keterangan}"
                            data-hasil="${data.hasil}">
                                Edit
                            </button>
                            <button class="btn btn-sm btn-danger btn-hapus-tindakan"
                                data-id="${data.kdTindakanSK}"
                                data-nomor="${data.noKunjungan}"
                                data-tindakan="${data.nmTindakan}">
                                Hapus
                            </button>
                        `;
                        }
                    }
                ],
                pageLength: 10,
                language: {
                    search: "Pencarian:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    zeroRecords: "Data tindakan tidak ditemukan",
                    infoEmpty: "Tidak ada data tersedia"
                }
            });
        } else {
            tableTindakan.ajax.reload();
        }
    });
    $('#btnTambahTindakan').on('click', function () {
        $('#noKunjungan').val(noKnjtindakan);
        $('#modalTambahTindakan').modal('show');
    });
    function loadTable() {
        noKnjtindakan = null;
        nokdTkp = null;
        kelamin = null;
        const noKartu = $("#noKartuSearch").val();
        const btn = $("#btnCariPasien");
        $.ajax({
            url: "controller/admisi/services/getDataKunjungan.php",
            type: "GET",
            dataType: "json",
            data: {
                nokartu: noKartu,
            },
            beforeSend: function () {
                APP.load_btn_aktif(btn);
            },
            success: function (res) {
                let data = Array.isArray(res.list) ? res.list : [res.list];
                let tbody = $("#datapasien tbody");

                tbody.empty();

                if (!data || data.length === 0) {
                    tbody.append(`
            <tr>
                <td colspan="6" class="text-center text-muted">
                    Data tidak ditemukan
                </td>
            </tr>
        `);
                    return;
                }

                $.each(data, function (index, item) {
                    let row = `
            <tr>
                <td>${index + 1}</td>
                <td>${item.tglDaftar}</td>
                <td>${item.noKunjungan}</td>
                <td>${item.kdTkp == "10" ? 'Rawat Jalan' : item.kdTkp == '20' ? 'Rawat Inap' : 'Promotif Preventif'}</td>
                <td>${item.patient_name}</td>
                <td>${item.nmPoli}</td>
                <td class="text-center">
                <div class="btn-group" role="group">
                    <button
                        type="button"
                        class="btn btn-outline-secondary btn-edit"
                        data-item='${JSON.stringify(item)}'
                        title="Edit Data">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    <button
                        type="button"
                        class="btn btn-outline-danger btn-delete"
                        data-nokunjung="${item.noKunjungan}"
                        data-tgl="${item.tglDaftar}"
                        data-poli="${item.kdPoli}"
                        data-kartu="${item.noKartu}"
                        title="Hapus Data">
                        <i class="bi bi-trash"></i>
                    </button>

                    <button
                        type="button"
                        class="btn btn-outline-primary btn-mcu"
                        data-item='${JSON.stringify(item)}'
                        title="Medical Check Up">
                        <i class="bi bi-heart-pulse"></i>
                    </button>

                    <button
                        type="button"
                        class="btn btn-outline-success btn-tindakan"
                        data-nokunjung="${item.noKunjungan}" data-kode="${item.kdTkp}" data-klm="${item.patient_gender}"
                        title="Tindakan Medis">
                        <i class="bi bi-clipboard2-pulse"></i>
                    </button>

                    <button
                        type="button"
                        class="btn btn-outline-info btn-obat"
                        data-nokunjung="${item.noKunjungan}"
                        title="Resep Obat">
                        <i class="bi bi-capsule"></i>
                    </button>

                </div>
            </td>

            </tr>
        `;
                    tbody.append(row);
                });
                $('[data-bs-toggle="tooltip"]').tooltip();
            },
            complete: function () {
                APP.load_btn_non(btn, `<i class="bi bi-search me-1"></i> Cari`);
            },
        });
    }
    let dataEditTindakan = null;
    $('#modalTambahTindakan').on('shown.bs.modal', function () {
        const $select = $('#kdTindakan');
        $select.empty().append('<option></option>');
        if (!$select.hasClass('select2-hidden-accessible')) {
            $select.select2({
                dropdownParent: $('#modalTambahTindakan'),
                placeholder: 'Sedang mengambil data...',
                allowClear: false,
                width: '100%',
                language: {
                    searching: function () {
                        return 'Sedang mencari data...';
                    },
                    noResults: function () {
                        return 'Data tindakan tidak ditemukan';
                    }
                }
            });
        }
        $.ajax({
            url: 'controller/admisi/services/getRefrensiTindakan.php',
            type: 'GET',
            data: { kd: nokdTkp, jnskelamin: kelamin },
            dataType: 'json',
            beforeSend: function () {
                $select.prop('disabled', true);
            },
            success: function (res) {
                $select.empty()
                $select.empty().append('<option> - Pilih Tindakan - </option>');
                if (!res.data || res.data.length === 0) {
                    $select.trigger('change');
                    return;
                }
                $.each(res.data, function (i, item) {
                    $select.append(
                        `<option value="${item.kdTindakan}" data-biaya="${item.maxTarif}" data-nama="${item.nmTindakan}">
                            ${item.kdTindakan} - ${item.nmTindakan}
                        </option>`
                    );
                });
                $select.prop('disabled', false).trigger('change');
                if (dataEditTindakan) {
                    $select.val(dataEditTindakan.kdTindakan).trigger('change');

                    $('input[name="kdTindakanSK"]').val(dataEditTindakan.kdTindakanSK);
                    $('input[name="noKunjungan"]').val(dataEditTindakan.noKunjung);
                    $('input[name="biaya"]').val(APP.formatRupiah(dataEditTindakan.biaya));
                    $('input[name="nmTindakan"]').val(dataEditTindakan.nmTindakan);
                    $('textarea[name="keterangan"]').val(dataEditTindakan.keterangan);
                    $('input[name="hasil"]').val(dataEditTindakan.hasil);
                }
            },
            error: function () {
                alert('Gagal mengambil data tindakan');
                $select.prop('disabled', false);
            }
        });
    });
    $('#kdTindakan').on('select2:select', function (e) {
        const biaya = $(e.params.data.element).data('biaya');
        const nama = $(e.params.data.element).data('nama');
        $('input[name="biaya"]').val(APP.formatRupiah(biaya));
        $('input[name="nmTindakan"]').val(nama);
    });
    $('#modalTambahTindakan').on('hidden.bs.modal', function () {
        const $select = $('#kdTindakan');
        if ($select.hasClass('select2-hidden-accessible')) {
            $select.select2('destroy');
        }
        $select.empty();
        $('#formTambahTindakan')[0].reset();
    });
    $(document).on('click', '#btnSimpanTindakan', function () {
        const btn = $(this);
        let data = $('#formTambahTindakan').serialize();
        $.ajax({
            url: "controller/admisi/services/insertTindakan.php",
            type: "POST",
            data: data,
            dataType: 'json',
            beforeSend: function () {
                APP.load_btn_aktif(btn);
            },
            complete: function () {
                APP.load_btn_non(btn, 'Simpan');
            },
            success: function (res) {
                if (res.success) {
                    Swal.fire({
                        title: "Success",
                        text: res.message,
                        icon: "success"
                    }).then(() => {
                        $('#modalTambahTindakan').modal('hide');
                        if ($.fn.DataTable.isDataTable('#tableTindakan')) {
                            tableTindakan.ajax.reload(null, false);
                        }
                    });
                } else {
                    Swal.fire({
                        title: "Warning",
                        text: res.message,
                        icon: "error",
                    });
                }
            }
        })
    })
    $(document).on('click', '.btn-hapus-tindakan', function () {
        const btn = $(this);
        const id = btn.data('id');
        const no = btn.data('nomor');
        const nama = btn.data('tindakan');
        Swal.fire({
            title: "Apakah Kamu Yakin?",
            text: "Menghapus Tindakan " + nama + "?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Ya, Hapus",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "controller/admisi/services/deleteTindakan.php",
                    type: "POST",
                    dataType: "json",
                    data: { nomor: no, id: id },
                    beforeSend: function () {
                        APP.load_btn_aktif(btn);
                    },
                    complete: function () {
                        APP.load_btn_non(btn, "Hapus");
                    },
                    success: function (res) {
                        if (res.success) {
                            Swal.fire({
                                title: "Sucess",
                                text: res.message,
                                icon: "success",
                            });
                            if ($.fn.DataTable.isDataTable('#tableTindakan')) {
                                tableTindakan.ajax.reload(null, false);
                            }
                        } else {
                            Swal.fire({
                                title: "Warning",
                                text: res.message,
                                icon: "error",
                            });
                        }
                    },
                });
            }
        });
    })
    $(document).on('click', '.btn-edit-tindakan', function () {
        const btn = $(this);
        dataEditTindakan = {
            kdTindakanSK: btn.data('id'),
            kdTindakan: btn.data('kd'),
            nmTindakan: btn.data('nama'),
            biaya: btn.data('biaya'),
            noKunjung: btn.data('nomor'),
            keterangan: btn.data('keterangan'),
            hasil: btn.data('hasil'),
        };
        $('#modalTambahTindakan').modal('show');
    })
    $(document).on('click', '.btn-obat', function () {
        const btn = $(this);
        noKnjtindakan = btn.data('nokunjung');
        $('#modalListObat').modal('show');
    })
    $('#modalListObat').on('shown.bs.modal', function () {
        if (!$.fn.DataTable.isDataTable('#tableListObat')) {
            tableObat = $('#tableListObat').DataTable({
                processing: true,
                ajax: {
                    url: 'controller/admisi/services/getDataObat.php',
                    type: 'GET',
                    data: function (d) {
                        d.no_kunjungan = noKnjtindakan;
                    },
                    dataSrc: ''
                },
                columns: [
                    { data: 'nmObat' },
                    {
                        data: null,
                        title: 'Signa',
                        render: function (data, type, row) {
                            return row.signa1 + " x " + row.signa2;
                        }
                    },
                    { data: 'jmlObat' },
                    { data: 'jmlPermintaan' },
                    {
                        data: null,
                        orderable: false,
                        className: 'text-center',
                        render: function (data) {
                            return `
                            <button class="btn btn-sm btn-danger btn-hapus-obat"
                                data-kdobat="${data.kdObatSK}"
                                data-nomor="${data.noKunjungan}"
                                data-nama="${data.nmObat}">
                                Hapus
                            </button>
                        `;
                        }
                    }
                ],
                pageLength: 10,
                language: {
                    search: "Pencarian:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    zeroRecords: "Data Obat tidak ditemukan",
                    infoEmpty: "Tidak ada data tersedia"
                }
            });
        } else {
            tableObat.ajax.reload();
        }
    });
    $('#btnTambahObat').on('click', function () {
        APP.addValueInput('#noKunjunganobat', noKnjtindakan);
        $('#modalTambahObat').modal('show');
    })
    $('#btnSimpanObat').on('click', function () {
        let data = $('#formTambahObat').serialize();
        const btn = $(this);
        $.ajax({
            url: "controller/admisi/services/insertObat.php",
            type: "POST",
            data: data,
            dataType: 'json',
            beforeSend: function () {
                APP.load_btn_aktif(btn);
            },
            complete: function () {
                APP.load_btn_non(btn, 'Simpan');
            },
            success: function (res) {
                if (res.success) {
                    Swal.fire({
                        title: "Success",
                        text: res.message,
                        icon: "success"
                    }).then(() => {
                        $('#modalTambahObat').modal('hide');
                        if ($.fn.DataTable.isDataTable('#tableListObat')) {
                            tableObat.ajax.reload(null, false);
                        }
                    });
                } else {
                    Swal.fire({
                        title: "Warning",
                        text: res.message,
                        icon: "error",
                    });
                }
            }
        })
    })
    $('#kdObat').select2({
        dropdownParent: $('#modalTambahObat'),
        placeholder: 'Cari obat...',
        minimumInputLength: 3,
        language: {
            inputTooShort: function (args) {
                const sisa = args.minimum - args.input.length;
                return 'Ketik minimal ' + args.minimum + ' karakter (' + sisa + ' lagi)';
            },
            searching: function () {
                return 'Sedang mencari obat...';
            },
            noResults: function () {
                return 'Obat tidak ditemukan';
            }
        },
        ajax: {
            url: 'controller/admisi/services/getObat.php',
            dataType: 'json',
            delay: 300,
            data: function (params) {
                return {
                    keyword: params.term
                };
            },
            processResults: function (data) {
                return {
                    results: data.data.map(item => ({
                        id: item.kdObat,
                        text: item.nmObat,
                        nmObat: item.nmObat
                    }))
                };
            },
            cache: true
        }
    });
    $('#kdObat').on('select2:select', function (e) {
        const data = e.params.data;
        $('input[name="nmObat"]').val(data.nmObat);
    });
    $('#kdObat').on('select2:open', function () {
        setTimeout(function () {
            document.querySelector('.select2-container--open .select2-search__field')?.focus();
        }, 0);
    });
    $('#modalTambahObat').on('hidden.bs.modal', function () {
        $('#formTambahObat')[0].reset();
        $('#kdObat').val(null).trigger('change');
        $('input[name="nmObat"]').val('');
        $('input[name="kdObatSK"]').val(0);
    });
    $(document).on('click', '.btn-hapus-obat', function () {
        const btn = $(this);
        const id = btn.data('kdobat');
        const nomor = btn.data('nomor');
        const nama = btn.data('nama');
        Swal.fire({
            title: "Apakah Kamu Yakin?",
            text: "Menghapus Obat " + nama + "?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Ya, Hapus",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "controller/admisi/services/deleteObat.php",
                    type: "POST",
                    dataType: "json",
                    data: { no: nomor, kode: id },
                    beforeSend: function () {
                        APP.load_btn_aktif(btn);
                    },
                    complete: function () {
                        APP.load_btn_non(btn, "Hapus");
                    },
                    success: function (res) {
                        if (res.success) {
                            Swal.fire({
                                title: "Sucess",
                                text: res.message,
                                icon: "success",
                            });
                            if ($.fn.DataTable.isDataTable('#tableListObat')) {
                                tableObat.ajax.reload(null, false);
                            }
                        } else {
                            Swal.fire({
                                title: "Warning",
                                text: res.message,
                                icon: "error",
                            });
                        }
                    },
                });
            }
        });
    })
});
