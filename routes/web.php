<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TecnicoCategoriaController;
use App\Http\Controllers\TecnicoController;
use App\Http\Controllers\TecnicoFirmaController;
use App\Http\Controllers\PropietarioAlmacenController;
use App\Http\Controllers\AlmacenController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CultivoController;
use App\Http\Controllers\MalezaController;
use Illuminate\Support\Facades\Route;

// Rutas públicas para login
Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/', [AuthController::class, 'login']);

// Rutas protegidas (middleware auth)
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::resource('users', UserController::class)->except(['show', 'destroy']);
    Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::post('users/{user}/changeEstado', [UserController::class, 'changeEstado'])->name('users.changeEstado');
    Route::patch('users/{user}/password', [UserController::class, 'updatePassword'])->name('users.updatePassword');
    Route::get('users/roles/active', [UserController::class, 'getRoles'])->name('users.roles.active');
    Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');


    Route::get('/technical/categories', [TecnicoCategoriaController::class, 'viewIndex'])->name('technical.categories');
    Route::prefix('tecnico-categorias')->name('tecnico-categorias.')->group(function () {
        Route::get('/', [TecnicoCategoriaController::class, 'index'])->name('index');
        Route::post('/', [TecnicoCategoriaController::class, 'store'])->name('store');
        Route::get('/{id}', [TecnicoCategoriaController::class, 'show'])->name('show');
        Route::patch('/{id}', [TecnicoCategoriaController::class, 'update'])->name('update');
        Route::delete('/{id}', [TecnicoCategoriaController::class, 'destroy'])->name('destroy');
    });

    // Vista principal
    Route::get('/technical', [TecnicoController::class, 'viewIndex'])->name('technical.index');

    // API CRUD
    Route::prefix('tecnico')->name('tecnico.')->group(function () {
        Route::get('/', [TecnicoController::class, 'index'])->name('index');
        Route::post('/', [TecnicoController::class, 'store'])->name('store');
        Route::get('/{id}', [TecnicoController::class, 'show'])->name('show');
        Route::put('/{id}', [TecnicoController::class, 'update'])->name('update');
        Route::delete('/{id}', [TecnicoController::class, 'destroy'])->name('destroy');
        Route::delete('/{id}/force', [TecnicoController::class, 'forceDelete'])->name('forceDelete');
    });

    Route::get('/technical/signature', [TecnicoFirmaController::class, 'viewIndex'])->name('technical.signature');
    // Lista de firmas
    Route::prefix('tecnico-firma')->name('tecnico-firma.')->group(function () {
        Route::get('/', [TecnicoFirmaController::class, 'index'])->name('index');
        Route::get('/create', [TecnicoFirmaController::class, 'create'])->name('create');
        Route::post('/', [TecnicoFirmaController::class, 'store'])->name('store');
        Route::get('/{id}', [TecnicoFirmaController::class, 'show'])->name('show');
        Route::put('/{id}', [TecnicoFirmaController::class, 'update'])->name('update');
        Route::delete('/{id}', [TecnicoFirmaController::class, 'destroy'])->name('destroy');
    });

    // Vista principal del módulo de propietarios de almacén
    Route::get('/store/storeboss', [PropietarioAlmacenController::class, 'viewIndex'])->name('store.storeboss');

    // Grupo de rutas para CRUD
    Route::prefix('propietario-almacen')->name('propietario-almacen.')->group(function () {
        Route::get('/', [PropietarioAlmacenController::class, 'index'])->name('index');       // Listar
        Route::get('/create', [PropietarioAlmacenController::class, 'create'])->name('create'); // Form crear
        Route::post('/', [PropietarioAlmacenController::class, 'store'])->name('store');       // Guardar nuevo
        Route::get('/{id}', [PropietarioAlmacenController::class, 'show'])->name('show');      // Ver detalle
        Route::patch('/{id}', [PropietarioAlmacenController::class, 'update'])->name('update');  // Actualizar
        Route::delete('/{id}', [PropietarioAlmacenController::class, 'destroy'])->name('destroy'); // Eliminar / cambiar estado

    });


    Route::get('/store', [AlmacenController::class, 'viewIndex'])->name('store.index');
    // Grupo de rutas para CRUD
    Route::prefix('almacen')->name('almacen.')->group(function () {
        Route::get('/search', [AlmacenController::class, 'search'])->name('search'); // Select2
        Route::get('/', [AlmacenController::class, 'index'])->name('index');         // Listar
        Route::get('/create', [AlmacenController::class, 'create'])->name('create'); // Form crear
        Route::post('/', [AlmacenController::class, 'store'])->name('store');        // Guardar nuevo
        Route::get('/{id}', [AlmacenController::class, 'show'])->name('show');       // Ver detalle
        Route::put('/{id}', [AlmacenController::class, 'update'])->name('update');   // Actualizar
        Route::delete('/{id}', [AlmacenController::class, 'destroy'])->name('destroy'); // Eliminar / cambiar estado

    });

    // Vista principal del listado de clientes
    Route::get('store/client', [ClienteController::class, 'viewIndex'])->name('store.client');

    // CRUD Cliente
    Route::prefix('cliente')->name('cliente.')->group(function () {
        Route::get('/', [ClienteController::class, 'index'])->name('index');             // Listar (con filtro)
        Route::post('/', [ClienteController::class, 'store'])->name('store');            // Crear nuevo
        Route::get('/{id}', [ClienteController::class, 'show'])->name('show');           // Ver detalle
        Route::put('/{id}', [ClienteController::class, 'update'])->name('update');       // Actualizar
        Route::delete('/{id}', [ClienteController::class, 'destroy'])->name('destroy');  // Activar/Inactivar
        Route::delete('/{id}/force', [ClienteController::class, 'forceDelete'])->name('forceDelete'); // Eliminación total (opcional)
    });

    // Vista principal del listado de cultivos
    Route::get('crop/plant', [CultivoController::class, 'viewIndex'])->name('crop.plant');

    // CRUD Cultivo
    Route::prefix('cultivos')->name('cultivo.')->group(function () {
    Route::get('/', [CultivoController::class, 'index'])->name('index');             // Listar con filtro
    Route::post('/', [CultivoController::class, 'store'])->name('store');            // Crear nuevo
    Route::get('/{id}', [CultivoController::class, 'show'])->name('show');           // Ver detalle
    Route::patch('/{id}', [CultivoController::class, 'update'])->name('update');     // Actualizar
    Route::delete('/{id}', [CultivoController::class, 'destroy'])->name('destroy');  // Activar/Inactivar (soft delete)
    });

    // Vista principal del listado de maleza
    Route::get('crop/plague', [MalezaController::class, 'viewIndex'])->name('crop.plague');
    // CRUD maleza
    Route::prefix('maleza')->name('maleza.')->group(function () {
        Route::get('/', [MalezaController::class, 'index'])->name('index');             // Listar con filtro
        Route::post('/', [MalezaController::class, 'store'])->name('store');            // Crear nuevo
        Route::get('/{id}', [MalezaController::class, 'show'])->name('show');           // Ver detalle
        Route::patch('/{id}', [MalezaController::class, 'update'])->name('update');     // Actualizar
        Route::delete('/{id}', [MalezaController::class, 'destroy'])->name('destroy');  // Activar/Inactivar (soft delete)
    });
});
