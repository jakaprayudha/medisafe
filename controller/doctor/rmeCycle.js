window.APP = window.APP || {};
window.APP.pemeriksaanSaveUrl = "controller/doctor/saveRmeCycle.php";
$(function () {
    $("#rmeCycleFields").find("input, select, textarea").each(function () {
        if (!this.closest("form")) this.setAttribute("form", "formPemeriksaan");
    });
    $("#formPemeriksaan").on("submit", function (event) {
        event.preventDefault();
        $("#simpan_pemeriksaan").trigger("click");
    });
});
