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
    let enableSocket = false;
    const data = JSON.parse(localStorage.getItem("rs_config"));
    let path_url = window.location.pathname;
    let parts = path_url.split("/");
    let moduleIndex = parts.indexOf("module");
    let moduleName = moduleIndex !== -1 ? parts[moduleIndex + 1].toUpperCase() : null;
    let pageName = moduleIndex !== -1 ? parts[moduleIndex + 2] : null;
    let target = moduleName;
    let pages = ["pemeriksaan"];
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
                    socket.emit("join", data.id_customer + "_" + pageName);
                } else if (pageName == 'pemeriksaan' && target == 'DOCTOR') {
                    socket.emit("join", data.id_customer + "_" + pageName + "_" + target + "_" + data.id_user);
                } else {
                    socket.emit("join", data.id_customer + "_" + target);
                }
            })
            // console.log(data.id_customer + "_" + target);
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
        socket.on("putar_suara_panggilan", (data) => {

            const myTabId = sessionStorage.getItem('tabId');
            const activeCallerTab = localStorage.getItem('activeCallerTab');

            console.log('=== DEBUG AUDIO ===');
            console.log('myTabId:', myTabId);
            console.log('activeCallerTab:', activeCallerTab);
            console.log('socket data:', data);

            if (myTabId !== activeCallerTab) {
                console.log('SKIP: tab tidak cocok');
                return;
            }

            console.log('TAB COCOK');

            if (!data.audioBase64) {
                console.log('audioBase64 kosong');
                return;
            }

            console.log('Panjang audio:', data.audioBase64.length);

            const audioFormat = `data:audio/mp3;base64,${data.audioBase64}`;

            const audio = new Audio(audioFormat);

            audio.onloadeddata = () => {
                console.log('Audio berhasil dimuat');
            };

            audio.onerror = (e) => {
                console.log('Audio error:', e);
            };

            audio.play()
                .then(() => {
                    console.log('Suara berhasil diputar');
                })
                .catch(err => {
                    console.error('Play error:', err);
                });
        });
    }
}