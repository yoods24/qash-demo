<x-customer.layout>
  <section class="secondary-white">
    <div class="section-wrapper text-black">
      <div class="menu-choice-hero text-center text-white mb-5 rounded-4">
        <p class="primer bold mb-2 text-uppercase">CHOOSE YOUR MENU</p>
        <h2 class="fw-bold mb-2 text-white">Pick how you'd like to browse</h2>
        <p class="text-light mb-0">Switch between the classic flipbook and a clean grid view.</p>
      </div>

      <div class="row row-cols-1 row-cols-md-2 g-4">
        <div class="col">
          <a href="{{ route('customer.menu.book') }}" class="card h-100 shadow-sm border-0 text-decoration-none text-black menu-choice-card">
            <div class="card-body d-flex flex-column gap-3 p-4">
              <div class="d-flex align-items-center gap-3">
                <span class="badge bg-dark text-white rounded-circle" style="width: 42px; height: 42px; display: inline-flex; align-items: center; justify-content: center;">
                  <i class="fa-solid fa-book-open fs-5"></i>
                </span>
                <div>
                  <p class="m-0 text-uppercase small text-muted">Classic</p>
                  <h4 class="m-0 fw-bold">Book Menu</h4>
                </div>
              </div>
              <p class="text-muted mb-0">Flip through pages with the immersive book-style menu you already know.</p>
              <div class="d-flex align-items-center justify-content-between">
                <span class="text-secondary">Open the flipbook</span>
                <i class="fa-solid fa-arrow-right menu-choice-arrow"></i>
              </div>
            </div>
          </a>
        </div>

        <div class="col">
          <a href="{{ route('customer.menu.grid') }}" class="card h-100 shadow-sm border-0 text-decoration-none text-black menu-choice-card">
            <div class="card-body d-flex flex-column gap-3 p-4">
              <div class="d-flex align-items-center gap-3">
                <span class="badge bg-light text-dark rounded-circle border" style="width: 42px; height: 42px; display: inline-flex; align-items: center; justify-content: center;">
                  <i class="fa-solid fa-table-cells-large fs-5"></i>
                </span>
                <div>
                  <p class="m-0 text-uppercase small text-muted">New</p>
                  <h4 class="m-0 fw-bold">Grid Menu</h4>
                </div>
              </div>
              <p class="text-muted mb-0">Browse sections in a responsive grid with quick descriptions and prices.</p>
              <div class="d-flex align-items-center justify-content-between">
                <span class="text-secondary">View the grid</span>
                <i class="fa-solid fa-arrow-right menu-choice-arrow"></i>
              </div>
            </div>
          </a>
        </div>
      </div>

      <div class="text-center mt-5">
        <a href="{{ route('customer.order') }}">
          <button class="reservation-btn w-100">Order Now</button>
        </a>
      </div>
    </div>
  </section>

  <style>
      .menu-choice-hero {
          background: linear-gradient(135deg, #0d0d0f, #1a120a);
          padding: 32px 18px;
          border: 1px solid rgba(255, 255, 255, 0.08);
          box-shadow: 0 18px 40px rgba(0, 0, 0, 0.25);
      }

      .menu-choice-heading {
          color: var(--mainColorOrange);
          letter-spacing: 0.01em;
      }

      .menu-choice-card {
          border: 1px solid #e9ecef;
          border-radius: 18px;
          transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
      }

      .menu-choice-card:hover {
          transform: translateY(-4px);
          box-shadow: 0 14px 30px rgba(0, 0, 0, 0.08);
          border-color: #d0d5dd;
      }

      .menu-choice-card:active {
          transform: translateY(-1px) scale(0.99);
          box-shadow: 0 10px 22px rgba(0, 0, 0, 0.08);
      }

      .menu-choice-arrow {
          transition: transform 0.2s ease;
      }

      .menu-choice-card:hover .menu-choice-arrow {
          transform: translateX(6px);
      }
  </style>
</x-customer.layout>
