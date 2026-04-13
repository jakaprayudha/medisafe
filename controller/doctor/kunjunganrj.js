window.APP = window.APP || {};
$(function () {
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
                    APP.cetak('#kode_poli', d.kdPoli);
                    APP.cetak('#nama_poli', d.nmPoli);
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
    APP.ambil_data('#kdStatusPulang', 'statuspulang/rawatInap/false', 'kdStatusPulang', 'nmStatusPulang', true);

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
})