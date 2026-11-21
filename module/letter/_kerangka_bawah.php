          <!-- /field khusus surat -->
          <div class="small text-danger mt-3 d-none" id="sfError"></div>
        </div>
        <div class="modal-footer py-2">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary" id="sfSimpan"><i class="fas fa-save me-1"></i>Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <?php require '../admin/library.php'; ?>
  <script>
    window.SF_SETTING = <?= json_encode(['ada' => $SF_SET['ada'], 'mode' => $SF_SET['mode'], 'format' => $SF_SET['format'], 'terakhir' => $SF_SET['terakhir'], 'admin' => $SF_ADMIN], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
  </script>
  <script src="assets/js/surat-form.js?v=<?= @filemtime(__DIR__ . '/../../assets/js/surat-form.js') ?>"></script>
