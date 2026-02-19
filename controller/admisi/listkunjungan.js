window.APP = window.APP || {};
$(function () {
    $('#btnCariPasien').prop('disabled', true);
    $('#noKartuSearch').on('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value.length > 12) {
            this.value = this.value.substring(0, 13);
            $('#btnCariPasien').prop('disabled', false);
        } else {
            $('#btnCariPasien').prop('disabled', true);
        }
    })
    $('#btnCariPasien').on('click', function () {
        loadTable();
    })
    $(document).on('click', '.btn-edit', function () {
        const data = $(this).data('item');
        const nokunjung = data.noKunjungan;
        Swal.fire({
            title: "Konformasi",
            text: "Edit Kunjungan: " + nokunjung + "?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Ya"
        }).then((result) => {
            if (result.isConfirmed) {
                sessionStorage.setItem('dataPasien', JSON.stringify(data));
                window.location.href = 'module/admisi/listkunjungan.php';
            }
        });
    })
    $(document).on('click', '.btn-delete', function () {
        const btn = $(this);
        const no = btn.data('nokunjung');
        const tgl = btn.data('tgl');
        const poli = btn.data('poli');
        const kartu = btn.data('kartu');
        Swal.fire({
            title: "Apakah Kamu Yakin?",
            text: "Menghapus Kunjungan",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Ya, Hapus"
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
                                icon: "success"
                            });
                            loadTable();
                        } else {
                            Swal.fire({
                                title: "Warning",
                                text: res.message,
                                icon: "success"
                            });
                        }
                    }
                })
            }
        });
    })
    function loadTable() {
        const noKartu = $('#noKartuSearch').val();
        const btn = $('#btnCariPasien');
        $.ajax({
            url: 'controller/admisi/services/getDataKunjungan.php',
            type: "GET",
            dataType: "json",
            data: {
                nokartu: noKartu
            },
            beforeSend: function () {
                APP.load_btn_aktif(btn);
            },
            success: function (res) {
                let data = Array.isArray(res.list) ? res.list : [res.list];
                let tbody = $('#datapasien tbody');
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
                            <td>
                                <button class="btn btn-sm btn-secondary btn-edit" data-item='${JSON.stringify(item)}'>
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button class="btn btn-sm btn-danger btn-delete" data-nokunjung="${item.noKunjungan}" data-tgl="${item.tglDaftar}" data-poli="${item.kdPoli}" data-kartu="${item.noKartu}"}>
                                    <i class="bi bi-file-earmark-x"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    tbody.append(row);
                });
            },
            complete: function () {
                APP.load_btn_non(btn, `<i class="bi bi-search me-1"></i> Cari`);
            }
        })
    }
});
