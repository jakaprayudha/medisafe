window.APP = window.APP || {}
let tableRekap, tableProlanis, tableHipertensi;
$(function () {
    function loadTabelKunjungan(noKartu) {
        if (tableRekap) {
            tableRekap.ajax.reload();
            return;
        }

        tableRekap = $('#tableRekapitulasi').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: 'controller/admisi/services/getRekapitulasi.php',
                type: 'GET',
                data: function (d) {
                    d.noKartu = noKartu;
                },
                dataSrc: ''
            },
            columns: [
                {
                    data: null,
                    render: (d, t, r, meta) => meta.row + 1
                },
                { data: 'nama' },

                // status penyakit (AMAN)
                {
                    data: 'status_penyakit',
                    render: d => d?.anemia ? 'Ya' : 'Tidak'
                },
                {
                    data: 'status_penyakit',
                    render: d => d?.hepatitis_b ? 'Ya' : 'Tidak'
                },
                {
                    data: 'status_penyakit',
                    render: d => d?.hepatitis_c ? 'Ya' : 'Tidak'
                },
                {
                    data: 'status_penyakit',
                    render: d => d?.hipertensi_stroke_ischemic_heart_disease ? 'Ya' : 'Tidak'
                },
                {
                    data: 'status_penyakit',
                    render: d => d?.kanker_paru ? 'Ya' : 'Tidak'
                },
                {
                    data: 'status_penyakit',
                    render: d => d?.kanker_payudara ? 'Ya' : 'Tidak'
                },
                {
                    data: 'status_penyakit',
                    render: d => d?.kanker_serviks ? 'Ya' : 'Tidak'
                },
                {
                    data: 'status_penyakit',
                    render: d => d?.kolorektal ? 'Ya' : 'Tidak'
                },
                {
                    data: 'status_penyakit',
                    render: d => d?.paru_obstruktif_kronis ? 'Ya' : 'Tidak'
                },
                {
                    data: 'status_penyakit',
                    render: d => d?.penyakit_diabetes_mellitus ? 'Ya' : 'Tidak'
                },
                {
                    data: 'status_penyakit',
                    render: d => d?.thalasemia ? 'Ya' : 'Tidak'
                },
                {
                    data: 'status_penyakit',
                    render: d => d?.tuberkulosis ? 'Ya' : 'Tidak'
                }
            ],
            order: [[0, 'asc']],
            scrollX: true,
            autoWidth: false,
            pageLength: 10,
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                zeroRecords: "Data rekapitulasi tidak ditemukan",
                infoEmpty: "Tidak ada data"
            }
        });
    }
    function loadTabelProlanisDiabetes(noKartu) {
        if (tableProlanis) {
            tableProlanis.ajax.reload();
            return;
        }

        tableProlanis = $('#tableProlanisDiabetes').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: 'controller/admisi/services/getProlanisdiabetes.php',
                type: 'GET',
                data: function (d) {
                    d.noKartu = noKartu;
                },
                dataSrc: ''
            },
            columns: [
                { data: null, render: (d, t, r, m) => m.row + 1 },
                { data: 'nama' },
                { data: 'jenis_kelamin' },
                { data: 'diagnosa_terakhir' },
                { data: 'status_prolanis' }
            ],
            scrollX: true,
            autoWidth: false,
            pageLength: 10,
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                zeroRecords: "Data Prolanis Diabetes tidak ditemukan",
                infoEmpty: "Tidak ada data"
            }
        });
    }
    function loadTabelHipertensi(noKartu) {
        if (tableHipertensi) {
            tableHipertensi.ajax.reload();
            return;
        }

        tableHipertensi = $('#tableHipertensi').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: 'controller/admisi/services/getHipertensi.php',
                type: 'GET',
                data: function (d) {
                    d.noKartu = noKartu;
                },
                dataSrc: ''
            },
            columns: [
                { data: null, render: (d, t, r, m) => m.row + 1 },
                { data: 'nama' },
                { data: 'jenis_kelamin' },
                { data: 'diagnosa_terakhir' },
                { data: 'status_prolanis' }
            ],
            scrollX: true,
            autoWidth: false,
            pageLength: 10,
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                zeroRecords: "Data Hipertensi tidak ditemukan",
                infoEmpty: "Tidak ada data"
            }
        });
    }

    $('#cari').on('click', function () {
        const nomor = $('#nomor').val().trim();
        if (!nomor) return alert('Nomor peserta wajib diisi');

        loadTabelKunjungan(nomor);
        loadTabelProlanisDiabetes(nomor);
        loadTabelHipertensi(nomor);
    });
})