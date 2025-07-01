<x-backoffice.layout>

    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="fw-bold">Roles</h2>
      <div class="action-buttons">
        <button class="btn btn-outline-secondary">Export</button>
        <button class="btn btn-outline-secondary">More actions</button>
        <button type="button" class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addProductModal">
          Add Role
        </button>
      </div>
    </div>
    <div class="order-summary">
      <div><strong>Total Role: </strong>{{$roles->count()}}</div>
    </div>

    <ul class="nav nav-tabs mb-3" id="orderTabs" role="tablist">
      <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">All</button></li>
      <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#unpaid" type="button" role="tab">Unpaid</button></li>
    </ul>

    <div class="filter-bar mb-2">
      <input type="text" class="form-control w-25" placeholder="Search order...">
      <button class="btn btn-outline-secondary">Filter</button>
    </div>

    <x-backoffice.table>
      <x-slot name="thead">
        <th scope="col">Id</th>
        <th scope="col">Name</th>
        <th scope="col">Action</th>
      </x-slot>
      <x-slot name="tbody">
          @foreach ($roles as $role)
            <tr>
            <td>{{$role->id}}</td>
            <td>{{$role->name}}</td>
          <td>
              <a href="{{route('backoffice.permission.index', $role->id)}}" class="action-btn" title="Edit" aria-label="Edit">
                  <i class="bi bi-pencil-fill"></i>
              </a>

          <button
              class="action-btn delete-btn"
              title="Delete"
              aria-label="Delete"
              data-bs-toggle="modal"
              data-bs-target="#confirmModal"
              data-id="{{ $role->id }}"
              data-title="{{ $role->name }}"
              data-url="{{ route('backoffice.role.destroy', $role->id) }}"
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
            {{-- <label for="itemsPerPage" class="form-label mx-3">{{$careerData->links()}}</label> --}}
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


<x-modal-create id="addProductModal" title="Tambah Role" action="{{ route('backoffice.role.store') }}">
    <div class="mb-3">
        <label for="product" class="form-label">Add Role</label>
        <input type="text" class="form-control" id="role" name="role" placeholder="Masukkan Role">
    </div>
</x-modal-create>

<!-- Modal -->
</x-backoffice.layout>
