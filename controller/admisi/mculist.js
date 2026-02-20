window.APP = window.APP || {};
$(function () {
    flatpickr("#tanggal", {
        dateFormat: "Y-m-d",
        altFormat: "F j, Y",
        altInput: true,
        defaultDate: "today",
    });
    var table = $('#datamcu').DataTable({
        processing: true,
        serverSide: false,
        searching: true,
        ajax: {
            url: 'controller/admisi/services/getDataMCU.php',
            type: 'POST',
            data: function (d) {
                d.tanggal = $('#tanggal').val();
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
            { data: 'created_at' },
            { data: 'noKunjungan' },
            { data: 'noKartu' },
            { data: 'patient_name' },
            {
                data: null,
                render: function (data, type, row) {
                    return `<div class="d-flex justify-content-center gap-1">
                            <button class="btn btn-sm btn-danger btn-batal" data-noknj="${row.noKunjungan}" data-nomcu="${row.kdMCU}">
                                Hapus
                            </button>
                        </div>`
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
    $('#tanggal').on('change', function () {
        table.ajax.reload();
    });
    $(document).on('click', '.btn-batal', function () {
        const btn = $(this);
        const nokunjung = btn.data('noknj');
        const nomcu = btn.data('nomcu');
        Swal.fire({
            title: "Apakah Kamu Yakin?",
            text: "Menghapus MCU",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Ya, Hapus"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "controller/admisi/services/deletemcu.php",
                    type: "POST",
                    dataType: "json",
                    data: { noKunjung: nokunjung, kdmcu: nomcu },
                    beforeSend: function () {
                        APP.load_btn_aktif(btn);
                    },
                    complete: function () {
                        APP.load_btn_non(btn, 'Hapus');
                    },
                    success: function (res) {
                        if (res.success) {
                            Swal.fire({
                                title: "Sucess",
                                text: res.message,
                                icon: "success"
                            });
                            table.ajax.reload();
                        } else {
                            Swal.fire({
                                title: "Warning",
                                text: res.message,
                                icon: "error"
                            });
                        }
                    }
                })
            }
        });
    })
})