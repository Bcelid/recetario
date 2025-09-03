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
use App\Http\Controllers\EspecieController;
use App\Http\Controllers\SubespecieController;
use App\Http\Controllers\IngredienteActivoController;
use App\Http\Controllers\UnidadMedidaController;
use App\Http\Controllers\UnidadMedidaDosificacionController;
use App\Http\Controllers\FormulacionController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\UserEmailConfigController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\RecetaEmailController;
use App\Http\Controllers\DashboardController;

// Rutas públicas para login
Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/', [AuthController::class, 'login']);

// Rutas protegidas (middleware auth)
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'getData'])->name('dashboard.data');
    Route::get('/dashboard/charts-data', [DashboardController::class, 'getChartsData'])->name('dashboard.charts');
    Route::get('/dashboard/counts', [DashboardController::class, 'getCounts'])->name('dashboard.counts');




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
    Route::post('client/import',[ClienteController::class,'import'])->name('cliente.import');

    // CRUD Cliente
    Route::prefix('cliente')->name('cliente.')->group(function () {
        Route::get('/', [ClienteController::class, 'index'])->name('index');             // Listar (con filtro)
        Route::post('/', [ClienteController::class, 'store'])->name('store');
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

    // Vista principal del listado de maleza
    Route::get('animal/specie', [EspecieController::class, 'viewIndex'])->name('animal.specie');
    // CRUD maleza
    Route::prefix('especie')->name('especie.')->group(function () {
        Route::get('/', [EspecieController::class, 'index'])->name('index');             // Listar con filtro
        Route::post('/', [EspecieController::class, 'store'])->name('store');            // Crear nuevo
        Route::get('/{id}', [EspecieController::class, 'show'])->name('show');           // Ver detalle
        Route::patch('/{id}', [EspecieController::class, 'update'])->name('update');     // Actualizar
        Route::delete('/{id}', [EspecieController::class, 'destroy'])->name('destroy');  // Activar/Inactivar (soft delete)
        // Listar especies activas
    });

    Route::get('/subespecie/activos', [SubespecieController::class, 'listActive'])->name('subespecie.listActive');
    Route::get('animal/subspecie', [SubespecieController::class, 'viewIndex'])->name('animal.subspecie');
    Route::get('/subespecie', [SubespecieController::class, 'index']);
    Route::post('/subespecie', [SubespecieController::class, 'store']);
    Route::get('/subespecie/{id}', [SubespecieController::class, 'show']);
    Route::put('/subespecie/{id}', [SubespecieController::class, 'update']);
    Route::delete('/subespecie/{id}', [SubespecieController::class, 'destroy']);
    Route::get('/subespecie/especie/{especie_id}', [SubespecieController::class, 'listByEspecie']); ////obtener las especies por especie_id


    Route::get('/product/activeingredient', [IngredienteActivoController::class, 'viewIndex'])->name('product.activeingredient'); // Vista Blade: product/activeingredient.blade.php

    // CRUD de Ingredientes Activos
    Route::prefix('ingredientes-activos')->name('ingredientes-activos.')->group(function () {
        Route::get('/', [IngredienteActivoController::class, 'index'])->name('list');         // Listar con filtro
        Route::post('/', [IngredienteActivoController::class, 'store'])->name('store');        // Crear nuevo
        Route::get('/{id}', [IngredienteActivoController::class, 'show'])->name('show');       // Ver detalle
        Route::patch('/{id}', [IngredienteActivoController::class, 'update'])->name('update'); // Actualizar
        Route::delete('/{id}', [IngredienteActivoController::class, 'destroy'])->name('destroy'); // Activar/Inactivar
    });

    Route::get('/settings/measure', [UnidadMedidaController::class, 'viewIndex'])->name('settings.measure'); // Vista Blade: product/activeingredient.blade.php

    // CRUD de Ingredientes Activos
    Route::prefix('unidad-medida')->name('unidad-medida.')->group(function () {
        Route::get('/', [UnidadMedidaController::class, 'index'])->name('list');         // Listar con filtro
        Route::post('/', [UnidadMedidaController::class, 'store'])->name('store');        // Crear nuevo
        Route::get('/{id}', [UnidadMedidaController::class, 'show'])->name('show');       // Ver detalle
        Route::patch('/{id}', [UnidadMedidaController::class, 'update'])->name('update'); // Actualizar
        Route::delete('/{id}', [UnidadMedidaController::class, 'destroy'])->name('destroy'); // Activar/Inactivar
    });

    Route::get('/settings/dosageunit', [UnidadMedidaDosificacionController::class, 'viewIndex'])->name('settings.dosageunit'); // Vista Blade: product/activeingredient.blade.php
    // CRUD de Ingredientes Activos
    Route::prefix('unidad-medida-dosificacion')->name('unidad-medida-dosificacion.')->group(function () {
        Route::get('/', [UnidadMedidaDosificacionController::class, 'index'])->name('list');         // Listar con filtro
        Route::post('/', [UnidadMedidaDosificacionController::class, 'store'])->name('store');        // Crear nuevo
        Route::get('/{id}', [UnidadMedidaDosificacionController::class, 'show'])->name('show');       // Ver detalle
        Route::patch('/{id}', [UnidadMedidaDosificacionController::class, 'update'])->name('update'); // Actualizar
        Route::delete('/{id}', [UnidadMedidaDosificacionController::class, 'destroy'])->name('destroy'); // Activar/Inactivar
    });

    Route::get('/settings/formulation', [FormulacionController::class, 'viewIndex'])->name('settings.formulation'); // Vista Blade: product/activeingredient.blade.php
    // CRUD de Ingredientes Activos
    Route::prefix('formulacion')->name('formulacion.')->group(function () {
        Route::get('/', [FormulacionController::class, 'index'])->name('list');         // Listar con filtro
        Route::post('/', [FormulacionController::class, 'store'])->name('store');        // Crear nuevo
        Route::get('/{id}', [FormulacionController::class, 'show'])->name('show');       // Ver detalle
        Route::patch('/{id}', [FormulacionController::class, 'update'])->name('update'); // Actualizar
        Route::delete('/{id}', [FormulacionController::class, 'destroy'])->name('destroy'); // Activar/Inactivar
    });

    Route::get('/product/show/{id}', [ProductoController::class, 'show'])->name('product.show');
    Route::get('/producto/tipo/{tipo}', [ProductoController::class, 'getByTipo'])->name('producto.byTipo');
    Route::get('/product/index', [ProductoController::class, 'viewIndex'])->name('product.index'); // Vista Blade: product/activeingredient.blade.php
    Route::get('/product/product', [ProductoController::class, 'viewCreate'])->name('product.product'); // Vista para crear producto
    Route::get('/producto', [ProductoController::class, 'index'])->name('list');
    Route::get('/producto/{id}', [ProductoController::class, 'edit'])->name('producto.edit');
    Route::put('/producto/{id}', [ProductoController::class, 'update'])->name('producto.update');



    Route::delete('/producto/{id}', [ProductoController::class, 'destroy'])->name('producto.destroy');
    Route::post('/producto', [ProductoController::class, 'store'])->name('store');
    Route::get('/prescription/newprescription', [PrescriptionController::class, 'viewCreate'])->name('prescription.newprescription'); // Vista para crear producto
    Route::get('/prescription/list_prescription', [PrescriptionController::class, 'viewPrescriptionLote'])->name('prescription.list_prescription'); // Vista para crear producto
    Route::post('/prescription', [PrescriptionController::class, 'store'])->name('prescription.store'); // Guardar nueva prescripción
    Route::get('/receta-agricola/imprimir/{loteId}', [PrescriptionController::class, 'imprimirRecetasAgricolas'])->name('receta.agricola.imprimir');
    Route::get('/recetas/lotes/data', [PrescriptionController::class, 'getLotesData'])->name('receta.lotes.data');
    Route::post('/receta/firmar', [PrescriptionController::class, 'firmarLote'])->name('receta.firmar');
    Route::delete('/receta/{id}', [PrescriptionController::class, 'destroy'])->name('receta.destroy');
    Route::get('/recetas/{id}/pdf', [PrescriptionController::class, 'exportarPDF']);
    Route::get('/prescription/newprescriptionv1', [PrescriptionController::class, 'viewCreatev1'])->name('prescription.newprescriptionv1'); // Vista para crear producto
    Route::post('/prescriptionv1', [PrescriptionController::class, 'storev1'])->name('prescription.storev1'); // Guardar nueva prescripción




    Route::get('/configuracion-correo', [UserEmailConfigController::class, 'edit'])->name('correo.config');
    Route::post('/configuracion-correo', [UserEmailConfigController::class, 'update'])->name('correo.config.update');
    Route::post('/correo/config/test', [UserEmailConfigController::class, 'testConnection'])->name('correo.config.test');

    Route::get('/receta/email/datos/{id}', [RecetaEmailController::class, 'getDatosEnvio'])->name('receta.email.datos');
    Route::post('/receta/enviar-correo', [RecetaEmailController::class, 'enviarCorreo'])->middleware('auth')->name('receta.correo.enviar');


    
});
