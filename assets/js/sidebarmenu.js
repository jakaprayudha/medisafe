/*
 * Penanda menu aktif di sidebar (dipakai semua role lewat module/admin/library.php).
 *
 * - Link yang cocok dengan halaman sekarang diberi class "active".
 *   Cocok = path sama (tanpa .php / query / garis miring akhir), atau halaman turunan
 *   (mis. patient_details -> "patient", pharmacy-details -> "pharmacy"); dipilih yang paling spesifik.
 * - Bila link berada di dalam dropdown (.sidebar-item.has-sub), dropdown dibuka otomatis
 *   (tanpa animasi) dan judul dropdown ditandai "has-active".
 * - Menu aktif digulir agar terlihat di sidebar.
 * Dulu: dropdown tidak pernah terbuka saat load, dan klik menu apa pun (termasuk membuka dropdown)
 * menghapus class "active" dari semua link sehingga penanda halaman sekarang hilang.
 */
(function () {
  "use strict";

  function bersih(p) {
    try {
      p = new URL(p, document.baseURI).pathname;
    } catch (e) {
      p = String(p || "");
    }
    return decodeURIComponent(p).replace(/\/index(\.php)?$/i, "/").replace(/\.php$/i, "").replace(/\/+$/, "").toLowerCase();
  }

  function tandai() {
    var nav = document.getElementById("sidebarnav");
    if (!nav) return;
    var sekarang = bersih(location.pathname);
    var nama = sekarang.split("/").pop();
    var folder = sekarang.slice(0, sekarang.length - nama.length);
    var terbaik = null;
    var skor = 0;

    nav.querySelectorAll("a[href]").forEach(function (a) {
      var h = a.getAttribute("href");
      if (!h || h.charAt(0) === "#" || /^javascript:/i.test(h)) return;
      var p = bersih(a.href);
      var s = 0;
      if (p === sekarang) {
        s = 1000 + p.length;
      } else {
        // halaman turunan di folder yang sama: patient_details -> patient
        var n = p.split("/").pop();
        var f = p.slice(0, p.length - n.length);
        if (n && f === folder && nama.length > n.length && nama.indexOf(n) === 0 && /[_-]/.test(nama.charAt(n.length))) {
          s = n.length;
        }
      }
      if (s > skor) {
        skor = s;
        terbaik = a;
      }
    });
    if (!terbaik) return;

    // hanya satu penanda halaman: bersihkan class active bawaan template yang bentrok di level yang sama
    nav.querySelectorAll(".collapse-menu a.active").forEach(function (x) {
      if (x !== terbaik) x.classList.remove("active");
    });
    terbaik.classList.add("active");

    var grup = terbaik.closest(".sidebar-item.has-sub");
    if (grup) {
      grup.classList.add("open", "has-active");
      var menu = grup.querySelector(".collapse-menu");
      if (menu) {
        // buka tanpa animasi saat halaman dimuat
        var t = menu.style.transition;
        menu.style.transition = "none";
        menu.style.maxHeight = menu.scrollHeight + "px";
        void menu.offsetHeight;
        menu.style.transition = t;
      }
    }

    // gulir agar menu aktif terlihat (sidebar memakai simplebar)
    setTimeout(function () {
      var wadah = terbaik.closest(".simplebar-content-wrapper") || terbaik.closest(".scroll-sidebar");
      if (!wadah) return;
      var a = terbaik.getBoundingClientRect();
      var w = wadah.getBoundingClientRect();
      if (a.top < w.top + 40 || a.bottom > w.bottom - 40) {
        wadah.scrollTop += a.top - w.top - w.height / 3;
      }
    }, 150);
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", tandai);
  } else {
    tandai();
  }
})();
