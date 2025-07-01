<x-customer.layout>
<div class="section-wrapper">
 <div class="text-black" id="flipbookContainer" style="width: 100%; max-width: 1000px; height: 600px; margin: auto;">
    <div id="book">
      <div class="my-page" data-density="hard">Welcome to Our Premium Rolls Menu</div>

      @php
        $grouped = $products->groupBy('category.name');
      @endphp

      @foreach($grouped as $category => $items)
        @foreach($items->chunk(5) as $chunk)
          <div class="my-page">
            <div class="menu-category">{{ $category }}</div>
            @foreach($chunk as $item)
              <div class="menu-item">
                <img src="{{ asset('storage/' . $item->product_image) }}" alt="{{ $item->name }}">
                <div class="menu-details">
                  <div class="menu-title">
                    <span>{{ $item->name }}</span>
                    <span>${{ number_format($item->price, 2) }}</span>
                  </div>
                  <div class="menu-description">{{ $item->description }}</div>
                </div>
                <button class="add-to-cart">Add</button>
              </div>
            @endforeach
          </div>
        @endforeach
      @endforeach

      <div class="my-page" data-density="hard">Thank you for visiting!</div>
    </div>
  </div>
</div>
</x-customer.layout>
