<x-backoffice.layout>
    <h4 class="mb-4">Dashboard</h4>
        <!-- Top cards -->
        <div class="row g-3 mb-4">

          <div class="col-md-3">
            <div class="card text-white card-black p-3 card-small">
              <small>Gross Sales</small>
              <h2>Rp. 26.978.874</h2>
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <svg width="80" height="30">
                    <polyline fill="none" stroke="#f05a28" stroke-width="2" points="0,15 20,10 40,18 60,12 80,15" />
                  </svg>
                </div>
                <div class="red-percentage">↓ 18%</div>
              </div>
            </div>
          </div>

          <div class="col-md-3">
            <div class="card text-white card-black p-3 card-small">
              <small>NET SALES</small>
              <h2>Rp. 17.841.101</h2>
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <svg width="80" height="30">
                    <polyline fill="none" stroke="#f05a28" stroke-width="2" points="0,15 20,10 40,18 60,12 80,15" />
                  </svg>
                </div>
              <div class="green-percentage">↑ 24%</div>
            </div>
            </div>
          </div>

          <div class="col-md-3">
            <div class="card card-white p-3 card-small">
              <small>GROSS PROFIT</small>
              <h2>Rp. 21.412.345</h2>
              <div class="text-info">↑ 18%</div>
            </div>
          </div>

          <div class="col-md-3">
            <div class="card card-white p-3 card-small">
              <small>Transaction</small>
              <h2>154</h2>
              <div class="text-danger">↓ 18%</div>
            </div>
          </div>
        </div>

        

        <!-- Charts row -->
        <div class="row g-3">
          <div class="col-md-6">
            <div class="card card-white p-3">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Sales Pipeline by Stage</h6>
                <select class="form-select form-select-sm w-auto">
                  <option>Week</option>
                  <option>Month</option>
                  <option>Year</option>
                </select>
              </div>
              <div class="chart-placeholder" data-bs-toggle="tooltip" data-bs-placement="top" title="Total New 60%"></div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card card-white p-3">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Sales Won Vs. Open</h6>
                <select class="form-select form-select-sm w-auto">
                  <option>Week</option>
                  <option>Month</option>
                  <option>Year</option>
                </select>
              </div>
              <div class="chart-placeholder" data-bs-toggle="tooltip" data-bs-placement="top" title="Total Open 80%"></div>
            </div>
          </div>
        </div>
</x-backoffice.layout>
