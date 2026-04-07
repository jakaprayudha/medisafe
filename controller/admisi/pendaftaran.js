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
                            APP.cetak('#namapatient', response.data.nama);
                            APP.cetak('#Kelamin', response.data.sex == "P" ? "Perempuan" : "Laki - Laki");
                            APP.cetak('#tgllahir', response.data.tglLahir);
                            if (response.data.noKTP == null || response.data.noKTP == ""){
                                let nomor = $('#nomor').val();
                                APP.cetak('#noNIK', nomor);
                                APP.cetakhtml('#nonik', nomor);
                            }else{
                                APP.cetak('#noNIK', response.data.noKTP);
                                APP.cetakhtml('#nonik', response.data.noKTP);
                            }
                            APP.cetak('#kdProviderPeserta', response.data.kdProviderPst['kdProvider']);
                        })
                    } else {
                        Swal.fire({
                            title: "Opss..",
                            text: response.message,
                            icon: "error",
                            showDenyButton: true,
                            confirmButtonText: "Daftar",
                            denyButtonText: "Kembali"
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = "module/admisi/registrasi-new";
                            } else if (result.isDenied) {
                                Swal.close();
                            }
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
        flatpickr("#tanggalKunjung", {
            dateFormat: "Y-m-d",
            altFormat: "F j, Y",
            defaultDate: "today",
            maxDate: "today"
        });
        APP.ambil_data('#kodedokter', 'dokter/0/100', 'nmDokter', 'nmDokter', false);
        APP.updatePoliOptions = function (poliSakit) {
            poliSakit = (poliSakit === true || poliSakit === 'true');
            var select = $('#kodepoli');
            select.empty();
            select.prop('disabled', true);
            select.html('<option value="">Mencari data...</option>');
            select.val('').trigger('change');
            $.ajax({
                url: 'controller/admisi/services/getPoli.php',
                type: 'POST',
                dataType: 'json',
                success: function (response) {
                    if (!response.success) {
                        console.log('Gagal load poli');
                        return;
                    }
                    select.empty();
                    $.each(response.data, function (index, item) {
                        if (item.poliSakit == poliSakit) {
                            select.append(new Option(item.nmPoli, item.kdPoli, false, false));
                        }
                    });
                    select.prop('disabled', false);
                    select.trigger('change');
                },
                error: function (xhr, status, error) {
                    console.log(xhr.responseText);
                }
            });
        }
        $("#kodepoli").on("change", function () {
            let nmPoli = $("#kodepoli option:selected").text();
            $("#nmPoli").val(nmPoli);
        });
        const knjsakit = [
            {
                id: "10",
                text: "Rawat Jalan",
            },
            {
                id: "20",
                text: "Rawat Inap"
            },
            {
                id: "50",
                text: "Promotif Preventif"
            }
        ]
        $("#kunjungan").select2({
            width: "100%",
        });
        $('#kodepoli').select2({
            width: '100%',
            language: {
                noResults: function () {
                    return "Poli tidak ditemukan";
                }
            }
        });
        $('#kodedokter').select2({
            width: '100%',
            language: {
                noResults: function () {
                    return "Dokter tidak ditemukan";
                }
            }
        });
        $('#kodeprov').select2({
            width: '100%',
            language: {
                noResults: function () {
                    return "Provider tidak ditemukan";
                }
            },
            ajax: {
                url: 'controller/admisi/services/get_provider.php',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        search: params.term
                    };
                },
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                id: item.id,
                                text: item.text
                            };
                        })
                    };
                },
                cache: true
            },
            minimumInputLength: 1 
        });
        $('#kunjSakit').select2({
            width: "100%",
            placeholder: "Jenis Kunjungan",
            allowClear: false,
            data: [
                {
                    id: "true",
                    text: "Kunjungan Sakit"
                },
                {
                    id: "false",
                    text: "Kunjungan Sehat"
                }
            ]
        })
        APP.jnsKunjungvalue = function () {
            var selectedValue = $('#kunjSakit').val();
            APP.resetSelect('#kunjungan');
            APP.updatePoliOptions(selectedValue);
            if (selectedValue == 'true') {
                knjsakit.forEach((item) => {
                    $('#kunjungan').append(new Option(item.text, item.id, false, false));
                });
                $("#kunjungan").val("10").trigger("change");
            } else {
                APP.addValueSelect('#kunjungan', '10', 'Rawat Jalan');
            }
        }
        APP.jnsKunjungvalue();
        $('#create').click(function () {
            APP.load_btn_aktif('#create');
            APP.createpeserta();
        });
        $('#kunjSakit').change(function () {
            APP.jnsKunjungvalue();
        });
    }
    APP.createpeserta = function () {
        var data = $('#isiform').serialize();
        $.ajax({
            type: "POST",
            data: data,
            dataType: "json",
            url: 'controller/admisi/services/insertPendaftaran.php',
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ok'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.assign("module/admisi/registrasi-poliklinik");
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
                APP.load_btn_non('#create', "Create");
            },
            complete: function () {
                APP.load_btn_non('#create', "Create");
            }
        })
    }
})