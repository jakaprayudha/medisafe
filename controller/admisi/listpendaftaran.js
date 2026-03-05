window.APP = window.APP || {};
$(function () {
    flatpickr("#tanggal", {
        dateFormat: "Y-m-d",
        altFormat: "F j, Y",
        defaultDate: "today",
        maxDate: 'today'
    });
    var table = $('#datapasien').DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        ajax: {
            url: 'controller/admisi/services/getDataPasien.php',
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
            { data: 'noUrut' },
            { data: 'noKartu' },
            { data: 'patient_name' },
            { data: 'nmPoli' },
            {
                data: 'kdTkp',
                render: function (data, type, row) {
                    if (data == "10") {
                        return `Rawat Jalan`;
                    } else if (data == "20") {
                        return `Rawat Inap`;
                    } else {
                        return `Rawat Promotif`;
                    }
                }
            },
            {
                data: 'status_kunjungan',
                render: function (data, type, row) {
                    if (data == 1) {
                        return `<span class="badge bg-success">Sudah Dilayani</span>`;
                    } else {
                        return `<span class="badge bg-danger">Belum Dilayani</span>`;
                    }
                }
            },
            {
                data: null,
                render: function (data, type, row) {
                    let btn = "";
                    if (row.status_kunjungan == '0') {
                        btn = `<div class="d-flex justify-content-center gap-1">
                            <button class="btn btn-sm btn-outline-danger btn-batal">
                                Batal
                            </button>
                            <button class="btn btn-sm btn-outline-primary btn-kunjungan">
                                Kunjungan
                            </button>
                        </div>`;
                    }
                    return btn;
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
        let data = table.row($(this).closest('tr')).data();
        const nokartu = data.noKartu;
        const tanggal = data.tanggal_daftar;
        const noUrut = data.noUrut;
        const kdpoli = data.kdPoli;
        const btn = $(this);
        Swal.fire({
            title: "Apakah Kamu Yakin?",
            text: "Menghapus Pendaftaran",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Ya, Hapus",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'controller/admisi/services/deletePendaftaran.php',
                    type: "POST",
                    data: {
                        nokartu: nokartu,
                        tanggal: tanggal,
                        noUrut: noUrut,
                        kdpoli: kdpoli
                    },
                    dataType: 'json',
                    beforeSend: function(){
                        APP.load_btn_aktif(btn);
                    },
                    complete: function(){
                        APP.load_btn_non(btn, 'Batal');
                    },
                    success: function (res) {
                        if (res.success) {
                            Swal.fire({
                                title: "Berhasil",
                                html: `
                            <b>${res.message}</b><br></b>
                        `,
                                icon: "success",
                                confirmButtonText: "Tutup",
                            });
                            table.ajax.reload();
                        } else {
                            Swal.fire({
                                title: "Gagal Hapus",
                                text: res.message,
                                icon: "error",
                                confirmButtonText: "Tutup"
                            });
                        }
                    }
                })
            }
        });
    })
    $('#searchLocal').on('keyup', function () {
        var value = this.value.toLowerCase();
        table.rows().every(function () {
            var data = this.data();
            var found =
                (data.noUrut || '').toLowerCase().includes(value) ||
                (data.peserta?.noKartu || '').toLowerCase().includes(value) ||
                (data.peserta?.nama || '').toLowerCase().includes(value) ||
                (data.poli?.nmPoli || '').toLowerCase().includes(value);

            if (found) {
                $(this.node()).show();
            } else {
                $(this.node()).hide();
            }
        });
    });
    $(document).on('click', '.btn-kunjungan', function () {
        let data = table.row($(this).closest('tr')).data();
        sessionStorage.setItem('dataPasien', JSON.stringify(data));
        window.location.href = 'module/admisi/listkunjungan.php';
    })
})