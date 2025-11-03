<x-backoffice.layout>
    <h4>Welcome, <span class="text-primer"> {{ request()->user()->fullName() }}</span></h4>
    <hr>
    <!-- Top metrics -->
    @php
        $m = $metrics ?? [
            'totalSales' => 0,
            'totalOrders' => 0,
            'activeOrders' => 0,
            'avgOrderValue' => 0,
            'totalUsers' => 0,
            'totalProducts' => 0,
            'totalCategories' => 0,
            'presentToday' => 0,
            'totalEmployees' => 0,
        ];
        $money = fn($v) => 'Rp '.number_format((float)$v, 0, ',', '.');
    @endphp
    <!-- Attendance summary -->
    <div class="row g-3 mb-3">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small ">Today's Attendance</div>
                        <div class="fs-5 fw-bold">{{ number_format($m['presentToday']) }}/{{ number_format($m['totalEmployees']) }} employees attended</div>
                    </div>
                    <div class="metric-icon rounded d-flex align-items-center justify-content-center text-success bg-success-subtle">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
          <div class="col-12 col-md-6 col-lg-3">
            <a href="{{ route('backoffice.reports.sales') }}" class="text-decoration-none text-dark">
              <div class="card shadow-sm h-100">
                  <div class="card-body d-flex justify-content-between align-items-start">
                      <div>
                          <div class="text-muted small">Total Sales</div>
                          <div class="fs-5 fw-bold">{{ $money($m['totalSales']) }}</div>
                      </div>
                      <div class="metric-icon rounded d-flex align-items-center justify-content-center text-primary bg-primary-subtle">
                          <i class="bi bi-cash-coin"></i>
                      </div>
                  </div>
              </div>
            </a>
          </div>
        <div class="col-12 col-md-6 col-lg-3">
          <a href="{{ route('backoffice.order.index') }}" class="text-decoration-none text-dark">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small">Total Orders</div>
                        <div class="fs-5 fw-bold">{{ number_format($m['totalOrders']) }}</div>
                    </div>
                    <div class="metric-icon rounded d-flex align-items-center justify-content-center text-success bg-success-subtle">
                        <i class="bi bi-basket"></i>
                    </div>
                </div>
            </div>
          </a>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small">Total Active Orders</div>
                        <div class="fs-5 fw-bold">{{ number_format($m['activeOrders']) }}</div>
                    </div>
                    <div class="metric-icon rounded d-flex align-items-center justify-content-center text-danger bg-danger-subtle">
                        <i class="bi bi-receipt"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small">Average Order Value</div>
                        <div class="fs-5 fw-bold">{{ $money($m['avgOrderValue']) }}</div>
                    </div>
                    <div class="metric-icon rounded d-flex align-items-center justify-content-center text-purple bg-purple-subtle">
                        <i class="bi bi-person-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small">Total Users</div>
                        <div class="fs-5 fw-bold">{{ number_format($m['totalUsers']) }}</div>
                    </div>
                    <div class="metric-icon rounded d-flex align-items-center justify-content-center text-warning bg-warning-subtle">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
            <a href="{{ route('backoffice.product.index') }}" class="text-decoration-none text-dark">
                <div class="card-body d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small">Total Products</div>
                        <div class="fs-5 fw-bold">{{ number_format($m['totalProducts']) }}</div>
                    </div>
                    <div class="metric-icon rounded d-flex align-items-center justify-content-center text-danger bg-danger-subtle">
                        <i class="bi bi-box"></i>
                    </div>
                </div>
                </a>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
            <a href="{{ route('backoffice.category.index') }}" class="text-decoration-none text-dark">
                <div class="card-body d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small">Total Categories</div>
                        <div class="fs-5 fw-bold">{{ number_format($m['totalCategories']) }}</div>
                    </div>
                    <div class="metric-icon rounded d-flex align-items-center justify-content-center text-info bg-info-subtle">
                        <i class="bi bi-folder"></i>
                    </div>
                </div>
            </a>
            </div>
        </div>
    </div>
        <!-- Charts row -->
        <div class="row g-3">
          <div class="col-12">
            @livewire(\App\Filament\Widgets\SalesPurchaseChart::class)
          </div>
        </div>

  <div class="row gx-3 gy-3 mt-3">
  <!-- Left Column -->
  <div class="col-lg-3 col-md-6 col-sm-12">
        @livewire(\App\Filament\Widgets\CustomersOverviewChart::class)
  </div>

  <!-- Right Column -->
  <div class="col-lg-9 col-md-6 col-sm-12">
    <div class="row gx-0 gy-3">
      <!-- Most Ordered Menu -->
      <div class="col-lg-12 col-md-12 dlmode-backup card">
        <div class="table-responsive ">
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
        <div class="h-100 card">
          <div class="panel-header fw-bold p-3 ">Top Customers</div>
          <hr class="m-0">
          <!-- Add your top customers content here -->
          <div class="d-flex">
            <div class="card m-3 p-3 justify-content-center align-items-center" style="width: 18rem;">
              <img src="{{ url('/storage/ui/profile.png') }}" 
                class="card-img-top rounded w-25" 
                alt="Profile">
              <p class="mt-2">Fadil Ramadhan</p>
              <div class="bg-green w-100 rounded d-flex justify-content-center text-white">
                35 Orders
              </div>
            </div>

            <div class="card m-3 p-3 justify-content-center align-items-center" style="width: 18rem;">
              <img src="{{ url('/storage/ui/profile.png') }}" 
                class="card-img-top rounded w-25" 
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
