<?php

namespace App\Livewire;

use Livewire\Component;
use Darryldecode\Cart\Facades\CartFacade as Cart;

class CartPage extends Component
{
    public $items = [];
    public $total = 0;
    public $grandTotal = 0;

    public function mount()
    {
        $this->refreshCart();
    }

    public function refreshCart()
    {
        $this->items = Cart::getContent();
        $this->total = Cart::getTotal();
        $this->grandTotal = $this->total; // or add tax/fees here
    }

    public function increaseQty($id)
    {
        Cart::update($id, [
            'quantity' => [
                'relative' => true,
                'value' => 1
            ]
        ]);
        $this->refreshCart();
    }

    public function decreaseQty($id)
    {
        Cart::update($id, [
            'quantity' => [
                'relative' => true,
                'value' => -1
            ]
        ]);
        $this->refreshCart();
    }

    public function removeItem($id)
    {
        Cart::remove($id);
        $this->refreshCart();
    }

    public function clearCart()
    {
        Cart::clear();
        $this->refreshCart();
    }

    public function checkout()
    {
        session()->flash('success', 'Checkout complete!');
        $this->clearCart();
    }

    public function render()
    {
        return view('livewire.cart-page');
    }
}
