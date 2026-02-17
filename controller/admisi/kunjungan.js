window.APP = window.APP || {};
$(function () {
    $('#kunjungan').select2({
        width: "100%",
        placeholder: "Jenis Kunjungan",
        allowClear: false,
        data: [
            {
                id: "1",
                text: "Kunjungan Sakit"
            },
            {
                id: "0",
                text: "Kunjungan Sehat"
            }
        ]
    })
    $('#rujukan').select2({
        width: "100%",
        allowClear: false,
        data: [
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
    })
    $('#alergiMakan').select2({
        width: "100%",
        allowClear: false,
        data: [
            {
                id: "00",
                text: "Tidak Ada"
            },
            {
                id: "01",
                text: "Seafood"
            },
            {
                id: "02",
                text: "Gandum"
            },
            {
                id: "03",
                text: "Susu Sapi"
            },
            {
                id: "04",
                text: "Kacang-Kacangan"
            },
            {
                id: "05",
                text: "Makanan Lain"
            }
        ]
    })
    $('#alergiUdara').select2({
        width: "100%",
        allowClear: false,
        data: [
            {
                id: "00",
                text: "Tidak Ada"
            },
            {
                id: "01",
                text: "Udara Panas"
            },
            {
                id: "02",
                text: "Udara Dingin"
            },
            {
                id: "03",
                text: "Udara Kotor"
            }
        ]
    })
    $('#alergiObat').select2({
        width: "100%",
        allowClear: false,
        data: [
            {
                id: "00",
                text: "Tidak Ada"
            },
            {
                id: "01",
                text: "Antibiotik"
            },
            {
                id: "02",
                text: "Antiinflamasi"
            },
            {
                id: "03",
                text: "Non Steroid"
            },
            {
                id: "04",
                text: "Aspirin"
            },
            {
                id: "05",
                text: "Kortikosteroid"
            },
            {
                id: "06",
                text: "Insulin"
            },
            {
                id: "07",
                text: "Obat-Obatan Lain"
            }
        ]
    })
    $('#kdPrognosa').select2({
        width: "100%",
        allowClear: true,
        data: [
            {
                id: "",
                text: "Pilih Prognosa"
            },
            {
                id: "01",
                text: "Sanam (Sembuh)"
            },
            {
                id: "02",
                text: "Bonam (Baik)"
            },
            {
                id: "03",
                text: "Malam (Buruk Jelek)"
            },
            {
                id: "04",
                text: "Dubia Ad Sanam Bolam (Tidak tentu Ragu-ragu, Cenderung Sembuh Baik)"
            },
            {
                id: "05",
                text: "Dubia Ad Malam (Tidak tentu Ragu-ragu, Cenderung Buruk Jelek)"
            }
        ]
    })
    $('#kdSadar').select2({
        width: "100%",
        allowClear: true,
        data: [
            {
                id: "",
                text: "Pilih Kesadaran"
            },
            {
                id: "01",
                text: "Compos mentis"
            },
            {
                id: "02",
                text: "Somnolence"
            },
            {
                id: "03",
                text: "Sopor"
            },
            {
                id: "04",
                text: "Coma"
            }
        ]
    })
    flatpickr("#tglPulang", {
        dateFormat: "Y-m-d",
        altFormat: "F j, Y",
        minDate: "today"
    });
    flatpickr("#tglRujukan", {
        dateFormat: "Y-m-d",
        altFormat: "F j, Y",
        minDate: "today",
        defaultDate: "today",
    });
    let dataPasien = JSON.parse(sessionStorage.getItem('dataPasien'));
    console.log(dataPasien);
    let statusEdit = dataPasien?.noKunjungan != "" && dataPasien?.noKunjungan != null ? true : false;
    loadFormData();
    APP.addValueInput('#keluhan', dataPasien.keluhan);
    APP.addValueInput('#tinggiBadan', dataPasien.tinggiBadan);
    APP.addValueInput('#beratBadan', dataPasien.beratBadan);
    APP.addValueInput('#lingkarPerut', dataPasien.lingkarPerut);
    APP.addValueInput('#sistole', dataPasien.sistole);
    APP.addValueInput('#diastole', dataPasien.diastole);
    APP.addValueInput('#respRate', dataPasien.respRate);
    APP.addValueInput('#heartRate', dataPasien.heartRate);
    APP.addValueInput('#noKartu', dataPasien.noKartu);
    APP.addValueInput('#kode_poli', dataPasien.kdPoli);
    APP.addValueInput('#nama_poli', dataPasien.nmPoli);
    APP.addValueInput('#tglDaftar', dataPasien.tanggal_daftar);
    $('#rujukan').val(dataPasien.kdTkp);
    $('#kunjungan').val(dataPasien.kunjSakit);
    if (dataPasien.kdTkp == "20") {
        $('#formAnamnesa, #formalergi, #formpronosa, #formnonobat, #formbmhp').addClass('d-none');
    }
    if (statusEdit) {
        $('#simpanEntry').text('Update');
        APP.addValueInput('#noKunjungan', dataPasien.noKunjungan);
        APP.addValueInput('#anamnesa', dataPasien.anamnesa);
        APP.addValueInput('#terapiObat', dataPasien.terapiObat);
        APP.addValueInput('#bmhp', dataPasien.bmhp);
        APP.addValueInput('#suhu', dataPasien.suhu);
        APP.addValueInput('#tglPulang', dataPasien.tglPulang);
        APP.addValueInput('#tglDaftar', dataPasien.tglDaftar);
        $('#kdPrognosa').val(dataPasien.kdPrognosa).trigger('change');
        $('#alergiMakan').val(dataPasien.alergiMakan).trigger('change');
        $('#alergiUdara').val(dataPasien.alergiUdara).trigger('change');
        $('#alergiObat').val(dataPasien.alergiObat).trigger('change');
        if (dataPasien.kdDiag1 != null) {
            APP.addValueSelect('#diag1', dataPasien.kdDiag1, dataPasien.nmDiag1);
            APP.addValueInput('#nmDiag1', dataPasien.nmDiag1);
        }
        if (dataPasien.kdDiag2 != null) {
            APP.addValueSelect('#diag2', dataPasien.kdDiag2, dataPasien.nmDiag2);
            APP.addValueInput('#nmDiag2', dataPasien.nmDiag2);
        }
        if (dataPasien.kdDiag3 != null) {
            APP.addValueSelect('#diag3', dataPasien.kdDiag3, dataPasien.nmDiag3);
            APP.addValueInput('#nmDiag3', dataPasien.nmDiag3);
        }
    }

    // $('#tanggalKll').on('change', function(){
    //    if ($(this).is(':checked')){
    //     APP.showSmoot('')
    //    }else{

    //    }
    // })
    APP.initDiagnosa('#diag1', '#nmDiag1');
    APP.initDiagnosa('#diag2', '#nmDiag2');
    APP.initDiagnosa('#diag3', '#nmDiag3');
    async function loadFormData() {
        try {
            $('select').select2({
                width: '100%'
            });
            $('#loading').show();
            await APP.ambil_data('#kdDokter', 'dokter/0/15', 'kdDokter', 'nmDokter', false);
            if (dataPasien.kdTkp == "20") {
                await APP.ambil_data('#kdStatusPulang', 'statuspulang/rawatInap/true', 'kdStatusPulang', 'nmStatusPulang', true);
            } else {
                await APP.ambil_data('#kdStatusPulang', 'statuspulang/rawatInap/false', 'kdStatusPulang', 'nmStatusPulang', true);
                $('#kdPrognosa').val(dataPasien.kdPrognosa).trigger('change');
                $('#alergiMakan').val("00").trigger('change');
                $('#alergiUdara').val("00").trigger('change');
                $('#alergiObat').val("00").trigger('change');
            }
            $('#kdSadar').val(dataPasien.kdSadar).trigger('change');
            $('#kdDokter').val(dataPasien.kdDokter).trigger('change');
            $('#kdStatusPulang').val(dataPasien.kdStatusPulang).trigger('change');
            // Testing Limit API Tester
            // APP.ambil_data('#kdStatusPulang', '/spesialis/sarana', 'kdSarana', 'nmSarana', false);
        } catch (err) {
            console.error('Gagal load data:', err);
        } finally {
            $('#loading').hide();
        }
    }
    $('#kdStatusPulang').on('change', function () {
        const idstatus = $(this).val();
        if (idstatus == "4") {
            APP.hideSmoot('#rujukhorizontal')
            APP.showSmoot('#rujukvertikal');
        } else if (idstatus == "6") {
            APP.hideSmoot('#rujukvertikal');
            APP.hideSmoot('#formrujukanvertikal');
            APP.showSmoot('#rujukhorizontal');
        } else {
            APP.hideSmoot('#rujukvertikal');
            APP.hideSmoot('#rujukhorizontal')
            APP.hideSmoot('#formrujukanvertikal');
            $('input[name="kdStatusPulang"]').prop('checked', false);
            APP.addValueInput('#typeRujukan', 'normal');
        }
    })
    $('input[name="kdStatusPulang"]').on('change', function () {
        let status = $(this).val();
        $('#kdKategori').val('');
        APP.showSmoot('#formrujukanvertikal');
        function changeCol(el, newCol) {
            el.closest('[class*="col-"]')
                .removeClass(function (index, className) {
                    return (className.match(/(^|\s)col-\d+/g) || []).join(' ');
                })
                .addClass(newCol);
        }
        if (status == 'SP') {
            APP.ambil_data('#kdKategori', '/spesialis', 'kdSpesialis', 'nmSpesialis', false);
            changeCol($('#kdKategori'), 'col-2');
            changeCol($('#tglRujukan'), 'col-3');
            APP.showSmoot('#subspesialis');
            APP.showSmoot('#sarana');
            APP.ambil_data('#kdSarana', '/spesialis/sarana', 'kdSarana', 'nmSarana', false);
            APP.hideSmoot('#alasanrujuk');
            APP.addValueInput('#typeRujukan', 'spesialis');
        } else {
            APP.addValueInput('#typeRujukan', 'khusus');
            $('#kdSarana').val(null).trigger('change');
            $('#kdsubspesialis').val(null).trigger('change');
            APP.ambil_data('#kdKategori', '/spesialis/khusus', 'kdKhusus', 'nmKhusus', false);
            APP.hideSmoot('#sarana');
            APP.hideSmoot('#subspesialis');
            changeCol($('#kdKategori'), 'col-5');
            changeCol($('#tglRujukan'), 'col-5');
            APP.showSmoot('#alasanrujuk');
        }
    });
    $('#kdKategori').on('change', function () {
        const data = $(this).val();
        APP.ambil_data('#kdsubspesialis', '/spesialis/' + data + '/subspesialis', 'kdSubSpesialis', 'nmSubSpesialis', false);
    })
    $('#simpanEntry').on('click', function () {
        const data = $('#isiform').serialize();
        const btn = $(this);
        $.ajax({
            url: "controller/admisi/services/prosesinsertkunjungan.php",
            type: "POST",
            data: data,
            dataType: 'json',
            beforeSend: function () {
                APP.load_btn_aktif(btn);
            },
            success: function (res) {
                if (res.success) {
                    Swal.fire({
                        icon: "success",
                        title: res.message,
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: res.message,
                    });
                }
            },
            complete: function () {
                APP.load_btn_non(btn, 'Save');
            }
        })

    })
    $('#btnCariFaskes').on('click', function () {
        const btn = $(this);
        const nokartu = $('#noKartu').val();
        const kdkategori = $('#kdKategori').val();
        const tglrujuk = $('#tglRujukan').val();
        const sarana = $('#kdSarana').val();
        const kdspesialis = $('#kdsubspesialis').val();
        $.ajax({
            url: "controller/admisi/services/getFaskesRujuk.php",
            type: "GET",
            dataType: "json",
            data: {
                noKartu: nokartu,
                Kategori: kdkategori,
                estRujuk: tglrujuk,
                kdsarana: sarana,
                kdsubspesialis: kdspesialis
            },
            beforeSend: function () {
                APP.load_btn_aktif(btn);
            },
            success: function (res) {
                if (res.success) {
                    let data = res.list;
                    let tbody = $('#tableRujukan tbody');
                    tbody.empty();
                    $.each(data, function (index, item) {
                        let row = `
                            <tr>
                                <td>${index + 1}</td>
                                <td><strong>${item.nmppk}</strong></td>
                                <td>${item.kelas}</td>
                                <td>${item.nmkc}</td>
                                <td>${item.alamatPpk}</td>
                                <td>${item.telpPpk}</td>
                                <td>${(Number(item.distance) / 1000).toFixed(2)} km</td>
                                <td>${item.jmlRujuk}</td>
                                <td>${item.kapasitas}</td>
                                <td>${item.persentase}%</td>
                                <td>${item.jadwal}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary pilihFaskes"
                                        data-kode="${item.kdppk}"
                                        data-nama="${item.nmppk}">
                                        Pilih
                                    </button>
                                </td>
                            </tr>
                        `;
                        tbody.append(row);
                    });
                    $('#modalFaskes').modal('show');
                } else {
                    Swal.fire({
                        title: "Error",
                        text: res.message,
                        icon: "error"
                    });
                }
            },
            complete: function () {
                APP.load_btn_non(btn, `<i class="bi bi-search"></i> Cari Faskes`);
            }
        })
    })
    $(document).on('click', '.pilihFaskes', function () {
        let kode = $(this).data('kode');
        let nama = $(this).data('nama');
        APP.addValueInput('#nmfaskes', nama);
        APP.addValueInput('#kdfaskes', kode);
        $('#modalFaskes').modal('hide');
    });

})