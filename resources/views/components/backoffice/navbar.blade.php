      <nav class="navbar navbar-expand navbar-custom d-flex justify-content-between px-3">
        <div class="d-flex w-100 gap-3">
        <button id="sidebarToggleDesktop" title="Toggle Sidebar"><i class="bi bi-list"></i></button>
        <form class="d-flex w-75" role="search">
          <input class="form-control" type="search" placeholder="Search" aria-label="Search" />
        </form>
        </div>
        <div class="d-flex align-items-center gap-1">
          <button class="sekunder nav-icon" title="Messages"><i class="bi bi-chat-left-text"></i></button>
          <button class="sekunder nav-icon" title="Notifications"><i class="bi bi-bell"></i></button>
          <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-black" data-bs-toggle="dropdown" aria-expanded="false">
              <img src="https://i.pravatar.cc/30" alt="User" class="rounded-circle me-2" />
              <strong class="sekunder">{{Auth::user()->name}}</strong>
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
        </div>
      </nav>