window.APP = window.APP || {};
// const base_url = "http://localhost:3001";
const base_url = "https://websocketservermedicine.online";
if (!window.io) {
    const script = document.createElement("script");
    script.src = base_url + "/socket.io/socket.io.js";
    script.onload = startApp;
    document.head.appendChild(script);
} else {
    startApp();
}
function startApp() {
    let enableSocket = true;
    const idDisplaypoli = localStorage.getItem("display_id");
    const data = JSON.parse(localStorage.getItem("rs_config"));
    let path_url = window.location.pathname;
    let parts = path_url.split("/");
    let moduleIndex = parts.indexOf("module");
    let moduleName = moduleIndex !== -1 ? parts[moduleIndex + 1].toUpperCase() : null;
    let pageName = moduleIndex !== -1 ? parts[moduleIndex + 2] : null;
    let target = moduleName;
    $(document).ready(function () {
        if (enableSocket) {
            const socket = io(base_url, {
                reconnection: true
            });
            socket.on("connect", () => {
                if (target == "DISPLAYPEMANGGILANPOLI") {
                    socket.emit("join", data.id_customer + "_" + target + "_" + idDisplaypoli);
                    target = "DISPLAYPEMANGGILANPOLI_" + idDisplaypoli;
                } else {
                    socket.emit("join", data.id_customer + "_" + target);
                }
                // console.log(data.id_customer + "_" + target);
                //  console.log(pageName);
            });
            socket.on('trigger', data => {
                let key = target +"/"+ pageName;
                // console.log(key);
                switch (key) {
                    case "ADMISI/counter-call":
                        APP.resetTable();
                        break;
                }
            });
            socket.on("panggil", data => {
                switch (target) {
                    case "DISPLAYPEMANGGILANADMISI":
                        APP.addAntaianAdmisi(data.type, data.nomor, data.loket, data.idantrian, "admisi");
                        break;
                    case "DISPLAYPEMANGGILANPOLI_" + idDisplaypoli:
                        APP.addAntaianPoli(data.type, data.nomor, data.loket, data.idantrian);
                        break;
                    case "DISPLAYPEMANGGILANFARMASI":
                        APP.addAntaianFarmasi(data.type, data.nomor, data.loket, data.idantrian, "farmasi");
                        break;
                }
            })
        }
    });
}