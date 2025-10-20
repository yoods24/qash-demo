<x-backoffice.layout>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="fw-bold">Product</h2>
      <div class="action-buttons">
        <button class="btn btn-outline-secondary">Export</button>
        <button class="btn btn-outline-secondary">More actions</button>
        <a href="{{route('backoffice.product.create')}}" class="btn btn-add" >
          Add Product
        </a>
      </div>
    </div>
    <div class="order-summary">
      <div class="summary-container">
          <p class="data">{{$products->total()}}</p>
          <p class="primer">Total Product</p>
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

    <div>
      @livewire('backoffice.tables.products-table')
    </div>

      <!-- Add other tabs like #unpaid, #ship etc. as needed -->
    </div>
<!-- Modal -->
</x-backoffice.layout>
