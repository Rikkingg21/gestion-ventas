<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductosController;
use Illuminate\Http\Request;


use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/productos', [ProductosController::class, 'index'])->name('producto.index');
Route::get('/productos/{id}', [ProductosController::class, 'show'])->name('producto.detalle');

Route::post('/cambiar-moneda', function (Request $request) {
    $request->validate(['currency' => 'required|string|size:3']);
    session(['moneda_seleccionada' => $request->currency]);
    return response()->json(['success' => true]);
})->name('cambiar.moneda');

// Rutas del carrito - TODAS USANDO POST para simplificar (o todas usando el método HTTP apropiado)
Route::get('/carrito/ver', [ProductosController::class, 'verCarrito'])->name('carrito.ver');
Route::post('/carrito/agregar/{producto}', [ProductosController::class, 'agregarAlCarrito'])->name('carrito.agregar');
Route::post('/carrito/actualizar-item/{item}', [ProductosController::class, 'actualizarCantidad'])->name('carrito.actualizar-item');
Route::delete('/carrito/eliminar/{productoId}', [ProductosController::class, 'eliminarDelCarrito'])->name('carrito.eliminar');
Route::post('/carrito/vaciar', [ProductosController::class, 'vaciarCarrito'])->name('carrito.vaciar');

Route::get('/checkout', [App\Http\Controllers\Client\PayController::class, 'index'])->name('checkout.index')->middleware('auth:client');
Route::post('/checkout', [App\Http\Controllers\Client\PayController::class, 'store'])->name('checkout.store')->middleware('client.auth');
Route::post('/checkout/validar-cupon', [App\Http\Controllers\Client\PayController::class, 'validarCupon'])->name('checkout.validar-cupon');
Route::post('/checkout/quitar-cupon', [App\Http\Controllers\Client\PayController::class, 'quitarCupon'])->name('checkout.quitar-cupon');
Route::post('/checkout/procesar', [App\Http\Controllers\Client\PayController::class, 'procesarPago'])->name('checkout.procesar');

Route::get('/mis-compras', [ComprasController::class, 'index'])->name('compras.index')->middleware('auth:client');
Route::get('/nosotros', [ProductosController::class, 'funcion'])->name('nosotros');

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

//ruta staff
Route::prefix('staff')->name('staff.')->group(function () {

    // Login del staff (público)
    Route::get('/login', [App\Http\Controllers\Staff\Auth\LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [App\Http\Controllers\Staff\Auth\LoginController::class, 'login']);

    // Rutas protegidas para staff
    Route::middleware('auth:staff')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Staff\DashboardController::class, 'dashboard'])->name('dashboard');
        Route::post('/logout', [App\Http\Controllers\Staff\Auth\LoginController::class, 'logout'])->name('logout');

        // Configuración del sistema
        Route::get('/perfil', [App\Http\Controllers\Staff\ProfileController::class, 'index'])->name('profile');

        // Gestión de asignación de permisos - requiere permiso de lectura
        Route::middleware('staff:leer,productos')->group(function () {
            Route::get('/productos', [App\Http\Controllers\Staff\ProductosController::class, 'index'])->name('productos.index');
            Route::get('/productos/create', [App\Http\Controllers\Staff\ProductosController::class, 'create'])->name('productos.create')->middleware('staff:crear,productos');
            Route::post('/productos', [App\Http\Controllers\Staff\ProductosController::class, 'store'])->name('productos.store')->middleware('staff:crear,productos');
            Route::get('/productos/{producto}/edit', [App\Http\Controllers\Staff\ProductosController::class, 'edit'])->name('productos.edit')->middleware('staff:actualizar,productos');
            Route::put('/productos/{producto}', [App\Http\Controllers\Staff\ProductosController::class, 'update'])->name('productos.update')->middleware('staff:actualizar,productos');
            Route::delete('/productos/{producto}', [App\Http\Controllers\Staff\ProductosController::class, 'destroy'])->name('productos.destroy')->middleware('staff:eliminar,productos');
        });

        // Módulos del staff
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

        // Configuración del sistema
        Route::get('/perfil', [App\Http\Controllers\Admin\ProfileController::class, 'index'])->name('profile');

        Route::middleware('admin:leer,modules')->group(function () {
            Route::get('/modules', [App\Http\Controllers\Admin\ModuleController::class, 'index'])->name('modules.index');
            Route::get('/modules/create', [App\Http\Controllers\Admin\ModuleController::class, 'create'])->name('modules.create')->middleware('admin:crear,modules');
            Route::post('/modules', [App\Http\Controllers\Admin\ModuleController::class, 'store'])->name('modules.store')->middleware('admin:crear,modules');
            Route::get('/modules/{module}/edit', [App\Http\Controllers\Admin\ModuleController::class, 'edit'])->name('modules.edit')->middleware('admin:actualizar,modules');
            Route::put('/modules/{module}', [App\Http\Controllers\Admin\ModuleController::class, 'update'])->name('modules.update')->middleware('admin:actualizar,modules');
            Route::delete('/modules/{module}', [App\Http\Controllers\Admin\ModuleController::class, 'destroy'])->name('modules.destroy')->middleware('admin:eliminar,modules');
        });

        Route::middleware('admin:leer,users')->group(function () {
            Route::get('/users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
            Route::get('/users/create', [App\Http\Controllers\Admin\UserController::class, 'create'])->name('users.create')->middleware('admin:crear,user');
            Route::get('/users/search-by-document', [App\Http\Controllers\Admin\UserController::class, 'searchByDocument'])->name('users.search-by-document');
            Route::post('/users', [App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store')->middleware('admin:crear,user');
            Route::get('/users/{user}/edit', [App\Http\Controllers\Admin\UserController::class, 'edit'])->name('users.edit')->middleware('admin:actualizar,user');
            Route::put('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update')->middleware('admin:actualizar,user');
            Route::delete('/usersd/{user}', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy')->middleware('admin:eliminar,user');
        });

        // Gestión de asignación de permisos - requiere permiso de lectura
        Route::middleware('admin:leer,permisos')->group(function () {
            Route::get('/permisos', [App\Http\Controllers\Admin\PermisosController::class, 'index'])->name('permisos.index');
            Route::post('/permisos/save', [App\Http\Controllers\Admin\PermisosController::class, 'save'])->name('permisos.save')->middleware('admin:actualizar,permisos');
            Route::delete('/permisos/{id}', [App\Http\Controllers\Admin\PermisosController::class, 'destroy'])->name('permisos.destroy')->middleware('admin:eliminar,permisos');
            Route::get('/permisos/user-permissions', [App\Http\Controllers\Admin\PermisosController::class, 'getUserPermissions'])->name('permisos.user-permissions');
            Route::get('/permisos/permission-history', [App\Http\Controllers\Admin\PermisosController::class, 'getPermissionHistory'])->name('permisos.history');
        });

        // Gestión de asignación de categorias - requiere permiso de lectura
        Route::middleware('admin:leer,categorias')->group(function () {
            Route::get('/categorias', [App\Http\Controllers\Admin\CategoriasController::class, 'index'])->name('categorias.index');
            Route::get('/categorias/create', [App\Http\Controllers\Admin\CategoriasController::class, 'create'])->name('categorias.create')->middleware('admin:crear,categorias');
            Route::post('/categorias', [App\Http\Controllers\Admin\CategoriasController::class, 'store'])->name('categorias.store')->middleware('admin:crear,categorias');
            Route::get('/categorias/{categoria}/edit', [App\Http\Controllers\Admin\CategoriasController::class, 'edit'])->name('categorias.edit')->middleware('admin:actualizar,categorias');
            Route::put('/categorias/{categoria}', [App\Http\Controllers\Admin\CategoriasController::class, 'update'])->name('categorias.update')->middleware('admin:actualizar,categorias');
            Route::delete('/categorias/{categoria}', [App\Http\Controllers\Admin\CategoriasController::class, 'destroy'])->name('categorias.destroy')->middleware('admin:eliminar,categorias');
        });

        // Gestión de asignación de permisos - requiere permiso de lectura
        Route::middleware('admin:leer,productos')->group(function () {
            Route::get('/productos', [App\Http\Controllers\Admin\ProductosController::class, 'index'])->name('productos.index');
            Route::get('/productos/create', [App\Http\Controllers\Admin\ProductosController::class, 'create'])->name('productos.create')->middleware('admin:crear,productos');
            Route::post('/productos', [App\Http\Controllers\Admin\ProductosController::class, 'store'])->name('productos.store')->middleware('admin:crear,productos');
            Route::get('/productos/{producto}/edit', [App\Http\Controllers\Admin\ProductosController::class, 'edit'])->name('productos.edit')->middleware('admin:actualizar,productos');
            Route::put('/productos/{producto}', [App\Http\Controllers\Admin\ProductosController::class, 'update'])->name('productos.update')->middleware('admin:actualizar,productos');
            Route::delete('/productos/{producto}', [App\Http\Controllers\Admin\ProductosController::class, 'destroy'])->name('productos.destroy')->middleware('admin:eliminar,productos');
            Route::post('/productos/{producto}/eliminar-imagen', [App\Http\Controllers\Admin\ProductosController::class, 'eliminarImagen'])->name('productos.eliminar-imagen')->middleware('admin:actualizar,productos');
        });

        // Gestión de personal
        Route::resource('personal', App\Http\Controllers\Admin\StaffController::class);

        // Configuración del sistema
        Route::get('/configuracion', [App\Http\Controllers\Admin\ConfigController::class, 'index'])->name('config');

        // Reportes generales
        Route::get('/reportes', [App\Http\Controllers\Admin\ReportesController::class, 'index'])->name('reportes');
    });
});
