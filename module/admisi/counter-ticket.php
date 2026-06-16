<?php
$title = 'Ambil Tiket Antrean Admisi';
require '../../controller/view.php';
?>
<!doctype html>
<html lang="id">
<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>

  <style>
    body {
      background: linear-gradient(135deg, #e8f1ff, #f8fbff);
    }
    .kiosk-wrapper {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .kiosk-card {
      max-width: 520px;
      width: 100%;
      border-radius: 20px;
      box-shadow: 0 20px 40px rgba(0,0,0,.12);
    }
    .service-option {
      cursor: pointer;
      border: 2px solid #e5e7eb;
      border-radius: 16px;
      padding: 20px;
      transition: all .2s ease;
      text-align: center;
    }
    .service-option:hover {
      border-color: #3b82f6;
      background: #eff6ff;
    }
    .service-option.active {
      border-color: #2563eb;
      background: #dbeafe;
    }
    .queue-number {
      font-size: 64px;
      font-weight: 800;
      letter-spacing: 2px;
    }
  </style>
</head>

<body>

<div class="kiosk-wrapper">
  <div class="card kiosk-card">
    <div class="card-body p-4">

      <!-- HEADER -->
      <div class="text-center mb-4">
        <h3 class="fw-bold mb-1">Ambil Tiket Antrean</h3>
        <p class="text-muted mb-0">Layanan Admisi</p>
      </div>

      <!-- PILIH JENIS -->
      <div class="row g-3 mb-3">
        <div class="col-6">
          <div class="service-option" onclick="selectJenis('BPJS', this)">
            <h5 class="fw-bold text-primary">BPJS</h5>
            <small class="text-muted">Peserta BPJS</small>
          </div>
        </div>
        <div class="col-6">
          <div class="service-option" onclick="selectJenis('UMUM', this)">
            <h5 class="fw-bold text-success">UMUM</h5>
            <small class="text-muted">Pasien Umum</small>
          </div>
        </div>
      </div>

      <input type="hidden" id="jenisLayanan">

      <!-- FORM -->
      <div class="mb-3">
        <label class="form-label">Nama Pasien</label>
        <input type="text" id="namaPasien" class="form-control form-control-lg"
               placeholder="Masukkan nama lengkap">
      </div>

      <div class="mb-4">
        <label class="form-label">Pilih Poli</label>
        <select id="poli" class="form-select form-select-lg">
          <option value="1">Poli Umum</option>
          <option value="2">Poli Gigi</option>
          <option value="3">Poli Anak</option>
        </select>
      </div>

      <button class="btn btn-primary btn-lg w-100" onclick="ambilAntrean()">
        Ambil Tiket
      </button>

      <!-- RESULT -->
      <div id="result" class="text-center mt-4 d-none">
        <small class="text-muted">Nomor Antrean Anda</small>
        <div class="queue-number text-primary" id="nomorAntrean">-</div>
        <p class="text-muted mt-2">Silakan menunggu panggilan</p>
      </div>

    </div>
  </div>
</div>

<?php require '../admin/library.php'; ?>
</body>
</html>

<script>
function selectJenis(jenis, el) {
   document.getElementById('jenisLayanan').value = jenis;
   document.querySelectorAll('.service-option')
      .forEach(e => e.classList.remove('active'));
   el.classList.add('active');
}

function ambilAntrean() {
   const jenis = document.getElementById('jenisLayanan').value;
   const nama  = document.getElementById('namaPasien').value.trim();
   const poli  = document.getElementById('poli').value;

   if (!jenis) {
      alert('Pilih jenis layanan BPJS atau UMUM');
      return;
   }
   if (!nama) {
      alert('Nama pasien wajib diisi');
      return;
   }

   fetch('controller/queue/admisiTicket.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
         jenis_layanan: jenis,
         nama_pasien: nama,
         id_poli: poli
      })
   })
   .then(res => res.json())
   .then(res => {
      if (res.status === 'success') {
         document.getElementById('nomorAntrean').textContent = res.no_antrian;
         document.getElementById('result').classList.remove('d-none');

      // AUTO PRINT
      window.open(
         `module/print/e-ticket-admisi.php?no_antrian=${res.no_antrian}&nama=${encodeURIComponent(res.nama_pasien)}&poli=${res.poli}`,
         '_blank'
      );
      } else {
         alert(res.message);
      }
   });
}
</script>