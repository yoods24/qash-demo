      <nav class="navbar navbar-custom hide d-flex justify-content-between px-3">
        <div class="left-group d-flex align-items-center gap-2 flex-grow-1 min-w-0">
          <button id="sidebarToggleDesktop" title="Toggle Sidebar"><i class="bi bi-list"></i></button>
        </div>
        <div class="right-group d-flex align-items-center gap-1">
          <button id="fullscreenToggle" class="sekunder nav-icon" title="Toggle fullscreen" aria-label="Toggle fullscreen">
            <i data-fullscreen-icon class="bi"></i>
          </button>
          <button id="themeToggle" class="sekunder nav-icon" title="Toggle theme" aria-label="Toggle theme">
            <i data-theme-icon class="bi"></i>
          </button>
          <livewire:backoffice.tenant-notification />
          <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-black" data-bs-toggle="dropdown" aria-expanded="false">
              <img src="https://i.pravatar.cc/30" alt="User" class="rounded-circle me-2" />
              <strong class="sekunder d-none d-sm-inline">{{ trim((Auth::user()->firstName ?? '') . ' ' . (Auth::user()->lastName ?? '')) }}</strong>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="{{route('backoffice.user.edit', Auth::user())}}">Profile</a></li>
              <li><a class="dropdown-item" href="#">Settings</a></li>
              <li><hr class="dropdown-divider" /></li>
              <form action="{{route('auth.logout', Auth::user()->id)}}" method="POST">
                @csrf
                @method('POST')
              <li><button class="dropdown-item" href="#">Logout</button></li>
              </form>
            </ul>
          </div>
          <div class="toggle-nav-container">
            <button id="toggleNavigationBar">
              <i class="bi bi-arrow-up-circle-fill p-1"></i>
            </button>
          </div>
        </div>
      </nav>
