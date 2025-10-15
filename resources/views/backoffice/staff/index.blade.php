<x-backoffice.layout>

    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="fw-bold">Staffs</h2>
      <div class="action-buttons">
        <button class="btn btn-outline-secondary">Export</button>
        <button class="btn btn-outline-secondary">More actions</button>
        <button type="button" class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addStaffModal">
          Add Staff
        </button>
      </div>
    </div>
    <div class="order-summary">
      <div class="summary-container">
          <p class="data">{{$staffs->count()}}</p>
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

    <x-backoffice.table>
      <x-slot name="thead">
        <th scope="col">Id</th>
        <th scope="col">Name</th>
        <th scope="col">Email</th>
        <th scope="col">Phone</th>
        <th scope="col">Role</th>
        <th scope="col">Status</th>
        <th scope="col">Action</th>
      </x-slot>
      <x-slot name="tbody">
          @foreach ($staffs as $staff)
            <tr>
            <td>{{$staff->id}}</td>
            <td>{{$staff->name}}</td>
            <td>{{$staff->email}}</td>
            <td>{{$staff->phone}}</td>
            <td>{{$staff->getRoleNames()->first() ?? 'No Role'}}</td>

              @if ($staff->status)
                  <td><span class="status-badge online">Kerja</span></td>
              @else
                  <td><span class="status-badge offline">Libur</span></td>
              @endif
          <td>
              <button onclick="window.location='{{ route('backoffice.career.edit', $staff->id) }}'" class="action-btn edit-btn-table" title="Edit" aria-label="Edit">
                  <i class="bi bi-pencil"></i>
              </button>
              <button onclick="window.location='{{ route('backoffice.staff.view', $staff->id) }}'" class="action-btn view-btn-table" title="View" aria-label="View">
                  <i class="bi bi-eye"></i>
              </button>
          <button
              class="action-btn delete-btn-table"
              title="Delete"
              aria-label="Delete"
              data-bs-toggle="modal"
              data-bs-target="#confirmModal"
              data-id="{{ $staff->id }}"
              data-title="{{ $staff->name }}"
              data-url="{{ route('backoffice.staff.destroy', $staff->id) }}"
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
            <label for="itemsPerPage" class="form-label mx-3">{{$staffs->links()}}</label>
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
<x-modal-create id="addStaffModal" title="Tambah Staff" action="{{ route('backoffice.staff.store') }}">
      <div class="container my-4">
              <div class="mb-3">
                  <label for="name" class="form-label">Name</label>
                  <input type="text" class="form-control" id="name" name="name" placeholder="Enter Staff Name" required>
                    @error('name')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
              </div>

              <div class="mb-3">
                  <label for="email" class="form-label">Email</label>
                  <input type="text" class="form-control" id="email" name="email" placeholder="email@.com" required>
                    @error('email')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror                  
              </div>
              
              <div class="mb-3">
                <label class="sr-only" for="inlineFormInputGroup">Phone</label>
                <div class="input-group mb-2">
                    <div class="input-group-prepend">
                        <div class="input-group-text">+62</div>
                    </div>
                <input type="text" class="form-control" placeholder="Phone Number" id="phone" name="phone">
                    @error('phone')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
              </div>

              <div class="mb-3">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        class="form-control @error('password') is-invalid @enderror" 
                        id="password" 
                        name="password" 
                        placeholder="Password"
                    >
                    @error('password')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
              </div>

              <div class="form-group w-50">
                  <label for="roleSelect">Role</label>
                  <select class="form-control" id="roleSelect" name="role" required>
                      <option value="">Select Role</option>
                      @foreach ($roles as $role)
                          <option value="{{ $role }}">{{ $role }}</option>
                      @endforeach
                  </select>
                  @error('role')
                      <small class="text-danger">{{ $message }}</small>
                  @enderror
              </div>
      </div>
</x-modal-create>
<!-- Modal -->
</x-backoffice.layout>
