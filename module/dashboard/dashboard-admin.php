   <div class="row">
            <div class="col-lg-8 d-flex align-items-strech">
              <div class="card w-100">
                <div class="card-body">
                  <div class="d-sm-flex d-block align-items-center justify-content-between mb-9">
                    <div class="mb-3 mb-sm-0">
                      <h5 class="card-title fw-semibold">Revenue Forecast </h5>
                    </div>
                    <div>
                      <select class="form-select">
                        <option value="1">March 2024</option>
                        <option value="2">April 2024</option>
                        <option value="3">May 2024</option>
                        <option value="4">June 2024</option>
                      </select>
                    </div>
                  </div>
                  <div id="revenue-forecast"></div>
                </div>
              </div>
            </div>
            <div class="col-lg-4">
              <div class="row">
                <div class="col-lg-12">
                  <div class="card">
                    <div class="card-body">
                      <div class="d-flex align-items-center gap-6 mb-4 pb-3">
                        <span
                          class="round-48 d-flex align-items-center justify-content-center rounded bg-secondary-subtle">
                          <iconify-icon icon="solar:user-outline" class="fs-6 text-secondary"> </iconify-icon>
                        </span>
                        <h6 class="mb-0 fs-4">Transaction Order</h6>
                      </div>
                      <div class="row">
                        <div class="col-12">
                          <h4><?= number_format($totalorder) ?></h4>
                          <span class="fs-11 text-success fw-semibold">+18%</span> bulan ini
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-lg-12">
                  <div class="card">
                    <div class="card-body">
                      <div class="d-flex align-items-center gap-6 mb-4">
                        <span
                          class="round-48 d-flex align-items-center justify-content-center rounded bg-danger-subtle">
                          <iconify-icon icon="solar:box-linear" class="fs-6 text-danger"></iconify-icon>
                        </span>
                        <h6 class="mb-0 fs-4">Total Income</h6>
                      </div>
                      <div class="row">
                        <div class="col-12">
                          <h4>IDR <?= number_format($amount) ?></h4>
                          <span class="fs-11 text-success fw-semibold">+27%</span> bulan ini
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>