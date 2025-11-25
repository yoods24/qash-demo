<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use App\Models\Category;
use Darryldecode\Cart\Facades\CartFacade as Cart;
use Illuminate\Support\Facades\Session;
use App\Services\OrderService;
use App\Support\PriceAfterDiscount;
use App\Services\Customer\OrderPage\OrderPageCartService;
use App\Services\Customer\OrderPage\OrderPageCustomerService;
use App\Services\Customer\OrderPage\OrderPageInitializer;
use App\Services\Customer\OrderPage\OrderPageOrderTypeService;
use App\Services\Customer\OrderPage\OrderPageProductService;

class OrderPage extends Component
{
    public $categories;
    public $selectedCategory = 'all';
    public $selectedProduct = null;
    public $selectedOptions = [];
    public $quantity = 1;
    public $showOptionModal = false;
    public $showCustomerModal = false;
    public $showTableModal = false;
    public $note = '';
    public $selectedProductSoldOut = false;
    public $customerName = '';
    public $customerEmail = '';
    public $customerGender = null; // 'man' | 'women' | null
    public $username = null;
    public $pendingAction = null; // 'add_to_cart' | 'update_profile' | null
    public $tenantId = null;   // current tenant id
    public $tenantName = null; // display-only name
    public $currentTable = null; // label for current dining table
    public $availableDiscounts;
    public string $orderType = 'takeaway';
    public bool $showSwitchOrderTypeModal = false;
    public ?string $pendingOrderType = null;

    public function mount(
        OrderPageInitializer $initializer,
        OrderPageOrderTypeService $orderTypeService
    ): void {
        $initializer->initialize($this);
        $orderTypeService->syncCurrentTable($this);
    }

    public function filterCategory($categoryId): void
    {
        $this->selectedCategory = $categoryId;

        if ($this->selectedCategory !== 'all' && ! Category::find($categoryId)) {
            $this->selectedCategory = 'all';
        }

        $this->dispatch('categoryUpdated');
    }

    public function showProductOptions(int $productId, OrderPageProductService $productService): void
    {
        $productService->prepareSelectedProduct($this, $productId);
    }

    public function incrementQuantity(): void
    {
        if ($this->selectedProductSoldOut) {
            return;
        }

        $this->quantity++;
    }

    public function decrementQuantity(): void
    {
        if ($this->selectedProductSoldOut) {
            return;
        }

        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    public function addSelectedProductToCart(OrderPageCartService $cartService): void
    {
        if (! $this->selectedProduct || $this->selectedProductSoldOut) {
            return;
        }

        if ($this->orderType === OrderService::DINE_IN && ! Session::get('dining_table_id')) {
            $this->showTableModal = true;
            return;
        }

        if (! Session::has('customer_detail_id')) {
            $this->pendingAction = 'add_to_cart';
            $this->showCustomerModal = true;
            return;
        }

        $cartService->addSelectedProductToCart($this);
    }

    public function closeTableModal(): void
    {
        $this->showTableModal = false;
    }

    public function cancelCustomerModal(): void
    {
        $this->showCustomerModal = false;
    }

    public function saveCustomer(
        OrderPageCustomerService $customerService,
        OrderPageOrderTypeService $orderTypeService,
        OrderPageCartService $cartService
    ): void {
        $customerService->saveCustomer($this);

        if ($this->pendingAction === 'add_to_cart') {
            $cartService->addSelectedProductToCart($this);
        } else {
            session()->flash('success', 'Details updated.');
        }

        $this->pendingAction = null;
        $orderTypeService->syncCurrentTable($this);
    }

    public function openCustomerEdit(OrderPageCustomerService $customerService): void
    {
        $customerService->populateCustomerForEdit($this);
    }

    public function selectOrderType(string $type, OrderPageOrderTypeService $orderTypeService): void
    {
        $orderTypeService->selectOrderType($this, $type);
    }

    public function confirmOrderTypeSwitch(OrderPageOrderTypeService $orderTypeService): void
    {
        $orderTypeService->confirmOrderTypeSwitch($this);
    }

    public function cancelOrderTypeSwitch(OrderPageOrderTypeService $orderTypeService): void
    {
        $orderTypeService->cancelOrderTypeSwitch($this);
    }

    public function closeOptionModal(): void
    {
        $this->showOptionModal = false;
        $this->selectedProductSoldOut = false;
        $this->quantity = 1;
        $this->dispatch('unlock-scroll');
    }

    public function getTotalPriceProperty()
    {
        $pricing = $this->selectedProductPriceInfo;

        return $pricing['unit_final'] * $this->quantity;
    }

    public function getSelectedProductPriceInfoProperty(): array
    {
        if (! $this->selectedProduct) {
            return [
                'unit_raw' => 0.0,
                'unit_final' => 0.0,
                'has_discount' => false,
                'badge' => null,
                'discount_name' => null,
            ];
        }

        $productService = app(OrderPageProductService::class);
        $unitPrice = $productService->computeUnitPrice($this->selectedProduct, $this->selectedOptions);

        $pricing = PriceAfterDiscount::calculate(
            $this->selectedProduct,
            $this->resolveTenantId(),
            $unitPrice
        );

        return [
            'unit_raw' => $unitPrice,
            'unit_final' => $pricing['final_price'],
            'has_discount' => $pricing['has_discount'],
            'badge' => $pricing['badge'],
            'discount_name' => $pricing['discount_name'],
        ];
    }

    public function render()
    {
        $productService = app(OrderPageProductService::class);
        $tenantId = $this->resolveTenantId();

        $productsForView = $this->selectedCategory === 'all'
            ? $productService->loadAllGrouped($tenantId)
            : $productService->loadByCategory($tenantId, $this->selectedCategory);

        $featured = $productService->loadFeatured($tenantId);

        return view('livewire.customer.order-page', [
            'products'           => $productsForView,
            'featuredProducts'   => $featured,
            'cartTotal'          => Cart::getTotal(),
            'cartQuantity'       => Cart::getTotalQuantity(),
            'availableDiscounts' => $this->availableDiscounts,
        ]);
    }

    private function resolveTenantId(): ?string
    {
        return tenant()?->id ?? $this->tenantId ?? request()->route('tenant');
    }
}
