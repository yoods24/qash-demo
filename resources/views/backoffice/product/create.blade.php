<x-backoffice.layout>
<div class="container my-4">
    <h2 class="fw-bold mb-4">Add New Product</h2>

    <form action="{{ route('backoffice.product.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <!-- Left Tabs -->
            <div class="col-md-3">
                <ul class="nav nav-pills flex-column" id="productTabs" role="tablist">
                    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#general" role="tab">General</a></li>
                </ul>
            </div>

            <!-- Right Tabs -->
            <div class="col-md-9">
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="general" role="tabpanel">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Category</label>
                            <select name="category_id" class="form-select" required>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Product Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Alternate Name</label>
                            <input type="text" name="alternate_name" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Price</label>
                            <input type="number" name="price" step="0.01" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Goods Price</label>
                            <input type="number" name="goods_price" step="0.01" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Product Image</label>
                            <input type="file" name="product_image" class="form-control">
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="featured" value="1">
                            <label class="form-check-label fw-bold">Featured Product</label>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-end">
            <a href="{{ route('backoffice.product.index') }}" class="btn btn-secondary me-2">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Product</button>
        </div>
    </form>
</div>
</x-backoffice.layout>
