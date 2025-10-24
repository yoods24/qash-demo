<x-backoffice.layout>

    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="fw-bold">Category</h2>
      <div class="action-buttons">
        <button class="btn btn-outline-secondary">Export</button>
        <button class="btn btn-outline-secondary">More actions</button>
        <button type="button" class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addProductModal">
          Add Role
        </button>
      </div>
    </div>
    <div class="order-summary">
      <div class="summary-container">
          <p class="data">{{$categories->total()}}</p>
          <p class="primer">Total Category</p>
      </div>
    </div>

    <ul class="nav nav-tabs mb-3" id="orderTabs" role="tablist">
      <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">All</button></li>
      <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#unpaid" type="button" role="tab">Unpaid</button></li>
    </ul>

    @livewire('backoffice.tables.category-table')
      <!-- Add other tabs like #unpaid, #ship etc. as needed -->
    </div>


<x-modal-create id="addProductModal" title="Add Category" action="{{ route('backoffice.category.store') }} ">
    <div class="mb-3">
        <label for="product" class="form-label">Add Category</label>
        <input type="text" class="form-control" id="name" name="name" placeholder="Category Name">
    </div>
    <div class="mb-3">
        <label for="image_url">Insert Image</label>
        <input type="file" id="image_url" name="image_url" class="form-control-file" >
    </div>
</x-modal-create>

<!-- Modal -->
</x-backoffice.layout>
