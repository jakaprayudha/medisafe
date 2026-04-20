window.APP = window.APP || {};
$(function () {
    statusEdit = false;
    let StatusPasien = 'BPJS';
    $('select').select2({
        width: '100%'
    });
    flatpickr("#tglRujukan", {
        dateFormat: "Y-m-d",
        altFormat: "F j, Y",
        minDate: "today",
        defaultDate: "today"
    });
    APP.getDataKunjungan = function () {
        const urlParams = new URLSearchParams(window.location.search);
        const no = urlParams.get('no');
        $.ajax({
            url: 'controller/admisi/services/doctor/getDataKunjungan.php',
            type: 'GET',
            data: { nomor_visit: no },
            dataType: 'json',
            success: function (res) {
                if (res.status === 'success' && res.data.length > 0) {
                    let d = res.data[0];
                    StatusPasien = res.pasienStatus == "UMUM" ? "UMUM" : "BPJS";
                    if (res.pasienStatus == "UMUM") {
                        APP.ambil_data('#kdStatusPulang', 'statuspulang/rawatInap/false', 'kdStatusPulang', 'nmStatusPulang', true).then(function () {
                            $('.statuspasien').addClass('d-none');
                            APP.cetak('#nama_poli', d.nmPoli);
                            APP.cetak('#id_patient', d.id_patient);
                            APP.cetak('#kdDokter', d.code_doctor);
                            APP.cetak('#nmDokter', d.id_doctor);
                            APP.cetak('#tinggiBadan', d.tinggi_badan);
                            APP.cetak('#beratBadan', d.berat_badan);
                            APP.cetak('#lingkar_perut', d.lingkar_perut || '0');
                            APP.cetak('#sistole', d.sistole || '0');
                            APP.cetak('#diastole', d.diastole || '0');
                            APP.cetak('#respRate', d.resp_rate || '0');
                            APP.cetak('#heartRate', d.heart_rate || '0');
                            APP.cetak('#suhu', d.suhu || '0');
                            APP.cetak('#saturasi', d.saturasi || '0');
                            APP.cetak('#keluhan_penyerta', d.keluhan || '');
                            APP.cetak('#keluhan_utama', d.anamnesa || '');
                            APP.cetak('#status_pasien', "UMUM");
                            APP.cetakhtml('#idUmur', d.umur);
                            APP.cetakhtml('#nomor_bpjs', d.noKartu);
                            APP.cetakhtml('#nomor_nik', d.patient_nik);
                            APP.hitungBMI();
                            if (d.kdDiag1 != null) {
                                APP.addValueSelect('#diag1', d.kdDiag1, d.kdDiag1 + ' - ' + d.nmDiag1);
                                APP.addValueInput('#nmDiag1', d.nmDiag1);
                            }
                            if (d.kdDiag2 != null) {
                                APP.addValueSelect('#diag2', d.kdDiag2, d.kdDiag2 + ' - ' + d.nmDiag2);
                                APP.addValueInput('#nmDiag2', d.nmDiag2);
                            }
                            if (d.kdDiag3 != null) {
                                APP.addValueSelect('#diag3', d.kdDiag3, d.kdDiag3 + ' - ' + d.nmDiag3);
                                APP.addValueInput('#nmDiag3', d.nmDiag3);
                            }
                            $('#kdStatusPulang').val(d.kdStatusPulang).trigger('change');
                        })
                    } else {
                        $('#logobpjs').removeClass('d-none');
                        if (d.noKunjungan == null) {
                            APP.cetak('#kode_poli', d.kdPoli);
                            APP.cetak('#nama_poli', d.nmPoli);
                            APP.cetak('#id_patient', d.id_patient);
                            APP.cetak('#tglDaftar', d.tanggal_daftar);
                            APP.cetak('#noKartu', d.noKartu);
                            APP.cetak('#kdDokter', d.code_doctor);
                            APP.cetak('#nmDokter', d.id_doctor);
                            APP.cetak('#tinggiBadan', d.tinggiBadan);
                            APP.cetak('#beratBadan', d.beratBadan);
                            APP.cetak('#lingkarPerut', d.lingkarPerut || '0');
                            APP.cetak('#sistole', d.sistole || '0');
                            APP.cetak('#diastole', d.diastole || '0');
                            APP.cetak('#respRate', d.respRate || '0');
                            APP.cetak('#heartRate', d.heartRate || '0');
                            APP.cetak('#suhu', d.suhu || '0');
                            APP.cetak('#saturasi', d.saturasi || '0');
                            APP.cetak('#keluhan_penyerta', d.keluhan || '');
                            APP.cetakhtml('#idUmur', d.umur);
                            APP.cetakhtml('#nomor_bpjs', d.noKartu);
                            APP.cetakhtml('#nomor_nik', d.patient_nik);
                            APP.hitungBMI();
                            APP.ambil_data('#kdStatusPulang', 'statuspulang/rawatInap/false', 'kdStatusPulang', 'nmStatusPulang', true);
                        } else {
                            APP.ambil_data('#kdStatusPulang', 'statuspulang/rawatInap/false', 'kdStatusPulang', 'nmStatusPulang', true)
                                .then(function () {
                                    APP.cetak('#noKunjungan', d.noKunjungan);
                                    APP.cetak('#kode_poli', d.kdPoli);
                                    APP.cetak('#nama_poli', d.nmPoli);
                                    APP.cetak('#id_patient', d.id_patient);
                                    APP.cetak('#tglDaftar', d.tglDaftar);
                                    APP.cetak('#noKartu', d.noKartu);
                                    APP.cetak('#kdDokter', d.kdDokter);
                                    APP.cetak('#nmDokter', d.nmDokter);
                                    APP.cetak('#visit_notes', d.catatan_screening || '');
                                    APP.cetak('#tinggiBadan', d.tinggiBadan);
                                    APP.cetak('#beratBadan', d.beratBadan);
                                    APP.cetak('#lingkarPerut', d.lingkarPerut);
                                    APP.cetak('#sistole', d.sistole || '0');
                                    APP.cetak('#diastole', d.diastole || '0');
                                    APP.cetak('#respRate', d.respRate || '0');
                                    APP.cetak('#heartRate', d.heartRate || '0');
                                    APP.cetak('#suhu', d.suhu);
                                    APP.cetak('#saturasi', d.saturasi);
                                    APP.cetak('#keluhan_penyerta', d.keluhan || '');
                                    APP.cetak('#keluhan_utama', d.anamnesa || '');
                                    APP.cetak('#tindakan', d.tindakan || '');
                                    APP.cetakhtml('#idUmur', d.umur);
                                    APP.cetakhtml('#nomor_bpjs', d.noKartu);
                                    APP.cetakhtml('#nomor_nik', d.patient_nik);
                                    APP.hitungBMI();
                                    $('#alergiMakan').val(d.alergiMakan).trigger('change');
                                    $('#alergiUdara').val(d.alergiUdara).trigger('change');
                                    $('#alergiObat').val(d.alergiObat).trigger('change');
                                    $('#kondisi_masuk').val(d.kdPrognosa).trigger('change');
                                    $('#kdSadar').val(d.kdSadar).trigger('change');
                                    if (d.kdDiag1 != null) {
                                        APP.addValueSelect('#diag1', d.kdDiag1, d.kdDiag1 + ' - ' + d.nmDiag1);
                                        APP.addValueInput('#nmDiag1', d.nmDiag1);
                                    }
                                    if (d.kdDiag2 != null) {
                                        APP.addValueSelect('#diag2', d.kdDiag2, d.kdDiag2 + ' - ' + d.nmDiag2);
                                        APP.addValueInput('#nmDiag2', d.nmDiag2);
                                    }
                                    if (d.kdDiag3 != null) {
                                        APP.addValueSelect('#diag3', d.kdDiag3, d.kdDiag3 + ' - ' + d.nmDiag3);
                                        APP.addValueInput('#nmDiag3', d.nmDiag3);
                                    }
                                    $('#kdStatusPulang').val(d.kdStatusPulang).trigger('change');
                                    $('#simpan_pemeriksaan').text('Update Pemeriksaan').removeClass('btn-primary').addClass('btn-danger');
                                    if (d.noKunjungan != null) {
                                        statusEdit = true;
                                    }
                                    checkRujuk(d.subSpesialis, d.kdkhSpesialis);
                                    window.kdTacc = d.kdTacc;
                                    window.alasanTacc = d.alasanTacc;
                                    window.kdKhusus = d.kdKhusus;
                                    window.subSpesialis = d.subSpesialis;
                                    window.kdSarana = d.kdSarana;
                                    window.tglEstRujuk = d.tglEstRujuk;
                                    window.kdfaskes = d.kdfaskes;
                                    window.nmfaskes = d.nmfaskes;

                                    if (d.kdppk != null) {
                                        $('.btn-print').removeClass('d-none');
                                    }
                                })
                                .catch(err => {
                                    console.error('Error ambil_data:', err);
                                },
                                );
                        }
                    }
                }
            }
        })
    }
    APP.getDataKunjungan();
    $('#tinggiBadan, #beratBadan').on('input', function () {
        APP.hitungBMI();
    });
    $('#kondisi_masuk').select2({
        width: "100%",
        allowClear: false,
        data: [
            {
                id: "",
                text: "-- Pilih --"
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
    APP.initDiagnosa('#diag1', '#nmDiag1', '#kdnonSpesialis1');
    APP.initDiagnosa('#diag2', '#nmDiag2', '#kdnonSpesialis2');
    APP.initDiagnosa('#diag3', '#nmDiag3', '#kdnonSpesialis3');
    $('#kdSadar').select2({
        width: "100%",
        allowClear: false,
        data: [
            {
                id: "",
                text: "-- Pilih --"
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

    $('#btn-print').on('click', function () {
        const nomor = $('#noKunjungan').val();
        window.open('controller/doctor/cetakan-rujukan.php?id=' + nomor, '_blank');
    })

    // BTN INSERT
    $('#simpan_pemeriksaan').on('click', function () {
        const btn = $(this);
        let data = $('#formPemeriksaan').serialize();
        $.ajax({
            url: "controller/admisi/services/doctor/insertKunjungan.php",
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
                        title: "Berhasil",
                        text: res.message,
                    });
                    location.reload();
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: res.message,
                    });
                }
            },
            complete: function () {
                APP.load_btn_non(btn, 'Simpan Pemeriksaan');
            }
        })
    })

    // action Rujuk
    $('#kdStatusPulang').on('change', function () {
        const idstatus = $(this).val();
        if (idstatus == "4" && StatusPasien == "BPJS") {
            APP.hideSmoot('#rujukhorizontal')
            APP.showSmoot('#rujukvertikal');
            APP.hideSmoot('#noLaporanPolisi');
            // console.log($('#kdnonSpesialis1').val());
            $('input[name="kdStatusRujuk"][value="SP"]').prop('checked', true).trigger('change');
            chackTacc();
            loadRujukan();
        } else if (idstatus == "6" && StatusPasien == "BPJS") {
            APP.hideSmoot('#rujukvertikal');
            APP.hideSmoot('#formrujukanvertikal');
            APP.showSmoot('#rujukhorizontal');
            APP.hideSmoot('#noLaporanPolisi');
            APP.hideSmoot('#formTacc');
        } else if (idstatus == "1" && StatusPasien == "BPJS") {
            APP.hideSmoot('#rujukvertikal');
            APP.hideSmoot('#formTacc');
            APP.hideSmoot('#rujukhorizontal')
            APP.hideSmoot('#formrujukanvertikal');
            $('input[name="kdStatusPulang"]').prop('checked', false);
            APP.addValueInput('#typeRujukan', 'normal');
            APP.showSmoot("#noLaporanPolisi");
        }
        else {
            APP.hideSmoot('#noLaporanPolisi');
            APP.hideSmoot('#formTacc');
            APP.hideSmoot('#rujukvertikal');
            APP.hideSmoot('#rujukhorizontal')
            APP.hideSmoot('#formrujukanvertikal');
            $('input[name="kdStatusPulang"]').prop('checked', false);
            APP.addValueInput('#typeRujukan', 'normal');
        }
    })
    $('input[name="kdStatusRujuk"]').on('change', function () {
        let status = $(this).val();
        if (status == 'SP') {
            TrigerSP();
            // console.log('SP');
        } else {
            triggerKH();
            // console.log('KH');
        }
    });
    $('#kdKategori').on('change', function () {
        const data = $(this).val();
        APP.ambil_data_save('#kdsubspesialis', '/spesialis/' + data + '/subspesialis', 'nmSubSpesialis', 'kdSubSpesialis', false, '#nmSubSpesialis1');
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
    $('#kdTacc').on('change', function () {
        $('#alasanTacc').val('');
    })
    function TrigerSP() {
        APP.showSmoot('#formrujukanvertikal');
        $('#kdKategori').val('');
        changeCol($('#kdKategori'), 'col-2');
        changeCol($('#tglRujukan'), 'col-3');
        APP.showSmoot('#subspesialis');
        APP.showSmoot('#sarana');
        $('#kdSarana').prop('disabled', true);
        $('#kdSaranaHidden').val('9');
        $('#useSarana').on('change', function () {
            if ($(this).is(':checked')) {
                console.log('pilih');
                $('#kdSarana').prop('disabled', false);
                APP.ambil_data('#kdSarana', '/spesialis/sarana', 'kdSarana', 'nmSarana', true);
            } else {
                console.log('tidak');
                $('#kdSarana')
                    .val('9')
                    .trigger('change')
                    .prop('disabled', true);
                $('#kdSaranaHidden').val('9');
            }
        });
        $('#kdSarana').on('change', function () {
            $('#kdSaranaHidden').val(this.value);
        });
        APP.hideSmoot('#alasanrujuk');
        APP.addValueInput('#typeRujukan', 'spesialis');
        if (!statusEdit) {
            APP.ambil_data_save('#kdKategori', '/spesialis', 'nmSpesialis', 'kdSpesialis', true, '#nmKategori');
        }
    }
    function triggerKH() {
        APP.showSmoot('#formrujukanvertikal');
        $('#kdKategori').val('');
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
    function changeCol(el, newCol) {
        el.closest('[class*="col-"]')
            .removeClass(function (index, className) {
                return (className.match(/(^|\s)col-\d+/g) || []).join(' ');
            })
            .addClass(newCol);
    }
    function checkRujuk(subSpesialis, kdkhSpesialis) {
        if (subSpesialis != null && statusEdit == true) {
            $('input[name="kdStatusRujuk"][value="SP"]').prop('checked', true).trigger('change');
            // console.log('masuk')
            TrigerSP()
        } else if (kdkhSpesialis != null && statusEdit == true) {
            $('input[name="kdStatusRujuk"][value="KH"]').prop('checked', true).trigger('change');
            triggerKH()
        }
    }
    function chackTacc() {
        if ($('#kdnonSpesialis1').val() == 'true') {
            $('#formTacc').removeClass('d-none');
            // console.log('tacc');
        } else {
            $('#formTacc').addClass('d-none');
            // console.log('non tacc');
        }
        if (statusEdit) {
            $('#formTacc').removeClass('d-none');
            $('#kdTacc').val(kdTacc).trigger('change');
            $('#alasanTacc').val(alasanTacc);
        }
    }
    async function loadRujukan() {
        await APP.ambil_data('#kdKategori', '/spesialis', 'kdSpesialis', 'nmSpesialis', true);
        $('#kdKategori').val(kdKhusus).trigger('change');
        $('#kdsubspesialis').val(subSpesialis).trigger('change');
        $('#kdSarana').val(kdSarana).trigger('change');
        $('#kdSaranaHidden').val(kdSarana);
        $('#tglRujukan').val(tglEstRujuk);
        $('#kdfaskes').val(kdfaskes);
        $('#nmfaskes').val(nmfaskes);
    }
})