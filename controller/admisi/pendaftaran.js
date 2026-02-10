window.APP = window.APP || {};
$(function () {
    $('#cari').on('click', function () {
        const btn = $(this);
        APP.load_btn_aktif(btn);
        const nomor = $('#nomor').val();
        if (nomor.length != "") {
            let pilih = '';
            if (nomor.length == 16) {
                pilih = "nik";
            } else {
                pilih = "noka";
            }
            $.ajax({
                url: 'controller/admisi/services/getPasien.php',
                type: 'GET',
                data: {
                    tipe: pilih,
                    nokartu: nomor
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        $('#tampilan').load("controller/admisi/pages/viewpendaftaran.php", function () {
                            APP.initLoadfunction();
                            APP.cetakhtml('#noK', response.data.noKartu);
                            APP.cetakhtml('#nama', response.data.nama);
                            APP.cetakhtml('#tglLahir', response.data.tglLahir);
                            APP.cetakhtml('#kelamin', response.data.sex == "P" ? "Perempuan" : "Laki - Laki");
                            APP.cetakhtml('#ppkumum', response.data.kdProviderPst['nmProvider']);
                            APP.cetakhtml('#noTelp', response.data.noHP);
                            APP.cetak('#noKartu', response.data.noKartu);
                            APP.cetak('#kdProviderPeserta', response.data.kdProviderPst['kdProvider']);
                        })
                    } else {
                        Swal.fire({
                            title: "Opss..",
                            text: "Data Tidak Ditemukan",
                            icon: "error"
                        });
                    }
                    APP.load_btn_non(btn, 'Cari');
                },
                error: function (xhr, status, error) {
                    Swal.fire({
                        title: "Opss..",
                        text: "Terjadi Kesalahan",
                        icon: "error"
                    });
                    APP.load_btn_non(btn, 'Cari');
                }
            })
        } else {
            Swal.fire({
                title: "Opss..",
                text: "Nomor Pencarian Tidak Boleh Kosong!",
                icon: "error"
            });
            APP.load_btn_non(btn, 'Cari');
        }
    });
    APP.initLoadfunction = function () {
        $('#kodepoli').select2();
        var kunjungan = $('#kunjungan');
        kunjungan.empty();
        kunjungan.append($('<option></option>').attr('value', '10').text('Rawat Jalan'));
        kunjungan.append($('<option></option>').attr('value', '20').text('Rawat Inap'));
        kunjungan.append($('<option></option>').attr('value', '50').text('Promotif Preventif'));
        APP.updatePoliOptions($('#kunjSakit').val());
        $('#create').click(function () {
            APP.load_btn_aktif('#create');
            APP.createpeserta();
        });
        $('#kunjSakit').change(function () {
            var selectedValue = $(this).val();
            APP.updatePoliOptions(selectedValue);
            if (this.value == 'true') {
                kunjungan.empty();
                kunjungan.append($('<option></option>').attr('value', '10').text('Rawat Jalan'));
                kunjungan.append($('<option></option>').attr('value', '20').text('Rawat Inap'));
                kunjungan.append($('<option></option>').attr('value', '50').text('Promotif Preventif'));
                $('#textpoli').text('Poli tujuan');
            } else {
                kunjungan.empty();
                kunjungan.append($('<option></option>').attr('value', '10').text('Rawat Jalan'));
                $('#textpoli').text('Kegiatan');
            }
        });
    }
    APP.createpeserta = function () {
        var data = $('#isiform').serialize();
        $.ajax({
            type: "POST",
            data: data,
            dataType: "json",
            url: 'pcare/proses_pendaftaran.php',
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: "Nomor Antrian: " + response.antrian,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ok'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            location.reload();
                        }
                    })
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: response.message,
                    });
                }
            },
            error: function (xhr, status, error) {
                alert('Terjadi kesalahan saat melakukan AJAX request: ' + error);
                load_btn_non('#create', "Create");
            },
            complete: function () {
                load_btn_non('#create', "Create");
            }
        })
    }
    APP.updatePoliOptions = function (poliSakit) {
        $.ajax({
            url: 'controller/admisi/services/get_api.php',
            type: 'POST',
            dataType: 'json',
            success: function (response) {
                var select = $('#kodepoli');
                select.empty();
                $.each(response.list, function (index, item) {
                    if (item['poliSakit'].toString() == poliSakit) {
                        select.append($('<option></option>').attr('value', item['kdPoli']).text(item['nmPoli']));
                    }
                });
            },
            error: function (xhr, status, error) {
                console.log(xhr.responseText);
            }
        });
    }
})