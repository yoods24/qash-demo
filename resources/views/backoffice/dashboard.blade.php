<x-backoffice.layout>
    <h4 class="mb-3 mb-md-4">{{tenant('id')}} Dashboard</h4>
    <hr>
        <!-- Top cards -->
        <div class="row g-3 mb-4">

        <div class="col col-lg-3 col-md-6 col-sm-12">
          <div class="gap-1 bg-green py-3 d-flex justify-content-start align-items-center p-2 rounded">
            <div class="mx-1 d-flex justify-content-center align-items-center bg-white rounded-circle" style="width: 50px; height: 50px;">
              <i class="bi bi-cash fs-4 text-green"></i>
            </div>
            <div class="d-flex flex-column text-white">
              <h5 class="mb-0">Total Earning</h5>
              <p class="m-0">Rp. 245.000</p>
            </div>
          </div>
        </div>

        <div class="col col-lg-3 col-md-6 col-sm-12">
          <div class="gap-1 bg-orange py-3 d-flex justify-content-start align-items-center p-2 rounded">
            <div class="mx-1 d-flex justify-content-center align-items-center bg-white rounded-circle" style="width: 50px; height: 50px;">
              <i class="bi bi-receipt fs-4 text-orange"></i>
            </div>
            <div class="d-flex flex-column text-white">
              <h5 class="mb-0">Total Order</h5>
              <p class="m-0">Rp. 245.000</p>
            </div>
          </div>
        </div>

        <div class="col col-lg-3 col-md-6 col-sm-12">
          <div class="gap-1 bg-purple-light py-3 d-flex justify-content-start align-items-center p-2 rounded">
            <div class="mx-1 d-flex justify-content-center align-items-center bg-white rounded-circle" style="width: 50px; height: 50px;">
              <i class="bi bi-person-fill fs-4 text-purple-light"></i>
            </div>
            <div class="d-flex flex-column text-white">
              <h5 class="mb-0">Total Customers</h5>
              <p class="m-0">Rp. 245.000</p>
            </div>
          </div>
        </div>

        <div class="col col-lg-3 col-md-6 col-sm-12">
          <div class="gap-1 bg-blue py-3 d-flex justify-content-start align-items-center p-2 rounded">
            <div class="mx-1 d-flex justify-content-center align-items-center bg-white rounded-circle" style="width: 50px; height: 50px;">
              <i class="bi bi-box-fill fs-4 text-blue"></i>
            </div>
            <div class="d-flex flex-column text-white">
              <h5 class="mb-0">Total Products</h5>
              <p class="m-0"></p>
            </div>
          </div>
        </div>

        </div>
        <!-- Charts row -->
        <div class="row g-3">
          <div class="col-12 col-md-6">
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
          <div class="col-12 col-md-6">
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

  <div class="row gx-3 gy-3 mt-3">
  <!-- Left Column -->
  <div class="col-lg-3 col-md-6 col-sm-12">
    <div class="border rounded bg-white p-3">
      <h4 class="mb-4 mt-3">Top 3 Favorite Menus</h4>
      <div id="favoriteMenuChart"></div>
    </div>
  </div>

  <!-- Right Column -->
  <div class="col-lg-9 col-md-6 col-sm-12">
    <div class="row gx-3 gy-3">
      <!-- Most Ordered Menu -->
      <div class="col-lg-12 col-md-12 ">
        <div class="table-responsive border rounded bg-white">
          <div class="panel-header p-3 fw-bold">Most Ordered Menu</div>
          <hr class="m-0">
          <table class="table table-hover text-center text-nowrap mb-0">
            <thead>
              <tr>
                <th>No</th>
                <th>Menu</th>
                <th>Changes Last Month</th>
                <th>Quantity</th>
                <th>Product Revenue</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>1</td>
                <td>Burger</td>
                <td>
                  <span class="text-success">
                    <i class="bi bi-caret-up me-1"></i>28%
                  </span>
                </td>
                <td>125</td>
                <td>
                  <span class="text-danger">
                    <i class="bi bi-caret-down me-1"></i>-17,654
                  </span>
                </td>
              </tr>
              <tr>
                <td>2</td>
                <td>Sampoerna Mild</td>
                <td>
                  <span class="text-danger">
                    <i class="bi bi-caret-down me-1"></i>28%
                  </span>
                </td>
                <td>125</td>
                <td>
                  <span class="text-danger">
                    <i class="bi bi-caret-down me-1"></i>-17,654
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Top Customers -->
      <div class="col-lg-12 col-md-12">
        <div class="border rounded bg-white h-100">
          <div class="panel-header fw-bold p-3">Top Customers</div>
          <hr class="m-0">
          <!-- Add your top customers content here -->
          <div class="d-flex">
            <div class="card m-3 p-3 justify-content-center align-items-center" style="width: 18rem;">
              <img src="{{ url('/storage/ui/profile.png') }}" 
                class="card-img-top rounded-circle w-25" 
                alt="Profile">
              <p class="mt-2">Fadil Ramadhan</p>
              <div class="bg-green w-100 rounded d-flex justify-content-center text-white">
                35 Orders
              </div>
            </div>

            <div class="card m-3 p-3 justify-content-center align-items-center" style="width: 18rem;">
              <img src="{{ url('/storage/ui/profile.png') }}" 
                class="card-img-top rounded-circle w-25" 
                alt="Profile">
              <p class="mt-2">Rio Jenari</p>
              <div class="bg-green w-100 rounded d-flex justify-content-center text-white">
                29 Orders
              </div>
            </div>
          </div>
        </div>
      </div>



    </div>
  </div>

  <div>
    @livewire('blog-posts-chart')
  </div>
</div>



@push('scripts')
<!-- ApexCharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        var chartHeight = window.innerWidth < 576 ? 260 : 350;
        var options = {
            chart: {
                type: 'pie',
                height: chartHeight
            },
            series: [44, 33, 23], // dummy data (orders count per menu)
            labels: ['Nasi Goreng', 'Mie Ayam', 'Sate Ayam'], // menu names
            colors: ['#01be5f', '#a953ff', '#ff8c00'],
            legend: {
                position: 'bottom'
            }
        };

        var chart = new ApexCharts(document.querySelector("#favoriteMenuChart"), options);
        chart.render();
        // Update chart size on resize
        window.addEventListener('resize', function() {
          var newHeight = window.innerWidth < 576 ? 260 : 350;
          chart.updateOptions({ chart: { height: newHeight } });
        });
    });
</script>
@endpush

</x-backoffice.layout>


