window.APP = window.APP || {}
$(function () {
    $('#nomor').on('input', function () {
        this.value = this.value.replace(/[^a-zA-Z0-9]/g, '');
        if (this.value.length > 18) {
            this.value = this.value.substring(0, 19);
            $('#cari').prop('disabled', false);
        } else {
            $('#cari').prop('disabled', true);
        }
    })
    $('#cari').on('click', function () {
        const nomor = $('#nomor').val();
        const btn = $(this);
        $.ajax({
            url: 'controller/admisi/services/sercDataKunjungan.php',
            type: "GET",
            dataType: 'json',
            data: {
                nomor: nomor
            },
            beforeSend: function () {
                APP.load_btn_aktif(btn);
            },
            complete: function () {
                APP.load_btn_non(btn, `<iconify-icon icon="solar:magnifer-linear"></iconify-icon>Cari`);
            },
            success: function (response) {
                if (response.success) {
                    const data = response.data;
                    $('#m_noRujukan').val(data.noRujukan);
                    $('#m_tglKunjungan').val(data.tglKunjungan);
                    $('#m_kdPPK').val(data.ppk?.kdPPK);
                    $('#m_nmPPK').val(data.ppk?.nmPPK);
                    $('#m_nmKC').val(data.ppk?.kc?.nmKC);
                    $('#m_nmDati').val(data.ppk?.kc?.dati?.nmDati);
                    $('#m_nokaPst').val(data.nokaPst);
                    $('#m_nmPst').val(data.nmPst);
                    $('#m_tglLahir').val(data.tglLahir);
                    $('#m_sex').val(data.sex === 'L' ? 'Laki-laki' : 'Perempuan');
                    $('#m_nmPoli').val(data.poli?.nmPoli);
                    if (data.diag1) {
                        $('#m_nmDiag1').val(data.diag1.kdDiag + ' - ' + data.diag1.nmDiag);
                    }
                    $('#m_nmDokter').val(data.dokter?.nmDokter);
                    $('#m_infoDenda').val(data.infoDenda);
                    $('#modalDetailRujukan').modal('show');
                } else {
                    Swal.fire({
                        title: "Opss..",
                        text: "Data Tidak Ditemukan",
                        icon: "error"
                    });
                }
            }
        })
    })
})