<div class="row">
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light fw-semibold">
                    Data Peserta BPJS
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-5 text-muted">No. BPJS</div>
                        <div class="col-7 fw-semibold" id="noK">-</div>

                        <div class="col-5 text-muted">Nama</div>
                        <div class="col-7 fw-semibold" id="nama">-</div>

                        <div class="col-5 text-muted">Tanggal Lahir</div>
                        <div class="col-7" id="tglLahir">-</div>

                        <div class="col-5 text-muted">Jenis Kelamin</div>
                        <div class="col-7" id="kelamin">-</div>

                        <div class="col-5 text-muted">PPK Umum</div>
                        <div class="col-7" id="ppkumum">-</div>

                        <div class="col-5 text-muted">No. HP</div>
                        <div class="col-7" id="noTelp">-</div>

                        <div class="col-5 text-muted">No. Rekam Medis</div>
                        <div class="col-7" id="noRm">-</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <form id="isiform">
                <div class="card shadow-sm">
                    <div class="card-header bg-light fw-semibold">
                        Form Kunjungan Pasien
                    </div>

                    <div class="card-body">

                        <!-- INFORMASI KUNJUNGAN -->
                        <h5 class="text-primary fw-semibold mb-3">Informasi Kunjungan</h5>
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">Jenis Kunjungan <span class="text-danger">*</span></label>
                                <select class="form-select" name="kunjSakit" id="kunjSakit">
                                    <option value="true">Kunjungan Sakit</option>
                                    <option value="false">Kunjungan Sehat</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Perawatan <span class="text-danger">*</span></label>
                                <select class="form-select" id="kunjungan" name="kdTkp"></select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Poli Tujuan <span class="text-danger">*</span></label>
                                <select class="form-select" id="kodepoli" name="kdPoli"></select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Tanggal Kunjungan <span class="text-danger">*</span></label>
                                <input type="date" class="form-control"
                                    name="tglDaftar" id="tanggal" value="<?= $tanggal ?>">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Keluhan <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="keluhan" name="keluhan" rows="3"></textarea>
                            </div>
                        </div>

                        <hr>

                        <!-- PEMERIKSAAN FISIK -->
                        <h5 class="text-primary fw-semibold mb-3">Pemeriksaan Fisik</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Tinggi Badan</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="tinggiBadan" id="tinggiBadan" value="0">
                                    <span class="input-group-text">cm</span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Berat Badan</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="beratBadan" id="beratBadan" value="0">
                                    <span class="input-group-text">kg</span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Lingkar Perut</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="lingkarPerut" id="lingkarPerut" value="0">
                                    <span class="input-group-text">cm</span>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- TANDA VITAL -->
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
                        </div>

                        <div class="text-end mt-4">
                            <button class="btn btn-success px-4" type="button" id="create">
                                Simpan Kunjungan
                            </button>
                        </div>

                    </div>
                </div>

                <!-- HIDDEN -->
                <input type="hidden" name="noKartu" id="noKartu">
                <input type="hidden" name="kdProviderPeserta" id="kdProviderPeserta">
            </form>
        </div>
    </div>
</div>