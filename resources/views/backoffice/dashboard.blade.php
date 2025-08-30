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

<div class="row g-3 mt-3">
  <div class="col-md-3 border">
    <h4 class="mb-4">Top 3 Favorite Menus</h4>
    <div id="favoriteMenuChart"></div>
  </div>

<div class="table-responsive border mt-3 col-md-9">
  <div class="panel-header p-3">Most Ordered Menu</div>
  <hr>
<table class="table table-hover text-center text-nowrap">
  <thead>
    <tr>
      <th scope="col">No</th>
      <th scope="col">Menu</th>
      <th scope="col">Changes Last Month</th>
      <th scope="col">Quantity</th>
      <th scope="col">Product Revenue</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>1</td>
      <td>Burger</td>
      <td>
        <span class="text-success">
          <i class="bi bi-caret-up me-1"></i><span>28%</span>
        </span>
      </td>
      <td>125</td>
      <td>
        <span class="text-danger">
          <i class="bi bi-caret-down me-1"></i><span>-17,654</span>
        </span>
      </td>
    </tr>
    <tr>
      <td>2</td>
      <td>Sampoerna Mild</td>
      <td>
        <span class="text-danger">
          <i class="bi bi-caret-down me-1"></i><span>28%</span>
        </span>
      </td>
      <td>125</td>
      <td>
        <span class="text-danger">
          <i class="bi bi-caret-down me-1"></i><span>-17,654</span>
        </span>
      </td>
    </tr>
  </tbody>
</table>
</div>
</div>



@push('scripts')
<!-- ApexCharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        var options = {
            chart: {
                type: 'pie',
                height: 350
            },
            series: [44, 33, 23], // dummy data (orders count per menu)
            labels: ['Nasi Goreng', 'Mie Ayam', 'Sate Ayam'], // menu names
            colors: ['#008FFB', '#00E396', '#FEB019'],
            legend: {
                position: 'bottom'
            }
        };

        var chart = new ApexCharts(document.querySelector("#favoriteMenuChart"), options);
        chart.render();
    });
</script>
@endpush

</x-backoffice.layout>



