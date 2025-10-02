      <nav class="navbar navbar-custom d-flex justify-content-between px-3">
        <div class="left-group d-flex align-items-center gap-2 flex-grow-1 min-w-0">
          <button id="sidebarToggleDesktop" title="Toggle Sidebar"><i class="bi bi-list"></i></button>
        </div>
        <div class="right-group d-flex align-items-center gap-1">
          <button class="sekunder nav-icon" title="Messages"><i class="bi bi-chat-left-text"></i></button>
          <livewire:backoffice.tenant-notification />
          <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-black" data-bs-toggle="dropdown" aria-expanded="false">
              <img src="https://i.pravatar.cc/30" alt="User" class="rounded-circle me-2" />
              <strong class="sekunder d-none d-sm-inline"></strong>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="#">Settings</a></li>
              <li><hr class="dropdown-divider" /></li>
            </ul>
          </div>
        </div>
      </nav>
