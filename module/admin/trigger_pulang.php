<?php
/**
 * Tombol "Selesaikan Pemeriksaan" pada menu RME (menu_rme / menu_rme_inap).
 * Tombol hanya tampil bila kunjungan belum selesai; status selesai/pulang (3, 4) atau batal (99) diganti label status.
 */
$tpNo = trim((string) ($_GET['no'] ?? ''));
$tpIdc = (int) ($_SESSION['id_customer'] ?? 0);
$tpStatus = null;
if ($tpNo !== '' && $tpIdc > 0 && isset($koneksi)) {
   $tpSt = $koneksi->prepare("SELECT visit_status FROM pasien_visit WHERE visit_ID = ? AND id_customer = ? LIMIT 1");
   $tpSt->bind_param('si', $tpNo, $tpIdc);
   $tpSt->execute();
   $tpStatus = $tpSt->get_result()->fetch_row()[0] ?? null;
   $tpSt->close();
}
$tpStatus = $tpStatus === null ? null : (int) $tpStatus;
?>
<?php if ($tpStatus === 3 || $tpStatus === 4): ?>
   <span class="nav-link text-success fw-semibold" style="cursor:default" title="Pemeriksaan kunjungan ini sudah diselesaikan">
      <i class="fas fa-check-circle me-2"></i> Pemeriksaan selesai
   </span>
<?php elseif ($tpStatus === 99): ?>
   <span class="nav-link text-danger fw-semibold" style="cursor:default" title="Kunjungan ini dibatalkan">
      <i class="fas fa-ban me-2"></i> Kunjungan batal
   </span>
<?php elseif ($tpStatus !== null): ?>
<button type="button" id="btnSelesai" class="nav-link text-success">
   <i class="fas fa-check-circle me-2"></i> Selesaikan Pemeriksaan
</button>
<script>
   document.getElementById("btnSelesai").addEventListener("click", function() {
      const btn = this;
      const visit_ID = new URLSearchParams(window.location.search).get("no");

      Swal.fire({
         title: "Selesaikan Pemeriksaan?",
         text: "Pasien akan dianggap selesai diperiksa.",
         icon: "warning",
         showCancelButton: true,
         confirmButtonText: "Ya, Selesaikan",
         cancelButtonText: "Batal"
      }).then((result) => {
         if (!result.isConfirmed) return;
         btn.disabled = true;
         fetch("controller/visit/selesaikanPemeriksaan.php", {
               method: "POST",
               headers: { "Content-Type": "application/json" },
               body: JSON.stringify({ visit_ID: visit_ID })
            })
            .then(res => res.json())
            .then(res => {
               Swal.fire({ icon: res.status, title: res.status === "success" ? "Berhasil" : "Gagal", text: res.message });
               if (res.status === "success") {
                  // tombol diganti label agar tidak bisa diklik ulang
                  btn.outerHTML = '<span class="nav-link text-success fw-semibold" style="cursor:default"><i class="fas fa-check-circle me-2"></i> Pemeriksaan selesai</span>';
                  setTimeout(() => { window.location.href = "module/doctor/pemeriksaan"; }, 1500);
               } else {
                  btn.disabled = false;
               }
            })
            .catch(err => {
               console.error(err);
               btn.disabled = false;
               Swal.fire("Error", "Gagal koneksi ke server", "error");
            });
      });
   });
</script>
<?php endif; ?>
