<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RedirectController extends Controller
{
    public function __invoke()
    {
        // Aquí puedes personalizar según el guard que intentaba acceder
        if (request()->is('client/*')) {
            return redirect()->route('client.login')
                ->with('info', 'Por favor, inicia sesión para acceder al área de clientes.');
        }

        // Redirección por defecto
        return redirect()->route('home')
            ->with('info', 'Por favor, inicia sesión para continuar.');
    }
}
