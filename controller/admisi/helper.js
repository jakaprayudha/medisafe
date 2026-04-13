window.APP = window.APP || {};
APP.state = {};
const today = new Date();
const dd = String(today.getDate()).padStart(2, '0');
const mm = String(today.getMonth() + 1).padStart(2, '0');
const yyyy = today.getFullYear();
const tanggalJS = `${dd}-${mm}-${yyyy}`;
APP.load_btn_aktif = function (id) {
    $(id).prop('disabled', true).html(`
        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
    `);
}
APP.load_btn_non = function (id, value) {
    $(id).prop('disabled', false).html(value);
}
APP.key_enter = function (idinput, idbtn) {
    $(idinput).keydown(function (event) {
        if (event.key === "Enter") {
            event.preventDefault();
            $(idbtn).click();
        }
    });
}
APP.value_data_select = (kode, nama, id) => {
    var selecthasil = document.getElementById(id);
    selecthasil.options[0].value = kode;
    selecthasil.options[0].text = nama;
    pmata();
}
APP.cetak = (id, nama) => {
    $(id).val(nama);
}
APP.cetakhtml = (id, nama) => {
    $(id).text(' ' + nama);
}
APP.cetakselect = (id, kode, nama) => {
    var selectElem = document.getElementById(id);
    selectElem.options[0].value = kode;
    selectElem.options[0].text = nama;
}
APP.getDataselect = (url, idselect, value1, value2) => {
    $.ajax({
        url: 'pcare/get_api.php',
        type: 'POST',
        data: {
            url: url
        },
        dataType: 'json',
        success: function (response) {
            var select = $(idselect);
            select.empty();
            $.each(response.list, function (index, item) {
                select.append($('<option></option>').attr('value', item[value1]).text(item[value2]));
            });
        },
        error: function (xhr, status, error) {
            console.log(xhr.responseText);
        }
    })
}
APP.tanggal_baru = (tanggal) => {
    let parsing = tanggal.replace(/\//g, '-');
    let bagian = parsing.split('-');
    return `${bagian[1]}-${bagian[0]}-${bagian[2]}`;
}
APP.tanggal_baru_tbt = (tanggal) => {
    let parsing = tanggal.replace(/\//g, '-');
    let bagian = parsing.split('-');
    return `${bagian[2]}-${bagian[1]}-${bagian[0]}`;
}
APP.getDatainput = (id) => {
    return $(id).val();
}
APP.resetSelect = function (id) {
    $(id).val(null).empty().trigger("change");
};
APP.addValueInput = function (id, data) {
    $(id).val(data);
};
APP.addValueSelect = function (id, iddata, data) {
    $(id).empty().append(new Option(data, iddata, true, true));
};
APP.ambil_data = async function (id, url, nama, kode, status) {
    try {
        $(id).html('<option>Loading...</option>');
        let response = await $.ajax({
            url: 'controller/admisi/services/getApi.php',
            type: 'POST',
            data: { url: url },
            dataType: 'json'
        });
        $(id).empty();
        if (status) {
            $(id).append('<option value="">- Pilih -</option>');
        }
        response.list.forEach(item => {
            $(id).append(
                `<option value="${item[nama]}">${item[kode]}</option>`
            );
        });
    } catch (err) {
        console.error(err);
        $(id).html('<option>Error loading data</option>');
    }
};
APP.ambil_data_dokter = async function (id, url, nama, kode, status) {
    try {
        $(id).html('<option>Loading...</option>');
        let response = await $.ajax({
            url: 'controller/admisi/services/getApi.php',
            type: 'POST',
            data: { url: url },
            dataType: 'json'
        });
        $(id).empty();
        if (status) {
            $(id).append('<option value="">- Pilih -</option>');
        }
        response.list.forEach(item => {
            $(id).append(
                `<option value="${item[kode]}">${item[nama]}</option>`
            );
        });
        $(id).on('change', function () {
            let namaDokter = $(this).find('option:selected').text();
            $('#namadokter').val(namaDokter);
        });
    } catch (err) {
        console.error(err);
        $(id).html('<option>Error loading data</option>');
    }
};

APP.initDiagnosa = function (selector, hiddenNameSelector, idkdspesialis) {
    $(selector).select2({
        placeholder: "Ketik Diagnosa...",
        width: "100%",
        minimumInputLength: 3,
        ajax: {
            url: "controller/admisi/services/getDiagnosa.php",
            dataType: "json",
            delay: 250,
            data: function (params) {
                return { q: params.term };
            },
            processResults: function (data) {
                let items = data.data || [];
                return {
                    results: items.map(item => ({
                        id: item.kdDiag,
                        text: `${item.nmDiag} ${item.nonSpesialis ? ": TACC" : ": NON TACC"}`,
                        nmDiag: item.nmDiag,
                        idspesialis: item.nonSpesialis
                    })),
                };
            },
            cache: true,
        },
        language: {
            searching: () => "Mencari diagnosa...",
            noResults: () => "Diagnosa Tidak Ditemukan...",
            inputTooShort: function (args) {
                const remaining = args.minimum - args.input.length;
                return `Ketik minimal ${remaining} karakter`;
            },
        }
    });

    // auto focus
    $(selector).on('select2:open', function () {
        setTimeout(function () {
            document.querySelector('.select2-container--open .select2-search__field').focus();
        }, 0);
    });
    $(selector).on('select2:select', function (e) {
        let data = e.params.data;
        $(hiddenNameSelector).val(data.text);
        $(idkdspesialis).val(data.idspesialis);
        console.log($('#kdStatusPulang').val() + data.idspesialis);
        if (data.idspesialis == true && $('#kdStatusPulang').val() == '4') {
            $('#formTacc').removeClass('d-none');
            // console.log('tacc');
        } else {
            $('#formTacc').addClass('d-none');
            // console.log('non tacc');
        }
    });
    $(selector).on('select2:clear', function () {
        $(hiddenNameSelector).val('');
        $(idkdspesialis).val('');
        $('#formTacc').addClass('d-none');
    });
}
APP.hideSmoot = function (selector, duration = 300) {
    $(selector).each(function () {
        const $el = $(this);

        if (!$el.hasClass('d-none')) {
            $el.stop(true, true).fadeOut(duration, function () {
                $el.addClass('d-none');
            });
        }
    });
}
APP.showSmoot = function (selector, duration = 500) {
    $(selector).each(function () {
        const $el = $(this);

        if ($el.hasClass('d-none')) {
            $el.removeClass('d-none')
                .hide()
                .stop(true, true)
                .fadeIn(duration);
        }
    });
}
APP.openModal = function (id) {
    const modal = new bootstrap.Modal(id);
    modal.show();
}
APP.formatRupiah = function (angka) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(angka);
}
APP.hitungBMI = function () {
    let tinggi = parseFloat($('#tinggiBadan').val());
    let berat = parseFloat($('#beratBadan').val());

    if (tinggi > 0 && berat > 0) {
        let tinggiMeter = tinggi / 100;
        let bmi = berat / (tinggiMeter * tinggiMeter);

        bmi = bmi.toFixed(2);

        $('#bmi').val(bmi);

        let ket = '';

        if (bmi < 18.5) {
            ket = 'Kurus';
        } else if (bmi < 25) {
            ket = 'Normal';
        } else if (bmi < 30) {
            ket = 'Kelebihan Berat';
        } else {
            ket = 'Obesitas';
        }

        $('#bmiKet').val(ket);
    } else {
        $('#bmi').val('');
        $('#bmiKet').val('');
    }
}