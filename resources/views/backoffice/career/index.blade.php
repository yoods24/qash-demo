<x-backoffice.layout>

    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="fw-bold">Career</h2>
      <div class="action-buttons">
        <button class="btn btn-outline-secondary">Export</button>
        <button class="btn btn-outline-secondary">More actions</button>
        <button type="button" class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addCareerModal">
          Add Career
        </button>
      </div>
    </div>
    <div class="order-summary">
      <div><strong>Today:</strong> 48</div>
      <div><strong>Total career:</strong>{{$careerData->total()}}</div>
      <div><strong>Total Salary per month</strong>Rp{{number_format($totalSalary, 0, ',', '.')}}</div>
      <div><strong>Fulfilled orders over time:</strong> 359</div>
      <div><strong>Delivered orders over time:</strong> 353</div>
    </div>

    <ul class="nav nav-tabs mb-3" id="orderTabs" role="tablist">
      <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">All</button></li>
      <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#unpaid" type="button" role="tab">Unpaid</button></li>
      <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#ship" type="button" role="tab">Need to ship</button></li>
      <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#sent" type="button" role="tab">Sent</button></li>
      <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#completed" type="button" role="tab">Completed</button></li>
      <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#cancelled" type="button" role="tab">Cancellation</button></li>
      <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#returns" type="button" role="tab">Returns</button></li>
    </ul>

    <div class="filter-bar mb-2">
      <input type="text" class="form-control w-25" placeholder="Search order...">
      <button class="btn btn-outline-secondary">Filter</button>
    </div>

    <x-backoffice.table>
      <x-slot name="thead">
        <th scope="col">Id</th>
        <th scope="col">Title</th>
        <th scope="col">Salary</th>
        <th scope="col">Date Created</th>
        <th scope="col">Status</th>
        <th scope="col">Action</th>
      </x-slot>
      <x-slot name="tbody">
          @foreach ($careerData as $data)
            <tr>
            <td>{{$data->id}}</td>
            <td>{{$data->title}}</td>
            <td>Rp. {{number_format($data->salary, '0',',','.')}}</td>
            <td>{{$data->created_at->format('d-F-Y')}}</td>
              @if ($data->status)
                  <td><span class="status-badge online">Online</span></td> 
              @else
                  <td><span class="status-badge offline">Offline</span></td> 
              @endif
          <td>
              <a href="{{route('backoffice.career.edit', $data->id)}}" class="action-btn" title="Edit" aria-label="Edit">
                  <i class="bi bi-pencil-fill"></i>
              </a>

          <button
              class="action-btn delete-btn"
              title="Delete"
              aria-label="Delete"
              data-bs-toggle="modal"
              data-bs-target="#confirmModal"
              data-id="{{ $data->id }}"
              data-title="{{ $data->title }}"
              data-url="{{ route('backoffice.career.destroy', $data->id) }}"
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
            <label for="itemsPerPage" class="form-label mx-3">{{$careerData->links()}}</label>
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
<x-modal-create id="addCareerModal" title="Tambah Career" action="{{ route('backoffice.career.store') }}">
      <div class="container my-4">
          <h2 class="mb-4 text-dark">Create Career</h2>
              <div class="mb-3">
                  <label for="title" class="form-label">Job Name</label>
                  <input type="text" class="form-control" id="title" name="title" placeholder="Enter job title" required>
              </div>

              <div class="mb-3">
                  <label for="salary" class="form-label">Salary</label>
                  <input type="text" class="form-control" id="salary" name="salary" placeholder="Rp. 0" required>
              </div>
              
              <div class="mb-3">
                  <label for="about" class="form-label">About</label>
                  <input type="text" class="form-control" id="about" name="about" placeholder="Description of the job" required>
              </div>

              <div class="mb-3">
                  <label for="status" class="form-label">Status</label>
                  <select class="form-select" id="status" name="status" required>
                      <option value="Offline">Offline</option>
                      <option value="Online">Online</option>
                  </select>
              </div>
      </div>
</x-modal-create>
<!-- Modal -->
</x-backoffice.layout>
