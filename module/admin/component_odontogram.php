 <style>
    .odontogram {
       display: flex;
       flex-direction: column;
       gap: 20px;
    }

    .row-gigi {
       display: flex;
       justify-content: center;
       gap: 8px;
       flex-wrap: wrap;
    }

    .gigi {
       width: 50px;
       height: 60px;
       border: 1px solid #ccc;
       border-radius: 6px;
       text-align: center;
       cursor: pointer;
       transition: 0.2s;
       background: #fff;
       position: relative;
    }

    .gigi:hover {
       background: #e6f7ff;
       border-color: #1890ff;
    }

    .nomor {
       font-size: 12px;
       font-weight: bold;
       margin-top: 4px;
    }

    .gigi-box {
       width: 30px;
       height: 30px;
       border: 1px solid #999;
       margin: 5px auto;
    }
 </style>

 <div class="card w-100">
    <div class="card-body p-4">
       <div class="d-flex justify-content-between align-items-center mb-4">
          <h5 class="card-title fw-semibold">Data Odontogram</h5>
          <!-- Grup tombol di sisi kanan -->
          <div class="d-flex ms-auto gap-2">
             <a href="module/admin/print/formulir_odontogram?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>" target="_blank">
                <button class="btn btn-light"><i class="fas fa-print"></i> Print</button>
             </a>
          </div>
       </div>



       <div class="row">

          <!-- 🦷 ODONTOGRAM FULL -->
          <div class="col-12">
             <div class="card mb-3">
                <div class="card-body">

                   <div class="odontogram">

                      <!-- Atas -->
                      <div class="row-gigi">
                         <?php
                           foreach ([18, 17, 16, 15, 14, 13, 12, 11, 21, 22, 23, 24, 25, 26, 27, 28] as $g) {
                              echo "<div class='gigi' onclick='pilihGigi($g, this)'>
                      <div class='nomor'>$g</div>
                      <div class='gigi-box'></div>
                    </div>";
                           }
                           ?>
                      </div>

                      <!-- Tengah -->
                      <div class="row-gigi">
                         <?php
                           foreach ([55, 54, 53, 52, 51, 61, 62, 63, 64, 65] as $g) {
                              echo "<div class='gigi' onclick='pilihGigi($g, this)'>
                      <div class='nomor'>$g</div>
                      <div class='gigi-box'></div>
                    </div>";
                           }
                           ?>
                      </div>

                      <div class="row-gigi">
                         <?php
                           foreach ([85, 84, 83, 82, 81, 71, 72, 73, 74, 75] as $g) {
                              echo "<div class='gigi' onclick='pilihGigi($g, this)'>
                      <div class='nomor'>$g</div>
                      <div class='gigi-box'></div>
                    </div>";
                           }
                           ?>
                      </div>

                      <!-- Bawah -->
                      <div class="row-gigi">
                         <?php
                           foreach ([48, 47, 46, 45, 44, 43, 42, 41, 31, 32, 33, 34, 35, 36, 37, 38] as $g) {
                              echo "<div class='gigi' onclick='pilihGigi($g, this)'>
                      <div class='nomor'>$g</div>
                      <div class='gigi-box'></div>
                    </div>";
                           }
                           ?>
                      </div>

                   </div>

                </div>
             </div>
          </div>


          <!-- 🧾 FORM (KIRI) -->
          <div class="col-md-4">
             <div class="card">
                <div class="card-body">

                   <h6>Input Data</h6>

                   <div class="mb-3">
                      <label>Elemen</label>
                      <select class="form-control" id="elemen">
                         <option value="">---Pilih---</option>
                         <option value="mahkota">Mahkota</option>
                         <option value="akar">Akar</option>
                      </select>
                   </div>

                   <div class="mb-3">
                      <label>Elemen Gigi</label>
                      <select class="form-control" id="elemen_gigi">
                         <option value="">---Pilih---</option>
                         <option value="mesial">Mesial</option>
                         <option value="distal">Distal</option>
                         <option value="oklusal">Oklusal</option>
                         <option value="lingual">Lingual</option>
                         <option value="bukal">Bukal</option>
                      </select>
                   </div>

                   <div class="mb-3">
                      <label>Diagnosa</label>
                      <input type="text" class="form-control" id="diagnosa">
                   </div>

                   <div class="mb-3">
                      <label>Prosedur</label>
                      <select class="form-control" id="prosedur">
                         <option value="">---</option>
                         <option value="tambal">Tambal</option>
                         <option value="cabut">Cabut</option>
                      </select>
                   </div>

                   <div class="mb-3">
                      <label>Keterangan</label>
                      <input type="text" class="form-control" id="keterangan">
                   </div>

                   <div class="d-flex gap-2">

                      <button
                         type="button"
                         class="btn btn-success flex-fill"
                         id="btnSimpanOdontogram"
                         onclick="simpanOdontogram()">

                         💾 Simpan

                      </button>

                      <button
                         type="button"
                         class="btn btn-secondary d-none"
                         id="btnBatalEdit"
                         onclick="batalEdit()">

                         Batal

                      </button>

                   </div>

                </div>
             </div>
          </div>


          <!-- 📊 TABLE (KANAN) -->
          <div class="col-md-8">
             <div class="card">
                <div class="card-body">

                   <h6>Data Odontogram</h6>

                   <table class="table table-bordered table-sm" id="tableOdontogram">
                      <thead class="table-light">
                         <tr>
                            <th>No</th>
                            <th>Gigi</th>
                            <th>Elemen</th>
                            <th>Elemen Gigi</th>
                            <th>Diagnosa</th>
                            <th>Prosedur</th>
                            <th>Keterangan</th>
                            <th width="130">Aksi</th>
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


 <script>
    function loadTableOdontogram() {

       fetch('controller/visit/getOdontogram?visit_ID=<?= $_GET['no'] ?>')

          .then(res => {

             if (!res.ok) {
                throw new Error("HTTP Error: " + res.status);
             }

             return res.json();

          })

          .then(res => {

             console.log("Data odontogram:", res);

             const tbody =
                document.querySelector('#tableOdontogram tbody');

             tbody.innerHTML = '';

             if (!res.data || res.data.length === 0) {

                tbody.innerHTML = `
                    <tr>
                        <td colspan="8"
                            class="text-center text-muted py-3">
                            Belum ada data odontogram
                        </td>
                    </tr>
                `;

                return;
             }

             let no = 1;

             res.data.forEach(row => {

                tbody.innerHTML += `

                    <tr>

                        <td>
                            ${no++}
                        </td>

                        <td>
                            <strong>
                                ${row.no_gigi ?? '-'}
                            </strong>
                        </td>

                        <td>
                            ${row.elemen ?? '-'}
                        </td>

                        <td>
                            ${row.elemen_gigi ?? '-'}
                        </td>

                        <td>
                            ${row.diagnosa ?? '-'}
                        </td>

                        <td>
                            ${row.prosedur ?? '-'}
                        </td>

                        <td>
                            ${row.keterangan ?? '-'}
                        </td>

                        <td>

                            <div class="d-flex gap-1">

                                <button
                                    type="button"
                                    class="btn btn-sm btn-warning"
                                    title="Ubah"
                                    onclick='editOdontogram(${JSON.stringify(row)})'>

                                    <i class="fas fa-edit"></i>

                                </button>


                                <button
                                    type="button"
                                    class="btn btn-sm btn-danger"
                                    title="Hapus"
                                    onclick="hapusOdontogram('${row.no_gigi}')">

                                    <i class="fas fa-trash"></i>

                                </button>

                            </div>

                        </td>

                    </tr>

                `;

             });


             // Tandai gigi yang sudah memiliki data

             document.querySelectorAll('.gigi').forEach(gigi => {

                gigi.style.background = '#fff';

             });


             res.data.forEach(row => {

                tandaiGigi(
                   parseInt(row.no_gigi)
                );

             });

          })

          .catch(err => {

             console.error(
                "Error load odontogram:",
                err
             );

             const tbody =
                document.querySelector(
                   '#tableOdontogram tbody'
                );

             tbody.innerHTML = `
                <tr>
                    <td colspan="8"
                        class="text-center text-danger py-3">

                        Gagal mengambil data odontogram

                    </td>
                </tr>
            `;

          });
    }


    // ======================================
    // LOAD DATA SAAT HALAMAN DIBUKA
    // ======================================

    document.addEventListener("DOMContentLoaded", function() {
       loadTableOdontogram();
    });
    let selectedGigi = null;
    let isLoading = false;
    let editMode = false;
    let editGigi = null;

    // pilih gigi
    function pilihGigi(no, el) {
       selectedGigi = no;

       // reset semua warna
       document.querySelectorAll('.gigi').forEach(g => {
          g.style.background = '#fff';
       });

       // highlight yg dipilih
       el.style.background = "#ffccc7";

       console.log("Selected gigi:", no);
    }

    function editOdontogram(row) {

       editMode = true;

       editGigi = row.no_gigi;


       // ==============================
       // PILIH GIGI
       // ==============================

       selectedGigi = parseInt(row.no_gigi);


       document.querySelectorAll('.gigi').forEach(g => {

          g.style.background = '#fff';

       });


       document.querySelectorAll('.gigi').forEach(g => {

          const nomor =
             g.querySelector('.nomor').innerText;

          if (
             parseInt(nomor) ===
             parseInt(row.no_gigi)
          ) {

             g.style.background = '#ffccc7';

             g.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
             });

          }

       });


       // ==============================
       // ISI FORM
       // ==============================

       document.getElementById('elemen').value =
          row.elemen ?? '';

       document.getElementById('elemen_gigi').value =
          row.elemen_gigi ?? '';

       document.getElementById('diagnosa').value =
          row.diagnosa ?? '';

       document.getElementById('prosedur').value =
          row.prosedur ?? '';

       document.getElementById('keterangan').value =
          row.keterangan ?? '';


       // ==============================
       // UBAH TOMBOL
       // ==============================

       const btnSimpan =
          document.getElementById(
             'btnSimpanOdontogram'
          );

       btnSimpan.innerHTML =
          '✏️ Update';

       btnSimpan.classList.remove(
          'btn-success'
       );

       btnSimpan.classList.add(
          'btn-warning'
       );


       document.getElementById(
          'btnBatalEdit'
       ).classList.remove('d-none');


       // ==============================
       // FOKUS DI FORM
       // ==============================

       document.getElementById(
          'elemen'
       ).focus();

    }


    function simpanOdontogram() {

       if (isLoading) return;


       if (!selectedGigi) {

          alert("⚠️ Pilih gigi dulu!");

          return;

       }


       const elemen =
          document.getElementById('elemen').value;

       const elemen_gigi =
          document.getElementById('elemen_gigi').value;

       const diagnosa =
          document.getElementById('diagnosa')
          .value
          .trim();

       const prosedur =
          document.getElementById('prosedur').value;

       const keterangan =
          document.getElementById('keterangan').value;


       if (!diagnosa) {

          alert("⚠️ Diagnosa wajib diisi!");

          return;

       }


       isLoading = true;


       fetch(
             'controller/visit/odontogramController.php', {
                method: 'POST',

                headers: {
                   'Content-Type': 'application/json'
                },

                body: JSON.stringify({

                   action: editMode ?
                      'update' : 'insert',

                   no_gigi: selectedGigi,

                   old_no_gigi: editGigi,

                   elemen,

                   elemen_gigi,

                   diagnosa,

                   prosedur,

                   keterangan,

                   visit_ID: "<?= $_GET['no'] ?>",

                   id_customer: "<?= $_GET['rm'] ?>"

                })
             }
          )

          .then(res => res.json())

          .then(res => {

             isLoading = false;


             if (res.status === "success") {

                alert(
                   editMode ?
                   "✅ Data berhasil diubah" :
                   "✅ Data berhasil disimpan"
                );


                loadTableOdontogram();

                batalEdit();

             } else {

                alert(
                   "❌ " +
                   (res.message ??
                      "Terjadi kesalahan")
                );

             }

          })

          .catch(err => {

             isLoading = false;

             console.error(err);

             alert(
                "❌ Terjadi error saat menyimpan data"
             );

          });

    }

    function batalEdit() {

       editMode = false;

       editGigi = null;

       selectedGigi = null;


       resetForm();


       const btnSimpan =
          document.getElementById(
             'btnSimpanOdontogram'
          );


       btnSimpan.innerHTML =
          '💾 Simpan';


       btnSimpan.classList.remove(
          'btn-warning'
       );

       btnSimpan.classList.add(
          'btn-success'
       );


       document.getElementById(
          'btnBatalEdit'
       ).classList.add('d-none');


       // Reset warna gigi

       document.querySelectorAll('.gigi')
          .forEach(g => {

             g.style.background = '#fff';

          });


       // Tampilkan kembali gigi yang memiliki data

       fetch(
             'controller/visit/getOdontogram?visit_ID=<?= $_GET['no'] ?>'
          )
          .then(res => res.json())
          .then(res => {

             if (res.data) {

                res.data.forEach(row => {

                   tandaiGigi(
                      parseInt(row.no_gigi)
                   );

                });

             }

          });

    }

    function hapusOdontogram(noGigi) {

       if (isLoading) return;


       const prosesHapus = () => {

          isLoading = true;


          fetch(
                'controller/visit/odontogramController.php', {
                   method: 'POST',

                   headers: {
                      'Content-Type': 'application/json'
                   },

                   body: JSON.stringify({

                      action: 'delete',

                      no_gigi: noGigi,

                      visit_ID: "<?= $_GET['no'] ?>"

                   })

                }
             )

             .then(res => res.json())

             .then(res => {

                isLoading = false;


                if (res.status === "success") {

                   alert(
                      "✅ Data berhasil dihapus"
                   );


                   loadTableOdontogram();


                   if (
                      editMode &&
                      parseInt(editGigi) ===
                      parseInt(noGigi)
                   ) {

                      batalEdit();

                   }

                } else {

                   alert(
                      "❌ " +
                      (res.message ??
                         "Gagal menghapus data")
                   );

                }

             })

             .catch(err => {

                isLoading = false;

                console.error(err);

                alert(
                   "❌ Terjadi error saat menghapus data"
                );

             });

       };


       if (typeof Swal !== "undefined") {

          Swal.fire({

             title: "Hapus Data?",

             text: "Data odontogram gigi " +
                noGigi +
                " akan dihapus.",

             icon: "warning",

             showCancelButton: true,

             confirmButtonColor: "#dc3545",

             cancelButtonColor: "#6c757d",

             confirmButtonText: "Ya, Hapus",

             cancelButtonText: "Batal"

          }).then(result => {

             if (result.isConfirmed) {

                prosesHapus();

             }

          });

       } else {

          if (
             confirm(
                "Hapus data odontogram gigi " +
                noGigi +
                "?"
             )
          ) {

             prosesHapus();

          }

       }

    }


    // reset form
    function resetForm() {
       document.getElementById('elemen').value = '';
       document.getElementById('elemen_gigi').value = '';
       document.getElementById('diagnosa').value = '';
       document.getElementById('prosedur').value = '';
       document.getElementById('keterangan').value = '';
    }


    // tandai gigi setelah simpan
    function tandaiGigi(no) {
       document.querySelectorAll('.gigi').forEach(g => {
          const nomor = g.querySelector('.nomor').innerText;

          if (parseInt(nomor) === no) {
             g.style.background = "#b7eb8f"; // hijau = tersimpan
          }
       });
    }
 </script>