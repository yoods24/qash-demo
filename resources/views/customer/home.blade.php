<x-customer.layout>
    <div class="justify-content-center" style="background: linear-gradient(to bottom right, #000000, #201200);">
        <section class="section-wrapper">
            <div class="d-flex justify-content-between hero-title w-100 ">
                <div class="d-flex flex-column gap-4 w-50">
                    <h1>Discover the taste of real coffee</h1>
                    <h6 class="text-secondary">We always ready to help by providing the best service for you. <br> We believe a good place to live can make life better.</h6>
                    <a href="{{route('customer.order')}}">
                    <button class="reservation-btn w-50">
                        Order Now
                    </button>
                    </a>
                    <button class="reservation-btn w-50">
                        Takeaway
                    </button>
                </div>
                <div class="ms-5">
                <img class="hero-img" src="http://picsum.photos/seed/4594/1600/1600" alt="img">
                </div>
            </div>
        </section>
    </div>
    <div class="secondary-white">
        <section class="section-wrapper ">
            <div class="mb-5 mt-5 d-flex justify-content-between">  
                <h1 class="text-black">
                    <strong>Careers</strong> 
                </h1>
                <a href="/career" class="reservation-btn text-decoration-none text-center" style="width: 15%;">
                    Learn More 
                </a>
            </div>
            <div class="d-flex justify-content-around text-black gap-5">
                <x-customer.career-card></x-customer.career-card>
                <x-customer.career-card></x-customer.career-card>
                <x-customer.career-card></x-customer.career-card>
                <x-customer.career-card></x-customer.career-card>
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

      <!-- Opening Hours -->
      <div class="col-md-4">
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
