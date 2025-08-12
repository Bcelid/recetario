
    <!-- HEADER -->
    <nav class="app-header navbar navbar-expand bg-body">
      <div class="container-fluid">
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
              <i class="bi bi-list"></i>
            </a>
          </li>
        </ul>

        <ul class="navbar-nav ms-auto">
          <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" role="button" aria-expanded="false">
            <i class="bi bi-person-circle fs-4"></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li class="dropdown-item-text">
              <strong>{{ auth()->user()->username }}</strong>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="dropdown-item">Cerrar sesión</button>
              </form>
            </li>
          </ul>
        </ul>
      </div>
    </nav>