window.APP = window.APP || {};
APP.state = {};

APP.load_btn_aktif = function (id) {
    $(id).prop('disabled', true).html(`
        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
        Loading...
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
    $(id).text(': ' + nama);
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