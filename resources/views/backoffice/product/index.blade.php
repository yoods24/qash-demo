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

    <ul class="nav nav-tabs mb-3" id="orderTabs" role="tablist">
      <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">All</button></li>
      <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#unpaid" type="button" role="tab">Unpaid</button></li>
      <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#ship" type="button" role="tab">Need to ship</button></li>
    </ul>

    <div class="filter-bar mb-2">
      <input type="text" class="form-control w-25" placeholder="Search order...">
      <button class="btn btn-outline-secondary">Filter</button>
    </div>

    <x-backoffice.table>
      <x-slot name="thead">
        <th scope="col">Id</th>
        <th scope="col">Title</th>
        <th scope="col">Price</th>
        <th scope="col">Date Created</th>
        <th scope="col">Category</th>
        <th scope="col">Status</th>
        <th scope="col">Action</th>
      </x-slot>
      <x-slot name="tbody">
          @foreach ($products as $product)
            <tr>
            <td>{{$product->id}}</td>
            <td>{{$product->name}}</td>
            <td>Rp. {{number_format($product->price, '0',',','.')}}</td>
            <td>{{$product->category->name}}</td>
            <td>{{$product->created_at->format('d-F-Y')}}</td>
              @if ($product->featured)
                  <td><span class="status-badge online">Featured</span></td> 
              @else
                  <td><span class="status-badge offline">Not Featured</span></td> 
              @endif
          <td>
              <button onclick="window.location='{{ route('backoffice.product.edit', $product->id) }}'" class="action-btn edit-btn-table" title="Edit" aria-label="Edit">
                  <i class="bi bi-pencil"></i>
              </button>
              <button onclick="window.location='{{ route('backoffice.career.edit', $product->id) }}'" class="action-btn view-btn-table" title="View" aria-label="View">
                  <i class="bi bi-eye"></i>
              </button>
          <button
              class="action-btn delete-btn-table"
              title="Delete"
              aria-label="Delete"
              data-bs-toggle="modal"
              data-bs-target="#confirmModal"
              data-id="{{ $product->id }}"
              data-title="{{ $product->title }}"
              data-url="{{ route('backoffice.career.destroy', $product->id) }}"
          >
              <i class="bi bi-trash-fill"></i>
          </button>
          </td>
          </tr>
          @endforeach
      </x-slot>
    </x-backoffice.table>

        <div class="d-flex justify-content-end align-items-center">
          <div>
            <label for="itemsPerPage" class="form-label mx-3">{{$products->links()}}</label>
            <select class="form-select d-inline-block w-auto" id="itemsPerPage">
              <option>10</option>
              <option>20</option>
              <option>50</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Add other tabs like #unpaid, #ship etc. as needed -->
    </div>
<!-- Modal -->
</x-backoffice.layout>
