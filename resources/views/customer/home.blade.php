<x-customer.layout>
<button class="about-btn" data-bs-toggle="modal" data-bs-target="#sideModal">
    <i class="bi bi-question"></i>
</button>
  <!-- Side Modal -->
  <div class="modal fade" id="sideModal" tabindex="-1" aria-hidden="true" data-lenis-prevent>
    <div class="modal-dialog modal-side">
      <div class="modal-content" >
        <button type="button" class="side-close-btn" data-bs-dismiss="modal">X</button>
        <div class="modal-body">
          <h3>About Us</h3>
          <div class="text-container">
            In the 1950s, all that stood in this iconic location was a car park with a small kiosk. 
            Even then it was the best spot for uninterrupted panoramic views of Adelaide, with the 
            address a tourist destination in itself. In October 1985, 
            RavoRes as we know it today opened.
          </div>
          <!-- Opening Hours -->
          <div class="">
            <h5 class="section-title text-center">OPENING HOURS</h5>
            <div class="card-style text-center mt-3">
              <div class="d-flex justify-content-between">
                <div class="text-start">
                  <p><strong>SENIN</strong></p>
                  <p><strong>SELASA</strong></p>
                  <p><strong>RABU</strong></p>
                  <p><strong>KAMIS</strong></p>
                  <p><strong>JUMAT</strong></p>
                  <p><strong>SABTU</strong></p>
                  <p><strong>MINGGU</strong></p>
                </div>
                <div class="text-end">
                  <p>16:00 - 22:30</p>
                  <p>16:00 - 22:30</p>
                  <p>16:00 - 22:30</p>
                  <p>16:00 - 22:30</p>
                  <p>16:00 - 22:30</p>
                  <p>16:00 - 22:30</p>
                  <p>16:00 - 22:30</p>
                </div>
              </div>
            </div>
          </div>

          <div class="d-flex flex-column gap-3">
            <h4 class="text-orange">Contact Us</h4>
            <div class="d-flex flex-column gap-1">
              <p class="m-0">No. 120 E 4th Ave, USA</p>
              <p class="m-0">reservation@ravores.com</p>
            </div>

            <div class="d-flex justify-content-between">
              <div>
                <i class="bi bi-telephone">
                  <span class="text-orange"> +62 81281381395</span>
                </i>
              </div>
              <div class="d-flex gap-3 mt-1 social-icons">
                <a href="https://wa.me/1234567890" target="_blank" class="text-dark">
                  <i class="fab fa-whatsapp fa-xl"></i>
                </a>
                <a href="https://instagram.com/yourhandle" target="_blank" class="text-dark">
                  <i class="fab fa-instagram fa-xl"></i>
                </a>
                <a href="https://facebook.com/yourpage" target="_blank" class="text-dark">
                  <i class="fab fa-facebook fa-xl"></i>
                </a>
              </div>
            </div>
          </div>

        </div>

      </div>
    </div>
  </div>

    <div class="justify-content-center" style="background: linear-gradient(to bottom right, #000000, #201200);">
        <section class="section-wrapper">
            <div class="d-flex flex-wrap justify-content-between gap-5 align-items-center w-100">
                <div class="col-12 col-md-6 d-flex flex-column gap-4 hero-title">
                    <h1>Discover the taste of real coffee</h1>
                    <h6 class="text-secondary">
                        We always ready to help by providing the best service for you. <br>
                        We believe a good place to live can make life better.
                    </h6>
                    <a class="col-lg-6" href="{{ route('customer.order') }}">
                        <button class="reservation-btn w-100">
                            Order Now
                        </button>
                    </a>
                    <a class="col-lg-6" href="{{ route('customer.order') }}">
                        <button class="reservation-btn w-100">
                            Takeaway
                        </button>
                    </a>
                </div>
                <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12 mt-3 mt-md-0">
                    <img class="hero-img" src="http://picsum.photos/seed/4594/1600/1600" alt="img">
                </div>
            </div>
        </section>
    </div>

@php
    /** @var \Illuminate\Support\Collection|\App\Models\Category[] $menuCategories */
    $menuCategories = $menuCategories ?? collect();
@endphp

<div class="secondary-white">
  <section class="section-wrapper menu-section text-black">
    <div class="d-flex flex-column gap-4">
      <div class="d-flex justify-content-between align-items-end flex-wrap gap-3">
        <div class="flex-grow-1">
          <p class="primer bold mb-1">O U R &nbsp; M E N U</p>
          <h2 class="fw-bold mb-0">Discover the Taste of Real Coffee</h2>
        </div>
        <div class="d-none d-md-flex gap-3">
          <a type="button" href="{{ route('customer.order') }}" class="btn btn-outline-main">Takeaway</a>
          <a href="{{ route('customer.order') }}" class="btn btn-main">Order Now</a>
        </div>
      </div>

      <div class="d-flex d-md-none flex-column gap-2 mt-2">
        <a href="{{ route('customer.order') }}" class="btn btn-main w-100">Order Now</a>
        <button class="btn reservation-btn w-100">Takeaway</button>
      </div>

      @if($menuCategories->isNotEmpty())
        @php
            $firstCategoryId = optional($menuCategories->first())->id;
        @endphp
        <div class="menu-category-row d-flex flex-wrap gap-3 mb-4">
          @foreach($menuCategories as $category)
            <button
              type="button"
              class="menu-category-card {{ $category->id === $firstCategoryId ? 'active' : '' }}"
              data-menu-category="{{ $category->id }}"
            >
              <span>{{ strtoupper($category->name) }}</span>
            </button>
          @endforeach
        </div>

        <div id="menuCategoryPanels" class="menu-panels">
          @foreach($menuCategories as $category)
            @php
                $products = $category->products ?? collect();
                $featured = $products->isNotEmpty()
                    ? $products->shuffle()->first()
                    : null;
                $columns = $products->values()->chunk(
                    $products->count() > 0 ? (int) ceil($products->count() / 2) : 1
                );
            @endphp
            <div
              class="menu-category-panel {{ $category->id === $firstCategoryId ? 'active' : '' }}"
              data-menu-panel="{{ $category->id }}"
            >
              <div class="row g-4 align-items-stretch">
                <div class="col-md-5">
                  <div class="menu-featured-wrapper">
                    @if($featured && $featured->product_image)
                      <img
                        src="{{ asset('storage/' . $featured->product_image) }}"
                        alt="{{ $featured->name }}"
                        class="menu-featured-img"
                      >
                    @else
                      <img
                        src="http://picsum.photos/seed/{{ $category->id }}/800/800"
                        alt="{{ $category->name }}"
                        class="menu-featured-img"
                      >
                    @endif
                  </div>
                </div>
                <div class="col-md-7">
                  <div class="menu-items-card">
                    <div class="row">
                      @foreach($columns as $chunk)
                        <div class="col-md-6">
                          @foreach($chunk as $product)
                            <div class="menu-item-row">
                              <div class="menu-item-header d-flex justify-content-between">
                                <span class="menu-item-name">{{ $product->name }}</span>
                                <span class="menu-item-leader"></span>
                                @if(!is_null($product->price))
                                  <span class="menu-item-price">
                                    {{ number_format($product->price, 0, ',', '.') }}
                                  </span>
                                @endif
                              </div>
                              @if($product->description)
                                <p class="menu-item-desc mb-1">{{ $product->description }}</p>
                              @endif
                            </div>
                          @endforeach
                        </div>
                      @endforeach
                    </div>
                  </div>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      @else
        <div class="alert alert-light mt-4" role="alert">
          Menu is coming soon. Please check back later.
        </div>
      @endif
    </div>
  </section>
</div>


    <section class="bg-black">
        <div class="section-wrapper mt-5">
            <div class="d-flex flex-column align-items-center mb-2 text-center">
                <p class="primer bold">G A L L E R Y</p>
                <h4>Kasumba</h4>
            </div>
            <div class="mx-auto carousel-wrapper">
            <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-indicators">
                    <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                    <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1" aria-label="Slide 2"></button>
                    <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2" aria-label="Slide 3"></button>
                </div>
                <div class="carousel-inner rounded">
                    <div class="carousel-item active" data-bs-interval="3000">
                        <img src="http://picsum.photos/seed/{{ rand(0, 10000) }}/1600/1600" class="d-block w-100 carousel-img" alt="...">
                    </div>
                    <div class="carousel-item" data-bs-interval="3000">
                        <img src="http://picsum.photos/seed/{{ rand(0, 10000) }}/1600/1600" class="d-block w-100 carousel-img" alt="...">
                    </div>
                    <div class="carousel-item" data-bs-interval="3000">
                        <img src="http://picsum.photos/seed/{{ rand(0, 10000) }}/1600/1600" class="d-block w-100 carousel-img" alt="...">
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
            </div>
        </div>
    </section>


<div class="secondary-white">
<section class="location-section py-5 text-black">
  <div class="container">
    <div class="row g-4">
      
      <!-- Map Section -->
      <div class="col-md-4">
        <h5 class="section-title text-center">WHERE ARE WE?</h5>
        <div class="card-style mt-3">
          <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d31693.789227843917!2d107.5790603!3d-6.8914796!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e68e7d4f8e80c75%3A0x5f785c3ebf1efac3!2sBandung!5e0!3m2!1sen!2sid!4v1716000000000!5m2!1sen!2sid"
            width="100%" height="300" style="border:0;" allowfullscreen=""
            loading="lazy" referrerpolicy="no-referrer-when-downgrade">
          </iframe>
        </div>
      </div>

      <!-- Contact Info -->
      <div class="col-md-4">
        <h5 class="section-title text-center">GET IN TOUCH</h5>
        <div class="card-style mt-3">
          <div class="d-flex justify-content-between">
            <div>
              <p><strong>ADDRESS</strong></p>
              <p><strong>PHONE</strong></p>
              <p><strong>EMAIL</strong></p>
              <p><strong>FOLLOW</strong></p>
            </div>
            <div>
              <p>23 CISATU BLOK F GACOR</p>
              <p>+62 8124 9571</p>
              <p>test@gmail.com</p>
              <div class="d-flex gap-3 mt-1 social-icons">
                <a href="https://wa.me/1234567890" target="_blank" class="text-dark">
                  <i class="fab fa-whatsapp fa-xl"></i>
                </a>
                <a href="https://instagram.com/yourhandle" target="_blank" class="text-dark">
                  <i class="fab fa-instagram fa-xl"></i>
                </a>
                <a href="https://facebook.com/yourpage" target="_blank" class="text-dark">
                  <i class="fab fa-facebook fa-xl"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
</section>
</div>
</x-customer.layout>
