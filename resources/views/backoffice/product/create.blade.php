<x-backoffice.layout>
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Add Product</h4>
            <div class="text-muted small">Create a new product</div>
        </div>
        <div>
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="collapse" data-bs-target=".multi-collapse"><i class="bi bi-arrows-collapse"></i></button>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <div class="fw-semibold mb-1">Please correct the following:</div>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('backoffice.product.store') }}" method="POST" enctype="multipart/form-data" class="d-grid gap-3">
        @csrf

        <!-- Section 1: Basic Info -->
        <div class="card">
            <div class="d-flex justify-content-between align-items-center p-3 cursor-pointer" data-bs-toggle="collapse" data-bs-target="#sec-basic">
                <div class="fw-bold"><i class="bi bi-basket me-2 text-warning"></i> Basic Info</div>
                <i class="bi bi-arrow-down"></i>
            </div>
            <hr class="m-0">
            <div id="sec-basic" class="collapse multi-collapse">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Product Name</label>
                            <input type="text" name="name" class="form-control" required>
                            @error('name')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Alternate Name</label>
                            <input type="text" name="alternate_name" class="form-control">
                            @error('alternate_name')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Category</label>
                            <select name="category_id" class="form-select" required>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="active" value="1" checked>
                                <label class="form-check-label fw-bold">Active (visible to customers)</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Description</label>
                            <textarea name="description" class="form-control" rows="3" required></textarea>
                            @error('description')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Product Image</label>
                            <input type="file" name="product_image" class="form-control">
                            @error('product_image')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Estimated Time (minutes)</label>
                            <input type="number" min="0" step="1" name="estimated_minutes" class="form-control" placeholder="e.g. 3">
                            <div class="form-text">Average time to prepare one unit.</div>
                            @error('estimated_minutes')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Options -->
        <div class="card">
            <div class="d-flex justify-content-between align-items-center p-3 cursor-pointer" data-bs-toggle="collapse" data-bs-target="#sec-options">
                <div class="fw-bold"><i class="bi bi-sliders me-2 text-warning"></i> Options</div>
                <i class="bi bi-arrow-down"></i>
            </div>
            <hr class="m-0">
            <div id="sec-options" class="collapse multi-collapse">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="fw-semibold">Option Groups</div>
                        <button type="button" id="addOptionGroup" class="btn btn-sm btn-success">+ Add Option</button>
                    </div>
                    <div id="optionsGroups" class="d-grid gap-3">
                        <!-- Option Group Template (first group) -->
                        <div class="option-group border rounded p-3" data-index="0" data-next-value-index="1">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Option Name</label>
                                    <input type="text" name="options[0][name]" class="form-control" placeholder="e.g. Size">
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input toggle-default-only" type="checkbox" name="options[0][default_only]" value="1" id="opt0DefaultOnly">
                                        <label class="form-check-label" for="opt0DefaultOnly">Default only</label>
                                    </div>
                                </div>
                                <div class="col-md-2 text-end">
                                    <!-- Remove button visible on added groups via JS -->
                                </div>
                            </div>

                            <div class="default-wrap mt-2" style="display:none;">
                                <label class="form-label">Default value</label>
                                <input type="text" class="form-control" name="options[0][default_value]" placeholder="Default" value="Default">
                                <div class="form-text">When default only is on, only this single value is used.</div>
                            </div>

                            <div class="values-wrap mt-2">
                                <table class="table table-bordered mb-2 option-values-table">
                                    <thead>
                                        <tr>
                                            <th>Value</th>
                                            <th>Price Change</th>
                                            <th width="10%"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><input type="text" name="options[0][values][0][value]" class="form-control" placeholder="Small"></td>
                                            <td><input type="number" step="0.01" name="options[0][values][0][price_change]" class="form-control" placeholder="0.00"></td>
                                            <td><button type="button" class="btn btn-outline-danger btn-sm removeRow">&times;</button></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="text-end pt-2">
                                    <button type="button" class="btn btn-sm btn-outline-success add-value ">+ Add Value</button>
                                </div>
                            </div>
                            @if($errors->first('options.*.name'))
                                <div class="text-danger small mt-2">{{ $errors->first('options.*.name') }}</div>
                            @endif
                            @if($errors->first('options.*.values.*.value'))
                                <div class="text-danger small">{{ $errors->first('options.*.values.*.value') }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Pricing & Stocks -->
        <div class="card">
            <div class="d-flex justify-content-between align-items-center p-3 cursor-pointer" data-bs-toggle="collapse" data-bs-target="#sec-pricing">
                <div class="fw-bold"><i class="bi bi-cash-stack me-2 text-warning"></i> Pricing & Stocks</div>
                <i class="bi bi-arrow-down"></i>
            </div>
            <hr class="m-0">
            <div id="sec-pricing" class="collapse multi-collapse">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Price</label>
                            <input type="number" name="price" step="0.01" class="form-control" required>
                            @error('price')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Goods Price</label>
                            <input type="number" name="goods_price" step="0.01" class="form-control">
                            @error('goods_price')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Stock Quantity</label>
                            <input type="number" min="0" name="stock_qty" class="form-control" value="0">
                            @error('stock_qty')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('backoffice.product.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-main">Create Product</button>
        </div>
    </form>
</div>
</x-backoffice.layout>
