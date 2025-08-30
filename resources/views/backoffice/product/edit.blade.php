<x-backoffice.layout>
<div class="container my-4">
    <h2 class="fw-bold mb-4">Edit Product</h2>

    <form action="{{ route('backoffice.product.update', $product->id) }}" method="POST" enctype="multipart/form-data">
        @method('PUT')
        @csrf
        <div class="row">
            <!-- Left Tabs -->
            <div class="col-md-3">
                <ul class="nav nav-pills flex-column" id="productTabs" role="tablist">
                    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#general" role="tab">General</a></li>
                    <li class="nav-item">
                        @if($product->id)
                            <a class="nav-link" href="#options" data-bs-toggle="tab">Options</a>
                        @else
                            <a class="nav-link disabled" href="#">Options</a>
                        @endif
                    </li>
                </ul>
            </div>

            <!-- Right Tabs -->
            <div class="col-md-9">
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="general" role="tabpanel">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Category</label>
                            <select name="category_id" class="form-select" required>
                                    <option selected value="{{ $product->category->id }}">{{ $product->category->name }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Product Name</label>
                            <input type="text" name="name" value="{{$product->name}}" class="form-control" required>
                            @error('name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>


                        <div class="mb-3">
                            <label class="form-label fw-bold">Alternate Name</label>
                            <input type="text" name="alternate_name" value="{{$product->alternate_name}}" class="form-control">
                            @error('alternate_name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Description</label>
                            <textarea type="text" name="description" class="form-control">{{$product->description}}</textarea>
                            @error('description')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Price</label>
                            <input value="{{$product->price}}" type="number" name="price" step="0.01" class="form-control" required>
                            @error('price')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Goods Price</label>
                            <input value="{{$product->goods_price}}" type="number" name="goods_price" step="0.01" class="form-control">
                            @error('goods_price')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Product Image</label>
                            <input value="{{$product->product_image}}" type="file" name="product_image" class="form-control">
                            @error('product_image')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="featured" value="1" 
                            {{ $product->featured ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold">Featured Product</label>
                            @error('featured')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <!-- Toggle Switch -->
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="toggleOptions">
                            <label class="form-check-label fw-bold" for="toggleOptions">
                                Add option for product?
                            </label>
                        </div>

                        <div class="mt-4 d-flex justify-content-end">
                            <a href="{{ route('backoffice.product.index') }}" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save Edit</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@if ($errors->any())
    <div class="alert alert-danger">
        <strong>There were some problems with your input:</strong>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


            {{-- Form to add product options and values --}}
        @if($product->id)
        <div class="tab-pane fade" id="options">
            
                <h5 class="fw-bold mb-3">Existing Options</h5>
                @if($product->options->count())
                    @foreach($product->options as $option)
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <strong>{{ $option->name }}</strong>
                                <form action="{{ route('backoffice.product.options.destroy', [$product->id, $option->id]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this option?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Delete Option</button>
                                </form>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Value</th>
                                            <th>Price Change</th>
                                            <th width="10%">Action</th>
                                        </tr>
                                    </thead>
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
                                                    <form action="{{ route('backoffice.product.option.value.destroy', [$product->id, $value->id]) }}" method="POST" onsubmit="return confirm('Delete this value?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted">No options assigned yet for this product.</p>
                @endif
            <form action="{{ route('backoffice.product.options.store', $product->id) }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-bold">Option Name</label>
                    <input type="text" name="option_name" class="form-control" placeholder="e.g. Size" required>
                </div>

                <table class="table table-bordered" id="optionValuesTable">
                    <thead>
                        <tr>
                            <th>Value</th>
                            <th>Price Change</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><input type="text" name="values[0][value]" class="form-control" placeholder="Small" required></td>
                            <td><input type="number" step="0.01" name="values[0][price_change]" class="form-control" placeholder="0.00"></td>
                            <td><button type="button" class="btn btn-danger btn-sm removeRow">&times;</button></td>
                        </tr>
                    </tbody>
                    <div class="d-flex justify-content-end mb-3">
                        <button type="button" id="addRow" class="btn btn-success">+ Add Value</button>
                    </div>
                </table>

                <button type="submit" class="btn btn-primer">Save Option</button>
            </form>
        @endif
</div>
</x-backoffice.layout>
