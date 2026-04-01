window.APP = window.APP || {};
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
                if (pageName == "display-admisi") {
                    socket.emit("join", data.id_customer + "_" + pageName);
                } else {
                    socket.emit("join", data.id_customer + "_" + target);
                }
                // console.log(data.id_customer + "_" + target);
                // console.log(pageName);
            });
            socket.on('trigger', data => {
                let key = target +"/"+ pageName;
                // console.log(key);
                switch (key) {
                    case "ADMISI/counter-call":
                        APP.resetTable();
                        break;
                    case "DISPLAY/display-admisi":
                        APP.showQueue();
                        break;
                }
            });
            socket.on("panggil", data => {
                let key = target +"/"+ pageName;
                console.log(key)
                switch (key) {
                    case "DISPLAY/display-admisi":
                        APP.CallAntrian(data.type, data.nomor, data.loket, data.idantrian, "pendaftaran");
                        break;
                }
            })
        }
    });
}