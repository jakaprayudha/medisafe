window.APP = window.APP || {};
const audioContext = new (window.AudioContext || window.webkitAudioContext)();

document.addEventListener('click', async () => {
    if (audioContext.state === 'suspended') {
        await audioContext.resume();
    }
});
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
    let enableSocket = false;
    const data = JSON.parse(localStorage.getItem("rs_config"));
    let path_url = window.location.pathname;
    let parts = path_url.split("/");
    let moduleIndex = parts.indexOf("module");
    let moduleName = moduleIndex !== -1 ? parts[moduleIndex + 1].toUpperCase() : null;
    let pageName = moduleIndex !== -1 ? parts[moduleIndex + 2] : null;
    let target = moduleName;
    let pages = ["pemeriksaan", "farmasi_order_detail", "display-admisi"];
    // let pages = ["counter-call", "display-admisi"];
    if (pages.includes(pageName)) {
        enableSocket = true;
    }
    if (enableSocket) {
        const socket = io(base_url, {
            reconnection: true
        });
        socket.on("connect", () => {
            pages.forEach((pageName) => {
                if (pageName == "display-admisi") {
                    socket.emit("join", data.id_customer + "_" + pageName + "_" + target);
                    console.log(data.id_customer + "_" + pageName + "_" + target);
                } else if (pageName == 'pemeriksaan' && target == 'DOCTOR') {
                    socket.emit("join", data.id_customer + "_" + pageName + "_" + target + "_" + data.id_user);
                    console.log(data.id_customer + "_" + pageName + "_" + target + "_" + data.id_user);
                } else if (pageName == "farmasi_order_detail" && target == "ADMIN") {
                    socket.emit("join", data.id_customer + "_" + pageName + "_" + target + "_" + data.id_user);
                    console.log(data.id_customer + "_" + pageName + "_" + target + "_" + data.id_user);
                }
            })

            // console.log(data.id_customer + "_" + pageName + "_" + target + "_" + data.id_user);
            // console.log(pageName);
            // console.log(data);
        });
        socket.on('trigger', data => {
            let key = target + "/" + pageName;
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
            let key = target + "/" + pageName;
            console.log(key)
            switch (key) {
                case "DISPLAY/display-admisi":
                    APP.CallAntrian(data.type, data.nomor, data.loket, data.idantrian, "pendaftaran");
                    break;
            }
        });

        socket.on("display_antian_poli", data => {
            APP.showAntrianPoli(data.nama, data.dokter);
            console.log(data);
        });

        socket.on("putar_suara_panggilan", (data) => {
            // console.log("DATA SOCKET:", data);
            const myRequestId = sessionStorage.getItem('requestId');
            // console.log("MY REQUEST:", myRequestId);
            // console.log("SOCKET REQUEST:", data.requestId);
            if (data.requestId !== myRequestId) {
                // console.log("SKIP");
                return;
            }
            console.log("PLAY AUDIO");
            const audio = new Audio(
                `data:audio/mp3;base64,${data.audioBase64}`
            );
            audio.play();
        });
    }
}