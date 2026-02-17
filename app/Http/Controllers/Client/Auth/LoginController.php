<?php

namespace App\Http\Controllers\Client\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    // Mostrar el formulario de login
    public function showLoginForm()
    {
        return view('client.auth.login');
    }

    //Procesar el login
    public function login(Request $request)
    {
        // Validar los datos de entrada
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->except('password'));
        }

        // Intentar autenticar con el guard de clientes
        if (Auth::guard('client')->attempt(['email' => $request->email, 'password' => $request->password])) {

            // Obtener el usuario autenticado
            $user = Auth::guard('client')->user();

            // Verificar si el usuario tiene un registro en clients
            if (!$user->client) {
                Auth::guard('client')->logout();
                return redirect()->back()
                    ->withErrors(['email' => 'Este usuario no está registrado como cliente en el sistema.'])
                    ->withInput($request->except('password'));
            }

            // Verificar si el cliente está activo
            if (!$user->client->is_active) {
                Auth::guard('client')->logout();
                return redirect()->back()
                    ->withErrors(['email' => 'Tu cuenta de cliente está desactivada. Contacta al administrador.'])
                    ->withInput($request->except('password'));
            }

            $request->session()->regenerate();

            return redirect()->intended(route('client.home'))
                ->with('success', '¡Bienvenido de nuevo, ' . $user->nombres . '!');
        }

        // Si las credenciales son incorrectas
        return redirect()->back()
            ->withErrors(['email' => 'Las credenciales proporcionadas son incorrectas.'])
            ->withInput($request->except('password'));
    }

    //Cerrar sesión
    public function logout(Request $request)
    {
        // Cerrar sesión del guard de clientes
        Auth::guard('client')->logout();

        // Invalidar la sesión actual
        $request->session()->invalidate();

        // Regenerar el token CSRF
        $request->session()->regenerateToken();

        // Redirigir a la home con mensaje de éxito
        return redirect('/')  // También puedes usar route('home')
            ->with('success', '¡Has cerrado sesión correctamente! Vuelve pronto.');
    }
}
