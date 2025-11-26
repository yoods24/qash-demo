<x-backoffice.layout>
    <div class="d-flex align-items-center justify-content-between mb-3 mb-md-4 flex-wrap gap-2">
        <div class="d-flex align-items-center">
            <i class="bi bi-images text-orange fs-3 me-2"></i>
            <h4 class="mb-0">Gallery</h4>
        </div>
        <div class="text-muted small">Maximum 5 photos</div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <form method="POST" action="{{ route('backoffice.gallery.store') }}" enctype="multipart/form-data" class="row g-3">
                @csrf
                <div class="col-12 col-md-8">
                    <label class="form-label fw-semibold">Upload photos</label>
                    <input
                        type="file"
                        name="photos[]"
                        accept="image/*"
                        multiple
                        class="form-control @error('photos.*') is-invalid @enderror"
                        {{ count($photos) >= 5 ? 'disabled' : '' }}
                    >
                    @error('photos.*')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">You can upload up to {{ max(0, 5 - count($photos)) }} more photo(s).</div>
                </div>
                <div class="col-12 col-sm-12">
                    <button class="btn btn-primer 100" {{ count($photos) >= 5 ? 'disabled' : '' }}>
                        <i class="bi bi-upload me-1"></i> Add Photos
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3">
        @forelse($photos as $photo)
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden">
                    <div class="ratio ratio-4x3">
                        <img src="{{ tenant_storage_url($photo) }}" class="w-100 h-100 object-fit-cover" alt="Gallery photo">
                    </div>
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <span class="text-truncate small">{{ $photo }}</span>
                        <form method="POST" action="{{ route('backoffice.gallery.destroy') }}">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="photo" value="{{ $photo }}">
                            <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Remove this photo?')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-light mb-0">No photos yet. Upload up to 5 images to show in your customer gallery.</div>
            </div>
        @endforelse
    </div>
</x-backoffice.layout>
