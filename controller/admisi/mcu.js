window.app = window.app || {};
$(function () {
    let dataKunjungan = JSON.parse(sessionStorage.getItem('dataKunjungan'));
    let datamcu = JSON.parse(sessionStorage.getItem('dataMcu'));
    let statusEdit = false;
    if (datamcu && Object.keys(datamcu).length > 0) {
        statusEdit = true;
    }
    console.log(datamcu);
    if (statusEdit == false) {
        $('#metaNoKunjungan').html(dataKunjungan.noKunjungan);
        $('#metaTglDaftar').html(dataKunjungan.tglDaftar);
        APP.addValueInput('input[name="noKunjungan"]', dataKunjungan.noKunjungan);
        APP.addValueInput('input[name="tglPelayanan"]', dataKunjungan.tglDaftar);
    } else {
        $('#metaNoKunjungan').html(datamcu.noKunjungan);
        $('#metaTglDaftar').html(datamcu.tglPelayanan);
        APP.addValueInput('input[name="noKunjungan"]', datamcu.noKunjungan);
        APP.addValueInput('input[name="tglPelayanan"]', datamcu.tglPelayanan);
        APP.addValueInput('input[name="kdMCU"]', datamcu.kdMCU);
        APP.addValueInput('input[name="tekananDarahSistole"]', datamcu.tekananDarahSistole);
        APP.addValueInput('input[name="tekananDarahDiastole"]', datamcu.tekananDarahDiastole);
        // APP.addValueInput('input[name="radiologiFoto"]', datamcu.radiologiFoto);
        APP.addValueInput('input[name="darahRutinHemo"]', datamcu.darahRutinHemo);
        APP.addValueInput('input[name="darahRutinLeu"]', datamcu.darahRutinLeu);
        APP.addValueInput('input[name="darahRutinErit"]', datamcu.darahRutinErit);
        APP.addValueInput('input[name="darahRutinLaju"]', datamcu.darahRutinLaju);
        APP.addValueInput('input[name="darahRutinHema"]', datamcu.darahRutinHema);
        APP.addValueInput('input[name="darahRutinTrom"]', datamcu.darahRutinTrom);
        APP.addValueInput('input[name="lemakDarahHDL"]', datamcu.lemakDarahHDL);
        APP.addValueInput('input[name="lemakDarahLDL"]', datamcu.lemakDarahLDL);
        APP.addValueInput('input[name="lemakDarahChol"]', datamcu.lemakDarahChol);
        APP.addValueInput('input[name="lemakDarahTrigli"]', datamcu.lemakDarahTrigli);
        APP.addValueInput('input[name="gulaDarahSewaktu"]', datamcu.gulaDarahSewaktu);
        APP.addValueInput('input[name="gulaDarahPuasa"]', datamcu.gulaDarahPuasa);
        APP.addValueInput('input[name="gulaDarahPostPrandial"]', datamcu.gulaDarahPostPrandial);
        APP.addValueInput('input[name="gulaDarahHbA1c"]', datamcu.gulaDarahHbA1c);
        APP.addValueInput('input[name="fungsiHatiSGOT"]', datamcu.fungsiHatiSGOT);
        APP.addValueInput('input[name="fungsiHatiSGPT"]', datamcu.fungsiHatiSGPT);
        APP.addValueInput('input[name="fungsiHatiGamma"]', datamcu.fungsiHatiGamma);
        APP.addValueInput('input[name="fungsiHatiProtKual"]', datamcu.fungsiHatiProtKual);
        APP.addValueInput('input[name="fungsiHatiAlbumin"]', datamcu.fungsiHatiAlbumin);
        APP.addValueInput('input[name="fungsiGinjalCrea"]', datamcu.fungsiGinjalCrea);
        APP.addValueInput('input[name="fungsiGinjalUreum"]', datamcu.fungsiGinjalUreum);
        APP.addValueInput('input[name="fungsiGinjalAsam"]', datamcu.fungsiGinjalAsam);
        APP.addValueInput('input[name="fungsiJantungABI"]', datamcu.fungsiJantungABI);
        APP.addValueInput('input[name="fungsiJantungEKG"]', datamcu.fungsiJantungEKG);
        APP.addValueInput('input[name="fungsiJantungEcho"]', datamcu.fungsiJantungEcho);
        APP.addValueInput('input[name="funduskopi"]', datamcu.funduskopi);
        APP.addValueInput('#pemeriksaanLain', datamcu.pemeriksaanLain);
        APP.addValueInput('#keterangan', datamcu.keterangan);
    }
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
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.message,
                        confirmButtonText: 'Kembali ke Daftar MCU',
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }).then(() => {
                        window.location.href = 'module/admisi/listmcu.php';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Proses Gagal',
                        text: res.message || 'Terjadi kesalahan saat memproses data Medical Check Up.',
                        confirmButtonText: 'Tutup'
                    });
                }
            }
        })
    });
})