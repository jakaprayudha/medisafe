window.APP = window.APP || {}
$(function () {
    flatpickr("#tglSearch", {
        dateFormat: "d-m-Y",
        altFormat: "F j, Y",
        altInput: true,
        defaultDate: "today",
    });
    flatpickr("#tglPelayanan", {
        dateFormat: "d-m-Y",
        altFormat: "F j, Y",
        altInput: true,
        defaultDate: "today",
    });
    $('#idKegiatan').select2({
        width: "100%",
        allowClear: true,
        dropdownParent: $('#modalKegiatan'),
        data: [
            {
                id: "01",
                text: "Senam",
            },
            {
                id: "10",
                text: "Penyuluhan"
            },
            {
                id: "11",
                text: "Penyuluhan dan Senam"
            }
        ]
    })
    $('#idkelompok').select2({
        width: "100%",
        allowClear: true,
        dropdownParent: $('#modalKegiatan'),
        data: [
            {
                id: "01",
                text: "Diabetes Melitus",
            },
            {
                id: "02",
                text: "Hipertensi"
            }
        ]
    })
    $('#idClpprolanis').select2({
        width: '100%'
    });
    var table = $('#datakegiatan').DataTable({
        processing: true,
        serverSide: false,
        searching: true,
        ajax: {
            url: 'controller/admisi/services/getListKegiatan.php',
            type: 'GET',
            data: function (d) {
                d.tanggal = $('#tglSearch').val();
            },
            dataSrc: function (json) {
                return json.data;
            }
        },
        columns: [
            {
                data: null,
                render: function (data, type, row, meta) {
                    return meta.row + 1;
                }
            },
            { data: 'tglPelayanan', defaultContent: '-' },
            { data: 'clubProl.jnsKelompok.nmProgram', defaultContent: '-' },
            { data: 'clubProl.alamat', defaultContent: '-' },
            { data: 'kegiatan.nama', defaultContent: '-' },
            { data: 'materi', defaultContent: '-' },
            { data: 'clubProl.ketua_nama', defaultContent: '-' },
            { data: 'clubProl.ketua_noHP', defaultContent: '-' },

            {
                data: null,
                render: function (data, type, row) {
                    return `<div class="d-flex justify-content-center gap-1">
                            <button class="btn btn-sm btn-danger btn-batal">
                                Hapus
                            </button>
                        </div>`;
                }
            }
        ],
        pageLength: 10,
        language: {
            search: "Pencarian:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            zeroRecords: "Data tidak ditemukan",
            infoEmpty: "Tidak ada data tersedia"
        }
    });
    $('#tglSearch').on('change', function () {
        table.ajax.reload();
    });
    $('#addTambahKelompok').on('click', function () {
        $('#modalKegiatan').modal('show');
    })
    $('#idbiaya').on('input', function () {
        let value = $(this).val().replace(/[^0-9]/g, '');
        if (value.length) {
            let format = new Intl.NumberFormat('id-ID').format(value);
            $(this).val('Rp ' + format);
        } else {
            $(this).val('');
        }
    })
    $(document).on('click', '#btnInsertKegiatan', function () {
        let data = $('#formKegiatan').serialize();
        const btn = $(this);
        $.ajax({
            url: "controller/admisi/services/insertKelompok.php",
            type: "POST",
            dataType: 'json',
            data: data,
            beforeSend: function () {
                APP.load_btn_aktif(btn);
            },
            complete: function () {
                APP.load_btn_non(btn, 'Simpan');
            },
            success: function (res) {
                if (res.success) {
                    Swal.fire({
                        title: res.message,
                        icon: "success"
                    });
                    table.ajax.reload();
                    $('#formKegiatan')[0].reset();
                    $('.select2').val(null).trigger('change');
                    $('#modalKegiatan').modal('hide');
                } else {
                    Swal.fire({
                        title: res.message,
                        icon: "error"
                    });
                }
            }
        })
    })
    $('#idClpprolanis').select2({
        width: '100%',
        placeholder: 'Pilih Club',
        dropdownParent: $('#modalKegiatan'),
        ajax: {
            url: 'controller/admisi/services/getDataClub.php',
            dataType: 'json',
            delay: 250,
            data: function () {
                return {
                    id: $('#idkelompok').val()
                };
            },
            processResults: function (response) {
                if (!response.success || !response.data || response.data.length === 0) {
                    return {
                        results: []
                    };
                }
                let mapped = $.map(response.data, function (item) {
                    return {
                        id: item.clubId,
                        text: item.nama + " - " + item.jnsKelompok.nmProgram
                    };
                });

                return {
                    results: mapped
                };
            }
        },
        language: {
            searching: function () {
                return "Sedang memuat data...";
            },
            noResults: function () {
                return "Data tidak ada";
            }
        }
    });
    $('#idClpprolanis').prop('disabled', true);
    $('#idkelompok').on('change', function () {

        let kelompok = $(this).val();

        $('#idClpprolanis')
            .val(null)
            .trigger('change');

        if (kelompok) {
            $('#idClpprolanis').prop('disabled', false);
        } else {
            $('#idClpprolanis').prop('disabled', true);
        }
    });

})