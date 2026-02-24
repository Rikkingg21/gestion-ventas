<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use App\View\Composers\AdminMenuComposer;
use App\View\Composers\StaffMenuComposer;
use App\View\Composers\ClienteMenuComposer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.admin.app', AdminMenuComposer::class);
        View::composer('layouts.staff.app', StaffMenuComposer::class);
        View::composer('layouts.cliente.app', ClienteMenuComposer::class);

        // Personalizar la redirección cuando un usuario no autenticado intenta acceder
        // a rutas protegidas
        Route::pattern('client', 'client');
    }
}
