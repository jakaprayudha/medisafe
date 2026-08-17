<div class="row">
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light fw-semibold">
                    Data Peserta BPJS
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-5 text-muted viewBpjs d-none">No. BPJS</div>
                        <div class="col-7 fw-semibold viewBpjs d-none" id="noK">-</div>

                        <div class="col-5 text-muted">No. NIK</div>
                        <div class="col-7 fw-semibold" id="nonik">-</div>

                        <div class="col-5 text-muted">Nama</div>
                        <div class="col-7 fw-semibold" id="nama">-</div>

                        <div class="col-5 text-muted">Tanggal Lahir</div>
                        <div class="col-7" id="tglLahir">-</div>

                        <div class="col-5 text-muted">Jenis Kelamin</div>
                        <div class="col-7" id="kelamin">-</div>

                        <div class="col-5 text-muted viewBpjs d-none">PPK Umum</div>
                        <div class="col-7 viewBpjs d-none" id="ppkumum">-</div>

                        <div class="col-5 text-muted">No. HP</div>
                        <div class="col-7" id="noTelp">-</div>

                        <div class="col-5 text-muted">No. Rekam Medis</div>
                        <div class="col-7" id="no_rekammedis">-</div>

                        <div class="col-5 text-muted">Prolanis</div>
                        <div class="col-7" id="status_prolanis" style="color:red"></div>
                        
                        <div class="col-5 text-muted">PRB</div>
                        <div class="col-7" id="status_PRB" style="color:red"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <form id="isiform">
                <input type="hidden" name="noNik" id="noNIK">
                <input type="hidden" name="nama" id="namapatient">
                <input type="hidden" name="jnsKlamin" id="Kelamin">
                <input type="hidden" name="tglLahir" id="tgllahir">
                <input type="hidden" name="typePatient" id="typePasien">
                <input type="hidden" name="noHp" id="nohp">
                <input type="hidden" name="norm" id="norm">
                <div class="card shadow-sm">
                    <div class="card-header bg-light fw-semibold">
                        Form Kunjungan Pasien
                    </div>
                    <div class="card-body">
                        <h5 class="text-primary fw-semibold mb-3">Informasi Kunjungan</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Jenis Kunjungan <span class="text-danger">*</span></label>
                                <select class="form-select" name="kunjSakit" id="kunjSakit"></select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Perawatan <span class="text-danger">*</span></label>
                                <select class="form-select" id="kunjungan" name="kdTkp"></select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Poli Tujuan <span class="text-danger">*</span></label>
                                <select class="form-select" id="kodepoli" name="kdPoli"></select>
                                <input type="hidden" name="nmPoli" id="nmPoli">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Kunjungan <span class="text-danger">*</span></label>
                                <input type="date" class="form-control"
                                    name="tglDaftar" id="tanggalKunjung" value="<?= $tanggal ?>">
                            </div>
                            <div id="statusKsehat">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Dokter <span class="text-danger">*</span></label>
                                        <select class="form-select" id="kodedokter" name="kdDokter"></select>
                                        <input type="hidden" name="nmDokter" id="namadokter">
                                        <input type="hidden" name="jampraktek" id="jampraktek">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Provider <span class="text-danger">*</span></label>
                                        <select class="form-select" id="kodeprov" name="kdProv"></select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Keluhan <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="keluhan" name="keluhan" rows="3" placeholder="Tulisa Keluhan..."></textarea>
                            </div>
                        </div>
                        <hr>
                        <h5 class="text-primary fw-semibold mb-3">Pemeriksaan Fisik</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Suhu</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="suhu" id="suhu" value="0" max="50">
                                    <span class="input-group-text">c</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tinggi Badan</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="tinggiBadan" id="tinggiBadan" value="0" max="200">
                                    <span class="input-group-text">cm</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Berat Badan</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="beratBadan" max="300" id="beratBadan" value="0">
                                    <span class="input-group-text">kg</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">BMI</label>
                                <input type="text" class="form-control bg-light" id="bmi" name="bmi" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Keterangan BMI</label>
                                <input type="text" class="form-control bg-light" id="bmiKet" name="bmiKet" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Lingkar Perut</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="lingkarPerut" max="300" id="lingkarPerut" value="0">
                                    <span class="input-group-text">cm</span>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <h5 class="text-primary fw-semibold mb-3">Tanda Vital</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Sistole</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="sistole" id="sistole" value="0">
                                    <span class="input-group-text">mmHg</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Diastole</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="diastole" id="diastole" value="0">
                                    <span class="input-group-text">mmHg</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Respiratory Rate</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="respRate" id="respRate" value="0">
                                    <span class="input-group-text">/menit</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Heart Rate</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="heartRate" id="heartRate" value="0">
                                    <span class="input-group-text">BPM</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Saturasi Oksigen</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="saturasiOksigen" id="saturasiOksigen" value="0">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>
                        <div class="text-end mt-4">
                            <button class="btn btn-success px-4" type="button" id="create">
                                Simpan Kunjungan
                            </button>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="noKartu" id="noKartu">
                <input type="hidden" name="kdProviderPeserta" id="kdProviderPeserta">
            </form>
        </div>
    </div>
</div>