<?php
$title = 'eKYC TTE';
require '../../controller/view.php';

// 🔐 Simpan / upload / verifikasi / hapus hanya untuk module admin
$canManageEkyc = in_array($_SESSION['roles'] ?? '', ['admin', 'superadmin'], true);
?>
<!doctype html>
<html lang="en">

<head>
  <base href="../../">
  <?php
  require '../../assets/template/head.php';
  ?>
</head>

<body>
  <!--  Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <!-- Sidebar Start -->
    <?php
    require 'sidebar.php';
    ?>
    <!--  Sidebar End -->
    <!--  Main wrapper -->
    <div class="body-wrapper">
      <!--  Header Start -->
      <?php
      require 'navbar.php';
      ?>
      <!--  Header End -->
      <div class="body-wrapper-inner">
        <div class="container-fluid">
          <div class="row">
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                    <div>
                      <h5 class="card-title fw-semibold mb-1">eKYC Tanda Tangan Elektronik (TTE)</h5>
                      <small class="text-muted">Registrasi identitas pemohon TTE: nama, email, nomor HP, dan NIK</small>
                    </div>
                    <?php if ($canManageEkyc): ?>
                      <div class="d-flex ms-auto gap-2">
                        <button class="btn btn-primary" id="btnTambah"><i class="fas fa-plus"></i> Tambah</button>
                      </div>
                    <?php endif; ?>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTable">
                      <thead>
                        <tr>
                          <th class="text-dark fw-normal col-1">ID</th>
                          <th scope="col" class="text-dark fw-normal">Nama</th>
                          <th scope="col" class="text-dark fw-normal">Email</th>
                          <th class="text-dark fw-normal">Nomor HP</th>
                          <th class="text-dark fw-normal">NIK</th>
                          <th class="text-dark fw-normal">Registrasi</th>
                          <th scope="col" class="text-dark fw-normal text-center col-1">Status</th>
                          <th scope="col" class="text-dark fw-normal text-center col-1">Actions</th>
                        </tr>
                      </thead>
                      <tbody></tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php
  require 'library.php';
  ?>
</body>

<!-- MODAL TAMBAH EKYC -->
<div class="modal fade" id="ekycModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="ekycForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Tambah eKYC TTE</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-12 mb-3">
            <label class="form-label required">Nama Lengkap</label>
            <input type="text" name="nama" id="nama" class="form-control" placeholder="Nama sesuai KTP" required>
          </div>
          <div class="col-12 mb-3">
            <label class="form-label required">NIK</label>
            <input type="text" name="nik" id="nik" class="form-control" placeholder="16 digit NIK" maxlength="16" inputmode="numeric" required>
            <small class="text-muted">NIK harus 16 digit angka.</small>
          </div>
          <div class="col-12 mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" id="email" class="form-control" placeholder="email@contoh.com">
          </div>
          <div class="col-12 mb-3">
            <label class="form-label">Nomor HP</label>
            <input type="text" name="nomor_hp" id="nomor_hp" class="form-control" placeholder="08xxxxxxxxxx" maxlength="15" inputmode="numeric">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
<script>
  const apiUrl = 'controller/master/ekycController';
  const canManageEkyc = <?= $canManageEkyc ? 'true' : 'false' ?>;

  $(document).ready(function() {
    var table = $('#periodeTable').DataTable({
      processing: true,
      serverSide: false,
      scrollX: true,
      ajax: {
        url: apiUrl,
        type: "GET",
        dataSrc: function(json) {
          return (json.data || []).map(function(row) {
            return {
              "id": row.id_ekyc,
              "nama": row.nama ?? '-',
              "email": row.email ?? '-',
              "nomor_hp": row.nomor_hp ?? '-',
              "nik": row.nik ?? '-',
              "registrasi": row.created_at ?? '-',
              "status": canManageEkyc ? `
                <label class="switch">
                  <input type="checkbox" class="toggle-status"
                    data-id="${row.id_ekyc}"
                    ${row.status == '1' ? 'checked' : ''}>
                  <span class="slider round"></span>
                </label>
              ` : `
                <span class="badge ${row.status == '1' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'}">
                  ${row.status == '1' ? 'Aktif' : 'Nonaktif'}
                </span>
              `,
              "actions": `
                <div class="text-center">
                  <div class="btn-group btn-group-sm" role="group">
                    <a class="btn btn-outline-primary detail-btn" href="javascript:;" data-id="${row.id_ekyc}" title="Detail & Verifikasi">
                      <i class="fas fa-id-card"></i>
                    </a>
                    ${canManageEkyc ? `
                    <a class="btn btn-danger delete-btn" href="javascript:;" data-id="${row.id_ekyc}" title="Hapus">
                      <i class="fas fa-trash"></i>
                    </a>` : ''}
                  </div>
                </div>
              `
            };
          });
        }
      },
      columns: [{
          data: "id"
        },
        {
          data: "nama"
        },
        {
          data: "email"
        },
        {
          data: "nomor_hp"
        },
        {
          data: "nik"
        },
        {
          data: "registrasi"
        },
        {
          data: "status"
        },
        {
          data: "actions",
          orderable: false,
          searchable: false
        },
      ]
    });

    // 🔹 Tambah
    $('#btnTambah').on('click', function() {
      $('#ekycForm')[0].reset();
      $('#ekycModal').modal('show');
    });

    // 🔹 Submit
    $('#ekycForm').on('submit', function(e) {
      e.preventDefault();
      let formData = new URLSearchParams(new FormData(this));

      fetch(apiUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: formData
        })
        .then(res => res.json())
        .then(data => {
          if (data.status === 'success') {
            Swal.fire('Berhasil!', data.message, 'success');
            $('#ekycModal').modal('hide');
            table.ajax.reload(null, false);
          } else {
            Swal.fire('Gagal!', data.message || 'Data gagal disimpan.', 'error');
          }
        })
        .catch(() => {
          Swal.fire('Gagal!', 'Tidak dapat terhubung ke server.', 'error');
        });
    });

    // 🔹 Toggle Status
    $(document).on('change', '.toggle-status', function() {
      let id = $(this).data('id');
      let status = $(this).is(':checked') ? 1 : 0;

      fetch(apiUrl, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: `id_ekyc=${id}&status=${status}`
        })
        .then(res => res.json())
        .then(res => {
          if (res.status === 'success') {
            Swal.fire({
              icon: 'success',
              title: 'Status diperbarui',
              timer: 1200,
              showConfirmButton: false
            });
          } else {
            Swal.fire('Gagal!', res.message, 'error');
            table.ajax.reload(null, false);
          }
        });
    });

    // 🔹 Delete
    $(document).on('click', '.delete-btn', function() {
      let id = $(this).data('id');
      Swal.fire({
        title: 'Hapus Data eKYC?',
        text: 'Data yang dihapus tidak dapat dikembalikan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#d33'
      }).then((result) => {
        if (result.isConfirmed) {
          fetch(apiUrl + `?id=${id}`, {
              method: 'DELETE'
            })
            .then(res => res.json())
            .then(data => {
              if (data.status === 'success') {
                Swal.fire('Berhasil!', data.message, 'success');
                table.ajax.reload(null, false);
              } else {
                Swal.fire('Gagal!', data.message, 'error');
              }
            });
        }
      });
    });

    /* =====================================================
       DETAIL & VERIFIKASI
    ===================================================== */

    let currentEkycId = null;
    let registeredNik = '';
    let registeredNama = '';
    let currentStatusVerifikasi = 'belum';

    const statusVerifikasiMap = {
      'belum': {
        label: 'Belum Upload',
        cls: 'bg-secondary'
      },
      'ktp_menunggu': {
        label: 'KTP Menunggu Verifikasi',
        cls: 'bg-warning text-dark'
      },
      'ktp_terverifikasi': {
        label: 'KTP Terverifikasi',
        cls: 'bg-info'
      },
      'menunggu': {
        label: 'Menunggu Verifikasi',
        cls: 'bg-warning text-dark'
      },
      'terverifikasi': {
        label: 'Terverifikasi',
        cls: 'bg-success'
      },
      'ditolak': {
        label: 'Ditolak',
        cls: 'bg-danger'
      }
    };

    function renderPreview(file, imgEl, emptyEl) {
      if (file) {
        $(imgEl).attr('src', 'uploads/ekyc/' + file).removeClass('d-none');
        $(emptyEl).addClass('d-none');
      } else {
        $(imgEl).addClass('d-none').attr('src', '');
        $(emptyEl).removeClass('d-none');
      }
    }

    function openDetail(id) {
      currentEkycId = id;

      fetch(apiUrl + '?id=' + id)
        .then(res => res.json())
        .then(res => {
          if (res.status !== 'success' || !res.data) {
            Swal.fire('Gagal!', res.message || 'Data tidak ditemukan.', 'error');
            return;
          }

          const d = res.data;

          registeredNik = d.nik || '';
          registeredNama = d.nama || '';
          currentStatusVerifikasi = d.status_verifikasi || 'belum';

          $('#dNama').text(d.nama || '-');
          $('#dNik').text(d.nik || '-');
          $('#dEmail').text(d.email || '-');
          $('#dHp').text(d.nomor_hp || '-');

          const sv = statusVerifikasiMap[currentStatusVerifikasi] || statusVerifikasiMap['belum'];
          $('#dStatusVerifikasi')
            .removeClass()
            .addClass('badge ' + sv.cls)
            .text(sv.label);

          renderPreview(d.ktp_file, '#prevKtp', '#prevKtpEmpty');
          renderPreview(d.face_file, '#prevFace', '#prevFaceEmpty');

          // reset OCR
          $('#ocrPanel').addClass('d-none');
          $('#ocrResult').addClass('d-none');
          $('#ocrLoading').removeClass('d-none');

          // 🔒 gating liveness: hanya setelah KTP terverifikasi
          if (currentStatusVerifikasi === 'ktp_terverifikasi') {
            $('#livenessLock').addClass('d-none');
          } else {
            $('#livenessLock').removeClass('d-none');
          }

          // tombol verifikasi KTP hanya saat ktp_menunggu
          $('#btnVerifikasiKtp').toggleClass('d-none', currentStatusVerifikasi !== 'ktp_menunggu');

          // tombol verifikasi akhir hanya saat menunggu
          $('#btnVerifikasi').toggleClass('d-none', currentStatusVerifikasi !== 'menunggu');

          $('#fileKtp').val('');
          $('#fileFace').val('');

          $('#ekycDetailModal').modal('show');
        })
        .catch(() => {
          Swal.fire('Gagal!', 'Tidak dapat terhubung ke server.', 'error');
        });
    }

    $(document).on('click', '.detail-btn', function() {
      openDetail($(this).data('id'));
    });

    // 🔹 Upload file (KTP / liveness)
    function uploadEkyc(type, fileInput, btn) {
      if (!currentEkycId) return;

      const file = fileInput.files[0];

      if (!file) {
        Swal.fire('Perhatian', 'Pilih file terlebih dahulu.', 'warning');
        return;
      }

      if (file.size > 5 * 1024 * 1024) {
        Swal.fire('Perhatian', 'Ukuran file maksimal 5 MB.', 'warning');
        return;
      }

      const formData = new FormData();
      formData.append('action', 'upload');
      formData.append('type', type);
      formData.append('id_ekyc', currentEkycId);
      formData.append('file', file);

      btn.prop('disabled', true);

      fetch(apiUrl, {
          method: 'POST',
          body: formData
        })
        .then(res => res.json())
        .then(res => {
          if (res.status === 'success') {
            Swal.fire({
              icon: 'success',
              title: 'Berhasil!',
              text: res.message,
              timer: 1200,
              showConfirmButton: false
            });
            openDetail(currentEkycId);
            table.ajax.reload(null, false);
          } else {
            Swal.fire('Gagal!', res.message, 'error');
          }
        })
        .catch(() => {
          Swal.fire('Gagal!', 'Gagal mengupload file.', 'error');
        })
        .finally(() => {
          btn.prop('disabled', false);
        });
    }

    $('#btnUploadKtp').on('click', function() {
      uploadEkyc('ktp', document.getElementById('fileKtp'), $(this));
    });

    $('#btnUploadFace').on('click', function() {
      uploadEkyc('liveness', document.getElementById('fileFace'), $(this));
    });

    /* =====================================================
       OCR BACA KTP (NIK & NAMA)
    ===================================================== */

    // Peta salah-baca umum OCR -> angka (O->0, I/l->1, dst)
    const OCR_DIGIT_MAP = {
      'O': '0',
      'o': '0',
      'Q': '0',
      'D': '0',
      'I': '1',
      'l': '1',
      'i': '1',
      '|': '1',
      '!': '1',
      'S': '5',
      's': '5',
      'B': '8',
      'Z': '2',
      'z': '2',
      'G': '6',
      'g': '9',
      'q': '9',
      'T': '7',
      'A': '4'
    };

    function mapOcrDigits(str) {
      let out = '';
      for (const ch of str) {
        if (ch >= '0' && ch <= '9') {
          out += ch;
        } else if (OCR_DIGIT_MAP[ch]) {
          out += OCR_DIGIT_MAP[ch];
        } else {
          out += ' '; // pisahkan agar label tidak menyatu dengan angka
        }
      }
      return out;
    }

    // cari window 16 digit yang tidak diapit digit lain
    function findNikWindow(mapped) {
      const re = /\d{16}/g;
      let m;
      let lastMatch = null;
      while ((m = re.exec(mapped)) !== null) {
        const before = mapped[m.index - 1] || ' ';
        const after = mapped[m.index + 16] || ' ';
        if (!/\d/.test(before) && !/\d/.test(after)) {
          return m[0];
        }
        lastMatch = m[0];
      }
      return lastMatch || '';
    }

    // Ekstraksi NIK 16 digit, toleran salah baca OCR
    function extractNikFromText(text) {
      const lines = text.split(/\r?\n/);

      // Prioritas 1: baris yang mengandung kata "NIK"
      const sortedLines = lines.slice().sort(function(a, b) {
        return (/NIK/i.test(b) ? 1 : 0) - (/NIK/i.test(a) ? 1 : 0);
      });

      for (const line of sortedLines) {
        const found = findNikWindow(mapOcrDigits(line));
        if (found) return found;

        // digit menyatu / label ikut terbaca -> squash & sliding window
        const squashed = mapOcrDigits(line).replace(/\s+/g, '');
        if (squashed.length >= 16) {
          let last = '';
          for (let i = 0; i + 16 <= squashed.length; i++) {
            last = squashed.substr(i, 16);
          }
          if (last) return last;
        }
      }

      // Prioritas 2: token alfanumerik panjang (NIK kadang terbaca menyatu)
      const tokens = text.match(/[0-9A-Za-z|!]{10,25}/g) || [];
      for (const token of tokens) {
        const squashed = mapOcrDigits(token).replace(/\s+/g, '');
        if (squashed.length === 16) return squashed;
        const m = squashed.match(/\d{16}/);
        if (m) return m[0];
      }

      return '';
    }

    // Ekstraksi nama dari baris berlabel "Nama"
    function extractNamaFromText(text) {
      const lines = text.split(/\r?\n/).map(function(l) {
        return l.trim();
      }).filter(Boolean);

      for (let i = 0; i < lines.length; i++) {
        if (/\bnama\b/i.test(lines[i])) {
          // teks setelah label pada baris yang sama
          const sameLine = lines[i].replace(/.*nama[:\s]*/i, '').trim();
          if (sameLine && /[A-Za-z]{3,}/.test(sameLine)) {
            return sameLine;
          }
          // baris berikutnya
          if (lines[i + 1]) {
            const next = lines[i + 1].replace(/[^A-Za-z .,'-]/g, ' ').replace(/\s+/g, ' ').trim();
            if (/[A-Za-z]{3,}/.test(next)) {
              return next;
            }
          }
        }
      }
      return '';
    }

    // Pra-pemrosesan gambar: upscale + grayscale + kontras
    // Menerima File (pilihan baru) atau URL string (KTP yang sudah terupload)
    function preprocessKtpImage(source) {
      return new Promise(function(resolve, reject) {
        const img = new Image();
        img.onload = function() {
          const scale = Math.min(3, 2400 / img.width);
          const canvas = document.createElement('canvas');
          canvas.width = Math.round(img.width * scale);
          canvas.height = Math.round(img.height * scale);
          const ctx = canvas.getContext('2d');
          ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

          const imgData = ctx.getImageData(0, 0, canvas.width, canvas.height);
          const d = imgData.data;
          for (let i = 0; i < d.length; i += 4) {
            const gray = 0.299 * d[i] + 0.587 * d[i + 1] + 0.114 * d[i + 2];
            const contrasted = Math.min(255, Math.max(0, (gray - 128) * 1.6 + 128));
            d[i] = d[i + 1] = d[i + 2] = contrasted;
          }
          ctx.putImageData(imgData, 0, 0);
          if (source instanceof File) {
            URL.revokeObjectURL(img.src);
          }
          resolve(canvas);
        };
        img.onerror = reject;
        img.src = source instanceof File ? URL.createObjectURL(source) : source;
      });
    }

    // Levenshtein kecil untuk toleransi salah baca OCR pada huruf
    function levenshtein(a, b) {
      const m = a.length,
        n = b.length;
      if (!m) return n;
      if (!n) return m;
      let prev = Array.from({
        length: n + 1
      }, function(_, i) {
        return i;
      });
      for (let i = 1; i <= m; i++) {
        const cur = [i];
        for (let j = 1; j <= n; j++) {
          cur[j] = Math.min(prev[j] + 1, cur[j - 1] + 1, prev[j - 1] + (a[i - 1] === b[j - 1] ? 0 : 1));
        }
        prev = cur;
      }
      return prev[n];
    }

    // bersihkan kandidat nama: huruf saja, buang kata label umum
    function cleanNameCandidate(str) {
      let s = String(str || '').toUpperCase().replace(/[^A-Z., ]/g, ' ');
      s = s.replace(/\b(NAMA|KTP|NIK|REPUBLIK|INDONESIA|KARTU|TANDA|PENDUDUK)\b/g, ' ');
      s = s.replace(/[.,]+/g, ' ').replace(/\s+/g, ' ').trim();
      return s;
    }

    // ekstrak nama dari label "Nama" (baris sama atau 1-2 baris berikutnya)
    function extractNamaFromText(text) {
      const lines = text.split(/\r?\n/).map(function(l) {
        return l.trim();
      }).filter(Boolean);

      for (let i = 0; i < lines.length; i++) {
        if (/nama/i.test(lines[i])) {
          // teks setelah label pada baris yang sama
          const after = lines[i].replace(/.*nama[:\s]*/i, '');
          let candidate = cleanNameCandidate(after);
          if (candidate.length >= 3) return candidate;

          // baris berikutnya
          for (let j = i + 1; j < Math.min(i + 3, lines.length); j++) {
            candidate = cleanNameCandidate(lines[j]);
            if (candidate.length >= 3) return candidate;
          }
        }
      }
      return '';
    }

    // cocokkan kata nama terdaftar dengan teks OCR (toleran 1-2 huruf salah baca)
    function matchRegisteredName(words, ocrTextUpper) {
      const ocrWords = ocrTextUpper.split(/\s+/).filter(function(w) {
        return w.length >= 2;
      });
      const matched = [];
      const missed = [];

      for (const w of words) {
        const tol = w.length >= 6 ? 2 : 1;
        const hit = ocrWords.some(function(ow) {
          if (ow === w) return true;
          if (Math.abs(ow.length - w.length) > tol) return false;
          return levenshtein(ow, w) <= tol;
        });
        (hit ? matched : missed).push(w);
      }

      return {
        matched: matched,
        missed: missed
      };
    }

    function runOcrKtp(file) {
      if (typeof Tesseract === 'undefined') return;

      $('#ocrPanel').removeClass('d-none');
      $('#ocrLoading').removeClass('d-none').html('<span class="spinner-border spinner-border-sm me-1"></span>Membaca KTP...');
      $('#ocrResult').addClass('d-none');

      preprocessKtpImage(file)
        .then(function(canvas) {
          // Bahasa Indonesia + Inggris, fallback Inggris jika data ind tidak tersedia
          return Tesseract.recognize(canvas, 'ind+eng', {
              logger: function() {}
            })
            .catch(function() {
              return Tesseract.recognize(canvas, 'eng', {
                logger: function() {}
              });
            });
        })
        .then(function(result) {
          const text = (result && result.data && result.data.text) || '';

          const detectedNik = extractNikFromText(text);

          // Nama: ekstrak dari label "Nama" lalu cocokkan (fuzzy) dengan nama terdaftar
          const detectedNama = extractNamaFromText(text);
          const textUpper = text.toUpperCase().replace(/[^A-Z ]/g, ' ');
          const words = (registeredNama || '').toUpperCase().split(/\s+/).filter(function(w) {
            return w.length >= 3;
          });
          const nameMatch = matchRegisteredName(words, textUpper);

          $('#ocrNik').text(detectedNik || 'Tidak terbaca');

          const nikCocok = detectedNik !== '' && detectedNik === registeredNik;
          $('#ocrNikBadge').html(nikCocok ?
            '<span class="badge bg-success-subtle text-success border border-success">Cocok</span>' :
            '<span class="badge bg-danger-subtle text-danger border border-danger">Tidak cocok</span>');

          $('#ocrNamaText').text(detectedNama || 'Tidak terbaca');

          const namaCocok = words.length > 0 && nameMatch.missed.length === 0;
          const namaText = words.length ? nameMatch.matched.length + '/' + words.length + ' kata cocok' : '-';
          $('#ocrNamaBadge').html(
            '<span class="badge ' + (namaCocok ? 'bg-success-subtle text-success border border-success' : 'bg-warning-subtle text-warning border border-warning') + '">' + namaText + '</span>'
          );

          $('#ocrLoading').addClass('d-none');
          $('#ocrResult').removeClass('d-none');
        })
        .catch(function() {
          $('#ocrLoading').html('<span class="text-danger fs-2">Gagal membaca gambar KTP.</span>');
        });
    }

    // preview lokal + OCR saat pilih file KTP
    $('#fileKtp').on('change', function() {
      const file = this.files[0];
      if (!file) return;

      const url = URL.createObjectURL(file);
      $('#prevKtp').attr('src', url).removeClass('d-none');
      $('#prevKtpEmpty').addClass('d-none');

      runOcrKtp(file);
    });

    // 🔹 Verifikasi KTP (tahap 1)
    $('#btnVerifikasiKtp').on('click', function() {
      Swal.fire({
        title: 'Verifikasi KTP?',
        text: 'Pastikan NIK & nama pada KTP cocok dengan data registrasi.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Verifikasi KTP',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          setVerifikasi('ktp_terverifikasi');
        }
      });
    });

    // 🔹 Verifikasi / Tolak
    function setVerifikasi(status) {
      if (!currentEkycId) return;

      fetch(apiUrl, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: `action=verify&id_ekyc=${currentEkycId}&status_verifikasi=${status}`
        })
        .then(res => res.json())
        .then(res => {
          if (res.status === 'success') {
            Swal.fire('Berhasil!', res.message, 'success');
            $('#ekycDetailModal').modal('hide');
            table.ajax.reload(null, false);
          } else {
            Swal.fire('Gagal!', res.message, 'error');
          }
        })
        .catch(() => {
          Swal.fire('Gagal!', 'Tidak dapat terhubung ke server.', 'error');
        });
    }

    $('#btnVerifikasi').on('click', function() {
      Swal.fire({
        title: 'Verifikasi eKYC?',
        text: 'Pastikan foto KTP dan liveness wajah sudah sesuai dengan identitas pemohon.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Verifikasi',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          setVerifikasi('terverifikasi');
        }
      });
    });

    $('#btnTolak').on('click', function() {
      Swal.fire({
        title: 'Tolak Verifikasi?',
        text: 'Status eKYC akan diubah menjadi Ditolak.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Tolak',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#d33'
      }).then((result) => {
        if (result.isConfirmed) {
          setVerifikasi('ditolak');
        }
      });
    });
  });
</script>

<!-- MODAL DETAIL & VERIFIKASI EKYC -->
<div class="modal fade" id="ekycDetailModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-id-card me-1"></i> Detail eKYC & Verifikasi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">

        <!-- IDENTITAS -->
        <div class="card border mb-3">
          <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
              <div>
                <h6 class="fw-bold mb-1" id="dNama">-</h6>
                <div class="text-muted fs-3">
                  NIK: <span class="fw-semibold text-dark" id="dNik">-</span>
                </div>
                <div class="text-muted fs-3">
                  Email: <span id="dEmail">-</span> | HP: <span id="dHp">-</span>
                </div>
              </div>
              <span id="dStatusVerifikasi" class="badge bg-secondary">-</span>
            </div>
          </div>
        </div>

        <div class="row g-3">

          <!-- UPLOAD KTP -->
          <div class="col-md-6">
            <div class="card border h-100">
              <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <h6 class="fw-bold mb-0"><i class="fas fa-address-card me-1 text-primary"></i> Foto KTP</h6>
                  <?php if ($canManageEkyc): ?>
                    <button class="btn btn-sm btn-info d-none" type="button" id="btnVerifikasiKtp">
                      <i class="fas fa-check me-1"></i> Verifikasi KTP
                    </button>
                  <?php endif; ?>
                </div>
                <div class="border rounded bg-light d-flex align-items-center justify-content-center mb-2"
                  style="min-height:180px; overflow:hidden;">
                  <img id="prevKtp" src="" alt="Belum ada KTP" class="img-fluid d-none" style="max-height:220px;">
                  <span id="prevKtpEmpty" class="text-muted fs-3">Belum diupload</span>
                </div>
                <?php if ($canManageEkyc): ?>
                  <div class="input-group mb-2">
                    <input type="file" class="form-control" id="fileKtp" accept="image/jpeg,image/png,image/webp">
                    <button class="btn btn-primary text-nowrap" type="button" id="btnUploadKtp">
                      <i class="fas fa-upload me-1"></i> Simpan KTP
                    </button>
                  </div>
                <?php endif; ?>

                <!-- HASIL BACA KTP (OCR) -->
                <div id="ocrPanel" class="border rounded p-2 d-none" style="background:#f8f9fc;">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="fs-2 text-muted mb-0" id="ocrLoading">
                      <span class="spinner-border spinner-border-sm me-1"></span>Membaca KTP...
                    </span>
                    <button class="btn btn-sm btn-outline-secondary py-0 px-1" type="button" id="btnBacaUlang" title="Baca ulang KTP">
                      <i class="fas fa-sync-alt"></i>
                    </button>
                  </div>
                  <div id="ocrResult" class="d-none">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                      <span class="fs-2 fw-semibold">NIK terbaca:</span>
                      <span class="fs-2"><code id="ocrNik">-</code> <span id="ocrNikBadge"></span></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                      <span class="fs-2 fw-semibold">Nama terbaca:</span>
                      <span class="fs-2" id="ocrNamaText">-</span>
                    </div>
                    <div class="d-flex justify-content-end align-items-center">
                      <span id="ocrNamaBadge"></span>
                    </div>
                  </div>
                </div>

                <small class="text-muted">JPG/PNG/WEBP, maks 5 MB. NIK & nama pada KTP dibaca otomatis.</small>
              </div>
            </div>
          </div>

          <!-- UPLOAD LIVENESS -->
          <div class="col-md-6">
            <div class="card border h-100 position-relative">
              <div class="card-body p-3">
                <h6 class="fw-bold mb-2"><i class="fas fa-face-smile me-1 text-success"></i> Liveness Wajah</h6>
                <div class="border rounded bg-light d-flex align-items-center justify-content-center mb-2"
                  style="min-height:180px; overflow:hidden;">
                  <img id="prevFace" src="" alt="Belum ada liveness" class="img-fluid d-none" style="max-height:220px;">
                  <span id="prevFaceEmpty" class="text-muted fs-3">Belum diupload</span>
                </div>
                <?php if ($canManageEkyc): ?>
                  <div class="input-group">
                    <input type="file" class="form-control" id="fileFace" accept="image/jpeg,image/png,image/webp">
                    <button class="btn btn-success text-nowrap" type="button" id="btnUploadFace">
                      <i class="fas fa-upload me-1"></i> Simpan Liveness
                    </button>
                  </div>
                <?php endif; ?>
                <small class="text-muted">Foto wajah selfie pemohon, maks 5 MB.</small>
              </div>
              <!-- 🔒 Terkunci sampai KTP diverifikasi -->
              <div id="livenessLock" class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center text-center p-3"
                style="background:rgba(255,255,255,.94); border-radius:8px; z-index:5;">
                <div>
                  <iconify-icon icon="solar:lock-bold-duotone" class="fs-4 text-muted"></iconify-icon>
                  <div class="fs-3 text-muted mt-1">Liveness dibuka setelah<br><strong>KTP diverifikasi admin</strong></div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
      <div class="modal-footer d-flex justify-content-between flex-wrap gap-2">
        <?php if ($canManageEkyc): ?>
          <button type="button" class="btn btn-outline-danger" id="btnTolak">
            <i class="fas fa-times me-1"></i> Tolak
          </button>
        <?php else: ?>
          <span></span>
        <?php endif; ?>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
          <?php if ($canManageEkyc): ?>
            <button type="button" class="btn btn-success" id="btnVerifikasi">
              <i class="fas fa-check me-1"></i> Verifikasi
            </button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

</html>
