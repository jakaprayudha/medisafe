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
                            <button class="btn btn-sm btn-success pilih"
                                data-id="${data.id}"
                                data-nama="${data.tindakan}">
                                Pilih
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
                <td>${item.noKunjungan}</td>
                <td>${item.tglDaftar}</td>
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
            data: {kd: nokdTkp, jnskelamin: kelamin},
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
                        `<option value="${item.kdTindakan}" data-biaya="${item.maxTarif}" data-nama"${item.nmTindakan}">
                        ${item.kdTindakan} - ${item.nmTindakan}
                    </option>`
                    );
                });
                $select.prop('disabled', false).trigger('change');
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
});
