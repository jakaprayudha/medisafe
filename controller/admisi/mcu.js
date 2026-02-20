window.app = window.app || {};
$(function () {
    let dataKunjungan = JSON.parse(sessionStorage.getItem('dataKunjungan'));
    // console.log(dataKunjungan);
    $('#metaNoKunjungan').html(dataKunjungan.noKunjungan);
    $('#metaTglDaftar').html(dataKunjungan.tglDaftar);
    APP.addValueInput('input[name="noKunjungan"]', dataKunjungan.noKunjungan);
    APP.addValueInput('input[name="tglPelayanan"]', dataKunjungan.tglDaftar);
    $('#btnSaveMCU').on('click', function () {
        const btn = $(this);
        let data = $('#isiform').serialize();
        $.ajax({
            url: 'controller/admisi/services/InsertMcu.php',
            type: "POST",
            data: data,
            dataType: 'json',
            beforeSend: function () {
                APP.load_btn_aktif(btn);
            },
            complete: function () {
                APP.load_btn_non(btn, `<i class="bi bi-save"></i> Simpan MCU`);
            },
            success: function (res) {
                if (res.success) {
                    Swal.fire({
                        icon: "success",
                        title: res.message,
                        confirmButtonText: "OK"
                    }).then(function () {
                        window.location.assign("module/admisi/listdatakunjungan.php");
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
    });
})