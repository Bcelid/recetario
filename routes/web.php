<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TecnicoCategoriaController;
use App\Http\Controllers\TecnicoController;
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
    Route::patch('users/{user}/changeEstado', [UserController::class, 'changeEstado'])->name('users.changeEstado');
    Route::patch('users/{user}/password', [UserController::class, 'updatePassword'])->name('users.updatePassword');

    Route::get('/technical/categories', [TecnicoCategoriaController::class, 'viewIndex'])->name('technical.categories');
    Route::get('/tecnico-categorias', [TecnicoCategoriaController::class, 'index'])->name('tecnico-categorias.index');
    Route::post('/tecnico-categorias', [TecnicoCategoriaController::class, 'store'])->name('tecnico-categorias.store');
    Route::get('/tecnico-categorias/{id}', [TecnicoCategoriaController::class, 'show'])->name('tecnico-categorias.show');
    Route::patch('/tecnico-categorias/{id}', [TecnicoCategoriaController::class, 'update'])->name('tecnico-categorias.update');
    Route::delete('/tecnico-categorias/{id}', [TecnicoCategoriaController::class, 'destroy'])->name('tecnico-categorias.destroy');


    // Vista principal
    Route::get('/technical', [TecnicoController::class, 'viewIndex'])->name('technical.index');

    // API CRUD
    Route::get('/tecnico', [TecnicoController::class, 'index']);
    Route::post('/tecnico', [TecnicoController::class, 'store']);
    Route::get('/tecnico/{id}', [TecnicoController::class, 'show']);
    Route::put('/tecnico/{id}', [TecnicoController::class, 'update']);
    Route::delete('/tecnico/{id}', [TecnicoController::class, 'destroy']);
    Route::delete('/tecnico/{id}/force', [TecnicoController::class, 'forceDelete']); // opcional
});
