window.APP = window.APP || {};
let type = 'BPJS';
let idPasien = '';
let nomorPasienAktif = '';
let dataPasienUmumAktif = null;
let sedangSinkronProvider = false;
$(function () {
    $('#pasienSelect').select2({
        placeholder: 'Ketik nama,nik,bpjs pasien (min. 2 karakter)...',
        minimumInputLength: 2,
        width: '100%',
        ajax: {
            url: 'controller/admisi/services/getPatient.php',
            dataType: 'json',
            delay: 300,
            cache: true,
            data: function (params) {
                return {
                    nama: params.term || ''
                };
            },
            processResults: function (data) {
                return {
                    results: (data || []).map(item => ({
                        id: item.id,
                        text: item.nama || '-',
                        rm: item.no_rm || '-',
                        nik: item.nik || '-',
                        tgl_lahir: item.tgl_lahir || '-',
                        bpjs: item.no_bpjs || '-'
                    }))
                };
            }
        },
        templateResult: function (data) {
            if (!data.id) return data.text;
            return $(`
            <div class="select2-result-item">
                <div class="nama">${data.text}</div>
                <div class="detail">
                    RM: ${data.rm} • ${data.tgl_lahir} • ${data.nik} • ${data.bpjs} 
                </div>
            </div>
        `);
        },
        templateSelection: function (data) {
            return data.text || 'Pilih pasien';
        },
        language: {
            inputTooShort: function (args) {
                let sisa = args.minimum - args.input.length;
                return sisa === 1 ?
                    'Masukkan 1 karakter lagi' :
                    `Masukkan ${sisa} karakter lagi`;
            },
            noResults: function () {
                return `
                    <div class="d-flex justify-content-between align-items-center" style="gap:10px;">
                        <span>Pasien tidak ditemukan</span>
                        <button type="button" id="btnCariBPJS" class="btn btn-sm btn-primary">
                            Pasien Baru
                        </button>
                    </div>
                `;
            },
            searching: function () {
                return 'Sedang mencari data pasien...';
            },
            errorLoading: function () {
                return 'Gagal mengambil data';
            }
        },
        escapeMarkup: function (markup) {
            return markup;
        }
    });
    $('#pasienSelect').on('select2:select', function (e) {
        resetSemuaForm();
        let data = e.params.data;
        const nomor = data.nik == "" || data.nik == null || data.nik == '-' ? data.bpjs : data.nik;
        idPasien = data.id;
        nomorPasienAktif = nomor;
        loadPasien(nomor);
    });

    function isProviderBpjsKesehatan() {
        const namaProvider = ($('#kodeprov').find(':selected').text() || '').trim().toUpperCase();
        return namaProvider === 'BPJS KESEHATAN';
    }

    function isiDataPasien(data, tipePasien, nomorAsli) {
        const dataProvider = data.kdProviderPst || {};
        APP.cetak('#typePatient', tipePasien);
        APP.cetak('#id_patient', idPasien);
        APP.cetakhtml('#noK', data.noKartu || '-');
        APP.cetakhtml('#nama', data.nama || '-');
        APP.cetakhtml('#tglLahir', data.tglLahir || '-');
        APP.cetakhtml('#kelamin', data.sex === "P" ? "Perempuan" : "Laki - Laki");
        APP.cetakhtml('#ppkumum', dataProvider.nmProvider || '-');
        APP.cetak('#kdProviderPeserta', dataProvider.kdProvider || '');
        APP.cetak('#nohp', data.noHP || '080000000000');
        APP.cetakhtml('#noTelp', data.noHP || '-');
        APP.cetak('#noKartu', data.noKartu || '-');
        APP.cetak('#namapatient', data.nama || '-');
        APP.cetak('#Kelamin', data.sex === "P" ? "Perempuan" : "Laki - Laki");
        APP.cetak('#tgllahir', data.tglLahir || '-');
        APP.cetakhtml('#no_rekammedis', data.rm || '-');
        APP.cetak('#norm', data.rm || '-');
        APP.cetak("#typePasien", tipePasien);
        let nik = data.noKTP || nomorAsli;
        if (nik != null && nik != '' && nik != '-') {
            APP.cetak('#noNIK', nik);
            APP.cetakhtml('#nonik', nik);
        }
    }

    function sinkronProviderDariDataPasien() {
        if (!nomorPasienAktif) return;
        if (!dataPasienUmumAktif) return;

        if (!isProviderBpjsKesehatan()) {
            type = 'UMUM';
            isiDataPasien(dataPasienUmumAktif, 'UMUM', nomorPasienAktif);
            return;
        }

        if (sedangSinkronProvider) return;
        sedangSinkronProvider = true;
        type = 'BPJS';

        Swal.fire({
            title: 'Memuat data BPJS...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        let pilih = nomorPasienAktif.length === 16 ? 'nik' : 'noka';
        $.ajax({
            url: "controller/admisi/services/getPasien.php",
            type: 'GET',
            data: {
                tipe: pilih,
                nokartu: nomorPasienAktif
            },
            dataType: 'json',
            success: function (response) {
                let hasil = null;
                if (response.success && response.data) {
                    hasil = response;
                } else if (response.result && response.result.success && response.result.data) {
                    hasil = response.result;
                }

                if (hasil) {
                    isiDataPasien(hasil.data, 'BPJS', nomorPasienAktif);
                    return;
                }

                isiDataPasien(dataPasienUmumAktif, 'UMUM', nomorPasienAktif);
                Swal.fire({
                    title: "Terjadi kesalahan pada layanan BPJS",
                    text: response.message || "Data BPJS tidak ditemukan",
                    icon: "warning"
                });
            },
            error: function () {
                isiDataPasien(dataPasienUmumAktif, 'UMUM', nomorPasienAktif);
                Swal.fire({
                    title: "Terjadi kesalahan pada layanan BPJS",
                    text: "Terjadi Kesalahan Server",
                    icon: "warning"
                });
            },
            complete: function () {
                sedangSinkronProvider = false;
                if (Swal.isVisible() && Swal.isLoading()) {
                    Swal.close();
                }
            }
        });
    }

    function loadPasien(nomor) {
        type = 'UMUM';
        dataPasienUmumAktif = null;
        $('#tampilan').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary"></div>
                <div class="mt-2 text-muted">Memuat data pasien...</div>
            </div>
        `);
        $.ajax({
            url: "controller/admisi/services/getPasienUmum.php",
            type: 'GET',
            data: {
                tipe: 'nik',
                nokartu: nomor
            },
            dataType: 'json',
            success: function (response) {
                if (response.success && response.data) {
                    dataPasienUmumAktif = response.data;
                    $('#tampilan').load("controller/admisi/pages/viewpendaftaran.php", function () {
                        APP.initLoadfunction();
                        isiDataPasien(response.data, 'UMUM', nomor);
                        sinkronProviderDariDataPasien();
                    });
                } else {
                    resetTampilan();
                    Swal.fire({
                        title: "Tidak Ditemukan",
                        text: response.message || "Data pasien umum tidak ditemukan",
                        icon: "error"
                    });
                }
            },
            error: function () {
                resetTampilan();
                Swal.fire({
                    title: "Opss..",
                    text: "Terjadi Kesalahan Server",
                    icon: "error"
                });
            }
        });
    }

    function resetTampilan() {
        $('#tampilan').html(`
        <div class="text-muted text-center py-4">
            <iconify-icon icon="solar:info-circle-linear" width="22"></iconify-icon>
            <p class="mb-0 mt-2">
                Data pasien akan ditampilkan di sini
            </p>
        </div>
    `);
    }
    $(document).on('click', '#btnCariBPJS', function () {
        $('#pasienSelect').select2('close');
        $('#modalCariBPJS').modal('show');
    });
    $(document).on('select2:open', () => {
        document.querySelector('.select2-container--open .select2-search__field')?.focus();
    });
    $('#btnSearchBPJS').on('click', function () {
        let noKartu = $('#inputNIK').val();
        const btn = $(this);
        let pilih = noKartu.length === 16 ? 'nik' : 'noka';
        $.ajax({
            url: "controller/admisi/services/getPasien.php",
            type: 'GET',
            data: {
                tipe: pilih,
                nokartu: noKartu
            },
            dataType: 'json',
            beforeSend() {
                APP.load_btn_aktif(btn);
                $('#formBPJS')[0].reset();
                $('#formBPJS').find('input').val('');
                $('#formBPJS, #btnTambahPasien').addClass('d-none');
            },
            success: function (response) {
                let hasil = null;
                if (response.success && response.data) {
                    hasil = response;
                } else if (response.result && response.result.success && response.result.data) {
                    hasil = response.result;
                }
                if (hasil) {
                    let d = hasil.data;
                    $('#btnTambahPasien, #formBPJS').removeClass('d-none');
                    APP.cetak('#regNoBPJS', d.noKartu);
                    APP.cetak('#regNama', d.nama);
                    APP.cetak('#regJnsKelamin', d.sex == "P" ? "Perempuan" : "Laki-laki");
                    APP.cetak('#regTglLahir', d.tglLahir);
                    APP.cetak('#noKartuDaftar', noKartu);
                    APP.cetak("#typePasien", "BPJS");
                    if (d.aktif === false) {
                        Swal.fire({
                            title: "Perhatian",
                            text: "Peserta tidak aktif: " + d.ketAktif,
                            icon: "warning"
                        });
                    }
                } else {
                    $('#formBPJS').addClass('d-none');
                    Swal.fire({
                        title: "Warning",
                        text: response.message || "Peserta tidak ditemukan",
                        icon: "error",
                        showDenyButton: true,
                        confirmButtonText: "Daftar",
                        denyButtonText: "Kembali"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "module/admisi/registrasi-new";
                        }
                    });
                }
            },
            complete: function () {
                APP.load_btn_non(btn, "Cari");
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
    })
    $(document).on('click', '#btnTambahPasien', function () {
        const btn = $(this);
        let data = $('#formBPJS').serialize();
        $.ajax({
            url: 'controller/admisi/services/addDataPatient.php',
            type: "POST",
            data: data,
            dataType: 'json',
            beforeSend: function () {
                APP.load_btn_aktif(btn);
            },
            success: function (res) {
                if (res.success) {
                    Swal.fire({
                        title: "Success",
                        text: "Berhasil Menambahkan Peserta",
                        icon: "success",
                        confirmButtonText: "OK",
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        allowEnterKey: false,
                        showLoaderOnConfirm: true,
                        preConfirm: () => {
                            Swal.disableButtons();
                            return new Promise((resolve) => {
                                setTimeout(() => {
                                    resolve();
                                    location.reload();
                                }, 800);
                            });
                        }
                    });
                } else {
                    APP.load_btn_non(btn, '+ Tambah Pasien');
                    Swal.fire({
                        title: "Perhatian",
                        text: res.message,
                        icon: "warning"
                    });
                }
            },
            error: function () {
                APP.load_btn_non(btn, '+ Tambah Pasien');
                Swal.fire({
                    title: "Error",
                    text: "Terjadi kesalahan server",
                    icon: "error"
                });
            }
        });
    });
    APP.initLoadfunction = function () {
        flatpickr("#tanggalKunjung", {
            dateFormat: "Y-m-d",
            altFormat: "F j, Y",
            defaultDate: "today",
            maxDate: "today"
        });
        $.ajax({
            url: 'controller/admisi/services/get_provider.php?type=BPJS',
            dataType: 'json',
            success: function (data) {
                let options = '';
                $.each(data, function (i, item) {
                    options += `<option value="${item.id}">${item.text}</option>`;
                });
                $('#kodeprov').html(options).trigger('change');
                setTypePasien();
            }
        });
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
        APP.ambil_data_dokter('#kodedokter', 'dokter/0/100', 'nmDokter', 'kdDokter', true);
        // function loadDokter(status = true) {
        //     const $select = $('#kodedokter');
        //     const tgl = $('#tanggalKunjung').val();
        //     const poli = $('#kodepoli').val();
        //     $select.html('<option value="">Loading...</option>').trigger('change.select2');
        //     $.ajax({
        //         url: 'controller/wsbpjs/getDokter.php',
        //         type: 'GET',
        //         data: {
        //             kdpoli: poli,
        //             tanggal: tgl
        //         },
        //         dataType: 'json',
        //         success: function (response) {
        //             $select.empty();
        //             if (status) {
        //                 $select.append('<option value="">- Pilih -</option>');
        //             }
        //             response.forEach(function (item) {
        //                 $select.append(
        //                     '<option value="' + item.kodedokter + '" ' +
        //                     'data-nama="' + item.namadokter + '" ' +
        //                     'data-jam="' + item.jampraktek + '">' +
        //                     item.namadokter + ' (' + item.jampraktek + ')' +
        //                     '</option>'
        //                 );
        //             });
        //             $select.trigger('change.select2');
        //         },
        //         error: function (err) {
        //             console.error(err);
        //             $select.html('<option value="">Error loading data</option>').trigger('change.select2');
        //         }
        //     });
        // }
        // $('#kodedokter').on('change', function () {
        //     let selected = $(this).find('option:selected');
        //     $('#namadokter').val(selected.data('nama') || '');
        //     $('#jampraktek').val(selected.data('jam') || '');
        // });
        // $("#kodepoli").on("change", function () {
        //     let nmPoli = $("#kodepoli option:selected").text();
        //     $("#nmPoli").val(nmPoli);
        //     loadDokter();
        // });
        const knjsakit = [{
                id: "10",
                text: "Rawat Jalan",
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
            }
        });
        $('#kunjSakit').select2({
            width: "100%",
            placeholder: "Jenis Kunjungan",
            allowClear: false,
            data: [{
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
        $('#tinggiBadan, #beratBadan').on('input', function () {
            APP.hitungBMI();
        });
        $('#kodeprov').on('change', function () {
            setTypePasien();
            sinkronProviderDariDataPasien();
        });
        $('#tanggalKunjung').on('change', function () {
            loadDokter();
        })

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
                    APP.load_btn_non('#create', "Simpan Kunjungan");
                }
            },
            error: function (xhr, status, error) {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: error
                });
                APP.load_btn_non('#create', "Simpan Kunjungan");
            }
        });
    };

    function resetSemuaForm() {
        $('#tampilan').html(`
            <div class="text-muted text-center py-4">
                <iconify-icon icon="solar:info-circle-linear" width="22"></iconify-icon>
                <p class="mb-0 mt-2">
                    Data pasien akan ditampilkan di sini
                </p>
            </div>
        `);
        $('#nik').val('');
        $('#no_bpjs').val('');
        $('#noKartu').val('');
        $('#namapatient').val('');
        $('#Kelamin').val('');
        $('#tgllahir').val('');
        $('#typePasien').val('');
        $('#noNIK').val('');
        $('#kdProviderPeserta').val('');
        $('#isiform')[0]?.reset();
    }

    function setTypePasien() {
        let selected = $('#kodeprov').find(':selected');
        let nama = (selected.text() || '').toLowerCase();

        let tipe = nama == 'bpjs kesehatan' || nama == 'bpjs' ? 'BPJS' : 'UMUM';
        $('#typePasien').val(tipe);
        console.log(tipe);
    }
})