<x-backoffice.layout>
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Edit Product</h4>
            <div class="text-muted small">Update product details</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('backoffice.product.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="collapse" data-bs-target=".multi-collapse" title="Toggle all sections"><i class="bi bi-arrows-collapse"></i></button>
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

    <form action="{{ route('backoffice.product.update', $product->id) }}" method="POST" enctype="multipart/form-data" class="d-grid gap-3">
        @csrf
        @method('PUT')
        <!-- Section 1: Basic Info -->
        <div class="card">
            <div class="d-flex justify-content-between align-items-center p-3 cursor-pointer" data-bs-toggle="collapse" data-bs-target="#sec-basic">
                <div class="fw-bold"><i class="bi bi-basket me-2 text-warning"></i> Basic Info</div>
                <i class="bi bi-arrow-down"></i>
            </div>
            <hr class="m-0">
            <div id="sec-basic" class="collapse multi-collapse show">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Product Name</label>
                            <input type="text" name="name" class="form-control" value="{{ $product->name }}" required>
                            @error('name')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Alternate Name</label>
                            <input type="text" name="alternate_name" class="form-control" value="{{ $product->alternate_name }}">
                            @error('alternate_name')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Category</label>
                            <select name="category_id" class="form-select" required>
                                <option selected value="{{ $product->category->id }}">{{ $product->category->name }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="active" value="1" {{ ($product->active ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold">Active (visible to customers)</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Description</label>
                            <textarea name="description" class="form-control" rows="3">{{ $product->description }}</textarea>
                            @error('description')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Product Image</label>
                            <input type="file" name="product_image" class="form-control">
                            @error('product_image')<small class="text-danger">{{ $message }}</small>@enderror
                            @if($product->product_image)
                                <div class="mt-2"><img src="{{ $product->product_image_url ?? 'https://via.placeholder.com/64' }}" alt="Current image" style="height:64px; border-radius:8px"></div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Estimated Time (minutes)</label>
                            <input type="number" min="0" step="1" name="estimated_minutes" class="form-control" value="{{ (int) floor(($product->estimated_seconds ?? 0) / 60) }}">
                            <div class="form-text">Average time to prepare one unit.</div>
                            @error('estimated_minutes')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Options (existing + add new) -->
        <div class="card">
            <div class="d-flex justify-content-between align-items-center p-3 cursor-pointer" data-bs-toggle="collapse" data-bs-target="#sec-options">
                <div class="fw-bold"><i class="bi bi-sliders me-2 text-warning"></i> Options</div>
                <i class="bi bi-arrow-down"></i>
            </div>
            <hr class="m-0">
            <div id="sec-options" class="collapse multi-collapse">
                <div class="card-body">
                    <h6 class="fw-bold mb-2">Existing Options</h6>
                    @if($product->options->count())
                        @foreach($product->options as $option)
                            <div class="card mb-3">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <strong>{{ $option->name }}</strong>
                                    <button
                                        type="submit"
                                        class="btn btn-sm btn-outline-danger"
                                        form="delete-option-{{ $option->id }}"
                                        onclick="return confirm('Delete this option?');"
                                    >
                                        Delete Option
                                    </button>
                                </div>
                                <div class="card-body p-3">
                                    <table class="table table-sm mb-0">
                                        <thead><tr><th>Value</th><th>Price Change</th><th width="10%">Action</th></tr></thead>
                                        <tbody>
                                            @foreach($option->values as $value)
                                                <tr>
                                                    <td>{{ $value->value }}</td>
                                                    <td>
                                                        @if($value->price_adjustment > 0)
                                                            +{{ number_format($value->price_adjustment, 2) }}
                                                        @elseif($value->price_adjustment < 0)
                                                            {{ number_format($value->price_adjustment, 2) }}
                                                        @else
                                                            No Change
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <button
                                                            type="submit"
                                                            class="btn btn-sm btn-outline-danger"
                                                            form="delete-option-value-{{ $value->id }}"
                                                            onclick="return confirm('Delete this value?');"
                                                        >
                                                            Delete
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted">No options yet.</p>
                    @endif
                    <hr>
                    <h6 class="fw-bold mb-2">Add New Option</h6>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Option Name</label>
                            <input type="text" name="option_name" class="form-control" placeholder="e.g. Size">
                            @error('option_name')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <table class="table table-bordered" id="optionValuesTable">
                            <thead><tr><th>Value</th><th>Price Change</th><th></th></tr></thead>
                            <tbody>
                                <tr>
                                    <td><input type="text" name="values[0][value]" class="form-control" placeholder="Small"></td>
                                    <td><input type="number" step="0.01" name="values[0][price_change]" class="form-control" placeholder="0.00"></td>
                                    <td><button type="button" class="btn btn-outline-danger btn-sm removeRow">&times;</button></td>
                                </tr>
                            </tbody>
                        </table>
                        @if($errors->first('values.*.value'))
                            <div class="text-danger small">{{ $errors->first('values.*.value') }}</div>
                        @endif
                        <div class="d-flex justify-content-end mb-3">
                            <button type="button" id="addRow" class="btn btn-success">+ Add Value</button>
                        </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Pricing & Stocks -->
        <div class="card my-3">
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
                            <input value="{{ $product->price }}" type="number" name="price" step="0.01" class="form-control" required>
                            @error('price')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Goods Price</label>
                            <input value="{{ $product->goods_price }}" type="number" name="goods_price" step="0.01" class="form-control">
                            @error('goods_price')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Stock Quantity</label>
                            <input value="{{ $product->stock_qty ?? 0 }}" type="number" min="0" name="stock_qty" class="form-control">
                            @error('stock_qty')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('backoffice.product.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-main">Save Changes</button>
        </div>
    </form>
    @foreach ($product->options as $option)
        <form id="delete-option-{{ $option->id }}" action="{{ route('backoffice.product.options.destroy', [$product->id, $option->id]) }}" method="POST" class="d-none">
            @csrf
            @method('DELETE')
        </form>
        @foreach ($option->values as $value)
            <form id="delete-option-value-{{ $value->id }}" action="{{ route('backoffice.product.option.value.destroy', [$product->id, $value->id]) }}" method="POST" class="d-none">
                @csrf
                @method('DELETE')
            </form>
        @endforeach
    @endforeach
</div>
</x-backoffice.layout>
