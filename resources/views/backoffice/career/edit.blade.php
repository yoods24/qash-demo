<x-backoffice.layout>
<div class="container my-4">
    <h2 class="mb-4 text-dark">Edit Career</h2>
    <form action="{{route('backoffice.career.store')}}" method="POST">
        @method('POST')
        @csrf
        <div class="mb-3">
            <label for="title" class="form-label">Job Name</label>
            <input type="text" class="form-control" id="title" name="title" value="{{$career->title}}" required>
        </div>

        <div class="mb-3">
            <label for="salary" class="form-label">Salary</label>
            <input type="text" class="form-control" id="salary" name="salary" value="Rp. {{number_format($career->salary)}}" required>
        </div>
        
        <div class="mb-3">
            <label for="about" class="form-label">About</label>
            <input type="text" class="form-control" id="about" name="about" value="{{$career->about}}" required>
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" id="status" name="status" required>
                @if($career->status === 1)
                <option value="Online">Online</option>
                <option value="Offline">Offline</option>
                @else
                <option value="Offline">Offline</option>
                <option value="Online">Online</option>
                @endif
            </select>
        </div>

        <div class="d-flex justify-content-between">
            <a href="{{ route('backoffice.careers.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primer text-white">Create Career</button>
        </div>
    </form>
</div>
</x-backoffice.layout>
