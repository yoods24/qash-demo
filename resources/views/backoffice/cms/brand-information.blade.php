<x-backoffice.layout>
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="mb-4">
                        <h5 class="mb-1">Brand Information</h5>
                        <p class="text-muted mb-0">
                            Control the heading (H1) and slogan (H3) that appear on your public pages.
                        </p>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <div class="fw-semibold mb-1">Please fix the following issues:</div>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('backoffice.tenant-profile.update-brand-info') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="brand_heading" class="form-label fw-semibold">
                                Brand Heading (H1) <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                class="form-control"
                                id="brand_heading"
                                name="brand_heading"
                                value="{{ old('brand_heading', $tenantProfile->brand_heading ?? '') }}"
                                placeholder="Create an unforgettable story"
                                required
                            >
                        </div>

                        <div class="mb-4">
                            <label for="brand_slogan" class="form-label fw-semibold">
                                Brand Slogan (H3)
                            </label>
                            <input
                                type="text"
                                class="form-control"
                                id="brand_slogan"
                                name="brand_slogan"
                                value="{{ old('brand_slogan', $tenantProfile->brand_slogan ?? '') }}"
                                placeholder="Artisan coffee & soulful bites"
                            >
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary px-4">
                                Save Brand Information
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-backoffice.layout>
