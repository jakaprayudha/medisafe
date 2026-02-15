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
    flatpickr("#tglDaftar", {
        dateFormat: "Y-m-d",
        altFormat: "F j, Y",
        defaultDate: "today",
        minDate: "today"
    });
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
    console.log(dataPasien.poli.kdPoli);
    APP.addValueInput('#keluhan', dataPasien.keluhan);
    APP.addValueInput('#tinggiBadan', dataPasien.tinggiBadan);
    APP.addValueInput('#beratBadan', dataPasien.beratBadan);
    APP.addValueInput('#lingkarPerut', dataPasien.lingkarPerut);
    APP.addValueInput('#sistole', dataPasien.sistole);
    APP.addValueInput('#diastole', dataPasien.diastole);
    APP.addValueInput('#respRate', dataPasien.respRate);
    APP.addValueInput('#heartRate', dataPasien.heartRate);
    APP.addValueInput('#noKartu', dataPasien.peserta.noKartu);
    APP.addValueInput('#kode_poli', dataPasien.poli.kdPoli);
    loadFormData();
    // $('#tanggalKll').on('change', function(){
    //    if ($(this).is(':checked')){
    //     APP.showSmoot('')
    //    }else{

    //    }
    // })
    for (let i = 1; i <= 3; i++) {
        APP.initDiagnosa('#diag' + i);
    }
    async function loadFormData() {
        try {
            $('select').select2({
                width: '100%'
            });
            $('#loading').show();
            // await APP.ambil_data('#alergiMakan', 'alergi/jenis/01', 'kdAlergi', 'nmAlergi', false);
            // await APP.ambil_data('#alergiUdara', 'alergi/jenis/02', 'kdAlergi', 'nmAlergi', false);
            // await APP.ambil_data('#alergiObat', 'alergi/jenis/03', 'kdAlergi', 'nmAlergi', false);
            // await APP.ambil_data('#kdPrognosa', 'prognosa', 'kdPrognosa', 'nmPrognosa', true);
            // await APP.ambil_data('#kdSadar', 'kesadaran', 'kdSadar', 'nmSadar', false);
            // await APP.ambil_data('#kdDokter', 'dokter/0/15', 'kdDokter', 'nmDokter', false);
            await APP.ambil_data('#kdStatusPulang', 'statuspulang/rawatInap/false', 'kdStatusPulang', 'nmStatusPulang', true);
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
        } else {
            $('#kdSarana').val(null).trigger('change');
            $('#kdsubspesialis').val(null).trigger('change');
            APP.ambil_data('#kdKategori', '/spesialis/khusus', 'kdKhusus', 'nmKhusus', false);
            APP.hideSmoot('#sarana');
            APP.hideSmoot('#subspesialis');
            changeCol($('#kdKategori'), 'col-5');
            changeCol($('#tglRujukan'), 'col-5');
        }
    });
    $('#kdKategori').on('change', function () {
        const data = $(this).val();
        APP.ambil_data('#kdsubspesialis', '/spesialis/' + data + '/subspesialis', 'kdSubSpesialis', 'nmSubSpesialis', false);
    })
    $('#simpanEntry').on('click', function () {
        const data = $('#isiform').serialize();
        const btn = $(this);
        APP.load_btn_aktif(btn);
        $.ajax({
            url: "controller/admisi/services/prosesinsertkunjungan.php",
            type: "POST",
            data: data,
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    Swal.fire({
                        icon: "success",
                        title: "Berhasil Simpan Kunjungan",
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: res.message,
                    });
                }
            }
        })
        APP.load_btn_non(btn, 'Save');
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
        $('#modalRujukan').modal('hide');
    });

})