<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;


use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;

//ruta publica
Route::get('/', [HomeController::class, 'index'])->name('home');

//ruta clientes
Route::prefix('/')->name('client.')->group(function () {

    // Login de clientes (público)
    Route::get('/login', [App\Http\Controllers\Client\Auth\LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [App\Http\Controllers\Client\Auth\LoginController::class, 'login']);

    // Registro de clientes
    Route::get('/register', [App\Http\Controllers\Client\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [App\Http\Controllers\Client\Auth\RegisterController::class, 'register']);

    Route::post('/logout', [App\Http\Controllers\Client\Auth\LoginController::class, 'logout'])->name('logout')->middleware('auth:client');
    // Rutas protegidas para clientes
    Route::middleware('auth:client')->group(function () {
        Route::get('/home', [App\Http\Controllers\Client\HomeController::class, 'home'])->name('home');
        Route::post('/logout', [App\Http\Controllers\Client\Auth\LoginController::class, 'logout'])->name('logout');

        // Perfil del cliente
        Route::get('/perfil', [App\Http\Controllers\Client\ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/perfil', [App\Http\Controllers\Client\ProfileController::class, 'update'])->name('profile.update');
    });
});
//ruta personal
Route::prefix('staff')->name('staff.')->group(function () {

    // Login del personal (público)
    Route::get('/login', [App\Http\Controllers\Staff\Auth\LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [App\Http\Controllers\Staff\Auth\LoginController::class, 'login']);

    // Rutas protegidas para personal
    Route::middleware('auth:staff')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Staff\DashboardController::class, 'dashboard'])->name('dashboard');
        Route::post('/logout', [App\Http\Controllers\Staff\Auth\LoginController::class, 'logout'])->name('logout');

        // Módulos del personal
        Route::resource('ventas', App\Http\Controllers\Staff\VentasController::class);
        Route::resource('clientes', App\Http\Controllers\Staff\ClientesController::class);
        Route::resource('productos', App\Http\Controllers\Staff\ProductosController::class);

        // Reportes
        Route::get('/reportes', [App\Http\Controllers\Staff\ReportesController::class, 'index'])->name('reportes');
    });
});
//ruta administradores
Route::prefix('admin')->name('admin.')->group(function () {

    // Login de administradores (público)
    Route::get('/login', [App\Http\Controllers\Admin\Auth\LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [App\Http\Controllers\Admin\Auth\LoginController::class, 'login']);

    // Rutas protegidas para administradores
    Route::middleware('auth:admin')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'dashboard'])->name('dashboard');
        Route::post('/logout', [App\Http\Controllers\Admin\Auth\LoginController::class, 'logout'])->name('logout');

        Route::get('/modulos', [App\Http\Controllers\Admin\ModuleController::class, 'index'])->name('index');
        Route::get('/modulos/create', [App\Http\Controllers\Admin\ModuleController::class, 'create'])->name('admin.modules.create');
        // Gestión de personal
        Route::resource('personal', App\Http\Controllers\Admin\StaffController::class);

        // Configuración del sistema
        Route::get('/configuracion', [App\Http\Controllers\Admin\ConfigController::class, 'index'])->name('config');

        // Reportes generales
        Route::get('/reportes', [App\Http\Controllers\Admin\ReportesController::class, 'index'])->name('reportes');
    });
});
