<x-backoffice.layout>
    <div>
    <div class="row">
        <div class="col-12 mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold mb-1">Discounts</h2>
                    <p class="text-muted mb-0">Plan promo rules, limit availability, and sync them with your events.</p>
                </div>
                <div class="action-buttons d-flex gap-2">
                    <button type="button" class="btn btn-add" data-bs-toggle="modal" data-bs-target="#discountCreateModal">
                        Add Discount
                    </button>
                </div>
            </div>
            @if (session('success'))
                <div class="alert alert-success mt-3 mb-0">
                    {{ session('success') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger mt-3 mb-0">
                    <div class="fw-semibold mb-1">Please fix the following:</div>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body">
                    @livewire('backoffice.tables.discount-table')
                </div>
            </div>
        </div>
    </div>

    @php
        $daysList = [
            'monday' => 'Monday',
            'tuesday' => 'Tuesday',
            'wednesday' => 'Wednesday',
            'thursday' => 'Thursday',
            'friday' => 'Friday',
            'saturday' => 'Saturday',
            'sunday' => 'Sunday',
        ];
        $oldDays = old('days', []);
        $oldProducts = collect(old('products', []))->map(fn($value) => (int) $value)->all();
    @endphp

    <x-modal.create 
        id="discountCreateModal" 
        title="Add Discount" 
        action="{{ route('backoffice.discounts.store', ['tenant' => $tenantParam]) }}"
        submit-label="Add Discount"
    >
        <div class="row g-3">
            <div class="col-md-4">
                <label for="discount-name" class="form-label fw-semibold">Discount Name <span class="text-danger">*</span></label>
                <input 
                    type="text" 
                    class="form-control" 
                    id="discount-name" 
                    name="name" 
                    value="{{ old('name') }}" 
                    placeholder="New member Monday"
                    required
                >
            </div>
            <div class="col-md-4">
                <label for="discount-plan" class="form-label fw-semibold">Discount Plan <span class="text-danger">*</span></label>
                <select class="form-select" id="discount-plan" name="quantity_type" required>
                    <option value="" disabled {{ old('quantity_type') ? '' : 'selected' }}>Select plan</option>
                    <option value="unlimited" @selected(old('quantity_type', 'unlimited') === 'unlimited')>Unlimited</option>
                    <option value="decrement" @selected(old('quantity_type') === 'decrement')>Decrement (per quantity)</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="discount-applicable-for" class="form-label fw-semibold">Applicable For <span class="text-danger">*</span></label>
                <select class="form-select" id="discount-applicable-for" name="applicable_for" required>
                    <option value="" disabled {{ old('applicable_for') ? '' : 'selected' }}>Select option</option>
                    <option value="all" @selected(old('applicable_for', 'all') === 'all')>All Products</option>
                    <option value="specific" @selected(old('applicable_for') === 'specific')>Specific Products</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="discount-valid-from" class="form-label fw-semibold">Valid From <span class="text-danger">*</span></label>
                <input 
                    type="date" 
                    class="form-control" 
                    id="discount-valid-from" 
                    name="valid_from" 
                    value="{{ old('valid_from') }}"
                    required
                >
            </div>
            <div class="col-md-6">
                <label for="discount-valid-till" class="form-label fw-semibold">Valid Till <span class="text-danger">*</span></label>
                <input 
                    type="date" 
                    class="form-control" 
                    id="discount-valid-till" 
                    name="valid_till" 
                    value="{{ old('valid_till') }}"
                    required
                >
            </div>
            <div class="col-md-4">
                <label for="discount-type" class="form-label fw-semibold">Discount Type <span class="text-danger">*</span></label>
                <select class="form-select" id="discount-type" name="discount_type" required>
                    <option value="" disabled {{ old('discount_type') ? '' : 'selected' }}>Select type</option>
                    <option value="flat" @selected(old('discount_type', 'flat') === 'flat')>Flat</option>
                    <option value="percent" @selected(old('discount_type') === 'percent')>Percent</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="discount-value" class="form-label fw-semibold">Value <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input 
                        type="number" 
                        class="form-control" 
                        id="discount-value" 
                        name="value" 
                        value="{{ old('value') }}" 
                        step="0.01" 
                        min="0"
                        placeholder="Enter amount"
                        required
                    >
                    <span class="input-group-text" id="discount-value-suffix">{{ old('discount_type', 'flat') === 'percent' ? '%' : 'IDR' }}</span>
                </div>
            </div>
            <div class="col-md-4">
                <label for="discount-status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                <select class="form-select" id="discount-status" name="status" required>
                    <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                </select>
            </div>

            <div class="col-12 {{ old('applicable_for', 'all') === 'specific' ? '' : 'd-none' }}" id="discount-products-wrapper">
                <label for="discount-products" class="form-label fw-semibold">Select Products <span class="text-danger">*</span></label>
                <select 
                    multiple 
                    class="form-select" 
                    id="discount-products" 
                    name="products[]"
                >
                    @foreach ($productOptions as $product)
                        <option value="{{ $product['id'] }}" @selected(in_array($product['id'], $oldProducts, true))>
                            {{ $product['name'] }}
                        </option>
                    @endforeach
                </select>
                <div class="form-text">Hold CTRL (or CMD on Mac) to select multiple products.</div>
            </div>

            <div class="col-md-4 {{ old('quantity_type', 'unlimited') === 'decrement' ? '' : 'd-none' }}" id="discount-quantity-wrapper">
                <label for="discount-quantity" class="form-label fw-semibold">Quantity</label>
                <input 
                    type="number" 
                    class="form-control" 
                    id="discount-quantity" 
                    name="quantity" 
                    value="{{ old('quantity') }}" 
                    min="1"
                >
            </div>

            <div class="col-12">
                <label class="form-label fw-semibold d-block mb-2">Valid on Following Days <span class="text-danger">*</span></label>
                <div class="d-flex flex-wrap gap-3">
                    @foreach ($daysList as $key => $label)
                        <div class="form-check">
                            <input 
                                class="form-check-input" 
                                type="checkbox" 
                                value="{{ $key }}" 
                                id="day-{{ $key }}" 
                                name="days[]" 
                                @checked(in_array($key, $oldDays, true))
                            >
                            <label class="form-check-label" for="day-{{ $key }}">
                                {{ $label }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </x-modal.create>

    <x-modal.update
        id="discountUpdateModal"
        title="Update Discount"
        :action="route('backoffice.discounts.update', ['tenant' => $tenantParam, 'discount' => '__discount__'])"
        submit-label="Save Changes"
    >
        <div class="row g-3">
            <div class="col-md-4">
                <label for="discount-update-name" class="form-label fw-semibold">Discount Name <span class="text-danger">*</span></label>
                <input
                    type="text"
                    class="form-control"
                    id="discount-update-name"
                    name="name"
                    placeholder="New member Monday"
                    required
                >
            </div>
            <div class="col-md-4">
                <label for="discount-update-plan" class="form-label fw-semibold">Discount Plan <span class="text-danger">*</span></label>
                <select class="form-select" id="discount-update-plan" name="quantity_type" required>
                    <option value="" disabled selected>Select plan</option>
                    <option value="unlimited">Unlimited</option>
                    <option value="decrement">Decrement (per quantity)</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="discount-update-applicable-for" class="form-label fw-semibold">Applicable For <span class="text-danger">*</span></label>
                <select class="form-select" id="discount-update-applicable-for" name="applicable_for" required>
                    <option value="" disabled selected>Select option</option>
                    <option value="all">All Products</option>
                    <option value="specific">Specific Products</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="discount-update-valid-from" class="form-label fw-semibold">Valid From <span class="text-danger">*</span></label>
                <input
                    type="date"
                    class="form-control"
                    id="discount-update-valid-from"
                    name="valid_from"
                    required
                >
            </div>
            <div class="col-md-6">
                <label for="discount-update-valid-till" class="form-label fw-semibold">Valid Till <span class="text-danger">*</span></label>
                <input
                    type="date"
                    class="form-control"
                    id="discount-update-valid-till"
                    name="valid_till"
                    required
                >
            </div>
            <div class="col-md-4">
                <label for="discount-update-type" class="form-label fw-semibold">Discount Type <span class="text-danger">*</span></label>
                <select class="form-select" id="discount-update-type" name="discount_type" required>
                    <option value="flat">Flat (IDR)</option>
                    <option value="percent">Percent (%)</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="discount-update-value" class="form-label fw-semibold">Discount Value <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input
                        type="number"
                        class="form-control"
                        id="discount-update-value"
                        name="value"
                        step="0.01"
                        min="0"
                        placeholder="Enter amount"
                        required
                    >
                    <span class="input-group-text" id="discount-update-value-suffix">IDR</span>
                </div>
            </div>
            <div class="col-md-4">
                <label for="discount-update-status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                <select class="form-select" id="discount-update-status" name="status" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div class="col-12 d-none" id="discount-update-products-wrapper">
                <label for="discount-update-products" class="form-label fw-semibold">Select Products <span class="text-danger">*</span></label>
                <select
                    multiple
                    class="form-select"
                    id="discount-update-products"
                    name="products[]"
                >
                    @foreach ($productOptions as $product)
                        <option value="{{ $product['id'] }}">
                            {{ $product['name'] }}
                        </option>
                    @endforeach
                </select>
                <div class="form-text">Hold CTRL (or CMD on Mac) to select multiple products.</div>
            </div>

            <div class="col-md-4 d-none" id="discount-update-quantity-wrapper">
                <label for="discount-update-quantity" class="form-label fw-semibold">Quantity</label>
                <input
                    type="number"
                    class="form-control"
                    id="discount-update-quantity"
                    name="quantity"
                    min="1"
                >
            </div>

            <div class="col-12">
                <label class="form-label fw-semibold d-block mb-2">Valid on Following Days <span class="text-danger">*</span></label>
                <div class="d-flex flex-wrap gap-3" id="discount-update-days">
                    @foreach ($daysList as $key => $label)
                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                value="{{ $key }}"
                                id="discount-update-day-{{ $key }}"
                                name="days[]"
                            >
                            <label class="form-check-label" for="discount-update-day-{{ $key }}">
                                {{ $label }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </x-modal.update>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const applicableSelect = document.getElementById('discount-applicable-for');
            const productsWrapper = document.getElementById('discount-products-wrapper');
            const planSelect = document.getElementById('discount-plan');
            const quantityWrapper = document.getElementById('discount-quantity-wrapper');
            const discountTypeSelect = document.getElementById('discount-type');
            const valueSuffix = document.getElementById('discount-value-suffix');

            const toggleProducts = () => {
                if (applicableSelect.value === 'specific') {
                    productsWrapper?.classList.remove('d-none');
                } else {
                    productsWrapper?.classList.add('d-none');
                }
            };

            const toggleQuantity = () => {
                if (planSelect.value === 'decrement') {
                    quantityWrapper?.classList.remove('d-none');
                } else {
                    quantityWrapper?.classList.add('d-none');
                }
            };

            const updateValueSuffix = () => {
                valueSuffix.textContent = discountTypeSelect.value === 'percent' ? '%' : 'IDR';
            };

            applicableSelect?.addEventListener('change', toggleProducts);
            planSelect?.addEventListener('change', toggleQuantity);
            discountTypeSelect?.addEventListener('change', updateValueSuffix);

            toggleProducts();
            toggleQuantity();
            updateValueSuffix();
        });
    </script>
</div>

</x-backoffice.layout>
