<x-backoffice.layout>

    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="fw-bold">Staffs</h2>
      <div class="action-buttons">
        <button class="btn btn-outline-secondary">Export</button>
        <button class="btn btn-outline-secondary">More actions</button>
        <a href="{{ route('backoffice.staff.create') }}" type="button" class="btn btn-add">
          Add Staff
        </a>
      </div>
    </div>
    <div class="order-summary">
      <div class="summary-container">
          <p class="data">{{$staffCount}}</p>
          <p class="primer">Total Staff</p>
      </div>
      <div class="summary-container">
          <p class="data">5</p>
          <p class="primer">Low On Stock</p>
      </div>
      <div class="summary-container">
          <p class="data">Non Coffee</p>
          <p class="primer">Best Category</p>
      </div>
    </div>

    <ul class="nav nav-tabs mb-3" id="orderTabs" role="tablist">
      <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">All</button></li>
      <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#unpaid" type="button" role="tab">Unpaid</button></li>
    </ul>

    <div class="filter-bar mb-2">
      <input type="text" class="form-control w-25" placeholder="Search order...">
      <button class="btn btn-outline-secondary">Filter</button>
    </div>
      @livewire('backoffice.tables.staff-table')
      </div>

      <!-- Add other tabs like #unpaid, #ship etc. as needed -->
    </div>
<!-- Modal -->
</x-backoffice.layout>

