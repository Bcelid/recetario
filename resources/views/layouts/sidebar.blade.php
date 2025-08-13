<!-- SIDEBAR -->
<aside class="app-sidebar custom-sidebar shadow" data-bs-theme="light">
    <div class="sidebar-brand">
        <a href="{{ route('dashboard') }}" class="nav-link">
            <img src="{{ asset('img/Logocombinado_sinfondo.png') }}" alt="Recetario Logo" style="max-height: 55px;">
        </a>
    </div>

    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">

                <!-- Menú Técnico cerrado por defecto -->
                <li class="nav-item">
                    <a href="#" class="nav-link text-white">
                        <i class="nav-icon bi bi-person-square"></i>
                        <p>
                            Técnico
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>

                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('technical.index') }}" class="nav-link text-white ps-4">
                                <i class="bi bi-person nav-icon"></i>
                                <p>Listado</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('technical.categories') }}" class="nav-link text-white ps-4">
                                <i class="bi bi-collection nav-icon"></i>
                                <p>Categoría</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('technical.signature') }}" class="nav-link text-white ps-4">
                                <i class="bi bi-pencil nav-icon"></i>
                                <p>Firma Electrónica</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a href="#" class="nav-link text-white">
                        <i class="nav-icon bi bi-shop"></i>
                        <p>
                            Almacen
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>

                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('store.storeboss') }}" class="nav-link text-white ps-4">
                                <i class="bi bi-person nav-icon"></i>
                                <p>Propietario</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('store.index') }}" class="nav-link text-white ps-4">
                                <i class="bi bi-shop nav-icon"></i>
                                <p>Almacen</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('store.client') }}" class="nav-link text-white ps-4">
                                <i class="bi bi-person-check nav-icon"></i>
                                <p>Cliente</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Usuarios (sin submenú) -->
                <li class="nav-item">
                    <a href="{{ route('users.index') }}" class="nav-link text-white">
                        <i class="bi bi-person nav-icon"></i>
                        <p>Usuario</p>
                    </a>
                </li>

            </ul>
        </nav>
    </div>
</aside>
