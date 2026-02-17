<?php

namespace App\Http\Controllers\Staff\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('staff.auth.login');
    }

    public function login(Request $request)
    {
        // Validar los datos de entrada
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|min:3|max:100',
            'password' => 'required|string|min:3',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->except('password'));
        }

        // Intentar autenticar con el guard de administradores
        if (Auth::guard('staff')->attempt(['username' => $request->username, 'password' => $request->password])) {

            // Obtener el usuario autenticado
            $user = Auth::guard('staff')->user();

            // Verificar si el usuario tiene un registro en admins
            if (!$user->staff) {
                Auth::guard('staff')->logout();
                return redirect()->back()
                    ->withErrors(['username' => 'Este usuario no está registrado como staff en el sistema.'])
                    ->withInput($request->except('password'));
            }

            // Verificar si el administrador está activo
            if (!$user->staff->is_active) {
                Auth::guard('staff')->logout();
                return redirect()->back()
                    ->withErrors(['username' => 'Tu cuenta de staff está desactivada. Contacta al administrador superior.'])
                    ->withInput($request->except('password'));
            }

            // Regenerar sesión por seguridad
            $request->session()->regenerate();

            // Redirigir al dashboard de admin
            return redirect()->intended(route('staff.dashboard'))
                ->with('success', '¡Bienvenido al panel de staff, ' . $user->nombres . '!');
        }

        // Si las credenciales son incorrectas
        return redirect()->back()
            ->withErrors(['username' => 'Datos incorrectos.'])
            ->withInput($request->except('password'));
    }

    public function logout(Request $request)
    {
        // Cerrar sesión del guard de administradores
        Auth::guard('staff')->logout();

        // Invalidar la sesión actual
        $request->session()->invalidate();

        // Regenerar el token CSRF
        $request->session()->regenerateToken();

        // Redirigir al login de admin con mensaje
        return redirect()->route('staff.login')
            ->with('success', 'Has cerrado sesión correctamente.');
    }
}
