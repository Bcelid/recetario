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

                <!-- Técnico -->
                <li class="nav-item">
                    <a href="#" class="nav-link text-white">
                        <i class="nav-icon fa-solid fa-user-tie"></i>
                        <p>
                            Técnico
                            <i class="nav-arrow fa-solid fa-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('technical.index') }}" class="nav-link text-white ps-4">
                                <i class="fa-solid fa-user nav-icon"></i>
                                <p>Listado</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('technical.categories') }}" class="nav-link text-white ps-4">
                                <i class="fa-solid fa-layer-group nav-icon"></i>
                                <p>Categoría</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('technical.signature') }}" class="nav-link text-white ps-4">
                                <i class="fa-solid fa-pen nav-icon"></i>
                                <p>Firma Electrónica</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Almacén -->
                <li class="nav-item">
                    <a href="#" class="nav-link text-white">
                        <i class="nav-icon fa-solid fa-store"></i>
                        <p>
                            Almacén
                            <i class="nav-arrow fa-solid fa-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('store.storeboss') }}" class="nav-link text-white ps-4">
                                <i class="fa-solid fa-user nav-icon"></i>
                                <p>Propietario</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('store.index') }}" class="nav-link text-white ps-4">
                                <i class="fa-solid fa-store nav-icon"></i>
                                <p>Almacén</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('store.client') }}" class="nav-link text-white ps-4">
                                <i class="fa-solid fa-user-check nav-icon"></i>
                                <p>Cliente</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Cultivo -->
                <li class="nav-item">
                    <a href="#" class="nav-link text-white">
                        <i class="nav-icon fa-solid fa-seedling"></i>
                        <p>
                            Cultivo
                            <i class="nav-arrow fa-solid fa-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('crop.plant') }}" class="nav-link text-white ps-4">
                                <i class="fa-solid fa-leaf nav-icon"></i>
                                <p>Planta</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('crop.plague') }}" class="nav-link text-white ps-4">
                                <i class="fa-solid fa-bug nav-icon"></i>
                                <p>Plaga</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Animal -->
                <li class="nav-item">
                    <a href="#" class="nav-link text-white">
                        <i class="nav-icon fa-solid fa-cow"></i>
                        <p>
                            Animal
                            <i class="nav-arrow fa-solid fa-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('animal.specie') }}" class="nav-link text-white ps-4">
                                <i class="fa-solid fa-paw nav-icon"></i>
                                <p>Especie</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('animal.subspecie') }}" class="nav-link text-white ps-4">
                                <i class="fa-solid fa-dna nav-icon"></i>
                                <p>Subespecie</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Producto -->
                <li class="nav-item">
                    <a href="#" class="nav-link text-white">
                        <i class="nav-icon fa-solid fa-box"></i>
                        <p>
                            Producto
                            <i class="nav-arrow fa-solid fa-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('product.activeingredient') }}" class="nav-link text-white ps-4">
                                <i class="fa-solid fa-flask nav-icon"></i>
                                <p>Ingrediente Activo</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('animal.subspecie') }}" class="nav-link text-white ps-4">
                                <i class="fa-solid fa-boxes-stacked nav-icon"></i>
                                <p>Subespecie</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <!-- Configuracion -->
                <li class="nav-item">
                    <a href="#" class="nav-link text-white">
                        <i class="nav-icon fa-solid fa-sliders"></i>
                        <p>
                            Configuracion
                            <i class="nav-arrow fa-solid fa-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('settings.measure') }}" class="nav-link text-white ps-4">
                                <i class="fa-solid fa-ruler nav-icon"></i>
                                <p>Medida</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('animal.subspecie') }}" class="nav-link text-white ps-4">
                               <i class="fa-solid fa-scale-balanced nav-icon"></i>
                                <p>Medida Dosificación</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Usuario -->
                <li class="nav-item">
                    <a href="{{ route('users.index') }}" class="nav-link text-white">
                        <i class="fa-solid fa-user nav-icon"></i>
                        <p>Usuario</p>
                    </a>
                </li>

            </ul>
        </nav>
    </div>
</aside>
