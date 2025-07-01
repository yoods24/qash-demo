<div class="container my-4">
    <h2 class="mb-4 text-dark">Create Career</h2>
    <form action="#" method="POST">
        @csrf
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

        <div class="d-flex justify-content-between">
            <a href="{{ route('careers.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primer text-white">Create Career</button>
        </div>
    </form>
</div>