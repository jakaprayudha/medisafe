<?php
$title = 'Detail Obat';
require '../../controller/view.php';
require_once '../../controller/master/pharmacyAkses.php';
require_once '../../controller/master/marginHarga.php';

$idObat = (int) ($_GET['no'] ?? 0);
$idcSesi = (int) ($_SESSION['id_customer'] ?? 0);
$obat = ($idObat > 0 && $idcSesi > 0) ? obatFaskes($koneksi, $idObat, $idcSesi) : null;

// margin aktif (> 0) => harga jual dihitung otomatis dari harga beli, juga ditegakkan di server
$mAktif = $idcSesi > 0 ? marginFaskes($koneksi, $idcSesi) : ['obat' => null, 'bmhp' => null];
$margin = ['margin_obat' => $mAktif['obat'], 'margin_bmhp' => $mAktif['bmhp']];
$h = fn($v, $k = '-') => trim((string) ($v ?? '')) === '' ? $k : htmlspecialchars(trim((string) $v), ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .min-w-0 {
      min-width: 0;
    }

    .od-hero {
      border: 1px solid #e5eaef;
      border-radius: 16px;
      background: #fff;
      overflow: hidden;
    }

    .od-hero-top {
      padding: 20px 24px;
      background: linear-gradient(120deg, #f0f4ff 0%, #fff 70%);
      border-bottom: 1px solid #eef2f6;
    }

    .od-ava {
      width: 60px;
      height: 60px;
      border-radius: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      flex-shrink: 0;
      overflow: hidden;
    }

    .od-ava img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .od-name {
      font-size: 1.35rem;
      font-weight: 700;
      color: #2a3547;
      line-height: 1.25;
    }

    .od-badge {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      font-size: .72rem;
      font-weight: 600;
      padding: .28em .65em;
      border-radius: 6px;
      white-space: nowrap;
    }

    .od-kpis {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    }

    .od-kpi {
      padding: 14px 24px;
      border-right: 1px solid #eef2f6;
    }

    .od-kpi:last-child {
      border-right: 0;
    }

    .od-kpi span {
      display: block;
      font-size: .7rem;
      color: #7c8fac;
      text-transform: uppercase;
      letter-spacing: .3px;
    }

    .od-kpi b {
      font-size: 1.05rem;
      color: #2a3547;
    }

    .od-tabs {
      border-bottom: 1px solid #e5eaef;
      gap: 4px;
      flex-wrap: nowrap;
      overflow-x: auto;
    }

    .od-tabs .nav-link {
      border: 0;
      border-bottom: 2px solid transparent;
      border-radius: 0;
      color: #5a6a85;
      font-weight: 600;
      font-size: .875rem;
      padding: .75rem 1rem;
      white-space: nowrap;
      background: none;
    }

    .od-tabs .nav-link.active {
      color: #5d87ff;
      border-bottom-color: #5d87ff;
    }

    .od-sec {
      font-size: .74rem;
      font-weight: 700;
      color: #5a6a85;
      text-transform: uppercase;
      letter-spacing: .4px;
      display: flex;
      align-items: center;
      gap: 8px;
      margin: 6px 0 14px;
    }

    .od-sec::after {
      content: '';
      flex: 1;
      height: 1px;
      background: #e5eaef;
    }

    .od-save {
      border-top: 1px solid #eef2f6;
      padding-top: 14px;
      margin-top: 18px;
    }

    .od-calc {
      border-radius: 12px;
      background: #f6f9fc;
      padding: 14px 16px;
    }

    .od-calc .row-c {
      display: flex;
      justify-content: space-between;
      font-size: .85rem;
      padding: 3px 0;
    }

    .od-calc .tot {
      border-top: 1px dashed #dfe5ef;
      margin-top: 6px;
      padding-top: 8px;
      font-weight: 700;
      color: #2a3547;
    }

    .od-doc {
      border: 1.5px dashed #dfe5ef;
      border-radius: 14px;
      padding: 14px;
      height: 100%;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .od-doc.ada {
      border-style: solid;
      border-color: #e5eaef;
    }

    .od-thumb {
      aspect-ratio: 16 / 10;
      border-radius: 10px;
      background: #f6f9fc;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      color: #adb5bd;
      font-size: 2rem;
    }

    .od-thumb img {
      width: 100%;
      height: 100%;
      object-fit: contain;
    }

    .od-thumb.klik {
      cursor: zoom-in;
    }
  </style>
</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <?php require 'sidebar.php'; ?>
    <div class="body-wrapper">
      <?php require 'navbar.php'; ?>
      <div class="body-wrapper-inner">
        <div class="container-fluid">

          <?php if (!$obat): ?>
            <div class="card">
              <div class="card-body text-center py-5">
                <i class="fas fa-prescription-bottle fa-3x text-muted mb-3"></i>
                <h5 class="fw-semibold">Obat tidak ditemukan</h5>
                <p class="text-muted mb-4">Obat tidak ada, sudah dihapus, atau bukan milik faskes Anda.</p>
                <a href="module/admin/pharmacy" class="btn btn-primary"><i class="fas fa-arrow-left me-1"></i> Kembali ke Master Obat</a>
              </div>
            </div>
          <?php else:
            $kat = strtolower((string) $obat['pharmacy_category']);
            $isBmhp = $kat === 'bmhp' || str_starts_with($kat, 'alkes') || str_starts_with($kat, 'alat');
            [$ic, $icCls] = $isBmhp ? ['fa-syringe', 'bg-info-subtle text-info'] : ['fa-pills', 'bg-success-subtle text-success'];
            $golWarna = ['Bebas' => ['#13a67a', '#e6fffa'], 'Bebas Terbatas' => ['#2e7dd7', '#e8f2ff'], 'Keras' => ['#e0473b', '#fdede8'], 'Psikotropika' => ['#7460ee', '#efecfd'], 'Narkotika' => ['#2a3547', '#e5eaef']][$obat['pharmcy_golongan']] ?? null;
            $img = trim((string) $obat['pharmacy_image']);
            $rp = fn($v) => (float) $v > 0 ? 'Rp ' . number_format((float) $v, 0, ',', '.') : '-';
            $kfa = trim((string) $obat['kfa_code']);
            $kfa = in_array(strtoupper($kfa), ['', 'NA', '-', 'NULL'], true) ? '' : $kfa;
          ?>
            <!-- RINGKASAN -->
            <div class="od-hero mb-3">
              <div class="od-hero-top">
                <div class="d-flex flex-wrap align-items-center gap-3">
                  <div class="od-ava <?= $icCls ?>" id="odAva">
                    <?php if ($img !== ''): ?><img src="uploads/pharmacy/<?= $h(rawurlencode(basename($img))) ?>" alt="" onerror="this.remove()"><?php else: ?><i class="fas <?= $ic ?>"></i><?php endif; ?>
                  </div>
                  <div class="flex-grow-1 min-w-0">
                    <div class="od-name" id="odNama"><?= $h($obat['pharmacy_name_generic']) ?></div>
                    <div class="text-muted small mb-2" id="odTrade"><?= $h($obat['pharmacy_name_trade'], '') ?></div>
                    <div class="d-flex flex-wrap gap-1">
                      <span class="od-badge bg-dark text-white" title="ID obat"><i class="fas fa-hashtag"></i><?= $h($obat['pharmacy_number']) ?></span>
                      <?php if (trim((string) $obat['pharmacy_code']) !== ''): ?><span class="od-badge bg-primary-subtle text-primary">BPJS <?= $h($obat['pharmacy_code']) ?></span><?php endif; ?>
                      <span class="od-badge bg-light text-dark"><?= $h($obat['pharmacy_category']) ?></span>
                      <?php if ($golWarna): ?><span class="od-badge" style="background:<?= $golWarna[1] ?>;color:<?= $golWarna[0] ?>"><?= $h($obat['pharmcy_golongan']) ?></span><?php endif; ?>
                      <?php if ($kfa !== ''): ?><span class="od-badge bg-success-subtle text-success" title="Kode KFA Satu Sehat"><i class="fas fa-check-circle"></i>KFA <?= $h($kfa) ?></span><?php endif; ?>
                      <?= (string) $obat['pharmacy_status'] === '0' ? '<span class="od-badge bg-danger-subtle text-danger">Nonaktif</span>' : '<span class="od-badge bg-success-subtle text-success">Aktif</span>' ?>
                      <?php if ($obat['sumber'] === 'global'): ?><span class="od-badge bg-warning-subtle text-warning" title="Master bawaan sistem"><i class="fas fa-globe"></i>Master bawaan</span><?php endif; ?>
                    </div>
                  </div>
                  <div class="d-flex flex-wrap gap-2">
                    <a href="module/admin/pharmacy" class="btn btn-sm btn-light"><i class="fas fa-arrow-left me-1"></i>Master Obat</a>
                    <a href="module/admin/pharmacy_stock?no=<?= (int) $obat['id_pharmacy'] ?>" class="btn btn-sm btn-outline-warning"><i class="fas fa-boxes-stacked me-1"></i>Stok</a>
                    <a href="module/admin/pharmacy_usage?id_pharmacy=<?= (int) $obat['id_pharmacy'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-users me-1"></i>Pemakaian</a>
                  </div>
                </div>
              </div>
              <div class="od-kpis">
                <div class="od-kpi"><span>Harga jual</span><b id="kpiJual"><?= $rp($obat['pharmacy_sale']) ?></b></div>
                <div class="od-kpi"><span>Harga beli terakhir</span><b id="kpiBeli"><?= $rp($obat['pharmacy_buy']) ?></b></div>
                <div class="od-kpi"><span>Stok min / maks</span><b id="kpiStok"><?= $h($obat['stok_min'], '-') ?> / <?= $h($obat['stok_max'], '-') ?></b></div>
                <div class="od-kpi"><span>Satuan</span><b><?= $h($obat['pharmacy_unit']) ?></b></div>
              </div>
            </div>

            <?php if ($obat['sumber'] === 'global'): ?>
              <div class="alert alert-warning d-flex gap-2 align-items-start small">
                <i class="fas fa-info-circle mt-1"></i>
                <div>Obat ini berasal dari <b>master bawaan</b> yang dipakai bersama. Saat Anda menyimpan perubahan, sistem membuat <b>salinan khusus faskes Anda</b>
                  dan halaman akan pindah ke salinan tersebut. Faskes lain tidak terpengaruh.</div>
              </div>
            <?php endif; ?>

            <!-- FORM -->
            <div class="card">
              <div class="card-body p-0">
                <ul class="nav od-tabs px-3" role="tablist">
                  <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tUmum" type="button"><i class="fas fa-info-circle me-1"></i>Data Umum</button></li>
                  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tHarga" type="button"><i class="fas fa-tags me-1"></i>Harga &amp; Persediaan</button></li>
                  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tSupplier" type="button"><i class="fas fa-truck me-1"></i>Supplier</button></li>
                  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tDokumen" type="button"><i class="fas fa-folder-open me-1"></i>Gambar &amp; Dokumen</button></li>
                </ul>
                <div class="tab-content p-3 p-md-4">

                  <!-- DATA UMUM -->
                  <div class="tab-pane fade show active" id="tUmum" role="tabpanel">
                    <form id="formIdentitas" class="od-form" novalidate>
                      <div class="od-sec">Identitas</div>
                      <div class="row g-3 mb-4">
                        <div class="col-md-6 col-xl-3">
                          <label class="form-label">ID Obat</label>
                          <input type="text" class="form-control bg-light" value="<?= $h($obat['pharmacy_number'], '') ?>" readonly>
                        </div>
                        <div class="col-md-6 col-xl-3">
                          <label class="form-label" for="f_code">Kode Obat BPJS</label>
                          <input type="text" id="f_code" name="pharmacy_code" class="form-control">
                        </div>
                        <div class="col-md-6 col-xl-3">
                          <label class="form-label required" for="f_generic">Nama Generik</label>
                          <input type="text" id="f_generic" name="pharmacy_name_generic" class="form-control" required maxlength="255">
                        </div>
                        <div class="col-md-6 col-xl-3">
                          <label class="form-label" for="f_trade">Nama Pabrikan / Dagang</label>
                          <input type="text" id="f_trade" name="pharmacy_name_trade" class="form-control" maxlength="255">
                        </div>
                      </div>
                      <div class="od-sec">Klasifikasi</div>
                      <div class="row g-3 mb-4">
                        <div class="col-md-6 col-xl-3">
                          <label class="form-label required" for="f_kat">Kategori</label>
                          <select name="pharmacy_category" class="form-select" id="f_kat" required>
                            <option value="">PILIH</option>
                            <option value="Obat">Obat</option>
                            <option value="BMHP">BMHP</option>
                            <option value="Alkes">Alkes</option>
                          </select>
                        </div>
                        <div class="col-md-6 col-xl-3">
                          <label class="form-label" for="f_sub">Sub Kategori</label>
                          <input type="text" id="f_sub" name="pharmacy_sub_category" class="form-control" list="listSub">
                          <datalist id="listSub">
                            <option value="Tablet">
                            <option value="Kapsul">
                            <option value="Sirup">
                            <option value="Krim/Salep">
                            <option value="Injeksi">
                            <option value="Tetes">
                          </datalist>
                        </div>
                        <div class="col-md-6 col-xl-3">
                          <label class="form-label" for="f_gol">Golongan</label>
                          <select name="pharmcy_golongan" class="form-select" id="f_gol">
                            <option value="">PILIH</option>
                            <option value="Bebas">Bebas</option>
                            <option value="Bebas Terbatas">Bebas Terbatas</option>
                            <option value="Keras">Keras</option>
                            <option value="Psikotropika">Psikotropika</option>
                            <option value="Narkotika">Narkotika</option>
                          </select>
                        </div>
                        <div class="col-md-6 col-xl-3">
                          <label class="form-label" for="f_jenis">Jenis</label>
                          <select name="pharmcy_jenis_drugs" class="form-select" id="f_jenis">
                            <option value="">PILIH</option>
                            <option value="Generik">Generik</option>
                            <option value="Paten">Paten</option>
                            <option value="Non-Generik">Non-Generik</option>
                          </select>
                        </div>
                      </div>
                      <div class="od-sec">Sediaan</div>
                      <div class="row g-3">
                        <div class="col-md-6 col-xl-3">
                          <label class="form-label" for="f_sediaan">Bentuk Sediaan</label>
                          <input type="text" id="f_sediaan" name="pharmacy_bentuk_sediaan" class="form-control" placeholder="cth. Tablet salut">
                        </div>
                        <div class="col-md-6 col-xl-3">
                          <label class="form-label" for="f_dosis">Dosis</label>
                          <input type="text" id="f_dosis" name="pharmacy_dosis" class="form-control" placeholder="cth. 500 mg">
                        </div>
                        <div class="col-md-6 col-xl-3">
                          <label class="form-label" for="f_unit">Satuan Terkecil</label>
                          <input type="text" id="f_unit" name="pharmacy_unit" class="form-control" placeholder="cth. Tablet" list="listUnit">
                          <datalist id="listUnit">
                            <option value="Tablet">
                            <option value="Kapsul">
                            <option value="Botol">
                            <option value="Tube">
                            <option value="Ampul">
                            <option value="Vial">
                            <option value="Pcs">
                          </datalist>
                        </div>
                        <div class="col-md-6 col-xl-3">
                          <label class="form-label" for="f_kemasan">Kemasan</label>
                          <input type="text" id="f_kemasan" name="pharmacy_kemasan" class="form-control" placeholder="cth. Box 10 strip x 10 tablet">
                        </div>
                      </div>
                      <div class="od-save text-end">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan Perubahan</button>
                      </div>
                    </form>
                  </div>

                  <!-- HARGA & PERSEDIAAN -->
                  <div class="tab-pane fade" id="tHarga" role="tabpanel">
                    <form id="formPersediaan" class="od-form" novalidate>
                      <div class="row g-4">
                        <div class="col-lg-7">
                          <div class="od-sec">Harga</div>
                          <div class="row g-3 mb-4">
                            <div class="col-md-6">
                              <label class="form-label" for="f_buy">Harga Beli Terakhir</label>
                              <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" id="f_buy" name="pharmacy_buy" class="form-control" min="0" step="1">
                              </div>
                            </div>
                            <div class="col-md-6">
                              <label class="form-label" for="f_sale">Harga Jual</label>
                              <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" id="f_sale" name="pharmacy_sale" class="form-control" min="0" step="1">
                              </div>
                              <div class="form-text" id="saleHint">Diisi manual.</div>
                            </div>
                          </div>
                          <div class="od-sec">Persediaan</div>
                          <div class="row g-3 mb-4">
                            <div class="col-md-6">
                              <label class="form-label" for="f_min">Stok Minimum</label>
                              <input type="number" id="f_min" name="stok_min" class="form-control" min="0" step="1">
                              <div class="form-text">Batas peringatan stok menipis.</div>
                            </div>
                            <div class="col-md-6">
                              <label class="form-label" for="f_max">Stok Maksimum</label>
                              <input type="number" id="f_max" name="stok_max" class="form-control" min="0" step="1">
                            </div>
                          </div>
                          <div class="od-sec">Katalog &amp; Status</div>
                          <div class="row g-3">
                            <div class="col-md-6">
                              <label class="form-label" for="f_katalog">Kode E-Katalog</label>
                              <input type="text" id="f_katalog" name="pharmacy_code_catalog" class="form-control">
                            </div>
                            <div class="col-md-6">
                              <label class="form-label" for="f_status">Status</label>
                              <select name="pharmacy_status" id="f_status" class="form-select">
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                              </select>
                            </div>
                            <div class="col-md-6">
                              <label class="form-label" for="f_fornas">Fornas</label>
                              <input type="text" id="f_fornas" name="fornas" class="form-control">
                            </div>
                            <div class="col-md-6">
                              <label class="form-label" for="f_formrs">Formularium RS</label>
                              <input type="text" id="f_formrs" name="formularium_rs" class="form-control">
                            </div>
                            <div class="col-12">
                              <label class="form-label" for="f_desc">Deskripsi</label>
                              <textarea id="f_desc" name="pharmacy_description" class="form-control" rows="3"></textarea>
                            </div>
                          </div>
                        </div>
                        <div class="col-lg-5">
                          <div class="od-sec">Perhitungan harga jual</div>
                          <?php if ($margin['margin_obat'] === null && $margin['margin_bmhp'] === null): ?>
                            <div class="alert alert-warning small mb-3">
                              Margin farmasi belum diatur, harga jual diisi manual.
                              <a href="module/admin/setting_margin" class="alert-link">Atur margin</a>
                            </div>
                          <?php endif; ?>
                          <div class="od-calc">
                            <div class="row-c"><span class="text-muted">Harga beli</span><span id="cBeli">-</span></div>
                            <div class="row-c"><span class="text-muted">Margin <span id="cMarginLbl"></span></span><span id="cMargin">-</span></div>
                            <div class="row-c"><span class="text-muted">Pembulatan ke atas</span><span id="cBulat">-</span></div>
                            <div class="row-c tot"><span id="cJualLbl">Harga jual disarankan</span><span id="cJual">-</span></div>
                            <button type="button" class="btn btn-sm btn-light w-100 mt-3" id="btnPakaiSaran"><i class="fas fa-magic me-1"></i>Pakai harga disarankan</button>
                          </div>
                        </div>
                      </div>
                      <div class="od-save text-end">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan Perubahan</button>
                      </div>
                    </form>
                  </div>

                  <!-- SUPPLIER -->
                  <div class="tab-pane fade" id="tSupplier" role="tabpanel">
                    <form id="formSupplier" class="od-form" novalidate style="max-width:760px">
                      <div class="od-sec">Pemasok</div>
                      <div class="row g-3">
                        <div class="col-md-6">
                          <label class="form-label" for="f_supplier">Supplier / PBF</label>
                          <input type="text" id="f_supplier" name="pharmacy_supplier" class="form-control">
                        </div>
                        <div class="col-md-6">
                          <label class="form-label" for="f_factory">Pabrik</label>
                          <input type="text" id="f_factory" name="pharmacy_factory" class="form-control">
                        </div>
                      </div>
                      <div class="od-save text-end">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan Perubahan</button>
                      </div>
                    </form>
                  </div>

                  <!-- DOKUMEN -->
                  <div class="tab-pane fade" id="tDokumen" role="tabpanel">
                    <?php if ($obat['sumber'] === 'global'): ?>
                      <div class="alert alert-light border small"><i class="fas fa-info-circle me-1 text-primary"></i>Simpan data obat terlebih dahulu (menjadi salinan faskes) sebelum mengunggah gambar atau dokumen.</div>
                    <?php endif; ?>
                    <div class="small text-muted mb-3">Gambar: JPG/PNG. Dokumen (brosur, sertifikat, dll.): JPG/PNG/PDF. Maksimal 2 MB.</div>
                    <div class="row g-3" id="docGrid"></div>
                  </div>
                </div>
              </div>
            </div>
          <?php endif; ?>

        </div>
      </div>
    </div>
  </div>

  <?php require 'library.php'; ?>

  <?php if ($obat): ?>
    <div class="modal fade" id="modalPreview" tabindex="-1">
      <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="previewTitle"></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body text-center" id="previewBody"></div>
        </div>
      </div>
    </div>

    <script>
      (function() {
        const OBAT = <?= json_encode($obat, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?>;
        const MARGIN = <?= json_encode(['obat' => $margin['margin_obat'], 'bmhp' => $margin['margin_bmhp']]) ?>;
        const ID = OBAT.id_pharmacy;
        const GLOBAL = OBAT.sumber === 'global';
        const api = 'controller/master/pharmacyDetailsController.php';
        const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({
          '&': '&amp;',
          '<': '&lt;',
          '>': '&gt;',
          '"': '&quot;',
          "'": '&#39;'
        } [c]));
        const rp = n => (+n > 0) ? 'Rp ' + Math.round(+n).toLocaleString('id-ID') : '-';
        const toast = (icon, title) => Swal.fire({
          toast: true,
          position: 'top-end',
          icon,
          title,
          showConfirmButton: false,
          timer: 2200,
          timerProgressBar: true
        });

        // ---------- isi form ----------
        document.querySelectorAll('.od-form [name]').forEach(el => {
          if (!(el.name in OBAT)) return;
          let v = OBAT[el.name] ?? '';
          if (el.type === 'number' && v !== '') v = Math.round(+v);
          el.value = v;
          // nilai lama di luar pilihan (mis. "Generic", "Tablet") tetap tampil apa adanya
          if (el.tagName === 'SELECT' && v !== '' && el.value !== String(v)) el.add(new Option(v, v, true, true));
        });
        if (OBAT.pharmacy_status === null || OBAT.pharmacy_status === '') document.getElementById('f_status').value = '1';

        // ---------- harga jual otomatis ----------
        const isBmhp = () => /^(bmhp|alkes|alat)/i.test(document.getElementById('f_kat').value || OBAT.pharmacy_category || '');

        function saranJual() {
          const beli = parseFloat(document.getElementById('f_buy').value) || 0;
          const m = isBmhp() ? MARGIN.bmhp : MARGIN.obat;
          const pm = m === null || m === '' ? null : parseFloat(m);
          $('#cBeli').text(rp(beli));
          $('#cMarginLbl').text(pm === null ? '(belum diatur)' : `${isBmhp() ? 'BMHP' : 'obat'} ${pm}%`);
          // margin aktif + ada harga beli: harga jual dikunci (server juga menghitung ulang saat simpan)
          const otomatis = pm !== null && beli > 0;
          $('#f_sale').prop('readonly', otomatis).toggleClass('bg-light', otomatis);
          $('#btnPakaiSaran').toggle(!otomatis);
          $('#cJualLbl').text(otomatis ? 'Harga jual (otomatis)' : 'Harga jual disarankan');
          $('#saleHint').html(otomatis ?
            `<i class="fas fa-lock me-1"></i>Otomatis dari harga beli + margin ${isBmhp() ? 'BMHP' : 'obat'} ${pm}%. <a href="module/admin/setting_margin">Ubah margin</a>` :
            (pm !== null ? 'Isi harga beli agar harga jual dihitung otomatis dari margin.' : 'Diisi manual (margin belum diatur).'));
          if (pm === null || !beli) {
            $('#cMargin, #cBulat, #cJual').text('-');
            $('#btnPakaiSaran').prop('disabled', true);
            return null;
          }
          const mentah = Math.round(beli * (1 + pm / 100) * 1e6) / 1e6;
          const kel = mentah < 1000 ? 10 : 100;
          const jual = Math.ceil(mentah / kel) * kel;
          $('#cMargin').text('+ ' + rp(mentah - beli));
          $('#cBulat').text((jual - mentah >= 0 ? '+ ' : '- ') + 'Rp ' + Math.abs(Math.round(jual - mentah)).toLocaleString('id-ID'));
          $('#cJual').text(rp(jual));
          $('#btnPakaiSaran').prop('disabled', false);
          return jual;
        }
        $('#f_buy, #f_kat').on('input change', () => {
          const j = saranJual();
          if (j !== null) $('#f_sale').val(j);
        });
        $('#btnPakaiSaran').on('click', () => {
          const j = saranJual();
          if (j !== null) $('#f_sale').val(j);
        });
        {
          const j = saranJual();
          if (j !== null) $('#f_sale').val(j);
        }

        // ---------- simpan ----------
        function pasang(id, cek) {
          const form = document.getElementById(id);
          form.addEventListener('submit', e => {
            e.preventDefault();
            if (!form.checkValidity()) {
              form.reportValidity();
              return;
            }
            if (cek) {
              const pesan = cek();
              if (pesan) return Swal.fire('Periksa data', pesan, 'warning');
            }
            const lanjut = () => {
              const btn = form.querySelector('[type="submit"]');
              const asli = btn.innerHTML;
              btn.disabled = true;
              btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
              const fd = new FormData(form);
              fd.append('id_pharmacy', ID);
              fd.append('_method', 'PUT');
              fetch(api, {
                  method: 'POST',
                  body: fd
                }).then(r => r.json()).then(r => {
                  if (!r.success) return Swal.fire('Gagal', r.message || 'Gagal menyimpan.', 'error');
                  if (r.new_id) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Tersimpan',
                        text: r.message,
                        timer: 1800,
                        showConfirmButton: false
                      })
                      .then(() => location.href = `module/admin/pharmacy_details?no=${r.new_id}`);
                    return;
                  }
                  fd.forEach((v, k) => {
                    if (k in OBAT) OBAT[k] = v;
                  });
                  if (r.harga_jual !== null && r.harga_jual !== undefined) {
                    OBAT.pharmacy_sale = r.harga_jual;
                    $('#f_sale').val(r.harga_jual);
                  }
                  $('#odNama').text(OBAT.pharmacy_name_generic || '-');
                  $('#odTrade').text(OBAT.pharmacy_name_trade || '');
                  $('#kpiJual').text(rp(OBAT.pharmacy_sale));
                  $('#kpiBeli').text(rp(OBAT.pharmacy_buy));
                  $('#kpiStok').text(`${OBAT.stok_min || '-'} / ${OBAT.stok_max || '-'}`);
                  toast('success', r.message || 'Tersimpan');
                })
                .catch(() => Swal.fire('Gagal', 'Tidak dapat terhubung ke server.', 'error'))
                .finally(() => {
                  btn.disabled = false;
                  btn.innerHTML = asli;
                });
            };
            if (!GLOBAL) return lanjut();
            Swal.fire({
              title: 'Buat salinan faskes?',
              html: 'Obat ini master bawaan. Perubahan akan disimpan sebagai <b>salinan khusus faskes Anda</b>.',
              icon: 'question',
              showCancelButton: true,
              confirmButtonText: 'Simpan',
              cancelButtonText: 'Batal'
            }).then(x => x.isConfirmed && lanjut());
          });
        }
        pasang('formIdentitas');
        pasang('formPersediaan', () => {
          const min = parseFloat($('#f_min').val()),
            max = parseFloat($('#f_max').val());
          if (!isNaN(min) && !isNaN(max) && max > 0 && min > max) return 'Stok minimum tidak boleh lebih besar dari stok maksimum.';
          const beli = parseFloat($('#f_buy').val()) || 0,
            jual = parseFloat($('#f_sale').val()) || 0;
          if (beli && jual && jual < beli) return 'Harga jual lebih kecil dari harga beli. Periksa kembali.';
        });
        pasang('formSupplier');

        // ---------- gambar & dokumen ----------
        const DOC = [{
          key: 'gambar',
          kolom: 'pharmacy_image',
          label: 'Gambar Obat',
          icon: 'fa-image',
          accept: 'image/jpeg,image/png'
        }, {
          key: 'dokumen',
          kolom: 'pharmacy_docs',
          label: 'Dokumen Barang',
          icon: 'fa-file-alt',
          accept: 'image/jpeg,image/png,application/pdf'
        }];
        const urlFile = f => 'uploads/pharmacy/' + encodeURIComponent(f);
        const isPdf = f => /\.pdf$/i.test(f || '');

        function renderDoc() {
          $('#docGrid').html(DOC.map(d => {
            const f = OBAT[d.kolom];
            const thumb = !f ? `<i class="fas ${d.icon}"></i>` : isPdf(f) ? '<i class="fas fa-file-pdf text-danger"></i>' :
              `<img src="${esc(urlFile(f))}" alt="" loading="lazy" onerror="this.replaceWith(Object.assign(document.createElement('i'),{className:'fas fa-image'}))">`;
            return `<div class="col-md-6 col-xl-4">
              <div class="od-doc ${f ? 'ada' : ''}">
                <div class="d-flex justify-content-between align-items-center">
                  <span class="fw-semibold"><i class="fas ${d.icon} text-primary me-2"></i>${d.label}</span>
                  ${f ? '<span class="od-badge bg-success-subtle text-success"><i class="fas fa-check"></i>Terunggah</span>' : '<span class="od-badge bg-light text-muted">Belum ada</span>'}
                </div>
                <div class="od-thumb ${f ? 'klik' : ''}" ${f ? `data-file="${esc(f)}" data-label="${d.label}"` : ''}>${thumb}</div>
                <div class="d-flex gap-2 mt-auto">
                  <label class="btn btn-sm ${f ? 'btn-light' : 'btn-primary'} flex-grow-1 mb-0 ${GLOBAL ? 'disabled' : ''}">
                    <i class="fas fa-upload me-1"></i>${f ? 'Ganti' : 'Unggah'}
                    <input type="file" class="d-none doc-input" data-key="${d.key}" accept="${d.accept}" ${GLOBAL ? 'disabled' : ''}>
                  </label>
                  ${f && !GLOBAL ? `<button type="button" class="btn btn-sm btn-light text-danger doc-hapus" data-key="${d.key}" title="Hapus"><i class="fas fa-trash"></i></button>` : ''}
                </div>
              </div>
            </div>`;
          }).join(''));
        }
        renderDoc();

        $(document).on('click', '.od-thumb.klik', function() {
          const f = this.dataset.file;
          $('#previewTitle').text(this.dataset.label);
          $('#previewBody').html(isPdf(f) ? `<iframe src="${esc(urlFile(f))}" style="width:100%;height:70vh;border:0"></iframe>` : `<img src="${esc(urlFile(f))}" class="img-fluid rounded" alt="">`);
          bootstrap.Modal.getOrCreateInstance('#modalPreview').show();
        });

        $(document).on('change', '.doc-input', function() {
          const file = this.files[0],
            key = this.dataset.key,
            d = DOC.find(x => x.key === key);
          if (!file) return;
          if (!d.accept.split(',').includes(file.type)) return Swal.fire('Format tidak didukung', key === 'gambar' ? 'Gunakan JPG atau PNG.' : 'Gunakan JPG, PNG atau PDF.', 'warning');
          if (file.size > 2 * 1024 * 1024) return Swal.fire('File terlalu besar', 'Ukuran maksimal 2 MB.', 'warning');
          $(this).closest('.od-doc').find('.od-thumb').html('<div class="spinner-border text-primary"></div>');
          const fd = new FormData();
          fd.append('id_pharmacy', ID);
          fd.append(key, file);
          $.ajax({
            url: 'controller/master/uploadPharmacy.php',
            type: 'POST',
            data: fd,
            contentType: false,
            processData: false,
            dataType: 'json'
          }).done(r => {
            if (r.status === 'success') {
              Object.assign(OBAT, r.files);
              toast('success', `${d.label} berhasil diunggah`);
            } else Swal.fire('Gagal', r.message || 'Upload gagal.', 'error');
          }).fail(x => Swal.fire('Gagal', (x.responseJSON && x.responseJSON.message) || 'Upload gagal.', 'error')).always(renderDoc);
        });

        $(document).on('click', '.doc-hapus', function() {
          const key = this.dataset.key,
            d = DOC.find(x => x.key === key);
          Swal.fire({
            title: `Hapus ${d.label}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#fa896b'
          }).then(x => {
            if (!x.isConfirmed) return;
            $.post('controller/master/uploadPharmacy.php', {
              id_pharmacy: ID,
              hapus: key
            }, null, 'json').done(r => {
              if (r.status !== 'success') return Swal.fire('Gagal', r.message, 'error');
              OBAT[d.kolom] = null;
              renderDoc();
              toast('success', `${d.label} dihapus`);
            }).fail(() => Swal.fire('Gagal', 'Tidak dapat terhubung ke server.', 'error'));
          });
        });
      })();
    </script>
  <?php endif; ?>
</body>

</html>
