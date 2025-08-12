<!-- SIDEBAR -->
    <aside class="app-sidebar custom-sidebar shadow" data-bs-theme="light">
      <div class="sidebar-brand">
        <a href="{{ route('dashboard') }}" class="nav-link">
              <img src="{{ asset('img/Logocombinado_sinfondo.png') }}" alt="Recetario Logo" style="max-height: 55px;">
            </a>
      </div>
      <div class="sidebar-wrapper">
        <nav class="mt-2">
          <ul
            class="nav sidebar-menu flex-column"
            data-lte-toggle="treeview"
            role="menu"
            data-accordion="false"
          >
            <li class="nav-item menu-open">
              <a href="#" class="nav-link active text-white">
                <i class="nav-icon bi bi-database"></i>
                <p>
                  Base de Datos
                  <i class="nav-arrow bi bi-chevron-right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                 <a href="{{ route('users.index') }}" class="nav-link text-white ps-4">
                  <i class="bi bi-person nav-icon"></i>
                  <p>Usuario</p>
                </a>

                </li>
              </ul>
            </li>
            <li class="nav-item menu-open">
              <a href="#" class="nav-link active text-white">
                <i class="nav-icon bi bi-gear"></i>
                <p>
                  Configuración
                  <i class="nav-arrow bi bi-chevron-right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                 <a href="{{ route('technical.categories') }}" class="nav-link text-white ps-4">
                  <i class="bi bi-collection nav-icon"></i>
                  <p>Categoria</p>
                </a>

                </li>
              </ul>
            </li>
          </ul>
        </nav>
      </div>
    </aside>