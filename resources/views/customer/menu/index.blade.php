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
                <button class="add-to-cart">
                  <i class="bi bi-cart-fill"></i>
                </button>
              </div>
            @endforeach
          </div>
        @endforeach
      @endforeach

      <div class="my-page" data-density="hard">Thank you for visiting!</div>
    </div>
  </div>
</div>

<script>
  // book menu
document.addEventListener("DOMContentLoaded", function () {
    const pageFlip = new PageFlip(document.getElementById('book'), {
        width: 500, // required parameter - base page width
        height: 600, // required parameter - base page height
    });

    pageFlip.loadFromHTML(document.querySelectorAll('.my-page'));
});
</script>
</x-customer.layout>
