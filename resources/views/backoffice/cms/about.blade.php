<x-backoffice.layout>
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="mb-4">
                        <h5 class="mb-1">Homepage About Content</h5>
                        <p class="text-muted mb-0">
                            Update the hero copy that appears on your public landing page.
                        </p>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <div class="fw-semibold mb-1">We ran into some issues:</div>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('backoffice.tenant-profile.update-about') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="about" class="form-label fw-semibold">About Copy</label>
                            <textarea
                                class="form-control"
                                id="about"
                                name="about"
                                rows="6"
                                placeholder="Share your story, highlight your signature menus, and include any CTA.">{{ old('about', $tenantProfile->about ?? '') }}</textarea>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary px-4">
                                Save Content
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-backoffice.layout>
